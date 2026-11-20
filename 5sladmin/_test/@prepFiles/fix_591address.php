<?php
/////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////// 更改來源中錯誤的住址  ///////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////
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
    $locJS=_fn_getCachedJsonDatac("locations.json");
    $startTime = time();
    $fromID = isset($_POST['fromID']) ? $_POST['fromID'] : '';
    $toID = isset($_POST['toID']) ? $_POST['toID'] : '';

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
    <h2>更改來源中錯誤的住址</h2>
    <form id="form_filetodb" name="form_filetodb" action="#" method="post">
        <table>
            <tr>
                <td style="padding-right:20px;border-right:#333333 1px solid;">
                    起始ID: <input type="text" id="fromID" name="fromID" style="width:100px;" value="<?php echo $fromID; ?>"><br><br>
                    終止ID: <input type="text" id="toID" name="toID" style="width:100px;" value="<?php echo $toID; ?>">
                </td>
                <td style="padding-left:40px;">
                    <button type="submit" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">執行修復</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/fix_591address.php';">RESET</button>
                    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
                </td>
            </tr>
        </table>
    </form>
    <hr>
<?php
        if(empty($fromID) || empty($toID)){
            echo "/尚未設定ID區段！"; 
            //echo "<script>window.document.getElementById('loading').style.display = 'none';</script>";
            exit();
        }
        $sql = "SELECT * FROM `data_buildcase`";
        $sql .= " WHERE bc_infomark='1'";
        //$sql .= " AND bc_year=101";
        $sql .= " AND bc_id BETWEEN ".$fromID." AND ".$toID."";
        $sql .= " ORDER BY bc_id ASC";
        $result = mysqli_query($link, $sql);
        $row_cnt = mysqli_num_rows($result);
        mysqli_data_seek($result, 0);  // 指標移到第一筆資料
        //if(!$row_cnt || $row_cnt<=0){ continue; }


        $cTotalRows = 0;
        $fixnum = 0;
        $cErrorCount = 0;
        $cNoneedCount = 0;

        while($bcrow = mysqli_fetch_assoc($result)){
            $setCityText = !empty($bcrow['bc_city']) ? $locJS[$bcrow['bc_city']]['text'] : '';
            $setDistText = !empty($bcrow['bc_city'])&&!empty($bcrow['bc_district']) ? $locJS[$bcrow['bc_city']]['district'][$bcrow['bc_district']] : '';
            $return = _fn_get591_fixadd($bcrow['bc_title'],$bcrow['bc_city'],$bcrow['bc_district'],$setCityText,$setDistText);
            //$return = false;
            if($return !== false){
                if($return != $bcrow['bc_addr']){
                    $up = mysqli_query($link, "UPDATE `data_buildcase` SET bc_addr='".$return."' WHERE bc_id=".$bcrow['bc_id'] );
                    //$up = true;
                    if($up){
                        $fixnum++;
                        echo 'Done  @@_'.$bcrow['bc_id'].'_'.$bcrow['bc_title'].'_'.$return.'(原：'.$bcrow['bc_addr'].')<br>'; 
                    } else { 
                        $cErrorCount++;
                        echo '<span style="color:orange;">Error @@_'.$bcrow['bc_id'].'_'.$bcrow['bc_title'].'_'.$return.'(原：'.$bcrow['bc_addr'].')</span><br>'; 
                    }
                } else {
                    echo '<span style="color:green;">Pass @@_'.$bcrow['bc_id'].'_'.$bcrow['bc_title'].'_'.$return.'(原：'.$bcrow['bc_addr'].')</span><br>'; 
                    $cNoneedCount++;
                }
            } else {
                $cErrorCount++;
                echo '<span style="color:red;">Error @@_'.$bcrow['bc_id'].'_'.$bcrow['bc_title'].'_'.$return.'(原：'.$bcrow['bc_addr'].')</span><br>';
            }

            $cTotalRows++;
            //usleep(500000);
        }




    $timeUse = time() - $startTime;
    echo "<hr>";
    echo date('Y-m-d H:s:i').' Done!';
    echo "<hr>檔案紀錄筆數: ".$cTotalRows." 筆";
    echo "<hr>共修正資料筆數: ".$fixnum." / ".$cTotalRows." 筆";
    echo "<hr>無需修正資料筆數: ".$cNoneedCount." / ".$cTotalRows." 筆";
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

