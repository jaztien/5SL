<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_XXXX');
define('PAGE_ITEM','ITEM_XXXX');
define('PAGE_ITEM2','ITEM_XXXX');
define('PAGE_OPANEL',9);
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//此頁必須載入的專用檔案
//require_once("XXXXXXXX");
//通用變數設定
$currID = currID;
//功能陣列建立，取自JSON檔案
//$colGet=file_get_contents("../jsonFiles/cols.json");
//$colJS = json_decode($colGet,true);
/*
$toColArr[5] = "A-";
$toColArr[8] = "B-";
$toColArr[9] = "C-";
$toColArr[12] = "D-";

$line[5] = "AAAA";
$line[8] = "BBBBB";
$line[9] = "CCCCCC";
$line[12] = "DDDDDDD";

foreach( $toColArr as $key => $value){
  $actArr[$value]=$line[$key];
}

//print_r($actArr);
echo $actArr['B-'];
*/
//專用變數設定
//$doSomething = isset($_POST['doSomething']) ? $_POST['doSomething'] : '';
//------------------- 創建展示圖檔及文件檔案陣列 ------------------------//
//功能陣列建立，取自JSON檔案
//$locGet=file_get_contents("../jsonFiles/locations.json");//若要給JAVA使用，必須去除 whitespace
//$locJS = json_decode($locGet,true);

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
	
  <title> Fifth Season Life 人生第五季 | XXX[本頁面簡名]XXX </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <!--
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-bs4/css/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-responsive/css/responsive.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-buttons/css/buttons.bootstrap4.css">
  -->
  <!-- Toastr 簡易提示訊息
  <link rel="stylesheet" href="../pluginFiles/toastr/toastr.min.css"> -->
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css">
<!-- -------------------------- /CSS Link -------------------------- -->
</head>
<body class="layout-navbar-fixed hold-transition sidebar-mini">
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
              <h4 class="m-0">XXX[本頁面命名]XXX</h4>
            </div>
            <div class="col-sm-5">
              <ol class="breadcrumb float-sm-right small">
                  <li class="breadcrumb-item">首頁</li>
                  <li class="breadcrumb-item">XXX[樹狀子層]XXX</li>
                  <li class="breadcrumb-item">XXX[樹狀子二層]XXX</li>
                <li class="breadcrumb-item active">XXX[樹狀本頁名稱]XXX</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <!-- /頁頭導覽條 -->
      <!-- 主要內容區域 ------------------------------------- -->
      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-3">
              <!-- Profile Image -->
              <div class="card card-info card-outline">
                <div class="card-body box-profile">

                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->
            <div class="col-md-9">
              <div class="card card-secondary">
                <!-- 分頁導航頭 -->
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#tabMenu1" data-index="1" data-toggle="tab" data-topbut="0">XXX[分頁一]XXX</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu2" data-index="2" data-toggle="tab" data-topbut="0">XXX[分頁二]XXX</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu3" data-index="3" data-toggle="tab" data-topbut="0">XXX[分頁三]XXX</a></li>
                  </ul>
                </div>
                <!-- /.分頁導航頭結尾 -->
                <div class="card-body">
                  <div class="tab-content">
                    <!-- 分頁一【XXXXXX】頁框 -->
                    <div class="tab-pane active" id="tabMenu1">
                      <div style="font-size:0.85em;line-height:1.4em;">
                        <i class="nav-icon fas fa fa-exclamation-circle" style="color:#DF8233;"></i>&nbsp;&nbsp;XXX[分頁一簡述]XXX
                      </div>
                      <!-- 分頁一主體內容 -->
                      <div class="card-body">
                        XXX[分頁一主體內容]XXX
                      </div>
                      <!-- /.分頁一主體內容結尾 -->
                    </div>
                    <!-- /分頁一【XXXXXX】頁框結尾 -->
                    <!-- 分頁二【XXXXXX】頁框 -->
                    <div class="tab-pane" id="tabMenu2">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                        <i class="nav-icon fas fa fa-exclamation-circle"></i>&nbsp;&nbsp;XXX[分頁二簡述]XXX
                      </div>
                      <!-- 分頁二主體內容 -->
                      <div class="card-body">
                        XXX[分頁二主體內容]XXX
                      </div>
                      <!-- /.分頁二主體內容結尾 -->
                    </div>
                    <!-- /分頁二【XXXXXX】頁框結尾 -->

                    <!-- 分頁三【XXXXXX】頁框 -->
                    <div class="tab-pane" id="tabMenu3">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                        <i class="nav-icon fas fa fa-exclamation-circle"></i>&nbsp;&nbsp;XXX[分頁三簡述]XXX
                      </div>
                      <!-- 分頁三主體內容 -->
                      <div class="card-body">
                        XXX[分頁三主體內容]XXX
                      </div>
                      <!-- /.分頁三主體內容結尾 -->
                    </div>
                    <!-- /分頁三【XXXXXX】頁框結尾 -->
                  </div>
                  <!-- /.tab-content -->
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

      </section>
      <!-- /主要內容區域結尾 ------------------------------------ -->
    </div>
    <!-- /頁內全頁內容結尾 -->

    <!-- jQuery -->
    <script src="../pluginFiles/jquery/jquery.min.js"></script>

    <!-- 右側功能控制區 -->
      <aside class="control-sidebar control-sidebar-dark">
        <!-- 功能區內容 ------------------------------------ -->

        <!-- /功能區內容 ----------------------------------- -->
      </aside>
    <!-- /右側功能控制區 -->
  </div>
  <!-- /全頁框 -->
<!-- ------------------------ Scripts Link [以下JS檔案連結可視情況增減] ------------------------ -->
<!-- jQuery is in front of footer file. -->
<!-- Bootstrap 4 -->
<!-- <script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script> -->
<!-- Bootstrap 5 -->
<script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
<!-- DataTables2 & Plugins -->
<!--
<script src="../pluginFiles/datatables/datatables/jquery.dataTables.min.js"></script>
<script src="../pluginFiles/datatables/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../pluginFiles/datatables/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
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
  //const colobj = $.parseJSON('<?php //echo func_stripewhite($colGet); ?>'); //若要給JAVA使用，必須去除 whitespace

////////////////////////////////////////////////////////////////
// -------------- JAVASCRIPTS FUNCTIONs 全域調用 -------------- //
// -------------- 可接收include檔案要求後自定義動作 -------------- //
////////////////////////////////////////////////////////////////
  function doReDirect(){
    window.history.back();
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
    //------------------------------------------------------
    //var csvlistTB = datatableCreate('XXX[表格ID]XXX');//建立表格
    //----------- 執行按鈕

    //-------- 加載完成後更改外框主頁中樹狀目錄開合狀態 --------//
    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  //////////////////////////////////////////////////////////////////
  });


</script>
</body>
</html>
