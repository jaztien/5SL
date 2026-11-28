<?php
/**
 * Fifth Season Life 人生第五季 - Zoom 專業版 (Pro) Server-to-Server OAuth API 完整接口庫
 * 支援功能：建立會議室、更改時間/參數、刪除會議室、查詢會議資訊、參加者預先註冊、取得會後出席名單
 */

// =========================================================================
// Zoom App 憑證設定區（請於 Zoom Marketplace 建立 Server-to-Server OAuth App 後填入）
// 所需 Scopes：meeting:write:admin, meeting:read:admin, report:read:admin (或 user:read:admin)
// =========================================================================
define('ZOOM_ACCOUNT_ID',    ''); // 必填：Zoom Account ID
define('ZOOM_CLIENT_ID',     ''); // 必填：Zoom Client ID
define('ZOOM_CLIENT_SECRET', ''); // 必填：Zoom Client Secret
define('ZOOM_HOST_EMAIL',    'me'); // 主持人帳號（預設 'me' 代表 App 擁有者，或填指定授權主持人 Email）
define('ZOOM_DRY_RUN',       true); // 尚未對接真實金鑰前設為 true（模擬測試模式）；正式上線改為 false

/**
 * 內部共用：取得 Zoom Server-to-Server OAuth Access Token
 */
function _fn_zoom_get_access_token(): array {
  static $cachedToken = null;
  if ($cachedToken !== null) {
    return ['ok' => true, 'access_token' => $cachedToken];
  }

  if (ZOOM_ACCOUNT_ID === '' || ZOOM_CLIENT_ID === '' || ZOOM_CLIENT_SECRET === '') {
    return ['ok' => false, 'msg' => '尚未設定 Zoom Account ID / Client ID / Client Secret 憑證！'];
  }

  $url = 'https://zoom.us' . urlencode(ZOOM_ACCOUNT_ID);
  $authHeader = 'Authorization: Basic ' . base64_encode(ZOOM_CLIENT_ID . ':' . ZOOM_CLIENT_SECRET);

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => [$authHeader, 'Content-Type: application/x-www-form-urlencoded']
  ]);
  $resp = curl_exec($ch);
  $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err  = curl_error($ch);
  curl_close($ch);

  if ($resp === false) {
    return ['ok' => false, 'msg' => 'Zoom OAuth 連線失敗：' . $err];
  }
  $data = json_decode($resp, true) ?: [];
  if ($code !== 200 || empty($data['access_token'])) {
    return ['ok' => false, 'msg' => 'Zoom OAuth 授權失敗（HTTP ' . $code . '）：' . ($data['reason'] ?? $data['message'] ?? $resp)];
  }

  $cachedToken = $data['access_token'];
  return ['ok' => true, 'access_token' => $cachedToken];
}

/**
 * 內部共用：統一發送 Zoom REST API v2 請求
 */
function _fn_zoom_request(string $method, string $endpoint, ?array $payload = null): array {
  $auth = _fn_zoom_get_access_token();
  if (!$auth['ok']) {
    return ['ok' => false, 'code' => 0, 'msg' => $auth['msg'], 'data' => []];
  }

  $url = 'https://zoom.us' . ltrim($endpoint, '/');
  $headers = [
    'Authorization: Bearer ' . $auth['access_token'],
    'Content-Type: application/json'
  ];

  $ch = curl_init($url);
  $opts = [
    CURLOPT_CUSTOMREQUEST  => strtoupper($method),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_HTTPHEADER     => $headers
  ];
  if ($payload !== null) {
    $opts[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_UNICODE);
  }
  curl_setopt_array($ch, $opts);

  $resp = curl_exec($ch);
  $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err  = curl_error($ch);
  curl_close($ch);

  if ($resp === false) {
    return ['ok' => false, 'code' => 0, 'msg' => 'Zoom API 連線失敗：' . $err, 'data' => []];
  }

  $data = ($resp !== '') ? (json_decode($resp, true) ?: []) : [];
  $isOk = ($code >= 200 && $code < 300);

  return [
    'ok'   => $isOk,
    'code' => $code,
    'msg'  => $isOk ? 'OK' : ($data['message'] ?? "Zoom API 錯誤 (HTTP {$code})"),
    'data' => $data
  ];
}

/**
 * 1. 建立 Zoom 線上諮詢/課程會議室
 */
