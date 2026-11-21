<?php

//error_reporting(0);
//////////////////////////// mb_chat_func.php //////////////////////////////////////
//////////////////////////// 管理者資料處理功能執行檔案 ///////////////////////////////
//////////////////////////// 2025-01-25 By Jaz Tien ///////////////////////////////
//////////////////////////// jaztien@gmail.com ////////////////////////////////////
//-------------------------------------------------------------------------------//
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/common.lib.php');
//-------------------------------------------------------------------------------//
//若有 TOKEN 驗證要求時，進行雙重認證。
$datapass = true;
$message = '';
if(!isset($_POST['token']) || $_POST['token']=='' || empty($_SESSION['token'])){
    $datapass = false;
	$message = '遺失TOKEN驗證值，檔案下載失敗！請聯絡相關管理人員！！';
} else {
    if($_POST['token'] != $_SESSION['token']){
        $datapass = false;
		$message = 'TOKEN驗證值比對不符，檔案下載失敗！請聯絡相關管理人員！！';
    }
}
$downFile = isset($_POST['downFile']) ? $_POST['downFile'] : '';
$origTitle = isset($_POST['origTitle']) ? $_POST['origTitle'] : '';
$downFilePath = '../uploadFiles/chat/'. $downFile;
if(empty($downFile)){
	$datapass = false;
	$message = '遺失必要傳遞值，檔案下載失敗！請聯絡相關管理人員！！';
}
if(!file_exists($downFilePath)) {
	$datapass = false;
	$message = '檔案不存在，檔案下載失敗！請聯絡相關管理人員！！';
}
if($datapass == false){
	$backURL = isset($_POST['backURL']) ? $_POST['backURL'] : '';
    echo "<script>alert('" . $message . "');location.href='" . $backURL . "';</script>";
    exit();
}
//-------------------------------------------------------------------------------//
//$fileName = basename($downFile);
//$origTitle = $fileName;
    header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="' . $origTitle . '"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($downFilePath));
	readfile($downFilePath);
	exit;

?>