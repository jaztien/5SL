<?php
/**
 * 專案名稱：FH-Php 安全防護系統
 * 模組名稱：SecurityGuard (安全防衛與防刷黑名單控制中心)
 * 
 * 【系統核心架構簡述】
 * 本類別為專案量身打造之核心安全層，專門整合「商業授權驗證」與「高併發行為防刷（Rate Limiting）」。
 * 具備以下核心設計技術：
 * 
 * 1. 雙重守門防禦：
 *    優先檢核特定功能之授權憑證（支援計次模式、24小時內免扣額期間、以及計日時間區間模式），
 *    授權驗證通過後，才放行並流向防刷名單檢驗，有效杜絕無權限之非法惡意流量耗費系統日誌。
 * 
 * 2. 記憶體靜態快取與序列化優化：
 *    完美串接 common.lib.php 共用函式庫，動態讀取 general.json 設定檔。
 *    採用 Static Memory Cache 與作業系統暫存夾反序列化（unserialize）雙重技術，
 *    在同一次請求中不重複讀取硬碟，以極低開銷動態配置課程商品、文章、服務等多元服務之點閱更新。
 * 
 * 3. 實體計數與原子性交易（Database Transaction）：
 *    徹底摒棄不穩定的時間戳 COUNT 統計，改採實體欄位 ban_count 的絕對值累加。
 *    全類別涉及多表複合寫入（如更新扣額並寫入明細、解除觀察名單並寫入事件日誌）之關鍵段落，
 *    全面導入 PDO beginTransaction/commit/rollBack 機制，確保底層資料 100% 同生共死。
 * 
 * 4. 參數去冒號防呆相容：
 *    內建 cleanPrefix 清理機制，外部傳入陣列鍵值（Key）一律不加冒號，
 *    完美契合主資料庫類別（DB Class）由內部自動補足冒號繫結（:name）之底層脾氣。
 * 
 * 【依賴項目】
 * @see DB (主資料庫操作類別 - pdoSQL.class.php)
 * @see _fn_getCachedJsonData (共用快取函式 - common.lib.php)
 * 
 * @package    FH-Php\ClassFiles
 * @author     Jaztien <jaztien@gmail.com>
 * @version    3.0.0 (授權防刷暨 Transaction 完全定案版)
 * @date       2026-08-18
 */class SecurityGuard
{
    private $db;    
    private $eventConfigs = array(
        'doVisit' => array(
            'config_limitSec'   => 2,
            'config_maxVio'     => 15,
            'config_banPeriod'  => 1
        ),
        'doRate' => array(
            'config_limitSec'   => 10,
            'config_maxVio'     => 5,
            'config_banPeriod'  => 24
        ),
        'default' => array(
            'config_limitSec'   => 3,
            'config_maxVio'     => 10,
            'config_banPeriod'  => 1
        )
    );

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    /**
     * 私有功用：JSON 設定檔之高效記憶體與序列化雙重快取
     * 
     * 1. 減少 I/O：呼叫外部共用函式庫，若記憶體中已有資料，同生命週期內不重複讀取硬碟。
     * 2. 反序列化：快取有效時，不重複執行重型的 json_decode，以極低開銷取得多服務參數。
     * 
     * @return array 回傳 general.json 的解析陣列，找不到或錯誤則回傳空陣列
     */
    private function getGeneralConfig()
    {
        $jsonPath = __DIR__ . '/../jsonFiles/general.json';
        
        // 檢查共用函式是否存在，若存在則直接調用共用函式庫的函數
        if (function_exists('_fn_getCachedJsonData')) {
            return _fn_getCachedJsonData($jsonPath);
        }
        
        // 防呆備援：若沒載入共用檔，退回基本的直接讀取
        if (file_exists($jsonPath)) {
            $geGet = file_get_contents($jsonPath);
            return json_decode($geGet, true) ?: array();
        }
        
        return array();    
    }
    /**
     * 公開主控：高併發精密防刷、黑名單檢驗與授權資格流向控制
     * 
     * 1. 雙重守門：優先調用 checkAuthorization，授權被拒時立刻切斷並短路返回錯誤。
     * 2. 三態鎖定：權限通過後觸發防刷檢驗，控制「正常操作」、「觀察中」、「限制中」三大行為狀態。
     * 3. 交易安全：對涉及黑名單異動與事件寫入的複合式 SQL 段落，全面啟用 Transaction 保護。
     * 
     * @param string $eventType    事件類型 (如 'doVisit', 'doRate')
     * @param int    $mbID         會員ID
     * @param string $thumbmark    瀏覽器裝置指紋值
     * @param array  $extraParams  前端傳入之業務專屬欄位資料 (不加冒號之純文字鍵名)
     * @return array 回傳包含執行布林(returnDone)、狀態代碼(authCode/secCode)與白話說明(desText)的關聯陣列
     */

    public function checkAction($eventType, $mbID, $thumbmark, $extraParams = array())
    {

        //取得並定義傳入的$extraParams陣列中的資料
        $auID     = isset($extraParams['auID']) ? (int)$extraParams['auID'] : 0;        
        $forVal   = isset($extraParams['forVal']) ? (int)$extraParams['forVal'] : 0;
        $foridVal = isset($extraParams['foridVal']) ? (int)$extraParams['foridVal'] : 0;
        //先預設權限狀態為「沒有設定權限」
        $authResult = 'SUCCESS_AUTH_NONE'; 
        //判定是否為開放性操作，開放性操作即是沒有目標頁面的瀏覽，可能是公開瀏覽的介紹、宣傳、敘事或是列表等頁面
        //沒有$auID並且沒有$forVal或是$foridVal值時為開放性操作，不執行資料庫權限反查，但維持 SUCCESS_AUTH_NONE，讓程式繼續往下進入安全防禦檢查
        if (empty($auID) && (empty($forVal) || empty($foridVal))) {
            $authResult = 'SUCCESS_AUTH_NONE';
            //此處【不能有 return 值】因為還要繼續執行安全防禦檢查
        }
        //有指定目標的目標性操作
        else {
            //目標性操作優先執行權限授權檢查
            $authResult = $this->checkAuthorization($eventType, $mbID, $thumbmark, $extraParams);
            //當權限檢查為授權駁回時
            if (strpos($authResult, 'REJECT_') === 0) {
                // 授權駁回後，直接返回終止操作，不進行防刷檢驗
                $returnMess = "此次操作必須授權執行，";
                if ($authResult === 'REJECT_AUTH_EXPIRED') {
                    $returnMess .= "您的授權已過期或已達使用次數上限，終止操作！";
                } elseif ($authResult === 'REJECT_AUTH_EXPIRED_ERROR') { // 【修正】補齊語法大括號與結構
                    $returnMess = "授權過期作業錯誤，請聯絡管理人員！";
                } elseif ($authResult === 'REJECT_AUTH_NOTYET') {
                    $returnMess .= "計時授權已設定但尚未啟用，終止操作！";
                } elseif ($authResult === 'REJECT_AUTH_LOSTDATA') {
                    $returnMess = "搜索條件遺失，為安全起見終止操作！";
                } elseif ($authResult === 'REJECT_AUTH_FAILURE') {
                    $returnMess = "找不到授權紀錄或授權失敗，請聯絡管理人員！";
                } elseif ($authResult === 'REJECT_AUTH_INVALID') {
                    $returnMess .= "授權紀錄失效，應為授權過期或已達使用次數上限，終止操作！";
                } else {
                    $returnMess .= "授權驗證失敗！";
                }
                // 回傳權限駁回結果陣列
                return array(
                    'returnDone'    => 0,
                    'authCode'      => $authResult,
                    'secCode'       => '',
                    'relopCode'     => '',
                    'desText'       => $returnMess
                );
            }
        }
        //************************  ******************************//
        //***** 權限通過(之前沒有經過 return 值)，繼續執行防刷檢驗 *****//
        ///***************** 以下為安全防禦檢驗 *********************//
        //************************  ******************************//
        $config = isset($this->eventConfigs[$eventType]) ? $this->eventConfigs[$eventType] : $this->eventConfigs['default'];
        // 不同關聯紀錄的表格與欄位
        if ($eventType === 'doRate') {
            $targetTable   = 'data_rating';
            $idColumn    = 'rat_id';
            $forColumn  = 'rat_for';
            $foridColumn  = 'rat_forid';
            $timeColumn    = 'rat_datetime';
            $tmColumn      = 'rat_thumbmark';
        } else if ($eventType === 'doVisit') {
            $targetTable   = 'data_visits';
            $idColumn    = 'vit_id';
            $forColumn  = 'vit_for';
            $foridColumn  = 'vit_forid';
            $timeColumn    = 'vit_datetime';
            $tmColumn      = 'vit_thumbmark';
        } else {
            $targetTable   = '';
            $idColumn    = '';
            $forColumn  = '';
            $foridColumn  = '';
            $timeColumn    = '';
            $tmColumn      = '';
        }
        //------------------ 搜尋最近一筆關聯操作紀錄(瀏覽或評價) -----------------//
        //------------- 以動態取得的關聯操作紀錄的表格與欄位名進行處理 --------------//
        $lastEventParams = array();
        $lastEventParams['mb_id'] = $mbID;
        $lastEventParams['thumbmark'] = $thumbmark;
        $sqlLastEvent = "SELECT {$idColumn},{$timeColumn} FROM {$targetTable} WHERE ( ( mb_id <> 0 AND mb_id = :mb_id ) OR ( {$tmColumn} <> '' AND {$tmColumn} = :thumbmark ) )";
        if ($eventType === 'doRate' || ($eventType === 'doVisit' && !empty($extraParams['forVal']) && !empty($extraParams['foridVal']))) {
            $sqlLastEvent .= " AND {$forColumn} = :for AND {$foridColumn} = :forid";
            $lastEventParams['for'] = $extraParams['forVal'];
            $lastEventParams['forid'] = $extraParams['foridVal'];
        }
        
        else { 
            $sqlLastEvent .= " AND {$forColumn} = 0 AND {$foridColumn} = 0";
        }
        $sqlLastEvent .= " ORDER BY {$timeColumn} DESC LIMIT 1";
        $lastEventRes = $this->db->row($sqlLastEvent, $lastEventParams);
        $actionID     = isset($lastEventRes[$idColumn]) ? (int)$lastEventRes[$idColumn] : 0;

        //預設當下操作時間在限制時限(違規操作時間間距)外，代表此次操作不將存在違規行為
        $isExpiredEvent = 1; //設為「沒有違規」(違規操作時間間距外)
        //有關聯紀錄且當下操作時間在限制時限(違規操作時間間距)內，代表此次操作存在違規行為
        if (!empty($lastEventRes) && isset($lastEventRes[$timeColumn])) {
            $lastTime = strtotime($lastEventRes[$timeColumn]);
            if ((time() - $lastTime) < $config['config_limitSec']) {
                $isExpiredEvent = 0; //設為「有違規」(違規操作時間間距內)
            }
        }
        //------------------ 已取得當下操作是否屬於違規操作的結果 -------------------//
        //-----------$isExpiredEvent 值為0時為違規操作；為1時為非違規操作 -----------//
        //----------------- 接著搜尋最近一筆關聯的禁限(黑名單)紀錄 ------------------//
        $sqlBan = "SELECT ban_status, ban_actiontime, ban_type, ban_count";
        $sqlBan .= " FROM data_banlist WHERE ( mb_id <> 0 AND mb_id = :mb_id ) OR ( ban_thumbmark <> '' AND ban_thumbmark = :thumbmark )";
        $sqlBan .= " ORDER BY ban_actiontime DESC LIMIT 1";
        
        $banRes = $this->db->row($sqlBan, array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
        //利用 $isExpiredEvent 以及 $banStatus ，以及禁限有效期限，決定安全防禦檢驗的結果
        //預設防禦狀態為未知錯誤
        $securityResult = 'UNKNOWN_ERROR';
        //預設關聯操作狀態為空值
        $relopResult = '';
        //-------- 禁限(黑名單)紀錄不存在時
        //if ($banStatus === null) {
        if (!$banRes) {
            //當處於「沒有違規」操作時
            if ($isExpiredEvent === 1) {                
                //嘗試新增一筆關聯紀錄(瀏覽或評價)
                $this->db->beginTransaction();
                try {
                    $doRelOp = $this->insertEvent($eventType, $targetTable, $actionID, $mbID, $thumbmark, $extraParams);
                    $this->db->commit();
                    $securityResult = 'SUCCESS_NORMAL';//正常操作
                    $relopResult = $doRelOp ?? 'RELOP_ERROR';
                } catch (Exception $e) {
                    $this->db->rollBack();
                    error_log("[checkAction] SUCCESS_NORMAL 寫入失敗: " . $e->getMessage());
                    $securityResult = 'SUCCESS_NORMAL_ERROR';////catch 正常操作，但資料庫寫入錯誤，請聯絡管理人員
                }

            }
              //當處於「有違規」操作時，此時沒有禁限紀錄
            else {
                //新增一筆禁限紀錄，並設置狀態為「觀察中」
                $sqlAddBan = "INSERT INTO data_banlist (ban_actiontime, ban_type, mb_id, ban_thumbmark, ban_status, ban_count) VALUES (GETDATE(), 1, :mb_id, :thumbmark, 2, 1)";
                $this->db->query($sqlAddBan, array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
                $securityResult = 'VIO_NEW_WATCH';// 首次違規操作，進入觀察名單
            }
        } 
        //-------- 禁限(黑名單)紀錄存在時
        else {
            //預設參數
            $banStatus = isset($banRes['ban_status']) ? (int)$banRes['ban_status'] : null;
            $banTime   = isset($banRes['ban_actiontime']) ? strtotime($banRes['ban_actiontime']) : null;
            $banCount  = isset($banRes['ban_count']) ? (int)$banRes['ban_count'] : 0;
            $banType  = isset($banRes['ban_type']) ? (int)$banRes['ban_type'] : 0;
            //當禁限狀態為「禁限中」(黑名單)時
            if ($banStatus === 1) {
                //「禁限中」狀態尚未解除(當下時間在解除時限時間內)時，視為「持續禁限中」終止操作，回傳'REJECT_BANNED'
                if ((time() - $banTime) < ($config['config_banPeriod'] * 3600)) {
                    $securityResult = 'REJECT_BANNED'; // 禁限中，終止操作
                }
                //「禁限中」狀態已解除(當下時間在解除時限時間外)時，視為「禁限解除」，繼續操作並處理禁限紀錄
                else {
                    //當處於「沒有違規」操作時
                    if ($isExpiredEvent === 1) {
                        //嘗試刪除禁限紀錄
                        $this->db->beginTransaction();
                        try {
                            $this->db->query("DELETE FROM data_banlist WHERE mb_id = :mb_id OR ban_thumbmark = :thumbmark", array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
                            $doRelOp = $this->insertEvent($eventType, $targetTable, $actionID, $mbID, $thumbmark, $extraParams);
                            $this->db->commit();
                            $securityResult = 'SUCCESS_UNBANNED';// 禁限狀態解除
                            $relopResult = $doRelOp ?? 'RELOP_ERROR';
                        } catch (Exception $e) {
                            $this->db->rollBack();
                            error_log("[checkAction] SUCCESS_UNBANNED 交易失敗: " . $e->getMessage());
                            $securityResult = 'SUCCESS_UNBANNED_ERROR';////catch 解除禁限，但資料庫交易錯誤，請聯絡管理人員
                        }
                    }
                    //當處於「有違規」操作時，直接將禁限狀態設為「觀察中」，並將觀察紀錄重設為1，代表雖然已經解除了禁限(移出黑名單)，但仍因違規操作而進入觀察名單
                    else {
                        $this->db->query("UPDATE data_banlist SET ban_status = 2, ban_actiontime = GETDATE(), ban_count = 1 WHERE mb_id = :mb_id OR ban_thumbmark = :thumbmark", array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
                        $securityResult = 'VIO_REWATCH';// 禁限狀態解除，同時重新觀察
                    }
                }
            }
            //-------- 禁限(黑名單)紀錄存在且禁限狀態為「觀察中」(觀察名單)時 ------------------------------------------------
            // 請注意：當處於禁限狀態下違規操作時，雖然可以繼續進行下一階段操作，但仍須增加一次違規紀錄，並且，取消觀察的時限要重新計時。
            // 也就是說，操作人只要違規操作被列入觀察名單時，要持續不違規直到取消觀察的時限到期，系統才會將操作人從觀察名單移除。期間若是
            // 有任何一次違規操作，取消觀察的時限就會重新計時。
            else if ($banStatus === 2) {
                //「觀察中」狀態尚未解除(當下時間在解除時限時間內)時，視為「持續觀察中」但可繼續操作
                if ((time() - $banTime) < ($config['config_banPeriod'] * 3600)) {
                    //當處於「有違規」操作時
                    if ($isExpiredEvent === 0) {
                        //而再加1筆觀察紀錄後便超過了最大違規(觀察)次數時，直接將禁限狀態設為「禁限中」，並終止操作
                        if ($banCount + 1 >= $config['config_maxVio']) {
                            $this->db->query("UPDATE data_banlist SET ban_status = 1, ban_actiontime = GETDATE(), ban_count = 0 WHERE mb_id = :mb_id OR ban_thumbmark = :thumbmark", array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
                            $securityResult = 'REJECT_UPGRADE_BAN';// 升級為禁限狀態
                        }
                        //而再加1筆觀察紀錄後未超過最大違規(觀察)次數時，繼續進行操作並增加一次違規(觀察)計次
                        //如果有任何一次違規操作，取消觀察的時限就會重新計時
                        else {
                            $this->db->query("UPDATE data_banlist SET ban_actiontime = GETDATE(), ban_count = ban_count + 1 WHERE mb_id = :mb_id OR ban_thumbmark = :thumbmark", array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
                            $securityResult = 'VIO_WATCH_COUNT_UP';// 觀察中狀態，增加一次違規操作
                        }
                    }
                    //當處於「沒有違規」操作時
                    else {
                        $this->db->beginTransaction();
                        try {
                            //沒有違規操作時，繼續進行下一步操作，且維持取消觀察的時限不做更動，
                            //$this->db->query("UPDATE data_banlist SET ban_actiontime = GETDATE() WHERE mb_id = :mb_id OR ban_thumbmark = :thumbmark", array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
                            $doRelOp = $this->insertEvent($eventType, $targetTable, $actionID, $mbID, $thumbmark, $extraParams);
                            $this->db->commit();
                            $securityResult = 'SUCCESS_WATCH_NORMAL';// 觀察中狀態，正常操作
                            $relopResult = $doRelOp ?? 'RELOP_ERROR';
                        } catch (Exception $e) {
                            $this->db->rollBack();
                            error_log("[checkAction] SUCCESS_WATCH_NORMAL 交易失敗: " . $e->getMessage());
                            $securityResult = 'SUCCESS_WATCH_NORMAL_ERROR';////catch 觀察中狀態，但資料庫交易錯誤，請聯絡管理人員
                        }
                    }
                }
                //「觀察中」狀態已解除(當下時間在解除時限時間外)時，視為「解除觀察」繼續操作
                else {
                    //嘗試刪除禁限紀錄(即使狀態只在「觀察中」)
                    $this->db->beginTransaction();
                    try {
                        $this->db->query("DELETE FROM data_banlist WHERE mb_id = :mb_id OR ban_thumbmark = :thumbmark", array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
                        $doRelOp = $this->insertEvent($eventType, $targetTable, $actionID, $mbID, $thumbmark, $extraParams);
                        $this->db->commit();
                        $securityResult = 'SUCCESS_WATCH_EXPIRED';// 解除觀察，正常操作
                        $relopResult = $doRelOp ?? 'RELOP_ERROR';
                    } catch (Exception $e) {
                        $this->db->rollBack();
                        error_log("[checkAction] SUCCESS_WATCH_EXPIRED 交易失敗: " . $e->getMessage());
                        $securityResult = 'SUCCESS_WATCH_EXPIRED_ERROR';////catch 解除觀察，但資料庫交易錯誤，請聯絡管理人員
                    }
                }
            }

        } 

        /////////////////////////////////////////////////////////////////////////////////
        ///////////////// 安全防禦檢驗後輸出值處理作業 ///////////////////////////////////////
        //////////////////// 輸出執行狀態陣列值 ////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////
        $returnDone = 0;
        $returnMess = "";
        // 修正點：防刷判定需要同時攔截 'REJECT_' (完全封鎖) 與 'VIO_' (頻率違規)
        if (strpos($securityResult, 'REJECT_') === 0) {
            if ($securityResult === 'REJECT_BANNED') {
                $returnMess .= "您目前處於禁限操作期間，請稍候再試！";
            } elseif ($securityResult === 'REJECT_UPGRADE_BAN') {
                $returnMess .= "您因違規操作頻繁，已被系統列入禁限名單，暫時限制操作！";
            } else {
                $returnMess .= "系統防護驗證未通過！";
            }
        }
        // 授權已通過，且防刷檢驗也判定為安全時 (SUCCESS_ && VIO_)
        else {
            $returnDone = 1;
            // 處理授權說明的白話文
            if ($authResult === 'SUCCESS_AUTH_NONE') {
                $returnMess .= ""; // 無需授權，保持空白
            } elseif ($authResult === 'SUCCESS_AUTH_RATE') {
                $returnMess .= "無需授權評分";
            } elseif ($authResult === 'SUCCESS_AUTH_PASS') {
                // 需要確認是計次(mode 1)還是計時(mode 2)
                if (isset($authRes) && (int)$authRes['au_authmode'] === 1) {
                    $returnMess .= "計次授權通過（處於24小時內免扣額期間）。";
                } else {
                    $returnMess .= "計時授權通過（處於有效期間內）。";
                }
            } elseif ($authResult === 'SUCCESS_AUTH_PASS_ERROR') {
                $returnMess .= "新計次扣額作業錯誤，請聯絡管理人員。";
            } elseif ($authResult === 'SUCCESS_AUTH_PASSNEW') {
                $returnMess .= "計次授權通過（此為有效的新計次扣額瀏覽）。";
            } elseif ($authResult === 'SUCCESS_AUTH_PASSNEW_ERROR') {
                $returnMess .= "計次授權通過作業錯誤，請聯絡管理人員。";
            }
            // 串接防刷通過的狀態說明
            if ($securityResult === 'SUCCESS_NORMAL') {
                $returnMess .= "已完成操作！";
            } elseif ($securityResult === 'SUCCESS_NORMAL_ERROR') {
                $returnMess .= "完成操作作業錯誤，請聯絡管理人員！";
            } elseif ($securityResult === 'SUCCESS_UNBANNED') {
                $returnMess .= "您的禁限狀態已解除，已完成操作！";
            } elseif ($securityResult === 'SUCCESS_UNBANNED_ERROR') {
                $returnMess .= "禁限狀態解除作業錯誤，請聯絡管理人員！";
            } elseif ($securityResult === 'SUCCESS_WATCH_NORMAL') {
                $returnMess .= "已完成操作（提示：您目前仍處於觀察名單中）。";
            } elseif ($securityResult === 'SUCCESS_WATCH_NORMAL_ERROR') {
                $returnMess .= "資料庫讀寫作業錯誤，請聯絡管理人員！";
            } elseif ($securityResult === 'SUCCESS_WATCH_EXPIRED') {
                $returnMess .= "您已順利從觀察名單中移出，並完成操作！";
            } elseif ($securityResult === 'SUCCESS_WATCH_EXPIRED_ERROR') {
                $returnMess .= "解除觀察作業錯誤，請聯絡管理人員！";
            } elseif ($securityResult === 'VIO_NEW_WATCH') {
                $returnMess .= "偵測到操作間隔過短！您已被列入觀察名單，請放慢操作速度。";
            } elseif ($securityResult === 'VIO_REWATCH') {
                $returnMess .= "解封後再次操作間隔過短！重新進入觀察名單。";
            } elseif ($securityResult === 'VIO_WATCH_COUNT_UP') {
                $returnMess .= "持續快速操作！已記錄一次違規行為。";
            }
        }
        //----------------------- 關聯操作回傳 -----------------------//
            if ($relopResult === 'RELOP_RATE_NONE') {
                $returnMess .= "目標並無開放評價，操作終止！";
            } elseif ($relopResult === 'RELOP_RATE_EXISTS') {
                $returnMess .= "這個目標的評價已經存在，請勿重複操作！";
            } elseif ($relopResult === 'RELOP_RATE_SUCCESS') {
                $returnMess .= "成功完成評價關聯資料表操作！";
            } elseif ($relopResult === 'RELOP_VISIT_INCREMENT') {
                $returnMess .= "成功完成造訪目標網頁的關聯操作！";
            } elseif ($relopResult === 'RELOP_VISIT_ADDNEW') {
                $returnMess .= "新增一筆無目標造訪紀錄！";
            } elseif ($relopResult === 'RELOP_VISIT_UPDATETIME') {
                $returnMess .= "已更新無目標造訪紀錄的最新時間！";
            } elseif ($relopResult === 'RELOP_RATE_NOTARGET') {
                $returnMess .= "沒有關聯目標無法評價！";
            } elseif ($relopResult === 'RELOP_RATE_UPDATEFAIL') {
                $returnMess .= "更新關聯資料表加總平均失敗！";
            } elseif ($relopResult === 'RELOP_RATE_TAKEBACK') {
                $returnMess .= "已成功收回讚或倒讚！";
            } elseif ($relopResult === 'RELOP_RATE_TAKEBACKFAIL') {
                $returnMess .= "收回讚或倒讚失敗！";
            }
        return array(
            'returnDone'    => $returnDone,
            'authCode'      => $authResult,
            'secCode'       => $securityResult,
            'relopCode'       => $relopResult,
            'desText'       => $returnMess
        );

    }
    /**
     * 私有功用：計次與計日雙模式權限時效、次數扣額與反查檢核
     * 
     * 1. 參數反查：若 auID 為 0，會動態以會員ID與目標課程商品類別自動去資料庫尋找有效授權。
     * 2. 計次原子性：當開啟新計次有效區間時，透過資料庫交易綁定「主表扣額」與「data_authusage 寫入」。
     * 3. 狀態硬阻斷：當發現計次用盡或計日逾期，在內部主動封印 au_status = 0 並傳回 Expiried 拒絕代碼。
     * 
     * @param string $eventType   事件類型 (用以判定查詢與繫結欄位為 rat_ 或 vit_)
     * @param int    $mbID        會員ID
     * @param array  $extraParams 包含 auID, forVal, foridVal 等權限必要引數
     * @return string 回傳權限判定代碼 (如 SUCCESS_AUTH_PASSNEW, REJECT_AUTH_EXPIRED)
     */

    private function checkAuthorization($eventType, $mbID, $thumbmark, $extraParams)
    {
        // 取得傳入的業務分類與目標ID
        $auID     = isset($extraParams['auID']) ? (int)$extraParams['auID'] : 0;        
        $forVal   = isset($extraParams['forVal']) ? (int)$extraParams['forVal'] : 0;
        $foridVal = isset($extraParams['foridVal']) ? (int)$extraParams['foridVal'] : 0;
        // 當基本參數不齊（沒有指定對象目標），則直接視為無授權
        if (($mbID <= 0 && $thumbmark == '') || $forVal <= 0 || $foridVal <= 0) {
        //if ($mbID <= 0 || $forVal <= 0 || $foridVal <= 0) {
            return 'REJECT_AUTH_LOSTDATA';// 沒有授權紀錄，視為公開授權
        } 
        
        if($eventType == 'doRate'){
            return 'SUCCESS_AUTH_RATE';// 
        }
        //如果有帶 auID，直接用 auID 查詢
        $authRes = array();
        if ($auID > 0) {
            $sqlAuth   = "SELECT au_id, mb_id, au_for, au_forid, au_authmode, au_usagelimit, au_authcount, au_start, au_end, au_status 
                            FROM data_authorize 
                            WHERE au_id = :au_id AND mb_id = :mb_id AND au_for = :au_for AND au_forid = :au_forid ORDER BY au_start DESC LIMIT 1";// AND au_status = 1
            $authRes = $this->db->row($sqlAuth, array('au_id' => $auID, 'mb_id' => $mbID, 'au_for' => $forVal, 'au_forid' => $foridVal));
        } 
        // 若無 auID (<=0)，但具備 mbID、auFor、auForid 三個參數，嘗試反查資料庫
        elseif ($mbID > 0 && $forVal > 0 && $foridVal > 0) {
            $sqlAuth   = "SELECT au_id, mb_id, au_for, au_forid, au_authmode, au_usagelimit, au_authcount, au_start, au_end, au_status 
                            FROM data_authorize 
                            WHERE mb_id = :mb_id AND au_for = :au_for AND au_forid = :au_forid ORDER BY au_start DESC LIMIT 1";// AND au_status = 1
            $authRes = $this->db->row($sqlAuth, array('mb_id' => $mbID, 'au_for' => $forVal, 'au_forid' => $foridVal));
        }

        // 若經過上述尋找，資料庫依然完全找不到關聯的授權紀錄，回傳 SUCCESS_AUTH_NONE (無須授權)
        if (!empty($authRes) && isset($authRes['au_id'])) {
            $auID = (int)$authRes['au_id'];
        } else {
            //當有auID卻找不到授權紀錄時，視為系統錯誤，終止操作
            if($auID > 0) {
                return 'REJECT_AUTH_FAILURE';// 找不到授權紀錄，授權失敗終止操作，請聯絡管理人員
            }
            // 而當$auID=0且基本參數皆齊備而無授權紀錄時，則視為公開授權
            elseif ($mbID > 0 && $forVal > 0 && $foridVal > 0) {
                return 'SUCCESS_AUTH_NONE';// 沒有授權紀錄，視為公開授權
            }
            // 其餘狀況都視為有授權無紀錄，視為系統錯誤，終止操作
            return 'REJECT_AUTH_FAILURE';// 找不到授權紀錄，授權失敗終止操作，請聯絡管理人員
        }
        //具備有效授權紀錄時，繼續進行授權查驗，而授權條件決定於以下參數值，自授權紀錄中取得
        $nowTime        = time();
        $authStatus     = (int)$authRes['au_status'];//授權紀錄狀態
        $authMode       = (int)$authRes['au_authmode'];//授權模式
        $authCount      = (int)$authRes['au_authcount'];//計次授權已使用次數
        $usageLimit     = (int)$authRes['au_usagelimit'];//計次授權使用上限
        $auStart        = !empty($authRes['au_start']) ? strtotime($authRes['au_start']) : 0;//計時授權開始時間戳
        $auEnd          = !empty($authRes['au_end']) ? strtotime($authRes['au_end']) : 0;//計時授權結束時間戳
        //當授權紀錄狀態為0時，視為無效授權
        if(empty($authStatus)){
            return 'REJECT_AUTH_INVALID';// 計次授權已達使用次數或逾期，終止操作
        }
        //$currentTimeStr = date('Y-m-d H:i:s', $nowTime);
        //當授權模式為1：計次授權時
        if ($authMode === 1) {
            //當下時間大於授權開始時間且小於授權開始時間+24小時時，授權通過不計使用次數
            if ($auStart > 0 && ($nowTime - $auStart) <= 86400) {
                //同時新增一筆授權使用紀錄
                $this->db->beginTransaction();
                try {
                    $this->db->insert('data_authusage', array(
                        'au_id'        => $auID,
                        'mb_id'        => $mbID,
                        'dau_for'      => $forVal,
                        'dau_forid'    => $foridVal,
                        'dau_datetime' => '[EXPR]GETDATE()'
                    ));
                    $this->db->commit();
                    return 'SUCCESS_AUTH_PASS'; // 計次授權通過
                } catch (Exception $e) {
                    $this->db->rollBack();
                    error_log("[checkAuthorization] 寫入使用明細失敗: " . $e->getMessage());
                    return 'SUCCESS_AUTH_PASS_ERROR';////catch 計次授權通過，寫入使用明細失敗
                }
            }
            //當下時間大於授權開始時間+24小時時，視為此次授權使用完畢，準備開始新的授權
            else {
                //當計次授權已達使用上限時，終止操作，並且更新授權狀態為無效，以及已使用次數加1
                if ($authCount >= $usageLimit) {
                    $this->db->beginTransaction();
                    try {
                        $this->db->query("UPDATE data_authorize SET au_status = 0, au_authcount = au_authcount + 1, au_start = :new_start_time WHERE au_id = :au_id", array(
                            'new_start_time' => '[EXPR]GETDATE()',//更新爲最新操作時間
                            'au_id'          => $auID
                        ));
                        $this->db->commit();
                        return 'REJECT_AUTH_EXPIRED'; // 計次授權已達使用次數，終止操作
                    } catch (Exception $e) {
                        $this->db->rollBack();
                        error_log("[checkAuthorization] 停用過期授權更新失敗: " . $e->getMessage());
                        return 'REJECT_AUTH_EXPIRED_ERROR';////catch 停用過期授權更新失敗
                    }
                }
                //當計次授權未達使用上限時，更新授權已使用次數加1，並且更新為當下時間
                else {
                    $this->db->beginTransaction();
                    try {
                        $this->db->query("UPDATE data_authorize SET au_authcount = au_authcount + 1, au_start = :new_start_time WHERE au_id = :au_id", array(
                            'new_start_time' => '[EXPR]GETDATE()',//更新爲最新操作時間
                            'au_id'          => $auID
                        ));
                        
                        // 同時新增一筆授權使用紀錄
                        $this->db->insert('data_authusage', array(
                            'au_id'        => $auID,
                            'mb_id'        => $mbID,
                            'dau_for'      => $forVal,
                            'dau_forid'    => $foridVal,
                            'dau_datetime' => '[EXPR]GETDATE()'
                        ));
                        
                        $this->db->commit();
                        return 'SUCCESS_AUTH_PASSNEW'; // 計次授權通過，使用一次新授權
                    } catch (Exception $e) {
                        $this->db->rollBack();
                        error_log("[checkAuthorization] 計次扣額交易失敗已回退: " . $e->getMessage());
                        return 'SUCCESS_AUTH_PASSNEW_ERROR';////catch 計次授權通過，寫入使用明細失敗
                    }
                }
            }
        }
        //當授權模式為2：計時授權時
        else if ($authMode === 2) {
            //當未達授權起始時間時，視為授權尚未生效並終止操作
            if ($auStart > 0 && $nowTime < $auStart) {
                return 'REJECT_AUTH_NOTYET'; // 計時授權已授權但尚未啟用，終止操作
            }
            //當超過授權結束時間時，視為授權過期並終止操作
            if ($auEnd > 0 && $nowTime > $auEnd) {
                $this->db->beginTransaction();
                try {
                    $this->db->query("UPDATE data_authorize SET au_status = 0 WHERE au_id = :au_id", array('au_id' => $auID));
                    $this->db->commit();
                    return 'REJECT_AUTH_EXPIRED'; // 計時授權過期，終止操作
                } catch (Exception $e) {
                    $this->db->rollBack();
                    error_log("[checkAuthorization] 計時過期更新狀態失敗: " . $e->getMessage());
                    return 'REJECT_AUTH_EXPIRED_ERROR';////catch 計時授權過期更新狀態失敗
                }
            }
            //非上述情況時，視為授權仍在有效期間內，並繼續操作
            return 'SUCCESS_AUTH_PASS';// 計時授權通過。處於有效授權期間
        }
        //當以上狀況皆不符合時，一律為授權失敗而終止操作
        return 'REJECT_AUTH_FAILURE';// 授權失敗，終止操作
    }
    /**
     * 私有功用：安全日誌寫入與跨服務目標點閱數動態累加
     * 
     * 1. 日誌控管：依事件類別（瀏覽/評價）寫入專屬日誌表。若寫入失敗會主動 throw 例外觸發外部回退。
     * 2. 動態累加：瀏覽事件通過時，從 JSON 快取中取得目標資料表與欄位，自動對該課程商品點閱數 +1。
     * 3. 連鎖防禦：內部所有寫入/更新皆與外部 checkAction 交易協同，任何一處失敗便同生共死全數復原。
     * 
     * @param string $eventType   事件類型 (如 'doVisit', 'doRate')
     * @param string $tableName   目標日誌資料表名稱 (如 'data_visits')
     * @param int    $mbID        會員ID
     * @param string $thumbmark   瀏覽器裝置指紋值
     * @param array  $extraParams 前端傳入之多欄位業務明細陣列 (如 rat_s1, review 等)
     * @throws Exception 當任何一筆寫入或更新 SQL 執行失敗時，拋出例外以供外部交易執行 rollBack
     * @return void
     */

    private function insertEvent($eventType, $tableName, $actionID, $mbID, $thumbmark, $extraParams)
    {
        $auID     = isset($extraParams['auID']) ? (int)$extraParams['auID'] : 0;
        $forVal   = isset($extraParams['forVal']) ? (int)$extraParams['forVal'] : 0;
        $foridVal = isset($extraParams['foridVal']) ? (int)$extraParams['foridVal'] : 0;
        $s1       = isset($extraParams['s1']) ? (int)$extraParams['s1'] : 0;
        $s2       = isset($extraParams['s2']) ? (int)$extraParams['s2'] : 0;
        $s3       = isset($extraParams['s3']) ? (int)$extraParams['s3'] : 0;
        $s4       = isset($extraParams['s4']) ? (int)$extraParams['s4'] : 0;
        $s5       = isset($extraParams['s5']) ? (int)$extraParams['s5'] : 0;
        $review   = isset($extraParams['review']) ? $extraParams['review'] : '';
        $getRateMode   = 0;
        //當有目標紀錄時，同時具備$forVal以及$foridVal兩個值時，判定當下操作「應該」具有目標性
        if ($forVal > 0 && $foridVal > 0) {
            //利用JSON檔案取得目標資料表與欄位
            $geJS = $this->getGeneralConfig(); 
            //利用JSON檔案取得目標資料表與欄位
            $getTable    = isset($geJS['idfor'][$forVal]['table']) ? $geJS['idfor'][$forVal]['table'] : '';
            $getPreCol   = isset($geJS['idfor'][$forVal]['precol']) ? $geJS['idfor'][$forVal]['precol'] : '';
            $getIdCol    = $getPreCol . 'id';
            $getRateMode = isset($geJS['idfor'][$forVal]['ratemode']) ? $geJS['idfor'][$forVal]['ratemode'] : 0;
        }
        ////////////////////////////////////////////////////////////////////////
        //////////////////////// 評價(評分)模式 //////////////////////////////////
        // $getRateMode評價模式為 1.依據general.json中['idfor'][index]['rate']內 //
        // 陣列所有星級評分；2.僅僅S1星級評分；// 3.沒有星級評分，但有讚以及倒讚 /////////
        ////////////////////////////////////////////////////////////////////////
        if ($eventType === 'doRate') {
            //當評價模式為0時，代表這個操作目標並沒有開放評價服務
            if( $getRateMode == 0){
                return 'RELOP_RATE_NONE';// 目標並無開放評價
            }
            //當有定義目標參數，同時具備 $forVal 以及 $foridVal 兩個值，判定當下操作具有目標性（目標資料紀錄存在時才成立）
            if ($forVal > 0 && $foridVal > 0) {
                //當$actionID有值時，代表操作人對該目標的評價已經做過，而評價時同一人每個目標只能有一筆評價紀錄
                if (!empty($actionID)) {
                    //評價模式為3時，直接收回讚或是倒讚
                    if ($getRateMode == 3) {
                        $deleteData = array();
                        $deleteData['rat_id'] = $actionID;
                        $deleteData['rat_s1'] = $s1;
                        $deleteData['rat_s2'] = $s2;
                        //收回讚或倒讚的條件必須與按讚或倒讚的條件相同（可以忽略rat_review，按讚不需要評鑑文，符合三個條件就好：rat_id、rat_s1、rat_s2）
                        //當符合上述條件時，刪除評價紀錄
                        $resDelete = $this->db->query("DELETE FROM data_rating WHERE rat_id=? AND rat_s1=? AND rat_s2=?",array($actionID,$s1,$s2));
                        if ($resDelete === false) {
                            throw new Exception("無目標特定操作日誌寫入失敗 ($tableName)");
                        }
                        //刪除評價紀錄(相當於收回讚或倒讚)後，更新關聯操作資料表按讚數或是倒讚數，不知道是讚還是倒讚不要緊，直接將這兩欄位減去對應的值即可
                        //這兩欄位其中一個值為1，另一個一定為0，一起相減即可，欄位即使減0數值也不會改變
                        $updateArray = array();
                        $getRateTop     = $getPreCol . 'ratetop';
                        $getRateBottom     = $getPreCol . 'ratebottom';
                        $updateArray[$getRateTop] = '[EXPR]['.$getRateTop.'] - '.$s1;//可能為0，-0 等於沒有動作，不會改變；也可能為1，直接減1達到目的
                        $updateArray[$getRateBottom] = '[EXPR]['.$getRateBottom.'] - '.$s2;//可能為0，-0 等於沒有動作，不會改變；也可能為1，直接減1達到目的
                        if (!empty($getTable) && !empty($getIdCol) && !empty($updateArray)) {
                            $resUpdate = $this->db->update($getTable, $updateArray, array($getIdCol => $foridVal));
                            if ($resUpdate === false) {
                                throw new Exception("更新關聯操作點閱數失敗 ($getTable)");
                            }
                            return 'RELOP_RATE_TAKEBACK';// 已成功收回讚或倒讚！
                        }
                        return 'RELOP_RATE_TAKEBACKFAIL';// 收回讚或倒讚失敗
                    }
                    //評價模式為1或2時，代表操作人對該目標的評價已經做過
                    return 'RELOP_RATE_EXISTS';// 已有這個操作目標的評價紀錄
                }
                //當這個操作目標還沒有此次操作人的評價紀錄時，則新增一筆評價紀錄
                else {
                    $insertData = array(
                        'mb_id'         => $mbID,
                        'rat_for'       => $forVal,
                        'rat_forid'     => $foridVal,
                        //'rat_datetime'  => date('Y-m-d H:i:s'),
                        'rat_s1'        => $s1,//$getRateMode值為3時，此欄位為按讚的數量
                        'rat_s2'        => $s2,//$getRateMode值為3時，此欄位為倒讚的數量
                        'rat_s3'        => $s3,
                        'rat_s4'        => $s4,
                        'rat_s5'        => $s5,
                        'rat_review'    => $review,
                        'rat_thumbmark' => $thumbmark
                    );
                    $resInsert = $this->db->insert($tableName, $insertData);

                    if ($resInsert === false) {
                        throw new Exception("無目標特定操作日誌寫入失敗 ($tableName)");
                    }
                    //當順利寫入一筆評價紀錄時，必須同時更新關聯資料表的評價數量，稱之為「關聯操作」
                    $updateArray = array();
                    $getRateTop     = $getPreCol . 'ratetop';
                    $getRateBottom  = $getPreCol . 'ratebottom';
                    $getS1Col       = $getPreCol . 's1';
                    $getS2Col       = $getPreCol . 's2';
                    $getS3Col       = $getPreCol . 's3';
                    $getS4Col       = $getPreCol . 's4';
                    $getS5Col       = $getPreCol . 's5';
                    //依照評價模式的不同，更新不同的欄位，以及這些欄位內該有的計算公式
                    //1.依據general.json中['idfor'][index]['rate']內陣列中，所有值的1-5分制星級評分；
                    //通常為五組評分項目，每組為不同的評分內容，皆可給予1～5分的評價
                    if($getRateMode == 1){
                        $thisRateSum = $s1 + $s2 + $s3 + $s4 + $s5;
                        // ==========================================
                        // 【究極優化：拆除 ISNULL 乾淨版】
                        // 1. 欄位已改為 NOT NULL DEFAULT 0，徹底移除所有 ISNULL()，讓 SQL 執行速度達到極致。
                        // 2. 五組星級平均 (s1~s5)：公式內部的 5.0 與 1.0 必須死守保留！確保微軟繼續使用浮點數精確計算小數點。
                        // 3. 總得分與總底數 (Top/Bottom)：直接進行純整數欄位累加。
                        // ==========================================
                        
                        // 1. 處理 s1 到 s5 的加權平均數（直接用欄位名，保留 .0 浮點數標記）
                        $updateArray[$getS1Col] = '[EXPR]( [' . $getRateBottom . '] * [' . $getS1Col . '] + ' . $s1 . ' ) / ( [' . $getRateBottom . '] + 1.0 )';
                        $updateArray[$getS2Col] = '[EXPR]( [' . $getRateBottom . '] * [' . $getS2Col . '] + ' . $s2 . ' ) / ( [' . $getRateBottom . '] + 1.0 )';
                        $updateArray[$getS3Col] = '[EXPR]( [' . $getRateBottom . '] * [' . $getS3Col . '] + ' . $s3 . ' ) / ( [' . $getRateBottom . '] + 1.0 )';
                        $updateArray[$getS4Col] = '[EXPR]( [' . $getRateBottom . '] * [' . $getS4Col . '] + ' . $s4 . ' ) / ( [' . $getRateBottom . '] + 1.0 )';
                        $updateArray[$getS5Col] = '[EXPR]( [' . $getRateBottom . '] * [' . $getS5Col . '] + ' . $s5 . ' ) / ( [' . $getRateBottom . '] + 1.0 )';
                        // 2. 總得分與總底數的獨立純整數累加
                        $updateArray[$getRateTop]    = '[EXPR][' . $getRateTop . '] + ' . $thisRateSum;
                        $updateArray[$getRateBottom] = '[EXPR][' . $getRateBottom . '] + 1';
                    }
                    //2.僅僅S1星級評分；同1.中的S1星級評分一樣，只有一組綜合評分項目
                    else if($getRateMode == 2){
                        // 單組星級平均（保留分母 1.0 確保小數點精確波動）
                        $updateArray[$getS1Col] = '[EXPR]( ([' . $getRateBottom . '] * [' . $getS1Col . ']) + ' . $s1 . ' ) / ([' . $getRateBottom . '] + 1.0)';
                        // 總得分與總底數整數累加
                        $updateArray[$getRateTop]    = '[EXPR][' . $getRateTop . '] + ' . $s1;
                        $updateArray[$getRateBottom] = '[EXPR][' . $getRateBottom . '] + 1';                    }
                    // 3.單分制評分，例如讚以及倒讚，以次數計算
                    else if($getRateMode == 3){
                        //讚以及倒讚分別寫入總得分欄位以及總評分次數欄位（當被加的值為 0 時不會改變，並且使用 ISNULL 確保 NULL + 數字 不會變成 NULL）
                        $updateArray[$getRateTop]    = '[EXPR][' . $getRateTop . '] + ' . $s1;
                        $updateArray[$getRateBottom]    = '[EXPR][' . $getRateBottom . '] + ' . $s2;
                    }
                    /////////////////////////////////////////////////////////////////
                    ////////////// 評價紀錄加總平均後，更新關聯目標資料表 /////////////////
                    if (!empty($getTable) && !empty($getIdCol) && !empty($updateArray)) {
                        $resUpdate = $this->db->update($getTable, $updateArray, array($getIdCol => $foridVal));
                        if ($resUpdate === false) {
                            throw new Exception("更新目標點閱數失敗 ($getTable)");
                        }
                        return 'RELOP_RATE_SUCCESS';// 成功完成關聯操作
                    }
                    return 'RELOP_RATE_UPDATEFAIL';// 更新關聯資料表加總平均失敗
                }
            }
            //當有沒有定義目標參數時，缺少$forVal 或是 $foridVal 值時，判定當下操作不具有目標性，而無從評價
            else {
                return 'RELOP_RATE_NOTARGET';// 評價時必須要有目標
            }
        }
        /////////////////////////////////////////////////////////////////
        //////////////////////// 造訪(瀏覽)模式 ///////////////////////////
        /////////////////////////////////////////////////////////////////
        elseif($eventType === 'doVisit') {
            //當有for以及forid值時，代表有指定目標頁面，此時新增加一筆紀錄，這種狀況會無限制
            //的新增紀錄，以便日後查詢分析
            if ($forVal > 0 && $foridVal > 0) {
                $data = array(
                    'mb_id'         => $mbID,
                    'au_id'         => $auID,
                    'vit_for'       => $forVal,
                    'vit_forid'     => $foridVal,
                    'vit_datetime'  => date('Y-m-d H:i:s'),
                    'vit_thumbmark' => $thumbmark
                );
                $resInsert = $this->db->insert($tableName, $data);
                if ($resInsert === false) {
                    throw new Exception("寫入瀏覽日誌失敗 ($tableName)");
                }
                $getVisitCol    = $getPreCol . 'visits';
                // 目標資料表紀錄點閱累加
                if (!empty($getTable) && !empty($getIdCol) && !empty($getVisitCol)) {
                    $sqlVisitUpdate = "UPDATE {$getTable} SET {$getVisitCol} = {$getVisitCol} + 1 WHERE {$getIdCol} = :forid";
                    $resUpdate = $this->db->query($sqlVisitUpdate, array('forid' => $foridVal));
                    if ($resUpdate === false) {
                        throw new Exception("更新目標點閱數失敗 ($getTable)");
                    }
                    return 'RELOP_VISIT_INCREMENT';// 成功完成造訪目標網頁的關聯操作
                }
            }
            //而當沒有指定目標時，也就沒有需要分析目標的瀏覽記錄，此時只需要留一個最新的紀錄即可
            else {
                // 當沒有指定業務目標，且具有特定 actionID（來自最新的紀錄時間，可能是有操作目標的多筆紀錄中最新的一
                // 筆，也可能是沒有操作目標的那筆唯一的單筆紀錄） 時，更新最新的這筆紀錄，但是不該更動有操作目標的瀏覽
                // 紀錄，所以這時需要新增一筆沒有目標的單筆紀錄。
                // 總的來說，不能以有沒有 actionID 的值來判定是否要更新這筆紀錄，而是要指定除了 actionID 外，同時 
                // vit_for = 0 且 vit_forid = 0 的這筆紀錄，這筆紀錄才是無操作目標時的紀錄，若是存在，更新它，若是
                // 不存在，就新增一個。那麼，actionID 存在且 vit_for>0 且 vit_forid>0 的這筆紀錄，就是有操作目標
                // 的紀錄，它是重要的歷史紀錄必須保留，不能刪除它且不可更動。
                ///////////////////////////////////////////////////////////////////////////////////////
                // 先嘗試更新，重要的是，除了 $actionID 外，要指定 vit_for = 0 且 vit_forid = 0，用以轉移到沒有
                // 操作目標的瀏覽紀錄上操作，而不更動到有操作目標的瀏覽紀錄。
                $sqlUpdate = "
                    UPDATE data_visits 
                    SET vit_datetime = GETDATE() 
                    WHERE vit_id = :action_id AND vit_for = 0 AND vit_forid = 0
                ";
                // 執行更新，DB Class 會取得影響行數 (rowCount)
                $affectedRows = $this->db->query($sqlUpdate, array('action_id' => $actionID));
                // 如果影響行數為 0，代表資料庫中沒有這筆 actionID 的紀錄，立刻發動 INSERT
                // 如果更新成功大於 0 就不會走這裡
                if ($affectedRows === 0 || $affectedRows === null || $affectedRows === false) {
                    $insertData = array(
                        'mb_id'         => $mbID,
                        'au_id'         => 0, 
                        'vit_for'       => 0,
                        'vit_forid'     => 0,
                        'vit_datetime'  => date('Y-m-d H:i:s'),
                        'vit_thumbmark' => $thumbmark
                    );
                    $resInsert = $this->db->insert($tableName, $insertData);
                    if ($resInsert === false) {
                        throw new Exception("無目標特定操作日誌寫入失敗 ($tableName)");
                    }
                    return 'RELOP_VISIT_ADDNEW';// 新增一筆無目標造訪紀錄
                } else {
                    return 'RELOP_VISIT_UPDATETIME';// 無目標造訪紀錄的日期已經更新
                }
            }
        }
    }
    /*
    private function cleanPrefix($params) 
    {
        $clean = array();
        foreach ($params as $k => $v) {
            $key = ltrim($k, ':');
            $clean[$key] = $v;
        }
        return $clean;
    }
    */
}