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
	
  <title> Fifth Season Life 人生第五季 | 系統管理人員 </title>
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
<!-- -------------------------- /CSS Link -------------------------- -->
</head>
<!-- [變更1] 移除舊版 hold-transition sidebar-mini 等多餘類別，改用 bg-body-tertiary -->
<body class="bg-body-tertiary">
  <!-- 全頁框：[變更2] .wrapper 改為 .app-wrapper -->
  <div class="app-wrapper">
    
    <!-- 頁內全頁內容：[變更3] .content-wrapper 改為 .app-main -->
    <main class="app-main">
      <!-- [變更4] 使用 BS5 原生 visually-hidden 取代 inline style -->
      <input type="text" id="searchURI" name="searchURI" class="visually-hidden" value="<?php //echo $searchURI; ?>">
      
      <!-- 頁頭導覽條：[變更5] .content-header 改為 .app-content-header -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                管理人員列表
                <!-- [變更6] 圖示改為 bi-question-circle-fill，屬性改為 data-bs-toggle="tooltip" -->
                <i class="bi bi-question-circle-fill fs-6 text-secondary" data-bs-toggle="tooltip" title="人生第五季系統內的管理人員帳號清單，包含工程人員、網站管理人員、網站客服人員等。"></i>
                <span class="text-muted small ms-2">ad_list.php</span>
              </h4>
            </div>
            <div class="col-sm-6">
              <!-- [變更7] float-sm-right 改為 float-sm-end -->
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">系統設定管理</li>
                <li class="breadcrumb-item active">管理人員列表</li>
              </ol>
            </div>
          </div>
        </div>
        <!-- /.container-fluid -->
      </div>
      <!-- /頁頭導覽條 -->

      <!-- 主要內容區域：[變更8] .content 改為 .app-content -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card shadow-sm">
                <div class="card-header position-relative">
                  <?php if(!empty($searchURI)){ ?>
                    <!-- [變更9] badge-warning 改為 text-bg-warning -->
                    <span class="badge text-bg-warning" style="position:absolute; top: -6px;">條件搜尋中</span>
                  <?php } ?>
                  <div class="card-title mb-0" style="font-size: 1.1em;">
                    <?php echo (!empty($searchURI)) ? "依條件搜尋共" : '最新'; ?> <span id="resultCount"></span> 筆紀錄
                    <!-- [變更10] 圖示改為 bi-exclamation-circle -->
                    <span class="ms-2" style="font-size:0.75em;font-weight:normal;color:#5F6E86;">
                      <i class="bi bi-exclamation-circle"></i> 搜尋上限預設為<?php echo $maxResult; ?>筆，請縮小搜尋範圍以減少系統負載。若要回復原始表列內容，請開啟進階搜尋側欄清除搜尋條件。
                    </span>
                  </div>
                </div>
                <!-- /.card-header -->

                <div class="card-body theme-green">
                  <table id="dataTable_adlist" class="table table-bordered table-striped table-hover w-100" data-pagemax="25" data-dirto="ad_profile.php" but-excel but-print="人生第五季管理人員資料紀錄" data-search>
                    <thead>
                      <tr>
                        <th>狀態</th>
                        <th>ID</th>
                        <th>姓名</th>
                        <th>暱稱</th>
                        <th>管理等級</th>
                        <th>建檔日期</th>
                        <th>前次登入</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $listSql = "SELECT *, COUNT(*) OVER() AS totalNum FROM fsl_administrator LIMIT {$maxResult}";
                        //if(!empty($searchURI)){ $listSql .= " WHERE 1=1"; }
                        $sr = DB->query($listSql);
                        $totalNum = intval(@$sr[0]['totalNum']);
                        $i = 0;
                        while($i<count($sr)){ 
                        ?>
                            <tr style="cursor:pointer;" data-currid="<?php echo $sr[$i]['ad_id']; ?>">
                              <td>
                                <!-- [注意] 請確認 administrator.json 中的 badge 欄位已更新為 BS5 的 text-bg-* 類別 -->
                                <span class="badge <?php echo trim($adJS['badge'][$sr[$i]['ad_status']]); ?>"><?php echo $adJS['status'][$sr[$i]['ad_status']]; ?></span>
                              </td>
                              <td><?php echo $sr[$i]['ad_id']; ?></td>
                              <td><?php echo $sr[$i]['ad_name']; ?></td>
                              <td><?php echo $sr[$i]['ad_title']; ?></td>
                              <td><?php echo $adJS['authlevel'][$sr[$i]['ad_authlevel']] ?? '未定權限'; ?></td>
                              <td><?php echo $sr[$i]['ad_published']; ?></td>
                              <td><?php echo $sr[$i]['ad_onboard']; ?></td>
                            </tr>
                      <?php 
                          $i++;
                          } 
                      ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>狀態</th>
                        <th>ID</th>
                        <th>姓名</th>
                        <th>暱稱</th>
                        <th>管理等級</th>
                        <th>建檔日期</th>
                        <th>前次登入</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
      </div>
      <!-- /.app-content -->
    </main>
    <!-- /頁內全頁內容 -->

    <!-- [變更11] 右側功能控制區：改用 Bootstrap 5 原生 Offcanvas 取代已移除的 control-sidebar -->
    <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasSearch" aria-labelledby="offcanvasSearchLabel">
      <div class="offcanvas-header border-bottom border-secondary py-2">
        <h6 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-search me-2"></i>進階搜尋與過濾</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body p-2">
        <form id="form_search" name="form_search" action="" method="get" class="h-100 d-flex flex-column">
          <!-- 上格 -->
          <div class="flex-grow-1 p-1 overflow-auto">
            <div class="mb-1 small">
              <i class="bi bi-question-circle-fill text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="此欄位專為已完成搜尋結果的紀錄，進行縮小範圍的再次查詢，輸入即查不必按搜尋按鈕，查詢後點按「清除」按鍵即可回復原始查詢結果。"></i>
              列表內資料過濾查詢
            </div>
            <div class="mb-3">
              <input type="text" class="form-control form-control-sm notSubmit" id="dataSearch" name="dataSearch" value="" placeholder="-請輸入搜尋條件-" autocomplete="off">
            </div>
            
            <hr class="border-secondary">
            
            <div class="mb-1 small">
              <i class="bi bi-question-circle-fill text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="部份開放查詢的文字型欄位皆列入被查目標，搜尋條件(欄位)可以依照管理者需求增減，請產生需求時通知工程人員修改。"></i>
              綜合搜尋
            </div>
            <div class="mb-3">
              <input type="text" class="form-control form-control-sm" id="searchText" name="searchText" value="<?php echo htmlspecialchars($searchText); ?>" placeholder="請輸入文字搜尋條件" autocomplete="off">
            </div>
            <div class="small text-secondary">指定搜尋</div>
          </div>

          <!-- 按鈕欄：[變更12] 移除 .form-group 與 .btn-block，改用 BS5 Grid (g-1) 與 d-grid -->
          <div class="row g-1 pt-2 border-top border-secondary" style="height:56px;">
            <div class="col-7 d-grid">
              <button type="submit" class="btn btn-info notSubmit" id="searchSend" name="searchSend" style="height:44px;padding:1px;">
                <i class="bi bi-search"></i> 執行搜尋
              </button>
            </div>
            <div class="col-3 d-grid">
              <button type="button" class="btn btn-secondary" id="searchReset" name="searchReset" style="height:44px;padding:1px;">清除</button>
            </div>
            <div class="col-2 d-grid">
              <button type="button" class="btn btn-warning" id="sidebarControl" name="sidebarControl" style="height:44px;padding:1px;" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="bi bi-box-arrow-right"></i>
              </button>
            </div>
          </div>
        </form>
      </div>
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
    window.location.replace('ad_profile.php?currID=0');
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

      //------------------------- [變更14] Bootstrap 5 Tooltips 初始化 -----------------------------//
      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

      // 動態顯示筆數
      $('#resultCount').html('<?php echo $totalNum > $maxResult ? $totalNum."筆符合，表列".$maxResult : $totalNum; ?>');
    
      //* iFrame 頁面中安全滯留倒數功能 scripts
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
