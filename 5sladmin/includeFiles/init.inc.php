<?php
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
ini_set('display_errors',        1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); //E_ALL&~E_NOTICE
@session_start();
if(!isset($_SESSION['SESS_AD_ID']) || empty($_SESSION['SESS_AD_ID'])){
	//header("location: ../5sladmin.php?e=9");//遺失 SESSION，可能閒置超時，系統自動登出！
	echo "<script>top.window.location = '../5sladmin.php?e=9'</script>";
	die;
	exit();
}
//$_SESSION['SESS_AD_ID'] = 1;
/*
@ini_set('date.timezone','Asia/Taipei');  
@ini_set('memory_limit',          '64M');
@ini_set('session.cache_expire',  180);
@ini_set('session.use_trans_sid', 0);
@ini_set('session.use_cookies',   1);
@ini_set('session.auto_start',    0);
*/
define('DOC_ROOT_PATH', str_replace('includeFiles/init.inc.php', '', str_replace('\\', '/', __FILE__)));
//define('CONFIG_NOWTIME', time()+(3600*8));
define('CF_NOWTIME', time());
define('CF_NOWDAY', date("d"));
define('CF_NOWMON', date("m"));
define('CF_NOWYEAR', date("Y"));
define('CF_NOWWEEK', date("l"));
//定義整個系統所在DNS下的資料夾（若是放置在根目錄下，則此值為空）
define('DOC_DIR', '5sladmin/');
//定義頁面所在路徑，DNS之後含檔案夾及檔案名稱，例如 /5sladmin/pageFiles/sys_admin.php
if (isset($_SERVER['PHP_SELF'])){
    define('PHP_SELF', $_SERVER['PHP_SELF']);
}else{define('PHP_SELF', $_SERVER['SCRIPT_NAME']);}
//定義頁面所在檔案名稱
define('URI_SELF', basename(__FILE__));
/*
if (isset($_SERVER['REQUEST_URI'])){
	$tempURIPATH=@explode('/',$_SERVER['REQUEST_URI']);
	define('URI_SELF', $tempURIPATH[sizeof($tempURIPATH)-1]);
}else{define('URI_SELF', PHP_SELF);}
*/
//定義根目錄網址
define('DOMAIN_ROOT',_FUN_GET_DOMAIN());
//定義系統檔案所在網址
define('DOC_ROOT_URL', _FUN_GET_DOMAIN().'/'.DOC_DIR);

//計算路徑(返回文件目錄「../」層數)
$folderLEVEL = 3;//決定返回至幾層之前，通常目標為根目錄或檔案所在資料夾同層。
$folderPATH = "";
$pathSplit = @explode('/',PHP_SELF);
$currDirLEVEL = sizeof($pathSplit);
$folderPATH = str_repeat("../",$currDirLEVEL-$folderLEVEL-1);//取得返回根目錄需要的層數
$pagepath = '';
for ($i=$folderLEVEL;$i<$currDirLEVEL;$i++){
	$pagepath .= $pathSplit[$i]."/";
}
$pagepath = substr($pagepath, 0, -1);//取得根目錄起始的檔案路徑
//$_SESSION['SESS_BACK_PATH'] = $pagepath;

/*
for ($i=0;$i<$currDirLEVEL-$folderLEVEL-1;$i++){
	$folderPATH = $folderPATH."../";
}
*/
define('DOC_PATH_LAYER',$folderPATH);
//定義所在頁面的完整路徑
define('PAGEURL_NOW',DOMAIN_ROOT.PHP_SELF);
define('PAGEPATH_NOW',$pagepath);
//定義現登錄的管理者ID
define('CF_AD_ID', (!defined('CF_AD_ID')) ? @intval($_SESSION['SESS_AD_ID']):0);
define('CF_AD_NICK', (!defined('CF_AD_NICK')) ? @$_SESSION['SESS_AD_NICK']:'');
define('CF_AD_LEVEL', (!defined('CF_AD_LEVEL')) ? @intval($_SESSION['SESS_AD_AUTH']):0);
// ------------------------------ Functions ------------------------------------- //
function _FUN_GET_DOMAIN(){
	$protoCOL = _FUN_GET_HTTP();
	if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])){$hosT = $_SERVER['HTTP_X_FORWARDED_HOST'];}
	elseif (isset($_SERVER['HTTP_HOST'])){$hosT = $_SERVER['HTTP_HOST'];}
	else{
		if (isset($_SERVER['SERVER_PORT'])){
			$porT = ':' . $_SERVER['SERVER_PORT'];
			if ((':80' == $porT && 'http://' == $protoCOL) || (':443' == $porT && 'https://' == $protoCOL)){$porT = '';}
		}
		else{$porT = '';}
		if (isset($_SERVER['SERVER_NAME'])){$hosT = $_SERVER['SERVER_NAME'] . $porT;}
		elseif (isset($_SERVER['SERVER_ADDR'])){$hosT = $_SERVER['SERVER_ADDR'] . $porT;}
	}
	return $protoCOL . $hosT;
}

function _FUN_GET_HTTP(){
	return (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
}

function _FUN_GET_IP(){
	if(!empty($_SERVER['HTTP_CLIENT_IP'])){
		//ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		//ip pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
  }
  

function _FUN_ADDSLASHES($value){
    if (!empty($value)){return is_array($value) ? array_map('_FUN_ADDSLASHES', $value) : addslashes($value);}
	else{return $value;}
}
// ------------------------------------------------------------------- //

if (!ini_get('magic_quotes_gpc')){
    if (!empty($_GET)){$_GET =_FUN_ADDSLASHES($_GET);}
    if (!empty($_POST)){$_POST=_FUN_ADDSLASHES($_POST);}
    $_COOKIE  =_FUN_ADDSLASHES($_COOKIE);
    $_REQUEST =_FUN_ADDSLASHES($_REQUEST);
}

//若無前一頁的資訊時，則前一頁取自 $_SESSION['SESS_PAGEBACK']
if(isset($_SERVER['HTTP_REFERER'])){
    define('PAGEURL_BACK',strtok($_SERVER['HTTP_REFERER'], '?'));
} else {
    define('PAGEURL_BACK',isset($_SESSION['SESS_PAGEBACK']) ? $_SESSION['SESS_PAGEBACK'] : PAGEURL_NOW);
}
//在定義前一頁 PAGEURL_BACK 之後，更新 $_SESSION['SESS_PAGEBACK'] 到現在所在頁面，留待轉頁後處理
$_SESSION['SESS_PAGEBACK'] = PAGEURL_NOW;
//若有 TOKEN 驗證要求時，進行雙重認證。
/*
if(isset($_POST['token']) && $_POST['token']!=''){
    if($_POST['token'] != $_SESSION['token']){
        if(PAGEURL_BACK != PAGEURL_NOW){//避免前後頁都是同一頁時，造成的無限迴圈
        	//header('Location: ' . PAGEURL_BACK . '?msg=2');
            //die();
			//echo "<script type='text/javascript'>window.top.location='".PAGEURL_BACK."?msg=2';</script>"; 
			//exit();
        }
		
    }
}
*/
?>
