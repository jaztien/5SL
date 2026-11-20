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
    $cityCode = isset($_POST['cityCode']) ? $_POST['cityCode'] : '';
    $fromYear = isset($_POST['fromYear']) ? $_POST['fromYear'] : '';
    $toYear = isset($_POST['toYear']) ? $_POST['toYear'] : '';
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
<?php
/*
        $sql = "SELECT * FROM `data_distsplit_17`";
        $sql .= " WHERE dt_distcode='1701' AND dt_year='108'";
        //$sql .= " ORDER BY dt_C ASC";
        $cStartRow = 4;
        $cTotalRows = 0;
        $result = mysqli_query($link, $sql);
        $row_cnt = mysqli_num_rows($result);
        mysqli_data_seek($result, 0);  // 指標移到第一筆資料
$count=0;
while($bcrow = mysqli_fetch_assoc($result)){
    $count++;

}
echo $row_cnt .' :::: '. $count;

exit();
*/
?>
<hr>
<div style="font-size:16px;line-height:20px;">
<form id="form_readtofile" name="form_readtofile" action="#" method="post">
    <div style="display:inline-block;vertical-align:top;">
        城市碼: <input type="text" id="cityCode" name="cityCode" style="width:100px;" value="<?php echo $cityCode; ?>"><br>
        起始年: <input type="text" id="fromYear" name="fromYear" style="width:100px;" value="<?php echo $fromYear; ?>"><br>
        終止年: <input type="text" id="toYear" name="toYear" style="width:100px;" value="<?php echo $toYear; ?>">
    </div>
    <div style="display:inline-block;vertical-align:top;margin-left:12px;">
        <button type="submit" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">SEND</button>
        <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/17_32_DBtoExcel.php';">RESET</button>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
        <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
    </div>
</form>
<hr>
   
<?php
if(empty($fromYear) || empty($toYear)){ echo "尚未設定最大截取值，或是起始結束ID段！";exit(); }
$cCity = $cityCode;//17|32
$cStartYear = $fromYear;
$cEndYear = $toYear;
$codeArr = array(
        '17' =>[
            array("code"=>"1700","text"=>"無區"),
            array("code"=>"1701","text"=>"東區"),
            array("code"=>"1702","text"=>"北區"),
            array("code"=>"1703","text"=>"香山區")
        ],
        '32' =>[
            array("code"=>"3200","text"=>"無區"),
            array("code"=>"3201","text"=>"東區"),
            array("code"=>"3202","text"=>"西區")
        ]
    );

$textArr = array(
    "1700"=>"無區",
    "1701"=>"東區",
    "1702"=>"北區",
    "1703"=>"香山區",
    "3200"=>"無區",
    "3201"=>"東區",
    "3202"=>"西區"
);

