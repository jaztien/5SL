<?php
    error_reporting(E_ALL); 
	include 'aprret_func.php';
	include 'PHPExcel/Classes/PHPExcel.php';
    header("Content-Type:text/html; charset=utf-8");
    $host = 'localhost';
    $dbuser ='root';
    $dbpassword = '1111';
    $dbname = 'jazhome';

    $link = mysqli_connect($host,$dbuser,$dbpassword,$dbname);
    if($link){
        mysqli_query($link,'SET NAMES utf8');
        //echo "正確連接資料庫";
    }
    else {
        //echo "不正確連接資料庫</br>" . mysqli_connect_error();
    }
    $startTime = time();
    $fromID = isset($_POST['fromID']) ? $_POST['fromID'] : '';
    $toID = isset($_POST['toID']) ? $_POST['toID'] : '';
    $folderr = isset($_POST['folderr']) ? $_POST['folderr'] : '';
    $oktobox = isset($_POST['oktobox']) ? $_POST['oktobox'] : '';
    $doloop = isset($_POST['doloop']) ? $_POST['doloop'] : '';
    $donealert = isset($_POST['donealert']) ? $_POST['donealert'] : '';
    /*
    $osql = "SELECT SUM(f_rows - f_startrow) AS CC FROM filelist";
    //$osql .= " WHERE f_year='".$folderr."'";
    $osql .= " WHERE f_id BETWEEN 1450 AND 1529";
    $oresult = mysqli_query($link, $osql);
    $orow = mysqli_fetch_array($oresult);
    echo $folderr."年紀錄數： ".$orow['CC'].'<hr>';
    */
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	
  <title> 人生第五季 Fifth Season Life </title>

  <style>
    .checksum{
        border-spacing: 0px;
    }
    .checksum td{
        border-bottom:#CCCCCC 1px solid;
        padding-left:8px;
        text-align:right;
    }
  </style>
  
  </head>
<body>

<div style="font-size:16px;line-height:20px;">

<table class="checksum">
        <tr>
            <td>年份</td>
            <td>起始ID</td>
            <td>終止ID</td>
            <td>終止頁數</td>
            <td>紀錄數量小計</td>
            <td>紀錄疊加數量</td>
            <td>檔案分割最末ID</td>
        </tr>
        <tr>
            <td>101</td>
            <td>1</td>
            <td>427</td>
            <td>5</td>
            <td>185752</td>
            <td>185752</td>
            <td>100|200|427</td>
        </tr>
        <tr>
            <td>102</td>
            <td>428</td>
            <td>1004</td>
            <td>11</td>
            <td>445882</td>
            <td>631634</td>
            <td>550|650|850|1004</td>
       </tr> 
       <tr>
            <td>103</td>
            <td>1005</td>
            <td>1521</td>
            <td>16</td>
            <td>368989</td>
            <td>1000623</td>
            <td>1100|1200|1300|1521</td>
       </tr> 
       <tr>
            <td>104</td>
            <td>1522</td>
            <td>1993</td>
            <td>21</td>
            <td>307176</td>
            <td>1307799</td>
            <td>1650|1800|1993</td>
       </tr> 
       <tr>
            <td>105</td>
            <td>1994</td>
            <td>2453</td>
            <td>25</td>
            <td>276984</td>
            <td>1584783</td>
            <td>2120|2250|2453</td>
       </tr> 
       <tr>
            <td>106</td>
            <td>2454</td>
            <td>2933</td>
            <td>30</td>
            <td>311587</td>
            <td>1896370</td>
            <td>2600|2750|2933</td>
       </tr> 
       <tr>
            <td>107</td>
            <td>2934</td>
            <td>3430</td>
            <td>35</td>
            <td>329800</td>
            <td>2226170</td>
            <td>3080|3200|3430</td>
       </tr> 
       <tr>
            <td>108</td>
            <td>3431</td>
            <td>3962</td>
            <td>40</td>
            <td>378475</td>
            <td>2604645</td>
            <td>3580|3720|3962</td>
       </tr> 
       <tr>
            <td>109</td>
            <td>3963</td>
            <td>4508</td>
            <td>46</td>
            <td>414323</td>
            <td>3018968</td>
            <td>4130|4280|4508</td>
       </tr> 
       <tr>
            <td>110</td>
            <td>4509</td>
            <td>5032</td>
            <td>51</td>
            <td>386596</td>
            <td>3405564</td>
            <td>4680|4850|5032</td>
       </tr> 
       <tr>
            <td>111</td>
            <td>5033</td>
            <td>5494</td>
            <td>56</td>
            <td>297375</td>
            <td>3702939</td>
            <td>5180|5300|5494</td>
       </tr> 
       <tr>
            <td>112</td>
            <td>5495</td>
            <td>5918</td>
            <td>60</td>
            <td>(292356)288620</td>
            <td>(3995295)3991559</td>
            <td>5620|5750|5918</td>
       </tr> 
    </table>


