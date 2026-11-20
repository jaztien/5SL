<?php
  error_reporting(E_ALL); 
  header("Content-Type:text/html; charset=utf-8");
	include 'aprret_func.php';

//print_r( _fn_get591_html('田恕仁',11,1101) );


//$locGet=file_get_contents("locations.json");
//$locJS = json_decode($locGet,true);
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

?>
<h2>591單筆資料修正&手動建立資料紀錄</h2>
<hr>
<form id="form_single591" name="form_single591" action="#" method="post">
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
                    建案名稱<br><br>
                    <input type="text" id="kw" name="kw" style="width:280px;" value="<?php echo $kw; ?>">
                </td>
                <td style="padding-left:40px;">
                    <button type="submit" style="height:60px;width:120px;cursor:pointer;background-color:#77C69E;font-size:1.2em;font-weight:bold;">ACTION</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/getBClist_singleFix_591.php';">RESET</button>
                    <button type="button" style="height:60px;width:60px;cursor:pointer;" onClick="window.location.href ='http://localhost:8888/';">HOME</button>
                </td>
            </tr>
        </table>
  
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

$setTitle = $kw;
$setRegion = $city;
$setSection = $dist;
if(empty($setTitle) || empty($setRegion)){
    echo "</form>";
    echo "尚未設定建案名稱或是城市地區！";
    exit();
}

$setCityText = !empty($setRegion) ? $locJS[$setRegion]['text'] : '';
$setDistText = !empty($setRegion)&&!empty($setSection) ? $locJS[$setRegion]['district'][$setSection]['text'] : '';

