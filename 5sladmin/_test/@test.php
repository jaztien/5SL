<?php
error_reporting(E_ALL);
//require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//require_once('../includeFiles/common.lib.php');
//require_once('../includeFiles/QPay.inc.php');

//echo "test";
//phpinfo();
$cDateTime = date('Y-m-d H:i:s');
$nowTimeline = (date('G')*4) + floor(intval(date('i'))/15);
echo $nowTimeline;
exit();

$posterAvatar = array();
$mblist = array();
$cmbres = $DB->query("SELECT mb_id FROM fh.chat_members WHERE cg_id=?", array(1001));

for($i=0;$i<count($cmbres);$i++){
  $mbres = $DB->row("SELECT mb_id,mb_title FROM fh.members WHERE mb_id=?", array($cmbres[$i]['mb_id']));
  $mblist[] = $mbres;
  if(file_exists('uploadFiles/mb/mbphoto_'.$mbres['mb_id'].'_thumb.jpg')){ $posterAvatar[2][$mbres['mb_id']] = 'uploadFiles/mb/mbphoto_'.$mbres['mb_id'].'_thumb.jpg'; }
  else { $posterAvatar[2][$mbres['mb_id']] = 'imageFiles/nopic_user_50x50.jpg'; }
}  

echo '<pre>';
print_r($posterAvatar);
echo '</pre>';


exit();

/*
$serverName = "localhost";
$connectionInfo = array( "Database"=>"FH", "CharacterSet" => "UTF-8", "UID"=>"fh", "PWD"=>"fullhouse2023");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
  die( print_r( sqlsrv_errors(), true));
}

$sql = "SELECT chat_postid,chat_published FROM fh.data_chats WHERE chat_posttype=2 GROUP BY chat_postid ORDER BY chat_published ASC";
$stmt = sqlsrv_query( $conn, $sql );

if( $stmt === false) {
  die( print_r( sqlsrv_errors(), true) );
}

while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
{
  //echo $row[0]."-----".$row[1]."<br/>";
  echo '<pre>';
  print_r($row);
  echo '</pre>';

}
//
*/
//echo '<pre>';
//print_r($row);
//echo '</pre>';
/*
$preArr = array(
  ' ','你好','您好','請問','請問一下','請問你','請問您',
  '請教','請教一下','請教你','請教您','hello',
  'hi','hihi','求助','求助一下','幫忙','幫幫忙','幫忙一下',
  '請給我','為何','為什麼','是什麼'
);
*/
$message = '您好！！請問什麼叫做「開放式服務」？另外，什麼又叫做「轉讓」？';

echo "<pre>";
print_r(_fn_message_filter($message));
echo "</pre>";

exit();



foreach ($preArr as $startWith) {
  if (str_starts_with($startWith, $message)) {

  }
}








//$sql = "SELECT chat_postid, chat_poster, chat_message, sum(chat_status) as totCost FROM fh.data_chats WHERE chat_posttype = ? ORDER BY chat_published ASC";
//$res = $DB->query($sql, array(2), PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC | PDO::FETCH_NUM);
$sql = "
SELECT chat_postid, chat_published, chat_poster, chat_message, 
dateadd(S, chat_published, '1970-01-01 00:00:00') AS datetime, 
(SELECT COUNT(*) FROM fh.data_chats e WHERE e.chat_postid = a.chat_postid AND e.chat_status = ?) AS toT, 
(SELECT mbol_onchat FROM fh.mb_online o WHERE o.mb_id = a.chat_postid) AS oNline  
FROM fh.data_chats a WHERE a.chat_posttype = ? ORDER BY oNline DESC, a.chat_published ASC";
$res = $DB->query($sql, array(2,2), PDO::FETCH_GROUP | PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);

echo '<pre>';
print_r($res);
echo '</pre>';
exit();



$mbID=3;
$sql = "SELECT * FROM fh.mb_points WHERE mb_id=? AND mbp_status>? AND mbp_status<? ORDER BY mbp_id ASC";
$res = $DB->query($sql, array($mbID,0,10));
$nowAvil = 0;
$i=0;
while($i<count($res)){
  $nowAvil += intval($res[$i]['mbp_trade']+$res[$i]['mbp_freepoints']);
  echo $res[$i]['mbp_id'].'.['.$res[$i]['mbp_trade'].']['.$res[$i]['mbp_freepoints'].'] '.$nowAvil.'<br>';

  $sql = "UPDATE fh.mb_points SET mbp_available=? WHERE mbp_id=?";
  $DB->query($sql, array($nowAvil,$res[$i]['mbp_id']));

  $i++;
}

$nowAvilO = $DB->getTotalPoints($mbID);
echo '<pre>';
print_r($nowAvilO);
echo '</pre>';


//$res = $DB->query("SELECT mbp_available FROM fh.mb_points WHERE mbp_id=(SELECT max(mbp_id) FROM fh.mb_points WHERE mb_id=? AND mbp_status>?)", array($mbID,0));
$res = $DB->single("SELECT mbp_available FROM fh.mb_points WHERE mb_id=? AND mbp_status>? ORDER BY mbp_id DESC", array($mbID,0));
//echo '<pre>';
print_r($res);
echo '</pre>';
exit();
//$return = $DB->_DB_userlevel_update(96,1);
echo '<pre>';
print_r($return);
echo '</pre>';

exit();



$inArr = array(
  "iv_published" => "2024-12-19 12:23:19",
  "iv_updated" => "2024-12-19 12:23:19",
  "mb_id" => 96,
  "pay_id" => 54,
  "pay_orderno" => "C20241219122309",
  "iv_invoiceno" => "HF88880016",
  "iv_invoicedate" => "2024-12-19",
  "iv_buyer" => "1234",
  "iv_buyerid" => "0000000000",
  "iv_mailto" => "",
  "iv_mailadd" => "安祥路67巷50弄15號6樓",
  "iv_phone" => "0919218661",
  "iv_email" => "jaztien@gmail.com",
  "iv_beforetax" => 257,
  "iv_tax" => 13,
  "iv_aftertax" => 270,
  "iv_donate" => 1,
  "iv_donateto" => "25885",
  "iv_carrier" => "/+F54786",
  "iv_randcode" => "3592",
  "iv_status" => 1
);
$payUp = $DB->update('fh.data_payments', array('iv_id'=>60), array('pay_id'=>5));//
//$invIns = $DB->insert('fh.invoices',$inArr);
if($payUp){
  //$extraMess .= "電子發票已開立並已建立發票記錄，";
 echo $payUp;
} else {
  //$extraMess .= "電子發票已開立但發票記錄新增失敗，";
  echo $payUp;
}