function _fn_zoom_create_meeting(string $topic, string $startAt, int $duration = 60, string $agenda = '', bool $enableRegistration = false): array {
  if (ZOOM_DRY_RUN || ZOOM_ACCOUNT_ID === '' || ZOOM_CLIENT_ID === '' || ZOOM_CLIENT_SECRET === '') {
    $mockId  = (string)random_int(81000000000, 89999999999);
    $mockPwd = (string)random_int(100000, 999999);
    return [
      'ok'         => true,
      'is_mock'    => true,
      'meeting_id' => $mockId,
      'password'   => $mockPwd,
      'join_url'   => "https://zoom.us{$mockId}?pwd=TEST{$mockPwd}",
      'start_url'  => "https://zoom.us{$mockId}?zak=TEST_HOST_TOKEN",
      'msg'        => '已建立 Zoom 測試會議室（目前為 ZOOM_DRY_RUN 模擬模式）'
    ];
  }

  $ts = strtotime($startAt) ?: time();
  $settings = [
    'host_video'        => true,
    'participant_video' => true,
    'waiting_room'      => isset($extraSettings['waiting_room']) ? (bool)$extraSettings['waiting_room'] : true,
    'join_before_host'  => isset($extraSettings['join_before_host']) ? (bool)$extraSettings['join_before_host'] : false,
    'jbh_time'          => (int)($extraSettings['jbh_time'] ?? 10),
    'watermark'         => isset($extraSettings['watermark']) ? (bool)$extraSettings['watermark'] : false,
    'mute_upon_entry'   => isset($extraSettings['mute_upon_entry']) ? (bool)$extraSettings['mute_upon_entry'] : true,
    'auto_recording'    => 'none'
  ];
  if ($enableRegistration || !empty($extraSettings['approval_type'])) {
    $settings['approval_type']     = 0; // 0 = 自動核准預先註冊並產生個人專屬加入連結
    $settings['registration_type'] = 1;
  }

  $payload = [
    'topic'      => mb_substr($topic, 0, 180),
    'type'       => 2,
    'start_time' => date('Y-m-d\TH:i:s', $ts),
    'duration'   => max(15, $duration),
    'timezone'   => 'Asia/Taipei',
    'agenda'     => mb_substr($agenda, 0, 1000),
    'settings'   => $settings
  ];

  $hostUser = urlencode(ZOOM_HOST_EMAIL ?: 'me');
  $res = _fn_zoom_request('POST', "users/{$hostUser}/meetings", $payload);

  if (!$res['ok'] || empty($res['data']['id'])) {
    return ['ok' => false, 'msg' => $res['msg']];
  }

  return [
    'ok'         => true,
    'is_mock'    => false,
    'meeting_id' => (string)$res['data']['id'],
    'password'   => (string)($res['data']['password'] ?? ''),
    'join_url'   => (string)($res['data']['join_url'] ?? ''),
    'start_url'  => (string)($res['data']['start_url'] ?? ''),
    'msg'        => 'Zoom 線上諮詢會議室已成功建立！'
  ];
}

/**
 * 2. 遠端更改 Zoom 會議室時間、時長或主題 (PATCH /v2/meetings/{meetingId})
 */
function _fn_zoom_update_meeting(string $meetingId, array $fields): array {
  $meetingId = trim($meetingId);
  if ($meetingId === '') {
    return ['ok' => false, 'msg' => '缺少 Zoom 會議室 ID'];
  }
  if (ZOOM_DRY_RUN || ZOOM_ACCOUNT_ID === '') {
    return ['ok' => true, 'is_mock' => true, 'msg' => "已同步更新 Zoom 測試會議室 ({$meetingId}) 時間與設定"];
  }

  $payload = [];
  if (!empty($fields['topic']))      $payload['topic']      = mb_substr((string)$fields['topic'], 0, 180);
  if (!empty($fields['start_time'])) $payload['start_time'] = date('Y-m-d\TH:i:s', strtotime((string)$fields['start_time']));
  if (!empty($fields['duration']))   $payload['duration']   = max(15, (int)$fields['duration']);
  if (isset($fields['agenda']))      $payload['agenda']     = mb_substr((string)$fields['agenda'], 0, 1000);
  $payload['timezone'] = 'Asia/Taipei';

  $res = _fn_zoom_request('PATCH', 'meetings/' . urlencode($meetingId), $payload);
  return [
    'ok'  => $res['ok'],
    'msg' => $res['ok'] ? 'Zoom 會議室時間與資訊已同步更新！' : $res['msg']
  ];
}

/**
 * 3. 遠端刪除 / 取消 Zoom 會議室 (DELETE /v2/meetings/{meetingId})
 */
