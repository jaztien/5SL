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
$artiID   = (int)($_POST['artiID'] ?? 0);
$imgUp    = trim((string)($_POST['imgUp'] ?? ''));

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

switch ($formAcce) {

  // =========================================================================
  // 1. 新增 / 更新知識專欄文章與動態變數模板 (func_art_addnew / func_art_infoUpdate)
  // =========================================================================
  case 'func_art_addnew':
  case 'func_art_infoUpdate':
    $acc     = ($formAcce === 'func_art_addnew') ? 'add' : 'up';
    $title   = trim((string)($formData['arti_title'] ?? ''));
    $keycode = strtoupper(trim((string)($formData['arti_keycode'] ?? '')));

    if ($title === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請填寫文章標題或郵件/通知主旨！']);
      exit();
    }

    // 若有填寫調用代碼 (arti_keycode)，檢查全站唯一性
    if ($keycode !== '') {
      $dupId = (int)DB->single(
        "SELECT arti_id FROM fsl_articles WHERE UPPER(arti_keycode) = ? AND arti_id != ? AND arti_status != 9",
        array($keycode, $artiID)
      );
      if ($dupId > 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => "唯一調用代碼「{$keycode}」已被其他內容（ID: #{$dupId}）使用，請更換代碼！"]);
        exit();
      }
    }

    $upTime = date('Y-m-d H:i:s');
    $formData['arti_mode']        = (int)($formData['arti_mode'] ?? 1);
    $formData['arti_keycode']     = ($keycode !== '') ? $keycode : null;
    $formData['arti_cate']        = trim((string)($formData['arti_cate'] ?? 'mindset')) ?: null;
    $formData['arti_title']       = $title;
    $formData['arti_subscript']   = trim((string)($formData['arti_subscript'] ?? '')) ?: null;
    $formData['arti_tag']         = trim((string)($formData['arti_tag'] ?? '')) ?: null;
    $formData['arti_tpl_vars']    = trim((string)($formData['arti_tpl_vars'] ?? '')) ?: null;
    $formData['arti_brief']       = trim((string)($formData['arti_brief'] ?? '')) ?: null;
    $formData['arti_body']        = (string)($formData['arti_body'] ?? '');
    $formData['arti_guestbrowse'] = (int)($formData['arti_guestbrowse'] ?? 1);
    $formData['arti_cost_pts']    = max(0, (int)($formData['arti_cost_pts'] ?? 0));
    $formData['arti_is_featured'] = (int)($formData['arti_is_featured'] ?? 0);
    $formData['arti_status']      = (int)($formData['arti_status'] ?? 1);
    $formData['arti_dateopen']    = !empty($formData['arti_dateopen']) ? trim((string)$formData['arti_dateopen']) : null;
    $formData['arti_dateclose']   = !empty($formData['arti_dateclose']) ? trim((string)$formData['arti_dateclose']) : null;
    $formData['course_id']        = !empty($formData['course_id']) ? (int)$formData['course_id'] : null;
    $formData['merch_id']         = !empty($formData['merch_id']) ? (int)$formData['merch_id'] : null;

    if ($acc === 'add') {
      $formData['ad_id']          = (int)$_SESSION['SESS_AD_ID'];
      $formData['mb_id']          = 0;
      $formData['arti_published'] = $upTime;
      $formData['arti_visits']    = 0;
      $formData['arti_likes']     = 0;
      DB->insert('fsl_articles', $formData);
      $targetID = (int)DB->lastInsertId();
    } else {
      if ($artiID <= 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的文章 / 模板 ID！']);
        exit();
      }
      $formData['arti_updated'] = $upTime;
      DB->update('fsl_articles', $formData, ['arti_id' => $artiID]);
      $targetID = $artiID;
    }

    // 處理文章封面圖上傳 (800x500)
    $imgMsg = '';
    if ($targetID > 0 && $imgUp !== '') {
      $uploadDir = '../uploadFiles/art/';
      if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
      }
      $imgOk = _fn_image_upload($imgUp, $uploadDir . 'aimg_' . $targetID, 800, 500);
      if ($imgOk) {
        $imgMsg = '並同步更新封面主圖！';
      }
    }

    $rtnArr = array(
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '內容已成功' . ($acc === 'add' ? '建立' : '更新') . '！' . $imgMsg,
      'upTime'  => $upTime
    );
    if ($acc === 'add') {
      $rtnArr['newID'] = $targetID;
    }
    echo json_encode($rtnArr);
    exit();
  break;

  // =========================================================================
  // 2. 軟刪除專欄文章或動態模板 (func_art_delete)
  // =========================================================================
  case 'func_art_delete':
    if ($artiID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的文章 / 模板 ID！']);
      exit();
    }
    DB->update('fsl_articles', ['arti_status' => 9], ['arti_id' => $artiID]);
    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '該筆專欄或動態模板已成功刪除封存！'
    ]);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
