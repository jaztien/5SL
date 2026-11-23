<?php
//error_reporting(0);
//////////////////////////// sys_email_func.php //////////////////////////////////////
//////////////////////////// 管理者資料處理功能執行檔案 ///////////////////////////////
//////////////////////////// 2024-09-02 By Jaz Tien ///////////////////////////////
//////////////////////////// jaztien@gmail.com ////////////////////////////////////
//-------------------------------------------------------------------------------//
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
/*
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../pluginFiles/phpmailer/src/Exception.php';
require '../pluginFiles/phpmailer/src/PHPMailer.php';
require '../pluginFiles/phpmailer/src/SMTP.php';
*/

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
$artiID = isset($_POST['artiID']) ? $_POST['artiID'] : 0;
$artiCODE = isset($_POST['artiCODE']) ? $_POST['artiCODE'] : '';
$arrDatA = isset($_POST['arrDatA']) ? $_POST['arrDatA'] : array();
$cDateTime = date('Y-m-d H:i:s');

//-------------------------------------------------------------------------------//
/*
<p><span style=\"font-size: 16px\">親愛的會員</span><span style=\"font-size: 18px\"><strong> [@username@] </strong></span><span style=\"font-size: 16px\">您好,</span></p><p><span style=\"font-size: 16px\"><br></span></p><p>您於 [@currdate@] 成功儲值了兌換幣共計 [@inpoint@] 點，截至您收到這封郵件之前，您的帳戶還剩餘 [@nowpoint@] 點，未使用完畢的點數超過使用期限時，將於過期日後歸零。<br>若有任何問題或意見，請聯絡本站的電話服務人員，服務電話為 [@comptel@] 分機號碼 [@compext@]，人生第五季竭誠為您服務！</p><p>[@nowdate@]</p><p><br></p><p><br></p><p><br></p><p><br></p><p style=\"text-align: right\">**此為系統自動回覆郵件，請勿直接回覆<br></p><hr class=\"__se__solid\"><p><br></p><table><tbody><tr><td rowspan=\"2\"><div><br></div><div><br></div><div>客戶服務專線：<span style=\"font-size: 16px\">02-81018800</span><br>服務時間：週一至週六 9：00~18：00<br>信箱：light@5seasonlife.com<br>地址：110台北市xx區xx路xx號x樓<br></div></td><td><div class=\"se-component se-image-container __se__float-right\"><figure><img src=\"../uploadFiles/sysimg/sysimg_20240901024320832.jpg\" alt=\"5SeasonLifeLOGOemail\" data-rotate=\"\" data-proportion=\"true\" data-size=\",\" data-align=\"right\" data-percentage=\"auto,auto\" data-file-name=\"sysimg_20240901024320832.jpg\" data-file-size=\"0\" origin-size=\"205,76\" data-origin=\",\" style=\"\"></figure></div></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><div style=\"text-align: right\">​<span style=\"font-size: 18px\"><strong>人生第五季生命藝術工作室</strong></span>​​<br></div></td></tr></tbody></table>
*/
/*
if(!empty($artiCODE)){
    $getArtiARR = DB->row("SELECT arti_subscript,arti_body FROM articles WHERE arti_keycode=? AND arti_status=?",array($artiCODE,1));
}
*/
/*
function _fn_email_sending($artiCODE,$setFrom=[],$setTo=[],$setCC=[],$setBCC=[],$replaceARR=[]){
    
    //取得紀錄內容
    require_once('../includeFiles/db.inc.php');
    $getArtiARR = DB->row("SELECT arti_subscript,arti_body FROM articles WHERE arti_keycode=? AND arti_status=?",array($artiCODE,1));
    if(!$getArtiARR || empty($getArtiARR['arti_subscript']) || empty($getArtiARR['arti_body'])){
        return false;
    }
    
    require '../pluginFiles/phpmailer/src/Exception.php';
    require '../pluginFiles/phpmailer/src/PHPMailer.php';
    require '../pluginFiles/phpmailer/src/SMTP.php';
    $cDateTime = date('Y-m-d H:i:s');
    //---- 設定固定值 ----//
    $setHost = 'smtp.gmail.com';
    $setPort = '587';
    $setUsername = 'jaztien@gmail.com';
    $setPassword = 'uikw gwnp zvcs sbhi';//uikw gwnp zvcs sbhi (應用程式密碼)
    //---- /設定固定值 ----//
    //---- 設定變數值 ----//
    $tagARR=array("[@nowdate@]","[@currdate@]","[@username@]","[@userid@]","[@inpoint@]","[@nowpoint@]","[@comptel@]","[@compext@]");
    //$toStr=array($cDateTime,"2024-06-08",CF_AD_NICK,1,"500","2650","02-23456789","888");

    //$setFromMail = $setFrom[0];
    //$setFromName = $setFrom[1];
    //$setFrom=[$_SESSION['SESS_AD_EMAIL'],CF_AD_NICK];
    //$setToMail = $_SESSION['SESS_AD_EMAIL'];
    //$setToName = "mailto";
    //---- /設定變數值 ----//
    
    $htmlBody = stripslashes($getArtiARR['arti_body']);//主文
    $subject = str_replace($tagARR,$replaceARR,stripslashes($getArtiARR['arti_subscript']));//標題
    $subject = "=?UTF-8?B?".base64_encode($subject)."?=";//格式化標題(避免亂嗎)

    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->SMTPDebug = 0;//0
    $mail->Host = $setHost;
    //$mail->Port = 465;  //SSL
    $mail->Port = $setPort;  //TLS
    //$mail->SMTPSecure = 'ssl';//ssl
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->SMTPAuth = true;
    
    $mail->Username = $setUsername;
    $mail->Password = $setPassword;//uikw gwnp zvcs sbhi (應用程式密碼)

    //$mail->From = $setFromMail; //設定寄件人電郵
    //$mail->FromName = $setFromName; //設定寄件人名稱
    $mail->setFrom($setFrom[0], $setFrom[1]);
    for($i=0;$i<count($setTo);$i++){
        $mail->addAddress($setTo[$i][0], $setTo[$i][1]);
    }
    if(count($setCC)>0){
        for($i=0;$i<count($setCC);$i++){
            $mail->addCC($setCC[$i][0], $setCC[$i][1]); //設定收件人電郵及名稱(CC)
        }
    }
    if(count($setBCC)>0){
        for($i=0;$i<count($setBCC);$i++){
            $mail->addBCC($setBCC[$i][0], $setBCC[$i][1]); //設定收件人電郵及名稱(BCC)
        }
    }
    $mail->Subject = $subject; //信件標題，解決亂碼問題

    $mail->IsHTML(true);
    $htmlBody=str_replace("<table>",'<table width="100%">',$htmlBody);
    //$htmlImgARR = explode("../uploadFiles/sysimg/",$htmlBody);
    //---- 解析並替換 HTML 主文中的圖檔 ----//
    $htmlImgARR = explode('data-file-name="',$htmlBody);
    for($i=1;$i<count($htmlImgARR);$i++){
      $getPureARR = explode('" data-file-size="',$htmlImgARR[$i]);
      $getPureName = trim($getPureARR[0]);
      $getPureCID = pathinfo($getPureName, PATHINFO_FILENAME);
      $htmlBody=str_replace("../uploadFiles/sysimg/".$getPureName,"cid:".$getPureCID,$htmlBody);
      $mail->AddEmbeddedImage('../uploadFiles/sysimg/'.$getPureName, $getPureCID, $getPureName); 
      //$mail->AddEmbeddedImage('sysimg_20240901024320832.jpg');//cid:fh-logo02//嵌入圖檔
    }
    //---- /解析並替換 HTML 主文中的圖檔 ----//
    //---- 解析並替換 HTML 主文中的文字 ----//
    $htmlBody = str_replace($tagARR,$replaceARR,$htmlBody);
    //---- /解析並替換 HTML 主文中的文字 ----//
    //---- 送出 HTML 主文 ----//
    $mail->Body = $htmlBody;//'This is the HTML message body in bold!';
    //---- /送出 HTML 主文 ----//
    //傳送 CC/BCC 
    //$mail->addCC("personC@gmail.com", "person C"); //設定收件人電郵及名稱(CC)
    //$mail->addBCC("personD@gmail.com", "person D"); //設定收件人電郵及名稱(BCC)
    //$mail->addAttachment("image1.jpg", "picture.jpg"); //設定附件, 對方會看到附件名稱為 picture.jpg

    return $mail->send();

}
*/
//-------------------------------------------------------------------------------//
switch ($formAcce) {
    case "func_email_preview":////////////////////////////////////////// func_email_preview 文章管理類

        $replaceTag = !empty($arrDatA['replaceTag']) ? $arrDatA['replaceTag'] : [];
        $replaceStr = !empty($arrDatA['replaceStr']) ? $arrDatA['replaceStr'] : [];
        $compEmailADD = DB->getConfigVal('comp_email');
        $compEmailFrom = DB->getConfigVal('comp_fullname');
        $sendRTN = DB->doSendEmail(
            '',
            $replaceTag,$replaceStr,
            '【測試預覽】'.$arrDatA['subject'],
            $arrDatA['body'],
            //[[$_SESSION['SESS_AD_EMAIL'],$_SESSION['SESS_AD_NICK']]],
            [[$compEmailADD['config_value'], $compEmailFrom['config_value']]],
            [[$arrDatA['mailTo'],$arrDatA['mailTo']]]
            );
        if ($sendRTN) {
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功測試Email！！');
            echo json_encode($rtn);
        } else {  
            $rtn = array('RTN'=>0, 'msgType'=>'success', 'msgStr'=>'測試Email失敗！！');
            echo json_encode($rtn); 
        }

    break;
    case "xxxx":////////////////////////////////////////// func_email_notify 管理人員通知函




    break;
    case "xxxxx":////////////////////////////////////////// func_email_sendmail 會員通知函/回函



    break;
    default:///////////////////////// default
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '沒有功能項目被指定，系統將認定為程式碼錯誤，請聯絡管理人員！！');
        echo json_encode($rtn);

    break;


}
                              
?>
