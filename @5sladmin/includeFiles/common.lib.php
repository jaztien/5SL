<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

///////////////////////////////system functions/////////////////////////////////////
//取得客戶端瀏覽器種類
function _FUN_GET_BROWSER(){

}
//取得客戶端操作平臺
function _FUN_GET_OS(){

}
//取得客戶端IP位置
function _FUN_GET_IPADD() {
	if (isset($_SERVER)) {
	  if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	  } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ip=$_SERVER['HTTP_CLIENT_IP'];
	  } else {
		$ip=$_SERVER['REMOTE_ADDR'];
	  }
	} else {
	  if (getenv('HTTP_X_FORWARDED_FOR')) {
		$ip=getenv('HTTP_X_FORWARDED_FOR');
	  } elseif (getenv('HTTP_CLIENT_IP')) {
		$ip=getenv('HTTP_CLIENT_IP');
	  } else {
		$ip=getenv('REMOTE_ADDR');
	  }
	}
	return $ip;
}
/////////////////////////////// Configurations //////////////////////////////////////


/////////////////////////////// Date & Time ////////////////////////////////////////
//返回指定星期值的中文([星期(0-6)],[完整顯示])
function _FUN_WEEK_BIG5($weeK=0,$fulL=false){
	$getWEEK[0]='日';
	$getWEEK[1]='一';
	$getWEEK[2]='二';
	$getWEEK[3]='三';
	$getWEEK[4]='四';
	$getWEEK[5]='五';
	$getWEEK[6]='六';
	$getHEAD=$fulL==true?'星期':'';
	return $getHEAD.$getWEEK[$weeK];
}
//返回指定日期(YYYYMMDD)處於特定期間內布林值([起始日],[終止日])
function _FUN_INDATE($startDATE='',$endDATE=''){
	$nowDATE=date('Ymd',CF_NOWTIME);
	if (empty($startDATE)==true&&empty($endDATE)==true) return true;
	else{
		if (!empty($startDATE)&&!empty($endDATE)){
			if($startDATE <= $nowDATE && $endDATE >= $nowDATE) return true;
			else return false;
		}
		elseif (!empty($startDATE)&&empty($endDATE)==true){
			if ($startDATE <= $nowDATE) return true;
			else return false;
		}
		elseif (empty($startDATE)==true&&!empty($endDATE)){
			if ($endDATE >= $nowDATE) return true;
			else return false;
		}
	}
}
//指定日期(YYYYMMDD)轉正式日期時間([日期],[分格符])
function _FUN_SHOWDATE($datE='',$sP=''){
	if(!empty($datE)&&!empty($sP)) return substr($datE,0,4).$sP.substr($datE,4,2).$sP.substr($datE,6,2);
	else return $datE;
}
//時間格式轉換([日期],[分格],[格式 (YYYYMMDD,YYYYMMDDHHMM,YYYYMMDDhhmmss)])
function _FUN_DATEFORMAT($datE=null,$sP='',$typE=''){
	$yR=date('Y',strtotime($datE));
	$mN=date('m',strtotime($datE));
	$dY=date('d',strtotime($datE));
	$hR=date('H',strtotime($datE));
	$mI=date('i',strtotime($datE));
	$sD=date('s',strtotime($datE));
	if($typE=='YYYYMMDD') $rtn=$yR.$sP.$mN.$sP.$dY;
	elseif($typE=='YYYYMMDDhhmm') $rtn=$yR.$sP.$mN.$sP.$dY.' '.$hR.':'.$mI;
	elseif($typE=='YYYYMMDDhhmmss') $rtn=$yR.$sP.$mN.$sP.$dY.' '.$hR.':'.$mI.':'.$sD;
	else $rtn=$datE;
	return $rtn;
}
//依TimeStamp取得當日日期 ([時間戳])
function _FUN_DAY_NUM($datE=null){
	if (empty($datE)==true) return 0;
	if (date("n",$datE)==2) {
		if (date("Y",$datE)%4==0) return 29;
		else return 28;
	}
	elseif(date("n",$datE)==4||date("n",$datE)==6||date("n",$datE)==9||date("n",$datE)==11) return 30;
	else return 31;
}
/////////////////////////////// File & Folder /////////////////////////////////////
//取得檔案附檔名或HEADER _FUN_FILE_TYPE([檔案名稱],[附檔名(none or false|HEADER true)])
function _FUN_FILE_TYPE($filE='',$heaD=false){
	$tempARR = explode('.', $filE);
	$tempTYPE = strtolower($tempARR[sizeof($tempARR)-1]);
	if ($tempTYPE !== false){
		if($heaD==true){
			$fileHEADS = array( 
				"jpg" => "image/pjpeg" , 
				"jpge" => "image/pjpeg" , 
				"png" => "image/x-png" , 
				"bmp" => "image/bmp" , 
				"gif" => "image/gif" , 
				"wmv" => "video/x-ms-wmv" ,
				"mp3" => "audio/mpeg" ,
				"mpg" => "video/mpg" ,
				"mpeg" => "video/mpg" ,
				"rm" => "application/vnd.rn-realmedia" ,
				"swf" => "application/x-shockwave-flash" ,
				"asf" => "video/x-ms-asf" ,
				"asx" => "video/x-ms-asf" ,
				"avi" => "video/avi" ,
				"rmvb" => "application/vnd.rn-realmedia-vbr" ,
				"txt" => "text/plain" ,
				"doc" => "application/msword" ,
				"elx" => "application/vnd.ms-excel" ,
				"pdf" => "application/pdf" ,
				"ppt" => "application/vnd.ms-powerpoint" ,
				"html" => "text/html" ,
				"htm" => "text/html" ,
				"zip" => "application/x-zip-compressed"
			);
			$rtn=$fileHEADS[$tempTYPE];
			return !empty($rtn) ? $rtn : 'application/octet-stream';
		}
		else return $tempTYPE;
	}
	else return false;
}
function _FUN_FILE_BASETITLE($filE){
	$tempARR = explode('.', $filE);
	return !empty($tempARR[0]) ? $tempARR[0] : false;
}
/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////// 系統相關函式 //////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////
/**
 * 具備記憶體快取、檔案序列化快取、自動清理與錯誤日誌的共用函式
 * 
 * @param string $jsonPath JSON 檔案路徑
 * @param int $maxCacheAge 快取最大存活時間（秒），預設 7 天 (604800 秒)，若 <= 0 則不主動過期
 * @return array 解析後的陣列資料
 * $artiJS = _fn_getCachedJsonData("../jsonFiles/articles.json");
 */
function _fn_getCachedJsonData($jsonPath, $maxCacheAge = 604800) {
    static $memoryCaches = array();
    // 1. 記憶體靜態快取檢查
    if (isset($memoryCaches[$jsonPath])) {
        return $memoryCaches[$jsonPath];
    }

    if (!file_exists($jsonPath)) {
        error_log("[_fn_getCachedJsonData] 錯誤：找不到指定的 JSON 檔案: " . $jsonPath);
        return array();
    }

    $fileMTime = filemtime($jsonPath);
    $tempDir = sys_get_temp_dir();
    $cacheFile = $tempDir . '/5sl_jsoncache_' . md5($jsonPath) . '.cache';
    // 2. 檢查序列化快取檔案是否有效（存在、未過期、時間戳大於等於原檔）
    if (file_exists($cacheFile)) {
        $cacheMTime = filemtime($cacheFile);
        $isExpired = ($maxCacheAge > 0 && (time() - $cacheMTime) > $maxCacheAge);

        if (!$isExpired && $cacheMTime >= $fileMTime) {
            $serializedData = @file_get_contents($cacheFile);
            $cachedData = @unserialize($serializedData);
            
            if ($cachedData !== false) {
                $memoryCaches[$jsonPath] = $cachedData;
                return $cachedData;
            } else {
                error_log("[_fn_getCachedJsonData] 警告：快取檔還原 (unserialize) 失敗，將重新讀取: " . $cacheFile);
            }
        }
    }

    // 3. 重新讀取與解析 JSON
    $jsonGet = @file_get_contents($jsonPath);
    if ($jsonGet === false) {
        error_log("[_fn_getCachedJsonData] 錯誤：無法讀取檔案內容: " . $jsonPath);
        return array();
    }

    $jsonData = json_decode($jsonGet, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("[_fn_getCachedJsonData] 錯誤：JSON 解析失敗 (" . json_last_error_msg() . "): " . $jsonPath);
        return array();
    }

    $jsonData = $jsonData ?: array();

    // 4. 寫入序列化快取檔案
    $putResult = @file_put_contents($cacheFile, serialize($jsonData), LOCK_EX);
    if ($putResult === false) {
        error_log("[_fn_getCachedJsonData] 警告：無法寫入快取暫存檔到: " . $cacheFile);
    }

    // 5. 附帶清理機制：隨機抽樣 (約 1%) 清理系統暫存夾中過期的舊快取檔案，避免硬碟被垃圾塞爆
    if (mt_rand(1, 100) === 1) {
        _fn_cleanExpiredCaches($tempDir, $maxCacheAge);
    }

    $memoryCaches[$jsonPath] = $jsonData;
    return $jsonData;
}

