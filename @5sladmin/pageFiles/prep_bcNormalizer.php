<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_BCANA');
define('PAGE_ITEM','ITEM_PREPARE');
define('PAGE_ITEM2','ITEM_NORMALIZER');
define('PAGE_OPANEL',9);
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//此頁必須載入的專用檔案
require_once("../classFiles/normalizer/bcNormalize.class.php");
//通用變數設定
$currID = currID;
$tabIDX = !empty($_REQUEST['tabIDX'])?intval($_REQUEST['tabIDX']):1;
$backUrl = !empty($pageMax) ? '&pageMax='.$pageMax : '';
//專用變數設定
$doact = isset($_POST['doact']) ? $_POST['doact'] : '';
$modeldata = isset($_POST['modeldata']) ? $_POST['modeldata'] : '';
$dataact = isset($_POST['dataact']) ? $_POST['dataact'] : '';
$dataanchor = isset($_POST['dataanchor']) ? $_POST['dataanchor'] : '';
//------------------- 創建展示圖檔及文件檔案陣列 ------------------------//
//功能陣列建立，取自JSON檔案
$locJS=_fn_getCachedJsonData("../jsonFiles/locations.json");//若要給JAVA使用，必須去除 whitespace
$bcJS=_fn_getCachedJsonData("../jsonFiles/buildcase.json");
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
	
  <title> Fifth Season Life 人生第五季 | Buildcase Normalizer Test </title>
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
            <div class="col">
              <h4 class="m-0">建案名稱規則化分析</h4>
            </div>
            <div class="col">
              <ol class="breadcrumb float-sm-right small">
                  <li class="breadcrumb-item">首頁</li>
                  <li class="breadcrumb-item">資料後備作業管理</li>
                  <li class="breadcrumb-item">命名規則化分析工具</li>
                <li class="breadcrumb-item active">建案名稱規則化分析</li>
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
            <div class="col-md-12">
              <div class="card card-secondary">
                <!-- 分頁導航頭 -->
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#tabMenu1" data-index="1" data-toggle="tab" data-topbut="0">個別名稱分析</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu2" data-index="2" data-toggle="tab" data-topbut="0">資料庫紀錄分析</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu3" data-index="3" data-toggle="tab" data-topbut="0">XXX[分頁三]XXX</a></li>
                  </ul>
                </div>
                <!-- /.分頁導航頭結尾 -->
                <div class="card-body">
                  <div class="tab-content">
                    <!-- 分頁一【個別名稱分析】頁框 -->
                    <div class="tab-pane active" id="tabMenu1">
                      <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/prep_act_func.php" method="post" data-func="func_prep_singleana">
                        <div style="font-size:0.85em;line-height:1.4em;">
                          <i class="nav-icon fas fa fa-exclamation-circle" style="color:#DF8233;"></i>&nbsp;&nbsp;輸入建案名稱後即可進行個別名稱分析，分析結果將顯示在下方表格中，並同時列出相同或類似的建案紀錄，可複製利用、修改已有的建案紀錄或直接以「未完成」狀態新增至資料庫中，等待後續的資料完善工作。
                        </div>
                        <!-- 分頁一主體內容 -->
                        <div class="card-body">
                              <!-- 搜尋框 -->
                              <div class="card card-warning card-outline">
                                  <div class="card-body box-profile">

                                      <div class="form-group row">
                                          <div class="col-md-9">

                                              <div class="form-group row">
                                                  <label for="merch_city" class="col-sm-2 col-form-label">建案所在區域</label>
                                                  <div class="col-sm-4">
                                                      <select class="form-control" id="search_city" name="search_city" data-placeholder="請選擇所在城市" data-select="city"<?PHP echo NdisAble; ?>>
                                                          <option value="">-請選擇(非必選)-</option>
                                                          <?PHP
                                                          foreach($locJS as $key => $value){
                                                              echo "<option value='".$value['value']."'";
                                                              echo ">".$value['text']."</option>";
                                                          }
                                                          ?>
                                                      </select>
                                                  </div>
                                                  
                                                  <div class="col-sm-6">
                                                      <select class="form-control" id="search_district" name="search_district" data-placeholder="請選擇行政區劃" data-select="dist" dist-fromcity="search_city" list-bc="1" list-circ="1" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                                          <option value='0'>請選擇行政劃區(非必選)</option>
                                                      </select>
                                                  </div>
                                              </div>
                                              <div class="form-group row">
                                                  <label for="search_bcname" class="col-sm-2 col-form-label">建案名稱*</label>
                                                  <div class="col-sm-10">
                                                      <input type="text" class="form-control" id="search_bcname" name="search_bcname" value="" placeholder="請輸入建案名稱" data-required<?PHP echo NreadOnly; ?>>
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="col-md-3">
                                              <a id="but_doSingleAna" class="btn btn-app bg-danger" style="height: 92px;width: 100%;"> <!-- bg-info -->
                                                  <i class="fas fa-solid fa-file-import" style="padding-top: 16px;"></i> 執行分析
                                              </a>
                                          </div>
                                      </div>


                                  </div>
                              </div>
                              <!-- /.搜尋框結束 -->
                              <!-- 搜尋結果框 -->
                              <div id="resultBOX" class="card card-info" style="display: none;">
                                  <div class="card-header">資料分析結果</div>
                                  <div class="card-body box-profile">
                                    <div class="form-group row">
                                      <label for="rtn_bcnormalized" class="col-sm-2 col-form-label">正規化名稱</label>
                                      <div class="col-sm-10">
                                        <input type="text" class="form-control" id="rtn_bcnormalized" name="rtn_bcnormalized" style="background-color: #f5f5f5;font-color: blue;" value="" placeholder="請輸入正規化名稱" readonly>
                                      </div>
                                    </div>

                                    <div class="form-group row">
                                      <label for="rtn_investor" class="col-sm-2 col-form-label">建商簡稱</label>
                                      <div class="col-sm-5">
                                        <input type="text" class="form-control" id="rtn_investor" name="rtn_investor" value="" placeholder="請輸入建商簡稱">
                                      </div>
                                      <label for="rtn_bctype" class="col-sm-2 col-form-label">建型</label>
                                      <div class="col-sm-3">
                                        <select class="form-control" id="rtn_bctype" name="rtn_bctype">
                                        <?php
                                            echo '<option value="">請選擇建型</option>';
                                            foreach($bcJS['bcmode'] as $key => $value){
                                                if($key==''){ continue; }
                                                echo '<option value="'.$key.'">'.$value.'</option>';
                                            }
                                        ?>
                                        </select>
                                      </div>
                                    </div>

                                    <div class="form-group row">
                                      <label for="rtn_cmpname" class="col-sm-2 col-form-label">主要比對名稱</label>
                                      <div class="col-sm-5">
                                        <input type="text" class="form-control" id="rtn_cmpname" name="rtn_cmpname" value="" placeholder="請輸入主要比對名稱">
                                      </div>
                                      <label for="rtn_phase" class="col-sm-2 col-form-label">期數</label>
                                      <div class="col-sm-3">
                                        <input type="text" class="form-control" id="rtn_phase" name="rtn_phase" value="" placeholder="請輸入期數">
                                      </div>
                                    </div>

                                    <div class="form-group row">
                                      <label for="rtn_subzone" class="col-sm-2 col-form-label">子區</label>
                                      <div class="col-sm-2">
                                        <input type="text" class="form-control" id="rtn_subzone" name="rtn_subzone" value="" placeholder="請輸入子區">
                                      </div>
                                      <label for="rtn_subdist" class="col-sm-1 col-form-label">子分屬</label>
                                      <div class="col-sm-2">
                                        <input type="text" class="form-control" id="rtn_subdist" name="rtn_subdist" value="" placeholder="請輸入子分屬">
                                      </div>
                                      <label for="rtn_loc" class="col-sm-2 col-form-label">位置區分</label>
                                      <div class="col-sm-3">
                                        <input type="text" class="form-control" id="rtn_loc" name="rtn_loc" value="" placeholder="請輸入位置區分">
                                      </div>
                                    </div>

                                    <div class="form-group row">
                                      <label for="rtn_alias1" class="col-sm-2 col-form-label">次要比對別名1</label>
                                      <div class="col-sm-5">
                                        <input type="text" class="form-control" id="rtn_alias1" name="rtn_alias1" value="" placeholder="請輸入次要比對別名1">
                                      </div>
                                      <label for="rtn_alias2" class="col-sm-2 col-form-label">次要比對別名2</label>
                                      <div class="col-sm-3">
                                        <input type="text" class="form-control" id="rtn_alias2" name="rtn_alias2" value="" placeholder="請輸入次要比對別名2">
                                      </div>
                                    </div>
                                    <hr>
                                    <div class="form-group row">
                                      <label for="rtn_remark" class="col-sm-2 col-form-label">分析註記</label>
                                      <div id="rtn_remark" class="col-sm-10" style="padding-top: 6px;"></div>
                                    </div>
                                    <hr>
                                    <div class="form-group row">
                                      <label for="rtn_debuglogs" class="col-sm-2 col-form-label">Debug Logs</label>
                                      <div id="rtn_debuglogs" class="col-sm-10" style="padding-top: 6px;"></div>
                                    </div>


                                  </div>
                              </div>
                              <!-- /.搜尋結果框結束 -->
                              <!-- 相關紀錄列表框 -->
                              <div class="card card-success" style="display: none;">
                                  <div class="card-header">相關紀錄</div>
                                  <div class="card-body box-profile">

                                  </div>
                              </div>
                              <!-- /.相關紀錄列表框結束 -->

                        </div>
                        <!-- /.分頁一主體內容結尾 -->
                      </div>
                    </form>
                    <!-- /分頁一【個別名稱分析】頁框結尾 -->
                    <!-- 分頁二【資料庫紀錄分析】頁框 -->
                    <div class="tab-pane" id="tabMenu2">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                        <i class="nav-icon fas fa fa-exclamation-circle"></i>&nbsp;&nbsp;利用右側功能控制區輸入搜尋條件，並點選查詢按鈕即可進行查詢，查詢結果將透過分析機制析出結果，並顯示在下方表格中，可複製利用或直接修改更新，唯資料庫內容修改後將無法回復。
                      </div>
                      <!-- 分頁二主體內容 -->
                      <div class="card-body">
                        XXX[分頁二主體內容]XXX
                      </div>
                      <!-- /.分頁二主體內容結尾 -->
                    </div>
                    <!-- /分頁二【資料庫紀錄分析】頁框結尾 -->

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
<!-- ezValidation -->
<script src="../pluginFiles/validator/validator.js<?php echo '?t='.time(); ?>"></script>
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

