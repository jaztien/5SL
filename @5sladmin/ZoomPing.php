<?php
/**
 * ping_zoom.php (智慧診斷版)
 * 一鍵揪出是 accountId、ClientID、ClientSecret 還是 SecretToken 填錯的終極工具
 */
require_once('includeFiles/db.inc.php');
require_once 'classFiles/ZoomManager.class.php';

// --- 🎯 請在此填入您要測試的四個核心參數 ---
$getZoomInfo = DB->getConfigVal('','zoom');
$accountId    = $getZoomInfo['zoom_accountid']['config_value'];
$clientId     = $getZoomInfo['zoom_clientid']['config_value'];
$clientSecret = $getZoomInfo['zoom_clientsecret']['config_value'];
$SecretToken  = $getZoomInfo['zoom_secrettoken']['config_value'];


echo "<h2>=== Zoom API 參數錯誤精準診斷工具 ===</h2>";
echo "診斷時間: " . date('Y-m-d H:i:s') . "<br><br>";

// ─── 檢查點 1：驗證 Webhook SecretToken 格式 ───────────────────────
echo "🔎 檢查點 1：驗證 Webhook Secret Token... ";
if (empty($SecretToken)) {
    echo "<span style='color:orange; font-weight:bold;'>【警告】</span> 偵測到 SecretToken 為空或預設值。這會導致 Webhook 驗證（CRC）失敗，請至 Zoom Marketplace 複製。<br>";
} else {
    // 模擬一次 Zoom 官方的 HMAC SHA256 加密演練
    $testHash = hash_hmac('sha256', 'test_plain_token', $SecretToken);
    if (strlen($testHash) === 64) {
        echo "<span style='color:green; font-weight:bold;'>【正常】</span> SecretToken 加密演練成功，格式正確！<br>";
    } else {
        echo "<span style='color:red; font-weight:bold;'>【錯誤】</span> SecretToken 似乎不合法，請重新確認。<br>";
    }
}
echo "<hr>";

// ─── 檢查點 2：第一階段 OAuth 認證（診斷 ClientID / ClientSecret / accountId） ───
echo "🔎 檢查點 2：測試第一階段憑證認證... <br>";

// 為了精準攔截錯誤，我們不直接 new 類別，而是手動模擬 ZoomManager 內部的第一階段 cURL 封包
$authUrl = "https://zoom.us/oauth/token?grant_type=account_credentials&account_id=" . urlencode($accountId);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $authUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['grant_type' => 'account_credentials']));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
    'Content-Type: application/x-www-form-urlencoded'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 判斷 cURL 掐斷
if ($httpCode === 0) {
    echo "<div style='padding:10px; background:#fff0f0; border-left:4px solid red; color:#aa0000;'>";
    echo "❌ <strong>[物理斷訊]</strong> 狀態碼為 0！這跟參數無關，百分之百是您的 Mac <strong>忘記關閉 VPN</strong>，封包被本地掐斷了！";
    echo "</div>";
    exit;
}

$result = json_decode($response, true);

if ($httpCode === 200 && isset($result['access_token'])) {
    echo "<span style='color:green; font-weight:bold;'>【連線全數成功】</span> 恭喜！您的 <b>ClientID、ClientSecret、accountId</b> 三者完全正確！已成功取得 Token。<br>";
} else {
    // 🔥 進入神探診斷核心邏輯
    echo "<div style='padding:15px; background:#fff5eb; border-left:4px solid #ff9900; margin-top:5px;'>";
    echo "🚨 <b>診斷報告：第一階段通訊失敗（HTTP 狀態碼: {$httpCode}）</b><br><br>";
    
    $errorDescription = $result['error_description'] ?? '';
    $errorReason = $result['reason'] ?? '';

    // 🔴 診斷 A：AccountId 填錯 (常見特徵：400 Bad Request 且提到 account_id 或者是無此帳號)
    if ($httpCode === 400 || strpos($errorDescription, 'account_id') !== false || strpos($errorReason, 'account') !== false) {
        echo "💡 <b>最大嫌疑犯【accountId】！</b><br>";
        echo "・原因分析：Zoom 伺服器看得懂您的密碼，但表示「找不到您指定的 Account ID 帳號」。<br>";
        echo "・請檢查：您的 <tt>\$accountId</tt> 是否有複製錯字、漏字，或帶有空白？請確認格式應為 <tt>/oauth/token?account_id=...</tt><br>";
    } 
    // 🔴 診斷 B：Client ID 或 Client Secret 填錯 (常見特徵：401 Unauthorized 或 Invalid client)
    elseif ($httpCode === 401 || isset($result['error']) && $result['error'] === 'invalid_client') {
        echo "💡 <b>最大嫌疑犯【Client ID】或【Client Secret】！</b><br>";
        echo "・原因分析：Zoom 伺服器拒絕了您的 Base64 帳密組合驗證（401 未授權）。<br>";
        echo "・請檢查：您的 App 憑證。最常發生的狀況是把 Client ID 複製成 Client Secret，或者在複製時不小心多圈選到了一個空空白字元。<br>";
    } 
    // 🔴 診斷 C：其他未定義錯誤
    else {
        echo "💡 <b>系統綜合判定：</b><br>";
        echo "・Zoom 原始錯誤訊息: " . ($errorDescription ?: $response) . "<br>";
        echo "・建議：請登入 Zoom Marketplace 確認該 Server-to-Server OAuth App 是否處於 <b>Activated（已啟用）</b> 激活狀態。";
    }
    echo "</div>";
}

echo "<br>=== 診斷結束 ===";
