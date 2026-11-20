////////////////////////////////////////////////////////////////

//var currID = $('#currID').val() || 0;
/*
var searchURI = {};//parseUriToObj
if($('#searchURI').val() !== '') {
  searchURI = parseUriToObj($('#searchURI').val());
}
  */
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
  //var jumpTO = parseInt(ths.data('jumpto'), 10) || 1;
  //jumpTO = (jumpTO - 1) * pageMax;
  var butClass = ths.data('butClass') || 'gray';
  var currentPage = 1;
  // 智慧判斷是否該套用初始 order
  // 先去撈撈看有沒有這頁的 DataTable 歷史快取紀錄
  var hasSavedState = sessionStorage.getItem('DT_State_' + window.location.pathname);
  var defaultOrder = [[orderBy, sortOrder]];
  // 如果有快取紀錄，就把初始 order 設為 undefined，避免它在初始化時覆蓋快取並重置頁碼
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
                text: 'Print', // 按鈕上顯示的文字
                title: printTitle,     // PDF/列印頁面的大標題
                //messageTop: '本報表由系統自動產生', // 表格上方的備註文字
                messageBottom: '--- 報表結束 ---', // 表格下方的備註文字
                exportOptions: {
                    columns: ':visible' // 只列印目前網頁上看得到的欄位 (隱藏欄位不列印)
                },
                customize: function (win) {
                    // 自訂列印網頁的 CSS 樣式（選填）
                    $(win.document.body)
                        .css('font-size', '10pt')
                        .css('color', '#333');
                    // 讓表格在列印時滿版
                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit')
                        .css('width', '100%');
                    // 動態注入 CSS 實現橫向列印
                    $(win.document.head).append('<style>@page { size: landscape; }</style>');
              }
            });
  }
  if (ths.attr('but-advsearch') != undefined) {
    var currClass = "advSearchBut";
    var currText = ' <i class="fa fa-search" data-widget="control-sidebar" data-controlsidebar-slide="true"> 進階搜尋</i>';
    /*
    if(ths.attr('onSearching') != undefined){
      currClass = 'btn btn-info';
      currText = '<span class="right badge badge-warning" style="position:absolute; top: -12px;">條件搜尋中</span> <i class="fa fa-search" data-widget="control-sidebar" data-controlsidebar-slide="true"> 進階搜尋</i>';
    }
    */
    newTableBut.push({
      className: currClass,
      text: currText
    });
  }
  //------------- 表格按鈕-新增按鈕 -----------------//
  if (ths.attr('but-addnew') !== undefined) {
    var buttext = ths.attr('but-addnew');
    if(buttext == ''){ buttext = '新增資料'; }
    newTableBut.push({
      className:'advAddBut'+'-'+butClass,
      text: '<i class="fa fa-circle-plus"> ' + buttext + '</i>',
      action: function (e) {
        var tbInfo = mainTable.page.info();
        //if (confirm('當按下按鍵繼續進行操作時，即代表同意遵循「人生第五季管理辦法」進行操作！\r\r確定進行資料新增操作？！')){
        var uri = ths.data('dirto');
          uri += '?currID=' + $(this).data('currid');
          uri += '&pageMax=' + pageMax;
          uri += '&currPage=' + tbInfo.page;
          uri += '&currOrder=' + mainTable.order();
          if($('#searchURI').val() !== '') { uri += '&' + $('#searchURI').val(); }
          window.location.replace(uri);
        //}
      }
    });
  }
  //------------- 表格按鈕-開啟彈出視窗 -----------------//
  if (ths.attr('but-popup') !== undefined) {
    var idPreTitle = tableID.split('_');
    var preTitle = idPreTitle[1];
    var buttext = ths.attr('but-popup');
    if(buttext == ''){ buttext = '新增資料'; }
    //var emptyURI='?id=0&rulemode=&fixed=&posit=&group=&top=&indist=&weekday=&weekend=&period=&status=0';
    var emptyURI='?id=0';
    //var emptyURI='';
    newTableBut.push({
      className:'popupBut'+'-'+butClass,
      text: '<i class="fa fa-circle-plus"> ' + buttext + '</i>',
      action: function (e) {
        $('#'+preTitle+'_modal').show().addClass('show');
        //當 data-include 賦值時（有效的檔案名稱），則進行檔案載入
        if (ths.data('include') !== undefined && ths.data('include') !== '') {
          $('#'+preTitle+'_modal .modal-container').load(ths.data('include')+emptyURI);
        }
      }
    });
  }
  var hasButtons = (typeof newTableBut !== 'undefined' && newTableBut && newTableBut.length > 0);
  //var mainTable = ths.DataTable({
  var mainTable = new DataTable(ths, {
    "order": defaultOrder,// 💡 這裡改用我們剛剛智慧判斷過後的 defaultOrder
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
    //"displayStart": jumpTO,
    //以下是穩健的狀態快取對接
    "stateSave": true, 
    "stateDuration": -1, 
    "stateSaveCallback": function(settings, data) {
        sessionStorage.setItem('DT_State_' + window.location.pathname, JSON.stringify(data));
    },
    "stateLoadCallback": function(settings) {
        return JSON.parse(sessionStorage.getItem('DT_State_' + window.location.pathname));
    },
    //強制 DataTable 在載入完畢後，強制跳回到快取的頁碼位置（非必要）
    /*
    "initComplete": function(settings, json) {
        var api = this.api();
        var state = api.state.loaded();
        
        if (state && state.start !== undefined) {
            var targetPage = Math.floor(state.start / state.length);
            setTimeout(function() {
                console.log("【生命週期強推】DataTable 載入完畢，強制彈回第 " + (targetPage + 1) + " 頁");
                api.page(targetPage).draw('page'); //
            }, 50);
        }
    },
  */
    "columnDefs": [
        {
            "targets": colOrder,     
            "orderable": doOrder
        }
    ],
    "layout": {
      // 定義左下方（bottomStart）同時放 info 和 pageLength，它們會自動並排！
      "bottomStart": ['info', 'pageLength'], 
      "bottomEnd": 'paging',   // 右下方放分頁
      "topStart": hasButtons ? {
                    buttons: newTableBut
                } : null,        // 清空預設的左上方位置
      "topEnd": null       // 右上方維持搜尋
    },
        // 關鍵新增：每次換頁、重繪時自動觸發
    //"dom": 'frt<"footer-left"il>p' ,//舊版本 1.x
    //"lengthMenu": [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "All"]],
    //"select": true,
    //"keys": false,
    //"colReorder": true,
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
      "oAria": {
          "sSortAscending": ": 以升序排列此列",
          "sSortDescending": ": 以降序排列此列"
      }
    }
        /*
    "drawCallback" : function () {
      thsPagi = $('#'+tableID+'_paginate');
      thsPagi.find('.page-item.active a').addClass(butClass);
    }
      */
  });
  //------------- 點選表格列時，導向到指定頁面 -----------------//
  mainTable.on('click','tbody tr',function(e) {
    //點選表格列時，先檢查點選的地方是否為最後一個欄位（操作/選擇欄）
    // 【最精準判斷】找出滑鼠當下點擊的元素，最鄰近的 td 是不是最後一欄
    var $clickedTd = $(e.target).closest('td');
    // 【跳過最後一欄(留給附加操作)】如果點擊的是最後一欄，直接結束並放行讓按鈕或是 checkbox 處理
    if ($clickedTd.is(':last-child')) {
        return; 
    }
    // 如果點擊的不是最後一欄，而且表格中帶有 data-dirto 時，則執行相應目標的導向操作
    if (ths.data('dirto') !== undefined && ths.data('dirto') !== '') {
      var targetUrl = ths.data('dirto');
      var uri = targetUrl + '?currID=' + $(this).data('currid');
      window.location.replace(uri);
    }
    //觸發頁面中帶有 data-triggerto 的值的同名函式
    //當 datatable 表格中沒有帶有 data-dirto，並且表格中帶有 data-triggerto 時，則啟動「動態函式映射」功能，並執行對應的函式
    else if(ths.data('triggerto') !== undefined && ths.data('triggerto') !== ''){
      if (typeof window[ths.data('triggerto')] === "function") {
          window[ths.data('triggerto')]($(this)); //這裡就等同於執行 function 【data-triggerto】();
      } else {
          console.error("找不到名為 " + ths.data('triggerto') + " 的全域函式！");
      }
    }
    //當 table 中沒有帶 data-dirto 也沒有帶 data-triggerto 時，則不執行任何動作
    else{
      return;
    }
  });
  //------------- 表格按鈕組合及顯示 -----------------//
  /*
  if(newTableBut.length>0){
    new $.fn.dataTable.Buttons( mainTable, {
        buttons: newTableBut
    });
    mainTable.buttons( 0, null ).container().prependTo('#'+ths.prop('id')+'_wrapper .col-md-6:eq(0)');//table.table().container()
  }
    */
  //輸出最終結果
  return mainTable;
}


