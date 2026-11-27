<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 架構搜尋條件
$searchText   = isset($_REQUEST['searchText']) ? trim(rawurldecode($_REQUEST['searchText'])) : '';
$searchTier   = isset($_REQUEST['searchTier']) ? trim($_REQUEST['searchTier']) : '';
$searchGrand  = isset($_REQUEST['searchGrand']) ? trim($_REQUEST['searchGrand']) : '';
$searchStatus = isset($_REQUEST['searchStatus']) ? trim($_REQUEST['searchStatus']) : '';

$searchURI = "";
if ($searchText !== '') {
  $searchURI .= "&searchText=" . rawurlencode($searchText);
}
if ($searchTier !== '') {
  $searchURI .= "&searchTier=" . rawurlencode($searchTier);
}
if ($searchGrand !== '') {
  $searchURI .= "&searchGrand=" . rawurlencode($searchGrand);
}
if ($searchStatus !== '') {
  $searchURI .= "&searchStatus=" . rawurlencode($searchStatus);
}
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}

$maxResult = (!empty($searchURI)) ? 300 : 120;

// 定義本頁面代碼（對應左側樹狀導航列與頂部操作面板）
define('PAGE_MARK', 'PG_QUEST');
define('PAGE_ITEM', 'ITEM_ACT');
define('PAGE_OPANEL', 1); // 1-新增
define('DRAWER_OPANEL', !empty($searchURI) ? 2 : 1);
define('EXPORT_OPANEL', 3); // 3-Excel+Print

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

// 組合查詢條件
$whereSql = " WHERE q.quest_status != 9 ";
$params   = array();

if ($searchText !== '') {
  $whereSql .= " AND (q.quest_title LIKE ? OR q.quest_code LIKE ? OR q.quest_reward_label LIKE ?) ";
  $likeStr = '%' . $searchText . '%';
  array_push($params, $likeStr, $likeStr, $likeStr);
}
if ($searchTier !== '') {
  $whereSql .= " AND q.quest_tier_lvl = ? ";
  $params[] = (int)$searchTier;
}
if ($searchGrand !== '') {
  $whereSql .= " AND q.quest_is_grand = ? ";
  $params[] = (int)$searchGrand;
}
if ($searchStatus !== '') {
  $whereSql .= " AND q.quest_status = ? ";
  $params[] = (int)$searchStatus;
}

