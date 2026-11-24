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
$questID  = (int)($_POST['questID'] ?? 0);

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

switch ($formAcce) {

  // =========================================================================
  // 1. 新增 / 更新階段任務主檔 (func_quest_addnew / func_quest_infoUpdate)
  // =========================================================================
  case 'func_quest_addnew':
  case 'func_quest_infoUpdate':
    $acc   = ($formAcce === 'func_quest_addnew') ? 'add' : 'up';
    $code  = strtoupper(trim((string)($formData['quest_code'] ?? '')));
    $title = trim((string)($formData['quest_title'] ?? ''));
    $giftId = (int)($formData['gift_id'] ?? 0);

    if ($code === '' || $title === '' || $giftId <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫任務系統識別碼、任務標題與關聯獎品禮包！']);
      exit();
    }

    // 檢查任務識別碼唯一性 (UNIQUE KEY uk_quest_code)
    $dupId = (int)DB->single(
      "SELECT quest_id FROM fsl_quests WHERE UPPER(quest_code) = ? AND quest_id != ? AND quest_status != 9",
      array($code, $questID)
    );
    if ($dupId > 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => "任務識別碼「{$code}」已被其他任務（ID: #{$dupId}）使用，請更換代碼！"]);
      exit();
    }

    $upTime = date('Y-m-d H:i:s');
    $formData['quest_code']         = $code;
    $formData['quest_tier_lvl']     = (int)($formData['quest_tier_lvl'] ?? 1);
    $formData['quest_is_grand']     = (int)($formData['quest_is_grand'] ?? 0);
    $formData['quest_sort']         = max(1, (int)($formData['quest_sort'] ?? 1));
    $formData['quest_title']        = $title;
    $formData['quest_desc']         = trim((string)($formData['quest_desc'] ?? '')) ?: null;
    $formData['quest_target_val']   = max(1, (int)($formData['quest_target_val'] ?? 1));
    $formData['quest_unit']         = trim((string)($formData['quest_unit'] ?? '次')) ?: '次';
    $formData['gift_id']            = $giftId;
    $formData['quest_reward_label'] = trim((string)($formData['quest_reward_label'] ?? ''));
    $formData['quest_status']       = (int)($formData['quest_status'] ?? 1);

    if ($acc === 'add') {
      $formData['quest_created'] = $upTime;
      DB->insert('fsl_quests', $formData);
      $newID = (int)DB->lastInsertId();
      echo json_encode([
        'RTN'     => 1,
        'msgType' => 'success',
        'msgStr'  => '階段任務已成功建立！',
        'newID'   => $newID,
        'upTime'  => $upTime
      ]);
    } else {
      if ($questID <= 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的任務 ID！']);
        exit();
      }
      $formData['quest_updated'] = $upTime;
      DB->update('fsl_quests', $formData, ['quest_id' => $questID]);
      echo json_encode([
        'RTN'     => 1,
        'msgType' => 'success',
        'msgStr'  => '任務設定已儲存更新！',
        'upTime'  => $upTime
      ]);
    }
    exit();
  break;

  // =========================================================================
  // 2. 後台手動更新會員任務進度 / 直接核發達標獎勵 (func_quest_grantProgress)
  // =========================================================================
  case 'func_quest_grantProgress':
    $mbID    = (int)($_POST['mbID'] ?? 0);
    $currVal = max(0, (int)($_POST['currVal'] ?? 0));

    if ($questID <= 0 || $mbID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請選擇任務與目標會員！']);
      exit();
    }

    $qRow = DB->row("SELECT * FROM fsl_quests WHERE quest_id = ? AND quest_status = 1", array($questID));
    if (!$qRow) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到該任務或任務目前為停用狀態！']);
      exit();
    }

    $targetVal = (int)$qRow['quest_target_val'];
    $isDone    = ($currVal >= $targetVal);
    $nowStr    = date('Y-m-d H:i:s');

    $existRec  = DB->row("SELECT * FROM fsl_quest_records WHERE quest_id = ? AND mb_id = ?", array($questID, $mbID));
    $grecID    = $existRec['grec_id'] ?? null;

    // 若已達標且尚未產生禮品領收單 (fsl_gift_records)，自動派發禮品並建立關聯
    if ($isDone && empty($grecID)) {
      $giftRow   = DB->row("SELECT gift_valid_days, gift_title FROM fsl_gifts WHERE gift_id = ?", array((int)$qRow['gift_id']));
      $validDays = (int)($giftRow['gift_valid_days'] ?? 30);
      $expiredAt = ($validDays > 0) ? date('Y-m-d H:i:s', strtotime("+{$validDays} days")) : null;

      DB->insert('fsl_gift_records', array(
        'gift_id'          => (int)$qRow['gift_id'],
        'mb_id'            => $mbID,
        'grec_source_type' => 5, // 5-任務獎勵
        'grec_source_id'   => $questID,
        'grec_status'      => 0, // 0-待領取(未拆封)
        'grec_expired_at'  => $expiredAt
      ));
      $grecID = (int)DB->lastInsertId();

      DB->query("UPDATE fsl_gifts SET gift_issued_count = gift_issued_count + 1 WHERE gift_id = ?", array((int)$qRow['gift_id']));

      // 發送通知至會員中心 (fsl_notifications)
      DB->insert('fsl_notifications', array(
        'mb_id'       => $mbID,
        'no_category' => 1,
        'no_title'    => "【任務達成通知】{$qRow['quest_title']}",
        'no_content'  => "恭喜您達成階段任務「{$qRow['quest_title']}」！系統已為您準備任務獎勵「{$qRow['quest_reward_label']}」，請前往會員中心點擊領取。"
      ));
    }

    if ($existRec) {
      $newStatus = ((int)$existRec['qrec_status'] === 2) ? 2 : ($isDone ? 1 : 0);
      $upd = array(
        'qrec_curr_val' => $currVal,
        'qrec_status'   => $newStatus,
        'grec_id'       => $grecID
      );
      if ($isDone && empty($existRec['qrec_completed_at'])) {
        $upd['qrec_completed_at'] = $nowStr;
      }
      DB->update('fsl_quest_records', $upd, ['qrec_id' => $existRec['qrec_id']]);
    } else {
      DB->insert('fsl_quest_records', array(
        'quest_id'          => $questID,
        'mb_id'             => $mbID,
        'qrec_curr_val'     => $currVal,
        'qrec_status'       => $isDone ? 1 : 0,
        'qrec_completed_at' => $isDone ? $nowStr : null,
        'grec_id'           => $grecID
      ));
    }

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => $isDone ? '已將該會員任務更新為「條件已達成」，並自動派發任務禮品領收單！' : '已更新該會員目前任務進度！'
    ]);
    exit();
  break;

  // =========================================================================
  // 3. 任務觸發條件規則管理：新增 / 刪除 (fsl_eligibility_rules, target_type=4)
  // =========================================================================
  case 'func_quest_rule_add':
    $ruleType = trim((string)($_POST['erule_type'] ?? ''));
    $ruleOp   = trim((string)($_POST['erule_operator'] ?? '='));
    $ruleVal  = trim((string)($_POST['erule_val'] ?? ''));
    $ruleMsg  = trim((string)($_POST['erule_fail_msg'] ?? ''));

    if ($questID <= 0 || $ruleType === '' || $ruleVal === '' || $ruleMsg === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫觸發條件類型、比對值與提示警語！']);
      exit();
    }

    DB->insert('fsl_eligibility_rules', array(
      'erule_target_type' => 4, // 4 = 每日與成長任務
      'erule_target_id'   => $questID,
      'erule_type'        => $ruleType,
      'erule_operator'    => $ruleOp,
      'erule_val'         => $ruleVal,
      'erule_fail_msg'    => $ruleMsg,
      'erule_sort'        => 100,
      'erule_status'      => 1
    ));

    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '任務觸發條件規則已新增！']);
    exit();
  break;

  case 'func_quest_rule_delete':
    $eruleID = (int)($_POST['eruleID'] ?? 0);
    if ($eruleID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的規則 ID！']);
      exit();
    }
    DB->query("DELETE FROM fsl_eligibility_rules WHERE erule_id = ? AND erule_target_type = 4", array($eruleID));
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '任務觸發條件規則已移除！']);
    exit();
  break;

  // =========================================================================
  // 4. 軟刪除任務定義 (func_quest_delete)
  // =========================================================================
  case 'func_quest_delete':
    if ($questID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的任務 ID！']);
      exit();
    }
    DB->update('fsl_quests', ['quest_status' => 9], ['quest_id' => $questID]);
    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '該階段任務已成功刪除封存！'
    ]);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
