<?php
  error_reporting(E_ALL); 
  header("Content-Type:text/html; charset=utf-8");
	include 'aprret_func.php';

//print_r( _fn_get591_html('田恕仁',11,1101) );
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
$locJS=_fn_getCachedJsonDatac("locations.json");
$startTime = time();
$doact = isset($_POST['doact']) ? $_POST['doact'] : '';
//3970859 高雄市那瑪夏區 3749
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	
  <title> 人生第五季 Fifth Season Life 測試檔案</title>
  </head>
<body>
  <h2>實價登錄資料處理——通用測試操作</h2>
  <hr>
  <div style="font-size:16px;line-height:20px;">
    <form id="form_readtofile" name="form_readtofile" action="#" method="post">
        <input type="hidden" id="doact" name="doact" value="1">
        <button type="submit" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">執行測試</button>
        <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/testingNO2.php';">重置</button>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
        <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">首頁</button>
    </form>
    <hr>
    <?php
        if(empty($doact)){ echo " /請按「執行測試」按鈕進行測試操作！！";exit();}

        $sql = "SELECT * FROM `data_buildcase`";
        //$sql .= " WHERE bc_id=43117";
        $sql .= " ORDER BY `bc_id` ASC";
        //$sql .= " LIMIT 0,25";
        $result = mysqli_query($link, $sql);
        //$row_cnt = mysqli_num_rows($result);
        //mysqli_data_seek($result, 0);  // 指標移到第一筆資料ret_district ".$row['XXXX']."
        //$sqlHead = "INSERT INTO fh.buildcase ('bc_id', 'bc_published', 'bc_updated', 'bc_city', 'bc_district', 'bc_title', 'bc_subtitle', 'bc_subtitle2', 'bc_mode', 'bc_type', 'bc_usefor', 'bc_addr', 'bc_lat', 'bc_lng', 'bc_area', 'bc_totalnum', 'bc_pratio', 'bc_coverage', 'bc_far', 'bc_completion', 'bc_state', 'bc_plan', 'bc_layernum', 'bc_des', 'bc_unitprice', 'bc_totalprice', 'bc_pprice', 'bc_sprice', 'bc_selling', 'bc_loan', 'bc_meetadd', 'bc_parkratio', 'bc_parkplan', 'bc_parknum', 'bc_building', 'bc_abovemin', 'bc_abovemax', 'bc_undermin', 'bc_undermax', 'bc_direction', 'bc_pmc', 'bc_rmc', 'bc_mfee', 'bc_structure', 'bc_zoning', 'bc_blicense', 'bc_ulicense', 'bc_materials', 'bc_inpublic', 'bc_pile', 'bc_trash', 'bc_investor', 'bc_constructer', 'bc_saleteam', 'bc_cdesign', 'bc_vdesign', 'bc_pdesign', 'bc_ldesign', 'bc_mrt', 'bc_hiway', 'bc_speedway', 'bc_thsr', 'bc_tra', 'bc_othertraffic', 'bc_edu', 'bc_market', 'bc_omarket', 'bc_outpublic', 'bc_circle', 'bc_medical', 'bc_gov', 'bc_pack', 'bc_image64', 'bc_status', 'bc_similar', 'bc_similaradd', 'bc_similarid', 'bc_infomark', 'bc_imagemark', 'bc_updateinfo', 'bc_distance') VALUES";
        //$sqlFoot = ",";
        //$sqlBR = "<br>";
        //$sqlFull=$sqlHead.$sqlBR;
        $cDone=0;
        $i=1;
        while($row = mysqli_fetch_assoc($result)){
            $setCheck = 0;
            $bcsql = "SELECT * FROM `commcheck` WHERE ";
            $bcsql .= "bc_id='".$row['bc_id']."'";
            $bcsql .= " ORDER BY `comm_id` ASC";
            //$sql .= " LIMIT 0,25";
            $bcres = mysqli_query($link, $bcsql);
            $bcrow = mysqli_fetch_array($bcres);
            $bcrow_cnt = mysqli_num_rows($bcres);
            //mysqli_data_seek($result, 0);  // 指標移到第一筆資料ret_district ".$row['XXXX']."


            $commPure=$bcrow['comm_puretitle'];
            $commPhase=$bcrow['comm_purephase'];
            $commExt=$bcrow['comm_pureext'];
            $bcPure=$row['bc_puretitle'];
            $bcPhase=$row['bc_purephase'];
            $bcExt=$row['bc_pureext'];
 
            if($bcrow_cnt>0){

                if(empty($commPure) && empty($commPhase) && empty($commExt) && empty($bcPure) && empty($bcPhase) && empty($bcExt)){
                    continue;
                }

                if(!empty($bcPure)){
                    if(empty($commPure)){ $commPure=$bcPure; }
                } else {
                    if(!empty($commPure)){ $bcPure=$commPure; }
                }

                if(!empty($bcPhase)){
                    if(empty($commPhase)){ $commPhase=$bcPhase; }
                } else {
                    if(!empty($commPhase)){ $bcPhase=$commPhase; }
                }

                if(!empty($bcExt)){
                    if(empty($commExt)){ $commExt=$bcExt; }
                } else {
                    if(!empty($commExt)){ $bcExt=$commExt; }
                }

                $bccsql = "UPDATE `data_buildcase` SET bc_puretitle='".$bcPure."', bc_purephase='".$bcPhase."', bc_pureext='".$bcExt."' WHERE bc_id='".intval($row['bc_id'])."'";
                $bccup = mysqli_query($link, $bccsql);
                //$bccup = true;
                if($bccup){
                    $commsql = "UPDATE `commcheck` SET comm_puretitle='".$commPure."', comm_purephase='".$commPhase."', comm_pureext='".$commExt."' WHERE comm_id='".intval($bcrow['comm_id'])."'";
                    $commup = mysqli_query($link, $commsql);
                    //$commup = true;
                    if($commup){
                        $outPut='';
                        $outPut .= $i.". @@ ".$commPure.$commPhase;
                        if(!empty($commExt)) { $outPut .= "(".$commExt.")"; } 
                        $outPut .= " @@ ".$bcPure.$bcPhase;
                        if(!empty($bcExt)) { $outPut .= "(".$bcExt.")"; }
                        $outPut .= "<br>";
                        echo $outPut;
                        $i++;
                    }
                }

            }
            //$commsql = "UPDATE `data_buildcase` SET bc_check='".$setCheck."' WHERE bc_id='".intval($row['bc_id'])."'";
            //$commup = mysqli_query($link, $commsql);
            /*
            $commup = true;
            if($commup){//
                if($setCheck==1){ $cDone++; }
                echo $i.". @@ ".$setCheck." @@ ".$row['bc_title']."<br>";
                
            }
            */


        }

        
echo "<br><br><hr><br>Total Done: ".$cDone;


  ?>
  </div>
</body>
</html>

