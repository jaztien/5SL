////////////////////////////////////////////////////////////////
// -------------- JAVASCRIPTS FUNCTIONs 全域調用 -------------- //
// -------------- 可接收include檔案要求後自定義動作 -------------- //
////////////////////////////////////////////////////////////////
//----------------- 共用 DataTable 創建 --------------------//
function datatableCreate(tableID){

  var ths = $('#'+tableID);
  var thsPagi;
  //列出不需要 orderly 箭頭的欄位
  var colOrder = ths.data('colorder') || '';
  if(colOrder!=''){ colOrder = colOrder.split(',').map(Number); }
  else { colOrder = []; }
  var doOrder = ths.data('doorder') || 0;
  doOrder = doOrder == 1 ? true : false;
  var orderBy = ths.data('orderby') || 0;
  var sortOrder = ths.data('sortorder') || 'asc';
  //定義是否需要搜尋按鈕
  var search = false;
  if (ths.data('search') !== undefined) { search = true;}
  var pageMax = parseInt(ths.data('pagemax')) || 25;
  var butClass = ths.data('butClass') || 'gray';
  var currentPage = 1;
  
  // 智慧判斷是否該套用初始 order
  var hasSavedState = sessionStorage.getItem('DT_State_' + window.location.pathname);
  var defaultOrder = [[orderBy, sortOrder]];
  if (hasSavedState) {
      defaultOrder = undefined; 
  }

  //------------- 表格按鈕群組設定 -----------------//
  var newTableBut=[];
  if (ths.attr('but-excel') != undefined) {
    newTableBut.push("excel");
  }
  if (ths.attr('but-pdf') != undefined) {
    newTableBut.push("pdf");
  }
  if (ths.attr('but-print') != undefined) {
    var printTitle = ths.attr('but-print');
    if(printTitle == ''){ printTitle = '無標題'; }
    newTableBut.push({
      extend: 'print',
      text: 'Print',
      title: printTitle,
      messageBottom: '--- 報表結束 ---',
      exportOptions: {
          columns: ':visible'
      },
      customize: function (win) {
          $(win.document.body)
              .css('font-size', '10pt')
              .css('color', '#333');
          $(win.document.body).find('table')
              .addClass('compact')
              .css('font-size', 'inherit')
              .css('width', '100%');
          $(win.document.head).append('<style>@page { size: landscape; }</style>');
      }
    });
  }

  // [變更1] 進階搜尋按鈕：改用 Bootstrap Icons (bi-search) 並聯動 BS5 Offcanvas / doButSerach()
  if (ths.attr('but-advsearch') != undefined) {
    var currClass = "advSearchBut";
    var currText = '<i class="bi bi-search"></i> 進階搜尋';
    newTableBut.push({
      className: currClass,
      text: currText,
      action: function (e) {
        if (typeof doButSerach === 'function') {
          doButSerach();
        } else {
          const offcanvasEl = document.getElementById('offcanvasSearch');
          if (offcanvasEl) {
            bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).toggle();
          }
        }
      }
    });
  }

  //------------- 表格按鈕-新增按鈕 -----------------//
  // [變更2] 圖示改用 Bootstrap Icons (bi-plus-circle)
  if (ths.attr('but-addnew') !== undefined) {
    var buttext = ths.attr('but-addnew');
    if(buttext == ''){ buttext = '新增資料'; }
    newTableBut.push({
      className:'advAddBut'+'-'+butClass,
      text: '<i class="bi bi-plus-circle"></i> ' + buttext,
      action: function (e) {
        var tbInfo = mainTable.page.info();
        var uri = ths.data('dirto');
          uri += '?currID=' + ($(this).data('currid') || 0);
          uri += '&pageMax=' + pageMax;
          uri += '&currPage=' + tbInfo.page;
          uri += '&currOrder=' + mainTable.order();
          if($('#searchURI').val() !== '') { uri += '&' + $('#searchURI').val(); }
          window.location.replace(uri);
      }
    });
  }

  //------------- 表格按鈕-開啟彈出視窗 -----------------//
  // [變更3] 圖示改用 bi-plus-circle，並改用 Bootstrap 5 原生 Modal 實例開啟視窗
  if (ths.attr('but-popup') !== undefined) {
    var idPreTitle = tableID.split('_');
    var preTitle = idPreTitle[1];
    var buttext = ths.attr('but-popup');
    if(buttext == ''){ buttext = '新增資料'; }
    var emptyURI='?id=0';
    newTableBut.push({
      className:'popupBut'+'-'+butClass,
      text: '<i class="bi bi-plus-circle"></i> ' + buttext,
      action: function (e) {
        var modalEl = document.getElementById(preTitle + '_modal');
        if (modalEl && typeof bootstrap !== 'undefined') {
          bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else {
          $('#'+preTitle+'_modal').show().addClass('show');
        }
        //當 data-include 賦值時（有效的檔案名稱），則進行檔案載入
        if (ths.data('include') !== undefined && ths.data('include') !== '') {
          $('#'+preTitle+'_modal .modal-container').load(ths.data('include')+emptyURI);
        }
      }
    });
  }

  var hasButtons = (typeof newTableBut !== 'undefined' && newTableBut && newTableBut.length > 0);
  var mainTable = new DataTable(ths, {
    "order": defaultOrder,
    "paging": true,
    "pagingType": "full_numbers",
    "lengthChange": true,
    "lengthMenu": [[1,10, 25, 50, 100], [1,10, 25, 50, 100]], 
    "pageLength": pageMax,
    "searching": search,
    "ordering": true,
    "info": true,
    "autoWidth": false,
    "responsive": true,
    "stateSave": true, 
    "stateDuration": -1, 
    "stateSaveCallback": function(settings, data) {
        sessionStorage.setItem('DT_State_' + window.location.pathname, JSON.stringify(data));
    },
    "stateLoadCallback": function(settings) {
        return JSON.parse(sessionStorage.getItem('DT_State_' + window.location.pathname));
    },
    "columnDefs": [
        {
            "targets": colOrder,     
            "orderable": doOrder
        }
    ],
    "layout": {
      "bottomStart": ['info', 'pageLength'], 
      "bottomEnd": 'paging',
      // 即使 newTableBut 為空陣列也初始化 buttons 容器，避免後續呼叫 .button().add() 報錯
      "topStart": {
          buttons: newTableBut
      },
      "topEnd": null
    },
    language: {
      "lengthMenu": "顯示 _MENU_ 筆/頁",
      "sProcessing": "處理中...",
      "sZeroRecords": "没有匹配结果",
      "sInfo": "共 _MAX_ 筆搜尋紀錄",
      "sInfoEmpty": "目前共有 0 筆紀錄",
      "sInfoFiltered": " ",
      "sInfoPostFix": "",
      "sSearch": "在搜尋紀錄中尋找:",
      "sUrl": "",
      "sEmptyTable": "尚未有資料紀錄存在",
      "sLoadingRecords": "載入資料中...",
      "sInfoThousands": ",",
      "oPaginate": {
          "sFirst": "首頁",
          "sPrevious": "上一頁",
          "sNext": "下一頁",
          "sLast": "末頁"
      },
      "drawCallback": function(settings) {
        var $emptyTd = ths.find('tbody td.dt-empty, tbody td.dataTables_empty');
        if ($emptyTd.length > 0) {
          var colCount = ths.find('thead tr:first th').length;
          $emptyTd.attr('colspan', colCount);
        }
      },
      "oAria": {
          "sSortAscending": ": 以升序排列此列",
          "sSortDescending": ": 以降序排列此列"
      }
    }
  });

  //------------- 點選表格列時，導向到指定頁面 -----------------//
  mainTable.on('click','tbody tr',function(e) {
    var $clickedTd = $(e.target).closest('td');
    if ($clickedTd.is(':last-child')) {
        return; 
    }
    if (ths.data('dirto') !== undefined && ths.data('dirto') !== '') {
      var targetUrl = ths.data('dirto');
      var uri = targetUrl + '?currID=' + $(this).data('currid');
      window.location.replace(uri);
    }
    else if(ths.data('triggerto') !== undefined && ths.data('triggerto') !== ''){
      if (typeof window[ths.data('triggerto')] === "function") {
          window[ths.data('triggerto')]($(this));
      } else {
          console.error("找不到名為 " + ths.data('triggerto') + " 的全域函式！");
      }
    }
    else{
      return;
    }
  });

  var $tabPane = ths.closest('.tab-pane');
  if ($tabPane.length > 0) {
    var tabId = $tabPane.attr('id');
    $('a[data-bs-toggle="tab"][href="#' + tabId + '"], button[data-bs-toggle="tab"][data-bs-target="#' + tabId + '"]').on('shown.bs.tab', function () {
      mainTable.columns.adjust().responsive.recalc();
    });
  }

  return mainTable;
}


