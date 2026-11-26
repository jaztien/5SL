<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_SYSADMIN');
define('PAGE_ITEM','ITEM_SYS');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);//部份form元件限制編輯操作
define('bkRedir',1);
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//
$adID=currID;
//
$res = DB->row("SELECT * FROM fsl_administrator WHERE ad_id=?",array($adID));
//代表圖像設定
if(!empty($adID)&&file_exists("../uploadFiles/ad/adphoto_".$adID."_thumb.jpg")){
  $cropImgDisplay = 'show';
  $mainimgpath = '../uploadFiles/ad/adphoto_'.$adID.'_thumb.jpg?'.time();
  $mainimgchk = 'adphoto_'.$adID.'_.jpg';
} else {
  $cropImgDisplay = 'hide';
  $mainimgpath = '../imageFiles/nopic_user_200x200.jpg';
  $mainimgchk = "";
}
//功能陣列建立，取自JSON檔案
$adJS=_fn_getCachedJsonData("../jsonFiles/administrator.json");
$admJS=_fn_getCachedJsonData("../jsonFiles/adminpage.json");

?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
	
  <title> Fifth Season Life 人生第五季 | 管理人員資料 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <!-- Bootstrap 5 & Bootstrap Icons -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <!-- DataTables 3 -->
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <!-- Theme style (AdminLTE 4) -->
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <!-- Dropzone JS style -->
  <link rel="stylesheet" href="../pluginFiles/dropzone/dropzone.css" />
  <!-- Cropper JS style -->
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.lightbox.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">

<!-- -------------------------- /CSS Link -------------------------- -->
<style>
  /* 取代舊版 iCheck-warning 與 iCheck-success 的 BS5 原生核取方塊色彩樣式 */
  .form-check-warning .form-check-input:checked {
    background-color: #ffc107;
    border-color: #ffc107;
  }
  .form-check-warning .form-check-input:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
  }
  .form-check-success .form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
  }
  .form-check-success .form-check-input:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
  }
