<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_SYSLOGS');
define('PAGE_ITEM','ITEM_SYS');
define('PAGE_OPANEL',5);//1-新增；2-編輯；3-上傳；4-編輯/刪除。
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//
$searchText = isset($_REQUEST['searchText']) ? rawurldecode($_REQUEST['searchText']) : ''; 
$searchType = isset($_REQUEST['searchType']) ? $_REQUEST['searchType'] : '';
$searchDateFrom = isset($_REQUEST['searchDateFrom']) ? $_REQUEST['searchDateFrom'] : '';
$searchDateTo = isset($_REQUEST['searchDateTo']) ? $_REQUEST['searchDateTo'] : '';
$searchCode = isset($_REQUEST['searchCode']) ? $_REQUEST['searchCode'] : '';
$searchBy = isset($_REQUEST['searchBy']) ? $_REQUEST['searchBy'] : '';
//$searchFrom = isset($_REQUEST['searchFrom']) ? $_REQUEST['searchFrom'] : '';
//
$searchURI = "";
//
if(!empty($searchText)){ $searchURI .= "&searchText=".$searchText; }
if(!empty($searchType)){ $searchURI .= "&searchType=".$searchType; }
if(!empty($searchDateFrom)){ $searchURI .= "&searchDateFrom=".$searchDateFrom; }
if(!empty($searchDateTo)){ $searchURI .= "&searchDateTo=".$searchDateTo; }
if(!empty($searchCode)){ $searchURI .= "&searchCode=".$searchCode; }
if(!empty($searchBy)){ $searchURI .= "&searchBy=".$searchBy; }
//if(!empty($searchFrom)){ $searchURI .= "&searchFrom=".$searchFrom; }
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}
//定義條件搜尋之前以及之後不同的顯示筆數
$maxResult = (!empty($searchURI)) ? 200 : 50;
$currOrder = isset($_REQUEST['currOrder']) ? $_REQUEST['currOrder'] : '0,asc';

//
$exSearchSql = '';
$topMaxNum = true;
if(!empty($searchType)){ $exSearchSql .= " AND lg_for=".$searchType; $topMaxNum = false; }
if(!empty($searchDateFrom) || !empty($searchDateTo)){
  //$dFrom = !empty($searchDateFrom) ? strtotime($searchDateFrom) : '';
  $dFrom = $searchDateFrom ?? '';
  //$dTo = !empty($searchDateTo) ? strtotime($searchDateTo) : '';
  $dTo = $searchDateTo ?? '';
  if(!empty($searchDateFrom)) { $exSearchSql .= " AND lg_created>=".$searchDateFrom; $topMaxNum = false; }
  if(!empty($searchDateTo)) { $exSearchSql .= " AND lg_created<=".$searchDateTo; $topMaxNum = false; }
}
if(!empty($searchCode)){ $exSearchSql .= " AND lg_code=".$searchCode; $topMaxNum = false; }
if(!empty($searchBy)){ $exSearchSql .= " AND lg_forid=".$searchBy; $topMaxNum = false; }
//if(!empty($searchFrom)){ $exSearchSql .= " AND lg_from=".$searchFrom; $topMaxNum = false; }

//功能陣列建立，取自JSON檔案
$logJS=_fn_getCachedJsonData("../jsonFiles/logs.json");
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
	
  <title> Fifth Season Life 人生第五季 | 操作記錄總表｜全系統 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
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
<style>
  table.dataTable tbody td, 
  table.dataTable theorems th {
      padding: 4px 4px; /* 上下內邊距 15px，左右內邊距 10px */
  }