//設定搜尋條件&取得內容-----------
$getRegion = !empty($setRegion) ? $locJS[$setRegion]['wj'] : '';//城市名稱（取自資料表）
$getSection = !empty($setSection) ? $locJS[$setRegion]['district'][$setSection]['wj'] : '';//地區名稱（取自資料表）
$setURL = "https://market.591.com.tw/list?regionId=".$getRegion."&sectionId=".$getSection."&keyword=".urlencode($setTitle);
$commGet = load_contents($setURL,true);//取得搜尋結果頁面的所有內容
//
if($commGet == false){
    echo "</form>";
    echo "截取網址失效！";
    exit();
}
if (strpos($commGet, '無匹配結果') !== false) {
    echo "</form>";
    echo "無匹配結果！";
    exit();
} 
@preg_match('/community-card(.*?)<\/em><\/p>/', $commGet, $match);
$getInfoArr = @explode('"',$match[1]);
$getBCtitleIdx = array_search(' alt=',$getInfoArr);
$getBCtitle = trim($getInfoArr[$getBCtitleIdx+1]);
$getBCphoto = trim($getInfoArr[$getBCtitleIdx-1]);
//$getBCdataV = trim($getInfoArr[$getBCtitleIdx+2]);
$getBCphoto = str_replace(['http://','https://','//'],'',$getBCphoto);
if($getBCphoto == 's.591.com.tw/v4/list-pc-default.DKi8CdeN.png'){ $getBCphoto = ''; }
$getBCid = $getInfoArr[array_search(' href=',$getInfoArr)+1];
$getBCid = explode('/',$getBCid);
$getBCid = intval(end($getBCid));
$getInfoArr = explode('>',end($getInfoArr));
$getToAdd = trim(end($getInfoArr));
//當搜尋結果的名稱不完全一致時，比對搜尋結果名稱的可靠性
if($getBCtitle != $setTitle){
    echo "</form>";
    echo "有匹配結果，但是名稱不符！<br><br>";
    //return array('match'=>false,'arr'=>array('similar'=>$getBCtitle,'similarADD'=>$getToAdd,'similarID'=>$getBCid,'error'=>3));
    print_r(array('match'=>false,'arr'=>array('similar'=>$getBCtitle,'similarADD'=>$getToAdd,'similarID'=>$getBCid,'error'=>3)));
    exit();
}
//------------------------------------
$mapViewCodw = load_contents("https://market.591.com.tw/".$getBCid."/album?type=full",true);
$getCommGeo = get_between($mapViewCodw,'streetviewOps:{latlng:{','},url:');//取得前後兩字串間的字串
$strJSgeo = str_replace(':','":"',$getCommGeo);
$strJSgeo = str_replace(',','","',$strJSgeo);
//取得詳細介紹頁面內容--------------------
if(!empty($_POST['prevstr'])){
    $getJsonSTR = $_POST['prevstr'];

} else {
    $commViewGet = load_contents("https://market.591.com.tw/".$getBCid."/overview",true);//取得詳細介紹頁面的所有內容
    $infostart = 'dynamic_info=';//設定擷取開頭字串
    $infoend = 'park_count_str=';//設定擷取結尾字串
    //------------- 截取需要字串 -------------
    $substring = get_between($commViewGet,$infostart,$infoend).$infoend;
    //------- 暫時替換 Value 內的特殊字符 -----
    preg_match_all('/="(.*?)";/', $substring, $match);
    if(is_array($match[1])){
        foreach($match[1] AS $oArr){
            if (strpos($oArr, ':') !== false || strpos($oArr, '=') !== false || strpos($oArr, ';') !== false) {
                $nArr=str_replace(':','@-colon-@',$oArr);
                $nArr=str_replace('=','@-equal-@',$nArr);
                $nArr=str_replace(';','@-semicolon-@',$nArr);
                $substring=str_replace($oArr,$nArr,$substring);
            }else{
                continue;
            }
        }
    }  
    //------------- 分析&格式化字串 -------------
    $strPure = get_between($substring,'};',$infoend);
    $skipStr = strchr($strPure,".",true).'.';
    $strJS = str_replace($skipStr,'"',$strPure);
    //echo $strJS;exit();
    $strJS = str_replace(':','=',$strJS);
    $strJS = str_replace('=','":"',$strJS);
    $strJS = str_replace(';','",',$strJS).'"';//value值若為單字母時無「“」包覆，所以最後一個value值後要加上「”」
    $strJS = str_replace('"{','{"',$strJS);
    $strJS = str_replace('}"','"}',$strJS);
    $strJS = str_replace('"[','[',$strJS);
    $strJS = str_replace(']"',']',$strJS);
    $strJS = str_replace(',unit"','","unit"',$strJS);
    $strJS = str_replace(',type"','","type"',$strJS);
    $strJS = str_replace('""','"',$strJS);
    //------------- 特殊字符回復 -------------
    $strJS = str_replace('@-colon-@',':',$strJS);
    $strJS = str_replace('@-equal-@','=',$strJS);
    $strJS = str_replace('@-semicolon-@',';',$strJS);
    //-------- Fix park_count_str ----------
    $strJS = preg_replace('/,([a-z]),/', ',',$strJS);
    $strJS = preg_replace('/\[([a-z]),/', '[',$strJS);
    $strJS = preg_replace('/,([a-z])\]/', ']',$strJS);
    $strJS = $strJS.$strJSgeo.'"';
    $strJS = preg_replace('/"([a-z])"/', '""',$strJS);
    //-------- 最終陣列化前字串 ---------------

    $getJsonSTR = '{'.$strJS.',"layer_num":"","comm_title":"'.$getBCtitle.'","comm_photo":"'.$getBCphoto.'"}';
}

//------------- 陣列化字串 -------------
$getARR = json_decode($getJsonSTR, true);

if(count($getARR)==0){
    echo "字串陣列組成有誤，進階資料取得失敗！！";
?>
    <hr>
    <h4>以下為原始字串解析，內容或存在有影響資料庫寫入的特殊字符</h4><span>(修正後重新送出以測試結果)</span>
    <textarea id="prevstr" name="prevstr" style="width:100%;height:300px;"><?php echo $getJsonSTR; ?></textarea>
<?php
    echo "</form>";
    exit();
    //return array('match'=>true,'arr'=>array('similar'=>$getBCtitle,'similarADD'=>$getToAdd,'similarID'=>$getBCid,'error'=>4));
}
?>
</form>