function datatableGroup(tableID,pageMax,butClass){
  var ths = $('[id^="'+tableID+'"]');
  var thsPagi;
  var groupTable = ths.DataTable({
    "paging": true,
    "pagingType": "full_numbers",
    "lengthChange": false,
    "searching": true,
    "ordering": true,
    "info": true,
    "autoWidth": false,
    "responsive": true,
    "pageLength": pageMax,
    "displayStart": 0,
    language: {
      "lengthMenu": "顯示 _MENU_ 筆搜尋紀錄",
      "sProcessing": "處理中...",
      "sZeroRecords": "没有匹配结果",
      "sInfo": "目前有 _MAX_ 筆搜尋紀錄",
      "sInfoEmpty": "目前共有 0 筆紀錄",
      "sInfoFiltered": " ",
      "sInfoPostFix": "",
      "sSearch": "在搜尋紀錄中尋找:",
      "sUrl": "",
      "sEmptyTable": "尚未有資料紀錄存在",
      "sLoadingRecords": "載入資料中...",
      "sInfoThousands": ",",
      "oPaginate": {
          "sFirst": "首頁",
          "sPrevious": "上一頁",
          "sNext": "下一頁",
          "sLast": "末頁"
      },
      "order": [[0, "desc"]],
      "oAria": {
          "sSortAscending": ": 以升序排列此列",
          "sSortDescending": ": 以降序排列此列"
      }
    },
    "drawCallback" : function () {
      thsPagi = $('#'+tableID+'_paginate');
      thsPagi.find('.page-item.active a').addClass(butClass);
      $emptyTd.attr('colspan', colCount);
    }
  });

  groupTable.on('click','tbody tr',function() {
    if (ths.data('dirto') === undefined || ths.data('dirto') === '') {
      return false;
    }
    var tbInfo = groupTable.page.info();
    var uri = ths.data('dirto');
      uri += '?currID=' + $(this).data('currid');
      uri += '&pageMax=' + pageMax;
      uri += '&currPage=' + tbInfo.page;
      uri += '&currOrder=' + groupTable.order();
      if($('#searchURI').val() !== '') { uri += '&' + $('#searchURI').val(); }
      window.location.replace(uri);  
  });
    
  var newTableBut=[];
  buttext = '新增資料';
  newTableBut.push({
    className:'advAddBut'+'-'+butClass,
    // [變更4] 圖示改用 Bootstrap Icons (bi-plus-circle)
    text: '<i class="bi bi-plus-circle"></i> ' + buttext,
    action: function (e) {
      var tbInfo = groupTable.page.info();
      var uri = ths.data('dirto');
      uri += '?currID=' + ($(this).data('currid') || 0);
      uri += '&pageMax=' + pageMax;
      uri += '&currPage=' + tbInfo.page;
      uri += '&currOrder=' + groupTable.order();
      if($('#searchURI').val() !== '') { uri += '&' + $('#searchURI').val(); }
      window.location.replace(uri);  
    }
  });

  new $.fn.dataTable.Buttons( groupTable, {
      buttons: newTableBut
  });
  groupTable.buttons( 0, null ).container().prependTo('#'+ths.prop('id')+'_wrapper .col-md-6:eq(0)');
  return groupTable;
}
// 簡易 JavaScript MD5 加密 
// 先用ls-byte轉換一個32位數字為十六進制字符串
function rhex(num){
  var hex_chr = "0123456789abcdef";
  var str = "";
  for(var j = 0; j <= 3; j++)
    str += hex_chr.charAt((num >> (j * 8 + 4)) & 0x0F) +
           hex_chr.charAt((num >> (j * 8)) & 0x0F);
  return str;
}
/** 將一個字符串轉換為一串16個字的塊，以數組形式存儲。 添加填充位和長度，如MD5標準中所述。**/
function str2blks_MD5(str){
  var nblk = ((str.length + 8) >> 6) + 1;
  var blks = new Array(nblk * 16);
  for(var i = 0; i < nblk * 16; i++) blks[i] = 0;
  for(i = 0; i < str.length; i++)
    blks[i >> 2] |= str.charCodeAt(i) << ((i % 4) * 8);
  blks[i >> 2] |= 0x80 << ((i % 4) * 8);
  blks[nblk * 16 - 2] = str.length * 8;
  return blks;
}
/** 整數相加，在2^32處包裹。這在內部使用16位操作 來解決一些JS解釋器中的錯誤。 **/
function add(x, y){
  var lsw = (x & 0xFFFF) + (y & 0xFFFF);
  var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
  return (msw << 16) | (lsw & 0xFFFF);
}
/** 將一個32位的數字向左旋轉。 **/
function rol(num, cnt){
  return (num << cnt) | (num >>> (32 - cnt));
}
/** 這些函數實現了每一輪的基本操作。算法的基本操作。 **/
function cmn(q, a, b, x, s, t){
  return add(rol(add(add(a, q), add(x, t)), s), b);
}
function ff(a, b, c, d, x, s, t){
  return cmn((b & c) | ((~b) & d), a, b, x, s, t);
}
function gg(a, b, c, d, x, s, t){
  return cmn((b & d) | (c & (~d)), a, b, x, s, t);
}
function hh(a, b, c, d, x, s, t){
  return cmn(b ^ c ^ d, a, b, x, s, t);
}
function ii(a, b, c, d, x, s, t){
  return cmn(c ^ (b | (~d)), a, b, x, s, t);
}
/** 取一個字符串並返回其MD5的十六進制表示。 **/
function calcMD5(str){
  var x = str2blks_MD5(str);
  var a =  1732584193;
  var b = -271733879;
  var c = -1732584194;
  var d =  271733878;
  for(var i = 0; i < x.length; i += 16){
    var olda = a;
    var oldb = b;
    var oldc = c;
    var oldd = d;

    a = ff(a, b, c, d, x[i+ 0], 7 , -680876936);
    d = ff(d, a, b, c, x[i+ 1], 12, -389564586);
    c = ff(c, d, a, b, x[i+ 2], 17,  606105819);
    b = ff(b, c, d, a, x[i+ 3], 22, -1044525330);
    a = ff(a, b, c, d, x[i+ 4], 7 , -176418897);
    d = ff(d, a, b, c, x[i+ 5], 12,  1200080426);
    c = ff(c, d, a, b, x[i+ 6], 17, -1473231341);
    b = ff(b, c, d, a, x[i+ 7], 22, -45705983);
    a = ff(a, b, c, d, x[i+ 8], 7 ,  1770035416);
    d = ff(d, a, b, c, x[i+ 9], 12, -1958414417);
    c = ff(c, d, a, b, x[i+10], 17, -42063);
    b = ff(b, c, d, a, x[i+11], 22, -1990404162);
    a = ff(a, b, c, d, x[i+12], 7 ,  1804603682);
    d = ff(d, a, b, c, x[i+13], 12, -40341101);
    c = ff(c, d, a, b, x[i+14], 17, -1502002290);
    b = ff(b, c, d, a, x[i+15], 22,  1236535329);    

    a = gg(a, b, c, d, x[i+ 1], 5 , -165796510);
    d = gg(d, a, b, c, x[i+ 6], 9 , -1069501632);
    c = gg(c, d, a, b, x[i+11], 14,  643717713);
    b = gg(b, c, d, a, x[i+ 0], 20, -373897302);
    a = gg(a, b, c, d, x[i+ 5], 5 , -701558691);
    d = gg(d, a, b, c, x[i+10], 9 ,  38016083);
    c = gg(c, d, a, b, x[i+15], 14, -660478335);
    b = gg(b, c, d, a, x[i+ 4], 20, -405537848);
    a = gg(a, b, c, d, x[i+ 9], 5 ,  568446438);
    d = gg(d, a, b, c, x[i+14], 9 , -1019803690);
    c = gg(c, d, a, b, x[i+ 3], 14, -187363961);
    b = gg(b, c, d, a, x[i+ 8], 20,  1163531501);
    a = gg(a, b, c, d, x[i+13], 5 , -1444681467);
    d = gg(d, a, b, c, x[i+ 2], 9 , -51403784);
    c = gg(c, d, a, b, x[i+ 7], 14,  1735328473);
    b = gg(b, c, d, a, x[i+12], 20, -1926607734);
    
    a = hh(a, b, c, d, x[i+ 5], 4 , -378558);
    d = hh(d, a, b, c, x[i+ 8], 11, -2022574463);
    c = hh(c, d, a, b, x[i+11], 16,  1839030562);
    b = hh(b, c, d, a, x[i+14], 23, -35309556);
    a = hh(a, b, c, d, x[i+ 1], 4 , -1530992060);
    d = hh(d, a, b, c, x[i+ 4], 11,  1272893353);
    c = hh(c, d, a, b, x[i+ 7], 16, -155497632);
    b = hh(b, c, d, a, x[i+10], 23, -1094730640);
    a = hh(a, b, c, d, x[i+13], 4 ,  681279174);
    d = hh(d, a, b, c, x[i+ 0], 11, -358537222);
    c = hh(c, d, a, b, x[i+ 3], 16, -722521979);
    b = hh(b, c, d, a, x[i+ 6], 23,  76029189);
    a = hh(a, b, c, d, x[i+ 9], 4 , -640364487);
    d = hh(d, a, b, c, x[i+12], 11, -421815835);
    c = hh(c, d, a, b, x[i+15], 16,  530742520);
    b = hh(b, c, d, a, x[i+ 2], 23, -995338651);

    a = ii(a, b, c, d, x[i+ 0], 6 , -198630844);
    d = ii(d, a, b, c, x[i+ 7], 10,  1126891415);
    c = ii(c, d, a, b, x[i+14], 15, -1416354905);
    b = ii(b, c, d, a, x[i+ 5], 21, -57434055);
    a = ii(a, b, c, d, x[i+12], 6 ,  1700485571);
    d = ii(d, a, b, c, x[i+ 3], 10, -1894986606);
    c = ii(c, d, a, b, x[i+10], 15, -1051523);
    b = ii(b, c, d, a, x[i+ 1], 21, -2054922799);
    a = ii(a, b, c, d, x[i+ 8], 6 ,  1873313359);
    d = ii(d, a, b, c, x[i+15], 10, -30611744);
    c = ii(c, d, a, b, x[i+ 6], 15, -1560198380);
    b = ii(b, c, d, a, x[i+13], 21,  1309151649);
    a = ii(a, b, c, d, x[i+ 4], 6 , -145523070);
    d = ii(d, a, b, c, x[i+11], 10, -1120210379);
    c = ii(c, d, a, b, x[i+ 2], 15,  718787259);
    b = ii(b, c, d, a, x[i+ 9], 21, -343485551);

    a = add(a, olda);
    b = add(b, oldb);
    c = add(c, oldc);
    d = add(d, oldd);
  }
  return rhex(a) + rhex(b) + rhex(c) + rhex(d);
}

