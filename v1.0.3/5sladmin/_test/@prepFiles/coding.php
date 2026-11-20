
<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	
  <title> 人生第五季 Fifth Season Life </title>
  </head>
<body>
    <hr>
    <div style="font-size:16px;line-height:20px;">


        <?php
/*
        echo rawurlencode('您的人生第五季5SeasonLife帳戶的手機驗證碼為: 4567');

        echo "<br>";
        echo "<br>";
        echo "<br>";

        //echo rawurldecode('%B4%FA%B8%D5A');
*/


$curl = curl_init();
// url
$url = 'https://smsapi.mitake.com.tw/api/mtk/SmSend?'; $url .= 'CharsetURL=UTF-8';
// parameters
$data = 'username=90113379SMS'; $data .= '&password=90113379ABC'; $data .= '&dstaddr=0919218661'; $data .= '&smbody=簡訊SmSend測試';
// 設定curl網址
curl_setopt($curl, CURLOPT_URL, $url);
// 設定Header
curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded") );
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data); curl_setopt($curl, CURLOPT_HEADER,0);
// 執行
$output = curl_exec($curl); curl_close($curl);
echo $output;




        ?>



    </div>
</body>
</html>

