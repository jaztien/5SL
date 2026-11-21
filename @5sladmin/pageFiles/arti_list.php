<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//架構搜尋條件(以搜尋條件架構搜尋表格，以及URI傳遞值字串)
$searchText = isset($_REQUEST['searchText']) ? rawurldecode($_REQUEST['searchText']) : ''; 
$searchMode = isset($_REQUEST['searchMode']) ? $_REQUEST['searchMode'] : '';
$searchType = isset($_REQUEST['searchType']) ? $_REQUEST['searchType'] : '';
$searchCate = isset($_REQUEST['searchCate']) ? $_REQUEST['searchCate'] : '';
$searchGroup = isset($_REQUEST['searchGroup']) ? $_REQUEST['searchGroup'] : '';
$searchFromarea = isset($_REQUEST['searchFromarea']) ? $_REQUEST['searchFromarea'] : '';
$searchIfpay = isset($_REQUEST['searchIfpay']) ? $_REQUEST['searchIfpay'] : '';
$searchStatus = isset($_REQUEST['searchStatus']) ? $_REQUEST['searchStatus'] : '';
//
$searchURI = "";
//
if(!empty($searchText)){ $searchURI .= "&searchText=".$searchText; }
if(!empty($searchMode)){ $searchURI .= "&searchMode=".$searchMode; }
if(!empty($searchType)){ $searchURI .= "&searchType=".$searchType; }
if(!empty($searchCate)){ $searchURI .= "&searchCate=".$searchCate; }
if(!empty($searchGroup)){ $searchURI .= "&searchGroup=".$searchGroup; }
if(!empty($searchFromarea)){ $searchURI .= "&searchFromarea=".$searchFromarea; }
if(!empty($searchIfpay)){ $searchURI .= "&searchIfpay=".$searchIfpay; }
if(!empty($searchStatus)){ $searchURI .= "&searchStatus=".$searchStatus; }
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}
//定義條件搜尋之前以及之後不同的顯示筆數
$maxResult = (!empty($searchURI)) ? 200 : 50;
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_ARTI');
define('PAGE_ITEM','ITEM_ARTI');
define('PAGE_OPANEL',1);//1-新增；2-編輯；3-上傳；4-編輯/刪除。
define('SEARCH_OPANEL',!empty($searchURI)?2:1);
define('EXPORT_OPANEL',3);//1-Excel；2-PDF；3-Excel+Print；
define('bkRedir',1);
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');


