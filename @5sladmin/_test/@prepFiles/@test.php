<?php
  error_reporting(E_ALL); 
  header("Content-Type:text/html; charset=utf-8");
	include 'aprret_func.php';

//print_r( _fn_get591_html('田恕仁',11,1101) );



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
<!-- <img src="data:image/png;base64,"> -->
<?php

/*
$url = "https://twzipcode.com/";
 
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("Search_C_T"=>"新竹市麗山街２４５號"))); 
//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0');
$output = curl_exec($ch); 
curl_close($ch);
 
echo $output;

exit();
*/


//$setURL = "https://community.houseprice.tw/list/台北市_city/士林區_zip/age-asc_sort/";
////$setURL = "https://buy.housefun.com.tw/%E7%A4%BE%E5%8D%80/%E9%9B%B2%E6%9E%97%E7%B8%A3_%E5%8F%A3%E6%B9%96%E9%84%89?hd_CityID=0016&hd_AreaID=220&hd_Sequence=Buildagedown&hd_SearchGroup=Group01&hd_PM=1&hd_Tab=1";
//$commGet = load_contents($setURL);
$rtn = _fn_get591_fixadd('世釜里玥',33,3303);
print_r($rtn);
exit();


//community-card hot-tag-touch-visble
//https://market.591.com.tw/list?regionId=2&sectionId=&postType=&keyword=碧海擎天天藍
//https://market.591.com.tw/list?regionId=25&sectionId=&postType=&keyword=金閣會館
//https://market.591.com.tw/list?regionId=8&sectionId=&postType=&keyword=廣擎天
/*
    $regionId = '8';
    $sectionId = '98';
    $setTitle = "廣擎天";//中山直美 大直美麗華廈 香榭富裔大廈 金閣會館 25 290 御璽圓 25 291 廣擎天 8 98
    $setURL = "https://market.591.com.tw/list?regionId=".$regionId."&sectionId=".$sectionId."&postType=&keyword=".urlencode($setTitle);
    $commGet = load_contents($setURL,true);//取得搜尋結果頁面的所有內容
    */
    //
    /*
    if (strpos($commGet, '無匹配結果') !== false) {
        print_r(array('match'=>false,'similar'=>'none'));
        exit();
    } 
    */
    //$infostart = '關鍵字: '.$setTitle;//設定擷取開頭字串
    //$infoend = 'alt="'.$setTitle.'"';//設定擷取結尾字串

    //截取需要字串以取得相關社區ID(表列首列紀錄)--------
    //$getCommCont = get_between($commGet,$infostart,$infoend);//取得前後兩字串間的字串
    //$getCommCont = explode('.jpg" alt="',$getCommCont);//取得上面所得字串內更為精準的段落
    //$getCommTitle = $getCommCont[1];