//------------- JavaScript POST 傳遞資料至頁面 --------------//
//------------ （帶 TOKEN 雙重驗證，非 ajax post） -----------//
function postData(actionUrl, data) {
  var mapForm = $('<form id="mapform" action="' + actionUrl + '" method="post"></form>');
  for (var key in data) {
      if (data.hasOwnProperty(key)) {
          mapForm.append('<input type="hidden" name="' + key + '" id="' + key + '" value="' + data[key] + '" />');
      }
  }
  $('body').append(mapForm);
  mapForm.submit();
}

/**
 * 全站共用 Toast 通知呼叫器（自動判斷外框存在與否，防止單獨開啟內頁時報錯）
 */
function notifyToast(type, msg, title = '', delay = 3500) {
  if (window.parent && typeof window.parent.showBS5Toast === 'function') {
    window.parent.showBS5Toast(type, msg, title, delay);
  } else if (typeof window.showBS5Toast === 'function') {
    window.showBS5Toast(type, msg, title, delay);
  } else {
    // 單獨開啟內頁且未載入 Toast 容器時的備援提示
    alert((title ? title + '：' : '') + msg);
  }
}

function postDataToken(actionUrl, data) {
  var mapForm = $('<form id="mapform" action="' + actionUrl + '" method="post"></form>');
  for (var key in data) {
      if (data.hasOwnProperty(key)) {
          mapForm.append('<input type="hidden" name="' + key + '" id="' + key + '" value="' + data[key] + '" />');
      }
  }
  mapForm.append('<input type="hidden" name="token" id="token" value="' + getTokenVal() + '" />');
  $('body').append(mapForm);
  mapForm.submit();
}
function getTokenVal(){
  var uai = navigator.userAgent.toLowerCase();
  var oNaviString = uai + 'PHPSESSID=' + getCookieVal('PHPSESSID');
  var Omuai = calcMD5(oNaviString);
  var saltH = Omuai.slice(0, 12);
  var saltT = Omuai.slice(-12);
  var muai = calcMD5(saltH+oNaviString+saltT);
  return muai;
}
function getCookieVal(cookieName) {
  let cookie = {};
  document.cookie.split(';').forEach(function(el) {
    let [key,value] = el.split('=');
    cookie[key.trim()] = value;
  });
  return cookie[cookieName];
}

