<?php
//error_reporting(0);
//////////////////////////// ad.act.func.php //////////////////////////////////////
//////////////////////////// 管理者資料處理功能執行檔案 ///////////////////////////////
//////////////////////////// 2024-08-20 By Jaz Tien ///////////////////////////////
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

if($formAcce=='form_photoupdate') {
    if(!isset($_POST["imgUp"])){ $datapass = false; }
} else if($formAcce=='func_ad_addnew') {
    if(empty($_POST['arrDatA'])){ $datapass = false; }
} else if($formAcce=='func_ad_delete') {
    if(empty($_POST['adID'])){ $datapass = false; }
} else if($formAcce=='func_ad_imgupdate') {
    if(empty($_POST['tag'])){ $datapass = false; }
} else if($formAcce=='func_ad_imgdelete') {
    if(empty($_POST['arrDatA'])){ $datapass = false; }
} else if($formAcce=='func_ad_getcsvinfo' || $formAcce=='func_ad_csvimport' || $formAcce=='func_ad_csvedit' || $formAcce=='func_ad_csvdelete') {
    if(empty($_POST['csvID'])){ $datapass = false; }
}
/*
else {
    if(empty($_POST['adID']) || empty($_POST['arrDatA'])){ $datapass = false; }
}
*/
if($datapass == false){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}

