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
    if($link){
        mysqli_query($link,'SET NAMES utf8');
        echo "正確連接資料庫";
    }
    else {
        echo "不正確連接資料庫</br>" . mysqli_connect_error();
    }
    // 關閉連線
    $locJS=_fn_getCachedJsonDatac("locations.json");
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

    <form id="form_commTOret" name="form_commTOret" action="#" method="post">
        <input type="hidden" id="doAct" name="doAct" value="1">
        <button type="button" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;" onClick="
            if(confirm('確定執行操作嗎？')){
                doAction();
            }
        ">ACTION</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="
            if(confirm('確定重置嗎？')){
                    window.location.href ='http://localhost:8888/commComparison.php';
            }
        ">RESET</button>
        <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="
        if(confirm('確定返回首頁嗎？')){
            window.location.href ='http://localhost:8888/';
        }
        ">HOME</button>
    </form>

    <hr>
        
    <?php
    $lastID=0;
    //$startID=4508;
    $stopID=34982;
    //echo (isset($_POST['doAct']) && !empty($_POST['doAct']))?$_POST['doAct']:0;
    if(!isset($_POST['doAct']) || empty($_POST['doAct'])){
        echo "請點擊 「 ACTION 」按鈕執行動作！！";
    } else {
        $startTime = time();
        $cTotalRowNum=0;
        $chkDoneOpt=0;
        $sql = "SELECT * FROM commcheck WHERE bc_id>0 AND comm_title<>'' AND comm_check='5'";
        //$sql = "SELECT comm_district,comm_title, COUNT(*) c FROM commcheck WHERE comm_title<>'' AND comm_check<>'' GROUP BY comm_district,comm_title HAVING c > 1 ";
        //$sql .= " WHERE comm_check=5"; 
        //$sql .= " AND comm_title<>''"; 
        $sql .= " ORDER BY comm_id ASC";
        $sql .= " LIMIT 0,500";
        //$sql .= " AND comm_id BETWEEN 10000 AND 35000";
        $result = mysqli_query($link, $sql);
        echo '<table>';
        echo '<tr>';
        echo '<td style="border-top:#CCCCCC 1px solid;">NO.</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">CommID</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">District</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">Title</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">SubTitle</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">SubTitle2</td>';
        echo '<td style="border-top:#CCCCCC 1px solid;">bcID</td>';
        echo '</tr>';

        $i=1;
        while( $row = mysqli_fetch_assoc($result)){
            
            $ssql = "UPDATE data_aprret SET comm_id='".$row['bc_id']."',ret_status=2,ret_check=2";
            $ssql .= " WHERE ret_status=0 AND ret_target<>3 AND ret_district='".$row['comm_district']."' AND (comm_title='".$row['comm_title']."'";
            if(!empty($row['comm_subtitle'])) $ssql .= " OR comm_title='".$row['comm_subtitle']."'";
            if(!empty($row['comm_subtitle2'])) $ssql .= " OR comm_title='".$row['comm_subtitle2']."'";
            $ssql .= ")";
            $sres = mysqli_query($link, $ssql);
            
            //$sres=true;
            if($sres){
                $lastID=$row['comm_id'];
                $csql = "UPDATE commcheck SET comm_check='1' WHERE comm_id='".$row['comm_id']."'";
                $cup = mysqli_query($link, $csql);
                echo '<tr>';
                echo '<td style="border-top:#CCCCCC 1px solid;">'.$i.'-'.$sres.'.</td>';
                echo '<td style="border-top:#CCCCCC 1px solid;">'.$row['comm_id'].'</td>';
                echo '<td style="border-top:#CCCCCC 1px solid;">'.$row['comm_district'].'</td>';
                echo '<td style="border-top:#CCCCCC 1px solid;">'.$row['comm_title'].'</td>';
                echo '<td style="border-top:#CCCCCC 1px solid;">'.$row['comm_subtitle'].'</td>';
                echo '<td style="border-top:#CCCCCC 1px solid;">'.$row['comm_subtitle2'].'</td>';
                echo '<td style="border-top:#CCCCCC 1px solid;">'.$row['bc_id'].'</td>';
                echo '</tr>';
                $chkDoneOpt++;
            }
            $cTotalRowNum++;
            $i++;

        }
        echo '</table>';
        $timeUse = time() - $startTime;
        echo "<hr><br>Total Row Numbers: ".$cTotalRowNum."<br>"; 
        echo "Done: ".$chkDoneOpt."<br><hr><br>";
        echo "<hr>花費時間: ".$timeUse." 秒<br><br><br><br>";
        echo "<audio autoplay='true' style='display:none;'><source src='alert.wav'></audio>";
    }

    mysqli_close($link);
//echo $lastID;
?>
    
</div>
<script> 
    function doAction(){
        //document.getElementById("doAct").value='0';34982
        document.getElementById("form_commTOret").submit();
    }
    <?php if($lastID>0 && $lastID<$stopID) { echo "const myTimeout = setTimeout(doAction, 5000);"; } ?>
</script>

</body>
</html>