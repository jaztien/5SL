<?php
//////////////////////////// sys.act.func.php /////////////////////////////////////
//////////////////////////// 系統參數設定處理功能執行檔案 //////////////////////////////
//////////////////////////// 2023-02-05 By Jaz Tien ///////////////////////////////
//////////////////////////// jaztien@gmail.com ////////////////////////////////////
//-------------------------------------------------------------------------------//
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
//-------------------------------------------------------------------------------//
if( empty($_POST['arrDatA']) || empty($_POST['formAcce'])){
    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，表單傳送失敗！請聯絡相關管理人員！！'));
    exit();
}
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
//-------------------------------------------------------------------------------//
$arrDatA = isset($_POST['arrDatA']) ? $_POST['arrDatA'] : null;
$formAcce = isset($_POST['formAcce']) ? $_POST['formAcce'] : '';
$cDateTime =  date('Y-m-d H:i:s');
//-------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------//
switch ($formAcce) {
    case "func_sys_config":////////////////////////////////////////// 更新管理者資料merch_startdate

        $ar=$arrDatA;

        $succeCount=0;
        $i=0;
        while($i<count($ar)){
            $name=$ar[$i]['name'];
            $cy = $DB->single("SELECT config_crypt FROM fsl_configurations WHERE config_code=? ",array($name));
            $val = (intval($cy)==1) ? $ar[$i]['value'] : $ar[$i]['value'] ;
            $setArr = array('config_value' => $val);
            $up = $DB->update('fsl_configurations',$setArr ,array('config_code'=>$name));
            if($up){ $succeCount++; }
            $i++;
        }
        $DB->closeConnection();
        if($succeCount > 0){
            /*
            switch($formAcce){
                case 'form_compinfo':
                    $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功編輯公司資料！！', 'upTime'=> $cDateTime);
                break;
                case 'form_sysinfo':
                    $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功編輯系統訊息！！', 'upTime'=> $cDateTime);
                break;
            }
            */
            $rtn = array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功編輯系統訊息！！', 'upTime'=> $cDateTime);
        //** 寫入操作記錄 **//


        //** /寫入操作記錄 **//
        } else {
            $rtn = array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'編輯參數設定資料失敗！請聯絡相關管理人員！！');
        }
        echo json_encode($rtn);
    break;
    //--------------------------- 同步校正操作(評價記錄) ---------------------------//func_data_revision//
    case "func_data_revision":

        $forVal = $arrDatA['forVal'] ?? 0;
        $foridVal = $arrDatA['foridVal'] ?? 0;
        $actionType = $arrDatA['actionType'] ?? '';
        $passHour = $arrDatA['passHour'] ?? 0;
        $thisVisits = $arrDatA['thisVisits'] ?? 0;
        $thisRateTop = $arrDatA['thisRateTop'] ?? 0;
        $thisRateBottom = $arrDatA['thisRateBottom'] ?? 0;
        
        if($forVal <= 0 || $foridVal <= 0 || $actionType == ''){
            echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要傳遞值，同步校正操作失敗！請聯絡相關管理人員！！'));
            exit();
        }
        $geJS=_fn_getCachedJsonData("../jsonFiles/general.json");
        $geJSfor = $geJS['idfor'][$forVal] ?? null;
        $toTable = $geJSfor['table'] ?? '';
        $getPreCol = $geJSfor['precol'] ?? '';
        $toIdCol = $getPreCol.'id';
        $rateMode = $geJSfor['ratemode'] ?? 0;
        if(empty($geJSfor) || empty($toTable) || empty($getPreCol)){
            echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'遺失必要參數值，同步校正操作失敗！請聯絡相關管理人員！！'));
            exit();
        }
        //當要校正造訪記錄時
        if($actionType == 'doVisit') {
            $toCol = $getPreCol.'visits';
            $doSql = "SELECT COUNT(1) AS totalSUM";
            $doSql .= " FROM data_visits WHERE vit_for = :vit_for AND vit_forid = :vit_forid";
            $res = $DB->row($doSql, array('vit_for'=>$forVal,'vit_forid'=>$foridVal));
            if(empty($res)){
                echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'同步校正操作失敗！請聯絡相關管理人員！！'));
                exit();
            } else {
                if($res['totalSUM'] == $thisVisits){
                    echo json_encode(array('RTN'=>1, 'msgType'=>'warning', 'msgStr'=>'造訪次數比對相符，無需同步校正！'));
                    exit();
                } else {
                    $up = $DB->update($toTable, array($toCol => $res['totalSUM']), array($toIdCol => $foridVal));
                    if($up){
                        echo json_encode(array('RTN'=>1, 'msgType'=>'success', 'msgStr'=>'成功同步校正造訪次數，由 '.$thisVisits.' 次改為 '.$res['totalSUM'].' 次！', 'newVisits'=>$res['totalSUM']));
                        exit();
                    }
                    echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'同步校正操作失敗！請聯絡相關管理人員！！'));
                    exit();
                }
            }

        }
        //當要校正評價記錄，且評價模式不爲0時
        else if($actionType == 'doRate' && $rateMode > 0) {
            $rateSql = "SELECT COUNT(1) AS totalCount";
            // 依據評價模式1:五組星級評價，計算總平均與各組平均
            if ($rateMode == 1) {
                $rateSql .= ", AVG((rat_s1+rat_s2+rat_s3+rat_s4+rat_s5)/5) AS totalAvg";
                $rateSql .= ", SUM(rat_s1+rat_s2+rat_s3+rat_s4+rat_s5) AS totalSum";
                $rateSql .= ", AVG(rat_s1) AS S1, AVG(rat_s2) AS S2, AVG(rat_s3) AS S3, AVG(rat_s4) AS S4, AVG(rat_s5) AS S5";
            }
            // 依據評價模式2:單組星級評價，計算總平均與單組平均
            elseif($rateMode == 2){
                $rateSql .= ", AVG(rat_s1) AS totalAvg";
                $rateSql .= ", SUM(rat_s1) AS totalSum";
                $rateSql .= ", AVG(rat_s1) AS S1";
            }
            // 依據評價模式3:讚與倒讚二元制，計算總讚數與總倒讚數
            elseif($rateMode == 3){
                $rateSql .= ", SUM(rat_s1) AS totalLike";
                $rateSql .= ", SUM(rat_s2) AS totalUnlike";
                $rateSql .= ", SUM(rat_s1 +rat_s2) AS totalSum";
            }
            $rateSql .= " FROM fsl_rating WHERE rat_for = :rat_for AND rat_forid = :rat_forid";

            try {
                $rateRes = false; 
                $rawRow = $DB->row($rateSql, array('rat_for' => $forVal, 'rat_forid' => $foridVal));
                $rateRes = ($rawRow === false) ? 0 : $rawRow;
            } catch (PDOException $e) {
                $rateRes = false; // 發生 SQL 錯誤，確保值維持為 false
                error_log("【SQL致命錯誤攔截】" . $e->getMessage());
            }

            if($rateRes === false){
                echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'同步校正操作失敗！請聯絡相關管理人員！！'));
                exit();
            }
            $newTotalAvg = ($rateRes['totalAvg'] === null || $rateRes['totalAvg'] === '') ? 4.20 : (float)$rateRes['totalAvg'];
            $newTotalCount = ($rateRes['totalCount'] === null) ? 0 : (int)$rateRes['totalCount'];
            if($newTotalCount === 0){
                echo json_encode(array('RTN'=>0, 'msgType'=>'warning', 'msgStr'=>'沒有任何的評價記錄，停止同步校正！！'));
                exit();
            } else if($rateRes['totalSum'] == $thisRateTop && $newTotalCount == $thisRateBottom){
                echo json_encode(array('RTN'=>1, 'msgType'=>'warning', 'msgStr'=>'評價次數與評分總數比對相符，無需同步校正！'));
                exit();
            }
            $updateArray = array();
            $getRateTop     = $getPreCol . 'ratetop';
            $getRateBottom  = $getPreCol . 'ratebottom';
            $updateArray[$getRateTop] = $rateRes['totalSum'];
            $updateArray[$getRateBottom] = $newTotalCount;
            $newDataStr = '';
            require_once('../classFiles/eRanking.class.php');
            if($rateMode == 1){
                $getS1Col       = $getPreCol . 's1';
                $getS2Col       = $getPreCol . 's2';
                $getS3Col       = $getPreCol . 's3';
                $getS4Col       = $getPreCol . 's4';
                $getS5Col       = $getPreCol . 's5';
                $updateArray = array(
                    $getRateTop => $rateRes['totalSum'],
                    $getRateBottom => $newTotalCount,
                    $getS1Col => $rateRes['S1'],
                    $getS2Col => $rateRes['S2'],
                    $getS3Col => $rateRes['S3'],
                    $getS4Col => $rateRes['S4'],
                    $getS5Col => $rateRes['S5']
                );
                $newDataStr = $rateRes['S1'].','.$rateRes['S2'].','.$rateRes['S3'].','.$rateRes['S4'].','.$rateRes['S5'];
                //$newTotalAvg = $rateRes['totalAvg'];
                $newLSR = Evs::lsrFromAvg($newTotalAvg, $newTotalCount);
                $newTDS = Evs::tds($newTotalAvg, $newTotalCount, $thisVisits, $passHour);
                $msgStr = '成功同步校正評價記錄計算，總得分由 '.$thisRateTop.' 改為 '.$rateRes['totalSum'].'；總評價次數由 '.$thisRateBottom.' 次改為 '.$newTotalCount.' 次！！';
            } elseif($rateMode == 2){
                $getS1Col       = $getPreCol . 's1';
                $updateArray = array(
                    $getRateTop => $rateRes['totalSum'],
                    $getRateBottom => $newTotalCount,
                    $getS1Col => $rateRes['S1'] ?? 0
                );
                $newDataStr = $rateRes['S1'];
                //$newTotalAvg = $rateRes['totalAvg'];
                $newLSR = Evs::lsrFromAvg($newTotalAvg, $newTotalCount);
                $newTDS = Evs::tds($newTotalAvg, $newTotalCount, $thisVisits, $passHour);
                $msgStr = '成功同步校正評價記錄計算，總得分由 '.$thisRateTop.' 改為 '.$rateRes['totalSum'].'；總評價次數由 '.$thisRateBottom.' 次改為 '.$newTotalCount.' 次！！';
            } elseif($rateMode == 3){
                $updateArray = array(
                    $getRateTop => $rateRes['totalLike'],
                    $getRateBottom => $rateRes['totalUnlike'],
                );
                $newUpdate = Evs::update($getPreCol,$rateMode,[$getRateTop => $rateRes['totalLike'], $getRateBottom => $rateRes['totalUnlike']],0);
                $newTotalAvg = $newUpdate['totalAVG'];
                $newLSR = Evs::lsrForLikes($rateRes['totalLike'], $rateRes['totalUnlike']);
                $newTDS = Evs::tds($newTotalAvg, $newTotalCount, $thisVisits, $passHour);
                $msgStr = '成功同步校正評價記錄計算，總獲贊次數由 '.$thisRateTop.' 次改為 '.$rateRes['totalLike'].'次；總倒讚次數由 '.$thisRateBottom.' 次改為 '.$rateRes['totalUnlike'].' 次！！';
            } 
            if(!empty($updateArray)){
                //$upSQL = $DB->update($toTable, $updateArray, array($toIdCol => $foridVal));
                $upSQL = 1;
                if($upSQL && $upSQL > 0){
                    echo json_encode(
                        array(
                            'RTN'=>1, 
                            'msgType' => 'success', 
                            'msgStr' => $msgStr, 
                            'newRateTop' => $rateRes['totalSum'], 
                            'newRateBottom' => $newTotalCount,
                            'newTotalAvg' => $newTotalAvg,
                            'newLSR' => $newLSR,
                            'newTDS' => $newTDS,
                            'newData' => $newDataStr
                        )
                    );
                    exit();
                }
                echo json_encode(array('RTN'=>0, 'msgType'=>'error', 'msgStr'=>'同步校正評價記錄計算操作失敗！請聯絡相關管理人員！！'));
                exit();
            }

        }

    break;
}
                              
?>