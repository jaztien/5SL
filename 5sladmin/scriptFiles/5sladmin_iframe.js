/**
 * 1. 全域原生 Bootstrap 5 Toast 顯示函式
 * @param {string} type  - 訊息類型: 'success' | 'error' (或 'danger') | 'warning' | 'info'
 * @param {string} msg   - 訊息內容
 * @param {string} title - 選填標題
 * @param {number} delay - 自動關閉毫秒數 (預設 2500ms，傳入 0 則不自動關閉)
 */
window.showBS5Toast = function(type, msg, title = '', delay = 2500) {
    const container = document.getElementById('bs5ToastContainer');
    if (!container || typeof bootstrap === 'undefined') return;

    const typeConfig = {
        success: { bg: 'text-bg-success', icon: 'bi-check-circle-fill',         closeBtn: 'btn-close-white' },
        error:   { bg: 'text-bg-danger',  icon: 'bi-x-circle-fill',             closeBtn: 'btn-close-white' },
        danger:  { bg: 'text-bg-danger',  icon: 'bi-x-circle-fill',             closeBtn: 'btn-close-white' },
        warning: { bg: 'text-bg-warning', icon: 'bi-exclamation-triangle-fill', closeBtn: '' },
        info:    { bg: 'text-bg-info',    icon: 'bi-info-circle-fill',          closeBtn: '' }
    };

    const cfg = typeConfig[type] || typeConfig.info;
    const toastId = 'bs5_toast_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
    const autoHideAttr = delay > 0 ? `data-bs-autohide="true" data-bs-delay="${delay}"` : `data-bs-autohide="false"`;

    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center ${cfg.bg} border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true" ${autoHideAttr}>
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2 py-2 px-3">
                    <i class="bi ${cfg.icon} fs-5 flex-shrink-0"></i>
                    <div class="small">
                        ${title ? `<strong class="d-block mb-1">${title}</strong>` : ''}
                        <span>${msg}</span>
                    </div>
                </div>
                <button type="button" class="btn-close ${cfg.closeBtn} me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', toastHtml);
    const toastEl = document.getElementById(toastId);
    const bsToast = new bootstrap.Toast(toastEl);
    bsToast.show();

    toastEl.addEventListener('hidden.bs.toast', function() {
        toastEl.remove();
    });
};

// 向下相容橋接器：接管 window.parent.toastr
window.toastr = {
    success: (msg, title = '') => window.showBS5Toast('success', msg, title),
    error:   (msg, title = '') => window.showBS5Toast('error',   msg, title),
    warning: (msg, title = '') => window.showBS5Toast('warning', msg, title),
    info:    (msg, title = '') => window.showBS5Toast('info',    msg, title)
};