exit();





//$InvoiceDate = date("Y-m-d", strtotime('2024/12/12'));
$lvlupdate = $DB->_DB_userlevel_update(96,3);
echo '<pre>';
print_r($lvlupdate);
echo '</pre>';

exit();




//$jsSTR=file_get_contents("test.js");
//{"OrderNo":"A20241128003236","ShopNo":"NA0443_001","TSNo":"NA044300000171","Amount":27000,"Status":"S","Description":"S0000 – 處理成功","Param1":"iass8ePCjL+snoUxggbcM7Wv2HpWxefvv+u5Krm31lI22ER68lo83xtUUtBdvoDtNF+yiSY8EwEeKD0sva5jbcQl7ftquPFVdMbIinMVKf8GUfamywC5Ds5eVcYu+u8C","Param2":"PZLIE7cJ7UotX1+YxC56JqhVIJqagyh7FoU6u8+wJD1jztv5Bp7vLvR2wlGXUGhCEjDsRckz8VCnyK2+VGMttjMsUgu6hW4IQ0vhuYETCM3STq+z/r7aQTOHRip12mtE","PayType":"A","ATMParam":{"AtmPayNo":"99922530276581","WebAtmURL":"https://sandbox.sinopac.com/QPay.WebPaySite/Bridge/PayWebATM?TD=NA044300000171&TK=2d27e0b9-e874-4cb1-bb6c-bd33eb96f153"}}

$invoiceInfo = "N,/1234ABC,1234,0000000000,田恕仁,0919218661,安祥路67巷50弄15號6樓,1,RS,jaztien@gmail.com";
$invoiceInfo = _fn_secure_crypto($invoiceInfo);
$para2 = 'AxSsXV+B5kRRAJxS9gek3QHOeomVDuKkylwrPvxnjv5drcYEcX93Q6rHyrpH3ywhmzXGy4FeS35RxbIw90GMJBJbjarbNqotz56fx7tEGzpT+kZTgjusSCYoFzDRbbZ4mOPRIQsCWTxmSpiHt7aVHQ==';
/*
//echo $invoiceInfo;
echo '<pre>';
print_r(explode(',',_fn_secure_crypto($para2, 'de')));
echo '</pre>';
exit();
*/
$payAmount = 300;
$OrderNo = "A20241128003296";

$param2Obj = explode(',',_fn_secure_crypto($para2, 'de'));//自填寫的發票訊息
$randCode = str_pad(rand(1,9999),4,'0',STR_PAD_LEFT);//發票用四位隨機碼
$bfTax = round($payAmount / 1.05);
$tax = round($payAmount - $bfTax);

$sendParam = array();
$sendParam['printOut'] = $param2Obj[0];
$sendParam['carrier'] = $param2Obj[1];
$sendParam['buyer'] = $param2Obj[2];
$sendParam['buyerID'] = $param2Obj[3];
$sendParam['donate'] = $param2Obj[7];
$sendParam['donateTo'] = $param2Obj[8];
$sendParam['dataNum'] = $OrderNo;        //線上支付單據號碼
$sendParam['items'] = array(
  array(
    "Description" => "廣告刊登及課程",	   //品名
       "Quantity" => "1",				        //數量
       "Unit" => "",					          //單位(非必填可為空白)
      "UnitPrice" => $bfTax,			      //單價
       "Amount" => $bfTax,			        //金額
   "SequenceNumber" => "1",				      //明細排列序號
       "Remark" => ""					          //單一欄位備註(非必填可為空白)
    )
);
$sendParam['amount'] = $bfTax;
$sendParam['tAmount'] = $tax;
$sendParam['total'] = $payAmount;
$sendParam['contact'] = array(
  "Name" => $param2Obj[4],				      //收件人
  "Address" => $param2Obj[6],			      //地址
    "TEL" => $param2Obj[5],				      //電話
  "Email" => $param2Obj[9]				      //Email
);
$sendParam['randCode'] = $randCode;			//發票用四位隨機碼

$sendInvoice = $DB->_DB_invoice_requestSend($sendParam);

echo '<pre>';
print_r($sendInvoice);
echo '</pre>';


				/*
				Array
				(
					[UXB2B] => 電子發票系統
					[Result] => Array
						(
							[value] => 1
							[timeStamp] => 2024-12-16T13:40:29.842711+08:00
						)
					[Automation] => Array
						(
							[0] => Array
								(
									[Status] => 1
									[Description] => 
									[Invoice] => Array
										(
											[SellerId] => 90113379
											[InvoiceNumber] => HF88880002
											[DataNumber] => A20241128003296
											[InvoiceDate] => 2024/12/16
											[InvoiceTime] => 13:40:29
											[CarrierType] => 3J0002
											[CarrierNo] => /1234ABC
											[CarrierNo2] => /1234ABC
										)
								)
						)
					[randCode] => 8245
				)
				*/








//print_r($param2Split);
//echo $sendInvoice;
exit();

// 兒童福利聯盟基金會 // 885521
// 伊甸社會福利基金會 // 25885
// 陽光社會福利基金會 // 13579
// 創世基金會 // 919
// 喜憨兒基金會 // 88432
// 兒童癌症基金會 // 88888
// 罕見疾病基金會 // 9527
// 家扶基金會 // 024
// 台灣之心愛護動物協會 // 978