/*
    if(_fn_write_errorlog('取得上面所得字串內更為精準的段落::::取得上面所得字串內更為精準的段落::::',11,1101)){
      echo 'OK';
    }
    */
    //exit();
    //$getCommCont = explode('https://market.591.com.tw/',$getCommCont);//取得上面所得字串內更為精準的段落
    //$getCommID = strchr($getCommCont[1],'" data-gtm-stat=',true);//取得上面所得字串內更為精準的段落（Only ID）
    //  /^[community-card][market.591.com.tw]$/ 
    //preg_match('/^community-card(.*?)market.591.com.tw$/', $commGet, $matches);
    /*
    preg_match('/community-card(.*?)class="type-icon"/', $commGet, $match);
    $getInfoArr = explode('"',$match[1]);
    $infoCount = count($getInfoArr);
    $getBCdataV = $getInfoArr[$infoCount-1];//dataV
    $getBCtitle = $getInfoArr[$infoCount-2];//Title
    $getBCphoto = $getInfoArr[$infoCount-4];//photo URL
    $getBCphoto = str_replace(['http://','https://','//'],'',$getBCphoto);
    $getBCid = $getInfoArr[array_search(' href=',$getInfoArr)+1];
    $getBCid = explode('/',$getBCid);
    $getBCid = intval(end($getBCid));
    echo $getBCphoto;
    //print_r($getInfoArr);
    exit();



    $getCommCont = explode('href="https://market.591.com.tw/',$commGet);
    //$getCommCont = get_between($commGet,'<a class="community-card" href="https://market.591.com.tw/','" data-gtm-stat=');
    $getCommID = strchr($getCommCont[1],'" data-gtm-stat=',true);
*/
    ////////////////////// 自圖庫頁面取得主建物座標 ///////////////////////
    /*
    $mapViewCodw = load_contents("https://market.591.com.tw/".$getCommID."/album?type=full",true);
    $getTitleStr = @explode('"街景","',$mapViewCodw);
    $getTitleStr = @explode('",',$getTitleStr[1]);
    $getCommTitle = trim($getTitleStr[0]);
*/

    
    //截取需要字串以取得相關社區ID(表列首列紀錄)--------
    //$infostart = '關鍵字: '.$setTitle;//設定擷取開頭字串
    //$infoend = 'alt="'.$setTitle.'"';//設定擷取結尾字串
    /*
    $getMetaArr = get_between($commGet,'window.__NUXT__=(function(','){');
    $getMetaArr = explode(',',$getMetaArr);
    $getMeta = get_between($commGet,',listItems:',',listPaginate:');//取得前後兩字串間的字串
    $getMetaKey = array_search($getMeta,$getMetaArr)+1;
    $getMetaName = $getMetaArr[$getMetaKey];

    $getCommCont = get_between($commGet,$getMeta.'[0]=',',build_type:');
    $getPhotoCont = get_between($commGet,',photo_src:{',',label_info:');
    $getPhotoCont = str_replace("\u002F","/",$getPhotoCont);
    $getFullInfoStr = $getCommCont.','.$getPhotoCont;
    $getFullInfoStr = str_replace('{','{"',$getFullInfoStr);
    $getFullInfoStr = str_replace('}','"}',$getFullInfoStr);
    $getFullInfoStr = str_replace(':','":"',$getFullInfoStr);
    $getFullInfoStr = str_replace(',','","',$getFullInfoStr);
    $getFullInfoStr = str_replace('""','"',$getFullInfoStr);
    $getFullInfoStr = str_replace('https":"','https:',$getFullInfoStr);

    $infoArr = json_decode($getFullInfoStr, true);
    */
//echo $getCommID;
//print_r($infoArr);
////
//echo strstr('大直美麗華廈" data-v-fa87d360><div class="type-icon" data-v-fa87d360><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGAAAABgCAMAAADVRocKAAAA21BMVEUAAAAAAABLS0sAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAASEhIMDAz29vZ+fn4uLi4ZGRn9/f1UVFTFxcX5+fnz8/OamppGRkYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADZ2dmysrLw8PDJycnBwcG+vr6srKyqqqqfn5+VlZWQkJBhYWE7Ozvc3NzW1tZ2dnZaWloeHh739/fm5ubT09O7u7uNjY2GhoZBQUElJSXOzs5qamro6Ojj4+OkpKTq6urp6en///+ByMwhAAAASHRSTlOZAK2YlY19NgiTg3hlVS4rnZz4v6Wf/bDd+/fKrHNeTjw7IiAfEwzn0/Xf29nR0MzHxbSo6ea8sqD67+TYxMGqouG38O3N8fETDofjAAA', '" data-v-', true);
//$arr = _fn_get591_html('金閣會館','52','');

//print_r($arr);
$locJS=_fn_getCachedJsonData("locations.json");
$rlocJS=_fn_getCachedJsonData("rlocations.json");

$setCityCode = "11";
$setDistCode = "1105";
$setCityText = !empty($setCityCode) ? $locJS[$setCityCode]['text'] : '';
$setDistText = !empty($setCityCode)&&!empty($setDistCode) ? $locJS[$setCityCode]['district'][$setDistCode] : '';
//$setCityText = str_replace('台','臺',$setCityText);
//$setDistText = $setDistCode!=4201 ? str_replace('台','臺',$setDistText) : $setDistText;
$enCity = urlencode($setCityText);
$enDist = urlencode($setDistText);
$enFullArea = $enCity;
if(!empty($setDistText)){ $enFullArea .= '_'.$enDist; }

