<?php
error_reporting(E_ALL); 


function _fn_get591_check($setTitle='',$setRegion='',$setSection='',$setSimpleAdd=''){
    //--------//591--------------------------------
    if(empty($setTitle) || empty($setRegion)){
        return array('match'=>false,'arr'=>array('similar'=>'','error'=>1));
    }
    //設定搜尋條件&取得內容-----------
    //data:{items:
    //https://market.591.com.tw/list?regionId=1&sectionId=9&keyword=樹山丘NO.2-捷運桃花園 
    //https://market.591.com.tw/list?regionId=1&sectionId=3&keyword=仁愛三青D棟
    //https://market.591.com.tw/list?regionId=1&sectionId=1&keyword=中正浩園大廈
    $locJS=_fn_getCachedJsonData("wejoin.json");
    //$setTitle = "美河市";//社區名稱（取自資料表）
    $getRegion = !empty($setRegion) ? $locJS[$setRegion]['wj'] : '';//城市名稱（取自資料表）
    $getSection = !empty($setSection) ? $locJS[$setRegion]['district'][$setSection]['wj'] : '';//地區名稱（取自資料表）
    $setCityText = !empty($setRegion) ? $locJS[$setRegion]['text'] : '';
    $setDistText = !empty($setRegion)&&!empty($setSection) ? $locJS[$setRegion]['district'][$setSection]['text'] : '';
    $setURL = "https://market.591.com.tw/list?regionId=".$getRegion."&sectionId=".$getSection."&keyword=".urlencode($setTitle);
    $commGet = load_contents($setURL,true);//取得搜尋結果頁面的所有內容
    //
    if($commGet == false){
        return array('match'=>false,'arr'=>array('similar'=>'','similarADD'=>'','similarID'=>0,'error'=>9));
    }
    if (strpos($commGet, '無匹配結果') !== false) {
        return array('match'=>false,'arr'=>array('similar'=>'','similarADD'=>'','similarID'=>0,'error'=>2));
    } 
    @preg_match('/community-card(.*?)<\/em><\/p>/', $commGet, $match);
    $getInfoArr = @explode('"',$match[1]);
    $getBCtitleIdx = @array_search(' alt=',$getInfoArr);
    //print_r($getInfoArr[18]);
    //exit();
    $getBCtitle = @trim($getInfoArr[$getBCtitleIdx+1]);
    $getBCphoto = @trim($getInfoArr[$getBCtitleIdx-1]);
    //$getBCdataV = trim($getInfoArr[$getBCtitleIdx+2]);
    $getBCphoto = str_replace(['http://','https://','//'],'',$getBCphoto);
    if($getBCphoto == 's.591.com.tw/v4/list-pc-default.DKi8CdeN.png'){ $getBCphoto = ''; }
    $getBCid = @$getInfoArr[array_search(' href=',$getInfoArr)+1];
    $getBCid = explode('/',$getBCid);
    $getBCid = intval(end($getBCid));
    $getInfoArr = explode('>',end($getInfoArr));
    $getToAdd = trim(end($getInfoArr));
    //當搜尋結果的名稱不完全一致時，比對搜尋結果名稱的可靠性
    if($getBCtitle != $setTitle){
        //similar_text($getBCtitle,$setTitle,$percent);
        //if(!empty($setSimpleAdd)) { $getAddSimilar = strpos($getToAdd, $setSimpleAdd); }
        //else { $getAddSimilar = false; }
        //if($percent <= 50 || $getAddSimilar == false){
            return array('match'=>false,'arr'=>array('similar'=>$getBCtitle,'similarADD'=>$getToAdd,'similarID'=>$getBCid,'error'=>3));
        //}
    }
    return array('match'=>true,'BCtitle'=>$getBCtitle,'BCid'=>$getBCid);
}

