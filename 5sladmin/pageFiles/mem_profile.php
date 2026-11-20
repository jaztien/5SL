<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_MEM');
define('PAGE_ITEM','ITEM_MEM');
define('bkRedir',1);
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//主要資料表欄位資料取得
$mbID=currID;
//主要資料表欄位資料取得
$sql='SELECT * FROM members WHERE mb_id=?'; 
$res = $DB->row($sql,array($mbID));
//功能陣列建立，取自JSON檔案
//$mdGet=file_get_contents("../jsonFiles/merchandise.json");
//$merchGet=file_get_contents("../jsonFiles/merchinfo.json");
//$geGet=file_get_contents("../jsonFiles/general.json");
$mbJS = _fn_getCachedJsonData('../jsonFiles/member.json');
$mdJS = _fn_getCachedJsonData('../jsonFiles/merchandise.json');
$merchJS = _fn_getCachedJsonData('../jsonFiles/merchinfo.json');
$geJS = _fn_getCachedJsonData('../jsonFiles/general.json');
//$locGet=file_get_contents("../jsonFiles/locations.json");
$locJS = _fn_getCachedJsonData('../jsonFiles/locations.json');
foreach($mbJS['cotype'] as $key=>$Val){
    $coTypeJS[$key]=$Val['type'];
    foreach($Val['expert'] as $keyT=>$ValT){
      $expertJS[$keyT]=$ValT;
    }
}
//--------------------- 評分計算 -----------------------
/*
$S1 = $res['mb_s1'] ?? 0;
$S2 = $res['mb_s2'] ?? 0;
$S3 = $res['mb_s3'] ?? 0;
$S4 = $res['mb_s4'] ?? 0;
$S5 = $res['mb_s5'] ?? 0;
$totAVE = ($S1+$S2+$S3+$S4+$S5)/5;
*/
/*
$counT="SELECT AVG((rat_s1 + rat_s2 + rat_s3 + rat_s4 + rat_s5)/5) AS totAVE, AVG(rat_s1) AS S1, AVG(rat_s2) AS S2, AVG(rat_s3) AS S3, AVG(rat_s4) AS S4, AVG(rat_s5) AS S5 FROM data_rating WHERE rat_forid=? AND rat_for=?";
$cnt = $DB->query($counT,array($mbID,1));
$S1 = !empty($cnt[0]['S1']) ? floatval($cnt[0]['S1']) : 0;
$S2 = !empty($cnt[0]['S2']) ? floatval($cnt[0]['S2']) : 0;
$S3 = !empty($cnt[0]['S3']) ? floatval($cnt[0]['S3']) : 0;
$S4 = !empty($cnt[0]['S4']) ? floatval($cnt[0]['S4']) : 0;
$S5 = !empty($cnt[0]['S5']) ? floatval($cnt[0]['S5']) : 0;
$totAVE = !empty($cnt[0]['totAVE']) ? floatval($cnt[0]['totAVE']) : 0;
*/
//------------------ Get Points ------------------
$sqlp="
SELECT SUM(mbp_trade) AS toT, 
SUM(CASE WHEN mbp_trade>0 AND mbp_redeem=1 THEN mbp_trade ELSE 0 END) AS toRN, 
SUM(CASE WHEN mbp_trade>0 THEN mbp_trade ELSE 0 END) AS toIN, 
SUM(CASE WHEN mbp_trade<0 THEN mbp_trade ELSE 0 END) AS toOUT 
FROM mb_points WHERE mb_id=? AND mbp_status>?";
$srp = $DB->query($sqlp,array($mbID,0));


$totalPoints=(!empty($res['mb_points']) && intval($res['mb_points'])==intval($srp[0]['toT'])) ? intval($res['mb_points']) : intval($srp[0]['toT']);
$totalRedeem=(!empty($res['mb_cashback']) && intval($res['mb_cashback'])==intval($srp[0]['toRN'])) ? intval($res['mb_cashback']) : intval($srp[0]['toRN']);
if($totalPoints > 0 && $totalRedeem > $totalPoints){
  $totalRedeem = $totalPoints;
}