$bcPageURL = 'https://buy.housefun.com.tw/'.urlencode('社區').'/'.$enFullArea.'?hd_Sequence=Buildagedown&hd_PM=';

//$bcPageURL = "https://pic.houseprice.tw/res/DrawImage/ShowPic/1920/1080/HPBUD01/Out/%7BC12BBA90-ADEC-4C1C-9623-AE7695F578D3%7D/IMG_20190912_145051.jpg?t=638058569418470000";
//$bcPageURL = "https://community.houseprice.tw/list/".$enCity."_city/".$enDist."_zip/age-desc_sort/";
//$commGet = load_contents($bcPageURL,true);//取得搜尋結果頁面的所有內容
$pi=1;
$getBClistStr = load_contents($bcPageURL.$pi,true);
$getBCarr = array();
$getBCsplit = explode('<h1 class="casename"><a href="/buy/building/',$getBClistStr);

//$setIMGname = 'test.jpg';
//file_put_contents($setIMGname, $getBClistStr);
//$getSubSplit = strrchr($getBClistStr,'<script type="application/ld+json">');
//$getSubSplit = strip_tags($getSubSplit);


//view-source:https://community.houseprice.tw/list/%E5%8F%B0%E5%8C%97%E5%B8%82_city/%E4%B8%AD%E5%B1%B1%E5%8D%80_zip/age-desc_sort/
//https://market.591.com.tw/list?regionId=3&sectionId=34&keyword=九昱希爾登
//$rtn = _fn_get591_html('皇翔御郡',11,1101);
//echo $rtn['arr']['comm_title'];
//print_r($rtn);
echo $getBClistStr;
exit();
/*
$setCompletion = '0001-01-01';
if(!empty(@$setINSarray['completion_date'])){
    $setDateFormat = explode('~',@$setINSarray['completion_date']);
    $setDateSplit = explode('/',$setDateFormat[0]);
    if(isset($setDateSplit[0])&&intval($setDateSplit[0])!=0){
        $dateStr=$setDateSplit[0].'-';
        if(isset($setDateSplit[1])&&intval($setDateSplit[1])!=0){
            $dateStr .= intval($setDateSplit[1])<10 ? '0'.intval($setDateSplit[1]) : intval($setDateSplit[1]);
        } else { $dateStr .= '01'; }
        if(isset($setDateSplit[2])&&intval($setDateSplit[2])!=0){
            $dateStr .= intval($setDateSplit[2])<10 ? '-0'.intval($setDateSplit[2]) : '-'.intval($setDateSplit[2]);
        } else { $dateStr .= '-01'; }
    }
}
*/
/*
$setFloor['aboveMin'] = 0;
$setFloor['aboveMax'] = 0;
$setFloor['underMin'] = 0;
$setFloor['underMax'] = 0;
if(!empty('地上4層,地下3層')){
    $getFloor = str_replace(['地上','地下','層',' '],'','地上4層,地下3層');
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

print_r($setFloor);exit();

*/


//echo $rtn;
print_r($rtn);
exit();