/*
function _fn_get591_infoByID($setTitle='',$setRegion='',$setSection='',$getBCid=''){
    //--------//591--------------------------------
    if(empty($setRegion)||empty($setSection)||empty($getBCid)){
        return false;
    }
    $locGet=file_get_contents("files/wejoin.json");
    $locJS = json_decode($locGet,true);
    //$setTitle = "美河市";//社區名稱（取自資料表）
    $getRegion = !empty($setRegion) ? $locJS[$setRegion]['wj'] : '';//城市名稱（取自資料表）
    $getSection = !empty($setSection) ? $locJS[$setRegion]['district'][$setSection]['wj'] : '';//地區名稱（取自資料表）
    $setCityText = !empty($setRegion) ? $locJS[$setRegion]['text'] : '';
    $setDistText = !empty($setRegion)&&!empty($setSection) ? $locJS[$setRegion]['district'][$setSection]['text'] : '';
    //------------------------------------
    $mapViewCodw = load_contents("https://market.591.com.tw/".$getBCid."/album?type=full",true);
    $getCommGeo = get_between($mapViewCodw,'streetviewOps:{latlng:{','},url:');//取得前後兩字串間的字串
    $strJSgeo = str_replace(':','":"',$getCommGeo);
    $strJSgeo = str_replace(',','","',$strJSgeo);
    $getCommPhotoSplit = @explode('!900x.water3',$mapViewCodw);
    $getBCphoto = @mb_substr(mb_strrchr($getCommPhotoSplit[0],'data-src="'),10);
    //$getBCphoto = @str_replace('!900x.water3','!1080x',$getBCphoto);
    if(!empty($getBCphoto)){
        $getBCphoto = $getBCphoto."!1080x.jpg";
    }
    //return $getBCphoto;
    //取得詳細介紹頁面內容--------------------
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
    //$getJsonSTR = '{'.$strJS.',"layer_num":"","comm_title":"'.$getBCtitle.'","comm_photo":"'.$getBCphoto.'","comm_id":"'.$getBCid.'"}';
    $getJsonSTR = '{'.$strJS.',"layer_num":"","comm_photo":"'.$getBCphoto.'","comm_id":"'.$getBCid.'"}';
    //------------- 陣列化字串 -------------
    $getARR = json_decode($getJsonSTR, true);
    if(count($getARR)==0){
        return false;
    }
    $fullADD = '';
    $getToAdd = '';
    if(empty($getARR['address'])){
        //------------------------------ Get Real location (address)
        $emptyArr = ["a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z"];
        $addrSubSTR = get_between($commViewGet,'.transport=','.region_id=');
        $addIdx = substr(strrchr($addrSubSTR,';'),1);
        $addrSubSTR =  str_replace([$addIdx.'.address=',$addIdx.'.simple_address=','"',' '],'',$addrSubSTR);
        $addrSplit = explode(';',$addrSubSTR);
        $fullADD = _fn_is_alpha($addrSplit[1]) ? '' : $addrSplit[1];
        $simpleADD = _fn_is_alpha($addrSplit[2]) ? '' : $addrSplit[2]; 
        //$fullADD = in_array($addrSplit[1],$emptyArr) ? '' : $addrSplit[1];
        //$simpleADD = in_array($addrSplit[2],$emptyArr) ? '' : $addrSplit[2]; 
        if(empty($fullADD) && !empty($simpleADD)) { 
            $fullADD = $setCityText.$setDistText.str_replace([$setCityText,$setDistText],'',$simpleADD);
        }
        if(!empty($fullADD)){ $getARR['address']=$fullADD; }
        else { $getARR['address']=$getToAdd; }
        if(empty($getARR['meeting_address'])){ 
            if(!empty($getToAdd)) { $getARR['meeting_address']=$getToAdd; } 
            else  { $getARR['meeting_address']=$fullADD; } 
        }
    }
    if(empty($getARR['meeting_address'])){ 
        if(!empty($getToAdd)) { $getARR['meeting_address']=$getToAdd; } 
        else { $getARR['meeting_address']=$getARR['address']; } 
    }
    //if(empty($getARR['meeting_address'])){ $getARR['meeting_address']=$getToAdd; }
    //if(empty($getARR['address'])){ $getARR['address']=$getToAdd; }
    //------------- 輸出 -------------
    return $getARR;
}
*/




