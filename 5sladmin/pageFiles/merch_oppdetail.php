<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_OPPLIST');
define('PAGE_ITEM','ITEM_MERCH');
define('FORMEDIT_TOPAUTH', true);//部份form元件限制編輯操作
//顯示「返回」按鈕
define('bkRedir',1);
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//
$oppID=currID;
//取得頁面資料
$res = $DB->row("SELECT * FROM merch_oppurchase WHERE opp_id=?",array($oppID));
//$DB->closeConnection();
//代表圖像設定
if(!empty($oppID)&&file_exists("../uploadFiles/md/opp/oppphoto_".$oppID."_thumb.jpg")){
  $cropImgDisplay = 'show';
  $mainimgpath = '../uploadFiles/md/opp/oppphoto_'.$oppID.'_thumb.jpg?'.time();
  $mainimgchk = 'oppphoto_'.$oppID.'_.jpg';
} else {
  $cropImgDisplay = 'hide';
  $mainimgpath = '../uploadFiles/imageFiles/nopic_400x300.jpg';
  $mainimgchk = "";
}
//功能陣列建立
// Get the contents of the JSON file
$mdJS=merchJS("../jsonFiles/merchandise.json");
$geJS=merchJS("../jsonFiles/general.json");
//--------------------- 評分計算 -----------------------
$idForItem = 6;//對應 general.json["idfor"] ## 1-會員(廠商、專家…等)；2-課程商品；3-文章;4-服務；5-廣告套裝；6-加購服務；11-儲值紀錄；12-廣告紀錄；13-加購紀錄
$counT="SELECT AVG((rat_s1 + rat_s2 + rat_s3 + rat_s4 + rat_s5)/5) AS totAVE, AVG(rat_s1) AS S1, AVG(rat_s2) AS S2, AVG(rat_s3) AS S3, AVG(rat_s4) AS S4, AVG(rat_s5) AS S5 FROM fsl_rating WHERE rat_forid=? AND rat_for=?";
$cnt = $DB->query($counT,array($oppID,$idForItem));
$S1 = !empty($cnt[0]['S1']) ? floatval($cnt[0]['S1']) : 0;
$S2 = !empty($cnt[0]['S2']) ? floatval($cnt[0]['S2']) : 0;
$S3 = !empty($cnt[0]['S3']) ? floatval($cnt[0]['S3']) : 0;
$S4 = !empty($cnt[0]['S4']) ? floatval($cnt[0]['S4']) : 0;
$S5 = !empty($cnt[0]['S5']) ? floatval($cnt[0]['S5']) : 0;
$totAVE = !empty($cnt[0]['totAVE']) ? floatval($cnt[0]['totAVE']) : 0;
$ratingItem = $geJS['idfor'][$idForItem]['rating']['1'].','.$geJS['idfor'][$idForItem]['rating']['2'].','.$geJS['idfor'][$idForItem]['rating']['3'].','.$geJS['idfor'][$idForItem]['rating']['4'].','.$geJS['idfor'][$idForItem]['rating']['5'];
$ratingData = $S1.','.$S2.','.$S3.','.$S4.','.$S5;
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
	
  <title> Fifth Season Life 人生第五季 | 課程商品加購項目管理資料 </title>
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
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Dropzone JS style -->
  <link rel="stylesheet" href="../pluginFiles/dropzone/dropzone.css" />
  <!-- Cropper JS style -->
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <!-- suneditor -->
  <link rel="stylesheet" href="../pluginFiles/suneditor/dist/css/suneditor.min.css<?php echo '?t='.time(); ?>">
  <!-- codeMirror -->
  <link rel="stylesheet" href="../pluginFiles/codemirror/lib/codemirror.min.css<?php echo '?t='.time(); ?>">
    <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css<?php echo '?t='.time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.lightbox.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css<?php echo '?t='.time(); ?>">

