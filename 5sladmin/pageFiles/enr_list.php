<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 架構搜尋條件
$searchText     = isset($_REQUEST['searchText']) ? trim(rawurldecode($_REQUEST['searchText'])) : '';
$searchCourseId = isset($_REQUEST['searchCourseId']) ? trim($_REQUEST['searchCourseId']) : '';
$searchPay      = isset($_REQUEST['searchPay']) ? trim($_REQUEST['searchPay']) : '';
$searchStatus   = isset($_REQUEST['searchStatus']) ? trim($_REQUEST['searchStatus']) : '';

$searchURI = "";
if ($searchText !== '')     { $searchURI .= "&searchText=" . rawurlencode($searchText); }
if ($searchCourseId !== '') { $searchURI .= "&searchCourseId=" . rawurlencode($searchCourseId); }
if ($searchPay !== '')      { $searchURI .= "&searchPay=" . rawurlencode($searchPay); }
if ($searchStatus !== '')   { $searchURI .= "&searchStatus=" . rawurlencode($searchStatus); }
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}

$maxResult = (!empty($searchURI)) ? 300 : 120;

// 定義本頁面代碼（對應左側樹狀導航列與頂部操作面板）
define('PAGE_MARK', 'PG_ENROLL');
define('PAGE_ITEM', 'ITEM_COURSE');
define('PAGE_OPANEL', 1); // 1-新增(手動補登報名)
define('DRAWER_OPANEL', !empty($searchURI) ? 2 : 1);
define('EXPORT_OPANEL', 3); // 3-Excel+Print

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

// 取得所有主課程供篩選
$masterCourses = DB->query("SELECT course_id, course_title FROM fsl_courses WHERE parent_id = 0 AND course_status != 9 ORDER BY course_sort ASC, course_id ASC");
if (!is_array($masterCourses)) $masterCourses = array();

// 組合查詢條件
$whereSql = " WHERE 1=1 ";
$params   = array();

if ($searchText !== '') {
  // 支援搜尋訂單編號、主題備註、以及透過 HMAC 雜湊精準比對加密姓名/信箱/手機
  $nameHash  = _fn_hash_hmac(_fn_secure_crypto($searchText, 'en'));
  $emailHash = _fn_hash_hmac(_fn_secure_crypto(strtolower($searchText), 'en'));
  $phoneHash = _fn_hash_hmac(_fn_secure_crypto($searchText, 'en'));
  $likeStr   = '%' . $searchText . '%';

  $whereSql .= " AND (o.ord_no LIKE ? OR e.enr_topic LIKE ? OR e.enr_note LIKE ? OR m.mb_name_hash = ? OR m.mb_email_hash = ? OR m.mb_cellphone_hash = ?) ";
  array_push($params, $likeStr, $likeStr, $likeStr, $nameHash, $emailHash, $phoneHash);
}
if ($searchCourseId !== '') {
  $whereSql .= " AND e.course_id = ? ";
  $params[] = (int)$searchCourseId;
}
if ($searchPay !== '') {
  $whereSql .= " AND e.enr_pay_status = ? ";
  $params[] = (int)$searchPay;
}
if ($searchStatus !== '') {
  $whereSql .= " AND e.enr_status = ? ";
  $params[] = (int)$searchStatus;
}