<?php
    //echo $getARR['address']; exit();
    $fullADD = '';
    if(empty($getARR['address'])){
        //------------------------------ Get Real location (address)
        $emptyArr = ["a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s"];
        $addrSubSTR = get_between($commViewGet,'.transport=','.region_id=');
        $addIdx = substr(strrchr($addrSubSTR,';'),1);
        $addrSubSTR =  str_replace([$addIdx.'.address=',$addIdx.'.simple_address=','"',' '],'',$addrSubSTR);
        $addrSplit = explode(';',$addrSubSTR);
        $fullADD = in_array($addrSplit[1],$emptyArr) ? '' : $addrSplit[1];
        $simpleADD = in_array($addrSplit[2],$emptyArr) ? '' : $addrSplit[2]; 
        if(empty($fullADD) && !empty($simpleADD)) { 
            $fullADD = $setCityText.$setDistText.str_replace([$setCityText,$setDistText],'',$simpleADD);
        }
        if(!empty($fullADD)){ $getARR['address']=$fullADD; }
        else { $getARR['address']=$getToAdd; }
    }
    if(empty($getARR['meeting_address'])){ 
        if(!empty($getToAdd)) { $getARR['meeting_address']=$getToAdd; } 
        else { $getARR['meeting_address']=$getARR['address']; } 
    }
   //echo $fullADD; exit();
//------------- 輸出 -------------
$setINSarray = array('match'=>true,'arr'=>$getARR);

