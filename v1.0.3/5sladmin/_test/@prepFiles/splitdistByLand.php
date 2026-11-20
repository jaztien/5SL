<?php
//////////////////////////////////////////////////////////////////////////////
//////////////////////////// 依土地劃分位置分區 /////////////////////////////////
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
    //$fromID = isset($_POST['fromID']) ? $_POST['fromID'] : '';
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

<?php
/*
SELECT * FROM `data_distsplit_17` WHERE `dt_distcode`='' AND `dt_K`='土地' AND `dt_A` REGEXP '港南段|華江段|虎山段|富山段|虎山段|樹下段|香雅段|忠孝段|中興段|唐高段|信義段|仁愛段|三姓段|香中段|香美段|香濱段|大庄段|美山段|師院段|柑林段|醫專段|朝山段|青山段|香村段|東香段|長興段|海山段|大湖段|茄苳段|鹽水段|湖港段|五福段|延壽段|南港段|麗園段|內湖段|南興段|中隘段|見樂段'
SELECT * FROM `data_distsplit_17` WHERE `dt_distcode`='' AND `dt_K`='土地' AND `dt_A` REGEXP '十塊寮|蟹仔捕小|新港段|竹港段|南華段|海濱段|中寮段|南寮段|西濱段|康朗段|康樂段|港北段|東濱段|古賢段|金雅段|舊社段|武陵段|北門段|福林段|士林段|民富段|中雅段|崙子段|南勢段|聚吉段|中山段一小段|中山段二小段|中山段三小段|西門段一小段|西門段二小段|西門段三小段'
UPDATE `data_distsplit_17` SET `dt_distcode`='1702' WHERE `dt_distcode`='' AND `dt_K`='土地' AND `dt_A` REGEXP '十塊寮|蟹仔捕小|新港段|竹港段|南華段|海濱段|中寮段|南寮段|西濱段|康朗段|康樂段|港北段|東濱段|古賢段|金雅段|舊社段|武陵段|北門段|福林段|士林段|民富段|中雅段|崙子段|南勢段|聚吉段|中山段一小段|中山段二小段|中山段三小段|西門段一小段|西門段二小段|西門段三小段'
SELECT * FROM `data_distsplit_17` WHERE `dt_distcode`='' AND `dt_K`='土地' AND `dt_A` REGEXP '溪橋段|民主段一小段|復中段|中央段|中華段|新興段一小段|新興段|竹蓮段|東山段一小段|東山段二小段|東光段|東明段|台肥段|水源段|建華段|光鎮段|育賢段|仙宮段|柴橋段|古峰段|高翠段|科學段一小段|科學段二小段|科學段三小段|成功段|大學段|翠湖段|高峰段|南湖段|光復段|隆恩段|千甲段|復興段|國道段|仙水段|科園段|龍山段|光武段|長春段|介壽段|關東段|東橋段|金山段|科技段|風空段|新莊段|親仁段一小段|親仁段二小段|榮光段一小段|榮光段二小段|榮光段三小段|東門段一小段|東門段二小段|南門段一小段'
UPDATE `data_distsplit_17` SET `dt_distcode`='1701' WHERE `dt_distcode`='' AND `dt_K`='土地' AND `dt_A` REGEXP '溪橋段|民主段一小段|復中段|中央段|中華段|新興段一小段|新興段|竹蓮段|東山段一小段|東山段二小段|東光段|東明段|台肥段|水源段|建華段|光鎮段|育賢段|仙宮段|柴橋段|古峰段|高翠段|科學段一小段|科學段二小段|科學段三小段|成功段|大學段|翠湖段|高峰段|南湖段|光復段|隆恩段|千甲段|復興段|國道段|仙水段|科園段|龍山段|光武段|長春段|介壽段|關東段|東橋段|金山段|科技段|風空段|新莊段|親仁段一小段|親仁段二小段|榮光段一小段|榮光段二小段|榮光段三小段|東門段一小段|東門段二小段|南門段一小段'


SELECT * FROM `data_distsplit_32` WHERE `dt_distcode` = '' AND NOT `dt_K`='土地' AND `dt_A` LIKE '%南門段四小段%'
SELECT * FROM `data_distsplit_32` WHERE `dt_distcode` = '' AND NOT `dt_K`='土地'
*/

    $doUpdate = 1;

    $basefolder = "aprretFiles/Original/1130326/";
    $tableName = "data_distsplit_17";
    $subfolder = "17/1701";
    $cityname = "新竹市";
    $landName = "南門段四小段";
    $setDistCode = 1701;//1702北區  1703香山區
    $codeArr = array("東區"=>"1701","北區"=>"1702","香山區"=>"1703");
///////////////////////////////////////////////////////////////////////

        // 取得資料 (欄位名稱 and 欄位值)
        $sql = "SELECT * FROM `".$tableName."`";
        $sql .= " WHERE dt_distcode='' AND dt_K='土地' AND dt_A LIKE '%".$landName."%'";
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
            $addrName = _fn_str_f2h($flist['dt_A']);
            $addrName = get_between($addrName,$landName,'地號');
            if(strpos($addrName,'-')!==false){$addrName = strchr($addrName,'-',true);}
            $baseNo = intval($addrName);
            /*
            if(
                ($baseNo >=19 && $baseNo <=29) ||
                $baseNo == 7 || $baseNo == 9
                ){ $distCode = 1702;}
            else{ $distCode = 1703;}
*/

            //if($baseNo<=39){ $distCode = 1701; }
            //elseif($baseNo==71){ $distCode = 1702;}
            /*
            if(
                ($baseNo%2==0 && ($baseNo >= 734 && $baseNo <= 746)) ||
                ($baseNo%2!=0 && ($baseNo >= 743 && $baseNo <= 753)) ||
                ($baseNo == 739 && $extraNo>0 && $extraNo%2==0)
            ){ $distCode = 1702; }
            */
            /////////////////////////////////////// DO UPDATE //////////////
            if($doUpdate==1){
                $up = mysqli_query($link, "UPDATE `".$tableName."` SET dt_distcode='".$distCode."' WHERE dt_id=".$flist['dt_id'] );
                if($up){
                    $cTotalDupRows++;
                    echo 'Done!! @@ '.$flist['dt_id'].' @@ '.$distCode.' @@ '.$flist['dt_A'].'<br>';
                } else{
                    $cErrorCount++;
                    echo 'Failed!! @@ '.$flist['dt_id'].' @@ '.$flist['dt_A'].'<br>';
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
    echo "<hr>共產生資料筆數: ".$cTotalDupRows." / ".$cTotalRowNum." 筆";
    echo "<hr>錯誤資料筆數: ".$cErrorCount." 筆";
    echo "<hr>花費時間: ".$timeUse." 秒<br><br><br><br>";
    
    //echo "<hr><br>Opt Doned: ".$chkDoneOpt."<br><br><br><br>";
    mysqli_close($link);
    ?>
    <br><br><br><br>
    </div>
</body>
</html>