//echo OrderCreate4Card();
//$rtn = _fn_OrderCreate('C',10000,'1','2','3');
//$rtn = OrderCreate4Card();
$mbID = 96;
$merchSN = 'MS20241215002';
$cDateTime = date('Y-m-d H:i:s');
$cTime=time();
$pubDays = '60';
$mbMail = 'jaztien@gmail.com';
$mbUser = 'JazMonster';
if($DB->doSendEmail(
  'PUBSUCCESS',
  ["[@username@]","[@userid@]","[@merchSN@]","[@currdate@]","[@startdate@]","[@enddate@]","[@linkurl@]"],
  [$mbUser,$mbID,$merchSN,$cDateTime,date('Y-m-d H:i:s',$cTime),date('Y-m-d H:i:s',$cTime + (intval($pubDays) * 86400)),'https://www.full-house.com.tw/Home/ServiceCenter'],
  '',
  '',
  [[$mbMail,$mbUser]]
)){
  echo "Done!";
}

//echo $DB->_DB_invoice_requestSend();
//$buyerCode = str_pad(rand(1,9999),4,'0',STR_PAD_LEFT);
//echo $buyerCode;
exit();


function strhex($string){
   $hex="";
   for ($i=0;$i<strlen($string);$i++)
       $hex.=(strlen(dechex(ord($string[$i])))<2)? "0".dechex(ord($string[$i])): dechex(ord($string[$i]));
   return $hex;
}

$encode = hash('sha256', '901133793a0a006c-683d-4380-947e-da5a13f9b0eaPiggyTailOnMyCat', true); // 1f5fc628ec5ede2eb16641385f80c92dca6babd17cb980cfaf986fb204887d05
$encode = bin2hex($encode);// 31663566633632386563356564653265623136363431333835663830633932646361366261626431376362393830636661663938366662323034383837643035
$encode = base64_encode($encode);// 

//echo bin2hex($encode);

echo $encode;
exit();
//echo $DB->doSendEmail($codE='OPPNOTIFY',$subJect='',$htmlBody='',$setTo=[['jaztien@gmail.com','mailto: ']],$setCC=[],$setBCC=[],$markerARR=["[@currdate@]","[@username@]","[@userid@]"],$replaceARR=['2024-12-03 12:22:22','Jaz Tien','96']);
$res = $DB->row("SELECT arti_subscript,arti_body FROM fh.articles WHERE arti_keycode=? AND arti_status=?",array('OPPNOTIFY',1));//'OPPNOTIFY'
$origBody = $res['arti_subscript'].$res['arti_body'];
//$origBody = 'sdfgh shfsdfhsdfghsfghs rsthj rehert rth';
$replaceVal = array();
$replaceTag = array();
if(str_contains($origBody, '[@')){
  $splitLeft = explode('[@',$origBody);
  for($i=1;$i<count($splitLeft);$i++){
    $splitRight = explode('@]',$splitLeft[$i]);
    if (in_array($splitRight[0], $replaceVal)) {
      continue;
    }
    $replaceVal[] = $splitRight[0];
    $replaceTag[] = '[@'.$splitRight[0].'@]';
  }
}

//$string = array_unique($string);
//rsort($string);
print_r($replaceArr);
exit();

//print_r($rtn);
$geJS=_fn_getCachedJsonData("../jsonFiles/general.json");
$getForInfo = !empty($geJS['idfor'][12]) ? $geJS['idfor'][12] : array();//紀錄
$getFromInfo = !empty($getForInfo['joinid']) ? (!empty($geJS['idfor'][$getForInfo['joinid']]) ? $geJS['idfor'][$getForInfo['joinid']] :array()) : array();//項目

//echo !empty($geJS['idfor'][12]['joinid']) ? "yes" : "no";
//echo !empty(array()) ? "yes" : "no";
//print_r($getFromInfo);

//echo $DB->getIDFORtitle(3,$getFor $DB->getIDFORtitle(3,$getForInfo,$getFromInfo);

/*

$fromTable="fh.merch_oppurchase";//取得Title的資料表
$joinTable="fh.merch_oppdata";//僅有紀錄的資料表
$fromTITLEcol="opp_title";//Title的欄位名稱
$fromIDcol="opp_id";//Title的相關ID值
$joinIDcol="opd_id";//僅有紀錄的ID值

$sql = "SELECT t1.{$fromTITLEcol} FROM {$fromTable} AS t1 INNER JOIN {$joinTable} AS t2 ON t1.{$fromIDcol}=t2.{$fromIDcol} WHERE t2.{$joinIDcol}=?";

//$sql = "SELECT t1.opp_title FROM fh.merch_oppurchase AS t1 INNER JOIN fh.merch_oppdata AS t2 ON t1.opp_id=t2.opp_id WHERE t2.opd_id=?";

$res = $DB->single($sql, array(11));
*/
//print_r($res);


