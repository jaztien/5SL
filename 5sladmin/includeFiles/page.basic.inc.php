<?php
/**------------------------------------------------------------------------**/
/**-------------------------- 頁面基本功能檔案 -------------------------------**/
/**------------------- 此檔案必須放置於 PAGE_MARK 定義之後 --------------------**/
/**------------------------------------------------------------------------**/

// 1. 定義必要數值與面板預設值
define('currID', isset($_REQUEST['currID']) ? intval($_REQUEST['currID']) : 0);

if (!defined('PAGE_OPANEL'))   define('PAGE_OPANEL', currID > 0 ? 4 : 5);
if (!defined('DRAWER_OPANEL')) define('DRAWER_OPANEL', 0);
if (!defined('EXPORT_OPANEL')) define('EXPORT_OPANEL', 0);
if (!defined('bkRedir'))       define('bkRedir', 0);

// ----------------- 頁面操作權限處理 ---------------- //
$getPageAuth = $_SESSION['SESS_PAGE_AUTH'][PAGE_MARK] ?? [
    'browse' => 0,
    'addnew' => 0,
    'edit'   => 0,
    'delete' => 0
];

$adAuthLevel = intval($_SESSION['SESS_AD_AUTH'] ?? 0);
$adSessId    = intval($_SESSION['SESS_AD_ID'] ?? 0);

if (defined('PAGE_TOPAUTH') && $adAuthLevel >= 6) {
    $getPageAuth['browse'] = 1;
    $getPageAuth['addnew'] = 1;
    $getPageAuth['edit']   = 1;
    $getPageAuth['delete'] = ($adAuthLevel == 9) ? 1 : 0;
    $topEditAuth = true;
}

// 本人通關權限
if (defined('PAGE_ADPASS') && $adSessId === currID && empty($getPageAuth['edit'])) {
    $getPageAuth['browse'] = 1;
    $getPageAuth['edit']   = 1;
}

// 無瀏覽權限強制退回歡迎頁
if (empty($getPageAuth['browse'])) {
    echo "<script>alert('您沒有這個頁面的瀏覽授權，請停止操作或聯絡管理人員提高這個頁面的操作權限！！');window.location.replace('5sladmin_startpage.html');</script>";
    exit();
}

// ------------------ 最終的權限定義 -------------------- //
define('auBROWSE', intval($getPageAuth['browse'] ?? 0));
define('auADDNEW', intval($getPageAuth['addnew'] ?? 0));
define('auEDIT',   intval($getPageAuth['edit']   ?? 0));
define('auDELETE', intval($getPageAuth['delete'] ?? 0));

$canWrite = (currID === 0) ? (auADDNEW > 0) : (auEDIT > 0);
define('NreadOnly', $canWrite ? '' : ' readonly');
define('NdisAble',  $canWrite ? '' : ' disabled');

if (defined('FORMEDIT_TOPAUTH') && $adAuthLevel >= 6) {
    define('TreadOnly', '');
    define('TdisAble',  '');
} else {
    define('TreadOnly', ' readonly');
    define('TdisAble',  ' disabled');
}

// 打包頂部導覽列設定，於頁面底部透過父視窗 updateTopNavbar 統一控制
$topNavConfig = [
    'auth' => [
        'browse' => auBROWSE > 0,
        'addnew' => auADDNEW > 0,
        'edit'   => auEDIT > 0,
        'delete' => auDELETE > 0,
    ],
    'opanel'  => intval(PAGE_OPANEL),
    'drawer'  => intval(DRAWER_OPANEL),
    'export'  => intval(EXPORT_OPANEL),
    'bkRedir' => intval(bkRedir),
];

register_shutdown_function(function() use ($topNavConfig) {
    foreach (headers_list() as $h) {
        if (stripos($h, 'application/json') !== false) return;
    }
    $json = json_encode($topNavConfig, JSON_UNESCAPED_UNICODE);
    echo "<script>(function(cfg){const run=()=>{if(window.parent&&typeof window.parent.updateTopNavbar==='function')window.parent.updateTopNavbar(cfg);};document.readyState==='loading'?document.addEventListener('DOMContentLoaded',run):run();})({$json});</script>";
});
