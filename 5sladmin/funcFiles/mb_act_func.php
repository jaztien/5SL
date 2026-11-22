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
$formData = (array)($_POST['arrDatA'] ?? null);
$mbID     = (int)($_POST['mbID'] ?? 0);
$imgUp    = trim((string)($_POST['imgUp'] ?? ''));

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}
/*
// 將前端 serializeArray() 轉為關聯陣列
$formData = array();
if (!empty($_POST['arrDatA']) && is_array($_POST['arrDatA'])) {
  foreach ($_POST['arrDatA'] as $item) {
    if (isset($item['name'])) {
      $formData[$item['name']] = trim((string)($item['value'] ?? ''));
    }
  }
}
*/
// 輔助函式：儲存裁切後的 Base64 會員頭像至 ../uploadFiles/mb/

switch ($formAcce) {

  // =========================================================================
  // 2. 更新會員基本資料與發票載具 (func_mb_infoUpdate)
  // =========================================================================
  case 'func_mb_addnew':
  case 'func_mb_infoUpdate':
    $acc = $formAcce=='func_mb_addnew' ? 'add' : 'up';
    //預備寫入資料再處理（加密、編碼或定義）
    $upTime = date('Y-m-d H:i:s');
    if($acc === 'up') $formData['mb_updated'] = $upTime;
    if(isset($formData['mb_password'])){
      $formData['mb_password'] = !empty($formData['mb_password']) ? password_hash($formData['mb_password'], PASSWORD_DEFAULT) : '';
    }
    if(!empty($formData['mb_name'])){
      $formData['mb_name'] =  _fn_secure_crypto($formData['mb_name']);
      $formData['mb_name_hash'] =  _fn_hash_hmac($formData['mb_name']);
    }
    if(!empty($formData['mb_cellphone'])){
      $formData['mb_cellphone'] =  _fn_secure_crypto($formData['mb_cellphone']);
      $formData['mb_cellphone_hash'] =  _fn_hash_hmac($formData['mb_cellphone']);
    }
    if(!empty($formData['mb_email'])){
      $formData['mb_email'] =  _fn_secure_crypto($formData['mb_email']);
      $formData['mb_email_hash'] =  _fn_hash_hmac($formData['mb_email']);
    }
    $formData['mb_birth'] = !empty($formData['mb_birth']) ? $formData['mb_birth'] : null;
    $formData['mb_add'] = !empty($formData['mb_add']) ? _fn_secure_crypto($formData['mb_add']) : '';
    //執行 Query
    $newID = 0;
    $res = 0;
    $img = false;
    $imgmsg = '';
    if(!empty($formData)){
      if($acc === 'add') {
          $res = DB->insert('fsl_members', $formData);
          $newID = DB->lastInsertId();
      } else {
          $res = DB->update('fsl_members', $formData, ['mb_id' => $mbID]);
      }
    }

    if ($res && $imgUp !== '') {
      $img = _fn_image_upload($imgUp, '../uploadFiles/mb/mbphoto_'.$mbID, 400, 400);
      if($img){
        $imgmsg = '並且成功更新頭像！！';
      }
      //_saveMemberBase64Photo($mbID, $imgUp);
    }
    //$upTime = DB->single("SELECT mb_updated FROM fsl_members WHERE mb_id = ?", array($mbID));
    $rtnARR = array(
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '會員資料與載具設定已成功'.($acc=='add'?'新增':'更新').'！'.$imgmsg,
      'upTime'  => $upTime ?: date('Y-m-d H:i:s')
    );
    if($acc === 'add') {
      $rtnARR['newID'] = $newID;
    }
    echo json_encode($rtnARR);
    exit();
  break;
  // =========================================================================
  // 3. 手動調帳會員 PTS 紅利點數 (func_mb_adjustPts - 依據 fsl_points 流水帳精準計算)
  // =========================================================================
  case 'func_mb_adjustPts':
    $adjDir  = trim((string)($_POST['adjDir'] ?? 'add'));
    $adjVal  = max(0, (int)($_POST['adjVal'] ?? 0));
    $adjMemo = trim((string)($_POST['adjMemo'] ?? ''));

    if ($mbID <= 0 || $adjVal <= 0 || $adjMemo === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請輸入正確的調帳點數與原因備註！']);
      exit();
    }

    $mbExist = (int)DB->single("SELECT mb_id FROM fsl_members WHERE mb_id = ? AND mb_status != 9", array($mbID));
    if ($mbExist <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到該會員資料！']);
      exit();
    }

    // 改由 fsl_points 流水帳即時彙算目前真實可用點數
    $ptInfo  = _fn_get_pointinfo($mbID);
    $currPts = $ptInfo['available'];

    if ($adjDir === 'sub' && $currPts < $adjVal) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => "該會員流水帳可用餘額僅有 {$currPts} PTS，不足扣除 {$adjVal} PTS！"]);
      exit();
    }

    $delta     = ($adjDir === 'sub') ? -$adjVal : $adjVal;
    $newPoints = $currPts + $delta;
    $ptIncome  = ($delta > 0) ? 1 : 0;
    $adminNick = $_SESSION['SESS_AD_NICK'] ?? '系統管理員';
    $fullMemo  = "[後台調帳:{$adminNick}] " . $adjMemo;
    $createdAt = date('Y-m-d H:i:s');
    $insARR = array(
      "pt_date" => $createdAt,
      "mb_id" => $mbID,
      "pt_income" => $ptIncome,
      "pt_type" => 4,
      "pt_for" => 9,
      "pt_forid" => (int)$_SESSION['SESS_AD_ID'],
      "pt_trade" => $delta,
      "pt_redeem" => 0,
      "pt_freepoints" => 0,
      "pt_available" => $newPoints,
      "pt_memo" => $fullMemo,
      "pt_status" => 1
    );
    $ins = DB->insert('fsl_points', $insARR);
    $ptId = DB->lastInsertId();

    // 同步發送通知至會員訊息中心
    $signTxt = ($delta > 0) ? "+{$delta}" : "{$delta}";
    $insARR = array(
      "mb_id" => $mbID,
      "no_category" => 1,
      "no_title" => "【PTS 點數異動通知】{$signTxt} PTS",
      "no_content" => "親愛的會員您好，管理團隊已為您的帳戶執行點數異動（{$signTxt} PTS），原因：{$adjMemo}，目前最新可用餘額為 " . number_format($newPoints) . " PTS。"
    );
    $ins = DB->insert('fsl_notifications', $insARR);

    echo json_encode([
      'RTN'       => 1,
      'msgType'   => 'success',
      'msgStr'    => "PTS 點數調帳完成（{$signTxt} PTS），最新餘額：{$newPoints} PTS",
      'delta'     => $delta,
      'newPoints' => $newPoints,
      'ptId'      => $ptId,
      'createdAt' => $createdAt ?: date('Y-m-d H:i:s')
    ]);
    exit();
  break;
  // =========================================================================
  // 4. 軟刪除 / 封存會員帳號 (func_mb_delete)
  // =========================================================================
  case 'func_mb_delete':
    if ($mbID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的會員 ID！']);
      exit();
    }
    DB->query("UPDATE fsl_members SET mb_status = 9 WHERE mb_id = ?", array($mbID));
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '會員帳號已成功刪除封存！']);
    exit();
  break;
  // ========================================================================= //
  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
