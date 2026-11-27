<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
// 定義本頁面代碼（服務工單中心）
define('PAGE_MARK', 'PG_SRV_TICKET');
define('PAGE_ITEM', 'ITEM_SRV');
define('PAGE_OPANEL', 1); // 1-新增（代客建立工單）
define('DRAWER_OPANEL', 0);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$csrfToken = function_exists('_fn_genToken') ? _fn_genToken() : ($_SESSION['token'] ?? '');
$myAdId    = (int)($_SESSION['SESS_AD_ID'] ?? 0);

// 篩選參數
$staFilter  = trim((string)($_GET['status'] ?? ''));
$catFilter  = trim((string)($_GET['cat'] ?? ''));
$prioFilter = trim((string)($_GET['prio'] ?? ''));
$mineOnly   = ((int)($_GET['mine'] ?? 0) === 1);
$kw         = trim((string)($_GET['kw'] ?? ''));

// 1. 狀態統計看板（全站即時統計）
$stat = DB->row("
  SELECT
    COALESCE(SUM(CASE WHEN tkt_status = 0 THEN 1 ELSE 0 END), 0) AS new_cnt,
    COALESCE(SUM(CASE WHEN tkt_status = 1 THEN 1 ELSE 0 END), 0) AS proc_cnt,
    COALESCE(SUM(CASE WHEN tkt_status = 2 THEN 1 ELSE 0 END), 0) AS wait_cnt,
    COALESCE(SUM(CASE WHEN tkt_status = 3 THEN 1 ELSE 0 END), 0) AS done_cnt,
    COALESCE(SUM(CASE WHEN tkt_status IN (0,1,2) AND tkt_priority = 3 THEN 1 ELSE 0 END), 0) AS urgent_cnt
  FROM fsl_service_tickets
") ?: ['new_cnt' => 0, 'proc_cnt' => 0, 'wait_cnt' => 0, 'done_cnt' => 0, 'urgent_cnt' => 0];

$conds = ['1=1'];
$binds = [];
if ($staFilter !== '') {
    $conds[] = 't.tkt_status = ?';
    $binds[] = (int)$staFilter;
}
if ($catFilter !== '') {
    $conds[] = 't.tkt_category = ?';
    $binds[] = $catFilter;
}
if ($prioFilter !== '') {
    $conds[] = 't.tkt_priority = ?';
    $binds[] = (int)$prioFilter;
}
if ($mineOnly && $myAdId > 0) {
    $conds[] = 't.ad_id = ?';
    $binds[] = $myAdId;
}
if ($kw !== '') {
    $conds[] = '(t.tkt_no LIKE ? OR t.tkt_subject LIKE ? OR t.tkt_content LIKE ? OR m.mb_name LIKE ? OR m.mb_email LIKE ? OR o.ord_no LIKE ?)';
    $like = "%{$kw}%";
    array_push($binds, $like, $like, $like, $like, $like, $like);
}
$whereSql = ' WHERE ' . implode(' AND ', $conds);

$page     = max(1, (int)($_GET['page'] ?? 1));
$pageSize = 25;
$offset   = ($page - 1) * $pageSize;

$totalRows = (int)DB->single("
  SELECT COUNT(*)
  FROM fsl_service_tickets t
  LEFT JOIN fsl_members m ON t.mb_id = m.mb_id
  LEFT JOIN fsl_orders o ON t.ord_id = o.ord_id
  {$whereSql}
", $binds);
$totalPages = max(1, (int)ceil($totalRows / $pageSize));

$rows = DB->query("
  SELECT t.*,
         m.mb_name, m.mb_email, m.mb_cellphone,
         o.ord_no,
         a.ad_name AS handler_name,
         (SELECT COUNT(*) FROM fsl_ticket_replies r WHERE r.tkt_id = t.tkt_id AND r.rep_is_internal = 0) AS reply_cnt
  FROM fsl_service_tickets t
  LEFT JOIN fsl_members m ON t.mb_id = m.mb_id
  LEFT JOIN fsl_orders o ON t.ord_id = o.ord_id
  LEFT JOIN fsl_administrator a ON t.ad_id = a.ad_id
  {$whereSql}
  ORDER BY (t.tkt_status IN (0,1,2)) DESC, t.tkt_priority DESC, t.tkt_updated DESC
  LIMIT {$offset}, {$pageSize}
", $binds) ?: [];

$catLabels = [
    'course'  => ['label' => '課程諮詢與改期', 'cls' => 'text-bg-primary'],
    'refund'  => ['label' => '退費與帳務',     'cls' => 'text-bg-danger'],
    'invoice' => ['label' => '電子發票修改',   'cls' => 'text-bg-info text-dark'],
    'system'  => ['label' => '系統操作問題',   'cls' => 'text-bg-secondary'],
    'general' => ['label' => '其他服務諮詢',   'cls' => 'text-bg-light border text-dark']
];

// 載入會員清單供「代客建立工單」下拉選單使用
$memberList = DB->query("SELECT mb_id, mb_name, mb_email FROM fsl_members WHERE mb_status != 9 ORDER BY mb_id DESC LIMIT 200") ?: [];

$queryWithoutPage = $_GET;
unset($queryWithoutPage['page']);
$qsBase = http_build_query($queryWithoutPage);
?>
<!doctype html>
<html lang="zh-Hant-TW">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fifth Season Life 人生第五季 | 服務工單接收與處理中心</title>
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
        <!-- 頂部標題與操作列 -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 fw-bold"><i class="bi bi-headset text-primary me-2"></i>會員服務工單接收中心</h5>
                <?php if ((int)$stat['urgent_cnt'] > 0): ?>
                    <a href="?prio=3" class="badge rounded-pill text-bg-danger text-decoration-none px-3 py-1">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>目前有 <?php echo (int)$stat['urgent_cnt']; ?> 件特急/退費案件待處理
                    </a>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2">
                <a href="?mine=<?php echo $mineOnly ? '0' : '1'; ?>" class="btn btn-sm <?php echo $mineOnly ? 'btn-primary' : 'btn-outline-primary'; ?> fw-bold">
                    <i class="bi bi-person-check me-1"></i>只看我負責的工單
                </a>
                <button type="button" class="btn btn-success btn-sm fw-bold" id="btnOpenNewTicket">
                    <i class="bi bi-plus-circle me-1"></i>代客建立服務工單
                </button>
            </div>
        </div>

        <!-- 狀態快速篩選 KPI 卡片 -->
        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
                <a href="?status=0" class="text-decoration-none">
                    <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
                        <div class="card-body py-2 px-3">
                            <div class="small text-muted fw-semibold">待受理（新立案未認領）</div>
                            <div class="fs-4 fw-bold text-danger mt-1"><?php echo (int)$stat['new_cnt']; ?> 件</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3">
                <a href="?status=1" class="text-decoration-none">
                    <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                        <div class="card-body py-2 px-3">
                            <div class="small text-muted fw-semibold">處理中（專員跟進中）</div>
                            <div class="fs-4 fw-bold text-primary mt-1"><?php echo (int)$stat['proc_cnt']; ?> 件</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3">
                <a href="?status=2" class="text-decoration-none">
                    <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                        <div class="card-body py-2 px-3">
                            <div class="small text-muted fw-semibold">待會員補充回覆</div>
                            <div class="fs-4 fw-bold text-warning mt-1"><?php echo (int)$stat['wait_cnt']; ?> 件</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3">
                <a href="?status=3" class="text-decoration-none">
                    <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                        <div class="card-body py-2 px-3">
                            <div class="small text-muted fw-semibold">已結案完成</div>
                            <div class="fs-4 fw-bold text-success mt-1"><?php echo (int)$stat['done_cnt']; ?> 件</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- 篩選列 -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2 px-3">
                <form method="get" class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1 text-muted">工單狀態</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">全部狀態</option>
                            <option value="0" <?php echo $staFilter === '0' ? 'selected' : ''; ?>>0 - 待受理</option>
                            <option value="1" <?php echo $staFilter === '1' ? 'selected' : ''; ?>>1 - 處理中</option>
                            <option value="2" <?php echo $staFilter === '2' ? 'selected' : ''; ?>>2 - 待會員回覆</option>
                            <option value="3" <?php echo $staFilter === '3' ? 'selected' : ''; ?>>3 - 已結案</option>
                            <option value="9" <?php echo $staFilter === '9' ? 'selected' : ''; ?>>9 - 已取消作廢</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1 text-muted">問題分類</label>
                        <select name="cat" class="form-select form-select-sm">
                            <option value="">全部分類</option>
                            <?php foreach ($catLabels as $cKey => $cInfo): ?>
                                <option value="<?php echo $cKey; ?>" <?php echo $catFilter === $cKey ? 'selected' : ''; ?>><?php echo $cInfo['label']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1 text-muted">優先等級</label>
                        <select name="prio" class="form-select form-select-sm">
                            <option value="">全部等級</option>
                            <option value="3" <?php echo $prioFilter === '3' ? 'selected' : ''; ?>>3 - 特急 (退費/客訴)</option>
                            <option value="2" <?php echo $prioFilter === '2' ? 'selected' : ''; ?>>2 - 急件</option>
                            <option value="1" <?php echo $prioFilter === '1' ? 'selected' : ''; ?>>1 - 一般</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small mb-1 text-muted">關鍵字（工單編號 / 主旨 / 會員姓名 / 訂單編號）</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="kw" class="form-control" value="<?php echo htmlspecialchars($kw); ?>" placeholder="搜尋 TK-、主旨、會員姓名...">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> 查詢</button>
                            <a href="srv_ticket_list.php" class="btn btn-outline-secondary" title="重設"><i class="bi bi-arrow-counterclockwise"></i></a>
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
                            <th>工單編號 / 立案時間</th>
                            <th>分類 / 優先度</th>
                            <th>主旨與問題摘要</th>
                            <th>申請會員 / 關聯單據</th>
                            <th>負責客服專員</th>
                            <th class="text-center">狀態</th>
                            <th class="text-end">處理操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-headset fs-2 d-block mb-2"></i>目前篩選條件下尚無服務工單紀錄
                                </td>
                            </tr>
                            <?php else: foreach ($rows as $r):
                                $tid     = (int)$r['tkt_id'];
                                $sta     = (int)$r['tkt_status'];
                                $prio    = (int)$r['tkt_priority'];
                                $catInfo = $catLabels[$r['tkt_category']] ?? $catLabels['general'];
                            ?>
                                <tr class="<?php echo $sta === 9 ? 'opacity-50' : ''; ?>">
                                    <td>
                                        <a href="srv_ticket_detail.php?currID=<?php echo $tid; ?>" class="fw-bold font-monospace text-primary text-decoration-none">
                                            <?php echo htmlspecialchars($r['tkt_no']); ?>
                                        </a>
                                        <div class="small text-muted"><?php echo substr($r['tkt_created'], 0, 16); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $catInfo['cls']; ?> mb-1"><?php echo $catInfo['label']; ?></span>
                                        <div>
                                            <?php if ($prio === 3): ?>
                                                <span class="badge text-bg-danger"><i class="bi bi-fire"></i> 特急</span>
                                            <?php elseif ($prio === 2): ?>
                                                <span class="badge text-bg-warning text-dark">急件</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-light border text-muted">一般</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="max-width: 320px;">
                                        <a href="srv_ticket_detail.php?currID=<?php echo $tid; ?>" class="fw-bold text-dark text-decoration-none d-block text-truncate">
                                            <?php echo htmlspecialchars($r['tkt_subject']); ?>
                                        </a>
                                        <small class="text-muted d-block text-truncate"><?php echo htmlspecialchars(mb_substr(strip_tags($r['tkt_content']), 0, 60)); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars(_fn_safe_dec_name($r['mb_name']) ?: ('會員 #' . $r['mb_id'])); ?></div>
                                        <div class="small">
                                            <?php if (!empty($r['ord_no'])): ?>
                                                <span class="badge text-bg-light border text-dark">訂單：<?php echo htmlspecialchars($r['ord_no']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($r['thread_id'])): ?>
                                                <span class="badge text-bg-info text-dark">來自客服對話 #<?php echo (int)$r['thread_id']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($r['handler_name'])): ?>
                                            <span class="badge text-bg-light border text-dark"><i class="bi bi-person-badge me-1 text-primary"></i><?php echo htmlspecialchars($r['handler_name']); ?></span>
                                        <?php else: ?>
                                            <span class="badge text-bg-warning text-dark">尚未認領</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($sta === 0): ?>
                                            <span class="badge text-bg-danger">待受理</span>
                                        <?php elseif ($sta === 1): ?>
                                            <span class="badge text-bg-primary">處理中</span>
                                        <?php elseif ($sta === 2): ?>
                                            <span class="badge text-bg-warning text-dark">待會員回覆</span>
                                        <?php elseif ($sta === 3): ?>
                                            <span class="badge text-bg-success">已結案</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary">已取消作廢</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <?php if (empty($r['ad_id']) && $sta === 0): ?>
                                            <button type="button" class="btn btn-outline-warning btn-xs btn-claim-tkt" data-id="<?php echo $tid; ?>">
                                                <i class="bi bi-hand-index-thumb"></i> 認領接手
                                            </button>
                                        <?php endif; ?>
                                        <a href="srv_ticket_detail.php?currID=<?php echo $tid; ?>" class="btn btn-primary btn-xs fw-bold">
                                            <i class="bi bi-chat-square-text"></i> 進入處理
                                        </a>
                                    </td>
                                </tr>
                        <?php endforeach;
                        endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2">
                    <small class="text-muted">共 <?php echo $totalRows; ?> 件服務工單，第 <?php echo $page; ?> / <?php echo $totalPages; ?> 頁</small>
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

    <!-- 代客建立服務工單 Modal -->
    <div class="modal fade" id="newTicketModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle text-success me-1"></i>代客建立服務工單</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">申請會員 <span class="text-danger">*</span></label>
                        <select class="form-select" id="new_tkt_mb_id">
                            <option value="">-- 請選擇會員 --</option>
                            <?php foreach ($memberList as $mb): ?>
                                <option value="<?php echo (int)$mb['mb_id']; ?>">
                                    #<?php echo (int)$mb['mb_id']; ?>｜<?php echo htmlspecialchars(_fn_safe_dec_name($mb['mb_name'])); ?>（<?php echo htmlspecialchars(_fn_safe_dec_name($mb['mb_email'])); ?>）
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label small fw-bold">問題分類</label>
                            <select class="form-select" id="new_tkt_category">
                                <option value="course">課程諮詢與改期</option>
                                <option value="refund">退費與帳務</option>
                                <option value="invoice">電子發票修改</option>
                                <option value="system">系統操作問題</option>
                                <option value="general">其他服務諮詢</option>
                            </select>
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-bold">優先等級</label>
                            <select class="form-select" id="new_tkt_priority">
                                <option value="1">1 - 一般</option>
                                <option value="2">2 - 急件</option>
                                <option value="3">3 - 特急</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">工單主旨 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="new_tkt_subject" placeholder="簡述會員需求或問題標題...">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">問題詳細說明 <span class="text-danger">*</span></label>
                        <textarea class="form-control" rows="4" id="new_tkt_content" placeholder="請輸入電話或來信內容細節..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-success btn-sm fw-bold" id="btnSubmitNewTicket">建立並進入處理</button>
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

        function doButAdd() {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('newTicketModal')).show();
        }

        function doButReset() {
            window.location.replace('srv_ticket_list.php');
        }

        $(document).ready(function() {
            $('#btnOpenNewTicket').on('click', doButAdd);

            // 一鍵認領接手
            $('.btn-claim-tkt').on('click', function() {
                const id = $(this).data('id');
                $.post('../funcFiles/srv_act_func.php', {
                    formAcce: 'func_srv_ticket_assign',
                    token: CSRF_TOKEN,
                    tktID: id
                }, function(res) {
                    if (window.parent && window.parent.toastr) window.parent.toastr[res.msgType](res.msgStr);
                    if (res.RTN == 1) location.reload();
                }, 'json');
            });

            // 代客建立新工單
            $('#btnSubmitNewTicket').on('click', function() {
                $.post('../funcFiles/srv_act_func.php', {
                    formAcce: 'func_srv_ticket_create',
                    token: CSRF_TOKEN,
                    mb_id: $('#new_tkt_mb_id').val(),
                    tkt_category: $('#new_tkt_category').val(),
                    tkt_priority: $('#new_tkt_priority').val(),
                    tkt_subject: $('#new_tkt_subject').val(),
                    tkt_content: $('#new_tkt_content').val()
                }, function(res) {
                    if (window.parent && window.parent.toastr) window.parent.toastr[res.msgType](res.msgStr);
                    if (res.RTN == 1 && res.newID) {
                        window.location.href = 'srv_ticket_detail.php?currID=' + res.newID;
                    }
                }, 'json');
            });

            if (window.parent && typeof window.parent.treeviewCheck === 'function') {
                window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
            }
        });
    </script>
</body>

</html>