<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 架構搜尋條件
$searchText   = isset($_REQUEST['searchText']) ? trim(rawurldecode($_REQUEST['searchText'])) : '';
$searchMode   = isset($_REQUEST['searchMode']) ? trim($_REQUEST['searchMode']) : '';
$searchCate   = isset($_REQUEST['searchCate']) ? trim($_REQUEST['searchCate']) : '';
$searchStatus = isset($_REQUEST['searchStatus']) ? trim($_REQUEST['searchStatus']) : '';

$searchURI = "";
if ($searchText !== '')   { $searchURI .= "&searchText=" . rawurlencode($searchText); }
if ($searchMode !== '')   { $searchURI .= "&searchMode=" . rawurlencode($searchMode); }
if ($searchCate !== '')   { $searchURI .= "&searchCate=" . rawurlencode($searchCate); }
if ($searchStatus !== '') { $searchURI .= "&searchStatus=" . rawurlencode($searchStatus); }
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}

$maxResult = (!empty($searchURI)) ? 300 : 120;

// 定義本頁面代碼
define('PAGE_MARK', 'PG_ARTI');
define('PAGE_ITEM', 'ITEM_ARTI');
define('PAGE_OPANEL', 1); // 1-新增
define('SEARCH_OPANEL', !empty($searchURI) ? 2 : 1);
define('EXPORT_OPANEL', 3); // 3-Excel+Print

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

// 組合查詢條件
$whereSql = " WHERE a.arti_status != 9 ";
$params   = array();

if ($searchText !== '') {
  $whereSql .= " AND (a.arti_title LIKE ? OR a.arti_subscript LIKE ? OR a.arti_keycode LIKE ? OR a.arti_tag LIKE ?) ";
  $likeStr = '%' . $searchText . '%';
  array_push($params, $likeStr, $likeStr, $likeStr, $likeStr);
}
if ($searchMode !== '') {
  $whereSql .= " AND a.arti_mode = ? ";
  $params[] = (int)$searchMode;
}
if ($searchCate !== '') {
  $whereSql .= " AND a.arti_cate = ? ";
  $params[] = $searchCate;
}
if ($searchStatus !== '') {
  $whereSql .= " AND a.arti_status = ? ";
  $params[] = (int)$searchStatus;
}

$totalNum = (int)DB->single("SELECT COUNT(a.arti_id) FROM fsl_articles a" . $whereSql, $params);
$artList  = DB->query("SELECT a.* FROM fsl_articles a {$whereSql} ORDER BY a.arti_is_featured DESC, a.arti_id DESC LIMIT {$maxResult}", $params);
if (!is_array($artList)) $artList = array();

