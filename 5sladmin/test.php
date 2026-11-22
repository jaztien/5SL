<?php
    require_once('./includeFiles/common.lib.php');
    define('DOC_PATH_LAYER', './');
    require_once('./includeFiles/db.inc.php');


$mbID = 1;
$_POST['adjVal'] = 100;
$_POST['adjMemo'] = "補漏！";
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
      "pt_forid" => 1,
      "pt_trade" => $delta,
      "pt_redeem" => 0,
      "pt_freepoints" => 0,
      "pt_available" => $newPoints,
      "pt_memo" => $fullMemo,
      "pt_status" => 1
    );
    $ins = DB->insert('fsl_points', $insARR);
    $ptId = DB->lastInsertId();

    // 1. 寫入 fsl_points 流水帳

    /*
    $ins = DB->query(
      "INSERT INTO fsl_points (mb_id, pt_income, pt_type, pt_for, pt_forid, pt_trade, pt_redeem, pt_freepoints, pt_available, pt_memo, pt_status)
       VALUES (?, ?, 4, 9, ?, ?, 0, 0, ?, ?, 1)",
      array($mbID, $ptIncome, (int)$_SESSION['SESS_AD_ID'], $delta, $newPoints, $fullMemo)
    );
    */

    // 2. 重新彙算 fsl_points 並同步校準寫回 fsl_members.mb_points
    //$latestPtInfo = _fn_get_pointinfo($mbID, true);
    //$newPoints    = $latestPtInfo['available'];
  
    //$ptId      = (int)DB->single("SELECT pt_id FROM fsl_points WHERE mb_id = ? ORDER BY pt_id DESC LIMIT 1", array($mbID));
    //$createdAt = DB->single("SELECT pt_created FROM fsl_points WHERE pt_id = ?", array($ptId));

    // 3. 同步發送通知至會員訊息中心
    $signTxt = ($delta > 0) ? "+{$delta}" : "{$delta}";
    $insARR = array(
      "mb_id" => $mbID,
      "no_category" => 1,
      "no_title" => "【PTS 點數異動通知】{$signTxt} PTS",
      "no_content" => "親愛的會員您好，管理團隊已為您的帳戶執行點數異動（{$signTxt} PTS），原因：{$adjMemo}，目前最新可用餘額為 " . number_format($newPoints) . " PTS。",
      "msg_status" => 1
    );
    $ins = DB->insert('fsl_messages', $insARR);

    echo json_encode([
      'RTN'       => 1,
      'msgType'   => 'success',
      'msgStr'    => "PTS 點數調帳完成（{$signTxt} PTS），最新餘額：{$newPoints} PTS",
      'delta'     => $delta,
      'newPoints' => $newPoints,
      'ptId'      => $ptId,
      'createdAt' => $createdAt ?: date('Y-m-d H:i:s')
    ]);

    ?>