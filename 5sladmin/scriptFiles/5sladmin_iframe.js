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
        // 直接、全權移交給瀏覽器最乾淨的歷史回退，絕對不再轉址製造新歷史節點
        activeIframe.contentWindow.history.back();
    }
}
//////////////////////////////////////////////////////////////////////////

  //直接取得iFrame中網址字串（URI）
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
  /** *************** 可使用URL傳遞的字串 ******************** **/
  //加密：字串轉換成純英文與數字
  function urlEncodeHex(text) {
    const encoder = new TextEncoder();
    const bytes = encoder.encode(text);
    return Array.from(bytes).map(b => b.toString(16).padStart(2, '0')).join('');
  }
  //解密：純英文與數字 還原成原字串
  function urlDecodeHex(hexString) {
      const bytes = new Uint8Array(hexString.match(/.{1,2}/g).map(byte => parseInt(byte, 16)));
      const decoder = new TextDecoder();
      return decoder.decode(bytes);
  }
  //更新未讀訊息
  function unreadUpdate(nuM) {
    //更新左側導航列未讀標註
    var $badge = $('#badge_PG_MESSBOARD');
    if($badge.length == 0){ return false; }
    var $badgeNum = parseInt($badge.text()) - parseInt(nuM);//扣除已讀的訊息數量
    if($badgeNum > 0){//更新顯示的未讀標註
      $badge.text($badgeNum);
    } else {
      $badge.text('');
    }
    //更新左側樹狀導航列中的未讀標註
    var $badge2 = $('#badge_ITEM_MESS');
    var $badgeNum2 = parseInt($badge2.text()) - parseInt(nuM);
    if($badgeNum2 > 0){
      $badge2.text($badgeNum2);
    } else {
      $badge2.text('');
    }
    //更新上方導航列中的未讀標註
    var $badgeT = $('#badge_NAVBAR_MESS');
    var $badgeNumT = parseInt($badgeT.text()) - parseInt(nuM);
    if($badgeNumT > 0){
      $badgeT.text($badgeNumT);
    } else {
      $badgeT.text('');
    }
    return true;
  }
  //------- 取得簡易訊息(上方導航列使用) -------
  //------- 預設取得最新50筆，顯示10筆，當顯示的紀錄被點閱後自動刪除 -------
  //------- 由後續被隱藏的紀錄遞補顯示，直到紀錄低於10筆時重新獲取50筆 -------
  function getEasyMess(nuM=50,daTa='') {
    if(daTa){
      var messShort = daTa.chat_message.length > 20 ? daTa.chat_message.substring(0, 20) + '...' : daTa.chat_message;//將最新訊息縮短顯示
      //var postPast = Math.floor((parseInt('<?php echo time(); ?>') - daTa.chat_published) / 3600);
      //JazFIX: 要找尋時間數據的傳遞軌跡，將時間格式徹底更改
      var postPast = Math.floor((parseInt('<?php echo time(); ?>') - daTa.chat_published) / 3600);
            if (postPast > 24) {
                postPast = Math.floor(postPast / 24) + ' 天前';
            } else {
              if(postPast<=0){
                    postPast = '小於1小時';
                } else {
                    postPast = postPast + ' 小時前';
                }
            }
      var chatAvatar = checkIfFileExist(`../uploadFiles/mb/mbphoto_${daTa.chat_postid}_thumb.jpg`);
      var newUnread = `
          <a href="mess_board.php?chatType=1&joinID=${daTa.chat_joinid}" target="iFrame_main" class="dropdown-item ezmess" data-chatid="${daTa.chat_id}" data-joinid="${daTa.chat_joinid}">
            <div class="media">
              <img src="${chatAvatar}" alt="User Avatar" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">${daTa.chat_poster}</h3>
                <p class="text-sm">${messShort}</p>
                <p class="text-xxs text-muted"><i class="far fa-clock mr-1"></i> ${postPast}</p>
              </div>
            </div>
            <div class="dropdown-divider" style="margin-top:4px;"></div>
          </a>
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
            async: false, 
          success: function (data) {
              if(data['RTN']==1){
                var d = data.messRes;
                if(data.messRes.length > 0){
                  var content = '';
                  for (var i = 0; i < d.length; i++) {
                    content += `
                        <a href="mess_board.php?chatType=1&joinID=${d[i].chat_joinid}" target="iFrame_main" class="dropdown-item ezmess" data-chatid="${d[i].chat_id}" data-joinid="${d[i].chat_joinid}">
                          <div class="media">
                            <img src="${d[i].chatAvatar}" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                            <div class="media-body">
                              <h3 class="dropdown-item-title">${d[i].chat_poster}</h3>
                              <p class="text-sm">${d[i].message}</p>
                              <p class="text-xxs text-muted"><i class="far fa-clock mr-1"></i> ${d[i].postPast}</p>
                            </div>
                          </div>
                          <div class="dropdown-divider" style="margin-top:4px;"></div>
                        </a>
                    `;
                  }
                }
                $('#messDropdown').prepend(content);
              }
            },
            error: function (data) {
              console.log(data);
              //window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
              toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
              return false;
            }
        });
      }

  }
  //getEasyMess();//開頁時同時取得簡易訊息
  //------- 取得簡易通知(上方導航列使用) -------
  //------- 預設取得最新50筆，顯示10筆，顯示的通知被點閱後不會刪除 -------
  //------- 由後續被隱藏的通知遞補顯示，直到通知低於10筆時重新獲取50筆 -------
  function getEasyNotify(nuM=50,daTa='') {
    if(daTa){
      var infoShort = daTa.noti_content.length > 20 ? daTa.noti_content.substring(0, 20) + '...' : daTa.noti_content;//將最新訊息縮短顯示
      var postPast = Math.floor((parseInt('<?php echo time(); ?>') - daTa.noti_published) / 3600);
            if (postPast > 24) {
                postPast = Math.floor(postPast / 24) + ' 天前';
            } else {
              if(postPast<=0){
                    postPast = '小於1小時';
                } else {
                    postPast = postPast + ' 小時前';
                }
            }
      var colorClass = daTa.cAlert !== '' ? ' color' + daTa.cAlert : '';
      var newInfo = `
            <a href="mess_notidetail.php?currID=${daTa.noti_id}" target="iFrame_main" class="dropdown-item eznotify" style="position:relative;" data-ifid="${daTa.noti_id}">
              <i class="fas fa-envelope mr-2${colorClass}"></i>${infoShort}
              <span class="text-muted text-sm" style="position:absolute;bottom:8px;right:8px;"> ${postPast}</span>
              <div class="dropdown-divider" style="margin-top:24px;"></div>
            </a>
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
            async: false, 
          success: function (data) {
              if(data.RTN == 1){
                var d = data.notifyRes;
                if(d.length > 0){
                  var infocontent = '';
                  for (var i = 0; i < d.length; i++) {
                    var colorClass = d[i].cAlert !== '' ? ' color' + d[i].cAlert : '';
                    infocontent += `
                        <a href="mess_notidetail.php?currID=${d[i].noti_id}" target="iFrame_main" class="dropdown-item eznotify" style="position:relative;" data-ifid="${d[i].noti_id}">
                          <i class="fas fa-envelope mr-2${colorClass}"></i>${d[i].content}
                          <span class="text-muted text-sm" style="position:absolute;bottom:8px;right:8px;"> ${d[i].postPast}</span>
                          <div class="dropdown-divider" style="margin-top:24px;"></div>
                        </a>
                    `;
                  }
                }
                $('#badge_notify').html(data.totalNum);
                $('#notifyDropdown').prepend(infocontent);
              }
            },
            error: function (data) {
              console.log(data);
              toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
            }
        });
      }

  }
  //getEasyNotify();//開頁時同時取得簡易訊息
  //-------- 檢查頭像檔案是否存在 ---------
  function checkIfFileExist(fileName) {
    filename = fileName.trim();
    var response = $.ajax({
      url: filename,
      type: 'HEAD',
      async: false
    }).status;	
    return (response != "200") ? '../imageFiles/nopic_user_64x64.jpg' : filename;
  }

