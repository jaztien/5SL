<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 架構搜尋條件
$searchText   = isset($_REQUEST['searchText']) ? trim(rawurldecode($_REQUEST['searchText'])) : '';
$searchStatus = isset($_REQUEST['searchStatus']) ? trim($_REQUEST['searchStatus']) : '';
$searchInv    = isset($_REQUEST['searchInv']) ? trim($_REQUEST['searchInv']) : '';
$searchRefund = isset($_REQUEST['searchRefund']) ? trim($_REQUEST['searchRefund']) : '';

$searchURI = "";
if ($searchText !== '')   { $searchURI .= "&searchText=" . rawurlencode($searchText); }
if ($searchStatus !== '') { $searchURI .= "&searchStatus=" . rawurlencode($searchStatus); }
if ($searchInv !== '')    { $searchURI .= "&searchInv=" . rawurlencode($searchInv); }
if ($searchRefund !== '') { $searchURI .= "&searchRefund=" . rawurlencode($searchRefund); }
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}

$maxResult = (!empty($searchURI)) ? 300 : 120;

// 定義本頁面代碼（對應左側樹狀導航列與頂部操作面板）
define('PAGE_MARK', 'PG_ORDER');
define('PAGE_ITEM', 'ITEM_MERCH');
define('PAGE_OPANEL', 9); // 9-列表無新增按鈕(訂單由前台產生)
define('DRAWER_OPANEL', !empty($searchURI) ? 2 : 1);
define('EXPORT_OPANEL', 3); // 3-Excel+Print

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

// 組合查詢條件
$whereSql = " WHERE 1=1 ";
$params   = array();

if ($searchText !== '') {
  $nameHash  = _fn_hash_hmac(_fn_secure_crypto($searchText, 'en'));
  $emailHash = _fn_hash_hmac(_fn_secure_crypto(strtolower($searchText), 'en'));
  $likeStr   = '%' . $searchText . '%';
  $whereSql .= " AND (o.ord_no LIKE ? OR i.inv_no LIKE ? OR o.ord_tracking_no LIKE ? OR m.mb_name_hash = ? OR m.mb_email_hash = ?) ";
  array_push($params, $likeStr, $likeStr, $likeStr, $nameHash, $emailHash);
}
if ($searchStatus !== '') {
  $whereSql .= " AND o.ord_status = ? ";
  $params[] = (int)$searchStatus;
}
if ($searchInv !== '') {
  $whereSql .= " AND i.inv_status = ? ";
  $params[] = (int)$searchInv;
}
if ($searchRefund === '1') {
  $whereSql .= " AND r.ref_id IS NOT NULL ";
}

