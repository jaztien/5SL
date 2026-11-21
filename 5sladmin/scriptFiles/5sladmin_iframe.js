// 1. 每當內頁發生網址切換，即時更新外框的 F5 記憶
window.updateCurrentIframeUrl = function(url) {
    sessionStorage.setItem('Current_Iframe_URL', url);
};

// 2. 手動精準清除函數 (特定前綴清除法)
window.clearIframeCache = function() {
    console.log("【外框】執行精準清除快取...");
    var targetPrefixes = ['DT_State_', 'Tab_State_', 'Last_List_URL', 'Last_Detail_URL', 'Manual_DT_Page_'];
    
    Object.keys(sessionStorage).forEach(function(key) {
        var shouldRemove = targetPrefixes.some(function(prefix) { return key.indexOf(prefix) === 0; });
        if (shouldRemove) { 
            sessionStorage.removeItem(key); 
        }
    });
};

function backIframe() {
    console.log("【外框守門員】收到返回請求，觸發原生歷史回退...");
    var activeIframe = document.querySelector('.tab-pane.active iframe') || 
                       document.querySelector('iframe:not([style*="display: none"])') ||
                       document.getElementById('iFrame_main');

    if (activeIframe && activeIframe.contentWindow) {
        activeIframe.contentWindow.history.back();
    }
}

// 直接取得 iFrame 中網址字串（URI）
function getUrlHex() {
    try {
        const iframeElement = document.getElementById('iFrame_main');
        const iframeUrl = iframeElement.contentWindow.location.href;
        const url = new URL(iframeUrl);
        let fullPATH = url.pathname.split('/').pop() + url.search;
        return urlEncodeHex(fullPATH);
    } catch (error) {
        console.error("無效的網址字串");
        return "";
    }
}

/** *************** JavaScript 十六進位 (Hex) 字串加解密 ************************* **/
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

// 更新未讀訊息
function unreadUpdate(nuM) {
    // 更新左側導航列未讀標註
    var $badge = $('#badge_PG_MESSBOARD');
    if ($badge.length == 0) { return false; }
    var $badgeNum = (parseInt($badge.text()) || 0) - parseInt(nuM);
    $badge.text($badgeNum > 0 ? $badgeNum : '');

    // 更新左側樹狀導航列中的未讀標註
    var $badge2 = $('#badge_ITEM_MESS');
    if ($badge2.length > 0) {
        var $badgeNum2 = (parseInt($badge2.text()) || 0) - parseInt(nuM);
        $badge2.text($badgeNum2 > 0 ? $badgeNum2 : '');
    }

    // 更新上方導航列中的未讀標註
    var $badgeT = $('#badge_NAVBAR_MESS');
    if ($badgeT.length > 0) {
        var $badgeNumT = (parseInt($badgeT.text()) || 0) - parseInt(nuM);
        $badgeT.text($badgeNumT > 0 ? $badgeNumT : '');
    }
    return true;
}

//------- 取得簡易訊息(上方導航列使用) -------
function getEasyMess(nuM = 50, daTa = '') {
    if (daTa) {
        var messShort = daTa.chat_message.length > 20 ? daTa.chat_message.substring(0, 20) + '...' : daTa.chat_message;
        // [修正1] 改用原生 JS 取得目前秒數，避免獨立 .js 檔無法解析 PHP 標籤
        var nowSec = Math.floor(Date.now() / 1000);
        var postPast = Math.floor((nowSec - parseInt(daTa.chat_published)) / 3600);
        if (postPast > 24) {
            postPast = Math.floor(postPast / 24) + ' 天前';
        } else if (postPast <= 0) {
            postPast = '小於1小時';
        } else {
            postPast = postPast + ' 小時前';
        }

        // [修正2] 使用 onerror 處理預設頭像，取代阻塞畫面的同步 HEAD 請求
        var chatAvatar = `../uploadFiles/mb/mbphoto_${daTa.chat_postid}_thumb.jpg`;
        // [修正3] 全面改用 Bootstrap 5 Flex 佈局 (d-flex, rounded-circle, ms-3) 與 Bootstrap Icons (bi-clock)
        var newUnread = `
            <a href="mess_board.php?chatType=1&joinID=${daTa.chat_joinid}" target="iFrame_main" class="dropdown-item ezmess py-2" data-chatid="${daTa.chat_id}" data-joinid="${daTa.chat_joinid}">
              <div class="d-flex align-items-center">
                <img src="${chatAvatar}" onerror="this.onerror=null;this.src='../imageFiles/nopic_user_64x64.jpg';" alt="User Avatar" class="rounded-circle flex-shrink-0" width="45" height="45">
                <div class="flex-grow-1 ms-3 overflow-hidden">
                  <h6 class="dropdown-item-title mb-1 fw-bold text-truncate">${daTa.chat_poster}</h6>
                  <p class="small mb-1 text-truncate">${messShort}</p>
                  <p class="small text-muted mb-0" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i>${postPast}</p>
                </div>
              </div>
            </a>
            <div class="dropdown-divider my-0"></div>
        `;
        $('#messDropdown').prepend(newUnread);
        return true;
    } else {
        $.ajax({
            type: "POST",
            url: "../funcFiles/mb_chat_func.php",
            data: {
                'nuM': nuM, 
                'formAcce': 'func_chat_ezmess',
                'token': getTokenVal()
            },
            dataType: 'json',
            success: function (data) {
                if (data['RTN'] == 1 && data.messRes && data.messRes.length > 0) {
                    var d = data.messRes;
                    var content = '';
                    for (var i = 0; i < d.length; i++) {
                        content += `
                            <a href="mess_board.php?chatType=1&joinID=${d[i].chat_joinid}" target="iFrame_main" class="dropdown-item ezmess py-2" data-chatid="${d[i].chat_id}" data-joinid="${d[i].chat_joinid}">
                              <div class="d-flex align-items-center">
                                <img src="${d[i].chatAvatar}" onerror="this.onerror=null;this.src='../imageFiles/nopic_user_64x64.jpg';" alt="User Avatar" class="rounded-circle flex-shrink-0" width="45" height="45">
                                <div class="flex-grow-1 ms-3 overflow-hidden">
                                  <h6 class="dropdown-item-title mb-1 fw-bold text-truncate">${d[i].chat_poster}</h6>
                                  <p class="small mb-1 text-truncate">${d[i].message}</p>
                                  <p class="small text-muted mb-0" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i>${d[i].postPast}</p>
                                </div>
                              </div>
                            </a>
                            <div class="dropdown-divider my-0"></div>
                        `;
                    }
                    $('#messDropdown').prepend(content);
                }
            },
            error: function (data) {
                console.log(data);
                if (typeof toastr !== 'undefined') toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
            }
        });
    }
}