$totalNum  = (int)DB->single("SELECT COUNT(q.quest_id) FROM fsl_quests q" . $whereSql, $params);
$questList = DB->query("
  SELECT q.*,
         g.gift_title,
         (SELECT COUNT(*) FROM fsl_quest_records qr WHERE qr.quest_id = q.quest_id) AS track_cnt,
         (SELECT COUNT(*) FROM fsl_quest_records qr WHERE qr.quest_id = q.quest_id AND qr.qrec_status IN (1, 2)) AS done_cnt
  FROM fsl_quests q
  LEFT JOIN fsl_gifts g ON q.gift_id = g.gift_id
  {$whereSql}
  ORDER BY q.quest_tier_lvl ASC, q.quest_is_grand ASC, q.quest_sort ASC, q.quest_id ASC
  LIMIT {$maxResult}
", $params);
if (!is_array($questList)) $questList = array();

$tierMap = array(
  1 => '<span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">Lv.1 一般會員</span>',
  2 => '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">Lv.2 進階會員</span>',
  3 => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Lv.3 黃金高階VIP</span>',
  4 => '<span class="badge bg-dark text-warning border border-warning">Lv.4 黑鑽尊享VIP</span>'
);
$statusMap = array(
  0 => '<span class="badge text-bg-secondary">0 - 已停用下架</span>',
  1 => '<span class="badge text-bg-success">1 - 啟用進行中</span>'
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
  <title>Fifth Season Life 人生第五季 | 每日與成長任務系統</title>

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
                每日與成長任務系統管理
                <i class="bi bi-question-circle-fill fs-6 text-secondary" data-bs-toggle="tooltip" title="管理各會員等級階段任務、全通關終極寶箱大禮、關聯禮品包與會員達成進度"></i>
              </h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">互動行銷管理</li>
                <li class="breadcrumb-item active">每日與成長任務系統</li>
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
                    <span class="fw-bold"><i class="bi bi-flag-fill me-1 text-primary"></i>會員等級階段任務清單</span>
                    <span class="badge text-bg-light border">共 <span id="resultCount"><?php echo $totalNum > $maxResult ? $totalNum . " 筆符合，表列 " . $maxResult : $totalNum; ?></span> 項任務</span>
                  </div>
                  <div class="input-group input-group-sm w-auto flex-grow-1 flex-md-grow-0 ms-md-auto">
                    <span class="input-group-text bg-white"><i class="bi bi-funnel"></i></span>
                    <input type="text" id="dataSearch" class="form-control" placeholder="表格內即時快篩...">
                  </div>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table id="dataTable_questlist" class="table table-striped table-hover table-bordered align-middle w-100 small" data-search="1">
                      <thead class="table-light text-nowrap">
                        <tr>
                          <th class="text-center" style="width: 55px;">ID</th>
                          <th class="text-center">所屬等級群 / 排序</th>
                          <th>識別碼 (quest_code) / 任務標題</th>
                          <th class="text-center">達成目標</th>
                          <th>獎勵短標 / 關聯禮品 (fsl_gifts)</th>
                          <th class="text-center">達成人數 / 追蹤中</th>
                          <th class="text-center">狀態</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($questList as $row):
                          $qid     = (int)$row['quest_id'];
                          $tier    = (int)($row['quest_tier_lvl'] ?? 1);
                          $isGrand = (int)($row['quest_is_grand'] ?? 0);
                          $sta     = (int)($row['quest_status'] ?? 1);
                        ?>
                          <tr style="cursor: pointer;" onclick="window.location.href='quest_detail.php?currID=<?php echo $qid; ?>';">
                            <td class="text-center fw-bold"><?php echo $qid; ?></td>
                            <td class="text-center">
                              <div><?php echo $tierMap[$tier] ?? $tierMap[1]; ?></div>
                              <div class="text-muted mt-1" style="font-size: 0.72rem;">排序 #<?php echo (int)$row['quest_sort']; ?></div>
                            </td>
                            <td>
                              <div class="fw-bold text-dark">
                                <?php if ($isGrand === 1): ?>
                                  <span class="badge text-bg-danger me-1"><i class="bi bi-trophy-fill me-1"></i>全通關終極大禮</span>
                                <?php endif; ?>
                                <code class="me-1 text-primary">[<?php echo htmlspecialchars($row['quest_code']); ?>]</code>
                                <?php echo htmlspecialchars($row['quest_title']); ?>
                              </div>
                              <?php if (!empty($row['quest_desc'])): ?>
                                <div class="text-muted text-truncate" style="max-width: 360px; font-size: 0.78rem;"><?php echo htmlspecialchars($row['quest_desc']); ?></div>
                              <?php endif; ?>
                            </td>
                            <td class="text-center fw-bold text-primary">
                              <?php echo number_format((int)$row['quest_target_val']) . ' ' . htmlspecialchars($row['quest_unit']); ?>
                            </td>
                            <td style="font-size: 0.78rem;">
                              <div class="fw-bold text-danger"><?php echo htmlspecialchars($row['quest_reward_label']); ?></div>
                              <div class="text-muted">禮品：#<?php echo (int)$row['gift_id']; ?> <?php echo htmlspecialchars($row['gift_title'] ?? ''); ?></div>
                            </td>
                            <td class="text-center">
                              <span class="badge text-bg-success"><?php echo number_format((int)$row['done_cnt']); ?> 人達成</span>
                              <span class="badge text-bg-light border text-dark"><?php echo number_format((int)$row['track_cnt']); ?> 人參與</span>
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
        <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-funnel-fill me-2 text-warning"></i>成長任務進階篩選</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <form id="form_search" method="get" action="quest_list.php">
          <div class="mb-3">
            <label class="form-label small text-white-50">關鍵字（任務標題 / 識別碼 / 獎勵短標）</label>
            <input type="text" class="form-control form-control-sm" name="searchText" value="<?php echo htmlspecialchars($searchText); ?>" placeholder="輸入搜尋關鍵字...">
          </div>
          <div class="mb-3">
            <label class="form-label small text-white-50">所屬等級任務群</label>
            <select class="form-select form-select-sm" name="searchTier">
              <option value="">-- 全部等級 --</option>
              <option value="1" <?php echo ($searchTier === '1') ? 'selected' : ''; ?>>1 - 一般會員</option>
              <option value="2" <?php echo ($searchTier === '2') ? 'selected' : ''; ?>>2 - 進階會員</option>
              <option value="3" <?php echo ($searchTier === '3') ? 'selected' : ''; ?>>3 - 黃金高階VIP</option>
              <option value="4" <?php echo ($searchTier === '4') ? 'selected' : ''; ?>>4 - 黑鑽尊享VIP</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small text-white-50">任務性質</label>
            <select class="form-select form-select-sm" name="searchGrand">
              <option value="">-- 全部性質 --</option>
              <option value="0" <?php echo ($searchGrand === '0') ? 'selected' : ''; ?>>0 - 一般階段任務</option>
              <option value="1" <?php echo ($searchGrand === '1') ? 'selected' : ''; ?>>1 - 全任務達成終極寶箱大禮</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label small text-white-50">任務狀態</label>
            <select class="form-select form-select-sm" name="searchStatus">
              <option value="">-- 全部狀態 --</option>
              <option value="1" <?php echo ($searchStatus === '1') ? 'selected' : ''; ?>>1 - 啟用進行中</option>
              <option value="0" <?php echo ($searchStatus === '0') ? 'selected' : ''; ?>>0 - 已停用或下架</option>
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
    function doReDirect() {
      window.history.back();
    }

    function doButAdd() {
      window.location.href = 'quest_detail.php?currID=0';
    }

    function doButReset() {
      window.location.replace('quest_list.php');
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

    $(document).ready(function() {
      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));

      $('body').on('keypress click', function() {
        let timerElm = window.parent.$('#timer_on_exhausted');
        if (timerElm.length) timerElm.backward_timer('reset');
      });

      var questlistTB = datatableCreate('dataTable_questlist');

      $('#dataSearch').on('keyup search input paste cut', function() {
        if ($(this).is(':focus') || this.value !== '') {
          questlistTB.search(this.value).draw();
        }
      });

      $('#searchReset').click(function() {
        if ($('#dataSearch').val() !== '') {
          $('#dataSearch').val('');
          questlistTB.search('').draw();
        } else {
          window.location.replace('quest_list.php');
        }
      });

      //-------- 加載完成後更改外框主頁中樹狀目錄開合狀態 --------//
      if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
      }
    });
  </script>
</body>

</html>