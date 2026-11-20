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
/* 
//Count Files & Rows
$sql = "select SUM(ff_filenum) AS totalfiles,SUM(ff_rownum) AS totalRows FROM ffcount";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
echo "Total Files: ".$row['totalfiles']."<br>";
echo "Total Rows: ".$row['totalRows']."<br>";
*/

$sqla = "select fo_text FROM flooropt";
$resulta = mysqli_query($link, $sqla);
mysqli_data_seek($resulta, 0);  // 指標移到第一筆資料
echo "INSERT INTO flooropt VALUES ";
while( $rowa = mysqli_fetch_assoc($resulta)){
    echo "(null, '".$rowa['fo_text']."',''),<br>";
}
//INSERT INTO flooropt VALUES (null, '門廳',''),(null, '走廊',''),(null, '通道',''),(null, '陽台',''),(null, '陽臺',''),(null, '平台',''),(null, '騎樓',''),(null, '夾層',''),(null, '露台',''),(null, '花台',''),(null, '雨遮',''),(null, '水箱',''),(null, '水塔',''),(null, '浴廁',''),(null, '倉庫',''),(null, '儲藏室',''),(null, '車庫',''),(null, '機械房',''),(null, '瞭望台',''),(null, '瞭望室',''),(null, '變電室',''),(null, '管理員室（警衛室）',''),(null, '電梯樓梯間',''),(null, '屋頂突出物',''),(null, '防空避難室',''),(null, '避難室兼停車場',''),(null, '停車場',''),(null, '見使用執照',''),(null, '見其他登記事項',''),(null, '見其它登記事項','')
exit();


$folderr="111";
$folderr2="112";
$sqla = "select fo_text FROM flooropt";
$resulta = mysqli_query($link, $sqla);
$frArr = array();
$ia=0;
while( $rowa = mysqli_fetch_assoc($resulta)){
    $frArr[$ia]=$rowa['fo_text'];
    $ia++;
}

// 取得資料 (欄位名稱 and 欄位值)
$csv_folderpath = "aprretCSV/";

$sql = "SELECT * FROM filelist";
$sql .= " WHERE f_year='".$folderr."'";
//$sql .= " LIMIT 1";
$result = mysqli_query($link, $sql);
mysqli_data_seek($result, 0);  // 指標移到第一筆資料
$chkSameFile = '';
$chkSameOpt=0;
$chkDoneOpt=0;
$cTotalRowNum=0;
$getBR = "";
while( $row = mysqli_fetch_assoc($result)){
    //foreach( $row as $item=>$val ) {// $item 會是 Column Name
        //echo $item . " = " . $val . "<br>";
    //}
    //-------- 取得基本參數 -------------------------------
    $csv_subfolder = $csv_folderpath.$row['f_year'].'/';//取得檔案路徑
    $highestRow = $row['f_rows'];           //總列數
    $startRow = $row['f_startrow']+1;       //起始列數
    $totalRows = $highestRow - $startRow;   //實機有效列數
    $cTotalRowNum += $totalRows;            //累積每個檔案的有效列數總和
    $getCity = $row['f_city'];              //取得城市代碼
    $getDist = $row['f_district'];          //取得區域代碼
    //--------- 載入檔案 ----------------------------------
    $EXL= PHPExcel_IOFactory::load($csv_subfolder.$row['f_name']); 
    $SHEET=$EXL->getSheet(0);
    /*
    if($chkSameFile != $row['f_name']){
        $getBR = "<br><br>";
        $chkSameFile = $row['f_name'];
    } 
    */
    //-------------------- 開始每個檔案中每筆紀錄一一表列 -----------------------
    for ($rowNum = $startRow; $rowNum <= $highestRow; $rowNum++) {//自CSV/XLS檔案中第 $startRow 行過後開始讀取


        //A?



        $getFloorStr = trim($SHEET->getCellByColumnAndRow(9, $rowNum)->getValue()); //取得樓層欄位內容




        if(empty($getFloorStr)){ continue; }
        $getFloor=strchr($getFloorStr,"/",true);
        $getFloorArr = explode(',',$getFloor);


        //-----------------------------------
        /*
        if(count($getFloorArr)==0){ continue; }
        else if(count($getFloorArr)==1){
            if($getFloorArr[0]!=''){ continue; }
            $getNFloor=trim($SHEET->getCellByColumnAndRow(0, $rowNum)->getValue());
            if (preg_match('/號(.*?)樓/', $getNFloor, $match) == 1) {
                $getFloorFix = numToWord(_fn_str_f2h($match[1])).'層'.$getFloorStr;
            } else {
                //if(strpos($getNFloor, '地下') !== false){ $getFloorFix = "地下一層".$getFloorStr; }
                //else { $getFloorFix = "一層".$getFloorStr; }
                $getFloorFix = '';
            }
        } else {
            continue;
        }
        echo $getBR;
        $getBR = "";
        echo $csv_subfolder.$row['f_name'].'   @@ J'.$rowNum.'   :   '.$getFloorFix.'<br>';
        */
        $count =count($getFloorArr);
        if($count==0){ continue; }
        else if($count==1){
            if($getFloorArr[0]=='全'||$getFloorArr[0]==''){ continue; }
            //else{
                if (strpos($getFloorArr[0], '層') != false){
                    if(strpos($getFloorArr[0], '地下') == false){ continue; }
                }
            //}
            if(in_array($getFloorArr[0],$frArr)){ 
                //$chkSameOpt++;
                continue; 
            }
            
            //--------------------------------
            $sqlc = "insert into flooropt values(null, '".$getFloorArr[0]."', '')";
            $resultc = mysqli_query($link, $sqlc);
            if($resultc){
                //echo "1@".$getFloorArr[0]."<br>";
                array_push($frArr, $getFloor);
                $chkDoneOpt++;
                echo $csv_subfolder.$row['f_name'].'@@'.$rowNum.':'.$count.':'.$getFloor.'<br>';
             }        
        } else {
            foreach($getFloorArr as $ot){
                if(strpos($ot, '層') != false){ continue; }
                if(in_array($ot,$frArr)){
                    $chkSameOpt++;
                    continue;
                }
                
                //--------------------------------
                $sqld = "insert into flooropt values(null, '".$ot."', '')";
                $resultd = mysqli_query($link, $sqld);
                if($resultd){
                    //echo ">1@".$ot."<br>";
                    array_push($frArr, $ot);
                    $chkDoneOpt++;
                    echo $csv_subfolder.$row['f_name'].'@@'.$rowNum.':'.$count.':'.$ot.'<br>';
                }
                
            }
        }
        
    }//for
    /*
    $sql = "INSERT INTO filelist VALUES(null, $sf, $getCity, $getDist, '".$f."', $startRow, $highestRow, 0, '')";
    $result = mysqli_query($link, $sql);
    */


}
echo "<hr><br>Folder: ".$folderr."/".$folderr2." Total Row Numbers: ".$cTotalRowNum."<br>";
echo "<hr><br>Opt Checked: ".$chkSameOpt."<br><br><br><br>";
echo "<hr><br>Opt Doned: ".$chkDoneOpt."<br><br><br><br>";


mysqli_close($link);

?>
</div>
</body>
</html>