//------- 取得簡易通知(上方導航列使用) -------
function getEasyNotify(nuM = 50, daTa = '') {
    if (daTa) {
        var infoShort = daTa.noti_content.length > 20 ? daTa.noti_content.substring(0, 20) + '...' : daTa.noti_content;
        var nowSec = Math.floor(Date.now() / 1000);
        var postPast = Math.floor((nowSec - parseInt(daTa.noti_published)) / 3600);
        if (postPast > 24) {
            postPast = Math.floor(postPast / 24) + ' 天前';
        } else if (postPast <= 0) {
            postPast = '小於1小時';
        } else {
            postPast = postPast + ' 小時前';
        }
        var colorClass = daTa.cAlert !== '' ? ' color' + daTa.cAlert : '';
        // [修正4] 圖示改用 bi-envelope-fill，間距改用 me-2
        var newInfo = `
            <a href="mess_notidetail.php?currID=${daTa.noti_id}" target="iFrame_main" class="dropdown-item eznotify py-2 position-relative" data-ifid="${daTa.noti_id}">
              <div class="text-truncate pe-5"><i class="bi bi-envelope-fill me-2${colorClass}"></i>${infoShort}</div>
              <span class="text-muted small position-absolute end-0 bottom-0 me-2 mb-1" style="font-size: 0.75rem;">${postPast}</span>
            </a>
            <div class="dropdown-divider my-0"></div>
        `;
        $('#notifyDropdown').prepend(newInfo);
    } else {
        $.ajax({
            type: "POST",
            url: "../funcFiles/mb_chat_func.php",
            data: {
                'nuM': nuM, 
                'formAcce': 'func_chat_eznotify',
                'token': getTokenVal()
            },
            dataType: 'json',
            success: function (data) {
                if (data.RTN == 1 && data.notifyRes && data.notifyRes.length > 0) {
                    var d = data.notifyRes;
                    var infocontent = '';
                    for (var i = 0; i < d.length; i++) {
                        var colorClass = d[i].cAlert !== '' ? ' color' + d[i].cAlert : '';
                        infocontent += `
                            <a href="mess_notidetail.php?currID=${d[i].noti_id}" target="iFrame_main" class="dropdown-item eznotify py-2 position-relative" data-ifid="${d[i].noti_id}">
                              <div class="text-truncate pe-5"><i class="bi bi-envelope-fill me-2${colorClass}"></i>${d[i].content}</div>
                              <span class="text-muted small position-absolute end-0 bottom-0 me-2 mb-1" style="font-size: 0.75rem;">${d[i].postPast}</span>
                            </a>
                            <div class="dropdown-divider my-0"></div>
                        `;
                    }
                    $('#badge_notify').html(data.totalNum);
                    $('#notifyDropdown').prepend(infocontent);
                }
            },
            error: function (data) {
                console.log(data);
                if (typeof toastr !== 'undefined') toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
            }
        });
    }
}

