<?php
error_reporting(E_ALL);
/*
header("Content-Type:text/html; charset=utf-8");
$serverName="localhost\SQLEXPRESS, 1433";
$connectionInfo=array("Database"=>"FH","UID"=>"fh","PWD"=>"fullhouse2023","CharacterSet" => "UTF-8");
$conn=sqlsrv_connect($serverName,$connectionInfo);
    if($conn){
        echo "Success!!!";
    }else{
        echo "Error!!!<br />";
        die(print_r(sqlsrv_errors(),true));
    } 
*/ 
//require_once('../includeFiles/init.inc.php');
session_start();
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
//require_once('../includeFiles/QPay.inc.php');
//24.949807931325,121.48293524888
//phpinfo();
/*
if(!isset($_POST['pass'])){
    return false;
}
*/
        $ifCate = isset($_POST['ifCate']) ? $_POST['ifCate'] : "";//單字元 int 會員種類
        $ifCotype = isset($_POST['ifCotype']) ? $_POST['ifCotype'] : "";//數字組以逗號分隔 string 合作方種類 
        $ifExpert = isset($_POST['ifExpert']) ? $_POST['ifExpert'] : "";//數字組以逗號分隔 string 專業服務項目
        $arr = '';
        if(is_null($_POST['search'])||trim($_POST['search'])==''){
            exit();
        }
        $search = isset($_POST['search']) ? $_POST['search'] : "";
        $sql="SELECT a.mb_id, a.mb_title, b.mb_cate, b.mb_cotype, b.mb_coexpert FROM fh.members AS a INNER JOIN fh.mb_info AS b ON b.mb_id=a.mb_id WHERE";
        if (is_numeric($search)) {
            $sql .= " a.mb_id=?";//主鍵欄不得鍵入非數值，否則 WHERE 不起作用【bug】
        } else {
            $sql .= " a.mb_title LIKE '%".$search."%'";
        }
        
        //$sql="SELECT * FROM fh.members WHERE mb_title LIKE '%".$search."%'";
        //$sql.=" OR a.mb_account LIKE '%".$search."%'";
        //$sql.=" OR b.mbinfo_name LIKE '%".$search."%'";
        //$sql.=" OR b.mbinfo_email LIKE '%".$search."%'";
        ////$sql.=" OR b.mbinfo_line LIKE '%".$search."%'";
        ////$sql.=" OR b.mbinfo_fb LIKE '%".$search."%'"; 
        //if(is_numeric($search)) $sql.=" OR a.mb_id=?";//主鍵欄不得鍵入非數值，否則 WHERE 不起作用【bug】

            if($ifCate) $sql.=" AND b.mb_cate='".$ifCate."'";//當 $ifCate 賦值時，「會員種類」欄位為其一的搜尋條件
            if ($ifCotype!="") {
                $coTypeArr = explode(',', $ifCotype);
                foreach ($coTypeArr as $key => $value) {
                    //$sql .= " AND TRANSLATE(b.mb_cotype, '[]', ',,') LIKE '%,".$value.",%'";
                    $sql .= " AND EXISTS ( SELECT 1 FROM OPENJSON(b.mb_cotype) WHERE value = '".$value."')";
                }
            }//當 $ifCotype 賦值時，「合作方種類」欄位為其一的搜尋條件


/*
            if ($ifExpert) {
                $coExpertArr = explode(',', $ifExpert);
                foreach ($coExpertArr as $key => $value) {
                    $sql .= " AND TRANSLATE(b.mb_coexpert, '[]', ',,') LIKE '%,".$value.",%'";
                }
            }//當 $ifCotype 賦值時，「合作方種類」欄位為其一的搜尋條件
*/
        echo '[{"id":"0","text":"'.$sql.'"}]';exit();
        $res = $DB->query($sql,array($search));
        $totalMB = count($res);

        if($totalMB>0){
            $arr = '[';
            $i = 0; //以$i為$nameList這個陣列的索引值
            while($i < $totalMB){ 
                //$arr .= '{"id":"'.$res[$i]['mb_id'].'","text":"【ID:'.$res[$i]['mb_id'].'】'.$res[$i]['mbinfo_name'].'(帳戶:'.$res[$i]['mb_account'].')"}';//使用Select2插件無論是key或Val，都必須加「"」。
                $arr .= '{';
                $arr .= '"id":"'.$res[$i]['mb_id'].'",';
                $arr .= '"text":"';
                $arr .= '【ID:'.$res[$i]['mb_id'].'】';//會員ID
                $arr .= $res[$i]['mb_title'];//會員暱稱
                $arr .= '['.$res[$i]['mb_cotype'].']';//會員合作方種類
                $arr .= '['.$res[$i]['mb_coexpert'].']';//會員專業服務項目
                $arr .= '"';
                $arr .= '}';//使用Select2插件無論是key或Val，都必須加「"」。
                if($i!=$totalMB-1){ $arr .= ','; }
                $i++;
            }
           $arr .= ']';
        }
        echo $arr;    