function datatableGroup(tableID,pageMax,butClass){
  //var ths = $('#'+tableID);
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
    //"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
    //"select": true,
    //"keys": false,
    //"colReorder": true,
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
  //if (ths.attr('but-addnew') !== undefined) {
    //var buttext = ths.attr('but-addnew');
    //if(buttext == ''){ buttext = '新增資料'; }
    buttext = '新增資料';
    newTableBut.push({
      className:'advAddBut'+'-'+butClass,
      text: '<i class="fa fa-circle-plus"> ' + buttext + '</i>',
      action: function (e) {
        var tbInfo = groupTable.page.info();
        var uri = ths.data('dirto');
        uri += '?currID=' + $(this).data('currid');
        uri += '&pageMax=' + pageMax;
        uri += '&currPage=' + tbInfo.page;
        uri += '&currOrder=' + groupTable.order();
        if($('#searchURI').val() !== '') { uri += '&' + $('#searchURI').val(); }
        window.location.replace(uri);  
      }
    });
 // }

  //if(newTableBut.length>0){
    new $.fn.dataTable.Buttons( groupTable, {
        buttons: newTableBut
    });
    groupTable.buttons( 0, null ).container().prependTo('#'+ths.prop('id')+'_wrapper .col-md-6:eq(0)');//table.table().container()
 //}
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
  var saltH = Omuai.slice(0, 12);//Omuai.substring(0,12);
  var saltT = Omuai.slice(-12);//Omuai.substr(Omuai.length-12,12);
  var muai = calcMD5(saltH+oNaviString+saltT);
  return muai;
}
function getCookieVal(cookieName) {
  /*
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
  */
  let cookie = {};
  document.cookie.split(';').forEach(function(el) {
    let [key,value] = el.split('=');
    cookie[key.trim()] = value;
  })
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
    })
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
    var dateObj = new Date(dateString); //將字串轉換成 Date 物件
    var dayNumber = dateObj.getDay(); //取得星期數字 (0 ~ 6)
    var weekNames = ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"];//定義中文名稱對照表
    return { //回傳物件，讓你數字和中文名字都能拿得到
        number: dayNumber,         // 例如: 1
        name: weekNames[dayNumber] // 例如: "星期一"
    };
}

