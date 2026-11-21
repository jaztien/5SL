<?php
    error_reporting(E_ALL); 
    header("Content-Type:text/html; charset=utf-8");
	include 'aprret_func.php';
	include 'PHPExcel/Classes/PHPExcel.php';
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
    $fromFile = isset($_POST['fromFile']) ? $_POST['fromFile'] : '';
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

<form id="form_readtofile" name="form_readtofile" action="#" method="post">
    年份: <input type="text" id="fromFile" name="fromFile" style="width:240px;" value="<?php echo $fromFile; ?>">&nbsp;&nbsp;&nbsp;&nbsp;
    <button type="submit">SEND</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <button type="button" onClick="window.location.href ='http://localhost:8888/conprojectFilter.php';">RESET</button>
    <button type="button" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
</form>
<hr>
    
<?php
if(empty($fromFile)){ echo "尚未指定檔案！";exit(); }


    //$fromFile = "101_Full_1-430_187732_2024-02-25.csv";
    $csv_subfolder = "aprretFinal/";
    // 取得資料 (欄位名稱 and 欄位值)
    $insertError='';
    $chkSameOpt=0;
    $chkDoneOpt=0;
    $cTotalRowNum=0;
    $excel= PHPExcel_IOFactory::load($csv_subfolder.$fromFile); 
    $sheet=$excel->getSheet(0);
    $highestRow = $sheet->getHighestRow(); // 取得總列數
    $startRow = 2;//起始列數
    $totalRows = $highestRow - $startRow;
    for ($ri = $startRow; $ri <= $highestRow; $ri++) {
        $cTotalRowNum++;
        $getCity = trim($sheet->getCellByColumnAndRow(0, $ri)->getValue());
        $getDist = trim($sheet->getCellByColumnAndRow(1, $ri)->getValue());
        $getCommStr = trim($sheet->getCellByColumnAndRow(3, $ri)->getValue());
        if(!empty($getCity) && !empty($getDist) && !empty($getCommStr)){
            $getCommStr = addslashes($getCommStr);
            //------------ Check if exists -----------------------
            $res = mysqli_query($link, "SELECT * FROM data_conproject WHERE conp_city='".$getCity."' AND conp_district='".$getDist."' AND conp_title='".$getCommStr."'");
            $row_cnt = mysqli_num_rows($res);
            if($row_cnt>0) {
                $chkSameOpt++;
            } else {
                $sqlc = "INSERT INTO `data_conproject` (`conp_city`, `conp_district`, `conp_title`, `conp_mode`, `conp_check`, `conp_status`) VALUES ";
                $sqld = "('".$getCity."', '".$getDist."', '".$getCommStr."', 1, 4, 1)";
                $resultc = mysqli_query($link, $sqlc.$sqld);
                if($resultc){
                    echo $sqld.',<br>';
                    $chkDoneOpt++;
                } else {
                    $insertError .= '[寫入失敗]@@ '.$fromFile.' @@ D'.$ri.' @@ '.$getCommStr.'<br>';
                }
            }
        }
    }
if(!empty($insertError)) { echo $insertError; }
echo "<hr><br>From File : ".$fromFile;
echo "<hr><br>Total Row Numbers: ".$cTotalRowNum."<br>"; 
echo "<hr><br>Opt Checked: ".$chkSameOpt."<br><br>";
echo "<hr><br>Opt Doned: ".$chkDoneOpt."<br><br><br>";

mysqli_close($link);

?>
</div>
</body>
</html>