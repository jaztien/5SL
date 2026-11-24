<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 架構搜尋條件
$searchText   = isset($_REQUEST['searchText']) ? trim(rawurldecode($_REQUEST['searchText'])) : '';
$searchMode   = isset($_REQUEST['searchMode']) ? trim($_REQUEST['searchMode']) : '';
$searchStatus = isset($_REQUEST['searchStatus']) ? trim($_REQUEST['searchStatus']) : '';

$searchURI = "";
if ($searchText !== '')   { $searchURI .= "&searchText=" . rawurlencode($searchText); }
if ($searchMode !== '')   { $searchURI .= "&searchMode=" . rawurlencode($searchMode); }
if ($searchStatus !== '') { $searchURI .= "&searchStatus=" . rawurlencode($searchStatus); }
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}

$maxResult = (!empty($searchURI)) ? 300 : 120;

// 定義本頁面代碼
define('PAGE_MARK', 'PG_LOTTERY');
define('PAGE_ITEM', 'ITEM_ACT');
define('PAGE_OPANEL', 1); // 1-新增
define('SEARCH_OPANEL', !empty($searchURI) ? 2 : 1);
define('EXPORT_OPANEL', 3); // 3-Excel+Print

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

// 組合查詢條件
$whereSql = " WHERE l.lot_status != 9 ";
$params   = array();

if ($searchText !== '') {
  $whereSql .= " AND (l.lot_title LIKE ? OR l.lot_subtitle LIKE ? OR l.lot_keycode LIKE ?) ";
  $likeStr = '%' . $searchText . '%';
  array_push($params, $likeStr, $likeStr, $likeStr);
}
if ($searchMode !== '') {
  $whereSql .= " AND l.lot_mode = ? ";
  $params[] = (int)$searchMode;
}
if ($searchStatus !== '') {
  $whereSql .= " AND l.lot_status = ? ";
  $params[] = (int)$searchStatus;
}

