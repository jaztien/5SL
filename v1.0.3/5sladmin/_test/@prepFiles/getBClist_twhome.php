<?php
    error_reporting(E_ALL); 
    header("Content-Type:text/html; charset=utf-8");
	include 'aprret_func.php';
	//include 'PHPExcel/Classes/PHPExcel.php';
    $host = 'localhost';
    $dbuser ='root';
    $dbpassword = '1111';
    $dbname = 'jazhome';

    //echo _fn_num_b2h("二十五");exit();
    $link = mysqli_connect($host,$dbuser,$dbpassword,$dbname);
    if($link){
        mysqli_query($link,'SET NAMES uff8');
        echo "正確連接資料庫";
    }
    else {
        echo "不正確連接資料庫</br>" . mysqli_connect_error();
    }

//----------------------------------------
//-----------------------------------------
    /*
    $link = @mysqli_connect("localhost", "root", "1111");
    if( !$link ) {
        echo "MySQL 連線失敗";
        return;
    }
    else
        echo "MySQL 連線成功<br>";
    */
    
    // 選擇資料庫
    //$db = mysqli_select_db($link, "jazhome");
    // 設定字元集 (這行要有, 不然下面新增資料的中文部分, 會是亂碼)
    //mysqli_query($link, "SET NAMES 'utf8'");
    /*
    // 新增資料 (DML)

    $result = mysqli_query($link, "insert into test_company values(3, '歡迎', '歡迎 tomkuo139')" );
    if( !$result )
        echo "新增資料失敗<br>";
    else
        echo "新增資料成功<br>";
    
    // 查詢資料 (Query)

    $result = mysqli_query($link, "select * from test_company");
    $row_cnt = mysqli_num_rows($result);
    echo "共有 " . $row_cnt . " 筆資料<br>";
    
    // 取得資料 (第幾個欄位 and 欄位值)
    while( $row = mysqli_fetch_row($result) )
    {
        foreach( $row as $item=>$val )  // $item 會是 0, 1, 2, ...
            echo $item . " = " . $val . "<br>";
    }
    
    // 取得資料 (欄位名稱 and 欄位值)
    mysqli_data_seek($result, 0);  // 指標移到第一筆資料
    while( $row = mysqli_fetch_assoc($result) )
    {
        foreach( $row as $item=>$val )  // $item 會是 Column Name
            echo $item . " = " . $val . "<br>";
    }
    */
    // 關閉連線
?>

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
////////////////////////////// TWHOME //////////////////////////////////////
$locJS=_fn_getCachedJsonDatac("rlocations.json");
//------------------------------------------



$pmaxNum = 1;
for($pi=1;$i<$pmaxNum;$pi++){

    $bcListURL = 'https://twhome.net/realsellprice/list?city=%E8%87%BA%E5%8C%97%E5%B8%82&district=中正區&page='.$pi;
    $getBClistStr = load_contents($bcListURL,true);
    $getBClist = get_between($getBClistStr,'<div class="n_sllist new_list ">','<div class="fenye">');
    $getSplit = explode('<div class="n_swd_lll_1 nz_boxty01 ">',$getBClist);
    //$bmaxNum = count($getSplit);
    $bmaxNum = 1;

    for($bi=0;$bi<$bmaxNum;$bi++){
        //-------- 取得詳細頁鏈接URL
        $getLink = strrchr($getSplit[$bi],'<a href="');
        $getLink = str_replace(['<a href="','">'],'',$getLink );
        $getLink = trim($getLink);
        //-------- 利用取得的鏈接中分解出城市、地區、建案名稱，並解碼
        $getInfo = explode('/',$getLink);
        $getCityName = urldecode($getInfo[3]);
        $getDistName = urldecode($getInfo[4]);
        $getBCName = urldecode($getInfo[5]);
        $getInfoLi = '"城市":"'.$locJS[$getCityName]['id'].'",';
        $getInfoLi .= '"地區":"'.$locJS[$getCityName]['dist'][$getDistName].'",';
        $getInfoLi .= '"建案名稱":"'.$getBCName.'",';
        //-------- 利用取得的鏈接截取建案詳細頁面資料
        $getBCinfoStr = load_contents($getLink,true);
        $getBCinfo = explode('<div class="cx_xx_left">',$getBCinfoStr);//過濾精簡資料字串1
        $getBCinfoLi = get_between($getBCinfo[1],'<ul>','</ul>');//過濾精簡資料字串2
        
        $getBCinfoLi = preg_replace('/\s+/', '', $getBCinfoLi);//格式化資料字串
        $getBCinfoLi = str_replace('<li>','"',$getBCinfoLi);//格式化資料字串
        $getBCinfoLi = str_replace('<span>','":"',$getBCinfoLi);//格式化資料字串
        $getBCinfoLi = str_replace('</span></li>','",',$getBCinfoLi);//格式化資料字串
        //-------- 取得建案特色描述
        $getBCintroLi = get_between($getBCinfoStr,'<h2 class="content" id="content0" style="letter-spacing: 1px; max-height: 25px;" >','<div class="showMore community_introduction_btn" id="more0">');//取得資料字串
        $getBCintroLi = str_replace('</h2>','',$getBCintroLi);//截取必要部份
        $getBCintroLi = '"社區介紹":"'.trim($getBCintroLi).'",';//格式化資料字串
        //-------- 取得建案座標
        $getBCgeoLi = get_between($getBCinfoStr,'{ lat: Number(',') },');
        $getBCgeoLi = str_replace('), lng: Number(',',"lng":',$getBCgeoLi);
        $getBCgeoLi = '"lat":'.$getBCgeoLi.',';//格式化資料字串
        //-------- 整合各部分格式化資料
        $getFInalARR = '{'.$getInfoLi.$getBCinfoLi.$getBCgeoLi.$getBCintroLi.'"status":"4"}';
        //-------- 轉換為 PHP 陣列
        $getArr = json_decode($getFInalARR, true);
        //-------- 輸出或寫入資料表



        print_r($getArr);
        echo "<hr>";

    }

    sleep(2);
}





?>
</div>
</body>
</html>