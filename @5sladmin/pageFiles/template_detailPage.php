<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_XXXX');
define('PAGE_ITEM','ITEM_XXXX');
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//
$xxxxID=currID;
//
$res = DB->row("SELECT * FROM xxxxxxxx WHERE xxxx_id=?",array($xxxxID));
//DB->closeConnection();
//代表圖像設定
if(!empty($xxxxID)&&file_exists("../uploadFiles/xx/xxphoto_".$xxxxID."_thumb.jpg")){
  $cropImgDisplay = 'show';
  $mainimgpath = '../uploadFiles/xx/xxphoto_'.$xxxxID.'_thumb.jpg';
  $mainimgchk = 'xxphoto_'.$xxxxID.'_.jpg';
} else {
  $cropImgDisplay = 'hide';
  $mainimgpath = '../uploadFiles/imageFiles/nopic_400x300.jpg';
  $mainimgchk = "";
}

//功能陣列建立
// Get the contents of the JSON file
$xxxxJS=_fn_getCachedJsonData("../jsonFiles/xxxxxxxx.json");
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
	
  <title> Fifth Season Life 人生第五季 | XXXXXX </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://googleapis.com">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- DataTables 3 -->
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
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
  .processingBut{
    cursor: pointer;
  }
</style>
</head>
<!-- [變更1] iFrame 內頁移除舊版 layout-navbar-fixed 等干擾類別 -->
<body class="bg-body-tertiary">
  <!-- processing waiting -->
  <div id="loadingMask" style="top:0;left:0;position:fixed;width:100%;height:100%;background-color:#2A2826;opacity:0.75;margin:0;text-align:center;z-index:99999;display:none;">
    <div style="color:#FFFFFF;position:relative;top:50%;transform:translate(0, -50%);">
      <img src="../imageFiles/loading2.gif"><br> 操作進行中，請稍候...
    </div>
  </div>
  
  <!-- [變更2] .wrapper 改為 .app-wrapper -->
  <div class="app-wrapper">
    <!-- [變更3] .content-wrapper 改為 .app-main -->
    <main class="app-main">
      <!-- 頁頭導覽條 (.content-header 改為 .app-content-header) -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-7">
              <h4 class="m-0"><?php echo !empty($xxxxID) ? "編輯" : "新增"; ?>XXXXXX資料<span class="fs-5">【ID: <?php echo $xxxxID; ?>】</span></h4>
            </div>
            <div class="col-sm-5">
              <!-- [變更4] float-sm-right 改為 float-sm-end -->
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">XXXXXX管理</li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回列表" onClick="window.location.replace('xxxx_list.php');">XXXXXX紀錄</li>
                <li class="breadcrumb-item active">XXXXXX詳細資料</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
      <!-- /頁頭導覽條 -->

      <!-- 主要內容區域 (.content 改為 .app-content) -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <!-- 左側資訊卡片 -->
            <div class="col-md-3">
              <div class="card card-primary card-outline shadow-sm">
                <div class="card-body box-profile">
                  <div class="text-center">
                    <div class="image_area">
                      <i class="nav-icon fas fa-2x fa-trash-can cropImgTrash<?php echo " ".$cropImgDisplay; ?>" title="刪除圖像"></i>
                      <form method="post">
                        <label for="cropUploadImage">
                          <!-- [變更5] img-responsive 改為 img-fluid，img-rounded 改為 rounded -->
                          <img id="avatarImage" name="avatarImage" class="img-fluid rounded imgEDicon" src="<?php echo $mainimgpath."?".time(); ?>" alt="XXXX代表圖" title="XXXX代表圖"/>
                          <div class="overlay">
                            <div class="overtext"><?php echo !empty($xxxxID) ? "更改" : "新增"; ?>代表圖</div>
                          </div>
                          <input type="file" name="image" class="image d-none" id="cropUploadImage" crop-width="800" crop-height="600">
                        </label>
                      </form>
                      <!-- -------------------- 裁切頭像彈出視窗 -------------------- -->
                      <?php include('include_avatarmodal.php'); ?>
                      <!-- -------------------- /裁切頭像彈出視窗 -------------------- -->
                    </div>
                  </div>
                  <h3 class="profile-title text-center mt-2"><?php echo !empty($res['xxxx_title']) ? $res['xxxx_title'] : "&nbsp;"; ?></h3>
                  <p class="text-muted text-center"><?php echo !empty($res['xxxx_subscript']) ? $res['xxxx_subscript'] : "&nbsp;"; ?></p>

                  <!-- [變更6] list-group-unbordered 改用 BS5 原生 list-group-flush，float-right 改為 float-end -->
                  <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item px-0">
                      <b>類別</b> <a class="float-end text-decoration-none"><?php echo !empty($res['xxxx_type']) ? $xxxxJS['type'][$res['xxxx_type']] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item px-0">
                      <b>狀態</b> <a class="float-end text-decoration-none<?php echo (!is_null($res['xxxx_status']) && $res['xxxx_status']==0) ? " text-danger" : ($res['xxxx_status']==1 ? " text-success" : " text-warning"); ?>"><?php echo !is_null($res['xxxx_status']) ? $xxxxJS['status'][$res['xxxx_status']] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item px-0">
                      <b>編輯日期</b> <a class="float-end text-decoration-none"><?php echo !empty($res['xxxx_published']) ? $res['xxxx_published'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item px-0">
                      <b>建立日期</b> <a class="float-end text-decoration-none text-dark"><b><?php echo !empty($res['xxxx_updated']) ? $res['xxxx_updated'] : "&nbsp;"; ?></b></a>
                    </li>
                    <li class="list-group-item px-0">
                      <b>總瀏覽數</b> <a class="float-end text-decoration-none" style="color:#cd5757;font-size:1.5em;"><b><?php echo !empty($res['xxxx_viewed']) ? number_format($res['xxxx_viewed']) : 0; ?></b></a>
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
              <div class="card card-secondary shadow-sm">
                <div class="card-header p-2">
                  <!-- [變更7] data-toggle="tab" 改為 data-bs-toggle="tab"，並補上 role="tablist" -->
                  <ul class="nav nav-pills" role="tablist">
                    <li class="nav-item" role="presentation"><a class="nav-link active" href="#tabMenu1" data-index="1" data-bs-toggle="tab" data-topbut="1" role="tab">XXX資料修改</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" href="#tabMenu2" data-index="2" data-bs-toggle="tab" data-topbut="1" role="tab">XXX主體編輯</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" href="#tabMenu3" data-index="3" data-bs-toggle="tab" data-topbut="0" role="tab">授權紀錄</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" href="#tabMenu4" data-index="4" data-bs-toggle="tab" data-topbut="0" role="tab">閱覽者紀錄</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" href="#tabMenu5" data-index="5" data-bs-toggle="tab" data-topbut="0" role="tab">評價紀錄</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" href="#tabMenu6" data-index="6" data-bs-toggle="tab" data-topbut="0" role="tab">替代文字說明</a></li>
                  </ul>
                </div><!-- /.card-header -->

                <div class="card-body">
                  <div class="tab-content">
                    <!-- 第一分頁文章資料修改 -->
                    <div class="tab-pane fade show active" id="tabMenu1" role="tabpanel">
                      <div class="small text-secondary">
                        <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;部份輸入使用HTML編輯器編輯內容，可以美化文字，也可以嵌入超連結，請注意，編輯器要先內部儲存後，本頁面的更新才能有效儲存新的編輯資料。
                      </div>
                      <hr>
                      <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/xxxx_act_func.php" method="post" data-formmode="infoupdate" data-func="func_xxxx_infoUpdate">
                        <input type="hidden" name="mainimgchk" id="mainimgchk" class="notSubmit" value="<?php echo $mainimgchk; ?>">
                        <div class="card-body">
                          <div class="row mb-3">
                            <label for="xxxx_title" class="col-sm-2 col-form-label">文章抬頭</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="xxxx_title" name="xxxx_title" value="<?php echo !empty($res['xxxx_title']) ? $res['xxxx_title'] : ""; ?>" placeholder="文章抬頭" data-min="2" data-required<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="xxxx_subscript" class="col-sm-2 col-form-label">下標(郵件抬頭)</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="xxxx_subscript" name="xxxx_subscript" value="<?php echo !empty($res['xxxx_subscript']) ? $res['xxxx_subscript'] : ""; ?>" placeholder="下標"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="xxxx_alt" class="col-sm-2 col-form-label">滑鼠彈窗</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="xxxx_alt" name="xxxx_alt" value="<?php echo !empty($res['xxxx_alt']) ? $res['xxxx_alt'] : ""; ?>" placeholder="滑鼠彈窗"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="xxxx_brief" class="col-sm-2 col-form-label">簡述</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="xxxx_brief" name="xxxx_brief" rows="4" placeholder="簡述" data-min="10" data-required<?php echo NreadOnly; ?>><?php echo !empty($res['xxxx_brief']) ? stripslashes($res['xxxx_brief']) : ""; ?></textarea>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="xxxx_keycode" class="col-sm-2 col-form-label">指定代碼</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="xxxx_keycode" name="xxxx_keycode" value="<?php echo !empty($res['xxxx_keycode']) ? $res['xxxx_keycode'] : ""; ?>" placeholder="指定代碼"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="xxxx_type" class="col-sm-2 col-form-label">文章種類</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <select class="form-select" id="xxxx_type" name="xxxx_type"<?php echo NdisAble; ?>>
                                  <?php
                                    foreach($xxxxJS['type'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""){ echo " disabled"; }
                                        if(!empty($res['xxxx_type']) && $res['xxxx_type']==$key) echo " selected";
                                        echo '>'.$value.'</option>';
                                    }
                                  ?>
                                </select>
                                <button type="button" class="btn btn-primary processingBut">測試郵件</button>
                              </div>
                            </div>
                            <label for="xxxx_cate" class="col-sm-2 col-form-label">文章分類</label>
                            <div class="col-sm-4">
                              <select class="form-select" id="xxxx_cate" name="xxxx_cate"<?php echo NdisAble; ?>>
                                <?php
                                  foreach($xxxxJS['cate'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(!empty($res['xxxx_cate']) && $res['xxxx_cate']==$key) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="xxxx_payfor" class="col-sm-2 col-form-label">有償使用</label>
                            <div class="col-sm-4">
                              <select class="form-select" id="xxxx_payfor" name="xxxx_payfor"<?php echo NdisAble; ?>>
                                <?php
                                  foreach($xxxxJS['yesorno'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(!empty($res['xxxx_payfor']) && $res['xxxx_payfor']==$key) echo " selected";
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                            <label for="xxxx_spend" class="col-sm-2 col-form-label">花費點數</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="xxxx_spend" name="xxxx_spend" value="<?php echo !empty($res['xxxx_spend']) ? $res['xxxx_spend'] : "0"; ?>" placeholder="花費點數"<?php echo NreadOnly; ?>>
                                <span class="input-group-text areaUnit">點</span>
                              </div>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="xxxx_count" class="col-sm-2 col-form-label">計次授權</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="xxxx_count" name="xxxx_count" value="<?php echo !empty($res['xxxx_count']) ? $res['xxxx_count'] : "0"; ?>" placeholder="計次授權"<?php echo NreadOnly; ?>>
                                <span class="input-group-text areaUnit">次</span>
                              </div>
                            </div>
                            <label for="xxxx_days" class="col-sm-2 col-form-label">授權天數</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="xxxx_days" name="xxxx_days" value="<?php echo !empty($res['xxxx_days']) ? $res['xxxx_days'] : "0"; ?>" placeholder="授權天數"<?php echo NreadOnly; ?>>
                                <span class="input-group-text areaUnit">天</span>
                              </div>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-12 text-end small text-secondary">
                              <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;授權天數若為0，則以計次授權為準，若計次授權亦為0，則為永久授權。
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label for="xxxx_dateclose" class="col-sm-2 col-form-label">下架日期</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                <input type="text" class="form-control" id="xxxx_dateclose" name="xxxx_dateclose" data-inputmask-alias="datetime" data-inputmask-inputformat="yyyy-mm-dd" value="<?php echo !empty($res['xxxx_dateclose'])?$res['xxxx_dateclose']:''; ?>" data-mask<?php echo NreadOnly; ?>>
                              </div>
                            </div>
                            <label for="xxxx_status" class="col-sm-2 col-form-label">文章狀態</label>
                            <div class="col-sm-4">
                              <select class="form-select" id="xxxx_status" name="xxxx_status"<?php echo NdisAble; ?>>
                                <?php
                                  foreach($xxxxJS['status'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(!is_null($res['xxxx_status']) && $res['xxxx_status']==$key) echo " selected";
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
                    <!-- /第一分頁文章資料修改 -->

                    <!-- 第二分頁文章主體編輯 -->
                    <div class="tab-pane fade" id="tabMenu2" role="tabpanel">
                      <div class="small text-secondary text-start">
                        <i class="nav-icon fas fa-2x fa-exclamation-circle"></i>&nbsp;&nbsp;Html 編輯器使用 SunEditor 2.46.3 版本，並依照 MIT 授權條款內容行使權利及義務。
                      </div>
                      <hr>
                      <form id="form_tabMenu2" name="form_tabMenu2" action="../funcFiles/xxxx_act_func.php" method="post" data-formmode="htmlupdate" data-func="func_xxxx_htmlUpdate">
                        <div class="card-body">
                          <textarea name="xxxx_body" id="xxxx_body"><?php echo !empty($res['xxxx_body']) ? stripslashes($res['xxxx_body']): ""; ?></textarea>
                        </div>
                      </form>  
                    </div>
                    <!-- /第二分頁文章主體編輯 -->

                    <!-- 第三分頁授權紀錄 -->
                    <div class="tab-pane fade" id="tabMenu3" role="tabpanel">
                      <div class="small text-secondary text-start">
                        <i class="nav-icon fas fa-2x fa-exclamation-circle"></i>&nbsp;&nbsp;XXXXX
                      </div>
                      <hr>
                      <div class="card-body bg-light">
                      </div>
                    </div>
                    <!-- /第三分頁授權紀錄 -->

                    <!-- 第四分頁閱覽者紀錄 -->
                    <div class="tab-pane fade" id="tabMenu4" role="tabpanel">
                      <div class="small text-secondary">
                        <i class="nav-icon fas fa-2x fa-exclamation-circle"></i>&nbsp;&nbsp;本會員的中庸本系統的操作記錄列表，本列表用於偵錯、改進、分析甚至於究責，所以一旦寫入記錄便無法更改，操作記錄將於多處以及多型態備份。
                      </div>
                      <hr>
                      <div class="card-body">
                        <table id="loTableList" class="table table-bordered table-striped table-hover w-100" data-search>
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
                              $srr = DB->query($sql,array($xxxxID,1));
                              $i = 0;
                              while($i<count($srr)){ 
                                ?>
                                <tr>
                                  <td><?php echo $srr[$i]['lg_code']; ?></td>
                                  <td><?php echo $srr[$i]['lg_created']; ?></td>
                                  <td><?php
                                  $oStr = DB->single("SELECT lg_string FROM sys_logstrings WHERE lg_code=?",array($srr[$i]['lg_code']));
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
                    </div>
                    <!-- /第四分頁閱覽者紀錄 -->

                    <!-- 第五分頁評價紀錄 -->
                    <div class="tab-pane fade" id="tabMenu5" role="tabpanel">
                    </div>
                    <!-- /第五分頁評價紀錄 -->

                    <!-- 第六分頁替代文字說明 -->
                    <div class="tab-pane fade" id="tabMenu6" role="tabpanel">
                      <div class="small text-secondary">
                        <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;作為系統模版的圖文記錄時，為使系統與使用者之間達到互動關係，時常會需要替換某些指定性的文字，例如會員暱稱、帳戶餘額、日期時間....等等，而系統只能辨識某個目標數據對應的指定字串，而主動進行替換。
                      </div>
                      <hr>
                      <div class="card-body">
                        <table id="substituteList" class="table table-bordered table-striped table-hover w-100" data-search>
                          <thead>
                            <tr>
                              <th>代碼</th>
                              <th>替換目標</th>
                              <th>對應值或資料表</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr><td>[@userid@]</td><td>會員ID</td><td>fsl_members.mb_title.mb_id</td></tr>
                            <tr><td>[@username@]</td><td>會員顯示名稱</td><td>fsl_members.mb_title.mb_id</td></tr>
                            <tr><td>[@useremail@]</td><td>會員電子郵件</td><td>fsl_members.mb_email.mb_id</td></tr>
                            <tr><td>[@currdate@]</td><td>指定日期</td><td>time(Y-m-d)</td></tr>
                            <tr><td>[@nowdate@]</td><td>現在日期</td><td>time()</td></tr>
                            <tr><td>[@+3600@]</td><td>距指定日期時間n秒後的日期時間</td><td>time() + n</td></tr>
                            <tr><td>[@-3600@]</td><td>距指定日期時間n秒之前的日期時間</td><td>time() - n</td></tr>
                            <tr><td>[@inpoint@]</td><td>現在儲值點數</td><td>fsl_points.pt_trade.mb_id</td></tr>
                            <tr><td>[@nowpoint@]</td><td>現在儲值點數</td><td>fsl_points.pt_trade.mb_id</td></tr>
                            <tr><td>[@comptel@]</td><td>電話</td><td>公司聯絡電話</td></tr>
                            <tr><td>[@compext@]</td><td>分機號碼</td><td>公司聯絡電話分機號碼</td></tr>
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
      </div>
      <!-- /主要內容區域 -->
    </main>
    <!-- /頁內全頁內容 -->
  </div>
  <!-- /全頁框 -->

  <!-- ------------------------ Scripts Link ------------------------ -->
  <!-- jQuery -->
  <script src="../pluginFiles/jquery/jquery.min.js"></script>
  <!-- Bootstrap 5 -->
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <!-- DataTables 3 & Plugins -->
  <script src="../pluginFiles/datatables3/datatables.min.js"></script>
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
    var mainObj = $.parseJSON( '<?php echo func_stripewhite($xxxxGet); ?>' ); 
    var briefActive = false;
    var bodyActive = false;
    var sunBrief;
    var sunBody;
  ////////////////////////////////////////////////////////////////     
  // ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
  // -------------- 可接收include檔案要求後自定義動作 ------------- //
  ////////////////////////////////////////////////////////////////
    // 當「儲存」按鈕按下時的動作
    function doButSave(){
      var frm = $('#form_'+$('.tab-pane.active').prop("id"));
      var accE = frm.data('func');
      if(briefActive == true) { sunBrief.save(); } // 聯動 Html 編輯器
      if(bodyActive == true) { sunBody.save(); } // 聯動 Html 編輯器
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
          url: '../funcFiles/xxxx_act_func.php',
          data: {
            'arrDatA': {}, 
            'formAcce':'func_xxxx_delete',
            'token': getTokenVal(), 
            'xxxxID':'<?php echo $xxxxID; ?>'
          },
          dataType: 'json',
          async: false, 
        success: function (data) {
            if(data['RTN']==1){
              window.location.replace("xxxx_list.php");
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
    function doReDirect(){
      window.history.back();
    }

    ///////////////////////////// AJAX 表單處理 ////////////////////////////////

    function ajaxPOST(frm){
      var frm = $('#form_'+$('.tab-pane.active').prop("id"));
      var accE = frm.data('func');
      var url = frm.prop('action');
      var typE = frm.prop('method');
      var imgBase64='';
      //var datA,datB;
      if(accE=='func_xxxx_infoUpdate'){
        //var datA = frm.find(":input:not(.notSubmit,[name^=suneditor_])").serializeArray();
        datA = getFormObjects(frm);
        if(0==parseInt('<?php echo $xxxxID; ?>')){
          accE = 'func_xxxx_addnew';
        }
        imgBase64 = $('#main_crop_area').data('cropImage64') || '';
      } else if(accE=='func_xxxx_htmlUpdate'){
        var datA = $('#xxxx_body').val();
      }
      $.ajax({
          type: typE,
          url: url,
          data: {
            'arrDatA':datA, 
            'formAcce':accE,
            'imgUp': imgBase64,
            'token': getTokenVal(), 
            'xxxxID':'<?php echo $xxxxID; ?>'
          },
          dataType: 'json',
          async: false, 
        success: function (data) {
            console.log(data);
            window.parent.toastr[data['msgType']](data['msgStr']);
            if(accE=='func_xxxx_infoUpdate'){
                if (data['RTN']==1) {
                  var newType = mainObj['type'][$('#xxxx_type').val()];
                  var newSta = mainObj['status'][$('#xxxx_status').val()];
                  switch ($('#xxxx_status').val()){
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
                  $('.profile-title').html($('#xxxx_title').val());
                  $('.profile-title + p').html($('#xxxx_subscript').val());
                  $('.box-profile > ul > li:nth-child(1) > a').html(newType);
                  $('.box-profile > ul > li:nth-child(2) > a').html(newSta);
                  $('.box-profile > ul > li:nth-child(3) > a').html(datA['upTime']);
                }
              } else if(accE=='func_xxxx_htmlupdate') {


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
      //* iFrame 頁面中安全滯留倒數功能 scripts
      $('body').on('keypress click', function(){
        let timerElm = window.parent.$('#timer_on_exhausted');
        if( timerElm.length ){
          timerElm.backward_timer('reset');
        }
      });
      //-------------------「Html Body 編輯器」啟動處理 ---------------------
      sunBrief = suneditorCreate('xxxx_brief','ez','100%','204');
      sunBrief.onChange = function (contents, core) {
        if(contents !== $('#xxxx_brief').val()){
            briefActive = true;
            window.parent.$('#topBut_save').addClass('active');
          } else {
            briefActive = false;
            if(bodyActive == false) { window.parent.$('#topBut_save').removeClass('active'); }
          }
        }

      sunBody = suneditorCreate('xxxx_body','full','100%');
      sunBody.onChange = function (contents, core) {
        if(contents !== $('#xxxx_body').val()){
            bodyActive = true;
            window.parent.$('#topBut_save').addClass('active');
          } else {
            bodyActive = false;
            if(briefActive == false) { window.parent.$('#topBut_save').removeClass('active'); }
          }
        }
  ///////////////////////// 「操作進行中等待」觸發處理 ///////////////////////////////
      $('.processingBut').on('click',function(){
        $('#loadingMask').show();
        var type = $('#xxxx_type').val();
          $.ajax({
              type: 'post',
              url: '../funcFiles/xxxxxx_func.php',
              data: {
                'arrDatA': {
                  "subject": $('#xxxx_subscript').val(),
                  "body": $('#xxxx_body').val()
                }, 
                'formAcce':'func_xxxxxx',
                'token': getTokenVal(), 
                'xxxxID':'<?php echo $xxxxID; ?>'
              },
              dataType: 'json',
              success: function (data) {
                  $('#loadingMask').hide();
                  window.parent.toastr[data['msgType']](data['msgStr']);
                  console.log(data);
              },
              error: function (data) {
                $('#loadingMask').hide();
                window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
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
