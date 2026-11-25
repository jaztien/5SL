<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 架構搜尋條件
$searchText   = isset($_REQUEST['searchText']) ? trim(rawurldecode($_REQUEST['searchText'])) : '';
$searchType   = isset($_REQUEST['searchType']) ? trim($_REQUEST['searchType']) : '';
$searchStatus = isset($_REQUEST['searchStatus']) ? trim($_REQUEST['searchStatus']) : '';

$searchURI = "";
if ($searchText !== '')   { $searchURI .= "&searchText=" . rawurlencode($searchText); }
if ($searchType !== '')   { $searchURI .= "&searchType=" . rawurlencode($searchType); }
if ($searchStatus !== '') { $searchURI .= "&searchStatus=" . rawurlencode($searchStatus); }
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}

$maxResult = (!empty($searchURI)) ? 300 : 100;

// 定義本頁面代碼（對應左側樹狀導航列與頂部操作面板）
define('PAGE_MARK', 'PG_COURSE');
define('PAGE_ITEM', 'ITEM_COURSE');
define('PAGE_OPANEL', 1); // 1-新增
define('SEARCH_OPANEL', !empty($searchURI) ? 2 : 1);
define('EXPORT_OPANEL', 3); // 3-Excel+Print

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

// 組合查詢條件（關聯主課程名稱、梯次數與有效報名學員數）
$whereSql = " WHERE c.course_status != 9 ";
$params   = array();

if ($searchText !== '') {
  $whereSql .= " AND (c.course_title LIKE ? OR c.course_subtitle LIKE ? OR c.course_badge_text LIKE ?) ";
  $likeStr = '%' . $searchText . '%';
  array_push($params, $likeStr, $likeStr, $likeStr);
}
if ($searchType !== '') {
  $whereSql .= " AND c.course_type = ? ";
  $params[] = (int)$searchType;
}
if ($searchStatus !== '') {
  $whereSql .= " AND c.course_status = ? ";
  $params[] = (int)$searchStatus;
}

$totalNum = (int)DB->single("SELECT COUNT(c.course_id) FROM fsl_courses c" . $whereSql, $params);
$sqlList  = "
  SELECT c.*,
         p.course_title AS parent_title,
         (SELECT COUNT(*) FROM fsl_course_sessions s WHERE s.course_id = IF(c.parent_id > 0, c.parent_id, c.course_id) AND s.sess_status != 9) AS sess_cnt,
         (SELECT COUNT(*) FROM fsl_course_enrolls e WHERE e.course_id = IF(c.parent_id > 0, c.parent_id, c.course_id) AND e.enr_status = 1) AS enroll_cnt
  FROM fsl_courses c
  LEFT JOIN fsl_courses p ON c.parent_id = p.course_id
  {$whereSql}
  ORDER BY c.course_sort ASC, c.course_id DESC
  LIMIT {$maxResult}
";
$csList = DB->query($sqlList, $params);
if (!is_array($csList)) $csList = array();

