<?php
/**------------------------------------------------------------------------**/
/**-------------------------- 頁面基本功能檔案 -------------------------------**/
/**------------------- 此檔案必須放置於 PAGE_MARK 定義之後 --------------------**/
/**------------------------------------------------------------------------**/

// 定義必要數值
define('currID', isset($_REQUEST['currID']) ? intval($_REQUEST['currID']) : 0);

if (!defined('PAGE_OPANEL')) {
    define('PAGE_OPANEL', currID > 0 ? 4 : 5);
}
if (!defined('SEARCH_OPANEL')) {
    define('SEARCH_OPANEL', 0);
}
if (!defined('EXPORT_OPANEL')) {
    define('EXPORT_OPANEL', 0);
}
if (!defined('bkRedir')) {
    define('bkRedir', 0);
}

// ----------------- 頁面操作權限處理 ---------------- //
// 取得登入時的授權（依當下頁面的授權條件，加上 ?? [] 防止 PHP 8+ 陣列鍵值警告）
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

// 當在管理者本人的資料頁面時，若有「本人通關」註記時，重新賦予「瀏覽」以及「編輯」權限
if (defined('PAGE_ADPASS') && $adSessId === currID && empty($getPageAuth['edit'])) {
    $getPageAuth['browse'] = 1;
    $getPageAuth['edit']   = 1;
}

// 若無瀏覽權限時，系統強制退回歡迎頁
if (empty($getPageAuth['browse'])) {
    echo "<script>";
    echo "alert('您沒有這個頁面的瀏覽授權，請停止操作或聯絡管理人員提高這個頁面的操作權限！！');";
    echo "window.location.replace('5sladmin_startpage.html');";
    echo "</script>";
    exit();
}

// ------------------ 最終的權限定義 -------------------- //
define('auBROWSE', intval($getPageAuth['browse'] ?? 0));
define('auADDNEW', intval($getPageAuth['addnew'] ?? 0));
define('auEDIT',   intval($getPageAuth['edit']   ?? 0));
define('auDELETE', intval($getPageAuth['delete'] ?? 0));

