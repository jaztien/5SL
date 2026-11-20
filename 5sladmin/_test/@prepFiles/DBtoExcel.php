<?php
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
        echo "正確連接資料庫";
    }
    else {
        echo "不正確連接資料庫</br>" . mysqli_connect_error();
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
    $maxDup = isset($_POST['maxDup']) ? $_POST['maxDup'] : 10000;
    $fromID = isset($_POST['fromID']) ? $_POST['fromID'] : '';
    $toID = isset($_POST['toID']) ? $_POST['toID'] : '';
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
    最大截取值: <input type="text" id="maxDup" name="maxDup" style="width:100px;" value="<?php echo $maxDup; ?>">
    <br>
    起始ID: <input type="text" id="fromID" name="fromID" style="width:100px;" value="<?php echo $fromID; ?>">
    終止ID: <input type="text" id="toID" name="toID" style="width:100px;" value="<?php echo $toID; ?>">
    <button type="submit">SEND</button>
    <button type="button" onClick="window.location.href ='http://localhost:8888/DBtoExcel.php';">RESET</button>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
    <button type="button" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
</form>
<hr>
   
<?php
//$cFolderNum = 0;
//$csv_folderpath = "aprretCSV/";
//$objPHPExcel = new PHPExcel();                     //實例化一個PHPExcel()對象
//$objSHEET = $objPHPExcel->getActiveSheet();        //選取當前的sheet對象
if(empty($fromID) || empty($toID) || empty($maxDup)){ echo "尚未設定組大截取值，或是起始結束ID段！";exit(); }
if($fromID==1){ $fromID=0; }
$osql = "SELECT SUM(f_rows - f_startrow) AS ss FROM filelist WHERE f_id > ".$fromID." AND f_id <=".$toID;
$oresult = mysqli_query($link, $osql);
//$orow = mysql_fetch_array($oresult);
$orow = mysqli_fetch_assoc($oresult);
//echo $orow['ss'].'/'.$orow_cnt.'<hr>';
echo $orow['ss'].'<hr>';



$sql = "SELECT * FROM filelist";
//$sql .= " WHERE f_year='".$folderr."'";
//$sql .= " WHERE f_id IN ('2','3')";
//$sql .= " WHERE f_id > ".$fromID." AND f_id <=".$toID;
//$sql .= " WHERE f_id =1";
//$sql .= " WHERE f_year IN ('".$folderr."','".$folderr2."')";
//$sql .= " LIMIT 1";
$cTotalRows = 0;
$totalFileRows = 0;
$nextFileRows = 0;
$checkLimit = 0;
$startID = 1;
$endID = 0;
$cFolderName = '101';
$result = mysqli_query($link, $sql);
mysqli_data_seek($result, 0);  // 指標移到第一筆資料
$sort=0;
while($flist = mysqli_fetch_assoc($result)){
    $nextFileRows = $flist['f_rows']-$flist['f_startrow'];
    $checkLimit = $totalFileRows + $nextFileRows;
    if($checkLimit > $maxDup || $cFolderName != $flist['f_year'] || $flist['f_id']==6009){
        echo $cFolderName;
        echo '@@';
        echo $sort;
        echo '@@';
        echo $totalFileRows;
        echo '@@';
        echo $startID;
        echo '<br>';
        if($cFolderName != $flist['f_year']){
            $sort=1;
        }
        $totalFileRows = 0;
        $cFolderName = $flist['f_year'];
            /*
            $sql = "INSERT INTO filewrap VALUES(null, '', '', '')";
            $result = mysqli_query($link, $sql);
            if($result){ $doneFiles++; }
            else { break; }
            */
    }
    $startID = $flist['f_id'];
    $cTotalRows += $nextFileRows;
    $totalFileRows += ($flist['f_rows']-$flist['f_startrow']);
/*
    $objSHEET->setCellValueByColumnAndRow(0, 1,'城市碼')
        ->setCellValueByColumnAndRow(1, 1,'地區碼')
        ->setCellValueByColumnAndRow(2, 1,'地址')
        ->setCellValueByColumnAndRow(3, 1,'社區簡稱')
        ->setCellValueByColumnAndRow(4, 1,'社區ID');
*/
       
}

echo '<hr>';
echo 'Total Rows: '.$cTotalRows;
echo '<br>';
//echo 'Total Rows: '.$cTotalRows;
//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'CSV');   //設定寫入excel的類型
//$objWriter->setUseBOM(true);//中文亂碼處理
//$objWriter->save($folderr.'_full_'.date('Y-m-d').'_'.$cTotalDupRows.'.csv');//保存文件


//釋放資源
//$EXL->disconnectWorksheets();
//釋放課程商品
//unset($EXL);

//----------------------------------
mysqli_close($link);

?>
</div>
</body>
</html>

