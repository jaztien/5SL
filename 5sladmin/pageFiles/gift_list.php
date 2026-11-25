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

$maxResult = (!empty($searchURI)) ? 300 : 120;

// 定義本頁面代碼（對應左側樹狀導航列與頂部操作面板）
define('PAGE_MARK', 'PG_GIFT');
define('PAGE_ITEM', 'ITEM_MERCH');
define('PAGE_OPANEL', 1); // 1-新增
define('DRAWER_OPANEL', !empty($searchURI) ? 2 : 1);
define('EXPORT_OPANEL', 3); // 3-Excel+Print

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

// 組合查詢條件
$whereSql = " WHERE g.gift_status != 9 ";
$params   = array();

if ($searchText !== '') {
  $whereSql .= " AND (g.gift_title LIKE ? OR g.gift_subtitle LIKE ? OR g.gift_code LIKE ?) ";
  $likeStr = '%' . $searchText . '%';
  array_push($params, $likeStr, $likeStr, $likeStr);
}
if ($searchType !== '') {
  $whereSql .= " AND g.gift_type = ? ";
  $params[] = (int)$searchType;
}
if ($searchStatus !== '') {
  $whereSql .= " AND g.gift_status = ? ";
  $params[] = (int)$searchStatus;
}

$totalNum = (int)DB->single("SELECT COUNT(g.gift_id) FROM fsl_gifts g" . $whereSql, $params);
$giftList = DB->query("
  SELECT g.*,
         (SELECT COUNT(*) FROM fsl_gift_records r WHERE r.gift_id = g.gift_id AND r.grec_status = 1) AS claimed_cnt
  FROM fsl_gifts g
  {$whereSql}
  ORDER BY g.gift_id DESC
  LIMIT {$maxResult}
", $params);
if (!is_array($giftList)) $giftList = array();

$typeMap = array(
  1 => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-coin me-1"></i>純紅利點數</span>',
  2 => '<span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle"><i class="bi bi-gift me-1"></i>商品贈品</span>',
  3 => '<span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle"><i class="bi bi-box2-heart me-1"></i>複合大禮包</span>',
  4 => '<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle"><i class="bi bi-ticket-perforated me-1"></i>課程折抵券</span>'
);
$targetMap = array(
  0 => '不限對象 (憑活動領取)',
  1 => '限制最低會員等級',
  2 => '指定特定會員名單'
);
$statusMap = array(
  0 => '<span class="badge text-bg-secondary">停用關閉</span>',
  1 => '<span class="badge text-bg-success">啟用發放中</span>'
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
  <title>Fifth Season Life 人生第五季 | 禮品優惠券管理</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>
<body class="bg-body-tertiary theme-red">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1 ms-2"><i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?></div>
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                禮品優惠券與派發管理
                <i class="bi bi-question-circle-fill fs-6 text-secondary" data-bs-toggle="tooltip" title="管理紅利點數禮包、課程折價券、關聯實體/數位商品贈品、對象規則與會員領收物流追蹤"></i>
              </h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">商品銷售管理</li>
                <li class="breadcrumb-item active">禮品優惠券管理</li>
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
                    <span class="fw-bold"><i class="bi bi-gift-fill me-1 text-danger"></i>禮品與優惠券方案清單</span>
                    <span class="badge text-bg-light border">共 <span id="resultCount"><?php echo $totalNum > $maxResult ? $totalNum . " 筆符合，表列 " . $maxResult : $totalNum; ?></span> 筆</span>
                  </div>
                  <div class="input-group input-group-sm w-auto flex-grow-1 flex-md-grow-0 ms-md-auto">
                    <span class="input-group-text bg-white"><i class="bi bi-funnel"></i></span>
                    <input type="text" id="dataSearch" class="form-control" placeholder="表格內即時快篩...">
                  </div>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table id="dataTable_giftlist" class="table table-striped table-hover table-bordered align-middle w-100 small" data-search="1">
                      <thead class="table-light text-nowrap">
                        <tr>
                          <th class="text-center" style="width: 55px;">ID</th>
                          <th class="text-center" style="width: 64px;">圖示</th>
                          <th>禮品代碼 / 名稱</th>
                          <th class="text-center">禮品類型</th>
                          <th>獎勵內容 (點數 / 折抵 / 關聯商品)</th>
                          <th>領取對象與效期</th>
                          <th class="text-center">已發放 / 名額上限</th>
                          <th class="text-center">狀態</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($giftList as $row):
                          $gid     = (int)$row['gift_id'];
                          $imgFile = "../uploadFiles/gift/gimg_" . $gid . "_thumb.jpg";
                          $imgSrc  = file_exists($imgFile) ? ($imgFile . "?t=" . filemtime($imgFile)) : "../imageFiles/nopic_user_200x200.jpg";
                          $gType   = (int)($row['gift_type'] ?? 1);
                          $tType   = (int)($row['gift_target_type'] ?? 0);
                          $quota   = (int)($row['gift_total_quota'] ?? -1);
                          $issued  = (int)($row['gift_issued_count'] ?? 0);
                          $sta     = (int)($row['gift_status'] ?? 1);
                        ?>
                        <tr style="cursor: pointer;" onclick="window.location.href='gift_detail.php?currID=<?php echo $gid; ?>';">
                          <td class="text-center fw-bold"><?php echo $gid; ?></td>
                          <td class="text-center">
                            <img src="<?php echo $imgSrc; ?>" class="rounded border" width="44" height="44" style="object-fit: cover;" alt="Gift">
                          </td>
                          <td>
                            <div class="fw-bold text-dark">
                              <?php if (!empty($row['gift_code'])): ?>
                                <code class="me-1 text-danger">[<?php echo htmlspecialchars($row['gift_code']); ?>]</code>
                              <?php endif; ?>
                              <?php echo htmlspecialchars($row['gift_title'] ?? ''); ?>
                            </div>
                            <?php if (!empty($row['gift_subtitle'])): ?>
                              <div class="text-muted" style="font-size: 0.78rem;"><?php echo htmlspecialchars($row['gift_subtitle']); ?></div>
                            <?php endif; ?>
                          </td>
                          <td class="text-center"><?php echo $typeMap[$gType] ?? $typeMap[1]; ?></td>
                          <td style="font-size: 0.78rem;">
                            <?php if ((int)$row['gift_pts'] > 0): ?>
                              <span class="badge bg-warning text-dark me-1">+<?php echo number_format((int)$row['gift_pts']); ?> PTS</span>
                            <?php endif; ?>
                            <?php if ((float)$row['gift_discount_usd'] > 0): ?>
                              <span class="badge bg-success me-1">折抵 USD $<?php echo number_format((float)$row['gift_discount_usd'], 2); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($row['gift_merch_ids'])): ?>
                              <span class="badge bg-info text-dark">含商品 ID: <?php echo htmlspecialchars($row['gift_merch_ids']); ?></span>
                            <?php endif; ?>
                          </td>
                          <td style="font-size: 0.78rem;">
                            <div class="fw-semibold text-dark"><?php echo $targetMap[$tType] ?? $targetMap[0]; ?></div>
                            <div class="text-muted">效期：<?php echo ((int)$row['gift_valid_days'] > 0) ? ($row['gift_valid_days'] . ' 天') : '永久有效'; ?></div>
                          </td>
                          <td class="text-center">
                            <span class="fw-bold text-primary"><?php echo number_format($issued); ?></span>
                            / <?php echo ($quota < 0) ? '無上限' : number_format($quota); ?>
                            <div class="text-success" style="font-size: 0.72rem;">已領取：<?php echo (int)$row['claimed_cnt']; ?> 份</div>
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
        <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-funnel-fill me-2 text-warning"></i>禮品與優惠券進階篩選</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <form id="form_search" method="get" action="gift_list.php">
          <div class="mb-3">
            <label class="form-label small text-white-50">關鍵字（禮品名稱 / 副標 / 禮品代碼）</label>
            <input type="text" class="form-control form-control-sm" name="searchText" value="<?php echo htmlspecialchars($searchText); ?>" placeholder="輸入搜尋關鍵字...">
          </div>
          <div class="mb-3">
            <label class="form-label small text-white-50">禮品類型</label>
            <select class="form-select form-select-sm" name="searchType">
              <option value="">-- 全部類型 --</option>
              <option value="1" <?php echo ($searchType === '1') ? 'selected' : ''; ?>>1 - 純紅利點數</option>
              <option value="2" <?php echo ($searchType === '2') ? 'selected' : ''; ?>>2 - 商品贈品 (實體或數位)</option>
              <option value="3" <?php echo ($searchType === '3') ? 'selected' : ''; ?>>3 - 複合禮包 (點數+商品)</option>
              <option value="4" <?php echo ($searchType === '4') ? 'selected' : ''; ?>>4 - 課程折抵券</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label small text-white-50">啟用狀態</label>
            <select class="form-select form-select-sm" name="searchStatus">
              <option value="">-- 全部狀態 --</option>
              <option value="1" <?php echo ($searchStatus === '1') ? 'selected' : ''; ?>>1 - 啟用發放中</option>
              <option value="0" <?php echo ($searchStatus === '0') ? 'selected' : ''; ?>>0 - 停用關閉</option>
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
  function doButAdd() { window.location.href = 'gift_detail.php?currID=0'; }
  function doButReset() { window.location.replace('gift_list.php'); }
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

    var giftlistTB = datatableCreate('dataTable_giftlist');

    $('#dataSearch').on('keyup search input paste cut', function() {
      if ($(this).is(':focus') || this.value !== '') {
        giftlistTB.search(this.value).draw();
      }
    });

    $('#searchReset').click(function() {
      if ($('#dataSearch').val() !== '') {
        $('#dataSearch').val('');
        giftlistTB.search('').draw();
      } else {
        window.location.replace('gift_list.php');
      }
    });

    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
      window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  });
  </script>
</body>
</html>
