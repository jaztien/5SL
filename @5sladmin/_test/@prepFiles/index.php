<?php
//------------------------------------------------
session_start();//測試用
$_SESSION['SESS_AD_ID'] = 1;//測試用
$_SESSION['SESS_AD_AUTH'] = 9;//測試用
//------------------------------------------------
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//------------------------------------------------
define('PAGE_MARK','');//測試用
define('PAGE_TOPAUTH', true);//測試用
//------------------------------------------------
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>title</title>
    <!-- Theme style -->
    <link rel="stylesheet" href="../cssFiles/5sladmin.light.css<?php echo '?t='.time(); ?>">
</head>
<body class="hold-transition login-page">
    <div class="wrapper">
        <div class="content-wrapper">
            <!-- 頁頭導覽條 -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="login-box">
                        <div id="testButtons" class="row col-md-12" style="margin-top: 20px;">
                            <div>以下為測試區域，非測試人員勿入！！</div>
                            <a id="CSVtoDBbc" href="BC_DBtoCSV.php" target="_blank" class="btn btn-primary btn-block">提取資料庫紀錄生成CSV檔案</a>
                            <a id="DBtoCSVbc" href="BC_doUpdateInfo.php" target="_blank" class="btn btn-primary btn-block">以預設ID截取課程商品詳細資料_591</a>
                            <a id="toPHPtester" href="_test/test.php" target="_blank" class="btn btn-info btn-block">PHP/HTML Tester</a>
                            <a id="toCHATtester" href="chat_in.html" target="_blank" class="btn btn-info btn-block">CHAT Tester</a>
                            <a id="toColorChart" href="test_bcNormalizer.php" target="_blank" class="btn btn-danger btn-block">建案名稱規則化測試</a>
                            <a id="toadminLTE3" href="test_addNormalizer.php" target="_blank" class="btn btn-danger btn-block">住址規則化測試</a>
                        </div>

                    </div>
                </div>

            </section>
            <!-- /頁頭導覽條 -->
        </div>
    </div>
    

    <script src="../pluginFiles/jquery/jquery.min.js"></script>
    <script src="../scriptFiles/page.basic.js"></script>
    <!-- ------------------------ /Scripts Link ------------------------ -->
    <script type="text/javascript">
    $(function() {





    });
    </script>  
</body>
</html>