$totalNum = (int)DB->single("SELECT COUNT(l.lot_id) FROM fsl_lotteries l" . $whereSql, $params);
$lotList  = DB->query("
  SELECT l.*,
         (SELECT COUNT(*) FROM fsl_lottery_items li WHERE li.lot_id = l.lot_id) AS item_cnt,
         (SELECT COALESCE(SUM(li.loti_won_count), 0) FROM fsl_lottery_items li WHERE li.lot_id = l.lot_id) AS total_won
  FROM fsl_lotteries l
  {$whereSql}
  ORDER BY l.lot_id DESC
  LIMIT {$maxResult}
", $params);
if (!is_array($lotList)) $lotList = array();

$modeMap = array(
  1 => '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle"><i class="bi bi-card-heading me-1"></i>1-優惠卡片</span>',
  2 => '<span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle"><i class="bi bi-bullseye me-1"></i>2-幸運輪盤</span>',
  3 => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-eraser me-1"></i>3-刮刮樂</span>',
  4 => '<span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle"><i class="bi bi-box2-heart me-1"></i>4-驚喜寶箱</span>'
);
$periodMap = array(0 => '活動期間總計', 1 => '每日重置', 2 => '每週重置', 3 => '每月重置');
$statusMap = array(
  0 => '<span class="badge text-bg-secondary">0 - 已關閉</span>',
  1 => '<span class="badge text-bg-success">1 - 進行中</span>'
);
?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
  <title>Fifth Season Life 人生第五季 | 幸運輪盤與互動抽獎管理</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>
<body class="bg-body-tertiary theme-pink">
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                幸運輪盤與互動抽獎管理
                <i class="bi bi-question-circle-fill fs-6 text-secondary" data-bs-toggle="tooltip" title="管理優惠卡片、幸運輪盤、刮刮樂與驚喜寶箱之活動期程、格位權重、禮品串接與資格規則"></i>
                <span class="text-muted small ms-2">lot_list.php</span>
              </h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">互動行銷管理</li>
                <li class="breadcrumb-item active">幸運輪盤與抽獎管理</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card card-outline shadow-sm">
                <div class="card-header d-flex flex-wrap align-items-center gap-2">
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold"><i class="bi bi-bullseye me-1 text-danger"></i>抽獎與互動活動清單</span>
                    <span class="badge text-bg-light border">共 <span id="resultCount"><?php echo $totalNum > $maxResult ? $totalNum . " 筆符合，表列 " . $maxResult : $totalNum; ?></span> 檔</span>
                  </div>
                  <div class="input-group input-group-sm w-auto flex-grow-1 flex-md-grow-0 ms-md-auto">
                    <span class="input-group-text bg-white"><i class="bi bi-funnel"></i></span>
                    <input type="text" id="dataSearch" class="form-control" placeholder="表格內即時快篩...">
                  </div>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table id="dataTable_lotlist" class="table table-striped table-hover table-bordered align-middle w-100 small" data-search="1">
                      <thead class="table-light text-nowrap">
                        <tr>
                          <th class="text-center" style="width: 55px;">ID</th>
                          <th>呼叫代碼 / 活動名稱</th>
                          <th class="text-center">抽獎模式</th>
                          <th>活動起訖期程</th>
                          <th class="text-center">消耗點數 / 參加限制</th>
                          <th class="text-center">獎項池格數 / 累計抽出</th>
                          <th class="text-center">狀態</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($lotList as $row):
                          $lid      = (int)$row['lot_id'];
                          $mode     = (int)($row['lot_mode'] ?? 1);
                          $costPts  = (int)($row['lot_cost_pts'] ?? 0);
                          $limUser  = (int)($row['lot_limit_per_user'] ?? 1);
                          $limPer   = (int)($row['lot_limit_period'] ?? 0);
                          $sta      = (int)($row['lot_status'] ?? 1);
                        ?>
                        <tr style="cursor: pointer;" onclick="window.location.href='lot_detail.php?currID=<?php echo $lid; ?>';">
                          <td class="text-center fw-bold"><?php echo $lid; ?></td>
                          <td>
                            <div class="fw-bold text-dark">
                              <code class="me-1 text-danger">[<?php echo htmlspecialchars($row['lot_keycode']); ?>]</code>
                              <?php echo htmlspecialchars($row['lot_title']); ?>
                            </div>
                            <?php if (!empty($row['lot_subtitle'])): ?>
                              <div class="text-muted" style="font-size: 0.78rem;"><?php echo htmlspecialchars($row['lot_subtitle']); ?></div>
                            <?php endif; ?>
                          </td>
                          <td class="text-center"><?php echo $modeMap[$mode] ?? $modeMap; ?></td>
                          <td style="font-size: 0.78rem;">
                            <div>起：<?php echo !empty($row['lot_start_at']) ? substr($row['lot_start_at'], 0, 16) : '立即開始'; ?></div>
                            <div class="text-muted">訖：<?php echo !empty($row['lot_end_at']) ? substr($row['lot_end_at'], 0, 16) : '常駐活動'; ?></div>
                          </td>
                          <td class="text-center">
                            <?php if ($costPts > 0): ?>
                              <span class="badge bg-warning text-dark"><?php echo number_format($costPts); ?> PTS / 次</span>
                            <?php else: ?>
                              <span class="badge bg-success-subtle text-success border border-success">免費參加</span>
                            <?php endif; ?>
                            <div class="text-muted mt-1" style="font-size: 0.72rem;">
                              <?php echo ($limUser === 0) ? '不限次數' : ($periodMap[$limPer] . '限 ' . $limUser . ' 次'); ?>
                            </div>
                          </td>
                          <td class="text-center">
                            <span class="badge text-bg-light border text-dark"><?php echo (int)$row['item_cnt']; ?> 格位</span>
                            <span class="badge text-bg-primary"><?php echo number_format((int)$row['total_won']); ?> 次抽出</span>
                          </td>
                          <td class="text-center"><?php echo $statusMap[$sta] ?? '<span class="badge text-bg-secondary">未知</span>'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- 右側進階搜尋 Offcanvas -->
    <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasSearch" aria-labelledby="offcanvasSearchLabel">
      <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-funnel-fill me-2 text-warning"></i>抽獎活動進階篩選</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <form id="form_search" method="get" action="lot_list.php">
          <div class="mb-3">
            <label class="form-label small text-white-50">關鍵字（活動名稱 / 副標題 / 呼叫代碼）</label>
            <input type="text" class="form-control form-control-sm" name="searchText" value="<?php echo htmlspecialchars($searchText); ?>" placeholder="輸入搜尋關鍵字...">
          </div>
          <div class="mb-3">
            <label class="form-label small text-white-50">互動抽獎模式</label>
            <select class="form-select form-select-sm" name="searchMode">
              <option value="">-- 全部模式 --</option>
              <option value="1" <?php echo ($searchMode === '1') ? 'selected' : ''; ?>>1 - 優惠卡片 (調取單一禮品)</option>
              <option value="2" <?php echo ($searchMode === '2') ? 'selected' : ''; ?>>2 - 幸運輪盤 (多禮品權重擇一)</option>
              <option value="3" <?php echo ($searchMode === '3') ? 'selected' : ''; ?>>3 - 刮刮樂</option>
              <option value="4" <?php echo ($searchMode === '4') ? 'selected' : ''; ?>>4 - 驚喜寶箱</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label small text-white-50">活動狀態</label>
            <select class="form-select form-select-sm" name="searchStatus">
              <option value="">-- 全部狀態 --</option>
              <option value="1" <?php echo ($searchStatus === '1') ? 'selected' : ''; ?>>1 - 進行中</option>
              <option value="0" <?php echo ($searchStatus === '0') ? 'selected' : ''; ?>>0 - 已關閉</option>
            </select>
          </div>
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-warning btn-sm fw-bold"><i class="bi bi-search me-1"></i>執行條件搜尋</button>
            <button type="button" id="searchReset" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i>清除所有條件</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <script src="../pluginFiles/datatables3/datatables.min.js"></script>
  <script src="../scriptFiles/adminlte.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

  <script type="text/javascript">
  function doButAdd() { window.location.href = 'lot_detail.php?currID=0'; }
  function doButReset() { window.location.replace('lot_list.php'); }
  function doButSerach() {
    const offcanvasEl = document.getElementById('offcanvasSearch');
    if (offcanvasEl) bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).toggle();
  }
  function doButExcel() { $('.buttons-excel').click(); }
  function doButPrint() { $('.buttons-print').click(); }

  $(document).ready(function() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));

    $('body').on('keypress click', function() {
      let timerElm = window.parent.$('#timer_on_exhausted');
      if (timerElm.length) timerElm.backward_timer('reset');
    });

    var lotlistTB = datatableCreate('dataTable_lotlist');

    $('#dataSearch').on('keyup search input paste cut', function() {
      if ($(this).is(':focus') || this.value !== '') {
        lotlistTB.search(this.value).draw();
      }
    });

    $('#searchReset').click(function() {
      if ($('#dataSearch').val() !== '') {
        $('#dataSearch').val('');
        lotlistTB.search('').draw();
      } else {
        window.location.replace('lot_list.php');
      }
    });

    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
      window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  });
  </script>
</body>
</html>
