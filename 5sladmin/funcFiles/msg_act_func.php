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

$adID     = (int)$_SESSION['SESS_AD_ID'];
$token    = $_POST['token'] ?? '';
$formAcce = trim((string)($_POST['formAcce'] ?? ''));
$formData = (array)($_POST['arrDatA'] ?? array());
$msgID    = (int)($_POST['msgID'] ?? 0);

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

/**
 * 內部輔助：依篩選條件查詢符合的會員 ID 陣列
 */
function _msg_resolve_filter_members($lv, $cid, $sid) {
  $where  = " WHERE m.mb_status = 1 ";
  $params = array();

  if ($lv !== '') {
    $where .= " AND m.mb_level = ? ";
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

  $rows = DB->query("SELECT m.mb_id FROM fsl_members m {$where} ORDER BY m.mb_id ASC", $params);
  if (!is_array($rows)) return array();
  return array_map('intval', array_column($rows, 'mb_id'));
}

switch ($formAcce) {

  // =========================================================================
  // 1. 試算條件篩選符合會員人數 (func_msg_previewFilter)
  // =========================================================================
  case 'func_msg_previewFilter':
    $lv  = trim((string)($_POST['filter_mb_level'] ?? ''));
    $cid = trim((string)($_POST['filter_course_id'] ?? ''));
    $sid = trim((string)($_POST['filter_sess_id'] ?? ''));

    $ids = _msg_resolve_filter_members($lv, $cid, $sid);
    echo json_encode(['RTN' => 1, 'count' => count($ids)]);
    exit();
  break;

  // =========================================================================
  // 2. 後台主動發送新訊息 / 分眾公告至前台 (func_msg_addnew)
  // =========================================================================
  case 'func_msg_addnew':
    $msgCat     = (int)($formData['msg_category'] ?? 4);
    $targetMode = trim((string)($formData['target_mode'] ?? 'single'));
    $isPinned   = (int)($formData['msg_is_pinned'] ?? 0);
    $msgTitle   = trim((string)($formData['msg_title'] ?? ''));
    $msgContent = trim((string)($formData['msg_content'] ?? ''));
    $alsoNoti   = (int)($formData['also_notify_new'] ?? 0);

    if ($msgTitle === '' || $msgContent === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫訊息主旨與內文！']);
      exit();
    }

    $targetMbIds  = array();
    $targetFilter = null;

    if ($targetMode === 'single') {
      $singleId = (int)($formData['single_mb_id'] ?? 0);
      if ($singleId <= 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請選擇單一接收會員！']);
        exit();
      }
      $targetMbIds = array($singleId);

    } elseif ($targetMode === 'multi') {
      $selRaw   = trim((string)($formData['multi_mb_ids'] ?? ''));
      $txtRaw   = trim((string)($formData['multi_mb_text'] ?? ''));
      $combined = $selRaw . ',' . $txtRaw;
      $parts    = preg_split('/[,，\s]+/', $combined);
      foreach ($parts as $p) {
        $v = (int)trim($p);
        if ($v > 0) $targetMbIds[] = $v;
      }
      $targetMbIds = array_values(array_unique($targetMbIds));
      if (empty($targetMbIds)) {
        echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請至少選擇或輸入一位接收會員！']);
        exit();
      }

    } elseif ($targetMode === 'filter') {
      $lv  = trim((string)($formData['filter_mb_level'] ?? ''));
      $cid = trim((string)($formData['filter_course_id'] ?? ''));
      $sid = trim((string)($formData['filter_sess_id'] ?? ''));
      $targetFilter = array(
        'mb_level'  => ($lv !== '' ? (int)$lv : null),
        'course_id' => ($cid !== '' ? (int)$cid : null),
        'sess_id'   => ($sid !== '' ? (int)$sid : null)
      );
      $targetMbIds = _msg_resolve_filter_members($lv, $cid, $sid);
      if (empty($targetMbIds)) {
        echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '目前沒有任何有效會員符合您設定的篩選條件！']);
        exit();
      }

    } elseif ($targetMode === 'all') {
      $targetMbIds = array();
    } else {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的發送對象模式！']);
      exit();
    }

    $nowStr = date('Y-m-d H:i:s');
    DB->insert('fsl_messages', array(
      'parent_id'         => 0,
      'msg_created'       => $nowStr,
      'msg_category'      => $msgCat,
      'sender_type'       => 2, // 後台管理者
      'sender_id'         => $adID,
      'target_mode'       => $targetMode,
      'target_mb_ids'     => !empty($targetMbIds) ? json_encode($targetMbIds) : null,
      'target_filter'     => !empty($targetFilter) ? json_encode($targetFilter) : null,
      'msg_title'         => $msgTitle,
      'msg_content'       => $msgContent,
      'msg_is_pinned'     => $isPinned,
      'msg_reply_status'  => 1, // 後台主動發送
      'msg_last_reply_at' => $nowStr,
      'msg_status'        => 1
    ));
    $newID = (int)DB->lastInsertId();

    if ($alsoNoti === 1 && !empty($targetMbIds) && count($targetMbIds) <= 200) {
      foreach ($targetMbIds as $mid) {
        DB->insert('fsl_notifications', array(
          'mb_id'       => $mid,
          'no_category' => 1,
          'no_title'    => "【專屬訊息】{$msgTitle}",
          'no_content'  => mb_strimwidth(strip_tags($msgContent), 0, 120, '...', 'UTF-8') . "\n（請前往會員中心「訊息中心」查看完整內容）"
        ));
      }
    }

    $cntLabel = ($targetMode === 'all') ? '全體有效會員' : (count($targetMbIds) . ' 位會員');
    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => "訊息已成功發送給【{$cntLabel}】！",
      'newID'   => $newID
    ]);
    exit();
  break;

  // =========================================================================
  // 3. 後台針對對話串進行人工回覆 (func_msg_reply)
  // =========================================================================
  case 'func_msg_reply':
    if ($msgID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的對話串 ID！']);
      exit();
    }
    $replyContent = trim((string)($formData['reply_content'] ?? ''));
    $alsoNotify   = (int)($formData['also_notify'] ?? 0);

    if ($replyContent === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請輸入回覆內容！']);
      exit();
    }

    $rootMsg = DB->row("SELECT * FROM fsl_messages WHERE msg_id = ? AND parent_id = 0", array($msgID));
    if (!$rootMsg) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到原始對話串資料！']);
      exit();
    }

    $nowStr     = date('Y-m-d H:i:s');
    $targetMbId = 0;
    if ((int)$rootMsg['sender_type'] === 1) {
      $targetMbId = (int)$rootMsg['sender_id'];
    } elseif (!empty($rootMsg['target_mb_ids'])) {
      $arrIds = json_decode((string)$rootMsg['target_mb_ids'], true);
      if (is_array($arrIds) && !empty($arrIds)) {
        $targetMbId = (int)$arrIds[0];
      }
    }

    DB->insert('fsl_messages', array(
      'parent_id'         => $msgID,
      'msg_created'       => $nowStr,
      'msg_category'      => (int)$rootMsg['msg_category'],
      'sender_type'       => 2, // 後台管理者
      'sender_id'         => $adID,
      'target_mode'       => ($targetMbId > 0) ? 'single' : $rootMsg['target_mode'],
      'target_mb_ids'     => ($targetMbId > 0) ? json_encode(array($targetMbId)) : $rootMsg['target_mb_ids'],
      'msg_title'         => 'Re: ' . $rootMsg['msg_title'],
      'msg_content'       => $replyContent,
      'msg_reply_status'  => 1,
      'msg_last_reply_at' => $nowStr,
      'msg_status'        => 1
    ));

    DB->update('fsl_messages', array(
      'msg_reply_status'  => 1,
      'msg_last_reply_at' => $nowStr
    ), array('msg_id' => $msgID));

    if ($targetMbId > 0) {
      DB->query("UPDATE fsl_message_reads SET is_read = 0 WHERE msg_id = ? AND mb_id = ?", array($msgID, $targetMbId));

      if ($alsoNotify === 1) {
        DB->insert('fsl_notifications', array(
          'mb_id'       => $targetMbId,
          'no_category' => 1,
          'no_title'    => "【客服回覆通知】{$rootMsg['msg_title']}",
          'no_content'  => "人生第五季團隊已回覆您的訊息：《{$rootMsg['msg_title']}》，歡迎前往會員中心「訊息中心」查看完整對話。"
        ));
      }
    }

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '人工回覆已成功送出並更新對話狀態！'
    ]);
    exit();
  break;

  // =========================================================================
  // 4. 切換對話處理狀態 (func_msg_statusUpdate)
  // =========================================================================
  case 'func_msg_statusUpdate':
    if ($msgID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的訊息 ID！']);
      exit();
    }
    $replyStatus = (int)($_POST['replyStatus'] ?? 1);
    DB->update('fsl_messages', ['msg_reply_status' => $replyStatus], ['msg_id' => $msgID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '對話處理狀態已更新！']);
    exit();
  break;

  // =========================================================================
  // 5. 切換置頂狀態 (func_msg_pinToggle)
  // =========================================================================
  case 'func_msg_pinToggle':
    if ($msgID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的訊息 ID！']);
      exit();
    }
    $isPinned = (int)($_POST['isPinned'] ?? 0);
    DB->update('fsl_messages', ['msg_is_pinned' => $isPinned], ['msg_id' => $msgID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => $isPinned ? '已將此訊息設為置頂！' : '已取消置頂顯示！']);
    exit();
  break;

  // =========================================================================
  // 6. 軟刪除對話串 (func_msg_delete)
  // =========================================================================
  case 'func_msg_delete':
    if ($msgID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的訊息 ID！']);
      exit();
    }
    DB->query("UPDATE fsl_messages SET msg_status = 9 WHERE msg_id = ? OR parent_id = ?", array($msgID, $msgID));
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '對話串及相關回覆已成功刪除！']);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
