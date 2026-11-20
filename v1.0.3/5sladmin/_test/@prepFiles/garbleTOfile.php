<?php
    error_reporting(E_ALL); 
	include 'aprret_func.php';
	include 'PHPExcel/Classes/PHPExcel.php';
    header("Content-Type:text/html; charset=utf-8");
    $year = isset($_POST['year']) ? $_POST['year'] : '';
    $sort = isset($_POST['sort']) ? $_POST['sort'] : '';
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

echo date('Y-m-d',1448294400);
exit();
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
排序: <input type="text" id="sort" name="sort" style="width:100px;" value="<?php echo $sort; ?>">&nbsp;&nbsp;&nbsp;&nbsp;
    <button type="submit">SEND</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <button type="button" onClick="window.location.href ='http://localhost:8888/garbleTOfile.php';">RESET</button>
    <button type="button" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
</form>
<hr>
<?php
/*
        $pattern = '/[?]/';
        if (preg_match($pattern, "這是一個測試?")) {
            echo "yes";
        } else { echo "no"; }
        exit();
*/
    //if(empty($year)||empty($sort)){ echo "尚未指定年份及序！";exit(); }
    if(empty($year)){ echo "尚未指定年份及序！";exit(); }
    $csv_folder = $year;
    $csv_sort = $sort;
    $csv_filename = "101-112_Full_1-6009_2024-02-28.xlsx";
    //$csv_filename = $csv_folder.'_'.$csv_sort.'.csv';//101-112_Full_1-6009_2024-02-28.xlsx
    //$csv_folderpath = "aprretFinal/split/".$year."/";
    $csv_folderpath = "aprretFinal/";

    $yearARR=array(
        '101' => 0,
        '102' => 1,
        '103' => 2,
        '104' => 3,
        '105' => 4,
        '106' => 5,
        '107' => 6,
        '108' => 7,
        '109' => 8,
        '110' => 9,
        '111' => 10,
        '112' => 11
    );



    $objPHPExcel = new PHPExcel();                     //實例化一個PHPExcel()對象
    $objSHEET = $objPHPExcel->getActiveSheet();        //選取當前的sheet對象
    
    $objSHEET->setCellValueByColumnAndRow(0, 1,'城市碼')
        ->setCellValueByColumnAndRow(1, 1,'地區碼')
        ->setCellValueByColumnAndRow(2, 1,'城市地區')
        ->setCellValueByColumnAndRow(3, 1,'待查地址')
        ->setCellValueByColumnAndRow(4, 1,'待查社區')
        ->setCellValueByColumnAndRow(5, 1,'更正結果')
        ->setCellValueByColumnAndRow(6, 1,'更正結果2')
        ->setCellValueByColumnAndRow(7, 1,'交易日期')
        ->setCellValueByColumnAndRow(8, 1,'校驗');

    $chkSameFile = '';
    $chkSameOpt=0;
    $chkDoneOpt=0;
    $cTotalDupRows=0;
    $insertRow=2;//跳過第一列抬頭列
        //--------- 載入檔案 ----------------------------------
        $EXL= PHPExcel_IOFactory::load($csv_folderpath.$csv_filename); 
        //$SHEET=$EXL->getSheet(0);
        $SHEET=$EXL->getSheet($yearARR[$year]);
        $highestRow = $SHEET->getHighestDataRow();           //總列數
        //$highestRow = 10;           //總列數
        $startRow = 2;       //起始列數
        $totalRows = $highestRow - $startRow;   //實機有效列數
        //-------------------- 開始每個檔案中每筆紀錄一一表列 -----------------------
        $pattern = '/[?]/';
        for ($rowNum = $startRow; $rowNum <= $highestRow; $rowNum++) {
            $getADD = $SHEET->getCellByColumnAndRow(2, $rowNum)->getValue();
            //$getCON = $SHEET->getCellByColumnAndRow(3, $rowNum)->getValue();
            $cTotalDupRows++;
            if (preg_match($pattern, $getADD)) {
                $getCity = $SHEET->getCellByColumnAndRow(0, $rowNum)->getValue();
                $getDist = $SHEET->getCellByColumnAndRow(1, $rowNum)->getValue();
                $objSHEET->setCellValueByColumnAndRow(0, $insertRow, $getCity);//寫入【A】城市代碼
                $objSHEET->setCellValueByColumnAndRow(1, $insertRow, $getDist);//寫入【B】區域代碼
                //----- 地段位置或門牌 ---------------------------------------------------------
                $getFormatAdd = _fn_get_fulladdress($getCity,$getDist,'');//讀取原始檔案A欄位住址，檢查並重新格式
                $objSHEET->setCellValueByColumnAndRow(2, $insertRow, $getFormatAdd['cityarea']);//寫入【C】地段位置或住址門牌
                //------ 地址/社區簡稱處理 ----------------------------------------------------------
                $objSHEET->setCellValueByColumnAndRow(3, $insertRow, $getADD);//寫入地址
                //$objSHEET->setCellValueByColumnAndRow(4, $insertRow, $getCON);//寫入社區
                //------ 日期處理 -------------------------------------------------------------
                $objSHEET->setCellValueByColumnAndRow(5, $insertRow, '');//更正結果
                $objSHEET->setCellValueByColumnAndRow(6, $insertRow, '');//更正結果2
                //------ 直接移轉 -------------------------------------------------------------
                $getDate = $SHEET->getCellByColumnAndRow(5, $rowNum)->getValue();
                $getDate = !empty($getDate) ? date('Y-m-d',$getDate) : '';
                $objSHEET->setCellValueByColumnAndRow(7, $insertRow, $getDate);//日期
                $objSHEET->setCellValueByColumnAndRow(8, $insertRow, 0);//校驗

                $insertRow++;
            } else { continue; }

        }
    //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'CSV');   //設定寫入excel的類型
    $objWriter->setUseBOM(true);//中文亂碼處理
    $objWriter->save('garble_'.$csv_folder.'_'.$csv_sort.'_'.$insertRow.'_'.date('Y-m-d').'.csv');//保存文件
    //$objWriter->save($cFolderName.'_'.$fromID.'-'.$toID.'_'.date('Y-m-d').'_'.$cTotalDupRows.'.xlsx');//保存文件
   

    //釋放資源
    $EXL->disconnectWorksheets();
    //釋放課程商品
    unset($EXL);

    $timeUse = time() - $startTime;
    echo date('Y-m-d H:s:i').' Done!';

    echo "<hr><br>年份: ".$year." 共產生資料筆數: ".$insertRow." / ".$cTotalDupRows." 筆<br>";
    echo "<hr><br>花費時間: ".$timeUse." 秒<br><br><br><br>";
    //echo "<hr><br>Opt Doned: ".$chkDoneOpt."<br><br><br><br>";
    
    //mysqli_close($link);
?>
</div>
</body>
</html>

