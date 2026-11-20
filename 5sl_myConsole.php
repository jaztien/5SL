<?php
// 2. 宣告當前頁面身分為 myConsole（訊息中心 / 主控台）
$current_page = 'myConsole';
// 引入共用頁首
include_once '5sl_include_header.php';

$mb_id = (int)($_SESSION['mbID'] ?? $_SESSION['user_id'] ?? 0);
$mb_lvl     = isset($_SESSION['mbLevel']) ? (int)$_SESSION['mbLevel'] : 3;

// 1. 查詢會員可見的「訊息與通知」（包含個人專屬通知 + 符合等級的全站廣播公告）
$sql_noti = "
    SELECT n.no_id, n.no_created, n.no_category, n.no_title, n.no_content, n.no_link, r.nor_id
    FROM fsl_notifications n
    LEFT JOIN fsl_notification_reads r ON n.no_id = r.no_id AND r.mb_id = ?
    WHERE (n.mb_id = ? OR n.mb_id IS NULL)
      AND n.no_minlvl <= ?
    ORDER BY n.no_created DESC
    LIMIT 50
";
$noti_list = ($mb_id > 0) ? $DB->query($sql_noti, [$mb_id, $mb_id, $mb_lvl]) : [];
if (!is_array($noti_list)) $noti_list = [];

// 計算未讀總數 (nor_id 為 NULL 代表尚未閱讀)
$unread_count = 0;
foreach ($noti_list as $row) {
    if (empty($row['nor_id'])) {
        $unread_count++;
    }
}

// 分類對照表（對齊資料庫 no_category 數字與前端篩選 data-cat）
$cat_map = [
    1 => ['code' => 'system',  'label' => '系統公告', 'badge' => 'bg-secondary-subtle text-secondary'],
    2 => ['code' => 'order',   'label' => '訂單通知', 'badge' => 'bg-primary-subtle text-primary'],
    3 => ['code' => 'support', 'label' => '客服回覆', 'badge' => 'bg-success-subtle text-success'],
    4 => ['code' => 'promo',   'label' => '專屬促銷', 'badge' => 'bg-warning text-dark'],
];

// 2. 查詢會員的「客服諮詢工單」與所有對話明細
$sql_tickets = "
    SELECT * FROM fsl_support_tickets
    WHERE mb_id = ?
    ORDER BY spt_updated DESC
";
$ticket_list = ($mb_id > 0) ? $DB->query($sql_tickets, [$mb_id]) : [];
if (!is_array($ticket_list)) $ticket_list = [];

// 預先撈出這些工單底下的對話明細並依 spt_id 分組（避免 N+1 查詢效能問題）
$replies_by_ticket = [];
if (!empty($ticket_list)) {
    $spt_ids = array_column($ticket_list, 'spt_id');
    $placeholders = implode(',', array_fill(0, count($spt_ids), '?'));
    $sql_replies = "
        SELECT * FROM fsl_support_replies
        WHERE spt_id IN ($placeholders)
        ORDER BY spr_created ASC
    ";
    $all_replies = $DB->query($sql_replies, $spt_ids);
    if (is_array($all_replies)) {
        foreach ($all_replies as $rep) {
            $replies_by_ticket[$rep['spt_id']][] = $rep;
        }
    }
}

// 3. 查詢會員「收藏與追蹤」清單 (fsl_wishlists 關聯 fsl_courses / fsl_merchandise / fsl_resources)
$sql_wishlist = "
    SELECT w.wish_id, w.wish_created, w.wish_target_type, w.wish_target_id, w.wish_note,
           c.course_title, c.course_brief, c.course_cost, c.course_type, c.course_status,
           m.merch_title, m.merch_brief, m.merch_price_twd, m.merch_price_usd, m.merch_sale_usd, m.merch_type, m.merch_status,
           r.res_title, r.res_brief, r.res_type, r.res_created, r.res_status
    FROM fsl_wishlists w
    LEFT JOIN fsl_courses c ON w.wish_target_type = 1 AND w.wish_target_id = c.course_id
    LEFT JOIN fsl_merchandise m ON w.wish_target_type = 2 AND w.wish_target_id = m.merch_id
    LEFT JOIN fsl_resources r ON w.wish_target_type = 3 AND w.wish_target_id = r.res_id
    WHERE w.mb_id = ?
    ORDER BY w.wish_created DESC
";
$raw_wishlist = ($mb_id > 0) ? $DB->query($sql_wishlist, array($mb_id)) : array();
if (!is_array($raw_wishlist)) $raw_wishlist = array();

$res_type_labels = array(
    1 => '深度文字專文',
    2 => '高解析圖檔教材',
    3 => '文書簡報檔案',
    4 => 'YouTube 限定視訊',
    5 => '私房引導音檔'
);

$wishlist_items = array();
foreach ($raw_wishlist as $wish_row) {
    $wish_type = (int)$wish_row['wish_target_type'];

    if ($wish_type === 1 && !empty($wish_row['course_title']) && (int)$wish_row['course_status'] === 1) {
        $is_consult = ((int)$wish_row['course_type'] === 2);
        $wishlist_items[] = array(
            'wish_id'       => (int)$wish_row['wish_id'],
            'wish_type'     => 1,
            'wish_category' => 'course',
            'target_id'     => (int)$wish_row['wish_target_id'],
            'badge_label'   => $is_consult ? '一對一顧問服務' : '陪跑與線上課程',
            'badge_cls'     => 'bg-warning text-dark',
            'title'         => $wish_row['course_title'],
            'brief'         => strip_tags($wish_row['course_brief'] ?? ''),
            'note'          => $wish_row['wish_note'] ?? '',
            'price_txt'     => 'NT$ ' . number_format((float)$wish_row['course_cost']),
            'created_date'  => date('Y-m-d', strtotime($wish_row['wish_created'])),
            'can_add_cart'  => true
        );
    } elseif ($wish_type === 2 && !empty($wish_row['merch_title']) && (int)$wish_row['merch_status'] === 1) {
        $twd_price = (float)$wish_row['merch_price_twd'];
        if ($twd_price <= 0) {
            $usd_price = ($wish_row['merch_sale_usd'] !== null) ? (float)$wish_row['merch_sale_usd'] : (float)$wish_row['merch_price_usd'];
            $twd_price = round($usd_price * 32);
        }
        $wishlist_items[] = array(
            'wish_id'       => (int)$wish_row['wish_id'],
            'wish_type'     => 2,
            'wish_category' => 'merch',
            'target_id'     => (int)$wish_row['wish_target_id'],
            'badge_label'   => ((int)$wish_row['merch_type'] === 1) ? '實體美學商品' : '數位影音/音檔',
            'badge_cls'     => 'bg-primary',
            'title'         => $wish_row['merch_title'],
            'brief'         => strip_tags($wish_row['merch_brief'] ?? ''),
            'note'          => $wish_row['wish_note'] ?? '',
            'price_txt'     => 'NT$ ' . number_format($twd_price),
            'created_date'  => date('Y-m-d', strtotime($wish_row['wish_created'])),
            'can_add_cart'  => true
        );
    } elseif ($wish_type === 3 && !empty($wish_row['res_title']) && (int)$wish_row['res_status'] === 1) {
        $r_sub_type = (int)($wish_row['res_type'] ?? 1);
        $wishlist_items[] = array(
            'wish_id'       => (int)$wish_row['wish_id'],
            'wish_type'     => 3,
            'wish_category' => 'article',
            'target_id'     => (int)$wish_row['wish_target_id'],
            'badge_label'   => $res_type_labels[$r_sub_type] ?? '限定素材資源',
            'badge_cls'     => 'bg-success',
            'title'         => $wish_row['res_title'],
            'brief'         => strip_tags($wish_row['res_brief'] ?? ''),
            'note'          => $wish_row['wish_note'] ?? '',
            'price_txt'     => '上架於 ' . date('Y-m-d', strtotime($wish_row['res_created'])),
            'created_date'  => date('Y-m-d', strtotime($wish_row['wish_created'])),
            'can_add_cart'  => false
        );
    } else {
        $type_names = array(1 => '陪跑與線上課程', 2 => '精選美學商品', 3 => '限定素材資源');
        $wishlist_items[] = array(
            'wish_id'       => (int)$wish_row['wish_id'],
            'wish_type'     => $wish_type,
            'wish_category' => 'other',
            'target_id'     => (int)$wish_row['wish_target_id'],
            'badge_label'   => $type_names[$wish_type] ?? '收藏項目',
            'badge_cls'     => 'bg-secondary',
            'title'         => ($wish_row['wish_note'] ?: "追蹤項目 #{$wish_row['wish_target_id']}"),
            'brief'         => '',
            'note'          => $wish_row['wish_note'] ?? '',
            'price_txt'     => '收藏於 ' . date('Y-m-d', strtotime($wish_row['wish_created'])),
            'created_date'  => date('Y-m-d', strtotime($wish_row['wish_created'])),
            'can_add_cart'  => false
        );
    }
}
$wishlist_count = count($wishlist_items);
// 4. 查詢會員「近期課程與服務排程提醒」 (優先撈取即將到來的未來預約/直播，其次顯示學習中課程)
$sql_upcoming = "
    SELECT 
        e.enr_id, e.enr_pay_status, e.enr_status, e.enr_done_lessons, e.enr_topic,
        c.course_id, c.course_title, c.course_type,
        s.sess_id, s.sess_batch, s.sess_altname, s.sess_startdate, s.sess_starttime, s.sess_duration, s.sess_lesson, s.sess_meetingid
    FROM fsl_course_enrolls e
    INNER JOIN fsl_courses c ON e.course_id = c.course_id
    INNER JOIN fsl_course_sessions s ON e.sess_id = s.sess_id
    WHERE e.mb_id = ?
      AND e.enr_status IN (1, 2, 4)
      AND e.enr_pay_status IN (1, 2)
    ORDER BY 
      (CONCAT(s.sess_startdate, ' ', s.sess_starttime) >= NOW()) DESC,
      ABS(TIMESTAMPDIFF(SECOND, NOW(), CONCAT(s.sess_startdate, ' ', s.sess_starttime))) ASC
    LIMIT 2
