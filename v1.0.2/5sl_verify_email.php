<?php
$current_page = 'verify';
include_once '5sl_include_header.php';

$token       = trim($_GET['token'] ?? '');
$verifyState = 'invalid'; // success | expired | invalid
$statusTitle = '無效的驗證連結';
$statusMsg   = '找不到對應的驗證資訊，此連結可能已經使用過、格式不完整或已失效。';
$memberTitle = '';

if ($token !== '' && preg_match('/^[a-f0-9]{32}$/i', $token) && isset($DB)) {
    $vcRow = $DB->row(
        "SELECT vc.vc_id, vc.mb_id, vc.vc_expiretime, m.mb_title, m.mb_email_verified
         FROM data_verify_codes vc
         INNER JOIN members m ON vc.mb_id = m.mb_id
         WHERE vc.vc_verifycode = ? AND vc.vc_mode = 1
         LIMIT 1",
        [$token]
    );

    if ($vcRow) {
        $memberTitle = $vcRow['mb_title'] ?: '第五季會員';
        $mId         = (int)$vcRow['mb_id'];

        if ((int)$vcRow['mb_email_verified'] === 1) {
            // 已經驗證過
            $DB->query("DELETE FROM data_verify_codes WHERE vc_id = ?", [(int)$vcRow['vc_id']]);
            $verifyState = 'success';
            $statusTitle = '您的電子信箱已完成驗證！';
            $statusMsg   = "親愛的 {$memberTitle}，您的電子信箱先前已通過認證，現在即可直接登入會員中心。";
        } elseif (strtotime($vcRow['vc_expiretime']) < time()) {
            // 超過 30 分鐘有效期限
            $verifyState = 'expired';
            $statusTitle = '驗證連結已超過 30 分鐘有效期限';
            $statusMsg   = "親愛的 {$memberTitle}，為了保障您的帳戶安全，此認證連結已逾時失效。請點擊下方按鈕重新發送新的驗證信！";
        } else {
            // 驗證通過：將 mb_email_verified 更新為 1，並刪除已使用的驗證碼
            $DB->beginTransaction();
            try {
                $DB->query("UPDATE members SET mb_email_verified = 1, mb_updated = NOW() WHERE mb_id = ?", [$mId]);
                $DB->query("DELETE FROM data_verify_codes WHERE mb_id = ? AND vc_mode = 1", [$mId]);
                $DB->commit();

                $verifyState = 'success';
                $statusTitle = '電子信箱驗證成功！帳戶已正式啟用';
                $statusMsg   = "歡迎加入 Fifth Season Life 人生第五季，{$memberTitle}！您的會員權益已全數開通，立即登入開啟專屬旅程。";
            } catch (Exception $e) {
                $DB->rollBack();
                $verifyState = 'invalid';
                $statusTitle = '系統驗證處理異常';
                $statusMsg   = '啟用帳戶時發生暫時性錯誤，請稍後再試或聯繫客服人員。';
            }
        }
    }

}
?>

<div class="container my-5 py-5">
    <div class="row justify-content-center my-4">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden text-center">
                <div class="p-4 bg-dark text-white border-bottom border-warning border-3">
                    <span class="badge bg-warning text-dark fw-bold mb-2">ACCOUNT VERIFICATION</span>
                    <h4 class="fw-bold mb-0">會員電子信箱驗證中心</h4>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <?php if ($verifyState === 'success'): ?>
                        <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle mb-3" style="width: 76px; height: 76px;">
                            <i class="bi bi-patch-check-fill display-4"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($statusTitle); ?></h5>
                        <p class="text-muted small lh-lg mb-4"><?php echo htmlspecialchars($statusMsg); ?></p>
                        <a href="5sl_login.php" class="btn btn-dark text-warning fw-bold w-100 py-2 shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i>立即前往會員登入
                        </a>

                    <?php elseif ($verifyState === 'expired'): ?>
                        <div class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning rounded-circle mb-3" style="width: 76px; height: 76px;">
                            <i class="bi bi-hourglass-bottom display-4"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($statusTitle); ?></h5>
                        <p class="text-muted small lh-lg mb-3"><?php echo htmlspecialchars($statusMsg); ?></p>

                        <form id="formResendFromVerify" class="text-start bg-light p-3 rounded-3 border mb-3">
                            <label class="form-label small fw-bold text-dark">輸入註冊電子信箱以重發驗證信：</label>
                            <div class="input-group input-group-sm">
                                <input type="email" class="form-control" id="verifyPageEmail" placeholder="name@example.com" required>
                                <button type="submit" class="btn btn-warning text-dark fw-bold px-3" id="btnVerifyPageResend">重發驗證信</button>
                            </div>
                        </form>
                        <div id="verifyPageAlert" class="small fw-bold mb-2"></div>

                    <?php else: ?>
                        <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle mb-3" style="width: 76px; height: 76px;">
                            <i class="bi bi-exclamation-triangle-fill display-4"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($statusTitle); ?></h5>
                        <p class="text-muted small lh-lg mb-4"><?php echo htmlspecialchars($statusMsg); ?></p>
                        <div class="d-grid gap-2">
                            <a href="5sl_login.php" class="btn btn-dark text-warning fw-bold py-2">返回會員登入頁（可重發驗證信）</a>
                            <a href="5sl_signup.php" class="btn btn-outline-secondary btn-sm">前往註冊新帳號</a>
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
    $('#formResendFromVerify').on('submit', function(e) {
        e.preventDefault();
        const email = $('#verifyPageEmail').val().trim();
        const $btn  = $('#btnVerifyPageResend');
        if (!email) return;

        $btn.prop('disabled', true).text('寄送中...');
        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_resend_verify',
            email: email
        }, function(res) {
            if (res.success === 1) {
                $('#verifyPageAlert').removeClass('text-danger').addClass('text-success').text(res.message);
                $btn.text('已寄出');
            } else {
                $('#verifyPageAlert').removeClass('text-success').addClass('text-danger').text(res.message || '重發失敗');
                $btn.prop('disabled', false).text('重發驗證信');
            }
        }, 'json');
    });
});
</script>
