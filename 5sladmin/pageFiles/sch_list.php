<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 架構搜尋條件
$searchText   = isset($_REQUEST['searchText']) ? trim(rawurldecode($_REQUEST['searchText'])) : '';
$searchCate   = isset($_REQUEST['searchCate']) ? trim($_REQUEST['searchCate']) : '';
$searchStatus = isset($_REQUEST['searchStatus']) ? trim($_REQUEST['searchStatus']) : '';

$searchURI = "";
if ($searchText !== '') {
  $searchURI .= "&searchText=" . rawurlencode($searchText);
}
if ($searchCate !== '') {
  $searchURI .= "&searchCate=" . rawurlencode($searchCate);
}
if ($searchStatus !== '') {
  $searchURI .= "&searchStatus=" . rawurlencode($searchStatus);
}
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}

$maxResult = (!empty($searchURI)) ? 300 : 150;

// 定義本頁面代碼（排程列表目錄）
define('PAGE_MARK', 'PG_SCH_LIST');
define('PAGE_ITEM', 'ITEM_SCH');
define('PAGE_OPANEL', 1); // 1-新增
define('DRAWER_OPANEL', !empty($searchURI) ? 2 : 1);
define('EXPORT_OPANEL', 3);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

// 組合查詢條件
$whereSql = " WHERE s.sch_exec_status != 9 ";
$params   = array();

if ($searchText !== '') {
  $whereSql .= " AND (s.sch_title LIKE ? OR s.sch_msg_title LIKE ? OR s.arti_keycode LIKE ?) ";
  $likeStr = '%' . $searchText . '%';
  array_push($params, $likeStr, $likeStr, $likeStr);
}
if ($searchCate !== '') {
  $whereSql .= " AND s.sch_category = ? ";
  $params[] = $searchCate;
}
if ($searchStatus !== '') {
  $whereSql .= " AND s.sch_exec_status = ? ";
  $params[] = (int)$searchStatus;
}

