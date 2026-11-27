<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['SESS_AD_ID'])) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '登入已逾時，請重新登入後台系統！']);
  exit();
}

$token    = $_POST['token'] ?? '';
$formAcce = trim((string)($_POST['formAcce'] ?? ''));
$formData = (array)($_POST['arrDatA'] ?? array());
$schID    = (int)($_POST['schID'] ?? 0);

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}


//確保請求進來時函式已完成定義，之後再抽離至 common.lib.php
function _fn_format_bscal_rows($rows) {
  $list = [];
  if (!is_array($rows)) return $list;
  foreach ($rows as $r) {
    $rawBody = (string)($r['sch_msg_body'] ?? '');
    $meta    = [];
    if (preg_match('/<!--BSCAL_META:(.*?)-->/s', $rawBody, $m)) {
      $meta    = json_decode($m[1], true) ?: [];
      $rawBody = trim(preg_replace('/<!--BSCAL_META:.*?-->/s', '', $rawBody));
    }
    $id     = (int)$r['sch_id'];
    //目前列表顯示時為了辨識自動通知排程，會在 title 前加上 '🔔 '；等下前端 openAppointmentModal 
    //開啟編輯視窗時，記得把開頭的 '🔔 ' 濾掉（title.replace(/^🔔\s*/, '')），否則每次編輯儲存都
    $validLink = (!empty($meta['link']) && is_string($meta['link']) && !str_contains($meta['link'], '[object')) ? trim($meta['link']) : '';
    //會多一顆鈴鐺！
    $prefix = ((int)$r['sch_trigger_enable'] === 1) ? '🔔 ' : '';
    $item   = [
      'id'          => 'sch_' . $id,
      'title'       => $prefix . $r['sch_title'],
      'start'       => $r['sch_start_at'],
      'end'         => !empty($r['sch_end_at']) ? $r['sch_end_at'] : $r['sch_start_at'],
      //'origStart'   => $r['sch_start_at'], // 保留資料庫原始開始時間
      //'origEnd'     => !empty($r['sch_end_at']) ? $r['sch_end_at'] : $r['sch_start_at'], // 保留資料庫原始結束時間
      'allDay'      => ((int)$r['sch_all_day'] === 1),
      'calendarId'  => $r['sch_category'] ?: 'course',
      'color'       => $r['sch_color'] ?: '#3b82f6',
      'description' => nl2br(htmlspecialchars($rawBody, ENT_QUOTES, 'UTF-8')),
      'rawDesc'     => $rawBody,
      'triggerEn'   => (int)$r['sch_trigger_enable'],
      'link'        => ($validLink !== '') ? $validLink : [
        'href' => "sch_detail.php?currID={$id}&fromCal=1", 'text' => '⚙️ 詳細排程與通知對象設定', 'target' => '_self'
      ]

    ];
    if (!empty($meta['recurrence'])) $item['recurrence'] = $meta['recurrence'];
    if (!empty($meta['task'])) $item['task'] = [
      'checked'  => !empty($meta['task']['checked']),
      'priority' => $meta['task']['priority'] ?? 'normal',
      'due'      => $meta['task']['due'] ?? null
    ];
    if (!empty($meta['location'])) $item['location'] = $meta['location'];
    if (!empty($meta['overlap'])) $item['overlap'] = true;
    $list[] = $item;
  }
  return $list;
}
function _fn_format_lesson_rows($rows) {
  $list = [];
  if (!is_array($rows)) return $list;
  $staText = [1 => '待上課', 2 => '上課中', 3 => '已完課', 4 => '已延期改期'];
  foreach ($rows as $r) {
    $lid   = (int)$r['less_id'];
    $sta   = (int)($r['less_status'] ?? 1);
    $color = ($sta === 3) ? '#10b981' : (($sta === 4) ? '#f97316' : '#3b82f6');
    $badge = $staText[$sta] ?? '待上課';

    $list[] = [
      'id'          => 'less_' . $lid,
      'title'       => "📚 [{$badge}] " . $r['less_title'],
      'start'       => $r['less_start_at'],
      'end'         => $r['less_end_at'],
      'allDay'      => false,
      'calendarId'  => 'course',
      'color'       => $color,
      'location'    => $r['less_location'] ?? '',
      'description' => nl2br(htmlspecialchars((string)($r['less_note'] ?? ''), ENT_QUOTES, 'UTF-8')),
      'rawDesc'     => (string)($r['less_note'] ?? ''),
      'triggerEn'   => 0,
      'link'        => [
        'href'   => 'cs_detail.php?currID=' . (int)$r['course_id'],
        'text'   => '🎓 前往所屬課程管理',
        'target' => '_self'
      ]
    ];
  }
  return $list;
}



