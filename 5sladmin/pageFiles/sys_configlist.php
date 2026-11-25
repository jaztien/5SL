<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_SYSCONFIG');
define('PAGE_ITEM','ITEM_SYS');
define('bkRedir',1);
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$sr = DB->query("SELECT * FROM fsl_configurations WHERE config_autoload=?",array('1'));
$i = 0; 
$cr = array();
while($i<count($sr)){ 
  $ari = array($sr[$i]['config_code'] => ($sr[$i]['config_crypt']==1) ? $sr[$i]['config_value'] : $sr[$i]['config_value']);
  $cr = array_merge($cr, $ari);
  $i++;
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
  <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
	
  <title> Fifth Season Life 人生第五季 | 系統參數設定 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <!-- Bootstrap 5 & Bootstrap Icons -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <!-- Theme style (AdminLTE 4) -->
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
<!-- -------------------------- /CSS Link -------------------------- -->
<style>
  /* 測試環境開關開啟時呈現紅色警戒狀態（對應原本 custom-switch-on-danger） */
  .switch-env-danger:checked {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
  }
  .switch-env-danger:focus {
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
  }
</style>
</head>
<!-- [變更1] 套用 AdminLTE 4 基礎背景與 theme-green 主題 -->
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
              <h4 class="m-0">系統參數設定</h4>
            </div>
            <div class="col-sm-6">
              <!-- [變更5] float-sm-right 改為 float-sm-end -->
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">系統設定管理</li>
                <li class="breadcrumb-item active">系統參數設定</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
      <!-- /頁頭導覽條 -->

      <!-- 主要內容區域：[變更6] .content 改為 .app-content -->
      <div class="app-content">
        <div class="container-fluid tab-pane active" id="sysinfo">
          <form id="form_sysinfo" name="form_sysinfo" method="post" action="../funcFiles/sys_act_func.php" data-formmode="infoupdate" data-func="func_sys_config" class="needs-validation" novalidate>
            <div class="row g-3">
              <!-- ==================== 左側欄位 ==================== -->
              <div class="col-md-6">
                
                <!-- 1. 公司基本資料卡片（修正 LOGO 層級浮於色塊上方） -->
                <div class="card shadow-sm mb-3 overflow-hidden">
                  <div class="card-header text-bg-info bg-gradient py-3 pb-5 text-center border-0">
                    <h4 class="mb-1 fw-bold">Fifth Season Life</h4>
                    <div class="small opacity-75">人生第五季生命藝術工作室</div>
                  </div>
                  <!-- [修正] 加上 position-relative z-1 提升層級 -->
                  <div class="text-center position-relative z-1" style="margin-top: -42px;">
                    <img class="rounded-circle shadow border border-3 border-white bg-white" src="../imageFiles/logoSRGB.jpg" alt="5SL Logo" width="84" height="84">
                  </div>
                  <div class="card-body pt-3">
                    <div class="mb-3">
                      <label for="comp_fullname" class="form-label">公司全名</label>
                      <input type="text" class="form-control" id="comp_fullname" name="comp_fullname" value="<?php echo $cr['comp_fullname']??''; ?>" placeholder="公司全名" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-3">
                      <label for="comp_enname" class="form-label">公司英文全名</label>
                      <input type="text" class="form-control" id="comp_enname" name="comp_enname" value="<?php echo $cr['comp_enname']??''; ?>" placeholder="公司英文全名" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label for="comp_shortname" class="form-label">公司簡稱</label>
                        <input type="text" class="form-control" id="comp_shortname" name="comp_shortname" value="<?php echo $cr['comp_shortname']??''; ?>" placeholder="公司簡稱" required<?php echo NreadOnly; ?>>
                      </div>
                      <div class="col-sm-6">
                        <label for="comp_enshort" class="form-label">公司英文簡稱</label>
                        <input type="text" class="form-control" id="comp_enshort" name="comp_enshort" value="<?php echo $cr['comp_enshort']??''; ?>" placeholder="公司英文簡稱" required<?php echo NreadOnly; ?>>
                      </div>
                    </div>
                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label for="comp_abbr" class="form-label">公司縮寫</label>
                        <input type="text" class="form-control" id="comp_abbr" name="comp_abbr" value="<?php echo $cr['comp_abbr']??''; ?>" placeholder="公司縮寫" required<?php echo NreadOnly; ?>>
                      </div>
                      <div class="col-sm-6">
                        <label for="comp_enabbr" class="form-label">公司英文縮寫</label>
                        <input type="text" class="form-control" id="comp_enabbr" name="comp_enabbr" value="<?php echo $cr['comp_enabbr']??''; ?>" placeholder="公司英文縮寫" required<?php echo NreadOnly; ?>>
                      </div>
                    </div>
                    <div class="mb-3">
                      <label for="comp_address" class="form-label">公司住址</label>
                      <input type="text" class="form-control" id="comp_address" name="comp_address" value="<?php echo $cr['comp_address']??''; ?>" placeholder="公司住址" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-3">
                      <label for="comp_email" class="form-label">電子郵件</label>
                      <input type="email" class="form-control" id="comp_email" name="comp_email" value="<?php echo $cr['comp_email']??''; ?>" placeholder="電子郵件" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-3">
                      <label for="comp_tel" class="form-label">公司電話</label>
                      <input type="text" class="form-control" id="comp_tel" name="comp_tel" value="<?php echo $cr['comp_tel']??''; ?>" placeholder="公司電話" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-3">
                      <label for="comp_tollfree" class="form-label">免付費電話</label>
                      <input type="text" class="form-control" id="comp_tollfree" name="comp_tollfree" value="<?php echo $cr['comp_tollfree']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-0">
                      <label for="comp_fax" class="form-label">傳真號碼</label>
                      <input type="text" class="form-control" id="comp_fax" name="comp_fax" value="<?php echo $cr['comp_fax']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                  </div>
                </div>

                <!-- 2. 郵件與 SMTP 設定卡片 -->
                <div class="card shadow-sm mb-3">
                  <div class="card-header text-bg-danger">
                    <h5 class="card-title mb-0"><i class="bi bi-envelope-at me-2"></i>郵件與 SMTP 設定</h5>
                  </div>
                  <div class="card-body">
                    <div class="mb-3">
                      <label for="mail_notifyadd" class="form-label">系統通知信箱</label>
                      <input type="email" class="form-control" id="mail_notifyadd" name="mail_notifyadd" value="<?php echo $cr['mail_notifyadd']??''; ?>" placeholder="系統通知信箱" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-3">
                      <label for="mail_notifytItle" class="form-label">通知對象</label>
                      <input type="text" class="form-control" id="mail_notifytItle" name="mail_notifytItle" value="<?php echo $cr['mail_notifytItle']??''; ?>" placeholder="通知對象" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label for="mail_cc1add" class="form-label">副本信箱1</label>
                        <input type="text" class="form-control" id="mail_cc1add" name="mail_cc1add" value="<?php echo $cr['mail_cc1add']??''; ?>"<?php echo NreadOnly; ?>>
                      </div>
                      <div class="col-sm-6">
                        <label for="mail_cc1title" class="form-label">副本信箱1抬頭</label>
                        <input type="text" class="form-control" id="mail_cc1title" name="mail_cc1title" value="<?php echo $cr['mail_cc1title']??''; ?>"<?php echo NreadOnly; ?>>
                      </div>
                    </div>
                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label for="mail_cc2add" class="form-label">副本信箱2</label>
                        <input type="text" class="form-control" id="mail_cc2add" name="mail_cc2add" value="<?php echo $cr['mail_cc2add']??''; ?>"<?php echo NreadOnly; ?>>
                      </div>
                      <div class="col-sm-6">
                        <label for="mail_cc2title" class="form-label">副本信箱2抬頭</label>
                        <input type="text" class="form-control" id="mail_cc2title" name="mail_cc2title" value="<?php echo $cr['mail_cc2title']??''; ?>"<?php echo NreadOnly; ?>>
                      </div>
                    </div>
                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label for="mail_bcc1add" class="form-label">密件副本信箱1</label>
                        <input type="text" class="form-control" id="mail_bcc1add" name="mail_bcc1add" value="<?php echo $cr['mail_bcc1add']??''; ?>"<?php echo NreadOnly; ?>>
                      </div>
                      <div class="col-sm-6">
                        <label for="mail_bcc1title" class="form-label">密件副本信箱1抬頭</label>
                        <input type="text" class="form-control" id="mail_bcc1title" name="mail_bcc1title" value="<?php echo $cr['mail_bcc1title']??''; ?>"<?php echo NreadOnly; ?>>
                      </div>
                    </div>
                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label for="mail_bcc2add" class="form-label">密件副本信箱2</label>
                        <input type="text" class="form-control" id="mail_bcc2add" name="mail_bcc2add" value="<?php echo $cr['mail_bcc2add']??''; ?>"<?php echo NreadOnly; ?>>
                      </div>
                      <div class="col-sm-6">
                        <label for="mail_bcc2title" class="form-label">密件副本信箱2抬頭</label>
                        <input type="text" class="form-control" id="mail_bcc2title" name="mail_bcc2title" value="<?php echo $cr['mail_bcc2title']??''; ?>"<?php echo NreadOnly; ?>>
                      </div>
                    </div>
                    <hr class="grouphr">
                    <div class="groupfield fw-bold mb-2 text-secondary">SMTP 伺服器</div>
                    <div class="mb-3">
                      <label for="mail_fromadd" class="form-label">寄件信箱</label>
                      <input type="text" class="form-control" id="mail_fromadd" name="mail_fromadd" value="<?php echo $cr['mail_fromadd']??''; ?>" placeholder="寄件信箱" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-3">
                      <label for="mail_fromtitle" class="form-label">寄件人抬頭</label>
                      <input type="text" class="form-control" id="mail_fromtitle" name="mail_fromtitle" value="<?php echo $cr['mail_fromtitle']??''; ?>" placeholder="寄件人抬頭" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-3">
                      <label for="smtp_host" class="form-label">SMTP 伺服器</label>
                      <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo $cr['smtp_host']??''; ?>" placeholder="SMTP 伺服器" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label for="smtp_accuont" class="form-label">SMTP 帳號</label>
                        <input type="text" class="form-control" id="smtp_accuont" name="smtp_accuont" value="<?php echo $cr['smtp_accuont']??''; ?>" placeholder="SMTP 帳號" required<?php echo NreadOnly; ?>>
                      </div>
                      <div class="col-sm-6">
                        <label for="smtp_pass" class="form-label">SMTP 密碼</label>
                        <input type="text" class="form-control" id="smtp_pass" name="smtp_pass" value="<?php echo $cr['smtp_pass']??''; ?>" placeholder="SMTP 密碼" required<?php echo NreadOnly; ?>>
                      </div>
                    </div>
                    <div class="row g-2">
                      <div class="col-sm-4">
                        <label for="smtp_smode" class="form-label">加密方式</label>
                        <select class="form-select" id="smtp_smode" name="smtp_smode"<?php echo NreadOnly; ?>>
                          <option value="1"<?php if(isset($cr['smtp_smode']) && ($cr['smtp_smode']=="TLS" || $cr['smtp_smode']=="1")) echo " selected"; ?>>TLS</option>
                          <option value="2"<?php if(isset($cr['smtp_smode']) && ($cr['smtp_smode']=="SSL" || $cr['smtp_smode']=="2")) echo " selected"; ?>>SSL</option>
                        </select>
                      </div>
                      <div class="col-sm-4">
                        <label for="smtp_tlsport" class="form-label">TLS 連接埠</label>
                        <input type="text" class="form-control" id="smtp_tlsport" name="smtp_tlsport" value="<?php echo $cr['smtp_tlsport']??''; ?>" placeholder="TLS 連接埠" required<?php echo NreadOnly; ?>>
                      </div>
                      <div class="col-sm-4">
                        <label for="smtp_sslport" class="form-label">SSL 連接埠</label>
                        <input type="text" class="form-control" id="smtp_sslport" name="smtp_sslport" value="<?php echo $cr['smtp_sslport']??''; ?>" placeholder="SSL 連接埠" required<?php echo NreadOnly; ?>>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- 3. Zoom API 設定卡片 -->
                <div class="card shadow-sm mb-3">
                  <div class="card-header text-bg-primary bg-gradient d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0"><i class="bi bi-camera-video me-2"></i>Zoom API</h5>
                    <button type="button" id="btnZoomPing" class="btn btn-sm btn-light text-primary fw-semibold" title="測試 Zoom API 連線" data-bs-toggle="tooltip" data-bs-placement="top">
                      <i class="bi bi-plug-fill me-1"></i>測試連線
                    </button>
                  </div>
                  <div class="card-body">
                    <div class="mb-3">
                      <label for="zoom_accountid" class="form-label">帳戶ID (Account ID)</label>
                      <input type="text" class="form-control" id="zoom_accountid" name="zoom_accountid" value="<?php echo $cr['zoom_accountid']??''; ?>" placeholder="帳戶ID(Account ID)" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-3">
                      <label for="zoom_clientid" class="form-label">用戶ID (Client ID)</label>
                      <input type="text" class="form-control" id="zoom_clientid" name="zoom_clientid" value="<?php echo $cr['zoom_clientid']??''; ?>" placeholder="用戶ID(Client ID)" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-3">
                      <label for="zoom_clientsecret" class="form-label">用戶密鑰 (Client Secret)</label>
                      <input type="text" class="form-control" id="zoom_clientsecret" name="zoom_clientsecret" value="<?php echo $cr['zoom_clientsecret']??''; ?>" placeholder="用戶密鑰(Client Secret)" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-0">
                      <label for="zoom_secrettoken" class="form-label">安全憑證 (Secret Token)</label>
                      <input type="text" class="form-control" id="zoom_secrettoken" name="zoom_secrettoken" value="<?php echo $cr['zoom_secrettoken']??''; ?>" placeholder="安全憑證(Secret Token)" required<?php echo NreadOnly; ?>>
                    </div>
                  </div>  
                </div>

              </div>
              <!-- /左側欄位 -->

              <!-- ==================== 右側欄位 ==================== -->
              <div class="col-md-6">
                
                <!-- 4. 系統訊息設定卡片 -->
                <div class="card shadow-sm mb-3">
                  <div class="card-header text-bg-warning">
                    <h5 class="card-title mb-0"><i class="bi bi-hdd-rack me-2"></i>系統訊息</h5>
                  </div>
                  <div class="card-body">
                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label for="sys_serverstatus" class="form-label">主機停機狀態</label>
                        <select class="form-select" id="sys_serverstatus" name="sys_serverstatus"<?php echo NreadOnly; ?>>
                          <option value=""></option>
                          <option value="1"<?php if(isset($cr['sys_serverstatus']) && $cr['sys_serverstatus']==1) echo " selected"; ?>>正常運行中</option>
                          <option value="2"<?php if(isset($cr['sys_serverstatus']) && $cr['sys_serverstatus']==2) echo " selected"; ?>>停機中</option>
                        </select>
                      </div>
                      <div class="col-sm-6">
                        <label for="sys_stopbrief" class="form-label">停機簡述</label>
                        <input type="text" class="form-control" id="sys_stopbrief" name="sys_stopbrief" value="<?php echo $cr['sys_stopbrief']??''; ?>"<?php echo NreadOnly; ?>>
                      </div>
                    </div>

                    <div class="mb-3">
                      <label for="sys_stopdes" class="form-label">停機說明</label>
                      <textarea class="form-control" id="sys_stopdes" name="sys_stopdes" rows="3"<?php echo NreadOnly; ?>><?php echo $cr['sys_stopdes']??''; ?></textarea>
                    </div>
                    <div class="mb-0">
                      <label for="sys_stopdirto" class="form-label">停機指向網址</label>
                      <input type="text" class="form-control" id="sys_stopdirto" name="sys_stopdirto" value="<?php echo $cr['sys_stopdirto']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                  </div>
                </div>

                <!-- 5. 後台功能參數設定 -->
                <div class="card shadow-sm mb-3">
                  <div class="card-header text-bg-info">
                    <h5 class="card-title mb-0"><i class="bi bi-sliders me-2"></i>後台功能參數設定</h5>
                  </div>
                  <div class="card-body">
                    <div class="mb-3">
                      <label for="sys_version" class="form-label">系統版本</label>
                      <input type="text" class="form-control" id="sys_version" name="sys_version" value="<?php echo $cr['sys_version']??''; ?>">
                    </div>
                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label for="sys_lang" class="form-label">主顯示語言</label>
                        <select class="form-select" id="sys_lang" name="sys_lang">
                          <option value="zh-tw" selected>繁體中文</option>
                        </select>
                      </div>
                      <div class="col-sm-6">
                        <label for="sys_idleout" class="form-label">閒置登出時限</label>
                        <input type="text" class="form-control" id="sys_idleout" name="sys_idleout" value="<?php echo $cr['sys_idleout']??''; ?>" placeholder="閒置登出時限" required>
                      </div>
                    </div>
                    <div class="mb-3">
                      <label for="page_title" class="form-label">頁面抬頭</label>
                      <input type="text" class="form-control" id="page_title" name="page_title" value="<?php echo $cr['page_title']??''; ?>" placeholder="頁面抬頭" required>
                    </div>
                    <div class="mb-0">
                      <label for="page_metades" class="form-label">頁面描述</label>
                      <textarea class="form-control" id="page_metades" name="page_metades" rows="3"><?php echo $cr['page_metades']??''; ?></textarea>
                    </div>
                  </div>
                </div>

                <!-- 6. 線上支付參數設定 -->
                <div class="card shadow-sm mb-3">
                  <div class="card-header text-bg-primary">
                    <h5 class="card-title mb-0"><i class="bi bi-credit-card me-2"></i>線上支付參數設定</h5>
                  </div>
                  <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                      <span class="fw-semibold text-success">正式環境</span>
                      <div class="form-check form-switch mb-0">
                        <input class="form-check-input switch-env-danger" type="checkbox" role="switch" id="qpay_test" name="qpay_test" value="1"<?php echo isset($cr['qpay_test']) && $cr['qpay_test']=="1" ? " checked" : ""; ?><?php echo NdisAble; ?>>
                        <label class="form-check-label fw-semibold text-danger" for="qpay_test">測試環境</label>
                      </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                      <label for="qpay_url" class="form-label">正式API網址</label>
                      <input type="text" class="form-control" id="qpay_url" name="qpay_url" value="<?php echo $cr['qpay_url']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-3">
                      <label for="qpay_testurl" class="form-label">測試API網址</label>
                      <input type="text" class="form-control" id="qpay_testurl" name="qpay_testurl" value="<?php echo $cr['qpay_testurl']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-3">
                      <label for="qpay_shopno" class="form-label">商店代號</label>
                      <input type="text" class="form-control" id="qpay_shopno" name="qpay_shopno" value="<?php echo $cr['qpay_shopno']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-3">
                      <label for="qpay_authcode" class="form-label">授權認證金鑰</label>
                      <input type="text" class="form-control" id="qpay_authcode" name="qpay_authcode" value="<?php echo $cr['qpay_authcode']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label for="qpay_hashA1" class="form-label">雜湊值A1</label>
                        <input type="text" class="form-control" id="qpay_hashA1" name="qpay_hashA1" value="<?php echo $cr['qpay_hashA1']??''; ?>"<?php echo NreadOnly; ?>>
                      </div>
                      <div class="col-sm-6">
                        <label for="qpay_hashA2" class="form-label">雜湊值A2</label>
                        <input type="text" class="form-control" id="qpay_hashA2" name="qpay_hashA2" value="<?php echo $cr['qpay_hashA2']??''; ?>"<?php echo NreadOnly; ?>>
                      </div>
                    </div>
                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label for="qpay_hashB1" class="form-label">雜湊值B1</label>
                        <input type="text" class="form-control" id="qpay_hashB1" name="qpay_hashB1" value="<?php echo $cr['qpay_hashB1']??''; ?>"<?php echo NreadOnly; ?>>
                      </div>
                      <div class="col-sm-6">
                        <label for="qpay_hashB2" class="form-label">雜湊值B2</label>
                        <input type="text" class="form-control" id="qpay_hashB2" name="qpay_hashB2" value="<?php echo $cr['qpay_hashB2']??''; ?>"<?php echo NreadOnly; ?>>
                      </div>
                    </div>
                    <div class="mb-3">
                      <label for="qpay_returnurl" class="form-label">回傳值接收器URL(即時)</label>
                      <input type="text" class="form-control" id="qpay_returnurl" name="qpay_returnurl" value="<?php echo $cr['qpay_returnurl']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-0">
                      <label for="qpay_backendurl" class="form-label">回傳值接收器URL(排程)</label>
                      <input type="text" class="form-control" id="qpay_backendurl" name="qpay_backendurl" value="<?php echo $cr['qpay_backendurl']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                  </div>
                </div>

                <!-- 7. 發票開立參數設定 -->
                <div class="card shadow-sm mb-3">
                  <div class="card-header text-bg-success">
                    <h5 class="card-title mb-0"><i class="bi bi-receipt me-2"></i>發票開立參數設定</h5>
                  </div>
                  <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                      <span class="fw-semibold text-success">正式環境</span>
                      <div class="form-check form-switch mb-0">
                        <input class="form-check-input switch-env-danger" type="checkbox" role="switch" id="inv_test" name="inv_test" value="1"<?php echo isset($cr['inv_test']) && $cr['inv_test']=="1" ? " checked" : ""; ?><?php echo NdisAble; ?>>
                        <label class="form-check-label fw-semibold text-danger" for="inv_test">測試環境</label>
                      </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                      <label for="inv_url" class="form-label">正式API網址</label>
                      <input type="text" class="form-control" id="inv_url" name="inv_url" value="<?php echo $cr['inv_url']??''; ?>" placeholder="正式API網址" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-3">
                      <label for="inv_testurl" class="form-label">測試API網址</label>
                      <input type="text" class="form-control" id="inv_testurl" name="inv_testurl" value="<?php echo $cr['inv_testurl']??''; ?>" placeholder="測試API網址" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label for="inv_taxid" class="form-label">營業人統一編號</label>
                        <input type="text" class="form-control" id="inv_taxid" name="inv_taxid" value="<?php echo $cr['inv_taxid']??''; ?>" placeholder="營業人統一編號" required<?php echo NreadOnly; ?>>
                      </div>
                      <div class="col-sm-6">
                        <label for="inv_seed" class="form-label">編碼鹽值</label>
                        <input type="text" class="form-control" id="inv_seed" name="inv_seed" value="<?php echo $cr['inv_seed']??''; ?>" placeholder="編碼鹽值" required<?php echo NreadOnly; ?>>
                      </div>
                    </div>
                    <div class="mb-3">
                      <label for="inv_akey" class="form-label">啟動序號</label>
                      <input type="text" class="form-control" id="inv_akey" name="inv_akey" value="<?php echo $cr['inv_akey']??''; ?>" placeholder="啟動序號" required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="mb-3">
                      <label for="inv_auth" class="form-label">驗證編碼</label>
                      <input type="text" class="form-control" id="inv_auth" name="inv_auth" value="<?php echo $cr['inv_auth']??''; ?>" placeholder="驗證編碼" required readonly>
                    </div>
                    <div class="mb-3">
                      <label for="inv_token" class="form-label">用戶認證碼</label>
                      <div class="input-group">
                        <input type="text" class="form-control" id="inv_token" name="inv_token" value="<?php echo $cr['inv_token']??''; ?>" placeholder="用戶認證碼" required>
                        <button type="button" id="getNewToken" name="getNewToken" class="btn btn-success">重新取得認證碼</button>
                      </div>
                    </div>
                    <div class="mb-0">
                      <label for="inv_tokendate" class="form-label">認證碼取得日期時間</label>
                      <input type="text" class="form-control" id="inv_tokendate" name="inv_tokendate" value="<?php echo $cr['inv_tokendate']??''; ?>" placeholder="認證碼取得日期時間" required readonly>
                    </div>
                  </div>
                </div>

                <!-- 8. 圖像參數設定 -->
                <div class="card shadow-sm mb-3">
                  <div class="card-header text-bg-secondary">
                    <h5 class="card-title mb-0"><i class="bi bi-images me-2"></i>圖像參數設定</h5>
                  </div>
                  <div class="card-body">
                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label for="image_title" class="form-label">主顯示語言</label>
                        <input type="text" class="form-control" id="image_title" name="image_title" value="<?php echo $cr['image_title']??''; ?>">
                      </div>
                      <div class="col-sm-6">
                        <label for="web_title" class="form-label">備用標題</label>
                        <input type="text" class="form-control" id="web_title" name="web_title" value="<?php echo $cr['web_title']??''; ?>">
                      </div>
                    </div>

                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label for="md_photoW" class="form-label">課程商品圖像尺寸：寬</label>
                        <div class="input-group">
                          <input type="text" class="form-control" id="md_photoW" name="md_photoW" value="<?php echo $cr['md_photoW']??''; ?>">
                          <span class="input-group-text">px</span>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <label for="md_photoH" class="form-label">高</label>
                        <div class="input-group">
                          <input type="text" class="form-control" id="md_photoH" name="md_photoH" value="<?php echo $cr['md_photoH']??''; ?>">
                          <span class="input-group-text">px</span>
                        </div>
                      </div>
                    </div>

                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label for="md_thumbMW" class="form-label">課程商品中縮圖尺寸：寬</label>
                        <div class="input-group">
                          <input type="text" class="form-control" id="md_thumbMW" name="md_thumbMW" value="<?php echo $cr['md_thumbMW']??''; ?>">
                          <span class="input-group-text">px</span>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <label for="md_thumbMH" class="form-label">高</label>
                        <div class="input-group">
                          <input type="text" class="form-control" id="md_thumbMH" name="md_thumbMH" value="<?php echo $cr['md_thumbMH']??''; ?>">
                          <span class="input-group-text">px</span>
                        </div>
                      </div>
                    </div>

                    <div class="row g-2 mb-0">
                      <div class="col-sm-6">
                        <label for="md_thumbSW" class="form-label">課程商品小縮圖尺寸：寬</label>
                        <div class="input-group">
                          <input type="text" class="form-control" id="md_thumbSW" name="md_thumbSW" value="<?php echo $cr['md_thumbSW']??''; ?>">
                          <span class="input-group-text">px</span>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <label for="md_thumbSH" class="form-label">高</label>
                        <div class="input-group">
                          <input type="text" class="form-control" id="md_thumbSH" name="md_thumbSH" value="<?php echo $cr['md_thumbSH']??''; ?>">
                          <span class="input-group-text">px</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
              <!-- /右側欄位 -->
            </div>
          </form>
        </div>
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
  <!-- 5SeasonLife App -->
  <script src="../scriptFiles/adminlte.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>
  <!-- ------------------------ /Scripts Link ------------------------ -->
  <script type="text/javascript">
  // 內建 Bootstrap 5 原生表單驗證
  function validateBS5Form($form) {
    if (!$form || $form.length === 0) return true;
    const formEl = $form.get(0);
    const isValid = formEl.checkValidity();
    formEl.classList.add('was-validated');
    return isValid;
  }

  // 安全提示呼叫（兼容 showBS5Toast 與舊版 toastr）
  function sendNotify(type, msg) {
    if (window.parent && typeof window.parent.showBS5Toast === 'function') {
      window.parent.showBS5Toast(type, msg);
    } else if (window.parent && window.parent.toastr) {
      window.parent.toastr[type](msg);
    } else {
      alert(msg);
    }
  }

  ////////////////////////////////////////////////////////////////
  // ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
  ////////////////////////////////////////////////////////////////
  $(document).ready(function() {
    // 初始化 Bootstrap 5 Tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    //* iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      if (window.parent && window.parent.$) {
        let timerElm = window.parent.$('#timer_on_exhausted');
        if( timerElm.length ){
          timerElm.backward_timer('reset');
        }
      }
    });

    //------------------ QPay & 發票 API 測試/運行環境同步切換 ---------------------------
    $("#qpay_test, #inv_test").on('change', function(){
      var isChecked = $(this).is(":checked");
      var trigerOn = ($(this).prop("id") == 'qpay_test') ? $("#inv_test") : $("#qpay_test");
      trigerOn.prop('checked', isChecked);
    });

    //---------------- ZOOM API Check -------------------------
    $("#btnZoomPing").on('click', function(){
      sendNotify('info', 'Zoom API 測試功能尚未完成，請稍後再使用！');
    });

    //-------- 加載完成後更改外框主頁中樹狀目錄開合狀態 --------//
    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
      window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  });

  ////////////////////////////////////////////////////////////////     
  // ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
  ////////////////////////////////////////////////////////////////
  function doReDirect(){
    window.history.back();
  }

  // 當「儲存」按鈕按下時的動作
  function doButSave(){
    var frm = $('#form_sysinfo');
    if(!validateBS5Form(frm)){
      sendNotify('warning', '請檢查標示紅框的必填或格式錯誤欄位！');
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
    return false;
  }

  // --------------------------- AJAX POST --------------------- //
  function ajaxPOST(frm){
    var $frm = frm || $('#form_sysinfo');
    var url = $frm.prop('action');
    var typE = $frm.prop('method');
    var accE = $frm.data('func');
    var datA = $frm.serializeArray();
    
    if(!$("#qpay_test").is(':checked')){
      datA.push({name: 'qpay_test', value: '0'});
    }
    if(!$("#inv_test").is(':checked')){
      datA.push({name: 'inv_test', value: '0'});
    }

    $.ajax({
      type: typE,
      url: url,
      data: {
        'arrDatA': datA, 
        'formAcce': accE,
        'token': getTokenVal()
      },
      dataType: 'json',
      success: function (data) {
        $frm.removeClass('was-validated');
        if (typeof doSerialize === 'function') {
          doSerialize();
        } else if (typeof CHGform !== 'undefined') {
          ORGForm = CHGform.serialize();
          if (window.parent && window.parent.$) {
            window.parent.$('#topBut_save').removeClass('active');
          }
        }
        sendNotify(data['msgType'] || 'success', data['msgStr'] || '系統參數已更新！');
      },
      error: function (data) {
        console.log(data);
        sendNotify('error', '系統讀寫錯誤，請聯絡管理人員！！');
      }
    });
  }
  </script>
</body>
</html>
