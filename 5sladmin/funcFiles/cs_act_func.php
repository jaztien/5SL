<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');

header('Content-Type: application/json; charset=utf-8');

// 1. 驗證管理者登入 Session 與 CSRF Token
if (empty($_SESSION['SESS_AD_ID'])) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '登入已逾時，請重新登入後台系統！']);
  exit();
}

$token    = $_POST['token'] ?? '';
$formAcce = trim((string)($_POST['formAcce'] ?? ''));
$formData = (array)($_POST['arrDatA'] ?? array());
$csID     = (int)($_POST['csID'] ?? 0);
$imgUp    = trim((string)($_POST['imgUp'] ?? ''));

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

switch ($formAcce) {

  // =========================================================================
  // 1. 新增 / 更新課程與價格方案 (func_cs_addnew / func_cs_infoUpdate)
  // =========================================================================
  case 'func_cs_addnew':
  case 'func_cs_infoUpdate':
    $acc = ($formAcce === 'func_cs_addnew') ? 'add' : 'up';

    if (empty(trim((string)($formData['course_title'] ?? '')))) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請填寫課程或價格卡片主標題！']);
      exit();
    }

    $upTime = date('Y-m-d H:i:s');
    $formData['parent_id']           = (int)($formData['parent_id'] ?? 0);
    $formData['course_show_pricing'] = (int)($formData['course_show_pricing'] ?? 1);
    $formData['course_is_hot']       = (int)($formData['course_is_hot'] ?? 0);
    $formData['course_sort']         = (int)($formData['course_sort'] ?? 100);
    $formData['course_type']         = (int)($formData['course_type'] ?? 1);
    $formData['course_cost']         = (float)($formData['course_cost'] ?? 0);
    $formData['course_lesson']       = max(1, (int)($formData['course_lesson'] ?? 1));
    $formData['course_duration']     = max(15, (int)($formData['course_duration'] ?? 60));
    $formData['course_max']          = max(1, (int)($formData['course_max'] ?? 20));
    $formData['course_status']       = (int)($formData['course_status'] ?? 1);
    $formData['course_startdate']    = !empty($formData['course_startdate']) ? $formData['course_startdate'] : date('Y-m-d');
    $formData['course_starttime']    = !empty($formData['course_starttime']) ? $formData['course_starttime'] : '14:00:00';

    if ($acc === 'add') {
      $formData['course_published'] = $upTime;
      $formData['course_ribbon']    = '';
      $res   = DB->insert('fsl_courses', $formData);
      $newID = (int)DB->lastInsertId();
      $targetID = $newID;
    } else {
      if ($csID <= 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的課程 ID！']);
        exit();
      }
      if ($formData['parent_id'] === $csID) {
        echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '方案不可關聯自己作為主課程！']);
        exit();
      }
      $formData['course_updated'] = $upTime;
      $res      = DB->update('fsl_courses', $formData, ['course_id' => $csID]);
      $targetID = $csID;
    }

    // 處理課程封面裁切圖上傳
    $imgMsg = '';
    if ($targetID > 0 && $imgUp !== '') {
      if (!is_dir('../uploadFiles/cs/')) {
        @mkdir('../uploadFiles/cs/', 0755, true);
      }
      $imgOk = _fn_image_upload($imgUp, '../uploadFiles/cs/cimg_' . $targetID, 800, 600);
      if ($imgOk) {
        $imgMsg = '並同步更新封面圖！';
      }
    }

    $rtnArr = array(
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '課程與價格方案資料已成功' . ($acc === 'add' ? '建立' : '更新') . '！' . $imgMsg,
      'upTime'  => $upTime
    );
    if ($acc === 'add') {
      $rtnArr['newID'] = $newID;
    }
    echo json_encode($rtnArr);
    exit();
  break;

  // =========================================================================
  // 2. 軟刪除課程方案 (func_cs_delete)
  // =========================================================================
  case 'func_cs_delete':
    if ($csID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的課程 ID！']);
      exit();
    }
    DB->update('fsl_courses', ['course_status' => 9], ['course_id' => $csID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '課程方案已成功刪除封存！']);
    exit();
  break;

  // =========================================================================
  // 3. 開課梯次管理：新增 / 切換狀態 / 刪除 (fsl_course_sessions)
  // =========================================================================
  case 'func_sess_add':
    $masterID  = (int)($_POST['masterID'] ?? 0);
    $batch     = max(1, (int)($_POST['sess_batch'] ?? 1));
    $startDate = trim((string)($_POST['sess_startdate'] ?? ''));
    $startTime = trim((string)($_POST['sess_starttime'] ?? '14:00:00'));
    $location  = trim((string)($_POST['sess_location'] ?? ''));

    if ($masterID <= 0 || $startDate === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫開課日期與梯次編號！']);
      exit();
    }

    $insSess = array(
      'course_id'      => $masterID,
      'sess_batch'     => $batch,
      'sess_startdate' => $startDate,
      'sess_starttime' => $startTime,
      'sess_location'  => ($location !== '' ? $location : '線上視訊會議室'),
      'sess_enrollee'  => 0,
      'sess_status'    => 1
    );
    DB->insert('fsl_course_sessions', $insSess);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => "第 {$batch} 梯次已成功新增！"]);
    exit();
  break;

  case 'func_sess_status':
    $sessID = (int)($_POST['sessID'] ?? 0);
    $status = (int)($_POST['status'] ?? 1);
    if ($sessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的梯次 ID！']);
      exit();
    }
    DB->update('fsl_course_sessions', ['sess_status' => $status], ['sess_id' => $sessID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '梯次開放狀態已更新！']);
    exit();
  break;

  case 'func_sess_delete':
    $sessID = (int)($_POST['sessID'] ?? 0);
    if ($sessID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的梯次 ID！']);
      exit();
    }
    $enrolled = (int)DB->single("SELECT sess_enrollee FROM fsl_course_sessions WHERE sess_id = ?", array($sessID));
    if ($enrolled > 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => "此梯次已有 {$enrolled} 位學員報名，無法直接刪除，請改用「切換狀態」關閉報名！"]);
      exit();
    }
    DB->update('fsl_course_sessions', ['sess_status' => 9], ['sess_id' => $sessID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '開課梯次已刪除！']);
    exit();
  break;

  // =========================================================================
  // 4. 更新參與學員修課進度狀態 (fsl_course_enrolls)
  // =========================================================================
  case 'func_enroll_status':
    $enrID     = (int)($_POST['enrID'] ?? 0);
    $enrStatus = (int)($_POST['enrStatus'] ?? 1);
    if ($enrID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的報名紀錄 ID！']);
      exit();
    }
    DB->update('fsl_course_enrolls', ['enr_status' => $enrStatus], ['enr_id' => $enrID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '學員修課進度狀態已更新！']);
    exit();
  break;

  // =========================================================================
  // 5. 特惠資格條件規則管理：新增 / 刪除 (fsl_eligibility_rules)
  // =========================================================================
  case 'func_rule_add':
    $ruleType = trim((string)($_POST['erule_type'] ?? ''));
    $ruleOp   = trim((string)($_POST['erule_operator'] ?? '='));
    $ruleVal  = trim((string)($_POST['erule_val'] ?? ''));
    $ruleMsg  = trim((string)($_POST['erule_fail_msg'] ?? ''));

    if ($csID <= 0 || $ruleType === '' || $ruleVal === '' || $ruleMsg === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫條件類型、比對值與提示警語！']);
      exit();
    }

    $insRule = array(
      'erule_target_type' => 1,
      'erule_target_id'   => $csID,
      'erule_type'        => $ruleType,
      'erule_operator'    => $ruleOp,
      'erule_val'         => $ruleVal,
      'erule_fail_msg'    => $ruleMsg,
      'erule_sort'        => 100,
      'erule_status'      => 1
    );
    DB->insert('fsl_eligibility_rules', $insRule);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '購買資格條件規則已新增！']);
    exit();
  break;

  case 'func_rule_delete':
    $eruleID = (int)($_POST['eruleID'] ?? 0);
    if ($eruleID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的規則 ID！']);
      exit();
    }
    DB->query("DELETE FROM fsl_eligibility_rules WHERE erule_id = ?", array($eruleID));
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '資格條件規則已移除！']);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
