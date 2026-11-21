<?php
//////////////////////////////////////////////////////////////////////////////
///////////////////////// 未分區紀錄寫入工作資料表 ///////////////////////////////
//////////////////////////////////////////////////////////////////////////////
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
    $filename = isset($_POST['filename']) ? $_POST['filename'] : '';
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
    來源檔案名稱：
    <input type="text" id="filename" name="filename" style="width:400px;" value="<?php echo $filename; ?>">
    <button type="submit" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">開始建立</button>
    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/circles_xlsToDB.php';">RESET</button>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
</form>
<hr>

<?php
    //if(empty($filename)){ echo "輸入指定的檔案名稱！";exit(); }
    $basefolder = "";
    $filename = "circles_0524.xlsx";
    $source_folderpath=$basefolder.$filename;
    ///////////////////////////////////////////////////////////////////////
        //$cTotalFiles=0;
        $cTotalRowNum=0;
        $cTotalDupRows=0;//
        //$cTotaXLSRows=0;
        $cErrorCount=0;


        $EXL= PHPExcel_IOFactory::load($source_folderpath); 
        $SHEET=$EXL->getSheet(0);
        $startRow = 2;
        $endRow = $SHEET->getHighestRow(); // 取得總列數 
        $dataRows = ($endRow - $startRow) + 1;
        //$endRow=4;
        $i=1;
        for ($rowNum = $startRow; $rowNum <= $endRow; $rowNum++) {

            $insSQL = "(".$i.", ";
            $insSQL .= "'',";
            $insSQL .= "'".$SHEET->getCellByColumnAndRow(0, $rowNum)->getValue()."',";
            $insSQL .= "'".$SHEET->getCellByColumnAndRow(2, $rowNum)->getValue()."',";
            $insSQL .= "'0',";
            $insSQL .= "'0',";
            $insSQL .= "'".$SHEET->getCellByColumnAndRow(4, $rowNum)->getValue()."',";         
            $insSQL .= "'".$SHEET->getCellByColumnAndRow(5, $rowNum)->getValue()."',";         
            $insSQL .= "'0.00',";
            $insSQL .= "'0.00',";
            $insSQL .= "'1'";
            $insSQL .= ");";
            echo "INSERT INTO fh.socialcircle (scrc_id,scrc_code,scrc_city,scrc_district,scrc_public,scrc_type,scrc_title,scrc_circletitle,scrc_lat,scrc_lng,scrc_status) VALUES".$insSQL.'<br>';
/*
            $doDISTsql = "INSERT INTO `data_aprret` VALUES ".$insSQL;
            $ins = mysqli_query($link, $doDISTsql);
            $insID = mysqli_insert_id($link);

            if($ins && $insID>0){
                $cTotalDupRows++;
                //echo $cTotalDupRows.'. ('.$insID.', '.$insSQL.'<br>';
            } else {
                $cErrorCount++;
                echo $cErrorCount.'. '.$insSQL.'<br>';
            }
*/
            $cTotalRowNum++;
            $i++;
            //if($fi==0){ break; }
            //$fi++;
            //sleep(2);
        }
        //$cTotalFiles++;
        //if($i==0){ break; }
        //$i++;
        //sleep(3);
    /*
    $timeUse = time() - $startTime;
    echo "<hr>";
    echo date('Y-m-d H:s:i').' Done!';
    echo "<hr>共產生資料筆數: ".$cTotalDupRows." / ".$cTotalRowNum." 筆";
    echo "<hr>錯誤資料筆數: ".$cErrorCount." 筆";
    echo "<hr>花費時間: ".$timeUse." 秒<br><br><br><br>";
    //echo "<hr><br>Opt Doned: ".$chkDoneOpt."<br><br><br><br>";
    */
    mysqli_close($link);
    ?>
    <!-- <audio autoplay='true' style='display:none;'><source src='alert.wav'></audio> -->
    <br><br><br><br>
    </div>
</body>
</html>

