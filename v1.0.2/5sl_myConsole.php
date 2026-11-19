<?php
// 2. 宣告當前頁面身分為 myConsole（訊息中心 / 主控台）
$current_page = 'myConsole';
// 引入共用頁首
include_once '5sl_include_header.php';

$mb_id = (int)($_SESSION['mbID'] ?? $_SESSION['user_id'] ?? 0);
$mb_lvl     = isset($_SESSION['mbLevel']) ? (int)$_SESSION['mbLevel'] : 3;
$avatar_idx = $mb_id % 223;

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

// 3. 查詢會員「收藏與追蹤」清單 (fsl_wishlists 關聯 courses / merchandise / articles)
$sql_wishlist = "
    SELECT w.wish_id, w.wish_created, w.wish_target_type, w.wish_target_id, w.wish_note,
           c.course_title, c.course_brief, c.course_cost, c.course_type, c.course_status,
           m.merch_title, m.merch_brief, m.merch_price_twd, m.merch_price_usd, m.merch_sale_usd, m.merch_type, m.merch_status,
           a.arti_title, a.arti_brief, a.arti_published, a.arti_status
    FROM fsl_wishlists w
    LEFT JOIN courses c ON w.wish_target_type = 1 AND w.wish_target_id = c.course_id
    LEFT JOIN merchandise m ON w.wish_target_type = 2 AND w.wish_target_id = m.merch_id
    LEFT JOIN articles a ON w.wish_target_type = 3 AND w.wish_target_id = a.arti_id
    WHERE w.mb_id = ?
    ORDER BY w.wish_created DESC
";
$raw_wishlist = ($mb_id > 0) ? $DB->query($sql_wishlist, [$mb_id]) : [];
if (!is_array($raw_wishlist)) $raw_wishlist = [];

