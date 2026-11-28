<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
require_once('../includeFiles/zoom_api.inc.php');

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
  // 1. 新增 / 更新梯次排程與 Zoom 會議室設定（自動建立或遠端同步更新 Zoom）
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

    // 檢查同一主課程下梯次編號是否重複
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

    $courseTitle = (string)DB->single("SELECT course_title FROM fsl_courses WHERE course_id = ?", [$courseId]) ?: '線上課程';
    $zoomTopic   = "【人生第五季】{$courseTitle}（第 {$batch} 梯" . (!empty($formData['sess_altname']) ? " - {$formData['sess_altname']}" : '') . "）";
    $startDate   = trim((string)($formData['sess_startdate'] ?? date('Y-m-d')));
    $startTime   = trim((string)($formData['sess_starttime'] ?? '19:30:00'));
    $fullStartAt = "{$startDate} {$startTime}";
    $curMeetId   = preg_replace('/\s+/', '', trim((string)($formData['sess_meetingid'] ?? '')));
    $zoomSyncMsg = '';

    // 線上/混合梯次 (batchmode 1 或 2)：無會議室 ID 且勾選自動建立時，自動呼叫 Zoom API 建立會議室
    $autoCreateZoom = ((int)($formData['auto_create_zoom'] ?? 0) === 1);
    unset($formData['auto_create_zoom']);

    if (in_array($formData['sess_batchmode'], [1, 2], true) && $curMeetId === '' && $autoCreateZoom && function_exists('_fn_zoom_create_meeting')) {
      $zRes = _fn_zoom_create_meeting(
        $zoomTopic,
        $fullStartAt,
        $formData['sess_duration'],
        (string)($formData['sess_subscript'] ?? ''),
        ($formData['sess_approval'] === 1)
      );
      if ($zRes['ok']) {
        $formData['sess_meetingid'] = $zRes['meeting_id'];
        $formData['sess_passcode']  = $zRes['password'];
        $formData['sess_join_url']  = $zRes['join_url'];
        $formData['sess_start_url'] = $zRes['start_url'];
        $zoomSyncMsg = "（已同步於 Zoom 建立會議室 ID：{$zRes['meeting_id']}）";
      } else {
        $zoomSyncMsg = "（警告：Zoom 建房失敗 - {$zRes['msg']}）";
      }
    } elseif ($acc === 'up' && $curMeetId !== '' && function_exists('_fn_zoom_update_meeting')) {
      // 編輯既有梯次且已有 Zoom 會議室時，自動同步更新 Zoom 雲端主題、開課時間與時長
      $zUpd = _fn_zoom_update_meeting($curMeetId, [
        'topic'      => $zoomTopic,
        'start_time' => $fullStartAt,
        'duration'   => $formData['sess_duration'],
        'agenda'     => (string)($formData['sess_subscript'] ?? '')
      ]);
      if ($zUpd['ok']) {
        $zoomSyncMsg = '（已同步更新 Zoom 雲端會議室資訊）';
      }
    }

    if ($acc === 'add') {
      $formData['sess_enrollee'] = 0;
      DB->insert('fsl_course_sessions', $formData);
      $newID = (int)DB->lastInsertId();

      // 連動自動展開單堂課次與課前提醒排程
      if ($newID > 0 && function_exists('_fn_sync_course_lessons')) {
        _fn_sync_course_lessons($courseId, $newID, $courseTitle, $formData);
      }

      echo json_encode([
        'RTN'     => 1,
        'msgType' => 'success',
        'msgStr'  => "第 {$batch} 梯次與單堂課次排程已成功建立！{$zoomSyncMsg}",
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

      // 同步更新單堂課次與課前提醒排程
      if (function_exists('_fn_sync_course_lessons')) {
        _fn_sync_course_lessons($courseId, $sessID, $courseTitle, $formData);
      }

      echo json_encode([
        'RTN'     => 1,
        'msgType' => 'success',
        'msgStr'  => "梯次排程與 Zoom 會議室設定已儲存並同步課次！{$zoomSyncMsg}",
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
  // 3. 單堂課次上課狀態直接切換 (func_sess_lessonStatus: 0=待上課/1=上課中/2=已完課/3=已延期)
  // =========================================================================
  case 'func_sess_lessonStatus':
    $lessID     = (int)($_POST['lessID'] ?? 0);
    $lessStatus = (int)($_POST['lessStatus'] ?? 0);
    if ($sessID <= 0 || $lessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的課堂參數！']);
      exit();
    }
    DB->update('fsl_course_lessons', ['less_status' => $lessStatus], ['less_id' => $lessID, 'sess_id' => $sessID]);

    if ($lessStatus === 3) {
      DB->query("UPDATE fsl_schedules SET sch_status = 0 WHERE cses_id = ? AND less_id = ? AND sch_status = 1", array($sessID, $lessID));
    } elseif ($lessStatus === 0) {
      DB->query("UPDATE fsl_schedules SET sch_status = 1 WHERE cses_id = ? AND less_id = ? AND sch_status = 0 AND sch_exec_at > NOW()", array($sessID, $lessID));
    }

    $stNames = array(0 => '待上課', 1 => '上課中', 2 => '已完課', 3 => '已延期');
    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '單堂上課狀態已更新為【' . ($stNames[$lessStatus] ?? '已更新') . '】！'
    ]);
    exit();
  break;

  // =========================================================================
  // 4. 儲存單堂課次編輯並連動順延課前提醒排程 + 同步更新單堂 Zoom (func_sess_lessonSave)
  // =========================================================================
  case 'func_sess_lessonSave':
    $lessID  = (int)($formData['less_id'] ?? 0);
    $title   = trim((string)($formData['less_title'] ?? ''));
    $startAt = trim((string)($formData['less_start_at'] ?? ''));
    $endAt   = trim((string)($formData['less_end_at'] ?? ''));

    if ($sessID <= 0 || $lessID <= 0 || $title === '' || $startAt === '' || $endAt === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫單堂主題與起訖時間！']);
      exit();
    }

    $oldLesson = DB->row("SELECT less_no, less_start_at FROM fsl_course_lessons WHERE less_id = ? AND sess_id = ?", array($lessID, $sessID));
    if (!$oldLesson) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到指定的單堂課次資料！']);
      exit();
    }

    $lessStatus = (int)($formData['less_status'] ?? 0);
    $lessZoomId = preg_replace('/\s+/', '', trim((string)($formData['less_zoom_id'] ?? '')));

    $updLesson  = array(
      'less_title'     => $title,
      'less_start_at'  => $startAt,
      'less_end_at'    => $endAt,
      'less_location'  => trim((string)($formData['less_location'] ?? '')),
      'less_status'    => $lessStatus,
      'less_zoom_id'   => $lessZoomId,
      'less_zoom_pass' => trim((string)($formData['less_zoom_pass'] ?? '')),
      'less_zoom_url'  => trim((string)($formData['less_zoom_url'] ?? '')),
      'less_note'      => trim((string)($formData['less_note'] ?? ''))
    );
    DB->update('fsl_course_lessons', $updLesson, ['less_id' => $lessID, 'sess_id' => $sessID]);

    // 若本堂課設有專屬 less_zoom_id，同步呼叫 Zoom API 更新會議起訖時間與主題
    $zoomLessMsg = '';
    if ($lessZoomId !== '' && function_exists('_fn_zoom_update_meeting')) {
      $lessDur = max(15, (int)round((strtotime($endAt) - strtotime($startAt)) / 60));
      $zUpd = _fn_zoom_update_meeting($lessZoomId, [
        'topic'      => $title,
        'start_time' => $startAt,
        'duration'   => $lessDur
      ]);
      if ($zUpd['ok']) {
        $zoomLessMsg = '（已同步更新單堂 Zoom 會議室）';
      }
    }

    // 連動順延 fsl_schedules 中對應本堂次的課前自動提醒時間
    $oldStartTs = strtotime((string)$oldLesson['less_start_at']);
    $newStartTs = strtotime($startAt);
    $syncedSch  = 0;

    if ($oldStartTs && $newStartTs && $oldStartTs !== $newStartTs) {
      $diffSec = $newStartTs - $oldStartTs;
      $schRows = DB->query("SELECT sch_id, sch_exec_at FROM fsl_schedules WHERE cses_id = ? AND less_id = ? AND sch_status IN (0, 1)", array($sessID, $lessID));
      if (is_array($schRows)) {
        foreach ($schRows as $sr) {
          $oldExecTs = strtotime((string)$sr['sch_exec_at']);
          if ($oldExecTs) {
            $newExecAt = date('Y-m-d H:i:s', $oldExecTs + $diffSec);
            $newSchSt  = ($lessStatus === 3) ? 0 : 1;
            DB->update('fsl_schedules', [
              'sch_exec_at' => $newExecAt,
              'sch_status'  => $newSchSt
            ], ['sch_id' => (int)$sr['sch_id']]);
            $syncedSch++;
          }
        }
      }
    }

    $extraMsg = ($syncedSch > 0) ? "（已同步順延 {$syncedSch} 筆課前提醒排程）" : '';
    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => "第 {$oldLesson['less_no']} 堂課次資料已儲存更新！{$zoomLessMsg}{$extraMsg}"
    ]);
    exit();
  break;
  // =========================================================================
  // 5. 儲存 Zoom 會議終結報告 (func_sess_zoomReportSave)
  // =========================================================================
  case 'func_sess_zoomReportSave':
    $lessID     = (int)($_POST['lessID'] ?? 0);
    $zoomReport = trim((string)($_POST['zoomReport'] ?? ''));

    if ($sessID <= 0 || $lessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的課堂參數！']);
      exit();
    }

    DB->update('fsl_course_lessons', ['less_zoom_report' => $zoomReport], ['less_id' => $lessID, 'sess_id' => $sessID]);
    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => 'Zoom 會議終結報告已成功儲存歸檔！'
    ]);
    exit();
  break;

  // =========================================================================
  // 6. 一鍵建立雲端 Zoom 會議室 & 同步 Zoom 雲端會議出席報告 API
  // =========================================================================
  case 'func_sess_createZoomRoom':
    if ($sessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請先儲存建立梯次基本資料後，再一鍵建立 Zoom 雲端會議室！']);
      exit();
    }
    $sRow = DB->row("SELECT s.*, c.course_title FROM fsl_course_sessions s LEFT JOIN fsl_courses c ON s.course_id = c.course_id WHERE s.sess_id = ?", [$sessID]);
    if (!$sRow) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到指定梯次！']);
      exit();
    }
    $zTopic = "【人生第五季】{$sRow['course_title']}（第 {$sRow['sess_batch']} 梯）";
    $zStart = ($sRow['sess_startdate'] ?: date('Y-m-d')) . ' ' . ($sRow['sess_starttime'] ?: '19:30:00');
    $zRes   = _fn_zoom_create_meeting($zTopic, $zStart, max(15, (int)$sRow['sess_duration']), (string)($sRow['sess_subscript'] ?? ''), ((int)$sRow['sess_approval'] === 1));

    if (!$zRes['ok']) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => 'Zoom 建房失敗：' . $zRes['msg']]);
      exit();
    }

    DB->update('fsl_course_sessions', [
      'sess_meetingid' => $zRes['meeting_id'],
      'sess_passcode'  => $zRes['password'],
      'sess_join_url'  => $zRes['join_url'],
      'sess_start_url' => $zRes['start_url'],
      'sess_updated'   => date('Y-m-d H:i:s')
    ], ['sess_id' => $sessID]);

    echo json_encode([
      'RTN'        => 1,
      'msgType'    => 'success',
      'msgStr'     => $zRes['msg'] . "（會議室 ID：{$zRes['meeting_id']}）",
      'meeting_id' => $zRes['meeting_id'],
      'password'   => $zRes['password'],
      'join_url'   => $zRes['join_url'],
      'start_url'  => $zRes['start_url']
    ]);
    exit();
  break;

  // =========================================================================
  // 6-1. 為指定單堂課次建立專屬 Zoom 會議室 (func_sess_lessonCreateZoom)
  // =========================================================================
  case 'func_sess_lessonCreateZoom':
    $lessID = (int)($_POST['lessID'] ?? 0);
    if ($sessID <= 0 || $lessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的梯次或課堂參數！']);
      exit();
    }

    $info = DB->row("
      SELECT l.*, s.sess_batch, s.sess_approval, c.course_title
      FROM fsl_course_lessons l
      INNER JOIN fsl_course_sessions s ON l.sess_id = s.sess_id
      LEFT JOIN fsl_courses c ON s.course_id = c.course_id
      WHERE l.less_id = ? AND l.sess_id = ?
    ", [$lessID, $sessID]);

    if (!$info) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到指定的單堂課次資料！']);
      exit();
    }

    $cTitle   = $info['course_title'] ?? '課程直播';
    $lTitle   = $info['less_title'] ?: ("第 {$info['less_no']} 堂");
    $zTopic   = "【人生第五季】{$cTitle}（第 {$info['sess_batch']} 梯 - {$lTitle}）";
    $startAt  = (string)$info['less_start_at'];
    $endAt    = (string)$info['less_end_at'];
    $duration = max(15, (int)round((strtotime($endAt) - strtotime($startAt)) / 60));

    $zRes = _fn_zoom_create_meeting(
      $zTopic,
      $startAt,
      $duration,
      (string)($info['less_note'] ?? ''),
      ((int)($info['sess_approval'] ?? 0) === 1)
    );

    if (!$zRes['ok']) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => 'Zoom 單堂會議室建立失敗：' . $zRes['msg']]);
      exit();
    }

    DB->update('fsl_course_lessons', [
      'less_zoom_id'   => $zRes['meeting_id'],
      'less_zoom_pass' => $zRes['password'],
      'less_zoom_url'  => $zRes['join_url']
    ], ['less_id' => $lessID, 'sess_id' => $sessID]);

    echo json_encode([
      'RTN'        => 1,
      'msgType'    => 'success',
      'msgStr'     => "第 {$info['less_no']} 堂專屬 Zoom 會議室已建立（ID：{$zRes['meeting_id']}）！",
      'meeting_id' => $zRes['meeting_id']
    ]);
    exit();
  break;




  case 'func_sess_zoomReportSync':
    $lessID = (int)($_POST['lessID'] ?? 0);
    if ($sessID <= 0 || $lessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的課堂參數！']);
      exit();
    }

    $lessInfo = DB->row("
      SELECT l.*, s.sess_meetingid, s.sess_batch, c.course_title
      FROM fsl_course_lessons l
      LEFT JOIN fsl_course_sessions s ON l.sess_id = s.sess_id
      LEFT JOIN fsl_courses c ON s.course_id = c.course_id
      WHERE l.less_id = ? AND l.sess_id = ?
    ", array($lessID, $sessID));

    $meetId = preg_replace('/\s+/', '', (string)($lessInfo['less_zoom_id'] ?: ($lessInfo['sess_meetingid'] ?? '')));
    if ($meetId === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '本堂課尚未設定 Zoom 會議室 ID，無法向 Zoom 雲端拉取報告！']);
      exit();
    }

    $apiRep = _fn_zoom_get_past_participants($meetId);
    $pList  = $apiRep['participants'] ?? [];

    $tplReport  = "【Zoom 雲端會議出席報告】\n";
    $tplReport .= "課程梯次：{$lessInfo['course_title']}（第 {$lessInfo['sess_batch']} 梯 - 第 {$lessInfo['less_no']} 堂）\n";
    $tplReport .= "會議室 ID：{$meetId}｜同步時間：" . date('Y-m-d H:i:s') . "\n";
    $tplReport .= "雲端實際出席人數：" . count($pList) . " 人\n";
    $tplReport .= "--------------------------------------------------\n";
    if (!empty($pList)) {
      foreach ($pList as $idx => $p) {
        $mins = round(((int)($p['duration'] ?? 0)) / 60, 1);
        $jIn  = str_replace('T', ' ', substr((string)($p['join_time'] ?? ''), 0, 16));
        $jOut = str_replace('T', ' ', substr((string)($p['leave_time'] ?? ''), 0, 16));
        $tplReport .= ($idx + 1) . ". " . ($p['name'] ?? '與會者') . " ({$jIn} ~ {$jOut}，停留 {$mins} 分鐘)\n";
      }
    } else {
      $tplReport .= "（目前 Zoom 雲端尚無已結束場次之出席明細，或會議尚未結束）\n";
    }

    echo json_encode([
      'RTN'        => 1,
      'msgType'    => $apiRep['ok'] ? 'success' : 'warning',
      'msgStr'     => $apiRep['ok'] ? '已從 Zoom 雲端同步最新與會出席名單！' : $apiRep['msg'],
      'reportText' => $tplReport
    ]);
    exit();
  break;

  // =========================================================================
  // 7. 依梯次規則重新展開單堂課次 (func_sess_regenLessons)
  // =========================================================================
  case 'func_sess_regenLessons':
    if ($sessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的梯次 ID！']);
      exit();
    }
    $sRow = DB->row("SELECT s.*, c.course_title FROM fsl_course_sessions s LEFT JOIN fsl_courses c ON s.course_id = c.course_id WHERE s.sess_id = ?", [$sessID]);
    if ($sRow && function_exists('_fn_sync_course_lessons')) {
      _fn_sync_course_lessons((int)$sRow['course_id'], $sessID, (string)($sRow['course_title'] ?? ''), $sRow);
      echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '已依目前梯次排程規則重新同步單堂課次與提醒！']);
    } else {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到梯次資料或 _fn_sync_course_lessons 共用函式！']);
    }
    exit();
  break;

  // =========================================================================
  // 8. Tab 4：新增多筆錄影或講義至 fsl_media (func_sess_mediaAdd)
  // =========================================================================
  case 'func_sess_mediaAdd':
    if ($sessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的梯次 ID！']);
      exit();
    }
    $target = trim((string)($formData['med_target'] ?? ''));
    $mType  = (int)($formData['med_type'] ?? 2);
    $mTitle = trim((string)($formData['med_title'] ?? ''));
    $mUrl   = trim((string)($formData['med_url'] ?? ''));
    $mSort  = (int)($formData['med_sort'] ?? 100);
    $mBrief = trim((string)($formData['med_brief'] ?? ''));

    if ($mTitle === '' || $mUrl === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請輸入資源標題與雲端連結網址！']);
      exit();
    }

    $medFor   = 'session';
    $medForId = $sessID;
    if (strpos($target, ':') !== false) {
      list($tScope, $tId) = explode(':', $target, 2);
      if (in_array($tScope, array('session', 'lesson'), true) && (int)$tId > 0) {
        $medFor   = $tScope;
        $medForId = (int)$tId;
      }
    }

    $medExt = 'URL';
    if ($mType === 2) $medExt = (stripos($mUrl, 'youtu') !== false) ? 'YT' : 'VIDEO';
    elseif ($mType === 3) $medExt = 'AUDIO';
    elseif ($mType === 4) $medExt = 'PDF';

    $sessDate  = DB->single("SELECT sess_startdate FROM fsl_course_sessions WHERE sess_id = ?", array($sessID));
    $eventDate = !empty($sessDate) ? $sessDate : date('Y-m-d');

    DB->insert('fsl_media', array(
      'med_event_date'     => $eventDate,
      'med_category'       => 'workshop',
      'med_type'           => $mType,
      'med_source'         => 2,
      'med_title'          => $mTitle,
      'med_brief'          => ($mBrief !== '') ? $mBrief : null,
      'med_url'            => $mUrl,
      'med_ext'            => $medExt,
      'med_filesize'       => 0,
      'med_allow_download' => 3,
      'med_for'            => $medFor,
      'med_forid'          => $medForId,
      'med_is_featured'    => 0,
      'med_in_gallery'     => 0,
      'med_sort'           => $mSort,
      'med_status'         => 1
    ));

    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '已成功掛載至本梯次課後資源檔案庫！']);
    exit();
  break;

  // =========================================================================
  // 9. Tab 4：即時更新 fsl_media 排序 (func_sess_mediaSort)
  // =========================================================================
  case 'func_sess_mediaSort':
    $medID   = (int)($_POST['medID'] ?? 0);
    $medSort = (int)($_POST['medSort'] ?? 100);
    if ($medID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的媒體 ID！']);
      exit();
    }
    DB->update('fsl_media', ['med_sort' => $medSort], ['med_id' => $medID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '檔案排序權重已更新！']);
    exit();
  break;

  // =========================================================================
  // 10. Tab 4：移除 fsl_media 掛載項目 (func_sess_mediaDelete)
  // =========================================================================
  case 'func_sess_mediaDelete':
    $medID = (int)($_POST['medID'] ?? 0);
    if ($medID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的媒體 ID！']);
      exit();
    }
    DB->update('fsl_media', ['med_status' => 9], ['med_id' => $medID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '已移除該筆課後錄影/講義檔案！']);
    exit();
  break;

  // =========================================================================
  // 11. 單發或群發 Zoom 專屬註冊加密連結 (呼叫 _fn_enroll_register_zoom)
  // =========================================================================
  case 'func_sess_sendZoomLink':
    $enrID = (int)($_POST['enrID'] ?? 0);
    if ($sessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的梯次 ID！']);
      exit();
    }

    $whereEnr = " WHERE sess_id = ? AND enr_status IN (1, 2, 3) ";
    $params   = array($sessID);
    if ($enrID > 0) {
      $whereEnr .= " AND enr_id = ? ";
      $params[]  = $enrID;
    }

    $targets = DB->query("SELECT enr_id FROM fsl_course_enrolls" . $whereEnr, $params);
    if (empty($targets)) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '目前沒有符合條件的有效報名學員可發送！']);
      exit();
    }

    $okCnt   = 0;
    $lastCnt = 0;
    $lastAt  = '';
    $lastErr = '';

    foreach ($targets as $t) {
      $resReg = _fn_enroll_register_zoom((int)$t['enr_id'], true);
      if ($resReg['ok']) {
        $okCnt++;
        $lastCnt = $resReg['sent_cnt'] ?? 1;
        $lastAt  = substr((string)($resReg['sent_at'] ?? ''), 5, 11);
      } else {
        $lastErr = $resReg['msg'];
      }
    }

    if ($okCnt === 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => $lastErr ?: '派發失敗，請先確認已建立 Zoom 會議室！']);
      exit();
    }

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => ($enrID > 0) ? '已完成 Zoom 會議註冊並發送個人專屬加密連結！' : "已為全體 {$okCnt} 位學員完成 Zoom 會議註冊並派發專屬加密連結！",
      'sentCnt' => $lastCnt,
      'sentAt'  => $lastAt
    ]);
    exit();
  break;

  // =========================================================================
  // 12. 課間點名簽到 / 簽退 / 請假 / 缺席 (func_sess_attend)
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
      $upd['enr_status']        = 2;
    } elseif ($attendAct === 'checkout') {
      $upd['enr_attend_status'] = 2;
      $upd['enr_checkout_at']   = $nowStr;
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
      'badgeHtml'     => $badges[$attSt] ?? $badges[0],
      'checkinShort'  => !empty($rowNow['enr_checkin_at']) ? substr($rowNow['enr_checkin_at'], 5, 11) : '-',
      'checkoutShort' => !empty($rowNow['enr_checkout_at']) ? substr($rowNow['enr_checkout_at'], 5, 11) : '-',
      'checkinCnt'    => $checkinCnt,
      'checkoutCnt'   => $checkoutCnt
    ]);
    exit();
  break;

  // =========================================================================
  // 13. 已簽到學員一鍵全體簽退 (func_sess_batchCheckout)
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
  // 14. 梯次課後總結重點摘要更新 (func_sess_recordingUpdate)
  // =========================================================================
  case 'func_sess_recordingUpdate':
    if ($sessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的梯次 ID！']);
      exit();
    }
    $upTime = date('Y-m-d H:i:s');
    $updRec = array(
      'sess_brief'   => trim((string)($formData['sess_brief'] ?? '')),
      'sess_updated' => $upTime
    );
    DB->update('fsl_course_sessions', $updRec, ['sess_id' => $sessID]);

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '梯次課後總結重點摘要已成功更新！',
      'upTime'  => $upTime
    ]);
    exit();
  break;

  // =========================================================================
  // 15. 軟刪除課程梯次與連動停用課次/排程 (func_sess_delete)
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
    DB->update('fsl_course_lessons', ['less_status' => 9], ['sess_id' => $sessID]);
    DB->update('fsl_schedules', ['sch_status' => 9], ['cses_id' => $sessID]);

    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '開課梯次及關聯單堂課次、排程提醒已成功刪除！']);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
