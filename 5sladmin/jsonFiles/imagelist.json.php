<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//$imglist = $DB->single("SELECT sj_json FROM sys_json WHERE sj_code=?",array('imglist'));
//$imglist = $DB->single("SELECT xj_json FROM sys_json WHERE xj_code=?",array('sysimglist'));
////echo $imglist;
$imglist = $DB->query("SELECT * FROM data_vdoimg WHERE vi_type=? AND vi_status=?",array(1,1));
$arrayString = '{"statusCode":200,"result":[';
$i = 0; //以$i為$nameList這個陣列的索引值
$num = count($imglist);
while ($i < $num) {
    $rowString = '{';
    $rowString .= '"src":"../uploadFiles/'.$imglist[$i]['vi_src'].'",';
    $rowString .= '"name":"'.$imglist[$i]['vi_name'].'",';
    $rowString .= '"alt":"'.$imglist[$i]['vi_alt'].'",';
    $rowString .= '"tag":"'.$imglist[$i]['vi_tag'].'",';
    $rowString .= '"id":"'.$imglist[$i]['vi_id'].'"';
    $rowString .= '}';
    if($i < $num-1){
        $rowString .= ',';
    }
    $arrayString .= $rowString;
    $i++;
}
$arrayString .= ']}';
echo $arrayString;
?>
