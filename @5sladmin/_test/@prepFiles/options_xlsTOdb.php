<?php
//////////////////////////////////////////////////////////////////////////////
///////////////////////// 未分區紀錄寫入工作資料表 ///////////////////////////////
//////////////////////////////////////////////////////////////////////////////
error_reporting(E_ALL); 
	include 'aprret_func.php';
	include 'PHPExcel/Classes/PHPExcel/IOFactory.php';
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
    $doact = isset($_POST['doact']) ? $_POST['doact'] : '';
    //$toID = isset($_POST['toID']) ? $_POST['toID'] : '';

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

<div style="font-size:16px;line-height:20px;">
<form id="form_readtofile" name="form_readtofile" action="#" method="post">
    <input type="hidden" id="doact" name="doact" value="1">
    <button type="submit" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">執行</button>
    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/options_xlsTOdb.php';">RESET</button>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
</form>
<hr>

<?php

echo _fn_twd97_to_wgs84(302141 ,2778620);
exit();
	

    $checkLoading = 1;
    if(empty($doact)){ echo " /請按「執行」按鈕進行操作！！"; $checkLoading = 0; }
    if(empty($checkLoading)){
        //echo "<script>window.parent.document.getElementById('loading').style.display = 'none';</script>";
        exit();
    }

    $schJS=_fn_getCachedJsonDatac("aprret_schema.json");
    $basefolder = "aprretFiles/aprretWithGeoData_105.01-112.04/";
    
    //$cityCode = "17";
    //$subfolder = $cityCode."/1701";
    //$cityname = "新竹市";
    //$codeArr = array("東區"=>"1701","北區"=>"1702","香山區"=>"1703");
    //$replace = "新竹市";
    /*
    $cityCode = "32";
    $subfolder = $cityCode."/3201";
    $cityname = "嘉義市";
    $codeArr = array("東區"=>"3201","西區"=>"3202");
    $replace=['嘉義市','嘉義巿'];
   */
//１路２路３路４路５路６路７路８路９路１０路。一樓 二路 三路 四路 五路 六路 七路 八路 九路 十路 
///////////////////////////////////////////////////////////////////////
        $cTotalFiles=0;
        $cTotalRowNum=0;
        $cTotalDupRows=0;
        $cTotaXLSRows=0;
        $cErrorCount=0;
        $source_folderpath=$basefolder;
        if (is_dir($source_folderpath)) {
            $cfile = opendir($source_folderpath); {
                if ($cfile) {
                    $dirFiles = array();
                    // opens images folder
                    if ($handle = opendir($source_folderpath)) {
                        while (false !== ($files = readdir($handle))) {
                            if ($files != '.' && $files != '..' && $files != '.DS_Store') {
                                $dirFiles[] = $files;
                            }
                        }
                        closedir($handle);
                    }
                    sort($dirFiles);

                    $cTotalFiles=0;
                    $i=1;
                    foreach($dirFiles as $filename){
                        //while (($filename = readdir($cfile)) !== FALSE) {
                        if($i==2){ break; }
                        if ($filename != '.' && $filename != '..' && $filename != '.DS_Store' && $filename != 'Icon?') {
                            //echo $filename."<br>";
                            //$nameSplit = preg_split('/\_|\./',$filename);
                            
                            $extSplit = explode('.',$filename);
                            $nameSplit = explode('_',$extSplit[0]);
                            $ext = $extSplit[1];
                            $check = $nameSplit[0];
                            $city = substr($nameSplit[1],0,1);
                            $from = substr($nameSplit[1],1);
                            $to = $nameSplit[2];
                            $letter = isset($nameSplit[3]) ? $nameSplit[3] : '';
                            
                            if($check!='list' || $city!='a' || $letter!='a'){
                                continue;
                            }  
                        
                            //echo $source_folderpath.$filename."<br>";
                            //if($i==1){
                             //   break;
                            //}  
                            $citytext = $schJS[$city]['title'];
                           
                            $EXL= PHPExcel_IOFactory::load($source_folderpath.$filename); 
                            $SHEET=$EXL->getSheet(0);
                            $startRow = 2;
                            //$endRow = $SHEET->getHighestRow(); // 取得總列數 
                            $endRow = 11; // 取得總列數 
                            //$xlsRows = ($endRow - $startRow) + 1;
                            //$cTotaXLSRows += $xlsRows;
                            $fi=0;  
                            //echo $citytext."<br>";

                            for ($rowNum = $startRow; $rowNum <= $endRow; $rowNum++) {
                                $colA = $SHEET->getCellByColumnAndRow(0, $rowNum)->getValue();//行政區劃
                                $colC = $SHEET->getCellByColumnAndRow(2, $rowNum)->getValue();//住址
                                $colC = str_replace($citytext.$colA,'',$colC);
                                $colAA = $SHEET->getCellByColumnAndRow(26, $rowNum)->getValue();//X 交易標的橫坐標
                                $colAB = $SHEET->getCellByColumnAndRow(27, $rowNum)->getValue();//Y 交易標的縱坐標
                                $colAE = $SHEET->getCellByColumnAndRow(30, $rowNum)->getValue();//SN
                                echo $colAE.' @@ '._fn_twd97_to_wgs84($colAA,$colAB)."<br>";
                            }

                            $i++;
                            $cTotalFiles++;
                            
                        }

                    }
                }
            }
        }
    $timeUse = time() - $startTime;
    echo "<hr>";
    echo date('Y-m-d H:s:i').' Done!';
    echo "<hr>檔案紀錄筆數: ".$cTotalFiles." 筆";
    echo "<hr>共產生資料筆數: ".$cTotalDupRows." / ".$cTotalRowNum." 筆";
    echo "<hr>錯誤資料筆數: ".$cErrorCount." 筆";
    echo "<hr>花費時間: ".$timeUse." 秒<br><br><br><br>";
    //echo "<hr><br>Opt Doned: ".$chkDoneOpt."<br><br><br><br>";
    mysqli_close($link);
    ?>
    <audio autoplay='true' style='display:none;'><source src='alert.wav'></audio>
    <br><br><br><br>
    </div>
</body>
</html>

