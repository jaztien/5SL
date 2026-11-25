<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 架構搜尋條件
$searchText     = isset($_REQUEST['searchText']) ? trim(rawurldecode($_REQUEST['searchText'])) : '';
$searchCourseId = isset($_REQUEST['searchCourseId']) ? trim($_REQUEST['searchCourseId']) : '';
$searchLive     = isset($_REQUEST['searchLive']) ? trim($_REQUEST['searchLive']) : '';

$searchURI = "";
if ($searchText !== '')     { $searchURI .= "&searchText=" . rawurlencode($searchText); }
if ($searchCourseId !== '') { $searchURI .= "&searchCourseId=" . rawurlencode($searchCourseId); }
if ($searchLive !== '')     { $searchURI .= "&searchLive=" . rawurlencode($searchLive); }
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}

$maxResult = (!empty($searchURI)) ? 300 : 100;

// 定義本頁面代碼
define('PAGE_MARK', 'PG_SESS');
define('PAGE_ITEM', 'ITEM_COURSE');
define('PAGE_OPANEL', 1); // 1-新增
define('DRAWER_OPANEL', !empty($searchURI) ? 2 : 1);
define('EXPORT_OPANEL', 3); // 3-Excel+Print

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$masterCourses = DB->query("SELECT course_id, course_title FROM fsl_courses WHERE parent_id = 0 AND course_status != 9 ORDER BY course_sort ASC, course_id ASC");
if (!is_array($masterCourses)) $masterCourses = array();

// 組合查詢條件
$whereSql = " WHERE s.sess_status != 9 ";
$params   = array();

if ($searchText !== '') {
  $whereSql .= " AND (c.course_title LIKE ? OR s.sess_altname LIKE ? OR s.sess_keycode LIKE ? OR s.sess_meetingid LIKE ?) ";
  $likeStr = '%' . $searchText . '%';
  array_push($params, $likeStr, $likeStr, $likeStr, $likeStr);
}
if ($searchCourseId !== '') {
  $whereSql .= " AND s.course_id = ? ";
  $params[] = (int)$searchCourseId;
}
if ($searchLive !== '') {
  $whereSql .= " AND s.sess_live_status = ? ";
  $params[] = (int)$searchLive;
}

$totalNum = (int)DB->single("SELECT COUNT(s.sess_id) FROM fsl_course_sessions s LEFT JOIN fsl_courses c ON s.course_id = c.course_id" . $whereSql, $params);
$sqlList  = "
  SELECT s.*,
         c.course_title, c.course_max,
         (SELECT COUNT(*) FROM fsl_course_enrolls e WHERE e.sess_id = s.sess_id AND e.enr_status IN (1, 2, 3)) AS active_enrolls,
         (SELECT COUNT(*) FROM fsl_course_enrolls e WHERE e.sess_id = s.sess_id AND e.enr_status IN (1, 2, 3) AND e.enr_attend_status IN (1, 2)) AS checked_in_cnt
  FROM fsl_course_sessions s
  LEFT JOIN fsl_courses c ON s.course_id = c.course_id
  {$whereSql}
  ORDER BY s.sess_startdate DESC, s.sess_starttime DESC, s.sess_id DESC
  LIMIT {$maxResult}
";
$sessList = DB->query($sqlList, $params);
if (!is_array($sessList)) $sessList = array();

$liveMap = array(
  0 => '<span class="badge text-bg-secondary"><i class="bi bi-hourglass-split me-1"></i>尚未開課</span>',
  1 => '<span class="badge text-bg-danger"><i class="bi bi-broadcast me-1"></i>Zoom 課間進行中</span>',
  2 => '<span class="badge text-bg-success"><i class="bi bi-check2-all me-1"></i>已結課歸檔</span>'
);
$weekNames = array('0' => '日', '1' => '一', '2' => '二', '3' => '三', '4' => '四', '5' => '五', '6' => '六');
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
  <title>Fifth Season Life 人生第五季 | 課程梯次與課堂管理</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>
