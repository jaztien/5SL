<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
////////////////////////////////////////////////////////////
$searchPart = isset($_REQUEST['searchPart']) ? $_REQUEST['searchPart'] : '0';//0
$searchCity = isset($_REQUEST['searchCity']) ? $_REQUEST['searchCity'] : '11';//11
$searchDist = isset($_REQUEST['searchDist']) ? $_REQUEST['searchDist'] : '1101';//1101
$searchText = isset($_REQUEST['searchText']) ? rawurldecode($_REQUEST['searchText']) : ''; 
$searchMode = isset($_REQUEST['searchMode']) ? $_REQUEST['searchMode'] : '';
$searchMDamountS = isset($_REQUEST['searchMDamountS']) ? $_REQUEST['searchMDamountS'] : '';
$searchMDamountE = isset($_REQUEST['searchMDamountE']) ? $_REQUEST['searchMDamountE'] : '';
$searchGeography = isset($_REQUEST['searchGeography']) ? $_REQUEST['searchGeography'] : '';
$searchCheck = isset($_REQUEST['searchCheck']) ? $_REQUEST['searchCheck'] : '';
$searchStatus = isset($_REQUEST['searchStatus']) ? $_REQUEST['searchStatus'] : '';
//
$searchURI = "";
//
$selectMode = true;
if($searchText!='' || $searchMode!='' || ($searchMDamountS!='' && $searchMDamountE!='') || $searchGeography!='' || $searchCheck!='' || $searchStatus!=''){
  $selectMode = false;
  //$searchCity = '';
  //$searchDist = '';
} 
if(!empty($searchPart)){ $searchURI .= "&searchPart=".$searchPart; }
if(!empty($searchCity)){ $searchURI .= "&searchCity=".$searchCity; }
if(!empty($searchDist)){ $searchURI .= "&searchDist=".$searchDist; }
if(!empty($searchText)){ $searchURI .= "&searchText=".$searchText; }
if(!empty($searchMode)){ $searchURI .= "&searchMode=".$searchMode; }
if(!empty($searchMDamountS)){ $searchURI .= "&searchMDamountS=".$searchMDamountS; }
if(!empty($searchMDamountE)){ $searchURI .= "&searchMDamountE=".$searchMDamountE; }
if(!empty($searchGeography)){ $searchURI .= "&searchGeography=".$searchGeography; }
if(!empty($searchCheck)){ $searchURI .= "&searchCheck=".$searchCheck; }
if(!empty($searchStatus)){ $searchURI .= "&searchStatus=".$searchStatus; }
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}
//定義條件搜尋之前以及之後不同的顯示筆數
$maxResult = (!empty($searchURI)) ? 1000 : 1000;
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_BCASE');
define('PAGE_ITEM','ITEM_TOOLS');
define('PAGE_ITEM2','ITEM_DATAS');
define('PAGE_OPANEL',1);//1-新增；2-編輯；3-上傳；4-編輯/刪除；；9-[空]。
define('SEARCH_OPANEL',$selectMode?1:2);
define('EXPORT_OPANEL',3);//1-Excel；2-PDF；3-Excel+Print；
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//架構URI鏈結內容傳遞值字串
$pageMax = isset($_REQUEST['pageMax']) ? intval($_REQUEST['pageMax']) : 25;
$currPage = isset($_REQUEST['currPage']) ? intval($_REQUEST['currPage']) : 0;
$jumpTo = ($pageMax*$currPage);
//$jumpData = isset($_REQUEST['jumpData']) ? intval($_REQUEST['jumpData']) : 1;
//$jumpTo = floor($jumpData / $pageMax);
//$jumpTo = $jumpData%$pageMax==0 ? ($jumpTo - 1)*$pageMax : $jumpTo*$pageMax;
//上述的條件，在按下目標頁面同時，會自動併入於$searchURI 之前
$currOrder = isset($_REQUEST['currOrder']) ? $_REQUEST['currOrder'] : '1,asc';
$listSortorder = explode(",", $currOrder);
//功能陣列建立，取自JSON檔案
$locJS=_fn_getCachedJsonData("../jsonFiles/locations.json");
$partJS=_fn_getCachedJsonData("../jsonFiles/twparts.json");//若要給JAVA使用，必須去除 whitespace
$topARR = $partJS;
$bcJS=_fn_getCachedJsonData("../jsonFiles/buildcase.json");
//------- 自動辨識首位城市，以及該城市下的首位區劃---------
if ($selectMode && !in_array($searchCity, $topARR[$searchPart]['city'])){
    $searchCity = $topARR[$searchPart]['city'][0];
    $searchDist = $searchCity.'01';
}
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
	
  <title> Fifth Season Life 人生第五季 | 社區建案資料表 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <link href="../pluginFiles/font-gis/css/font-gis.css" rel="stylesheet" />
  <!-- DataTables -->
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-bs4/css/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-responsive/css/responsive.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-buttons/css/buttons.bootstrap4.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css">
  <!-- -------------------------- /CSS Link -------------------------- -->
  <style>
    i[data-bs-toggle="tooltip"]{
      color: #5F6E86;
      cursor: help;
    }
    i[data-bs-toggle="tooltip"]:hover{
      color: #236FB7;/*2C333E*/ 
    }
    table.dataTable tbody td, 
    table.dataTable theorems th {
        padding: 8px 4px; /* 上下內邊距 4px，左右內邊距 4px */
    }
    .dataTables_filter {
      display: none !important;
    }
    #form_search >div div{
      margin-bottom: 4px;
    }
    .dt-buttons {
        display: none !important;
    }
    #dataSearch {
      background-color: #5F6E86;
      color: #ffffff;
      height: 54px;
    }
  </style>