//功能陣列建立，取自JSON檔案
$artiJS=_fn_getCachedJsonData("../jsonFiles/articles.json");
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
	
  <title> Fifth Season Life 人生第五季 | 圖文模板紀錄 </title>
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
                圖文模板紀錄
                <i class="fa-solid fa-circle-question text-md" data-bs-toggle="tooltip" data-bs-placement="top" title="圖文模板資料庫為網站內部管理人員建立文字類素材所用，並非歸類於由使用者建立的有償服務系統中，圖文模板資料包括特定頁面的說明文稿、設計美工、新聞資料、法律說明文件、技術說明文件、一般共享知識、各類模板......等等，無償或有條件收費使用的文字資料。"></i>
                <span class="text-muted text-sm" style="margin-left:6px;">arti_list.php</span>
              </h4>
            </div><!-- /.col -->
            <div class="col-sm-5">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">圖文模板管理</li>
                <li class="breadcrumb-item active">圖文模板紀錄</li>
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
                    <table id="dataTable_artiList" class="table table-bordered table-striped table-hover" data-pagemax="25" data-dirto="arti_detail.php" but-excel but-print="人生第五季圖文模板資料紀錄" data-search>
                      <thead>
                        <tr>
                          <th>狀態</th>
                          <th>ID</th>
                          <th>最新更新</th>
                          <th>名稱</th>
                          <th>代碼</th>
                          <th>類別</th>
                          <th>分類</th>
                          <th>子類</th>
                          <th>付費</th>
                          <th>有效日</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          $listSql = "SELECT *, COUNT(*) OVER() 'totalNum' FROM articles";
                          if(!empty($searchURI)){ $listSql .= " WHERE 1=1"; }
                          if(!empty($searchText)){ $listSql .= " AND (arti_title LIKE '%".$searchText."%' OR arti_keycode LIKE '%".$searchText."%' OR arti_tag LIKE '%".$searchText."%')"; }
                          if(!empty($searchMode)){ $listSql .= " AND arti_mode = ".$searchMode; }
                          if(!empty($searchType)){ $listSql .= " AND arti_type = ".$searchType; }
                          if(!empty($searchCate)){ $listSql .= " AND TRANSLATE(arti_cate, '[]', ',,') LIKE '%,".$searchCate.",%'"; }
                          if(!empty($searchGroup)){ $listSql .= " AND arti_group = ".$searchGroup; }
                          if(!empty($searchFromarea)){ $listSql .= " AND arti_fromarea = ".$searchFromarea; }
                          if(!empty($searchIfpay)){ $listSql .= " AND arti_payfor = ".$searchIfpay; }
                          if(!empty($searchStatus)){ $listSql .= " AND arti_status = ".$searchStatus; }
                          $listSql .= " ORDER BY arti_id DESC LIMIT {$maxResult}";
                          $sr = $DB->query($listSql);
                          $totalNum = intval(@$sr[0]['totalNum']);
                          $dateNow = new DateTime();
                          $i = 0; //以$i為$nameList這個陣列的索引值
                          while($i<count($sr)){ 
                          ?>
                            <tr style="cursor:pointer;" data-currid="<?php echo $sr[$i]['arti_id']; ?>" class="<?php echo $sr[$i]['arti_status'] == 0 ? 'text-black-25 font-italic' : ''; ?> ">
                              <td>
                                  <span class="right badge<?php echo $artiJS['badge'][$sr[$i]['arti_status']]; ?>"><?php echo $artiJS['status'][$sr[$i]['arti_status']]; ?></span>
                                  <?php echo ($sr[$i]['arti_type'] >= 1 && $sr[$i]['arti_type'] <=10) ? '<span class="right badge badge-warning">系統</span>' : ''; ?>
                              </td>
                              <td><?php echo $sr[$i]['arti_id']; ?></td>
                              <td><?php echo $sr[$i]['arti_updated']; ?></td>
                              <td><?php echo $sr[$i]['arti_title']; ?></td>
                              <td><b class="text-info"><?php echo $sr[$i]['arti_keycode']; ?></b></td>
                              <td><?php
                              if(isset($artiJS['artitype'][$sr[$i]['arti_type']])){
                                  echo $artiJS['artitype'][$sr[$i]['arti_type']]['text'];
                              }
                              ?></td>
                              <td class="text-sm"><?php
                              //if(empty($sr[$i]['arti_cate']) || $sr[$i]['arti_cate']=="[]"){
                              if(empty($sr[$i]['arti_cate'])){
                                echo "未分類";
                              } else {
                                echo $artiJS['articate'][$sr[$i]['arti_cate']]['text'];
                                /*
                                $getCateString = "";
                                $getCateArr = json_decode($sr[$i]['arti_cate'], true);
                                foreach ($getCateArr as $cateID) {
                                    $getCateString .= $artiJS['articate'][$cateID]['text'].", ";
                                }
                                echo mb_substr($getCateString, 0, -2, "utf-8");
                                */

                              }
                              ?></td>
                              <td class="text-sm"><?php
                              //if(empty($sr[$i]['arti_subcate']) || $sr[$i]['arti_subcate']=="[]"){
                              if(empty($sr[$i]['arti_subcate'])){
                                echo "未分類";
                              } else {
                                $getCateIDX = substr($sr[$i]['arti_subcate'], 0, 2);
                                echo $artiJS['articate'][$getCateIDX]['sub'][$sr[$i]['arti_subcate']]['text'];
                                /*
                                $getSCateString = "";
                                $getSCateArr = json_decode($sr[$i]['arti_subcate'], true);
                                foreach ($getSCateArr as $ScateID) {
                                    $getCateID = substr($ScateID, 0, 2);
                                    $getSCateString .= $artiJS['articate'][$getCateID]['sub'][$ScateID]['text'].", ";
                                }
                                echo mb_substr($getSCateString, 0, -2, "utf-8");
                                */
                              }
                              ?></td>
                              <td><?php
                              if(isset($artiJS['yesorno'][$sr[$i]['arti_payfor']])){
                                  echo $artiJS['yesorno'][$sr[$i]['arti_payfor']];
                              }
                              ?></td>
                              <td><?php
                                if(empty($sr[$i]['arti_dateclose']) || $sr[$i]['arti_dateclose'] == null){
                                  echo "無期限";
                                } else {
                                  $dateClose = new DateTime($sr[$i]['arti_dateclose']);
                                  $interval = $dateNow->diff($dateClose);
                                  if ($interval->invert === 1) {
                                      echo "已過期";
                                  } else {
                                      echo $interval->days . "天";
                                  }
                                }
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
                          <th>修改日期</th>
                          <th>名稱</th>
                          <th>代碼</th>
                          <th>類別</th>
                          <th>分類</th>
                          <th>子類</th>
                          <th>付費</th>
                          <th>有效日</th>
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
        <div style="height:100%;display:flex;flex-direction: column;">
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
            <div><input type="text" class="form-control" id="searchText" name="searchText" value="<?php echo $searchText; ?>" placeholder="請輸入文字搜尋條件" autocomplete="off"></div>
            <div>指定搜尋</div>
            <div class="form-group">
              <select class="form-control" id="searchMode" name="searchMode">
                <option value="">-請選擇文章格式-</option>
                <?php
                  foreach($artiJS['artimode'] as $key => $value){
                      if($key=="") { continue; }
                      echo '<option value="'.$key.'"';
                      if($searchMode==$key) echo " selected";
                      echo '>'.$value.'</option>';
                  }
                ?>
              </select>
            </div>
            <div class="form-group">
              <select class="form-control" id="searchType" name="searchType">
                <option value="">-請選擇文章種類-</option>
                <?php
                  foreach($artiJS['artitype'] as $key => $value){
                      if($key=="") { continue; }
                      echo '<option value="'.$key.'"';
                      if($searchType==$key) echo " selected";
                      echo '>'.$value['text'].'</option>';
                  }
                ?>
              </select>
            </div>
            <div class="form-group">
              <select class="form-control" id="searchCate" name="searchCate">
                <option value="">-請選擇內容主分類-</option>
                <?php
                  foreach($artiJS['articate'] as $key => $value){
                      if($key=="") { continue; }
                      echo '<option value="'.$key.'"';
                      if(@$searchCate==$key) echo " selected";
                      echo '>'.$value['text'].'</option>';
                  }
                ?>
              </select>
            </div>
            <div class="form-group">
              <select class="form-control" id="searchSubCate" name="searchSubCate">
                <option value="">-請選擇文章子分類-</option>
                  <?php
                    $subJS=!empty($res['arti_cate']) ? $artiJS['articate'][$res['arti_cate']]['sub'] : array();
                    foreach ($subJS as $key => $value) {
                        echo '<option value="'.$key.'" title="'.$value['des'].'"';
                        if(@$searchSubCate == $key) echo " selected";
                        echo '>'.$value['text'].'</option>';
                    }
                  ?>
              </select>
            </div>
            <div class="form-group">
              <select class="form-control" id="searchFromarea" name="searchFromarea">
                <option value="">-請選擇地緣關係-</option>
                <?php
                  foreach($artiJS['fromarea'] as $key => $value){
                      if($key=="") { continue; }
                      echo '<option value="'.$key.'"';
                      if($searchFromarea==$key) echo " selected";
                      echo '>'.$value.'</option>';
                  }
                ?>
              </select>
            </div>
            <div class="form-group">
              <select class="form-control" id="searchIfpay" name="searchIfpay">
                <option value="">-是否有償使用-</option>
                <?php
                  foreach($artiJS['yesorno'] as $key => $value){
                      if($key=="") { continue; }
                      echo '<option value="'.$key.'"';
                      if($searchIfpay==$key) echo " selected";
                      echo '>'.$value.'</option>';
                  }
                ?>
              </select>
            </div>
            <div class="form-group">
              <select class="form-control" id="searchStatus" name="searchStatus">
                <option value="">-請選擇文章狀態-</option>
                <?php
                  foreach($artiJS['status'] as $key => $value){
                      if($key=="") { continue; }
                      echo '<option value="'.$key.'"';
                      if($searchStatus==$key) echo " selected";
                      echo '>'.$value.'</option>';
                  }
                ?>
              </select>
            </div>
          </div>
          <hr style="border-color:#5F6E86;">
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
<script>
  var artiObj = <?php echo json_encode($artiJS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

////////////////////////////////////////////////////////////////
// -------------- JAVASCRIPTS FUNCTIONs 全域調用 -------------- //
// -------------- 可接收include檔案要求後自定義動作 -------------- //
////////////////////////////////////////////////////////////////
  // 當「返回」按鈕按下時的動作
  function doReDirect(){
    window.history.back();
  }
// 當「新增」按鈕按下時的動作
  function doButAdd(){
    window.location.replace('arti_detail.php?currID=0');
  }
  function doButReset(){
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
////////////////////////////////////////////////////////////////
// ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
// ---------------------------------------------------------- //
////////////////////////////////////////////////////////////////
  $(document).ready(function() {    
    //------------------------- Tooltips -----------------------------//
    $('[data-toggle="tooltip"]').tooltip();
    //動態顯示筆數
    $('#resultCount').html('<?php echo $totalNum > $maxResult ? $totalNum."筆符合，表列".$maxResult : $totalNum; ?>');
  
    //* iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });
    //-----------------------------------------
    var artilistTB = datatableCreate('dataTable_artiList');
    //外置表列內容搜尋框動作操作
    $('#dataSearch').on('keyup search input paste cut', function () {
      if ($(this).is(':focus') || this.value !== '') {
          artilistTB.search(this.value).draw();
      }
    });
    // --------------- 當改變文章主分類選項時 ------------------
    $('#searchCate').on('change', function(){
      var cateId = $(this).val();//Array
      var subId = $('#arti_subcate').val();//Array
      var ac = artiObj['articate'];
      $('#searchSubCate').html('<option value="">-請選擇內容子分類-</option>');
      $.each(ac[cateId]['sub'], function( index, value ) {
        var appendHTML = '<option value="' + index + '">'+ value['text'] + ' [' + value['des'] +']</option>';
        $('#searchSubCate').append(appendHTML);
      });
    });
    //搜尋表格重置清除
    $('#searchReset').click(function() {
      if($('#dataSearch').val() != '') {
        $('#dataSearch').val('');
        artilistTB.search('').draw();
      } else {
        $('#form_search').find('input').val('');
        $('#form_search').find('select').val('');
        //$("#searchDist,#searchScrc,#searchBC").find("option:gt(0)").remove();
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