<!-- ------------------------ /Scripts Link ------------------------ -->
<script type="text/javascript">

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
    $('#but_doSingleAna').click(function(){//bg-info bg-warning bg-danger
      var frm = $('#form_'+$('.tab-pane.active').prop("id"));
      var accE = frm.data('func');

      if(accE=='func_prep_singleana'){
        var getValid = Validator.validate(frm);
        if(!getValid){
          return false;
        } else {
          $.ajax({
              type: 'post',
              url: '../funcFiles/prep_act_func.php',
              data: {
                'formAcce': 'func_prep_singleana',
                'bcCity': $('#search_city').val(), 
                'bcDist': $('#search_district').val(),
                'bcTitle': $('#search_bcname').val(),
                'token': getTokenVal()
              },
              dataType: 'json',
              async: false, 
              beforeSend:function(){//送出前的動作
                  //if(!confirm('確定進行下一步？！')){return false;}
              },
            success: function (data) {
                window.parent.toastr[data['msgType']](data['msgStr']);
                if(data['RTN']==1){
                  //成功後回傳
                  console.log(data);
                  var rString = data['rString'];
                  $('#resultBOX').show();
                  $('#rtn_bcnormalized').val(rString['origFixed']);
                  $('#rtn_investor').val(rString['investorAbbr']);        
                  $('#rtn_bctype').val(rString['parseBCtype']);
                  $('#rtn_cmpname').val(rString['parseTitle']);
                  $('#rtn_phase').val(rString['parsePhase']);
                  $('#rtn_subzone').val(rString['parseArea']);
                  $('#rtn_subdist').val(rString['parseAreaExt']);
                  $('#rtn_loc').val(rString['parseLocated']);
                  $('#rtn_alias1').val(rString['parseAka1']);
                  $('#rtn_alias2').val(rString['parseAka2']);
                  $('#rtn_remark').html(rString['tmatches']);
                  $('#rtn_debuglogs').html(rString['tDebug']);
                } 
              },
              error: function (data) {
                console.log(data);
                window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
              }
          });
        }
      } else if(accE=='func_prep_databaseana'){
        //全資料分析作業
      }

    });




  });


  ////////////////////////////////////////////////////////////////     
  // ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
  // -------------- 可接收include檔案要求後自定義動作 ------------- //
  ////////////////////////////////////////////////////////////////
  function doReDirect(){
    window.history.back();
  }
  // 當「重置」按鈕按下時的動作
  function doButReset(){
    location.reload(true);
  }


</script>
</body>
</html>
