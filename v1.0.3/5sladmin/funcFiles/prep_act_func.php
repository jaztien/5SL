<?php
//////////////////////////// ad.act.func.php //////////////////////////////////////
//////////////////////////// 資料後備作業處理功能執行檔案 ///////////////////////////////
//////////////////////////// 2026-03-26 By Jaz Tien ///////////////////////////////
//////////////////////////// jaztien@gmail.com ////////////////////////////////////
//-------------------------------------------------------------------------------//
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
//error_reporting(0);
//-------------------------------------------------------------------------------//

if(!isset($_POST['token']) || $_POST['token']=='' || empty($_SESSION['token'])){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失TOKEN驗證值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
} else {
    if($_POST['token'] != $_SESSION['token']){
        //if(PAGEURL_BACK != PAGEURL_NOW){//避免前後頁都是同一頁時，造成的無限迴圈
          echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'TOKEN驗證值比對不符，表單傳送失敗！請聯絡相關管理人員！！'));
          exit();
        //}
    }
}
$datapass = true;
if( empty($_POST['formAcce'])){ 
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
$formAcce = $_POST['formAcce'];
if($formAcce=='func_prep_singleana') {

}
if($datapass == false){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}

//-------------------------------------------------------------------------------//
$bcCity = isset($_POST['bcCity']) ? $_POST['bcCity'] : null;
$bcDist = isset($_POST['bcDist']) ? $_POST['bcDist'] : null;
$bcTitle = isset($_POST['bcTitle']) ? $_POST['bcTitle'] : null;
$cDateTime = date('Y-m-d H:i:s');
//-------------------------------------------------------------------------------//
switch ($formAcce) {
    case "func_prep_singleana":////////////////////////////////////////// 更新管理者資料

        require_once("../classFiles/normalizer/bcNormalize.class.php");
        $bcJS=_fn_getCachedJsonData("../jsonFiles/buildcase.json");

        $bcNORM = new NormBC($bcTitle, '', '');
        $rString = $bcNORM->normalized();
        $rString['parseBCtype'] = intval(@$bcJS['rbcmode'][$rString['parseBCtype']]);
        if($rString && count($rString) > 0){
            //搜尋資料庫中關聯的建案
            $rSql = "SELECT bc_id, bc_city, bc_district, bc_title, bc_pureprefix, bc_puretitle, bc_purephase, bc_pureext , bc_mode, bc_type FROM buildcase WHERE bc_status > ?";
            if(!empty($bcCity)) $rSql .= " AND bc_city=".$bcCity;
            if(!empty($bcDist)) $rSql .= " AND bc_district=".$bcDist;
            $rSql .= " AND (";
            $rSql .= " bc_title LIKE '%".$rString['parseTitle']."%'";
            //$rSql .= " OR";
            //$rSql .= " bc_title=".$rString['parseTitle'];
            $rSql .= ")";
            //if(!empty($rString['parsePhase'])) $rSql .= " AND bc_purephase=".$rString['parsePhase'];
            $searchRes = $DB->query($rSql,array(0));


            $ilog = $DB->insSysLogs(CF_AD_ID,1,1110,'["'.CF_AD_ID.'","0"]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功析出建案名稱結構！！', 'upTime'=> $cDateTime, 'rString'=> $rString, 'searchRes'=> $searchRes);
        } else {
            $ilog = $DB->insSysLogs(CF_AD_ID,1,1010,'["'.CF_AD_ID.'","0"]');//寫入操作記錄//
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'分析建案名稱結構失敗！請聯絡相關管理人員！！');
        }
        echo json_encode($rtn);

    break;
    case "func_prep_xxxxxxxx":////////////////////////////////////////// func_prep_xxxxxxxx XXXXXXXX




        $ilog = $DB->insSysLogs(CF_AD_ID,1,2380,'["'.CF_AD_ID.'","'.$mdID.'"]');//寫入操作記錄
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'編輯視訊鏈結資料失敗！請重新操作或聯絡管理人員！！', 'upTime'=> $cDateTime, 'insID'=> $insI);
        echo json_encode($rtn);
    break;

}  

?>