<?php
$to = 'jaztien@gmail.com';
// 中文主旨必須進行 Base64 編碼，否則收件匣會顯示亂碼
$subject = '=?UTF-8?B?' . base64_encode('測試信件主旨：系統通知') . '?=';

$message = '
<html>
<head><title>系統通知</title></head>
<body>
  <h2>您好！</h2>
  <p>這是一封透過 <strong>PHP 8 mail()</strong> 寄出的測試信件。</p>
</body>
</html>
';

// PHP 8 支援直接用關聯陣列設定 Headers（比傳統字串拼接更安全）
$headers = [
    'MIME-Version' => '1.0',
    'Content-type' => 'text/html; charset=UTF-8',
    'From'         => '=?UTF-8?B?' . base64_encode('網站管理員') . '?= <noreply@yourdomain.com>',
    'Reply-To'     => 'service@yourdomain.com',
    'X-Mailer'     => 'PHP/' . phpversion()
];

if (mail($to, $subject, $message, $headers)) {
    echo '信件已成功交付系統發送！';
} else {
    echo '信件發送失敗，請檢查伺服器設定。';
}
?>