$wishlist_items = [];
foreach ($raw_wishlist as $wish_row) {
    $wish_type = (int)$wish_row['wish_target_type'];

    if ($wish_type === 1 && !empty($wish_row['course_title']) && (int)$wish_row['course_status'] === 1) {
        $is_consult = ((int)$wish_row['course_type'] === 2);
        $wishlist_items[] = [
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
            'created_date'  => date('Y/m/d', strtotime($wish_row['wish_created'])),
            'can_add_cart'  => true
        ];
    } elseif ($wish_type === 2 && !empty($wish_row['merch_title']) && (int)$wish_row['merch_status'] === 1) {
        $twd_price = (float)$wish_row['merch_price_twd'];
        if ($twd_price <= 0) {
            $usd_price = ($wish_row['merch_sale_usd'] !== null) ? (float)$wish_row['merch_sale_usd'] : (float)$wish_row['merch_price_usd'];
            $twd_price = round($usd_price * 32);
        }
        $wishlist_items[] = [
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
            'created_date'  => date('Y/m/d', strtotime($wish_row['wish_created'])),
            'can_add_cart'  => true
        ];
    } elseif ($wish_type === 3 && !empty($wish_row['arti_title']) && (int)$wish_row['arti_status'] === 1) {
        $wishlist_items[] = [
            'wish_id'       => (int)$wish_row['wish_id'],
            'wish_type'     => 3,
            'wish_category' => 'article',
            'target_id'     => (int)$wish_row['wish_target_id'],
            'badge_label'   => '深度專欄文章',
            'badge_cls'     => 'bg-success',
            'title'         => $wish_row['arti_title'],
            'brief'         => strip_tags($wish_row['arti_brief'] ?? ''),
            'note'          => $wish_row['wish_note'] ?? '',
            'price_txt'     => '發布於 ' . date('Y/m/d', strtotime($wish_row['arti_published'])),
            'created_date'  => date('Y/m/d', strtotime($wish_row['wish_created'])),
            'can_add_cart'  => false
        ];
    } else {
        // 備援顯示：若關聯主表尚無該筆測試資料或已下架，仍顯示收藏紀錄供會員檢視或移除
        $type_names = [1 => '陪跑與線上課程', 2 => '精選美學商品', 3 => '深度專欄文章'];
        $wishlist_items[] = [
            'wish_id'       => (int)$wish_row['wish_id'],
            'wish_type'     => $wish_type,
            'wish_category' => 'other',
            'target_id'     => (int)$wish_row['wish_target_id'],
            'badge_label'   => $type_names[$wish_type] ?? '收藏項目',
            'badge_cls'     => 'bg-secondary',
            'title'         => ($wish_row['wish_note'] ?: "追蹤項目 #{$wish_row['wish_target_id']}"),
            'brief'         => '',
            'note'          => $wish_row['wish_note'] ?? '',
            'price_txt'     => '收藏於 ' . date('Y/m/d', strtotime($wish_row['wish_created'])),
            'created_date'  => date('Y/m/d', strtotime($wish_row['wish_created'])),
            'can_add_cart'  => false
        ];
    }
}
$wishlist_count = count($wishlist_items);


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
                         區塊 1：主要商品（課程）排程顯示以及提醒通知
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
                            <!-- 近期排程卡片 1 (高優先級即將到期提醒) -->
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 border border-warning bg-light h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-danger badge-alarm-pulse"><i class="bi bi-bell-fill me-1"></i>即將到來 • 倒數 6 天</span>
                                            <small class="fw-bold text-dark">2026/11/18 (三) 14:00</small>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">一對一網站架構健檢與效能優化諮詢</h6>
                                        <p class="text-muted small mb-2">資深架構師線上會議室已建立，建議提前 10 分鐘測試麥克風與螢幕分享。</p>
                                    </div>
                                    <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="remindSwitch1" checked>
                                            <label class="form-check-label small text-muted" for="remindSwitch1">行前 1 小時推播提醒</label>
                                        </div>
                                        <a href="5sl_myProducts.php" class="btn btn-dark text-warning btn-sm py-0 px-2 fw-bold">查看詳情</a>
                                    </div>
                                </div>
                            </div>

                            <!-- 近期排程卡片 2 (課程進度與到期提醒) -->
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 border bg-white h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-primary-subtle text-primary"><i class="bi bi-play-circle-fill me-1"></i>直播加開單元</span>
                                            <small class="fw-bold text-muted">2026/11/25 (三) 20:00</small>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">全端商業架構實戰：雙十一高併發復盤 Q&A</h6>
                                        <p class="text-muted small mb-2">課程目前進度 75%，歡迎於直播前至課程專區提交您的架構疑問。</p>
                                    </div>
                                    <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="remindSwitch2" checked>
                                            <label class="form-check-label small text-muted" for="remindSwitch2">開播前自動提醒</label>
                                        </div>
                                        <a href="5sl_myProducts.php" class="btn btn-outline-dark btn-sm py-0 px-2">進入教室</a>
                                    </div>
                                </div>
                            </div>
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
                                        $time_fmt  = date('Y/m/d H:i', strtotime($noti['no_created']));
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
                                                    <?php if ($w_type === 1): ?>
                                                        <button type="button"
                                                                class="btn btn-dark text-warning btn-sm flex-grow-1 fw-bold btn-fsl-add-cart"
                                                                data-type="1"
                                                                data-course-id="<?php echo $target_id; ?>"
                                                                data-redirect="1">
                                                            前往報名
                                                        </button>
                                                    <?php elseif ($w_type === 2): ?>
                                                        <button type="button"
                                                                class="btn btn-dark text-warning btn-sm flex-grow-1 fw-bold btn-fsl-add-cart"
                                                                data-type="2"
                                                                data-merch-id="<?php echo $target_id; ?>"
                                                                data-qty="1"
                                                                data-redirect="1">
                                                            立即購買
                                                        </button>
                                                    <?php else: ?>
                                                        <a href="5sl_myProducts.php#exclusive-pane" class="btn btn-outline-dark btn-sm flex-grow-1 fw-bold">閱讀文章</a>
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
                                                                $rep_time = date('Y/m/d H:i', strtotime($rep['spr_created']));
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








});
</script>
