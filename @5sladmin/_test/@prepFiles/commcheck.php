<?php
    error_reporting(E_ALL); 
    header("Content-Type:text/html; charset=utf-8");
	include 'aprret_func.php';
	include 'PHPExcel/Classes/PHPExcel.php';
    $host = 'localhost';
    $dbuser ='root';
    $dbpassword = '1111';
    $dbname = 'jazhome';
    $link = mysqli_connect($host,$dbuser,$dbpassword,$dbname);
    //--------------------------
    $year = isset($_POST['year']) ? $_POST['year'] : '';
    $fromID = isset($_POST['fromID']) ? $_POST['fromID'] : '';
    $toID = isset($_POST['toID']) ? $_POST['toID'] : '';
    // 關閉連線 -----------------
    $locJS=_fn_getCachedJsonDatac("locations.json");

if(!empty($_POST['doAdd'])){
    $doADD = $_POST['doAdd'];
    for($i=0;$i<count($doADD);$i++){
        $getJSON = json_decode($doADD[$i],true);
        $getMode = $getJSON[0];
        $getCommID = $getJSON[1];
        $getBCID = $getJSON[2];
        
        switch($getMode){
            case 1: // Get link with data_buildcase
                $ssql = "SELECT * FROM data_buildcase WHERE bc_id='".$getBCID."'";
                $sres = mysqli_query($link, $ssql);
                $srow = mysqli_fetch_array($sres);
                //
                if($sres){
                    $upSQL = "UPDATE `commcheck` SET `bc_id` = '".$getBCID."',`comm_bcaddr`='".$srow['bc_addr']."',`comm_check`='5' WHERE `comm_id`=".$getCommID;
                    $up = mysqli_query($link, $upSQL);
                    if($up){
                        $csql = "SELECT * FROM commcheck WHERE comm_id='".$getCommID."'";
                        $cres = mysqli_query($link, $csql);
                        $crow = mysqli_fetch_array($cres);
                        $upAddr = !empty($_POST['addr'.$getBCID])?$_POST['addr'.$getBCID]:'';
                        //
                        $upSQLbc = "UPDATE `data_buildcase` SET bc_addr='".$upAddr."'";
                        if(empty($srow['bc_subtitle'])==true) { $upSQLbc .= ", `bc_subtitle`='".$crow['comm_title']."'"; }
                        elseif(empty($srow['bc_subtitle2'])==true) { $upSQLbc .= ", `bc_subtitle2`='".$crow['comm_title']."'"; }

                        $upSQLbc .= " WHERE `bc_id`=".$getBCID;
                        $upbc = mysqli_query($link, $upSQLbc);
                    } else {

                    }
                }

            break;
            //------------------------------------------
            case 2: //ADD New buildcase from 591 if exists.






            break;
            //------------------------------------------
            case 3: // Doesn't match any case 






            break;
        }



        









        //echo "<br>".$getMode;

    }



        

}




    
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

<form id="form_commmerge" name="form_commmerge" action="#" method="post">
    <button type="submit" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">ACTION</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="
   if(confirm('確定重置嗎？')){
        window.location.href ='http://localhost:8888/commcheck.php';
   }
    ">RESET</button>
    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="
    if(confirm('確定返回首頁嗎？')){
        window.location.href ='http://localhost:8888/';
    }
    ">HOME</button>
<hr>
    
<?php


$sql = "SELECT comm_district,comm_title, COUNT(*) c FROM commcheck WHERE comm_title<>'' AND comm_check<>'' GROUP BY comm_district,comm_title HAVING c > 1 ";
//$sql .= " WHERE comm_check=5"; 
//$sql .= " AND comm_title<>''"; 
//$sql .= " ORDER BY bc_id ASC;
//$sql .= " LIMIT 400,600";
//$sql .= " AND comm_id BETWEEN 10000 AND 35000";
$result = mysqli_query($link, $sql);
$i=1;
while( $row = mysqli_fetch_assoc($result)){

    echo $i.". ".$row['comm_district']." ".$row['comm_subtitle2']."<br>";
    $i++;
    /*
    $ssql = "SELECT bc_id FROM commcheck WHERE bc_id='".$row['bc_id']."' AND comm_id <> '".$row['comm_id']."'";
    //$ssql .= " AND (bc_title = '".$getTitle."'";
    //$ssql .= " OR (bc_subtitle<>'' AND bc_subtitle = '".$getTitle."')";
    //$ssql .= " AND (bc_title LIKE '%".$getFTitle."%'";
    //$ssql .= " OR '".$row['comm_title']."' LIKE '%`bc_title`%'";
    //$ssql .= " OR (bc_subtitle<>'' AND bc_subtitle LIKE '%".$getFTitle."%')";
    //$ssql .= " OR (bc_subtitle2<>'' AND bc_subtitle2 LIKE '%".$getFTitle."%')";
    //$ssql .= ")";
    $sres = mysqli_query($link, $ssql);
    $srow = mysqli_fetch_array($sres);
    $srow_cnt = mysqli_num_rows($sres);
    if($srow_cnt>0) {
        echo $srow['bc_id']."<br>";
    }
*/

}