// 2. 統一管理頂部導覽列權限燈號與操作按鈕狀態（供子頁面 page.basic.inc.php 呼叫）
window.updateTopNavbar = function(cfg) {
    if (!cfg) return false;
    const authPanel  = document.querySelector('.nav-item.nav-authpanel');
    const topPanel   = document.querySelector('.nav-item.nav-toppanel');
    const topDrawer  = document.querySelector('.nav-item.nav-topdrawer');
    const topExport  = document.querySelector('.nav-item.nav-topexport');
    const topBKredir = document.querySelector('.nav-item.nav-topbkredir');
    if (!authPanel) return false;

    // 重置四大功能面板
    [topPanel, topDrawer, topExport, topBKredir].forEach(function(panel) {
        if (!panel) return;
        panel.style.display = 'none';
        panel.querySelectorAll('i').forEach(function(el) {
            el.classList.remove('active', 'drawer-open', 'text-warning');
            el.style.display = 'none';
            el.style.opacity = '';
            el.style.cursor  = '';
        });
    });

    const toggleIcon = function(el, show, active, isOpen) {
        if (!el) return;
        el.style.display = show ? 'inline-flex' : 'none';
        el.classList.toggle('active', !!active);
        el.classList.toggle('drawer-open', !!isOpen);

        // 沒有抽屜的那一邊（show=true 但 active=false）顯示為較淡的灰色且不顯示手指游標
        if (show && !active) {
            el.style.opacity = '0.3';
            el.style.cursor  = 'default';
        } else {
            el.style.opacity = '';
            el.style.cursor  = '';
        }
    };

    // (1) 頁面權限燈號
    authPanel.style.display = 'flex';
    toggleIcon(authPanel.querySelector('#topAuth_browser'), true, cfg.auth.browse, false);
    toggleIcon(authPanel.querySelector('#topAuth_addnew'),  true, cfg.auth.addnew, false);
    toggleIcon(authPanel.querySelector('#topAuth_edit'),    true, cfg.auth.edit,   false);
    toggleIcon(authPanel.querySelector('#topAuth_delete'),  true, cfg.auth.delete, false);

    // (2) 操作按鈕框 (1-列表新增 / 2-單純儲存 / 3-上傳+刪除 / 4-編輯儲存+刪除 / 5-新增儲存)
    const hasAnyAuth = cfg.auth.browse || cfg.auth.addnew || cfg.auth.edit || cfg.auth.delete;
    if (cfg.opanel > 0 && hasAnyAuth && topPanel) {
        topPanel.style.display = 'flex';
        const btnAdd   = topPanel.querySelector('#topBut_add');
        const btnSave  = topPanel.querySelector('#topBut_save');
        const btnUp    = topPanel.querySelector('#topBut_upload');
        const btnDel   = topPanel.querySelector('#topBut_delete');
        const btnReset = topPanel.querySelector('#topBut_reset');

        if (cfg.opanel === 1) {
            toggleIcon(btnAdd, true, cfg.auth.addnew, false);
        } else if (cfg.opanel === 2 || cfg.opanel === 5) {
            // 保留 false 等待子頁面表單異動 (dirty) 後點亮
            toggleIcon(btnSave, true, false, false);
        } else if (cfg.opanel === 3) {
            toggleIcon(btnUp, true, cfg.auth.addnew || cfg.auth.edit, false);
            if (cfg.auth.delete) toggleIcon(btnDel, true, true, false);
        } else if (cfg.opanel === 4) {
            toggleIcon(btnSave, true, false, false);
            if (cfg.auth.delete) toggleIcon(btnDel, true, true, false);
        }
        toggleIcon(btnReset, true, true, false);
    }

    // (3) 雙向抽屜按鈕框（cfg.drawer > 0 時左右按鈕一起出現，自動偵測 #offcanvasLeft 與 #offcanvasSearch）
    if (cfg.drawer > 0 && topDrawer) {
        topDrawer.style.display = 'flex';
        const btnL = topDrawer.querySelector('#topBut_SideBarL');
        const btnR = topDrawer.querySelector('#topBut_SideBarR');

        const iframeEl = document.getElementById('iFrame_main');
        const iDoc     = (iframeEl && iframeEl.contentDocument) ? iframeEl.contentDocument : null;

        const leftDrawerEl  = iDoc ? iDoc.getElementById('offcanvasLeft')   : null;
        const rightDrawerEl = iDoc ? iDoc.getElementById('offcanvasSearch') : null;

        const hasLeft  = !!leftDrawerEl;
        const hasRight = !!rightDrawerEl;

        toggleIcon(btnL, true, hasLeft,  hasLeft  && leftDrawerEl.classList.contains('show'));
        toggleIcon(btnR, true, hasRight, hasRight && rightDrawerEl.classList.contains('show'));

        // 監聽子頁面 Offcanvas 拉開與關上事件，即時同步 drawer-open 狀態（與 Hover 樣式一致）
        const bindDrawerEvents = function(drawerEl, btnEl) {
            if (!drawerEl || !btnEl || drawerEl.dataset.navSynced === '1') return;
            drawerEl.dataset.navSynced = '1';
            drawerEl.addEventListener('show.bs.offcanvas', function() {
                btnEl.classList.add('drawer-open');
            });
            drawerEl.addEventListener('hidden.bs.offcanvas', function() {
                btnEl.classList.remove('drawer-open');
            });
        };

        bindDrawerEvents(leftDrawerEl,  btnL);
        bindDrawerEvents(rightDrawerEl, btnR);
    }

    // (4) 匯出與列印按鈕框 (1-Excel / 2-Print / 3-Excel+Print)
    if (cfg.export > 0 && topExport) {
        topExport.style.display = 'flex';
        toggleIcon(topExport.querySelector('#topBut_excel'), cfg.export === 1 || cfg.export === 3, true, false);
        toggleIcon(topExport.querySelector('#topBut_print'), cfg.export === 2 || cfg.export === 3, true, false);
    }

    // (5) 返回關聯前頁按鈕框
    if (cfg.bkRedir > 0 && topBKredir) {
        topBKredir.style.display = 'flex';
        toggleIcon(topBKredir.querySelector('#topBut_bkurl'), true, true, false);
    }

    return true;
};