$getTitleSplit = explode('/',@$setINSarray['arr']['comm_title']);
$getTitle = $getTitleSplit[0];
$getSubTitle = !empty($getTitleSplit[1]) ? $getTitleSplit[1] : '';
$getSubTitle2 = !empty($getTitleSplit[2]) ? $getTitleSplit[2] : '';
$getInfoMark = 1;//來自591
switch(@$setINSarray['arr']['purpose_str']){
    case '公寓': $setPurpose = 1 ; break;
    case '透天厝': $setPurpose = 2 ; break;
    case '店面' : $setPurpose = 3 ; break;
    case '商辦': $setPurpose = 4 ; break;
    case '住宅大樓': $setPurpose = 5 ; break;
    case '華廈': $setPurpose = 6 ; break;
    case '套房': $setPurpose = 7 ; break;
    case '工廠': $setPurpose = 8 ; break;
    case '廠辦': $setPurpose = 9 ; break;
    case '農舍': $setPurpose = 10 ; break;
    case '倉庫': $setPurpose = 11 ; break;
    default: $setPurpose = 0; break;
}
switch(@$setINSarray['arr']['purpose_other2']){
    case '住家用': $setUsefor = 1 ; break;
    case '商業用': $setUsefor = 2 ; break;
    case '工業用': $setUsefor = 3 ; break;
    case '農業用': $setUsefor = 4 ; break;
    case '住商用': $setUsefor = 5 ; break;
    case '住工用':  $setUsefor = 6 ; break;
    case '工商用':  $setUsefor = 7 ; break;
    case '停車空間':  $setUsefor = 8 ; break;
    case '見使用執照': $setUsefor = 9 ; break;
    default: $setUsefor = 0 ; break;
}
$setAreanum = number_format((float)str_replace(',','',@$setINSarray['arr']['base_area_num']), 2, '.', '');//基地面積 ##以「坪」計
$setHousenum = intval(@$setINSarray['arr']['all_house_num']['content']);//總戶數
$setRatio = number_format((float)str_replace('%','',@$setINSarray['arr']['ratio']), 2, '.', '');//公設比
$setJbrate = number_format((float)str_replace('%','',@$setINSarray['arr']['jbrate']), 2, '.', '');//建蔽率
$setCompletion = !empty(@$setINSarray['arr']['age']['content']) ? (date('Y') - @$setINSarray['arr']['age']['content'] - 1)."-01-01" : '0001-01-01';
switch(@$setINSarray['arr']['decorate']){
    case '毛胚屋': $setState = 1 ; break;
    case '標準配備': $setState = 2 ; break;
    case '裝潢': $setState = 3 ; break;
    case '裝潢+傢俱': $setState = 4 ; break;
    default: $setState = 0 ; break;
}
$setLayOut = _fn_str_arr2str(@$setINSarray['arr']['layout']);
$setFloor['aboveMin'] = 0;
$setFloor['aboveMax'] = 0;
$setFloor['underMin'] = 0;
$setFloor['underMax'] = 0;
if(!empty(@$setINSarray['arr']['floor'])){
    $getFloor = str_replace(['地上','地下','層',' '],'',@$setINSarray['arr']['floor']);
    $setFloorSplit = explode(',',$getFloor);
    if(isset($setFloorSplit[0])&&!empty($setFloorSplit[0])){
        $aboveSplit = explode('~',$setFloorSplit[0]);
        $setFloor['aboveMin'] = (isset($aboveSplit[0])&&!empty($aboveSplit[0])) ? intval($aboveSplit[0]) : 0;
        $setFloor['aboveMax'] = (isset($aboveSplit[1])&&!empty($aboveSplit[1])) ? intval($aboveSplit[1]) : $setFloor['aboveMin'];
    }
    if(isset($setFloorSplit[1])&&!empty($setFloorSplit[1])){
        $underSplit = explode('~',$setFloorSplit[1]);
        $setFloor['underMin'] = (isset($underSplit[0])&&!empty($underSplit[0])) ? intval($underSplit[0]) : 0;
        $setFloor['underMax'] = (isset($underSplit[1])&&!empty($underSplit[1])) ? intval($underSplit[1]) : $setFloor['underMin'];
    }
}
$setProperty = (@$setINSarray['arr']['property_company']=='有' ? 1 : 0);//物業公司
$setCommit = (@$setINSarray['arr']['manage_committee']=='有' ? 1 : 0);//管委會
$setPiles = (@$setINSarray['arr']['has_piles_str']=='有' ? 1 : 0);//充電樁 
$setLendrate = @number_format((float)str_replace('%','',@$setINSarray['arr']['lend_rate']), 2, '.', '');//貸款成數 
$setParkrate = @number_format(substr(strrchr(@$setINSarray['arr']['park_rate'],':'),1), 2, '.', '');//車位配比//##30
$setManagecost = @$setINSarray['arr']['manage_cost']['price'].@$setINSarray['arr']['manage_cost']['unit'];//管理費用//
$setFacility = @_fn_str_arr2str(@$setINSarray['arr']['facility']);//內部公共設施

