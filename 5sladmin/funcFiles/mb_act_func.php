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
$mbID     = (int)($_POST['mbID'] ?? 0);
$imgUp    = trim((string)($_POST['imgUp'] ?? ''));

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

// 將前端 serializeArray() 轉為關聯陣列
$formData = array();
if (!empty($_POST['arrDatA']) && is_array($_POST['arrDatA'])) {
  foreach ($_POST['arrDatA'] as $item) {
    if (isset($item['name'])) {
      $formData[$item['name']] = trim((string)($item['value'] ?? ''));
    }
  }
}

// 輔助函式：儲存裁切後的 Base64 會員頭像至 ../uploadFiles/mb/
function _saveMemberBase64Photo($mbId, $base64Str) {
  if ($mbId <= 0 || $base64Str === '' || strpos($base64Str, 'base64,') === false) {
    return false;
  }
  $parts = explode('base64,', $base64Str, 2);
  $bin   = base64_decode($parts[1]);
  if ($bin === false) return false;

  $uploadDir = '../uploadFiles/mb/';
  if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
  }
  $mainFile  = $uploadDir . 'mbphoto_' . $mbId . '_.jpg';
  $thumbFile = $uploadDir . 'mbphoto_' . $mbId . '_thumb.jpg';

  file_put_contents($mainFile, $bin);
  file_put_contents($thumbFile, $bin);
  return true;
}

