<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//架構搜尋條件(以搜尋條件架構搜尋表格，以及URI傳遞值字串)
$searchText = isset($_REQUEST['searchText']) ? rawurldecode($_REQUEST['searchText']) : ''; 
//$searchxxxx = isset($_REQUEST['searchxxxx']) ? $_REQUEST['searchxxxx'] : '';
//
$searchURI = "";
//
if(!empty($searchText)){ $searchURI .= "&searchText=".$searchText; }
//if(!empty($searchxxxx)){ $searchURI .= "&searchxxxx=".$searchxxxx; }
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}
//定義條件搜尋之前以及之後不同的顯示筆數
$maxResult = (!empty($searchURI)) ? 200 : 50;
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_SYSADMIN');
define('PAGE_ITEM','ITEM_SYS');
define('PAGE_OPANEL',1);//1-新增；2-編輯；3-上傳；4-編輯/刪除；9-[空]。
define('SEARCH_OPANEL',!empty($searchURI)?2:1);
define('EXPORT_OPANEL',3);//1-Excel；2-PDF；3-Excel+Print；
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//功能陣列建立，取自JSON檔案
$adJS=_fn_getCachedJsonData("../jsonFiles/administrator.json");

?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
	
  <title> Fifth Season Life 人生第五季 | {{頁面名稱}} </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <!-- Bootstrap 5 & Bootstrap Icons -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <!-- DataTables 3 -->
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <!-- Theme style (AdminLTE 4) -->
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
<!-- -------------------------- /CSS Link -------------------------- -->
</head>
<body class="bg-body-tertiary">
  <div class="app-wrapper">
    
    <main class="app-main">
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                {{頁面名稱}}
                <i class="bi bi-question-circle-fill fs-6 text-secondary" data-bs-toggle="tooltip" title="{{說明彈框}}"></i>
                <span class="text-muted small ms-2">{{檔案名稱}}</span>
              </h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">{{上層目錄名稱}}</li>
                <li class="breadcrumb-item active">{{頁面名稱}}</li>
              </ol>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </div>
      <!-- /頁頭導覽條 -->
      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
              {{頁面內容}}
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </div><!-- /.app-content -->
    </main>
    <!-- /頁內全頁內容 -->
    <!-- 右側功能控制區 -->
    <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasSearch" aria-labelledby="offcanvasSearchLabel">
      {{右側隱藏區塊內容}}
    </div>
    <!-- /右側功能控制區 -->
  </div>
  <!-- /全頁框 -->

  <!-- ------------------------ Scripts Link ------------------------ -->
  <!-- jQuery -->
  <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
  <!-- Bootstrap 5 -->
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <!-- DataTables 3 & Plugins -->
  <script src="../pluginFiles/datatables3/datatables.min.js"></script>
  <!-- 5SeasonLife App -->
  <script src="../scriptFiles/adminlte.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>
  <!-- ------------------------ /Scripts Link ------------------------ -->
  <script type="text/javascript">
  //*************************** *********************************//
  //***************** JAVASCRIPTS 全域調用參數設置 *****************//
  //*************************** *********************************//





  //*************************** *********************************//
  //*************** JAVASCRIPTS FUNCTIONs 全域調用 ***************//
  //*************** 可接收include檔案要求後自定義動作 ***************//
  //*************************** *********************************//
  // 當「新增」按鈕按下時的動作
  function doButAdd(){
    window.location.replace('?currID=0');
  }
  // 當「重置」按鈕按下時的動作
  function doButReset(){
    location.reload(true);
  }
  // [變更13] 側邊隱藏區域開啟閉合：改用 Bootstrap 5 Offcanvas 實例切換
  function doButSerach(){
    const offcanvasEl = document.getElementById('offcanvasSearch');
    if (!offcanvasEl) {
      return false;
    }
    const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
    bsOffcanvas.toggle();
  }
  // 系統自動生成Excel按鍵
  function doButExcel(){
    $('.buttons-excel').click(); 
  }
  // 系統開啟列印視窗按鍵
  function doButPrint(){
    $('.buttons-print').click(); 
  }

  //*************************** *********************************//
  //***************** jQuery FUNCTIONs 區域調用 ******************//
  //*************************** *********************************//
  $(document).ready(function() {

      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

      // 動態顯示筆數
      //$('#resultCount').html('<?php //echo $totalNum > $maxResult ? $totalNum."筆符合，表列".$maxResult : $totalNum; ?>');
    
      // iFrame 頁面中安全滯留倒數功能 scripts
      $('body').on('keypress click', function(){
        let timerElm = window.parent.$('#timer_on_exhausted');
        if( timerElm.length ){
          timerElm.backward_timer('reset');
        }
      });

      // 生成datatable表格
      var adlistTB = datatableCreate('dataTable_adlist');

      // 外置表列內容搜尋框動作操作
      $('#dataSearch').on('keyup search input paste cut', function () {
        if ($(this).is(':focus') || this.value !== '') {
            adlistTB.search(this.value).draw();
        }
      });

      // 搜尋表格重置清除
      $('#searchReset').click(function() {
        if($('#dataSearch').val() != '') {
          $('#dataSearch').val('');
          adlistTB.search('').draw();
        } else {
          $('#form_search').find('input').val('');
          $('#form_search').find('select').val('');
        }
      });

      //-------- 加載完成後更改外框主頁中樹狀目錄開合狀態 --------//
      if (window.parent && typeof window.parent.treeviewCheck === 'function') {
          window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
      }
  ////////////////////////////////////////////////////////////////
  });
  </script>

</body>
</html>
