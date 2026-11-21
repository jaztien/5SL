<?php
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
$formAcce=$_POST['formAcce'];
switch($formAcce){
    case 'func_bc_xxxxx':
    break;
}
if($datapass == false){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
$arrDatA = isset($_POST['arrDatA']) ? $_POST['arrDatA'] : null;
$arrDatB = isset($_POST['arrDatB']) ? $_POST['arrDatB'] : null;
$cDateTime = date('Y-m-d H:i:s');

switch ($formAcce) {
   case "func_md_imgUpload":////////////////////////////////////////// func_md_imgUpload 上傳課程商品圖檔
      $mdID = isset($_POST['mdID']) ? intval($_POST['mdID']) : 0; 
      $imgUp = isset($_FILES['imgUp']) ? $_FILES['imgUp'] : array();
      $subFolder = ceil(intval($mdID)/100);
      if(empty($mdID)){
          echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失重要傳遞值，上傳檔案失敗，請重新操作或聯絡管理人員！！'));
          exit();
      }
      $totalImg = isset($imgUp['tmp_name']) ? count($imgUp['tmp_name']) : 0; 
      if($totalImg==0 && !isset($_POST['defIdx'])==true){
         echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'操作指令為空，系統無法辨識，請重新操作或聯絡管理人員！！'));
         exit();
      }
      $upDone = 0;
      $cropDone = 0;
      if($totalImg>0){
         foreach( $imgUp['tmp_name'] AS $key => $value){
            if ($value != ''){
               $upPath = "../uploadFiles/md/".$subFolder."/mdimg_".$mdID."_".$key."_";
               if(move_uploaded_file($value, $upPath.".jpg")) {
                  $upDone++;
                  $toPhoto = _fn_resize_crop($upPath.".jpg", $upPath.".jpg", 992, 558);
                  if($toPhoto){
                     $toThumb = _fn_resize_crop($upPath.".jpg", $upPath."thumb.jpg", 480, 270);
                     if($toThumb){
                        $toTiny = _fn_resize_crop($upPath.".jpg", $upPath."tiny.jpg", 160, 90);
                        if($toTiny){ $cropDone++; }
                     }
                  }
               }
            }
         }
      }
      if($upDone == $totalImg){
         $defChange=false;
         $defChangeMess="";
         if(isset($_POST['defIdx'])==true){
            $upArr = array('merch_updated' => '[EXPR]GETDATE()', 'merch_defaultphoto'=>intval($_POST['defIdx']));
            $up = DB->update('fsl_merchandise',$upArr,array("merch_id"=>$mdID));
            if($up){
               $defChange = true;
               $defChangeMess = "，並同時更改預設主圖";
            }
            if($totalImg==0 && $defChange){
               $ilog = DB->insSysLogs(51, CF_AD_ID, 2421, '["'.CF_AD_ID.'","'.$mdID.'"]');//寫入操作記錄//
               echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功更改預設圖檔！！'));
               exit();
            }
         }
         $ilog = DB->insSysLogs(51, CF_AD_ID, 2411, '["'.CF_AD_ID.'","'.$mdID.'","'.$upDone.'"]');//寫入操作記錄//
         echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功上傳 '.$upDone.' 張圖檔，校正裁切 '.$cropDone.' 張圖檔'.$defChangeMess.'！！'));
         exit();
      }
      $ilog = DB->insSysLogs(51, CF_AD_ID, 2410, '["'.CF_AD_ID.'","'.$mdID.'"]');//寫入操作記錄//
      echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'檔案上傳不完全，請重新操作或聯絡管理人員！！'));
      exit();
   break;
   case "func_bc_imgUpload":////////////////////////////////////////// func_bc_imgUpload 上傳建案圖檔
      $bcID = isset($_POST['bcID']) ? intval($_POST['bcID']) : 0; 
      $imgUp = isset($_FILES['imgUp']) ? $_FILES['imgUp'] : array();
      //$defIdx = isset($_POST['defIdx']) ? intval($_POST['defIdx']) : 0;
      $cityCode = isset($_POST['cityCode']) ? intval($_POST['cityCode']) : '';
      $distCode = isset($_POST['distCode']) ? intval($_POST['distCode']) : '';
      if(empty($bcID)||empty($cityCode)||empty($distCode)){
         echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失重要傳遞值，上傳檔案失敗，請重新操作或聯絡管理人員！！'));
         exit();
      }
      $totalImg = isset($imgUp['tmp_name']) ? count($imgUp['tmp_name']) : 0; 
      if($totalImg==0 && !isset($_POST['defIdx'])==true){
         echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'操作指令為空，系統無法辨識，請重新操作或聯絡管理人員！！'));
         exit();
      }

      $upDone = 0;
      $cropDone = 0;
      if($totalImg>0){
         foreach( $imgUp['tmp_name'] AS $key => $value){
            if ($value != ''){
               $upPath = "../uploadFiles/bc/".$cityCode."/".$distCode."/bcimg_".$bcID."_".$key."_";
               if(move_uploaded_file($value, $upPath.".jpg")) {
                  $upDone++;
                  $toPhoto = _fn_resize_crop($upPath.".jpg", $upPath.".jpg", 992, 558);
                  if($toPhoto){
                     $cropDone++;
                  }
               }
            }
         }
      }
      if($upDone == $totalImg){
         if(isset($_POST['defIdx'])==true){
            $upArr = array('bc_updated' => '[EXPR]GETDATE()','bc_defaultphoto'=>intval($_POST['defIdx']));
            $up = DB->update('buildcase',$upArr,array("bc_id"=>$bcID));
            if($totalImg==0){
               $ilog = DB->insSysLogs(51, CF_AD_ID, 2461, '["'.CF_AD_ID.'","'.$bcID.'"]');//寫入操作記錄//
               echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功更改預設圖檔！！'));
               exit();
            }
         }
         $ilog = DB->insSysLogs(51, CF_AD_ID, 2451, '["'.CF_AD_ID.'","'.$bcID.'","'.$upDone.'"]');//寫入操作記錄//
          echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功上傳 '.$upDone.' 張圖檔，校正裁切 '.$cropDone.' 張圖檔！！'));
          exit();
      }
      $ilog = DB->insSysLogs(51, CF_AD_ID, 2450, '["'.CF_AD_ID.'","'.$bcID.'"]');//寫入操作記錄//
      echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'檔案上傳不完全，請重新操作或聯絡管理人員！！'));
      exit();
   break;
   case "func_file_upload":////////////////////////////////////////// func_bc_infoUpdate 更新資料



   break;
   case "func_file_deleteByPath":////////////////////////////////////////// form_delete 刪除管理者資料
      $filePath = $arrDatA;
      $path_parts = pathinfo($filePath.'.jpg');
      if(empty($filePath)){
         echo json_encode(array('RTN'=>0, 'msgType'=>'error'));
         exit();
      }
      if(
         @unlink($filePath.".jpg") && 
         @unlink($filePath."thumb.jpg") && 
         @unlink($filePath."tiny.jpg")
      ){
         $ilog = DB->insSysLogs(51, CF_AD_ID, 2481, '["'.CF_AD_ID.'","'.$path_parts['filename'].'"]');//寫入操作記錄//
         echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功刪除圖像！！'));
         exit();
      }
      $ilog = DB->insSysLogs(51, CF_AD_ID, 2480, '["'.CF_AD_ID.'","'.$path_parts['filename'].'"]');//寫入操作記錄//
      echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'圖像刪除失敗，請重新操作或聯絡管理人員！！'));
      exit();
   break;
   case "func_file_dirdownload":////////////////////////////////////////// func_file_dirdownload 檔案下載

      $downfile = isset($_POST['downfile']) ? trim($_POST['downfile']) : '';  //下載檔案的完整路徑包含檔名(實體路徑)
      if(empty($downfile)){
         echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>$downfile));
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
      exit;
   break;

}
?>