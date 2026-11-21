<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 架構搜尋條件
$searchText   = isset($_REQUEST['searchText']) ? trim(rawurldecode($_REQUEST['searchText'])) : '';
$searchLevel  = isset($_REQUEST['searchLevel']) ? trim($_REQUEST['searchLevel']) : '';
$searchStatus = isset($_REQUEST['searchStatus']) ? trim($_REQUEST['searchStatus']) : '';

$searchURI = "";
if ($searchText !== '')   { $searchURI .= "&searchText=" . rawurlencode($searchText); }
if ($searchLevel !== '')  { $searchURI .= "&searchLevel=" . rawurlencode($searchLevel); }
if ($searchStatus !== '') { $searchURI .= "&searchStatus=" . rawurlencode($searchStatus); }
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}

$maxResult = (!empty($searchURI)) ? 300 : 100;

// 定義本頁面代碼（對應左側樹狀導航列與頂部操作面板）
define('PAGE_MARK','PG_MEM');
define('PAGE_ITEM','ITEM_MEM');
define('PAGE_ITEM2','ITEM_MEMSHIP');
define('PAGE_OPANEL', 1); // 1-新增
define('SEARCH_OPANEL', !empty($searchURI) ? 2 : 1);
define('EXPORT_OPANEL', 3); // 3-Excel+Print

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

// 組合查詢條件
$whereSql = " WHERE mb_status != 9 ";
$params   = array();

if ($searchText !== '') {
  $whereSql .= " AND (mb_email LIKE ? OR mb_name LIKE ? OR mb_nick LIKE ? OR mb_phone LIKE ?) ";
  $likeStr = '%' . $searchText . '%';
  array_push($params, $likeStr, $likeStr, $likeStr, $likeStr);
}
if ($searchLevel !== '') {
  $whereSql .= " AND mb_level = ? ";
  $params[] = (int)$searchLevel;
}
if ($searchStatus !== '') {
  $whereSql .= " AND mb_status = ? ";
  $params[] = (int)$searchStatus;
}

$totalNum = (int)DB->single("SELECT COUNT(mb_id) FROM fsl_members" . $whereSql, $params);
$mbList   = DB->query("SELECT * FROM fsl_members" . $whereSql . " ORDER BY mb_id DESC LIMIT " . $maxResult, $params);
if (!is_array($mbList)) $mbList = array();

