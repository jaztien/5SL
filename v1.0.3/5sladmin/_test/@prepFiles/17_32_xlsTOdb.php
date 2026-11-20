<?php
//////////////////////////////////////////////////////////////////////////////
///////////////////////// 未分區紀錄寫入工作資料表 ///////////////////////////////
//////////////////////////////////////////////////////////////////////////////
error_reporting(E_ALL); 
	include 'aprret_func.php';
	include 'PHPExcel/Classes/PHPExcel.php';
    header("Content-Type:text/html; charset=utf-8");
    $host = 'localhost';
    $dbuser ='root';
    $dbpassword = '1111';
    $dbname = 'jazhome';

    $link = mysqli_connect($host,$dbuser,$dbpassword,$dbname);
    if($link){
        mysqli_query($link,'SET NAMES utf8');
        //echo "正確連接資料庫";
    }
    else {
        //echo "不正確連接資料庫</br>" . mysqli_connect_error();
    }
    $startTime = time();
    $doact = isset($_POST['doact']) ? $_POST['doact'] : '';
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

<div style="font-size:16px;line-height:20px;">
<form id="form_readtofile" name="form_readtofile" action="#" method="post">
    <input type="hidden" id="doact" name="doact" value="1">
    <button type="submit" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">執行</button>
    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/17_32_xlsTOdb.php';">RESET</button>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
</form>
<hr>

<?php
    $checkLoading = 1;
    if(empty($doact)){ echo " /請按「執行」按鈕進行操作！！"; $checkLoading = 0; }
    if(empty($checkLoading)){
        //echo "<script>window.parent.document.getElementById('loading').style.display = 'none';</script>";
        exit();
    }


    $basefolder = "aprretFiles/Original/20240416/";
    
    $cityCode = "17";
    $subfolder = $cityCode."/1701";
    $cityname = "新竹市";
    $codeArr = array("東區"=>"1701","北區"=>"1702","香山區"=>"1703");
    $replace = "新竹市";
    /*
    $cityCode = "32";
    $subfolder = $cityCode."/3201";
    $cityname = "嘉義市";
    $codeArr = array("東區"=>"3201","西區"=>"3202");
    $replace=['嘉義市','嘉義巿'];
   */