exit();

$baseString = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

$charArray = str_split('JOZ');



for($i = 0; $i < count($charArray); $i++){
    $nowPosition = strpos($baseString, $charArray[$i]) + 1;
    $prevPosition = $i==0 ? 0 : strpos($baseString, $charArray[$i-1]) + 1;
    $gap = $nowPosition - $prevPosition;
    
    if($gap > 10){
        break;
    }
    echo $gap."<br>";
}

  echo "<pre>";
  print_r($charArray);
  echo "</pre>";






exit();


$idString2 = '870,3378,6123,6500,6875,7163,7199,7842,8148,8454,9094,9630,10124,10280,10310,10344,10364,11185,11551,13237,14217,14518,14979,15017,15175,15386,15626,16891,16956,17470,17471,17558,19186,19187,19211,19333,19334,19337,20109,20319,21516,21548,21681,21701,21729,22102,22246,23116,23349,23982,24002,25077,25880,25930,26520,27047,27155,27435,28614,28836,28837,28858,29190,29202,29834,30470,30786,31254,31282,31295,31852,32038,32195,32645,32699,33303,34099,34171,34330,34386,35026,36109,36301,36309,36619,36846,36963,37568,37789,37896,38447,38547,39006,39007,39008,39009,39043,39938,40430,40559,40594,40751,40906,41730,41782,41790,41886,42369,42458,42617,42631,42662,42664,42965,42970,43049,43325,43360,43888,44065,44082,44087,43969,44263,44264,44506,44565,44568,44614,44902,44931,44924,44927,45356,45767,46254,46702,46703,46704,46705,46708,47137,47140,47284,48020,48141,48290,48344,48347,48353,48387,48411,48420,48749,48988,49261,50425,51002,51123,51189,51222,51223,51426,51460,51800,51887,51979,53149,53150,53315';
$idArray2 = explode(',',$idString2);

foreach ($idArray2 as $key => $value) {
  $inArr = array(
    "prep_m_bcid" => $value,
  );
  $invIns = $DB->insert('fh.prep_bcmodel',$inArr);
}





exit();

$returnSTR = 'NOTHING CHANGE!';
$title = '恆美(山水居)第VI、VI期';
$phase = '';

$num = 21;
if(preg_match('/.*[a-zA-Z]+\-(([a-zA-Z]+)|([0-9]{1,3}))([^a-zA-Z0-9區棟期]+|$)/iu', 'i-990')==true){
  echo 'matched!';
} else {
  echo 'not matched!';
}

exit();

//$pattern = '/\W(VI)\W|$/i';
/*
$title = str_replace(['VII','II','VIII','III'],['Ⅶ','Ⅱ','Ⅷ','Ⅲ'],$title);
$title = preg_replace('/\W(VI)\W|$/', 'Ⅵ', $title);
$title = preg_replace('/\W(IV)\W|$/', 'Ⅳ', $title);
$title = preg_replace('/\W(IX)\W|$/', 'Ⅸ', $title);
$title = preg_replace('/\W(I)\W|$/', 'Ⅰ', $title);
$title = preg_replace('/\W(V)\W|$/', 'Ⅴ', $title);
$title = preg_replace('/\W(X)\W|$/', 'Ⅹ', $title);
*/
//preg_match('/\W(IV)\W|$/', $title, $matches);
echo preg_replace_callback('/第/u', function($m){ 
  return '@@'.$m[0].'@@';
}, $title);

exit();
  $mr = preg_replace_callback(
        '/[\x{4e00}-\x{9fa5}|,|、]{1}(VI)期{0,1}/u',
        function($m){ return str_replace('VI', 'Ⅵ', $m[0]); },
        $title);


echo "<pre>";
print_r($mr);
echo "</pre>";
//echo $title;
exit();



