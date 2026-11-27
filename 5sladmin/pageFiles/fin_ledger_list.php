<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
// 定義本頁面代碼（統一進出帳總帳與結算中心）
define('PAGE_MARK', 'PG_FIN_LEDGER');
define('PAGE_ITEM', 'ITEM_FIN');
define('PAGE_OPANEL', 1); // 1-新增線下收支/退款傳票
define('DRAWER_OPANEL', 1);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$csrfToken = function_exists('_fn_genToken') ? _fn_genToken() : ($_SESSION['token'] ?? '');

// 篩選參數（預設顯示本月 1 日至今日）
$dateFrom     = trim((string)($_GET['dateFrom'] ?? date('Y-m-01')));
$dateTo       = trim((string)($_GET['dateTo'] ?? date('Y-m-d')));
$dirFilter    = trim((string)($_GET['dir'] ?? ''));
$sourceFilter = trim((string)($_GET['source'] ?? ''));
$catFilter    = trim((string)($_GET['cat'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? ''));
$kw           = trim((string)($_GET['kw'] ?? ''));
$printMode    = ((int)($_GET['print'] ?? 0) === 1);

$conds = ['1=1'];
$binds = [];
if ($dateFrom !== '') {
  $conds[] = 'l.led_tx_date >= ?';
  $binds[] = $dateFrom;
}
if ($dateTo !== '') {
  $conds[] = 'l.led_tx_date <= ?';
  $binds[] = $dateTo;
}
if ($dirFilter !== '') {
  $conds[] = 'l.led_dir = ?';
  $binds[] = (int)$dirFilter;
}
if ($sourceFilter !== '') {
  $conds[] = 'l.led_source = ?';
  $binds[] = (int)$sourceFilter;
}
if ($catFilter !== '') {
  $conds[] = 'l.led_category = ?';
  $binds[] = $catFilter;
}
if ($statusFilter !== '') {
  $conds[] = 'l.led_status = ?';
  $binds[] = (int)$statusFilter;
}
if ($kw !== '') {
  $conds[] = '(l.led_no LIKE ? OR l.led_counterparty LIKE ? OR l.led_summary LIKE ? OR l.led_voucher_ref LIKE ? OR o.ord_no LIKE ? OR i.inv_no LIKE ?)';
  $like = "%{$kw}%";
  array_push($binds, $like, $like, $like, $like, $like, $like);
}
$whereSql = ' WHERE ' . implode(' AND ', $conds);

// 1. 本期會計結算 KPI 統計（排除 9-已作廢沖銷）
$kpiSql = "
  SELECT
    COALESCE(SUM(CASE WHEN l.led_status != 9 AND l.led_dir = 1  THEN l.led_amount ELSE 0 END), 0) AS total_in,
    COALESCE(SUM(CASE WHEN l.led_status != 9 AND l.led_dir = -1 THEN l.led_amount ELSE 0 END), 0) AS total_out,
    COALESCE(SUM(CASE WHEN l.led_status = 0 THEN 1 ELSE 0 END), 0) AS pending_cnt,
    COALESCE(SUM(CASE WHEN l.led_status = 1 THEN 1 ELSE 0 END), 0) AS verified_cnt
  FROM fsl_fin_ledger l
  LEFT JOIN fsl_orders o ON l.ord_id = o.ord_id
  LEFT JOIN fsl_invoices i ON l.inv_id = i.inv_id
  {$whereSql}
";
$kpi = DB->row($kpiSql, $binds) ?: ['total_in' => 0, 'total_out' => 0, 'pending_cnt' => 0, 'verified_cnt' => 0];
$netCashFlow = (float)$kpi['total_in'] - (float)$kpi['total_out'];

// 2. 分頁與明細查詢（列印模式則輸出全部筆數）
$page     = max(1, (int)($_GET['page'] ?? 1));
$pageSize = $printMode ? 2000 : 25;
$offset   = ($page - 1) * $pageSize;

$totalRows = (int)DB->single("
  SELECT COUNT(*)
  FROM fsl_fin_ledger l
  LEFT JOIN fsl_orders o ON l.ord_id = o.ord_id
  LEFT JOIN fsl_invoices i ON l.inv_id = i.inv_id
  {$whereSql}
", $binds);
$totalPages = max(1, (int)ceil($totalRows / $pageSize));

$listSql = "
  SELECT l.*, o.ord_no, i.inv_no, i.inv_status AS invoice_status
  FROM fsl_fin_ledger l
  LEFT JOIN fsl_orders o ON l.ord_id = o.ord_id
  LEFT JOIN fsl_invoices i ON l.inv_id = i.inv_id
  {$whereSql}
  ORDER BY l.led_tx_date DESC, l.led_id DESC
  LIMIT {$offset}, {$pageSize}
";
$rows = DB->query($listSql, $binds) ?: [];

$srcBadges = [
  1 => ['label' => '線上金流收款', 'cls' => 'text-bg-primary'],
  2 => ['label' => '訂單退款支出', 'cls' => 'text-bg-danger'],
  3 => ['label' => '線下收入',     'cls' => 'text-bg-success'],
  4 => ['label' => '線下營運支出', 'cls' => 'text-bg-warning']
];
$catLabels = [
  'course_fee' => '學費與商品收入',
  'refund'     => '退費支出',
  'lecturer'   => '講師鐘點費',
  'venue'      => '場地設備費',
  'marketing'  => '行銷雜支',
  'other'      => '其他收支'
];
$queryWithoutPage = $_GET;
unset($queryWithoutPage['page'], $queryWithoutPage['print']);
$qsBase = http_build_query($queryWithoutPage);
?>
<!doctype html>
<html lang="zh-Hant-TW">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Fifth Season Life 人生第五季 | 統一進出帳與會計結算中心</title>
  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
  <style>
    @media print {
      .no-print {
        display: none !important;
      }

      body {
        background: #fff !important;
        color: #000 !important;
        padding: 0 !important;
      }

      .card {
        border: 1px solid #ccc !important;
        box-shadow: none !important;
      }

      .table {
        font-size: 12px !important;
      }
    }
  </style>
</head>

<body class="bg-body-tertiary theme-cyan p-2">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1 no-print">
    <i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?>
  </div>

  <div class="container-fluid py-2">
    <!-- 頂部功能切換列 -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
      <div class="d-flex align-items-center gap-2">
        <h5 class="mb-0 fw-bold"><i class="bi bi-cash-coin text-primary me-2"></i>財會總帳與進出帳結算中心</h5>
        <div class="btn-group btn-group-sm ms-2" role="group">
          <a href="fin_ledger_list.php" class="btn btn-primary active"><i class="bi bi-journal-text me-1"></i>進出帳總流水帳</a>
          <a href="fin_invoice_list.php" class="btn btn-outline-primary"><i class="bi bi-receipt-cutoff me-1"></i>電子發票與對帳管理</a>
        </div>
      </div>

      <div class="d-flex flex-wrap gap-2">
        <a href="fin_ledger_detail.php?currID=0" class="btn btn-success btn-sm fw-bold">
          <i class="bi bi-plus-circle me-1"></i>登錄線下收支 / 退款
        </a>
        <a href="../funcFiles/fin_act_func.php?formAcce=export_ledger_csv&<?php echo $qsBase; ?>" class="btn btn-outline-secondary btn-sm fw-bold">
          <i class="bi bi-file-earmark-excel text-success me-1"></i>匯出會計傳票 CSV
        </a>
        <a href="fin_ledger_list.php?<?php echo $qsBase; ?>&print=1" target="_blank" class="btn btn-outline-dark btn-sm fw-bold">
          <i class="bi bi-printer me-1"></i>列印結算總表單據
        </a>
      </div>
    </div>

    <?php if ($printMode): ?>
      <div class="border-bottom border-2 border-dark pb-2 mb-3">
        <div class="d-flex justify-content-between align-items-end">
          <div>
            <h4 class="fw-bold mb-1">人生第五季 Fifth Season Life — 會計收支結算總表</h4>
            <div class="small text-muted">結算期間：<?php echo htmlspecialchars($dateFrom ?: '期初'); ?> 至 <?php echo htmlspecialchars($dateTo ?: '至今'); ?> ｜ 製表時間：<?php echo date('Y-m-d H:i'); ?></div>
          </div>
          <button type="button" class="btn btn-primary btn-sm no-print" onclick="window.print()"><i class="bi bi-printer me-1"></i>立即列印 / 存成 PDF</button>
        </div>
      </div>
    <?php endif; ?>

    <!-- 期間會計結算 KPI 看板 -->
    <div class="row g-3 mb-3">
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
          <div class="card-body py-2 px-3">
            <div class="small text-muted fw-semibold">本期總收入 (線上 + 線下)</div>
            <div class="fs-4 fw-bold text-success mt-1">+ NT$ <?php echo number_format((float)$kpi['total_in']); ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
          <div class="card-body py-2 px-3">
            <div class="small text-muted fw-semibold">本期退款與營運支出 (-)</div>
            <div class="fs-4 fw-bold text-danger mt-1">- NT$ <?php echo number_format((float)$kpi['total_out']); ?></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
          <div class="card-body py-2 px-3">
            <div class="small text-muted fw-semibold">本期淨現金流結餘</div>
            <div class="fs-4 fw-bold <?php echo $netCashFlow >= 0 ? 'text-primary' : 'text-danger'; ?> mt-1">
              <?php echo ($netCashFlow >= 0 ? '+ ' : '- ') . 'NT$ ' . number_format(abs($netCashFlow)); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
          <div class="card-body py-2 px-3">
            <div class="small text-muted fw-semibold">核帳進度（已入帳 / 待核帳）</div>
            <div class="fs-5 fw-bold mt-1">
              <span class="text-success"><?php echo (int)$kpi['verified_cnt']; ?> 筆已核</span> /
              <span class="text-warning"><?php echo (int)$kpi['pending_cnt']; ?> 筆待核</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 多維度篩選列 -->
    <div class="card border-0 shadow-sm mb-3 no-print">
      <div class="card-body py-2 px-3">
        <form method="get" class="row g-2 align-items-end">
          <div class="col-6 col-md-2">
            <label class="form-label small mb-1 text-muted">入帳起日</label>
            <input type="date" name="dateFrom" class="form-control form-control-sm" value="<?php echo htmlspecialchars($dateFrom); ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label small mb-1 text-muted">入帳迄日</label>
            <input type="date" name="dateTo" class="form-control form-control-sm" value="<?php echo htmlspecialchars($dateTo); ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label small mb-1 text-muted">帳務來源</label>
            <select name="source" class="form-select form-select-sm">
              <option value="">全部來源</option>
              <option value="1" <?php echo $sourceFilter === '1' ? 'selected' : ''; ?>>1-線上金流收款</option>
              <option value="2" <?php echo $sourceFilter === '2' ? 'selected' : ''; ?>>2-訂單退款支出</option>
              <option value="3" <?php echo $sourceFilter === '3' ? 'selected' : ''; ?>>3-線下收入</option>
              <option value="4" <?php echo $sourceFilter === '4' ? 'selected' : ''; ?>>4-線下營運支出</option>
            </select>
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label small mb-1 text-muted">會計科目</label>
            <select name="cat" class="form-select form-select-sm">
              <option value="">全部科目</option>
              <?php foreach ($catLabels as $cKey => $cName): ?>
                <option value="<?php echo $cKey; ?>" <?php echo $catFilter === $cKey ? 'selected' : ''; ?>><?php echo $cName; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6 col-md-1">
            <label class="form-label small mb-1 text-muted">狀態</label>
            <select name="status" class="form-select form-select-sm">
              <option value="">全部</option>
              <option value="1" <?php echo $statusFilter === '1' ? 'selected' : ''; ?>>已核帳</option>
              <option value="0" <?php echo $statusFilter === '0' ? 'selected' : ''; ?>>待核帳</option>
              <option value="9" <?php echo $statusFilter === '9' ? 'selected' : ''; ?>>已作廢</option>
            </select>
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label small mb-1 text-muted">關鍵字（單號/對象/摘要/憑證/發票）</label>
            <div class="input-group input-group-sm">
              <input type="text" name="kw" class="form-control" value="<?php echo htmlspecialchars($kw); ?>" placeholder="搜尋...">
              <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
              <a href="fin_ledger_list.php" class="btn btn-outline-secondary" title="重設"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>傳票單號 / 入帳日</th>
              <th>帳務來源 / 會計科目</th>
              <th>交易對象 / 關聯單據</th>
              <th>摘要說明 / 外部憑證</th>
              <th class="text-end">收入 (+)</th>
              <th class="text-end">支出 / 退款 (-)</th>
              <th class="text-center">核帳狀態</th>
              <th class="text-end no-print">操作</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                  <i class="bi bi-inbox fs-2 d-block mb-2"></i>目前篩選條件下尚無進出帳流水紀錄
                </td>
              </tr>
              <?php else: foreach ($rows as $r):
                $lid     = (int)$r['led_id'];
                $isIn    = ((int)$r['led_dir'] === 1);
                $sta     = (int)$r['led_status'];
                $srcInfo = $srcBadges[(int)$r['led_source']] ?? ['label' => '其他', 'cls' => 'text-bg-secondary'];
                $catName = $catLabels[$r['led_category']] ?? $r['led_category'];
                $rowMute = ($sta === 9) ? 'opacity-50 text-decoration-line-through' : '';
              ?>
                <tr class="<?php echo $rowMute; ?>">
                  <td>
                    <div class="fw-bold font-monospace text-primary"><?php echo htmlspecialchars($r['led_no']); ?></div>
                    <small class="text-muted"><i class="bi bi-calendar3 me-1"></i><?php echo htmlspecialchars($r['led_tx_date']); ?></small>
                  </td>
                  <td>
                    <span class="badge <?php echo $srcInfo['cls']; ?> mb-1"><?php echo $srcInfo['label']; ?></span>
                    <div class="small text-muted"><?php echo htmlspecialchars($catName); ?>（<?php echo htmlspecialchars($r['led_pay_channel']); ?>）</div>
                  </td>
                  <td>
                    <div class="fw-semibold"><?php echo htmlspecialchars(_fn_safe_dec_name($r['led_counterparty']) ?: '—'); ?></div>
                    <div class="small">
                      <?php if (!empty($r['ord_no'])): ?>
                        <span class="badge text-bg-light border text-dark">訂單：<?php echo htmlspecialchars($r['ord_no']); ?></span>
                      <?php endif; ?>
                      <?php if (!empty($r['inv_no'])): ?>
                        <span class="badge text-bg-info text-dark">發票：<?php echo htmlspecialchars($r['inv_no']); ?></span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td>
                    <div><?php echo htmlspecialchars($r['led_summary']); ?></div>
                    <?php if (!empty($r['led_voucher_ref'])): ?>
                      <small class="text-muted font-monospace"><i class="bi bi-hash"></i>憑證：<?php echo htmlspecialchars($r['led_voucher_ref']); ?></small>
                    <?php endif; ?>
                  </td>
                  <td class="text-end fw-bold text-success font-monospace">
                    <?php echo $isIn ? ('+ ' . number_format((float)$r['led_amount'], 0)) : '—'; ?>
                  </td>
                  <td class="text-end fw-bold text-danger font-monospace">
                    <?php echo !$isIn ? ('- ' . number_format((float)$r['led_amount'], 0)) : '—'; ?>
                  </td>
                  <td class="text-center">
                    <?php if ($sta === 1): ?>
                      <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>已核帳入帳</span>
                    <?php elseif ($sta === 0): ?>
                      <span class="badge text-bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>待核帳</span>
                    <?php else: ?>
                      <span class="badge text-bg-secondary">已作廢沖銷</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end text-nowrap no-print">
                    <?php if ($sta === 0): ?>
                      <button type="button" class="btn btn-outline-success btn-xs btn-verify-led" data-id="<?php echo $lid; ?>" title="核帳入帳">
                        <i class="bi bi-check2-all"></i> 核帳
                      </button>
                    <?php endif; ?>
                    <a href="fin_ledger_detail.php?currID=<?php echo $lid; ?>" class="btn btn-outline-primary btn-xs" title="編輯傳票">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                    <?php if ($sta !== 9): ?>
                      <button type="button" class="btn btn-outline-danger btn-xs btn-void-led" data-id="<?php echo $lid; ?>" title="作廢沖銷">
                        <i class="bi bi-trash"></i>
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
            <?php endforeach;
            endif; ?>
          </tbody>
        </table>
      </div>
      <?php if (!$printMode && $totalPages > 1): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2 no-print">
          <small class="text-muted">共 <?php echo $totalRows; ?> 筆傳票紀錄，第 <?php echo $page; ?> / <?php echo $totalPages; ?> 頁</small>
          <ul class="pagination pagination-sm mb-0">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
              <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                <a class="page-link" href="?<?php echo $qsBase . '&page=' . $p; ?>"><?php echo $p; ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($printMode): ?>
      <div class="row mt-4 pt-3 border-top text-center small">
        <div class="col-4">製表經辦：____________________</div>
        <div class="col-4">會計覆核：____________________</div>
        <div class="col-4">財務主管核准：____________________</div>
      </div>
      <script>
        window.addEventListener('load', () => window.print());
      </script>
    <?php endif; ?>
  </div>

  <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script>
    const CSRF_TOKEN = '<?php echo htmlspecialchars($csrfToken, ENT_QUOTES); ?>';

    function doReDirect() {
      window.history.back();
    }

    function doButAdd() {
      window.location.href = 'fin_ledger_detail.php?currID=0';
    }

    function doButReset() {
      window.location.replace('fin_ledger_list.php');
    }

    $(document).ready(function() {
      $('.btn-verify-led').on('click', function() {
        const id = $(this).data('id');
        if (!confirm('確認此筆傳票憑證無誤並核帳入帳？')) return;
        $.post('../funcFiles/fin_act_func.php', {
          formAcce: 'func_fin_ledger_verify',
          token: CSRF_TOKEN,
          ledID: id
        }, function(res) {
          if (window.parent && window.parent.toastr) window.parent.toastr[res.msgType](res.msgStr);
          if (res.RTN == 1) location.reload();
        }, 'json');
      });

      $('.btn-void-led').on('click', function() {
        const id = $(this).data('id');
        if (!confirm('確定要作廢沖銷此筆會計傳票嗎？（作廢後將不計入結算金額）')) return;
        $.post('../funcFiles/fin_act_func.php', {
          formAcce: 'func_fin_ledger_delete',
          token: CSRF_TOKEN,
          ledID: id
        }, function(res) {
          if (window.parent && window.parent.toastr) window.parent.toastr[res.msgType](res.msgStr);
          if (res.RTN == 1) location.reload();
        }, 'json');
      });

      if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
      }
    });
  </script>
</body>

</html>