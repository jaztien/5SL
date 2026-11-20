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
            $getBCID = $getJSON[1];
            $toArea = $getJSON[2];
            
            switch($getMode){
                case 1: // Get link with data_buildcase
                    $ssql = "SELECT * FROM data_buildcase WHERE bc_id='".$getBCID."'";
                    $sres = mysqli_query($link, $ssql);
                    $srow = mysqli_fetch_array($sres);
                    //
                    if($sres){
                        $upSQL = "UPDATE `commcheck` SET `bc_area` = '".$toArea."' WHERE `bc_id`=".$getBCID;
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
        window.location.href ='http://localhost:8888/BCcheck.php';
   }
    ">RESET</button>
    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="
    if(confirm('確定返回首頁嗎？')){
        window.location.href ='http://localhost:8888/';
    }
    ">HOME</button>
<hr>
    
<?php
function inpCheckDup($inp){
    $isql = "SELECT inp_id FROM inCheck WHERE inp_title='".$inp."'";
    $ires = mysqli_query($link, $isql);
    $row_cnt = mysqli_num_rows($ires);
    if($row_cnt > 0) { return true; }
    else { return false; }
}


$getIDX = 2;
/*
1平方公尺 = 0.3025坪
1坪 = 3.30579 平方公尺
if(empty($year) && empty($fromID) && empty($toID)){ echo "尚未設定年份,或起始結束ID段！";exit(); }
if(!empty($year) && ($year<101 || $year>112)){ echo "年份錯誤！";exit(); }
if(empty($year) && (empty($fromID) || empty($toID))){ echo "尚未設定起始結束ID段！";exit(); }
*/

// 取得資料 (欄位名稱 and 欄位值)
$sql = "SELECT bc_id,bc_district,bc_title,bc_zoning FROM data_buildcase";
$sql .= " WHERE bc_zoning <> ''"; 
//$sql .= " AND bc_materials LIKE '%基地%'"; 
//$sql .= " AND bc_materials NOT LIKE '%坪，%'"; 
//$sql .= " AND bc_materials REGEXP '(基地面積)[0-9](坪)|(基地面積約)[0-9](坪)'"; 
//$sql .= " ORDER BY bc_id ASC;
//$sql .= " LIMIT 400,600";
//$sql .= " AND comm_id BETWEEN 10000 AND 35000";
$result = mysqli_query($link, $sql);
//mysqli_data_seek($result, 0);  // 指標移到第一筆資料
echo '<table>';
echo '<tr>';
echo '<td style="border-top:#CCCCCC 1px solid;">Action</td>';
echo '<td style="border-top:#CCCCCC 1px solid;">ID</td>';
echo '<td style="border-top:#CCCCCC 1px solid;">District</td>';
echo '<td style="border-top:#CCCCCC 1px solid;">Title</td>';
echo '<td style="border-top:#CCCCCC 1px solid;padding-right:12px;"></td>';
echo '<td style="border-top:#CCCCCC 1px solid;">Inpublic</td>';
echo '<td style="border-top:#CCCCCC 1px solid;">GetArray</td>';
echo '</tr>';

$chkAddOpt=0;
$chkSameOpt=0;
$chkDoneOpt=1;
$cTotalRowNum=0;
$getBR = "";
while( $row = mysqli_fetch_assoc($result)){
    $cTotalRowNum++;
    //$getID = $row['bc_id'];
    //$getCity = $row['bc_city'];
    //$setCityText = !empty($getCity) ? $locJS[$getCity]['text'] : '';
    //$setDistText = !empty($getCity)&&!empty($getDist) ? $locJS[$getCity]['district'][$getDist] : '';
    //$getCityDist = $setCityText.$setDistText;
    //$commEZadd = strchr($row['comm_retaddr'],'號',true).'號';
    /*
    $getAreaSplit = explode('基地',$row['bc_materials']);
    if(strpos($row['bc_materials'],"平方公尺，")) { 
        $getAreaSplit = explode('平方公尺，',$getAreaSplit[1]); 
        $getArea = $getAreaSplit[0];
        $getArea = str_replace(['約','大約','約為','約近','為','總共為','有','面積約','達','僅','僅有','共',':','：',','],'',$getArea);
        $getArea = intval($getArea);
        $getArea = $getArea * 0.3025;
    } elseif(strpos($row['bc_materials'],"坪，")) { 
        $getAreaSplit = explode('坪，',$getAreaSplit[1]); 
        $getArea = $getAreaSplit[0];
        $getArea = str_replace(['約','大約','約為','約近','為','總共為','有','面積約','達','僅','僅有','共',':','：',','],'',$getArea);
        $getArea = intval($getArea);
    } else {
        $getArea = 0;
    }
    $getArea = round($getArea,2);
    //$getFTitle = str_replace(['大樓','大廈','名廈','華廈',' '],'',$getTitle);
    //$getSub = trim($row['comm_subtitle']);
    //$getSub2 = trim($row['comm_subtitle2']);
    if($getArea==0){ continue; }
    */
    
    /*
    $getArray='';
    if(strpos(trim($row['bc_structure']),",")) {
        $getArray = str_replace(',','","',trim($row['bc_structure']));
        $getArray = '["'.$getArray.'"]';
    } else {
        $getArray='["'.trim($row['bc_structure']).'"]';
    }
    */
    //$getArray='';
    if(strpos(trim($row['bc_zoning']),",")) {
        //$getArray = str_replace(',','","',trim($row['bc_structure']));
        $getArray = explode(',',trim($row['bc_zoning']));
        for($i=0;$i<count($getArray);$i++){
            $isql = "SELECT zo_id FROM zoCheck WHERE zo_title='".$getArray[$i]."'";
            $ires = mysqli_query($link, $isql);
            $row_cnt = mysqli_num_rows($ires);
            if($row_cnt > 0) { 
                continue;
            } else {
                $insql = "INSERT INTO zoCheck VALUES(null, '".$getArray[$i]."', 0)";
                $ins = mysqli_query($link, $insql);
                if($ins){
                    echo '<tr style="color:green;">';
                    echo '<td style="border-top:#CCCCCC 1px solid;"><input type="checkbox" id="doAdd[]" name="doAdd[]" style="width:24px;height:24px;" value="['.$getIDX.','.$row['bc_id'].',0]"></td>';
                    echo '<td style="border-top:#CCCCCC 1px solid;">'.$chkDoneOpt.'</td>';
                    echo '<td style="border-top:#CCCCCC 1px solid;"></td>';
                    echo '<td style="border-top:#CCCCCC 1px solid;">'.trim($row['bc_title']).'</td>';
                    echo '<td style="border-top:#CCCCCC 1px solid;"></td>';
                    echo '<td style="border-top:#CCCCCC 1px solid;">'.$getArray[$i].'</td>';
                    echo '<td style="border-top:#CCCCCC 1px solid;"></td>';
                    echo '</tr>';
                    $chkDoneOpt++;
                }
            }

        }


    } else {
        $isql = "SELECT zo_id FROM zoCheck WHERE zo_title='".trim($row['bc_zoning'])."'";
        $ires = mysqli_query($link, $isql);
        $row_cnt = mysqli_num_rows($ires);
        if($row_cnt > 0) { 
            continue;
        } else {
            $insql = "INSERT INTO zoCheck VALUES(null, '".trim($row['bc_zoning'])."', 0)";
            $ins = mysqli_query($link, $insql);
            if($ins){
                echo '<tr style="color:green;">';
                echo '<td style="border-top:#CCCCCC 1px solid;"><input type="checkbox" id="doAdd[]" name="doAdd[]" style="width:24px;height:24px;" value="['.$getIDX.','.$row['bc_id'].',0]"></td>';
                echo '<td style="border-top:#CCCCCC 1px solid;">'.$chkDoneOpt.'</td>';
                echo '<td style="border-top:#CCCCCC 1px solid;"></td>';
                echo '<td style="border-top:#CCCCCC 1px solid;">'.trim($row['bc_title']).'</td>';
                echo '<td style="border-top:#CCCCCC 1px solid;"></td>';
                echo '<td style="border-top:#CCCCCC 1px solid;">'.$row['bc_zoning'].'</td>';
                echo '<td style="border-top:#CCCCCC 1px solid;"></td>';
                echo '</tr>';
                $chkDoneOpt++;
            }
        }
    }


    /*
    $csql = "UPDATE data_buildcase SET bc_area='".$getArea."' WHERE bc_id='".$row['bc_id']."'";
    $cup = mysqli_query($link, $csql);
    */



    //$cup=true;
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