<?php
//error_reporting(0);
//////////////////////////// sv.act.func.php //////////////////////////////////////
//////////////////////////// 開放式服務資料處理功能執行檔案 ////////////////////////////
//////////////////////////// 2024-01-10 By Jaz Tien ///////////////////////////////
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
//-------------------------------------------------------------------------------//
$svID = isset($_POST['svID']) ? intval($_POST['svID']) : 0; 
$arrDatA = isset($_POST['arrDatA']) ? $_POST['arrDatA'] : null;
$cDateTime = date('Y-m-d H:i:s');

$datapass = true;
$formAcce = isset($_POST['formAcce']) ? $_POST['formAcce'] : "";
if( empty($formAcce)){ 
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
if($formAcce=='form_svphotoup') {
    if(!isset($_POST["image"])){ $datapass = false; }
} 
/*
else if($formAcce=='form_infoupdate'||$formAcce=='form_htmlupdate') {
    if(empty(advpID) || empty($arrDatA)){ $datapass = false; }
    else if(empty(advpID) && empty(arrDatB)){ $datapass = false; }
} else if($formAcce=='form_addnew') {
    if(empty($arrDatA)){ $datapass = false; }
} else if($formAcce=='form_authupdate') {
    if(empty(arrDatB)){ $datapass = false; }
} else if($formAcce=='form_delete') {
    if(empty(advpID)){ $datapass = false; }
} else if($formAcce=='form_ruleupdate') {
    if(empty(advrID)||advrID==""){
        $formAcce='form_addrule';
    }
} else if($formAcce=='form_applyupdate') {
    if(empty(adviID)||adviID==""){
        $formAcce='form_addapply';
    }
} 
*/
//define('formAcce',$formAcce);

if($datapass == false){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
//-------------------------------------------------------------------------------//

switch ($formAcce) {
     case "func_sv_infoUpdate":////////////////////////////////////////// func_sv_infoUpdate 更新廣告套裝資料
        $upArr = $arrDatA;
        $upArr['sv_updated'] = '[EXPR]GETDATE()';
        $up = DB->update('sv_service', $upArr, array('sv_id'=>$svID));
        if($up){
            //------------ 代表圖像處理 --------------//
            $imgUp = $_POST["imgUp"];
            $newUpPhoto = '';
            if(!empty($imgUp)){
                $imgUpRtn = false;
                if($imgUp=='@DELETE@'){
                    if(
                        @unlink('../uploadFiles/sv/svphoto_' . $svID . '_.jpg') && 
                        @unlink('../uploadFiles/sv/svphoto_' . $svID . '_thumb.jpg') && 
                        @unlink('../uploadFiles/sv/svphoto_' . $svID . '_tiny.jpg')
                    ){
                        $imgUpRtn = true;
                        $newUpPhoto ='../imageFiles/nopic_400x300.jpg';
                    }
                } else {
                    $image_array_1 = explode(";", $imgUp);
                    $image_array_2 = explode(",", $image_array_1[1]);
                    $imgUp = base64_decode($image_array_2[1]);
                    if(!empty($svID)){
                        $imageName = '../uploadFiles/sv/svphoto_'. $svID .'_.jpg';
                        if (file_put_contents($imageName, $imgUp) !== false) {//---------------- 製作縮圖 ----------------//
                            $thumbName = '../uploadFiles/sv/svphoto_' . $svID . '_thumb.jpg';
                            $toThumb = _fn_resize_image($imageName,$thumbName, 400, 300);
                            if($toThumb){
                                $tinyName = '../uploadFiles/sv/svphoto_' . $svID . '_tiny.jpg';
                                $toTiny = _fn_resize_image($imageName,$tinyName, 120, 90);
                                if($toTiny){
                                    $imgUpRtn = true;
                                    $newUpPhoto = $thumbName;
                                }
                            }                        
                        }
                    }
                }
            }
            //------------ /代表圖像處理 --------------//
            //$ilog = DB->insSysLogs(51, CF_AD_ID, nnnnn,'["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功修改開放式服務資料'.(isset($imgUpRtn) ? ($imgUpRtn ? '，以及代表圖像' : '，但圖像編輯失敗') : '').'！！', 'newUpPhoto'=> $newUpPhoto, 'upTime'=> $cDateTime); 
        } else {
            //$ilog = DB->insSysLogs(51, CF_AD_ID, nnnnn,'["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'error', 'msgStr'=>'修改開放式服務提案失敗！請聯絡相關管理人員！！');
        }
        echo json_encode($rtn);
    break;
    case "form_svadd":////////////////////////////////////////// form_svadd 新增開放式服務提案資料
        //$inArr = array_merge($arrDatA, array('sv_published'=>$cDateTime));
        $inArr = $arrDatA;
        //---------------- 重複輸入檢查 ------------------//
        /*
        $dupC = DB->query("SELECT * FROM advi_pack WHERE advp_keycode=? OR advp_title=?",array($inArr['advp_keycode'],$inArr['advp_title']));
        if($dupC){
            echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'廣告套裝重複建立！！'));
            exit();
        }
        */
       //---------------- /重複輸入檢查 ------------------//
        $ins = DB->insert('sv_service', $inArr);
        if($ins && $ins > 0){
            //------------ 代表圖像處理 --------------//
            $imgUp = $_POST["imgUp"];
            if(!empty($imgUp)){
                $image_array_1 = explode(";", $imgUp);
                $image_array_2 = explode(",", $image_array_1[1]);
                $imgUp = base64_decode($image_array_2[1]);
                if(!empty($ins)){
                    $imageName = '../uploadFiles/sv/svphoto_' . $ins . '_.jpg';
                    file_put_contents($imageName, $imgUp);
                    //---------------- 製作縮圖 ----------------//
                    $src = imagecreatefromjpeg($imageName);
                    $thumb = imagecreatetruecolor(100, 100);
                    imagecopyresized($thumb, $src, 0, 0, 0, 0, 120, 90, 400, 300);
                    $thumbName = '../uploadFiles/sv/svphoto_' . $ins . '_thumb.jpg';
                    imagejpeg($thumb, $thumbName);
                    //---------------- /製作縮圖 ----------------//
                }
            }
            //------------ /代表圖像處理 --------------//
            
            //$ilog = DB->insSysLogs(51, CF_AD_ID, nnnnn,'["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功新增開放式服務資料！！', 'insID'=>$ins); 
        } 
        else { 
            //$ilog = DB->insSysLogs(51, CF_AD_ID, nnnnn,'["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'新增開放式服務資料失敗！請聯絡相關管理人員！！'); 
        } /* 回傳失敗時的資訊 */
        echo json_encode($rtn);
    break;






    case "form_svdelete":////////////////////////////////////////// form_svdelete 刪除開放式服務提案紀錄
        $del = DB->query("DELETE FROM sv_service WHERE sv_id = ?", array($svID)); 
        if($del){
            //$ilog = DB->insSysLogs(51, CF_AD_ID, nnnnn,'["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功刪除開放式服務紀錄！！'); 
        } else {
            //$ilog = DB->insSysLogs(51, CF_AD_ID, nnnnn,'["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'刪除開放式服務紀錄失敗！請聯絡相關管理人員！！'); 
        }
        echo json_encode($rtn);
    break;
    case "form_doreject":////////////////////////////////////////// form_doreject 退回/駁回開放式服務提案
        
        $up = DB->update('sv_service', array(
            'sv_updated'=>'[EXPR]GETDATE()',
            'sv_review'=>$arrDatA['rv_review'],
            'sv_status'=>$arrDatA['rv_status']
            ),array('sv_id'=>$svID));
        if($up){
            $ins = DB->insert('data_reviews', $arrDatA);
            if($ins && $ins > 0){
                //JazNOTE:do notification
                //$ilog = DB->insSysLogs(51, CF_AD_ID, nnnnn,'["vari1","vari2",...]');//寫入操作記錄
                $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功退回/駁回開放式服務提案！！');
                echo json_encode($rtn);
                exit();
            } else {

                
            }
        }
        //$ilog = DB->insSysLogs(51, CF_AD_ID, nnnnn,'["vari1","vari2",...]');//寫入操作記錄
        $rtn = array('RTN'=>1, 'msgType'=>'error', 'msgStr'=>'退回/駁回開放式服務提案失敗！請聯絡相關管理人員！！');
        echo json_encode($rtn);

        break;
    case "form_doreview":////////////////////////////////////////// form_review 
        //$rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>json_encode($arrDatA));
        //echo json_encode($rtn);exit();
        $up = DB->update('sv_service', array(
            'sv_updated'=>'[EXPR]GETDATE()',
            'sv_review'=>$arrDatA['rv_status'],
            'sv_status'=>$arrDatA['rv_status']
            ),array('sv_id'=>$svID));
        if($up){
            $ins = DB->insert('data_reviews', $arrDatA);
            if($ins && $ins > 0){
                //JazNOTE:do notification
                //$ilog = DB->insSysLogs(51, CF_AD_ID, nnnnn,'["vari1","vari2",...]');//寫入操作記錄
                $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功審核通過開放式服務提案！！');
                echo json_encode($rtn);
                exit();
            } else {

                
            }
        } 
        //$ilog = DB->insSysLogs(51, CF_AD_ID, nnnnn,'["vari1","vari2",...]');//寫入操作記錄
        $rtn = array('RTN'=>1, 'msgType'=>'error', 'msgStr'=>'通過開放式服務提案失敗！請聯絡相關管理人員！！');
        echo json_encode($rtn);


        break;
    case "form_imgdelete":////////////////////////////////////////// form_imgdelete 刪除圖像
        if($svID>0){
            @unlink("../uploadFiles/sv/svphoto_". $svID ."_.jpg");
            @unlink("../uploadFiles/sv/svphoto_". $svID ."_thumb.jpg");
            @unlink("../uploadFiles/sv/svphoto_". $svID ."_tiny.jpg");
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功刪除服務提案代表圖！！');
            echo json_encode($rtn);
            exit();
        }
        $rtn = array('RTN'=>1, 'msgType'=>'error', 'msgStr'=>'刪除服務提案代表圖失敗！請聯絡相關管理人員！！');
        echo json_encode($rtn);
        
        break;
    case "form_addapply":////////////////////////////////////////// up_adphoto 更新管理者頭像 form_photoupdate




        break;
    case "form_doapply":////////////////////////////////////////// form_doapply 發佈且生成執行紀錄

        



    break;
    case "form_doreject":////////////////////////////////////////// form_doreject 駁回

        



    break;
    case "form_mbsearch":////////////////////////////////////////// form_mbsearch 




        break;


}  
                        
?>