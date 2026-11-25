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
$noID     = (int)($_POST['noID'] ?? 0);

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

/**
 * 內部輔助：依篩選條件查詢符合的有效會員資料（含 mb_id 與加密 mb_name）
 */
function _noti_resolve_filter_members($lv, $cid, $sid) {
  $where  = " WHERE m.mb_status = 1 ";
  $params = array();

  if ($lv !== '') {
    $where .= " AND m.mb_level >= ? ";
    $params[] = (int)$lv;
  }
  if ($cid !== '' || $sid !== '') {
    $subWhere = " e.mb_id = m.mb_id AND e.enr_status IN (1, 2, 3) ";
    if ($cid !== '') {
      $subWhere .= " AND e.course_id = ? ";
      $params[]  = (int)$cid;
    }
    if ($sid !== '') {
      $subWhere .= " AND e.sess_id = ? ";
      $params[]  = (int)$sid;
    }
    $where .= " AND EXISTS (SELECT 1 FROM fsl_course_enrolls e WHERE {$subWhere}) ";
  }

  $rows = DB->query("SELECT m.mb_id, m.mb_name FROM fsl_members m {$where} ORDER BY m.mb_id ASC", $params);
  return is_array($rows) ? $rows : array();
}

switch ($formAcce) {

  // =========================================================================
  // 1. 試算條件篩選符合會員人數 (func_noti_previewFilter)
  // =========================================================================
  case 'func_noti_previewFilter':
    $lv  = trim((string)($_POST['filter_mb_level'] ?? ''));
    $cid = trim((string)($_POST['filter_course_id'] ?? ''));
    $sid = trim((string)($_POST['filter_sess_id'] ?? ''));

    $rows = _noti_resolve_filter_members($lv, $cid, $sid);
    echo json_encode(['RTN' => 1, 'count' => count($rows)]);
    exit();
  break;

  // =========================================================================
  // 2. 手動派發站內通知至 fsl_notifications (func_noti_addnew)
  // =========================================================================
  case 'func_noti_addnew':
    $noCat      = (int)($formData['no_category'] ?? 1);
    $targetMode = trim((string)($formData['target_mode'] ?? 'single'));
    $rawTitle   = trim((string)($formData['no_title'] ?? ''));
    $rawContent = trim((string)($formData['no_content'] ?? ''));
    $noLink     = trim((string)($formData['no_link'] ?? ''));
    $noMinLvl   = (int)($formData['no_minlvl'] ?? 0);

    if ($rawTitle === '' || $rawContent === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫通知主旨與內文內容！']);
      exit();
    }

    // 全站 / 等級公告：直接寫入一筆 mb_id = NULL 搭配 no_minlvl
    if ($targetMode === 'all') {
      DB->insert('fsl_notifications', array(
        'mb_id'       => null,
        'no_category' => $noCat,
        'no_title'    => str_replace('{會員姓名}', '貴賓', $rawTitle),
        'no_content'  => str_replace('{會員姓名}', '貴賓', $rawContent),
        'no_link'     => ($noLink !== '' ? $noLink : null),
        'no_minlvl'   => $noMinLvl
      ));
      echo json_encode([
        'RTN'     => 1,
        'msgType' => 'success',
        'msgStr'  => "全站分級公告（Lv.{$noMinLvl}+）已成功發布！"
      ]);
      exit();
    }

    $targetMembers = array(); // 元素格式：['mb_id' => int, 'mb_name' => string]

    if ($targetMode === 'single') {
      $singleId = (int)($formData['single_mb_id'] ?? 0);
      if ($singleId <= 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請選擇單一接收會員！']);
        exit();
      }
      $mRow = DB->row("SELECT mb_id, mb_name FROM fsl_members WHERE mb_id = ? AND mb_status = 1", array($singleId));
      if (!$mRow) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到該有效會員資料！']);
        exit();
      }
      $targetMembers[] = $mRow;

    } elseif ($targetMode === 'multi') {
      $selRaw   = trim((string)($formData['multi_mb_ids'] ?? ''));
      $txtRaw   = trim((string)($formData['multi_mb_text'] ?? ''));
      $combined = $selRaw . ',' . $txtRaw;
      $parts    = preg_split('/[,，\s]+/', $combined);
      $idList   = array();
      foreach ($parts as $p) {
        $v = (int)trim($p);
        if ($v > 0) $idList[] = $v;
      }
      $idList = array_values(array_unique($idList));
      if (empty($idList)) {
        echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請至少選擇或輸入一位接收會員！']);
        exit();
      }
      $placeholders  = implode(',', array_fill(0, count($idList), '?'));
      $mRows         = DB->query("SELECT mb_id, mb_name FROM fsl_members WHERE mb_status = 1 AND mb_id IN ({$placeholders}) ORDER BY mb_id ASC", $idList);
      $targetMembers = is_array($mRows) ? $mRows : array();

    } elseif ($targetMode === 'filter') {
      $lv  = trim((string)($formData['filter_mb_level'] ?? ''));
      $cid = trim((string)($formData['filter_course_id'] ?? ''));
      $sid = trim((string)($formData['filter_sess_id'] ?? ''));
      $targetMembers = _noti_resolve_filter_members($lv, $cid, $sid);

    } else {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的接收對象模式！']);
      exit();
    }

    if (empty($targetMembers)) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '目前沒有任何有效會員符合接收條件！']);
      exit();
    }

    $hasNameVar = (strpos($rawTitle, '{會員姓名}') !== false || strpos($rawContent, '{會員姓名}') !== false);
    $sentCount  = 0;

    foreach ($targetMembers as $tm) {
      $mid   = (int)$tm['mb_id'];
      $title = $rawTitle;
      $body  = $rawContent;

      if ($hasNameVar) {
        $decName = !empty($tm['mb_name']) ? _fn_secure_crypto($tm['mb_name'], 'de') : '';
        $mbLabel = ($decName !== '') ? $decName : '學員';
        $title   = str_replace('{會員姓名}', $mbLabel, $title);
        $body    = str_replace('{會員姓名}', $mbLabel, $body);
      }

      DB->insert('fsl_notifications', array(
        'mb_id'       => $mid,
        'no_category' => $noCat,
        'no_title'    => $title,
        'no_content'  => $body,
        'no_link'     => ($noLink !== '' ? $noLink : null),
        'no_minlvl'   => 0
      ));
      $sentCount++;
    }

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => "站內通知已成功派發給 {$sentCount} 位有效會員！"
    ]);
    exit();
  break;

  // =========================================================================
  // 3. 刪除單筆站內通知紀錄 (func_noti_delete)
  // =========================================================================
  case 'func_noti_delete':
    if ($noID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的通知 ID！']);
      exit();
    }
    DB->query("DELETE FROM fsl_notifications WHERE no_id = ?", array($noID));
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '該筆站內通知紀錄已成功刪除！']);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