// 3. 呼叫 IFrame 內頁的全域函式
function callFrameFunc(funcName) {
    const frameWin = document.getElementById('iFrame_main').contentWindow;
    if (frameWin && typeof frameWin[funcName] === 'function') {
        frameWin[funcName]();
    } else {
        toastr.warning('目前頁面不支援此操作功能');
    }
}

// 4. 供 IFrame 內頁載入完成後同步展開並高亮側邊欄選單
function treeviewCheck(itemCode, markCode, subItemCode) {
    const $sidebar = $('#sidebarL');
    if (!$sidebar.length) return;

    $sidebar.find('.nav-link').removeClass('active');
    $sidebar.find('.nav-item').removeClass('menu-open');
    $sidebar.find('.nav-treeview').hide();
    // 重置所有主分類加總 Badge 為可見
    $sidebar.find('> ul > li > a span.badge').css('visibility', 'visible');

    const $targetLi = markCode ? $sidebar.find('li[data-pagecode="' + markCode + '"]') : $();

    if ($targetLi.length) {
        $targetLi.children('a.nav-link').addClass('active');
        $targetLi.parents('li.nav-item').each(function() {
            $(this).addClass('menu-open');
            $(this).children('ul.nav-treeview').show();
            $(this).children('a.nav-link').addClass('active');
            // 主分類展開時隱藏加總 Badge，讓子分類各自的 Badge 接手顯示
            $(this).children('a.nav-link').find('span.badge').css('visibility', 'hidden');
        });
    } else if (itemCode) {
        const $itemLi = $sidebar.find('li[data-pagecode="' + itemCode + '"]');
        $itemLi.addClass('menu-open');
        $itemLi.children('ul.nav-treeview').show();
        $itemLi.children('a.nav-link').addClass('active');
        $itemLi.children('a.nav-link').find('span.badge').css('visibility', 'hidden');
    }
}


function treeviewReset() {
    const $sidebar = $('#sidebarL');
    $sidebar.find('.nav-link').removeClass('active');
    $sidebar.find('.nav-item').removeClass('menu-open');
    $sidebar.find('.nav-treeview').slideUp(200);
    $sidebar.find('> ul > li > a span.badge').css('visibility', 'visible');
}

// 5. 每當內頁發生網址切換，即時更新外框的 F5 記憶
window.updateCurrentIframeUrl = function(url) {
    sessionStorage.setItem('Current_Iframe_URL', url);
};

// 6. 手動精準清除函數
window.clearIframeCache = function() {
    var targetPrefixes = ['DT_State_', 'Tab_State_', 'Last_List_URL', 'Last_Detail_URL', 'Manual_DT_Page_'];
    Object.keys(sessionStorage).forEach(function(key) {
        var shouldRemove = targetPrefixes.some(function(prefix) { return key.indexOf(prefix) === 0; });
        if (shouldRemove) {
            sessionStorage.removeItem(key);
        }
    });
};

function backIframe() {
    var activeIframe = document.querySelector('.tab-pane.active iframe') ||
                       document.querySelector('iframe:not([style*="display: none"])') ||
                       document.getElementById('iFrame_main');

    if (activeIframe && activeIframe.contentWindow) {
        activeIframe.contentWindow.history.back();
    }
}

function getUrlHex() {
    try {
        const iframeElement = document.getElementById('iFrame_main');
        const iframeUrl = iframeElement.contentWindow.location.href;
        const url = new URL(iframeUrl);
        let fullPATH = url.pathname.split('/').pop() + url.search;
        return urlEncodeHex(fullPATH);
    } catch (error) {
        return "";
    }
}

function urlEncodeHex(text) {
    const encoder = new TextEncoder();
    const bytes = encoder.encode(text);
    return Array.from(bytes).map(b => b.toString(16).padStart(2, '0')).join('');
}

function urlDecodeHex(hexString) {
    if (!hexString) return "";
    const bytes = new Uint8Array(hexString.match(/.{1,2}/g).map(byte => parseInt(byte, 16)));
    const decoder = new TextDecoder();
    return decoder.decode(bytes);
}

