<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_SESSION');
define('PAGE_ITEM','ITEM_MERCH');
define('bkRedir',1);
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
require_once('../classFiles/eRanking.class.php');
//
$sessID=currID;
//
$res = DB->row("SELECT * FROM fsl_course_sessions WHERE sess_id=?",array($sessID));
$courseres = DB->row("SELECT * FROM fsl_courses WHERE course_id=?",array($res['course_id'] ?? 0));
$courseID = $courseres['course_id'] ?? 0;
//功能陣列建立
// Get the contents of the JSON file
$courseJS=_fn_getCachedJsonData("../jsonFiles/courses.json");
$geJS=_fn_getCachedJsonData("../jsonFiles/general.json");
//------------------- 取得並且定義必要參數 ------------------------//
$idForItem = 5;//對應 general.json["idfor"] ## 1-會員(廠商、專家…等)；2-課程商品；3-文章;4-服務；5-交易；7-加購項目
$idForJS = $geJS['idfor'][$idForItem];
$idForRateMode = $idForJS['ratemode'] ?? 5;

//--------------------- 評分計算 -----------------------
/*
$counT="SELECT AVG((rat_s1 + rat_s2 + rat_s3 + rat_s4 + rat_s5)/5) AS totAVE, AVG(rat_s1) AS S1, AVG(rat_s2) AS S2, AVG(rat_s3) AS S3, AVG(rat_s4) AS S4, AVG(rat_s5) AS S5 FROM fsl_rating WHERE rat_forid=? AND rat_for=?";
$cnt = DB->query($counT,array($courseID,5));
$S1 = !empty($cnt[0]['S1']) ? floatval($cnt[0]['S1']) : 0;
$S2 = !empty($cnt[0]['S2']) ? floatval($cnt[0]['S2']) : 0;
$S3 = !empty($cnt[0]['S3']) ? floatval($cnt[0]['S3']) : 0;
$S4 = !empty($cnt[0]['S4']) ? floatval($cnt[0]['S4']) : 0;
$S5 = !empty($cnt[0]['S5']) ? floatval($cnt[0]['S5']) : 0;
$totAVE = !empty($cnt[0]['totAVE']) ? floatval($cnt[0]['totAVE']) : 0;
$ratingItem = $geJS['idfor'][$idForItem]['rating']['1'].','.$geJS['idfor'][$idForItem]['rating']['2'].','.$geJS['idfor'][$idForItem]['rating']['3'].','.$geJS['idfor'][$idForItem]['rating']['4'].','.$geJS['idfor'][$idForItem]['rating']['5'];
$ratingData = $S1.','.$S2.','.$S3.','.$S4.','.$S5;
*/
//$S1 = $res['course_s1'] ?? 0;
$ratingItem = $geJS['idfor'][$idForItem]['rating'][0];
$totSum = $res['sess_ratetop'] ?? 0;
$totCount = $res['sess_ratebottom'] ?? 0;
$totAVE = $res['sess_s1'] ?? 0;
//代表圖像設定
if(!empty($sessID)&&file_exists("../uploadFiles/course/sessimg_".$sessID."_thumb.jpg")){
  $cropImgDisplay = 'show';
  $mainimgpath = '../uploadFiles/course/sessimg_'.$sessID.'_thumb.jpg?'.time();
  $mainimgchk = 'sessimg_'.$sessID.'_.jpg';
} else {
  $cropImgDisplay = 'hide';
  $mainimgpath = '../uploadFiles/imageFiles/nopic_400x300.jpg';
  $mainimgchk = "";
}
//
if (!empty($courseID) && file_exists("../uploadFiles/course/courseimg_{$courseID}_thumb.jpg")) {
$courseimgpath = "../uploadFiles/course/courseimg_{$courseID}_thumb.jpg";
} else {
    $courseimgpath = '../uploadFiles/imageFiles/nopic_400x300.jpg';
}
/*
if($res['sess_defaultimg'] == 1){
  $mainimgpath = $courseimgpath;
  $mainimgchk = !empty($courseimgpath) ? "courseimg_'.$courseID.'_.jpg" : "";
}
*/
$totalSess = 0;
$lessonCount = $res['sess_lesson'] ?? 0;
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
	
  <title> Fifth Season Life 人生第五季 | 課程梯次詳細資料 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Tempusdominus -->
  <link rel="stylesheet" href="../pluginFiles/tempusdominus/tempus-dominus.min.css">
  <!-- <link rel="stylesheet" href="../pluginFiles/icheck/icheck.css"> -->
  <!-- DataTables -->
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <!--
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-bs4/css/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-responsive/css/responsive.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-buttons/css/buttons.bootstrap4.css">
  -->
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="../pluginFiles/sweetalert2/sweetalert2.min.css">
  <!-- Dropzone JS style -->
  <link rel="stylesheet" href="../pluginFiles/dropzone/dropzone.css" />
  <!-- Cropper JS style -->
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <!-- suneditor -->
  <link rel="stylesheet" href="../pluginFiles/suneditor/dist/css/suneditor.min.css">
  <!-- codeMirror -->
  <link rel="stylesheet" href="../pluginFiles/codemirror/lib/codemirror.min.css">
  <!-- select2 -->
  <link rel="stylesheet" href="../pluginFiles/select2/css/select2.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css<?php echo '?t='.time(); ?>">
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css<?php echo '?t='.time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.lightbox.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css<?php echo '?t='.time(); ?>">

<!-- -------------------------- /CSS Link -------------------------- -->
<style>
  /*
    table#dataTable_enroList thead > tr:first-child > th{
      line-height:0px;
      background-color:#5F6E86;
      opacity: 0.85;
      color:#f8f9fa;
    }
    table#dataTable_enroList thead > tr:last-child > th{
      line-height:6px;
      background-color:#5F6E86;
      color:#f8f9fa;
    }
      */
  #thisStars .star {
      display: inline-block !important;
      font-size: 32px;       /* 👈 星星的大小，可依您的網頁設計自由調整 */
      margin-right: 2px;     /* 星星之間的微小間距 */
      letter-spacing: -2px;  /* 緊縮排版，讓星星看起來更精緻 */
  }

  /* 2. 滿星 (star-full)：繼承您原本的金黃色 */
  #thisStars .star-full {
      color: goldenrod !important;
  }

  /* 3. 空星 (star-empty)：強行覆蓋金色，染成質感的淺灰色 */
  #thisStars .star-empty {
      color: #e0e0e0 !important;
      -webkit-text-fill-color: #e0e0e0 !important; /* 確保手機端與各瀏覽器也有效 */
  }

  /* 4. 半星 (star-half)：核心魔術！強行切斷金色，啟用左邊金色、右邊灰色的精準切半 */
  #thisStars .star-half {
      background: linear-gradient(90deg, goldenrod 50%, #e0e0e0 50%) !important; /* 💡 50% 金色，50% 灰色 */
      -webkit-background-clip: text !important;          /* 將漸層背景裁切並鎖定在文字形狀內 */
      -webkit-text-fill-color: transparent !important;   /* 將原本的文字填滿色變透明，讓後方的漸層浮現 */
  }
  /* ------------------------------------- */
  .tooltip .tooltip-inner {
      background-color: #007bff !important; /* 改為您要的背景色 */
      color: #ffffff !important;            /* 改為您要的文字色 */
      font-weight: 500;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }

  /* 2. 更改小箭頭的顏色（必須針對四個方向分別設定） */
  .tooltip.bs-tooltip-top .arrow::before {
      border-top-color: #007bff !important; /* 上方箭頭 */
  }
  .tooltip.bs-tooltip-bottom .arrow::before {
      border-bottom-color: #007bff !important; /* 下方箭頭 */
  }
  .tooltip.bs-tooltip-left .arrow::before {
      border-left-color: #007bff !important; /* 左方箭頭 */
  }
  .tooltip.bs-tooltip-right .arrow::before {
      border-right-color: #007bff !important; /* 右方箭頭 */
  }
