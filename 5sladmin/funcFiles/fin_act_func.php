<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
$formAcce = trim((string)($_REQUEST['formAcce'] ?? ''));

// =========================================================================
// 匯出模式（GET/POST 下載 CSV 會計傳票或發票清冊，需在 JSON Header 前攔截）
// =========================================================================
if ($formAcce === 'export_ledger_csv' || $formAcce === 'export_invoice_csv') {
  if (empty($_SESSION['SESS_AD_ID'])) {
    die('登入已逾時，請重新登入後台系統！');
  }

  $dateFrom = trim((string)($_GET['dateFrom'] ?? ''));
  $dateTo   = trim((string)($_GET['dateTo'] ?? ''));
  $kw       = trim((string)($_GET['kw'] ?? ''));

  while (ob_get_level() > 0) {
    ob_end_clean();
  }
  header('Content-Type: text/csv; charset=UTF-8');

  if ($formAcce === 'export_ledger_csv') {
    $dirFilter    = trim((string)($_GET['dir'] ?? ''));
    $sourceFilter = trim((string)($_GET['source'] ?? ''));
    $statusFilter = trim((string)($_GET['status'] ?? ''));

    $filename = 'FSL_會計進出帳傳票明細_' . date('Ymd_His') . '.csv';
    header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");

    fputcsv($out, [
      '傳票單號', '入帳日期', '收支方向', '帳務來源', '會計科目',
      '收付管道', '幣別', '收入金額(+)', '支出/退款金額(-)', '交易對象/會員',
      '關聯訂單編號', '外部憑證/金流序號/發票字軌', '摘要說明', '核帳狀態', '登錄時間'
    ]);

    $conds = ['1=1'];
    $binds = [];
    if ($dateFrom !== '') { $conds[] = 'l.led_tx_date >= ?'; $binds[] = $dateFrom; }
    if ($dateTo !== '')   { $conds[] = 'l.led_tx_date <= ?'; $binds[] = $dateTo; }
    if ($dirFilter !== '')    { $conds[] = 'l.led_dir = ?';    $binds[] = (int)$dirFilter; }
    if ($sourceFilter !== '') { $conds[] = 'l.led_source = ?'; $binds[] = (int)$sourceFilter; }
    if ($statusFilter !== '') { $conds[] = 'l.led_status = ?'; $binds[] = (int)$statusFilter; }
    if ($kw !== '') {
      $conds[] = '(l.led_no LIKE ? OR l.led_counterparty LIKE ? OR l.led_summary LIKE ? OR l.led_voucher_ref LIKE ? OR o.ord_no LIKE ?)';
      $like = "%{$kw}%";
      array_push($binds, $like, $like, $like, $like, $like);
    }

    $sql = "
      SELECT l.*, o.ord_no
      FROM fsl_fin_ledger l
      LEFT JOIN fsl_orders o ON l.ord_id = o.ord_id
      WHERE " . implode(' AND ', $conds) . "
      ORDER BY l.led_tx_date DESC, l.led_id DESC
    ";
    $rows = DB->query($sql, $binds);

    $srcMap = [1 => '線上金流收款', 2 => '訂單退款支出', 3 => '線下收入', 4 => '線下營運支出'];
    $catMap = [
      'course_fee' => '學費與商品收入',
      'refund'     => '退費支出',
      'lecturer'   => '講師鐘點費',
      'venue'      => '場地設備費',
      'marketing'  => '行銷雜支',
      'other'      => '其他收支'
    ];
    $staMap = [0 => '待核帳', 1 => '已核帳入帳', 9 => '已作廢沖銷'];

    foreach ((array)$rows as $r) {
      $isIn = ((int)$r['led_dir'] === 1);
      fputcsv($out, [
        $r['led_no'],
        $r['led_tx_date'],
        $isIn ? '收入 (+)' : '支出 (-)',
        $srcMap[(int)$r['led_source']] ?? '其他',
        $catMap[$r['led_category']] ?? $r['led_category'],
        $r['led_pay_channel'],
        $r['led_currency'] ?: 'TWD',
        $isIn ? number_format((float)$r['led_amount'], 2, '.', '') : '0.00',
        !$isIn ? number_format((float)$r['led_amount'], 2, '.', '') : '0.00',
        _fn_safe_dec_name($r['led_counterparty'] ?? ''),
        $r['ord_no'] ?? '',
        $r['led_voucher_ref'] ?? '',
        $r['led_summary'] ?? '',
        $staMap[(int)$r['led_status']] ?? '',
        $r['led_created']
      ]);
    }
  } else {
    $invSta = trim((string)($_GET['invStatus'] ?? ''));
    $filename = 'FSL_電子發票與金流對帳清冊_' . date('Ymd_His') . '.csv';
    header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");

    fputcsv($out, [
      '發票ID', '發票字軌號碼', '隨機碼', '發票類型', '統一編號', '公司抬頭',
      '載具類型', '載具條碼', '關聯訂單編號', '會員姓名', '支付管道', '付款方式',
      '金流交易序號', '卡號末四碼', '實付金額', '支付時間', '開立時間', '作廢時間', '發票狀態'
    ]);

    $conds = ['1=1'];
    $binds = [];
    if ($dateFrom !== '') { $conds[] = 'DATE(i.inv_created) >= ?'; $binds[] = $dateFrom; }
    if ($dateTo !== '')   { $conds[] = 'DATE(i.inv_created) <= ?'; $binds[] = $dateTo; }
    if ($invSta !== '')   { $conds[] = 'i.inv_status = ?';         $binds[] = (int)$invSta; }
    if ($kw !== '') {
      $conds[] = '(i.inv_no LIKE ? OR i.inv_trade_no LIKE ? OR i.inv_tax_id LIKE ? OR i.inv_company_title LIKE ? OR o.ord_no LIKE ? OR m.mb_name LIKE ?)';
      $like = "%{$kw}%";
      array_push($binds, $like, $like, $like, $like, $like, $like);
    }

    $sql = "
      SELECT i.*, o.ord_no, m.mb_name
      FROM fsl_invoices i
      LEFT JOIN fsl_orders o ON i.ord_id = o.ord_id
      LEFT JOIN fsl_members m ON i.mb_id = m.mb_id
      WHERE " . implode(' AND ', $conds) . "
      ORDER BY i.inv_id DESC
    ";
    $rows = DB->query($sql, $binds);

    $typeMap    = [1 => '二聯式個人', 2 => '三聯式公司統編', 3 => '捐贈發票'];
    $carrierMap = [0 => '會員載具', 1 => '手機條碼', 2 => '自然人憑證'];
    $staMap     = [0 => '待付款未開立', 1 => '已正式開立', 2 => '已開立折讓單', 9 => '已作廢'];

    foreach ((array)$rows as $r) {
      fputcsv($out, [
        $r['inv_id'],
        $r['inv_no'] ?? '尚未開立',
        $r['inv_random_code'] ?? '',
        $typeMap[(int)$r['inv_type']] ?? '二聯式',
        $r['inv_tax_id'] ?? '',
        $r['inv_company_title'] ?? '',
        $carrierMap[(int)$r['inv_carrier_type']] ?? '會員載具',
        $r['inv_carrier_code'] ?? '',
        $r['ord_no'] ?? '',
        (_fn_safe_dec_name($r['mb_name'] ?? '') ?: ('會員#' . $r['mb_id'])),
        $r['inv_pay_gateway'],
        $r['inv_pay_method'],
        $r['inv_trade_no'] ?? '',
        $r['inv_card_last4'] ?? '',
        number_format((float)$r['inv_paid_amount'], 2, '.', ''),
        $r['inv_paid_at'] ?? '',
        $r['inv_issued_at'] ?? '',
        $r['inv_voided_at'] ?? '',
        $staMap[(int)$r['inv_status']] ?? ''
      ]);
    }
  }

  fclose($out);
  exit();
}