echo _fn_twd97_to_wgs84(300719.70, 2768441.55);

exit();
/*
  $setNodeURL = "https://www.openstreetmap.org/api/0.6/node/5688170222";
  $getNodeContent = _fn_load_contents($setNodeURL, true);
  $loadNodeXML = simplexml_load_string($getNodeContent);
  $nodeOBJ = $loadNodeXML->node->attributes();
  $nodeCoord = json_decode("[".$nodeOBJ->lat.",".$nodeOBJ->lon."]", true);
  echo count($nodeCoord);
  echo "<pre>";
  print_r($nodeCoord);
  echo "</pre>";
  exit();
*/

$setWayURL = "https://www.openstreetmap.org/api/0.6/way/353678885/3";
$getXMLcontent = _fn_load_contents($setWayURL,true);// Read entire file into string
$loadWayXML = simplexml_load_string($getXMLcontent);// Convert xml string into an object
$wayOBJ = $loadWayXML->way->nd;// Convert into json
$wayOBJnum = count($wayOBJ);
$output = "";
for($i=0; $i < $wayOBJnum; $i++){
  $nodeCode = trim($wayOBJ[$i]->attributes()->ref);
  if(empty($nodeCode)){
    continue;
  }
  $setNodeURL = "https://www.openstreetmap.org/api/0.6/node/".$nodeCode;
  $getNodeContent = _fn_load_contents($setNodeURL, true);
  if($getNodeContent == false){
    continue;
  }
  $loadNodeXML = @simplexml_load_string($getNodeContent);
  $nodeOBJ = $loadNodeXML->node->attributes();
  $nodeCoord = json_decode("[".$nodeOBJ->lat.",".$nodeOBJ->lon."]", true);
  $output .= $nodeCoord[0].",".$nodeCoord[1];
if($i < $wayOBJnum-1){
  $output .= "
";//NOTE：這是換行，不是失手
}

}

//echo $output;
?>

  <textarea class="form-control" id="coordinates" name="coordinates" placeholder="坐標群" rows="10"><?php echo $output; ?></textarea>


<?php
exit();
$coordinates = isset($_POST['coordinates']) ? $_POST['coordinates'] : '';
//$coordinates = nl2br($coordinates);
//$coordArray = explode('<br />', $coordinates);
$coordArray = explode(' ', $coordinates);
//_fn_stringto_geography()
print_r(_fn_stringto_geography($coordinates));
//echo $coordinates;
exit();
//$pres = $DB->query("INSERT INTO fh.coordtest (test_coordinates) VALUES(geography::STGeomFromText('POLYGON((121.4821322 24.9620919,121.4818416 24.9613964,121.4810703 24.9611803,121.4808971 24.9611067,121.4812800 24.9601489,121.4820740 24.9589330,121.4825353 24.9585051,121.4830396 24.9583397,121.4840052 24.9586704,121.4843377 24.9586023,121.4852132 24.9589384,121.4848323 24.9597265,121.4845110 24.9603806,121.4837919 24.9601923,121.4831162 24.9600272,121.4826616 24.9603507,121.4824937 24.9606955,121.4826325 24.9611024,121.4828851 24.9616230,121.4827685 24.9620126,121.4821322 24.9620919))', 4326))", array());
//$pres = $DB->query("INSERT INTO fh.coordtest (test_coordinates) VALUES(geography::STGeomFromText('POINT(121.4821322 24.9620919)', 4326))", array());
$pres = $DB->query("SELECT test_coordinates.EnvelopeCenter().Lat AS lat,test_coordinates.EnvelopeCenter().Long AS lng FROM fh.coordtest WHERE test_id = ?", array(2));
//使用CURL截取網頁內容 @g.EnvelopeCenter().Lat

    //$setURL = "https://www.openstreetmap.org/way/261259401";
    //$commGet = _fn_load_contents($setURL,true);//取得搜尋結果頁面的所有內容
//[lat] => 24.960201199782443
//[lng] => 121.48290595534272
echo "<pre>";
print_r($pres);
echo "</pre>";
exit();

echo $commGet;
/*

$pres = $DB->query("
SELECT
 test_coordinates.STDistance(geography::STGeomFromText('Point(121.48419369910161 24.95877879854515)', 4326)) AS disTance
 ,test_coordinates.STGeometryType() AS coordType 
  FROM fh.coordtest WHERE test_id = ?", array(1));//121.513999940000005 25.048799509999998
*/