$totalNum = (int)DB->single("
  SELECT COUNT(e.enr_id)
  FROM fsl_course_enrolls e
  LEFT JOIN fsl_members m ON e.mb_id = m.mb_id
  LEFT JOIN fsl_orders o ON e.ord_id = o.ord_id
  {$whereSql}
", $params);

$sqlList = "
  SELECT e.*,
         m.mb_name, m.mb_email, m.mb_cellphone,
         c.course_title, c.course_type, c.course_lesson,
         s.sess_batch, s.sess_startdate, s.sess_starttime, s.sess_lesson,
         (SELECT COUNT(*) FROM fsl_course_lessons l WHERE l.sess_id = e.sess_id AND l.less_status != 9) AS actual_lessons,
         o.ord_no, o.ord_total,
         i.inv_no, i.inv_status
  FROM fsl_course_enrolls e
  LEFT JOIN fsl_members m ON e.mb_id = m.mb_id
  LEFT JOIN fsl_courses c ON e.course_id = c.course_id
  LEFT JOIN fsl_course_sessions s ON e.sess_id = s.sess_id
  LEFT JOIN fsl_orders o ON e.ord_id = o.ord_id
  LEFT JOIN fsl_invoices i ON o.ord_id = i.ord_id
  {$whereSql}
  ORDER BY e.enr_id DESC
  LIMIT {$maxResult}
";
$enrList = DB->query($sqlList, $params);
if (!is_array($enrList)) $enrList = array();

$payMap = array(
  0 => '<span class="badge text-bg-warning">待繳費占名額</span>',
  1 => '<span class="badge text-bg-success">已付款完成</span>',
  2 => '<span class="badge bg-info-subtle text-info-emphasis border border-info">免費/票券兌換</span>',
  9 => '<span class="badge text-bg-secondary">逾期未繳釋出</span>'
);
$statusMap = array(
  0 => '<span class="badge text-bg-secondary">已取消報名</span>',
  1 => '<span class="badge text-bg-primary">待開課(已預約)</span>',
  2 => '<span class="badge text-bg-info">修課進行中</span>',
  3 => '<span class="badge text-bg-success"><i class="bi bi-award-fill me-1"></i>已完課結業</span>',
  4 => '<span class="badge text-bg-danger"><i class="bi bi-arrow-left-right me-1"></i>申請改期/延梯</span>'
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
  <title>Fifth Season Life 人生第五季 | 講座與課程報名紀錄</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>
<body class="bg-body-tertiary theme-purple">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1 ms-2"><i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?></div>
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                講座與課程報名紀錄
                <i class="bi bi-question-circle-fill fs-6 text-secondary" data-bs-toggle="tooltip" title="以預約報名學員為核心，統籌追蹤支付發票、課前通訊、修課進度、完課結業與課後關懷服務"></i>
              </h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">課程管理系統</li>
                <li class="breadcrumb-item active">講座與課程報名紀錄</li>
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
                    <span class="fw-bold"><i class="bi bi-person-lines-fill me-1 text-primary"></i>全站學員報名與預約總覽</span>
                    <span class="badge text-bg-light border">共 <span id="resultCount"><?php echo $totalNum > $maxResult ? $totalNum . " 筆符合，表列 " . $maxResult : $totalNum; ?></span> 筆</span>
                  </div>
                  <div class="input-group input-group-sm w-auto flex-grow-1 flex-md-grow-0 ms-md-auto">
                    <span class="input-group-text bg-white"><i class="bi bi-funnel"></i></span>
                    <input type="text" id="dataSearch" class="form-control" placeholder="表格內即時快篩...">
                  </div>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table id="dataTable_enrlist" class="table table-striped table-hover table-bordered align-middle w-100 small" data-search="1">
                      <thead class="table-light text-nowrap">
                        <tr>
                          <th class="text-center" style="width: 65px;">報名ID</th>
                          <th>學員姓名 / 聯絡方式</th>
                          <th>報名課程與梯次</th>
                          <th>訂單 / 發票狀態</th>
                          <th class="text-center">支付狀態</th>
                          <th class="text-center">修課進度</th>
                          <th class="text-center">課堂 / 服務追蹤</th>
                          <th class="text-center">報名時間</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($enrList as $row):
                          $eid      = (int)$row['enr_id'];
                          $decName  = !empty($row['mb_name']) ? _fn_secure_crypto($row['mb_name'], 'de') : '未填寫';
                          $decMail  = !empty($row['mb_email']) ? _fn_secure_crypto($row['mb_email'], 'de') : '';
                          $decPhone = !empty($row['mb_cellphone']) ? _fn_secure_crypto($row['mb_cellphone'], 'de') : '-';
                          $paySta   = (int)($row['enr_pay_status'] ?? 0);
                          $enrSta   = (int)($row['enr_status'] ?? 1);
                          $doneLsn  = (int)($row['enr_done_lessons'] ?? 0);
                          $actLsn   = (int)($row['actual_lessons'] ?? 0);
                          $totLsn   = ($actLsn > 0) ? $actLsn : max(1, (int)($row['sess_lesson'] ?? $row['course_lesson'] ?? 1));
                          $pct      = min(100, round(($doneLsn / $totLsn) * 100));
                        ?>
                        <tr style="cursor: pointer;" onclick="window.location.href='enr_detail.php?currID=<?php echo $eid; ?>';">
                          <td class="text-center fw-bold">#<?php echo $eid; ?></td>
                          <td>
                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($decName); ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($decMail); ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-phone me-1"></i><?php echo htmlspecialchars($decPhone); ?></div>
                          </td>
                          <td>
                            <div class="fw-bold text-primary">
                              <?php if ((int)($row['course_type'] ?? 1) === 2): ?>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle me-1">1對1顧問</span>
                              <?php endif; ?>
                              <?php echo htmlspecialchars($row['course_title'] ?? '課程已下架'); ?>
                            </div>
                            <div class="text-muted" style="font-size: 0.78rem;">
                              <?php if (!empty($row['sess_startdate'])): ?>
                                <span class="badge text-bg-dark me-1">第 <?php echo (int)$row['sess_batch']; ?> 梯</span>
                                <?php echo $row['sess_startdate'] . ' ' . substr($row['sess_starttime'], 0, 5); ?>
                              <?php else: ?>
                                隨報隨上 / 尚未排定梯次
                              <?php endif; ?>
                            </div>
                          </td>
                          <td>
                            <?php if (!empty($row['ord_no'])): ?>
                              <div class="fw-semibold text-dark"><?php echo htmlspecialchars($row['ord_no']); ?></div>
                              <div class="text-muted" style="font-size: 0.75rem;">
                                NT$ <?php echo number_format((float)$row['ord_total']); ?>
                                • <?php echo !empty($row['inv_no']) ? ('發票 ' . htmlspecialchars($row['inv_no'])) : '發票未開'; ?>
                              </div>
                            <?php else: ?>
                              <span class="badge bg-light text-secondary border">後台手動補登 / VIP額度</span>
                            <?php endif; ?>
                          </td>
                          <td class="text-center"><?php echo $payMap[$paySta] ?? $payMap[0]; ?></td>
                          <td class="text-center">
                            <div class="mb-1"><?php echo $statusMap[$enrSta] ?? $statusMap[1]; ?></div>
                            <div class="progress" style="height: 6px;" title="完成度 <?php echo $pct; ?>% (<?php echo $doneLsn; ?>/<?php echo $totLsn; ?>堂)">
                              <div class="progress-bar bg-success" style="width: <?php echo $pct; ?>%;"></div>
                            </div>
                            <div class="text-muted mt-1" style="font-size: 0.7rem;"><?php echo $doneLsn; ?> / <?php echo $totLsn; ?> 堂 (<?php echo $pct; ?>%)</div>
                          </td>
                          <td class="text-center" style="font-size: 0.75rem;">
                            <?php if (!empty($row['enr_link_sent_at'])): ?>
                              <span class="text-success d-block"><i class="bi bi-send-check me-1"></i>已發上課連結</span>
                            <?php else: ?>
                              <span class="text-warning-emphasis d-block"><i class="bi bi-exclamation-circle me-1"></i>尚未發連結</span>
                            <?php endif; ?>
                            <?php if (!empty($row['enr_note'])): ?>
                              <span class="badge bg-info-subtle text-info-emphasis border mt-1"><i class="bi bi-chat-left-text me-1"></i>有追蹤紀錄</span>
                            <?php endif; ?>
                          </td>
                          <td class="text-center text-muted" style="font-size: 0.78rem;">
                            <?php echo !empty($row['enr_created']) ? date('Y-m-d H:i', strtotime($row['enr_created'])) : '-'; ?>
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
        <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-funnel-fill me-2 text-warning"></i>報名與學員進階篩選</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <form id="form_search" method="get" action="enr_list.php">
          <div class="mb-3">
            <label class="form-label small text-white-50">精準搜尋（完整姓名 / 信箱 / 手機 / 訂單編號 / 備註）</label>
            <input type="text" class="form-control form-control-sm" name="searchText" value="<?php echo htmlspecialchars($searchText); ?>" placeholder="輸入搜尋關鍵字...">
          </div>
          <div class="mb-3">
            <label class="form-label small text-white-50">所屬課程</label>
            <select class="form-select form-select-sm" name="searchCourseId">
              <option value="">-- 全部課程 --</option>
              <?php foreach ($masterCourses as $mc): ?>
                <option value="<?php echo (int)$mc['course_id']; ?>" <?php echo ($searchCourseId === (string)$mc['course_id']) ? 'selected' : ''; ?>>
                  #<?php echo (int)$mc['course_id']; ?> - <?php echo htmlspecialchars($mc['course_title']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small text-white-50">付款狀態</label>
            <select class="form-select form-select-sm" name="searchPay">
              <option value="">-- 全部付款狀態 --</option>
              <option value="0" <?php echo ($searchPay === '0') ? 'selected' : ''; ?>>0 - 待繳費占名額</option>
              <option value="1" <?php echo ($searchPay === '1') ? 'selected' : ''; ?>>1 - 已付款完成</option>
              <option value="2" <?php echo ($searchPay === '2') ? 'selected' : ''; ?>>2 - 免費或票券兌換</option>
              <option value="9" <?php echo ($searchPay === '9') ? 'selected' : ''; ?>>9 - 逾期未繳已釋出</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label small text-white-50">修課與服務狀態</label>
            <select class="form-select form-select-sm" name="searchStatus">
              <option value="">-- 全部修課狀態 --</option>
              <option value="1" <?php echo ($searchStatus === '1') ? 'selected' : ''; ?>>1 - 待開課 (已預約)</option>
              <option value="2" <?php echo ($searchStatus === '2') ? 'selected' : ''; ?>>2 - 修課進行中</option>
              <option value="3" <?php echo ($searchStatus === '3') ? 'selected' : ''; ?>>3 - 已完課結業</option>
              <option value="4" <?php echo ($searchStatus === '4') ? 'selected' : ''; ?>>4 - 申請改期或延梯中</option>
              <option value="0" <?php echo ($searchStatus === '0') ? 'selected' : ''; ?>>0 - 已取消報名</option>
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
  function doButAdd() { window.location.href = 'enr_detail.php?currID=0'; }
  function doButReset() { window.location.replace('enr_list.php'); }
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

    var enrlistTB = datatableCreate('dataTable_enrlist');

    $('#dataSearch').on('keyup search input paste cut', function() {
      if ($(this).is(':focus') || this.value !== '') {
        enrlistTB.search(this.value).draw();
      }
    });

    $('#searchReset').click(function() {
      if ($('#dataSearch').val() !== '') {
        $('#dataSearch').val('');
        enrlistTB.search('').draw();
      } else {
        window.location.replace('enr_list.php');
      }
    });

    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
      window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  });
  </script>
</body>
</html>