/**
 * 計算所有的上課日期
 * @param {string} startDateStr - 起始日期字串 (例如 "2026-10-31")
 * @param {Array} classDays - 上課的星期數字陣列 (例如 代表一、四、六)
 * @param {number} totalClasses - 總堂數 (例如 10)
 * @returns {Array} 包含所有上課日期的字串陣列
 */
function getCourseDatesTriple(startDateStr, classDays, totalClasses) {
    var dateArray = [];    // 1. 裝純日期，例如 "2026-10-31"
    var weekdayArray = []; // 2. 裝星期中文，例如 "星期六"
    var numDayArray = [];  // 3. 裝星期數字，例如 6
    
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
    
    // 👇 關鍵追加：抓取陣列的最後一筆，即為「結業日期 / 最後一日」
    // 如果總堂數是大於 0 的話才抓取，做個防呆
    var lastDate = dateArray.length > 0 ? dateArray[dateArray.length - 1] : "";
    var lastWeekday = weekdayArray.length > 0 ? weekdayArray[weekdayArray.length - 1] : "";
    
    // 回傳時，多附帶一個結業日期的欄位
    return {
        dates: dateArray,
        weekdays: weekdayArray,
        dayNums: numDayArray,
        endDate: lastDate,             // 👈 結業日期 (例如: "2026-11-21")
        endWeekday: lastWeekday        // 👈 結業日星期 (例如: "星期六")
    };
}

/**
 * 萬用拼接函式（可以自由挑選要怎麼組合日期、星期、週數三個資料）
 */
////////////////////////////////////////////////////////
//--------------------- 萬用拼接函式 --------------------//
//-----（可以自由挑選要怎麼組合日期、星期、週數三個資料） -----//
////////////////////////////////////////////////////////
function courseDateCombine(dates, weekdays, dayNumbers) {
    return $.map(dates, function(dateStr, index) {
        // 利用 index 隨時撈出對應的中文與數字
        var chineseDay = weekdays[index];
        var numericDay = dayNumbers[index];
        // 範例 A：2026-10-31 (星期六) [6]
        return dateStr + " (" + chineseDay + ") [" + numericDay + "]";
        // 範例 B：如果您只想拼日期跟數字，可以改成：
        // return dateStr + " 星期代碼：" + numericDay;
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
    if (typeof URLSearchParams !== 'undefined' && typeof Object.fromEntries !== 'function') {
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


