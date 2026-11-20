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
    //$fromID = isset($_POST['fromID']) ? $_POST['fromID'] : '';
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

<?php
    /*
    $checkLoading = 1;
    if(empty($fromID) || empty($toID)){ echo "尚未設定起始結束ID段！";$checkLoading = 0; }
    if(empty($checkLoading)){
        echo "<script>window.parent.document.getElementById('loading').style.display = 'none';</script>";
        exit();
    }
   */ 
    //if($fromID==1){ $fromID=0; }

    //$folderr="101";
    //$folderr2="110";






    $basefolder = "aprretFiles/Original/1130326/";
    
    $subfolder = "17/1701";
    $cityname = "新竹市";
    $codeArr = array("東區"=>"1701","北區"=>"1702","香山區"=>"1703");
    /*
    $subfolder = "32/3201";
    $cityname = "嘉義市";
    $codeArr = array("東區"=>"3201","西區"=>"3202");
    */
    /*
    $objPHPExcel = new PHPExcel();                     //實例化一個PHPExcel()對象
    $objSHEET = $objPHPExcel->getActiveSheet();        //選取當前的sheet對象
    $objSHEET->setCellValueByColumnAndRow(0, 1,'')
        ->setCellValueByColumnAndRow(1, 1,'')
        ->setCellValueByColumnAndRow(2, 1,'')
        ->setCellValueByColumnAndRow(3, 1,'')
        ->setCellValueByColumnAndRow(4, 1,'')
        ->setCellValueByColumnAndRow(5, 1,'')
        ->setCellValueByColumnAndRow(6, 1,'')
        ->setCellValueByColumnAndRow(7, 1,'')
        ->setCellValueByColumnAndRow(8, 1,'') 
        ->setCellValueByColumnAndRow(9, 1,'')
        ->setCellValueByColumnAndRow(10, 1,'')  
        ->setCellValueByColumnAndRow(11, 1,'')
        ->setCellValueByColumnAndRow(12, 1,'')  
        ->setCellValueByColumnAndRow(13, 1,'') 
        ->setCellValueByColumnAndRow(14, 1,'')
        ->setCellValueByColumnAndRow(15, 1,'')
        ->setCellValueByColumnAndRow(16, 1,'')
        ->setCellValueByColumnAndRow(17, 1,'')
        ->setCellValueByColumnAndRow(0, 2,'')
        ->setCellValueByColumnAndRow(1, 2,'')
        ->setCellValueByColumnAndRow(2, 2,'')
        ->setCellValueByColumnAndRow(3, 2,'')
        ->setCellValueByColumnAndRow(4, 2,'單價')
        ->setCellValueByColumnAndRow(5, 2,'總面積')
        ->setCellValueByColumnAndRow(6, 2,'')
        ->setCellValueByColumnAndRow(7, 2,'')
        ->setCellValueByColumnAndRow(8, 2,'') 
        ->setCellValueByColumnAndRow(9, 2,'')
        ->setCellValueByColumnAndRow(10, 2,'')  
        ->setCellValueByColumnAndRow(11, 2,'')
        ->setCellValueByColumnAndRow(12, 2,'')  
        ->setCellValueByColumnAndRow(13, 2,'車位總價') 
        ->setCellValueByColumnAndRow(14, 2,'')
        ->setCellValueByColumnAndRow(15, 2,'')
        ->setCellValueByColumnAndRow(16, 2,'')
        ->setCellValueByColumnAndRow(17, 2,'')
        ->setCellValueByColumnAndRow(0, 3,'地段位置或門牌')
        ->setCellValueByColumnAndRow(1, 3,'社區簡稱')
        ->setCellValueByColumnAndRow(2, 3,'交易日期')
        ->setCellValueByColumnAndRow(3, 3,'總價(萬元)')
        ->setCellValueByColumnAndRow(4, 3,'(萬元/坪)')
        ->setCellValueByColumnAndRow(5, 3,'(坪)')
        ->setCellValueByColumnAndRow(6, 3,'主建物佔比')
        ->setCellValueByColumnAndRow(7, 3,'型態')
        ->setCellValueByColumnAndRow(8, 3,'屋齡') 
        ->setCellValueByColumnAndRow(9, 3,'樓別/樓高')
        ->setCellValueByColumnAndRow(10, 3,'交易標的')  
        ->setCellValueByColumnAndRow(11, 3,'交易筆棟數')
        ->setCellValueByColumnAndRow(12, 3,'建物現況格局')  
        ->setCellValueByColumnAndRow(13, 3,'(萬元)') 
        ->setCellValueByColumnAndRow(14, 3,'管理組織')
        ->setCellValueByColumnAndRow(15, 3,'電梯')
        ->setCellValueByColumnAndRow(16, 3,'主要用途')
        ->setCellValueByColumnAndRow(17, 3,'備註');
        */
