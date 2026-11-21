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
//DB->closeConnection();
$i = 0; 
$cr = array();
while($i<count($sr)){ 
  $ari = array($sr[$i]['config_code'] => ($sr[$i]['config_crypt']==1) ? $sr[$i]['config_value'] : $sr[$i]['config_value']);
  $cr = array_merge($cr, $ari);
  $i++;
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
	
	<title> Fifth Season Life 人生第五季 | 系統參數設定 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
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
              <h4 class="m-0">系統參數設定</h4>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">系統設定管理</li>
                <li class="breadcrumb-item active">系統參數設定</li>
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </section>
      <!-- /頁頭導覽條 -->
      <!-- 主要內容區域 ------------------------------------- -->
      <section class="content">
        <div class="container-fluid tab-pane active" id="sysinfo">
          <form id="form_sysinfo" name="form_sysinfo" method="post" action="../funcFiles/sys_act_func.php" data-formmode="infoupdate" data-func="func_sys_config">
            <div class="row">
              <div class="col-md-6">
                <!-- Widget: user widget style 1 -->
                <div class="card card-widget widget-user">
                  <!-- Add the bg color to the header using any of the bg-* classes -->
                  <div class="widget-user-header bg-info">
                    <h3 class="widget-user-username">Fifth Season Life</h3>
                    <h6 class="widget-user-desc">人生第五季生命藝術工作室</h6>
                  </div>
                  <div class="widget-user-image">
                    <img class="img-circle elevation-2" src="../imageFiles/logoSRGB.jpg" alt="5SL Logo">
                  </div>
                  <div class="card-body">
                    <div class="form-group">
                      <label for="comp_fullname">公司全名</label>
                      <input type="text" class="form-control" id="comp_fullname" name="comp_fullname" value="<?php echo $cr['comp_fullname']??''; ?>" placeholder="公司全名" data-required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="form-group">
                      <label for="comp_enname">公司英文全名</label>
                      <input type="text" class="form-control" id="comp_enname" name="comp_enname" value="<?php echo $cr['comp_enname']??''; ?>" placeholder="公司英文全名" data-required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label for="comp_shortname">公司簡稱</label>
                          <input type="text" class="form-control" id="comp_shortname" name="comp_shortname" value="<?php echo $cr['comp_shortname']??''; ?>" placeholder="公司簡稱" data-required<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label for="comp_enshort">公司英文簡稱</label>
                          <input type="text" class="form-control" id="comp_enshort" name="comp_enshort" value="<?php echo $cr['comp_enshort']??''; ?>" placeholder="公司英文簡稱" data-required<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label for="comp_abbr">公司縮寫</label>
                          <input type="text" class="form-control" id="comp_abbr" name="comp_abbr" value="<?php echo $cr['comp_abbr']??''; ?>" placeholder="公司縮寫" data-required<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label for="comp_enabbr">公司英文縮寫</label>
                          <input type="text" class="form-control" id="comp_enabbr" name="comp_enabbr" value="<?php echo $cr['comp_enabbr']??''; ?>" placeholder="公司英文縮寫" data-required<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="comp_address">公司住址</label>
                      <input type="text" class="form-control" id="comp_address" name="comp_address" value="<?php echo $cr['comp_address']??''; ?>" placeholder="公司住址" data-required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="form-group">
                      <label for="comp_email">電子郵件</label>
                      <input type="email" class="form-control" id="comp_email" name="comp_email" value="<?php echo $cr['comp_email']??''; ?>" placeholder="電子郵件" data-required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="form-group">
                      <label for="comp_tel">公司電話</label>
                      <input type="text" class="form-control" id="comp_tel" name="comp_tel" value="<?php echo $cr['comp_tel']??''; ?>" placeholder="公司電話" data-required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="form-group">
                      <label for="comp_tollfree">免付費電話</label>
                      <input type="text" class="form-control" id="comp_tollfree" name="comp_tollfree" value="<?php echo $cr['comp_tollfree']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                    <div class="form-group">
                      <label for="comp_fax">傳真號碼</label>
                      <input type="text" class="form-control" id="comp_fax" name="comp_fax" value="<?php echo $cr['comp_fax']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                  </div>
                </div>
                <!-- /.widget-user -->
                <div class="card card-danger">
                  <div class="card-header">
                    <h3 class="card-title">郵件</h3>
                  </div>
                  <div class="card-body">
                    <!-- Form Elements -->
                    <div class="row">
                      <div class="col-sm-12">
                        <div class="form-group">
                          <label>系統通知信箱</label>
                          <input type="text" class="form-control" id="mail_notifyadd" name="mail_notifyadd" value="<?php echo $cr['mail_notifyadd']??''; ?>" placeholder="系統通知信箱" data-required<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-12">
                        <div class="form-group">
                          <label>通知對象</label>
                          <input type="text" class="form-control" id="mail_notifytItle" name="mail_notifytItle" value="<?php echo $cr['mail_notifytItle']??''; ?>" placeholder="通知對象" data-required<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>副本信箱1</label>
                          <input type="text" class="form-control" id="mail_cc1add" name="mail_cc1add" value="<?php echo $cr['mail_cc1add']??''; ?>"<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>副本信箱1抬頭</label>
                          <input type="text" class="form-control" id="mail_cc1title" name="mail_cc1title" value="<?php echo $cr['mail_cc1title']??''; ?>"<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>副本信箱2</label>
                          <input type="text" class="form-control" id="mail_cc2add" name="mail_cc2add" value="<?php echo $cr['mail_cc2add']??''; ?>"<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>副本信箱2抬頭</label>
                          <input type="text" class="form-control" id="mail_cc2title" name="mail_cc2title" value="<?php echo $cr['mail_cc2title']??''; ?>"<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>密件副本信箱1</label>
                          <input type="text" class="form-control" id="mail_bcc1add" name="mail_bcc1add" value="<?php echo $cr['mail_bcc1add']??''; ?>"<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>密件副本信箱1抬頭</label>
                          <input type="text" class="form-control" id="mail_bcc1title" name="mail_bcc1title" value="<?php echo $cr['mail_bcc1title']??''; ?>"<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>密件副本信箱2</label>
                          <input type="text" class="form-control" id="mail_bcc2add" name="mail_bcc2add" value="<?php echo $cr['mail_bcc2add']??''; ?>"<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>密件副本信箱2抬頭</label>
                          <input type="text" class="form-control" id="mail_bcc2title" name="mail_bcc2title" value="<?php echo $cr['mail_bcc2title']??''; ?>"<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                    </div>
                    <hr class="grouphr">
                    <div class="groupfield">SMTP 伺服器</div>
                    <div class="form-group">
                      <label>寄件信箱</label>
                      <input type="text" class="form-control" id="mail_fromadd" name="mail_fromadd" value="<?php echo $cr['mail_fromadd']??''; ?>" placeholder="寄件信箱" data-required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="form-group">
                      <label>寄件人抬頭</label>
                      <input type="text" class="form-control" id="mail_fromtitle" name="mail_fromtitle" value="<?php echo $cr['mail_fromtitle']??''; ?>" placeholder="寄件人抬頭" data-required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="form-group">
                      <label for="smtp_host">SMTP 伺服器</label>
                      <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo $cr['smtp_host']??''; ?>" placeholder="SMTP 伺服器" data-required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label for="smtp_accuont">SMTP 帳號</label>
                          <input type="text" class="form-control" id="smtp_accuont" name="smtp_accuont" value="<?php echo $cr['smtp_accuont']??''; ?>" placeholder="SMTP 帳號" data-required<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label for="smtp_pass">SMTP 密碼</label>
                          <input type="text" class="form-control" id="smtp_pass" name="smtp_pass" value="<?php echo $cr['smtp_pass']??''; ?>" placeholder="SMTP 密碼" data-required<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-4">
                        <div class="form-group">
                          <label>加密方式</label>
                          <select class="form-control" id="smtp_smode" name="smtp_smode"<?php echo NreadOnly; ?>>
                            <option value="1"<?php if(isset($cr['smtp_smode']) && $cr['smtp_smode']=="TLS") echo " selected"; ?>>TLS</option>
                            <option value="2"<?php if(isset($cr['smtp_smode']) && $cr['smtp_smode']=="SSL") echo " selected"; ?>>SSL</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-sm-4">
                        <div class="form-group">
                          <label>TLS 連接埠</label>
                          <input type="text" class="form-control" id="smtp_tlsport" name="smtp_tlsport" value="<?php echo $cr['smtp_tlsport']??''; ?>" placeholder="TLS 連接埠" data-required<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                      <div class="col-sm-4">
                        <div class="form-group">
                          <label>SSL 連接埠</label>
                          <input type="text" class="form-control" id="smtp_sslport" name="smtp_sslport" value="<?php echo $cr['smtp_sslport']??''; ?>" placeholder="SSL 連接埠" data-required<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                    </div>
                    <!-- /Form Elements -->
                  </div>
                </div>
                <div class="card bg-gradient-primary">
                  <div class="card-header">
                    <h3 class="card-title">Zoom API</h3>
                    <div class="card-tools">
                          <button type="button" id="btnZoomPing" class="btn btn-tool" style="margin-top:-4px;" title="測試 Zoom API 連線" data-toggle="tooltip" data-placement="top">
                            <i class="fas fa-2x fa-solid fa-plug-circle-check"></i>
                          </button>
                        </div>
                    </div>
                  <div class="card-body">
                    <!-- Form Elements -->
                    <div class="row">
                      <div class="col-sm-12">
                        <div class="form-group">
                          <label>帳戶ID(Account ID)</label>
                          <input type="text" class="form-control" id="zoom_accountid" name="zoom_accountid" value="<?php echo $cr['zoom_accountid']??''; ?>" placeholder="帳戶ID(Account ID)" data-required<?php echo NreadOnly; ?>>
                        </div>
                        <div class="form-group">
                          <label>用戶ID(Client ID)</label>
                          <input type="text" class="form-control" id="zoom_clientid" name="zoom_clientid" value="<?php echo $cr['zoom_clientid']??''; ?>" placeholder="用戶ID(Client ID)" data-required<?php echo NreadOnly; ?>>
                        </div>
                        <div class="form-group">
                          <label>用戶密鑰(Client Secret)</label>
                          <input type="text" class="form-control" id="zoom_clientsecret" name="zoom_clientsecret" value="<?php echo $cr['zoom_clientsecret']??''; ?>" placeholder="用戶密鑰(Client Secret)" data-required<?php echo NreadOnly; ?>>
                        </div>
                        <div class="form-group">
                          <label>安全憑證(Secret Token)</label>
                          <input type="text" class="form-control" id="zoom_secrettoken" name="zoom_secrettoken" value="<?php echo $cr['zoom_secrettoken']??''; ?>" placeholder="安全憑證(Secret Token)" data-required<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                    </div>
                  </div>  
                </div>

              </div>
              <div class="col-md-6">
                <div class="card card-warning">
                  <div class="card-header">
                    <h3 class="card-title">系統訊息</h3>
                  </div>
                  <div class="card-body">
                    <!-- Form Elements -->
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>主機停機狀態</label>
                          <select class="form-control" id="sys_serverstatus" name="sys_serverstatus"<?php echo NreadOnly; ?>>
                            <option value=""></option>
                            <option value="1"<?php if(isset($cr['sys_serverstatus']) && $cr['sys_serverstatus']==1) echo " selected"; ?>>正常運行中</option>
                            <option value="2"<?php if(isset($cr['sys_serverstatus']) && $cr['sys_serverstatus']==2) echo " selected"; ?>>停機中</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>停機簡述</label>
                          <input type="text" class="form-control" id="sys_stopbrief" name="sys_stopbrief" value="<?php echo $cr['sys_stopbrief']??''; ?>"<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                    </div>

                    <div class="form-group">
                      <label for="sys_stopdes">停機說明</label>
                      <textarea class="form-control" id="sys_stopdes" name="sys_stopdes" rows="3"<?php echo NreadOnly; ?>><?php echo $cr['sys_stopdes']??''; ?></textarea>
                    </div>
                    <div class="form-group">
                      <label for="sys_stopdirto">停機指向網址</label>
                      <input type="text" class="form-control" id="sys_stopdirto" name="sys_stopdirto" value="<?php echo $cr['sys_stopdirto']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                    <!-- /Form Elements -->
                  </div>
                </div>
                <!-- 後台功能參數設定 -->
                <div class="card card-info">
                  <div class="card-header">
                    <h3 class="card-title">後台功能參數設定</h3>
                  </div>
                  <div class="card-body">
                    <!-- Form Elements -->
                    <div class="form-group">
                      <label for="sys_version">系統版本</label>
                      <input type="text" class="form-control" id="sys_version" name="sys_version" value="<?php echo $cr['sys_version']??''; ?>">
                    </div>
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>主顯示語言</label>
                          <select class="form-control" id="sys_lang" name="sys_lang">
                            <option value="zh-tw" selected>繁體中文</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>閒置登出時限</label>
                          <input type="text" class="form-control" id="sys_idleout" name="sys_idleout" value="<?php echo $cr['sys_idleout']??''; ?>" placeholder="閒置登出時限" data-required>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="page_title">頁面抬頭</label>
                      <input type="text" class="form-control" id="page_title" name="page_title" value="<?php echo $cr['page_title']??''; ?>" placeholder="頁面抬頭" data-required>
                    </div>
                    <div class="form-group">
                      <label for="page_metades">頁面描述</label>
                      <textarea type="text" class="form-control" id="page_metades" name="page_metades" rows="3"><?php echo $cr['page_metades']??''; ?></textarea>
                    </div>
                  <!-- /Form Elements -->
                  </div>
                </div>
                <!-- /後台功能參數設定 -->


                <!-- 線上支付參數設定 -->
                <div class="card card-primary">
                  <div class="card-header">
                    <h3 class="card-title">線上支付參數設定</h3>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label class="d-inline">正式環境</label>
                          <div class="custom-control custom-switch d-inline<?php echo NdisAble == "disabled" ? "" : " custom-switch-off-success custom-switch-on-danger"; ?>">
                            <input type="checkbox" class="custom-control-input" id="qpay_test" name="qpay_test" value="1"<?php echo isset($cr['qpay_test']) && $cr['qpay_test']=="1" ? " checked" : ""; ?><?php echo NdisAble; ?>>
                            <label class="custom-control-label" for="qpay_test">測試環境</label>
                          </div>
                        </div>                    
                      </div>
                    </div>  
                    <hr>                  
                    <div class="form-group">
                      <label for="qpay_url">正式API網址</label>
                      <input type="text" class="form-control" id="qpay_url" name="qpay_url" value="<?php echo $cr['qpay_url']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                    <div class="form-group">
                      <label for="qpay_testurl">測試API網址</label>
                      <input type="text" class="form-control" id="qpay_testurl" name="qpay_testurl" value="<?php echo $cr['qpay_testurl']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                    <div class="form-group">
                      <label for="web_title">商店代號</label>
                      <input type="text" class="form-control" id="qpay_shopno" name="qpay_shopno" value="<?php echo $cr['qpay_shopno']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                    <div class="form-group">
                      <label for="web_title">授權認證金鑰</label>
                      <input type="text" class="form-control" id="qpay_authcode" name="qpay_authcode" value="<?php echo $cr['qpay_authcode']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>雜湊值A1</label>
                          <input type="text" class="form-control" id="qpay_hashA1" name="qpay_hashA1" value="<?php echo $cr['qpay_hashA1']??''; ?>"<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>雜湊值A2</label>
                          <input type="text" class="form-control" id="qpay_hashA2" name="qpay_hashA2" value="<?php echo $cr['qpay_hashA2']??''; ?>"<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>雜湊值B1</label>
                          <input type="text" class="form-control" id="qpay_hashB1" name="qpay_hashB1" value="<?php echo $cr['qpay_hashB1']??''; ?>"<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>雜湊值B2</label>
                          <input type="text" class="form-control" id="qpay_hashB2" name="qpay_hashB2" value="<?php echo $cr['qpay_hashB2']??''; ?>"<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="qpay_returnurl">回傳值接收器URL(即時)</label>
                      <input type="text" class="form-control" id="qpay_returnurl" name="qpay_returnurl" value="<?php echo $cr['qpay_returnurl']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                    <div class="form-group">
                      <label for="qpay_backendurl">回傳值接收器URL(排程)</label>
                      <input type="text" class="form-control" id="qpay_backendurl" name="qpay_backendurl" value="<?php echo $cr['qpay_backendurl']??''; ?>"<?php echo NreadOnly; ?>>
                    </div>
                  </div>
                </div>
                <!-- /線上支付參數設定 -->
                <!-- 發票開立參數設定 -->
                <div class="card card-olive">
                  <div class="card-header">
                    <h3 class="card-title">發票開立參數設定</h3>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label class="d-inline">正式環境</label>
                          <div class="custom-control custom-switch d-inline<?php echo NdisAble == "disabled" ? "" : " custom-switch-off-success custom-switch-on-danger"; ?>">
                            <input type="checkbox" class="custom-control-input" id="inv_test" name="inv_test" value="1"<?php echo isset($cr['inv_test']) && $cr['inv_test']=="1" ? " checked" : ""; ?><?php echo NdisAble; ?>>
                            <label class="custom-control-label" for="inv_test">測試環境</label>
                          </div>
                        </div>                    
                      </div>
                    </div>  
                    <hr>                  
                    <div class="form-group">
                      <label for="inv_url">正式API網址</label>
                      <input type="text" class="form-control" id="inv_url" name="inv_url" value="<?php echo $cr['inv_url']??''; ?>" placeholder="正式API網址" data-required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="form-group">
                      <label for="inv_testurl">測試API網址</label>
                      <input type="text" class="form-control" id="inv_testurl" name="inv_testurl" value="<?php echo $cr['inv_testurl']??''; ?>" placeholder="測試API網址" data-required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>營業人統一編號</label>
                          <input type="text" class="form-control" id="inv_taxid" name="inv_taxid" value="<?php echo $cr['inv_taxid']??''; ?>" placeholder="營業人統一編號" data-required<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>編碼鹽值</label>
                          <input type="text" class="form-control" id="inv_seed" name="inv_seed" value="<?php echo $cr['inv_seed']??''; ?>" placeholder="編碼鹽值" data-required<?php echo NreadOnly; ?>>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="inv_akey">啟動序號</label>
                      <input type="text" class="form-control" id="inv_akey" name="inv_akey" value="<?php echo $cr['inv_akey']??''; ?>" placeholder="啟動序號" data-required<?php echo NreadOnly; ?>>
                    </div>
                    <div class="form-group">
                      <label for="inv_auth">驗證編碼</label>
                      <input type="text" class="form-control" id="inv_auth" name="inv_auth" value="<?php echo $cr['inv_auth']??''; ?>" placeholder="驗證編碼" data-required readOnly>
                    </div>
                    <div class="form-group">
                      <label for="inv_token">用戶認證碼</label>
                      <div class="input-group">
                        <input type="text" class="form-control" id="inv_token" name="inv_token" value="<?PHP echo $cr['inv_token']??''; ?>" placeholder="用戶認證碼" data-required placeholder="用戶認證碼">
                        <div class="input-group-append">
                          <span id="getNewToken" name="getNewToken" class="input-group-text btn btn-success">重新取得認證碼</span>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="inv_tokendate">認證碼取得日期時間</label>
                      <input type="text" class="form-control" id="inv_tokendate" name="inv_tokendate" value="<?php echo $cr['inv_tokendate']??''; ?>" placeholder="認證碼取得日期時間" data-required readOnly>
                    </div>
                  </div>
                </div>
                <!-- /發票開立參數設定 -->

                <!-- 圖像參數設定 -->
                <div class="card card-primary">
                  <div class="card-header">
                    <h3 class="card-title">圖像參數設定</h3>
                  </div>
                    <div class="card-body">
                      <!-- Form Elements -->
                      <div class="row">
                        <div class="col-sm-6">
                          <div class="form-group">
                            <label>主顯示語言</label>
                            <input type="text" class="form-control" id="image_title" name="web_title" value="<?php echo $cr['image_title']??''; ?>">
                          </div>
                        </div>
                        <div class="col-sm-6">
                          <div class="form-group">
                            <label>XXXX</label>
                            <input type="text" class="form-control" id="web_title" name="web_title">
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-sm-6">
                          <div class="form-group">
                            <label for="md_photoW">課程商品圖像尺寸：寬</label>
                            <div class="input-group">
                              <input type="text" class="form-control" id="md_photoW" name="md_photoW">
                              <div class="input-group-append">
                                <span class="input-group-text">px</span>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-sm-6">
                          <div class="form-group">
                            <label for="md_photoH">：高</label>
                            <div class="input-group">
                              <input type="text" class="form-control" id="md_photoH" name="md_photoH">
                              <div class="input-group-append">
                                <span class="input-group-text">px</span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-sm-6">
                          <div class="form-group">
                            <label for="md_thumbMW">課程商品中縮圖尺寸：寬</label>
                            <div class="input-group">
                              <input type="text" class="form-control" id="md_thumbMW" name="md_thumbMW">
                              <div class="input-group-append">
                                <span class="input-group-text">px</span>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-sm-6">
                          <div class="form-group">
                            <label for="md_thumbMH">：高</label>
                            <div class="input-group">
                              <input type="text" class="form-control" id="md_thumbMH" name="md_thumbMH">
                              <div class="input-group-append">
                                <span class="input-group-text">px</span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-sm-6">
                          <div class="form-group">
                            <label for="md_thumbSW">課程商品小縮圖尺寸：寬</label>
                            <div class="input-group">
                              <input type="text" class="form-control" id="md_thumbSW" name="md_thumbSW">
                              <div class="input-group-append">
                                <span class="input-group-text">px</span>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-sm-6">
                          <div class="form-group">
                            <label for="md_thumbSH">：高</label>
                            <div class="input-group">
                              <input type="text" class="form-control" id="md_thumbSH" name="md_thumbSH">
                              <div class="input-group-append">
                                <span class="input-group-text">px</span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    <!-- /Form Elements -->
                    </div>
                </div>
                <!-- /圖像參數設定 -->

              </div>
            </div>
          </form>
        </div>
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
<!-- jquery-validation
<script src="../pluginFiles/jquery-validation/jquery.validate.min.js"></script>
<script src="../pluginFiles/jquery-validation/additional-methods.min.js"></script> -->
<!-- ezValidation -->
<script src="../pluginFiles/validator/validator.js<?php echo '?t='.time(); ?>"></script>
<!-- 5SL Admin App -->
<script src="../scriptFiles/5sladmin.js"></script>
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>
<!-- ------------------------ /Scripts Link ------------------------ -->
<script type="text/javascript">

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
    //------------------ QPay API 測試/運行環境切換 ---------------------------
    $("#qpay_test,#inv_test").change(function(e){
      var nowVal = $(this).is(":checked") ? 1 : 0;
      var nowOn = $(this).prop( "id" );
      var trigerOn = nowOn=='qpay_test' ? $("#inv_test") : $("#qpay_test");
      if(nowVal==1){
        trigerOn.prop('checked', true);
      } else {
        trigerOn.prop('checked', false);
      }
    });
    //---------------- ZOOM API Check -------------------------
    $("#btnZoomPing").on('click', function(e){
      alert(' Zoom API 測試功能尚未完成，請稍後再使用！');
    });

  });
////////////////////////////////////////////////////////////////     
// ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
// -------------- 可接收include檔案要求後自定義動作 ------------- //
////////////////////////////////////////////////////////////////
// 當「儲存」按鈕按下時的動作
function doButSave(){
  var frm = $('#form_sysinfo');
  var accE = frm.data('func');
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
  return false;
}
// --------------------------- AJAX POST --------------------- //
function ajaxPOST(frm){
  var frm = frm ? frm : $('#form_sysinfo');
  var url = frm.prop('action');
  var typE = frm.prop('method');
	var accE = frm.data('func');
  //var fiD = frm.prop('id');
  var datA = frm.serializeArray();
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
        'arrDatA':datA, 
        'formAcce':accE,
        'token': getTokenVal()
      },
      dataType: 'json',  
      async: false, 
      beforeSend:function(){//送出前的動作
      },
    success: function (data) {
        ORGForm = CHGform.serialize();
        window.parent.$('#topBut_save').removeClass('active');
        window.parent.toastr[data['msgType']](data['msgStr']);
      },
      error: function (data) {
        console.log(data);
        window.parent.toastr[data['msgType']](data['msgStr']);
      }
  });
}

</script>

</body>
</html>