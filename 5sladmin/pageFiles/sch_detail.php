<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

$fromCal = isset($_GET['fromCal']) && $_GET['fromCal'] === '1';

// 定義本頁面代碼
define('PAGE_MARK', $fromCal ? 'PG_SCH_CAL' : 'PG_SCH_LIST');
define('PAGE_ITEM', 'ITEM_SCH');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$schID     = currID;
$initDate  = isset($_GET['startDate']) ? trim($_GET['startDate']) : '';
$res       = ($schID > 0) ? DB->row("SELECT * FROM fsl_schedules WHERE sch_id = ? AND sch_exec_status != 9", array($schID)) : array();
if (!is_array($res)) $res = array();

// 撈取課程清單、動態通知/郵件模板清單、有效會員清單、後台管理者/講師清單
$courseOptions = DB->query("SELECT course_id, course_title FROM fsl_courses WHERE parent_id = 0 AND course_status != 9 ORDER BY course_id DESC");
$tplOptions    = DB->query("SELECT arti_keycode, arti_title, arti_mode FROM fsl_articles WHERE arti_keycode IS NOT NULL AND arti_keycode != '' AND arti_status = 1 ORDER BY arti_id DESC");
$adminOptions = array();
try {
  $adminOptions = DB->query("SELECT ad_id, ad_name, ad_account FROM fsl_administrator WHERE ad_status = 1 ORDER BY ad_id ASC");
} catch (Exception $e) {
  $adminOptions = array();
}
if (!is_array($adminOptions)) $adminOptions = array();
$memberOptions = array();
$mList = DB->query("SELECT mb_id, mb_name, mb_email FROM fsl_members WHERE mb_status = 1 ORDER BY mb_id DESC LIMIT 300");
if (is_array($mList)) {
  foreach ($mList as $ml) {
    $memberOptions[] = array(
      'mb_id' => (int)$ml['mb_id'],
      'label' => '#' . $ml['mb_id'] . ' - ' . _fn_secure_crypto($ml['mb_name'], 'de') . ' (' . _fn_secure_crypto($ml['mb_email'], 'de') . ')'
    );
  }
}

if (!is_array($courseOptions)) $courseOptions = array();
if (!is_array($tplOptions))    $tplOptions    = array();
if (!is_array($adminOptions))  $adminOptions  = array();

// 查詢發送執行明細子表 (fsl_schedule_logs)
$logRows = ($schID > 0) ? DB->query("SELECT * FROM fsl_schedule_logs WHERE sch_id = ? ORDER BY slog_id DESC LIMIT 300", array($schID)) : array();
if (!is_array($logRows)) $logRows = array();

$selectedChannels = explode(',', $res['sch_channels'] ?? 'noti,email');
$selectedTargetIds = !empty($res['sch_target_ids']) ? array_map('trim', explode(',', $res['sch_target_ids'])) : array();
$currTargetType    = (int)($res['sch_target_type'] ?? 1);

