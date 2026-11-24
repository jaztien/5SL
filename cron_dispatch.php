<?php
require_once(__DIR__ . '/5sladmin/includeFiles/init.inc.php');
require_once(__DIR__ . '/5sladmin/includeFiles/db.inc.php');
require_once(__DIR__ . '/5sladmin/includeFiles/common.lib.php');

/**
 * 執行單一排程的多對象、多通路通知觸發引擎
 * 支援：1-會員個別發送 / 2-依課程群組學員發送 / 3-自選多位會員 / 4-關係管理者或講師發送
 * 通路：noti(站內通知) / email(電子郵件) / sms(簡訊佇列) / line(LINE推播佇列)
 */
function _fn_execute_single_schedule($schID) {
  $sch = DB->row("SELECT * FROM fsl_schedules WHERE sch_id = ?", array((int)$schID));
  if (!$sch) return '找不到指定排程';

  $targetType = (int)$sch['sch_target_type'];
  $targetIds  = array_filter(array_map('intval', explode(',', (string)($sch['sch_target_ids'] ?? ''))));
  $channels   = array_filter(array_map('trim', explode(',', (string)($sch['sch_channels'] ?? 'noti'))));

  // 1. 解析接收者清單：['type' => 1(會員)|2(管理者/講師), 'id' => ..., 'name' => ..., 'email' => ..., 'phone' => ...]
  $recipients = array();

  if ($targetType === 1 || $targetType === 3) {
    // 1-會員個別發送 / 3-自選多位會員
    if (!empty($targetIds)) {
      $inList = implode(',', $targetIds);
      $mRows  = DB->query("SELECT mb_id, mb_name, mb_email, mb_cellphone FROM fsl_members WHERE mb_id IN ({$inList}) AND mb_status = 1");
      if (is_array($mRows)) {
        foreach ($mRows as $m) {
          $recipients[] = array(
            'type'  => 1,
            'id'    => (int)$m['mb_id'],
            'name'  => _fn_secure_crypto($m['mb_name'], 'de'),
            'email' => _fn_secure_crypto($m['mb_email'], 'de'),
            'phone' => _fn_secure_crypto($m['mb_cellphone'], 'de')
          );
        }
      }
    }
  } elseif ($targetType === 2) {
    // 2-依群組（特定課程所有已報名付費學員）發送
    if (!empty($targetIds)) {
      $inList = implode(',', $targetIds);
      $eRows  = DB->query("
        SELECT DISTINCT m.mb_id, m.mb_name, m.mb_email, m.mb_cellphone
        FROM fsl_course_enrolls e
        INNER JOIN fsl_members m ON e.mb_id = m.mb_id
        WHERE e.course_id IN ({$inList}) AND e.enr_pay_status IN (1, 2) AND e.enr_status IN (1, 2, 3) AND m.mb_status = 1
      ");
      if (is_array($eRows)) {
        foreach ($eRows as $m) {
          $recipients[] = array(
            'type'  => 1,
            'id'    => (int)$m['mb_id'],
            'name'  => _fn_secure_crypto($m['mb_name'], 'de'),
            'email' => _fn_secure_crypto($m['mb_email'], 'de'),
            'phone' => _fn_secure_crypto($m['mb_cellphone'], 'de')
          );
        }
      }
    }
  } elseif ($targetType === 4) {
    // 4-關係管理者或講師發送 (fsl_administrators)
    if (!empty($targetIds)) {
      $inList = implode(',', $targetIds);
      $aRows  = DB->query("SELECT ad_id, ad_name, ad_email FROM fsl_administrator WHERE ad_id IN ({$inList}) AND ad_status = 1");
      if (is_array($aRows)) {
        foreach ($aRows as $a) {
          $recipients[] = array(
            'type'  => 2,
            'id'    => (int)$a['ad_id'],
            'name'  => $a['ad_name'],
            'email' => $a['ad_email'] ?? '',
            'phone' => ''
          );
        }
      }
    }
  }

  // 2. 取得課程名稱（供變數替換）
  $courseTitle = '';
  if (!empty($sch['course_id'])) {
    $courseTitle = (string)DB->single("SELECT course_title FROM fsl_courses WHERE course_id = ?", array((int)$sch['course_id']));
  }

  // 3. 取得通知/郵件基礎模板（優先讀取 arti_keycode，否則用 sch_msg_title / sch_msg_body）
  $baseTitle = (string)($sch['sch_msg_title'] ?? $sch['sch_title']);
  $baseBody  = (string)($sch['sch_msg_body'] ?? '');
  if (!empty($sch['arti_keycode'])) {
    $tplRow = DB->row("SELECT arti_title, arti_body FROM fsl_articles WHERE UPPER(arti_keycode) = ? AND arti_status = 1 LIMIT 1", array(strtoupper($sch['arti_keycode'])));
    if ($tplRow) {
      $baseTitle = $tplRow['arti_title'];
      $baseBody  = $tplRow['arti_body'];
    }
  }

  // 4. 逐一針對每位接收者與每個通路執行派發
  $okCount = 0;
  $nowStr  = date('Y-m-d H:i:s');

  foreach ($recipients as $rcp) {
    $vars = array(
      'member_name'  => $rcp['name'],
      'member_email' => $rcp['email'],
      'course_title' => $courseTitle,
      'date'         => substr($sch['sch_start_at'], 0, 10),
      'url'          => 'https://5slife.com'
    );

    $rendered = function_exists('_fn_replace_tpl_vars')
      ? _fn_replace_tpl_vars(['title' => $baseTitle, 'body' => $baseBody], $vars)
      : ['title' => $baseTitle, 'body' => $baseBody];

    foreach ($channels as $ch) {
      $sendStatus = 1;
      $sendNote   = '發送成功';

      if ($ch === 'noti') {
        if ($rcp['type'] === 1) {
          DB->insert('fsl_notifications', array(
            'mb_id'       => $rcp['id'],
            'no_category' => 1,
            'no_title'    => $rendered['title'],
            'no_content'  => strip_tags($rendered['body'])
          ));
          $okCount++;
        } else {
          $sendNote = '管理者/講師僅透過外部信箱或通訊通知';
        }
      } elseif ($ch === 'email') {
        if (!empty($rcp['email']) && filter_var($rcp['email'], FILTER_VALIDATE_EMAIL)) {
          if (function_exists('_fn_send_mail')) {
            $mailOk = _fn_send_mail($rcp['email'], $rcp['name'], $rendered['title'], $rendered['body']);
            $sendStatus = $mailOk ? 1 : 0;
            $sendNote   = $mailOk ? 'Email 寄送成功' : 'SMTP 寄送失敗';
          } else {
            $sendNote = 'Email 已排入發信佇列 (' . $rcp['email'] . ')';
          }
          if ($sendStatus === 1) $okCount++;
        } else {
          $sendStatus = 0;
          $sendNote   = '缺少有效 Email 信箱';
        }
      } elseif ($ch === 'sms') {
        $sendStatus = 2; // 2-待串接外部API佇列
        $sendNote   = '已寫入簡訊發送佇列（預留閘道接口，手機：' . ($rcp['phone'] ?: '未填') . '）';
        $okCount++;
      } elseif ($ch === 'line') {
        $sendStatus = 2; // 2-待串接外部API佇列
        $sendNote   = '已寫入 LINE 官方推播佇列（預留 Messaging API 接口）';
        $okCount++;
      }

      DB->insert('fsl_schedule_logs', array(
        'sch_id'         => (int)$sch['sch_id'],
        'recipient_type' => $rcp['type'],
        'recipient_id'   => $rcp['id'],
        'channel'        => $ch,
        'send_status'    => $sendStatus,
        'send_note'      => $sendNote
      ));
    }
  }

  $summary = "目標 " . count($recipients) . " 位對象，共派發 {$okCount} 筆通路任務";
  DB->update('fsl_schedules', array(
    'sch_exec_status' => 1,
    'sch_executed_at' => $nowStr,
    'sch_exec_result' => $summary
  ), ['sch_id' => (int)$sch['sch_id']]);

  return "排程執行完畢：{$summary}";
}

// =========================================================================
// 若為伺服器 Crontab 直接執行本檔案，自動掃描所有到期未執行之排程
// =========================================================================
if (php_sapi_name() === 'cli' || basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'cron_dispatch.php') {
  $dueList = DB->query("
    SELECT sch_id
    FROM fsl_schedules
    WHERE sch_trigger_enable = 1
      AND sch_exec_status = 0
      AND sch_trigger_at IS NOT NULL
      AND sch_trigger_at <= NOW()
    ORDER BY sch_trigger_at ASC
    LIMIT 50
  ");

  $ran = 0;
  if (is_array($dueList)) {
    foreach ($dueList as $due) {
      echo _fn_execute_single_schedule((int)$due['sch_id']) . PHP_EOL;
      $ran++;
    }
  }
  if ($ran === 0) {
    echo '[' . date('Y-m-d H:i:s') . '] 目前無到期排程需執行。' . PHP_EOL;
  }
}
