<?php
//////////////////////////////////////////////////////////////////////////////
//////////////////////////// 依郵遞區號規則分區 /////////////////////////////////
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
    $writeto = isset($_POST['writeto']) ? $_POST['writeto'] : '';
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
    <input type="checkbox" id="writeto" name="writeto" value="1"> 寫入資料庫 &nbsp&nbsp&nbsp&nbsp
    <button type="submit" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">執行</button>
    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/17_32_splitdistByAddr.php';">RESET</button>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
</form>
<hr>

<?php
//////////////////////////////////// 設定 ////////////////////////////////////
    $basefolder = "aprretFiles/Original/20240416/";
    $tableName = "data_distsplit_32";
    $subfolder = "32/3201";
    $cityname = "嘉義市";
    //大業街|中山路|中正路|文化路|世賢路一段|世賢路四段|北榮街|民族路|民權路|永安街|光彩街|安業街|延平街|林森西路|長榮街|垂楊路|國華街|崇文街|康樂街|博愛路一段|湖美一路|順興七街|順興六街|興安街|興美六路|興業東路|蘭井街
    $roadName = "蘭井街";
    //$codeArr = array("東區"=>"1701","北區"=>"1702","香山區"=>"1703");
    //$codeArr = array("東區"=>"3201","西區"=>"3202");
    $setDistCode = '3201';
    $passToDistCode = '3202'; //通過以下的條件後，可認定為此區，若未通過以下條件，則認定為上述設定區域；而為空值時，則不同區域分開單獨歸類。
    $base = array(//「號」，當有「巷」時，其數值紀錄於 $extra 陣列中的鍵值
        'even' => [[154,0]],//雙數範圍陣列，0為無限  [0,0]
        'odd' => [[213,0]]//單數範圍陣列，0為無限  [0,0]
    );
    $extra = array(//「巷」=>「號」
        ''=>array(//指定「巷」
            'even' => [],//雙數範圍陣列，0為無限 [0,0]
            'odd' => [[0,0]]//單數範圍陣列，0為無限 [0,0]
        )
    );
///////////////////////////////////////////////////////////////////////
echo '主要資料夾：'.$basefolder.'<br>';
echo '子資料夾：'.$subfolder.'<br>';
echo '操作資料表：'.$tableName.'<br>';
echo '搜尋條件(城市)：'.$cityname.'<br>';
echo '搜尋條件(道路)：'.$roadName.'<br>';
echo '預設區劃：'.$setDistCode.'<br>';
echo '比對成功後區劃：'.$passToDistCode.'<br>';
echo '<hr>';
    $checkLoading = 1;
    if(empty($doact)){ echo " /請點選「操作」鍵進行操作！！可選擇是否「寫入資料庫」"; $checkLoading = 0; }
    if(empty($checkLoading)){
        echo "<script>window.parent.document.getElementById('loading').style.display = 'none';</script>";
        exit();
    }

    $doUpdate = $writeto;

///////////////////////////////////////////////////////////////////////
$checkLoading = 1;
if(empty($doact)){ echo " /請點選「操作」鍵進行操作！！可選擇是否「寫入資料庫」"; $checkLoading = 0; }
if(empty($checkLoading)){
    echo "<script>window.parent.document.getElementById('loading').style.display = 'none';</script>";
    exit();
}

