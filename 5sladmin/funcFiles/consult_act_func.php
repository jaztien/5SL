<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
require_once('../includeFiles/zoom_api.inc.php');

header('Content-Type: application/json; charset=utf-8');

$adId = (int)($_SESSION['SESS_AD_ID'] ?? 0);
$mbId = (int)($_SESSION['SESS_MB_ID'] ?? $_SESSION['mb_id'] ?? 0);

if ($adId <= 0 && $mbId <= 0) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '登入已逾時，請重新登入系統！']);
  exit();
}

$token    = $_POST['token'] ?? '';
$formAcce = trim((string)($_POST['formAcce'] ?? ''));
$schID    = (int)($_POST['schID'] ?? 0);
$bkgID    = (int)($_POST['bkgID'] ?? 0);

if ($adId > 0 && function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面！']);
  exit();
}

/** 產生不重複預約單號 BKG-YYYYMMDD-XXXX */
function _fn_gen_booking_no(): string {
  $ymd    = date('Ymd');
  $prefix = "BKG-{$ymd}-";
  $cnt    = (int)DB->single("SELECT COUNT(*) FROM fsl_consult_bookings WHERE bkg_no LIKE ?", [$prefix . '%']);
  return $prefix . str_pad((string)($cnt + 1), 4, '0', STR_PAD_LEFT);
}

/** 通用比較運算子評估 */
function _fn_eval_rule_op($actual, string $op, string $ruleVal): bool {
  $op = strtoupper(trim($op));
  if ($op === 'IN' || $op === 'NOT_IN') {
    $list = array_map('trim', explode(',', $ruleVal));
    $hit  = in_array((string)$actual, $list, true);
    return ($op === 'IN') ? $hit : !$hit;
  }
  if ($op === 'BETWEEN') {
    $parts = array_map('trim', explode(',', $ruleVal));
    if (count($parts) < 2) return true;
    return ($actual >= $parts[0] && $actual <= $parts[1]);
  }
  if ($op === '=')  return ((string)$actual === trim($ruleVal));
  if ($op === '!=') return ((string)$actual !== trim($ruleVal));
  if ($op === '>=') return ((float)$actual >= (float)$ruleVal);
  if ($op === '<=') return ((float)$actual <= (float)$ruleVal);
  return true;
}

/**
 * 檢查會員是否符合指定諮詢時段之 fsl_eligibility_rules (erule_target_type = 4) 資格條件
 */
function _fn_check_consult_eligibility(int $mbId, int $schId): array {
  $rules = DB->query("
    SELECT * FROM fsl_eligibility_rules
    WHERE erule_target_type = 4 AND erule_target_id = ? AND erule_status = 1
    ORDER BY erule_sort ASC, erule_id ASC
  ", [$schId]) ?: [];

  if (empty($rules)) {
    return ['ok' => true, 'msg' => '符合預約資格'];
  }

  $mb = DB->row("SELECT * FROM fsl_members WHERE mb_id = ? AND mb_status = 1", [$mbId]);
  if (!$mb) {
    return ['ok' => false, 'msg' => '會員帳號不存在或尚未啟用！'];
  }

  $totalSpent = null;
  $nowStr     = date('Y-m-d H:i:s');

  foreach ($rules as $r) {
    $type = trim((string)$r['erule_type']);
    $op   = (string)$r['erule_operator'];
    $val  = (string)$r['erule_val'];
    $fail = (string)($r['erule_fail_msg'] ?: '您目前尚未符合此時段的預約資格條件');
    $pass = true;

    switch ($type) {
      case 'time_range':
        $pass = _fn_eval_rule_op($nowStr, 'BETWEEN', $val);
        break;
      case 'mb_level':
        $pass = _fn_eval_rule_op((int)($mb['mb_level'] ?? 1), $op, $val);
        break;
      case 'gender':
        $pass = _fn_eval_rule_op((string)($mb['mb_gender'] ?? ''), $op, $val);
        break;
      case 'city':
        $pass = _fn_eval_rule_op((string)($mb['mb_city'] ?? ''), $op, $val);
        break;
      case 'birthday_month':
        $bMonth = !empty($mb['mb_birth']) ? date('m', strtotime($mb['mb_birth'])) : '';
        $pass = ($bMonth !== '') && _fn_eval_rule_op($bMonth, $op, $val);
        break;
      case 'total_spent':
        if ($totalSpent === null) {
          $totalSpent = (float)DB->single("SELECT COALESCE(SUM(ord_total), 0) FROM fsl_orders WHERE mb_id = ? AND ord_status IN (2,3,4)", [$mbId]);
        }
        $pass = _fn_eval_rule_op($totalSpent, $op, $val);
        break;
    }

    if (!$pass) {
      return ['ok' => false, 'msg' => $fail];
    }
  }

  return ['ok' => true, 'msg' => '符合預約資格'];
}

/** 安全寄送通知信件（呼叫系統既有的寄信函式） */
function _fn_consult_send_mail(string $toEmail, string $subject, string $htmlBody): void {
  if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) return;
  if (function_exists('_fn_sendMail')) {
    @_fn_sendMail($toEmail, $subject, $htmlBody);
  }
}