//geography::STGeomFromText('POLYGON((121.48250546414639 24.962270301931753,121.48178686088256 24.961468089012836,121.4808978236427 24.961201316103235,121.48094310608357 24.960485773274605,121.48279612534824 24.958249144087244,121.48547720712833 24.958849139038843,121.48512168151467 24.9602824414218,121.48440306591255 24.960453887957723,121.4830793003297 24.960049273749718,121.48244389284996 24.96074191760051,121.48288262659051 24.961777447194567,121.48250546414639 24.962270301931753))', 4326)
//121.48419369910161 24.95877879854515






//echo "<pre>";
//print_r($commGet);
//echo "</pre>";
exit();



$res = $DB->query("SELECT merch_add,merch_coordinates.STDistance(geography::STGeomFromText('Point(121.51399994 25.04879951)', 4326)) AS Distance FROM fh.merchandise WHERE merch_coordinates.STDistance(geography::STGeomFromText('Point(121.51399994 25.04879951)', 4326)) < ?", array(500));
//$res = $DB->query("SELECT merch_add,merch_coordinates.STDistance(geography::STGeomFromText('Point(121.51399994 25.04879951)', 4326)) AS Distance FROM fh.merchandise WHERE merch_coordinates.STDistance(geography::STGeomFromText('Point(121.51399994 25.04879951)', 4326)) < ?", array(500));

echo "<pre>";
print_r($res);
echo "</pre>";


  $Ssql = "SELECT * FROM (
          SELECT merch_add,merch_coordinates.STDistance(geography::STGeomFromText('Point(".$pres['long']." ".$pres['lat'].")', 4326)) AS disTance
          FROM fh.merchandise
          WHERE merch_status >= ?
      ) AS c WHERE c.disTance < ?"; 
  $Sres = $DB->query($Ssql, array(0, 500));

echo "<pre>";
print_r($Sres);
echo "</pre>";


exit();


//scrc_coordinates = geography::STGeomFromText(null, 4326)
$dt = strtotime('2025-05-21 21:28:27');
$up = $DB->query("UPDATE fh.socialcircle SET scrc_coordinates = geography::STGeomFromText(null, 4326) WHERE scrc_id = ?", array(1));

echo "<pre>";
print_r($up);
echo "</pre>";

exit();


echo _fn_getdistance(25.048828717352777,121.51377868222998,24.08291156,120.55843633);
exit();


$hcsql = "SELECT merch_id, opd_id, mb_id";
$hcsql .= " FROM fh.merch_oppdata";
$hcsql .= " WHERE opp_id='6'";//指定為精選好物，ID：6來自 fh.merch_oppdata
$hcsql .= " AND opd_type='2'";//種類為加價購
$hcsql .= " AND opd_mode='1'";//模式為依欄位值計時（精選好物僅能使用依欄位值計時模式）
$hcsql .= " AND opd_startdate <= GETDATE() AND opd_enddate >= GETDATE()";//於有效時間內
$hcsql .= " AND opd_status=?";//狀態為啟用
$hCres = $DB->query($hcsql, array(1));//使用 FETCH_COLUMN 來回傳一維陣列, PDO::FETCH_COLUMN
//------------------- 將全部精選好物進行篩濾並分類 -------------------//
$checkOpt = array();
$rsOpt = array();
foreach($hCres AS $key => $value){
    $nowMdID = intval($value['merch_id']);
    $flag = true; //標記是否存在重複值
    if (in_array($nowMdID, $checkOpt)){//比對這個ID是否已經存在於比對陣列中
        $flag = false;//若已經存在則標記為false
        break;
    }
    if($flag){//當標記為true時，表示不存在重複值，繼續進行操作流程
        $checkOpt[] = intval($value['merch_id']);//生成簡易比對陣列
        $preSql = "SELECT merch_id, merch_rsoptions";
        if(!empty($whereStatment)){
            $preSql .= ", (CASE WHEN(";
            $preSql .= "SELECT sm.merch_id FROM fh.merchandise sm WHERE sm.merch_id=m.merch_id";
            $preSql .= $whereStatment;
            $preSql .= ") IS NOT NULL THEN 1 ELSE 0 END) AS topHot";
        }
        $preSql .= " FROM fh.merchandise m WHERE m.merch_id=?";
        $preRes = $DB->row($preSql, array($nowMdID));
        //將結果加入對應銷售模式的陣列
        if($preRes['merch_rsoptions'] == 'S'){//售屋模式
            $rsOpt['S'][] = $value;
            if(!empty($isMatchSearch) && !empty($preRes['topHot'])){
                $rsOpt['S'][] = $value;
            }
        } elseif($preRes['merch_rsoptions'] == 'R'){//組屋模式
            $rsOpt['R'][] = $value;
            if(!empty($isMatchSearch) && !empty($preRes['topHot'])){
                $rsOpt['R'][] = $value;
            }
        } elseif($preRes['merch_rsoptions'] == 'T'){//轉讓模式
            $rsOpt['T'][] = $value;
            if(!empty($isMatchSearch) && !empty($preRes['topHot'])){
                $rsOpt['T'][] = $value;
            }
        }
    }
}
//$_SESSION['hotcase'][date('Y-m-d')] = $rsOpt;//更新或寫入SESSION