function _fn_get591_fixadd($setTitle='',$setRegion='',$setSection='',$setCityText='',$setDistText=''){
    if(empty($setTitle) || empty($setRegion)){
        return array('match'=>false,'arr'=>array('similar'=>'','error'=>1));
    }
    //設定搜尋條件&取得內容-----------
    //data:{items:
    //https://market.591.com.tw/list?regionId=1&sectionId=9&keyword=樹山丘NO.2-捷運桃花園 
    //https://market.591.com.tw/list?regionId=1&sectionId=3&keyword=仁愛三青D棟
    //https://market.591.com.tw/list?regionId=1&sectionId=1&keyword=中正浩園大廈
    $locJS=_fn_getCachedJsonData("files/wejoin.json");
    //$setTitle = "美河市";//社區名稱（取自資料表）
    $getRegion = !empty($setRegion) ? $locJS[$setRegion]['wj'] : '';//城市名稱（取自資料表）
    $getSection = !empty($setSection) ? $locJS[$setRegion]['district'][$setSection]['wj'] : '';//地區名稱（取自資料表）
    $setURL = "https://market.591.com.tw/list?regionId=".$getRegion."&sectionId=".$getSection."&keyword=".urlencode($setTitle);
    $commGet = load_contents($setURL,true);//取得搜尋結果頁面的所有內容

    //
    if($commGet == false){
        return false;
    }
    if (strpos($commGet, '無匹配結果') !== false) {
        return false;
    } 
    @preg_match('/community-card(.*?)<\/em><\/p>/', $commGet, $match);
    $getInfoArr = @explode('"',$match[1]);
    $getBCtitleIdx = @array_search(' alt=',$getInfoArr);
    $getBCtitle = @trim($getInfoArr[$getBCtitleIdx+1]);
    $getBCid = @$getInfoArr[array_search(' href=',$getInfoArr)+1];
    $getBCid = explode('/',$getBCid);
    $getBCid = intval(end($getBCid));
    $getInfoArr = explode('>',end($getInfoArr));
    $getToAdd = trim(end($getInfoArr));
    //當搜尋結果的名稱不完全一致時，比對搜尋結果名稱的可靠性
    if($getBCtitle != $setTitle || empty($getBCid )){
        return false;
    }
    //取得詳細介紹頁面內容--------------------
    $commViewGet = load_contents("https://market.591.com.tw/".$getBCid."/overview",true);//取得詳細介紹頁面的所有內容
    //------------------------------ Get Real location (address)
    $fullADD = '';
    $emptyArr = ["a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s"];
    $addrSubSTR = get_between($commViewGet,'.transport=','.region_id=');
    $addIdx = substr(strrchr($addrSubSTR,';'),1);
    $addrSubSTR =  str_replace([$addIdx.'.address=',$addIdx.'.simple_address=','"',' '],'',$addrSubSTR);
    $addrSplit = explode(';',$addrSubSTR);
    $fullADD = _fn_is_alpha($addrSplit[1]) ? '' : $addrSplit[1];
    $simpleADD = _fn_is_alpha($addrSplit[2]) ? '' : $addrSplit[2]; 
    //$fullADD = in_array($addrSplit[1],$emptyArr) ? '' : $addrSplit[1];
    //$simpleADD = in_array($addrSplit[2],$emptyArr) ? '' : $addrSplit[2]; 
    if(empty($fullADD) && !empty($simpleADD)) { 
       $fullADD = $setCityText.$setDistText.str_replace([$setCityText,$setDistText],'',$simpleADD);
    }
/*
    if(!empty($fullADD)){ $getARR['address']=$fullADD; }
    else { $getARR['address']=$getToAdd; }
    if(empty($getARR['meeting_address'])){ 
        if(!empty($getToAdd)) { $getARR['meeting_address']=$getToAdd; } 
        else  { $getARR['meeting_address']=$fullADD; } 
    }
*/
    if(empty($fullADD)){ return false; }
    return trim($fullADD);

}

