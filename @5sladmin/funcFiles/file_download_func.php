<?php

session_start();
$bkUrl = isset($_POST['bkUrl']) ? trim($_POST['bkUrl']) : '';
if(!isset($_POST['token']) || $_POST['token']=='' || empty($_SESSION['token'])){
    echo "<script>";
    echo "alert('遺失TOKEN驗證值，表單傳送失敗！請聯絡相關管理人員！！');";
    //echo "window.history.back();";
    echo "window.location.replace('".$bkUrl."');";
    echo "</script>";
    exit();
} else {
    if($_POST['token'] != $_SESSION['token']){
        echo "<script>";
        echo "alert('TOKEN驗證值比對不符，表單傳送失敗！請聯絡相關管理人員！！');";
        //echo "window.history.back();";
        echo "window.location.replace('".$bkUrl."');";
        echo "</script>";
        exit();
    }
}

//$cDateTime = date('Y-m-d H:i:s');
$downfile = isset($_POST['downfile']) ? trim($_POST['downfile']) : '';  //下載檔案的完整路徑包含檔名(實體路徑)
if(empty($downfile) || (!empty($downfile) && !file_exists($downfile))){
    echo "<script>";
    echo "alert('下載檔案路徑錯誤，或沒有這個檔案！！');";
    //echo "window.history.back();";
    echo "window.location.replace('".$bkUrl."');";
    echo "</script>";
    exit();
}
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.basename($downfile));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($downfile));
ob_clean(); //清除緩存
flush();
readfile($downfile);
exit();
?>