<?php
    $checkLoading = 1;
    if(empty($fromID) || empty($toID) || empty($folderr)){ echo "尚未設定年份或起始結束ID段！";$checkLoading = 0; }
    if(empty($checkLoading)){
        echo "<script>window.parent.document.getElementById('loading').style.display = 'none';</script>";
        exit();
    }
    $locJS=_fn_getCachedJsonData("wejoin.json");
    //if($fromID==1){ $fromID=0; }
    //$folderr2="110";
    $cTotalRowNum=0;
    $cTotalDupRows=0;
    $cTotalInsRows = 0;

    if(!empty($oktobox)){
        $csv_folderpath = "aprretFiles/CSV_single/";
        $csv_toPath = "aprretFiles/CSV_arranged/".$folderr."/";
        $objPHPExcel = new PHPExcel();                     //實例化一個PHPExcel()對象
        $objSHEET = $objPHPExcel->getActiveSheet();        //選取當前的sheet對象
        $objSHEET->setCellValueByColumnAndRow(0, 1,'ID')//預留ID欄位
            ->setCellValueByColumnAndRow(1, 1,'城市碼')
            ->setCellValueByColumnAndRow(2, 1,'地區碼')
            ->setCellValueByColumnAndRow(3, 1,'地址')       //*原始
            ->setCellValueByColumnAndRow(4, 1,'社區簡稱')    //*原始
            ->setCellValueByColumnAndRow(5, 1,'社區ID')
            ->setCellValueByColumnAndRow(6, 1,'緯度')//lat
            ->setCellValueByColumnAndRow(7, 1,'經度')//lng
            ->setCellValueByColumnAndRow(8, 1,'時間戳')//交易日期 microtime
            ->setCellValueByColumnAndRow(9, 1,'交易日期')    //*原始
            ->setCellValueByColumnAndRow(10, 1,'總價')       //*原始
            ->setCellValueByColumnAndRow(11, 1,'單價')       //*原始
            ->setCellValueByColumnAndRow(12, 1,'總面積')     //*原始
            ->setCellValueByColumnAndRow(13, 1,'主建物佔比')  //*原始
            ->setCellValueByColumnAndRow(14, 1,'型態')      //*原始
            ->setCellValueByColumnAndRow(15, 1,'型態ID')
            ->setCellValueByColumnAndRow(16, 1,'屋齡')      //*原始
            ->setCellValueByColumnAndRow(17, 1,'交屋年份')   //*
            ->setCellValueByColumnAndRow(18, 1,'樓別/樓高')  //*原始
            ->setCellValueByColumnAndRow(19, 1,'地下層數')
            ->setCellValueByColumnAndRow(20, 1,'地下起始層')
            ->setCellValueByColumnAndRow(21, 1,'單戶層數')
            ->setCellValueByColumnAndRow(22, 1,'所在樓層')
            ->setCellValueByColumnAndRow(23, 1,'樓高')
            ->setCellValueByColumnAndRow(24, 1,'附屬建物')    //*原始
            ->setCellValueByColumnAndRow(25, 1,'交易標的')    //*原始
            ->setCellValueByColumnAndRow(26, 1,'交易筆棟數')   //*原始
            ->setCellValueByColumnAndRow(27, 1,'土地筆數')
            ->setCellValueByColumnAndRow(28, 1,'建物筆數')
            ->setCellValueByColumnAndRow(29, 1,'車位筆數')
            ->setCellValueByColumnAndRow(30, 1,'車位總價')     //*原始
            ->setCellValueByColumnAndRow(31, 1,'建物格局')     //*原始
            ->setCellValueByColumnAndRow(32, 1,'房間數')
            ->setCellValueByColumnAndRow(33, 1,'廳數')
            ->setCellValueByColumnAndRow(34, 1,'衛浴數')
            ->setCellValueByColumnAndRow(35, 1,'管理組織')     //*原始
            ->setCellValueByColumnAndRow(36, 1,'管理組織ID')          //管理組織ID
            ->setCellValueByColumnAndRow(37, 1,'電梯')        //*原始
            ->setCellValueByColumnAndRow(38, 1,'電梯ID')      //電梯ID
            ->setCellValueByColumnAndRow(39, 1,'主要用途')     //*原始
            ->setCellValueByColumnAndRow(40, 1,'用途ID')
            ->setCellValueByColumnAndRow(41, 1,'備註')        //*原始
            ->setCellValueByColumnAndRow(42, 1,'備註組ID')    //備註組ID陣列
            ->setCellValueByColumnAndRow(43, 1,'狀態')        //狀態
            ->setCellValueByColumnAndRow(44, 1,'操作註記');   //操作註記
    
            // 取得資料 (欄位名稱 and 欄位值)
        $sql = "SELECT * FROM filelist";
        $sql .= " WHERE f_year='".$folderr."'";
        $sql .= " AND f_id BETWEEN ".$fromID." AND ".$toID;
        //$sql .= " WHERE f_id =1";
        //$sql .= " WHERE f_year IN ('".$folderr."','".$folderr2."')";
        //$sql .= " LIMIT 1";
        $result = mysqli_query($link, $sql);
        mysqli_data_seek($result, 0);  // 指標移到第一筆資料
        $chkSameFile = '';
        $chkSameOpt=0;
        $chkDoneOpt=0;
        $getBR = "";
        $insertRow=2;//跳過第一列抬頭列
        while($flist = mysqli_fetch_assoc($result)){
            //-------- 取得基本參數 -------------------------------
            $csv_subfolder = $csv_folderpath.$flist['f_year'].'/';//取得檔案路徑
            $highestRow = $flist['f_rows'];           //總列數
            $startRow = $flist['f_startrow']+1;       //起始列數
            $totalRows = $highestRow - $startRow;   //實際有效列數
            $cTotalRowNum += ($flist['f_rows']-$flist['f_startrow']);            //累積每個檔案的有效列數總和
            $getCity = $flist['f_city'];              //取得城市代碼
            $getDist = $flist['f_district'];          //取得區域代碼
            
            //--------- 載入檔案 ----------------------------------
            $EXL= PHPExcel_IOFactory::load($csv_subfolder.$flist['f_name']); 
            $SHEET=$EXL->getSheet(0);
            //--------- 自原始檔案提取程式碼以及區域碼 ----------------------------------
            $nameSplit=@explode("_",$flist['f_name']);
            $getCity = $nameSplit[0];//城市代碼 00
            $getDist = $nameSplit[1];//區域代碼 0000
            //$highestRow = $SHEET->getHighestRow(); // 取得總列數
            //$highestRow = 10;
            //-------------------- 開始每個檔案中每筆紀錄一一表列 -----------------------
            for ($rowNum = $startRow; $rowNum <= $highestRow; $rowNum++) {
                $toDBsql = "(NULL, ";
                $objSHEET->setCellValueByColumnAndRow(0, $insertRow, '');//預留 ID
                $objSHEET->setCellValueByColumnAndRow(1, $insertRow, $getCity);//寫入【A】城市代碼
                $toDBsql .= "'".$getCity."',";
                $objSHEET->setCellValueByColumnAndRow(2, $insertRow, $getDist);//寫入【B】區域代碼
                $toDBsql .= "'".$getDist."',";
                //----- 地段位置或門牌 ---------------------------------------------------------
                $getFormatAdd = _fn_get_fulladdress($getCity,$getDist,$SHEET->getCellByColumnAndRow(0, $rowNum)->getValue());//讀取原始檔案A欄位住址，檢查並重新格式
                //$getFormatAdd = _fn_get_fulladdress($locJS[$getCity]['text'],$locJS[$getCity]['district'][$getDist]['text'],$SHEET->getCellByColumnAndRow(0, $rowNum)->getValue());//讀取原始檔案A欄位住址，檢查並重新格式
                $objSHEET->setCellValueByColumnAndRow(3, $insertRow, $getFormatAdd['addonly']);//寫入【C】地段位置或住址門牌
                $toDBsql .= "'".addslashes($getFormatAdd['addonly'])."',";
                //------ 社區簡稱處理 ----------------------------------------------------------
                $getCommOrig = $SHEET->getCellByColumnAndRow(1, $rowNum)->getValue();//讀取原始檔案B欄位社區簡稱
                $objSHEET->setCellValueByColumnAndRow(4, $insertRow, $getCommOrig);//寫入社區簡稱
                $toDBsql .= "'".addslashes($getCommOrig)."',";
                $objSHEET->setCellValueByColumnAndRow(5, $insertRow, '');//寫入社區ID
                $toDBsql .= "'0',";
                //------ 座標處理 -------------------------------------------------------------
                $objSHEET->setCellValueByColumnAndRow(6, $insertRow, '');//寫入 lat (後續補齊)
                $toDBsql .= "'0.0',";
                $objSHEET->setCellValueByColumnAndRow(7, $insertRow, '');//寫入 lng (後續補齊)
                $toDBsql .= "'0.0',";
                //------ 日期處理 -------------------------------------------------------------
                $getDateOrig =  $SHEET->getCellByColumnAndRow(2, $rowNum)->getValue();//讀取原始檔案C欄位交易年月
                $aYear = '';
                $nDate = '0001-01-01';
                $nDateStamp = '0';
                if(!empty($getDateOrig)){
                    $nDateSplit = @explode("/",$getDateOrig);
                    $aYear = $nDateSplit[0];
                    $nDate = ($aYear+1911)."-".$nDateSplit[1]."-".$nDateSplit[2];
                    $nDate = str_replace('-00','-01',$nDate);
                    $nDateStamp = strtotime($nDate);
                }
                $objSHEET->setCellValueByColumnAndRow(8, $insertRow, $nDateStamp);//寫入【E】交易日期時間戳
                $toDBsql .= "'".$nDateStamp."',";
                $objSHEET->setCellValueByColumnAndRow(9, $insertRow, $nDate);//寫入【E】交易日期時間戳【原始】
                $toDBsql .= "'".$nDate."',";
                //------ 直接移轉 -------------------------------------------------------------
                $getTotal = $SHEET->getCellByColumnAndRow(3, $rowNum)->getValue();
                //if(empty($getTotal)){ $getTotal='0.0'; }
                $objSHEET->setCellValueByColumnAndRow(10, $insertRow, (float)$getTotal);//讀取原始檔案D欄位「總價(萬元)」直接寫入【F】總價(萬元)
                $toDBsql .= "'".(float)$getTotal."',";
                $getUnitprice = $SHEET->getCellByColumnAndRow(4, $rowNum)->getValue();
                //if(empty($getUnitprice)){ $getUnitprice='0.0'; }
                $objSHEET->setCellValueByColumnAndRow(11, $insertRow, (float)$getUnitprice);//讀取原始檔案E欄位「單價」直接寫入【G】單價
                $toDBsql .= "'".(float)$getUnitprice."',";
                $getTotalarea = $SHEET->getCellByColumnAndRow(5, $rowNum)->getValue();
                //if(empty($getTotalarea)){ $getTotalarea='0.0'; }
                $objSHEET->setCellValueByColumnAndRow(12, $insertRow, (float)$getTotalarea);//讀取原始檔案F欄位「總面積」直接寫入【H】「總面積」
                $toDBsql .= "'".(float)$getTotalarea."',";
                //------ 主建物佔比處理 -------------------------------------------------------------
                $getPercentage = $SHEET->getCellByColumnAndRow(6, $rowNum)->getValue();
                $getPercentage = str_replace('%','',$getPercentage);
                $objSHEET->setCellValueByColumnAndRow(13, $insertRow, (float)$getPercentage);//寫入【I】主建物佔比【移除%】
                $toDBsql .= "'".(float)$getPercentage."',";
                //------ 課程商品型態(轉換為數值)---------------------------------------------------------
                $getBCType = $SHEET->getCellByColumnAndRow(7, $rowNum)->getValue();
                $objSHEET->setCellValueByColumnAndRow(14, $insertRow, $getBCType);//寫入【J】型態【分析後以數字代替】 1-公寓(5樓含以下無電梯);2-透天厝;3-店面(店鋪);4-辦公商業大樓;5-住宅大樓(11層含以上有電梯);6-華廈(10層含以下有電梯);7-套房(1房1廳1衛);8-工廠;9-廠辦;10-農舍;11-倉庫;
                $toDBsql .= "'".$getBCType."',";
                switch($getBCType){
                    case '其他': $getType=0; break;
                    case '公寓(5樓含以下無電梯)': $getType=1; break;
                    case '透天厝': $getType=2; break;
                    case '店面(店鋪)': $getType=3; break;
                    case '辦公商業大樓': $getType=4; break;
                    case '住宅大樓(11層含以上有電梯)': $getType=5; break;
                    case '華廈(10層含以下有電梯)': $getType=6; break;
                    case '套房(1房(1廳)1衛)': $getType=7; break;
                    case '工廠': $getType=8; break;
                    case '廠辦': $getType=9; break;
                    case '農舍': $getType=10; break;
                    case '倉庫': $getType=11; break;
                    default:$getType=0; break;
                }
                $objSHEET->setCellValueByColumnAndRow(15, $insertRow, $getType);//寫入【J】型態【分析後以數字代替】 1-公寓(5樓含以下無電梯);2-透天厝;3-店面(店鋪);4-辦公商業大樓;5-住宅大樓(11層含以上有電梯);6-華廈(10層含以下有電梯);7-套房(1房1廳1衛);8-工廠;9-廠辦;10-農舍;11-倉庫;
                $toDBsql .= "'".$getType."',";
                //------ 建物完工日期(轉換為日期)---------------------------------------------------------
                $getHouseAge = $SHEET->getCellByColumnAndRow(8, $rowNum)->getValue();
                //if(empty($getHouseAge)){ $getHouseAge = '0'; }
                $objSHEET->setCellValueByColumnAndRow(16, $insertRow, (int)$getHouseAge);//讀取原始檔案I欄位「屋齡」直接寫入【K】屋齡【計算建造日期】
                $toDBsql .= "'".(int)$getHouseAge."',";
                $getComplete = '0000';
                if(!empty($aYear)){
                    $getComplete = (int)$aYear - (int)$getHouseAge + 1911;
                }
                $objSHEET->setCellValueByColumnAndRow(17, $insertRow, $getComplete);//留著為用屋齡計算出的課程商品完工年份【L】
                $toDBsql .= "'".$getComplete."',";
                //------ 樓別/樓高 ----------------------------------------------------------
                $getFloorStr = trim($SHEET->getCellByColumnAndRow(9, $rowNum)->getValue());//讀取原始檔案J欄位「樓別/樓高」
                $setBasement = '0';//單戶地下層數
                $setBdFloor = '0';//單戶地上層數
                $setOnFloor = '0';//地上層數所在(起始)層數
                $setOnBasement = '0';//地下層數所在(起始)層數
                $setTotalFloor = '0';//樓高
                $setOutBD = '';
                if(!empty($getFloorStr)){
                /*
                    $setBasement = '';//單戶地下層數
                    $setBdFloor = '';//單戶地上層數
                    $setOnFloor = '';//地上層數所在(起始)層數
                    $setOnBasement = '';//地下層數所在(起始)層數
                    $setTotalFloor = '';//樓高
                    $setOutBD = '';
                } else {
                    $setBasement = 0;//單戶地下層數
                    $setBdFloor = 0;//單戶地上層數
                    $setOnFloor = 0;//地上層數所在(起始)層數
                    $setOnBasement = 0;//地下層數所在(起始)層數
                    $setTotalFloor = 0;//樓高
                */

                    $setInBD = array();
                    $setOutBD = null;
                    $aboveBD = array();
                    $underBD = array();
                    //$getFloor=strchr($getFloorStr,"/",true);
                    $getFloor = @explode('/',$getFloorStr);
                    $getFloorSplit=@explode(',',$getFloor[0]);
                    $setTotalFloor = str_replace(array('層','地下'),'',@$getFloor[1]);
                    $setTotalFloor = _fn_num_b2h($setTotalFloor);
                    $getFloorSplit = array_filter($getFloorSplit);//移除空值
                    $getFloorSplit = array_values($getFloorSplit);
                    $getFScount = count($getFloorSplit);//分子值項目數量
                    if($getFScount==1){//分子值僅有一個項目時
                        $getOnly = trim($getFloorSplit[0]);//定義這個陣列值內容，以便於之後的操作
                        if($getOnly=="" || $getOnly=="全"){
                            $setOnFloor=1;
                            $setBdFloor = $getOnly=="全" ? $setTotalFloor : 1 ;
                        }//僅有一個項目時，若是為「全」，則代表課程商品為整棟樓，起始樓層為第一層。
                        else{//不是「全」以及「夾層」時
                            if(strpos($getOnly, '層') !== false && $getOnly!=="夾層"){//只要包含「層」時，皆是屬於主要建物中的「樓層」。
                                $getFnum = str_replace(array('層','地下'),'',$getOnly);//篩出純數字。
                                $getFnum = _fn_num_b2h($getFnum);//將篩出的數字由國字大寫轉換為阿拉伯數字。
                                if(strpos($getOnly, '地下') !== false){//區分地下層以及地上層，分別寫入所屬的欄位。
                                    $setBasement = 1;
                                    $setOnBasement = empty($getFnum)?1:$getFnum;//若是地下層時，課程商品所在樓層為陣列值
                                    //$setOnFloor = $getFnum;//若是地下層時，課程商品所在樓層為1
                                } else {
                                    $setBdFloor = 1;
                                    $setOnFloor = empty($getFnum)?1:$getFnum;//若是地上層時，則篩出的數字為課程商品所在樓層
                                }
                            } else {//沒有包含「層」時，代表此項目為附屬建物，將之列入附屬建物陣列中
                                $setOutBD = '["'.$getOnly.'"]';
                            }
                        } 
                        //else { $setOutBD[] = "夾層"; }//夾層也包含「層」，所以夾層直接列為附屬建物陣列中。
                    } elseif($getFScount>1){//分子值多於一個項目時//平台、陽台、騎樓、地下N層、屋頂突出物、夾層、防空避難室、停車場、電梯樓梯間、走廊、通道、儲藏室、見其他登記事項
                        foreach($getFloorSplit as $gOB){//將分子值陣列迴圈處理
                            if(strpos($gOB, '層') !== false && $gOB!=='夾層'){//當迴圈值中包含「層」且非「夾層」時，則視為主要建物
                                $getFloorSplit = array_diff($getFloorSplit, [$gOB] );//將這個值自原始陣列中移除
                                $setInBD[] = $gOB;//移除後同時寫入主要建物陣列中
                            }
                        }
                        if($getFloorSplit==null){ $setOutBD = ''; }
                        else {
                            $setOutBD = '';
                            foreach($getFloorSplit as $gOB2){
                                $setOutBD .= '"'.$gOB2.'",';
                            }
                            $setOutBD = '['.substr($setOutBD,0,-1).']';
                            //$setOutBD = json_encode(array_values($getFloorSplit));//移除所有主要建物值後的原始陣列，內部值僅剩下附屬建物，此即為附屬建物的主要內容（要重新排列鍵值）
                        }
                        if(count($setInBD)>0){//當主要建物陣列數量大於0時
                            foreach($setInBD as $gIB){//開始迴圈分出陣列值
                                $getFnum2 = str_replace(array('層','地下'),'',$gIB);//移除字串中的「層」以及「地下」兩個國字字串，剩餘的字串便是數字
                                $getFnum2 = _fn_num_b2h($getFnum2);//將剩餘的數字由國字轉換為阿拉伯數字
                                if(strpos($gIB, '地下') !== false){//區分地上以及地下層
                                    $underBD[] = empty($getFnum2)?1:$getFnum2;//寫入地下層陣列中
                                } else {
                                    $aboveBD[] = empty($getFnum2)?1:$getFnum2;//寫入地上層陣列中
                                }
                            }
                            //地下樓層處理
                            $underBD_num = count($underBD);//地上下建物的層數陣列長度
                            if($underBD_num==0){ //當沒有地下建物時，地下建物層數以及地下起始層數為0
                                $setBasement = 0;
                                $setOnBasement = 0; 
                            }
                            elseif($underBD_num==1){ //只有一層地下建物時，地下建物層數為1，地下起始層數為登錄的層數(例如：地下二層/八層=>則為2)
                                $setBasement = 1; 
                                $setOnBasement = $underBD[0]; 
                            }
                            else{//地下建物超過一層時
                                array_values($underBD);//重新以值排序
                                $setOnBasement = $underBD[0];//最小的值為課程商品所在(起始)地下樓層
                                $setBasement = $underBD[$underBD_num-1] - $setOnBasement + 1;//單戶地下樓層數為【最大陣列值】-【最小陣列值】+ 1
                            }
                            //地上樓層處理
                            $aboveBD_num = count($aboveBD);//地上建物的層數陣列長度
                            if($aboveBD_num==0){//當沒有地上建物時，地上建物層數以及地上起始層數為0
                                $setOnFloor = 0;
                                $setBdFloor = 0;
                            }
                            elseif($aboveBD_num==1){//只有一層地上建物時，地上建物層數為1，地上起始層數為登錄的層數(例如：五層/八層=>則為5)
                                $setOnFloor = $aboveBD[0];
                                $setBdFloor = 1;
                            }
                            else{
                                array_values($aboveBD);//重新以值排序
                                $setOnFloor = $aboveBD[0];//最小的值為課程商品所在(起始)樓層
                                $setBdFloor = $aboveBD[$aboveBD_num-1] - $setOnFloor + 1;//單戶樓層數為【最大陣列值】-【最小陣列值】+ 1
                            }
                        } else {//當沒有任何樓層紀錄時，無論地上地下建物層數以及樓層數都是0
                            $setBasement = 0;
                            $setOnBasement = 0; 
                            $setOnFloor = 0;
                            $setBdFloor = 0;
                        }
                    } else {
                        $setOnFloor = 1;
                        $setBdFloor = 1;
                    }
                }
                $getLayerplan = $SHEET->getCellByColumnAndRow(9, $rowNum)->getValue();
                $objSHEET->setCellValueByColumnAndRow(18, $insertRow, $getLayerplan);//樓別/樓高原始紀錄
                $toDBsql .= "'".$getLayerplan."',";
                $objSHEET->setCellValueByColumnAndRow(19, $insertRow, $setBasement);//寫入【M】樓別/樓高 【提取為地下層數】
                $toDBsql .= "'".(int)str_replace([',','-',' '],'',$setBasement)."',";
                $objSHEET->setCellValueByColumnAndRow(20, $insertRow, $setOnBasement);//寫入【M】樓別/樓高 【提取為地下起始層數】
                $toDBsql .= "'".(int)str_replace([',','-',' '],'',$setOnBasement)."',";
                $objSHEET->setCellValueByColumnAndRow(21, $insertRow, $setBdFloor);//寫入【N】樓別/樓高 【提取為單戶層數】
                $toDBsql .= "'".(int)str_replace([',','-',' '],'',$setBdFloor)."',";
                $objSHEET->setCellValueByColumnAndRow(22, $insertRow, $setOnFloor);//寫入【O】樓別/樓高 【提取為樓別(自第N層)】(此值可視為課程商品所在樓層)
                $toDBsql .= "'".(int)str_replace([',','-',' '],'',$setOnFloor)."',";
                $objSHEET->setCellValueByColumnAndRow(23, $insertRow, $setTotalFloor);//寫入【P】 樓別/樓高 【提取為樓高】
                $toDBsql .= "'".(int)str_replace([',','-',' '],'',$setTotalFloor)."',";
                $objSHEET->setCellValueByColumnAndRow(24, $insertRow, $setOutBD);//寫入【Q】 樓別/樓高 【提取為課程商品附屬建物】
                $toDBsql .= "'".$setOutBD."',";
                //----- 交易標的 -----------------------------------------------------------
                switch($SHEET->getCellByColumnAndRow(10, $rowNum)->getValue()){
                    case '': $getTarget=0; break;
                    case '房地(土地+建物)': $getTarget=1; break;
                    case '房地(土地+建物)+車位': $getTarget=2; break;
                    case '土地': $getTarget=3; break;
                    case '建物': $getTarget=4; break;
                    case '車位': $getTarget=5; break;
                    default:$getTarget='0'; break;
                }
                $objSHEET->setCellValueByColumnAndRow(25, $insertRow, $getTarget);//寫入【Q】交易標的 【分析後以數字代替】  1-房地(土地+建物);2-房地(土地+建物)+車位;3-土地;4-建物;5-車位;
                $toDBsql .= "'".$getTarget."',";
                //---- 交易筆數 -------------------------------------------------------------
                $getTradefor = $SHEET->getCellByColumnAndRow(11, $rowNum)->getValue();
                $objSHEET->setCellValueByColumnAndRow(26, $insertRow, $getTradefor);//交易筆棟數
                $toDBsql .= "'".$getTradefor."',";
                $getPureTarget=str_replace(array("土","建","車"," "),"",$SHEET->getCellByColumnAndRow(11, $rowNum)->getValue());
                $getSplitTarget=@explode(":",$getPureTarget);
                $objSHEET->setCellValueByColumnAndRow(27, $insertRow, @(int)$getSplitTarget[1]);//寫入【R】交易筆數【提取為土地筆數】
                $toDBsql .= "'".@(int)$getSplitTarget[1]."',";
                $objSHEET->setCellValueByColumnAndRow(28, $insertRow, @(int)$getSplitTarget[2]);//寫入【S】【提取為建物筆數】
                $toDBsql .= "'".@(int)$getSplitTarget[2]."',";
                $objSHEET->setCellValueByColumnAndRow(29, $insertRow, @(int)$getSplitTarget[3]);//寫入【T】交易筆數【提取為車位筆數】
                $toDBsql .= "'".@(int)$getSplitTarget[3]."',";//
                //---------------------------------------------------------------------------
                $getPPrice = str_replace(',','',$SHEET->getCellByColumnAndRow(13, $rowNum)->getValue());
                $objSHEET->setCellValueByColumnAndRow(30, $insertRow, (float)$getPPrice);//寫入【U】車位總價
                $toDBsql .= "'".(float)$getPPrice."',";
                //----建物現況格局-------------------------------------------------------------
                $getFloorplan = $SHEET->getCellByColumnAndRow(12, $rowNum)->getValue();
                $objSHEET->setCellValueByColumnAndRow(31, $insertRow, $getFloorplan);//建物現況格局
                $toDBsql .= "'".$getFloorplan."',";
                //$layoutO = $SHEET->getCellByColumnAndRow(12, $rowNum)->getValue();
                $split1 = preg_split('/[\s\d]+/',$getFloorplan);
                $split2 = str_replace($split1,':',$getFloorplan);
                $split2 = @explode(':',':'.$split2);
                $key1 = array_search("房",$split1);
                $key2 = array_search("廳",$split1);
                $key3 = array_search("衛",$split1);
                $getRoomNum = $key1?$split2[$key1]:0;
                $getHallNum = $key2?$split2[$key2]:0;
                $getBathNum = $key3?$split2[$key3]:0;
                $objSHEET->setCellValueByColumnAndRow(32, $insertRow, $getRoomNum);//寫入【V】建物現況格局【提取為房間數】
                $toDBsql .= "'".$getRoomNum."',";
                $objSHEET->setCellValueByColumnAndRow(33, $insertRow, $getHallNum);//寫入【W】建物現況格局【提取為廳數】
                $toDBsql .= "'".$getHallNum."',";
                $objSHEET->setCellValueByColumnAndRow(34, $insertRow, $getBathNum);//寫入【X】建物現況格局【提取為衛浴數】
                $toDBsql .= "'".$getBathNum."',";
                //----管理組織-----------------------------------------------------------------
                $getPMC = $SHEET->getCellByColumnAndRow(14, $rowNum)->getValue();
                $objSHEET->setCellValueByColumnAndRow(35, $insertRow, $getPMC);//管理組織【原始】
                $toDBsql .= "'".$getPMC."',";
                if($getPMC=='有'){ $getPMC='1'; }
                elseif($getPMC=='無'){ $getPMC='0'; }
                else{ $getPMC='9'; }
                $objSHEET->setCellValueByColumnAndRow(36, $insertRow, $getPMC);//寫入【Y】管理組織【轉換為布林值】
                $toDBsql .= "'".$getPMC."',";
                //----------------------------------------------------------------------------
                $getELV = $SHEET->getCellByColumnAndRow(15, $rowNum)->getValue();
                $objSHEET->setCellValueByColumnAndRow(37, $insertRow, $getELV);//電梯【原始】
                $toDBsql .= "'".$getELV."',";
                if($getELV=='有'){ $getELV='1'; }
                elseif($getELV=='無'){ $getELV='0'; }
                else{ $getELV='9'; }
                $objSHEET->setCellValueByColumnAndRow(38, $insertRow, $getELV);//寫入【Y】寫入【Y】電梯【轉換為布林值】
                $toDBsql .= "'".$getELV."',";
                //----------------------------------------------------------------------------
                $getBCUsefor = $SHEET->getCellByColumnAndRow(16, $rowNum)->getValue();
                $objSHEET->setCellValueByColumnAndRow(39, $insertRow, $getBCUsefor);//Q 主要用途【分析後以數字代替】
                $toDBsql .= "'".$getBCUsefor."',";
                switch($getBCUsefor){
                    case '': $getUsefor=0; break;
                    case '其他': $getUsefor=0; break;
                    case '住家用': $getUsefor=1; break;
                    case '商業用': $getUsefor=2; break;
                    case '工業用': $getUsefor=3; break;
                    case '農業用': $getUsefor=4; break;
                    case '辦公用': $getUsefor=5; break;
                    case '住商用': $getUsefor=6; break;
                    case '住工用': $getUsefor=7; break;
                    case '住辦用': $getUsefor=8; break;
                    case '工商用': $getUsefor=9; break;
                    case '商辦用': $getUsefor=10; break;
                    case '住商辦用': $getUsefor=11; break;
                    case '工商辦用': $getUsefor=12; break;
                    case '見使用執照': $getUsefor=13; break;
                    case '見其他登記事項': $getUsefor=20; break;
                    default: $getUsefor='99'; break;
                }
                $objSHEET->setCellValueByColumnAndRow(40, $insertRow, $getUsefor);//Q z【分析後以數字代替】
                $toDBsql .= "'".$getUsefor."',";
                $getRemark = $SHEET->getCellByColumnAndRow(17, $rowNum)->getValue();
                $objSHEET->setCellValueByColumnAndRow(41, $insertRow, $getRemark);//R 備註
                $toDBsql .= "'".addslashes($getRemark)."',";
                $objSHEET->setCellValueByColumnAndRow(42, $insertRow, '');//預留備註ID陣列組
                $toDBsql .= "'',";//預留備註ID陣列組
                $objSHEET->setCellValueByColumnAndRow(43, $insertRow, '0');//狀態//
                $toDBsql .= "'0',";
                $objSHEET->setCellValueByColumnAndRow(44, $insertRow, '0');//操作註記
                $toDBsql .= "'0')";

                $doBCsql = "INSERT INTO `data_aprret` VALUES ".$toDBsql;
                $ins = mysqli_query($link, $doBCsql);
                $insID = mysqli_insert_id($link);
                if($ins && $insID>0){
                    $cTotalInsRows++;
                }else{
                    echo $toDBsql."<br>";
                }

                $insertRow++;
                $cTotalDupRows++;
            }//for
        }//while
        //////$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'CSV');   //設定寫入excel的類型
        //$objWriter->setUseBOM(true);//中文亂碼處理
        //$objWriter->save($csv_toPath.$folderr.'_'.$fromID.'-'.$toID.'_'.$cTotalDupRows.'.csv');//保存文件
        //////釋放資源
        //$EXL->disconnectWorksheets();
        //釋放課程商品
        //unset($EXL);
    }//oktobox
    $timeUse = time() - $startTime;
    echo date('Y-m-d H:s:i').' Done!';
    echo "<hr><br>".$folderr." 年資料庫ID: ".$fromID."~".$toID." 共轉移資料筆數: ".$cTotalDupRows." (".$cTotalInsRows.") / ".$cTotalRowNum." 筆<hr>";
    
    $osql = "SELECT SUM(f_rows - f_startrow) AS CC FROM filelist";
    $osql .= " WHERE f_year='".$folderr."'";
    $osql .= " AND f_id BETWEEN ".$fromID." AND ".$toID;
    $oresult = mysqli_query($link, $osql);
    $orow = mysqli_fetch_array($oresult);

    echo $folderr." 年資料庫ID: ".$fromID."~".$toID." 原始紀錄數： ".$orow['CC'].'<hr>';
    echo "<br>花費時間: ".$timeUse." 秒<br><br><br><br>";
    mysqli_close($link);
    if(!empty($donealert)) { echo "<audio autoplay='true' style='display:none;'><source src='alert.wav'></audio>"; }
    ?>
    <br><br>
    </div>
    <script>
        window.parent.Clock.resetTimer();
        window.parent.document.getElementById('loading').style.display = 'none';
    </script>
</body>
</html>