</style>
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
            <div class="col-sm-7">
              <h4 class="m-0">全系統操作記錄</h4>
            </div>
            <div class="col-sm-5">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">系統設定管理</li>
                <li class="breadcrumb-item active">全系統操作記錄</li>
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
                  &nbsp;&nbsp;<h6 class="card-title" style="font-size: 1.2em;"><?php echo (empty($searchURI)) ? "最新{$maxResult}筆全系統操作記錄資料" : "依據搜尋結果表列最近{$maxResult}筆紀錄。"; ?>
                  <i class="nav-icon fas fa fa-exclamation-circle" style="font-size: 0.75em;font-weight:light;color:#B8BFCB;"> 全系統操作記錄紀錄著本站所有的重要的操作動作，以為系統分析以及責任歸屬作用。搜尋上限預設為<?php echo $maxResult; ?>筆，請縮小搜尋範圍以減少系統負載。若要回復原始表列內容，請開啟進階搜尋側欄清除搜尋條件。</i>
                </div>
                <div class="card-body">
                  <table id="dataTable_lolist" class="table table-bordered table-striped table-hover" data-pagemax="25" data-search but-excel but-pdf but-advsearch>
                    <thead>
                      <tr>
                        <th>操作時間</th>
                        <th>種類</th>
                        <th>操作者</th>
                        <th>代碼</th>
                        <th>操作內容</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $topMax = '';
                        if($topMaxNum){ $topMax = " LIMIT 200"; }
                        $sql = "SELECT * FROM data_logs WHERE 1=1";
                        $sql .= $exSearchSql;
                        $sql .= " ORDER BY lg_created DESC{$topMax}";
                        $sr = DB->query($sql ,array());
                        $i = 0; //以$i為$nameList這個陣列的索引值
                        while($i<count($sr)){ 
                        ?>
                          <tr style="cursor:pointer;" data-currid="<?php echo $sr[$i]['lg_id']; ?>">
                            <td><?php
                            //echo date('Y-m-d H:i:s',$sr[$i]['lg_created']);
                            echo $sr[$i]['lg_created'];
                            ?></td>
                            <td><?php echo $logJS['lgtype'][$sr[$i]['lg_for']]??'&nbsp;'; ?></td>
                            <td><?php
                              if($sr[$i]['lg_for']==1){
                                  $adnick = DB->single("SELECT ad_title FROM fsl_administrator WHERE ad_id=?",array($sr[$i]['lg_forid']));
                                  echo $adnick ?: '&nbsp;';
                              }elseif($sr[$i]['lg_for']==2){
                                  $mbnick = DB->single("SELECT mb_nickname FROM fsl_members WHERE mb_id=?",array($sr[$i]['lg_forid']));
                                  echo $mbnick ?: '&nbsp;';
                              }else{
                                  echo '系統';
                              }
                            ?></td>
                            <td style="font-size:1.2em;font-weight:bold;"><?php echo $sr[$i]['lg_code']; ?></td>
                            <td><?php
                                $oStr = DB->single("SELECT lg_string FROM sys_logstrings WHERE lg_code=?",array($sr[$i]['lg_code']));
                                echo _fn_getLogString($sr[$i]['lg_params'],$oStr);
                            ?></td>
                          </tr>
                        <?php 
                          $i++;
                          } 
                          //DB->closeConnection();
                        ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>操作時間</th>
                        <th>種類</th>
                        <th>操作者</th>
                        <th>代碼</th>
                        <th>操作內容</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
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
    <!-- /.content -->
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
              <div>綜合搜尋</div>
              <div class="form-group"><input type="text" class="form-control" id="searchText" name="searchText" value="<?php echo $searchText; ?>" placeholder="請輸入搜尋條件" autocomplete="off"></div>
              <div>操作種類</div>
              <div>
                <select class="form-control" id="searchType" name="searchType" data-placeholder="-請選擇操作種類-">
                  <option value="">-請選擇操作種類-</option>
                  <?php
                    if (isset($logJS['lgtype']) && is_array($logJS['lgtype'])) {
                        foreach ($logJS['lgtype'] as $key => $value) {
                            if ($key == "") {
                                continue;
                            }
                            echo '<option value="'.$key.'"';
                            if (@$searchType == $key) {
                                echo " selected";
                            }
                            echo '>'.$value.'</option>';
                        }
                    }
                  ?>
                </select>
              </div>
              <div>自操作日期</div>
              <div>
                <div class="input-group">
                  <div class="input-group-prepend">
                      <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                  </div>
                  <input type="text" class="form-control"  id="searchDateFrom" name="searchDateFrom" value="<?php echo $searchDateFrom; ?>" data-inputmask-alias="datetime" data-inputmask-inputformat="yyyy-mm-dd" data-mask>
                </div>
              </div>  
              <div>至操作日期</div>
              <div>
                <div class="input-group">
                  <div class="input-group-prepend">
                      <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                  </div>
                  <input type="text" class="form-control"  id="searchDateTo" name="searchDateTo" value="<?php echo $searchDateTo; ?>" data-inputmask-alias="datetime" data-inputmask-inputformat="yyyy-mm-dd" data-mask>
                </div>
              </div>
              <div>代碼搜尋</div>
              <div>
                <input type="text" class="form-control"  id="searchCode" name="searchCode" value="<?php echo $searchCode; ?>">
              </div>
              <div>操作者搜尋</div>
              <div>
                <input type="text" class="form-control"  id="searchBy" name="searchBy" value="<?php echo $searchBy; ?>">
              </div>
              <!-- 
              <div>來源IP</div>
              <div>
                <input type="text" class="form-control"  id="searchFrom" name="searchFrom" value="<?php echo $searchFrom; ?>">
              </div>
              -->
              <div>每頁筆數</div>
              <div>
                <input type="text" class="form-control"  id="pageMax" name="pageMax" value="<?php echo $pageMax; ?>">
              </div>
              <div></div>
              <div></div>
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
    <script src="../pluginFiles/datatables/datatables-buttons/js/buttons.html5.min.js"></script>
  -->
  <!-- DataTables3 & Plugins -->
  <script src="../pluginFiles/datatables3/datatables.min.js"></script>
  <!-- Toastr
  <script src="../pluginFiles/toastr/toastr.min.js"></script> -->
  <!-- InputMask 日期輸入元件JS -->
  <script src="../pluginFiles/moment/moment.min.js"></script>
  <script src="../pluginFiles/inputmask/jquery.inputmask.min.js"></script>
  <!-- 5SeasonLife App -->
  <script src="../scriptFiles/5sladmin.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>
  <!-- ------------------------ /Scripts Link ------------------------ -->
  <script>
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
    //-----------------------------------------
    var lolistTB = datatableCreate('dataTable_lolist');
    //-----------------------------------------
    $('#datemask2').inputmask('yyyy-mm-dd', { 'placeholder': 'yyyy-mm-dd' })
    $('[data-mask]').inputmask();
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
  // 當「重置」按鈕按下時的動作
  function doButReset(){
    location.reload(true);
  }
</script>
  
</body>
</html>