<body class="bg-body-tertiary theme-cyan">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1 ms-2"><i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?></div>
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                課程梯次與課堂管理
                <i class="bi bi-question-circle-fill fs-6 text-secondary" data-bs-toggle="tooltip" title="管理開課梯次、Zoom 會議間建立與安全設定、學員授權連結派發、課間點名簽退與課後錄影"></i>
              </h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">課程管理系統</li>
                <li class="breadcrumb-item active">課程梯次與課堂列表</li>
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
                    <span class="fw-bold"><i class="bi bi-camera-video-fill me-1 text-info"></i>開課梯次與 Zoom 課間清單</span>
                    <span class="badge text-bg-light border">共 <span id="resultCount"><?php echo $totalNum > $maxResult ? $totalNum . " 筆符合，表列 " . $maxResult : $totalNum; ?></span> 梯次</span>
                  </div>
                  <div class="input-group input-group-sm w-auto flex-grow-1 flex-md-grow-0 ms-md-auto">
                    <span class="input-group-text bg-white"><i class="bi bi-funnel"></i></span>
                    <input type="text" id="dataSearch" class="form-control" placeholder="表格內即時快篩...">
                  </div>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table id="dataTable_sesslist" class="table table-striped table-hover table-bordered align-middle w-100 small" data-search="1">
                      <thead class="table-light text-nowrap">
                        <tr>
                          <th class="text-center" style="width: 55px;">ID</th>
                          <th>主課程 / 梯次名稱 / 代碼</th>
                          <th>開課排程與週期</th>
                          <th>Zoom 會議室 ID / 密碼</th>
                          <th class="text-center">課間即時狀態</th>
                          <th class="text-center">報名 / 簽到人數</th>
                          <th class="text-center">課後錄影</th>
                          <th class="text-center">報名開放</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($sessList as $row):
                          $sid     = (int)$row['sess_id'];
                          $liveSta = (int)($row['sess_live_status'] ?? 0);
                          $sessSta = (int)($row['sess_status'] ?? 1);
                          $maxCap  = (int)($row['course_max'] ?? 20);
                          $enrCnt  = (int)($row['active_enrolls'] ?? 0);
                          $chkCnt  = (int)($row['checked_in_cnt'] ?? 0);
                          $hasZoom = !empty($row['sess_meetingid']);
                          $hasRec  = !empty($row['sess_rec_url']);

                          // 解析週間上課日 (0-6)
                          $weekStr = '';
                          if (!empty($row['sess_onweek'])) {
                            $wArr = explode(',', $row['sess_onweek']);
                            $wTxt = array();
                            foreach ($wArr as $w) {
                              if (isset($weekNames[trim($w)])) $wTxt[] = '週' . $weekNames[trim($w)];
                            }
                            if (!empty($wTxt)) $weekStr = '每' . implode('、', $wTxt);
                          }
                        ?>
                        <tr style="cursor: pointer;" onclick="window.location.href='sess_detail.php?currID=<?php echo $sid; ?>';">
                          <td class="text-center fw-bold"><?php echo $sid; ?></td>
                          <td>
                            <div class="fw-bold text-dark">
                              <span class="badge text-bg-dark me-1">第 <?php echo (int)$row['sess_batch']; ?> 梯</span>
                              <?php echo htmlspecialchars($row['course_title'] ?? '未指定主課程'); ?>
                              <?php if (!empty($row['sess_keycode'])): ?>
                                <code class="ms-1 text-primary">[<?php echo htmlspecialchars($row['sess_keycode']); ?>]</code>
                              <?php endif; ?>
                            </div>
                            <?php if (!empty($row['sess_altname'])): ?>
                              <div class="text-muted" style="font-size: 0.78rem;"><?php echo htmlspecialchars($row['sess_altname']); ?></div>
                            <?php endif; ?>
                          </td>
                          <td class="fw-semibold">
                            <div><?php echo htmlspecialchars($row['sess_startdate'] ?? '-'); ?> <?php echo !empty($row['sess_starttime']) ? substr($row['sess_starttime'], 0, 5) : ''; ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;">
                              共 <?php echo (int)($row['sess_lesson'] ?? 1); ?> 堂 • <?php echo (int)($row['sess_duration'] ?? 40); ?> 分鐘
                              <?php if ($weekStr !== '') echo ' • ' . $weekStr; ?>
                            </div>
                          </td>
                          <td>
                            <?php if ($hasZoom): ?>
                              <div class="fw-bold text-primary"><i class="bi bi-camera-video me-1"></i><?php echo htmlspecialchars($row['sess_meetingid']); ?></div>
                              <?php if (!empty($row['sess_passcode'])): ?>
                                <div class="text-muted" style="font-size: 0.75rem;">密碼：<code><?php echo htmlspecialchars($row['sess_passcode']); ?></code></div>
                              <?php endif; ?>
                            <?php else: ?>
                              <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">尚未建立 Zoom 會議間</span>
                            <?php endif; ?>
                          </td>
                          <td class="text-center"><?php echo $liveMap[$liveSta] ?? $liveMap[0]; ?></td>
                          <td class="text-center">
                            <span class="badge text-bg-primary"><?php echo $enrCnt; ?> / <?php echo $maxCap; ?> 人</span>
                            <div class="text-success fw-semibold mt-1" style="font-size: 0.75rem;">已簽到：<?php echo $chkCnt; ?> 人</div>
                          </td>
                          <td class="text-center">
                            <?php if ($hasRec): ?>
                              <span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-play-btn-fill me-1"></i>已上架</span>
                            <?php else: ?>
                              <span class="text-muted">-</span>
                            <?php endif; ?>
                          </td>
                          <td class="text-center">
                            <?php echo ($sessSta === 1) ? '<span class="badge text-bg-success">開放中</span>' : '<span class="badge text-bg-secondary">已關閉</span>'; ?>
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
        <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-funnel-fill me-2 text-warning"></i>梯次與課堂進階篩選</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <form id="form_search" method="get" action="sess_list.php">
          <div class="mb-3">
            <label class="form-label small text-white-50">關鍵字（課程名稱 / 梯次代碼 / Zoom 會議號）</label>
            <input type="text" class="form-control form-control-sm" name="searchText" value="<?php echo htmlspecialchars($searchText); ?>" placeholder="輸入搜尋關鍵字...">
          </div>
          <div class="mb-3">
            <label class="form-label small text-white-50">所屬主課程</label>
            <select class="form-select form-select-sm" name="searchCourseId">
              <option value="">-- 全部主課程 --</option>
              <?php foreach ($masterCourses as $mc): ?>
                <option value="<?php echo (int)$mc['course_id']; ?>" <?php echo ($searchCourseId === (string)$mc['course_id']) ? 'selected' : ''; ?>>
                  #<?php echo (int)$mc['course_id']; ?> - <?php echo htmlspecialchars($mc['course_title']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label small text-white-50">課間即時狀態</label>
            <select class="form-select form-select-sm" name="searchLive">
              <option value="">-- 全部狀態 --</option>
              <option value="0" <?php echo ($searchLive === '0') ? 'selected' : ''; ?>>0 - 尚未開課</option>
              <option value="1" <?php echo ($searchLive === '1') ? 'selected' : ''; ?>>1 - Zoom 課間進行中 (LIVE)</option>
              <option value="2" <?php echo ($searchLive === '2') ? 'selected' : ''; ?>>2 - 已結課歸檔</option>
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
  function doButAdd() { window.location.href = 'sess_detail.php?currID=0'; }
  function doButReset() { window.location.replace('sess_list.php'); }
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

    var sesslistTB = datatableCreate('dataTable_sesslist');

    $('#dataSearch').on('keyup search input paste cut', function() {
      if ($(this).is(':focus') || this.value !== '') {
        sesslistTB.search(this.value).draw();
      }
    });

    $('#searchReset').click(function() {
      if ($('#dataSearch').val() !== '') {
        $('#dataSearch').val('');
        sesslistTB.search('').draw();
      } else {
        window.location.replace('sess_list.php');
      }
    });

    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
      window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  });
  </script>
</body>
</html>
