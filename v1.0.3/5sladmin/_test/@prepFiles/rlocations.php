<?php
    error_reporting(E_ALL); 
    header("Content-Type:text/html; charset=utf-8");
	include 'aprret_func.php';
	include 'PHPExcel/Classes/PHPExcel.php';
    $host = 'localhost';
    $dbuser ='root';
    $dbpassword = '1111';
    $dbname = 'jazhome';

    //echo _fn_num_b2h("二十五");exit();
    $link = mysqli_connect($host,$dbuser,$dbpassword,$dbname);
    if($link){
        mysqli_query($link,'SET NAMES uff8');
        echo "正確連接資料庫";
    }
    else {
        echo "不正確連接資料庫</br>" . mysqli_connect_error();
    }

//----------------------------------------

/*
$str='dynamic_info={};j.hid=c;j.service_phone="02-55722000";j.build_type=l;j.build_type_str=a;j.price={price:"18.1~37.3",unit:"萬\u002F坪"};j.total_price={price:"788~3,780",unit:g};j.park_price={price:"0~150",unit:g};j.shop_price={price:a,unit:g};j.community_type=h;j.age={content:"33",unit:"年"};j.all_house_num={content:"176",unit:"戶"};j.sell_status="時間待定";j.deal_date=a;j.deal_time=a;j.completion_date="1991年";j.ratio="15~20%";j.jbrate="33.35%";j.purpose_str="住宅=大樓";j.purpose_other2="住家用";j.structural_engine="RC造、其他";j.floor="地上4層";j.build="3棟";j.layout=["一房(21.06坪)","二房(21.06~38.07坪)","三房(34.35~59.97坪)","四房(34.35~82.84坪)","五房(29.4~66.34坪)"];j.meeting_address=a;j.down_pay=a;j.project_pay=a;j.decorate=a;j.lend_rate=a;j.base_area=a;j.base_area_num="0";j.land_division="山坡地保育區;丙種建築用地";j.park=a;j.park_rate=a;j.direction_rule="朝西、朝東、朝東北、朝東南、朝西北、朝西南";j.all_park_num=e;j.build_intro=a;j.facility=["游泳池","中庭花園","閱覽室","會議室"];j.manage_committee="有";j.property_company=a;j.manage_cost={price:"30",unit:"元\u002F戶\u002F月",type:d};j.sell_company=a;j.company="漢陽建設";j.build_company="漢陽營造股份有限公司 負責人:侯西隆";j.construction_company="李祖原";j.landscape_name=a;j.postulate_name=a;j.light_company=a;j.build_remark=a;j.school=a;j.market=a;j.tradition_market=a;j.public_construction=a;j.hot_business_area=a;j.hospital=a;j.government=a;j.other=a;j.subway=a;j.freeway=a;j.rapid_traffic=a;j.speed_railway=a;j.tw_railway=a;j.other_traffic=a;j.address="新北市新店區黎明路50巷1號";j.has_piles_str=a;j.refuse_disposal_str="自行處理（需等垃圾車）";j.license=a;j.use_license="80店使字第01473號";j.park_type_str=a;j.park_count_str=';

preg_match_all('/="(.*?)";/', $str, $match);
if(is_array($match[1])){
    foreach($match[1] AS $oArr){
        if (strpos($oArr, ':') !== false || strpos($oArr, '=') !== false || strpos($oArr, ';') !== false) {
            $nArr=str_replace(':','@-colon-@',$oArr);
            $nArr=str_replace('=','@-equal-@',$nArr);
            $nArr=str_replace(';','@-semicolon-@',$nArr);
            $str=str_replace($oArr,$nArr,$str);

        }else{
            continue;
        }
    }
}  
  echo $str;
  //print_r($setBack);
//echo is_array($match[1]);
 exit();
 */
//-----------------------------------------
    /*
    $link = @mysqli_connect("localhost", "root", "1111");
    if( !$link ) {
        echo "MySQL 連線失敗";
        return;
    }
    else
        echo "MySQL 連線成功<br>";
    */
    
    // 選擇資料庫
    //$db = mysqli_select_db($link, "jazhome");
    // 設定字元集 (這行要有, 不然下面新增資料的中文部分, 會是亂碼)
    //mysqli_query($link, "SET NAMES 'utf8'");
    /*
    // 新增資料 (DML)

    $result = mysqli_query($link, "insert into test_company values(3, '歡迎', '歡迎 tomkuo139')" );
    if( !$result )
        echo "新增資料失敗<br>";
    else
        echo "新增資料成功<br>";
    
    // 查詢資料 (Query)

    $result = mysqli_query($link, "select * from test_company");
    $row_cnt = mysqli_num_rows($result);
    echo "共有 " . $row_cnt . " 筆資料<br>";
    
    // 取得資料 (第幾個欄位 and 欄位值)
    while( $row = mysqli_fetch_row($result) )
    {
        foreach( $row as $item=>$val )  // $item 會是 0, 1, 2, ...
            echo $item . " = " . $val . "<br>";
    }
    
    // 取得資料 (欄位名稱 and 欄位值)
    mysqli_data_seek($result, 0);  // 指標移到第一筆資料
    while( $row = mysqli_fetch_assoc($result) )
    {
        foreach( $row as $item=>$val )  // $item 會是 Column Name
            echo $item . " = " . $val . "<br>";
    }
    */
    // 關閉連線
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
    
<?php
    $locJS=_fn_getCachedJsonDatac("locations.json");
    $locStr = '{';

foreach($locJS as $k => $v){

    $locStr .= '"'.$v['text'].'":{<br>"id":"'.$k.'",<br>';
    $locStr .= '"dist":{<br>';
    $si=1;
    foreach($v['district'] as $ik => $iv){
        $locStr .= '"'.$iv.'":"'.$ik.'"';
        if($si!=count($v['district'])){$locStr .= ',';}
        $locStr .= '<br>';
        $si++;
    }
    $locStr .= '}<br>},<br>';
}
$locStr .= '}';
echo $locStr;



?>
</div>
</body>
</html>