<?php 
$current_page = 'login'; // 💡 宣告身分為登入頁
// 引入全局配置與頭部 (包含防快取機制與外掛)
include_once '5sl_include_header.php'; 
?>

<!-- 💡 客製化美化樣式區塊，可移至 5seasonlife.css 中或保留在此 -->
<style>
    body {
        background: #f4f7f6; /* 清爽柔和的背景底色 */
    }
    .login-wrapper {
        min-height: calc(100vh - 80px);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-container {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        width: 100%;
        max-width: 900px; /* 電腦版改為高質感寬幅雙欄 */
    }
    /* 左側電影感品牌側欄 */
    .login-brand-sidebar {
        background: linear-gradient(rgba(33, 37, 41, 0.75), rgba(33, 37, 41, 0.85)), 
                    url('https://unsplash.com') no-repeat center center;
        background-size: cover;
        color: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 3rem;
    }
    /* 輸入框 Focus 特效與外觀優化 */
    .form-control:focus {
        border-color: #212529;
        box-shadow: 0 0 0 0.25rem rgba(33, 37, 41, 0.1);
    }
    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
        color: #6c757d;
    }
    .input-group .form-control {
        border-left: none;
    }
    .input-group:focus-within .input-group-text {
        border-color: #212529;
    }
    /* 密碼眼睛與社群按鈕 Hover 微幅放大位移回饋 */
    .btn-interactive {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .btn-interactive:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
    }
    .password-toggle {
        cursor: pointer;
        transition: color 0.2s;
    }
    .password-toggle:hover {
        color: #212529 !important;
    }
</style>

<div class="container login-wrapper my-5">
    <div class="login-container">
        <div class="row g-0">
            
            <!-- 💡 左側欄：品牌情境空間 (電腦版顯示，手機平板 d-none 自動隱藏) -->
            <div class="col-lg-5 d-none d-lg-flex login-brand-sidebar">
                <div class="mb-4">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3">5th Season Life</span>
                    <h2 class="display-6 fw-bold lh-sm">探索生活<br>解鎖全新體驗</h2>
                </div>
                <p class="text-white-50 small lh-lg">歡迎登入會員中心。在這裡，我們共同經營高效、優雅且充滿質感的商用系統架構，開啟您每日的智慧管理之旅。</p>
            </div>

            <!-- 💡 右側欄：精緻登入表單 -->
            <div class="col-lg-7 p-4 p-md-5 bg-white">
                <div class="mb-4">
                    <h3 class="fw-bold text-dark mb-1">會員登入</h3>
                    <p class="text-muted small mb-0">歡迎回到 Fifth Season Life</p>
                </div>
                
                <!-- 登入表單 -->
                <form id="loginForm" novalidate>
                    <!-- 電子信箱 -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">電子信箱 (Email)</label>
                        <div class="input-group">
                            <!-- 💡 換成平面簡約圖標 -->
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control py-2.5" id="loginEmail" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <!-- 密碼 -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold small text-muted mb-0">密碼</label>
                            <a href="#" class="text-primary small text-decoration-none">忘記密碼？</a>
                        </div>
                        <div class="input-group">
                            <!-- 💡 換成平面簡約圖標 -->
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control py-2.5" id="loginPassword" placeholder="請輸入密碼" required>
                            <!-- 💡 整合實用功能：明隱切換眼球 (平面化) -->
                            <span class="input-group-text password-toggle text-muted" id="togglePasswordBtn"><i class="bi bi-eye"></i></span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-dark w-100 py-2.5 fw-bold mb-3 shadow-sm btn-interactive">
                        <i class="bi bi-shield-lock me-2"></i>安全登入系統
                    </button>
                </form>

                <!-- 第三方快速登入 -->
                <div class="position-relative text-center my-4 py-2">
                    <hr class="text-muted opacity-25">
                    <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small" style="font-size: 0.8rem;">或透過社群快速登入</span>
                </div>
                
                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" class="btn btn-light w-100 btn-sm py-2.5 d-flex align-items-center justify-content-center fw-bold text-nowrap border btn-interactive" id="btnGoogleLogin">
                            <!-- 💡 換成平面簡約圖標 -->
                            <i class="bi bi-google me-2 text-danger"></i> Google 登入
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-success w-100 btn-sm py-2.5 d-flex align-items-center justify-content-center fw-bold text-nowrap border-0 btn-interactive" style="background-color: #06C755;" id="btnLineLogin">
                            <!-- 💡 LINE 沒有原生官方圖標時，改用高質感平面對話泡泡符號 -->
                            <i class="bi bi-chat-fill me-2"></i> LINE 登入
                        </button>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <span class="text-muted small">還沒有帳號嗎？</span>
                    <a href="5sl_signup.php" class="text-primary fw-bold small text-decoration-none ms-1">立即免費註冊</a>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ==========================================================================
    POPUP 通知燈箱 (Modal) - 登入狀態異步反饋專用 (外觀優化)
    ========================================================================== -->
