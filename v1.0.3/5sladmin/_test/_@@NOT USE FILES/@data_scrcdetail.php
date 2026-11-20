<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_CIRCLE');
define('PAGE_ITEM','ITEM_TOOLS');
define('PAGE_ITEM2','ITEM_DATAS');
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//
$scrcID=currID;
$tabIDX = !empty($_REQUEST['tabIDX'])?intval($_REQUEST['tabIDX']):1;
$pageMax = isset($_REQUEST['pageMax']) ? intval($_REQUEST['pageMax']) : 25;
$jumpData = isset($_REQUEST['jumpData']) ? intval($_REQUEST['jumpData']) : 1;
//
$backUrl = !empty($pageMax) ? '&pageMax='.$pageMax : '';
$backUrl = substr($backUrl,1);
//
$res = $DB->row("SELECT *, scrc_coordinates.Long AS lng, scrc_coordinates.Lat AS lat FROM socialcircle WHERE scrc_id=?",array($scrcID));

//$DB->closeConnection();
//代表圖像設定
if(!empty($scrcID)&&file_exists("../uploadFiles/scrc/scrcphoto_".$scrcID."_thumb.jpg")){
  $cropImgDisplay = 'show';
  $mainimgpath = '../uploadFiles/scrc/scrcphoto_'.$scrcID.'_thumb.jpg?'.time();
  $mainimgchk = 'scrcphoto_'.$scrcID.'_.jpg';
} else {
  $cropImgDisplay = 'hide';
  $mainimgpath = '../uploadFiles/imageFiles/nopic_400x300.jpg';
  $mainimgchk = "";
}

//功能陣列建立
// Get the contents of the JSON file
$mdJS=_fn_getCachedJsonData("../jsonFiles/merchandise.json");
$geJS=_fn_getCachedJsonData("../jsonFiles/general.json");
$locJS=_fn_getCachedJsonData("../jsonFiles/locations.json");
//--------------------------------------------
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
	
  <title> Fifth Season Life 人生第五季 | 商圈&聚落詳細資料 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="../pluginFiles/select2/css/select2.min.css">
  <link rel="stylesheet" href="../pluginFiles/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Leaflet Map -->
  <link rel="stylesheet" href="../pluginFiles/leaflet/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <!-- Dropzone JS style
  <link rel="stylesheet" href="../pluginFiles/dropzone/dropzone.css" /> -->
  <!-- Cropper JS style -->
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css<?php echo '?t='.time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.lightbox.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css<?php echo '?t='.time(); ?>">

<!-- -------------------------- /CSS Link -------------------------- -->
 <style>
    #map { height: 440px; }