function _fn_get591_html($setTitle='',$setRegion='',$setSection='',$setSimpleAdd=''){
    //--------//591--------------------------------
    if(empty($setTitle) || empty($setRegion)){
        return array('match'=>false,'arr'=>array('similar'=>'','error'=>1));
    }
    //設定搜尋條件&取得內容-----------
    //data:{items:
    //https://market.591.com.tw/list?regionId=1&sectionId=9&keyword=樹山丘NO.2-捷運桃花園 
    //https://market.591.com.tw/list?regionId=1&sectionId=3&keyword=仁愛三青D棟
    //https://market.591.com.tw/list?regionId=1&sectionId=1&keyword=中正浩園大廈
    $locJS=_fn_getCachedJsonDatac("wejoin.json");
    //$setTitle = "美河市";//社區名稱（取自資料表）
    $getRegion = !empty($setRegion) ? $locJS[$setRegion]['wj'] : '';//城市名稱（取自資料表）
    $getSection = !empty($setSection) ? $locJS[$setRegion]['district'][$setSection]['wj'] : '';//地區名稱（取自資料表）
    $setCityText = !empty($setRegion) ? $locJS[$setRegion]['text'] : '';
    $setDistText = !empty($setRegion)&&!empty($setSection) ? $locJS[$setRegion]['district'][$setSection]['text'] : '';
    $setURL = "https://market.591.com.tw/list?regionId=".$getRegion."&sectionId=".$getSection."&keyword=".urlencode($setTitle);
    $commGet = load_contents($setURL,true);//取得搜尋結果頁面的所有內容
    //
    if($commGet == false){
        return array('match'=>false,'arr'=>array('similar'=>'','similarADD'=>'','similarID'=>0,'error'=>9));
    }
    if (strpos($commGet, '無匹配結果') !== false) {
        return array('match'=>false,'arr'=>array('similar'=>'','similarADD'=>'','similarID'=>0,'error'=>2));
    } 
    @preg_match('/community-card(.*?)<\/em><\/p>/', $commGet, $match);
    $getInfoArr = @explode('"',$match[1]);
    $getBCtitleIdx = @array_search(' alt=',$getInfoArr);
    //print_r($getInfoArr[18]);
    //exit();
    $getBCtitle = @trim($getInfoArr[$getBCtitleIdx+1]);
    $getBCphoto = @trim($getInfoArr[$getBCtitleIdx-1]);
    //$getBCdataV = trim($getInfoArr[$getBCtitleIdx+2]);
    $getBCphoto = str_replace(['http://','https://','//'],'',$getBCphoto);
    if($getBCphoto == 's.591.com.tw/v4/list-pc-default.DKi8CdeN.png'){ $getBCphoto = ''; }
    $getBCid = @$getInfoArr[array_search(' href=',$getInfoArr)+1];
    $getBCid = explode('/',$getBCid);
    $getBCid = intval(end($getBCid));
    $getInfoArr = explode('>',end($getInfoArr));
    $getToAdd = trim(end($getInfoArr));
    //當搜尋結果的名稱不完全一致時，比對搜尋結果名稱的可靠性
    if($getBCtitle != $setTitle){
        //similar_text($getBCtitle,$setTitle,$percent);
        //if(!empty($setSimpleAdd)) { $getAddSimilar = strpos($getToAdd, $setSimpleAdd); }
        //else { $getAddSimilar = false; }
        //if($percent <= 50 || $getAddSimilar == false){
            return array('match'=>false,'arr'=>array('similar'=>$getBCtitle,'similarADD'=>$getToAdd,'similarID'=>$getBCid,'error'=>3));
        //}
    }
    //------------------------------------
    $mapViewCodw = load_contents("https://market.591.com.tw/".$getBCid."/album?type=full",true);
    $getCommGeo = get_between($mapViewCodw,'streetviewOps:{latlng:{','},url:');//取得前後兩字串間的字串
    $strJSgeo = str_replace(':','":"',$getCommGeo);
    $strJSgeo = str_replace(',','","',$strJSgeo);
    //取得詳細介紹頁面內容--------------------
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
    $getJsonSTR = '{'.$strJS.',"layer_num":"","comm_title":"'.$getBCtitle.'","comm_photo":"'.$getBCphoto.'","comm_id":"'.$getBCid.'"}';
    //------------- 陣列化字串 -------------
    $getARR = json_decode($getJsonSTR, true);
    if(count($getARR)==0){
        return array('match'=>true,'arr'=>array('similar'=>$getBCtitle,'similarADD'=>$getToAdd,'similarID'=>$getBCid,'error'=>4));
    }
    $fullADD = '';
    if(empty($getARR['address'])){
        //------------------------------ Get Real location (address)
        $emptyArr = ["a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z"];
        $addrSubSTR = get_between($commViewGet,'.transport=','.region_id=');
        $addIdx = substr(strrchr($addrSubSTR,';'),1);
        $addrSubSTR =  str_replace([$addIdx.'.address=',$addIdx.'.simple_address=','"',' '],'',$addrSubSTR);
        $addrSplit = explode(';',$addrSubSTR);
        $fullADD = _fn_is_alpha($addrSplit[1]) ? '' : $addrSplit[1];
        $simpleADD = _fn_is_alpha($addrSplit[2]) ? '' : $addrSplit[2]; 
        //$fullADD = in_array($addrSplit[1],$emptyArr) ? '' : $addrSplit[1];
        //$simpleADD = in_array($addrSplit[2],$emptyArr) ? '' : $addrSplit[2]; 
        if(empty($fullADD) && !empty($simpleADD)) { 
            $fullADD = $setCityText.$setDistText.str_replace([$setCityText,$setDistText],'',$simpleADD);
        }
        if(!empty($fullADD)){ $getARR['address']=$fullADD; }
        else { $getARR['address']=$getToAdd; }
        if(empty($getARR['meeting_address'])){ 
            if(!empty($getToAdd)) { $getARR['meeting_address']=$getToAdd; } 
            else  { $getARR['meeting_address']=$fullADD; } 
        }
    }
    if(empty($getARR['meeting_address'])){ 
        if(!empty($getToAdd)) { $getARR['meeting_address']=$getToAdd; } 
        else { $getARR['meeting_address']=$getARR['address']; } 
    }
    //if(empty($getARR['meeting_address'])){ $getARR['meeting_address']=$getToAdd; }
    //if(empty($getARR['address'])){ $getARR['address']=$getToAdd; }
    //------------- 輸出 -------------
    return array('match'=>true,'arr'=>$getARR);
}

