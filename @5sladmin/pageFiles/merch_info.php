<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_MERCH');
define('PAGE_ITEM','ITEM_MERCH');
define('bkRedir',1);
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
require_once('../classFiles/eRanking.class.php');
//
$mdID=currID;
//主要資料表欄位資料取得
$seleOPT = "*";
$sql = "SELECT {$seleOPT}";
$sql .= ", a.merch_coordinates.Long AS lng";
$sql .= ", a.merch_coordinates.Lat AS lat";
$sql .= ", b.* , a.merch_coordinates.Long AS lng";
$sql .= ", a.merch_coordinates.Lat AS lat";
$sql .= " FROM fsl_merchandise a";
$sql .= " INNER JOIN merch_info b ON b.merch_id=a.merch_id";
$sql .= " WHERE a.merch_id=?";
$res = $DB->row($sql,array($mdID));
if(!empty($res)){
  unset($res['merch_lng']);//移除經緯度欄位
  unset($res['merch_lat']);//移除經緯度欄位
  unset($res['merch_coordinates']);//移除經緯度欄位
}
//------------------- 創建展示圖檔及文件檔案陣列 ------------------------//
//功能陣列建立，取自JSON檔案
$merchJS=_fn_getCachedJsonData("../jsonFiles/merchandise.json");
$merchiJS=_fn_getCachedJsonData("../jsonFiles/merchinfo.json");
$geJS=_fn_getCachedJsonData("../jsonFiles/general.json");
$mbJS=_fn_getCachedJsonData("../jsonFiles/member.json");
$locJS=_fn_getCachedJsonData("../jsonFiles/locations.json");
$linkJS =  !empty($res['merch_vdolink']) ? json_decode($res['merch_vdolink'],true) : null;
//------------------- 取得並且定義必要參數 ------------------------//
$idForItem = 2;//對應 general.json["idfor"] ## 1-會員(廠商、專家…等)；2-課程商品；3-文章;4-服務；5-交易；6-廣告套裝；7-加購項目
$idForJS = $geJS['idfor'][$idForItem];
$idForRateMode = $idForJS['ratemode'] ?? 2;
//------------------- 創建展示圖檔及文件檔案陣列 ------------------------//
//防止過多的檔案，在主資料夾下建立子資料夾
$subFolder=ceil(intval($mdID)/100);
//可接受的檔案名稱，通常爲 jpg/jpeg
$imgArrType=$merchJS['mdimages']['arrType'];
//課程商品展示圖檔的名稱，通常爲客廳、廚房、主臥室...等指定位置的圖像
$imgArrTitle=$merchJS['mdimages']['arrTitle'];
//--------------------- 評分計算 -----------------------
$S1 = $res['merch_s1'] ?? 0;
$S2 = $res['merch_s2'] ?? 0;
$S3 = $res['merch_s3'] ?? 0;
$S4 = $res['merch_s4'] ?? 0;
$S5 = $res['merch_s5'] ?? 0;
//取得所有組的加總平均
$totAVE = ($S1 + $S2 + $S3 + $S4 + $S5)/5;
//取得所屬會員的名稱以及會員等級
if(!empty($res['mb_id'])){
  $mbArr = $DB->row("SELECT mb_title,mb_level FROM fsl_members WHERE mb_id=?",array($res['mb_id']));
}
//自JSON檔案取得評分項目名稱，連同評分分數，組合排列後做爲Javascript繪圖參數
//$ratingItem = $idForJS['rating']['1'].'|'.$S1.','.$idForJS['rating']['2'].'|'.$S2.','.$idForJS['rating']['3'].'|'.$S3.','.$idForJS['rating']['4'].'|'.$S4.','.$idForJS['rating']['5'].'|'.$S5;
$ratingItem = $idForJS['rating']['1'].','.$idForJS['rating']['2'].','.$idForJS['rating']['3'].','.$idForJS['rating']['4'].','.$idForJS['rating']['5'];
$ratingData = $S1.','.$S2.','.$S3.','.$S4.','.$S5;
//------------------ 取得售價異動記錄 -------------------------
try {
    // SQL Server 把時間切好（105 代表 YYYY-MM-DD 格式），並下別名
    $sql = "SELECT CONVERT(VARCHAR(10), mdpr_create, 105) AS [date], mdpr_listprice AS [price] 
            FROM merch_prices WHERE merch_id = ?";
    $prr = $DB->query($sql, array($mdID));
    //直接用 PHP 內建函式把指定的直行（Column）抽出來變成一維清單
    $prARR = array(
        'date'  => array_column($prr, 'date'),
        'price' => array_column($prr, 'price')
    );
    // 補上最後一筆「當下即時價格」
    $prARR['date'][]  = 'Now';
    $prARR['price'][] = $res['merch_listprice'] ?? 0;

} catch (PDOException $e) {
    echo "價格軌跡解析異常：" . $e->getMessage();
}
//JazNOTE:還有加價購買記錄，記得要加進去！！
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	
  <title> Fifth Season Life 人生第五季 | 課程商品資料 </title>
  <link rel="shortcut icon" href="../imageFiles/favicon.ico" type="image/x-icon" />
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <!-- <link rel="stylesheet" href="../pluginFiles/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css"> -->
  <link rel="stylesheet" href="../pluginFiles/tempusdominus/tempus-dominus.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <!--
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-bs4/css/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-responsive/css/responsive.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-buttons/css/buttons.bootstrap4.css">
  -->
  <!-- Select2 -->
  <link rel="stylesheet" href="../pluginFiles/select2/css/select2.min.css">
  <link rel="stylesheet" href="../pluginFiles/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Dropzone JS style
  <link rel="stylesheet" href="../pluginFiles/dropzone/dropzone.css" /> -->
  <!-- Cropper JS style
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/> -->
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css<?php echo '?t='.time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.lightbox.css<?php echo '?t='.time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css<?php echo '?t='.time(); ?>">
<!-- -------------------------- /CSS Link -------------------------- -->
<style>
  #thisStars .star {
      display: inline-block !important;
      font-size: 20px;       /* 👈 星星的大小，可依您的網頁設計自由調整 */
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
</style>
</head>
<body class="layout-navbar-fixed hold-transition sidebar-mini">
  <!-- Light BOX -->
  <div id="boxModal" class="lightBoxFrame">
    <span class="close">&times;</span>
    <img class="imageViewURL" id="imageViewer">
    <div id="imageViewTXT"></div>
  </div>
  <!-- /Light BOX -->
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
              <h4 class="m-0">課程商品資料 <span style="font-size:large;">ID: <?PHP echo @$res['merch_id']; ?></span></h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                  <li class="breadcrumb-item">首頁</li>
                  <li class="breadcrumb-item">課程商品管理</li>
                  <li class="breadcrumb-item prevLink" alt="返回列表" title="返回列表">課程商品資料列表</li>
                <li class="breadcrumb-item active">課程商品資料</li>
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

              <!-- Profile Image $res['merch_defaultphoto'] -->
              <div class="card card-primary card-outline shadow">
                <div class="card-body box-profile">
                  <div class="text-center">
                    <div class="image_area">
                        <img id="defaultPreview" name="defaultPreview" class="img-responsive imgEDicon" width="100%" src="<?php
                        //$defaultPhoto = file_exists("../uploadFiles/md/".$subFolder."/mdimg_".$res['merch_id']."_".$res['merch_defaultphoto']."_thumb.jpg") ? "../uploadFiles/md/".$subFolder."/mdimg_".$res['merch_id']."_".$res['merch_defaultphoto']."_thumb.jpg" : "../imageFiles/nopic_400x300.jpg";
                        $defaultPhoto = "../uploadFiles/md/".$subFolder."/mdimg_".@$res['merch_id']."_".@$res['merch_defaultphoto']."_thumb.jpg";
                        if(file_exists($defaultPhoto)){
                          echo $defaultPhoto."?".time();
                        } else {
                          echo "../imageFiles/nopic_400x300.jpg";
                        }
                        ?>" alt="課程商品圖像" title="課程商品圖像"/>
                    </div>
                  </div>
                  <h3 class="profile-title text-center" style="margin-top:8px;"><?PHP echo $res['merch_title'] ?? "&nbsp;"; ?></h3>
                  <p class="profile-mdtype text-muted text-center"><?PHP echo $merchJS['mdtype'][$res['merch_mdtype'] ?? null] ?? "&nbsp;"; ?></p>
                  <p class="profile-bdtype text-muted text-center"><?PHP echo $merchJS['bdtype'][$res['merch_bdtype'] ?? null] ?? "&nbsp;"; ?></p>

                  <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                      <b>狀態</b> <a class="profile-status float-right<?PHP echo (($res['merch_status'] ?? 0) == 1) ? "" : " text-danger"; ?>"><?PHP echo $merchJS['status'][$res['merch_status'] ?? null] ?? "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>編輯日期</b> <a class="profile-updated float-right"><?PHP echo $res['merch_updated'] ?? "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>建立日期</b> <a class="float-right" style="color:black;"><b><?PHP echo $res['merch_published'] ?? "&nbsp;"; ?></b></a>
                    </li>
                  </ul>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->

              <div class="card card-primary card-outline shadow">
                <div class="card-body box-profile row fromWhere" onclick="window.location.replace('mem_profile.php?currID=<?php echo $res['mb_id'] ?? 0; ?>');" alt="進入登錄者資料頁" title="進入登錄者資料頁">
                  <div class="col-sm-5">
                    <div class="image_area text-left">
                        <label for="upload_image" style="padding-left:8px;">  
                          <img id="uploaded_image" name="uploaded_image" class="img-responsive profile-user-img img-fluid img-circle imgEDicon" style="width:160px;" src="<?php
                          if(!empty($mbArr) && !empty($res['mb_id']) && file_exists("../uploadFiles/mb/mbphoto_".$res['mb_id']."_.jpg")) {
                            echo "../uploadFiles/mb/mbphoto_".$res['mb_id']."_.jpg?".time();
                          } else echo "../imageFiles/nopic_user_200x200.png";
                          ?>" alt="會員頭像" title="會員頭像"/>
                        </label>
                    </div>
                  </div>
                  <div class="col-sm-7">
                    <p>登錄者：</p>
                    <hr>
                    <h4 class="profile-username"><?php echo $mbArr['mb_title'] ?? ''; ?></h4>
                    <p class="text-muted"><?php echo $mbJS['levels'][$mbArr['mb_level'] ?? null]['title'] ?? ''; ?></p>
                  </div>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
              <div class="card card-danger card-outline">
                <div class="card-header">
                  <h3 class="card-title">價格異動(萬元)</h3>
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
                  <canvas class="chart" id="line-chart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
              <!-- /.card -->
              </div>
              <!-- Visits -->
              <div class="card card-info card-outline">
                <div class="card-header">
                  <h3 class="card-title">造訪記錄</h3>
                  <div class="card-tools">
                    <?php if(!empty($mdID)){ ?>
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
                    <?PHP echo $res['merch_visits'] ?? '0'; ?>
                    </div>
                </div>
              </div>
              <!-- /Visits -->

              <!-- Rating -->
              <div class="card card-warning card-outline">
                <div class="card-header">
                  <h3 class="card-title">課程商品受評</h3>
                  <div class="card-tools">
                    <?php if(!empty($mdID)){ ?>
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
                    <div id="thisStars" style="margin-left:42px;color:goldenrod;">
                    <?PHP
                    $starText = Evs::stars($totAVE, true);
                    echo $starText;
                    ?>
                    </div>
                    <div id="thisRateAvg" style="font-size:60px;font-weight:bold;margin-top:-32px;">
                      <?PHP
                      $getAvg = explode('.',round($totAVE,2));
                      ?>
                      <span><?PHP echo $getAvg[0] ?? '0'; ?></span><span style="font-size:32px;font-weight:normal;">.<?PHP echo $getAvg[1] ?? '00'; ?></span>
                    </div>
                    <canvas id="radarChart"></canvas>
                </div>
                <div class="card-body">
                    <hr>
                    <div class="form-group row" style="margin-bottom:-12px;">
                      <div class="col-sm-4">獲評總分</div>
                      <div class="col-sm-8" id="thisRateTop" style="font-size:24px;font-weight:bold;color:goldenrod;"><?PHP echo $res['merch_ratetop'] ?? 0 ?></div>
                    </div>
                    <hr>
                    <div class="form-group row">
                      <div class="col-sm-4">總評次數</div>
                      <div class="col-sm-8" id="thisRateBottom" style="font-size:18px;font-weight:bold;color:#236FB7;"><?PHP echo $res['merch_ratebottom'] ?? 0; ?></div>
                    </div>
                </div>
                <div class="card-footer" style="padding:4px 0px 0px 8px;">
                  LSR平滑星級: <span class="my-stars" id="thisLSR">
                  <?php
                    if(!empty($mdID)){
                        $totSum = $res['merch_ratetop'] ?? 0;
                        $totCount = $res['merch_ratebottom'] ?? 0;
                        $views = $res['merch_visits'] ?? 0;
                        $hourPassed = _fn_hours_passed($res['merch_startdate'] ?? ($res['merch_published'] ?? date('Y-m-d H:i:s')));
                        $score = Evs::lsrFromAvg($totAVE, $totCount); 
                        $starText = Evs::stars($score, true);
                        $promoScore = Evs::tds($score, $totCount, $res['merch_visits'] ?? 0, $hourPassed);
                        echo $starText. ' '. $score;
                    } else {
                        echo "&nbsp;";
                    }
                  ?>
                  </span>
                </div>
                <div class="card-footer" style="padding:0px 0px 0px 8px;">
                  TDS熱度權重: <span style="font-weight:bold;font-size:1.5em;" id="thisTDS"><?php echo !empty($mdID) ? number_format((float)$promoScore, 5) :  "&nbsp;"; ?></span>
                </div>
              </div>
              <!-- /Rating -->

            </div>
            <!-- /.col -->
            <div class="col-md-9">
              <div class="card card-secondary">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#tabMenu1" data-index="1" data-toggle="tab" data-topbut="1">課程商品基本訊息</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu2" data-index="2" data-toggle="tab" data-topbut="1">展示圖像管理</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu3" data-index="3" data-toggle="tab" data-topbut="1">視訊鏈結管理</a></li>
                    <?php if(!empty($mdID)){ ?>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu4" data-index="4" data-toggle="tab" data-topbut="0">刊登&加價購紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu5" data-index="5" data-toggle="tab" data-topbut="0">定價記錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu6" data-index="6" data-toggle="tab" data-topbut="0">議價紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu7" data-index="7" data-toggle="tab" data-topbut="0">評價紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu8" data-index="8" data-toggle="tab" data-topbut="0">推廣紀錄</a></li>
                    <li class="nav-item"><a class="nav-link<?PHP if(empty($res['md_org']) || @$res['md_org']==0) { echo " invisible"; } ?>" href="#tabMenu9" data-index="9" id="tabMenu9" data-toggle="tab" data-topbut="0">成員列表</a></li>
                    <?php } ?>
                  </ul>
                </div>
                <!-- /.card-header -->
                <!-- ///////////////////////////////////////////////////////////////////////////////////// -->
                <!-- /////////////////////////////////// 功能分頁  //////////////////////////////////////// -->
                <!-- ///////////////////////////////////////////////////////////////////////////////////// -->
                <div class="card-body" style="border:1px solid #5F6E86;">
                  <div class="tab-content">
                    <!-- 第1分頁管理者資料修改 -->
                    <div class="tab-pane active" id="tabMenu1">
                      <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/md_act_func.php" method="post" data-func="func_md_infoUpdate">
                        <input type="hidden" class="form-control" id="mb_id" name="mb_id" value="<?PHP echo $res['mb_id'] ?? ''; ?>">
                        <div class="card-body">
                          <hr style="border: 1px solid #2C333E"><div class="hr-more">基本資料</div>
                          <div class="form-group row">
                            <label for="merch_sn" class="col-sm-2 col-form-label">案件編號</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="merch_sn" name="merch_sn" value="<?PHP echo $res['merch_sn'] ?? ''; ?>" placeholder="案件編號" readonly>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_title" class="col-sm-2 col-form-label">課程商品名稱</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="merch_title" name="merch_title" value="<?PHP echo $res['merch_title'] ?? ''; ?>" placeholder="課程商品名稱"<?PHP echo $res['merch_readonly'] ?? false ? ' readonly' : ''; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-2 col-form-label">上標</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="merch_superscript" name="merch_superscript" value="<?PHP echo $res['merch_superscript'] ?? ''; ?>" placeholder="上標"<?PHP echo $res['merch_readonly'] ?? false ? ' readonly' : ''; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-2 col-form-label">下標</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="merch_subtitle" name="merch_subtitle" value="<?PHP echo $res['merch_subtitle'] ?? ''; ?>" placeholder="下標"<?PHP echo $res['merch_readonly'] ?? false ? ' readonly' : ''; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_city" class="col-sm-2 col-form-label">城市區域</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_city" name="merch_city" data-placeholder="-請選擇所在城市-" data-select="city"<?PHP echo NdisAble; ?>>
                                <option value="">-請選擇所在城市-</option>
                                <?PHP
                                foreach($locJS as $key => $value){
                                  echo "<option value='".$value['value']."'";
                                  if(($res['merch_city'] ?? null) == $value['value']) { echo " selected"; }
                                  echo ">".$value['text']."</option>";
                                }
                                ?>
                              </select>
                            </div>
                            
                            <div class="col-sm-6">
                              <select class="form-control" id="merch_district" name="merch_district" data-placeholder="-請選擇行政區劃-" data-select="dist" dist-fromcity="merch_city" list-bc="1" list-circ="1" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <option value='0'>-請選擇行政劃區-</option>
                                <?PHP
                                if(!empty($res['merch_city'])) {
                                  foreach($locJS[$res['merch_city']]['district'] as $key => $value){
                                    echo "<option value='".$key."'";
                                    if(($res['merch_district'] ?? null) == $key) { echo " selected"; }
                                    echo ">".$value."</option>";
                                  }
                                }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_add" class="col-sm-2 col-form-label">住址</label>
                            <div class="col-sm-6">
                              <input type="text" class="form-control" id="merch_add" name="merch_add" value="<?PHP echo $res['merch_add'] ?? ''; ?>" placeholder="住址"<?PHP echo NreadOnly; ?>>
                            </div>
                            <div class="col-sm-4">
                              <div class="icheck-primary d-inline">
                                <input type="checkbox" id="merch_hideadd" name="merch_hideadd" value="1"<?php if (($res['merch_hideadd'] ?? 0) == 1) echo " checked"; ?><?php echo NdisAble; ?>>
                                <label for="merch_hideadd" class="font-weight-light">隱藏門牌</label>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_lng" class="col-sm-2 col-form-label">縱向座標</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">經度</span></div>
                                <input type="text" class="form-control notSubmit" id="merch_lng" name="merch_lng" value="<?php echo $res['lng'] ?? ""; ?>" placeholder="經度"<?php echo NreadOnly; ?>>
                                <div class="input-group-append"><span class="input-group-text">度</span></div>
                              </div>
                            </div>
                            <label for="merch_lat" class="col-sm-2 col-form-label">橫向座標</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">緯度</span></div>
                                <input type="text" class="form-control notSubmit" id="merch_lat" name="merch_lat" value="<?php echo $res['lat'] ?? ""; ?>" placeholder="緯度"<?php echo NreadOnly; ?>>
                                <div class="input-group-append"><span class="input-group-text">度</span></div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-2 col-form-label">所屬生活商圈</label>
                            <div class="col-sm-10">
                              <select class="form-control" id="scrc_id" name="scrc_id" data-placeholder="-請選擇生活商圈-" circ-fromcity="merch_city"<?PHP echo NdisAble; ?>>
                                <option value="">-請選擇生活商圈-</option>
                                <?PHP
                                  if(!empty($res['merch_district'])) {
                                    $sqlscrc="SELECT scrc_id,scrc_title,scrc_circletitle FROM socialcircle WHERE TRANSLATE(scrc_district,'[]',',,') LIKE '%,".$res['merch_district'].",%' AND scrc_status=? ORDER BY scrc_title ASC";
                                    $resscrc = $DB->query($sqlscrc,array(1));
                                    $i=0;
                                    while($i<count($resscrc)){
                                      echo "<option value='".$resscrc[$i]['scrc_id']."'";
                                      if ($res['scrc_id']==$resscrc[$i]['scrc_id'])  echo " selected";
                                      echo ">".$resscrc[$i]['scrc_title'];
                                      if(!empty($resscrc[$i]['scrc_circletitle'])) { echo "(".$resscrc[$i]['scrc_circletitle'].")"; }
                                      echo "</option>";
                                      $i++;
                                    }
                                  }
                                ?>
                              </select>
                            </div>
                          </div>

                          <div class="form-group row">
                            <label class="col-sm-2 col-form-label">所屬社區建案</label>
                            <div class="col-sm-10">
                              <select class="form-control" id="bc_id" name="bc_id" data-placeholder="請選擇社區建案" bc-fromcity="merch_city" data-select="bc" data-addnew="1"<?PHP echo NdisAble; ?>>
                                <option value="">請選擇社區建案</option>
                                <?PHP
                                  if(!empty($res['merch_district'])) {
                                    $sqlcomm="SELECT bc_id, bc_title FROM buildcase WHERE bc_district=? AND bc_status<=? ORDER BY bc_title ASC";
                                    $rescom = $DB->query($sqlcomm,array($res['merch_district'],5));
                                    $i=0;
                                    while($i<count($rescom)){
                                      echo "<option value='".$rescom[$i]['bc_id']."'";
                                      if ($res['bc_id']==$rescom[$i]['bc_id'])  echo " selected";
                                      echo ">".$rescom[$i]['bc_title']."</option>";
                                      $i++;
                                    }
                                  }
                                ?>
                                <option value="N">自填其他社區建案</option>
                              </select>
                            </div>
                          </div>
                          <div id="addnewcomm" name="addnewcomm" class="form-group row">
                            <label class="col-sm-2 col-form-label">自填社區建案</label>
                            <div class="col-sm-10"><input type="text" class="form-control" id="merch_addnewcomm" name="merch_addnewcomm" value="" placeholder="自行填寫未出現於表列中的社區建案名稱" addbc-fromcity="merch_city" data-select="bcaddnew" disabled="disabled"></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-10"><span style="font-size:0.85em;margin-top:-12px;position:absolute;color:#236FB7;">* 請詳細確認您需求的社區建案並未列於選單中，再行自行建立新的社區建案選單！</span></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-2 col-form-label">區域地點描述</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="merch_area" name="merch_area"<?PHP echo NdisAble; ?>><?PHP echo $res['merch_area'] ?? ''; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-2 col-form-label">課程商品特色描述</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="merch_des" name="merch_des" rows="6"<?PHP echo NdisAble; ?>><?PHP echo $res['merch_des'] ?? ''; ?></textarea>
                            </div>
                          </div>
                          <hr class="grouphr">
                          <div class="groupfield">建物資訊</div>

                          <div class="form-group row">
                            <label for="merch_finish" class="col-sm-2 col-form-label">完工日期</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="text" class="form-control" id="merch_finish" name="merch_finish" data-inputmask-alias="datetime" data-inputmask-inputformat="yyyy-mm-dd" value="<?php echo $res['merch_finish'] ?? ''; ?>" data-mask<?php echo NreadOnly; ?>>
                              </div>
                            </div>
                            <label for="merch_mdtype" class="col-sm-2 col-form-label">課程商品屬性</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="merch_mdtype" name="merch_mdtype"<?PHP echo NdisAble; ?>>
                                        <?php
                                            foreach($merchJS['mdtype'] as $key => $value){
                                                echo '<option value="'.$key.'"';
                                                if($key==""){ echo " disabled"; }
                                                if(($res['merch_mdtype'] ?? null) == $key) echo " selected";
                                                echo '>'.$value.'</option>';
                                            }
                                        ?>
                                </select>
                            </div>
                          </div>

                          <div class="form-group row">
                            <label for="merch_usage" class="col-sm-2 col-form-label">謄本用途登錄</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_usage" name="merch_usage" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchJS['usage'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_usage'] ?? null) == $key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            <label for="merch_bdtype" class="col-sm-2 col-form-label">建物種類登錄</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="merch_bdtype" name="merch_bdtype"<?PHP echo NdisAble; ?>>
                                        <?php
                                            foreach($merchJS['bdtype'] as $key => $value){
                                                echo '<option value="'.$key.'"';
                                                if($key==""){ echo " disabled"; }
                                                if(($res['merch_bdtype'] ?? null) == $key) echo " selected";
                                                echo '>'.$value.'</option>';
                                            }
                                        ?>
                                </select>
                            </div>
                          </div>
                          <div class="form-group row">
                          <label for="merch_usecate" class="col-sm-2 col-form-label">用途分類</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_usecate" name="merch_usecate"<?PHP echo NdisAble; ?>>
                              <?php
                                  //$arJS=!empty($res['merch_usecate'])?json_decode($res['merch_usecate']):array();
                                  foreach($merchJS['usecate'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      //if(in_array($key, $arJS)) echo " selected";
                                      if(($res['merch_usecate'] ?? null) == $key) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                              ?>
                              </select>
                            </div>
                            <label for="merch_bdcate" class="col-sm-2 col-form-label">型態分類</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="merch_bdcate" name="merch_bdcate"<?PHP echo NdisAble; ?>>
                                    <?php
                                        foreach($merchJS['bdcate'] as $key => $value){
                                            echo '<option value="'.$key.'"';
                                            if($key==""){ echo " disabled"; }
                                            if(($res['merch_bdcate'] ?? null) == $key) echo " selected";
                                            echo '>'.$value.'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                          </div>

                          <div class="form-group row">
                            <label for="merch_material" class="col-sm-2 col-form-label">結構建材</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_material" name="merch_material" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchJS['material'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_material'] ?? null) == $key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            <label for="merch_room" class="col-sm-2 col-form-label">結構格局</label>
                            <div class="col-sm-4 row">    
                                <div class="input-group" style="width:80px;">
                                  <input type="text" class="form-control" id="merch_room" name="merch_room" value="<?PHP echo $res['merch_room'] ?? ''; ?>" placeholder="房"<?PHP echo NreadOnly; ?>>
                                  <div class="input-group-append">
                                    <span class="input-group-text">房</span>
                                  </div>
                                </div>
                                <div class="input-group" style="width:80px;">
                                  <input type="text" class="form-control" id="merch_hall" name="merch_hall" value="<?PHP echo $res['merch_hall'] ?? ''; ?>" placeholder="廳"<?PHP echo NreadOnly; ?>>
                                  <div class="input-group-append">
                                    <span class="input-group-text">廳</span>
                                  </div>
                                </div>   
                                <div class="input-group" style="width:80px;">
                                  <input type="text" class="form-control" id="merch_bath" name="merch_bath" value="<?PHP echo $res['merch_bath'] ?? ''; ?>" placeholder="衛"<?PHP echo NreadOnly; ?>>
                                  <div class="input-group-append">
                                    <span class="input-group-text">衛</span>
                                  </div>
                                </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_onfloor" class="col-sm-2 col-form-label">所在樓層</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="merch_onfloor" name="merch_onfloor" value="<?PHP echo $res['merch_onfloor'] ?? ''; ?>" placeholder="所在樓層"<?PHP echo NreadOnly; ?>>
                            </div>
                            <label for="merch_totalfloor" class="col-sm-2 col-form-label">大樓總層數</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="merch_totalfloor" name="merch_totalfloor" value="<?PHP echo $res['merch_totalfloor'] ?? ''; ?>" placeholder="大樓總層數"<?PHP echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_bdfloor" class="col-sm-2 col-form-label">課程商品層數</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="merch_bdfloor" name="merch_bdfloor" value="<?PHP echo $res['merch_bdfloor'] ?? ''; ?>" placeholder="建物層數"<?PHP echo NreadOnly; ?>>
                            </div>
                            <label for="merch_orientation" class="col-sm-2 col-form-label">建物朝向</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_orientation" name="merch_orientation" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchJS['orientation'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_orientation'] ?? null) == $key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                          </div>


                          <div class="form-group row">
                            <label for="merch_landdist" class="col-sm-2 col-form-label">土地分區</label>
                            <div class="col-sm-10">
                                <select class="form-control" id="merch_landdist" name="merch_landdist"<?PHP echo NdisAble; ?>>
                                    <?php
                                        $i=0;
                                        foreach($merchJS['landdist'] as $key => $value){
                                            if($key=="c" || $key=="oc"){
                                              if($i > 0) echo '</optgroup>';
                                              echo '<optgroup label="'.$value.'">';
                                              //echo '<option value="'.$key.'" disabled>'.$value.'</option>';

                                            } else {
                                              echo '<option value="'.$key.'"';
                                              if(($res['merch_landdist'] ?? null) == $key) echo " selected";
                                              echo '>'.$value.'</option>';
                                            }
                                            if($i==count($merchJS['landdist'])-1){
                                              echo '</optgroup>';
                                            }
                                            $i++;
                                        }
                                    ?>
                                </select>
                            </div>
                          </div>
                          <hr class="grouphr">
                          <div class="groupfield">租售相關</div>
                          <div class="form-group row">
                            <label for="merch_rsoptions" class="col-sm-2 col-form-label">租售選項</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="merch_rsoptions" name="merch_rsoptions"<?PHP echo NdisAble; ?>>
                                    <?php
                                      foreach($merchJS['rsoptions'] as $key => $value){
                                          echo '<option value="'.$key.'"';
                                          if($key==""){ echo " disabled"; }
                                          if(($res['merch_rsoptions'] ?? null) == $key) echo " selected";
                                          echo '>'.$value.'</option>';
                                      }
                                    ?>
                                </select>
                            </div>
                            <label for="merch_tstype" class="col-sm-2 col-form-label">刊登類別</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="merch_tstype" name="merch_tstype"<?PHP echo NdisAble; ?>>
                                        <?php
                                            foreach($merchJS['tstype'] as $key => $value){
                                                echo '<option value="'.$key.'"';
                                                if($key==""){ echo " disabled"; }
                                                if(($res['merch_tstype'] ?? null) == $key) echo " selected";
                                                echo '>'.$value.'</option>';
                                            }
                                        ?>
                                </select>
                            </div>
                          </div>
                          <hr class="grouphr">
                          <div class="groupfield">租賃條件</div>
                          <div class="form-group row">
                            <label for="merch_minterm" class="col-sm-2 col-form-label">最短租期</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_minterm" name="merch_minterm"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchJS['minterm'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_minterm'] ?? null) == $key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            <label for="merch_deposit" class="col-sm-2 col-form-label">押租金</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="merch_deposit" name="merch_deposit" value="<?PHP echo $res['merch_deposit'] ?? ''; ?>" placeholder="押租金"<?PHP echo (isset($res['merch_rsoptions'])&&$res['merch_rsoptions']=='R') ? NreadOnly : " readonly"; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text">月</span>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_qualify" class="col-sm-2 col-form-label">身份要求</label>
                            <div class="col-sm-10 select2-info">
                              <select class="select2" id="merch_qualify" name="merch_qualify" multiple="multiple" data-placeholder="選擇身份要求" data-dropdown-css-class="select2-info" style="width: 100%;"<?PHP echo (isset($res['merch_rsoptions'])&&$res['merch_rsoptions']=='R') ? NdisAble : " disabled"; ?>>
                              <?php
                                  $quaJS=!empty($res['merch_qualify'])?json_decode($res['merch_qualify']):array();
                                  foreach($merchJS['qualify'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(in_array($key, $quaJS)) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                              ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_restrict" class="col-sm-2 col-form-label">約束限制</label>
                            <div class="col-sm-10 select2-info">
                              <select class="select2" id="merch_restrict" name="merch_restrict" multiple="multiple" data-placeholder="選擇約束限制" data-dropdown-css-class="select2-info" style="width: 100%;"<?PHP echo (isset($res['merch_rsoptions'])&&$res['merch_rsoptions']=='R') ? NdisAble : " disabled"; ?>>
                              <?php
                                  $resJS=!empty($res['merch_restrict'])?json_decode($res['merch_restrict']):array();
                                  foreach($merchJS['restrict'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(in_array($key, $resJS)) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                              ?>
                              </select>
                            </div>
                          </div>
                            <div class="form-group row">
                            <label for="merch_furniture" class="col-sm-2 col-form-label">隨屋配備</label>
                            <div class="col-sm-10 select2-purple">
                              <select class="select2" id="merch_furniture" name="merch_furniture" multiple="multiple" data-placeholder="選擇隨屋配備" data-dropdown-css-class="select2-purple" style="width: 100%;"<?PHP echo (isset($res['merch_rsoptions'])&&$res['merch_rsoptions']=='R') ? NdisAble : " disabled"; ?>>
                              <?php
                                  $furnJS=!empty($res['merch_furniture'])?json_decode($res['merch_furniture']):array();
                                  foreach($merchJS['furniture'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(in_array($key, $furnJS)) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                              ?>
                              </select>
                            </div>
                          </div>
                          <hr class="grouphr">
                          <div class="groupfield">面積相關</div>
                          <div class="form-group row">
                            <label for="merch_landarea" class="col-sm-2 col-form-label">土地面積</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="merch_landarea" name="merch_landarea" value="<?PHP echo number_format($res['merch_landarea'] ?? 0, 2, '.', ''); ?>" placeholder="土地面積"<?PHP echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">坪</span>
                                </div>
                              </div>
                            </div>
                            <label for="merch_certarea" class="col-sm-2 col-form-label">主建面積</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="merch_certarea" name="merch_certarea" value="<?PHP echo number_format($res['merch_certarea'] ?? 0, 2, '.', ''); ?>" placeholder="主建面積"<?PHP echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">坪</span>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_otherarea" class="col-sm-2 col-form-label">附建面積</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="merch_otherarea" name="merch_otherarea" value="<?PHP echo number_format($res['merch_otherarea'] ?? 0, 2, '.', ''); ?>" placeholder="附建面積"<?PHP echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">坪</span>
                                </div>
                              </div>
                            </div>
                            <label for="merch_amearea" class="col-sm-2 col-form-label">公設面積</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="merch_amearea" name="merch_amearea" value="<?PHP echo number_format($res['merch_amearea'] ?? 0, 2, '.', ''); ?>" placeholder="公設面積"<?PHP echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">坪</span>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_parkarea" class="col-sm-2 col-form-label">車位面積</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="merch_parkarea" name="merch_parkarea" value="<?PHP echo number_format($res['merch_parkarea'] ?? 0, 2, '.', ''); ?>" placeholder="車位面積"<?PHP echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">坪</span>
                                </div>
                              </div>
                            </div>
                            <label for="merch_landown" class="col-sm-2 col-form-label">土地持分</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_landown" name="merch_landown" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchJS['landown'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if(($res['merch_landown'] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_landarea" class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-10">
                              <span><i><b>請注意：</b>課程商品銷售公示總面積的定義為「主建物」+「附屬建物」+「公設」面積之總和，並不包含土地以及車位面積，請確實填寫以免計算錯誤。</i></span>
                            </div>
                          </div>

                          <hr class="grouphr">
                          <div class="groupfield">價格相關</div>
                          <div class="form-group row">
                            <label for="merch_listprice" class="col-sm-2 col-form-label">標示售(租)價</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="merch_listprice" name="merch_listprice" currprice="<?PHP echo $res['merch_listprice'] ?? ''; ?>" value="<?PHP echo $res['merch_listprice'] ?? ''; ?>" placeholder="標示價"<?PHP echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text">萬元</span>
                                </div>
                              </div>
                            </div>
                            <label for="merch_saleprice" class="col-sm-2 col-form-label">建物售(租)價</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="merch_saleprice" name="merch_saleprice" currprice="<?PHP echo $res['merch_saleprice'] ?? ''; ?>" value="<?PHP echo $res['merch_saleprice'] ?? ''; ?>" placeholder="建物售(租)價"<?PHP echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text">萬元</span>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="form-group row">
                            <label for="merch_landprice" class="col-sm-2 col-form-label">土地售(租)價</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="merch_landprice" name="merch_landprice" currprice="<?PHP echo $res['merch_landprice'] ?? ''; ?>" value="<?PHP echo $res['merch_landprice'] ?? ''; ?>" placeholder="土地價格"<?PHP echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text">萬元</span>
                                </div>
                              </div>
                            </div>
                            <label for="merch_parkprice" class="col-sm-2 col-form-label">車位售(租)價</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="merch_parkprice" name="merch_parkprice" currprice="<?PHP echo $res['merch_parkprice'] ?? ''; ?>" value="<?PHP echo $res['merch_parkprice'] ?? ''; ?>" placeholder="車位價格"<?PHP echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text">萬元</span>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="form-group row">
                            <label for="merch_paytype" class="col-sm-2 col-form-label">支付方式</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="merch_paytype" name="merch_paytype"<?PHP echo NdisAble; ?>>
                                    <?php
                                        foreach($merchJS['paytype'] as $key => $value){
                                            echo '<option value="'.$key.'"';
                                            if($key==""){ echo " disabled"; }
                                            if(($res['merch_paytype'] ?? null)==$key) echo " selected";
                                            echo '>'.$value.'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                          </div>


                          <hr class="grouphr">
                          <div class="groupfield">車位相關</div>
                          <?php
                          $pkJS = json_decode($res['merch_ownparking'] ?? '[["",""]]', true);
                          $ci = count($pkJS ?? []);
                          $i=0;
                          while($i<$ci){
                            $letter = chr(65+$i);
                          ?>
                          <div class="form-group row">
                            <label for="merch_parktitle" class="col-sm-2 col-form-label">車位 <?PHP echo $letter; ?></label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <div id="itemOption" name="itemOption" class="input-group-prepend">
                                  <span class="input-group-text<?php if($i==$ci-1) echo " addparking"; ?>"><?php echo ($i==$ci-1) ? "<i class='fas fa fa-plus-circle'></i>" : "&nbsp;&nbsp;&nbsp;"; ?></span>
                                  <span class="input-group-text delparking"><i class="fas fa fa-minus-circle"></i></span>
                                </div>
                                <input type="text" class="form-control" id="merch_parknum" name="merch_parknum" value="<?PHP echo $pkJS[$i][0] ?? ''; ?>" placeholder="車位數量"<?PHP echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">個</span>
                                </div>
                              </div>
                            </div>
                            <!-- <label for="merch_parktype" class="col-sm-2 col-form-label">車位<?PHP echo $letter; ?>型態</label> -->
                            <div class="col-sm-6">
                              <select class="form-control" id="merch_parktype" name="merch_parktype"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchJS['ptype'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($pkJS[$i][1] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                          </div>

                          <?php
                              $i++;
                            } 
                          ?>
                          <hr class="grouphr">
                          <div class="groupfield">生活機能</div>
                          <div class="form-group row">
                            <label for="merch_amenities" class="col-sm-2 col-form-label">週邊機能</label>
                            <div class="col-sm-10 select2-purple">
                              <select class="select2" id="merch_amenities" name="merch_amenities" multiple="multiple" data-placeholder="選擇週邊機能" data-dropdown-css-class="select2-purple" style="width: 100%;"<?PHP echo NdisAble; ?>>
                              <?php
                                  $likeJS=!empty($res['merch_amenities'])?json_decode($res['merch_amenities']):array();
                                  foreach($merchJS['amenities'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(in_array($key, $likeJS)) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                              ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_traffic" class="col-sm-2 col-form-label">周邊交通</label>
                            <div class="col-sm-10 select2-purple">
                              <select class="select2" id="merch_traffic" name="merch_traffic" multiple="multiple" data-placeholder="選擇周邊交通" data-dropdown-css-class="select2-purple" style="width: 100%;"<?PHP echo NdisAble; ?>>
                              <?php
                                  $dlikeJS=!empty($res['merch_traffic'])?json_decode($res['merch_traffic']):array();
                                  foreach($merchJS['traffic'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(in_array($key, $dlikeJS)) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                              ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_schools" class="col-sm-2 col-form-label">周邊學校</label>
                            <div class="col-sm-10 select2-purple">
                              <select class="select2" id="merch_schools" name="merch_schools" multiple="multiple" data-placeholder="選擇周邊學校" data-dropdown-css-class="select2-purple" style="width: 100%;"<?PHP echo NdisAble; ?>>
                              <?php
                                  $dlikeJS=!empty($res['merch_schools'])?json_decode($res['merch_schools']):array();
                                  foreach($merchJS['schools'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(in_array($key, $dlikeJS)) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                              ?>
                              </select>
                            </div>
                          </div>
                          <hr class="grouphr">
                          <div class="groupfield">課程商品管理</div>
                          <div class="form-group row">
                            <label for="merch_now" class="col-sm-2 col-form-label">使用現況</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_now" name="merch_now"<?PHP echo NdisAble; ?>>
                                    <?php
                                        foreach($merchJS['now'] as $key => $value){
                                            echo '<option value="'.$key.'"';
                                            if($key==""){ echo " disabled"; }
                                            if(($res['merch_now'] ?? null)==$key) echo " selected";
                                            echo '>'.$value.'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <label for="merch_janitor" class="col-sm-2 col-form-label">門衛管理</label>
                            <div class="col-sm-4">
                              <div class="icheck-primary d-inline">
                                <input type="checkbox" id="merch_janitor" name="merch_janitor" value="1"<?php if (($res['merch_janitor'] ?? 0) == 1) echo " checked"; ?><?php echo NdisAble; ?>>
                                <label for="merch_janitor" class="font-weight-light">配置門衛管理人員</label>
                              </div>
                            </div>
                          </div>

                          <div class="form-group row">
                            <label for="merch_pmfee" class="col-sm-2 col-form-label">管理費用</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="merch_pmfee" name="merch_pmfee" value="<?PHP echo $res['merch_pmfee'] ?? ''; ?>" placeholder="管理費用"<?PHP echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text">元/月</span>
                                </div>
                              </div>
                            </div>
                            <label for="merch_property" class="col-sm-2 col-form-label">物業管理公司</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="merch_property" name="merch_property" value="<?PHP echo $res['merch_property'] ?? ''; ?>" placeholder="物業管理公司"<?PHP echo NreadOnly; ?>>
                            </div>
                          </div>
                          

                          <div class="form-group row">
                          <label class="col-sm-2 col-form-label">特色精品</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_icon" name="merch_icon"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchJS['icon'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_icon'] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            <label class="col-sm-2 col-form-label">勿擾模式</label>
                            <div class="col-sm-4">
                              <div class="icheck-primary d-inline">
                                <input type="checkbox" id="merch_disturb" name="merch_disturb" value="1"<?php if (($res['merch_disturb'] ?? 0) == 1) echo " checked"; ?><?php echo NdisAble; ?>>
                                <label for="merch_disturb" class="font-weight-light">謝絕仲介</label>
                              </div>
                            </div>
                            <!--   merch_disturb : $('#merch_disturb').is(':checked') ? 1 : 0,

                            <label class="col-sm-2 col-form-label">仲介合作</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_disturb" name="merch_disturb"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchJS['disturb'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_disturb'] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            -->
                          </div>
                          <div class="form-group row">
                            <label for="merch_prstatus" class="col-sm-2 col-form-label">產權狀態</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="merch_prstatus" name="merch_prstatus"<?PHP echo NdisAble; ?>>
                                    <?php
                                        foreach($merchJS['prstatus'] as $key => $value){
                                            echo '<option value="'.$key.'"';
                                            if($key==""){ echo " disabled"; }
                                            if(($res['merch_prstatus'] ?? null)==$key) echo " selected";
                                            echo '>'.$value.'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <label for="merch_lnstatus" class="col-sm-2 col-form-label">貸款狀態</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="merch_lnstatus" name="merch_lnstatus"<?PHP echo NdisAble; ?>>
                                    <?php
                                        foreach($merchJS['lnstatus'] as $key => $value){
                                            echo '<option value="'.$key.'"';
                                            if($key==""){ echo " disabled"; }
                                            if(($res['merch_lnstatus'] ?? null)==$key) echo " selected";
                                            echo '>'.$value.'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_tsstatus" class="col-sm-2 col-form-label">交易狀態</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="merch_tsstatus" name="merch_tsstatus"<?PHP echo NdisAble; ?>>
                                    <?php
                                        foreach($merchJS['tsstatus'] as $key => $value){
                                            echo '<option value="'.$key.'"';
                                            if($key==""){ echo " disabled"; }
                                            if(($res['merch_tsstatus'] ?? null)==$key) echo " selected";
                                            echo '>'.$value.'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <label for="merch_status" class="col-sm-2 col-form-label">上架狀態</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="merch_status" name="merch_status"<?PHP echo NdisAble; ?>>
                                  <?php
                                      foreach($merchJS['status'] as $key => $value){
                                          echo '<option value="'.$key.'"';
                                          if($key==""){ echo " disabled"; }
                                          if(($res['merch_status'] ?? null)==$key) echo " selected";
                                          echo '>'.$value.'</option>';
                                      }
                                  ?>
                                </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_startdate" class="col-sm-2 col-form-label">上架日期</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="text" class="form-control" id="merch_startdate" name="merch_startdate" data-inputmask-alias="datetime" data-inputmask-inputformat="yyyy-mm-dd" value="<?php echo $res['merch_startdate'] ?? ''; ?>" data-mask<?php echo NreadOnly; ?>>
                              </div>
                            </div>
                            <label for="merch_enddate" class="col-sm-2 col-form-label">下架日期</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="text" class="form-control" id="merch_enddate" name="merch_enddate" data-inputmask-alias="datetime" data-inputmask-inputformat="yyyy-mm-dd" value="<?php echo $res['merch_enddate'] ?? ''; ?>" data-mask<?php echo NreadOnly; ?>>
                              </div>
                            </div>
                          </div>


                          
                          <!-- ////////////////////////// 加價購 ///////////////////////// 
                          <hr class="grouphr">
                          <div class="groupfield">加價購項目</div>
                          <div class="form-group row">
                            <label for="merch_onfloor" class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-10">
                            <div class="icheck-primary d-inline">
                                <input type="checkbox" id="merch_openday" name="merch_openday" value="1"<?php //if(!empty($res['merch_openday']) && intval($res['merch_openday']==1)) echo " checked"; ?><?php echo NdisAble; ?>>
                                <label for="merch_openday" class="font-weight-light">約看/駐點排程&nb sp;&nbsp;&nbsp;&nbsp;</label>
                              </div>
                              <div class="icheck-primary d-inline">
                                <input type="checkbox" id="merch_hotcase" name="merch_hotcase" value="1"<?php //if(!empty($res['merch_hotcase']) && intval($res['merch_hotcase']==1)) echo " checked"; ?><?php echo NdisAble; ?>>
                                <label for="merch_hotcase" class="font-weight-light">精選好案&nbsp;&nbsp;&nbsp;&nbsp;</label>
                              </div>
                              <div class="icheck-primary d-inline">
                                <input type="checkbox" id="merch_drawing" name="merch_drawing" value="1"<?php //if(!empty($res['merch_drawing']) && intval($res['merch_drawing']==1)) echo " checked"; ?><?php echo NdisAble; ?>>
                                <label for="merch_drawing" class="font-weight-light">格局圖繪製&nbsp;&nbsp;&nbsp;&nbsp;</label>
                              </div>
                              <div class="icheck-primary d-inline">
                                <input type="checkbox" id="merch_visual" name="merch_visual" value="1"<?php //if(!empty($res['merch_visual']) && intval($res['merch_visual']==1)) echo " checked"; ?><?php echo NdisAble; ?>>
                                <label for="merch_visual" class="font-weight-light">圖片設計拍攝</label>
                              </div>
                            </div>
                          </div>
                          --> 

                          <!-- ////////////////////////// 進階資料 ///////////////////////// -->
                          <hr style="border: 1px solid #5F6E86;margin-top:46px;"><div class="hr-more">進階資料</div>
                          <div class="form-group row">
                            <label for="merch_rearco" class="col-sm-2 col-form-label">前院(台)面積</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="merch_rearco" name="merch_rearco" value="<?PHP echo number_format($res['merch_rearco'] ?? 0, 2, '.', ''); ?>" placeholder="前陽台(庭院)面積"<?PHP echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">坪</span>
                                </div>
                              </div>
                            </div>
                            <label for="merch_backco" class="col-sm-2 col-form-label">後院(台)面積</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="merch_backco" name="merch_backco" value="<?PHP echo number_format($res['merch_backco'] ?? 0, 2, '.', ''); ?>" placeholder="後陽台(庭院)面積"<?PHP echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">坪</span>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_basement" class="col-sm-2 col-form-label">地下室層數</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="merch_basement" name="merch_basement" value="<?PHP echo $res['merch_basement'] ?? ''; ?>" placeholder="地下室層數"<?PHP echo NreadOnly; ?>>
                            </div>
                            <label for="merch_basearea" class="col-sm-2 col-form-label">地下室面積</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="merch_basearea" name="merch_basearea" value="<?PHP echo number_format($res['merch_basearea'] ?? 0, 2, '.', ''); ?>" placeholder="地下室面積"<?PHP echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">坪</span>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="form-group row">
                            <label for="merch_interior" class="col-sm-2 col-form-label">內牆材質</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_interior" name="merch_interior" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchiJS['interior'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if(($res['merch_interior'] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            <label for="merch_outside" class="col-sm-2 col-form-label">外牆材質</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_outside" name="merch_outside" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchiJS['outside'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_outside'] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_floor" class="col-sm-2 col-form-label">地板材質</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_floor" name="merch_floor" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchiJS['floor'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_floor'] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            <label for="merch_cistern" class="col-sm-2 col-form-label">儲水設施</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_cistern" name="merch_cistern" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchiJS['cistern'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_cistern'] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_gas" class="col-sm-2 col-form-label">天然氣設施</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_gas" name="merch_gas" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchiJS['boolean'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_gas'] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            <label for="merch_net" class="col-sm-2 col-form-label">網路設施</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_net" name="merch_net" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchiJS['net'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_net'] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_generator" class="col-sm-2 col-form-label">緊急供電</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_generator" name="merch_generator" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchiJS['generator'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_generator'] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            <label for="merch_pole" class="col-sm-2 col-form-label">避雷設施</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_pole" name="merch_pole" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchiJS['boolean'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_pole'] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_fire" class="col-sm-2 col-form-label">消防設施</label>
                            <div class="col-sm-10 select2-purple">
                              <select class="select2" id="merch_fire" name="merch_fire" multiple="multiple" data-placeholder="選擇消防設施" data-dropdown-css-class="select2-purple" style="width: 100%;"<?PHP echo NdisAble; ?>>
                              <?php
                                  $fireJS=!empty($res['merch_fire'])?json_decode($res['merch_fire']):array();
                                  foreach($merchiJS['fire'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(in_array($key, $fireJS)) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                              ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_prevent" class="col-sm-2 col-form-label">防災設備</label>
                            <div class="col-sm-10 select2-purple">
                              <select class="select2" id="merch_prevent" name="merch_prevent" multiple="multiple" data-placeholder="選擇防災設備" data-dropdown-css-class="select2-purple" style="width: 100%;"<?PHP echo NdisAble; ?>>
                              <?php
                                  $prevJS=!empty($res['merch_prevent'])?json_decode($res['merch_prevent']):array();
                                  foreach($merchiJS['prevent'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(in_array($key, $prevJS)) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                              ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_barrierfree" class="col-sm-2 col-form-label">無障礙設施</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_barrierfree" name="merch_barrierfree" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchiJS['boolean'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(intval($res['merch_barrierfree'] ?? 0)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            <label for="merch_monitor" class="col-sm-2 col-form-label">監控設備</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_monitor" name="merch_monitor" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchiJS['boolean'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_monitor'] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_charger" class="col-sm-2 col-form-label">充電柱</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_charger" name="merch_charger" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchiJS['boolean'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_charger'] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            <label class="col-sm-2 col-form-label">有線電視</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_cable" name="merch_cable" placeholder="有線電視" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchiJS['boolean'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_cable'] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_condition" class="col-sm-2 col-form-label">屋況</label>
                            <div class="col-sm-10">
                              <select class="select2" id="merch_condition" name="merch_condition" multiple="multiple" data-placeholder="選擇屋況" style="width: 100%;"<?PHP echo NdisAble; ?>>
                              <?php
                                  $condJS=!empty($res['merch_condition'])?json_decode($res['merch_condition']):array();
                                  foreach($merchiJS['condition'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(in_array($key, $condJS)) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                              ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_rentparking" class="col-sm-2 col-form-label">承租車位</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="merch_rentparking" name="merch_rentparking" style="width: 100%;"<?PHP echo NdisAble; ?>>
                                <?php
                                    foreach($merchiJS['boolean'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(($res['merch_rentparking'] ?? null)==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            <label for="merch_rental" class="col-sm-2 col-form-label">車位租金</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="merch_rental" name="merch_rental" value="<?PHP echo @$res['merch_rental']; ?>" placeholder="車位租金"<?PHP echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text">元/月</span>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="form-group row">
                            <label for="merch_facilities" class="col-sm-2 col-form-label">公共設施</label>
                            <div class="col-sm-10 select2-purple">
                              <select class="select2" id="merch_facilities" name="merch_facilities" multiple="multiple" data-placeholder="選擇公共設施" data-dropdown-css-class="select2-purple" style="width: 100%;"<?PHP echo NdisAble; ?>>
                              <?php
                                  $faciJS=!empty($res['merch_facilities'])?json_decode($res['merch_facilities']):array();
                                  foreach($merchiJS['facilities'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(in_array($key, $faciJS)) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                              ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_landform" class="col-sm-2 col-form-label">特殊地形地貌</label>
                            <div class="col-sm-10 select2-purple">
                              <select class="select2" id="merch_landform" name="merch_landform" multiple="multiple" data-placeholder="選擇特殊地形地貌" data-dropdown-css-class="select2-purple" style="width: 100%;"<?PHP echo NdisAble; ?>>
                              <?php
                                  $landfJS=!empty($res['merch_landform'])?json_decode($res['merch_landform']):array();
                                  foreach($merchiJS['landform'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(in_array($key, $landfJS)) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                              ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_environment" class="col-sm-2 col-form-label">特殊自然環境</label>
                            <div class="col-sm-10 select2-purple">
                              <select class="select2" id="merch_environment" name="merch_environment" multiple="multiple" data-placeholder="選擇特殊自然環境" data-dropdown-css-class="select2-purple" style="width: 100%;"<?PHP echo NdisAble; ?>>
                              <?php
                                  $enviJS=!empty($res['merch_environment'])?json_decode($res['merch_environment']):array();
                                  foreach($merchiJS['environment'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(in_array($key, $enviJS)) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                              ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-2 col-form-label">其他費用描述</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="merch_otherfee" name="merch_otherfee" rows="4"<?PHP echo NdisAble; ?>><?PHP echo $res['merch_otherfee'] ?? ''; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-2 col-form-label">合約條件簡述</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="merch_contract" name="merch_contract" rows="6"<?PHP echo NdisAble; ?>><?PHP echo $res['merch_contract'] ?? ''; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-2 col-form-label">課程商品關鍵字</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="merch_seo" name="merch_seo"<?PHP echo NdisAble; ?>><?PHP echo $res['merch_seo'] ?? ''; ?></textarea>
                            </div>
                          </div>


                        </div>
                        <!-- /.card-body -->
                      </form>
                    </div>
                    <!-- /第1分頁管理者資料修改 -->
                    <!-- 第2分頁展示圖像管理 -->
                    <div class="tab-pane" id="tabMenu2" style=”background-color:#5F6E86;“>
                      <h5>課程商品圖像管理</h5>
                      <div style="font-size:0.85em;line-height:1.4em;"><i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;一般會員上架的課程商品提供 17 個圖檔位，其中前12個圖檔位為指定圖檔，13～16圖檔位為自選圖檔，主要為附加環境圖檔，或是產品相關文件，如建築平面圖、水電配置圖、租約範本、社區規約、產權文件、稅務文件、申辦事項表格...等，第17圖檔位指定為建物格局圖。上傳的圖檔大小限制在 30Kb 至 300Kb 以內寬高比為 16:9 的 JPG 檔案，超過尺寸系統將自動按比例裁切，建議圖檔寬高 992*558px (含)以上以及 1920*1080px (含)以下，檔案大小越接近上限則越清晰，重要部分請勿接近圖像外側邊緣。</div>
                      <form id="form_tabMenu2" name="form_tabMenu2" role="form" action="../funcFiles/file_act_func.php" method="post" enctype="multipart/form-data" data-formmode="photoUpload" data-func="func_md_imgUpload">
                        <div class="card-body" style="text-align:center;">
                          <hr style="border: 1px solid #2C333E"><div class="hr-more">展示圖像列表</div>
                          <input type="hidden" id="currIdx" name="currIdx" data-orival="<?php echo $res['merch_defaultphoto'] ?? 0; ?>" value="<?php echo intval($res['merch_defaultphoto'] ?? 0); ?>">
                            <!--Photo List -->
                              <!-- Our File Inputs -->
                              <?php
                              $totalUpNum = count($imgArrTitle);
                              for($i=0;$i<$totalUpNum;$i++){
                                $imgOK=false;
                                $filename = "mdimg_".($res['merch_id'] ?? '')."_".$i."_";
                                $filePath = '../uploadFiles/md/'.$subFolder.'/'.$filename;
                                //$thumbPath = '../uploadFiles/md/'.$subFolder.'/'.$imgUpArray['upPath'][$i].'_thumb.jpg';
                                if(file_exists($filePath.'.jpg')){
                                    $imgOK = true;
                                }
                              ?>
                              <div class="area-upload-notext">
                                <div class="wrap-custom-file">
                                  <input type="file" name="imgUpload_<?php echo $i; ?>" id="imgUpID_<?php echo $i; ?>" accept=".jpg,.jpeg" />
                                  <label for="imgUpID_<?php echo $i; ?>" fileext="jpg"<?php echo ($imgOK==true) ? ' class="file-ok" style="background-image:url(\''.$filePath.'thumb.jpg?'.time().'\')"' : ' style="background-image:url(\'../imageFiles/nopic_240x180.jpg\');background-size: cover;background-position: center;"'; ?>>
                                    <input type="hidden" name="imgCheck_<?php echo $i; ?>" id="imgCheck_<?php echo $i; ?>" value="<?php echo ($imgOK==true) ? microtime(true) : ''; ?>" />
                                    <input type="radio" name="defIdx" id="defIdx" style="<?php echo ($imgOK==true) ? "display:block;" : "display:none;"; ?>" value="<?php echo $i; ?>" title="設為預設主圖" alt="設為預設主圖"<?php echo intval(@$res['merch_defaultphoto'])==$i ? " checked": ''; ?>>
                                    <?php if ($imgOK==true) { ?>
                                      <span>
                                        <i class="fas fa-window-close funcClose" onClick="clearUpload(this,'<?php echo $i; ?>','<?php echo $filePath;//不加副檔名 ?>','photo');return false;"></i>
                                        <div><?php echo $imgArrTitle[$i]; ?></div>
                                        <i class="fas fa-external-link-square-alt funcView" onClick="imagePopView('<?php echo $i; ?>','<?php echo $filePath.'.jpg?'.time(); ?>','<?php echo $imgArrTitle[$i]; ?>');return false;"></i>
                                      </span>
                                    <?php } else { ?>
                                      <span><?php echo $imgArrTitle[$i]; ?></span>
                                    <?php } ?>
                                    <i class="fa fa-plus funcAdd"></i>
                                  </label>
                                </div>
                                <!--
                                <div class="wrap-custom-text">
                                  <input type="text" id="imgTitle_<?php echo $i; ?>" name="imgTitle_<?php echo $i; ?>" value="<?php if ($imgOK==true) echo $imgArrTitle[$i]; ?>" placeholder="標題"<?php echo ($imgOK==true) ? '' :' disabled="disabled"'; ?>>
                                  <textarea name="imgDes_<?php echo $i; ?>" id="imgDes_<?php echo $i; ?>" rows="3" placeholder="描述"<?php echo ($imgOK==true) ? '' :' disabled="disabled"'; ?>><?php //if ($imgOK==true) echo $imgUpArray['upDes'][$i]; ?></textarea>
                                </div>
                                -->
                              </div>
                              <?php } ?>
                              <div class="clear-inline"></div>
                            <!-- /Photo List -->
                        </div>
                      </form>
                    </div>
                    <!-- /第2分頁展示圖像管理 -->
                    <!-- 第3分頁視訊鏈結管理 -->
                    <div class="tab-pane" id="tabMenu3">
                      <h5>視訊鏈結管理</h5>
                      <div style="font-size:0.85em;line-height:1.4em;"><i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;一般會員上架的課程商品提供4個視訊位，主要為產品相關文件，如課程商品介紹、驗屋過程、會議紀錄、線上解說...等，採用串流平台鏈結方式，上傳者必須先行將視訊檔案上傳串流平台，例如YouTube、Vimeo...等，取得影像鏈接網址後填入。</div>
                      <form id="form_tabMenu3" name="form_tabMenu3" role="form" action="../funcFiles/md_act_func.php" method="post" data-formmode="linkUpdate" data-func="func_md_linkUpdate">
                        <div class="card-body" style="text-align:center;">
                          <hr style="border: 1px solid #2C333E"><div class="hr-more">視訊鏈結列表</div>
                          <?php
                          for($i=0;$i<4;$i++){
                            $linkOK=false;
                            if(!empty($linkJS[$i]['lPath'])){
                              $linkOK = true;
                            }
                          ?>
                          <div class="link-upload">
                            <div class="wrap-custom-link">
                              <iframe id="linkiFrame_<?php echo $i; ?>" name="linkiFrame_<?php echo $i; ?>" width="500" height="100%" style="background-image: url('../imageFiles/uploadBG_youtube.jpg')" src="<?php if ($linkOK==true) echo $linkJS[$i]['lPath']; ?>"></iframe>
                            </div>
                            <textarea id="linkURL_<?php echo $i; ?>" name="linkURL_<?php echo $i; ?>" rows="2" placeholder="影像連結"><?php if ($linkOK==true) echo $linkJS[$i]['lPath']; ?></textarea>
                            <input type="text" id="linkTitle_<?php echo $i; ?>" name="linkTitle_<?php echo $i; ?>" value="<?php if ($linkOK==true) echo $linkJS[$i]['lTitle']; ?>" placeholder="標題">
                            <textarea id="linkDes_<?php echo $i; ?>" name="linkDes_<?php echo $i; ?>" rows="3" placeholder="描述"><?php if ($linkOK==true) echo $linkJS[$i]['lDes']; ?></textarea>
                          </div>
                          <?php } ?>


                          <hr>
                          <div class="clear-inline"></div>
                        </div>
                      <!-- </div> -->
                      </form>
                    </div>
                    <!-- /第3分頁視訊鏈結管理 -->
                     <?php if(!empty($mdID)){ ?>
                    <!-- 第4分頁刊登&加價購紀錄 -->
                    <div class="tab-pane" id="tabMenu4">
                      <div class="card card-info">
                        <!-- card-body -->
                        <div class="card-body">
                          <table id="dataTable_opdList" class="table table-bordered table-striped table-hover" data-butClass="gray" data-pagemax="25"  data-dirto="" data-search>
                            <thead>
                              <tr>
                                <th>狀態</th>
                                <th>申購日期</th>
                                <th>種類</th>
                                <th>刊登起始</th>
                                <th>刊登終止</th>
                                <th>模式</th>
                                <th>已/待扣點數</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <?PHP
                                $opares = $DB->query("SELECT * FROM merch_oppdata WHERE merch_id = ?",array($mdID));
                                $opai = 0; //以$i為$nameList這個陣列的索引值
                                while($opai < count($opares)){
                                    $oppTitle = $DB->single("SELECT opp_title FROM merch_oppurchase WHERE opp_id = ?",array($opares[$opai]['opp_id']));
                                    $uriData = [
                                      'opdID'          => $opares[$opai]['opd_id'],
                                      'oppID'          => $opares[$opai]['opp_id'],
                                      'oppTitle'       => $oppTitle, // 修正你的變數大小寫
                                      'mbID'           => $opares[$opai]['mb_id'],
                                      'mdID'           => $opares[$opai]['merch_id'],
                                      'opdKeycode'     => $opares[$opai]['opd_keycode'],
                                      'opdType'        => $opares[$opai]['opd_type'],
                                      'opdApplyDate'   => $opares[$opai]['opd_applydate'],
                                      'opdStartDate'   => $opares[$opai]['opd_startdate'], // 這裡有空格
                                      'opdEndDate'     => $opares[$opai]['opd_enddate'],   // 這裡有空格
                                      'opdMode'        => $opares[$opai]['opd_mode'],
                                      'opdCost'        => $opares[$opai]['opd_cost'],
                                      'opdPaid'        => $opares[$opai]['opd_paid'],
                                      'opdDays'        => $opares[$opai]['opd_days'],
                                      'opdNotify'      => $opares[$opai]['opd_notify'],
                                      'opdStatus'      => $opares[$opai]['opd_status']
                                  ];

                                  // 2. 自動生成安全、已編碼的 Query String
                                  $getURI = http_build_query($uriData);
                                  //$getURI = "opdID={$opares[$opai]['opd_id']}&oppID={$opares[$opai]['opp_id']}&oppTitle={$oppTitle}&mbID={$opares[$opai]['mb_id']}&mdID={$opares[$opai]['merch_id']}&opdKeycode={$opares[$opai]['opd_keycode']}&opdType={$opares[$opai]['opd_type']}&opdApplyDate={$opares[$opai]['opd_applydate']}&opdStartDate={$opares[$opai]['opd_startdate']}&opdEndDate={$opares[$opai]['opd_enddate']}&opdMode={$opares[$opai]['opd_mode']}&opdCost={$opares[$opai]['opd_cost']}&opdPaid={$opares[$opai]['opd_paid']}&opdDays={$opares[$opai]['opd_days']}&opdNotify={$opares[$opai]['opd_notify']}&opdStatus={$opares[$opai]['opd_status']}";
                                ?>
                                  <tr id="opdlist_<?php echo $opares[$opai]['opd_id']; ?>">
                                    <td><span class="right badge<?php echo $geJS['actionbadge'][$opares[$opai]['opd_status']] ?? ''; ?>"><?PHP echo $merchJS['opdstatus'][$opares[$opai]['opd_status']] ?? '&nbsp;'; ?></span></td>
                                    <td><?PHP echo !empty($opares[$opai]['opd_applydate']) ? substr($opares[$opai]['opd_applydate'], 0,10) : '&nbsp;'; ?></td>
                                    <td><?PHP echo $merchJS['opptype'][$opares[$opai]['opd_type']] ?? '&nbsp;'; ?></td>
                                    <td><?PHP echo !empty($opares[$opai]['opd_startdate']) ? $opares[$opai]['opd_startdate'] : '&nbsp;'; ?></td>
                                    <td><?PHP echo !empty($opares[$opai]['opd_enddate']) ? $opares[$opai]['opd_enddate'] : '&nbsp;'; ?></td>
                                    <td><?PHP echo $merchJS['oppmode'][$opares[$opai]['opd_mode']] ?? '&nbsp;'; ?></td>
                                    <td><?PHP echo $opares[$opai]['opd_cost'] ?? '&nbsp;'; ?></td>
                                    <td>
                                      <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#opdList_modal" data-opdid="<?PHP echo $opares[$opai]['opd_id'] ?? ''; ?>" data-uri="<?PHP echo $getURI; ?>"><i class="fa-solid fa-pen-to-square"></i> 編輯</button>
                                      <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#paidList_modal" data-paidid="<?PHP echo $opares[$opai]['opd_id'] ?? ''; ?>" data-uri="">支付記錄</button>
                                    </td>
                                  </tr>
                              <?PHP 
                                  $opai++;
                                  } 
                                ?>
                            </tbody>
                            <tfoot>
                              <tr>
                                <th>狀態</th>
                                <th>申購日期</th>
                                <th>種類</th>
                                <th>刊登起始</th>
                                <th>刊登終止</th>
                                <th>模式</th>
                                <th>已/待扣點數</th>
                                <th></th>
                              </tr>
                            </tfoot>
                          </table>
                        </div>
                        <!-- /.card-body -->
                      </div>

                    </div>
                    <!-- /第4分頁刊登&加價購紀錄 -->
                    <!-- 第5分頁定價記錄 -->
                    <div class="tab-pane" id="tabMenu5">
                      <div class="card card-info">
                        <!-- card-body -->
                        <div class="card-body">
                          <table id="dataTable_mpList" class="table table-bordered table-striped table-hover" data-butClass="gray" data-pagemax="25"  data-dirto="" data-search>
                            <thead>
                              <tr>
                                <th>日期</th>
                                <th>標示售(租)價</th>
                                <th>建物售(租)價</th>
                                <th>土地售(租)價</th>
                                <th>車位售(租)價</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?PHP
                                $prr = $DB->query("SELECT * FROM merch_prices WHERE merch_id=?",array($mdID));
                                $i = 0; //以$i為$nameList這個陣列的索引值
                                while($i<count($prr)){ 
                                  ?>
                                  <tr>
                                    <td><?PHP echo $prr[$i]['mdpr_create'] ?? '&nbsp;'; ?></td>
                                    <td><?PHP echo $prr[$i]['mdpr_listprice'] ?? '&nbsp;'; ?></td>
                                    <td><?PHP echo $prr[$i]['mdpr_saleprice'] ?? '&nbsp;'; ?></td>
                                    <td><?PHP echo $prr[$i]['mdpr_landprice'] ?? '&nbsp;'; ?></td>
                                    <td><?PHP echo $prr[$i]['mdpr_parkprice'] ?? '&nbsp;'; ?></td>
                                  </tr>
                              <?PHP 
                                  $i++;
                                  } 
                                ?>
                            </tbody>
                            <tfoot>
                              <tr>
                                <th>日期</th>
                                <th>標示售(租)價</th>
                                <th>建物售(租)價</th>
                                <th>土地售(租)價</th>
                                <th>車位售(租)價</th>
                              </tr>
                            </tfoot>
                          </table>
                        </div>
                        <!-- /.card-body -->
                      </div>
                    </div>
                    <!-- /第5分頁定價記錄 -->
                    <!-- 第6分頁議價紀錄 -->
                    <div class="tab-pane" id="tabMenu6">

                    </div>
                    <!-- /第6分頁議價紀錄 -->
                    <!-- 第7分頁評價紀錄 -->
                    <div class="tab-pane" id="tabMenu7">
                      <div class="card card-info">
                        <!-- card-body -->
                        <div class="card-body">
                          <table id="dataTable_ratingList" class="table table-bordered table-striped table-hover" data-butClass="gray" data-pagemax="25"  data-dirto="" data-search>
                            <thead>
                              <tr>
                                <th>日期</th>
                                <th>來自</th>
                                <th><?php echo $idForJS['rating']['1']?:" &nbsp"; ?></th>
                                <th><?php echo $idForJS['rating']['2']?:" &nbsp"; ?></th>
                                <th><?php echo $idForJS['rating']['3']?:" &nbsp"; ?></th>
                                <th><?php echo $idForJS['rating']['4']?:" &nbsp"; ?></th>
                                <th><?php echo $idForJS['rating']['5']?:" &nbsp"; ?></th>
                                <th>評論</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?PHP
                                $sql="SELECT * FROM fsl_rating WHERE rat_forid=? AND rat_for=?";
                                $sr = $DB->query($sql,array($mdID,$idForItem));
                                $i = 0; //以$i為$nameList這個陣列的索引值
                                while($i<count($sr)){ 
                                  ?>
                                  <tr>
                                    <td><?PHP echo $sr[$i]['rat_datetime'] ?? '&nbsp;'; ?></td>
                                    <td><?PHP echo $sr[$i]['mb_id'] ?? '&nbsp;'; ?></td>
                                    <td><?PHP echo $sr[$i]['rat_s1'] ?? '&nbsp;'; ?></td>
                                    <td><?PHP echo $sr[$i]['rat_s2'] ?? '&nbsp;'; ?></td>
                                    <td><?PHP echo $sr[$i]['rat_s3'] ?? '&nbsp;'; ?></td>
                                    <td><?PHP echo $sr[$i]['rat_s4'] ?? '&nbsp;'; ?></td>
                                    <td><?PHP echo $sr[$i]['rat_s5'] ?? '&nbsp;'; ?></td>
                                    <td>
                                    <?PHP if(!empty($sr[$i]['rat_review'])) { ?>
                                      <button type="button" class="btn btn-info btn-block btn-sm" title="<?PHP echo $sr[$i]['rat_review'] ?? ''; ?>" alt="<?PHP echo $sr[$i]['rat_review'] ?? ''; ?>"><i class="fa fa-comments"></i> </button>
                                    <?PHP } ?>
                                    </td>
                                  </tr>
                              <?PHP 
                                  $i++;
                                  } 
                                ?>
                            </tbody>
                            <tfoot>
                              <tr>
                                <th>總平均</th>
                                <th></th>
                                <!-- <th></th> -->
                                <th><?PHP echo $S1; ?></th>
                                <th><?PHP echo $S2; ?></th>
                                <th><?PHP echo $S3; ?></th>
                                <th><?PHP echo $S4; ?></th>
                                <th><?PHP echo $S5; ?></th>
                                <th style="font-size:1.2em;font-weight:bold;"><?PHP echo $totAVE; ?></th>
                              </tr>
                            </tfoot>
                          </table>
                        </div>
                        <!-- /.card-body -->
                      </div>
                    </div>
                    <!-- /第7分頁評價紀錄 -->
                    <!-- 第8分頁推廣記錄列表 -->
                    <div class="tab-pane" id="tabMenu8">
                        <div class="card-body">

                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /第8分頁推廣記錄列表 -->
                    <!-- 第9分頁成員列表 -->
                    <div class="tab-pane<?PHP if (empty($res['md_org'])) { echo " invisible"; } ?>" id="tabMenu9">
                      <div class="card card-primary" style="border:1px solid #17a2b8;">
                        <div class="card-header">
                          <h3 class="card-title">成員列表</h3>
                        </div>
                      </div>
                    </div>
                    <!-- /第9分頁成員列表 tabMenu9-->
                     <?php } ?>
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
        <!-- -------------------- 裁切頭像彈出視窗 -------------------- -->
        <?php //include('include_avatarmodal.php'); ?>
        <!-- -------------------- /裁切頭像彈出視窗 -------------------- -->
        <!-- -------------------- 編輯加價購項目內容彈出視窗 -------------------- -->
        <div id="opdList_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="background:rgba(0,0,0,0.75);">
          <div class="modal-dialog modal-dialog-scrollable modal-lg" style="width:50%;">
            <div class="modal-content">
              <!-- Header -->
              <div class="modal-header bg-secondary">
                <h5 class="modal-title">編輯加價購項目內容</h5>
                <button type="button" id="opdModalCloseTop" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <!-- /Header -->
              <!-- Model Body -->
              <div class="modal-body">
                <h6 class="modal-des"></h6>
                <form id="form_opdList_modal" name="form_opdList_modal" data-opdid="0" action="../funcFiles/md_act_func.php" method="post" data-formmode="ruleupdate" data-func="func_md_opdUpdate">
                  <div class="modal-container"></div>
                </form>
              </div>
              <!-- /Model Body -->
              <!-- Footer -->
              <div class="modal-footer justify-content-between">
                <button type="button" id="modal_delBut" class="btn btn-danger" style="visibility:hidden;">刪除</button>
                <button type="button" id="modal_upBut" class="btn btn-primary">編輯</button>
              </div>
              <!-- /Footer -->
            </div>
          </div>
          <!-- /.modal-dialog -->
        </div>
      <!-- -------------------- /編輯加價購項目內容彈出視窗 -------------------- -->
        
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
-->
<!-- DataTables3 & Plugins -->
<script src="../pluginFiles/datatables3/datatables.min.js"></script>
<!--
<script src="../pluginFiles/datatables/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../pluginFiles/datatables/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
-->
<!-- Select2 -->
<script src="../pluginFiles/select2/js/select2.full.min.js"></script>
<!-- InputMask 日期輸入元件JS -->
<script src="../pluginFiles/moment/moment.min.js"></script>
<script src="../pluginFiles/inputmask/jquery.inputmask.min.js"></script>
<!-- ChartJS -->
<script src="../pluginFiles/chart.js/Chart.min.js"></script>
<!-- jquery-validation -->
<script src="../pluginFiles/jquery-validation/jquery.validate.min.js"></script>
<script src="../pluginFiles/jquery-validation/localization/messages_zh_TW.min.js"></script>
<script src="../pluginFiles/jquery-validation/additional-methods.min.js"></script>
<!-- Dropzone JS 
<script src="../pluginFiles/dropzone/dropzone.js"></script> -->
<!-- Cropper JS
<script src="../pluginFiles/cropper/cropper.js"></script> -->
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js<?php echo '?t='.time(); ?>"></script>
<script src="../scriptFiles/page.basic.js<?php echo '?t='.time(); ?>"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
<script src="../scriptFiles/fileupload.js<?php echo '?t='.time(); ?>"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>
<!-- ------------------------ /Scripts Link ------------------------ -->
<script type="text/javascript">
////////////////////////////////////////////////////////////////
// ---------------- JAVASCRIPTS 全域調用參數設置 --------------- //
////////////////////////////////////////////////////////////////
  var prARR = <?PHP echo json_encode($prARR, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
  var mdobj = <?php echo json_encode($merchJS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
  var geobj = <?php echo json_encode($geJS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
////////////////////////////////////////////////////////////////
// -------------- JAVASCRIPTS FUNCTIONs 全域調用 -------------- //
// -------------- 可接收include檔案要求後自定義動作 -------------- //
////////////////////////////////////////////////////////////////
    function doReDirect(){
      window.history.back();
    }
    // 當「儲存」按鈕按下時的動作
    function doButSave(){
      var frm = $('#form_'+$('.tab-pane.active').prop("id"));
      var accE = frm.data('func');
      if(accE=='func_md_infoUpdate'){
        ajaxPOST(frm);
      }else if(accE=='func_md_imgUpload'){
        filePOST(frm);
      }else if(accE=='func_md_linkUpdate'){
        linkPOST(frm);
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
          url: '../funcFiles/md_act_func.php',
          data: {
            'arrDatA': {}, 
            'formAcce':'form_delete',
            'token': getTokenVal(), 
            'mdID':'<?PHP echo $mdID; ?>'
          },
          dataType: 'json',
          async: false,
        success: function (data) {
            if(data['RTN']==1){
              window.location.replace("merch_list.php?msg=5&doneid=<?PHP echo $mdID; ?>");
            } else {
              console.log(data);
              window.parent.toastr['error']('刪除檔案失敗，請重新操作或聯絡管理人員！！');
            }
          },
          error: function (data) {
            console.log(data);
            window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
          }
      });

    }


///////////////////////////// AJAX 表單處理 ////////////////////////////////
//資料新增更新
    function ajaxPOST(frm){
        var url = frm.prop('action');
        var typE = frm.prop('method');
        var frm = $('#form_'+$('.tab-pane.active').prop("id"));
        var accE = frm.data('func');
        var datA = {};
        var datB = {};
        if(0=='<?php echo $mdID; ?>') { accE = 'func_md_addnew'; }
        else {//當為編輯狀態時
            var inspricing = false;
            if(
              $('#merch_listprice').val()!=$('#merch_listprice').attr('currprice') ||
              $('#merch_saleprice').val()!=$('#merch_saleprice').attr('currprice') ||
              $('#merch_landprice').val()!=$('#merch_landprice').attr('currprice') ||
              $('#merch_parkprice').val()!=$('#merch_parkprice').attr('currprice')
            ){
              if(!confirm('相關價格內容已變更，系統將自動儲存舊有價格紀錄，以提供價格異動趨勢服務。價格資訊一經系統記錄，便無法隨意更改，請小心審慎檢視所設定之價格是否正確！\r\r確定送出這個價格紀錄？!\r\r')){
                return false;
              }
              inspricing = true;
            }

            var parkingJS = '';
            $( 'input[id="merch_parknum"]' ).each(function() {
              var pnum = $(this).val();
              var ptype = $(this).parent().parent().next().find('#merch_parktype').val();
              if(pnum==''|| pnum=='0' || ptype==''){ return; }
              parkingJS += '[' + pnum + ',' + ptype + '],';
            });
            if(parkingJS != ''){
              parkingJS = '['+parkingJS.substring(0,parkingJS.length-1)+']';
            }
            if($('#merch_rsoptions').val()=='R'){
              var minterm = $('#merch_minterm').val();
              var qualify = arrayTOstring($('#merch_qualify').val());
              var restrict = arrayTOstring($('#merch_restrict').val());
            } else {
              var minterm = '';
              var qualify = '';
              var restrict = '';
            }

            datA = {
              mb_id :             $('#mb_id').val(),
              merch_startdate :   $('#merch_startdate').val(),
              merch_enddate :     $('#merch_enddate').val(),
              merch_title :       $('#merch_title').val(),
              merch_superscript : $('#merch_superscript').val(),
              merch_subtitle :    $('#merch_subtitle').val(),
              merch_city :        $('#merch_city').val(),
              merch_district :    $('#merch_district').val(),
              merch_add :         $('#merch_add').val(),
              merch_hideadd :     $('#merch_hideadd').is(':checked') ? 1 : 0,
              //merch_lat :         $('#merch_lat').val(),
              //merch_lng :         $('#merch_lng').val(),
              bc_id :             $('#bc_id').val()=='N'?'0':$('#bc_id').val(),
              scrc_id :           $('#scrc_id').val(),
              //merch_addnewcomm :  $('#merch_addnewcomm').val(),
              merch_area :        $('#merch_area').val(),
              merch_des :         $('#merch_des').val(),
              merch_rsoptions :   $('#merch_rsoptions').val(),
              merch_finish :      $('#merch_finish').val(),
              merch_usage :       $('#merch_usage').val(),
              merch_bdtype :      $('#merch_bdtype').val(),
              merch_tstype :      $('#merch_tstype').val(),
              merch_mdtype :      $('#merch_mdtype').val(),
              merch_bdcate :      $('#merch_bdcate').val(),
              merch_usecate :     $('#merch_usecate').val(),
              merch_material :    $('#merch_material').val(),
              merch_room :        $('#merch_room').val(),
              merch_hall :        $('#merch_hall').val(),
              merch_bath :        $('#merch_bath').val(),
              merch_onfloor :     $('#merch_onfloor').val(),
              merch_totalfloor :  $('#merch_totalfloor').val(),
              merch_bdfloor :     $('#merch_bdfloor').val(),
              merch_orientation : $('#merch_orientation').val(),
              merch_landdist :    $('#merch_landdist').val(),
              merch_minterm :     minterm,
              merch_deposit :     $('#merch_deposit').val(),
              merch_qualify :     qualify,
              merch_restrict :    restrict,
              merch_furniture :   arrayTOstring($('#merch_furniture').val()),
              merch_landarea :    $('#merch_landarea').val(),
              merch_certarea :    $('#merch_certarea').val(),
              merch_otherarea :   $('#merch_otherarea').val(),
              merch_amearea :     $('#merch_amearea').val(),
              merch_parkarea :    $('#merch_parkarea').val(),
              merch_landown :     $('#merch_landown').val(),
              merch_paytype :     $('#merch_paytype').val(),
              merch_prstatus :    $('#merch_prstatus').val(),
              merch_lnstatus :    $('#merch_lnstatus').val(),
              merch_disturb :     $('#merch_disturb').is(':checked') ? 1 : 0,
              merch_listprice :   $('#merch_listprice').val(),
              merch_landprice :   $('#merch_landprice').val(),
              merch_saleprice :   $('#merch_saleprice').val(),
              merch_parkprice :   $('#merch_parkprice').val(),
              merch_ownparking :  parkingJS,
              merch_amenities :   arrayTOstring($('#merch_amenities').val()),
              merch_traffic :     arrayTOstring($('#merch_traffic').val()),
              merch_schools :     arrayTOstring($('#merch_schools').val()),
              merch_janitor:      $('#merch_janitor').is(':checked') ? 1 : 0,
              merch_pmfee :       $('#merch_pmfee').val(),
              merch_property :    $('#merch_property').val(),
              merch_icon :        $('#merch_icon').val(),
              merch_now :         $('#merch_now').val(),
              merch_tsstatus :    $('#merch_tsstatus').val(),
              merch_status :      $('#merch_status').val(),
              //merch_openday:      $('#merch_openday').is(':checked') ? 1 : 0,//????
              //merch_hotcase:      $('#merch_hotcase').is(':checked') ? 1 : 0,
              //merch_drawing:      $('#merch_drawing').is(':checked') ? 1 : 0,
              //merch_visual:       $('#merch_visual').is(':checked') ? 1 : 0
              //merch_expiry:       '',
              //merch_closedate:    '',
              //merch_opptotal:     ''
            };
            datB = {
              //merch_areaunit :    $('#merch_areaunit').val(),
              //merch_pubratio :    $('#merch_pubratio').val(),
              merch_rearco :      $('#merch_rearco').val(),
              merch_backco :      $('#merch_backco').val(),
              merch_basement :    $('#merch_basement').val(),
              merch_basearea :    $('#merch_basearea').val(),
              merch_cable :       $('#merch_cable').val(),
              merch_interior :    $('#merch_interior').val(),
              merch_outside :     $('#merch_outside').val(),
              merch_floor :       $('#merch_floor').val(),
              merch_cistern :     $('#merch_cistern').val(),
              merch_gas :         $('#merch_gas').val(),
              merch_net :         $('#merch_net').val(),
              merch_generator :   $('#merch_generator').val(),
              merch_pole :        $('#merch_pole').val(),
              merch_fire :        arrayTOstring($('#merch_fire').val()),
              merch_prevent :     arrayTOstring($('#merch_prevent').val()),
              merch_barrierfree : $('#merch_barrierfree').val(),
              merch_monitor :     $('#merch_monitor').val(),
              merch_charger :     $('#merch_charger').val(),
              merch_condition :   arrayTOstring($('#merch_condition').val()),
              //merch_psell :       $('#merch_psell').val(),
              merch_rentparking : $('#merch_rentparking').val(),
              merch_rental :      $('#merch_rental').val(),
              //merch_ptype :       $('#merch_ptype').val(),
              merch_facilities :  arrayTOstring($('#merch_facilities').val()),
              merch_landform :    arrayTOstring($('#merch_landform').val()),
              merch_environment : arrayTOstring($('#merch_environment').val()),
              merch_otherfee :    $('#merch_otherfee').val(),
              merch_contract :    $('#merch_contract').val(),
              merch_seo :         $('#merch_seo').val(),
            };
            datC = {
              merch_listprice :   $('#merch_listprice').attr('currprice'),
              merch_saleprice :   $('#merch_saleprice').attr('currprice'),
              merch_landprice :   $('#merch_landprice').attr('currprice'),
              merch_parkprice :   $('#merch_parkprice').attr('currprice')
            };
            if(inspricing==false){ datC = []; }
            //console.log(datB);return false;
          }
        $.ajax({
            type: typE,
            url: url,
            data: {
              'arrDatA': datA, 
              'arrDatB': datB,
              'arrDatC': datC,
              'formAcce':accE,
              'merch_lat': $('#merch_lat').val(),
              'merch_lng': $('#merch_lng').val(),
              'token': getTokenVal(), 
              'mdID':'<?PHP echo $mdID; ?>'
            },
            dataType: 'json',//data format  
            async: false, 
          success: function (data) {
              console.log(data);
              doSerialize();
              //ORGForm = CHGform.serialize();
              //window.parent.$('#topBut_save').removeClass('active');
              window.parent.toastr[data['msgType']](data['msgStr']); // PopUp 視窗
            },
            error: function (data) {
              console.log(data);
              window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！'); // PopUp 視窗
            }
        });
    }
    //檔案文件上傳/更新
    function filePOST(frm){
        var url = frm.prop('action');
        var typE = frm.prop('method');
        var frm = $('#form_'+$('.tab-pane.active').prop("id"));
        var accE = frm.data('func');
        var defIdx = $("#defIdx:checked").val();
        var currIdx = $('#currIdx').val();
        //var defChange = 0;
        var formData = new FormData();
            formData.append("formAcce",accE);
            formData.append("mdID",'<?PHP echo $mdID; ?>');
            formData.append("token",getTokenVal());
        //----------------------------
        if(defIdx!=currIdx){
          formData.append("defIdx",defIdx);
          //formData.append("defChange",1);
        }
        for (var i=0;i<16;i++) {
          var file_data = $('#imgUpID_'+i).prop('files')[0];
          if(file_data){
            formData.append("imgUp["+i+"]",file_data);
          }
        }
        $.ajax({
              type: typE,
              url: url,
              data: formData,
              dataType: 'json',
              contentType: false,
              cache: false,
              processData:false,
            async: false,
            success: function (data) {
              window.parent.toastr[data['msgType']](data['msgStr']);
            },
              error: function (data) {
                console.log(data);
                window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！'); 
              }
          });
    }
    //檔案文件上傳/更新 [{"lPath":"https://www.youtube.com/embed/Q9TSiv__paM?list=RDX5olOpmD-ak","lTitle":"這是抬頭測試","lDes":"這是描述測試"},{"lPath":"https://youtu.be/bYSXB3_e1aQ?list=RDbYSXB3_e1aQ","lTitle":"八月的記憶ＭＶ","lDes":"鹹豬肉樂團-八月的記憶ＭＶ"},{"lPath":"","lTitle":"","lDes":""},{"lPath":"","lTitle":"","lDes":""}]
    function linkPOST(frm){
        var url = frm.prop('action');
        var typE = frm.prop('method');
        var frm = $('#form_'+$('.tab-pane.active').prop("id"));
        var accE = frm.data('func');
        var datA = '';
        //----------------------------
        var link_string = '';
        for (var i=0;i<4;i++) {//linkURL_ linkTitle_ linkDes_
          link_string += '{"lPath":"'+$('#linkURL_'+i).val()+'",';
          link_string += '"lTitle":"'+$('#linkTitle_'+i).val()+'",';
          link_string += '"lDes":"'+$('#linkDes_'+i).val()+'"},';
        }
        link_string = link_string.slice(0, -1);
        link_json = '['+link_string+']';
        if(link_json != '[]'){
          datA = link_json;
        } else {
          datA = '';
        }
        //alert(accE);return;
        $.ajax({
              type: typE,
              url: url,
              data: {
              'arrDatA': datA, 
              'formAcce':accE,
              'token': getTokenVal(), 
              'mdID':'<?PHP echo $mdID; ?>'
              },
              dataType: 'json',
            async: false,
            success: function (data) {
              //console.log(data);
              window.parent.toastr[data['msgType']](data['msgStr']);
            },
              error: function (data) {
                console.log(data);
                window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！'); 
              }
          });
    }

/////////////////////// AJAX 返回觸發行為 ////////////////////////////
    function ajaxRTN(datA,acCE,rE){
      window.parent.toastr[data['msgType']](datA['msgStr']); // PopUp 視窗
      if(acCE=='form_tabMenu1'){
          if (datA['RTN']==1) { 
            /*
            var newStaT = $('#merch_status option:selected').text();
            var newSta = $('#merch_status').val();
            $('.profile-status').html(newStaT);
            if (newSta==0){ $('.profile-status').addClass( "text-danger" ); }
            else { $('.profile-status').removeClass( "text-danger" ); }
            $('.profile-title').html($('#merch_title').val());
            $('.profile-mdtype').html($('#merch_mdtype option:selected').text());
            $('.profile-bdtype').html($('#merch_bdtype option:selected').text());
            $('.profile-updated').html(datA['upTime']);
            */
            //window.location.replace("merch_info.php?currID="+data['newID']);
            var daTa = { 'mdID': '<?PHP echo $mdID; ?>','tabIDX': '1' };
            postData('merch_info.php',daTa);
          } else {
            $('#form_tabMenu1')[0].reset();
          }
          window.parent.$('#topBut_save').removeClass('active');
        }
        else if(acCE=='form_tabMenu2') {
          if (datA['RTN']==1) {
            //重置JS操作陣列（或刷新頁面）
            var daTa = { 'mdID': '<?PHP echo $mdID; ?>','tabIDX': '2' };
            postData('merch_info.php',daTa);
          }
          else {
            //$('#form_tabMenu2')[0].reset();
          }
          window.parent.$('#topBut_save').removeClass('active');
        }
        else if(acCE=='form_tabMenu3') {
          if (datA['RTN']==1) {
            //重置JS操作陣列（或刷新頁面）
            var daTa = { 'mdID': '<?PHP echo $mdID; ?>','tabIDX': '3' };
            postData('merch_info.php',daTa);
          }
          else {
            $('#form_tabMenu3')[0].reset();
          }
          window.parent.$('#topBut_save').removeClass('active');
        }
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
    /////////////////////////////////////////////////////////////////////////
    //Initialize Select2 Elements
    $('.select2').select2();
    $('#datemask2').inputmask('yyyy-mm-dd', { 'placeholder': 'yyyy-mm-dd' })
    //Money Euro
    $('[data-mask]').inputmask()
    ////////////////////////////////////////////////////////////
   //
    var mplistTB = datatableCreate('dataTable_mpList');
    var ratingListTB = datatableCreate('dataTable_ratingList');
    var opdListTB = datatableCreate('dataTable_opdList');
    // --------------- 當改變租售選項時 ------------------
    $('#merch_rsoptions').on('change', function(){
      var rsopVal = $(this).val();
      if(rsopVal=='R'){//當選擇租屋時
        $('#merch_minterm').removeAttr("readonly");
        $('#merch_deposit').removeAttr("readonly");
        $('#merch_qualify').removeAttr("disabled");
        $('#merch_restrict').removeAttr("disabled");
        $('#merch_furniture').removeAttr("disabled");
      } else {
        $('#merch_minterm').prop('readonly', 'readonly').blur();
        $('#merch_deposit').prop('readonly', 'readonly').blur();
        $('#merch_qualify').prop('disabled', 'disabled').blur();
        $('#merch_restrict').prop('disabled', 'disabled').blur();
        $('#merch_furniture').prop('disabled', 'disabled').blur();
      }
    });
    // ------------------------- 評價五角圖 ------------------------------- //
    var ctx = document.getElementById( "radarChart" );
    const itemArr = '<?php echo $ratingItem; ?>'.split(',');
    const dataArr = '<?php echo $ratingData; ?>'.split(',');
    const labels = itemArr.map((item, i) => [item, Number(dataArr[i])]);
    ctx.height = 280;
    var myChart = new Chart( ctx, {
        type: 'radar',
        data: {
            //labels: '<?php echo $ratingItem; ?>'.split(',').map(item => item.split('|')),
            labels: labels,
            datasets: [
                          {
                            label: "課程商品受評",
                            data: dataArr,
                            borderColor: "rgba(0, 123, 255, 0.6)",
                            borderWidth: "1",
                            backgroundColor: "rgba(0, 123, 255, 0.4)"
                          }
                      ]
              },
          options: {
                  legend: {
                      display:false,
                      position: 'top'
                  },
                  scale: {
                      pointLabels: {
                          fontSize: 12,
                          fontColor: 'black',
                          fontStyle: 'bold'
                      },
                      gridLines: {
                          color: 'lightgray'
                      },
                      ticks: {
                          beginAtZero: true,
                          max: 5,
                          min: 0,
                          stepSize: 1
                      }
                  }
              },
              scaleSteps: 5,
              scaleStepWidth: 1,
              scaleStartValue: 0
    });

  // 價格異動 Line Chart
  var salesGraphChartCanvas = $('#line-chart').get(0).getContext('2d');
  var salesGraphChartData = {
    labels: prARR['date'],
    datasets: [
      {
        label: 'ListPrice:',
        fill: true,
        borderWidth: 2,
        lineTension: 0,
        spanGaps: true,
        borderColor: '#C35050',
        pointRadius: 5,
        pointHoverRadius: 7,
        pointColor: '#C35050',
        pointBackgroundColor: '#C35050',
        data: prARR['price']
      }
    ]
  }
  var salesGraphChartOptions = {
    maintainAspectRatio: false,
    responsive: true,
    legend: {
      display: false
    },
    scales: {
      xAxes: [{
        ticks: {
          fontColor: '#2C333E'
        },
        gridLines: {
          display: false,
          color: '#5F6E86',
          drawBorder: false
        }
      }],
      yAxes: [{
        ticks: {
          stepSize: 100,
          fontColor: '#5F6E86'
        },
        gridLines: {
          display: true,
          color: '#5F6E86',
          drawBorder: false
        }
      }]
    }
  }
  // This will get the first returned node in the jQuery collection.
  // eslint-disable-next-line no-unused-vars
  var salesGraphChart = new Chart(salesGraphChartCanvas, { // lgtm[js/unused-local-variable]
    type: 'line',
    data: salesGraphChartData,
    options: salesGraphChartOptions
  });
  ////---------- 停車位資料管理 -------------------------------------------
  $('#form_tabMenu1').on('click', 'span.addparking', function() {
    var numparking = $('label[for="merch_parktitle"]').length;
    if(numparking>10){
      alert('已達車位數量型態筆數上限，請刪除非必要的資料再行操作！！');
      return false;
    }
    var lastParking = $('label[for="merch_parktitle"]').eq(numparking-1).parent();
    var htmlappend = '<div class="form-group row">';
        htmlappend += '<label for="merch_parktitle" class="col-sm-2 col-form-label">車位 ' + getAlphabet(numparking) + '</label>';
        htmlappend += '<div class="col-sm-4">';
        htmlappend += '<div class="input-group">';
        htmlappend += '<div id="itemOption" name="itemOption" class="input-group-prepend">';
        htmlappend += '<span class="input-group-text addparking"><i class="fas fa fa-plus-circle"></i></span>';
        htmlappend += '<span class="input-group-text delparking"><i class="fas fa fa-minus-circle"></i></span>';
        htmlappend += '</div>';
        htmlappend += '<input type="text" class="form-control" id="merch_parknum" name="merch_parknum" value="" placeholder="車位數量">';
        htmlappend += '<div class="input-group-append">';
        htmlappend += '<span class="input-group-text areaUnit">個</span>';
        htmlappend += '</div>';
        htmlappend += '</div>';
        htmlappend += '</div>';
        htmlappend += '<div class="col-sm-6">';
        htmlappend += '<select class="form-control" id="merch_parktype" name="merch_parktype">';
        htmlappend += '<option value="">'+mdobj['ptype']['']+'</option>';
        $.each(mdobj['ptype'],function(index,data){
          if (index == '') { return; } 
          htmlappend += '<option value="'+index+'">'+data+'</option>';
        });
        htmlappend += '</select>';
        htmlappend += '</div>';
        htmlappend += '</div>';
        $(this).removeClass('addparking').html('&nbsp;&nbsp;&nbsp;');
        lastParking.after(htmlappend);
        window.parent.$('#topBut_save').addClass('active');
  });

  $('#form_tabMenu1').on('click', 'span.delparking', function() {
    var label = $( 'label[for="merch_parktitle"]' );
    var currElm = $(this).parent().parent().parent().parent();
    if(label.length == 1){
      currElm.find('#merch_parknum').val('');
      currElm.find('#merch_parktype').val('');
      return false;
    }
    var resetLabel = false;
    if($(this).prev().hasClass('addparking')){
      var currLast = currElm.prev().find('#itemOption').find('span').eq(0);
      currLast.addClass('addparking').html('<i class="fas fa fa-plus-circle"></i>');
    } else {
      resetLabel = true;
    }
    currElm.remove();
    if(resetLabel){
      var i = 0;
      $( 'label[for="merch_parktitle"]' ).each(function() {
        $(this).html('車位 ' + getAlphabet(i));
        i++;
      });    
    }
    window.parent.$('#topBut_save').addClass('active');
  });
  // 內頁右上方小導航返回列表頁
  $('li.prevLink').on('click', function(){
    window.location.replace(getCurrentURI('','merch_list.php'));
  });
  //
  $('#dataTable_opdList').on('click',"button.btn-info",function(e){   
  //$(".modal-container tr[id^='rulelist_']").click(function(e){
    var currID=$(this).data("opdid");
    var currURI=$(this).data("uri");
    $('#opdList_modal').show().addClass('show');
    //$('#opdList_modal .modal-title').html('編輯加價購項目內容');
    $('#opdList_modal #modal_upBut').html('更新');
    $('#opdList_modal #modal_delBut').css("visibility", "visible");
    $('#form_opdList_modal').data('opdid',currID);
    $('#opdList_modal .modal-container').load('include_opdinfo.php?'+currURI);
  });

  /////////////////////////////////////////////////////////////////////////
  //-- 加價購紀錄內容更新操作 modal_upBut modal_delBut
  $('#modal_upBut,#modal_delBut').click(function(e){
    var actID = $('#form_opdList_modal').data('opdid');
    var actIDname = $(this).attr('id');
    var formObj = {
            //opd_id: $('#opd_id').val(),
            opd_keycode: $('#opd_keycode').val(),
            //opd_type: $('#opd_type').val(),
            opd_applydate: $('#opd_applydate').val(),
            opd_days: parseInt($('#opd_days').val()),
            opd_startdate: $('#opd_startdate').val(),
            opd_enddate: $('#opd_enddate').val(),
            //opd_mode: $('#opd_mode').val(),
            //opd_cost: $('#opd_cost').val(),
            opd_paid: $('#opd_paid').val(),
            opd_notify: $('#opd_notify').val(),
            opd_status: actIDname == 'modal_delBut' ? 8 : $('#opd_status').val()
    };
    $.ajax({
            type: 'post',
            url: '../funcFiles/md_act_func.php',
            data: {
              'arrDatA': formObj,
              'opdID': actID,
              'formAcce':'func_opd_infoUpdate',
              'token': getTokenVal()
            },
            dataType: 'json',
            //async: false, 
            beforeSend:function(){
              if(actIDname == 'modal_delBut'){
                var comF = '這個動作將刪除此份加價購項目且無法復原。\r\n確定進行更新操作？！';
              } else {
                var comF = '這個動作將更新此份加價購項目的內容，有可能導致相關的自動操作錯誤，請再三確認這個項目與對應課程商品的有效刊登時間關聯。\r\n確定進行更新操作？！';
              }
              if(!confirm(comF)){return false;}
            },
            success: function (data) {
              //$('#opdList_modal').removeClass('show').hide();
              $('#opdList_modal').modal('hide');
              console.log(data);
              if(data['RTN']==1){
                  window.parent.toastr['success'](data['msgStr']);
                  //var getURI = "opdID="+actID+"&oppID="+$('#opp_id').html()+"&oppTitle="+$('#opp_title').val()+"&mbID="+$('#mb_id').val()+"&mdID="+$('#merch_id').val()+"&opdKeycode="+$('#opd_keycode').val()+"&opdType="+$('#opd_type').val()+"&opdApplyDate="+datetimeToTimestamp(formObj.opd_applydate)+"&opdStartDate="+datetimeToTimestamp(formObj.opd_startdate)+"&opdEndDate="+datetimeToTimestamp(formObj.opd_enddate)+"&opdMode="+$('#opd_mode').val()+"&opdCost="+$('#opd_cost').val()+"&opdPaid="+formObj.opd_paid+"&opdDays="+formObj.opd_days+"&opdNotify="+formObj.opd_notify+"&opdStatus="+formObj.opd_status;
                  //var getURI = "opdID="+actID+"&oppID="+$('#opp_id').html()+"&oppTitle="+$('#opp_title').val()+"&mbID="+$('#mb_id').val()+"&mdID="+$('#merch_id').val()+"&opdKeycode="+$('#opd_keycode').val()+"&opdType="+$('#opd_type').val()+"&opdApplyDate="+formObj.opd_applydate+"&opdStartDate="+formObj.opd_startdate+"&opdEndDate="+formObj.opd_enddate+"&opdMode="+$('#opd_mode').val()+"&opdCost="+$('#opd_cost').val()+"&opdPaid="+formObj.opd_paid+"&opdDays="+formObj.opd_days+"&opdNotify="+formObj.opd_notify+"&opdStatus="+formObj.opd_status;
                  var uriParams = new URLSearchParams({
                      opdID:        actID,
                      oppID:        $('#opp_id').html(),
                      oppTitle:     $('#opp_title').val(), // 如果標題有中文字或特殊符號，這行會自動安全編碼
                      mbID:         $('#mb_id').val(),
                      mdID:         $('#merch_id').val(),
                      opdKeycode:   $('#opd_keycode').val(),
                      opdType:      $('#opd_type').val(),
                      opdApplyDate: formObj.opd_applydate,
                      opdStartDate: formObj.opd_startdate, // 日期時間之間的「空格」會被自動轉為 %20
                      opdEndDate:   formObj.opd_enddate,   // 絕對不會再破壞 HTML 結構
                      opdMode:      $('#opd_mode').val(),
                      opdCost:      $('#opd_cost').val(),
                      opdPaid:      formObj.opd_paid,
                      opdDays:      formObj.opd_days,
                      opdNotify:    formObj.opd_notify,
                      opdStatus:    formObj.opd_status
                  });
                  // 2. 轉成標準編碼後的字串
                  var getURI = uriParams.toString();           
                  //-- 更改當前頁面的相關數值
                  var currTR = $('#dataTable_opdList tbody').find('tr[id="opdlist_'+actID+'"]');
                  var currTD = currTR.find('td');
                  currTR.find('button.btn-info').data('uri',getURI);
                  currTD.eq(0).find('span').removeClass('badge-success').removeClass('badge-primary').removeClass('badge-info').removeClass('badge-warning').removeClass('badge-danger').addClass(geobj['actionbadge'][formObj.opd_status]);
                  currTD.eq(0).find('span').html(mdobj['opdstatus'][formObj.opd_status]);
                  currTD.eq(1).html(formObj.opd_applydate.substring(0,10));
                  currTD.eq(3).html(formObj.opd_startdate.substring(0,10));
                  currTD.eq(4).html(formObj.opd_enddate.substring(0,10));
              }
            },
            error: function (data) {
              console.log(data);
            }
        });
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
      foridVal: parseInt('<?php echo $mdID; ?>'),//這個值即爲 $mdID，搭配 forVal 值便是指 forVal 代表的資料表主鍵ID爲 foridVal 的記錄
      passHour: parseFloat('<?php echo $hourPassed ?? 0; ?>'),//
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
                        : newTotalAvg.toFixed(2);
                    const [newRateAvgInt, newRateAvgDec] = newRateAvg.split('.');
                    const starsHTML = _js_fn_getStars(newRateAvg, true);
                    const starsEMOJI = _js_fn_getStars(newLSR);
                    $('#thisRateAvg > span:first').html(newRateAvgInt);
                    $('#thisRateAvg > span:eq(1):last').html('.' + newRateAvgDec);
                    $('#thisStars').html(starsHTML);
                    $('#thisLSR').html(starsEMOJI + ' ' + newLSR);
                    $('#thisTDS').html(newTDS);

                    //當有新的數據時，同時改變圖表的數據、圖形形狀，以及圖表的標題，此功能僅在五組星級評分模式下使用
                    if(data?.newData){
                      const newData = data['newData'].split(',');
                      const newLabel = itemArr.map((item, i) => [item, Number(newData[i])]);
                      myChart.data.labels = newLabel;
                      myChart.data.datasets[0].data = newData.map(Number);
                      myChart.update();
                    }

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
  //////////////////////////////////////////////////////////////////

});



</script>
</body>
</html>
