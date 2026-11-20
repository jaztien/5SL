<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_APRR');
define('PAGE_ITEM','ITEM_APRRET');
define('PAGE_OPANEL',1);//1-新增；2-編輯；3-上傳；4-編輯/刪除；5-[空]。
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
//架構URI鏈結內容傳遞值字串
$pageMax = isset($_REQUEST['pageMax']) ? intval($_REQUEST['pageMax']) : 25;
$currPage = isset($_REQUEST['currPage']) ? intval($_REQUEST['currPage']) : 0;
$jumpTo = ($pageMax*$currPage);
//上述的條件，在按下目標頁面同時，會自動併入於$searchURI 之前
$currOrder = isset($_REQUEST['currOrder']) ? $_REQUEST['currOrder'] : '1,asc';
$listSortorder = explode(",", $currOrder);
//功能陣列建立，取自JSON檔案
$merchJS=_fn_getCachedJsonData("../jsonFiles/merchandise.json");
$locJS=_fn_getCachedJsonData("../jsonFiles/locations.json");

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../../imageFiles/favicon.ico" type="image/ico" />
	
  <title> Fifth Season Life 人生第五季 | 實價登錄資料紀錄 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-bs4/css/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-responsive/css/responsive.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-buttons/css/buttons.bootstrap4.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css">
<!-- -------------------------- /CSS Link -------------------------- -->
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <input type="text" id="searchURI" name="searchURI" style="width:1px;height:1px;opacity:0;" value="<?php echo $searchURI; ?>">
  <!-- 全頁框 -->
  <div class="wrapper">
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="../imageFiles/preloaderLOGO.png" alt="Fifth Season Life LOGO" height="60" width="60"> 
    </div>
    <!-- /Preloader -->
    <!-- 上方訊息區 -->
    <?php //include_once('include_navbar.php'); ?>
    <!-- /上方訊息區 -->
    <!-- 左側方導覽區 -->
    <?php //include_once('include_sidebarL.php'); ?>
    <!-- /左側方導覽區 -->
    <!-- 頁內全頁內容 -->
    <div class="content-wrapper">
      <!-- 頁頭導覽條 -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h4 class="m-0">實價登錄資料紀錄</h4>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">實價登錄資料管理</li>
                <li class="breadcrumb-item active">實價登錄資料紀錄</li>
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </section>
      <!-- /頁頭導覽條 -->
      <!-- 主要內容區域 ------------------------------------- -->
      <div class="card">
          <div class="card-header">
            &nbsp;&nbsp;<h6 class="card-title" style="font-size: 1.2em;"><?php echo (empty($searchURI)) ? "最新{$maxResult}筆實價登錄資料" : "依據搜尋結果表列最近{$maxResult}筆紀錄。"; ?>
            <i class="nav-icon fas fa fa-exclamation-circle" style="font-size: 0.75em;font-weight:light;color:#B8BFCB;"> 搜尋上限預設為<?php echo $maxResult; ?>筆，請縮小搜尋範圍以減少系統負載。若要回復原始表列內容，請開啟進階搜尋側欄清除搜尋條件。</i>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <table id="dataTable_aprrlist" class="table table-bordered table-striped table-hover" data-orderby="<?php echo $listSortorder[0]; ?>" data-sortorder="<?php echo $listSortorder[1]; ?>" data-pagemax="<?php echo $pageMax; ?>" data-jumpto="<?php echo $jumpTo; ?>" data-dirto="merch_aprrinfo.php" data-search but-advsearch but-addnew="新增實價登錄紀錄">
              <thead>
                <tr class="green">
                  <th>ID</th>
                  <th>地區碼</th>
                  <th>住址</th>
                  <th>交易日期</th>
                  <th>地坪</th>
                  <th>售價</th>
                  <th>所屬建案</th>
                </tr>
              </thead>
              <tbody>
                <?php

                  $listSql = "SELECT * FROM aprret";
                  if(!empty($searchURI)){ $listSql .= " WHERE 1=1"; }

                  $listSql .= " ORDER BY ret_id DESC LIMIT {$maxResult}";
                  $sr = $DB->query($listSql);
                  //$DB->closeConnection();
                  $i = 0; //以$i為$nameList這個陣列的索引值
                  while($i<count($sr)){ 
                  ?>
                    <tr style="cursor:pointer;" data-currid="<?php echo $sr[$i]['ret_id']; ?>">
                      <td><?php echo $sr[$i]['ret_id']; ?></td>
                      <td><?php echo $sr[$i]['ret_district']; ?></td>
                      <td><?php echo $sr[$i]['ret_addr']; ?></td>
                      <td><?php echo $sr[$i]['ret_tdate']; ?></td>
                      <td><?php echo $sr[$i]['ret_totalarea']; ?></td>
                      <td><?php echo number_format($sr[$i]['ret_total']); ?>萬</td>
                      <td><?php echo $sr[$i]['comm_id']; ?></td>
                    </tr>
                <?php 
                    $i++;
                    } 
                ?>
              </tbody>
              <tfoot>
                <tr class="green">
                  <th>ID</th>
                  <th>地區碼</th>
                  <th>住址</th>
                  <th>交易日期</th>
                  <th>地坪</th>
                  <th>售價</th>
                  <th>所屬建案</th>
                </tr>
              </tfoot>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
    
      <!-- /主要內容區域 ------------------------------------ -->
    </div>
    <!-- /頁內全頁內容 -->

    <!-- jQuery -->
    <script src="../pluginFiles/jquery/jquery.min.js"></script>

    <!-- 下方訊息區 -->
    <?php //include_once('include_footer.php'); ?>
    <!-- /下方訊息區 -->
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