</head>

<!-- ----- nXXXXXX ---------------------------------------------- -->
<!-- ----- layout-navbar-fixed layout-footer-fixed 上下導覽條固定不動 ------------------ -->
<!-- ----- layout-fixed 左側導覽固定不動（但上下捲動時LOGO固定不動，畫面不佳） -------------- -->
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <!-- 全頁框 -->
  <div class="wrapper">
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="../imageFiles/preloaderLOGO.png" alt="Fifth Season Life LOGO" height="60" width="60"> 
    </div>
    <!-- /Preloader -->
    <!-- 頁內全頁內容 -->
    <div class="content-wrapper">
      <input type="text" id="searchURI" name="searchURI" style="width:1px;height:1px;opacity:0;" value="<?php echo $searchURI; ?>">
      <!-- 頁頭導覽條 -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-7">
              <h4 class="m-0">
                社區建案資料表
                <i class="fa-solid fa-circle-question text-md" data-bs-toggle="tooltip" data-bs-placement="top" title="xxxx"></i>
                <span class="text-muted text-sm" style="margin-left:6px;">data_bclist.php</span>
              </h4>
            </div>
            <div class="col-sm-5">
                <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">基礎數據&管理工具</li>
                <li class="breadcrumb-item">基礎結構數據</li>
                <li class="breadcrumb-item active">社區建案資料列表</li>
                </ol>
            </div>
          </div>
        </div>
      </section>
      <!-- /頁頭導覽條 -->
      <!-- 主要內容區域 ------------------------------------- -->
      <section class="content">
        <div class="container-fluid">
        <?php if($selectMode){//當為地區選擇模式時，隱藏地區選項按鈕 ?>
          <div class="row pb-2" style="margin-top:-16px;">
            <?php foreach($topARR as $key => $value){//自JSON檔案取得台灣分區，並產生按鈕 ?>
            <div class="col-2">
              <button type="button" class="btn btn-block btn-outline-info<?php echo ($searchPart == $key) ? ' active' : ''; ?>" onClick="<?php echo ($searchPart != $key) ? "location.href = '?searchPart=".$key."';" : "return false;"; ?>"><?php echo $topARR[$key]['text']; ?></button>
            </div>
            <?php } ?>
          </div>
          <div class="card card-info card-tabs">
              <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                  <?php 
                  $ti=0;
                  $loc_b = $topARR[$searchPart]['city'];
                  foreach($loc_b as $key => $value){//每個台灣分區下的縣市
                  ?>
                  <li class="nav-item"><a class="nav-link<?php echo ($value == $searchCity) ? ' active' : ''; ?>" href="?searchPart=<?php echo $searchPart; ?>&searchCity=<?php echo $value; ?>&searchDist=<?php echo $value."01"; ?>"><?php echo $locJS[$value]['text']; ?></a></li>
                  <?php } ?>
                </ul>
              </div>
              <!-- /.card-header -->
              <div class="row pb-2" style="padding-left:20px;padding-right:20px;">
                <!-- 分頁訊息傳遞 -->
                <?php
                  foreach($locJS[$searchCity]['district'] as $key => $value){//每個縣市下的行政區劃
                ?>
                <div class="col-auto" style="padding-top:4px;">
                  <button type="button" class="btn btn-block btn-outline-primary text-sm<?php echo ($searchDist==$key) ? ' active' : ''; ?>" style="padding:2px 6px;" onClick="<?php echo ($searchDist!=$key) ? "location.href = '?searchPart=".$searchPart."&searchCity=".$searchCity."&searchDist=".$key."';" : "return false;"; ?>"><?php echo $value; ?></button>
                </div>
                <?php } ?>
              </div>
        <?php } else { ?>

          <div class="row">
            <div class="col-12">
              <!-- 主要內容區域 ------------------------------------- -->
              <div class="card">
                <div class="card-header">
                    <?php if(!empty($searchURI)){ ?>
                    <span class="right badge badge-warning" style="position:absolute; top: -6px;">條件搜尋中</span>
                    <?php } ?>
                    <div class="card-title" style="font-size: 1.1em;">
                      <?php echo (!empty($searchURI)) ? "依條件搜尋共" : '最新'; ?> <span id="resultCount"></span> 筆紀錄
                      <i class="nav-icon fas fa fa-exclamation-circle" style="font-size:0.75em;font-weight:light;color:#5F6E86;"> 搜尋上限預設為<?php echo $maxResult; ?>筆，請縮小搜尋範圍以減少系統負載。若要回復原始表列內容，請開啟進階搜尋側欄清除搜尋條件。</i>
                    </div>
                </div>
                <!-- /.card-header -->
        <?php } ?>
              <div class="card-body">
                  <div class="tab-content">
                    <div class="tab-pane active">
                      <table id="dataTable_bcList" class="table table-bordered table-striped table-hover" data-orderby="<?php echo $listSortorder[0]; ?>" data-sortorder="<?php echo $listSortorder[1]; ?>" data-butClass="cyan" data-pagemax="<?php echo $pageMax; ?>" data-jumpto="<?php echo $jumpTo; ?>" data-dirto="data_bcedit.php" but-excel but-print="人生第五季社區建案資料紀錄" data-search>
                        <thead>
                          <tr class="cyan">
                            <th>狀態</th>
                            <th>ID</th>
                            <th>地區</th>
                            <th>建案名稱</th>
                            <th>期數</th>
                            <th>建案別名</th>
                            <th>住址</th>
                            <th>型態</th>
                            <th>座標</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                            $i=0;
                            $listSql = "SELECT *, bc_coordinates.STGeometryType() AS coordType, COUNT(*) OVER() 'totalNum' FROM buildcase";// AND bc_coordinates.STGeometryType() = 'MultiPolygon'
                            $listSql .= " WHERE 1=1";
                            if(!empty($searchCity)) { $listSql .= " AND bc_city='".$searchCity."'"; }
                            if(!empty($searchDist)) { $listSql .= " AND bc_district='".$searchDist."'"; }
                            if($searchText != '') {
                              $listSql .= " AND (";
                              $listSql .= "bc_title LIKE '%".$searchText."%'";
                              $listSql .= " OR bc_subtitle LIKE '%".$searchText."%'";
                              $listSql .= " OR bc_subtitle2 LIKE '%".$searchText."%'";
                              $listSql .= " OR bc_addr LIKE '%".$searchText."%'";
                              $listSql .= " OR bc_investor LIKE '%".$searchText."%'";
                              $listSql .= " OR bc_constructer LIKE '%".$searchText."%'";
                              $listSql .= ")";
                            
                            }
                            if($searchMode != '') { $listSql .= " AND bc_mode='".$searchMode."'"; }
                            if($searchGeography != '') {
                              if($searchGeography == '0') { $listSql .= " AND bc_coordinates IS NULL"; }
                              else if($searchGeography == '1'){ $listSql .= " AND bc_coordinates IS NOT NULL"; }
                            }
                            if($searchCheck != '') { $listSql .= " AND bc_check='".$searchCheck."'"; }
                            //if($searchStatus != '') { $listSql .= " AND bc_status='".$searchStatus."'"; }
                            //else { $listSql .= " AND bc_status > 0"; }
                            $listSql .= " AND bc_status > 0";
                            //if(!empty($searchAddr)) { $listSql .= " AND bc_addr LIKE '%".$searchAddr."%'"; }
                            //if(!empty($searchComp)) { $listSql .= " AND (bc_investor LIKE '%".$searchComp."%' OR bc_constructer LIKE '%".$searchComp."%')"; }
                            $listSql .= " ORDER BY bc_district,bc_title ASC LIMIT {$maxResult}";
                            $sr = $DB->query($listSql,array(0));
                            $totalNum = intval(@$sr[0]['totalNum']);
                            //$sr = $DB->query("SELECT * FROM buildcase WHERE bc_city=? AND bc_district=? ORDER BY bc_district,bc_title ASC", array($searchCity,$searchDist));
                            while($i<count($sr)){
                                $localText = $locJS[$sr[$i]['bc_city']]['text'].$locJS[$sr[$i]['bc_city']]['district'][$sr[$i]['bc_district']];
                          ?>
                          <tr style="cursor:pointer;" data-currid="<?php echo $sr[$i]['bc_id']; ?>"<?php if($sr[$i]['bc_status'] >= 8){ echo ' class="text-muted"'; } ?>><!--  -->
                            <td>
                              <span class="right badge<?php echo $bcJS['badge'][$sr[$i]['bc_status']]; ?>"><?php echo $bcJS['bcstatus'][$sr[$i]['bc_status']]; ?></span>
                            </td>
                            <td><?php echo $sr[$i]['bc_id']; ?></td>
                            <td><?php echo $localText; ?></td>
                            <td><?php echo $sr[$i]['bc_title']; ?></td>
                            <td><?php echo !empty($sr[$i]['bc_purephase']) ? $sr[$i]['bc_purephase'].'期' : '&nbsp;'; ?></td>
                            <td><?php echo $sr[$i]['bc_subtitle']; ?></td>
                            <td><?php echo $sr[$i]['bc_addr']; ?></td>
                            <td style="padding:2px 0 0 0;margin:0;text-align:center;"><img src="../imageFiles/bcmode/bcmode_<?php echo $sr[$i]['bc_mode']; ?>.png" alt="<?php echo $bcJS['bcmode'][$sr[$i]['bc_mode']]; ?>" title="<?php echo $bcJS['bcmode'][$sr[$i]['bc_mode']]; ?>" height="36"></td>
                            <td style="padding:8px 0 0 0;margin:0;text-align:center;"><?php
                              $coordType = @$sr[$i]['coordType'];
                              //echo $coordType;
                              if($coordType == null){ echo '&nbsp;'; }
                              else if($coordType == 'Point'){ echo '<i class="fg-2x fg-point"></i>'; }
                              else if($coordType == 'LineString'){ echo '<i class="fg-2x fg-polyline-pt"></i>'; }
                              else if($coordType == 'Polygon'){ echo '<i class="fg-2x fg-polygon-pt"></i>'; }
                              else if($coordType == 'MultiPolygon'){ echo '<i class="fg-2x fg-polygon-hole-pt"></i>'; }
                            ?></td>
                          </tr>
                          <?php $i++; } ?>
                        </tbody>
                        <tfoot>
                          <tr class="cyan">
                            <th>狀態</th>
                            <th>ID</th>
                            <th>地區</th>
                            <th>建案名稱</th>
                            <th>期數</th>
                            <th>建案別名</th>
                            <th>住址</th>
                            <th>型態</th>
                            <th>座標</th>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                    <!-- /分頁訊息傳遞 -->
                  </div>
              </div>
        <?php if($selectMode){//當為地區選擇模式時，隱藏地區選項按鈕 ?>
          </div>
        <?php } else { ?>
            </div>
          </div>
      </div>
        
        <?php } ?>
        </div>
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
              <div>
                <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="此欄位專為已完成搜尋結果的紀錄，進行縮小範圍的再次查詢，輸入即查不必按搜尋按鈕，查詢後點按「清除」按鍵即可回復原始查詢結果。"></i>
                列表內資料二次查尋
              </div>
              <div><input type="text" class="form-control notSubmit" id="dataSearch" name="dataSearch" value="" placeholder="-請輸入搜尋條件-" autocomplete="off"></div>
              <hr style="border-color:#5F6E86;">
              <div>
                <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="部份開放查詢的文字型欄位皆列入被查目標，搜尋條件(欄位)可以依照管理者需求增減，請產生需求時通知工程人員修改。"></i>
                綜合搜尋
              </div>
              <div><input type="text" class="form-control" id="searchText" name="searchText" value="<?php echo $searchText; ?>" placeholder="請輸入文字搜尋條件" autocomplete="off"></div>
              <div>
                <select class="form-control" id="searchMode" name="searchMode" placeholder="-請選擇建案型態-">
                  <option value="">-請選擇建案型態-</option>
                  <?php
                      foreach($bcJS['bcmode'] as $key => $value){
                          if($key==""){ continue; }
                          echo '<option value="'.$key.'"';
                          if($searchMode==$key) echo " selected";
                          echo '>'.$value.'</option>';
                      }
                  ?>
                </select>
              </div>
              <div>社區總戶數</div>
              <div>
                <div class="input-group">
                  <input type="text" class="form-control"  id="searchMDamountS" name="searchMDamountS" value="<?php echo $searchMDamountS; ?>">
                  <div class="input-group-append"><span class="input-group-text" style="background-color:#44a273;border-color:#265D42;color:#FFFFFF;">戶至</span></div>
                  <input type="text" class="form-control"  id="searchMDamountE" name="searchMDamountE" value="<?php echo $searchMDamountE; ?>">
                  <div class="input-group-append"><span class="input-group-text" style="background-color:#44a273;border-color:#265D42;color:#FFFFFF;">戶</span></div>
                </div>
              </div>
              <div>
                <select class="form-control" id="searchGeography" name="searchGeography" placeholder="-請選擇定位狀態-">
                  <option value="">-請選擇定位狀態-</option>
                  <?php
                      foreach($bcJS['ifGeography'] as $key => $value){
                          if($key==""){ continue; }
                          echo '<option value="'.$key.'"';
                          if($searchGeography!='' && $searchGeography==$key) echo " selected";
                          echo '>'.$value.'</option>';
                      }
                  ?>
                </select>
              </div>
              <div>
                <select class="form-control" id="searchCheck" name="searchCheck" placeholder="-請選擇檢號-">
                  <option value="">-請選擇檢號-</option>
                  <?php
                      foreach($bcJS['bccheck'] as $key => $value){
                          if($key==""){ continue; }
                          echo '<option value="'.$key.'"';
                          if($searchCheck==$key) echo " selected";
                          echo '>'.$value.'</option>';
                      }
                  ?>
                </select>
              </div>
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
<script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables  & Plugins -->
<script src="../pluginFiles/datatables/datatables/jquery.dataTables.min.js"></script>
<script src="../pluginFiles/datatables/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<!-- <script src="../pluginFiles/datatables/datatables-responsive/js/dataTables.responsive.min.js"></script> -->
<!-- <script src="../pluginFiles/datatables/datatables-responsive/js/responsive.bootstrap4.min.js"></script> -->
<script src="../pluginFiles/datatables/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../pluginFiles/jszip/jszip.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.html5.min.js"></script>
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