$defaultStart = !empty($res['sch_start_at']) ? $res['sch_start_at'] : ($initDate !== '' ? ($initDate . (strlen($initDate) === 10 ? ' 09:00:00' : '')) : date('Y-m-d 09:00:00'));
$defaultTrig  = !empty($res['sch_trigger_at']) ? $res['sch_trigger_at'] : $defaultStart;
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
  <title>Fifth Season Life 人生第五季 | 排程與自動化通知詳情</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../pluginFiles/flatpickr/flatpickr.min.css">
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
                <?php echo !empty($schID) ? "編輯" : "新增"; ?>排程與自動化通知任務
                <span class="fs-7">【ID: <?php echo $schID; ?>】</span>
              </h4>
              <h6 class="m-0 fw-light text-muted fs-7 mt-1 lh-xs mb-n2"><i class="bi bi-arrow-return-right"></i> 設定日曆時段、定時觸發通知、發送對象模式（個別/課程群組/自選/講師）與多通路發送</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">排程與通知管理</li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回上一頁"><?php echo $fromCal ? '排程視覺日曆' : '排程事件列表'; ?></li>
                <li class="breadcrumb-item active"><?php echo !empty($schID) ? "排程詳情" : "新增排程"; ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <!-- 左側排程狀態與觸發摘要卡 -->
            <div class="col-md-3 mb-3">
              <div class="card card-primary card-outline shadow-lg">
                <div class="card-body">
                  <div class="text-center mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle text-white mb-2" id="sideColorBadge"
                         style="width: 64px; height: 64px; background-color: <?php echo htmlspecialchars($res['sch_color'] ?? '#3b82f6'); ?>;">
                      <i class="bi bi-alarm-fill fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1" id="sideSchTitle"><?php echo !empty($res['sch_title']) ? htmlspecialchars($res['sch_title']) : ($schID > 0 ? '未命名排程' : '新建立排程'); ?></h5>
                    <span class="badge bg-light text-dark border"><?php echo ((int)($res['sch_source'] ?? 1) === 2) ? '課程梯次自動同步' : '手動建立排程'; ?></span>
                  </div>

                  <ul class="list-group list-group-flush small mb-3">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>定時通知觸發</b>
                      <span class="<?php echo ((int)($res['sch_trigger_enable'] ?? 0) === 1) ? 'text-danger fw-bold' : 'text-muted'; ?>">
                        <?php echo ((int)($res['sch_trigger_enable'] ?? 0) === 1) ? '已啟用觸發' : '僅純日曆記事'; ?>
                      </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>執行狀態</b>
                      <span>
                        <?php
                          $eSt = (int)($res['sch_exec_status'] ?? 0);
                          if ($eSt === 1) echo '<span class="text-success fw-bold">已執行完成</span>';
                          elseif ($eSt === 2) echo '<span class="text-danger fw-bold">部分失敗</span>';
                          else echo '<span class="text-warning-emphasis fw-bold">待執行</span>';
                        ?>
                      </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>實際執行時間</b>
                      <span class="text-muted"><?php echo $res['sch_executed_at'] ?? '-'; ?></span>
                    </li>
                    <li class="list-group-item px-0">
                      <b class="d-block mb-1">執行結果摘要</b>
                      <span class="text-muted"><?php echo !empty($res['sch_exec_result']) ? htmlspecialchars($res['sch_exec_result']) : '尚未觸發執行'; ?></span>
                    </li>
                  </ul>

                  <?php if ($schID > 0 && (int)($res['sch_trigger_enable'] ?? 0) === 1): ?>
                  <div class="d-grid">
                    <button type="button" class="btn btn-warning btn-sm fw-bold" id="btnManualTriggerNow">
                      <i class="bi bi-send-fill me-1"></i>立即手動觸發執行發送
                    </button>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <!-- 右側多分頁排程與自動化通知設定區 -->
            <div class="col-md-9">
              <div class="card shadow-lg modern-card-tabs">
                <div class="card-header p-2">
                  <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                      <a class="nav-link active" href="#tabMenu1" data-index="1" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-calendar-event me-1"></i>日曆事件與時段設定
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu2" data-index="2" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-bell-fill me-1"></i>定時通知觸發與發送對象
                      </a>
                    </li>
                    <?php if ($schID > 0): ?>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu3" data-index="3" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-list-check me-1"></i>發送執行明細紀錄 (<?php echo count($logRows); ?>)
                      </a>
                    </li>
                    <?php endif; ?>
                  </ul>
                </div>

                <div class="card-body bg-opacity-50">
                  <form id="form_schMaster" action="../funcFiles/sch_act_func.php" method="post" data-func="func_sch_infoUpdate" novalidate>
                    <input type="hidden" name="sch_channels" id="sch_channels" value="<?php echo htmlspecialchars($res['sch_channels'] ?? 'noti,email'); ?>">
                    <input type="hidden" name="sch_target_ids" id="sch_target_ids" value="<?php echo htmlspecialchars($res['sch_target_ids'] ?? ''); ?>">

                    <div class="tab-content">
                      <!-- Tab 1: 日曆事件與時段設定 -->
                      <div class="tab-pane fade show active" id="tabMenu1" role="tabpanel">
                        <div class="row g-3">
                          <div class="col-md-6">
                            <label class="form-label small fw-bold">排程事件標題 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sch_title" name="sch_title" required
                                   value="<?php echo htmlspecialchars($res['sch_title'] ?? ''); ?>" placeholder="例如：色彩覺察工作坊（台北場）開課提醒">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">事件分類</label>
                            <select class="form-select" name="sch_category">
                              <option value="course" <?php echo (($res['sch_category'] ?? 'course') === 'course') ? 'selected' : ''; ?>>course - 課程梯次</option>
                              <option value="remind" <?php echo (($res['sch_category'] ?? '') === 'remind') ? 'selected' : ''; ?>>remind - 課前提醒</option>
                              <option value="marketing" <?php echo (($res['sch_category'] ?? '') === 'marketing') ? 'selected' : ''; ?>>marketing - 行銷 EDM</option>
                              <option value="admin" <?php echo (($res['sch_category'] ?? '') === 'admin') ? 'selected' : ''; ?>>admin - 內部工作提醒</option>
                            </select>
                          </div>
                          <div class="col-md-2">
                            <label class="form-label small fw-bold">日曆標籤色</label>
                            <input type="color" class="form-control form-control-color w-100" id="sch_color" name="sch_color"
                                   value="<?php echo htmlspecialchars($res['sch_color'] ?? '#3b82f6'); ?>">
                          </div>

                          <div class="col-md-5">
                            <label class="form-label small fw-bold">事件開始時間 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control datetime-picker" name="sch_start_at" required
                                   value="<?php echo htmlspecialchars($defaultStart); ?>">
                          </div>
                          <div class="col-md-5">
                            <label class="form-label small fw-bold">事件結束時間 (選填)</label>
                            <input type="text" class="form-control datetime-picker" name="sch_end_at"
                                   value="<?php echo htmlspecialchars($res['sch_end_at'] ?? ''); ?>">
                          </div>
                          <div class="col-md-2">
                            <label class="form-label small fw-bold">全天事件</label>
                            <select class="form-select" name="sch_all_day">
                              <option value="0" <?php echo ((int)($res['sch_all_day'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 指定時段</option>
                              <option value="1" <?php echo ((int)($res['sch_all_day'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 全天事件</option>
                            </select>
                          </div>

                          <div class="col-md-8">
                            <label class="form-label small fw-bold">關聯課程 (選填，供群組通知與日曆篩選)</label>
                            <select class="form-select" id="course_id" name="course_id">
                              <option value="">-- 不限定特定課程 --</option>
                              <?php foreach ($courseOptions as $co): ?>
                                <option value="<?php echo (int)$co['course_id']; ?>" <?php echo ((int)($res['course_id'] ?? 0) === (int)$co['course_id']) ? 'selected' : ''; ?>>
                                  #<?php echo (int)$co['course_id']; ?> - <?php echo htmlspecialchars($co['course_title']); ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">排程執行狀態</label>
                            <select class="form-select" name="sch_exec_status">
                              <option value="0" <?php echo ((int)($res['sch_exec_status'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 待執行 (未觸發)</option>
                              <option value="1" <?php echo ((int)($res['sch_exec_status'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 已執行完成</option>
                              <option value="2" <?php echo ((int)($res['sch_exec_status'] ?? 0) === 2) ? 'selected' : ''; ?>>2 - 部分失敗 (可重試)</option>
                            </select>
                          </div>
                        </div>
                      </div>

                      <!-- Tab 2: 定時通知觸發、發送對象與多通路設定 -->
                      <div class="tab-pane fade" id="tabMenu2" role="tabpanel">
                        <div class="row g-3">
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">啟用伺服器定時通知觸發</label>
                            <select class="form-select" id="sch_trigger_enable" name="sch_trigger_enable">
                              <option value="0" <?php echo ((int)($res['sch_trigger_enable'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 關閉 (僅日曆記事)</option>
                              <option value="1" <?php echo ((int)($res['sch_trigger_enable'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 啟用定時自動發送</option>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">預定觸發發送時間 (Cron 掃描)</label>
                            <input type="text" class="form-control datetime-picker" name="sch_trigger_at"
                                   value="<?php echo htmlspecialchars($defaultTrig); ?>">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">發送對象類型 (sch_target_type)</label>
                            <select class="form-select" id="sch_target_type" name="sch_target_type">
                              <option value="1" <?php echo ($currTargetType === 1) ? 'selected' : ''; ?>>1 - 會員個別發送</option>
                              <option value="2" <?php echo ($currTargetType === 2) ? 'selected' : ''; ?>>2 - 依群組 (特定課程學員) 發送</option>
                              <option value="3" <?php echo ($currTargetType === 3) ? 'selected' : ''; ?>>3 - 自選多位對象發送</option>
                              <option value="4" <?php echo ($currTargetType === 4) ? 'selected' : ''; ?>>4 - 關係管理者或講師發送</option>
                            </select>
                          </div>

                          <!-- 動態切換的 4 種對象挑選器 -->
                          <div class="col-12 target-box" id="targetBox_1" style="<?php echo ($currTargetType === 1) ? '' : 'display:none;'; ?>">
                            <label class="form-label small fw-bold text-primary">選擇個別接收會員</label>
                            <select class="form-select" id="selTargetSingleMb">
                              <option value="">-- 請選擇單一會員 --</option>
                              <?php foreach ($memberOptions as $mo): ?>
                                <option value="<?php echo $mo['mb_id']; ?>" <?php echo ($currTargetType === 1 && in_array((string)$mo['mb_id'], $selectedTargetIds, true)) ? 'selected' : ''; ?>>
                                  <?php echo htmlspecialchars($mo['label']); ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>

                            <div class="col-12 target-box" id="targetBox_2" style="<?php echo ($currTargetType === 2) ? '' : 'display:none;'; ?>">
                              <label class="form-label small fw-bold">選擇課程群組（系統將自動抓出該課程所有已報名付費學員）</label>
                              <select class="form-select" id="target_course_id">
                                <option value="">-- 請選擇課程 --</option>
                                <?php foreach ($courseOptions as $co):
                                  $cidStr = (string)$co['course_id'];
                                  $sel = ($currTargetType === 2 && in_array($cidStr, $selectedTargetIds, true)) ? 'selected' : '';
                                ?>
                                  <option value="<?php echo $cidStr; ?>" <?php echo $sel; ?>>#<?php echo $cidStr; ?> - <?php echo htmlspecialchars($co['course_title']); ?></option>
                                <?php endforeach; ?>
                              </select>
                            </div>

                            <!-- 對象模式 3: 自選多位會員 -->
                            <div class="col-12 target-box" id="targetBox_3" style="<?php echo ($currTargetType === 3) ? '' : 'display:none;'; ?>">
                              <label class="form-label small fw-bold">勾選多位指定會員（可複選）</label>
                              <div class="border rounded-2 p-2 bg-white" style="max-height: 180px; overflow-y: auto;">
                                <?php foreach ($memberOptions as $mo):
                                  $midStr = (string)$mo['mb_id'];
                                  $chk = ($currTargetType === 3 && in_array($midStr, $selectedTargetIds, true)) ? 'checked' : '';
                                ?>
                                <div class="form-check py-1 border-bottom">
                                  <input class="form-check-input chk-target-mb" type="checkbox" value="<?php echo $midStr; ?>" id="tmb_<?php echo $midStr; ?>" <?php echo $chk; ?>>
                                  <label class="form-check-label small" for="tmb_<?php echo $midStr; ?>"><?php echo htmlspecialchars($mo['label']); ?></label>
                                </div>
                                <?php endforeach; ?>
                              </div>
                            </div>

                            <!-- 對象模式 4: 關係管理者或講師發送 -->
                            <div class="col-12 target-box" id="targetBox_4" style="<?php echo ($currTargetType === 4) ? '' : 'display:none;'; ?>">
                              <label class="form-label small fw-bold">勾選接收通知的管理者 / 講師（可複選）</label>
                              <div class="border rounded-2 p-2 bg-white" style="max-height: 160px; overflow-y: auto;">
                                <?php foreach ($adminOptions as $ao):
                                  $aidStr = (string)$ao['ad_id'];
                                  $chk = ($currTargetType === 4 && in_array($aidStr, $selectedTargetIds, true)) ? 'checked' : '';
                                ?>
                                <div class="form-check py-1 border-bottom">
                                  <input class="form-check-input chk-target-ad" type="checkbox" value="<?php echo $aidStr; ?>" id="tad_<?php echo $aidStr; ?>" <?php echo $chk; ?>>
                                  <label class="form-check-label small" for="tad_<?php echo $aidStr; ?>">
                                    #<?php echo $aidStr; ?> - <?php echo htmlspecialchars($ao['ad_name']); ?> (<?php echo htmlspecialchars($ao['ad_account']); ?>)
                                  </label>
                                </div>
                                <?php endforeach; ?>
                              </div>
                            </div>

                            <input type="hidden" name="sch_target_ids" id="sch_target_ids" value="<?php echo htmlspecialchars($res['sch_target_ids'] ?? ''); ?>">

                            <!-- 多通路勾選 (noti / email / sms / line) -->
                            <div class="col-12">
                              <label class="form-label small fw-bold d-block">發送通路選擇 (可複選，預留簡訊與 LINE 串接介面)</label>
                              <div class="d-flex flex-wrap gap-3 p-2 px-3 bg-light border rounded-2 small">
                                <div class="form-check">
                                  <input class="form-check-input chk-sch-channel" type="checkbox" value="noti" id="ch_noti" <?php echo in_array('noti', $selectedChannels, true) ? 'checked' : ''; ?>>
                                  <label class="form-check-label fw-bold text-primary" for="ch_noti"><i class="bi bi-bell-fill me-1"></i>會員站內通知 (noti)</label>
                                </div>
                                <div class="form-check">
                                  <input class="form-check-input chk-sch-channel" type="checkbox" value="email" id="ch_email" <?php echo in_array('email', $selectedChannels, true) ? 'checked' : ''; ?>>
                                  <label class="form-check-label fw-bold text-success" for="ch_email"><i class="bi bi-envelope-fill me-1"></i>電子郵件 (email)</label>
                                </div>
                                <div class="form-check">
                                  <input class="form-check-input chk-sch-channel" type="checkbox" value="sms" id="ch_sms" <?php echo in_array('sms', $selectedChannels, true) ? 'checked' : ''; ?>>
                                  <label class="form-check-label fw-bold text-warning-emphasis" for="ch_sms"><i class="bi bi-chat-dots-fill me-1"></i>手機簡訊 (sms 預留)</label>
                                </div>
                                <div class="form-check">
                                  <input class="form-check-input chk-sch-channel" type="checkbox" value="line" id="ch_line" <?php echo in_array('line', $selectedChannels, true) ? 'checked' : ''; ?>>
                                  <label class="form-check-label fw-bold text-info-emphasis" for="ch_line"><i class="bi bi-line me-1"></i>LINE 官方推播 (line 預留)</label>
                                </div>
                              </div>
                              <input type="hidden" name="sch_channels" id="sch_channels" value="<?php echo htmlspecialchars($res['sch_channels'] ?? 'noti,email'); ?>">
                            </div>

                            <div class="col-md-6">
                              <label class="form-label small fw-bold">關聯動態變數模板 (arti_keycode，選填)</label>
                              <select class="form-select" name="arti_keycode">
                                <option value="">-- 不套用模板（使用下方自訂主旨與內容） --</option>
                                <?php foreach ($tplOptions as $to): ?>
                                  <option value="<?php echo htmlspecialchars($to['arti_keycode']); ?>" <?php echo (($res['arti_keycode'] ?? '') === $to['arti_keycode']) ? 'selected' : ''; ?>>
                                    [<?php echo htmlspecialchars($to['arti_keycode']); ?>] <?php echo htmlspecialchars($to['arti_title']); ?>
                                  </option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                            <div class="col-md-6">
                              <label class="form-label small fw-bold">自訂通知 / 郵件主旨 (支援 {{member_name}}, {{course_title}}, {{date}})</label>
                              <input type="text" class="form-control" name="sch_msg_title" value="<?php echo htmlspecialchars($res['sch_msg_title'] ?? ''); ?>" placeholder="若未選擇動態模板則使用此主旨">
                            </div>
                            <div class="col-12">
                              <label class="form-label small fw-bold">自訂通知 / 郵件內容 (支援 {{member_name}}, {{course_title}}, {{date}} 變數替換)</label>
                              <textarea class="form-control" name="sch_msg_body" rows="4"><?php echo htmlspecialchars($res['sch_msg_body'] ?? ''); ?></textarea>
                            </div>
                          </div>
                        </form>
                      </div>
                      <!-- Tab 3: 發送執行明細子表 (fsl_schedule_logs) -->
                      <?php if ($schID > 0): ?>
                      <div class="tab-pane fade" id="tabMenu3" role="tabpanel">
                        <div class="table-responsive">
                          <table id="dataTable_schlogs" class="table table-striped table-sm table-bordered table-hover align-middle w-100 small" data-search="1">
                            <thead class="table-light text-nowrap">
                              <tr>
                                <th>流水ID</th>
                                <th>發送時間</th>
                                <th class="text-center">接收者身分</th>
                                <th>接收者 ID</th>
                                <th class="text-center">發送通路</th>
                                <th class="text-center">發送狀態</th>
                                <th>回傳訊息 / 佇列備註</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php foreach ($logRows as $lg):
                                $rType = (int)($lg['recipient_type'] ?? 1);
                                $sSta  = (int)($lg['send_status'] ?? 1);
                              ?>
                              <tr>
                                <td class="fw-bold">#<?php echo (int)$lg['slog_id']; ?></td>
                                <td class="text-muted"><?php echo $lg['slog_created']; ?></td>
                                <td class="text-center">
                                  <?php echo ($rType === 1) ? '<span class="badge bg-primary-subtle text-primary border">前台會員</span>' : '<span class="badge bg-dark text-white">管理者/講師</span>'; ?>
                                </td>
                                <td class="fw-bold">#<?php echo (int)$lg['recipient_id']; ?></td>
                                <td class="text-center"><code class="text-uppercase fw-bold"><?php echo htmlspecialchars($lg['channel']); ?></code></td>
                                <td class="text-center">
                                  <?php
                                    if ($sSta === 1) echo '<span class="badge text-bg-success">1 - 發送成功</span>';
                                    elseif ($sSta === 2) echo '<span class="badge text-bg-warning">2 - 待外部API佇列</span>';
                                    else echo '<span class="badge text-bg-danger">0 - 發送失敗</span>';
                                  ?>
                                </td>
                                <td class="text-muted"><?php echo htmlspecialchars($lg['send_note'] ?? '-'); ?></td>
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
    function syncScheduleHiddenFields() {
      // 1. 同步多通路複選值
      const chs = [];
      $('.chk-sch-channel:checked').each(function() { chs.push($(this).val()); });
      $('#sch_channels').val(chs.join(','));

      // 2. 依發送對象類型同步 sch_target_ids
      const tType = $('#sch_target_type').val();
      let tIds = '';
      if (tType === '1') {
        tIds = $('#target_single_mb').val() || '';
      } else if (tType === '2') {
        tIds = $('#target_course_id').val() || '';
      } else if (tType === '3') {
        const arr = [];
        $('.chk-target-mb:checked').each(function() { arr.push($(this).val()); });
        tIds = arr.join(',');
      } else if (tType === '4') {
        const arr = [];
        $('.chk-target-ad:checked').each(function() { arr.push($(this).val()); });
        tIds = arr.join(',');
      }
      $('#sch_target_ids').val(tIds);
    }

    function doReDirect() { window.history.back(); }

    function doButSave() {
      syncScheduleHiddenFields();
      const frm1 = document.getElementById('form_tabMenu1');
      if (!frm1.checkValidity()) {
        frm1.classList.add('was-validated');
        window.parent.toastr['warning']('請檢查標示紅框的必填欄位（事件標題與開始時間）！');
        return false;
      }

      // 合併 Tab 1 與 Tab 2 所有欄位送出儲存
      const datA = Object.assign({}, getFormObjects($('#form_tabMenu1')), getFormObjects($('#form_tabMenu2')));
      const accE = (0 == parseInt('<?php echo $schID; ?>')) ? 'func_sch_addnew' : 'func_sch_infoUpdate';

      $.ajax({
        type: 'post',
        url: '../funcFiles/sch_act_func.php',
        data: {
          'arrDatA': datA,
          'formAcce': accE,
          'token': getTokenVal(),
          'schID': '<?php echo $schID; ?>'
        },
        dataType: 'json',
        success: function(data) {
          window.parent.toastr[data['msgType']](data['msgStr']);
          if (data['RTN'] == 1) {
            if (accE === 'func_sch_addnew' && data['newID']) {
              window.location.replace('sch_detail.php?currID=' + data['newID'] + '<?php echo $fromCal ? "&fromCal=1" : ""; ?>');
            } else {
              $('#sideSchTitle').text($('#sch_title').val() || '未命名排程');
              $('#sideColorBadge').css('background-color', $('#sch_color').val());
              if (typeof doSerialize === 'function') doSerialize();
            }
          }
        }
      });
    }

    function doButDelete() {
      if (0 == parseInt('<?php echo $schID; ?>')) return false;
      $.post('../funcFiles/sch_act_func.php', {
        'formAcce': 'func_sch_delete',
        'token': getTokenVal(),
        'schID': '<?php echo $schID; ?>'
      }, function(data) {
        if (data['RTN'] == 1) {
          window.parent.toastr['success'](data['msgStr']);
          window.location.replace('<?php echo $fromCal ? "sch_cal_list.php" : "sch_list.php"; ?>');
        } else {
          window.parent.toastr['error'](data['msgStr'] || '刪除失敗');
        }
      }, 'json');
    }

    function doButReset() { location.reload(true); }

    $(document).ready(function() {
      if (typeof flatpickr !== 'undefined') {
        flatpickr($('.datetime-picker'), { locale: 'zh_tw', enableTime: true, enableSeconds: true, dateFormat: 'Y-m-d H:i:S', time_24hr: true, allowInput: true });
      }
      if ($('#dataTable_schlogs').length) datatableCreate('dataTable_schlogs');

      // 切換發送對象類型面板
      $('#sch_target_type').on('change', function() {
        $('.target-box').hide();
        $('#targetBox_' + $(this).val()).fadeIn(150);
      });

      // 手動立即觸發測試發送
      $('#btnManualTriggerNow').on('click', function() {
        if (!confirm('確定要立即執行此排程的定時通知發送嗎？（將寫入會員站內通知與發送紀錄）')) return;
        const $btn = $(this).prop('disabled', true);
        $.post('../funcFiles/sch_act_func.php', {
          'formAcce': 'func_sch_triggerNow',
          'token': getTokenVal(),
          'schID': '<?php echo $schID; ?>'
        }, function(data) {
          $btn.prop('disabled', false);
          window.parent.toastr[data['msgType']](data['msgStr']);
          if (data['RTN'] == 1) location.reload(true);
        }, 'json');
      });

      $('li.prevLink').on('click', function() {
        window.location.replace(getCurrentURI('', '<?php echo $fromCal ? "sch_cal_list.php" : "sch_list.php"; ?>'));
      });

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