////////////////////////////////////////////////////////////////
// ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
// ---------------------------------------------------------- //
////////////////////////////////////////////////////////////////
  $(document).ready(function() {
    /////////////// Top Navigator 按鈕群操作 //////////////////////
    $('#topBut_upload').click(function(e){ 
      e.preventDefault();
      if(!$(this).hasClass('active')){ return false;}
      $("#iFrame_main")[0].contentWindow.doButUpload();
    });
    // 操作按鈕框_儲存/（新增/更新/上傳 通用）
    $('#topBut_add').click(function(e){ 
      e.preventDefault();
      if(!$(this).hasClass('active')){ return false;}
      if (confirm('當按下按鍵繼續進行操作時，即代表同意遵循「人生第五季管理辦法」進行操作！\r\r確定進行資料新增操作？！')){
        $("#iFrame_main")[0].contentWindow.doButAdd();
      }
    });
    // 操作按鈕框_存儲
    $('#topBut_save').click(function(e){ 
      e.preventDefault();
      if(!$(this).hasClass('active')){ return false;}
      if (confirm('當按下按鍵繼續進行操作時，即代表同意遵循「人生第五季管理辦法」進行操作！\r\r確定進行資料儲存操作？！')){
        $("#iFrame_main")[0].contentWindow.doButSave();
      }
    });
    // 操作按鈕框_復原
    $('#topBut_reset').click(function(e){ 
      e.preventDefault();
      if(!$(this).hasClass('active')){ return false;}
      $("#iFrame_main")[0].contentWindow.doButReset();
    });
    // 操作按鈕框_刪除
    $('#topBut_delete').click(function(e){ 
      e.preventDefault();
      if(!$(this).hasClass('active')){ return false;}
      $("#iFrame_main")[0].contentWindow.doButDelete();
    });
    //
    
    $('#topBut_bkurl').click(function(e){
      //$("#iFrame_main")[0].contentWindow.doReDirect();
      //alert('tyjutyu');
      backIframe();
    });
    
    //
    $('#topBut_search').click(function(e){
      e.preventDefault();
      /*
      var sidebarElm = $("#iFrame_main").contents().find("#sidebarControl");
      if (sidebarElm.length) {
        sidebarElm.click();
      }
      */
      $("#iFrame_main")[0].contentWindow.doButSerach();
    });
    $('#topBut_excel').click(function(e){
      e.preventDefault();
      $("#iFrame_main")[0].contentWindow.doButExcel();
    });
    $('#topBut_print').click(function(e){
      e.preventDefault();
      $("#iFrame_main")[0].contentWindow.doButPrint();
    });

    //
    $('#lockscreen').click(function(e){
      window.location.assign("../lockscreen.php?backURI=" + getUrlHex());
    }); 
    $('#logout').click(function(e){
      if(!confirm('確定登出系統？！')){return false;}
      window.location.replace("../5sladmin.php");
    });
    //------------------------------------------------------------------------


    /*NOTE: 左側樹狀目錄首層數值標註顯示控制 */
    $('#sidebarL > ul > li > a').click(function() { 
      var parentLI = $(this).parent();
      var bedgeSPAN = $(this).parent().find('> a span');
      var allLIspan = parentLI.parent().find('> li').find('span');//遍歷所有下一層的 Li
      if(parentLI.hasClass('menu-open')){
        bedgeSPAN.css("visibility", "visible");
      } else {
        allLIspan.css("visibility", "visible");
        bedgeSPAN.css("visibility", "hidden");
      }
    });
    
    //------------------------------------------------------------------------
    //let countdownSec = parseInt('<?php $getidleout = $DB->getConfigVal('sys_idleout'); echo $getidleout['config_value']; ?>');
    let countdownSec = 600;
    $('#timer_on_exhausted').backward_timer({
      seconds: countdownSec,//600
      format: 'h%:m%:s%',
      on_exhausted: function(timer) {
        window.location.assign("../lockscreen.php?backURI=" + getUrlHex());
        //window.location.assign("../lockscreen.php");
      }
    });
    $('#timer_on_exhausted').backward_timer('start');
    //------------------------------------------------------
    $('body').on('keypress click', function(){
      $('#timer_on_exhausted').backward_timer('reset');
    });
    ///////////////////////////////////////////////////////
    //--------------- 網頁歷史紀錄操作區域函數 --------------//
    ///////////////////////////////////////////////////////
    var iframe = document.getElementById('iFrame_main');
    var lastIframeUrl = sessionStorage.getItem('Current_Iframe_URL');
    
    // 💡 F5 重新整理原地復活機制：重新整理時，把 src 屬性指回剛剛的內頁
    if (lastIframeUrl) {
        iframe.setAttribute('src', lastIframeUrl);
    } else {
        iframe.setAttribute('src', '5sladmin_startpage.html'); 
    }

    // 💡 側邊欄點擊事件：切換主功能時精準清洗舊狀態
    $('.main-sidebar, .nav-sidebar').on('click', 'a:not([data-widget="pushmenu"])', function() {
        //alert('側邊欄點擊事件');
        //console.log("【外框選單切換】切換不同主功能，清除分頁快取");
        sessionStorage.removeItem('Current_Iframe_URL');
        if (typeof window.clearIframeCache === 'function') {
            window.clearIframeCache();
        }
    });    
//////////////////////////////////////////////////////////////////////////
  });
