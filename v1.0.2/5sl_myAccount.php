<?php
// 宣告當前頁面身分，讓 Header 與 Subnavi 自動亮起對應 Active 狀態
$current_page = 'myAccount';
// 引入共用頁首，SESSION 檢查以及操作在 5sl_include_header.php 之內
include_once '5sl_include_header.php';
$mb_id = (int)($_SESSION['mbID'] ?? $_SESSION['user_id'] ?? 0);
$locJS = _fn_getCachedJsonData('5sladmin/jsonFiles/locations.json');
$res = $DB->row('SELECT * FROM members WHERE mb_id=?',array($_SESSION['mbID'] ?? 0));
?>

<div class="container my-5 py-4">
    <div class="row g-4 mt-2">
        
        <!-- 左側：會員中心共用子導航列 -->
        <div class="col-md-4 col-lg-3">
            <?php include_once '5sl_include_subnavi.php'; ?>
        </div>

        <!-- 右側：帳號身份核心工作視窗 -->
        <div class="col-md-8 col-lg-9">
            <div class="card border-0 shadow-sm p-4 p-md-5 bg-white bg-opacity-75 rounded-3 h-100" style="min-height: 788px;">
                <div class="card-body p-0">
                    
                    <!-- 頁面主標題 -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 mb-4">
                        <div>
                            <h3 class="fw-bold text-dark mb-1">
                                <i class="bi bi-person-badge me-2 text-warning"></i>帳號身份 (Identity & Profile)
                            </h3>
                            <p class="text-muted small mb-0">管理您在 Fifth Season Life 的個人基本資訊與帳戶安全設定。</p>
                        </div>
                        <div class="mt-3 mt-md-0">
                            <span class="badge bg-dark text-warning border border-warning px-3 py-2">
                                <i class="bi bi-shield-check me-1"></i>已認證會員
                            </span>
                        </div>
                    </div>

                    <!-- 功能分頁切換按鈕 (Pills) -->
                    <ul class="nav nav-pills mb-4 gap-2" id="accountTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold px-4" id="profile-tab" data-bs-toggle="pill" data-bs-target="#profile-pane" type="button" role="tab" aria-controls="profile-pane" aria-selected="true">
                                <i class="bi bi-person-lines-fill me-2"></i>個人資料維護
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-4" id="security-tab" data-bs-toggle="pill" data-bs-target="#security-pane" type="button" role="tab" aria-controls="security-pane" aria-selected="false">
                                <i class="bi bi-key-fill me-2"></i>變更與找回密碼
                            </button>
                        </li>
                    </ul>

                    <!-- AJAX 操作結果即時提示區 (預設隱藏) -->
                    <div id="accountAlertBox" class="alert d-none align-items-center mb-4" role="alert">
                        <i id="accountAlertIcon" class="bi me-2 fs-5"></i>
                        <div id="accountAlertMsg" class="small fw-bold"></div>
                    </div>

                    <!-- 分頁內容容器 -->
                    <div class="tab-content" id="accountTabContent">
                        
                        <!-- ============================================================
                             區塊 1：個人資料維護
                             ============================================================ -->
                        <div class="tab-pane fade show active" id="profile-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                            <form id="formUpdateProfile">
                                <!-- 頭像預覽與更換區 -->
                                <div class="d-flex flex-column flex-sm-row align-items-center gap-4 p-4 bg-light rounded-3 mb-4 border">
                                    <div class="position-relative">
                                        <img src="<?php echo _fn_getMemberAvatar($mb_id, true); ?>" id="profileAvatarPreview" class="rounded-circle border border-3 border-warning shadow-sm" style="width: 96px; height: 96px; object-fit: cover;" alt="會員大頭貼">
                                        <label for="avatarUploadInput" class="btn btn-sm btn-dark text-warning rounded-circle position-absolute bottom-0 end-0 shadow" style="width: 32px; height: 32px; padding: 4px; cursor: pointer;" title="更換大頭貼">
                                            <i class="bi bi-camera-fill"></i>
                                        </label>
                                        <input type="file" id="avatarUploadInput" name="avatar" class="d-none" accept="image/png, image/jpeg, image/webp">
                                    </div>
                                    <div class="text-center text-sm-start">
                                        <h6 class="fw-bold text-dark mb-1">個人專屬頭像</h6>
                                        <p class="text-muted small mb-2">支援 JPG、PNG 或 WebP 格式，檔案大小請勿超過 2MB。</p>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="$('#avatarUploadInput').click();">
                                            <i class="bi bi-upload me-1"></i>選擇圖片檔案
                                        </button>
                                    </div>
                                </div>

                                <!-- 基本資料表單欄位 -->
                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label for="mb_name" class="form-label small fw-bold text-dark">真實姓名 <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control" id="mb_name" name="mb_name" placeholder="請輸入您的真實姓名" value="<?php echo isset($res['mb_name']) ? _fn_secure_crypto($res['mb_name'], 'de') : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mb_gender" class="form-label small fw-bold text-muted">性別 <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                                <input type="radio" class="btn-check" name="mb_gender" id="mb_gender1" value="M" autocomplete="off"<?php echo (isset($res['mb_gender']) && $res['mb_gender'] == 'M') ? ' checked' : ''; ?>>
                                                <label class="btn btn-outline-info" for="mb_gender1">男性</label>

                                                <input type="radio" class="btn-check" name="mb_gender" id="mb_gender2" value="N" autocomplete="off"<?php echo (!isset($res['mb_gender']) || $res['mb_gender'] == 'N') ? ' checked' : ''; ?>>
                                                <label class="btn btn-outline-secondary" for="mb_gender2">保密</label>

                                                <input type="radio" class="btn-check" name="mb_gender" id="mb_gender3" value="F" autocomplete="off"<?php echo (isset($res['mb_gender']) && $res['mb_gender'] == 'F') ? ' checked' : ''; ?>>
                                                <label class="btn btn-outline-danger" for="mb_gender3">女性</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">電子信箱/帳號 <span class="badge bg-secondary ms-1">不可變更</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                                            <input type="email" class="form-control bg-light text-muted" value="<?php echo isset($res['mb_email']) ? _fn_secure_crypto($res['mb_email'], 'de') : ''; ?>" readonly disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mb_title" class="form-label small fw-bold text-dark">顯示暱稱 <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control" id="mb_title" name="mb_title" placeholder="請輸入您的稱呼" value="<?php echo $res['mb_title'] ?? ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mb_cellphone" class="form-label small fw-bold text-dark">行動電話</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-phone"></i></span>
                                            <input type="tel" class="form-control" id="mb_cellphone" name="mb_cellphone" placeholder="例如：0912-345-678" value="<?php echo isset($res['mb_cellphone']) ? _fn_secure_crypto($res['mb_cellphone'], 'de') : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mb_birth" class="form-label small fw-bold text-dark">出生年月日</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-calendar-event"></i></span>
                                            <input type="date" class="form-control" id="mb_birth" name="mb_birth" value="<?php echo $res['mb_birth'] ?? ''; ?>" placeholder="yyyy-mm-dd">
                                        </div>
                                    </div>
                                    <div class="col-md-6">

                                        <label for="mb_city" class="form-label small fw-bold text-dark">居住城市</label>
                                        <select class="form-control" id="mb_city" name="mb_city" placeholder="通訊地址" data-placeholder="-請選擇居住城市-" data-select="city">
                                        <option value="">-請選擇居住城市-</option>
                                        <?php
                                        foreach($locJS as $key => $value) {
                                            echo "<option value='".$value['value']."'";
                                            if(!empty($res['mb_city']) && $res['mb_city']==$value['value']) { echo " selected"; }
                                            echo ">".$value['text']."</option>";
                                        }
                                        ?>
                                        </select>
                                    </div>


                                    <div class="col-md-6">
                                        <label for="mb_district" class="form-label small fw-bold text-dark">行政區劃</label>
                                        <select class="form-control" id="mb_district" name="mb_district" placeholder="行政區劃" data-placeholder="-請選擇行政區劃-" dist-fromcity="mb_city" style="width: 100%;">
                                        <?php
                                        if(!empty($res['mb_city'])) {
                                            echo "<option value='0'>-請選擇行政劃區-</option>";
                                            foreach($locJS[$res['mb_city']]['district'] as $key => $value){
                                            echo "<option value='".$key."'";
                                            if(!empty($res['mb_district']) && $res['mb_district']==$key) { echo " selected"; }
                                            echo ">".$value."</option>";
                                            }
                                            //CHARINDEX
                                        }
                                        ?>
                                        </select>

                                    </div>




                                    <div class="col-12">
                                        <label for="mb_add" class="form-label small fw-bold text-dark">聯絡通訊地址</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
                                            <input type="text" class="form-control" id="mb_add" name="mb_add" placeholder="請輸入聯絡地址" value="<?php echo isset($res['mb_add']) ? _fn_secure_crypto($res['mb_add'], 'de') : ''; ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-top text-end">
                                    <button type="submit" class="btn btn-dark text-warning fw-bold px-4 py-2 shadow-sm">
                                        <i class="bi bi-check2-circle me-1"></i>儲存個人資料變更
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- ============================================================
                             區塊 2：變更密碼、忘記密碼找回
                             ============================================================ -->
                        <div class="tab-pane fade" id="security-pane" role="tabpanel" aria-labelledby="security-tab" tabindex="0">
                            <div class="row g-4">
                                
                                <!-- 左半部：主動變更密碼 -->
                                <div class="col-lg-7">
                                    <div class="p-4 border rounded-3 h-100 bg-white">
                                        <h5 class="fw-bold text-dark mb-1"><i class="bi bi-shield-lock me-2 text-primary"></i>主動變更登入密碼</h5>
                                        <p class="text-muted small mb-4">建議定期更新密碼，並混合英文大小寫與數字以提升安全性。</p>
                                        
                                        <form id="formChangePassword">
                                            <div class="mb-3">
                                                <label for="currentPwd" class="form-label small fw-bold text-dark">目前使用的密碼 <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="currentPwd" name="current_password" placeholder="請輸入舊密碼" required>
                                                    <button class="btn btn-outline-secondary toggle-pwd-btn" type="button" data-target="#currentPwd"><i class="bi bi-eye"></i></button>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="newPwd" class="form-label small fw-bold text-dark">設定新密碼 <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="newPwd" name="new_password" placeholder="至少 8 碼英數字組合" minlength="8" required>
                                                    <button class="btn btn-outline-secondary toggle-pwd-btn" type="button" data-target="#newPwd"><i class="bi bi-eye"></i></button>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <label for="confirmNewPwd" class="form-label small fw-bold text-dark">再次確認新密碼 <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="confirmNewPwd" name="confirm_password" placeholder="請再次輸入新密碼" minlength="8" required>
                                                    <button class="btn btn-outline-secondary toggle-pwd-btn" type="button" data-target="#confirmNewPwd"><i class="bi bi-eye"></i></button>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary fw-bold w-100 py-2">
                                                <i class="bi bi-arrow-repeat me-1"></i>確認更新密碼
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- 右半部：忘記密碼快速找回 -->
                                <div class="col-lg-5">
                                    <div class="p-4 border border-warning rounded-3 h-100 bg-light d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-inline-flex align-items-center justify-content-center bg-warning text-dark rounded-circle mb-3" style="width: 48px; height: 48px;">
                                                <i class="bi bi-envelope-exclamation-fill fs-4"></i>
                                            </div>
                                            <h5 class="fw-bold text-dark mb-2">忘記目前密碼？</h5>
                                            <p class="text-muted small lh-lg mb-3">
                                                若您已遺忘目前的登入密碼，無法完成左側的舊密碼驗證，可透過系統寄送「一次性密碼重設連結」至您的註冊信箱。
                                            </p>
                                            <div class="p-3 bg-white rounded border small text-muted mb-4">
                                                <div class="fw-bold text-dark mb-1">目標接收信箱：</div>
                                                <div class="text-truncate text-primary fw-bold">
                                                    <?php echo isset($res['mb_email']) ? _fn_secure_crypto($res['mb_email'], 'de') : '系統無法取得信箱'; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button type="button" id="btnSendResetPwdMail" class="btn btn-outline-dark fw-bold w-100 py-2">
                                                <i class="bi bi-send me-1"></i>寄送密碼重設信件
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div> <!-- 結束 tab-content -->

                </div>
            </div>
        </div>
        
    </div>
