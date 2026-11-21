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
if($formAcce=='form_photoupdate') {
    if(!isset($_POST["image"])){ $datapass = false; }
} else if($formAcce=='func_mb_infoUpdate') {
    if(empty($_POST['mbID']) || empty($_POST['arrDatA']) ||  empty($_POST['arrDatB'])){ $datapass = false; }
} else if($formAcce=='func_mb_addnew') {
    if(empty($_POST['arrDatA']) || empty($_POST['arrDatB'])){ $datapass = false; }
} else if($formAcce=='func_mb_needUpdate') {
    if(empty($_POST['arrNeed']) || empty($_POST['mbID'])){ $datapass = false; }
} else if($formAcce=='func_mb_delete') {
    if(empty($_POST['mbID'])){ $datapass = false; }
}
if($datapass == false){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}

//-------------------------------------------------------------------------------//
$mbID = isset($_POST['mbID']) ? intval($_POST['mbID']) : 0; 
$arrDatA = isset($_POST['arrDatA']) ? $_POST['arrDatA'] : null;
$arrDatB = isset($_POST['arrDatB']) ? $_POST['arrDatB'] : null;
$arrNeed = isset($_POST['arrNeed']) ? $_POST['arrNeed'] : null;
$cDateTime = date('Y-m-d H:i:s');