function _fn_getYQ_html($setTitle='',$setRegion='',$setSection=''){
    //--------//永慶房屋----------------------------------------
    if(empty($setTitle) || empty($setRegion) || empty($setSection)){
        return false;
    }
    $getAdd = _fn_get_fulladdress($setRegion,$setSection,'',true);
    $setCityDist = $getAdd['cityarea'];
    $setCommTitle = $setTitle;
    $commSearchURL = 'https://community.yungching.com.tw/region/'.urlencode($setCityDist).'_c/?kw='.urlencode($setCommTitle);
    $searchS = '<a href="#" data-bid="';
    $searchE = '" data-sid=';
    $getSearchStr = load_contents($commSearchURL,true);
    $getCommID = get_between($getSearchStr,$searchS,$searchE);
    $commGetStr = "https://buy.housefun.com.tw/buy/building/".$getCommID;
    $getFullStr = load_contents($commGetStr);
    //$getFullStr = str_replace(array("\r\n", "\r", "\n", " "),'',$getFullStr);
    $infoS = '<!--end.Bdetail_tab-->';//"<!--end.Bdetail_tab-->";
    $infoE = '<!--end.tab_content-->';//"<!--end.tab_content-->";
    $substring = get_between($getFullStr,$infoS,$infoE);
    return $substring;
}
function _fn_write_errorlog($msg='',$city='',$dist=''){
    if(empty($msg)){ return false; }
    //$basePath = "errorlog/".$city."/".$dist."/";
    $basePath = "errorlog/".$city."/";
    if (!is_dir($basePath)) { mkdir($basePath, 0777, true); }
    $filename = $basePath.$city.'_'.$dist.'.txt';
    if(file_put_contents($filename, PHP_EOL.$msg.';',FILE_APPEND)){
        return true;
    }
    return false;//
}
function _fn_is_alpha($char) {
    //判斷字符長度，只處理一個字符
    if(mb_strlen($char) != 1) return 0;
    // 大寫或小寫字母
    $pattern = '/[a-z]/';
    return preg_match($pattern, $char);
  }
  
