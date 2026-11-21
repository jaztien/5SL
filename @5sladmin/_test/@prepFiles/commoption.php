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
    $year = isset($_POST['year']) ? $_POST['year'] : '';
    $fromID = isset($_POST['fromID']) ? $_POST['fromID'] : '';
    $toID = isset($_POST['toID']) ? $_POST['toID'] : '';
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
    年份: <input type="text" id="year" name="year" style="width:100px;" value="<?php echo $year; ?>">&nbsp;&nbsp;&nbsp;&nbsp;
    起始ID: <input type="text" id="fromID" name="fromID" style="width:100px;" value="<?php echo $fromID; ?>">
    終止ID: <input type="text" id="toID" name="toID" style="width:100px;" value="<?php echo $toID; ?>">
    <button type="submit">SEND</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <button type="button" onClick="window.location.href ='http://localhost:8888/commoption.php';">RESET</button>
    <button type="button" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
</form>
<hr>
    
<?php
if(empty($year) && empty($fromID) && empty($toID)){ echo "尚未設定年份,或起始結束ID段！";exit(); }
if(!empty($year) && ($year<101 || $year>112)){ echo "年份錯誤！";exit(); }
if(empty($year) && (empty($fromID) || empty($toID))){ echo "尚未設定起始結束ID段！";exit(); }


$folderr="101";
$folderr2="0";
// 取得資料 (欄位名稱 and 欄位值)
$csv_folderpath = "aprretCSV/";

$sql = "SELECT * FROM filelist";
if(!empty($year)) { $sql .= " WHERE f_year='".$year."'"; }
elseif(!empty($fromID) && !empty($toID)) { $sql .= " WHERE f_id > ".$fromID." AND f_id <= ".$toID; }
//$sql .= " WHERE f_year IN ('".$folderr."','".$folderr2."')";
//$sql .= " LIMIT 1";
$result = mysqli_query($link, $sql);
mysqli_data_seek($result, 0);  // 指標移到第一筆資料
$chkSameFile = '';
$chkSameOpt=0;
$chkDoneOpt=0;
$cTotalRowNum=0;
$getBR = "";
while( $row = mysqli_fetch_assoc($result)){
    $csv_subfolder = $csv_folderpath.$row['f_year'].'/';

    $highestRow = $row['f_rows']; //總列數
    $startRow = $row['f_startrow']+1;//起始列數
    $totalRows = $highestRow - $startRow;
    //echo $totalRows.'<br>';
    //$cTotalRowNum += $totalRows;
    //--------------------------------//commcheck//layercheck
    $getCity = $row['f_city'];
    $getDist = $row['f_district'];
    //--------------------------------
    if($chkSameFile != $row['f_name']){
        $getBR = "<br><br>";
        $chkSameFile = $row['f_name'];
    } 
    $excel= PHPExcel_IOFactory::load($csv_subfolder.$row['f_name']); 
    $sheet=$excel->getSheet(0);
    for ($ri = $startRow; $ri <= $highestRow; $ri++) {
        $cTotalRowNum++;
        $getCommStr = trim($sheet->getCellByColumnAndRow(1, $ri)->getValue());
        if(empty($getCommStr)){ continue; }
        $getCommStr = addslashes($getCommStr);
        //-----------------------------------
        $res = mysqli_query($link, "SELECT * FROM data_conproject WHERE conp_city='".$getCity."' AND conp_district='".$getDist."' AND conp_title='".$getCommStr."'");
        $row_cnt = mysqli_num_rows($res);
        if($row_cnt>0) {
            $chkSameOpt++;
            continue;
        } else {
        //--------------------------------
            $sqlc = "INSERT INTO data_conproject (conp_city, conp_district, conp_title, conp_mode, conp_check, conp_status) VALUES ('".$getCity."', '".$getDist."', '".$getCommStr."', 1, 4, 1)";
            $resultc = mysqli_query($link, $sqlc);
            if($resultc){
                $chkDoneOpt++;
                //echo $csv_subfolder.$row['f_name'].'@@ B'.$ri.' @@ '.$getCity.'/'.$getDist.'/'.$getCommStr.'<br>';
            } 
        }
    }
}
if(!empty($year)) { echo "<hr><br>Folder: ".$year."/ Total Row Numbers: ".$cTotalRowNum."<br>"; }
elseif(!empty($fromID) && !empty($toID)) { echo "<hr><br>ID : ".($fromID+1)."~".$toID." Total Row Numbers: ".$cTotalRowNum."<br>"; }
echo "<hr><br>Opt Checked: ".$chkSameOpt."<br><br>";
echo "<hr><br>Opt Doned: ".$chkDoneOpt."<br><br><br>";

mysqli_close($link);

?>
</div>
</body>
</html>