function _fn_zoom_delete_meeting(string $meetingId): array {
  $meetingId = trim($meetingId);
  if ($meetingId === '') {
    return ['ok' => true, 'msg' => '無需刪除（未建立 Zoom 會議室）'];
  }
  if (ZOOM_DRY_RUN || ZOOM_ACCOUNT_ID === '') {
    return ['ok' => true, 'is_mock' => true, 'msg' => "已刪除 Zoom 測試會議室 ({$meetingId})"];
  }

  $res = _fn_zoom_request('DELETE', 'meetings/' . urlencode($meetingId));
  // HTTP 204 (已刪除) 或 404 (雲端已不存在) 皆視為清理完成
  $ok = ($res['ok'] || $res['code'] === 404);
  return [
    'ok'  => $ok,
    'msg' => $ok ? 'Zoom 雲端會議室已刪除！' : $res['msg']
  ];
}

/**
 * 4. 取得 Zoom 會議室最新資訊與狀態 (GET /v2/meetings/{meetingId})
 */
function _fn_zoom_get_meeting(string $meetingId): array {
  $meetingId = trim($meetingId);
  if ($meetingId === '') {
    return ['ok' => false, 'msg' => '缺少 Zoom 會議室 ID', 'data' => []];
  }
  if (ZOOM_DRY_RUN || ZOOM_ACCOUNT_ID === '') {
    return [
      'ok'   => true,
      'msg'  => 'OK (Mock)',
      'data' => [
        'id'         => $meetingId,
        'status'     => 'waiting',
        'topic'      => '一對一顧問諮詢測試會議',
        'start_time' => date('Y-m-d\TH:i:s'),
        'duration'   => 60,
        'join_url'   => "https://zoom.us{$meetingId}"
      ]
    ];
  }

  $res = _fn_zoom_request('GET', 'meetings/' . urlencode($meetingId));
  return [
    'ok'   => $res['ok'],
    'msg'  => $res['msg'],
    'data' => $res['data']
  ];
}

/**
 * 5. 參加者管理：新增註冊參加者並取得個人專屬加入連結 (POST /v2/meetings/{meetingId}/registrants)
 */
function _fn_zoom_add_registrant(string $meetingId, string $email, string $firstName, string $lastName = ''): array {
  $meetingId = trim($meetingId);
  if ($meetingId === '' || $email === '') {
    return ['ok' => false, 'msg' => '缺少會議室 ID 或參加者 Email'];
  }
  if (ZOOM_DRY_RUN || ZOOM_ACCOUNT_ID === '') {
    $mockRegId = 'REG_' . substr(md5($email . $meetingId), 0, 10);
    return [
      'ok'            => true,
      'registrant_id' => $mockRegId,
      'join_url'      => "https://zoom.us{$meetingId}?tk={$mockRegId}",
      'msg'           => '已將會員加入 Zoom 參加者名單（模擬模式）'
    ];
  }

  $payload = [
    'email'      => $email,
    'first_name' => $firstName ?: '學員',
    'last_name'  => $lastName ?: '會員'
  ];
  $res = _fn_zoom_request('POST', 'meetings/' . urlencode($meetingId) . '/registrants', $payload);
  if (!$res['ok']) {
    return ['ok' => false, 'msg' => $res['msg']];
  }

  return [
    'ok'            => true,
    'registrant_id' => (string)($res['data']['registrant_id'] ?? ''),
    'join_url'      => (string)($res['data']['join_url'] ?? ''),
    'msg'           => '已成功註冊參加者並取得專屬加入連結！'
  ];
}

/**
 * 6. 課堂/諮詢出席稽核：取得已結束會議的實際參加者與停留時間 (GET /v2/past_meetings/{meetingId}/participants)
 */
function _fn_zoom_get_past_participants(string $meetingId): array {
  $meetingId = trim($meetingId);
  if ($meetingId === '') {
    return ['ok' => false, 'msg' => '缺少會議室 ID', 'participants' => []];
  }
  if (ZOOM_DRY_RUN || ZOOM_ACCOUNT_ID === '') {
    return [
      'ok'           => true,
      'msg'          => '已取得模擬出席名單',
      'participants' => [
        [
          'name'       => '測試學員',
          'user_email' => 'member@example.com',
          'join_time'  => date('Y-m-d\TH:i:s', strtotime('-55 minutes')),
          'leave_time' => date('Y-m-d\TH:i:s', strtotime('-2 minutes')),
          'duration'   => 3180 // 秒數（53 分鐘）
        ]
      ]
    ];
  }

  $res = _fn_zoom_request('GET', 'past_meetings/' . urlencode($meetingId) . '/participants?page_size=100');
  if (!$res['ok']) {
    return ['ok' => false, 'msg' => $res['msg'], 'participants' => []];
  }

  return [
    'ok'           => true,
    'msg'          => 'OK',
    'participants' => $res['data']['participants'] ?? []
  ];
}
