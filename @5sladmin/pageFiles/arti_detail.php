<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_ARTI');
define('PAGE_ITEM','ITEM_ARTI');
define('bkRedir',1);
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
require_once('../classFiles/eRanking.class.php');
//
$artiID=currID;
//
$res = DB->row("SELECT * FROM articles WHERE arti_id=?",array($artiID));
//DB->closeConnection();
//代表圖像設定
if(!empty($artiID)&&file_exists("../uploadFiles/ar/arphoto_".$artiID."_thumb.jpg")){
  $cropImgDisplay = 'show';
  $mainimgpath = '../uploadFiles/ar/arphoto_'.$artiID.'_thumb.jpg?'.time();
  $mainimgchk = 'arphoto_'.$artiID.'_.jpg';
} else {
  $cropImgDisplay = 'hide';
  $mainimgpath = '../uploadFiles/imageFiles/nopic_400x300.jpg';
  $mainimgchk = "";
}

//功能陣列建立
// Get the contents of the JSON file
$artiJS = _fn_getCachedJsonData("../jsonFiles/articles.json");
$geJS = _fn_getCachedJsonData("../jsonFiles/general.json");
$mbJS = _fn_getCachedJsonData("../jsonFiles/member.json");
foreach($mbJS['cotype'] as $key=>$Val){
    $coTypeJS[$key]=$Val['type'];
    foreach($Val['expert'] as $keyT=>$ValT){
      $expertJS[$keyT]=$ValT;
    }
}
//--------------------- 評分計算 -----------------------
$idForIdx = 3;//對應 general.json["idfor"] ## 1-會員(廠商、專家…等)；2-課程商品；3-文章;4-服務；5-廣告套裝；6-加購服務；11-儲值紀錄；12-廣告紀錄；13-加購紀錄
$rateMode = "";
if(is_array($geJS['idfor'][$idForIdx]['rating'])){
  $getCount = count($geJS['idfor'][$idForIdx]['rating']);//計算評分項目數量
  if($getCount>0){//若有設定評分項目
    $rateMode = $getCount;
  } else {//若沒有設定評分項目，則以「like」模式計算
    $rateMode = 'like';
  }
}

//-----------------------------------------------------

$counT="SELECT AVG((rat_s1 + rat_s2 + rat_s3 + rat_s4 + rat_s5)/5) AS totAVE, AVG(rat_s1) AS S1, AVG(rat_s2) AS S2, AVG(rat_s3) AS S3, AVG(rat_s4) AS S4, AVG(rat_s5) AS S5 FROM fsl_rating WHERE rat_forid=? AND rat_for=?";
$cnt = DB->query($counT,array($artiID,$idForIdx));
$S1 = !empty($cnt[0]['S1']) ? floatval($cnt[0]['S1']) : 0;
$S2 = !empty($cnt[0]['S2']) ? floatval($cnt[0]['S2']) : 0;
$S3 = !empty($cnt[0]['S3']) ? floatval($cnt[0]['S3']) : 0;
$S4 = !empty($cnt[0]['S4']) ? floatval($cnt[0]['S4']) : 0;
$S5 = !empty($cnt[0]['S5']) ? floatval($cnt[0]['S5']) : 0;
$totAVE = !empty($cnt[0]['totAVE']) ? floatval($cnt[0]['totAVE']) : 0;
//$ratingItem = $geJS['idfor'][$idForIdx]['rating']['1'].','.$geJS['idfor'][$idForIdx]['rating']['2'].','.$geJS['idfor'][$idForIdx]['rating']['3'].','.$geJS['idfor'][$idForIdx]['rating']['4'].','.$geJS['idfor'][$idForIdx]['rating']['5'];
//$ratingData = $S1.','.$S2.','.$S3.','.$S4.','.$S5;
$ratingItem = "test,test2,test3";
$ratingData = "4,5,4";

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
	
  <title> Fifth Season Life 人生第五季 | 文章資料 </title>
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
  <!-- Dropzone JS style -->
  <link rel="stylesheet" href="../pluginFiles/dropzone/dropzone.css" />
  <!-- Cropper JS style -->
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <!-- suneditor -->
  <link rel="stylesheet" href="../pluginFiles/suneditor/dist/css/suneditor.min.css<?php echo '?t='.time(); ?>">
  <!-- codeMirror -->
  <link rel="stylesheet" href="../pluginFiles/codemirror/lib/codemirror.min.css<?php echo '?t='.time(); ?>">
  <!-- select2 -->
  <!-- <link rel="stylesheet" href="../pluginFiles/select2/css/select2.css"> -->
  <link rel="stylesheet" href="../pluginFiles/select2/css/select2.min.css">
  <link rel="stylesheet" href="../pluginFiles/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css<?php echo '?t='.time(); ?>">
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css<?php echo '?t='.time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.lightbox.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css<?php echo '?t='.time(); ?>">

<!-- -------------------------- /CSS Link -------------------------- -->
<style>

  .emailTest{
    cursor: pointer;
  }
  i[data-bs-toggle="tooltip"]{
    color: #236FB7;
    cursor: help;
  }



.review-body {
  width: 100%;               
  aspect-ratio: 1 / 1;      
  background-image: url('../imageFiles/likeNdislike.png');
  background-size: contain; /* 改成 contain 確保正方形內 100% 貼合 */
  background-position: center;
  background-repeat: no-repeat;
  position: relative;
  
  /* 關鍵 1：開啟容器查詢，這會把父層寬度封裝成一個獨立基準 */
  container-type: inline-size; 
}  

.review-box {
  position: absolute;
  /*color: #000000;*/
  padding: 1%;                  
  box-sizing: border-box;
}
.review-box-one {
  top: 26%;                     
  left: 30%;                    
  width: 65%; /* 稍微縮小寬度，給文字留一點點緩衝不折行 */
  /*background-color: rgba(255, 255, 0, 0.5);*/
}

.review-box-one h2 {
  /* 關鍵 2：改成 cqi (1cqi = 父層寬度的 1%) */
  /* 這樣無論有沒有捲軸，字體跟父層的比例絕對鎖死 */
  font-size: 18cqi; 
  margin: 0;
  color: #088146;
  white-space: nowrap; /* 強制文字絕對不折行，防止縮放時因為微調而突然掉到下一行 */
}

.review-box-two {
  bottom: 30%;                  
  right: 28%;                   
  width: 65%;                   
  text-align: right;
  /*background-color: rgba(255, 0, 255, 0.5);*/
}