exit();
/*

{"Version":"1.0.0","ShopNo":"NA0443_001","APIService":"AllotQuery","Sign":"6EB510BD0553FE2A2FA61190680A3801E46F8B1C8EADA83D314D529EFC02167C","Nonce":"NjM4Njc5MTI0ODU5MTkuNDo2YzFhMTY0MGZkZTkxMTIxMTRjNGJiYTcxNjEyZTRiNDRhODE5YTdkMjc0NjA4YmM5NjE4MGYzOTQ0NjFiMTQ3","Message":"A9815710ADD19E35A149938F63D1755862278CC937EDAC0FA3D6822E564B07C49061190704B64A4D7C518D0BDC8520B89D0AF7140F92D2094AC1B60E80ECFB3F5DC727BAFBA9DF20B6FAED7D1765D409B81219A0E297C901B36F48A9E93A899C2DB88A0E61EB71EE16EF8A736BB72F94ADA51074527BBB874AD5B36749032002F46FE4955D53C7A67AFD97D0E7EFDB6C5F9D0648964FAFE81D00AA433B848651"}
{"Version":"1.0.0","ShopNo":"NA0443_001","APIService":"OrderCreate","Sign":"C59BFCA6046A3C53062104CD6B99AAD10DA7C9EB5176031BA00135FB6C5EA16C","Nonce":"NjM4Njc5MTI0ODYwNDQuNDplNzRmMjNjYzEyNWJhYWY4YmUwZTM4ODRmM2JkZDA1NmQ2YjljY2QzMzgyOWE2Y2IyNTA2MTI2YTg2MmViMmMz","Message":"B6C4F1406172001CD0742170CCCF4706026CB8FC10CB97DE61C9AC064EAEF56AAE7CDAEB7F4C2B1F24153A7606A61F8788EC8066D29243D261F380BF6A42BD225FD23FD3BAACD9CCC8216DC5CD6382A514560EA7241F4368E41DF0F81BCD42F5F6F9310FF5C7D038D57982C2FFECEC03356E0A4A4E7F7DF6E2DEE767E1F49284830BAA8FEEFD5AB9CC0CE7742189B1DA8C0BE25D4FDD66CC1B7109B37C1C9F2D0FFAC8EB875DEDA0B336763A0A3816D3DD584814719CDB0C168EDAA338FF98395FD89C45AE4D546F2958D34A0466CEEE1E613F4E7CF652F0E4B74EC1742F35FE1CBF0D92F8D006EFBC190FE1AAA0E9CFD318B6FAF7CFB255447C3F85CEC7583D95D79A84C46072F886A2D324399B6F1B4A4AFFA45454DF7A327848685FBCF4310CD442CCF7ABCD0C980389C180BD98D3"}
Array ( 
      [OrderNo] => C20241122224805 
      [ShopNo] => NA0443_001 
      [TSNo] => NA044300000022 
      [Amount] => 50000 
      [Status] => S 
      [Description] => S0000 – 處理成功 
      [PayType] => C 
      [CardParam] => Array ( 
                            [CardPayURL] => https://sandbox.sinopac.com/QPay.WebPaySite/Bridge/PayCard?TD=NA044300000022&TK=937401f6-6b43-4ec4-a0b9-63b57caa2632
                            )
      )


Array ( 
      [Version] => 1.0.0 
      [ShopNo] => NA0443_001 
      [APIService] => OrderCreate 
      [Sign] => 286227A9FCD05A70410AE1CCE32B3FD91AC801C0ABAA25E78656FFA354DC5752 
      [Nonce] => NjM4Njc5MTMzNzMyNzEuNDo3NTJhZmFmNzgwNGZlYWY1YTgyOTU5MWEyMjQzZjFhZWNiZTg0ZTA5OWVkY2M2MmFlYzM4ODcyNjMzMGUxNWNl 
      [Message] => EC95F22A21CEDE824777473B79C78A57F42F2DF3C304B2BB7D676E18E8F367F024414B6E3F0CFBF268E44046FABF413DB91B8784CCA2D9AFD5E4F4A1ED9AADE7881F1B453E0F01109229137081465978F3EC8B64BC43DEEE5281206FD3480245EB1DFCD308FB0B60711820F647351977E77BBFCAD3E0FE5CCE3B6B19854D43F7EA702B86BBE0E9FAC27AD6680682648A57C36B241423A2D98279BCF85DF5CFFA58BB8C6588AA3A033B9D4DF70F2A5B774BF7E289A5F148B5FAB97D20ADC18B38A0918B92D1B848C99E88D52CE46F04CF5393A95CD56312A4FF4AE0CE298B918128310C69D12C514F18CB891EE96EB5DF7D4609A829EE8F0524BFD9711B844E1362252B57179E4A9E4D95D2ABA1620E3D7048920BCAE4536BE1974C3AD1A0A031345681F39CC241347974EFBCC3A1064D 
      )


594EF7B1E4971FCC6A826C6FF3289759D53E9B4DA5A7BA53DAE5516EEAB5972379B40DE3BD228C2F55D56BD0BC70AAF723283F19C022FCBD257ECC30A23C50F4D0EE84E3D8959B64582AF755541A01FCFA63BCCF481582BB3E27E0C7A0A429F4997F3020AA8ED4D3371B28F1FDB50F33DD8CD571A8ACFD22863A093F8E31456E92B3E9A5C4E05A3071F1C82A0C4BEDB575B74E796902DD5EFEB7EB8DD5BB152E22EE9701811BE72EFD8D38289D631967261502175ECE35F42B78B35A9F9A75D31B8A3452B9D8BE5526BEFFD1AD18A4CEC25002800B1F447C0CFFDF8D67709C3F679AD334064795F7FF95EF1B0F12C0F02068D910608A519962359E25C78988D2A76A41E9D746522E90DE278F750F464E9B313402695B13E8160095BA198E3DE430AD85CA67758E4AA3B115AD3D12DD170C6837800754730DF1B4AAF1BC91BDCA4BA790BECA24654C207941DEA5574826F4EC2899176B7D4904B50EA95F4071CC
98785C4D1987D24EF8C7952A14B66AB582D14A9C62BC0F029ECF3127DAAECA7D5A7370CFC86122F4BF8E9F41543A04FCFDEE5A4EB8F1FE8B9E39824F4E89989EED4E60AD9E112E53E3C8DCCC86176E21D7AC8E750900C3B9ABA358551856BEFD35FBD56292243E92EAADBFD2D61E5B06704A5AF375AE20D14A23B2B41FD84DA85C65D6000F4F0DF7F5195DD7C1FF934289121C3A6F479712C0168941639838DF680CB4BBE1FCF639FE456B5C7A51CEAE691EBACB6224F33AE8C15B9785C17EE34F0BA8D7F73C89A6C179EFFC925A80F3524CF3522A08F94AC2DE874E099468A6E252B2AD13E16B8D77022A5C734C5328B5E02F5D6C5310276241990F906323F2B8F211B8AC2C2349B1DB71A615DF4228D8CA8352D28A57662EF5A490570BD5E6876D3F8CBA7493E3B64054FAEBBCE683
826580AA14208E9CCCFE3BC30DC5DC2B1F89065E973318B4516EE877D1A8D072C7E4763EDE79135B787F749D834ECBD9BF83A377BF5E560EEA94D6F781C7702B229AC02DCAE6E7311F10CB59AC391619DD32C7948F649ABD2BAD5328A333A3C1D06C2D424C66B0D8399CF427EFDDD72A78306C307E862FD5177538E14E26DE8C2E6D72756E6880A73F6346932E31356848A0F7A823F2550BD31E5C7DCD5DB837CA12EE5D7214FC433E2E60CFD78FED69F1C5B54D46956FA9398CCF05F7332CA8C462ACE82FF7C49AA02B3F7FC218958F0A302B1691AC4CF7F99920BB77653DB61F7BF65097B5E7188F8A81F7433DE8D713180BFEDC0643E57765EC4BFA99083216F1CC93E3D6E73529B0F71CC1CFE2C580718D0964D9703359CF3A36AFD7A9CAF660035BCF9D3C6F1E19AF63603FAFC2ACCC9C452ADAC9E33FE53409F56D01DCCEED96637E64DBD439BCF4B4F10946CF4074A20C4460941F04D3A712BE59D3AE
*/