function base64_to_jpeg( $base64_string, $output_file ) {
    $ifp = fopen( $output_file, "wb" ); 
    fwrite( $ifp, base64_decode( $base64_string) ); 
    fclose( $ifp ); 
    return( $output_file ); 
}
/*
function URL_exists($url){
    $headers=@get_headers($url);
    return stripos($headers[0],"200 OK")?true:false;
 }
function _fn_str_arr2str($arr){
    if(count($arr)>0){
        $string = "[";
        foreach($arr as $v){
            $string .= '"'.$v.'",';
        }
        $string = substr($string, 0, -1)."]";
    } else {
        $string = '[]';
    }
    return $string;
}
*/
//全形數字符號轉半形數字符號
/*
function _fn_tai_2tai($str){
	//「臺」
	$f=array("臺灣","臺北","臺中","臺南","臺東");
	//半形英數字及符號
	$h=array("台灣","台北","台中","台南","台東");
	return str_replace($f,$h,$str);
}
*/
//全形數字符號轉半形數字符號
function _fn_num_2sect($str){
	//全形英數字及符號
	$f=array("1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25");
	//半形英數字及符號
	$h=array("一","二","三","四","五","六","七","八","九","十","十一","十二","十三","十四","十五","十六","十七","十八","十九","二十","二十一","二十二","二十三","二十四","二十五");
	return str_replace($f,$h,$str);
}
//全形數字符號轉半形數字符號
/*
function _fn_str_f2h($str){
	//全形英數字及符號
	$f=array(" ","　","０","１","２","３","４","５","６","７","８","９","Ａ","Ｂ","Ｃ","Ｄ","Ｅ","Ｆ","Ｇ","Ｈ","Ｉ","Ｊ","Ｋ","Ｌ","Ｍ","Ｎ","Ｏ","Ｐ","Ｑ","Ｒ","Ｓ","Ｔ","Ｕ","Ｖ","Ｗ","Ｘ","Ｙ","Ｚ","ａ","ｂ","ｃ","ｄ","ｅ","ｆ","ｇ","ｈ","ｉ","ｊ","ｋ","ｌ","ｍ","ｎ","ｏ","ｐ","ｑ","ｒ","ｓ","ｔ","ｕ","ｖ","ｗ","ｘ","ｙ","ｚ","～","！","＠","＃","＄","％","＾","＆","＊","（","）","＿","＋","｜","‘","－","＝","＼","｛","｝","〔","〕","：","”","；","’","＜","＞","？","，","．","／");
	//半形英數字及符號
	$h=array("","","0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","~","!","@","#","\$","%","^","&","*","(",")","_","+","|","`","-","=","\\\\","{","}","[","]",":","\"",";","'","<",">","?",",",".","/");//注意"\\\\"為四條
	return str_replace($f,$h,$str);
}
*/
//中文數字轉阿拉伯數字
function _fn_num_b2h($str){
    $len = mb_strlen($str);
	//全形英數字及符號
	$f=array("〇","一","二","三","四","五","六","七","八","九","十");
	//半形英數字及符號
	$h=array("0","1","2","3","4","5","6","7","8","9","10");
    if($len==1){ return str_replace($f,$h,$str); }
	else if($len==2){
       if(mb_substr($str, 0, 1)=="十"){
            $h[10]="1";
            return str_replace($f,$h,$str); 
        }
       else{
            $h[10]="0";
            return str_replace($f,$h,$str);
        }
    }
    else{
        $h[10]="";
        return str_replace($f,$h,$str);
    }
}

//英文小寫轉大寫
function _fn_key_yq2fh($str){
	$yq=array("manage_cost","all_house_num","ratio","jbrate","purpose_str","purpose_other2","structural_engine","completion_date","floor","build","layout","meeting_address","base_area_num","land_division","park_rate","direction_rule","all_park_num","facility","manage_committee","property_company","lend_rate","price","total_price","park_price","shop_price","deal_time","build_remark","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
	$fh=array("bc_mfee","bc_totalnum","bc_parkratio","bc_coverage","bc_type","bc_usefor","bc_structure","bc_date","bc_floor","bc_building","bc_plan","bc_meetadd","bc_area","bc_zoning","bc_parkratio","bc_direction","bc_parknum","bc_inpublic","bc_rmc","bc_pmc","bc_loan","bc_unitprice","bc_totalprice","bc_pprice","bc_sprice","bc_date","bc_des","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
	return str_replace($yq,$fh,$str);
}

//英文小寫轉大寫
/*
function _fn_str_min2maj($str){
	$min=array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
	$maj=array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
	return str_replace($min,$maj,$str);
}
*/
//阿拉伯數字轉中文數字(運算數值)
function numToWord($num){
    if(!is_numeric($num)) return $num;
    $chiNum = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
    $chiUni = array('','十', '百', '千', '萬', '億', '十', '百', '千');
    $chiStr = '';
    $num_str = (string)$num;
    $count = strlen($num_str);
    $last_flag = true; 
    $zero_flag = true; 
    $temp_num = null; 
    $chiStr = '';
    if ($count == 2) {
        $temp_num = $num_str[0];
        $chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num].$chiUni[1];
        $temp_num = $num_str[1];
        $chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num];
    }else if($count > 2){
        $index = 0;
        for ($i=$count-1; $i >= 0 ; $i--) {
            $temp_num = $num_str[$i];
            if ($temp_num == 0) {
                if (!$zero_flag && !$last_flag ) {
                    $chiStr = $chiNum[$temp_num]. $chiStr;
                    $last_flag = true;
                }
            }else{
                $chiStr = $chiNum[$temp_num].$chiUni[$index%9] .$chiStr;
                $zero_flag = false;
                $last_flag = false;
            }
            $index ++;
        }
    }else{
        $chiStr = $chiNum[$num_str[0]];
    }
    return $chiStr;
}

