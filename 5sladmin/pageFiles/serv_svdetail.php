<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_BOOK');
define('PAGE_ITEM','ITEM_SERV');
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//主要資料表欄位資料取得
$svID=currID;
//
$res = $DB->row("SELECT * FROM sv_service WHERE sv_id=?",array($svID));
//$pres = $DB->row("SELECT * FROM advi_pack WHERE advp_id=?",array($res['advp_id']));
//$mres=$DB->row("SELECT * FROM fsl_members AS a INNER JOIN mb_info AS b ON b.mb_id=a.mb_id WHERE a.mb_id=?",array($res['mb_id']));
//$mdres = $DB->row("SELECT * FROM fsl_merchandise WHERE merch_id=?",array($res['merch_id']));
//$DB->closeConnection();

//功能陣列建立
// Get the contents of the JSON file
$geJS = _fn_getCachedJsonData("../jsonFiles/general.json");
$svJS = _fn_getCachedJsonData("../jsonFiles/service.json");
$mbJS = _fn_getCachedJsonData("../jsonFiles/member.json");
//$mdGet=file_get_contents("../jsonFiles/merchandise.json");
//$mdJS = json_decode($mdGet,true);
$mbArr = null;
if(!empty($res['mb_id'])){
  $mbArr = $DB->row("SELECT mb_title,mb_level FROM fsl_members WHERE mb_id=?",array($res['mb_id']));
}
//--------------------- 評分計算 -----------------------
$counT="SELECT AVG((rat_s1 + rat_s2 + rat_s3 + rat_s4 + rat_s5)/5) AS totAVE, AVG(rat_s1) AS S1, AVG(rat_s2) AS S2, AVG(rat_s3) AS S3, AVG(rat_s4) AS S4, AVG(rat_s5) AS S5 FROM fsl_rating WHERE rat_forid=? AND rat_for=?";
$cnt = $DB->query($counT,array($svID,4));
$S1 = !empty($cnt[0]['S1']) ? floatval($cnt[0]['S1']) : 0;
$S2 = !empty($cnt[0]['S2']) ? floatval($cnt[0]['S2']) : 0;
$S3 = !empty($cnt[0]['S3']) ? floatval($cnt[0]['S3']) : 0;
$S4 = !empty($cnt[0]['S4']) ? floatval($cnt[0]['S4']) : 0;
$S5 = !empty($cnt[0]['S5']) ? floatval($cnt[0]['S5']) : 0;
$totAVE = !empty($cnt[0]['totAVE']) ? floatval($cnt[0]['totAVE']) : 0;