switch ($formAcce) {

  // =========================================================================
  // 1. 新增會員資料 (func_mb_addnew)
  // =========================================================================
  case 'func_mb_addnew':
    $email = strtolower($formData['mb_email'] ?? '');
    $pass  = $formData['mb_password'] ?? '';
    $name  = $formData['mb_name'] ?? '';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請輸入正確的登入電子信箱格式！']);
      exit();
    }
    if (strlen($pass) < 6) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '初始登入密碼至少需 6 個字元！']);
      exit();
    }
    if ($name === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請填寫會員真實姓名！']);
      exit();
    }

    // 檢查信箱是否已被註冊
    $existId = (int)DB->single("SELECT mb_id FROM fsl_members WHERE mb_email = ? AND mb_status != 9", array($email));
    if ($existId > 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '此電子信箱已存在於會員資料庫中，無法重複建立！']);
      exit();
    }

    $hashPass = password_hash($pass, PASSWORD_DEFAULT);
    $nick     = $formData['mb_title'] ?? '';
    $gender   = in_array($formData['mb_gender'] ?? 'N', ['F', 'M', 'N'], true) ? $formData['mb_gender'] : 'N';
    $birth    = !empty($formData['mb_birth']) ? $formData['mb_birth'] : null;
    $city     = $formData['mb_city'] ?? null;

    // 敏感欄位加密
    $encPhone = !empty($formData['mb_phone']) ? _fn_secure_crypto($formData['mb_phone'], 'en') : null;
    $encIdno  = !empty($formData['mb_idno'])  ? _fn_secure_crypto(strtoupper($formData['mb_idno']), 'en') : null;
    $encAddr  = !empty($formData['mb_addr'])  ? _fn_secure_crypto($formData['mb_addr'], 'en') : null;

    $level    = (int)($formData['mb_level'] ?? 1);
    $status   = (int)($formData['mb_status'] ?? 1);
    $verified = (int)($formData['mb_email_verified'] ?? 1);

    $invType     = (int)($formData['mb_inv_type'] ?? 1);
    $carrierType = (int)($formData['mb_carrier_type'] ?? 0);
    $carrierCode = strtoupper($formData['mb_carrier_code'] ?? '') ?: null;
    $taxId       = ($formData['mb_tax_id'] ?? '') ?: null;
    $compTitle   = ($formData['mb_company_title'] ?? '') ?: null;

    $sqlIns = "
      INSERT INTO fsl_members
      (mb_email, mb_password, mb_name, mb_title, mb_gender, mb_birth, mb_phone, mb_idno, mb_city, mb_addr,
       mb_level, mb_status, mb_email_verified, mb_inv_type, mb_carrier_type, mb_carrier_code, mb_tax_id, mb_company_title)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    DB->query($sqlIns, array(
      $email, $hashPass, $name, ($nick ?: null), $gender, $birth, $encPhone, $encIdno, ($city ?: null), $encAddr,
      $level, $status, $verified, $invType, $carrierType, $carrierCode, $taxId, $compTitle
    ));

    $newID = (int)DB->single("SELECT mb_id FROM fsl_members WHERE mb_email = ? ORDER BY mb_id DESC LIMIT 1", array($email));
    if ($newID > 0 && $imgUp !== '') {
      _saveMemberBase64Photo($newID, $imgUp);
    }

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => "新會員「{$name}」已成功建立！",
      'newID'   => $newID
    ]);
    exit();
  break;

  // =========================================================================
  // 2. 更新會員基本資料與發票載具 (func_mb_infoUpdate)
  // =========================================================================
  case 'func_mb_infoUpdate':
    if ($mbID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的會員 ID！']);
      exit();
    }

    $name = $formData['mb_name'] ?? '';
    if ($name === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '會員真實姓名不可為空！']);
      exit();
    }

    $pass     = $formData['mb_password'] ?? '';
    $nick     = $formData['mb_nick'] ?? '';
    $gender   = in_array($formData['mb_gender'] ?? 'N', ['F', 'M', 'N'], true) ? $formData['mb_gender'] : 'N';
    $birth    = !empty($formData['mb_birth']) ? $formData['mb_birth'] : null;
    $city     = $formData['mb_city'] ?? null;

    $encPhone = !empty($formData['mb_phone']) ? _fn_secure_crypto($formData['mb_phone'], 'en') : null;
    $encIdno  = !empty($formData['mb_idno'])  ? _fn_secure_crypto(strtoupper($formData['mb_idno']), 'en') : null;
    $encAddr  = !empty($formData['mb_addr'])  ? _fn_secure_crypto($formData['mb_addr'], 'en') : null;

    $level    = (int)($formData['mb_level'] ?? 1);
    $status   = (int)($formData['mb_status'] ?? 1);
    $verified = (int)($formData['mb_email_verified'] ?? 0);

    $invType     = (int)($formData['mb_inv_type'] ?? 1);
    $carrierType = (int)($formData['mb_carrier_type'] ?? 0);
    $carrierCode = strtoupper($formData['mb_carrier_code'] ?? '') ?: null;
    $taxId       = ($formData['mb_tax_id'] ?? '') ?: null;
    $compTitle   = ($formData['mb_company_title'] ?? '') ?: null;

    if ($pass !== '') {
      if (strlen($pass) < 6) {
        echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '重設新密碼至少需 6 個字元！']);
        exit();
      }
      $hashPass = password_hash($pass, PASSWORD_DEFAULT);
      DB->query("UPDATE fsl_members SET mb_password = ? WHERE mb_id = ?", array($hashPass, $mbID));
    }

    $sqlUpd = "
      UPDATE fsl_members SET
        mb_name = ?, mb_nick = ?, mb_gender = ?, mb_birth = ?,
        mb_phone = ?, mb_idno = ?, mb_city = ?, mb_addr = ?,
        mb_level = ?, mb_status = ?, mb_email_verified = ?,
        mb_inv_type = ?, mb_carrier_type = ?, mb_carrier_code = ?, mb_tax_id = ?, mb_company_title = ?
      WHERE mb_id = ?
    ";
    DB->query($sqlUpd, array(
      $name, ($nick ?: null), $gender, $birth,
      $encPhone, $encIdno, ($city ?: null), $encAddr,
      $level, $status, $verified,
      $invType, $carrierType, $carrierCode, $taxId, $compTitle,
      $mbID
    ));

    if ($imgUp !== '') {
      _saveMemberBase64Photo($mbID, $imgUp);
    }

    $upTime = DB->single("SELECT mb_updated FROM fsl_members WHERE mb_id = ?", array($mbID));

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '會員資料與載具設定已成功更新！',
      'upTime'  => $upTime ?: date('Y-m-d H:i:s')
    ]);
    exit();
  break;

  // =========================================================================
  // 3. 手動調帳會員 PTS 紅利點數 (func_mb_adjustPts)
  // =========================================================================
  case 'func_mb_adjustPts':
    $adjDir  = trim((string)($_POST['adjDir'] ?? 'add'));
    $adjVal  = max(0, (int)($_POST['adjVal'] ?? 0));
    $adjMemo = trim((string)($_POST['adjMemo'] ?? ''));

    if ($mbID <= 0 || $adjVal <= 0 || $adjMemo === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請輸入正確的調帳點數與原因備註！']);
      exit();
    }

    $currPts = (int)DB->single("SELECT mb_points FROM fsl_members WHERE mb_id = ? AND mb_status != 9", array($mbID));
    if ($adjDir === 'sub' && $currPts < $adjVal) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => "該會員目前僅有 {$currPts} PTS，不足扣除 {$adjVal} PTS！"]);
      exit();
    }

    $delta     = ($adjDir === 'sub') ? -$adjVal : $adjVal;
    $newPoints = $currPts + $delta;
    $ptIncome  = ($delta > 0) ? 1 : 0;
    $adminNick = $_SESSION['SESS_AD_NICK'] ?? '系統管理員';
    $fullMemo  = "[後台調帳:{$adminNick}] " . $adjMemo;

    DB->query("UPDATE fsl_members SET mb_points = ? WHERE mb_id = ?", array($newPoints, $mbID));
    DB->query(
      "INSERT INTO fsl_points (mb_id, pt_income, pt_type, pt_for, pt_forid, pt_trade, pt_redeem, pt_freepoints, pt_available, pt_memo, pt_status)
       VALUES (?, ?, 4, 9, ?, ?, 0, 0, ?, ?, 1)",
      array($mbID, $ptIncome, (int)$_SESSION['SESS_AD_ID'], $delta, $newPoints, $fullMemo)
    );

    $ptId      = (int)DB->single("SELECT pt_id FROM fsl_points WHERE mb_id = ? ORDER BY pt_id DESC LIMIT 1", array($mbID));
    $createdAt = DB->single("SELECT pt_created FROM fsl_points WHERE pt_id = ?", array($ptId));

    // 同步發送通知至會員訊息中心
    $signTxt = ($delta > 0) ? "+{$delta}" : "{$delta}";
    DB->query(
      "INSERT INTO fsl_messages (mb_id, msg_category, msg_title, msg_content, msg_is_read, msg_status)
       VALUES (?, 1, ?, ?, 0, 1)",
      array($mbID, "【PTS 點數異動通知】{$signTxt} PTS", "親愛的會員您好，管理團隊已為您的帳戶執行點數異動（{$signTxt} PTS），原因：{$adjMemo}，目前最新可用餘額為 " . number_format($newPoints) . " PTS。")
    );

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

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