$str = '
dynamic_info={};
o.hid=102977;
o.service_phone="02-55722000";
o.build_type=l;
o.build_type_str=a;
o.price={price:"29~38.5",unit:"萬\u002F坪"};
o.total_price={price:"1,300~2,680",unit:h};
o.park_price={price:"0~180",unit:h};
o.shop_price={price:a,unit:h};
o.community_type=g;
o.age={content:"8",unit:"年"};
o.all_house_num={content:"38",unit:"戶"};
o.sell_status="已完銷";
o.deal_date=a;
o.deal_time="2014年4月";
o.completion_date="2014年";
o.ratio="29%";
o.jbrate="49.92%";
o.purpose_str="住宅大樓";
o.purpose_other2="住家用";
o.structural_engine="RC造";
o.floor="地上10層,地下1層";
o.build="4棟，37戶住家";
o.layout=["一房(33.66~36.06坪)","二房(35.32~80.44坪)","三房(33.66~71.77坪)","四房(60.36~64.53坪)","五房(68.5坪)"];
o.meeting_address="新北市深坑區文山路二段\u002F深坑國中旁";
o.down_pay=a;
o.project_pay=a;
o.decorate=a;
o.lend_rate=a;
o.base_area="459坪";
o.base_area_num="459";
o.land_division="住宅區";
o.park=p;
o.park_rate="1:1.14";
o.direction_rule="朝東北、朝東、朝北、朝西";
o.all_park_num=45;
o.build_intro=a;
o.facility=["接待大廳","中庭花園","空中花園",dk,"交誼廳","會議室","閱覽室","親子遊戲區",d,"俱樂部"];
o.manage_committee="有";
o.property_company=a;
o.manage_cost={price:"7500",unit:"元\u002F戶\u002F月",type:f};
o.sell_company="倍立";
o.company="元大商業銀行股份有限公司";
o.build_company="豪豐營造股份有限公司";
o.construction_company="黃啟煌";
o.landscape_name=a;
o.postulate_name=a;
o.light_company=a;
o.build_remark="深坑「信義時代」 3字頭房價 8分鐘進信義區\r\n\r\n奢侈稅上路一週年，大台北地區房價不減反漲，再加上通膨時代，萬物齊漲，不指房子變貴，人們口袋裡的錢也縮小了，面對北市房價動輒上看3位數、新北多區房價也頻頻上探5、6字頭，距離信義區僅8分鐘的深坑個案「信義時代」維持在3字頭房價，吸引不少台北客搶進，甫公開兩週，銷售逼近7成，創下5月份房市交易的新熱度。\r\n\r\n「信義時代」以「三低」為訴求：低單價、低總價、低公設，讓不少看屋者心動，再者深坑房市新屋少，因此下定速度快。相較於同區個案上看每坪53萬元起，「信義時代」生活環境更好，目前每坪開價僅35萬元起，且將本求利，座向正朝南，走實價實用路線。此外，所以公設比平均僅28%，讓購屋者得房率提高，可用坪數增加。\r\n\r\n開案兩週 熱銷七成 再創深坑交易記錄\r\n\r\n本案開案甫兩週，銷售成績逼近7成，購屋客還真的是要手腳快、有機運才買的到。短短兩個週末，就有如此的銷售成果，也再次顯現出深坑所具有的潛力與市場需求性。\r\n\r\n目前深坑沒有太多可開發之土地，目前住宅用地大多已開發完成，而鄰近唯一剩下住宅用地為國有地，開發時程則無法預期；而深坑另一塊可能發展之地為黑松公司所擁有之工業用地，但若未來想開發為住宅建築，必須先行變更土地使用名目後方可開發。目前深坑房市交易市場少有新屋。\r\n\r\n快速道路匯集點 8分鐘抵信義區\r\n\r\n本案雖然位於深坑，但交通方便，靠著高架快速道路，四通八達；且人口密度低，自然環境可媲美花蓮，但又保有城市便利生活品質，「住深坑就是好山、好水、好便利」。\r\n\r\n從交通層面來看，由於台北聯絡道、國道三號、國道五號…等高架道路匯集提高深坑交通便利性，從本案開車入信義區走台北聯絡道(信義支線)僅需8分鐘，相對於北市其他地區而言，交通更為方便。深坑是大台北地區所有高架快速道路的交會點」樓鈞穗說。他也舉例，以深坑到天母的路程來說，從快速道路接環東、再接堤頂就可達天母，幾乎全程走高架道路，無紅綠燈。\r\n\r\n自然、地質優勢 免憂水、震兩患\r\n\r\n生活環境部分，本案與深坑國中、深坑國小相鄰，距離深坑老街約6分鐘路程；且正對「情人山丘保護區」。與景美溪由左至右緩緩流過，將本案緊緊環抱。基地位置在深坑主幹道文化路二段之上。在生活機能上，周圍亦有傳統市場、超市賣場與3C家電可滿足需求。\r\n\r\n除此之外，本案在地理結構上具有兩大優良天然自然環境結構，讓住戶完全不用擔心台灣常見的「地震」與「水患」問題。其一、本案基地往下3.7米即為岩盤層，N值高達100，相對於台北盆地的土壤結構安全許多，降低地震造成的撼動；其二，本案海拔高度為39公尺，地勢高，更不用擔心大雨水患問題，\r\n\r\n大樓透天產品皆備 滿足多元客能需求\r\n\r\n「信義時代」基地約為459坪，分別為兩棟10樓與7樓大樓型產品，與三間透天型產品，目前每坪開價約35萬元起，公設比平均約29%，總價約在1188萬元至1380萬元之間，具有平面車位與機械車位，目前開價區間落在150萬元至170萬元之間。\r\n\r\n本案主力坪數為35坪至38坪，而兩棟大樓型產品之頂樓層為雙併景觀樓中樓，每戶約為70坪。兩側另有75坪獨棟雙拼大庭園別墅，提供不同家戶類型之需求。此外，本案預計將申請機械式停車塔，補足平面車位不足之問題，讓37戶住戶保有42個車位，避開深坑停車位缺乏之困擾。\r\n\r\n樓鈞穗表示，目前已購客層約八成為台北客，本地客僅約占兩成，主要都是相中深坑交通位置的便利性，再加上本案規劃坪數為中坪數、標準三房規劃，也較符合市場需求，因此本案一推出即吸引不少台北客的目光，對深坑後勢看好也看漲。\r\n\r\n2. 堅守經營之道 太倚：蓋該蓋的房子、賣該賣的價格\r\n\r\n面對近年來台灣房市的起起伏伏，首次出手推案便獲得市場熱烈迴響的太倚開發，再次證明在對的市場規劃適當產品，依舊可吸引購屋人潮。對此，太倚開發發言人樓鈞穗表示，「信義時代」的經驗，顯示出建商應以合理利潤設定合理房價，兩者相互影響，就可達買賣雙方雙贏的局面。\r\n\r\n以「信義時代」為例，不少已購客戶說，本案擁有好條件，但是卻不賣過高的價格，莫怪乎在市場震盪之時仍有不少客人搶進，但對太倚而言「蓋好的房子、賣合理的價格」，才是永久的經營之道。\r\n\r\n此次，「信義時代」選擇與元大銀行信託合作，讓購屋者無後顧之憂。\r\n\r\n信義時代 即將圓滿 B3-4F廣告戶 33.82坪 總價1188萬";
o.school=a;
o.market=a;
o.tradition_market=a;
o.public_construction=a;
o.hot_business_area=a;
o.hospital=a;
o.government=a;
o.other=a;
o.subway=a;
o.freeway=a;
o.rapid_traffic=a;
o.speed_railway=a;
o.tw_railway=a;
o.other_traffic="信義STYLE很近 8分鐘、換一種節奏 8分鐘、換一種時代生活 信義計畫8分鐘 坐擁後花園好山好水 好便利■北市核心圈 近攬無極限生活 信義商圈-8分鐘-101、統一阪急 台大商圈-10分鐘-公館夜市、師大夜市 永康商圈-15分鐘-鼎泰豐、冰館西門商圈-20分鐘-西門紅樓、老天祿滷味 民生商圈-20分鐘-榮星花園、濱江市場";
o.address=n;
o.has_piles_str=a;
o.refuse_disposal_str=a;
o.license=a;
o.use_license="104深使字第00143號";
o.park_type_str="機械式";
o.park_count_str=
';