// 更新未讀訊息數量徽章
function unreadUpdate(nuM) {
    var $badge = $('#badge_PG_MSG');
    if ($badge.length > 0) {
        var $badgeNum = (parseInt($badge.text()) || 0) - parseInt(nuM);
        $badge.text($badgeNum > 0 ? $badgeNum : '');
    }

    var $badge2 = $('#badge_ITEM_MSG');
    if ($badge2.length > 0) {
        var $badgeNum2 = (parseInt($badge2.text()) || 0) - parseInt(nuM);
        $badge2.text($badgeNum2 > 0 ? $badgeNum2 : '');
    }

    var $badgeT = $('#badge_NAVBAR_MESS');
    if ($badgeT.length > 0) {
        var $badgeNumT = (parseInt($badgeT.text()) || 0) - parseInt(nuM);
        $badgeT.text($badgeNumT > 0 ? $badgeNumT : '');
    }
    return true;
}

////////////////////////////////////////////////////////////////
// ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
////////////////////////////////////////////////////////////////
$(document).ready(function() {
    // 統一切換子頁面左右 Offcanvas 輔助函式
    function toggleChildDrawer(drawerId, fallbackFuncName) {
        const iframeEl = document.getElementById('iFrame_main');
        if (!iframeEl) return;
        const win  = iframeEl.contentWindow;
        const iDoc = iframeEl.contentDocument;

        if (win && typeof win[fallbackFuncName] === 'function') {
            win[fallbackFuncName]();
            return;
        }
        if (iDoc && win && win.bootstrap) {
            const el = iDoc.getElementById(drawerId);
            if (el) win.bootstrap.Offcanvas.getOrCreateInstance(el).toggle();
        }
    }

    /////////////// Top Navigator 按鈕群操作 //////////////////////
    $('#topBut_upload').click(function(e) {
        e.preventDefault();
        if (!$(this).hasClass('active')) return false;
        const win = $("#iFrame_main")[0].contentWindow;
        if (win && typeof win.doButUpload === 'function') win.doButUpload();
    });

    $('#topBut_add').click(function(e) {
        e.preventDefault();
        if (!$(this).hasClass('active')) return false;
        if (confirm('當按下按鍵繼續進行操作時，即代表同意遵循「人生第五季管理辦法」進行操作！\r\r確定進行資料新增操作？！')) {
            const win = $("#iFrame_main")[0].contentWindow;
            if (win && typeof win.doButAdd === 'function') win.doButAdd();
        }
    });

    $('#topBut_save').click(function(e) {
        e.preventDefault();
        if (!$(this).hasClass('active')) return false;
        if (confirm('當按下按鍵繼續進行操作時，即代表同意遵循「人生第五季管理辦法」進行操作！\r\r確定進行資料儲存操作？！')) {
            const win = $("#iFrame_main")[0].contentWindow;
            if (win && typeof win.doButSave === 'function') win.doButSave();
        }
    });

    $('#topBut_reset').click(function(e) {
        e.preventDefault();
        if (!$(this).hasClass('active')) return false;
        const win = $("#iFrame_main")[0].contentWindow;
        if (win && typeof win.doButReset === 'function') win.doButReset();
    });

    $('#topBut_delete').click(function(e) {
        e.preventDefault();
        if (!$(this).hasClass('active')) return false;
        const win = $("#iFrame_main")[0].contentWindow;
        if (win && typeof win.doButDelete === 'function') win.doButDelete();
    });

    $('#topBut_bkurl').click(function(e) {
        e.preventDefault();
        if (!$(this).hasClass('active')) return false;
        backIframe();
    });

    $('#topBut_SideBarL').click(function(e) {
        e.preventDefault();
        if (!$(this).hasClass('active')) return false;
        toggleChildDrawer('offcanvasLeft', 'doButSideBarL');
    });

    $('#topBut_SideBarR').click(function(e) {
        e.preventDefault();
        if (!$(this).hasClass('active')) return false;
        toggleChildDrawer('offcanvasSearch', 'doButSerach');
    });

    $('#topBut_excel').click(function(e) {
        e.preventDefault();
        if (!$(this).hasClass('active')) return false;
        const win = $("#iFrame_main")[0].contentWindow;
        if (win && typeof win.doButExcel === 'function') win.doButExcel();
    });

    $('#topBut_print').click(function(e) {
        e.preventDefault();
        if (!$(this).hasClass('active')) return false;
        const win = $("#iFrame_main")[0].contentWindow;
        if (win && typeof win.doButPrint === 'function') win.doButPrint();
    });

    // 鎖定與登出
    $('#lockscreen').click(function(e) {
        window.location.assign("../lockscreen.php?backURI=" + getUrlHex());
    });
    $('#logout').click(function(e) {
        if (!confirm('確定登出系統？！')) return false;
        window.location.replace("../5sladmin.php");
    });

    // 點擊側邊欄連結時即時切換 Active 樣式
    $('.sidebar-menu .nav-treeview .nav-link').on('click', function() {
        $('.sidebar-menu .nav-link').removeClass('active');
        $(this).addClass('active');
        $(this).closest('.nav-item').parent().closest('.nav-item').children('.nav-link').addClass('active');
    });

    // 左側樹狀目錄首層數值標註顯示控制
    $('#sidebarL > ul > li > a').click(function() {
        var parentLI = $(this).parent();
        var bedgeSPAN = parentLI.find('> a span.badge');
        var allLIspan = parentLI.parent().find('> li > a span.badge');
        if (parentLI.hasClass('menu-open')) {
            bedgeSPAN.css("visibility", "visible");
        } else {
            allLIspan.css("visibility", "visible");
            bedgeSPAN.css("visibility", "hidden");
        }
    });

    // 安全滯留倒數計時器（側邊欄收合時自動裁去小時只顯示 mm:ss）
    let countdownSec = (typeof window.SYS_IDLEOUT !== 'undefined') ? parseInt(window.SYS_IDLEOUT) : 600;
    const timerNode = document.getElementById('timer_on_exhausted');
    if (timerNode && typeof $(timerNode).backward_timer === 'function') {
        let fullTimeStr = '';
        const applyTimerDisplay = () => {
            if (!fullTimeStr) return;
            const isMini = $('body').hasClass('sidebar-collapse') && !$('body').hasClass('sidebar-open') && !$('.app-sidebar').is(':hover');
            const targetText = isMini ? fullTimeStr.replace(/^\d{2}:/, '') : fullTimeStr;
            if (timerNode.textContent !== targetText) {
                timerNode.textContent = targetText;
            }
        };

        // 監聽計時器每秒寫入的新時間，收合狀態下自動只留 mm:ss
        new MutationObserver(() => {
            const cur = timerNode.textContent.trim();
            if (/^\d{2}:\d{2}:\d{2}$/.test(cur)) {
                fullTimeStr = cur;
                applyTimerDisplay();
            }
        }).observe(timerNode, { childList: true, characterData: true, subtree: true });

        // 側邊欄展開/收合或滑鼠移入移出時即時切換格式
        new MutationObserver(applyTimerDisplay).observe(document.body, { attributes: true, attributeFilter: ['class'] });
        $('.app-sidebar').on('mouseenter mouseleave', () => setTimeout(applyTimerDisplay, 30));

        $(timerNode).backward_timer({
            seconds: countdownSec,
            format: 'h%:m%:s%',
            on_exhausted: function(timer) {
                window.location.assign("../lockscreen.php?backURI=" + getUrlHex());
            }
        });
        $(timerNode).backward_timer('start');
    }

    $('body').on('keypress click', function() {
        if ($('#timer_on_exhausted').length && typeof $('#timer_on_exhausted').backward_timer === 'function') {
            $('#timer_on_exhausted').backward_timer('reset');
        }
    });

    // F5 重新整理原地復活機制
    var iframe = document.getElementById('iFrame_main');
    if (iframe) {
        var lastIframeUrl = sessionStorage.getItem('Current_Iframe_URL');
        if (lastIframeUrl) {
            iframe.setAttribute('src', lastIframeUrl);
        } else {
            iframe.setAttribute('src', '5sladmin_startpage.html');
        }
    }

    // 側邊欄點擊事件：清除 IFrame 記憶與列表快取
    $('.app-sidebar, .sidebar-menu').on('click', 'a[href!="#"]:not([data-lte-toggle="sidebar"])', function() {
        sessionStorage.removeItem('Current_Iframe_URL');
        if (typeof window.clearIframeCache === 'function') {
            window.clearIframeCache();
        }
    });
});
