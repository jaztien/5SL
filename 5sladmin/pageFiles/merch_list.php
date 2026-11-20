<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//架構搜尋條件(以搜尋條件架構搜尋表格，以及URI傳遞值字串)
$searchText       = isset($_REQUEST['searchText']) ? rawurldecode($_REQUEST['searchText']) : ''; 
$searchBdtype     = $_REQUEST['searchBdtype'] ?? '';
$searchRsoptions  = $_REQUEST['searchRsoptions'] ?? '';
$searchUsecate    = $_REQUEST['searchUsecate'] ?? '';
$searchMDtype     = $_REQUEST['searchMDtype'] ?? '';
$searchCity       = $_REQUEST['searchCity'] ?? '';
$searchDist       = $_REQUEST['searchDist'] ?? '';
$searchScrc       = $_REQUEST['searchScrc'] ?? '';
$searchBC         = $_REQUEST['searchBC'] ?? '';
$searchStatus     = $_REQUEST['searchStatus'] ?? '';
$searchAdmin      = $_REQUEST['searchAdmin'] ?? '';

$searchURI = "";

if(!empty($searchText)){ $searchURI .= "&searchText=".$searchText; }
if(!empty($searchBdtype)){ $searchURI .= "&searchBdtype=".$searchBdtype; }
if(!empty($searchRsoptions)){ $searchURI .= "&searchRsoptions=".$searchRsoptions; }
if(!empty($searchUsecate)){ $searchURI .= "&searchUsecate=".$searchUsecate; }
if(!empty($searchMDtype)){ $searchURI .= "&searchMDtype=".$searchMDtype; }
if(!empty($searchCity)){ $searchURI .= "&searchCity=".$searchCity; }
if(!empty($searchDist)){ $searchURI .= "&searchDist=".$searchDist; }
if(!empty($searchScrc)){ $searchURI .= "&searchScrc=".$searchScrc; }
if(!empty($searchBC)){ $searchURI .= "&searchBC=".$searchBC; }
if(!empty($searchStatus)){ $searchURI .= "&searchStatus=".$searchStatus; }
if(!empty($searchAdmin)){ $searchURI .= "&searchAdmin=".$searchAdmin; }
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}
//定義條件搜尋之前以及之後不同的顯示筆數
$maxResult = (!empty($searchURI)) ? 200 : 50;
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_MERCH');
define('PAGE_ITEM','ITEM_MERCH');
define('PAGE_OPANEL',1);//1-新增；2-編輯；3-上傳；4-編輯/刪除；9-[空]。
define('SEARCH_OPANEL',!empty($searchURI)?2:1);
define('EXPORT_OPANEL',3);//1-Excel；2-PDF；3-Excel+Print；
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

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
  <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
	
  <title> Fifth Season Life 人生第五季 | 課程商品資料紀錄 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <link href="../pluginFiles/font-gis/css/font-gis.css" rel="stylesheet" />
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
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css<?php echo '?t='.time(); ?>">
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
 </style>
