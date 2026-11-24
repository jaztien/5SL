<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 定義本頁面代碼
define('PAGE_MARK', 'PG_QUEST');
define('PAGE_ITEM', 'ITEM_ACT');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$questID = currID;
$res     = ($questID > 0) ? DB->row("SELECT * FROM fsl_quests WHERE quest_id = ? AND quest_status != 9", array($questID)) : array();
if (!is_array($res)) $res = array();

// 撈取上架禮品清單 (gift_id 為 fsl_quests 必填外鍵)
$giftOptions = DB->query("SELECT gift_id, gift_code, gift_title, gift_type FROM fsl_gifts WHERE gift_status = 1 ORDER BY gift_id DESC");
if (!is_array($giftOptions)) $giftOptions = array();

// 查詢會員任務進度與領獎子表 (fsl_quest_records)
$recRows    = array();
$memberOpts = array();
$doneCnt    = 0;
$claimedCnt = 0;
$ruleRows = array();

if ($questID > 0) {
  $recRows = DB->query("
    SELECT qr.*, m.mb_name, m.mb_email, gr.grec_status, gr.grec_shipping_note
    FROM fsl_quest_records qr
    LEFT JOIN fsl_members m ON qr.mb_id = m.mb_id
    LEFT JOIN fsl_gift_records gr ON qr.grec_id = gr.grec_id
    WHERE qr.quest_id = ?
    ORDER BY qr.qrec_updated DESC, qr.qrec_id DESC
    LIMIT 200
  ", array($questID));
  if (!is_array($recRows)) $recRows = array();

  foreach ($recRows as $rr) {
    if ((int)$rr['qrec_status'] >= 1) $doneCnt++;
    if ((int)$rr['qrec_status'] === 2) $claimedCnt++;
  }

  $mList = DB->query("SELECT mb_id, mb_name, mb_email FROM fsl_members WHERE mb_status = 1 ORDER BY mb_id DESC LIMIT 300");
  if (is_array($mList)) {
    foreach ($mList as $ml) {
      $memberOpts[] = array(
        'mb_id' => (int)$ml['mb_id'],
        'label' => '#' . $ml['mb_id'] . ' - ' . _fn_secure_crypto($ml['mb_name'], 'de') . ' (' . _fn_secure_crypto($ml['mb_email'], 'de') . ')'
      );
    }
  }

  $ruleRows = DB->query("
    SELECT * FROM fsl_eligibility_rules
    WHERE erule_target_type = 4 AND erule_target_id = ?
    ORDER BY erule_sort ASC, erule_id ASC
  ", array($questID));
  if (!is_array($ruleRows)) $ruleRows = array();


}

$tierLabels = array(1 => 'Lv.1 一般會員', 2 => 'Lv.2 進階會員', 3 => 'Lv.3 黃金高階VIP', 4 => 'Lv.4 黑鑽尊享VIP');
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
  <title>Fifth Season Life 人生第五季 | 每日與成長任務詳情</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>
<body class="bg-body-tertiary theme-purple">
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                <?php echo !empty($questID) ? "編輯" : "新增"; ?>成長任務與關聯獎勵
                <span class="fs-5 text-primary">【ID: <?php echo $questID; ?>】</span>
              </h4>
              <h6 class="m-0 fw-light text-muted">設定會員等級任務群、系統識別碼、目標門檻、關聯禮品包與人工補發進度</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">互動行銷管理</li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回列表">每日與成長任務系統</li>
                <li class="breadcrumb-item active"><?php echo !empty($questID) ? "任務詳情編輯" : "新增任務"; ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <!-- 左側任務摘要與達成統計卡片 -->
            <div class="col-md-3 mb-3">
              <div class="card card-primary card-outline shadow-lg">
                <div class="card-body">
                  <div class="text-center mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary mb-2" style="width: 66px; height: 66px;">
                      <i class="bi bi-flag-fill fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1" id="sideQuestTitle"><?php echo !empty($res['quest_title']) ? htmlspecialchars($res['quest_title']) : ($questID > 0 ? '未命名任務' : '新建立任務'); ?></h5>
                    <code class="text-primary" id="sideQuestCode"><?php echo !empty($res['quest_code']) ? htmlspecialchars($res['quest_code']) : '尚未設定識別碼'; ?></code>
                  </div>

                  <ul class="list-group list-group-flush small">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>啟用狀態</b>
                      <span id="sideQuestStatus" class="<?php echo ((int)($res['quest_status'] ?? 1) === 1) ? 'text-success fw-bold' : 'text-secondary'; ?>">
                        <?php echo ((int)($res['quest_status'] ?? 1) === 1) ? '啟用進行中' : '已停用下架'; ?>
                      </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>所屬等級群</b>
                      <span><?php echo $tierLabels[(int)($res['quest_tier_lvl'] ?? 1)] ?? 'Lv.1 一般會員'; ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>達成門檻</b>
                      <span class="fw-bold text-dark"><?php echo (int)($res['quest_target_val'] ?? 1) . ' ' . htmlspecialchars($res['quest_unit'] ?? '次'); ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>累計追蹤人數</b>
                      <span><?php echo count($recRows); ?> 人</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>達成 / 已領獎</b>
                      <span class="fw-bold text-success"><?php echo $doneCnt; ?> / <?php echo $claimedCnt; ?> 人</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>最後更新</b>
                      <span class="text-muted" id="sideQuestUpdated"><?php echo $res['quest_updated'] ?? '-'; ?></span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- 右側多分頁任務管理區 -->
            <div class="col-md-9">
              <div class="card shadow-lg modern-card-tabs">
                <div class="card-header p-2">
                  <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                      <a class="nav-link active" href="#tabMenu1" data-index="1" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-sliders me-1"></i>任務規則與關聯禮品設定
                      </a>
                    </li>
                    <?php if ($questID > 0): ?>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu2" data-index="2" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-people-fill me-1"></i>會員進度追蹤與人工補發 (<?php echo count($recRows); ?>)
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu3" data-index="3" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-shield-check me-1"></i>任務觸發條件規則 (<?php echo count($ruleRows); ?>)
                      </a>
                    </li>
                    <?php endif; ?>
                  </ul>
                </div>

                <div class="card-body bg-opacity-50">
                  <div class="tab-content">
                    <!-- Tab 1: 任務定義主表設定 (fsl_quests) -->
                    <div class="tab-pane fade show active" id="tabMenu1" role="tabpanel">
                      <form id="form_tabMenu1" action="../funcFiles/quest_act_func.php" method="post" data-func="func_quest_infoUpdate" novalidate>
                        <div class="row g-3">
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">任務系統識別碼 (quest_code) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" id="quest_code" name="quest_code" required
                                   value="<?php echo htmlspecialchars($res['quest_code'] ?? ''); ?>" placeholder="如：PROFILE_COMPLETE">
                          </div>
                          <div class="col-md-5">
                            <label class="form-label small fw-bold">任務標題 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quest_title" name="quest_title" required
                                   value="<?php echo htmlspecialchars($res['quest_title'] ?? ''); ?>" placeholder="如：任務一：完善個人專屬檔案與安全認證">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">任務狀態</label>
                            <select class="form-select" id="quest_status" name="quest_status">
                              <option value="1" <?php echo ((int)($res['quest_status'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 啟用進行中</option>
                              <option value="0" <?php echo ((int)($res['quest_status'] ?? 1) === 0) ? 'selected' : ''; ?>>0 - 已停用或下架</option>
                            </select>
                          </div>

                          <div class="col-md-4">
                            <label class="form-label small fw-bold">所屬等級任務群 (quest_tier_lvl)</label>
                            <select class="form-select" name="quest_tier_lvl">
                              <option value="1" <?php echo ((int)($res['quest_tier_lvl'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 一般會員</option>
                              <option value="2" <?php echo ((int)($res['quest_tier_lvl'] ?? 1) === 2) ? 'selected' : ''; ?>>2 - 進階會員</option>
                              <option value="3" <?php echo ((int)($res['quest_tier_lvl'] ?? 1) === 3) ? 'selected' : ''; ?>>3 - 黃金高階VIP</option>
                              <option value="4" <?php echo ((int)($res['quest_tier_lvl'] ?? 1) === 4) ? 'selected' : ''; ?>>4 - 黑鑽尊享VIP</option>
                            </select>
                          </div>
                          <div class="col-md-5">
                            <label class="form-label small fw-bold">任務性質 (是否為全通關終極大禮)</label>
                            <select class="form-select" name="quest_is_grand">
                              <option value="0" <?php echo ((int)($res['quest_is_grand'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 一般階段任務</option>
                              <option value="1" <?php echo ((int)($res['quest_is_grand'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 全任務達成終極寶箱大禮</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">顯示排序 (小靠前)</label>
                            <input type="number" class="form-control" name="quest_sort" min="1" value="<?php echo (int)($res['quest_sort'] ?? 1); ?>">
                          </div>

                          <div class="col-md-3">
                            <label class="form-label small fw-bold">達成目標數值 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quest_target_val" min="1" required
                                   value="<?php echo (int)($res['quest_target_val'] ?? 1); ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">進度單位顯示</label>
                            <input type="text" class="form-control" name="quest_unit" maxlength="16"
                                   value="<?php echo htmlspecialchars($res['quest_unit'] ?? '次'); ?>" placeholder="次 / % / 堂 / 位">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label small fw-bold">關聯獎品禮包 (fsl_gifts) <span class="text-danger">*</span></label>
                            <select class="form-select" name="gift_id" required>
                              <option value="">-- 請選擇對應發放的禮品包 --</option>
                              <?php foreach ($giftOptions as $go): ?>
                                <option value="<?php echo (int)$go['gift_id']; ?>" <?php echo ((int)($res['gift_id'] ?? 0) === (int)$go['gift_id']) ? 'selected' : ''; ?>>
                                  #<?php echo (int)$go['gift_id']; ?> - <?php echo htmlspecialchars($go['gift_title']); ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>

                          <div class="col-12">
                            <label class="form-label small fw-bold">獎品徽章顯示短標 (quest_reward_label) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="quest_reward_label" required
                                   value="<?php echo htmlspecialchars($res['quest_reward_label'] ?? ''); ?>" placeholder="例如：+150 PTS 紅利點數 / 限量黑金精裝筆記本(實物寄送)">
                          </div>

                          <div class="col-12">
                            <label class="form-label small fw-bold">任務條件與說明文案 (quest_desc)</label>
                            <textarea class="form-control" name="quest_desc" rows="3" maxlength="300"><?php echo htmlspecialchars($res['quest_desc'] ?? ''); ?></textarea>
                          </div>
                        </div>
                      </form>
                    </div>
                    <!-- Tab 2: 會員任務進度追蹤與人工補發達標 (fsl_quest_records) -->
                    <?php if ($questID > 0): ?>
                    <div class="tab-pane fade" id="tabMenu2" role="tabpanel">
                      <form id="form_grant_quest" class="row g-2 align-items-end p-3 bg-light border rounded-3 mb-4">
                        <div class="col-md-6">
                          <label class="form-label small fw-bold">選擇目標會員 <span class="text-danger">*</span></label>
                          <select class="form-select form-select-sm" id="grantMbId" required>
                            <option value="">-- 請選擇有效會員 --</option>
                            <?php foreach ($memberOpts as $mo): ?>
                              <option value="<?php echo $mo['mb_id']; ?>"><?php echo htmlspecialchars($mo['label']); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">設定目前進度值 (目標: <?php echo (int)($res['quest_target_val'] ?? 1); ?>)</label>
                          <input type="number" class="form-control form-control-sm" id="grantCurrVal" min="1" value="<?php echo (int)($res['quest_target_val'] ?? 1); ?>" required>
                        </div>
                        <div class="col-md-3 d-grid">
                          <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-check2-circle me-1"></i>更新進度 / 核發達標</button>
                        </div>
                      </form>

                      <div class="table-responsive">
                        <table id="dataTable_questrec" class="table table-striped table-sm table-bordered table-hover align-middle w-100 small" data-search="1">
                          <thead class="table-light text-nowrap">
                            <tr>
                              <th>流水ID</th>
                              <th>參與會員</th>
                              <th class="text-center">目前進度 / 目標</th>
                              <th class="text-center">任務狀態</th>
                              <th>達成 / 領獎時間</th>
                              <th>關聯禮品領收與寄送狀態</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            $targetV = max(1, (int)($res['quest_target_val'] ?? 1));
                            $unitStr = $res['quest_unit'] ?? '次';
                            $qStaMap = array(
                              0 => '<span class="badge text-bg-secondary">0 - 進行中 (未達標)</span>',
                              1 => '<span class="badge text-bg-warning">1 - 條件已達成 (待領獎)</span>',
                              2 => '<span class="badge text-bg-success">2 - 已完成並領取獎勵</span>'
                            );
                            $gStaMap = array(
                              0 => '待拆封領取',
                              1 => '已領取入帳',
                              2 => '實體寄送中',
                              3 => '已使用完畢',
                              9 => '已逾期失效'
                            );
                            foreach ($recRows as $rr):
                              $decName  = !empty($rr['mb_name']) ? _fn_secure_crypto($rr['mb_name'], 'de') : '會員 #' . $rr['mb_id'];
                              $decEmail = !empty($rr['mb_email']) ? _fn_secure_crypto($rr['mb_email'], 'de') : '';
                              $qSta     = (int)($rr['qrec_status'] ?? 0);
                              $curV     = (int)($rr['qrec_curr_val'] ?? 0);
                              $pct      = min(100, round(($curV / $targetV) * 100));
                            ?>
                            <tr>
                              <td class="fw-bold">#<?php echo (int)$rr['qrec_id']; ?></td>
                              <td>
                                <a href="mb_detail.php?currID=<?php echo (int)$rr['mb_id']; ?>" class="fw-bold text-decoration-none"><?php echo htmlspecialchars($decName); ?></a>
                                <div class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($decEmail); ?></div>
                              </td>
                              <td class="text-center">
                                <div class="fw-bold text-primary"><?php echo $curV . ' / ' . $targetV . ' ' . htmlspecialchars($unitStr); ?> (<?php echo $pct; ?>%)</div>
                                <div class="progress mt-1" style="height: 5px;">
                                  <div class="progress-bar bg-primary" style="width: <?php echo $pct; ?>%;"></div>
                                </div>
                              </td>
                              <td class="text-center"><?php echo $qStaMap[$qSta] ?? ''; ?></td>
                              <td style="font-size: 0.74rem;">
                                <div>達成：<?php echo !empty($rr['qrec_completed_at']) ? substr($rr['qrec_completed_at'], 0, 16) : '-'; ?></div>
                                <div class="text-muted">領獎：<?php echo !empty($rr['qrec_claimed_at']) ? substr($rr['qrec_claimed_at'], 0, 16) : '-'; ?></div>
                              </td>
                              <td style="font-size: 0.75rem;">
                                <?php if (!empty($rr['grec_id'])): ?>
                                  <span class="badge bg-danger-subtle text-danger-emphasis border">禮品流水 #<?php echo (int)$rr['grec_id']; ?></span>
                                  <span class="text-muted ms-1"><?php echo $gStaMap[(int)($rr['grec_status'] ?? 0)] ?? ''; ?></span>
                                  <?php if (!empty($rr['grec_shipping_note'])): ?>
                                    <div class="text-primary mt-1"><i class="bi bi-truck me-1"></i><?php echo htmlspecialchars($rr['grec_shipping_note']); ?></div>
                                  <?php endif; ?>
                                <?php else: ?>
                                  <span class="text-muted">尚未產生禮品領收單</span>
                                <?php endif; ?>
                              </td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <!-- Tab 3: 任務觸發與資格條件規則 (共用 fsl_eligibility_rules, target_type=4) -->
                    <div class="tab-pane fade" id="tabMenu3" role="tabpanel">
                      <form id="form_add_quest_rule" class="row g-2 align-items-end p-3 bg-light border rounded-3 mb-4">
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">觸發條件類型</label>
                          <select class="form-select form-select-sm" id="qRuleType">
                            <option value="gender">會員性別 (F / M)</option>
                            <option value="time_range">任務有效時間區間 (起,迄)</option>
                            <option value="birthday_month">當月壽星限定 (1)</option>
                            <option value="city">居住城市代碼 (如 01,02)</option>
                            <option value="mb_level">會員等級門檻 (1/2/3)</option>
                            <option value="total_spent">累計消費金額門檻</option>
                            <option value="id_last_digit">身分證尾數 (如 1,3,5,7,9)</option>
                          </select>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small fw-bold">比對運算子</label>
                          <select class="form-select form-select-sm" id="qRuleOp">
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
                          <input type="text" class="form-control form-control-sm" id="qRuleVal" placeholder="如：1 或 2026-11-01 00:00:00,2026-12-31 23:59:59" required>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">未達條件提示警語 <span class="text-danger">*</span></label>
                          <input type="text" class="form-control form-control-sm" id="qRuleMsg" placeholder="顯示給會員的提示訊息" required>
                        </div>
                        <div class="col-md-1 d-grid">
                          <button type="submit" class="btn btn-primary btn-sm fw-bold" title="新增觸發條件"><i class="bi bi-plus-lg"></i></button>
                        </div>
                      </form>

                      <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle w-100 small">
                          <thead class="table-light">
                            <tr>
                              <th>ID</th>
                              <th>觸發條件類型</th>
                              <th>運算子</th>
                              <th>比對值</th>
                              <th>未達條件提示警語</th>
                              <th class="text-center" style="width: 70px;">刪除</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if (empty($ruleRows)): ?>
                              <tr><td colspan="6" class="text-center text-muted py-3">尚未設定額外前置觸發條件（該等級群會員皆自動啟用追蹤）</td></tr>
                            <?php else: foreach ($ruleRows as $rl):
                              $rid = (int)$rl['erule_id'];
                            ?>
                              <tr id="qRuleRow_<?php echo $rid; ?>">
                                <td><?php echo $rid; ?></td>
                                <td class="fw-bold text-primary"><?php echo htmlspecialchars($rl['erule_type']); ?></td>
                                <td><code><?php echo htmlspecialchars($rl['erule_operator']); ?></code></td>
                                <td><?php echo htmlspecialchars($rl['erule_val']); ?></td>
                                <td><?php echo htmlspecialchars($rl['erule_fail_msg']); ?></td>
                                <td class="text-center">
                                  <button type="button" class="btn btn-outline-danger btn-xs py-0 px-2 btn-del-quest-rule" data-rid="<?php echo $rid; ?>">
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
    if (0 == parseInt('<?php echo $questID; ?>')) return false;
    $.post('../funcFiles/quest_act_func.php', {
      'formAcce': 'func_quest_delete',
      'token': getTokenVal(),
      'questID': '<?php echo $questID; ?>'
    }, function(data) {
      if (data['RTN'] == 1) {
        window.parent.toastr['success'](data['msgStr']);
        window.location.replace('quest_list.php');
      } else {
        window.parent.toastr['error'](data['msgStr'] || '刪除失敗');
      }
    }, 'json');
  }

  function doButReset() { location.reload(true); }

  function ajaxPOST(frm) {
    var $frm = frm || $('#form_tabMenu1');
    var accE = (0 == parseInt('<?php echo $questID; ?>')) ? 'func_quest_addnew' : $frm.data('func');
    var datA = getFormObjects($frm);

    $.ajax({
      type: 'post',
      url: '../funcFiles/quest_act_func.php',
      data: {
        'arrDatA': datA,
        'formAcce': accE,
        'token': getTokenVal(),
        'questID': '<?php echo $questID; ?>'
      },
      dataType: 'json',
      success: function(data) {
        if (accE == 'func_quest_addnew' && data['RTN'] == 1) {
          window.parent.toastr['success'](data['msgStr']);
          window.location.replace('quest_detail.php?currID=' + data['newID']);
          return false;
        }
        if (window.parent && window.parent.toastr) {
          window.parent.toastr[data['msgType']](data['msgStr']);
        }
        if (data['RTN'] == 1) {
          $frm.removeClass('was-validated');
          $('#sideQuestTitle').text($('#quest_title').val() || '未命名任務');
          $('#sideQuestCode').text($('#quest_code').val().toUpperCase() || '');
          const stVal = $('#quest_status').val();
          $('#sideQuestStatus').text(stVal === '1' ? '啟用進行中' : '已停用下架').toggleClass('text-success fw-bold', stVal === '1').toggleClass('text-secondary', stVal !== '1');
          if (data['upTime']) $('#sideQuestUpdated').text(data['upTime']);
          if (typeof doSerialize === 'function') doSerialize();
        }
      }
    });
  }

  $(document).ready(function() {
    if ($('#dataTable_questrec').length) datatableCreate('dataTable_questrec');

    // 後台手動更新會員任務進度或核發達標獎勵
    $('#form_grant_quest').on('submit', function(e) {
      e.preventDefault();
      const mbId = $('#grantMbId').val();
      const cVal = parseInt($('#grantCurrVal').val(), 10) || 1;
      if (!mbId) {
        window.parent.toastr['warning']('請選擇目標會員！');
        return;
      }
      $.post('../funcFiles/quest_act_func.php', {
        'formAcce': 'func_quest_grantProgress',
        'token': getTokenVal(),
        'questID': '<?php echo $questID; ?>',
        'mbID': mbId,
        'currVal': cVal
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 新增 / 刪除任務觸發條件規則 (erule_target_type = 4)
    $('#form_add_quest_rule').on('submit', function(e) {
      e.preventDefault();
      $.post('../funcFiles/quest_act_func.php', {
        'formAcce': 'func_quest_rule_add',
        'token': getTokenVal(),
        'questID': '<?php echo $questID; ?>',
        'erule_type': $('#qRuleType').val(),
        'erule_operator': $('#qRuleOp').val(),
        'erule_val': $('#qRuleVal').val().trim(),
        'erule_fail_msg': $('#qRuleMsg').val().trim()
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    $(document).on('click', '.btn-del-quest-rule', function() {
      if (!confirm('確定要刪除此任務觸發條件嗎？')) return;
      const rid = $(this).data('rid');
      $.post('../funcFiles/quest_act_func.php', {
        'formAcce': 'func_quest_rule_delete',
        'token': getTokenVal(),
        'eruleID': rid
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) $('#qRuleRow_' + rid).fadeOut(250, function() { $(this).remove(); });
      }, 'json');
    });


    $('li.prevLink').on('click', function() { window.location.replace(getCurrentURI('', 'quest_list.php')); });
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