echo "<pre>";
print_r($rsOpt);
echo "</pre>";

exit();


$sql = "SELECT tn_title,tn_id,tn_entitle FROM fh.trafficnode WHERE tn_district=? AND tn_type=? AND tn_status=?";
$res = $DB->query($sql, array(1101,1,1), PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
$filterObj = array();
foreach($res as $key => $value){
  $filterObj[] = array('tn_id'=>$value[0]['tn_id'],'tn_title'=>$key,'tn_entitle'=>$value[0]['tn_entitle']);
}



echo "<pre>";
print_r($filterObj);
echo "</pre>";
exit();



exit();
$searchLAT=25.048828717352777;//臺北車站緯度
$searchLNG=121.51377868222998;//臺北車站經度

$sql = "
SELECT * FROM (
  SELECT 
      ((6378.138 * acos(
          cos(radians(".$searchLAT.")) * cos(radians(merch_lat)) * 
          cos(radians(merch_lng) - radians(".$searchLNG.")) + 
          sin(radians(".$searchLAT.")) * sin(radians(merch_lat))
      )) * 1000) AS Distance,merch_status,merch_id,mb_id,merch_title,merch_listprice,merch_saleprice,merch_usecate,merch_bdcate,merch_totalfloor,merch_onfloor,merch_bdfloor,merch_city,merch_district,merch_add,merch_lat,merch_lng,merch_tstype,merch_finish,merch_certarea,merch_otherarea,merch_amearea,merch_defaultphoto
  FROM 
      fh.merchandise
  WHERE  merch_status=1
) AS T
WHERE 
  T.Distance < ?
";

$res = $DB->query($sql,array(1000));
echo "<pre>";
print_r($res);
echo "</pre>";
exit();

/*

$searchLAT=25.048828717352777;//臺北車站緯度
$searchLNG=121.51377868222998;//臺北車站經度
//SELECT merch_title, FLOOR((6378.138 * acos( cos( radians(25.048828717352777) ) * cos( radians(merch_lat) ) * cos( radians(merch_lng) - radians(121.51377868222998) ) + sin( radians(25.048828717352777) ) * sin( radians( merch_lat ) ) ) ) * 1000 ) AS Distance  FROM fh.merchandise WHERE merch_status=1 HAVING FLOOR((6378.138 * acos( cos( radians(25.048828717352777) ) * cos( radians(merch_lat) ) * cos( radians(merch_lng) - radians(121.51377868222998) ) + sin( radians(25.048828717352777) ) * sin( radians( merch_lat ) ) ) ) * 1000 ) AS disTance

$Ssql = "SELECT merch_title";
$Ssql .= ", FLOOR((6378.138 * acos( cos( radians(?) ) * cos( radians(merch_lat) ) * cos( radians(merch_lng) - radians(?) ) + sin( radians(?) ) * sin( radians( merch_lat ) ) ) ) * 1000 ) AS disTance";
$Ssql .= " FROM fh.merchandise";
$Ssql .= " WHERE merch_status=?";
//$Ssql .= " GROUP BY disTance";
//$Ssql .= " HAVING disTance < 500";
$Ssql .= " HAVING FLOOR((6378.138 * acos( cos( radians(?) ) * cos( radians(tn_latmerch_lat) ) * cos( radians(tn_latmerch_lng) - radians(?) ) + sin( radians(?) ) * sin( radians( tn_latmerch_lat ) ) ) ) * 1000 ) < ?";
$Sres = sqlsrv_query( $conn, $Ssql, array($searchLAT,$searchLNG,$searchLAT,$searchLAT,$searchLNG,$searchLAT,1,500));
//$Sres = sqlsrv_query( $conn, $Ssql, array($searchLAT,$searchLNG,$searchLAT,1));
//$rowCount = sqlsrv_num_rows($Sres);
//echo $rowCount."筆";
echo "<br>";
echo "<br>";
echo "<br>";
while( $row = sqlsrv_fetch_array( $Sres, SQLSRV_FETCH_ASSOC))
{
      echo $row['merch_title'].' '.$row['disTance']."<br>";
}


sqlsrv_free_stmt( $Sres);
sqlsrv_close( $conn);






exit();
*/
$searchLAT=25.048828717352777;//臺北車站緯度
$searchLNG=121.51377868222998;//臺北車站經度

$Ssql = "SELECT COUNT(*) OVER() 'totalNum'";

//$Ssql .= ", SUM(CASE WHEN (merch_tstype='2') THEN 1 ELSE 0 END) OVER() 'selfNum'";
//$Ssql .= ", SUM(CASE WHEN (merch_tstype='3') THEN 1 ELSE 0 END) OVER() 'agentNum'";

//$Ssql .= ", ((6378.138 * acos( cos( radians(?) ) * cos( radians(md.merch_lat) ) * cos( radians(md.merch_lng) - radians(?) ) + sin( radians(?) ) * sin( radians( md.merch_lat ) ) ) ) * 1000 ) AS disTance";

//$Ssql .= ", (CASE WHEN (SELECT dst.stl_id FROM fh.data_sch_timeline dst WHERE dst.merch_id=md.merch_id AND dst.stl_mode=1 AND dst.stl_type=1 AND dst.stl_datestart<='".$cDate."' AND dst.stl_dateend>='".$cDate."' AND dst.stl_timestart<=".$cOpenTime." AND dst.stl_timeend>=".$cOpenTime." AND dst.stl_status=1) > 0 THEN 1 ELSE 0 END) AS openToday";

//$Ssql .= ", (SELECT TOP 1 opd.opd_id FROM fh.merch_oppdata AS opd WHERE opd.mb_id=md.mb_id";
//$Ssql .= " AND opd.merch_id=md.merch_id AND opd.opd_type='1'";
//$Ssql .= " AND opd.opd_status='1' ORDER BY opd.opd_id DESC) AS pubID";
//$Ssql .= ", (SELECT wl_id FROM fh.data_wishlist AS w WHERE w.mb_id = md.mb_id AND w.wl_for='2' AND w.wl_forid=md.merch_id) AS wlID";
$Ssql .= ", merch_id,merch_title,merch_listprice,merch_saleprice,merch_usecate,merch_bdcate,merch_totalfloor,merch_onfloor,merch_bdfloor,merch_city,merch_district,merch_add,merch_lat,merch_lng,merch_tstype,merch_finish,merch_defaultphoto";
$Ssql .= ", (CAST(md.merch_certarea AS INT) + CAST(md.merch_otherarea AS INT) + CAST(md.merch_amearea AS INT)) AS totalArea";
//$Ssql .= ", CONCAT(md.merch_lat ,',',md.merch_lng) AS 'geogroup'";
$Ssql .= " FROM fh.merchandise AS md";
$Ssql .= " WHERE md.merch_status=?";
//$Ssql .= " GROUP BY totalArea";
//$Ssql .= " HAVING ((6378.138 * acos( cos( radians(?) ) * cos( radians(md.merch_lat) ) * cos( radians(md.merch_lng) - radians(?) ) + sin( radians(?) ) * sin( radians( md.merch_lat ) ) ) ) * 1000 ) < ?";
//$Ssql .= " HAVING disTance < ?";
//$Ssql .= " HAVING (CAST(md.merch_certarea AS INT) + CAST(md.merch_otherarea AS INT) + CAST(md.merch_amearea AS INT)) > ?";
//$Sres = $DB->query($Ssql,array($searchLAT,$searchLNG,$searchLAT,1,50000));
$Sres = $DB->query($Ssql,array(1));
/*
$countArray = array('totalNum'=>!empty($Sres[0]['totalNum']) ? $Sres[0]['totalNum'] : 0,'selfNum'=>!empty($Sres[0]['selfNum']) ? $Sres[0]['selfNum'] : 0,'agentNum'=>!empty($Sres[0]['agentNum']) ? $Sres[0]['agentNum'] : 0);
$searchArray = $Sres;
echo json_encode(array('done'=>'1','code'=>'6021','countArray'=>$countArray,'searchArray'=>$searchArray));
*/
$coordARR = array();
foreach ($Sres as $key => $value) {
    if (!empty($Sres[$key]['merch_lat']) && !empty($Sres[$key]['merch_lng'])) {
        $coordARR[] = [$Sres[$key]['merch_lat'],$Sres[$key]['merch_lng']];
    }
}

if (count($coordARR) > 0) {
    $coordCenter = _fn_getcenter($coordARR);
}

print_r($coordCenter);
echo "<pre>";
print_r($Sres);
echo "</pre>";

exit();




$Ssql .= " WHERE md.merch_status=?";//,(GETDATE() - merch_finish) AS MDAge



$res = $DB->query($sql,array($lat,$lat,$lng,1,4,1,50000));//搜尋距離臺北車站500公尺內目標


var_export($res);
exit();




echo _fn_getdistance(121.51399994,25.04879951,121.51568470,25.04638340);//25.05270004,121.51999664 // 25.04638340,121.51568470
exit();
//$subFolder=ceil(intval($merchID)/100);
$basePath = "../_test/";
$imgBase = "mdimg_jaz_";
$fleBase = "mdfile_jaz_";

$attachARR = array(
  "photos" => [
    array(
      "fileUpID" => 1, //【圖像的代表號(Index)】
      "src" => "",
      "fileTitle" => '', //【圖像抬頭】
      "fileDes" => '', //【圖像描述】
      "default" => 1, //【是否為預設圖像】
      "note" => '' //【管理者註記】
    )
  ],
  "files" => [],
  "links" =>[]
);





$uploadReturn = _fn_upload_multi($attachARR,$basePath,$imgBase,$fleBase);//檔案上傳 Function 取得回傳值

print_r($uploadReturn);

/*

處理圖檔的方式有三個主要的值：
A. merchID - 課程商品ID
B. attached['photos']['fileUpID'] - 上傳圖檔中的 Index 值
C. attached['photos']['src'] - 上傳圖檔中的 Base64 格式字串

1. 無論是新增、更新還是刪除，A.值與B.值都是必須的。
2. 當原本就無檔案存在時，A.值與B.值加上C.值後送後，系統視之為新增檔案。
3. 當已經有檔案存在時，A.值與B.值加上C.值後送後，系統視之為更新檔案，此時舊的圖檔會被新的圖檔覆蓋，請注意網頁的暫存檔案有可能會造成仍是舊的檔案顯示。
4. 當A.值與B.值皆帶值，但是C.值為空值時，系統將視之為刪除檔案，此時系統將會刪除主圖檔以及其他兩個縮圖，在操作刪除檔案時請注意別誤刪了其他的檔案。
5. 其他非圖檔的檔案刪除操作同圖檔一樣。








*/

$data = array(
  array(24.9491737,121.4825214),
  array(24.9475567,121.4850187),
  array(24.9471054,121.4810754)
);

function GetCenterFromDegrees ($data = []){
  if (!is_array($data)) return FALSE;
  $num_coords = count($data);
  $x = 0.0;
  $y = 0.0;
  $z = 0.0;
  foreach ($data as $coord) {
    #$lat = $coord[0] * pi() / 180;
    $lat = deg2rad($coord[0]);
    #$lon = $coord[1] * pi() / 180;
    $lon = deg2rad($coord[1]);

    $a = cos($lat) * cos($lon);
    $b = cos($lat) * sin($lon);
    $c = sin($lat);

    $x += $a;
    $y += $b;
    $z += $c;
  }

  $x /= $num_coords;
  $y /= $num_coords;
  $z /= $num_coords;
  $lon = atan2($y, $x);
  $hyp = sqrt($x * $x + $y * $y);
  $lat = atan2($z, $hyp) ;
  #return array($lat * 180 / pi(), $lon * 180 / pi());
  return array( rad2deg($lat) , rad2deg($lon));
}

$center = GetCenterFromDegrees($data);
echo "經緯度中心點: ".$center[0]." , ".$center[1];


exit();






?>