";
$upcoming_schedules = ($mb_id > 0) ? $DB->query($sql_upcoming, array($mb_id)) : array();
if (!is_array($upcoming_schedules)) $upcoming_schedules = array();

$week_chars = array('日', '一', '二', '三', '四', '五', '六');

// 預載所有上架課程詳細資訊（供「前往報名 / 我要預約」確認視窗顯示主圖、簡介、價格）
$courses_detail_map = array();
$all_courses_rows = $DB->query("SELECT course_id, course_title, course_brief, course_cost, course_type, course_max FROM fsl_courses WHERE course_status = 1");
if (is_array($all_courses_rows)) {
    foreach ($all_courses_rows as $cr) {
        $cid = (int)$cr['course_id'];
        $cover_path = './5sladmin/uploadFiles/cs/cimg_' . $cid . '_.jpg';
        $cover_url  = file_exists($cover_path)
            ? ($cover_path . '?v=' . filemtime($cover_path))
            : ('imageFiles/test/' . (($cid % 18) + 1) . '.jpg');

        $courses_detail_map[$cid] = array(
            'course_id'    => $cid,
            'course_title' => $cr['course_title'],
            'course_brief' => strip_tags($cr['course_brief'] ?? '人生第五季精選陪跑與生命美學課程，帶您深度梳理並開啟全新篇章。'),
            'course_cost'  => (int)round((float)$cr['course_cost']),
            'course_type'  => (int)$cr['course_type'],
            'cover_url'    => $cover_url,
            'sessions'     => array()
        );
    }
}