/**
 * 輔助函式：清理暫存夾內超過存活期限的舊快取檔
 */
function _fn_cleanExpiredCaches($dir='', $maxAge=0) {
    if ($maxAge <= 0) return;
    
    $files = glob($dir . '/5sl_jsoncache_*.cache');
    if ($files === false) return;

    $now = time();
    foreach ($files as $file) {
        if (is_file($file)) {
            // 若檔案年齡大於限制時間則刪除
            if (($now - @filemtime($file)) > $maxAge) {
                @unlink($file);
            }
        }
    }
}

function _fn_cleanAllCaches() {
    $tempDir = sys_get_temp_dir();
    $files = glob($tempDir . '/5sl_jsoncache_*.cache');
    if ($files === false) return;
    foreach ($files as $file) {
        if (is_file($file)) {
			@unlink($file);
        }
    }
}

/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////// 字串相關函式 //////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////

function _fn_replace_unicode_esc($match=array()) {
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}
//轉換 unicode 為正常符號
function _fn_unicode_decode($str='') {
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', '_fn_replace_unicode_esc', $str);
}
//UTF8字串顯示限制 ([原始字串],[顯示字數],[尾端加註...])
function _FUN_UTF8_TEXTCUT($sourceSTR='',$cutLEN=0,$appenD=false){
	$returnSTR='';
	$i=0;
	$n=0;
	$stringLEN=strlen($sourceSTR);//字符串的字節數
	while (($n<$cutLEN) && ($i<=$stringLEN)) {//001
		$tempSTR=substr($sourceSTR,$i,1);
		$ascNUM=Ord($tempSTR);//得到字符串中第$i位字符的ascii碼
		if ($ascNUM>=224){ //如果ASCII位高與224，
			$returnSTR.=substr($sourceSTR,$i,3); //根據UTF-8編碼規範，將3個連續的字符計為單個字符
			$i=$i+3; //實際Byte計為3
			$n++; //字串長度計
		}
		elseif ($ascNUM>=192) {//如果ASCII位高與192，
			$returnSTR.=substr($sourceSTR,$i,2); //根據UTF-8編碼規範，將2個連續的字符計為單個字符
			$i=$i+2; //實際Byte計為2
			$n++; //字串長度計1
		}
		elseif ($ascNUM>=65 && $ascNUM<=90) {//如果是大寫字母，
			$returnSTR.=substr($sourceSTR,$i,1);
			$i=$i+1; //實際的Byte數仍計1個
			$n=$n+0.75; //但考慮整體美觀，大寫字母計成0.75個高位字符
		}
		else {//其他情況下，包括小寫字母和半角標點符號，
			$returnSTR.=substr($sourceSTR,$i,1);
			$i=$i+1; //實際的Byte數計1個
			$n=$n+0.5; //小寫字母和半角標點等與半個高位字符寬...
		}
	}//001
	if($appenD==true&&$stringLEN>$cutLEN) $returnSTR .= "…";//超過長度時在尾處加上省略號
	return $returnSTR;
}
//UTF8字串分行顯示 ([原始字串],[顯示字數],[顯示行數],[尾端加註...])
function _FUN_UTF8_LINESTR($sourceSTR='',$cutLEN=0,$lineNUM=0,$appenD=false){
	$returnSTR='';
	$aRR=array();
	$i=0;
	$n=0;
	$l=1;
	$stringLEN=strlen($sourceSTR);//字符串的字節數
	while ($i<=$stringLEN) {//001
		$tempSTR=substr($sourceSTR,$i,1);
		$ascNUM=Ord($tempSTR);//得到字符串中第$i位字符的ascii碼
		if ($ascNUM>=224){ //如果ASCII位高與224，
			$returnSTR.=substr($sourceSTR,$i,3); //根據UTF-8編碼規範，將3個連續的字符計為單個字符
			$i=$i+3; //實際Byte計為3
			$n++; //字串長度計1
		}
		elseif ($ascNUM>=192) {//如果ASCII位高與192，
			$returnSTR.=substr($sourceSTR,$i,2); //根據UTF-8編碼規範，將2個連續的字符計為單個字符
			$i=$i+2; //實際Byte計為2
			$n++; //字串長度計1
		}
		elseif ($ascNUM>=65 && $ascNUM<=90) {//如果是大寫字母
			$returnSTR.=substr($sourceSTR,$i,1);
			$i=$i+1; //實際的Byte數仍計1個
			$n++; //但考慮整體美觀，大寫字母計成一個高位字符
		}
		else {//其他情況下，包括小寫字母和半角標點符號，
			$returnSTR.=substr($sourceSTR,$i,1);
			$i=$i+1; //實際的Byte數計1個
			$n=$n+0.5; //小寫字母和半角標點等與半個高位字符寬...
		}
		if ($n==$cutLEN||$i==$stringLEN) {
			if ($l==$lineNUM&&$appenD==true) $returnSTR.='...';
			array_push($aRR,$returnSTR);
			if ($l==$lineNUM) break;
			else {
				$returnSTR='';
				$l++;
				$n=0;
			}
		}
	}//001
	return $aRR;
}
function func_stripewhite($stR='')
{
	return preg_replace("/\s+/", "", $stR);
}
//去除標點符號
function _fn_str_removepunc($str='')
{
    $str = str_replace([',','、','|','.','(',')','[',']','。',':','~','「','」','‧'], '', $str);//「-」另外處理，因為當為英文單字中的「-」時則必須保留
	return $str;
}
//全形字轉半形字用
function _fn_str_f2h($str=''){
	//全形英數字及符號
	$f=array("　","０","１","２","３","４","５","６","７","８","９","Ａ","Ｂ","Ｃ","Ｄ","Ｅ","Ｆ","Ｇ","Ｈ","Ｉ","Ｊ","Ｋ","Ｌ","Ｍ","Ｎ","Ｏ","Ｐ","Ｑ","Ｒ","Ｓ","Ｔ","Ｕ","Ｖ","Ｗ","Ｘ","Ｙ","Ｚ","ａ","ｂ","ｃ","ｄ","ｅ","ｆ","ｇ","ｈ","ｉ","ｊ","ｋ","ｌ","ｍ","ｎ","ｏ","ｐ","ｑ","ｒ","ｓ","ｔ","ｕ","ｖ","ｗ","ｘ","ｙ","ｚ","～","！","＠","＃","＄","％","＾","＆","＊","（","）","＿","＋","｜","‘","－","＝","＼","｛","｝","〔","〕","：","”","；","’","＜","＞","？","，","．","／");
	//半形英數字及符號
	$h=array(" ","0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","~","!","@","#","\$","%","^","&","*","(",")","_","+","|","`","-","=","\\\\","{","}","[","]",":","\"",";","'","<",">","?",",",".","/");//注意"\\\\"為四條
	return str_replace($f,$h,$str);
}
//羅馬數字修正
function _fn_str_letter2rom($str=''){
	$str = str_replace(['VIII','III'],['Ⅷ','Ⅲ'],$str);
	$str = str_replace(['VII','II'],['Ⅶ','Ⅱ'],$str);
	if (preg_match('/\W^\-|\s(VI)\W^\-|\s/', $str)) {
		$str =str_replace('VI','Ⅵ', $str);
	}
	if (preg_match('/\W^\-|\s(IV)\W^\-|\s/', $str)) {
		$str =str_replace('IV','Ⅳ', $str);
	}
	if (preg_match('/\W^\-|\s(IX)\W^\-|\s/', $str)) {
		$str =str_replace('IX','Ⅸ', $str);
	}
	if (preg_match('/\W^\-|\s(I)\W^\-|\s/', $str)) {
		$str =str_replace('I','Ⅰ', $str);
	}
	if (preg_match('/\W^\-|\s(V)\W^\-|\s/', $str)) {
		$str =str_replace('V','Ⅴ', $str);
	}
	if (preg_match('/\W^\-|\s(IX)\W^\-|\s/', $str)) {
		$str =str_replace('X','Ⅹ', $str);
	}	
	return $str;
}
//
function _fn_str_extifexist($arr=null){
	$str = implode(",",$arr);
	$rtn = preg_match('/大樓|華廈|別墅|透天|公寓|座|棟|區|特區|樂章|篇|樓/i', $str);
	return $rtn;
}
//英文小寫轉大寫
function _fn_str_min2maj($str=''){
	$min=array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
	$maj=array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
	return str_replace($min,$maj,$str);
}
//英文大寫轉小寫
function _fn_str_maj2min($str=''){
	$maj=array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
	$min=array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
	return str_replace($maj,$min,$str);
}

