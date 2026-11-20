<?php
  error_reporting(E_ALL); 
  header("Content-Type:text/html; charset=utf-8");
	include 'aprret_func.php';

//print_r( _fn_get591_html('田恕仁',11,1101) );

//https://buy.housefun.com.tw/%E7%A4%BE%E5%8D%80/%E5%8F%B0%E5%8C%97%E5%B8%82_%E5%A4%A7%E5%90%8C%E5%8D%80?hd_CityID=0000&hd_AreaID=2&hd_Keyword=%E6%9D%B1%E9%A8%B0%E5%A5%BD%E5%85%89%E6%99%AF&hd_Sequence=BUILDAGEDOWN&hd_SearchGroup=Group01&hd_PM=2&hd_Tab=1
$locJS=_fn_getCachedJsonDatac("wejoin.json");

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
<?php
$city = isset($_POST['city']) ? $_POST['city'] : '';
$dist = isset($_POST['dist']) ? $_POST['dist'] : '';
$kw = isset($_POST['kw']) ? $_POST['kw'] : '';
$kid = isset($_POST['kid']) ? $_POST['kid'] : '';

?>
<h2>好房網單筆資料修正&手動建立資料紀錄</h2>
<hr>
<form id="form_singleHF" name="form_singleHF" action="#" method="post">
        <table>
            <tr>
                <td style="padding-right:20px;border-right:#333333 1px solid;">
                    城市 <select id="city" name="city" onchange="localSelect(this);" style="width:120px;height:24px;">
                        <option value="">--選擇城市--</option>
                        <?PHP
                            foreach($locJS as $key => $value){
                            echo "<option value='".$value['value']."'";
                            if($city==$value['value']) { echo " selected"; }
                            echo ">".$value['text']."</option>";
                            }
                        ?>
                    </select><span id="citynum" style="padding-left:8px;font-weight:bold;"><?php echo $city;?></span><br><br>
                    地區 <select id="dist" name="dist" onchange="distSelect(this)" style="width:120px;height:24px;">
                        <option value=''>--選擇地區--</option>
                        <?PHP
                            if(!empty($city)) {
                                foreach($locJS[$city]['district'] as $key => $value){
                                    echo "<option value='".$key."'";
                                    if($dist==$key) { echo " selected"; }
                                    echo ">".$value['text']."</option>";
                                }
                            }
                        ?>
                    </select><span id="distnum" style="padding-left:8px;font-weight:bold;"><?php echo $dist;?></span>
                </td>
                <td style="padding-right:20px;border-right:#333333 1px solid;">
                    建案名稱 <input type="text" id="kw" name="kw" style="width:280px;" value="<?php echo $kw; ?>"><br><br>
                    建 案 ID <input type="text" id="kid" name="kid" style="width:280px;" value="<?php echo $kid; ?>">
                </td>
                <td style="padding-left:40px;">
                    <button type="submit" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">ACTION</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/getBClist_singleFix_housefun.php';">RESET</button>
                    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
                </td>
            </tr>
        </table>
</form>  
<script> 
    var locobj = JSON.parse('<?PHP echo preg_replace("/\s+/", "", $locGet); ?>'); //若要給JAVA使用，必須去除 whitespace
    function localSelect(){
        //------------- 改變行政區劃選單
        var cityID = document.getElementById('city').value;
        if(cityID==0) {
            cityID='';
            document.getElementById('distnum').innerHTML = '';
        }
        document.getElementById('citynum').innerHTML = cityID;
        const distElm = document.getElementById('dist');
        distElm.innerHTML = '<option value="">--選擇地區--</option>'; 
        const disT = locobj[cityID]['district'];
        for (const [key, value] of Object.entries(disT)) {
            distElm.insertAdjacentHTML('beforeend', `<option value="${key}">${value['text']}</option>`)
        }
    }
    function distSelect(){
        var distyID = document.getElementById('dist').value;
        document.getElementById('distnum').innerHTML = distyID;
    }
    function CopytoClipboard() {
        var copyText = document.getElementById("prevstr");
        copyText.select();
        document.execCommand("copy");
        alert("Copied!!");
    }
</script>
  
<hr>
<?php

$setRegion = $city;
$setSection = $dist;
$setTitle = $kw;
$getID = $kid;
$getSubTitle = '';
$getSubTitle2 = '';
if(empty($setTitle) || empty($setRegion) || empty($getID)){
  echo "</form>";
  echo "尚未設定建案名稱、ID或是城市地區！";
  exit();
}

