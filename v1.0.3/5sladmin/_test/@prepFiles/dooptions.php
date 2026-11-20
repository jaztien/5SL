<?php
    error_reporting(E_ALL); 
    header("Content-Type:text/html; charset=utf-8");
	include 'PHPExcel/Classes/PHPExcel.php';
    $host = 'localhost';
    $dbuser ='root';
    $dbpassword = '1111';
    $dbname = 'jazhome';

    //echo _fn_num_b2h("二十五");exit();
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
    
<?php
echo date('Y-m-d',1354291200);
exit();

$folderr="111";
$optSQL = "SELECT * FROM options_usefor";
$optRES = mysqli_query($link, $optSQL);
$optARR = array();
$maxIdx=0;
$ia=0;
while( $rowa = mysqli_fetch_assoc($optRES)){
    $optARR[] = $rowa['optu_title'];
    $maxIdx = $rowa['optu_index'];
    $ia++;
}

// 取得資料 (欄位名稱 and 欄位值)
$csv_folderpath = "aprretCSV/";

$sql = "SELECT * FROM filelist";
$sql .= " WHERE f_year='".$folderr."'";
//$sql .= " WHERE f_year IN ('".$folderr."','".$folderr2."')";
//$sql .= " LIMIT 1";
$result = mysqli_query($link, $sql);
mysqli_data_seek($result, 0);  // 指標移到第一筆資料
$chkSameFile = '';
$chkSameOpt=0;
$chkAddOpt=0;
$cTotalRowNum=0;
$getBR = "";
while( $row = mysqli_fetch_assoc($result)){
    $csv_subfolder = $csv_folderpath.$row['f_year'].'/';
    $highestRow = $row['f_rows']; //總列數
    $startRow = $row['f_startrow']+1;//起始列數
    $totalRows = $highestRow - $startRow;
    //--------------------------------//commcheck//layercheck
    $excel= PHPExcel_IOFactory::load($csv_subfolder.$row['f_name']); 
    $sheet=$excel->getSheet(0);
    for ($ri = $startRow; $ri <= $highestRow; $ri++) {
        $cTotalRowNum++;
        $getOptionStr = trim($sheet->getCellByColumnAndRow(16, $ri)->getValue());
        if(empty($getOptionStr)){ continue; }
        $getOptionArr = explode('、',$getOptionStr);
        $count =count($getOptionArr);
        if($count==0){ continue; }
        foreach($getOptionArr as $ot){
            if(in_array($ot,$optARR)){ 
                $chkSameOpt++;
                continue; 
            } 
            //$sqlc = "INSERT INTO flooropt VALUES(null, '".$ot."', '')";
            //$resultc = mysqli_query($link, $sqlc);
            //if($resultc){
                array_push($optARR, $ot);
                $chkAddOpt++;
                echo $csv_subfolder.$row['f_name'].'@@'.$ri.':'.$maxIdx.':'.$ot.'<br>';
                $maxIdx++;
            // }        
        }        
    }


}
echo "<hr><br>Folder: ".$folderr." Total Row Numbers: ".$cTotalRowNum."<br>";
echo "<hr><br>Opt Checked: ".$chkSameOpt."<br><br><br><br>";
echo "<hr><br>Opt Doned: ".$chkAddOpt."<br><br><br><br>";

mysqli_close($link);

?>
</div>
</body>
</html>