$totalNum = (int)DB->single("
  SELECT COUNT(o.ord_id)
  FROM fsl_orders o
  LEFT JOIN fsl_members m ON o.mb_id = m.mb_id
  LEFT JOIN fsl_invoices i ON o.ord_id = i.ord_id
  LEFT JOIN fsl_refunds r ON o.ord_id = r.ord_id
  {$whereSql}
", $params);

$sqlList = "
  SELECT o.*,
         m.mb_name, m.mb_email,
         i.inv_no, i.inv_type, i.inv_status, i.inv_pay_gateway,
         r.ref_id, r.ref_no, r.ref_type, r.ref_status,
         (SELECT COUNT(*) FROM fsl_order_items oi WHERE oi.ord_id = o.ord_id) AS item_cnt
  FROM fsl_orders o
  LEFT JOIN fsl_members m ON o.mb_id = m.mb_id
  LEFT JOIN fsl_invoices i ON o.ord_id = i.ord_id
  LEFT JOIN fsl_refunds r ON o.ord_id = r.ord_id
  {$whereSql}
  ORDER BY o.ord_id DESC
  LIMIT {$maxResult}
";
$ordList = DB->query($sqlList, $params);
if (!is_array($ordList)) $ordList = array();

$ordStatusMap = array(
  1 => '<span class="badge text-bg-warning">1 - 待付款</span>',
  2 => '<span class="badge text-bg-info">2 - 已付款處理中</span>',
  3 => '<span class="badge text-bg-primary">3 - 已出貨配送中</span>',
  4 => '<span class="badge text-bg-success">4 - 已完成</span>',
  5 => '<span class="badge text-bg-danger">5 - 售後退換申請中</span>',
  6 => '<span class="badge text-bg-secondary">6 - 已退費 / 取消</span>'
);
$invStatusMap = array(
  0 => '<span class="badge bg-light text-secondary border">未開立</span>',
  1 => '<span class="badge bg-success-subtle text-success border border-success">已開立</span>',
  2 => '<span class="badge bg-danger-subtle text-danger border border-danger">已作廢/折讓</span>'
);
$refStatusMap = array(
  1 => '<span class="badge text-bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>退換待審核</span>',
  2 => '<span class="badge text-bg-warning">審核通過退貨中</span>',
  3 => '<span class="badge text-bg-success">已完成退款換貨</span>',
  4 => '<span class="badge text-bg-secondary">已駁回申請</span>'
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
  <title>Fifth Season Life 人生第五季 | 訂單、發票與售後管理</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>
<body class="bg-body-tertiary theme-gray">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1 ms-2"><i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?></div>
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                訂單、發票與售後退換貨管理
                <i class="bi bi-question-circle-fill fs-6 text-secondary" data-bs-toggle="tooltip" title="管理全站消費訂單、物流出貨追蹤、電子發票開立與作廢、以及售後退換貨審核退款"></i>
              </h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">商品銷售管理</li>
                <li class="breadcrumb-item active">訂單與發票退換貨管理</li>
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
                    <span class="fw-bold"><i class="bi bi-receipt-cutoff me-1 text-dark"></i>全站訂單與財務對帳清單</span>
                    <span class="badge text-bg-light border">共 <span id="resultCount"><?php echo $totalNum > $maxResult ? $totalNum . " 筆符合，表列 " . $maxResult : $totalNum; ?></span> 筆</span>
                  </div>
                  <div class="input-group input-group-sm w-auto flex-grow-1 flex-md-grow-0 ms-md-auto">
                    <span class="input-group-text bg-white"><i class="bi bi-funnel"></i></span>
                    <input type="text" id="dataSearch" class="form-control" placeholder="表格內即時快篩...">
                  </div>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table id="dataTable_ordlist" class="table table-striped table-hover table-bordered align-middle w-100 small" data-search="1">
                      <thead class="table-light text-nowrap">
                        <tr>
                          <th>訂單編號 / 下單時間</th>
                          <th>訂購會員</th>
                          <th class="text-center">品項數</th>
                          <th class="text-end">實付金額 / 點數折抵</th>
                          <th>電子發票 / 金流</th>
                          <th class="text-center">訂單與物流狀態</th>
                          <th class="text-center">售後退換貨</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($ordList as $row):
                          $oid      = (int)$row['ord_id'];
                          $decName  = !empty($row['mb_name']) ? _fn_secure_crypto($row['mb_name'], 'de') : '會員 #' . $row['mb_id'];
                          $decEmail = !empty($row['mb_email']) ? _fn_secure_crypto($row['mb_email'], 'de') : '';
                          $oSta     = (int)($row['ord_status'] ?? 1);
                          $iSta     = (int)($row['inv_status'] ?? 0);
                          $rSta     = (int)($row['ref_status'] ?? 0);
                        ?>
                        <tr style="cursor: pointer;" onclick="window.location.href='ord_detail.php?currID=<?php echo $oid; ?>';">
                          <td>
                            <div class="fw-bold text-primary"><?php echo htmlspecialchars($row['ord_no']); ?></div>
                            <div class="text-muted" style="font-size: 0.74rem;"><?php echo substr($row['ord_created'], 0, 16); ?></div>
                          </td>
                          <td>
                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($decName); ?></div>
                            <div class="text-muted" style="font-size: 0.74rem;"><?php echo htmlspecialchars($decEmail); ?></div>
                          </td>
                          <td class="text-center">
                            <span class="badge bg-light text-dark border"><?php echo (int)$row['item_cnt']; ?> 項</span>
                          </td>
                          <td class="text-end">
                            <div class="fw-bold text-dark">NT$ <?php echo number_format((float)$row['ord_total']); ?></div>
                            <?php if ((int)($row['ord_pts_used'] ?? 0) > 0): ?>
                              <div class="text-warning-emphasis" style="font-size: 0.72rem;">折抵 <?php echo number_format((int)$row['ord_pts_used']); ?> PTS</div>
                            <?php endif; ?>
                          </td>
                          <td>
                            <div class="fw-semibold">
                              <?php echo !empty($row['inv_no']) ? htmlspecialchars($row['inv_no']) : '<span class="text-muted">尚未開立</span>'; ?>
                              <?php echo $invStatusMap[$iSta] ?? ''; ?>
                            </div>
                            <div class="text-muted" style="font-size: 0.74rem;"><?php echo htmlspecialchars($row['inv_pay_gateway'] ?? '-'); ?></div>
                          </td>
                          <td class="text-center">
                            <div><?php echo $ordStatusMap[$oSta] ?? $ordStatusMap; ?></div>
                            <?php if (!empty($row['ord_tracking_no'])): ?>
                              <div class="text-muted mt-1" style="font-size: 0.72rem;"><i class="bi bi-truck me-1"></i><?php echo htmlspecialchars($row['ord_tracking_no']); ?></div>
                            <?php endif; ?>
                          </td>
                          <td class="text-center">
                            <?php if (!empty($row['ref_id'])): ?>
                              <?php echo $refStatusMap[$rSta] ?? ''; ?>
                              <div class="text-muted mt-1" style="font-size: 0.7rem;"><?php echo htmlspecialchars($row['ref_no'] ?? ''); ?></div>
                            <?php else: ?>
                              <span class="text-muted">-</span>
                            <?php endif; ?>
                          </td>
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
        <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-funnel-fill me-2 text-warning"></i>訂單與財務進階篩選</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <form id="form_search" method="get" action="ord_list.php">
          <div class="mb-3">
            <label class="form-label small text-white-50">關鍵字（訂單編號 / 發票號碼 / 物流單號 / 會員完整姓名或信箱）</label>
            <input type="text" class="form-control form-control-sm" name="searchText" value="<?php echo htmlspecialchars($searchText); ?>" placeholder="輸入搜尋關鍵字...">
          </div>
          <div class="mb-3">
            <label class="form-label small text-white-50">訂單狀態</label>
            <select class="form-select form-select-sm" name="searchStatus">
              <option value="">-- 全部訂單狀態 --</option>
              <option value="1" <?php echo ($searchStatus === '1') ? 'selected' : ''; ?>>1 - 待付款</option>
              <option value="2" <?php echo ($searchStatus === '2') ? 'selected' : ''; ?>>2 - 已付款處理中</option>
              <option value="3" <?php echo ($searchStatus === '3') ? 'selected' : ''; ?>>3 - 已出貨配送中</option>
              <option value="4" <?php echo ($searchStatus === '4') ? 'selected' : ''; ?>>4 - 已完成</option>
              <option value="5" <?php echo ($searchStatus === '5') ? 'selected' : ''; ?>>5 - 售後退換申請中</option>
              <option value="6" <?php echo ($searchStatus === '6') ? 'selected' : ''; ?>>6 - 已退費 / 取消</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small text-white-50">電子發票狀態</label>
            <select class="form-select form-select-sm" name="searchInv">
              <option value="">-- 全部發票狀態 --</option>
              <option value="0" <?php echo ($searchInv === '0') ? 'selected' : ''; ?>>0 - 尚未開立</option>
              <option value="1" <?php echo ($searchInv === '1') ? 'selected' : ''; ?>>1 - 已開立</option>
              <option value="2" <?php echo ($searchInv === '2') ? 'selected' : ''; ?>>2 - 已作廢 / 折讓</option>
            </select>
          </div>
          <div class="mb-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="searchRefund" value="1" id="chkOnlyRefund" <?php echo ($searchRefund === '1') ? 'checked' : ''; ?>>
              <label class="form-check-label small text-warning fw-bold" for="chkOnlyRefund">僅顯示有售後退換貨申請的訂單</label>
            </div>
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
  function doButReset() { window.location.replace('ord_list.php'); }
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

    var ordlistTB = datatableCreate('dataTable_ordlist');

    $('#dataSearch').on('keyup search input paste cut', function() {
      if ($(this).is(':focus') || this.value !== '') {
        ordlistTB.search(this.value).draw();
      }
    });

    $('#searchReset').click(function() {
      if ($('#dataSearch').val() !== '') {
        $('#dataSearch').val('');
        ordlistTB.search('').draw();
      } else {
        window.location.replace('ord_list.php');
      }
    });

    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
      window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  });
  </script>
</body>
</html>
