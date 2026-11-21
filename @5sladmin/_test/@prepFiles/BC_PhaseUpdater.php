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
        <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/BC_PhaseUpdater.php';">重置</button>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
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
        $tableName = "commcheck";//commcheck//data_buildcase
        $preFix = "comm";//comm//bc
        $startTime = time();
        $cTotalRowNum=0;
        $cTotalReady = 0;
        $cTotalDone = 0;
        $cTotalFailed = 0;
        $cTotalNoMatch = 0;
        $sql = "SELECT * FROM `commcheck` WHERE ";
        $sql .= "comm_status<=5 AND comm_status!=1 AND ";
        //$sql .= "comm_status=1 AND bc_id=0 AND ";
        $sql .= "comm_check=1";
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
          $bcsql .= "bc_status<=5 AND ";
          //$bcsql .= "bc_check=1 AND ";
          $bcsql .= "bc_district='".$row['comm_district']."' AND ";
          $bcsql .= "bc_puretitle LIKE '%".$row['comm_puretitle']."%' AND ";
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
          $bcsql .= "bc_purephase='".$row['comm_purephase']."'";
          if(!empty($row['comm_pureext'])){
            $bcsql .= " AND (bc_pureext='".$row['comm_pureext']."' OR ";
            $bcsql .= "REPLACE(REPLACE(bc_pureext, '區', ''),'棟','') = '".$getLikeExt."')";
          }
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

            $setLinkID = intval($bcrow['bc_id']);
            $setLinkDistrict = trim($bcrow['bc_district']);
            /*
            $commsql = "UPDATE `commcheck` SET bc_id='".$setLinkID."' WHERE comm_id='".intval($row['comm_id'])."'";
            $commup = mysqli_query($link, $commsql);
            $commup = true;
            if($commup){
              $tr .= ' style="color:green;"';
              $cTotalDone++;
            }
            */
            if($row['comm_status']==1 && !empty($row['bc_id'])){
              $tr .= ' style="color:purple;"';
              $cTotalReady++;
            } else {
              //action
              //$retsql = "UPDATE `data_aprret` SET bc_id='".$setLinkID."',ret_status='2',ret_check='1' WHERE ret_district='".$setLinkDistrict."' AND bc_title='".$setLinkTitle."'";
              //$retup = mysqli_query($link, $retsql);
              $retup = true;
              if($retup){
                //$commsql = "UPDATE `commcheck` SET bc_id='".$setLinkID."', comm_status='1' WHERE comm_id='".intval($row['comm_id'])."'";
                //$commup = mysqli_query($link, $commsql);
                $commup = true;
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
          $tr .= '<td style="border-top:#CCCCCC 1px solid;">'.$row['comm_puretitle'].'</td>';
          $tr .= '<td style="border-top:#CCCCCC 1px solid;">'.$row['comm_purephase'].'</td>';
          $tr .= '<td style="border-top:#CCCCCC 1px solid;">'.$row['comm_pureext'].'</td>';
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


  ?>
  </div>
</body>
</html>

