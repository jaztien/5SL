<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_SIGNUP');
define('PAGE_ITEM','ITEM_SERV');
define('PAGE_OPANEL',1);//1-新增；2-編輯；3-上傳；4-編輯/刪除；9-[空]。
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//架構搜尋條件(以搜尋條件架構搜尋表格，以及URI傳遞值字串)
$searchText = isset($_REQUEST['searchText']) ? rawurldecode($_REQUEST['searchText']) : ''; 
$searchxxxx = isset($_REQUEST['searchxxxx']) ? $_REQUEST['searchxxxx'] : '';
//
$searchURI = "";
//
if(!empty($searchText)){ $searchURI .= "&searchText=".$searchText; }
if(!empty($searchxxxx)){ $searchURI .= "&searchxxxx=".$searchxxxx; }
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}
//定義條件搜尋之前以及之後不同的顯示筆數
$maxResult = (!empty($searchURI)) ? 200 : 50;
//功能陣列建立，取自JSON檔案

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
	
  <title> Fifth Season Life 人生第五季 | 講座課程報名紀錄 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <!--
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-bs4/css/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-responsive/css/responsive.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-buttons/css/buttons.bootstrap4.css">
  -->
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css">
<!-- -------------------------- /CSS Link -------------------------- -->
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <input type="text" id="searchURI" name="searchURI" style="width:1px;height:1px;opacity:0;" value="<?php //echo $searchURI; ?>">
  <!-- 全頁框 -->
  <div class="wrapper">
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="../imageFiles/preloaderLOGO.png" alt="Fifth Season Life LOGO" height="60" width="60"> 
    </div>
    <!-- /Preloader -->
    <!-- 頁內全頁內容 -->
    <div class="content-wrapper">
      <!-- 頁頭導覽條 -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h4 class="m-0">講座課程報名紀錄</h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">開放式服務管理</li>
                <li class="breadcrumb-item active">講座課程報名紀錄</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <!-- /頁頭導覽條 -->
      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <!-- 主要內容區域 ------------------------------------- -->
              <div class="card">
                <div class="card-header">
                  &nbsp;&nbsp;<h6 class="card-title" style="font-size: 1.2em;"><?php echo (empty($searchURI)) ? "最新{$maxResult}筆資料" : "依據搜尋結果表列最近{$maxResult}筆紀錄。"; ?>
                  <i class="nav-icon fas fa fa-exclamation-circle" style="font-size: 0.75em;font-weight:light;color:#B8BFCB;"> 搜尋上限預設為<?php echo $maxResult; ?>筆，請縮小搜尋範圍以減少系統負載。若要回復原始表列內容，請開啟進階搜尋側欄清除搜尋條件。</i>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                  <table id="dataTable_xxxlist" class="table table-bordered table-striped table-hover" data-pagemax="25" data-dirto="xx_xxxx.php" data-search but-addnew>
                    <thead>
                      <tr>
                        <th>XXX</th>
                        <th>XXX</th>
                        <th>XXX</th>
                        <th>XXX</th>
                        <th>XXX</th>
                      </tr>
                    </thead>
                    <tbody>
                      <td>xxxxx</td>
                      <td>xxxxx</td>
                      <td>xxxxx</td>
                      <td>xxxxx</td>
                      <td>xxxxx</td>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>XXX</th>
                        <th>XXX</th>
                        <th>XXX</th>
                        <th>XXX</th>
                        <th>XXX</th>
                      </tr>
                    </tfoot>
                  </table>
                <!-- /.card-body -->
                </div>
              <!-- /.card -->
              <!-- /主要內容區域 ------------------------------------ -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
      </section>
      <!-- /主要內容區域 ------------------------------------ -->
    </div>
    <!-- /頁內全頁內容 -->

    <!-- jQuery -->
    <script src="../pluginFiles/jquery/jquery.min.js"></script>

    <!-- 右側功能控制區 -->
      <aside class="control-sidebar control-sidebar-dark">
        <!-- 功能區內容 ------------------------------------ -->
        <form id="form_search" name="form_search" action="" method="get">
          <div style="height:100%;display:flex;flex-direction: column;">
            <!-- 上格 -->
            <div style="flex: 1;padding:4px">
              <p>上格內容（置頂）</p>
            </div>
            <!-- 按鈕爛 -->
            <div class="form-group row" style="height:56px;margin:4px;">
              <button type="submit" class="col-sm-7 btn btn-block btn-info notSubmit"  id="searchSend" name="searchSend" style="height:48px;padding:1px;margin:0;"><i class="fa fa-solid fa-magnifying-glass"></i> 執行搜尋</button>
              <button type="button" class="col-sm-3 btn btn-block btn-secondary"  id="searchReset" name="searchReset" style="height:48px;padding:1px;margin:0;">清除</button>
              <button type="button" class="col-sm-2 btn btn-block btn-warning"  id="sidebarControl" name="sidebarControl" style="height:48px;padding:1px;margin:0;" data-widget="control-sidebar" data-controlsidebar-slide="true"><i class="fa fa-solid fa-right-from-bracket"></i></button>
            </div>
          </div>
        </form>
        <!-- /功能區內容 ----------------------------------- -->
      </aside>
    <!-- /右側功能控制區 -->
  </div>
  <!-- /全頁框 -->
<!-- ------------------------ Scripts Link ------------------------ -->
<!-- jQuery is in front of footer file. -->
<!-- Bootstrap 4 -->
<!-- <script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script> -->
<!-- Bootstrap 5 -->
<script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
<!-- DataTables2 & Plugins -->
<!--
<script src="../pluginFiles/datatables/datatables/jquery.dataTables.min.js"></script>
<script src="../pluginFiles/datatables/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
-->
<!-- DataTables3 & Plugins -->
<script src="../pluginFiles/datatables3/datatables.min.js"></script>

<script src="../pluginFiles/jszip/jszip.min.js"></script>
<script src="../pluginFiles/pdfmake/pdfmake.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.html5.min.js"></script>
<!-- skip for list without advance buttons
<script src="../pluginFiles/datatables/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../pluginFiles/datatables/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.colVis.min.js"></script>
<script src="../pluginFiles/pdfmake/vfs_fonts.js"></script>
-->
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>
<!-- ------------------------ /Scripts Link ------------------------ -->
<script>
  $(function () {
    //* iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });
    //-----------------------------------------
    //var mblistTB = datatableCreate('dataTable_xxxxlist');
    //搜尋表格重置清除
    $('#searchReset').click(function() {
      $('#form_search').find('input').val('');
      $('#form_search').find('select').val('');
      //$("#searchDist,#searchCOexpert").find("option:gt(0)").remove();
      //$("#searchCOexpert").find("optgroup").remove();
      //$('#form_search input[type="checkbox"]').prop('checked', false);
    });
  });
  ////////////////////////////////////////////////////////////////     
  // ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
  // -------------- 可接收include檔案要求後自定義動作 ------------- //
  ////////////////////////////////////////////////////////////////
  // 當「新增」按鈕按下時的動作
  function doButAdd(){
    window.location.replace('xxxxxxxx.php?currID=0');
  }
  // 當「重置」按鈕按下時的動作
  function doButReset(){
    location.reload(true);
  }

</script>
</body>
</html>
