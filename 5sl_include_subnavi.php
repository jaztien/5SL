<?php
// 取得當前會員 Session 資訊（提供預設值防呆）
$member_name  = $_SESSION['mbTitle'] ?? '新會員';
$member_email = $_SESSION['mbEmail'] ?? '';
$member_pts   = $_SESSION['mbPoints'] ?? 0;
$member_level  = $_SESSION['mbLevel'] ?? 1;
$member_grade  = $_SESSION['mbGrade'] ?? 1;
$mb_id = (int)($_SESSION['mbID'] ?? $_SESSION['user_id'] ?? 0);

// 定義會員中心六大核心導航項目
$subnavi_items = [
    [
        'key'      => 'myConsole',
        'url'      => '5sl_myConsole.php',
        'icon'     => 'bi-grid-1x2-fill',
        'label'    => '訊息中心',
        'sub'      => '通知與客服主控台',
        'badge_id' => 'fslBadge_myConsole'
    ],
    [
        'key'      => 'myAccount',
        'url'      => '5sl_myAccount.php',
        'icon'     => 'bi-person-badge-fill',
        'label'    => '帳號身份',
        'sub'      => '個人資料與密碼安全',
        'badge_id' => 'fslBadge_myAccount'
    ],
    [
        'key'      => 'myProducts',
        'url'      => '5sl_myProducts.php',
        'icon'     => 'bi-journal-bookmark-fill',
        'label'    => '課程與服務',
        'sub'      => '限定內容與訂閱管理',
        'badge_id' => 'fslBadge_myProducts'
    ],
    [
        'key'      => 'myGifts',
        'url'      => '5sl_myGifts.php',
        'icon'     => 'bi-gift-fill',
        'label'    => '禮物與點券',
        'sub'      => 'VIP 等級與紅利錢包',
        'badge_id' => 'fslBadge_myGifts'
    ],
    [
        'key'      => 'myOrders',
        'url'      => '5sl_myOrders.php',
        'icon'     => 'bi-receipt-cutoff',
        'label'    => '我的訂購',
        'sub'      => '歷史訂單與售後申請',
        //'badge_id' => 'fslBadge_myOrders'
        'badge_id' => 'fslBadge_myOrders'
    ],
    [
        'key'      => 'myQuests',
        'url'      => '5sl_myQuests.php',
        'icon'     => 'bi-trophy-fill',
        'label'    => '我的任務',
        'sub'      => '等級挑戰與通關大禮',
        'badge_id' => 'fslBadge_myQuests'
    ]
];
?>

<!-- ==========================================================================
     💻 電腦版：整片靠左黑金高奢側邊欄 (緊貼視窗最左側，正對上方 Header LOGO)
     ========================================================================== -->
<aside class="fsl-member-sidebar-panel d-none d-lg-flex flex-column text-white shadow-lg" style="min-height: 788px;">
    <!-- 會員個人資料與 3D 發光 PTS 點數卡片 -->
    <div class="p-4 border-bottom border-secondary border-opacity-25">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="position-relative">
                <img src="<?php echo _fn_getMemberAvatar($mb_id, true); ?>" 
                     class="rounded-circle border border-2 border-warning shadow" 
                     style="width: 56px; height: 56px; object-fit: cover;" alt="會員大頭貼">
                <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-dark rounded-circle" style="width: 13px; height: 13px;" title="線上狀態"></span>
            </div>
            <div class="overflow-hidden">
                <div class="fw-bold text-white text-truncate"><?php echo $member_name; ?></div>
                <span class="badge<?php echo MEMBER_GRADE['color'] ?? ''; ?> fw-bold mt-1" style="font-size: 0.68rem;">
                    <i class="bi bi-crown-fill me-1"></i><?php echo MEMBER_GRADE['title'] ?? '&nbsp;'; ?>
                </span>
            </div>
        </div>

        <!-- 金黃色霓虹發光 PTS 點數面板 -->
        <div class="fsl-pts-glow-card p-3 rounded-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-white-50" style="font-size: 0.72rem; letter-spacing: 1px;">AVAILABLE POINTS</span>
                <i class="bi bi-coin text-warning"></i>
            </div>
            <div class="d-flex align-items-baseline justify-content-between">
                <div class="fs-4 fw-bold text-warning" style="font-family: 'Montserrat', sans-serif;">
                    <?php echo number_format($member_pts ?? 0); ?> <span class="fs-6 fw-normal text-white-50">PTS</span>
                </div>
                <a href="5sl_myGifts.php" class="text-warning text-decoration-none fw-bold" style="font-size: 0.75rem;">
                    兌換 <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- 六大功能導航清單 + 最後一列安全登出 -->
    <div class="py-3">
        <div class="px-4 mb-2 text-uppercase text-white-50 fw-bold" style="font-size: 0.7rem; letter-spacing: 1.5px;">
            Member Console
        </div>
        <nav class="nav flex-column fsl-left-nav-list">
            <?php foreach ($subnavi_items as $item): 
                $is_active = ($current_page === $item['key']);
            ?>
            <a href="<?php echo $item['url']; ?>" class="fsl-left-nav-link d-flex align-items-center justify-content-between px-4 py-3 text-decoration-none <?php echo $is_active ? 'active' : ''; ?>">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi <?php echo $item['icon']; ?> fs-5 nav-icon"></i>
                    <div>
                        <div class="fw-bold small nav-title"><?php echo $item['label']; ?></div>
                        <div class="text-white-50" style="font-size: 0.72rem;"><?php echo $item['sub']; ?></div>
                    </div>
                </div>
                <span class="badge rounded-pill bg-danger" id="<?php echo $item['badge_id']; ?>" style="display: none;">0</span>
            </a>
            <?php endforeach; ?>

            <!-- 分隔線與最後一列：安全登出 -->
            <hr class="border-secondary border-opacity-25 my-2 mx-4">
            <a href="javascript:void(0);" class="fsl-left-nav-link d-flex align-items-center justify-content-between px-4 py-3 text-decoration-none btn-ajax-logout">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-power fs-5 text-warning"></i>
                    <div>
                        <div class="fw-bold small text-white">安全登出</div>
                        <div class="text-white-50" style="font-size: 0.72rem;">結束本次安全連線</div>
                    </div>
                </div>
            </a>
        </nav>
    </div>
