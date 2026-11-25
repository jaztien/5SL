<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 架構搜尋條件
$searchText     = isset($_REQUEST['searchText']) ? trim(rawurldecode($_REQUEST['searchText'])) : '';
$searchCat      = isset($_REQUEST['searchCat']) ? trim($_REQUEST['searchCat']) : '';
$searchReplySta = isset($_REQUEST['searchReplySta']) ? trim($_REQUEST['searchReplySta']) : '';
$searchTarget   = isset($_REQUEST['searchTarget']) ? trim($_REQUEST['searchTarget']) : '';

$searchURI = "";
if ($searchText !== '')     { $searchURI .= "&searchText=" . rawurlencode($searchText); }
if ($searchCat !== '')      { $searchURI .= "&searchCat=" . rawurlencode($searchCat); }
if ($searchReplySta !== '') { $searchURI .= "&searchReplySta=" . rawurlencode($searchReplySta); }
if ($searchTarget !== '')   { $searchURI .= "&searchTarget=" . rawurlencode($searchTarget); }
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}

$maxResult = (!empty($searchURI)) ? 300 : 120;

// 定義本頁面代碼
define('PAGE_MARK', 'PG_MSG');
define('PAGE_ITEM', 'ITEM_MSG');
define('PAGE_OPANEL', 1); // 1-新增(發送新訊息給前台)
define('SEARCH_OPANEL', !empty($searchURI) ? 2 : 1);
define('EXPORT_OPANEL', 3); // 3-Excel+Print

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

// 統計看板數據（僅統計首則主題對話 parent_id = 0）
$cntPending = (int)DB->single("SELECT COUNT(*) FROM fsl_messages WHERE parent_id = 0 AND msg_status = 1 AND msg_reply_status = 0");
$cntRobot   = (int)DB->single("SELECT COUNT(*) FROM fsl_messages WHERE parent_id = 0 AND msg_status = 1 AND msg_reply_status = 2");
$cntReplied = (int)DB->single("SELECT COUNT(*) FROM fsl_messages WHERE parent_id = 0 AND msg_status = 1 AND msg_reply_status = 1");
$cntActiveRobotRules = (int)DB->single("SELECT COUNT(*) FROM fsl_robot WHERE rob_status = 1");

// 組合查詢條件（僅列出對話串首則訊息 parent_id = 0）
$whereSql = " WHERE m.parent_id = 0 AND m.msg_status != 9 ";
$params   = array();

if ($searchText !== '') {
  $whereSql .= " AND (m.msg_title LIKE ? OR m.msg_content LIKE ?) ";
  $likeStr = '%' . $searchText . '%';
  array_push($params, $likeStr, $likeStr);
}
if ($searchCat !== '') {
  $whereSql .= " AND m.msg_category = ? ";
  $params[] = (int)$searchCat;
}
if ($searchReplySta !== '') {
  $whereSql .= " AND m.msg_reply_status = ? ";
  $params[] = (int)$searchReplySta;
}
if ($searchTarget !== '') {
  $whereSql .= " AND m.target_mode = ? ";
  $params[] = $searchTarget;
}

$totalNum = (int)DB->single("SELECT COUNT(m.msg_id) FROM fsl_messages m {$whereSql}", $params);
$sqlList  = "
  SELECT m.*,
         mb.mb_name AS sender_mb_name, mb.mb_email AS sender_mb_email,
         ad.ad_name AS sender_ad_name,
         (SELECT COUNT(*) FROM fsl_messages r WHERE r.parent_id = m.msg_id AND r.msg_status = 1) AS reply_cnt,
         (SELECT COUNT(*) FROM fsl_message_reads rd WHERE rd.msg_id = m.msg_id AND rd.is_read = 1) AS read_cnt
  FROM fsl_messages m
  LEFT JOIN fsl_members mb ON (m.sender_type = 1 AND m.sender_id = mb.mb_id)
  LEFT JOIN fsl_administrator ad ON (m.sender_type = 2 AND m.sender_id = ad.ad_id)
  {$whereSql}
  ORDER BY m.msg_is_pinned DESC, COALESCE(m.msg_last_reply_at, m.msg_created) DESC, m.msg_id DESC
  LIMIT {$maxResult}
