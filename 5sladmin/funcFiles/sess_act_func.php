<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');

header('Content-Type: application/json; charset=utf-8');

// 1. 驗證管理者登入 Session 與 CSRF Token
if (empty($_SESSION['SESS_AD_ID'])) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '登入已逾時，請重新登入後台系統！']);
  exit();
}

$token    = $_POST['token'] ?? '';
$formAcce = trim((string)($_POST['formAcce'] ?? ''));
$formData = (array)($_POST['arrDatA'] ?? array());
$sessID   = (int)($_POST['sessID'] ?? 0);

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

switch ($formAcce) {

  // =========================================================================
  // 1. 新增 / 更新梯次排程與 Zoom 會議室設定 (func_sess_addnew / func_sess_infoUpdate)
  // =========================================================================
  case 'func_sess_addnew':
  case 'func_sess_infoUpdate':
    $acc = ($formAcce === 'func_sess_addnew') ? 'add' : 'up';

    $courseId = (int)($formData['course_id'] ?? 0);
    $batch    = max(1, (int)($formData['sess_batch'] ?? 1));
    if ($courseId <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請選擇所屬主課程！']);
      exit();
    }

    // 檢查同一主課程下梯次編號是否重複 (UNIQUE KEY course_id + sess_batch)
    $dupId = (int)DB->single(
      "SELECT sess_id FROM fsl_course_sessions WHERE course_id = ? AND sess_batch = ? AND sess_id != ? AND sess_status != 9",
      array($courseId, $batch, $sessID)
    );
    if ($dupId > 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => "該課程的「第 {$batch} 梯次」已存在（ID: {$dupId}），請更換梯次編號！"]);
      exit();
    }

    $upTime = date('Y-m-d H:i:s');
    $formData['course_id']       = $courseId;
    $formData['sess_batch']      = $batch;
    $formData['sess_lesson']     = max(1, (int)($formData['sess_lesson'] ?? 1));
    $formData['sess_duration']   = max(15, (int)($formData['sess_duration'] ?? 40));
    $formData['sess_batchmode']  = (int)($formData['sess_batchmode'] ?? 1);
    $formData['sess_secured']    = (int)($formData['sess_secured'] ?? 0);
    $formData['sess_approval']   = (int)($formData['sess_approval'] ?? 0);
    $formData['sess_beforehost'] = (int)($formData['sess_beforehost'] ?? 0);
    $formData['sess_jbhtime']    = max(0, (int)($formData['sess_jbhtime'] ?? 0));
    $formData['sess_watermark']  = (int)($formData['sess_watermark'] ?? 0);
    $formData['sess_muteentry']  = (int)($formData['sess_muteentry'] ?? 0);
    $formData['sess_status']     = (int)($formData['sess_status'] ?? 1);
    $formData['sess_keycode']    = strtoupper(trim((string)($formData['sess_keycode'] ?? '')));

    if ($acc === 'add') {
      $formData['sess_enrollee'] = 0;
      DB->insert('fsl_course_sessions', $formData);
      $newID = (int)DB->lastInsertId();
      echo json_encode([
        'RTN'     => 1,
        'msgType' => 'success',
        'msgStr'  => "第 {$batch} 梯次與 Zoom 會議間設定已成功建立！",
        'newID'   => $newID,
        'upTime'  => $upTime
      ]);
    } else {
      if ($sessID <= 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的梯次 ID！']);
        exit();
      }
      $formData['sess_updated'] = $upTime;
      DB->update('fsl_course_sessions', $formData, ['sess_id' => $sessID]);
      echo json_encode([
        'RTN'     => 1,
        'msgType' => 'success',
        'msgStr'  => '梯次排程與 Zoom 會議室設定已儲存更新！',
        'upTime'  => $upTime
      ]);
    }
    exit();
  break;

  // =========================================================================
  // 2. 課間即時狀態切換 (func_sess_liveStatus: 0=未開課 / 1=LIVE課中 / 2=已結課)
  // =========================================================================
  case 'func_sess_liveStatus':
    $liveStatus = (int)($_POST['liveStatus'] ?? 0);
    if ($sessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的梯次 ID！']);
      exit();
    }
    DB->update('fsl_course_sessions', ['sess_live_status' => $liveStatus, 'sess_updated' => date('Y-m-d H:i:s')], ['sess_id' => $sessID]);

    // 若切換為「已結課 (2)」，同步將本梯次修課中(2)的學員狀態推進為已完課(3)或累計已完成堂數
    if ($liveStatus === 2) {
      DB->query("UPDATE fsl_course_enrolls SET enr_done_lessons = enr_done_lessons + 1, enr_last_watched = NOW() WHERE sess_id = ? AND enr_attend_status IN (1, 2)", array($sessID));
    }

    $liveNames = array(0 => '尚未開課', 1 => 'Zoom 課間進行中 (LIVE)', 2 => '已結課歸檔');
    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '課間即時狀態已切換為：【' . ($liveNames[$liveStatus] ?? '已更新') . '】'
    ]);
    exit();
  break;

  // =========================================================================
  // 3. 單發或群發 Zoom 專屬授權連結 (func_sess_sendZoomLink)
  // =========================================================================
  case 'func_sess_sendZoomLink':
    $enrID = (int)($_POST['enrID'] ?? 0); // 0 = 群發全體學員；> 0 = 單發指定報名ID
    if ($sessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的梯次 ID！']);
      exit();
    }

    $sessInfo = DB->row("
      SELECT s.*, c.course_title
      FROM fsl_course_sessions s
      LEFT JOIN fsl_courses c ON s.course_id = c.course_id
      WHERE s.sess_id = ?
    ", array($sessID));

    if (!$sessInfo || empty($sessInfo['sess_join_url'])) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請先於「梯次排程與 Zoom 設定」填寫 Zoom 會議室加入連結並儲存後，再發送給學員！']);
      exit();
    }

    $whereEnr = " WHERE sess_id = ? AND enr_status IN (1, 2, 3) ";
    $params   = array($sessID);
    if ($enrID > 0) {
      $whereEnr .= " AND enr_id = ? ";
      $params[]  = $enrID;
    }

    $targets = DB->query("SELECT enr_id, mb_id, enr_link_sent_cnt FROM fsl_course_enrolls" . $whereEnr, $params);
    if (empty($targets)) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '目前沒有符合條件的有效報名學員可發送！']);
      exit();
    }

    $nowStr    = date('Y-m-d H:i:s');
    $baseJoin  = trim($sessInfo['sess_join_url']);
    $sep       = (strpos($baseJoin, '?') !== false) ? '&' : '?';
    $cTitle    = $sessInfo['course_title'] ?? '課程直播';
    $batchNo   = (int)$sessInfo['sess_batch'];
    $meetId    = $sessInfo['sess_meetingid'] ?? '';
    $meetPwd   = $sessInfo['sess_passcode'] ?? '';
    $startTime = ($sessInfo['sess_startdate'] ?? '') . ' ' . substr(($sessInfo['sess_starttime'] ?? ''), 0, 5);

    $lastCnt = 0;
    foreach ($targets as $t) {
      $eid     = (int)$t['enr_id'];
      $mid     = (int)$t['mb_id'];
      $lastCnt = (int)$t['enr_link_sent_cnt'] + 1;

      // 為每位學員產生專屬授權參數連結
      $authSig = substr(hash_hmac('sha256', $sessID . '_' . $eid . '_' . $mid, 'FSL_ZOOM_AUTH'), 0, 12);
      $authUrl = $baseJoin . $sep . "fsl_enr={$eid}&auth={$authSig}";

      DB->update('fsl_course_enrolls', array(
        'enr_zoom_url'     => $authUrl,
        'enr_link_sent_at' => $nowStr,
        'enr_link_sent_cnt' => $lastCnt
      ), ['enr_id' => $eid]);

      // 寫入會員通知中心 (fsl_notifications)
      $notiTitle = "【上課通知】《{$cTitle}》第 {$batchNo} 梯 Zoom 教室專屬連結";
      $notiBody  = "親愛的學員您好，您報名的《{$cTitle}》（第 {$batchNo} 梯，開課時間：{$startTime}）Zoom 線上會議室已備妥。\n"
                 . "會議室 ID：{$meetId}｜密碼：{$meetPwd}\n"
                 . "您的專屬授權進入連結：{$authUrl}";

      DB->insert('fsl_notifications', array(
        'mb_id'       => $mid,
        'no_category' => 2,
        'no_title'    => $notiTitle,
        'no_content'  => $notiBody
      ));
    }

    $sentTotal = count($targets);
    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => ($enrID > 0) ? '已成功發送專屬 Zoom 授權連結給該學員！' : "已成功群發 Zoom 專屬授權連結給全體 {$sentTotal} 位學員！",
      'sentCnt' => $lastCnt,
      'sentAt'  => substr($nowStr, 5, 11)
    ]);
    exit();
  break;

  // =========================================================================
  // 4. 課間點名簽到 / 簽退 / 請假 / 缺席 (func_sess_attend)
  // =========================================================================
  case 'func_sess_attend':
    $enrID     = (int)($_POST['enrID'] ?? 0);
    $attendAct = trim((string)($_POST['attendAct'] ?? ''));

    if ($sessID <= 0 || $enrID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的報名紀錄參數！']);
      exit();
    }

    $nowStr = date('Y-m-d H:i:s');
    $upd    = array();

    if ($attendAct === 'checkin') {
      $upd['enr_attend_status'] = 1;
      $upd['enr_checkin_at']    = $nowStr;
      $upd['enr_last_watched']  = $nowStr;
      $upd['enr_status']        = 2; // 同步更新修課狀態為 2-修課進行中
    } elseif ($attendAct === 'checkout') {
      $upd['enr_attend_status'] = 2;
      $upd['enr_checkout_at']   = $nowStr;
      // 若尚未簽到就直接按簽退，補上簽到時間
      $hasIn = DB->single("SELECT enr_checkin_at FROM fsl_course_enrolls WHERE enr_id = ?", array($enrID));
      if (empty($hasIn)) {
        $upd['enr_checkin_at'] = $nowStr;
      }
    } elseif ($attendAct === 'leave') {
      $upd['enr_attend_status'] = 3;
    } elseif ($attendAct === 'absent') {
      $upd['enr_attend_status'] = 4;
    } else {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的點名動作！']);
      exit();
    }

    DB->update('fsl_course_enrolls', $upd, ['enr_id' => $enrID]);

    $rowNow = DB->row("SELECT enr_attend_status, enr_checkin_at, enr_checkout_at FROM fsl_course_enrolls WHERE enr_id = ?", array($enrID));
    $attSt  = (int)($rowNow['enr_attend_status'] ?? 0);
    $badges = array(
      0 => '<span class="badge text-bg-secondary">未簽到</span>',
      1 => '<span class="badge text-bg-primary">已簽到上課中</span>',
      2 => '<span class="badge text-bg-success">已完成簽退</span>',
      3 => '<span class="badge text-bg-warning">請假</span>',
      4 => '<span class="badge text-bg-danger">缺席</span>'
    );

    $checkinCnt  = (int)DB->single("SELECT COUNT(*) FROM fsl_course_enrolls WHERE sess_id = ? AND enr_attend_status IN (1, 2)", array($sessID));
    $checkoutCnt = (int)DB->single("SELECT COUNT(*) FROM fsl_course_enrolls WHERE sess_id = ? AND enr_attend_status = 2", array($sessID));

    echo json_encode([
      'RTN'           => 1,
      'msgType'       => 'success',
      'msgStr'        => '學員課間點名紀錄已更新！',
      'badgeHtml'     => $badges[$attSt] ?? $badges,
      'checkinShort'  => !empty($rowNow['enr_checkin_at']) ? substr($rowNow['enr_checkin_at'], 5, 11) : '-',
      'checkoutShort' => !empty($rowNow['enr_checkout_at']) ? substr($rowNow['enr_checkout_at'], 5, 11) : '-',
      'checkinCnt'    => $checkinCnt,
      'checkoutCnt'   => $checkoutCnt
    ]);
    exit();
  break;

  // =========================================================================
  // 5. 已簽到學員一鍵全體簽退 (func_sess_batchCheckout)
  // =========================================================================
  case 'func_sess_batchCheckout':
    if ($sessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的梯次 ID！']);
      exit();
    }
    $nowStr = date('Y-m-d H:i:s');
    DB->query(
      "UPDATE fsl_course_enrolls SET enr_attend_status = 2, enr_checkout_at = ? WHERE sess_id = ? AND enr_attend_status = 1",
      array($nowStr, $sessID)
    );
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '已完成所有已簽到學員的課後簽退！']);
    exit();
  break;

  // =========================================================================
  // 6. 課後雲端錄影回放與講義歸檔更新 (func_sess_recordingUpdate)
  // =========================================================================
  case 'func_sess_recordingUpdate':
    if ($sessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的梯次 ID！']);
      exit();
    }
    $upTime = date('Y-m-d H:i:s');
    $updRec = array(
      'sess_rec_url'      => trim((string)($formData['sess_rec_url'] ?? '')),
      'sess_rec_passcode' => trim((string)($formData['sess_rec_passcode'] ?? '')),
      'sess_handout_url'  => trim((string)($formData['sess_handout_url'] ?? '')),
      'sess_brief'        => trim((string)($formData['sess_brief'] ?? '')),
      'sess_updated'      => $upTime
    );
    DB->update('fsl_course_sessions', $updRec, ['sess_id' => $sessID]);

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '課後錄影回放與講義歸檔資料已成功更新！',
      'upTime'  => $upTime
    ]);
    exit();
  break;

  // =========================================================================
  // 7. 軟刪除課程梯次 (func_sess_delete)
  // =========================================================================
  case 'func_sess_delete':
    if ($sessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的梯次 ID！']);
      exit();
    }
    $enrolled = (int)DB->single("SELECT COUNT(*) FROM fsl_course_enrolls WHERE sess_id = ? AND enr_status IN (1, 2, 3)", array($sessID));
    if ($enrolled > 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => "此梯次已有 {$enrolled} 位有效報名學員，無法直接刪除，請改為關閉報名狀態！"]);
      exit();
    }
    DB->update('fsl_course_sessions', ['sess_status' => 9], ['sess_id' => $sessID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '開課梯次已成功刪除！']);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