//代表圖像設定
if(!empty($mbID)&&file_exists("../uploadFiles/mb/mbphoto_".$mbID."_thumb.jpg")){
  $cropImgDisplay = 'show';
  $mainimgpath = '../uploadFiles/mb/mbphoto_'.$mbID.'_thumb.jpg';
  $mainimgchk = 'arphoto_'.$mbID.'_.jpg';
} else {
  $cropImgDisplay = 'hide';
  $mainimgpath = '../uploadFiles/imageFiles/nopic_400x300.jpg';
  $mainimgchk = "";
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
  <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
	
  <title> Fifth Season Life 人生第五季 | 會員資料 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.css">
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
  <!-- Toastr 移往外框
  <link rel="stylesheet" href="../pluginFiles/toastr/toastr.min.css"> -->
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Dropzone JS style -->
  <link rel="stylesheet" href="../pluginFiles/dropzone/dropzone.css" />
  <!-- Cropper JS style -->
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
<!-- -------------------------- /CSS Link -------------------------- -->
</head>
<body class="layout-navbar-fixed hold-transition sidebar-mini">
  <!-- 全頁框 -->
  <div class="wrapper">
    <!-- Preloader 
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="../imageFiles/preloaderLOGO.png" alt="Fifth Season Life LOGO" height="60" width="60"> 
    </div>
  -->
    <!-- /Preloader -->
    <!-- 頁內全頁內容 -->
    <div class="content-wrapper">
      <!-- 頁頭導覽條 -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h4 class="m-0"><?php if(empty($mbID)) echo "新增"; ?>會員資料 <span style="font-size:large;"><?php echo !empty($mbID) ? "ID: ".$mbID:""; ?></span></h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                  <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                  <li class="breadcrumb-item">會員管理</li>
                  <li class="breadcrumb-item prevLink" alt="返回列表" title="返回列表">會員資料列表</li>
                <li class="breadcrumb-item active">會員資料</li>
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
              <div class="card card-primary card-outline shadow">
                <div class="card-body box-profile">
                  <div class="text-center fa-ul"><i id="orgShow" name="orgShow" class="nav-icon fas fa-2x<?php //if(!empty($res['mb_org']) && intval($res['mb_org']==1)) echo " fa-users"; ?>" style="left:6%;position:absolute;color:#2C333E;text-shadow:2px 2px 4px #B8BFCB;"></i>
                    <div class="image_area">
                      <i class="nav-icon fas fa-2x fa-trash-can cropImgTrash<?php echo " ".$cropImgDisplay; ?>" alt="刪除圖像" title="刪除圖像"></i>
                      <form method="post">
                        <label for="cropUploadImage">
                          <img id="avatarImage" name="avatarImage" class="img-responsive profile-user-img img-fluid img-circle imgEDicon" src="<?php echo $mainimgpath."?".time(); ?>" alt="會員頭像" title="會員頭像"/>
                          <div class="overlay">
                            <div class="overtext">更改代表圖</div>
                          </div>
                          <input type="file" name="image" class="image" id="cropUploadImage" style="display:none" crop-width="400" crop-height="400">
                        </label>
                      </form>
                      <!-- -------------------- 裁切頭像彈出視窗 -------------------- -->
                      <?php include('include_avatarmodal.php'); ?>
                      <!-- -------------------- /裁切頭像彈出視窗 -------------------- -->
                    </div>
                  </div>
                  <h3 class="profile-username text-center"><?php echo !empty($res['mb_title'])?$res['mb_title']:''; ?></h3>
                  <p class="text-muted text-center"><?php echo !empty($res['mb_cate'])?$mbJS['cate'][$res['mb_cate']]:''; ?></p>
                  <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                      <b>狀態</b> <a class="float-right<?php if(!empty($res['mb_status'])) { echo ($res['mb_status']==1) ? "" : " text-danger";} ?>"><?php echo !empty($res['mb_status'])?$mbJS['status'][$res['mb_status']]:''; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>編輯日期</b> <a class="float-right"><?php echo !empty($res['mb_updated'])?$res['mb_updated']:''; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>建立日期</b> <a class="float-right" style="color:black;"><b><?php echo !empty($res['mb_published'])?$res['mb_published']:''; ?></b></a>
                    </li>
                  </ul>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
              <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="nav-icon fas fa-1x fa-coins"></i>&nbsp;兌換幣餘額</h3>
                    <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                    </div>
                </div>
                <div class="card-body box-profile" style="padding:48px 0px 48px 0px;">
                  <div class="row" style="position:relative;top:-24px;">
                      <div class="col-sm-6" style="padding-left:20px;">已使用&nbsp;<i class="nav-icon fas fa-coins"></i><i class="nav-icon fas fa-long-arrow-alt-up" style="color:#C35050;"></i></div>
                      <div class="col-sm-6" style="padding-right:20px;text-align:right;">剩餘&nbsp;<i class="nav-icon fas fa-coins"></i><i class="nav-icon fas fa-long-arrow-alt-down" style="color:#44a273;"></i></div>
                  </div>
                  <div style="position:absolute;font-size:24px;font-weight:bold;left:12px;margin-top:-24px;color:#C35050;"><?php echo !empty($srp[0]['toOUT']) ? number_format($srp[0]['toOUT']) : "0"; ?></div>
                  <div style="position:absolute;font-size:48px;font-weight:bold;right:12px;margin-top:-36px;color:#44a273;">
                    <?php echo !empty($srp[0]['toT']) ? number_format($srp[0]['toT']) : "0"; ?>
                    </div>
                </div>
              </div>
              <!-- /.card -->
              <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">會員受評</h3>
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
                  <!--
                    <div style="position:absolute;font-size:60px;font-weight:bold;margin-top:-36px;">
                    <?php
                    /*
                    $getPoints = explode('.',$totAVE);
                    $getStars = floor($totAVE);
                    $getHStar = round($totAVE - $getStars);
                    echo $getPoints[0];
                    if(!empty($getPoints[1])){
                    */
                    ?><span style="font-size:32px;font-weight:normal;">.<?php //echo $getPoints[1]; ?></span>
                    <?php //} ?>
                    </div>
                    <div style="position:absolute;margin-left:36px;margin-top:-16px;color:goldenrod;">
                    <?php
                    /*
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
              </div>
            </div>
            <!-- /.col -->
            <div class="col-md-9">
              <div class="card card-secondary">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#tabMenu1" data-index="1" data-toggle="tab" data-topbut="1">會員訊息資料</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu2" data-index="2" data-toggle="tab" data-topbut="0">參與課程列表<span title="" class="badge bg-danger" style="position:absolute;margin-top:-10px;"></span></a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu3" data-index="3" data-toggle="tab" data-topbut="0">點數使用紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu4" data-index="4" data-toggle="tab" data-topbut="0">最愛列表</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu5" data-index="5" data-toggle="tab" data-topbut="0">支付紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu6" data-index="6" data-toggle="tab" data-topbut="0">操作紀錄</a></li>
                  </ul>
                </div>
                <!-- /.card-header -->
                <!-- ///////////////////////////////////////////////////////////////////////////////////// -->
                <!-- /////////////////////////////////// 功能分頁  //////////////////////////////////////// -->
                <!-- ///////////////////////////////////////////////////////////////////////////////////// -->
                <!-- ///////////////////////////////////////////////////////////////////////////////////// -->
                <div class="card-body" style="border:1px solid #5F6E86;">
                  <div class="tab-content">
                    <!-- 第1分頁管理者資料修改 -->
                    <div class="tab-pane active" id="tabMenu1">
                      <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/mb_act_func.php" method="post" data-formmode="infoupdate" data-func="func_mb_infoUpdate">
                        <input type="hidden" name="mainimgchk" id="mainimgchk" class="notSubmit" value="<?php echo $mainimgchk; ?>">
                        <div class="card-body">

                          <hr style="border: 1px solid #2C333E"><div class="hr-more">系統資料</div>
                          <div class="form-group row">
                            <label for="mb_status" class="col-sm-2 col-form-label">會員狀態</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="mb_status" name="mb_status" placeholder="會員狀態" data-required<?php echo NdisAble; ?>>
                                  <?php
                                    foreach($mbJS['status'] as $key => $value){
                                        if($key=="") continue;
                                        echo '<option value="'.$key.'"';
                                        if(!empty($res['mb_status']) && $res['mb_status']==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                  ?>
                              </select>
                            </div>
                            <label for="mb_level" class="col-sm-2 col-form-label">會員等級</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="mb_level" name="mb_level" placeholder="會員等級" data-required<?php echo NdisAble; ?>>
                                <?php
                                  foreach($mbJS['levels'] as $key => $value){
                                      if($key=="") continue;
                                      echo '<option value="'.$key.'"';
                                      if(!empty($res['mb_level']) && $res['mb_level']==$key) echo " selected";
                                      echo '>'.$value['title'].'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="mb_cate" class="col-sm-2 col-form-label">會員分類</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="mb_cate" name="mb_cate" placeholder="會員分類" data-required<?php echo NdisAble; ?>>
                                <?php
                                  foreach($mbJS['cate'] as $key => $value){
                                      if($key=="") continue;
                                      echo '<option value="'.$key.'"';
                                      if(!empty($res['mb_cate']) && $res['mb_cate']==$key) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="parent_id" class="col-sm-2 col-form-label">所屬組織</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="parent_id" name="parent_id"<?php echo NdisAble; ?>>
                                <option value="">- 請選擇所屬組織 -</option>
                                ?>
                              </select>
                            </div>
                          </div>

                          <div class="form-group row">
                            <label for="mb_points" class="col-sm-2 col-form-label ">剩餘點數</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control notSubmit" id="mb_points" name="mb_points" value="<?php echo $totalPoints; ?>" placeholder="積點" readonly>
                            </div>
                            <label for="mb_cashback" class="col-sm-2 col-form-label">可返現點數</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control notSubmit" id="mb_cashback" name="mb_cashback" value="<?php echo $totalRedeem; ?>" placeholder="可退" readonly>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="mb_subscribe" class="col-sm-2 col-form-label">刊物訂閱</label>
                            <div class="col-sm-4">
                              <div class="icheck-primary d-inline">
                                <input type="checkbox" id="mb_subscribe" name="mb_subscribe" value="1"<?php if(!empty($res['mb_subscribe']) && intval($res['mb_subscribe']==1)) echo " checked"; ?><?php echo NdisAble; ?>>
                                <label for="mb_subscribe" class="font-weight-light">訂閱</label>
                              </div>
                            </div>
                          </div>
                          

                          <br>
                          <hr style="border: 1px solid #2C333E"><div class="hr-more">基本資料</div>
                          <div class="form-group row">
                            <label for="mb_name" class="col-sm-2 col-form-label">真實姓名</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="mb_name" name="mb_name" value="<?php echo !empty($res['mb_name']) ? _fn_secure_crypto($res['mb_name'], 'de') : '';  ?>" placeholder="真實姓名" data-min="2" data-required<?php echo NreadOnly; ?>>
                            </div>
                            <label for="mb_title" class="col-sm-2 col-form-label">暱稱</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="mb_title" name="mb_title" value="<?php echo !empty($res['mb_title'])?$res['mb_title']:''; ?>" placeholder="暱稱" data-min="2" data-required<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-2 col-form-label">出生</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="text" class="form-control" id="mb_birth" name="mb_birth" data-inputmask-alias="datetime" data-inputmask-inputformat="yyyy-mm-dd" value="<?php echo $res['mb_birth'] ?? ''; ?>" data-mask<?php echo NreadOnly; ?>>
                              </div>
                            </div>
                            <label for="mb_gender" class="col-sm-2 col-form-label">性別</label>
                            <div class="col-sm-4">
                              <div class="icheck-primary d-inline">
                                <input type="radio" id="radioPrimary1" name="mb_gender" value="M"<?php if(!empty($res['mb_gender']) && $res['mb_gender']=="M") echo " checked"; ?><?php echo NdisAble; ?>>
                                <label for="radioPrimary1" class="font-weight-light">男&nbsp;&nbsp;</label>
                              </div>
                              <div class="icheck-primary d-inline">
                                <input type="radio" id="radioPrimary2" name="mb_gender" value="F"<?php if(!empty($res['mb_gender']) && $res['mb_gender']=="F") echo " checked"; ?><?php echo NdisAble; ?>>
                                <label for="radioPrimary2" class="font-weight-light">女</label>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="mb_city" class="col-sm-2 col-form-label">通訊地址</label>
                            <div class="col-sm-3">
                              <select class="form-control" id="mb_city" name="mb_city" placeholder="通訊地址" data-placeholder="-請選擇居住城市-" data-select="city"<?php echo NdisAble; ?>>
                              <option value="">-請選擇居住城市-</option>
                              <?php
                              foreach($locJS as $key => $value) {
                                echo "<option value='".$value['value']."'";
                                if(!empty($res['mb_city']) && $res['mb_city']==$value['value']) { echo " selected"; }
                                echo ">".$value['text']."</option>";
                              }
                              ?>
                              </select>
                            </div>
                            <div class="col-sm-7">
                              <select class="select2" id="mb_district" name="mb_district" placeholder="行政區劃" data-placeholder="-請選擇行政區劃-" dist-fromcity="mb_city" style="width: 100%;"<?php echo NdisAble; ?>>
                              <?php
                              if(!empty($res['mb_city'])) {
                                echo "<option value='0'>-請選擇行政劃區-</option>";
                                foreach($locJS[$res['mb_city']]['district'] as $key => $value){
                                  echo "<option value='".$key."'";
                                  if(!empty($res['mb_district']) && $res['mb_district']==$key) { echo " selected"; }
                                  echo ">".$value."</option>";
                                }
                                //CHARINDEX
                              }
                              ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="mb_add" class="col-sm-2 col-form-label">巷弄街道</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="mb_add" name="mb_add" value="<?php echo !empty($res['mb_add']) ? _fn_secure_crypto($res['mb_add'], 'de') : ''; ?>" placeholder="巷弄街道"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="mb_cellphone" class="col-sm-2 col-form-label">聯絡電話</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="mb_cellphone" name="mb_cellphone" value="<?php echo !empty($res['mb_cellphone']) ? _fn_secure_crypto($res['mb_cellphone'], 'de') : ''; ?>" placeholder="聯絡電話"<?php echo NreadOnly; ?>>
                            </div>
                            <label for="mb_ext" class="col-sm-2 col-form-label">分機</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="mb_ext" name="mb_ext" value="<?php echo !empty($res['mb_ext'])?$res['mb_ext']:''; ?>" placeholder="分機"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="mb_line" class="col-sm-2 col-form-label">Line ID</label>
                            <div class="col-sm-4">
                              <div><input type="text" class="form-control" id="mb_line" name="mb_line" value="<?php echo !empty($res['mb_line']) ? _fn_secure_crypto($res['mb_line'], 'de') : ''; ?>" placeholder="Line ID"<?php echo NreadOnly; ?>></div>
                            </div>
                            <label for="mb_lineuser" class="col-sm-2 col-form-label">LineUser ID</label>
                            <div class="col-sm-4">
                              <div><input type="text" class="form-control" id="mb_lineuser" name="mb_lineuser" value="<?php echo !empty($res['mb_lineuser'])?$res['mb_lineuser']:''; ?>" placeholder="LineUser ID"<?php echo NreadOnly; ?>></div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="mb_email" class="col-sm-2 col-form-label">E-mail</label>
                            <div class="col-sm-10">
                              <div><input type="email" class="form-control" id="mb_email" name="mb_email" value="<?php echo !empty($res['mb_email']) ? _fn_secure_crypto($res['mb_email'], 'de') : ''; ?>" placeholder="E-mail" data-type="email" data-required<?php echo NreadOnly; ?>></div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="mb_remark" class="col-sm-2 col-form-label">會員註記</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="mb_remark" name="mb_remark" rows="3" placeholder="會員註記"<?php echo NreadOnly; ?>><?php echo !empty($res['mb_remark'])?$res['mb_remark']:''; ?></textarea>
                            </div>
                          </div>
                          <br>
                          <hr style="border: 1px solid #5F6E86;"><div class="hr-more">合作型會員資料</div>
                          <div class="form-group row">
                            <label for="mb_cotype" class="col-sm-2 col-form-label">合作種類</label>
                            <div class="col-sm-10 select2-purple">
                              <select class="select2" id="mb_cotype" name="mb_cotype" multiple="multiple" data-placeholder="選擇合作種類" data-dropdown-css-class="select2-purple" style="width: 100%;"<?php
                              if(!empty($res['mb_cate']) && ($res['mb_cate']==1 || $res['mb_cate']==4)){ echo " disabled"; }
                              else echo NdisAble; 
                              ?>>
                                <?php
                                  $cotypeJS=!empty($res['mb_cotype'])?json_decode($res['mb_cotype'],true):array();
                                  foreach($mbJS['cotype'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if(in_array($key, $cotypeJS)) echo " selected";
                                      echo '>'.$value['type'].'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="mb_coexpert" class="col-sm-2 col-form-label">專業服務項目</label>
                            <div class="col-sm-10 select2-purple">
                              <select class="select2" id="mb_coexpert" name="mb_coexpert" multiple="multiple" data-placeholder="選擇專業服務項目" data-dropdown-css-class="select2-purple" style="width: 100%;"<?php
                              if(!empty($res['mb_cate']) && ($res['mb_cate']==1 || $res['mb_cate']==4)){ echo " disabled"; }
                              else echo NdisAble; 
                              ?>><?php
                                  $coexJS=!empty($res['mb_coexpert']) ? json_decode($res['mb_coexpert']) : array();
                                  foreach($mbJS['cotype'] as $key => $value){
                                    if(in_array($key, $cotypeJS)) {
                                      echo '<optgroup label="-- '.$value['type'].' --">';
                                      foreach($mbJS['cotype'][$key]['expert'] as $ekey => $evalue){
                                        echo '<option value="'.$ekey.'"';
                                        if(in_array($ekey, $coexJS)) echo " selected";
                                        echo '>'.$evalue.'</option>';
                                      }
                                    }
                                  }
                                ?></select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="mb_remark" class="col-sm-2 col-form-label">管理者註記</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="mb_remark" name="mb_remark" rows="5" placeholder="管理者註記"<?php echo NreadOnly; ?>><?php echo !empty($res['mb_remark'])?$res['mb_remark']:''; ?></textarea>
                            </div>
                          </div>
                          <hr>
                        </div>
                        <!-- /.card-body -->
                      </form>
                    </div>
                    <!-- /第1分頁管理者資料修改 -->
                    <!-- 第2分頁參與課程列表 -->
                    <div class="tab-pane<?php echo (empty($mbID)) ? " invisible" : ""; ?>" id="tabMenu2">
                      <!-- card-body -->
                      <div class="card-body" style="text-align:center;">
                        <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                        <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;管理者於此可得知會員登錄的課程商品狀況，點選課程商品圖片可進入課程商品編輯頁面，編輯完成後可按返回鍵跳轉回此頁。</div>
                        <hr style="border: 1px solid #2C333E"><div class="hr-more">上架中課程商品</div>
                        <!--Classes List -->
                        <!-- /Classes List -->
                      </div>
                      <!-- /.card-body -->
                      <div>&nbsp;</div>
                    </div>
                    <!-- /第2分頁參與課程列表 -->
                    <!-- 第3分頁點數使用紀錄 -->
                    <div class="tab-pane" id="tabMenu3">
                      <!-- card-body -->
                      <div class="card-body">
                        <div style="font-size:0.85em;line-height:1.4em;">
                        <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;此表為會員付費購買點數的記錄，由於有價交易涉及法律問題，此表原則上僅供瀏覽查驗使用，不可隨意更改，若是系統造成的錯誤必須修正數值時，可以以補加或是補減的方式調整校正。</div>
                        <hr>
                        <table id="tdTableList" class="table table-bordered table-striped table-hover">
                          <thead>
                            <tr>
                              <th>日期</th>
                              <th>類別</th>
                              <th>點數</th>
                              <th>用途</th>
                              <th>關聯對象</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                              $sqlp="SELECT * FROM mb_points WHERE mb_id=? AND mbp_status>?";
                              $srp = $DB->query($sqlp,array($mbID,0));
                              $i = 0; //以$i為$nameList這個陣列的索引值
                              while($i<count($srp)){
                            ?>
                                <tr style="background-color:<?php
                                if($srp[$i]['mbp_trade']>0) echo "#D4EDE0";
                                else echo "#FFFFFF";
                                ?>;">
                                  <td><?php echo $srp[$i]['mbp_date'] ?? '&nbsp;'; ?></td>
                                  <td><?php
                                    echo $mbJS['ptstatus'][$srp[$i]['mbp_status']] ?? '&nbsp;';
                                    /*
                                    if($srp[$i]['mbp_trade']>0 && $srp[$i]['mbp_for']==0) echo "[儲值]";
                                    else {
                                      echo '<span class="right badge badge-dark">'; 
                                      switch($srp[$i]['mbp_status']){
                                        case '2': echo "預扣"; break;
                                        case '9': echo "凍結"; break;
                                        default: echo ""; break;
                                      }
                                      echo '</span> ';
                                      echo $geJS['idfor'][$srp[$i]['mbp_for']]['short'];
                                    }
                                    */
                                  ?></td>
                                  <td>
                                    <?php
                                      $tIncome=$srp[$i]['mbp_income'];
                                      if($tIncome==1) echo '<i class="nav-icon fas fa-long-arrow-alt-up" style="color:#44a273;"></i>&nbsp;<b>+'.$srp[$i]['mbp_trade'].'</b>';
                                      else echo '<i class="nav-icon fas fa-long-arrow-alt-down" style="color:#C35050;"></i>&nbsp;<b>'.$srp[$i]['mbp_trade'].'</b>';
                                    ?>
                                  </td>
                                  <td><?php
                                      $getType = $srp[$i]['mbp_type'];
                                      if($srp[$i]['mbp_for']==4){
                                        $sql="SELECT sv_type, sv_title FROM sv_service WHERE sv_id=?";
                                        $cr = $DB->row($sql,array($srp[$i]['mbp_forid']));
                                        echo $getType = $cr['sv_type'] ?? 0;
                                      }
                                      if(!empty($getType)) {
                                        $getMBPtype = $geJS['idfor'][$srp[$i]['mbp_for']]['otype'][$getType] ?? [];
                                        echo $getMBPtype && !is_array($getMBPtype) ? $getMBPtype : $geJS['idfor'][$srp[$i]['mbp_for']]['short'];
                                      } else {
                                        echo "&nbsp;";
                                      }
                                        
                                      ?>
                                  </td>
                                  <td>
                                    <?php
                                      $getForInfo = $geJS['idfor'][$srp[$i]['mbp_for']] ?? [];//紀錄
                                      $getFromInfo = $geJS['idfor'][$getForInfo['joinid'] ?? null] ?? [];//項目
                                      $idforTitle = $DB->getIDFORtitle($srp[$i]['mbp_forid'],$getForInfo,$getFromInfo);
                                      echo $idforTitle;
                                      ?><i class="nav-icon fas fa-sign-out-alt" style="position:absolute;right:12px;"></i>
                                    </td>
                                </tr>
                            <?php $i++; } ?>
                          </tbody>
                          <tfoot>
                            <tr>
                              <th>日期</th>
                              <th>類別</th>
                              <th>點數</th>
                              <th>用途</th>
                              <th>關聯對象</th>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第3分頁點數使用紀錄 -->
                    <!-- 第4分頁最愛列表 -->
                    <div class="tab-pane" id="tabMenu4">
                      <div class="card-body">
                        <div style="font-size:0.85em;line-height:1.4em;"><i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;本會員的我的最愛列表，包含課程商品、文章、OpenService...等。</div>
                        <hr>
                        <table id="wlTableList" class="table table-bordered table-striped table-hover">
                          <thead>
                            <tr>
                              <th>種類</th>
                              <th>編號</th>
                              <th>建立時間</th>
                              <th>項目名稱</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                              $sql="SELECT wl_created,wl_for,wl_forid FROM data_wishlist WHERE mb_id=?";//wishlist
                              $srr = $DB->query($sql,array($mbID));
                              $i = 0;
                              while($i<count($srr)){ 
                                $mRes = $DB->row("SELECT merch_sn,merch_title FROM merchandise WHERE merch_id=?",array($srr[$i]['wl_forid']));
                                ?>
                                <tr>
                                  <td><?php echo $geJS['idfor'][$srr[$i]['wl_for']]['short'] ?? '&nbsp;'; ?></td>
                                  <td><?php echo $mRes['merch_sn'] ?? '&nbsp;'; ?></td>
                                  <td><?php echo $srr[$i]['wl_created'] ?? '&nbsp;'; ?></td>
                                  <td><?php echo $mRes['merch_title'] ?? '&nbsp;'; ?></td>
                                </tr>
                            <?php 
                                $i++;
                                } 
                              ?>
                          </tbody>
                          <tfoot>
                            <tr>
                              <th>種類</th>
                              <th>編號</th>
                              <th>建立時間</th>
                              <th>項目名稱</th>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                      <!-- /.card-body -->

                    </div>
                    <!-- /第4分頁最愛列表 -->
                    <!-- 第5分頁支付紀錄 -->
                    <div class="tab-pane<?php echo (empty($mbID)) ? " invisible" : ""; ?>" id="tabMenu5">
                      <!-- card-body -->
                      <div class="card-body" style="text-align:center;">
                        <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                        <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;xxxx。</div>
                        <hr style="border: 1px solid #2C333E"><div class="hr-more">支付紀錄</div>
                        <!--Classes List -->
                        <!-- /Classes List -->
                      </div>
                      <!-- /.card-body -->
                      <div>&nbsp;</div>
                    </div>
                    <!-- /第5分頁支付紀錄 -->
                    <!-- 第6分頁操作紀錄 -->
                    <div class="tab-pane" id="tabMenu6">
                      <!-- card-body -->
                      <div class="card-body">
                        <div style="font-size:0.85em;line-height:1.4em;">
                        <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;本會員的中庸本系統的操作記錄列表，本列表用於偵錯、改進、分析甚至於究責，所以一旦寫入記錄便無法更改，操作記錄將於多處以及多型態備份。</div>
                        <hr>
                        <table id="loTableList" class="table table-bordered table-striped table-hover">
                          <thead>
                            <tr>
                              <th>代碼</th>
                              <th>操作日期時間</th>
                              <th>操作內容</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                              $sql="SELECT * FROM data_logs WHERE lg_forid=? AND lg_for=?";
                              $srr = $DB->query($sql,array($mbID,2));
                              $i = 0; //以$i為$nameList這個陣列的索引值
                              while($i<count($srr)){ 
                                ?>
                                <tr>
                                  <td><?php echo $srr[$i]['lg_code']; ?></td>
                                  <td><?php echo $srr[$i]['lg_created']; ?></td>
                                  <td><?php
                                  $oStr = $DB->single("SELECT lg_string FROM sys_logstrings WHERE lg_code=?",array($srr[$i]['lg_code']));
                                  echo _fn_getLogString($srr[$i]['lg_params'],$oStr);
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
                    <!-- /第6分頁操作紀錄 -->
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
<script src="../pluginFiles/datatables/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../pluginFiles/datatables/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
-->
<!-- DataTables3 & Plugins -->
<script src="../pluginFiles/datatables3/datatables.min.js"></script>
<!-- Select2 -->
<script src="../pluginFiles/select2/js/select2.full.min.js"></script>
<!-- InputMask 日期輸入元件JS -->
<script src="../pluginFiles/moment/moment.min.js"></script>
<script src="../pluginFiles/inputmask/jquery.inputmask.min.js"></script>
<!-- ChartJS -->
<script src="../pluginFiles/chart.js/Chart.min.js"></script>
<!-- jquery-validation 
<script src="../pluginFiles/jquery-validation/jquery.validate.min.js"></script>
<script src="../pluginFiles/jquery-validation/localization/messages_zh_TW.min.js"></script>
<script src="../pluginFiles/jquery-validation/additional-methods.min.js"></script>
-->
<!-- ezValidation -->
<script src="../pluginFiles/validator/validator.js<?php echo '?t='.time(); ?>"></script>
<!-- Toastr 移往外框
<script src="../pluginFiles/toastr/toastr.min.js"></script> -->
<!-- Dropzone JS -->
<script src="../pluginFiles/dropzone/dropzone.js"></script>
<!-- Cropper JS -->
<script src="../pluginFiles/cropper/cropper.js"></script>
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
//轉換會員選項為陣列，並宣告為全域變數
var mbObj = <?php echo json_encode($mbJS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
////////////////////////////////////////////////////////////////
// -------------- JAVASCRIPTS FUNCTIONs 全域調用 -------------- //
// -------------- 可接收include檔案要求後自定義動作 -------------- //
////////////////////////////////////////////////////////////////
function doReDirect(){
    window.history.back();
}
// 當「儲存」按鈕按下時的動作
function doButSave(){
  //var frm = $('#form_'+$('.tab-pane.active').prop("id"));
	//var accE = frm.data('func');
  var frm = $('#form_'+$('.tab-pane.active').prop("id"));
  var accE = frm.data('func');

  if(accE=='func_mb_infoUpdate'){
    var getValid = Validator.validate(frm);
    if(!getValid){
      return false;
    } else {
      ajaxPOST(frm);
    }
  }else if(accE=='func_mb_needUpdate'){
    needPOST(frm);
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
      url: '../funcFiles/mb_act_func.php',
      data: {
        'arrDatA': {}, 
        'formAcce':'func_mb_delete',
        'token': getTokenVal(), 
        'mbID': parseInt('<?php echo $mbID; ?>')
      },
      dataType: 'json',
      async: false, 
    success: function (data) {
        if(data['RTN']==1){
          window.location.replace("mem_mblist.php");
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

/////////////////////////// AJAX FORM POST Send ///////////////////////////     
function ajaxPOST(frm){
        var url = frm.prop('action');
        var typE = frm.prop('method');
        var frm = $('#form_'+$('.tab-pane.active').prop("id"));
        var accE = frm.data('func');
        var datA;datB;
        var imgBase64 = $('#main_crop_area').data('cropImage64') || '';
        if(0=='<?php echo $mbID; ?>') { accE = 'func_mb_addnew'; }
        else {
          var datA = {
              parent_id : $('#parent_id').val(),
              mb_level : $('#mb_level').val(),
              mb_org : $("input[name=mb_org]:checked").val(),
              mb_name : $('#mb_name').val(),
              mb_title : $('#mb_title').val(),
              mb_birth : $('#mb_birth').val(),
              mb_gender : $("input[name=mb_gender]:checked").val(),
              mb_city : $('#mb_city').val(),
              mb_district : $('#mb_district').val(),
              mb_add : $('#mb_add').val(),//AES
              mb_cellphone : $('#mb_cellphone').val(),//AES
              mb_ext : $('#mb_ext').val(),
              mb_line : $('#mb_line').val(),//AES
              mb_lineuser : $('#mb_lineuser').val(),//AES
              mb_google : $('#mb_google').val(),
              mb_email : $('#mb_email').val(),//AES
              mb_remark : $('#mb_remark').val(),
              mb_adminremark : $('#mb_adminremark').val()
          };
          var datB = {
              mb_cate : $('#mb_cate').val(),
              mb_cotype : arrayTOstring($('#mb_cotype').val()),
              mb_coexpert : arrayTOstring($('#mb_coexpert').val()),
              mb_merchjoint : $('#mb_merchjoint').is(':checked') ? 1 : 0,
              mb_speccity : $('#mb_speccity').val(),
              mb_specarea : arrayTOstring($('#mb_specarea').val()),
              mb_speccomm : arrayTOstring($('#mb_speccomm').val()),
              mb_company : $('#mb_company').val(),
              mb_ccity : $('#mb_ccity').val(),
              mb_cdistrict : $('#mb_cdistrict').val(),
              mb_cadd : $('#mb_cadd').val(),//AES
              mb_ctel : $('#mb_ctel').val(),
              mb_fb : $('#mb_fb').val(),//AES
              mb_url : $('#mb_url').val(),
              mb_ig : $('#mb_ig').val(),
              mb_dialog : $('#mb_dialog').is(':checked') ? 1 : 0,
              mb_consult : $('#mb_consult').is(':checked') ? 1 : 0,
              mb_openhouse : $('#mb_openhouse').is(':checked') ? 1 : 0,
              mb_live : $('#mb_live').is(':checked') ? 1 : 0,
              mb_subscribe : $('#mb_subscribe').is(':checked') ? 1 : 0,
              mb_points : $('#mb_points').val(),
              mb_cashback : $('#mb_cashback').val(),
              mb_des : $('#mb_des').val(),
              mb_status : $("input[name=mb_status]:checked").val()
          };
        }

        $.ajax({
            type: typE,
            url: url,
            data: {
              'arrDatA':datA, 
              'arrDatB':datB, 
              'formAcce':accE,
              'imgUp': imgBase64,
              'token': getTokenVal(), 
              'mbID':'<?php echo $mbID; ?>'
            },
            dataType: 'json',//data format  
            async: false, 
          success: function (data) {
              if(data['RTN']==0){
                $('#form_tabMenu1')[0].reset();
              }
              else{
                if(data['RTN']==1){
                  var newSta = $('#mb_status').val();
                  switch (newSta){
                    case '0': 
                      $('.box-profile > ul > li:nth-child(1) > a').addClass( "text-danger" );//newSta='停權中';
                    break;
                    case '1': 
                      $('.box-profile > ul > li:nth-child(1) > a').removeClass( "text-danger" );//newSta='正常授權';
                    break;
                  }
                  var ms = mbObj['status'][newSta];
                  $('.profile-username').html($('#mb_title').val());
                  $('.profile-username + p').html($("#mb_cate option:selected").text());
                  $('.box-profile > ul > li:nth-child(1) > a').html(ms);
                  $('.box-profile > ul > li:nth-child(2) > a').html(datA['upTime']);
                  ORGForm = CHGform.serialize();
                  window.parent.$('#topBut_save').removeClass('active');
                } else if(data['RTN']==2){
                  window.location.replace("mem_profile.php?currID="+data['newID']);
                  return true;
                }
              }
              window.parent.toastr[data['msgType']](data['msgStr']);
              console.log(data);
            },
            error: function (data) {
              console.log(data);
              window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
            }
        });
    }

    function needPOST(frm){
        var url = frm.prop('action');
        var typE = frm.prop('method');
        var frm = $('#form_'+$('.tab-pane.active').prop("id"));
        var accE = frm.data('func');
        var rsoptionsARR = [];prstatusARR = [];orientationARR = [];
        $.each($("input[name='mbn_rsoptions']:checked"), function(){
          rsoptionsARR.push($(this).val());
        });
        $.each($("input[name='mbn_prstatus']:checked"), function(){
          prstatusARR.push($(this).val());
        });
        $.each($("input[name='mbn_orientation']:checked"), function(){
          orientationARR.push($(this).val());
        });
        var needARR={
          city: !$('#mbn_city').val() ? 0 : parseInt($('#mbn_city').val()),
          district: $('#mbn_district').val(),
          comm: $('#mbn_comm').val(),
          rsoptions: rsoptionsARR,
          mdtype: $('#mbn_mdtype').val(),
          prstatus: prstatusARR,
          finish: [!$('#mbn_finish_min').val() ? 0 : parseInt($('#mbn_finish_min').val()),!$('#mbn_finish_max').val() ? 0 : parseInt($('#mbn_finish_max').val())],
          usage: $('#mbn_usage').val(),
          bdtype: $('#mbn_bdtype').val(),
          saleprice: [!$('#mbn_saleprice_min').val() ? 0 : parseInt($('#mbn_saleprice_min').val()),!$('#mbn_saleprice_max').val() ? 0 : parseInt($('#mbn_saleprice_max').val())],
          ownparking: !$('#mbn_ownparking').val() ? 0 : parseInt($('#mbn_ownparking').val()),
          rentparking: !$('#mbn_rentparking').val() ? 0 : parseInt($('#mbn_rentparking').val()),
          certarea: [!$('#mbn_certarea_min').val() ? 0 : parseInt($('#mbn_certarea_min').val()),!$('#mbn_certarea_max').val() ? 0 : parseInt($('#mbn_certarea_max').val())],
          pubratio: [!$('#mbn_pubratio_min').val() ? 0 : parseInt($('#mbn_pubratio_min').val()),!$('#mbn_pubratio_max').val() ? 0 : parseInt($('#mbn_pubratio_max').val())],
          orientation: orientationARR,
          room: [!$('#mbn_room_min').val() ? 0 : parseInt($('#mbn_room_min').val()),!$('#mbn_room_max').val() ? 0 : parseInt($('#mbn_room_max').val())],
          hall: [!$('#mbn_hall_min').val() ? 0 : parseInt($('#mbn_hall_min').val()),!$('#mbn_hall_max').val() ? 0 : parseInt($('#mbn_hall_max').val())],
          bath: [!$('#mbn_bath_min').val() ? 0 : parseInt($('#mbn_bath_min').val()),!$('#mbn_bath_max').val() ? 0 : parseInt($('#mbn_bath_max').val())],
          furniture: $('#mbn_furniture').val(),
          amenities: $('#mbn_amenities').val(),
          traffic: $('#mbn_traffic').val(),
          schools: $('#mbn_schools').val()
        };
        $.ajax({
            type: typE,
            url: url,
            data: {
              'arrNeed': needARR, 
              'formAcce':accE, 
              'token': getTokenVal(), 
              'mbID':'<?php echo $mbID; ?>'
            },
            dataType: 'json',//data format  
            async: false, 
          success: function (data) {
              console.log(data);
              //toastr[data['msgType']](data['msgStr']);
              window.parent.toastr[data['msgType']](data['msgStr']);
              //ORGForm = CHGform.serialize();
              //window.parent.$('#topBut_save').removeClass('active');
            },
            error: function (data) {
              console.log(data);
              //window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
              window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
            }
        });
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
        }c
      });
      // --------------- 當改變合作方種類選項時 ------------------
      $('#mb_cotype').on('change', function(){
        var typId = $(this).val();//Array
        var expId = $('#mb_coexpert').val();//Array
        var ct = mbObj['cotype'];
        $('#mb_coexpert').html('');
        for(var i=0; i<typId.length; i++){
          $('#mb_coexpert').append('<optgroup label="-- '+ ct[typId[i]]['type'] +' --">');
          $.each(ct[typId[i]]['expert'], function( index, value ) {
            var appendHTML = '<option value="' + index + '"';
            if ($.inArray(index, expId) >= 0) { appendHTML += ' selected'; }
            appendHTML += '>'+ value +'</option>';
            $('#mb_coexpert').append(appendHTML);
          });
        }
      });
      /////////////////////////////////////////////////////////////////////////
      //////////////////// SELECT2 複選元件初始化 //////////////////
      $('.select2').select2();
      //////////////////// DATEMASK 日期輸入元件初始化 //////////////
      $('#datemask2').inputmask('yyyy-mm-dd', { 'placeholder': 'yyyy-mm-dd' })
      $('[data-mask]').inputmask();

  //////////////////////////////////////////////////////////////////
  /////////////////////////// 各式按鈕行為 ///////////////////////////     
  //////////////////////////////////////////////////////////////////

    $("input[name='mb_org']").change(function(e){
        var method =$(this).val(); //radio 取值，注意寫法
        if(method==1){
          $('#tabMenu10,[href$="#tabMenu10"]').removeClass( 'invisible' );
          $('#orgShow').addClass( "fa-users" );
        } else{
          $('#tabMenu10,[href$="#tabMenu10"]').addClass( "invisible" );
          $('#orgShow').removeClass( "fa-users" );
        }
      });

    $('#mb_cate').on('change', function(){
      if($(this).val()==1 || $(this).val()==4){
        $('#mb_cotype,#mb_coexpert').prop('disabled', 'disabled');
      } else {
        $('#mb_cotype,#mb_coexpert').removeAttr("disabled");
      }
    });
    // 內頁右上方小導航返回列表頁
    $('li.prevLink').on('click', function(){
      window.location.replace(getCurrentURI('','mem_mblist.php'));
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