";
$msgList = DB->query($sqlList, $params);
if (!is_array($msgList)) $msgList = array();

$catMap = array(
  1 => '<span class="badge bg-secondary-subtle text-secondary-emphasis border">一般諮詢客服</span>',
  2 => '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">課程與報名</span>',
  3 => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">訂單與帳務</span>',
  4 => '<span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">官方分眾公告</span>',
  5 => '<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">顧問專屬關懷</span>'
);

$replyStaMap = array(
  0 => '<span class="badge text-bg-danger"><i class="bi bi-exclamation-circle me-1"></i>待後台回覆</span>',
  1 => '<span class="badge text-bg-success"><i class="bi bi-check2-all me-1"></i>後台已回覆</span>',
  2 => '<span class="badge text-bg-info text-dark"><i class="bi bi-robot me-1"></i>機器人已自動回覆</span>',
  3 => '<span class="badge text-bg-secondary"><i class="bi bi-archive me-1"></i>已結案歸檔</span>'
);

$targetModeMap = array(
  'admin'  => '<span class="text-danger fw-bold"><i class="bi bi-arrow-down-left-circle me-1"></i>會員來訊給後台</span>',
  'single' => '<span class="text-primary"><i class="bi bi-person me-1"></i>單一指定會員</span>',
  'multi'  => '<span class="text-info-emphasis"><i class="bi bi-people me-1"></i>多筆指定會員</span>',
  'filter' => '<span class="text-warning-emphasis"><i class="bi bi-funnel me-1"></i>條件篩選受眾</span>',
  'all'    => '<span class="text-success fw-bold"><i class="bi bi-broadcast me-1"></i>全體會員廣播</span>'
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
  <title>Fifth Season Life 人生第五季 | 雙向訊息與自動應答管理</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>
<body class="bg-body-tertiary theme-indigo">
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                雙向訊息與自動應答管理
                <i class="bi bi-question-circle-fill fs-6 text-secondary" data-bs-toggle="tooltip" title="管理前後台雙向客服對話、單人/多人/條件篩選分眾發信，以及 AI 自動應答知識庫"></i>
                <span class="text-muted small ms-2">msg_list.php</span>
              </h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">訊息管理系統</li>
                <li class="breadcrumb-item active">雙向訊息列表</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <!-- 頂部狀態統計與自動應答知識庫快速入口 -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
              <a href="msg_list.php?searchReplySta=0" class="text-decoration-none">
                <div class="card border-start border-danger border-4 shadow-sm h-100">
                  <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                    <div>
                      <div class="text-muted small">待後台人工回覆</div>
                      <div class="fs-4 fw-bold text-danger"><?php echo $cntPending; ?></div>
                    </div>
                    <i class="bi bi-chat-left-dots-fill fs-2 text-danger opacity-50"></i>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-6 col-md-3">
              <a href="msg_list.php?searchReplySta=2" class="text-decoration-none">
                <div class="card border-start border-info border-4 shadow-sm h-100">
                  <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                    <div>
                      <div class="text-muted small">機器人已自動應答</div>
                      <div class="fs-4 fw-bold text-info-emphasis"><?php echo $cntRobot; ?></div>
                    </div>
                    <i class="bi bi-robot fs-2 text-info opacity-50"></i>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-6 col-md-3">
              <a href="msg_list.php?searchReplySta=1" class="text-decoration-none">
                <div class="card border-start border-success border-4 shadow-sm h-100">
                  <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                    <div>
                      <div class="text-muted small">後台已回覆對話</div>
                      <div class="fs-4 fw-bold text-success"><?php echo $cntReplied; ?></div>
                    </div>
                    <i class="bi bi-check2-circle fs-2 text-success opacity-50"></i>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-6 col-md-3">
              <a href="robot_list.php" class="text-decoration-none">
                <div class="card border-start border-primary border-4 shadow-sm h-100 bg-primary-subtle">
                  <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                    <div>
                      <div class="text-primary-emphasis fw-bold small">自動應答知識庫</div>
                      <div class="fs-6 fw-bold text-primary mt-1">啟用中 <?php echo $cntActiveRobotRules; ?> 組規則 <i class="bi bi-arrow-right-short"></i></div>
                    </div>
                    <i class="bi bi-cpu-fill fs-2 text-primary opacity-75"></i>
                  </div>
                </div>
              </a>
            </div>
          </div>

          <!-- 訊息對話列表卡片 -->
          <div class="row">
            <div class="col-12">
              <div class="card card-outline shadow-sm">
                <div class="card-header d-flex flex-wrap align-items-center gap-2">
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold"><i class="bi bi-envelope-paper-fill me-1 text-primary"></i>前後台雙向訊息與分眾發信清單</span>
                    <span class="badge text-bg-light border">共 <span id="resultCount"><?php echo $totalNum > $maxResult ? $totalNum . " 筆符合，表列 " . $maxResult : $totalNum; ?></span> 則對話</span>
                  </div>
                  <div class="ms-md-auto d-flex flex-wrap align-items-center gap-2">
                    <a href="robot_list.php" class="btn btn-outline-primary btn-sm fw-bold">
                      <i class="bi bi-robot me-1"></i>設定自動應答規則
                    </a>
                    <div class="input-group input-group-sm w-auto">
                      <span class="input-group-text bg-white"><i class="bi bi-funnel"></i></span>
                      <input type="text" id="dataSearch" class="form-control" placeholder="表格內即時快篩...">
                    </div>
                  </div>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table id="dataTable_msglist" class="table table-striped table-hover table-bordered align-middle w-100 small" data-search="1">
                      <thead class="table-light text-nowrap">
                        <tr>
                          <th class="text-center" style="width: 60px;">ID</th>
                          <th>分類 / 訊息主旨與摘要</th>
                          <th>發送者 / 對象模式</th>
                          <th class="text-center">對話處理狀態</th>
                          <th class="text-center">往來回覆 / 已讀數</th>
                          <th class="text-center">最新動態時間</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($msgList as $row):
                          $mid       = (int)$row['msg_id'];
                          $sType     = (int)$row['sender_type'];
                          $rSta      = (int)$row['msg_reply_status'];
                          $tMode     = $row['target_mode'] ?? 'single';
                          $isPinned  = (int)($row['msg_is_pinned'] ?? 0);

                          if ($sType === 1) {
                            $decName = !empty($row['sender_mb_name']) ? _fn_secure_crypto($row['sender_mb_name'], 'de') : '會員 #' . $row['sender_id'];
                            $senderLabel = '<span class="fw-bold text-dark"><i class="bi bi-person-circle text-info me-1"></i>' . htmlspecialchars($decName) . '</span>';
                          } else {
                            $adName = $row['sender_ad_name'] ?: ('管理員 #' . $row['sender_id']);
                            $senderLabel = '<span class="fw-bold text-primary"><i class="bi bi-shield-check me-1"></i>' . htmlspecialchars($adName) . '</span>';
                          }

                          $briefTxt = mb_strimwidth(strip_tags((string)$row['msg_content']), 0, 75, '...', 'UTF-8');
                          $lastTime = $row['msg_last_reply_at'] ?: $row['msg_created'];
                        ?>
                        <tr style="cursor: pointer;" onclick="window.location.href='msg_detail.php?currID=<?php echo $mid; ?>';">
                          <td class="text-center fw-bold">
                            <?php if ($isPinned): ?><i class="bi bi-pin-angle-fill text-danger me-1" title="置頂"></i><?php endif; ?>
                            #<?php echo $mid; ?>
                          </td>
                          <td>
                            <div class="mb-1">
                              <?php echo $catMap[(int)$row['msg_category']] ?? $catMap[1]; ?>
                              <span class="fw-bold text-dark ms-1"><?php echo htmlspecialchars($row['msg_title']); ?></span>
                            </div>
                            <div class="text-muted" style="font-size: 0.76rem;"><?php echo htmlspecialchars($briefTxt); ?></div>
                          </td>
                          <td>
                            <div><?php echo $senderLabel; ?></div>
                            <div style="font-size: 0.75rem;"><?php echo $targetModeMap[$tMode] ?? $tMode; ?></div>
                          </td>
                          <td class="text-center"><?php echo $replyStaMap[$rSta] ?? $replyStaMap[0]; ?></td>
                          <td class="text-center">
                            <span class="badge text-bg-light border text-dark"><?php echo (int)$row['reply_cnt']; ?> 則回覆</span>
                            <div class="text-muted mt-1" style="font-size: 0.72rem;">已讀：<?php echo (int)$row['read_cnt']; ?> 人</div>
                          </td>
                          <td class="text-center text-muted" style="font-size: 0.78rem;">
                            <?php echo !empty($lastTime) ? date('Y-m-d H:i', strtotime($lastTime)) : '-'; ?>
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
        <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-funnel-fill me-2 text-warning"></i>雙向訊息進階篩選</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <form id="form_search" method="get" action="msg_list.php">
          <div class="mb-3">
            <label class="form-label small text-white-50">關鍵字（訊息主旨 / 內文）</label>
            <input type="text" class="form-control form-control-sm" name="searchText" value="<?php echo htmlspecialchars($searchText); ?>" placeholder="輸入搜尋關鍵字...">
          </div>
          <div class="mb-3">
            <label class="form-label small text-white-50">訊息分類</label>
            <select class="form-select form-select-sm" name="searchCat">
              <option value="">-- 全部分類 --</option>
              <option value="1" <?php echo ($searchCat === '1') ? 'selected' : ''; ?>>1 - 一般諮詢客服</option>
              <option value="2" <?php echo ($searchCat === '2') ? 'selected' : ''; ?>>2 - 課程與報名相關</option>
              <option value="3" <?php echo ($searchCat === '3') ? 'selected' : ''; ?>>3 - 訂單與帳務問題</option>
              <option value="4" <?php echo ($searchCat === '4') ? 'selected' : ''; ?>>4 - 官方分眾公告</option>
              <option value="5" <?php echo ($searchCat === '5') ? 'selected' : ''; ?>>5 - 顧問專屬關懷</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small text-white-50">對話處理狀態</label>
            <select class="form-select form-select-sm" name="searchReplySta">
              <option value="">-- 全部狀態 --</option>
              <option value="0" <?php echo ($searchReplySta === '0') ? 'selected' : ''; ?>>0 - 待後台回覆</option>
              <option value="2" <?php echo ($searchReplySta === '2') ? 'selected' : ''; ?>>2 - 機器人已自動回覆</option>
              <option value="1" <?php echo ($searchReplySta === '1') ? 'selected' : ''; ?>>1 - 後台已回覆</option>
              <option value="3" <?php echo ($searchReplySta === '3') ? 'selected' : ''; ?>>3 - 已結案歸檔</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label small text-white-50">發送對象模式</label>
            <select class="form-select form-select-sm" name="searchTarget">
              <option value="">-- 全部模式 --</option>
              <option value="admin" <?php echo ($searchTarget === 'admin') ? 'selected' : ''; ?>>會員來訊給後台</option>
              <option value="single" <?php echo ($searchTarget === 'single') ? 'selected' : ''; ?>>單一指定會員</option>
              <option value="multi" <?php echo ($searchTarget === 'multi') ? 'selected' : ''; ?>>多筆指定會員</option>
              <option value="filter" <?php echo ($searchTarget === 'filter') ? 'selected' : ''; ?>>條件篩選受眾</option>
              <option value="all" <?php echo ($searchTarget === 'all') ? 'selected' : ''; ?>>全體會員廣播</option>
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
  function doButAdd() { window.location.href = 'msg_detail.php?currID=0'; }
  function doButReset() { window.location.replace('msg_list.php'); }
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

    var msglistTB = datatableCreate('dataTable_msglist');

    $('#dataSearch').on('keyup search input paste cut', function() {
      if ($(this).is(':focus') || this.value !== '') {
        msglistTB.search(this.value).draw();
      }
    });

    $('#searchReset').click(function() {
      if ($('#dataSearch').val() !== '') {
        $('#dataSearch').val('');
        msglistTB.search('').draw();
      } else {
        window.location.replace('msg_list.php');
      }
    });

    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
      window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  });
  </script>
</body>
</html>
