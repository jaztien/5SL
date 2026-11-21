<?php
session_start();
//////////////////////////// ad_sesspass_func.php /////////////////////////////////
//////////////////////////// 管理者資料處理功能執行檔案 ///////////////////////////////
//////////////////////////// 2024-07-22 By Jaz Tien //////////////////////////////
//////////////////////////// jaztien@gmail.com ///////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////
//--------------------------------- 外部關聯文件 ---------------------------------//
//////////////////////////////////////////////////////////////////////////////////
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

if(empty($_POST['formAcce'])){ 
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
$formAcce = trim($_POST['formAcce']);
//-------------------------------------------------------------------------------//
//$adID = isset($_POST['adID']) ? intval($_POST['adID']) : 0; 
$arrDatA = isset($_POST['arrDatA']) ? $_POST['arrDatA'] : null;
$cDateTime = date('Y-m-d H:i:s');
//////////////////////////////////////////////////////////////////////////////////
//----------------------------- 執行 formAcce 操作 ------------------------------//
//////////////////////////////////////////////////////////////////////////////////
switch ($formAcce) {
//////-------------------- form_reenterPass 鎖定頁面時再登入 ---------------------//
    case "form_reenterPass":
        $r = $DB->single("SELECT ad_password FROM fsl_administrator WHERE ad_id=?",array($arrDatA['adID']));
        if($r && password_verify($arrDatA['reenterPass'], $r)){//,_fn_FHMD5_encrypt($arrDatA['reenterPass'])
            $_SESSION["SESS_AD_ID"] = $arrDatA['adID'];
            $ilog = $DB->insSysLogs(51, $_SESSION['SESS_AD_ID'], 2051, '["'.$_SESSION['SESS_AD_ID'].'"]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success');
            echo json_encode($rtn);
            exit();
        }
        //$ilog = $DB->insSysLogs(51, $_SESSION['SESS_AD_ID'], 2050, '["'.$_SESSION['SESS_AD_ID'].'"]');//寫入操作記錄
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '密碼錯誤，請重新操作或聯絡管理人員！！');
        echo json_encode($rtn);

    break;


}  

?>