$levelMap = array(
  1 => '<span class="badge text-bg-secondary">一般會員</span>',
  2 => '<span class="badge text-bg-info">向光學員</span>',
  3 => '<span class="badge text-bg-warning">VIP 會員</span>'
);
$statusMap = array(
  0 => '<span class="badge text-bg-danger">停權凍結</span>',
  1 => '<span class="badge text-bg-success">正常啟用</span>',
  2 => '<span class="badge text-bg-warning">待信箱驗證</span>'
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
  <title>Fifth Season Life 人生第五季 | 會員資料列表</title>

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
                會員資料管理
                <i class="bi bi-question-circle-fill fs-6 text-secondary" data-bs-toggle="tooltip" title="管理全站註冊會員基本資料、等級權限、PTS 紅利點數與修課訂單紀錄"></i>
                <span class="text-muted small ms-2">mb_list.php</span>
              </h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">會員管理系統</li>
                <li class="breadcrumb-item active">會員資料列表</li>
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
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold"><i class="bi bi-people-fill me-1 text-primary"></i>會員清單</span>
                    <span class="badge text-bg-light border">共 <span id="resultCount"><?php echo $totalNum > $maxResult ? $totalNum . " 筆符合，表列 " . $maxResult : $totalNum; ?></span> 筆</span>
                  </div>
                  <div class="input-group input-group-sm" style="max-width: 260px;">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" id="dataSearch" class="form-control" placeholder="表格內即時快篩...">
                  </div>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table id="dataTable_mblist" class="table table-hover table-bordered align-middle w-100 small">
                      <thead class="table-light">
                        <tr>
                          <th class="text-center" style="width: 60px;">ID</th>
                          <th class="text-center" style="width: 65px;">頭像</th>
                          <th>帳號 (Email)</th>
                          <th>姓名 / 暱稱</th>
                          <th>聯絡手機</th>
                          <th class="text-center">會員等級</th>
                          <th class="text-end">PTS 點數</th>
                          <th class="text-center">信箱驗證</th>
                          <th class="text-center">狀態</th>
                          <th class="text-center">註冊日期</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($mbList as $row):
                          $mid = (int)$row['mb_id'];
                          $thumbPath = "../uploadFiles/mb/mbphoto_" . $mid . "_thumb.jpg";
                          $avatarSrc = file_exists($thumbPath) ? ($thumbPath . "?t=" . filemtime($thumbPath)) : "../imageFiles/nopic_user_64x64.jpg";
                          $lvl = (int)($row['mb_level'] ?? 1);
                          $sta = (int)($row['mb_status'] ?? 1);
                          $isVerified = ((int)($row['mb_email_verified'] ?? 0) === 1);
                        ?>
                        <tr style="cursor: pointer;" onclick="window.location.href='mb_detail.php?currID=<?php echo $mid; ?>';">
                          <td class="text-center fw-bold"><?php echo $mid; ?></td>
                          <td class="text-center">
                            <img src="<?php echo $avatarSrc; ?>" class="rounded-circle border" width="38" height="38" style="object-fit: cover;" alt="Avatar">
                          </td>
                          <td class="fw-semibold text-primary"><?php echo htmlspecialchars(_fn_secure_crypto($row['mb_email'],'de') ?? ''); ?></td>
                          <td>
                            <div class="fw-bold text-dark"><?php echo htmlspecialchars(_fn_secure_crypto($row['mb_name'],'de') ?? '-'); ?></div>
                            <?php if (!empty($row['mb_nick'])): ?>
                              <div class="text-muted" style="font-size: 0.78rem;">/<?php echo htmlspecialchars($row['mb_title']); ?></div>
                            <?php endif; ?>
                          </td>
                          <td><?php echo htmlspecialchars($row['mb_phone'] ?? '-'); ?></td>
                          <td class="text-center"><?php echo $levelMap[$lvl] ?? $levelMap[1]; ?></td>
                          <td class="text-end fw-bold text-warning-emphasis"><?php echo number_format((int)($row['mb_points'] ?? 0)); ?></td>
                          <td class="text-center">
                            <?php if ($isVerified): ?>
                              <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-patch-check-fill me-1"></i>已驗證</span>
                            <?php else: ?>
                              <span class="badge bg-secondary-subtle text-secondary border"><i class="bi bi-clock me-1"></i>未驗證</span>
                            <?php endif; ?>
                          </td>
                          <td class="text-center"><?php echo $statusMap[$sta] ?? '<span class="badge text-bg-secondary">未知</span>'; ?></td>
                          <td class="text-center text-muted"><?php echo !empty($row['mb_created']) ? date('Y-m-d H:i', strtotime($row['mb_created'])) : '-'; ?></td>
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
        <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-funnel-fill me-2 text-warning"></i>會員進階條件搜尋</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <form id="form_search" method="get" action="mb_list.php">
          <div class="mb-3">
            <label class="form-label small text-white-50">關鍵字（信箱 / 姓名 / 暱稱 / 手機）</label>
            <input type="text" class="form-control form-control-sm" name="searchText" value="<?php echo htmlspecialchars($searchText); ?>" placeholder="輸入搜尋關鍵字...">
          </div>
          <div class="mb-3">
            <label class="form-label small text-white-50">會員等級</label>
            <select class="form-select form-select-sm" name="searchLevel">
              <option value="">-- 全部等級 --</option>
              <option value="1" <?php echo ($searchLevel === '1') ? 'selected' : ''; ?>>一般會員</option>
              <option value="2" <?php echo ($searchLevel === '2') ? 'selected' : ''; ?>>向光學員</option>
              <option value="3" <?php echo ($searchLevel === '3') ? 'selected' : ''; ?>>VIP 會員</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label small text-white-50">帳號狀態</label>
            <select class="form-select form-select-sm" name="searchStatus">
              <option value="">-- 全部狀態 --</option>
              <option value="1" <?php echo ($searchStatus === '1') ? 'selected' : ''; ?>>正常啟用</option>
              <option value="2" <?php echo ($searchStatus === '2') ? 'selected' : ''; ?>>待信箱驗證</option>
              <option value="0" <?php echo ($searchStatus === '0') ? 'selected' : ''; ?>>停權凍結</option>
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
    window.location.href = 'mb_detail.php?currID=0';
  }
  function doButReset() {
    window.location.replace('mb_list.php');
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

    var mblistTB = datatableCreate('dataTable_mblist');

    $('#dataSearch').on('keyup search input paste cut', function() {
      if ($(this).is(':focus') || this.value !== '') {
        mblistTB.search(this.value).draw();
      }
    });

    $('#searchReset').click(function() {
      if ($('#dataSearch').val() !== '') {
        $('#dataSearch').val('');
        mblistTB.search('').draw();
      } else {
        window.location.replace('mb_list.php');
      }
    });

    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
      window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  });
  </script>
</body>
</html>