<!-- -------------------------- /CSS Link -------------------------- -->
<style>
/*
  .processingBut{
    cursor: pointer;
  }
*/
</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <!-- processing waiting -->
  <div id="loadingMask" style="top:0;left:0;position:absolute;display:block;width:100%;height:100%;background-color:#2A2826;background-position: 50% 0;opacity: 0.75;margin:0;text-align: center; z-index:99999;display:none;">
    <div style="color:#FFFFFF;position: relative;top: 50%;transform: translate(0, -50%);">
      <img src="../imageFiles/loading2.gif" style="left:50%;top:50%;"><br> 操作進行中，請稍候...
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
              <h4 class="m-0"><?php echo !empty($oppID) ? "編輯" : "新增"; ?>課程商品加購項目管理<span style="font-size:large;">【ID: <?php echo $oppID; ?>】</span></h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                  <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                  <li class="breadcrumb-item">課程商品加購項目管理</li>
                  <li class="breadcrumb-item prevLink" alt="返回列表" title="返回列表">課程商品加購項目紀錄</a></li>
                <li class="breadcrumb-item active">課程商品加購項目詳細資料</li>
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
                      <i class="nav-icon fas fa-2x fa-trash-can cropImgTrash<?php echo " ".$cropImgDisplay; ?>" alt="刪除圖像" title="刪除圖像"></i>
                      <form method="post">
                        <label for="cropUploadImage">
                        <img id="avatarImage" name="avatarImage" class="img-responsive img-fluid img-rounded imgEDicon" src="<?php echo $mainimgpath; ?>" alt="文章代表圖" title="文章代表圖"/>
                          <!-- <div id="changeProfilePhoto" class="fa fa-2x fa-camera imgEDiconLi" data-toggle="modal" data-target="#changeProfilePhotoModal" alt="編輯頭像" title="編輯頭像"></div> -->
                          <div class="overlay">
                            <div class="overtext"><?php echo !empty($oppID) ? "更改" : "新增"; ?>代表圖</div>
                          </div>
                          <input type="file" name="image" class="image" id="cropUploadImage" style="display:none" crop-width="800" crop-height="600">
                        </label>
                      </form>
                    </div>
                  </div>
                  <h5 class="profile-title text-center"><?php echo !empty($res['opp_title']) ? $res['opp_title'] : "&nbsp;"; ?></h5>
                  <p class="text-muted text-center"><?php echo !empty($res['opp_subscript']) ? $res['opp_subscript'] : "&nbsp;"; ?></p>

                  <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                      <b>類別</b> <a class="float-right"><?php echo !empty($res['opp_type']) ? $mdJS['opptype'][$res['opp_type']] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>模式</b> <a class="float-right"><?php echo !empty($res['opp_mode']) ? $mdJS['oppmode'][$res['opp_mode']] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>編輯日期</b> <a class="float-right"><?php echo !empty($res['opp_published']) ? $res['opp_published'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>建立日期</b> <a class="float-right" style="color:black;"><b><?php echo !empty($res['opp_updated']) ? $res['opp_updated'] : "&nbsp;"; ?></b></a>
                    </li>
                    <li class="list-group-item">
                      <b>狀態</b> <a class="float-right<?php echo (!is_null(@$res['opp_status']) && $res['opp_status']==0) ? " text-danger" : (!is_null(@$res['opp_status']) && $res['opp_status']>=1 ? " text-success" : " text-warning"); ?>"><?php echo !is_null(@$res['opp_status']) && @$res['opp_status']!='' ? $mdJS['oppstatus'][$res['opp_status']] : "&nbsp;"; ?></a>
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
                <div class="card-body box-profile">
                    <div style="position:absolute;font-size:60px;font-weight:bold;margin-top:-36px;">
                    <?PHP
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
                    ?>
                    </div>
                    <canvas id="radarChart"></canvas>
                </div>
              </div>
              <!-- /Rating -->

            </div>
            <!-- /.col -->
            <div class="col-md-9">
              <div class="card card-secondary">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#tabMenu1" data-index="1" data-toggle="tab" data-topbut="1">加購項目資料修改</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu2" data-index="2" data-toggle="tab" data-topbut="1">加購項目介紹頁編輯</a></li>
                    <?php if (!empty($oppID)) { ?>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu3" data-index="3" data-toggle="tab" data-topbut="0">項目使用紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu4" data-index="4" data-toggle="tab" data-topbut="0">評價紀錄</a></li>
                    <?php } ?>
                  </ul>
                </div><!-- /.card-header -->
                <div class="card-body">
                  <div class="tab-content">
                    <!-- 第一分頁項目資料修改 -->
                    <div class="tab-pane active" id="tabMenu1">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;部份輸入使用HTML編輯器編輯內容，可以美化文字，也可以嵌入超連結，請注意，編輯器要先內部儲存後，本頁面的更新才能有效儲存新的編輯資料。</div>
                      <hr>
                      <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/md_act_func.php" method="post" data-formmode="infoupdate" data-func="func_opp_infoUpdate">
                        <input type="hidden" name="mainimgchk" id="mainimgchk" class="notSubmit" value="<?php echo $mainimgchk; ?>">
                        <div class="card-body">

                          <div class="form-group row">
                            <label for="opp_type" class="col-sm-2 col-form-label">項目種類</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="opp_type" name="opp_type" placeholder="項目種類" data-required<?php echo NdisAble; ?>>
                                  <?php
                                    foreach($mdJS['opptype'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(!empty($res['opp_type']) && $res['opp_type']==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                  ?>
                                </select>
                            </div>
                            <label for="opp_keycode" class="col-sm-2 col-form-label">指定代碼</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="opp_keycode" name="opp_keycode" value="<?php echo !empty($res['opp_keycode']) ? $res['opp_keycode'] : ""; ?>" placeholder="指定代碼" data-min="4" data-max="12" data-type="keycode" data-required<?php echo !empty($oppID) ? " readonly" :  NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <div class="col-sm-12" style="font-size:0.85em;line-height:1.4em;text-align:right;">
                              <i class="nav-icon fas fa fa-exclamation-circle"></i>&nbsp;&nbsp;基於相關紀錄溯源的準確性，代碼一經設定後便無法更改。
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="opp_title" class="col-sm-2 col-form-label">項目抬頭</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="opp_title" name="opp_title" value="<?php echo !empty($res['opp_title']) ? $res['opp_title'] : ""; ?>" placeholder="項目抬頭" data-min="4" data-required<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="opp_subscript" class="col-sm-2 col-form-label">項目簡稱(下標)</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="opp_subscript" name="opp_subscript" value="<?php echo !empty($res['opp_subscript']) ? $res['opp_subscript'] : ""; ?>" placeholder="下標" data-min="2" data-required<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="opp_brief" class="col-sm-2 col-form-label">項目簡述</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="opp_brief" name="opp_brief" rows="4" placeholder="項目簡述"<?php echo NreadOnly; ?>><?php echo !empty($res['opp_brief']) ? stripslashes($res['opp_brief']) : ""; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="opp_mode" class="col-sm-2 col-form-label">項目模式</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="opp_mode" name="opp_mode" placeholder="項目模式" data-required<?php echo NdisAble; ?>>
                                <?php
                                  foreach($mdJS['oppmode'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(!empty($res['opp_mode']) && $res['opp_mode']==$key) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                            <label for="opp_notify" class="col-sm-2 col-form-label">通知管理人員</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="opp_notify" name="opp_notify" placeholder="通知管理人員" data-required<?php echo NdisAble; ?>>
                                <?php
                                  foreach($mdJS['oppnotify'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(!empty($res['opp_notify']) && $res['opp_notify']==$key) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="opp_cost" class="col-sm-2 col-form-label">花費點數</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="opp_cost" name="opp_cost" value="<?php echo !empty($res['opp_cost']) ? $res['opp_cost'] : "0"; ?>" placeholder="花費點數" data-type="number"<?php echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">點</span>
                                </div>
                              </div>
                            </div>
                            <label for="opp_days" class="col-sm-2 col-form-label">有效期限</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="opp_days" name="opp_days" value="<?php echo !empty($res['opp_days']) ? $res['opp_days'] : "0"; ?>" placeholder="有效期限" data-type="number"<?php echo !empty($res['opp_mode']) && $res['opp_mode']==2 ? ' readonly' : NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text areaUnit">天</span>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <div style="text-align:right;font-size:0.85em;line-height:1.4em;width:100%;">
                              <i class="nav-icon fas fa fa-exclamation-circle"></i>&nbsp;&nbsp;本欄若為0，則為計次服務或與相關刊登期限相同起訖。
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="opp_trigger" class="col-sm-2 col-form-label">功能觸發代碼</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="opp_trigger" name="opp_trigger" rows="5" placeholder="功能觸發代碼" data-min="2"<?php echo TreadOnly; ?>><?php echo @$res['opp_trigger']; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <div style="text-align:right;font-size:0.85em;line-height:1.4em;width:100%;">
                              <i class="nav-icon fas fa fa-exclamation-circle"></i>&nbsp;&nbsp;功能觸發代碼僅開放給被授權的高級或以上的工程人員編輯操作。
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="opp_rating" class="col-sm-2 col-form-label">項目評分</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="opp_rating" name="opp_rating" value="<?php echo !empty($res['opp_rating'])?$res['opp_rating']:"0"; ?>" placeholder="項目評分" disabled>
                            </div>
                            <label for="opp_status" class="col-sm-2 col-form-label">項目狀態</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="opp_status" name="opp_status" data-required<?php echo NdisAble; ?>>
                                <?php
                                  foreach($mdJS['oppstatus'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(@!is_null($res['opp_status']) && @$res['opp_status']==$key) echo " selected";
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
                    
                    <!-- /第一分頁項目資料修改 -->
                    <!-- 第二分頁文章主體編輯 -->
                    <div class="tab-pane" id="tabMenu2">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                      <i class="nav-icon fas fa fa-exclamation-circle"></i>&nbsp;&nbsp;Html 編輯器使用 SunEditor 2.46.3 版本，並依照 MIT 授權條款內容行使權利及義務。</div>
                      <hr>
                      <form id="form_tabMenu2" name="form_tabMenu2" action="../funcFiles/md_act_func.php" method="post" data-formmode="htmlupdate" data-func="func_opp_infoUpdate">
                        <div class="card-body">
                          <textarea name="opp_htmlbody" id="opp_htmlbody"><?php echo !empty($res['opp_htmlbody']) ? stripslashes($res['opp_htmlbody']): ""; ?></textarea>
                        </div>
                      </form>  
                    </div>
                    <!-- /第二分頁文章主體編輯 -->
                    <?php if (!empty($oppID)) { ?>
                    <!-- 第三分頁項目使用紀錄 -->
                    <div class="tab-pane" id="tabMenu3">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;XXXXX</div>
                      <hr>
                      <!-- /.card-header -->
                      <div class="card-body">
                        <table id="dataTable_tradelist" class="table table-bordered table-striped table-hover" data-pagemax="25" data-search>
                          <thead>
                            <tr>
                              <th>申購日期時間</th>
                              <th>生效日期時間</th>
                              <th>有效期限</th>
                              <th>申購會員</th>
                              <th>已使用點數</th>
                              <th>執行狀態</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                              $sql="SELECT * FROM merch_oppdata WHERE opp_id=?";
                              $srr = $DB->query($sql,array($oppID));
                              $i = 0; //以$i為這個陣列的索引值
                              while($i<count($srr)){ 
                                ?>
                                <tr>
                                  <td><?php echo $srr[$i]['opd_applydate'] ?? "&nbsp;"; ?></td>
                                  <td><?php echo ($srr[$i]['opd_startdate'] ?? "&nbsp;")."~".($srr[$i]['opd_enddate'] ?? "&nbsp;"); ?></td>
                                  <td><?php echo $srr[$i]['opd_days'] ?? "&nbsp;"; ?></td>
                                  <td><?php echo $srr[$i]['mb_id'] ?? "&nbsp;"; ?></td>
                                  <td><?php echo $srr[$i]['opd_paid'] ?? "&nbsp;"; ?></td>
                                  <td><?php echo $mdJS['oppstatus'][$srr[$i]['opd_status']] ?? "&nbsp;"; ?></td>
                                </tr>
                            <?php 
                                $i++;
                                } 
                              ?>
                          </tbody>
                          <tfoot>
                            <tr>
                              <th>申購日期時間</th>
                              <th>生效日期時間</th>
                              <th>有效期限</th>
                              <th>申購會員</th>
                              <th>已使用點數</th>
                              <th>執行狀態</th>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第三分頁項目使用紀錄 -->
                    <!-- 第四分頁項目評價紀錄 -->
                    <div class="tab-pane" id="tabMenu4">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;會員評價是為了本站未來更為友善操作的改版，而整合使用者的使用體驗數據，無論評價數據產生的時間有多久，評價紀錄不應也不將自資料庫中刪除。
                      </div>
                      <hr>
                      <!-- card-body -->
                      <div class="card-body">
                        <table id="dataTable_ratinglist" class="table table-bordered table-striped table-hover" data-search>
                          <thead>
                            <tr>
                              <th>日期</th>
                              <th>來自</th>
                              <th><?php echo $geJS['idfor'][$idForItem]['rating']['1']?:" &nbsp"; ?></th>
                              <th><?php echo $geJS['idfor'][$idForItem]['rating']['2']?:" &nbsp"; ?></th>
                              <th><?php echo $geJS['idfor'][$idForItem]['rating']['3']?:" &nbsp"; ?></th>
                              <th><?php echo $geJS['idfor'][$idForItem]['rating']['4']?:" &nbsp"; ?></th>
                              <th><?php echo $geJS['idfor'][$idForItem]['rating']['5']?:" &nbsp"; ?></th>
                              <th>評論</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                              $sql="SELECT * FROM fsl_rating WHERE rat_forid=? AND rat_for=?";
                              $srr = $DB->query($sql,array($oppID,$idForItem));
                              $i = 0; //以$i為$nameList這個陣列的索引值
                              while($i<count($srr)){ 
                                ?>
                                <tr>
                                  <td><?php echo $srr[$i]['rat_datetime']; ?></td>
                                  <td><?php echo $srr[$i]['mb_id']; ?></td>
                                  <td><?php echo $srr[$i]['rat_s1']; ?></td>
                                  <td><?php echo $srr[$i]['rat_s2']; ?></td>
                                  <td><?php echo $srr[$i]['rat_s3']; ?></td>
                                  <td><?php echo $srr[$i]['rat_s4']; ?></td>
                                  <td><?php echo $srr[$i]['rat_s5']; ?></td>
                                  <td>
                                  <?php if(!empty($srr[$i]['rat_review'])) { ?>
                                    <button type="button" class="btn btn-info btn-block btn-sm" title="<?php echo $srr[$i]['rat_review']; ?>" alt="<?php echo $srr[$i]['rat_review']; ?>"><i class="fa fa-comments"></i> </button>
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
                              <th>總平均</th>
                              <th></th>
                              <th><?php echo $S1; ?></th>
                              <th><?php echo $S2; ?></th>
                              <th><?php echo $S3; ?></th>
                              <th><?php echo $S4; ?></th>
                              <th><?php echo $S5; ?></th>
                              <th style="font-size:1.2em;font-weight:bold;"><?php echo $totAVE; ?></th>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第四分頁項目評價紀錄 -->
                    <?php } ?>
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
<script src="../pluginFiles/cropper/cropper.js<?php echo '?t='.time(); ?>"></script>
<!-- suneditor -->
<script src="../pluginFiles/suneditor/dist/suneditor.min.js<?php echo '?t='.time(); ?>"></script>
<script src="../pluginFiles/suneditor/sample/js/common.js<?php echo '?t='.time(); ?>"></script>
<script src="../pluginFiles/suneditor/src/lang/zh_tw.js<?php echo '?t='.time(); ?>"></script>
<!-- codemirror -->
<script src="../pluginFiles/codemirror/lib/codemirror.min.js<?php echo '?t='.time(); ?>"></script>
<script src="../pluginFiles/codemirror/mode/htmlmixed/htmlmixed.js<?php echo '?t='.time(); ?>"></script>
<script src="../pluginFiles/codemirror/mode/xml/xml.js<?php echo '?t='.time(); ?>"></script>
<script src="../pluginFiles/codemirror/mode/css/css.js<?php echo '?t='.time(); ?>"></script>
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<!-- Custom JS style -->
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
<script src="../scriptFiles/fileupload.js<?php echo '?t='.time(); ?>"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

<!-- ------------------------ /Scripts Link ------------------------ -->
<script type="text/javascript">
////////////////////////////////////////////////////////////////
// ---------------- JAVASCRIPTS 全域調用參數設置 --------------- //
////////////////////////////////////////////////////////////////

  var mainObj = $.parseJSON( '<?php echo func_stripewhite($mdGet); ?>' ); 
  var briefActive = false;
  var bodyActive = false;
  var sunBrief;
  var sunBody;

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
    //----------------------------------------
    var tradelistTB = datatableCreate('dataTable_tradelist');
    var ratinglistTB = datatableCreate('dataTable_ratinglist');

    sunBrief = suneditorCreate('opp_brief','ez','100%','180');
    sunBrief.onChange = function (contents, core) {
      if(contents !== $('#opp_brief').val()){
        briefActive = true;
        window.parent.$('#topBut_save').addClass('active');
      } else {
        briefActive = false;
        if(bodyActive == false) { window.parent.$('#topBut_save').removeClass('active'); }
      }
    }

    sunBody = suneditorCreate('opp_htmlbody','full','100%');
    sunBody.onChange = function (contents, core) {
      if(contents !== $('#opp_htmlbody').val()){
        bodyActive = true;
        window.parent.$('#topBut_save').addClass('active');
      } else {
        bodyActive = false;
        if(briefActive == false) { window.parent.$('#topBut_save').removeClass('active'); }
      }
    }
  // ------------------------- 評價五角圖 ------------------------------- //
  var ctx = document.getElementById( "radarChart" );
    ctx.height = 280;
    var myChart = new Chart( ctx, {
        type: 'radar',
        data: {
          labels: '<?php echo $ratingItem; ?>'.split(','),//$ratingItem $ratingData
          datasets: [
                          {
                            label: "項目受評",
                            data: '<?php echo $ratingData; ?>'.split(','),
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
  // ------------------------- 加購模式選單關聯 ------------------------------- //
    $('#opp_mode').on('change',function(){
      var thsVal = $(this).val();
      if(thsVal=='0'||thsVal==''){//.prop('disabled', 'disabled')
        $('#opp_notify').val('0').prop('disabled', 'disabled');
        $('#opp_cost,#opp_days').val('0').prop('readonly', 'readonly');
      } else {
        $('#opp_notify').val('0').removeAttr("disabled");
        if(thsVal=='2'){ 
          $('#opp_cost').val('0').removeAttr("readonly");
          $('#opp_days').val('0').prop('readonly', 'readonly');
        }
        else {
          $('#opp_cost,#opp_days').val('0').removeAttr("readonly");
        }

      }

    });

  // 內頁右上方小導航返回列表頁
  $('li.prevLink').on('click', function(){
    window.location.replace(getCurrentURI('','merch_opplist.php'));
  });
  ///////////////////////// 「操作進行中等待」觸發處理 ///////////////////////////////
          /*
          $('.preloader').css("height", "").children().show();
          $('#loadingMask').show();

          $('#loadingMask').hide();
          $('.preloader').css("height", 0).children().hide();
          */
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
            url: '../funcFiles/md_act_func.php',
            data: {
              'arrDatA': {}, 
              'formAcce':'func_opp_delete',
              'token': getTokenVal(), 
              'oppID':'<?php echo $oppID; ?>'
            },
            dataType: 'json',
            async: false, 
          success: function (data) {
              if(data['RTN']==1){
                window.location.replace("merch_opplist.php");
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
        if(accE=='func_opp_infoUpdate'){
          datA = getFormObjects($('#form_tabMenu1'));
          datB = getFormObjects($('#form_tabMenu2'));
          formData = Object.assign(formData, datA, datB);
          /*
          if(0==parseInt('<?php echo $oppID; ?>')){
            accE = 'func_opp_addnew';
          }
          */
          imgBase64 = $('#main_crop_area').data('cropImage64') || '';
        }else{

        }
        $.ajax({
            type: typE,
            url: url,
            data: {
              'arrDatA': formData, 
              'formAcce': accE,
              'imgUp': imgBase64,
              'token': getTokenVal(), 
              'oppID':'<?php echo $oppID; ?>'
            },
            dataType: 'json',
            async: false, 
          success: function (data) {
              console.log(data);
              if(accE=='func_opp_infoUpdate'){
                if (data['RTN']==1) {
                  if(data['newID'] && parseInt(data['newID'])>0){
                    window.location.replace("merch_oppdetail.php?currID="+data['newID']);
                    return true;
                  }
                  var newType = mainObj['opptype'][$('#opp_type').val()];
                  var newMode = mainObj['oppmode'][$('#opp_mode').val()];
                  var newSta = mainObj['oppstatus'][$('#opp_status').val()];
                  $('.profile-title').html($('#opp_title').val());
                  $('.profile-title + p').html($('#opp_subscript').val());
                  $('.box-profile > ul > li:nth-child(1) > a').html(newType);
                  $('.box-profile > ul > li:nth-child(2) > a').html(newMode);
                  $('.box-profile > ul > li:nth-child(3) > a').html(datA['upTime']);
                  switch ($('#opp_status').val()){
                    case '0': 
                      $('.box-profile > ul > li:nth-child(5) > a').removeClass( "text-success" ).removeClass( "text-warning" ).addClass( "text-danger" );
                    break;
                    case '1': 
                      $('.box-profile > ul > li:nth-child(5) > a').removeClass( "text-warning" ).removeClass( "text-danger" ).addClass( "text-success" );
                    break;
                    case '2': 
                      $('.box-profile > ul > li:nth-child(5) > a').removeClass( "text-success" ).removeClass( "text-danger" ).addClass( "text-warning" );
                    break;
                    case '3': 
                      $('.box-profile > ul > li:nth-child(5) > a').removeClass( "text-success" ).removeClass( "text-danger" ).addClass( "text-warning" );
                    break;
                  }
                  $('.box-profile > ul > li:nth-child(5) > a').html(newSta);
                  doSerialize();
                } 
              } else {


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