//-------------------------------------------------------------------------------//
$adID = $_POST['adID'] ?? 0;
$arrDatA = $_POST['arrDatA'] ?? null;
$arrDatB = $_POST['arrDatB'] ?? null;
$cDateTime = date('Y-m-d H:i:s');
//-------------------------------------------------------------------------------//
switch ($formAcce) {
    case "func_ad_infoUpdate":////////////////////////////////////////// func_ad_infoUpdate 更新管理者資料
        $ar=$arrDatA;
        $setArr=array();
        $i=0;
        while($i<count($ar)-1){
            $name=$ar[$i]['name'];
            $val=$ar[$i]['value'];
            $setArr = array_merge($setArr, array($name => $val));
            $i++;
        }
        $setArr['ad_updated'] = '[EXPR]NOW()';
        $up = DB->update('fsl_administrator', $setArr,array('ad_id'=>$adID));
        if($up){
            $imgUp = $_POST["imgUp"];
            $newUpPhoto = '';
            if(!empty($imgUp)){
                $imgUpRtn = false;
                if($imgUp=='@DELETE@'){
                    if(
                        @unlink('../uploadFiles/ad/adphoto_' . $adID . '_.jpg') &&
                        @unlink('../uploadFiles/ad/adphoto_' . $adID . '_thumb.jpg')
                    ){
                        $imgUpRtn = true;
                        if($adID == CF_AD_ID){
                            $_SESSION['SESS_AD_THUMB'] = 'imageFiles/nopic_user_128x128.jpg';
                            $newUpPhoto ='../imageFiles/nopic_user_200x200.jpg';
                        }
                    }
                } else {
                    $image_array_1 = explode(";", $imgUp);
                    $image_array_2 = explode(",", $image_array_1[1]);
                    $imgUp = base64_decode($image_array_2[1]);
                    if(!empty($adID)){
                        $imageName = '../uploadFiles/ad/adphoto_' . $adID . '_.jpg';
                        if (file_put_contents($imageName, $imgUp)!==false){
                            chmod($imageName, 0755);
                        }
                        //---------------- 製作縮圖 ----------------//
                        $thumbName = '../uploadFiles/ad/adphoto_' . $adID . '_thumb.jpg';
                        $toThumb = _fn_resize_crop($imageName, $thumbName, 200, 200);
                        if($toThumb){
                            chmod($thumbName, 0755);
                            $imgUpRtn = true;
                            if($adID == CF_AD_ID){
                                $_SESSION['SESS_AD_THUMB'] = 'uploadFiles/ad/adphoto_' . $adID . '_thumb.jpg';
                                $newUpPhoto = '../'.$thumbName;
                            }
                        }
                    }
                }
            }
            $ilog = DB->insSysLogs(51, CF_AD_ID, 2121, '["'.CF_AD_ID.'","'.$adID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>(isset($imgUpRtn) ? ($imgUpRtn ? 'success' : 'warning') : 'success'), 'msgStr'=>'成功編輯管理者資料'.(isset($imgUpRtn) ? ($imgUpRtn ? '，以及代表圖像' : '，但圖像編輯失敗') : '').'！！', 'newUpPhoto'=> $newUpPhoto, 'upTime'=> $cDateTime);
        } else {
            $ilog = DB->insSysLogs(51, CF_AD_ID, 2120, '["'.CF_AD_ID.'","'.$adID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'編輯管理者資料失敗！請聯絡相關管理人員！！');
        }
        echo json_encode($rtn);
    break;

    case "func_ad_passUpdate":////////////////////////////////////////// func_ad_passUpdate 更新密碼
        //當密碼變更時會傳入兩組欄位名稱以及密碼，第一組為新密碼，第二組為密碼再確認
        //所以當$arrDatA[0]['value']等於$arrDatA[1]['value']時代表確認密碼正確
        if(trim($arrDatA[0]['value'])== trim($arrDatA[1]['value'] )){
            $ar=$arrDatA;
            $setArr=array('ad_updated'=> '[EXPR]NOW()', trim($arrDatA[0]['name']) => password_hash(trim($arrDatA[0]['value']), PASSWORD_DEFAULT));
            $up = DB->update('fsl_administrator', $setArr,array('ad_id'=>$adID));
            if($up){
                $ilog = DB->insSysLogs(51, CF_AD_ID, 2141, '["'.CF_AD_ID.'","'.$adID.'"]');//寫入操作記錄
                $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'更新登入密碼成功！！', 'upTime'=> $cDateTime);
            } else {
                $ilog = DB->insSysLogs(51, CF_AD_ID, 2140, '["'.CF_AD_ID.'","'.$adID.'"]');//寫入操作記錄
                $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'更新登入密碼失敗！請聯絡相關管理人員！！');
            }
        } else {
            $rtn = array('RTN'=>2, 'msgType'=>'warning', 'msgStr'=>'密碼比對不同！請重新輸入密碼！！');
        }
        echo json_encode($rtn);
    break;

    case "func_ad_authUpdate":///////////////////////// func_ad_authUpdate 更新管理權限 //【admin_page】 建立聯合唯一約束（聯合唯一索引）
        /* 先將 Ajax 傳遞來的陣列建立一個不含索引單純可以以 key 值直取 Value 的陣列 */
        $apt=$arrDatA;
        $rtnSta=false;
        $colorCHG=[];
        $i=0;
        $ar = array(); 
        while($i<count($apt)){
            $ar = array_merge($ar, array($apt[$i]['name']=>1));//Checkbox沒有勾選時是不會傳送任何資料，所以只要有key值傳出，則其Value在此必為“1”。
            $i++;
        }
        //echo json_encode(array('RTN'=>1, 'msgType'=>'error', 'msgStr'=>'HERE！！'));
        //exit();

        $admJS=_fn_getCachedJsonData("../jsonFiles/adminpage.json");
        $authpage = $admJS['sidebar'];
        $optARR = array();
        $i=0;
        while($i < count($authpage)){
            $authpage2 = isset($authpage[$i]['subgroup']) ? $authpage[$i]['subgroup'] : array();
            $si=0;
            while($si < count($authpage2)){
                $authpage3 = isset($authpage2[$si]['subgroup']) ? $authpage2[$si]['subgroup'] : array();
                if(count($authpage3) <= 0) { array_push($optARR, $authpage2[$si]); } 
                $ti=0;
                while($ti < count($authpage3)){
                    array_push($optARR, $authpage3[$ti]);
                    $ti++;
                }
                $si++;
            }
            $i++;
        }
        $chkCount=0;
        $succeCount=0;
        $errorCount=0;
        $i=0;
        while($i < count($optARR)){
            $idX=$optARR[$i]['pagecode'];
            $viewCHK=isset($ar['view_'.$idX]) ? '1' : '0';
            $addCHK=isset($ar['add_'.$idX]) ? '1' : '0';
            $editCHK=isset($ar['edit_'.$idX]) ? '1' : '0';
            $delCHK=isset($ar['del_'.$idX]) ? '1' : '0';
            $newCHK = strval($viewCHK.$addCHK.$editCHK.$delCHK);
            $comF=strval($optARR[$i]['default']);
            // 找出跟頁面與管理者相關聯的一筆權限紀錄 
            $au = DB->row("SELECT * FROM fsl_admin_auth WHERE ad_id = ? AND ap_pagecode = ?", array($adID,$idX));
            // 取得有紀錄的權限紀錄 ID，沒有的話賦值 0 
            $auID = ($au) ? intval($au['auth_id']) : 0;

        
            if($newCHK==$comF){ // 若是頁面預設權限與 ajax 傳遞來的權限資料相同時處理 
                if($auID>0){ // 若是有相關權限紀錄，刪除這個紀錄，系統將會自動讀取預設權限（與預設權限相同時沒有必要生成權限紀錄） 
                    $chkCount++;
                    $del=DB->query("DELETE FROM fsl_admin_auth WHERE auth_id=?", array($auID));
                    if($del){ 
                        $colorCHG['adminPG_'.$idX ]='icheck-warning'; // 刪除成功後寫入更新 Class 狀態陣列（即時更改Checkbox顏色）
                        $succeCount++;
                    } else {// 有權限紀錄確刪除失敗時，認定為此列紀錄處理失敗 
                        $errorCount++;
                    }
                }
            } else { // 若是頁面預設權限與 ajax 傳遞來的權限資料「不」相同時處理（與預設權限不同時，代表需要建立一個權限紀錄，而以此紀錄為主要權限佈局） 
                $goARR = array(// 建立 SQL 更新資料陣列 
                    "auth_browse"=>$viewCHK,
                    "auth_addnew"=>$addCHK,
                    "auth_edit"=>$editCHK,
                    "auth_delete"=>$delCHK,
                    "ap_pagecode"=>$idX
                );
                if($auID>0){ // 若是已經有了權限紀錄時，更新這個權限紀錄 
                    $chkCount++;
                    $up = DB->query("UPDATE fsl_admin_auth SET auth_browse = :auth_browse, auth_addnew = :auth_addnew, auth_edit = :auth_edit, auth_delete = :auth_delete, ap_pagecode = :ap_pagecode WHERE auth_id = '".$auID."'", $goARR);
                    if($up){ $succeCount++; }// 順利更新紀錄時，認定為此列紀錄處理成功 
                    else { // 若是更新失敗時處理 
                        if(intval($au['auth_browse'])==$viewCHK && intval($au['auth_addnew'])==$addCHK && intval($au['auth_edit'])==$editCHK && intval($au['auth_delete'])==$delCHK) { $succeCount++; }// 當更改資料並無異動時，即使更新失敗仍然認定為成功
                        else { $errorCount++; } // 若是更新資料有異動時，代表更新必須成功，所以認定為此列處理失敗 
                    }
                } else { // 若是並無權限紀錄時，便新建立一個權限紀錄 
                    $chkCount++;
                    $ins = DB->query("INSERT INTO fsl_admin_auth(ad_id,auth_browse,auth_addnew,auth_edit,auth_delete,ap_pagecode) VALUES('".$adID."',:auth_browse,:auth_addnew,:auth_edit,:auth_delete,:ap_pagecode)",$goARR);
                    if($ins){ // 建立紀錄成功時
                        $colorCHG['adminPG_'.$idX ]='icheck-success'; // 寫入更新 Class 狀態陣列（即時更改Checkbox顏色） 
                        $succeCount++;// 當然認定為處理成功 
                    }
                    else { $errorCount++; }// 新增紀錄失敗時，當然認定為處理失敗 
                } 
            }
            $i++;
        }

        if($errorCount==0 && $succeCount==$chkCount){ $rtnSta=true; } // 成功筆數與總處理筆數相同時，則為此次更新處理完全成功


        if($rtnSta==true){ 
            $ilog = DB->insSysLogs(51, CF_AD_ID, 2131, '["'.CF_AD_ID.'","'.$adID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'更新操作權限成功！！', 'upTime' => $cDateTime, 'colorCHG'=>$colorCHG); 
            echo json_encode($rtn);
            exit();
        } /* 回傳成功時的資訊 */
        else {
            $ilog = DB->insSysLogs(51, CF_AD_ID, 2130, '["'.CF_AD_ID.'","'.$adID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'更新操作權限失敗！請聯絡相關管理人員！！', 'upTime' => $cDateTime, 'colorCHG'=>array()); 
            echo json_encode($rtn);
            exit();
        } /* 回傳失敗時的資訊 */
        $rtn = array('RTN'=>1, 'msgType'=>'error', 'msgStr'=>'系統錯誤！請聯絡相關管理人員！！', 'upTime' => $cDateTime, 'colorCHG'=>$colorCHG); 
        echo json_encode($rtn);
        exit();
    break;
    case "func_ad_addnew":////////////////////////////////////////// func_ad_addnew 新增管理者資料
        $ar=$arrDatA;
        $i=0;
        while($i<count($ar)){
            //if($ar[$i]['name']=='comfPass') continue;
            $name=$ar[$i]['name'];
            $val=$ar[$i]['value'];
            if($name == 'ad_password'){
                //$val=_fn_FHMD5_encrypt($val);
                $val = password_hash($val, PASSWORD_DEFAULT);;
            }
            $inArr = array_merge($inArr, array($name => $val));
            $i++;
        }
        //---------------- 重複輸入檢查 ------------------//
        $dupC = DB->duplicateCheck('fsl_administrator',array(
            'ad_phone'=>$inArr['ad_phone'],
            'ad_email'=>$inArr['ad_email'],
            'ad_line'=>$inArr['ad_line'],
            'ad_account'=>$inArr['ad_account']
        ));
        if($dupC){
            echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'管理者重複登錄！！'));
            exit();
        }
        //---------------- /重複輸入檢查 ------------------//

        $ins = DB->insert('fsl_administrator', $inArr);
        if($ins && $ins > 0){
            //------------ 頭像更新檔名處理 --------------//
            $imgUp = $_POST["imgUp"];
            if(!empty($imgUp)&&$imgUp!='@DELETE@'){
                $image_array_1 = explode(";", $imgUp);
                $image_array_2 = explode(",", $image_array_1[1]);
                $imgUp = base64_decode($image_array_2[1]);
                $imageName = '../uploadFiles/ad/adphoto_' . $ins . '_.jpg';
                file_put_contents($imageName, $imgUp);
                if (file_put_contents($imageName, $imgUp) !== false) {
                    _fn_resize_image($imageName,'../uploadFiles/ad/adphoto_' . $ins . '_thumb.jpg', 100, 100);//製作縮圖
                }
            }
            $ilog = DB->insSysLogs(CF_AD_ID,1,2111,'["'.CF_AD_ID.'","'.$ins.'"]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'newID'=>$ins); 
            echo json_encode($rtn);
            exit();
        } 
        $ilog = DB->insSysLogs(51, CF_AD_ID, 2110, '["'.CF_AD_ID.'"]');//寫入操作記錄
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'新增管理者失敗！請聯絡相關管理人員！！'); 
        echo json_encode($rtn);
    break;
    case "func_ad_delete":////////////////////////////////////////// func_ad_delete 刪除管理者資料
        //$del = DB->query("DELETE FROM fsl_administrator WHERE ad_id = ?", array($adID)); 
        $up = DB->update('fsl_administrator', array('ad_updated'=> '[EXPR]NOW()','ad_status'=>8), array('ad_id'=>$adID));
        if($up){
            $ilog = DB->insSysLogs(51, CF_AD_ID, 2151, '["'.CF_AD_ID.'","'.$adID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>1); 
        } else {
            $ilog = DB->insSysLogs(51, CF_AD_ID, 2150, '["'.CF_AD_ID.'","'.$adID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'刪除管理者失敗！請聯絡相關管理人員！！'); 
        }
        echo json_encode($rtn);
    break;
