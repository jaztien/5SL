<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
define('PAGE_MARK', 'PG_SYS_OPLOG');
define('PAGE_ITEM', 'ITEM_SYS');
define('bkRedir', 1);
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$dict = _fn_get_op_log_dict();

// 篩選參數
$dateFrom  = trim((string)($_GET['dateFrom'] ?? date('Y-m-d', strtotime('-7 days'))));
$dateTo    = trim((string)($_GET['dateTo'] ?? date('Y-m-d')));
$roleFilter = trim((string)($_GET['role'] ?? ''));
$typeFilter = trim((string)($_GET['type'] ?? ''));
$opIdFilter = trim((string)($_GET['opId'] ?? ''));
$kwFilter   = trim((string)($_GET['kw'] ?? ''));

$conds = ['1=1'];
$binds = [];
if ($dateFrom !== '')   { $conds[] = 'l.log_at >= ?';   $binds[] = $dateFrom . ' 00:00:00'; }
if ($dateTo !== '')     { $conds[] = 'l.log_at <= ?';   $binds[] = $dateTo . ' 23:59:59'; }
if ($roleFilter !== '') { $conds[] = 'l.op_role = ?';   $binds[] = (int)$roleFilter; }
if ($typeFilter !== '') { $conds[] = 'l.log_type = ?';  $binds[] = (int)$typeFilter; }
if ($opIdFilter !== '') { $conds[] = 'l.op_id = ?';     $binds[] = (int)$opIdFilter; }
if ($kwFilter !== '')   { $conds[] = '(l.log_params LIKE ? OR l.log_ip LIKE ?)'; $like = "%{$kwFilter}%"; array_push($binds, $like, $like); }

$whereSql = ' WHERE ' . implode(' AND ', $conds);

$page       = max(1, (int)($_GET['page'] ?? 1));
$pageSize   = 30;
$offset     = ($page - 1) * $pageSize;
$totalRows  = (int)DB->single("SELECT COUNT(*) FROM fsl_op_logs l {$whereSql}", $binds);
$totalPages = max(1, (int)ceil($totalRows / $pageSize));

