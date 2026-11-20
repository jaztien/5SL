<?php
error_reporting(E_ALL); //E_ALL&~E_NOTICE
ini_set('display_errors',        1);
if (!isset($current_page)) {
    $current_page = 'index';
}
// ==========================================================================
// 🛡️ 5SL 全站會員中心 PHP Session 安全攔截防線 (fun_auth_guard.php)
// ==========================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 定義受保護的會員中心頁面清單
$protected_member_pages = [
    'myConsole',
    'myAccount',
    'myProducts',
    'myGifts',
    'myOrders',
    'myQuests'
];

// 檢查當前頁面是否屬於受保護範圍
if (isset($current_page) && in_array($current_page, $protected_member_pages, true)) {
    
    // 驗證 Session 中是否存在有效的會員登入憑證
    if (!isset($_SESSION['mbID']) || empty($_SESSION['mbID'])) {
        
        // 紀錄原本想前往的網址，登入成功後可自動導回原頁面
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '5sl_myConsole.php';
        $_SESSION['auth_flash_msg'] = '請先登入您的會員帳號，方可存取會員中心功能。';

        // 若為 AJAX 異步請求，回傳標準 401 JSON 狀態碼
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8', true, 401);
            echo json_encode([
                'success' => false,
                'code' => 401,
                'message' => '登入逾時或尚未登入，請重新登入。',
                'redirect' => '5sl_login.php'
            ]);
            exit;
        }

        // 一般網頁瀏覽請求：立即在伺服器端阻斷並導向登入頁
        header('Location: 5sl_login.php');
        exit;
    }
}

// 強制網頁不進入瀏覽器快取
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

define('DOC_ROOT_PATH', str_replace('5sladmin/includeFiles/cli.init.inc.php', '', str_replace('\\', '/', __FILE__)));
//定義整個系統所在DNS下的資料夾（若是放置在根目錄下，則此值為空）
define('DOC_DIR', '');
//定義頁面所在路徑，DNS之後含檔案夾及檔案名稱，例如 /5sladmin/pageFiles/sys_admin.php
if (isset($_SERVER['PHP_SELF'])){
    define('PHP_SELF', $_SERVER['PHP_SELF']);
}else{define('PHP_SELF', $_SERVER['SCRIPT_NAME']);}
//定義頁面所在檔案名稱
define('URI_SELF', basename(__FILE__));
//定義根目錄網址
define('DOMAIN_ROOT',_FUN_GET_DOMAIN());
//定義系統檔案所在網址
define('DOC_ROOT_URL', DOMAIN_ROOT.'/'.DOC_DIR);

//計算路徑(返回文件目錄「../」層數)
$folderLEVEL = 2;//決定返回至幾層之前，通常目標為根目錄或檔案所在資料夾同層。
$folderPATH = "";
$pathSplit = @explode('/',PHP_SELF);
$currDirLEVEL = sizeof($pathSplit);

$folderPATH = str_repeat("../",$currDirLEVEL-$folderLEVEL-1);//取得返回根目錄需要的層數
$pagepath = '';
for ($i=$folderLEVEL;$i<$currDirLEVEL;$i++){
	$pagepath .= $pathSplit[$i]."/";
}
$pagepath = substr($pagepath, 0, -1);//取得根目錄起始的檔案路徑
define('DOC_PATH_LAYER', $folderPATH.'5sladmin/');



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
    if (isset($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'] || '1' == $_SERVER['HTTPS'])){$protoCOL = 'https://';}
    else{$protoCOL = 'http://';}
    return $protoCOL;
}
function _FUN_ADDSLASHES($value){
    if (!empty($value)){return is_array($value) ? array_map('_FUN_ADDSLASHES', $value) : addslashes($value);}
	else{return $value;}
}

if (!ini_get('magic_quotes_gpc')){
    if (!empty($_GET)){$_GET =_FUN_ADDSLASHES($_GET);}
    if (!empty($_POST)){$_POST=_FUN_ADDSLASHES($_POST);}
    $_COOKIE  =_FUN_ADDSLASHES($_COOKIE);
    $_REQUEST =_FUN_ADDSLASHES($_REQUEST);
}

function _fn_token_create($mb,$acct,$expiry) {
	$jsStr = $mb."|".$acct."|".$expiry;
	return $jsStr;
} 

?>
