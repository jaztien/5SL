<?php
//error_reporting(0);
//////////////////////////// mb_chat_func.php //////////////////////////////////////
//////////////////////////// 管理者資料處理功能執行檔案 ///////////////////////////////
//////////////////////////// 2025-01-25 By Jaz Tien ///////////////////////////////
//////////////////////////// jaztien@gmail.com ////////////////////////////////////
//-------------------------------------------------------------------------------//
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
//-------------------------------------------------------------------------------//
//若有 TOKEN 驗證要求時，進行雙重認證。

if(!isset($_POST['token']) || $_POST['token']=='' || empty($_SESSION['token'])){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失TOKEN驗證值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
} else {
    if($_POST['token'] != $_SESSION['token']){
        echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'TOKEN驗證值比對不符，表單傳送失敗！請聯絡相關管理人員！！'));
        exit();
    }
}

$datapass = true;
if( empty($_POST['formAcce'])){ 
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
$formAcce = $_POST['formAcce'];

if($datapass == false){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
//-------------------------------------------------------------------------------//
$cDateTime = date('Y-m-d H:i:s');
$cTime = time();
$cTimeStamp = round(microtime(true) * 1000);
//-------------------------------------------------------------------------------//
switch ($formAcce) {
    case "func_chat_getdata":////////////////////////////////////////// func_chat_getdata 
        /*
        $chatType = isset($_POST['chatType']) ? $_POST['chatType'] : 0;
        //$toTime = $cTime - (86400 * 180);//保留至N日前
        $toTime = (new DateTime())->modify('-180 days')->format('Y-m-d H:i:s');//保留至N日前
        $joinID = isset($_POST['joinID']) ? $_POST['joinID'] : 0;
        //$sql = "SELECT *, dateadd(S, chat_published, '1970-01-01 00:00:00') AS datetime";
        $sql = "SELECT *";
        $sql .= ", (SELECT CONCAT(e.chat_messtype,'|',e.chat_message,'|',e.chat_filename,'|',e.chat_poster,'|',e.chat_published) FROM data_chats e WHERE e.chat_id = a.chat_replyid) AS replymess";
        $sql .= " FROM data_chats a WHERE";
        if(!empty($chatType)&&!empty($joinID)){
            $sql .= " (chat_type=".$chatType." AND chat_joinid=".$joinID.") OR chat_type=0";
        } else {
            $sql .= " chat_type=0";
        }
        $sql .=  " AND chat_published > ? AND chat_status < ? ORDER BY chat_published ASC";
        $res = $DB->query($sql, array($toTime, 9));
        $upres =0;
        if($res){
            if(!empty($chatType)&&!empty($joinID)){//更新會員已讀狀態
                $upsql = "UPDATE data_chats SET chat_read=chat_read+?, chat_status=? WHERE chat_published > ?";
                if (!empty($chatType) && !empty($joinID)) {
                    $upsql .= " AND ((chat_type=".$chatType." AND chat_joinid=".$joinID.") OR chat_type=0)";
                } else {
                    $upsql .= " AND chat_type=0";
                }
                $upsql .= " AND chat_status=? AND chat_posttype=?";
                $upres = $DB->query($upsql, array(1,1,$toTime,2,2));
            }
            $rtn = $res;
        } else{
            $rtn = array();
        }
        */
        //JazNOTE: 以下爲新的寫法，尚未經過測試
        // 1. 整理並強制轉型輸入值（安全防禦第一步）
        $chatType = isset($_POST['chatType']) ? (int)$_POST['chatType'] : 0;
        $joinID   = isset($_POST['joinID']) ? (int)$_POST['joinID'] : 0;
        //取得當下時間180日之前的時間，格式爲Y-m-d H:i:s
        $toTime   = (new DateTime())->modify('-180 days')->format('Y-m-d H:i:s');

        // 2. 使用 LEFT JOIN 取代子查詢，並整合 CONCAT 字串（支援 MS SQL 2012+）
        $sql = "SELECT a.*, 
                    CONCAT(e.chat_messtype, '|', e.chat_message, '|', e.chat_filename, '|', e.chat_poster, '|', e.chat_published) AS replymess
                FROM data_chats a
                LEFT JOIN data_chats e ON e.chat_id = a.chat_replyid
                WHERE ";

        // 3. 建構條件與參數陣列（解決 SQL 注入風險）
        $params = [];
        if ($chatType > 0 && $joinID > 0) {
            $sql .= "((a.chat_type = ? AND a.chat_joinid = ?) OR a.chat_type = 0)";
            $params[] = $chatType;
            $params[] = $joinID;
        } else {
            $sql .= "a.chat_type = 0";
        }

        $sql .= " AND a.chat_published > ? AND a.chat_status < ? ORDER BY a.chat_published ASC";
        $params[] = $toTime;
        $params[] = 9;
        $res = $DB->query($sql, $params);

        $upres = 0;
        $rtn = array();

        if ($res) {
            $rtn = $res;
            // 5. 更新會員已讀狀態（移除內部重複的 if 判斷，全面參數化）
            if ($chatType > 0 && $joinID > 0) {
                $upsql = "UPDATE data_chats 
                        SET chat_read = chat_read + ?, chat_status = ? 
                        WHERE chat_published > ? 
                            AND ((chat_type = ? AND chat_joinid = ?) OR chat_type = 0)
                            AND chat_status = ? 
                            AND chat_posttype = ?";
                $upParams = [1, 1, $toTime, $chatType, $joinID, 2, 2];
                $upres = $DB->query($upsql, $upParams);
            }
        }


        echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'list'=>$rtn, 'upNum'=>$upres));

    break;
    case "func_chat_poston":////////////////////////////////////////// func_chat_poston 寫入聊天記錄
        $messData = isset($_POST['messData']) ? $_POST['messData'] : array();
        $messAction = isset($_POST['messAction']) ? $_POST['messAction'] : '';
        $messType = isset($_POST['messType']) ? $_POST['messType'] : 0;
        $fileData = isset($_POST['fileData']) ? $_POST['fileData'] : '';
        $fileName = isset($_POST['fileName']) ? $_POST['fileName'] : '';
        $fileExt = isset($_POST['fileExt']) ? $_POST['fileExt'] : '';
        if(empty($messData)){
            echo json_encode(array( 
                'action'=>'system',
                'datetime'=>$cDateTime,
                'message'=>'system error and post failed'
            ));
            exit();
        }
        $messContent = $messData['chat_message'];
        if($messType==0){
            if(!empty($messContent)){
                $messContent = strip_tags($messContent);
            } else {
                echo json_encode(array( 
                    'action'=>'system',
                    'datetime'=>$cDateTime,
                    'message'=>'message content is empty'
                ));
                exit();
            }
        } else {
            if(!empty($fileData)){
                $file_array_1 = explode(";", $fileData);
                $file_array_2 = explode(",", $file_array_1[1]);
                $fileUp = base64_decode($file_array_2[1]);
                $newName = 'chatfile_' . $cTimeStamp . '_';
                $filePath = '../uploadFiles/chat/'.$newName.'.'.$fileExt;
                if (file_put_contents($filePath, $fileUp)!==false){
                    chmod($filePath, 0755);
                }
                if($messType==1){
                    //---------------- 製作縮圖 ----------------//
                    $thumbPath = '../uploadFiles/chat/' . $newName . 'thumb.'.$fileExt;
                    $toThumb = _fn_image_autothumb($filePath, $thumbPath, 200);
                    if($toThumb){
                        chmod($thumbPath, 0755);
                        $messContent = $newName.'thumb.'.$fileExt;
                    }
                } else {
                    $messContent = $newName.'.'.$fileExt;
                }
            } else {
                echo json_encode(array( 
                    'action'=>'system',
                    'datetime'=>$cDateTime,
                    'message'=>'file upload failed'
                ));
                exit();
            }
        }
        //
        $messData['chat_message'] = $messContent;
        $messData['chat_published'] = $cDateTime;
        $ins = $DB->insert('data_chats', $messData);
        //$ins = 123456;
        if($ins && $ins > 0){
            $messData['replymess'] = null;
            if(!empty($messData['chat_replyid'])){
                $replyres = $DB->single("SELECT CONCAT(chat_messtype,'|',chat_message,'|',chat_filename,'|',chat_poster,'|',chat_published) FROM data_chats WHERE chat_id = ?",array($messData['chat_replyid']));
                if($replyres){
                    $messData['replymess'] = $replyres;
                }
            }
            $messData['action'] = $messAction;
            $messData['chat_id'] = $ins;
            $messData['datetime'] = $cDateTime;
        } else {
            echo json_encode(array( 
                'action'=>'system',
                'datetime'=>$cDateTime,
                'message'=>'input data error and post failed'
            ));
            exit();
        }
        echo json_encode($messData);

    break;
    case "func_chat_getpodcast":////////////////////////////////////////// func_chat_getpodcast 取得全數廣播數據
        //$toTime = $cTime - (86400 * 180);
        $toTime   = (new DateTime())->modify('-180 days')->format('Y-m-d H:i:s');//保留至N日前
        //$sql = "SELECT *, dateadd(S, chat_published, '1970-01-01 00:00:00') AS datetime FROM data_chats WHERE chat_type=0 AND chat_joinid=0";
        $rtn = array();
        $sql = "SELECT * FROM data_chats WHERE chat_type=0 AND chat_joinid=0";
        $sql .= " AND chat_published > ? AND chat_status < ? ORDER BY chat_published ASC";
        $res = $DB->query($sql, array($toTime, 9));
        if($res){
            $rtn = $res;
        }
        echo json_encode(array('list'=>$rtn));
    break;
    case "func_chat_robotconfig":////////////////////////////////////////// func_chat_robotconfig 更改機器人應答狀態
        $nextOn = isset($_POST['nextOn']) ? intval($_POST['nextOn']) : 0;
        //$up = $DB->update('fsl_configurations', array('config_value'=>$nextOn), array('config_code'=>'mess_robot'));
        $up = $DB->setConfigVal($nextOn, 'mess_robot');
        if($up){
            $rtn = array(
                'nextOn' => $nextOn
            );
        }
        echo json_encode($rtn);
    break;
    case "func_chat_readupdate":////////////////////////////////////////// func_chat_readupdate 已讀更新
        $chatID = isset($_POST['chatID']) ? intval($_POST['chatID']) : 0;
        if(empty($chatID)){
            echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgType' => 'chatID為空值，操作失敗！'));
            exit();
        }
        $sql = "UPDATE data_chats SET chat_read = chat_read+?, chat_status=? WHERE chat_id=?";
        $res = $DB->query($sql, array(1, 1, $chatID));
        //$res = 1;
        if($res){
            echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr' => '應答紀錄已讀標記完成！'));
            exit();
        }
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '更新為已讀狀態失敗，請重新操作或聯絡管理人員！！');
        echo json_encode($rtn);
    break;
    case "func_chat_optlink":////////////////////////////////////////// func_chat_optlink 保留紀錄操作
        $chatID = isset($_POST['chatID']) ? intval($_POST['chatID']) : 0;
        $act = isset($_POST['act']) ? $_POST['act'] : '';
        $nextStep = isset($_POST['nextStep']) ? intval($_POST['nextStep']) : 0;
        if(empty($chatID)){
            echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgType' => 'chatID為空值，操作失敗！'));
            exit();
        }
        switch ($act) {
            case 'keep':
                $up = $DB->update('data_chats', array('chat_keep'=>$nextStep), array('chat_id'=>$chatID));
            break;
            case 'back':
                $up = $DB->update('data_chats', array('chat_status'=>$nextStep), array('chat_id'=>$chatID));
            break;
            case 'delete':
            case 'hidemess':
            case 'vio':
                $up = $DB->update('data_chats', array('chat_status'=>$nextStep), array('chat_id'=>$chatID));
            break;
            case 'marker':
                $up = $DB->update('data_chats', array('chat_incharge'=>$nextStep), array('chat_id'=>$chatID));
            break;
            default:
                $up = 0;
            break;
        }
        if($up){
            echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'應答紀錄狀態已更新！'));
            exit();
        }
        echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr' => '操作失敗！'));
        exit();
    break;
    case "func_chat_ezmess"://///////////// func_chat_ezmess 取得完整上方導航列最新未讀訊息（開頁時顯示）
        $toTime   = (new DateTime())->modify('-180 days')->format('Y-m-d H:i:s');//保留至N日前
        $nuM = isset($_POST['nuM']) ? intval($_POST['nuM']) : 50;
        $content = '';
        $sql = "
        SELECT 
        chat_id, 
        chat_postid, 
        chat_published, 
        chat_type, 
        chat_joinid, 
        chat_incharge, 
        chat_poster, 
        chat_message, 
        chat_messtype, 
        chat_read, 
        chat_status, 
        chat_published AS datetime 
        FROM data_chats WHERE chat_type = ? AND chat_posttype = ? AND chat_published > DATEADD(day, -180, GETDATE())  AND chat_read = ? 
        ORDER BY chat_published DESC LIMIT 50
        ";
        //$getUNread = $DB->query($sql, array(1,2,(time()-(86400*180)),0));
        $getUNread = $DB->query($sql, array(1,2,0));

        foreach ($getUNread as $key => $value) {
            /*
            $chat_id = $value['chat_id'];
            $chat_postid = $value['chat_postid'];
            $totAVE = $DB->single("SELECT mb_rating FROM mb_info WHERE mb_id = ?", array($chat_postid));
            $getStars = floor($totAVE);
            $getHStar = round($totAVE - $getStars);
            */
            $chatAvatar = '../imageFiles/nopic_user_64x64.jpg';
            if (file_exists('../uploadFiles/mb/mbphoto_'.$value['chat_postid'].'_thumb.jpg')) {
                $chatAvatar = '../uploadFiles/mb/mbphoto_'.$value['chat_postid'].'_thumb.jpg';
            }
            $getUNread[$key]['chatAvatar'] = $chatAvatar;
            //$postPast = floor((time() - $value['chat_published']) / 3600);
            $postPast = floor(_fn_hours_passed($value['chat_published']));
            if ($postPast > 24) {
                $postPast = floor($postPast / 24).' 天前';
            } else {
                if($postPast<=0){
                    $postPast = '小於1小時';
                } else {
                    $postPast = $postPast.' 小時前';
                }
            }
            $getUNread[$key]['postPast'] = $postPast;
            $chat_message = $value['chat_message'];
            $message = mb_strlen($chat_message) > 20 ? mb_substr($chat_message, 0, 20).'...' : $chat_message;
            $getUNread[$key]['message'] = $message;
        }
        $rtn = array('RTN'=>1, 'messRes'=>$getUNread);
        echo json_encode($rtn);
    break;
    case "func_chat_eznotify"://///////////// func_chat_eznotify 取得完整上方導航列最新通知（開頁時顯示）
        $nuM = isset($_POST['nuM']) ? intval($_POST['nuM']) : 50;
        $content = '';
        $sql = "
        SELECT 
        noti_id, 
        noti_published, 
        noti_mode, 
        noti_level, 
        noti_forid, 
        noti_content, 
        noti_visits, 
        noti_status, 
        noti_published AS datetime, 
        COUNT(*) OVER() AS totalNum 
        FROM data_notifications WHERE noti_published > DATEADD(day, -180, GETDATE()) 
        ORDER BY noti_published DESC LIMIT 50
        ";
        $getInfo = $DB->query($sql, null);
        //$getInfo = $DB->query($sql, array((time()-(86400*180))));
        $totalNum = !empty($getInfo[0]['totalNum']) ? $getInfo[0]['totalNum'] : 0;    
        foreach ($getInfo as $key => $value) {
            $postPast = floor((time() - $value['noti_published']) / 3600);
            if ($postPast > 24) {
                $postPast = floor($postPast / 24).' 天前';
            } else {
                if($postPast<=0){
                    $postPast = '小於1小時';
                } else {
                    $postPast = $postPast.' 小時前';
                }
            }
            $getInfo[$key]['postPast'] = $postPast;
            unset($getInfo[$key]['totalNum']);
            $noti_content = $value['noti_content'];
            $content = mb_strlen($noti_content) > 20 ? mb_substr($noti_content, 0, 20).'...' : $noti_content;
            $getInfo[$key]['content'] = $content;
            switch($value['noti_level']){
                case 1:
                    $cLevel = '一般';
                    $cAlert = 'success';
                break;
                case 2:
                    $cLevel = '一般重要';
                    $cAlert = 'success';
                break;
                case 3:
                    $cLevel = '重要';
                    $cAlert = 'info';
                break;
                case 4:
                    $cLevel = '非常重要';
                    $cAlert = 'warning';
                break;
                case 5:
                    $cLevel = '緊急';
                    $cAlert = 'danger';
                break;
            }
            $getInfo[$key]['clevel'] = $cLevel;
            $getInfo[$key]['cAlert'] = $cAlert;
        }
        $rtn = array('RTN'=>1, 'notifyRes'=>$getInfo, 'totalNum'=>$totalNum);
        echo json_encode($rtn);
    break;
    case "func_resp_addnew"://///////////// func_resp_addnew 新增應答
        $arrDatA = isset($_POST['arrDatA']) ? $_POST['arrDatA'] : array();
        $arrDatB = isset($_POST['arrDatB']) ? $_POST['arrDatB'] : array();
        $ins = $DB->insert('chatbot_response', $arrDatA);
        //$ins = 1;
        if($ins){
            $i = 0;
            while($i<count($arrDatB)){
                $arrDatB[$i]['rbot_id'] = $ins;
                $qins = $DB->insert('chatbot_question', $arrDatB[$i]);
                $i++;
            }
            echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr' => '成功新增資料！', 'newID'=>$ins));
            exit();
        }
        echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgType' => '新增資料失敗！'));
        exit();

    break;
    case "func_resp_infoUpdate"://///////////// func_resp_infoUpdate 更新應答
        $arrDatA = isset($_POST['arrDatA']) ? $_POST['arrDatA'] : array();
        $rbotID = isset($_POST['rbotID']) ? intval($_POST['rbotID']) : 0;
        if(empty($rbotID)){
            echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgType' => 'respID 為空值，操作失敗！'));
            exit();
        }
        $up = $DB->update('chatbot_response', $arrDatA, array('rbot_id'=>$rbotID));
        if($up){
            echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr' => '更新資料成功！'));
            exit();
        }
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '更新資料失敗，請重新操作或聯絡管理人員！！');
        echo json_encode($rtn);
    break;
    case "func_resp_delete"://///////////// func_resp_delete 刪除應答
        $rbotID = isset($_POST['rbotID']) ? intval($_POST['rbotID']) : 0;
        if(empty($rbotID)){
            echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgType' => 'respID 為空值，操作失敗！'));
            exit();
        }
        $del = $DB->query('DELETE FROM chatbot_response WHERE rbot_id=?', array($rbotID));
        if($del){
            $qdel = $DB->query('DELETE FROM chatbot_question WHERE rbot_id=?', array($rbotID));
            echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr' => '刪除資料成功！'));
            exit();
        }
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '刪除資料失敗，請重新操作或聯絡管理人員！！');
        echo json_encode($rtn);
    break;
    case "func_qbot_status"://///////////// func_qbot_status
        $arrDatA = isset($_POST['arrDatA']) ? intval($_POST['arrDatA']) : 1;
        $qbotID = isset($_POST['qbotID']) ? intval($_POST['qbotID']) : 0;
        $up = $DB->update('chatbot_question', array('qbot_status'=>$arrDatA), array('qbot_id'=>$qbotID));
        if($up){
            echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr' => '更新資料成功！'));
            exit();
        }
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '更新資料失敗，請重新操作或聯絡管理人員！！');
        echo json_encode($rtn);
    break;
    case "func_qbot_delete"://///////////// func_qbot_delete
        $arrDatA = isset($_POST['arrDatA']) ? intval($_POST['arrDatA']) : 1;
        $qbotID = isset($_POST['qbotID']) ? intval($_POST['qbotID']) : 0;
        //$del = $DB->query("DELETE FROM chatbot_question WHERE qbot_id=?", array($qbotID));
        $del = 1;
        if($del){
            echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr' => '刪除資料成功！'));
            exit();
        }
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '刪除資料失敗，請重新操作或聯絡管理人員！！');
        echo json_encode($rtn);
    break;
    case "func_qbot_update"://///////////// func_qbot_update
        $arrDatA = isset($_POST['arrDatA']) ? $_POST['arrDatA'] : array();
        $qbotID = isset($_POST['qbotID']) ? intval($_POST['qbotID']) : 0;
        $up = $DB->update('chatbot_question', $arrDatA, array('qbot_id'=>$qbotID));
        if($up){
            echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr' => '更新資料成功！'));
            exit();
        }
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '更新資料失敗，請重新操作或聯絡管理人員！！');
        echo json_encode($rtn);

    break;
    case "func_qbot_addnew"://///////////// func_qbot_addnew
        $arrDatA = isset($_POST['arrDatA']) ? $_POST['arrDatA'] : array();
        $rbotID = isset($_POST['rbotID']) ? intval($_POST['rbotID']) : 0;
        if($rbotID == 0){
            echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr' => '新增資料成功！'));
            exit();
        }
        //$ins = $DB->insert('chatbot_question', $arrDatA);
        $ins = 100000;
        if($ins){
            echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr' => '新增資料成功！', 'newID'=>$ins));
            exit();
        }
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '新增資料失敗，請重新操作或聯絡管理人員！！');
        echo json_encode($rtn);
    break;
    default:///////////////////////// default
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '沒有功能項目被指定，系統將認定為程式碼錯誤，請聯絡管理人員！！');
        echo json_encode($rtn);
    
    break;


}
                              
?>