exit();
/*
if(empty($year) && empty($fromID) && empty($toID)){ echo "尚未設定年份,或起始結束ID段！";exit(); }
if(!empty($year) && ($year<101 || $year>112)){ echo "年份錯誤！";exit(); }
if(empty($year) && (empty($fromID) || empty($toID))){ echo "尚未設定起始結束ID段！";exit(); }
*/

// 取得資料 (欄位名稱 and 欄位值)
$sql = "SELECT * FROM commcheck";
$sql .= " WHERE comm_check='0'"; 
//$sql .= " AND comm_title<>''"; 
//$sql .= " ORDER BY bc_id ASC;
//$sql .= " LIMIT 400,600";
$sql .= " AND comm_id BETWEEN 10000 AND 35000";
$result = mysqli_query($link, $sql);
//mysqli_data_seek($result, 0);  // 指標移到第一筆資料
echo '<table>';
echo '<tr>';
echo '<td style="border-top:#CCCCCC 1px solid;"></td>';
echo '<td style="border-top:#CCCCCC 1px solid;"></td>';
echo '<td style="border-top:#CCCCCC 1px solid;"></td>';
echo '<td style="border-top:#CCCCCC 1px solid;"></td>';
echo '<td style="border-top:#CCCCCC 1px solid;padding-right:12px;">CommCheck(Aprret) Title</td>';
echo '<td style="border-top:#CCCCCC 1px solid;">BuildCase Title</td>';
echo '</tr>';

