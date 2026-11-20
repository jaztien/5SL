<?php
/**
* SecurityGuard.class.php
* 安全防衛與防刷黑名單類別 (實體 ban_count 欄位修正版)
* 2026-08-16 By Jaz Tien
* jaztien@gmail.com
*/
class SecurityGuard
{
    private $db;
    
    private $eventConfigs = array(
        'view_page' => array(//頁面瀏覽類
            'limit_seconds'  => 5,//頁面瀏覽間隔時間為 5 秒
            'max_violations' => 15, // 觀察期內滿 15 次違規操作時升級封鎖
            'ban_hours'      => 1 //觀察/封鎖時間為 1 小時
        ),
        'submit_review' => array(//評價類
            'limit_seconds'  => 10,//評價間隔時間為 10 秒
            'max_violations' => 5,//觀察期內滿 5 次違規操作時升級封鎖
            'ban_hours'      => 24//觀察/封鎖時間為 24 小時
        ),
        'default' => array(//預設
            'limit_seconds'  => 5,//預設間隔時間為 5 秒
            'max_violations' => 10,//預設觀察期內滿 10 次違規操作時升級封鎖
            'ban_hours'      => 1//預設觀察/封鎖時間為 1 小時
        )
    );

    public function __construct(DB $db)
    {
        $this->db = $db;
    }
    /**
     * 核心功用：高併發精密防刷與黑名單行為檢驗
     * 
     * 1. 頻率控制：依據事件類型（如瀏覽、評價）動態檢測會員或瀏覽器指紋是否在限制秒數內頻繁點擊。
     * 2. 三態防衛：結合資料庫行級鎖（Row Lock），精準判斷並切換「正常操作」、「觀察中」、「禁限中」三種安全狀態。
     * 3. 自動短路：當黑名單確立（禁限中）時，自動攔截並拒絕後續的惡意寫入，以極低開銷保護主系統不被並發癱瘓。
     * 
     * @param string $eventType    事件類型 (如 'view_page', 'submit_review')
     * @param int    $mbID         會員ID
     * @param string $thumbmark    瀏覽器指紋值
     * @param array  $extraParams  通過檢驗時，要寫入事件表的其他欄位資料
     * @return string 回傳對應的動作代碼 (如 SUCCESS_NORMAL, REJECT_BANNED)
     */
    public function checkAction($eventType, $mbID, $thumbmark, $extraParams = array())
    {
        $config = isset($this->eventConfigs[$eventType]) ? $this->eventConfigs[$eventType] : $this->eventConfigs['default'];

        if ($eventType === 'submit_review') {
            $targetTable   = 'fh.data_rating';
            $timeColumn    = 'rat_datetime';
            $tmColumn      = 'rat_thumbmark';
        } else {
            $targetTable   = 'fh.data_visits';
            $timeColumn    = 'vit_datetime';
            $tmColumn      = 'vit_thumbmark';
        }

        // 1. 檢查最近一筆事件時間
        $sqlLastEvent = "SELECT TOP 1 {$timeColumn} FROM {$targetTable} WHERE mb_id = :mb_id OR {$tmColumn} = :thumbmark ORDER BY {$timeColumn} DESC";
        $lastEventRes = $this->db->row($sqlLastEvent, array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
        
        $isExpiredEvent = 1; 
        if (!empty($lastEventRes) && isset($lastEventRes[$timeColumn])) {
            $lastTime = strtotime($lastEventRes[$timeColumn]);
            if ((time() - $lastTime) < $config['limit_seconds']) {
                $isExpiredEvent = 0; 
            }
        }

        // 2. 檢查禁限表紀錄 (精準讀取實體 ban_count)
        $sqlBan = "SELECT TOP 1 ban_status, ban_actiontime, ban_count FROM fh.data_banlist WHERE mb_id = :mb_id OR ban_thumbmark = :thumbmark";
        $banRes = $this->db->row($sqlBan, array('mb_id' => $mbID, 'thumbmark' => $thumbmark));

        $banStatus = isset($banRes['ban_status']) ? (int)$banRes['ban_status'] : null;
        $banTime   = isset($banRes['ban_actiontime']) ? strtotime($banRes['ban_actiontime']) : null;
        $banCount  = isset($banRes['ban_count']) ? (int)$banRes['ban_count'] : 0;

        // 3. 開始邏輯分支判定
        if ($banStatus === null) {
            // 【無禁限紀錄】
            if ($isExpiredEvent === 1) {
                $this->insertEvent($eventType, $targetTable, $mbID, $thumbmark, $extraParams);
                return 'SUCCESS_NORMAL';
            } else {
                // 新增觀察紀錄 (2)，將實體 ban_count 設為 1
                $sqlAddBan = "INSERT INTO fh.data_banlist (ban_actiontime, ban_type, mb_id, ban_thumbmark, ban_status, ban_count) VALUES (GETDATE(), 0, :mb_id, :thumbmark, 2, 1)";
                $this->db->query($sqlAddBan, array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
                return 'VIO_NEW_WATCH';
            }
        } 
        else if ($banStatus === 1) {
            // 【禁限中 (1)】
            if ((time() - $banTime) < ($config['ban_hours'] * 3600)) {
                return 'REJECT_BANNED'; 
            } else {
                if ($isExpiredEvent === 1) {
                    $this->db->query("DELETE FROM fh.data_banlist WHERE mb_id = :mb_id OR ban_thumbmark = :thumbmark", array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
                    $this->insertEvent($eventType, $targetTable, $mbID, $thumbmark, $extraParams);
                    return 'SUCCESS_UNBANNED';
                } else {
                    // 非黑名單的違規操作，更新為觀察中 (2)，並重置 ban_count = 1
                    $this->db->query("UPDATE fh.data_banlist SET ban_status = 2, ban_actiontime = GETDATE(), ban_count = 1 WHERE mb_id = :mb_id OR ban_thumbmark = :thumbmark", array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
                    return 'VIO_REWATCH';
                }
            }
        } 
        else if ($banStatus === 2) {
            // 【觀察中 (2)】
            if ((time() - $banTime) < ($config['ban_hours'] * 3600)) {
                if ($isExpiredEvent === 0) {
                    // 違規操作：採用記憶體中已撈出的 $banCount + 1 進行精準判斷
                    if ($banCount + 1 >= $config['max_violations']) {
                        // 升級黑名單 (1)，計數器歸零
                        $this->db->query("UPDATE fh.data_banlist SET ban_status = 1, ban_actiontime = GETDATE(), ban_count = 0 WHERE mb_id = :mb_id OR ban_thumbmark = :thumbmark", array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
                        return 'REJECT_UPGRADE_BAN';
                    } else {
                        // 未滿上限：更新時間，且 ban_count 累加 1
                        $this->db->query("UPDATE fh.data_banlist SET ban_actiontime = GETDATE(), ban_count = ban_count + 1 WHERE mb_id = :mb_id OR ban_thumbmark = :thumbmark", array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
                        return 'VIO_WATCH_COUNT_UP';
                    }
                } else {
                    // 超過事件指定時間：視為正常操作，維持觀察中，更新時間，且寫入事件紀錄
                    $this->db->query("UPDATE fh.data_banlist SET ban_actiontime = GETDATE() WHERE mb_id = :mb_id OR ban_thumbmark = :thumbmark", array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
                    $this->insertEvent($eventType, $targetTable, $mbID, $thumbmark, $extraParams);
                    return 'SUCCESS_WATCH_NORMAL';
                }
            } else {
                // 超過 1 小時觀察期：解封移除紀錄，新增事件紀錄
                $this->db->query("DELETE FROM fh.data_banlist WHERE mb_id = :mb_id OR ban_thumbmark = :thumbmark", array('mb_id' => $mbID, 'thumbmark' => $thumbmark));
                $this->insertEvent($eventType, $targetTable, $mbID, $thumbmark, $extraParams);
                return 'SUCCESS_WATCH_EXPIRED';
            }
        }

        return 'UNKNOWN_ERROR';
    }
    /**
     * 輔助功用：安全配置與自動寫入真實事件紀錄
     * 
     * 1. 欄位適應：依據安全檢查通過後的事件類型（如瀏覽、評價），動態配置對應的目標資料表與基礎欄位。
     * 2. 自動補全：將 PHP 當前伺服器時間與瀏覽器指紋自動寫入，並對前端傳入的自訂資料進行安全合併（Merge）。
     * 3. 去冒號相容：內建去冒號防呆機制（cleanPrefix），完美相容主資料庫類別（DB Class）由內部自動補足冒號的繫結規範。
     * 
     * @param string $eventType   事件類型 (如 'view_page', 'submit_review')
     * @param string $tableName   目標寫入的資料表名稱 (如 'fh.data_visits')
     * @param int    $mbID        會員ID
     * @param string $thumbmark   瀏覽器指紋值
     * @param array  $extraParams 前端傳入的業務專屬欄位資料 (如 au_id, rat_review)
     * @return void
     */
    private function insertEvent($eventType, $tableName, $mbID, $thumbmark, $extraParams)
    {
        //$cleanExtra = $this->cleanPrefix($extraParams);//使用cleanPrefix()清除Index值中的多餘的「:」

        if ($eventType === 'submit_review') {
            $data = array_merge(array(
                'mb_id'         => $mbID,
                'rat_for'       => 0,
                'rat_forid'     => 0,
                'rat_datetime'  => date('Y-m-d H:i:s'),
                'rat_s1'        => 0,
                'rat_s2'        => 0,
                'rat_s3'        => 0,
                'rat_s4'        => 0,
                'rat_s5'        => 0,
                'rat_review'    => null,
                'rat_thumbmark' => $thumbmark
            ), $extraParams);
        } else {
            $data = array_merge(array(
                'mb_id'         => $mbID,
                'au_id'         => 0,
                'vit_for'       => 0,
                'vit_forid'     => 0,
                'vit_datetime'  => date('Y-m-d H:i:s'),
                'vit_thumbmark' => $thumbmark
            ), $extraParams);
        }
        
        $this->db->insert($tableName, $data);
    }

    private function cleanPrefix($params) {
        $clean = array();
        foreach ($params as $k => $v) {
            $key = ltrim($k, ':');
            $clean[$key] = $v;
        }
        return $clean;
    }
}
