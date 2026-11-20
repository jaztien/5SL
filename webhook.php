<?php
/**
 * webhook.php (終極全功能擴充版)
 * Zoom API 學院自動化點名、錄影下架、講師防呆與 VIP 智慧監控中心
 * 
 * 建議設定之網址: https://ngrok-free.dev
 * ⚠️ 物理狀態提醒：本地測試時請務必關閉 Mac 上的 VPN！
 */

// 引入核心 ZoomManager 檔案，以便在 Webhook 中反向呼叫 API 進行權限操作
require_once('5sladmin/includeFiles/db.inc.php');
require_once '5sladmin/classFiles/ZoomManager.class.php';

$getZoomInfo = $DB->getConfigVal('','zoom');
$accountId    = $getZoomInfo['zoom_accountid']['config_value'];
$clientId     = $getZoomInfo['zoom_clientid']['config_value'];
$clientSecret = $getZoomInfo['zoom_clientsecret']['config_value'];
$SecretToken = $getZoomInfo['zoom_secrettoken']['config_value'];

// 初始化 ZoomManager（供智慧型功能呼叫 API 使用，請替換為您的正式憑證）
$zoomManager = new ZoomUltimateCourseManager($clientId, $clientSecret, $accountId);

$rawJson = file_get_contents('php://input');
$data = json_decode($rawJson, true);

/* ==========================================================================
   安全機制：處理 Zoom 官方 CRC 明文驗證
   💡 這裡做出寬容優化：只要發現 Zoom 來要驗證，無論如何都直接計算回傳，確保點亮綠燈
   ========================================================================== */
if (isset($data['event']) && $data['event'] === 'endpoint.url_validation') {
    $plainToken = $data['payload']['plainToken'];
    $hashValue  = hash_hmac('sha256', $plainToken, ZOOM_WEBHOOK_SECRET_TOKEN);
    
    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode([
        'plainToken' => $plainToken,
        'encryptedToken' => $hashValue
    ]);
    exit;
}

/* ==========================================================================
   💡 防禦微調：如果是瀏覽器點擊或 ngrok HTML 干擾（不含標準 JSON）
   我們讓他顯示友善的等待提示，而不是噴 400 Invalid Payload 阻斷連線
   ========================================================================== */
if (!$data) {
    header('Content-Type: text/plain; charset=utf-8');
    // 只留下一句最簡單的狀態，外人看不出這是什麼系統，但您自己看 Log 知道它活著
    exit('Service Operational.'); 
}
$event     = $data['event'] ?? '';
$payload   = $data['payload']['object'] ?? [];
$meetingId = $payload['id'] ?? '';

switch ($event) {

    /* ==========================================================================
       實用功能 1：實時進出點名 + VIP/助教進場智慧提示
       ========================================================================== */
    case 'meeting.participant_joined':
    case 'meeting.participant_left':
        $participant = $payload['participant'] ?? [];
        
        // 從 custom_questions 解出當初透傳的 student_id
        $studentId = null;
        if (!empty($participant['custom_questions'])) {
            foreach ($participant['custom_questions'] as $question) {
                if ($question['title'] === 'student_id') {
                    $studentId = $question['value'];
                    break;
                }
            }
        }

        if ($studentId !== null) {
            $studentName = $participant['user_name'] ?? '未知學員';
            $timeAction  = $participant['date_time'] ?? date('Y-m-d H:i:s');
            
            if ($event === 'meeting.participant_joined') {
                // 執行點名寫入
                logToDatabase($meetingId, $studentId, $studentName, 'JOIN', $timeAction);
                
                // 【智慧擴充：VIP/助教身份動態識別】
                // 學院實務：此處可從本地資料庫查詢該 student_id 的身份標籤
                $isVIPOrAssistant = ($studentId == '9527'); // 範例：假設 9527 是核心助教
                
                if ($isVIPOrAssistant) {
                    // 自動記錄 Log 或發送即時通知，提醒講師或在官網後台彈出提示
                    logSystemAlert("【智慧助教提示】特聘助教/VIP學員 [{$studentName}] 已進入教室 {$meetingId}。");
                }
                
            } else {
                // 執行出場點名，用以課後扣除或計算總時數
                logToDatabase($meetingId, $studentId, $studentName, 'LEFT', $timeAction);
            }
        }
        break;

    /* ==========================================================================
       實用功能 2：雲端錄影與白板板書自動同步系統
       ========================================================================== */
    case 'recording.completed':
        $recordingFiles = $payload['recording_files'] ?? [];
        $topic          = $payload['topic'] ?? '未命名課程';
        
        logSystemAlert("【雲端同步】課程 [{$topic}] 的雲端錄影與白板已處理完成，準備下載存檔...");
        
        foreach ($recordingFiles as $file) {
            $downloadUrl = $file['download_url'] ?? '';
            $fileType    = $file['file_type'] ?? ''; // MP4 (影片), M4A (音訊), TIMELINE, WHITEBOARD (白板板書)
            $fileSize    = $file['file_size'] ?? 0;
            
            // 🛠️ 學院實務：在此處將錄影檔案與白板圖檔寫入本地資料庫的補課資料表
            // 讓有權限的學員登入官網後台時，能直接抓到對應的 $downloadUrl 進行線上補課
            saveRecordingToAcademy($meetingId, $topic, $downloadUrl, $fileType, $fileSize);
        }
        break;

    /* ==========================================================================
       實用功能 3：講師控管、未到通知與防呆預警
       ========================================================================== */
    case 'meeting.started':
        $startTime = $payload['start_time'] ?? date('Y-m-d H:i:s');
        $topic     = $payload['topic'] ?? '未命名課程';
        
        // 🛠️ 學院實務：可從資料庫比對這堂課原本「預計開始時間」
        // 如果表訂 19:00 開課，但此事件在 19:05 之後才觸發，或是提早 2 小時觸發，進行相應提醒
        logSystemAlert("【開課通知】講師已成功開啟教室 [{$topic}]，開門時間為: {$startTime}。");
        break;

    case 'meeting.ended':
        $endTime = $payload['end_time'] ?? date('Y-m-d H:i:s');
        logSystemAlert("【下課通知】會議ID: {$meetingId} 已正式由主持人關閉，下課時間為: {$endTime}。");
        break;
}