//$rtn = DecryptAesCBC('4C574C7BD63EE428A3A7C8F2ED788BC392BFDCE60092BF50B121043302585A8ED39FC00D7458537FBCC3260DBD90FF15F40BB55951918935BFC74476F4133EE221C24DD866BB99E949A78E76C2CB6E0F90DA50613706460174D6FCF3258F62F082FB83EEA4726639C9AC991E29AAA086575E665B601FB7935BE334537DD7343C7C2A9BA481F8351AABB402ABF6A3FF8F772CA7C9BD86F60F9FE91F8D445B7474245341159AABF6F52B8B8C4D5A019B6EDDF60034C021952A187216C8EB42426D02B07A2A68358905B05173883F22B6F939F0978832734B57BB8263FDB1961832B4713809F156EDDAB41A023F5A86B9D23640C2BF549A3346AE7DFCE0CA619517A87090FCC91E53617F4C7C7BDB5F1157B0D8AD34B2F74561BCFA0760FC6935EC1BB939870A50400FCA27A0E8B1AEEA85','AD7E945BBCEC0A1F68EA6F7940F4062F','AA45A711F2D707A6');
//print_r(json_decode($rtn,true)); 
//print_r($rtn); 
//echo $rtn."  ";
$nowdate = new DateTime();
$nowMonthSE = '_'.$nowdate->format('Ym').'01';
$nowdate->modify('last day of this month');;//PT5M//P10D
$nowMonthSE .= '-'.$nowdate->format('Ymd');
//
echo date('Y-m-d H:i:s');
//echo _fn_secure_crypto("cqZEuUT3ZzzwuxcwcmiZgzbh/KnFjCzZ7CcyB3fCYbpq1cL7AnNUQgHfCLL5yTiIgUCMC6FoaAa1pCmMxoVsKPRyFDvvSeqZRXNPeTQxgy8MHKhstE1z9ENm6ihKOd+S/VnXlfZhSviFEqvRAbSUt0pZKDe1ph2+zehJVa7W+7XhRUqz7Ju+0KHXwJg6vv8Z44lzoAYZfTK7gcaXCmTDNw==", 'de');
exit();

$cTime = time();
$cDateTime = date('Y-m-d h:i:s');
$donePublishMess = 0;
$pubDays = 60;
$merchID = 1149;
$opdUP = $DB->query("UPDATE fh.merch_oppdata SET opd_startdate='".$cTime."', opd_enddate='".$cTime + (86400 * $pubDays)."', opd_paid=opd_cost, opd_days='".$pubDays."', opd_status=1 WHERE merch_id=? AND opd_status IN (2,3)",array($merchID));

exit();
$emailTemplate = $DB->getEmailTemplate('OPPNOTIFY');
if($emailTemplate && !empty($emailTemplate['subject']) && !empty($emailTemplate['content'])){
/*
    $sendRTN = _fn_email_sending(
        $emailTemplate['content'],//自資料庫中取得的郵件主體
        $emailTemplate['subject'],//自資料庫中取得的郵標題
        [['jaztien@gmail.com','mailto: ']],//收件人郵箱&收件人名稱(複合陣列 [ [ email1,title1 ],[ [ email2,title2 ],... ] ] )
        [],//CC收件者郵箱&收件人名稱(複合陣列 [ [ email1,title1 ],[ [ email2,title2 ],... ] ] )
        [],//BCC收件者郵箱&收件人名稱(複合陣列 [ [ email1,title1 ],[ [ email2,title2 ],... ] ] )
        ["[@nowdate@]","[@currdate@]","[@username@]","[@userid@]"],//替換目標陣列
        [$cDateTime,"2024-06-08",'Jaz Tien','1']//替換值陣列 
    );
*/
    if (_fn_email_sending(
      $emailTemplate['content'],//自資料庫中取得的郵件主體
      $emailTemplate['subject'],//自資料庫中取得的郵標題
      [['jaztien@gmail.com','mailto: ']],//收件人郵箱&收件人名稱(複合陣列 [ [ email1,title1 ],[ [ email2,title2 ],... ] ] )
      [],//CC收件者郵箱&收件人名稱(複合陣列 [ [ email1,title1 ],[ [ email2,title2 ],... ] ] )
      [],//BCC收件者郵箱&收件人名稱(複合陣列 [ [ email1,title1 ],[ [ email2,title2 ],... ] ] )
      ["[@username@]","[@userid@]","[@merchSN@]","[@currdate@]","[@startdate@]","[@enddate@]","[@linkurl@]"],//替換目標陣列
      [$cDateTime,"2024-06-08",'Jaz Tien','1']//替換值陣列 
    )) {
        $donePublishMess = 1;
    }
}
echo $donePublishMess;

//echo _fn_secure_crypto('96|0919218661|1800000000');
//print_r( _fn_token_verify(96,'HHCsF7cIMqNu5SDw4x9T9w==','1800000000','uNB6aNo2YfHZH6aEeaqmHfOihF2NRHq5nlLDgM6MNFQ=') );
//print_r( _fn_get_mbpoints(96) );
//$oppTotal = $DB->single("SELECT SUM(opd_cost) FROM fh.merch_oppdata WHERE merch_id=? AND opd_status<>? AND opd_enddate<=?",array(1,9,time()));
//$res = $DB->getEmailTemplate('OPPNOTIFY');
//$chkCost = $DB->query("SELECT * FROM fh.merch_oppdata WHERE merch_id=? AND opd_mode IN (2,3) AND opd_status=? AND opd_cost=?",array(1,3,0));
//echo count($chkCost);
//echo $oppTotal;
//print_r( $res );
exit();

