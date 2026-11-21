<?php
//error_reporting(0);
//////////////////////////// ad.act.func.php //////////////////////////////////////
//////////////////////////// 管理者資料處理功能執行檔案 ///////////////////////////////
//////////////////////////// 2023-01-30 By Jaz Tien ///////////////////////////////
//////////////////////////// jaztien@gmail.com ////////////////////////////////////
//-------------------------------------------------------------------------------//
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
//-------------------------------------------------------------------------------//

//若有 TOKEN 驗證要求時，進行雙重認證。
if(!isset($_POST['token']) || $_POST['token']=='' || empty($_SESSION['token'])){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失TOKEN驗證值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
} else {
    if($_POST['token'] != $_SESSION['token']){
        if(PAGEURL_BACK != PAGEURL_NOW){//避免前後頁都是同一頁時，造成的無限迴圈
          echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'TOKEN驗證值比對不符，表單傳送失敗！請聯絡相關管理人員！！'));
          exit();
        }
    }
}

$datapass = true;
if( empty($_POST['formAcce'])){ 
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
$formAcce=$_POST['formAcce'];
switch($formAcce){
    case 'func_bc_xxxxx':
    break;
}
if($datapass == false){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}

//-------------------------------------------------------------------------------//
$bcID = isset($_POST['bcID']) ? intval($_POST['bcID']) : 0; 
$arrDatA = isset($_POST['arrDatA']) ? $_POST['arrDatA'] : null;
$arrDatB = isset($_POST['arrDatB']) ? $_POST['arrDatB'] : null;
$cDateTime = date('Y-m-d H:i:s');
//-------------------------------------------------------------------------------//
switch ($formAcce) {
     case "func_bc_infoUpdate":////////////////////////////////////////// func_bc_infoUpdate 更新資料
        $arrDatA['bc_updated'] = '[EXPR]GETDATE()';
        $arrDatA['bc_completion'] = $arrDatA['bc_completion'] !== '' ? $arrDatA['bc_completion'] : null;
        $setArr=$arrDatA;
        $getCoord = @$setArr['bc_coordinates'];
        $setCoordinates = _fn_stringto_geography($getCoord);

        if(isset($setArr['bc_coordinates'])){
            unset($setArr['bc_coordinates']);
        }
        $queryString = _fn_getQueryString($setArr);
        $upsql = "UPDATE buildcase SET ".$queryString;
        //更新地理座標欄位，請注意，一般情形下，課程商品編輯時不使用 GoogleMap API 更新座標。錯過新增時的自動偵測座標操作後，之後只能手動更新座標。
        if(!empty($setCoordinates)){
            $upsql .= ", bc_coordinates = geography::STGeomFromText('".$setCoordinates."', 4326)";
        } else {
            $upsql .= ", bc_coordinates = geography::STGeomFromText(null, 4326)";
        }
        $upsql .= " WHERE bc_id=?";
        $up = DB->query($upsql, array($bcID));
        //$up = DB->update('buildcase', $setArr,array('bc_id'=>$bcID));
        if($up){
            //$ilog = DB->insSysLogs(CF_AD_ID,1,'2121',CF_AD_ID.','.$bcID);//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功編輯社區案件資料！！', 'upTime'=> $cDateTime);
            echo json_encode($rtn);
            exit();
        } 
        //$ilog = DB->insSysLogs(51, CF_AD_ID, 2120, '["'.CF_AD_ID.'","'.$bcID.'"]');//寫入操作記錄
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'編輯社區案件資料失敗！請聯絡相關管理人員！！');
        echo json_encode($rtn);
    break;
    case "func_bc_delete":////////////////////////////////////////// form_delete 刪除管理者資料
        /*
        $del = DB->query("DELETE FROM xxxxxxxx WHERE xxxx_id = ?", array(xxxxID)); 
        if($del){
            $delauth = DB->query("DELETE FROM xxxxxxxx WHERE xxxx_id = ?", array(xxxxID)); 
            //----- and more......
            @unlink("../uploadFiles/xxxx/xxphoto_" . xxxxID . "_.jpg");
            @unlink("../uploadFiles/xxxx/xxphoto_" . xxxxID . "_thumb.jpg");
            //----- and more......
            $rtn = array('RTN'=>1); 
        } else {
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'刪除xxxx失敗！請聯絡相關管理人員！！'); 
        }
        */
        
        $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'！！'); 
        echo json_encode($rtn);
    break;
    case "func_bc_addnew":////////////////////////////////////////// func_bc_addnew 新增紀錄
        $inArr = $arrDatA;
        //$inArr['bc_id'] = null;
        $ins = DB->insert('buildcase', $inArr);
        if($ins && $ins > 0){
            $rtn = array('RTN'=>2, 'msgType'=>'success', 'msgStr'=>'！！', 'newID'=>$ins); 
            echo json_encode($rtn);
            exit();
        }
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'新增社區建案失敗！請重新操作或聯絡相關管理人員！！'); 
        echo json_encode($rtn);
        exit();

    break;
    case "func_bc_imgDelete":////////////////////////////////////////// func_bc_imgDelete 刪除圖像





    break;

}  
?>
