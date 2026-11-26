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
$giftID   = (int)($_POST['giftID'] ?? 0);
$imgUp    = trim((string)($_POST['imgUp'] ?? ''));

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

switch ($formAcce) {

  // =========================================================================
  // 1. 新增 / 更新禮品與優惠券方案 (func_gift_addnew / func_gift_infoUpdate)
  // =========================================================================
  case 'func_gift_addnew':
  case 'func_gift_infoUpdate':


    $acc   = ($formAcce === 'func_gift_addnew') ? 'add' : 'up';
    $title = trim((string)($formData['gift_title'] ?? ''));
    $code  = strtoupper(trim((string)($formData['gift_code'] ?? '')));

    if ($title === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請填寫禮品名稱！']);
      exit();
    }

    // 若有填寫禮品代碼，檢查唯一性
    if ($code !== '') {
      $dupId = (int)DB->single(
        "SELECT gift_id FROM fsl_gifts WHERE UPPER(gift_code) = ? AND gift_id != ? AND gift_status != 9",
        array($code, $giftID)
      );
      if ($dupId > 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => "禮品調用代碼「{$code}」已被其他禮品（ID: #{$dupId}）使用，請更換代碼！"]);
        exit();
      }
    }

    $upTime = date('Y-m-d H:i:s');
    $formData['gift_code']          = ($code !== '') ? $code : null;
    $formData['gift_title']         = $title;
    $formData['gift_subtitle']      = trim((string)($formData['gift_subtitle'] ?? '')) ?: null;
    $formData['gift_type']          = (int)($formData['gift_type'] ?? 1);
    $formData['gift_pts']           = max(0, (int)($formData['gift_pts'] ?? 0));
    $formData['gift_discount_usd']  = max(0, (float)($formData['gift_discount_usd'] ?? 0));
    $formData['gift_merch_ids']     = trim((string)($formData['gift_merch_ids'] ?? '')) ?: null;
    $formData['gift_target_type']   = (int)($formData['gift_target_type'] ?? 0);
    $formData['gift_target_minlvl'] = (int)($formData['gift_target_minlvl'] ?? 0);
    $formData['gift_target_mbids']  = trim((string)($formData['gift_target_mbids'] ?? '')) ?: null;
    $formData['gift_valid_days']    = max(0, (int)($formData['gift_valid_days'] ?? 30));
    $formData['gift_total_quota']   = (int)($formData['gift_total_quota'] ?? -1);
    $formData['gift_status']        = (int)($formData['gift_status'] ?? 1);

    if ($acc === 'add') {
      $formData['gift_created']      = $upTime;
      $formData['gift_issued_count'] = 0;
      DB->insert('fsl_gifts', $formData);
      $targetID = (int)DB->lastInsertId();
    } else {
      if ($giftID <= 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的禮品 ID！']);
        exit();
      }
      $formData['gift_updated'] = $upTime;
      DB->update('fsl_gifts', $formData, ['gift_id' => $giftID]);
      $targetID = $giftID;
    }

    // 處理禮品展示圖上傳
    $imgMsg = '';
    if ($targetID > 0 && $imgUp !== '') {
      $uploadDir = '../uploadFiles/gift/';
      if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
      }
      $imgOk = _fn_image_upload($imgUp, $uploadDir . 'gimg_' . $targetID, 400, 400);
      if ($imgOk) {
        $imgMsg = '並同步更新禮品展示圖！';
      }
    }

    $rtnArr = array(
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '禮品與優惠券方案已成功' . ($acc === 'add' ? '建立' : '更新') . '！' . $imgMsg,
      'upTime'  => $upTime
    );
    if ($acc === 'add') {
      $rtnArr['newID'] = $targetID;
    }
    echo json_encode($rtnArr);
    exit();
  break;

  // =========================================================================
  // 2. 後台主動派發禮品給指定會員 (func_gift_issue)
  // =========================================================================
  case 'func_gift_issue':
    $mbID       = (int)($_POST['mbID'] ?? 0);
    $sourceType = (int)($_POST['sourceType'] ?? 4);
    $qty        = max(1, min(20, (int)($_POST['qty'] ?? 1)));

    if ($giftID <= 0 || $mbID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請選擇要派發的禮品與目標會員！']);
      exit();
    }

    $giftRow = DB->row("SELECT * FROM fsl_gifts WHERE gift_id = ? AND gift_status = 1", array($giftID));
    if (!$giftRow) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到該禮品或禮品目前為停用狀態！']);
      exit();
    }

    $quota  = (int)$giftRow['gift_total_quota'];
    $issued = (int)$giftRow['gift_issued_count'];
    if ($quota >= 0 && ($issued + $qty) > $quota) {
      $remain = max(0, $quota - $issued);
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => "禮品發放名額不足（目前僅剩 {$remain} 份可發放）！"]);
      exit();
    }

    $validDays = (int)$giftRow['gift_valid_days'];
    $expiredAt = ($validDays > 0) ? date('Y-m-d H:i:s', strtotime("+{$validDays} days")) : null;

    for ($i = 0; $i < $qty; $i++) {
      DB->insert('fsl_gift_records', array(
        'gift_id'          => $giftID,
        'mb_id'            => $mbID,
        'grec_source_type' => $sourceType,
        'grec_source_id'   => (int)$_SESSION['SESS_AD_ID'],
        'grec_status'      => 0, // 0-待領取(未拆封)
        'grec_expired_at'  => $expiredAt
      ));
    }

    // 累計已發放數量
    DB->query("UPDATE fsl_gifts SET gift_issued_count = gift_issued_count + ? WHERE gift_id = ?", array($qty, $giftID));

    // 發送通知至會員中心 (fsl_notifications)
    $gTitle = $giftRow['gift_title'];
    DB->insert('fsl_notifications', array(
      'mb_id'       => $mbID,
      'no_category' => 1,
      'no_title'    => "【專屬禮品通知】您獲得了「{$gTitle}」（共 {$qty} 份）",
      'no_content'  => "親愛的會員您好，您已獲得一份專屬好禮「{$gTitle}」" . ($qty > 1 ? "（共 {$qty} 份）" : "") . "！請前往會員中心「我的禮品與優惠券」點擊拆封領取。"
    ));

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => "已成功派發 {$qty} 份「{$gTitle}」給該會員並發送通知！"
    ]);
    exit();
  break;

  // =========================================================================
  // 3. 更新實體贈品寄送狀態與物流單號 (func_gift_updateShipping)
  // =========================================================================
  case 'func_gift_updateShipping':
    $grecID       = (int)($_POST['grecID'] ?? 0);
    $shippingNote = trim((string)($_POST['shippingNote'] ?? ''));

    if ($grecID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的禮品領收流水 ID！']);
      exit();
    }

    $upd = array(
      'grec_shipping_note' => ($shippingNote !== '') ? $shippingNote : null
    );
    // 若有填寫物流單號，同步將狀態標記為 2-實體寄送中
    if ($shippingNote !== '') {
      $upd['grec_status'] = 2;
    }

    DB->update('fsl_gift_records', $upd, ['grec_id' => $grecID]);

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => ($shippingNote !== '') ? '物流單號已儲存並更新為「實體寄送中」！' : '寄送備註已清除！'
    ]);
    exit();
  break;

  // =========================================================================
  // 4. 軟刪除 / 停用封存禮品方案 (func_gift_delete)
  // =========================================================================
  case 'func_gift_delete':
    if ($giftID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的禮品 ID！']);
      exit();
    }
    DB->update('fsl_gifts', ['gift_status' => 9], ['gift_id' => $giftID]);
    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '禮品方案已成功刪除封存！'
    ]);
    exit();
  break;

  // =========================================================================
  // 5. 禮品領收條件規則管理：新增 / 刪除 (fsl_eligibility_rules, target_type=2)
  // =========================================================================
  case 'func_gift_rule_add':
    $ruleType = trim((string)($_POST['erule_type'] ?? ''));
    $ruleOp   = trim((string)($_POST['erule_operator'] ?? '='));
    $ruleVal  = trim((string)($_POST['erule_val'] ?? ''));
    $ruleMsg  = trim((string)($_POST['erule_fail_msg'] ?? ''));

    if ($giftID <= 0 || $ruleType === '' || $ruleVal === '' || $ruleMsg === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫條件類型、比對值與提示警語！']);
      exit();
    }

    DB->insert('fsl_eligibility_rules', array(
      'erule_target_type' => 2, // 2 = 禮品與優惠券
      'erule_target_id'   => $giftID,
      'erule_type'        => $ruleType,
      'erule_operator'    => $ruleOp,
      'erule_val'         => $ruleVal,
      'erule_fail_msg'    => $ruleMsg,
      'erule_sort'        => 100,
      'erule_status'      => 1
    ));
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '禮品領收條件規則已新增！']);
    exit();
  break;

  case 'func_gift_rule_delete':
    $eruleID = (int)($_POST['eruleID'] ?? 0);
    if ($eruleID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的規則 ID！']);
      exit();
    }
    DB->query("DELETE FROM fsl_eligibility_rules WHERE erule_id = ? AND erule_target_type = 2", array($eruleID));
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '禮品領收條件規則已移除！']);
    exit();
  break;





  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
