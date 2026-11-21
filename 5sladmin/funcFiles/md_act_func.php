<?php
//////////////////////////// ad.act.func.php //////////////////////////////////////
//////////////////////////// 管理者資料處理功能執行檔案 ///////////////////////////////
//////////////////////////// 2023-01-30 By Jaz Tien ///////////////////////////////
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
if($formAcce=='func_md_infoUpdate') {
    if(empty($_POST['mdID']) || empty($_POST['arrDatA']) || empty($_POST['arrDatB'])){ $datapass = false; }
} else if($formAcce=='form_addnew') {
    if(empty($_POST['arrDatA']) || empty($_POST['arrDatB'])){ $datapass = false; }
} else if($formAcce=='form_delete') {
    if(empty($_POST['mdID'])){ $datapass = false; }
} else if($formAcce=='form_photomana' || $formAcce=='filemana') {
    if(empty($_POST['mdID'])){ $datapass = false; }
}
if($datapass == false){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}

//-------------------------------------------------------------------------------//
$mdID = $_POST['mdID'] ?? 0; 
$arrDatA = $_POST['arrDatA'] ?? null;
$arrDatB = $_POST['arrDatB'] ?? null;
$arrDatC = $_POST['arrDatC'] ?? array();
$fiLe = $_POST['fiLe'] ?? null;
$deS = $_POST['deS'] ?? null;
//$arrNeed = isset($_POST['arrNeed']) ? $_POST['arrNeed'] : null;
$cDateTime = date('Y-m-d H:i:s');
//-------------------------------------------------------------------------------//
switch ($formAcce) {
    case "func_md_infoUpdate":////////////////////////////////////////// 更新管理者資料merch_startdate
        $arrDatA['merch_updated'] = '[EXPR]GETDATE()';
        $arrDatA['merch_finish'] = $arrDatA['merch_finish'] !== '' ? $arrDatA['merch_finish'] : null;
        $arrDatA['merch_startdate'] =  $arrDatA['merch_startdate'] !== '' ? $arrDatA['merch_startdate'] : null;
        $arrDatA['merch_enddate'] = $arrDatA['merch_enddate'] !== '' ? $arrDatA['merch_enddate'] : null;
        //$arrDatA['merch_statusdate'] = $arrDatA['merch_enddate'] !== '' ? $arrDatA['merch_enddate'] : null;
        
        $upA = $DB->update('fsl_merchandise', $arrDatA, array('merch_id'=>$mdID));
        $upB = $DB->update('merch_info', $arrDatB, array('merch_id'=>$mdID));
        if($upA==1 && $upB==1){
            if(count($arrDatC) > 0 ){
                $insARR = array(
                    "merch_id" => $mdID,
                    //"mdpr_create" => '[EXPR]GETDATE()',
                    "mdpr_listprice" => $arrDatC['merch_listprice'],
                    "mdpr_saleprice" => $arrDatC['merch_saleprice'],
                    "mdpr_landprice" => $arrDatC['merch_landprice'],
                    "mdpr_parkprice" => $arrDatC['merch_parkprice']
                );
                $ins = $DB->insert('merch_prices',$insARR);
            }
            $ilog = $DB->insSysLogs(51, CF_AD_ID, 2330, '["'.CF_AD_ID.'","'.$mdID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功編輯課程商品資料！！', 'upTime'=> $cDateTime);
        } else {
            $ilog = $DB->insSysLogs(51, CF_AD_ID, 2331, '["'.CF_AD_ID.'","'.$mdID.'"]');//寫入操作記錄//
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'編輯課程商品資料失敗！請聯絡相關管理人員！！');
        }
        echo json_encode($rtn);

    break;
    case "func_md_linkUpdate":////////////////////////////////////////// func_md_linkUpdate 編輯視訊鏈結
        if(!empty($arrDatA)){
            $arrDatA = stripslashes($arrDatA);
            $up = $DB->update('fsl_merchandise', array('merch_updated'=>'[EXPR]GETDATE()', 'merch_vdolink'=>$arrDatA), array('merch_id'=>$mdID));
            if($up){
                $ilog = $DB->insSysLogs(51, CF_AD_ID, 2381, '["'.CF_AD_ID.'","'.$mdID.'"]');//寫入操作記錄
                $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功編輯視訊鏈結資料！！', 'upTime'=> $cDateTime);
                echo json_encode($rtn);
                exit();
            }
        }
        $ilog = $DB->insSysLogs(51, CF_AD_ID, 2380, '["'.CF_AD_ID.'","'.$mdID.'"]');//寫入操作記錄
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'編輯視訊鏈結資料失敗！請重新操作或聯絡管理人員！！', 'upTime'=> $cDateTime, 'insID'=> $insI);
        echo json_encode($rtn);
    break;



    
    case "func_md_addnew":////////////////////////////////////////// form_addnew 新增管理者資料
        $insID=1;
        $ilog = $DB->insSysLogs(51, CF_AD_ID, 2321, '["'.CF_AD_ID.'","'.$insID.'"]');//寫入操作記錄//
        $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功新增課程商品資料！！', 'upTime'=> $cDateTime, 'insID'=> $insID);
        echo json_encode($rtn);
        exit();
        $ilog = $DB->insSysLogs(51, CF_AD_ID, 2320, '["'.CF_AD_ID.'","'.$insID.'"]');//寫入操作記錄//
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'新增課程商品資料失敗！請重新操作或聯絡管理人員！！');
        echo json_encode($rtn);
    break;

    case "func_md_delete"://////////// func_md_delete 刪除課程商品資料（使用狀態更新代替刪除，保留原始資料）
        //$del = $DB->query("DELETE FROM fsl_members WHERE mb_id = ?", array($mbID)); 
        $up = $DB->query("UPDATE fsl_merchandise SET merch_status = ? WHERE merch_id = ?", array(8,$mdID));
        if($up){
            /*
            $del = $DB->query("DELETE FROM merch_info WHERE merch_id = ?", array($mdID)); 
            if($del){
                @unlink("../uploadFiles/md/".$subFolder."/mdimg_" . $mdID ."_.jpg");
                @unlink("../uploadFiles/md/".$subFolder."/mdimg_" . $mdID . "_thumb.jpg");
                @unlink("../uploadFiles/md/".$subFolder."/mdimg_" . $mdID . "_tiny.jpg");
            }
            */
            $ilog = $DB->insSysLogs(51, CF_AD_ID, 2351, '["'.CF_AD_ID.'","'.$mdID.'"]');//寫入操作記錄//
            $rtn = array('RTN'=>1); 
        } else {
            $ilog = $DB->insSysLogs(51, CF_AD_ID, 2350, '["'.CF_AD_ID.'","'.$mdID.'"]');//寫入操作記錄//
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'刪除課程商品資料失敗！請聯絡相關管理人員！！'); 
        }
        echo json_encode($rtn);
    break;
    case "func_opp_infoUpdate"://////////////// func_opp_infoUpdate 編輯加購服務項目資料
        $oppID = isset($_POST['oppID']) ? intval($_POST['oppID']) : 0; 
        $upArr = $arrDatA;
        $ins = 0;
        $currID = 0;
        if(!empty($oppID)){//---------------- 更新資料 ------------------//
            $currID = $oppID;
            $upArr['opp_updated'] = '[EXPR]GETDATE()';
            $up = $DB->update('merch_oppurchase', $upArr,array('opp_id'=>$oppID));
        } else {//---------------- 新增資料 ------------------//
        //---------------- 重複輸入檢查 ------------------//
            $dupC = $DB->duplicateCheck('merch_oppurchase',array(
                //'opp_title'=>$upArr['opp_title'],
                //'opp_subscript'=>$upArr['opp_subscript'],
                'opp_keycode'=>$upArr['opp_keycode']
            ));
            if($dupC){
                echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'加購服務項目重複登錄！！'));
                exit();
            }
            //$upArr['opp_published'] = $cDateTime;
            $ins = $DB->insert('merch_oppurchase', $upArr);
            if($ins && $ins > 0){
                $currID = $ins;
            }
        }
        //---------------- /重複輸入檢查 ------------------//
        if($up || ($ins && $ins > 0)){
            $imgUp = !empty($_POST["imgUp"]) ? $_POST["imgUp"] : "";
            $imgUpRtn = array('imgUpRtn'=>true, 'imgUpDes'=>'', 'newUpPhoto'=>'');
            if(!empty($imgUp)) { $imgUpRtn = _fn_upload_avatar($imgUp, $currID, 'md/opp/oppphoto_'); }
            $ilog = $DB->insSysLogs(51, CF_AD_ID, (!empty($oppID)? 1010 : 1110), '["'.CF_AD_ID.'","'.$oppID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>(!empty($imgUp) ? ($imgUpRtn['imgUpRtn']!==false ? 'success' : 'warning') : 'success'), 'msgStr'=>'成功'.(!empty($oppID)?'編輯':'新增').'管理者資料'.(!empty($imgUp) ? ($imgUpRtn['imgUpRtn']!==false ? '，以及代表圖像' : '，但圖像編輯失敗') : '').'！！', 'newUpPhoto'=> $imgUpRtn['newUpPhoto'], 'upTime'=> $cDateTime, 'newID'=>$ins);
        } else {
            $ilog = $DB->insSysLogs(51, CF_AD_ID, 2380, '["'.CF_AD_ID.'","'.$oppID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>(!empty($oppID)?'編輯':'新增').'管理者資料失敗！請聯絡相關管理人員！！');
        }
        echo json_encode($rtn);
    break;
    case "func_opd_infoUpdate":///// func_opd_infoUpdate 編輯加購服務項目資料
        $opdID = isset($_POST['opdID']) ? intval($_POST['opdID']) : 0; 
        $upArr = $arrDatA;
        $upArr['opd_applydate'] = $upArr['opd_applydate']!=='' ? $upArr['opd_applydate'] : null;
        $upArr['opd_startdate'] = $upArr['opd_startdate']!=='' ? $upArr['opd_startdate'] : null;
        $upArr['opd_enddate'] = $upArr['opd_enddate']!=='' ? $upArr['opd_enddate'] : null;
        $ins = 0;
        if(!empty($opdID)){
            $up = $DB->update('merch_oppdata', $upArr,array('opd_id'=>$opdID));
            if($up && $up > 0){
                echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'加購服務項目已'.($upArr['opd_status']==8 ? '刪除' : '更新').'！！'));
                exit();
            }
        } else {
        //---------------- 重複輸入檢查 ------------------//
            $dupSql = "SELECT opd_id FROM merch_oppdata WHERE opd_keycode=".$upArr['opd_keycode'];
            $dupSql .= " AND ((opd_startdate >= ".$upArr['opd_startdate']." AND opd_enddate <= ".$upArr['opd_enddate'].")";
            $dupSql .= " OR (opd_startdate <= ".$upArr['opd_startdate']." AND opd_enddate >= ".$upArr['opd_startdate'].")";
            $dupSql .= " OR (opd_startdate <= ".$upArr['opd_enddate']." AND opd_enddate >= ".$upArr['opd_enddate']."))";
            $dupSql .= " AND opd_status > ? AND opd_status < ?";
            $dupC = $DB->query($dupSql,array(0, 8));
            if($dupC){
                echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'加購服務項目重複登錄！！'));
                exit();
            }
            $ins = $DB->insert('merch_oppdata', $upArr);
            if($ins && $ins > 0){
                echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'已新增一筆加購服務項目！！', 'newID'=>$ins));
                exit();
            }
        }
        echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'加購服務項目操作失敗！請聯絡相關管理人員！！'));
        exit();
    break;
    case "func_scrc_infoUpdate":////////////////////////////////////////// func_scrc_infoUpdate
        $scrcID = isset($_POST['scrcID']) ? intval($_POST['scrcID']) : 0; 
        $scrc_lng = isset($_POST['scrc_lng']) ? $_POST['scrc_lng'] : ''; 
        $scrc_lat = isset($_POST['scrc_lat']) ? $_POST['scrc_lat'] : ''; 
        $upArr = $arrDatA;
        //使用 geography 操作時，不能使用本站的 sql class 
        //scrc_coordinates = geography::STGeomFromText('POINT(121.53243286146436 25.015805668671774)', 4326)
        $ins = 0;
        $up = 0;
        if(!empty($scrcID)){
            $currID = $scrcID;
            $upsql = "UPDATE socialcircle SET scrc_code='".$upArr['scrc_code']."'";
            $upsql .= ", scrc_city='".$upArr['scrc_city']."', scrc_district='".$upArr['scrc_district']."', scrc_public='".$upArr['scrc_public']."', scrc_type='".$upArr['scrc_type']."', scrc_title='".$upArr['scrc_title']."', scrc_circletitle='".$upArr['scrc_circletitle']."'";
            if(!empty($scrc_lng) && !empty($scrc_lat)){//當經緯度座標賦值時，轉換為 geography binary 資料後寫入 geography 型態欄位
                $upsql .= ", scrc_coordinates = geography::STGeomFromText('POINT(".$scrc_lng." ".$scrc_lat.")', 4326)";
            } else {
                $upsql .= ", scrc_coordinates = geography::STGeomFromText(null, 4326)";
            }
            $upsql .= ", scrc_status='".$upArr['scrc_status']."'";
            $upsql .= " WHERE scrc_id=?";
            $up = $DB->query($upsql, array($scrcID));

            //$up = $DB->update('socialcircle', $upArr, array('scrc_id'=>$scrcID));
            /*
            if($up && $up > 0){
                echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'商圈聚落資料已更新！！', 'upTime'=> $cDateTime));
                exit();
            }
            */
        } else {
        //---------------- 重複輸入檢查 ------------------//
            $dupSql = "SELECT scrc_id FROM socialcircle WHERE scrc_city='".$upArr['scrc_city']."' AND scrc_district='".$upArr['scrc_district']."' AND scrc_code='".$upArr['scrc_code']."' AND (scrc_title='".$upArr['scrc_title']."' OR scrc_circletitle='".$upArr['scrc_circletitle']."')";
            $dupC = $DB->query($dupSql,array());
            if($dupC){
                echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'商圈聚落資料重複紀錄！！'));
                exit();
            }
            $upArr['scrc_published'] = $cDateTime;
            $upArr['scrc_updated'] = $cDateTime;
            $insql = "INSERT INTO socialcircle (scrc_published, scrc_updated, scrc_code, scrc_city, scrc_district, scrc_public, scrc_type, scrc_title, scrc_circletitle, scrc_coordinates, scrc_status)";
            $insql .= " VALUES ('".$upArr['scrc_published']."', '".$upArr['scrc_updated']."', '".$upArr['scrc_code']."', '".$upArr['scrc_city']."', '".$upArr['scrc_district']."', ".$upArr['scrc_public'].", ".$upArr['scrc_type'].", '".$upArr['scrc_title']."', '".$upArr['scrc_circletitle']."'";
            if (!empty($scrc_lng) && !empty($scrc_lat)) {
                $insql .= ", geography::STGeomFromText('POINT(".$scrc_lng." ".$scrc_lat.")', 4326)";
            } else {
                $insql .= ", geography::STGeomFromText(null, 4326)";
            }
            $insql .= ", ".$upArr['scrc_status'].")";
            $doins = $DB->query($insql, array());
            $ins = $DB->lastInsertId();
            //echo json_encode(array('RTN'=>$ins));
            //exit();
            if($ins && $ins > 0){
                $currID = $ins;
            }
        }

        if($up || ($ins && $ins > 0)){
            $imgUp = !empty($_POST["imgUp"]) ? $_POST["imgUp"] : "";
            $imgUpRtn = array('imgUpRtn'=>true, 'imgUpDes'=>'', 'newUpPhoto'=>'');
            if(!empty($imgUp)) { $imgUpRtn = _fn_upload_avatar($imgUp, $currID, 'scrc/scrcphoto_'); }
            $ilog = $DB->insSysLogs(51, CF_AD_ID, (!empty($scrcID) ? 1010 : 1110), '["'.CF_AD_ID.'","'.$scrcID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>$imgUpRtn['imgUpRtn']!==false ? 'success' : 'warning', 'msgStr'=>'成功'.(!empty($scrcID)?'編輯':'新增').'商圈聚落資料'.$imgUpRtn['imgUpDes'].'！！', 'newUpPhoto'=> $imgUpRtn['newUpPhoto'], 'upTime'=> $cDateTime, 'newID'=>$ins);
        } else {
            $ilog = $DB->insSysLogs(51, CF_AD_ID, 1010, '["'.CF_AD_ID.'","'.$scrcID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>(!empty($scrcID)?'編輯':'新增').'商圈聚落資料失敗！請聯絡相關管理人員！！');
        }
        echo json_encode($rtn);
        //echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'商圈聚落資料操作失敗！請聯絡相關管理人員！！'));
        exit();
    break;
    case "func_scrc_delete":////////////////////////////////////////// func_scrc_delete
        $scrcID = isset($_POST['scrcID']) ? intval($_POST['scrcID']) : 0; 
        if(empty($scrcID)){
            echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失重要傳遞值，商圈聚落資料刪除失敗！請聯絡相關管理人員！！'));
            exit();
        }
        $del = $DB->query("DELETE FROM socialcircle WHERE scrc_id = ?", array($scrcID));
        if($del){
            echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功刪除商圈聚落資料！！'));
            exit();
        }
        echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'商圈聚落資料刪除失敗！請聯絡相關管理人員！！'));
        exit();
    break;
    case "func_osmway_getosmway":////////////////////////////////////////// func_osmway_getosmway
        $wayCode = $_POST['wayCode'] ?? '';
        $wayVersion = $_POST['wayVersion'] ?? '';
        $output = _fn_OSM_getCoordinates($wayCode, $wayVersion, $mode = "coordinates");
        if(!empty($output['output'])){
            $rtn = array('RTN' => 1, 'msgType' => 'success', 'msgStr' => $output['des'], 'nodePoints' => $output['output']);
        } else {
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>$output['des']);
        }
        echo json_encode($rtn);
        exit();
    break;

}  

?>