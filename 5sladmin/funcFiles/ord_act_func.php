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
$ordID    = (int)($_POST['ordID'] ?? 0);

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

switch ($formAcce) {

  // =========================================================================
  // 1. 更新收件人資訊、物流追蹤單號與訂單狀態 (func_ord_updateShipping)
  // =========================================================================
  case 'func_ord_updateShipping':
    if ($ordID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的訂單 ID！']);
      exit();
    }

    $ordRow = DB->row("SELECT ord_id, mb_id, ord_no, ord_status FROM fsl_orders WHERE ord_id = ?", array($ordID));
    if (!$ordRow) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到該筆訂單資料！']);
      exit();
    }

    $oldStatus  = (int)$ordRow['ord_status'];
    $newStatus  = (int)($formData['ord_status'] ?? $oldStatus);
    $carrier    = trim((string)($formData['ord_shipping_carrier'] ?? ''));
    $shippingNo = trim((string)($formData['ord_shipping_no'] ?? ''));
    $adminNote  = trim((string)($formData['ord_admin_note'] ?? ''));

    // 收件人敏感欄位加密儲存
    $recvName  = trim((string)($formData['ord_receiver_name'] ?? ''));
    $recvPhone = trim((string)($formData['ord_receiver_phone'] ?? ''));
    $recvAddr  = trim((string)($formData['ord_receiver_address'] ?? ''));

    $upd = array(
      'ord_status'           => $newStatus,
      'ord_shipping_carrier' => ($carrier !== '' ? $carrier : null),
      'ord_shipping_no'      => ($shippingNo !== '' ? $shippingNo : null),
      'ord_admin_note'       => ($adminNote !== '' ? $adminNote : null),
      'ord_receiver_name'    => ($recvName !== '' ? _fn_secure_crypto($recvName, 'en') : null),
      'ord_receiver_phone'   => ($recvPhone !== '' ? _fn_secure_crypto($recvPhone, 'en') : null),
      'ord_receiver_address' => ($recvAddr !== '' ? _fn_secure_crypto($recvAddr, 'en') : null)
    );

    // 若狀態首次切換為「3 - 已出貨配送中」，自動記錄出貨時間並推播通知給會員
    if ($oldStatus !== 3 && $newStatus === 3) {
      $upd['ord_shipped_at'] = date('Y-m-d H:i:s');
      $trackTxt = ($shippingNo !== '') ? "（物流：{$carrier}，追蹤單號：{$shippingNo}）" : "";
      DB->insert('fsl_notifications', array(
        'mb_id'       => (int)$ordRow['mb_id'],
        'no_category' => 2,
        'no_title'    => "【商品出貨通知】訂單 {$ordRow['ord_no']} 已寄出！",
        'no_content'  => "親愛的會員您好，您的訂單（{$ordRow['ord_no']}）已完成打包並交寄出貨{$trackTxt}，請留意近期包裹配送動態。"
      ));
    }


    DB->update('fsl_orders', $upd, ['ord_id' => $ordID]);

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => ($oldStatus !== 3 && $newStatus === 3) ? '訂單已更新為「已出貨」並發送出貨通知給會員！' : '訂單與物流配送資訊已儲存更新！'
    ]);
    exit();
  break;

  // =========================================================================
  // 2. 確認收款入帳並立即開立電子發票 (func_ord_issueInvoice)
  // =========================================================================
  case 'func_ord_issueInvoice':
    if ($ordID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的訂單 ID！']);
      exit();
    }

    $ordRow = DB->row("SELECT ord_id, mb_id, ord_no, ord_total, ord_status FROM fsl_orders WHERE ord_id = ?", array($ordID));
    if (!$ordRow) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到該筆訂單！']);
      exit();
    }

    $nowStr = date('Y-m-d H:i:s');
    $invNo  = 'EF-' . mt_rand(10000000, 99999999);
    $randCd = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

    DB->query(
      "UPDATE fsl_invoices SET inv_status = 1, inv_paid_at = COALESCE(inv_paid_at, ?), inv_issued_at = ?, inv_no = COALESCE(inv_no, ?), inv_random_code = COALESCE(inv_random_code, ?) WHERE ord_id = ?",
      array($nowStr, $nowStr, $invNo, $randCd, $ordID)
    );

    if ((int)$ordRow['ord_status'] === 1) {
      DB->update('fsl_orders', ['ord_status' => 2], ['ord_id' => $ordID]);
      DB->query("UPDATE fsl_course_enrolls SET enr_pay_status = 1 WHERE ord_id = ? AND enr_pay_status = 0", array($ordID));
    }

    DB->insert('fsl_notifications', array(
      'mb_id'       => (int)$ordRow['mb_id'],
      'no_category' => 2,
      'no_title'    => "【電子發票開立通知】訂單 {$ordRow['ord_no']}",
      'no_content'  => "親愛的會員您好，您的訂單（{$ordRow['ord_no']}，實付 NT$ " . number_format((float)$ordRow['ord_total']) . "）電子發票已正式開立，歡迎至「訂單與發票中心」檢視明細。"
    ));

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '電子發票已正式開立並同步更新訂單入帳狀態！'
    ]);
    exit();
  break;

  // =========================================================================
  // 3. 執行電子發票作廢 / 折讓 (func_ord_voidInvoice)
  // =========================================================================
  case 'func_ord_voidInvoice':
    if ($ordID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的訂單 ID！']);
      exit();
    }

    $nowStr = date('Y-m-d H:i:s');
    DB->update('fsl_invoices', ['inv_status' => 2, 'inv_voided_at' => $nowStr], ['ord_id' => $ordID]);

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '電子發票已執行作廢 / 折讓歸檔！'
    ]);
    exit();
  break;

  // =========================================================================
  // 4. 售後退換貨審核處理與自動退還 PTS 點數 (func_ord_refundAction)
  // =========================================================================
  case 'func_ord_refundAction':
    $refID      = (int)($_POST['refID'] ?? 0);
    $refundAct  = trim((string)($_POST['refundAct'] ?? ''));
    $adminReply = trim((string)($_POST['adminReply'] ?? ''));

    if ($ordID <= 0 || $refID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的訂單或售後申請 ID！']);
      exit();
    }

    $refRow = DB->row("
      SELECT r.*, o.mb_id, o.ord_no
      FROM fsl_refunds r
      INNER JOIN fsl_orders o ON r.ord_id = o.ord_id
      WHERE r.ref_id = ? AND r.ord_id = ?
    ", array($refID, $ordID));

    if (!$refRow) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到該筆售後退換貨申請紀錄！']);
      exit();
    }

    $mbId   = (int)$refRow['mb_id'];
    $nowStr = date('Y-m-d H:i:s');

    if ($refundAct === 'approve') {
      DB->update('fsl_refunds', [
        'ref_status'      => 2,
        'ref_admin_reply' => ($adminReply !== '' ? $adminReply : '審核通過，請依指示辦理退貨/後續流程')
      ], ['ref_id' => $refID]);

      DB->insert('fsl_notifications', array(
        'mb_id'       => $mbId,
        'no_category' => 2,
        'no_title'    => "【售後申請審核通過】單號 {$refRow['ref_no']}",
        'no_content'  => "親愛的會員您好，您的售後申請（{$refRow['ref_no']}）已審核通過。客服回覆：{$adminReply}"
      ));

      echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '已核准售後申請並通知會員！']);
      exit();

    } elseif ($refundAct === 'reject') {
      DB->update('fsl_refunds', [
        'ref_status'       => 4,
        'ref_admin_reply'  => ($adminReply !== '' ? $adminReply : '經審核不符合退換貨條件'),
        'ref_processed_at' => $nowStr
      ], ['ref_id' => $refID]);

      DB->update('fsl_orders', ['ord_status' => 4], ['ord_id' => $ordID]);

      DB->insert('fsl_notifications', array(
        'mb_id'       => $mbId,
        'no_category' => 2,
        'no_title'    => "【售後申請結果通知】單號 {$refRow['ref_no']}",
        'no_content'  => "親愛的會員您好，您的售後申請（{$refRow['ref_no']}）未能通過審核。說明：{$adminReply}"
      ));

      echo json_encode(['RTN' => 1, 'msgType' => 'info', 'msgStr' => '已駁回售後申請並通知會員！']);
      exit();

    } elseif ($refundAct === 'complete') {
      // 1. 更新退換貨申請為 3-已完成退款/換貨
      DB->update('fsl_refunds', [
        'ref_status'       => 3,
        'ref_admin_reply'  => ($adminReply !== '' ? $adminReply : '已完成退款作業與點數退還'),
        'ref_processed_at' => $nowStr
      ], ['ref_id' => $refID]);

      // 2. 更新訂單狀態為 6-已退費/取消，並同步作廢電子發票
      DB->update('fsl_orders', ['ord_status' => 6], ['ord_id' => $ordID]);
      DB->update('fsl_invoices', ['inv_status' => 2, 'inv_voided_at' => $nowStr], ['ord_id' => $ordID]);

      // 3. 若有折抵 PTS 點數，自動退還並寫入 fsl_points 流水帳與校準餘額
      $ptsReturn = (int)$refRow['ref_pts_return'];
      if ($ptsReturn > 0) {
        $ptInfo    = _fn_get_pointinfo($mbId);
        $newPoints = $ptInfo['available'] + $ptsReturn;
        DB->insert('fsl_points', array(
          'pt_date'       => $nowStr,
          'mb_id'         => $mbId,
          'pt_income'     => 1,
          'pt_type'       => 5,
          'pt_for'        => 2,
          'pt_forid'      => $ordID,
          'pt_trade'      => $ptsReturn,
          'pt_redeem'     => 0,
          'pt_freepoints' => 0,
          'pt_available'  => $newPoints,
          'pt_memo'       => "訂單 {$refRow['ord_no']} 退費退還折抵點數",
          'pt_status'     => 1
        ));
        _fn_get_pointinfo($mbId, true);
      }

      DB->insert('fsl_notifications', array(
        'mb_id'       => $mbId,
        'no_category' => 2,
        'no_title'    => "【退款完成通知】單號 {$refRow['ref_no']}",
        'no_content'  => "親愛的會員您好，您的售後退款（{$refRow['ref_no']}，金額 NT$ " . number_format((float)$refRow['ref_amount']) . ($ptsReturn > 0 ? "，退還 {$ptsReturn} PTS" : "") . "）已處理完畢。{$adminReply}"
      ));

      echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '已完成退款、作廢發票並自動退還折抵 PTS 點數！']);
      exit();
    }

    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的審核動作！']);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}