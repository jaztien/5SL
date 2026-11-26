<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 定義本頁面代碼
define('PAGE_MARK', 'PG_NOTIFY');
define('PAGE_ITEM', 'ITEM_MSG');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$noID = currID;
$res  = ($noID > 0) ? DB->row("
  SELECT n.*, m.mb_name, m.mb_email, m.mb_cellphone, m.mb_level
  FROM fsl_notifications n
  LEFT JOIN fsl_members m ON n.mb_id = m.mb_id
  WHERE n.no_id = ?
", array($noID)) : array();
if (!is_array($res)) $res = array();

// 新增發送模式所需的會員、課程、梯次選單
$memberOptions  = array();
$masterCourses  = array();
$sessionOptions = array();

if ($noID === 0) {
  $mRows = DB->query("SELECT mb_id, mb_name, mb_email, mb_level FROM fsl_members WHERE mb_status = 1 ORDER BY mb_id DESC LIMIT 500");
  if (is_array($mRows)) {
    foreach ($mRows as $mr) {
      $dName = !empty($mr['mb_name']) ? _fn_secure_crypto($mr['mb_name'], 'de') : '未填寫';
      $dMail = !empty($mr['mb_email']) ? _fn_secure_crypto($mr['mb_email'], 'de') : '';
      $memberOptions[] = array(
        'mb_id' => (int)$mr['mb_id'],
        'label' => '#' . $mr['mb_id'] . ' - ' . $dName . ($dMail ? " ({$dMail})" : '') . ' [Lv.' . (int)$mr['mb_level'] . ']'
      );
    }
  }

  $masterCourses = DB->query("SELECT course_id, course_title FROM fsl_courses WHERE parent_id = 0 AND course_status != 9 ORDER BY course_sort ASC, course_id ASC");
  if (!is_array($masterCourses)) $masterCourses = array();

  $sessionOptions = DB->query("
    SELECT s.sess_id, s.course_id, s.sess_batch, s.sess_startdate, c.course_title
    FROM fsl_course_sessions s
    LEFT JOIN fsl_courses c ON s.course_id = c.course_id
    WHERE s.sess_status != 9
    ORDER BY s.course_id ASC, s.sess_batch DESC
  ");
  if (!is_array($sessionOptions)) $sessionOptions = array();
}

$catLabels = array(
  1 => '1 - 系統公告 (system)',
  2 => '2 - 訂單與課程 (order)',
  3 => '3 - 客服與支援 (support)',
  4 => '4 - 優惠與活動 (promo)'
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
  <title>Fifth Season Life 人生第五季 | <?php echo ($noID > 0) ? '站內通知明細檢視' : '手動發送站內通知'; ?></title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
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
                <?php echo ($noID > 0) ? '站內通知發送明細檢視' : '手動發送站內通知推播'; ?>
                <span class="fs-7">【ID: #<?php echo $noID; ?>】</span>
              </h4>
              <h6 class="m-0 fw-light text-muted fs-7 mt-1 lh-xs mb-n2"><i class="bi bi-arrow-return-right"></i> 直接推播通知至會員中心小鈴鐺（fsl_notifications），支援單一、群組與條件篩選發送</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">訊息管理系統</li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回站內通知列表">站內通知管理</li>
                <li class="breadcrumb-item active"><?php echo ($noID > 0) ? '通知明細' : '發送通知'; ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <?php if ($noID > 0 && !empty($res)):
            $isGlobal = is_null($res['mb_id']);
            $mid      = (int)($res['mb_id'] ?? 0);
            $decName  = !empty($res['mb_name']) ? _fn_secure_crypto($res['mb_name'], 'de') : '會員 #' . $mid;
            $decEmail = !empty($res['mb_email']) ? _fn_secure_crypto($res['mb_email'], 'de') : '-';
            $minLvl   = (int)($res['no_minlvl'] ?? 0);
          ?>
          <!-- =============================================================== -->
          <!-- 模式 A：檢視已發送之站內通知明細 (currID > 0)                     -->
          <!-- =============================================================== -->
          <div class="row justify-content-center">
            <div class="col-lg-9">
              <div class="card card-primary card-outline shadow-sm">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                  <span class="fw-bold text-primary"><i class="bi bi-bell-fill me-1"></i>站內通知內容明細</span>
                  <div>
                    <?php echo $isGlobal
                      ? '<span class="badge text-bg-success"><i class="bi bi-broadcast me-1"></i>全站 / 等級公告 (Lv.' . $minLvl . '+)</span>'
                      : '<span class="badge text-bg-info text-dark">指定會員通知</span>'; ?>
                  </div>
                </div>
                <div class="card-body">
                  <div class="row g-3 mb-3 small">
                    <div class="col-md-4">
                      <div class="p-2 bg-light border rounded">
                        <span class="text-muted d-block">接收對象</span>
                        <?php if ($isGlobal): ?>
                          <strong class="text-success">全站符合等級會員 (mb_id = NULL)</strong>
                        <?php else: ?>
                          <a href="mb_detail.php?currID=<?php echo $mid; ?>" class="fw-bold text-decoration-none">
                            <?php echo htmlspecialchars($decName); ?> (#<?php echo $mid; ?>)
                          </a>
                          <div class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($decEmail); ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="p-2 bg-light border rounded">
                        <span class="text-muted d-block">關聯跳轉網址 (no_link)</span>
                        <strong><?php echo !empty($res['no_link']) ? htmlspecialchars($res['no_link']) : '無設定跳轉網址'; ?></strong>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="p-2 bg-light border rounded">
                        <span class="text-muted d-block">發送時間 / 分類</span>
                        <strong><?php echo $res['no_created']; ?></strong>
                        <div class="text-muted" style="font-size: 0.72rem;"><?php echo $catLabels[(int)$res['no_category']] ?? '系統公告'; ?></div>
                      </div>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">通知主旨標題</label>
                    <div class="p-2 bg-light border rounded fw-bold text-dark"><?php echo htmlspecialchars($res['no_title']); ?></div>
                  </div>

                  <div class="mb-0">
                    <label class="form-label small fw-bold text-muted">通知內文內容</label>
                    <div class="p-3 bg-white border rounded text-dark" style="min-height: 140px; line-height: 1.7;">
                      <?php echo nl2br(htmlspecialchars((string)$res['no_content'])); ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <?php else: ?>
          <!-- =============================================================== -->
          <!-- 模式 B：管理者手動發送新站內通知 (currID = 0)                   -->
          <!-- =============================================================== -->
          <div class="row justify-content-center">
            <div class="col-lg-10">
              <div class="card card-primary card-outline shadow-lg">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                  <span class="fw-bold text-primary"><i class="bi bi-megaphone-fill me-1"></i>撰寫並派發站內通知（fsl_notifications）</span>
                  <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">支援 {會員姓名} 自動替換</span>
                </div>
                <div class="card-body">
                  <form id="form_tabMenu1" action="../funcFiles/noti_act_func.php" method="post" data-func="func_noti_addnew" novalidate>
                    <div class="row g-3">
                      <div class="col-md-4">
                        <label for="no_category" class="form-label small fw-bold">通知分類 <span class="text-danger">*</span></label>
                        <select class="form-select" id="no_category" name="no_category" required>
                          <option value="1">1 - 系統公告 (system)</option>
                          <option value="2">2 - 訂單與課程 (order)</option>
                          <option value="3">3 - 客服與支援 (support)</option>
                          <option value="4">4 - 優惠與活動 (promo)</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label for="target_mode" class="form-label small fw-bold">接收對象模式 <span class="text-danger">*</span></label>
                        <select class="form-select" id="target_mode" name="target_mode" required>
                          <option value="single">single - 單一指定會員</option>
                          <option value="multi">multi - 多筆群組指定會員</option>
                          <option value="filter">filter - 條件篩選會員</option>
                          <option value="all">all - 全站 / 等級公告廣播</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label for="noti_tpl_select" class="form-label small fw-bold">快速套用通知範本</label>
                        <select class="form-select" id="noti_tpl_select">
                          <option value="">-- 選擇常用範本 (選填) --</option>
                          <option value="event">近期沙龍 / 講座活動開放報名通知</option>
                          <option value="course_remind">課程行前準備與系統操作提醒</option>
                          <option value="points">專屬點數 / 任務獎勵發放通知</option>
                          <option value="maintenance">系統維護與服務時間公告</option>
                        </select>
                      </div>

                      <!-- 對象面板 1：單一會員 -->
                      <div class="col-12 target-panel" id="panel_single">
                        <div class="p-3 bg-light border rounded-3">
                          <label for="single_mb_id" class="form-label small fw-bold">選擇單一接收會員 <span class="text-danger">*</span></label>
                          <select class="form-select" id="single_mb_id" name="single_mb_id">
                            <option value="">-- 請選擇接收會員 --</option>
                            <?php foreach ($memberOptions as $mo): ?>
                              <option value="<?php echo $mo['mb_id']; ?>"><?php echo htmlspecialchars($mo['label']); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>

                      <!-- 對象面板 2：多筆群組會員 -->
                      <div class="col-12 target-panel d-none" id="panel_multi">
                        <div class="p-3 bg-light border rounded-3">
                          <label for="multi_mb_ids" class="form-label small fw-bold">按住 Ctrl / Command 複選多位群組會員 <span class="text-danger">*</span></label>
                          <select class="form-select" id="multi_mb_ids" name="multi_mb_ids[]" multiple size="6">
                            <?php foreach ($memberOptions as $mo): ?>
                              <option value="<?php echo $mo['mb_id']; ?>"><?php echo htmlspecialchars($mo['label']); ?></option>
                            <?php endforeach; ?>
                          </select>
                          <div class="form-text small">或直接輸入會員 ID 列表（以半形逗號分隔，如：<code>5, 12, 28</code>）：</div>
                          <input type="text" class="form-control form-control-sm mt-1" id="multi_mb_text" name="multi_mb_text" placeholder="選填：輸入會員 ID，以半形逗號分隔">
                        </div>
                      </div>

                      <!-- 對象面板 3：條件篩選會員 -->
                      <div class="col-12 target-panel d-none" id="panel_filter">
                        <div class="p-3 bg-info-subtle border border-info-subtle rounded-3">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold small text-info-emphasis"><i class="bi bi-funnel-fill me-1"></i>設定受眾篩選條件（留空表示不限該項）</span>
                            <button type="button" class="btn btn-info btn-xs text-white fw-bold" id="btnPreviewFilterCount">
                              <i class="bi bi-calculator me-1"></i>試算符合條件會員人數
                            </button>
                          </div>
                          <div class="row g-2">
                            <div class="col-md-4">
                              <label for="filter_mb_level" class="form-label small fw-bold mb-1">最低會員等級 (mb_level &ge;)</label>
                              <select class="form-select form-select-sm" id="filter_mb_level" name="filter_mb_level">
                                <option value="">-- 不限會員等級 --</option>
                                <option value="1">1 - 一般會員以上</option>
                                <option value="2">2 - 進階學員以上</option>
                                <option value="3">3 - 黃金高階 VIP 以上</option>
                                <option value="4">4 - 黑鑽尊享 VIP</option>
                              </select>
                            </div>
                            <div class="col-md-4">
                              <label for="filter_course_id" class="form-label small fw-bold mb-1">已報名指定主課程</label>
                              <select class="form-select form-select-sm" id="filter_course_id" name="filter_course_id">
                                <option value="">-- 不限課程 --</option>
                                <?php foreach ($masterCourses as $mc): ?>
                                  <option value="<?php echo (int)$mc['course_id']; ?>">#<?php echo (int)$mc['course_id']; ?> - <?php echo htmlspecialchars($mc['course_title']); ?></option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                            <div class="col-md-4">
                              <label for="filter_sess_id" class="form-label small fw-bold mb-1">已報名指定開課梯次</label>
                              <select class="form-select form-select-sm" id="filter_sess_id" name="filter_sess_id">
                                <option value="">-- 不限梯次 --</option>
                                <?php foreach ($sessionOptions as $so): ?>
                                  <option value="<?php echo (int)$so['sess_id']; ?>">
                                    <?php echo htmlspecialchars($so['course_title']); ?> - 第 <?php echo (int)$so['sess_batch']; ?> 梯 (<?php echo $so['sess_startdate']; ?>)
                                  </option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                          </div>
                          <div class="mt-2 small fw-bold text-primary" id="filterPreviewResult"></div>
                        </div>
                      </div>

                      <!-- 對象面板 4：全站 / 等級廣播 -->
                      <div class="col-12 target-panel d-none" id="panel_all">
                        <div class="p-3 bg-warning-subtle border border-warning-subtle rounded-3">
                          <label for="no_minlvl" class="form-label small fw-bold">設定全站公告最低可見會員等級 (no_minlvl)</label>
                          <select class="form-select form-select-sm" id="no_minlvl" name="no_minlvl">
                            <option value="0">0 - 全體會員皆可見（含一般會員）</option>
                            <option value="1">1 - 一般會員以上可見</option>
                            <option value="2">2 - 進階學員以上可見</option>
                            <option value="3">3 - 黃金高階 VIP 以上可見</option>
                            <option value="4">4 - 黑鑽尊享 VIP 專屬可見</option>
                          </select>
                          <div class="form-text small text-dark mt-1">將以 <code>mb_id = NULL</code> 寫入一筆全站分級公告，符合等級的會員登入前台即可看到（此模式不支援 <code>{會員姓名}</code> 變數）。</div>
                        </div>
                      </div>

                      <div class="col-md-7">
                        <label for="no_title" class="form-label small fw-bold">通知主旨標題 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="no_title" name="no_title" placeholder="例如：【活動快訊】人生第五季 12 月生命藝術沙龍開放預約！" required>
                      </div>

                      <div class="col-md-5">
                        <label for="no_link" class="form-label small fw-bold">關聯跳轉網址 (no_link，選填)</label>
                        <input type="text" class="form-control" id="no_link" name="no_link" placeholder="例如：member_courses.php 或 https://...">
                      </div>

                      <div class="col-12">
                        <label for="no_content" class="form-label small fw-bold">
                          通知內文內容 <span class="text-danger">*</span>
                          <span class="text-muted fw-normal ms-1">（個人/群組/條件篩選模式可使用 <code>{會員姓名}</code> 變數）</span>
                        </label>
                        <textarea class="form-control" id="no_content" name="no_content" rows="6" placeholder="親愛的 {會員姓名} 您好，..." required></textarea>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>

  <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
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
    if (parseInt('<?php echo $noID; ?>') > 0) {
      window.parent.toastr['info']('已發送的站內通知僅供檢視或刪除，無法修改內容！');
      return false;
    }
    var frm = $('#form_tabMenu1');
    if (!frm.length) return false;
    if (!validateBS5Form(frm)) {
      window.parent.toastr['warning']('請檢查標示紅框的必填欄位！');
      return false;
    }
    if ($('#target_mode').val() === 'all') {
      if (!confirm('確定要發布全站分級公告通知嗎？')) return false;
    }
    ajaxPOST(frm);
  }

  function doButDelete() {
    if (0 == parseInt('<?php echo $noID; ?>')) return false;
    $.post('../funcFiles/noti_act_func.php', {
      'formAcce': 'func_noti_delete',
      'token': getTokenVal(),
      'noID': '<?php echo $noID; ?>'
    }, function(data) {
      if (data['RTN'] == 1) {
        window.parent.toastr['success'](data['msgStr']);
        window.location.replace("noti_list.php");
      } else {
        window.parent.toastr['error'](data['msgStr'] || '刪除失敗');
      }
    }, 'json');
  }

  function doButReset() { location.reload(true); }

  function ajaxPOST(frm) {
    var $frm = frm || $('#form_tabMenu1');
    var datA = getFormObjects($frm);

    $.ajax({
      type: 'post',
      url: '../funcFiles/noti_act_func.php',
      data: {
        'arrDatA': datA,
        'formAcce': 'func_noti_addnew',
        'token': getTokenVal(),
        'noID': '<?php echo $noID; ?>'
      },
      dataType: 'json',
      success: function(data) {
        if (window.parent && window.parent.toastr) {
          window.parent.toastr[data['msgType']](data['msgStr']);
        }
        if (data['RTN'] == 1) {
          window.location.replace("noti_list.php");
        }
      }
    });
  }

  $(document).ready(function() {
    // 1. 切換接收對象模式面板
    $('#target_mode').on('change', function() {
      const mode = $(this).val();
      $('.target-panel').addClass('d-none');
      if (mode === 'single') $('#panel_single').removeClass('d-none');
      if (mode === 'multi')  $('#panel_multi').removeClass('d-none');
      if (mode === 'filter') $('#panel_filter').removeClass('d-none');
      if (mode === 'all')    $('#panel_all').removeClass('d-none');
    });

    // 2. 試算條件篩選符合會員人數
    $('#btnPreviewFilterCount').on('click', function() {
      $.post('../funcFiles/noti_act_func.php', {
        'formAcce': 'func_noti_previewFilter',
        'token': getTokenVal(),
        'filter_mb_level': $('#filter_mb_level').val(),
        'filter_course_id': $('#filter_course_id').val(),
        'filter_sess_id': $('#filter_sess_id').val()
      }, function(data) {
        if (data['RTN'] == 1) {
          $('#filterPreviewResult').html(`<i class="bi bi-people-fill me-1"></i>目前共有 <strong>${data['count']}</strong> 位有效會員符合上述篩選條件。`);
        } else {
          window.parent.toastr['warning'](data['msgStr']);
        }
      }, 'json');
    });

    // 3. 套用常用通知範本
    $('#noti_tpl_select').on('change', function() {
      const key = $(this).val();
      const tpls = {
        event: {
          cat: '4',
          title: '【活動快訊】人生第五季最新生命藝術沙龍開放報名！',
          link: 'salon.php',
          body: '親愛的 {會員姓名} 您好，\n\n人生第五季最新一期主題沙龍與工作坊已正式開放預約報名！名額有限，誠摯邀請您前來與我們共度美好的自我探索時光。'
        },
        course_remind: {
          cat: '2',
          title: '【課前提醒】您報名的課程即將開課，請確認上課連線資訊',
          link: 'member_courses.php',
          body: '親愛的 {會員姓名} 您好，\n\n提醒您報名的線上課程即將開課，請提前 10 分鐘登入會員中心「我的專屬課程」取得 Zoom 教室進入連結，期待在線上與您相見！'
        },
        points: {
          cat: '4',
          title: '【禮遇通知】您的專屬會員積分與任務好禮已更新',
          link: 'member_quests.php',
          body: '親愛的 {會員姓名} 您好，\n\n感謝您對人生第五季的支持！您的會員積分與每日成長任務獎勵已更新，歡迎隨時前往會員中心查看並兌換專屬好禮。'
        },
        maintenance: {
          cat: '1',
          title: '【系統公告】平台服務優化與維護時間通知',
          link: '',
          body: '親愛的 {會員姓名} 您好，\n\n為提供更流暢的學習與瀏覽體驗，本平台將於本週日凌晨 02:00 至 04:00 進行系統升級維護，期間若短暫無法連線敬請見諒。'
        }
      };
      if (tpls[key]) {
        $('#no_category').val(tpls[key].cat);
        $('#no_title').val(tpls[key].title);
        $('#no_link').val(tpls[key].link);
        $('#no_content').val(tpls[key].body);
      }
    });

    $('li.prevLink').on('click', function() { window.location.replace(getCurrentURI('', 'noti_list.php')); });
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
