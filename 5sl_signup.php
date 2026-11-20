<?php 
$current_page = 'signup'; // 💡 宣告身分為註冊頁
// 引入全局配置與頭部
include_once '5sl_include_header.php'; 
?>

<!-- 💡 客製化美化樣式區塊，可移至 5seasonlife.css 中或保留在此 -->
<style>
    body {
        background-image: url("imageFiles/mainBG-3.jpg");
        background-attachment: fixed;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    .signup-wrapper {
        min-height: calc(100vh - 80px);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .signup-container {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        width: 100%;
        max-width: 1000px; /* 註冊欄位較多，改用稍寬的雙欄幅佈局 */
    }
    /* 左側電影感品牌側欄 */
    .signup-brand-sidebar {
        background: linear-gradient(rgba(13, 110, 253, 0.75), rgba(33, 37, 41, 0.85)), 
                    url('imageFiles/test/<?php echo rand(1,18); ?>.jpg') no-repeat center center;
        background-size: cover;
        color: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 3.5rem;
    }
    /* 輸入框 Focus 特效與外觀優化 */
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
        color: #6c757d;
    }
    .input-group .form-control, .input-group .form-select {
        border-left: none;
    }
    .input-group:focus-within .input-group-text {
        border-color: #0d6efd;
    }
    /* 按鈕與社群連動 Hover 微幅放大位移回饋 */
    .btn-interactive {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .btn-interactive:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.2) !important;
    }
    .btn-social-interactive {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .btn-social-interactive:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08) !important;
    }
</style>

<div class="container signup-wrapper my-5">
    <div class="signup-container">
        <div class="row g-0">
            
            <!-- 💡 左側欄：品牌情境空間 (電腦版顯示，手機平板 d-none 自動隱藏) -->
            <div class="col-lg-5 d-none d-lg-flex signup-brand-sidebar">
                <div class="mb-4">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3">Join 5SL Studio</span>
                    <h2 class="display-6 fw-bold lh-sm">開啟您的<br>第五賽季人生</h2>
                </div>
                <p class="text-white-50 small lh-lg">只需花費一分鐘填寫右側基礎資訊，您就能立刻解鎖 5SL 專屬會員中心。我們將透過高效安全的 PHP+MySQL 後端防護，全程守護您的個人化數據配置與專案軌跡。</p>
                <div class="mt-4 pt-3 border-top border-secondary text-white-50 small">
                    <i class="bi bi-shield-check text-warning me-2"></i> 您的隱私數據皆經過 256 位元強效雜湊加密
                </div>
            </div>

            <!-- 💡 右側欄：精緻註冊表單 -->
            <div class="col-lg-7 p-4 p-md-5 bg-white">
                <div class="mb-4">
                    <h3 class="fw-bold text-dark mb-1">免費註冊</h3>
                    <p class="text-muted small mb-0">填寫以下資訊，開啟您在 Fifth Season Life 的全新智慧體驗</p>
                </div>
                
                <!-- 註冊表單 -->
                <form id="signupForm" autocomplete="off" novalidate>
                    <!-- 電子信箱 -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">電子信箱 (Email) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-at"></i></span>
                            <input type="email" class="form-control py-2.5" id="regEmail" placeholder="作為日後登入帳號與Email驗證" required>
                        </div>
                    </div>

                    <!-- 姓名與性別 (雙欄配置) -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">真實姓名 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control py-2.5" id="regRealName" placeholder="您的真實姓名" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">性別 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-gender-ambiguous"></i></span>
                                <select class="form-select py-2.5" id="regGender" required>
                                    <option value="" selected disabled>請選擇...</option>
                                    <option value="male">男性</option>
                                    <option value="female">女性</option>
                                    <option value="other">不便透露</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 顯示名稱 -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">顯示名稱 / 暱稱 <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-card-heading"></i></span>
                            <input type="text" class="form-control py-2.5" id="regDisplayName" placeholder="網站上公開顯示的別名" required>
                        </div>
                    </div>

                    <!-- 帳號密碼 -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">設定登入密碼 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control py-2.5" id="regPassword" placeholder="至少 8 碼英數字" minlength="8" autocomplete="new-password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">再次確認密碼 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" class="form-control py-2.5" id="regConfirmPassword" placeholder="請再次輸入密碼" minlength="8" autocomplete="new-password" required>
                            </div>
                        </div>
                    </div>
                    <!-- 法律條文確認 -->
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="regNotify" checked>
                        <label class="form-check-label small text-muted" for="regNotify">願意接收 Fifth Season Life 最新課程與專屬會員優惠通知</label>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="regAgree" required>
                        <label class="form-check-label small text-muted" for="regAgree">我已詳細閱讀並同意 <a href="#" class="text-primary text-decoration-none">會員服務條款</a> 與 <a href="#" class="text-primary text-decoration-none">隱私權政策</a> <span class="text-danger">*</span></label>
                    </div>


                    <!-- 收取通知與同意聲明 (維持不變)... -->
                    
                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold mb-3 shadow-sm btn-interactive" id="btnSubmitSignup">
                        <i class="bi bi-check2-circle me-2"></i>確認送出並進行驗證
                    </button>
                </form>

                
                <!-- 第三方快捷連動 -->
                <div class="position-relative text-center my-4 py-1">
                    <hr class="text-muted opacity-25">
                    <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small" style="font-size: 0.8rem;">快捷社群帳號連動</span>
                </div>
                
                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" class="btn btn-light w-100 btn-sm py-2.5 d-flex align-items-center justify-content-center fw-bold text-nowrap border btn-social-interactive" id="btnGoogleRegister">
                            <span class="me-2" style="font-size: 1.1rem;">🌐</span> Google 註冊
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-success w-100 btn-sm py-2.5 d-flex align-items-center justify-content-center fw-bold text-nowrap border-0 btn-social-interactive" style="background-color: #06C755;" id="btnLineRegister">
                            <span class="me-2" style="font-size: 1.1rem;">💬</span> LINE 註冊
                        </button>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <span class="text-muted small">已經是會員了？</span>
                    <a href="5sl_login.php" class="text-dark fw-bold small text-decoration-none ms-1">立即登入專區</a>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ==========================================================================
    POPUP 通知燈箱 (Modal) - 註冊狀態異步反饋專用 (外觀優化)
    ========================================================================== -->
