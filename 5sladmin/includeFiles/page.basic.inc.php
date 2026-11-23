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
(function() {
    const cfg = <?php echo json_encode($topNavConfig, JSON_UNESCAPED_UNICODE); ?>;

    function syncParentTopNavbar() {
        if (!window.parent || !window.parent.document) return false;
        const pDoc = window.parent.document;

        const authPanel  = pDoc.querySelector('.nav-item.nav-authpanel');
        const topPanel   = pDoc.querySelector('.nav-item.nav-toppanel');
        const topSearch  = pDoc.querySelector('.nav-item.nav-topsearch');
        const topExport  = pDoc.querySelector('.nav-item.nav-topexport');
        const topBKredir = pDoc.querySelector('.nav-item.nav-topbkredir');

        if (!authPanel) return false;

        // 1. 全面重置所有面板與圖示的 active 狀態
        authPanel.querySelectorAll('i').forEach(el => el.classList.remove('active'));
        if (topPanel)   topPanel.querySelectorAll('i').forEach(el => { el.classList.remove('active'); el.style.display = 'none'; });
        if (topSearch)  topSearch.querySelectorAll('i').forEach(el => { el.classList.remove('active'); el.style.display = 'none'; });
        if (topExport)  topExport.querySelectorAll('i').forEach(el => { el.classList.remove('active'); el.style.display = 'none'; });
        if (topBKredir) topBKredir.querySelectorAll('i').forEach(el => { el.classList.remove('active'); el.style.display = 'none'; });

        // 2. 嚴格依照 cfg.auth 的 true / false 點亮或熄滅四大權限圖示
        authPanel.style.display = 'flex';
        const toggleAuthIcon = (id, allow) => {
            const icon = authPanel.querySelector('#' + id);
            if (!icon) return;
            if (allow === true) {
                icon.classList.add('active');
            } else {
                icon.classList.remove('active');
            }
        };
        toggleAuthIcon('topAuth_browser', cfg.auth.browse);
        toggleAuthIcon('topAuth_addnew',  cfg.auth.addnew);
        toggleAuthIcon('topAuth_edit',    cfg.auth.edit);
        toggleAuthIcon('topAuth_delete',  cfg.auth.delete);

        // 3. 操作按鈕框控制 (結合 PAGE_OPANEL 與實際授權防呆)
        const hasAnyAuth = cfg.auth.browse || cfg.auth.addnew || cfg.auth.edit || cfg.auth.delete;
        if (cfg.opanel > 0 && hasAnyAuth && topPanel) {
            topPanel.style.display = 'flex';
            const btnAdd   = topPanel.querySelector('#topBut_add');
            const btnSave  = topPanel.querySelector('#topBut_save');
            const btnUp    = topPanel.querySelector('#topBut_upload');
            const btnDel   = topPanel.querySelector('#topBut_delete');
            const btnReset = topPanel.querySelector('#topBut_reset');

            if (cfg.opanel === 1 && btnAdd) {
                btnAdd.style.display = 'inline-flex';
                cfg.auth.addnew ? btnAdd.classList.add('active') : btnAdd.classList.remove('active');
            } else if ((cfg.opanel === 2 || cfg.opanel === 5) && btnSave) {
                btnSave.style.display = 'inline-flex';
                btnSave.classList.remove('active');
            } else if (cfg.opanel === 3) {
                if (btnUp) {
                    btnUp.style.display = 'inline-flex';
                    (cfg.auth.addnew || cfg.auth.edit) ? btnUp.classList.add('active') : btnUp.classList.remove('active');
                }
                if (btnDel && cfg.auth.delete) {
                    btnDel.style.display = 'inline-flex';
                    btnDel.classList.add('active');
                }
            } else if (cfg.opanel === 4) {
                if (btnSave) {
                    btnSave.style.display = 'inline-flex';
                    btnSave.classList.remove('active');
                }
                if (btnDel && cfg.auth.delete) {
                    btnDel.style.display = 'inline-flex';
                    btnDel.classList.add('active');
                }
            }

            if (btnReset) {
                btnReset.style.display = 'inline-flex';
                btnReset.classList.add('active');
            }
        } else if (topPanel) {
            topPanel.style.display = 'none';
        }

        // 4. 搜尋按鈕框控制
        if (cfg.search > 0 && topSearch) {
            topSearch.style.display = 'flex';
            const searchIcon = topSearch.querySelector('#topBut_search');
            if (searchIcon) {
                searchIcon.style.display = 'inline-flex';
                searchIcon.classList.add('active');
            }
        } else if (topSearch) {
            topSearch.style.display = 'none';
        }

        // 5. 匯出按鈕框控制
        if (cfg.export > 0 && topExport) {
            topExport.style.display = 'flex';
            const btnExcel = topExport.querySelector('#topBut_excel');
            const btnPrint = topExport.querySelector('#topBut_print');
            if (btnExcel) btnExcel.style.display = (cfg.export === 1 || cfg.export === 3) ? 'inline-flex' : 'none';
            if (btnPrint) btnPrint.style.display = (cfg.export === 2 || cfg.export === 3) ? 'inline-flex' : 'none';
            if (btnExcel && (cfg.export === 1 || cfg.export === 3)) btnExcel.classList.add('active');
            if (btnPrint && (cfg.export === 2 || cfg.export === 3)) btnPrint.classList.add('active');
        } else if (topExport) {
            topExport.style.display = 'none';
        }

        // 6. 返回前頁按鈕框控制
        if (cfg.bkRedir > 0 && topBKredir) {
            topBKredir.style.display = 'flex';
            const btnBk = topBKredir.querySelector('#topBut_bkurl');
            if (btnBk) {
                btnBk.style.display = 'inline-flex';
                btnBk.classList.add('active');
            }
        } else if (topBKredir) {
            topBKredir.style.display = 'none';
        }

        return true;
    }

    // 立即嘗試執行，若父視窗尚未就緒則於 DOMContentLoaded 再次同步
    if (!syncParentTopNavbar()) {
        document.addEventListener('DOMContentLoaded', syncParentTopNavbar);
    }
})();
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