//-------------------------------------------------------------------------------//
switch ($formAcce) {
    case "func_mb_infoUpdate":////////////////////////////////////////// 更新管理者資料
        $imgUp = $_POST['imgUp'] ?? '';
        $mainPhotoPath = '';
        $thumbPhotoPath = '';         
        if (!empty($arrDatA) || !empty($arrDatB)) {
            DB->beginTransaction();
            try {
                if (!empty($arrDatA)) {
                    $arrDatA['mb_updated'] = '[EXPR]NOW()';
                    $arrDatA['mb_name'] = !empty($arrDatA['mb_name']) ? _fn_secure_crypto($arrDatA['mb_name']) : '';
                    $arrDatA['mb_birth'] = !empty($arrDatA['mb_birth']) ? $arrDatA['mb_birth'] : null;
                    $arrDatA['mb_add'] = !empty($arrDatA['mb_add']) ? _fn_secure_crypto($arrDatA['mb_add']) : '';
                    $arrDatA['mb_cellphone'] = !empty($arrDatA['mb_cellphone']) ? _fn_secure_crypto($arrDatA['mb_cellphone']) : '';
                    $arrDatA['mb_email'] = !empty($arrDatA['mb_email']) ? _fn_secure_crypto($arrDatA['mb_email']) : '';
                    $arrDatA['mb_line'] = !empty($arrDatA['mb_line']) ? _fn_secure_crypto($arrDatA['mb_line']) : '';
                    $arrDatA['mb_google'] = !empty($arrDatA['mb_google']) ? _fn_secure_crypto($arrDatA['mb_google']) : '';
                    $upA = DB->update('fsl_members', $arrDatA, array('mb_id'=>$mbID));
                }
                if(!empty($arrDatB)){
                    $upB = DB->update('mb_info', $arrDatB, array('mb_id'=>$mbID));
                }

                if ($upA === 0 || $upB === 0 || $upA === false || $upB === false) {
                    // 主動大喊救命！強制中斷並空降到 catch 區塊執行全面撤銷
                    throw new Exception("雙表同步失敗：會員主要資料表影響 {$upA} 筆，進階資料表影響 {$upB} 筆，其中一方查無資料！");
                    $ilog = DB->insSysLogs(51, CF_AD_ID, 2220, '["'.CF_AD_ID.'","'.$mbID.'"]');//寫入操作記錄//
                    $returnArr = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'編輯會員資料失敗！請聯絡相關管理人員！！');
                }

                if(!empty($imgUp)){
                    $imgUpRtn = false;
                    if($imgUp=="@DELETE@"){
                        if(
                            @unlink('../uploadFiles/mb/mbphoto_' . $mbID . '_.jpg') &&
                            @unlink('../uploadFiles/mb/mbphoto_' . $mbID . '_thumb.jpg')
                        ){
                            $imgUpRtn = true;
                        }
                    } else {
                        $image_array_1 = explode(";", $imgUp);
                        $image_array_2 = explode(",", $image_array_1[1]);
                        $imgUp = base64_decode($image_array_2[1]);
                        $mainPhotoPath = '../uploadFiles/mb/mbphoto_' . $mbID . '_.jpg';
                        if (file_put_contents($mainPhotoPath, $imgUp)!==false){
                            chmod($mainPhotoPath, 0755);
                        }
                        //---------------- 製作縮圖 ----------------//
                        $thumbPhotoPath = '../uploadFiles/mb/mbphoto_' . $mbID . '_thumb.jpg';
                        $toThumb = _fn_resize_crop($mainPhotoPath, $thumbPhotoPath, 100, 100);
                        if($toThumb){
                            chmod($thumbPhotoPath, 0755);
                            $imgUpRtn = true;
                        } else {
                            @unlink($mainPhotoPath);
                        }
                    }
                }
                DB->commit();
                $ilog = DB->insSysLogs(51, CF_AD_ID, 2221, '["'.CF_AD_ID.'","'.$mbID.'"]');//寫入操作記錄
                $returnArr = array('RTN'=>1, 'msgType'=>(isset($imgUpRtn) ? ($imgUpRtn ? 'success' : 'warning') : 'success'), 'msgStr'=>'成功編輯會員資料'.(isset($imgUpRtn) ? ($imgUpRtn ? '，以及代表圖像' : '，但圖像編輯失敗') : '').'！！', 'upTime'=> $cDateTime);
            } catch (Exception $e) {
                DB->rollBack();
                // 如果圖像已經生成，就刪除它
                if (!empty($mainPhotoPath) && is_file($mainPhotoPath)) {
                    @unlink($mainPhotoPath);
                }
                if (!empty($thumbPhotoPath) && is_file($thumbPhotoPath)) {
                    @unlink($thumbPhotoPath);
                }
                error_log("[雙表 update 交易攔截] 更新已安全撤銷。成因: " . $e->getMessage());
                $ilog = DB->insSysLogs(51, CF_AD_ID, 2220, '["'.CF_AD_ID.'","'.$mbID.'"]');//寫入操作記錄
                $returnArr = array('RTN'=>0, 'msgType'=>'warning', 'msgStr'=>'編輯會員資料失敗！請聯絡相關管理人員！！【ERROR】:'.$e->getMessage(), 'upTime'=> $cDateTime);
            }

        } else {
            $ilog = DB->insSysLogs(51, CF_AD_ID, 2220, '["'.CF_AD_ID.'","'.$mbID.'"]');//寫入操作記錄//
            $returnArr = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'傳入的會員資料為空，編輯會員資料失敗！請聯絡相關管理人員！！');
        }
        echo json_encode($returnArr);
        exit();

    break;
    //----------- form_addnew 新增管理者資料
    case "func_mb_addnew"://----------- form_addnew 新增管理者資料
        $insID=1;
        $ilog = DB->insSysLogs(51, CF_AD_ID, 2211, '["'.CF_AD_ID.'","'.$insID.'"]');//寫入操作記錄//
        $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功新增會員資料！！', 'upTime'=> $cDateTime, 'insID'=> $insID);
        echo json_encode($rtn);
        exit();
        $ilog = DB->insSysLogs(51, CF_AD_ID, 2210, '["'.CF_AD_ID.'","'.$mbID.'"]');//寫入操作記錄//
        $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'新增會員資料失敗，請重新操作或聯絡管理人員！！');
        echo json_encode($rtn);
       
    break;
    case "func_mb_delete":////////////////////////////////////////// func_mb_delete 刪除會員資料，這個刪除功能僅僅是將會員狀態改成8，也就是丟進垃圾桶，仍然可以取回。
        //$del = DB->query("DELETE FROM fsl_members WHERE mb_id = ?", array($mbID)); 
        $up = DB->query("UPDATE fsl_members SET mb_updated=getdate(), mb_status = ? WHERE mb_id = ?", array(8,$mbID));
        if($up){
            $ilog = DB->insSysLogs(51, CF_AD_ID, 2251, '["'.CF_AD_ID.'","'.$mbID.'"]');//寫入操作記錄//
            $rtn = array('RTN'=>1); 
        } else {
            $ilog = DB->insSysLogs(51, CF_AD_ID, 2250, '["'.CF_AD_ID.'","'.$mbID.'"]');//寫入操作記錄//
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'刪除會員資料失敗！請聯絡相關管理人員！！'); 
        }
        echo json_encode($rtn);
    break;
    case "func_mb_needUpdate":////////////////////////////////////////// func_mb_needUpdate 更新課程商品需求範圍
        $cid = DB->single("SELECT mbn_id FROM mb_needs WHERE mb_id=?", array($mbID));
        if($cid && $cid>0){
            $up = DB->update('mb_needs',array('mbn_array'=>json_encode($arrNeed)),array('mbn_id'=>$cid));
            if($up==1){
                $ilog = DB->insSysLogs(51, CF_AD_ID, 2231, '["'.CF_AD_ID.'","'.$mbID.'"]');//寫入操作記錄
                $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功編輯課程商品需求範圍！！', 'upTime'=> $cDateTime);
            } else {
                $ilog = DB->insSysLogs(51, CF_AD_ID, 2230, '["'.CF_AD_ID.'","'.$mbID.'"]');//寫入操作記錄
                $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'編輯課程商品需求範圍失敗！請聯絡相關管理人員！！');
            }
        } else {
                $ins = DB->insert('mb_needs', array('mb_id'=>$mbID,'mbn_array'=>json_encode($arrNeed)));
                if($ins){
                    $ilog = DB->insSysLogs(51, CF_AD_ID, 2241, '["'.CF_AD_ID.'","'.$mbID.'"]');//寫入操作記錄
                    $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功新增課程商品需求範圍！！', 'upTime'=> $cDateTime);
                } else {
                    $ilog = DB->insSysLogs(51, CF_AD_ID, 2240, '["'.CF_AD_ID.'","'.$mbID.'"]');//寫入操作記錄
                    $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'新增課程商品需求範圍失敗！請聯絡相關管理人員！！'.$ins);
                }
        }
        echo json_encode($rtn);
    break;
    case "func_mb_selector":////////////////////////////////////////// func_mb_selector 
        $ifCate = $_POST['ifCate'] ?? "";//單字元 int 會員種類
        $ifCotype = $_POST['ifCotype'] ?? "";//數字組以逗號分隔 string 合作方種類 
        $ifExpert = $_POST['ifExpert'] ?? "";//數字組以逗號分隔 string 專業服務項目
        $search = $_POST['search'] ?? "";
        $mbJS = _fn_getCachedJsonData("../jsonFiles/member.json");
        foreach($mbJS['cotype'] as $key=>$Val){
        $coTypeJS[$key]=$Val['type'];
        foreach($Val['expert'] as $keyT=>$ValT){
            $expertJS[$keyT]=$ValT;
        }
        }
        $arr = '[]';
        if($search == ''){
            exit();
        }
        $sql="SELECT a.mb_id, a.mb_title, b.mb_cate, b.mb_cotype, b.mb_coexpert FROM fsl_members AS a INNER JOIN mb_info AS b ON b.mb_id=a.mb_id WHERE";
        if (is_numeric($search)) {
            $sql .= " a.mb_id=?";//主鍵欄不得鍵入非數值，否則 WHERE 不起作用【bug】
        } else {
            $sql .= " a.mb_title LIKE '%".$search."%'";
        }

        if($ifCate) $sql.=" AND b.mb_cate='".$ifCate."'";//當 $ifCate 賦值時，「會員種類」欄位為其一的搜尋條件

        if (!empty($ifCotype)) {
            $coTypeArr = explode(',', $ifCotype);
            $sql .= " AND EXISTS(SELECT 1 FROM OPENJSON(b.mb_cotype) WHERE";
            if(count($coTypeArr) == 1) $sql .= " value = '".$ifCotype."'";
            else $sql .= " value IN (".$ifCotype.")";
            $sql .= ")";
        }//當 $ifCotype 賦值時，「合作方種類」欄位為其一的搜尋條件

        if (!empty($ifExpert)) {
            $coExpertArr = explode(',', $ifExpert);
            $sql .= " AND EXISTS(SELECT 1 FROM OPENJSON(b.mb_coexpert) WHERE";
            if(count($coExpertArr) == 1) $sql .= " value = '".$ifExpert."'";
            else $sql .= " value IN (".$ifExpert.")";
            $sql .= ")";
        }//當 $ifCotype 賦值時，「合作方種類」欄位為其一的搜尋條件
        $res = DB->query($sql,array($search));
        $totalMB = count($res);
        if($totalMB>0){
            $arr = '[';
            $i = 0; //以$i為$nameList這個陣列的索引值
            while($i < $totalMB){ 
                //$arr .= '{"id":"'.$res[$i]['mb_id'].'","text":"【ID:'.$res[$i]['mb_id'].'】'.$res[$i]['mbinfo_name'].'(帳戶:'.$res[$i]['mb_account'].')"}';//使用Select2插件無論是key或Val，都必須加「"」。
                $arr .= '{';
                $arr .= '"id":"'.$res[$i]['mb_id'].'",';
                $arr .= '"text":"';
                $arr .= '【'.$res[$i]['mb_id'].'】';//會員ID
                $arr .= $res[$i]['mb_title'];//會員暱稱
                $coTypeStr = '';
                $coExpertStr = '';
                if(!empty($res[$i]['mb_cotype']) && $res[$i]['mb_cotype'] != '[]'){
                    $ti=0;
                    $ctJS = json_decode($res[$i]['mb_cotype']);
                    $cnt = count($ctJS)-1;
                    foreach($ctJS as $val){
                        //$coTypeStr .= $mbJS['cotype'][$val]['type'];
                        $coTypeStr .= $coTypeJS[$val];
                        if($ti<$cnt) $coTypeStr .= ',';
                        $ti++;
                    } 
                    $arr .= ' ['.$coTypeStr.']';//會員合作方種類
                }
                if(!empty($res[$i]['mb_coexpert']) && $res[$i]['mb_coexpert'] != '[]'){
                    $ei=0;
                    $ceJS = json_decode($res[$i]['mb_coexpert']);
                    $cnt = count($ceJS)-1;
                    foreach($ceJS as $val){
                        $coExpertStr .= $expertJS[$val];
                        if($ei<$cnt) $coExpertStr .= ',';
                        $ei++;
                    } 
                    $arr .= ' ['.$coExpertStr.']';//會員專業服務項目
                }

                $arr .= '"';
                $arr .= '}';//使用Select2插件無論是key或Val，都必須加「"」。
                if($i!=$totalMB-1){ $arr .= ','; }
                $i++;
            }
            $arr .= ']';
        }
        echo $arr;    
    break;

}  
?>