<?php
/////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////// 實價登錄資料處理——檢查比對資料內區域與檔案名稱區域是否相符  ///////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////
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

    $locJS=_fn_getCachedJsonData("locations.json");
    //------------------------------------------
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	
  <title> 人生第五季 Fifth Season Life||實價登錄資料處理——檢查比對資料內區域與檔案名稱區域是否相符 </title>
  </head>
    <body style="margin:10px;">
        <div id="loading" style="top:0;left:0;position:absolute;display:block;width:100%;height:100%;;background-image: url('loading.gif');background-color:#2A2826;background-repeat: no-repeat;background-position: 50% 0;background-size:contain;opacity: 0.75;margin:0;text-align: center; z-index:99;">
            <div style="color:#FFFFFF;position: relative;top: 50%;transform: translate(0, -50%);">
                <div id="loadingtext" style="font-size:24px;">正在生成檔案資料表......</div>
                <div><button id="loopbut" style="height:32px;width:120px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/aprret_03_filelistToDB.php';">終止操作</button></div>
            </div>
        </div>
        <h2>實價登錄資料處理——檢查比對資料內區域與檔案名稱區域是否相符</h2>
        <hr>
        <div style="font-size:16px;line-height:20px;">
            
        <?php
        //--------------------------------
        $fromID = 5000;
        $sql = "SELECT * FROM filelist";
        $sql .= " LIMIT $fromID, 1000";
        $result = mysqli_query($link, $sql);
        mysqli_data_seek($result, 0);  // 指標移到第一筆資料
        $ptn_filename = "/(^\d{2}\_)\d{4}\_\d{3}\.\d{2}\-\d{3}\.\d{2}\.xls$/";
        $ptn_filename2 = "/(^\d{2}\_)\d{4}\_\d{3}\.\d{2}\.xls$/";
        $ptn_uptitle = '/(買賣案件:)(.*?)(房地|建物|車位|土地)/';
        while($flist = mysqli_fetch_assoc($result)){

            if(!preg_match($ptn_filename, $flist['f_name'])) {
                if(!preg_match($ptn_filename2, $flist['f_name'])) {
                    echo "Error 0 @@ ".$flist['f_name']." @@ 檔案丟失或檔名格式錯誤！ @@ <br>";
                    continue;
                }
            }

            if($flist['f_district']=='3436'){ continue; }
            $setCityText = !empty($flist['f_city']) ? $locJS[$flist['f_city']]['text'] : '';
            $setDistText = !empty($flist['f_city'])&&!empty($flist['f_district']) ? $locJS[$flist['f_city']]['district'][$flist['f_district']] : '';
            $fullarea = $setCityText.$setDistText;

            $csv_folderpath = "aprretFiles/CSV_single/".$flist['f_year']."/";//aprretCSV
            $excel= PHPExcel_IOFactory::load($csv_folderpath.$flist['f_name']); 
            $sheet=$excel->getSheet(0);
            $highestRow = $flist['f_rows']; // 取得總列數
            $startRow = $flist['f_startrow'];
            $totalRows = $flist['f_datarows'];
            //--------------------------------  來自 檔名
            $nameSplit = explode('_',$flist['f_name']);
            $pureDate = str_replace('.csv','',$nameSplit[2]);
            if(strpos($pureDate,'-')==false){ $pureDate = $pureDate.'-'.$pureDate; }
            $equalA = trim($fullarea.$pureDate);
            //-------------------------------- 來自 內容
            $getUpTitle = $sheet->getCell("A1");
            $getUpTitle = str_replace(' ','',$getUpTitle);
            if(
                strpos($getUpTitle,"房地(土地+建物),")!==false &&
                strpos($getUpTitle,",房地(土地+建物)+車位,")!==false &&
                strpos($getUpTitle,",建物,")!==false &&
                strpos($getUpTitle,",車位,")!==false &&
                strpos($getUpTitle,",土地,")!==false
            ){ 
                echo "<span style='color:green;'>比對型態選項通過！ @@ ".$flist['f_name']." @@</span><br>";
            }else {
                echo "Error @@ ".$flist['f_id']." @@ ".$flist['f_name']." @@ ".$getUpTitle."<br>";
                continue;
            }

            preg_match($ptn_uptitle, $getUpTitle, $matches);
            $fullTitle = str_replace(['買賣案件:','房地','建物','車位','土地'],'',$matches[0]);
            $fullTitle =  str_replace('臺','台',$fullTitle);
            //$getUpPeriod = explode('土地,',@$getUpTitleSplit[1]);
            //--------------------------------
            $getUpPeriod = mb_substr(mb_strrchr($getUpTitle, ','), 1);
            $getPeriod = str_replace('年','.',$getUpPeriod);
            $getPeriod = str_replace('月至','-',$getPeriod);
            $getPeriod = str_replace('月','',$getPeriod);
            $getDateSplit = explode('-',$getPeriod);
            $getFromSplit = explode('.',@$getDateSplit[0]);
            $getToSplit = explode('.',@$getDateSplit[1]);
            $getFrom = @(int)$getFromSplit[1] < 10 ? '0'.@$getFromSplit[1] : @$getFromSplit[1];
            $getTo = @(int)$getToSplit[1] < 10 ? '0'.@$getToSplit[1] : @$getToSplit[1];
            $fullperiod = @$getFromSplit[0].'.'.$getFrom.'-'.$getToSplit[0].'.'.$getTo;
            $equalB = trim($fullTitle.$fullperiod);
            //--------------------------------
            if($equalA != $equalB){
                echo "Error 2 @@ ".$flist['f_name']." @@ ".$equalA."!==".$equalB."<br>";
            }else{
                echo "<span style='color:green;'>比對地區&時段通過！ @@ ".$flist['f_name']." @@</span><br>";
            }
            
            //--------------------------------

        }
        //----------------------------------
        echo "<hr>";
        echo "處理筆數： 自 ".$fromID." 至 ".($fromID+1000);
        echo "<script>window.document.getElementById('loading').style.display = 'none';</script>";
        mysqli_close($link);

        ?>
        </div>
    </body>
</html>