$chkAddOpt=0;
$chkSameOpt=0;
$chkDoneOpt=0;
$cTotalRowNum=0;
$getBR = "";
while( $row = mysqli_fetch_assoc($result)){
    $cTotalRowNum++;
    $getID = $row['comm_id'];
    $getCity = $row['comm_city'];
    $getDist = $row['comm_district'];
    $setCityText = !empty($getCity) ? $locJS[$getCity]['text'] : '';
    $setDistText = !empty($getCity)&&!empty($getDist) ? $locJS[$getCity]['district'][$getDist] : '';
    $getCityDist = $setCityText.$setDistText;
    $commEZadd = strchr($row['comm_retaddr'],'號',true).'號';

    $getTitle = trim($row['comm_title']);
    $getFTitle = str_replace(['大樓','大廈','名廈','華廈',' '],'',$getTitle);
    $getSub = trim($row['comm_subtitle']);
    $getSub2 = trim($row['comm_subtitle2']);
    
    //--------------------------------
    $ssql = "SELECT * FROM data_buildcase WHERE bc_city='".$getCity."' AND bc_district='".$getDist."'";
    $ssql .= " AND (bc_title='".$getTitle."' OR bc_subtitle='".$getTitle."')";
    //$ssql .= " AND (bc_title = '".$getTitle."'";
    //$ssql .= " OR (bc_subtitle<>'' AND bc_subtitle = '".$getTitle."')";
    //$ssql .= " AND (bc_title LIKE '%".$getFTitle."%'";
    //$ssql .= " OR '".$row['comm_title']."' LIKE '%`bc_title`%'";
    //$ssql .= " OR (bc_subtitle<>'' AND bc_subtitle LIKE '%".$getFTitle."%')";
    //$ssql .= " OR (bc_subtitle2<>'' AND bc_subtitle2 LIKE '%".$getFTitle."%')";
    //$ssql .= ")";
    $sres = mysqli_query($link, $ssql);
    $srow = mysqli_fetch_array($sres);
    $srow_cnt = mysqli_num_rows($sres);
    //-------------------------------- 
    //$srow_cnt = 1;
    if($srow_cnt>0){
        //echo '<tr style="color:green;">'.$getTitle.'</tr>';
        
        $bcEZadd = str_replace('臺','台',$srow['bc_addr']);
        $bcEZadd = str_replace($getCityDist,'',$bcEZadd);
/*
        echo '<tr>';
        echo '<td><input type="checkbox" id="doAdd[]" name="doAdd[]" value="[2,'.$getID.',0]"></td>';
        echo '<td>'.$getID.'</td>';
        echo '<td>XXXX</td>';
        echo '<td>'.$getDist.'</td>';
        echo '<td>'.$getTitle.(!empty($row['comm_subtitle'])?'/'.$row['comm_subtitle']:'').(!empty($row['comm_subtitle2'])?'/'.$row['comm_subtitle2']:'').'</td>';
        echo '<td>XXXX</td>';
        echo '</tr>';
*/


        echo '<tr style="color:green;">';
        echo '<td style="border-top:#CCCCCC 1px solid;"><input type="checkbox" id="doAdd[]" name="doAdd[]" style="width:24px;height:24px;" value="[1,'.$getID.','.$srow['bc_id'].']"></td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">'.$getID.'</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">'.$srow['bc_id'].'</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">'.$getDist.'</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">'.$getTitle.(!empty($row['comm_subtitle'])?'/'.$row['comm_subtitle']:'').(!empty($row['comm_subtitle2'])?'/'.$row['comm_subtitle2']:'').'</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;"><a href="https://www.google.com/search?q='.$getCityDist.$srow['bc_title'].'" target="_blank" style="color:green;">'.$srow['bc_title'].(!empty($srow['bc_subtitle'])?'/'.$srow['bc_subtitle']:'').(!empty($srow['bc_subtitle2'])?'/'.$srow['bc_subtitle2']:'').'</a></td>';
        echo '</tr>';
        echo '<tr style="color:green;">';
        echo '<td style="border-bottom:#CCCCCC 1px solid;padding-right:4px;"></td>';
        echo '<td style="border-bottom:#CCCCCC 1px solid;padding-right:4px;"></td>';
        echo '<td style="border-bottom:#CCCCCC 1px solid;padding-right:4px;"></td>';
        echo '<td style="border-bottom:#CCCCCC 1px solid;padding-right:4px;">'.$getCityDist.'</td>';
        echo '<td style="border-bottom:#CCCCCC 1px solid;padding-right:4px;background-color:#DDDDDD;"><a href="https://www.google.com/maps/place/'.$getCityDist.$commEZadd.'" target="_blank" style="cursor:pointer;">'.$commEZadd.'</a></td>';
        echo '<td style="border-bottom:#CCCCCC 1px solid;padding-right:4px;background-color:#DDDDDD;"><input type="text" id="addr'.$srow['bc_id'].'" name="addr'.$srow['bc_id'].'" style="width:80%;" value="'.$bcEZadd.'"><span onClick="var tourl=\'https://www.google.com/maps/place/\'+document.getElementById(\'addr'.$srow['bc_id'].'\').value;window.open(tourl);" style="cursor:pointer;">GO</span></td>';
        echo '</tr>';


        
        $chkSameOpt++;
    }
    else {
        /*
        $rtn['match'] = true;
        $rtn['BCid'] = 0;
        $rtn['BCtitle'] = '';
        
       // $rtn = _fn_get591_check($getTitle,$getCity,$getDist);
        if(!empty($rtn['match'])){
            echo '<tr>';
            echo '<td><input type="checkbox" id="doAdd[]" name="doAdd[]" value="[2,'.$getID.','.$rtn['BCid'].']"></td>';
            echo '<td>'.$getID.'</td>';
            echo '<td>'.$rtn['BCid'].'</td>';
            echo '<td>'.$getDist.'</td>';
            echo '<td>'.$getTitle.(!empty($row['comm_subtitle'])?'/'.$row['comm_subtitle']:'').(!empty($row['comm_subtitle2'])?'/'.$row['comm_subtitle2']:'').'</td>';
            echo '<td>'.$rtn['BCtitle'].'</td>';
            echo '</tr>';
            $chkAddOpt++;
        } else {
            echo '<tr style="color:red;">';
            echo '<td><input type="checkbox" id="doAdd[]" name="doAdd[]" value="[3,'.$getID.',0]"></td>';
            echo '<td>'.$getID.'</td>';
            echo '<td></td>';
            echo '<td>'.$getDist.'</td>';
            echo '<td>'.$getTitle.(!empty($row['comm_subtitle'])?'/'.$row['comm_subtitle']:'').(!empty($row['comm_subtitle2'])?'/'.$row['comm_subtitle2']:'').'</td>';
            echo '<td></td>';
            echo '</tr>';
        }
        */
        $chkDoneOpt++;
    }
}
echo '</table>';
echo "<hr><br>Total Row Numbers: ".$cTotalRowNum."<br>"; 
echo "<hr><br>Need Check: ".$chkSameOpt."<br>";
echo "Need Add: ".$chkAddOpt." / ".$chkDoneOpt."<br><hr><br>";

mysqli_close($link);

?>
</form>

</div>
</body>
</html>