function getAlphabet(idX) {
  if(idX >= 26) { return false; }
  var alphabet = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
  return alphabet[idX];
}

//陣列轉換成字串，寫入於資料庫中。格式：[val1,val2,val3,.....]
function arrayTOstring(arr,emptY=false){
  var stR = '';
  if(arr.length > 0){
    $.each(arr, function(i, val) {
      if($.isNumeric(val)==true) { stR += val + ','; }
      else { stR += '"' + val + '",'; }
    });
    stR = '[' + stR.slice(0, -1) + ']'; 
  } else {
    if(emptY==false){ stR = '[]'; }
  }
  return stR;
}

function getCurrentPage(){
  var pathName = window.location.pathname; 
  var currentFileName = pathName.substring(pathName.lastIndexOf('/') + 1);
  return currentFileName;
}
function getCurrentURI(urI='',pathName=''){
  let currTab = $('.nav-pills li a.active');
  let currTabIndex = currTab.length > 0 ? 'tabIDX=' + currTab.data('index') : '';
  let refreshURL = urI;
  if(pathName===''){
    pathName = document.location.pathname.match(/[^\/]+$/)[0];
  }
  refreshURL = pathName + '?' +  currTabIndex + (currTabIndex!='' ? '&' : '') + urI;
  return refreshURL;
}
function datetimeToTimestamp(datetimeString) {
  const date = new Date(datetimeString);
  return date.getTime() / 1000;
}
//取得星期數字與中文名稱
function _js_fn_getWeekday(dateString) {
    var dateObj = new Date(dateString);
    var dayNumber = dateObj.getDay();
    var weekNames = ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"];
    return {
        number: dayNumber,
        name: weekNames[dayNumber]
    };
}

