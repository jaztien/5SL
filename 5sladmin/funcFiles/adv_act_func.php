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
        echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'TOKEN 驗證值比對不符，表單傳送失敗！請聯絡相關管理人員！！'));
        exit();
    }
}
//-------------------------------------------------------------------------------//
$adviID = isset($_POST['adviID']) ? intval($_POST['adviID']) : 0; 
$advpID = isset($_POST['advpID']) ? intval($_POST['advpID']) : 0; 
$advrID = isset($_POST['advrID']) ? intval($_POST['advrID']) : 0; 
$arrDatA = $_POST['arrDatA'] ?? null;
$arrDatB = $_POST['arrDatB'] ?? null;
$cDateTime = date('Y-m-d H:i:s');

$datapass = true;
$formAcce = $_POST['formAcce'] ?? "";
if( empty($formAcce)){ 
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
if($formAcce=='form_photoupdate') {
    if(!isset($_POST["image"])){ $datapass = false; }
} else if($formAcce=='func_advp_infoUpdate') {
    if(empty($advpID) || empty($arrDatA)){ $datapass = false; }
} else if($formAcce=='func_advp_addnew') {
    if(empty($arrDatA)){ $datapass = false; }
//} else if($formAcce=='form_authupdate') {
    //if(empty($arrDatB)){ $datapass = false; }
} else if($formAcce=='func_advp_delete') {
    if(empty($advpID)||$advrID=="0"){ $datapass = false; }
} else if($formAcce=='func_advp_ruleUpdate') {
    if(empty($advrID)||$advrID=="0"){
        $formAcce='func_advp_ruleAddnew';
    }
} else if($formAcce=='func_adv_applyUpdate') {
    if(empty($adviID)||$adviID==""){
        $formAcce='func_adv_sendApply';
    }
} 

if($datapass == false){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
//-------------------------------------------------------------------------------//

switch ($formAcce) {
     case "func_advp_infoUpdate":////////////////////////////////////////// func_advp_infoUpdate 更新廣告套裝資料
        //----- 檢查投放規則，若無，終止更新！ -------//
        $dupC = $DB->query("SELECT * FROM advi_rules WHERE advp_id=? AND advr_status=?",array($advpID,true));
        if(!$dupC||$dupC==0){
            echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'廣告套裝未包含投放規則，中止更新！！'));
            exit();
        }
        //----- //檢查投放規則，若無，終止更新！ -------//
        $arrDatA['advp_updated'] = '[EXPR]GETDATE()';
        $ar=$arrDatA;
        /*
        $setArr=array();
        $i=0;
        while($i<count($ar)){
            $name=$ar[$i]['name'];
            $val=$ar[$i]['value'];
            $setArr = array_merge($setArr, array($name => $val));
            $i++;
        }
        */
        $up = $DB->update('advi_pack', $ar,array('advp_id'=>$advpID));
        if($up){

            //------------ 代表圖像處理 --------------//
            $imgUp = $_POST["imgUp"];
            $newUpPhoto = '';
            if(!empty($imgUp)){
                $imgUpRtn = false;
                if($imgUp=='@DELETE@'){
                    if(
                        @unlink('../uploadFiles/adv/advpphoto_' . $advpID . '_.jpg') && 
                        @unlink('../uploadFiles/adv/advpphoto_' . $advpID . '_thumb.jpg') && 
                        @unlink('../uploadFiles/adv/advpphoto_' . $advpID . '_tiny.jpg')
                    ){
                        $imgUpRtn = true;
                        $newUpPhoto ='../imageFiles/nopic_400x300.jpg';
                    }
                } else {
                    $image_array_1 = explode(";", $imgUp);
                    $image_array_2 = explode(",", $image_array_1[1]);
                    $imgUp = base64_decode($image_array_2[1]);
                    if(!empty($advpID)){
                        $imageName = '../uploadFiles/adv/advpphoto_'. $advpID .'_.jpg';
                        if (file_put_contents($imageName, $imgUp) !== false) {//---------------- 製作縮圖 ----------------//
                            $thumbName = '../uploadFiles/adv/advpphoto_' . $advpID . '_thumb.jpg';
                            $toThumb = _fn_resize_image($imageName,$thumbName, 400, 300);
                            if($toThumb){
                                $tinyName = '../uploadFiles/adv/advpphoto_' . $advpID . '_tiny.jpg';
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
            $ilog = $DB->insSysLogs(51, CF_AD_ID, 2311, '["'.CF_AD_ID.'","'.$advpID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功編輯廣告套裝資料'.(isset($imgUpRtn) ? ($imgUpRtn ? '，以及代表圖像' : '，但圖像編輯失敗') : '').'！！', 'newUpPhoto'=> $newUpPhoto, 'upTime'=> $cDateTime); 
            //$ilog = $DB->insSysLogs(CF_AD_ID,1,'02311',CF_AD_ID.','.$advpID);//寫入操作記錄
            //$rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功編輯廣告套裝資料！！', 'upTime'=> $cDateTime);
        } else {
            $ilog = $DB->insSysLogs(51, CF_AD_ID, 2310, '["'.CF_AD_ID.'","'.$advpID.'"]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'編輯廣告套裝資料失敗！請聯絡相關管理人員！！');
        }
        echo json_encode($rtn);
    break;
    case "func_advp_addnew":////////////////////////////////////////// form_addnew 新增廣告套裝資料
        $inArr=$arrDatA;
        //$inArr = array_merge($ar, array('advp_published'=>$cDateTime));
        //---------------- 重複輸入檢查 ------------------//
        $dupC = $DB->query("SELECT * FROM advi_pack WHERE advp_keycode=? OR advp_title=?",array($inArr['advp_keycode'],$inArr['advp_title']));
        if($dupC){
            echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'廣告套裝重複建立！！'));
            exit();
        }
       //---------------- /重複輸入檢查 ------------------//
        $ins = $DB->insert('advi_pack', $inArr);
        if($ins && $ins > 0){
            //------------ 代表圖像處理 --------------//
            $imgUp = !empty($_POST["imgUp"]) ? $_POST["imgUp"] : "";
            $newUpPhoto = '';
            if(!empty($imgUp)){
                $imgUpRtn = false;
                /*
                if($imgUp=='@DELETE@'){
                    if(
                        @unlink('../uploadFiles/adv/advpphoto_' . $advpID . '_.jpg') && 
                        @unlink('../uploadFiles/adv/advpphoto_' . $advpID . '_thumb.jpg') && 
                        @unlink('../uploadFiles/adv/advpphoto_' . $advpID . '_tiny.jpg')
                    ){
                        $imgUpRtn = true;
                        $newUpPhoto ='../imageFiles/nopic_400x300.jpg';
                    }
                } else {
                */
                    $image_array_1 = explode(";", $imgUp);
                    $image_array_2 = explode(",", $image_array_1[1]);
                    $imgUp = base64_decode($image_array_2[1]);
                    if(!empty($ins)){
                        $imageName = '../uploadFiles/adv/advpphoto_'. $ins .'_.jpg';
                        if (file_put_contents($imageName, $imgUp) !== false) {//---------------- 製作縮圖 ----------------//
                            $thumbName = '../uploadFiles/adv/advpphoto_' . $ins . '_thumb.jpg';
                            $toThumb = _fn_resize_image($imageName,$thumbName, 400, 300);
                            if($toThumb){
                                $tinyName = '../uploadFiles/adv/advpphoto_' . $ins . '_tiny.jpg';
                                $toTiny = _fn_resize_image($imageName,$tinyName, 120, 90);
                                if($toTiny){
                                    $imgUpRtn = true;
                                    $newUpPhoto = $thumbName;
                                }
                            }                        
                        }
                    }
                //}
            }
            //------------ /代表圖像處理 --------------//
            
            //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功新增廣告套裝！！', 'newID'=>$ins); 
        } 
        else { 
            //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'新增廣告套裝失敗！請聯絡相關管理人員！！'); 
        } /* 回傳失敗時的資訊 */
        echo json_encode($rtn);
    break;
    case "func_advp_delete":////////////////////////////////////////// func_advp_delete 刪除廣告推廣套裝
        $del = $DB->query("DELETE FROM advi_pack WHERE advp_id = ?", array($advpID)); 
        if($del){
            //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功刪除廣告套裝！！'); 
        } else {
            //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'刪除廣告套裝失敗！請聯絡相關管理人員！！'); 
        }
        echo json_encode($rtn);
    break;
    case "func_advp_ruleUpdate":////////////////////////////////////////// func_advp_ruleUpdate 更新管理者資料
        $arrDatA['advr_updated'] = '[EXPR]GETDATE()';
        $up = $DB->update('advi_rules', $arrDatA,array('advr_id'=>$advrID));
        if($up){
            //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功編輯廣告投放規則！！','arr'=>array('newID'=>0));
        } else {
            //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'編輯廣告投放規則失敗！請聯絡相關管理人員！！');
        }
        echo json_encode($rtn);
    break;
    case "func_advp_ruleAddnew":////////////////////////////////////////// func_advp_ruleAddnew 新增投放規則
        $ar=$arrDatA;
        $ar = array_merge($ar, array('advp_id' => $advpID));
        //---------------- 重複輸入檢查 ------------------//
        $dupSql = "SELECT * FROM advi_rules WHERE advp_id=". $advpID ." AND (";
        $dupSql .= "((advr_positgroup<>0 AND advr_positgroup=?) AND ((advr_weekday<>0 AND advr_weekday=?) OR (advr_weekend<>0 AND advr_weekend=?)) AND (advr_period=? OR advr_period=1))";
        $dupSql .= " OR ((advr_topsearch<>0 AND advr_topsearch=?) AND ((advr_weekday<>0 AND advr_weekday=?) OR (advr_weekend<>0 AND advr_weekend=?)) AND (advr_period=? OR advr_period=1)))";

        $dupArr = array(
            $ar['advr_positgroup'],
            $ar['advr_weekday'],
            $ar['advr_weekend'],
            $ar['advr_period'],
            $ar['advr_topsearch'],
            $ar['advr_weekday'],
            $ar['advr_weekend'],
            $ar['advr_period']
        );
        $dupC = $DB->query($dupSql,$dupArr);
        if($dupC && count($dupC)>0){
            echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'投放規則重複登錄！！'));
            exit();
        }
        //---------------- /重複輸入檢查 ------------------//
        $ins = $DB->insert('advi_rules',$ar);
        if($ins && $ins > 0){
            //$ar = array_merge($ar, array('newID' => $ins));
            $ar['newID'] = $ins;
            //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功新增廣告投放規則！！','arr'=>$ar);
            
        } else {
            //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'新增廣告投放規則失敗！請聯絡相關管理人員！！');
        }
        echo json_encode($rtn);    
    break;
    case "func_advp_ruleDelete":////////////////////////////////////////// func_advp_ruleDelete 刪除投放規則
        $del = $DB->query("DELETE FROM advi_rules WHERE advr_id = ?", array($advrID)); 
        if($del){
            //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功刪除廣告套裝！！', 'advrID'=>$advrID); 
        } else {
            //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'刪除廣告套裝失敗！請聯絡相關管理人員！！'); 
        }
        echo json_encode($rtn);
    break;
    case "func_adv_addapply":////////////////////////////////////////// func_adv_addapply 更新
        $fileS = isset($_FILES['fileS']) ? $_FILES['fileS'] : null;
        $fileB = isset($_FILES['fileB']) ? $_FILES['fileB'] : null;
        if(
            empty($_POST['advp_id']) ||
            empty($_POST['mb_id']) ||
            empty($_POST['advi_startdate']) ||
            empty($_POST['advi_slogan']) ||
            (intval($_POST['advi_advtype'])==1 && empty($_POST['merch_id'])) || 
            (intval($_POST['advi_advtype'])==2 && empty($_POST['sv_id'])) || 
            (intval($_POST['advi_advtype'])==3 && empty($_POST['advi_outsite']))
        ){
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'缺少廣告投放素材，請填妥必填表格！！');
            echo json_encode($rtn);
            exit();
        }
        if((empty($_POST['advi_defaultimg']) && is_null($fileS)) || is_null($fileB)){
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'缺少廣告投放素材，請上傳兩種格式的廣告圖樣！！');
            echo json_encode($rtn);
            exit();
        }
        $addResult=false;       
        $endDate = date('Y-m-d', strtotime($_POST['advi_startdate'] . ' + '. $_POST['period'] .' days'));
        $ar = array(
            'advp_id'=>intval($_POST['advp_id']),
            'mb_id'=>intval($_POST['mb_id']),
            'merch_id'=>intval($_POST['merch_id']),
            'sv_id'=>intval($_POST['sv_id']),
            'advi_advtype'=>intval($_POST['advi_advtype']),
            'advi_outsite'=>$_POST['advi_outsite'],
            'advi_slogan'=>$_POST['advi_slogan'],
            //'advi_defaultimg'=>$_POST['advi_defaultimg'],
            //'advi_outsite'=>$_POST['advi_outsite'],
            'advi_startdate'=>$_POST['advi_startdate'],
            'advi_enddate'=>$endDate,
            'advi_limitmode'=>$_POST['advi_limitmode'],
            'advi_visitlimit'=>$_POST['advi_visitlimit'],
            'advi_status'=>6
        );
        $ins = $DB->insert('advitising',$ar);
        if($ins && $ins > 0){
            $photoUp=true;
            if($fileS){
                $fNameS = basename($fileS["name"]); 
                $fTypeS = pathinfo($fNameS, PATHINFO_EXTENSION); 
                $fPathS = '../uploadFiles/adv/adviphoto_' . $ins . '_.'.$fTypeS;
                if(!move_uploaded_file($fileS["tmp_name"], $fPathS)){ $photoUp=false; }
            } else {
                if(!empty($_POST['advi_defaultimg'])&&!empty($_POST['merch_id'])){
                    $subFolder=ceil(intval($_POST['merch_id'])/100);
                    $mdImgPath = "../uploadFiles/md/".$subFolder."/mdimg_".intval($_POST['merch_id'])."_default_thumb.jpg";
                    $copySnew = "../uploadFiles/adv/adviphoto_".$ins."_.jpg";
                    @copy($mdImgPath, $copySnew);
                } else {
                    $photoUp=false; 
                }
            }
            if($fileB){
                $fNameB = basename($fileB["name"]); 
                $fTypeB = pathinfo($fNameB, PATHINFO_EXTENSION); 
                $fPathB = '../uploadFiles/adv/adviphoto_' . $ins . '_banner.'.$fTypeB;
                if(!move_uploaded_file($fileB["tmp_name"], $fPathB)){ $photoUp=false; }
            }
                //---------------- 製作縮圖 ----------------//
            /*
            $src = imagecreatefromjpeg($imageName);
            $thumb = imagecreatetruecolor(400, 300);
            imagecopyresized($thumb, $src, 0, 0, 0, 0, 400, 300, 800, 600);
            $thumbName = '../uploadFiles/adv/advpphoto_' . $advpID . '_thumb.jpg';
            imagejpeg($thumb, $thumbName);
            */
            //---------------- /製作縮圖 ----------------//
            if($photoUp==true){
                //---------------- 預扣點數 ----------------//
                $ptArr = array(
                    'mb_id' => intval($_POST['mb_id']),
                    'mbp_trade' => (0-intval($_POST['cost'])),
                    'mbp_for' => 5,
                    'mbp_forid' => $ins,
                    'mbp_status' => 2
                );
                $pins = $DB->insert('mb_points',$ptArr);
                if($pins && $pins>0){
                    $addResult=true;
                }
            }
            //---------------- /預扣點數 ----------------//
            if($addResult==true){
                //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
                $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功申請廣告套裝！！');
                echo json_encode($rtn);    
                exit();
            }
        }
            //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
        $del = $DB->query("DELETE FROM advitising WHERE advi_id = ?", array($ins)); 
        $pdel = $DB->query("DELETE FROM mb_points WHERE mbp_for = ? AND mbp_forid = ?", array(5,$ins)); 
        @unlink("../uploadFiles/adv/adviphoto_".$ins."_.jpg");
        @unlink("../uploadFiles/adv/adviphoto_".$ins."_banner.jpg");
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'申請廣告套裝失敗！請聯絡相關管理人員！！');
        echo json_encode($rtn);    
    break;
    case "func_adv_applyUpdate":////////////////////////////////////////// func_adv_applyUpdate
        $arrDatA['advi_updated'] = '[EXPR]GETDATE()';
        $up = $DB->update('advitising', $arrDatA,array('advi_id'=>$adviID));
        if($up){
            //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功編輯廣告投放申購資料！！');
        } else {
            //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'編輯廣告投放申購資料失敗！請聯絡相關管理人員！！');
        }
        echo json_encode($rtn);
    break;
    case "func_adv_sendApply":////////////////////////////////////////// form_doapply 建立執行紀錄
        // to status 1
        $sql="SELECT * FROM advitising WHERE advi_id = ?";
        $res = $DB->row($sql,array($adviID));
        //---- 建立執行紀錄
        $rsql="SELECT * FROM advi_rules WHERE advp_id = ?";
        $rres = $DB->query($rsql,array($res['advp_id']));
        $insNum = 0;
      
        $i=0;
        while($i<count($rres)){
            $inArr = array(
                'mb_id' => $res['mb_id'],
                'advi_id' => $adviID,
                'advc_starttime' => $res['advi_startdate'],
                'advc_endtime' => $res['advi_enddate'],
                'advc_weekday' => $rres[$i]['advr_weekday'],
                'advc_weekend' => $rres[$i]['advr_weekend'],
                'advc_period' => $rres[$i]['advr_period'],
                'advc_fixed' => $rres[$i]['advr_fixed'],
                'advc_positnum' => $rres[$i]['advr_positnum'],
                'advc_positgroup' => $rres[$i]['advr_positgroup'],
                'advc_topsearch' => $rres[$i]['advr_topsearch'],
                'advc_indistinct' => $rres[$i]['advr_indistinct'],
                'advc_limitmode' => $rres[$i]['advr_limitmode'],
                'advc_outsite' => $res['advi_outsite'],
                'advc_slogan' => $res['advi_slogan'],
                'advc_status' => 1
            );
            $ins = $DB->insert('adv_cues', $inArr);
            if($ins && $ins>0){ $insNum++; }
            $i++;
        }
       // $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=> $res['mb_id']);
        //echo json_encode($rtn); exit();
        
        //---- /建立執行紀錄
        if($insNum==count($rres)){ 
            $up = $DB->update("advitising", array(
                'advi_updated'=>'[EXPR]GETDATE()',
                'advi_status'=>1
            ), array('advi_id'=>$adviID));
            if($up){
                //---- 扣除預扣點數
                $pup = $DB->update('mb_points', array(
                    'mbp_status'=>1
                ),array('mb_id'=>$res['mb_id'],'mbp_for'=>5,'mbp_forid'=>$adviID));
                //---- 通知申購人

                //---- /通知申購人
                if($pup){
                    //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
                    $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'已發佈且生成執行紀錄');
                    echo json_encode($rtn); 
                    exit();
                }
            }
        }
        $up = $DB->update("advitising", array(
            'advi_updated'=>'[EXPR]GETDATE()',
            'advi_status'=>6
        ), array('advi_id'=>$adviID));
        $del = $DB->query("DELETE FROM adv_cues WHERE advi_id = ?", array($adviID)); 
        //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'更改審核狀態失敗，已清除所有已建立的紀錄！');
        echo json_encode($rtn); 

    break;
    case "func_adv_doReject":////////////////////////////////////////// form_doreject 駁回
        if(!empty($arrDatA['advv_content'])){
            //$inArr = array_merge($arrDatA, array('advv_published'=>$cDateTime));
            $inArr = $arrDatA;
            $ins = $DB->insert('advi_review', $inArr);
        }
        $up = $DB->update("advitising", array(
            'advi_updated'=>'[EXPR]GETDATE()',
            'advi_status'=>$arrDatA['advv_step']
        ), array('advi_id'=>$adviID));
        if($up){
            //---- 退回預扣點數
            $pup = $DB->update('mb_points', array('mbp_status'=>0),array('mb_id'=>$arrDatA['mb_id'],'mbp_for'=>5,'mbp_forid'=>$adviID));
            //---- /退回預扣點數
            if($pup){
                //$ilog = $DB->insSysLogs(51, CF_AD_ID, nnnnn, '["vari1","vari2",...]');//寫入操作記錄
                $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'駁回廣告申購案件成功！');
                echo json_encode($rtn);
                exit();
            }
        }
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'駁回廣告申購案件失敗！');
        echo json_encode($rtn); 

    break;
    case "func_adv_mbsearch":////////////////////////////////////////// func_adv_mbsearch 
        if (is_null($_POST['search']) || trim($_POST['search']) == '') {
            exit();
        }
        $search = $_POST['search'];
        $params = [];
        // 1. 安全地建立查詢條件（避免 SQL 注入與引數不匹配錯誤）
        // 處理 LIKE 的部分，將參數放入陣列中
        $sql = "SELECT mb_id, mb_name, mb_title, mb_coexpert FROM members WHERE mb_title LIKE ?";
        $params[] = "%" . $search . "%";
        // 如果是數字，再額外加上 OR 條件並新增綁定參數
        if (is_numeric($search)) { 
            $sql .= " OR mb_id = ?";
            $params[] = $search;
        }
        // 2. 執行查詢
        $res = $DB->query($sql, $params);
        $totalMB = count($res);
        // 3. 建立符合 Select2 格式的乾淨陣列
        $output = [];
        if ($totalMB > 0) {
            $i = 0;
            while ($i < $totalMB) {
                // 解密名字
                $decrypted_name = _fn_secure_crypto($res[$i]['mb_name'], 'de');
                // 組合 Select2 要求的文字
                $text_string = '【ID:' . $res[$i]['mb_id'] . '】' . $decrypted_name . $res[$i]['mb_title'] . '[' . $res[$i]['mb_coexpert'] . ']';
                // 直接塞入 PHP 陣列，免去手動拼字串的痛苦
                $output[] = [
                    'id'   => (string)$res[$i]['mb_id'],
                    'text' => $text_string
                ];
                $i++;
            }
        }
        // 4. 設定正確的標頭，並由 PHP 自動轉為完美的 JSON
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($output, JSON_UNESCAPED_UNICODE);
        exit();
    break;


}  
                        
?>