switch ($formAcce) {

  // =========================================================================
  // 1. 管理者新增開放一對一諮詢時段（獨立寫入 fsl_schedules, sch_source=3）
  // =========================================================================
  case 'func_consult_slot_create':
    if ($adId <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無後台操作權限！']);
      exit();
    }

    $title     = trim((string)($_POST['sch_title'] ?? '一對一專屬顧問諮詢'));
    $startDate = trim((string)($_POST['start_date'] ?? ''));
    $startTime = trim((string)($_POST['start_time'] ?? ''));
    $duration  = max(15, min(240, (int)($_POST['duration'] ?? 60)));
    $hostAdId  = (int)($_POST['host_ad_id'] ?? $adId);
    $color     = trim((string)($_POST['sch_color'] ?? '#8b5cf6')) ?: '#8b5cf6';
    $memo      = trim((string)($_POST['sch_msg_body'] ?? ''));

    // 選填：同步建立資格條件規則 (erule_target_type = 4)
    $ruleLevel = trim((string)($_POST['rule_mb_level'] ?? ''));
    $ruleSpent = trim((string)($_POST['rule_total_spent'] ?? ''));
    $ruleFail  = trim((string)($_POST['rule_fail_msg'] ?? '此時段僅限指定會員等級或累計消費達標學員預約。'));

    if ($startDate === '' || $startTime === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫諮詢日期與開始時間！']);
      exit();
    }

    $startAt = "{$startDate} {$startTime}:00";
    $endAt   = date('Y-m-d H:i:s', strtotime($startAt . " +{$duration} minutes"));

    DB->insert('fsl_schedules', [
      'sch_source'         => 3, // 3 = 一對一顧問諮詢開放時段
      'sch_category'       => 'consult',
      'sch_title'          => $title,
      'sch_color'          => $color,
      'sch_start_at'       => $startAt,
      'sch_end_at'         => $endAt,
      'sch_all_day'        => 0,
      'sch_trigger_enable' => 0, // 尚未審核確認人選前不觸發通知
      'sch_target_type'    => 4,
      'sch_target_ids'     => (string)$hostAdId, // 暫存負責顧問 ad_id
      'sch_channels'       => 'noti,email',
      'sch_msg_title'      => "【一對一諮詢提醒】{$title}",
      'sch_msg_body'       => $memo,
      'sch_exec_status'    => 0
    ]);
    $newSchId = (int)DB->lastInsertId();

    // 若有設定資格條件，寫入 fsl_eligibility_rules (erule_target_type = 4)
    if ($ruleLevel !== '') {
      DB->insert('fsl_eligibility_rules', [
        'erule_target_type' => 4,
        'erule_target_id'   => $newSchId,
        'erule_type'        => 'mb_level',
        'erule_operator'    => 'IN',
        'erule_val'         => $ruleLevel,
        'erule_fail_msg'    => $ruleFail,
        'erule_sort'        => 10,
        'erule_status'      => 1
      ]);
    }
    if ($ruleSpent !== '' && (float)$ruleSpent > 0) {
      DB->insert('fsl_eligibility_rules', [
        'erule_target_type' => 4,
        'erule_target_id'   => $newSchId,
        'erule_type'        => 'total_spent',
        'erule_operator'    => '>=',
        'erule_val'         => $ruleSpent,
        'erule_fail_msg'    => $ruleFail,
        'erule_sort'        => 20,
        'erule_status'      => 1
      ]);
    }

    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '開放預約諮詢時段已建立，符合資格的會員現已可於前台申請！', 'newID' => $newSchId]);
    exit();
  break;

  // =========================================================================
  // 2. 前台會員提出一對一諮詢預約申請（未確認前仍開放他人申請，並即時通知管理人）
  // =========================================================================
  case 'func_consult_apply':
    if ($mbId <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '請先登入會員帳號！']);
      exit();
    }
    $topic = trim((string)($_POST['bkg_topic'] ?? ''));
    $note  = trim((string)($_POST['bkg_note'] ?? ''));

    if ($schID <= 0 || $topic === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請選擇諮詢時段並填寫您想諮詢的主題！']);
      exit();
    }

    // 確認時段存在且尚未確定人選
    $slot = DB->row("SELECT * FROM fsl_schedules WHERE sch_id = ? AND sch_source = 3 AND sch_exec_status = 0", [$schID]);
    if (!$slot) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '此時段不存在或已關閉預約！']);
      exit();
    }
    $confirmedCnt = (int)DB->single("SELECT COUNT(*) FROM fsl_consult_bookings WHERE sch_id = ? AND bkg_status = 1", [$schID]);
    if ($confirmedCnt > 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '此時段剛剛已由顧問確認預約人選，請改選其他開放時段！']);
      exit();
    }

    // 檢查會員資格條件 (fsl_eligibility_rules)
    $elig = _fn_check_consult_eligibility($mbId, $schID);
    if (!$elig['ok']) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => $elig['msg']]);
      exit();
    }

    // 檢查是否重複申請同一時段
    $dup = (int)DB->single("SELECT COUNT(*) FROM fsl_consult_bookings WHERE sch_id = ? AND mb_id = ? AND bkg_status IN (0,1)", [$schID, $mbId]);
    if ($dup > 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'info', 'msgStr' => '您已送出過此時段的預約申請，請靜候顧問審核通知！']);
      exit();
    }

    $hostAdId = (int)($slot['sch_target_ids'] ?? 0);
    $bkgNo    = _fn_gen_booking_no();

    DB->insert('fsl_consult_bookings', [
      'bkg_no'     => $bkgNo,
      'sch_id'     => $schID,
      'mb_id'      => $mbId,
      'host_ad_id' => ($hostAdId > 0 ? $hostAdId : null),
      'bkg_topic'  => $topic,
      'bkg_note'   => ($note !== '' ? $note : null),
      'bkg_status' => 0
    ]);

    // 取得會員與負責管理員資料，發送通知與信件給負責管理人
    $mbRow   = DB->row("SELECT mb_name, mb_email FROM fsl_members WHERE mb_id = ?", [$mbId]);
    $mbName  = _fn_safe_dec_name($mbRow['mb_name'] ?? '') ?: ("會員 #{$mbId}");
    $slotStr = date('Y-m-d H:i', strtotime($slot['sch_start_at']));

    if ($hostAdId > 0) {
      $admRow = DB->row("SELECT ad_name, ad_email FROM fsl_administrator WHERE ad_id = ?", [$hostAdId]);
      if ($admRow && !empty($admRow['ad_email'])) {
        $mailSub  = "【一對一諮詢新預約申請】{$mbName} 申請預約 {$slotStr}";
        $mailBody = "<p>您好 {$admRow['ad_name']}：</p>"
                  . "<p>會員 <strong>{$mbName}</strong> 剛剛提出了「一對一顧問諮詢」預約申請：</p>"
                  . "<ul><li>申請單號：{$bkgNo}</li><li>期望時段：{$slotStr}</li><li>諮詢主題：{$topic}</li></ul>"
                  . "<p>請登入後台「預約排程申請審核」決定最終歸屬並自動建立 Zoom 會議室。</p>";
        _fn_consult_send_mail((string)$admRow['ad_email'], $mailSub, $mailBody);
      }
    }

    // 寫入會員端操作紀錄 (9-901)
    if (function_exists('_fn_write_op_log')) {
      _fn_write_op_log(9, 901, [$bkgNo, '一對一顧問諮詢預約', "{$slotStr} - {$topic}"]);
    }

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => "預約申請【{$bkgNo}】已送出！在顧問審核確認最終歸屬後，系統將立即發送通知與 Zoom 會議室連結給您。",
      'bkg_no'  => $bkgNo
    ]);
    exit();
  break;

  // =========================================================================
  // 3. 管理者審核確認某筆預約申請 -> 自動建 Zoom 房 + 登載正式排程 + 通知會員
  // =========================================================================
  case 'func_consult_approve':
    if ($adId <= 0 || $bkgID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的操作權限或預約 ID！']);
      exit();
    }

    $adminMemo = trim((string)($_POST['bkg_admin_memo'] ?? ''));

    $bkg = DB->row("
      SELECT b.*, s.sch_title, s.sch_start_at, s.sch_end_at, s.sch_msg_body,
             m.mb_name, m.mb_email
      FROM fsl_consult_bookings b
      INNER JOIN fsl_schedules s ON b.sch_id = s.sch_id
      LEFT JOIN fsl_members m ON b.mb_id = m.mb_id
      WHERE b.bkg_id = ?
    ", [$bkgID]);

    if (!$bkg) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到指定的預約申請紀錄！']);
      exit();
    }
    if ((int)$bkg['bkg_status'] === 1) {
      echo json_encode(['RTN' => 0, 'msgType' => 'info', 'msgStr' => '此筆預約申請已經核准確認過了！']);
      exit();
    }

    $schId      = (int)$bkg['sch_id'];
    $targetMbId = (int)$bkg['mb_id'];
    $mbName     = _fn_safe_dec_name($bkg['mb_name'] ?? '') ?: ("會員 #{$targetMbId}");
    $mbEmail    = _fn_safe_dec_name($bkg['mb_email'] ?? '');

    // 檢查該時段是否已確認過其他會員
    $alreadyApproved = (int)DB->single("SELECT COUNT(*) FROM fsl_consult_bookings WHERE sch_id = ? AND bkg_status = 1 AND bkg_id != ?", [$schId, $bkgID]);
    if ($alreadyApproved > 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '此時段已經確認過其他會員，若要改派請先取消原確認紀錄！']);
      exit();
    }

    // 1. 計算時長並呼叫 Zoom API 建立專屬會議室
    $startTs  = strtotime($bkg['sch_start_at']);
    $endTs    = !empty($bkg['sch_end_at']) ? strtotime($bkg['sch_end_at']) : ($startTs + 3600);
    $duration = max(15, (int)round(($endTs - $startTs) / 60));

    $zoomTopic  = "【人生第五季】一對一顧問諮詢 - {$mbName} ({$bkg['bkg_topic']})";
    $zoomAgenda = "預約單號：{$bkg['bkg_no']}\n諮詢主題：{$bkg['bkg_topic']}\n學員備註：" . ($bkg['bkg_note'] ?? '無');

    $zoomRes = _fn_zoom_create_meeting($zoomTopic, $bkg['sch_start_at'], $duration, $zoomAgenda);
    if (!$zoomRes['ok']) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => 'Zoom 會議室建立失敗：' . $zoomRes['msg']]);
      exit();
    }

    $nowStr = date('Y-m-d H:i:s');

    // 2. 更新中選的預約申請紀錄為「1-已核准確認」
    DB->update('fsl_consult_bookings', [
      'bkg_status'      => 1,
      'bkg_admin_memo'  => ($adminMemo !== '' ? $adminMemo : null),
      'zoom_meeting_id' => $zoomRes['meeting_id'],
      'zoom_password'   => $zoomRes['password'],
      'zoom_join_url'   => $zoomRes['join_url'],
      'zoom_start_url'  => $zoomRes['start_url'],
      'reviewed_by'     => $adId,
      'reviewed_at'     => $nowStr
    ], ['bkg_id' => $bkgID]);

    // 3. 將同一開放時段的其他待審申請標記為「2-未中選」並發送站內通知提醒改約
    $otherBkgs = DB->query("SELECT bkg_id, mb_id, bkg_no FROM fsl_consult_bookings WHERE sch_id = ? AND bkg_id != ? AND bkg_status = 0", [$schId, $bkgID]) ?: [];
    if (!empty($otherBkgs)) {
      DB->query("UPDATE fsl_consult_bookings SET bkg_status = 2, reviewed_by = ?, reviewed_at = ? WHERE sch_id = ? AND bkg_id != ? AND bkg_status = 0", [$adId, $nowStr, $schId, $bkgID]);
      foreach ($otherBkgs as $ob) {
        try {
          DB->insert('fsl_notifications', [
            'mb_id'      => (int)$ob['mb_id'],
            'noti_type'  => 'consult',
            'noti_title' => "一對一諮詢時段預約結果通知（{$ob['bkg_no']}）",
            'noti_body'  => "您申請的 " . date('Y-m-d H:i', $startTs) . " 諮詢時段已優先安排給其他學員，歡迎至會員中心改約其他開放時段！",
            'noti_link'  => "member_consult.php"
          ]);
        } catch (Throwable $e) {}
      }
    }

    // 4. 正式登載進 fsl_schedules 排程表（納入日曆正式排程 + 啟用諮詢前1小時自動提醒）
    $trigTime = date('Y-m-d H:i:s', max(time() + 60, $startTs - 3600));
    $schedBody = "【已確認一對一顧問諮詢】\n"
               . "學員姓名：{$mbName}\n"
               . "諮詢主題：{$bkg['bkg_topic']}\n"
               . "Zoom 會議室 ID：{$zoomRes['meeting_id']}\n"
               . "會議室密碼：{$zoomRes['password']}\n"
               . "學員專屬加入連結：{$zoomRes['join_url']}";

    DB->update('fsl_schedules', [
      'sch_title'          => "✅ [已確認諮詢] {$mbName} - {$bkg['bkg_topic']}",
      'sch_color'          => '#10b981',
      'sch_target_type'    => 1,
      'sch_target_ids'     => (string)$targetMbId,
      'sch_trigger_enable' => 1,
      'sch_trigger_at'     => $trigTime,
      'sch_channels'       => 'noti,email',
      'sch_msg_title'      => "【諮詢即將開始提醒】{$bkg['bkg_topic']}（含 Zoom 會議室連結）",
      'sch_msg_body'       => $schedBody
    ], ['sch_id' => $schId]);

    // 5. 立即發送站內通知與 Email 給中選會員（附上 Zoom 會議室 ID、密碼與加入連結）
    $confirmMsg = "恭喜！您預約的 " . date('Y-m-d H:i', $startTs) . " 一對一顧問諮詢已審核通過。\n"
                . "Zoom 會議室 ID：{$zoomRes['meeting_id']}｜密碼：{$zoomRes['password']}\n"
                . "加入連結：{$zoomRes['join_url']}";
    try {
      DB->insert('fsl_notifications', [
        'mb_id'      => $targetMbId,
        'noti_type'  => 'consult',
        'noti_title' => "【預約確認】一對一顧問諮詢與 Zoom 會議室通知",
        'noti_body'  => $confirmMsg,
        'noti_link'  => "member_consult.php"
      ]);
    } catch (Throwable $e) {}

    if ($mbEmail !== '') {
      $htmlMail = "<p>親愛的 {$mbName} 您好：</p>"
                . "<p>您申請的一對一顧問諮詢已由顧問正式確認，並為您建立專屬 Zoom 線上會議室：</p>"
                . "<ul>"
                . "<li><strong>諮詢時間：</strong>" . date('Y-m-d H:i', $startTs) . "</li>"
                . "<li><strong>諮詢主題：</strong>{$bkg['bkg_topic']}</li>"
                . "<li><strong>Zoom 會議室 ID：</strong><code>{$zoomRes['meeting_id']}</code></li>"
                . "<li><strong>會議室密碼：</strong><code>{$zoomRes['password']}</code></li>"
                . "<li><strong>直接加入連結：</strong><a href='{$zoomRes['join_url']}'>{$zoomRes['join_url']}</a></li>"
                . "</ul>";
      _fn_consult_send_mail($mbEmail, "【人生第五季】一對一顧問諮詢確認與 Zoom 會議室資訊", $htmlMail);
    }

    echo json_encode([
      'RTN'        => 1,
      'msgType'    => 'success',
      'msgStr'     => "已核准【{$mbName}】的預約申請，Zoom 會議室 ({$zoomRes['meeting_id']}) 已建立並正式登載入排程表！",
      'meeting_id' => $zoomRes['meeting_id']
    ]);
    exit();
  break;

  // =========================================================================
  // 4. Zoom 專業版遠端管理：同步修改 Zoom 會議時間 / 刪除取消會議室 / 取得出席名單
  // =========================================================================
  case 'func_consult_zoom_sync_update':
    if ($adId <= 0 || $bkgID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的操作參數！']);
      exit();
    }
    $newStart = trim((string)($_POST['new_start_at'] ?? ''));
    $duration = max(15, (int)($_POST['duration'] ?? 60));

    $bkg = DB->row("SELECT * FROM fsl_consult_bookings WHERE bkg_id = ?", [$bkgID]);
    if (!$bkg || empty($bkg['zoom_meeting_id'])) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '此預約尚未建立 Zoom 會議室！']);
      exit();
    }

    $zUpd = _fn_zoom_update_meeting($bkg['zoom_meeting_id'], [
      'start_time' => $newStart,
      'duration'   => $duration
    ]);
    if (!$zUpd['ok']) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => $zUpd['msg']]);
      exit();
    }

    if ($newStart !== '') {
      $endAt = date('Y-m-d H:i:s', strtotime($newStart . " +{$duration} minutes"));
      DB->update('fsl_schedules', [
        'sch_start_at'   => $newStart,
        'sch_end_at'     => $endAt,
        'sch_trigger_at' => date('Y-m-d H:i:s', strtotime($newStart . ' -1 hour'))
      ], ['sch_id' => (int)$bkg['sch_id']]);
    }

    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => $zUpd['msg']]);
    exit();
  break;

  case 'func_consult_cancel_and_delete_zoom':
    if ($adId <= 0 || $bkgID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的操作參數！']);
      exit();
    }
    $bkg = DB->row("SELECT * FROM fsl_consult_bookings WHERE bkg_id = ?", [$bkgID]);
    if (!$bkg) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到指定預約紀錄！']);
      exit();
    }

    if (!empty($bkg['zoom_meeting_id'])) {
      $zDel = _fn_zoom_delete_meeting($bkg['zoom_meeting_id']);
      if (!$zDel['ok']) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '刪除 Zoom 會議室失敗：' . $zDel['msg']]);
        exit();
      }
    }

    DB->update('fsl_consult_bookings', [
      'bkg_status'      => 3,
      'zoom_meeting_id' => null,
      'zoom_join_url'   => null,
      'zoom_start_url'  => null
    ], ['bkg_id' => $bkgID]);

    DB->update('fsl_schedules', [
      'sch_title'          => '一對一專屬顧問諮詢（重新開放預約）',
      'sch_color'          => '#8b5cf6',
      'sch_trigger_enable' => 0
    ], ['sch_id' => (int)$bkg['sch_id']]);

    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '已取消此預約並遠端刪除 Zoom 雲端會議室，時段已重新釋出！']);
    exit();
  break;

  case 'func_consult_zoom_report':
    if ($adId <= 0 || $bkgID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的操作參數！']);
      exit();
    }
    $bkg = DB->row("SELECT zoom_meeting_id FROM fsl_consult_bookings WHERE bkg_id = ?", [$bkgID]);
    if (!$bkg || empty($bkg['zoom_meeting_id'])) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '此預約尚無 Zoom 會議室紀錄！']);
      exit();
    }
    $rep = _fn_zoom_get_past_participants($bkg['zoom_meeting_id']);
    echo json_encode([
      'RTN'          => $rep['ok'] ? 1 : 0,
      'msgType'      => $rep['ok'] ? 'success' : 'warning',
      'msgStr'       => $rep['msg'],
      'participants' => $rep['participants']
    ]);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