switch ($formAcce) {

  // =========================================================================
  // 1. FullCalendar 6 日曆事件 JSON 來源 (func_sch_calFeed)
  // =========================================================================
  case 'func_sch_calFeed':
    $start = trim((string)($_POST['start'] ?? ''));
    $end   = trim((string)($_POST['end'] ?? ''));

    $where = " WHERE sch_exec_status != 9 ";
    $params = array();
    if ($start !== '' && $end !== '') {
      $where .= " AND sch_start_at >= ? AND sch_start_at <= ? ";
      $params[] = substr($start, 0, 10) . ' 00:00:00';
      $params[] = substr($end, 0, 10) . ' 23:59:59';
    }

    $rows = DB->query("SELECT * FROM fsl_schedules {$where} ORDER BY sch_start_at ASC", $params);
    $events = array();
    if (is_array($rows)) {
      foreach ($rows as $r) {
        $prefix = ((int)$r['sch_trigger_enable'] === 1) ? '🔔 ' : '';
        $events[] = array(
          'id'              => (int)$r['sch_id'],
          'title'           => $prefix . $r['sch_title'],
          'start'           => str_replace(' ', 'T', $r['sch_start_at']),
          'end'             => !empty($r['sch_end_at']) ? str_replace(' ', 'T', $r['sch_end_at']) : null,
          'allDay'          => ((int)$r['sch_all_day'] === 1),
          'backgroundColor' => $r['sch_color'] ?: '#3b82f6',
          'borderColor'     => $r['sch_color'] ?: '#3b82f6'
        );
      }
    }
    echo json_encode($events, JSON_UNESCAPED_UNICODE);
    exit();
  break;

  // =========================================================================
  // 2. 從日曆左側拖放快捷範本快速建立排程 (func_sch_quickDrop)
  // =========================================================================
  case 'func_sch_quickDrop':
    $title   = trim((string)($_POST['sch_title'] ?? '新排程事件'));
    $cate    = trim((string)($_POST['sch_category'] ?? 'admin'));
    $color   = trim((string)($_POST['sch_color'] ?? '#3b82f6'));
    $startAt = trim((string)($_POST['sch_start_at'] ?? date('Y-m-d 09:00:00')));
    $allDay  = (int)($_POST['sch_all_day'] ?? 0);

    DB->insert('fsl_schedules', array(
      'sch_source'         => 1,
      'sch_category'       => $cate,
      'sch_title'          => $title,
      'sch_color'          => $color,
      'sch_start_at'       => $startAt,
      'sch_all_day'        => $allDay,
      'sch_trigger_enable' => ($cate === 'remind' || $cate === 'marketing') ? 1 : 0,
      'sch_trigger_at'     => $startAt,
      'sch_target_type'    => 1,
      'sch_channels'       => 'noti,email',
      'sch_exec_status'    => 0
    ));

    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => "已於日曆建立「{$title}」，點擊事件可編輯細部通知對象！"]);
    exit();
  break;

  // =========================================================================
  // 3. 日曆拖曳改期或拉長時段 (func_sch_moveDate)
  // =========================================================================
  case 'func_sch_moveDate':
    $startAt = trim((string)($_POST['sch_start_at'] ?? ''));
    $endAt   = trim((string)($_POST['sch_end_at'] ?? ''));
    $allDay  = (int)($_POST['sch_all_day'] ?? 0);

    if ($schID <= 0 || $startAt === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的排程參數！']);
      exit();
    }

    $upd = array(
      'sch_start_at' => $startAt,
      'sch_end_at'   => ($endAt !== '') ? $endAt : null,
      'sch_all_day'  => $allDay
    );
    // 若尚未執行觸發，同步更新預定觸發時間為事件開始前 24 小時（或開始當下）
    $old = DB->row("SELECT sch_exec_status, sch_trigger_enable FROM fsl_schedules WHERE sch_id = ?", array($schID));
    if ($old && (int)$old['sch_exec_status'] === 0 && (int)$old['sch_trigger_enable'] === 1) {
      $upd['sch_trigger_at'] = $startAt;
    }

    DB->update('fsl_schedules', $upd, ['sch_id' => $schID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '排程時間已同步更新！']);
    exit();
  break;

  // =========================================================================
  // 4. 自動從 fsl_course_sessions 提取未來開課梯次時間 (func_sch_syncCourses)
  // =========================================================================
  case 'func_sch_syncCourses':
    $sessions = DB->query("
      SELECT s.*, c.course_title, c.course_lesson, c.course_duration
      FROM fsl_course_sessions s
      INNER JOIN fsl_courses c ON s.course_id = c.course_id
      WHERE s.sess_status IN (1, 2) AND s.sess_startdate >= CURDATE()
      ORDER BY s.sess_startdate ASC
    ");

    $syncCnt = 0;
    if (is_array($sessions) && function_exists('_fn_generate_course_lessons')) {
      foreach ($sessions as $ss) {
        $sessId = (int)$ss['sess_id'];
        $exists = (int)DB->single("SELECT COUNT(*) FROM fsl_course_lessons WHERE sess_id = ?", array($sessId));
        if ($exists > 0) continue;

        $ss['sess_lesson']   = max(1, (int)($ss['sess_lesson'] ?? $ss['course_lesson'] ?? 1));
        $ss['sess_duration'] = max(15, (int)($ss['sess_duration'] ?? $ss['course_duration'] ?? 60));
        $syncCnt += _fn_generate_course_lessons((int)$ss['course_id'], $sessId, $ss['course_title'], $ss);
      }
    }

    echo json_encode([
      'RTN'     => 1,
      'msgType' => ($syncCnt > 0) ? 'success' : 'info',
      'msgStr'  => ($syncCnt > 0) ? "已成功補齊並展開 {$syncCnt} 堂課次與課前提醒！" : '所有未來課程梯次皆已同步完成，無新增項目。'
    ]);
    exit();
  break;

  // =========================================================================
  // 5. 新增 / 更新排程設定 (func_sch_addnew / func_sch_infoUpdate)
  // =========================================================================
  case 'func_sch_addnew':
  case 'func_sch_infoUpdate':
    $acc     = ($formAcce === 'func_sch_addnew') ? 'add' : 'up';
    $title   = trim((string)($formData['sch_title'] ?? ''));
    $startAt = trim((string)($formData['sch_start_at'] ?? ''));

    if ($title === '' || $startAt === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請填寫排程事件標題與開始時間！']);
      exit();
    }

    $dataArr = array(
      'sch_category'       => trim((string)($formData['sch_category'] ?? 'course')),
      'sch_title'          => $title,
      'sch_color'          => trim((string)($formData['sch_color'] ?? '#3b82f6')),
      'sch_start_at'       => $startAt,
      'sch_end_at'         => !empty($formData['sch_end_at']) ? trim((string)$formData['sch_end_at']) : null,
      'sch_all_day'        => (int)($formData['sch_all_day'] ?? 0),
      'course_id'          => !empty($formData['course_id']) ? (int)$formData['course_id'] : null,
      'sch_trigger_enable' => (int)($formData['sch_trigger_enable'] ?? 0),
      'sch_trigger_at'     => !empty($formData['sch_trigger_at']) ? trim((string)$formData['sch_trigger_at']) : null,
      'sch_target_type'    => (int)($formData['sch_target_type'] ?? 1),
      'sch_target_ids'     => trim((string)($formData['sch_target_ids'] ?? '')) ?: null,
      'sch_channels'       => trim((string)($formData['sch_channels'] ?? 'noti')) ?: 'noti',
      'arti_keycode'       => trim((string)($formData['arti_keycode'] ?? '')) ?: null,
      'sch_msg_title'      => trim((string)($formData['sch_msg_title'] ?? '')) ?: null,
      'sch_msg_body'       => (string)($formData['sch_msg_body'] ?? '')
    );

    if ($acc === 'add') {
      $dataArr['sch_source']      = 1;
      $dataArr['sch_exec_status'] = 0;
      DB->insert('fsl_schedules', $dataArr);
      $newID = (int)DB->lastInsertId();
      echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '排程任務已成功建立！', 'newID' => $newID]);
    } else {
      if ($schID <= 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的排程 ID！']);
        exit();
      }
      DB->update('fsl_schedules', $dataArr, ['sch_id' => $schID]);
      echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '排程與通知設定已儲存更新！']);
    }
    exit();
  break;

  // =========================================================================
  // 6. 後台手動立即觸發執行單一排程 (func_sch_triggerNow)
  // =========================================================================
  case 'func_sch_triggerNow':
    if ($schID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的排程 ID！']);
      exit();
    }
    $cronFile = __DIR__ . '/../../cron_dispatch.php';
    if (!file_exists($cronFile)) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到根目錄的 cron_dispatch.php 檔案，請確認檔案位置！']);
      exit();
    }
    require_once($cronFile);
    $resMsg = _fn_execute_single_schedule($schID);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => $resMsg]);
    exit();
  break;
  // =========================================================================
  // 7. 軟刪除作廢排程 (func_sch_delete)
  // =========================================================================
  case 'func_sch_delete':
    $rawId = trim((string)($_POST['schID'] ?? ''));

    // 情況 A：刪除的是「單堂課次 (less_15)」
    if (str_starts_with($rawId, 'less_')) {
      $lessId = (int)substr($rawId, 5);
      DB->update('fsl_course_lessons', ['less_status' => 9], ['less_id' => $lessId]);
      // 同步作廢該堂課尚未發送的課前提醒
      DB->query("UPDATE fsl_schedules SET sch_exec_status = 9 WHERE sch_source = 2 AND cses_id = ? AND sch_exec_status = 0", [$lessId]);
      echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '該堂課次與課前提醒已作廢！']);
      exit();
    }

    // 情況 B：刪除的是「一般排程 (sch_28 或純數字)」
    $realSchId = (int)str_replace('sch_', '', $rawId);
    if ($realSchId <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的排程 ID！']);
      exit();
    }
    DB->update('fsl_schedules', ['sch_exec_status' => 9], ['sch_id' => $realSchId]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '該筆排程已刪除作廢！']);
    exit();
  break;

  // =========================================================================
  // 8. bs-calendar 載入排程與搜尋端點 (func_bs_cal_load)
  // =========================================================================
  case 'func_bs_cal_load':
    // 1. 接收 bs-calendar 查詢參數 (view, fromDate, toDate, year, search, limit, offset 等)
    $view      = trim((string)($_POST['view'] ?? 'month'));
    $fromDate  = trim((string)($_POST['fromDate'] ?? ''));
    $toDate    = trim((string)($_POST['toDate'] ?? ''));
    $year      = (int)($_POST['year'] ?? date('Y'));
    $search    = trim((string)($_POST['search'] ?? ''));
    $limit     = max(1, (int)($_POST['limit'] ?? 10));
    $offset    = max(0, (int)($_POST['offset'] ?? 0));
    $showTasks = filter_var($_POST['showTasks'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $calIds    = is_array($_POST['calendarIds'] ?? null) ? $_POST['calendarIds'] : [];

    $conds = ["sch_exec_status != 9"];
    $binds = [];
    if (!empty($calIds)) {
      $ph = implode(',', array_fill(0, count($calIds), '?'));
      $conds[] = "sch_category IN ({$ph})";
      foreach ($calIds as $cid) $binds[] = trim((string)$cid);
    }
    if (!$showTasks) {
      $conds[] = "sch_category != 'admin'";
    }
    $whereSql = ' WHERE ' . implode(' AND ', $conds);

    // 搜尋模式
    if ($search !== '') {
      $sWhere = $whereSql . " AND (sch_title LIKE ? OR sch_msg_body LIKE ?)";
      $sBinds = array_merge($binds, ["%{$search}%", "%{$search}%"]);
      $total  = (int)DB->single("SELECT COUNT(*) FROM fsl_schedules {$sWhere}", $sBinds);
      $rows   = DB->query("SELECT * FROM fsl_schedules {$sWhere} ORDER BY sch_start_at DESC LIMIT {$offset}, {$limit}", $sBinds);
      echo json_encode(['rows' => _fn_format_bscal_rows($rows), 'total' => $total], JSON_UNESCAPED_UNICODE);
      exit();
    }

    // 年視圖統計模式
    if ($view === 'year') {
      $yWhere = $whereSql . " AND YEAR(sch_start_at) = ?";
      $yRows  = DB->query("SELECT DATE(sch_start_at) AS d, COUNT(*) AS cnt FROM fsl_schedules {$yWhere} GROUP BY DATE(sch_start_at)", array_merge($binds, [$year]));
      $out    = [];
      foreach ((array)$yRows as $yr) {
        $out[] = ['date' => $yr['d'], 'total' => (int)$yr['cnt'], 'content' => "<strong>{$yr['cnt']} 筆排程</strong>"];
      }
      echo json_encode($out, JSON_UNESCAPED_UNICODE);
      exit();
    }

    // 一般視圖區間查詢 (合併 fsl_schedules 與 fsl_course_lessons)
    if ($fromDate !== '' && $toDate !== '') {
      $whereSql .= " AND sch_start_at <= ? AND (sch_end_at >= ? OR sch_end_at IS NULL OR sch_msg_body LIKE '%\"recurrence\"%')";
      $binds[]   = substr($toDate, 0, 10) . ' 23:59:59';
      $binds[]   = substr($fromDate, 0, 10) . ' 00:00:00';
    }
    $rows   = DB->query("SELECT * FROM fsl_schedules {$whereSql} ORDER BY sch_start_at ASC", $binds);
    $merged = _fn_format_bscal_rows($rows);

    if (empty($calIds) || in_array('course', $calIds, true)) {
      $lWhere = " WHERE less_status != 9 ";
      $lBinds = [];
      if ($fromDate !== '' && $toDate !== '') {
        $lWhere .= " AND less_start_at <= ? AND less_end_at >= ? ";
        $lBinds[] = substr($toDate, 0, 10) . ' 23:59:59';
        $lBinds[] = substr($fromDate, 0, 10) . ' 00:00:00';
      }
      $lRows  = DB->query("SELECT * FROM fsl_course_lessons {$lWhere} ORDER BY less_start_at ASC", $lBinds);
      $merged = array_merge($merged, _fn_format_lesson_rows($lRows));
    }
    echo json_encode($merged, JSON_UNESCAPED_UNICODE);
    exit();
  break;
  // =========================================================================
  // 9. bs-calendar Modal 快速新增 / 拖曳改期儲存 (func_bs_cal_save)
  // =========================================================================
  case 'func_bs_cal_save':
    // 處理日曆排程建立與編輯更新邏輯（詳見原始碼檔案 5sladmin/funcFiles/sch_act_func.php）
    $rawId = trim((string)($_POST['id'] ?? ''));
    $title = preg_replace('/^(?:🔔|📚\s*\[[^\]]+\])\s*/u', '', trim((string)($_POST['title'] ?? '')));
    $start = trim((string)($_POST['start'] ?? ''));
    $end   = trim((string)($_POST['end'] ?? ''));

    // 若編輯或拖曳改期的是「課堂 (less_15)」-> 同步更新 fsl_course_lessons 與對應課前提醒
    if (str_starts_with($rawId, 'less_')) {
      $lessId = (int)substr($rawId, 5);
      $updLess = [
        'less_title'    => $title,
        'less_start_at' => $start,
        'less_end_at'   => ($end !== '' ? $end : $start)
      ];
      DB->update('fsl_course_lessons', $updLess, ['less_id' => $lessId]);

      $newTrig = date('Y-m-d H:i:s', strtotime($start . ' -1 day'));
      DB->query(
        "UPDATE fsl_schedules SET sch_title = ?, sch_start_at = ?, sch_end_at = ?, sch_trigger_at = ? WHERE sch_source = 2 AND cses_id = ? AND sch_exec_status = 0",
        ["【課前提醒】{$title}", $newTrig, $start, $newTrig, $lessId]
      );
      echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '課堂時間與課前提醒已同步更新！', 'id' => $rawId], JSON_UNESCAPED_UNICODE);
      exit();
    }

    $id = (int)str_replace('sch_', '', $rawId);
    $start   = trim((string)($_POST['start'] ?? ''));
    $end     = trim((string)($_POST['end'] ?? ''));
    $allDay  = filter_var($_POST['allDay'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    $catId   = trim((string)($_POST['calendarId'] ?? 'admin')) ?: 'admin';
    $color   = trim((string)($_POST['color'] ?? '#3b82f6')) ?: '#3b82f6';
    if (preg_match('/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i', $color, $cm)) {
      $color = sprintf('#%02x%02x%02x', $cm[1], $cm[2], $cm[3]);
    }
    $color = substr($color, 0, 20);
    $desc    = (string)($_POST['description'] ?? '');
    $trigEn  = isset($_POST['trigger_enable']) ? (int)$_POST['trigger_enable'] : null;
    $recRaw  = $_POST['recurrence'] ?? null;
    $taskRaw = $_POST['task'] ?? null;
    $recVal  = is_string($recRaw) ? json_decode($recRaw, true) : $recRaw;
    $taskVal = is_string($taskRaw) ? json_decode($taskRaw, true) : $taskRaw;
    //(小提醒：若前端 Modal 的「地點 (location)」輸入多行文字時，readAppointmentModal 會轉成陣列並
    //由 callSchApi 轉成 JSON 字串，若需要支援多行陣列地點，也可以比照加上 
    $locRaw = $_POST['location'] ?? null;
    $locVal = (is_string($locRaw) && str_starts_with($locRaw, '[')) ? (json_decode($locRaw, true) ?: $locRaw) : $locRaw;

    if ($title === '' || $start === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請填寫標題與開始時間！']);
      exit();
    }

    $meta = array_filter([
      'recurrence' => $recVal,
      'task'       => $taskVal,
      'location'   => $locVal, //$_POST['location'] ?? null,
      'link'       => $_POST['link'] ?? null,
      'overlap'    => filter_var($_POST['overlap'] ?? false, FILTER_VALIDATE_BOOLEAN)
    ], fn($v) => $v !== null && $v !== '');
    $bodyPayload = $desc . (!empty($meta) ? "\n<!--BSCAL_META:" . json_encode($meta, JSON_UNESCAPED_UNICODE) . "-->" : '');

    $dataArr = [
      'sch_category' => $catId, 'sch_title' => $title, 'sch_color' => $color,
      'sch_start_at' => $start, 'sch_end_at' => ($end !== '' ? $end : null),
      'sch_all_day'  => $allDay, 'sch_msg_body' => $bodyPayload
    ];
    if ($trigEn !== null) {
      $dataArr['sch_trigger_enable'] = $trigEn;
      if ($trigEn === 1) $dataArr['sch_trigger_at'] = $start;
    }

    if (!isset($_POST['description'])) unset($dataArr['sch_msg_body']);

    if ($id > 0) {
      DB->update('fsl_schedules', $dataArr, ['sch_id' => $id]);
      echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '排程已更新！', 'id' => $id], JSON_UNESCAPED_UNICODE);
    } else {
      $dataArr['sch_source'] = 1;
      $dataArr['sch_exec_status'] = 0;
      DB->insert('fsl_schedules', $dataArr);
      echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '新排程已建立！', 'id' => (int)DB->lastInsertId()], JSON_UNESCAPED_UNICODE);
    }
    exit();
  break;

  // =========================================================================
  // 10. bs-calendar 切換任務完成狀態 (func_bs_cal_toggleTask)
  // =========================================================================
  case 'func_bs_cal_toggleTask':
    // 切換排程執行狀態邏輯（詳見原始碼檔案 5sladmin/funcFiles/sch_act_func.php）
    $rawId   = trim((string)($_POST['id'] ?? ''));
    $id      = (int)str_replace('sch_', '', $rawId);
    $checked = filter_var($_POST['checked'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $row     = DB->row("SELECT sch_msg_body FROM fsl_schedules WHERE sch_id = ?", [$id]);
    if (!$row) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到指定排程！']);
      exit();
    }
    $rawBody = (string)$row['sch_msg_body'];
    $meta    = [];
    if (preg_match('/<!--BSCAL_META:(.*?)-->/s', $rawBody, $m)) {
      $meta    = json_decode($m[1], true) ?: [];
      $rawBody = trim(preg_replace('/<!--BSCAL_META:.*?-->/s', '', $rawBody));
    }
    $meta['task']['checked'] = $checked;
    $newBody = $rawBody . "\n<!--BSCAL_META:" . json_encode($meta, JSON_UNESCAPED_UNICODE) . "-->";
    DB->update('fsl_schedules', ['sch_msg_body' => $newBody], ['sch_id' => $id]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '任務狀態已更新！'], JSON_UNESCAPED_UNICODE);
    exit();
  break;












  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