// =========================================================================
// 一般 JSON AJAX API 模式
// =========================================================================
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['SESS_AD_ID'])) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '登入已逾時，請重新登入後台系統！']);
  exit();
}

$token    = $_POST['token'] ?? '';
$formData = (array)($_POST['arrDatA'] ?? []);
$ledID    = (int)($_POST['ledID'] ?? 0);
$invID    = (int)($_POST['invID'] ?? 0);

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

/** 產生不重複傳票單號 FIN-YYYYMMDD-XXXX */
function _fn_gen_ledger_no($txDate) {
  $ymd = str_replace('-', '', substr($txDate, 0, 10));
  if (strlen($ymd) !== 8) $ymd = date('Ymd');
  $prefix = "FIN-{$ymd}-";
  $cnt = (int)DB->single("SELECT COUNT(*) FROM fsl_fin_ledger WHERE led_no LIKE ?", [$prefix . '%']);
  return $prefix . str_pad((string)($cnt + 1), 4, '0', STR_PAD_LEFT);
}

switch ($formAcce) {

  // =========================================================================
  // 1. 新增 / 更新財會進出帳傳票 (線下收支 / 退款 / 核帳登錄)
  // =========================================================================
  case 'func_fin_ledger_addnew':
  case 'func_fin_ledger_update':
    $isNew   = ($formAcce === 'func_fin_ledger_addnew');
    $txDate  = trim((string)($formData['led_tx_date'] ?? date('Y-m-d')));
    $dir     = ((int)($formData['led_dir'] ?? 1) === -1) ? -1 : 1;
    $source  = (int)($formData['led_source'] ?? ($dir === 1 ? 3 : 4));
    $cat     = trim((string)($formData['led_category'] ?? ($dir === 1 ? 'course_fee' : 'other')));
    $channel = trim((string)($formData['led_pay_channel'] ?? 'BankTransfer'));
    $curr    = trim((string)($formData['led_currency'] ?? 'TWD')) ?: 'TWD';
    $amount  = round((float)($formData['led_amount'] ?? 0), 2);
    $party   = trim((string)($formData['led_counterparty'] ?? ''));
    $summary = trim((string)($formData['led_summary'] ?? ''));
    $voucher = trim((string)($formData['led_voucher_ref'] ?? ''));
    $status  = (int)($formData['led_status'] ?? 1);
    $ordId   = !empty($formData['ord_id']) ? (int)$formData['ord_id'] : null;
    $invId   = !empty($formData['inv_id']) ? (int)$formData['inv_id'] : null;
    $mbId    = !empty($formData['mb_id'])  ? (int)$formData['mb_id']  : null;

    if ($txDate === '' || $amount <= 0 || $summary === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫入帳日期、有效交易金額與摘要說明！']);
      exit();
    }

    $dataArr = [
      'led_tx_date'      => $txDate,
      'led_dir'          => $dir,
      'led_source'       => $source,
      'led_category'     => $cat,
      'ord_id'           => $ordId,
      'inv_id'           => $invId,
      'mb_id'            => $mbId,
      'led_pay_channel'  => $channel,
      'led_currency'     => $curr,
      'led_amount'       => $amount,
      'led_counterparty' => ($party !== '' ? $party : null),
      'led_summary'      => $summary,
      'led_voucher_ref'  => ($voucher !== '' ? $voucher : null),
      'led_status'       => $status,
      'ad_id'            => (int)$_SESSION['SESS_AD_ID']
    ];

    if ($isNew) {
      $dataArr['led_no'] = _fn_gen_ledger_no($txDate);
      DB->insert('fsl_fin_ledger', $dataArr);
      $newID = (int)DB->lastInsertId();

      // 若為「訂單退款支出 (source=2)」且有指定訂單，同步將訂單標記為已退費
      if ($source === 2 && $ordId > 0) {
        DB->update('fsl_orders', [
          'ord_status'        => 6,
          'ord_refund_reason' => $summary,
          'ord_refunded_at'   => date('Y-m-d H:i:s')
        ], ['ord_id' => $ordId]);
      }

      echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => "會計傳票【{$dataArr['led_no']}】已登錄完成！", 'newID' => $newID]);
    } else {
      if ($ledID <= 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的傳票 ID！']);
        exit();
      }
      DB->update('fsl_fin_ledger', $dataArr, ['led_id' => $ledID]);
      echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '傳票資料已更新！']);
    }
    exit();
  break;

  // =========================================================================
  // 2. 快速核帳入帳 / 作廢沖銷單筆傳票
  // =========================================================================
  case 'func_fin_ledger_verify':
    if ($ledID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的傳票 ID！']);
      exit();
    }
    DB->update('fsl_fin_ledger', [
      'led_status' => 1,
      'ad_id'      => (int)$_SESSION['SESS_AD_ID']
    ], ['led_id' => $ledID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '該筆帳務已完成核帳入帳！']);
    exit();
  break;

  case 'func_fin_ledger_delete':
    if ($ledID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的傳票 ID！']);
      exit();
    }
    DB->update('fsl_fin_ledger', [
      'led_status' => 9,
      'ad_id'      => (int)$_SESSION['SESS_AD_ID']
    ], ['led_id' => $ledID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '該筆傳票已作廢沖銷！']);
    exit();
  break;

  // =========================================================================
  // 3. 電子發票開立 / 折讓單(部分退費) / 作廢(全額退費) + 總流水帳連動
  // =========================================================================
  case 'func_fin_inv_issue':
    $invNo      = strtoupper(trim((string)($_POST['inv_no'] ?? '')));
    $randomCode = trim((string)($_POST['inv_random_code'] ?? ''));
    $invType    = (int)($_POST['inv_type'] ?? 1);
    $taxId      = trim((string)($_POST['inv_tax_id'] ?? ''));
    $compTitle  = trim((string)($_POST['inv_company_title'] ?? ''));

    if ($invID <= 0 || $invNo === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請輸入正確的電子發票字軌號碼（如 EF-99887766）！']);
      exit();
    }
    if ($randomCode === '') {
      $randomCode = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }
    if ($invType === 2 && ($taxId === '' || $compTitle === '')) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '三聯式發票請填寫公司統一編號與發票抬頭！']);
      exit();
    }

    $inv = DB->row("SELECT i.*, o.ord_no, o.ord_currency, m.mb_name FROM fsl_invoices i LEFT JOIN fsl_orders o ON i.ord_id = o.ord_id LEFT JOIN fsl_members m ON i.mb_id = m.mb_id WHERE i.inv_id = ?", [$invID]);
    if (!$inv) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到指定的發票紀錄！']);
      exit();
    }

    $now = date('Y-m-d H:i:s');
    DB->update('fsl_invoices', [
      'inv_no'            => $invNo,
      'inv_random_code'   => substr($randomCode, 0, 4),
      'inv_type'          => $invType,
      'inv_tax_id'        => ($invType === 2 ? $taxId : null),
      'inv_company_title' => ($invType === 2 ? $compTitle : null),
      'inv_paid_at'       => $inv['inv_paid_at'] ?: $now,
      'inv_issued_at'     => $now,
      'inv_status'        => 1
    ], ['inv_id' => $invID]);

    // 同步確認總流水帳已有對應收入紀錄（若無則自動補建一筆）
    $hasLed = (int)DB->single("SELECT COUNT(*) FROM fsl_fin_ledger WHERE inv_id = ? AND led_dir = 1 AND led_status != 9", [$invID]);
    if ($hasLed === 0 && (float)$inv['inv_paid_amount'] > 0) {
      DB->insert('fsl_fin_ledger', [
        'led_tx_date'      => date('Y-m-d'),
        'led_no'           => _fn_gen_ledger_no(date('Y-m-d')),
        'led_dir'          => 1,
        'led_source'       => 1,
        'led_category'     => 'course_fee',
        'ord_id'           => (int)$inv['ord_id'],
        'inv_id'           => $invID,
        'mb_id'            => (int)$inv['mb_id'],
        'led_pay_channel'  => $inv['inv_pay_gateway'],
        'led_currency'     => $inv['ord_currency'] ?: 'TWD',
        'led_amount'       => (float)$inv['inv_paid_amount'],
        'led_counterparty' => $inv['mb_name'] ?: ('會員 #' . $inv['mb_id']),
        'led_summary'      => '開立電子發票收款入帳（訂單：' . ($inv['ord_no'] ?? $inv['ord_id']) . '）',
        'led_voucher_ref'  => trim(($inv['inv_trade_no'] ? $inv['inv_trade_no'] . ' / ' : '') . $invNo),
        'led_status'       => 1,
        'ad_id'            => (int)$_SESSION['SESS_AD_ID']
      ]);
    }

    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => "電子發票【{$invNo}】已正式開立並完成入帳！"]);
    exit();
  break;

  case 'func_fin_inv_allowance':
  case 'func_fin_inv_void':
    $isVoid    = ($formAcce === 'func_fin_inv_void');
    $refundAmt = round((float)($_POST['refund_amount'] ?? 0), 2);
    $reason    = trim((string)($_POST['refund_reason'] ?? ($isVoid ? '全額退費作廢發票' : '部分退費開立折讓單')));

    $inv = DB->row("SELECT i.*, o.ord_no, o.ord_currency, m.mb_name FROM fsl_invoices i LEFT JOIN fsl_orders o ON i.ord_id = o.ord_id LEFT JOIN fsl_members m ON i.mb_id = m.mb_id WHERE i.inv_id = ?", [$invID]);
    if (!$inv) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到指定的發票紀錄！']);
      exit();
    }

    if ($isVoid) {
      $refundAmt = (float)$inv['inv_paid_amount'];
    } elseif ($refundAmt <= 0 || $refundAmt > (float)$inv['inv_paid_amount']) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '折讓退費金額必須大於 0 且不可超過原實付金額！']);
      exit();
    }

    $now = date('Y-m-d H:i:s');
    $updInv = ['inv_status' => ($isVoid ? 9 : 2)];
    if ($isVoid) $updInv['inv_voided_at'] = $now;
    DB->update('fsl_invoices', $updInv, ['inv_id' => $invID]);

    // 同步更新訂單狀態為「6-已退費」
    if ((int)$inv['ord_id'] > 0) {
      DB->update('fsl_orders', [
        'ord_status'        => 6,
        'ord_refund_reason' => $reason,
        'ord_refunded_at'   => $now
      ], ['ord_id' => (int)$inv['ord_id']]);
    }

    // 自動產生一筆「訂單退款支出 (-1)」會計傳票至總流水帳
    if ($refundAmt > 0) {
      $ledNo = _fn_gen_ledger_no(date('Y-m-d'));
      DB->insert('fsl_fin_ledger', [
        'led_tx_date'      => date('Y-m-d'),
        'led_no'           => $ledNo,
        'led_dir'          => -1,
        'led_source'       => 2,
        'led_category'     => 'refund',
        'ord_id'           => (int)$inv['ord_id'],
        'inv_id'           => $invID,
        'mb_id'            => (int)$inv['mb_id'],
        'led_pay_channel'  => $inv['inv_pay_gateway'],
        'led_currency'     => $inv['ord_currency'] ?: 'TWD',
        'led_amount'       => $refundAmt,
        'led_counterparty' => $inv['mb_name'] ?: ('會員 #' . $inv['mb_id']),
        'led_summary'      => ($isVoid ? '【發票作廢全額退費】' : '【發票折讓部分退費】') . $reason,
        'led_voucher_ref'  => $inv['inv_no'] ?: ($inv['inv_trade_no'] ?? ''),
        'led_status'       => 1,
        'ad_id'            => (int)$_SESSION['SESS_AD_ID']
      ]);
    }

    $msg = $isVoid ? '發票已作廢，並自動產生全額退費支出傳票！' : "折讓單已開立（退費 NT$ " . number_format($refundAmt) . "），並自動寫入總流水帳！";
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => $msg]);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
