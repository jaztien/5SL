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
<body class="bg-body-tertiary theme-green">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1 ms-2"><i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?></div>
  <!-- 全頁框 -->
  <div class="app-wrapper">
    <!-- 頁內全頁內容 -->
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0"><?php echo !empty($adID) ? "編輯" : "新增"; ?><?php echo (!empty($adID) && $adID==$_SESSION['SESS_AD_ID']) ? "我的個人資料" : "管理人員資料"; ?><span class="fs-5">【ID: <?php echo $adID; ?>】</span></h4>
              <h6 class="m-0 fw-light text-muted">管理者資料編輯</h6>
            </div>
            <div class="col-sm-6">
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

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <!-- 左側個人資訊卡片 -->
            <div class="col-md-4">
              <div class="card card-primary card-outline shadow-sm bg-body-secondary bg-opacity-75" data-bs-theme="dark">
                <div class="card-body box-profile">
                  <div class="text-center">
                    <!-- Image Area -->
                    <div class="image_area">
                      <i class="nav-icon bi bi-trash3 fs-4 cropImgTrash<?php echo " ".$cropImgDisplay; ?>" title="刪除圖像"></i>
                      <form method="post">
                        <label for="cropUploadImage">
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

                  <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item px-0 bg-transparent">
                      <b>狀態</b> <a class="float-end text-decoration-none<?php echo (!empty($res['ad_status']) && $res['ad_status']==1) ? "" : " text-danger"; ?>"><?php echo !empty($res['ad_status']) ? $adJS['status'][$res['ad_status']] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item px-0 bg-transparent">
                      <b>暱稱</b> <a class="float-end text-decoration-none"><?php echo !empty($res['ad_title']) ? $res['ad_title'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item px-0 bg-transparent">
                      <b>電子郵箱</b> <a class="float-end text-decoration-none"><?php echo !empty($res['ad_email']) ? $res['ad_email'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item px-0 bg-transparent">
                      <b>手機號碼</b> <a class="float-end text-decoration-none"><?php echo !empty($res['ad_phone']) ? $res['ad_phone'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item px-0 bg-transparent">
                      <b>Line ID</b> <a class="float-end text-decoration-none"><?php echo !empty($res['ad_line']) ? $res['ad_line'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item px-0 bg-transparent">
                      <b>編輯日期</b> <a class="float-end text-decoration-none"><?php echo !empty($res['ad_updated']) ? $res['ad_updated'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item px-0 bg-transparent">
                      <b>建立日期</b> <a class="float-end text-decoration-none"><b><?php echo !empty($res['ad_published']) ? $res['ad_published'] : "&nbsp;"; ?></b></a>
                    </li>
                  </ul>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->

            <!-- 右側多分頁表單區 -->
            <div class="col-md-8">
              <div class="card shadow-sm modern-card-tabs">
                <div class="card-header p-2">
                  <!-- [變更10] data-toggle="tab" 改為 data-bs-toggle="tab" -->
                  <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation"><a class="nav-link active" href="#tabMenu1" data-index="1" data-bs-toggle="tab" data-topbut="1" role="tab">個人訊息修改</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" href="#tabMenu2" data-index="2" data-bs-toggle="tab" data-topbut="1" role="tab">管理權限修改</a></li>
                  </ul>
                </div><!-- /.card-header -->

                <div class="card-body">
                  <div class="tab-content">
                    <!-- 第一分頁管理者資料修改 -->
                    <div class="tab-pane fade show active" id="tabMenu1" role="tabpanel">
                    {{Tab1 內容}}
                   </div>
                    <!-- /第一分頁管理者資料修改 -->
                    <!-- 第二分頁管理權限修改 -->
                    <div class="tab-pane fade" id="tabMenu2" role="tabpanel">
                      {{Tab2 內容}}
                    </div>
                    <!-- /第二分頁管理權限修改 -->
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
  //**************************** ********************************//
  //***************** JAVASCRIPTS 全域調用參數設置 *****************//
  //****************************                    ********************************//
  var adobj = <?php echo json_encode($adJS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

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
  //**************************** ********************************//
  //*************** JAVASCRIPTS FUNCTIONs 全域調用 ***************//
  //**************************** ********************************//
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
      dataType: 'json',      success: function (data) {
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

  //*************************** *********************************//
  //***************** jQuery FUNCTIONs 區域調用 ******************//
  //*************************** *********************************//
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
          text: '<i class="bi bi-journal-plus"></i> 新增投放規則',
          action: function(e) {
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

    //-------- 加載完成後更改外框主頁中樹狀目錄開合狀態 --------//
    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  });
</script>
</body>
</html>