</style>
</head>
<!-- [變更1] 移除舊版 layout-navbar-fixed hold-transition sidebar-mini，改用 bg-body-tertiary -->
<body class="bg-body-tertiary theme-green">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1 ms-2"><i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?></div>
  <!-- 全頁框：[變更2] .wrapper 改為 .app-wrapper -->
  <div class="app-wrapper">
    <!-- 頁內全頁內容：[變更3] .content-wrapper 改為 .app-main -->
    <main class="app-main">
      <!-- 頁頭導覽條：[變更4] .content-header 改為 .app-content-header -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0"><?php echo !empty($adID) ? "編輯" : "新增"; ?><?php echo (!empty($adID) && $adID==$_SESSION['SESS_AD_ID']) ? "我的個人資料" : "管理人員資料"; ?><span class="fs-7">【ID: <?php echo $adID; ?>】</span></h4>
              <h6 class="m-0 fw-light text-muted fs-7 lh-ms">管理者資料編輯</h6>
            </div>
            <div class="col-sm-6">
              <!-- [變更5] float-sm-right 改為 float-sm-end -->
              <ol class="breadcrumb float-sm-end small mb-0">
                  <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                  <li class="breadcrumb-item">系統設定管理</li>
                  <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回列表" onClick="window.location.replace('ad_list.php');">管理人員列表</li>
                  <li class="breadcrumb-item active"><?php echo (!empty($res['ad_id']) && $res['ad_id']==$_SESSION['SESS_AD_ID']) ? "我的個人資料" : "管理人員資料"; ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>
      <!-- /頁頭導覽條 -->

      <!-- 主要內容區域：[變更6] .content 改為 .app-content -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <!-- 左側個人資訊卡片 -->
            <div class="col-md-3">
              <div class="card card-primary card-outline shadow-sm">
                <div class="card-body box-profile">
                  <div class="text-center">
                    <!-- Image Area -->
                    <div class="image_area">
                      <!-- [變更7] 圖示改為 Bootstrap Icons (bi-trash3) -->
                      <i class="nav-icon bi bi-trash3 fs-4 cropImgTrash<?php echo " ".$cropImgDisplay; ?>" title="刪除圖像"></i>
                      <form method="post">
                        <label for="cropUploadImage">
                          <!-- [變更8] img-circle 改為 rounded-circle -->
                          <img id="avatarImage" name="avatarImage" class="profile-user-img img-fluid rounded-circle imgEDicon" src="<?php echo $mainimgpath; ?>" alt="代表圖像" title="代表圖像"/>
                          <div class="overlay">
                            <div class="overtext"><?php echo !empty($adID) ? "更改" : "新增"; ?>代表圖像</div>
                          </div>
                          <input type="file" name="image" class="image d-none" id="cropUploadImage" crop-width="400" crop-height="400">
                        </label>
                      </form>
                      <!-- -------------------- 裁切主圖彈出視窗 -------------------- -->
                      <?php include('include_avatarmodal.php'); ?>
                    </div>
                    <!-- /Image Area -->
                  </div>
                  <h3 class="profile-username text-center mt-2"><?php echo !empty($res['ad_name']) ? $res['ad_name'] : "&nbsp;"; ?></h3>
                  <p class="text-muted text-center"><?php echo !empty($res['ad_authlevel']) ? $adJS['authlevel'][$res['ad_authlevel']] : "&nbsp;"; ?></p>

                  <!-- [變更9] list-group-unbordered 改用 BS5 原生 list-group-flush，float-right 改為 float-end -->
                  <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item px-0">
                      <b>狀態</b> <a class="float-end text-decoration-none<?php echo (!empty($res['ad_status']) && $res['ad_status']==1) ? "" : " text-danger"; ?>"><?php echo get_json_value($adJS['status']??'', $sr[$i]['ad_status']??'', true) ?? "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item px-0">
                      <b>暱稱</b> <a class="float-end text-decoration-none"><?php echo !empty($res['ad_title']) ? $res['ad_title'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item px-0">
                      <b>電子郵箱</b> <a class="float-end text-decoration-none"><?php echo !empty($res['ad_email']) ? $res['ad_email'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item px-0">
                      <b>手機號碼</b> <a class="float-end text-decoration-none"><?php echo !empty($res['ad_phone']) ? $res['ad_phone'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item px-0">
                      <b>Line ID</b> <a class="float-end text-decoration-none"><?php echo !empty($res['ad_line']) ? $res['ad_line'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item px-0">
                      <b>編輯日期</b> <a class="float-end text-dark text-decoration-none"><?php echo !empty($res['ad_updated']) ? $res['ad_updated'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item px-0">
                      <b>建立日期</b> <a class="float-end text-dark text-decoration-none"><b><?php echo !empty($res['ad_published']) ? $res['ad_published'] : "&nbsp;"; ?></b></a>
                    </li>
                  </ul>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->

            <!-- 右側多分頁表單區 -->
            <div class="col-md-9">
              <div class="card shadow-sm modern-card-tabs">
                <div class="card-header p-2">
                  <!-- [變更10] data-toggle="tab" 改為 data-bs-toggle="tab" -->
                  <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation"><a class="nav-link active" href="#tabMenu1" data-index="1" data-bs-toggle="tab" data-topbut="1" role="tab">個人訊息修改</a></li>
                    <?php if (!empty($adID)) { ?>
                    <li class="nav-item" role="presentation"><a class="nav-link" href="#tabMenu2" data-index="2" data-bs-toggle="tab" data-topbut="1" role="tab">管理權限修改</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" href="#tabMenu3" data-index="3" data-bs-toggle="tab" data-topbut="1" role="tab">密碼更改</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" href="#tabMenu4" data-index="4" data-bs-toggle="tab" data-topbut="0" role="tab">管理紀錄</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" href="#tabMenu5" data-index="5" data-bs-toggle="tab" data-topbut="0" role="tab">訊息傳遞</a></li>
                    <?php } ?>
                  </ul>
                </div><!-- /.card-header -->

                <div class="card-body">
                  <div class="tab-content">
                    <!-- 第一分頁管理者資料修改 -->
                    <div class="tab-pane fade show active" id="tabMenu1" role="tabpanel">
                      <div class="small text-secondary">
                        <i class="bi bi-exclamation-circle me-1"></i>被授權的管理者可以新增或更改管理者資料，唯「功能頁面授權」以及「管理權限等級」必須具有最高管理權限的管理者始得編輯，新增資料之後的管理人員，僅僅具備基本的預設頁面操作權限，若必要請立即設定每頁功能的操作權限。
                      </div>
                      <hr>
                      <!-- [變更11] 使用 Bootstrap 5 原生表單驗證 (.needs-validation + novalidate) -->
                      <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/ad_act_func.php" method="post" data-formmode="infoupdate" data-func="func_ad_infoUpdate" class="needs-validation" novalidate>
                        <input type="hidden" name="mainimgchk" id="mainimgchk" class="notSubmit" value="<?php echo $mainimgchk; ?>">
                        <div class="card-body">
                          <div class="row mb-3">
                            <label for="ad_name" class="col-sm-3 col-form-label">管理者姓名</label>
                            <div class="col-sm-9">
                              <input type="text" class="form-control" id="ad_name" name="ad_name" value="<?php echo !empty($res['ad_name']) ? $res['ad_name'] : ""; ?>" placeholder="管理者姓名" minlength="2" required<?php echo NreadOnly; ?>>
                              <div class="invalid-feedback">請輸入管理者姓名（至少 2 個字元）。</div>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="ad_title" class="col-sm-3 col-form-label">暱稱</label>
                            <div class="col-sm-9">
                              <input type="text" class="form-control" id="ad_title" name="ad_title" value="<?php echo !empty($res['ad_title']) ? $res['ad_title'] : ""; ?>" placeholder="暱稱" minlength="2" required<?php echo NreadOnly; ?>>
                              <div class="invalid-feedback">請輸入暱稱（至少 2 個字元）。</div>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="ad_email" class="col-sm-3 col-form-label">E-mail</label>
                            <div class="col-sm-9">
                              <input type="email" class="form-control" id="ad_email" name="ad_email" value="<?php echo !empty($res['ad_email']) ? $res['ad_email'] : ""; ?>" placeholder="E-mail" required<?php echo NreadOnly; ?>>
                              <div class="invalid-feedback">請輸入有效的電子郵件格式。</div>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="ad_phone" class="col-sm-3 col-form-label">聯絡電話</label>
                            <div class="col-sm-9">
                              <input type="text" class="form-control" id="ad_phone" name="ad_phone" value="<?php echo !empty($res['ad_phone']) ? $res['ad_phone'] : ""; ?>" placeholder="聯絡電話" required<?php echo NreadOnly; ?>>
                              <div class="invalid-feedback">請輸入聯絡電話。</div>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="ad_line" class="col-sm-3 col-form-label">Line ID</label>
                            <div class="col-sm-9">
                              <input type="text" class="form-control" id="ad_line" name="ad_line" value="<?php echo !empty($res['ad_line']) ? $res['ad_line'] : ""; ?>" placeholder="Line ID"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="ad_authlevel" class="col-sm-3 col-form-label">管理等級</label>
                            <div class="col-sm-9">
                              <select class="form-select" id="ad_authlevel" name="ad_authlevel" required<?php echo TdisAble; ?>>
                                <?php
                                  foreach($adJS['authlevel'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if((!empty($res['ad_authlevel']) && $res['ad_authlevel']==$key) || (empty($adID) && $key==0)) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                              <div class="invalid-feedback">請選擇管理等級。</div>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="ad_status" class="col-sm-3 col-form-label">管理授權狀態</label>
                            <div class="col-sm-9">
                              <select class="form-select" id="ad_status" name="ad_status" required<?php echo NdisAble; ?>>
                                <?php echo init_select_options($adJS['status']??[], $res['ad_status']??'',true); ?>
                              </select>
                              <div class="invalid-feedback">請選擇管理授權狀態。</div>
                            </div>
                          </div>
                          <hr>
                          <?php if(empty($adID)){ ?>
                          <div class="row mb-3">
                            <label for="ad_account" class="col-sm-3 col-form-label">登入帳號</label>
                            <div class="col-sm-9">
                              <div class="input-group has-validation">
                                <input id="ad_account" name="ad_account" type="text" class="form-control" value="<?php echo !empty($res['ad_account']) ? $res['ad_account'] : ""; ?>" placeholder="登入帳號" required<?php echo !empty($adID) ? " readonly" : NreadOnly; ?>>
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <div class="invalid-feedback">請輸入登入帳號。</div>
                              </div>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="ad_password" class="col-sm-3 col-form-label">登入密碼</label>
                            <div class="col-sm-9">
                              <div class="input-group has-validation">
                                <input id="ad_password" name="ad_password" type="password" class="form-control" placeholder="登入密碼" minlength="6" data-match="comfPass" required<?php echo NreadOnly; ?>>
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <div class="invalid-feedback">請輸入至少 6 個字元的登入密碼。</div>
                              </div>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="comfPass" class="col-sm-3 col-form-label">密碼確認</label>
                            <div class="col-sm-9">
                              <div class="input-group has-validation">
                                <input id="comfPass" name="comfPass" type="password" class="form-control notSubmit" placeholder="密碼確認" required<?php echo NreadOnly; ?>>
                                <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                <div class="invalid-feedback">兩次輸入的密碼不一致。</div>
                              </div>
                            </div>
                          </div>
                          <?php } ?>
                        </div>
                        <!-- /.card-body -->
                      </form>
                    </div>
                    <!-- /第一分頁管理者資料修改 -->

                    <?php if (!empty($adID)) { ?>
                    <!-- 第二分頁管理權限修改 -->
                    <div class="tab-pane fade" id="tabMenu2" role="tabpanel">
                      <div class="small text-secondary text-start">
                        <i class="bi bi-exclamation-circle me-1"></i>管理權限只得在最高管理者身份下始得進行編輯。並且最高管理者的管理權限不被其他管理人員控管。
                      </div>
                      <hr>
                      <div class="mb-2 small">
                        <i class="bi bi-dash-lg text-warning fw-bold"></i> 預設權限
                        <i class="bi bi-dash-lg text-success fw-bold ms-3"></i> 自定義權限
                      </div>
                      <div class="row">
                        <div class="col-md-12">
                          <form id="form_tabMenu2" name="form_tabMenu2" action="../funcFiles/ad_act_func.php" method="post" data-formmode="authupdate" data-func="func_ad_authUpdate">
                            <?php
                              $pgARR = array();
                              $authpage = $admJS['sidebar'];
                              $apnum = count($authpage);
                              $i=0;
                              while($i < $apnum){
                                $authpage2 = isset($authpage[$i]['subgroup']) ? $authpage[$i]['subgroup'] : array();
                                $apnum2 = count($authpage2);
                              ?>
                                <div class="card card-outline card-secondary mb-3">
                                  <div class="card-body">
                                    <div class="row border-bottom table-active pt-2 pb-1 mb-2" style="margin-top:-12px;">
                                      <div class="col-sm-7"><h5 class="mb-0"><?php echo $authpage[$i]['title']; ?></h5></div>
                                      <div class="col-sm-5">
                                        <div class="row text-center">
                                            <div class="col-3"><i class="bi bi-eye" title="檢視"></i></div>
                                            <div class="col-3"><i class="bi bi-file-earmark-plus" title="新增"></i></div>
                                            <div class="col-3"><i class="bi bi-pencil-square" title="編輯"></i></div>
                                            <div class="col-3"><i class="bi bi-trash" title="刪除"></i></div>
                                        </div>
                                      </div>
                                    </div>
                              <?php
                                if($apnum2 > 0){
                                  $si=0;
                                  while($si < $apnum2){
                                    $rs2 = DB->row("SELECT * FROM fsl_admin_auth WHERE ap_pagecode=? AND ad_id=?", array($authpage2[$si]['pagecode'],$adID));
                                    $authpage3 = isset($authpage2[$si]['subgroup']) ? $authpage2[$si]['subgroup'] : array();
                                    $apnum3 = count($authpage3);
                                    $viewTag = "view_".$authpage2[$si]['pagecode'];
                                    $addTag = "add_".$authpage2[$si]['pagecode'];
                                    $editTag = "edit_".$authpage2[$si]['pagecode'];
                                    $delTag = "del_".$authpage2[$si]['pagecode'];
                                    if($rs2){
                                      $viewCHK=($rs2['auth_browse']==1) ? " checked" : "";
                                      $addCHK=($rs2['auth_addnew']==1) ? " checked" : "";
                                      $editCHK=($rs2['auth_edit']==1) ? " checked" : "";
                                      $delCHK=($rs2['auth_delete']==1) ? " checked" : "";
                                      $chkColorClass="form-check-success";
                                    } else {
                                      $gF = isset($authpage2[$si]['default']) ? $authpage2[$si]['default'] : '0000';
                                      $viewCHK=(substr($gF,0,1)=='1') ? " checked" : "";
                                      $addCHK=(substr($gF,1,1)=='1') ? " checked" : "";
                                      $editCHK=(substr($gF,2,1)=='1') ? " checked" : "";
                                      $delCHK=(substr($gF,3,1)=='1') ? " checked" : "";
                                      $chkColorClass="form-check-warning";
                                    }
                                  ?>
                                    <div class="row border-bottom py-2 align-items-center">
                                      <div class="col-sm-7"><?php echo $authpage2[$si]['title']; ?></div>
                                      <div class="col-sm-5">
                                      <?php if(isset($authpage2[$si]['default'])) { ?>
                                        <div id="adminPG_<?php echo $authpage2[$si]['pagecode']; ?>" name="adminPG_<?php echo $authpage2[$si]['pagecode']; ?>" class="row text-center">
                                          <div class="col-3 d-flex justify-content-center">
                                            <div class="form-check <?php echo $chkColorClass; ?> mb-0">
                                              <input class="form-check-input" type="checkbox" id="<?php echo $viewTag; ?>" name="<?php echo $viewTag; ?>" value="1"<?php echo $viewCHK.TdisAble; ?>>
                                            </div>
                                          </div>
                                          <div class="col-3 d-flex justify-content-center">
                                            <div class="form-check <?php echo $chkColorClass; ?> mb-0">
                                              <input class="form-check-input" type="checkbox" id="<?php echo $addTag; ?>" name="<?php echo $addTag; ?>" value="1"<?php echo $addCHK.TdisAble; ?>>
                                            </div>
                                          </div>
                                          <div class="col-3 d-flex justify-content-center">
                                            <div class="form-check <?php echo $chkColorClass; ?> mb-0">
                                              <input class="form-check-input" type="checkbox" id="<?php echo $editTag; ?>" name="<?php echo $editTag; ?>" value="1"<?php echo $editCHK.TdisAble; ?>>
                                            </div>
                                          </div>
                                          <div class="col-3 d-flex justify-content-center">
                                            <div class="form-check <?php echo $chkColorClass; ?> mb-0">
                                              <input class="form-check-input" type="checkbox" id="<?php echo $delTag; ?>" name="<?php echo $delTag; ?>" value="1"<?php echo $delCHK.TdisAble; ?>>
                                            </div>
                                          </div>
                                        </div>
                                        <?php } ?>
                                      </div>
                                    </div>
                              <?php
                                    if($apnum3 > 0){
                                      $ti=0;
                                      while($ti < $apnum3){
                                        $rs3 = DB->row("SELECT * FROM fsl_admin_auth WHERE ap_pagecode=? AND ad_id=?", array($authpage3[$ti]['pagecode'],$adID));
                                        $viewTag = "view_".$authpage3[$ti]['pagecode'];
                                        $addTag = "add_".$authpage3[$ti]['pagecode'];
                                        $editTag = "edit_".$authpage3[$ti]['pagecode'];
                                        $delTag = "del_".$authpage3[$ti]['pagecode'];
                                        if($rs3){
                                          $viewCHK=($rs3['auth_browse']==1) ? " checked" : "";
                                          $addCHK=($rs3['auth_addnew']==1) ? " checked" : "";
                                          $editCHK=($rs3['auth_edit']==1) ? " checked" : "";
                                          $delCHK=($rs3['auth_delete']==1) ? " checked" : "";
                                          $chkColorClass="form-check-success";
                                        } else {
                                          $gF = isset($authpage3[$ti]['default']) ? $authpage3[$ti]['default'] : '0000';
                                          $viewCHK=(substr($gF,0,1)=='1') ? " checked" : "";
                                          $addCHK=(substr($gF,1,1)=='1') ? " checked" : "";
                                          $editCHK=(substr($gF,2,1)=='1') ? " checked" : "";
                                          $delCHK=(substr($gF,3,1)=='1') ? " checked" : "";
                                          $chkColorClass="form-check-warning";
                                        }
                                      ?>
                                        <div class="row border-bottom py-2 align-items-center">
                                          <div class="col-sm-7 ps-4"><?php echo $authpage3[$ti]['title']; ?></div>
                                          <div class="col-sm-5">
                                          <?php if(isset($authpage3[$ti]['default'])) { ?>
                                            <div id="adminPG_<?php echo $authpage3[$ti]['pagecode']; ?>" name="adminPG_<?php echo $authpage3[$ti]['pagecode']; ?>" class="row text-center">
                                              <div class="col-3 d-flex justify-content-center">
                                                <div class="form-check <?php echo $chkColorClass; ?> mb-0">
                                                  <input class="form-check-input" type="checkbox" id="<?php echo $viewTag; ?>" name="<?php echo $viewTag; ?>" value="1"<?php echo $viewCHK.TdisAble; ?>>
                                                </div>
                                              </div>
                                              <div class="col-3 d-flex justify-content-center">
                                                <div class="form-check <?php echo $chkColorClass; ?> mb-0">
                                                  <input class="form-check-input" type="checkbox" id="<?php echo $addTag; ?>" name="<?php echo $addTag; ?>" value="1"<?php echo $addCHK.TdisAble; ?>>
                                                </div>
                                              </div>
                                              <div class="col-3 d-flex justify-content-center">
                                                <div class="form-check <?php echo $chkColorClass; ?> mb-0">
                                                  <input class="form-check-input" type="checkbox" id="<?php echo $editTag; ?>" name="<?php echo $editTag; ?>" value="1"<?php echo $editCHK.TdisAble; ?>>
                                                </div>
                                              </div>
                                              <div class="col-3 d-flex justify-content-center">
                                                <div class="form-check <?php echo $chkColorClass; ?> mb-0">
                                                  <input class="form-check-input" type="checkbox" id="<?php echo $delTag; ?>" name="<?php echo $delTag; ?>" value="1"<?php echo $delCHK.TdisAble; ?>>
                                                </div>
                                              </div>
                                            </div>
                                          <?php } ?>
                                          </div>
                                        </div>
                                <?php
                                          $ti++;
                                        }
                                      }
                                      $si++;
                                    }
                                  }
                                ?>
                                </div>
                              </div>
                              <?php
                                $i++;
                              }
                              ?>
                          </form>
                        <hr>
                        </div>
                      </div>
                      <!-- /End -->
                    </div>
                    <!-- /第二分頁管理權限修改 -->

                    <!-- 第三分頁密碼管理 -->
                    <div class="tab-pane fade" id="tabMenu3" role="tabpanel">
                      <div class="small text-secondary text-start">
                        <i class="bi bi-exclamation-circle me-1"></i>管理者的帳號密碼除了管理者本人以及最高管理權限者之外，其他管理者無權更改（管理帳號目前無法在操作介面上更改）。
                      </div>
                      <hr>
                      <div class="card-body rounded" style="background-color:#DFE3E8;">
                        <strong><i class="bi bi-person me-1"></i>登入帳號</strong>
                        <div class="input-group mb-3 mt-1">
                          <input id="ad_account" name="ad_account" type="text" class="form-control" value="<?php echo !empty($res['ad_account']) ? $res['ad_account'] : ""; ?>" placeholder="登入帳號" required<?php echo !empty($adID) ? " readonly" : NreadOnly; ?>>
                          <span class="input-group-text"><i class="bi bi-person"></i></span>
                        </div>
                        <hr>
                        <form id="form_tabMenu3" name="form_tabMenu3" action="../funcFiles/ad_act_func.php" method="post" data-formmode="passupdate" data-func="func_ad_passUpdate" class="needs-validation" novalidate>
                          <strong><i class="bi bi-lock me-1"></i>新登入密碼</strong>
                          <div class="input-group has-validation mb-3 mt-1">
                            <input id="ad_password" name="ad_password" type="password" class="form-control" placeholder="新登入密碼（至少 6 個字元）" minlength="6" data-match="comfPass" required<?php echo CF_AD_ID==$adID ? NreadOnly : TreadOnly; ?>>
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <div class="invalid-feedback">請輸入至少 6 個字元的新登入密碼。</div>
                          </div>
                          <hr>
                          <strong><i class="bi bi-shield-check me-1"></i>密碼確認</strong>
                          <div class="input-group has-validation mb-3 mt-1">
                            <input id="comfPass" name="comfPass" type="password" class="form-control notSubmit" placeholder="密碼確認" required<?php echo CF_AD_ID==$adID ? NreadOnly : TreadOnly; ?>>
                            <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                            <div class="invalid-feedback">兩次輸入的密碼不一致。</div>
                          </div>
                        </form>
                      </div>
                    </div>
                    <!-- /第三分頁密碼管理 -->

                    <!-- 第四分頁管理記錄 adminlog -->
                    <div class="tab-pane fade" id="tabMenu4" role="tabpanel">
                      <div class="small text-secondary">
                        <i class="bi bi-exclamation-circle me-1"></i>本會員的中庸本系統的操作記錄列表，本列表用於偵錯、改進、分析甚至於究責，所以一旦寫入記錄便無法更改，操作記錄將於多處以及多型態備份。
                      </div>
                      <hr>
                      <div class="card-body">
                        <table id="dataTable_logList" class="table table-bordered table-striped table-hover w-100" data-butClass="gray" data-pagemax="25" data-dirto="" data-search>
                          <thead>
                            <tr>
                              <th>代碼</th>
                              <th>操作日期時間</th>
                              <th>操作內容</th>
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
                              $sr = DB->query($sql,array($adID,50));
                              foreach ($sr as $log) {
                                ?>
                                <tr>
                                  <td><?php echo $log['lg_code']; ?></td>
                                  <td><?php echo $log['lg_created']; ?></td>
                                  <td><?php echo _fn_getLogString($log['lg_params'],$log['lg_string']); ?></td>
                                </tr>
                              <?php } ?>
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
                    </div>
                    <!-- /第四分頁管理記錄 -->

                    <!-- 第五分頁訊息傳遞 sysinfo -->
                    <div class="tab-pane fade" id="tabMenu5" role="tabpanel">
                      <hr>
                    </div>
                    <!-- /第五分頁訊息傳遞 -->
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
      </div>
      <!-- /主要內容區域 -->
    </main>
    <!-- /頁內全頁內容 -->
  </div>
  <!-- /全頁框 -->

  <!-- ------------------------ Scripts Link ------------------------ -->
  <!-- jQuery -->
  <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
  <!-- Bootstrap 5 -->
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <!-- DataTables3 & Plugins -->
  <script src="../pluginFiles/datatables3/datatables.min.js"></script>
  <!-- Dropzone JS -->
  <script src="../pluginFiles/dropzone/dropzone.js"></script>
  <!-- Cropper JS -->
  <script src="../pluginFiles/cropper/cropper.js"></script>
  <!-- 5SeasonLife App -->
  <script src="../scriptFiles/adminlte.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/fileupload.js<?php echo '?t='.time(); ?>"></script>
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>
  <!-- ------------------------ /Scripts Link ------------------------ -->
  <script type="text/javascript">
  //************************************************************//
  //***************** JAVASCRIPTS 全域調用參數設置 *****************//
  //************************************************************//
  var adobj = <?php echo json_encode($adJS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

  // [變更15] 內建 Bootstrap 5 原生表單驗證函式（完全取代外部 validator.js）
  // [修正] 加上 [0] 取得原生 DOM 元素，才能呼叫 checkValidity() 與 setCustomValidity()
  function validateBS5Form($form) {
    if (!$form || $form.length === 0) return true;
    const formEl = $form[0];

    // 1. 檢查密碼一致性 (data-match)
    $form.find('[data-match]').each(function() {
      const targetId = $(this).data('match');
      const $confirmEl = $form.find('#' + targetId);
      if ($confirmEl.length) {
        if ($(this).val() !== $confirmEl.val()) {
          $confirmEl[0].setCustomValidity('密碼不一致');
        } else {
          $confirmEl[0].setCustomValidity('');
        }
      }
    });

    // 2. 觸發 HTML5 原生驗證並套用 BS5 .was-validated 視覺回饋
    const isValid = formEl.checkValidity();
    formEl.classList.add('was-validated');
    return isValid;
  }
  
  // 當使用者修改密碼確認欄位時，即時清除自訂錯誤訊息並重新比對
  $(document).on('input', '#ad_password, #comfPass', function() {
    const $form = $(this).closest('form');
    const $pass = $form.find('#ad_password');
    const $conf = $form.find('#comfPass');
    if ($pass.length && $conf.length) {
      $conf[0].setCustomValidity($pass.val() !== $conf.val() ? '密碼不一致' : '');
    }
  });
  //************************************************************//
  //*************** JAVASCRIPTS FUNCTIONs 全域調用 ***************//
  //************************************************************//
  function doReDirect(){
    window.history.back();
  }

  function doButSave(){
    var frm = $('#form_'+$('.tab-pane.active').prop("id"));
    // 使用內建 BS5 驗證
    if(!validateBS5Form(frm)){
      window.parent.toastr['warning']('請檢查標示紅框的必填或格式錯誤欄位！');
      return false;
    } else {
      ajaxPOST(frm);
    }
  }

  function doButDelete(){
    $.ajax({
        type: 'post',
        url: '../funcFiles/ad_act_func.php',
        data: {
          'arrDatA': {}, 
          'formAcce':'func_ad_delete',
          'token': getTokenVal(), 
          'adID':'<?php echo $adID; ?>'
        },
        dataType: 'json',
        async: false, 
      success: function (data) {
          if(data['RTN']==1){
            window.location.replace("ad_list.php");
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

  function doButReset(){
    location.reload(true);
  }

  ///////////////////////////// AJAX 表單處理 ////////////////////////////////
  ///////////////////////////// AJAX 表單處理 ////////////////////////////////
  function ajaxPOST(frm) {
    var $frm = frm || $('#form_' + $('.tab-pane.active').prop("id"));
    var accE = $frm.data('func');
    if (0 == parseInt('<?php echo $adID; ?>')) {
      accE = 'func_ad_addnew';
    }
    var url = $frm.attr('action');
    var typE = $frm.attr('method');
    var datA = $frm.find(":input").not(".notSubmit").serializeArray();
    
    // [修正1] 優先從 fileupload.js 的全域變數 cropImage64 取得裁切後的 Base64 圖片
    var imgBase64 = (typeof cropImage64 !== 'undefined' && cropImage64 !== '') 
                    ? cropImage64 
                    : ($('#main_crop_area').data('cropImage64') || '');

    $.ajax({
      type: typE,
      url: url,
      data: {
        'arrDatA': datA, 
        'formAcce': accE,
        'imgUp': imgBase64,
        'token': getTokenVal(), 
        'adID': '<?php echo $adID; ?>'
      },
      dataType: 'json',
      // [優化1] 移除已過時的 async: false，改用標準非同步請求避免瀏覽器警告與畫面凍結
      success: function (data) {
        console.log(data);
        if (accE == 'func_ad_addnew') {
          if (data['RTN'] == 1) {
            window.location.replace("ad_profile.php?currID=" + data['newID']);
            return false;
          }
        }
        
        if (window.parent && window.parent.toastr) {
          window.parent.toastr[data['msgType']](data['msgStr']);
        }

        if (accE == 'func_ad_infoUpdate') {
          if (data['RTN'] == 1) {
            $frm.removeClass('was-validated');
            var newLvl = adobj['authlevel'][$('#ad_authlevel').val()];
            var newSta = $('#ad_status').val();
            switch (newSta) {
              case '0': 
                $('.box-profile > ul > li:nth-child(1) > a').addClass("text-danger");
                break;
              case '1': 
                $('.box-profile > ul > li:nth-child(1) > a').removeClass("text-danger");
                break;
            }
            newSta = adobj['status'][newSta];
            $('.profile-username').html($('#ad_name').val());
            $('.profile-username + p').html(newLvl);
            $('.box-profile > ul > li:nth-child(1) > a').html(newSta);
            $('.box-profile > ul > li:nth-child(2) > a').html($('#ad_title').val());
            $('.box-profile > ul > li:nth-child(3) > a').html($('#ad_email').val());
            $('.box-profile > ul > li:nth-child(4) > a').html($('#ad_phone').val());
            $('.box-profile > ul > li:nth-child(5) > a').html($('#ad_line').val());
            $('.box-profile > ul > li:nth-child(6) > a').html(data['upTime']);
            $('#ad_terms').prop('checked', false);

            // [修正2] jQuery 4.0 已移除 $.now()，改用原生 Date.now()；並同步更新父視窗右上角頭像
            if (data['newUpPhoto'] && data['newUpPhoto'] != '') {
              var newPhotoUrl = data['newUpPhoto'] + '?' + Date.now();
              $(".adTopNaviPhoto").attr("src", newPhotoUrl);
              if (window.parent && window.parent.$) {
                window.parent.$(".adTopNaviPhoto").attr("src", newPhotoUrl);
              }
            }

            /* ** 重置表單異動數據 ** */
            cropImage64 = ''; // 清除圖檔資料
            if ($('#main_crop_area').length) {
              $('#main_crop_area').data('cropImage64', '');
            }
            if (typeof doSerialize === 'function') {
              doSerialize();
            }
          }
        } else if (accE == 'func_ad_passUpdate') {
          // [修正3] 原生 DOM 表單重設必須加上 取得 HTMLFormElement
          var passFormEl = document.getElementById('form_tabMenu3');
          if (passFormEl) passFormEl.reset();
          $('#form_tabMenu3').removeClass('was-validated');
          if (window.parent && window.parent.$) {
            window.parent.$('#topBut_save').removeClass('active');
          }
        } else if (accE == 'func_ad_authUpdate') {
          var colorCHG = data['colorCHG'];
          if (colorCHG) {
            $.each(colorCHG, function(index, value) {
              var elM = '#' + index;
              var bs5ColorClass = value.replace('icheck-', 'form-check-');
              $(elM + " .form-check").removeClass("form-check-warning form-check-success").addClass(bs5ColorClass);
            });
          }
          if (window.parent && window.parent.$) {
            window.parent.$('#topBut_save').removeClass('active');
          }
        }
      },
      error: function (data) {
        console.log(data);
        if (window.parent && window.parent.toastr) {
          window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
        }
      }
    });
  }

  //************************************************************//
  //***************** jQuery FUNCTIONs 區域調用 ******************//
  //************************************************************//
  $(document).ready(function() {
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });

    var logListTB = datatableCreate('dataTable_logList');
    logListTB.button().add(logListTB.buttons().count(), {
          className:'advAddBut-gray',
          // [變更17] 圖示改用 Bootstrap Icons (bi-journal-plus)
          text: '<i class="bi bi-journal-plus"></i> 新增投放規則',
          action: function(e) {
            // [變更18] 改用 Bootstrap 5 原生 Modal 實例開啟視窗
            const modalEl = document.getElementById('rulesModal');
            if (modalEl) {
              bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
            $('#rulesModal .modal-title').html('新增投放規則');
            $('#rulesModal #rulUpBut').html('新增');
            $('#rulesModal #rulDelBut').css("visibility", "hidden");
            $('#form_ruleupdate').attr('data-ruleid','0');
            $('#rulesModal .rule-container').load('include_adrules.php');
          }
    });   

    $('li.prevLink').on('click', function(){
      window.location.replace(getCurrentURI('','ad_list.php'));
    });

    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  });
</script>
</body>
</html>
