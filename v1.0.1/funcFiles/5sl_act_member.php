<?php
require_once('../5sladmin/includeFiles/cli.init.inc.php');
require_once('../5sladmin/includeFiles/db.inc.php');
require_once('../5sladmin/includeFiles/common.lib.php');
//////////////////////////////////////////////////////////////////////////////////
//-------------------------------- 基礎傳遞值篩查 --------------------------------//
//////////////////////////////////////////////////////////////////////////////////
$datapass = true;
$action = $_POST['action'] ?? '';
$dataForm = isset($_POST['dataForm']) ? $_POST['dataForm'] : null;

if (empty($action)) {
    echo json_encode(array('success'=>0, 'code'=>'[@-errorcode-@]', 'message'=>'傳遞值 $action 為空白或遺失，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
//////////////////////////////////////////////////////////////////////////////////
//--------------------------- 整合訊息傳遞錯誤結果並輸出 ---------------------------//
//////////////////////////////////////////////////////////////////////////////////
if ($datapass == false) {
    echo json_encode(array('success'=>0, 'code'=>'[@-errorcode-@]', 'message'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
//////////////////////////////////////////////////////////////////////////////////
//----------------------------- 執行 action 操作 ------------------------------//
//////////////////////////////////////////////////////////////////////////////////
$mb_id  = isset($_SESSION['mbID']) ? (int)$_SESSION['mbID'] : 0;
$mb_lvl = isset($_SESSION['mbLevel']) ? (int)$_SESSION['mbLevel'] : 3; // 預設黃金VIP等級3

try {
    $guest_allowed = ['act_member_login', 'act_member_logout', 'act_member_addnew', 'act_cart_add', 'act_toggle_wishlist'];
    if ($mb_id <= 0 && !in_array($action, $guest_allowed, true)) {
        echo json_encode(['success' => 0, 'need_login' => 1, 'code' => '[@-errorcode-@]', 'message' => '登入已逾時，請重新登入會員帳號']);
        exit();
    }
    switch ($action) {
        case 'act_member_login':
            // ==========================================
            // 會員登入驗證區塊 (您目前的暫時回傳測試)
            // ==========================================
            //取得前端傳來的輸入帳號密碼，未定義時預設為空值
            $loginAccount = $_POST['loginAccount'] ?? '';
            $loginPass = $_POST['loginPass'] ?? '';
            $isLine = $_POST['isLine'] ?? 0;
            $isGoogle = $_POST['isGoogle'] ?? 0;
            $lineSUB = $_POST['lineSUB'] ?? '';
            $googleSUB = $_POST['googleSUB'] ?? '';
            $toTable = "";
            $toSUB = "";
            // 1. 安全檢查：同時啟用或同時傳入驗證碼則拒絕（第三方登入與傳統登入共用防錯）
            if (!empty($isLine) && !empty($isGoogle)) {
                echo json_encode(array('success'=>0,'code'=>'[@-errorcode-@]','message'=>'Line 以及 Google 不得同時選擇！請重新登入！！'));
                exit();
            }
            if (empty($isLine) && empty($isGoogle) && !empty($lineSUB) && !empty($googleSUB)) {
                echo json_encode(array('success'=>0,'code'=>'[@-errorcode-@]','message'=>'Line 以及 Google 驗證碼不得同時做為登入驗證使用！第三方登入綁定失敗！！','loginStatus'=>5));
                exit();
            }



            // 2. 主流程：判斷是第三方登入還是傳統帳密登入
            $allowed_3rd_cols = ['mb_linesub', 'mb_googlesub'];

            if (!empty($isLine) || !empty($isGoogle)) {
                $isLineLogin = !empty($isLine);
                $toSUB       = $isLineLogin ? $lineSUB : $googleSUB;
                $targetName  = $isLineLogin ? 'Line' : 'Google';

                if (empty($toSUB)) {
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => "{$targetName} 驗證碼不得為空值！請重新登入！！"]);
                    exit();
                }

                $toTable = $isLineLogin ? 'mb_linesub' : 'mb_googlesub';
                if (!in_array($toTable, $allowed_3rd_cols, true)) {
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '不支援的第三方登入驗證欄位！']);
                    exit();
                }

                $res = $DB->row("SELECT * FROM members WHERE {$toTable}=? AND mb_status<=?", [$toSUB, 6]);
            } else {
                if (empty($loginAccount) || empty($loginPass)) {
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '帳號密碼不得為空值！請重新登入！！']);
                    exit();
                }

                $toTable = !empty($lineSUB) ? 'mb_linesub' : (!empty($googleSUB) ? 'mb_googlesub' : '');
                $toSUB   = !empty($lineSUB) ? $lineSUB : ($googleSUB ?? '');

                $hashAccount = _fn_hash_hmac($loginAccount);
                $res = $DB->row("SELECT * FROM members WHERE mb_account_hash=? AND mb_status<=?", [$hashAccount, 6]);
            }

            ///////////////////////////// 當搜尋出紀錄時 ///////////////////////////
            if ($res) {
                //預設第三方登入綁定狀態為 0
                $subUp = 0;
                //當會員狀態為2「資料完善中」（尚未驗證）時
                if ($res['mb_status']==2) {
                    //設定臨時SESSION
                    $_SESSION['mbID'] = $res['mb_id'];
                    $_SESSION['mbStatus'] = 2;
                    echo json_encode(array('success'=>0,'code'=>'[@-errorcode-@]','message'=>'會員 '.$res['mb_title'].' 尚未完成驗證作業！！','loginStatus'=>2));
                    exit();
                }
                //當會員狀態不為2「資料完善中」時
                else {
                    //當不是使用第三方登入（前端未指定），代表為傳統帳密登入，此時已以帳號搜尋出紀錄，但密碼驗證失敗，拒絕登入
                    if ((empty($isLine) && empty($isGoogle)) && !password_verify($loginPass, $res['mb_password'])) {
                        echo json_encode(array('success'=>0,'code'=>'[@-errorcode-@]','message'=>'密碼錯誤！登入失敗！！','loginStatus'=>4));//
                        exit();
                    }
                    //---------- 到此代表登入成功，檢查此次登入是否夾帶了第三方登入驗證碼，以及第三方登入目標欄位 ----------//
                    //當第三方目標欄位存在，且準備寫入的欄位值不為空值時，代表此次登入是使用第三方登入
                    //此時進行綁定操作(將第三方帳號寫入到會員帳戶指定的欄位)
                    if (!empty($toTable) && !empty($toSUB) && in_array($toTable, ['mb_linesub', 'mb_googlesub'], true)) {
                        $subUp = $DB->update('members', ['mb_updated' => '[EXPR]NOW()', $toTable => $toSUB], ['mb_id' => $res['mb_id']]);
                    }
                    
                    //即已登入成功，取得登入會員的有效點數
                    $nowPoints = !empty($res['mb_id']) ? $DB->getTotalPoints($res['mb_id']) : 0;
                    //定義過期失效時間 24 小時
                    $expireTime = time() + 86400;
                    //當是使用 Line 作為登入驗證時，將輸入帳號轉為解碼後的hash欄位值，否則保持原輸入值
                    //Hint: 此時應已經通過登入驗證，按理說 $loginAccount 的值應該與 _fn_secure_crypto($res['mb_account'], 'de')解碼過後的值一樣
                    //$loginAccount = !empty($isLine) ? _fn_secure_crypto($res['mb_account'], 'de') : $loginAccount;
                    //當使用第三方登入時，若是還未完整填寫個人資料，也就是說帳戶名稱還是空值時，無法將這個空值回傳給前端使用
                    $enTokenAccount = !empty($res['mb_account']) 
                        ? $res['mb_account'] 
                        : ( !empty($res['mb_linesub']) ? _fn_secure_crypto($res['mb_linesub'])
                        : ( !empty($res['mb_googlesub']) ? _fn_secure_crypto($res['mb_googlesub']) : '' ));
                    $deTokenAccount = !empty($res['mb_account']) 
                        ? _fn_secure_crypto($res['mb_account'], 'de') 
                        : ( !empty($res['mb_linesub']) ? $res['mb_linesub'] 
                        : ( !empty($res['mb_googlesub']) ? $res['mb_googlesub'] : '' ));
                    //$loginAccount = !empty($isLine) ? _fn//($res['mb_account']) : $loginAccount;
                    //當是使用 Line 作為登入驗證時，將加密帳號轉為已搜尋資料的值，否則保持原加密過的帳號
                    //$enAccount = !empty($isLine) ? $res['mb_account'] : $enAccount;
                    //將會員ID、未加密登入帳號、過期時間組合成字串並加密
                    $mergeStr = $res['mb_id'].'|'.$deTokenAccount.'|'.$expireTime;//$mergestr = $res['mb_id'].'|'.$loginAccount.'|'.$expireTime;
                    $enTokenStr = _fn_secure_crypto($mergeStr);//$enStr = _fn//_FH_crypto($mergestr, 'en');
                    //將未加密的帳號末四碼取出，做為【個人識別碼】
                    $mbJS=_fn_getCachedJsonData("../5sladmin/jsonFiles/member.json");
                    $buyerCode = substr($deTokenAccount, -4);//$buyerCode = substr($loginAccount, -4);
                    $_SESSION['mbID'] = (int)$res['mb_id'];
                    $_SESSION['mbLevel'] = (int)$res['mb_level'];
                    $_SESSION['mbGrade'] = $mbJS['levels'][(int)$res['mb_level']]['title'] ?? '';
                    $_SESSION['mbTitle'] = $res['mb_title'];
                    $_SESSION['mbGender'] = $res['mb_gender'];
                    $_SESSION['mbEmail'] = !empty($res['mb_email']) ? _fn_secure_crypto($res['mb_email'], 'de') : '';
                    $_SESSION['buyerCode'] = $buyerCode;
                    $_SESSION['mbPoints'] = $nowPoints;
                    $_SESSION['mbStatus'] = (int)$res['mb_status'];
                    $_SESSION['mbAvatar'] = './5sladmin/uploadFiles/mb/mbphoto_' . $res['mb_id'] . '_.jpg?t=' . time();
                    $_SESSION['mbAvatarThumb'] = './5sladmin/uploadFiles/mb/mbphoto_' . $res['mb_id'] . '_thumb.jpg?t=' . time();

                    //寫入在線記錄//FIXME:
                    //$DB->_DB_online_active($res['mb_id']);
                    //寫入操作記錄
                    //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn,'["vari1","vari2",...]');//寫入操作記錄
                    //輸出//BUG:
                    $from3rd = !empty($toTable) ? ($toTable === 'mb_linesub' ? 'Line 登入' : 'Google 登入') : '第三方登入';
                    $Note3rd = '會員 ' . $res['mb_title'] . ' 登入成功';
                    $Note3rd .= (!empty($toTable) && !empty($toSUB) && !empty($subUp)) ? "；並同時綁定{$from3rd}帳號！！" 
                            : ((!empty($isLine) || !empty($isGoogle)) && empty($subUp) ? "；但{$from3rd}帳號綁定失敗！！" : '！！');
                    echo json_encode(array('success'=>1,'code'=>'[@-successcode-@]','message'=>$Note3rd,'loginStatus'=>1));
                    exit();
                }
            } else {
                echo json_encode(array('success'=>0,'code'=>'[@-errorcode-@]','message'=>(!empty($isLine)||!empty($isGoogle) ? '查無相應第三方登入驗證碼的帳戶，請建立一個新帳戶，或是綁定一個已註冊帳戶！' : '無此帳號登入失敗！請聯絡管理人員！！'),'loginStatus'=>!empty($isLine)||!empty($isGoogle) ? 5 : 3));//
                exit();
            }
        break;
        case 'act_member_logout':
            // ==========================================
            // 💡 3. 【實體核心】安全登出清除機制
            // ==========================================
            // 清空記憶體中的所有 Session 變數
            $_SESSION = [];
            // 釋放當前 Session 所註冊的所有變數
            session_unset();
            // 徹底摧毀伺服器端對應的實體 Session 檔案
            session_destroy();
            // 💡 額外保險：同步消除瀏覽器端的 Session Cookie 檔案
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            // 💡 4. 清除完畢後，透過 PHP 後端 header 重新導向回網站首頁
            // 加上 exit 防止後續的程式碼在背景繼續執行，確保資安安全
            echo json_encode([
                'success'=>1, 
                'msgType'=>'error', 
                'msgStr'=>'您已安全登出系統！!'
            ]);
            exit;
        break;

        case "act_member_addnew":
            $userFormARR = $dataForm['userForm'] ?? array();
            $infoFormARR = $dataForm['infoForm'] ?? array();
            $doMailSend = $dataForm['doMailSend'] ?? 0;
            $sendDelay = $dataForm['sendDelay'] ?? 0;
            if (empty($userFormARR)) {
                echo json_encode(array('success'=>0,'code'=>'[@-errorcode-@]','message'=>'主表單傳遞值為空值，系統認定為表單傳送失敗！請聯絡相關管理人員！！'));
                exit();
            }
            //檢查重複帳戶
            $deAccount = trim($userFormARR['mb_account']);
            //$enAccount = _fn//_FH_crypto($deAccount, 'en');
            $enAccount = _fn_secure_crypto($deAccount);
            $enAccountHash = _fn_hash_hmac($deAccount);
            $getUser = $userFormARR['mb_name'];
            //檢查是否有相同帳戶，有同樣的帳戶名稱將被視為重複紀錄，拒絕新增！
            $chkDup = $DB->single("SELECT mb_id FROM members WHERE mb_account_hash=? AND mb_status<=?", array($enAccountHash,6));
            if ($chkDup && $chkDup>0) {
                echo json_encode(array('success'=>'0','code'=>'[@-successcode-@]','message'=>'創建會員失敗！已有相同且有效的紀錄登錄！！','newid'=>0,'newsn'=>''));
                exit();
            }
            //此時已通過重複紀錄檢查
            //將前台傳來的表單值做必要的加密或是新增，並將這些加密欄位替換以及增加到準備寫入資料庫欄位的陣列中
            //使用隨機IV值加密
            $replaceFormARR = array(
                //'mb_published' => $cDateTime,
                'mb_birth' => $userFormARR['mb_birth']!=='' ? $userFormARR['mb_birth'] : null,
                'mb_name' => !empty($getUser) ? _fn_secure_crypto($getUser) : null,
                'mb_add' => !empty($userFormARR['mb_add']) ? _fn_secure_crypto($userFormARR['mb_add']) : null,
                'mb_cellphone' => !empty($userFormARR['mb_cellphone']) ? _fn_secure_crypto($userFormARR['mb_cellphone']) : null,
                'mb_account' => $enAccount,
                'mb_account_hash' => $enAccountHash,
                'mb_password' => password_hash($userFormARR['mb_password'], PASSWORD_DEFAULT),
                'mb_line' => !empty($userFormARR['mb_line']) ? _fn_secure_crypto($userFormARR['mb_line']) : null,
                'mb_google' => !empty($userFormARR['mb_google']) ? _fn_secure_crypto($userFormARR['mb_google']) : null,
                'mb_email' => !empty($userFormARR['mb_email']) ? _fn_secure_crypto($userFormARR['mb_email']) : null,
                'mb_status' => 2
            );
            $userFormARR = array_replace($userFormARR, $replaceFormARR);
            //在 try 之外先宣告主圖以及縮略圖，以及簡訊驗證狀態回傳陣列，以利後續執行時的檢查或RollBack後刪除
            $mainPhotoPath = '';
            $thumbPhotoPath = '';         
            $verifyArr = array();//預設簡訊狀態回傳為空陣列
            //加入 try 語式
            $DB->beginTransaction();
            try {
                //執行寫入
                $ins = $DB->insert('members', $userFormARR);
                if ($ins && $ins > 0) {
                    //將新增的會員紀錄ID寫入進階會員資料表陣列中
                    $mbID = $ins;
                    $infoFormARR['mb_id'] = $mbID;
                    $basePath = "../uploadFiles/mb/";
                    $upExt = 'jpg';
                    $uptoPath = "mbphoto_".$mbID."_";
                    $mainPhotoPath = $basePath.$uptoPath.".".$upExt;
                    $thumbPath = $uptoPath."thumb.".$upExt;
                    $thumbPhotoPath = $basePath.$thumbPath;
                    //執行進階會員資料新增
                    //當進階會員資料新增成功時
                    //--------------------- 附件檔案處理 ---------------------
                    $fileUploadStatus = $_POST['photoUploadStatus'] ?? 0;
                    $photoDone = false;
                    //當會員ID不為空值時，且 photoUploadStatus【是否同時上傳頭像圖檔 0|1】變數 為 1 時
                    if (!empty($mbID) && $fileUploadStatus == 1) {
                        $attachARR = $dataForm['attached'] ?? null;
                        $uContent = $attachARR['src'];
                        if (!empty($uContent)) {
                            $contSplit = explode(";", $uContent);
                            $getDataSplit = explode(",", $contSplit[1]);
                            $uData = base64_decode($getDataSplit[1]);
                            if (file_put_contents($mainPhotoPath, $uData)) {//600*600
                                chmod($mainPhotoPath, 0755);
                                if (_fn_resize_image($mainPhotoPath, $thumbPhotoPath, 100, 100)) {
                                    $photoDone = true;
                                }
                            }
                        }
                    }
                    //--------------------- SMS Send 驗證碼簡訊發送 ---------------------//
                    //預設簡訊狀態回傳為空陣列
                    $verifyArr = array();
                    //預設附加說明為空值
                    $optMessage = '';
                    //當新增的會員資料中帶有Line驗證碼時，附加文字說明為...
                    if (!empty($userFormARR['mb_linesub'])) {
                        $optMessage = '並已同時綁定 Line 帳號。';
                    }
                    //當新增的會員資料中帶有Google驗證碼時，附加文字說明為...
                    if (!empty($userFormARR['mb_googlesub'])) {
                        $optMessage = '並已同時綁定 Google 帳號。';
                    }
                    //當前台傳來的「是否同時發送簡訊驗證碼」值為1時(true)，則執行簡訊驗證碼簡訊發送
                    if (!empty($doMailSend)) {
                        //生成隨機6位數驗證碼
                        $verifyCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);//rand(123456, 999999);
                        //生成隨機2~4位數的 ACS 畫面進階驗證碼
                        $acsCode = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, random_int(2, 4));
                        //生成驗證碼到期時間(當下時間過後的5分鐘+10秒)
                        $expireTime = (new DateTime())->modify('+5 minutes +10 seconds')->format('Y-m-d H:i:s');
                        //傳送電郵驗證碼//JazFIX:正式使用時請取消註解，並刪除 $mailSend = 1;
                        $mailSend = 1;
                        //$SMSsend['smsdone'] = 1;
                        //當電郵發送回傳資料中的「maildone」值為1時，則電郵驗證碼發送成功
                        if (!empty($mailSend['done'])) {
                        //if (!empty($SMSsend['smsdone']) && $SMSsend['smsdone'] == "1") {
                            //新增這組驗證碼到驗證碼資料表
                            $insv = $DB->insert('data_verify_codes', [
                                'mb_id'       => $mbID, // 剛出爐的 ID！
                                'vc_verifycode' => $verifyCode,
                                'vc_acscode' => $acsCode,
                                'vc_expiretime' => $expireTime,
                                'vc_mode' => 1
                            ]);
                            //$SMSsend['verifyCode'] = $verifyCode;
                            //$SMSsend['acsCode'] = $acsCode;
                            //$SMSsend['SMSto'] = $deAccount;
                            //定義簡訊狀態回傳陣列，將簡訊驗證碼資料帶入
                            $verifyArr = $SMSsend;
                            $optMessage = '電子郵箱驗證連結已發送至您的帳戶郵箱，請檢視您的電子郵件，並點擊連結完成驗證作業!!';
                        }
                        //當郵件發送回傳資料中的「maildone」值為0時，則電子郵箱驗證連結發送失敗
                        else {
                            $optMessage = '但電子郵箱驗證連結發送失敗，請重新發送驗證信件，若持續發送失敗，請聯繫客服人員!!';
                        }
                    }
                    //操作記錄處理
                    //------- 這裡要放操作紀錄 -------
                    //
                    //返回
                    $returnArr = array('success' => '1','code' => '[@-successcode-@]','message' => '創建使用者 '. $getUser .' 成功! '.$optMessage,'newid' => $mbID,'photoDone' => $photoDone, 'verify' => $verifyArr);
                } else{
                    $returnArr = array('success' => '0','code' => '[@-errorcode-@]','message' => '創建使用者 '. $getUser .' 基本資料失敗! ','newid' => 0,'photoDone' => $photoDone, 'verify' => $verifyArr);
                }
                $DB->commit();
            } catch (Exception $e) {
                $DB->rollBack();
                // 如果圖像已經生成，就刪除它
                if (!empty($mainPhotoPath) && is_file($mainPhotoPath)) {
                    @unlink($mainPhotoPath);
                }
                if (!empty($thumbPhotoPath) && is_file($thumbPhotoPath)) {
                    @unlink($thumbPhotoPath);
                }
                //
                error_log("[新增使用者] 寫入失敗: " . $e->getMessage());
                $returnArr = array('success' => '0','code' => '[@-errorcode-@]','message' => '創建使用者 '. $getUser .' 失敗! '. $e->getMessage(), 'verify' => $verifyArr);
            }

            echo json_encode($returnArr);
            exit();

        break;
        // =========================================================================
        // 動作 act_update_profile ：更新會員個人資料(強制加密) + 頭像中心裁切雙尺寸 JPG
        // =========================================================================
        case "act_update_profile":
            $mb_name      = trim($_POST['mb_name'] ?? '');
            $mb_gender    = trim($_POST['mb_gender'] ?? 'N');
            $mb_title     = trim($_POST['mb_title'] ?? '');
            $mb_cellphone = trim($_POST['mb_cellphone'] ?? '');
            $mb_birth     = trim($_POST['mb_birth'] ?? '');
            $mb_city      = trim($_POST['mb_city'] ?? '');
            $mb_district  = trim($_POST['mb_district'] ?? '');
            $mb_add       = trim($_POST['mb_add'] ?? '');

            // 1. 必填與格式驗證
            if ($mb_name === '' || $mb_title === '') {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '真實姓名與顯示暱稱為必填欄位']);
                exit;
            }
            if (!in_array($mb_gender, ['M', 'F', 'N'], true)) {
                $mb_gender = 'N';
            }
            if ($mb_birth !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $mb_birth)) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '出生年月日格式須為 yyyy-mm-dd']);
                exit;
            }
            $birth_db = ($mb_birth !== '') ? $mb_birth : null;

            // 2. 處理大頭貼上傳：中心正方形裁切並轉存為 400x400 主圖與 100x100 縮圖 (JPG)
            $main_photo_name  = null;
            $thumb_photo_name = null;

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $file_tmp  = $_FILES['avatar']['tmp_name'];
                $file_size = (int)$_FILES['avatar']['size'];

                if ($file_size > 2 * 1024 * 1024) {
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '大頭貼檔案不可超過 2MB']);
                    exit;
                }

                // 檢查真實圖片類型並建立來源畫布
                $img_info = @getimagesize($file_tmp);
                if ($img_info === false) {
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '無法辨識的圖片檔案格式']);
                    exit;
                }

                $src_w = $img_info[0]; // 取得圖片寬度 (int)
                $src_h = $img_info[1]; // 取得圖片高度 (int)
                $mime  = $img_info['mime'];

                switch ($mime) {
                    case 'image/jpeg':
                        $src_img = @imagecreatefromjpeg($file_tmp);
                        break;
                    case 'image/png':
                        $src_img = @imagecreatefrompng($file_tmp);
                        break;
                    case 'image/webp':
                        $src_img = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file_tmp) : false;
                        break;
                    default:
                        $src_img = false;
                        break;
                }

                if (!$src_img) {
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '僅支援 JPG、PNG 或 WebP 圖片格式']);
                    exit;
                }

                // 計算中心正方形裁切座標（取短邊為基準，避免人物變形）
                $crop_size = min($src_w, $src_h);
                $src_x     = (int)floor(($src_w - $crop_size) / 2);
                $src_y     = (int)floor(($src_h - $crop_size) / 2);

                $upload_dir = '../5sladmin/uploadFiles/mb/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $main_photo_name  = 'mbphoto_' . $mb_id . '_.jpg';
                $thumb_photo_name = 'mbphoto_' . $mb_id . '_thumb.jpg';

                // 輔助閉包：產生指定邊長之白底 JPG（防 PNG/WebP 透明背景轉 JPG 變黑）
                $save_square_jpg = function ($target_size, $dest_path) use ($src_img, $src_x, $src_y, $crop_size) {
                    $dst_img  = imagecreatetruecolor($target_size, $target_size);
                    $white_bg = imagecolorallocate($dst_img, 255, 255, 255);
                    imagefill($dst_img, 0, 0, $white_bg);
                    imagecopyresampled($dst_img, $src_img, 0, 0, $src_x, $src_y, $target_size, $target_size, $crop_size, $crop_size);
                    $saved = imagejpeg($dst_img, $dest_path, 88);
                    unset($dst_img); // 取代已棄用的 imagedestroy()
                    if ($saved && file_exists($dest_path)) {
                        @chmod($dest_path, 0644);
                    }
                    return $saved;
                };

                $ok_main  = $save_square_jpg(400, $upload_dir . $main_photo_name);
                $ok_thumb = $save_square_jpg(100, $upload_dir . $thumb_photo_name);
                unset($src_img); // 取代已棄用的 imagedestroy()

                if (!$ok_main || !$ok_thumb) {
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '頭像裁切儲存失敗，請檢查伺服器目錄權限']);
                    exit;
                }
            }

            // 3. 強制使用 _fn_secure_crypto() 加密三大敏感欄位
            $enc_name      = _fn_secure_crypto($mb_name, 'en');
            $enc_cellphone = _fn_secure_crypto($mb_cellphone, 'en');
            $enc_add       = _fn_secure_crypto($mb_add, 'en');

            // 4. 更新 members 資料表（原生 SQL 一律使用 $DB->query）
            $sql_upd = "
                UPDATE members 
                SET mb_name = ?, mb_gender = ?, mb_title = ?, mb_cellphone = ?, mb_birth = ?, mb_city = ?, mb_district = ?, mb_add = ?
                WHERE mb_id = ?
            ";
            $DB->query($sql_upd, [
                $enc_name, $mb_gender, $mb_title, $enc_cellphone, $birth_db, $mb_city, $mb_district, $enc_add, $mb_id
            ]);

            if ($main_photo_name !== null) {
                $_SESSION['mbAvatar']       = './5sladmin/uploadFiles/mb/' . $main_photo_name . '?t=' . time();
                $_SESSION['mbAvatarThumb'] = './5sladmin/uploadFiles/mb/' . $thumb_photo_name . '?t=' . time();
            }

            // 5. 同步更新 Session 顯示暱稱
            $_SESSION['mbTitle'] = $mb_title;

            echo json_encode([
                'success'    => 1,
                'code'       => '[@-successcode-@]',
                'message'    => '個人資料已加密更新，頭像亦已同步裁切儲存！',
                'avatar_url' => $_SESSION['mbAvatarThumb'] ?? null
            ]);
            exit;
        break;

        //主動變更密碼
        case "act_change_password":
            $current_pwd = $_POST['current_password'] ?? '';
            $new_pwd     = $_POST['new_password'] ?? '';
            $confirm_pwd = $_POST['confirm_password'] ?? '';

            if ($current_pwd === '' || $new_pwd === '' || $confirm_pwd === '') {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '請完整填寫所有密碼欄位']);
                exit;
            }
            if (strlen($new_pwd) < 8) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '新密碼長度至少需 8 個字元']);
                exit;
            }
            if ($new_pwd !== $confirm_pwd) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '兩次輸入的新密碼不一致']);
                exit;
            }
            if ($current_pwd === $new_pwd) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '新密碼不可與目前使用的密碼相同']);
                exit;
            }

            // 1. 查詢會員目前資料庫密碼（請確認密碼欄位名稱為 mb_password 或 mb_password）
            $member = $DB->row("SELECT mb_id, mb_password FROM members WHERE mb_id = ?", [$mb_id]);
            if (!$member) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '找不到會員帳號資料']);
                exit;
            }

            $db_hash = $member['mb_password'] ?? '';
            // 兼容 password_hash 與舊版 md5 / 明碼比對
            $is_valid_old = password_verify($current_pwd, $db_hash)
                         || ($db_hash === md5($current_pwd))
                         || ($db_hash === $current_pwd);

            if (!$is_valid_old) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '目前使用的密碼輸入錯誤，請重新確認']);
                exit;
            }

            // 2. 使用安全雜湊加密新密碼並寫入資料庫
            $new_hash = password_hash($new_pwd, PASSWORD_DEFAULT);
            $DB->query("UPDATE members SET mb_password = ? WHERE mb_id = ?", [$new_hash, $mb_id]);

            echo json_encode([
                'success' => 1,
                'code'    => '[@-successcode-@]',
                'message' => '登入密碼已成功更新！下次登入請使用您的新密碼。'
            ]);
            exit;
        break;
        //忘記密碼找回 (寄發重設驗證信)
        case "act_send_reset_pwd":
            $member = $DB->row("SELECT mb_id, mb_email, mb_title FROM members WHERE mb_id = ?", [$mb_id]);
            if (!$member || empty($member['mb_email'])) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '無法取得您的註冊電子信箱']);
                exit;
            }

            $user_email = _fn_secure_crypto($member['mb_email'], 'de');
            $user_name  = $member['mb_title'] ?: '第五季尊榮會員';

            // 產生 30 分鐘有效的一次性重設 Token（可存入 Session 或資料庫重設欄位）
            $reset_token = bin2hex(random_bytes(24));
            $_SESSION['pwd_reset_token']  = $reset_token;
            $_SESSION['pwd_reset_expire'] = time() + 1800;

            $reset_url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/5sl_reset_pwd.php?token=' . $reset_token;

            // 若您站內已有共用寄信函式（如 _fn_sendMail），可在此呼叫寄出；此處附上標準 PHP mail 備援
            $subject = '【Fifth Season Life 人生第五季】會員密碼重設驗證信';
            $body    = "親愛的 {$user_name} 您好：\n\n請點擊以下連結重設您的登入密碼（連結將於 30 分鐘後失效）：\n{$reset_url}\n\n若非本人操作，請忽略此信件。";
            $headers = "From: noreply@5sl-studio.com\r\nContent-Type: text/plain; charset=UTF-8\r\n";
            @mail($user_email, $subject, $body, $headers);

            echo json_encode([
                'success' => 1,
                'code'    => '[@-successcode-@]',
                'message' => "密碼重設驗證信已寄送至 {$user_email}，請於 30 分鐘內前往收信！"
            ]);
            exit;
        break;
        // 動作 act_read_one ：單則訊息標記為已讀
        case "act_read_one":
            $no_id = (int)($_POST['no_id'] ?? 0);
            if ($no_id > 0) {
                // 利用 UNIQUE KEY (no_id, mb_id) 搭配 INSERT IGNORE 防止重複寫入報錯
                $sql = "INSERT IGNORE INTO fsl_notification_reads (no_id, mb_id) VALUES (?, ?)";
                $DB->query($sql, [$no_id, $mb_id]);
            }
            echo json_encode(['success' => 1, 'code' => '[@-successcode-@]', 'message' => '訊息標記為已讀']);
            exit;
        break;
        //動作 act_read_all ：全部訊息標示為已讀
        case "act_read_all":
            $sql = "
                INSERT IGNORE INTO fsl_notification_reads (no_id, mb_id)
                SELECT n.no_id, ?
                FROM fsl_notifications n
                LEFT JOIN fsl_notification_reads r ON n.no_id = r.no_id AND r.mb_id = ?
                WHERE (n.mb_id = ? OR n.mb_id IS NULL)
                AND n.no_minlvl <= ?
                AND r.nor_id IS NULL
            ";
            $DB->query($sql, [$mb_id, $mb_id, $mb_id, $mb_lvl]);
            echo json_encode(['success' => 1, 'code' => '[@-successcode-@]', 'message' => '全部訊息標示為已讀']);
            exit;
        break;
        // 動作 act_submit_ticket ：送出客服諮詢工單（寫入主表 + 明細表）
        case "act_submit_ticket":
            $category = trim($_POST['category'] ?? '');
            $subject  = trim($_POST['subject'] ?? '');
            $message  = trim($_POST['message'] ?? '');

            if ($category === '' || $subject === '' || $message === '') {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '請完整填寫問題分類、主旨與詳細內容']);
                exit;
            }

            // 產生唯一工單編號（如 TKT-20261116-842）
            $ticket_no = 'TKT-' . date('Ymd') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);

            // 1. 寫入客服工單主表 (spt_status = 2 代表處理中)
            $sql_ticket = "
                INSERT INTO fsl_support_tickets (spt_ticketno, mb_id, spt_category, spt_subject, spt_status)
                VALUES (?, ?, ?, ?, 2)
            ";
            $insert_res = $DB->query($sql_ticket, [$ticket_no, $mb_id, $category, $subject]);
            
            // 取得剛新增的 spt_id（兼容回傳 ID 或需透過 single 查詢的 PDO 封裝）
            $spt_id = is_numeric($insert_res) && $insert_res > 0 
                ? (int)$insert_res 
                : (int)$DB->single("SELECT spt_id FROM fsl_support_tickets WHERE spt_ticketno = ?", [$ticket_no]);

            // 2. 寫入第一則會員提問明細 (spr_sender = 1 代表會員)
            $sql_reply = "
                INSERT INTO fsl_support_replies (spt_id, spr_sender, spr_senderid, spr_message)
                VALUES (?, 1, ?, ?)
            ";
            $DB->query($sql_reply, [$spt_id, $mb_id, $message]);

            echo json_encode([
                'success'    => 1,
                'code'      => '[@-successcode-@]',
                'message'   => '客服工單已成功送出！',
                'ticket_no' => $ticket_no,
                'created'   => date('Y-m-d H:i')
            ]);
            exit;
        break;
        // =========================================================================
        // 動作 act_draw_lottery ：優惠卡片(mode=1) 與 幸運輪盤(mode=2) 統一抽獎引擎
        // =========================================================================
        case "act_draw_lottery":
            $lot_keycode = trim($_POST['lot_keycode'] ?? '');
            $auto_claim  = (int)($_POST['auto_claim'] ?? 0); // 1=抽出後立即自動領收(適合優惠卡片)，0=存入待領取(適合輪盤)

            if ($lot_keycode === '') {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '缺少活動代碼參數']);
                exit;
            }

            // 1. 查詢活動設定與狀態
            $lottery = $DB->row("SELECT * FROM fsl_lotteries WHERE lot_keycode = ? AND lot_status = 1", [$lot_keycode]);
            if (!$lottery) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '此活動目前未開放或已結束']);
                exit;
            }

            $lot_id   = (int)$lottery['lot_id'];
            $lot_mode = (int)$lottery['lot_mode']; // 1=優惠卡片, 2=幸運輪盤
            $now_str  = date('Y-m-d H:i:s');

            // 2. 檢核活動起訖時間與會員等級資格
            if (!empty($lottery['lot_start_at']) && $now_str < $lottery['lot_start_at']) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '活動尚未開始，敬請期待']);
                exit;
            }
            if (!empty($lottery['lot_end_at']) && $now_str > $lottery['lot_end_at']) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '活動已截止，感謝您的參與']);
                exit;
            }
            if ($mb_lvl < (int)$lottery['lot_min_lvl']) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '您的會員等級尚未達到此活動參與資格']);
                exit;
            }

            // 3. 檢核每位會員參加次數上限 (依 lot_limit_period 計算週期)
            $limit_max = (int)$lottery['lot_limit_per_user'];
            if ($limit_max > 0) {
                $period = (int)$lottery['lot_limit_period'];
                $time_cond = "";
                if ($period === 1) {
                    $time_cond = " AND DATE(grec_created) = CURDATE()";
                } elseif ($period === 2) {
                    $time_cond = " AND YEARWEEK(grec_created, 1) = YEARWEEK(CURDATE(), 1)";
                } elseif ($period === 3) {
                    $time_cond = " AND DATE_FORMAT(grec_created, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
                }

                $used_count = (int)$DB->single(
                    "SELECT COUNT(*) FROM fsl_gift_records WHERE mb_id = ? AND grec_source_type = ? AND grec_source_id = ?{$time_cond}",
                    [$mb_id, $lot_mode, $lot_id]
                );

                if ($used_count >= $limit_max) {
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '您本期的參與／領取次數已達上限']);
                    exit;
                }
            }

            $DB->beginTransaction();
            try {
                // 4. 若活動需消耗 PTS 點數，使用 FOR UPDATE 鎖定會員點數列防連點透支
                $cost_pts = (int)$lottery['lot_cost_pts'];
                if ($cost_pts > 0) {
                    $curr_pts = (int)$DB->single("SELECT mb_points FROM members WHERE mb_id = ? FOR UPDATE", [$mb_id]);
                    if ($curr_pts < $cost_pts) {
                        $DB->rollBack();
                        echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => "您的紅利點數不足（需消耗 {$cost_pts} PTS）"]);
                        exit;
                    }
                    $DB->query("UPDATE members SET mb_points = mb_points - ? WHERE mb_id = ? AND mb_points >= ?", [$cost_pts, $mb_id, $cost_pts]);
                    $new_pts = (int)$DB->single("SELECT mb_points FROM members WHERE mb_id = ?", [$mb_id]);
                    $_SESSION['mbPoints'] = $new_pts;

                    $memo = '參與抽獎消耗：' . $lottery['lot_title'];
                    $DB->query(
                        "INSERT INTO fsl_points (mb_id, pt_income, pt_type, pt_for, pt_forid, pt_trade, pt_redeem, pt_freepoints, pt_available, pt_memo, pt_status) VALUES (?, 0, 4, 5, ?, ?, 0, 0, ?, ?, 1)",
                        [$mb_id, $lot_id, -$cost_pts, $new_pts, $memo]
                    );
                }

                // 5. 撈取獎項池中「尚有名額」且「禮品啟用中」的有效格位
                $sql_items = "
                    SELECT li.*, g.gift_title, g.gift_pts, g.gift_valid_days, g.gift_total_quota, g.gift_issued_count
                    FROM fsl_lottery_items li
                    LEFT JOIN fsl_gifts g ON li.gift_id = g.gift_id
                    WHERE li.lot_id = ?
                      AND (li.loti_quota = -1 OR li.loti_quota > li.loti_won_count)
                      AND (li.gift_id IS NULL OR (g.gift_status = 1 AND (g.gift_total_quota = -1 OR g.gift_total_quota > g.gift_issued_count)))
                    ORDER BY li.loti_slot_order ASC
                ";
                $items = $DB->query($sql_items, [$lot_id]);
                if (empty($items)) {
                    $DB->rollBack();
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '活動獎項已全數發放完畢']);
                    exit;
                }

                // 6. 依權重隨機擇一中獎項目
                $total_weight = 0;
                foreach ($items as $it) {
                    $total_weight += max(1, (int)$it['loti_weight']);
                }
                $rand_num   = mt_rand(1, $total_weight);
                $acc_weight = 0;
                $won_item   = $items;

                foreach ($items as $it) {
                    $acc_weight += max(1, (int)$it['loti_weight']);
                    if ($rand_num <= $acc_weight) {
                        $won_item = $it;
                        break;
                    }
                }

                // 7. 累計獎項抽出次數
                $DB->query("UPDATE fsl_lottery_items SET loti_won_count = loti_won_count + 1 WHERE loti_id = ?", [(int)$won_item['loti_id']]);

                // 8. 寫入會員禮品領收子表 (fsl_gift_records)
                $grec_id     = 0;
                $won_gift_id = (int)($won_item['gift_id'] ?? 0);

                if ($won_gift_id > 0) {
                    $valid_days  = (int)($won_item['gift_valid_days'] ?? 30);
                    $expired_at  = ($valid_days > 0) ? date('Y-m-d H:i:s', strtotime("+{$valid_days} days")) : null;
                    $init_status = ($auto_claim === 1) ? 1 : 0;
                    $claimed_at  = ($auto_claim === 1) ? $now_str : null;

                    $sql_grec = "
                        INSERT INTO fsl_gift_records (gift_id, mb_id, grec_source_type, grec_source_id, grec_status, grec_claimed_at, grec_expired_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ";
                    $ins_res = $DB->query($sql_grec, [$won_gift_id, $mb_id, $lot_mode, $lot_id, $init_status, $claimed_at, $expired_at]);
                    $grec_id = is_numeric($ins_res) && $ins_res > 0
                        ? (int)$ins_res
                        : (int)$DB->single("SELECT MAX(grec_id) FROM fsl_gift_records WHERE mb_id = ? AND gift_id = ?", [$mb_id, $won_gift_id]);

                    $DB->query("UPDATE fsl_gifts SET gift_issued_count = gift_issued_count + 1 WHERE gift_id = ?", [$won_gift_id]);

                    $gift_pts = (int)($won_item['gift_pts'] ?? 0);
                    if ($auto_claim === 1 && $gift_pts > 0) {
                        $DB->query("UPDATE members SET mb_points = mb_points + ? WHERE mb_id = ?", [$gift_pts, $mb_id]);
                        $new_pts = (int)$DB->single("SELECT mb_points FROM members WHERE mb_id = ?", [$mb_id]);
                        $_SESSION['mbPoints'] = $new_pts;

                        $memo = '活動獲得：' . ($won_item['gift_title'] ?? $won_item['loti_label']);
                        $DB->query(
                            "INSERT INTO fsl_points (mb_id, pt_income, pt_type, pt_for, pt_forid, pt_trade, pt_redeem, pt_freepoints, pt_available, pt_memo, pt_status) VALUES (?, 1, 2, 4, ?, ?, 0, ?, ?, ?, 1)",
                            [$mb_id, $grec_id, $gift_pts, $gift_pts, $new_pts, $memo]
                        );
                    }
                }

                $DB->commit();
            } catch (Exception $e) {
                $DB->rollBack();
                throw $e;
            }


            echo json_encode([
                'success'    => 1,
                'code'       => '[@-successcode-@]',
                'message'    => '抽獎／領取處理完成！',
                'slot_order' => (int)$won_item['loti_slot_order'],
                'loti_label' => $won_item['loti_label'],
                'gift_id'    => $won_gift_id,
                'gift_title' => $won_item['gift_title'] ?? $won_item['loti_label'],
                'grec_id'    => $grec_id,
                'claimed'    => ($auto_claim === 1) ? 1 : 0,
                'user_pts'   => $_SESSION['mbPoints'] ?? null
            ]);
            exit;
        break;
        // =========================================================================
        // 動作 act_claim_gift ：會員點擊領收待領禮品 (點數入帳 + 解鎖數位商品下載)
        // =========================================================================
        case "act_claim_gift":
            $grec_id = (int)($_POST['grec_id'] ?? 0);
            if ($grec_id <= 0) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '無效的禮品領收編號']);
                exit;
            }

            // 1. 查詢該筆領收紀錄與禮品主檔（限定本人且狀態為 0-待領取）
            $sql_rec = "
                SELECT r.*, g.gift_type, g.gift_title, g.gift_pts, g.gift_discount_usd, g.gift_merch_ids
                FROM fsl_gift_records r
                INNER JOIN fsl_gifts g ON r.gift_id = g.gift_id
                WHERE r.grec_id = ? AND r.mb_id = ?
            ";
            $rec = $DB->row($sql_rec, [$grec_id, $mb_id]);

            if (!$rec) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '找不到該筆禮品紀錄']);
                exit;
            }
            if ((int)$rec['grec_status'] !== 0) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '此禮品已領取過或已失效']);
                exit;
            }
            if (!empty($rec['grec_expired_at']) && date('Y-m-d H:i:s') > $rec['grec_expired_at']) {
                $DB->query("UPDATE fsl_gift_records SET grec_status = 9 WHERE grec_id = ?", [$grec_id]);
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '很抱歉，此禮品已超過領取期限']);
                exit;
            }

            // 2. 檢查禮品是否包含實體商品（若有實體商品，狀態轉為 2-實體寄送中；純點數/數位商品轉為 1-已領取）
            $next_status = 1;
            $digital_files = [];
            $merch_ids_str = trim($rec['gift_merch_ids'] ?? '');

            if ($merch_ids_str !== '') {
                $id_arr = array_filter(array_map('intval', explode(',', $merch_ids_str)));
                if (!empty($id_arr)) {
                    $placeholders = implode(',', array_fill(0, count($id_arr), '?'));
                    $merches = $DB->query(
                        "SELECT merch_id, merch_type, merch_title, merch_download_url, merch_file_format FROM merchandise WHERE merch_id IN ($placeholders)",
                        $id_arr
                    );
                    if (is_array($merches)) {
                        foreach ($merches as $m) {
                            if ((int)$m['merch_type'] === 1) {
                                $next_status = 2; // 內含實體書籍或工具包，標記為待寄送
                            } elseif ((int)$m['merch_type'] === 2 && !empty($m['merch_download_url'])) {
                                $digital_files[] = [
                                    'title'  => $m['merch_title'],
                                    'format' => $m['merch_file_format'],
                                    'url'    => $m['merch_download_url']
                                ];
                            }
                        }
                    }
                }
            }

            // 3. 更新領收紀錄狀態與領取時間
            $DB->query(
                "UPDATE fsl_gift_records SET grec_status = ?, grec_claimed_at = NOW() WHERE grec_id = ? AND grec_status = 0",
                [$next_status, $grec_id]
            );

            // 4. 若禮品內含紅利點數，同步更新 members 總餘額並寫入 mb_points 流水帳子表
            $add_pts = (int)$rec['gift_pts'];
            if ($add_pts > 0) {
                $DB->query("UPDATE members SET mb_points = mb_points + ? WHERE mb_id = ?", [$add_pts, $mb_id]);
                
                // 查詢更新後的最新可用總點數，確保 pt_available 帳務精準
                $new_pts = (int)$DB->single("SELECT mb_points FROM members WHERE mb_id = ?", [$mb_id]);
                if (isset($_SESSION['mbPoints'])) {
                    $_SESSION['mbPoints'] = $new_pts;
                }

                // 寫入點數明細子表 (pt_income=1收入, pt_type=2活動贈點, pt_for=4禮品領取, pt_forid=$grec_id)
                $memo = '領取禮品：' . $rec['gift_title'];
                $sql_mbp = "
                    INSERT INTO fsl_points 
                    (mb_id, pt_income, pt_type, pt_for, pt_forid, pt_trade, pt_redeem, pt_freepoints, pt_available, pt_memo, pt_status)
                    VALUES (?, 1, 2, 4, ?, ?, 0, ?, ?, ?, 1)
                ";
                $DB->query($sql_mbp, [$mb_id, $grec_id, $add_pts, $add_pts, $new_pts, $memo]);
            }

            echo json_encode([
                'success'       => 1,
                'code'          => '[@-successcode-@]',
                'message'       => ($next_status === 2) ? '禮品已領取！實體贈品將盡快為您安排寄出。' : '禮品已成功領取並入帳！',
                'grec_status'   => $next_status,
                'added_pts'     => $add_pts,
                'user_pts'      => $_SESSION['mbPoints'] ?? null,
                'digital_files' => $digital_files
            ]);
            exit;
        break;
        // =========================================================================
        // 動作 act_redeem_code ：輸入活動優惠代碼即時兌換歸戶 (對應 gifts.gift_code)
        // =========================================================================
        case "act_redeem_code":
            $code = strtoupper(trim($_POST['code'] ?? ''));
            if ($code === '') {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '請輸入活動優惠代碼']);
                exit;
            }

            // 1. 查詢對應的禮品設定 (比對 gift_code 且狀態為啟用)
            $gift = $DB->row("SELECT * FROM fsl_gifts WHERE UPPER(gift_code) = ? AND gift_status = 1", [$code]);
            if (!$gift) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => "無效或已停用的優惠代碼「{$code}」"]);
                exit;
            }

            $gift_id     = (int)$gift['gift_id'];
            $target_type = (int)$gift['gift_target_type'];

            // 2. 檢核領取對象資格 (1=指定等級, 2=指定特定會員ID)
            if ($target_type === 1 && $mb_lvl < (int)$gift['gift_target_minlvl']) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '您的會員等級尚未達到此代碼兌換資格']);
                exit;
            }
            if ($target_type === 2) {
                $allow_ids = array_filter(array_map('intval', explode(',', $gift['gift_target_mbids'] ?? '')));
                if (!in_array($mb_id, $allow_ids, true)) {
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '此為特定會員專屬代碼，您不符合兌換對象資格']);
                    exit;
                }
            }

            // 3. 檢核是否超過總發放名額上限
            $total_quota = (int)$gift['gift_total_quota'];
            if ($total_quota !== -1 && (int)$gift['gift_issued_count'] >= $total_quota) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '很抱歉，此優惠代碼名額已全數兌換完畢']);
                exit;
            }

            // 4. 防止同一會員重複兌換相同代碼
            $already_used = (int)$DB->single(
                "SELECT COUNT(*) FROM fsl_gift_records WHERE mb_id = ? AND gift_id = ? AND grec_source_type = 1",
                [$mb_id, $gift_id]
            );
            if ($already_used > 0) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => "您已經兌換過「{$code}」，請勿重複輸入"]);
                exit;
            }

            // 5. 計算到期時間並寫入會員禮品領收子表 (兌換代碼直接設為 1-已領取歸戶)
            $valid_days = (int)$gift['gift_valid_days'];
            $expired_at = ($valid_days > 0) ? date('Y-m-d H:i:s', strtotime("+{$valid_days} days")) : null;
            $expired_fmt = $expired_at ? date('Y-m-d', strtotime($expired_at)) : '永久有效';

            $ins_res = $DB->query(
                "INSERT INTO fsl_gift_records (gift_id, mb_id, grec_source_type, grec_status, grec_claimed_at, grec_expired_at) VALUES (?, ?, 1, 1, NOW(), ?)",
                [$gift_id, $mb_id, $expired_at]
            );
            $grec_id = is_numeric($ins_res) && $ins_res > 0
                ? (int)$ins_res
                : (int)$DB->single("SELECT MAX(grec_id) FROM fsl_gift_records WHERE mb_id = ? AND gift_id = ?", [$mb_id, $gift_id]);

            // 累計禮品總發放數
            $DB->query("UPDATE fsl_gifts SET gift_issued_count = gift_issued_count + 1 WHERE gift_id = ?", [$gift_id]);

            // 6. 若禮品內含紅利點數，同步入帳並寫入 mb_points 流水帳子表
            $add_pts = (int)$gift['gift_pts'];
            $new_pts = null;
            if ($add_pts > 0) {
                $DB->query("UPDATE members SET mb_points = mb_points + ? WHERE mb_id = ?", [$add_pts, $mb_id]);
                $new_pts = (int)$DB->single("SELECT mb_points FROM members WHERE mb_id = ?", [$mb_id]);
                if (isset($_SESSION['mbPoints'])) {
                    $_SESSION['mbPoints'] = $new_pts;
                }

                $memo = '兌換優惠代碼：' . $gift['gift_title'] . " ({$code})";
                $DB->query(
                    "INSERT INTO fsl_points (mb_id, pt_income, pt_type, pt_for, pt_forid, pt_trade, pt_redeem, pt_freepoints, pt_available, pt_memo, pt_status) VALUES (?, 1, 2, 4, ?, ?, 0, ?, ?, ?, 1)",
                    [$mb_id, $grec_id, $add_pts, $add_pts, $new_pts, $memo]
                );
            }

            echo json_encode([
                'success'      => 1,
                'code'         => '[@-successcode-@]',
                'message'      => "序號「{$code}」兌換成功！已為您將「{$gift['gift_title']}」歸戶。",
                'gift_code'    => $code,
                'gift_title'   => $gift['gift_title'],
                'gift_sub'     => $gift['gift_subtitle'] ?? '透過專屬優惠代碼兌換成功，可於結帳或禮品櫃使用。',
                'added_pts'    => $add_pts,
                'discount_usd' => (float)$gift['gift_discount_usd'],
                'expired_str'  => $expired_fmt,
                'new_pts'      => $new_pts
            ]);
            exit;
        break;

        // =========================================================================
        // 動作 act_claim_vip_gift ：領取 VIP 等級專屬每月優惠禮遇 (gifts.gift_target_type = 1)
        // =========================================================================
        case "act_claim_vip_gift":
            $gift_id = (int)($_POST['gift_id'] ?? 0);
            if ($gift_id <= 0) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '無效的 VIP 禮遇項目編號']);
                exit;
            }

            // 1. 查詢該 VIP 專屬禮品設定
            $gift = $DB->row("SELECT * FROM fsl_gifts WHERE gift_id = ? AND gift_target_type = 1 AND gift_status = 1", [$gift_id]);
            if (!$gift) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '找不到此 VIP 專屬禮遇活動或已下架']);
                exit;
            }

            // 2. 檢核會員等級是否達標
            $req_lvl = (int)$gift['gift_target_minlvl'];
            if ($mb_lvl < $req_lvl) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '您的會員等級尚未達到此專屬禮遇解鎖門檻']);
                exit;
            }

            // 3. 檢核總發放名額上限
            $total_quota = (int)$gift['gift_total_quota'];
            if ($total_quota !== -1 && (int)$gift['gift_issued_count'] >= $total_quota) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '很抱歉，本月 VIP 專屬禮遇名額已全數領取完畢']);
                exit;
            }

            // 4. 檢核本月是否已領取過此 VIP 禮遇 (grec_source_type = 5 代表等級/任務專屬領取)
            $claimed_this_month = (int)$DB->single(
                "SELECT COUNT(*) FROM fsl_gift_records WHERE mb_id = ? AND gift_id = ? AND grec_source_type = 5 AND DATE_FORMAT(grec_created, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')",
                [$mb_id, $gift_id]
            );
            if ($claimed_this_month > 0) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '您本月已經領取過此項 VIP 專屬禮遇囉！']);
                exit;
            }

            // 5. 檢查禮品是否內含實體商品（決定領收狀態為 1-已領取 或 2-實體寄送中）
            $next_status = 1;
            $merch_ids_str = trim($gift['gift_merch_ids'] ?? '');
            if ($merch_ids_str !== '') {
                $id_arr = array_filter(array_map('intval', explode(',', $merch_ids_str)));
                if (!empty($id_arr)) {
                    $placeholders = implode(',', array_fill(0, count($id_arr), '?'));
                    $merches = $DB->query("SELECT merch_type FROM merchandise WHERE merch_id IN ($placeholders)", $id_arr);
                    if (is_array($merches)) {
                        foreach ($merches as $m) {
                            if ((int)$m['merch_type'] === 1) {
                                $next_status = 2;
                                break;
                            }
                        }
                    }
                }
            }

            // 6. 寫入會員禮品領收子表 (fsl_gift_records)
            $valid_days = (int)$gift['gift_valid_days'];
            $expired_at = ($valid_days > 0) ? date('Y-m-d H:i:s', strtotime("+{$valid_days} days")) : null;

            $DB->query(
                "INSERT INTO fsl_gift_records (gift_id, mb_id, grec_source_type, grec_status, grec_claimed_at, grec_expired_at) VALUES (?, ?, 5, ?, NOW(), ?)",
                [$gift_id, $mb_id, $next_status, $expired_at]
            );
            $grec_id = (int)$DB->single("SELECT MAX(grec_id) FROM fsl_gift_records WHERE mb_id = ? AND gift_id = ?", [$mb_id, $gift_id]);

            // 累計禮品總發放數
            $DB->query("UPDATE fsl_gifts SET gift_issued_count = gift_issued_count + 1 WHERE gift_id = ?", [$gift_id]);

            // 7. 若內含紅利點數，同步更新 members.mb_points 並寫入 fsl_points 流水帳子表
            $add_pts = (int)$gift['gift_pts'];
            $new_pts = null;
            if ($add_pts > 0) {
                $DB->query("UPDATE members SET mb_points = mb_points + ? WHERE mb_id = ?", [$add_pts, $mb_id]);
                $new_pts = (int)$DB->single("SELECT mb_points FROM members WHERE mb_id = ?", [$mb_id]);
                if (isset($_SESSION['mbPoints'])) {
                    $_SESSION['mbPoints'] = $new_pts;
                }

                $memo = 'VIP 專屬禮遇領取：' . $gift['gift_title'];
                $DB->query(
                    "INSERT INTO fsl_points (mb_id, pt_income, pt_type, pt_for, pt_forid, pt_trade, pt_redeem, pt_freepoints, pt_available, pt_memo, pt_status) VALUES (?, 1, 2, 4, ?, ?, 0, ?, ?, ?, 1)",
                    [$mb_id, $grec_id, $add_pts, $add_pts, $new_pts, $memo]
                );
            }

            echo json_encode([
                'success'    => 1,
                'code'       => '[@-successcode-@]',
                'message'    => "VIP 專屬禮遇「{$gift['gift_title']}」已成功領取並歸戶！",
                'gift_title' => $gift['gift_title'],
                'added_pts'  => $add_pts,
                'new_pts'    => $new_pts
            ]);
            exit;
        break;
        // =========================================================================
        // 動作 act_submit_refund ：送出退換貨與退費申請 (關聯 fsl_orders 與 fsl_invoices)
        // =========================================================================
        case "act_submit_refund":
            $ord_no     = strtoupper(trim($_POST['ord_no'] ?? ''));
            $ref_type   = (int)($_POST['ref_type'] ?? 1);
            $ref_detail = trim($_POST['ref_detail'] ?? '');

            if ($ord_no === '' || $ref_detail === '') {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '請完整填寫訂單編號與詳細原因說明']);
                exit;
            }
            if (!in_array($ref_type, [1, 2, 3], true)) {
                $ref_type = 1;
            }

            // 1. 驗證訂單是否屬於本人且狀態允許申請售後 (2=已付款處理中, 3=實體已出貨, 4=已完成)
            $sql_ord = "
                SELECT o.ord_id, o.ord_no, o.ord_total, o.ord_pts_used, o.ord_status, i.inv_id
                FROM fsl_orders o
                LEFT JOIN fsl_invoices i ON o.ord_id = i.ord_id
                WHERE UPPER(o.ord_no) = ? AND o.mb_id = ?
            ";
            $ord = $DB->row($sql_ord, [$ord_no, $mb_id]);

            if (!$ord) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => "找不到屬於您的訂單編號「{$ord_no}」"]);
                exit;
            }
            if (!in_array((int)$ord['ord_status'], [2, 3, 4], true)) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '此訂單目前狀態無法重複申請退費（可能為待付款、退費中或已取消）']);
                exit;
            }

            $ord_id  = (int)$ord['ord_id'];
            $inv_id  = !empty($ord['inv_id']) ? (int)$ord['inv_id'] : null;
            $ref_no  = 'REF-' . date('Ymd') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);

            // 預設預計退款金額（換貨 ref_type=3 則金額為 0）
            $ref_amount     = ($ref_type === 3) ? 0.00 : (float)$ord['ord_total'];
            $ref_pts_return = ($ref_type === 1) ? (int)$ord['ord_pts_used'] : 0;

            $type_labels = [
                1 => '全額退費申請',
                2 => '部分比例退費申請',
                3 => '實體商品瑕疵換貨'
            ];
            $ref_reason = $type_labels[$ref_type];

            // 2. 寫入 fsl_refunds 退費申請表（原生 SQL 一律使用 $DB->query）
            $sql_ins_ref = "
                INSERT INTO fsl_refunds (ref_no, ord_id, inv_id, mb_id, ref_type, ref_reason, ref_detail, ref_amount, ref_pts_return, ref_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ";
            $DB->query($sql_ins_ref, [
                $ref_no, $ord_id, $inv_id, $mb_id, $ref_type, $ref_reason, $ref_detail, $ref_amount, $ref_pts_return
            ]);

            // 3. 同步將原訂單狀態更新為 5-退費申請中
            $DB->query("UPDATE fsl_orders SET ord_status = 5 WHERE ord_id = ? AND mb_id = ?", [$ord_id, $mb_id]);

            echo json_encode([
                'success'      => 1,
                'code'         => '[@-successcode-@]',
                'message'      => "售後申請單（{$ref_no}）已成功送出！我們將於 3 個工作日內完成審核。",
                'ref_no'       => $ref_no,
                'ord_no'       => $ord['ord_no'],
                'ref_type_txt' => $ref_reason,
                'ref_amount'   => $ref_amount
            ]);
            exit;
        break;
        // =========================================================================
        // 動作 act_reschedule_session ：一對一顧問預約自助改期 (直接換時段 或 另訂時段客服工單)
        // =========================================================================
        case "act_reschedule_session":
            $enr_id      = (int)($_POST['enr_id'] ?? 0);
            $new_sess_raw = trim($_POST['new_sess_id'] ?? '');
            $custom_note = trim($_POST['custom_note'] ?? '');

            if ($enr_id <= 0 || $new_sess_raw === '') {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '請選擇要變更的預約時段或申請方式']);
                exit;
            }

            // 1. 驗證原報名紀錄是否屬於本人且狀態允許改期
            $sql_enr = "
                SELECT e.enr_id, e.course_id, e.sess_id, e.enr_status, c.course_title, s.sess_startdate, s.sess_starttime
                FROM fsl_course_enrolls e
                INNER JOIN courses c ON e.course_id = c.course_id
                INNER JOIN course_sessions s ON e.sess_id = s.sess_id
                WHERE e.enr_id = ? AND e.mb_id = ?
            ";
            $enr = $DB->row($sql_enr, [$enr_id, $mb_id]);
            if (!$enr || in_array((int)$enr['enr_status'], [0, 3], true)) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '找不到可改期的有效預約紀錄']);
                exit;
            }

            // =====================================================================
            // 分支 A：會員選擇「無合適時段，請聯絡另訂時段」(custom_request)
            // =====================================================================
            if ($new_sess_raw === 'custom_request') {
                if ($custom_note === '') {
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '請填寫您方便的期望時段與需求說明']);
                    exit;
                }

                // 1) 將報名紀錄狀態更新為 4-申請改期或延梯中，並寫入備註
                $DB->query(
                    "UPDATE fsl_course_enrolls SET enr_status = 4, enr_note = ? WHERE enr_id = ? AND mb_id = ?",
                    ['[申請另訂時段] ' . $custom_note, $enr_id, $mb_id]
                );

                // 2) 自動在客服工單系統建立一筆「課程與觀看權限」追蹤工單，讓客服後台與訊息中心同步呈現
                $ticket_no = 'TKT-' . date('Ymd') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
                $orig_dt   = $enr['sess_startdate'] . ' ' . substr($enr['sess_starttime'], 0, 5);
                $subj      = "一對一顧問預約另訂時段申請：{$enr['course_title']}";
                $full_msg  = "【系統自動建立之改期協調申請】\n預約項目：{$enr['course_title']}\n原定時段：{$orig_dt}\n會員期望時段與備註：\n{$custom_note}";

                $DB->query(
                    "INSERT INTO fsl_support_tickets (spt_ticketno, mb_id, spt_category, spt_subject, spt_status) VALUES (?, ?, '課程與觀看權限', ?, 2)",
                    [$ticket_no, $mb_id, $subj]
                );
                $spt_id = (int)$DB->single("SELECT spt_id FROM fsl_support_tickets WHERE spt_ticketno = ?", [$ticket_no]);

                if ($spt_id > 0) {
                    $DB->query(
                        "INSERT INTO fsl_support_replies (spt_id, spr_sender, spr_senderid, spr_message) VALUES (?, 1, ?, ?)",
                        [$spt_id, $mb_id, $full_msg]
                    );
                }

                echo json_encode([
                    'success' => 1,
                    'code'    => '[@-successcode-@]',
                    'mode'    => 'custom_request',
                    'message' => "已為您送出另訂時段協調申請（追蹤工單：{$ticket_no}），專屬顧問助理將於 1 個工作日內與您確認新時段！"
                ]);
                exit;
            }

            // =====================================================================
            // 分支 B：會員從既定空檔中直接選擇新時段
            // =====================================================================
            $new_sess_id = (int)$new_sess_raw;
            $old_sess_id = (int)$enr['sess_id'];

            if ($old_sess_id === $new_sess_id) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '您選擇的時段與原本相同，無需變更']);
                exit;
            }

            $new_sess = $DB->row(
                "SELECT s.sess_id, s.sess_startdate, s.sess_starttime, s.sess_duration, s.sess_enrollee, c.course_max
                 FROM course_sessions s
                 INNER JOIN courses c ON s.course_id = c.course_id
                 WHERE s.sess_id = ? AND s.course_id = ? AND s.sess_status = 1",
                [$new_sess_id, (int)$enr['course_id']]
            );
            if (!$new_sess || (int)$new_sess['sess_enrollee'] >= (int)$new_sess['course_max']) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '很抱歉，該時段剛剛已被其他會員預約額滿，請選擇其他時段']);
                exit;
            }

            // 釋放舊時段名額、佔用新時段名額，並更新報名紀錄
            $DB->query("UPDATE course_sessions SET sess_enrollee = GREATEST(0, sess_enrollee - 1) WHERE sess_id = ?", [$old_sess_id]);
            $DB->query("UPDATE course_sessions SET sess_enrollee = sess_enrollee + 1 WHERE sess_id = ?", [$new_sess_id]);
            $DB->query(
                "UPDATE fsl_course_enrolls SET sess_id = ?, enr_status = 1, enr_note = CONCAT(IFNULL(enr_note,''), ' [會員自助改期]') WHERE enr_id = ?",
                [$new_sess_id, $enr_id]
            );

            $week_chars = ['日', '一', '二', '三', '四', '五', '六'];
            $ts_start   = strtotime($new_sess['sess_startdate'] . ' ' . $new_sess['sess_starttime']);
            $ts_end     = $ts_start + ((int)$new_sess['sess_duration'] * 60);
            $new_time_str = date('Y/m/d', $ts_start) . ' (' . $week_chars[(int)date('w', $ts_start)] . ') ' . date('H:i', $ts_start) . ' - ' . date('H:i', $ts_end);

            echo json_encode([
                'success'      => 1,
                'code'         => '[@-successcode-@]',
                'mode'         => 'direct_slot',
                'message'      => "預約時段已成功變更為「{$new_time_str}」！",
                'new_time_str' => $new_time_str
            ]);
            exit;
        break;
        // =========================================================================
        // 動作 act_claim_quest ：領取階段任務獎勵 & 終極達成大禮包 (fsl_quests -> fsl_gifts)
        // =========================================================================
        case "act_claim_quest":
            $quest_id  = (int)($_POST['quest_id'] ?? 0);
            $recv_name = trim($_POST['receiver_name'] ?? '');
            $recv_tel  = trim($_POST['receiver_phone'] ?? '');
            $recv_addr = trim($_POST['receiver_address'] ?? '');

            if ($quest_id <= 0) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '無效的任務編號參數']);
                exit;
            }

            // 1. 查詢任務定義與關聯的 fsl_gifts 獎品設定
            $sql_q = "
                SELECT q.*, g.gift_title, g.gift_pts, g.gift_merch_ids, g.gift_valid_days
                FROM fsl_quests q
                INNER JOIN fsl_gifts g ON q.gift_id = g.gift_id
                WHERE q.quest_id = ? AND q.quest_status = 1
            ";
            $quest = $DB->row($sql_q, [$quest_id]);
            if (!$quest) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '找不到此任務或活動已結束']);
                exit;
            }

            $tier_lvl = (int)$quest['quest_tier_lvl'];
            $is_grand = (int)$quest['quest_is_grand'];

            
            
            
            $DB->beginTransaction();
            try {
                // 2. 鎖定該會員在此任務的進度紀錄，防止連點重複領獎
                $qrec = $DB->row("SELECT * FROM fsl_quest_records WHERE mb_id = ? AND quest_id = ? FOR UPDATE", [$mb_id, $quest_id]);

                if ($is_grand === 1) {
                    $total_normal = (int)$DB->single(
                        "SELECT COUNT(*) FROM fsl_quests WHERE quest_tier_lvl = ? AND quest_is_grand = 0 AND quest_status = 1",
                        [$tier_lvl]
                    );
                    $done_normal = (int)$DB->single(
                        "SELECT COUNT(*) FROM fsl_quest_records qr
                         INNER JOIN fsl_quests q ON qr.quest_id = q.quest_id
                         WHERE qr.mb_id = ? AND q.quest_tier_lvl = ? AND q.quest_is_grand = 0 AND q.quest_status = 1 AND qr.qrec_status = 2",
                        [$mb_id, $tier_lvl]
                    );
                    if ($total_normal === 0 || $done_normal < $total_normal) {
                        $DB->rollBack();
                        echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '請先完成並領取本階段所有任務後，再領取終極大禮包']);
                        exit;
                    }
                    if ($qrec && (int)$qrec['qrec_status'] === 2) {
                        $DB->rollBack();
                        echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '您已經領取過本階段的終極達成大禮包囉！']);
                        exit;
                    }
                } else {
                    if (!$qrec || (int)$qrec['qrec_status'] !== 1) {
                        $DB->rollBack();
                        echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '此任務尚未達成領獎條件，或您已經領取過獎勵']);
                        exit;
                    }
                }


                // 3. 檢查獎品是否內含實體商品或數位下載檔
                $has_physical  = false;
                $digital_files = [];
                $merch_ids_str = trim($quest['gift_merch_ids'] ?? '');

                if ($merch_ids_str !== '') {
                    $id_arr = array_filter(array_map('intval', explode(',', $merch_ids_str)));
                    if (!empty($id_arr)) {
                        $placeholders = implode(',', array_fill(0, count($id_arr), '?'));
                        $merches = $DB->query(
                            "SELECT merch_id, merch_type, merch_title, merch_download_url, merch_file_format FROM merchandise WHERE merch_id IN ($placeholders)",
                            $id_arr
                        );
                        if (is_array($merches)) {
                            foreach ($merches as $m) {
                                if ((int)$m['merch_type'] === 1) {
                                    $has_physical = true;
                                } elseif ((int)$m['merch_type'] === 2 && !empty($m['merch_download_url'])) {
                                    $digital_files[] = [
                                        'title'  => $m['merch_title'],
                                        'format' => $m['merch_file_format'],
                                        'url'    => $m['merch_download_url']
                                    ];
                                }
                            }
                        }
                    }
                }

                // 若內含實體商品（非終極自動預設寄送時）檢核寄送資訊
                $shipping_note = null;
                if ($has_physical) {
                    if ($recv_name !== '' && $recv_addr !== '') {
                        $shipping_note = "收件人：{$recv_name} / 電話：{$recv_tel} / 地址：{$recv_addr}";
                    } else {
                        $shipping_note = "已登記實體獎品寄送（將依會員資料聯絡地址寄出）";
                    }
                }

                // 4. 寫入 fsl_gift_records 禮品領收紀錄 (grec_source_type = 5 代表任務獎勵)
                $gift_id    = (int)$quest['gift_id'];
                $valid_days = (int)$quest['gift_valid_days'];
                $expired_at = ($valid_days > 0) ? date('Y-m-d H:i:s', strtotime("+{$valid_days} days")) : null;
                $grec_stat  = $has_physical ? 2 : 1; // 2=實體寄送中, 1=已領取入帳

                $DB->query(
                    "INSERT INTO fsl_gift_records (gift_id, mb_id, grec_source_type, grec_source_id, grec_status, grec_claimed_at, grec_expired_at, grec_shipping_note)
                    VALUES (?, ?, 5, ?, ?, NOW(), ?, ?)",
                    [$gift_id, $mb_id, $quest_id, $grec_stat, $expired_at, $shipping_note]
                );
                $grec_id = (int)$DB->single("SELECT MAX(grec_id) FROM fsl_gift_records WHERE mb_id = ? AND gift_id = ?", [$mb_id, $gift_id]);

                $DB->query("UPDATE fsl_gifts SET gift_issued_count = gift_issued_count + 1 WHERE gift_id = ?", [$gift_id]);

                // 5. 更新或寫入 fsl_quest_records 為 2-已完成並領取獎勵
                if ($qrec) {
                    $DB->query(
                        "UPDATE fsl_quest_records SET qrec_status = 2, qrec_claimed_at = NOW(), grec_id = ? WHERE qrec_id = ?",
                        [$grec_id, (int)$qrec['qrec_id']]
                    );
                } else {
                    $DB->query(
                        "INSERT INTO fsl_quest_records (quest_id, mb_id, qrec_curr_val, qrec_status, qrec_completed_at, qrec_claimed_at, grec_id)
                        VALUES (?, ?, ?, 2, NOW(), NOW(), ?)",
                        [$quest_id, $mb_id, (int)$quest['quest_target_val'], $grec_id]
                    );
                }

                // 6. 若獎品內含紅利點數，同步更新 members.mb_points 並寫入 fsl_points 流水帳子表
                $add_pts = (int)$quest['gift_pts'];
                $new_pts = null;
                if ($add_pts > 0) {
                    $DB->query("UPDATE members SET mb_points = mb_points + ? WHERE mb_id = ?", [$add_pts, $mb_id]);
                    $new_pts = (int)$DB->single("SELECT mb_points FROM members WHERE mb_id = ?", [$mb_id]);
                    if (isset($_SESSION['mbPoints'])) {
                        $_SESSION['mbPoints'] = $new_pts;
                    }

                    $memo = '達成任務獎勵：' . $quest['quest_title'];
                    $DB->query(
                        "INSERT INTO fsl_points (mb_id, pt_income, pt_type, pt_for, pt_forid, pt_trade, pt_redeem, pt_freepoints, pt_available, pt_memo, pt_status)
                        VALUES (?, 1, 2, 4, ?, ?, 0, ?, ?, ?, 1)",
                        [$mb_id, $grec_id, $add_pts, $add_pts, $new_pts, $memo]
                    );
                }


                $DB->commit();
            } catch (Exception $e) {
                $DB->rollBack();
                throw $e;
            }




            // 7. 計算該等級任務群最新達成進度，回傳給前端即時更新進度條與終極大禮解鎖狀態
            $total_tier_q = (int)$DB->single(
                "SELECT COUNT(*) FROM fsl_quests WHERE quest_tier_lvl = ? AND quest_is_grand = 0 AND quest_status = 1",
                [$tier_lvl]
            );
            $done_tier_q = (int)$DB->single(
                "SELECT COUNT(*) FROM fsl_quest_records qr
                 INNER JOIN fsl_quests q ON qr.quest_id = q.quest_id
                 WHERE qr.mb_id = ? AND q.quest_tier_lvl = ? AND q.quest_is_grand = 0 AND q.quest_status = 1 AND qr.qrec_status = 2",
                [$mb_id, $tier_lvl]
            );

            echo json_encode([
                'success'       => 1,
                'code'          => '[@-successcode-@]',
                'message'       => $has_physical
                    ? "任務獎勵「{$quest['gift_title']}」已登記寄送，我們將於 3 個工作日內安排宅配寄出！"
                    : "恭喜完成任務！「{$quest['gift_title']}」已成功領取並入帳！",
                'quest_title'   => $quest['quest_title'],
                'gift_title'    => $quest['gift_title'],
                'has_physical'  => $has_physical ? 1 : 0,
                'added_pts'     => $add_pts,
                'new_pts'       => $new_pts,
                'digital_files' => $digital_files,
                'done_count'    => $done_tier_q,
                'total_count'   => $total_tier_q
            ]);
            exit;
        break;
        // =========================================================================
        // 動作 act_cart_add ：全站一鍵加入購物車 / 立即預約課程與時段
        // =========================================================================
        case "act_cart_add":
            if ($mb_id <= 0) {
                echo json_encode(['success' => 0, 'need_login' => 1, 'message' => '請先登入會員帳號']);
                exit;
            }

            $item_type = (int)($_POST['item_type'] ?? 1); // 1=課程或顧問預約, 2=實體或數位商品
            $course_id = (int)($_POST['course_id'] ?? 0);
            $sess_id   = (int)($_POST['sess_id'] ?? 0);
            $merch_id  = (int)($_POST['merch_id'] ?? 0);
            $qty       = max(1, (int)($_POST['qty'] ?? 1));
            $item_title = '';

            if ($item_type === 1) {
                if ($course_id <= 0) {
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '缺少課程項目參數']);
                    exit;
                }
                $course = $DB->row("SELECT course_id, course_title, course_type, course_max FROM courses WHERE course_id = ? AND course_status = 1", [$course_id]);
                if (!$course) {
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '找不到該課程或目前已下架']);
                    exit;
                }
                $item_title = $course['course_title'];

                // 若前端未指定梯次 sess_id，自動抓取該課程最新開放的梯次/空檔時段
                if ($sess_id <= 0) {
                    $auto_sess = $DB->row(
                        "SELECT sess_id FROM course_sessions WHERE course_id = ? AND sess_status = 1 AND sess_enrollee < ? ORDER BY sess_startdate ASC, sess_starttime ASC LIMIT 1",
                        [$course_id, (int)$course['course_max']]
                    );
                    if ($auto_sess) {
                        $sess_id = (int)$auto_sess['sess_id'];
                    }
                }

                // 檢查購物車是否已存在相同課程梯次（課程/預約數量固定為 1，不重複疊加）
                $exist = $DB->row(
                    "SELECT cart_id FROM fsl_cart_items WHERE mb_id = ? AND cart_item_type = 1 AND course_id = ? AND sess_id = ?",
                    [$mb_id, $course_id, $sess_id]
                );
                if (!$exist) {
                    $DB->query(
                        "INSERT INTO fsl_cart_items (mb_id, cart_item_type, course_id, sess_id, merch_id, cart_qty) VALUES (?, 1, ?, ?, NULL, 1)",
                        [$mb_id, $course_id, $sess_id]
                    );
                }
            } else {
                if ($merch_id <= 0) {
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '缺少商品項目參數']);
                    exit;
                }
                $merch = $DB->row("SELECT merch_id, merch_title, merch_type, merch_stock FROM merchandise WHERE merch_id = ? AND merch_status = 1", [$merch_id]);
                if (!$merch) {
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '找不到該商品或目前已停售']);
                    exit;
                }
                if ((int)$merch['merch_stock'] === 0) {
                    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '很抱歉，此商品目前已售完']);
                    exit;
                }
                $item_title = $merch['merch_title'];

                // 數位物件 (merch_type=2) 固定數量 1；實體商品可累加數量
                $exist = $DB->row(
                    "SELECT cart_id, cart_qty FROM fsl_cart_items WHERE mb_id = ? AND cart_item_type = 2 AND merch_id = ?",
                    [$mb_id, $merch_id]
                );
                if ($exist) {
                    $new_qty = ((int)$merch['merch_type'] === 2) ? 1 : ((int)$exist['cart_qty'] + $qty);
                    $DB->query("UPDATE fsl_cart_items SET cart_qty = ? WHERE cart_id = ?", [$new_qty, (int)$exist['cart_id']]);
                } else {
                    $ins_qty = ((int)$merch['merch_type'] === 2) ? 1 : $qty;
                    $DB->query(
                        "INSERT INTO fsl_cart_items (mb_id, cart_item_type, course_id, sess_id, merch_id, cart_qty) VALUES (?, 2, NULL, NULL, ?, ?)",
                        [$mb_id, $merch_id, $ins_qty]
                    );
                }
            }

            $cart_count = (int)$DB->single("SELECT COALESCE(SUM(cart_qty), 0) FROM fsl_cart_items WHERE mb_id = ?", [$mb_id]);

            echo json_encode([
                'success'    => 1,
                'code'       => '[@-successcode-@]',
                'message'    => "「{$item_title}」已加入購物車！",
                'item_title' => $item_title,
                'cart_count' => $cart_count
            ]);
            exit;
        break;

        // =========================================================================
        // 動作 act_cart_update ：購物車調整商品數量 或 刪除單項
        // =========================================================================
        case "act_cart_update":
            $cart_id = (int)($_POST['cart_id'] ?? 0);
            $op      = trim($_POST['op'] ?? 'remove'); // qty | remove
            $qty     = max(1, (int)($_POST['qty'] ?? 1));

            if ($cart_id <= 0) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '無效的購物車項目編號']);
                exit;
            }

            if ($op === 'qty') {
                $DB->query("UPDATE fsl_cart_items SET cart_qty = ? WHERE cart_id = ? AND mb_id = ?", [$qty, $cart_id, $mb_id]);
            } else {
                $DB->query("DELETE FROM fsl_cart_items WHERE cart_id = ? AND mb_id = ?", [$cart_id, $mb_id]);
            }

            $cart_count = (int)$DB->single("SELECT COALESCE(SUM(cart_qty), 0) FROM fsl_cart_items WHERE mb_id = ?", [$mb_id]);

            echo json_encode([
                'success'    => 1,
                'code'       => '[@-successcode-@]',
                'message'    => '購物車內容已更新',
                'cart_count' => $cart_count
            ]);
            exit;
        break;
        // =========================================================================
        // 動作 act_checkout ：極簡一鍵結帳 (寫入訂單 + 明細 + 課程報名 + 發票 + 點數流水)
        // =========================================================================
        case "act_checkout":
            $use_pts     = max(0, (int)($_POST['use_pts'] ?? 0));
            $grec_id     = (int)($_POST['grec_id'] ?? 0);
            $pay_gateway = trim($_POST['pay_gateway'] ?? 'ECPay');
            $pay_method  = trim($_POST['pay_method'] ?? 'Credit');
            $inv_type    = (int)($_POST['inv_type'] ?? 1); // 1=個人二聯式, 2=公司三聯式
            $inv_tax_id  = trim($_POST['inv_tax_id'] ?? '');
            $inv_comp    = trim($_POST['inv_company_title'] ?? '');
            $inv_carrier = trim($_POST['inv_carrier_code'] ?? '');
            $recv_name   = trim($_POST['receiver_name'] ?? '');
            $recv_phone  = trim($_POST['receiver_phone'] ?? '');
            $recv_addr   = trim($_POST['receiver_address'] ?? '');
            $ord_note    = trim($_POST['ord_note'] ?? '');

            // 1. 撈取購物車內所有項目與最新價格
            $sql_cart = "
                SELECT ci.*,
                       c.course_title, c.course_cost, c.course_type,
                       s.sess_batch, s.sess_startdate, s.sess_starttime,
                       m.merch_title, m.merch_type, m.merch_price_twd, m.merch_price_usd, m.merch_sale_usd
                FROM fsl_cart_items ci
                LEFT JOIN courses c ON ci.cart_item_type = 1 AND ci.course_id = c.course_id
                LEFT JOIN course_sessions s ON ci.cart_item_type = 1 AND ci.sess_id = s.sess_id
                LEFT JOIN merchandise m ON ci.cart_item_type = 2 AND ci.merch_id = m.merch_id
                WHERE ci.mb_id = ?
                ORDER BY ci.cart_id ASC
            ";
            $cart_rows = $DB->query($sql_cart, [$mb_id]);
            if (empty($cart_rows)) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '您的購物車目前是空的']);
                exit;
            }

            // 2. 計算商品小計、是否包含實體商品、整理明細快照
            $subtotal        = 0.00;
            $has_physical    = false;
            $order_snapshots = [];

            foreach ($cart_rows as $cr) {
                $qty = max(1, (int)$cr['cart_qty']);
                if ((int)$cr['cart_item_type'] === 1) {
                    $title = $cr['course_title'];
                    if (!empty($cr['sess_startdate'])) {
                        $title .= ' (' . $cr['sess_startdate'] . ' ' . substr($cr['sess_starttime'], 0, 5) . ')';
                    }
                    $unit_price = (float)$cr['course_cost'];
                    $item_sub   = $unit_price * $qty;
                    $subtotal  += $item_sub;

                    $order_snapshots[] = [
                        'ordi_type'       => 1,
                        'course_id'       => (int)$cr['course_id'],
                        'sess_id'         => !empty($cr['sess_id']) ? (int)$cr['sess_id'] : null,
                        'merch_id'        => null,
                        'ordi_title'      => $title,
                        'ordi_unit_price' => $unit_price,
                        'ordi_qty'        => $qty,
                        'ordi_subtotal'   => $item_sub
                    ];
                } else {
                    if ((int)$cr['merch_type'] === 1) {
                        $has_physical = true;
                    }
                    $twd_p = (float)$cr['merch_price_twd'];
                    if ($twd_p <= 0) {
                        $usd_p = ($cr['merch_sale_usd'] !== null) ? (float)$cr['merch_sale_usd'] : (float)$cr['merch_price_usd'];
                        $twd_p = round($usd_p * 32);
                    }
                    $item_sub  = $twd_p * $qty;
                    $subtotal += $item_sub;

                    $order_snapshots[] = [
                        'ordi_type'       => 2,
                        'course_id'       => null,
                        'sess_id'         => null,
                        'merch_id'        => (int)$cr['merch_id'],
                        'ordi_title'      => $cr['merch_title'],
                        'ordi_unit_price' => $twd_p,
                        'ordi_qty'        => $qty,
                        'ordi_subtotal'   => $item_sub
                    ];
                }
            }

            if ($has_physical && ($recv_name === '' || $recv_phone === '' || $recv_addr === '')) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '訂單內含實體商品，請完整填寫收件人姓名、電話與配送地址']);
                exit;
            }





            $DB->beginTransaction();
            try {
                // 3. 鎖定並驗證優惠券折抵 (grec_id)，防止同一張券在雙視窗同時折抵
                $coupon_discount = 0.00;
                $valid_grec_id   = null;
                if ($grec_id > 0) {
                    $coupon = $DB->row(
                        "SELECT r.grec_id, g.gift_discount_usd FROM fsl_gift_records r INNER JOIN fsl_gifts g ON r.gift_id = g.gift_id WHERE r.grec_id = ? AND r.mb_id = ? AND r.grec_status = 1 FOR UPDATE",
                        [$grec_id, $mb_id]
                    );
                    if ($coupon && (float)$coupon['gift_discount_usd'] > 0) {
                        $coupon_discount = round((float)$coupon['gift_discount_usd'] * 32);
                        $valid_grec_id   = $grec_id;
                    }
                }

                // 4. 鎖定並驗證會員可用 PTS 紅利點數餘額
                $curr_pts        = (int)$DB->single("SELECT mb_points FROM members WHERE mb_id = ? FOR UPDATE", [$mb_id]);
                $max_deduct_pts  = max(0, (int)floor($subtotal - $coupon_discount));
                $actual_pts_used = min($use_pts, $curr_pts, $max_deduct_pts);

                $final_total = max(0.00, $subtotal - $coupon_discount - $actual_pts_used);
                $ord_no      = 'ORD-' . date('Ymd') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
                $ord_status  = ($final_total <= 0) ? 2 : 1;





                // 5. 寫入訂購訂單主表 (fsl_orders)
                $sql_ord = "
                    INSERT INTO fsl_orders
                    (ord_no, mb_id, ord_currency, ord_subtotal, ord_discount, ord_pts_used, ord_shipping_fee, ord_total, grec_id, ord_receiver_name, ord_receiver_phone, ord_receiver_address, ord_note, ord_status)
                    VALUES (?, ?, 'TWD', ?, ?, ?, 0.00, ?, ?, ?, ?, ?, ?, ?)
                ";
                $DB->query($sql_ord, [
                    $ord_no, $mb_id, $subtotal, $coupon_discount, $actual_pts_used, $final_total,
                    $valid_grec_id, ($recv_name ?: null), ($recv_phone ?: null), ($recv_addr ?: null), ($ord_note ?: null), $ord_status
                ]);
                $ord_id = (int)$DB->single("SELECT ord_id FROM fsl_orders WHERE ord_no = ?", [$ord_no]);

                // 6. 寫入訂單明細子表 (fsl_order_items) & 同步寫入課程報名紀錄 (fsl_course_enrolls)
                foreach ($order_snapshots as $snap) {
                    $DB->query(
                        "INSERT INTO fsl_order_items (ord_id, ordi_type, course_id, sess_id, merch_id, ordi_title, ordi_unit_price, ordi_qty, ordi_subtotal)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [$ord_id, $snap['ordi_type'], $snap['course_id'], $snap['sess_id'], $snap['merch_id'], $snap['ordi_title'], $snap['ordi_unit_price'], $snap['ordi_qty'], $snap['ordi_subtotal']]
                    );

                    // 若為課程或一對一顧問預約，自動寫入報名紀錄表
                    if ($snap['ordi_type'] === 1 && !empty($snap['course_id']) && !empty($snap['sess_id'])) {
                        $enr_pay = ($final_total <= 0) ? 2 : 0; // 2=免費或折抵完成, 0=待付款占名額
                        $DB->query(
                            "INSERT IGNORE INTO fsl_course_enrolls (mb_id, course_id, sess_id, ord_id, enr_pay_status, enr_status) VALUES (?, ?, ?, ?, ?, 1)",
                            [$mb_id, $snap['course_id'], $snap['sess_id'], $ord_id, $enr_pay]
                        );
                        if ($final_total <= 0) {
                            $DB->query("UPDATE course_sessions SET sess_enrollee = sess_enrollee + 1 WHERE sess_id = ?", [$snap['sess_id']]);
                        }
                    } elseif ($snap['ordi_type'] === 2 && !empty($snap['merch_id'])) {
                        $DB->query("UPDATE merchandise SET merch_sold = merch_sold + ? WHERE merch_id = ?", [$snap['ordi_qty'], $snap['merch_id']]);
                    }
                }

                // 7. 寫入發票收據與支付紀錄表 (fsl_invoices)
                $carrier_type = ($inv_carrier !== '') ? 1 : 0;
                $inv_stat     = ($final_total <= 0) ? 1 : 0;
                $paid_at      = ($final_total <= 0) ? date('Y-m-d H:i:s') : null;
                $gateway_use  = ($final_total <= 0) ? 'PTS全額折抵/免費' : $pay_gateway;

                $DB->query(
                    "INSERT INTO fsl_invoices (ord_id, mb_id, inv_pay_gateway, inv_pay_method, inv_paid_amount, inv_paid_at, inv_type, inv_tax_id, inv_company_title, inv_carrier_type, inv_carrier_code, inv_status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$ord_id, $mb_id, $gateway_use, $pay_method, $final_total, $paid_at, $inv_type, ($inv_tax_id ?: null), ($inv_comp ?: null), $carrier_type, ($inv_carrier ?: null), $inv_stat]
                );

                // 8. 若使用了優惠券，標記為 3-已使用完畢
                if ($valid_grec_id !== null) {
                    $DB->query("UPDATE fsl_gift_records SET grec_status = 3 WHERE grec_id = ?", [$valid_grec_id]);
                }



                // 9. 若折抵了 PTS 點數，扣除會員餘額並寫入 fsl_points 流水帳
                if ($actual_pts_used > 0) {
                    $DB->query("UPDATE members SET mb_points = mb_points - ? WHERE mb_id = ? AND mb_points >= ?", [$actual_pts_used, $mb_id, $actual_pts_used]);
                    $new_pts = (int)$DB->single("SELECT mb_points FROM members WHERE mb_id = ?", [$mb_id]);
                    $_SESSION['mbPoints'] = $new_pts;

                    $memo = "訂單結帳點數折抵：{$ord_no}";
                    $DB->query(
                        "INSERT INTO fsl_points (mb_id, pt_income, pt_type, pt_for, pt_forid, pt_trade, pt_redeem, pt_freepoints, pt_available, pt_memo, pt_status)
                         VALUES (?, 0, 3, 2, ?, ?, 0, 0, ?, ?, 1)",
                        [$mb_id, $ord_id, -$actual_pts_used, $new_pts, $memo]
                    );
                }

                // 10. 清空該會員購物車
                $DB->query("DELETE FROM fsl_cart_items WHERE mb_id = ?", [$mb_id]);

                $DB->commit();
            } catch (Exception $e) {
                $DB->rollBack();
                throw $e;
            }




            echo json_encode([
                'success'     => 1,
                'code'        => '[@-successcode-@]',
                'message'     => "訂單（{$ord_no}）已建立成功！",
                'ord_no'      => $ord_no,
                'final_total' => $final_total
            ]);
            exit;
        break;

        // =========================================================================
        // 動作 act_toggle_wishlist ：全站一鍵加入/移除「收藏與追蹤」(fsl_wishlists)
        // =========================================================================
        case "act_toggle_wishlist":
            if ($mb_id <= 0) {
                echo json_encode(['success' => 0, 'need_login' => 1, 'message' => '請先登入會員帳號即可加入收藏']);
                exit;
            }

            $target_type = (int)($_POST['target_type'] ?? 0); // 1=課程, 2=商品, 3=文章
            $target_id   = (int)($_POST['target_id'] ?? 0);
            $force_op    = trim($_POST['op'] ?? 'toggle');    // toggle | remove

            if ($target_type < 1 || $target_type > 3 || $target_id <= 0) {
                echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '無效的收藏項目參數']);
                exit;
            }

            $exist = $DB->row(
                "SELECT wish_id FROM fsl_wishlists WHERE mb_id = ? AND wish_target_type = ? AND wish_target_id = ?",
                [$mb_id, $target_type, $target_id]
            );

            if ($exist || $force_op === 'remove') {
                $DB->query(
                    "DELETE FROM fsl_wishlists WHERE mb_id = ? AND wish_target_type = ? AND wish_target_id = ?",
                    [$mb_id, $target_type, $target_id]
                );
                $is_wishlist = 0;
                $msg         = '已從您的「收藏與追蹤」清單移除';
            } else {
                $DB->query(
                    "INSERT IGNORE INTO fsl_wishlists (mb_id, wish_target_type, wish_target_id) VALUES (?, ?, ?)",
                    [$mb_id, $target_type, $target_id]
                );
                $is_wishlist = 1;
                $msg         = '已加入您的「收藏與追蹤」清單！';
            }

            $wishlist_total = (int)$DB->single("SELECT COUNT(*) FROM fsl_wishlists WHERE mb_id = ?", [$mb_id]);

            echo json_encode([
                'success'        => 1,
                'code'           => '[@-successcode-@]',
                'is_wishlist'    => $is_wishlist,
                'wishlist_total' => $wishlist_total,
                'message'        => $msg
            ]);
            exit;
        break;


















        /////////////////////////////////////////////////////////////////////////////////////
        default:// 無動作預設
            echo json_encode([
                'success'=>0, 
                'code'=>'[@-errorcode-@]',
                'message'=>'無效的請求動作!'
            ]);
            exit();
        break;

    }  
} catch (Exception $e) {
    echo json_encode(['success' => 0, 'code' => '[@-errorcode-@]', 'message' => '系統處理異常，請稍後再試', 'error' => $e->getMessage()]);
    exit();
}


?>