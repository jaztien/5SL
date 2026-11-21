<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_COURSE');
define('PAGE_ITEM','ITEM_MERCH');
define('bkRedir',1);
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
require_once('../classFiles/eRanking.class.php');
//
$courseID=currID;
//
$res = $DB->row("SELECT * FROM fsl_courses WHERE course_id=?",array($courseID));
//功能陣列建立
$courseJS=_fn_getCachedJsonData("../jsonFiles/courses.json");
$geJS=_fn_getCachedJsonData("../jsonFiles/general.json");
//------------------- 取得並且定義必要參數 ------------------------//
$idForItem = 5;//對應 general.json["idfor"] ## 1-會員(廠商、專家…等)；2-課程商品；3-文章;4-服務；5-交易；7-加購項目
$idForJS = $geJS['idfor'][$idForItem];
$idForRateMode = $idForJS['ratemode'] ?? 5;

//--------------------- 評分計算 -----------------------
/*
$counT="SELECT AVG((rat_s1 + rat_s2 + rat_s3 + rat_s4 + rat_s5)/5) AS totAVE, AVG(rat_s1) AS S1, AVG(rat_s2) AS S2, AVG(rat_s3) AS S3, AVG(rat_s4) AS S4, AVG(rat_s5) AS S5 FROM fsl_rating WHERE rat_forid=? AND rat_for=?";
$cnt = $DB->query($counT,array($courseID,5));
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
$totSum = $res['course_ratetop'] ?? 0;
$totCount = $res['course_ratebottom'] ?? 0;
$totAVE = $res['course_s1'] ?? 0;
//代表圖像設定
if(!empty($courseID)&&file_exists("../uploadFiles/course/courseimg_".$courseID."_thumb.jpg")){
  $cropImgDisplay = 'show';
  $mainimgpath = '../uploadFiles/course/courseimg_'.$courseID.'_thumb.jpg?'.time();
  $mainimgchk = 'courseimg_'.$courseID.'_.jpg';
} else {
  $cropImgDisplay = 'hide';
  $mainimgpath = '../uploadFiles/imageFiles/nopic_400x300.jpg';
  $mainimgchk = "";
}
//
$totalSess = 0;

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
	
  <title> Fifth Season Life 人生第五季 | 課程詳細資料 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
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
    table#dataTable_sessList thead > tr:first-child > th{
      line-height:0px;
      background-color:#5F6E86;
      opacity: 0.85;
      color:#f8f9fa;
    }
    table#dataTable_sessList thead > tr:last-child > th{
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
              <h4 class="m-0"><?php echo !empty($courseID) ? "編輯" : "新增"; ?>課程資料<span class="tiny"> 課程資料內容編輯</span></h4>
              
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                  <li class="breadcrumb-item">首頁</li>
                  <li class="breadcrumb-item">課程商品資料管理</li>
                  <li class="breadcrumb-item prevLink" alt="返回列表" title="返回列表" onClick="window.location.replace('course_list.php');">課程資料紀錄</li>
                <li class="breadcrumb-item active">課程詳細資料</li>
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
                    <div class="image_area crop-group" id="main_crop_area">
                      <i class="nav-icon fas fa-2x fa-trash-can cropImgTrash<?php echo " ".$cropImgDisplay; ?>" alt="刪除圖像" title="刪除圖像"></i>
                      <form method="post">
                        <input type="hidden" name="mainimgchk" id="mainimgchk" class="mainimgchk notSubmit" value="<?php echo $mainimgchk; ?>">
                        <label for="main_cropUploadImage">
                          <img id="avatarImage" name="avatarImage" style="border-radius:10px;" class="img-responsive img-fluid imgEDicon avatarImage" src="<?php echo $mainimgpath; ?>" alt="代表圖像" title="代表圖像"/>
                          <div class="overlay">
                            <div class="overtext"><?php echo !empty($courseID) ? "更改" : "新增"; ?>代表圖像</div>
                          </div>
                          <input type="file" id="main_cropUploadImage" name="image" class="image cropUploadImage notSubmit" style="display:none" crop-width="800" crop-height="600"<?php echo NdisAble; ?>>
                        </label>
                      </form>
                      <!-- -------------------- 裁切主圖彈出視窗 -------------------- -->
                      <?php include('include_avatarmodal.php'); ?>
                    </div>
                  </div>
                  <h5 class="profile-main text-center"><?php echo !empty($courseID) ? ($res['course_title'] ?? "&nbsp;") : "預上傳代表圖像<br><span style='font-size:0.75em;'>(可於編輯時補上)</span>"; ?></h5>
                  <p class="text-muted text-center"><?php echo $res['course_subscript'] ?? "&nbsp;"; ?></p>

                  <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                      <b>種類</b> <a class="float-right"><?php echo $courseJS['type'][$res['course_type'] ?? null] ?? "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>狀態</b> <a class="float-right<?php echo !empty($courseID) && (($res['course_status'] ?? 0)==1) ? "" : " text-danger"; ?>"><?php echo !empty($courseID) ? $courseJS['status'][$res['course_status'] ?? null] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>編輯日期</b> <a class="float-right"><?php echo $res['course_published'] ?? "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>建立日期</b> <a class="float-right" style="color:black;"><b><?php echo $res['course_updated'] ?? "&nbsp;"; ?></b></a>
                    </li>
                    <li class="list-group-item">
                      <b>選購次數</b> <a class="float-right" style="color:#cd5757;font-size:1.5em;"><b><?php echo !empty($res['course_attend']) ? number_format($res['course_attend']) : 0; ?></b></a>
                    </li>
                  </ul>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
              <!-- Visits -->
              <div class="card card-info card-outline">
                <div class="card-header">
                  <h3 class="card-title">造訪記錄</h3>
                  <div class="card-tools">
                    <?php if(!empty($courseID)){ ?>
                    <button type="button" class="btn btn-tool" title="同步校正">
                        <i class="fas fa-2x fa-solid fa-code-compare doVisit"></i>
                    </button>
                    <?php } ?>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body box-profile">
                    <div id="thisVisits" style="font-size:48px;font-weight:bold;margin-top:-36px;">
                    <?PHP echo $res['course_visits']; ?>
                    </div>
                </div>
              </div>
              <!-- /Visits -->

              <!-- Rating -->
              <div class="card card-warning card-outline">
                <div class="card-header">
                  <h3 class="card-title">項目受評</h3>
                  <div class="card-tools">
                    <?php if(!empty($courseID)){ ?>
                    <button type="button" class="btn btn-tool" title="同步校正">
                        <i class="fas fa-2x fa-solid fa-code-compare doRate"></i>
                    </button>
                    <?php } ?>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body box-profile">
                    <div id="thisStars" style="margin-left:42px;margin-top:-8px;color:goldenrod;">
                    <?PHP echo Evs::stars($totAVE, true); ?>
                    </div>
                    <div id="thisRateAvg" style="font-size:60px;font-weight:bold;margin-top:-32px;">
                      <?PHP $getAvg = explode('.',round($totAVE,2)); ?>
                      <span><?PHP echo $getAvg[0] ?? '0'; ?></span><span style="font-size:32px;font-weight:normal;">.<?PHP echo $getAvg[1] ?? '00'; ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <hr>
                    <div class="form-group row" style="margin-bottom:-12px;">
                      <div class="col-sm-4">獲評總分</div>
                      <div class="col-sm-8" id="thisRateTop" style="font-size:24px;font-weight:bold;color:goldenrod;"><?PHP echo $res['course_ratetop'] ?? 0 ?></div>
                    </div>
                    <hr>
                    <div class="form-group row">
                      <div class="col-sm-4">總評次數</div>
                      <div class="col-sm-8" id="thisRateBottom" style="font-size:18px;font-weight:bold;color:#236FB7;"><?PHP echo $res['course_ratebottom'] ?? 0; ?></div>
                    </div>
                </div>
                <div class="card-footer" style="padding:4px 0px 0px 8px;">
                  LSR平滑星級: <span class="my-stars" id="thisLSR">
                  <?php
                    if(!empty($courseID)){
                        $totSum = $res['course_ratetop'] ?? 0;
                        $totCount = $res['course_ratebottom'] ?? 0;
                        $views = $res['course_visits'] ?? 0;
                        $hourPassed = _fn_hours_passed($res['course_published']);
                        $score = Evs::lsrFromAvg($res['course_s1'], $totCount); 
                        $starText = Evs::stars($score, true);
                        $promoScore = Evs::tds($score, $totCount, $res['course_visits'], $hourPassed);
                        echo $starText. ' '. $score;
                    } else {
                        echo "&nbsp;";
                    }
                  ?>
                  </span>
                </div>
                <div class="card-footer" style="padding:0px 0px 0px 8px;">
                  TDS熱度權重: <span style="font-weight:bold;font-size:1.5em;" id="thisTDS"><?php echo !empty($courseID) ? number_format((float)$promoScore, 5) :  "&nbsp;"; ?></span>
                </div>
              </div>
              <!-- /Rating -->
              <div><span style="font-size:large;"> <?php if(!empty($courseID)) { echo "【ID: ".$courseID."】"; } ?>【 course_detail.php 】</span></div>
            </div>
            <!-- /.col -->
            <div class="col-md-9">
              <div class="card card-secondary">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                      <li class="nav-item"><a class="nav-link active" href="#tabMenu1" data-index="1" data-toggle="tab" data-topbut="1">課程基本資料</a></li>
                      <li class="nav-item"><a class="nav-link" href="#tabMenu2" data-index="2" data-toggle="tab" data-topbut="1">介紹版面編輯</a></li>
                      <?php if(!empty($courseID)) { ?>
                      <li class="nav-item"><a class="nav-link" href="#tabMenu3" data-index="3" data-toggle="tab" data-topbut="0">課程梯次列表</a></li>
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
                      <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/course_act_func.php" method="post" data-formmode="infoupdate" data-func="func_course_infoUpdate">
                        <div class="card-body">
                          <div class="form-group row">
                            <label for="course_title" class="col-sm-2 col-form-label">課程抬頭</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="course_title" name="course_title" value="<?php echo !empty($res['course_title']) ? $res['course_title'] : ""; ?>" placeholder="課程抬頭" data-min="4" data-required<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="course_subscript" class="col-sm-2 col-form-label">下標</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="course_subscript" name="course_subscript" value="<?php echo !empty($res['course_subscript']) ? $res['course_subscript'] : ""; ?>" placeholder="下標" data-min="4" data-required<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="course_alt" class="col-sm-2 col-form-label">滑鼠彈窗</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="course_alt" name="course_alt" value="<?php echo !empty($res['course_alt']) ? $res['course_alt'] : ""; ?>" placeholder="滑鼠彈窗"<?php echo NreadOnly; ?>>
                            </div>
                            <label for="course_ribbon" class="col-sm-2 col-form-label">代表飾帶</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="course_ribbon" name="course_ribbon" placeholder="套裝飾帶"<?php echo NreadOnly; ?>>
                                  <?php
                                    foreach($courseJS['ribbon'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        echo (($res['course_ribbon'] ?? '') === $key) ? ' selected' : '';
                                        echo '>'.$value.'</option>';
                                    }
                                  ?>
                                </select>
                            </div>
                            
                          </div>
                          <div class="form-group row">
                            <label for="course_brief" class="col-sm-2 col-form-label">簡述</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="course_brief" name="course_brief" rows="4" placeholder="簡述" data-min="4" data-required<?php echo NreadOnly; ?>><?php echo !empty($res['course_brief']) ? stripslashes($res['course_brief']) : ""; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="course_keycode" class="col-sm-2 col-form-label">指定代碼</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="course_keycode" name="course_keycode" value="<?php echo !empty($res['course_keycode']) ? $res['course_keycode'] : ""; ?>" placeholder="指定代碼" data-min="4" data-type="keycode" data-required<?php echo NreadOnly; ?>>
                            </div>
                            <label for="course_type" class="col-sm-2 col-form-label">課程內容種類</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="course_type" name="course_type" placeholder="課程內容種類" data-required<?php echo NdisAble; ?>>
                                <?php
                                  foreach($courseJS['type'] as $key => $value){
                                    echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      echo (($res['course_type'] ?? '') === $key) ? ' selected' : '';
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="course_duration" class="col-sm-2 col-form-label">課程時長</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="course_duration" name="course_duration" value="<?php echo $res['course_duration'] ?? "0"; ?>" placeholder="課程時長" data-required<?php echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">分鐘</span>
                                </div>
                              </div>
                            </div>
                            <label for="course_lesson" class="col-sm-2 col-form-label">課程週期</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="course_lesson" name="course_lesson" value="<?php echo $res['course_lesson'] ?? "0"; ?>" placeholder="課程週期"  data-required<?php echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">堂課</span>
                                </div>
                              </div>
                            </div>
                          </div> 
                          <div class="form-group row">
                            <label for="course_min" class="col-sm-2 col-form-label">最少課程學員</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="course_min" name="course_min" value="<?php echo $res['course_min'] ?? "0"; ?>" placeholder="基本學員數量"<?php echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span id="limit_unit" name="limit_unit" class="input-group-text areaUnit">名</span>
                                </div>
                              </div>
                            </div>
                            <label for="course_max" class="col-sm-2 col-form-label">課程學員上限</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="course_max" name="course_max" value="<?php echo $res['course_max'] ?? "0"; ?>" placeholder="最多學員數量"<?php echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span id="limit_unit" name="limit_unit" class="input-group-text areaUnit">名</span>
                                </div>
                              </div>
                            </div>
                          </div>  
                          <div class="form-group row">
                            <label for="course_cost" class="col-sm-2 col-form-label">課程計費</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="course_cost" name="course_cost" value="<?php echo !empty($res['course_cost']) ? $res['course_cost'] : "0"; ?>" placeholder="課程計費"<?php echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span id="cost_unit" name="cost_unit" class="input-group-text areaUnit">元<?php
                                  if(!empty($res['course_deduct'])) echo "/".$courseJS['deductS'][$res['course_deduct']];
                                  ?></span>
                                </div>
                              </div>
                            </div>
                            <label for="course_deduct" class="col-sm-2 col-form-label">收費模式</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="course_deduct" name="course_deduct"<?php echo NdisAble; ?>>
                                <?php
                                  foreach($courseJS['deduct'] as $key => $value){
                                    echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      echo (($res['course_deduct'] ?? '') === $key) ? ' selected' : '';
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="course_status" class="col-sm-2 col-form-label">課程狀態</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="course_status" name="course_status" data-origval="<?php if(!empty($courseID) && !is_null($res['course_status'])) echo $res['course_status']; ?>" placehold=""<?php echo NdisAble; ?>>
                                <?php
                                  foreach($courseJS['status'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      echo (($res['course_status'] ?? '') === $key) ? ' selected' : '';
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
                      <form id="form_tabMenu2" name="form_tabMenu2" action="../funcFiles/course_act_func.php" method="post" data-formmode="htmlupdate" data-func="func_course_infoUpdate">
                        <div class="card-body">
                          <textarea name="course_body" id="course_body"><?php echo !empty($res['course_body']) ? stripslashes($res['course_body']): ""; ?></textarea>
                        </div>
                      </form>
                    </div>
                    <!-- /第二分頁課程介紹內容主體編輯 -->
                    <!-- 第三分頁課程梯次列表 -->
                    <div class="tab-pane" id="tabMenu3">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;一個課程內必須包含至少一組課程梯次，會員選擇了課程後，系統將自動提取套裝資料表與課程梯次表內容合併生成一個或多個執行紀錄於資料表 adv_cues 中，等候前台依照執行內容執行課程。</div>
                      <hr>
                      <!-- /.card-header -->
                      <div class="card-body">
                        <table id="dataTable_sessList" class="table table-bordered table-striped table-hover" data-butClass="gray" data-pagemax="10" data-jumpto="0"  but-popup="新增梯次" data-include="include_courseSess.php?courseid=<?php echo $courseID; ?>" data-triggerto="openSessModel">
                          <thead>
                            <tr class="gray" style="line-height:6px;">
                              <th>梯次</th>
                              <th>別名</th>
                              <th>開課日期</th>
                              <th>時間</th>
                              <th>週間</th>
                              <th>報名</th>
                              <th>教室ID</th>
                              <th>狀態</th>
                              <th>操作</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                              $sql="SELECT * FROM fsl_course_sessions WHERE course_id=?";
                              $srr = $DB->query($sql,array($courseID));
                              $totalSess = count($srr);
                              $i = 0; //以$i為$nameList這個陣列的索引值
                              while($i < $totalSess){ 
                                //$getURI="id=".$srr[$i]['sess_id']."&batch=".$srr[$i]['sess_batch']."&altname=".$srr[$i]['sess_altnsme']."&startdate=".$srr[$i]['sess_startdate']."&starttime=".$srr[$i]['sess_starttime']."&onweek=".$srr[$i]['sess_onweek']."&ta1=".$srr[$i]['sess_ta1']."&ta2=".$srr[$i]['sess_ta2']."&enrollee=".$srr[$i]['sess_enrollee']."&meetingid=".$srr[$i]['sess_meetingid']."&sessstatus=".$srr[$i]['sess_status'];
                                //$getURI="courseid={$courseID}&sessid={$srr[$i]['sess_id']}&batch={$srr[$i]['sess_batch']}&altname={$srr[$i]['sess_altname']}&startdate={$srr[$i]['sess_startdate']}&starttime={$srr[$i]['sess_starttime']}&onweek={$srr[$i]['sess_onweek']}&ta1={$srr[$i]['sess_ta1']}&ta2={$srr[$i]['sess_ta2']}&enrollee={$srr[$i]['sess_enrollee']}&meetingid={$srr[$i]['sess_meetingid']}&sessstatus={$srr[$i]['sess_status']}";
                                $getURI="courseid={$courseID}&sessid={$srr[$i]['sess_id']}";
                            ?>
                                <tr style="cursor:pointer;" id="sesslist_<?php echo $srr[$i]['sess_id']; ?>" data-sessno="<?php echo $srr[$i]['sess_batch']; ?>" data-sessid="<?php echo $srr[$i]['sess_id']; ?>" data-uri="<?php echo $getURI; ?>">
                                  <td data-toggle="tooltip" title="快速編輯"><?php echo $srr[$i]['sess_batch'] ?? "&nbsp;"; ?>.</td>
                                  <td data-toggle="tooltip" title="快速編輯"><?php echo $srr[$i]['sess_altname'] ?? "&nbsp;"; ?></td>
                                  <td data-toggle="tooltip" title="快速編輯"><?php echo $srr[$i]['sess_startdate'] ?? "&nbsp;"; ?></td>
                                  <td data-toggle="tooltip" title="快速編輯"><?php echo $srr[$i]['sess_starttime'] ?? "&nbsp;"; ?></td>
                                  <td data-toggle="tooltip" title="快速編輯"><?php echo $srr[$i]['sess_onweek'] ?? "&nbsp;"; ?></td>
                                  <td data-toggle="tooltip" title="快速編輯"><?php echo $srr[$i]['sess_enrollee'] ?? "&nbsp;"; ?></td>
                                  <td data-toggle="tooltip" title="快速編輯"><?php echo $srr[$i]['sess_meetingid'] ?? "&nbsp;"; ?></td>
                                  <td data-toggle="tooltip" title="快速編輯"><?php echo $srr[$i]['sess_status'] ?? "&nbsp;"; ?></td>
                                  <td><button type="button" class="btn btn-info btn-block btn-sm" onClick="location.href='course_sessinfo.php&courseID=<?php echo $courseID; ?>&currID=<?php echo $srr[$i]['sess_id']; ?>';">進入專頁</button></td>
                                </tr>
                            <?php 
                                $i++;
                                } 
                              ?>
                          </tbody>
                        </table>
                        <input type="hidden" id="totalSess" name="totalSess" value="<?php echo $totalSess; ?>">
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
                              $sr = $DB->query($sql,array($courseID,1));
                              //$sql="SELECT * FROM data_logs WHERE lg_forid=? AND lg_for=?";
                              //$srr = $DB->query($sql,array($courseID,1));
                              //$i = 0; //以$i為$nameList這個陣列的索引值
                              //while($i<count($srr)){ 
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
                                $sr = $DB->query($sql,array($courseID,5));
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
        </form>
      <!-- -------------------- 課程梯次內容彈出視窗 -------------------- -->
      <div id="sessList_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="background:rgba(0,0,0,0.75);">
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
                <form id="form_sessList_modal" name="form_sessList_modal" data-sessid="0" action="../funcFiles/course_act_func.php" method="post" data-formmode="sessupdate" data-func="func_sess_infoUpdate">
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
  //【 DataTable 動態函式映射  】
  // 由 DataTable 經由 form.init.inc.js 產生的動態函式映射目標，這個函式名稱與 data-triggerto="openSessModel" 的值相同
  function openSessModel(trELM){
    var currID=trELM.attr("data-sessid");
    var currURI=trELM.attr("data-uri");
    var sessNum=trELM.attr("data-sessno");
    $('#sessList_modal').show().addClass('show');
    $('#sessList_modal .modal-title').html('快速編輯「' + $('#course_title').val() + '」第' + sessNum + '梯次課程');
    $('#sessList_modal #modal_upBut').html('更新');
    $('#sessList_modal #modal_delBut').css("visibility", "visible");
    $('#form_sessList_modal').attr('data-sessid',currID);
    $('#sessList_modal .modal-container').load('include_courseSess.php?'+currURI);
  }

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
          'formAcce':'func_course_delete',
          'token': getTokenVal(), 
          'courseID':'<?php echo $courseID; ?>'
        },
        dataType: 'json',//data format  
        async: false, 
      success: function (data) {
          if(data['RTN']==1){
            window.location.replace("course_list.php");
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
        var sessID = parseInt($('#form_sessList_modal').attr('data-sessid'));
        var formData={},datA={},datB={};
        var imgBase64='';

        if(accE=='func_course_infoUpdate'){
          datA = getFormObjects($('#form_tabMenu1'));
          datB = getFormObjects($('#form_tabMenu2'));
          formData = Object.assign(formData, datA, datB);
          if(null=='<?php echo $courseID; ?>' || 0=='<?php echo $courseID; ?>'){ accE='func_course_addnew'; }
          imgBase64 = $('#main_crop_area').data('cropImage64') || '';
        } else if(accE=='func_sess_infoUpdate'){
          datA = getFormObjects($('#form_sessList_modal'));
          formData = Object.assign(formData, datA);
          //Swal.fire(JSON.stringify(formData)); return false;
          if(sessID == '0'){
            accE ='func_sess_addnew';
          }
          imgBase64 = $('#sess_crop_area').data('cropImage64') || '';
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
              'sessID': sessID
            },
            dataType: 'json',
            async: false,
            beforeSend: function () {
                //alert(imgBase64);
                //return false;
            },
          success: function (data) {
            window.parent.toastr[data['msgType']](data['msgStr']);
            if(accE=='func_course_infoUpdate'){
                if (data['RTN']==1) {
                  var newType = mainObj['type'][$('#course_type').val()];
                  var newSta = mainObj['status'][$('#course_status').val()];
                  switch ($('#course_status').val()){
                    case '0': 
                      $('.box-profile > ul > li:nth-child(2) > a').removeClass( "text-success" ).removeClass( "text-warning" ).addClass( "text-danger" );
                    break;
                    case '1': 
                      $('.box-profile > ul > li:nth-child(2) > a').removeClass( "text-warning" ).removeClass( "text-danger" ).addClass( "text-success" );
                    break;
                    case '2': 
                      $('.box-profile > ul > li:nth-child(2) > a').removeClass( "text-success" ).removeClass( "text-danger" ).addClass( "text-warning" );
                    break;
                    case '3': 
                      $('.box-profile > ul > li:nth-child(2) > a').removeClass( "text-success" ).removeClass( "text-danger" ).addClass( "text-warning" );
                    break;
                  }
                  $('.profile-main').html($('#course_title').val());
                  $('.profile-main + p').html($('#course_subscript').val());
                  $('.box-profile > ul > li:nth-child(1) > a').html(newType);
                  $('.box-profile > ul > li:nth-child(2) > a').html(newSta);
                  $('.box-profile > ul > li:nth-child(3) > a').html(datA['upTime']);
                  /* **  重置表單異動數據  ** */
                  var newStatus = $('#form_tabMenu1 input[id="course_status"]');
                  var origStatus = newStatus.attr('data-origval');
                  if(newStatus.val() != origStatus){
                    if (newStatus.val() == '2') {
                        alert(newStatus.val());
                    }
                  }
                  ORGForm = CHGform.serialize();
                  window.parent.$('#topBut_save').removeClass('active');
                } else {
                  doButReset($('#form_tabMenu1,#form_tabMenu2'));   
                }
            }
            else if(accE=='func_course_addnew') {
              if (data['RTN']==1) {
                window.location.replace("course_detail.php?currID="+data['newID']);
                return false;
              }
            }
            //---------- 新增課程梯次 ----------
            else if(accE=='func_sess_addnew') {
              if(!data['arr']['newID'] || data['arr']['newID']==0){
                return false;
              }
                var r = data['arr'];
                //var rows = $('#dataTable_sessList tbody tr').length + 1;
                if($('#totalSess').val() == 0){ rows = 1; }
                var resetURI="id="+r['newID'];
                var onweekString = $('input[name="sess_onweek[]"]:checked').map(function() {
                    return $(this).val();
                }).get().join(',');
                    /*
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
                    */
                var html = '<tr id="sesslist_'+r['newID']+'" data-sessno="'+r['newBatch']+'" data-sessid="'+r['newID']+'" data-uri="'+resetURI+'"';
                    if($('#totalSess').val() == 0){ html += ' style="cursor:pointer;background-color:#EEEEEE;"'; }
                    else{ html += ' style="cursor:pointer;"'; }
                    html += '>';
                    html += '<td>'+r['newBatch']+'</td>';
                    html += '<td>'+r['sess_altname']+'</td>';
                    html += '<td>'+r['sess_startdate']+'</td>';
                    html += '<td>'+r['sess_starttime']+'</td>';
                    html += '<td>'+onweekString+'</td>';
                    html += '<td>'+r['sess_enrollee']+'</td>';
                    html += '<td>'+r['sess_meetingid']+'</td>';
                    html += '<td>'+r['sess_status']+'</td>';
                    html += '</tr>';
                if($('#totalSess').val() == 0){//當沒有任何規則紀錄時
                  $('#dataTable_sessList tbody tr').remove(); //移除「沒有紀錄」標註字樣
                  $('#totalSess').val('1');//將隱藏註記改寫為有存在紀錄
                }
                $('#dataTable_sessList tbody').append(html);
                //-- Reset Modal --//
                //$('#sessList_modal .modal-title').html('');
                $('#form_sessList_modal').attr('data-sessid','0');
                $('#sessList_modal .modal-container').html('');
                $('#sessList_modal').removeClass('show').hide(); 
            }
            //---------- 編輯課程梯次 ----------
            else if(accE=='func_sess_infoUpdate') {
              //更新列表中的資料
              var upID=$('#form_sessList_modal').attr('data-sessid');
              var onweekString = $('input[name="sess_onweek[]"]:checked').map(function() {
                  return $(this).val();
              }).get().join(',');

                $('#sesslist_' + upID).find('td:nth-child(2)').html($('#form_sessList_modal #sess_altname').val());// option:selected
                $('#sesslist_' + upID).find('td:nth-child(3)').html($('#form_sessList_modal #sess_startdate').val());
                $('#sesslist_' + upID).find('td:nth-child(4)').html($('#form_sessList_modal #sess_starttime').val());
                $('#sesslist_' + upID).find('td:nth-child(5)').html(onweekString);
                $('#sesslist_' + upID).find('td:nth-child(6)').html($('#form_sessList_modal #sess_enrollee').val());
                $('#sesslist_' + upID).find('td:nth-child(7)').html($('#form_sessList_modal #sess_meetingid').val());
                $('#sesslist_' + upID).find('td:nth-child(8)').html($('#form_sessList_modal #sess_status').val());
              //更新彈出視窗的URI
              /*
              var resetURI="id="+upID;
                resetURI+="&sessmode="+$('#form_sessList_modal #sess_mode').val();
                resetURI+="&fixed="+$('#form_sessList_modal #sess_fixed').val();
                resetURI+="&posit="+$('#form_sessList_modal #sess_positnum').val();
                resetURI+="&group="+$('#form_sessList_modal #sess_positgroup').val();
                resetURI+="&top="+$('#form_sessList_modal #sess_topsearch').val();
                resetURI+="&indist="+($('#form_sessList_modal #sess_indistinct').is(':checked') ? 1 : 0);
                resetURI+="&weekday="+($('#form_sessList_modal #sess_weekday').is(':checked') ? 1 : 0);
                resetURI+="&weekend="+($('#form_sessList_modal #sess_weekend').is(':checked') ? 1 : 0);
                resetURI+="&period="+$('#form_sessList_modal #sess_period').val();
                resetURI+="&status="+$('#form_sessList_modal input[name=sess_status]:checked').val();
                $('#sesslist_'+upID).attr('data-uri',resetURI);
                */
                //-- Reset Modal --//
                $('#sessList_modal .modal-title').html('新增課程梯次');
                $('#form_sessList_modal').attr('data-sessid','0');
                $('#sessList_modal .modal-container').html('');
                $('#sessList_modal #modal_upBut').html('新增');
                $('#sessList_modal #modal_delBut').css("visibility", "hidden");
                $('#sessList_modal').removeClass('show').hide();
            }
            else if(accE=='func_sess_delete') {
                $('#dataTable_sessList tr[id=sesslist_'+data['sessID']+']').remove();
                var numRows = $('#dataTable_sessList tbody tr').length;
                $('#dataTable_sessList tbody tr').each(function( index ) {
                  index++;
                  $(this).find('td:nth-child(1)').html(index+'.');
                });
                //-- Reset Modal --//
                $('#sessList_modal .modal-title').html('新增課程梯次');
                $('#form_sessList_modal').attr('data-sessid','0');
                $('#sessList_modal .modal-container').html('');
                $('#sessList_modal #modal_upBut').html('新增');
                $('#sessList_modal #modal_delBut').css("visibility", "hidden");
                $('#sessList_modal').removeClass('show').hide();
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
  $(function () {
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
    var sessListTB = datatableCreate('dataTable_sessList');
    var enrolleeslistTB = datatableCreate('dataTable_enrolleeList');
    var ratingListTB = datatableCreate('dataTable_ratingList');
    //------------------- suneditor -------------------//
    sunBrief = suneditorCreate('course_brief','ez','100%','204');
    sunBrief.onChange = function (contents, core) {
      if(contents !== $('#course_brief').val()){
        briefActive = true;
        window.parent.$('#topBut_save').addClass('active');
      } else {
        briefActive = false;
        if(bodyActive == false) { window.parent.$('#topBut_save').removeClass('active'); }
      }
    }

    sunBody = suneditorCreate('course_body','full','100%');
    sunBody.onChange = function (contents, core) {
      if(contents !== $('#course_body').val()){
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
        placeholder: '請輸入名稱、暱稱或ID...',
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
            processResults: function (data) {
              console.log(data);
                return {
                    results: data
                };
            }
        }
    });
    ////////////////////////////////////////////////////////////limit_unit
    /*
    $('#course_deduct').change(function(e){
      var toElm = $('span#cost_unit');
      var currVal = $(this).val();
      toElm.html('元/'+mainObj['deductS'][currVal]);
      switch(currVal) {  
        case "1":
          $('#course_visitlimit').val('0').prop('disabled', 'disabled');
          $('#course_limitmode').val('0').prop('disabled', 'disabled');
        break;
        case "2":
          $('#course_visitlimit').removeAttr("disabled");
          $('#course_limitmode').removeAttr("disabled");
        break;
      }
    });
    $('#course_limitmode').change(function(e){
      var toElm = $('span#limit_unit');
      var currVal = $(this).val();
      var preF = '次';
      if(parseInt(currVal)>0) { preF = preF+'/'; }
      toElm.html(preF+mainObj['limitmodeS'][currVal]);
    });
    */
    //---------------- 開啟課程梯次彈出視窗 ----------------------
    /*
    //$('#sess_popup').click(function(e){data-sessno
    $('#dataTable_sessList').on('click',"tbody tr",function(e){
    //$(".modal-container tr[id^='sesslist_']").click(function(e){
      var currID=$(this).attr("data-sessid");
      var currURI=$(this).attr("data-uri");
      var sessNum=$(this).attr("data-sessno");
      //alert(currURI);
      $('#sessList_modal').show().addClass('show');
      $('#sessList_modal .modal-title').html('快速編輯「' + $('#course_title').val() + '」第' + sessNum + '梯次課程');
      $('#sessList_modal #modal_upBut').html('更新');
      $('#sessList_modal #modal_delBut').css("visibility", "visible");
      $('#form_sessList_modal').attr('data-sessid',currID);
      $('#sessList_modal .modal-container').load('include_courseSess.php?'+currURI);
    });
    */
    //----------------- /開啟課程梯次彈出視窗 --------------------
    //----------------- 彈出窗關閉按鍵 -----------------------
    //$('#sessList_modal #modalCloseTop, #sessList_modal #modalCloseBut').click(function(){ 
    $('#sessList_modal #modalCloseTop, #sessList_modal #modalCloseBut').click(function(){ 
      $('#sessList_modal .modal-title').html('新增「' + $('#course_title').val() + '」課程梯次');
      $('#form_sessList_modal').attr('data-sessid','0');
      $('#sessList_modal .modal-container').html('');
      $('#sessList_modal #modal_upBut').html('新增');
      $('#sessList_modal #modal_delBut').css("visibility", "hidden");
      $('#sessList_modal').removeClass('show').hide();
    });
    //---------------- 彈框按鈕_編輯/上傳按鈕 ----------------
    $('#sessList_modal #modal_upBut').click(function(e){
      $('#form_sessList_modal').attr('data-func','func_sess_infoUpdate');
      var getValid = Validator.validate($('#form_sessList_modal'));
      if(!getValid){
        return false;
      } else {
        ajaxPOST($('#form_sessList_modal'));
      }
    });
    $('#sessList_modal #modal_delBut').click(function(e){
      $('#form_sessList_modal').attr('data-func','func_sess_delete');
      ajaxPOST($('#form_sessList_modal'));
    });
    // 內頁右上方小導航返回列表頁
    $('li.prevLink').on('click', function(){
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
