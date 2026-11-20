<?php
    error_reporting(E_ALL); 
    header("Content-Type:text/html; charset=utf-8");
	include 'aprret_func.php';
	//include 'PHPExcel/Classes/PHPExcel.php';
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
//新北市新店區安祥路67巷50弄15號
//1-591;2-永慶;3-好房網;4-台灣成家網;5-中信
////////////////////////////// TWHOME //////////////////////////////////////
$locJS=_fn_getCachedJsonDatac("locations.json");
$rlocJS=_fn_getCachedJsonDatac("rlocations.json");
//------------------------------------------
$setDataFrom = 5; //1-591;2-永慶;3-好房網;4-台灣成家網;5-中信
$setCityCode = "11";
$setDistCode = "1105";
$setCityText = !empty($setCityCode) ? $locJS[$setCityCode]['text'] : '';
$setDistText = !empty($setCityCode)&&!empty($setDistCode) ? $locJS[$setCityCode]['district'][$setDistCode] : '';
$enCity = urlencode($setCityText);
$enDist = urlencode($setDistText);
$enFullArea = $enCity;
if(!empty($setDistText)){ $enFullArea .= '-'.$enDist; }
if($setDataFrom==2){//1-591;2-永慶;3-好房網;4-台灣成家網;5-中信
    $bcPageURL = 'https://community.yungching.com.tw/region/'.$enFullArea.'_c/?pg=';//永慶
    $pmaxNum = 20;
} elseif($setDataFrom==5) { //1-591;2-永慶;3-好房網;4-台灣成家網;5-中信
    $setCityText = str_replace('台','臺',$setCityText);
    $setDistText = $setDistCode!=4201 ? str_replace('台','臺',$setDistText) : $setDistText;
    $enCity = urlencode($setCityText);
    $enDist = urlencode($setDistText);
    $enFullArea = $enCity.'-city/';
    if(!empty($setDistText)){ $enFullArea .= $enDist.'-town/'; }
    $bcPageURL = 'https://buy.cthouse.com.tw/community/'.$enFullArea;
    $pmaxNum = 20;
} elseif($setDataFrom==3) { //1-591;2-永慶;3-好房網;4-台灣成家網;5-中信
    $bcPageURL = 'https://buy.housefun.com.tw/'.urlencode('社區').'/'.$enFullArea.'?hd_PM=';
    $pmaxNum = 10;
}

