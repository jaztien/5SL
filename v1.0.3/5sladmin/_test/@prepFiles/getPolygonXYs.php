<?php
error_reporting(E_ALL);
/*
header("Content-Type:text/html; charset=utf-8");
$serverName="localhost\SQLEXPRESS, 1433";
$connectionInfo=array("Database"=>"FH","UID"=>"fh","PWD"=>"fullhouse2023","CharacterSet" => "UTF-8");
$conn=sqlsrv_connect($serverName,$connectionInfo);
    if($conn){
        echo "Success!!!";
    }else{
        echo "Error!!!<br />";
        die(print_r(sqlsrv_errors(),true));
    } 
*/ 
//require_once('../includeFiles/init.inc.php');
session_start();
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
//require_once('../includeFiles/QPay.inc.php');
//24.949807931325,121.48293524888
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
  <input type="hidden" id="pass" name="pass" value="pass" style="width:120px;height:40px;">
  <input type="text" id="wayCode" name="wayCode" value="261259401" style="width:120px;height:40px;padding-left:12px;">&nbsp;&nbsp;<input type="text" id="wayVersion" name="wayVersion" value="1" style="width:30px;height:40px;padding-left:12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <button type="submit" style="width:80px;height:40px;background-color:#cd5757;color:#FFFFFF;border-radius:5px;cursor:pointer;">執行</button>&nbsp;&nbsp;
  <button type="button" onClick="window.location.replace('getPolygonXYs.php');" style="width:60px;height:40px;border-radius:5px;cursor:pointer;">重置</button>
</form>
<br>
<!-- <input type="text" id="transTo" name="transTo" value="<?php //echo $transTo; ?>" style="width:400px;height:24px;"> -->
<br>
<?php

if(!isset($_POST['pass'])){
    return false;
}
$wayCode = isset($_POST['wayCode']) ? trim($_POST['wayCode']) : '';
$wayVersion = isset($_POST['wayVersion']) ? "/".trim($_POST['wayVersion']) : '';
//$pres = $DB->query("INSERT INTO fh.coordtest (test_coordinates) VALUES(geography::STGeomFromText('POLYGON((121.48250546414639 24.962270301931753,121.48178686088256 24.961468089012836,121.4808978236427 24.961201316103235,121.48094310608357 24.960485773274605,121.48279612534824 24.958249144087244,121.48547720712833 24.958849139038843,121.48512168151467 24.9602824414218,121.48440306591255 24.960453887957723,121.4830793003297 24.960049273749718,121.48244389284996 24.96074191760051,121.48288262659051 24.961777447194567,121.48250546414639 24.962270301931753))', 4326))", array());
//使用CURL截取網頁內容

$setURL = "https://www.openstreetmap.org/api/0.6/way/".$wayCode.$wayVersion;
$getContent = _fn_load_contents($setURL,true);//取得搜尋結果頁面的所有內容
if($getContent == false){
    echo "取得頁面資料失敗！！";
    exit();
}

$inSplit = explode('<nd ref="', $getContent);
$splitNum = count($inSplit);
$inDetail = array();
$output = "";
for($i=1; $i<$splitNum; $i++){
    $subSplit = explode('"/>', $inSplit[$i]);
    if(!empty($subSplit[0])) {
        $inDetail[] = $subSplit[0];
        $nodeURL = "https://www.openstreetmap.org/api/0.6/node/".$subSplit[0];
        $getNodeContent = _fn_load_contents($nodeURL,true);
        $inContent = _fn_get_between($getNodeContent, 'lat="', '"/>');
        $subDetail = explode('" lon="', $inContent);
        $output .= $subDetail[0]." ".$subDetail[1]."&#10;";
    }
}
if(!empty($output)){
    $output = substr($output, 0, -5);
}
//$inDetail = _fn_get_between($getContent, '<way id="261259401" visible="true" version="1" changeset="20524395" timestamp="2014-02-12T16:01:41Z" user="小白棒棒" uid="1931420">', '<tag k="building" v="yes"/>');

//$inDetail = str_replace('  <nd ref="', "", $inDetail);

//echo $inDetail;
echo "<pre>";
print_r($output);
echo "</pre>";




?>

</body>
</html>