//https://buy.housefun.com.tw/Building/building_map.aspx?bid=5888850
//$setCityCode = $city;
//$setDistCode = $dist;
$setCityText = !empty($setRegion) ? $locJS[$setRegion]['text'] : '';
$setDistText = !empty($setRegion)&&!empty($setSection) ? $locJS[$setRegion]['district'][$setSection]['text'] : '';
$enCity = urlencode($setCityText);
$enDist = urlencode($setDistText);
$enFullArea = $enCity;
if(!empty($setDistText)){ $enFullArea .= '_'.$enDist; }
$getBCinfoStr = load_contents('https://buy.housefun.com.tw/buy/building/'.$getID,true);
$getBCinfoTop = get_between($getBCinfoStr,'<ul class="standards">','<div id="content_building_txt">');
$getBCinfoTop = trim(str_replace(['</ul>','</div>'],'',$getBCinfoTop));
$getBCinfoTop = str_replace('<li><span>','"',$getBCinfoTop);
$getBCinfoTop = str_replace('：</span><b>','":"',$getBCinfoTop);
$getBCinfoTop = str_replace('</b></li>','",',$getBCinfoTop);
$getBCinfoBtm = get_between($getBCinfoStr,'float: left;">','<ul class="btns">');
$getBCinfoBtm = strip_tags($getBCinfoBtm,'<b></b>');
$getBCinfoBtm = trim(preg_replace('/\s\s+/', '', $getBCinfoBtm));
$getBCinfoBtm = trim(str_replace(' ','',$getBCinfoBtm));
$getBCinfoBtm = str_replace('：<b>','":"',$getBCinfoBtm);
$getBCinfoBtm = str_replace('</b>','","',$getBCinfoBtm);
$getBCinfoBtm = '"'.substr($getBCinfoBtm,0,-1);
//取得座標
$getBCgeoStr = load_contents('https://buy.housefun.com.tw/Building/building_map.aspx?bid='.$getID,true);
$getBCgeoStr = explode('var BuildingLat = ',$getBCgeoStr);
$getBClat = substr($getBCgeoStr[1],0,strpos($getBCgeoStr[1],'</script>'));
$getBClat =  trim(str_replace([',',';',"'"],'',$getBClat));
$getBClng = substr($getBCgeoStr[0], strrpos($getBCgeoStr[0], 'var BuildingLng = ') + 18);
$getBClng =  trim(str_replace([',',';',"'"],'',$getBClng));
$getFullGeoStr = '"lat":"'.$getBClat.'","lng":"'.$getBClng.'"';
$getFullStr = $getBCinfoTop.$getBCinfoBtm.$getFullGeoStr;
//重整陣列字串
$getFullStr = str_replace('"外觀建材"','"build_intro"',$getFullStr);
$getFullStr = str_replace('"營造公司"','"build_company"',$getFullStr);
$getFullStr = str_replace('"建築設計"','"construction_company"',$getFullStr);
$getFullStr = str_replace('"建設公司"','"company"',$getFullStr);
$getFullStr = str_replace('"完工日期"','"completion_date"',$getFullStr);
$getFullStr = str_replace('"建物地址"','"address"',$getFullStr);  //臺北市大同區市民大道一段
$getFullStr = str_replace('"建物樓層"','"floor"',$getFullStr);	//地上 1~18 層 / 地下 1~6 層
$getFullStr = str_replace('"總戶數"','"all_house_num"',$getFullStr);   //979戶 ['all_house_num']['content']
$getFullStr = str_replace('"同層戶數"','"layer_num"',$getFullStr);	//10~20戶 bc_layernum
$getFullStr = str_replace('"坪數規劃"','"layout2"',$getFullStr);	//13~41坪
$getFullStr = str_replace('"格局規劃"','"layout1"',$getFullStr);	//['1~3房(室)']
$getFullStr = str_replace('"管理方式"','"property_company"',$getFullStr);	//飯店式(物業)管理,管理員(警衛)
$getFullStr = str_replace('"建物型態"','"purpose_str"',$getFullStr);	//大樓
$getFullStr = str_replace('"主結構"','"structural_engine"',$getFullStr); 	//鋼骨(SC)或鋼骨混凝土,其他
$getFullStr = $getFullStr.',"similar":"'.@$setINSarray['arr']['similar'].'","similarADD":"'.@$setINSarray['arr']['similarADD'].'","similarID":"'.@$setINSarray['arr']['similarID'].'"';
//生成陣列
$setINSarray['arr'] = json_decode('{'.$getFullStr.'}',true);
//取得說明字串
$getBCinfoSplit = explode('<h1>',$getBCinfoStr);
$setFacility = '';
for($ci=2;$ci<count($getBCinfoSplit);$ci++){
  $infoKey = strchr($getBCinfoSplit[$ci],'</h1>',true);
  $infoText = get_between($getBCinfoSplit[$ci],'<p>','</p>');
  $infoText = str_replace("'","",$infoText);
  $infoText = str_replace('/\s\s+/','',$infoText);
  switch($infoKey){
    case '大樓特色':
      $setINSarray['arr']['build_remark'] = $infoText;
    break;
    case '周邊交通':
      $setINSarray['arr']['other_traffic'] = $infoText;
    break;
    case '公共設施':
      $setFacility = $infoText;
    break;
    case '建築設計':
      $setINSarray['arr']['build_intro'] = $infoText;
    break;
  }
}
//定義寫入資料表數值
switch(@$setINSarray['arr']['purpose_str']){
    case '公寓': $setPurpose = 1 ; break;
    case '透天厝': $setPurpose = 2 ; break;
    case '店面' : $setPurpose = 3 ; break;
    case '商辦': $setPurpose = 4 ; break;
    case '大樓': $setPurpose = 5 ; break;
    case '華廈': $setPurpose = 6 ; break;
    case '套房': $setPurpose = 7 ; break;
    case '工廠': $setPurpose = 8 ; break;
    case '廠辦': $setPurpose = 9 ; break;
    case '農舍': $setPurpose = 10 ; break;
    case '倉庫': $setPurpose = 11 ; break;
    default: $setPurpose = 0; break;
}
$setUsefor = 0 ;
$setAreanum = number_format(0, 2, '.', '');
$setHousenum = intval(str_replace(['戶',','],'',@$setINSarray['arr']['all_house_num']));//總戶數
$setRatio = number_format(0, 2, '.', '');//公設比
$setJbrate = number_format(0, 2, '.', '');//建蔽率
$setCompletion = '0001-01-01';
//完工日期
if(!empty(@$setINSarray['arr']['completion_date'])){
    $setDateFormat = explode('~',@$setINSarray['arr']['completion_date']);
    $setDateSplit = explode('/',$setDateFormat[0]);
    if(isset($setDateSplit[0])&&intval($setDateSplit[0])!=0){
        $setCompletion=$setDateSplit[0].'-';
        if(isset($setDateSplit[1])&&intval($setDateSplit[1])!=0){
            $setCompletion .= intval($setDateSplit[1])<10 ? '0'.intval($setDateSplit[1]) : intval($setDateSplit[1]);
        } else { $setCompletion .= '01'; }
        if(isset($setDateSplit[2])&&intval($setDateSplit[2])!=0){
            $setCompletion .= intval($setDateSplit[2])<10 ? '-0'.intval($setDateSplit[2]) : '-'.intval($setDateSplit[2]);
        } else { $setCompletion .= '-01'; }
    }
}
//
$setState = 0;
$setLayOut = !empty(@$setINSarray['arr']['layout1']) ? '"'.@$setINSarray['arr']['layout1'].'",' : '';
$setLayOut .= !empty(@$setINSarray['arr']['layout2']) ? '"'.@$setINSarray['arr']['layout2'].'",' : '';
$setLayOut = '['.substr($setLayOut, 0, -1).']';
//樓層規劃
$setFloor['aboveMin'] = 0;
$setFloor['aboveMax'] = 0;
$setFloor['underMin'] = 0;
$setFloor['underMax'] = 0;
if(!empty(@$setINSarray['arr']['floor'])){
    $getFloor = str_replace(['地上','地下','層',' '],'',@$setINSarray['arr']['floor']);
    $setFloorSplit = explode('/',$getFloor);
    if(isset($setFloorSplit[0])&&!empty($setFloorSplit[0])){
        $aboveSplit = explode('~',$setFloorSplit[0]);
        $setFloor['aboveMin'] = (isset($aboveSplit[0])&&!empty($aboveSplit[0])) ? intval($aboveSplit[0]) : 0;
        $setFloor['aboveMax'] = (isset($aboveSplit[1])&&!empty($aboveSplit[1])) ? intval($aboveSplit[1]) : $setFloor['aboveMin'];
    }
    if(isset($setFloorSplit[1])&&!empty($setFloorSplit[1])){
        $underSplit = explode('~',$setFloorSplit[1]);
        $setFloor['underMin'] = (isset($underSplit[0])&&!empty($underSplit[0])) ? abs(intval($underSplit[0])) : 0;
        $setFloor['underMax'] = (isset($underSplit[1])&&!empty($underSplit[1])) ? abs(intval($underSplit[1])) : $setFloor['underMin'];
    }
}
//
$setProperty = stripos(@$setINSarray['arr']['property_company'],"物業") ? 1 : 0;//物業公司////飯店式(物業)管理,管理員(警衛)
$setCommit = 0;//管委會
$setPiles = 0;
$setLendrate = @number_format(0, 2, '.', '');//貸款成數 
$setParkrate = @number_format(0, 2, '.', '');//車位配比//##30
$setManagecost = '';