/*
    case "form_photoupdate":////////////////////////////////////////// up_adphoto 更新管理者頭像 form_photoupdate
            $data = $_POST["image"];
			$image_array_1 = explode(";", $data);
			$image_array_2 = explode(",", $image_array_1[1]);
			$data = base64_decode($image_array_2[1]);
            if(!empty($adID)){
                $imageName = '../uploadFiles/ad/adphoto_' . $adID . '_.jpg';
                //$rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'XX成功上傳圖像！！', 'upTime'=> time(), 'imgPATH'=> '$imageName'.'?'.time());
                //echo json_encode($rtn); exit();
                file_put_contents($imageName, $data);
                //---------------- 製作縮圖 ----------------//
                $src = imagecreatefromjpeg($imageName);
                $thumb = imagecreatetruecolor(100, 100);
                imagecopyresized($thumb, $src, 0, 0, 0, 0, 100, 100, 400, 400);
                $thumbName = '../uploadFiles/ad/adphoto_' . $adID . '_thumb.jpg';
                imagejpeg($thumb, $thumbName);
                //---------------- /製作縮圖 ----------------//
            } else {
                $imageName = "../uploadFiles/ad/adphoto_".$_SESSION['SESS_AD_ID']."_temp.jpg";
                file_put_contents($imageName, $data);
                $_SESSION['SESS_AD_TEMPPHOTO'] = $imageName;
            }
			$rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功上傳圖像！！', 'upTime'=> time(), 'imgPATH'=> $imageName.'?'.time());
			echo json_encode($rtn);
    break;
*/
    case "func_ad_imgdelete":////////////////////////////////////////// imgdelete 刪除圖像
        $inID = implode(",", $arrDatA);
        $delNum = count($arrDatA);
        $del = DB->query("DELETE FROM data_vdoimg WHERE vi_id IN (".$inID.")");
        if($del){ 
            foreach( $arrDatB as $value){
                @unlink($value);
            }
            $ilog = DB->insSysLogs(51, CF_AD_ID, 2521, '["'.CF_AD_ID.'","'.$delNum.'"]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=> '成功刪除圖像紀錄', 'keyDel'=> '');
        }//順利更新紀錄時，認定為此列紀錄處理成功
        else { //若是更新失敗時處理
            $ilog = DB->insSysLogs(51, CF_AD_ID, 2520, '["'.CF_AD_ID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '刪除圖像紀錄失敗', 'upTime'=> time());
        }
        echo json_encode($rtn);
    break;
    case "func_ad_imgupdate":////////////////////////////////////////// func_ad_imgupdate 編輯圖像
        $viID = intval($_POST['viID']);
        $srC = $_POST['srC'] ?? '';
        $fname = $_POST['fname'] ?? '';
        $name = $_POST['name'] ?? '';
        $alt = $_POST['alt'] ?? '';
        $tag = $_POST['tag'] ?? '';
        $newCate = $_POST['newCate'] ?? '';
        $image = $_FILES['img_upload'] ?? null;
        $up = false;
        $ins = false;
        $file_count = !empty($image) ? sizeof($image["name"]) : 0;
        if($file_count>0){//當有至少一個檔案執行上傳時
            for ($i=0; $i<$file_count; $i++) {
                $img_name = $image["name"][$i];
                //-----------------------------------------
                $img_path = $image["tmp_name"][$i];
                $img_nFname = pathinfo($img_path, PATHINFO_FILENAME);
                $img_type = pathinfo($img_name, PATHINFO_EXTENSION);
                
                $img_imgData = file_get_contents($img_path);
                $img_Encoded = base64_encode($img_imgData);
                $img_Base64 = 'data:image/' . $img_type . ';base64,' . $img_Encoded;
                //-----------------------------------------
                if(!empty($viID)){//當這個請求有ID值後送時，表示為圖像更新，此時圖像路徑使用原路徑不變，除了保持原路徑外，還可同時覆蓋舊圖像
                    $fileName = $fname;
                } else {//當這個請求沒有ID值後送時，相當於新增圖像或影片，此時建立新的路徑
                    $timeExt = round(microtime(true) * 1000) - time()*(1000-$i); 
                    $file_name = "sysimg_".date('Ymdhis').$timeExt;
                    //$fileName = "../uploadFiles/sysimg/".$file_name.".".$img_type;
                    $fileName = "sysimg/".$file_name.".".$img_type;
                }
                if(is_uploaded_file($img_path)) {//當成功完成檔案上傳時，這個請求可能是新增檔案或是更新檔案
                    $decodedImage = base64_decode($img_Encoded);
                    $putReturn = file_put_contents("../uploadFiles/".$fileName, $decodedImage);
                    if($putReturn !== false){//當成功完成檔案上傳，且生成檔案於指定資料夾中時
                        if(!empty($viID)){//當這個請求有ID值後送時，更新這個紀錄的資料
                            $up = DB->update('data_vdoimg', array('vi_updated'=>'[EXPR]NOW()', 'vi_name'=>$name, 'vi_alt'=>$alt, 'vi_src'=>$fileName, 'vi_tag'=>$tag), array('vi_id'=>$viID));
                        } else {//當這個請求沒有ID值後送時，新增一筆紀錄資料
                            $ins = DB->insert('data_vdoimg', array('vi_name'=>$name, 'vi_alt'=>$alt, 'vi_src'=>$fileName, 'vi_tag'=>$tag));
                        }
                    } 
                }
            }
        } else {//當沒有檔案執行上傳時，這個請求只能是更新這個紀錄的資料
            $up = DB->update('data_vdoimg', array('vi_updated'=>'[EXPR]NOW()', 'vi_name'=>$name, 'vi_alt'=>$alt, 'vi_tag'=>$tag), array('vi_id'=>$viID));
        }

        if($up){
            $ilog = DB->insSysLogs(51, CF_AD_ID, 2511, '["'.CF_AD_ID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=> '成功編輯圖像紀錄', 'newCate'=> $newCate);
        }/* 順利更新紀錄時，認定為此列紀錄處理成功 */
        else if($ins){ 
            //$ilog = DB->insSysLogs(51, CF_AD_ID, '2511', CF_AD_ID);//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=> '成功新增圖像紀錄', 'newCate'=> $newCate, 'newIdx'=> $file_count);
        }/* 順利更新紀錄時，認定為此列紀錄處理成功 */
        else { /* 若是更新失敗時處理 */
            $ilog = DB->insSysLogs(51, CF_AD_ID, 2510, '["'.CF_AD_ID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '編輯圖像紀錄失敗', 'upTime'=> time());
        }
        echo json_encode($rtn);
    break;
    case "func_ad_csvupload":////////////////////////////////////////// func_ad_csvupload 匯入檔案預先上傳
        $uFile = isset($_FILES['csvfile']) ? $_FILES['csvfile'] : NULL;
        
        $file_table = isset($_POST['file_table']) ? $_POST['file_table'] : '';
        $file_create = isset($_POST['file_create']) ? $_POST['file_create'] : date('Y-m-d- H:i:s');
        $file_title = isset($_POST['file_title']) ? $_POST['file_title'] : '';
        $file_ext = isset($_POST['file_ext']) ? $_POST['file_ext'] : '';
        $file_part = isset($_POST['file_part']) ? $_POST['file_part'] : 1;
        $file_rows = isset($_POST['file_rows']) ? $_POST['file_rows'] : 0;
        $file_size = isset($_POST['file_size']) ? $_POST['file_size'] : 0;
        $file_remark = isset($_POST['file_remark']) ? $_POST['file_remark'] : '';

        //check batch
        $file_batch = 1;
        $getBatchNum = DB->single("SELECT csvio_batch FROM sys_csvio WHERE csvio_filetitle='".$file_title."' AND csvio_part='".$file_part."' ORDER BY csvio_batch DESC", array(0));
        if(!empty($getBatchNum)) {
            $file_batch = $getBatchNum + 1;
        }
        //Get destination table's last insert ID
        $getLastID = DB->single("SELECT IDENT_CURRENT($file_table)", array(0));
        $getLastID = !empty($getLastID) ? $getLastID : 0;
        if($uFile) {
            $tmpFilePath = $uFile['tmp_name'];
            chmod($tmpFilePath, 0700);
            if ($tmpFilePath != ''){
                $timeExt = round(microtime(true) * 1000) - time()*1000; 
                $file_name = "import_".date('Ymdhis').$timeExt.".".$file_ext;
                $newFilePath = "../dataFiles/csvio/".$file_name;
                if(move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $inArr = array(
                        'csvio_mode' => 1,
                        'csvio_table' => $file_table,
                        'csvio_filename' => $file_name,
                        'csvio_filetitle' => $file_title,
                        'csvio_filetype' => $file_ext,
                        'csvio_part' => $file_part,
                        'csvio_batch' => $file_batch,
                        'csvio_size' => $file_size,
                        'csvio_rows' => $file_rows,
                        'csvio_remark' => $file_remark,
                        'csvio_status' => 1
                    );
                    $ins = DB->insert('sys_csvio', $inArr);
                    if($ins){
                        $inArr['csvio_id'] = $ins;
                        $inArr['getLastID'] = $getLastID;
                        $ilog = DB->insSysLogs(51, CF_AD_ID, 2601, '["'.CF_AD_ID.'","'.$file_name.'"]');//寫入操作記錄
                        $rtn = array('RTN'=>1, 'msgType'=>'success', 'fInfo'=> $inArr, 'upTime'=> time());
                        echo json_encode($rtn);
                        exit();
                    }
                }      
            }    
        }
        $ilog = DB->insSysLogs(51, CF_AD_ID, 2600, '["'.CF_AD_ID.'","'.$file_name.'"]');//寫入操作記錄
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'uFile'=> $uFile['tmp_name'], 'upTime'=> time());
        echo json_encode($rtn);
    break;
    case "func_ad_getcsvinfo":////////////////////////////////////////// func_ad_getcsvinfo 取得匯入檔案資料
        $csvID = isset($_POST['csvID']) ? $_POST['csvID'] : 0;
        if(!empty($csvID)){
            $cio = DB->row("SELECT * FROM sys_csvio WHERE csvio_id=?",array($csvID));
            if(!empty($cio['csvio_table'])){
                $getLastID = DB->single("SELECT IDENT_CURRENT('".$cio['csvio_table']."')", array(0));
                $getLastID = !empty($getLastID) ? $getLastID : 0;
            } else {
                $getLastID = 0;
            }
            $cio['csvio_lastinsert'] = $getLastID;


            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=> '成功取得匯入檔案資料！！', 'fInfo'=> $cio);
            echo json_encode($rtn);
            exit();
        }
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '取得匯入檔案資料失敗，請重新操作或聯絡管理人員！！');
        echo json_encode($rtn);
    break;
    case "func_ad_csvimport":////////////////////////////////////////// func_ad_csvimport 匯入檔案資料
        $csvID = isset($_POST['csvID']) ? $_POST['csvID'] : 0;
        $csvMode = isset($_POST['csvMode']) ? $_POST['csvMode'] : 'i';
        $csvTrun = isset($_POST['csvTrun']) ? $_POST['csvTrun'] : '';
        $csvName = isset($_POST['csvName']) ? $_POST['csvName'] : '';
        $opraMode = $csvMode=='i' ? 'insert mode' : ($csvMode=='u' ? 'update mode' : 'auto mode');
        $opraMode .=  ($csvTrun=='t' ? ' after truncate' : '');
        $colNum = 0;
        $toTable = '';
        $toColArr = array();
        $cProcessData=0;
        $cDoneData=0;
        $cErrorData=0;
        $errorRowStr='';
        $file = fopen("../dataFiles/csvio/".$csvName, "r");
        $i=1;