$getBCid = 12098;
$commViewGet = load_contents("https://market.591.com.tw/".$getBCid."/overview",true);//取得詳細介紹頁面的所有內容
$infostart = 'dynamic_info=';//設定擷取開頭字串
$infoend = 'park_count_str=';//設定擷取結尾字串
//------------- 截取需要字串 -------------
//$start_pos = strpos($commViewGet, $infostart);
//$end_pos = strpos($commViewGet, $infoend);
//$substring = substr($commViewGet, $start_pos, $end_pos - $start_pos + strlen($infoend));
$substring = get_between($commViewGet,$infostart,$infoend);
//$substring = $str;
preg_match_all('/="(.*?)";/', $substring, $match);
//return $match[1];
if(is_array($match[1])){
    foreach($match[1] AS $oArr){
      //if (strpos($oArr, ':') !== false || strpos($oArr, ',') !== false || strpos($oArr, '=') !== false || strpos($oArr, ';') !== false) {
        if (strpos($oArr, ':') !== false || strpos($oArr, '=') !== false || strpos($oArr, ';') !== false) {
            $nArr=str_replace(':','@-colon-@',$oArr);
            //$nArr=str_replace(',','@-comma-@',$nArr);
            $nArr=str_replace('=','@-equal-@',$nArr);
            $nArr=str_replace(';','@-semicolon-@',$nArr);
            $substring=str_replace($oArr,$nArr,$substring);
        }else{
            continue;
        }
    }
}  
$strstart = mb_strpos($substring,'};');
$strPure = substr($substring, $strstart+2,-3);
$skipStr = strchr($strPure,".",true).'.';
$strJS = str_replace($skipStr,'"',$strPure);
$strJS = str_replace(':','=',$strJS);
$strJS = str_replace('=','":"',$strJS);
$strJS = str_replace(';','",',$strJS);
$strJS = str_replace('"{','{"',$strJS);
$strJS = str_replace('}"','"}',$strJS);
$strJS = str_replace('"[','[',$strJS);
$strJS = str_replace(']"',']',$strJS);
$strJS = str_replace(',unit"','","unit"',$strJS);
$strJS = str_replace(',type"','","type"',$strJS);
$strJS = str_replace('""','"',$strJS);
//------------- 特殊字符回復 -------------
$strJS = str_replace('@-colon-@',':',$strJS);
//$strJS = str_replace('@-comma-@',',',$strJS);
$strJS = str_replace('@-equal-@','=',$strJS);
$strJS = str_replace('@-semicolon-@',';',$strJS);

