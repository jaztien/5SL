<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 定義本頁面代碼
define('PAGE_MARK', 'PG_ENROLL');
define('PAGE_ITEM', 'ITEM_COURSE');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$enrID = currID;
$res   = ($enrID > 0) ? DB->row("
  SELECT e.*,
         m.mb_name, m.mb_email, m.mb_cellphone, m.mb_title, m.mb_level,
         c.course_title, c.course_type, c.course_lesson, c.course_cost,
         s.sess_batch, s.sess_startdate, s.sess_starttime, s.sess_meetingid, s.sess_passcode, s.sess_rec_url,
         o.ord_no, o.ord_subtotal, o.ord_discount, o.ord_pts_used, o.ord_total, o.ord_status,
         i.inv_id, i.inv_no, i.inv_type, i.inv_carrier_type, i.inv_carrier_code, i.inv_tax_id, i.inv_company_title, i.inv_pay_gateway, i.inv_paid_at, i.inv_status
  FROM fsl_course_enrolls e
  LEFT JOIN fsl_members m ON e.mb_id = m.mb_id
  LEFT JOIN fsl_courses c ON e.course_id = c.course_id
  LEFT JOIN fsl_course_sessions s ON e.sess_id = s.sess_id
  LEFT JOIN fsl_orders o ON e.ord_id = o.ord_id
  LEFT JOIN fsl_invoices i ON o.ord_id = i.ord_id
  WHERE e.enr_id = ?
", array($enrID)) : array();
if (!is_array($res)) $res = array();

$mbID     = (int)($res['mb_id'] ?? 0);
$courseID = (int)($res['course_id'] ?? 0);
$sessID   = (int)($res['sess_id'] ?? 0);

// 解密學員個資
$decName  = !empty($res['mb_name']) ? _fn_secure_crypto($res['mb_name'], 'de') : '';
$decEmail = !empty($res['mb_email']) ? _fn_secure_crypto($res['mb_email'], 'de') : '';
$decPhone = !empty($res['mb_cellphone']) ? _fn_secure_crypto($res['mb_cellphone'], 'de') : '';

// 頭像設定
$thumbFile = "../uploadFiles/mb/mbphoto_" . $mbID . "_thumb.jpg";
$avatarSrc = ($mbID > 0 && file_exists($thumbFile)) ? ($thumbFile . "?t=" . filemtime($thumbFile)) : "../imageFiles/nopic_user_200x200.jpg";

// 取得所有主課程與梯次（供手動補登或梯次調度切換）
$masterCourses = DB->query("SELECT course_id, course_title, course_lesson FROM fsl_courses WHERE parent_id = 0 AND course_status != 9 ORDER BY course_sort ASC, course_id ASC");
if (!is_array($masterCourses)) $masterCourses = array();

$allSessions = DB->query("SELECT sess_id, course_id, sess_batch, sess_startdate, sess_starttime, sess_altname FROM fsl_course_sessions WHERE sess_status != 9 ORDER BY course_id ASC, sess_batch ASC");
if (!is_array($allSessions)) $allSessions = array();

// 若為新增補登模式，撈取有效會員供選擇
$memberOptions = array();
if ($enrID === 0) {
  $mRows = DB->query("SELECT mb_id, mb_name, mb_email FROM fsl_members WHERE mb_status = 1 ORDER BY mb_id DESC LIMIT 300");
  if (is_array($mRows)) {
    foreach ($mRows as $mr) {
      $memberOptions[] = array(
        'mb_id' => (int)$mr['mb_id'],
        'label' => '#' . $mr['mb_id'] . ' - ' . _fn_secure_crypto($mr['mb_name'], 'de') . ' (' . _fn_secure_crypto($mr['mb_email'], 'de') . ')'
      );
    }
  }
}

// 查詢發送給該學員的歷史通知紀錄 (fsl_notifications)
// 查詢發送給該學員的歷史通知紀錄 (fsl_notifications)
$notiRows = ($mbID > 0) ? DB->query("SELECT * FROM fsl_notifications WHERE mb_id = ? ORDER BY no_id DESC LIMIT 30", array($mbID)) : array();
if (!is_array($notiRows)) $notiRows = array();

// 查詢所屬梯次的單堂課次排程明細 (fsl_course_lessons)
$lessonRows = ($sessID > 0) ? DB->query("
  SELECT *
  FROM fsl_course_lessons
  WHERE sess_id = ? AND less_status != 9
  ORDER BY less_no ASC, less_start_at ASC, less_id ASC
", array($sessID)) : array();
if (!is_array($lessonRows)) $lessonRows = array();

$actualTotalLessons = !empty($lessonRows) ? count($lessonRows) : max(1, (int)($res['course_lesson'] ?? 1));

$payLabels    = array(0 => '待繳費占名額', 1 => '已付款完成', 2 => '免費/票券兌換', 9 => '逾期未繳釋出');
$statusLabels = array(0 => '已取消報名', 1 => '待開課(已預約)', 2 => '修課進行中', 3 => '已完課結業', 4 => '申請改期/延梯中');
$attendLabels = array(0 => '未簽到', 1 => '已簽到上課中', 2 => '已完成簽退', 3 => '請假', 4 => '缺席');
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
  <title>Fifth Season Life 人生第五季 | 學員報名與服務全景中控</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../pluginFiles/flatpickr/flatpickr.min.css">
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
                <?php echo ($enrID > 0) ? "學員報名與服務全景中控" : "手動補登課程報名"; ?>
                <span class="fs-5 text-primary">【報名ID: #<?php echo $enrID; ?>】</span>
              </h4>
              <h6 class="m-0 fw-light text-muted">支付發票對帳、梯次調度、專屬訊息通訊、課間出席與課後結業追蹤</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">課程管理系統</li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回列表">講座與課程報名紀錄</li>
                <li class="breadcrumb-item active"><?php echo ($enrID > 0) ? "報名服務中控" : "手動補登"; ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <!-- 左側學員身份與快速服務動作卡 -->
            <div class="col-md-3 mb-3">
              <div class="card card-primary card-outline shadow-lg">
                <div class="card-body box-profile">
                  <div class="text-center mb-2">
                    <img src="<?php echo $avatarSrc; ?>" class="rounded-circle border shadow-sm" width="84" height="84" style="object-fit: cover;" alt="Avatar">
                    <h5 class="fw-bold mt-2 mb-0"><?php echo ($enrID > 0) ? htmlspecialchars($decName) : '新補登學員'; ?></h5>
                    <div class="small text-muted"><?php echo ($enrID > 0) ? htmlspecialchars($decEmail) : '請於右側選擇會員與課程'; ?></div>
                    <?php if ($mbID > 0): ?>
                      <a href="mb_detail.php?currID=<?php echo $mbID; ?>" class="btn btn-outline-secondary btn-xs py-0 px-2 mt-2">
                        <i class="bi bi-person-badge me-1"></i>檢視完整會員檔案 (#<?php echo $mbID; ?>)
                      </a>
                    <?php endif; ?>
                  </div>

                  <?php if ($enrID > 0): ?>
                  <!-- 快速服務動作按鈕群 -->
                  <div class="d-grid gap-2 my-3">
                    <button type="button" class="btn btn-primary btn-sm fw-bold" id="btnQuickSendZoom">
                      <i class="bi bi-send-check-fill me-1"></i>發送 / 補發 Zoom 上課連結
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm fw-bold" id="btnQuickGraduate">
                      <i class="bi bi-award-fill me-1"></i>一鍵核發完課結業並通知
                    </button>
                  </div>
                  <?php endif; ?>

                  <ul class="list-group list-group-flush small">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>聯絡手機</b>
                      <span><?php echo htmlspecialchars($decPhone ?: '-'); ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>付款狀態</b>
                      <span id="sidePayLabel" class="fw-bold <?php echo in_array((int)($res['enr_pay_status'] ?? 0), array(1, 2), true) ? 'text-success' : 'text-warning-emphasis'; ?>">
                        <?php echo $payLabels[(int)($res['enr_pay_status'] ?? 0)] ?? '-'; ?>
                      </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>修課進度</b>
                      <span id="sideEnrStatus" class="fw-bold text-primary">
                        <?php echo $statusLabels[(int)($res['enr_status'] ?? 1)] ?? '-'; ?>
                      </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>課堂點名</b>
                      <span><?php echo $attendLabels[(int)($res['enr_attend_status'] ?? 0)] ?? '未簽到'; ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>連結發送次數</b>
                      <span id="sideLinkCnt"><?php echo (int)($res['enr_link_sent_cnt'] ?? 0); ?> 次</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>報名時間</b>
                      <span class="text-muted"><?php echo $res['enr_created'] ?? '-'; ?></span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <!-- 右側多分頁全景服務中控台 -->
            <div class="col-md-9">
              <div class="card shadow-lg modern-card-tabs">
                <div class="card-header p-2">
                  <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                      <a class="nav-link active" href="#tabMenu1" data-index="1" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-sliders me-1"></i>學員選課設定
                      </a>
                    </li>
                    <?php if ($enrID > 0): ?>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu2" data-index="2" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-receipt-cutoff me-1"></i>付費與金流
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu3" data-index="3" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-chat-dots me-1"></i>通訊通知推播 (<?php echo count($notiRows); ?>)
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu4" data-index="4" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-clipboard2-pulse me-1"></i>課堂與課後狀態
                      </a>
                    </li>
                    <?php endif; ?>
                  </ul>
                </div>

                <div class="card-body bg-opacity-50">
                  <div class="tab-content">
                    <!-- Tab 1: 報名進度、梯次調度與諮詢主題 -->
                    <div class="tab-pane fade show active" id="tabMenu1" role="tabpanel">
                      <form id="form_tabMenu1" action="../funcFiles/enr_act_func.php" method="post" data-func="func_enr_infoUpdate" novalidate>
                        <div class="row g-3">
                          <?php if ($enrID === 0): ?>
                          <div class="col-md-12">
                            <label class="form-label small fw-bold">選擇報名會員 <span class="text-danger">*</span></label>
                            <select class="form-select" name="mb_id" required>
                              <option value="">-- 請選擇有效會員 --</option>
                              <?php foreach ($memberOptions as $mo): ?>
                                <option value="<?php echo $mo['mb_id']; ?>"><?php echo htmlspecialchars($mo['label']); ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <?php endif; ?>

                          <div class="col-md-6">
                            <label class="form-label small fw-bold">報名主課程 <span class="text-danger">*</span></label>
                            <select class="form-select" id="course_id" name="course_id" required <?php echo ($enrID > 0) ? 'disabled' : ''; ?>>
                              <option value="">-- 請選擇主課程 --</option>
                              <?php foreach ($masterCourses as $mc): ?>
                                <option value="<?php echo (int)$mc['course_id']; ?>" <?php echo ($courseID === (int)$mc['course_id']) ? 'selected' : ''; ?>>
                                  #<?php echo (int)$mc['course_id']; ?> - <?php echo htmlspecialchars($mc['course_title']); ?> (共 <?php echo (int)$mc['course_lesson']; ?> 堂)
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>

                          <div class="col-md-6">
                            <label class="form-label small fw-bold">所屬開課梯次（支援改期/延梯調度） <span class="text-danger">*</span></label>
                            <select class="form-select" id="sess_id" name="sess_id" required>
                              <option value="">-- 請選擇開課梯次 --</option>
                              <?php foreach ($allSessions as $as):
                                if ($enrID > 0 && (int)$as['course_id'] !== $courseID) continue;
                              ?>
                                <option value="<?php echo (int)$as['sess_id']; ?>" data-cid="<?php echo (int)$as['course_id']; ?>" <?php echo ($sessID === (int)$as['sess_id']) ? 'selected' : ''; ?>>
                                  第 <?php echo (int)$as['sess_batch']; ?> 梯 • <?php echo $as['sess_startdate'] . ' ' . substr($as['sess_starttime'], 0, 5); ?>
                                  <?php if (!empty($as['sess_altname'])) echo ' (' . htmlspecialchars($as['sess_altname']) . ')'; ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>

                          <div class="col-md-4">
                            <label class="form-label small fw-bold">預約計費類型</label>
                            <select class="form-select" name="enr_quota_type">
                              <option value="1" <?php echo ((int)($res['enr_quota_type'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 付費購買 / 訂單結帳</option>
                              <option value="2" <?php echo ((int)($res['enr_quota_type'] ?? 1) === 2) ? 'selected' : ''; ?>>2 - VIP 免費額度 / 公關贈課</option>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">付款狀態</label>
                            <select class="form-select" id="enr_pay_status" name="enr_pay_status">
                              <option value="0" <?php echo ((int)($res['enr_pay_status'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 未付款 (待繳費/占名額)</option>
                              <option value="1" <?php echo ((int)($res['enr_pay_status'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 已付款完成</option>
                              <option value="2" <?php echo ((int)($res['enr_pay_status'] ?? 0) === 2) ? 'selected' : ''; ?>>2 - 免費或票券兌換</option>
                              <option value="9" <?php echo ((int)($res['enr_pay_status'] ?? 0) === 9) ? 'selected' : ''; ?>>9 - 逾期未繳已釋出</option>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">報名與修課進度狀態</label>
                            <select class="form-select" id="enr_status" name="enr_status">
                              <option value="1" <?php echo ((int)($res['enr_status'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 待開課 (已預約)</option>
                              <option value="2" <?php echo ((int)($res['enr_status'] ?? 1) === 2) ? 'selected' : ''; ?>>2 - 修課進行中</option>
                              <option value="3" <?php echo ((int)($res['enr_status'] ?? 1) === 3) ? 'selected' : ''; ?>>3 - 已完課結業</option>
                              <option value="4" <?php echo ((int)($res['enr_status'] ?? 1) === 4) ? 'selected' : ''; ?>>4 - 申請改期或延梯中</option>
                              <option value="0" <?php echo ((int)($res['enr_status'] ?? 1) === 0) ? 'selected' : ''; ?>>0 - 已取消報名</option>
                            </select>
                          </div>

                          <div class="col-md-4">
                            <label class="form-label small fw-bold">已完成單元 / 堂數（本梯共 <?php echo $actualTotalLessons; ?> 堂）</label>
                            <input type="number" class="form-control" name="enr_done_lessons" min="0" max="<?php echo $actualTotalLessons; ?>"
                                  value="<?php echo (int)($res['enr_done_lessons'] ?? 0); ?>">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">回放觀看權限到期日 (留空=永久)</label>
                            <input type="date" class="form-control" name="enr_expire_at"
                                   value="<?php echo !empty($res['enr_expire_at']) ? substr($res['enr_expire_at'], 0, 10) : ''; ?>">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">專屬 Zoom 進入連結</label>
                            <input type="text" class="form-control" name="enr_zoom_url"
                                   value="<?php echo htmlspecialchars($res['enr_zoom_url'] ?? ''); ?>" placeholder="派發連結後自動產生">
                          </div>

                          <div class="col-12">
                            <label class="form-label small fw-bold">會員預約填寫之諮詢主題與重點 (enr_topic)</label>
                            <textarea class="form-control" name="enr_topic" rows="2" placeholder="會員於前台預約一對一顧問或陪跑時填寫的困擾與期待..."><?php echo htmlspecialchars($res['enr_topic'] ?? ''); ?></textarea>
                          </div>

                          <div class="col-12">
                            <label class="form-label small fw-bold">學員備註或改期延梯需求紀錄 (enr_note)</label>
                            <textarea class="form-control" id="enr_note" name="enr_note" rows="3" placeholder="記錄客服接洽改期原因、特殊協助需求或後續服務追蹤重點..."><?php echo htmlspecialchars($res['enr_note'] ?? ''); ?></textarea>
                          </div>
                        </div>
                      </form>
                    </div>

                    <!-- Tab 2: 支付金流與電子發票對帳 -->
                    <div class="tab-pane fade" id="tabMenu2" role="tabpanel">
                      <?php if (empty($res['ord_no'])): ?>
                        <div class="alert alert-secondary small">
                          <i class="bi bi-info-circle me-1"></i>本筆報名為「後台手動補登 / VIP 免費額度兌換」，無關聯前台消費訂單。
                        </div>
                      <?php else: ?>
                        <div class="card border mb-3">
                          <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-primary"><i class="bi bi-bag-check me-1"></i>關聯訂單：<?php echo htmlspecialchars($res['ord_no']); ?></span>
                            <?php if ((int)($res['enr_pay_status'] ?? 0) === 0): ?>
                              <button type="button" class="btn btn-success btn-sm fw-bold" id="btnConfirmPaid">
                                <i class="bi bi-check2-circle me-1"></i>確認已收款入帳並開立發票
                              </button>
                            <?php endif; ?>
                          </div>
                          <div class="card-body small">
                            <div class="row g-3">
                              <div class="col-md-3"><span class="text-muted d-block">訂單小計</span><strong>NT$ <?php echo number_format((float)$res['ord_subtotal']); ?></strong></div>
                              <div class="col-md-3"><span class="text-muted d-block">優惠券折抵</span><strong class="text-danger">- NT$ <?php echo number_format((float)$res['ord_discount']); ?></strong></div>
                              <div class="col-md-3"><span class="text-muted d-block">PTS 點數折抵</span><strong class="text-warning-emphasis">- <?php echo number_format((int)$res['ord_pts_used']); ?> PTS</strong></div>
                              <div class="col-md-3"><span class="text-muted d-block">實付總金額</span><strong class="text-success fs-6">NT$ <?php echo number_format((float)$res['ord_total']); ?></strong></div>
                            </div>
                          </div>
                        </div>

                        <div class="card border">
                          <div class="card-header bg-light fw-bold"><i class="bi bi-receipt me-1 text-success"></i>電子發票與載具憑證</div>
                          <div class="card-body small">
                            <div class="row g-3">
                              <div class="col-md-4"><span class="text-muted d-block">電子發票號碼</span><strong class="text-dark"><?php echo htmlspecialchars($res['inv_no'] ?? '尚未開立'); ?></strong></div>
                              <div class="col-md-4"><span class="text-muted d-block">支付管道 / 入帳時間</span><strong><?php echo htmlspecialchars($res['inv_pay_gateway'] ?? '-'); ?> / <?php echo $res['inv_paid_at'] ?? '未付款'; ?></strong></div>
                              <div class="col-md-4">
                                <span class="text-muted d-block">發票類型與載具</span>
                                <strong>
                                  <?php
                                    if ((int)($res['inv_type'] ?? 1) === 2) {
                                      echo '三聯式統編：' . htmlspecialchars($res['inv_tax_id'] ?? '') . ' (' . htmlspecialchars($res['inv_company_title'] ?? '') . ')';
                                    } elseif ((int)($res['inv_carrier_type'] ?? 0) === 1) {
                                      echo '二聯式手機條碼：' . htmlspecialchars($res['inv_carrier_code'] ?? '');
                                    } elseif ((int)($res['inv_carrier_type'] ?? 0) === 2) {
                                      echo '二聯式自然人憑證：' . htmlspecialchars($res['inv_carrier_code'] ?? '');
                                    } else {
                                      echo '二聯式會員載具（自動歸戶）';
                                    }
                                  ?>
                                </strong>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?php endif; ?>
                    </div>
                    <!-- Tab 3: 專屬通訊通知推播 (fsl_notifications) -->
                    <div class="tab-pane fade" id="tabMenu3" role="tabpanel">
                      <form id="form_send_noti" class="p-3 bg-light border rounded-3 mb-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-send-plus me-1 text-primary"></i>發送專屬一對一訊息至會員中心</h6>
                        <div class="row g-2">
                          <div class="col-md-4">
                            <select class="form-select form-select-sm" id="notiTemplate">
                              <option value="">-- 套用常用服務訊息範本 --</option>
                              <option value="remind">課前提醒與 Zoom 進入連結通知</option>
                              <option value="reschedule">梯次改期 / 延梯完成確認通知</option>
                              <option value="followup">課後關懷追蹤與複習影音提醒</option>
                              <option value="graduate">課程完課結業祝賀與後續服務邀請</option>
                            </select>
                          </div>
                          <div class="col-md-8">
                            <input type="text" class="form-control form-control-sm" id="notiTitle" placeholder="輸入通知標題..." required>
                          </div>
                          <div class="col-12">
                            <textarea class="form-control form-control-sm" id="notiContent" rows="3" placeholder="輸入要推播給學員的詳細內容..." required></textarea>
                          </div>
                          <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-send-fill me-1"></i>立即發送通知</button>
                          </div>
                        </div>
                      </form>

                      <div class="table-responsive">
                        <table id="dataTable_enrnoti" class="table table-striped table-sm table-bordered align-middle w-100 small">
                          <thead class="table-light">
                            <tr>
                              <th style="width: 60px;">ID</th>
                              <th>通知標題與內容摘要</th>
                              <th class="text-center" style="width: 85px;">已讀狀態</th>
                              <th style="width: 145px;">發送時間</th>
                            </tr>
                          </thead>
                          <tbody id="notiTbody">
                            <?php foreach ($notiRows as $nr): ?>
                            <tr>
                              <td><?php echo (int)$nr['no_id']; ?></td>
                              <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($nr['no_title'] ?? ''); ?></div>
                                <div class="text-muted" style="font-size: 0.78rem;"><?php echo nl2br(htmlspecialchars($nr['no_content'] ?? '')); ?></div>
                              </td>
                              <td class="text-center">
                                <?php echo ((int)($nr['no_is_read'] ?? 0) === 1) ? '<span class="badge text-bg-success">已讀</span>' : '<span class="badge text-bg-warning">未讀</span>'; ?>
                              </td>
                              <td class="text-muted"><?php echo $nr['no_created'] ?? '-'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <!-- Tab 4: 課堂出席軌跡與課後關懷服務日誌 -->
                    <div class="tab-pane fade" id="tabMenu4" role="tabpanel">
                      <div class="row g-3 mb-3">
                        <div class="col-md-4">
                          <div class="p-3 border rounded-3 bg-light">
                            <div class="small text-muted">課間簽到時間</div>
                            <div class="fw-bold text-primary mt-1"><?php echo $res['enr_checkin_at'] ?? '尚未簽到'; ?></div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="p-3 border rounded-3 bg-light">
                            <div class="small text-muted">課後簽退時間</div>
                            <div class="fw-bold text-success mt-1"><?php echo $res['enr_checkout_at'] ?? '尚未簽退'; ?></div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="p-3 border rounded-3 bg-light">
                            <div class="small text-muted">最近一次上課/觀看紀錄</div>
                            <div class="fw-bold text-dark mt-1"><?php echo $res['enr_last_watched'] ?? '-'; ?></div>
                          </div>
                        </div>
                      </div>
                      <?php if (!empty($lessonRows)): ?>
                      <div class="card border mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                          <span class="fw-bold small text-dark"><i class="bi bi-calendar2-week me-1 text-primary"></i>本梯次單堂課次排程與 Zoom 連線資訊（共 <?php echo count($lessonRows); ?> 堂）</span>
                          <a href="sess_detail.php?currID=<?php echo $sessID; ?>" class="btn btn-outline-primary btn-xs py-0 px-2">前往梯次課堂中控台</a>
                        </div>
                        <div class="table-responsive">
                          <table class="table table-sm table-bordered align-middle mb-0 small">
                            <thead class="table-light text-nowrap">
                              <tr>
                                <th class="text-center" style="width: 65px;">堂次</th>
                                <th>課程主題</th>
                                <th>上課時段</th>
                                <th class="text-center">上課狀態</th>
                                <th>單堂 Zoom / 地點</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php
                              $lessStBadge = array(
                                0 => '<span class="badge text-bg-secondary">待上課</span>',
                                1 => '<span class="badge text-bg-danger">上課中</span>',
                                2 => '<span class="badge text-bg-success">已完課</span>',
                                3 => '<span class="badge text-bg-warning">已延期</span>'
                              );
                              foreach ($lessonRows as $lr):
                                $lSt = (int)($lr['less_status'] ?? 0);
                              ?>
                              <tr>
                                <td class="text-center fw-bold">第 <?php echo (int)$lr['less_no']; ?> 堂</td>
                                <td>
                                  <div class="fw-semibold text-dark"><?php echo htmlspecialchars($lr['less_title'] ?: '單堂課程'); ?></div>
                                  <?php if (!empty($lr['less_note'])): ?>
                                    <div class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($lr['less_note']); ?></div>
                                  <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                  <?php echo substr((string)$lr['less_start_at'], 0, 16); ?> ~ <?php echo substr((string)$lr['less_end_at'], 11, 5); ?>
                                </td>
                                <td class="text-center"><?php echo $lessStBadge[$lSt] ?? $lessStBadge[0]; ?></td>
                                <td style="font-size: 0.75rem;">
                                  <?php if (!empty($lr['less_location'])): ?>
                                    <div><i class="bi bi-geo-alt text-danger me-1"></i><?php echo htmlspecialchars($lr['less_location']); ?></div>
                                  <?php endif; ?>
                                  <?php if (!empty($lr['less_zoom_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($lr['less_zoom_url']); ?>" target="_blank" class="text-primary text-decoration-none"><i class="bi bi-camera-video me-1"></i>單堂專屬連結</a>
                                  <?php elseif (!empty($res['enr_zoom_url'])): ?>
                                    <span class="text-muted">沿用梯次專屬授權連結</span>
                                  <?php else: ?>
                                    <span class="text-warning-emphasis">尚未派發授權連結</span>
                                  <?php endif; ?>
                                </td>
                              </tr>
                              <?php endforeach; ?>
                            </tbody>
                          </table>
                        </div>
                      </div>
                      <?php endif; ?>

                    <form id="form_tabMenu4" action="../funcFiles/enr_act_func.php" method="post" data-func="func_enr_followupUpdate">
                      <div class="mb-0">
                        <label class="form-label small fw-bold">課後追蹤與顧問後續服務紀錄 (enr_note)</label>
                        <textarea class="form-control" id="enr_note" name="enr_note" rows="5" placeholder="記錄每次課後訪談回饋、學員狀態觀察、轉介下一階段陪跑或一對一顧問建議..."><?php echo htmlspecialchars($res['enr_note'] ?? ''); ?></textarea>
                      </div>
                    </form>
                    </div>
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
    const formEl = $form[0];
    const isValid = formEl.checkValidity();
    formEl.classList.add('was-validated');
    return isValid;
  }

  function doReDirect() { window.history.back(); }

  function doButSave() {
    var frm = $('#form_' + $('.tab-pane.active').prop("id"));
    if (!frm.length) return false;
    if (!validateBS5Form(frm)) {
      window.parent.toastr['warning']('請檢查標示紅框的必填或格式錯誤欄位！');
      return false;
    }
    ajaxPOST(frm);
  }

  function doButDelete() {
    if (0 == parseInt('<?php echo $enrID; ?>')) return false;
    $.post('../funcFiles/enr_act_func.php', {
      'formAcce': 'func_enr_delete',
      'token': getTokenVal(),
      'enrID': '<?php echo $enrID; ?>'
    }, function(data) {
      if (data['RTN'] == 1) {
        window.parent.toastr['success'](data['msgStr']);
        window.location.replace("enr_list.php");
      } else {
        window.parent.toastr['error'](data['msgStr'] || '操作失敗');
      }
    }, 'json');
  }


  function doButReset() { location.reload(true); }

  function ajaxPOST(frm) {
    var $frm = frm || $('#form_tabMenu1');
    var accE = (0 == parseInt('<?php echo $enrID; ?>')) ? 'func_enr_addnew' : $frm.data('func');
    var datA = getFormObjects($frm);

    $.ajax({
      type: 'post',
      url: '../funcFiles/enr_act_func.php',
      data: {
        'arrDatA': datA,
        'formAcce': accE,
        'token': getTokenVal(),
        'enrID': '<?php echo $enrID; ?>'
      },
      dataType: 'json',
      success: function(data) {
        if (accE == 'func_enr_addnew' && data['RTN'] == 1) {
          window.parent.toastr['success'](data['msgStr']);
          window.location.replace("enr_detail.php?currID=" + data['newID']);
          return false;
        }
        if (window.parent && window.parent.toastr) {
          window.parent.toastr[data['msgType']](data['msgStr']);
        }
        if (data['RTN'] == 1) {
          $frm.removeClass('was-validated');
          if ($('#enr_status').length) {
            $('#sideEnrStatus').text($('#enr_status option:selected').text().replace(/^\d+\s*-\s*/, ''));
          }
          if ($('#enr_pay_status').length) {
            $('#sidePayLabel').text($('#enr_pay_status option:selected').text().replace(/^\d+\s*-\s*/, ''));
          }
          if (typeof doSerialize === 'function') doSerialize();
        }
      }
    });
  }

  $(document).ready(function() {
    if (typeof flatpickr !== 'undefined') {
      flatpickr($('input[type="date"]'), { locale: 'zh_tw', dateFormat: 'Y-m-d', allowInput: true, disableMobile: true });
    }
    if ($('#dataTable_enrnoti').length) datatableCreate('dataTable_enrnoti');

    // 新增補登模式：切換課程時過濾對應梯次
    $('#course_id').on('change', function() {
      const cid = $(this).val();
      $('#sess_id option').each(function() {
        const optCid = $(this).attr('data-cid');
        if (!optCid) return;
        $(this).toggle(cid === '' || optCid === cid);
      });
      $('#sess_id').val('');
    });

    // 1. 確認已收款入帳並開立電子發票
    $('#btnConfirmPaid').on('click', function() {
      if (!confirm('確定要將關聯訂單與發票標記為「已收款入帳」，並正式確認該學員修課名額嗎？')) return;
      $.post('../funcFiles/enr_act_func.php', {
        'formAcce': 'func_enr_confirmPaid',
        'token': getTokenVal(),
        'enrID': '<?php echo $enrID; ?>'
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 2. 左側快速發送 Zoom 上課連結
    $('#btnQuickSendZoom').on('click', function() {
      $.post('../funcFiles/enr_act_func.php', {
        'formAcce': 'func_enr_sendZoom',
        'token': getTokenVal(),
        'enrID': '<?php echo $enrID; ?>'
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) {
          $('#sideLinkCnt').text(data['sentCnt'] + ' 次');
          $('input[name="enr_zoom_url"]').val(data['zoomUrl']);
        }
      }, 'json');
    });

    // 3. 一鍵核發完課結業並推播通知
    $('#btnQuickGraduate').on('click', function() {
      if (!confirm('確定核發「已完課結業」狀態並發送結業祝賀通知給該學員嗎？')) return;
      $.post('../funcFiles/enr_act_func.php', {
        'formAcce': 'func_enr_graduate',
        'token': getTokenVal(),
        'enrID': '<?php echo $enrID; ?>'
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 4. 人工確認收款入帳並開立電子發票
    $('#btnConfirmPaid').on('click', function() {
      if (!confirm('確定此筆訂單已收款入帳？系統將同步開立發票並正式計入梯次名額。')) return;
      $.post('../funcFiles/enr_act_func.php', {
        'formAcce': 'func_enr_confirmPaid',
        'token': getTokenVal(),
        'enrID': '<?php echo $enrID; ?>'
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 5. 發送專屬通訊通知推播至會員中心
    $('#form_send_noti').on('submit', function(e) {
      e.preventDefault();
      const title   = $('#notiTitle').val().trim();
      const content = $('#notiContent').val().trim();
      if (!title || !content) {
        window.parent.toastr['warning']('請輸入通知主旨與訊息內容！');
        return;
      }
      $.post('../funcFiles/enr_act_func.php', {
        'formAcce': 'func_enr_sendNoti',
        'token': getTokenVal(),
        'enrID': '<?php echo $enrID; ?>',
        'mbID': '<?php echo $mbID; ?>',
        'noTitle': title,
        'noContent': content
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) {
          $('#notiContent').val('');
          $('#notiHistoryList').prepend(`
            <div class="p-2 border rounded-2 bg-white">
              <div class="d-flex justify-content-between">
                <strong class="text-primary">${$('<div>').text(title).html()}</strong>
                <span class="text-muted">${data['createdAt']}</span>
              </div>
              <div class="text-dark mt-1">${$('<div>').text(content).html().replace(/\n/g, '<br>')}</div>
            </div>
          `);
        }
      }, 'json');
    });

    // 套用常用服務訊息範本
    $('#notiTemplate').on('change', function() {
      const tpl = $(this).val();
      const cName = <?php echo json_encode($res['course_title'] ?? '課程', JSON_UNESCAPED_UNICODE); ?>;
      const bNo   = '<?php echo (int)($res['sess_batch'] ?? 1); ?>';
      const zUrl  = $('input[name="enr_zoom_url"]').val() || '（請至會員中心查看專屬連結）';
      const map = {
        remind: {
          title: `【課前提醒】《${cName}》第 ${bNo} 梯即將開課`,
          body: `親愛的學員您好，提醒您報名的《${cName}》（第 ${bNo} 梯）即將開課，請準時透過您的專屬連結進入教室：\n${zUrl}`
        },
        reschedule: {
          title: `【梯次調度確認】《${cName}》已為您調整至第 ${bNo} 梯`,
          body: `親愛的學員您好，我們已依您的需求協助將《${cName}》調整至第 ${bNo} 梯次，新的上課連結將另行通知派發。`
        },
        followup: {
          title: `【課後關懷與複習提醒】《${cName}》課後影音與講義已上架`,
          body: `親愛的學員您好，感謝您參與《${cName}》課程！課後回放影音與講義已歸檔，歡迎隨時登入「我的專屬課程」複習。`
        },
        graduate: {
          title: `【完課結業祝賀】恭喜圓滿完成《${cName}》`,
          body: `親愛的學員您好，恭喜您完成《${cName}》全部單元！期待在人生第五季的下一段旅程繼續陪伴您。`
        }
      };
      if (map[tpl]) {
        $('#notiTitle').val(map[tpl].title);
        $('#notiContent').val(map[tpl].body);
      }
    });

    // 發送專屬通訊通知推播至會員中心
    $('#form_send_noti').on('submit', function(e) {
      e.preventDefault();
      const title   = $('#notiTitle').val().trim();
      const content = $('#notiContent').val().trim();
      if (!title || !content) {
        window.parent.toastr['warning']('請輸入通知主旨與訊息內容！');
        return;
      }
      $.post('../funcFiles/enr_act_func.php', {
        'formAcce': 'func_enr_sendNoti',
        'token': getTokenVal(),
        'enrID': '<?php echo $enrID; ?>',
        'mbID': '<?php echo $mbID; ?>',
        'noTitle': title,
        'noContent': content
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) {
          $('#notiTitle').val('');
          $('#notiContent').val('');
          $('#notiTbody').prepend(`
            <tr>
              <td>NEW</td>
              <td>
                <div class="fw-bold text-dark">${$('<div>').text(title).html()}</div>
                <div class="text-muted" style="font-size: 0.78rem;">${$('<div>').text(content).html().replace(/\n/g, '<br>')}</div>
              </td>
              <td class="text-center"><span class="badge text-bg-warning">未讀</span></td>
              <td class="text-muted">${data['createdAt']}</td>
            </tr>
          `);
        }
      }, 'json');
    });





    $('li.prevLink').on('click', function() { window.location.replace(getCurrentURI('', 'enr_list.php')); });
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
