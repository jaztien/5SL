<?php
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
session_start();
if(!isset($_SESSION["SESS_AD_ID"]) || empty($_SESSION["SESS_AD_ID"])){
	header("location: 5sladmin.php?e=9");//遺失 SESSION，可能閒置超時，系統自動登出！
	exit();
}
$adID = 0;
if(!empty($_SESSION["SESS_AD_ID"])){
  $adID = $_SESSION["SESS_AD_ID"];
  define('DOC_PATH_LAYER', './');
  require_once('includeFiles/db.inc.php');
  $ilog = $DB->insSysLogs(51, $adID, 1111, '["'.$adID.'"]');//寫入操作記錄
}
unset($_SESSION["SESS_AD_ID"]);
session_regenerate_id(TRUE);
//更新註冊過後的 TOKEN ，降低被盜取的機率
require_once('includeFiles/common.lib.php');
$_SESSION['token'] = _fn_FHMD5_encrypt(strtolower($_SERVER['HTTP_USER_AGENT']).'PHPSESSID='.session_id());
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
$PAGE_MARK = "PG_LOCKPAGE";//鎖定頁面（登出後保留 Session中的操作者ID資料）
$PAGE_ITEM = "ITEM_LOCK";
//
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
<html lang="en">
<head>	
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
  <title>Fifth Season Life 人生第五季 | Lockscreen</title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="pluginFiles/fontawesome-free/css/all.css">
  <!-- Toastr -->
  <link rel="stylesheet" href="pluginFiles/toastr/toastr.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="cssFiles/5sladmin.light.css">
  <style>
    .btn-primary{color:#fff;background-color:#133b61;border-color:rgba(99,99,99,1.00);box-shadow:none}
    .btn-primary:hover{color:#fff;background-color:#1A5388;border-color:#236FB7}
    .btn-primary.focus,.btn-primary:focus{color:#fff;background-color:#1A5388;border-color:#236FB7;box-shadow:0 0 0 0 rgba(38,143,255,.5)}    
    .btn-primary.disabled, .btn-primary:disabled {color: #fff;background-color: #3F8ED9;border-color: #3F8ED9;}
    .btn-primary:not(:disabled):not(.disabled):active, .btn-primary:not(:disabled):not(.disabled).active,
    .show > .btn-primary.dropdown-toggle {color: #fff;background-color: #236FB7;border-color: #005cbf;}
    .btn-primary:not(:disabled):not(.disabled):active:focus, .btn-primary:not(:disabled):not(.disabled).active:focus,
    .show > .btn-primary.dropdown-toggle:focus {box-shadow: 0 0 0 0 rgba(38, 143, 255, 0.5);}
  </style>
  
</head>
<body class="hold-transition login-page">
<!-- Automatic element centering -->
<div class="login-box">
  <div class="login-logo">
      <img src="imageFiles/preloaderLOGO.png" alt="Fifth Season Life Logo" class="brand-image animation__wobble"  style="vertical-align:baseline;" height="80" width="80">
      <div class="brand-text font-weight-bold" style="margin-top: -10px;margin-bottom: -8px;">Fifth Season Life<span class="brand-text font-weight-light"> ADMIN</span></div>
  </div>
  <hr>
  <!-- User name -->
  <div class="lockscreen-name text-center font-weight-bolder"><?php
  if(!empty($_SESSION["SESS_AD_NICK"])){ echo $_SESSION["SESS_AD_NICK"]; }
  else{
    if(!empty($_SESSION["SESS_AD_NAME"])){ echo $_SESSION["SESS_AD_NAME"]; }
    else{ echo 'Administrator ID: '.$_SESSION["SESS_AD_ID"]; }
  }
  ?></div>
  <!-- START LOCK SCREEN ITEM -->
  <div class="lockscreen-item">
    <!-- lockscreen image -->
    <div class="lockscreen-image">
      <img src="<?php echo $_SESSION['SESS_AD_THUMB']; ?>" alt="User Image">
    </div>
    <!-- /.lockscreen-image -->

    <!-- lockscreen credentials (contains the form) -->
    <form class="lockscreen-credentials" action="pageFiles/ad_home.php" method="POST">
      <div class="input-group">
        <input type="password" id="reenterpass" name="reenterpass" class="form-control" placeholder="password">

        <div class="input-group-append">
          <button id="but_reenterpass" name="but_reenterpass" type="button" class="btn">
            <i class="fas fa-arrow-right text-muted"></i>
          </button>
        </div>
      </div>
    </form>
    <!-- /.lockscreen credentials -->

  </div>
  <!-- /.lockscreen-item -->
  <hr>
  <div class="help-block text-center small">
    <!-- Enter your password to retrieve your session -->
    操作功能已鎖定，請輸入密碼回復至鎖定前頁面
  </div>
  <div class="text-center">
    <a href="5sladmin.php"><!-- Or sign in as a different user -->或使用其他帳號登入</a>
  </div>
  <div class="help-block text-center small">
    請注意！超過10分鐘沒有登入動作，將自動完全登出系統。
  </div>
  <div class="lockscreen-footer text-center">
    Copyright &copy; 2024 <b><a href="https://www.5seasonlife.com/" class="text-black">5SeasonLife</a> </b>All rights reserved<br>
    
  </div>
</div>
<!-- /.center -->

<!-- jQuery -->
<script src="pluginFiles/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Toastr -->
<script src="pluginFiles/toastr/toastr.min.js"></script>
<!-- 5SeasonLife App -->
<script src="scriptFiles/page.basic.js"></script>
<script>
$(function () {
  $('#but_reenterpass').click(function(e){
    var rePass = $('#reenterpass').val();
    rePass = rePass.trim();
    if(rePass==''){
      toastr['error']('密碼欄位不得為空！！');
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
            async: false, 
          success: function (data) {
            if(data['RTN']==1){
              //toastr[data['msgType']](data['msgStr']);
              window.location.replace("pageFiles/<?php echo $fullBackURL; ?>");
              //window.history.back();
            }else{
              window.parent.toastr[data['msgType']](data['msgStr']);
            }
          },
          error: function (data) {
              console.log(data);
              toastr['error']('系統錯誤，請聯絡管理人員！！');
          }
    });

  });

  //-------- 計時自動登出 ---------
  setTimeout(function() { 
    window.location.replace("5sladmin.php");
    }, 1800000);//1800000

});
//移除問號（參數）和井字號（錨點），只保留純路徑
window.addEventListener('DOMContentLoaded', () => {
    window.history.replaceState({}, '', window.location.pathname);
});

</script>
</body>
</html>
