<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_NODES');
define('PAGE_ITEM','ITEM_TOOLS');
define('PAGE_ITEM2','ITEM_DATAS');
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//URI鏈結內容
$pageMax = isset($_REQUEST['pageMax']) ? intval($_REQUEST['pageMax']) : 25;
$currPage = isset($_REQUEST['currPage']) ? intval($_REQUEST['currPage']) : 0;
$jumpTo = ($pageMax*$currPage);
//
$searchURI = "pageMax=".$pageMax;
//JSON 檔案引用

//
$topARR=array();
$tn_a = $DB->query("SELECT tn_id, tn_title FROM trafficnode WHERE tn_depth=? AND tn_status=?",array(1,1));
$i=0;
while($i<count($tn_a)){
  $topARR[$i]=array('id'=>$tn_a[$i]['tn_id'],'title'=>$tn_a[$i]['tn_title']);
  $i++;
}
$tnID = (!empty($_GET['tnID'])) ? $_GET['tnID'] : $topARR[0]['id'];
function getStatus($sta){
  switch($sta){
    case '0': return '停用'; break;
    case '1': return '運營中'; break;
    case '2': return '施工中'; break;
    case '3': return '規劃中'; break;
    default: return '停用'; break;
  }
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
	
  <title> Fifth Season Life 人生第五季 | 交通節點資料表 </title>
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
            <div class="col-sm-7">
              <h4 class="m-0">交通節點資料表 <span class="m-0 font-weight-light text-md">全國主要生活交通站點（運營中&施工中）資料一覽表（供範圍搜尋使用）</span></h4>
              <div class="text-muted text-sm" style="margin-top:-6px;">data_trafficnodes.php</div>
              
            </div>
            <div class="col-sm-5">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">基礎數據&管理工具</li>
                <li class="breadcrumb-item">基礎結構數據</li>
                <li class="breadcrumb-item active">交通節點資料表</li>
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
            $i=0;
            while($i<count($topARR)){
            ?>
            <div class="col-2">
              <button type="button" class="btn btn-block btn-outline-info<?php echo ($tnID==$topARR[$i]['id']) ? ' active' : ''; ?>" onClick="<?php echo ($tnID!=$topARR[$i]['id']) ? "window.location.replace('?tnID=".$topARR[$i]['id']."');" : "return false;"; ?>"><?php echo $topARR[$i]['title']; ?></button>
            </div>
            <?php $i++; } ?>
          </div>
        <?php 
          if($tnID==1 || $tnID==4){ 
            $tn_b = $DB->query("SELECT tn_id, tn_title FROM trafficnode WHERE tn_depth=? AND tn_status = ? AND tn_pid=?",array(2,1,$tnID));
            $nodeID = (!empty($_REQUEST['nodeID'])) ? $_REQUEST['nodeID'] : $tn_b[0]['tn_id'];
            if($tnID==1){
          ?>
          <div class="row pb-2">
            <?php
            $i=0;
            while($i<count($tn_b)){
            ?>
            <div class="col-2">
              <button type="button" class="btn btn-block btn-outline-primary<?php echo ($nodeID==$tn_b[$i]['tn_id']) ? ' active' : ''; ?>" onClick="<?php echo ($nodeID!=$tn_b[$i]['tn_id']) ? "window.location.replace('?tnID=".$tnID."&nodeID=".$tn_b[$i]['tn_id']."');" : "return false;"; ?>"><?php echo $tn_b[$i]['tn_title']; ?></button>
            </div>
            <?php $i++; } ?>
          </div>
        <?php } else { $nodeID=$tnID; }?>
          <div class="card card-info card-tabs">
            <div class="card-header p-0 pt-1">
              <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                <?php 
                $i=0;
                $areaARR=array();
                $tn_c = $DB->query("SELECT tn_id, tn_title, tn_entitle FROM trafficnode WHERE tn_status=? AND tn_pid=?",array(1,$nodeID));
                //$cityARR[$i]=array('pcode'=>$loc_b[$i]['code']);
                while($i<count($tn_c)){
                  $lineARR[$i]=array('id'=>$tn_c[$i]['tn_id'],'title'=>$tn_c[$i]['tn_title'],'title2'=>$tn_c[$i]['tn_entitle']);
                ?>
                <li class="nav-item"><a class="nav-link<?php echo ($i==0) ? ' active' : ''; ?>" href="#tnTab<?php echo $tn_c[$i]['tn_id']; ?>" data-toggle="pill" role="tab" aria-controls="tnTab<?php echo $tn_c[$i]['tn_id']; ?>" aria-selected="true"><?php echo $tn_c[$i]['tn_title']; ?></a></li>
                <?php $i++; } ?>
              </ul>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div class="tab-content">
                <!-- 分頁訊息傳遞 -->
                <?php
                  $ci=0;
                  while($ci<count($lineARR)){
                    $tn_node = $DB->query("SELECT tn_id, tn_title, tn_entitle, tn_code, tn_node, tn_mode, tn_status FROM trafficnode WHERE tn_status>=? AND tn_pid=?",array(1,$lineARR[$ci]['id']));
                ?>
                <div class="tab-pane<?php echo ($ci==0) ? ' active' : ''; ?>" id="tnTab<?php echo $lineARR[$ci]['id'];?>">
                <h5><?php echo $lineARR[$ci]['title2'];?></h5>
                  <table id="tnTableList<?php echo $lineARR[$ci]['id'];?>" class="table table-bordered table-striped table-hover">
                    <thead>
                      <tr class="lightgray">
                        <th>代碼</th>
                        <?php if($tnID==4) echo '<th class="text-right">里程</th>'; ?>
                        <th>站點名稱</th>
                        <th>別名</th>
                        <th>交會點</th>
                        <th>站體模式</th>
                        <th>啟用狀態</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $ai=0;
                        while($ai<count($tn_node)){
                      ?>
                      <tr 
                        style="<?php if($tn_node[$ai]['tn_status']>1) echo 'color:#BBBBBB;'; ?>cursor:pointer;" onclick="
                        var daTa = { 'adID': '<?php echo $tn_node[$ai]['tn_id']; ?>' };
                        postData('data_locedit.php',daTa);
                        ">
                        <td><?php echo $tn_node[$ai]['tn_code']; ?></td>
                        <?php if($tnID==4) echo '<td class="text-right"><b>'.explode('N',$tn_node[$ai]['tn_code'])[1].'</b> km</td>'; ?>
                        <td><b><?php echo $tn_node[$ai]['tn_title']; ?></b></td>
                        <td><?php echo $tn_node[$ai]['tn_entitle']; ?></td>
                        <td><?php echo $tn_node[$ai]['tn_node']; ?></td>
                        <td><?php echo (!empty($tn_node[$ai]['tn_mode'])&&$tn_node[$ai]['tn_mode']==1) ? '高架' : '地面'; ?></td>
                        <td><?php echo getStatus($tn_node[$ai]['tn_status']); ?></td>
                      </tr>
                      <?php $ai++; } ?>
                    </tbody>
                    <tfoot>
                      <tr class="lightgray">
                        <th>代碼</th>
                        <?php if($tnID==4) echo '<th class="text-right">里程</th>'; ?>
                        <th>站點名稱</th>
                        <th>別名</th>
                        <th>交會點</th>
                        <th>站體模式</th>
                        <th>啟用狀態</th>
                      </tr>
                    </tfoot>
                  </table>

                </div>
                <?php $ci++; } ?>
                <!-- /分頁訊息傳遞 -->
              </div>
            </div>
            <?php } else if($tnID==2 || $tnID==3) { ?>

            <div class="card card-info" style="border:1px solid #17a2b8;">
              <div class="card-header">
                <h3 class="card-title"><?php echo $topARR[$tnID-1]['title']; ?>列表</h3>
              </div>
              <div class="card-body">
                <table id="tnTableList<?php echo $tnID;?>" class="table table-bordered table-striped table-hover" data-butClass="cyan" data-pagemax="<?php echo $pageMax; ?>" data-jumpto="<?php echo $jumpTo; ?>" data-dirto="data_tnedit.php" data-search but-addnew>
                  <thead>
                    <tr class="lightgray">
                      <th>代碼</th>
                      <th>站點名稱</th>
                      <th>別名</th>
                      <th>交會點</th>
                      <th>站體模式</th>
                      <th>啟用狀態</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $tn_node = $DB->query("SELECT tn_id, tn_title, tn_entitle, tn_code, tn_node, tn_mode, tn_status FROM trafficnode WHERE tn_status>=? AND tn_pid=?",array(1,$tnID));
                      $ai=0;
                      while($ai<count($tn_node)){
                    ?>
                      <tr 
                        style="<?php if($tn_node[$ai]['tn_status']>1) echo 'color:#BBBBBB;'; ?>cursor:pointer;" onclick="
                        var daTa = { 'adID': '<?php echo $tn_node[$ai]['tn_id']; ?>' };
                        postData('data_locedit.php',daTa);
                        ">
                      <td><?php echo $tn_node[$ai]['tn_code']; ?></td>
                      <td><b><?php echo $tn_node[$ai]['tn_title']; ?></b></td>
                      <td><?php echo $tn_node[$ai]['tn_entitle']; ?></td>
                      <td><?php echo $tn_node[$ai]['tn_node']; ?></td>
                      <td><?php echo (!empty($tn_node[$ai]['tn_mode'])&&$tn_node[$ai]['tn_mode']==1) ? '高架' : '地面'; ?></td>
                      <td><?php echo getStatus($tn_node[$ai]['tn_status']); ?></td>
                    </tr>
                    <?php $ai++; } ?>
                  </tbody>
                  <tfoot>
                    <tr class="lightgray">
                      <th>代碼</th>
                      <th>站點名稱</th>
                      <th>別名</th>
                      <th>交會點</th>
                      <th>站體模式</th>
                      <th>啟用狀態</th>
                    </tr>
                  </tfoot>
                </table>


              </div>
            </div>

            <?php } else echo '<h6>尚未規劃......</h6>'; ?>
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
<script src="../scriptFiles/page.basic.js<?php echo '?t='.time(); ?>"></script>
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
    var tnlistTB = [];
    $('[id^="tnTableList"]').each(function(index) {
      let thisID = $(this).attr('id');
      tnlistTB[index] = datatableCreate(thisID);
    });
    /*
    $('[id^="tnTableList"]').DataTable({
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
    }).buttons().container().appendTo('#tnTableList_wrapper .col-md-6:eq(0)');
    //$('#tnTableList_wrapper').removeClass( 'display' ).addClass('table table-striped table-bordered');
    */

});
////////////////////////////////////////////////////////////////     
// ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
// -------------- 可接收include檔案要求後自定義動作 ------------- //
////////////////////////////////////////////////////////////////
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