$modeMap = array(
  1 => '<span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle"><i class="bi bi-journal-richtext me-1"></i>知識專欄文章</span>',
  2 => '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle"><i class="bi bi-bell me-1"></i>系統通知模板</span>',
  3 => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-envelope-paper me-1"></i>Email / EDM 模板</span>',
  4 => '<span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle"><i class="bi bi-file-earmark-ruled me-1"></i>法律條款 / 靜態頁</span>'
);
$cateMap = array(
  'mindset' => '心靈與生命整理',
  'healing' => '自然療癒與實作',
  'notice'  => '官方公告與快訊',
  'tpl'     => '系統動態變數模板',
  'legal'   => '服務條款與隱私權'
);
$browseMap = array(
  1 => '<span class="badge bg-light text-dark border">公開免費</span>',
  2 => '<span class="badge text-bg-primary">限登入會員</span>',
  3 => '<span class="badge text-bg-warning">點數解鎖</span>'
);
$statusMap = array(
  0 => '<span class="badge text-bg-secondary">0 - 下架停用</span>',
  1 => '<span class="badge text-bg-success">1 - 上架啟用</span>',
  2 => '<span class="badge text-bg-warning">2 - 草稿待審</span>'
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
  <title>Fifth Season Life 人生第五季 | 知識專欄與動態模板管理</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>
<body class="bg-body-tertiary theme-blue">
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                知識專欄文章與動態模板管理
                <i class="bi bi-question-circle-fill fs-6 text-secondary" data-bs-toggle="tooltip" title="管理知識專欄文章、PTS解鎖閱讀，以及支援 {{變數}} 動態替換的系統通知、Email 與 EDM 模板"></i>
                <span class="text-muted small ms-2">art_list.php</span>
              </h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">互動行銷管理</li>
                <li class="breadcrumb-item active">知識專欄與動態模板</li>
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
                    <span class="fw-bold"><i class="bi bi-journal-richtext me-1 text-primary"></i>專欄文章與動態變數模板清單</span>
                    <span class="badge text-bg-light border">共 <span id="resultCount"><?php echo $totalNum > $maxResult ? $totalNum . " 筆符合，表列 " . $maxResult : $totalNum; ?></span> 篇</span>
                  </div>
                  <div class="input-group input-group-sm w-auto flex-grow-1 flex-md-grow-0 ms-md-auto">
                    <span class="input-group-text bg-white"><i class="bi bi-funnel"></i></span>
                    <input type="text" id="dataSearch" class="form-control" placeholder="表格內即時快篩...">
                  </div>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table id="dataTable_artlist" class="table table-striped table-hover table-bordered align-middle w-100 small" data-search="1">
                      <thead class="table-light text-nowrap">
                        <tr>
                          <th class="text-center" style="width: 55px;">ID</th>
                          <th class="text-center">內容定位模式</th>
                          <th>調用代碼 / 標題與主旨</th>
                          <th>分類 / 支援變數</th>
                          <th class="text-center">閱讀權限</th>
                          <th class="text-center">上架排程 / 瀏覽</th>
                          <th class="text-center">狀態</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($artList as $row):
                          $aid     = (int)$row['arti_id'];
                          $mode    = (int)($row['arti_mode'] ?? 1);
                          $browse  = (int)($row['arti_guestbrowse'] ?? 1);
                          $sta     = (int)($row['arti_status'] ?? 1);
                        ?>
                        <tr style="cursor: pointer;" onclick="window.location.href='art_detail.php?currID=<?php echo $aid; ?>';">
                          <td class="text-center fw-bold"><?php echo $aid; ?></td>
                          <td class="text-center"><?php echo $modeMap[$mode] ?? $modeMap; ?></td>
                          <td>
                            <div class="fw-bold text-dark">
                              <?php if ((int)($row['arti_is_featured'] ?? 0) === 1): ?>
                                <span class="badge text-bg-danger me-1">精選</span>
                              <?php endif; ?>
                              <?php if (!empty($row['arti_keycode'])): ?>
                                <code class="me-1 text-primary">[<?php echo htmlspecialchars($row['arti_keycode']); ?>]</code>
                              <?php endif; ?>
                              <?php echo htmlspecialchars($row['arti_title'] ?? ''); ?>
                            </div>
                            <?php if (!empty($row['arti_subscript'])): ?>
                              <div class="text-muted" style="font-size: 0.78rem;"><?php echo htmlspecialchars($row['arti_subscript']); ?></div>
                            <?php endif; ?>
                          </td>
                          <td style="font-size: 0.76rem;">
                            <div class="fw-semibold text-dark"><?php echo $cateMap[$row['arti_cate']] ?? htmlspecialchars($row['arti_cate'] ?? '-'); ?></div>
                            <?php if (!empty($row['arti_tpl_vars'])): ?>
                              <div class="text-danger mt-1">變數：<code><?php echo htmlspecialchars($row['arti_tpl_vars']); ?></code></div>
                            <?php endif; ?>
                          </td>
                          <td class="text-center">
                            <?php echo $browseMap[$browse] ?? $browseMap; ?>
                            <?php if ($browse === 3 && (int)$row['arti_cost_pts'] > 0): ?>
                              <div class="text-warning-emphasis fw-bold mt-1" style="font-size: 0.72rem;"><?php echo (int)$row['arti_cost_pts']; ?> PTS</div>
                            <?php endif; ?>
                          </td>
                          <td class="text-center" style="font-size: 0.75rem;">
                            <div><?php echo !empty($row['arti_dateopen']) ? $row['arti_dateopen'] : '立即上架'; ?></div>
                            <div class="text-muted">瀏覽：<?php echo number_format((int)($row['arti_visits'] ?? 0)); ?></div>
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
        <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-funnel-fill me-2 text-warning"></i>專欄與動態模板篩選</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <form id="form_search" method="get" action="art_list.php">
          <div class="mb-3">
            <label class="form-label small text-white-50">關鍵字（標題 / 郵件主旨 / 調用代碼 / 標籤）</label>
            <input type="text" class="form-control form-control-sm" name="searchText" value="<?php echo htmlspecialchars($searchText); ?>" placeholder="輸入搜尋關鍵字...">
          </div>
          <div class="mb-3">
            <label class="form-label small text-white-50">內容定位模式</label>
            <select class="form-select form-select-sm" name="searchMode">
              <option value="">-- 全部模式 --</option>
              <option value="1" <?php echo ($searchMode === '1') ? 'selected' : ''; ?>>1 - 知識專欄文章</option>
              <option value="2" <?php echo ($searchMode === '2') ? 'selected' : ''; ?>>2 - 系統通知與推播模板</option>
              <option value="3" <?php echo ($searchMode === '3') ? 'selected' : ''; ?>>3 - Email / EDM 郵件模板</option>
              <option value="4" <?php echo ($searchMode === '4') ? 'selected' : ''; ?>>4 - 法律條款 / 靜態頁面</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label small text-white-50">上下架狀態</label>
            <select class="form-select form-select-sm" name="searchStatus">
              <option value="">-- 全部狀態 --</option>
              <option value="1" <?php echo ($searchStatus === '1') ? 'selected' : ''; ?>>1 - 上架啟用</option>
              <option value="2" <?php echo ($searchStatus === '2') ? 'selected' : ''; ?>>2 - 草稿待審</option>
              <option value="0" <?php echo ($searchStatus === '0') ? 'selected' : ''; ?>>0 - 下架停用</option>
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
  function doButAdd() { window.location.href = 'art_detail.php?currID=0'; }
  function doButReset() { window.location.replace('art_list.php'); }
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

    var artlistTB = datatableCreate('dataTable_artlist');

    $('#dataSearch').on('keyup search input paste cut', function() {
      if ($(this).is(':focus') || this.value !== '') {
        artlistTB.search(this.value).draw();
      }
    });

    $('#searchReset').click(function() {
      if ($('#dataSearch').val() !== '') {
        $('#dataSearch').val('');
        artlistTB.search('').draw();
      } else {
        window.location.replace('art_list.php');
      }
    });

    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
      window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  });
  </script>
</body>
</html>
