<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
define('PAGE_MARK', 'PG_FIN_INVOICE');
define('PAGE_ITEM', 'ITEM_FIN');
define('PAGE_OPANEL', 0);
define('DRAWER_OPANEL', 0);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$csrfToken = function_exists('_fn_genToken') ? _fn_genToken() : ($_SESSION['token'] ?? '');

$dateFrom  = trim((string)($_GET['dateFrom'] ?? ''));
$dateTo    = trim((string)($_GET['dateTo'] ?? ''));
$invStatus = trim((string)($_GET['invStatus'] ?? ''));
$kw        = trim((string)($_GET['kw'] ?? ''));

$conds = ['1=1'];
$binds = [];
if ($dateFrom !== '') {
    $conds[] = 'DATE(i.inv_created) >= ?';
    $binds[] = $dateFrom;
}
if ($dateTo !== '') {
    $conds[] = 'DATE(i.inv_created) <= ?';
    $binds[] = $dateTo;
}
if ($invStatus !== '') {
    $conds[] = 'i.inv_status = ?';
    $binds[] = (int)$invStatus;
}
if ($kw !== '') {
    $conds[] = '(i.inv_no LIKE ? OR i.inv_trade_no LIKE ? OR i.inv_tax_id LIKE ? OR i.inv_company_title LIKE ? OR o.ord_no LIKE ? OR m.mb_name LIKE ?)';
    $like = "%{$kw}%";
    array_push($binds, $like, $like, $like, $like, $like, $like);
}
$whereSql = ' WHERE ' . implode(' AND ', $conds);