/**
 * 計算所有的上課日期
 */
function getCourseDatesTriple(startDateStr, classDays, totalClasses) {
    var dateArray = [];
    var weekdayArray = [];
    var numDayArray = [];
    
    var weekNames = ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"];
    var currentDate = new Date(startDateStr + 'T12:00:00');
    
    while (dateArray.length < totalClasses) {
        var currentDayOfWeek = currentDate.getDay(); 
        
        if (classDays.includes(currentDayOfWeek)) {
            var year = currentDate.getFullYear();
            var month = String(currentDate.getMonth() + 1).padStart(2, '0');
            var date = String(currentDate.getDate()).padStart(2, '0');
            
            dateArray.push(year + '-' + month + '-' + date);
            weekdayArray.push(weekNames[currentDayOfWeek]);
            numDayArray.push(currentDayOfWeek); 
        }
        
        currentDate.setDate(currentDate.getDate() + 1);
    }
    
    var lastDate = dateArray.length > 0 ? dateArray[dateArray.length - 1] : "";
    var lastWeekday = weekdayArray.length > 0 ? weekdayArray[weekdayArray.length - 1] : "";
    
    return {
        dates: dateArray,
        weekdays: weekdayArray,
        dayNums: numDayArray,
        endDate: lastDate,
        endWeekday: lastWeekday
    };
}