// 預載所有課程目前開放報名的梯次 / 預約時段 (fsl_course_sessions)
$sess_rows = $DB->query("
    SELECT s.sess_id, s.course_id, s.sess_batch, s.sess_altname, s.sess_startdate, s.sess_starttime, s.sess_duration, s.sess_enrollee, c.course_max
    FROM fsl_course_sessions s
    INNER JOIN fsl_courses c ON s.course_id = c.course_id
    WHERE s.sess_status = 1 AND c.course_status = 1 AND s.sess_enrollee < c.course_max
    ORDER BY s.sess_startdate ASC, s.sess_starttime ASC
");
//$wk_chars = array('日', '一', '二', '三', '四', '五', '六');
if (is_array($sess_rows)) {
    foreach ($sess_rows as $sr) {
        $cid = (int)$sr['course_id'];
        if (!isset($courses_detail_map[$cid])) continue;
        $ts1 = strtotime($sr['sess_startdate'] . ' ' . $sr['sess_starttime']);
        $ts2 = $ts1 + ((int)$sr['sess_duration'] * 60);
        $rem = max(1, (int)$sr['course_max'] - (int)$sr['sess_enrollee']);
        $lbl = '第 ' . (int)$sr['sess_batch'] . ' 梯 • ' . date('Y-m-d', $ts1) . ' (' . $week_chars[(int)date('w', $ts1)] . ') ' . date('H:i', $ts1) . '-' . date('H:i', $ts2);
        if (!empty($sr['sess_altname'])) $lbl .= '｜' . $sr['sess_altname'];
        $lbl .= " (剩餘 {$rem} 名額)";

        $courses_detail_map[$cid]['sessions'][] = array(
            'sess_id' => (int)$sr['sess_id'],
            'label'   => $lbl
        );
    }
}

// 預載所有上架商品詳細資訊（供「立即購買」確認視窗顯示主圖、簡介、價格與庫存數量）
$merch_detail_map = array();
$all_merch_rows = $DB->query("
    SELECT merch_id, merch_title, merch_brief, merch_price_twd, merch_price_usd, merch_sale_usd, merch_type, merch_stock
    FROM fsl_merchandise
    WHERE merch_status = 1
");
if (is_array($all_merch_rows)) {
    foreach ($all_merch_rows as $mr) {
        $mid = (int)$mr['merch_id'];
        $twd_p = (int)round((float)$mr['merch_price_twd']);
        if ($twd_p <= 0) {
            $usd_p = ($mr['merch_sale_usd'] !== null) ? (float)$mr['merch_sale_usd'] : (float)$mr['merch_price_usd'];
            $twd_p = (int)round($usd_p * 32);
        }

        // 商品封面主圖（優先讀取 uploadFiles/mc/mimg_{id}_.jpg，無檔案時自動退回預設圖）
        $m_cover_path = './5sladmin/uploadFiles/mc/mimg_' . $mid . '_.jpg';
        $m_cover_url  = file_exists($m_cover_path)
            ? ($m_cover_path . '?v=' . filemtime($m_cover_path))
            : ('imageFiles/test/' . ((($mid * 7 + 2) % 18) + 1) . '.jpg');

        $merch_detail_map[$mid] = array(
            'merch_id'    => $mid,
            'merch_title' => $mr['merch_title'],
            'merch_brief' => strip_tags($mr['merch_brief'] ?? '人生第五季精選生命美學與自我整理專屬物件。'),
            'unit_price'  => $twd_p,
            'merch_type'  => (int)$mr['merch_type'], // 1=實體商品, 2=數位物件
            'merch_stock' => (int)$mr['merch_stock'], // -1=無限量
            'cover_url'   => $m_cover_url
        );
    }
}




?>

<div class="container my-5 py-4">
    <div class="row g-4 mt-2">
        
        <!-- 左側：會員中心共用子導航列 -->
        <div class="col-md-4 col-lg-3">
            <?php include_once '5sl_include_subnavi.php'; ?>
        </div>

        <!-- 右側：訊息中心核心工作視窗 -->
        <div class="col-md-8 col-lg-9">
            <div class="card border-0 shadow-sm p-4 p-md-5 bg-white bg-opacity-75 rounded-3 h-100" style="min-height: 788px;">
                <div class="card-body p-0">
                    
                    <!-- ============================================================
                         區塊 0：登入者基本資訊與即時狀態總覽橫幅
                         ============================================================ -->
                    <div class="p-4 rounded-3 text-white mb-4 shadow-sm" style="background: linear-gradient(135deg, #16181b 0%, #2b3035 100%); border-left: 5px solid #ffc107;">
                        <div class="row align-items-center g-3">
                            <div class="col-md-7 d-flex align-items-center gap-3">
                                <img src="<?php echo _fn_getMemberAvatar($mb_id, true); ?>" class="rounded-circle border border-2 border-warning shadow flex-shrink-0" style="width: 68px !important; height: 68px !important; min-width: 68px; object-fit: cover;" alt="會員頭像">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h5 class="fw-bold text-white mb-0">
                                            歡迎回來，<?php echo $_SESSION['mbTitle'] ?? '會員'; ?>
                                        </h5>
                                        <span class="badge<?php echo MEMBER_GRADE['color'] ?? ''; ?> fw-bold" style="font-size: 0.7rem;"><?php echo MEMBER_GRADE['title'] ?? '&nbsp;'; ?></span>
                                    </div>
                                    <div class="text-white-50 small mb-1">
                                        <i class="bi bi-envelope me-1"></i><?php echo $_SESSION['mbEmail'] ?? ''; ?>
                                    </div>
                                    <div class="text-white-50" style="font-size: 0.75rem;">
                                        <i class="bi bi-shield-check text-success me-1"></i>帳號狀態正常 • 上次登入：今日 09:45 (新北新店)
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="row g-2 text-center">
                                    <div class="col-4">
                                        <div class="p-2 rounded bg-dark border border-secondary">
                                            <div class="fw-bold text-warning fs-5" id="hubUnreadCount"><?php echo $unread_count; ?></div>
                                            <div class="text-white-50" style="font-size: 0.7rem;">未讀通知</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 rounded bg-dark border border-secondary">
                                            <div class="fw-bold text-info fs-5"><?php echo number_format($member_pts ?? 0); ?></div>
                                            <div class="text-white-50" style="font-size: 0.7rem;">紅利 PTS</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 rounded bg-dark border border-secondary">
                                            <div class="fw-bold text-success fs-5">3</div>
                                            <div class="text-white-50" style="font-size: 0.7rem;">可用票券</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================
                         區塊 1：主要商品（課程）排程顯示以及提醒通知（資料庫動態讀取）
                         ============================================================ -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">
                                <i class="bi bi-calendar2-week-fill me-2 text-warning"></i>近期課程與服務排程提醒
                            </h5>
                            <a href="5sl_myProducts.php" class="small text-decoration-none fw-bold text-primary">
                                管理全部課程與預約 <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>

                        <div class="row g-3">
                            <?php if (empty($upcoming_schedules)): ?>
                                <div class="col-12">
                                    <div class="p-4 rounded-3 border bg-light text-center">
                                        <p class="text-muted small mb-2">目前尚無已排定的近期課程或一對一顧問預約行程。</p>
                                        <a href="5sl_myProducts.php" class="btn btn-dark text-warning btn-sm fw-bold px-3">
                                            <i class="bi bi-calendar-plus me-1"></i>立即預約一對一顧問輔導
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($upcoming_schedules as $idx => $up):
                                    $enr_id     = (int)$up['enr_id'];
                                    $is_consult = ((int)$up['course_type'] === 2);
                                    $start_ts   = strtotime($up['sess_startdate'] . ' ' . $up['sess_starttime']);
                                    $is_future  = ($start_ts > time());
                                    $diff_days  = (int)ceil(($start_ts - time()) / 86400);
                                    $dt_label   = date('Y-m-d', $start_ts) . ' (' . $week_chars[(int)date('w', $start_ts)] . ') ' . date('H:i', $start_ts);

                                    // 計算課程進度百分比
                                    $tot_les = max(1, (int)$up['sess_lesson']);
                                    $don_les = min($tot_les, (int)$up['enr_done_lessons']);
                                    $pct     = (int)round(($don_les / $tot_les) * 100);

                                    // 第一張或 7 天內即將到來的行程採用高優先級金框提醒樣式
                                    $is_urgent = ($is_future && $diff_days <= 7);
                                    $box_cls   = $is_urgent ? 'border-warning bg-light' : 'bg-white';
                                ?>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 border <?php echo $box_cls; ?> h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <?php if ($is_future): ?>
                                                        <span class="badge <?php echo ($diff_days <= 7) ? 'bg-danger badge-alarm-pulse' : 'bg-warning text-dark'; ?>">
                                                            <i class="bi bi-bell-fill me-1"></i>即將到來 • 倒數 <?php echo max(1, $diff_days); ?> 天
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary-subtle text-primary">
                                                            <i class="bi bi-play-circle-fill me-1"></i><?php echo $is_consult ? '顧問服務進行中' : "學習進度 {$pct}%"; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <small class="fw-bold <?php echo $is_urgent ? 'text-dark' : 'text-muted'; ?>"><?php echo $dt_label; ?></small>
                                                </div>

                                                <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($up['course_title']); ?></h6>
                                                <p class="text-muted small mb-2">
                                                    <?php if ($is_consult): ?>
                                                        <?php echo !empty($up['enr_topic']) ? ('諮詢主題：' . htmlspecialchars($up['enr_topic'])) : '專屬一對一顧問線上會議室已建立，建議提前 10 分鐘測試設備。'; ?>
                                                    <?php else: ?>
                                                        第 <?php echo (int)$up['sess_batch']; ?> 梯次 • 目前已完成 <?php echo $don_les; ?> / <?php echo $tot_les; ?> 單元（<?php echo $pct; ?>%），點擊右側按鈕繼續學習。
                                                    <?php endif; ?>
                                                </p>
                                            </div>

                                            <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" id="remindSwitch_<?php echo $enr_id; ?>" checked>
                                                    <label class="form-check-label small text-muted" for="remindSwitch_<?php echo $enr_id; ?>">
                                                        <?php echo $is_consult ? '行前 1 小時推播提醒' : '新單元與開播自動提醒'; ?>
                                                    </label>
                                                </div>
                                                <button type="button"
                                                        class="btn <?php echo $is_urgent ? 'btn-dark text-warning fw-bold' : 'btn-outline-dark'; ?> btn-sm py-0 px-2 btn-console-schedule-detail"
                                                        data-title="<?php echo htmlspecialchars($up['course_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-is-consult="<?php echo $is_consult ? '1' : '0'; ?>"
                                                        data-time="<?php echo htmlspecialchars($dt_label, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-batch="<?php echo (int)$up['sess_batch']; ?>"
                                                        data-progress="<?php echo "{$don_les} / {$tot_les} 單元 ({$pct}%)"; ?>"
                                                        data-meeting="<?php echo htmlspecialchars($up['sess_meetingid'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-topic="<?php echo htmlspecialchars($up['enr_topic'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php echo $is_consult ? '查看詳情' : '進入教室'; ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <!-- 區塊分隔線：區隔上方排程提醒與下方互動分頁中心 -->
                    <hr class="border-secondary my-5">

                    <!-- 操作結果即時提示區 -->
                    <div id="consoleAlertBox" class="alert d-none align-items-center mb-4" role="alert">
                        <i id="consoleAlertIcon" class="bi me-2 fs-5"></i>
                        <div id="consoleAlertMsg" class="small fw-bold"></div>
                    </div>

                    <!-- 三大互動中心切換分頁 (Nav Pills) -->
                    <ul class="nav nav-pills mb-4 gap-2" id="consoleTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold px-3 px-md-4" id="messages-tab" data-bs-toggle="pill" data-bs-target="#messages-pane" type="button" role="tab">
                                <i class="bi bi-bell-fill me-1"></i>訊息與通知 <span class="badge rounded-pill bg-danger ms-1" id="tabUnreadBadge">3</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-3 px-md-4" id="wishlist-tab" data-bs-toggle="pill" data-bs-target="#wishlist-pane" type="button" role="tab">
                                <i class="bi bi-bookmark-heart-fill me-1"></i>收藏與追蹤 (<span id="wishlistCountBadge"><?php echo $wishlist_count; ?></span>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-3 px-md-4" id="support-tab" data-bs-toggle="pill" data-bs-target="#support-pane" type="button" role="tab">
                                <i class="bi bi-headset me-1"></i>客服與歷史問答
                            </button>
                        </li>
                    </ul>

                    <!-- 分頁內容主體 -->
                    <div class="tab-content" id="consoleTabContent">

                        <!-- ============================================================
                             區塊 2：訊息與通知 (系統公告、訂單通知、促銷、客服回覆)
                             ============================================================ -->
                        <div class="tab-pane fade show active" id="messages-pane" role="tabpanel" tabindex="0">
                            
                            <!-- 分類過濾與全部已讀控制列 -->
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <div class="btn-group btn-group-sm" role="group" id="msgCategoryFilter">
                                    <button type="button" class="btn btn-dark active" data-cat="all">全部訊息</button>
                                    <button type="button" class="btn btn-outline-secondary" data-cat="order">訂單通知</button>
                                    <button type="button" class="btn btn-outline-secondary" data-cat="support">客服回覆</button>
                                    <button type="button" class="btn btn-outline-secondary" data-cat="promo">專屬促銷</button>
                                    <button type="button" class="btn btn-outline-secondary" data-cat="system">系統公告</button>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm fw-bold" id="btnMarkAllRead">
                                    <i class="bi bi-check2-all me-1"></i>全部標示為已讀
                                </button>
                            </div>

                            <!-- 訊息列表容器 -->
                            <div class="list-group shadow-none border rounded-3 overflow-hidden" id="messageListGroup">
                                <?php if (empty($noti_list)): ?>
                                    <div class="p-4 text-center text-muted small bg-white">目前尚無任何系統通知或訊息。</div>
                                <?php else: ?>
                                    <?php foreach ($noti_list as $noti):
                                        $is_unread = empty($noti['nor_id']);
                                        $cat_info  = $cat_map[(int)$noti['no_category']] ?? $cat_map[1];
                                        $time_fmt  = date('Y-m-d H:i', strtotime($noti['no_created']));
                                        $item_cls  = $is_unread ? 'border-start border-4 border-warning bg-light unread' : '';
                                    ?>
                                        <a href="javascript:void(0);"
                                        class="list-group-item list-group-item-action p-3 msg-item <?php echo $item_cls; ?>"
                                        data-no-id="<?php echo (int)$noti['no_id']; ?>"
                                        data-cat="<?php echo $cat_info['code']; ?>"
                                        data-title="<?php echo htmlspecialchars($noti['no_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-time="<?php echo $time_fmt; ?>"
                                        data-content="<?php echo htmlspecialchars($noti['no_content'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div>
                                                    <?php if ($is_unread): ?>
                                                        <span class="badge bg-danger me-1 unread-dot">未讀</span>
                                                    <?php endif; ?>
                                                    <span class="badge <?php echo $cat_info['badge']; ?> me-2"><?php echo $cat_info['label']; ?></span>
                                                    <strong class="<?php echo $is_unread ? 'text-dark' : 'text-secondary'; ?>">
                                                        <?php echo htmlspecialchars($noti['no_title'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </strong>
                                                </div>
                                                <small class="text-muted"><?php echo date('m/d H:i', strtotime($noti['no_created'])); ?></small>
                                            </div>
                                            <p class="text-muted small mb-0 text-truncate">
                                                <?php echo htmlspecialchars($noti['no_content'], ENT_QUOTES, 'UTF-8'); ?>
                                            </p>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <!-- 訊息分頁導覽列 -->
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3" id="msgPaginationBar">
                                <small class="text-muted" id="msgPageInfo"></small>
                                <nav aria-label="訊息分頁">
                                    <ul class="pagination pagination-sm mb-0" id="msgPagination"></ul>
                                </nav>
                            </div>

                        </div> <!-- 結束區塊 2：messages-pane -->

                        <!-- ============================================================
                             區塊 3：收藏與追蹤清單 (商品、文章、課程管理)
                             ============================================================ -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                            <div class="d-flex flex-wrap gap-1" id="wishlistFilterGroup">
                                <button type="button" class="btn btn-sm btn-dark active" data-filter="all">全部收藏</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="course">課程與服務</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="merch">精選商品</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="article">專欄文章</button>
                            </div>
                            <small class="text-muted">顯示 <span id="wishShownCount">0</span> / <span id="wishFilteredTotal">0</span> 筆</small>
                        </div>
                        <div class="tab-pane fade" id="wishlist-pane" role="tabpanel" tabindex="0">
                            <div class="row g-3" id="wishlistGrid">
                                <?php foreach ($wishlist_items as $wi):
                                    $w_type    = (int)$wi['wish_type']; // 1=課程, 2=商品, 3=文章
                                    $target_id = (int)$wi['target_id'];
                                    $img_no    = (($target_id * 7 + $w_type) % 18) + 1;
                                ?>
                                    <div class="col-md-4 wishlist-card"
                                        data-wish-id="<?php echo (int)$wi['wish_id']; ?>"
                                        data-category="<?php echo htmlspecialchars($wi['wish_category']); ?>">
                                        <div class="card border h-100 shadow-none">
                                            <img src="imageFiles/test/<?php echo $img_no; ?>.jpg" class="card-img-top" style="height: 140px; object-fit: cover;" alt="<?php echo htmlspecialchars($wi['title']); ?>">
                                            <div class="card-body d-flex flex-column justify-content-between p-3">
                                                <div>
                                                    <span class="badge <?php echo $wi['badge_cls']; ?> mb-2"><?php echo htmlspecialchars($wi['badge_label']); ?></span>
                                                    <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($wi['title']); ?></h6>
                                                    <div class="<?php echo ($w_type === 3) ? 'text-muted' : 'text-warning fw-bold'; ?> small mb-2">
                                                        <?php echo ($w_type === 3) ? ('收藏於 ' . $wi['created_date']) : htmlspecialchars($wi['price_txt']); ?>
                                                    </div>
                                                    <?php if (!empty($wi['note'])): ?>
                                                        <p class="text-muted mb-3" style="font-size: 0.75rem;"><i class="bi bi-sticky me-1"></i><?php echo htmlspecialchars($wi['note']); ?></p>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="d-flex gap-2 mt-auto">
                                                    <?php if ($w_type === 1):
                                                        $is_c_consult = (isset($courses_detail_map[$target_id]) && (int)$courses_detail_map[$target_id]['course_type'] === 2);
                                                    ?>
                                                        <button type="button"
                                                                class="btn btn-dark text-warning btn-sm flex-grow-1 fw-bold btn-open-course-confirm"
                                                                data-course-id="<?php echo $target_id; ?>">
                                                            <?php echo $is_c_consult ? '我要預約' : '前往報名'; ?>
                                                        </button>
                                                    <?php elseif ($w_type === 2): ?>
                                                        <button type="button"
                                                                class="btn btn-dark text-warning btn-sm flex-grow-1 fw-bold btn-open-merch-confirm"
                                                                data-merch-id="<?php echo $target_id; ?>">
                                                            立即購買
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button"
                                                                class="btn btn-outline-dark btn-sm flex-grow-1 fw-bold btn-console-view-res"
                                                                data-res-id="<?php echo $target_id; ?>">
                                                            <i class="bi bi-eye me-1"></i>檢視素材
                                                        </button>
                                                    <?php endif; ?>

                                                    <button type="button"
                                                            class="btn btn-outline-danger btn-sm btn-remove-wish"
                                                            data-type="<?php echo $w_type; ?>"
                                                            data-id="<?php echo $target_id; ?>"
                                                            title="取消收藏">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div> <!-- 結束 wishlistGrid -->
                            <div class="text-center mt-4 d-none" id="btnLoadMoreWishWrap">
                                <button type="button" class="btn btn-outline-dark btn-sm px-4 fw-bold" id="btnLoadMoreWish">
                                    <i class="bi bi-chevron-down me-1"></i>顯示更多收藏項目
                                </button>
                            </div>
                            <!-- 收藏清空提示（當無任何收藏時自動顯示） -->
                            <div id="emptyWishlistBox" class="text-center py-5 bg-light rounded-3 border <?php echo empty($wishlist_items) ? '' : 'd-none'; ?>">
                                <i class="bi bi-heartbreak display-4 text-muted mb-2 d-block"></i>
                                <h6 class="fw-bold text-muted">目前尚無任何收藏項目</h6>
                                <p class="text-muted small mb-0">瀏覽全站作品、課程或專欄時，點擊愛心即可加入追蹤清單。</p>
                            </div>
                        </div>
                        <!-- 結束區塊 3：wishlist-pane -->

                        <!-- ============================================================
                             區塊 4：客服與表單 (提交問題、聯繫客服、查看歷史問答紀錄)
                             ============================================================ -->
                        <div class="tab-pane fade" id="support-pane" role="tabpanel" tabindex="0">
                            <div class="row g-4">
                                
                                <!-- 左側：線上客服工單提交表單 -->
                                <div class="col-lg-5">
                                    <div class="p-4 border rounded-3 bg-light h-100">
                                        <h6 class="fw-bold text-dark mb-1"><i class="bi bi-chat-square-text-fill me-2 text-primary"></i>提交客服諮詢表單</h6>
                                        <p class="text-muted small mb-3">專屬客服團隊將於服務時間內（週一至週五 09:00-18:00）優先回覆 VIP 會員提問。</p>
                                        
                                        <form id="formSubmitTicket">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">問題分類 <span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" id="ticketCategory" required>
                                                    <option value="課程與觀看權限">課程與觀看權限</option>
                                                    <option value="訂單、發票與帳務">訂單、發票與帳務</option>
                                                    <option value="客製化專案技術諮詢">客製化專案技術諮詢</option>
                                                    <option value="帳號安全與其他建議">帳號安全與其他建議</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">問題主旨 <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm" id="ticketSubject" placeholder="請簡述您的問題主旨" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">詳細內容說明 <span class="text-danger">*</span></label>
                                                <textarea class="form-control form-control-sm" id="ticketMessage" rows="4" placeholder="請詳細描述您遇到的狀況或需求..." required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-dark text-warning fw-bold btn-sm w-100 py-2">
                                                <i class="bi bi-send-fill me-1"></i>送出客服工單
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- 右側：歷史問答與客服回覆紀錄 -->
                                <div class="col-lg-7">
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-chat-left-dots-fill me-2 text-success"></i>歷史問答與客服回覆紀錄</h6>
                                    <div class="accordion" id="ticketHistoryAccordion">
                                        <?php if (empty($ticket_list)): ?>
                                            <div class="p-4 text-center text-muted small bg-light rounded-3 border" id="emptyTicketHint">
                                                目前尚無歷史提問紀錄，歡迎使用左側表單與專屬客服聯繫。
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($ticket_list as $idx => $tkt):
                                                $spt_id      = (int)$tkt['spt_id'];
                                                $collapse_id = 'ticketItem_' . $spt_id;
                                                $is_first    = ($idx === 0);
                                                $status      = (int)$tkt['spt_status'];

                                                if ($status === 1) {
                                                    $status_badge = '<span class="badge bg-success me-2">已回覆</span>';
                                                } elseif ($status === 9) {
                                                    $status_badge = '<span class="badge bg-secondary me-2">已結案</span>';
                                                } else {
                                                    $status_badge = '<span class="badge bg-primary me-2">處理中</span>';
                                                }
                                            ?>
                                                <div class="accordion-item border rounded-3 mb-3 overflow-hidden shadow-none">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button <?php echo $is_first ? '' : 'collapsed'; ?> fw-bold small bg-white text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapse_id; ?>">
                                                            <?php echo $status_badge; ?>
                                                            <span class="text-truncate">[<?php echo htmlspecialchars($tkt['spt_category']); ?>] <?php echo htmlspecialchars($tkt['spt_subject']); ?></span>
                                                        </button>
                                                    </h2>
                                                    <div id="<?php echo $collapse_id; ?>" class="accordion-collapse collapse <?php echo $is_first ? 'show' : ''; ?>" data-bs-parent="#ticketHistoryAccordion">
                                                        <div class="accordion-body bg-light small">
                                                            <?php
                                                            $replies = $replies_by_ticket[$spt_id] ?? [];
                                                            foreach ($replies as $rep):
                                                                $rep_time = date('Y-m-d H:i', strtotime($rep['spr_created']));
                                                                if ((int)$rep['spr_sender'] === 1): // 會員提問
                                                            ?>
                                                                    <div class="mb-2 pb-2 border-bottom">
                                                                        <div class="fw-bold text-dark mb-1">我的提問 (<?php echo $rep_time; ?>)：</div>
                                                                        <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($rep['spr_message'])); ?></p>
                                                                    </div>
                                                                <?php else: // 專屬客服回覆 ?>
                                                                    <div class="p-3 mb-2 bg-white rounded border-start border-4 border-success">
                                                                        <div class="fw-bold text-success mb-1"><i class="bi bi-person-badge me-1"></i>5SL 專屬客服回覆 (<?php echo $rep_time; ?>)：</div>
                                                                        <p class="text-dark mb-0"><?php echo nl2br(htmlspecialchars($rep['spr_message'])); ?></p>
                                                                    </div>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <!-- 歷史問答分頁導覽列 -->
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3" id="ticketPaginationBar">
                                        <small class="text-muted" id="ticketPageInfo"></small>
                                        <nav aria-label="歷史問答分頁">
                                            <ul class="pagination pagination-sm mb-0" id="ticketPagination"></ul>
                                        </nav>
                                    </div>

                                </div>

                            </div>
                        </div> <!-- 結束區塊 4：support-pane -->

                    </div> <!-- 結束 tab-content -->

                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- ==========================================================================
     彈窗：單則訊息詳情閱讀視窗
     ========================================================================== -->
<div class="modal fade" id="messageDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold text-warning" id="msgModalTitle">通知詳情</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-muted small mb-3 pb-2 border-bottom">
                    <i class="bi bi-clock me-1"></i>發送時間：<span id="msgModalTime">-</span>
                </div>
                <p class="text-dark lh-lg mb-0" id="msgModalContent">-</p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-dark text-warning btn-sm px-4 fw-bold" data-bs-dismiss="modal">確認已讀</button>
            </div>
        </div>
    </div>
</div>

<!-- 彈窗 A：課程與顧問服務確認報名/預約視窗 -->
<div class="modal fade" id="courseConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="position-relative">
                <img id="ccModalCover" src="" class="w-100" style="height: 200px; object-fit: cover;" alt="課程主圖">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 bg-dark p-2 rounded-circle" data-bs-dismiss="modal" aria-label="Close"></button>
                <span class="badge bg-warning text-dark fw-bold position-absolute bottom-0 start-0 m-3 px-3 py-1 shadow-sm" id="ccModalTypeBadge">課程方案</span>
            </div>
            <div class="modal-body p-4">
                <h5 class="fw-bold text-dark mb-2" id="ccModalTitle">-</h5>
                <p class="text-muted small lh-lg mb-3" id="ccModalBrief">-</p>

                <div class="p-3 bg-light rounded-3 border mb-3 d-flex justify-content-between align-items-center">
                    <span class="small fw-bold text-muted">課程／服務費用</span>
                    <span class="fs-4 fw-bold text-danger" id="ccModalPrice">NT$ 0</span>
                </div>

                <!-- 梯次 / 預約時段選擇區（若有開放梯次則顯示下拉選單） -->
                <div class="mb-2" id="ccModalSessionWrap">
                    <label for="ccModalSessionSelect" class="form-label small fw-bold text-dark">
                        選擇報名梯次 / 預約時段 <span class="text-danger">*</span>
                    </label>
                    <select class="form-select form-select-sm" id="ccModalSessionSelect"></select>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>放棄返回
                </button>
                <button type="button" class="btn btn-dark text-warning fw-bold btn-sm px-4" id="btnConfirmCourseCart">
                    <i class="bi bi-check2-circle me-1"></i>同意並加入購物車結帳
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 彈窗 B：排程詳情 & 限定素材即時預覽燈箱 -->
<div class="modal fade" id="consoleInstantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h6 class="modal-title fw-bold text-warning" id="ciModalTitle">內容預覽</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="ciModalBody"></div>
        </div>
    </div>
</div>

<!-- 彈窗 C：實體與數位商品確認購買視窗（含主圖、簡介、數量選擇與總計說明） -->
<div class="modal fade" id="merchConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="position-relative">
                <img id="mcModalCover" src="" class="w-100" style="height: 200px; object-fit: cover;" alt="商品主圖">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 bg-dark p-2 rounded-circle" data-bs-dismiss="modal" aria-label="Close"></button>
                <span class="badge bg-primary fw-bold position-absolute bottom-0 start-0 m-3 px-3 py-1 shadow-sm" id="mcModalTypeBadge">精選商品</span>
            </div>
            <div class="modal-body p-4">
                <h5 class="fw-bold text-dark mb-2" id="mcModalTitle">-</h5>
                <p class="text-muted small lh-lg mb-3" id="mcModalBrief">-</p>

                <!-- 單價與購買數量選擇列 -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span class="small fw-bold text-muted">商品單價</span>
                        <span class="fw-bold text-dark" id="mcModalUnitPrice">NT$ 0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span class="small fw-bold text-muted">選擇購買數量</span>
                        <div class="input-group input-group-sm" style="width: 125px;" id="mcModalQtyGroup">
                            <button class="btn btn-outline-secondary" type="button" id="btnMcQtyMinus">-</button>
                            <input type="text" class="form-control text-center fw-bold bg-white" id="mcModalQtyInput" value="1" readonly>
                            <button class="btn btn-outline-secondary" type="button" id="btnMcQtyPlus">+</button>
                        </div>
                        <span class="badge bg-secondary d-none" id="mcModalDigitalQtyBadge">數位物件固定 1 份</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-baseline pt-1">
                        <span class="small fw-bold text-dark">本次小計金額</span>
                        <span class="fs-4 fw-bold text-danger" id="mcModalTotalPrice">NT$ 0</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>放棄返回
                </button>
                <button type="button" class="btn btn-dark text-warning fw-bold btn-sm px-4" id="btnConfirmMerchCart">
                    <i class="bi bi-cart-check-fill me-1"></i>同意購買並前往結帳
                </button>
            </div>
        </div>
    </div>
</div>


<style>
    body {
        background-image: url("imageFiles/mainBG-3.jpg");
        background-attachment: fixed;   /* 核心語句：固定背景圖不隨頁面滾動 */
        background-size: cover;         /* 等比例縮放填滿整個視窗 */
        background-position: center;    /* 背景圖置中對齊 */
        background-repeat: no-repeat;   /* 防止圖片重複平鋪 */
    }
    #consoleTab .nav-link {
        color: #495057;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
    }
    #consoleTab .nav-link:hover {
        background-color: #e9ecef;
        color: #212529;
    }
    #consoleTab .nav-link.active {
        background-color: #212529 !important;
        color: #ffc107 !important;
        border-color: #212529 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
</style>

<?php include_once '5sl_include_footer.php'; ?>

<script>
$(function() {
    // 1. 同步更新全站未讀徽章數量 (頂部卡片 + 分頁標籤 + 左側 Subnavi 紅燈)
    function syncUnreadBadges() {
        const unreadTotal = $('.msg-item.unread').length;
        $('#hubUnreadCount').text(unreadTotal);
        $('#tabUnreadBadge').text(unreadTotal);

        const $sideBadges = $('#fslBadge_myConsole, #fslBadge_myConsole_mobile');
        if (unreadTotal > 0) {
            $sideBadges
                .text(unreadTotal)
                .removeClass('bg-success bg-warning text-dark')
                .addClass('bg-danger')
                .attr('style', 'display: inline-flex !important;');
        } else {
            $('#tabUnreadBadge').hide();
            $sideBadges.hide();
        }
    }

    syncUnreadBadges();

    // 2. 通用提示框控制
    function showConsoleAlert(type, msg) {
        const $box = $('#consoleAlertBox');
        const $icon = $('#consoleAlertIcon');
        $box.removeClass('d-none alert-success alert-danger').addClass('d-flex');
        $icon.removeClass('bi-check-circle-fill bi-exclamation-triangle-fill');

        if (type === 'success') {
            $box.addClass('alert-success');
            $icon.addClass('bi-check-circle-fill');
        } else {
            $box.addClass('alert-danger');
            $icon.addClass('bi-exclamation-triangle-fill');
        }
        $('#consoleAlertMsg').text(msg);
        $('html, body').animate({ scrollTop: $box.offset().top - 120 }, 300);
    }

    // =========================================================================
    // 3. 訊息分類篩選與雙區分頁引擎 (每頁顯示 5 筆，可依需求修改 pageSize)
    // =========================================================================
    const msgPageSize = 5;
    let currentMsgCat = 'all';

    // 通用 Bootstrap 5 黑金風格頁碼產生器
    function buildPaginationUI($ul, totalItems, pageSize, currentPage, onPageClick) {
        $ul.empty();
        const totalPages = Math.ceil(totalItems / pageSize);
        if (totalPages <= 1) return;

        // 上一頁
        $ul.append(`
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link text-dark" href="javascript:void(0);" data-page="${currentPage - 1}">&laquo;</a>
            </li>
        `);

        // 數字頁碼
        for (let i = 1; i <= totalPages; i++) {
            const activeCls = (i === currentPage) ? 'bg-dark text-warning border-dark fw-bold' : 'text-dark';
            $ul.append(`
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link ${activeCls}" href="javascript:void(0);" data-page="${i}">${i}</a>
                </li>
            `);
        }

        // 下一頁
        $ul.append(`
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link text-dark" href="javascript:void(0);" data-page="${currentPage + 1}">&raquo;</a>
            </li>
        `);

        $ul.find('a.page-link').on('click', function() {
            const targetPage = parseInt($(this).attr('data-page'), 10);
            if (targetPage >= 1 && targetPage <= totalPages && targetPage !== currentPage) {
                onPageClick(targetPage);
            }
        });
    }

    // A. 渲染訊息列表分頁
    function renderMsgPage(page = 1) {
        const $allItems = $('.msg-item');
        const $matched = (currentMsgCat === 'all')
            ? $allItems
            : $allItems.filter(`[data-cat="${currentMsgCat}"]`);

        $allItems.hide();
        const total = $matched.length;

        if (total === 0) {
            $('#msgPaginationBar').hide();
            return;
        }

        const start = (page - 1) * msgPageSize;
        const end = start + msgPageSize;
        $matched.slice(start, end).show();

        $('#msgPaginationBar').show();
        $('#msgPageInfo').text(`顯示第 ${start + 1} - ${Math.min(end, total)} 則，共 ${total} 則訊息`);
        buildPaginationUI($('#msgPagination'), total, msgPageSize, page, renderMsgPage);
    }

    // B. 渲染歷史問答分頁
    const ticketPageSize = 5;
    function renderTicketPage(page = 1) {
        const $tickets = $('#ticketHistoryAccordion .accordion-item');
        const total = $tickets.length;

        if (total === 0) {
            $('#ticketPaginationBar').hide();
            return;
        }

        $tickets.hide();
        const start = (page - 1) * ticketPageSize;
        const end = start + ticketPageSize;
        $tickets.slice(start, end).show();

        $('#ticketPaginationBar').show();
        $('#ticketPageInfo').text(`顯示第 ${start + 1} - ${Math.min(end, total)} 筆，共 ${total} 筆紀錄`);
        buildPaginationUI($('#ticketPagination'), total, ticketPageSize, page, renderTicketPage);
    }

    // 訊息分類按鈕點擊（切換分類時自動回到第 1 頁）
    $('#msgCategoryFilter button').on('click', function() {
        $('#msgCategoryFilter button').removeClass('btn-dark active').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-dark active');
        currentMsgCat = $(this).attr('data-cat');
        renderMsgPage(1);
    });

    // 頁面載入時初始化兩區分頁
    renderMsgPage(1);
    renderTicketPage(1);


    // 4. 點擊單則訊息閱讀並寫入資料庫已讀
    $(document).on('click', '.msg-item', function() {
        const $item = $(this);
        const noId = $item.attr('data-no-id');

        $('#msgModalTitle').text($item.attr('data-title'));
        $('#msgModalTime').text($item.attr('data-time'));
        $('#msgModalContent').text($item.attr('data-content'));

        if ($item.hasClass('unread')) {
            $item.removeClass('unread bg-light border-start border-4 border-danger border-warning');
            $item.find('.unread-dot').remove();
            syncUnreadBadges();

            // 呼叫統一會員動作中心 5sl_act_member.php
            $.post('funcFiles/5sl_act_member.php', { action: 'act_read_one', no_id: noId });
        }

        const msgModal = new bootstrap.Modal(document.getElementById('messageDetailModal'));
        msgModal.show();
    });

    // 5. 全部標示為已讀（批次寫入資料庫）
    $('#btnMarkAllRead').on('click', function() {
        if ($('.msg-item.unread').length === 0) return;

        $.post('funcFiles/5sl_act_member.php', { action: 'act_read_all' }, function(res) {
            if (res.success === 1) {
                $('.msg-item.unread').each(function() {
                    $(this).removeClass('unread bg-light border-start border-4 border-danger border-warning');
                    $(this).find('.unread-dot').remove();
                });
                syncUnreadBadges();
                showConsoleAlert('success', res.message || '所有系統與客服通知均已標示為已讀狀態！');
            }
        }, 'json');
    });

    // 6. 刪除收藏項目（串接 act_toggle_wishlist）
    $(document).on('click', '.btn-remove-wish', function() {
        const $btn       = $(this);
        const $card      = $btn.closest('.wishlist-card');
        const targetType = $btn.attr('data-type');
        const targetId   = $btn.attr('data-id');

        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_toggle_wishlist',
            target_type: targetType,
            target_id: targetId,
            op: 'remove'
        }, function(res) {
            if (res.success === 1) {
                $card.fadeOut(220, function() {
                    $(this).remove();
                    // 同步更新頁籤上的收藏數量徽章（若有 #wishlistCountBadge）
                    if ($('#wishlistCountBadge').length) {
                        $('#wishlistCountBadge').text(res.wishlist_total);
                    }
                    if ($('.wishlist-card').length === 0) {
                        $('#emptyWishlistBox').removeClass('d-none');
                    }
                });
            } else {
                alert(res.message || '移除收藏失敗，請稍後再試。');
            }
        }, 'json').fail(function() {
            alert('網路連線異常，請稍後再試。');
        });
    });

    // 7. 送出客服諮詢工單（寫入 fsl_support_tickets 與 fsl_support_replies）
    $('#formSubmitTicket').on('submit', function(e) {
        e.preventDefault();
        const cat = $('#ticketCategory').val();
        const subj = $('#ticketSubject').val().trim();
        const msg = $('#ticketMessage').val().trim();

        if (window.fslLoader) window.fslLoader.show();

        $.post('funcFiles/5sl_act_member.php', {
            action: 'act_submit_ticket',
            category: cat,
            subject: subj,
            message: msg
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();

            if (res.success === 1) {
                $('#emptyTicketHint').remove();
                const collapseId = 'ticketNew_' + Date.now();
                const safeSubj = $('<div>').text(subj).html();
                const safeMsg = $('<div>').text(msg).html().replace(/\n/g, '<br>');

                const newTicketHtml = `
                    <div class="accordion-item border border-primary rounded-3 mb-3 overflow-hidden shadow-none">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold small bg-white text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}">
                                <span class="badge bg-primary me-2">處理中</span>
                                <span class="text-truncate">[${cat}] ${safeSubj}</span>
                            </button>
                        </h2>
                        <div id="${collapseId}" class="accordion-collapse collapse show" data-bs-parent="#ticketHistoryAccordion">
                            <div class="accordion-body bg-light small">
                                <div class="mb-2 pb-2 border-bottom">
                                    <div class="fw-bold text-dark mb-1">我的提問 (${res.created} • 單號 ${res.ticket_no})：</div>
                                    <p class="text-muted mb-0">${safeMsg}</p>
                                </div>
                                <div class="text-primary small">
                                    <i class="bi bi-hourglass-split me-1"></i>客服團隊已收到您的提問單，將於服務時間內儘速為您回覆。
                                </div>
                            </div>
                        </div>
                    </div>`;

                $('#ticketHistoryAccordion').prepend(newTicketHtml);
                renderTicketPage(1);
                $('#formSubmitTicket')[0].reset();
                showConsoleAlert('success', `${res.message}（工單編號：${res.ticket_no}）`);
            } else {
                showConsoleAlert('danger', res.message || '送出失敗，請稍後再試。');
            }
        }, 'json').fail(function() {
            if (window.fslLoader) window.fslLoader.hide();
            showConsoleAlert('danger', '網路連線異常，請檢查連線後重試。');
        });
/*
        }, 'json').fail(function(xhr) {
            if (window.fslLoader) window.fslLoader.hide();
            console.error('Server Response:', xhr.status, xhr.responseText);
            showGiftsAlert('danger', '錯誤狀態：' + xhr.status + '｜詳情：' + xhr.responseText.substring(0, 150));
        });

*/
    });

    // 收藏與追蹤：分類篩選 + 每批顯示 6 筆（載入更多）
    let wishFilter   = 'all';
    let wishPageSize = 6;
    let wishLimit    = wishPageSize;

    function renderWishlistView() {
        const $allCards = $('.wishlist-card');
        const $matched  = (wishFilter === 'all')
            ? $allCards
            : $allCards.filter(`[data-category="${wishFilter}"]`);

        $allCards.hide();
        $matched.slice(0, wishLimit).fadeIn(180);

        const totalMatched = $matched.length;
        const shownCount   = Math.min(wishLimit, totalMatched);

        $('#wishShownCount').text(shownCount);
        $('#wishFilteredTotal').text(totalMatched);

        if (totalMatched === 0) {
            $('#emptyWishlistBox').removeClass('d-none');
            $('#btnLoadMoreWishWrap').addClass('d-none');
        } else {
            $('#emptyWishlistBox').addClass('d-none');
            if (shownCount < totalMatched) {
                $('#btnLoadMoreWishWrap').removeClass('d-none');
            } else {
                $('#btnLoadMoreWishWrap').addClass('d-none');
            }
        }
    }

    $('#wishlistFilterGroup button').on('click', function() {
        $('#wishlistFilterGroup button').removeClass('btn-dark active').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-dark active');
        wishFilter = $(this).attr('data-filter');
        wishLimit  = wishPageSize; // 切換分類時重置回前 6 筆
        renderWishlistView();
    });

    $('#btnLoadMoreWish').on('click', function() {
        wishLimit += wishPageSize;
        renderWishlistView();
    });

    // 頁面載入後初始化收藏清單顯示
    renderWishlistView();

    // 預載課程與梯次資料映射表
    const consoleCoursesMap = <?php echo json_encode((object)$courses_detail_map, JSON_UNESCAPED_UNICODE); ?>;
    let selectedConfirmCourseId = 0;

    // 1. 「近期課程與服務排程提醒」 -> 點擊「查看詳情 / 進入教室」即時彈窗
    $(document).on('click', '.btn-console-schedule-detail', function() {
        const title     = $(this).attr('data-title');
        const isConsult = $(this).attr('data-is-consult') === '1';
        const timeStr   = $(this).attr('data-time');
        const batch     = $(this).attr('data-batch');
        const progress  = $(this).attr('data-progress');
        const meeting   = ($(this).attr('data-meeting') || '').trim();
        const topic     = ($(this).attr('data-topic') || '').trim();
        const isUrl     = /^https?:\/\//i.test(meeting);

        $('#ciModalTitle').text(title);
        const detailHtml = `
            <div class="p-3 rounded-3 bg-black border border-secondary small">
                <div class="d-flex justify-content-between py-2 border-bottom border-secondary">
                    <span class="text-white-50">類型梯次</span>
                    <strong class="text-warning">${isConsult ? '一對一專屬顧問輔導' : `線上/實體課程（第 ${batch} 梯）`}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom border-secondary">
                    <span class="text-white-50">排程時間</span>
                    <strong class="text-white">${timeStr}</strong>
                </div>
                ${!isConsult ? `
                <div class="d-flex justify-content-between py-2 border-bottom border-secondary">
                    <span class="text-white-50">目前學習進度</span>
                    <strong class="text-info">${progress}</strong>
                </div>` : ''}
                ${topic ? `
                <div class="py-2 border-bottom border-secondary">
                    <div class="text-white-50 mb-1">預約諮詢主題與備註：</div>
                    <div class="text-white">${$('<div>').text(topic).html()}</div>
                </div>` : ''}
                <div class="pt-3 text-center">
                    ${meeting
                        ? (isUrl
                            ? `<a href="${meeting}" target="_blank" class="btn btn-warning text-dark fw-bold px-4 py-2"><i class="bi bi-camera-video-fill me-1"></i>立即進入線上會議室 / 教室</a>`
                            : `<div class="text-warning">專屬會議室代碼：<code>${$('<div>').text(meeting).html()}</code></div>`)
                        : `<div class="text-white-50">專屬教室與課程內容已準備就緒，祝您學習愉快！</div>`
                    }
                </div>
            </div>`;

        $('#ciModalBody').html(detailHtml);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('consoleInstantModal')).show();
    });

    // 2. 「收藏與追蹤」 -> 點擊「檢視素材」即時呼叫 act_access_resource 彈窗預覽
    $(document).on('click', '.btn-console-view-res', function() {
        const resId = $(this).attr('data-res-id');
        if (window.fslLoader) window.fslLoader.show();

        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_access_resource',
            res_id: resId,
            use_mode: 'preview'
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();

            if (res.success !== 1) {
                showConsoleAlert('danger', res.message || '您尚未取得此素材存取權限，請至「課程與服務 -> 限定內容專區」兌換解鎖。');
                return;
            }

            $('#ciModalTitle').text(res.res_title);
            const rType = parseInt(res.res_type, 10);
            const mUrl  = res.res_media_url || '';
            let bodyHtml = '';

            if (rType === 4) {
                //let ytId = '';
                let bodyHtml = '';
                const m = mUrl.match(/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
                if (m && m[1]) {
                    //ytId = m[1];
                    var embedPath = "em" + "bed";
                    bodyHtml = "https://youtube.com" + "/" + embedPath + "/" + m[1];
                }
                bodyHtml = bodyHtml
                    ? `<div class="ratio ratio-16x9 bg-black rounded overflow-hidden">`
                        + `<iframe src="`+ bodyHtml +`" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; web-share; compute-pressure" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen>`
                        + `</iframe></div>`
                    : `<div class="p-4 text-warning text-center">無法解析 YouTube 影片網址</div>`;
            } else if (rType === 5) {
                bodyHtml = `
                    <div class="p-4 bg-black rounded border border-secondary text-center">
                        <i class="bi bi-soundwave display-3 text-info mb-3 d-block"></i>
                        <h6 class="text-white mb-3">${$('<div>').text(res.res_title).html()}</h6>
                        <audio controls autoplay class="w-100"><source src="${mUrl}"></audio>
                    </div>`;
            } else if (rType === 2) {
                bodyHtml = `<div class="text-center"><img src="${mUrl}" class="img-fluid rounded shadow" style="max-height: 460px;" alt="素材圖檔"></div>`;
            } else if (rType === 3) {
                const isPdf = (res.res_file_ext || '').toUpperCase() === 'PDF' || mUrl.toLowerCase().endsWith('.pdf');
                bodyHtml = isPdf
                    ? `<div class="ratio ratio-16x9 bg-white rounded overflow-hidden"><iframe src="${mUrl}"></iframe></div>`
                    : `<div class="p-4 bg-black rounded border border-secondary text-center">
                           <i class="bi bi-file-earmark-slides display-3 text-warning mb-2 d-block"></i>
                           <p class="text-white mb-3">文書簡報檔案 (${res.res_file_ext}) 已驗證通過</p>
                           ${res.can_download === 1 ? `<a href="${mUrl}" target="_blank" class="btn btn-warning text-dark fw-bold px-4"><i class="bi bi-download me-1"></i>立即下載檔案</a>` : ''}
                       </div>`;
            } else {
                bodyHtml = `<div class="text-start p-4 bg-white text-dark rounded lh-lg" style="max-height: 440px; overflow-y: auto;">${res.res_body}</div>`;
            }

            if (res.can_download === 1 && mUrl && rType !== 3) {
                bodyHtml += `<div class="text-end mt-3"><a href="${mUrl}" target="_blank" class="btn btn-warning text-dark btn-sm fw-bold px-3"><i class="bi bi-download me-1"></i>下載原始檔案</a></div>`;
            }

            $('#ciModalBody').html(bodyHtml);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('consoleInstantModal')).show();
        }, 'json');
    });

    // 確保會員關閉預覽彈窗時影片聲音也會立刻停止
    $('#consoleInstantModal, #exclusiveMediaModal').on('hidden.bs.modal', function() {
        $(this).find('.modal-body').empty();
    });


    // 3. 「前往報名 / 我要預約」 -> 開啟帶主圖、簡介、價格與梯次選擇的確認視窗
    $(document).on('click', '.btn-open-course-confirm', function() {
        const cid  = parseInt($(this).attr('data-course-id'), 10) || 0;
        const info = consoleCoursesMap[cid];
        if (!info) return;

        selectedConfirmCourseId = cid;
        const isConsult = (info.course_type === 2);

        $('#ccModalCover').attr('src', info.cover_url);
        $('#ccModalTypeBadge').text(isConsult ? '一對一顧問預約' : '陪跑與線上課程');
        $('#ccModalTitle').text(info.course_title);
        $('#ccModalBrief').text(info.course_brief);
        $('#ccModalPrice').text('NT$ ' + info.course_cost.toLocaleString());
        $('#btnConfirmCourseCart').html(`<i class="bi bi-check2-circle me-1"></i>${isConsult ? '同意預約並前往結帳' : '同意報名並前往結帳'}`);

        const $sel = $('#ccModalSessionSelect');
        $sel.empty();
        if (Array.isArray(info.sessions) && info.sessions.length > 0) {
            $('#ccModalSessionWrap').removeClass('d-none');
            info.sessions.forEach(function(s) {
                $sel.append(`<option value="${s.sess_id}">${s.label}</option>`);
            });
        } else {
            $('#ccModalSessionWrap').addClass('d-none');
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('courseConfirmModal')).show();
    });

    // 點擊確認視窗的「同意並加入購物車結帳」
    $('#btnConfirmCourseCart').on('click', function() {
        if (!selectedConfirmCourseId) return;
        const sessId = $('#ccModalSessionSelect').val() || 0;

        bootstrap.Modal.getInstance(document.getElementById('courseConfirmModal')).hide();
        if (window.fslLoader) window.fslLoader.show();

        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_cart_add',
            item_type: 1,
            course_id: selectedConfirmCourseId,
            sess_id: sessId,
            qty: 1
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();
            if (res.success === 1) {
                window.location.href = '5sl_myCart.php';
            } else {
                showConsoleAlert('danger', res.message || '加入購物車失敗，請稍後再試。');
            }
        }, 'json');
    });

    // 4. 商品類「立即購買」 -> 開啟帶主圖、簡介、單價與數量選擇的確認視窗
    const consoleMerchMap = <?php echo json_encode((object)$merch_detail_map, JSON_UNESCAPED_UNICODE); ?>;
    let selectedConfirmMerchId = 0;
    let selectedMerchUnitPrice = 0;
    let selectedMerchMaxStock  = 99;

    function updateMerchModalTotal() {
        const qty   = parseInt($('#mcModalQtyInput').val(), 10) || 1;
        const total = selectedMerchUnitPrice * qty;
        $('#mcModalTotalPrice').text('NT$ ' + total.toLocaleString());
    }

    $(document).on('click', '.btn-open-merch-confirm', function() {
        const mid  = parseInt($(this).attr('data-merch-id'), 10) || 0;
        const info = consoleMerchMap[mid];
        if (!info) return;

        selectedConfirmMerchId = mid;
        selectedMerchUnitPrice = info.unit_price;
        selectedMerchMaxStock  = (info.merch_stock > 0) ? info.merch_stock : 99;

        const isDigital = (info.merch_type === 2);

        $('#mcModalCover').attr('src', info.cover_url);
        $('#mcModalTypeBadge')
            .removeClass('bg-primary bg-success')
            .addClass(isDigital ? 'bg-success' : 'bg-primary')
            .text(isDigital ? '數位影音 / 音檔物件' : '實體美學商品');
        $('#mcModalTitle').text(info.merch_title);
        $('#mcModalBrief').text(info.merch_brief);
        $('#mcModalUnitPrice').text('NT$ ' + info.unit_price.toLocaleString());
        $('#mcModalQtyInput').val(1);

        if (isDigital) {
            $('#mcModalQtyGroup').addClass('d-none');
            $('#mcModalDigitalQtyBadge').removeClass('d-none');
        } else {
            $('#mcModalQtyGroup').removeClass('d-none');
            $('#mcModalDigitalQtyBadge').addClass('d-none');
        }

        updateMerchModalTotal();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('merchConfirmModal')).show();
    });

    $('#btnMcQtyMinus').on('click', function() {
        let qty = parseInt($('#mcModalQtyInput').val(), 10) || 1;
        if (qty > 1) {
            $('#mcModalQtyInput').val(qty - 1);
            updateMerchModalTotal();
        }
    });

    $('#btnMcQtyPlus').on('click', function() {
        let qty = parseInt($('#mcModalQtyInput').val(), 10) || 1;
        if (qty < selectedMerchMaxStock) {
            $('#mcModalQtyInput').val(qty + 1);
            updateMerchModalTotal();
        }
    });

    // 點擊確認視窗的「同意購買並前往結帳」
    $('#btnConfirmMerchCart').on('click', function() {
        if (!selectedConfirmMerchId) return;
        const qty = parseInt($('#mcModalQtyInput').val(), 10) || 1;

        bootstrap.Modal.getInstance(document.getElementById('merchConfirmModal')).hide();
        if (window.fslLoader) window.fslLoader.show();

        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_cart_add',
            item_type: 2,
            merch_id: selectedConfirmMerchId,
            qty: qty
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();
            if (res.success === 1) {
                window.location.href = '5sl_myCart.php';
            } else {
                showConsoleAlert('danger', res.message || '加入購物車失敗，請稍後再試。');
            }
        }, 'json');
    });






});
</script>
