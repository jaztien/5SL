<?php
//error_reporting(0);
//////////////////////////// comm.selector.php //////////////////////////////////////
//////////////////////////// 建案資料回傳 API ///////////////////////////////
//////////////////////////// 2023-01-30 By Jaz Tien ///////////////////////////////
//////////////////////////// jaztien@gmail.com ////////////////////////////////////
//-------------------------------------------------------------------------------//
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//require_once('../includeFiles/common.lib.php');
//error_reporting(0);
//-------------------------------------------------------------------------------//
if( empty($_POST['arrDatA'])){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
//-------------------------------------------------------------------------------//
//define('merchID',(isset($_POST['merchID'])) ? intval($_POST['merchID']) : 0); 
$arrDatA = isset($_POST['arrDatA']) ? $_POST['arrDatA'] : null;
$commSelector = isset($_POST['commSelector']) ? $_POST['commSelector'] : 0;
$circleSelector = isset($_POST['circleSelector']) ? $_POST['circleSelector'] : 0;
$multiSelect = !empty($arrDatA) && is_array($arrDatA) ? 1 : 0;
$cDateTime = date('Y-m-d H:i:s');
//-------------------------------------------------------------------------------//

$commArray = array();
$circleArray = array();
$multi = is_array($arrDatA);
/*
echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>$multi));
exit();
*/

if(!empty($commSelector)){
    //$resARR = array();
    if(empty($multiSelect)){
        $sql = "SELECT bc_id,bc_title,bc_city,bc_district FROM buildcase WHERE bc_district=? AND bc_status<=? ORDER BY bc_title ASC";
        $res = $DB->query($sql, array($arrDatA,5));
        //$commArray= $res;
    } else {
        $sql = "SELECT bc_id,bc_title,bc_city,bc_district FROM buildcase WHERE bc_district IN ('".implode("','",$arrDatA)."') AND bc_status<=? ORDER BY bc_district, bc_title ASC";
        $res = $DB->query($sql, array(5));
        //$commArray= $res; 
    }
/*
    foreach($res as $key => $value){
        $bcARRs = array(
            'bc_id' => $res['bc_id'],
            'bc_title' => $res['bc_title'],
            'bc_district' => $res['bc_district']
        );
        array_push($resARR, $bcARRs);
    }
*/
    //$keys = array_column($res, 'bc_district');
    //$commArray= array_multisort($keys, SORT_ASC, $res);

   $commArray= $res; 
}
if(!empty($circleSelector)){
    $cres = $DB->query("SELECT scrc_id,scrc_title,scrc_circletitle FROM socialcircle WHERE TRANSLATE(scrc_district, '[]', ',,') LIKE '%,".$arrDatA.",%' AND scrc_status=? ORDER BY scrc_title ASC",array(1));
    if($cres){
        $circleArray= $cres; 
    }
}
$mergeArray = array('comm'=>$commArray,'circle'=>$circleArray);
echo json_encode($mergeArray);

?>