$commGet="<p><span style=\"font-size: 16px\">親愛的會員</span><span style=\"font-size: 18px\"><strong> [@username@] </strong></span><span style=\"font-size: 16px\">您好,</span></p><p><span style=\"font-size: 16px\"><br></span></p><p>您於 [@currdate@] 成功儲值了全房幣共計 [@inpoint@] 點，截至您收到這封郵件之前，您的帳戶還剩餘 [@nowpoint@] 點，未使用完畢的點數超過使用期限時，將於過期日後歸零。<br>若有任何問題或意見，請聯絡本站的電話服務人員，服務電話為 [@comptel@] 分機號碼 [@compext@]，人生第五季竭誠為您服務！</p><p>[@nowdate@]</p><div class=\"se-component se-image-container __se__float-none\"><figure><img src=\"../uploadFiles/sysimg/sysimg_20240901022256200.jpg\" alt=\"5SeasonLifeLOGOthumb\" data-rotate=\"\" data-proportion=\"true\" data-size=\",\" data-align=\"none\" data-percentage=\"auto,auto\" data-file-name=\"sysimg_20240901022256200.jpg\" data-file-size=\"0\" origin-size=\"100,100\" data-origin=\",\" style=\"\"></figure></div><p><br></p><p><br></p><p><br></p><p style=\"text-align: right\">**此為系統自動回覆郵件，請勿直接回覆<br></p><hr class=\"__se__solid\"><p><br></p><table><tbody><tr><td rowspan=\"2\"><div><br></div><div><br></div><div>客戶服務專線：<span style=\"font-size: 16px\">02-81018800</span><br>服務時間：週一至週六 9：00~18：00<br>信箱：service@full-house.com.tw<br>地址：110台北市xx區xx路xx號x樓<br></div></td><td><div class=\"se-component se-image-container __se__float-right\"><figure><img src=\"../uploadFiles/sysimg/sysimg_20240901024320832.jpg\" alt=\"5SeasonLifeLOGOemail\" data-rotate=\"\" data-proportion=\"true\" data-size=\",\" data-align=\"right\" data-percentage=\"auto,auto\" data-file-name=\"sysimg_20240901024320832.jpg\" data-file-size=\"0\" origin-size=\"205,76\" data-origin=\",\" style=\"\"></figure></div></td></tr><tr><td colspan=\"1\" rowspan=\"1\"><div style=\"text-align: right\">​<span style=\"font-size: 18px\"><strong>人生第五季數位科技股份有限公司</strong></span>​​<br></div></td></tr></tbody></table>";
$commGet = stripcslashes($commGet);
$commGetARR = explode("../uploadFiles/sysimg/",$commGet);
$fineNameARR=array();
for($i=1;$i<count($commGetARR);$i++){
  $getPureARR = explode('" alt="',$commGetARR[$i]);
  $getPureName = trim($getPureARR[0]);
  $getPureCID = pathinfo($getPureName, PATHINFO_FILENAME);
  $commGet=str_replace("../uploadFiles/sysimg/".$getPureName,"cid:".$getPureCID,$commGet);
  $fineNameARR[] = $getPureName;
}

@preg_match('/..\/Webstore\/uploadFiles\/sysimg\/(.*?)" alt="/', $commGet, $match);
//$getInfoArr = @explode('"',$match[1]);

//print_r($fineNameARR);
echo $commGet;


//$jsSTR=file_get_contents("../jsonFiles/articles.json");
/*
$sql = "SELECT * FROM fh.buildcase WHERE bc_district=? AND bc_status<=? ORDER BY bc_title ASC";
$res = $DB->query($sql, array(1102,5));

print_r($res);
*/

exit();
function _fn_FH_crypto2($str,$crypto='de') {
	$passCode = 'flushfullhouse';
	$encryptMode = 'AES-256-CBC';
	$keyCode = substr(hash('sha256', $passCode, true), 0, 32);
	//$ivCode = chr(112).chr(105).chr(103).chr(103).chr(121).chr(115).chr(101).chr(118).chr(101).chr(114).chr(121).chr(119).chr(104).chr(101).chr(114).chr(101);//piggyseverywhere
	$ivCode = chr(80).chr(105).chr(103).chr(103).chr(121).Chr(84).Chr(97).Chr(105).Chr(108).Chr(79).Chr(110).Chr(77).Chr(121).Chr(67).Chr(97).Chr(116);//PiggyTailOnMyCat
	if($crypto=='de'){
		$crypted = openssl_decrypt(base64_decode($str), $encryptMode, $keyCode, OPENSSL_RAW_DATA, $ivCode);
	} else {
		$crypted = base64_encode(openssl_encrypt($str, $encryptMode, $keyCode, OPENSSL_RAW_DATA, $ivCode));
	}
	return empty($crypted) || $crypted==false ? '' : $crypted;
}

$transBy = isset($_POST['transBy']) ? trim($_POST['transBy']) : '';
$transTo = !empty($transBy) ? _fn_FH_crypto2($transBy,'en') : '';
?>
<!DOCTYPE html>
<html lang="en">
  <head>	
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
<title>人生第五季 Fifth Season Life | Log in</title>
</head>

<body>


<form id="transform" name="transform" action="#" method="post">
<input type="text" id="transBy" name="transBy" value="<?php echo $transBy; ?>" style="width:400px;height:24px;">
<button type="submit">轉</button>
</form>
<br>
<input type="text" id="transTo" name="transTo" value="<?php echo $transTo; ?>" style="width:400px;height:24px;">
<br><br><br>
<button type="button" onClick="window.location.replace('test.php');">重置</button>


<?php


//$enAccount = _fn_secure_crypto('jazzmonster');
//echo _fn_FHMD5_encrypt($enAccount);
//echo _fn_FHMD5_encrypt('111111');
//echo _fn_FH_crypto2('11111111');
//echo $enAccount;
/*
$SMScode = 548625;
$SMSsend =  _fn_SMS_messageSend(5,'0919218661',$mess='親愛的會員,您的 5SeasonLife 人生第五季的簡訊驗證碼為 '.$SMScode.' ,請於5分鐘內完成驗證,謝謝!',610);

print_r($SMSsend);
*/


exit();

