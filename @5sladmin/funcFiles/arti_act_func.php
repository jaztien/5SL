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
$formAcce = $_POST['formAcce'];
if($formAcce=='form_photoUpdate') {
    if(!isset($_POST["image"])){ $datapass = false; }
} else if($formAcce=='func_arti_infoUpdate') {
    if(empty($_POST['arrDatA'])){ $datapass = false; }
} else if($formAcce=='func_arti_authupdate') {
    if(empty($_POST['arrDatB'])){ $datapass = false; }
} else if($formAcce=='func_arti_delete') {
    if(empty($_POST['artiID'])){ $datapass = false; }
}
if($datapass == false){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
//-------------------------------------------------------------------------------//
$artiID = (isset($_POST['artiID'])) ? intval($_POST['artiID']) : 0; 
$arrDatA = (isset($_POST['arrDatA'])) ? $_POST['arrDatA'] : null;
$arrDatB = (isset($_POST['arrDatB'])) ? $_POST['arrDatB'] : null;
$cDateTime = date('Y-m-d H:i:s');
//-------------------------------------------------------------------------------//
switch ($formAcce) {
    case "func_arti_infoUpdate":////////////////////////////////////////// up_profile 更新管理者資料
        $upArr=$arrDatA;
        $up = false;
        $ins=0;
        $currID = 0;
        //$upArr['arti_published'] = $cDateTime;
        $upArr['arti_dateopen'] = ($arrDatA['arti_dateopen'] === '') ? null : $arrDatA['arti_dateopen'];
        $upArr['arti_dateclose'] = ($arrDatA['arti_dateclose'] === '') ? null : $arrDatA['arti_dateclose'];
        if(!empty($artiID)){
            $upArr['arti_updated'] = '[EXPR]GETDATE()';
            try {
                $currID = $artiID;
                $up = $DB->update('articles', $upArr,array('arti_id'=>$artiID));
            } catch (Exception $e) {
                echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>$e->getMessage()));
                exit();
            }
        } else {
            //---------------- 重複輸入檢查 ------------------//
            $dupC = $DB->duplicateCheck('articles',array(
                'arti_keycode'=>$upArr['arti_keycode']                                                                                          
            ));
            if($dupC){
                echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'文章代碼重複登錄！！'));
                exit();
            }
            //---------------- /重複輸入檢查 ------------------//
            try {
                $ins = $DB->insert('articles', $upArr);
                if($ins && $ins > 0){
                    $currID = $ins;
                }
            } catch (Exception $e) {
                echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>$e->getMessage()));
                exit();
            }
        }

        if($up || ($ins && $ins > 0)){
            $imgUp = !empty($_POST["imgUp"]) ? $_POST["imgUp"] : "";
            $newUpPhoto = '';
            if(!empty($imgUp) && !empty($currID)){
                $imgUpRtn = false;
                if($imgUp=='@DELETE@'){
                    if(
                        @unlink('../uploadFiles/ar/arphoto_' . $currID . '_.jpg') &&
                        @unlink('../uploadFiles/ar/arphoto_' . $currID . '_thumb.jpg')
                    ){
                        $imgUpRtn = true;
                        $newUpPhoto ='../imageFiles/nopic_400x300.jpg';
                    }
                } else {
                    $image_array_1 = explode(";", $imgUp);
                    $image_array_2 = explode(",", $image_array_1[1]);
                    $imgUp = base64_decode($image_array_2[1]);
                    $imageName = '../uploadFiles/ar/arphoto_' . $currID . '_.jpg';
                    if (file_put_contents($imageName, $imgUp)!==false){
                        chmod($imageName, 0755);
                    }
                    //---------------- 製作縮圖 ----------------//
                    $thumbName = '../uploadFiles/ar/arphoto_' . $currID . '_thumb.jpg';
                    $toThumb = _fn_resize_crop($imageName, $thumbName, 400, 300);
                    if($toThumb){
                        chmod($thumbName, 0755);
                        $imgUpRtn = true;
                        $newUpPhoto = $thumbName;
                    }
                }
            }
            $ilog = $DB->insSysLogs(51, CF_AD_ID, (!empty($artiID) ? 1010 : 1110), '["'.CF_AD_ID.'","'.$artiID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>(isset($imgUpRtn) ? ($imgUpRtn ? 'success' : 'warning') : 'success'), 'msgStr'=>'成功'.(!empty($artiID)?'編輯':'新增').'圖文模板資料'.(isset($imgUpRtn) ? ($imgUpRtn ? '，以及代表圖像' : '，但圖像編輯失敗') : '').'！！', 'newUpPhoto'=> $newUpPhoto, 'upTime'=> $cDateTime, 'newID'=>$ins);
        } else {
            $ilog = $DB->insSysLogs(51, CF_AD_ID, (!empty($artiID) ? 1010 : 1110), '["'.CF_AD_ID.'","'.$artiID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>(!empty($artiID)?'編輯':'新增').'圖文模板資料失敗！請聯絡相關管理人員！！');
        }
        echo json_encode($rtn);
    break;
    /*
    case "func_arti_addnew":////////////////////////////////////////// form_addnew 新增文章資料
        $inArr=$arrDatA;
        $inArr['arti_published'] = $cDateTime;
        $inArr['arti_updated'] = $cDateTime;
        $i=0;
        
        //---------------- /重複輸入檢查 ------------------//
        $ins = $DB->insert('articles', $inArr);
        if($ins && $ins > 0){
            $imgUp = $_POST["imgUp"];
            $newUpPhoto = '';
            if(!empty($imgUp)){
                $imgUpRtn = false;
                if($imgUp=='@DELETE@'){
                    if(
                        @unlink('../uploadFiles/ar/arphoto_' . $artiID . '_.jpg') &&
                        @unlink('../uploadFiles/ar/arphoto_' . $artiID . '_thumb.jpg')
                    ){
                        $imgUpRtn = true;
                        $newUpPhoto ='../imageFiles/nopic_400x300.jpg';
                    }
                } else {
                    $image_array_1 = explode(";", $imgUp);
                    $image_array_2 = explode(",", $image_array_1[1]);
                    $imgUp = base64_decode($image_array_2[1]);
                    if(!empty($artiID)){
                        $imageName = '../uploadFiles/ar/arphoto_' . $artiID . '_.jpg';
                        if (file_put_contents($imageName, $imgUp)!==false){
                            chmod($imageName, 0755);
                        }
                        //---------------- 製作縮圖 ----------------//
                        $thumbName = '../uploadFiles/ar/arphoto_' . $artiID . '_thumb.jpg';
                        $toThumb = _fn_resize_crop($imageName, $thumbName, 400, 300);
                        if($toThumb){
                            chmod($thumbName, 0755);
                            $imgUpRtn = true;
                            $newUpPhoto = $thumbName;
                        }
                    }
                }
            }
            $ilog = $DB->insSysLogs(51, CF_AD_ID, 1110, '["'.CF_AD_ID.'","'.$artiID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功新增文章資料'.(isset($imgUpRtn) ? ($imgUpRtn ? '，以及代表圖像' : '，但圖像編輯失敗') : '').'！！', 'newUpPhoto'=> $newUpPhoto, 'newID'=>$ins, 'upTime'=> $cDateTime);
        } 
        else { 
            $ilog = $DB->insSysLogs(51, CF_AD_ID, 1010, '["'.CF_AD_ID.'","'.$artiID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'新增文章資料失敗！請聯絡相關管理人員！！');
        }
        echo json_encode($rtn);

    break;
    */

    case "func_arti_delete":////////////////////////////////////////// form_delete 刪除管理者資料

        //$del = $DB->query("DELETE FROM articles WHERE ad_id = ?", array($artiID)); 
        $up = $DB->update('articles', array(
            'arti_updated'=> '[EXPR]GETDATE()',
            'arti_status'=>8
        ), array('arti_id'=>$artiID));
        if($up){
            //----- and more......
            //@unlink("../uploadFiles/ar/arphoto_" . $artiID . ".jpg");
            //@unlink("../uploadFiles/ar/arphoto_" . $artiID . "_thumb.jpg");
            //----- and more......
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功刪除文章！', 'upTime'=> time()); 
        } else {
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'刪除文章失敗！請聯絡相關管理人員！！'); 
        }
        echo json_encode($rtn);
    break;
    case "func_arti_photoUpdate":////////////////////////////////////////// func_arti_photoUpdate 更新頭像
            $data = $_POST["image"];
			$image_array_1 = explode(";", $data);
			$image_array_2 = explode(",", $image_array_1[1]);
			$data = base64_decode($image_array_2[1]);
            if(!empty($artiID)){
                $imageName = '../uploadFiles/ar/arphoto_' . $artiID . '.jpg';
                //$rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'XX成功上傳圖像！！', 'upTime'=> time(), 'imgPATH'=> '$imageName'.'?'.time());
                //echo json_encode($rtn); exit();
                file_put_contents($imageName, $data);
                //---------------- 製作縮圖 ----------------//
                $src = imagecreatefromjpeg($imageName);
                $thumb = imagecreatetruecolor(400, 300);
                imagecopyresized($thumb, $src, 0, 0, 0, 0, 400, 300, 800, 600);
                $thumbName = '../uploadFiles/ar/arphoto_' . $artiID . '_thumb.jpg';
                imagejpeg($thumb, $thumbName);
                //---------------- /製作縮圖 ----------------//
            } else {
                $imageName = "../uploadFiles/ar/arphoto_".$_SESSION['SESS_AD_ID']."_temp.jpg";
                file_put_contents($imageName, $data);
                $_SESSION['SESS_AR_TEMPPHOTO'] = $imageName;
            }
			$rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功上傳圖像！！', 'upTime'=> time(), 'imgPATH'=> $imageName.'?'.time());
			echo json_encode($rtn);
    break;
    
    case "func_email_preview":////////////////////////////////////////// func_arti_testemail


        


    
    break;
    
}  
?>