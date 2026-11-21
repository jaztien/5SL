<?php
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
session_start();
if(!isset($_SESSION["SESS_AD_ID"]) || empty($_SESSION["SESS_AD_ID"])){
	header("location: 5sladmin.php?e=9"); // 遺失 SESSION，可能閒置超時，系統自動登出！
	exit();
}
$adID = intval($_SESSION["SESS_AD_ID"]);
if(!empty($adID)){
  define('DOC_PATH_LAYER', './');
  require_once('includeFiles/db.inc.php');
  $ilog = DB->insSysLogs(51, $adID, 1111, '["'.$adID.'"]'); // 寫入操作記錄
}
unset($_SESSION["SESS_AD_ID"]);
session_regenerate_id(TRUE);

// 更新註冊過後的 TOKEN ，降低被盜取的機率
require_once('includeFiles/common.lib.php');
$_SESSION['token'] = _fn_FHMD5_encrypt(strtolower($_SERVER['HTTP_USER_AGENT']).'PHPSESSID='.session_id());

$PAGE_MARK = "PG_LOCKPAGE"; // 鎖定頁面（登出後保留 Session 中的操作者ID資料）
$PAGE_ITEM = "ITEM_LOCK";

$backURI = isset($_GET['backURI']) ? $_GET['backURI'] : '';
$fullBackURL = "5sladmin_iframe.php";
if(!empty($backURI) && strlen($backURI) % 2 === 0){
  $fullBackURL = "5sladmin_iframe.php?backURI=".$backURI;
} else {
  header("Location: 5sladmin.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>	
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="./imageFiles/favicon.ico" type="image/ico" />
  <title>Fifth Season Life 人生第五季 | Lockscreen</title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="./pluginFiles/googleapis/fonts.googleapis.css">
  <!-- Bootstrap 5 & Bootstrap Icons -->
  <link rel="stylesheet" href="./pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="./pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <!-- Theme style (AdminLTE 4) -->
  <link rel="stylesheet" href="./cssFiles/adminlte.css">

  <style>
    @keyframes wobbleLogo {
        0%, 100% { transform: translateX(0%) rotate(0deg); }
        15% { transform: translateX(-12%) rotate(-5deg); }
        30% { transform: translateX(10%) rotate(3deg); }
        45% { transform: translateX(-8%) rotate(-3deg); }
        60% { transform: translateX(5%) rotate(2deg); }
        75% { transform: translateX(-2%) rotate(-1deg); }
    }
    .animation__wobble {
        animation: wobbleLogo 2s ease-in-out infinite;
    }
    .btn-primary {
        color: #fff;
        background-color: #133b61;
        border-color: rgba(99, 99, 99, 1.00);
    }
    .btn-primary:hover, .btn-primary:focus {
        color: #fff;
        background-color: #1A5388;
        border-color: #236FB7;
    }
  </style>
</head>
<body class="lockscreen bg-body-secondary">
<!-- Automatic element centering -->
<div class="lockscreen-wrapper" style="max-width: 400px; margin: 10% auto 0 auto;">
  <div class="lockscreen-logo text-center mb-3">
      <img src="imageFiles/preloaderLOGO.png" alt="Fifth Season Life Logo" class="brand-image animation__wobble d-block mx-auto mb-2" height="80" width="80">
      <div class="brand-text fw-bold fs-4">Fifth Season Life<span class="fw-light"> ADMIN</span></div>
  </div>
  <hr>
  <!-- User name -->
  <div class="lockscreen-name text-center fw-bold mb-3"><?php
  if(!empty($_SESSION["SESS_AD_NICK"])){ echo $_SESSION["SESS_AD_NICK"]; }
  else{
    if(!empty($_SESSION["SESS_AD_NAME"])){ echo $_SESSION["SESS_AD_NAME"]; }
    else{ echo 'Administrator ID: '.$adID; }
  }
  ?></div>
  <!-- START LOCK SCREEN ITEM -->
  <div class="lockscreen-item shadow-sm">
    <!-- lockscreen image -->
    <div class="lockscreen-image">
      <img src="<?php echo !empty($_SESSION['SESS_AD_THUMB']) ? $_SESSION['SESS_AD_THUMB'] : 'imageFiles/nopic_user_128x128.jpg'; ?>" alt="User Image">
    </div>
    <!-- /.lockscreen-image -->

    <!-- lockscreen credentials (contains the form) -->
    <form id="lockForm" class="lockscreen-credentials" action="pageFiles/ad_home.php" method="POST">
      <div class="input-group">
        <input type="password" id="reenterpass" name="reenterpass" class="form-control" placeholder="請輸入登入密碼" autofocus>
        <button id="but_reenterpass" name="but_reenterpass" type="submit" class="btn btn-light border-start">
          <i class="bi bi-arrow-right text-muted"></i>
        </button>
      </div>
    </form>
    <!-- /.lockscreen credentials -->
  </div>
  <!-- /.lockscreen-item -->

  <hr>
  <div class="help-block text-center small text-muted mb-2">
    操作功能已鎖定，請輸入密碼回復至鎖定前頁面
  </div>
  <div class="text-center mb-3">
    <a href="5sladmin.php" class="text-decoration-none">或使用其他帳號登入</a>
  </div>
  <div class="help-block text-center small text-secondary mb-4">
    請注意！超過 30 分鐘沒有登入動作，將自動完全登出系統。
  </div>
  <div class="lockscreen-footer text-center small text-muted">
    Copyright &copy; 2024 <b><a href="https://5seasonlife.com" class="text-dark text-decoration-none">5SeasonLife</a></b> All rights reserved
  </div>
</div>
<!-- /.center -->

<!-- Bootstrap 5 原生 Toast 容器 (供獨立頁面顯示錯誤提示) -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 10900;" id="bs5ToastContainer"></div>

<!-- jQuery -->
<script src="./pluginFiles/jquery/jquery-4.0.0.min.js"></script>
<!-- Bootstrap 5 -->
<script src="./pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
<!-- 5SeasonLife App -->
<script src="./scriptFiles/adminlte.js"></script>
<script src="scriptFiles/page.basic.js"></script>
<script>
// 獨立頁面專用的 Bootstrap 5 Toast 函式
function showLocalToast(type, msg) {
  const container = document.getElementById('bs5ToastContainer');
  if (!container || typeof bootstrap === 'undefined') {
    alert(msg);
    return;
  }
  const bgClass = (type === 'error' || type === 'danger') ? 'text-bg-danger' : (type === 'warning' ? 'text-bg-warning' : 'text-bg-info');
  const iconClass = (type === 'error' || type === 'danger') ? 'bi-x-circle-fill' : 'bi-exclamation-triangle-fill';
  const toastId = 'toast_' + Date.now();
  const html = `
    <div id="${toastId}" class="toast align-items-center ${bgClass} border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3500">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2">
          <i class="bi ${iconClass}"></i>
          <span>${msg}</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `;
  container.insertAdjacentHTML('beforeend', html);
  const el = document.getElementById(toastId);
  new bootstrap.Toast(el).show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}

$(function () {
  // 支援按下 Enter 鍵或點擊箭頭按鈕送出解鎖
  $('#lockForm').on('submit', function(e){
    e.preventDefault();
    var rePass = $('#reenterpass').val().trim();
    if(rePass == ''){
      showLocalToast('error', '密碼欄位不得為空！！');
      $('#reenterpass').focus();
      return false;
    }
    var datA = {
      reenterPass: rePass,
      adID: '<?php echo $adID; ?>'
    };
    $.ajax({
      type: 'post',
      url: 'funcFiles/ad_sesspass_func.php',
      data: {
        'arrDatA': datA, 
        'formAcce': 'form_reenterPass',
        'token': getTokenVal()
      },
      dataType: 'json',
      success: function (data) {
        if(data['RTN'] == 1){
          window.location.replace("pageFiles/<?php echo $fullBackURL; ?>");
        } else {
          showLocalToast(data['msgType'] || 'error', data['msgStr'] || '密碼驗證錯誤！');
          $('#reenterpass').val('').focus();
        }
      },
      error: function (data) {
        console.log(data);
        showLocalToast('error', '系統錯誤，請聯絡管理人員！！');
      }
    });
  });

  //-------- 計時自動登出 ---------
  setTimeout(function() { 
    window.location.replace("5sladmin.php");
  }, 1800000); // 30分鐘
});

// 移除問號（參數）和井字號（錨點），只保留純路徑
window.addEventListener('DOMContentLoaded', () => {
    window.history.replaceState({}, '', window.location.pathname);
});
</script>
</body>
</html>
