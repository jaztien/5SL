<?php
##############################################################
################### 5SeasonLife 後備作業 #########################
############ 生成 Excel 檔案以供管理者比對操作使用 ################
##############################################################
    error_reporting(E_ALL); 
    header("Content-Type:text/html; charset=utf-8");
	include 'PHPExcel/Classes/PHPExcel.php';
    $host = 'localhost';
    $dbuser ='root';
    $dbpassword = '1111';
    $dbname = 'jazhome';
    $link = mysqli_connect($host,$dbuser,$dbpassword,$dbname);
    if($link){
        mysqli_query($link,'SET NAMES uff8');
        //echo "正確連接資料庫";
    }
    else {
        //echo "不正確連接資料庫</br>" . mysqli_connect_error();
    }
    /* 
    $link = @mysqli_connect("localhost", "root", "1111");
    if( !$link ) {
        echo "MySQL 連線失敗";
        return;
    }
    else
        echo "MySQL 連線成功<br>";
    */
    
    // 選擇資料庫
    //$db = mysqli_select_db($link, "jazhome");
    // 設定字元集 (這行要有, 不然下面新增資料的中文部分, 會是亂碼)
    //mysqli_query($link, "SET NAMES 'utf8'");
    /*
    // 新增資料 (DML)

    $result = mysqli_query($link, "insert into test_company values(3, '歡迎', '歡迎 tomkuo139')" );
    if( !$result )
        echo "新增資料失敗<br>";
    else
        echo "新增資料成功<br>";
    
    // 查詢資料 (Query)

    $result = mysqli_query($link, "select * from test_company");
    $row_cnt = mysqli_num_rows($result);
    echo "共有 " . $row_cnt . " 筆資料<br>";
    
    // 取得資料 (第幾個欄位 and 欄位值)
    while( $row = mysqli_fetch_row($result) )
    {
        foreach( $row as $item=>$val )  // $item 會是 0, 1, 2, ...
            echo $item . " = " . $val . "<br>";
    }
    
    // 取得資料 (欄位名稱 and 欄位值)
    mysqli_data_seek($result, 0);  // 指標移到第一筆資料
    while( $row = mysqli_fetch_assoc($result) )
    {
        foreach( $row as $item=>$val )  // $item 會是 Column Name
            echo $item . " = " . $val . "<br>";
    }
    */
    // 關閉連線
    //$maxDup = isset($_POST['maxDup']) ? $_POST['maxDup'] : 10000;
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

<hr>
<div style="font-size:16px;line-height:20px;">
<!--
<form id="form_readtofile" name="form_readtofile" action="#" method="post">
    最大截取值: <input type="text" id="maxDup" name="maxDup" style="width:100px;" value="<?php echo $maxDup; ?>">
    <br>
    起始ID: <input type="text" id="fromID" name="fromID" style="width:100px;" value="<?php echo $fromID; ?>">
    終止ID: <input type="text" id="toID" name="toID" style="width:100px;" value="<?php echo $toID; ?>">
    <button type="submit">SEND</button>
    <button type="button" onClick="window.location.href ='http://localhost:8888/DBtoExcel.php';">RESET</button>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
    <button type="button" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
</form>
-->
<hr>
   