.review-box-two p {
  font-size: 10cqi; /* 同步改用 cqi */
  margin: 0;
  color: #e3422a;
  white-space: nowrap;
}

/*=========================*/
/* 基礎星星大小與樣式 */
.my-stars .star {
    font-size: 20px;
    display: inline-block;
}
/* 滿星：全金色 */
.my-stars .star-full {
    color: #ffca28;
}
/* 空星：全灰色 */
.my-stars .star-empty {
    color: #e0e0e0;
}
/* 半星：利用漸層（Gradient）直接切出一半金色、一半灰色 */
.my-stars .star-half {
    background: linear-gradient(90deg, #ffca28 50%, #e0e0e0 50%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}


</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <!-- processing waiting -->
  <div id="loadingMask" style="top:0;left:0;position:absolute;display:block;width:100%;height:100%;background-color:#2A2826;background-position: 50% 0;opacity: 0.75;margin:0;text-align: center; z-index:99999;display:none;">
    <div style="color:#FFFFFF;position: relative;top: 50%;transform: translate(0, -50%);">
      <img src="../imageFiles/loading2.gif" style="left:50%;top:50%;"><br>測試郵件發送中...
    </div>
  </div>
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
              <h4 class="m-0"><?php echo !empty($artiID) ? "編輯" : "新增"; ?>圖文模板資料<span style="font-size:large;">【ID: <?php echo $artiID; ?>】</span></h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                  <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                  <li class="breadcrumb-item">圖文模板管理</li>
                  <li class="breadcrumb-item prevLink" alt="返回列表" title="返回列表">圖文模板紀錄</li>
                  <li class="breadcrumb-item active">圖文模板詳細資料</li>
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
                            <div class="overtext"><?php echo !empty($artiID) ? "更改" : "新增"; ?>代表圖像</div>
                          </div>
                          <input type="file" name="image" class="image" id="cropUploadImage" style="display:none" crop-width="800" crop-height="600">
                        </label>
                      </form>
                      <!-- -------------------- 裁切頭像彈出視窗 -------------------- -->
                      <?php include('include_avatarmodal.php'); ?>
                      <!-- -------------------- /裁切頭像彈出視窗 -------------------- -->
                    </div>
                    <!-- /Image Area -->
                  </div>
                  <h3 class="profile-article text-center"><?php echo !empty($res['arti_title']) ? $res['arti_title'] : "&nbsp;"; ?></h3>
                  <p class="text-muted text-center"><?php echo !empty($res['arti_subscript']) ? $res['arti_subscript'] : "&nbsp;"; ?></p>

                  <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                      <b>類別</b> <a class="float-right text-danger"><?php echo !empty($res['arti_type']) ? $artiJS['artitype'][$res['arti_type']]['text'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>主分類</b> <a class="float-right"><?php echo !empty($res['arti_cate']) ? $artiJS['articate'][$res['arti_cate']]['text'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>子分類</b> <a class="float-right"><?php echo !empty($res['arti_subcate']) ? $artiJS['articate'][$res['arti_cate']]['sub'][$res['arti_subcate']]['text'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>狀態</b> <a class="float-right<?php echo (!is_null(@$res['arti_status']) && @$res['arti_status']==0) ? " text-danger" : (!is_null(@$res['arti_status']) && @$res['arti_status']==1 ? " text-success" : " text-warning"); ?>"><?php echo !is_null(@$res['arti_status']) ? $artiJS['status'][@$res['arti_status']] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>編輯日期</b> <a class="float-right"><?php echo !empty($res['arti_published']) ? $res['arti_published'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>建立日期</b> <a class="float-right" style="color:black;"><b><?php echo !empty($res['arti_updated']) ? $res['arti_updated'] : "&nbsp;"; ?></b></a>
                    </li>
                    <li class="list-group-item">
                      <b>總瀏覽數</b> <a class="float-right" style="color:#cd5757;font-size:1.5em;"><b><?php echo !empty($res['arti_visits']) ? number_format($res['arti_visits']) : 0; ?></b></a>
                    </li>
                  </ul>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
              <!-- Rating -->
              <div class="card card-warning card-outline">
                <div class="card-header">
                  <h3 class="card-title">項目受評</h3>
                  <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body box-profile review-body">
                      <div class="review-box review-box-one">
                        <h2><?php echo !empty($res['arti_ratetop']) ? number_format($res['arti_ratetop']) : "0"; ?></h2>
                      </div>
                      
                      <!-- 子層元件 2 -->
                      <div class="review-box review-box-two">
                        <p><?php echo !empty($res['arti_ratebottom']) ? number_format($res['arti_ratebottom']) : "0"; ?></p>
                      </div>
                
                    <!--
                    <div style="position:absolute;font-size:60px;font-weight:bold;margin-top:-36px;">
                    <?PHP
                    /*
                    $getPoints = explode('.',round($totAVE,2));
                    $getStars = floor($totAVE);
                    $getHStar = round($totAVE - $getStars);
                    echo $getPoints[0];
                    if(!empty($getPoints[1])){
                    ?><span style="font-size:32px;font-weight:normal;">.<?PHP echo $getPoints[1]; ?></span>
                    <?PHP } ?>
                    </div>
                    <div style="position:absolute;margin-left:36px;margin-top:-16px;color:goldenrod;">
                    <?PHP
                      for($i=0;$i<intval($getStars);$i++){
                        echo '<i class="nav-icon fas fa-star"></i>';
                      }
                      if(intval($getStars)>0 && $getHStar>0){ echo '<i class="nav-icon fas fa-star-half-alt"></i>'; }
                    */
                      ?>
                    </div>
                    <canvas id="radarChart"></canvas>
                    -->

                </div>
                <div class="card-footer">
                  LSR平滑星級: <span class="my-stars">
                  <?php
                    if(!empty($artiID)){
                        $likes = $res['arti_ratetop'] ?? 0;
                        $dislikes = $res['arti_ratebottom'] ?? 0;
                        $views = $res['arti_visits'] ?? 0;
                        $hoursSincePublished = _fn_hours_passed($res['arti_dateopen'] ?? $res['arti_published']);//JazFIX: check here
                        $score = Evs::lsrForLikes($likes, $dislikes);
                        $starText = Evs::stars($score, true);
                        $promoScore = Evs::tds($score, $likes+$dislikes, $res['arti_visits'], $hoursSincePublished);
                        echo $starText. ' '. $score;
                    } else {
                        echo "&nbsp;";
                    }
                  ?>
                  </span>
                </div>
                <div class="card-footer">
                  TDS熱度權重: <span style="font-weight:bold;font-size:1.5em;"><?php echo !empty($artiID) ? $promoScore :  "&nbsp;"; ?></span>
                </div>
              </div>
              <!-- /Rating -->

            </div>
            <!-- /.col -->
            <div class="col-md-9">
              <div class="card card-secondary">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#tabMenu1" data-index="1" data-toggle="tab" data-topbut="1">文章資料修改</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu2" data-index="2" data-toggle="tab" data-topbut="1">文章主體編輯</a></li>
                    <?php if(!empty($artiID)) { ?>
                      <li class="nav-item"><a class="nav-link" href="#tabMenu3" data-index="3" data-toggle="tab" data-topbut="0">授權紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu4" data-index="4" data-toggle="tab" data-topbut="0">閱覽者紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu5" data-index="5" data-toggle="tab" data-topbut="0">評價紀錄</a></li>
                    <?php } ?>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu6" data-index="6" data-toggle="tab" data-topbut="0">替代文字說明</a></li>
                  </ul>
                </div><!-- /.card-header -->
                <div class="card-body">
                  <div class="tab-content">
                    <!-- 第一分頁文章資料修改 -->
                    <div class="tab-pane active" id="tabMenu1">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;部份輸入使用HTML編輯器編輯內容，可以美化文字，也可以嵌入超連結，請注意，編輯器要先內部儲存後，本頁面的更新才能有效儲存新的編輯資料。</div>
                      <hr>
                      <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/arti_act_func.php" method="post" data-formmode="infoupdate" data-func="func_arti_infoUpdate">
                        <input type="hidden" name="mainimgchk" id="mainimgchk" class="notSubmit" value="<?php echo $mainimgchk; ?>">
                        <div class="card-body">
                          <hr class="grouphr top">
                          <div class="groupfield top">文字內容設定</div>
                          <div class="form-group row">
                            <label for="arti_title" class="col-sm-2 col-form-label">文章抬頭</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="arti_title" name="arti_title" value="<?php echo !empty($res['arti_title']) ? $res['arti_title'] : ""; ?>" placeholder="文章抬頭" data-min="2" data-required<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="arti_subscript" class="col-sm-2 col-form-label">下標(郵件抬頭)</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="arti_subscript" name="arti_subscript" value="<?php echo !empty($res['arti_subscript']) ? $res['arti_subscript'] : ""; ?>" placeholder="下標"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="arti_alt" class="col-sm-2 col-form-label">滑鼠彈窗</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="arti_alt" name="arti_alt" value="<?php echo !empty($res['arti_alt']) ? $res['arti_alt'] : ""; ?>" placeholder="滑鼠彈窗"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="arti_tag" class="col-sm-2 col-form-label">
                              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="設定一組或數組關鍵字，做為文章的標籤或是分類，或為文章資料中的搜尋條件，兩組以上時以半形逗號「,」分隔。"></i>
                              自定義關鍵字
                            </label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="arti_tag" name="arti_tag" value="<?php echo !empty($res['arti_tag']) ? $res['arti_tag'] : ""; ?>" placeholder="自定義關鍵字"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="arti_brief" class="col-sm-2 col-form-label">簡述</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="arti_brief" name="arti_brief" rows="4" placeholder="簡述" data-min="10" data-required<?php echo NreadOnly; ?>><?php echo !empty($res['arti_brief']) ? stripslashes($res['arti_brief']) : ""; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="arti_note" class="col-sm-2 col-form-label">
                              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="做為文章重要的註腳說明，主要為法律相關的內容，如版權、著作權歸屬、來源出處....等。"></i>
                              加註(摘錄)
                            </label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="arti_note" name="arti_note" value="<?php echo !empty($res['arti_note']) ? $res['arti_note'] : ""; ?>" placeholder="加註"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <hr class="grouphr">
                          <div class="groupfield">搜尋指定撰稿人</div>
                          <div class="form-group row">
                            <label for="mb_id" class="col-sm-2 col-form-label">文章編撰</label>
                            <div class="col-sm-10">
                              <select id="mb_id" name="mb_id" class="form-control js-data-example-ajax"<?php echo NdisAble; ?>>
                              <?php
                                if(!empty($res['mb_id'])){
                                  $msql = "SELECT a.mb_id, a.mb_title, b.mb_cate, b.mb_cotype, b.mb_coexpert FROM fsl_members AS a INNER JOIN mb_info AS b ON b.mb_id=a.mb_id WHERE a.mb_id=?";
                                  $mres = DB->row($msql,array($res['mb_id']));
                                  if($mres){
                                    echo "<option value='".$mres['mb_id']."'>";
                                    echo "【".$mres['mb_id']."】";
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
                          <hr class="grouphr">
                          <div class="groupfield">文章歸類</div>
                          <div class="form-group row">
                            <label for="arti_keycode" class="col-sm-2 col-form-label">
                              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="設定一組字串，字串區分大小寫，字元中間不得存在空白符號，儘量簡易易懂，並避免使用特殊符號。這個字串具有唯一性，主要做為此文章的辨識條件，便於精準搜尋指定。此代碼非必要存在，且基於相關紀錄溯源的準確性，代碼一經設定後便無法更改。"></i>
                              指定代碼
                            </label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="arti_keycode" name="arti_keycode" value="<?php echo !empty($res['arti_keycode']) ? $res['arti_keycode'] : ""; ?>" placeholder="指定代碼"<?php echo !empty($res['arti_keycode']) ? " readonly" : NreadOnly; ?>>
                            </div>
                            <label for="arti_mode" class="col-sm-2 col-form-label">文章格式</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="arti_mode" name="arti_mode" data-required<?php echo NdisAble; ?>>
                                <?php
                                  foreach($artiJS['artimode'] as $key => $value){
                                      if($key=="") continue;
                                      echo '<option value="'.$key.'"';
                                      if(!empty($res['arti_mode']) && $res['arti_mode']==$key) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="arti_type" class="col-sm-2 col-form-label">
                              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="文章種類的指定主要作用在於文章的分類管理上，代表這則文章的建立目的，以及文章內容的結構，例如，標註「模板」的文章內容，即是代表這則文字稿屬於固定模板模式，替換目標文字後可套用於各類用途。"></i>
                              文章種類
                            </label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <select class="form-control" id="arti_type" name="arti_type" placeholder="文章種類" data-required<?php echo NdisAble; ?>>
                                  <option value="">-請選擇文章種類-</option>
                                  <?php
                                    foreach($artiJS['artitype'] as $key => $value){
                                        if($key=="") continue;
                                        if($key=="1") echo '<optgroup label="--系統專用--">';
                                        if($key=="11") echo '</optgroup><optgroup label="--公開使用--">';
                                        echo '<option value="'.$key.'" title="'.$value['des'].'"';
                                        if(!empty($res['arti_type']) && $res['arti_type']==$key) echo " selected";
                                        echo '>'.$value['text'].'</option>';
                                    }
                                    echo '</optgroup>';
                                  ?>
                                </select>
                                <div class="input-group-append">
                                  <span class="input-group-text btn btn-primary emailTest<?php echo !empty($res['arti_type']) && $res['arti_type'] == "2" ? "" : " disabled"; ?>">測試郵件</span>
                                </div>
                              </div>
                            </div>
                            <label for="arti_fromarea" class="col-sm-2 col-form-label">
                              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="地緣關係為文章的相關地域屬性，一些數值於不同的地方會有不同的結果，例如台灣的稅法便與海外的稅法不同。"></i>
                              地緣關係
                            </label>
                            <div class="col-sm-4">
                              <select class="form-control" id="arti_fromarea" name="arti_fromarea"<?php echo NdisAble; ?>>
                                <?php
                                  foreach($artiJS['fromarea'] as $key => $value){
                                      if($key=="") continue;
                                      echo '<option value="'.$key.'"';
                                      if(!empty($res['arti_fromarea']) && $res['arti_fromarea']==$key) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          
                          <div class="form-group row">
                            <label for="arti_cate" class="col-sm-2 col-form-label">
                              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="文章內容分類為文章內容的相關屬性，用於便利搜尋以及歸類。此為非必填項目。"></i>
                              內容分類
                            </label>
                            <div class="col-sm-4 select2-blue">
                              <select class="form-control" id="arti_cate" name="arti_cate" placeholder="內容分類" style="width: 100%;" data-required<?php echo NdisAble; ?>>
                                <option value="">-請選擇內容分類-</option>
                                <?php
                                  foreach($artiJS['articate'] as $key => $value){
                                      if($key=="") continue;
                                      echo '<option value="'.$key.'"';
                                      if(@$res['arti_cate'] == $key) echo " selected";
                                      echo '>'.$value['text'].'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                            <label for="arti_group" class="col-sm-2 col-form-label">
                              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="相關主分類之下的子分類小項，用於便利搜尋以及細部歸類，通常用於新聞時事、知識文章等大範圍的文章。此為非必填項目。滑鼠移到下拉選擇框可查看選項詳細說明。"></i>
                              子分類
                            </label>
                            <div class="col-sm-4 select2-green">
                              <select class="form-control" id="arti_subcate" name="arti_subcate" placeholder="子分類" style="width: 100%;"<?php echo NdisAble; ?>>
                                <option value="">-請選擇子分類-</option>
                                <?php
                                  $subJS=!empty($res['arti_cate']) ? $artiJS['articate'][$res['arti_cate']]['sub'] : array();
                                  foreach ($subJS as $key => $val) {
                                      echo '<option value="'.$key.'" title="'.$val['des'].'"';
                                      if(@$res['arti_subcate'] == $key) echo " selected";
                                      echo '>'.$val['text'].'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          <hr class="grouphr">
                          <div class="groupfield">訪客權限</div>
                          <div class="form-group row">
                            <label for="arti_guestbrowse" class="col-sm-2 col-form-label">訪客瀏覽</label>
                            <div class="col-sm-4" style="display:flex;align-items: center;">
                            <?php
                                $i=1;
                                foreach($artiJS['yesorno'] as $key => $value){
                                    if($key=="") continue;
                                    echo '<div class="icheck-primary d-inline">';
                                    echo '<input type="radio" id="arti_guestbrowse'.$i.'" name="arti_guestbrowse" value="'.$key.'"';
                                    if (($res['arti_guestbrowse'] ?? '') == $key) echo ' checked';
                                    echo !empty($res['arti_payfor']) ? " readOnly" : NdisAble;
                                    echo '>';
                                    echo '<label for="arti_guestbrowse'.$i.'" class="font-weight-light">'.$value.'&nbsp;&nbsp;&nbsp;&nbsp;</label>';
                                    echo '</div>';
                                    $i++;
                                }
                              ?>
                            </div>
                            <label for="arti_payfor" class="col-sm-2 col-form-label">訪客評價</label>
                            <div class="col-sm-4" style="display:flex;align-items: center;">
                            <?php
                                $i=1;
                                foreach($artiJS['yesorno'] as $key => $value){
                                    if($key=="") continue;
                                    echo '<div class="icheck-primary d-inline">';
                                    echo '<input type="radio" id="arti_guestrate'.$i.'" name="arti_guestrate" value="'.$key.'"';
                                    if($res['arti_guestrate'] != "" && intval($res['arti_guestrate']==$key)) echo " checked";
                                    echo !empty($res['arti_payfor']) ? " readOnly" : NdisAble;
                                    echo '>';
                                    echo '<label for="arti_guestrate'.$i.'" class="font-weight-light">'.$value.'&nbsp;&nbsp;&nbsp;&nbsp;</label>';
                                    echo '</div>';
                                    $i++;
                                }
                              ?>
                            </div>
                          </div>
                          <hr class="grouphr">
                          <div class="groupfield">付費瀏覽</div>
                          <div class="form-group row">                            
                            <label for="arti_payfor" class="col-sm-2 col-form-label">有償使用</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="arti_payfor" name="arti_payfor"<?php echo NdisAble; ?>>
                                <?php
                                  foreach($artiJS['yesorno'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if(!empty($res['arti_payfor']) && $res['arti_payfor']==$key) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                            <label for="arti_cost" class="col-sm-2 col-form-label">花費點數</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="arti_cost" name="arti_cost" value="<?php echo !empty($res['arti_cost']) ? $res['arti_cost'] : "0"; ?>" placeholder="花費點數"<?php
                              if(!empty($res['arti_payfor'])){
                                echo NdisAble;
                              } else {
                                echo " readOnly";
                              }
                              ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">點</span>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="arti_count" class="col-sm-2 col-form-label">
                              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="允許特定會員瀏覽的次數，若計次授權為0，則以授權天數為準，若授權天數亦為0，則為永久授權。"></i>
                              計次授權
                            </label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="arti_count" name="arti_count" value="<?php echo !empty($res['arti_count']) ? $res['arti_count'] : "0"; ?>" placeholder="計次授權"<?php
                              if(!empty($res['arti_payfor'])){
                                echo NdisAble;
                              } else {
                                echo " readOnly";
                              }
                              ?><?php
                              if(!empty($res['arti_payfor'])){
                                echo NdisAble;
                              } else {
                                echo " readOnly";
                              }
                              ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">次</span>
                                </div>
                              </div>
                            </div>
                            <label for="arti_days" class="col-sm-2 col-form-label">
                              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="允許特定會員瀏覽的天數，授權天數若為0，則以計次授權為準，若計次授權亦為0，則為永久授權。"></i>
                              授權天數
                            </label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="arti_days" name="arti_days" value="<?php echo !empty($res['arti_days']) ? $res['arti_days'] : "0"; ?>" placeholder="授權天數"<?php
                              if(!empty($res['arti_payfor'])){
                                echo NdisAble;
                              } else {
                                echo " readOnly";
                              }
                              ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">天</span>
                                </div>
                              </div>
                            </div>
                          </div>
                          <hr class="grouphr">
                          <div class="groupfield">功能參數值</div>
                          <div class="form-group row">
                            <label for="arti_ratetop" class="col-sm-2 col-form-label">
                              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="按讚數量的總和，此值由系統產生，無法藉由管理頁面更改，若要修改，必須由工程人員自資料底層修改。"></i>
                              按讚數量
                            </label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control notSubmit" id="arti_ratetop" name="arti_ratetop" value="<?php echo $res['arti_ratetop'] ?? '0'; ?>" readonly>
                            </div>
                            <label for="arti_ratebottom" class="col-sm-2 col-form-label">
                              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="倒讚數量的總和，此值由系統產生，無法藉由管理頁面更改，若要修改，必須由工程人員自資料底層修改。"></i>
                              倒讚數量
                            </label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control notSubmit" id="arti_ratebottom" name="arti_ratebottom" value="<?php echo $res['arti_ratebottom'] ?? '0'; ?>" readonly>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="arti_lsr" class="col-sm-2 col-form-label">
                              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="平滑星級（拉普拉斯平滑得分 / LSR），採用統計學平滑修正。新文章預設給予中立的基本星級，隨著讚與倒讚累積，評分將逐步貼近大眾真實評價。"></i>
                              平滑星級
                            </label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control notSubmit" id="arti_lsr" name="arti_lsr" value="<?php echo $score ?? ''; ?>" readonly>
                            </div>
                            <label for="arti_tds" class="col-sm-2 col-form-label">
                              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="熱度分數（時間衰減熱度評分 / TDS），融合文章按讚率與點閱數，並引入時間衰減重力。越新、越受歡迎的文章分數越高，舊文章分數則會隨時間自然下滑。"></i>
                              熱度權重
                            </label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control notSubmit" id="arti_tds" name="arti_tds" value="<?php echo $promoScore ?? ''; ?>" readonly>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="arti_dateopen" class="col-sm-2 col-form-label">
                              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="這則圖文資料的上架日期，在上架日期之前時，系統將視為下架狀態。"></i>
                              上架日期
                            </label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="text" class="form-control" id="arti_dateopen" name="arti_dateopen" data-inputmask-alias="datetime" data-inputmask-inputformat="yyyy-mm-dd" value="<?php echo $res['arti_dateopen'] ?? ''; ?>" data-mask<?php echo NreadOnly; ?>>
                              </div>
                            </div>
                            <label for="arti_dateclose" class="col-sm-2 col-form-label">
                              <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="下架日期與文章狀態的關聯，即是下架日期過後，即使文章狀態為上架狀態，文章亦會自動下架終止顯示；而當文章狀態為下架狀態時，即使下架日期尚未到達，文章亦會自動下架。也就是說，下架或過期兩者只要存在其一，這則文字便屬於下架狀態。"></i>
                              下架日期
                            </label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="text" class="form-control" id="arti_dateclose" name="arti_dateclose" data-inputmask-alias="datetime" data-inputmask-inputformat="yyyy-mm-dd" value="<?php echo $res['arti_dateclose'] ?? ''; ?>" data-mask<?php echo NreadOnly; ?>>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="arti_status" class="col-sm-2 col-form-label">文章狀態</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="arti_status" name="arti_status"<?php echo NdisAble; ?>>
                                <?php
                                  foreach($artiJS['status'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(!is_null(@$res['arti_status']) && @$res['arti_status']==$key) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                            <label for="arti_dateclose" class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-4">
                              <div class="input-group"></div>
                            </div>
                          </div>
                          <hr>
                        </div>
                        <!-- /.card-body -->
                      </form>
                    </div>
                    <!-- /第一分頁文章資料修改 -->
                    <!-- 第二分頁文章主體編輯 -->
                    <div class="tab-pane" id="tabMenu2">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                      <i class="nav-icon fas fa fa-exclamation-circle"></i>&nbsp;&nbsp;Html 編輯器使用 SunEditor 2.46.3 版本，並依照 MIT 授權條款內容行使權利及義務。</div>
                      <hr>
                      <form id="form_tabMenu2" name="form_tabMenu2" action="../funcFiles/arti_act_func.php" method="post" data-formmode="htmlupdate" data-func="func_arti_infoUpdate">
                        <div class="card-body">
                          <textarea name="arti_body" id="arti_body"><?php echo !empty($res['arti_body']) ? stripslashes($res['arti_body']): ""; ?></textarea>
                        </div>
                      </form>  
                    </div>
                    <!-- /第二分頁文章主體編輯 -->
                    <?php if(!empty($artiID)) { ?>
                    <!-- 第三分頁授權紀錄 -->
                    <div class="tab-pane" id="tabMenu3">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                      <i class="nav-icon fas fa fa-exclamation-circle"></i>&nbsp;&nbsp;無論有償無償的文章，都有著授權閱讀的功能，而這些被授予閱讀權力的使用者，將被列入表列中被記錄管理。</div>
                      <hr>
                      <!-- /.card-header -->
                      <div class="card-body">
                        <table id="dataTable_authList" class="table table-bordered table-striped table-hover" data-search>
                          <thead>
                            <tr>
                              <th>日期</th>
                              <th>來自</th>
                              <th>評論</th>
                            </tr>
                          </thead>
                          <tbody>
                              <tr>
                                <td>xxxx</td>
                                <td>xxxx</td>
                                <td>xxxx</td>
                              </tr>
                          </tbody>
                          <tfoot>
                            <tr>
                              <th>日期</th>
                              <th>來自</th>
                              <th>評論</th>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第三分頁授權紀錄 -->
                    <!-- 第四分頁閱覽者紀錄 -->
                    <div class="tab-pane" id="tabMenu4">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa fa-exclamation-circle"></i>&nbsp;&nbsp;只要是順利進入閱覽室閱讀，系統針對每一次的閱覽都會存入一筆紀錄，而這些紀錄除了用於授權管理外，還可以用做閱覽習慣的分析，以利未來的進階改版用。
                      </div>
                      <hr>
                      <!-- card-body -->
                      <div class="card-body">
                        <table id="dataTable_readerList" class="table table-bordered table-striped table-hover" data-search>
                          <thead>
                            <tr>
                              <th>日期</th>
                              <th>來自</th>
                              <th>評論</th>
                            </tr>
                          </thead>
                          <tbody>
                              <tr>
                                <td>xxxx</td>
                                <td>xxxx</td>
                                <td>xxxx</td>
                              </tr>
                          </tbody>
                          <tfoot>
                            <tr>
                              <th>日期</th>
                              <th>來自</th>
                              <th>評論</th>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第四分頁閱覽者紀錄 -->
                    <!-- 第五分頁評價紀錄 -->
                    <div class="tab-pane" id="tabMenu5">
                    <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;會員評價是為了本站未來更為友善操作的改版，而整合使用者的使用體驗數據，無論評價數據產生的時間有多久，評價紀錄不應也不將自資料庫中刪除。
                      </div>
                      <hr>
                      <!-- card-body -->
                      <div class="card-body">
                        <table id="dataTable_ratingList" class="table table-bordered table-striped table-hover" data-search>
                          <thead>
                            <tr>
                              <th>日期</th>
                              <th>來自</th>
                              <th>按讚數</th>
                              <th>倒讚數</th>
                              <th>評論</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                              $rsql = "
                                SELECT r.*, m.mb_title
                                FROM fsl_rating r
                                LEFT JOIN fsl_members m ON r.mb_id = m.mb_id
                                WHERE rat_for=? AND rat_forid=? ORDER BY r.rat_id DESC LIMIT 200
                              ";
                              $rr = DB->query($rsql,array(3,$artiID));

                              //$sql="SELECT * FROM fsl_rating WHERE rat_forid=? AND rat_for=?";
                              //$srr = DB->query($sql,array($artiID,$idForIdx));
                              //$i = 0; //以$i為$nameList這個陣列的索引值
                              //while($i<count($srr)){
                              foreach ($rr as $rat) {
                                ?>
                                <tr>
                                  <td><?php echo $rat['rat_datetime']; ?></td>
                                  <td><?php echo $rat['mb_id']; ?></td>
                                  <td><?php echo $rat['rat_s1']; ?></td>
                                  <td><?php echo $rat['rat_s2']; ?></td>
                                  <td>
                                  <?php if(!empty($rat['rat_review'])) { ?>
                                    <button type="button" class="btn btn-info btn-block btn-sm" title="<?php echo $rat['rat_review']; ?>" alt="<?php echo $rat['rat_review']; ?>"><i class="fa fa-comments"></i> </button>
                                  <?php } ?>
                                  </td>
                                </tr>
                            <?php 
                                //$i++;
                                } 
                              ?>
                          </tbody>
                          <tfoot>
                            <tr>
                              <th>總平均</th>
                              <th></th>
                              <th><?php echo $S1; ?></th>
                              <th><?php echo $S2; ?></th>
                              <th style="font-size:1.2em;font-weight:bold;"><?php echo $totAVE; ?></th>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第五分頁評價紀錄 -->
                    <?php } ?>
                    <!-- 第六分頁替代文字說明 -->
                    <div class="tab-pane" id="tabMenu6">
                      <div style="font-size:0.85em;line-height:1.4em;">
                        <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;作為系統模版的圖文記錄時，為使系統與使用者之間達到互動關係，時常會需要替換某些指定性的文字，例如會員暱稱、帳戶餘額、日期時間....等等，而系統只能辨識某個目標數據對應的指定字串，而主動進行替換。
                        </div>
                        <hr>
                        <!-- card-body -->
                        <div class="card-body">

                          <table id="dataTable_desList" class="table table-bordered table-striped table-hover" data-search>
                            <thead>
                              <tr>
                                <th>代碼</th>
                                <th>替換目標</th>
                                <th>對應值或資料表</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>[@userid@]</td>
                                <td>會員ID</td>
                                <td>fsl_members.mb_title.mb_id</td>
                              </tr>
                              <tr>
                                <td>[@username@]</td>
                                <td>會員顯示名稱</td>
                                <td>fsl_members.mb_title.mb_id</td>
                              </tr>
                              <tr>
                                <td>[@useremail@]</td>
                                <td>會員電子郵件</td>
                                <td>fsl_members.mb_email.mb_id</td>
                              </tr>
                              <tr>
                                <td>[@currdate@]</td>
                                <td>指定日期</td>
                                <td>time(Y-m-d)</td>
                              </tr>
                              <tr>
                                <td>[@nowdate@]</td>
                                <td>現在日期</td>
                                <td>time(Y-m-d H:i:s)</td>
                              </tr>
                              <tr>
                                <td>[@startdate@]</td>
                                <td>起始日期</td>
                                <td>time(Y-m-d H:i:s)</td>
                              </tr>
                              <tr>
                                <td>[@enddate@]</td>
                                <td>結束日期</td>
                                <td>time(Y-m-d H:i:s)</td>
                              </tr>
                              <tr>
                                <td>[@linkurl@]</td>
                                <td>指定鏈結</td>
                                <td>URL/URI</td>
                              </tr>
                              <tr>
                                <td>[@+3600@]</td>
                                <td>距指定日期時間n秒後的日期時間</td>
                                <td>time() + n</td>
                              </tr>
                              <tr>
                                <td>[@-3600@]</td>
                                <td>距指定日期時間n秒之前的日期時間</td>
                                <td>time() - n</td>
                              </tr>
                              <tr>
                                <td>[@inpoint@]</td>
                                <td>現在儲值點數</td>
                                <td>fsl_points.pt_trade.mb_id</td>
                              </tr>
                              <tr>
                                <td>[@nowpoint@]</td>
                                <td>現在剩餘點數</td>
                                <td>fsl_points.pt_trade.mb_id</td>
                              </tr>
                              <tr>
                                <td>[@comptel@]</td>
                                <td>電話</td>
                                <td>公司聯絡電話</td>
                              </tr>
                              <tr>
                                <td>[@compext@]</td>
                                <td>分機號碼</td>
                                <td>公司聯絡電話分機號碼</td>
                              </tr>
                              <tr>
                                <td>[@extrain@]</td>
                                <td>額外附加內容</td>
                                <td>某些郵件除了模板所載內容外，尚需要額外的附加說明，這些說明為HTML格式，往往位於本文與署名之間。</td>
                              </tr>
                            </tbody>
                            <tfoot>
                              <tr>
                                <th>代碼</th>
                                <th>替換目標</th>
                                <th>對應值或資料表</th>
                              </tr>
                            </tfoot>
                          </table>
                        </div>
                      <!-- /.card-body -->                     
                    </div>
                    <!-- /第六分頁替代文字說明 -->
                  </div>
                  <!-- /.tab-content -->
                </div><!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
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

<!-- ChartJS -->
<script src="../pluginFiles/chart.js/Chart.min.js"></script>
<!-- ezValidation -->
<script src="../pluginFiles/validator/validator.js<?php echo '?t='.time(); ?>"></script>
<!-- Dropzone JS -->
<script src="../pluginFiles/dropzone/dropzone.js"></script>
<!-- Cropper JS -->
<script src="../pluginFiles/cropper/cropper.js"></script>
<!-- suneditor -->
<script src="../pluginFiles/suneditor/dist/suneditor.min.js<?php echo '?t='.time(); ?>"></script>
<script src="../pluginFiles/suneditor/sample/js/common.js<?php echo '?t='.time(); ?>"></script>
<script src="../pluginFiles/suneditor/src/lang/zh_tw.js<?php echo '?t='.time(); ?>"></script>
<!-- codemirror -->
<script src="../pluginFiles/codemirror/lib/codemirror.min.js<?php echo '?t='.time(); ?>"></script>
<script src="../pluginFiles/codemirror/mode/htmlmixed/htmlmixed.js<?php echo '?t='.time(); ?>"></script>
<script src="../pluginFiles/codemirror/mode/xml/xml.js<?php echo '?t='.time(); ?>"></script>
<script src="../pluginFiles/codemirror/mode/css/css.js<?php echo '?t='.time(); ?>"></script>
<!-- select2 -->
<!-- <script src="../pluginFiles/select2/js/select2.js"></script> -->
<script src="../pluginFiles/select2/js/select2.full.min.js"></script>
<script src="../pluginFiles/select2/js/i18n/zh-TW.js"></script>
<!-- InputMask 日期輸入元件JS -->
<script src="../pluginFiles/moment/moment.min.js"></script>
<script src="../pluginFiles/inputmask/jquery.inputmask.min.js"></script>
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<!-- Custom JS style -->
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
<script src="../scriptFiles/fileupload.js<?php echo '?t='.time(); ?>"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

<!-- ------------------------ /Scripts Link ------------------------ -->
<script type="text/javascript">
      // JSON_UNESCAPED_UNICODE 防止中文變成 \u4e2d\u6587
    // JSON_HEX_TAG / JSON_HEX_AMP 確保安全性，防止 XSS 注入
  var artiObj = <?php echo json_encode($artiJS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

  var briefActive = false;
  var bodyActive = false;
  var sunBrief;
  var sunBody;

  //
  $(function () {
    //------------------------- Tooltips -----------------------------//
    $('[data-toggle="tooltip"]').tooltip();
    //* iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });
    //-----------------------------------------
    var authListTB = datatableCreate('dataTable_authList');
    var readerListTB = datatableCreate('dataTable_readerList');
    var ratingListTB = datatableCreate('dataTable_ratingList');
    var desListTB = datatableCreate('dataTable_desList');
    
    sunBrief = suneditorCreate('arti_brief','ez','100%','204');
    sunBrief.onChange = function (contents, core) {
      if(contents !== $('#arti_brief').val()){
        briefActive = true;
        window.parent.$('#topBut_save').addClass('active');
      } else {
        briefActive = false;
        if(bodyActive == false) { window.parent.$('#topBut_save').removeClass('active'); }
      }
    }

    sunBody = suneditorCreate('arti_body','full','100%');
    sunBody.onChange = function (contents, core) {
      if(contents !== $('#arti_body').val()){
        bodyActive = true;
        window.parent.$('#topBut_save').addClass('active');
      } else {
        bodyActive = false;
        if(briefActive == false) { window.parent.$('#topBut_save').removeClass('active'); }
      }
    }
    /////////////////////////////////////////////////////////////////////////
    //////////////////// SELECT2 複選元件初始化 //////////////////
    $('.select2').select2();
    //////////////////// DATEMASK 日期輸入元件初始化 //////////////
    $('#datemask2').inputmask('yyyy-mm-dd', { 'placeholder': 'yyyy-mm-dd' })
    $('[data-mask]').inputmask();
    /////////////////////////////////////////////////////////////////////////
  // ------------------------- 評價五角圖 ------------------------------- //
  /*
  var ctx = document.getElementById( "radarChart" );
    ctx.height = 280;
    var myChart = new Chart( ctx, {
        type: 'radar',
        data: {
          labels: '<?php //echo $ratingItem; ?>'.split(','),//$ratingItem $ratingData
          datasets: [
                          {
                            label: "項目受評",
                            data: '<?php //echo $ratingData; ?>'.split(','),
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
                          fontSize: 12
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
    */
    // --------------- 分類選單操作 ------------------
    // --------------- 當改變文章主分類選項時 ------------------
    $('#arti_cate').on('change', function(){
      var cateId = $(this).val();//Array
      var subId = $('#arti_subcate').val();//Array
      var ac = artiObj['articate'];
      $('#arti_subcate').html('<option value="">-請選擇內容子分類-</option>');
      $.each(ac[cateId]['sub'], function( index, value ) {
        var appendHTML = '<option value="' + index + '" title="' + value['des'] + '">'+ value['text'] +'</option>';
        $('#arti_subcate').append(appendHTML);
      });
    });
    /*
    $('#arti_cate').on('change', function(){
      var cateId = $(this).val();//Array
      var subId = $('#arti_subcate').val();//Array
      var ac = artiObj['articate'];
      $('#arti_subcate').html('');
      for(var i=0; i<cateId.length; i++){
        $('#arti_subcate').append('<optgroup label="-- '+ ac[cateId[i]]['text'] +' --">');
        $.each(ac[cateId[i]]['sub'], function( index, value ) {
          var appendHTML = '<option value="' + index + '" title="' + value['des'] + '"';
          if ($.inArray(index, subId) >= 0) { appendHTML += ' selected'; }
          appendHTML += '>'+ value['text'] +'</option>';
          $('#arti_subcate').append(appendHTML);
        });
      }
    });
    */
  // --------------------- 改變有無償收費選項時操作 ------------------------------- //
    $('#arti_payfor').on('change', function(){
      var payId = $(this).val();//Array
      if(payId==1){//arti_cost arti_count arti_days arti_guestbrowse
        $('input[name="arti_guestbrowse"][value="0"]').prop('checked', true);
        $('input[name="arti_guestbrowse"][value="1"]').prop('disabled', true);
        $('input[name="arti_guestrate"][value="0"]').prop('checked', true);
        $('input[name="arti_guestrate"][value="1"]').prop('disabled', true);
        $('#arti_cost').val('').attr('readonly', false);
        $('#arti_count').val('').attr('readonly', false);
        $('#arti_days').val('').attr('readonly', false);
      } else {
        $('input[name="arti_guestbrowse"][value="1"]').prop('disabled', false);
        $('input[name="arti_guestrate"][value="1"]').prop('disabled', false);
        $('#arti_cost').val('').attr('readonly', true);
        $('#arti_count').val('').attr('readonly', true);
        $('#arti_days').val('').attr('readonly', true);
      }

  
    });
  // ------------------------- 鍵入搜尋 ------------------------------- //
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
            url: "../funcFiles/mb_act_func.php",
            //url: "../_test/pure.php",
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    type: 'user_search',
                    ifCate: 2,
                    ifCotype: '1,5',
                    ifExpert: '',
                    token: getTokenVal(),                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        
                    formAcce: 'func_mb_selector'
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


///////////////////////// 文章分類變更時操作 ////////////////////////////////////
    $('#arti_type').on('change',function(){
      if($(this).val() == 2){
        $('.emailTest').removeClass('disabled');
      } else {
        $('.emailTest').addClass('disabled');
      }
    });
///////////////////////// 郵件模板測試 ////////////////////////////////////
    $('.emailTest').on('click',function(){
      //$('.preloader').css("height", "").children().show();
      var type = $('#arti_type').val();
      if(type!=2){ return false; }
      if(bodyActive == true) { sunBody.save(); }
      if($('#arti_subscript').val()=='' || $('#arti_body').val()==''){
        alert('請輸入郵件標題和內容'); 
        return false; 
      } 
      var mailTo = '';
      var setMailto = window.prompt('請輸入測試用的郵件地址', '<?php echo $_SESSION['SESS_AD_EMAIL']; ?>');
      if (setMailto == null || "") {
          return false;
      } else {
          const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if(!regex.test(setMailto)) {
              alert('請輸入正確的郵件地址');
              return false;
          }
          mailTo = setMailto;
      }


      window.parent.$('#loadingMask').find('span').html('測試郵件發送中...');
      window.parent.$('#loadingMask').show();

      var htmlALL = $('#arti_subscript').val() + $('#arti_body').val();
      //htmlALL = regex.Replace(htmlALL, "\t|\n|\r", "")
      var htmlALLsplit = htmlALL.split(/\[\@|\@\]/);
      var tagArr = [];
      $.each( htmlALLsplit, function( key, value ) {
        if (key != 0 && key%2 != 0) {
          tagArr.push( '[@'+value+'@]' );
        }
      });

      $.ajax({
          type: 'post',
          url: '../funcFiles/sys_email_func.php',
          data: {
            'arrDatA': {
              "subject": $('#arti_subscript').val(),
              "body": $('#arti_body').val(),
              "mailTo": mailTo,
              "replaceTag": tagArr,
              "replaceStr": []
            }, 
            'formAcce':'func_email_preview',
            'token': getTokenVal(), 
            'artiID':'<?php echo $artiID; ?>'
          },
          dataType: 'json',
          //async: false,
          success: function (data) {
              //$('.preloader').css("height", 0).children().hide();
              window.parent.$('#loadingMask').hide();
              window.parent.$('#loadingMask').find('span').html('');
              window.parent.toastr[data['msgType']](data['msgStr']);
              console.log(data);
          },
          error: function (data) {
            $('.preloader').css("height", 0).children().hide();
            window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
            console.log(data);
          }
      });

        //return false;
        //$('.emailTest').addClass('btn-primary');
    });
  // 內頁右上方小導航返回列表頁
  $('li.prevLink').on('click', function(){
    window.location.replace(getCurrentURI('','arti_list.php'));
  });

});
  ////////////////////////////////////////////////////////////////     
  // ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
  // -------------- 可接收include檔案要求後自定義動作 ------------- //
  ////////////////////////////////////////////////////////////////
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
            url: '../funcFiles/arti_act_func.php',
            data: {
              'arrDatA': {}, 
              'formAcce':'func_arti_delete',
              'token': getTokenVal(), 
              'artiID':'<?php echo $artiID; ?>'
            },
            dataType: 'json',
            async: false,
            beforeSend: function () {
              if(!confirm('刪除紀錄成功後將無法回復，確定執行這個操作？！')){ return false; }
            },
          success: function (data) {
              if(data['RTN']==1){
                window.location.replace("arti_list.php");
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
  function ajaxPOST(frm){
    var frm = $('#form_'+$('.tab-pane.active').prop("id"));
    var accE = frm.data('func');
    var url = frm.prop('action');
    var typE = frm.prop('method');
    var datA,datB,formData={};
    var imgBase64='';
    if(accE=='func_arti_infoUpdate'){
      datA = getFormObjects($('#form_tabMenu1'));
      datB = getFormObjects($('#form_tabMenu2'));
      formData = Object.assign(formData, datA, datB);
      imgBase64 = $('#main_crop_area').data('cropImage64') || '';
    }
    $.ajax({
        type: typE,
        url: url,
        data: {
          'arrDatA':formData, 
          'formAcce':accE,
          'imgUp': imgBase64,
          'token': getTokenVal(), 
          'artiID':'<?php echo $artiID; ?>'
        },
        dataType: 'json',
        async: false, 
      success: function (data) {
          console.log(data);
          if(accE=='func_arti_infoUpdate'){
              if (data['RTN']==1) {
                if(data['newID'] && parseInt(data['newID'])>0){
                  window.location.replace("arti_detail.php?currID="+data['newID']);
                  return true;
                }
                var newType = artiObj['artitype'][$('#arti_type').val()];
                var newSta = artiObj['status'][$('#arti_status').val()];
                switch ($('#arti_status').val()){
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
                $('.profile-article').html($('#arti_title').val());
                $('.profile-article + p').html($('#arti_subscript').val());
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