/*
      function _fn_FHMD5_encrypt($str) {
        $origString=md5($str);
        $saltH = substr($origString,0,12);
        $saltT = substr($origString,-12);
        return md5($saltH.$str.$saltT);
      }

      function _fn_token_create($mb,$acct,$expiry) {
        $jsStr = $mb."|".$acct."|".$expiry;
        //$jsStr = "['".$mb."','".$acct."','".$expiry."']";
        return $jsStr;
      } 
      function _fn_FH_crypto($str,$crypto='de') {
        $passCode = 'flushfullhouse';
        $encryptMode = 'AES-256-CBC';
        $keyCode = substr(hash('sha256', $passCode, true), 0, 32);
        $ivCode = chr(112).chr(105).chr(103).chr(103).chr(121).chr(115).chr(101).chr(118).chr(101).chr(114).chr(121).chr(119).chr(104).chr(101).chr(114).chr(101);//piggyseverywhere
        if($crypto=='de'){
          $crypted = openssl_decrypt(base64_decode($str), $encryptMode, $keyCode, OPENSSL_RAW_DATA, $ivCode);
        } else {
          $crypted = base64_encode(openssl_encrypt($str, $encryptMode, $keyCode, OPENSSL_RAW_DATA, $ivCode));
        }
        return $crypted;
      }
      */
      //$crypto =_fn_secure_crypto(_fn_token_create('123',_fn_secure_crypto('0919218661'),time()+86400));
      $expire = time()+86400;
      $crypto = _fn_secure_crypto(_fn_token_create('3','0919218661',1716732221));
      //echo "(".$expire.") ".$crypto;
      //echo _fn_FHMD5_encrypt('peter1258');
      //echo _fn_secure_crypto('0910013080');//
/*
      $encrypto ="H+1B6g7pbgjVzIW97KkxtsfBF8Gic8QQQm+HAaFPnjw=";
      $arr = explode('|',_fn_secure_crypto($encrypto, 'de'));
      echo date('Y-m-d H:i:s',$arr[2]);
*/
      //echo _fn_FH_decrypt('sURuRM9cJN0p2kZ0LRDLYUHubZQXLcdZKEbJB597ZyPBxW6vB19I2C9V3CJZuAI6');
      //echo _fn_secure_crypto(_fn_token_create('123',_fn_secure_crypto('0919218661'),time()+86400));
      echo _fn_secure_crypto(_fn_token_create('3','0919218661',1725380000));



      exit();
//--------------------------------------------------------------------------------------------
      function generate_pbkdf2_hash($password, $salt, $iterations, $length, $algorithm = 'sha256') {
        return hash_pbkdf2($algorithm, $password, $salt, $iterations, $length, true);
      }
      
      // 示例参数
      $password = 'Ralph';
      $salt = 'fullhouse';
      $iterations = 10000;
      $length = 40; // 输出长度（字节）
      
      // 生成哈希
      $hash = generate_pbkdf2_hash($password, $salt, $iterations, $length);
      echo "PHP 生成的哈希值: " . bin2hex($hash) . "\n";
      
      exit();
      
      
      $password = "Ralph";
      $iterations = 10000;
      
      // Generate a cryptographically secure random salt using random_bytes()
      //$salt = random_bytes(16);
      $salt = "fullhouse";
      
      $hash = hash_pbkdf2("sha256", $password, $salt, $iterations, 40);
      var_dump($hash);
      /*
      // for raw binary, the $length needs to be halved for equivalent results
      $hash = hash_pbkdf2("sha256", $password, $salt, $iterations, 10, true);
      var_dump(bin2hex($hash));
      */
      exit();
      
$a1 = array(
'a'=>1,
'b'=>2,
'3'=>3
);

$a2 = array('b'=>88);
$a1 = array_replace($a1,$a2);
$a3 = array();
echo empty($a3);
//print_r($a1);
exit();

$mbID = isset($_POST['mbID']) ? $_POST['mbID'] : 0;
$searchCity = isset($_POST['searchCity']) ? $_POST['searchCity'] : '';
$searchDistrict = isset($_POST['searchDistrict']) ? $_POST['searchDistrict'] : '';
$searchUsecate = isset($_POST['searchUsecate']) ? $_POST['searchUsecate'] : '';//merch_usecate
$searchBDcate = isset($_POST['searchBDcate']) ? $_POST['searchBDcate'] : '';//merch_bdcate
$searchAreaS = isset($_POST['searchAreaS']) ? $_POST['searchAreaS'] : '';//merch_certarea+merch_otherarea+merch_amearea
$searchAreaE = isset($_POST['searchAreaE']) ? $_POST['searchAreaE'] : '';//merch_certarea+merch_otherarea+merch_amearea
$searchPriceS = isset($_POST['searchPriceS']) ? $_POST['searchPriceS'] : '';//merch_listprice
$searchPriceE = isset($_POST['searchPriceE']) ? $_POST['searchPriceE'] : '';//merch_listprice
$searchAgeS = isset($_POST['searchAgeS']) ? $_POST['searchAgeS'] : '';//nowDate - merch_finish
$searchAgeE = isset($_POST['searchAgeE']) ? $_POST['searchAgeE'] : '';//nowDate - merch_finish
$searchRoom = isset($_POST['searchRoom']) ? $_POST['searchRoom'] : '';//merch_room
//merch_tstype ## 交易種類 ## 1-預售;2-自(租)售;3-託(租)售
$page = isset($_POST['page']) ? $_POST['page'] : 1;
$itemsPerPage = isset($_POST['itemsPerPage']) ? $_POST['itemsPerPage'] : 20;
$sortBy = isset($_POST['sortBy']) && (is_array($_POST['sortBy'])&& count($_POST['sortBy'])>0) ? $_POST['sortBy'] : ['merch_id'];
$sortDesc = isset($_POST['sortDesc']) && (is_array($_POST['sortDesc'])&& count($_POST['sortDesc'])>0)  ? $_POST['sortDesc'] :  ['ASC'];
$today=strtotime(date('Y-m-d'));

//$searchCity = 11;
/*
$searchAreaS = 30;
$searchAreaE = 100;
$searchPriceS = 1000;
$searchPriceE = 2500;
*/

//$searchAgeS = 1;
//$searchAgeE = 5;

