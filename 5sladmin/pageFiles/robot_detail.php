<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 定義本頁面代碼（歸屬於訊息管理系統 ITEM_MSG / PG_MSG）
define('PAGE_MARK', 'PG_ROBOT');
define('PAGE_ITEM', 'ITEM_MSG');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$robID = currID;
$res   = ($robID > 0) ? DB->row("SELECT * FROM fsl_robot WHERE rob_id = ? AND rob_status != 9", array($robID)) : array();
if (!is_array($res)) $res = array();

$repliesArr = array();
if (!empty($res['rob_replies'])) {
  $decoded = json_decode((string)$res['rob_replies'], true);
  if (is_array($decoded)) $repliesArr = $decoded;
}

// 新增模式預設提供兩組不同口吻的空白變體卡片
if (empty($repliesArr)) {
  $repliesArr = array(
    array('tone' => '溫暖陪伴型', 'weight' => 50, 'content' => ''),
    array('tone' => '親切專業型', 'weight' => 50, 'content' => '')
  );
}
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
  <title>Fifth Season Life 人生第五季 | <?php echo ($robID > 0) ? '編輯自動應答規則' : '新增自動應答規則'; ?></title>

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
                <?php echo ($robID > 0) ? '編輯自動應答規則' : '新增自動應答規則'; ?>
                <span class="fs-7">【ID: #<?php echo $robID; ?>】</span>
              </h4>
              <h6 class="m-0 fw-light text-muted fs-7 mt-1 lh-xs mb-n2"><i class="bi bi-arrow-return-right"></i> 設定關鍵字觸發條件，並建立多組不同口吻與語氣的應答內容依權重隨機回覆</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item"><a href="msg_list.php">雙向訊息列表</a></li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回知識庫列表">自動應答知識庫</li>
                <li class="breadcrumb-item active"><?php echo ($robID > 0) ? '編輯規則' : '新增規則'; ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <form id="form_tabMenu1" action="../funcFiles/robot_act_func.php" method="post" data-func="func_robot_infoUpdate" novalidate>
            <div class="row">
              <!-- 左側觸發條件與優先權設定 -->
              <div class="col-md-4 mb-3">
                <div class="card card-primary card-outline shadow-sm">
                  <div class="card-header bg-white py-2 fw-bold small">
                    <i class="bi bi-sliders text-primary me-1"></i>觸發條件與比對權重設定
                  </div>
                  <div class="card-body">
                    <div class="mb-3">
                      <label for="rob_topic" class="form-label small fw-bold">問題主題名稱 <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="rob_topic" name="rob_topic" required
                             value="<?php echo htmlspecialchars($res['rob_topic'] ?? ''); ?>" placeholder="例如：課程報名後如何改期或退費">
                    </div>

                    <div class="mb-3">
                      <label for="rob_category" class="form-label small fw-bold">適用訊息分類</label>
                      <select class="form-select" id="rob_category" name="rob_category">
                        <option value="0" <?php echo ((int)($res['rob_category'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 全分類通用</option>
                        <option value="1" <?php echo ((int)($res['rob_category'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 一般諮詢客服</option>
                        <option value="2" <?php echo ((int)($res['rob_category'] ?? 0) === 2) ? 'selected' : ''; ?>>2 - 課程與報名相關</option>
                        <option value="3" <?php echo ((int)($res['rob_category'] ?? 0) === 3) ? 'selected' : ''; ?>>3 - 訂單與帳務問題</option>
                        <option value="4" <?php echo ((int)($res['rob_category'] ?? 0) === 4) ? 'selected' : ''; ?>>4 - Zoom 與影音技術</option>
                      </select>
                    </div>

                    <div class="mb-3">
                      <label for="rob_keywords" class="form-label small fw-bold">觸發關鍵字（以逗號分隔） <span class="text-danger">*</span></label>
                      <textarea class="form-control" id="rob_keywords" name="rob_keywords" rows="3" required
                                placeholder="例如：退費, 退款, 取消報名, 改期, 延梯, 換時間"><?php echo htmlspecialchars($res['rob_keywords'] ?? ''); ?></textarea>
                      <div class="form-text small">支援半形逗號 <code>,</code> 或全形逗號 <code>，</code> 分隔多組同義詞。</div>
                    </div>

                    <div class="row g-2 mb-3">
                      <div class="col-7">
                        <label for="rob_match_mode" class="form-label small fw-bold">比對邏輯模式</label>
                        <select class="form-select form-select-sm" id="rob_match_mode" name="rob_match_mode">
                          <option value="1" <?php echo ((int)($res['rob_match_mode'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 包含任一關鍵字 (OR)</option>
                          <option value="2" <?php echo ((int)($res['rob_match_mode'] ?? 1) === 2) ? 'selected' : ''; ?>>2 - 同時包含全部關鍵字 (AND)</option>
                          <option value="3" <?php echo ((int)($res['rob_match_mode'] ?? 1) === 3) ? 'selected' : ''; ?>>3 - 關鍵字 + 相似度比對</option>
                        </select>
                      </div>
                      <div class="col-5">
                        <label for="rob_priority" class="form-label small fw-bold">優先權重</label>
                        <input type="number" class="form-control form-control-sm" id="rob_priority" name="rob_priority" min="1" max="999"
                               value="<?php echo (int)($res['rob_priority'] ?? 100); ?>" title="數字越小越優先比對">
                      </div>
                    </div>

                    <div class="mb-3">
                      <label for="rob_need_human" class="form-label small fw-bold">自動回覆後處理狀態</label>
                      <select class="form-select form-select-sm" id="rob_need_human" name="rob_need_human">
                        <option value="0" <?php echo ((int)($res['rob_need_human'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 標記「機器人已自動回覆」即可</option>
                        <option value="1" <?php echo ((int)($res['rob_need_human'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 仍維持「待後台人工覆核」提醒</option>
                      </select>
                    </div>

                    <div class="mb-0">
                      <label for="rob_status" class="form-label small fw-bold">規則啟用狀態</label>
                      <select class="form-select form-select-sm" id="rob_status" name="rob_status">
                        <option value="1" <?php echo ((int)($res['rob_status'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 啟用自動應答</option>
                        <option value="0" <?php echo ((int)($res['rob_status'] ?? 1) === 0) ? 'selected' : ''; ?>>0 - 暫停停用</option>
                      </select>
                    </div>

                    <?php if ($robID > 0): ?>
                    <hr>
                    <div class="d-flex justify-content-between small text-muted">
                      <span>累計自動命中次數：</span>
                      <strong class="text-success"><?php echo number_format((int)($res['rob_hit_count'] ?? 0)); ?> 次</strong>
                    </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <!-- 右側多組口吻與語氣變體編輯器 -->
              <div class="col-md-8 mb-3">
                <div class="card card-info card-outline shadow-sm">
                  <div class="card-header bg-white py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span class="fw-bold small text-dark">
                      <i class="bi bi-chat-heart-fill text-danger me-1"></i>多口吻與語氣應答變體（依權重隨機挑選一組回覆）
                    </span>
                    <div class="d-flex gap-2">
                      <button type="button" class="btn btn-outline-secondary btn-xs" id="btnAddTonePreset">
                        <i class="bi bi-stars me-1"></i>補齊三種推薦語氣組
                      </button>
                      <button type="button" class="btn btn-primary btn-xs fw-bold" id="btnAddReplyVariant">
                        <i class="bi bi-plus-lg me-1"></i>新增一組語氣變體
                      </button>
                    </div>
                  </div>
                  <div class="card-body bg-light">
                    <div class="alert alert-info py-2 px-3 small mb-3">
                      <i class="bi bi-info-circle-fill me-1"></i>
                      <strong>可用個人化變數：</strong>
                      <code>{會員姓名}</code>（自動帶入解密後的學員姓名）、<code>{日期}</code>（當天日期）。建議設定 2~4 組語氣相近但用詞略有不同的回覆，避免制式感。
                    </div>

                    <div id="replyVariantsContainer">
                      <?php foreach ($repliesArr as $idx => $rep): ?>
                      <div class="card border shadow-sm mb-3 reply-variant-card">
                        <div class="card-header bg-white py-2 d-flex flex-wrap align-items-center gap-2">
                          <span class="badge text-bg-primary variant-num">#<?php echo $idx + 1; ?></span>
                          <div class="input-group input-group-sm" style="max-width: 240px;">
                            <span class="input-group-text">口吻標籤</span>
                            <input type="text" class="form-control inp-tone" value="<?php echo htmlspecialchars($rep['tone'] ?? ''); ?>" placeholder="如：溫暖陪伴型">
                          </div>
                          <div class="input-group input-group-sm" style="max-width: 150px;">
                            <span class="input-group-text">抽樣權重</span>
                            <input type="number" class="form-control inp-weight" min="1" max="100" value="<?php echo max(1, (int)($rep['weight'] ?? 50)); ?>">
                          </div>
                          <button type="button" class="btn btn-outline-danger btn-xs ms-auto btn-remove-variant" title="刪除此組變體">
                            <i class="bi bi-trash"></i> 移除
                          </button>
                        </div>
                        <div class="card-body p-2">
                          <textarea class="form-control form-control-sm inp-content" rows="4" placeholder="輸入此口吻的完整回覆內容..."><?php echo htmlspecialchars($rep['content'] ?? ''); ?></textarea>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="rob_replies" name="rob_replies" value="">
                  </div>
                </div>
              </div>
            </div>
          </form>
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
  function refreshVariantNumbers() {
    $('.reply-variant-card').each(function(i) {
      $(this).find('.variant-num').text('#' + (i + 1));
    });
  }

  function buildVariantCardHtml(tone, weight, content) {
    const safeTone = $('<div>').text(tone || '').html();
    const safeContent = $('<div>').text(content || '').html();
    return `
      <div class="card border shadow-sm mb-3 reply-variant-card">
        <div class="card-header bg-white py-2 d-flex flex-wrap align-items-center gap-2">
          <span class="badge text-bg-primary variant-num">#</span>
          <div class="input-group input-group-sm" style="max-width: 240px;">
            <span class="input-group-text">口吻標籤</span>
            <input type="text" class="form-control inp-tone" value="${safeTone}" placeholder="如：溫暖陪伴型">
          </div>
          <div class="input-group input-group-sm" style="max-width: 150px;">
            <span class="input-group-text">抽樣權重</span>
            <input type="number" class="form-control inp-weight" min="1" max="100" value="${weight || 50}">
          </div>
          <button type="button" class="btn btn-outline-danger btn-xs ms-auto btn-remove-variant" title="刪除此組變體">
            <i class="bi bi-trash"></i> 移除
          </button>
        </div>
        <div class="card-body p-2">
          <textarea class="form-control form-control-sm inp-content" rows="4" placeholder="輸入此口吻的完整回覆內容...">${safeContent}</textarea>
        </div>
      </div>
    `;
  }

  function collectRepliesJson() {
    const list = [];
    $('.reply-variant-card').each(function() {
      const tone    = $.trim($(this).find('.inp-tone').val()) || '標準應答';
      const weight  = Math.max(1, parseInt($(this).find('.inp-weight').val(), 10) || 10);
      const content = $.trim($(this).find('.inp-content').val());
      if (content !== '') {
        list.push({ tone: tone, weight: weight, content: content });
      }
    });
    $('#rob_replies').val(JSON.stringify(list));
    return list;
  }

  function validateBS5Form($form) {
    if (!$form || $form.length === 0) return true;
    const formEl = $form;
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
    const variants = collectRepliesJson();
    if (variants.length === 0) {
      window.parent.toastr['warning']('請至少填寫一組有效的自動應答回覆內容！');
      return false;
    }
    ajaxPOST(frm);
  }

  function doButDelete() {
    if (0 == parseInt('<?php echo $robID; ?>')) return false;
    $.post('../funcFiles/robot_act_func.php', {
      'formAcce': 'func_robot_delete',
      'token': getTokenVal(),
      'robID': '<?php echo $robID; ?>'
    }, function(data) {
      if (data['RTN'] == 1) {
        window.parent.toastr['success'](data['msgStr']);
        window.location.replace("robot_list.php");
      } else {
        window.parent.toastr['error'](data['msgStr'] || '刪除失敗');
      }
    }, 'json');
  }

  function doButReset() { location.reload(true); }

  function ajaxPOST(frm) {
    var $frm = frm || $('#form_tabMenu1');
    var accE = (0 == parseInt('<?php echo $robID; ?>')) ? 'func_robot_addnew' : $frm.data('func');
    var datA = getFormObjects($frm);

    $.ajax({
      type: 'post',
      url: '../funcFiles/robot_act_func.php',
      data: {
        'arrDatA': datA,
        'formAcce': accE,
        'token': getTokenVal(),
        'robID': '<?php echo $robID; ?>'
      },
      dataType: 'json',
      success: function(data) {
        if (window.parent && window.parent.toastr) {
          window.parent.toastr[data['msgType']](data['msgStr']);
        }
        if (data['RTN'] == 1) {
          if (accE == 'func_robot_addnew' && data['newID']) {
            window.location.replace("robot_detail.php?currID=" + data['newID']);
          } else {
            $frm.removeClass('was-validated');
            if (typeof doSerialize === 'function') doSerialize();
          }
        }
      }
    });
  }

  $(document).ready(function() {
    refreshVariantNumbers();

    // 新增空白語氣變體卡片
    $('#btnAddReplyVariant').on('click', function() {
      $('#replyVariantsContainer').append(buildVariantCardHtml('親切引導型', 50, ''));
      refreshVariantNumbers();
    });

    // 一鍵補齊三種推薦語氣組
    $('#btnAddTonePreset').on('click', function() {
      const presets = [
        { tone: '溫暖陪伴型', weight: 40, content: '親愛的 {會員姓名} 您好，非常感謝您的來訊與信任！關於您提到的問題，我們為您說明如下：\n\n（請在此填寫溫暖細膩的解答內容）\n\n若還有任何需要陪伴或協助的地方，歡迎隨時回覆告訴我們喔！' },
        { tone: '親切專業型', weight: 40, content: '{會員姓名} 您好，歡迎來到人生第五季！針對您的詢問，為您整理相關處理方式如下：\n\n（請在此填寫條列式清晰解答）\n\n如有其他疑問，我們的服務團隊隨時樂意為您服務。' },
        { tone: '簡潔俐落型', weight: 20, content: '您好 {會員姓名}，收到您的訊息了！相關資訊提供給您參考：\n\n（請在此填寫精簡重點回覆）\n\n祝您順心愉快！' }
      ];
      $('#replyVariantsContainer').empty();
      presets.forEach(p => {
        $('#replyVariantsContainer').append(buildVariantCardHtml(p.tone, p.weight, p.content));
      });
      refreshVariantNumbers();
      window.parent.toastr['info']('已為您帶入三種推薦語氣範本，請依實際問題修改括號內說明後儲存！');
    });

    // 移除單組語氣變體
    $(document).on('click', '.btn-remove-variant', function() {
      if ($('.reply-variant-card').length <= 1) {
        window.parent.toastr['warning']('至少需保留一組應答變體卡片！');
        return;
      }
      $(this).closest('.reply-variant-card').remove();
      refreshVariantNumbers();
    });

    $('li.prevLink').on('click', function() { window.location.replace(getCurrentURI('', 'robot_list.php')); });
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