</head>
<body class="hold-transition sidebar-mini layout-navbar-fixed layout-footer-fixed">
  <!-- 全頁框 -->
  <div class="wrapper">
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="../imageFiles/preloaderLOGO.png" alt="Fifth Season Life LOGO" height="60" width="60"> 
    </div>
    <!-- /Preloader -->
    <!-- 頁內全頁內容 -->
    <div class="content-wrapper">
      <input type="text" id="searchURI" name="searchURI" style="width:1px;height:1px;opacity:0;" value="<?php //echo $searchURI; ?>">
      <!-- 頁頭導覽條 -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-7">
              <h4 class="m-0">
                課程商品資料紀錄
                <i class="fa-solid fa-circle-question text-md" data-bs-toggle="tooltip" data-bs-placement="top" title="xxxx"></i>
                <span class="text-muted text-sm" style="margin-left:6px;">merch_list.php</span>
              </h4>
            </div><!-- /.col -->
            <div class="col-sm-5">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">課程商品管理</li>
                <li class="breadcrumb-item active">課程商品資料紀錄</li>
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </section>
      <!-- /頁頭導覽條 -->
      <section class="content">
        <div class="container-fluid">
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
                <div class="card-body">
                  <table id="dataTable_mdlist" class="table table-bordered table-striped table-hover" data-pagemax="25" data-dirto="merch_info.php" but-excel but-print="人生第五季課程商品資料紀錄" data-search>
                    <thead>
                      <tr>
                        <th>狀態</th>
                        <th>ID</th>
                        <th>圖像</th>
                        <th>名稱</th>
                        <th>種類</th>
                        <th>用途</th>
                        <th>建物</th>
                        <th>城市</th>
                        <th>行政區</th>
                        <th>住址</th>
                        <th>售價</th>
                        <th>座標</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $listSql = "SELECT merch_id, merch_title, merch_rsoptions, merch_usecate, merch_bdcate, merch_city, merch_district, merch_add, merch_coordinates, merch_saleprice, merch_defaultphoto, merch_status, COUNT(*) OVER() 'totalNum' FROM merchandise";//, merch_coordinates.STGeometryType() AS coordType
                        if(!empty($searchURI) || !empty($searchAdmin)){ $listSql .= " WHERE 1=1"; }
                        if(!empty($searchText)){ $listSql .= " AND (merch_sn LIKE '%".$searchText."%' OR merch_title LIKE '%".$searchText."%' OR merch_add LIKE '%".$searchText."%' OR merch_superscript LIKE '%".$searchText."%' OR merch_subtitle LIKE '%".$searchText."%' OR merch_area LIKE '%".$searchText."%' OR merch_des LIKE '%".$searchText."%')"; }
                        if(!empty($searchBdtype)){ $listSql .= " AND merch_bdtype = ".$searchBdtype; }
                        if(!empty($searchRsoptions)){ $listSql .= " AND merch_rsoptions = '".$searchRsoptions."'"; }
                        if(!empty($searchUsecate)){ $listSql .= " AND merch_usecate = ".$searchUsecate; }
                        if(!empty($searchMDtype)){ $listSql .= " AND merch_mdtype = ".$searchMDtype; }
                        if(!empty($searchCity)){ $listSql .= " AND merch_city = ".$searchCity; }
                        if(!empty($searchDist)){ $listSql .= " AND merch_district = ".$searchDist; }
                        if(!empty($searchScrc)){ $listSql .= " AND scrc_id = ".$searchScrc; }
                        if(!empty($searchBC)){ $listSql .= " AND bc_id = ".$searchBC; }
                        if(!empty($searchStatus)){ $listSql .= " AND merch_status = ".$searchStatus; }
                        if(!empty($searchAdmin)){
                          switch($searchAdmin){
                            case 1: 
                              $listSql .= " AND merch_coordinates IS NULL";
                            break;
                            case 2:
                              $listSql .= " AND (merch_title LIKE '%?%' OR merch_add LIKE '%?%')";
                            break;
                            case 3:
                              $listSql .= " AND (merch_city IS NULL OR merch_district IS NULL OR merch_add IS NULL)";
                            break;
                          }
                        }

                        $listSql .= " ORDER BY merch_published DESC LIMIT 500";
                        $sr = $DB->query($listSql);
                        $totalNum = intval(@$sr[0]['totalNum']);
                        $i = 0; //以$i為$nameList這個陣列的索引值 
                        while($i<count($sr)){ 
                        ?>
                          <tr style="cursor:pointer;" data-currid="<?php echo $sr[$i]['merch_id'] ?? 0; ?>">
                            <td><span class="right badge<?php echo $merchJS['actionbadge'][$sr[$i]['merch_status'] ?? ''] ?? ''; ?>"><?php echo str_replace("/", "<br>/", $merchJS['status'][$sr[$i]['merch_status'] ?? ''] ?? ''); ?></span></td>
                            <td><?php echo $sr[$i]['merch_id']; ?></td>
                            <td><?php
                            $subFolder = ceil(intval($sr[$i]['merch_id'])/100);
                            $tinypic = "../uploadFiles/md/".$subFolder."/mdimg_".$sr[$i]['merch_id']."_".$sr[$i]['merch_defaultphoto']."_tiny.jpg";
                            echo "<img style='margin:-6px 0px -6px;' height='48px' src='";
                            if(file_exists($tinypic)) echo $tinypic."?".time();
                            else echo "../imageFiles/nopic_160x90.jpg";
                            echo "'>";
                            ?></td>
                            <td><?php echo $sr[$i]['merch_title'] ?? '&nbsp;'; ?></td>
                            <td><?php echo $merchJS['rsoptions'][$sr[$i]['merch_rsoptions'] ?? ''] ?? '&nbsp;'; ?></td>
                            <td><?php echo $merchJS['usecate'][$sr[$i]['merch_usecate'] ?? ''] ?? '&nbsp;'; ?></td>
                            <td><?php echo $merchJS['bdcate'][$sr[$i]['merch_bdcate'] ?? ''] ?? '&nbsp;'; ?></td>
                            <td><?php echo intval($sr[$i]['merch_city'])>0 ? $locJS[$sr[$i]['merch_city']]['text'] ?? '不明' : '不明'; ?></td>
                            <td><?php echo intval($sr[$i]['merch_city'])>0 && intval($sr[$i]['merch_district'])>0 ? $locJS[$sr[$i]['merch_city']]['district'][$sr[$i]['merch_district']] ?? '不明' : '不明'; ?></td>
                            <td><?php echo $sr[$i]['merch_city'] ?? '&nbsp;'; ?></td>
                            <td><?php echo number_format($sr[$i]['merch_saleprice']); ?>萬</td>
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
                      <?php 
                          $i++;
                          } 
                      ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>狀態</th>
                        <th>ID</th>
                        <th>圖像</th>
                        <th>名稱</th>
                        <th>種類</th>
                        <th>用途</th>
                        <th>建物</th>
                        <th>城市</th>
                        <th>行政區</th>
                        <th>住址</th>
                        <th>售價</th>
                        <th>座標</th>
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
    </div>
    <!-- /頁內全頁內容 -->

    <!-- jQuery -->
    <script src="../pluginFiles/jquery/jquery.min.js"></script>

    <!-- 右側功能控制區 -->
    <aside class="control-sidebar control-sidebar-dark">
      <!-- 功能區內容 ------------------------------------ -->
      <form id="form_search" name="form_search" action="" method="get">
        <div style="height:100%;display:flex;flex-direction:column;">
          <!-- 上格 -->
          <div style="flex: 1;padding:4px">
            <div>
              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="此欄位專為已完成搜尋結果的紀錄，進行縮小範圍的再次查詢，輸入即查不必按搜尋按鈕，查詢後點按「清除」按鍵即可回復原始查詢結果。"></i>
              列表內資料過濾查尋
            </div>
            <div><input type="text" class="form-control notSubmit" id="dataSearch" name="dataSearch" value="" placeholder="-請輸入搜尋條件-" autocomplete="off"></div>
            <hr style="border-color:#5F6E86;">
            <div>
              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="部份開放查詢的文字型欄位皆列入被查目標，搜尋條件(欄位)可以依照管理者需求增減，請產生需求時通知工程人員修改。"></i>
              綜合搜尋
            </div>
            <div><input type="text" class="form-control" id="searchText" name="searchText" value="<?php echo $searchText; ?>" placeholder="請輸入搜尋條件" autocomplete="off"></div>
            <div>指定搜尋</div>
            <div>
              <select class="form-control" id="searchBdtype" name="searchBdtype">
                <option value="">-請選擇建物種類-</option>
                <?php
                  foreach($merchJS['bdtype'] as $key => $value){
                    if($key==""){ continue; }
                    echo '<option value="'.$key.'"';
                    if(@$searchBdtype!="" && @$searchBdtype==$key) echo " selected";
                    echo '>'.$value.'</option>';
                  }
                ?>
              </select>
            </div>
            <div>
              <select class="form-control" id="searchRsoptions" name="searchRsoptions">
                <option value="">-請選擇租售選項-</option>
                <?php
                  foreach($merchJS['rsoptions'] as $key => $value){
                    if($key==""){ continue; }
                    echo '<option value="'.$key.'"';
                    if(@$searchRsoptions!="" && @$searchRsoptions==$key) echo " selected";
                    echo '>'.$value.'</option>';
                  }
                ?>
              </select>
            </div>
            <div>
              <select class="form-control" id="searchUsecate" name="searchUsecate">
                <option value="">-請選擇用途分類-</option>
                <?php
                  foreach($merchJS['usecate'] as $key => $value){
                    if($key==""){ continue; }
                    echo '<option value="'.$key.'"';
                    if(@$searchUsecate!="" && @$searchUsecate==$key) echo " selected";
                    echo '>'.$value.'</option>';
                  }
                ?>
              </select>
            </div>
            <div>
              <select class="form-control" id="searchMDtype" name="searchMDtype">
                <option value="">-請選擇課程商品屬性-</option>
                <?php
                  foreach($merchJS['mdtype'] as $key => $value){
                    if($key==""){ continue; }
                    echo '<option value="'.$key.'"';
                    if(@$searchMDtype!="" && @$searchMDtype==$key) echo " selected";
                    echo '>'.$value.'</option>';
                  }
                ?>
              </select>
            </div>
            <div>
              <select class="form-control" id="searchCity" name="searchCity" data-placeholder="-請選擇所在城市-" data-select="city">
                <option value="">-請選擇所在城市-</option>
                <?PHP
                  foreach($locJS as $key => $value){
                    echo "<option value='".$value['value']."'";
                    if(@$searchCity!="" && @$searchCity==$key) echo " selected";
                    echo ">".$value['text']."</option>";
                  }
                ?>
              </select>
            </div>
            <div>
              <select class="form-control" id="searchDist" name="searchDist" data-placeholder="-請選擇行政區劃-" data-select="dist" dist-fromcity="searchCity" list-bc="1" list-circ="1" style="width: 100%;">
                <option value="">-請選擇行政劃區-</option>
                <?PHP
                if(!empty($searchCity)) {
                  foreach($locJS[$searchCity]['district'] as $key => $value){
                    echo "<option value='".$key."'";
                      if(@$searchDist!="" && @$searchDist==$key) echo " selected";
                    echo ">".$value."</option>";
                  }
                }
                ?>
              </select>
            </div>
            <div>
              <select class="form-control" id="searchScrc" name="searchScrc" data-placeholder="-請選擇所屬生活商圈-" circ-fromcity="searchCity">
                <option value="">-請選擇所屬生活商圈-</option>
                <?PHP
                  if(!empty($searchDist)) {
                    $sqlscrc="SELECT scrc_id,scrc_title,scrc_circletitle FROM socialcircle WHERE TRANSLATE(scrc_district,'[]',',,') LIKE '%,".$searchDist.",%' AND scrc_status=? ORDER BY scrc_title ASC";
                    $resscrc = $DB->query($sqlscrc,array(1));
                    $i=0;
                    while($i<count($resscrc)){
                      echo "<option value='".$resscrc[$i]['scrc_id']."'";
                      if (@$searchScrc==$resscrc[$i]['scrc_id'])  echo " selected";
                      echo ">".$resscrc[$i]['scrc_title'];
                      if(!empty($resscrc[$i]['scrc_circletitle'])) { echo "(".$resscrc[$i]['scrc_circletitle'].")"; }
                      
                      echo "</option>";
                      $i++;
                    }
                  }
                ?>
              </select>
            </div>
            <div>
              <select class="form-control" id="searchBC" name="searchBC" data-placeholder="-請選擇所屬社區建案-" bc-fromcity="searchCity" data-select="bc" data-addnew="1">
                <option value="">-請選擇所屬社區建案-</option>
                <?PHP
                  if(!empty($searchDist)) {
                    $sqlcomm="SELECT bc_id, bc_title FROM buildcase WHERE bc_district=? AND bc_status<=? ORDER BY bc_title ASC";
                    $rescom = $DB->query($sqlcomm,array($searchDist,5));
                    $i=0;
                    while($i<count($rescom)){
                      echo "<option value='".$rescom[$i]['bc_id']."'";
                      if (@$searchBC==$rescom[$i]['bc_id'])  echo " selected";
                      echo ">".$rescom[$i]['bc_title']."</option>";
                      $i++;
                    }
                  }
                ?>
              </select>
            </div>
            <div>
              <select class="form-control" id="searchStatus" name="searchStatus">
                <option value="">-請選擇上架狀態-</option>
                <?php
                  foreach($merchJS['status'] as $key => $value){
                      echo '<option value="'.$key.'"';
                      if($key==""){ continue; }
                      if(@$searchStatus!="" && @$searchStatus==$key) echo " selected";
                      echo '>'.$value.'</option>';
                  }
                ?>
              </select>
            </div>
            <div>
              <select class="form-control" id="searchAdmin" name="searchAdmin">
                <option value="">-請選擇管理狀態-</option>
                <option value="1">未完成地理座標欄位</option>
                <option value="2">名稱或住址欄位含有「?」亂嗎</option>
                <option value="3">地理位置不完整</option>
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
<!-- <script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script> -->
<!-- Bootstrap 5 -->
<script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
<!-- DataTables2 & Plugins -->
<!--
<script src="../pluginFiles/datatables/datatables/jquery.dataTables.min.js"></script>
<script src="../pluginFiles/datatables/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.html5.min.js"></script>
-->
<!-- DataTables3 & Plugins -->
<script src="../pluginFiles/datatables3/datatables.min.js"></script>
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>
<!-- ------------------------ /Scripts Link ------------------------ -->
<script type="text/javascript">
////////////////////////////////////////////////////////////////
// ---------------- JAVASCRIPTS 全域調用參數設置 --------------- //
////////////////////////////////////////////////////////////////



