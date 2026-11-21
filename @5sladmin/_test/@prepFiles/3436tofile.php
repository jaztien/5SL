<?php
    error_reporting(E_ALL); 
	include 'aprret_func.php';
	include 'PHPExcel/Classes/PHPExcel.php';
    header("Content-Type:text/html; charset=utf-8");
    $year = isset($_POST['year']) ? $_POST['year'] : '';
 /*   
    $host = 'localhost';
    $dbuser ='root';
    $dbpassword = '1111';
    $dbname = 'jazhome';

    //echo _fn_num_b2h("二十五");exit();
    $link = mysqli_connect($host,$dbuser,$dbpassword,$dbname);
    if($link){
        mysqli_query($link,'SET NAMES utf8');
        echo "正確連接資料庫";
    }
    else {
        echo "不正確連接資料庫</br>" . mysqli_connect_error();
    }

*/
    $startTime = time();

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
  </head>
<body>

<hr>
<div style="font-size:16px;line-height:20px;">
<form id="form_readtofile" name="form_readtofile" action="#" method="post">
    年份: <input type="text" id="year" name="year" style="width:100px;" value="<?php echo $year; ?>">&nbsp;&nbsp;&nbsp;&nbsp;
    <button type="submit">SEND</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <button type="button" onClick="window.location.href ='http://localhost:8888/3436tofile.php';">RESET</button>
    <button type="button" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
</form>
<hr>
<?php
if(empty($year)){ echo "尚未設定年份！";exit(); }
$csv_folder = $year;
$csv_folderpath = "aprretCSV/".$csv_folder."/";
$csv_filename = "34_3436_".$csv_folder.".01-".$csv_folder.".12.csv";
$objPHPExcel = new PHPExcel();                     //實例化一個PHPExcel()對象
$objSHEET = $objPHPExcel->getActiveSheet();        //選取當前的sheet對象
    
    $objSHEET->setCellValueByColumnAndRow(0, 1,'城市碼')
        ->setCellValueByColumnAndRow(1, 1,'地區碼')
        ->setCellValueByColumnAndRow(2, 1,'地址')
        ->setCellValueByColumnAndRow(3, 1,'社區簡稱')
        ->setCellValueByColumnAndRow(4, 1,'社區ID')
        ->setCellValueByColumnAndRow(5, 1,'交易日期')
        ->setCellValueByColumnAndRow(6, 1,'總價')
        ->setCellValueByColumnAndRow(7, 1,'單價')
        ->setCellValueByColumnAndRow(8, 1,'總面積')
        ->setCellValueByColumnAndRow(9, 1,'主建物佔比')
        ->setCellValueByColumnAndRow(10, 1,'型態')
        ->setCellValueByColumnAndRow(11, 1,'型態ID')
        ->setCellValueByColumnAndRow(12, 1,'屋齡')
        ->setCellValueByColumnAndRow(13, 1,'交屋年份')
        ->setCellValueByColumnAndRow(14, 1,'樓別/樓高')
        ->setCellValueByColumnAndRow(15, 1,'地下層數')
        ->setCellValueByColumnAndRow(16, 1,'地下起始層')
        ->setCellValueByColumnAndRow(17, 1,'單戶層數')
        ->setCellValueByColumnAndRow(18, 1,'所在樓層')
        ->setCellValueByColumnAndRow(19, 1,'樓高')
        ->setCellValueByColumnAndRow(20, 1,'附屬建物')
        ->setCellValueByColumnAndRow(21, 1,'交易標的')
        ->setCellValueByColumnAndRow(22, 1,'交易筆棟數')
        ->setCellValueByColumnAndRow(23, 1,'土地筆數')
        ->setCellValueByColumnAndRow(24, 1,'建物筆數')
        ->setCellValueByColumnAndRow(25, 1,'車位筆數')
        ->setCellValueByColumnAndRow(26, 1,'車位總價')
        ->setCellValueByColumnAndRow(27, 1,'建物格局')
        ->setCellValueByColumnAndRow(28, 1,'房間數')
        ->setCellValueByColumnAndRow(29, 1,'廳數')
        ->setCellValueByColumnAndRow(30, 1,'衛浴數')
        ->setCellValueByColumnAndRow(31, 1,'管理組織')
        ->setCellValueByColumnAndRow(32, 1,'電梯')
        ->setCellValueByColumnAndRow(33, 1,'主要用途')
        ->setCellValueByColumnAndRow(34, 1,'用途ID')
        ->setCellValueByColumnAndRow(35, 1,'備註');

    $chkSameFile = '';
    $chkSameOpt=0;
    $chkDoneOpt=0;