////////////////////////////////////////////////////////
//--------------------- 萬用拼接函式 --------------------//
////////////////////////////////////////////////////////
function courseDateCombine(dates, weekdays, dayNumbers) {
    return $.map(dates, function(dateStr, index) {
        var chineseDay = weekdays[index];
        var numericDay = dayNumbers[index];
        return dateStr + " (" + chineseDay + ") [" + numericDay + "]";
    });
}

////////////////////////////////////////////////////////
//------- 萬用 URI 字串轉物件工具函式 (全瀏覽器相容) -------//
////////////////////////////////////////////////////////
function parseUriToObj(uriString) {
    // 如果傳入的是空值，直接回傳空物件
    if (!uriString || uriString.trim() === '') return {};
    
    // 如果有帶網址問號 (例如 ?a=1&b=2)，自動把問號切掉
    if (uriString.indexOf('?') === 0) {
        uriString = uriString.substring(1);
    }

    // 🚀 1. 優先判斷：如果瀏覽器支援現代寫法，直接光速執行
    if (typeof URLSearchParams !== 'undefined' && typeof Object.fromEntries === 'function') {
        return Object.fromEntries(new URLSearchParams(uriString));
    }

    // 🍂 2. 備援方案：若不支援，自動降級切換成老舊瀏覽器相容寫法 (純 JS 迴圈)
    var objResult = {};
    var pairs = uriString.split('&');
    
    for (var i = 0; i < pairs.length; i++) {
        var pair = pairs[i].split('=');
        var key = pair[0] ? decodeURIComponent(pair[0]) : '';
        var value = pair[1] ? decodeURIComponent(pair[1]) : '';
        
        if (key) {
            objResult[key] = value;
        }
    }
    
    return objResult;
}