//代表圖像設定
if(!empty($svID)&&file_exists("../uploadFiles/sv/svphoto_".$svID."_thumb.jpg")){
  $cropImgDisplay = 'show';
  $mainimgpath = '../uploadFiles/sv/svphoto_'.$svID.'_thumb.jpg';
  $mainimgchk = 'svphoto_'.$svID.'_.jpg';
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
  <link rel="icon" href="../../imageFiles/favicon.ico" type="image/ico" />
	
  <title> Fifth Season Life 人生第五季 | 開放式服務提案詳細資料 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="../pluginFiles/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-bs4/css/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-responsive/css/responsive.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-buttons/css/buttons.bootstrap4.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css<?php echo '?t='.time(); ?>">
  <!-- Select2 -->
  <link rel="stylesheet" href="../pluginFiles/select2/css/select2.min.css">
  <link rel="stylesheet" href="../pluginFiles/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <!-- Dropzone JS style -->
  <link rel="stylesheet" href="../pluginFiles/dropzone/dropzone.css" />
  <!-- Cropper JS style -->
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <!-- suneditor -->
  <link rel="stylesheet" href="../pluginFiles/suneditor/dist/css/suneditor.min.css">
  <!-- codeMirror -->
  <link rel="stylesheet" href="../pluginFiles/codemirror/lib/codemirror.min.css">
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css<?php echo '?t='.time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.lightbox.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css<?php echo '?t='.time(); ?>">

<!-- -------------------------- /CSS Link -------------------------- -->
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
              <h4 class="m-0"><?php echo !empty($svID) ? "編輯" : "新增"; ?>開放式服務提案資料<span style="font-size:large;">【ID: <?php echo $svID; ?>】</span></h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                  <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                  <li class="breadcrumb-item">開放式服務管理</li>
                  <li class="breadcrumb-item prevLink" alt="返回列表" title="返回列表" onClick="window.location.replace('serv_svlist.php');">服務項目提案紀錄</a></li>
                <li class="breadcrumb-item active">服務項目詳細資料</li>
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
                          <img id="avatarImage" name="avatarImage" class="img-responsive img-fluid img-rounded imgEDicon" src="<?php echo $mainimgpath."?".time(); ?>" alt="服務項目代表圖" title="服務項目代表圖"/>
                          <div class="overlay">
                            <div class="overtext"><?php echo !empty($svID) ? "編輯" : "新增"; ?>代表圖</div>
                          </div>
                          <input type="file" name="image" class="image" id="cropUploadImage" style="display:none" crop-width="800" crop-height="600">
                        </label>
                      </form>
                    </div>
                  </div>
                </div>
              </div>

              <div class="card card-primary card-outline shadow">
                <div class="card-body box-profile row fromWhere" onclick="window.location.replace('mem_profile.php?currID=<?php echo $res['mb_id']; ?>');" alt="進入提案者資料頁" title="進入提案者資料頁">
                  <div class="col-sm-5">
                    <div class="image_area text-left">
                        <label for="upload_image" style="padding-left:8px;">
                          <img id="uploaded_image" name="uploaded_image" class="img-responsive profile-user-img img-fluid img-circle imgEDicon" style="width:160px;" src="<?php
                          if(!empty($res['mb_id']) && file_exists("../uploadFiles/mb/mbphoto_".$res['mb_id']."_.jpg")) {
                            echo "../uploadFiles/mb/mbphoto_".$res['mb_id']."_.jpg?".time();
                          } else echo "../imageFiles/nopic_user_200x200.png";
                          ?>" alt="會員頭像" title="會員頭像"/>
                        </label>
                    </div>
                  </div>
                  <div class="col-sm-7">
                    <p>提案者：</p>
                    <hr>
                    <h4 class="profile-username"><?php echo !empty($mbArr['mb_title']) ? $mbArr['mb_title'] : ''; ?></h4>
                    <p class="text-muted"><?php echo !empty($mbArr['mb_level']) ? $mbJS['levels'][$mbArr['mb_level']]['title'] : ''; ?></p>
                  </div>
                </div>
              </div>


              <div id="reviewstep_show" name="reviewstep_show" class="card card-warning card-outline">
                <div class="card-header">
                  <h3 class="card-title">審核進度 <span style="font-size:140%;color:#ECC244;"><?php echo $geJS['reviewstatus'][$res['sv_status']]; ?></span></h3>
                </div>
                <div class="card-body">
                  <?php echo $geJS['review'][$res['sv_status']]; ?>
                </div>
              </div>
              <!-- /.card -->
              <?php
              if(intval($res['sv_status'])>=6 && intval($res['sv_status'])<=8) {
                $nowStatus = $res['sv_status'];
                $nextStatus = $nowStatus==8 ? 1 : $nowStatus+1;
                $nextText = $nowStatus==8 ? "審結通過上架" : "進入".$geJS['status'][$nowStatus+1];
                $butClass = $nowStatus==8 ? " bg-gradient-success" : " bg-gradient-info";
                $butIcon = $nowStatus==8 ? " fa-regular fa-thumbs-up" : " fa-file-signature";
              ?>
                <div id="butApply_group" name="butApply_group" class="form-group row">
                  <div class="col-sm-6">
                    <button type="button" id="but_doreview" name="but_doreview" class="btn btn-block btn-lg<?php echo $butClass; ?>" data-status="<?php echo $nowStatus; ?>" style="cursor:pointer;height:100px;"><i class="nav-icon fas fa-2x<?php echo $butIcon; ?>"></i><br><?php echo $nextText; ?></button>
                  </div>
                  <div class="col-sm-6">
                    <button type="button" id="but_doreject" name="but_doreject" class="btn btn-block bg-gradient-secondary btn-lg" style="cursor:pointer;height:100px;"><i class="nav-icon fas fa-2x fa-hand"></i><br>退回</button>
                  </div>
                </div>
              <?php } ?>

            </div>
            <!-- /.col -->
            <div class="col-md-9">
              <div class="card card-secondary">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#tabMenu1" data-index="1" data-toggle="tab" data-topbut="1">服務提案內容</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu2" data-index="2" data-toggle="tab" data-topbut="0">審核流程紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu3" data-index="3" data-toggle="tab" data-topbut="0">檔案及鏈結管理</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu4" data-index="4" data-toggle="tab" data-topbut="0">服務排程表</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu5" data-index="5" data-toggle="tab" data-topbut="0">服務紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu6" data-index="6" data-toggle="tab" data-topbut="0">評價紀錄</a></li>
                  </ul>
                </div><!-- /.card-header -->
                <div class="card-body">
                  <div class="tab-content">
                    <!-- 第一分頁【主要】資料修改 -->
                    <div class="tab-pane active" id="tabMenu1">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;提案內容於必要修改時，原則由提案人自行修改並重新送審，除非公告規則載明，否則管理者僅負檢視審查權力，並無直接修改內容的權力。</div>
                      <hr>
                      <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/sv_act_func.php" method="post" data-formmode="infoupdate" data-func="func_sv_infoUpdate">
                        <input type="hidden" id="mainimgchk" name="mainimgchk" class="notSubmit" value="<?php echo $mainimgchk; ?>">
                        <div class="form-group row">
                          <label for="sv_title" class="col-sm-2 col-form-label">服務名稱</label>
                          <div class="col-sm-10">
                            <input type="text" class="form-control" id="sv_title" name="sv_title" value="<?php echo !empty($res['sv_title']) ? $res['sv_title'] : ""; ?>" placeholder="服務名稱"<?php echo NreadOnly; ?>>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="sv_subscript" class="col-sm-2 col-form-label">名稱下標</label>
                          <div class="col-sm-10">
                            <input type="text" class="form-control" id="sv_subscript" name="sv_subscript" value="<?php echo !empty($res['sv_subscript']) ? $res['sv_subscript'] : ""; ?>" placeholder="名稱下標"<?php echo NreadOnly; ?>>
                          </div>
                        </div>

                        <div class="form-group row">
                          <label for="sv_mode" class="col-sm-2 col-form-label">服務模式</label>
                          <div class="col-sm-4">
                            <select class="form-control" id="sv_mode" name="sv_mode"<?php echo NdisAble; ?>>
                                  <?php
                                    foreach($svJS['svmode'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(!empty($res['sv_mode']) && $res['sv_mode']==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                  ?>
                            </select>
                          </div>
                          <label for="sv_type" class="col-sm-2 col-form-label">服務種類</label>
                          <div class="col-sm-4">
                            <select class="form-control" id="sv_type" name="sv_type"<?php echo NdisAble; ?>>
                                  <?php
                                  if(!empty($svType)){
                                    echo '<option value="'.$svType.'"';
                                    echo '>'.$svJS['svtype'][$svType].'</option>';
                                  } else {
                                    foreach($svJS['svtype'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(!empty($res['sv_type']) && $res['sv_type']==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                  }
                                  ?>
                            </select>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="sv_cate" class="col-sm-2 col-form-label">內容分類</label>
                          <div class="col-sm-10">
                            <select class="form-control" id="sv_cate" name="sv_cate"<?php echo NdisAble; ?>>
                                  <?php
                                    foreach($svJS['svcate'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(!empty($res['sv_cate']) && $res['sv_cate']==$key) echo " selected";
                                      echo '>'.$value.'</option>';
                                    }
                                  ?>
                            </select>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="sv_cotype" class="col-sm-2 col-form-label">專業分類</label>
                          <div class="col-sm-10 select2-primary">
                            <select class="select2" id="sv_cotype" name="sv_cotype" multiple="multiple" data-placeholder="專業分類" data-dropdown-css-class="select2-primary" style="width: 100%;"<?php echo NdisAble; ?>>
                              <?php
                                $cotypeJS=!empty($res['sv_cotype'])?json_decode($res['sv_cotype']):array();
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
                          <label for="sv_coexpert" class="col-sm-2 col-form-label">專業服務項目</label>
                          <div class="col-sm-10 select2-primary">
                            <select class="select2" id="sv_coexpert" name="sv_coexpert" multiple="multiple" data-placeholder="選擇專業服務項目" data-dropdown-css-class="select2-primary" style="width: 100%;"<?php
                                if(empty($res['sv_coexpert']) || $res['sv_coexpert']=="[]"){ echo " disabled='disabled'"; }
                                else echo NdisAble; 
                                ?>>
                            <?php
                                $coexJS=!empty($res['sv_coexpert']) ? json_decode($res['sv_coexpert']) : array();
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
                          <label for="sv_brief" class="col-sm-2 col-form-label">服務內容簡述</label>
                          <div class="col-sm-10"><textarea id="sv_brief" name="sv_brief" cols="4" style="width:100%;" placeholder="服務內容簡述"<?php echo NreadOnly; ?>><?php echo !empty($res['sv_brief'])?$res['sv_brief']:''; ?></textarea></div>
                        </div>
                        <div class="form-group row">
                          <label for="sv_exinfo" class="col-sm-2 col-form-label">素材說明</label>
                          <div class="col-sm-10"><textarea id="sv_exinfo" name="sv_exinfo" cols="4" style="width:100%;" placeholder="項目文案"<?php echo NreadOnly; ?>><?php echo !empty($res['sv_exinfo'])?$res['sv_exinfo']:''; ?></textarea></div>
                        </div>
                        <div class="form-group row">
                          <label for="sv_intro" class="col-sm-2 col-form-label">詳細介紹</label>
                          <div class="col-sm-10"><textarea id="sv_intro" name="sv_intro" cols="6" style="width:100%;" placeholder="詳細介紹"<?php echo NreadOnly; ?>><?php echo !empty($res['sv_intro'])?$res['sv_intro']:''; ?></textarea></div>
                        </div>
                        <hr style="margin-top:32px;"><div class="hr-more">資費設定</div>  
                        <div class="form-group row">
                          <label for="sv_payfor" class="col-sm-2 col-form-label">有償服務</label>
                          <div class="col-sm-4">
                            <select class="form-control" id="sv_payfor" name="sv_payfor"<?php echo NdisAble; ?>>
                            <?php
                              foreach($svJS['yesorno'] as $key => $value){
                                echo '<option value="'.$key.'"';
                                  if($key==""){ echo " disabled"; }
                                  if(!empty($res['sv_payfor']) && $res['sv_payfor']==$key) echo " selected";
                                  echo '>'.$value.'</option>';
                              }
                            ?>
                            </select>
                          </div>
                          <label for="sv_paytype" class="col-sm-2 col-form-label">支付模式</label>
                          <div class="col-sm-4">
                            <select class="form-control" id="sv_paytype" name="sv_paytype"<?php
                                if(empty($res['sv_payfor']) || $res['sv_payfor']==0){ echo " disabled='disabled'"; }
                                else echo NdisAble; 
                                ?>>
                            <?php
                              foreach($svJS['paytype'] as $key => $value){
                                echo '<option value="'.$key.'"';
                                if($key==""){ echo " disabled"; }
                                if(!empty($res['sv_paytype']) && $res['sv_paytype']==$key) echo " selected";
                                echo '>'.$value.'</option>';
                              }
                            ?>
                            </select>
                          </div>
                        </div>

                        <div class="form-group row">
                          <label for="sv_every" class="col-sm-2 col-form-label">計費基數</label>
                          <div class="col-sm-4">
                            <div class="input-group">
                              <div class="input-group-append"><span class="input-group-text">每</span></div>
                              <input type="text" class="form-control" id="sv_every" name="sv_every" value="<?php echo !empty($res['sv_every'])?$res['sv_every']:'0'; ?>" placeholder="計費基數"<?php
                                if(empty($res['sv_payfor']) || $res['sv_payfor']==0){ echo " disabled='disabled'"; }
                                else echo NdisAble; 
                                ?>>
                              <div class="input-group-append"><span id="everyUnit" class="input-group-text">個基數</span></div>
                            </div>
                          </div>

                          <label for="sv_cost" class="col-sm-2 col-form-label">支付點數</label>
                          <div class="col-sm-4">
                            <div class="input-group">
                              <input type="text" class="form-control" id="sv_cost" name="sv_cost" value="<?php echo !empty($res['sv_cost'])?$res['sv_cost']:'0'; ?>" placeholder="支付點數"<?php
                                if(empty($res['sv_payfor']) || $res['sv_payfor']==0){ echo " disabled='disabled'"; }
                                else echo NdisAble; 
                                ?>>
                              <div class="input-group-append"><span class="input-group-text">點</span></div>
                            </div>
                          </div>
                        </div>

                        <div class="form-group row">
                          <label for="sv_authmode" class="col-sm-2 col-form-label">授權模式</label>
                          <div class="col-sm-4">
                            <select class="form-control" id="sv_authmode" name="sv_authmode"<?php echo NdisAble; ?>>
                            <?php
                              foreach($svJS['authmode'] as $key => $value){
                                echo '<option value="'.$key.'"';
                                if($key==""){ echo " disabled"; }
                                if(!empty($res['sv_authmode']) && $res['sv_authmode']==$key) echo " selected";
                                echo '>'.$value.'</option>';
                              }
                            ?>
                            </select>
                          </div>
                          <label for="sv_authcount" class="col-sm-2 col-form-label">授權計數</label>
                          <div class="col-sm-4">
                            <div class="input-group">
                              <input type="text" class="form-control" id="sv_authcount" name="sv_authcount" value="<?php echo !empty($res['sv_authcount'])?$res['sv_authcount']:'0'; ?>" placeholder="授權計數"<?php
                                if(empty($res['sv_authmode']) || $res['sv_authmode']==0){ echo " disabled='disabled'"; }
                                else echo NdisAble; 
                                ?>>
                              <div class="input-group-append"><span id="authUnit" class="input-group-text">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></div>
                            </div>
                          </div>
                        </div>

                        <div class="form-group row">
                          <label for="sv_capacity" class="col-sm-2 col-form-label">服務人數</label>
                          <div class="col-sm-4">
                            <div class="input-group">
                              <div class="input-group-append"><span class="input-group-text">同時至多</span></div>
                              <input type="text" class="form-control" id="sv_capacity" name="sv_capacity" value="<?php echo !empty($res['sv_capacity'])?$res['sv_capacity']:'0'; ?>" placeholder="服務人數"<?php echo NreadOnly; ?>>
                              <div class="input-group-append"><span class="input-group-text">人</span></div>
                            </div>
                          </div>
                          <label for="sv_notify" class="col-sm-2 col-form-label">是否通知</label>
                          <div class="col-sm-4">
                            <select class="form-control" id="sv_notify" name="sv_notify"<?php echo NdisAble; ?>>
                              <?php
                                foreach($svJS['yesorno'] as $key => $value){
                                  echo '<option value="'.$key.'"';
                                  if($key==""){ echo " disabled"; }
                                  if(!empty($res['sv_notify']) && $res['sv_notify']==$key) echo " selected";
                                  echo '>'.$value.'</option>';
                                }
                              ?>
                            </select>
                          </div>
                        </div>
                      
                        <div class="form-group row">
                          <label for="sv_prepare" class="col-sm-2 col-form-label">素材準備</label>
                          <div class="col-sm-10">
                            <select class="form-control" id="sv_prepare" name="sv_prepare"<?php echo NdisAble; ?>>
                              <?php
                                foreach($svJS['prepare'] as $key => $value){
                                  echo '<option value="'.$key.'"';
                                    if($key==""){ echo " disabled"; }
                                    if(!empty($res['sv_prepare']) && $res['sv_prepare']==$key) echo " selected";
                                    echo '>'.$value.'</option>';
                                }
                              ?>
                            </select>
                          </div>
                        </div>
                      </form>
                    </div>
                    <!-- /第一分頁【主要】資料修改 -->
                    <!-- 第二分頁【主要】主體編輯 -->
                    <div class="tab-pane" id="tabMenu2">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;此頁表列出審核過程中的所有紀錄，以供審核人以及監察人了解整個過程動機。</div>
                      <hr>
                      <div class="card-body">

                        <table id="revTableList" class="table table-bordered table-striped table-hover">
                          <thead>
                            <tr>
                              <th>日期</th>
                              <th>(自)階段</th>
                              <th>動作</th>
                              <th>(至)狀態</th>
                              <th>註記內容</th>
                              <th>審核人</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                              $rvarr = $DB->query("SELECT * FROM data_reviews WHERE rv_for=? AND rv_forid=?",array(4,$svID));
                              $i = 0; //以$i為$nameList這個陣列的索引值
                              while($i<count($rvarr)){
                                $adnick = $DB->single("SELECT ad_title FROM fsl_administrator WHERE ad_id=?",array($rvarr[$i]['ad_id']));
                                ?>
                                <tr>
                                  <td><?php echo $rvarr[$i]['rv_created'] ?? '&nbsp;'; ?></td>
                                  <td><?php echo $geJS['status'][$rvarr[$i]['rv_review']] ?? '&nbsp;'; ?></td>
                                  <td><span class="right badge<?php echo $geJS['actionbadge'][$rvarr[$i]['rv_status']] ?? '&nbsp;'; ?>" style="font-size:100%;"><?php echo $geJS['reviewaction'][$rvarr[$i]['rv_status']]; ?></span></td>
                                  <td><?php echo $geJS['status'][$rvarr[$i]['rv_status']] ?? '&nbsp;'; ?></td>
                                  <td><?php echo $rvarr[$i]['rv_content'] ?? '&nbsp;'; ?></td>
                                  <td><?php echo $adnick; ?></td>
                                </tr>
                            <?php 
                                $i++;
                                } 
                              ?>
                          </tbody>
                          <tfoot>
                            <tr>
                              <th>日期</th>
                              <th>(自)階段</th>
                              <th>動作</th>
                              <th>(至)狀態</th>
                              <th>註記內容</th>
                              <th>審核人</th>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                    </div>
                    <!-- /第二分頁【主要】主體編輯 -->
                    <!-- 第三分頁檔案及鏈結管理 -->
                    <div class="tab-pane" id="tabMenu3">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;XXXXX</div>
                      <hr>
                      <!-- /.card-header -->
                      <div class="card-body" style="background-color:#DFE3E8;">


                      </div>
                      <!-- /.card-body -->檔案及鏈結管理 服務排程表 服務紀錄 評價紀錄
                    </div>
                    <!-- /第三分頁檔案及鏈結管理 -->
                    <!-- 第四分頁服務排程表 -->
                    <div class="tab-pane" id="tabMenu4">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;XXXX。</div>
                      <hr>
                      <!-- card-body -->
                      <div class="card-body">

                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第四分頁服務排程表 -->
                    <!-- 第五分頁服務紀錄 -->
                    <div class="tab-pane" id="tabMenu5">

                      <div style="font-size:0.85em;line-height:1.4em;">
                        <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;服務紀錄列表。</div>
                        <hr>
                        <!-- card-body -->
                        <div class="card-body">
                          <table id="dataTable_servelist" class="table table-bordered table-striped table-hover" data-pagemax="10" data-jumpto="0" data-search>
                            <thead>
                              <tr class="gray">
                                <th>日期</th>
                                <th>來自</th>
                                <!-- <th>類別</th> -->
                                <th>A</th>
                                <th>B</th>
                                <th>C</th>
                                <th>D</th>
                                <th>E</th>
                                <th>評論</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php
                                $sql="SELECT * FROM fsl_rating WHERE rat_forid=? AND rat_for=?";
                                $sr = $DB->query($sql,array($svID,4));
                                $i = 0; //以$i為$nameList這個陣列的索引值
                                while($i<count($sr)){ 
                                  ?>
                                  <tr>
                                    <td><?php echo $sr[$i]['rat_create']; ?></td>
                                    <td><?php echo $sr[$i]['md_id']; ?></td>
                                    <td><?php echo $sr[$i]['rat_s1']; ?></td>
                                    <td><?php echo $sr[$i]['rat_s2']; ?></td>
                                    <td><?php echo $sr[$i]['rat_s3']; ?></td>
                                    <td><?php echo $sr[$i]['rat_s4']; ?></td>
                                    <td><?php echo $sr[$i]['rat_s5']; ?></td>
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
                              <tr class="gray">
                                <th>總平均</th>
                                <th></th>
                                <!-- <th></th> -->
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
                    <!-- /第五分頁服務紀錄 -->
                    <!-- 第六分頁服務紀錄 -->
                    <div class="tab-pane" id="tabMenu6">

                      <div style="font-size:0.85em;line-height:1.4em;">
                        <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;評價紀錄列表。</div>
                        <hr>
                        <!-- card-body -->
                        <div class="card-body">
                          <table id="dataTable_ratlist" class="table table-bordered table-striped table-hover" data-pagemax="10" data-jumpto="0" data-search>
                            <thead>
                              <tr class="gray">
                                <th>日期</th>
                                <th>來自</th>
                                <!-- <th>類別</th> -->
                                <th>A</th>
                                <th>B</th>
                                <th>C</th>
                                <th>D</th>
                                <th>E</th>
                                <th>評論</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php
                                $sql="SELECT * FROM fsl_rating WHERE rat_forid=? AND rat_for=?";
                                $sr = $DB->query($sql,array($svID,4));
                                $i = 0; //以$i為$nameList這個陣列的索引值
                                while($i<count($sr)){ 
                                  ?>
                                  <tr>
                                    <td><?php echo $sr[$i]['rat_create']; ?></td>
                                    <td><?php echo $sr[$i]['md_id']; ?></td>
                                    <td><?php echo $sr[$i]['rat_s1']; ?></td>
                                    <td><?php echo $sr[$i]['rat_s2']; ?></td>
                                    <td><?php echo $sr[$i]['rat_s3']; ?></td>
                                    <td><?php echo $sr[$i]['rat_s4']; ?></td>
                                    <td><?php echo $sr[$i]['rat_s5']; ?></td>
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
                              <tr class="gray">
                                <th>總平均</th>
                                <th></th>
                                <!-- <th></th> -->
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
                    <!-- /第六分頁服務紀錄 -->
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
        <?php
          if(intval($res['sv_status'])>=6 && intval($res['sv_status'])<=8) {
        ?>
        <!-- -------------------- 修改建議彈出視窗 -------------------- -->
        <div id="rejectModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="background:rgba(0,0,0,0.75);">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <!-- Header -->
              <div class="modal-header">
                <h4 class="modal-title">退回提案建議說明</h4>
                <button type="button" id="modalCloseTop" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <!-- /Header -->
              <!-- Model Body -->
              <div class="modal-body">
                <h6 class="modal-des"></h6>
                <form id="form_doreject" name="form_doreject" data-ruleid="" action="../funcFiles/sv_act_func.php" method="post">
                  <div class="modal-container">
                    <textarea id="rv_content" name="rv_content" rows="6" style="width:100%;"></textarea>
                  </div>
                  <div>
                    <div class="icheck-primary d-inline">
                      <input type="radio" name="doReject" id="doReject_0" value="3" title="退回待修" alt="退回待修" checked>
                      <label for="doReject_0" class="font-weight-light">退回待修(返回原審核過程)&nbsp;&nbsp;&nbsp;&nbsp;</label>
                    </div>
                    <div class="icheck-primary d-inline">
                      <input type="radio" name="doReject" id="doReject_1" value="4" title="駁回提案" alt="駁回提案">
                      <label for="doReject_1" class="font-weight-light">駁回提案(終止審核並封存)</label>
                    </div>
                  </div>
                </form>
              </div>
              <!-- /Model Body -->
              <!-- Footer -->
              <div class="modal-footer justify-content-between">
                <button type="button" id="modalCloseBut" class="btn btn-default" data-dismiss="modal">關閉</button>
                <button type="button" id="revUpBut" name="revUpBut" class="btn btn-primary">執行</button>
              </div>

              <!-- /Footer -->
            </div>
          </div>
        <!-- /.modal-dialog -->
        </div>
        <!-- -------------------- /修改建議彈出視窗 -------------------- -->
        <!-- -------------------- 審核註記彈出視窗 -------------------- -->
        <div id="reviewModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="background:rgba(0,0,0,0.75);">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <!-- Header -->
              <div class="modal-header">
                <h4 class="modal-title"><?php echo $geJS['status'][$res['sv_status']]; ?>審核註記</h4>
                <button type="button" id="modalCloseTop" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <!-- /Header -->
              <!-- Model Body -->
              <div class="modal-body">
                <h6 class="modal-des"></h6>
                <form id="form_doreview" name="form_doreview" data-ruleid="" action="../funcFiles/sv_act_func.php" method="post">
                  <div class="modal-container">
                    <textarea id="rv_content" name="rv_content" rows="6" style="width:100%;" placeholder="<?php
                    echo $geJS['status'][$nowStatus]."通過進入".$geJS['status'][$nextStatus]."階段時的註記事項(可留空)";
                    ?>"></textarea>
                  </div>
                  <?php if($nowStatus==8){ ?>
                  <div>
                    <div class="icheck-primary d-inline">
                      <input type="radio" name="doPass" id="doPass_0" value="1" title="直接上架" alt="直接上架" checked>
                      <label for="doPass_0" class="font-weight-light">通過後直接上架&nbsp;&nbsp;&nbsp;&nbsp;</label>
                    </div>
                    <div class="icheck-primary d-inline">
                      <input type="radio" name="doPass" id="doPass_1" value="2" title="擇期上架" alt="擇期上架">
                      <label for="doPass_1" class="font-weight-light">由提案人擇期上架</label>
                    </div>
                  </div>
                  <?php } ?>
                </form>
              </div>
              <!-- /Model Body -->
              <!-- Footer -->
              <div class="modal-footer justify-content-between">
                <button type="button" id="modalCloseBut" class="btn btn-default" data-dismiss="modal">關閉</button>
                <button type="button" id="revNextBut" name="revNextBut" class="btn btn-primary" data-status="<?php echo $res['sv_status']; ?>">通過審核</button>
              </div>

              <!-- /Footer -->
            </div>
          </div>
        <!-- /.modal-dialog -->
        </div>
        <!-- -------------------- /審核註記彈出視窗 -------------------- -->
        <?php } ?>
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
<script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="../pluginFiles/select2/js/select2.full.min.js"></script>
<!-- InputMask 日期輸入元件JS -->
<script src="../pluginFiles/moment/moment.min.js"></script>
<script src="../pluginFiles/inputmask/jquery.inputmask.min.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="../pluginFiles/moment/moment.js"></script>
<script src="../pluginFiles/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- DataTables  & Plugins -->
<script src="../pluginFiles/datatables/datatables/jquery.dataTables.min.js"></script>
<script src="../pluginFiles/datatables/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
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
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
<script src="../scriptFiles/fileupload.js<?php echo '?t='.time(); ?>"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

<!-- ------------------------ /Scripts Link ------------------------ -->
<script type="text/javascript">
////////////////////////////////////////////////////////////////
// ---------------- JAVASCRIPTS 全域調用參數設置 --------------- //
////////////////////////////////////////////////////////////////

  var introActive = false;
  var infoActive = false;
  var sunIntro;
  var sunInfo;

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
    // ---------------------------------------
    //var servelistTB = datatableCreate('dataTable_servelist');
    var ratinglistTB = datatableCreate('dataTable_ratlist');
    ////////////////////////////////////////////////////////////
    $('.select2').select2();
    ////////////////////////////////////////////////////////////
    var mainobj = <?php echo json_encode($geJS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    //---------------------- 表單行為 --------------------------//
    $('#sv_cotype').on('change', function(){
      var typId = $(this).val();//Array
      var expId = $('#sv_coexpert').val();//Array
      var ct = mbobj['cotype'];
      $('#sv_coexpert').html('');
      if(typId!=''){
        $('#sv_coexpert').removeAttr("disabled");
      } else {
        $('#sv_coexpert').prop('disabled', 'disabled');
      }
      for(var i=0; i<typId.length; i++){
        $('#sv_coexpert').append('<optgroup label="-- '+ ct[typId[i]]['type'] +' --">');
        $.each(ct[typId[i]]['expert'], function( index, value ) {
          var appendHTML = '<option value="' + index + '"';
          //if ($.inArray(index, expId) >= 0) { appendHTML += ' selected'; }
          appendHTML += '>'+ value +'</option>';
          $('#sv_coexpert').append(appendHTML);
        });
      }
    });

    $('#sv_payfor').on('change', function(){
      var payval = $(this).val();
      if(payval==1){
        $('#sv_paytype').val('').removeAttr("disabled");
        $('#sv_every').val('').prop('disabled', 'disabled');
        $('#sv_cost').val('').removeAttr("disabled");
        //$('#sv_id').val('').prop('disabled', 'disabled');
      }else{
        $('#sv_paytype').val('').prop('disabled', 'disabled');
        $('#sv_every').val('').prop('disabled', 'disabled');
        $('#sv_cost').val('').prop('disabled', 'disabled');
      }
    });

    $('#sv_paytype').on('change', function(){
      var paytypeval = $(this).val();
      if(paytypeval=='0'){
        $('#sv_every').val('').prop('disabled', 'disabled');
        $('#everyUnit').html('個基數');
      } else {
        $('#sv_every').val('').removeAttr("disabled");
        if(paytypeval=='1'){ $('#everyUnit').html('案件'); }
        else if(paytypeval=='2'){ $('#everyUnit').html('小時'); }
        else if(paytypeval=='3'){ $('#everyUnit').html('日'); }
        else if(paytypeval=='4'){ $('#everyUnit').html('字'); }
        else if(paytypeval=='5'){ $('#everyUnit').html('行'); } 
        else if(paytypeval=='6'){ $('#everyUnit').html('檔案');}
        else { $('#everyUnit').html('個基數'); }
      }
    });

    $('#sv_authmode').on('change', function(){
      var authval = $(this).val();
      if(authval=='0'){
        $('#sv_authcount').val('0').prop('disabled', 'disabled');
        $('#authUnit').html('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
      } else {
        $('#sv_authcount').removeAttr("disabled");
        if(authval=='1'){ $('#authUnit').html('次'); }
        else if(authval=='2'){ $('#authUnit').html('時'); }
        else if(authval=='3'){ $('#authUnit').html('日'); }
        else { $('#authUnit').html('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'); }
      }

    });

    ////////////////////////////////////////////////////////////
    //-- 開放式服務提案審核通過
    $('#revNextBut').click(function(e){
      var nowStatus = parseInt($(this).attr('data-status'));
      var nextStatus = nowStatus + 1;
      var nextStep = nextStatus;
      if(nextStatus==9){
        nextStep=1;
        nextStatus = $("input[name='doPass']:checked").val();
      }
      $.ajax({
              type: 'post',
              url: '../funcFiles/sv_act_func.php',
              data: {
                'arrDatA': {
                  mb_id: '<?php echo $res['mb_id']; ?>',
                  ad_id: '<?php echo CF_AD_ID; ?>',
                  rv_for: '1',
                  rv_forid: '<?php echo $svID; ?>',
                  rv_content: $('textarea#rv_content').val(),
                  rv_review: nowStatus,
                  rv_status: nextStatus
                }, 
                'formAcce':'form_doreview',
                'token': getTokenVal(), 
                'svID':'<?php echo $svID; ?>'
              },
              dataType: 'json',
              async: false, 
              beforeSend:function(){
                var comF = '這個動作將會將通過此份提案的'+mainobj['status'][nowStatus]+'，並且直接進入'+mainobj['status'][nextStep]+'階段，由具備複審資格的管理人員接手，進行下一階段的審核作業！\r\n\r\n確定通過此份開放式服務提案的'+mainobj['status'][nowStatus]+'？！';
                if(!confirm(comF)){return false;}
              },
              success: function (data) {
                if(data['RTN']==1){
                  //postData('serv_svdetail.php',{ 'svID': '<?php echo $svID; ?>' });
                  window.location.replace("serv_svdetail.php?currID=<?php echo $svID; ?>");
                  return false;
                } else {
                  
                }
                window.parent.toastr[data['msgType']](data['msgStr']);
              },
              error: function (data) {
                  window.parent.toastr['error']('資料庫API轉譯器錯誤，請聯絡工程人員立即處理！！');
              }
          });
      });
      
      //-- 開放式服務提案退回
      
      $('#revUpBut').click(function(e){
        if($.trim($('textarea#rv_content').val())==''){
          var errStr = '您必須填寫以下的內容，始得執行退回或駁回提案：\n\r-- 提案退回或駁回意見說明。\n\r';
          alert(errStr);
          return false;
        }
        var reviewStep =  $("input[name='doReject']:checked").val();
        var statusStep = reviewStep;
        if(reviewStep==3){reviewStep='<?php echo $res['sv_status']; ?>'; }
        $.ajax({
                type: 'post',
                url: '../funcFiles/sv_act_func.php',
                data: {
                  'arrDatA': {
                    mb_id: '<?php echo $res['mb_id']; ?>',
                    ad_id: '<?php echo CF_AD_ID; ?>',
                    rv_for: '1',
                    rv_forid: '<?php echo $svID; ?>',
                    rv_content: $('textarea#rv_content').val(),
                    rv_review: reviewStep,
                    rv_status: statusStep
                  }, 
                  'formAcce':'form_doreject',
                  'token': getTokenVal(), 
                  'svID':'<?php echo $svID; ?>'
                },
                dataType: 'json',
                async: false, 
                beforeSend:function(){
                  var comF = '這個動作將會退回此份開放式服務提案，並提出建議及說明內容。\r\n\r\n確定退回此份開放式服務提案？！';
                  if(!confirm(comF)){return false;}
                },
                success: function (data) {
                  window.parent.toastr[data['msgType']](data['msgStr']);
                  if(data['RTN']==1){
                      //-- 更改當前頁面的相關數值
                    $('#butApply_group').hide();
                    $('#rv_content').html('');
                    $('#doReject_0').prop( "checked", true );
                    $('#reviewstep_show .card-header span').html(mainobj['reviewstatus'][statusStep]);
                    $('#reviewstep_show .card-body').html(mainobj['review'][statusStep]);
                    $('#rejectModal').removeClass('show').fadeOut(200);
                    var newNum = parseInt($('#badge_<?php echo PAGE_MARK; ?>').html())-1;
                    if(newNum==0){ newNum=""; }
                    $('#badge_<?php echo PAGE_MARK; ?>').html(newNum);
                    //$('#sv_status').val("4").change();
                  } else {
                    
                  }
                },
                error: function (data) {
                  window.parent.toastr['error']('資料庫API轉譯器錯誤，請聯絡工程人員立即處理！！');
                }
            });
      });

      $('#but_doreject').click(function(e){ 
        $('#rejectModal').fadeIn(200).addClass('show');
      });
      $('#but_doreview').click(function(e){ 
        $('#reviewModal').fadeIn(200).addClass('show');
      });
    //----------------- 彈出窗關閉按鍵 -----------------------
    $('#rejectModal #modalCloseTop, #rejectModal #modalCloseBut').click(function(e){ 
      $('#rejectModal #rv_content').html('');
      $('#rejectModal').removeClass('show').fadeOut(200);
    });
    $('#reviewModal #modalCloseTop, #reviewModal #modalCloseBut').click(function(e){ 
      $('reviewModal #rv_content').html('');
      $('#reviewModal').removeClass('show').fadeOut(200);
    });

    //----------------- SUNEDITOR -----------------------
    sunIntro = suneditorCreate('sv_exinfo','full');
    sunInfo = suneditorCreate('sv_intro');
    sunInfo.onChange = function (contents, core) {
      if(contents !== $('#sv_exinfo').val()){
        infoActive = true;
        window.parent.$('#topBut_save').addClass('active');
      } else {
        infoActive = false;
        if(introActive == false) { window.parent.$('#topBut_save').removeClass('active'); }
      }
    }
    sunIntro.onChange = function (contents, core) {
      if(contents !== $('#sv_intro').val()){
        introActive = true;
        window.parent.$('#topBut_save').addClass('active');
      } else {
        introActive = false;
        if(infoActive == false) { window.parent.$('#topBut_save').removeClass('active'); }
      }
    }
    // 內頁右上方小導航返回列表頁
    $('li.prevLink').on('click', function(){
      window.location.replace(getCurrentURI('','serv_svlist.php'));
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
    if(introActive == true) { sunIntro.save(); }
    if(infoActive == true) { sunInfo.save(); }
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
          url: '../funcFiles/adv_act_func.php',
          data: {
            'arrDatA': {}, 
            'formAcce':'form_delete',
            'token': getTokenVal(), 
            'adviID':'<?php echo $svID; ?>'
          },
          dataType: 'json',
          async: false, 
        success: function (data) {
            if(data['RTN']==1){
              window.location.replace("advp_list.php");
            } else {
              ajaxRTN(data,'form_delete','error');
            }
          },
          error: function (data) {
            ajaxRTN(data,'form_delete','error');
          }
      });
  }
  ///////////////////////////// AJAX 表單處理 ////////////////////////////////
  function ajaxPOST(frm){
        //var frm = $('#form_'+$('.tab-pane.active').prop("id"));
        var accE = frm.data('func');
        var url = frm.prop('action');
        var typE = frm.prop('method');
        //時間排程建立控制，某些階段必須建立排程始得送出結果
        //var schCount = $('#schTableList >tbody >tr').length;
        var schARR = [];
        var mediumARR = [];
        var datA,datB;
        var imgBase64='';
        if(accE=='func_sv_infoUpdate'){
          datA = getFormObjects(frm);
          imgBase64 = $('#main_crop_area').data('cropImage64') || '';
        } else if(accE=='func_sv_htmlUpdate'){
          datA = $('#sv_body').val();
        }
        $.ajax({
            type: typE,
            url: url,
            data: {
              'arrDatA':datA, 
              //'arrDatB':datB, 
              'formAcce':accE,
              'imgUp': imgBase64,
              'svID': '<?php echo $svID; ?>',
              'token': getTokenVal(), 
              'schedule': schARR,
              'medium': mediumARR
            },
            dataType: 'json',
            async: false, 
          success: function (data) {
              console.log(data);
              ajaxRTN(data,accE,'success');
            },
            error: function (data) {
              console.log(data);
              window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
            }
        });
    }

    function ajaxRTN(datA,acCE,rE){
      window.parent.toastr[datA['msgType']](datA['msgStr']);
      if(acCE=='func_sv_infoUpdate'){
          if (datA['RTN']==1) {
            if(cropImage64!='') { 
              $('#mainimgchk').val('svphoto_<?php echo $svID; ?>_.jpg');
              $('#upload_image').val('');
              $('#delmainimg').removeClass('invisible').addClass('visible');
            }
          } else {
            $('#form_svupdate')[0].reset();   
          }
          window.parent.$('#topBut_save').removeClass('active');
          cropImage64 = '';
      }else if(acCE=='form_applyhtmlupdate') {
        window.parent.$('#topBut_save').removeClass('active');
      }else if(acCE=='form_doapply') {
        
      }
      
    }
</script>
</body>
</html>