$totalNum = (int)DB->single("SELECT COUNT(s.sch_id) FROM fsl_schedules s" . $whereSql, $params);
$schList  = DB->query("
  SELECT s.*, c.course_title
  FROM fsl_schedules s
  LEFT JOIN fsl_courses c ON s.course_id = c.course_id
  {$whereSql}
  ORDER BY s.sch_start_at DESC, s.sch_id DESC
  LIMIT {$maxResult}
", $params);
if (!is_array($schList)) $schList = array();

$cateMap = array(
  'course'    => '<span class="badge bg-primary-subtle text-primary-emphasis border">課程梯次</span>',
  'remind'    => '<span class="badge bg-warning-subtle text-warning-emphasis border">課前提醒</span>',
  'marketing' => '<span class="badge bg-danger-subtle text-danger-emphasis border">行銷 EDM</span>',
  'admin'     => '<span class="badge bg-secondary-subtle text-secondary-emphasis border">內部記事</span>',
  'consult'   => '<span class="badge bg-info-subtle text-info-emphasis border">一對一諮詢</span>'
);
$targetMap = array(
  1 => '會員個別發送',
  2 => '依課程群組發送',
  3 => '自選多位對象',
  4 => '管理者/講師發送'
);
$execMap = array(
  0 => '<span class="badge text-bg-warning">0 - 待定時觸發</span>',
  1 => '<span class="badge text-bg-success">1 - 已執行完成</span>',
  2 => '<span class="badge text-bg-danger">2 - 部分發送失敗</span>'
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
  <title>Fifth Season Life 人生第五季 | 排程與自動化通知列表</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>

<body class="bg-body-tertiary theme-teal">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1 ms-2"><i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?></div>
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                排程與自動化通知管理（列表）
                <i class="bi bi-question-circle-fill fs-6 text-secondary" data-bs-toggle="tooltip" title="管理全站課程梯次排程、自動提取時間事件與 Cron Job 定時多通路通知發送"></i>
              </h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">排程與通知管理</li>
                <li class="breadcrumb-item active">排程事件列表</li>
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
                    <span class="fw-bold"><i class="bi bi-alarm-fill me-1 text-primary"></i>全站排程與通知任務清單</span>
                    <span class="badge text-bg-light border">共 <span id="resultCount"><?php echo $totalNum > $maxResult ? $totalNum . " 筆符合，表列 " . $maxResult : $totalNum; ?></span> 筆</span>
                  </div>

                  <div class="d-flex flex-wrap align-items-center gap-2 ms-md-auto">
                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold" id="btnSyncCourseSessions">
                      <i class="bi bi-arrow-repeat me-1"></i>自動提取課程梯次時間
                    </button>
                    <a href="sch_cal_list.php" class="btn btn-outline-dark btn-sm">
                      <i class="bi bi-calendar3 me-1"></i>切換日曆視圖
                    </a>
                    <div class="input-group input-group-sm w-auto">
                      <span class="input-group-text bg-white"><i class="bi bi-funnel"></i></span>
                      <input type="text" id="dataSearch" class="form-control" placeholder="表格內即時快篩...">
                    </div>
                  </div>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table id="dataTable_schlist" class="table table-striped table-hover table-bordered align-middle w-100 small" data-search="1">
                      <thead class="table-light text-nowrap">
                        <tr>
                          <th class="text-center" style="width: 55px;">ID</th>
                          <th>事件分類 / 排程標題</th>
                          <th>事件起訖時間</th>
                          <th>通知觸發時間 / 對象類型</th>
                          <th class="text-center">發送通路</th>
                          <th class="text-center">執行狀態 / 結果</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($schList as $row):
                          $sid   = (int)$row['sch_id'];
                          $cate  = $row['sch_category'] ?? 'course';
                          $tType = (int)($row['sch_target_type'] ?? 1);
                          $eSta  = (int)($row['sch_exec_status'] ?? 0);
                          $trig  = (int)($row['sch_trigger_enable'] ?? 0);
                          $chs   = explode(',', $row['sch_channels'] ?? '');
                        ?>
                          <tr style="cursor: pointer;" onclick="window.location.href='sch_detail.php?currID=<?php echo $sid; ?>';">
                            <td class="text-center fw-bold"><?php echo $sid; ?></td>
                            <td>
                              <div class="fw-bold text-dark">
                                <span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:<?php echo htmlspecialchars($row['sch_color'] ?? '#3b82f6'); ?>;"></span>
                                <?php echo $cateMap[$cate] ?? ''; ?>
                                <?php if ((int)$row['sch_source'] === 2): ?>
                                  <span class="badge bg-light text-muted border ms-1" title="由課程梯次自動提取">自動同步</span>
                                <?php endif; ?>
                                <span class="ms-1"><?php echo htmlspecialchars($row['sch_title']); ?></span>
                              </div>
                              <?php if (!empty($row['course_title'])): ?>
                                <div class="text-muted" style="font-size: 0.75rem;">關聯課程：<?php echo htmlspecialchars($row['course_title']); ?></div>
                              <?php endif; ?>
                            </td>
                            <td style="font-size: 0.76rem;">
                              <div>起：<?php echo substr($row['sch_start_at'], 0, 16); ?></div>
                              <div class="text-muted">訖：<?php echo !empty($row['sch_end_at']) ? substr($row['sch_end_at'], 0, 16) : '-'; ?></div>
                            </td>
                            <td style="font-size: 0.76rem;">
                              <?php if ($trig === 1): ?>
                                <div class="fw-bold text-danger"><i class="bi bi-clock-history me-1"></i><?php echo substr($row['sch_trigger_at'] ?? '', 0, 16); ?></div>
                                <div class="text-dark"><?php echo $targetMap[$tType] ?? ''; ?></div>
                              <?php else: ?>
                                <span class="text-muted">純日曆記事（不發通知）</span>
                              <?php endif; ?>
                            </td>
                            <td class="text-center">
                              <?php if ($trig === 1): ?>
                                <?php if (in_array('noti', $chs))  echo '<span class="badge bg-primary me-1">站內</span>'; ?>
                                <?php if (in_array('email', $chs)) echo '<span class="badge bg-success me-1">郵件</span>'; ?>
                                <?php if (in_array('sms', $chs))   echo '<span class="badge bg-warning text-dark me-1">簡訊</span>'; ?>
                                <?php if (in_array('line', $chs))  echo '<span class="badge bg-info text-dark">LINE</span>'; ?>
                              <?php else: ?>
                                <span class="text-muted">-</span>
                              <?php endif; ?>
                            </td>
                            <td class="text-center">
                              <?php if ($trig === 1): ?>
                                <div><?php echo $execMap[$eSta] ?? ''; ?></div>
                                <?php if (!empty($row['sch_exec_result'])): ?>
                                  <div class="text-muted mt-1" style="font-size: 0.72rem;"><?php echo htmlspecialchars($row['sch_exec_result']); ?></div>
                                <?php endif; ?>
                              <?php else: ?>
                                <span class="badge bg-light text-secondary border">日曆事件</span>
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
        <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-funnel-fill me-2 text-warning"></i>排程任務進階篩選</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <form id="form_search" method="get" action="sch_list.php">
          <div class="mb-3">
            <label class="form-label small text-white-50">關鍵字（排程標題 / 通知主旨 / 模板代碼）</label>
            <input type="text" class="form-control form-control-sm" name="searchText" value="<?php echo htmlspecialchars($searchText); ?>" placeholder="輸入搜尋關鍵字...">
          </div>
          <div class="mb-3">
            <label class="form-label small text-white-50">事件分類</label>
            <select class="form-select form-select-sm" name="searchCate">
              <option value="">-- 全部事件分類 --</option>
              <option value="course" <?php echo ($searchCate === 'course') ? 'selected' : ''; ?>>課程梯次</option>
              <option value="remind" <?php echo ($searchCate === 'remind') ? 'selected' : ''; ?>>課前提醒</option>
              <option value="marketing" <?php echo ($searchCate === 'marketing') ? 'selected' : ''; ?>>行銷 EDM</option>
              <option value="admin" <?php echo ($searchCate === 'admin') ? 'selected' : ''; ?>>內部記事</option>
              <option value="consult" <?php echo ($searchCate === 'consult') ? 'selected' : ''; ?>>一對一顧問諮詢</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label small text-white-50">定時執行狀態</label>
            <select class="form-select form-select-sm" name="searchStatus">
              <option value="">-- 全部執行狀態 --</option>
              <option value="0" <?php echo ($searchStatus === '0') ? 'selected' : ''; ?>>0 - 待定時觸發</option>
              <option value="1" <?php echo ($searchStatus === '1') ? 'selected' : ''; ?>>1 - 已執行完成</option>
              <option value="2" <?php echo ($searchStatus === '2') ? 'selected' : ''; ?>>2 - 部分發送失敗</option>
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
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t=' . time(); ?>"></script>

  <script type="text/javascript">
    ////////////////////////////////////////////////////////////////
    // ---------------- JAVASCRIPTS 全域調用參數設置 --------------- //
    ////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////
    // -------------- JAVASCRIPTS FUNCTIONs 全域調用 -------------- //
    // -------------- 可接收include檔案要求後自定義動作 -------------- //
    ////////////////////////////////////////////////////////////////

    function doReDirect() {
      window.history.back();
    }

    function doButAdd() {
      window.location.href = 'sch_detail.php?currID=0';
    }

    function doButReset() {
      window.location.replace('sch_list.php');
    }

    function doButSerach() {
      const offcanvasEl = document.getElementById('offcanvasSearch');
      if (offcanvasEl) bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).toggle();
    }

    function doButExcel() {
      $('.buttons-excel').click();
    }

    function doButPrint() {
      $('.buttons-print').click();
    }

    ////////////////////////////////////////////////////////////////
    // ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
    // ---------------------------------------------------------- //
    ////////////////////////////////////////////////////////////////

    $(document).ready(function() {

      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));

      $('body').on('keypress click', function() {
        let timerElm = window.parent.$('#timer_on_exhausted');
        if (timerElm.length) timerElm.backward_timer('reset');
      });

      var schlistTB = datatableCreate('dataTable_schlist');

      $('#dataSearch').on('keyup search input paste cut', function() {
        if ($(this).is(':focus') || this.value !== '') {
          schlistTB.search(this.value).draw();
        }
      });

      $('#searchReset').click(function() {
        if ($('#dataSearch').val() !== '') {
          $('#dataSearch').val('');
          schlistTB.search('').draw();
        } else {
          window.location.replace('sch_list.php');
        }
      });

      // 一鍵從 fsl_course_sessions 自動提取所有未來開課梯次時間並建立課前24小時提醒排程
      $('#btnSyncCourseSessions').on('click', function() {
        const $btn = $(this).prop('disabled', true);
        $.post('../funcFiles/sch_act_func.php', {
          'formAcce': 'func_sch_syncCourses',
          'token': getTokenVal()
        }, function(data) {
          $btn.prop('disabled', false);
          window.parent.toastr[data['msgType']](data['msgStr']);
          if (data['RTN'] == 1) location.reload(true);
        }, 'json');
      });

      //-------- 加載完成後更改外框主頁中樹狀目錄開合狀態 --------//
      if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
      }
    });
  </script>
</body>

</html>