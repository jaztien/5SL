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
//DB->closeConnection();
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
<html lang="en">
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
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <!-- Dropzone JS style -->
  <link rel="stylesheet" href="../pluginFiles/dropzone/dropzone.css" />
  <!-- Cropper JS style -->
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.lightbox.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
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
            <div class="col-sm-6">
              <h4 class="m-0"><?php echo !empty($adID) ? "編輯" : "新增"; ?><?php echo (!empty($adID) && $adID==$_SESSION['SESS_AD_ID']) ? "我的個人資料" : "管理人員資料"; ?><span style="font-size:large;">【ID: <?php echo $adID; ?>】</span></h4>
              <h6 class="m-0 font-weight-light">管理者資料編輯</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                  <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                  <li class="breadcrumb-item">系統設定管理</li>
                  <li class="breadcrumb-item prevLink" alt="返回列表" title="返回列表" onClick="window.location.replace('ad_list.php');">管理人員列表</a></li>
                <li class="breadcrumb-item active"><?php echo (!empty($res['ad_id']) && $res['ad_id']==$_SESSION['SESS_AD_ID']) ? "我的個人資料" : "管理人員資料"; ?></li>
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
            <div class="col-md-4">
              <!-- Profile Image -->
              <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                  <div class="text-center">
                    <!-- Image Area -->
                    <div class="image_area">
                      <i class="nav-icon fas fa-2x fa-trash-can cropImgTrash<?php echo " ".$cropImgDisplay; ?>" alt="刪除圖像" title="刪除圖像"></i>
                      <form method="post">
                        <label for="cropUploadImage">
                          <img id="avatarImage" name="avatarImage" class="img-responsive profile-user-img img-fluid img-circle imgEDicon" src="<?php echo $mainimgpath; ?>" alt="代表圖像" title="代表圖像"/>
                          <div class="overlay">
                            <div class="overtext"><?php echo !empty($adID) ? "更改" : "新增"; ?>代表圖像</div>
                          </div>
                          <input type="file" name="image" class="image" id="cropUploadImage" style="display:none" crop-width="400" crop-height="400">
                        </label>
                      </form>
                      <!-- -------------------- 裁切主圖彈出視窗 -------------------- -->
                      <?php include('include_avatarmodal.php'); ?>
                    </div>
                    <!-- /Image Area -->
                  </div>
                  <h3 class="profile-username text-center"><?php echo !empty($res['ad_name']) ? $res['ad_name'] : "&nbsp;"; ?></h3>
                  <p class="text-muted text-center"><?php echo !empty($res['ad_authlevel']) ? $adJS['authlevel'][$res['ad_authlevel']] : "&nbsp;"; ?></p>

                  <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                      <b></b> <a class="float-right<?php echo (!empty($res['ad_status']) && $res['ad_status']==1) ? "" : " text-danger"; ?>"><?php echo !empty($res['ad_status']) ? $adJS['status'][$res['ad_status']] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>暱稱</b> <a class="float-right"><?php echo !empty($res['ad_title']) ? $res['ad_title'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>電子郵箱</b> <a class="float-right"><?php echo !empty($res['ad_email']) ? $res['ad_email'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>手機號碼</b> <a class="float-right"><?php echo !empty($res['ad_phone']) ? $res['ad_phone'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>Line ID</b> <a class="float-right"><?php echo !empty($res['ad_line']) ? $res['ad_line'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>編輯日期</b> <a class="float-right"><?php echo !empty($res['ad_updated']) ? $res['ad_updated'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>建立日期</b> <a class="float-right" style="color:black;"><b><?php echo !empty($res['ad_published']) ? $res['ad_published'] : "&nbsp;"; ?></b></a>
                    </li>
                  </ul>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->

            </div>
            <!-- /.col -->
            <div class="col-md-8">
              <div class="card card-secondary">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#tabMenu1" data-index="1" data-toggle="tab" data-topbut="1">個人訊息修改</a></li>
                    <?php if (!empty($adID)) { ?>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu2" data-index="2" data-toggle="tab" data-topbut="1">管理權限修改</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu3" data-index="3" data-toggle="tab" data-topbut="1">密碼更改</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu4" data-index="4" data-toggle="tab" data-topbut="0">管理紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu5" data-index="5" data-toggle="tab" data-topbut="0">訊息傳遞</a></li>
                    <?php } ?>
                  </ul>
                </div><!-- /.card-header -->
                <div class="card-body">
                  <div class="tab-content">
                    <!-- 第一分頁管理者資料修改 -->
                    <div class="tab-pane active" id="tabMenu1">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;被授權的管理者可以新增或更改管理者資料，唯「功能頁面授權」以及「管理權限等級」必須具有最高管理權限的管理者始得編輯，新增資料之後的管理人員，僅僅具備基本的預設頁面操作權限，若必要請立即設定每頁功能的操作權限。</div>
                      <hr>
                      <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/ad_act_func.php" method="post" data-formmode="infoupdate" data-func="func_ad_infoUpdate">
                        <input type="hidden" name="mainimgchk" id="mainimgchk" class="notSubmit" value="<?php echo $mainimgchk; ?>">
                        <div class="card-body">
                          <div class="form-group row">
                            <label for="ad_name" class="col-sm-2 col-form-label">管理者姓名</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="ad_name" name="ad_name" value="<?php echo !empty($res['ad_name']) ? $res['ad_name'] : ""; ?>" placeholder="管理者姓名" data-min="2" data-required<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="ad_title" class="col-sm-2 col-form-label">暱稱</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="ad_title" name="ad_title" value="<?php echo !empty($res['ad_title']) ? $res['ad_title'] : ""; ?>" placeholder="暱稱" data-min="2" data-required<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="ad_email" class="col-sm-2 col-form-label">E-mail</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="ad_email" name="ad_email" value="<?php echo !empty($res['ad_email']) ? $res['ad_email'] : ""; ?>" placeholder="E-mail" data-type="email" data-required<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="ad_phone" class="col-sm-2 col-form-label">聯絡電話</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="ad_phone" name="ad_phone" value="<?php echo !empty($res['ad_phone']) ? $res['ad_phone'] : ""; ?>" placeholder="聯絡電話" data-required<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="ad_line" class="col-sm-2 col-form-label">Line ID</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="ad_line" name="ad_line" value="<?php echo !empty($res['ad_line']) ? $res['ad_line'] : ""; ?>" placeholder="Line ID"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="ad_authlevel" class="col-sm-2 col-form-label">管理等級</label>
                            <div class="col-sm-10">
                              <select class="form-control" id="ad_authlevel" name="ad_authlevel" placeholder="管理等級" data-required<?php echo TdisAble; ?>>
                                <?php
                                  foreach($adJS['authlevel'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if((!empty($res['ad_authlevel']) && $res['ad_authlevel']==$key) || (empty($adID) && $key==0)) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="ad_status" class="col-sm-2 col-form-label">管理授權狀態</label>
                            <div class="col-sm-10">
                              <select class="form-control" id="ad_status" name="ad_status" placeholder="管理授權狀態" data-required<?php echo NdisAble; ?>>
                                <?php
                                  foreach($adJS['status'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if(!empty($res['ad_status']) && $res['ad_status']==$key) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          <hr>
                          <?php if(empty($adID)){ ?>
                          <div class="form-group row">
                            <label for="ad_status" class="col-sm-2 col-form-label">登入帳號</label>
                            <div class="input-group col-sm-10">
                              <input id="ad_account" name="ad_account" type="text" class="form-control" value="<?php echo !empty($res['ad_account']) ? $res['ad_account'] : ""; ?>" placeholder="登入帳號" data-required<?php echo !empty($adID) ? " readonly" : NreadOnly; ?>>
                              <div class="input-group-append">
                                <div class="input-group-text">
                                  <span class="fas fa-user"></span>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="ad_status" class="col-sm-2 col-form-label">登入密碼</label>
                            <div class="input-group col-sm-10">
                              <input id="ad_password" name="ad_password" type="password" class="form-control" placeholder="登入密碼" data-type="password" data-match="comfPass" data-required<?php echo NreadOnly; ?>>
                              <div class="input-group-append">
                                <div class="input-group-text">
                                  <span class="fas fa-lock"></span>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="ad_status" class="col-sm-2 col-form-label">密碼確認</label>
                            <div class="input-group col-sm-10">
                              <input id="comfPass" name="comfPass" type="password" class="form-control notSubmit" placeholder="密碼確認"<?php echo NreadOnly; ?>>
                              <div class="input-group-append">
                                <div class="input-group-text">
                                  <span class="fas fa-hand-peace"></span>
                                </div>
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
                    <div class="tab-pane" id="tabMenu2">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;管理權限只得在最高管理者身份下始得進行編輯。並且最高管理者的管理權限不被其他管理人員控管。</div>
                      <hr>
                      <div class="row" style="margin-bottom:8px;"><i class="fa fa-window-minimize text-warning"></i>&nbsp;&nbsp;預設權限<i class="fa fa-window-minimize text-success" style="margin-left:16px;"></i>&nbsp;&nbsp;自定義權限</div>
                      <!-- Start -->
                      <div class="row">
                        <div class="col-md-12">
                          <form id="form_tabMenu2" name="form_tabMenu2" action="../funcFiles/ad_act_func.php" method="post" data-formmode="authupdate" data-func="func_ad_authUpdate">
                            <?php
                              $pgARR = array();
                              $authpage = $admJS['sidebar'];
                              $apnum = count($authpage);
                              $i=0;
                              while($i < $apnum){
                                //$rs1 = DB->row("SELECT * FROM fsl_admin_auth WHERE ap_pagecode=? AND ad_id=?", array($authpage[$i]['pagecode'],$adID));
                                $authpage2 = isset($authpage[$i]['subgroup']) ? $authpage[$i]['subgroup'] : array();
                                $apnum2 = count($authpage2);
                              ?>
                                <div class="card card-outline card-secondary">
                                  <div class="card-body">
                                    <div class="row border-bottom table-active" style="padding-top: 8px;margin-top:-12px;">
                                      <div class="col-sm-7"><h5><?php echo $authpage[$i]['title']; ?></h5></div>
                                      <div class="col-sm-5" style="margin:0 -4px 0 4px;">
                                        <div class="row clearfix">
                                            <div class="d-inline col-3"><i class="fa fa-eye" alt="檢視" title="檢視"></i></div>
                                            <div class="d-inline col-3"><i class="fa fa-file-medical" alt="新增" title="新增"></i></div>
                                            <div class="d-inline col-3"><i class="fa fa-edit" alt="編輯" title="編輯"></i></div>
                                            <div class="d-inline col-3"><i class="fa fa-trash" alt="刪除" title="刪除"></i></div>
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
                                      $iCHKclass="icheck-success ";
                                    } else {
                                      $gF = isset($authpage2[$si]['default']) ? $authpage2[$si]['default'] : '0000';
                                      $viewCHK=(substr($gF,0,1)=='1') ? " checked" : "";
                                      $addCHK=(substr($gF,1,1)=='1') ? " checked" : "";
                                      $editCHK=(substr($gF,2,1)=='1') ? " checked" : "";
                                      $delCHK=(substr($gF,3,1)=='1') ? " checked" : "";
                                      $iCHKclass="icheck-warning ";
                                    }
                                  ?>
                                    <div class="row border-bottom">
                                      <div class="col-sm-7" style="padding-bottom:12px;"><?php echo $authpage2[$si]['title']; ?></div>
                                      <div class="col-sm-5">
                                      <?php if(isset($authpage2[$si]['default'])) { ?>
                                        <div id="adminPG_<?php echo $authpage2[$si]['pagecode']; ?>" name="adminPG_<?php echo $authpage2[$si]['pagecode']; ?>" class="row clearfix">
                                          <div class="<?php echo $iCHKclass; ?>d-inline col-3">
                                            <input type="checkbox" id="<?php echo $viewTag; ?>" name="<?php echo $viewTag; ?>" value="1"<?php echo $viewCHK.TdisAble; ?>><label for="<?php echo $viewTag; ?>"></label>
                                          </div>
                                          <div class="<?php echo $iCHKclass; ?>d-inline col-3">
                                            <input type="checkbox" id="<?php echo $addTag; ?>" name="<?php echo $addTag; ?>" value="1"<?php echo $addCHK.TdisAble; ?>><label for="<?php echo $addTag; ?>"></label>
                                          </div>
                                          <div class="<?php echo $iCHKclass; ?>d-inline col-3">
                                            <input type="checkbox" id="<?php echo $editTag; ?>" name="<?php echo $editTag; ?>" value="1"<?php echo $editCHK.TdisAble; ?>><label for="<?php echo $editTag; ?>"></label>
                                          </div>
                                          <div class="<?php echo $iCHKclass; ?>d-inline col-3">
                                            <input type="checkbox" id="<?php echo $delTag; ?>" name="<?php echo $delTag; ?>" value="1"<?php echo $delCHK.TdisAble; ?>><label for="<?php echo $delTag; ?>"></label>
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
                                        $iCHKclass = $rs3 ? "icheck-success " : "icheck-warning ";
                                        $viewTag = "view_".$authpage3[$ti]['pagecode'];
                                        $addTag = "add_".$authpage3[$ti]['pagecode'];
                                        $editTag = "edit_".$authpage3[$ti]['pagecode'];
                                        $delTag = "del_".$authpage3[$ti]['pagecode'];
                                        if($rs3){
                                          $viewCHK=($rs3['auth_browse']==1) ? " checked" : "";
                                          $addCHK=($rs3['auth_addnew']==1) ? " checked" : "";
                                          $editCHK=($rs3['auth_edit']==1) ? " checked" : "";
                                          $delCHK=($rs3['auth_delete']==1) ? " checked" : "";
                                          $iCHKclass="icheck-success ";
                                        } else {
                                          $gF = isset($authpage3[$ti]['default']) ? $authpage3[$ti]['default'] : '0000';
                                          $viewCHK=(substr($gF,0,1)=='1') ? " checked" : "";
                                          $addCHK=(substr($gF,1,1)=='1') ? " checked" : "";
                                          $editCHK=(substr($gF,2,1)=='1') ? " checked" : "";
                                          $delCHK=(substr($gF,3,1)=='1') ? " checked" : "";
                                          $iCHKclass="icheck-warning ";
                                        }
                                      ?>
                                        <div class="row border-bottom">
                                          <div class="col-sm-7" style="padding-left:24px;"><?php echo $authpage3[$ti]['title']; ?></div>
                                          <div class="col-sm-5">
                                          <?php if(isset($authpage3[$ti]['default'])) { ?>
                                            <div id="adminPG_<?php echo $authpage3[$ti]['pagecode']; ?>" name="adminPG_<?php echo $authpage3[$ti]['pagecode']; ?>" class="row clearfix">
                                              <div class="<?php echo $iCHKclass; ?>d-inline col-3">
                                                <input type="checkbox" id="<?php echo $viewTag; ?>" name="<?php echo $viewTag; ?>" value="1"<?php echo $viewCHK.TdisAble; ?>><label for="<?php echo $viewTag; ?>"></label>
                                              </div>
                                              <div class="<?php echo $iCHKclass; ?>d-inline col-3">
                                                <input type="checkbox" id="<?php echo $addTag; ?>" name="<?php echo $addTag; ?>" value="1"<?php echo $addCHK.TdisAble; ?>><label for="<?php echo $addTag; ?>"></label>
                                              </div>
                                              <div class="<?php echo $iCHKclass; ?>d-inline col-3">
                                                <input type="checkbox" id="<?php echo $editTag; ?>" name="<?php echo $editTag; ?>" value="1"<?php echo $editCHK.TdisAble; ?>><label for="<?php echo $editTag; ?>"></label>
                                              </div>
                                              <div class="<?php echo $iCHKclass; ?>d-inline col-3">
                                                <input type="checkbox" id="<?php echo $delTag; ?>" name="<?php echo $delTag; ?>" value="1"<?php echo $delCHK.TdisAble; ?>><label for="<?php echo $delTag; ?>"></label>
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
                    <div class="tab-pane" id="tabMenu3">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;管理者的帳號密碼除了管理者本人以及最高管理權限者之外，其他管理者無權更改（管理帳號目前無法在操作介面上更改）。</div>
                      <hr>
                      <!-- /.card-header -->
                      <div class="card-body" style="background-color:#DFE3E8;">
                        <strong><i class="fas fa-user mr-1"></i>登入帳號</strong>
                        <div class="input-group mb-3">
                          <input id="ad_account" name="ad_account" type="text" class="form-control" value="<?php echo !empty($res['ad_account']) ? $res['ad_account'] : ""; ?>" placeholder="登入帳號" data-required<?php echo !empty($adID) ? " readonly" : NreadOnly; ?>>
                          <div class="input-group-append">
                            <div class="input-group-text">
                              <span class="fas fa-user"></span>
                            </div>
                          </div>
                        </div>
                        <hr>
                        <strong><i class="fas fa-lock mr-1"></i>新登入密碼</strong>
                        <form id="form_tabMenu3" name="form_tabMenu3" action="../funcFiles/ad_act_func.php" method="post" data-formmode="passupdate" data-func="func_ad_passUpdate">
                          <div class="input-group mb-3">
                            <input id="ad_password" name="ad_password" type="password" class="form-control" placeholder="新登入密碼" data-type="password" data-match="comfPass" data-required<?php echo CF_AD_ID==$adID ? NreadOnly : TreadOnly; ?>>
                            <div class="input-group-append">
                              <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                              </div>
                            </div>
                          </div>
                          <hr>
                          <strong><i class="fas fa-hand-peace mr-1"></i> 密碼確認</strong>
                          <div class="input-group mb-3">
                            <input id="comfPass" name="comfPass" type="password" class="form-control" placeholder="密碼確認"<?php echo CF_AD_ID==$adID ? NreadOnly : TreadOnly; ?>>
                            <div class="input-group-append">
                              <div class="input-group-text">
                                <span class="fas fa-hand-peace"></span>
                              </div>
                            </div>
                          </div>
                        </form>
                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第三分頁密碼管理 -->
                    <!-- 第四分頁管理記錄 adminlog -->
                    <div class="tab-pane" id="tabMenu4">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;本會員的中庸本系統的操作記錄列表，本列表用於偵錯、改進、分析甚至於究責，所以一旦寫入記錄便無法更改，操作記錄將於多處以及多型態備份。</div>
                      <hr>
                      <!-- card-body -->
                      <div class="card-body">
                        <table id="dataTable_logList" class="table table-bordered table-striped table-hover" data-butClass="gray" data-pagemax="25" data-dirto="" data-search>
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
                              //$sql="SELECT * FROM data_logs WHERE lg_forid=? AND lg_for=?";
                              //$srr = DB->query($sql,array($adID,1));
                              //$i = 0; //以$i為$nameList這個陣列的索引值
                              //while($i<count($srr)){ 

                              foreach ($sr as $log) {
                                ?>
                                <tr>
                                  <td><?php echo $log['lg_code']; ?></td>
                                  <td><?php echo $log['lg_created']; ?></td>
                                  <td><?php
                                  //$oStr = DB->single("SELECT lg_string FROM sys_logstrings WHERE lg_code=?",array($log['lg_code']));
                                  echo _fn_getLogString($log['lg_params'],$log['lg_string']);
                                  //echo _fn_getLogString($log['lg_params'],$oStr);
                                  //echo json_decode($srr[$i]['lg_params']);
                                  ?></td>
                                </tr>
                              <?php 
                                //$i++;
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
                    <!-- /第四分頁管理記錄 -->
                    <!-- 第五分頁訊息傳遞 sysinfo -->
                    <div class="tab-pane" id="tabMenu5">
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

      </section>
      <!-- /主要內容區域 ------------------------------------ -->
    </div>
    <!-- /頁內全頁內容 -->

    <!-- jQuery -->
    <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>

    <!-- 右側功能控制區 -->
      <aside class="control-sidebar control-sidebar-dark">
        <!-- 功能區內容 ------------------------------------ -->

        <!-- /功能區內容 ----------------------------------- -->
      </aside>
    <!-- /右側功能控制區 -->
  </div>
  <!-- /全頁框 -->
<!-- ------------------------ Scripts Link ------------------------ -->
<!-- Bootstrap 5 -->
<script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
<!-- DataTables3 & Plugins -->
<script src="../pluginFiles/datatables3/datatables.min.js"></script>
<!-- ezValidation -->
<script src="../pluginFiles/validator/validator.js<?php echo '?t='.time(); ?>"></script>
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
  //*************************** *********************************//
  //***************** JAVASCRIPTS 全域調用參數設置 *****************//
  //*************************** *********************************//

  var adobj = <?php echo json_encode($adJS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
  //*************************** *********************************//
  //*************** JAVASCRIPTS FUNCTIONs 全域調用 ***************//
  //*************** 可接收include檔案要求後自定義動作 ***************//
  //*************************** *********************************//

  // 當「返回」按鈕按下時的動作
  function doReDirect(){
    window.history.back();
  }
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
  // 當「刪除」按鈕按下時的動作
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
	// 當「重置」按鈕按下時的動作
	function doButReset(){
		location.reload(true);
	}

  ///////////////////////////// AJAX 表單處理 ////////////////////////////////
  function ajaxPOST(frm){
    //$('.getUpFileMine').html(cropImage64+' [END]');return false;
      var frm = $('#form_'+$('.tab-pane.active').prop("id"));
      var accE = frm.data('func');
      if(0==parseInt('<?php echo $adID; ?>')){
        accE = 'func_ad_addnew';
      }
      var url = frm.attr('action');
      var typE = frm.attr('method');
      var datA = frm.find(":input").not(".notSubmit").serializeArray();
      var imgBase64 = $('#main_crop_area').data('cropImage64') || '';
      $.ajax({
          type: typE,
          url: url,
          data: {
            'arrDatA':datA, 
            //'arrDatB':datB, 
            'formAcce':accE,
            'imgUp': imgBase64,
            'token': getTokenVal(), 
            'adID':'<?php echo $adID; ?>'
          },
          dataType: 'json', 
          async: false, 
        success: function (data) {
          console.log(data);
            if(accE=='func_ad_addnew') {
              if(data['RTN']==1) {
                window.location.replace("ad_profile.php?currID="+data['newID']);
                return false;
              }
            }
            window.parent.toastr[data['msgType']](data['msgStr']);
            if(accE=='func_ad_infoUpdate'){
              if (data['RTN']==1) {
                var newLvl = adobj[$('#ad_authlevel').val()];
                var newSta = $('#ad_status').val();
                switch (newSta){
                  case '0': 
                    $('.box-profile > ul > li:nth-child(1) > a').addClass( "text-danger" );
                  break;
                  case '1': 
                    $('.box-profile > ul > li:nth-child(1) > a').removeClass( "text-danger" );
                  break;
                }
                newSta = adobj[newSta];
                $('.profile-username').html($('#ad_name').val());
                $('.profile-username + p').html(newLvl);
                $('.box-profile > ul > li:nth-child(1) > a').html(newSta);
                $('.box-profile > ul > li:nth-child(2) > a').html($('#ad_title').val());
                $('.box-profile > ul > li:nth-child(3) > a').html($('#ad_email').val());
                $('.box-profile > ul > li:nth-child(4) > a').html($('#ad_phone').val());
                $('.box-profile > ul > li:nth-child(5) > a').html($('#ad_line').val());
                $('.box-profile > ul > li:nth-child(6) > a').html(data['upTime']);
                $('#ad_terms').prop('checked', false);
                if(data['newUpPhoto']!=''){
                  $(".adTopNaviPhoto").attr("src",data['newUpPhoto']+'?'+$.now());
                }
                /* **  重置表單異動數據  ** */
                cropImage64='';//清除圖檔資料
                doSerialize();
              } else {
                //doButReset(false);
              }
              //window.parent.$('#topBut_save').removeClass('active');
            } else if(accE=='func_ad_passUpdate') {
              $('#form_tabMenu3')[0].reset();
              window.parent.$('#topBut_save').removeClass('active');
            } else if(accE=='func_ad_authUpdate') {
              var colorCHG = data['colorCHG'];
              $.each(colorCHG, function(index, value) {
                var elM = '#' + index;
                $(elM + " > div").removeClass( "icheck-warning" ).removeClass( "icheck-success" ).addClass( value );
              });
              window.parent.$('#topBut_save').removeClass('active');
            }
          },
          error: function (data) {
            console.log(data);
            window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
          }
      });
  }

  //*************************** *********************************//
  //***************** jQuery FUNCTIONs 區域調用 ******************//
  //*************************** *********************************//
  $(document).ready(function() {
    // iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });
    //-----------------------------------------
    var logListTB = datatableCreate('dataTable_logList');
    logListTB.button().add(logListTB.buttons().count(), {
          className:'advAddBut-gray',
            text: '<i class="fa fa-solid fa-notes-medical"> 新增投放規則</i>',
            action: function(e) {
              $('#rulesModal').show().addClass('show');
              $('#rulesModal .modal-title').html('新增投放規則');
              $('#rulesModal #rulUpBut').html('新增');
              $('#rulesModal #rulDelBut').css("visibility", "hidden");
              $('#form_ruleupdate').attr('data-ruleid','0');
              $('#rulesModal .rule-container').load('include_adrules.php');
            }
    });   
    // 內頁右上方小導航返回列表頁
    $('li.prevLink').on('click', function(){
      window.location.replace(getCurrentURI('','ad_list.php'));
    });
    //-------- 加載完成後更改外框主頁中樹狀目錄開合狀態 --------//
    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
////////////////////////////////////////////////////////////////
  });

</script>
</body>
</html>