///////////////////////////////////////////////////////////////////////



        // 取得資料 (欄位名稱 and 欄位值)
        $sql = "SELECT * FROM data_distsplit_17";
        $sql .= " WHERE dt_year='101' AND NOT dt_K = '土地'";
        //$sql .= " WHERE f_id > ".$fromID." AND f_id <=".$toID;
        //$sql .= " WHERE f_id =1";
        //$sql .= " WHERE f_year IN ('".$folderr."','".$folderr2."')";
        $sql .= " LIMIT 1,1";
        $result = mysqli_query($link, $sql);
        mysqli_data_seek($result, 0);  // 指標移到第一筆資料
        while($flist = mysqli_fetch_assoc($result)){
            $addrName = strchr($flist['dt_A'],'號',true).'號';
            $addrUrl = 'https://zip5.5432.tw/zip/'.urlencode($cityname.$addrName);
            
            $getDistStr = load_contents($addrUrl,true);
            //$getDistStr = get_between($getDistStr,'id="new-adrs">','id="zipcode6"');
            //$getDist = trim(get_between($getDistStr,$cityname,$addrName));
            //$getDistCode = $codeArr[$getDist];
            //echo $getDist.'<br>';
            echo $getDistStr.'<br>';
            sleep(2);

        }
        exit();
        /*
        $chkSameFile = '';
        $chkSameOpt=0;
        $chkDoneOpt=0;
        $cTotalRowNum=0;
        $cTotalDupRows=0;
        $getBR = "";
        $insertRow=2;//跳過第一列抬頭列
        */
        $cTotalFiles=0;
        $cTotalRowNum=0;
        $cTotalDupRows=0;
        $cTotaXLSRows=0;
        $cErrorCount=0;
        $source_folderpath=$basefolder.$subfolder;
        if (is_dir($source_folderpath)) {
            $cfile = opendir($source_folderpath); {
                if ($cfile) {
                    $cTotalFiles=0;
                    $i=0;
                    while (($filename = readdir($cfile)) !== FALSE) {
                        if ($filename != '.' && $filename != '..' && $filename != '.DS_Store' && $filename != 'Icon?') {
                            $EXL= PHPExcel_IOFactory::load($source_folderpath.'/'.$filename); 
                            $SHEET=$EXL->getSheet(0);
                            $startRow = $SHEET->getCell("E2")=='單價'? 4 : 3;
                            $endRow = $SHEET->getHighestRow(); // 取得總列數 
                            $xlsRows = ($endRow - $startRow) + 1;
                            $cTotaXLSRows += $xlsRows;
                            $fi=0;     
                            for ($rowNum = $startRow; $rowNum <= $endRow; $rowNum++) {
                                $rowTypeFrom = $SHEET->getCellByColumnAndRow(10, $rowNum)->getValue();
                                //if($rowTypeFrom=='土地'){ continue; }
                                $rowNameFrom = $SHEET->getCellByColumnAndRow(0, $rowNum)->getValue();
                                $rowName = $rowTypeFrom=='土地' ? $rowNameFrom : strchr($rowNameFrom,'號',true).'號';

                                $addrUrl = 'https://zip5.5432.tw/zip/'.urlencode($cityname.$rowName);
                                $getDistCode = '';
                                /*
                                if($rowTypeFrom=='土地'){
                                    $getDistCode = '';
                                } else {
                                    $getDistStr = load_contents($addrUrl,true);
                                    $getDistStr = get_between($getDistStr,'id="new-adrs">','id="zipcode6"');
                                    $getDist = trim(get_between($getDistStr,$cityname,$rowName));
                                    $getDistCode = $codeArr[$getDist];
                                }
                                */
                                $getyearFrom = $SHEET->getCellByColumnAndRow(2, $rowNum)->getValue();
                                $getyearFromSplit = explode('/',$getyearFrom);
                                $getyear = $getyearFromSplit[0];

                                $insSQLh = "(NULL, ";
                                $insSQL = "'".$getDistCode."',";
                                $insSQL .= "'".$getyear."',";
                                $insSQL .= "'".$rowNameFrom."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(1, $rowNum)->getValue()."',";
                                $insSQL .= "'".$getyearFrom."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(3, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(4, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(5, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(6, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(7, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(8, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(9, $rowNum)->getValue()."',";
                                $insSQL .= "'".$rowTypeFrom."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(11, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(12, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(13, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(14, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(15, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(16, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(17, $rowNum)->getValue()."',";
                                $insSQL .= "'".$filename."'";
                                $insSQL .= ")";
                                //echo $insSQL;

                                $doDISTsql = "INSERT INTO `data_distsplit_32` VALUES ".$insSQLh.$insSQL;
                                $ins = mysqli_query($link, $doDISTsql);
                                $insID = mysqli_insert_id($link);
                                if($ins && $insID>0){
                                    $cTotalDupRows++;
                                    //echo $cTotalDupRows.'. ('.$insID.', '.$insSQL.'<br>';
                                } else {
                                    $cErrorCount++;
                                    echo $cErrorCount.'. ("0", '.$insSQL.'<br>';
                                }
                                $cTotalRowNum++;
                                //if($fi==0){ break; }
                                //$fi++;
                                //sleep(2);
                            }
                            $cTotalFiles++;
                            //if($i==0){ break; }
                            //$i++;
                            //sleep(3);
                        }
                    }
                }
            }
        }
    $timeUse = time() - $startTime;
    echo "<hr>";
    echo date('Y-m-d H:s:i').' Done!';
    echo "<hr>檔案紀錄筆數: ".$cTotaXLSRows." 筆";
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