<?php
/*
$objPHPExcel = new PHPExcel();                     //實例化一個PHPExcel()對象
$objSHEET = $objPHPExcel->getActiveSheet();        //選取當前的sheet對象
$locGet=file_get_contents("locations.json");
$locJS = json_decode($locGet,true);


$cTotalUp=0;

$sql = "SELECT * FROM commcheck WHERE bc_id=0 AND comm_status=0";
//$sql = "SELECT comm_district,comm_title, COUNT(*) c FROM commcheck WHERE comm_title<>'' AND comm_check<>'' GROUP BY comm_district,comm_title HAVING c > 1 ";
//$sql .= " WHERE comm_check=5"; 
//$sql .= " AND comm_title<>''"; 
$sql .= " ORDER BY comm_id ASC";
//$sql .= " LIMIT 0,500";
//$sql .= " AND comm_id BETWEEN 10000 AND 35000";
$res = mysqli_query($link, $sql);


$objSHEET->setCellValueByColumnAndRow(0, 1,'實登建案ID')
->setCellValueByColumnAndRow(1, 1,'地區碼')
->setCellValueByColumnAndRow(2, 1,'建案名稱')
->setCellValueByColumnAndRow(3, 1,'比對用簡稱')
->setCellValueByColumnAndRow(4, 1,'期數')
->setCellValueByColumnAndRow(5, 1,'分區')
->setCellValueByColumnAndRow(6, 1,'區域名稱')
->setCellValueByColumnAndRow(7, 1,'代表住址')
->setCellValueByColumnAndRow(8, 1,'關聯建案ID')
->setCellValueByColumnAndRow(9, 1,'檢號')
->setCellValueByColumnAndRow(10, 1,'狀態');

$objSHEET->setCellValueByColumnAndRow(0, 2,'comm_id')
->setCellValueByColumnAndRow(1, 2,'comm_district')
->setCellValueByColumnAndRow(2, 2,'comm_title')
->setCellValueByColumnAndRow(3, 2,'comm_puretitle')
->setCellValueByColumnAndRow(4, 2,'comm_purephase')
->setCellValueByColumnAndRow(5, 2,'comm_pureext')
->setCellValueByColumnAndRow(6, 2,'Area')
->setCellValueByColumnAndRow(7, 2,'comm_retaddr')
->setCellValueByColumnAndRow(8, 2,'bc_id')
->setCellValueByColumnAndRow(9, 2,'comm_check')
->setCellValueByColumnAndRow(10, 2,'comm_status');

$startRow = 3;

$i=1;
while( $row = mysqli_fetch_assoc($res)){

    $setCityText = !empty($row['comm_city']) ? $locJS[$row['comm_city']]['text'] : '';
    $setDistText = !empty($row['comm_city'])&&!empty($row['comm_district']) ? $locJS[$row['comm_city']]['district'][$row['comm_district']] : '';
    $fullTitle =  str_replace('臺','台',$row['comm_retaddr']);
    $EZadd = str_replace($setCityText.$setDistText,'',$fullTitle);
    
    $objSHEET->setCellValueByColumnAndRow(0, $startRow, $row['comm_id'])
    ->setCellValueByColumnAndRow(1, $startRow, $row['comm_district'])
    ->setCellValueByColumnAndRow(2, $startRow, $row['comm_title'])
    ->setCellValueByColumnAndRow(3, $startRow, $row['comm_puretitle'])
    ->setCellValueByColumnAndRow(4, $startRow, $row['comm_purephase'])
    ->setCellValueByColumnAndRow(5, $startRow, $row['comm_pureext'])
    ->setCellValueByColumnAndRow(6, $startRow, $setCityText.$setDistText)
    ->setCellValueByColumnAndRow(7, $startRow, $EZadd)
    ->setCellValueByColumnAndRow(8, $startRow,'')
    ->setCellValueByColumnAndRow(9, $startRow,$row['comm_check'])
    ->setCellValueByColumnAndRow(10, $startRow,$row['comm_status']);

    echo $row['comm_id']."-".$row['comm_title']."<br>";
    $startRow++;
$i++;

}
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('【比對用】實價登錄建案資料_尚無關聯筆數+部份欄位_20240629.xlsx');//保存文件



echo $cTotalUp ." / ". $i;
exit();

*/
//$cFolderNum = 0;
//$csv_folderpath = "aprretCSV/";
$objPHPExcel = new PHPExcel();                     //實例化一個PHPExcel()對象
$objSHEET = $objPHPExcel->getActiveSheet();        //選取當前的sheet對象
$locJS=_fn_getCachedJsonData("locations.json");