</div>

<style>
    body {
        background-image: url("imageFiles/mainBG-3.jpg");
        background-attachment: fixed;   /* 核心語句：固定背景圖不隨頁面滾動 */
        background-size: cover;         /* 等比例縮放填滿整個視窗 */
        background-position: center;    /* 背景圖置中對齊 */
        background-repeat: no-repeat;   /* 防止圖片重複平鋪 */
    }
    #accountTab .nav-link {
        color: #495057;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
    }
    #accountTab .nav-link:hover {
        background-color: #e9ecef;
        color: #212529;
    }
    #accountTab .nav-link.active {
        background-color: #212529 !important;
        color: #ffc107 !important;
        border-color: #212529 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
</style>

<?php include_once '5sl_include_footer.php'; ?>

<script>
$(function() {
    ////////////////////////////////////////////////////////
    if (typeof flatpickr !== 'undefined') {
            flatpickr($('input[type="date"]'), {
                locale: 'zh_tw',
                dateFormat: 'Y-m-d',   // 嚴格統一為 yyyy-mm-dd
                allowInput: true,      // 允許直接用鍵盤輸入 2026-11-17
                disableMobile: true    // 手機與平板也強制維持 yyyy-mm-dd 格式，不被系統原生輪盤覆蓋
            });
    }

    ////////////////////////////////////////////////////////
    // 1. 通用訊息提示控制函式
    function showAccountAlert(type, message) {
        const $box = $('#accountAlertBox');
        const $icon = $('#accountAlertIcon');
        const $msg = $('#accountAlertMsg');

        $box.removeClass('d-none alert-success alert-danger alert-warning').addClass('d-flex');
        $icon.removeClass('bi-check-circle-fill bi-exclamation-triangle-fill');

        if (type === 'success') {
            $box.addClass('alert-success');
            $icon.addClass('bi-check-circle-fill');
        } else {
            $box.addClass('alert-danger');
            $icon.addClass('bi-exclamation-triangle-fill');
        }

        $msg.text(message);
        $('html, body').animate({ scrollTop: $box.offset().top - 120 }, 300);
    }

    // 切換分頁時自動隱藏舊提示訊息
    $('#accountTab button[data-bs-toggle="pill"]').on('shown.bs.tab', function() {
        $('#accountAlertBox').addClass('d-none').removeClass('d-flex');
    });

    // 2. 大頭貼即時預覽與檔案大小防呆檢核
    $('#avatarUploadInput').on('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            showAccountAlert('danger', '圖片檔案過大！請選擇小於 2MB 的 JPG 或 PNG 圖片。');
            $(this).val('');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(event) {
            $('#profileAvatarPreview').attr('src', event.target.result);
        };
        reader.readAsDataURL(file);
    });

    // 3. 密碼欄位「顯示 / 隱藏」眼睛按鈕切換
    $('.toggle-pwd-btn').on('click', function() {
        const targetSelector = $(this).attr('data-target');
        const $input = $(targetSelector);
        const $icon = $(this).find('i');

        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // 4. AJAX 送出：個人資料維護 (支援大頭貼檔案上傳 + 欄位更新)
    $('#formUpdateProfile').on('submit', function(e) {
        e.preventDefault();
        if (window.fslLoader) window.fslLoader.show();

        const formData = new FormData(this);
        formData.append('action', 'act_update_profile');

        $.ajax({
            url: './funcFiles/5sl_act_member.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (window.fslLoader) window.fslLoader.hide();
                if (res.success === 1) {
                    showAccountAlert('success', res.message || '個人資料已成功更新並同步儲存！');

                    // 若為首次完整填寫個人資料並升級為正式會員，觸發 3D 慶祝獎勵卡片
                    if (res.profile_upgraded === 1 && window.fslEpicEngine) {
                        window.fslEpicEngine.trigger({
                            contentType: 'html',
                            contentSource: window.fslEpicEngine.createCouponTemplate({
                                bgUrl: 'imageFiles/test/<?php echo rand(1,18); ?>.jpg',
                                points: (res.reward_pts || 100).toString(),
                                unit: 'PTS',
                                title: 'PROFILE COMPLETED!',
                                subTitle: '感謝完整填寫會員資料，帳戶已正式啟用！',
                                label: '★ 專屬完善資料獎勵點數已匯入您的帳戶 ★'
                            }),
                            theme: 'celebration',
                            onConfirm: function(close) {
                                close();
                            }
                        });
                    }
                } else {
                    showAccountAlert('danger', res.message || '資料更新失敗，請檢查欄位後再試。');
                }
            },
            error: function() {
                if (window.fslLoader) window.fslLoader.hide();
                showAccountAlert('danger', '網路連線異常，請稍後再試。');
            }
        });
    });

    // 5. AJAX 送出：主動變更密碼
    $('#formChangePassword').on('submit', function(e) {
        e.preventDefault();
        const currentPwd = $('#currentPwd').val();
        const newPwd = $('#newPwd').val();
        const confirmPwd = $('#confirmNewPwd').val();

        if (newPwd !== confirmPwd) {
            showAccountAlert('danger', '兩次輸入的新密碼不一致，請重新確認！');
            $('#confirmNewPwd').focus();
            return;
        }

        if (window.fslLoader) window.fslLoader.show();

        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_change_password',
            current_password: currentPwd,
            new_password: newPwd,
            confirm_password: confirmPwd
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();
            if (res.success === 1) {
                $('#formChangePassword')[0].reset();
                showAccountAlert('success', res.message || '登入密碼已成功變更！下次登入請使用新密碼。');
            } else {
                showAccountAlert('danger', res.message || '密碼變更失敗，請確認目前密碼是否正確。');
            }
        }, 'json').fail(function() {
            if (window.fslLoader) window.fslLoader.hide();
            showAccountAlert('danger', '網路連線異常，請稍後再試。');
        });
    });

    // 6. AJAX 送出：忘記密碼找回 (寄發重設驗證信)
    $('#btnSendResetPwdMail').on('click', function() {
        const $btn = $(this);
        if (window.fslLoader) window.fslLoader.show();

        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_send_reset_pwd'
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();
            if (res.success === 1) {
                showAccountAlert('success', res.message || '密碼重設驗證信已寄出！請前往您的註冊信箱收信。');
                let cooldown = 60;
                $btn.prop('disabled', true).html(`<i class="bi bi-hourglass-split me-1"></i>已寄出 (${cooldown}s)`);
                const timer = setInterval(function() {
                    cooldown--;
                    if (cooldown <= 0) {
                        clearInterval(timer);
                        $btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i>重新寄送重設信件');
                    } else {
                        $btn.html(`<i class="bi bi-hourglass-split me-1"></i>已寄出 (${cooldown}s)`);
                    }
                }, 1000);
            } else {
                showAccountAlert('danger', res.message || '驗證信發送失敗，請稍後再試。');
            }
        }, 'json').fail(function() {
            if (window.fslLoader) window.fslLoader.hide();
            showAccountAlert('danger', '網路連線異常，請稍後再試。');
        });
    });





});
</script>
