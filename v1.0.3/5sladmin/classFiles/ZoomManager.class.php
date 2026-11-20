<?php

/**
 * ZoomManager.class.php
 * Zoom API 學院開課系統對接核心類別
 * 
 * 執行環境備忘：
 * - 本地路徑: /Users/jazmini/Documents/_www/5seasonlife/
 * - 內網穿透: https://kangaroo-botch-bonelike.ngrok-free.dev/5seasonlife/webhook.php
 * - ⚠️ 物理狀態警告：若 cURL 收到 HTTP Code 0，請務必完全關閉本地 Mac 的 VPN！
 */

class ZoomUltimateCourseManager {
    private $clientId;
    private $clientSecret;
    private $accountId;
    private $accessToken;

    /**
     * 建構子：初始化憑證並自動獲取第一階段 Access Token
     */
    public function __construct($clientId, $clientSecret, $accountId) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accountId = $accountId;
        $this->accessToken = $this->getAccessToken();
    }

    /**
     * 核心第一階段：獲取 Access Token (依據用戶校正之黃金路徑)
     */
    private function getAccessToken() {
        // 黃金絕對路徑："https://zoom.us" . urlencode($accountId)
        $authUrl = "https://zoom.us/oauth/token?grant_type=account_credentials&account_id=" . urlencode($this->accountId);
        
        $headers = [
            'Authorization: Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            'Content-Type: application/x-www-form-urlencoded'
        ];
        
        $data = ['grant_type' => 'account_credentials'];
        
        // 呼叫內部萬能發送器進行 OAuth 請求
        $response = $this->sendRequest($authUrl, 'POST', $data, $headers);
        $result = json_decode($response, true);
        
        if (isset($result['access_token'])) {
            return $result['access_token'];
        }
        
        throw new Exception("Zoom OAuth 認證失敗。回應內容: " . $response);
    }

    /**
     * 方法 1: sendRequest($absoluteUrl, $method, $data)
     * 通用 cURL 萬能發送器 (含終極防禦與物理狀態除錯機制)
     */
    public function sendRequest($absoluteUrl, $method = 'GET', $data = null, $customHeaders = null) {
        $ch = curl_init();
        
        $method = strtoupper($method);
        curl_setopt($ch, CURLOPT_URL, $absoluteUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // 🛠️ 備忘錄指定：安裝三大防禦機制
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($ch, CURLOPT_POSTREDIR, CURL_REDIR_POST_ALL); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        // 設定標頭
        if ($customHeaders !== null) {
            $headers = $customHeaders;
        } else {
            $headers = [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json'
            ];
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // 處理 HTTP 動作與 Data
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                $payload = is_array($data) && $customHeaders !== null ? http_build_query($data) : json_encode($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            }
        } elseif ($method === 'PUT' || $method === 'PATCH' || $method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } else {
            if ($data !== null && is_array($data)) {
                $query = http_build_query($data);
                curl_setopt($ch, CURLOPT_URL, $absoluteUrl . '?' . $query);
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // ⚠️ 終極除錯結論：處理 HTTP 狀態碼 0 的封包掐斷
        // ⚠️ 終極除錯結論：處理 HTTP 狀態碼 0 的網路層掐斷
        if ($httpCode === 0) {
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // 自動判斷是否在您的 Mac 本地環境，給予最精準的提示
            $isLocal = (strpos($_SERVER['HTTP_HOST'] ?? '', 'ngrok') !== false || strpos($_SERVER['DOCUMENT_ROOT'] ?? '', '/Users/jazmini/') !== false);
            
            if ($isLocal) {
                throw new Exception("【網路層錯誤】HTTP 狀態碼為 0！在您的 Mac 本地測試環境中，這百分之百是因為開啟了 VPN 導致封包被掐斷，請完全關閉 VPN 後再試。cURL 錯誤: " . $curlError);
            } else {
                throw new Exception("【正式環境網路中斷】HTTP 狀態碼為 0！封包在主機外發層被掐斷。請檢查正式主機的內部防火牆政策、Egress Proxy 設定，或確認伺服器是否無法解析 zoom.us 的 DNS。cURL 錯誤: " . $curlError);
            }
        }        
        curl_close($ch);
        return $response;
    }

    /**
     * 方法 2: createCourse($topic, $type, $options, $isSecured)
     * 建立四種型態教室（含安全限制模式開關）
     */
    public function createCourse($topic, $type = 2, $options = [], $isSecured = true) {
        // 預設管理者 ID 為 'me'
        if (!isset($options['admin_user_id'])) {
            $options['admin_user_id'] = 'me';
        }

        // 第二階段黃金絕對路徑：'https://api.zoom.us/v2/users/' . trim($options['admin_user_id']) . '/meetings'
        $url = 'https://api.zoom.us/v2/users/' . trim($options['admin_user_id']) . '/meetings';

        // 基本開課參數設定 
        $body = [
            'topic' => $topic,
            'type' => $type,    //(1: 即時課程, 2: 預約課程, 3: 週期性無固定時間課程, 8: 週期性固定時間課程)
            'start_time' => isset($options['start_time']) ? $options['start_time'] : date(DATE_ISO8601, strtotime('+1 hour')),//可以改成date('Y-m-d\TH:i:s', strtotime('+1 hour'))
            'duration' => isset($options['duration']) ? (int)$options['duration'] : 60,
            'timezone' => isset($options['timezone']) ? $options['timezone'] : 'Asia/Taipei',
            'settings' => [
                'join_before_host' => false,//決定與會者是否能在主持人正式上線開會前，就先進入會議室互動或等待。true|false
                //'jbh_time' => 5, //指定允許提前加入的時間，如：5 分鐘
                'mute_upon_entry' => true, //控制與會者剛進入會議室時的初始麥克風狀態，常用於管理大型會議以避免雜音干擾。true|false
                'watermark' => false,//安全與防洩密功能。開啟後，系統會在與會者觀看的共享螢幕畫面或視訊畫面上，強制加上該與會者自身電子郵件地址的隱約浮水印，防止會議內容被拍照或錄影外洩。true|false
                'approval_type' => 0 //針對需要預先註冊（Registration）的會議，設定與會者在填寫註冊表單後的審核機制。0 為(自動審批（拿到專屬網址必備） / Automatically Approve)：與會者填寫完註冊表單後，系統立刻自動核准，並自動寄發會議加入連結的郵件;1 為(手動審批 / Manually Approve)：與會者註冊後會進入等待名單，必須由主持人或管理員在後台點選「核准」後，與會者才會收到包含加入連結的郵件；2 為(無需註冊 / No Registration Required)：此會議不需要任何註冊流程，與會者只需有點連結或會議 ID 即可直接加入。
            ]
        ];

        // 依據 $isSecured 安全限制模式開關，動態強化教室防護
        if ($isSecured) {
            $body['settings']['password'] = substr(md5(uniqid(mt_rand(), true)), 0, 8); // 強制隨機密碼
            $body['settings']['waiting_room'] = true; // 開啟等候室
            $body['settings']['meeting_authentication'] = true; // 要求登入 Zoom 帳號才能加入
        } else {
            $body['settings']['waiting_room'] = false;
            $body['settings']['meeting_authentication'] = false;
        }

        $response = $this->sendRequest($url, 'POST', $body);
        return json_decode($response, true);
    }

    /**
     * 方法 3: addStudentToCourse($meetingId, $email, $firstName, $lastName)
     * 基本學員加入白名單，獲取專屬連結
     */
    public function addStudentToCourse($meetingId, $email, $firstName, $lastName) {
        //$url = "https://zoom.us" . trim($meetingId) . "/registrants";
        $url = "https://api.zoom.us/v2/meetings/" . trim($meetingId) . "/registrants";
        
        $body = [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName
        ];

        $response = $this->sendRequest($url, 'POST', $body);
        return json_decode($response, true);
    }

    /**
     * 方法 4: addStudentToCourseWithCustomId($meetingId, $email, $firstName, $lastName, $studentId)
     * 透傳本地 student_id 給自訂欄位，供 Webhook 回傳使用
     */
    public function addStudentToCourseWithCustomId($meetingId, $email, $firstName, $lastName, $studentId) {
        $url = "https://api.zoom.us/v2/meetings/" . trim($meetingId) . "/registrants";
        
        $body = [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'custom_questions' => [
                [
                    'title' => 'student_id',
                    'value' => (string)$studentId
                ]
            ]
        ];

        $response = $this->sendRequest($url, 'POST', $body);
        return json_decode($response, true);
    }

    /**
     * 方法 5: getAttendanceReport($meetingId)
     * 撈取課後學員出席實時名單與聽課時長 
     */
    public function getAttendanceReport($meetingId) {
        //$url = "https://zoom.us" . trim($meetingId) . "/participants";
        $url = "https://api.zoom.us/v2/report/meetings/" . trim($meetingId) . "/participants";
        // 預設撈取最大一頁 300 筆資料
        $data = ['page_size' => 300];
        
        $response = $this->sendRequest($url, 'GET', $data);
        return json_decode($response, true);
    }

    /**
     * 方法 6: getQAReport($meetingId)
     * 撈取課堂問答（Q&A）數據
     */
    public function getQAReport($meetingId) {
        //$url = "https://zoom.us" . trim($meetingId) . "/qa";
        $url = "https://api.zoom.us/v2/report/meetings/" . trim($meetingId) . "/qa";
        $response = $this->sendRequest($url, 'GET');
        return json_decode($response, true);
    }

    /**
     * 方法 7: getPollReport($meetingId)
     * 撈取課堂投票結果
     */
    public function getPollReport($meetingId) {
        //$url = "https://zoom.us" . trim($meetingId) . "/polls";
        $url = "https://api.zoom.us/v2/report/meetings/" . trim($meetingId) . "/polls";
        $response = $this->sendRequest($url, 'GET');
        return json_decode($response, true);
    }

    /**
     * 方法 8: getCloudRecordings($meetingId)
     * 撈取雲端錄影檔案與白板（Whiteboard）板書圖檔快照網址
     */
    public function getCloudRecordings($meetingId) {
        //$url = "https://zoom.us" . trim($meetingId) . "/recordings";
        $url = "https://api.zoom.us/v2/meetings/" . trim($meetingId) . "/recordings";
        
        $response = $this->sendRequest($url, 'GET');
        return json_decode($response, true);
    }
}

?>