$rows = DB->query("
  SELECT l.*,
         m.mb_name,
         a.ad_name
  FROM fsl_op_logs l
  LEFT JOIN fsl_members m ON (l.op_role = 1 AND l.op_id = m.mb_id)
  LEFT JOIN fsl_administrator a ON (l.op_role = 2 AND l.op_id = a.ad_id)
  {$whereSql}
  ORDER BY l.log_id DESC
  LIMIT {$offset}, {$pageSize}
", $binds) ?: [];

$queryWithoutPage = $_GET;
unset($queryWithoutPage['page']);
$qsBase = http_build_query($queryWithoutPage);
?>
<!doctype html>
<html lang="zh-Hant-TW">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Fifth Season Life 人生第五季 | 全站操作紀錄與高爭議稽核中心</title>
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
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-journal-check text-primary me-2"></i>全站操作紀錄與爭議查證稽核中心</h5>
        <small class="text-muted">採用純數字代碼與雙層快取 JSON 樣板字典即時還原（目前篩選共 <strong><?php echo number_format($totalRows); ?></strong> 筆紀錄）</small>
      </div>
      <a href="sys_db_maint.php" class="btn btn-outline-danger btn-sm fw-bold">
        <i class="bi bi-database-fill-gear me-1"></i>前往資料庫維護與定期清理
      </a>
    </div>

    <!-- 多維度篩選列 -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body py-2 px-3">
        <form method="get" class="row g-2 align-items-end">
          <div class="col-6 col-md-2">
            <label class="form-label small mb-1 text-muted">發生起日</label>
            <input type="date" name="dateFrom" class="form-control form-control-sm" value="<?php echo htmlspecialchars($dateFrom); ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label small mb-1 text-muted">發生迄日</label>
            <input type="date" name="dateTo" class="form-control form-control-sm" value="<?php echo htmlspecialchars($dateTo); ?>">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label small mb-1 text-muted">操作者身分</label>
            <select name="role" class="form-select form-select-sm">
              <option value="">全部身分</option>
              <option value="1" <?php echo $roleFilter === '1' ? 'selected' : ''; ?>>1 - 前台會員</option>
              <option value="2" <?php echo $roleFilter === '2' ? 'selected' : ''; ?>>2 - 後台管理員</option>
              <option value="0" <?php echo $roleFilter === '0' ? 'selected' : ''; ?>>0 - 系統自動排程</option>
            </select>
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label small mb-1 text-muted">業務模組分類</label>
            <select name="type" class="form-select form-select-sm">
              <option value="">全部分類</option>
              <?php foreach ($dict as $tCode => $tInfo): ?>
              <option value="<?php echo htmlspecialchars((string)$tCode); ?>" <?php echo $typeFilter === (string)$tCode ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($tCode . ' - ' . ($tInfo['name'] ?? '')); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-4 col-md-1">
            <label class="form-label small mb-1 text-muted">操作者ID</label>
            <input type="number" name="opId" class="form-control form-control-sm" value="<?php echo htmlspecialchars($opIdFilter); ?>" placeholder="ID">
          </div>
          <div class="col-8 col-md-3">
            <label class="form-label small mb-1 text-muted">參數關鍵字 / 來源 IP</label>
            <div class="input-group input-group-sm">
              <input type="text" name="kw" class="form-control" value="<?php echo htmlspecialchars($kwFilter); ?>" placeholder="訂單號、課號、IP...">
              <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
              <a href="sys_op_log_list.php" class="btn btn-outline-secondary" title="重設"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- 稽核紀錄明細表 -->
    <div class="card border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 155px;">發生時間 / 流水號</th>
              <th style="width: 130px;">操作者身分</th>
              <th style="width: 140px;">分類 / 動作代碼</th>
              <th>還原完整操作敘述（爭議查證內容）</th>
              <th style="width: 130px;">來源 IP</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
            <tr>
              <td colspan="5" class="text-center py-5 text-muted">
                <i class="bi bi-journal-x fs-2 d-block mb-2"></i>目前篩選條件下尚無操作紀錄
              </td>
            </tr>
            <?php else: foreach ($rows as $r):
              $role = (int)$r['op_role'];
              $opId = (int)$r['op_id'];
              if ($role === 1) {
                $decMbName = _fn_safe_dec_name($r['mb_name']);
                $opLabel   = ($decMbName !== '' ? $decMbName : '會員') . " (#{$opId})";
                $roleBadge = '<span class="badge text-bg-primary">前台會員</span>';
              } elseif ($role === 2) {
                $opLabel   = ($r['ad_name'] ?: '管理員') . " (#{$opId})";
                $roleBadge = '<span class="badge text-bg-dark">後台管理員</span>';
              } else {
                $opLabel   = '系統排程 / 訪客';
                $roleBadge = '<span class="badge text-bg-secondary">系統排程</span>';
              }

              $rendered = _fn_format_op_log_row((int)$r['log_type'], (int)$r['log_code'], $opLabel, $r['log_params']);
              $lvl = $rendered['level'];
              $rowHighlight = ($lvl === 3) ? 'table-warning' : '';
            ?>
            <tr class="<?php echo $rowHighlight; ?>">
              <td>
                <div class="fw-semibold font-monospace small"><?php echo htmlspecialchars($r['log_at']); ?></div>
                <small class="text-muted font-monospace">#<?php echo (int)$r['log_id']; ?></small>
              </td>
              <td>
                <?php echo $roleBadge; ?>
                <div class="small fw-semibold mt-1"><?php echo htmlspecialchars($opLabel); ?></div>
              </td>
              <td>
                <span class="badge text-bg-light border text-dark"><?php echo htmlspecialchars($rendered['type_name']); ?></span>
                <div class="small text-muted font-monospace mt-1">[<?php echo (int)$r['log_code']; ?>] <?php echo htmlspecialchars($rendered['module_name']); ?></div>
              </td>
              <td>
                <?php if ($lvl === 3): ?>
                  <span class="badge text-bg-danger me-1">重要查證</span>
                <?php elseif ($lvl === 2): ?>
                  <span class="badge text-bg-info text-dark me-1">關鍵異動</span>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($rendered['text']); ?></span>
              </td>
              <td class="font-monospace small text-muted">
                <?php echo htmlspecialchars($r['log_ip'] ?: '—'); ?>
              </td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($totalPages > 1): ?>
      <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">共 <?php echo number_format($totalRows); ?> 筆紀錄，第 <?php echo $page; ?> / <?php echo $totalPages; ?> 頁</small>
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

  <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script>
    function doReDirect() {
      window.history.back();
    }

    function doButReset() {
      window.location.replace('sys_op_log_list.php');
    }
    $(document).ready(function() {
      if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
      }
    });
  </script>
</body>
</html>
