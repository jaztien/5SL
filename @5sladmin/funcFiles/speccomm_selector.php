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
if( empty($_POST['arrDatA'])){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
//-------------------------------------------------------------------------------//
define('arrDatA',(isset($_POST['arrDatA'])) ? $_POST['arrDatA'] : null);
//-------------------------------------------------------------------------------//
   /*
    $res = $DB->query("SELECT comm_id,comm_title FROM data_comms WHERE comm_district IN (:comm_district) AND comm_status='1'",array('comm_district' => arrDatA));
    if($res){
        echo json_encode($res); 
    } else {
        return false;
    }
    */
    $arrAY=array();
    $loc = $DB->query("SELECT loc_title,loc_code FROM data_locations WHERE loc_code IN (:loc_code) AND loc_depth='3' AND loc_status='1'",array('loc_code' => arrDatA));
    $i=0;
    while($i<count($loc)){
        $res = $DB->query("SELECT comm_id,comm_title FROM data_comms WHERE comm_district=? AND comm_status='1'",array($loc[$i]['loc_code']));
        $arrA = array('coDe'=>$loc[$i]['loc_code'],'lTit'=>$loc[$i]['loc_title'],'coMm'=>$res);
        array_push($arrAY,$arrA);
        $i++;
    }

    echo json_encode($arrAY); 
?>