////////////////////////////////////////////////////////////////
// ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
// ---------------------------------------------------------- //
////////////////////////////////////////////////////////////////
$(document).ready(function() {
    /////////////// Top Navigator 按鈕群操作 //////////////////////
    $('#topBut_upload').click(function(e){ 
        e.preventDefault();
        if (!$(this).hasClass('active')) return false;
        const win = $("#iFrame_main")[0].contentWindow;
        if (win && typeof win.doButUpload === 'function') win.doButUpload();
    });

    $('#topBut_add').click(function(e){ 
        e.preventDefault();
        if (!$(this).hasClass('active')) return false;
        if (confirm('當按下按鍵繼續進行操作時，即代表同意遵循「人生第五季管理辦法」進行操作！\r\r確定進行資料新增操作？！')){
            const win = $("#iFrame_main")[0].contentWindow;
            if (win && typeof win.doButAdd === 'function') win.doButAdd();
        }
    });

    $('#topBut_save').click(function(e){ 
        e.preventDefault();
        if (!$(this).hasClass('active')) return false;
        if (confirm('當按下按鍵繼續進行操作時，即代表同意遵循「人生第五季管理辦法」進行操作！\r\r確定進行資料儲存操作？！')){
            const win = $("#iFrame_main")[0].contentWindow;
            if (win && typeof win.doButSave === 'function') win.doButSave();
        }
    });

    $('#topBut_reset').click(function(e){ 
        e.preventDefault();
        if (!$(this).hasClass('active')) return false;
        const win = $("#iFrame_main")[0].contentWindow;
        if (win && typeof win.doButReset === 'function') win.doButReset();
    });

    $('#topBut_delete').click(function(e){ 
        e.preventDefault();
        if (!$(this).hasClass('active')) return false;
        const win = $("#iFrame_main")[0].contentWindow;
        if (win && typeof win.doButDelete === 'function') win.doButDelete();
    });

    $('#topBut_bkurl').click(function(e){
        e.preventDefault();
        backIframe();
    });

    $('#topBut_search').click(function(e){
        e.preventDefault();
        const win = $("#iFrame_main")[0].contentWindow;
        if (win && typeof win.doButSerach === 'function') win.doButSerach();
    });

    $('#topBut_excel').click(function(e){
        e.preventDefault();
        const win = $("#iFrame_main")[0].contentWindow;
        if (win && typeof win.doButExcel === 'function') win.doButExcel();
    });

    $('#topBut_print').click(function(e){
        e.preventDefault();
        const win = $("#iFrame_main")[0].contentWindow;
        if (win && typeof win.doButPrint === 'function') win.doButPrint();
    });

    // 鎖定與登出
    $('#lockscreen').click(function(e){
        window.location.assign("../lockscreen.php?backURI=" + getUrlHex());
    }); 
    $('#logout').click(function(e){
        if (!confirm('確定登出系統？！')) return false;
        window.location.replace("../5sladmin.php");
    });

    /* NOTE: 左側樹狀目錄首層數值標註顯示控制 */
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
    
    // 安全滯留倒數計時器 (若需從 PHP 動態傳入秒數，建議在 PHP 頁面定義 window.SYS_IDLEOUT)
    let countdownSec = (typeof window.SYS_IDLEOUT !== 'undefined') ? parseInt(window.SYS_IDLEOUT) : 600;
    if ($('#timer_on_exhausted').length && typeof $('#timer_on_exhausted').backward_timer === 'function') {
        $('#timer_on_exhausted').backward_timer({
            seconds: countdownSec,
            format: 'h%:m%:s%',
            on_exhausted: function(timer) {
                window.location.assign("../lockscreen.php?backURI=" + getUrlHex());
            }
        });
        $('#timer_on_exhausted').backward_timer('start');
    }

    $('body').on('keypress click', function(){
        if ($('#timer_on_exhausted').length && typeof $('#timer_on_exhausted').backward_timer === 'function') {
            $('#timer_on_exhausted').backward_timer('reset');
        }
    });

    ///////////////////////////////////////////////////////
    //--------------- 網頁歷史紀錄操作區域函數 --------------//
    ///////////////////////////////////////////////////////
    var iframe = document.getElementById('iFrame_main');
    if (iframe) {
        var lastIframeUrl = sessionStorage.getItem('Current_Iframe_URL');
        // 💡 F5 重新整理原地復活機制
        if (lastIframeUrl) {
            iframe.setAttribute('src', lastIframeUrl);
        } else {
            iframe.setAttribute('src', '5sladmin_startpage.html'); 
        }
    }

    // 側邊欄點擊事件：更新為 AdminLTE 4 的 .app-sidebar 與 .sidebar-menu，並排除資料夾節點 (href="#")
    $('.app-sidebar, .sidebar-menu').on('click', 'a[href!="#"]:not([data-lte-toggle="sidebar"])', function() {
        sessionStorage.removeItem('Current_Iframe_URL');
        if (typeof window.clearIframeCache === 'function') {
            window.clearIframeCache();
        }
    });    
});