$cStart = $codeArr[$cCity][0]['code'];
$cEnd = $codeArr[$cCity][count($codeArr[$cCity])-1]['code'];//
$cTotalFolder = ($cEndYear - $cStartYear) + 1;
$cFileFolder = 'aprretFiles/Original/20240416/'.$cCity;
//echo $cStart."-".$cEnd;
//print_r($codeArr);
//exit();
for($di=$cStart;$di<=$cEnd;$di++) {
    for($yi=$cStartYear;$yi<=$cEndYear;$yi++) {
        $sql = "SELECT * FROM `data_distsplit_".$cCity."`";
        $sql .= " WHERE dt_distcode='".$di."' AND dt_year='".$yi."'";
        $sql .= " ORDER BY dt_C DESC";
        //$sql .= " LIMIT 0,10";
        $cStartRow = 4;
        $cTotalRows = 0;
        $result = mysqli_query($link, $sql);
        $row_cnt = mysqli_num_rows($result);
        mysqli_data_seek($result, 0);  // 指標移到第一筆資料

        if(!$row_cnt || $row_cnt<=0){ continue; }

        $cFilePath = $cFileFolder.'/'.$di.'/';
        if (!is_dir($cFilePath)) { mkdir($cFilePath, 0777, true); }

        $cFileName = $cFilePath.$cCity.'_'.$di.'_'.$yi.'.01-'.$yi.'.12.xls';


        $objPHPExcel = new PHPExcel();                     //實例化一個PHPExcel()對象
        $objSHEET = $objPHPExcel->getActiveSheet();        //選取當前的sheet對象
        $objSHEET->setCellValueByColumnAndRow(0, 1,'買賣案件:嘉義市'.$textArr[$di].'房地(土地+建物),房地(土地+建物)+車位,建物,車位,土地,'.$yi.' 年 1 月 至'.$yi.' 年 12 月')
            ->setCellValueByColumnAndRow(1, 1,$row_cnt)
            ->setCellValueByColumnAndRow(2, 1,'')
            ->setCellValueByColumnAndRow(3, 1,'')
            ->setCellValueByColumnAndRow(4, 1,'')
            ->setCellValueByColumnAndRow(5, 1,'')
            ->setCellValueByColumnAndRow(6, 1,'')
            ->setCellValueByColumnAndRow(7, 1,'')
            ->setCellValueByColumnAndRow(8, 1,'') 
            ->setCellValueByColumnAndRow(9, 1,'')
            ->setCellValueByColumnAndRow(10, 1,'')  
            ->setCellValueByColumnAndRow(11, 1,'')
            ->setCellValueByColumnAndRow(12, 1,'')  
            ->setCellValueByColumnAndRow(13, 1,'') 
            ->setCellValueByColumnAndRow(14, 1,'')
            ->setCellValueByColumnAndRow(15, 1,'')
            ->setCellValueByColumnAndRow(16, 1,'')
            ->setCellValueByColumnAndRow(17, 1,'')
            ->setCellValueByColumnAndRow(0, 2,'')
            ->setCellValueByColumnAndRow(1, 2,'')
            ->setCellValueByColumnAndRow(2, 2,'')
            ->setCellValueByColumnAndRow(3, 2,'')
            ->setCellValueByColumnAndRow(4, 2,'單價')
            ->setCellValueByColumnAndRow(5, 2,'總面積')
            ->setCellValueByColumnAndRow(6, 2,'')
            ->setCellValueByColumnAndRow(7, 2,'')
            ->setCellValueByColumnAndRow(8, 2,'') 
            ->setCellValueByColumnAndRow(9, 2,'')
            ->setCellValueByColumnAndRow(10, 2,'')  
            ->setCellValueByColumnAndRow(11, 2,'')
            ->setCellValueByColumnAndRow(12, 2,'')  
            ->setCellValueByColumnAndRow(13, 2,'車位總價') 
            ->setCellValueByColumnAndRow(14, 2,'')
            ->setCellValueByColumnAndRow(15, 2,'')
            ->setCellValueByColumnAndRow(16, 2,'')
            ->setCellValueByColumnAndRow(17, 2,'')
            ->setCellValueByColumnAndRow(0, 3,'地段位置或門牌')
            ->setCellValueByColumnAndRow(1, 3,'社區簡稱')
            ->setCellValueByColumnAndRow(2, 3,'交易日期')
            ->setCellValueByColumnAndRow(3, 3,'總價(萬元)')
            ->setCellValueByColumnAndRow(4, 3,'(萬元/坪)')
            ->setCellValueByColumnAndRow(5, 3,'(坪)')
            ->setCellValueByColumnAndRow(6, 3,'主建物佔比')
            ->setCellValueByColumnAndRow(7, 3,'型態')
            ->setCellValueByColumnAndRow(8, 3,'屋齡') 
            ->setCellValueByColumnAndRow(9, 3,'樓別/樓高')
            ->setCellValueByColumnAndRow(10, 3,'交易標的')  
            ->setCellValueByColumnAndRow(11, 3,'交易筆棟數')
            ->setCellValueByColumnAndRow(12, 3,'建物現況格局')  
            ->setCellValueByColumnAndRow(13, 3,'(萬元)') 
            ->setCellValueByColumnAndRow(14, 3,'管理組織')
            ->setCellValueByColumnAndRow(15, 3,'電梯')
            ->setCellValueByColumnAndRow(16, 3,'主要用途')
            ->setCellValueByColumnAndRow(17, 3,'備註')
            ->setCellValueByColumnAndRow(18, 3,'系統備註');
        $cStartRow = 4;
        //$cTotalRows = $row_cnt;
        $testnun = 0;
        while($bcrow = mysqli_fetch_assoc($result)){
    
            $objSHEET->setCellValueByColumnAndRow(0, $cStartRow,$bcrow['dt_A'])
                    ->setCellValueByColumnAndRow(1, $cStartRow,$bcrow['dt_B'])
                    ->setCellValueByColumnAndRow(2, $cStartRow,$bcrow['dt_C'])
                    ->setCellValueByColumnAndRow(3, $cStartRow,$bcrow['dt_D'])
                    ->setCellValueByColumnAndRow(4, $cStartRow,$bcrow['dt_E'])
                    ->setCellValueByColumnAndRow(5, $cStartRow,$bcrow['dt_F'])
                    ->setCellValueByColumnAndRow(6, $cStartRow,$bcrow['dt_G'])
                    ->setCellValueByColumnAndRow(7, $cStartRow,$bcrow['dt_H'])
                    ->setCellValueByColumnAndRow(8, $cStartRow,$bcrow['dt_I']) 
                    ->setCellValueByColumnAndRow(9, $cStartRow,$bcrow['dt_J'])
                    ->setCellValueByColumnAndRow(10, $cStartRow,$bcrow['dt_K'])  
                    ->setCellValueByColumnAndRow(11, $cStartRow,$bcrow['dt_L'])
                    ->setCellValueByColumnAndRow(12, $cStartRow,$bcrow['dt_M'])  
                    ->setCellValueByColumnAndRow(13, $cStartRow,$bcrow['dt_N']) 
                    ->setCellValueByColumnAndRow(14, $cStartRow,$bcrow['dt_O'])
                    ->setCellValueByColumnAndRow(15, $cStartRow,$bcrow['dt_P'])
                    ->setCellValueByColumnAndRow(16, $cStartRow,$bcrow['dt_Q'])
                    ->setCellValueByColumnAndRow(17, $cStartRow,$bcrow['dt_R'])
                    ->setCellValueByColumnAndRow(18, $cStartRow,'ID:'.$bcrow['dt_id']);
                    $testnun++;
                    $cStartRow++;
                    $cTotalRows++;       
                    //if($testnun<=10){ continue; }
                    //else { break; }
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //$objWriter->setUseBOM(true);//中文亂碼處理
        $objWriter->save($cFileName);//保存文件
        echo $cFileName.'__ '.$cTotalRows.' Rows <br>';

        //$cTotalFolder++;
    }

}
echo '<hr>';
echo 'Total Rows: '.$cTotalFolder;
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
<audio autoplay='true' style='display:none;'><source src='sounds/theetone.wav'></audio>

</body>
</html>

