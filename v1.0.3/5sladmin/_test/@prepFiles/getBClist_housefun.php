<?php
error_reporting(E_ALL); 
include 'aprret_func.php';
//include 'PHPExcel/Classes/PHPExcel.php';
$host = 'localhost';
$dbuser ='root';
$dbpassword = '1111';
$dbname = 'jazhome';

//echo _fn_num_b2h("二十五");exit();
$link = mysqli_connect($host,$dbuser,$dbpassword,$dbname);
if($link){
    mysqli_query($link,'SET NAMES utf8mb4');
    //echo "正確連接資料庫";
}
else {
    echo "連接資料庫失敗</br>" . mysqli_connect_error();
    exit();
}
//----------------------------------------
$spage = isset($_POST['spage']) ? $_POST['spage'] : '';
$epage = isset($_POST['epage']) ? $_POST['epage'] : '';
$sdata = isset($_POST['sdata']) ? $_POST['sdata'] : 1;
$edata = isset($_POST['edata']) ? $_POST['edata'] : 10;
$city = isset($_POST['city']) ? $_POST['city'] : '';
$dist = isset($_POST['dist']) ? $_POST['dist'] : '';
$oktobox = isset($_POST['oktobox']) ? $_POST['oktobox'] : 0;
$doloop = isset($_POST['doloop']) ? $_POST['doloop'] : 0;
$donealert = isset($_POST['donealert']) ? $_POST['donealert'] : 0;
//-----------------------------------------
$bmaxNum = 10;//10 for HouseFun
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
  <style>
    .readonly{
        height:18px;
        background-color: #EEEEEE;
        color: #999999;
        border: #666666 1px solid;
    }
  </style>

  </head>
<body style="padding:0px;margin:0px;background-color:#EEEEEE">
<div style="font-size:16px;padding:12px 32px 12px 32px;">
<?php
$checkLoading = 1;
if(empty($city)){ echo "尚未設定城市代碼！"; $checkLoading = 0; }
if(empty($spage)){ echo "尚未指定頁數！"; $checkLoading = 0; }
if(empty($sdata)){ echo "尚未指定列數！"; $checkLoading = 0; }
if(empty($checkLoading)){
    echo "<script>window.parent.document.getElementById('loading').style.display = 'none';</script>";
    exit();
}
////////////////////////////////////////////////////////////////////////////
$startPage = intval($spage);
$endPage = intval($epage);
$startData = intval($sdata);
$endData = intval($edata);
$desText = '';