$strJS = preg_replace('/,([a-z]),/', ',',$strJS);
$strJS = preg_replace('/\[([a-z]),/', '[',$strJS);
$strJS = preg_replace('/,([a-z])\]/', ']',$strJS);
$strJS = preg_replace('/"([a-z])"/', '""',$strJS);




$getARR = json_decode('{'.$strJS.'}', true);




//echo $strJS;
print_r($getARR);
exit();
//similar_text('敦化大廈(敦化北路)','國泰敦化大廈',$percent);
//echo $percent.'%';

//$rtn = _fn_get591_html('綠色傳奇',11,1112);
//echo _fn_str_arr2str($rtn['arr']['facility']);

$getBCinfoStr = load_contents('https://buy.housefun.com.tw/buy/building/11523',true);
$getBCinfoSplit = explode('<h1>',$getBCinfoStr);
$setFacility = '';
for($ci=2;$ci<count($getBCinfoSplit);$ci++){
  $infoKey = strchr($getBCinfoSplit[$ci],'</h1>',true);
  $infoText = strip_tags(get_between($getBCinfoSplit[$ci],'<p>','</p>'));
  switch($infoKey){
    case '大樓特色:':
      $setINSarray['build_remark'] = $infoText;
    break;
    case '周邊交通:':
      $setINSarray['other_traffic'] = $infoText;
    break;
    case '公共設施:':
      $setFacility = $infoText;
    break;
    case '建築設計:':
      $setINSarray['build_intro'] = $infoText;
    break;
  }
}

exit();




$enFullArea = $enCity;
if(!empty($setDistText)){ $enFullArea .= '_'.$enDist; }
$pi = 5;
$bcPageURL = 'https://buy.housefun.com.tw/%E7%A4%BE%E5%8D%80/'.$enFullArea.'?hd_PM='.$pi;