// 統計各狀態發票張數與總金額
$stat = DB->row("
  SELECT
    COUNT(*) AS all_cnt,
    COALESCE(SUM(CASE WHEN i.inv_status = 0 THEN 1 ELSE 0 END), 0) AS unissued_cnt,
    COALESCE(SUM(CASE WHEN i.inv_status = 1 THEN 1 ELSE 0 END), 0) AS issued_cnt,
    COALESCE(SUM(CASE WHEN i.inv_status IN (2,9) THEN 1 ELSE 0 END), 0) AS void_allow_cnt,
    COALESCE(SUM(CASE WHEN i.inv_status = 1 THEN i.inv_paid_amount ELSE 0 END), 0) AS issued_sum
  FROM fsl_invoices i
  LEFT JOIN fsl_orders o ON i.ord_id = o.ord_id
  LEFT JOIN fsl_members m ON i.mb_id = m.mb_id
  {$whereSql}
", $binds) ?: ['all_cnt' => 0, 'unissued_cnt' => 0, 'issued_cnt' => 0, 'void_allow_cnt' => 0, 'issued_sum' => 0];

$page     = max(1, (int)($_GET['page'] ?? 1));
$pageSize = 25;
$offset   = ($page - 1) * $pageSize;
$totalRows  = (int)$stat['all_cnt'];
$totalPages = max(1, (int)ceil($totalRows / $pageSize));

$rows = DB->query("
  SELECT i.*, o.ord_no, o.ord_currency, m.mb_name, m.mb_email
  FROM fsl_invoices i
  LEFT JOIN fsl_orders o ON i.ord_id = o.ord_id
  LEFT JOIN fsl_members m ON i.mb_id = m.mb_id
  {$whereSql}
  ORDER BY i.inv_id DESC
  LIMIT {$offset}, {$pageSize}
", $binds) ?: [];

$typeMap    = [1 => '二聯式個人', 2 => '三聯式統編', 3 => '捐贈發票'];
$carrierMap = [0 => '會員載具', 1 => '手機條碼', 2 => '自然人憑證'];
$queryWithoutPage = $_GET;
unset($queryWithoutPage['page']);
$qsBase = http_build_query($queryWithoutPage);
?>
<!doctype html>
<html lang="zh-Hant-TW">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fifth Season Life 人生第五季 | 電子發票與金流對帳管理</title>
    <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
    <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
    <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../cssFiles/adminlte.css">
    <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
    <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>

<body class="bg-body-tertiary theme-cyan p-2">
    <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1">
        <i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?>
    </div>

    <div class="container-fluid py-2">
        <!-- 頂部模組切換列 -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 fw-bold"><i class="bi bi-receipt-cutoff text-primary me-2"></i>電子發票與金流對帳管理</h5>
                <div class="btn-group btn-group-sm ms-2" role="group">
                    <a href="fin_ledger_list.php" class="btn btn-outline-primary"><i class="bi bi-journal-text me-1"></i>進出帳總流水帳</a>
                    <a href="fin_invoice_list.php" class="btn btn-primary active"><i class="bi bi-receipt-cutoff me-1"></i>電子發票與對帳管理</a>
                </div>
            </div>

            <div>
                <a href="../funcFiles/fin_act_func.php?formAcce=export_invoice_csv&<?php echo $qsBase; ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                    <i class="bi bi-file-earmark-excel text-success me-1"></i>匯出電子發票報稅清冊 CSV
                </a>
            </div>
        </div>

        <!-- 發票統計 KPI -->
        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm border-start border-4 border-warning">
                    <div class="card-body py-2 px-3">
                        <div class="small text-muted fw-semibold">待付款 / 尚未開立發票</div>
                        <div class="fs-4 fw-bold text-warning mt-1"><?php echo (int)$stat['unissued_cnt']; ?> 筆</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm border-start border-4 border-success">
                    <div class="card-body py-2 px-3">
                        <div class="small text-muted fw-semibold">已正式開立發票</div>
                        <div class="fs-4 fw-bold text-success mt-1"><?php echo (int)$stat['issued_cnt']; ?> 張</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm border-start border-4 border-primary">
                    <div class="card-body py-2 px-3">
                        <div class="small text-muted fw-semibold">有效發票總開立金額</div>
                        <div class="fs-4 fw-bold text-primary mt-1">NT$ <?php echo number_format((float)$stat['issued_sum']); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm border-start border-4 border-danger">
                    <div class="card-body py-2 px-3">
                        <div class="small text-muted fw-semibold">折讓單 / 已作廢發票</div>
                        <div class="fs-4 fw-bold text-danger mt-1"><?php echo (int)$stat['void_allow_cnt']; ?> 張</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 篩選列 -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2 px-3">
                <form method="get" class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1 text-muted">建立起日</label>
                        <input type="date" name="dateFrom" class="form-control form-control-sm" value="<?php echo htmlspecialchars($dateFrom); ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1 text-muted">建立迄日</label>
                        <input type="date" name="dateTo" class="form-control form-control-sm" value="<?php echo htmlspecialchars($dateTo); ?>">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small mb-1 text-muted">發票與收款狀態</label>
                        <select name="invStatus" class="form-select form-select-sm">
                            <option value="">全部狀態</option>
                            <option value="0" <?php echo $invStatus === '0' ? 'selected' : ''; ?>>0 - 待付款未開立</option>
                            <option value="1" <?php echo $invStatus === '1' ? 'selected' : ''; ?>>1 - 已付款並正式開立</option>
                            <option value="2" <?php echo $invStatus === '2' ? 'selected' : ''; ?>>2 - 已開立折讓單 (部分退費)</option>
                            <option value="9" <?php echo $invStatus === '9' ? 'selected' : ''; ?>>9 - 已作廢 (全額退費)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label small mb-1 text-muted">關鍵字（發票字軌 / 金流序號 / 統編 / 公司抬頭 / 訂單 / 會員）</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="kw" class="form-control" value="<?php echo htmlspecialchars($kw); ?>" placeholder="搜尋 EF-、統編、訂單編號...">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> 查詢</button>
                            <a href="fin_invoice_list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 發票清冊表格 -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>電子發票字軌 / 隨機碼</th>
                            <th>發票類型 / 統編與載具</th>
                            <th>關聯訂單 / 會員</th>
                            <th>金流交易憑證</th>
                            <th class="text-end">實付金額</th>
                            <th class="text-center">狀態</th>
                            <th class="text-end">發票作業</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-receipt fs-2 d-block mb-2"></i>尚無符合條件的發票與交易紀錄
                                </td>
                            </tr>
                            <?php else: foreach ($rows as $r):
                                $iid = (int)$r['inv_id'];
                                $sta = (int)$r['inv_status'];
                            ?>
                                <tr class="<?php echo $sta === 9 ? 'opacity-50' : ''; ?>">
                                    <td>
                                        <?php if (!empty($r['inv_no'])): ?>
                                            <div class="fw-bold font-monospace text-primary fs-6"><?php echo htmlspecialchars($r['inv_no']); ?></div>
                                            <small class="text-muted">隨機碼：<span class="font-monospace fw-bold"><?php echo htmlspecialchars($r['inv_random_code'] ?? '—'); ?></span></small>
                                        <?php else: ?>
                                            <span class="badge text-bg-light border text-muted">尚未開立字軌</span>
                                        <?php endif; ?>
                                        <?php if (!empty($r['inv_issued_at'])): ?>
                                            <div class="small text-muted">開立：<?php echo substr($r['inv_issued_at'], 0, 16); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-secondary mb-1"><?php echo $typeMap[(int)$r['inv_type']] ?? '二聯式'; ?></span>
                                        <?php if ((int)$r['inv_type'] === 2): ?>
                                            <div class="small fw-semibold">統編：<?php echo htmlspecialchars($r['inv_tax_id'] ?? ''); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($r['inv_company_title'] ?? ''); ?></div>
                                        <?php else: ?>
                                            <div class="small text-muted">
                                                <?php echo $carrierMap[(int)$r['inv_carrier_type']] ?? '會員載具'; ?>
                                                <?php echo !empty($r['inv_carrier_code']) ? ('：' . htmlspecialchars($r['inv_carrier_code'])) : ''; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars(_fn_safe_dec_name($r['mb_name']) ?: ('會員 #' . $r['mb_id'])); ?></div>
                                        <small class="text-muted font-monospace">訂單：<?php echo htmlspecialchars($r['ord_no'] ?: ('#' . $r['ord_id'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?php echo htmlspecialchars($r['inv_pay_gateway']); ?> / <?php echo htmlspecialchars($r['inv_pay_method']); ?></div>
                                        <small class="text-muted font-monospace"><?php echo htmlspecialchars($r['inv_trade_no'] ?: '無金流序號'); ?></small>
                                        <?php if (!empty($r['inv_card_last4'])): ?>
                                            <span class="badge text-bg-light border text-dark">末四碼 <?php echo htmlspecialchars($r['inv_card_last4']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-bold font-monospace">
                                        <?php echo htmlspecialchars($r['ord_currency'] ?: 'TWD'); ?> <?php echo number_format((float)$r['inv_paid_amount'], 0); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($sta === 0): ?>
                                            <span class="badge text-bg-warning text-dark">待付款未開立</span>
                                        <?php elseif ($sta === 1): ?>
                                            <span class="badge text-bg-success">已正式開立</span>
                                        <?php elseif ($sta === 2): ?>
                                            <span class="badge text-bg-info text-dark">已開立折讓單</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-danger">已作廢</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <?php if ($sta === 0): ?>
                                            <button type="button" class="btn btn-primary btn-xs btn-open-issue"
                                                data-id="<?php echo $iid; ?>"
                                                data-type="<?php echo (int)$r['inv_type']; ?>"
                                                data-taxid="<?php echo htmlspecialchars($r['inv_tax_id'] ?? ''); ?>"
                                                data-title="<?php echo htmlspecialchars($r['inv_company_title'] ?? ''); ?>">
                                                <i class="bi bi-receipt"></i> 開立發票
                                            </button>
                                        <?php elseif ($sta === 1): ?>
                                            <button type="button" class="btn btn-outline-warning btn-xs btn-open-refund"
                                                data-mode="allowance"
                                                data-id="<?php echo $iid; ?>"
                                                data-invno="<?php echo htmlspecialchars($r['inv_no'] ?? ''); ?>"
                                                data-max="<?php echo (float)$r['inv_paid_amount']; ?>">
                                                開立折讓
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-xs btn-open-refund"
                                                data-mode="void"
                                                data-id="<?php echo $iid; ?>"
                                                data-invno="<?php echo htmlspecialchars($r['inv_no'] ?? ''); ?>"
                                                data-max="<?php echo (float)$r['inv_paid_amount']; ?>">
                                                作廢退費
                                            </button>
                                        <?php else: ?>
                                            <span class="small text-muted">已完成結案</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                        <?php endforeach;
                        endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2">
                    <small class="text-muted">共 <?php echo $totalRows; ?> 筆發票紀錄，第 <?php echo $page; ?> / <?php echo $totalPages; ?> 頁</small>
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
    </div>

    <!-- 開立電子發票 Modal -->
    <div class="modal fade" id="issueInvModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold"><i class="bi bi-receipt text-primary me-1"></i>正式開立電子發票</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="issue_inv_id">
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label small fw-bold">電子發票字軌號碼 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace text-uppercase" id="issue_inv_no" placeholder="如：EF-99887766">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-bold">4碼隨機碼</label>
                            <input type="text" maxlength="4" class="form-control font-monospace" id="issue_random_code" placeholder="留空自動產生">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">發票類型</label>
                            <select class="form-select" id="issue_inv_type">
                                <option value="1">1 - 二聯式個人電子發票</option>
                                <option value="2">2 - 三聯式公司統一編號</option>
                                <option value="3">3 - 捐贈發票</option>
                            </select>
                        </div>
                        <div class="col-md-5 issue-b2b-box" style="display:none;">
                            <label class="form-label small fw-bold">公司統一編號</label>
                            <input type="text" maxlength="8" class="form-control font-monospace" id="issue_tax_id" placeholder="8碼統編">
                        </div>
                        <div class="col-md-7 issue-b2b-box" style="display:none;">
                            <label class="form-label small fw-bold">公司發票抬頭</label>
                            <input type="text" class="form-control" id="issue_company_title" placeholder="輸入完整公司名稱">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary btn-sm fw-bold" id="btnSubmitIssueInv">確認開立並入帳</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 折讓單 / 作廢退費 Modal -->
    <div class="modal fade" id="refundInvModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold" id="refundModalTitle">發票折讓 / 作廢退費作業</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="refund_inv_id">
                    <input type="hidden" id="refund_mode">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">退費金額 (NT$) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="1" class="form-control fw-bold text-danger" id="refund_amount">
                        <div class="form-text" id="refundMaxHint"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">折讓 / 作廢退費原因說明 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="refund_reason" placeholder="例如：學員開課前申請退費、課程取消全額退費...">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-danger btn-sm fw-bold" id="btnSubmitRefundInv">確認執行並寫入總帳</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
    <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
    <script src="../scriptFiles/page.basic.js"></script>
    <script src="../scriptFiles/iframe-state-keeper.js"></script>

    <script>
        const CSRF_TOKEN = '<?php echo htmlspecialchars($csrfToken, ENT_QUOTES); ?>';

        function doReDirect() {
            window.history.back();
        }

        function doButReset() {
            window.location.replace('fin_invoice_list.php');
        }

        $(document).ready(function() {
            $('.btn-open-issue').on('click', function() {
                const $btn = $(this);
                $('#issue_inv_id').val($btn.data('id'));
                $('#issue_inv_no').val('');
                $('#issue_random_code').val('');
                const t = String($btn.data('type') || '1');
                $('#issue_inv_type').val(t).trigger('change');
                $('#issue_tax_id').val($btn.data('taxid') || '');
                $('#issue_company_title').val($btn.data('title') || '');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('issueInvModal')).show();
            });

            $('#issue_inv_type').on('change', function() {
                $('.issue-b2b-box').toggle($(this).val() === '2');
            });

            $('#btnSubmitIssueInv').on('click', function() {
                $.post('../funcFiles/fin_act_func.php', {
                    formAcce: 'func_fin_inv_issue',
                    token: CSRF_TOKEN,
                    invID: $('#issue_inv_id').val(),
                    inv_no: $('#issue_inv_no').val(),
                    inv_random_code: $('#issue_random_code').val(),
                    inv_type: $('#issue_inv_type').val(),
                    inv_tax_id: $('#issue_tax_id').val(),
                    inv_company_title: $('#issue_company_title').val()
                }, function(res) {
                    if (window.parent && window.parent.toastr) window.parent.toastr[res.msgType](res.msgStr);
                    if (res.RTN == 1) location.reload();
                }, 'json');
            });

            $('.btn-open-refund').on('click', function() {
                const $btn = $(this);
                const mode = $btn.data('mode');
                const maxAmt = parseFloat($btn.data('max')) || 0;
                const invNo = $btn.data('invno') || '';

                $('#refund_inv_id').val($btn.data('id'));
                $('#refund_mode').val(mode);
                $('#refundModalTitle').text(mode === 'void' ? `作廢發票並全額退費（${invNo}）` : `開立發票折讓單（${invNo}）`);
                $('#refund_amount').val(maxAmt).prop('readonly', mode === 'void');
                $('#refundMaxHint').text(mode === 'void' ? '發票作廢將自動依原實付金額產生全額退款支出傳票。' : `部分退費折讓金額上限：NT$ ${maxAmt.toLocaleString()}`);
                $('#refund_reason').val('');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('refundInvModal')).show();
            });

            $('#btnSubmitRefundInv').on('click', function() {
                const mode = $('#refund_mode').val();
                const acce = (mode === 'void') ? 'func_fin_inv_void' : 'func_fin_inv_allowance';
                $.post('../funcFiles/fin_act_func.php', {
                    formAcce: acce,
                    token: CSRF_TOKEN,
                    invID: $('#refund_inv_id').val(),
                    refund_amount: $('#refund_amount').val(),
                    refund_reason: $('#refund_reason').val()
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