//Google Map 相關
//合成完整住址
/*
function _fn_get_fulladdress($citY='',$disT='',$adD='',$dash=false){
	$getCityarea = '';
	$getOnlyAdd = '';
	$getFullAdd = '';
	$getGEOAdd = '';
	if(empty($citY)&&empty($disT)&&empty($adD)){
		return false;
	}
	if(!empty($adD)){
		$getOnlyAdd = preg_replace("/\s+/", "", _fn_str_f2h($adD));
        $getOnlyAdd = _fn_tai_2tai($getOnlyAdd);
		$getOnlyAdd = str_replace([$citY,$disT], '', $getOnlyAdd);

////////////////////////////

$fromAddr = str_replace('臺','台',$bcrow['bc_addr']);//臺
$fromAddr = str_replace([$cityText,$distText],'',$fromAddr);
if(strpos($hName,'段')!==false){
    $pureaddr = strchr($hName,'段',true).'段';
    $pureaddr = _fn_num_2sect($pureaddr);
    $addrSplit = explode('里',$pureaddr);
    if(count($addrSplit)>1) $pureaddr = @$addrSplit[1];
} else {
    if(strpos($hName,'路')!==false){
        $pureaddr = strchr($hName,'路',true).'路';
        $pureaddr = _fn_num_2sect($pureaddr);
        $addrSplit = explode('里',$pureaddr);
        if(count($addrSplit)>1) $pureaddr = @$addrSplit[1];
    }elseif(strpos($hName,'街')!==false){
        $pureaddr = strchr($hName,'街',true).'街';
        $pureaddr = _fn_num_2sect($pureaddr);
        $addrSplit = explode('里',$pureaddr);
        if(count($addrSplit)>1) $pureaddr = @$addrSplit[1];
    }elseif(strpos($hName,'村')!==false){
        $pureaddr = strchr($hName,'村',true).'村';
        $pureaddr = _fn_num_2sect($pureaddr);
        $addrSplit = explode('里',$pureaddr);
        if(count($addrSplit)>1) $pureaddr = @$addrSplit[1];
    }elseif(strpos($hName,'場')!==false){
        $pureaddr = strchr($hName,'場',true).'場';
        $pureaddr = _fn_num_2sect($pureaddr);
        $addrSplit = explode('里',$pureaddr);
        if(count($addrSplit)>1) $pureaddr = @$addrSplit[1];
    }elseif(strpos($hName,'道')!==false){
        $pureaddr = strchr($hName,'道',true).'道';
        $pureaddr = _fn_num_2sect($pureaddr);
        $addrSplit = explode('里',$pureaddr);
        if(count($addrSplit)>1) $pureaddr = @$addrSplit[1];
    } 

}
///////////////////////////
	}
	if(!empty($citY) && !empty($disT)){
        
		//$locGet=file_get_contents("wejoin.json");
		//$locJS = json_decode($locGet,true);
		//$getCityarea = $locJS[$citY]['text'].($dash==true ? '-' : '').$locJS[$citY]['district'][$disT]['text'];
        
		$getCityarea = $citY.($dash==true ? '-' : '').$disT;
		$getFullAdd = $getCityarea.$getOnlyAdd;
	}
	if(!empty($getFullAdd)){
        $getGEOAdd=strchr($getFullAdd,"號",true)."號";
    }
    return array("cityarea"=>$getCityarea,"addonly"=>$getOnlyAdd,"fulladd"=>$getFullAdd,"geoadd"=>$getGEOAdd);
}
*/
//使用CURL截取網頁內容
function load_contents($sourceUrl,$curl=false) {//此方式試用特定設定的站點
    if(empty($sourceUrl)){ return false; }
    if($curl==true){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sourceUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_USERAGENT, "Google Bot"); 
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); 
        $rtnConts = curl_exec($ch);
        curl_close($ch);
        //if (empty($rtnConts)){ return false; }
    } else {
        $rtnConts = @file_get_contents($sourceUrl);
    }
    return $rtnConts;
}
//截取兩個指定字串中間的字串
function get_between($str,$start,$end) {
    $start_pos = mb_stripos($str, $start);
    $end_pos = mb_stripos($str, $end);
    $getBetween = mb_substr($str, $start_pos + mb_strlen($start), $end_pos - $start_pos - mb_strlen($start));
    return $getBetween;
}
//計算2個緯度和經度之間的距離
/*
function get_geoDistance($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'kilometers') {
    $theta = $longitude1 - $longitude2; 
    $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta))); 
    $distance = acos($distance); 
    $distance = rad2deg($distance); 
    $distance = $distance * 60 * 1.1515; 
    switch($unit) { 
      case 'miles': 
        break; 
      case 'kilometers' : 
        $distance = $distance * 1.609344; 
    } 
    //return (round($distance,2)); 
    return ($distance*1000); 
  }
  */
  function remove_emoji($string) {

    // Match Emoticons
    $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clear_string = preg_replace($regex_emoticons, '', $string);

    // Match Miscellaneous Symbols and Pictographs
    $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clear_string = preg_replace($regex_symbols, '', $clear_string);

    // Match Transport And Map Symbols
    $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clear_string = preg_replace($regex_transport, '', $clear_string);

    // Match Miscellaneous Symbols
    $regex_misc = '/[\x{2600}-\x{26FF}]/u';
    $clear_string = preg_replace($regex_misc, '', $clear_string);

    // Match Dingbats
    $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
    $clear_string = preg_replace($regex_dingbats, '', $clear_string);

    return $clear_string;
}
/*
function _fn_twd97_to_wgs84($x,$y){//X 橫坐標 //Y 縱坐標
        $a = 6378137.0;
        $b = 6356752.314245;
        $pi = 3.14159265358979323846;
        $lng0 = 121 * $pi / 180;
        $k0 = 0.9999;
        $dx = 250000;
       
        $dy = 0;
        $e = pow((1- pow($b,2)/pow($a,2)), 0.5);
   
        $x -= $dx;
        $y -= $dy;
   
        // Calculate the Meridional Arc
        $M = $y/$k0;

        // Calculate Footprint Latitude
        $mu = $M/($a*(1.0 - pow($e, 2)/4.0 - 3*pow($e, 4)/64.0 - 5*pow($e, 6)/256.0));
        $e1 = (1.0 - pow((1.0 - pow($e, 2)), 0.5)) / (1.0 + pow((1.0 - pow($e, 2)), 0.5));

        $J1 = (3*$e1/2 - 27*pow($e1, 3)/32.0);
        $J2 = (21*pow($e1, 2)/16 - 55*pow($e1, 4)/32.0);
        $J3 = (151*pow($e1, 3)/96.0);
        $J4 = (1097*pow($e1, 4)/512.0);

        $fp = $mu + $J1*sin(2*$mu) + $J2*sin(4*$mu) + $J3*sin(6*$mu) + $J4*sin(8*$mu);

        // Calculate Latitude and Longitude

        $e2 = pow(($e*$a/$b), 2);
        $C1 = pow($e2*cos($fp), 2);
        $T1 = pow(tan($fp), 2);
        $R1 = $a*(1-pow($e, 2))/pow((1-pow($e, 2)*pow(sin($fp), 2)), (3.0/2.0));
        $N1 = $a/pow((1-pow($e, 2)*pow(sin($fp), 2)), 0.5);

        $D = $x/($N1*$k0);

        // 計算緯度
        $Q1 = $N1*tan($fp)/$R1;
        $Q2 = (pow($D, 2)/2.0);
        $Q3 = (5 + 3*$T1 + 10*$C1 - 4*pow($C1, 2) - 9*$e2)*pow($D, 4)/24.0;
        $Q4 = (61 + 90*$T1 + 298*$C1 + 45*pow($T1, 2) - 3*pow($C1, 2) - 252*$e2)*pow($D, 6)/720.0;
        $lat = $fp - $Q1*($Q2 - $Q3 + $Q4);

        // 計算經度
        $Q5 = $D;
        $Q6 = (1 + 2*$T1 + $C1)*pow($D, 3)/6;
        $Q7 = (5 - 2*$C1 + 28*$T1 - 3*pow($C1, 2) + 8*$e2 + 24*pow($T1, 2))*pow($D, 5)/120.0;
        $lng = $lng0 + ($Q5 - $Q6 + $Q7)/cos($fp);

        $lat = ($lat * 180) / $pi; //緯
        $lng = ($lng * 180) / $pi; //經

        $latlng = $lat . "," . $lng;
        return $latlng;
   }
*/

?>