/*
        while (($line = fgetcsv($file)) !== FALSE) {
            if($i==1){
                $toTable = $line[3];
            } elseif($i==2){
                for($li=0;$li<count($line);$li++){
                    if(!empty($line[$li])) {
                        $toColArr[$li]=$line[$li];
                    }
                }
                $colNum = count($toColArr);
            } elseif($i==3){
                if($csvTrun=='t') {
                    $trun = DB->query("TRUNCATE TABLE ".$toTable, array());
                }
            } elseif($i==5){

                $actArr=array();
                foreach( $toColArr as $key => $value){
                    //if($key>0 || ($key==0 && !empty($line[$key]))){
                        $actArr[$value] = $line[$key];
                    //}
                }

                if(empty($line[0])){
                    unset($actArr[$toColArr[0]]);
                }
                $ins = DB->insert($toTable, $actArr);

            }
            $i++;
        }
        fclose($file);
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> $actArr, 'num'=>count($actArr));
        echo json_encode($rtn);
        exit();
*/




        while (($line = fgetcsv($file)) !== FALSE) {
            if($i==1){
                $toTable = $line[3];
            } elseif($i==2){
                for($li=0;$li<count($line);$li++){
                    if(!empty($line[$li])) {
                        $toColArr[$li]=$line[$li];
                    }
                }
                $colNum = count($toColArr);
            } elseif($i==3){
                if($csvTrun=='t') {
                    $trun = DB->query("TRUNCATE TABLE ".$toTable, array());
                }
            } else {
                $actArr=array();
                foreach( $toColArr as $key => $value){
                    if($key>0){ $actArr[$value] = $line[$key]; }
                }
                /*
                for($ai=1;$ai<$colNum;$ai++){
                    $actArr[$toColArr[$ai]]=$line[$ai];
                }
                */

                switch($csvMode){
                    case 'i':
                        $cProcessData++;
                        if(!empty($line[0])){
                            $actArr[$toColArr[0]] = $line[0];
                        }                        
                        //$rtn = array('RTN'=>0, 'msgType'=>'error', 'toTable'=> $toTable, 'actArr'=> $actArr);
                        //echo json_encode($rtn);
                        //exit();
                        $ins = DB->insert($toTable, $actArr);
                        if($ins && $ins > 0){
                            $cDoneData++;
                        } else {
                            $cErrorData++;
                            $errorRowStr .= "error insert row: ".$i."\n";
                        }
                    break;
                    case 'u':
                        $cProcessData++;
                        if(!empty($line[0])){
                            $idCol[$toColArr[0]]=$line[0];
                            $up = DB->update($toTable, $actArr,$idCol);
                            if($up){
                                $cDoneData++;
                            } else {
                                $cErrorData++;
                                $errorRowStr .= "error update row: ".$i."\n";
                            }
                        } else {
                            $cErrorData++;
                            $errorRowStr .= "no ID exist row: ".$i."\n";
                        }
                    break;
                    case 'a':
                        $cProcessData++;
                        if(!empty($line[0])){
                            $idCol[$toColArr[0]]=$line[0];
                            $up = DB->update($toTable, $actArr,$idCol);
                            if($up){
                                $cDoneData++;
                            } else {
                                $cErrorData++;
                                $errorRowStr .= "error update row: ".$i."\n";
                            }
                        } else {
                            $ins = DB->insert($toTable, $actArr);
                            if($ins && $ins > 0){
                                $cDoneData++;
                            } else {
                                $cErrorData++;
                                $errorRowStr .= "error insert row: ".$i."\n";
                            }
                        }
                    break;
                    default:

                    break;
                }

            }
            $i++;
        }
        fclose($file);
        $errorMess='';
        if(!empty($cErrorData)){
            $errorMess = "，但有".$cErrorData."筆資料操作失敗";
            $preMess = "\n-------------------------------\n";
            $preMess .= "csv filename: ".$csvName."\n";
            $preMess .= "operation date: ".date('Y-m-d H:i:s')."\n";
            $preMess .= "data table: ".$toTable."\n";
            $preMess .= "operation mode: ".$opraMode."\n";
            $preMess .= "administrator: ".CF_AD_NICK."【 ID: ".CF_AD_ID." 】\n";
            $preMess .= "-------------------------------\n\n";
            $preMess .= "error at rows:\n\n";
            $postMess = "\n\n-------------------------------\n";
            $errorRowStr = $preMess.$errorRowStr.$postMess;
            $log_filename = "../dataFiles/csvio/".$csvName.".".date('YmdHis').".log";
            $fp = fopen($log_filename, 'w');
            if(fwrite($fp, $errorRowStr)!==false){
                $errorMess .= "，已記錄於log檔案中";
            } else {
                $errorMess .= "，log檔案寫入失敗";
            }
            fclose($fp);
        }

        if($cDoneData > 0){
            $ilog = DB->insSysLogs(51, CF_AD_ID, 2651, '["'.CF_AD_ID.'","'.$csvName.'","'.$cDoneData.'"]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=> '成功匯入 '.$cDoneData.' 筆檔案資料'.$errorMess.'！！', 'done'=>$cDoneData, 'error'=>$cDoneData, 'importNum'=>$i-4);
            echo json_encode($rtn);
            return false;
        } else {
            $ilog = DB->insSysLogs(51, CF_AD_ID, 2650, '["'.CF_AD_ID.'","'.$csvName.'"]');//寫入操作記錄
            if($cProcessData > 0){
                $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '匯入檔案資料失敗，請檢查匯入檔案中，ID欄位的賦值狀態，在非自動匯入狀態下，ID有賦值的欄位無法新增，而ID為空值的欄位無法編輯，詳情請查閱 ErrorLog 檔案！！', 'done'=>0, 'importNum'=>0);
                echo json_encode($rtn);
            } else{
                $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '檔案讀取失敗，無法進行檔案匯入動作，請重新操作或聯絡管理人員，詳情請查閱 ErrorLog 檔案！！', 'done'=>0, 'importNum'=>0);
                echo json_encode($rtn);
            }
        }
    break;
    case "func_ad_csvdelete":////////////////////////////////////////// func_ad_csvdelete 刪除匯入檔案資料
        $csvID = isset($_POST['csvID']) ? $_POST['csvID'] : 0;
        $csvName = isset($_POST['csvName']) ? $_POST['csvName'] : '';
        if(!empty($csvID)){
            $del = DB->query("DELETE FROM sys_csvio WHERE csvio_id=?", array($csvID));
            if($del){
                if(unlink("../dataFiles/csvio/".$csvName)){
                    $ilog = DB->insSysLogs(51, CF_AD_ID, 2621, '["'.CF_AD_ID.'","'.$csvName.'"]');//寫入操作記錄
                    $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=> '刪除已匯入檔案資料成功！！');
                    echo json_encode($rtn);
                    exit();
                } else {
                    $ilog = DB->insSysLogs(51, CF_AD_ID, 2630, '["'.CF_AD_ID.'","'.$csvName.'"]');//寫入操作記錄
                    $rtn = array('RTN'=>1, 'msgType'=>'warning', 'msgStr'=> '刪除已匯入檔案資料成功，但檔案刪除失敗，請手動刪除檔案「'.$csvName.'」！！');
                    echo json_encode($rtn);
                    exit();
                }
            }
        }
        $ilog = DB->insSysLogs(51, CF_AD_ID, 2620, '["'.CF_AD_ID.'","'.$csvName.'"]');//寫入操作記錄
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '刪除已匯入檔案資料失敗，請重新操作或聯絡管理人員！！');
        echo json_encode($rtn);
    break;
    case "func_ad_csvedit":////////////////////////////////////////// func_ad_csvedit 編輯匯入檔案資料
        $csvID = isset($_POST['csvID']) ? $_POST['csvID'] : 0;
        $ar=$arrDatA;
        $ar['csvio_updated']='[EXPR]NOW()';
        if(!empty($csvID)){
            $up = DB->update('sys_csvio', $ar,array('csvio_id'=>$csvID));
            if($up){
                $ilog = DB->insSysLogs(51, CF_AD_ID, 2611, '["'.CF_AD_ID.'","'.$csvID.'"]');//寫入操作記錄
                $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=> '編輯已匯入檔案資料成功！！');
                echo json_encode($rtn);
                exit();
            }
        }
        $ilog = DB->insSysLogs(51, CF_AD_ID, 2610, '["'.CF_AD_ID.'","'.$csvID.'"]');//寫入操作記錄
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '編輯已匯入檔案資料失敗，請重新操作或聯絡管理人員！！');
        echo json_encode($rtn);
    break;
    default:///////////////////////// default
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> '沒有功能項目被指定，系統將認定為程式碼錯誤，請聯絡管理人員！！');
        echo json_encode($rtn);

    break;


}

?>