// 100% 安全回傳 200 給 Zoom
http_response_code(200);
echo json_encode(['status' => 'success']);

/**
 * 資料庫/Log 記錄常規點名方法
 */
function logToDatabase($meetingId, $studentId, $name, $action, $time) {
    $logMessage = sprintf("[%s] 教室: %s | 學員: %s (ID: %s) | 動作: %s\n", $time, $meetingId, $name, $studentId, $action);
    file_put_contents(__DIR__ . '/zoom_attendance_log.txt', $logMessage, FILE_APPEND);
}

/**
 * 自動化業務系統：記錄雲端影片與板書資產
 */
function saveRecordingToAcademy($meetingId, $topic, $url, $type, $size) {
    $logMessage = sprintf("[%s] 教室: %s | 課程: %s | 類型: %s | 連結: %s | 大小: %s bytes\n", date('Y-m-d H:i:s'), $meetingId, $topic, $type, $url, $size);
    file_put_contents(__DIR__ . '/zoom_recordings_sync.txt', $logMessage, FILE_APPEND);
}

/**
 * 學院維運系統：發送警報、防呆與即時通知 Log
 */
function logSystemAlert($message) {
    $logMessage = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message);
    file_put_contents(__DIR__ . '/zoom_system_alerts.txt', $logMessage, FILE_APPEND);
    
    // 💡 提示：此處可直接接上您的 Line Notify、Telegram 或簡訊 API
    // 當有嚴重異常（如講師遲到）時，直接把 $message 實時推送到您的手機。
}

?>


<?php
// 確保沒有任何 HTML 或空白在 <?php 之前輸出

// === 【除錯專用】在最開頭記錄：只要有人連過來，立刻寫入檔案 ===
$rawInput = file_get_contents('php://input');
file_put_contents('webhook_debug.txt', "[".date('Y-m-d H:i:s')."] 收到請求！原始資料：\n" . $rawInput . "\n\n", FILE_APPEND);
//file_put_contents(__DIR__ . '/webhook_debug.txt', "測試寫入\n", FILE_APPEND);
$data = json_decode($rawInput, true);

// 🔥 【修正】將 verification 改為 validation
if (isset($data['event']) && $data['event'] === 'endpoint.url_validation') {
    
    // 請確認此處已替換為您在 Feature 頁面看到的 Webhook Secret Token
    $secretToken = 'FTXxNhqQSu67dbOMKn7t0w'; 
    
    $plainToken = $data['payload']['plainToken'];
    
    // 使用 HMAC-SHA256 加密挑戰碼
    $hash = hash_hmac('sha256', $plainToken, $secretToken);
    
    // 清空所有緩衝區，確保輸出純淨的 JSON
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    
    echo json_encode([
        'plainToken' => $plainToken,
        'encryptedToken' => $hash
    ]);
    exit(); // 強制中斷，避免混入其他輸出
}

// 正常點名邏輯...
http_response_code(200);




// ---- 這裡開始處理日常的點名通知 ----

$event = $data['event'];
$payload = $data['payload']['object'];

$meetingId = $payload['id']; // Zoom 會議 ID
$participant = $payload['participant'];
$studentName = $participant['user_name']; // 學生在 Zoom 顯示的名字
$studentEmail = $participant['email'] ?? '未提供'; // 學生 Zoom 帳號的 Email (若有)

// 根據不同的事件進行資料庫操作
switch ($event) {
    case 'meeting.participant_joined':
        $joinTime = $participant['join_time']; // 進入時間 (UTC 格式)
        
        // 💾 【實務操作】在這裡寫入您的 SQL 資料庫
        // 例如：UPDATE attendance SET status = '出席', join_time = :joinTime WHERE meeting_id = :meetingId AND student_name = :studentName
        
        // 測試階段：先寫入本地 log 檔案觀察
        file_put_contents('attendance_log.txt', "[".date('Y-m-d H:i:s')."] 📥 學生 【{$studentName}】 進入教室。會議 ID: {$meetingId}，時間: {$joinTime}\n", FILE_APPEND);
        break;
        
    case 'meeting.participant_left':
        $leaveTime = $participant['leave_time']; // 離開時間 (UTC 格式)
        
        // 💾 【實務操作】在這裡更新資料庫學生的離開時間，並計算總聽課時數
        
        // 測試階段：先寫入本地 log 檔案觀察
        file_put_contents('attendance_log.txt', "[".date('Y-m-d H:i:s')."] 📤 學生 【{$studentName}】 離開教室。會議 ID: {$meetingId}，時間: {$leaveTime}\n", FILE_APPEND);
        break;
}

// 必須回應 200 OK 告知 Zoom 伺服器您已收到，否則 Zoom 會在 48 小時內不斷重發
http_response_code(200);
?>