//１路２路３路４路５路６路７路８路９路１０路。一樓 二路 三路 四路 五路 六路 七路 八路 九路 十路 
///////////////////////////////////////////////////////////////////////
        $cTotalFiles=0;
        $cTotalRowNum=0;
        $cTotalDupRows=0;
        $cTotaXLSRows=0;
        $cErrorCount=0;
        $source_folderpath=$basefolder.$subfolder;
        if (is_dir($source_folderpath)) {
            $cfile = opendir($source_folderpath); {
                if ($cfile) {
                    $cTotalFiles=0;
                    $i=0;
                    while (($filename = readdir($cfile)) !== FALSE) {
                        if ($filename != '.' && $filename != '..' && $filename != '.DS_Store' && $filename != 'Icon?') {
                            $EXL= PHPExcel_IOFactory::load($source_folderpath.'/'.$filename); 
                            $SHEET=$EXL->getSheet(0);
                            $startRow = $SHEET->getCell("E2")=='單價'? 4 : 3;
                            $endRow = $SHEET->getHighestRow(); // 取得總列數 
                            $xlsRows = ($endRow - $startRow) + 1;
                            $cTotaXLSRows += $xlsRows;
                            $fi=0;     
                            for ($rowNum = $startRow; $rowNum <= $endRow; $rowNum++) {
                                $rowTypeFrom = $SHEET->getCellByColumnAndRow(10, $rowNum)->getValue();
                                //if($rowTypeFrom=='土地'){ continue; }
                                $rowNameFrom = trim($SHEET->getCellByColumnAndRow(0, $rowNum)->getValue());
                                $rowName = $rowTypeFrom=='土地' ? $rowNameFrom : strchr($rowNameFrom,'號',true).'號';
                                if(mb_strpos($rowNameFrom,'鄰')!==false){
                                    $rowNameFrom = mb_substr(mb_strrchr($rowNameFrom,'鄰'),1);
                                }
                                
                                $rowNameFrom = str_replace($replace,'',$rowNameFrom);//注意！！兩個嘉義市不同！！（不明原因                            ）
                                //$hName = str_replace($cityname,'',$hName);
                                $hName = _fn_str_f2h($rowNameFrom);

                                if($rowTypeFrom=='土地'){
                                    $secNum = mb_substr_count($hName,'段');
                                    if($secNum==1){
                                        $pureaddr = strchr($hName,'段',true).'段';
                                    }elseif($secNum==2){
                                        $pureaddrSplit = explode('段',$hName);
                                        $pureaddr = $pureaddrSplit[0].'段'.$pureaddrSplit[1].'段';
                                    }

                                } else {
                                    if(strpos($hName,'段')!==false){
                                        $pureaddr = strchr($hName,'段',true).'段';
                                        $pureaddr = _fn_num_2sect($pureaddr);
                                        $addrSplit = explode('里',$pureaddr);
                                        if(count($addrSplit)>1) $pureaddr = @$addrSplit[1];
                                    } else {
                                        if(strpos($hName,'路')!==false){
                                            $pureaddr = strchr($hName,'路',true).'路';
                                            $pureaddr = _fn_num_2sect($pureaddr);
                                            $addrSplit = explode('里',$pureaddr);
                                            if(count($addrSplit)>1) $pureaddr = @$addrSplit[1];
                                        }elseif(strpos($hName,'街')!==false){
                                            $pureaddr = strchr($hName,'街',true).'街';
                                            $pureaddr = _fn_num_2sect($pureaddr);
                                            $addrSplit = explode('里',$pureaddr);
                                            if(count($addrSplit)>1) $pureaddr = @$addrSplit[1];
                                        }elseif(strpos($hName,'村')!==false){
                                            $pureaddr = strchr($hName,'村',true).'村';
                                            $pureaddr = _fn_num_2sect($pureaddr);
                                            $addrSplit = explode('里',$pureaddr);
                                            if(count($addrSplit)>1) $pureaddr = @$addrSplit[1];
                                        }elseif(strpos($hName,'場')!==false){
                                            $pureaddr = strchr($hName,'場',true).'場';
                                            $pureaddr = _fn_num_2sect($pureaddr);
                                            $addrSplit = explode('里',$pureaddr);
                                            if(count($addrSplit)>1) $pureaddr = @$addrSplit[1];
                                        }elseif(strpos($hName,'道')!==false){
                                            $pureaddr = strchr($hName,'道',true).'道';
                                            $pureaddr = _fn_num_2sect($pureaddr);
                                            $addrSplit = explode('里',$pureaddr);
                                            if(count($addrSplit)>1) $pureaddr = @$addrSplit[1];
                                        } else {
                                            if(strpos($hName,'舊港')!==false ){ $pureaddr = "舊港"; } 
                                            elseif(strpos($hName,'美之城')!==false ){ $pureaddr = "美之城"; } 
                                            elseif(strpos($hName,'柯仔湳')!==false ){ $pureaddr = "柯仔湳"; } 
                                            /*
                                                --請選擇-- 香山區 北區 舊港路->舊港 東香里

                                            路|街|舊港|美之城|柯仔湳|景觀大道
                                            if(strpos($hName,'紅毛埤')!==false ){ $pureaddr = "紅毛埤"; } 
                                            elseif(strpos($hName,'竹子腳')!==false || strpos($hName,'竹子?')!==false){ $pureaddr = "竹子腳"; } 
                                            elseif(strpos($hName,'東洋新?')!==false || strpos($hName,'東洋新')!==false){ $pureaddr = "東洋新村"; } 
                                            //elseif(strpos($hName,'嘉農新村')!==false ){ $pureaddr = "嘉農新村"; } 
                                            elseif(strpos($hName,'盧厝')!==false ){ $pureaddr = "盧厝"; } 
                                            elseif(strpos($hName,'過溪')!==false ){ $pureaddr = "過溪"; } 
                                            //elseif(strpos($hName,'興業新村')!==false ){ $pureaddr = "興業新村"; } 
                                            //elseif(strpos($hName,'仁義新村')!==false ){ $pureaddr = "仁義新村"; } 
                                            elseif(strpos($hName,'頂埤')!==false ){ $pureaddr = "頂埤"; } 
                                            elseif(strpos($hName,'藝術新?')!==false || strpos($hName,'藝術新')!==fals){ $pureaddr = "藝術新村"; } 
                                            //elseif(strpos($hName,'國華新村')!==false ){ $pureaddr = "國華新村"; } 
                                            //elseif(strpos($hName,'海埔新村')!==false ){ $pureaddr = "海埔新村"; } 
                                            elseif(strpos($hName,'北社尾')!==false ){ $pureaddr = "北社尾"; } 
                                            elseif(strpos($hName,'草地尾')!==false ){ $pureaddr = "草地尾"; } 
                                            elseif(strpos($hName,'山子頂')!==false ){ $pureaddr = "山子頂"; } 
                                            elseif(strpos($hName,'許厝庄仔')!==false ){ $pureaddr = "許厝庄仔"; } 
                                            elseif(strpos($hName,'後厝仔')!==false ){ $pureaddr = "後厝仔"; } 
                                            elseif(strpos($hName,'水源地')!==false ){ $pureaddr = "水源地"; } 
                                            elseif(strpos($hName,'湖子內')!==false ){ $pureaddr = "湖子內"; } 
                                            elseif(strpos($hName,'荖藤宅')!==false ){ $pureaddr = "荖藤宅"; } //SELECT `dt_id`,`dt_pureaddr`,`dt_A` FROM `data_distsplit_32` WHERE `dt_K`!='土地' GROUP BY `dt_pureaddr`!=''
                                            elseif(strpos($hName,'磚?')!==false ){ $pureaddr = "磚磘"; } //       秀園新村 育樂新村 村 場 道
                                            */
                                            else {
                                                $pureaddr = $hName;
                                            }
                                        }

                                    }

                                }



                                $addrUrl = 'https://zip5.5432.tw/zip/'.urlencode($cityname.$rowName);
                                $getDistCode = '';
                                /*
                                if($rowTypeFrom=='土地'){
                                    $getDistCode = '';
                                } else {
                                    $getDistStr = load_contents($addrUrl,true);
                                    $getDistStr = get_between($getDistStr,'id="new-adrs">','id="zipcode6"');
                                    $getDist = trim(get_between($getDistStr,$cityname,$rowName));
                                    $getDistCode = $codeArr[$getDist];
                                }
                                */
                                $getyearFrom = $SHEET->getCellByColumnAndRow(2, $rowNum)->getValue();
                                $getyearFromSplit = explode('/',$getyearFrom);
                                $getyear = $getyearFromSplit[0];

                                $insSQLh = "(NULL, ";
                                $insSQL = "'".$getDistCode."',";
                                $insSQL .= "'".$getyear."',";
                                $insSQL .= "'".$pureaddr."',";
                                $insSQL .= "'".$rowNameFrom."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(1, $rowNum)->getValue()."',";
                                $insSQL .= "'".$getyearFrom."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(3, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(4, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(5, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(6, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(7, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(8, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(9, $rowNum)->getValue()."',";
                                $insSQL .= "'".$rowTypeFrom."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(11, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(12, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(13, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(14, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(15, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(16, $rowNum)->getValue()."',";
                                $insSQL .= "'".$SHEET->getCellByColumnAndRow(17, $rowNum)->getValue()."',";
                                $insSQL .= "'".$filename."'";
                                $insSQL .= ")";
                                //echo $insSQL."<br>";

                                $doDISTsql = "INSERT INTO `data_distsplit_".$cityCode."` VALUES ".$insSQLh.$insSQL;
                                $ins = mysqli_query($link, $doDISTsql);
                                $insID = mysqli_insert_id($link);
                                if($ins && $insID>0){
                                    $cTotalDupRows++;
                                    //echo $cTotalDupRows.'. ('.$insID.', '.$insSQL.'<br>';
                                } else {
                                    $cErrorCount++;
                                    echo $cErrorCount.'. ("0", '.$insSQL.'<br>';
                                }

                                //if($cTotalRowNum==100){ break; }
                                $cTotalRowNum++;
                                //if($fi==0){ break; }
                                //$fi++;
                                //sleep(2);
                            }
                            $cTotalFiles++;
                            //if($i==0){ break; }
                            //$i++;
                            //sleep(3);
                        }
                    }
                }
            }
        }
    $timeUse = time() - $startTime;
    echo "<hr>";
    echo date('Y-m-d H:s:i').' Done!';
    echo "<hr>檔案紀錄筆數: ".$cTotaXLSRows." 筆";
    echo "<hr>共產生資料筆數: ".$cTotalDupRows." / ".$cTotalRowNum." 筆";
    echo "<hr>錯誤資料筆數: ".$cErrorCount." 筆";
    echo "<hr>花費時間: ".$timeUse." 秒<br><br><br><br>";
    //echo "<hr><br>Opt Doned: ".$chkDoneOpt."<br><br><br><br>";
    mysqli_close($link);
    ?>
    <audio autoplay='true' style='display:none;'><source src='alert.wav'></audio>
    <br><br><br><br>
    </div>
</body>
</html>

