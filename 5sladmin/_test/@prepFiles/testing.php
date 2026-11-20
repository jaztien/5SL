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
        <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/testing.php';">重置</button>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
        <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">首頁</button>
    </form>
    <hr>
    <?php
        if(empty($doact)){ echo " /請按「執行測試」按鈕進行測試操作！！";exit();}


        echo '<table>';
        echo '<tr>';
        echo '<td style="border-top:#CCCCCC 1px solid;">NO.</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">BCID</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">District</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">CommTitle</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">BCTitle</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">pureTitle</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">purePhase</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">pureExt</td>';
        echo '</tr>';
        $startTime = time();
        $cTotalRowNum=0;
        $cTotalReady = 0;
        $cTotalDone = 0;
        $cTotalFailed = 0;
        $cTotalNoMatch = 0;
        $sql = "SELECT * FROM `commcheck` WHERE ";
        //$sql .= "comm_status<=5 AND comm_status!=1 AND bc_id=0";
        $sql .= "bc_id<>0";
        //$sql .= "comm_title REGEXP '/中央住戶|住戶|管理|委員|委員會|管委|管理委|負積人|管理委會|管理員會|||||||/'";
        //$sql .= "comm_check=1";
        $sql .= " ORDER BY `comm_id` ASC";
        //$sql .= " LIMIT 0,25";
        $result = mysqli_query($link, $sql);
        //$row_cnt = mysqli_num_rows($result);
        //mysqli_data_seek($result, 0);  // 指標移到第一筆資料ret_district ".$row['XXXX']."
        //$NOpattern = '/NO|No|no/i';
        $i=1;
        while($row = mysqli_fetch_assoc($result)){
          $tr = '';
          $getLikeExt = str_replace(['區','棟'],'',$row['comm_pureext']);
          $bcsql = "SELECT * FROM `data_buildcase` WHERE ";
          $bcsql .= "bc_id='".$row['bc_id']."'";
          //$bcsql .= "bc_check=1 AND ";
          //$bcsql .= " AND bc_district='".$row['comm_district']."'";
          //if(!empty($row['comm_puretitle'])) { $bcsql .= " AND bc_title LIKE '%".$row['comm_puretitle']."%'"; }
          //else { $bcsql .= " AND bc_title LIKE '%".$row['comm_title']."%'"; }
          //$bcsql .= "bc_puretitle='".$row['comm_puretitle']."' AND ";
          /*
          $bcsql .= "(";
          $bcsql .= "(";
          $bcsql .= "(bc_puretitle IS NOT NULL AND bc_puretitle='".$row['comm_puretitle']."') AND ";
          $bcsql .= "(bc_purephase IS NOT NULL AND bc_purephase='".$row['comm_purephase']."') AND ";
          $bcsql .= "(bc_pureext IS NOT NULL AND REPLACE(REPLACE(bc_pureext, '區', ''),'棟','') = '".$getLikeExt."')";
          $bcsql .= ") OR ";
          $bcsql .= "(bc_puretitle IS NULL AND bc_title LIKE '%".$row['comm_puretitle']."%')";
          $bcsql .= ")";
          */
          //$bcsql .= "bc_purephase='".$row['comm_purephase']."' AND ";
          //$bcsql .= "bc_pureext='".$row['comm_pureext']."' AND ";
          //$bcsql .= "REPLACE(REPLACE(bc_pureext, '區', ''),'棟','') = '".$getLikeExt."'";

          $bcsql .= " ORDER BY `bc_id` ASC";
          //$sql .= " LIMIT 0,25";
          $bcres = mysqli_query($link, $bcsql);
          $bcrow = mysqli_fetch_array($bcres);
          $bcrow_cnt = mysqli_num_rows($bcres);
          //mysqli_data_seek($result, 0);  // 指標移到第一筆資料ret_district ".$row['XXXX']."
  
          $setLinkID = intval($row['bc_id']);
          $setLinkDistrict = trim($row['comm_district']);
          $setLinkTitle = trim($row['comm_title']);
          $tr .= '<tr';
          if($bcrow_cnt>0){
            if(empty($row['comm_puretitle'] && empty($bcrow['bc_puretitle'])) && (empty($row['comm_purephase']) && empty($bcrow['bc_purephase']))){ continue; }
            if((!empty($row['comm_puretitle']) && !empty($bcrow['bc_puretitle']) && $row['comm_puretitle']==$bcrow['bc_puretitle']) && (!empty($row['comm_purephase']) && !empty($bcrow['bc_purephase']) && $row['comm_purephase']==$bcrow['bc_purephase'])){ continue; }
            //if((!empty($row['comm_puretitle']) && !empty($bcrow['bc_puretitle']) && $row['comm_puretitle']==$bcrow['bc_puretitle']) && (!empty($row['comm_purephase']) && !empty($bcrow['bc_purephase']) && $row['comm_purephase']==$bcrow['bc_purephase'])){ continue; }
            $setLinkID = intval($bcrow['bc_id']);
            $setLinkDistrict = trim($bcrow['bc_district']);
            
            //$commsql = "UPDATE `commcheck` SET bc_id='".$setLinkID."' WHERE comm_id='".intval($row['comm_id'])."'";
            //$commup = mysqli_query($link, $commsql);
            //$commup = true;
            $commup = true;
            if($commup){
              $tr .= ' style="color:green;"';
              $cTotalDone++;
            }
            
/*
            if($row['comm_status']==1 && !empty($row['bc_id'])){
              $tr .= ' style="color:purple;"';
              $cTotalReady++;
            } else {
              //action
              $retsql = "UPDATE `data_aprret` SET bc_id='".$setLinkID."',ret_status='2',ret_check='1' WHERE ret_district='".$setLinkDistrict."' AND bc_title='".$setLinkTitle."'";
              $retup = mysqli_query($link, $retsql);
              //$retup = true;
              if($retup){
                $commsql = "UPDATE `commcheck` SET bc_id='".$setLinkID."', comm_status='1' WHERE comm_id='".intval($row['comm_id'])."'";
                $commup = mysqli_query($link, $commsql);
                //$commup = true;
                if($commup){
                  $cTotalDone++;
                  $tr .= ' style="color:green;"';
                } else {
                  $tr .= ' style="color:red;"';
                  $cTotalFailed++;
                }
              } else {
                $tr .= ' style="color:red;"';
                $cTotalFailed++;
              }

            }
 */           
          } else {
            $tr .= ' style="color:orange;"';
            $cTotalNoMatch++;
          }
          $tr .= '>';
          $tr .= '<td style="border-top:#CCCCCC 1px solid;">'.$i.'.</td>';
          $tr .= '<td style="border-top:#CCCCCC 1px solid;">'.$setLinkID.'</td>';
          $tr .= '<td style="border-top:#CCCCCC 1px solid;">'.$setLinkDistrict.'</td>';
          $tr .= '<td style="border-top:#CCCCCC 1px solid;">'.$setLinkTitle.'</td>';
          $tr .= '<td style="border-top:#CCCCCC 1px solid;">'.$bcrow['bc_title'].'</td>';
          $tr .= '<td style="border-top:#CCCCCC 1px solid;">'.$row['comm_puretitle'].' / '.$bcrow['bc_puretitle'].'</td>';
          $tr .= '<td style="border-top:#CCCCCC 1px solid;">'.$row['comm_purephase'].' / '.$bcrow['bc_purephase'].'</td>';
          $tr .= '<td style="border-top:#CCCCCC 1px solid;">'.$row['comm_pureext'].' / '.$bcrow['bc_pureext'].'</td>';
          $tr .= '</tr>';
          echo $tr;
          $i++;
        }
        echo '</table>';
        $cTotalRowNum=$i;
        $timeUse = time() - $startTime;
        echo "<hr><br>Total Row Numbers: ".$cTotalRowNum."<br><hr><br>"; 
        echo "Ready: ".$cTotalReady."<br>";
        echo "Done: ".$cTotalDone."<br>";
        echo "Failed: ".$cTotalFailed."<br><hr><br>";
        echo "NO Match: ".$cTotalNoMatch."<br><hr><br>";
        echo "<hr>花費時間: ".$timeUse." 秒<br><br><br><br>";
        echo "<audio autoplay='true' style='display:none;'><source src='alert.wav'></audio>";


        /*
        $sql = "SELECT * FROM `data_buildcase`";
         $sql .= " WHERE bc_id=43117";
        $sql .= " ORDER BY `bc_id` ASC";
        //$sql .= " LIMIT 0,25";
        $result = mysqli_query($link, $sql);
        //$row_cnt = mysqli_num_rows($result);
        //mysqli_data_seek($result, 0);  // 指標移到第一筆資料ret_district ".$row['XXXX']."
        $sqlHead = "INSERT INTO fh.buildcase ('bc_id', 'bc_published', 'bc_updated', 'bc_city', 'bc_district', 'bc_title', 'bc_subtitle', 'bc_subtitle2', 'bc_mode', 'bc_type', 'bc_usefor', 'bc_addr', 'bc_lat', 'bc_lng', 'bc_area', 'bc_totalnum', 'bc_pratio', 'bc_coverage', 'bc_far', 'bc_completion', 'bc_state', 'bc_plan', 'bc_layernum', 'bc_des', 'bc_unitprice', 'bc_totalprice', 'bc_pprice', 'bc_sprice', 'bc_selling', 'bc_loan', 'bc_meetadd', 'bc_parkratio', 'bc_parkplan', 'bc_parknum', 'bc_building', 'bc_abovemin', 'bc_abovemax', 'bc_undermin', 'bc_undermax', 'bc_direction', 'bc_pmc', 'bc_rmc', 'bc_mfee', 'bc_structure', 'bc_zoning', 'bc_blicense', 'bc_ulicense', 'bc_materials', 'bc_inpublic', 'bc_pile', 'bc_trash', 'bc_investor', 'bc_constructer', 'bc_saleteam', 'bc_cdesign', 'bc_vdesign', 'bc_pdesign', 'bc_ldesign', 'bc_mrt', 'bc_hiway', 'bc_speedway', 'bc_thsr', 'bc_tra', 'bc_othertraffic', 'bc_edu', 'bc_market', 'bc_omarket', 'bc_outpublic', 'bc_circle', 'bc_medical', 'bc_gov', 'bc_pack', 'bc_image64', 'bc_status', 'bc_similar', 'bc_similaradd', 'bc_similarid', 'bc_infomark', 'bc_imagemark', 'bc_updateinfo', 'bc_distance') VALUES";
        $sqlFoot = ",";
        $sqlBR = "<br>";
        $sqlFull=$sqlHead.$sqlBR;
        $i=1;
        while($row = mysqli_fetch_assoc($result)){

          $sqlBody = "(".$row['bc_id']."";
          $sqlBody .= ", '".$row['bc_published']."'";
          $sqlBody .= ", '".$row['bc_updated']."'";
          $sqlBody .= ", '".$row['bc_city']."'";
          $sqlBody .= ", '".$row['bc_district']."'";
          $sqlBody .= ", N'".$row['bc_title']."'";
          $sqlBody .= ", N'".$row['bc_subtitle']."'";
          $sqlBody .= ", N'".$row['bc_subtitle2']."'";
          $sqlBody .= ", ".$row['bc_mode'];
          $sqlBody .= ", ".$row['bc_type'];
          $sqlBody .= ", ".$row['bc_usefor'];
          $sqlBody .= ", N'".$row['bc_addr']."'";
          $sqlBody .= ", '".$row['bc_lat']."'";
          $sqlBody .= ", '".$row['bc_lng']."'";
          $sqlBody .= ", ".$row['bc_area']."";
          $sqlBody .= ", ".$row['bc_totalnum']."";
          $sqlBody .= ", '".$row['bc_pratio']."'";//bc_pratio
          $sqlBody .= ", '".$row['bc_coverage']."'";//bc_coverage
          $sqlBody .= ", '".$row['bc_far']."'";//bc_far
          $sqlBody .= ", '".$row['bc_completion']."'";//bc_completion
          $sqlBody .= ", ".$row['bc_state']."";//bc_state
          $sqlBody .= ", '".$row['bc_plan']."'";//bc_plan
          $sqlBody .= ", '".$row['bc_layernum']."'";//bc_layernum
          $sqlBody .= ", N'".$row['bc_des']."'";//bc_des//
          $sqlBody .= ", '".$row['bc_unitprice']."'";//bc_unitprice
          $sqlBody .= ", '".$row['bc_totalprice']."'";//bc_totalprice
          $sqlBody .= ", '".$row['bc_pprice']."'";//bc_pprice
          $sqlBody .= ", '".$row['bc_sprice']."'";//bc_sprice
          $sqlBody .= ", N'".$row['bc_selling']."'";//bc_selling
          $sqlBody .= ", '".$row['bc_loan']."'";//bc_loan
          $sqlBody .= ", N'".$row['bc_meetadd']."'";//bc_meetadd
          $sqlBody .= ", '".$row['bc_parkratio']."'";//bc_parkratio
          $sqlBody .= ", '".$row['bc_parkplan']."'";//bc_parkplan
          $sqlBody .= ", ".$row['bc_parknum']."";//bc_parknum
          $sqlBody .= ", '".$row['bc_building']."'";//bc_building
          $sqlBody .= ", ".$row['bc_abovemin']."";//bc_abovemin
          $sqlBody .= ", ".$row['bc_abovemax']."";//bc_abovemax
          $sqlBody .= ", ".$row['bc_undermin']."";//bc_undermin
          $sqlBody .= ", ".$row['bc_undermax']."";//bc_undermax
          $sqlBody .= ", '".$row['bc_direction']."'";//bc_direction
          $sqlBody .= ", '".$row['bc_pmc']."'";//bc_pmc
          $sqlBody .= ", '".$row['bc_rmc']."'";//bc_rmc
          $sqlBody .= ", '".$row['bc_mfee']."'";//bc_mfee
          $sqlBody .= ", '".$row['bc_structure']."'";//bc_structure
          $sqlBody .= ", N'".$row['bc_zoning']."'";//bc_zoning
          $sqlBody .= ", N'".$row['bc_blicense']."'";//bc_blicense
          $sqlBody .= ", N'".$row['bc_ulicense']."'";//bc_ulicense
          $sqlBody .= ", N'".$row['bc_materials']."'";//bc_materials
          $sqlBody .= ", '".$row['bc_inpublic']."'";//bc_inpublic
          $sqlBody .= ", ".$row['bc_pile']."";//bc_pile
          $sqlBody .= ", '".$row['bc_trash']."'";//bc_trash
          $sqlBody .= ", N'".$row['bc_investor']."'";//bc_investor
          $sqlBody .= ", N'".$row['bc_constructer']."'";//bc_constructer
          $sqlBody .= ", N'".$row['bc_saleteam']."'";//bc_saleteam
          $sqlBody .= ", N'".$row['bc_cdesign']."'";//bc_cdesign
          $sqlBody .= ", N'".$row['bc_vdesign']."'";//bc_vdesign
          $sqlBody .= ", N'".$row['bc_pdesign']."'";//bc_pdesign
          $sqlBody .= ", N'".$row['bc_ldesign']."'";//bc_ldesign
          $sqlBody .= ", N'".$row['bc_mrt']."'";//bc_mrt
          $sqlBody .= ", N'".$row['bc_hiway']."'";//bc_hiway
          $sqlBody .= ", N'".$row['bc_speedway']."'";//bc_speedway
          $sqlBody .= ", N'".$row['bc_thsr']."'";//bc_thsr
          $sqlBody .= ", N'".$row['bc_tra']."'";//bc_tra
          $sqlBody .= ", N'".$row['bc_othertraffic']."'";//bc_othertraffic
          $sqlBody .= ", N'".$row['bc_edu']."'";//bc_edu
          $sqlBody .= ", N'".$row['bc_market']."'";//bc_market
          $sqlBody .= ", N'".$row['bc_omarket']."'";//bc_omarket
          $sqlBody .= ", N'".$row['bc_outpublic']."'";//bc_outpublic
          $sqlBody .= ", N'".$row['bc_circle']."'";//bc_circle
          $sqlBody .= ", N'".$row['bc_medical']."'";//bc_medical
          $sqlBody .= ", N'".$row['bc_gov']."'";//bc_gov
          $sqlBody .= ", N'".$row['bc_pack']."'";//bc_pack
          $sqlBody .= ", '".$row['bc_image64']."'";//bc_image64
          $sqlBody .= ", ".$row['bc_status']."";//bc_status
          $sqlBody .= ", N'".$row['bc_similar']."'";//bc_similar
          $sqlBody .= ", N'".$row['bc_similaradd']."'";//bc_similaradd
          $sqlBody .= ", ".$row['bc_similarid']."";//bc_similarid
          $sqlBody .= ", ".$row['bc_infomark']."";//bc_infomark
          $sqlBody .= ", ".$row['bc_imagemark']."";//bc_imagemark
          $sqlBody .= ", ".$row['bc_updateinfo']."";//bc_updateinfo
          $sqlBody .= ", '".$row['bc_distance']."'";//bc_distance
          $sqlBody .= ")";
          if($i%100==0){//
            if($i==53395){ $sqlBody = $sqlBody.';'; }
            else { $sqlBody = $sqlBody.';'.$sqlBR.$sqlHead.$sqlBR; }
          } else {
            if($i==53395){ $sqlBody = $sqlBody.';'; }
            else { $sqlBody  = $sqlBody.','.$sqlBR; }
          }

          $sqlFull .= $sqlBody;


          $i++;
        }

        echo $sqlFull;
        */



  ?>
  </div>
</body>
</html>

