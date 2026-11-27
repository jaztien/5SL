<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');

// =========================================================================
// 最高安全權限閘門：僅限指定最高管理者進入（可依實際管理員 ad_id 或帳號調整）
// =========================================================================
$SUPER_ADMIN_IDS  = [1]; // 允許進入的最高管理員 ad_id 白名單
$SUPER_ADMIN_ACCS = ['admin', 'superadmin']; // 允許進入的最高管理員帳號白名單
$DB_MAX_QUOTA_MB = 1024 * 3; // 制定資料庫最大使用空間上限（單位：MB，例如 1024 = 1GB）

$currAdId  = (int)($_SESSION['SESS_AD_ID'] ?? 0);
$currAdAcc = strtolower(trim((string)($_SESSION['SESS_AD_ACC'] ?? $_SESSION['SESS_AD_ACCOUNT'] ?? '')));

if ($currAdId <= 0) {
  header('Location: ../login.php');
  exit();
}

// 從 fsl_administrator 二次核實身分
$admRow = DB->row("SELECT * FROM fsl_administrator WHERE ad_id = ? LIMIT 1", [$currAdId]);
$dbAcc  = strtolower(trim((string)($admRow['ad_acc'] ?? $admRow['ad_account'] ?? $currAdAcc)));
$isSuperAdmin = in_array($currAdId, $SUPER_ADMIN_IDS, true) || ($dbAcc !== '' && in_array($dbAcc, $SUPER_ADMIN_ACCS, true));

if (!$admRow || !$isSuperAdmin) {
  if (isset($_POST['ajaxAction'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '權限不足：僅限系統指定最高管理者執行資料庫維運！']);
    exit();
  }
  die('<!doctype html><html lang="zh-Hant-TW"><head><meta charset="utf-8"><link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css"></head><body class="p-5 text-center"><div class="alert alert-danger d-inline-block shadow-sm px-5 py-4"><h5 class="fw-bold mb-2">⛔ 存取被拒絕 (403 Forbidden)</h5><p class="mb-3">本頁面為系統最高權限資料庫維護中心，僅限指定最高管理者進入。</p><a href="javascript:history.back()" class="btn btn-outline-danger btn-sm">返回上一頁</a></div></body></html>');
}

