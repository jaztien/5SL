<?php
//////////////////////////// ad.act.func.php //////////////////////////////////////
//////////////////////////// 管理者資料處理功能執行檔案 ///////////////////////////////
//////////////////////////// 2023-01-30 By Jaz Tien ///////////////////////////////
//////////////////////////// jaztien@gmail.com ////////////////////////////////////
//-------------------------------------------------------------------------------//
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//error_reporting(0);
//-------------------------------------------------------------------------------//
if( empty($_POST['arrDatA']) || empty($_POST['mbID'])){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
//-------------------------------------------------------------------------------//
define('mbID',(isset($_POST['mbID'])) ? intval($_POST['mbID']) : 0); 
define('arrDatA',(isset($_POST['arrDatA'])) ? $_POST['arrDatA'] : null);
define('cDateTime', date('Y-m-d H:i:s'));
//-------------------------------------------------------------------------------//
   /*
    $res = $DB->query("SELECT comm_id,comm_title FROM fh.data_comms WHERE comm_district IN (:comm_district) AND comm_status='1'",array('comm_district' => arrDatA));
    if($res){
        echo json_encode($res); 
    } else {
        return false;
    }
    */
    $arrAY=array();
    $loc = $DB->query("SELECT exp_id,exp_title FROM fh.data_experts WHERE exp_id IN (:exp_id) AND exp_pid='0' AND exp_status='1'",array('exp_id' => arrDatA));
    $i=0;
    while($i<count($loc)){
        $res = $DB->query("SELECT exp_id,exp_title FROM fh.data_experts WHERE exp_pid=? AND exp_status='1'",array($loc[$i]['exp_id']));
        $arrA = array('iD'=>$loc[$i]['exp_id'],'lTit'=>$loc[$i]['exp_title'],'exP'=>$res);
        array_push($arrAY,$arrA);
        $i++;
    }

    echo json_encode($arrAY); 
?>