//https://twhome.net/storage/images/building/uploads/東騰好光景.jpg

$getImage='';
$getImageMark = 4;//1-591;2-永慶;3-好房網;4-台灣成家網;5-台灣成家網API
$setImageUrl = "https://twhome.net/storage/images/building/uploads/".$setTitle.".jpg";//https://twhome.net/storage/images/building/uploads/長虹天韻.jpg
if(URL_exists($setImageUrl)==false) {
    $setImageUrl = '';
    $getImageMark = 0;
    //先比對成家網頁面資訊帶入圖檔
    $CJphotoURL = 'https://twhome.net/'.urlencode(str_replace('台','臺',$setCityText)).'/'.urlencode(str_replace('台','臺',$setDistText)).'/'.urlencode($setTitle);
    if(URL_exists($CJphotoURL)) {
        $getCJphoto = load_contents($CJphotoURL,true);
        $getCJphoto = get_between($getCJphoto,'<meta property="og:image" content="','<meta property="og:site_name"');
        $getCJphoto = trim(str_replace('" />','',$getCJphoto));
        if($getCJphoto != 'https://twhome.net/new/images/logo200.jpeg'){
            if(URL_exists($getCJphoto)) {
                $setImageUrl = $getCJphoto;
                $getImageMark = 5;//1-591;2-永慶;3-好房網;4-台灣成家網;5-台灣成家網API
            }
        }
    }
}
/*
if($getImageMark==0 && $setINSarray['match']==true && !empty($setINSarray['arr']['comm_photo'])) {
    if(URL_exists('https://'.$setINSarray['arr']['comm_photo'])){
        $setImageUrl = 'https://'.$setINSarray['arr']['comm_photo'];
        $getImageMark = 1;//1-591;2-永慶;3-好房網;4-台灣成家網;5-台灣成家網API
     } 
}
*/
echo '<div style="display:inline-block;"><img style="height:200px;" src="'.$setImageUrl.'"><br><input type="text" value="bcimg_XXXX_default.jpg" style="width:200px;height:24px;"></div>';
echo '<div style="display:inline-block;padding-left:24px;">將以下資料生成資料紀錄，下載圖像後重新命名以生成的ID取代「XXXX」後，儲存於指定資料夾內。</div>';
echo '<hr>';