// 將權限與面板參數打包為 JSON，交由前端安全執行（避免在 <head> 之前就呼叫尚未載入的 jQuery）
$topNavConfig = [
    'auth' => [
        'browse' => auBROWSE > 0,
        'addnew' => auADDNEW > 0,
        'edit'   => auEDIT > 0,
        'delete' => auDELETE > 0,
    ],
    'opanel' => intval(PAGE_OPANEL),
    'search' => intval(SEARCH_OPANEL),
    'export' => intval(EXPORT_OPANEL),
    'bkRedir'=> intval(bkRedir),
];
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 確保在有父視窗且父視窗已載入 jQuery 的環境下執行
    if (!window.parent || !window.parent.$) return;

    const $p = window.parent.$;
    const cfg = <?php echo json_encode($topNavConfig, JSON_UNESCAPED_UNICODE); ?>;

    const $authPanel   = $p('.nav-item.nav-authpanel');
    const $topPanel    = $p('.nav-item.nav-toppanel');
    const $topSearch   = $p('.nav-item.nav-topsearch');
    const $topExport   = $p('.nav-item.nav-topexport');
    const $topBKredir  = $p('.nav-item.nav-topbkredir');

    // 1. 全面重置所有面板與圖示狀態
    $authPanel.find('i').removeClass('active');
    $topPanel.find('i').removeClass('active').hide();
    $topSearch.find('i').removeClass('active').hide();
    $topExport.find('i').removeClass('active').hide();
    $topBKredir.find('i').removeClass('active').hide();

    // 2. 顯示並點亮四大權限圖示 (Bootstrap 5 Flex 建議使用 d-flex 顯示，若 CSS 原本為 inline-block 則用 show())
    if ($authPanel.length > 0) {
        $authPanel.css('display', 'flex');
        if (cfg.auth.browse) $authPanel.find('i#topAuth_browser').addClass('active');
        if (cfg.auth.addnew) $authPanel.find('i#topAuth_addnew').addClass('active');
        if (cfg.auth.edit)   $authPanel.find('i#topAuth_edit').addClass('active');
        if (cfg.auth.delete) $authPanel.find('i#topAuth_delete').addClass('active');
    }

    // 3. 操作按鈕框控制 (結合 PAGE_OPANEL 與實際授權防呆)
    const hasAnyAuth = cfg.auth.browse || cfg.auth.addnew || cfg.auth.edit || cfg.auth.delete;
    if (cfg.opanel > 0 && hasAnyAuth && $topPanel.length > 0) {
        $topPanel.css('display', 'flex');

        if (cfg.opanel === 1) {
            // 列表頁：新增按鈕 (有新增權限才亮起 active)
            const $addBtn = $topPanel.find('i#topBut_add').show();
            cfg.auth.addnew ? $addBtn.addClass('active') : $addBtn.removeClass('active');
        } else if (cfg.opanel === 2 || cfg.opanel === 5) {
            // 編輯/新增頁：儲存按鈕 (預設灰色，等表單異動後才由 JS 加上 active)
            $topPanel.find('i#topBut_save').show().removeClass('active');
        } else if (cfg.opanel === 3) {
            // 上傳頁：上傳與刪除按鈕
            const $upBtn = $topPanel.find('i#topBut_upload').show();
            (cfg.auth.addnew || cfg.auth.edit) ? $upBtn.addClass('active') : $upBtn.removeClass('active');
            
            if (cfg.auth.delete) {
                $topPanel.find('i#topBut_delete').show().addClass('active');
            }
        } else if (cfg.opanel === 4) {
            // 詳細資料編輯頁：儲存與刪除按鈕
            $topPanel.find('i#topBut_save').show().removeClass('active');
            if (cfg.auth.delete) {
                $topPanel.find('i#topBut_delete').show().addClass('active');
            }
        }

        // 無論 PAGE_OPANEL 為何，皆顯示重置按鈕
        $topPanel.find('i#topBut_reset').show().addClass('active');
    } else {
        $topPanel.hide();
    }

    // 4. 搜尋按鈕框控制
    if (cfg.search > 0 && $topSearch.length > 0) {
        $topSearch.css('display', 'flex');
        const $searchIcon = $topSearch.find('i#topBut_search');
        if (cfg.search === 1) {
            $searchIcon.find('span').hide();
        } else {
            $searchIcon.find('span').show();
        }
        $searchIcon.show().addClass('active');
    } else {
        $topSearch.hide();
    }

    // 5. 匯出按鈕框控制
    if (cfg.export > 0 && $topExport.length > 0) {
        $topExport.css('display', 'flex');
        if (cfg.export === 1) {
            $topExport.find('i#topBut_excel').show().addClass('active');
            $topExport.find('i#topBut_print').hide().removeClass('active');
        } else if (cfg.export === 2) {
            $topExport.find('i#topBut_print').show().addClass('active');
            $topExport.find('i#topBut_excel').hide().removeClass('active');
        } else {
            $topExport.find('i#topBut_excel').show().addClass('active');
            $topExport.find('i#topBut_print').show().addClass('active');
        }
    } else {
        $topExport.hide();
    }

    // 6. 返回前頁按鈕框控制
    if (cfg.bkRedir > 0 && $topBKredir.length > 0) {
        $topBKredir.css('display', 'flex');
        $topBKredir.find('i#topBut_bkurl').show().addClass('active');
    } else {
        $topBKredir.hide();
    }
});
</script>
<?php
///////////////////////////////////////////////////////////////////////////
// 頁面讀寫權限佈局 // auBROWSE,auADDNEW,auEDIT,auDELETE
// 依據四大權限結構，設定表單中元件的讀寫權限
define('NreadOnly', ((auADDNEW == 0 && currID == 0) || (auEDIT == 0 && currID > 0)) ? " readonly" : ""); // 一般管理權
define('NdisAble',  ((auADDNEW == 0 && currID == 0) || (auEDIT == 0 && currID > 0)) ? " disabled" : ""); // 一般管理權

// 若是當下頁面有最高授權註記時，忽略系統設定的權限，重新賦予新的且完全的權限
if (defined('FORMEDIT_TOPAUTH') && $adAuthLevel >= 6) {
    define('TreadOnly', ""); // 最高管理權
    define('TdisAble',  ""); // 最高管理權
} else {
    define('TreadOnly', " readonly"); // 最高管理權
    define('TdisAble',  " disabled"); // 最高管理權
}
?>