//--------------- 優先至台灣成家網取得圖像 ------------------------------------------------------------
$getImage = '';
$getImageMark = 0;//1-591;2-永慶;3-好房網;4-台灣成家網;5-台灣成家網API
if(URL_exists("https://twhome.net/storage/images/building/uploads/".$getTitle.".jpg")) {
    $setImageUrl = "https://twhome.net/storage/images/building/uploads/".$getTitle.".jpg";
    $getImageMark = 4;

} else {
    //先比對成家網頁面資訊帶入圖檔
    $CJphotoURL = 'https://twhome.net/'.urlencode(str_replace('台','臺',$setCityText)).'/'.urlencode(str_replace('台','臺',$setDistText)).'/'.urlencode($getTitle);
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
if($getImageMark==0 && $setINSarray['match']==true && !empty($setINSarray['arr']['comm_photo'])) {
    if(URL_exists('https://'.$setINSarray['arr']['comm_photo'])){
        if($setINSarray['arr']['comm_photo']!='s.591.com.tw/v4/list-pc-default.DKi8CdeN.png'){
            $getPhotoSplit = explode('!',$setINSarray['arr']['comm_photo']);
            $getOrigSize = explode('.',$getPhotoSplit[1]);
            $getOrigSizeWH = explode('x',$getOrigSize[0]);
            $setNewSize = round($getOrigSizeWH[0]*(1080 / round($getOrigSizeWH[1]))).'x1080.'.$getOrigSize[1];
            $setNewURL = str_replace($getPhotoSplit[1],$setNewSize,$setINSarray['arr']['comm_photo']);
            $setImageUrl = 'https://'.$setNewURL;
            $getImageMark = 1;//1-591;2-永慶;3-好房網;4-台灣成家網;5-台灣成家網API
        }
     } 
}

echo '<div style="display:inline-block;"><img style="height:200px;" src="'.$setImageUrl.'"><br><input type="text" value="bcimg_XXXX_default.jpg" style="width:200px;height:24px;"></div>';
echo '<div style="display:inline-block;padding-left:24px;">將以下資料生成資料紀錄，下載圖像後重新命名以生成的ID取代「XXXX」後，儲存於指定資料夾內。</div>';
echo '<hr>';

// 建立 SQL
$insSQLhead = "(NULL, ";
$insSQL = "'".date('Y-m-d H:i:s')."',";
$insSQL .= "'".date('Y-m-d H:i:s')."',";
$insSQL .= "'".$setRegion."',";
$insSQL .= "'".$setSection."',";
$insSQL .= "'".$getTitle."',";
$insSQL .= "'".$getSubTitle."',";
$insSQL .= "'".$getSubTitle2."',";
$insSQL .= $setPurpose.",";
$insSQL .= "0,";
$insSQL .= $setUsefor.",";
$insSQL .= "'".@$setINSarray['arr']['address']."',";//##10
$insSQL .= "'".(!empty(@$setINSarray['arr']['lat']) ? @$setINSarray['arr']['lat'] : '0.0')."',";
$insSQL .= "'".(!empty(@$setINSarray['arr']['lng']) ? @$setINSarray['arr']['lng'] : '0.0')."',";
$insSQL .= $setAreanum.",";//基地面積 ##以「坪」計
$insSQL .= $setHousenum.",";//總戶數
$insSQL .= $setRatio.",";//公設比
$insSQL .= $setJbrate.",";//建蔽率
$insSQL .= number_format('0', 2, '.', '').",";//容積率
$insSQL .= "'".$setCompletion."',";//完工時間
$insSQL .= $setState.",";//交屋屋況
$insSQL .= "'".$setLayOut."',";//格局規劃//                                         ##20
$insSQL .= "'".@$setINSarray['arr']['layer_num']."',";//同層戶數 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['build_remark'])."',";//特色說明 
$insSQL .= "'".@$setINSarray['arr']['price']['price']."',";//建案單價    
$insSQL .= "'".@$setINSarray['arr']['total_price']['price']."',";//建案總價
$insSQL .= "'".@$setINSarray['arr']['park_price']['price']."',";//車位價格
$insSQL .= "'".@$setINSarray['arr']['shop_price']['price']."',";//商鋪價格
$insSQL .= "'".@$setINSarray['arr']['sell_status']."',";//公開銷售 sell_status
$insSQL .= $setLendrate.",";//貸款成數 
$insSQL .= "'".@$setINSarray['arr']['meeting_address']."',";//接待會館地址
$insSQL .= $setParkrate.",";//車位配比//                                            ##30
$insSQL .= "'".@$setINSarray['arr']['park_type_str']."',";//車位規劃 
$insSQL .= intval(str_replace(',','',@$setINSarray['arr']['all_park_num'])).",";//車位總數量
$insSQL .= "'".@$setINSarray['arr']['build']."',";//棟戶規劃
$insSQL .= "'".$setFloor['aboveMin']."',";//樓層規劃
$insSQL .= "'".$setFloor['aboveMax']."',";//樓層規劃
$insSQL .= "'".$setFloor['underMin']."',";//樓層規劃
$insSQL .= "'".$setFloor['underMax']."',";//樓層規劃
$insSQL .= "'".@$setINSarray['arr']['direction_rule']."',";//座向規劃
$insSQL .= $setProperty.",";//物業公司//
$insSQL .= $setCommit.",";//管委會
$insSQL .= "'".$setManagecost."',";//管理費用// ".$setINSarray['XXX']."
$insSQL .= "'".@$setINSarray['arr']['structural_engine']."',";//結構工程 
$insSQL .= "'".@$setINSarray['arr']['land_division']."',";//土地分區 //                    ##40
$insSQL .= "'".@$setINSarray['arr']['license']."',";//建造執照
$insSQL .= "'".@$setINSarray['arr']['use_license']."',";//使用執照
$insSQL .= "'".strip_tags(@$setINSarray['arr']['build_intro'])."',";//建材說明
$insSQL .= "'".$setFacility."',";//內部公共設施
$insSQL .= $setPiles.",";//充電樁 
$insSQL .= "'".@$setINSarray['arr']['refuse_disposal_str']."',";//垃圾處理
$insSQL .= "'".@$setINSarray['arr']['company']."',";//投資建設 company
$insSQL .= "'".@$setINSarray['arr']['build_company']."',";//營造公司 build_company
$insSQL .= "'".@$setINSarray['arr']['sell_company']."',";//企劃銷售 
$insSQL .= "'".@$setINSarray['arr']['construction_company']."',";//建築設計//              ##50
$insSQL .= "'".@$setINSarray['arr']['landscape_name']."',";//景觀設計
$insSQL .= "'".@$setINSarray['arr']['postulate_name']."',";//公設設計 
$insSQL .= "'".@$setINSarray['arr']['light_company']."',";//燈光設計 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['subway'])."',";//捷運系統 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['freeway'])."',";//高速公路 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['rapid_traffic'])."',";//快速道路 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['speed_railway'])."',";//高鐵系統 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['speed_railway'])."',";//臺鐵系統 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['other_traffic'])."',";//其他交通方式
$insSQL .= "'".strip_tags(@$setINSarray['arr']['school'])."',";//學區 //##60
$insSQL .= "'".strip_tags(@$setINSarray['arr']['market'])."',";//超商/賣場 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['tradition_market'])."',";//傳統市場 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['public_construction'])."',";//公共建設 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['hot_business_area'])."',";//熱門商圈 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['hospital'])."',";//醫療機構
$insSQL .= "'".strip_tags(@$setINSarray['arr']['government'])."',";//政府機構 
$insSQL .= "'".strip_tags(@$setINSarray['arr']['other'])."',";//其他配套
$insSQL .= "'".$getImage."',";//#68
$insSQL .= "4,";//狀態
$insSQL .= "'".@$setINSarray['arr']['similar']."',";//bc_similar//##70 
$insSQL .= "'".@$setINSarray['arr']['similarADD']."',";//bc_similaradd
$insSQL .= intval(@$setINSarray['arr']['similarID']).",";//bc_similarid
$insSQL .= "1,";//1-591;2-永慶;3-好房網;4-台灣成家網;
$insSQL .= $getImageMark; //1-591;2-永慶;3-好房網;4-台灣成家網;5-台灣成家網API
$insSQL .= "),";

//echo $insSQLhead.$insSQL;
//echo '<hr><br><br>';

echo '<textarea id="prevstr" name="prevstr" style="width:100%;height:300px;">'.$insSQLhead.$insSQL.'</textarea>';
echo '<button type="button" onclick="CopytoClipboard()" style="height:60px;width:200px;cursor:pointer;">Copy to Clipboard</button>';
echo '<hr><br><br>';


?>

</body>
</html>