<div class="modal fade" id="authAlertModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-body p-4 text-center">
                <div id="modalAlertIcon" class="display-5 mb-2"></div>
                <h5 class="fw-bold mb-2" id="modalAlertTitle">提示</h5>
                <p class="text-muted small mb-4" id="modalAlertMessage"></p>
                <button type="button" class="btn btn-primary btn-sm px-4 py-2 w-100 fw-bold shadow-none" style="border-radius: 8px;" data-bs-dismiss="modal">確 認</button>
            </div>
        </div>
    </div>
</div>

<?php 
// 引入頁尾與腳本載入
include_once '5sl_include_footer.php'; 
?>

<!-- ==========================================================================
    100% 閉環封裝的 jQuery AJAX 異步控制機制
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

    // 監聽註冊表單送出
    $('#signupForm').on('submit', function(e) {
        e.preventDefault();
        
        const email       = $('#regEmail').val().trim();
        const realName    = $('#regRealName').val().trim();
        const gender      = $('#regGender').val();
        const displayName = $('#regDisplayName').val().trim();
        const password    = $('#regPassword').val();
        const confirmPwd  = $('#regConfirmPassword').val();
        const isNotify    = $('#regNotify').is(':checked') ? 1 : 0;
        const isAgree     = $('#regAgree').is(':checked');

        // 1. 前端欄位與密碼驗證
        if (!email || !realName || !gender || !displayName || !password) {
            showPopup('⚠️', '欄位未齊全', '請填寫所有必填欄位！');
            return;
        }
        if (password.length < 8) {
            showPopup('⚠️', '密碼長度不足', '登入密碼至少需設定 8 個字元！');
            return;
        }
        if (password !== confirmPwd) {
            showPopup('⚠️', '密碼不一致', '兩次輸入的登入密碼不相符，請重新確認！');
            return;
        }
        if (!isAgree) {
            showPopup('🔒', '條款未核取', '您必須勾選並同意服務條款與隱私權政策。');
            return;
        }

        // 2. 發送註冊 AJAX 請求（已包含 password，並與後端雙格式接收完全對齊）
        $.ajax({
            url: './funcFiles/5sl_act_member.php',
            type: 'POST',
            data: {
                action: 'act_member_addnew',
                dataForm: {
                    doMailSend: 1,
                    notify: isNotify,
                    userForm: {
                        email: email,
                        password: password,
                        real_name: realName,
                        gender: gender,
                        display_name: displayName
                    }
                }
            },
            dataType: 'json',
            success: function(response) {
                if (response.success == 1) {
                    showPopup('📧', '註冊申請成功', response.message || '認證信件已寄出，請前往您的 Email 完成驗證。');
                    $('#authAlertModal').on('hidden.bs.modal', function() {
                        window.location.href = '5sl_login.php';
                    });
                } else {
                    showPopup('❌', '註冊失敗', response.message || '註冊失敗，請檢查輸入資料。');
                }
            },
            error: function(xhr, status, error) {
                console.log('PHP 實際回傳內容：', xhr.responseText);
                showPopup('📡', '連線異常', '系統暫時無法處理註冊請求，請稍後再試。');
            }
        });
    });
});
</script>
</body>
</html>