//$cTotalRowNum=0;
    $cTotalDupRows=0;
    //$getBR = "";
    $insertRow=2;//跳過第一列抬頭列
    //while($flist = mysqli_fetch_assoc($result)){
        //-------- 取得基本參數 -------------------------------
        //$csv_subfolder = $csv_folderpath.$csv_filenam;//取得檔案路徑
        //--------- 載入檔案 ----------------------------------
        $EXL= PHPExcel_IOFactory::load($csv_folderpath.$csv_filename); 
        $SHEET=$EXL->getSheet(0);
        $highestRow = $SHEET->getHighestDataRow();           //總列數
        $startRow = 4;       //起始列數
        $totalRows = $highestRow - $startRow;   //實機有效列數
//echo $highestRow;exit();

        //$cTotalRowNum += ($flist['f_rows']-$flist['f_startrow']);            //累積每個檔案的有效列數總和
        //$getCity = $flist['f_city'];              //取得城市代碼
        //$getDist = $flist['f_district'];          //取得區域代碼
        
        //--------- 自原始檔案提取程式碼以及區域碼 ----------------------------------
        $nameSplit=@explode("_",$csv_filename);
        $getCity = $nameSplit[0];//城市代碼 00
        $getDist = $nameSplit[1];//區域代碼 0000
        //$highestRow = $SHEET->getHighestRow(); // 取得總列數
        //-------------------- 開始每個檔案中每筆紀錄一一表列 -----------------------
        for ($rowNum = $startRow; $rowNum <= $highestRow; $rowNum++) {
            $objSHEET->setCellValueByColumnAndRow(0, $insertRow, $getCity);//寫入【A】城市代碼
            $objSHEET->setCellValueByColumnAndRow(1, $insertRow, $getDist);//寫入【B】區域代碼
            //----- 地段位置或門牌 ---------------------------------------------------------
            $getFormatAdd = _fn_get_fulladdress($getCity,$getDist,$SHEET->getCellByColumnAndRow(0, $rowNum)->getValue());//讀取原始檔案A欄位住址，檢查並重新格式
            $objSHEET->setCellValueByColumnAndRow(2, $insertRow, $getFormatAdd['addonly']);//寫入【C】地段位置或住址門牌
            //------ 社區簡稱處理 ----------------------------------------------------------
            $getCommOrig = $SHEET->getCellByColumnAndRow(1, $rowNum)->getValue();//讀取原始檔案B欄位社區簡稱
            $objSHEET->setCellValueByColumnAndRow(3, $insertRow, $SHEET->getCellByColumnAndRow(1, $rowNum)->getValue());//寫入【D】社區簡稱
            $objSHEET->setCellValueByColumnAndRow(4, $insertRow, '');//寫入【D】社區ID
            //------ 日期處理 -------------------------------------------------------------
            $getDateOrig =  $SHEET->getCellByColumnAndRow(2, $rowNum)->getValue();//讀取原始檔案C欄位交易年月
            if(!empty($getDateOrig)){
                $nDate = @explode("/",$getDateOrig);
                $nDate = ($nDate[0]+1911)."-".$nDate[1]."-".$nDate[2];
                $nDate = strtotime($nDate);
            }
            $objSHEET->setCellValueByColumnAndRow(5, $insertRow, $nDate);//寫入【E】交易日期時間戳
            //------ 直接移轉 -------------------------------------------------------------
            $objSHEET->setCellValueByColumnAndRow(6, $insertRow, $SHEET->getCellByColumnAndRow(3, $rowNum)->getValue());//讀取原始檔案D欄位「總價(萬元)」直接寫入【F】總價(萬元)
            $objSHEET->setCellValueByColumnAndRow(7, $insertRow, $SHEET->getCellByColumnAndRow(4, $rowNum)->getValue());//讀取原始檔案E欄位「單價」直接寫入【G】單價
            $objSHEET->setCellValueByColumnAndRow(8, $insertRow, $SHEET->getCellByColumnAndRow(5, $rowNum)->getValue());//讀取原始檔案F欄位「總面積」直接寫入【H】「總面積」
            //------ 主建物佔比處理 -------------------------------------------------------------
            $getPercentage = $SHEET->getCellByColumnAndRow(6, $rowNum)->getValue();
            $getPercentage = str_replace('%','',$getPercentage);
            $objSHEET->setCellValueByColumnAndRow(9, $insertRow, $getPercentage);//寫入【I】主建物佔比【移除%】
            //------ 課程商品型態(轉換為數值)---------------------------------------------------------
            $objSHEET->setCellValueByColumnAndRow(10, $insertRow, $SHEET->getCellByColumnAndRow(7, $rowNum)->getValue());//寫入【J】型態【分析後以數字代替】 1-公寓(5樓含以下無電梯);2-透天厝;3-店面(店鋪);4-辦公商業大樓;5-住宅大樓(11層含以上有電梯);6-華廈(10層含以下有電梯);7-套房(1房1廳1衛);8-工廠;9-廠辦;10-農舍;11-倉庫;
            switch($SHEET->getCellByColumnAndRow(7, $rowNum)->getValue()){
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
            $objSHEET->setCellValueByColumnAndRow(11, $insertRow, $getType);//寫入【J】型態【分析後以數字代替】 1-公寓(5樓含以下無電梯);2-透天厝;3-店面(店鋪);4-辦公商業大樓;5-住宅大樓(11層含以上有電梯);6-華廈(10層含以下有電梯);7-套房(1房1廳1衛);8-工廠;9-廠辦;10-農舍;11-倉庫;
            //------ 建物完工日期(轉換為日期)---------------------------------------------------------
            $objSHEET->setCellValueByColumnAndRow(12, $insertRow, $SHEET->getCellByColumnAndRow(8, $rowNum)->getValue());//讀取原始檔案I欄位「屋齡」直接寫入【K】屋齡【計算建造日期】
            $objSHEET->setCellValueByColumnAndRow(13, $insertRow, '');//留著為用屋齡計算出的課程商品完工年份【L】
            //------ 樓別/樓高 ----------------------------------------------------------
            $getFloorStr = trim($SHEET->getCellByColumnAndRow(9, $rowNum)->getValue());//讀取原始檔案J欄位「樓別/樓高」
            $setBasement = '';//單戶地下層數
            $setBdFloor = '';//單戶地上層數
            $setOnFloor = '';//地上層數所在(起始)層數
            $setOnBasement = '';//地下層數所在(起始)層數
            $setTotalFloor = '';//樓高
            $setOutBD = '';
            if(empty($getFloorStr)){
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
                $setInBD = array();
                $setOutBD = null;
                $aboveBD = array();
                $underBD = array();
                //$getFloor=strchr($getFloorStr,"/",true);
                $getFloor = @explode('/',$getFloorStr);
                $getFloorSplit=@explode(',',$getFloor[0]);
                $setTotalFloor = str_replace(array('層','地下'),'',$getFloor[1]);
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
            
            $objSHEET->setCellValueByColumnAndRow(14, $insertRow, $SHEET->getCellByColumnAndRow(9, $rowNum)->getValue());//樓別/樓高原始紀錄
            $objSHEET->setCellValueByColumnAndRow(15, $insertRow, $setBasement);//寫入【M】樓別/樓高 【提取為地下層數】
            $objSHEET->setCellValueByColumnAndRow(16, $insertRow, $setOnBasement);//寫入【M】樓別/樓高 【提取為地下起始層數】
            $objSHEET->setCellValueByColumnAndRow(17, $insertRow, $setBdFloor);//寫入【N】樓別/樓高 【提取為單戶層數】
            $objSHEET->setCellValueByColumnAndRow(18, $insertRow, $setOnFloor);//寫入【O】樓別/樓高 【提取為樓別(自第N層)】(此值可視為課程商品所在樓層)
            $objSHEET->setCellValueByColumnAndRow(19, $insertRow, $setTotalFloor);//寫入【P】 樓別/樓高 【提取為樓高】
            $objSHEET->setCellValueByColumnAndRow(20, $insertRow, $setOutBD);//寫入【Q】 樓別/樓高 【提取為課程商品附屬建物】
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
            $objSHEET->setCellValueByColumnAndRow(21, $insertRow, $getTarget);//寫入【Q】交易標的 【分析後以數字代替】  1-房地(土地+建物);2-房地(土地+建物)+車位;3-土地;4-建物;5-車位;
            //---- 交易筆數 -------------------------------------------------------------
            $objSHEET->setCellValueByColumnAndRow(22, $insertRow, $SHEET->getCellByColumnAndRow(11, $rowNum)->getValue());//交易筆棟數
            $getPureTarget=str_replace(array("土","建","車"," "),"",$SHEET->getCellByColumnAndRow(11, $rowNum)->getValue());
            $getSplitTarget=@explode(":",$getPureTarget);
            $objSHEET->setCellValueByColumnAndRow(23, $insertRow, $getSplitTarget[1]);//寫入【R】交易筆數【提取為土地筆數】
            $objSHEET->setCellValueByColumnAndRow(24, $insertRow, $getSplitTarget[2]);//寫入【S】【提取為建物筆數】
            $objSHEET->setCellValueByColumnAndRow(25, $insertRow, $getSplitTarget[3]);//寫入【T】交易筆數【提取為車位筆數】
            //---------------------------------------------------------------------------
            $objSHEET->setCellValueByColumnAndRow(26, $insertRow, $SHEET->getCellByColumnAndRow(13, $rowNum)->getValue());//寫入【U】車位總價
            //----建物現況格局-------------------------------------------------------------
            $objSHEET->setCellValueByColumnAndRow(27, $insertRow, $SHEET->getCellByColumnAndRow(12, $rowNum)->getValue());//建物現況格局
            $layoutO = $SHEET->getCellByColumnAndRow(12, $rowNum)->getValue();
            $split1 = preg_split('/[\s\d]+/',$layoutO);
            $split2 = str_replace($split1,':',$layoutO);
            $split2 = @explode(':',':'.$split2);
            $key1 = array_search("房",$split1);
            $key2 = array_search("廳",$split1);
            $key3 = array_search("衛",$split1);
            $objSHEET->setCellValueByColumnAndRow(28, $insertRow, $key1?$split2[$key1]:0);//寫入【V】建物現況格局【提取為房間數】
            $objSHEET->setCellValueByColumnAndRow(29, $insertRow, $key2?$split2[$key2]:0);//寫入【W】建物現況格局【提取為廳數】
            $objSHEET->setCellValueByColumnAndRow(30, $insertRow, $key3?$split2[$key3]:0);//寫入【X】建物現況格局【提取為衛浴數】
            //----管理組織-----------------------------------------------------------------
            $getPMC = $SHEET->getCellByColumnAndRow(14, $rowNum)->getValue();
            if($getPMC=='有'){ $getPMC='1'; }
            else{ $getPMC='0'; }
            $objSHEET->setCellValueByColumnAndRow(31, $insertRow, $getPMC);//寫入【Y】管理組織【轉換為布林值】
            //----------------------------------------------------------------------------
            $getELV = $SHEET->getCellByColumnAndRow(15, $rowNum)->getValue();
            if($getELV=='有'){ $getELV='1'; }
            else{ $getELV='0'; }
            $objSHEET->setCellValueByColumnAndRow(32, $insertRow, $getELV);//寫入【Y】寫入【Y】電梯【轉換為布林值】
            //----------------------------------------------------------------------------          
            $objSHEET->setCellValueByColumnAndRow(33, $insertRow, $SHEET->getCellByColumnAndRow(16, $rowNum)->getValue());//Q 主要用途【分析後以數字代替】
            /*
            switch($SHEET->getCellByColumnAndRow(16, $rowNum)->getValue()){
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
                case '見其他登記事項': $getUsefor=20; break;
                default: $getUsefor='0'; break;
            }
            */
            $objSHEET->setCellValueByColumnAndRow(34, $insertRow, '');//Q z【分析後以數字代替】
            $objSHEET->setCellValueByColumnAndRow(35, $insertRow, $SHEET->getCellByColumnAndRow(17, $rowNum)->getValue());//R 備註
            $insertRow++;
            $cTotalDupRows++;
        }
        //$cFolderName = $flist['f_year'];
//}
    //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'CSV');   //設定寫入excel的類型
    $objWriter->setUseBOM(true);//中文亂碼處理
    $objWriter->save($csv_folder.'_'.$cTotalDupRows.'_'.date('Y-m-d').'.csv');//保存文件
    //$objWriter->save($cFolderName.'_'.$fromID.'-'.$toID.'_'.date('Y-m-d').'_'.$cTotalDupRows.'.xlsx');//保存文件
   

    //釋放資源
    $EXL->disconnectWorksheets();
    //釋放課程商品
    unset($EXL);

    $timeUse = time() - $startTime;
    echo date('Y-m-d H:s:i').' Done!';

    echo "<hr><br>資料夾: ".$csv_folder." 共產生資料筆數: ".$cTotalDupRows." / ".($totalRows+1)." 筆<br>";
    echo "<hr><br>花費時間: ".$timeUse." 秒<br><br><br><br>";
    //echo "<hr><br>Opt Doned: ".$chkDoneOpt."<br><br><br><br>";
    
    //mysqli_close($link);
?>
</div>
</body>
</html>