// 建立 SQL
$insSQLhead = "(NULL, ";
$insSQL = "'".date('Y-m-d H:i:s')."',";
$insSQL .= "'".date('Y-m-d H:i:s')."',";
$insSQL .= "'".$setRegion."',";
$insSQL .= "'".$setSection."',";
$insSQL .= "'".$setTitle."',";
$insSQL .= "'".$getSubTitle."',";
$insSQL .= "'".$getSubTitle2."',";
$insSQL .= $setPurpose.",";
$insSQL .= "0,";//##10
$insSQL .= $setUsefor.",";
$insSQL .= "'".@$setINSarray['arr']['address']."',";
$insSQL .= "'".(!empty(@$setINSarray['arr']['lat']) ? @$setINSarray['arr']['lat'] : '0.0')."',";
$insSQL .= "'".(!empty(@$setINSarray['arr']['lng']) ? @$setINSarray['arr']['lng'] : '0.0')."',";
$insSQL .= $setAreanum.",";//基地面積 ##以「坪」計
$insSQL .= $setHousenum.",";//總戶數
$insSQL .= $setRatio.",";//公設比
$insSQL .= $setJbrate.",";//建蔽率
$insSQL .= number_format('0', 2, '.', '').",";//容積率
$insSQL .= "'".$setCompletion."',";//完工時間                                        ##20
$insSQL .= $setState.",";//交屋屋況
$insSQL .= "'".$setLayOut."',";//格局規劃// 
$insSQL .= "'".@$setINSarray['arr']['layer_num']."',";//同層戶數 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['build_remark'])."',";//特色說明 
$insSQL .= "'".@$setINSarray['arr']['price']['price']."',";//建案單價    
$insSQL .= "'".@$setINSarray['arr']['total_price']['price']."',";//建案總價
$insSQL .= "'".@$setINSarray['arr']['park_price']['price']."',";//車位價格
$insSQL .= "'".@$setINSarray['arr']['shop_price']['price']."',";//商鋪價格
$insSQL .= "'".@$setINSarray['arr']['sell_status']."',";//公開銷售 sell_status
$insSQL .= $setLendrate.",";//貸款成數                                               ##30
$insSQL .= "'".@$setINSarray['arr']['meeting_address']."',";//接待會館地址
$insSQL .= $setParkrate.",";//車位配比//
$insSQL .= "'".@$setINSarray['arr']['park_type_str']."',";//車位規劃 
$insSQL .= intval(str_replace(',','',@$setINSarray['arr']['all_park_num'])).",";//車位總數量
$insSQL .= "'".@$setINSarray['arr']['build']."',";//棟戶規劃
$insSQL .= "'".$setFloor['aboveMin']."',";//樓層規劃
$insSQL .= "'".$setFloor['aboveMax']."',";//樓層規劃
$insSQL .= "'".$setFloor['underMin']."',";//樓層規劃
$insSQL .= "'".$setFloor['underMax']."',";//樓層規劃
$insSQL .= "'".@$setINSarray['arr']['direction_rule']."',";//座向規劃                    ##40
$insSQL .= $setProperty.",";//物業公司//
$insSQL .= $setCommit.",";//管委會
$insSQL .= "'".$setManagecost."',";//管理費用// ".$setINSarray['XXX']."
$insSQL .= "'".@$setINSarray['arr']['structural_engine']."',";//結構工程 
$insSQL .= "'".@$setINSarray['arr']['land_division']."',";//土地分區 //
$insSQL .= "'".@$setINSarray['arr']['license']."',";//建造執照
$insSQL .= "'".@$setINSarray['arr']['use_license']."',";//使用執照
$insSQL .= "'".strip_tags(@$setINSarray['arr']['build_intro'])."',";//建材說明
$insSQL .= "'".$setFacility."',";//內部公共設施
$insSQL .= $setPiles.",";//充電樁                                                       ##50
$insSQL .= "'".@$setINSarray['arr']['refuse_disposal_str']."',";//垃圾處理
$insSQL .= "'".@$setINSarray['arr']['company']."',";//投資建設 company
$insSQL .= "'".@$setINSarray['arr']['build_company']."',";//營造公司 build_company
$insSQL .= "'".@$setINSarray['arr']['sell_company']."',";//企劃銷售 
$insSQL .= "'".@$setINSarray['arr']['construction_company']."',";//建築設計//
$insSQL .= "'".@$setINSarray['arr']['landscape_name']."',";//景觀設計
$insSQL .= "'".@$setINSarray['arr']['postulate_name']."',";//公設設計 
$insSQL .= "'".@$setINSarray['arr']['light_company']."',";//燈光設計 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['subway'])."',";//捷運系統 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['freeway'])."',";//高速公路              //##60
$insSQL .= "'".strip_tags(@$setINSarray['arr']['rapid_traffic'])."',";//快速道路 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['speed_railway'])."',";//高鐵系統 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['speed_railway'])."',";//臺鐵系統 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['other_traffic'])."',";//其他交通方式
$insSQL .= "'".strip_tags(@$setINSarray['arr']['school'])."',";//學區
$insSQL .= "'".strip_tags(@$setINSarray['arr']['market'])."',";//超商/賣場 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['tradition_market'])."',";//傳統市場 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['public_construction'])."',";//公共建設 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['hot_business_area'])."',";//熱門商圈 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['hospital'])."',";//醫療機構               //##70
$insSQL .= "'".strip_tags(@$setINSarray['arr']['government'])."',";//政府機構 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['other'])."',";//其他配套
$insSQL .= "'".$getImage."',";//#68
$insSQL .= "4,";//狀態
$insSQL .= "'".@$setINSarray['arr']['similar']."',";//bc_similar//##70 
$insSQL .= "'".@$setINSarray['arr']['similarADD']."',";//bc_similaradd
$insSQL .= intval(@$setINSarray['arr']['similarID']).",";//bc_similarid
$insSQL .= "3,";//1-591;2-永慶;3-好房網;4-台灣成家網;
$insSQL .= $getImageMark; //1-591;2-永慶;3-好房網;4-台灣成家網;5-台灣成家網API
$insSQL .= "),";
//echo $insSQLhead.$insSQL;
echo '<hr><br>';




?>
<h4>以下為原始字串解析，內容或存在有影響資料庫寫入的特殊字符</h4><span>(修正後複製並生成資料紀錄)</span>
<textarea id="prevstr" name="prevstr" style="width:100%;height:300px;"><?php echo $insSQLhead.$insSQL; ?></textarea>
<button type="button" onclick="CopytoClipboard()" style="height:60px;width:200px;cursor:pointer;">Copy to Clipboard</button>

</body>
</html>