</aside>

<!-- ==========================================================================
     📱 手機與平板版：頂部橫向快捷膠囊導航列 (max-width: 991.98px)
     ========================================================================== -->
<div class="d-lg-none mb-3">
    <div class="p-3 rounded-3 bg-dark text-white mb-3 d-flex align-items-center justify-content-between shadow-sm">
        <div class="d-flex align-items-center gap-2">
            <img src="<?php echo _fn_getMemberAvatar($mb_id, true); ?>" class="rounded-circle border border-warning" style="width: 42px; height: 42px; object-fit: cover;" alt="頭像">
            <div>
                <div class="fw-bold small"><?php echo $member_name; ?></div>
                <span class="badge<?php echo MEMBER_GRADE['color'] ?? ''; ?>" style="font-size: 0.65rem;"><?php echo MEMBER_GRADE['title'] ?? '&nbsp;'; ?></span>
            </div>
        </div>
        <div class="text-end">
            <div class="text-white-50" style="font-size: 0.68rem;">紅利點數</div>
            <div class="fw-bold text-warning small"><?php echo number_format($member_pts ?? 0); ?> PTS</div>
        </div>
    </div>

    <!-- 可橫向滑動的子分頁膠囊列（最後一顆為安全登出） -->
    <div class="d-flex gap-2 overflow-auto pb-2" style="scrollbar-width: none;">
        <?php foreach ($subnavi_items as $item): 
            $is_active   = ($current_page === $item['key']);
            $badge_val   = 0;
            $badge_style = 'display: none; font-size: 0.6rem;';

            // 若此項目綁定購物車徽章，且資料庫購物車件數 > 0，直接顯示紅色數字圓標
            if (($item['badge_id'] ?? '') === 'subnaviCartBadge' && !empty($global_cart_count) && $global_cart_count > 0) {
                $badge_val   = (int)$global_cart_count;
                $badge_style = 'font-size: 0.6rem;';
            }
        ?>
        <a href="<?php echo $item['url']; ?>" class="btn btn-sm text-nowrap fw-bold position-relative <?php echo $is_active ? 'btn-dark text-warning border-warning' : 'btn-outline-secondary bg-white'; ?>">
            <i class="bi <?php echo $item['icon']; ?> me-1"></i><?php echo $item['label']; ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                  id="<?php echo $item['badge_id']; ?>_mobile"
                  style="<?php echo $badge_style; ?>">
                <?php echo $badge_val; ?>
            </span>
        </a>
        <?php endforeach; ?>
        <a href="javascript:void(0);" class="btn btn-sm btn-outline-danger bg-white text-nowrap fw-bold btn-ajax-logout">
            <i class="bi bi-power me-1"></i>安全登出
        </a>
    </div>
</div>
