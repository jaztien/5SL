/**
 * @Module       iframe-state-keeper.js 
 * @Description  專門解決 PHP + AdminLTE3 iFrame 架構下的跨頁返回、
 *               DataTables 頁碼記憶、AdminLTE3 Tab 鎖定及防 F5 重新整理重置功能。
 * @Version      1.0.0
 */


// iframe-state-keeper.js 內容：
$(function() {
    var pagePath = window.location.pathname;
    var pageSearch = window.location.search;
    var pageUniqueKey = pagePath + pageSearch;

    // =================================================================
    // 1. 自動記憶與自動還原 AdminLTE3 Tab 狀態 (精準綁定 ID 參數)
    // =================================================================
    $('.nav-tabs a, .nav-pills a').on('shown.bs.tab', function (e) {
        var tabId = $(e.target).attr('href');
        sessionStorage.setItem('Tab_State_' + pageUniqueKey, tabId);
    });
    
    setTimeout(function() {
        var activeTab = sessionStorage.getItem('Tab_State_' + pageUniqueKey);
        if (activeTab) {
            var $targetTab = $('.nav-tabs a[href="' + activeTab + '"], .nav-pills a[href="' + activeTab + '"]');
            if ($targetTab.length > 0) { $targetTab.tab('show'); }
        }
    }, 150);

    // =================================================================
    // 2. 向外框回報當前的「完整網址與參數」 (供 F5 重新整理原地復活使用)
    // =================================================================
    if (window.parent && typeof window.parent.updateCurrentIframeUrl === 'function') {
        window.parent.updateCurrentIframeUrl(window.location.href);
    }

    // =================================================================
    // 3. 💡【收服原生返回鍵】優化版安全氣囊
    // =================================================================
    if (pagePath.indexOf('startpage') === -1) {
        
        // 不管在哪一頁，載入時都在歷史紀錄裡插一根「虛擬歷史旗幟」，留下阻斷伏筆
        window.history.pushState({ page: 'iframe_lock' }, '', window.location.href);

        window.addEventListener('popstate', function(event) {
            
            // 💡 情況 A：如果使用者已經退回到「最上層列表頁 (list.php)」，點擊原生返回鍵
            if (pagePath.indexOf('list') !== -1) {
                console.log("【安全防護】已在最上層列表頁，原地阻斷原生返回一次，保留 DataTable 頁碼！");
                // 重新把旗幟插回去，原地凍結，維持在 list.php 的當前頁碼
                window.history.pushState({ page: 'iframe_lock' }, '', window.location.href);
                return; 
            }
            
            // 💡 情況 B：如果是在詳細頁或編輯頁按原生返回鍵
            console.log("【內頁攔截】詳細/編輯頁按下原生返回，觸發原生歷史回退...");
            if (window.parent && typeof window.parent.backIframe === 'function') {
                window.parent.backIframe(); // 導向外框的 history.back()
            } else {
                window.history.back();
            }
            
            // 補回旗幟
            window.history.pushState({ page: 'iframe_lock' }, '', window.location.href);
        }, false);
    }
});
