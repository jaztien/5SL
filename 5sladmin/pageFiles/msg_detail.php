<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 定義本頁面代碼
define('PAGE_MARK', 'PG_MSG');
define('PAGE_ITEM', 'ITEM_MSG');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$msgID = currID;
$res   = ($msgID > 0) ? DB->row("
  SELECT m.*,
         mb.mb_name AS sender_mb_name, mb.mb_email AS sender_mb_email, mb.mb_cellphone AS sender_mb_phone,
         ad.ad_name AS sender_ad_name
  FROM fsl_messages m
  LEFT JOIN fsl_members mb ON (m.sender_type = 1 AND m.sender_id = mb.mb_id)
  LEFT JOIN fsl_administrator ad ON (m.sender_type = 2 AND m.sender_id = ad.ad_id)
  WHERE m.msg_id = ? AND m.parent_id = 0 AND m.msg_status != 9
", array($msgID)) : array();
if (!is_array($res)) $res = array();

// 對話時間軸所有訊息（首則 + 所有回覆）
$threadList = array();
$readList   = array();
if ($msgID > 0 && !empty($res)) {
  $threadList[] = $res;
  $replies = DB->query("
    SELECT r.*,
           mb.mb_name AS sender_mb_name,
           ad.ad_name AS sender_ad_name,
           rb.rob_topic AS robot_topic
    FROM fsl_messages r
    LEFT JOIN fsl_members mb ON (r.sender_type = 1 AND r.sender_id = mb.mb_id)
    LEFT JOIN fsl_administrator ad ON (r.sender_type = 2 AND r.sender_id = ad.ad_id)
    LEFT JOIN fsl_robot rb ON (r.sender_type = 3 AND r.sender_id = rb.rob_id)
    WHERE r.parent_id = ? AND r.msg_status = 1
    ORDER BY r.msg_created ASC, r.msg_id ASC
  ", array($msgID));
  if (is_array($replies)) {
    $threadList = array_merge($threadList, $replies);
  }

  // 查詢已讀名單
  $readList = DB->query("
    SELECT rd.*, mb.mb_name, mb.mb_email
    FROM fsl_message_reads rd
    LEFT JOIN fsl_members mb ON rd.mb_id = mb.mb_id
    WHERE rd.msg_id = ? AND rd.is_read = 1
    ORDER BY rd.read_at DESC
    LIMIT 100
  ", array($msgID));
  if (!is_array($readList)) $readList = array();
}

// 新增發信模式所需的會員、課程、梯次選單
$memberOptions  = array();
$masterCourses  = array();
$sessionOptions = array();

if ($msgID === 0) {
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
  1 => '一般諮詢客服',
  2 => '課程與報名相關',
  3 => '訂單與帳務問題',
  4 => '官方分眾公告',
  5 => '顧問專屬關懷'
);
$replyStaLabels = array(
  0 => array('text' => '待後台人工回覆', 'cls' => 'text-danger fw-bold'),
  1 => array('text' => '後台已回覆', 'cls' => 'text-success fw-bold'),
  2 => array('text' => '機器人已自動回覆', 'cls' => 'text-info-emphasis fw-bold'),
  3 => array('text' => '已結案歸檔', 'cls' => 'text-secondary fw-bold')
);
$currReplySta = (int)($res['msg_reply_status'] ?? 0);
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
  <title>Fifth Season Life 人生第五季 | <?php echo ($msgID > 0) ? '雙向訊息對話中控' : '發送分眾與專屬訊息'; ?></title>

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
                <?php echo ($msgID > 0) ? '雙向訊息對話串與狀態管理' : '撰寫並發送新訊息至前台'; ?>
                <span class="fs-7">【ID: #<?php echo $msgID; ?>】</span>
              </h4>
              <h6 class="m-0 fw-light text-muted fs-7 mt-1 lh-xs mb-n2"><i class="bi bi-arrow-return-right"></i> 支援單一會員、多選名單與條件篩選分眾發送，並完整留存前後台往來對話</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">訊息管理系統</li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回列表">雙向訊息列表</li>
                <li class="breadcrumb-item active"><?php echo ($msgID > 0) ? '對話串詳情' : '發送新訊息'; ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <?php if ($msgID > 0 && !empty($res)): ?>
          <!-- =============================================================== -->
          <!-- 模式 A：檢視既有對話串與人工回覆 (currID > 0)                     -->
          <!-- =============================================================== -->
          <div class="row">
            <!-- 左側對話資訊與狀態控制卡 -->
            <div class="col-md-3 mb-3">
              <div class="card card-primary card-outline shadow-sm">
                <div class="card-body small">
                  <div class="text-center mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary mb-2" style="width: 60px; height: 60px;">
                      <i class="bi bi-chat-square-text-fill fs-3"></i>
                    </div>
                    <h6 class="fw-bold mb-0"><?php echo $catLabels[(int)$res['msg_category']] ?? '訊息對話'; ?></h6>
                    <div class="text-muted mt-1" style="font-size: 0.75rem;">建立於 <?php echo $res['msg_created']; ?></div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-bold text-muted mb-1">切換對話處理狀態</label>
                    <select class="form-select form-select-sm" id="selReplyStatus">
                      <option value="0" <?php echo ($currReplySta === 0) ? 'selected' : ''; ?>>0 - 待後台人工回覆</option>
                      <option value="2" <?php echo ($currReplySta === 2) ? 'selected' : ''; ?>>2 - 機器人已自動回覆</option>
                      <option value="1" <?php echo ($currReplySta === 1) ? 'selected' : ''; ?>>1 - 後台已回覆</option>
                      <option value="3" <?php echo ($currReplySta === 3) ? 'selected' : ''; ?>>3 - 已結案歸檔</option>
                    </select>
                  </div>

                  <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="chkMsgPinned" name="chkMsgPinned" <?php echo !empty($res['msg_is_pinned']) ? 'checked' : ''; ?>>
                    <label class="form-check-label fw-bold" for="chkMsgPinned">置頂此則訊息/公告</label>
                  </div>

                  <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>目前狀態</b>
                      <span class="<?php echo $replyStaLabels[$currReplySta]['cls'] ?? ''; ?>"><?php echo $replyStaLabels[$currReplySta]['text'] ?? '-'; ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>對象模式</b>
                      <span class="fw-semibold"><?php echo htmlspecialchars($res['target_mode']); ?></span>
                    </li>
                    <?php if ((int)$res['sender_type'] === 1):
                      $mName = !empty($res['sender_mb_name']) ? _fn_secure_crypto($res['sender_mb_name'], 'de') : '會員 #' . $res['sender_id'];
                    ?>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>發問會員</b>
                      <a href="mb_detail.php?currID=<?php echo (int)$res['sender_id']; ?>" class="fw-bold text-decoration-none"><?php echo htmlspecialchars($mName); ?></a>
                    </li>
                    <?php endif; ?>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>已讀人數</b>
                      <span class="text-success fw-bold"><?php echo count($readList); ?> 人</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- 右側對話氣泡時間軸與回覆輸入區 -->
            <div class="col-md-9">
              <div class="card shadow-sm mb-3">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                  <span class="fw-bold text-dark fs-6"><i class="bi bi-chat-dots-fill text-primary me-2"></i><?php echo htmlspecialchars($res['msg_title']); ?></span>
                  <span class="badge text-bg-light border">共 <?php echo count($threadList); ?> 則對話紀錄</span>
                </div>
                <div class="card-body bg-light" style="max-height: 520px; overflow-y: auto;">
                  <?php foreach ($threadList as $idx => $item):
                    $sType = (int)$item['sender_type'];
                    $isAdminOrBot = ($sType === 2 || $sType === 3);

                    if ($sType === 1) {
                      $whoName = !empty($item['sender_mb_name']) ? _fn_secure_crypto($item['sender_mb_name'], 'de') : '會員 #' . $item['sender_id'];
                      $badgeHtml = '<span class="badge text-bg-info text-dark me-1">前台會員</span>';
                      $cardCls = 'bg-white border';
                    } elseif ($sType === 3) {
                      $toneStr = str_replace('robot_tone:', '', (string)($item['msg_attachment'] ?? ''));
                      $whoName = '自動應答機器人' . (!empty($item['robot_topic']) ? "（規則：{$item['robot_topic']}）" : '');
                      $badgeHtml = '<span class="badge text-bg-warning text-dark me-1"><i class="bi bi-robot me-1"></i>自動應答</span>'
                                 . ($toneStr !== '' ? '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle me-1">語氣：' . htmlspecialchars($toneStr) . '</span>' : '');
                      $cardCls = 'bg-warning-subtle border border-warning';
                    } else {
                      $whoName = $item['sender_ad_name'] ?: ('管理員 #' . $item['sender_id']);
                      $badgeHtml = '<span class="badge text-bg-primary me-1"><i class="bi bi-shield-check me-1"></i>後台團隊</span>';
                      $cardCls = 'bg-primary-subtle border border-primary-subtle';
                    }
                  ?>
                  <div class="d-flex flex-column <?php echo $isAdminOrBot ? 'align-items-end' : 'align-items-start'; ?> mb-3">
                    <div class="small text-muted mb-1">
                      <?php echo $badgeHtml; ?>
                      <strong><?php echo htmlspecialchars($whoName); ?></strong>
                      <span class="ms-2"><?php echo $item['msg_created']; ?></span>
                    </div>
                    <div class="p-3 rounded-3 shadow-sm <?php echo $cardCls; ?>" style="max-width: 85%; width: fit-content;">
                      <div class="small text-dark" style="line-height: 1.65;"><?php echo nl2br(htmlspecialchars((string)$item['msg_content'])); ?></div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>

                <!-- 後台人工回覆表單 -->
                <div class="card-footer bg-white p-3">
                  <form id="form_tabMenu1" action="../funcFiles/msg_act_func.php" method="post" data-func="func_msg_reply" novalidate>
                    <label for="reply_content" class="form-label small fw-bold text-primary">
                      <i class="bi bi-reply-fill me-1"></i>撰寫後台人工回覆內容（送出後將同步通知會員並標記為「後台已回覆」）
                    </label>
                    <textarea class="form-control mb-2" id="reply_content" name="reply_content" rows="3" placeholder="輸入要回覆給會員的詳細說明..." required></textarea>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                      <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="also_notify" name="also_notify" value="1" checked>
                        <label class="form-check-label small text-muted" for="also_notify">同時寫入會員小鈴鐺通知中心 (fsl_notifications)</label>
                      </div>
                      <button type="button" class="btn btn-primary btn-sm fw-bold px-3" id="btnSendReplyNow">
                        <i class="bi bi-send-fill me-1"></i>立即送出回覆
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <!-- 已讀會員名單（若為分眾訊息或已閱讀） -->
              <?php if (!empty($readList)): ?>
              <div class="card shadow-sm">
                <div class="card-header bg-light py-2 fw-bold small">
                  <i class="bi bi-eye-fill text-success me-1"></i>已閱讀會員紀錄（共 <?php echo count($readList); ?> 位）
                </div>
                <div class="card-body py-2">
                  <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($readList as $rd):
                      $rName = !empty($rd['mb_name']) ? _fn_secure_crypto($rd['mb_name'], 'de') : '會員 #' . $rd['mb_id'];
                    ?>
                    <span class="badge bg-light text-dark border py-1 px-2">
                      <?php echo htmlspecialchars($rName); ?>
                      <span class="text-muted ms-1">(<?php echo substr((string)$rd['read_at'], 5, 11); ?>)</span>
                    </span>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <?php else: ?>
          <!-- =============================================================== -->
          <!-- 模式 B：後台主動發送新訊息 / 分眾公告給前台 (currID = 0)          -->
          <!-- =============================================================== -->
          <div class="row justify-content-center">
            <div class="col-lg-10">
              <div class="card card-primary card-outline shadow-lg">
                <div class="card-header bg-white py-2">
                  <span class="fw-bold text-primary"><i class="bi bi-send-plus-fill me-1"></i>建立前台訊息 / 分眾公告推播</span>
                </div>
                <div class="card-body">
                  <form id="form_tabMenu1" action="../funcFiles/msg_act_func.php" method="post" data-func="func_msg_addnew" novalidate>
                    <div class="row g-3">
                      <div class="col-md-4">
                        <label for="msg_category" class="form-label small fw-bold">訊息分類 <span class="text-danger">*</span></label>
                        <select class="form-select" id="msg_category" name="msg_category" required>
                          <option value="4">4 - 官方分眾公告</option>
                          <option value="5">5 - 顧問專屬關懷</option>
                          <option value="2">2 - 課程與報名相關</option>
                          <option value="3">3 - 訂單與帳務問題</option>
                          <option value="1">1 - 一般諮詢客服</option>
                        </select>
                      </div>
                      <div class="col-md-5">
                        <label for="target_mode" class="form-label small fw-bold">發送對象模式 <span class="text-danger">*</span></label>
                        <select class="form-select" id="target_mode" name="target_mode" required>
                          <option value="single">single - 單一指定會員</option>
                          <option value="multi">multi - 多筆指定會員（複選）</option>
                          <option value="filter">filter - 條件篩選會員（等級/課程/梯次）</option>
                          <option value="all">all - 全體有效會員廣播</option>
                        </select>
                      </div>
                      <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                          <input class="form-check-input" type="checkbox" id="msg_is_pinned" name="msg_is_pinned" data-unchecked="0" value="1">
                          <label class="form-check-label small fw-bold" for="msg_is_pinned">置頂顯示於訊息中心</label>
                        </div>
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

                      <!-- 對象面板 2：多筆指定會員 -->
                      <div class="col-12 target-panel d-none" id="panel_multi">
                        <div class="p-3 bg-light border rounded-3">
                          <label for="multi_mb_ids" class="form-label small fw-bold">按住 Ctrl / Command 複選多位接收會員 <span class="text-danger">*</span></label>
                          <select class="form-select" id="multi_mb_ids" name="multi_mb_ids[]" multiple size="6">
                            <?php foreach ($memberOptions as $mo): ?>
                              <option value="<?php echo $mo['mb_id']; ?>"><?php echo htmlspecialchars($mo['label']); ?></option>
                            <?php endforeach; ?>
                          </select>
                          <div class="form-text small">已支援多選，或於下方輸入逗號分隔的會員 ID（如：<code>12, 18, 25</code>）：</div>
                          <input type="text" class="form-control form-control-sm mt-1" id="multi_mb_text" name="multi_mb_text" placeholder="選填：直接貼上會員 ID 列表，以半形逗號分隔">
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
                              <label for="filter_mb_level" class="form-label small fw-bold mb-1">會員等級 (mb_level)</label>
                              <select class="form-select form-select-sm" id="filter_mb_level" name="filter_mb_level">
                                <option value="">-- 不限會員等級 --</option>
                                <option value="1">1 - 一般會員</option>
                                <option value="2">2 - 進階學員</option>
                                <option value="3">3 - VIP 會員</option>
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

                      <div class="col-12">
                        <label for="msg_title" class="form-label small fw-bold">訊息主旨 / 標題 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="msg_title" name="msg_title" placeholder="輸入清楚明確的訊息標題..." required>
                      </div>

                      <div class="col-12">
                        <label for="msg_content" class="form-label small fw-bold">訊息內文 <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="msg_content" name="msg_content" rows="6" placeholder="輸入要發送給會員的訊息內容..." required></textarea>
                      </div>

                      <div class="col-12">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" id="also_notify_new" name="also_notify_new" data-unchecked="0" value="1" checked>
                          <label class="form-check-label small fw-bold text-secondary" for="also_notify_new">
                            同步推播「新訊息提醒」至接收會員的小鈴鐺通知中心（<code>fsl_notifications</code>，單一/多選/條件篩選人數 &le; 200 人時自動發送）
                          </label>
                        </div>
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
    var frm = $('#form_tabMenu1');
    if (!frm.length) return false;
    if (!validateBS5Form(frm)) {
      window.parent.toastr['warning']('請檢查標示紅框的必填欄位！');
      return false;
    }
    ajaxPOST(frm);
  }

  function doButDelete() {
    if (0 == parseInt('<?php echo $msgID; ?>')) return false;
    $.post('../funcFiles/msg_act_func.php', {
      'formAcce': 'func_msg_delete',
      'token': getTokenVal(),
      'msgID': '<?php echo $msgID; ?>'
    }, function(data) {
      if (data['RTN'] == 1) {
        window.parent.toastr['success'](data['msgStr']);
        window.location.replace("msg_list.php");
      } else {
        window.parent.toastr['error'](data['msgStr'] || '刪除失敗');
      }
    }, 'json');
  }

  function doButReset() { location.reload(true); }

  function ajaxPOST(frm) {
    var $frm = frm || $('#form_tabMenu1');
    var accE = (0 == parseInt('<?php echo $msgID; ?>')) ? 'func_msg_addnew' : $frm.data('func');
    var datA = getFormObjects($frm);

    $.ajax({
      type: 'post',
      url: '../funcFiles/msg_act_func.php',
      data: {
        'arrDatA': datA,
        'formAcce': accE,
        'token': getTokenVal(),
        'msgID': '<?php echo $msgID; ?>'
      },
      dataType: 'json',
      success: function(data) {
        if (window.parent && window.parent.toastr) {
          window.parent.toastr[data['msgType']](data['msgStr']);
        }
        if (data['RTN'] == 1) {
          if (accE == 'func_msg_addnew' && data['newID']) {
            window.location.replace("msg_detail.php?currID=" + data['newID']);
          } else {
            location.reload(true);
          }
        }
      }
    });
  }

  $(document).ready(function() {
    // 1. 切換發送對象模式顯示對應面板
    $('#target_mode').on('change', function() {
      const mode = $(this).val();
      $('.target-panel').addClass('d-none');
      if (mode === 'single') $('#panel_single').removeClass('d-none');
      if (mode === 'multi')  $('#panel_multi').removeClass('d-none');
      if (mode === 'filter') $('#panel_filter').removeClass('d-none');
    });

    // 2. 試算條件篩選符合會員人數
    $('#btnPreviewFilterCount').on('click', function() {
      $.post('../funcFiles/msg_act_func.php', {
        'formAcce': 'func_msg_previewFilter',
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

    // 3. 模式 A：立即送出人工回覆按鈕
    $('#btnSendReplyNow').on('click', function() {
      doButSave();
    });

    // 4. 模式 A：即時切換對話處理狀態
    $('#selReplyStatus').on('change', function() {
      $.post('../funcFiles/msg_act_func.php', {
        'formAcce': 'func_msg_statusUpdate',
        'token': getTokenVal(),
        'msgID': '<?php echo $msgID; ?>',
        'replyStatus': $(this).val()
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 5. 模式 A：切換置頂狀態
    $('#chkMsgPinned').on('change', function() {
      $.post('../funcFiles/msg_act_func.php', {
        'formAcce': 'func_msg_pinToggle',
        'token': getTokenVal(),
        'msgID': '<?php echo $msgID; ?>',
        'isPinned': $(this).is(':checked') ? 1 : 0
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
      }, 'json');
    });

    $('li.prevLink').on('click', function() { window.location.replace(getCurrentURI('', 'msg_list.php')); });
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
