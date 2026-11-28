<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
define('PAGE_MARK', 'PG_SCH_CONSULT');
define('PAGE_ITEM', 'ITEM_SCH');
define('PAGE_OPANEL', 1); // 1-新增開放諮詢時段
define('DRAWER_OPANEL', 0);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$csrfToken = function_exists('_fn_genToken') ? _fn_genToken() : ($_SESSION['token'] ?? '');
$myAdId    = (int)($_SESSION['SESS_AD_ID'] ?? 0);

$tabFilter = trim((string)($_GET['tab'] ?? 'pending')); // pending(待審/開放中) | confirmed(已確認) | all(全部)

// 1. 讀取所有一對一顧問諮詢排程時段 (sch_source = 3)
$slots = DB->query("
  SELECT s.*,
         a.ad_name AS host_name,
         (SELECT COUNT(*) FROM fsl_consult_bookings b WHERE b.sch_id = s.sch_id AND b.bkg_status = 0) AS pending_cnt,
         (SELECT COUNT(*) FROM fsl_consult_bookings b WHERE b.sch_id = s.sch_id AND b.bkg_status = 1) AS approved_cnt,
         (SELECT COUNT(*) FROM fsl_eligibility_rules r WHERE r.erule_target_type = 4 AND r.erule_target_id = s.sch_id AND r.erule_status = 1) AS rule_cnt
  FROM fsl_schedules s
  LEFT JOIN fsl_administrator a ON (s.sch_target_type = 4 AND CAST(s.sch_target_ids AS UNSIGNED) = a.ad_id)
  WHERE s.sch_source = 3 AND s.sch_exec_status != 9
  ORDER BY (approved_cnt = 0 AND pending_cnt > 0) DESC, s.sch_start_at ASC
") ?: [];

// 計算 KPI 數據
$unconfirmedWithApps = 0; // 已有申請卻尚未確認歸屬的時段（義務提醒重點）
$totalPendingApps    = 0;
$confirmedSlots      = 0;
$openEmptySlots      = 0;

foreach ($slots as $sl) {
  $pCnt = (int)$sl['pending_cnt'];
  $aCnt = (int)$sl['approved_cnt'];
  $totalPendingApps += $pCnt;
  if ($aCnt > 0) {
    $confirmedSlots++;
  } elseif ($pCnt > 0) {
    $unconfirmedWithApps++;
  } else {
    $openEmptySlots++;
  }
}

// 2. 讀取所有關聯預約申請明細（依申請時間先後排序）
$allBookings = DB->query("
  SELECT b.*,
         m.mb_name, m.mb_email, m.mb_cellphone, m.mb_level,
         (SELECT COALESCE(SUM(o.ord_total), 0) FROM fsl_orders o WHERE o.mb_id = b.mb_id AND o.ord_status IN (2,3,4)) AS member_spent
  FROM fsl_consult_bookings b
  LEFT JOIN fsl_members m ON b.mb_id = m.mb_id
  ORDER BY b.bkg_status = 1 DESC, b.bkg_created ASC
") ?: [];

$bookingsBySch = [];
foreach ($allBookings as $bk) {
  $bookingsBySch[(int)$bk['sch_id']][] = $bk;
}

// 3. 讀取資格條件說明對照
$allRules = DB->query("SELECT * FROM fsl_eligibility_rules WHERE erule_target_type = 4 AND erule_status = 1 ORDER BY erule_sort ASC") ?: [];
$rulesBySch = [];
foreach ($allRules as $rl) {
  $rulesBySch[(int)$rl['erule_target_id']][] = $rl;
}

$admins  = DB->query("SELECT ad_id, ad_name FROM fsl_administrator WHERE ad_status = 1 ORDER BY ad_id ASC") ?: [];
$mbJS    = function_exists('_fn_getCachedJsonData') ? _fn_getCachedJsonData('../jsonFiles/member.json') : [];
$lvMap   = $mbJS['levels'] ?? [1 => ['title' => '一般會員'], 2 => ['title' => '向光學員'], 3 => ['title' => 'VIP 會員']];
?>
<!doctype html>
<html lang="zh-Hant-TW">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Fifth Season Life 人生第五季 | 一對一顧問諮詢預約排程與審核中心</title>
  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>

<body class="bg-body-tertiary theme-cyan p-2">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1">
    <i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?>
  </div>

  <div class="container-fluid py-2">
    <!-- 頂部標題與快速操作列 -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-camera-video-fill text-primary me-2"></i>一對一顧問諮詢預約排程與 Zoom 審核中心</h5>
        <small class="text-muted">開放時段在未核准確認歸屬前，前台仍開放符合資格之會員申請；核准確認後將自動建立 Zoom 會議室並登載入正式排程表</small>
      </div>

      <div class="d-flex gap-2">
        <a href="sch_cal_list.php" class="btn btn-outline-secondary btn-sm fw-bold">
          <i class="bi bi-calendar3 me-1"></i>檢視全站排程日曆
        </a>
        <button type="button" class="btn btn-primary btn-sm fw-bold" id="btnOpenNewSlot">
          <i class="bi bi-plus-circle me-1"></i>新增開放諮詢時段
        </button>
      </div>
    </div>

    <?php if ($unconfirmedWithApps > 0): ?>
    <div class="alert alert-danger d-flex align-items-center justify-content-between py-2 px-3 shadow-sm mb-3">
      <div>
        <i class="bi bi-bell-fill me-2"></i>
        <strong>待審核義務提醒：</strong>目前共有 <strong><?php echo $unconfirmedWithApps; ?> 個諮詢時段</strong> 已有會員提出預約申請，但尚未確認最終歸屬（預約尚未完成），請儘速審核決策！
      </div>
      <a href="?tab=pending" class="btn btn-danger btn-xs fw-bold">立即檢視待審時段</a>
    </div>
    <?php endif; ?>

    <!-- KPI 狀態統計看板 -->
    <div class="row g-3 mb-3">
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
          <div class="card-body py-2 px-3">
            <div class="small text-muted fw-semibold">已有申請・待決定歸屬時段</div>
            <div class="fs-4 fw-bold text-danger mt-1"><?php echo $unconfirmedWithApps; ?> 個時段 <small class="fs-6 fw-normal">(<?php echo $totalPendingApps; ?> 筆申請)</small></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
          <div class="card-body py-2 px-3">
            <div class="small text-muted fw-semibold">已確認歸屬並建立 Zoom 房</div>
            <div class="fs-4 fw-bold text-success mt-1"><?php echo $confirmedSlots; ?> 場</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
          <div class="card-body py-2 px-3">
            <div class="small text-muted fw-semibold">開放中・尚無人申請時段</div>
            <div class="fs-4 fw-bold text-info mt-1"><?php echo $openEmptySlots; ?> 個時段</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm border-start border-4 border-secondary h-100">
          <div class="card-body py-2 px-3">
            <div class="small text-muted fw-semibold">篩選檢視模式</div>
            <div class="btn-group btn-group-sm mt-1 w-100">
              <a href="?tab=pending" class="btn <?php echo $tabFilter === 'pending' ? 'btn-dark' : 'btn-outline-dark'; ?>">待審/開放中</a>
              <a href="?tab=confirmed" class="btn <?php echo $tabFilter === 'confirmed' ? 'btn-success' : 'btn-outline-success'; ?>">已確認Zoom</a>
              <a href="?tab=all" class="btn <?php echo $tabFilter === 'all' ? 'btn-secondary' : 'btn-outline-secondary'; ?>">全部</a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 各開放諮詢時段與申請人清單卡片 -->
    <?php
    $shownCount = 0;
    foreach ($slots as $sl):
      $sid   = (int)$sl['sch_id'];
      $pCnt  = (int)$sl['pending_cnt'];
      $aCnt  = (int)$sl['approved_cnt'];
      $isConfirmed = ($aCnt > 0);

      if ($tabFilter === 'pending' && $isConfirmed) continue;
      if ($tabFilter === 'confirmed' && !$isConfirmed) continue;
      $shownCount++;

      $apps  = $bookingsBySch[$sid] ?? [];
      $rules = $rulesBySch[$sid] ?? [];
      $startStr = date('Y-m-d (D) H:i', strtotime($sl['sch_start_at']));
      $endStr   = !empty($sl['sch_end_at']) ? date('H:i', strtotime($sl['sch_end_at'])) : '';
      $cardBorder = $isConfirmed ? 'border-success' : ($pCnt > 0 ? 'border-danger' : 'border-info');
    ?>
    <div class="card border-0 shadow-sm mb-3 border-start border-4 <?php echo $cardBorder; ?>">
      <div class="card-header bg-white py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex flex-wrap align-items-center gap-2">
          <?php if ($isConfirmed): ?>
            <span class="badge text-bg-success"><i class="bi bi-check-circle-fill me-1"></i>已確認歸屬・正式排程</span>
          <?php elseif ($pCnt > 0): ?>
            <span class="badge text-bg-danger"><i class="bi bi-hourglass-split me-1"></i>待審核確認 (<?php echo $pCnt; ?> 人申請中・前台仍開放)</span>
          <?php else: ?>
            <span class="badge text-bg-info text-dark"><i class="bi bi-unlock me-1"></i>開放預約中 (尚無人申請)</span>
          <?php endif; ?>

          <span class="fw-bold font-monospace text-dark fs-6">
            <i class="bi bi-calendar-event text-primary me-1"></i><?php echo $startStr . ($endStr ? " ~ {$endStr}" : ''); ?>
          </span>
          <span class="fw-bold text-primary"><?php echo htmlspecialchars($sl['sch_title']); ?></span>
          <?php if (!empty($sl['host_name'])): ?>
            <span class="badge text-bg-light border text-dark">顧問：<?php echo htmlspecialchars($sl['host_name']); ?></span>
          <?php endif; ?>
        </div>

        <div class="small text-muted">
          <?php if (empty($rules)): ?>
            <span class="badge text-bg-light border text-success">資格條件：全體會員皆可預約</span>
          <?php else: ?>
            <span class="badge text-bg-warning text-dark">
              <i class="bi bi-funnel-fill me-1"></i>資格限制：
              <?php
              $rTexts = [];
              foreach ($rules as $rl) {
                $rTexts[] = ($rl['erule_type'] === 'mb_level' ? '會員等級 IN (' . $rl['erule_val'] . ')' : '累計消費 >= ' . $rl['erule_val']);
              }
              echo htmlspecialchars(implode(' 且 ', $rTexts));
              ?>
            </span>
          <?php endif; ?>
        </div>
      </div>

      <div class="card-body p-0">
        <?php if (empty($apps)): ?>
          <div class="py-3 px-4 text-muted small">
            目前尚無會員提出此時段的預約申請（已依資格條件同步顯示於前台會員中心）。
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
              <thead class="table-light">
                <tr>
                  <th>申請單號 / 提出時間</th>
                  <th>申請會員 / 等級與消費</th>
                  <th>諮詢主題與補充提問</th>
                  <th>審核狀態 / Zoom 會議室資訊</th>
                  <th class="text-end">決策與 Zoom 遠端管理</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($apps as $bk):
                  $bid     = (int)$bk['bkg_id'];
                  $bSta    = (int)$bk['bkg_status'];
                  $mName   = _fn_safe_dec_name($bk['mb_name'] ?? '') ?: ('會員 #' . $bk['mb_id']);
                  $mEmail  = _fn_safe_dec_name($bk['mb_email'] ?? '');
                  $mPhone  = _fn_safe_dec_name($bk['mb_cellphone'] ?? '');
                  $lvTitle = $lvMap[(int)($bk['mb_level'] ?? 1)]['title'] ?? '一般會員';
                ?>
                <tr class="<?php echo $bSta === 1 ? 'table-success' : ($bSta === 2 ? 'opacity-50' : ''); ?>">
                  <td>
                    <div class="fw-bold font-monospace"><?php echo htmlspecialchars($bk['bkg_no']); ?></div>
                    <div class="text-muted"><i class="bi bi-clock me-1"></i><?php echo substr($bk['bkg_created'], 0, 16); ?></div>
                  </td>
                  <td>
                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($mName); ?> <span class="badge text-bg-light border text-dark"><?php echo htmlspecialchars($lvTitle); ?></span></div>
                    <div class="text-muted">累計消費：NT$ <?php echo number_format((float)$bk['member_spent']); ?></div>
                    <div class="text-muted"><?php echo htmlspecialchars($mEmail); ?> <?php echo $mPhone ? "/ {$mPhone}" : ''; ?></div>
                  </td>
                  <td style="max-width: 340px;">
                    <div class="fw-bold text-primary"><?php echo htmlspecialchars($bk['bkg_topic']); ?></div>
                    <?php if (!empty($bk['bkg_note'])): ?>
                      <div class="text-muted mt-1" style="white-space: pre-wrap;"><?php echo htmlspecialchars($bk['bkg_note']); ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($bSta === 0): ?>
                      <span class="badge text-bg-warning text-dark">待管理者審核</span>
                    <?php elseif ($bSta === 1): ?>
                      <span class="badge text-bg-success mb-1"><i class="bi bi-camera-video me-1"></i>已核准・Zoom 已建立</span>
                      <div class="font-monospace">ID: <strong><?php echo htmlspecialchars($bk['zoom_meeting_id'] ?? ''); ?></strong> ｜ 密碼: <strong><?php echo htmlspecialchars($bk['zoom_password'] ?? ''); ?></strong></div>
                      <div class="mt-1">
                        <a href="<?php echo htmlspecialchars($bk['zoom_join_url'] ?? '#'); ?>" target="_blank" class="me-2"><i class="bi bi-box-arrow-up-right"></i> 學員加入連結</a>
                        <?php if (!empty($bk['zoom_start_url'])): ?>
                          <a href="<?php echo htmlspecialchars($bk['zoom_start_url']); ?>" target="_blank" class="text-danger fw-bold"><i class="bi bi-play-circle-fill"></i> 主持人啟動</a>
                        <?php endif; ?>
                      </div>
                    <?php elseif ($bSta === 2): ?>
                      <span class="badge text-bg-secondary">未中選（已確認其他會員）</span>
                    <?php else: ?>
                      <span class="badge text-bg-danger">已取消 / 婉拒</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end text-nowrap">
                    <?php if (!$isConfirmed && $bSta === 0): ?>
                      <button type="button" class="btn btn-success btn-xs fw-bold btn-approve-bkg"
                              data-id="<?php echo $bid; ?>"
                              data-name="<?php echo htmlspecialchars($mName); ?>"
                              data-topic="<?php echo htmlspecialchars($bk['bkg_topic']); ?>">
                        <i class="bi bi-check2-circle me-1"></i>確認歸屬並建 Zoom 房
                      </button>
                    <?php elseif ($bSta === 1): ?>
                      <div class="btn-group btn-group-xs">
                        <button type="button" class="btn btn-outline-primary btn-zoom-resched"
                                data-id="<?php echo $bid; ?>"
                                data-start="<?php echo htmlspecialchars(str_replace(' ', 'T', substr($sl['sch_start_at'], 0, 16))); ?>">
                          Zoom改期
                        </button>
                        <button type="button" class="btn btn-outline-info text-dark btn-zoom-report" data-id="<?php echo $bid; ?>">
                          出席名單
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-zoom-cancel" data-id="<?php echo $bid; ?>">
                          取消並刪Zoom房
                        </button>
                      </div>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>

    <?php if ($shownCount === 0): ?>
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
          <i class="bi bi-calendar-check fs-2 d-block mb-2"></i>目前分類下沒有符合的顧問諮詢排程時段
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- 新增開放諮詢時段 Modal -->
  <div class="modal fade" id="newConsultSlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2">
          <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-1"></i>新增一對一顧問諮詢開放時段</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-bold">諮詢排程標題 <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="slot_title" value="一對一專屬顧問諮詢">
          </div>
          <div class="row g-2 mb-3">
            <div class="col-5">
              <label class="form-label small fw-bold">諮詢日期 <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="slot_date" value="<?php echo date('Y-m-d', strtotime('+2 days')); ?>">
            </div>
            <div class="col-4">
              <label class="form-label small fw-bold">開始時間 <span class="text-danger">*</span></label>
              <input type="time" class="form-control" id="slot_time" value="14:00">
            </div>
            <div class="col-3">
              <label class="form-label small fw-bold">時長(分)</label>
              <input type="number" class="form-control" id="slot_duration" value="60" min="15" max="240">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-bold">負責諮詢顧問 / 管理員</label>
            <select class="form-select" id="slot_host_ad">
              <?php foreach ($admins as $adm): ?>
              <option value="<?php echo (int)$adm['ad_id']; ?>" <?php echo (int)$adm['ad_id'] === $myAdId ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($adm['ad_name']); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <hr class="my-2">
          <div class="small fw-bold text-primary mb-2"><i class="bi bi-funnel me-1"></i>前台會員預約資格條件 (fsl_eligibility_rules，留空則全體開放)</div>
          <div class="row g-2 mb-2">
            <div class="col-6">
              <label class="form-label small mb-1">限制會員等級（逗號分隔）</label>
              <input type="text" class="form-control form-control-sm" id="slot_rule_level" placeholder="例如：2,3 (限向光/VIP)">
            </div>
            <div class="col-6">
              <label class="form-label small mb-1">最低累計消費金額 (NT$)</label>
              <input type="number" class="form-control form-control-sm" id="slot_rule_spent" placeholder="例如：3000">
            </div>
          </div>
          <div>
            <label class="form-label small mb-1">資格不符提示警語</label>
            <input type="text" class="form-control form-control-sm" id="slot_rule_fail" value="此時段僅限向光學員或 VIP 會員專屬預約。">
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
          <button type="button" class="btn btn-primary btn-sm fw-bold" id="btnSubmitNewSlot">建立開放時段</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Zoom 會後出席名單 Modal -->
  <div class="modal fade" id="zoomReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2">
          <h6 class="modal-title fw-bold"><i class="bi bi-people-fill text-info me-1"></i>Zoom 會議室出席與停留時間紀錄</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="zoomReportBody">載入中...</div>
      </div>
    </div>
  </div>

  <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script>
    const CSRF_TOKEN = '<?php echo htmlspecialchars($csrfToken, ENT_QUOTES); ?>';

    function doButAdd() {
      bootstrap.Modal.getOrCreateInstance(document.getElementById('newConsultSlotModal')).show();
    }
    function doButReset() {
      window.location.replace('consult_booking_list.php');
    }

    $(document).ready(function() {
      $('#btnOpenNewSlot').on('click', doButAdd);

      // 新增開放諮詢時段
      $('#btnSubmitNewSlot').on('click', function() {
        $.post('../funcFiles/consult_act_func.php', {
          formAcce: 'func_consult_slot_create',
          token: CSRF_TOKEN,
          sch_title: $('#slot_title').val(),
          start_date: $('#slot_date').val(),
          start_time: $('#slot_time').val(),
          duration: $('#slot_duration').val(),
          host_ad_id: $('#slot_host_ad').val(),
          rule_mb_level: $('#slot_rule_level').val(),
          rule_total_spent: $('#slot_rule_spent').val(),
          rule_fail_msg: $('#slot_rule_fail').val()
        }, function(res) {
          if (window.parent && window.parent.toastr) window.parent.toastr[res.msgType](res.msgStr);
          if (res.RTN == 1) location.reload();
        }, 'json');
      });

      // 核准確認歸屬並自動建立 Zoom 會議室
      $('.btn-approve-bkg').on('click', function() {
        const $btn  = $(this);
        const bid   = $btn.data('id');
        const mName = $btn.data('name');
        const topic = $btn.data('topic');

        if (!confirm(`確定將此時段確認核准給會員【${mName}】（主題：${topic}）嗎？\n\n確認後系統將自動：\n1. 呼叫 Zoom API 建立專屬會議室\n2. 正式登載進排程表並啟用提醒\n3. 發送 Zoom 連結通知給該會員（並通知同時間段其他申請者改約）`)) return;

        $btn.prop('disabled', true).text('Zoom 建房中...');
        $.post('../funcFiles/consult_act_func.php', {
          formAcce: 'func_consult_approve',
          token: CSRF_TOKEN,
          bkgID: bid
        }, function(res) {
          $btn.prop('disabled', false);
          if (window.parent && window.parent.toastr) window.parent.toastr[res.msgType](res.msgStr);
          if (res.RTN == 1) location.reload();
        }, 'json');
      });

      // Zoom 遠端修改時間
      $('.btn-zoom-resched').on('click', function() {
        const bid = $(this).data('id');
        const curStart = $(this).data('start').replace('T', ' ');
        const newTime = prompt('請輸入新的諮詢開始時間（格式 YYYY-MM-DD HH:MM）：', curStart);
        if (!newTime) return;

        $.post('../funcFiles/consult_act_func.php', {
          formAcce: 'func_consult_zoom_sync_update',
          token: CSRF_TOKEN,
          bkgID: bid,
          new_start_at: newTime + ':00',
          duration: 60
        }, function(res) {
          if (window.parent && window.parent.toastr) window.parent.toastr[res.msgType](res.msgStr);
          if (res.RTN == 1) location.reload();
        }, 'json');
      });

      // 取消預約並遠端刪除 Zoom 會議室
      $('.btn-zoom-cancel').on('click', function() {
        const bid = $(this).data('id');
        if (!confirm('確定要取消此筆確認預約，並同步在 Zoom 雲端刪除該會議室嗎？')) return;

        $.post('../funcFiles/consult_act_func.php', {
          formAcce: 'func_consult_cancel_and_delete_zoom',
          token: CSRF_TOKEN,
          bkgID: bid
        }, function(res) {
          if (window.parent && window.parent.toastr) window.parent.toastr[res.msgType](res.msgStr);
          if (res.RTN == 1) location.reload();
        }, 'json');
      });

      // 拉取 Zoom 會後出席名單
      $('.btn-zoom-report').on('click', function() {
        const bid = $(this).data('id');
        $('#zoomReportBody').html('<div class="text-center py-3">正在從 Zoom API 取得出席紀錄...</div>');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('zoomReportModal')).show();

        $.post('../funcFiles/consult_act_func.php', {
          formAcce: 'func_consult_zoom_report',
          token: CSRF_TOKEN,
          bkgID: bid
        }, function(res) {
          if (res.RTN != 1 || !res.participants || !res.participants.length) {
            $('#zoomReportBody').html(`<div class="alert alert-warning mb-0 small">${res.msgStr || '目前尚無已結束會議之出席紀錄。'}</div>`);
            return;
          }
          let html = '<table class="table table-sm table-bordered small mb-0"><thead class="table-light"><tr><th>參加者</th><th>進入時間</th><th>離開時間</th><th>停留分鐘</th></tr></thead><tbody>';
          res.participants.forEach(p => {
            const mins = Math.round((Number(p.duration) || 0) / 60);
            html += `<tr><td>${p.name || '—'}</td><td>${(p.join_time || '').replace('T',' ').substr(0,16)}</td><td>${(p.leave_time || '').replace('T',' ').substr(0,16)}</td><td class="fw-bold">${mins} 分</td></tr>`;
          });
          html += '</tbody></table>';
          $('#zoomReportBody').html(html);
        }, 'json');
      });

      if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
      }
    });
  </script>
</body>
</html>