$getBClistStr = load_contents($bcPageURL,true);
if(!empty($getBClistStr)){
  $getBCsplit = explode('<h1 class="casename"><a href="/buy/building/',$getBClistStr);
  if(!empty($getBCsplit)&&is_array($getBCsplit)){
    for($i=1;$i<count($getBCsplit)-1;$i++){
      $getSubSplit = explode('" target="_blank" title="',$getBCsplit[$i]);
      $getBCarr[$i-1]['id'] = $getSubSplit[0];
      $getBCarr[$i-1]['title'] = strchr($getSubSplit[1],'" class="',true);
      $getBCarr[$i-1]['add'] = get_between($getSubSplit[1],'<address class="address">','</address>');
    }
  }
}
echo @$getBCarr['test'];
print_r($getBCarr);

//if($getBClistStr){}
//echo $getBClistStr;
exit();





//pic.hfcdn.com/res/DrawImage/ShowPic/480/360/HFBUD01/Out/{BF3C5473-5A15-446F-841B-31BA3AC420A5}/首3.JPG












$enFullArea = $enCity.'-city/';
if(!empty($setDistText)){ $enFullArea .= $enDist.'-town/'; }
$bcPageURL = 'https://buy.cthouse.com.tw/community/'.$enFullArea;
$pi = 5;
$bcListURL = $bcPageURL.'page'.$pi.'.html';
//$getBClistStr = load_contents($bcListURL,true);
$getBClistStr = load_contents('https://buy.housefun.com.tw/buy/building/12471',true);
echo $getBClistStr;
exit();


$getBClist = get_between($getBClistStr,'"sitem": ','"uid": userid');
$getIDstr = trim(str_replace('",','"',$getBClist));
$getIDstr = '['.str_replace(',','","',$getIDstr).']';
$getIDarr = json_decode($getIDstr, true);


//$getTitle = get_between($getBClistStr,'/community/'.$getIDarr[10].'.html" target="_blank">','<span class="pc_sell_objects"');
$getTitleSplit = explode('<span class="pc_sell_objects"',$getBClistStr);
for($i=0;$i<count($getTitleSplit)-1;$i++){
  $getTitleArr[$i] = trim(strrchr($getTitleSplit[$i], 'target="_blank">'),'target="_blank">');
  $getAddArr[$i] = get_between($getTitleSplit[$i+1],'<span class="intro__add">','</span></li>');
}

print_r($getIDarr);
print_r($getTitleArr);
print_r($getAddArr);

/*

$getAddSplit = explode('<span class="intro__add">',$getBClistStr);
$getAdd = strchr($getAddSplit[1],'</span></li>',true);

echo $getTitle;
*/
//print_r($getIDarr);
//echo $getBCstr;
exit();


$CJphotoURL = 'https://twhome.net/'.urlencode('臺北市').'/'.urlencode('大安區').'/'.urlencode('仁愛三青');
$getImage = '';
if(URL_exists($CJphotoURL)) {
    $getCJphoto = load_contents($CJphotoURL,true);
    $getCJphoto = get_between($getCJphoto,'<meta property="og:image" content="','<meta property="og:site_name"');
    $getCJphoto = trim(str_replace('" />','',$getCJphoto));
    if(URL_exists($getCJphoto)) {
        $getImage = load_contents($getCJphoto);
        $getImage = base64_encode($getImage);
        $getImageMark = 1;
    }
}

echo "<img id='base64image' src='data:image/jpeg;base64,".$getImage."'>";
exit();

  $setImageUrl = "https://community.yungching.com.tw/region/";
  $getImage = file_get_contents($setImageUrl);
  //base64_encode();

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_URL, $setImageUrl);
  $rtnConts = curl_exec($ch);
  curl_close($ch);
 // echo $rtnConts;

$getIDarr = get_between($rtnConts,'"list_ids":','<body ng-app="ycapp">');
$getIDarr = trim(str_replace(['}','</script>','</head>'],'',$getIDarr));
$getIDarr = json_decode($getIDarr, true);
//echo $getIDarr;
  //preg_match_all('/<h2(.*?)h2>/', $rtnConts, $match);

  print_r($getIDarr);

?>

</body>
</html>