<!-- ------------------------ /Scripts Link ------------------------ -->
<script>
  //Tooltip 啟動
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
  //
  $(function () {
    <?php if($totalNum > $maxResult){ echo "window.parent.toastr['error']('搜尋結果為 {$totalNum} 筆，已超過最高搜尋筆數限制 {$maxResult} 筆紀錄，請縮小搜尋範圍或進行批次操作處理！！');"; } ?>
    $('#resultCount').html('<?php echo $totalNum > $maxResult ? $totalNum."筆符合，表列".$maxResult : $totalNum; ?>');
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
    let bcListTB = datatableCreate('dataTable_bcList');
    $('#dataSearch').on('keyup search input paste cut', function () {
      if ($(this).is(':focus') || this.value !== '') {
          bcListTB.search(this.value).draw();
      }
    });
    //搜尋表格重置清除
    $('#searchReset').click(function() {
      if($('#dataSearch').val() != '') {
        $('#dataSearch').val('');
        bcListTB.search('').draw();
      } else {
        $('#form_search').find('input').val('');
        $('#form_search').find('select').val('');
        //$("#searchDist,#searchCOexpert").find("option:gt(0)").remove();
        //$("#searchCOexpert").find("optgroup").remove();
        //$('#form_search input[type="checkbox"]').prop('checked', false);
      }
    });

    $("#searchText").keydown(function(e){
      if (e.keyCode == 13 && !e.shiftKey){
        e.preventDefault();// prevent default behavior
        return false;
      }
    });
});
////////////////////////////////////////////////////////////////     
// ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
// -------------- 可接收include檔案要求後自定義動作 ------------- //
////////////////////////////////////////////////////////////////
// 當「儲存」按鈕按下時的動作
  function doButAdd(){
    window.location.replace('data_bcedit.php?currID=0&pageMax=<?php echo $pageMax;?>&currPage=<?php echo $currPage;?>&currOrder=<?php echo $currOrder;?>&<?php echo $searchURI;?>');
  }
  // 當「重置」按鈕按下時的動作
  function doButReset(){
    if (!confirm('重整頁面將會強制自伺服器重新載入這個頁面，確定執行這個操作？！')) {
      return false;
    }
    location.reload(true);
  }
  //側邊隱藏區域開啟閉合
  function doButSerach(){
    if($("#sidebarControl").length == 0) {
      return false;
    }
    $('#sidebarControl').click();
  }
  //系統自動生成Excel按鍵
  function doButExcel(){
    $('.buttons-excel').click(); 
  }
  //系統開啟列印視窗按鍵
  function doButPrint(){
    $('.buttons-print').click(); 
  }
</script>

</body>
</html>
