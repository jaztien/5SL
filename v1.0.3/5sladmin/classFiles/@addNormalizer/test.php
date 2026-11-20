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
require_once('../../includeFiles/common.lib.php');
require __DIR__.'/vendor/autoload.php';
use Recca0120\Twzipcode\Address;
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
<title>Fifth Season Life 人生第五季 | Address Normalizer Test</title>
</head>

<body>


<form id="transform" name="transform" action="#" method="post">
  <input type="hidden" id="pass" name="pass" value="pass" style="width:400px;height:24px;">
  <button type="button" onClick="window.location.replace('test.php');" style="width:60px;height:40px;border-radius:5px;cursor:pointer;">重置</button>&nbsp;&nbsp;&nbsp;&nbsp;
  <button type="submit" style="width:80px;height:40px;background-color:#cd5757;color:#FFFFFF;border-radius:5px;cursor:pointer;">執行</button>
</form>
<br>
<!-- <input type="text" id="transTo" name="transTo" value="<?php //echo $transTo; ?>" style="width:400px;height:24px;"> -->
<br>
<?php

if(!isset($_POST['pass'])){
    return false;
}
$res = '';
$res = Address::set('台北市中正區中華路１段25號');
//$res = $Address->set('台北市大安區東大路一段100號');



echo "<pre>";
print_r($res);
echo "</pre>";
exit();
?>

</body>
</html>