$sql = "SELECT * FROM `data_buildcase` WHERE `bc_status`<=5";
//$sql .= " AND bc_year='".$folderr."'";
//$sql .= " AND bc_id IN ('2','3')";
//$sql .= " AND bc_id > ".$fromID." AND bc_id <=".$toID;
//$sql .= " AND bc_id =1";
//$sql .= " AND bc_year IN ('".$folderr."','".$folderr2."')";
$sql .= " ORDER BY `bc_id` ASC";
//$sql .= " LIMIT 0,100";
$cTotalRows = 0;
$totalFileRows = 0;
$nextFileRows = 0;
$checkLimit = 0;
$startID = 1;
$endID = 0;
//$cFolderName = '101';
$res = mysqli_query($link, $sql);
mysqli_data_seek($res, 0);  // 指標移到第一筆資料
$sort=0;

$objSHEET->setCellValueByColumnAndRow(0, 1,'建案ID')
->setCellValueByColumnAndRow(1, 1,'地區碼')
->setCellValueByColumnAndRow(2, 1,'建案名稱')
->setCellValueByColumnAndRow(3, 1,'比對用簡稱')
->setCellValueByColumnAndRow(4, 1,'期數')
->setCellValueByColumnAndRow(5, 1,'區數')
->setCellValueByColumnAndRow(6, 1,'區域名稱')
->setCellValueByColumnAndRow(7, 1,'代表住址')
->setCellValueByColumnAndRow(8, 1,'建商')
->setCellValueByColumnAndRow(9, 1,'樓高')
->setCellValueByColumnAndRow(10, 1,'檢號')
->setCellValueByColumnAndRow(11, 1,'狀態');

$objSHEET->setCellValueByColumnAndRow(0, 2,'bc_id')
->setCellValueByColumnAndRow(1, 2,'bc_district')
->setCellValueByColumnAndRow(2, 2,'bc_title')
->setCellValueByColumnAndRow(3, 2,'bc_puretitle')
->setCellValueByColumnAndRow(4, 2,'bc_purephase')
->setCellValueByColumnAndRow(5, 2,'bc_pureext')
->setCellValueByColumnAndRow(6, 2,'Area')
->setCellValueByColumnAndRow(7, 2,'bc_addr')
->setCellValueByColumnAndRow(8, 2,'bc_investor')
->setCellValueByColumnAndRow(9, 2,'bc_abovemax')
->setCellValueByColumnAndRow(10, 2,'bc_check')
->setCellValueByColumnAndRow(11, 2,'bc_status');

$startRow = 3;
while($row = mysqli_fetch_assoc($res)){
        $setCityText = !empty($row['bc_city']) ? $locJS[$row['bc_city']]['text'] : '';
        $setDistText = !empty($row['bc_city'])&&!empty($row['bc_district']) ? $locJS[$row['bc_city']]['district'][$row['bc_district']] : '';
        $fullTitle =  str_replace('臺','台',$row['bc_addr']);
        $EZadd = str_replace($setCityText.$setDistText,'',$fullTitle);
        
        $objSHEET->setCellValueByColumnAndRow(0, $startRow, $row['bc_id'])
        ->setCellValueByColumnAndRow(1, $startRow, $row['bc_district'])
        ->setCellValueByColumnAndRow(2, $startRow, $row['bc_title'])
        ->setCellValueByColumnAndRow(3, $startRow, $row['bc_puretitle'])
        ->setCellValueByColumnAndRow(4, $startRow, $row['bc_purephase'])
        ->setCellValueByColumnAndRow(5, $startRow, $row['bc_pureext'])
        ->setCellValueByColumnAndRow(6, $startRow, $setCityText.$setDistText)
        ->setCellValueByColumnAndRow(7, $startRow, $EZadd)
        ->setCellValueByColumnAndRow(8, $startRow,$row['bc_investor'])
        ->setCellValueByColumnAndRow(9, $startRow,$row['bc_abovemax'])
        ->setCellValueByColumnAndRow(10, $startRow,$row['bc_check'])
        ->setCellValueByColumnAndRow(11, $startRow,$row['bc_status']);
    
        echo $row['bc_id']."-".$row['bc_title']."<br>";
        $startRow++;
    }
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('【被比對用】建案資料_全筆數+部份欄位_20240629.xlsx');//保存文件

//----------------------------------
mysqli_close($link);

?>
</div>
</body>
</html>

