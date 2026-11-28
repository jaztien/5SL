<?php
/**
 * 共用課程梯次設定表單元件 (供 sess_detail.php 與 cs_detail.php 彈窗共用)
 * 外部需提供變數：$res, $masterCourses, $courseID, $sessID, $onWeekArr
 * 若為彈窗模式，外部設定 $isModalSessForm = true;
 */
$res           = isset($res) && is_array($res) ? $res : array();
$masterCourses = isset($masterCourses) && is_array($masterCourses) ? $masterCourses : array();
$courseID      = (int)($courseID ?? 0);
$sessID        = (int)($sessID ?? 0);
$onWeekArr     = isset($onWeekArr) && is_array($onWeekArr) ? $onWeekArr : array();

$isModalSessForm = !empty($isModalSessForm);
$formIdAttr      = $isModalSessForm ? 'form_modal_session' : 'form_tabMenu1';
?>
<form id="<?php echo $formIdAttr; ?>" action="../funcFiles/sess_act_func.php" method="post" data-func="func_sess_infoUpdate" novalidate>
  <div class="row g-3">
    <div class="col-md-5">
      <label class="form-label small fw-bold">所屬主課程 <span class="text-danger">*</span></label>
      <select class="form-select" id="course_id" name="course_id" required <?php echo $isModalSessForm ? 'readonly' : ''; ?>>
        <option value="">-- 請選擇主課程 --</option>
        <?php foreach ($masterCourses as $mc): ?>
          <option value="<?php echo (int)$mc['course_id']; ?>" <?php echo ((int)$courseID === (int)$mc['course_id']) ? 'selected' : ''; ?>>
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
            <input class="form-check-input chk-onweek" type="checkbox" value="<?php echo $wKey; ?>" id="wk_<?php echo ($isModalSessForm ? 'm_' : '') . $wKey; ?>" name="wk_<?php echo $wKey; ?>" <?php echo $chk; ?>>
            <label class="form-check-label small" for="wk_<?php echo ($isModalSessForm ? 'm_' : '') . $wKey; ?>"><?php echo $wName; ?></label>
          </div>
        <?php endforeach; ?>
      </div>
      <input type="hidden" name="sess_onweek" id="sess_onweek" value="<?php echo htmlspecialchars($res['sess_onweek'] ?? ''); ?>">
    </div>

    <div class="col-12">
      <div class="hr-more">Zoom 會議間建立與連線憑證</div>
      <hr class="my-1">
    </div>

    <div class="col-md-4">
      <label class="form-label small fw-bold">Zoom 會議室 ID (sess_meetingid)</label>
      <div class="input-group">
        <input type="text" class="form-control" id="sess_meetingid" name="sess_meetingid"
          value="<?php echo htmlspecialchars($res['sess_meetingid'] ?? ''); ?>" placeholder="例如：892 1045 6678">
        <?php if (!$isModalSessForm && (int)$sessID > 0): ?>
          <button type="button" class="btn btn-outline-primary btn-sm" id="btnAutoZoomRoom" title="一鍵建立 Zoom 雲端會議室">
            <i class="bi bi-magic"></i> 建立
          </button>
        <?php endif; ?>
      </div>
      <?php if ((int)$sessID === 0): ?>
        <div class="form-check mt-1">
          <input class="form-check-input" type="checkbox" id="auto_create_zoom" name="auto_create_zoom" value="1" checked>
          <label class="form-check-label small text-primary fw-bold" for="auto_create_zoom">儲存梯次時自動呼叫 Zoom API 建立雲端會議室</label>
        </div>
      <?php endif; ?>
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

    <div class="col-12">
      <div class="hr-more">Zoom 會議室安全限制與課間預設參數</div>
      <hr class="my-1">
    </div>

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
