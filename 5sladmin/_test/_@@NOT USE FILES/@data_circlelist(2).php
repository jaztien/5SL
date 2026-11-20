<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
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
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_CIRCLE');
define('PAGE_ITEM','ITEM_TOOLS');
define('PAGE_ITEM2','ITEM_DATAS');
define('PAGE_OPANEL',1);//1-新增；2-編輯；3-上傳；4-編輯/刪除；9-[空]。
define('SEARCH_OPANEL',!empty($searchURI)?2:1);
define('EXPORT_OPANEL',3);//1-Excel；2-PDF；3-Excel+Print；
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//功能陣列建立，取自JSON檔案
$mdGet=c("../jsonFiles/merchandise.json");//若要給JAVA使用，必須去除 whitespace
$partGet=_fn_getCachedJsonData("../jsonFiles/twparts.json");//若要給JAVA使用，必須去除 whitespace
$locGet=_fn_getCachedJsonData("../jsonFiles/locations.json");//若要給JAVA使用，必須去除 whitespace
//
$topARR=$partJS;
//
$locCode = (!empty($_REQUEST['locCode'])) ? $_REQUEST['locCode'] :0;
$pageMax = isset($_REQUEST['pageMax']) ? intval($_REQUEST['pageMax']) : 25;
$jumpData = isset($_REQUEST['jumpData']) ? intval($_REQUEST['jumpData']) : 1;
$jumpTo = floor($jumpData / $pageMax);
$jumpTo = $jumpData%$pageMax==0 ? ($jumpTo - 1)*$pageMax : $jumpTo*$pageMax;
//
//$searchURI .= "locCode=".$locCode;
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
	
  <title> Fifth Season Life 人生第五季 | 商圈&聚落資料表 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
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
                商圈&聚落資料紀錄
                <i class="fa-solid fa-circle-question text-md" data-bs-toggle="tooltip" data-bs-placement="top" title="具指標性重要社交及生活圈區域資料一覽表（供範圍搜尋使用）"></i>
                <span class="text-muted text-sm" style="margin-left:6px;">data_circlelist.php</span>
              </h4>
            </div>
            <div class="col-sm-5">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">基礎數據&管理工具</li>
                <li class="breadcrumb-item">基礎結構數據</li>
                <li class="breadcrumb-item active">商圈&聚落資料列表</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <!-- /頁頭導覽條 -->
      <!-- 主要內容區域 ------------------------------------- -->
      <section class="content">
        <div class="container-fluid">
          <div class="row pb-2">
            <?php 
            //$i=0;
            foreach($topARR as $key => $value){
            //while($i<count($topARR)){
            ?>
            <div class="col-2">
              <button type="button" class="btn btn-block btn-outline-info<?php echo ($locCode==$key) ? ' active' : ''; ?>" onClick="<?php echo ($locCode!=$key) ? "location.href = '?locCode=".$key."';" : "return false;"; ?>"><?php echo $topARR[$key]['text']; ?></button>
            </div>
            <?php 
            //$i++; 
            } 
            ?>
          </div>
          <div class="card card-info card-tabs">
              <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                  <?php 
                  $ti=0;
                  //$areaARR=array();
                  //$loc_b = $DB->query("SELECT * FROM data_locations WHERE loc_depth=? AND loc_status=? AND loc_pcode=?",array(2,1,$locCode));
                  $loc_b = $topARR[$locCode]['city'];
                  //$cityARR[$i]=array('pcode'=>$loc_b[$i]['code']);
                  $cityARR = array();
                  while($ti<count($loc_b)){
                    //$cityARR[$i]=array('code'=>$loc_b[$i]['loc_code'],'title'=>$loc_b[$i]['loc_title']);
                    $cityARR[$ti]=array('code'=>$loc_b[$ti],'title'=>$locJS[$loc_b[$ti]]['text']);
                  ?>
                  <li class="nav-item"><a class="nav-link<?php echo ($ti==0) ? ' active' : ''; ?>" href="#locTab<?php echo $loc_b[$ti]; ?>" data-toggle="pill" role="tab" aria-controls="locTab<?php echo $loc_b[$ti]; ?>" aria-selected="true"><?php echo $locJS[$loc_b[$ti]]['text']; ?></a></li>
                  <?php $ti++; } ?>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                  <div class="tab-content">
                    <!-- 分頁訊息傳遞 -->
                    <?php
                      $totalNum = 0;
                      $ci=0;
                      while($ci<count($cityARR)){
                        $sr = $DB->query("SELECT * FROM socialcircle WHERE scrc_city=?",array($cityARR[$ci]['code']));
                    ?>
                    <div class="tab-pane<?php echo ($ci==0) ? ' active' : ''; ?>" id="locTab<?php echo $cityARR[$ci]['code'];?>">
                      <table id="dataTable_scrcList<?php echo $cityARR[$ci]['code'];?>" class="table table-bordered table-striped table-hover" data-butClass="cyan" data-pagemax="<?php echo $pageMax; ?>" data-jumpto="<?php echo $jumpTo; ?>" data-dirto="data_scrcdetail.php" data-search but-addnew>
                        <thead>
                          <tr class="cyan">
                            <th>狀態</th>
                            <th>ID</th>
                            <th>代碼</th>
                            <th>種類</th>
                            <th>所屬行政區</th>
                            <th>名稱</th>
                            <th>簡稱/俗名</th>
                            <th>座標</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                            $ai=0;
                            while($ai<count($sr)){
                          ?>
                          <tr style="cursor:pointer;" data-currid="<?php echo $sr[$ai]['scrc_id']; ?>">
                            <td>
                              <span class="right badge<?php echo $mdJS['actionbadge'][$sr[$i]['scrc_status']]; ?>"><?php echo $mdJS['onoff'][$sr[$i]['scrc_status']]; ?></span>
                            </td>
                            <td><?php echo $sr[$ai]['scrc_id']; ?></td>
                            <td><?php echo $sr[$ai]['scrc_code'] ?: '未定'; ?></td>
                            <td><?php echo $mdJS['scrctype'][$sr[$ai]['scrc_type']]; ?></td>
                            <td><?php 
                              if(!empty($sr[$ai]['scrc_district'])){
                                $distJS = json_decode($sr[$ai]['scrc_district']);
                                $distText = "";
                                foreach($distJS as $key => $value){
                                  $distText .= $locJS[$sr[$ai]['scrc_city']]['district'][$value]." ";
                                }
                                $getDistTitle = $locJS[$sr[$ai]['scrc_city']]['text'].' '.substr($distText, 0, -1);
                                echo $getDistTitle ?: '';
                                //<i class="fa-solid fa-location-dot"></i> point
                                //<i class="fa-solid fa-circle-dot"></i> point
                                //<i class="fa-solid fa-location-dot"></i> point
                                //<i class="fa-solid fa-genderless"></i> point
                                //**<i class="fa-light fa-draw-polygon"></i> polygon
                                //**<i class="fa-light fa-object-ungroup"></i>multipolygon
                                //<i class="fa-light fa-circle-nodes"></i> circle
                                //**<i class="fa-light fa-share-nodes"></i> line
                                //<i class="fa-light fa-vector-square"></i> square
                                
                              } 
                            ?></td>
                            <td><b><?php echo $sr[$ai]['scrc_title']; ?></b></td>
                            <td><?php echo $sr[$ai]['scrc_circletitle']; ?></td>
                            <td style="text-align:center;"><i class="fas fa fa-solid fa-map-location-dot<?php echo @$sr[$ai]['scrc_coordinates']!==null ? '' : ' text-black-25'; ?>"></i></td>
                          </tr>
                          <?php $ai++;$totalNum++; } ?>
                        </tbody>
                        <tfoot>
                          <tr class="cyan">
                            <th>狀態</th>
                            <th>ID</th>
                            <th>代碼</th>
                            <th>種類</th>
                            <th>所屬行政區</th>
                            <th>名稱</th>
                            <th>簡稱/俗名</th>
                            <th>座標</th>
                          </tr>
                        </tfoot>
                      </table>

                    </div>
                    <?php $ci++;$totalNum++; } ?>
                    <!-- /分頁訊息傳遞 -->
                  </div>
              </div>
          </div>
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
              <div>指定搜尋</div>
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
    //動態顯示筆數
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
    var scrclistTB = [];
    $('[id^="dataTable_scrcList"]').each(function(index) {
      let thisID = $(this).attr('id');
      scrclistTB[index] = datatableCreate(thisID);
    });

    //外置表列內容搜尋框動作操作
    $('#dataSearch').on('keyup search input paste cut', function () {
      if ($(this).is(':focus') || this.value !== '') {
          scrclistTB.search(this.value).draw();
      }
    });
    //搜尋表格重置清除
    $('#searchReset').click(function() {
      if($('#dataSearch').val() != '') {
        $('#dataSearch').val('');
        scrclistTB.search('').draw();
      } else {
        $('#form_search').find('input').val('');
        $('#form_search').find('select').val('');
        //$("#searchDist,#searchScrc,#searchBC").find("option:gt(0)").remove();
      }
    });


  });
////////////////////////////////////////////////////////////////     
// ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
// -------------- 可接收include檔案要求後自定義動作 ------------- //
////////////////////////////////////////////////////////////////
  // 當「儲存」按鈕按下時的動作
  function doButAdd(){
    //window.location.replace('data_scrcdetail.php?currID=0&jumpData=' + $('#dataTable_bcList').data('jumpidx') + '&' + '<?php echo $searchURI;?>');
    window.location.replace('data_scrcdetail.php?currID=0');
  }
  // 當「重置」按鈕按下時的動作
  function doButReset(){
    if (!confirm('重整頁面將會強制自伺服器重新載入這個頁面，確定執行這個操作？！')) {
      return false;
    }
    location.reload(true);
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
