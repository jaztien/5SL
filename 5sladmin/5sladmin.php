<?php
session_start();
if (isset($_SESSION["SESS_AD_ID"]) && !empty($_SESSION["SESS_AD_ID"])) {
    define('DOC_PATH_LAYER', './');
    require_once('./includeFiles/db.inc.php');
    $ilog = DB->insSysLogs(51, $_SESSION["SESS_AD_ID"], 2061, '["'.$_SESSION["SESS_AD_ID"].'"]'); // 寫入操作記錄
}
$SESSID = 'PHPSESSID='.session_id();
session_destroy();
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
// 定義本頁面的代碼
$PAGE_MARK = "PG_LOGIN"; // 登入頁面
$PAGE_ITEM = "ITEM_LOGIN";
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
    <title>Fifth Season Life 人生第五季 | Admin Login Page</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="./pluginFiles/googleapis/fonts.googleapis.css">
    <!-- Bootstrap 5 & Bootstrap Icons -->
    <link rel="stylesheet" href="./pluginFiles/bootstrap5/bootstrap.min.css">
    <link rel="stylesheet" href="./pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
    <!-- Theme style (AdminLTE 4) -->
    <link rel="stylesheet" href="./cssFiles/adminlte.css">
    <style>
        /* 補回 Logo 持續搖晃動畫 */
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

<body class="login-page bg-body-secondary">
    <div class="login-box" style="width: 380px; max-width: 92%;">
        <!-- 雙層 Logo 嚴格置中對齊 -->
        <div class="login-logo d-flex flex-column align-items-center justify-content-center mb-3">
            <img src="imageFiles/5slLOGO_sB&W.png" alt="5SeasonLife Logo" class="brand-image animation__wobble d-block mx-auto" style="margin-bottom: -18px; position: relative; z-index: 2;" height="80" width="80">
            <div class="w-100 text-center">
                <img src="imageFiles/5slADMINlogo_text1G.png?t=3" class="d-block w-100 h-auto" alt="Fifth Season Life Admin">
            </div>
        </div>
        <!-- /.login-logo -->

        <div class="card shadow-sm border-0">
            <div class="card-body login-card-body p-4">
                <p class="login-box-msg text-muted small text-center mb-3">帳戶的使用受到法律的保護及約束，請使用自己合法的帳戶</p>

                <!-- 使用 Bootstrap 5 原生驗證 (.needs-validation + novalidate) -->
                <form id="loginForm" action="./loginaccess.php" method="post" class="needs-validation" novalidate>
                    <div class="input-group has-validation mb-3">
                        <input id="loginUser" name="loginUser" type="text" class="form-control" placeholder="Username" minlength="6" required>
                        <div class="input-group-text">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="invalid-feedback">請輸入使用者名稱（至少 6 個字元）</div>
                    </div>

                    <div class="input-group has-validation mb-3">
                        <input id="loginPass" name="loginPass" type="password" class="form-control" placeholder="Password" minlength="8" required>
                        <div class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </div>
                        <div class="invalid-feedback">請輸入登入密碼（至少 8 個字元）</div>
                    </div>

                    <hr class="my-3">

                    <div class="row align-items-center">
                        <div class="col-7">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberme" value="1">
                                <label class="form-check-label fw-light" for="rememberme">
                                    Remember Me!
                                </label>
                            </div>
                        </div>
                        <!-- /.col -->

                        <div class="col-5 d-grid">
                            <button id="signinBut" type="submit" class="btn btn-primary">Sign In</button>
                        </div>
                        <!-- /.col -->
                    </div>

                    <div class="text-end mt-2">
                        <span class="fw-light text-secondary" style="cursor:pointer; font-size:0.75rem;" onclick="alert('管理操作守則還沒有寫好！小心操作別搗亂！！');">管理操作守則(?)</span>
                        <input type="hidden" id="token" name="token" value="">
                    </div>
                </form>

                <div id="testButtons" class="mt-3 pt-3 border-top" style="display:none;">
                    <div class="small text-danger fw-bold mb-2">以下為測試區域，非測試人員勿入！！</div>
                    <div class="d-grid gap-2">
                        <a id="toAPItest" href="_test/api.html" target="_blank" class="btn btn-info btn-sm text-white">API Tester</a>
                        <a id="toPHPtester" href="_test/test.php" target="_blank" class="btn btn-info btn-sm text-white">PHP/HTML Tester</a>
                        <a id="toCHATtester" href="chat_in.html" target="_blank" class="btn btn-info btn-sm text-white">CHAT Tester</a>
                        <a id="toColorChart" href="_test/color.html" target="_blank" class="btn btn-info btn-sm text-white">Color Chart</a>
                        <a id="toadminLTE3" href="_test/adminLTE3/index.html" target="_blank" class="btn btn-info btn-sm text-white">adminLTE3</a>
                    </div>
                </div>
            </div>
            <!-- /.login-card-body -->
        </div>
    </div>
    <!-- /.login-box -->

    <!-- jQuery -->
    <script src="./pluginFiles/jquery/jquery-4.0.0.min.js"></script>
    <!-- Bootstrap 5 -->
    <script src="./pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
    <!-- 5SeasonLife App -->
    <script src="./scriptFiles/adminlte.js"></script>
    <!-- 引入 page.basic.js 以使用內建原生 calcMD5() 與 getCookieVal() -->
    <script src="./scriptFiles/page.basic.js"></script>
    <script>
    $(document).ready(function() {
        // 💡 登入頁面 DOM 載入完成後，立刻徹底清空當前分頁的所有操作快取
        sessionStorage.clear();
        console.log("【安全防線】jQuery Ready 觸發：前端 sessionStorage 已完全清洗乾淨！");

        const rmCheck = $('#rememberme'),
              userInput = $('#loginUser');

        if (localStorage.checkbox && localStorage.checkbox !== "") {
            rmCheck.prop('checked', true);
            userInput.val(localStorage.username);
        } else {
            rmCheck.prop('checked', false);
            userInput.val('');
        }

        // [變更] 改用 Bootstrap 5 原生表單驗證 + page.basic.js 內建的 calcMD5()
        $('#loginForm').on('submit', function(e) {
            const formEl = this;
            if (!formEl.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                formEl.classList.add('was-validated');
                return false;
            }
            formEl.classList.add('was-validated');

            if (rmCheck.prop('checked') && userInput.val() !== "") {
                localStorage.username = userInput.val();
                localStorage.checkbox = rmCheck.val();
            } else {
                localStorage.username = "";
                localStorage.checkbox = "";
            }

            var uai = navigator.userAgent.toLowerCase();
            var oNaviString = uai + 'PHPSESSID=' + getCookieVal('PHPSESSID');
            var Omuai = calcMD5(oNaviString);
            var saltH = Omuai.substring(0, 12);
            var saltT = Omuai.substr(Omuai.length - 12, 12);
            var muai = calcMD5(saltH + oNaviString + saltT);
            $('#token').val(muai);
        });

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