//全形數字符號轉半形數字符號
function _fn_num_2sect($str=''){
	//全形英數字及符號
	$f=array("1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25");
	//半形英數字及符號
	$h=array("一","二","三","四","五","六","七","八","九","十","十一","十二","十三","十四","十五","十六","十七","十八","十九","二十","二十一","二十二","二十三","二十四","二十五");
	return str_replace($f,$h,$str);
}
//中文數字轉阿拉伯數字
function _fn_num_b2h($str=''){
    $len = mb_strlen($str);
	//全形英數字及符號
	$f=array("〇","一","二","三","四","五","六","七","八","九","十","Ⅰ","Ⅱ","Ⅲ","Ⅳ","Ⅴ","Ⅵ","Ⅶ","Ⅷ","Ⅸ","Ⅹ");
	//半形英數字及符號
	$h=array("0","1","2","3","4","5","6","7","8","9","10","1","2","3","4","5","6","7","8","9","10");
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
function _fn_key_yq2fh($str=''){
	$yq=array("manage_cost","all_house_num","ratio","jbrate","purpose_str","purpose_other2","structural_engine","completion_date","floor","build","layout","meeting_address","base_area_num","land_division","park_rate","direction_rule","all_park_num","facility","manage_committee","property_company","lend_rate","price","total_price","park_price","shop_price","deal_time","build_remark","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
	$fh=array("bc_mfee","bc_totalnum","bc_parkratio","bc_coverage","bc_type","bc_usefor","bc_structure","bc_date","bc_floor","bc_building","bc_plan","bc_meetadd","bc_area","bc_zoning","bc_parkratio","bc_direction","bc_parknum","bc_inpublic","bc_rmc","bc_pmc","bc_loan","bc_unitprice","bc_totalprice","bc_pprice","bc_sprice","bc_date","bc_des","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
	return str_replace($yq,$fh,$str);
}
//阿拉伯數字轉中文數字(運算數值)
function numToWord($num=0){
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
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
//----------------------------------- CURRENCY INFO -------------------------------------	
function _GET_CURR_PRICE($vaL=0,$ratE=1,$deC=0,$eliM=0,$spL=1) {//$eliM 0-保留0 1-去除0  $spL 0-不加','  1-加','
	$getSPL="";
	if (!empty($vaL)) {
		$getSPL=intval($spL)==0 ? "" : ",";
		$getVAL=number_format($vaL/$ratE,intval($deC),'.',$getSPL);
		if ($deC>0&&$eliM==1) {
			$valARR=@explode('.',$getVAL);
			if (intval($valARR[1])==0) $getVAL=$valARR[0];
			else {
				$post="";
				for ($i=1;$i<=$deC;$i++) {
					if (intval(substr($valARR[1],0-$i,1))>0) {
						$post=substr($valARR[1],0,($deC+1)-$i);
						break;
					}
				}
				$getVAL=!empty($post) ? $valARR[0].".".$post : $valARR[0];
			}
		}
		return $getVAL;
	}
	else return $eliM==1 ? 0 : number_format(0,intval($deC),'.',$getSPL);
}
//----------------------------------- ONLINE PAYMENT -------------------------------------	
function _GET_PAY_TYPE($typE=0){
	switch($typE){
		case 1: return '信用卡'; break;
		case 2: return '虛擬帳號轉帳'; break;
		case 4: return 'WebATM'; break;
		case 8: return '便利商店'; break;
		case 9: return '7-11 ibon付款'; break;
		case 10: return '中國銀聯卡'; break;
		case 11: return 'PayPal'; break;
		case 12: return '全家 FamiPort'; break;
		default: return false; break;
	}
}
//----------------------------------- ONLINE PAYMENT -------------------------------------	


//-- --------------------------------------------------------------------------------- --//
//-- -------------------------------- New Functions----------------------------------- --//
//-- --------------------------------------------------------------------------------- --//
//取得前一頁網頁檔案名稱
function _fn_get_prepage($include_extension = true) {
    // 檢查是否有來源網址
    if (!isset($_SERVER['HTTP_REFERER']) || empty($_SERVER['HTTP_REFERER'])) {
        return false;
    } 
    // 解析網址路徑 (例如: /folder/index.php)
    $path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
    if (!$path) {
        return false;
    }
    
    // 取得檔案名稱 (例如: index.php)
    $filename = basename($path);
    
    // 如果不包含副檔名 (例如只要 "index")
    if (!$include_extension) {
        $filename = pathinfo($filename, PATHINFO_FILENAME);
    }
    
    return $filename;
}
//---------- 判斷網址是否存在 ----------  Prepare job
function _fn_url_ifexists($url=''){
    $headers=@get_headers($url);
    return stripos($headers[0],"200 OK")?true:false;
 }

function func_logToString($stR='', $params = null)
{
	foreach ($params as $parameter_key => $parameter) {
		$array_parameter_found = true;
		$stR = preg_replace("/@".$parameter_key."/", $parameter, $stR);
		// removes array form $params
		unset($params[$parameter_key]);
	}
	return $stR;
}

function func_getPageGroup($ni=0)
{
	$strARR[1]="管理首頁控制檯";
	$strARR[2]="系統設定管理";
	$strARR[3]="會員管理";
	$strARR[4]="課程商品管理";
	$strARR[5]="知識庫文章管理";
	$strARR[6]="訊息傳遞管理";
	$strARR[7]="會員活動服務管理";
	$strARR[8]="廣告登載投放管理";
	$strARR[9]="財務會計資料管理";
	if(isset($strARR[$ni])){ return $strARR[$ni]; }
	else { return false; }
}

function _fn_getLogString($rVal='', $oStr='') {
    // 💡 關鍵修正：將 $oStr 強制轉為 string，把 null 轉成 ""，完美解決 PHP 8.1+ 警告
    $template_text = (string)$oStr; 
    
    $params = [];

    // 型態正規化邏輯（維持不變）
    if (is_array($rVal)) {
        $params = $rVal;
    } elseif (is_string($rVal)) {
        $rVal = trim($rVal);
        if (strpos($rVal, '[') === 0 && substr($rVal, -1) === ']') {
            $decoded = json_decode($rVal, true);
            $params = is_array($decoded) ? $decoded : [$rVal];
        } elseif ($rVal !== '') {
            $params = explode(',', $rVal);
        }
    }

    $params = array_map('strval', $params);

    // 執行取代（採用您提議的 ?? 現代化寫法）
    $message = preg_replace_callback('/\{(\d+)\}/u', function($matches) use ($params) {
        $array_index = (int)$matches[1];
        return $params[$array_index] ?? $matches[0];
    }, $template_text);

    return $message!=='' ? $message : '**沒有對應的樣板資料**';
}


//------------------
function _fn_token_verify($uid='',$key='',$time='',$token='',$skiptime=false) {
	if(empty($uid) || empty($key) || empty($time) || empty($token)){
		return array('exit'=>true,'rtn'=>json_encode(array('done'=>0, 'code'=>'0000', 'des'=>'遺失 TOKEN 驗證值，操作失敗並終止！請重新操作或聯絡相關管理人員！！')));
	} else {
		$v_recall = $uid.'|'._fn_secure_crypto($key, 'de').'|'.$time;
		$v_recall = _fn_secure_crypto($v_recall);
		if($token != $v_recall){
			return array('exit'=>true,'rtn'=>json_encode(array('done'=>0, 'code'=>'0000', 'des'=>'TOKEN 驗證值比對不符，操作失敗並終止！請重新操作或聯絡相關管理人員！！')));
		} else {
			if($skiptime==false && time() > $time){
				return array('exit'=>true,'rtn'=>json_encode(array('done'=>0, 'code'=>'0000', 'des'=>'TOKEN 使用期限已過，操作失敗並終止！請重新登入！！')));
			}
		}
	}
	return array('exit'=>false);
}
///////////////////////////////////////////////////////////////////////////////////
////////////////////////// 安全代碼/加密解密函式 /////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////
function _fn_hash_hmac($str='')
{
    $stringHash = hash_hmac(
		'sha256',
		$str,
		'MyCatISaPuppy'
	);
	return $stringHash ?? $str;
}
//------------------- Password Encrypt/Decrypt
//password_hash(【string】, PASSWORD_DEFAULT);
//password_verify(【decrypted Password】, 【encrypted Password】);
//----------------_fn_FH_crypto()-- 這個加解密功能準備被取代
function _fn_FH_crypto($str='',$crypto='de') {
	$passCode = 'eighteenwalnuttrees';
	$encryptMode = 'AES-256-CBC';
	$keyCode = substr(hash('sha256', $passCode, true), 0, 32);
	//$ivCode = chr(112).chr(105).chr(103).chr(103).chr(121).chr(115).chr(101).chr(118).chr(101).chr(114).chr(121).chr(119).chr(104).chr(101).chr(114).chr(101);//piggyseverywhere
	$ivCode = chr(80).chr(105).chr(103).chr(103).chr(121).Chr(84).Chr(97).Chr(105).Chr(108).Chr(79).Chr(110).Chr(77).Chr(121).Chr(67).Chr(97).Chr(116);//PiggyTailOnMyCat
	if($crypto=='de'){
		$crypted = openssl_decrypt(base64_decode($str), $encryptMode, $keyCode, OPENSSL_RAW_DATA, $ivCode);
	} else {
		$crypted = base64_encode(openssl_encrypt($str, $encryptMode, $keyCode, OPENSSL_RAW_DATA, $ivCode));
	}
	return empty($crypted) || $crypted==false ? '' : $crypted;
}

//動態隨機金鑰加解密
//JazNOTE:每次加密的字串都不同，但是解碼後的字串保持原始是一樣的
function _fn_secure_crypto($str='', $action = 'en') {
    $passCode = 'eighteenwalnuttrees';
    $encryptMode = 'AES-256-CBC';
    // 效能優化：金鑰可用單引號，這段維持不變
    $keyCode = substr(hash('sha256', $passCode, true), 0, 32); 
    if ($action === 'de') {
        // 先將 Base64 还原為二進位
        $rawBinary = base64_decode($str);
        if (strlen($rawBinary) < 32) return ''; // 安全檢查
        // 前 16 Bytes 是 IV，後面才是真正的密文
        $ivCode = substr($rawBinary, 0, 16);
        $ciphertext = substr($rawBinary, 16);
        $decrypted = openssl_decrypt($ciphertext, $encryptMode, $keyCode, OPENSSL_RAW_DATA, $ivCode);
        return $decrypted ?: '';
    } else {
        //每次都產生全新、隨機、不可預測的 16 Bytes IV
        $ivCode = openssl_random_pseudo_bytes(16);
        //執行加密（維持原始二進位輸出）
        $ciphertext = openssl_encrypt($str, $encryptMode, $keyCode, OPENSSL_RAW_DATA, $ivCode);
        //將 [16 Bytes IV] + [密文] 拼接後，再一次做 Base64 轉碼存入資料庫
        return base64_encode($ivCode . $ciphertext);
    }
}


function _fn_FHMD5_encrypt($str) {
	$origString=md5($str);
	$saltH = substr($origString,0,12);
	$saltT = substr($origString,-12);
	return md5($saltH.$str.$saltT);
}
//-------------------------------------------------//
//--------------------- 圖像處理 --------------------//
//-------------------------------------------------//
function _fn_image_upload($imgUp='', $patH='', $newW=800, $newH=600)
{
	$imgUpRtn = true;
	if($imgUp=="@DELETE@"){
		if(
			!@unlink($patH.'_.jpg') || !@unlink($patH.'_thumb.jpg') || !@unlink($patH.'_tiny.jpg')
		){
			$imgUpRtn = false;
		}
	} else {
		$image_array_1 = explode(";", $imgUp);
		$image_array_2 = explode(",", $image_array_1[1]);
		$imgUp = base64_decode($image_array_2[1]);
		if (file_put_contents($patH.'_.jpg', $imgUp)!==false){
			chmod($patH.'_.jpg', 0755);
			$toThumb = _fn_resize_crop($patH.'_.jpg', $patH.'_thumb.jpg', (int)($newW/2), (int)($newH/2));
			if($toThumb){
				chmod($patH.'_thumb.jpg', 0755);
				$toTiny = _fn_resize_crop($patH.'_.jpg', $patH.'_tiny.jpg', (int)($newW/4), (int)($newH/4));
				if($toTiny){
					chmod($patH.'_tiny.jpg', 0755);
				} else {
					@unlink($patH.'_thumb.jpg');
					$imgUpRtn = false;
				}
			} else {
				@unlink($patH.'_.jpg');
				$imgUpRtn = false;
			}
		} else {
			$imgUpRtn = false;
		}
		//---------------- 製作縮圖 ----------------//
	}
	return $imgUpRtn;
}

function _fn_image_unlink($patH='')
{
    $imgUpRtn = true;
	if (file_exists($patH.'_.jpg')) {
		if(!@unlink($patH.'_.jpg')){
			$imgUpRtn = false;
		}
	}
	if (file_exists($patH.'_thumb.jpg')) {
		if(!@unlink($patH.'_thumb.jpg')){
			$imgUpRtn = false;
		}
	}
	if (file_exists($patH.'_tiny.jpg')) {
		if(!@unlink($patH.'_tiny.jpg')){
			$imgUpRtn = false;
		}
	}
	return $imgUpRtn;
}

//取得圖像指定等比長寬 ([圖像路徑],[指定寬],[指定長])
function _fn_image_newsize($patH='',$newW=0,$newH=0){
	list($Mwidth, $Mheight)= @getimagesize($patH);	
	$landscape=$Mwidth>$Mheight ? 1 : 0;
	$nMwidth=$newW;
	$nMheight=ceil(($nMwidth*$Mheight)/$Mwidth);
	if ($nMheight<$newH) {
		$nMheight=$newH;
		$nMwidth=floor(($Mwidth*$nMheight)/$Mheight);
	}
	$arr=array('LANDSCAPE'=>$landscape,'WIDTH'=>$nMwidth,'HEIGHT'=>$nMheight);
	return $arr;
}

function _fn_resize_image($file=null, $target='', $w=0, $h=0, $crop=FALSE) {
    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width-($width*abs($r-$w/$h)));
        } else {
            $height = ceil($height-($height*abs($r-$w/$h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        if ($w/$h > $r) {
            $newwidth = $h*$r;
            $newheight = $h;
        } else {
            $newheight = $w/$r;
            $newwidth = $w;
        }
    }
    $src = @imagecreatefromjpeg($file);
    $dst = @imagecreatetruecolor($newwidth, $newheight);
    @imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
	@imagejpeg($dst, $target, 75);
    return $dst;
}
 //$img_to_resize = _fn_resize_image(‘path-to-jpg-image’, 250, 250);

 function _fn_resize_crop($source_file='', $dst_dir='', $max_width=0, $max_height=0, $quality = 80){
    $imgsize = getimagesize($source_file);
    $width = $imgsize[0];
    $height = $imgsize[1];
    $mime = $imgsize['mime'];
    switch($mime){
        case 'image/gif':
            $image_create = "imagecreatefromgif";
            $image = "imagegif";
            break;
        case 'image/png':
            $image_create = "imagecreatefrompng";
            $image = "imagepng";
            $quality = 7;
            break;
        case 'image/jpeg':
            $image_create = "imagecreatefromjpeg";
            $image = "imagejpeg";
            $quality = 80;
            break;
        default:
            return false;
            break;
    }
    $dst_img = @imagecreatetruecolor($max_width, $max_height);
    $src_img = $image_create($source_file); 
    $width_new = $height * $max_width / $max_height;
    $height_new = $width * $max_height / $max_width;
    if($width_new > $width){
        $h_point = (($height - $height_new) / 2);
        @imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
    }else{
        $w_point = (($width - $width_new) / 2);
        @imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
    }
    $image($dst_img, $dst_dir, $quality);
    if($dst_img){ @imagedestroy($dst_img); }
    if($src_img){ @imagedestroy($src_img); }
	return $dst_img;
}

function _fn_image_autothumb($source_file='', $dst_dir='', $longsize=0, $quality = 80){
    $imgsize = @getimagesize($source_file);
    $width = $imgsize[0];
    $height = $imgsize[1];
    $mime = $imgsize['mime'];
    switch($mime){
        case 'image/gif':
            $image_create = "imagecreatefromgif";
            $image = "imagegif";
            break;
        case 'image/png':
            $image_create = "imagecreatefrompng";
            $image = "imagepng";
            $quality = 7;
            break;
        case 'image/jpeg':
            $image_create = "imagecreatefromjpeg";
            $image = "imagejpeg";
            $quality = 80;
            break;
        default:
            return false;
            break;
    }
	if($width > $height){
		$r = $height / $width;
		$width_new = $longsize;
		$height_new = $width_new * $r;
	} else if($height > $width){
		$r = $width / $height;
		$height_new = $longsize;
		$width_new = $height_new * $r;
	} else {
		$width_new = $longsize;
		$height_new = $longsize;
	}
    $dst_img = @imagecreatetruecolor($width_new, $height_new);
    $src_img = $image_create($source_file); 
	@imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $width_new, $height_new, $width, $height);

    $image($dst_img, $dst_dir, $quality);
    if($dst_img){ @imagedestroy($dst_img); }
    if($src_img){ @imagedestroy($src_img); }
	return $dst_img;

}
////////////////////////////////////////////////////////////////
function _fn_get_citydistrict($distCode=''){
	if(empty($distCode)){
		return false;
	} else {
		if(strlen($distCode)!=4){
			return false;
		}		
	}

	$citY = substr($distCode, 0, 2);
	//$disT = substr($distCode, 2, 2);
	$locJS=_fn_getCachedJsonData("../jsonFiles/locations.json");
	$getCityarea = $locJS[$citY]['text'].$locJS[$citY]['district'][$distCode];

	return $getCityarea;
}


//Google Map 相關
//合成完整住址
function _fn_get_fulladdress($citY='',$disT='',$adD='',$sep=''){
	$getCityarea = '';
	$getOnlyAdd = '';
	$getFullAdd = '';
	$getGEOAdd = '';
	if($sep=='dash') { $sep = '-'; }
	elseif($sep=='blank') { $sep = ' '; }
	elseif($sep=='dot') { $sep = '.'; }
	elseif($sep=='underline') { $sep = '_'; }
	if(empty($citY)&&empty($disT)&&empty($adD)){
		return false;
	}
	if(!empty($adD)){
		$getOnlyAdd = preg_replace("/\s+/", "", _fn_str_f2h($adD));
        $getOnlyAdd = _fn_tai_2tai($getOnlyAdd);
	}
	if(!empty($citY) && !empty($disT)){
		$locJS=_fn_getCachedJsonData("../jsonFiles/locations.json");
		$getOnlyAdd = str_replace([$locJS[$citY]['text'], $locJS[$citY]['district']], '', $getOnlyAdd);
		$getCityarea = $locJS[$citY]['text'].$sep.$locJS[$citY]['district'][$disT];
		$getFullAdd = $getCityarea.$getOnlyAdd;
	}
	if(!empty($getFullAdd)){
        $getGEOAdd=strchr($getFullAdd,"號",true)."號";
    }
    return array("cityarea"=>$getCityarea,"addonly"=>$getOnlyAdd,"fulladd"=>$getFullAdd,"geoadd"=>$getGEOAdd);
}
//將主要地區文字「臺」轉換為簡寫「台」，以統一標準
function _fn_tai_2tai($str=''){
	//「臺」
	$f=array("臺灣","臺北","臺中","臺南","臺東");
	//半形英數字及符號
	$h=array("台灣","台北","台中","台南","台東");
	return str_replace($f,$h,$str);
}

//
function _fn_get_datetimeformat($datetimeStr=''){
	if(strlen($datetimeStr)<12){ return false; }
	$getY = substr($datetimeStr, 0, 4);
	$getM = '-'.substr($datetimeStr, 4, 2);
	$getD = '-'.substr($datetimeStr, 6, 2);
	$getH = ' '.substr($datetimeStr, 8, 2);
	$getI = ':'.substr($datetimeStr, 10, 2);
	$getS = ':00';
	if(strlen($datetimeStr)==14){ $getS = ':'.substr($datetimeStr, 12, 2); }
	return $getY.$getM.$getD.$getH.$getI.$getS;
}
//////////////////////////// 擷取網頁內容 //////////////////////////
function _fn_load_contents($sourceUrl='',$curl=false) {//此方式試用特定設定的站點
    if (empty($sourceUrl)) {
        return false;
    }
    if ($curl == true) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sourceUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_USERAGENT, "Google Bot");
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $rtnConts = curl_exec($ch);
        curl_close($ch);
        //if (empty($rtnConts)){ return false; }
    } else {//當不用 CURL 時
        $rtnConts = @file_get_contents($sourceUrl);//直接使用 PHP 內建函式 file_get_contents
    }
    return $rtnConts;
}
/////////////////////////// 截取兩個指定字串中間的字串 ////////////////////////
function _fn_get_between($str='',$start=0,$end=0) {
    $start_pos = mb_stripos($str, $start);
    $end_pos = mb_stripos($str, $end);
    $getBetween = mb_substr($str, $start_pos + mb_strlen($start), $end_pos - $start_pos - mb_strlen($start));
    return $getBetween;
}
//---- 陣列轉JASON字串 ---- Prepare job
function _fn_str_arr2str($arr=null){
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

//------------------ SMS Options ------------------------
function _fn_SMS_messageSend($id=0,$to='',$mess='',$delay=''){
	if(empty($id)||empty($to)||empty($mess)){
		$rtn = array('smsdone'=>'0','smsdes'=>'遺失重要傳遞值，請重新操作!!');
		return false;
	}
	$curl = curl_init();
	// url
	$url = 'https://smsapi.mitake.com.tw/api/mtk/SmSend?';//https://smsapi.mitake.com.tw/api/mtk/SmSend?CharsetURL=UTF-8
	$url .= 'CharsetURL=UTF-8';
	// parameters
	$data = 'username=90113379SMS';//username=90113379SMS&password=Sms90113379&dstaddr=0919218661&smbody=testAPI
	$data .= '&password=Sms90113379';
	$data .= '&dstaddr='.$to;
	$data .= '&smbody='.urlencode($mess);//'親愛的會員,您的 5SeasonLife 人生第五季的簡訊驗證碼為 123456 ,請於5分鐘內完成驗證,謝謝!'
	if(!empty($delay) || !is_numeric($delay)) { $data .= '&dlvtime='.$delay; }//預約發送
	// 設定curl網址
	curl_setopt($curl, CURLOPT_URL, $url);
	// 略過SSL驗證
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	// 設定Header
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded") );
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	curl_setopt($curl, CURLOPT_HEADER,0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	// 執行
	$output = curl_exec($curl);
	curl_close($curl);
	$output = preg_replace("/\r\n|\n|\r|\s/", ",", trim($output));
	$output = preg_replace("/\[[0-9]\],/", "", $output);
	$output = str_replace('=', '":"', $output);
	$output = str_replace(',', '","', $output);
	$output = '{"'.$output.'"}';
	$respJS = json_decode($output,true);// msgid // statuscode // AccountPoint
	$exArr = array('smsdone'=>$respJS['statuscode']<=4?1:0,'smsdes'=>$respJS['statuscode']<=4?'成功發送簡訊!!':'發送簡訊失敗!!','mbid'=>$id);
	//$exArr = array('mbid'=>$id);
	$rtnArray = array_merge($exArr,$respJS);
	return $rtnArray;

}
//------------------ Invoice Options ------------------------
function _fn_invoice_getToken($curlData=array()){

	$curlData = array(
		'Seed' => 'PiggyTailOnMyCat',
		'Authorization' => 'MWY1ZmM2MjhlYzVlZGUyZWIxNjY0MTM4NWY4MGM5MmRjYTZiYWJkMTdjYjk4MGNmYWY5ODZmYjIwNDg4N2QwNQ==',
		'SellerID' => '90113379'
	);
	//202412071749// P9IUD5A3dTEPjwUbGIE2KQ==
	
	$curlData = json_encode($curlData);

	$curl = curl_init();
	$EGUIurl = 'http://eguitest.uxifs.com/eGUICenter/Account/Auth';//測試取得驗證碼
	//$EGUIurl = 'https://egui.uxifs.com/eGUICenter/Account/Auth';//正式取得驗證碼
	//$EGUIurl = 'https://eguitest.uxifs.com/cbe/Published/eInvoiceService.asmx';//測試
	//$EGUIurl = 'https://egui.uxifs.com/eivo/Published/eInvoiceService.asmx';//正式

	// 設定curl網址
	curl_setopt($curl, CURLOPT_URL, $EGUIurl);
	// 略過SSL驗證
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	// 設定Header
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($curlData)
	));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $curlData);//json_data
	curl_setopt($curl, CURLOPT_HEADER,false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$output = curl_exec($curl);
	curl_close($curl);

	//$output = json_decode($output, true);

	return $output;

}

//------------------ Invoice Options ------------------------
function _fn_invoice_requestSend($curlData=array()){
	$curlData = $curlData;
}

//------------------ Export/Import Verification ----------------
function _fn_csvio_getdata($filE='',$getAll=false){
	$fpPath = "../dataFiles/csvio/".$filE;
	$fpFsize=filesize($fpPath)+1;
	$fpInfo = array();
	if(($handle=fopen($fpPath,"r")) !== FALSE){
		$fpRow=1;
		while($data=fgetcsv($handle,$fpFsize,",")){
			if($fpRow==1){
				for ($c=0; $c < 8; $c++) {
					$fpInfo['verify'][$c] = $data[$c];
				}
			} elseif($fpRow==2){
				$num = count($data);
				for ($c=0; $c < $num; $c++) {
					$fpInfo['column'][$c] = $data[$c];
				}
				if($getAll==false){ break; }
			} elseif($fpRow==3){
				//continue;
			} else {
				$num = count($data);
				for ($c=0; $c < $num; $c++) {
					$fpInfo['data'][$fpRow-4][$c] = $data[$c];
				}
			}
			$fpRow++;
		}
	  fclose($handle);
	  return $fpInfo;
	}
	return false;
}
//////////////////////////////////////////////////////////////////////////////
/////////////////////////// 圖像檔案上傳函式 ////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//--------------- _fn_upload_multi 多圖像檔案上傳函式（課程商品管理使用） --------------
function _fn_upload_multi($attachARR=null,$basePath='',$imgBase='',$fleBase='',$thumbSize=[608,342],$tinySize=[400,225]){
	if(empty($attachARR) || (empty($basePath) && (!empty($thumbSize) || !empty($tinySize))) || (!empty($basePath) && !empty($imgBase) && (empty($thumbSize) && empty($tinySize)))){
		return array(
			'upCount' => 0,
			'upDone' => 0,
			'defaultIMG' => '',
			'defaultFLE' => '',
			'defaultLNK' => '',
			'doUpdateArr' => null
		);
	}
	$defaultUpArr = array('photos'=>'','files'=>'','links'=>'');
	$upCount=0;//預計上傳的檔案數量
	$upDone=0;//實際完成上傳的檔案數量
	$photoDeleteNum = 0;
	//$subFolder=ceil(intval($merchID)/100);
	//$basePath = "../uploadFiles/md/".$subFolder;
	//$basePath = $basePath.$subFolder.'/';
	if (!is_dir($basePath)) { mkdir($basePath, 0755, true); }
	//$targetArr = array('photos'=>$basePath."/mdimg_".$merchID."_",'files'=>$basePath."/mdfile_".$merchID."_");
	$targetArr = array('photos'=>$basePath.$imgBase,'files'=>$basePath.$fleBase,'links'=>'');
	$doUpdateArr = array();
	$dolinkArr = array();
	foreach($targetArr AS $key => $val){
		if(!isset($attachARR[$key]) || count($attachARR[$key])==0){
			continue;
		}
		$uploadARR = $attachARR[$key];
		$cnt_upload = count($uploadARR);
		//$cnt_upload = count($attachARR[$key]);
		for($i=0;$i<$cnt_upload;$i++) {
			if($key=='photos' || $key=='files'){//當為圖檔或是檔案時
				$uContent = isset($uploadARR[$i]['src']) ? $uploadARR[$i]['src'] : '';
				$uIdx = isset($uploadARR[$i]['fileUpID']) ? $uploadARR[$i]['fileUpID'] : '';
				$uDefault = isset($uploadARR[$i]['default']) ? $uploadARR[$i]['default'] : '';
				if($uIdx==''){ continue;}
				//---------- 佈局檔案描述內容 ----------
				$uTitle = isset($uploadARR[$i]['fileTitle']) ? $uploadARR[$i]['fileTitle'] : '';
				$uDes = isset($uploadARR[$i]['fileDes']) ? $uploadARR[$i]['fileDes'] : '';
				$uNote = isset($uploadARR[$i]['note']) ? $uploadARR[$i]['note'] : '';
				//-----------------------------------
				if (!empty($uContent)){
					$upCount++;
					//data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/
					$contSplit = explode(";", $uContent);
					$mime_split = explode("/", $contSplit[0]);
					$upExt = strtolower($mime_split[1]);
					if($upExt=='jpeg'){ $upExt = 'jpg'; }
					$getDataSplit = explode(",", $contSplit[1]);
					$uptoPath = $val.$uIdx."_";
					$uptoName = $uptoPath.".".$upExt;
					$uData = base64_decode($getDataSplit[1]);
					if(file_put_contents($uptoName, $uData)){//992*558
						chmod($uptoName, 0755);
						if($key=='photos' && $upExt=='jpg'){
							if($thumbSize && count($thumbSize)>0){
								_fn_resize_image($uptoName, $uptoPath."thumb.".$upExt, $thumbSize[0], $thumbSize[1]);
							}
							if($tinySize && count($tinySize)>0){
								_fn_resize_image($uptoPath."thumb.".$upExt, $uptoPath."tiny.". $upExt, $tinySize[0], $tinySize[1]);
							}
						}
						$upDone++;
						//[寫入JSON後儲存至欄位merch_fileinfo]
						$doUpdateArr[$key][$uIdx]['title'] = $uTitle;
						$doUpdateArr[$key][$uIdx]['des'] = $uDes;
						$doUpdateArr[$key][$uIdx]['note'] = $uNote;
					}
				}//if (!empty($uContent))
				else{
					if($key=='photos' && $uDefault==''){
						$deleteLink = $basePath.$imgBase.$uIdx;//delete link
						@unlink($deleteLink."_.jpg");
						@unlink($deleteLink."_thumb.jpg");
						@unlink($deleteLink."_tiny.jpg");
						$photoDeleteNum++;
					}
				}
			} elseif($key=='links'){
				$uContent = isset($uploadARR[$i]['linkurl']) ? $uploadARR[$i]['linkurl'] : '';
				$uIdx = isset($uploadARR[$i]['linkUpID']) ? $uploadARR[$i]['linkUpID'] : '';
				$uDefault = isset($uploadARR[$i]['default']) ? $uploadARR[$i]['default'] : '';
				$uPlatform = isset($uploadARR[$i]['platform']) ? $uploadARR[$i]['platform'] : '';
				if($uIdx==''){ continue;}
				//---------- 佈局檔案描述內容 ----------
				$uTitle = isset($uploadARR[$i]['linkTitle']) ? $uploadARR[$i]['linkTitle'] : '';
				$uDes = isset($uploadARR[$i]['linkDes']) ? $uploadARR[$i]['linkDes'] : '';
				$uNote = isset($uploadARR[$i]['note']) ? $uploadARR[$i]['note'] : '';
				//-----------------------------------
				if (!empty($uContent)){
					//[寫入JSON後儲存至欄位merch_fileinfo]
					$upCount++;
					$dolinkArr[$uIdx]['linkurl'] = $uContent;
					$dolinkArr[$uIdx]['linkUpID'] = $uIdx;
					$dolinkArr[$uIdx]['default'] = $uDefault;
					$dolinkArr[$uIdx]['platform'] = $uPlatform;
					$dolinkArr[$uIdx]['linkTitle'] = $uTitle;
					$dolinkArr[$uIdx]['linkDes'] = $uDes;
					$dolinkArr[$uIdx]['note'] = $uNote;
					$upDone++;
				}

			}
			if($uDefault){ $defaultUpArr[$key] = $uIdx; }
		}//for
	}//foreach
	
	return array(
		'upCount' => $upCount,
		'upDone' => $upDone,
		'defaultIMG' => $defaultUpArr['photos'],
		'defaultFLE' => $defaultUpArr['files'],
		'defaultLNK' => $defaultUpArr['links'],
		'doUpdateArr' => $doUpdateArr,
		'dolinkArr' => $dolinkArr,
		'doPhotoDel' => $photoDeleteNum
	);

}
//--------------- _fn_upload_multi 多圖像檔案上傳函式（課程商品管理使用） --------------
function _fn_upload_avatar($imgUp=null, $currID=0, $upPath='', $thumbSize=[400, 300], $tinySize=[120, 90]){
	if(empty($imgUp) || empty($currID) || empty($upPath)){ 
		return array('imgUpRtn'=>false,'imgUpDes'=>'遺失重要傳遞值，操作失敗','newUpPhoto'=>'');
	}
	$basePath = '../uploadFiles/'.$upPath.$currID;
	$origPath = $basePath.'_.jpg';
	$thumbPath = $basePath.'_thumb.jpg';
	$tinyPath = $basePath.'_tiny.jpg';
	$newUpPhoto = '';
	$imgUpRtn = false;
	$imgUpDes = '';
	if($imgUp=='@DELETE@'){
		if(
			@unlink($origPath) &&
			@unlink($thumbPath) &&
			@unlink($tinyPath)
		){
			$imgUpRtn = true;
			$imgUpDes = ',並成功刪除圖像';
			if($basePath === '../uploadFiles/ad/adphoto_'.CF_AD_ID){
				$_SESSION['SESS_AD_THUMB'] = 'imageFiles/nopic_user_128x128.jpg';
				$newUpPhoto ='../imageFiles/nopic_user_200x200.jpg';
			}
		}
	} else {
		$image_array_1 = explode(";", $imgUp);
		$image_array_2 = explode(",", $image_array_1[1]);
		$imgUp = base64_decode($image_array_2[1]);
		//$imageName = $origPath;
		if (file_put_contents($origPath, $imgUp)!==false){
			chmod($origPath, 0755);
		}
		//---------------- 製作縮圖 ----------------//
		//$thumbName = $thumbPath;
		$toThumb = _fn_resize_crop($origPath, $thumbPath, $thumbSize[0], $thumbSize[1]);
		if($toThumb){
			chmod($thumbPath, 0755);
			$toTiny = _fn_resize_image($thumbPath,$tinyPath, $tinySize[0], $tinySize[1]);
			if($toTiny){
				chmod($tinyPath, 0755);
				$imgUpRtn = true;
				$imgUpDes = ',並成功上傳圖像且製作縮圖成功';
				$newUpPhoto = $thumbPath;
			}
		}
	}
	return array('imgUpRtn'=>$imgUpRtn,'imgUpDes'=>$imgUpDes,'newUpPhoto'=>$newUpPhoto);

}


/////////////////////////////// Email Sending /////////////////////////////////////
function _fn_email_sending($subJect='',$htmlBody='',$setTo=[],$setCC=[],$setBCC=[],$markerARR=[],$replaceARR=[]){
    
    //取得紀錄內容
    require '../pluginFiles/phpmailer/src/Exception.php';
    require '../pluginFiles/phpmailer/src/PHPMailer.php';
    require '../pluginFiles/phpmailer/src/SMTP.php';
    /*
    $getArtiARR = $DB->row("SELECT arti_subscript,arti_body FROM articles WHERE arti_keycode=? AND arti_status=?",array('PUBSUCCESS',1));
    if(!$getArtiARR || empty($getArtiARR['arti_subscript']) || empty($getArtiARR['arti_body'])){
        return false;
    }
    */
    $cDateTime = date('Y-m-d H:i:s');
    //---- 設定固定值 ----//
    $setHost = 'smtp.gmail.com';
    $setPort = '587';
    $setFrom = 'jaztien@gmail.com';
    $setFromTitle = '人生第五季管理中心';
    $setUsername = 'jaztien@gmail.com';
    $setPassword = 'uikw gwnp zvcs sbhi';//uikw gwnp zvcs sbhi (應用程式密碼)
    //---- /設定固定值 ----//
    //---- 設定變數值 ----//
    //$markerARR=array("[@nowdate@]","[@currdate@]","[@username@]","[@userid@]","[@inpoint@]","[@nowpoint@]","[@comptel@]","[@compext@]");
    //$toStr=array($cDateTime,"2024-06-08",CF_AD_NICK,1,"500","2650","02-23456789","888");

    //$setFromMail = $setFrom[0];
    //$setFromName = $setFrom[1];
    //$setFrom=[$_SESSION['SESS_AD_EMAIL'],CF_AD_NICK];
    //$setToMail = $_SESSION['SESS_AD_EMAIL'];
    //$setToName = "mailto";
    //---- /設定變數值 ----//
    
    $htmlBody = stripslashes($htmlBody);//主文
	$subJect = stripslashes($subJect);
    if(!empty($markerARR) && !empty($replaceARR)) { $subJect = str_replace($markerARR,$replaceARR,$subJect); }//標題
    $subJect = "=?UTF-8?B?".base64_encode($subJect)."?=";//格式化標題(避免亂嗎)

    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->SMTPDebug = 0;//0
    $mail->Host = $setHost;
    //$mail->Port = 465;  //SSL
    $mail->Port = $setPort;  //TLS
    //$mail->SMTPSecure = 'ssl';//ssl
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->SMTPAuth = true;
    
    $mail->Username = $setUsername;
    $mail->Password = $setPassword;//uikw gwnp zvcs sbhi (應用程式密碼)

    //$mail->From = $setFromMail; //設定寄件人電郵
    //$mail->FromName = $setFromName; //設定寄件人名稱
    $mail->setFrom($setFrom, $setFromTitle);
    for($i=0;$i<count($setTo);$i++){
        $mail->addAddress($setTo[$i][0], $setTo[$i][1]);
    }
    if(count($setCC)>0){
        for($i=0;$i<count($setCC);$i++){
            $mail->addCC($setCC[$i][0], $setCC[$i][1]); //設定收件人電郵及名稱(CC)
        }
    }
    if(count($setBCC)>0){
        for($i=0;$i<count($setBCC);$i++){
            $mail->addBCC($setBCC[$i][0], $setBCC[$i][1]); //設定收件人電郵及名稱(BCC)
        }
    }
    $mail->Subject = $subJect; //信件標題，解決亂碼問題

    $mail->IsHTML(true);
    //$htmlbody=str_replace("<table>",'<table width="100%">',$htmlbody);
    //$htmlImgARR = explode("../uploadFiles/sysimg/",$htmlBody);
    //---- 解析並替換 HTML 主文中的圖檔 ----//
    $htmlImgARR = explode('data-file-name="',$htmlBody);
    for($i=1;$i<count($htmlImgARR);$i++){
      $getPureARR = explode('" data-file-size="',$htmlImgARR[$i]);
      $getPureName = trim($getPureARR[0]);
      $getPureCID = pathinfo($getPureName, PATHINFO_FILENAME);
      $htmlBody=str_replace("../uploadFiles/sysimg/".$getPureName,"cid:".$getPureCID,$htmlBody);
      $mail->AddEmbeddedImage('../uploadFiles/sysimg/'.$getPureName, $getPureCID, $getPureName); 
      //$mail->AddEmbeddedImage('sysimg_20240901024320832.jpg');//cid:fh-logo02//嵌入圖檔
    }
    //---- /解析並替換 HTML 主文中的圖檔 ----//
    //---- 解析並替換 HTML 主文中的文字 ----//
    if(!empty($markerARR) && !empty($replaceARR)) { $htmlBody = str_replace($markerARR,$replaceARR,$htmlBody); }
    //---- /解析並替換 HTML 主文中的文字 ----//
    //---- 送出 HTML 主文 ----//
    $mail->Body = $htmlBody;//'This is the HTML message body in bold!';
    //---- /送出 HTML 主文 ----//
    //傳送 CC/BCC 
    //$mail->addCC("personC@gmail.com", "person C"); //設定收件人電郵及名稱(CC)
    //$mail->addBCC("personD@gmail.com", "person D"); //設定收件人電郵及名稱(BCC)
    //$mail->addAttachment("image1.jpg", "picture.jpg"); //設定附件, 對方會看到附件名稱為 picture.jpg

    return $mail->send();

}

/////////////////////////////// 會員等級計算 /////////////////////////////////////
function _fn_mblevel_status($mbLevel=0, $pointIN=0){
	if(empty($mbLevel)){ return false; }
	$geJS=_fn_getCachedJsonData("../jsonFiles/member.json");
	$needUpdate = 0;
	$newLevel = $mbLevel;
	$nextIDX = $mbLevel<5 ? intval($mbLevel+1) : 5; 
	$newLvlObj = $geJS['levels'][$mbLevel];
	$nextLvlObj = $geJS['levels'][$nextIDX];
	foreach($geJS['levels'] as $key => $value){
		if($pointIN>=intval($value['min']) && $pointIN<=intval($value['max'])){
			if($mbLevel != $key){
				$needUpdate = 1;
				$newLevel = $key;
				$newLvlObj = $value;
				$nextIDX = $key<5 ? intval($key+1) : 5;
				$nextLvlObj = $geJS['levels'][$nextIDX];
				break;
			}
		}
		continue;
	}
	return array('mbLevel'=>$mbLevel, 'newLevel'=>$newLevel, 'pointIN'=>$pointIN, 'newLvlObj'=>$newLvlObj, 'nextLvlObj'=>$nextLvlObj, 'needUpdate'=>$needUpdate);
}
/////////////////////////////// 應答室內容篩檢 /////////////////////////////////////
function _fn_message_filter($message=''){
	$passThu = 1;
	$count = 0;
	$preArr = array(
	  '請問一下','請問你','請問您','請問','請教一下',
	  '請教你','請教您','請教','你好','您好','hello',
	  'hi','求助一下','求助','幫忙一下','幫幫忙','幫忙',
	  '請幫忙一下','請幫幫忙','請幫忙'
	);
	$postArr = array(
	  '請給我','為何','為什麼','是什麼','是啥','什麼是','什麼叫做','什麼叫','什麼又是','什麼又叫做','什麼又叫','是不是就是','是不是','代表什麼','表示什麼','意思是什麼','的說明','操作','進行','執行','還有','另外','除此之外','除了這個',' '
	);
	if($passThu == 1){
	  $message = str_replace([',','，','。','；',';','、'],'｜',$message);
  
	  $char = "，。、！？：；﹑•＂…‘’“”〝〞∕¦‖—　〈〉﹞﹝「」‹›〖〗】【»«』『〕〔》《﹐¸﹕︰﹔！¡？¿﹖﹌﹏﹋＇´ˊˋ―﹫︳︴¯＿￣﹢﹦﹤‐­˜﹟﹩﹠﹪﹡﹨﹍﹉﹎﹊ˇ︵︶︷︸︹︿﹀︺︽︾ˉ﹁﹂﹃﹄︻︼（）";
	  $pattern = array(
		  "/[[:punct:]]/i", //英文標點符號
		  '/['.$char.']/u', //中文標點符號
		  '/[ ]{2,}/'
	  );
	  $message = preg_replace($pattern, '', $message);
	  $message = strtolower( $message );
	  $messSplit = explode('｜',$message);
	  $messSplit = array_values(array_filter($messSplit));
	  $count = count($messSplit);
	  if($count==1){
		$getIDX = array_search($messSplit[0],$preArr);
		if($getIDX > -1){
		  //$message = $preArr[$getIDX];
		  //$passThu = 0;
		  return array($preArr[$getIDX]);
		}
	  }
	}
  
	if($passThu == 1){
	  $message = str_replace(array_merge($preArr,$postArr),'',$message);
	  $messSplit = explode('｜',$message);
	  $messSplit = array_values(array_filter($messSplit));
	  if($count > 0 && $count <= 5){
		return $messSplit;
	  }
	}
	return array();
  }

////////////////////////////////////////////////////////////////////////////
/////////////////////////////// 字串處理函式 /////////////////////////////////
////////////////////////////////////////////////////////////////////////////
//------------------------ 取得並生成 SQL Query 字串 -------------------------
function _fn_getQueryString($aRR = array(), $moDE='up')
{
	if(empty($aRR)) { return false; }
	$intoQuery = '';
	$valueQuery = '';
	$queryStr = '';
	foreach($aRR as $key => $val){
		if($moDE == 'up'){
			$queryStr .= $key."='".$val."',";
		} else {
			$intoQuery .= $key.',';
			$valueQuery .= "'".$val."',";
		}
	}
	if ($moDE == 'up') {
		$queryStr = substr($queryStr, 0, -1);
		return $queryStr;
	} else {
		$intoQuery = substr($intoQuery, 0, -1);
		$valueQuery = substr($valueQuery, 0, -1);
		return array('intoQuery' => $intoQuery, 'valueQuery' => $valueQuery);
	}
	
}
//------------------------ 將字串隱藏（加「*」處理） -------------------------

function _fn_maskStringCustom($str='') {
    $len = mb_strlen($str, 'UTF-8');
    // 條件 1：長度小於等於 0，直接回傳空字串
    if ($len <= 0) {
        return '';
    }
    // 條件 2：長度等於 1，直接以「*」代替
    if ($len == 1) {
        return '*';
    }
    // 條件 3：長度等於 2，僅保留第一個字元，第二個字元變成「*」
    if ($len == 2) {
        return mb_substr($str, 0, 1, 'UTF-8') . '*';
    }
    // 條件 4：長度大於 2，保留首尾，其餘變成「*」
    $first = mb_substr($str, 0, 1, 'UTF-8');
    $last = mb_substr($str, -1, 1, 'UTF-8');
    $mask = str_repeat('*', $len - 2);
    return $first . $mask . $last;
}

////////////////////////////////////////////////////////////////////////////
////////////////////////////// 時間軸操作函式 /////////////////////////////////
////////////////////////////////////////////////////////////////////////////
//----------------- 取得日期區間內的日期陣列（以日期為單位） ----------------------
function _fn_getDateFromRange ($startdate='', $enddate='') {
	$stimestamp = strtotime($startdate);
	$etimestamp = strtotime($enddate);
	// 計算日期段內有多少天
	$days = ($etimestamp-$stimestamp)/86400+1;
	// 保存每天日期
	$date = array();
	for ($i = 0; $i < $days; $i++) {
		//$date[] = date('Y-m-d', $stimestamp + (86400 * $i));
		$nowStamp = $stimestamp + (86400 * $i);
		$date[] = array(
			'year' => date('Y', $nowStamp),
			'month' => date('m', $nowStamp),
			'day' => date('d', $nowStamp),
			'week' => date('w', $nowStamp)
		);
	}
	return $date;
}
/**
 * 計算文章建立至今過了多少小時（保留一位小數）
 * 
 * @param string|null $created_at 文章建立時間 (格式: yyyy-mm-dd hh:mm:ss)
 * @return string 回傳格式化後的字串（例如: "5.3 小時前"）
 */
function _fn_hours_passed(?string $created_at): string 
{
    // 1. 檢查是否為空值
    if (empty($created_at)) {
        return false;
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $created_at)) {
        $created_at = $created_at . " 00:00:00";
    }

    // 2. 將字串轉為時間戳記
    $created_time = strtotime($created_at);
    
    // 如果解析失敗（非合法時間格式），防錯回傳
    if ($created_time === false) {
        //return '時間格式錯誤';
        return false;
    }

    // 3. 計算與當下的秒數差並轉為小時
    $passed_hours = (time() - $created_time) / 3600;

    // 4. 防止未來時間導致負數
    if ($passed_hours < 0) {
        $passed_hours = 0.0;
    }

    // 5. 格式化回傳
    return number_format($passed_hours, 1, '.', '');
}


////////////////////////////////////////////////////////////////////////////
////////////////////////// 人生第五季後備工作操作函式 //////////////////////////////
////////////////////////////////////////////////////////////////////////////

/**
 * 通用資格與優惠條件規則驗證引擎
 * @param object $DB          PDO 資料庫物件
 * @param int    $mb_id       會員 ID
 * @param int    $target_type 1=課程方案, 2=禮品優惠, 3=限定素材
 * @param int    $target_id   目標主鍵 ID
 * @return array ['pass' => bool, 'message' => string]
 */
function _fn_checkEligibility($DB, $mb_id, $target_type, $target_id) {
    $rules = $DB->query(
        "SELECT * FROM fsl_eligibility_rules WHERE erule_target_type = ? AND erule_target_id = ? AND erule_status = 1 ORDER BY erule_sort ASC, erule_id ASC",
        array((int)$target_type, (int)$target_id)
    );

    // 若無設定任何限制條件，直接放行
    if (empty($rules) || !is_array($rules)) {
        return array('pass' => true, 'message' => '');
    }

    // 預先撈取會員基本資料（含加密欄位解密）
    $member = ($mb_id > 0) ? $DB->row("SELECT * FROM fsl_members WHERE mb_id = ?", array((int)$mb_id)) : null;
    if (!$member) {
        return array('pass' => false, 'message' => '請先登入會員帳號以驗證優惠資格！');
    }

    foreach ($rules as $r) {
        $type = trim((string)$r['erule_type']);
        $op   = strtoupper(trim((string)$r['erule_operator']));
        $val  = trim((string)$r['erule_val']);
        $msg  = trim((string)$r['erule_fail_msg']) ?: '您目前不符合此優惠方案的適用條件。';
        $ok   = false;

        switch ($type) {
            // 1. 報名/活動時間區間 (BETWEEN: 2026-11-01 00:00:00,2026-11-30 23:59:59)
            case 'time_range':
                $parts = array_map('trim', explode(',', $val));
                if (count($parts) === 2) {
                    $nowTs = time();
                    $ok = ($nowTs >= strtotime($parts[0]) && $nowTs <= strtotime($parts[1]));
                }
                break;

            // 2. 會員性別 (F / M / N)
            case 'gender':
                $mbGender = strtoupper(trim((string)($member['mb_gender'] ?? 'N')));
                $allowArr = array_map('trim', explode(',', strtoupper($val)));
                $ok = ($op === 'NOT_IN' || $op === '!=')
                    ? !in_array($mbGender, $allowArr, true)
                    : in_array($mbGender, $allowArr, true);
                break;

            // 3. 當月壽星驗證 (比對 fsl_members.mb_birth 的月份)
            case 'birthday_month':
                if (!empty($member['mb_birth'])) {
                    $birthMonth = (int)date('m', strtotime($member['mb_birth']));
                    $nowMonth   = (int)date('m');
                    $ok = ($birthMonth === $nowMonth);
                }
                break;

            // 4. 居住城市篩選 (例如：01,02 代表台北市、新北市)
            case 'city':
                $mbCity   = trim((string)($member['mb_city'] ?? ''));
                $cityList = array_map('trim', explode(',', $val));
                $ok = ($mbCity !== '') && (
                    ($op === 'NOT_IN' || $op === '!=')
                        ? !in_array($mbCity, $cityList, true)
                        : in_array($mbCity, $cityList, true)
                );
                break;

            // 5. 會員等級門檻 (mb_level)
            case 'mb_level':
                $mbLvl = (int)($member['mb_level'] ?? 1);
                if ($op === '>=') $ok = ($mbLvl >= (int)$val);
                elseif ($op === '<=') $ok = ($mbLvl <= (int)$val);
                elseif ($op === 'IN') $ok = in_array((string)$mbLvl, array_map('trim', explode(',', $val)), true);
                else $ok = ($mbLvl === (int)$val);
                break;

            // 6. MySQL 累計消費金額門檻查詢 (fsl_orders)
            case 'total_spent':
                $spent = (float)$DB->single(
                    "SELECT COALESCE(SUM(ord_total), 0) FROM fsl_orders WHERE mb_id = ? AND ord_status IN (2, 3, 4)",
                    array((int)$mb_id)
                );
                $ok = ($op === '<=') ? ($spent <= (float)$val) : ($spent >= (float)$val);
                break;

            // 7. 身分證字號尾數篩選 (例如：1,3,5,7,9 代表奇數尾號)
            case 'id_last_digit':
                if (!empty($member['mb_idno'])) {
                    $deIdNo    = _fn_secure_crypto($member['mb_idno'], 'de');
                    $lastDigit = substr(trim($deIdNo), -1);
                    $allowDigs = array_map('trim', explode(',', $val));
                    $ok = in_array($lastDigit, $allowDigs, true);
                }
                break;

            // 8. 白名單自訂 PHP 擴充函式 (例如 erule_val = '_fn_custom_rule_vip_first_order')
            case 'php_callback':
                if (preg_match('/^_fn_custom_rule_[a-zA-Z0-9_]+$/', $val) && function_exists($val)) {
                    $ok = (bool)$val($DB, $member, $target_id);
                }
                break;
        }

        // 只要有任何一條規則未通過，立即中斷並回傳該條規則的專屬提示警語
        if (!$ok) {
            return array('pass' => false, 'message' => $msg);
        }
    }

    return array('pass' => true, 'message' => '');
}


?>
