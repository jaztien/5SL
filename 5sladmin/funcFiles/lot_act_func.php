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
$lotID    = (int)($_POST['lotID'] ?? 0);

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

switch ($formAcce) {

  // =========================================================================
  // 1. 新增 / 更新抽獎活動主檔 (func_lot_addnew / func_lot_infoUpdate)
  // =========================================================================
  case 'func_lot_addnew':
  case 'func_lot_infoUpdate':
    $acc     = ($formAcce === 'func_lot_addnew') ? 'add' : 'up';
    $keycode = strtoupper(trim((string)($formData['lot_keycode'] ?? '')));
    $title   = trim((string)($formData['lot_title'] ?? ''));

    if ($keycode === '' || $title === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫活動呼叫代碼與活動名稱！']);
      exit();
    }

    // 檢查活動呼叫代碼唯一性 (UNIQUE KEY uk_lot_keycode)
    $dupId = (int)DB->single(
      "SELECT lot_id FROM fsl_lotteries WHERE UPPER(lot_keycode) = ? AND lot_id != ? AND lot_status != 9",
      array($keycode, $lotID)
    );
    if ($dupId > 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => "活動呼叫代碼「{$keycode}」已被其他活動（ID: #{$dupId}）使用，請更換代碼！"]);
      exit();
    }

    $upTime = date('Y-m-d H:i:s');
    $formData['lot_keycode']        = $keycode;
    $formData['lot_mode']           = (int)($formData['lot_mode'] ?? 2);
    $formData['lot_title']          = $title;
    $formData['lot_subtitle']       = trim((string)($formData['lot_subtitle'] ?? '')) ?: null;
    $formData['lot_start_at']       = !empty($formData['lot_start_at']) ? trim((string)$formData['lot_start_at']) : null;
    $formData['lot_end_at']         = !empty($formData['lot_end_at']) ? trim((string)$formData['lot_end_at']) : null;
    $formData['lot_cost_pts']       = max(0, (int)($formData['lot_cost_pts'] ?? 0));
    $formData['lot_limit_per_user'] = max(0, (int)($formData['lot_limit_per_user'] ?? 1));
    $formData['lot_limit_period']   = (int)($formData['lot_limit_period'] ?? 0);
    $formData['lot_min_lvl']        = (int)($formData['lot_min_lvl'] ?? 0);
    $formData['lot_status']         = (int)($formData['lot_status'] ?? 1);

    if ($acc === 'add') {
      $formData['lot_created'] = $upTime;
      DB->insert('fsl_lotteries', $formData);
      $newID = (int)DB->lastInsertId();
      echo json_encode([
        'RTN'     => 1,
        'msgType' => 'success',
        'msgStr'  => '抽獎活動已成功建立！請接著設定獎項池格位。',
        'newID'   => $newID,
        'upTime'  => $upTime
      ]);
    } else {
      if ($lotID <= 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的抽獎活動 ID！']);
        exit();
      }
      $formData['lot_updated'] = $upTime;
      DB->update('fsl_lotteries', $formData, ['lot_id' => $lotID]);
      echo json_encode([
        'RTN'     => 1,
        'msgType' => 'success',
        'msgStr'  => '抽獎活動設定已儲存更新！',
        'upTime'  => $upTime
      ]);
    }
    exit();
  break;

  // =========================================================================
  // 2. 新增 / 刪除獎項池格位 (fsl_lottery_items)
  // =========================================================================
  case 'func_lot_item_add':
    $slotOrder = max(1, (int)($_POST['loti_slot_order'] ?? 1));
    $label     = trim((string)($_POST['loti_label'] ?? ''));
    $giftId    = !empty($_POST['gift_id']) ? (int)$_POST['gift_id'] : null;
    $color     = trim((string)($_POST['loti_color'] ?? '#ffc107')) ?: null;
    $weight    = max(1, (int)($_POST['loti_weight'] ?? 100));
    $quota     = (int)($_POST['loti_quota'] ?? -1);

    if ($lotID <= 0 || $label === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請填寫輪盤顯示短標名稱！']);
      exit();
    }

    DB->insert('fsl_lottery_items', array(
      'lot_id'          => $lotID,
      'gift_id'         => $giftId,
      'loti_slot_order' => $slotOrder,
      'loti_label'      => $label,
      'loti_color'      => $color,
      'loti_weight'     => $weight,
      'loti_quota'      => $quota,
      'loti_won_count'  => 0
    ));

    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '獎項池格位已新增並重新計算中獎機率！']);
    exit();
  break;

  case 'func_lot_item_delete':
    $lotiID = (int)($_POST['lotiID'] ?? 0);
    if ($lotiID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的格位項目 ID！']);
      exit();
    }
    DB->query("DELETE FROM fsl_lottery_items WHERE loti_id = ?", array($lotiID));
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '該獎項池格位已移除！']);
    exit();
  break;

  // =========================================================================
  // 3. 參加資格條件規則：新增 / 刪除 (fsl_eligibility_rules, target_type=3)
  // =========================================================================
  case 'func_lot_rule_add':
    $ruleType = trim((string)($_POST['erule_type'] ?? ''));
    $ruleOp   = trim((string)($_POST['erule_operator'] ?? '='));
    $ruleVal  = trim((string)($_POST['erule_val'] ?? ''));
    $ruleMsg  = trim((string)($_POST['erule_fail_msg'] ?? ''));

    if ($lotID <= 0 || $ruleType === '' || $ruleVal === '' || $ruleMsg === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫條件類型、比對值與提示警語！']);
      exit();
    }

    DB->insert('fsl_eligibility_rules', array(
      'erule_target_type' => 3, // 3 = 抽獎活動
      'erule_target_id'   => $lotID,
      'erule_type'        => $ruleType,
      'erule_operator'    => $ruleOp,
      'erule_val'         => $ruleVal,
      'erule_fail_msg'    => $ruleMsg,
      'erule_sort'        => 100,
      'erule_status'      => 1
    ));

    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '參加資格條件規則已新增！']);
    exit();
  break;

  case 'func_lot_rule_delete':
    $eruleID = (int)($_POST['eruleID'] ?? 0);
    if ($eruleID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的規則 ID！']);
      exit();
    }
    DB->query("DELETE FROM fsl_eligibility_rules WHERE erule_id = ? AND erule_target_type = 3", array($eruleID));
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '參加資格條件規則已移除！']);
    exit();
  break;

  // =========================================================================
  // 4. 軟刪除抽獎活動 (func_lot_delete)
  // =========================================================================
  case 'func_lot_delete':
    if ($lotID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的抽獎活動 ID！']);
      exit();
    }
    DB->update('fsl_lotteries', ['lot_status' => 9], ['lot_id' => $lotID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '抽獎活動已成功刪除封存！']);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