// =========================================================================
// 同頁內建 Ajax 資料庫維護保養引擎（支援 OPTIMIZE / ANALYZE / CHECK / REPAIR / SQL 備份）
// =========================================================================
$ajaxAction = trim((string)($_REQUEST['ajaxAction'] ?? ''));
if ($ajaxAction !== '') {
  $token = $_REQUEST['token'] ?? '';
  if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
    if ($ajaxAction === 'export_sql') die('安全驗證失敗（Token 無效）！');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面！']);
    exit();
  }

  // 取得目前資料庫所有合法資料表白名單（防止任何 SQL Injection）
  $validTables = [];
  $rawTables = DB->query("SHOW TABLES") ?: [];
  foreach ($rawTables as $tr) {
    $tName = (string)reset($tr);
    if ($tName !== '') $validTables[$tName] = true;
  }

  // 1. 匯出指定資料表或全庫 SQL 備份檔
  if ($ajaxAction === 'export_sql') {
    @set_time_limit(300);
    while (ob_get_level() > 0) ob_end_clean();

    $reqTables = isset($_GET['tables']) && $_GET['tables'] !== '' ? explode(',', (string)$_GET['tables']) : array_keys($validTables);
    $targetTables = array_values(array_filter($reqTables, fn($t) => isset($validTables[$t])));

    $filename = '5SL_DB_Backup_' . date('Ymd_His') . '.sql';
    header('Content-Type: application/sql; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');

    $out = fopen('php://output', 'w');
    fwrite($out, "-- Fifth Season Life Database Backup\n");
    fwrite($out, "-- Generated at: " . date('Y-m-d H:i:s') . "\n");
    fwrite($out, "-- Operator ID: {$currAdId}\n\n");
    fwrite($out, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n");

    foreach ($targetTables as $tbl) {
      $safeTbl = '`' . str_replace('`', '``', $tbl) . '`';
      $createRow = DB->row("SHOW CREATE TABLE {$safeTbl}");
      $createSql = $createRow['Create Table'] ?? '';
      if ($createSql !== '') {
        fwrite($out, "-- ----------------------------\n-- Table structure for {$tbl}\n-- ----------------------------\n");
        fwrite($out, "DROP TABLE IF EXISTS {$safeTbl};\n{$createSql};\n\n");
      }

      $dataRows = DB->query("SELECT * FROM {$safeTbl}") ?: [];
      if (!empty($dataRows)) {
        fwrite($out, "-- Records of {$tbl} (" . count($dataRows) . " rows)\n");
        foreach ($dataRows as $dr) {
          $cols = array_map(fn($c) => '`' . str_replace('`', '``', $c) . '`', array_keys($dr));
          $vals = array_map(function($v) {
            if ($v === null) return 'NULL';
            return "'" . addslashes((string)$v) . "'";
          }, array_values($dr));
          fwrite($out, "INSERT INTO {$safeTbl} (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n");
        }
        fwrite($out, "\n");
      }
    }

    fwrite($out, "SET FOREIGN_KEY_CHECKS = 1;\n");
    fclose($out);
    exit();
  }

  header('Content-Type: application/json; charset=utf-8');

  // 2. 執行資料表維護指令（OPTIMIZE / ANALYZE / CHECK / REPAIR）
  if (in_array($ajaxAction, ['optimize', 'analyze', 'check', 'repair'], true)) {
    @set_time_limit(180);
    $reqTables = (array)($_POST['tables'] ?? []);
    $targetTables = array_values(array_filter($reqTables, fn($t) => isset($validTables[$t])));

    if (empty($targetTables)) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請至少勾選一張要維護的資料表！']);
      exit();
    }

    $cmdSqlMap = [
      'optimize' => 'OPTIMIZE TABLE',
      'analyze'  => 'ANALYZE TABLE',
      'check'    => 'CHECK TABLE',
      'repair'   => 'REPAIR TABLE'
    ];
    $sqlPrefix = $cmdSqlMap[$ajaxAction];
    $logs = [];

    foreach ($targetTables as $tbl) {
      $safeTbl = '`' . str_replace('`', '``', $tbl) . '`';
      try {
        $resRows = DB->query("{$sqlPrefix} {$safeTbl}");
        if (is_array($resRows) && !empty($resRows)) {
          foreach ($resRows as $rr) {
            $msgType = $rr['Msg_type'] ?? 'status';
            $msgText = $rr['Msg_text'] ?? 'OK';
            $logs[]  = "[{$tbl}] ({$msgType}) {$msgText}";
          }
        } else {
          $logs[] = "[{$tbl}] (status) " . strtoupper($ajaxAction) . " OK";
        }
      } catch (Throwable $e) {
        $logs[] = "[{$tbl}] (error) " . $e->getMessage();
      }
    }

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => "已完成 " . count($targetTables) . " 張資料表的 " . strtoupper($ajaxAction) . " 維運作業！",
      'logs'    => $logs
    ]);
    exit();
  }

  // 3. 計算單張或多張資料表的精準筆數 (SELECT COUNT(*))
  if ($ajaxAction === 'exact_count') {
    $reqTables = (array)($_POST['tables'] ?? array_keys($validTables));
    $targetTables = array_values(array_filter($reqTables, fn($t) => isset($validTables[$t])));
    $counts = [];
    foreach ($targetTables as $tbl) {
      $safeTbl = '`' . str_replace('`', '``', $tbl) . '`';
      $counts[$tbl] = (int)DB->single("SELECT COUNT(*) FROM {$safeTbl}");
    }
    echo json_encode(['RTN' => 1, 'counts' => $counts]);
    exit();
  }

  // 4. 清理過期歷史冗餘資料（例如超過 90 天之已讀站內通知）
  if ($ajaxAction === 'purge_old_logs') {
    $days = max(30, (int)($_POST['days'] ?? 90));
    $target = trim((string)($_POST['target'] ?? ''));
    $deleted = 0;

    if ($target === 'notifications' && isset($validTables['fsl_notifications'])) {
      DB->query("DELETE FROM `fsl_notifications` WHERE `noti_is_read` = 1 AND `noti_created` < DATE_SUB(NOW(), INTERVAL ? DAY)", [$days]);
      $deleted = (int)DB->single("SELECT ROW_COUNT()");
      DB->query("OPTIMIZE TABLE `fsl_notifications`");
    }

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => "已安全清理 {$deleted} 筆超過 {$days} 天之過期已讀紀錄，並完成資料表空間重組！"
    ]);
    exit();
  }

  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的維運指令']);
  exit();
}

