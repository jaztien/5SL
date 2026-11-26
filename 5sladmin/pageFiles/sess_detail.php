<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 定義本頁面代碼
define('PAGE_MARK', 'PG_SESS');
define('PAGE_ITEM', 'ITEM_COURSE');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$sessID = currID;
$res    = ($sessID > 0) ? DB->row("SELECT * FROM fsl_course_sessions WHERE sess_id = ? AND sess_status != 9", array($sessID)) : array();
if (!is_array($res)) $res = array();

$courseID = (int)($res['course_id'] ?? 0);

// 取得所有主課程（供選擇所屬課程）
$masterCourses = DB->query("SELECT course_id, course_title, course_max FROM fsl_courses WHERE parent_id = 0 AND course_status != 9 ORDER BY course_sort ASC, course_id ASC");
if (!is_array($masterCourses)) $masterCourses = array();

$courseTitle = '尚未指定主課程';
$courseMax   = 20;
foreach ($masterCourses as $mc) {
  if ((int)$mc['course_id'] === $courseID) {
    $courseTitle = $mc['course_title'];
    $courseMax   = (int)$mc['course_max'];
    break;
  }
}

// 解析週間上課日陣列 (0-6)
$onWeekArr = (!empty($res['sess_onweek'])) ? array_map('trim', explode(',', $res['sess_onweek'])) : array();

// 查詢資料：單堂課次、學員名單、多筆媒體附件
$lessonRows  = array();
$enrollRows  = array();
$mediaRows   = array();
$checkinCnt  = 0;
$checkoutCnt = 0;
$linkSentCnt = 0;

