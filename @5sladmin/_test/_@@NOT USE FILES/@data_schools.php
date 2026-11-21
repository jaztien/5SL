<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_SCHOOL');
define('PAGE_ITEM','ITEM_TOOLS');
define('PAGE_ITEM2','ITEM_DATAS');
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//功能陣列建立，取自JSON檔案
$geJS=_fn_getCachedJsonData("../jsonFiles/general.json");//若要給JAVA使用，必須去除 whitespace
$partJS=_fn_getCachedJsonData("../jsonFiles/twparts.json");//若要給JAVA使用，必須去除 whitespace
$locJS=_fn_getCachedJsonData("../jsonFiles/locations.json");//若要給JAVA使用，必須去除 whitespace
//
$topARR=$partJS;
$locCode = (!empty($_REQUEST['locCode'])) ? $_REQUEST['locCode'] :0;
//
$pageMax = isset($_REQUEST['pageMax']) ? intval($_REQUEST['pageMax']) : 25;
$currPage = isset($_REQUEST['currPage']) ? intval($_REQUEST['currPage']) : 0;
$jumpTo = ($pageMax*$currPage);
//
$searchURI = "locCode=".$locCode."&pageMax=".$pageMax;
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
	
  <title> Fifth Season Life 人生第五季 | 教育機構資料表 </title>
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
              <h4 class="m-0">教育機構資料表 <span class="m-0 font-weight-light text-md">中華民國縣市區域教育機構一覽表（可供選單使用）</span></h4>
              <div class="text-muted text-sm" style="margin-top:-6px;">data_schools.php</div>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">基礎數據&管理工具</li>
                <li class="breadcrumb-item">基礎結構數據</li>
                <li class="breadcrumb-item active">教育機構資料表</li>
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
                      $ci=0;
                      while($ci<count($cityARR)){
                        $loc_area = $DB->query("SELECT * FROM schools WHERE sch_city=? ORDER BY sch_district,sch_title ASC", array($cityARR[$ci]['code']));

                    ?>
                    <div class="tab-pane<?php echo ($ci==0) ? ' active' : ''; ?>" id="locTab<?php echo $cityARR[$ci]['code'];?>">
                      <table id="dataTable_schlist<?php echo $cityARR[$ci]['code'];?>" class="table table-bordered table-striped table-hover" data-butClass="cyan" data-pagemax="<?php echo $pageMax; ?>" data-jumpto="<?php echo $jumpTo; ?>" data-dirto="data_schdetail.php" data-search but-addnew>
                        <thead>
                          <tr class="cyan">
                            <th>啟用狀態</th>
                            <th>代碼</th>
                            <th>等級</th>
                            <th>學校種類</th>
                            <th>所在區域</th>
                            <th>公私立</th>
                            <th>學校名稱</th>
                            <th>別名</th>
                            <th>座標</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                            $ai=0;
                            while($ai<count($loc_area)){
                          ?>
                          <tr style="cursor:pointer;" data-currid="<?php echo $loc_area[$ai]['sch_id']; ?>">
                            <td><?php echo ($loc_area[$ai]['sch_status']==1) ? '啟用' : '停用'; ?></td>
                            <td><?php echo $loc_area[$ai]['sch_code']; ?></td>
                            <td><?php echo $geJS['schgrade'][$loc_area[$ai]['sch_grade']]; ?></td>
                            <td><?php echo $geJS['schtype'][$loc_area[$ai]['sch_grade']][$loc_area[$ai]['sch_type']]; ?></td>
                            <td><?php echo $locJS[$loc_area[$ai]['sch_city']]['district'][$loc_area[$ai]['sch_district']]; ?></td>
                            <td><?php echo $geJS['schpubpri'][$loc_area[$ai]['sch_mtype']]; ?></td>
                            <td><b><?php echo $loc_area[$ai]['sch_title']; ?></b></td>
                            <td><?php echo $loc_area[$ai]['sch_entitle']; ?></td>
                            <td><i class="fas fa-2x fa-solid fa-map-location-dot<?php echo @$loc_area[$ai]['sch_coordinates']!==null ? '' : ' text-black-25'; ?>"></i></td>

                          </tr>
                          <?php $ai++; } ?>
                        </tbody>
                        <tfoot>
                          <tr class="cyan">
                            <th>啟用狀態</th>
                            <th>代碼</th>
                            <th>等級</th>
                            <th>學校種類</th>
                            <th>所在區域</th>
                            <th>公私立</th>
                            <th>學校名稱</th>
                            <th>別名</th>
                            <th>座標</th>
                          </tr>
                        </tfoot>
                      </table>

                    </div>
                    <?php $ci++; } ?>
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

    <!-- 下方訊息區 -->
    <?php //include_once('include_footer.php'); ?>
<!-- /下方訊息區 -->
    <!-- 右側功能控制區 -->
      <aside class="control-sidebar control-sidebar-dark">
        <!-- 功能區內容 ------------------------------------ -->

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
<script src="../pluginFiles/datatables/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../pluginFiles/datatables/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
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
    var schlistTB = [];
    //var dai=0;
    $('[id^="dataTable_schlist"]').each(function(index) {
      let thisID = $(this).attr('id');
      schlistTB[index] = datatableCreate(thisID);
    });

    //var rulelistTB = datatableGroup('dataTable_schlist',25,'gray');


    /*
    $('[id^="locTableList"]').DataTable({
      "paging": true,
      "pagingType": "full_numbers",
      "lengthChange": false,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
      //"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
      //"select": true,
      //"keys": false,
      //"colReorder": true,
      language: {
        "lengthMenu": "顯示 _MENU_ 筆搜尋紀錄",
        "sProcessing": "處理中...",
        "sZeroRecords": "没有匹配结果",
        "sInfo": "目前有 _MAX_ 筆搜尋紀錄",
        "sInfoEmpty": "目前共有 0 筆紀錄",
        "sInfoFiltered": " ",
        "sInfoPostFix": "",
        "sSearch": "在搜尋紀錄中尋找:",
        "sUrl": "",
        "sEmptyTable": "尚未有資料紀錄存在",
        "sLoadingRecords": "載入資料中...",
        "sInfoThousands": ",",
        "oPaginate": {
            "sFirst": "首頁",
            "sPrevious": "上一頁",
            "sNext": "下一頁",
            "sLast": "末頁"
        },
        "order": [[0, "desc"]],
        "oAria": {
            "sSortAscending": ": 以升序排列此列",
            "sSortDescending": ": 以降序排列此列"
        }
      },
        buttons: {
          "buttons": [{
              className:'listBut',
                text: ' 新增管理者',
                action: function ( e ) {
                    $(location).attr('href','ad_addnew.php');
                }
            }]
        }
    }).buttons().container().appendTo('#locTableList_wrapper .col-md-6:eq(0)');
    //$('#locTableList_wrapper').removeClass( 'display' ).addClass('table table-striped table-bordered');
*/

});

</script>

</body>
</html>