// =========================================================================
// 頁面初次載入：讀取資料庫伺服器狀態與全資料表體檢清單
// =========================================================================
define('PAGE_MARK', 'PG_SYS_DB');
define('PAGE_ITEM', 'ITEM_SYS');
define('PAGE_TOPAUTH', true);
define('bkRedir', 1);
require_once('../includeFiles/page.basic.inc.php');

$csrfToken  = function_exists('_fn_genToken') ? _fn_genToken() : ($_SESSION['token'] ?? '');
$dbVersion  = (string)DB->single("SELECT VERSION()");
$dbName     = (string)DB->single("SELECT DATABASE()");

// 讀取伺服器關鍵效能指標
$statusVars = [];
foreach ((DB->query("SHOW GLOBAL STATUS WHERE Variable_name IN ('Uptime','Threads_connected','Queries','Slow_queries','Innodb_buffer_pool_reads','Innodb_buffer_pool_read_requests')") ?: []) as $sv) {
  $statusVars[$sv['Variable_name']] = (int)$sv['Value'];
}
$bpReq  = max(1, $statusVars['Innodb_buffer_pool_read_requests'] ?? 1);
$bpRead = $statusVars['Innodb_buffer_pool_reads'] ?? 0;
$bpHitRate = round((1 - ($bpRead / $bpReq)) * 100, 2);
$uptimeDays = round(($statusVars['Uptime'] ?? 0) / 86400, 1);

// 讀取所有資料表狀態（含註解與碎片量）
$tableStatuses = DB->query("SHOW TABLE STATUS") ?: [];
$totalDataBytes  = 0;
$totalIndexBytes = 0;
$totalFreeBytes  = 0;
$totalEstRows    = 0;
$fragTableCount  = 0;

foreach ($tableStatuses as &$ts) {
  $dLen = (int)($ts['Data_length'] ?? 0);
  $iLen = (int)($ts['Index_length'] ?? 0);
  $fLen = (int)($ts['Data_free'] ?? 0);
  $rows = (int)($ts['Rows'] ?? 0);

  $totalDataBytes  += $dLen;
  $totalIndexBytes += $iLen;
  $totalFreeBytes  += $fLen;
  $totalEstRows    += $rows;
  if ($fLen >= 1024 * 64) $fragTableCount++; // 超過 64KB 碎片標記建議最佳化
}
unset($ts);

$totalUsedBytes = $totalDataBytes + $totalIndexBytes;
$maxQuotaBytes  = max(1, $DB_MAX_QUOTA_MB * 1048576);
$usedPercent    = min(100, round(($totalUsedBytes / $maxQuotaBytes) * 100, 2));
$remainBytes    = max(0, $maxQuotaBytes - $totalUsedBytes);
$donutColor     = ($usedPercent >= 85) ? '#dc3545' : (($usedPercent >= 65) ? '#ffc107' : '#0d6efd');

function _fn_fmt_bytes($bytes) {
  if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
  if ($bytes >= 1048576)    return round($bytes / 1048576, 2) . ' MB';
  if ($bytes >= 1024)       return round($bytes / 1024, 1) . ' KB';
  return $bytes . ' B';
}
?>
<!doctype html>
<html lang="zh-Hant-TW">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Fifth Season Life 人生第五季 | MySQL 資料庫最高級維護保養中心</title>
  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>

