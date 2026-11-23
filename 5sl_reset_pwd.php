<?php
$current_page = 'reset_pwd';
include_once '5sl_include_header.php';

$token      = trim($_GET['token'] ?? '');
$tokenValid = false;
$errMsg     = '無效或已過期的密碼重設連結，請返回登入頁重新申請。';

if ($token !== '' && preg_match('/^[a-f0-9]{32}$/i', $token) && isset($DB)) {
    $vcRow = DB->row(
        "SELECT vc_id, vc_expiretime FROM fsl_verify_codes WHERE vc_verifycode = ? AND vc_mode = 2 LIMIT 1",
        [$token]
    );
    if ($vcRow) {
        if (strtotime($vcRow['vc_expiretime']) >= time()) {
            $tokenValid = true;
        } else {
            $errMsg = '此密碼重設連結已超過 30 分鐘有效期限，請返回登入頁重新申請。';
        }
    }
}
?>

<div class="container my-5 py-5">
    <div class="row justify-content-center my-4">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="p-4 bg-dark text-white text-center border-bottom border-warning border-3">
                    <span class="badge bg-warning text-dark fw-bold mb-2">PASSWORD RESET</span>
                    <h4 class="fw-bold mb-0">設定您的新登入密碼</h4>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <?php if ($tokenValid): ?>
                        <form id="formResetNewPwd">
                            <input type="hidden" id="resetTokenVal" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">輸入新密碼 <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="resetNewPwd" placeholder="至少 8 碼英數字組合" minlength="8" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-dark">再次確認新密碼 <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="resetConfirmPwd" placeholder="請再次輸入新密碼" minlength="8" required>
                            </div>
                            <div id="resetPwdAlert" class="small fw-bold mb-3"></div>
                            <button type="submit" class="btn btn-dark text-warning fw-bold w-100 py-2 shadow-sm" id="btnSubmitResetPwd">
                                <i class="bi bi-check2-circle me-1"></i>確認重設密碼
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle mb-3" style="width: 72px; height: 72px;">
                                <i class="bi bi-exclamation-triangle-fill display-5"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">連結已失效</h5>
                            <p class="text-muted small mb-4"><?php echo htmlspecialchars($errMsg); ?></p>
                            <a href="5sl_login.php" class="btn btn-dark text-warning fw-bold w-100 py-2">返回會員登入頁</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '5sl_include_footer.php'; ?>

<script>
$(function() {
    $('#formResetNewPwd').on('submit', function(e) {
        e.preventDefault();
        const token      = $('#resetTokenVal').val();
        const newPwd     = $('#resetNewPwd').val();
        const confirmPwd = $('#resetConfirmPwd').val();
        const $btn       = $('#btnSubmitResetPwd');

        if (newPwd !== confirmPwd) {
            $('#resetPwdAlert').addClass('text-danger').text('兩次輸入的新密碼不一致！');
            return;
        }

        $btn.prop('disabled', true).text('處理中...');
        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_do_reset_pwd',
            token: token,
            new_password: newPwd,
            confirm_password: confirmPwd
        }, function(res) {
            if (res.success === 1) {
                $('#resetPwdAlert').removeClass('text-danger').addClass('text-success').text(res.message);
                setTimeout(function() {
                    window.location.href = '5sl_login.php';
                }, 1500);
            } else {
                $('#resetPwdAlert').removeClass('text-success').addClass('text-danger').text(res.message || '重設失敗');
                $btn.prop('disabled', false).text('確認重設密碼');
            }
        }, 'json');
    });
});
</script>
