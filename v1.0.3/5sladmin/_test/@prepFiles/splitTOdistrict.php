<?php
/////////////////////////////////////////////////////////////////////////
//////// 建立暫存資料表，以利修改分區作業，完成分區後以此資料表製作工作檔案 ////////
/////////////////////////////////////////////////////////////////////////
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

    $basefolder = "aprretFiles/Original/1130326/"; 
    $subfolder = "17/1701";
    $cityname = "新竹市";
    $codeArr = array("東區"=>"1701","北區"=>"1702","香山區"=>"1703");
    $dataTable = "data_distsplit_17";

/////////////////////////////////////////////////////////////////////////
//////// 建立暫存資料表，以利修改分區作業，完成分區後以此資料表製作工作檔案 ////////
/////////////////////////////////////////////////////////////////////////
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

                                $doDISTsql = "INSERT INTO `".$dataTable."` VALUES ".$insSQLh.$insSQL;
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
                                sleep(2);
                            }
                            $cTotalFiles++;
                            //if($i==0){ break; }
                            //$i++;
                            sleep(2);
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

