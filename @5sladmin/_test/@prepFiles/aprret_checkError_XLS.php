<?php
/////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////// 實價登錄資料處理——第三步.建立工作檔案資料表  ///////////////////////////
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
	
  <title> 人生第五季 Fifth Season Life||實價登錄資料處理——第三步.建立工作檔案資料表 </title>
  </head>
    <body style="margin:10px;">
        <div id="loading" style="top:0;left:0;position:absolute;display:block;width:100%;height:100%;;background-image: url('loading.gif');background-color:#2A2826;background-repeat: no-repeat;background-position: 50% 0;background-size:contain;opacity: 0.75;margin:0;text-align: center; z-index:99;">
            <div style="color:#FFFFFF;position: relative;top: 50%;transform: translate(0, -50%);">
                <div id="loadingtext" style="font-size:24px;">正在生成檔案資料表......</div>
                <div><button id="loopbut" style="height:32px;width:120px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">終止操作</button></div>
            </div>
        </div>
        <h2>實價登錄資料處理——檢查比對資料內區域與檔案名稱區域是否相符</h2>
        <hr>
        <div style="font-size:16px;line-height:20px;">
            
        <?php
        //--------------------------------
        $xls_folderpath = "aprretFiles/Original/20240421/";
        $ptn_filename = "/(^\d{2}\_)\d{4}\_\d{3}\.\d{2}\-\d{3}\.\d{2}\.xls$/";
        $ptn_filename2 = "/(^\d{2}\_)\d{4}\_\d{3}\.\d{2}\.xls$/";
        $ptn_uptitle = '/(買賣案件:)(.*?)(房地|建物|車位|土地)/';
        $cFolder = opendir($xls_folderpath); {
            if ($cFolder) {
                $totalNum = 0;
                $ctotalNum = 0;
                while (($xls_filename = readdir($cFolder)) !== FALSE) {
                    if ($xls_filename != '.' && $xls_filename != '..' && $xls_filename != '.DS_Store') {

                        if(!preg_match($ptn_filename, $xls_filename)) {
                            if(!preg_match($ptn_filename2, $xls_filename)) {
                                echo "Error 0 @@ ".$xls_filename." @@ 檔案丟失或檔名格式錯誤！ @@ <br>";
                                continue;
                            }
                        }

                        $excel= PHPExcel_IOFactory::load($xls_folderpath.$xls_filename); 
                        $sheet=$excel->getSheet(0);
                        $highestRow = $sheet->getHighestRow(); // 取得總列數
                        //--------------------------------  來自 檔名
                        $nameSplit = explode('_',$xls_filename);

                        if($nameSplit[1]=='1700' || $nameSplit[1]=='3200'){
                            $setCityText = $nameSplit[1]=='1700' ? '新竹市' : '嘉義市';
                            $setDistText = $nameSplit[1]=='1700' ? '新竹市' : '嘉義市';
                        }else{
                            $setCityText = !empty($nameSplit[0]) ? $locJS[$nameSplit[0]]['text'] : '';
                            $setDistText = !empty($nameSplit[0])&&!empty($nameSplit[1]) ? $locJS[$nameSplit[0]]['district'][$nameSplit[1]] : '';
                        }
                        $fullarea = $setCityText.$setDistText;
                        $pureDate = str_replace('.xls','',$nameSplit[2]);
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
                            //echo "<span style='color:green;'>比對型態選項通過！ @@ ".$xls_filename." @@</span><br>";
                            //continue;
                        }
                        else {
                            echo "Error 1 @@ ".$highestRow." @@ ".$xls_filename." @@ ".$getUpTitle."<br>";
                            continue;
                        }

                        preg_match($ptn_uptitle, $getUpTitle, $matches);
                        $fullTitle = str_replace(['買賣案件:','房地','建物','車位','土地'],'',$matches[0]);
                        $fullTitle =  str_replace('臺','台',$fullTitle);
                        /*
                        房地(土地+建物),房地(土地+建物)+車位,建物,車位,土地,
                        $getUpTitle = str_replace(['買賣案件:',' '],'',$getUpTitle);
                        $getUpTitle = str_replace('臺','台',$getUpTitle);//房地(土地+建物),房地(土地+建物)+車位,建物,車位,土地
                        //$getUpTitleSplit = explode('房地(',$getUpTitle);
                        $getUpCity = mb_substr($getUpTitle,0,3);
                        $getUpTitle = str_replace($getUpCity,'',$getUpTitle);
                        if(mb_strpos($getUpTitle, '區')!==false){ $getUpDistText = '區'; }
                        elseif(mb_strpos($getUpTitle, '鄉')!==false){ $getUpDistText = '鄉'; }
                        elseif(mb_strpos($getUpTitle, '鎮')!==false){ $getUpDistText = '鎮'; }
                        elseif(mb_strpos($getUpTitle, '市')!==false){ $getUpDistText = '市'; }
                        $getUpDist = mb_substr($getUpTitle,0,mb_strpos($getUpTitle, $getUpDistText)+1);
                        //$fullTitle = strchr($getUpTitle,"房地(",true);
                        $fullTitle = $getUpCity.$getUpDist;
                        $getUpPeriod = mb_substr(mb_strrchr($getUpTitle, ','), 1);
                        //$getUpPeriod = explode('土地,',@$getUpTitleSplit[1]);
                        */
                        //--------------------------------
                        //$getPeriodSplit = explode(',',$getUpTitle);
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
                            echo "Error 2 @@ ".$xls_filename." @@ ".$equalA."!==".$equalB."<br>";
                        }else{
                            echo "<span style='color:green;'>比對地區&時段通過！ @@ ".$xls_filename." @@</span><br>";
                        }
                        
                        //--------------------------------
                    }
                }
            }

        }
        //----------------------------------
        echo "<hr>";
        //echo "處理筆數： 自 ".$fromID." 至 ".($fromID+1000);
        echo "<script>window.document.getElementById('loading').style.display = 'none';</script>";
        mysqli_close($link);

        ?>
        </div>
    </body>
</html>

