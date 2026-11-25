<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 定義本頁面代碼
define('PAGE_MARK', 'PG_LOTTERY');
define('PAGE_ITEM', 'ITEM_ACT');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$lotID = currID;
$res   = ($lotID > 0) ? DB->row("SELECT * FROM fsl_lotteries WHERE lot_id = ? AND lot_status != 9", array($lotID)) : array();
if (!is_array($res)) $res = array();

// 撈取上架禮品清單 (供獎項池關聯 gift_id，NULL 代表銘謝惠顧)
$giftOptions = DB->query("SELECT gift_id, gift_code, gift_title, gift_type FROM fsl_gifts WHERE gift_status = 1 ORDER BY gift_id DESC");
if (!is_array($giftOptions)) $giftOptions = array();

// 查詢關聯資料：獎項池子表 (fsl_lottery_items)、資格規則 (erule_target_type=3)、中獎領收紀錄 (fsl_gift_records)
$itemRows    = array();
$ruleRows    = array();
$winLogRows  = array();
$totalWeight = 0;
$totalWonCnt = 0;

if ($lotID > 0) {
  $itemRows = DB->query("
    SELECT li.*, g.gift_title, g.gift_code
    FROM fsl_lottery_items li
    LEFT JOIN fsl_gifts g ON li.gift_id = g.gift_id
    WHERE li.lot_id = ?
    ORDER BY li.loti_slot_order ASC, li.loti_id ASC
  ", array($lotID));
  if (!is_array($itemRows)) $itemRows = array();

  foreach ($itemRows as $ir) {
    // 僅計算名額尚未抽完 (loti_quota != 0) 的有效權重總和
    if ((int)$ir['loti_quota'] !== 0) {
      $totalWeight += (int)$ir['loti_weight'];
    }
    $totalWonCnt += (int)$ir['loti_won_count'];
  }

  $ruleRows = DB->query("
    SELECT * FROM fsl_eligibility_rules
    WHERE erule_target_type = 3 AND erule_target_id = ?
    ORDER BY erule_sort ASC, erule_id ASC
  ", array($lotID));
  if (!is_array($ruleRows)) $ruleRows = array();

  $winLogRows = DB->query("
    SELECT r.*, g.gift_title, m.mb_name, m.mb_email
    FROM fsl_gift_records r
    LEFT JOIN fsl_gifts g ON r.gift_id = g.gift_id
    LEFT JOIN fsl_members m ON r.mb_id = m.mb_id
    WHERE r.grec_source_type = 2 AND r.grec_source_id = ?
    ORDER BY r.grec_id DESC
    LIMIT 200
  ", array($lotID));
  if (!is_array($winLogRows)) $winLogRows = array();
}

$modeLabels = array(
  1 => '1 - 優惠卡片 (調取單一禮品)',
  2 => '2 - 幸運輪盤 (多禮品權重擇一)',
  3 => '3 - 刮刮樂',
  4 => '4 - 驚喜寶箱'
);
$periodLabels = array(0 => '活動期間總計', 1 => '每日重置', 2 => '每週重置', 3 => '每月重置');
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
  <title>Fifth Season Life 人生第五季 | 幸運輪盤與抽獎活動詳情</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../pluginFiles/flatpickr/flatpickr.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>
<body class="bg-body-tertiary theme-pink">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1 ms-2"><i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?></div>
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                <?php echo !empty($lotID) ? "編輯" : "新增"; ?>幸運輪盤與抽獎活動
                <span class="fs-7">【ID: <?php echo $lotID; ?>】</span>
              </h4>
              <h6 class="m-0 fw-light text-muted fs-7 mt-1 lh-xs mb-n2"><i class="bi bi-arrow-return-right"></i> 活動呼叫代碼、消耗點數與次數週期、輪盤格位權重機率與資格規則</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">互動行銷管理</li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回列表">幸運輪盤與抽獎管理</li>
                <li class="breadcrumb-item active"><?php echo !empty($lotID) ? "抽獎活動詳情" : "新增抽獎活動"; ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <!-- 左側活動狀態與機率總覽卡片 -->
            <div class="col-md-3 mb-3">
              <div class="card card-danger card-outline shadow-lg">
                <div class="card-body">
                  <div class="text-center mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger mb-2" style="width: 66px; height: 66px;">
                      <i class="bi bi-bullseye fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1" id="sideLotTitle"><?php echo !empty($res['lot_title']) ? htmlspecialchars($res['lot_title']) : ($lotID > 0 ? '未命名活動' : '新建立抽獎活動'); ?></h5>
                    <code class="text-danger" id="sideLotCode"><?php echo !empty($res['lot_keycode']) ? htmlspecialchars($res['lot_keycode']) : '尚未設定代碼'; ?></code>
                  </div>

                  <ul class="list-group list-group-flush small">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>活動狀態</b>
                      <span id="sideLotStatus" class="<?php echo ((int)($res['lot_status'] ?? 1) === 1) ? 'text-success fw-bold' : 'text-secondary'; ?>">
                        <?php echo ((int)($res['lot_status'] ?? 1) === 1) ? '進行中' : '已關閉'; ?>
                      </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>抽獎模式</b>
                      <span><?php echo $modeLabels[(int)($res['lot_mode'] ?? 2)] ?? '幸運輪盤'; ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>單次消耗點數</b>
                      <span class="fw-bold text-warning-emphasis"><?php echo ((int)($res['lot_cost_pts'] ?? 0) > 0) ? number_format((int)$res['lot_cost_pts']) . ' PTS' : '免費參加'; ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>輪盤獎項格數</b>
                      <span><?php echo count($itemRows); ?> 格 (總權重 <?php echo $totalWeight; ?>)</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>累計抽出次數</b>
                      <span class="fw-bold text-primary"><?php echo number_format($totalWonCnt); ?> 次</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>最後更新</b>
                      <span class="text-muted" id="sideLotUpdated"><?php echo $res['lot_updated'] ?? '-'; ?></span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <!-- 右側多分頁管理區 -->
            <div class="col-md-9">
              <div class="card shadow-lg modern-card-tabs">
                <div class="card-header p-2">
                  <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                      <a class="nav-link active" href="#tabMenu1" data-index="1" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-sliders me-1"></i>基本規則與期程
                      </a>
                    </li>
                    <?php if ($lotID > 0): ?>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu2" data-index="2" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-pie-chart-fill me-1"></i>格位與機率 (<?php echo count($itemRows); ?>)
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu3" data-index="3" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-shield-check me-1"></i>資格條件規則 (<?php echo count($ruleRows); ?>)
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu4" data-index="4" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-trophy me-1"></i>中獎領收紀錄 (<?php echo count($winLogRows); ?>)
                      </a>
                    </li>
                    <?php endif; ?>
                  </ul>
                </div>

                <div class="card-body bg-opacity-50">
                  <div class="tab-content">
                    <!-- Tab 1: 抽獎活動基本規則與期程 (fsl_lotteries) -->
                    <div class="tab-pane fade show active" id="tabMenu1" role="tabpanel">
                      <form id="form_tabMenu1" action="../funcFiles/lot_act_func.php" method="post" data-func="func_lot_infoUpdate" novalidate>
                        <div class="row g-3">
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">活動呼叫代碼 (lot_keycode) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" id="lot_keycode" name="lot_keycode" required
                                   value="<?php echo htmlspecialchars($res['lot_keycode'] ?? ''); ?>" placeholder="如：LUCKY_WHEEL_2026">
                          </div>
                          <div class="col-md-5">
                            <label class="form-label small fw-bold">抽獎活動名稱 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lot_title" name="lot_title" required
                                   value="<?php echo htmlspecialchars($res['lot_title'] ?? ''); ?>" placeholder="如：人生第五季向光幸運輪盤">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">活動狀態</label>
                            <select class="form-select" id="lot_status" name="lot_status">
                              <option value="1" <?php echo ((int)($res['lot_status'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 進行中</option>
                              <option value="0" <?php echo ((int)($res['lot_status'] ?? 1) === 0) ? 'selected' : ''; ?>>0 - 已關閉</option>
                            </select>
                          </div>

                          <div class="col-md-4">
                            <label class="form-label small fw-bold">互動抽獎模式 (lot_mode)</label>
                            <select class="form-select" id="lot_mode" name="lot_mode">
                              <option value="2" <?php echo ((int)($res['lot_mode'] ?? 2) === 2) ? 'selected' : ''; ?>>2 - 幸運輪盤 (多禮品權重擇一)</option>
                              <option value="1" <?php echo ((int)($res['lot_mode'] ?? 2) === 1) ? 'selected' : ''; ?>>1 - 優惠卡片 (調取單一禮品)</option>
                              <option value="3" <?php echo ((int)($res['lot_mode'] ?? 2) === 3) ? 'selected' : ''; ?>>3 - 刮刮樂</option>
                              <option value="4" <?php echo ((int)($res['lot_mode'] ?? 2) === 4) ? 'selected' : ''; ?>>4 - 驚喜寶箱</option>
                            </select>
                          </div>
                          <div class="col-md-8">
                            <label class="form-label small fw-bold">活動副標說明</label>
                            <input type="text" class="form-control" name="lot_subtitle"
                                   value="<?php echo htmlspecialchars($res['lot_subtitle'] ?? ''); ?>" placeholder="顯示於抽獎視窗上方的活動說明">
                          </div>

                          <div class="col-12"><div class="hr-more">活動起訖期程、點數消耗與參加次數限制</div><hr class="my-1"></div>

                          <div class="col-md-6">
                            <label class="form-label small fw-bold">活動開始時間 (留空代表立即開始)</label>
                            <input type="text" class="form-control datetime-picker" name="lot_start_at"
                                   value="<?php echo htmlspecialchars($res['lot_start_at'] ?? ''); ?>" placeholder="YYYY-MM-DD HH:mm:ss">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label small fw-bold">活動結束時間 (留空代表常駐活動)</label>
                            <input type="text" class="form-control datetime-picker" name="lot_end_at"
                                   value="<?php echo htmlspecialchars($res['lot_end_at'] ?? ''); ?>" placeholder="YYYY-MM-DD HH:mm:ss">
                          </div>

                          <div class="col-md-3">
                            <label class="form-label small fw-bold">每次消耗 PTS (0=免費)</label>
                            <input type="number" class="form-control" name="lot_cost_pts" min="0" step="1"
                                   value="<?php echo (int)($res['lot_cost_pts'] ?? 0); ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">每人參加次數上限 (0=不限)</label>
                            <input type="number" class="form-control" name="lot_limit_per_user" min="0" step="1"
                                   value="<?php echo (int)($res['lot_limit_per_user'] ?? 1); ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">次數限制重置週期</label>
                            <select class="form-select" name="lot_limit_period">
                              <option value="0" <?php echo ((int)($res['lot_limit_period'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 活動期間總計</option>
                              <option value="1" <?php echo ((int)($res['lot_limit_period'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 每日重置</option>
                              <option value="2" <?php echo ((int)($res['lot_limit_period'] ?? 0) === 2) ? 'selected' : ''; ?>>2 - 每週重置</option>
                              <option value="3" <?php echo ((int)($res['lot_limit_period'] ?? 0) === 3) ? 'selected' : ''; ?>>3 - 每月重置</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">參與最低會員等級</label>
                            <select class="form-select" name="lot_min_lvl">
                              <option value="0" <?php echo ((int)($res['lot_min_lvl'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 所有註冊會員</option>
                              <option value="1" <?php echo ((int)($res['lot_min_lvl'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 一般會員以上</option>
                              <option value="2" <?php echo ((int)($res['lot_min_lvl'] ?? 0) === 2) ? 'selected' : ''; ?>>2 - 向光學員以上</option>
                              <option value="3" <?php echo ((int)($res['lot_min_lvl'] ?? 0) === 3) ? 'selected' : ''; ?>>3 - 僅限 VIP 會員</option>
                            </select>
                          </div>
                        </div>
                      </form>
                    </div>

                    <!-- Tab 2: 獎項池格位與權重機率設定 (fsl_lottery_items) -->
                    <?php if ($lotID > 0): ?>
                    <div class="tab-pane fade" id="tabMenu2" role="tabpanel">
                      <form id="form_add_lotitem" class="row g-2 align-items-end p-3 bg-light border rounded-3 mb-4">
                        <div class="col-md-1">
                          <label class="form-label small fw-bold">格位</label>
                          <input type="number" class="form-control form-control-sm" id="newSlotOrder" min="1" max="12" value="<?php echo count($itemRows) + 1; ?>" required>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">輪盤短標名稱 <span class="text-danger">*</span></label>
                          <input type="text" class="form-control form-control-sm" id="newSlotLabel" placeholder="如：500 PTS / 銘謝惠顧" required>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">關聯禮品 (空=銘謝惠顧)</label>
                          <select class="form-select form-select-sm" id="newSlotGift">
                            <option value="">-- 銘謝惠顧 / 再接再厲 --</option>
                            <?php foreach ($giftOptions as $go): ?>
                              <option value="<?php echo (int)$go['gift_id']; ?>">
                                #<?php echo (int)$go['gift_id']; ?> - <?php echo htmlspecialchars($go['gift_title']); ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="col-md-1">
                          <label class="form-label small fw-bold">底色</label>
                          <input type="color" class="form-control form-control-color form-control-sm w-100" id="newSlotColor" value="#ffc107">
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small fw-bold">權重值</label>
                          <input type="number" class="form-control form-control-sm" id="newSlotWeight" min="1" value="100" required>
                        </div>
                        <div class="col-md-1">
                          <label class="form-label small fw-bold">名額(-1無限)</label>
                          <input type="number" class="form-control form-control-sm" id="newSlotQuota" min="-1" value="-1" required>
                        </div>
                        <div class="col-md-1 d-grid">
                          <button type="submit" class="btn btn-danger btn-sm fw-bold" title="新增格位"><i class="bi bi-plus-lg"></i></button>
                        </div>
                      </form>

                      <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover align-middle w-100 small">
                          <thead class="table-light text-nowrap">
                            <tr>
                              <th class="text-center">格位</th>
                              <th>輪盤顯示短標 / 底色</th>
                              <th>關聯禮品 (fsl_gifts)</th>
                              <th class="text-center">權重比重</th>
                              <th class="text-center">即時中獎機率</th>
                              <th class="text-center">已抽出 / 名額上限</th>
                              <th class="text-center" style="width: 65px;">刪除</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if (empty($itemRows)): ?>
                              <tr><td colspan="7" class="text-center text-muted py-3">尚未設定任何輪盤格位獎項（建議設定 6～8 個格位）</td></tr>
                            <?php else: foreach ($itemRows as $ir):
                              $iid    = (int)$ir['loti_id'];
                              $wVal   = (int)$ir['loti_weight'];
                              $qVal   = (int)$ir['loti_quota'];
                              $prob   = ($totalWeight > 0 && $qVal !== 0) ? round(($wVal / $totalWeight) * 100, 2) : 0;
                              $bgHex  = !empty($ir['loti_color']) ? $ir['loti_color'] : '#6c757d';
                            ?>
                              <tr id="lotiRow_<?php echo $iid; ?>">
                                <td class="text-center fw-bold">#<?php echo (int)$ir['loti_slot_order']; ?></td>
                                <td>
                                  <span class="d-inline-block rounded-circle border me-1 align-middle" style="width: 14px; height: 14px; background-color: <?php echo htmlspecialchars($bgHex); ?>;"></span>
                                  <strong><?php echo htmlspecialchars($ir['loti_label']); ?></strong>
                                </td>
                                <td>
                                  <?php if (!empty($ir['gift_id'])): ?>
                                    <a href="gift_detail.php?currID=<?php echo (int)$ir['gift_id']; ?>" class="badge bg-danger-subtle text-danger-emphasis border text-decoration-none">
                                      <i class="bi bi-gift me-1"></i>#<?php echo (int)$ir['gift_id']; ?> <?php echo htmlspecialchars($ir['gift_title'] ?? ''); ?>
                                    </a>
                                  <?php else: ?>
                                    <span class="badge bg-light text-secondary border">銘謝惠顧（無實體/點數獎勵）</span>
                                  <?php endif; ?>
                                </td>
                                <td class="text-center fw-bold"><?php echo number_format($wVal); ?></td>
                                <td class="text-center">
                                  <?php if ($qVal === 0): ?>
                                    <span class="badge text-bg-secondary">0% (已抽完)</span>
                                  <?php else: ?>
                                    <span class="badge text-bg-primary"><?php echo $prob; ?> %</span>
                                  <?php endif; ?>
                                </td>
                                <td class="text-center">
                                  <span class="fw-bold text-success"><?php echo number_format((int)$ir['loti_won_count']); ?></span>
                                  / <?php echo ($qVal < 0) ? '無上限' : number_format($qVal); ?>
                                </td>
                                <td class="text-center">
                                  <button type="button" class="btn btn-outline-danger btn-xs py-0 px-2 btn-del-loti" data-iid="<?php echo $iid; ?>">
                                    <i class="bi bi-trash3"></i>
                                  </button>
                                </td>
                              </tr>
                            <?php endforeach; endif; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <?php endif; ?>
                    <!-- Tab 3: 參加資格條件規則 (共用 fsl_eligibility_rules, target_type=3) -->
                    <?php if ($lotID > 0): ?>
                    <div class="tab-pane fade" id="tabMenu3" role="tabpanel">
                      <form id="form_add_lot_rule" class="row g-2 align-items-end p-3 bg-light border rounded-3 mb-4">
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">條件判斷類型</label>
                          <select class="form-select form-select-sm" id="lRuleType">
                            <option value="gender">會員性別 (F / M)</option>
                            <option value="time_range">參加時間區間 (起,迄)</option>
                            <option value="birthday_month">當月壽星限定 (1)</option>
                            <option value="city">居住城市代碼 (如 01,02)</option>
                            <option value="mb_level">會員等級門檻 (1/2/3)</option>
                            <option value="total_spent">累計消費金額門檻</option>
                            <option value="id_last_digit">身分證尾數 (如 1,3,5,7,9)</option>
                          </select>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small fw-bold">比對運算子</label>
                          <select class="form-select form-select-sm" id="lRuleOp">
                            <option value="=">= (等於)</option>
                            <option value="IN">IN (包含於清單)</option>
                            <option value="BETWEEN">BETWEEN (區間)</option>
                            <option value=">=">&gt;= (大於等於)</option>
                            <option value="<=">&lt;= (小於等於)</option>
                            <option value="!=">!= (不等於)</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">條件值 <span class="text-danger">*</span></label>
                          <input type="text" class="form-control form-control-sm" id="lRuleVal" placeholder="如：1 或 2026-11-01 00:00:00,2026-12-31 23:59:59" required>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">不符資格提示警語 <span class="text-danger">*</span></label>
                          <input type="text" class="form-control form-control-sm" id="lRuleMsg" placeholder="顯示給會員的提示訊息" required>
                        </div>
                        <div class="col-md-1 d-grid">
                          <button type="submit" class="btn btn-danger btn-sm fw-bold" title="新增資格規則"><i class="bi bi-plus-lg"></i></button>
                        </div>
                      </form>

                      <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle w-100 small">
                          <thead class="table-light">
                            <tr>
                              <th>ID</th>
                              <th>條件類型</th>
                              <th>運算子</th>
                              <th>比對值</th>
                              <th>不符資格提示警語</th>
                              <th class="text-center" style="width: 70px;">刪除</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if (empty($ruleRows)): ?>
                              <tr><td colspan="6" class="text-center text-muted py-3">尚未設定額外參加資格限制（僅依 Tab 1 最低會員等級限制）</td></tr>
                            <?php else: foreach ($ruleRows as $rl):
                              $rid = (int)$rl['erule_id'];
                            ?>
                              <tr id="lRuleRow_<?php echo $rid; ?>">
                                <td><?php echo $rid; ?></td>
                                <td class="fw-bold text-danger"><?php echo htmlspecialchars($rl['erule_type']); ?></td>
                                <td><code><?php echo htmlspecialchars($rl['erule_operator']); ?></code></td>
                                <td><?php echo htmlspecialchars($rl['erule_val']); ?></td>
                                <td><?php echo htmlspecialchars($rl['erule_fail_msg']); ?></td>
                                <td class="text-center">
                                  <button type="button" class="btn btn-outline-danger btn-xs py-0 px-2 btn-del-lot-rule" data-rid="<?php echo $rid; ?>">
                                    <i class="bi bi-trash3"></i>
                                  </button>
                                </td>
                              </tr>
                            <?php endforeach; endif; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <!-- Tab 4: 會員中獎領收明細紀錄 (fsl_gift_records, grec_source_type=2) -->
                    <div class="tab-pane fade" id="tabMenu4" role="tabpanel">
                      <div class="table-responsive">
                        <table id="dataTable_lotwins" class="table table-striped table-sm table-bordered table-hover align-middle w-100 small" data-search="1">
                          <thead class="table-light text-nowrap">
                            <tr>
                              <th>領收ID</th>
                              <th>中獎會員</th>
                              <th>獲得禮品 / 獎項</th>
                              <th class="text-center">領取狀態</th>
                              <th>中獎時間</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            $gStaMap = array(
                              0 => '<span class="badge text-bg-warning">未拆封領取</span>',
                              1 => '<span class="badge text-bg-success">已領取入帳</span>',
                              2 => '<span class="badge text-bg-primary">實體寄送中</span>',
                              3 => '<span class="badge text-bg-secondary">已使用完畢</span>',
                              9 => '<span class="badge text-bg-dark">已逾期失效</span>'
                            );
                            foreach ($winLogRows as $wl):
                              $decWName = !empty($wl['mb_name']) ? _fn_secure_crypto($wl['mb_name'], 'de') : '會員 #' . $wl['mb_id'];
                              $decWMail = !empty($wl['mb_email']) ? _fn_secure_crypto($wl['mb_email'], 'de') : '';
                              $wSta     = (int)($wl['grec_status'] ?? 0);
                            ?>
                            <tr>
                              <td class="fw-bold">#<?php echo (int)$wl['grec_id']; ?></td>
                              <td>
                                <a href="mb_detail.php?currID=<?php echo (int)$wl['mb_id']; ?>" class="fw-bold text-decoration-none"><?php echo htmlspecialchars($decWName); ?></a>
                                <div class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($decWMail); ?></div>
                              </td>
                              <td class="fw-bold text-danger"><?php echo htmlspecialchars($wl['gift_title'] ?? '禮品 #' . $wl['gift_id']); ?></td>
                              <td class="text-center"><?php echo $gStaMap[$wSta] ?? ''; ?></td>
                              <td class="text-muted"><?php echo $wl['grec_created']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <script src="../pluginFiles/datatables3/datatables.min.js"></script>
  <script src="../pluginFiles/flatpickr/flatpickr.min.js"></script>
  <script src="../pluginFiles/flatpickr/location/zh_tw.js"></script>
  <script src="../scriptFiles/adminlte.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

  <script type="text/javascript">
  function validateBS5Form($form) {
    if (!$form || $form.length === 0) return true;
    const isValid = $form.checkValidity();
    $form.classList.add('was-validated');
    return isValid;
  }

  function doReDirect() { window.history.back(); }

  function doButSave() {
    var frm = $('#form_' + $('.tab-pane.active').prop("id"));
    if (!frm.length) return false;
    if (!validateBS5Form(frm)) {
      window.parent.toastr['warning']('請檢查標示紅框的必填欄位！');
      return false;
    }
    ajaxPOST(frm);
  }

  function doButDelete() {
    if (0 == parseInt('<?php echo $lotID; ?>')) return false;
    $.post('../funcFiles/lot_act_func.php', {
      'formAcce': 'func_lot_delete',
      'token': getTokenVal(),
      'lotID': '<?php echo $lotID; ?>'
    }, function(data) {
      if (data['RTN'] == 1) {
        window.parent.toastr['success'](data['msgStr']);
        window.location.replace('lot_list.php');
      } else {
        window.parent.toastr['error'](data['msgStr'] || '刪除失敗');
      }
    }, 'json');
  }

  function doButReset() { location.reload(true); }

  function ajaxPOST(frm) {
    var $frm = frm || $('#form_tabMenu1');
    var accE = (0 == parseInt('<?php echo $lotID; ?>')) ? 'func_lot_addnew' : $frm.data('func');
    var datA = getFormObjects($frm);

    $.ajax({
      type: 'post',
      url: '../funcFiles/lot_act_func.php',
      data: {
        'arrDatA': datA,
        'formAcce': accE,
        'token': getTokenVal(),
        'lotID': '<?php echo $lotID; ?>'
      },
      dataType: 'json',
      success: function(data) {
        if (accE == 'func_lot_addnew' && data['RTN'] == 1) {
          window.parent.toastr['success'](data['msgStr']);
          window.location.replace('lot_detail.php?currID=' + data['newID']);
          return false;
        }
        if (window.parent && window.parent.toastr) {
          window.parent.toastr[data['msgType']](data['msgStr']);
        }
        if (data['RTN'] == 1) {
          $frm.removeClass('was-validated');
          $('#sideLotTitle').text($('#lot_title').val() || '未命名活動');
          $('#sideLotCode').text($('#lot_keycode').val().toUpperCase() || '');
          const stVal = $('#lot_status').val();
          $('#sideLotStatus').text(stVal === '1' ? '進行中' : '已關閉').toggleClass('text-success fw-bold', stVal === '1').toggleClass('text-secondary', stVal !== '1');
          if (data['upTime']) $('#sideLotUpdated').text(data['upTime']);
          if (typeof doSerialize === 'function') doSerialize();
        }
      }
    });
  }

  $(document).ready(function() {
    if (typeof flatpickr !== 'undefined') {
      flatpickr($('.datetime-picker'), { locale: 'zh_tw', enableTime: true, enableSeconds: true, dateFormat: 'Y-m-d H:i:S', time_24hr: true, allowInput: true });
    }
    if ($('#dataTable_lotwins').length) datatableCreate('dataTable_lotwins');

    // 新增 / 刪除獎項池格位
    $('#form_add_lotitem').on('submit', function(e) {
      e.preventDefault();
      $.post('../funcFiles/lot_act_func.php', {
        'formAcce': 'func_lot_item_add',
        'token': getTokenVal(),
        'lotID': '<?php echo $lotID; ?>',
        'loti_slot_order': $('#newSlotOrder').val(),
        'loti_label': $('#newSlotLabel').val().trim(),
        'gift_id': $('#newSlotGift').val(),
        'loti_color': $('#newSlotColor').val(),
        'loti_weight': $('#newSlotWeight').val(),
        'loti_quota': $('#newSlotQuota').val()
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    $(document).on('click', '.btn-del-loti', function() {
      if (!confirm('確定要刪除此獎項池格位嗎？')) return;
      const iid = $(this).data('iid');
      $.post('../funcFiles/lot_act_func.php', {
        'formAcce': 'func_lot_item_delete',
        'token': getTokenVal(),
        'lotiID': iid
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 新增 / 刪除參加資格條件規則 (target_type=3)
    $('#form_add_lot_rule').on('submit', function(e) {
      e.preventDefault();
      $.post('../funcFiles/lot_act_func.php', {
        'formAcce': 'func_lot_rule_add',
        'token': getTokenVal(),
        'lotID': '<?php echo $lotID; ?>',
        'erule_type': $('#lRuleType').val(),
        'erule_operator': $('#lRuleOp').val(),
        'erule_val': $('#lRuleVal').val().trim(),
        'erule_fail_msg': $('#lRuleMsg').val().trim()
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    $(document).on('click', '.btn-del-lot-rule', function() {
      if (!confirm('確定要刪除此參加資格條件嗎？')) return;
      const rid = $(this).data('rid');
      $.post('../funcFiles/lot_act_func.php', {
        'formAcce': 'func_lot_rule_delete',
        'token': getTokenVal(),
        'eruleID': rid
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) $('#lRuleRow_' + rid).fadeOut(250, function() { $(this).remove(); });
      }, 'json');
    });

    $('li.prevLink').on('click', function() { window.location.replace(getCurrentURI('', 'lot_list.php')); });
    $('body').on('keypress click', function() {
      let timerElm = window.parent.$('#timer_on_exhausted');
      if (timerElm.length) timerElm.backward_timer('reset');
    });

    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
      window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  });
  </script>
</body>
</html>
