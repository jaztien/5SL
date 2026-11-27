<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');

header('Content-Type: application/json; charset=utf-8');

$adId = (int)($_SESSION['SESS_AD_ID'] ?? 0);
$mbId = (int)($_SESSION['SESS_MB_ID'] ?? $_SESSION['mb_id'] ?? 0);

if ($adId <= 0 && $mbId <= 0) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '登入已逾時，請重新登入系統！']);
  exit();
}

$token    = $_POST['token'] ?? '';
$formAcce = trim((string)($_POST['formAcce'] ?? ''));
$tktID    = (int)($_POST['tktID'] ?? 0);

if ($adId > 0 && function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

/** 產生不重複工單編號 TK-YYYYMMDD-XXXX */
function _fn_gen_ticket_no() {
  $ymd    = date('Ymd');
  $prefix = "TK-{$ymd}-";
  $cnt    = (int)DB->single("SELECT COUNT(*) FROM fsl_service_tickets WHERE tkt_no LIKE ?", [$prefix . '%']);
  return $prefix . str_pad((string)($cnt + 1), 4, '0', STR_PAD_LEFT);
}

/** 寫入工單處理歷程明細 */
function _fn_add_ticket_reply($tktId, $senderType, $senderId, $senderName, $msg, $isInternal = 0) {
  DB->insert('fsl_ticket_replies', [
    'tkt_id'          => (int)$tktId,
    'sender_type'     => (int)$senderType,
    'sender_id'       => (int)$senderId,
    'sender_name'     => $senderName,
    'rep_message'     => $msg,
    'rep_is_internal' => (int)$isInternal
  ]);
}

switch ($formAcce) {

  // =========================================================================
  // 1. 建立新服務工單（前台會員立案 或 後台客服代客立案）
  // =========================================================================
  case 'func_srv_ticket_create':
    $targetMbId = ($adId > 0) ? (int)($_POST['mb_id'] ?? 0) : $mbId;
    $threadId   = !empty($_POST['thread_id']) ? (int)$_POST['thread_id'] : null;
    $ordId      = !empty($_POST['ord_id']) ? (int)$_POST['ord_id'] : null;
    $category   = trim((string)($_POST['tkt_category'] ?? 'general')) ?: 'general';
    $priority   = max(1, min(3, (int)($_POST['tkt_priority'] ?? ($category === 'refund' ? 3 : 1))));
    $subject    = trim((string)($_POST['tkt_subject'] ?? ''));
    $content    = trim((string)($_POST['tkt_content'] ?? ''));

    if ($targetMbId <= 0 || $subject === '' || $content === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫會員、工單主旨與問題詳細說明！']);
      exit();
    }

    $tktNo = _fn_gen_ticket_no();
    DB->insert('fsl_service_tickets', [
      'tkt_no'       => $tktNo,
      'mb_id'        => $targetMbId,
      'thread_id'    => $threadId,
      'ord_id'       => $ordId,
      'tkt_category' => $category,
      'tkt_priority' => $priority,
      'tkt_subject'  => $subject,
      'tkt_content'  => $content,
      'ad_id'        => ($adId > 0 ? $adId : null),
      'tkt_status'   => ($adId > 0 ? 1 : 0)
    ]);
    $newId = (int)DB->lastInsertId();

    $creatorName = ($adId > 0) ? ('客服人員 #' . $adId) : '會員本人';
    _fn_add_ticket_reply($newId, 3, ($adId ?: $targetMbId), $creatorName, "工單【{$tktNo}】已正式建立立案。", 0);

    // 若為「退費與帳務 (refund)」且關聯訂單，自動將訂單狀態標記為 5-退費申請中
    if ($category === 'refund' && $ordId > 0) {
      DB->query("UPDATE fsl_orders SET ord_status = 5, ord_refund_reason = ? WHERE ord_id = ? AND ord_status IN (2,3,4)", [
        "【工單 {$tktNo}】" . mb_substr($subject, 0, 120),
        $ordId
      ]);
    }

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => "服務工單【{$tktNo}】已建立！",
      'newID'   => $newId,
      'tkt_no'  => $tktNo
    ]);
    exit();
  break;

  // =========================================================================
  // 2. 客服認領 / 指派負責人
  // =========================================================================
  case 'func_srv_ticket_assign':
    if ($adId <= 0 || $tktID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的操作權限或工單 ID！']);
      exit();
    }
    $assignAdId = (int)($_POST['assign_ad_id'] ?? $adId);
    $adName     = trim((string)($_SESSION['SESS_AD_NAME'] ?? ('客服專員 #' . $assignAdId)));

    $tkt = DB->row("SELECT tkt_status FROM fsl_service_tickets WHERE tkt_id = ?", [$tktID]);
    if (!$tkt) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到指定工單！']);
      exit();
    }

    $upd = ['ad_id' => $assignAdId];
    if ((int)$tkt['tkt_status'] === 0) {
      $upd['tkt_status'] = 1; // 自動轉為「1-處理中」
    }
    DB->update('fsl_service_tickets', $upd, ['tkt_id' => $tktID]);
    _fn_add_ticket_reply($tktID, 3, $adId, $adName, "案件已指派由【{$adName}】接手處理。", 0);

    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '已認領/指派此服務工單！']);
    exit();
  break;

  // =========================================================================
  // 3. 送出工單雙向回覆 或 內部交接備註
  // =========================================================================
  case 'func_srv_ticket_reply':
    $msg        = trim((string)($_POST['rep_message'] ?? ''));
    $isInternal = ($adId > 0) ? ((int)($_POST['rep_is_internal'] ?? 0) === 1 ? 1 : 0) : 0;
    $nextStatus = isset($_POST['next_status']) && $_POST['next_status'] !== '' ? (int)$_POST['next_status'] : null;

    if ($tktID <= 0 || $msg === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請輸入回覆內容！']);
      exit();
    }

    $tkt = DB->row("SELECT * FROM fsl_service_tickets WHERE tkt_id = ?", [$tktID]);
    if (!$tkt) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到指定工單！']);
      exit();
    }

    if ($adId > 0) {
      $adName = trim((string)($_SESSION['SESS_AD_NAME'] ?? ('客服專員 #' . $adId)));
      _fn_add_ticket_reply($tktID, 2, $adId, $adName, $msg, $isInternal);

      $upd = [];
      if (empty($tkt['ad_id'])) $upd['ad_id'] = $adId;
      if ($nextStatus !== null) {
        $upd['tkt_status'] = $nextStatus;
        if ($nextStatus === 3) $upd['tkt_resolved_at'] = date('Y-m-d H:i:s');
      } elseif ($isInternal === 0 && (int)$tkt['tkt_status'] === 0) {
        $upd['tkt_status'] = 1;
      }
      if (!empty($upd)) {
        DB->update('fsl_service_tickets', $upd, ['tkt_id' => $tktID]);
      }

      // 若為公開回覆且有對應站內通知表，可同步推播站內通知給會員
      if ($isInternal === 0) {
        try {
          DB->insert('fsl_notifications', [
            'mb_id'      => (int)$tkt['mb_id'],
            'noti_type'  => 'service',
            'noti_title' => "客服工單回覆通知【{$tkt['tkt_no']}】",
            'noti_body'  => mb_substr($msg, 0, 150),
            'noti_link'  => "member_tickets.php?tktID={$tktID}"
          ]);
        } catch (Throwable $e) {
          // 若站內通知欄位略有差異則略過不影響工單主流程
        }
      }

      echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => $isInternal ? '內部交接備註已儲存！' : '回覆已發送並通知會員！']);
    } else {
      // 前台會員補充回覆
      if ((int)$tkt['mb_id'] !== $mbId) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無權限操作此工單！']);
        exit();
      }
      $mbName = trim((string)($_SESSION['SESS_MB_NAME'] ?? ('會員 #' . $mbId)));
      _fn_add_ticket_reply($tktID, 1, $mbId, $mbName, $msg, 0);

      // 若原狀態為「2-待會員回覆」，自動切回「1-處理中」提醒客服
      if ((int)$tkt['tkt_status'] === 2) {
        DB->update('fsl_service_tickets', ['tkt_status' => 1], ['tkt_id' => $tktID]);
      }
      echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '您的補充說明已送出！']);
    }
    exit();
  break;

  // =========================================================================
  // 4. 更新工單狀態 / 優先等級 / 內部總備註
  // =========================================================================
  case 'func_srv_ticket_update':
    if ($adId <= 0 || $tktID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的操作權限或工單 ID！']);
      exit();
    }
    $status   = (int)($_POST['tkt_status'] ?? 1);
    $priority = max(1, min(3, (int)($_POST['tkt_priority'] ?? 1)));
    $assignId = !empty($_POST['ad_id']) ? (int)$_POST['ad_id'] : null;
    $note     = (string)($_POST['tkt_internal_note'] ?? '');

    $upd = [
      'tkt_status'        => $status,
      'tkt_priority'      => $priority,
      'ad_id'             => $assignId,
      'tkt_internal_note' => $note
    ];
    if ($status === 3) {
      $upd['tkt_resolved_at'] = date('Y-m-d H:i:s');
    }

    DB->update('fsl_service_tickets', $upd, ['tkt_id' => $tktID]);

    $staLabels = [0 => '待受理', 1 => '處理中', 2 => '待會員回覆', 3 => '已結案', 9 => '已作廢取消'];
    $adName    = trim((string)($_SESSION['SESS_AD_NAME'] ?? ('客服專員 #' . $adId)));
    _fn_add_ticket_reply($tktID, 3, $adId, $adName, "工單狀態變更為【" . ($staLabels[$status] ?? '') . "】。", 0);

    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '工單狀態與處理設定已更新！']);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