/**
 * 搭配 ArticleRankingSystem 的前端獨立函式（支援 0分 全空心星）
 * 修正點：已全面修正為 JavaScript 標準的 += 拼接語法
 */
function _js_fn_getStars(stars, asHtml) {
    asHtml = (asHtml !== undefined) ? asHtml : false;
    
    // 1. 0分安全攔截點
    if (Number(stars) === 0) {
        if (asHtml) return '<span class="star star-empty">☆</span>'.repeat(5);
        return '☆☆☆☆☆';
    }
    // 2. 限制分數範圍在 1.0 到 5.0 之間
    stars = Math.max(1.0, Math.min(5.0, Number(stars)));    
    // 3. 💡 核心優化：將分數精準校正到最近的 0.5 單位（例如 4.40 -> 4.50; 4.20 -> 4.00）
    var roundedStars = Math.round(stars * 2) / 2;
    // 4. 💡 採用數學地板函數，4.5 的地板就是 4，絕對不會暴衝成 5
    var fullStarsCount = Math.floor(roundedStars); 
    // 5. 精準判定有沒有半顆星（4.5 - 4 = 0.5 -> 有半星）
    var hasHalfStar = (roundedStars - fullStarsCount) === 0.5 ? 1 : 0;
    // 6. 剩餘的就是空星
    var emptyStarsCount = 5 - fullStarsCount - hasHalfStar;
    if (asHtml) {
        var html = '';
        for (var i = 0; i < fullStarsCount; i++) html += '<span class="star star-full">★</span>';
        if (hasHalfStar) html += '<span class="star star-half">★</span>';
        for (var i = 0; i < emptyStarsCount; i++) html += '<span class="star star-empty">☆</span>';
        return html;
    } else {
        return '★'.repeat(fullStarsCount) + (hasHalfStar ? '🌓' : '') + '☆'.repeat(emptyStarsCount);
    }
}