//$bcPageURL = 'https://buy.housefun.com.tw/'.urlencode('社區').'/'.$enFullArea.'?hd_PM=';
$pmaxNum = 6;//好房10；永慶20
for($pi=5;$pi<$pmaxNum;$pi++){
echo $pi.'<hr>';
    // 好房網
    /*
    $bcListURL = $bcPageURL.$pi;
    $getBClistStr = load_contents($bcListURL,true);
    $getBClist = get_between($getBClistStr,'"list_ids":','</body>');
    $getBClist = trim(str_replace(['}','</script>',' '],'',$getBClist));
    $getBCarr = json_decode($getBClist, true);
    */
    //print_r($getBCarr);
    //$bmaxNum = count($getBCarr);

    // 永慶
    if($setDataFrom==2){//for 永慶
        $bcListURL = $bcPageURL.$pi;
        if($setDataFrom==5) {
            $bcListURL = $bcPageURL.'page'.$pi.'.html';
        }
        if(URL_exists($bcListURL)===false){
            continue; 
        }
        $getBClistStr = load_contents($bcListURL,true);
        $getBClist = get_between($getBClistStr,'<!-- 主列表 -->','<div class="search-list-not-found">');
        $getSplit = explode('<h2>',$getBClist);
    } elseif($setDataFrom==5) {//1-591;2-永慶;3-好房網;4-台灣成家網;5-中信
        $bcListURL = $bcPageURL.'page'.$pi.'.html';
        echo $bcListURL.'<hr>';

        if(URL_exists($bcListURL)===false){
            continue; 
        }
        $getBClistStr = load_contents($bcListURL,true);
        $getBClist = get_between($getBClistStr,'"sitem": ','"uid": userid');
        $getIDstr = trim(str_replace('",','"',$getBClist));
        $getIDstr = '['.str_replace(',','","',$getIDstr).']';
        $getIDarr = json_decode($getIDstr, true);
        //$getAddSplit = explode('<span class="intro__add">',$getBClistStr);

        $getTitleSplit = explode('<span class="pc_sell_objects"',$getBClistStr);
        for($i=0;$i<count($getTitleSplit)-1;$i++){
          $getTitleArr[$i] = trim(strrchr($getTitleSplit[$i], 'target="_blank">'),'target="_blank">');
          $getAddArr[$i] = get_between($getTitleSplit[$i+1],'<span class="intro__add">','</span></li>');
        }        
    }

    //print_r($getSplit);

    //$bmaxNum = count($getSplit)-1;
    $bmaxNum = 5;
    
    for($bi=4;$bi<$bmaxNum;$bi++){
        echo $bi.'<hr>';
       
        $getInfoMark = 0;
        $getImageMark = 0;
        if($setDataFrom==2){//1-591;2-永慶;3-好房網;4-台灣成家網;5-中信
            $getTitle = trim(get_between($getSplit[$bi+1],'','<div class="good-e-tag tag">'));
            $getAdd = trim(get_between($getSplit[$bi+1],'<em class="item-addr">','</em>'));
            $getID = explode('/building/',$getSplit[$bi]);
            $getID = trim(str_replace('">','',end($getID)));
        } elseif($setDataFrom==5) {//1-591;2-永慶;3-好房網;4-台灣成家網;5-中信
            $getID = $getIDarr[$bi];
            //$getTitle = get_between($getBClistStr,$getID.'.html" target="_blank">','<span class="pc_sell_objects"');
            $getTitle = trim(str_replace(['-',' '],'',$getTitleArr[$bi]));
            $getAdd = trim($getAddArr[$bi]);
        }
        $getTitleSplit = explode('/',$getTitle);
        $getTitle = $getTitleSplit[0];
        $getSubTitle = !empty($getTitleSplit[1]) ? $getTitleSplit[1] : '';
        $getSubTitle2 = !empty($getTitleSplit[2]) ? $getTitleSplit[2] : '';
        echo '搜尋名稱： '.$getTitle."<hr>";
        echo '搜尋住址： '.$getAdd."<hr>";
        //優先至591取得資料
        $get591Arr = _fn_get591_html($getTitle,$setCityCode,$setDistCode);
         
        $insSQL = "";
        //--------------- Get Photo ------------------------------------------------------------

        $getImage = '';
        $getImageMark = 0;
        $setImageUrl = "https://twhome.net/storage/images/building/uploads/".$getTitle.".jpg";//https://twhome.net/storage/images/building/uploads/長虹天韻.jpg
        if(URL_exists($setImageUrl)) {
            $getImage = @file_get_contents($setImageUrl);
            $getImage = base64_encode($getImage);
            $getImageMark = 4;//1-591;2-永慶;3-好房網;4-台灣成家網;5-中信
        }
        else {
            //先比對成家網頁面資訊帶入圖檔
            $CJphotoURL = 'https://twhome.net/'.urlencode(str_replace('台','臺',$setCityText)).'/'.urlencode(str_replace('台','臺',$setDistText)).'/'.urlencode($getTitle);
            if(URL_exists($CJphotoURL)) {
                $getCJphoto = load_contents($CJphotoURL,true);
                $getCJphoto = get_between($getCJphoto,'<meta property="og:image" content="','<meta property="og:site_name"');
                $getCJphoto = trim(str_replace('" />','',$getCJphoto));
                if(URL_exists($getCJphoto)) {
                    $getImage = load_contents($getCJphoto);
                    $getImage = base64_encode($getImage);
                    $getImageMark = 4;//來自台灣成家網
                }
            }
        }
        if($getImageMark==0 && $get591Arr['match']==true) {
            if(URL_exists('https://'.$get591Arr['arr']['comm_photo'])){
                $getImage = @file_get_contents('https://'.$get591Arr['arr']['comm_photo']);
                $getImage = base64_encode($getImage);
                $getImageMark = 1;//1-591;2-永慶;3-好房網;4-台灣成家網;5-中信
             } 
        } else {


            //$getImageMark = 5;//1-591;2-永慶;3-好房網;4-台灣成家網;5-中信
        }

        //--------------- //Get Photo ------------------------------------------------------------
        if($get591Arr['match']==true) {//主要資料來源 from 591
            //--------- 定義陣列 -----------------------------------------------------------------------
            $get591Arr = $get591Arr['arr'];
            //--------------------------------------------------------------------------------------
            $getInfoMark = 1;//來自591
            $insSQL .= "(";
            $insSQL .= "NULL,";
            $insSQL .= "'".$setCityCode."',";
            $insSQL .= "'".$setDistCode."',";
            $insSQL .= "'".$get591Arr['comm_title']."',";
            $insSQL .= "'".$getSubTitle."',";
            $insSQL .= "'".$getSubTitle2."',";
            switch($get591Arr['purpose_str']){
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
            $insSQL .= $setPurpose.",";
            $insSQL .= "0,";
            switch($get591Arr['purpose_other2']){
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
            $insSQL .= $setUsefor.",";
            $insSQL .= "'".$get591Arr['address']."',";
            $insSQL .= "'".$get591Arr['lat']."',";
            $insSQL .= "'".$get591Arr['lng']."',";
            $insSQL .= number_format((float)$get591Arr['base_area_num'], 2, '.', '').",";//基地面積 ##以「坪」計
            $insSQL .= $get591Arr['all_house_num']['content'].",";//總戶數
            
            $insSQL .= number_format((float)str_replace('%','',$get591Arr['ratio']), 2, '.', '').",";//公設比
            $insSQL .= number_format((float)str_replace('%','',$get591Arr['jbrate']), 2, '.', '').",";//建蔽率
            $insSQL .= number_format('0', 2, '.', '').",";//容積率
            $setCompletion = (date('Y') - $get591Arr['age']['content'] - 1)."-01-01";
            $insSQL .= "'".$setCompletion."',";//完工時間
            switch($get591Arr['decorate']){
                case '毛胚屋': $setState = 1 ; break;
                case '標準配備': $setState = 2 ; break;
                case '裝潢': $setState = 3 ; break;
                case '裝潢+傢俱': $setState = 4 ; break;
                default: $setState = 0 ; break;
            }
            $insSQL .= $setState.",";//交屋屋況
            $insSQL .= "'"._fn_str_arr2str($get591Arr['layout'])."',";//格局規劃
            $insSQL .= "'".$get591Arr['build_remark']."',";//特色說明 
            $insSQL .= "'".$get591Arr['price']['price']."',";//建案單價    
            $insSQL .= "'".$get591Arr['total_price']['price']."',";//建案總價
            $insSQL .= "'".$get591Arr['park_price']['price']."',";//車位價格
            $insSQL .= "'".$get591Arr['shop_price']['price']."',";//商鋪價格
            $insSQL .= "'".$get591Arr['sell_status']."',";//公開銷售 sell_status
            $insSQL .= number_format((float)str_replace('%','',$get591Arr['lend_rate']), 2, '.', '').",";//貸款成數 
            $insSQL .= "'".$get591Arr['meeting_address']."',";//接待會館地址
            $insSQL .= number_format(substr(strrchr($get591Arr['park_rate'],':'),1), 2, '.', '').",";//車位配比
            $insSQL .= "'".$get591Arr['park_type_str']."',";//車位規劃 
            $insSQL .= $get591Arr['all_park_num'].",";//車位總數量
            $insSQL .= "'".$get591Arr['build']."',";//棟戶規劃
            $insSQL .= "'".$get591Arr['floor']."',";//樓層規劃
            $insSQL .= "'".$get591Arr['direction_rule']."',";//座向規劃
            $insSQL .= ($get591Arr['property_company']=='有' ? 1 : 0).",";//物業公司//
            $insSQL .= ($get591Arr['manage_committee']=='有' ? 1 : 0).",";//管委會
            $insSQL .= "'".$get591Arr['manage_cost']['price'].$get591Arr['manage_cost']['unit']."',";//管理費用// ".$get591Arr['XXX']."
            $insSQL .= "'".$get591Arr['structural_engine']."',";//結構工程 
            $insSQL .= "'".$get591Arr['land_division']."',";//土地分區 
            $insSQL .= "'".$get591Arr['license']."',";//建造執照
            $insSQL .= "'".$get591Arr['use_license']."',";//使用執照
            $insSQL .= "'".$get591Arr['build_intro']."',";//建材說明
            $insSQL .= "'"._fn_str_arr2str($get591Arr['facility'])."',";//內部公共設施
            $insSQL .= ($get591Arr['has_piles_str']=='有' ? 1 : 0).",";//充電樁 
            $insSQL .= "'".$get591Arr['refuse_disposal_str']."',";//垃圾處理
            $insSQL .= "'".$get591Arr['company']."',";//投資建設 company
            $insSQL .= "'".$get591Arr['build_company']."',";//營造公司 build_company
            $insSQL .= "'".$get591Arr['sell_company']."',";//企劃銷售 
            $insSQL .= "'".$get591Arr['construction_company']."',";//建築設計
            $insSQL .= "'".$get591Arr['landscape_name']."',";//景觀設計
            $insSQL .= "'".$get591Arr['postulate_name']."',";//公設設計 
            $insSQL .= "'".$get591Arr['light_company']."',";//燈光設計 
            $insSQL .= "'".$get591Arr['subway']."',";//捷運系統 
            $insSQL .= "'".$get591Arr['freeway']."',";//高速公路 
            $insSQL .= "'".$get591Arr['rapid_traffic']."',";//快速道路 
            $insSQL .= "'".$get591Arr['speed_railway']."',";//高鐵系統 
            $insSQL .= "'".$get591Arr['speed_railway']."',";//臺鐵系統 
            $insSQL .= "'".$get591Arr['other_traffic']."',";//其他交通方式
            $insSQL .= "'".$get591Arr['school']."',";//學區 
            $insSQL .= "'".$get591Arr['market']."',";//超商/賣場 
            $insSQL .= "'".$get591Arr['tradition_market']."',";//傳統市場 
            $insSQL .= "'".$get591Arr['public_construction']."',";//公共建設 
            $insSQL .= "'".$get591Arr['hot_business_area']."',";//熱門商圈 
            $insSQL .= "'".$get591Arr['hospital']."',";//醫療機構
            $insSQL .= "'".$get591Arr['government']."',";//政府機構 
            $insSQL .= "'".$get591Arr['other']."',";//其他配套
            $insSQL .= "'".$getImage."',";//
            $insSQL .= "'4',";//狀態
            $insSQL .= "'',";//bc_similar
            $insSQL .= "'',";//bc_similaradd
            $insSQL .= "0,";//bc_similaraid
            $insSQL .= $getInfoMark.",";//bc_infomark
            $insSQL .= $getImageMark."''"; //bc_imagemark
            $insSQL .= ")";
            $insSQL .= ",";

            echo  $insSQL;
        } else {//次要資料來源 from 永慶or好房網

            if(URL_exists('https://buy.housefun.com.tw/buy/building/'.$getID)){
                $getInfoMark = 3;//來自好房網
                $getBCinfoStr = load_contents('https://buy.housefun.com.tw/buy/building/'.$getID,true);
                /*
                $getBCinfoLi = get_between($getBCinfoStr,'<title>','</title>');
                $getBCinfoArr = explode('|',trim($getBCinfoLi));
                $getBCinfoArr = explode(':',trim($getBCinfoArr[0]));
                $getBCtitle = $getBCinfoArr[0];
                $getBCadd = $getBCinfoArr[1];
                echo $getBCtitle.' @@ '.$getBCadd;
                */
                $getBCinfoTop = get_between($getBCinfoStr,'<ul class="standards">','<div id="content_building_txt">');
                $getBCinfoTop = trim(str_replace(['</ul>','</div>',' '],'',$getBCinfoTop));
                $getBCinfoTop = str_replace('<li><span>','"',$getBCinfoTop);
                $getBCinfoTop = str_replace('：</span><b>','":"',$getBCinfoTop);
                $getBCinfoTop = str_replace('</b></li>','",',$getBCinfoTop);
                $getBCinfoBtm = get_between($getBCinfoStr,'float: left;">','<ul class="btns">');
                $getBCinfoBtm = strip_tags($getBCinfoBtm,'<b></b>');
                $getBCinfoBtm = trim(str_replace(["\r\n","\n","\r"," "],'',$getBCinfoBtm));
                $getBCinfoBtm = str_replace('：<b>','":"',$getBCinfoBtm);
                $getBCinfoBtm = str_replace('</b>','","',$getBCinfoBtm);
                $getBCinfoBtm = '"'.substr($getBCinfoBtm,0,-1);

                $getBCgeoStr = load_contents('https://buy.housefun.com.tw/Building/building_map.aspx?bid='.$getID,true);
                $getBCgeoStr = explode('var BuildingLat = ',$getBCgeoStr);
                $getBClat = substr($getBCgeoStr[1],0,strpos($getBCgeoStr[1],'</script>'));
                $getBClat =  trim(str_replace([',',';',"'"],'',$getBClat));
                $getBClng = substr($getBCgeoStr[0], strrpos($getBCgeoStr[0], 'var BuildingLng = ') + 18);
                $getBClng =  trim(str_replace([',',';',"'"],'',$getBClng));
                $getFullGeoStr = '"lat":"'.$getBClat.'","lng":"'.$getBClng.'"';
/*
                外觀建材
                營造公司
                建築設計
                建設公司
                完工日期
                建物地址
                臺北市大同區市民大道一段
                建物樓層：	地上 1~18 層 / 地下 1~6 層
                總戶數：	979戶
                同層戶數：	10~20戶
                坪數規劃：	13~41坪
                格局規劃：	1~3房(室)
                管理方式：	飯店式(物業)管理,管理員(警衛)
                建物型態：	大樓
                主 結 構 ：	鋼骨(SC)或鋼骨混凝土,其他
*/



                $getHFArr = json_decode('{'.$getBCinfoTop.$getBCinfoBtm.$getFullGeoStr.'}',true);







                //echo $getHFArr['總戶數'];
                //print_r($getHFArr);



            } else {
                $getInfoMark = 2;//來自永慶 
                $getLink = strrchr($getSplit[$bi],'building/');//取得永慶房屋的頁面資料
                $getLink = 'https://community.yungching.com.tw/'.trim(str_replace('">','',$getLink));

                $getBCinfoStr = load_contents($getLink,true);
                $getBCinfoLi = get_between($getBCinfoStr,'<ul class="building-info-list">','<div class="get-more-infomation ">');
                $getBCinfoLi = preg_replace('/\s+/', '', $getBCinfoLi);
                
                echo $getBCinfoLi;
            } 

                //Array ( [外觀建材] => 馬賽克 [營造公司] => 華中營造股份有限公司 [建築設計] => 正義建築師事務所 [建設公司] => 三普建設、中南紡織 [完工日期] => 1979/01~1979/01 [建物地址] => 臺北市大安區復興南路一段255號 [建物樓層] => 地上1~14層/地下1~2層 [總戶數] => 280戶 [同層戶數] => 2~4戶 [坪數規劃] => 7~45坪 [格局規劃] => 1~4房(室) [管理方式] => 管理員(警衛) [建物型態] => 大樓 [主結構] => 鋼筋混凝土(RC),其他 [lat] => 25.0364536 [lng] => 121.5439049 )

                $insSQL .= "(";
                $insSQL .= "NULL,";
                $insSQL .= "'".$setCityCode."',";
                $insSQL .= "'".$setDistCode."',";
                $insSQL .= "'".$getTitle."',";
                $insSQL .= "'".$getSubTitle."',";
                $insSQL .= "'".$getSubTitle2."',";
                switch($getHFArr['建物型態']){
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
                $insSQL .= $setPurpose.",";
                $insSQL .= "0,";
                $insSQL .= "0,";
                $insSQL .= "'".$getHFArr['建物地址']."',";
                $insSQL .= "'".$getHFArr['lat']."',";
                $insSQL .= "'".$getHFArr['lng']."',";
                $insSQL .= number_format(0, 2, '.', '').",";//基地面積 ##以「坪」計
                //$insSQL .= intval(str_replace('戶','',$getHFArr['總戶數'])).",";//總戶數
                $insSQL .= "0,";//總戶數
                
                $insSQL .= number_format(0, 2, '.', '').",";//公設比
                $insSQL .= number_format(0, 2, '.', '').",";//建蔽率
                $insSQL .= number_format(0, 2, '.', '').",";//容積率

                $setCompletion = explode('~',$getHFArr['完工日期']);
                $setCompletion = str_replace('/','-',$setCompletion[0]).'-01';
                $insSQL .= "'".$setCompletion."',";//完工時間
                $insSQL .= "0,";//交屋屋況
                $insSQL .= "'".$getHFArr['格局規劃']."',";//格局規劃
                $insSQL .= "'',";//特色說明 
                $insSQL .= "'',";//建案單價    
                $insSQL .= "'',";//建案總價
                $insSQL .= "'',";//車位價格
                $insSQL .= "'',";//商鋪價格
                $insSQL .= "'',";//公開銷售 sell_status
                $insSQL .= number_format(0, 2, '.', '').",";//貸款成數 
                $insSQL .= "'".$getAdd."',";//接待會館地址
                $insSQL .= number_format(0, 2, '.', '').",";//車位配比
                $insSQL .= "'',";//車位規劃 
                $insSQL .= "0,";//車位總數量
                $insSQL .= "'',";//棟戶規劃
                $insSQL .= "'".$getHFArr['建物樓層']."',";//樓層規劃
                $insSQL .= "'',";//座向規劃
                $insSQL .= ($getHFArr['管理方式']=='管理員(警衛)' ? 1 : 0).",";//物業公司//
                $insSQL .= "0,";//管委會
                $insSQL .= "'',";//管理費用// ".$get591Arr['XXX']."
                $insSQL .= "'',";//結構工程 
                $insSQL .= "'',";//土地分區 
                $insSQL .= "'',";//建造執照
                $insSQL .= "'',";//使用執照
                $insSQL .= "'".$getHFArr['外觀建材']."',";//建材說明
                $insSQL .= "'',";//內部公共設施
                $insSQL .= "0,";//充電樁 
                $insSQL .= "'',";//垃圾處理
                $insSQL .= "'".$getHFArr['營造公司']."',";//投資建設 company
                $insSQL .= "'".$getHFArr['建設公司']."',";//營造公司 build_company
                $insSQL .= "'',";//企劃銷售 
                $insSQL .= "'".$getHFArr['建築設計']."',";//建築設計
                $insSQL .= "'',";//景觀設計
                $insSQL .= "'',";//公設設計 
                $insSQL .= "'',";//燈光設計 
                $insSQL .= "'',";//捷運系統 
                $insSQL .= "'',";//高速公路 
                $insSQL .= "'',";//快速道路 
                $insSQL .= "'',";//高鐵系統 
                $insSQL .= "'',";//臺鐵系統 
                $insSQL .= "'',";//其他交通方式
                $insSQL .= "'',";//學區 
                $insSQL .= "'',";//超商/賣場 
                $insSQL .= "'',";//傳統市場 
                $insSQL .= "'',";//公共建設 
                $insSQL .= "'',";//熱門商圈 
                $insSQL .= "'',";//醫療機構
                $insSQL .= "'',";//政府機構 
                $insSQL .= "'',";//其他配套
                $insSQL .= "'".$getImage."',";//
                $insSQL .= "'4',";//狀態
                $insSQL .= "'".$get591Arr['similar']."',";//bc_similar
                $insSQL .= "'".$get591Arr['similarADD']."',";//bc_similaradd
                $insSQL .= $get591Arr['similarID'].",";//bc_similarid
                $insSQL .= $getInfoMark.",";//bc_infomark
                $insSQL .= $getImageMark; //bc_imagemark
                $insSQL .= ")";
                $insSQL .= ",";
    
                echo  $insSQL;
    

            

        }
        echo "<br>";
        echo "<br>";
        //print_r($get591Arr);
        //echo "<br>";

    }
    //sleep(2);

}


exit();



$pmaxNum = 1;
for($pi=1;$i<$pmaxNum;$pi++){



    $bcListURL = $bcPageURL.'&page='.$pi;
    $getBClistStr = load_contents($bcListURL,true);


    //$getBClist = get_between($getBClistStr,'<div class="n_sllist new_list ">','<div class="fenye">');
    //$getSplit = explode('<div class="n_swd_lll_1 nz_boxty01 ">',$getBClist);
    //$bmaxNum = count($getSplit);
    $bmaxNum = 1;

    for($bi=0;$bi<$bmaxNum;$bi++){
        /*
        //-------- 取得詳細頁鏈接URL
        $getLink = strrchr($getSplit[$bi],'<a href="');
        $getLink = str_replace(['<a href="','">'],'',$getLink );
        $getLink = trim($getLink);
        //-------- 利用取得的鏈接中分解出城市、地區、建案名稱，並解碼
        $getInfo = explode('/',$getLink);
        $getCityName = urldecode($getInfo[3]);
        $getDistName = urldecode($getInfo[4]);
        $getBCName = urldecode($getInfo[5]);
        $getInfoLi = '"城市":"'.$rlocJS[$getCityName]['id'].'",';
        $getInfoLi .= '"地區":"'.$rlocJS[$getCityName]['dist'][$getDistName].'",';
        $getInfoLi .= '"建案名稱":"'.$getBCName.'",';
        //-------- 利用取得的鏈接截取建案詳細頁面資料
        $getBCinfoStr = load_contents($getLink,true);
        $getBCinfo = explode('<div class="cx_xx_left">',$getBCinfoStr);//過濾精簡資料字串1
        $getBCinfoLi = get_between($getBCinfo[1],'<ul>','</ul>');//過濾精簡資料字串2
        
        $getBCinfoLi = preg_replace('/\s+/', '', $getBCinfoLi);//格式化資料字串
        $getBCinfoLi = str_replace('<li>','"',$getBCinfoLi);//格式化資料字串
        $getBCinfoLi = str_replace('<span>','":"',$getBCinfoLi);//格式化資料字串
        $getBCinfoLi = str_replace('</span></li>','",',$getBCinfoLi);//格式化資料字串
        //-------- 取得建案特色描述
        $getBCintroLi = get_between($getBCinfoStr,'<h2 class="content" id="content0" style="letter-spacing: 1px; max-height: 25px;" >','<div class="showMore community_introduction_btn" id="more0">');//取得資料字串
        $getBCintroLi = str_replace('</h2>','',$getBCintroLi);//截取必要部份
        $getBCintroLi = '"社區介紹":"'.trim($getBCintroLi).'",';//格式化資料字串
        //-------- 取得建案座標
        $getBCgeoLi = get_between($getBCinfoStr,'{ lat: Number(',') },');
        $getBCgeoLi = str_replace('), lng: Number(',',"lng":',$getBCgeoLi);
        $getBCgeoLi = '"lat":'.$getBCgeoLi.',';//格式化資料字串
        //-------- 整合各部分格式化資料
        $getFInalARR = '{'.$getInfoLi.$getBCinfoLi.$getBCgeoLi.$getBCintroLi.'"status":"4"}';
        //-------- 轉換為 PHP 陣列
        $getArr = json_decode($getFInalARR, true);
        //-------- 輸出或寫入資料表



        print_r($getArr);
        echo "<hr>";
        */      

    }

    //sleep(2);
}


echo $getBClistStr;


?>
</div>
</body>
</html>