if(empty($startPage)){ $startPage = 1; }
if(empty($endPage)){ $endPage = $startPage; }
$totalPage = $endPage - $startPage + 1;
if(empty($startData)){ $startData = 1; }
if(empty($endData)){ $endData = $startData; }
//
if($startPage > $endPage){
    $errorMsg = '資料頁數不符邏輯：起始頁大於結束頁';
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp ".$errorMsg."<hr>";
    $checkLoading = 0;
}
if($startData > $endData){
    $errorMsg = '資料頁數不符邏輯：起始列大於結束列';
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp ".$errorMsg."<hr>";
    $checkLoading = 0;
}
if(empty($checkLoading)){
    echo "<script>window.parent.document.getElementById('loading').style.display = 'none';</script>";
    exit();
}
//
$startTime = time();
//新北市新店區安祥路67巷50弄15號
//1-591;2-永慶;3-好房網;4-台灣成家網;5-中信
////////////////////////////// TWHOME //////////////////////////////////////
$locJS=_fn_getCachedJsonDatac("locations.json");
//------------------------------------------
$setCityCode = $city;
$setDistCode = $dist;
$setCityText = !empty($setCityCode) ? $locJS[$setCityCode]['text'] : '';
$setDistText = !empty($setCityCode)&&!empty($setDistCode) ? $locJS[$setCityCode]['district'][$setDistCode] : '';
$enCity = urlencode($setCityText);
$enDist = urlencode($setDistText);
$enFullArea = $enCity;
if(!empty($setDistText)){ $enFullArea .= '_'.$enDist; }
//建立圖片資料夾
$basePath = "uploadFiles/bc/".$setCityCode."/";
if (!is_dir($basePath)) { mkdir($basePath, 0777, true); }
//建立資料表備份檔案資料夾
$setSQLfileFolder = 'bcFiles/'.$setCityCode;
if(!empty($setDistCode)) { $setSQLfile = $setSQLfileFolder.'/'.$setCityCode.'_'.$setDistCode.'.sql'; }
else { $setSQLfile = $setSQLfileFolder.'/'.$setCityCode.'_all.sql'; }
if (!is_dir($setSQLfileFolder)) { @mkdir($setSQLfileFolder, 0777); }
if(!is_file($setSQLfile)){
    $contents = "INSERT INTO `data_buildcase` VALUES ";
    file_put_contents($setSQLfile, $contents);
}
//定義擷取目標URL
$bcPageURL = 'https://buy.housefun.com.tw/'.urlencode('社區').'/'.$enFullArea.'?hd_Sequence=Buildagedown&hd_PM=';
//定義變數起始值
$workPages = 0;
$workDatas = 0;
$pageFinished = 0;//起始已完成的頁數
$dataTotals = 0;//起始所有的紀錄
$dataFinished = 0;//起始已完成的紀錄
$getTotalPage = 0;
$getTotalRow = 0;
$finalPage = 0;
$insID = 0;
//////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////// 以頁為單位進行迴圈 FOR PAGEs List ///////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
for($pi=$startPage;$pi<=$endPage;$pi++){
    $pageErrorMsg = '';
    $finalPage=$pi;
    // 自好房網截取並生成搜尋頁面資料陣列
    $getBClistStr = load_contents($bcPageURL.$pi,true);
    //$getBClistStr = '';
    if(empty($getBClistStr)){
        $pageErrorMsg = '第'.$pi.'頁配置::截取頁面陣列失敗::::';
        _fn_write_errorlog($pageErrorMsg,$setCityCode,$setDistCode);
        //$errorIns = mysqli_query($link, "INSERT INTO bc_errormark VALUES (NULL, '".date('Y-m-d H:i:s')."', '".$setCityCode."', '".$setDistCode."', 'p', $pi, $pi, 0, 0, 0, '', 1, '".$pageErrorMsg."', 0)" );//mark 1-load error; 2-
        echo $pageErrorMsg."<hr>";
        continue;
    }
    $getBCsplit = explode('<section class="m-list-obj" ng-if="!ListDetailLoad">',$getBClistStr);

    /*
    $getBCarr = array();
    $getBCsplit = explode('<h1 class="casename"><a href="/buy/building/',$getBClistStr);
    if(!empty($getBCsplit)&&is_array($getBCsplit)){
        for($di=1;$di<count($getBCsplit)-1;$di++){
            $getSubSplit = explode('" target="_blank" title="',$getBCsplit[$di]);
            $getBCarr[$di-1]['id'] = $getSubSplit[0];
            $getBCarr[$di-1]['title'] = strchr($getSubSplit[1],'" class="',true);
            $getBCarr[$di-1]['add'] = get_between($getSubSplit[1],'<address class="address">','</address>');
        }
    }
    */
    if($pi == $startPage){
        $getPagen = get_between($getBClistStr,'<span class="total ng-cloak">','<span class="m-button-group">');
        $getTotalRow = (int)get_between($getPagen,"{{ RecordCount || '","'}}</em>");
        $getTotalPage = (int)get_between($getPagen,"{{ PageCount || '","'}}頁</em>");
        if($startPage > $getTotalPage) { $startPage = $getTotalPage; }
        if($endPage > $getTotalPage) { $endPage = $getTotalPage; }
        $workPages = ($endPage - $startPage) + 1;
        $lastDatas = 10;
        $workDatas = (($workPages - 1)*$bmaxNum) + $lastDatas;
        if($endPage==$getTotalPage){
            $getRemain = $getTotalRow%$bmaxNum;
            $lastDatas = $getRemain==0 ? 10 : $getRemain;
            $workDatas = (($workPages - 1)*$bmaxNum) + $lastDatas;
        }
    }
    
    if($endPage > $getTotalPage){
        $pageErrorMsg = '資料頁數不符邏輯：設定尾頁大於實際尾頁::::';
        _fn_write_errorlog($pageErrorMsg,$setCityCode,$setDistCode);
        echo $pageErrorMsg."<hr>";
        break;
    }
    if($pi == $getTotalPage){
        $endData = $lastDatas;
    }

    $getRowCount = count($getBCsplit)-1;
    if(($pi!=$getTotalPage && $getRowCount!=$bmaxNum) || ($pi==$getTotalPage && $getRowCount!=$lastDatas)){
        $pageErrorMsg = '第'.$pi.'頁面陣列數不符，認定為截取失敗資料缺失::::';
        _fn_write_errorlog($pageErrorMsg,$setCityCode,$setDistCode);
        /*
        if(!empty($oktobox)){
            $errorIns = mysqli_query($link, "INSERT INTO bc_errormark VALUES (NULL, '".date('Y-m-d H:i:s')."', '".$setCityCode."', '".$setDistCode."', 'p', $pi, $pi, 0, 0, 0, '', 2, '".$errorMsg."', 0)" );//mark 1-load error; 2-陣列截取失敗
        }
        */
        echo $pageErrorMsg."<hr>";
        continue;
    }
    //////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////// 以資料紀錄為單位進行迴圈 FOR DATAs List /////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////
    for($bi=$startData;$bi<=$endData;$bi++){
        $sortNumber = $dataTotals+1;
        $getInfoMark = 0;
        $getImageMark = 0;
        //------------------------------
        $getSubSplit = explode('" alt="',$getBCsplit[$bi]);
        $getTitle = strchr($getSubSplit[1],'" onerror="',true);
        $getPhoto = substr(strrchr(@$getSubSplit[0],'<img src="'),10);
        if(strpos($getPhoto, '//p1-news.hfcdn.com/p1-news/')!==false){
            $getPhoto = '';
        } else {
            $getPhoto = str_replace('ShowPic/272/0/','ShowPic/0/1080/',$getPhoto);
        }
        $getID = intval(get_between(@$getSubSplit[0],'/buy/building/','" target="_blank"'));
        $getAdd = get_between(@$getSubSplit[1],'<address class="address">','</address>');
        $getSimpleAdd = str_replace('臺','台',$getAdd);
        $getSimpleAdd = str_replace([$setCityText,$setDistText],'',$getSimpleAdd);
    //echo '<img src="'.$getPhoto.'" style="width:500px;">';
        /*
        $getAdd = trim($getBCarr[$bi-1]['add']);
        $getSimpleAdd = str_replace('臺','台',$getAdd);
        $getSimpleAdd = str_replace([$setCityText,$setDistText],'',$getSimpleAdd);
        $getID = intval($getBCarr[$bi-1]['id']);
        $getTitle = trim($getBCarr[$bi-1]['title']);
        */
        //------------------------------
        $resMessage = $sortNumber.'. '.$setCityCode.'-'.$setDistCode.'_'.$pi.'_'.$bi.'::::';
        $resMessage .= '搜尋名稱：'.$getTitle.'::::';
        $resMessage .= '搜尋住址：'.$getAdd.'::::';
        if(empty($getTitle)) {
            $resMessage .= '初始資料取得失敗::::prevID:'.$insID;
            _fn_write_errorlog($resMessage,$setCityCode,$setDistCode);
            echo $resMessage."<hr>";
            $dataTotals++;   
            continue;
        }
        $getSubTitle = '';
        $getSubTitle2 = '';
        if(strpos($getTitle, '/')!==false){
            $getTitleSplit = explode('/',$getTitle);
            $getTitle = trim($getTitleSplit[0]);
            $getSubTitle = !empty($getTitleSplit[1]) ? trim($getTitleSplit[1]) : '';
            $getSubTitle2 = !empty($getTitleSplit[2]) ? trim($getTitleSplit[2]) : '';
        } 
        //比對是否重複
        $chkSql = "SELECT * FROM `data_buildcase` WHERE bc_city='".$setCityCode."' AND bc_district='".$setDistCode."' AND bc_title='".$getTitle."'";
        $chkRes = mysqli_query($link, $chkSql);
        //$row = mysqli_fetch_assoc($chkRes);
        $chk_cnt = mysqli_num_rows($chkRes);
        if($chk_cnt>0){
            $resMessage .= '重複建立資料::::prevID:'.$insID;
            _fn_write_errorlog($resMessage,$setCityCode,$setDistCode);
            echo $resMessage."<hr>";
            $dataTotals++;   
            continue;
        }
        
        //優先至591取得資料
        $setINSarray = _fn_get591_html($getTitle,$setCityCode,$setDistCode,$getSimpleAdd);
        //if($setINSarray['match']==true && empty(@$setINSarray['arr']['comm_title'])) {
        if($setINSarray['match']==true && @$setINSarray['arr']['error']==4) {
            $resMessage .= '以來源1取得字串陣列組成有誤(ID:'.$setINSarray['similarID'].')，進階資料取得失敗！！::::prevID:'.$insID;
            _fn_write_errorlog($resMessage,$setCityCode,$setDistCode);
            echo $resMessage."<hr>";
            $dataTotals++;   
            continue;
        }
        $insSQL = "";
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
        if($getImageMark==0 && URL_exists('https:'.$getPhoto)){
            $setImageUrl = 'https:'.$getPhoto;
            $getImageMark = 3;//1-591;2-永慶;3-好房網;4-台灣成家網;5-台灣成家網API
        }
        //--------------- //Get Photo ------------------------------------------------------------
        //////////////////////////////////////////////////////////////////////////////////////////
        if($setINSarray['match']==true) {//主要資料來源 from 591 ///////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////////////////
            //--------------------------------------------------------------------------------------
            $getID = @$setINSarray['arr']['comm_id'];
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
        /////////////////////////////////////////////////////////////////////////////////////////////////////////    
        } else {//次要資料來源 from 永慶or好房網 ////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
            if(URL_exists('https://buy.housefun.com.tw/buy/building/'.$getID)){
                $getInfoMark = 3;//來自好房網
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

            } else {
                $resMessage .= '以來源3截取資料失敗(ID:'.$getID.')，無法連結介紹頁::::prevID:'.$insID;
                /*
                if(!empty($oktobox)){
                    $errorIns = mysqli_query($link, "INSERT INTO bc_errormark VALUES (NULL, '".date('Y-m-d H:i:s')."', '".$setCityCode."', '".$setDistCode."', 'r', $pi, $pi, $bi, $bi, $getID, '".$getTitle."', 3, '".$errorMsg."', 0)" );//mark 1-load error; 2-陣列截取失敗；3-截取資料失敗
                }
                */
                _fn_write_errorlog($resMessage,$setCityCode,$setDistCode);
                echo $resMessage."<hr>";
                $dataTotals++;
                continue;
            }
        }
        // 建立 SQL
        $insSQLhead = "(NULL, ";
        $insSQL = "'".date('Y-m-d H:i:s')."',";
        $insSQL .= "'".date('Y-m-d H:i:s')."',";
        $insSQL .= "'".$setCityCode."',";
        $insSQL .= "'".$setDistCode."',";
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
        $insSQL .= $getInfoMark.",";//1-591;2-永慶;3-好房網;4-台灣成家網;
        $insSQL .= $getImageMark; //1-591;2-永慶;3-好房網;4-台灣成家網;5-台灣成家網API
        $insSQL .= ")";
        //$insSQL .= ";";
        //----------------------- Insert Into Database -------------------
        if(!empty($oktobox)){
            $doBCsql = "INSERT INTO `data_buildcase` VALUES ".$insSQLhead.$insSQL;
            $ins = mysqli_query($link, $doBCsql);
            $insID = mysqli_insert_id($link);
            if($ins && $insID>0){
                if(!empty($setImageUrl) && $getImageMark != 0){
                    $setIMGname = $basePath.'bcimg_'.$insID.'_default.jpg';//mdimg_1_0.jpg
                    $setImage = load_contents($setImageUrl,true);
                    if(!empty($setImage) && file_put_contents($setIMGname, $setImage)){
                        $resMessage .= 'Image OK::::';
                        echo $resMessage."<hr>";
                    } else {
                        $resMessage .= 'Image Failed::::';
                        echo $resMessage."<hr>";
                        if(!empty($getImageMark)){
                            $up = mysqli_query($link, "UPDATE `data_buildcase` SET bc_imagemark=0 WHERE bc_id=".$insID );//mark 1-load error; 2-陣列截取失敗；3-截取資料失敗；4-寫入資料表失敗
                            $getImageMark = 0;
                        }
                    }
                } else {
                    $resMessage .= 'Image Failed::::';
                    echo $resMessage."<hr>";
                    if(!empty($getImageMark)){
                        $up = mysqli_query($link, "UPDATE `data_buildcase` SET bc_imagemark=0 WHERE bc_id=".$insID );//mark 1-load error; 2-陣列截取失敗；3-截取資料失敗；4-寫入資料表失敗
                        $getImageMark = 0;
                    }
                }
                file_put_contents($setSQLfile, PHP_EOL."(".$insID.", ".$insSQL.",",FILE_APPEND);
                //_fn_write_errorlog($resMessage,$setCityCode,$setDistCode);
                $dataFinished++;
            } else {
                $resMessage .= '以來源'.$getInfoMark.'(ID:'.$getID.')寫入資料庫失敗::::prevID:'.$insID;
                _fn_write_errorlog($resMessage,$setCityCode,$setDistCode);
                //$errorIns = mysqli_query($link, "INSERT INTO `bc_errormark` VALUES (NULL, '".date('Y-m-d H:i:s')."', '".$setCityCode."', '".$setDistCode."', 'r', $pi, $pi, $bi, $bi, $getID, '".$getTitle."', 4, '".$resMessage."', 0)" );//mark 1-load error; 2-陣列截取失敗；3-截取資料失敗；4-寫入資料表失敗
                echo $resMessage."<hr>";
            }

        } else {
            $dataFinished++;
            $insID = 'NULL';
            echo "<hr>";
        }
        //----------------------- Insert Into Database -------------------
        $dataTotals++;
        echo "(".$insID.", ".$insSQL.";";
        echo "<hr><br>";
        //------------------------
        $setRandSec = rand(0,100);
        if($setRandSec>0){
            $setRandSec = ($setRandSec / 10) * 2 * 1000000;
            usleep($setRandSec);
        }
    }//每筆/頁//每換一筆紀錄睡1-3秒隨機
    //----------------------- Add Operation Record -------------------
    $pageFinished++;
    if($pi == $getTotalPage){
        break;
    }
    sleep(2);//每換一頁睡2秒
}//每頁
if(!empty($oktobox)){
    $doneStatus = $dataFinished==$workDatas ? 2 : 5;
    $doneSql = "INSERT INTO bc_donemark VALUES (NULL, '".date('Y-m-d H:i:s')."', '".$setCityCode."', '".$setDistCode."', 'p', $startPage, $endPage, $startData, $endData, $finalPage, $workPages, $pageFinished, $workDatas, $dataFinished, '".$desText."', $doneStatus)";
    $doneIns = mysqli_query($link, $doneSql);//mark 1-load error; 2-陣列截取失敗  // 
}
mysqli_close($link);
$endTime = time();
echo '<hr>'.date('i:s',($endTime-$startTime));
echo '<hr>共完成 '.$pageFinished.'/'.$workPages.' 頁 '.$dataFinished.'/'.$workDatas.' 筆資料！';
$loopStatus = ($pi == $getTotalPage) ? 0 : 1;
if(!empty($donealert)) { echo "<audio autoplay='true' style='display:none;'><source src='alert.wav'></audio>"; }
if($pi < $getTotalPage){
?>
<script>
    var loop = parseInt('<?php echo $loopStatus; ?>');
    var pLoop = window.parent.document.getElementById('doloop').checked;
    if(pLoop==true && loop==1){
        window.parent.document.getElementById('loadingtext').innerHTML = "下一循環準備中...";
        window.parent.document.getElementById('loadingtext').style.color = "#44a273";
        window.parent.Clock.resetTimer();
        const myTimeout = setTimeout(window.parent.doNext, 5000);
    } else {
        window.parent.Clock.resetTimer();
        window.parent.document.getElementById('loading').style.display = 'none';
    }
    function stopNext() {
        clearTimeout(myTimeout);
        //alert('The Loop has been stopped!!');
    }
</script>
<?php } else { ?>
<script>
    window.parent.Clock.resetTimer();
    window.parent.document.getElementById('loading').style.display = 'none';
</script>
<?php } ?>
<br><br><br><br>
</div>
</body>
</html>