<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_LECTURE');
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
<html lang="zh-Hant-TW">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
	
  <title> Fifth Season Life 人生第五季 | XXXXXXXX </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://googleapis.com">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- iCheck for checkboxes and radio inputs (BS5 可考慮逐步改用原生 form-check) -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- DataTables 3 -->
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <!-- Theme style (需確認此檔已基於 AdminLTE 4 / BS5 更新) -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css">
<!-- -------------------------- /CSS Link -------------------------- -->
</head>
<!-- [變更1] AdminLTE 4 建議在 iFrame 內頁使用單純的 layout 類別，移除舊版 layout-navbar-fixed 等干擾 -->
<body class="bg-body-tertiary">
  <input type="text" id="searchURI" name="searchURI" class="visually-hidden" value="<?php //echo $searchURI; ?>">
  
  <!-- [變更2] .wrapper 改為 .app-wrapper -->
  <div class="app-wrapper">
    
    <!-- [變更3] .content-wrapper 改為 .app-main -->
    <main class="app-main">
      <!-- 頁頭導覽條 (.content-header 改為 .app-content-header) -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">XXXXXXXXX紀錄</h4>
            </div>
            <div class="col-sm-6">
              <!-- [變更4] float-sm-right 改為 float-sm-end -->
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">XXXXXXXX管理</li>
                <li class="breadcrumb-item active">XXXXXXXX紀錄</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
      <!-- /頁頭導覽條 -->

      <!-- 主要內容區域 (.content 改為 .app-content) -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card shadow-sm">
                <!-- [變更5] 修正原本缺少 </h6> 閉合標籤的問題，並改用 Flexbox 排版 -->
                <div class="card-header d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title mb-0 d-inline-block" style="font-size: 1.2em;">
                      <?php echo (empty($searchURI)) ? "最新{$maxResult}筆資料" : "依據搜尋結果表列最近{$maxResult}筆紀錄。"; ?>
                    </h6>
                    <span class="ms-2 small text-secondary">
                      <i class="fas fa-exclamation-circle"></i> 
                      搜尋上限預設為<?php echo $maxResult; ?>筆，請縮小搜尋範圍以減少系統負載。若要回復原始表列內容，請開啟進階搜尋側欄清除搜尋條件。
                    </span>
                  </div>
                  <!-- [新增] 觸發右側 Offcanvas 搜尋欄的按鈕 (若原本是由外框或 table 按鈕觸發，可保留原邏輯) -->
                  <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSearch" aria-controls="offcanvasSearch">
                    <i class="fas fa-filter"></i> 進階搜尋
                  </button>
                </div>
                <!-- /.card-header -->

                <div class="card-body">
                  <table id="dataTable_xxxxlist" class="table table-bordered table-striped table-hover w-100" data-pagemax="25" data-dirto="xx_xxxx.php" data-search but-addnew>
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
                      <?php
                        $listSql = "SELECT * FROM fsl_administrator";
                        $listSql .= " ORDER BY ad_id DESC LIMIT {$maxResult}";
                        $sr = $DB->query($listSql);
                        $i = 0;
                        while($i<count($sr)){ 
                        ?>
                            <tr style="cursor:pointer;" data-currid="<?php echo $sr[$i]['xxx_id']; ?>">
                            <td><?php echo $sr[$i]['xxx_name']; ?></td>
                            <td><?php echo $sr[$i]['xxx_nickname']; ?></td>
                            <td><?php 
                              switch ($sr[$i]['xxx_authlevel']) {
                                case 1: echo "資料查詢人員"; break;
                                case 2: echo "客戶服務人員"; break;
                                case 3: echo "一般操作者"; break;
                                case 4: echo "初階管理者"; break;
                                case 5: echo "高級管理者"; break;
                                case 8: echo "工程技術人員"; break;
                                case 9: echo "最高管理者"; break;
                                default: echo "未定權限"; break;
                              }
                            ?></td>
                            <td><?php echo $sr[$i]['xxx_published']; ?></td>
                            <td><?php echo $sr[$i]['xxx_onboard']; ?></td>
                            </tr>
                      <?php 
                          $i++;
                          } 
                      ?>
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
      <!-- /主要內容區域 -->
    </main>
    <!-- /頁內全頁內容 -->

    <!-- [變更6] 右側功能控制區：AdminLTE 4 移除 control-sidebar，改用 Bootstrap 5 原生 Offcanvas -->
    <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasSearch" aria-labelledby="offcanvasSearchLabel">
      <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="fas fa-search me-2"></i>進階條件搜尋</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body d-flex flex-column p-2">
        <form id="form_search" name="form_search" action="" method="get" class="d-flex flex-column h-100">
          <!-- 上格 (可捲動搜尋表單區) -->
          <div class="flex-grow-1 overflow-auto p-2">
            <p>上格內容（置頂）</p>
            <!-- 範例：BS5 表單寫法 -->
            <div class="mb-3">
              <label for="searchText" class="form-label">關鍵字搜尋</label>
              <input type="text" class="form-control form-control-sm" id="searchText" name="searchText" value="<?php echo htmlspecialchars($searchText); ?>">
            </div>
          </div>
          
          <!-- 按鈕欄：[變更7] 移除 .form-group 與 .btn-block，改用 BS5 Grid (g-1) 與 d-grid -->
          <div class="row g-1 pt-2 border-top border-secondary" style="height:56px;">
            <div class="col-7 d-grid">
              <button type="submit" class="btn btn-info notSubmit" id="searchSend" name="searchSend">
                <i class="fa-solid fa-magnifying-glass"></i> 執行搜尋
              </button>
            </div>
            <div class="col-3 d-grid">
              <button type="button" class="btn btn-secondary" id="searchReset" name="searchReset">清除</button>
            </div>
            <div class="col-2 d-grid">
              <!-- [變更8] 改用 data-bs-dismiss="offcanvas" 關閉側欄 -->
              <button type="button" class="btn btn-warning" id="sidebarControl" name="sidebarControl" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="fa-solid fa-right-from-bracket"></i>
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
<script src="../pluginFiles/jquery/jquery.min.js"></script>
<!-- Bootstrap 5 (必須在 AdminLTE 之前引入，且不依賴 jQuery) -->
<script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
<!-- DataTables 3 & Plugins -->
<script src="../pluginFiles/datatables3/datatables.min.js"></script>
<script src="../pluginFiles/jszip/jszip.min.js"></script>
<script src="../pluginFiles/pdfmake/pdfmake.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.html5.min.js"></script>
<!-- 5SeasonLife App (請確認 5sladmin.js 已更新為 AdminLTE 4 核心) -->
<script src="../scriptFiles/5sladmin.js"></script>
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>
<!-- ------------------------ /Scripts Link ------------------------ -->
<script type="text/javascript">
////////////////////////////////////////////////////////////////
// ---------------- JAVASCRIPTS 全域調用參數設置 --------------- //
////////////////////////////////////////////////////////////////
var xxxlistTB;
////////////////////////////////////////////////////////////////
// -------------- JAVASCRIPTS FUNCTIONs 全域調用 -------------- //
// -------------- 可接收include檔案要求後自定義動作 -------------- //
////////////////////////////////////////////////////////////////
// 當「新增」按鈕按下時的動作
function doButAdd(){
  window.location.replace('xxxxxxxx.php?currID=0');
}
// 當「重置」按鈕按下時的動作
function doButReset(){
  location.reload(true);
}
////////////////////////////////////////////////////////////////
// ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
// ---------------------------------------------------------- //
////////////////////////////////////////////////////////////////
  $(document).ready(function() {
    //* iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });
    //---------------------------------------------
    xxxlistTB = datatableCreate('dataTable_xxxxlist');
    //搜尋表格重置清除
    $('#searchReset').click(function() {
      $('#form_search').find('input').val('');
      $('#form_search').find('select').val('');
      //$("#searchDist,#searchCOexpert").find("option:gt(0)").remove();
      //$("#searchCOexpert").find("optgroup").remove();
      //$('#form_search input[type="checkbox"]').prop('checked', false);
    });

    //-------- 加載完成後更改外框主頁中樹狀目錄開合狀態 --------//
    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  //////////////////////////////////////////////////////////////////
  });

</script>
</body>
</html>
