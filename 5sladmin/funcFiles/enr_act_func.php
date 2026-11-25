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
$enrID    = (int)($_POST['enrID'] ?? 0);

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

switch ($formAcce) {

  // =========================================================================
  // 1. 手動補登報名 / 更新報名進度與梯次調度 (func_enr_addnew / func_enr_infoUpdate)
  // =========================================================================
  case 'func_enr_addnew':
  case 'func_enr_infoUpdate':
    $acc = ($formAcce === 'func_enr_addnew') ? 'add' : 'up';

    $newSessId  = (int)($formData['sess_id'] ?? 0);
    $quotaType  = (int)($formData['enr_quota_type'] ?? 1);
    $payStatus  = (int)($formData['enr_pay_status'] ?? 0);
    $enrStatus  = (int)($formData['enr_status'] ?? 1);
    $doneLesson = max(0, (int)($formData['enr_done_lessons'] ?? 0));
    $expireAt   = !empty($formData['enr_expire_at']) ? ($formData['enr_expire_at'] . ' 23:59:59') : null;
    $zoomUrl    = trim((string)($formData['enr_zoom_url'] ?? ''));
    $enrTopic   = trim((string)($formData['enr_topic'] ?? ''));
    $enrNote    = trim((string)($formData['enr_note'] ?? ''));

    if ($newSessId <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請選擇開課梯次！']);
      exit();
    }

    if ($acc === 'add') {
      $mbId     = (int)($formData['mb_id'] ?? 0);
      $courseId = (int)($formData['course_id'] ?? 0);
      if ($mbId <= 0 || $courseId <= 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請選擇報名會員與主課程！']);
        exit();
      }

      // 防止同一會員重複報名同一梯次
      $dupEnr = (int)DB->single("SELECT enr_id FROM fsl_course_enrolls WHERE mb_id = ? AND sess_id = ?", array($mbId, $newSessId));
      if ($dupEnr > 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => "該會員已存在此梯次的報名紀錄（ID: #{$dupEnr}），請勿重複建立！"]);
        exit();
      }

      $insData = array(
        'mb_id'            => $mbId,
        'course_id'        => $courseId,
        'sess_id'          => $newSessId,
        'ord_id'           => null,
        'enr_quota_type'   => $quotaType,
        'enr_pay_status'   => $payStatus,
        'enr_status'       => $enrStatus,
        'enr_done_lessons' => $doneLesson,
        'enr_expire_at'    => $expireAt,
        'enr_zoom_url'     => ($zoomUrl !== '' ? $zoomUrl : null),
        'enr_topic'        => ($enrTopic !== '' ? $enrTopic : null),
        'enr_note'         => ($enrNote !== '' ? $enrNote : null)
      );
      DB->insert('fsl_course_enrolls', $insData);
      $newID = (int)DB->lastInsertId();

      // 若狀態為有效占名額，同步更新梯次報名人數
      if (in_array($enrStatus, array(1, 2, 3), true)) {
        DB->query("UPDATE fsl_course_sessions SET sess_enrollee = sess_enrollee + 1 WHERE sess_id = ?", array($newSessId));
      }

      echo json_encode([
        'RTN'     => 1,
        'msgType' => 'success',
        'msgStr'  => "課程報名紀錄 (#{$newID}) 已手動補登完成！",
        'newID'   => $newID
      ]);
    } else {
      if ($enrID <= 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的報名紀錄 ID！']);
        exit();
      }

      $oldRow = DB->row("SELECT sess_id, enr_status FROM fsl_course_enrolls WHERE enr_id = ?", array($enrID));
      if (!$oldRow) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到該筆報名紀錄！']);
        exit();
      }

      $oldSessId = (int)$oldRow['sess_id'];
      $oldStatus = (int)$oldRow['enr_status'];
      $wasActive = in_array($oldStatus, array(1, 2, 3), true);
      $nowActive = in_array($enrStatus, array(1, 2, 3), true);

      $isBatchChanged = ($oldSessId !== $newSessId);
      if ($isBatchChanged) {
        // 更換梯次後清空舊梯次的專屬 Zoom 連結，避免學員點進舊會議室
        $zoomUrl = '';
      }

      $updData = array(
        'sess_id'          => $newSessId,
        'enr_quota_type'   => $quotaType,
        'enr_pay_status'   => $payStatus,
        'enr_status'       => $enrStatus,
        'enr_done_lessons' => $doneLesson,
        'enr_expire_at'    => $expireAt,
        'enr_zoom_url'     => ($zoomUrl !== '' ? $zoomUrl : null),
        'enr_topic'        => ($enrTopic !== '' ? $enrTopic : null),
        'enr_note'         => ($enrNote !== '' ? $enrNote : null)
      );
      DB->update('fsl_course_enrolls', $updData, ['enr_id' => $enrID]);

      // 處理梯次調度或取消時的新舊梯次人數同步增減
      if ($oldSessId !== $newSessId) {
        if ($wasActive && $oldSessId > 0) {
          DB->query("UPDATE fsl_course_sessions SET sess_enrollee = GREATEST(0, sess_enrollee - 1) WHERE sess_id = ?", array($oldSessId));
        }
        if ($nowActive && $newSessId > 0) {
          DB->query("UPDATE fsl_course_sessions SET sess_enrollee = sess_enrollee + 1 WHERE sess_id = ?", array($newSessId));
        }
      } else {
        if ($wasActive && !$nowActive && $newSessId > 0) {
          DB->query("UPDATE fsl_course_sessions SET sess_enrollee = GREATEST(0, sess_enrollee - 1) WHERE sess_id = ?", array($newSessId));
        } elseif (!$wasActive && $nowActive && $newSessId > 0) {
          DB->query("UPDATE fsl_course_sessions SET sess_enrollee = sess_enrollee + 1 WHERE sess_id = ?", array($newSessId));
        }
      }

      $msgTip = $isBatchChanged ? '學員已調度至新梯次（舊 Zoom 連結已清除，請點擊左側按鈕重新派發新梯次連結）！' : '學員報名進度與梯次調度已儲存更新！';
      echo json_encode([
        'RTN'     => 1,
        'msgType' => 'success',
        'msgStr'  => $msgTip
      ]);

    }
    exit();
  break;

  // =========================================================================
  // 2. 左側快速發送 / 補發 Zoom 專屬授權連結 (func_enr_sendZoom)
  // =========================================================================
  case 'func_enr_sendZoom':
    if ($enrID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的報名紀錄 ID！']);
      exit();
    }

    $info = DB->row("
      SELECT e.enr_id, e.mb_id, e.sess_id, e.enr_link_sent_cnt,
             c.course_title, s.sess_batch, s.sess_startdate, s.sess_starttime, s.sess_meetingid, s.sess_passcode, s.sess_join_url
      FROM fsl_course_enrolls e
      LEFT JOIN fsl_courses c ON e.course_id = c.course_id
      LEFT JOIN fsl_course_sessions s ON e.sess_id = s.sess_id
      WHERE e.enr_id = ?
    ", array($enrID));

    if (!$info || empty($info['sess_join_url'])) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '所屬梯次尚未設定 Zoom 會議室加入連結，請先至「課程梯次管理」設定！']);
      exit();
    }

    $baseJoin = trim($info['sess_join_url']);
    $sep      = (strpos($baseJoin, '?') !== false) ? '&' : '?';
    $authSig  = substr(hash_hmac('sha256', $info['sess_id'] . '_' . $enrID . '_' . $info['mb_id'], 'FSL_ZOOM_AUTH'), 0, 12);
    $authUrl  = $baseJoin . $sep . "fsl_enr={$enrID}&auth={$authSig}";
    $newCnt   = (int)$info['enr_link_sent_cnt'] + 1;

    DB->update('fsl_course_enrolls', array(
      'enr_zoom_url'      => $authUrl,
      'enr_link_sent_at'  => date('Y-m-d H:i:s'),
      'enr_link_sent_cnt' => $newCnt
    ), ['enr_id' => $enrID]);

    $cTitle    = $info['course_title'] ?? '課程直播';
    $startTime = ($info['sess_startdate'] ?? '') . ' ' . substr(($info['sess_starttime'] ?? ''), 0, 5);
    DB->insert('fsl_notifications', array(
      'mb_id'       => (int)$info['mb_id'],
      'no_category' => 2,
      'no_title'    => "【上課專屬連結】《{$cTitle}》第 {$info['sess_batch']} 梯次",
      'no_content'  => "親愛的學員您好，您的《{$cTitle}》（開課時間：{$startTime}）專屬 Zoom 教室進入連結如下：\n{$authUrl}\n會議室 ID：{$info['sess_meetingid']}｜密碼：{$info['sess_passcode']}"
    ));

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '專屬 Zoom 上課連結已派發並寫入會員通知中心！',
      'sentCnt' => $newCnt,
      'zoomUrl' => $authUrl
    ]);
    exit();
  break;

  // =========================================================================
  // 3. 一鍵核發完課結業並發送祝賀通知 (func_enr_graduate)
  // =========================================================================
  case 'func_enr_graduate':
    if ($enrID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的報名紀錄 ID！']);
      exit();
    }

    $info = DB->row("
      SELECT e.mb_id, e.sess_id, c.course_title, c.course_lesson,
             (SELECT COUNT(*) FROM fsl_course_lessons l WHERE l.sess_id = e.sess_id AND l.less_status != 9) AS actual_lessons
      FROM fsl_course_enrolls e
      LEFT JOIN fsl_courses c ON e.course_id = c.course_id
      WHERE e.enr_id = ?
    ", array($enrID));

    if (!$info) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '找不到該筆報名紀錄！']);
      exit();
    }

    $finalLessons = ((int)$info['actual_lessons'] > 0) ? (int)$info['actual_lessons'] : max(1, (int)$info['course_lesson']);
    DB->update('fsl_course_enrolls', array(
      'enr_status'       => 3, // 3-已完課結業
      'enr_done_lessons' => $finalLessons
    ), ['enr_id' => $enrID]);


    $cTitle = $info['course_title'] ?? '專屬課程';
    DB->insert('fsl_notifications', array(
      'mb_id'       => (int)$info['mb_id'],
      'no_category' => 2,
      'no_title'    => "【完課結業祝賀】恭喜完成《{$cTitle}》全部課程！",
      'no_content'  => "親愛的學員您好，恭喜您圓滿完成《{$cTitle}》的所有學習單元！歡迎隨時透過「我的專屬課程」回顧複習講義與影音，我們也在人生第五季持續陪伴您前行。"
    ));

    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '已核發「完課結業」狀態並推播結業祝賀通知！']);
    exit();
  break;

  // =========================================================================
  // 4. 人工確認收款入帳並開立電子發票 (func_enr_confirmPaid)
  // =========================================================================
  case 'func_enr_confirmPaid':
    if ($enrID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的報名紀錄 ID！']);
      exit();
    }

    $info = DB->row("
      SELECT e.enr_id, e.mb_id, e.sess_id, e.ord_id, e.enr_pay_status, o.ord_no, o.ord_total
      FROM fsl_course_enrolls e
      LEFT JOIN fsl_orders o ON e.ord_id = o.ord_id
      WHERE e.enr_id = ?
    ", array($enrID));

    if (!$info || empty($info['ord_id'])) {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '此報名單無關聯前台訂單！']);
      exit();
    }

    $ordId  = (int)$info['ord_id'];
    $nowStr = date('Y-m-d H:i:s');
    $invNo  = 'EF-' . mt_rand(10000000, 99999999);
    $randCd = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

    // 1. 更新報名付款狀態為 1-已付款完成
    DB->update('fsl_course_enrolls', ['enr_pay_status' => 1], ['enr_id' => $enrID]);

    // 2. 更新訂單主表為 2-已付款處理中
    DB->update('fsl_orders', ['ord_status' => 2], ['ord_id' => $ordId]);

    // 3. 更新電子發票為 1-已付款並正式開立
    DB->query(
      "UPDATE fsl_invoices SET inv_status = 1, inv_paid_at = ?, inv_issued_at = ?, inv_no = COALESCE(inv_no, ?), inv_random_code = COALESCE(inv_random_code, ?) WHERE ord_id = ?",
      array($nowStr, $nowStr, $invNo, $randCd, $ordId)
    );

    // 4. 通知會員款項已入帳
    DB->insert('fsl_notifications', array(
      'mb_id'       => (int)$info['mb_id'],
      'no_category' => 2,
      'no_title'    => "【收款與發票開立通知】訂單 {$info['ord_no']}",
      'no_content'  => "親愛的學員您好，我們已確認收到您的訂單（{$info['ord_no']}，金額 NT$ " . number_format((float)$info['ord_total']) . "）款項，電子發票已同步開立，您可隨時至「訂單與發票中心」查詢。"
    ));

    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '已確認收款入帳、開立電子發票並通知會員！']);
    exit();
  break;

  // =========================================================================
  // 5. 發送一對一專屬通訊通知至會員中心 (func_enr_sendNoti)
  // =========================================================================
  case 'func_enr_sendNoti':
    $mbID      = (int)($_POST['mbID'] ?? 0);
    $noTitle   = trim((string)($_POST['noTitle'] ?? ''));
    $noContent = trim((string)($_POST['noContent'] ?? ''));

    if ($mbID <= 0 || $noTitle === '' || $noContent === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫通知主旨與訊息內容！']);
      exit();
    }

    DB->insert('fsl_notifications', array(
      'mb_id'       => $mbID,
      'no_category' => 2,
      'no_title'    => $noTitle,
      'no_content'  => $noContent
    ));

    echo json_encode([
      'RTN'       => 1,
      'msgType'   => 'success',
      'msgStr'    => '專屬訊息已推播至該學員的會員通知中心！',
      'createdAt' => date('Y-m-d H:i:s')
    ]);
    exit();
  break;

  // =========================================================================
  // 6. 儲存課後追蹤與後續服務日誌 (func_enr_followupUpdate)
  // =========================================================================
  case 'func_enr_followupUpdate':
    if ($enrID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的報名紀錄 ID！']);
      exit();
    }
    $note = trim((string)($formData['enr_note'] ?? ''));
    DB->update('fsl_course_enrolls', ['enr_note' => $note], ['enr_id' => $enrID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '課後追蹤與顧問服務紀錄已儲存！']);
    exit();
  break;

  // =========================================================================
  // 7. 取消報名並釋出梯次名額 (func_enr_delete)
  // =========================================================================
  case 'func_enr_delete':
    if ($enrID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的報名紀錄 ID！']);
      exit();
    }
    $row = DB->row("SELECT sess_id, enr_status FROM fsl_course_enrolls WHERE enr_id = ?", array($enrID));
    if ($row && in_array((int)$row['enr_status'], array(1, 2, 3), true) && (int)$row['sess_id'] > 0) {
      DB->query("UPDATE fsl_course_sessions SET sess_enrollee = GREATEST(0, sess_enrollee - 1) WHERE sess_id = ?", array((int)$row['sess_id']));
    }
    DB->update('fsl_course_enrolls', ['enr_status' => 0], ['enr_id' => $enrID]);
    echo json_encode(['RTN' => 1, 'msgType' => 'success', 'msgStr' => '該筆報名已取消並釋出梯次名額！']);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