if ($sessID > 0) {
  // 1. 查詢本梯次所有單堂課次 (fsl_course_lessons)
  $lessonRows = DB->query("
    SELECT *
    FROM fsl_course_lessons
    WHERE sess_id = ? AND less_status != 9
    ORDER BY less_no ASC, less_start_at ASC, less_id ASC
  ", array($sessID));
  if (!is_array($lessonRows)) $lessonRows = array();

  // 2. 查詢本梯次參與學員名單
  $enrollRows = DB->query("
    SELECT e.*,
           m.mb_name, m.mb_email, m.mb_cellphone, m.mb_title,
           o.ord_no, o.ord_total
    FROM fsl_course_enrolls e
    LEFT JOIN fsl_members m ON e.mb_id = m.mb_id
    LEFT JOIN fsl_orders o ON e.ord_id = o.ord_id
    WHERE e.sess_id = ?
    ORDER BY e.enr_status ASC, e.enr_id ASC
  ", array($sessID));
  if (!is_array($enrollRows)) $enrollRows = array();

  foreach ($enrollRows as $er) {
    $att = (int)($er['enr_attend_status'] ?? 0);
    if (in_array($att, array(1, 2), true)) $checkinCnt++;
    if ($att === 2) $checkoutCnt++;
    if (!empty($er['enr_link_sent_at'])) $linkSentCnt++;
  }

  // 3. 查詢本梯次及所屬單堂之多筆錄影與講義 (fsl_media)
  $lessIds = array_column($lessonRows, 'less_id');
  if (!empty($lessIds)) {
    $inPlaceholders = implode(',', array_fill(0, count($lessIds), '?'));
    $medParams = array_merge(array($sessID), $lessIds);
    $mediaRows = DB->query("
      SELECT *
      FROM fsl_media
      WHERE med_status != 9
        AND ((med_for = 'session' AND med_forid = ?) OR (med_for = 'lesson' AND med_forid IN ({$inPlaceholders})))
      ORDER BY med_sort ASC, med_id DESC
    ", $medParams);
  } else {
    $mediaRows = DB->query("
      SELECT *
      FROM fsl_media
      WHERE med_status != 9 AND med_for = 'session' AND med_forid = ?
      ORDER BY med_sort ASC, med_id DESC
    ", array($sessID));
  }
  if (!is_array($mediaRows)) $mediaRows = array();
}

// 建立課堂 ID 對應堂次名稱的查詢表（供 Tab 4 顯示歸屬堂次）
$lessonTitleMap = array();
foreach ($lessonRows as $lr) {
  $lessonTitleMap[(int)$lr['less_id']] = '第 ' . (int)$lr['less_no'] . ' 堂' . (!empty($lr['less_title']) ? '：' . $lr['less_title'] : '');
}

$liveLabels = array(
  0 => array('text' => '尚未開課', 'cls' => 'text-secondary'),
  1 => array('text' => 'Zoom 課間進行中 (LIVE)', 'cls' => 'text-danger fw-bold'),
  2 => array('text' => '已結課歸檔', 'cls' => 'text-success fw-bold')
);
$currLive = (int)($res['sess_live_status'] ?? 0);
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
  <title>Fifth Season Life 人生第五季 | 課程梯次與課堂中控台</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../pluginFiles/flatpickr/flatpickr.min.css">
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
                <?php echo !empty($sessID) ? "課堂中控與梯次管理" : "新增開課梯次"; ?>
                <span class="fs-5 text-info">【ID: <?php echo $sessID; ?>】</span>
              </h4>
              <h6 class="m-0 fw-light text-muted fs-7 mt-1 lh-xs mb-n2"><i class="bi bi-arrow-return-right"></i> Zoom 會議間建立與即時狀態、單堂課次排程與報告、學員授權連結派發與課後影音講義</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">課程管理系統</li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回列表">課程梯次列表</li>
                <li class="breadcrumb-item active"><?php echo !empty($sessID) ? "課堂中控台" : "新增梯次"; ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <!-- 左側課堂即時狀態與 Zoom 快速控制台 -->
            <div class="col-md-3 mb-3">
              <div class="card card-info card-outline shadow-lg">
                <div class="card-body">
                  <div class="text-center mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-info-subtle text-info mb-2" style="width: 68px; height: 68px;">
                      <i class="bi bi-camera-video-fill fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1" id="sideSessTitle">
                      <?php echo ($sessID > 0) ? ('第 ' . (int)$res['sess_batch'] . ' 梯次') : '新建立梯次'; ?>
                    </h5>
                    <div class="small text-muted" id="sideCourseName"><?php echo htmlspecialchars($courseTitle); ?></div>
                  </div>

                  <!-- 課間即時狀態快速切換器 -->
                  <?php if ($sessID > 0): ?>
                  <div class="p-2 bg-light border rounded-3 mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">即時課間狀態控制</label>
                    <div class="btn-group w-100 btn-group-sm" role="group">
                      <button type="button" class="btn <?php echo ($currLive === 0) ? 'btn-secondary' : 'btn-outline-secondary'; ?> btn-live-switch" data-live="0">未開課</button>
                      <button type="button" class="btn <?php echo ($currLive === 1) ? 'btn-danger' : 'btn-outline-danger'; ?> btn-live-switch" data-live="1">LIVE 課中</button>
                      <button type="button" class="btn <?php echo ($currLive === 2) ? 'btn-success' : 'btn-outline-success'; ?> btn-live-switch" data-live="2">已結課</button>
                    </div>
                  </div>

                  <?php if (!empty($res['sess_start_url'])): ?>
                    <div class="d-grid mb-3">
                      <a href="<?php echo htmlspecialchars($res['sess_start_url']); ?>" target="_blank" class="btn btn-primary btn-sm fw-bold">
                        <i class="bi bi-box-arrow-up-right me-1"></i>開啟 Zoom 主持人會議室
                      </a>
                    </div>
                  <?php endif; ?>
                  <?php endif; ?>

                  <ul class="list-group list-group-flush small">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>課間狀態</b>
                      <span id="sideLiveTxt" class="<?php echo $liveLabels[$currLive]['cls']; ?>"><?php echo $liveLabels[$currLive]['text']; ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>Zoom 會議號</b>
                      <span class="fw-bold text-primary" id="sideMeetingId"><?php echo !empty($res['sess_meetingid']) ? htmlspecialchars($res['sess_meetingid']) : '尚未建立'; ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>會議密碼</b>
                      <code id="sidePasscode"><?php echo !empty($res['sess_passcode']) ? htmlspecialchars($res['sess_passcode']) : '-'; ?></code>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>展開單堂數</b>
                      <span class="fw-bold"><?php echo count($lessonRows); ?> 堂</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>有效學員 / 上限</b>
                      <span><?php echo count($enrollRows); ?> / <?php echo $courseMax; ?> 人</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>已派發連結</b>
                      <span class="text-info fw-bold" id="sideSentCnt"><?php echo $linkSentCnt; ?> 人</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>已簽到 / 已簽退</b>
                      <span class="text-success fw-bold" id="sideAttendCnt"><?php echo $checkinCnt; ?> / <?php echo $checkoutCnt; ?> 人</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>最後更新</b>
                      <span class="text-muted" id="sideSessUpdated"><?php echo $res['sess_updated'] ?? '-'; ?></span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- 右側多分頁課堂中控台 -->
            <div class="col-md-9">
              <div class="card shadow-lg modern-card-tabs">
                <div class="card-header p-2">
                  <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                      <a class="nav-link active" href="#tabMenu1" data-index="1" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-camera-video me-1"></i>課程梯次設定
                      </a>
                    </li>
                    <?php if ($sessID > 0): ?>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu2" data-index="2" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-calendar2-week me-1"></i>課堂排課安排 (<?php echo count($lessonRows); ?>)
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu3" data-index="3" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-person-check me-1"></i>授權與點名簽退 (<?php echo count($enrollRows); ?>)
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu4" data-index="4" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-film me-1"></i>課後管理 (<?php echo count($mediaRows); ?>)
                      </a>
                    </li>
                    <?php endif; ?>
                  </ul>
                </div>

                <div class="card-body bg-opacity-50">
                  <div class="tab-content">
                    <!-- Tab 1: 梯次基本排程、Zoom 會議間建立與安全設定 -->
                    <div class="tab-pane fade show active" id="tabMenu1" role="tabpanel">
                      <form id="form_tabMenu1" action="../funcFiles/sess_act_func.php" method="post" data-func="func_sess_infoUpdate" novalidate>
                        <div class="row g-3">
                          <div class="col-md-5">
                            <label class="form-label small fw-bold">所屬主課程 <span class="text-danger">*</span></label>
                            <select class="form-select" id="course_id" name="course_id" required>
                              <option value="">-- 請選擇主課程 --</option>
                              <?php foreach ($masterCourses as $mc): ?>
                                <option value="<?php echo (int)$mc['course_id']; ?>" <?php echo ($courseID === (int)$mc['course_id']) ? 'selected' : ''; ?>>
                                  #<?php echo (int)$mc['course_id']; ?> - <?php echo htmlspecialchars($mc['course_title']); ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="col-md-2">
                            <label class="form-label small fw-bold">梯次編號 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="sess_batch" name="sess_batch" min="1" required
                                   value="<?php echo (int)($res['sess_batch'] ?? 1); ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">梯次識別代碼</label>
                            <input type="text" class="form-control text-uppercase" name="sess_keycode"
                                   value="<?php echo htmlspecialchars($res['sess_keycode'] ?? ''); ?>" placeholder="如：FSL26-B01">
                          </div>
                          <div class="col-md-2">
                            <label class="form-label small fw-bold">報名開放狀態</label>
                            <select class="form-select" name="sess_status">
                              <option value="1" <?php echo ((int)($res['sess_status'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 開放報名</option>
                              <option value="0" <?php echo ((int)($res['sess_status'] ?? 1) === 0) ? 'selected' : ''; ?>>0 - 關閉/額滿</option>
                            </select>
                          </div>

                          <div class="col-md-6">
                            <label class="form-label small fw-bold">梯次專屬別名 (sess_altname)</label>
                            <input type="text" class="form-control" name="sess_altname"
                                   value="<?php echo htmlspecialchars($res['sess_altname'] ?? ''); ?>" placeholder="例如：2026 冬季平日晚間班">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label small fw-bold">梯次副標說明 (sess_subscript)</label>
                            <input type="text" class="form-control" name="sess_subscript"
                                   value="<?php echo htmlspecialchars($res['sess_subscript'] ?? ''); ?>" placeholder="例如：每週三晚間 19:30 線上直播陪跑">
                          </div>

                          <div class="col-md-3">
                            <label class="form-label small fw-bold">首堂開課日期 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="sess_startdate" required
                                   value="<?php echo htmlspecialchars($res['sess_startdate'] ?? date('Y-m-d', strtotime('+7 days'))); ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">首堂開課時間 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="sess_starttime" required
                                   value="<?php echo htmlspecialchars($res['sess_starttime'] ?? '19:30:00'); ?>" placeholder="19:30:00">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">總堂數</label>
                            <input type="number" class="form-control" name="sess_lesson" min="1"
                                   value="<?php echo (int)($res['sess_lesson'] ?? 12); ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">單堂時長 (分鐘)</label>
                            <input type="number" class="form-control" name="sess_duration" min="15" step="5"
                                   value="<?php echo (int)($res['sess_duration'] ?? 90); ?>">
                          </div>

                          <!-- 週間固定上課日多選 (sess_onweek: 0~6) -->
                          <div class="col-12">
                            <label class="form-label small fw-bold d-block">週間固定上課日（多堂課時每週同一時段上課日）</label>
                            <div class="d-flex flex-wrap gap-3 p-2 bg-light border rounded-2">
                              <?php
                              $wLabels = array('1' => '週一', '2' => '週二', '3' => '週三', '4' => '週四', '5' => '週五', '6' => '週六', '0' => '週日');
                              foreach ($wLabels as $wKey => $wName):
                                $chk = in_array((string)$wKey, $onWeekArr, true) ? 'checked' : '';
                              ?>
                              <div class="form-check mb-0">
                                <input class="form-check-input chk-onweek" type="checkbox" value="<?php echo $wKey; ?>" id="wk_<?php echo $wKey; ?>" name="wk_<?php echo $wKey; ?>" <?php echo $chk; ?>>
                                <label class="form-check-label small" for="wk_<?php echo $wKey; ?>"><?php echo $wName; ?></label>
                              </div>
                              <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="sess_onweek" id="sess_onweek" value="<?php echo htmlspecialchars($res['sess_onweek'] ?? ''); ?>">
                          </div>

                          <div class="col-12"><div class="hr-more">Zoom 會議間建立與連線憑證</div><hr class="my-1"></div>

                          <div class="col-md-4">
                            <label class="form-label small fw-bold">Zoom 會議室 ID (sess_meetingid)</label>
                            <div class="input-group">
                              <input type="text" class="form-control" id="sess_meetingid" name="sess_meetingid"
                                     value="<?php echo htmlspecialchars($res['sess_meetingid'] ?? ''); ?>" placeholder="例如：892 1045 6678">
                              <button type="button" class="btn btn-outline-primary btn-sm" id="btnAutoZoomRoom" title="一鍵建立/產生 Zoom 會議間憑證">
                                <i class="bi bi-magic"></i> 產生
                              </button>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">Zoom 會議室密碼 (sess_passcode)</label>
                            <input type="text" class="form-control" id="sess_passcode" name="sess_passcode"
                                   value="<?php echo htmlspecialchars($res['sess_passcode'] ?? ''); ?>" placeholder="6 碼英數字密碼">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">梯次會議模式 (sess_batchmode)</label>
                            <select class="form-select" name="sess_batchmode">
                              <option value="1" <?php echo ((int)($res['sess_batchmode'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - Zoom 線上互動直播室</option>
                              <option value="2" <?php echo ((int)($res['sess_batchmode'] ?? 1) === 2) ? 'selected' : ''; ?>>2 - 線上 + 實體混合同步</option>
                              <option value="0" <?php echo ((int)($res['sess_batchmode'] ?? 1) === 0) ? 'selected' : ''; ?>>0 - 純實體教室授課</option>
                            </select>
                          </div>

                          <div class="col-md-6">
                            <label class="form-label small fw-bold">學員通用加入連結 (sess_join_url)</label>
                            <input type="url" class="form-control" id="sess_join_url" name="sess_join_url"
                                   value="<?php echo htmlspecialchars($res['sess_join_url'] ?? ''); ?>" placeholder="https://zoom.us...">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label small fw-bold">講師主持人啟動連結 (sess_start_url)</label>
                            <input type="url" class="form-control" id="sess_start_url" name="sess_start_url"
                                   value="<?php echo htmlspecialchars($res['sess_start_url'] ?? ''); ?>" placeholder="https://zoom.us...">
                          </div>

                          <div class="col-12"><div class="hr-more">Zoom 會議室安全限制與課間預設參數</div><hr class="my-1"></div>

                          <div class="col-md-4">
                            <div class="form-check form-switch">
                              <input class="form-check-input" type="checkbox" id="sess_secured" name="sess_secured" data-unchecked="0" value="1" <?php echo !empty($res['sess_secured']) ? 'checked' : ''; ?>>
                              <label class="form-check-label small fw-bold" for="sess_secured">安全限制模式 (等候室驗證)</label>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-check form-switch">
                              <input class="form-check-input" type="checkbox" id="sess_approval" name="sess_approval" data-unchecked="0" value="1" <?php echo !empty($res['sess_approval']) ? 'checked' : ''; ?>>
                              <label class="form-check-label small fw-bold" for="sess_approval">預先註冊審核模式</label>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-check form-switch">
                              <input class="form-check-input" type="checkbox" id="sess_beforehost" name="sess_beforehost" data-unchecked="0" value="1" <?php echo !empty($res['sess_beforehost']) ? 'checked' : ''; ?>>
                              <label class="form-check-label small fw-bold" for="sess_beforehost">允許學員提前進入教室</label>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-check form-switch">
                              <input class="form-check-input" type="checkbox" id="sess_watermark" name="sess_watermark" data-unchecked="0" value="1" <?php echo !empty($res['sess_watermark']) ? 'checked' : ''; ?>>
                              <label class="form-check-label small fw-bold" for="sess_watermark">開啟電子郵件浮水印防錄</label>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-check form-switch">
                              <input class="form-check-input" type="checkbox" id="sess_muteentry" name="sess_muteentry" data-unchecked="0" value="1" <?php echo !empty($res['sess_muteentry']) ? 'checked' : ''; ?>>
                              <label class="form-check-label small fw-bold" for="sess_muteentry">學員進入時麥克風預設靜音</label>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="input-group input-group-sm">
                              <span class="input-group-text">提前進入時間</span>
                              <input type="number" class="form-control" name="sess_jbhtime" min="0" max="60" value="<?php echo (int)($res['sess_jbhtime'] ?? 10); ?>">
                              <span class="input-group-text">分鐘</span>
                            </div>
                          </div>
                        </div>
                      </form>
                    </div>

                    <?php if ($sessID > 0): ?>
                    <!-- Tab 2: 單堂課次排程與 Zoom 終結報告列表 -->
                    <div class="tab-pane fade" id="tabMenu2" role="tabpanel">
                      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-light border rounded-3 mb-3">
                        <div class="small text-muted">
                          <i class="bi bi-calendar2-check-fill text-info me-1"></i>
                          修改單堂上課時間後，系統將自動同步順延 <code>fsl_schedules</code> 中對應的課前自動提醒排程。
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm fw-bold" id="btnRegenLessons">
                          <i class="bi bi-arrow-repeat me-1"></i>依梯次規則重新展開課次
                        </button>
                      </div>

                      <div class="table-responsive">
                        <table id="dataTable_sesslessons" class="table table-striped table-sm table-bordered table-hover align-middle w-100 small" data-search="1">
                          <thead class="table-light text-nowrap">
                            <tr>
                              <th class="text-center" style="width: 65px;">堂次</th>
                              <th>單堂主題 / 地點與 Zoom 資訊</th>
                              <th>起訖時間</th>
                              <th class="text-center" style="width: 135px;">上課狀態</th>
                              <th class="text-center" style="width: 185px;">課堂與報告操作</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($lessonRows as $lr):
                              $lid      = (int)$lr['less_id'];
                              $lStatus  = (int)($lr['less_status'] ?? 0);
                              $hasRpt   = !empty(trim((string)($lr['less_zoom_report'] ?? '')));
                              $lessJson = htmlspecialchars(json_encode($lr, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                            ?>
                            <tr id="lessRow_<?php echo $lid; ?>">
                              <td class="text-center fw-bold">
                                <span class="badge text-bg-dark">第 <?php echo (int)$lr['less_no']; ?> 堂</span>
                              </td>
                              <td>
                                <div class="fw-bold text-dark" id="lessTitle_<?php echo $lid; ?>"><?php echo htmlspecialchars($lr['less_title'] ?: ('第 ' . (int)$lr['less_no'] . ' 堂課程')); ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;" id="lessMeta_<?php echo $lid; ?>">
                                  <?php if (!empty($lr['less_location'])): ?>
                                    <span class="me-2"><i class="bi bi-geo-alt text-danger"></i> <?php echo htmlspecialchars($lr['less_location']); ?></span>
                                  <?php endif; ?>
                                  <?php if (!empty($lr['less_zoom_id'])): ?>
                                    <span class="text-primary"><i class="bi bi-camera-video"></i> ID: <?php echo htmlspecialchars($lr['less_zoom_id']); ?></span>
                                  <?php else: ?>
                                    <span>沿用梯次預設 Zoom 會議室</span>
                                  <?php endif; ?>
                                </div>
                                <?php if (!empty($lr['less_note'])): ?>
                                  <div class="text-secondary fst-italic mt-1" style="font-size: 0.74rem;" id="lessNote_<?php echo $lid; ?>">備註：<?php echo htmlspecialchars($lr['less_note']); ?></div>
                                <?php endif; ?>
                              </td>
                              <td class="text-nowrap" id="lessTime_<?php echo $lid; ?>">
                                <div class="fw-semibold"><?php echo substr((string)$lr['less_start_at'], 0, 16); ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">至 <?php echo substr((string)$lr['less_end_at'], 11, 5); ?></div>
                              </td>
                              <td class="text-center">
                                <select class="form-select form-select-sm sel-lesson-status" data-lid="<?php echo $lid; ?>">
                                  <option value="0" <?php echo ($lStatus === 0) ? 'selected' : ''; ?>>0 - 待上課</option>
                                  <option value="1" <?php echo ($lStatus === 1) ? 'selected' : ''; ?>>1 - 上課中</option>
                                  <option value="2" <?php echo ($lStatus === 2) ? 'selected' : ''; ?>>2 - 已完課</option>
                                  <option value="3" <?php echo ($lStatus === 3) ? 'selected' : ''; ?>>3 - 已延期</option>
                                </select>
                              </td>
                              <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-outline-primary btn-xs py-1 px-2 btn-open-edit-lesson" data-lesson='<?php echo $lessJson; ?>'>
                                  <i class="bi bi-pencil-square me-1"></i>編輯課堂
                                </button>
                                <button type="button" class="btn <?php echo $hasRpt ? 'btn-success' : 'btn-outline-secondary'; ?> btn-xs py-1 px-2 btn-open-zoom-report" data-lesson='<?php echo $lessJson; ?>'>
                                  <i class="bi bi-file-earmark-bar-graph me-1"></i><?php echo $hasRpt ? '已存報告' : 'Zoom 報告'; ?>
                                </button>
                              </td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <!-- Tab 3: 學員 Zoom 授權連結派發與課間點名簽到簽退 -->
                    <div class="tab-pane fade" id="tabMenu3" role="tabpanel">
                      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-light border rounded-3 mb-3">
                        <div class="small text-muted">
                          <i class="bi bi-info-circle-fill text-info me-1"></i>
                          發送授權連結後，系統將自動為學員產生專屬進入憑證並推播至會員通知中心（<code>fsl_notifications</code>）。
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                          <button type="button" class="btn btn-primary btn-sm fw-bold" id="btnBatchSendZoomLink">
                            <i class="bi bi-send-check-fill me-1"></i>一鍵群發 Zoom 授權連結給全體學員
                          </button>
                          <button type="button" class="btn btn-outline-success btn-sm fw-bold" id="btnBatchCheckoutAll">
                            <i class="bi bi-check2-circle me-1"></i>已簽到學員一鍵全體簽退
                          </button>
                        </div>
                      </div>

                      <div class="table-responsive">
                        <table id="dataTable_sessenroll" class="table table-striped table-sm table-bordered table-hover align-middle w-100 small" data-search="1">
                          <thead class="table-light text-nowrap">
                            <tr>
                              <th>ID</th>
                              <th>學員姓名 / 信箱</th>
                              <th>聯絡手機</th>
                              <th class="text-center">授權連結派發</th>
                              <th class="text-center">課間點名簽到 / 簽退</th>
                              <th>點名時間與備註</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            $attendBadges = array(
                              0 => '<span class="badge text-bg-secondary">未簽到</span>',
                              1 => '<span class="badge text-bg-primary">已簽到上課中</span>',
                              2 => '<span class="badge text-bg-success">已完成簽退</span>',
                              3 => '<span class="badge text-bg-warning">請假</span>',
                              4 => '<span class="badge text-bg-danger">缺席</span>'
                            );
                            foreach ($enrollRows as $er):
                              $eid     = (int)$er['enr_id'];
                              $decName = !empty($er['mb_name']) ? _fn_secure_crypto($er['mb_name'], 'de') : '未填寫';
                              $decMail = !empty($er['mb_email']) ? _fn_secure_crypto($er['mb_email'], 'de') : '';
                              $decTel  = !empty($er['mb_cellphone']) ? _fn_secure_crypto($er['mb_cellphone'], 'de') : '-';
                              $attSta  = (int)($er['enr_attend_status'] ?? 0);
                              $sentCnt = (int)($er['enr_link_sent_cnt'] ?? 0);
                            ?>
                            <tr id="enrRow_<?php echo $eid; ?>">
                              <td class="fw-bold"><?php echo $eid; ?></td>
                              <td>
                                <a href="mb_detail.php?currID=<?php echo (int)$er['mb_id']; ?>" class="fw-bold text-decoration-none"><?php echo htmlspecialchars($decName); ?></a>
                                <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($decMail); ?></div>
                              </td>
                              <td><?php echo htmlspecialchars($decTel); ?></td>
                              <td class="text-center">
                                <button type="button" class="btn btn-outline-primary btn-xs py-0 px-2 btn-send-single-link" data-eid="<?php echo $eid; ?>">
                                  <i class="bi bi-send me-1"></i><?php echo ($sentCnt > 0) ? "補發 ({$sentCnt})" : "發送連結"; ?>
                                </button>
                                <?php if (!empty($er['enr_link_sent_at'])): ?>
                                  <div class="text-muted mt-1" style="font-size: 0.7rem;" id="sentTime_<?php echo $eid; ?>"><?php echo substr($er['enr_link_sent_at'], 5, 11); ?></div>
                                <?php else: ?>
                                  <div class="text-muted mt-1" style="font-size: 0.7rem;" id="sentTime_<?php echo $eid; ?>">尚未派發</div>
                                <?php endif; ?>
                              </td>
                              <td class="text-center">
                                <div class="mb-1" id="attBadge_<?php echo $eid; ?>"><?php echo $attendBadges[$attSta] ?? $attendBadges[0]; ?></div>
                                <div class="btn-group btn-group-sm" role="group">
                                  <button type="button" class="btn btn-outline-primary btn-xs py-0 px-2 btn-attend-act" data-eid="<?php echo $eid; ?>" data-act="checkin" title="課間簽到">簽到</button>
                                  <button type="button" class="btn btn-outline-success btn-xs py-0 px-2 btn-attend-act" data-eid="<?php echo $eid; ?>" data-act="checkout" title="課後簽退">簽退</button>
                                  <button type="button" class="btn btn-outline-warning btn-xs py-0 px-2 btn-attend-act" data-eid="<?php echo $eid; ?>" data-act="leave" title="標記請假">請假</button>
                                  <button type="button" class="btn btn-outline-danger btn-xs py-0 px-2 btn-attend-act" data-eid="<?php echo $eid; ?>" data-act="absent" title="標記缺席">缺席</button>
                                </div>
                              </td>
                              <td style="font-size: 0.75rem;" id="attTimeCell_<?php echo $eid; ?>">
                                <div>簽到：<?php echo !empty($er['enr_checkin_at']) ? substr($er['enr_checkin_at'], 5, 11) : '-'; ?></div>
                                <div>簽退：<?php echo !empty($er['enr_checkout_at']) ? substr($er['enr_checkout_at'], 5, 11) : '-'; ?></div>
                              </td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <!-- Tab 4: 課後錄影回放與講義歸檔（多筆 fsl_media 管理） -->
                    <div class="tab-pane fade" id="tabMenu4" role="tabpanel">
                      <form id="form_tabMenu4" action="../funcFiles/sess_act_func.php" method="post" data-func="func_sess_recordingUpdate" class="mb-4">
                        <div class="row g-3">
                          <div class="col-12">
                            <div class="alert alert-success-subtle border border-success-subtle small py-2 mb-0">
                              <i class="bi bi-collection-play-fill text-success me-1"></i>
                              本區支援為「整個梯次」或「指定單堂課次」掛載多支雲端錄影回放與多份 PDF 講義（寫入全站檔案庫 <code>fsl_media</code>，並自動限制僅限本課程已購學員觀看/下載）。
                            </div>
                          </div>
                          <div class="col-md-12">
                            <label class="form-label small fw-bold">梯次課後總結重點摘要與作業提醒 (sess_brief)</label>
                            <textarea class="form-control" name="sess_brief" rows="2" placeholder="輸入本梯次課後複習指引或作業說明（點擊上方「儲存」按鈕可單獨更新此摘要）..."><?php echo htmlspecialchars($res['sess_brief'] ?? ''); ?></textarea>
                          </div>
                        </div>
                      </form>

                      <div class="card border-info mb-4">
                        <div class="card-header bg-info-subtle text-info-emphasis fw-bold small py-2">
                          <i class="bi bi-plus-circle-fill me-1"></i>新增課後錄影回放或講義附件至檔案庫 (fsl_media)
                        </div>
                        <div class="card-body bg-light">
                          <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                              <label class="form-label small fw-bold mb-1">掛載歸屬範圍</label>
                              <select class="form-select form-select-sm" id="new_med_target">
                                <option value="session:<?php echo $sessID; ?>">全梯次共用 (第 <?php echo (int)$res['sess_batch']; ?> 梯)</option>
                                <?php foreach ($lessonRows as $lr): ?>
                                  <option value="lesson:<?php echo (int)$lr['less_id']; ?>">第 <?php echo (int)$lr['less_no']; ?> 堂 - <?php echo htmlspecialchars($lr['less_title'] ?: '單堂課次'); ?></option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                            <div class="col-md-2">
                              <label class="form-label small fw-bold mb-1">資源類型</label>
                              <select class="form-select form-select-sm" id="new_med_type">
                                <option value="2">2 - 雲端錄影回放</option>
                                <option value="4">4 - 講義/學習單 (PDF)</option>
                                <option value="3">3 - 課堂音頻錄音</option>
                              </select>
                            </div>
                            <div class="col-md-4">
                              <label class="form-label small fw-bold mb-1">資源名稱 / 標題 <span class="text-danger">*</span></label>
                              <input type="text" class="form-control form-control-sm" id="new_med_title" placeholder="例如：第1堂 完整回放錄影 (上集)">
                            </div>
                            <div class="col-md-3">
                              <label class="form-label small fw-bold mb-1">排序 / 密碼或時長備註</label>
                              <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="new_med_sort" value="100" title="排序(越小越靠前)" style="max-width: 70px;">
                                <input type="text" class="form-control" id="new_med_brief" placeholder="密碼或片長說明">
                              </div>
                            </div>
                            <div class="col-md-9">
                              <label class="form-label small fw-bold mb-1">雲端連結網址 (YouTube / Zoom Cloud / Google Drive) <span class="text-danger">*</span></label>
                              <input type="url" class="form-control form-control-sm" id="new_med_url" placeholder="https://...">
                            </div>
                            <div class="col-md-3 d-grid">
                              <button type="button" class="btn btn-info btn-sm text-white fw-bold" id="btnAddSessMedia">
                                <i class="bi bi-cloud-plus me-1"></i>加入檔案庫清單
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="table-responsive">
                        <table id="table_sess_media" class="table table-bordered table-hover table-sm align-middle small w-100">
                          <thead class="table-light text-nowrap">
                            <tr>
                              <th class="text-center" style="width: 75px;">排序</th>
                              <th>歸屬堂次</th>
                              <th>類型</th>
                              <th>標題與密碼/備註</th>
                              <th>連結預覽</th>
                              <th class="text-center" style="width: 90px;">操作</th>
                            </tr>
                          </thead>
                          <tbody id="tbody_sess_media">
                            <?php
                            $medTypeBadge = array(
                              2 => '<span class="badge text-bg-danger"><i class="bi bi-play-btn me-1"></i>錄影回放</span>',
                              3 => '<span class="badge text-bg-warning"><i class="bi bi-mic me-1"></i>課堂音頻</span>',
                              4 => '<span class="badge text-bg-primary"><i class="bi bi-file-earmark-pdf me-1"></i>課程講義</span>',
                              1 => '<span class="badge text-bg-secondary">圖檔</span>'
                            );
                            if (empty($mediaRows)):
                            ?>
                            <tr id="emptyMediaRow"><td colspan="6" class="text-center text-muted py-3">尚未掛載任何課後錄影或講義檔案</td></tr>
                            <?php else: foreach ($mediaRows as $mr):
                              $mid = (int)$mr['med_id'];
                              $mFor = $mr['med_for'];
                              $mForId = (int)$mr['med_forid'];
                              $scopeLabel = ($mFor === 'lesson' && isset($lessonTitleMap[$mForId]))
                                ? '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">' . htmlspecialchars($lessonTitleMap[$mForId]) . '</span>'
                                : '<span class="badge bg-secondary-subtle text-secondary-emphasis border">全梯次共用</span>';
                            ?>
                            <tr id="medRow_<?php echo $mid; ?>">
                              <td class="text-center">
                                <input type="number" class="form-control form-control-sm text-center inp-media-sort" data-mid="<?php echo $mid; ?>" value="<?php echo (int)$mr['med_sort']; ?>">
                              </td>
                              <td><?php echo $scopeLabel; ?></td>
                              <td><?php echo $medTypeBadge[(int)$mr['med_type']] ?? '<span class="badge text-bg-secondary">檔案</span>'; ?></td>
                              <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($mr['med_title']); ?></div>
                                <?php if (!empty($mr['med_brief'])): ?>
                                  <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($mr['med_brief']); ?></div>
                                <?php endif; ?>
                              </td>
                              <td class="text-truncate" style="max-width: 220px;">
                                <a href="<?php echo htmlspecialchars($mr['med_url']); ?>" target="_blank" class="text-decoration-none">
                                  <i class="bi bi-box-arrow-up-right me-1"></i>開啟連結
                                </a>
                              </td>
                              <td class="text-center">
                                <button type="button" class="btn btn-outline-danger btn-xs py-0 px-2 btn-del-sess-media" data-mid="<?php echo $mid; ?>">
                                  <i class="bi bi-trash"></i> 移除
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

  <?php if ($sessID > 0): ?>
  <!-- Modal 1: 編輯單堂課次排程與專屬 Zoom 設定 (#modalEditLesson) -->
  <div class="modal fade" id="modalEditLesson" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white py-2">
          <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-1"></i>編輯單堂課次排程與專屬教室設定</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_less_id" value="0">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label small fw-bold">堂次編號</label>
              <input type="number" class="form-control form-control-sm" id="edit_less_no" min="1" readonly>
            </div>
            <div class="col-md-9">
              <label class="form-label small fw-bold">單堂課程主題名稱 <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" id="edit_less_title" placeholder="例如：第一週：內在覺察與靜心引導">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">開始日期時間 <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm flatpickr-datetime" id="edit_less_start_at" placeholder="YYYY-MM-DD HH:mm:ss">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">結束日期時間 <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm flatpickr-datetime" id="edit_less_end_at" placeholder="YYYY-MM-DD HH:mm:ss">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">實體上課地點（混合或實體課填寫）</label>
              <input type="text" class="form-control form-control-sm" id="edit_less_location" placeholder="線上直播可留空">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">單堂上課狀態</label>
              <select class="form-select form-select-sm" id="edit_less_status">
                <option value="0">0 - 待上課</option>
                <option value="1">1 - 上課中</option>
                <option value="2">2 - 已完課</option>
                <option value="3">3 - 已延期</option>
              </select>
            </div>
            <div class="col-12"><hr class="my-1"><div class="small fw-bold text-primary">單堂專屬 Zoom 覆寫設定（留空則自動沿用梯次預設 Zoom 會議室）</div></div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">單堂 Zoom 會議室 ID</label>
              <input type="text" class="form-control form-control-sm" id="edit_less_zoom_id" placeholder="留空沿用梯次設定">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">單堂 Zoom 密碼</label>
              <input type="text" class="form-control form-control-sm" id="edit_less_zoom_pass" placeholder="留空沿用梯次設定">
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold">單堂專屬加入連結 (less_zoom_url)</label>
              <input type="url" class="form-control form-control-sm" id="edit_less_zoom_url" placeholder="https://zoom.us...">
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold">課堂講師備註 / 調課說明 (less_note)</label>
              <textarea class="form-control form-control-sm" id="edit_less_note" rows="2" placeholder="例如：因國定假日順延一週..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
          <button type="button" class="btn btn-primary btn-sm fw-bold" id="btnSaveEditLesson"><i class="bi bi-check-lg me-1"></i>儲存單堂設定並同步排程</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal 2: Zoom 會議終結報告 (#modalZoomReport) -->
  <div class="modal fade" id="modalZoomReport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-success text-white py-2">
          <h6 class="modal-title fw-bold"><i class="bi bi-file-earmark-bar-graph me-1"></i>Zoom 會議終結報告與出缺席紀實</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="rpt_less_id" value="0">
          <div class="alert alert-light border small py-2 mb-3 d-flex justify-content-between align-items-center">
            <div>
              <strong id="rpt_less_header" class="text-dark">第 1 堂</strong>
              <span class="text-muted ms-2" id="rpt_less_meeting">Zoom ID：-</span>
            </div>
            <button type="button" class="btn btn-outline-success btn-xs" id="btnFetchZoomApiReport" title="預留串接 Zoom Past Meetings API 自動拉取實際與會名單與時長">
              <i class="bi bi-cloud-download me-1"></i>同步 Zoom 雲端報告
            </button>
          </div>
          <div class="mb-2">
            <label class="form-label small fw-bold">會議終結報告內容 / 實際出席與課堂紀錄 (less_zoom_report)</label>
            <textarea class="form-control form-control-sm font-monospace" id="rpt_less_zoom_report" rows="8" placeholder="可貼上 Zoom 後台匯出之與會者統計摘要、實際起訖時間、互動重點或課後觀察紀錄..."></textarea>
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">關閉</button>
          <button type="button" class="btn btn-success btn-sm fw-bold" id="btnSaveZoomReport"><i class="bi bi-save me-1"></i>儲存 Zoom 終結報告</button>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

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
  function syncOnWeekHidden() {
    const vals = [];
    $('.chk-onweek:checked').each(function() { vals.push($(this).val()); });
    $('#sess_onweek').val(vals.join(','));
  }

  function validateBS5Form($form) {
    if (!$form || $form.length === 0) return true;
    const isValid = $form[0].checkValidity();
    $form[0].classList.add('was-validated');
    return isValid;
  }

  function doReDirect() { window.history.back(); }

  function doButSave() {
    syncOnWeekHidden();
    var frm = $('#form_' + $('.tab-pane.active').prop("id"));
    if (!frm.length) return false;
    if (!validateBS5Form(frm)) {
      window.parent.toastr['warning']('請檢查標示紅框的必填或格式錯誤欄位！');
      return false;
    }
    ajaxPOST(frm);
  }

  function doButDelete() {
    if (0 == parseInt('<?php echo $sessID; ?>')) return false;
    $.post('../funcFiles/sess_act_func.php', {
      'formAcce': 'func_sess_delete',
      'token': getTokenVal(),
      'sessID': '<?php echo $sessID; ?>'
    }, function(data) {
      if (data['RTN'] == 1) {
        window.parent.toastr['success'](data['msgStr']);
        window.location.replace("sess_list.php");
      } else {
        window.parent.toastr['error'](data['msgStr'] || '刪除失敗');
      }
    }, 'json');
  }

  function doButReset() { location.reload(true); }

  function ajaxPOST(frm) {
    var $frm = frm || $('#form_tabMenu1');
    var accE = (0 == parseInt('<?php echo $sessID; ?>')) ? 'func_sess_addnew' : $frm.data('func');
    var datA = getFormObjects($frm);

    $.ajax({
      type: 'post',
      url: '../funcFiles/sess_act_func.php',
      data: {
        'arrDatA': datA,
        'formAcce': accE,
        'token': getTokenVal(),
        'sessID': '<?php echo $sessID; ?>'
      },
      dataType: 'json',
      success: function(data) {
        if (accE == 'func_sess_addnew' && data['RTN'] == 1) {
          window.parent.toastr['success'](data['msgStr']);
          window.location.replace("sess_detail.php?currID=" + data['newID']);
          return false;
        }
        if (window.parent && window.parent.toastr) {
          window.parent.toastr[data['msgType']](data['msgStr']);
        }
        if (data['RTN'] == 1) {
          $frm.removeClass('was-validated');
          $('#sideSessTitle').text('第 ' + ($('#sess_batch').val() || 1) + ' 梯次');
          $('#sideMeetingId').text($('#sess_meetingid').val() || '尚未建立');
          $('#sidePasscode').text($('#sess_passcode').val() || '-');
          if (data['upTime']) $('#sideSessUpdated').text(data['upTime']);
          if (typeof doSerialize === 'function') doSerialize();
        }
      }
    });
  }

  $(document).ready(function() {
    if (typeof flatpickr !== 'undefined') {
      flatpickr($('input[type="date"]'), { locale: 'zh_tw', dateFormat: 'Y-m-d', allowInput: true, disableMobile: true });
      flatpickr($('.flatpickr-datetime'), { locale: 'zh_tw', enableTime: true, enableSeconds: true, time_24hr: true, dateFormat: 'Y-m-d H:i:S', allowInput: true });
    }
    if ($('#dataTable_sessenroll').length) datatableCreate('dataTable_sessenroll');

    $('.chk-onweek').on('change', syncOnWeekHidden);

    // 1. 一鍵自動產生 Zoom 會議室 ID 與安全密碼
    $('#btnAutoZoomRoom').on('click', function() {
      const part1 = Math.floor(800 + Math.random() * 199);
      const part2 = Math.floor(1000 + Math.random() * 8999);
      const part3 = Math.floor(1000 + Math.random() * 8999);
      const meetId = `${part1} ${part2} ${part3}`;
      const rawId  = `${part1}${part2}${part3}`;
      const pwd    = Math.random().toString(36).substring(2, 8).toUpperCase();

      $('#sess_meetingid').val(meetId).trigger('change');
      $('#sess_passcode').val(pwd).trigger('change');
      if (!$('#sess_join_url').val()) {
        $('#sess_join_url').val(`https://zoom.us${rawId}?pwd=${pwd}`).trigger('change');
      }
      if (!$('#sess_start_url').val()) {
        $('#sess_start_url').val(`https://zoom.us{rawId}?zak=host_token`).trigger('change');
      }
      window.parent.toastr['info']('已自動產生 Zoom 會議號與密碼，請記得點擊頂部「儲存」按鈕寫入！');
    });

    // 2. 課間即時狀態切換 (0=未開課 / 1=LIVE課中 / 2=已結課)
    $('.btn-live-switch').on('click', function() {
      const liveVal = $(this).data('live');
      $.post('../funcFiles/sess_act_func.php', {
        'formAcce': 'func_sess_liveStatus',
        'token': getTokenVal(),
        'sessID': '<?php echo $sessID; ?>',
        'liveStatus': liveVal
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 3. 單堂課次狀態直接切換 (0=待上課 / 1=上課中 / 2=已完課 / 3=已延期)
    $(document).on('change', '.sel-lesson-status', function() {
      const lid = $(this).data('lid');
      const statusVal = $(this).val();
      $.post('../funcFiles/sess_act_func.php', {
        'formAcce': 'func_sess_lessonStatus',
        'token': getTokenVal(),
        'sessID': '<?php echo $sessID; ?>',
        'lessID': lid,
        'lessStatus': statusVal
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
      }, 'json');
    });

    // 4. 開啟「編輯課堂」互動彈窗 (#modalEditLesson)
    $(document).on('click', '.btn-open-edit-lesson', function() {
      const item = $(this).data('lesson');
      if (!item) return;
      $('#edit_less_id').val(item.less_id);
      $('#edit_less_no').val(item.less_no);
      $('#edit_less_title').val(item.less_title || '');
      $('#edit_less_start_at').val(item.less_start_at || '');
      $('#edit_less_end_at').val(item.less_end_at || '');
      $('#edit_less_location').val(item.less_location || '');
      $('#edit_less_status').val(item.less_status ?? 0);
      $('#edit_less_zoom_id').val(item.less_zoom_id || '');
      $('#edit_less_zoom_pass').val(item.less_zoom_pass || '');
      $('#edit_less_zoom_url').val(item.less_zoom_url || '');
      $('#edit_less_note').val(item.less_note || '');
      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditLesson')).show();
    });

    // 儲存「編輯課堂」並連動順延課前提醒
    $('#btnSaveEditLesson').on('click', function() {
      const title = $.trim($('#edit_less_title').val());
      const startAt = $.trim($('#edit_less_start_at').val());
      const endAt = $.trim($('#edit_less_end_at').val());
      if (!title || !startAt || !endAt) {
        window.parent.toastr['warning']('請填寫單堂主題名稱與起訖時間！');
        return;
      }
      $.post('../funcFiles/sess_act_func.php', {
        'formAcce': 'func_sess_lessonSave',
        'token': getTokenVal(),
        'sessID': '<?php echo $sessID; ?>',
        'arrDatA': {
          'less_id': $('#edit_less_id').val(),
          'less_title': title,
          'less_start_at': startAt,
          'less_end_at': endAt,
          'less_location': $.trim($('#edit_less_location').val()),
          'less_status': $('#edit_less_status').val(),
          'less_zoom_id': $.trim($('#edit_less_zoom_id').val()),
          'less_zoom_pass': $.trim($('#edit_less_zoom_pass').val()),
          'less_zoom_url': $.trim($('#edit_less_zoom_url').val()),
          'less_note': $.trim($('#edit_less_note').val())
        }
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 5. 開啟「Zoom 終結報告」互動彈窗 (#modalZoomReport)
    $(document).on('click', '.btn-open-zoom-report', function() {
      const item = $(this).data('lesson');
      if (!item) return;
      $('#rpt_less_id').val(item.less_id);
      $('#rpt_less_header').text(`第 ${item.less_no} 堂：${item.less_title || ''}`);
      const zId = item.less_zoom_id || $('#sess_meetingid').val() || '未設定';
      $('#rpt_less_meeting').text(`Zoom 會議號：${zId}`);
      $('#rpt_less_zoom_report').val(item.less_zoom_report || '');
      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalZoomReport')).show();
    });

    // 預留一鍵同步 Zoom 雲端報告按鈕
    $('#btnFetchZoomApiReport').on('click', function() {
      const lid = $('#rpt_less_id').val();
      $.post('../funcFiles/sess_act_func.php', {
        'formAcce': 'func_sess_zoomReportSync',
        'token': getTokenVal(),
        'sessID': '<?php echo $sessID; ?>',
        'lessID': lid
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1 && data['reportText']) {
          $('#rpt_less_zoom_report').val(data['reportText']);
        }
      }, 'json');
    });

    // 儲存 Zoom 終結報告
    $('#btnSaveZoomReport').on('click', function() {
      $.post('../funcFiles/sess_act_func.php', {
        'formAcce': 'func_sess_zoomReportSave',
        'token': getTokenVal(),
        'sessID': '<?php echo $sessID; ?>',
        'lessID': $('#rpt_less_id').val(),
        'zoomReport': $('#rpt_less_zoom_report').val()
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 依梯次規則重新展開課次
    $('#btnRegenLessons').on('click', function() {
      if (!confirm('確定要依目前梯次的開課日期、週間上課日與總堂數重新同步單堂課次嗎？')) return;
      $.post('../funcFiles/sess_act_func.php', {
        'formAcce': 'func_sess_regenLessons',
        'token': getTokenVal(),
        'sessID': '<?php echo $sessID; ?>'
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 6. Tab 4: 新增多筆課後錄影 / 講義至 fsl_media
    $('#btnAddSessMedia').on('click', function() {
      const title = $.trim($('#new_med_title').val());
      const url   = $.trim($('#new_med_url').val());
      if (!title || !url) {
        window.parent.toastr['warning']('請輸入資源名稱與雲端連結網址！');
        return;
      }
      $.post('../funcFiles/sess_act_func.php', {
        'formAcce': 'func_sess_mediaAdd',
        'token': getTokenVal(),
        'sessID': '<?php echo $sessID; ?>',
        'arrDatA': {
          'med_target': $('#new_med_target').val(),
          'med_type': $('#new_med_type').val(),
          'med_title': title,
          'med_url': url,
          'med_sort': $('#new_med_sort').val() || 100,
          'med_brief': $.trim($('#new_med_brief').val())
        }
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 即時更新 fsl_media 排序
    $(document).on('change', '.inp-media-sort', function() {
      const mid = $(this).data('mid');
      const sortVal = $(this).val();
      $.post('../funcFiles/sess_act_func.php', {
        'formAcce': 'func_sess_mediaSort',
        'token': getTokenVal(),
        'sessID': '<?php echo $sessID; ?>',
        'medID': mid,
        'medSort': sortVal
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
      }, 'json');
    });

    // 移除 fsl_media 掛載項目
    $(document).on('click', '.btn-del-sess-media', function() {
      if (!confirm('確定要移除此筆錄影或講義檔案嗎？')) return;
      const mid = $(this).data('mid');
      $.post('../funcFiles/sess_act_func.php', {
        'formAcce': 'func_sess_mediaDelete',
        'token': getTokenVal(),
        'sessID': '<?php echo $sessID; ?>',
        'medID': mid
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) $('#medRow_' + mid).remove();
      }, 'json');
    });

    // 7. 單獨發送 Zoom 授權連結給單一學員
    $(document).on('click', '.btn-send-single-link', function() {
      const $btn = $(this);
      const eid  = $btn.data('eid');
      $.post('../funcFiles/sess_act_func.php', {
        'formAcce': 'func_sess_sendZoomLink',
        'token': getTokenVal(),
        'sessID': '<?php echo $sessID; ?>',
        'enrID': eid
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) {
          $btn.html(`<i class="bi bi-send me-1"></i>補發 (${data['sentCnt']})`);
          $('#sentTime_' + eid).text(data['sentAt']);
        }
      }, 'json');
    });

    // 8. 一鍵群發 Zoom 授權連結給本梯次所有有效學員
    $('#btnBatchSendZoomLink').on('click', function() {
      if (!confirm('確定要一鍵群發 Zoom 專屬授權連結通知給本梯次所有有效學員嗎？')) return;
      $.post('../funcFiles/sess_act_func.php', {
        'formAcce': 'func_sess_sendZoomLink',
        'token': getTokenVal(),
        'sessID': '<?php echo $sessID; ?>',
        'enrID': 0
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 9. 學員課間點名簽到 / 簽退 / 請假 / 缺席
    $(document).on('click', '.btn-attend-act', function() {
      const eid = $(this).data('eid');
      const act = $(this).data('act');
      $.post('../funcFiles/sess_act_func.php', {
        'formAcce': 'func_sess_attend',
        'token': getTokenVal(),
        'sessID': '<?php echo $sessID; ?>',
        'enrID': eid,
        'attendAct': act
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) {
          $('#attBadge_' + eid).html(data['badgeHtml']);
          $('#attTimeCell_' + eid).html(`<div>簽到：${data['checkinShort']}</div><div>簽退：${data['checkoutShort']}</div>`);
          $('#sideAttendCnt').text(`${data['checkinCnt']} / ${data['checkoutCnt']} 人`);
        }
      }, 'json');
    });

    // 10. 已簽到學員一鍵全體簽退
    $('#btnBatchCheckoutAll').on('click', function() {
      if (!confirm('確定要將目前「已簽到上課中」的所有學員一鍵完成課後簽退嗎？')) return;
      $.post('../funcFiles/sess_act_func.php', {
        'formAcce': 'func_sess_batchCheckout',
        'token': getTokenVal(),
        'sessID': '<?php echo $sessID; ?>'
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    $('li.prevLink').on('click', function() { window.location.replace(getCurrentURI('', 'sess_list.php')); });
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