</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <input type="hidden" id="currID" name="currID" value="<?php echo currID; ?>">
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
            <div class="col-sm-6">
              <h4 class="m-0"><?php echo !empty($courseID) ? "編輯" : "新增"; ?>課程梯次資料<span class="tiny"> 課程梯次資料內容編輯</span></h4>
              
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                  <li class="breadcrumb-item">首頁</li>
                  <li class="breadcrumb-item">課程商品資料管理</li>
                  <li class="breadcrumb-item prevLink" alt="返回列表" title="返回列表" onClick="window.location.replace('course_sesslist.php?<?php //echo $backUrl; ?>');">課程梯次資料列表</li>
                <li class="breadcrumb-item active">課程梯次詳細資料</li>
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
                    <div class="image_area">
                        <img id="courseImage" name="courseImage" style="border-radius:10px;" class="img-responsive img-fluid imgEDicon" src="<?php echo $courseimgpath; ?>" alt="代表圖像" title="代表圖像"/>
                    </div>
                  </div>
                  <h5 class="profile-main text-center" style="margin-top:10px"><?php echo $courseres['course_title'] ?? "&nbsp;"; ?></h5>
                  <p class="text-muted text-center"><?php echo $courseres['course_subscript'] ?? "&nbsp;"; ?></p>

                  <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                      <b>種類</b> <a class="float-right"><?php echo $courseJS['type'][$courseres['course_type'] ?? null] ?? "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>狀態</b> <a class="float-right<?php echo !empty($courseID) && (($courseres['course_status'] ?? 0)==1) ? "" : " text-danger"; ?>"><?php echo !empty($courseID) ? $courseJS['status'][$courseres['course_status'] ?? null] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>建立日期</b> <a class="float-right" style="color:black;"><b><?php echo $courseres['course_updated'] ?? "&nbsp;"; ?></b></a>
                    </li>
                    <li class="list-group-item">
                      <b>編輯日期</b> <a class="float-right"><?php echo $courseres['course_published'] ?? "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>課程週期</b> <a class="float-right"><b><?php echo $courseres['course_lesson'] ?? 0; ?></b> 堂課</a>
                    </li>
                    <li class="list-group-item">
                      <b>課程時長</b> <a class="float-right"><b><?php echo $courseres['course_duration'] ?? 0; ?></b> 分鐘</a>
                    </li>
                    <li class="list-group-item">
                      <b>開班條件</b> <a class="float-right"><b><?php echo ($courseres['course_min'] ?? 0) ." ~ ". ($courseres['course_max'] ?? 0); ?></b> 人</a>
                    </li>
                    <li class="list-group-item">
                      <b>身份限制</b> <a class="float-right"><b><?php echo $courseJS['qualify'][$courseres['course_qualify']] ?? "不限"; ?></b></a>
                    </li>
                    <li class="list-group-item">
                      <b>課程計費</b> <a class="float-right"><b><?php echo $courseres['course_cost'] ?? 0; ?></b> 元</a>
                    </li>
                    <li class="list-group-item">
                      <b>計費模式</b> <a class="float-right"><b><?php echo $courseJS['deduct'][$courseres['course_deduct']] ?? ""; ?></b></a>
                    </li>
                    <li class="list-group-item">
                      <b>開通方式</b> <a class="float-right"><b><?php echo $courseJS['unlock'][$courseres['course_unlock']] ?? ""; ?></b></a>
                    </li>
                    <li class="list-group-item">
                      <b>選購次數</b> <a class="float-right"><b><?php echo $courseres['course_attend'] ?? 0; ?></b></a>
                    </li>
                    <li class="list-group-item">
                      <b>造訪次數</b> <a class="float-right"><b><?php echo $courseres['course_visits'] ?? 0; ?></b></a>
                    </li>
                  </ul>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
              <div><span style="font-size:large;"> <?php if(!empty($sessID)) { echo "【ID: ".$sessID."】"; } ?>【 course_sessinfo.php 】</span></div>
            </div>
            <!-- /.col -->
            <div class="col-md-9">
              <div class="card card-secondary">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                      <li class="nav-item"><a class="nav-link active" href="#tabMenu1" data-index="1" data-toggle="tab" data-topbut="1">課程梯次基本資料</a></li>
                      <li class="nav-item"><a class="nav-link" href="#tabMenu2" data-index="2" data-toggle="tab" data-topbut="1">介紹版面編輯</a></li>
                      <?php if(!empty($sessID)) { ?>
                      <li class="nav-item"><a class="nav-link" href="#tabMenu3" data-index="3" data-toggle="tab" data-topbut="0">學員列表</a></li>
                      <li class="nav-item"><a class="nav-link" href="#tabMenu4" data-index="4" data-toggle="tab" data-topbut="0">選購紀錄</a></li>
                      <li class="nav-item"><a class="nav-link" href="#tabMenu5" data-index="5" data-toggle="tab" data-topbut="0">評價紀錄</a></li>
                    <?php } ?>
                  </ul>
                </div><!-- /.card-header -->
                <div class="card-body">
                  <div class="tab-content">
                    <!-- 第一分頁課程資料修改 -->
                    <div class="tab-pane active" id="tabMenu1">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;部份輸入使用HTML編輯器編輯內容，可以美化文字，也可以嵌入超連結。若是這個套裝包含「指定會員」資料時，這個套裝只有指定的會員才能預覽及選擇。</div>
                      <hr>
                      <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/course_act_func.php" method="post" data-formmode="infoupdate" data-func="func_sess_infoUpdate">
                        <div class="card-body">
                          <div class="form-group row">
                            <div class="col-sm-5">

                              <div class="text-center">
                                <div class="image_area crop-group" id="main_crop_area">
                                  <i class="nav-icon fas fa-2x fa-trash-can cropImgTrash<?php echo " ".$cropImgDisplay; ?>" alt="刪除圖像" title="刪除圖像"></i>
                                  <form method="post">
                                    <input type="hidden" name="mainimgchk" id="mainimgchk" class="mainimgchk notSubmit" value="<?php echo $mainimgchk; ?>">
                                    <label for="main_cropUploadImage">
                                      <?php
                                        $isRibbon = "none";
                                        if(!empty($res['sess_ribbon'])) {
                                          $isRibbon = "block";
                                        }
                                        echo "<div id='ribbonOnPhoto' class='ribbon-wrapper ribbon-lg' style='display:{$isRibbon};'>";
                                        echo "<img src='../imageFiles/medal05.png' height='60' style='position: absolute;bottom:10;right:0;z-index:1002;'>";
                                        echo "<div class='ribbon ".($courseJS['ribbonbg'][$res['sess_ribbon']] ?? '')."'>";
                                        echo $courseJS['ribbon'][$res['sess_ribbon']] ?? "&nbsp;";
                                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></div>";
                                      ?>
                                      <img id="avatarImage" name="avatarImage" style="border-radius:10px;" class="img-responsive img-fluid imgEDicon avatarImage" src="<?php echo $mainimgpath; ?>" alt="代表圖像" title="代表圖像"/>
                                      <div class="overlay">
                                        <div class="overtext"><?php echo !empty($sessID) ? "更改" : "新增"; ?>代表圖像</div>
                                      </div>
                                      <input type="file" id="main_cropUploadImage" name="image" class="image cropUploadImage notSubmit" style="display:none" crop-width="800" crop-height="600"<?php echo NdisAble; ?>>
                                    </label>
                                  </form>
                                  <!-- -------------------- 裁切主圖彈出視窗 -------------------- -->
                                  <?php include('include_avatarmodal.php'); ?>
                                </div>
                              </div>
                            </div>


                            <div class="col-sm-7">
                              <div class="form-group row">
                                <label for="selectBatch" class="col-sm-3 col-form-label text-nowrap">梯次選擇</label>
                                <div class="col-sm-9">
                                  <select class="form-control notSubmit" id="selectBatch" name="selectBatch"<?php echo NdisAble; ?>>
                                    <?php
                                      $sessList = DB->query("SELECT sess_id,sess_batch,sess_altname FROM fsl_course_sessions WHERE course_id = ? ORDER BY sess_batch DESC",array($courseID));
                                      foreach($sessList as $key => $value){
                                        echo '<option value="'.$value['sess_id'].'"';
                                        echo (($value['sess_id'] ?? '') === $sessID) ? ' selected' : '';
                                        echo '>第 '.$value['sess_batch'].' 梯次【'.$value['sess_altname'].'】</option>';
                                      }
                                    ?>
                                  </select>
                                </div>
                              </div>
                              <div class="form-group row">
                                <label for="sess_altname" class="col-sm-3 col-form-label text-nowrap">梯次抬頭</label>
                                <div class="col-sm-9">
                                  <input type="text" class="form-control" id="sess_altname" name="sess_altname" value="<?php echo $res['sess_altname'] ?? ""; ?>" placeholder="課程抬頭" data-min="4" data-required<?php echo NreadOnly; ?>>
                                </div>
                              </div>
                              <div class="form-group row">
                                <label for="sess_subscript" class="col-sm-3 col-form-label text-nowrap">下標</label>
                                <div class="col-sm-9">
                                  <input type="text" class="form-control" id="sess_subscript" name="sess_subscript" value="<?php echo $res['sess_subscript'] ?? ""; ?>" placeholder="下標" data-min="4" data-required<?php echo NreadOnly; ?>>
                                </div>
                              </div>
                              <div class="form-group row">
                                <label for="sess_keycode" class="col-sm-3 col-form-label text-nowrap">指定代碼</label>
                                <div class="col-sm-9">
                                  <input type="text" class="form-control" id="sess_keycode" name="sess_keycode" value="<?php echo $res['sess_keycode'] ?? ""; ?>" placeholder="指定代碼" data-min="4" data-type="keycode"<?php echo NreadOnly; ?>>
                                </div>
                              </div>
                              <div class="form-group row">
                                <label for="sess_ribbon" class="col-sm-3 col-form-label text-nowrap">滑鼠彈窗</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="sess_alt" name="sess_alt" value="<?php echo $res['sess_alt'] ?? ""; ?>" placeholder="滑鼠彈窗" data-min="4"<?php echo NreadOnly; ?>>
                                </div>
                              </div>
                              <div class="form-group row">
                                <label for="sess_ribbon" class="col-sm-3 col-form-label text-nowrap">代表飾帶</label>
                                <div class="col-sm-9">
                                    <select class="form-control" id="sess_ribbon" name="sess_ribbon" placeholder="套裝飾帶"<?php echo NreadOnly; ?>>
                                      <?php
                                        foreach($courseJS['ribbon'] as $key => $value){
                                            echo '<option value="'.$key.'"';
                                            if($key==""){ echo " disabled"; }
                                            echo (($res['sess_ribbon'] ?? '') === $key) ? ' selected' : '';
                                            echo '>'.$value.'</option>';
                                        }
                                      ?>
                                    </select>
                                </div>
                              </div>

                            </div>
                          </div>

                          <div class="form-group row">
                            <label for="sess_brief" class="col-sm-2 col-form-label">梯次課程簡述</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="sess_brief" name="sess_brief" rows="4" placeholder="簡述" data-min="4" data-required<?php echo NreadOnly; ?>><?php echo !empty($res['sess_brief']) ? stripslashes($res['sess_brief']) : ""; ?></textarea>
                            </div>
                          </div>
                          <br>
                          <hr><div class="hr-more">建立梯次課程</div>
                          <div class="form-group row">
                              <label for="sess_startdate" class="col-sm-2 col-form-label">開課日期</label>
                              <div class="col-sm-4">
                                  <div class="input-group">
                                  <div class="input-group-prepend">
                                      <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                  </div>
                                  <input type="text" class="form-control" id="sess_startdate" name="sess_startdate" value="<?php echo $res['sess_startdate'] ?? ''; ?>" placeholder="開課日期" data-required<?php echo NreadOnly; ?>>
                                  </div>
                              </div>
                              <label for="sess_starttime" class="col-sm-2 col-form-label">開課時間</label>
                              <div class="col-sm-4">
                                  <div class="input-group">
                                  <div class="input-group-prepend">
                                      <span class="input-group-text"><i class="far fa-clock"></i></span>
                                  </div>
                                  <input type="text" class="form-control" id="sess_starttime" name="sess_starttime" value="<?php echo $res['sess_starttime'] ?? ''; ?>" placeholder="開課時間" data-required<?php echo NreadOnly; ?>>
                                  </div>
                              </div>
                          </div>
                          <div class="form-group row">
                            <label for="sess_duration" class="col-sm-2 col-form-label">梯次課程時長</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="sess_duration" name="sess_duration" value="<?php echo $res['sess_duration'] ?? "0"; ?>" placeholder="課程時長" data-required<?php echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">分鐘</span>
                                </div>
                              </div>
                            </div>
                            <label for="sess_lesson" class="col-sm-2 col-form-label">梯次課程週期</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="sess_lesson" name="sess_lesson" value="<?php echo $res['sess_lesson'] ?? "0"; ?>" placeholder="課程週期"  data-required<?php echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">堂課</span>
                                </div>
                              </div>
                            </div>
                          </div> 
                          <div class="form-group row">
                              <label for="sess_onweek" class="col-sm-2 col-form-label"></label>
                              <div class="col-sm-10" style="font: size 1rem;color: #6c757d;line-height:1;">堂數超過1堂時，週間星期可複選，並依照堂數自動順序排列直至堂數結束。若每週只有一堂課時，則預設為首堂課的星期數。</div>
                          </div>
                          <div class="form-group row">
                              <label for="sess_onweek" class="col-sm-2 col-form-label">週間星期</label>
                              <div class="col-sm-9">
                                  <?php
                                  $getDateWeek = $res['sess_startdate'] ?? '';
                                  if ($getDateWeek != '') $getDateWeek = date('w', strtotime($res['sess_startdate'] ?? ''));
                                  $weeks = [
                                      0 => '星期日',
                                      1 => '星期一',
                                      2 => '星期二',
                                      3 => '星期三',
                                      4 => '星期四',
                                      5 => '星期五',
                                      6 => '星期六'
                                  ];
                                  $savedWeeks = !empty($res['sess_starttime']) ? explode(',', $res['sess_onweek'] ?? '') : []; // 預設勾選範例
                                  foreach ($weeks as $key => $value) {
                                      $disAble = $lessonCount > 1 ? '' : ' disabled';
                                      $iCheckClass = $getDateWeek == $key ? 'icheck-danger' : 'icheck-primary';
                                      $disAble = $getDateWeek == $key ? ' disabled' : '';
                                      if(!empty(NdisAble)){
                                        $disAble = ' disabled';
                                      }
                                      if(in_array($key, $savedWeeks)){
                                          $checked = ' checked';
                                          //$disAble = '';
                                      } else {
                                          $checked = '';
                                      }
                                      $paddingR = $key == 6 ? 'padding-right:0;' : 'padding-right:30px;';
                                      // 👇 調整結構：套用 Bootstrap 4 的 form-check 和 form-check-inline
                                      echo '<div class="'.$iCheckClass.' d-inline" style="'.$paddingR.'"><input type="checkbox" id="sess_onweek'.$key.'" name="sess_onweek[]" value="' . $key . '"' . $checked.$disAble . ' data-readonly="true"><label for="sess_onweek'.$key.'" style="margin-top:10px;font-weight: normal; cursor: pointer;">' . $value . '</label></div>';
                                  }
                                  ?>
                              </div>
                          </div>


                          <div class="form-group row">
                            <label for="sess_ta1" class="col-sm-2 col-form-label">課堂助教1</label>
                            <div class="col-sm-10">
                              <select id="sess_ta1" name="sess_ta1" class="form-control js-data-example-ajax"<?php echo NdisAble; ?>>
                              <?php
                                if(!empty($res['sess_ta1'])){
                                  $msql = "SELECT mb_id, mb_name, mb_title, mb_cate, mb_cotype, mb_coexpert FROM fsl_members WHERE mb_id=?";
                                  $mres = DB->row($msql,array($res['sess_ta1']));
                                  if($mres){
                                    echo "<option value='".$mres['mb_id']."'>";
                                    echo "【".$mres['mb_id']."】";
                                    echo !empty($mres['mb_name']) ? _fn_secure_crypto($mres['mb_name'],'de') : "";
                                    echo "/";
                                    echo $mres['mb_title'];
                                    if(!empty($mres['mb_cotype']) && $mres['mb_cotype'] != '[]'){
                                        $coTypeStr = '';
                                        $ti=0;
                                        $ctJS = json_decode($mres['mb_cotype']);
                                        $cnt = count($ctJS)-1;
                                        foreach($ctJS as $val){
                                            $coTypeStr .= $coTypeJS[$val];
                                            if($ti<$cnt) $coTypeStr .= ',';
                                            $ti++;
                                        } 
                                        echo " [".$coTypeStr."]";//會員合作方種類
                                    }
                                    if(!empty($mres['mb_coexpert']) && $mres['mb_coexpert'] != '[]'){
                                        $coExpertStr = '';
                                        $ei=0;
                                        $ceJS = json_decode($mres['mb_coexpert']);
                                        $cnt = count($ceJS)-1;
                                        foreach($ceJS as $val){
                                            $coExpertStr .= $expertJS[$val];
                                            if($ei<$cnt) $coExpertStr .= ',';
                                            $ei++;
                                        } 
                                        echo " [".$coExpertStr."]";//會員專業服務項目
                                    }
                                    echo "</option>";
                                  }
                                }
                              ?>
                              </select>
                            </div>
                          </div>

                          <div class="form-group row">
                            <label for="sess_ta2" class="col-sm-2 col-form-label">課堂助教2</label>
                            <div class="col-sm-10">
                              <select id="sess_ta2" name="sess_ta2" class="form-control js-data-example-ajax"<?php echo NdisAble; ?>>
                              <?php
                                if(!empty($res['sess_ta2'])){
                                  $msql = "SELECT mb_id, mb_name, mb_title, mb_cate, mb_cotype, mb_coexpert FROM fsl_members WHERE mb_id=?";
                                  $mres = DB->row($msql,array($res['sess_ta2']));
                                  if($mres){
                                    echo "<option value='".$mres['mb_id']."'>";
                                    echo "【".$mres['mb_id']."】";
                                    echo !empty($mres['mb_name']) ? _fn_secure_crypto($mres['mb_name'],'de') : "";
                                    echo "/";
                                    echo $mres['mb_title'];
                                    if(!empty($mres['mb_cotype']) && $mres['mb_cotype'] != '[]'){
                                        $coTypeStr = '';
                                        $ti=0;
                                        $ctJS = json_decode($mres['mb_cotype']);
                                        $cnt = count($ctJS)-1;
                                        foreach($ctJS as $val){
                                            $coTypeStr .= $coTypeJS[$val];
                                            if($ti<$cnt) $coTypeStr .= ',';
                                            $ti++;
                                        } 
                                        echo " [".$coTypeStr."]";//會員合作方種類
                                    }
                                    if(!empty($mres['mb_coexpert']) && $mres['mb_coexpert'] != '[]'){
                                        $coExpertStr = '';
                                        $ei=0;
                                        $ceJS = json_decode($mres['mb_coexpert']);
                                        $cnt = count($ceJS)-1;
                                        foreach($ceJS as $val){
                                            $coExpertStr .= $expertJS[$val];
                                            if($ei<$cnt) $coExpertStr .= ',';
                                            $ei++;
                                        } 
                                        echo " [".$coExpertStr."]";//會員專業服務項目
                                    }
                                    echo "</option>";
                                  }
                                }
                              ?>
                              </select>
                            </div>
                          </div>


                          <br>
                          <hr><div class="hr-more">建立教室</div>
                          <div class="form-group row">
                              <label for="sess_xxxx" class="col-sm-2 col-form-label"></label>
                              <div class="col-sm-4">
                              </div>
                              <label for="sess_batchmode" class="col-sm-2 col-form-label">教室模式</label>
                              <div class="col-sm-4">
                                    <select class="form-control" id="sess_batchmode" name="sess_batchmode" placeholder="教室模式"<?php echo NreadOnly; ?>>
                                      <?php
                                        foreach($courseJS['mode'] as $key => $value){
                                            echo '<option value="'.$key.'"';
                                            if($key==""){ echo " disabled"; }
                                            echo (($res['sess_batchmode'] ?? '') === $key) ? ' selected' : '';
                                            echo '>'.$value.'</option>';
                                        }
                                      ?>
                                    </select>
                              </div>
                          </div>
                          <div class="form-group row">
                              <label for="sess_secured" class="col-sm-2 col-form-label">
                                  <div class="icheck-primary d-inline">
                                      <input type="checkbox" name="sess_secured" id="sess_secured" value="1"  data-unchecked="0"<?php echo ($res['sess_secured'] ?? '') == 1 ? "checked" : ""; ?><?php echo NdisAble; ?>>
                                      <label for="sess_secured"></label>
                                  </div>
                              </label>
                              <div class="col-sm-4">安全限制模式開關，動態強化教室防護。</div>
                              <label for="sess_secured" class="col-sm-2 col-form-label">註冊審核</label>
                              <div class="col-sm-4">
                                  <select class="form-control" id="sess_approval" name="sess_approval"<?php echo NdisAble; ?>>
                                      <option value="0" <?php echo ($res['sess_approval'] ?? '') == 0 ? "selected" : ""; ?>>自動審核(取得專屬網址)</option>
                                      <option value="1" <?php echo ($res['sess_approval'] ?? '') == 1 ? "selected" : ""; ?>>手動審核</option>
                                      <option value="2" <?php echo ($res['sess_approval'] ?? '') == 2 ? "selected" : ""; ?>>無需註冊</option>
                                  </select>
                              </div>
                          </div>
                          <div class="form-group row">
                              <label for="sess_beforehost" class="col-sm-2 col-form-label">
                                  <div class="icheck-primary d-inline">
                                      <input type="checkbox" name="sess_beforehost" id="sess_beforehost" value="1" data-unchecked="0"<?php echo ($res['sess_beforehost'] ?? '') == 1 ? "checked" : ""; ?><?php echo NdisAble; ?>>
                                      <label for="sess_beforehost"></label>
                                  </div>
                              </label>
                              <div class="col-sm-4">與會者在主持人正式上線前，可先進入教室互動或等待。</div>
                              <label for="sess_jbhtime" class="col-sm-2 col-form-label">提前時間</label>
                              <div class="col-sm-4">
                                  <div class="input-group">
                                      <input type="text" class="form-control" id="sess_jbhtime" name="sess_jbhtime" value="<?php echo $res['sess_jbhtime'] ?? ""; ?>" placeholder="提前加入時間"<?php echo ($res['sess_beforehost'] ?? '') == 1 ? (!empty(NreadOnly) ? " readonly" : "") : " readonly"; ?>>
                                      <div class="input-group-append">
                                          <span class="input-group-text">分鐘</span>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <div class="form-group row">
                              <label for="sess_watermark" class="col-sm-2 col-form-label">
                                  <div class="icheck-primary d-inline">
                                      <input type="checkbox" name="sess_watermark" id="sess_watermark" value="1" data-unchecked="0"<?php echo ($res['sess_watermark'] ?? '') == 1 ? "checked" : ""; ?><?php echo NdisAble; ?>>
                                      <label for="sess_watermark"></label>
                                  </div>
                              </label>
                              <div class="col-sm-4">視訊畫面強制加上該與會者電子郵件的浮水印。</div>
                              <label for="sess_muteentry" class="col-sm-2 col-form-label">
                                  <div class="icheck-primary d-inline">
                                      <input type="checkbox" name="sess_muteentry" id="sess_muteentry" value="1" data-unchecked="0"<?php echo ($res['sess_muteentry'] ?? '') == 1 ? "checked" : ""; ?><?php echo NdisAble; ?>>
                                      <label for="sess_muteentry"></label>
                                  </div>
                              </label>
                              <div class="col-sm-4">與會者進入教室時麥克風預設為靜音狀態。</div>
                          </div>
                          <div class="form-group row">
                              <label for="sess_meetingid" class="col-sm-2 col-form-label">教室編號ID</label>
                              <div class="col-sm-4">
                                  <input type="text" class="form-control" id="sess_meetingid" name="sess_meetingid" value="<?php echo $res['sess_meetingid'] ?? ""; ?>" placeholder="教室編號ID" readonly>
                              </div>
                              <label for="sess_meetingid" class="col-sm-2 col-form-label"></label>
                              <div class="col-sm-4">
                                  <button type="button" class="btn btn-primary notSubmit" id="creatMeetingID" name="creatMeetingID" title="建立教室取得編號ID"<?php echo !empty($res['sess_meetingid']) ? " disabled" : ""; ?><?php echo NdisAble; ?>>建立</button>
                                  <button type="button" class="btn btn-info notSubmit" id="upMeetingID" name="upMeetingID" title="更新教室狀態"<?php echo !empty($res['sess_meetingid']) ? "" : " disabled"; ?><?php echo NdisAble; ?>>更新</button>
                                  <button type="button" class="btn btn-danger notSubmit" id="deleteMeetingID" name="deleteMeetingID" title="刪除教室"<?php echo !empty($res['sess_meetingid']) ? "" : " disabled"; ?><?php echo NdisAble; ?>>刪除</button>
                                  <button type="button" class="btn btn-secondary notSubmit" id="syncMeeting" name="syncMeeting" title="同步教室狀態"<?php echo !empty($res['sess_meetingid']) ? "" : " disabled"; ?><?php echo NdisAble; ?>>同步</button>
                              </div>
                          </div>
                          <br>
                          <hr><div class="hr-more">課程上架狀態</div>
                          <div class="form-group row">
                            <label for="sess_status" class="col-sm-2 col-form-label">課程狀態</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="sess_status" name="sess_status" data-origval="<?php if(!empty($sessID) && !is_null($res['sess_status'])) echo $res['sess_status']; ?>" placehold=""<?php echo NdisAble; ?>>
                                <?php
                                  foreach($courseJS['status'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      echo (($res['sess_status'] ?? '') === $key) ? ' selected' : '';
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          <hr>
                        </div>
                        <!-- /.card-body -->
                      </form>
                    </div>
                    <!-- /第一分頁課程資料修改 -->
                    <!-- 第二分頁課程介紹內容主體編輯 -->
                    <div class="tab-pane" id="tabMenu2">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;Html 編輯器使用 SunEditor 2.45.1 版本，並依照 MIT 授權條款內容行使權利及義務。</div>
                      <hr>
                      <form id="form_tabMenu2" name="form_tabMenu2" action="../funcFiles/course_act_func.php" method="post" data-formmode="htmlupdate" data-func="func_sess_infoUpdate">
                        <div class="card-body">
                          <textarea name="sess_body" id="sess_body"><?php echo !empty($res['sess_body']) ? stripslashes($res['sess_body']): ""; ?></textarea>
                        </div>
                      </form>
                    </div>
                    <!-- /第二分頁課程介紹內容主體編輯 -->
                    <!-- 第三分頁報名學員列表 -->
                    <div class="tab-pane" id="tabMenu3">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;xxxx。</div>
                      <hr>
                      <!-- /.card-header -->
                      <div class="card-body">
                        <table id="dataTable_enrolleeList" class="table table-bordered table-striped table-hover" data-butClass="gray" data-orderby="2" data-sortorder="desc" data-colorder="0,9" data-pagemax="10" data-jumpto="0"  but-popup="新增學員" data-include="include_sessEnrollee.php?courseid=<?php echo $courseID; ?>&sessid=<?php echo $sessID; ?>" data-triggerto="openEnroModel">
                          <thead>
                            <tr class="gray" style="line-height:6px;">
                              <th>No.</th>
                              <th>姓名</th>
                              <th>報名日期</th>
                              <th>支付</th>
                              <th>簽到</th>
                              <th>簽退</th>
                              <th>教評</th>
                              <th>通知</th>
                              <th>狀態</th>
                              <th>操作</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                              $sql="SELECT * FROM course_enrollees WHERE course_id=? AND sess_id=? ORDER BY cen_registdate DESC";
                              $censrr = DB->query($sql,array($courseID,$sessID));
                              $totalEnro = count($censrr);
                              $i = 0; //以$i為$nameList這個陣列的索引值
                              while($i < $totalEnro){ 
                                //$getURI="id=".$censrr[$i]['sess_id']."&batch=".$censrr[$i]['sess_batch']."&altname=".$censrr[$i]['sess_altnsme']."&startdate=".$censrr[$i]['sess_startdate']."&starttime=".$censrr[$i]['sess_starttime']."&onweek=".$censrr[$i]['sess_onweek']."&ta1=".$censrr[$i]['sess_ta1']."&ta2=".$censrr[$i]['sess_ta2']."&enrollee=".$censrr[$i]['sess_enrollee']."&meetingid=".$censrr[$i]['sess_meetingid']."&sessstatus=".$censrr[$i]['sess_status'];
                                //$getURI="courseid={$courseID}&sessid={$censrr[$i]['sess_id']}&batch={$censrr[$i]['sess_batch']}&altname={$censrr[$i]['sess_altname']}&startdate={$censrr[$i]['sess_startdate']}&starttime={$censrr[$i]['sess_starttime']}&onweek={$censrr[$i]['sess_onweek']}&ta1={$censrr[$i]['sess_ta1']}&ta2={$censrr[$i]['sess_ta2']}&enrollee={$censrr[$i]['sess_enrollee']}&meetingid={$censrr[$i]['sess_meetingid']}&sessstatus={$censrr[$i]['sess_status']}";
                                $getURI="currID={$censrr[$i]['cen_id']}&courseid={$courseID}&sessid={$censrr[$i]['sess_id']}";
                                //$getMBURI="currID={$censrr[$i]['cen_id']}&".$backUrl;
                                //$getPayURI="currID={$censrr[$i]['pay_id']}&".$backUrl;
                                $getMBURI="currID={$censrr[$i]['cen_id']}";
                                $getPayURI="currID={$censrr[$i]['pay_id']}";
                                $mbName = DB->single("SELECT mb_name FROM fsl_members WHERE mb_id=?",array($censrr[$i]['mb_id']));
                                if(!empty($mbName)){
                                  $mbName = _fn_secure_crypto($mbName, 'de');
                                }
                            ?>
                                <tr style="cursor:pointer;" id="cenlist_<?php echo $censrr[$i]['cen_id']; ?>" data-enrono="<?php //echo $censrr[$i]['sess_batch']; ?>" data-enroid="<?php echo $censrr[$i]['cen_id']; ?>" data-uri="<?php echo $getURI; ?>">
                                  <td data-toggle="tooltip" title="快速編輯"><?php echo $censrr[$i]['cen_id'] ?? "&nbsp;"; ?></td>
                                  <td data-toggle="tooltip" title="快速編輯"><?php echo $mbName ?? "&nbsp;"; ?></td>
                                  <td data-toggle="tooltip" title="快速編輯"><?php echo $censrr[$i]['cen_registdate'] ?? "&nbsp;"; ?></td>
                                  <td data-toggle="tooltip" title="快速編輯">Sign-in</td>
                                  <td data-toggle="tooltip" title="快速編輯">Sign-out</td>
                                  <td data-toggle="tooltip" title="快速編輯">rate</td>
                                  <td data-toggle="tooltip" title="快速編輯">notified</td>
                                  <td data-toggle="tooltip" title="快速編輯"><?php echo $censrr[$i]['sess_status'] ?? "&nbsp;"; ?></td>
                                  <td data-toggle="tooltip" title="快速編輯"><?php echo $courseJS['enrostatus'][$censrr[$i]['cen_status']] ?? "&nbsp;"; ?></td>
                                  <td style="display: flex;gap: 2px;">
                                    <button type="button" class="btn btn-info btn-sm" style="padding:2px;" onClick="location.href='mem_profile.php?<?php echo $getMBURI; ?>';">專頁</button>
                                    <button type="button" class="btn btn-warning btn-sm" style="padding:2px;" onClick="location.href='fn_paidinfo.php?<?php echo $getPayURI; ?>';">支付</button>
                                  </td>
                                </tr>
                            <?php 
                                $i++;
                                } 
                              ?>
                          </tbody>
                        </table>
                        <input type="hidden" id="totalEnro" name="totalEnro" value="<?php echo $totalEnro; ?>">
                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第三分頁課程梯次列表 -->
                    <!-- 第四分頁課程支付紀錄 -->
                    <div class="tab-pane" id="tabMenu4">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;本系統購買這個課程的紀錄列表，本列表用於偵錯、改進、分析甚至於究責，所以一旦寫入記錄便無法更改，支付記錄將於多處以及多型態備份。</div>
                      <hr>
                      <!-- card-body -->
                      <div class="card-body">
                        <table id="dataTable_enrolleeList" class="table table-bordered table-striped table-hover" data-pagemax="10" data-jumpto="0" data-search>
                          <thead>
                            <tr>
                              <th>代碼</th>
                              <th>選購日期時間</th>
                              <th>選購人</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                              $sql = "
                                SELECT 
                                    a.*, 
                                    b.lg_string, 
                                    c.ad_title,
                                    c.ad_name
                                FROM data_logs a
                                LEFT JOIN sys_logstrings b ON a.lg_code = b.lg_code
                                LEFT JOIN fsl_administrator c ON a.lg_forid = c.ad_id
                                WHERE lg_forid=? AND lg_for=? ORDER BY a.lg_id DESC LIMIT 200
                              ";
                              $sr = DB->query($sql,array($courseID,1));
                              //$sql="SELECT * FROM data_logs WHERE lg_forid=? AND lg_for=?";
                              //$censrr = DB->query($sql,array($courseID,1));
                              //$i = 0; //以$i為$nameList這個陣列的索引值
                              //while($i<count($censrr)){ 
                              foreach ($sr as $log) {
                              ?>
                                <tr>
                                  <td><?php echo $log['lg_code']; ?></td>
                                  <td><?php echo $log['lg_created']; ?></td>
                                  <td><?php
                                  echo _fn_getLogString($log['lg_params'],$log['lg_string']);
                                  ?></td>
                                </tr>
                            <?php 
                                $i++;
                                } 
                              ?>
                          </tbody>
                          <tfoot>
                            <tr>
                              <th>代碼</th>
                              <th>操作日期時間</th>
                              <th>操作內容</th>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第四分頁課程支付紀錄 -->
                    <!-- 第五分頁評價紀錄 -->
                    <div class="tab-pane" id="tabMenu5">
                      <div style="font-size:0.85em;line-height:1.4em;">
                        <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;評價紀錄列表。</div>
                        <hr>
                        <!-- card-body -->
                        <div class="card-body">
                          <table id="dataTable_ratingList" class="table table-bordered table-striped table-hover" data-search>
                            <thead>
                              <tr>
                                <th>日期</th>
                                <th>來自</th>
                                <th><?php echo $geJS['idfor'][$idForItem]['rating'][0]?:" &nbsp"; ?></th>
                                <th>評論</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php
                                $sql="SELECT * FROM fsl_rating WHERE rat_forid=? AND rat_for=?";
                                $sr = DB->query($sql,array($courseID,5));
                                $i = 0; //以$i為$nameList這個陣列的索引值
                                while($i<count($sr)){ 
                                  ?>
                                  <tr>
                                    <td><?php echo $sr[$i]['rat_create']; ?></td>
                                    <td><?php echo $sr[$i]['md_id']; ?></td>
                                    <td><?php echo $sr[$i]['rat_s1']; ?></td>
                                    <td>
                                    <?php if(!empty($sr[$i]['rat_review'])) { ?>
                                      <button type="button" class="btn btn-info btn-block btn-sm" title="<?php echo $sr[$i]['rat_review']; ?>"><i class="fa fa-comments"></i> </button>
                                    <?php } ?>
                                    </td>
                                  </tr>
                              <?php 
                                  $i++;
                                  } 
                                ?>
                            </tbody>
                            <tfoot>
                              <tr>
                                <th>&nbsp;</th>
                                <th><?php echo $totSum; ?></th>
                                <th><?php echo $totCount; ?></th>
                                <th style="font-size:1.2em;font-weight:bold;"><?php echo $totAVE; ?></th>
                              </tr>
                            </tfoot>
                        </table>
                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第五分頁評價紀錄 -->
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
      <!-- -------------------- 課程梯次內容彈出視窗 -------------------- -->
      <div id="enroList_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="background:rgba(0,0,0,0.75);">
          <div class="modal-dialog modal-dialog-scrollable modal-lg" style="width:50%;">
            <div class="modal-content">
              <!-- Header -->
              <div class="modal-header bg-secondary">
                <h5 class="modal-title">新增課程梯次</h5>
                <button type="button" id="modalCloseTop" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <!-- /Header -->
              <!-- Model Body -->
              <div class="modal-body">
                <h6 class="modal-des"></h6>
                <form id="form_enroList_modal" name="form_enroList_modal" data-enroid="0" action="../funcFiles/course_act_func.php" method="post" data-formmode="sessupdate" data-func="func_sess_infoUpdate">
                  <div class="modal-container"></div>
                </form>
              </div>
              <!-- /Model Body -->
              <!-- Footer -->
              <div class="modal-footer justify-content-between">
                <button type="button" id="modal_delBut" class="btn btn-danger" style="visibility:hidden;">刪除</button>
                <button type="button" id="modal_upBut" class="btn btn-primary">新增</button>
              </div>
              <!-- /Footer -->
            </div>
          </div>
        <!-- /.modal-dialog -->
        </div>
        <!-- -------------------- /課程梯次內容彈出視窗 -------------------- -->
      </section>
      <!-- /主要內容區域 ------------------------------------ -->
    </div>
    <!-- /頁內全頁內容 -->

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
<script src="../pluginFiles/datatables/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../pluginFiles/datatables/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
-->
<!-- DataTables3 & Plugins -->
<script src="../pluginFiles/datatables3/datatables.min.js"></script>
<!-- SweetAlert2 -->
<script src="../pluginFiles/sweetalert2/sweetalert2.min.js"></script> 
<!-- InputMask 日期輸入元件JS -->
<script src="../pluginFiles/moment/moment.min.js"></script>
<script src="../pluginFiles/inputmask/jquery.inputmask.min.js"></script>
<!-- Tempusdominus -->
<script src="../pluginFiles/tempusdominus/tempus-dominus.min.js"></script>
<script src="../pluginFiles/popperjs/popper.min.js"></script>
<!-- iCheck -->
<script src="../pluginFiles/icheck/icheck.js"></script>
<!-- ChartJS -->
<script src="../pluginFiles/chart.js/Chart.min.js"></script>
<!-- ezValidation -->
<script src="../pluginFiles/validator/validator.js<?php echo '?t='.time(); ?>"></script>
<!-- Dropzone JS -->
<script src="../pluginFiles/dropzone/dropzone.js"></script>
<!-- Cropper JS -->
<script src="../pluginFiles/cropper/cropper.js"></script>
<!-- suneditor -->
<script src="../pluginFiles/suneditor/dist/suneditor.min.js"></script>
<script src="../pluginFiles/suneditor/sample/js/common.js"></script>
<script src="../pluginFiles/suneditor/src/lang/zh_tw.js"></script>
<!-- codemirror -->
<script src="../pluginFiles/codemirror/lib/codemirror.min.js"></script>
<script src="../pluginFiles/codemirror/mode/htmlmixed/htmlmixed.js"></script>
<script src="../pluginFiles/codemirror/mode/xml/xml.js"></script>
<script src="../pluginFiles/codemirror/mode/css/css.js"></script>
<!-- select2 -->
<script src="../pluginFiles/select2/js/select2.js"></script>
<script src="../pluginFiles/select2/js/i18n/zh-TW.js"></script>
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<!-- Custom JS style -->
<script src="../scriptFiles/page.basic.js<?php echo '?t='.time(); ?>"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
<script src="../scriptFiles/imageCropUpload.class.js<?php echo '?t='.time(); ?>"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

<!-- ------------------------ /Scripts Link ------------------------ -->
<script type="text/javascript">
////////////////////////////////////////////////////////////////
// ---------------- JAVASCRIPTS 全域調用參數設置 --------------- //
////////////////////////////////////////////////////////////////
  var mainObj = <?php echo json_encode($courseJS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
  var briefActive = false;
  var bodyActive = false;
  var sunBrief;
  var sunBody;
////////////////////////////////////////////////////////////////
// -------------- JAVASCRIPTS FUNCTIONs 全域調用 -------------- //
// -------------- 可接收include檔案要求後自定義動作 -------------- //
////////////////////////////////////////////////////////////////
  // 當「返回」按鈕按下時的動作
  function doReDirect(){
      window.history.back();
  }
  // 當「儲存」按鈕按下時的動作
  function doButSave(){
	  var frm = $('#form_'+$('.tab-pane.active').prop("id"));
    var accE = frm.data('func');
    if(briefActive == true) { sunBrief.save(); }
    if(bodyActive == true) { sunBody.save(); }
    var getValid = Validator.validate(frm);
    if(!getValid){
      return false;
    } else {
      ajaxPOST(frm);
    }
  }
  // 當「重置」按鈕按下時的動作
  function doButReset(){
    location.reload(true);
  }
  // 當「刪除」按鈕按下時的動作
  function doButDelete(){
    $.ajax({
        type: 'post',
        url: '../funcFiles/course_act_func.php',
        data: {
          'arrDatA': {}, 
          'formAcce':'func_sess_delete',
          'token': getTokenVal(), 
          'sessID':'<?php echo $sessID; ?>'
        },
        dataType: 'json',//data format  
        async: false, 
      success: function (data) {
          if(data['RTN']==1){
            window.location.replace("course_sesslist.php");//JazNOTE: 確定是否需要修改為 backIframe()
          } else {
            window.parent.toastr[data['msgType']](data['msgStr']);
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
    //var frm = $('#form_'+$('.tab-pane.active').prop("id"));
    var accE = frm.data('func');
    var url = frm.prop('action');
    var typE = frm.prop('method');
    var enroID = parseInt($('#form_enroList_modal').attr('data-enroid'));
    var formData={},datA={},datB={};
    var imgBase64='';
    if(accE=='func_sess_infoUpdate'){
      datA = getFormObjects($('#form_tabMenu1'));
      datB = getFormObjects($('#form_tabMenu2'));
      formData = Object.assign(formData, datA, datB);
      if(null=='<?php echo $sessID; ?>' || 0=='<?php echo $sessID; ?>'){ accE='func_sess_addnew'; }
      imgBase64 = $('#main_crop_area').data('cropImage64') || '';
    } else if(accE=='func_enro_infoUpdate'){
      datA = getFormObjects($('#form_enroList_modal'));
      formData = Object.assign(formData, datA);
      //Swal.fire(JSON.stringify(formData)); return false;
      if(enroID == '0'){
        accE ='func_enro_addnew';
      }

    }
    $.ajax({
        type: typE,
        url: url,
        data: {
          'arrDatA': formData, 
          //'arrDatB':datB, 
          'formAcce':accE,
          'imgUp': imgBase64,
          'token': getTokenVal(), 
          'courseID':'<?php echo $courseID; ?>',
          'sessID': '<?php echo $sessID; ?>',
          'enroID': enroID
        },
        dataType: 'json',
        async: false,
        beforeSend: function () {
            //alert(JSON.stringify(formData));return false;
        },    
      success: function (data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if(accE=='func_sess_addnew') {
            if (data['RTN']==1) {
              //window.location.replace("course_sessinfo.php?currID="+data['newID']+'&<?php //echo $backUrl; ?>');
              window.location.replace("course_sessinfo.php?currID="+data['newID']);
              return false;
            }
        }
        //---------- 新增報名會員 ----------
        else if(accE=='func_enro_addnew') {
          if(!data['arr']['newID'] || data['arr']['newID']==0){
            return false;
          }
            var r = data['arr'];
            var rows = $('#dataTable_enroList tbody tr').length + 1;
            if($('#totalSess').val() == 0){ rows = 1; }
            var resetURI="id="+r['newID'];
                resetURI+="&sessmode="+r['sess_mode'];
                resetURI+="&fixed="+r['sess_fixed'];
                resetURI+="&posit="+r['sess_positnum'];
                resetURI+="&group="+r['sess_positgroup'];
                resetURI+="&top="+r['sess_topsearch'];
                resetURI+="&indist="+r['sess_indistinct'];
                resetURI+="&weekday="+r['sess_weekday'];
                resetURI+="&weekend="+r['sess_weekend'];
                resetURI+="&period="+r['sess_period'];
                resetURI+="&status="+r['sess_status'];
            var html = '<tr id="enrolist_'+r['newID']+'" data-enrono="'+rows+'" data-enroid="'+r['newID']+'" data-uri="'+resetURI+'"';
                if($('#totalSess').val() == 0){ html += ' style="cursor:pointer;background-color:#EEEEEE;"'; }
                else{ html += ' style="cursor:pointer;"'; }
                html += '>';
                html += '<td>'+rows+'.</td>';
                html += '<td>'+mainObj['sessmode'][r['sess_mode']]+'</td>';
                html += '<td>'+mainObj['fixedS'][r['sess_fixed']]+'</td>';
                html += '<td>'+mainObj['positgroupS'][r['sess_positgroup']]+'</td>';
                html += '<td>'+r['sess_positnum']+'</td>';
                html += '<td>'+mainObj['topsearchS'][r['sess_topsearch']]+'</td>';
                html += '<td>'+mainObj['indistinct'][r['sess_indistinct']]+'</td>';
                html += '<td>'+mainObj['isweekday'][r['sess_weekday']]+'</td>';
                html += '<td>'+mainObj['isweekend'][r['sess_weekend']]+'</td>';
                html += '<td>'+mainObj['periodS'][r['sess_period']]+'</td>';
                html += '<td>'+mainObj['rulstatus'][r['sess_status']]+'</td>';
                html += '</tr>';
            if($('#totalSess').val() == 0){//當沒有任何規則紀錄時
              $('#dataTable_enroList tbody tr').remove(); //移除「沒有紀錄」標註字樣
              $('#totalSess').val('1');//將隱藏註記改寫為有存在規則
            }
            $('#dataTable_enroList tbody').append(html);
            //-- Reset Modal --//
            //$('#enroList_modal .modal-title').html('');
            $('#form_enroList_modal').attr('data-enroid','0');
            $('#enroList_modal .modal-container').html('');
            $('#enroList_modal').removeClass('show').hide();
        }
        //---------- 編輯課程梯次 ----------
        else if(accE=='func_enro_infoUpdate') {
          //更新列表中的資料
          var upID=$('#form_enroList_modal').attr('data-enroid');
            $('#dataTable_enroList tr[id=enrolist_'+upID+'] td:nth-child(2)').html($('#form_enroList_modal #sess_mode option:selected').text());
            $('#dataTable_enroList tr[id=enrolist_'+upID+'] td:nth-child(3)').html(mainObj['fixedS'][$('#form_enroList_modal #sess_fixed').val()]);
            $('#dataTable_enroList tr[id=enrolist_'+upID+'] td:nth-child(4)').html(mainObj['positgroupS'][$('#form_enroList_modal #sess_positgroup').val()]);
            $('#dataTable_enroList tr[id=enrolist_'+upID+'] td:nth-child(5)').html($('#form_enroList_modal #sess_positnum').val());
            $('#dataTable_enroList tr[id=enrolist_'+upID+'] td:nth-child(6)').html(mainObj['topsearchS'][$('#form_enroList_modal #sess_topsearch').val()]);
            $('#dataTable_enroList tr[id=enrolist_'+upID+'] td:nth-child(7)').html(mainObj['indistinct'][$('#form_enroList_modal #sess_indistinct').is(':checked') ? 1 : 0]);
            $('#dataTable_enroList tr[id=enrolist_'+upID+'] td:nth-child(8)').html(mainObj['isweekday'][$('#form_enroList_modal #sess_weekday').is(':checked') ? 1 : 0]);
            $('#dataTable_enroList tr[id=enrolist_'+upID+'] td:nth-child(9)').html(mainObj['isweekend'][$('#form_enroList_modal #sess_weekend').is(':checked') ? 1 : 0]);
            $('#dataTable_enroList tr[id=enrolist_'+upID+'] td:nth-child(10)').html(mainObj['periodS'][$('#form_enroList_modal #sess_period').val()]);
            $('#dataTable_enroList tr[id=enrolist_'+upID+'] td:nth-child(11)').html(mainObj['rulstatus'][$('#form_enroList_modal input[name=sess_status]:checked').val()]);
          //更新彈出視窗的URI
          var resetURI="id="+upID;
            resetURI+="&sessmode="+$('#form_enroList_modal #sess_mode').val();
            resetURI+="&fixed="+$('#form_enroList_modal #sess_fixed').val();
            resetURI+="&posit="+$('#form_enroList_modal #sess_positnum').val();
            resetURI+="&group="+$('#form_enroList_modal #sess_positgroup').val();
            resetURI+="&top="+$('#form_enroList_modal #sess_topsearch').val();
            resetURI+="&indist="+($('#form_enroList_modal #sess_indistinct').is(':checked') ? 1 : 0);
            resetURI+="&weekday="+($('#form_enroList_modal #sess_weekday').is(':checked') ? 1 : 0);
            resetURI+="&weekend="+($('#form_enroList_modal #sess_weekend').is(':checked') ? 1 : 0);
            resetURI+="&period="+$('#form_enroList_modal #sess_period').val();
            resetURI+="&status="+$('#form_enroList_modal input[name=sess_status]:checked').val();
            $('#enrolist_'+upID).attr('data-uri',resetURI);
            //-- Reset Modal --//
            $('#enroList_modal .modal-title').html('新增課程梯次');
            $('#form_enroList_modal').attr('data-enroid','0');
            $('#enroList_modal .modal-container').html('');
            $('#enroList_modal #modal_upBut').html('新增');
            $('#enroList_modal #modal_delBut').css("visibility", "hidden");
            $('#enroList_modal').removeClass('show').hide();
        }
        else if(accE=='func_enro_delete') {
            $('#dataTable_enroList tr[id=enrolist_'+data['enroID']+']').remove();
            var numRows = $('#dataTable_enroList tbody tr').length;
            $('#dataTable_enroList tbody tr').each(function( index ) {
              index++;
              $(this).find('td:nth-child(1)').html(index+'.');
            });
            //-- Reset Modal --//
            $('#enroList_modal .modal-title').html('新增課程梯次');
            $('#form_enroList_modal').attr('data-enroid','0');
            $('#enroList_modal .modal-container').html('');
            $('#enroList_modal #modal_upBut').html('新增');
            $('#enroList_modal #modal_delBut').css("visibility", "hidden");
            $('#enroList_modal').removeClass('show').hide();
        }
      },
      error: function (data) {
        console.log(data);
        window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
      }
    });
  }

////////////////////////////////////////////////////////////////
// ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
// ---------------------------------------------------------- //
////////////////////////////////////////////////////////////////
  $(document).ready(function() {
    //------------------------- Tooltips -----------------------------//
    $('[data-toggle="tooltip"]').tooltip();
    // iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });
    //------------------ datatable -----------------------
    var sessListTB = datatableCreate('dataTable_enroList');
    var enrolleeslistTB = datatableCreate('dataTable_enrolleeList');
    var ratingListTB = datatableCreate('dataTable_ratingList');
    //------------------- suneditor -------------------//
    sunBrief = suneditorCreate('sess_brief','ez','100%','204');
    sunBrief.onChange = function (contents, core) {
      if(contents !== $('#sess_brief').val()){
        briefActive = true;
        window.parent.$('#topBut_save').addClass('active');
      } else {
        briefActive = false;
        if(bodyActive == false) { window.parent.$('#topBut_save').removeClass('active'); }
      }
    }

    sunBody = suneditorCreate('sess_body','full','100%');
    sunBody.onChange = function (contents, core) {
      if(contents !== $('#sess_body').val()){
        bodyActive = true;
        window.parent.$('#topBut_save').addClass('active');
      } else {
        bodyActive = false;
        if(briefActive == false) { window.parent.$('#topBut_save').removeClass('active'); }
      }
    }
    //------------------------- 鍵入搜尋 ------------------------------- //
    $(".js-data-example-ajax").select2({
        language: {
          errorLoading: function () {return '錯誤！無法顯示搜尋結果！'},
          searching: function () {return '搜尋中…';},
          inputTooShort:function(n){return"請再輸入"+(n.minimum-n.input.length)+"個字元"},
          loadingMore:function(){return"載入更多結果…"},
          noResults:function(){return"沒有找到相符的項目"},
          removeAllItems:function(){return"刪除所有項目"}
        },
        width: '100%',
        maximumInputLength: 20,// 最多字元限制
        minimumInputLength: 2,// 最少字元才觸發尋找, 0 不指定
        //tags: true,// 當找不到可以使用輸入的文字
        allowClear: true,
        placeholder: '請輸入顯示名稱或ID...',
        cache: true,
        delay: 500,
        ajax: {
            type: 'post',
            url: "../funcFiles/adv_act_func.php",
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    type: 'user_search',
                    token: getTokenVal(),                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        
                    formAcce: 'func_adv_mbsearch'
                }
                return query;
            },
            beforeSend: function () {
              //alert('paramssdfgs');
            },
            processResults: function (data) {
              console.log(data);
                return {
                    results: data
                };
            }
        }
    });
    ////////////////////////////////////////////////////////////mainObj
    //------------------- DATEMASK 日期輸入元件初始化 -------------------//
    $('#sess_startdate').inputmask({
        alias: "datetime",
        inputFormat: "yyyy-mm-dd",
        placeholder: "yyyy-mm-dd",
        removeMaskOnSubmit: true, 
        showMaskOnFocus: true, // 只有滑鼠點擊時才顯示提示底線  
        jitMasking: false,//關閉即時遮罩，確保網頁一載入就看得到完整 placeholder   
        clearIncomplete: true //如果沒填完整，離開焦點（Blur）時會自動清空整個欄位
    });
    $('#sess_starttime').inputmask({
        alias: "datetime",
        inputFormat: "HH:MM",
        placeholder: "hh:mm",
        removeMaskOnSubmit: true, 
        showMaskOnFocus: true,        
        jitMasking: false,       
        clearIncomplete: true
    });
    //---------------------- 日期選擇後自動勾選星期 --------------------//
    $('#sess_startdate').change(function(){
        var currDate = $(this).val();
        if(currDate == '') { return false; } //alert('changed!');
        var getWeekday = _js_fn_getWeekday(currDate); 
        // 先尋找所有 name 為 sess_onweek[] 的 checkbox，並命令 iCheck 把勾勾通通拔除
        $('input[name="sess_onweek[]"]').prop('checked', false).prop('disabled', false).parent().removeClass('icheck-danger').addClass('icheck-primary');
        $('input[name="sess_onweek[]"][value="' + getWeekday.number + '"]').prop('checked', true).prop('disabled', true).parent().addClass('icheck-danger');
    });
    //------------------ 選擇「可提前進入等待」後動作 ------------------//
    $('#sess_beforehost').change(function(){
        var isChecked = $(this).prop('checked'); 
        var currentVal = $(this).val(); // 拿到當前點擊的數值 (例如 0~6)
        var jbhELM = $('#sess_jbhtime');
        if (isChecked) {
            jbhELM.val('5').prop('readonly', false).select();
        } else {
            jbhELM.val('0').prop('readonly', true);
        }

    });
    //------------- 選擇的週間星期數量超過課程總堂數時自動限制 -----------//
    $('input[name="sess_onweek[]"]').change(function(){
        var totalChecked = $('input[name="sess_onweek[]"]:checked').length;
        var totalLesson = parseInt($('#sess_lesson').val()); 
        if(totalChecked > totalLesson){
            alert('您選擇的週間星期數已經超過了課程總堂數 '+totalLesson+' 堂，請先取消其他星期的勾選，再重新選擇！！');
            $(this).prop('checked', false);
        }
    });

    //--------- 設定課程總堂數時，超過選擇的週間星期數量時自動限制 --------//
    $('#sess_lesson').change(function(){
        var totalChecked = $('input[name="sess_onweek[]"]:checked').length;
        var totalLesson = parseInt($(this).val()); 
        if(totalChecked > totalLesson){
            alert('您設定的課程總堂數 '+totalLesson+' 堂，低於所選的週間星期數，請先取消足夠的星期的勾選，再重新輸入課堂數！！');
            $(this).val(totalChecked);
        }

    });
    //--------- 點選「創建ZOOM課堂」時動作JazNOTE:尚未完成 --------//
    $('#creatMeetingID').click(function(){
        Swal.fire({
            title: "人生第五季系統提醒",
            imageUrl: '../imageFiles/5slLOGO_sB&W.png', 
            imageWidth: 60,  // 👈 2. 直接指定寬度 (px)，一秒縮小！
            imageHeight: 60, // 👈 3. 直接指定高度 (px)
            html: '<div class="text-left pl-5 small" style="line-height: 1.5;">這個動作將會利用 Zoom API 進行遠端會議/課程管理，並依據這筆課程梯次紀錄內容，自動建立一個新的會議室(教室)，確定要繼續進行嗎？！</div>',
            //icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3c8dbc",
            cancelButtonColor: "#C35050",
            confirmButtonText: "是的，我要繼續!",
            cancelButtonText: "取消"
        }).then((result) => {            
            if (result.isConfirmed) {
                var sessID = '<?php echo $sessID; ?>';
                var sessName = $('#sess_altname').val();
                var sessCount = parseInt($('#sess_lesson').val());
                var startDate = $('#sess_startdate').val();
                var weekValues = $('input[name="sess_onweek[]"]:checked').map(function() {
                    return parseInt($(this).val(), 10); // 轉成整數（int）再塞入陣列
                }).get(); //將 jQuery 物件轉換成真正的 JavaScript 陣列
                var currAllDates = getCourseDatesTriple(startDate, weekValues, sessCount);
                var allDates = currAllDates.dates;
                //var allDatesStr = weekValues.join(',');
                //sess_secured sess_approval sess_beforehost sess_jbhtime sess_watermark sess_muteentry
                var zm_secured = $('#sess_secured').prop('checked') ? "true" : "false";
                var zm_approval = $('#sess_approval').prop('checked') ? "true" : "false";
                var zm_beforehost = $('#sess_beforehost').prop('checked') ? "true" : "false";
                var zm_jbhtime = $('#sess_jbhtime').val();
                var zm_watermark = $('#sess_watermark').prop('checked') ? "true" : "false";
                var zm_muteentry = $('#sess_muteentry').prop('checked') ? "true" : "false";

                var creatDatas = '以下是準備建立的會議間/教室資料，請先行確認:<br>';
                    creatDatas += '課程ID: ' + sessID + '<br>';
                    creatDatas += '課程名稱: ' + sessName + '<br>';
                    creatDatas += '週期(堂數): ' + sessCount + '<br>';
                    creatDatas += '開課日期: ' + startDate + '<br>';
                    creatDatas += '每堂課日期: <br>';
                    creatDatas += '---------------------------------<br>';
                    creatDatas += allDates.join('<br>');
                    creatDatas += '<br>---------------------------------<br>';
                    //creatDatas += 'allDatesStr: ' + allDatesStr + '<br>';     
                    creatDatas += '安全限制模式: ' + zm_secured + '<br>';
                    creatDatas += '預先註冊審核: ' + zm_approval + '<br>';
                    creatDatas += '提前進入教室等待: ' + zm_beforehost + '<br>';
                    creatDatas += '提前等待時間: ' + zm_jbhtime + '<br>';
                    creatDatas += '視訊浮水印: ' + zm_watermark + '<br>';
                    creatDatas += '麥克風靜音: ' + zm_muteentry + '<br>';
                    creatDatas += '<br><br>確定要繼續進行嗎？！';

                    Swal.fire({
                        title: "人生第五季系統提醒",
                        imageUrl: '../imageFiles/5slLOGO_sB&W.png', 
                        imageWidth: 60,  // 👈 2. 直接指定寬度 (px)，一秒縮小！
                        imageHeight: 60, // 👈 3. 直接指定高度 (px)
                        html: '<div class="text-left pl-5 small" style="line-height: 1.5;">' + creatDatas + '</div>',
                        showCancelButton: true,
                        confirmButtonColor: "#3c8dbc",
                        cancelButtonColor: "#C35050",
                        confirmButtonText: "是的，我要繼續!",
                        cancelButtonText: "取消"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            //-------------- ZOOM API 連結 --------------



                            //-------------- //ZOOM API 連結 --------------
                            Swal.fire({
                                title: "完成!",
                                text: "已經成功建立了會議/課程！並更新了課程編號紀錄",
                                icon: "success",
                                confirmButtonColor: "#3c8dbc",
                                confirmButtonText: "確定"
                            });
                        }
                    });

            }
            
        });
        //myAlert('---------------------------------\n' + creatDatas + '\n確定要繼續進行嗎？！');
        //if(!confirm('---------------------------------\n' + creatDatas + '\n確定要繼續進行嗎？！')) { return false; }
    });

    //--------- 改變課程代表飾帶時，自動顯示所選代表飾帶 --------//
    $('#sess_ribbon').change(function(){
      var currVal = $(this).val();
      if( currVal != '' && currVal != '0' ) {
        $('#ribbonOnPhoto').css('display','block');
        $('#ribbonOnPhoto').find('div').removeClass('bg-danger').removeClass('bg-info').removeClass('bg-warning').removeClass('bg-success').addClass(mainObj['ribbonbg'][currVal]).html(mainObj['ribbon'][currVal]+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
      } else {
        $('#ribbonOnPhoto').css('display','none');
      }
      //window.location.replace(getCurrentURI('<?php //echo $backUrl; ?>','course_sessinfo.php'));
    });
    $('#selectBatch').change(function(){

      //window.location.replace(getCurrentURI('<?php //echo $backUrl; ?>&currID='+$(this).val(),'course_sessinfo.php'));
      window.location.replace(getCurrentURI('currID='+$(this).val(),'course_sessinfo.php'));
    });
    //---------------- 開啟報名會員彈出視窗 ----------------------
    /*
    //$('#sess_popup').click(function(e){data-enrono
    $('#dataTable_enroList').on('click',"tbody tr",function(e){
    //$(".modal-container tr[id^='enrolist_']").click(function(e){
      var currID=$(this).attr("data-enroid");
      var currURI=$(this).attr("data-uri");
      var enroNum=$(this).attr("data-enrono");
      //alert(currURI);
      $('#enroList_modal').show().addClass('show');
      $('#enroList_modal .modal-title').html('快速編輯「' + $('#course_title').val() + '」第' + enroNum + '梯次課程');
      $('#enroList_modal #modal_upBut').html('更新');
      $('#enroList_modal #modal_delBut').css("visibility", "visible");
      $('#form_enroList_modal').attr('data-enroid',currID);
      $('#enroList_modal .modal-container').load('include_courseSess.php?'+currURI);
    });
    */
    //----------------- /開啟課程梯次彈出視窗 --------------------
    //----------------- 彈出窗關閉按鍵 -----------------------
    $('#enroList_modal #modalCloseTop, #enroList_modal #modalCloseBut').click(function(e){ 
      $('#enroList_modal .modal-title').html('新增「' + $('#course_title').val() + '」課程梯次');
      $('#form_enroList_modal').attr('data-enroid','0');
      $('#enroList_modal .modal-container').html('');
      $('#enroList_modal #modal_upBut').html('新增');
      $('#enroList_modal #modal_delBut').css("visibility", "hidden");
      $('#enroList_modal').removeClass('show').hide();
    });
    //---------------- 彈框按鈕_編輯/上傳按鈕 ----------------
    $('#enroList_modal #modal_upBut').click(function(e){
      $('#form_enroList_modal').attr('data-func','func_sess_infoUpdate');
      var getValid = Validator.validate($('#form_enroList_modal'));
      if(!getValid){
        return false;
      } else {
        ajaxPOST($('#form_enroList_modal'));
      }
    });
    //-------------------- 彈框按鈕_刪除按鈕 --------------------//
    $('#enroList_modal #modal_delBut').click(function(e){
      $('#form_enroList_modal').attr('data-func','func_sess_delete');
      ajaxPOST($('#form_enroList_modal'));
    });
    //---------------- 內頁右上方小導航返回列表頁 ----------------//
    // 內頁右上方小導航返回列表頁
    $('li.prevLink').on('click', function(){
      //window.location.replace(getCurrentURI('<?php //echo $backUrl; ?>','course_list.php'));
      window.location.replace(getCurrentURI('','course_list.php'));
    });
    //------------------ 同步校正操作 ------------------//
    $('.fa-code-compare').on('click', function() {
      //將當前點擊元素的所有 class 轉成陣列 (例如: ['fas', 'fa-2x', 'fa-code-compare', 'doRate'])
      const classList = Array.from(this.classList);
      const targetIndex = classList.indexOf('fa-code-compare');
      let thisActType = '';
      if (targetIndex !== -1 && targetIndex + 1 < classList.length) {
          thisActType = classList[targetIndex + 1];
      }
      var formObj = {
        forVal: parseInt('<?php echo $idForItem; ?>'),//此值即爲指定的資料表，在這兒爲「 2 」課程商品資料表
        foridVal: parseInt('<?php echo $courseID; ?>'),//這個值即爲 $mdID，搭配 forVal 值便是指 forVal 代表的資料表主鍵ID爲 foridVal 的記錄
        passHour: parseFloat('<?php echo $hourPassed ?? 0; ?>'),//此值即爲當操作是爲了校正評價記錄值時，評價的模式，課程商品的評價模式爲 1：五組星級評分
        actionType: thisActType,//校正操作的類型：doVisit 爲造訪次數； doRate 爲評價分數
        thisVisits: parseInt($('#thisVisits').html()),
        thisRateTop: parseInt($('#thisRateTop').html()),
        thisRateBottom: parseInt($('#thisRateBottom').html())
      };
      $.ajax({
              type: 'post',
              url: '../funcFiles/sys_act_func.php',
              data: {
                'arrDatA': formObj,
                'formAcce': 'func_data_revision',
                'token': getTokenVal()
              },
              dataType: 'json',
              //async: false, 
              beforeSend:function(){
                var comF = '繼續操作將比對此份課程商品的造訪次數與系統內造訪歷史記錄，若是有差異時將進行校正，以系統的歷史記錄爲基準值回寫到此課程商品的造訪次數欄位。\r\n確定進行同步校正操作？！';
                if(!confirm(comF)){return false;}
              },
              success: function (data) {
                console.log(data);
                window.parent.toastr[data['msgType']](data['msgStr']);
                if(data['RTN']==1){
                  if(thisActType=='doVisit' && data?.newVisits) {
                      $('#thisVisits').html(data['newVisits']);
                  } else if(thisActType=='doRate' && data?.newRateTop && data?.newRateBottom) {
                    const newTotalSum = Number(data['newRateTop']);
                    const newTotalCount = Number(data['newRateBottom']);
                    const newTotalAvg = data['newTotalAvg'];
                    const newLSR = data['newLSR'];
                    const newTDS = data['newTDS'];
                    //寫入頁面目標位置
                    $('#thisRateTop').html(newTotalSum);
                    $('#thisRateBottom').html(newTotalCount);
                    //生成新的格式，並寫入到目標位置
                    const newRateAvg = (newTotalCount === 0) 
                        ? "0.00" 
                        : newTotalAvg.toFixed(2);//[*]:特別注意，這裡的 newTotalCount 的乘數決定在有多少組的星級評分，ratemode 為 1 時乘數為 5,ratemode 為 2 時乘數為 1,ratemode 為 3 時計算方式不同
                    const [newRateAvgInt, newRateAvgDec] = newRateAvg.split('.');
                    const starsHTML = _js_fn_getStars(newRateAvg, true);
                    const starsEMOJI = _js_fn_getStars(newLSR);
                    $('#thisRateAvg > span:first').html(newRateAvgInt);
                    $('#thisRateAvg > span:eq(1):last').html('.' + newRateAvgDec);
                    $('#thisStars').html(starsHTML);
                    $('#thisLSR').html(starsEMOJI + ' ' + newLSR);
                    $('#thisTDS').html(newTDS);
                  }
                }
              },
              error: function (data) {
                console.log(data);
              }
          });
    });
    //-------- 加載完成後更改外框主頁中樹狀目錄開合狀態 --------//
    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
///////////////////////////////////////////////////////////////////
  });

</script>
</body>
</html>