$tierMap = array(
  'plan-tier-basic'    => '<span class="badge text-bg-secondary">基本 (Basic)</span>',
  'plan-tier-standard' => '<span class="badge text-bg-dark">一般 (Standard)</span>',
  'plan-tier-premium'  => '<span class="badge text-bg-warning">尊榮熱門 (Premium)</span>',
  'plan-tier-special'  => '<span class="badge bg-success-subtle text-success border border-success">特殊特惠 (Special)</span>'
);
$statusMap = array(
  0 => '<span class="badge text-bg-secondary">下架停售</span>',
  1 => '<span class="badge text-bg-success">上架販售中</span>',
  2 => '<span class="badge text-bg-warning">草稿籌備中</span>'
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
  <title>Fifth Season Life 人生第五季 | 課程與方案列表</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>
<body class="bg-body-tertiary theme-green">
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                課程與價格方案管理
                <i class="bi bi-question-circle-fill fs-6 text-secondary" data-bs-toggle="tooltip" title="管理主課程、特惠子方案、開課梯次與報名學員名單"></i>
              </h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">課程管理系統</li>
                <li class="breadcrumb-item active">課程與方案列表</li>
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
                  <!-- 左側：Title 與筆數徽章 -->
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold"><i class="bi bi-journal-richtext me-1 text-success"></i>課程與價格方案清單</span>
                    <span class="badge text-bg-light border">共 <span id="resultCount"><?php echo $totalNum > $maxResult ? $totalNum . " 筆符合，表列 " . $maxResult : $totalNum; ?></span> 筆</span>
                  </div>
                  <!-- 右側：手機版自動填滿 100%，平板/電腦版 (md 以上) 縮為自動寬度並靠右 -->
                  <div class="input-group input-group-sm w-auto flex-grow-1 flex-md-grow-0 ms-md-auto">
                    <span class="input-group-text bg-white"><i class="bi bi-funnel"></i></span>
                    <input type="text" id="dataSearch" class="form-control" placeholder="表格內即時快篩...">
                    <span class="input-group-text bg-white"><i class="bi bi-x-lg"></i></span>
                  </div>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table id="dataTable_cslist" class="table table-striped table-hover table-bordered align-middle w-100 small"  data-search="1">
                      <thead class="table-light text-nowrap">
                        <tr>
                          <th class="text-center" style="width: 55px;">ID</th>
                          <th class="text-center" style="width: 72px;">封面</th>
                          <th>課程/方案名稱</th>
                          <th class="text-center">方案歸屬</th>
                          <th class="text-end">價格/單位</th>
                          <th class="text-center">卡片樣式</th>
                          <th class="text-center">首頁價卡</th>
                          <th class="text-center">梯次/學員</th>
                          <th class="text-center">排序</th>
                          <th class="text-center">狀態</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($csList as $row):
                          $cid       = (int)$row['course_id'];
                          $pid       = (int)($row['parent_id'] ?? 0);
                          $coverFile = "../uploadFiles/cs/cimg_" . ($pid > 0 && !file_exists("../uploadFiles/cs/cimg_{$cid}_.jpg") ? $pid : $cid) . "_.jpg";
                          $coverSrc  = file_exists($coverFile) ? ($coverFile . "?t=" . filemtime($coverFile)) : "../imageFiles/nopic_user_200x200.jpg";
                          $tierKey   = $row['course_tier_class'] ?? 'plan-tier-standard';
                          $sta       = (int)($row['course_status'] ?? 1);
                          $showPrice = ((int)($row['course_show_pricing'] ?? 1) === 1);
                        ?>
                        <tr style="cursor: pointer;" onclick="window.location.href='cs_detail.php?currID=<?php echo $cid; ?>';">
                          <td class="text-center fw-bold"><?php echo $cid; ?></td>
                          <td class="text-center">
                            <img src="<?php echo $coverSrc; ?>" class="rounded border" width="52" height="38" style="object-fit: cover;" alt="Cover">
                          </td>
                          <td>
                            <div class="fw-bold text-dark">
                              <?php if ((int)($row['course_is_hot'] ?? 0) === 1): ?>
                                <span class="badge text-bg-danger me-1">置中主打</span>
                              <?php endif; ?>
                              <?php echo htmlspecialchars($row['course_title'] ?? ''); ?>
                            </div>
                            <?php if (!empty($row['course_subtitle'])): ?>
                              <div class="text-muted" style="font-size: 0.78rem;"><?php echo htmlspecialchars($row['course_subtitle']); ?></div>
                            <?php endif; ?>
                          </td>
                          <td class="text-center">
                            <?php if ($pid > 0): ?>
                              <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                <i class="bi bi-diagram-2 me-1"></i>子方案 (#<?php echo $pid; ?>)
                              </span>
                            <?php else: ?>
                              <span class="badge bg-light text-dark border">獨立主課程</span>
                            <?php endif; ?>
                          </td>
                          <td class="text-end">
                            <span class="fw-bold text-success-emphasis">NT$ <?php echo number_format((float)($row['course_cost'] ?? 0)); ?></span>
                            <?php if (!empty($row['course_price_unit'])): ?>
                              <div class="text-muted" style="font-size: 0.75rem;">/ <?php echo htmlspecialchars($row['course_price_unit']); ?></div>
                            <?php endif; ?>
                          </td>
                          <td class="text-center"><?php echo $tierMap[$tierKey] ?? $tierMap['plan-tier-standard']; ?></td>
                          <td class="text-center">
                            <?php if ($showPrice): ?>
                              <i class="bi bi-check-circle-fill text-success fs-6" title="顯示於首頁價格卡片"></i>
                            <?php else: ?>
                              <i class="bi bi-dash-circle text-muted fs-6" title="不顯示於價格卡片"></i>
                            <?php endif; ?>
                          </td>
                          <td class="text-center">
                            <span class="badge text-bg-light border text-dark"><?php echo (int)$row['sess_cnt']; ?> 梯</span>
                            <span class="badge text-bg-primary"><?php echo (int)$row['enroll_cnt']; ?> 人</span>
                          </td>
                          <td class="text-center text-muted"><?php echo (int)($row['course_sort'] ?? 100); ?></td>
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
        <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-funnel-fill me-2 text-warning"></i>課程進階條件搜尋</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <form id="form_search" method="get" action="cs_list.php">
          <div class="mb-3">
            <label class="form-label small text-white-50">關鍵字（課程名稱 / 副標題 / 徽章）</label>
            <input type="text" class="form-control form-control-sm" name="searchText" value="<?php echo htmlspecialchars($searchText); ?>" placeholder="輸入搜尋關鍵字...">
          </div>
          <div class="mb-3">
            <label class="form-label small text-white-50">課程性質類型</label>
            <select class="form-select form-select-sm" name="searchType">
              <option value="">-- 全部類型 --</option>
              <option value="1" <?php echo ($searchType === '1') ? 'selected' : ''; ?>>1 - 系列陪跑 / 團體工作坊</option>
              <option value="2" <?php echo ($searchType === '2') ? 'selected' : ''; ?>>2 - 一對一深度顧問預約</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label small text-white-50">上下架狀態</label>
            <select class="form-select form-select-sm" name="searchStatus">
              <option value="">-- 全部狀態 --</option>
              <option value="1" <?php echo ($searchStatus === '1') ? 'selected' : ''; ?>>1 - 上架販售中</option>
              <option value="2" <?php echo ($searchStatus === '2') ? 'selected' : ''; ?>>2 - 草稿籌備中</option>
              <option value="0" <?php echo ($searchStatus === '0') ? 'selected' : ''; ?>>0 - 下架停售</option>
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
  function doButAdd() {
    window.location.href = 'cs_detail.php?currID=0';
  }
  function doButReset() {
    window.location.replace('cs_list.php');
  }
  function doButSerach() {
    const offcanvasEl = document.getElementById('offcanvasSearch');
    if (offcanvasEl) {
      bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).toggle();
    }
  }
  function doButExcel() {
    $('.buttons-excel').click();
  }
  function doButPrint() {
    $('.buttons-print').click();
  }

  $(document).ready(function() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));

    $('body').on('keypress click', function() {
      let timerElm = window.parent.$('#timer_on_exhausted');
      if (timerElm.length) timerElm.backward_timer('reset');
    });

    var cslistTB = datatableCreate('dataTable_cslist');

    $('#dataSearch').on('keyup search input paste cut', function() {
      if ($(this).is(':focus') || this.value !== '') {
        cslistTB.search(this.value).draw();
      }
    });

    $('#searchReset').click(function() {
      if ($('#dataSearch').val() !== '') {
        $('#dataSearch').val('');
        cslistTB.search('').draw();
      } else {
        window.location.replace('cs_list.php');
      }
    });

    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
      window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  });
  </script>
</body>
</html>