<body class="bg-body-tertiary theme-cyan p-2">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1">
    <i class="bi bi-shield-lock-fill text-danger"></i> <?php echo basename(__FILE__); ?>
  </div>

  <div class="container-fluid py-2">
    <!-- 頂部最高權限標題與全局操作列 -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div>
        <h5 class="mb-0 fw-bold">
          <i class="bi bi-database-fill-gear text-danger me-2"></i>MySQL 資料庫最高級維護保養中心
          <span class="badge text-bg-danger ms-1">SUPER ADMIN ONLY</span>
        </h5>
        <small class="text-muted">
          目標資料庫：<strong class="font-monospace text-primary"><?php echo htmlspecialchars($dbName); ?></strong> ｜
          引擎版本：<span class="font-monospace"><?php echo htmlspecialchars($dbVersion); ?></span> ｜
          運行時間：<?php echo $uptimeDays; ?> 天 ｜ 當前連線數：<?php echo (int)($statusVars['Threads_connected'] ?? 1); ?>
        </small>
      </div>

      <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm fw-bold" id="btnExactCountAll">
          <i class="bi bi-calculator me-1"></i>計算全庫精準筆數
        </button>
        <button type="button" class="btn btn-outline-success btn-sm fw-bold" id="btnExportSelectedSql">
          <i class="bi bi-download me-1"></i>匯出勾選 / 全庫 SQL 備份
        </button>
      </div>
    </div>

    <!-- 效能與容量 KPI 看板 -->
    <div class="row g-3 mb-3">
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
          <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between gap-2">
            <div>
              <div class="small text-muted fw-semibold">資料庫空間配額使用率</div>
              <div class="fs-5 fw-bold text-primary mt-1">
                <?php echo _fn_fmt_bytes($totalUsedBytes); ?>
                <small class="text-muted fw-normal fs-7">/ <?php echo _fn_fmt_bytes($maxQuotaBytes); ?></small>
              </div>
              <div class="small text-muted">
                資料 <?php echo _fn_fmt_bytes($totalDataBytes); ?> · 索引 <?php echo _fn_fmt_bytes($totalIndexBytes); ?>
              </div>
              <div class="small text-success">剩餘可用：<?php echo _fn_fmt_bytes($remainBytes); ?></div>
            </div>

            <!-- 純 CSS 環形比例圖 (Donut Chart) -->
            <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle position-relative"
                 style="width: 68px; height: 68px; background: conic-gradient(<?php echo $donutColor; ?> <?php echo $usedPercent; ?>%, #e9ecef 0);"
                 title="已使用 <?php echo $usedPercent; ?>%（剩餘 <?php echo round(100 - $usedPercent, 2); ?>%）">
              <div class="rounded-circle bg-white d-flex align-items-center justify-content-center fw-bold font-monospace"
                   style="width: 50px; height: 50px; font-size: 0.78rem; color: <?php echo $donutColor; ?>;">
                <?php echo $usedPercent >= 1 ? round($usedPercent, 1) : $usedPercent; ?>%
              </div>
            </div>
          </div>
        </div>
      </div>


      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
          <div class="card-body py-2 px-3">
            <div class="small text-muted fw-semibold">資料表總數 / 總資料筆數</div>
            <div class="fs-4 fw-bold text-success mt-1"><?php echo count($tableStatuses); ?> 張 / <span id="kpiTotalRows"><?php echo number_format($totalEstRows); ?></span> 筆</div>
            <div class="small text-muted" id="kpiRowModeHint">目前為引擎統計估值（可點右上計算精準值）</div>
          </div>
        </div>
      </div>

      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm border-start border-4 <?php echo $totalFreeBytes > 0 ? 'border-warning' : 'border-info'; ?> h-100">
          <div class="card-body py-2 px-3">
            <div class="small text-muted fw-semibold">可回收空間碎片 (Data Free)</div>
            <div class="fs-4 fw-bold <?php echo $totalFreeBytes > 0 ? 'text-warning' : 'text-info'; ?> mt-1"><?php echo _fn_fmt_bytes($totalFreeBytes); ?></div>
            <div class="small text-muted">建議最佳化資料表：<?php echo $fragTableCount; ?> 張</div>
          </div>
        </div>
      </div>

      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm border-start border-4 border-dark h-100">
          <div class="card-body py-2 px-3">
            <div class="small text-muted fw-semibold">InnoDB 快取命中率 / 慢查詢</div>
            <div class="fs-4 fw-bold text-dark mt-1"><?php echo $bpHitRate; ?>%</div>
            <div class="small text-muted">累計慢查詢次數：<?php echo number_format($statusVars['Slow_queries'] ?? 0); ?> 次</div>
          </div>
        </div>
      </div>
    </div>

    <!-- 批量維護指令工具列 & 定期瘦身保養 -->
    <div class="row g-3 mb-3">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white py-2 fw-bold d-flex justify-content-between align-items-center">
            <span><i class="bi bi-wrench-adjustable-circle text-primary me-1"></i>批量資料表維護保養指令（請先於下方勾選資料表）</span>
            <span class="badge text-bg-light border text-dark" id="selectedCountBadge">已勾選 0 張表</span>
          </div>
          <div class="card-body py-2 d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-primary btn-sm fw-bold btn-db-cmd" data-cmd="optimize">
              <i class="bi bi-stars me-1"></i>OPTIMIZE 最佳化重組（回收碎片）
            </button>
            <button type="button" class="btn btn-info btn-sm fw-bold text-dark btn-db-cmd" data-cmd="analyze">
              <i class="bi bi-bar-chart-steps me-1"></i>ANALYZE 更新索引統計（加速查詢）
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm fw-bold btn-db-cmd" data-cmd="check">
              <i class="bi bi-shield-check me-1"></i>CHECK 完整性檢查
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm fw-bold btn-db-cmd" data-cmd="repair">
              <i class="bi bi-bandaid me-1"></i>REPAIR 資料表修復
            </button>
            <button type="button" class="btn btn-outline-warning btn-sm ms-auto" id="btnSelectFragmented">
              僅勾選有碎片資料表 (<?php echo $fragTableCount; ?>)
            </button>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white py-2 fw-bold">
            <i class="bi bi-recycle text-success me-1"></i>歷史冗餘紀錄定期瘦身
          </div>
          <div class="card-body py-2">
            <div class="input-group input-group-sm">
              <span class="input-group-text">清理超過</span>
              <select class="form-select" id="selPurgeDays">
                <option value="90" selected>90 天</option>
                <option value="180">180 天</option>
                <option value="365">365 天</option>
              </select>
              <span class="input-group-text">已讀站內通知</span>
              <button type="button" class="btn btn-outline-danger fw-bold" id="btnPurgeLogs">執行清理</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 維運執行結果日誌區（執行後自動展開） -->
    <div class="card border-0 shadow-sm mb-3 d-none" id="maintLogCard">
      <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
        <span class="small fw-bold font-monospace"><i class="bi bi-terminal me-1 text-success"></i>MySQL 維護指令執行回報日誌</span>
        <button type="button" class="btn-close btn-close-white btn-sm" onclick="document.getElementById('maintLogCard').classList.add('d-none')"></button>
      </div>
      <div class="card-body bg-dark text-light py-2 px-3">
        <pre class="mb-0 small font-monospace text-info" id="maintLogOutput" style="max-height: 200px; overflow-y: auto;"></pre>
      </div>
    </div>

    <!-- 全資料表健康體檢與容量明細表 -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="fw-bold"><i class="bi bi-table text-primary me-1"></i>全資料庫資料表體檢與容量明細</span>
        <input type="text" class="form-control form-control-sm w-auto" id="tblFilterInput" placeholder="快速過濾資料表名稱或註解...">
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="dbTableList">
          <thead class="table-light">
            <tr>
              <th style="width: 36px;"><input class="form-check-input" type="checkbox" id="chkAllTables"></th>
              <th>資料表名稱 / 備註說明</th>
              <th>引擎 / 編碼</th>
              <th class="text-end">資料筆數</th>
              <th class="text-end">資料容量</th>
              <th class="text-end">索引容量</th>
              <th class="text-end">空間碎片 (Free)</th>
              <th class="text-center">健康狀態</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tableStatuses as $ts):
              $tName   = (string)$ts['Name'];
              $comment = (string)($ts['Comment'] ?? '');
              $engine  = (string)($ts['Engine'] ?? 'InnoDB');
              $coll    = (string)($ts['Collation'] ?? '');
              $estRows = (int)($ts['Rows'] ?? 0);
              $dBytes  = (int)($ts['Data_length'] ?? 0);
              $iBytes  = (int)($ts['Index_length'] ?? 0);
              $fBytes  = (int)($ts['Data_free'] ?? 0);
              $hasFrag = ($fBytes >= 1024 * 64);
            ?>
            <tr data-tname="<?php echo htmlspecialchars(strtolower($tName . ' ' . $comment)); ?>">
              <td>
                <input class="form-check-input chk-tbl-item" type="checkbox" value="<?php echo htmlspecialchars($tName); ?>" data-frag="<?php echo $hasFrag ? '1' : '0'; ?>">
              </td>
              <td>
                <div class="fw-bold font-monospace text-primary"><?php echo htmlspecialchars($tName); ?></div>
                <?php if ($comment !== ''): ?>
                  <small class="text-muted"><?php echo htmlspecialchars($comment); ?></small>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge text-bg-light border text-dark"><?php echo htmlspecialchars($engine); ?></span>
                <div class="small text-muted font-monospace"><?php echo htmlspecialchars($coll); ?></div>
              </td>
              <td class="text-end font-monospace fw-semibold cell-row-count" data-table="<?php echo htmlspecialchars($tName); ?>">
                <?php echo number_format($estRows); ?>
              </td>
              <td class="text-end font-monospace"><?php echo _fn_fmt_bytes($dBytes); ?></td>
              <td class="text-end font-monospace"><?php echo _fn_fmt_bytes($iBytes); ?></td>
              <td class="text-end font-monospace <?php echo $hasFrag ? 'text-warning fw-bold' : 'text-muted'; ?>">
                <?php echo _fn_fmt_bytes($fBytes); ?>
              </td>
              <td class="text-center">
                <?php if ($hasFrag): ?>
                  <span class="badge text-bg-warning text-dark">建議最佳化</span>
                <?php else: ?>
                  <span class="badge text-bg-success">健康正常</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
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
      window.location.replace('sys_db_maint.php');
    }

    function getSelectedTables() {
      return $('.chk-tbl-item:checked').map(function() { return $(this).val(); }).get();
    }

    function updateSelectedBadge() {
      const cnt = getSelectedTables().length;
      $('#selectedCountBadge').text(`已勾選 ${cnt} 張表`);
    }

    $(document).ready(function() {
      // 全選 / 單選連動
      $('#chkAllTables').on('change', function() {
        $('.chk-tbl-item:visible').prop('checked', $(this).is(':checked'));
        updateSelectedBadge();
      });
      $(document).on('change', '.chk-tbl-item', updateSelectedBadge);

      // 僅勾選有碎片資料表
      $('#btnSelectFragmented').on('click', function() {
        $('.chk-tbl-item').prop('checked', false);
        $('.chk-tbl-item[data-frag="1"]').prop('checked', true);
        updateSelectedBadge();
      });

      // 快速過濾資料表
      $('#tblFilterInput').on('input', function() {
        const kw = ($(this).val() || '').toLowerCase().trim();
        $('#dbTableList tbody tr').each(function() {
          const txt = $(this).data('tname') || '';
          $(this).toggle(!kw || txt.includes(kw));
        });
      });

      // 計算全庫精準筆數 (SELECT COUNT(*))
      $('#btnExactCountAll').on('click', function() {
        const $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>計算中...');
        const allTables = $('.chk-tbl-item').map(function() { return $(this).val(); }).get();

        $.post('sys_db_maint.php', {
          ajaxAction: 'exact_count',
          token: CSRF_TOKEN,
          tables: allTables
        }, function(res) {
          $btn.prop('disabled', false).html('<i class="bi bi-calculator me-1"></i>計算全庫精準筆數');
          if (res.RTN == 1 && res.counts) {
            let sum = 0;
            Object.entries(res.counts).forEach(([tbl, cnt]) => {
              sum += Number(cnt);
              $(`.cell-row-count[data-table="${tbl}"]`).text(Number(cnt).toLocaleString()).addClass('text-primary');
            });
            $('#kpiTotalRows').text(sum.toLocaleString());
            $('#kpiRowModeHint').html('<span class="text-success fw-bold">✔ 已完成全庫精準 COUNT(*) 核算</span>');
            if (window.parent && window.parent.toastr) window.parent.toastr['success']('全庫精準資料筆數計算完成！');
          }
        }, 'json');
      });

      // 執行批量維運指令 (OPTIMIZE / ANALYZE / CHECK / REPAIR)
      $('.btn-db-cmd').on('click', function() {
        const cmd = $(this).data('cmd');
        const tables = getSelectedTables();
        if (!tables.length) {
          alert('請先勾選至少一張要維護的資料表！');
          return;
        }
        if (!confirm(`確定要對已勾選的 ${tables.length} 張資料表執行 ${cmd.toUpperCase()} 作業嗎？`)) return;

        const $btn = $(this);
        const origHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>執行中...');

        $.ajax({
          type: 'POST',
          url: 'sys_db_maint.php',
          data: {
            ajaxAction: cmd,
            token: (typeof getTokenVal === 'function') ? getTokenVal() : CSRF_TOKEN,
            tables: tables
          },
          dataType: 'json'
        })
        .done(function(res) {
          if (window.parent && window.parent.toastr) {
            window.parent.toastr[res.msgType || 'info'](res.msgStr || '指令執行完成');
          } else if (res.msgStr) {
            alert(res.msgStr);
          }

          if (res.logs && res.logs.length) {
            $('#maintLogCard').removeClass('d-none');
            $('#maintLogOutput').text(res.logs.join('\n'));
          } else if (res.RTN == 0) {
            $('#maintLogCard').removeClass('d-none');
            $('#maintLogOutput').text('[ERROR] ' + (res.msgStr || '執行失敗'));
          }
        })
        .fail(function(xhr) {
          $('#maintLogCard').removeClass('d-none');
          $('#maintLogOutput').text(`[HTTP ${xhr.status}] 伺服器回應異常：\n` + (xhr.responseText || '無回應內容'));
          alert('維運指令執行失敗，請查看下方黑色回報日誌細節！');
        })
        .always(function() {
          $btn.prop('disabled', false).html(origHtml);
        });
      });


      // 匯出勾選 / 全庫 SQL 備份
      $('#btnExportSelectedSql').on('click', function() {
        const tables = getSelectedTables();
        const msg = tables.length
          ? `即將匯出已勾選的 ${tables.length} 張資料表 SQL 備份檔，是否繼續？`
          : '目前未勾選特定資料表，將匯出「全資料庫」完整 SQL 備份檔，是否繼續？';
        if (!confirm(msg)) return;
        const url = `sys_db_maint.php?ajaxAction=export_sql&token=${encodeURIComponent(CSRF_TOKEN)}&tables=${encodeURIComponent(tables.join(','))}`;
        window.location.href = url;
      });

      // 清理過期已讀站內通知
      $('#btnPurgeLogs').on('click', function() {
        const days = $('#selPurgeDays').val();
        if (!confirm(`確定要永久刪除超過 ${days} 天的「已讀站內通知」並重組資料表嗎？`)) return;
        $.post('sys_db_maint.php', {
          ajaxAction: 'purge_old_logs',
          token: CSRF_TOKEN,
          target: 'notifications',
          days: days
        }, function(res) {
          if (window.parent && window.parent.toastr) window.parent.toastr[res.msgType](res.msgStr);
          if (res.RTN == 1) setTimeout(() => location.reload(), 900);
        }, 'json');
      });

      if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
      }
    });
  </script>
</body>
</html>
