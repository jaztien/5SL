<?php
##############################################################
##############################################################
##############################################################
##############################################################
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
    $locJS=_fn_getCachedJsonDatac("locations.json");

    $doact = isset($_POST['doact']) ? $_POST['doact'] : '';
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
<br>
<br>
<hr>
<div style="font-size:16px;line-height:20px;">
<form id="form_readtofile" name="form_readtofile" action="#" method="post">
    <input type="hidden" id="doact" name="doact" style="width:100px;" value="1">
    <button type="submit" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">開始</button>
    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/BC_reconfirmInfo.php';">RESET</button>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
</form>
<hr>
   
<?php
if(empty($doact)){ echo "請按「開始」按鍵進行操作！";exit(); }
        //$cFileFolder = 'aprretFiles/Original/1130326/'.$cCity;
        //$Addr = "汀州路三段160巷112之1號";
        //$Addr = preg_replace("/\d+(?=[號])+號/","",$Addr);
        //$Addr = preg_replace("/\d+(?=-)/","",$Addr);
        //echo  mb_substr($Addr, -1);
        //exit();
        $sql = "SELECT * FROM `data_buildcase`";
        $sql .= " WHERE bc_infomark='3' AND bc_similar!=''";
        $sql .= " ORDER BY bc_id ASC";
        //$sql .= " LIMIT 1000,100";
        $cTotalRows = 0;
        $cDoneRows = 0;
        $result = mysqli_query($link, $sql);
        $row_cnt = mysqli_num_rows($result);
        mysqli_data_seek($result, 0);  // 指標移到第一筆資料
        //echo $row_cnt;
        echo '<table style="border-collapse:collapse;">';
        while($bcrow = mysqli_fetch_assoc($result)){
            //$bcrow['bc_city']
            //$bcrow['bc_district']
            //$bcrow['bc_title']
            //$bcrow['bc_subtitle']
            //$bcrow['bc_addr']
            //$bcrow['bc_similar']
            //$bcrow['bc_similaradd']
            //$bcrow['bc_similarid']
            //$cStartRow,$bcrow['bc_infomark']
            //$bcrow['bc_imagemark']
            //bc_updateinfo
            //$cStartRow++;
            $cityText = $locJS[$bcrow['bc_city']]['text'];
            $distText = $locJS[$bcrow['bc_city']]['district'][$bcrow['bc_district']];

            $fromTitle = trim($bcrow['bc_title']);
            $fromTitlePure = str_replace(['大樓','大廈','華廈','名廈',' '],'',$fromTitle);
            $toTitle = trim($bcrow['bc_similar']);
            $toTitlePure = str_replace(['大樓','大廈','華廈','名廈',' '],'',$toTitle);
                        
            $fromAddr = str_replace('臺','台',$bcrow['bc_addr']);//臺
            $fromAddr = str_replace([$cityText,$distText],'',$fromAddr);
            $toAddr = str_replace('臺','台',$bcrow['bc_similaradd']);
            $toAddr = str_replace([$cityText,$distText],'',$toAddr);
            /*
            if(
                (strpos($fromAddr,'市')==false && strpos($fromAddr,'區')==false)
                //&&(strpos($toAddr,'市')==false && strpos($toAddr,'區')==false)
            ){
                //echo "【0】@@ ".$bcrow['bc_similarid']." @@";
                //echo $bcrow['bc_title']."[".$fromAddr."]@@".$bcrow['bc_similar']."[".$toAddr."]<br>";
                    continue;
            }
            if(
                (strpos($fromAddr,'市政')!==false || 
                strpos($fromAddr,'園區')!==false || 
                strpos($fromAddr,'新市')!==false || 
                strpos($fromAddr,'環市')!==false || 
                strpos($fromAddr,'環區')!==false || 
                strpos($fromAddr,'中市街')!==false || 
                strpos($fromAddr,'工業區')!==false || 
                strpos($fromAddr,'站區')!==false || 
                strpos($fromAddr,'市鎮')!==false || 
                strpos($fromAddr,'工區')!==false || 
                strpos($fromAddr,'市中')!==false || 
                strpos($fromAddr,'市場')!==false || 
                strpos($fromAddr,'西市')!==false ||
                strpos($fromAddr,'夜市')!==false ||
                strpos($fromAddr,'門市')!==false ||
                strpos($fromAddr,'埔市')!==false)

                || (strpos($toAddr,'市政')!==false || 
                strpos($toAddr,'園區')!==false || 
                strpos($toAddr,'新市')!==false || 
                strpos($toAddr,'環市')!==false || 
                strpos($toAddr,'環區')!==false || 
                strpos($toAddr,'中市街')!==false || 
                strpos($toAddr,'工業區')!==false || 
                strpos($toAddr,'站區')!==false || 
                strpos($toAddr,'社區')!==false || 
                strpos($toAddr,'重劃區')!==false || 
                strpos($toAddr,'北市巷')!==false || 
                strpos($toAddr,'市鎮')!==false || 
                strpos($toAddr,'工區')!==false || 
                strpos($toAddr,'市中')!==false || 
                strpos($toAddr,'市場')!==false || 
                strpos($toAddr,'夜市')!==false || 
                strpos($toAddr,'埔市')!==false || 
                strpos($toAddr,'門市')!==false || 
                strpos($toAddr,'西市')!==false)
                ){
                    continue;
            }
            */

            if($fromTitlePure==$toTitlePure){ $srate_title = 1; }//排除樓廈誤差的相同名稱
            elseif(@strpos($fromTitlePure,$toTitlePure)!==false || @strpos($toTitlePure,$fromTitlePure)!==false){ $srate_title = 2; }///不同住址，但排除樓廈誤差後，包含相同字串的交叉比對
            else{ $srate_title = 0; }//非相同名稱
            //比對雙邊住址是否為對方地址之一部分
            $ckFromAddr =  @strpos($toAddr,$fromAddr)!==false ? 1 : 0;
            $ckToAddr =  @strpos($fromAddr,$toAddr)!==false ? 1 : 0;
            $checkAddr = $ckFromAddr+$ckToAddr;
            if($checkAddr==2){ $srate_addr = 1; }//若雙方皆為對方住址之一部分，則住址完全相同
            //elseif($fromAddrPure==$toAddrPure){ $srate_addr = 2; }//位於同路段(不確定同門號)
            elseif($checkAddr==1){//若只有一方為對方住址之一部分，則判定為住址有關連性
                if($ckFromAddr==1){//取得關聯住址中最後一個關鍵字，用以決定住址不同的部分
                    $lastSec = mb_substr($fromAddr, -1);
                } else {
                    $lastSec = mb_substr($toAddr, -1);
                }
                switch($lastSec){//位於同路段同巷弄(不確定同門號)
                    case '號':
                    case '弄':
                    case '巷':
                        $srate_addr = 2;
                    break;
                    case '段'://位於同路段(不確定同巷弄門號)
                    case '街':
                    case '路':
                        $srate_addr = 3;
                    break;
                }
            }
            else{ $srate_addr = 0; }//住址無關連性

            $checkMark = 0;
            if($srate_title==1){//排除樓廈誤差下相同建案名稱時
                if($srate_addr==1 || $srate_addr==2){
                    $checkMark = 1;
                }elseif($srate_addr==3){
                    $checkMark = 2;
                }else{
                    $checkMark = 3;
                }
            }elseif($srate_title==2){//不同住址，但排除樓廈誤差後，建案名稱包含相同字串的交叉比對時
                if($srate_addr==0){
                    $checkMark = 3;
                }else{
                    $checkMark = $srate_addr;
                }
            }else{//非相同建案名稱時
                if($srate_addr==1){//當住址比對完全相同時
                    $checkMark = 2;
                }elseif($srate_addr==2){//當住址比對部份相同時
                    $checkMark = 3;
                }
            }

            $trColor = 'black';
            $up = mysqli_query($link, "UPDATE `data_buildcase` SET bc_updateinfo='".$checkMark."' WHERE bc_id='".$bcrow['bc_id']."' AND bc_updateinfo='0'" );
            if($up){
                $cDoneRows++;
            } else {
                $trColor = 'red';
            }

            echo '<tr style="border-bottom:#CCCCCC 1px solid;color:'.$trColor.'">';
            echo '<td>【'.$checkMark.'】</td>';
            echo '<td>'.$bcrow['bc_id'].'</td>';
            echo '<td style="font-weight:bold;">'.$bcrow['bc_title'].'</span></td>';
            echo '<td>'.$fromAddr.'</td>';
            echo '<td style="font-weight:bold;">'.$bcrow['bc_similar'].'</span></td>';
            echo '<td>'.$toAddr.'</td>';
            echo '<td style="padding:0 16px 0 16px;">'.$srate_title.'</td>';
            echo '<td style="padding:0 16px 0 16px;">'.$srate_addr.'</td>';
            echo '</tr>';
            //print_r($pregArr);
            //echo $bcrow['bc_title']."[".$bcrow['bc_addr']."]@@".$bcrow['bc_similar']."[".$bcrow['bc_similaradd']."]@@".$srate_title."@@".$srate_addr."<br>";
            //echo "【".$checkMark."】@@ ".$bcrow['bc_id']." @@";
            //echo "<span style='font-weight:bold;'>".$bcrow['bc_title']."</span>[".$fromAddr."]@@<span style='font-weight:bold;'>".$bcrow['bc_similar']."</span>[".$toAddr."]@@".$srate_title."@@".$srate_addr."<br>";
           
            $cTotalRows++;       
            //echo $cFileName.'__'.$cTotalRows.' Rows <br>';
        }
        echo '</table>';

        echo '<hr>';
        echo 'Total Rows: '.$cDoneRows.' / '.$cTotalRows;
        echo '<hr>';
        echo 'Error Rows: '.($cTotalRows - $cDoneRows);
        echo '<br>';
        echo '<br>';
        echo '<br>';
        echo '<br>';

//----------------------------------
mysqli_close($link);

?>
</div>
</body>
</html>

