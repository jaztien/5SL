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

    $basefolder = "aprretFiles/Original/1130326/";
    $subfolder = "32/原嘉義市3201";//原嘉義市3201||原新竹市1701
    $cityname = "嘉義市";
    $codeArr = array("東區"=>"1701","北區"=>"1702","香山區"=>"1703");
///////////////////////////////////////////////////////////////////////

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
    //echo "<hr>共產生資料筆數: ".$cTotalDupRows." / ".$cTotalRowNum." 筆";
   // echo "<hr>錯誤資料筆數: ".$cErrorCount." 筆";
    echo "<hr>花費時間: ".$timeUse." 秒<br><br><br><br>";
    //echo "<hr><br>Opt Doned: ".$chkDoneOpt."<br><br><br><br>";
    mysqli_close($link);
    ?>
    <audio autoplay='true' style='display:none;'><source src='alert.wav'></audio>
    <br><br><br><br>
    </div>
</body>
</html>

