<?php
session_start();
if (isset($_SESSION["SESS_AD_ID"]) && !empty($_SESSION["SESS_AD_ID"])) {
    define('DOC_PATH_LAYER', './');
    require_once('./includeFiles/db.inc.php');
    $ilog = DB->insSysLogs(51, $_SESSION["SESS_AD_ID"], 2061, '["'.$_SESSION["SESS_AD_ID"].'"]');//寫入操作記錄
}
$SESSID = 'PHPSESSID='.session_id();
session_destroy();
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
$PAGE_MARK = "PG_LOGIN";//登入頁面
$PAGE_ITEM = "ITEM_LOGIN";
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
    <title>Fifth Season Life 人生第五季 | Admin Login Page</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="pluginFiles/fontawesome-free/css/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="cssFiles/5sladmin.light.css<?php echo "?t=".time(); ?>">
    <style>
    /*
                .btn-primary{color:#fff;background-color:#133b61;border-color:rgba(99,99,99,1.00);box-shadow:none}
                .btn-primary:hover{color:#fff;background-color:#1A5388;border-color:#236FB7}
                .btn-primary.focus,.btn-primary:focus{color:#fff;background-color:#1A5388;border-color:#236FB7;box-shadow:0 0 0 0 rgba(38,143,255,.5)}    
                .btn-primary.disabled, .btn-primary:disabled {color: #fff;background-color: #3F8ED9;border-color: #3F8ED9;}
                .btn-primary:not(:disabled):not(.disabled):active, .btn-primary:not(:disabled):not(.disabled).active,
                .show > .btn-primary.dropdown-toggle {color: #fff;background-color: #236FB7;border-color: #005cbf;}
                .btn-primary:not(:disabled):not(.disabled):active:focus, .btn-primary:not(:disabled):not(.disabled).active:focus,
                .show > .btn-primary.dropdown-toggle:focus {box-shadow: 0 0 0 0 rgba(38, 143, 255, 0.5);}
                    */
    </style>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <img src="imageFiles/5slLOGO_sB&W.png" alt="5SeasonLife Logo" class="brand-image animation__wobble" style="vertical-align:baseline;margin-bottom:-26px;" height="80" width="80">
            <!-- <div class="brand-text font-weight-bold" style="margin-top: -10px;margin-bottom: -8px;">Fifth Season Life<span class="brand-text font-weight-light"> ADMIN</span></div> -->
            <div><img src="imageFiles/5slADMINlogo_text1G.png?t=3" style="width:100%;"></div>
        </div>
        <!-- /.login-logo -->
        <hr>
        <div style="width:360px;margin-left:30px;">
            <div>
                <p class="login-box-msg" style="font-size: 75%;opacity: .6">帳戶的使用受到法律的保護及約束，請使用自己合法的帳戶</p>
                <form id="loginForm" action="./loginaccess.php" method="post">
                    <div class="input-group mb-3">
                        <input id="loginUser" name="loginUser" type="text" class="form-control" placeholder="Username">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input id="loginPass" name="loginPass" type="password" class="form-control" placeholder="Password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <hr style="margin-top: 20px;">
                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="rememberme">
                                <label for="rememberme" class="brand-text font-weight-light">
                                    Remember Me!
                                </label>
                            </div>
                        </div>
                        <!-- /.col -->

                        <div class="col-4">
                            <button id="signinBut" type="submit" class="btn btn-primary btn-block">Sign In</button>
                            <div class="brand-text font-weight-light text-right" style="cursor:pointer;font-size:75%;" onclick="alert('管理操作守則還沒有寫好！小心操作別搗亂！！');">管理操作守則(?)
                                <input type="hidden" id="token" name="token" value="">
                            </div>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>
                <div id="testButtons" class="row col-md-12" style="margin-top: 20px;display:none">
                    <div>以下為測試區域，非測試人員勿入！！</div>
                    <a id="toAPItest" href="_test/api.html" target="_blank" class="btn btn-info btn-block">API Tester</a>
                    <a id="toPHPtester" href="_test/test.php" target="_blank" class="btn btn-info btn-block">PHP/HTML Tester</a>
                    <a id="toCHATtester" href="chat_in.html" target="_blank" class="btn btn-info btn-block">CHAT Tester</a>
                    <a id="toColorChart" href="_test/color.html" target="_blank" class="btn btn-info btn-block">Color Chart</a>
                    <a id="toadminLTE3" href="_test/adminLTE3/index.html" target="_blank" class="btn btn-info btn-block">adminLTE3</a>
                </div>

                <!-- /.social-auth-links -->

                <p class="mb-1">
                    <!-- <a href="forgot-password.html">我忘記了密碼</a>-->
                </p>
            </div>
            <!-- /.login-card-body -->
        </div>
    </div>
    <!-- /.login-box -->
    <!-- jQuery -->
    <script src="pluginFiles/jquery/jquery.min.js"></script>
    <script src="pluginFiles/jquery-md5/jquery.md5.js"></script>
    <!-- Bootstrap 4 -->
    <script src="pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- jquery-validation -->
    <script src="pluginFiles/jquery-validation/jquery.validate.min.js"></script>
    <script src="pluginFiles/jquery-validation/additional-methods.min.js"></script>
    <!-- AdminLTE App -->
    <script src="scriptFiles/5sladmin.min.js"></script>
    <script>
    ////////////////////////////////////////////////////////////////
    // ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
    // ---------------------------------------------------------- //
    ////////////////////////////////////////////////////////////////
    $(document).ready(function() {
        // 💡 登入頁面 DOM 載入完成後，立刻徹底清空當前分頁的所有操作快取
        sessionStorage.clear();
        console.log("【安全防線】jQuery Ready 觸發：前端 sessionStorage 已完全清洗乾淨！");
        ////////////////////////////////////////////////////////////////
        const rmCheck = $('#rememberme'),
            userInput = $('#loginUser');

        if (localStorage.checkbox && localStorage.checkbox !== "") {
            rmCheck.prop('checked', true);
            userInput.val(localStorage.username);
        } else {
            rmCheck.prop('checked', false);
            userInput.val('');
        }
        $('#loginForm').validate({
            submitHandler: function(form) {
                if (rmCheck.prop('checked') && userInput.val() !== "") {
                    localStorage.username = userInput.val();
                    localStorage.checkbox = rmCheck.val();
                } else {
                    localStorage.username = "";
                    localStorage.checkbox = "";
                }
                var uai = navigator.userAgent.toLowerCase();
                //var muai = $.md5(uai + document.cookie);
                var oNaviString = uai + 'PHPSESSID=' + getCookieVal('PHPSESSID');
                var Omuai = $.md5(oNaviString);
                var saltH = Omuai.substring(0, 12);
                var saltT = Omuai.substr(Omuai.length - 12, 12);
                var muai = $.md5(saltH + oNaviString + saltT);
                $('#token').val(muai);
                form.submit();
            },
            rules: {
                loginUser: {
                    required: true,
                    minlength: 6
                },
                loginPass: {
                    required: true,
                    minlength: 8
                },
            },
            messages: {
                loginUser: {
                    required: "請輸入使用者名稱",
                    minlength: "使用者名稱至少需要 6 個字元"
                },
                loginPass: {
                    required: "請輸入登入密碼",
                    minlength: "登入密碼必須包含至少 8 個字元"
                }
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.input-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });

        function getCookieVal(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
        }


        $('.login-logo div img').dblclick(function() {
            if ($('#testButtons').is(':visible')) { 
                $('#testButtons').hide(); 
            } else {
                if (prompt("系統測試區域，請輸入安全驗證碼") == "1111") {
                    $('#testButtons').show();
                }
            }
        });

    });
    </script>
</body>

</html>