<!-- 
        <div class="p-0 control-sidebar-content">
          <i class="fas fa-external-link-square-alt" id="collSearch" name="collSearch" data-widget="control-sidebar" data-controlsidebar-slide="true" alt="收起" title="收起"></i>
          <form method="post" action="mem_mblist.php?currCATE=<?php echo $currCATE; ?>">

            <div class="card-body" style="font-size:0.8rem">
              <h5>進階搜尋</h5>
              <strong>顯示名稱</strong>
              <div class="input-group mb-3">
                <input id="advs_search" name="advs_search" type="text" class="form-control" value="" placeholder="輸入文字">
              </div>
              <strong>個人或組織</strong>
              <div class="form-group clearfix">
                <div class="icheck-info d-inline">
                  <input type="checkbox" id="merch_org[0]" name="merch_org[0]">
                  <label for="merch_org[0]">個人</label>
                </div>
                <div class="icheck-info d-inline">
                  <input type="checkbox" id="merch_org[1]" name="merch_org[1]">
                  <label for="merch_org[1]">組織</label>
                </div>
              </div>
              <strong>會員分類</strong>
              <div class="form-group clearfix">
                <div class="icheck-info d-inline">
                  <input type="checkbox" id="merch_cate[1]" name="merch_cate[1]">
                  <label for="merch_cate[1]">買售方</label>
                </div>
                <div class="icheck-info d-inline">
                  <input type="checkbox" id="merch_cate[2]" name="merch_cate[2]">
                  <label for="merch_cate[2]">合作方</label>
                </div>
                <div class="icheck-info d-inline">
                  <input type="checkbox" id="merch_cate[3]" name="merch_cate[3]">
                  <label for="merch_cate[3]">消費方</label>
                </div>
              </div>
              <strong>專業服務項目</strong>
              <div class="form-group clearfix">
                <div class="icheck-info d-inline">
                  <input type="checkbox" id="merch_specialty[1]" name="merch_specialty[1]">
                  <label for="merch_specialty[1]">項目A</label>
                </div>
                <div class="icheck-info d-inline">
                  <input type="checkbox" id="merch_specialty[2]" name="merch_specialty[2]">
                  <label for="merch_specialty[2]">項目B</label>
                </div>
                <div class="icheck-info d-inline">
                  <input type="checkbox" id="merch_specialty[3]" name="merch_specialty[3]">
                  <label for="merch_specialty[3]">項目C</label>
                </div>
              </div>
              <strong>住址/專責區域</strong>
              <div class="form-group clearfix">
                <select class="custom-select rounded-0" id="advs_city" name="advs_city">
                    <option>-選擇城市-</option>
                    <option>台北市</option>
                    <option>新北市</option>
                  </select>
                  <select class="custom-select rounded-0" id="advs_dist" name="advs_dist">
                    <option>--</option>
                  </select>
                  <input id="advs_add" name="advs_add" type="text" class="form-control" value="" placeholder="住址">
              </div>
              <strong>專責社區</strong>
              <div class="form-group clearfix">
                <select class="custom-select rounded-0" id="advs_comm" name="advs_comm">
                    <option>--</option>
                  </select>
              </div>

              <hr class="mb-2"/>
              <button type="button" id="upAdvbut" name="upAdvbut" class="btn btn-block btn-outline-info">進階搜尋</button>
            </div>



          </form>
        </div>
-->

        <!-- /功能區內容 ----------------------------------- -->
      </aside>
    <!-- /右側功能控制區 -->
  </div>
  <!-- /全頁框 -->
<!-- ------------------------ Scripts Link ------------------------ -->
<!-- jQuery is in front of footer file. -->
<!-- Bootstrap 4 -->
<script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables  & Plugins -->
<script src="../pluginFiles/datatables/datatables/jquery.dataTables.min.js"></script>
<script src="../pluginFiles/datatables/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>
<!-- ------------------------ /Scripts Link ------------------------ -->
<script>
  $(function () {
    //當未經過 TreeView 轉跳頁面時，改變外層 TreeView 結構與之相關(必須要載入 page.basic.js 才會執行)
    treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>');
    //* iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });
    //-----------------------------------------
    var aprrlistTB = datatableCreate('dataTable_aprrlist');
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
// 當「儲存」按鈕按下時的動作
function doButAdd(){
  window.location.replace('merch_aprrinfo.php?currID=0&pageMax=<?php echo $pageMax;?>&currPage=<?php echo $currPage;?>&currOrder=<?php echo $currOrder;?>&<?php echo $searchURI;?>');
}
// 當「重置」按鈕按下時的動作
function doButReset(){
  if (!confirm('重整頁面將會強制自伺服器重新載入這個頁面，確定執行這個操作？！')) {
    return false;
  }
  location.reload(true);
}

</script>
</body>
</html>