////////////////////////////////////////////////////////////////
// -------------- JAVASCRIPTS FUNCTIONs 全域調用 -------------- //
// -------------- 可接收include檔案要求後自定義動作 -------------- //
////////////////////////////////////////////////////////////////


  // 當「儲存」按鈕按下時的動作
  function doButAdd(){
    window.location.replace('merch_info.php?currID=0');
  }
  // 當「重置」按鈕按下時的動作
  function doButReset(){
    location.reload(true);
  }
  //側邊隱藏區域開啟閉合
  function doButSerach(){
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
////////////////////////////////////////////////////////////////
// ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
// ---------------------------------------------------------- //
////////////////////////////////////////////////////////////////
  $(document).ready(function() {
    //------------------------- Tooltips -----------------------------//
    $('[data-toggle="tooltip"]').tooltip();

    //動態顯示筆數
    $('#resultCount').html('<?php echo $totalNum > $maxResult ? $totalNum."筆符合，表列".$maxResult : $totalNum; ?>');
  
    // iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });
    //----------------------------------------
    var mdlistTB = datatableCreate('dataTable_mdlist');
    //外置表列內容搜尋框動作操作
    $('#dataSearch').on('keyup search input paste cut', function () {
      if ($(this).is(':focus') || this.value !== '') {
          mdlistTB.search(this.value).draw();
      }
    });
    //搜尋表格重置清除
    $('#searchReset').click(function() {
      if($('#dataSearch').val() != '') {
        $('#dataSearch').val('');
        mdlistTB.search('').draw();
      } else {
        $('#form_search').find('input').val('');
        $("#form_search").find("select").val("");
        $("#searchDist,#searchScrc,#searchBC").find("option:gt(0)").remove();
      }
    });
/*
    $(window).on('wheel', function (event) {
      // 檢查右側控制側邊欄目前是否處於「開啟」狀態
      if ($('body').hasClass('control-sidebar-slide-open')) {
        //【防誤觸】確保滑鼠游標「不在」右側側邊欄內部
        if ($(event.target).closest('.control-sidebar').length === 0) {
          // 呼叫 AdminLTE 3 官方 API 關閉右側側邊欄
          $('[data-widget="control-sidebar"]').ControlSidebar('collapse');
          //$('#sidebarControl').click(); 
        }
      }
    });
*/
    //-------- 加載完成後更改外框主頁中樹狀目錄開合狀態 --------//
    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  //////////////////////////////////////////////////////////////////
  });
</script>
</body>
</html>
