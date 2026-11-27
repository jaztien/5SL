<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 架構搜尋條件
$searchText = isset($_REQUEST['searchText']) ? trim(rawurldecode($_REQUEST['searchText'])) : '';
$searchCat  = isset($_REQUEST['searchCat']) ? trim($_REQUEST['searchCat']) : '';

$searchURI = "";
if ($searchText !== '') {
  $searchURI .= "&searchText=" . rawurlencode($searchText);
}
if ($searchCat !== '') {
  $searchURI .= "&searchCat=" . rawurlencode($searchCat);
}
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}

$maxResult = (!empty($searchURI)) ? 300 : 120;

// 定義本頁面代碼（歸屬於訊息管理系統 ITEM_MSG / PG_MSG）
define('PAGE_MARK', 'PG_ROBOT');
define('PAGE_ITEM', 'ITEM_MSG');
define('PAGE_OPANEL', 1); // 1-新增
define('DRAWER_OPANEL', !empty($searchURI) ? 2 : 1);
define('EXPORT_OPANEL', 3); // 3-Excel+Print
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$whereSql = " WHERE rob_status != 9 ";
$params   = array();

if ($searchText !== '') {
  $whereSql .= " AND (rob_topic LIKE ? OR rob_keywords LIKE ?) ";
  $likeStr = '%' . $searchText . '%';
  array_push($params, $likeStr, $likeStr);
}
if ($searchCat !== '') {
  $whereSql .= " AND rob_category = ? ";
  $params[] = (int)$searchCat;
}

$totalNum = (int)DB->single("SELECT COUNT(rob_id) FROM fsl_robot {$whereSql}", $params);
$sqlList  = "
  SELECT *
  FROM fsl_robot
  {$whereSql}
  ORDER BY rob_priority ASC, rob_id ASC
  LIMIT {$maxResult}
";
$robList = DB->query($sqlList, $params);
if (!is_array($robList)) $robList = array();

$catMap = array(
  0 => '<span class="badge bg-dark-subtle text-dark-emphasis border">0 - 全分類通用</span>',
  1 => '<span class="badge bg-secondary-subtle text-secondary-emphasis border">1 - 一般諮詢</span>',
  2 => '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">2 - 課程報名</span>',
  3 => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">3 - 訂單帳務</span>',
  4 => '<span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">4 - Zoom與影音</span>'
);

$matchMap = array(
  1 => '<span class="badge text-bg-light border text-dark">任一關鍵字 (OR)</span>',
  2 => '<span class="badge text-bg-light border text-primary">全部關鍵字 (AND)</span>',
  3 => '<span class="badge text-bg-light border text-info-emphasis">關鍵字 + 相似度</span>'
);

// 讀取全站自動應答總開關狀態（預設 1-啟用中）
$sysRobotCfg     = DB->getConfigVal('mess_robot');
$isRobotGlobalOn = (!is_array($sysRobotCfg) || !isset($sysRobotCfg['config_value']) || (string)$sysRobotCfg['config_value'] !== '0');
echo $isRobotGlobalOn;

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
  <title>Fifth Season Life 人生第五季 | 自動應答知識庫管理</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>