</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <!-- processing waiting -->
  <div id="loadingMask" style="top:0;left:0;position:absolute;display:block;width:100%;height:100%;background-color:#2A2826;background-position: 50% 0;opacity: 0.75;margin:0;text-align: center; z-index:99999;display:none;">
    <div style="color:#FFFFFF;position: relative;top: 50%;transform: translate(0, -50%);">
      <img src="../imageFiles/loading2.gif" style="left:50%;top:50%;"><br>...
    </div>
  </div>
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
              <h4 class="m-0"><?php echo !empty($scrcID) ? "編輯" : "新增"; ?>商圈&聚落資料<span style="font-size:large;">【ID: <?php echo $scrcID; ?>】</span></h4>
              <div class="text-muted text-sm" style="margin-top:-6px;">data_scrcdetail.php</div>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">基礎數據&管理工具</li>
                <li class="breadcrumb-item">基礎結構數據</li>
                <li class="breadcrumb-item prevLink" alt="返回列表" title="返回列表">商圈&聚落紀錄</li>
                <li class="breadcrumb-item active">商圈&聚落詳細資料</li>
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
              <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                  <div class="text-center">
                    <!-- Image Area -->
                    <div class="image_area">
                      <i class="nav-icon fas fa-2x fa-trash-can cropImgTrash<?php echo " ".$cropImgDisplay; ?>" alt="刪除圖像" title="刪除圖像"></i>
                      <form method="post">
                        <label for="cropUploadImage">
                          <img id="avatarImage" name="avatarImage" class="img-responsive img-fluid img-rounded imgEDicon" src="<?php echo $mainimgpath; ?>" alt="代表圖像" title="代表圖像"/>
                          <div class="overlay">
                            <div class="overtext"><?php echo !empty($scrcID) ? "更改" : "新增"; ?>代表圖像</div>
                          </div>
                          <input type="file" name="image" class="image" id="cropUploadImage" style="display:none" crop-width="800" crop-height="600">
                        </label>
                      </form>
                    </div>
                    <!-- /Image Area -->
                  </div>
                  <h3 class="profile-title text-center"><?php echo $res['scrc_title'] ?? "&nbsp;"; ?></h3>
                  <p class="text-muted text-center"><?php echo $res['scrc_circletitle'] ?? "&nbsp;"; ?></p>

                  <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                      <b>類別</b> <a class="float-right"><?php echo $res['scrc_type'] ?? "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>狀態</b> <a class="float-right<?php echo (($res['scrc_status'] ?? null) === 0) ? " text-danger" : (($res['scrc_status'] ?? null) === 1 ? " text-success" : " text-warning"); ?>"><?php echo $mdJS['onoff'][$res['scrc_status'] ?? ''] ?? "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>編輯日期</b> <a class="float-right"><?php echo $res['scrc_published'] ?? "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>建立日期</b> <a class="float-right" style="color:black;"><b><?php echo $res['scrc_updated'] ?? "&nbsp;"; ?></b></a>
                    </li>
                  </ul>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->
            <div class="col-md-9">
              <div class="card card-secondary">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link<?php if($tabIDX==1) echo " active"; ?>" href="#tabMenu1" data-index="1" data-toggle="tab" data-topbut="1">商圈&聚落資料</a></li>
                    <li class="nav-item"><a class="nav-link<?php if($tabIDX==2) echo " active"; ?>" href="#tabMenu2" data-index="2" data-toggle="tab" data-topbut="0">位置圖資</a></li>
                  </ul>
                </div><!-- /.card-header -->
                <div class="card-body">
                  <div class="tab-content">
                    <!-- 第一分頁 資料修改 -->
                    <div class="tab-pane<?php if($tabIDX==1) echo " active"; ?>" id="tabMenu1">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;商圈聚落資料為課程商品的子選單內容，資料紀錄可實際刪除，但為求關聯資料的準確性，建議不再使用時改變狀態為「停用」即可。</div>
                      <hr>
                      <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/md_act_func.php" method="post" data-formmode="infoupdate" data-func="func_scrc_infoUpdate">
                        <input type="hidden" name="mainimgchk" id="mainimgchk" class="notSubmit" value="<?php echo $mainimgchk; ?>">
                        <div class="card-body" style="padding:0px;margin:0px;">
                          
                          <div class="form-group row">
                            <label for="scrc_title" class="col-sm-2 col-form-label">商圈抬頭</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="scrc_title" name="scrc_title" value="<?php echo !empty($res['scrc_title']) ? $res['scrc_title'] : ""; ?>" placeholder="商圈抬頭" data-min="2" data-required<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="scrc_circletitle" class="col-sm-2 col-form-label">商圈別名</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="scrc_circletitle" name="scrc_circletitle" value="<?php echo !empty($res['scrc_circletitle']) ? $res['scrc_circletitle'] : ""; ?>" placeholder="商圈別名"<?php echo NreadOnly; ?>>
                            </div>
                            <label for="scrc_code" class="col-sm-2 col-form-label">代碼</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="scrc_code" name="scrc_code" value="<?php echo !empty($res['scrc_code']) ? $res['scrc_code'] : ""; ?>" placeholder="代碼"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="scrc_type" class="col-sm-2 col-form-label">商圈分類</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="scrc_type" name="scrc_type" data-required<?php echo NdisAble; ?>>
                                <?php
                                  foreach($mdJS['scrctype'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if(!empty($res['scrc_type']) && $res['scrc_type']==$key) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                            <label for="scrc_public" class="col-sm-2 col-form-label">政府立案</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="scrc_public" name="scrc_public"<?php echo NdisAble; ?>>
                                <?php
                                  foreach($mdJS['yesorno'] AS $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if(!empty($res['scrc_public']) && $res['scrc_public']==$key) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          <hr class="grouphr">
                          <div class="groupfield">坐落位置設定</div>
                          <div class="form-group row">
                            <label for="scrc_city" class="col-sm-2 col-form-label">城市區劃</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="scrc_city" name="scrc_city" placeholder="城市區劃" data-placeholder="請選擇城市區劃" data-select="city" data-required<?php echo NdisAble; ?>>
                                <option value="">不限城市</option>
                                <?php
                                foreach($locJS as $key => $value){
                                  echo "<option value='".$value['value']."'";
                                  if(!empty($res['scrc_city']) && $res['scrc_city']==$value['value']) { echo " selected"; }
                                  echo ">".$value['text']."</option>";
                                }
                                ?>
                              </select>
                            </div>
                            <div class="col-sm-6 select2-cyan">
                              <select class="select2" id="scrc_district" name="scrc_district" multiple="multiple" placeholder="行政區劃" data-placeholder="請選擇行政區劃" dist-fromcity="scrc_city" data-select="dist" list-bc="1" list-circ="0" style="width: 100%;" data-required<?php echo NdisAble; ?>>
                              <?php
                                $mbsJS=!empty($res['scrc_district']) ? json_decode($res['scrc_district']) : array();
                                if(!empty($res['scrc_city'])) {
                                    //CHARINDEX
                                    //$cityJS=$locJS[$cityIDX]['district'];
                                    foreach($locJS[$res['scrc_city']]['district'] as $key => $value){
                                        echo "<option value='".$key."'"; 
                                        if(!empty($res['scrc_district']) && in_array($key, $mbsJS)) { echo " selected"; }
                                        echo ">".$value."</option>";
                                    }
                                } else {
                                    echo "<option value='' selected>--</option>";
                                }
                              ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="scrc_lng" class="col-sm-2 col-form-label">縱向座標</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">經度</span></div>
                                <input type="text" class="form-control notSubmit" id="scrc_lng" name="scrc_lng" value="<?php echo !empty($res['lng']) ? $res['lng'] : ""; ?>" placeholder="經度"<?php echo NreadOnly; ?>>
                                <div class="input-group-append"><span class="input-group-text">度</span></div>
                              </div>
                            </div>
                            <label for="scrc_lat" class="col-sm-2 col-form-label">橫向座標</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">緯度</span></div>
                                <input type="text" class="form-control notSubmit" id="scrc_lat" name="scrc_lat" value="<?php echo !empty($res['lat']) ? $res['lat'] : ""; ?>" placeholder="緯度"<?php echo NreadOnly; ?>>
                                <div class="input-group-append"><span class="input-group-text">度</span></div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="scrc_polygon" class="col-sm-2 col-form-label">座標範圍</label>
                            <div class="col-sm-10">
                              <textarea class="form-control notSubmit" rows="3" id="scrc_polygon" name="scrc_polygon" placeholder="座標範圍"<?php echo NreadOnly; ?>></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="scrc_city" class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-10 text-sm text-muted">
                              <i class="nav-icon fas fa fa-exclamation-circle" style="margin-left:-20px;"></i>&nbsp;&nbsp;經緯度座標可為空，其值將寫入 Geography 形態欄位，必須經由轉換不可直接讀寫。座標範圍為多組平面座標組合而成的不規則範圍。
                            </div>
                          </div>
                          <hr class="grouphr">
                          <div class="groupfield">系統設定</div>
                          <div class="form-group row">
                            <label for="scrc_status" class="col-sm-2 col-form-label">啟用狀態</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="scrc_status" name="scrc_status"<?php echo NdisAble; ?>>
                                <?php
                                  foreach($mdJS['onoff'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(!empty($res['scrc_status']) && $res['scrc_status']==$key) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                        </div>
                        <!-- /.card-body -->
                      </form>
                    </div>
                    <!-- /第一分頁 商圈資料 -->
                    <!-- 第二分頁 位置圖資 -->
                    <div class="tab-pane<?php if($tabIDX==2) echo " active"; ?>" id="tabMenu2">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;xxxx</div>
                      <hr>

                <div class="card-header">
                  <h3 class="card-title">地圖顯示</h3>
                  <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body box-profile">
                  <div id="map"></div>
                </div>


                    </div>
                    <!-- /第二分頁 位置圖資 -->
                  </div>
                  <!-- /.tab-content -->
                </div><!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div><!-- /.container-fluid -->

        <!-- -------------------- 裁切頭像彈出視窗 -------------------- -->
        <?php include('include_avatarmodal.php'); ?>
        <!-- -------------------- /裁切頭像彈出視窗 -------------------- -->
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
<!-- Select2 -->
<script src="../pluginFiles/select2/js/select2.full.min.js"></script>
<!-- ezValidation -->
<script src="../pluginFiles/validator/validator.js<?php echo '?t='.time(); ?>"></script>
<!-- Leaflet Map -->
<script src="../pluginFiles/leaflet/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<!-- Dropzone JS
<script src="../pluginFiles/dropzone/dropzone.js"></script> -->
<!-- Cropper JS -->
<script src="../pluginFiles/cropper/cropper.js"></script>
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<!-- Custom JS style -->
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/fileupload.js<?php echo '?t='.time(); ?>"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

<!-- ------------------------ /Scripts Link ------------------------ -->
<script type="text/javascript">
  var mainObj = <?php echo json_encode($mdJS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
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
    //////////////////// OSM 地圖顯示 ///////////////////////////
    <?php if(!empty(@$res['lat']) && !empty(@$res['lng'])) { ?>
    var map = L.map('map').setView([<?php echo @$res['lat']; ?>, <?php echo @$res['lng']; ?>], 17);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
    var marker = L.marker([<?php echo @$res['lat']; ?>, <?php echo @$res['lng']; ?>]).addTo(map);
    <?php } ?>
    //-----------------------------------------
    $('.select2').select2();
/////////////////////////////////////////////////////////////
  // 內頁右上方小導航返回列表頁
  $('li.prevLink').on('click', function(){
    window.location.replace(getCurrentURI('<?php echo $backUrl; ?>','data_circlelist.php'));
  });

});
  ////////////////////////////////////////////////////////////////     
  // ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
  // -------------- 可接收include檔案要求後自定義動作 ------------- //
  ////////////////////////////////////////////////////////////////
  // 當「儲存」按鈕按下時的動作
  function doButSave(){
  var frm = $('#form_'+$('.tab-pane.active').prop("id"));
  var accE = frm.data('func');
	var getValid = Validator.validate(frm);
	if(!getValid){
		return false;
	} else {
		ajaxPOST(frm);
	}
  }
  // 當「重置」按鈕按下時的動作
  function doButReset(){
    if (!confirm('重整頁面將會強制自伺服器重新載入這個頁面，確定執行這個操作？！')) {
      return false;
    }
    location.reload(true);
  }
  // 當「刪除」按鈕按下時的動作
  function doButDelete(){
    $.ajax({
            type: 'post',
            url: '../funcFiles/md_act_func.php',
            data: {
              'arrDatA': {}, 
              'formAcce':'func_scrc_delete',
              'token': getTokenVal(), 
              'scrcID':'<?php echo $scrcID; ?>'
            },
            dataType: 'json',
            async: false,
            beforeSend: function () {
              if(!confirm('刪除紀錄成功後將無法回復，確定執行這個操作？！')){ return false; }
              else {
                  let recomF = prompt("請輸入安全驗證碼");
                  if (recomF !== "我是豬") {
                    return false;
                  }
              }
            },
          success: function (data) {
              console.log(data);
              window.parent.toastr[data.msgType](data.msgStr);
              if(data['RTN']==1){
                window.location.replace("data_circlelist.php?<?php echo $backUrl; ?>");
              }
            },
            error: function (data) {
              console.log(data);
              window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
            }
        });
  }

  ///////////////////////////// AJAX 表單處理 ////////////////////////////////
  function ajaxPOST(frm){
    var frm = $('#form_tabMenu1');
    var accE = frm.data('func');
    var url = frm.prop('action');
    var typE = frm.prop('method');
    var datA = getFormObjects(frm);
    //console.log(datA);
    $.ajax({
        type: typE,
        url: url,
        data: {
          'arrDatA':datA, 
          'formAcce':accE,
          'scrc_lng': $('#scrc_lng').val(),
          'scrc_lat': $('#scrc_lat').val(), 
          'imgUp': cropImage64,
          'token': getTokenVal(), 
          'scrcID':'<?php echo $scrcID; ?>'
        },
        dataType: 'json',
        //async: false, 
      success: function (data) {
          console.log(data);
          if(accE=='func_scrc_infoUpdate'){
              if (data['RTN']==1) {
                if(data['newID'] && parseInt(data['newID'])>0){
                  window.location.replace("data_scrcdetail.php?currID="+data['newID']+'&<?php echo $backUrl; ?>');
                  return true;
                }
                var newType = mainObj['scrctype'][$('#scrc_type').val()];
                var newSta = mainObj['onoff'][$('#scrc_status').val()];
                switch ($('#scrc_status').val()){
                  case '0': 
                    $('.box-profile > ul > li:nth-child(2) > a').removeClass( "text-success" ).removeClass( "text-warning" ).addClass( "text-danger" );
                  break;
                  case '1': 
                    $('.box-profile > ul > li:nth-child(2) > a').removeClass( "text-warning" ).removeClass( "text-danger" ).addClass( "text-success" );
                  break;
                }
                $('.profile-title').html($('#scrc_title').val());
                $('.profile-title + p').html($('#scrc_circletitle').val());
                $('.box-profile > ul > li:nth-child(1) > a').html(newType);
                $('.box-profile > ul > li:nth-child(2) > a').html(newSta);
                $('.box-profile > ul > li:nth-child(3) > a').html(data['upTime']);
                doSerialize();
              }
            }
            window.parent.toastr[data['msgType']](data['msgStr']);
        },
        error: function (data) {
          console.log(data);
          window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
        }
    });
  }

</script>
</body>
</html>
