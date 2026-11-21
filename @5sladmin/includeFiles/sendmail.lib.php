<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

///////////////////////////////system functions/////////////////////////////////////

function _fn_email_sending($htmlBody,$subJect,$setFrom=[],$setTo=[],$setCC=[],$setBCC=[],$replaceARR=[]){
    
    //取得紀錄內容
    //require_once '../includeFiles/db.inc.php';
    require '../pluginFiles/phpmailer/src/Exception.php';
    require '../pluginFiles/phpmailer/src/PHPMailer.php';
    require '../pluginFiles/phpmailer/src/SMTP.php';
    /*
    $getArtiARR = $DB->row("SELECT arti_subscript,arti_body FROM articles WHERE arti_keycode=? AND arti_status=?",array($artiCODE,1));
    if(!$getArtiARR || empty($getArtiARR['arti_subscript']) || empty($getArtiARR['arti_body'])){
        return false;
    }
    exit();
    */
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
    
    $htmlbody = stripslashes($htmlBody);//主文
    $subject = str_replace($tagARR,$replaceARR,stripslashes($subJect));//標題
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
    //$htmlbody=str_replace("<table>",'<table width="100%">',$htmlbody);
    //$htmlImgARR = explode("../uploadFiles/sysimg/",$htmlBody);
    //---- 解析並替換 HTML 主文中的圖檔 ----//
    $htmlImgARR = explode('data-file-name="',$htmlbody);
    for($i=1;$i<count($htmlImgARR);$i++){
      $getPureARR = explode('" data-file-size="',$htmlImgARR[$i]);
      $getPureName = trim($getPureARR[0]);
      $getPureCID = pathinfo($getPureName, PATHINFO_FILENAME);
      $htmlbody=str_replace("../uploadFiles/sysimg/".$getPureName,"cid:".$getPureCID,$htmlbody);
      $mail->AddEmbeddedImage('../uploadFiles/sysimg/'.$getPureName, $getPureCID, $getPureName); 
      //$mail->AddEmbeddedImage('sysimg_20240901024320832.jpg');//cid:fh-logo02//嵌入圖檔
    }
    //---- /解析並替換 HTML 主文中的圖檔 ----//
    //---- 解析並替換 HTML 主文中的文字 ----//
    $htmlbody = str_replace($tagARR,$replaceARR,$htmlbody);
    //---- /解析並替換 HTML 主文中的文字 ----//
    //---- 送出 HTML 主文 ----//
    $mail->Body = $htmlbody;//'This is the HTML message body in bold!';
    //---- /送出 HTML 主文 ----//
    //傳送 CC/BCC 
    //$mail->addCC("personC@gmail.com", "person C"); //設定收件人電郵及名稱(CC)
    //$mail->addBCC("personD@gmail.com", "person D"); //設定收件人電郵及名稱(BCC)
    //$mail->addAttachment("image1.jpg", "picture.jpg"); //設定附件, 對方會看到附件名稱為 picture.jpg

    return $mail->send();

}




?>