<body class="bg-body-tertiary theme-indigo">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1 ms-2"><i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?></div>
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                自動應答知識庫管理
                <i class="bi bi-question-circle-fill fs-6 text-secondary" data-bs-toggle="tooltip" title="設定關鍵字比對規則與多組不同口吻語氣變體，會員發問時自動依權重隨機挑選回覆"></i>
              </h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item"><a href="msg_list.php">雙向訊息列表</a></li>
                <li class="breadcrumb-item active">自動應答知識庫</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <!-- 即時問答模擬測試器 + 全站自動應答總開關 -->
          <div class="card border-info shadow-sm mb-3">
            <div class="card-header bg-info-subtle text-info-emphasis py-2 fw-bold small d-flex flex-wrap justify-content-between align-items-center gap-2">
              <span><i class="bi bi-magic me-1"></i>自動應答模擬測試器（測試會員輸入問題時會命中哪條規則與隨機語氣）</span>
              <div class="form-check form-switch mb-0 d-flex align-items-center gap-2 bg-white px-3 py-1 rounded-pill border border-info-subtle shadow-xs">
                <input class="form-check-input mt-0" type="checkbox" role="switch" id="chkGlobalRobotSwitch" name="chkGlobalRobotSwitch" style="cursor: pointer;" <?php echo $isRobotGlobalOn ? 'checked' : ''; ?>>
                <label class="form-check-label fw-bold mb-0 <?php echo $isRobotGlobalOn ? 'text-success' : 'text-danger'; ?>" id="lblGlobalRobotSwitch" for="chkGlobalRobotSwitch" style="cursor: pointer;">
                  <?php echo $isRobotGlobalOn ? '全站自動應答：啟用中' : '全站自動應答：已暫停'; ?>
                </label>
              </div>
            </div>
            <div class="card-body py-2 bg-white">
              <div class="row g-2 align-items-center">
                <div class="col-md-3">
                  <select class="form-select form-select-sm" id="simCategory">
                    <option value="0">不限分類 (全分類比對)</option>
                    <option value="1">1 - 一般諮詢客服</option>
                    <option value="2">2 - 課程與報名相關</option>
                    <option value="3">3 - 訂單與帳務問題</option>
                    <option value="4">4 - Zoom 與影音技術</option>
                  </select>
                </div>
                <div class="col-md-7">
                  <input type="text" class="form-control form-control-sm" id="simQuestion" placeholder="輸入模擬會員發問內容，例如：請問報名後如果臨時有事可以改期或退費嗎？">
                </div>
                <div class="col-md-2 d-grid">
                  <button type="button" class="btn btn-info btn-sm text-white fw-bold" id="btnSimulateRobot">
                    <i class="bi bi-play-fill me-1"></i>模擬比對測試
                  </button>
                </div>
              </div>
              <div id="simResultBox" class="mt-2 d-none"></div>
            </div>
          </div>

          <!-- 知識庫規則清單 -->
          <div class="row">
            <div class="col-12">
              <div class="card card-outline shadow-sm">
                <div class="card-header d-flex flex-wrap align-items-center gap-2">
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold"><i class="bi bi-cpu-fill me-1 text-primary"></i>自動應答規則清單</span>
                    <span class="badge text-bg-light border">共 <span id="resultCount"><?php echo $totalNum > $maxResult ? $totalNum . " 筆符合，表列 " . $maxResult : $totalNum; ?></span> 組規則</span>
                  </div>
                  <div class="ms-md-auto d-flex flex-wrap align-items-center gap-2">
                    <a href="msg_list.php" class="btn btn-outline-secondary btn-sm fw-bold">
                      <i class="bi bi-arrow-left me-1"></i>返回雙向訊息列表
                    </a>
                    <div class="input-group input-group-sm w-auto">
                      <span class="input-group-text bg-white"><i class="bi bi-funnel"></i></span>
                      <input type="text" id="dataSearch" class="form-control" placeholder="表格內即時快篩...">
                    </div>
                  </div>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table id="dataTable_robotlist" class="table table-striped table-hover table-bordered align-middle w-100 small" data-search="1">
                      <thead class="table-light text-nowrap">
                        <tr>
                          <th class="text-center" style="width: 65px;">優先度</th>
                          <th>適用分類 / 問題主題</th>
                          <th>觸發關鍵字與比對模式</th>
                          <th class="text-center">多口吻語氣變體</th>
                          <th class="text-center">覆核標記</th>
                          <th class="text-center">累計命中</th>
                          <th class="text-center">狀態</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($robList as $row):
                          $rid      = (int)$row['rob_id'];
                          $replies  = json_decode((string)$row['rob_replies'], true);
                          if (!is_array($replies)) $replies = array();
                          $toneList = array();
                          foreach ($replies as $rep) {
                            if (!empty($rep['tone'])) $toneList[] = $rep['tone'];
                          }
                          $needHuman = (int)($row['rob_need_human'] ?? 0);
                          $robSta    = (int)($row['rob_status'] ?? 1);
                        ?>
                          <tr style="cursor: pointer;" onclick="window.location.href='robot_detail.php?currID=<?php echo $rid; ?>';">
                            <td class="text-center fw-bold text-primary"><?php echo (int)$row['rob_priority']; ?></td>
                            <td>
                              <div class="mb-1"><?php echo $catMap[(int)$row['rob_category']] ?? $catMap[0]; ?></div>
                              <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['rob_topic']); ?></div>
                            </td>
                            <td>
                              <div class="mb-1"><?php echo $matchMap[(int)$row['rob_match_mode']] ?? $matchMap[1]; ?></div>
                              <div class="text-muted" style="font-size: 0.76rem;"><?php echo htmlspecialchars($row['rob_keywords']); ?></div>
                            </td>
                            <td class="text-center">
                              <span class="badge text-bg-primary"><?php echo count($replies); ?> 組變體</span>
                              <?php if (!empty($toneList)): ?>
                                <div class="text-muted mt-1" style="font-size: 0.72rem;"><?php echo htmlspecialchars(implode(' / ', $toneList)); ?></div>
                              <?php endif; ?>
                            </td>
                            <td class="text-center">
                              <?php echo ($needHuman === 1)
                                ? '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">仍需人工覆核</span>'
                                : '<span class="badge bg-light text-secondary border">直接完成應答</span>'; ?>
                            </td>
                            <td class="text-center fw-bold text-success"><?php echo number_format((int)$row['rob_hit_count']); ?> 次</td>
                            <td class="text-center">
                              <?php echo ($robSta === 1) ? '<span class="badge text-bg-success">啟用中</span>' : '<span class="badge text-bg-secondary">已停用</span>'; ?>
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
        <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-funnel-fill me-2 text-warning"></i>自動應答規則篩選</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <form id="form_search" method="get" action="robot_list.php">
          <div class="mb-3">
            <label class="form-label small text-white-50">關鍵字（問題主題 / 觸發關鍵字）</label>
            <input type="text" class="form-control form-control-sm" name="searchText" value="<?php echo htmlspecialchars($searchText); ?>" placeholder="輸入搜尋關鍵字...">
          </div>
          <div class="mb-4">
            <label class="form-label small text-white-50">適用分類</label>
            <select class="form-select form-select-sm" name="searchCat">
              <option value="">-- 全部分類 --</option>
              <option value="0" <?php echo ($searchCat === '0') ? 'selected' : ''; ?>>0 - 全分類通用</option>
              <option value="1" <?php echo ($searchCat === '1') ? 'selected' : ''; ?>>1 - 一般諮詢客服</option>
              <option value="2" <?php echo ($searchCat === '2') ? 'selected' : ''; ?>>2 - 課程與報名相關</option>
              <option value="3" <?php echo ($searchCat === '3') ? 'selected' : ''; ?>>3 - 訂單與帳務問題</option>
              <option value="4" <?php echo ($searchCat === '4') ? 'selected' : ''; ?>>4 - Zoom 與影音技術</option>
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
      window.location.href = 'robot_detail.php?currID=0';
    }

    function doButReset() {
      window.location.replace('robot_list.php');
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

      var robotlistTB = datatableCreate('dataTable_robotlist');

      $('#dataSearch').on('keyup search input paste cut', function() {
        if ($(this).is(':focus') || this.value !== '') {
          robotlistTB.search(this.value).draw();
        }
      });

      $('#searchReset').click(function() {
        if ($('#dataSearch').val() !== '') {
          $('#dataSearch').val('');
          robotlistTB.search('').draw();
        } else {
          window.location.replace('robot_list.php');
        }
      });

      // 即時模擬測試自動應答
      $('#btnSimulateRobot').on('click', function() {
        const q = $.trim($('#simQuestion').val());
        if (!q) {
          window.parent.toastr['warning']('請先輸入模擬會員發問內容！');
          return;
        }
        $.post('../funcFiles/robot_act_func.php', {
          'formAcce': 'func_robot_simulate',
          'token': getTokenVal(),
          'category': $('#simCategory').val(),
          'question': q
        }, function(data) {
          const $box = $('#simResultBox').removeClass('d-none');
          if (data['RTN'] == 1 && data['matched']) {
            $box.html(`
            <div class="alert alert-success py-2 px-3 mb-0 small">
              <div class="fw-bold mb-1">
                <i class="bi bi-check-circle-fill me-1"></i>命中規則：#${data['rob_id']}【${$('<div>').text(data['topic']).html()}】
                <span class="badge text-bg-warning text-dark ms-2">隨機抽中語氣：${$('<div>').text(data['tone']).html()}</span>
                <span class="badge bg-light text-dark border ms-1">匹配分數：${data['score']}</span>
              </div>
              <div class="p-2 bg-white border rounded text-dark mt-1">${$('<div>').text(data['reply']).html().replace(/\n/g, '<br>')}</div>
            </div>
          `);
          } else {
            $box.html(`
            <div class="alert alert-secondary py-2 px-3 mb-0 small">
              <i class="bi bi-info-circle me-1"></i>未命中任何啟用中的自動應答規則，系統將轉為「待後台人工回覆」狀態。
            </div>
          `);
          }
        }, 'json');
      });

      // 全站自動應答總開關即時切換
      $('#chkGlobalRobotSwitch').on('change', function() {
        const isChecked = $(this).is(':checked');
        const $lbl = $('#lblGlobalRobotSwitch');

        $.post('../funcFiles/robot_act_func.php', {
          'formAcce': 'func_robot_globalToggle',
          'token': getTokenVal(),
          'enabled': isChecked ? 1 : 0
        }, function(data) {
          console.log(data);
          if (window.parent && window.parent.toastr) {
            window.parent.toastr[data['msgType']](data['msgStr']);
          }
          if (data['RTN'] == 1) {
            if (isChecked) {
              $lbl.removeClass('text-danger').addClass('text-success').text('全站自動應答：啟用中');
            } else {
              $lbl.removeClass('text-success').addClass('text-danger').text('全站自動應答：已暫停');
            }
          } else {
            $('#chkGlobalRobotSwitch').prop('checked', !isChecked);
          }
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