$Ssql = "SELECT COUNT(*) OVER() 'totalNum'";
$Ssql .= ", SUM(CASE WHEN (merch_tstype='2') THEN 1 ELSE 0 END) OVER() 'selfNum'";
$Ssql .= ", SUM(CASE WHEN (merch_tstype='3') THEN 1 ELSE 0 END) OVER() 'agentNum'";
$Ssql .= ", (SELECT wl_id
            FROM fh.data_wishlist AS w
            WHERE w.mb_id = md.mb_id AND w.wl_for='4' AND w.wl_forid=md.merch_id) AS wlID
          ";
$Ssql .= ",merch_id,merch_title,merch_listprice,merch_usecate,merch_totalfloor,merch_onfloor,merch_bdfloor,merch_city,merch_district,merch_add,merch_tstype,merch_finish";
$Ssql .= ",(merch_certarea + merch_otherarea + merch_amearea) AS totalArea";
$Ssql .= " FROM fh.merchandise md WHERE md.merch_status=?";//,(GETDATE() - merch_finish) AS MDAge
if(!empty($searchCity)){ $Ssql .= " AND merch_city='".$searchCity."'"; }
if(!empty($searchDistrict)){ $Ssql .= " AND merch_district='".$searchDistrict."'"; }
if(!empty($searchUsecate)){ $Ssql .= " AND merch_usecate='".$searchUsecate."'"; }
if(!empty($searchBDcate)){ $Ssql .= " AND merch_bdcate='".$searchBDcate."'"; }
if($searchAreaS!=''&&$searchAreaE!=''){
  if($searchAreaS>0) { $Ssql .= " AND (merch_certarea + merch_otherarea + merch_amearea)>='".$searchAreaS."'"; } 
  if($searchAreaE>0) { $Ssql .= " AND (merch_certarea + merch_otherarea + merch_amearea)<='".$searchAreaE."'"; } 
}
if($searchPriceS!='' && $searchPriceE!=''){
  if($searchPriceS>0) { $Ssql .= " AND merch_listprice>='".$searchPriceS."'"; } 
  if($searchPriceE>0) { $Ssql .= " AND merch_listprice<='".$searchPriceE."'"; } 
}
if($searchAgeS!=''&&$searchAgeE!=''){
  $yearS = date('Y-m-d', strtotime('-'.$searchAgeE.' years',$today));
  $yearE = date('Y-m-d', strtotime('-'.$searchAgeS.' years',$today));
  $Ssql .= " AND (merch_finish IS NOT NULL AND merch_finish<>'1900-01-01')";
  if($searchAgeS>0) { $Ssql .= " AND merch_finish>='".$yearS."'"; } 
  if($searchAgeE>0) { $Ssql .= " AND merch_finish<='".$yearE."'"; } 
}
if($searchRoom!=''){ 
  if($searchRoom<=5){ $Ssql .= " AND merch_room='".$searchRoom."'"; }
  else{ $Ssql .= " AND merch_room > 5"; }
}
if(count($sortBy)>0 && count($sortDesc)>0) { 
  $setSort = ($sortDesc[0]==true) ? 'DESC' : 'ASC';
  $Ssql .= " ORDER BY ".$sortBy[0]." ".$setSort; 
}
//$Ssql .= " ORDER BY merch_id ASC";
if(!empty($page)&&!empty($itemsPerPage)){
  $offsetNum = ($page - 1) * $itemsPerPage;
  $Ssql .= " OFFSET ".$offsetNum." ROWS FETCH NEXT ".$itemsPerPage." ROWS ONLY";
}

$Sres = $DB->query($Ssql,array(1));

$countArray = array('totalNum'=>$Sres[0]['totalNum'],'selfNum'=>$Sres[0]['selfNum'],'agentNum'=>$Sres[0]['agentNum']);
$searchArray = $Sres;

print_r($searchArray);


exit();
//$mbJS = json_decode($mbGet,true);
/*
$jsSTR='
const levelList = [
     { text: "無等級", value: "0" },
     { text: "一般會員", value: "1" },
     { text: "綠金會員", value: "2" },
     { text: "黃金會員", value: "3" },
     { text: "白金會員", value: "4" },
     { text: "鑽石會員", value: "5" }
   ];
   
const orgList = [
     { text: "個人", value: "0" },
     { text: "組織(公司)", value: "1" }
   ];

const cateList = [
     { text: "不分類", value: "0" },
     { text: "一般買售型會員", value: "1" },
     { text: "合作型會員", value: "2" },
     { text: "聘雇型會員", value: "3" },
     { text: "消費型會員(廣告or活動)", value: "4" }
   ];
   
';
*/
//echo $jsSTR;exit();

$jsSTR = str_replace('const ', '', $jsSTR);
$jsSTR = str_replace(' = [', ': [', $jsSTR);
$jsSTR = str_replace('];', '],', $jsSTR);
$jsSTR = preg_replace('/([a-z_]+)\:/ui', '"$1":', $jsSTR);
/* Remove spaces, tabs, new lines, etc. */

$jsSTR = str_replace(' ', '', $jsSTR);
$jsSTR = str_replace("\n", '', $jsSTR);
$jsSTR = str_replace("\t", '', $jsSTR);
$jsSTR = str_replace("\r", '', $jsSTR);

$jsSTR = "{".$jsSTR."}";

/* Clean JSON and encapsulate in brackets */
$jsSTR = str_replace('],}', ']}', $jsSTR);
$jsSTR = preg_replace('/\xEF\xBB\xBF/', '', $jsSTR);
//$jsSTR = preg_replace('/\x{feff}$/u', '', $jsSTR);
/* Remove unecessary trailing commas */
//$to_decode = str_replace(',}', '}', $to_decode);

//$jsSTR = '{'.substr($jsSTR, 0, -3).'}';


echo $jsSTR;
$jsJS=json_decode($jsSTR,true);
//preg_match_all('/\{([^}]+)\}/', $jsSTR, $matches);
//print_r($matches[1]);

echo "<br><br>";
//echo $jsJS['cateList'][1]['text'];
//print_r($jsJS);
echo array_search("聘雇型會員",$jsJS['cateList']);
?>

</body>
</html>