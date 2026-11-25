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
$robID    = (int)($_POST['robID'] ?? 0);

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

switch ($formAcce) {

  // =========================================================================
  // 1. 新增 / 更新自動應答知識庫規則 (func_robot_addnew / func_robot_infoUpdate)
  // =========================================================================
  case 'func_robot_addnew':
  case 'func_robot_infoUpdate':
    $acc = ($formAcce === 'func_robot_addnew') ? 'add' : 'up';

    $topic     = trim((string)($formData['rob_topic'] ?? ''));
    $category  = (int)($formData['rob_category'] ?? 0);
    $keywords  = trim((string)($formData['rob_keywords'] ?? ''));
    $matchMode = (int)($formData['rob_match_mode'] ?? 1);
    $priority  = max(1, (int)($formData['rob_priority'] ?? 100));
    $needHuman = (int)($formData['rob_need_human'] ?? 0);
    $status    = (int)($formData['rob_status'] ?? 1);
    $replies   = trim((string)($formData['rob_replies'] ?? ''));

    if ($topic === '' || $keywords === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫問題主題名稱與觸發關鍵字！']);
      exit();
    }

    $decodedReplies = json_decode($replies, true);
    if (!is_array($decodedReplies) || empty($decodedReplies)) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請至少建立一組有效的自動應答變體內容！']);
      exit();
    }

    // 正規化關鍵字分隔符號（全形逗號/頓號轉為半形逗號並去除多餘空白）
    $kwArr   = array_filter(array_map('trim', preg_split('/[,，、\n]+/u', $keywords)));
    $cleanKw = implode(', ', array_unique($kwArr));

    // 重新編碼乾淨的多口吻 JSON
    $cleanVariants = array();
    foreach ($decodedReplies as $rep) {
      $cTxt = trim((string)($rep['content'] ?? ''));
      if ($cTxt === '') continue;
      $cleanVariants[] = array(
        'tone'    => trim((string)($rep['tone'] ?? '標準應答')) ?: '標準應答',
        'weight'  => max(1, (int)($rep['weight'] ?? 50)),
        'content' => $cTxt
      );
    }

    if (empty($cleanVariants)) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '應答內容不可為空白！']);
      exit();
    }

    $saveData = array(
      'rob_category'   => $category,
      'rob_topic'      => $topic,
      'rob_keywords'   => $cleanKw,
      'rob_match_mode' => $matchMode,
      'rob_priority'   => $priority,
      'rob_replies'    => json_encode($cleanVariants, JSON_UNESCAPED_UNICODE),
      'rob_need_human' => $needHuman,
      'rob_status'     => $status
    );

    if ($acc === 'add') {
      $saveData['rob_hit_count'] = 0;
      DB->insert('fsl_robot', $saveData);
      $newID = (int)DB->lastInsertId();
      echo json_encode([
        'RTN'     => 1,
        'msgType' => 'success',
        'msgStr'  => "自動應答規則【{$topic}】已成功建立！",
        'newID'   => $newID
      ]);
    } else {
      if ($robID <= 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的規則 ID！']);
        exit();
      }
      DB->update('fsl_robot', $saveData, ['rob_id' => $robID]);
      echo json_encode([
        'RTN'     => 1,
        'msgType' => 'success',
        'msgStr'  => '自動應答規則與多語氣變體已儲存更新！'
      ]);
    }
    exit();
  break;

  // =========================================================================
  // 2. 軟刪除自動應答規則 (func_robot_delete)
  // =========================================================================
  case 'func_robot_delete':
    if ($robID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的規則 ID！']);
      exit();
    }
    DB->update('fsl_robot', ['rob_status' => 9], ['rob_id' => $robID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '自動應答規則已成功刪除！']);
    exit();
  break;

  // =========================================================================
  // 3. 即時模擬問答比對測試器 (func_robot_simulate)
  // =========================================================================
  case 'func_robot_simulate':
    $simCat = (int)($_POST['category'] ?? 0);
    $simQ   = trim((string)($_POST['question'] ?? ''));

    if ($simQ === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請輸入模擬發問內容！']);
      exit();
    }

    $rawText = mb_strtolower($simQ, 'UTF-8');
    $rules   = DB->query("
      SELECT *
      FROM fsl_robot
      WHERE rob_status = 1 AND (rob_category = 0 OR rob_category = ? OR ? = 0)
      ORDER BY rob_priority ASC, rob_id ASC
    ", array($simCat, $simCat));

    if (!is_array($rules) || empty($rules)) {
      echo json_encode(['RTN' => 1, 'matched' => false]);
      exit();
    }

    $bestRule  = null;
    $bestScore = 0;

    foreach ($rules as $rule) {
      $kwStr = trim((string)($rule['rob_keywords'] ?? ''));
      if ($kwStr === '') continue;

      $kwList = array_filter(array_map('trim', preg_split('/[,，、\n]+/u', mb_strtolower($kwStr, 'UTF-8'))));
      if (empty($kwList)) continue;

      $matchMode = (int)($rule['rob_match_mode'] ?? 1);
      $hitCount  = 0;
      foreach ($kwList as $kw) {
        if ($kw !== '' && mb_strpos($rawText, $kw, 0, 'UTF-8') !== false) {
          $hitCount++;
        }
      }

      $score = 0;
      if ($matchMode === 1) {
        if ($hitCount > 0) {
          $score = ($hitCount * 20) + max(1, (200 - (int)$rule['rob_priority']) / 10);
        }
      } elseif ($matchMode === 2) {
        if ($hitCount === count($kwList)) {
          $score = ($hitCount * 35) + max(1, (200 - (int)$rule['rob_priority']) / 10);
        }
      } elseif ($matchMode === 3) {
        $topicLow = mb_strtolower(trim((string)$rule['rob_topic']), 'UTF-8');
        similar_text($rawText, $topicLow, $simPct);
        if ($hitCount > 0 || $simPct >= 45) {
          $score = ($hitCount * 15) + $simPct + max(1, (200 - (int)$rule['rob_priority']) / 10);
        }
      }

      if ($score > $bestScore) {
        $bestScore = $score;
        $bestRule  = $rule;
      }
    }

    if (!$bestRule) {
      echo json_encode(['RTN' => 1, 'matched' => false]);
      exit();
    }

    $replies = json_decode((string)$bestRule['rob_replies'], true);
    if (!is_array($replies) || empty($replies)) {
      echo json_encode(['RTN' => 1, 'matched' => false]);
      exit();
    }

    $totalWeight = 0;
    foreach ($replies as $rep) {
      $totalWeight += max(1, (int)($rep['weight'] ?? 10));
    }
    $randPoint = mt_rand(1, $totalWeight);
    $cursor    = 0;
    $chosen    = $replies;

    foreach ($replies as $rep) {
      $cursor += max(1, (int)($rep['weight'] ?? 10));
      if ($randPoint <= $cursor) {
        $chosen = $rep;
        break;
      }
    }

    $toneTag   = trim((string)($chosen['tone'] ?? '標準應答'));
    $replyText = str_replace(
      array('{會員姓名}', '{日期}', '{語氣標籤}'),
      array('王小明（測試會員）', date('Y-m-d'), $toneTag),
      trim((string)($chosen['content'] ?? ''))
    );

    echo json_encode([
      'RTN'     => 1,
      'matched' => true,
      'rob_id'  => (int)$bestRule['rob_id'],
      'topic'   => $bestRule['rob_topic'],
      'score'   => round($bestScore, 1),
      'tone'    => $toneTag,
      'reply'   => $replyText
    ]);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