$doUpdate = $writeto;
///////////////////////////////////////////////////////////////////////

        // 取得資料 (欄位名稱 and 欄位值)
        $sql = "SELECT * FROM `".$tableName."`";
        $sql .= " WHERE dt_distcode='' AND dt_pureaddr LIKE '%".$roadName."%'";
        //$sql .= " WHERE f_id > ".$fromID." AND f_id <=".$toID;
        //$sql .= " WHERE f_id =1";
        //$sql .= " WHERE f_year IN ('".$folderr."','".$folderr2."')";
        //$sql .= " LIMIT 1,1";
        $result = mysqli_query($link, $sql);
        mysqli_data_seek($result, 0);  // 指標移到第一筆資料
        $cTotalRowNum = 0;
        $cTotalDupRows = 0;
        $cErrorCount = 0;
        while($flist = mysqli_fetch_assoc($result)){
            $distCode = $setDistCode;
            $nong = 0;
            $extraNo = 0;
            $addrName = _fn_str_f2h($flist['dt_A']);
            $addrName = strchr($addrName,'號',true);
            if(strpos($addrName,'鄰')!==false){$addrName = mb_substr(mb_strrchr($addrName,'鄰'),1);}
            else { $addrName = str_replace([$cityname,$roadName],'',$addrName); }
            if(strpos($addrName,'之')!==false){
                $addrName = strchr($addrName,'之',true);
            }
            if(strpos($addrName,'弄')!==false){
                $nong= get_between($addrName,'巷','弄');
                $nong = intval($nong);
                $addrName = str_replace($nong.'弄','',$addrName);
            }
            if(strpos($addrName,'巷')!==false){
                //$addrName = strchr($addrName,'巷',true);
                $addrNameSplit = explode('巷',$addrName);
                $addrName = $addrNameSplit[0];
                $extraNo = intval($addrNameSplit[1]);
            } 
            if(strpos($addrName,'至')!==false){
                $addrName = mb_substr(mb_strrchr($addrName,'至'),1);
            }
            $baseNo = intval($addrName);
            ////////////////////////////////////////////////////////////
            $passed = 0;
            $oe = $baseNo%2==0 ? 'even' : 'odd';
            if(count($base[$oe])>0){//處理「號」或「巷」
                for($i=0;$i<count($base[$oe]);$i++){
                    $fromN = (int)$base[$oe][$i][0];
                    $toN = (int)$base[$oe][$i][1];
                    if($fromN==0 && $toN==0){
                        $passed = 1;
                        continue;
                    }
                    if($baseNo >= $fromN){
                        if($toN==0 || ($toN>0 && $toN>=$fromN && $baseNo <= $toN)){
                            $passed = 1;
                            continue;
                        }
                    }
                }
            }
            //
            if(!empty($extraNo) && isset($extra[$baseNo])){
                $passed = 0;
                $oe = $extraNo%2==0 ? 'even' : 'odd';
                if(count($extra[$baseNo][$oe])>0){//處理「號」或「巷」
                    for($ei=0;$ei<count($extra[$baseNo][$oe]);$ei++){
                        $fromN = (int)$extra[$baseNo][$oe][$ei][0];
                        $toN = (int)$extra[$baseNo][$oe][$ei][1];
                        if($fromN==0 && $toN==0){
                            $passed = 1;
                            continue;
                        }
                        if($extraNo >= $fromN){
                            if($toN==0 || ($toN>0 && $toN>=$fromN && $extraNo <= $toN)){
                                $passed = 1;
                                continue;
                            }
                        }
                    }
                }
            }
            
            if($passed == 1){ $distCode = $passToDistCode;  }
            /////////////////////////////////////// DO UPDATE //////////////
            if($doUpdate==1){
                $up = mysqli_query($link, "UPDATE `".$tableName."` SET dt_distcode='".$distCode."' WHERE dt_id=".$flist['dt_id'] );
                if($up){
                    $cTotalDupRows++;
                    //echo 'Done!! @@ '.$flist['dt_id'].' @@ '.$distCode.' @@ '.$flist['dt_A'].'<br>';
                } else{
                    $cErrorCount++;
                    echo '<span style="color:red">Failed!! @@ '.$flist['dt_id'].' @@ '.$flist['dt_A'].'</span><br>';
                }
            } else {
                $cTotalDupRows++;
                //echo $flist['dt_id'].'@@'.$baseNo.'@@'.$extraNo.'<br>';
                echo $flist['dt_id'].'@@'.$distCode.'@@'.$flist['dt_A'].'<br>';
            }

            //sleep(2);
            $cTotalRowNum++;
        }


       
    $timeUse = time() - $startTime;
    echo "<hr>";
    echo date('Y-m-d H:s:i').' Done!';
    //echo "<hr>檔案紀錄筆數: ".$cTotaXLSRows." 筆";
    echo "<hr>".$roadName." 共完成資料筆數: ".$cTotalDupRows." / ".$cTotalRowNum." 筆";
    echo "<hr>錯誤資料筆數: ".$cErrorCount." 筆";
    echo "<hr>花費時間: ".$timeUse." 秒<br><br><br><br>";
    
    //echo "<hr><br>Opt Doned: ".$chkDoneOpt."<br><br><br><br>";
    mysqli_close($link);
    ?>
    <audio autoplay='true' style='display:none;'><source src='alert.wav'></audio>
    <br><br><br><br>
    </div>
"<script>window.document.getElementById('loading').style.display = 'none';</script>
</body>
</html>