<div class="modal fade" id="authAlertModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-body p-4 text-center">
                <div id="modalAlertIcon" class="display-5 mb-2"></div>
                <h5 class="fw-bold mb-2" id="modalAlertTitle">提示</h5>
                <p class="text-muted small mb-4" id="modalAlertMessage"></p>
                <button type="button" class="btn btn-dark btn-sm px-4 py-2 w-100 fw-bold shadow-none" style="border-radius: 8px;" data-bs-dismiss="modal">確 認</button>
            </div>
        </div>
    </div>
</div>

<?php 
// 引入頁尾與腳本載入
include_once '5sl_include_footer.php'; 
?>

<!-- ==========================================================================
    100% 閉環封裝的 jQuery AJAX 異步控制與前端網效機制
    ========================================================================== -->
<script>

$(function() {
    // 初始化 Bootstrap Modal 實例
    const authModal = new bootstrap.Modal(document.getElementById('authAlertModal'));
    
    function showPopup(icon, title, message) {
        $('#modalAlertIcon').text(icon);
        $('#modalAlertTitle').text(title);
        $('#modalAlertMessage').text(message);
        authModal.show();
    }

    // 前端特效加分功能：密碼看得到/看不到 切換
    $('#togglePasswordBtn').on('click', function() {
        const passwordInput = document.getElementById('loginPassword');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            $(this).text('🙈');
        } else {
            passwordInput.type = 'password';
            $(this).text('👁️');
        }
    });

    // 監聽登入表單送出
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        const loginAccount = $('#loginEmail').val().trim();
        const loginPass = $('#loginPassword').val();
        const $submitBtn = $(this).find('button[type="submit"]');

        if(!loginAccount || !loginPass) {
            showPopup('⚠️', '輸入未完整', '請填寫正確的電子信箱與密碼！');
            return;
        }
        //alert('登錄中，請稍候...');

        // 發送異步請求
        $.ajax({
            url: './funcFiles/5sl_act_member.php',
            type: 'POST',
            data: {
                action: 'act_member_login',
                loginAccount: loginAccount,
                loginPass: loginPass,
                isLine: '',
                isGoogle: '',
                lineSUB: '',
                googleSUB: ''
            },
            dataType: 'json',
            beforeSend: function() {
                // 💡 視覺優化：發送請求時將按鈕文字改為處理中，並暫時禁用，防止重複點擊
                $submitBtn.attr('disabled', true).text('驗證中，請稍候...');
            },
            success: function(response) {
                    console.log(response);
                // 💡 實體後端對接與前端聯動行為
                if(response.success) {
                    // 1. 【成功動作】：彈出成功提示
                    showPopup('✅', '登入成功', response.message || '歡迎回來！正在為您導向會員訊息中心...');
                    
                    // 2. 【成功動作】：當使用者點選彈出視窗的「確認」鈕，或是 1.5 秒後，自動重新導向到會員訊息中心
                    $('#authAlertModal').on('hidden.bs.modal', function () {
                        window.location.href = '5sl_myConsole.php';
                    });
                    setTimeout(function() {
                        window.location.href = '5sl_myConsole.php';
                    }, 1500);
                    
                } else {
                    // 3. 【失敗動作】：還原按鈕狀態
                    $submitBtn.attr('disabled', false).text('安全登入系統');
                    
                    // 4. 【失敗動作】：彈出視窗警語，提示使用者具體失敗原因（如密碼錯誤）
                    showPopup('❌', '登入失敗', response.message || '您輸入的密碼錯誤，或該帳號不存在，請重新輸入。');
                }
            },
            error: function(r) {
                // 還原按鈕狀態
                //$submitBtn.attr('disabled', false).text('安全登入系統');
                
                // 5. 【目前測試分支】：由於您尚未建立 funcFiles/5sl_act_member.php，目前會走此分支
                // 為了方便您測試，我們在這裡「模擬回傳成功」的動作，讓您直接看到導航列與頭像改變的效果！
                showPopup('📡', 'AJAX 失敗', r.error);
                
                setTimeout(function() {
                    // 💡 注意：由於實際 MySQL 資料庫尚未建立，此處我們先導向首頁並帶入模擬參數，
                    // 日後經由 PHP 讀取 Session 後，導航列與大頭像就會 100% 完美自動變身。
                    window.location.href = 'index.php';
                }, 2000);
            }
        });
    });
});
</script>
