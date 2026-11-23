<?php
// 2. 宣告當前頁面身分為 myGifts（禮物與點券）
$current_page = 'myGifts';

// 引入共用頁首
include_once '5sl_include_header.php';

$mb_id = (int)($_SESSION['mbID'] ?? $_SESSION['user_id'] ?? 0);
$mb_lvl = isset($_SESSION['mbLevel']) ? (int)$_SESSION['mbLevel'] : 3;

// 1. 查詢會員目前真實 PTS 點數餘額（若無資料庫則預設 0
$db_pts = ($mb_id > 0) ? DB->single("SELECT mb_points FROM fsl_members WHERE mb_id = ?", [$mb_id]) : false;
$member_pts = ($db_pts !== false && $db_pts !== null) ? (int)$db_pts : 0;

// 在頂部 PHP 查詢區補上真實累計消費金額
$total_spent = ($mb_id > 0) ? (int)DB->single("SELECT COALESCE(SUM(ord_total), 0) FROM fsl_orders WHERE mb_id = ? AND ord_status IN (2, 3, 4)", [$mb_id]) : 0;
$next_target = 50000;
$spent_pct   = min(100, (int)round(($total_spent / $next_target) * 100));
$remain_amt  = max(0, $next_target - $total_spent);

// 2. 查詢該會員的所有禮品領收紀錄 (關聯 fsl_gifts 主表)
$sql_gifts = "
    SELECT 
        r.grec_id, r.grec_created, r.grec_source_type, r.grec_status, r.grec_claimed_at, r.grec_expired_at, r.grec_shipping_note,
        g.gift_id, g.gift_code, g.gift_type, g.gift_title, g.gift_subtitle, g.gift_pts, g.gift_discount_usd, g.gift_merch_ids
    FROM fsl_gift_records r
    INNER JOIN fsl_gifts g ON r.gift_id = g.gift_id
    WHERE r.mb_id = ?
    ORDER BY (r.grec_status = 0) DESC, r.grec_created DESC
";
$gift_records = ($mb_id > 0) ? DB->query($sql_gifts, [$mb_id]) : [];
if (!is_array($gift_records)) $gift_records = [];

// 3. 預先批次撈出這些禮品關聯的數位下載檔案 (fsl_merchandise)，避免迴圈內重複查詢
$merch_map = [];
$all_merch_ids = [];
foreach ($gift_records as $gr) {
    if (!empty($gr['gift_merch_ids'])) {
        foreach (explode(',', $gr['gift_merch_ids']) as $mid) {
            $mid_int = (int)trim($mid);
            if ($mid_int > 0) $all_merch_ids[$mid_int] = $mid_int;
        }
    }
}
if (!empty($all_merch_ids)) {
    $placeholders = implode(',', array_fill(0, count($all_merch_ids), '?'));
    $merch_rows = DB->query(
        "SELECT merch_id, merch_type, merch_title, merch_download_url, merch_file_format FROM fsl_merchandise WHERE merch_id IN ($placeholders)",
        array_values($all_merch_ids)
    );
    if (is_array($merch_rows)) {
        foreach ($merch_rows as $mr) {
            $merch_map[(int)$mr['merch_id']] = $mr;
        }
    }
}

// 計算待領取禮品數量（用於分頁徽章顯示）
$unclaimed_count = 0;
foreach ($gift_records as $gr) {
    if ((int)$gr['grec_status'] === 0) $unclaimed_count++;
}

// 取得來源文字對照
$source_labels = [
    1 => '優惠卡片',
    2 => '幸運輪盤',
    3 => '完課小禮物',
    4 => '系統派發',
    5 => '任務獎勵',
];

// 4. 查詢會員 PTS 紅利點數異動明細 (mb_points)
$pts_logs = ($mb_id > 0)
    ? DB->query("SELECT * FROM fsl_points WHERE mb_id = ? AND pt_status = 1 ORDER BY pt_date DESC, pt_id DESC LIMIT 30", [$mb_id])
    : [];
if (!is_array($pts_logs)) $pts_logs = [];
// 5. 查詢「VIP 等級專屬優惠活動」(gifts.gift_target_type = 1)
$vip_gifts = DB->query(
    "SELECT * FROM fsl_gifts WHERE gift_target_type = 1 AND gift_status = 1 ORDER BY gift_target_minlvl ASC, gift_id ASC"
);
if (!is_array($vip_gifts)) $vip_gifts = [];

// 查詢該會員「本月已領取」過的 VIP 禮遇 gift_id 清單
$claimed_vip_ids = [];
if ($mb_id > 0) {
    $claimed_rows = DB->query(
        "SELECT DISTINCT gift_id FROM fsl_gift_records WHERE mb_id = ? AND grec_source_type = 5 AND DATE_FORMAT(grec_created, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')",
        [$mb_id]
    );
    if (is_array($claimed_rows)) {
        foreach ($claimed_rows as $cr) {
            $claimed_vip_ids[(int)$cr['gift_id']] = true;
        }
    }
}

// 等級名稱對照表（對齊您設定的 1=一般, 2=進階, 3=黃金高階VIP, 4=黑鑽尊享VIP）
$lvl_names = [
    1 => '一般會員',
    2 => '進階會員',
    3 => '黃金高階 VIP',
    4 => '黑鑽尊享 VIP',
];
?>

<div class="container my-5 py-4">
    <div class="row g-4 mt-2">
        
        <!-- 左側：會員中心共用子導航列 -->
        <div class="col-md-4 col-lg-3">
            <?php include_once '5sl_include_subnavi.php'; ?>
        </div>

        <!-- 右側：禮物與點券核心工作視窗 -->
        <div class="col-md-8 col-lg-9">
            <div class="card border-0 shadow-sm p-4 p-md-5 bg-white bg-opacity-75 rounded-3 h-100" style="min-height: 788px;">
                <div class="card-body p-0">
                    
                    <!-- 頁面主標題 -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 mb-4">
                        <div>
                            <h3 class="fw-bold text-dark mb-1">
                                <i class="bi bi-gift-fill me-2 text-warning"></i>禮物與點券 (Gifts & Vouchers)
                            </h3>
                            <p class="text-muted small mb-0">檢視您的 VIP 等級升級進度、管理紅利 PTS 點數與折價券，並領取專屬禮遇。</p>
                        </div>
                        <div class="mt-3 mt-md-0">
                            <button type="button" class="btn btn-dark text-warning btn-sm fw-bold px-3 shadow-sm" onclick="$('#fslGlobalWheelPanel').fadeIn(300);">
                                <i class="bi bi-disc-fill me-1"></i>開啟幸運大轉盤
                            </button>
                        </div>
                    </div>

                    <!-- ============================================================
                         區塊 1：會員等級與進度 (身分、升級條件、累積消費金額)
                         ============================================================ -->
                    <div class="p-4 rounded-3 text-white mb-4 shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, #16181b 0%, #2b3035 100%); border: 1px solid #ffc107;">
                        <div class="row align-items-center g-3">
                            <div class="col-md-7">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-warning text-dark shadow" style="width: 52px; height: 52px;">
                                        <i class="bi bi-crown-fill fs-3"></i>
                                    </div>
                                    <div>
                                        <div class="small text-warning fw-bold tracking-wide">CURRENT TIER • 目前會員等級</div>
                                        <h4 class="fw-bold text-white mb-0"><?php echo $lvl_names[$mb_lvl]; ?> </h4>
                                    </div>
                                </div>
                                <p class="text-white-50 small mb-3">
                                    年度累積消費金額已達 <strong class="text-white">NT$ <?php echo number_format($total_spent); ?></strong>。再累積消費 <strong class="text-warning">NT$ <?php echo number_format($remain_amt); ?></strong>，即可自動晉升為最高階「黑鑽領袖會員 (Black Diamond)」，享全站課程 85 折與專屬顧問優先排程！
                                </p>
                                <!-- 升級進度條 -->
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-white-50">升級進度 (NT$ <?php echo number_format($total_spent); ?> / NT$ <?php echo number_format($next_target); ?>)</span>
                                    <span class="fw-bold text-warning"><?php echo $spent_pct; ?>%</span>
                                </div>
                                <div class="progress bg-dark border border-secondary" style="height: 10px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: <?php echo $spent_pct; ?>%;" aria-valuenow="<?php echo $spent_pct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>

                            <!-- 右側：PTS 紅利點數資產總覽 -->
                            <div class="col-md-5">
                                <div class="p-3 rounded-3 bg-dark border border-secondary text-center h-100 d-flex flex-column justify-content-center">
                                    <div class="text-white-50 small mb-1">可用紅利點數餘額</div>
                                    <div class="display-5 fw-bold text-warning mb-1" id="memberPtsDisplay">
                                        <?php echo number_format($member_pts ?? 0); ?> <span class="fs-6 fw-normal text-white-50">PTS</span>
                                    </div>
                                    <div class="text-muted small mb-3" style="font-size: 0.75rem;">1 PTS = NT$ 1 結帳折抵（300 PTS 將於年底到期）</div>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" class="btn btn-outline-warning btn-sm px-3" data-bs-toggle="modal" data-bs-target="#ptsHistoryModal">
                                            <i class="bi bi-clock-history me-1"></i>點數明細
                                        </button>
                                        <a href="index.php#pricing" class="btn btn-warning text-dark fw-bold btn-sm px-3">
                                            前往折抵
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 操作結果即時提示區 -->
                    <div id="giftsAlertBox" class="alert d-none align-items-center mb-4" role="alert">
                        <i id="giftsAlertIcon" class="bi me-2 fs-5"></i>
                        <div id="giftsAlertMsg" class="small fw-bold"></div>
                    </div>

                    <!-- 分頁導航列 (Nav Pills) -->
                    <ul class="nav nav-pills mb-4 gap-2" id="giftsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold px-4" id="vouchers-tab" data-bs-toggle="pill" data-bs-target="#vouchers-pane" type="button" role="tab">
                                <i class="bi bi-ticket-perforated-fill me-2"></i>我的折價券與代碼 (<span id="voucherCountBadge"><?php echo count($gift_records); ?></span>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-4" id="exclusive-gifts-tab" data-bs-toggle="pill" data-bs-target="#exclusive-gifts-pane" type="button" role="tab">
                                <i class="bi bi-stars me-2"></i>等級專屬優惠活動
                            </button>
                        </li>
                    </ul>

                    <!-- 分頁內容主體 -->
                    <div class="tab-content" id="giftsTabContent">

                        <!-- ============================================================
                             區塊 2：點數/紅利與票券 (可用折價券、優惠代碼、使用期限)
                             ============================================================ -->
                        <div class="tab-pane fade show active" id="vouchers-pane" role="tabpanel" tabindex="0">
                            
                            <!-- 【補足模組 A】：優惠代碼即時兌換歸戶列 -->
                            <form id="formRedeemPromoCode" class="p-3 bg-light border rounded-3 mb-4">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <label for="inputPromoCode" class="fw-bold text-dark small mb-0">
                                            <i class="bi bi-qr-code-scan me-1 text-primary"></i>輸入活動優惠代碼歸戶：
                                        </label>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" class="form-control form-control-sm text-uppercase" id="inputPromoCode" placeholder="請輸入優惠代碼（測試：WELCOME_500）" required>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-dark text-warning fw-bold btn-sm w-100">
                                            立即兌換票券
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- ============================================================
                                資料庫動態區：我的專屬禮品與數位贈品櫃 (fsl_gift_records + gifts)
                                ============================================================ -->
                            <div class="mb-4 pb-4 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold text-dark mb-0">
                                        <i class="bi bi-box2-heart-fill me-2 text-warning"></i>我的專屬禮品櫃
                                        <?php if ($unclaimed_count > 0): ?>
                                            <span class="badge rounded-pill bg-danger ms-1" id="unclaimedGiftBadge"><?php echo $unclaimed_count; ?> 待領取</span>
                                        <?php endif; ?>
                                    </h5>
                                    <span class="small text-muted">共 <?php echo count($gift_records); ?> 份禮遇紀錄</span>
                                </div>

                                <?php if (empty($gift_records)): ?>
                                    <div class="p-4 text-center bg-light rounded-3 border text-muted small">
                                        目前尚無禮品紀錄，點擊右上角「開啟幸運大轉盤」試試手氣吧！
                                    </div>
                                <?php else: ?>
                                    <div class="row g-3" id="myGiftRecordsList">
                                        <?php foreach ($gift_records as $gr):
                                            $grec_id   = (int)$gr['grec_id'];
                                            $status    = (int)$gr['grec_status']; // 0待領取, 1已領取, 2實體寄送中, 3已使用, 9已逾期
                                            $src_label = $source_labels[(int)$gr['grec_source_type']] ?? '專屬禮遇';
                                            $pts_val   = (int)$gr['gift_pts'];

                                            // 整理該禮品關聯的數位下載檔
                                            $digital_items = [];
                                            if (!empty($gr['gift_merch_ids'])) {
                                                foreach (explode(',', $gr['gift_merch_ids']) as $mid) {
                                                    $m_obj = $merch_map[(int)trim($mid)] ?? null;
                                                    if ($m_obj && (int)$m_obj['merch_type'] === 2 && !empty($m_obj['merch_download_url'])) {
                                                        $digital_items[] = $m_obj;
                                                    }
                                                }
                                            }
                                        ?>
                                            <div class="col-md-6">
                                                <div class="border <?php echo ($status === 0) ? 'border-warning border-2' : ''; ?> rounded-3 p-3 bg-white shadow-sm h-100 d-flex flex-column justify-content-between" id="giftCard_<?php echo $grec_id; ?>">
                                                    <div>
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="badge bg-dark text-warning"><?php echo htmlspecialchars($src_label); ?></span>
                                                            <span class="gift-status-badge">
                                                                <?php if ($status === 0): ?>
                                                                    <span class="badge bg-danger">待領取</span>
                                                                <?php elseif ($status === 1): ?>
                                                                    <span class="badge bg-success">已領取入帳</span>
                                                                <?php elseif ($status === 2): ?>
                                                                    <span class="badge bg-info text-dark">實體寄送中</span>
                                                                <?php elseif ($status === 3): ?>
                                                                    <span class="badge bg-secondary">已使用完畢</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary">已逾期失效</span>
                                                                <?php endif; ?>
                                                            </span>
                                                        </div>

                                                        <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($gr['gift_title']); ?></h6>
                                                        <?php if (!empty($gr['gift_subtitle'])): ?>
                                                            <p class="text-muted small mb-2"><?php echo htmlspecialchars($gr['gift_subtitle']); ?></p>
                                                        <?php endif; ?>

                                                        <!-- 內含獎勵明細標籤 -->
                                                        <div class="d-flex flex-wrap gap-1 mb-3">
                                                            <?php if ($pts_val > 0): ?>
                                                                <span class="badge bg-warning-subtle text-dark border border-warning">
                                                                    <i class="bi bi-coin me-1"></i>+<?php echo number_format($pts_val); ?> PTS
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if ((float)$gr['gift_discount_usd'] > 0): ?>
                                                                <span class="badge bg-primary-subtle text-primary">
                                                                    折抵 USD$ <?php echo number_format((float)$gr['gift_discount_usd'], 0); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php foreach ($digital_items as $di): ?>
                                                                <span class="badge bg-success-subtle text-success">
                                                                    <i class="bi bi-file-earmark-arrow-down me-1"></i><?php echo htmlspecialchars($di['merch_file_format'] ?: '數位檔'); ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>

                                                    <!-- 底部操作按鈕區 -->
                                                    <div class="pt-2 border-top d-flex flex-wrap justify-content-between align-items-center gap-2 gift-action-zone">
                                                        <small class="text-muted">
                                                            <?php echo !empty($gr['grec_expired_at']) ? '期限：' . date('Y-m-d', strtotime($gr['grec_expired_at'])) : '永久有效'; ?>
                                                        </small>

                                                        <?php if ($status === 0): ?>
                                                            <button type="button"
                                                                    class="btn btn-warning text-dark btn-sm fw-bold px-3 btn-claim-db-gift"
                                                                    data-grec-id="<?php echo $grec_id; ?>"
                                                                    data-title="<?php echo htmlspecialchars($gr['gift_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-pts="<?php echo $pts_val; ?>">
                                                                <i class="bi bi-gift-fill me-1"></i>立即領取
                                                            </button>
                                                        <?php elseif ($status === 1 && !empty($digital_items)): ?>
                                                            <div class="d-flex flex-wrap gap-1">
                                                                <?php foreach ($digital_items as $di): ?>
                                                                    <a href="<?php echo htmlspecialchars($di['merch_download_url']); ?>" target="_blank" class="btn btn-dark text-warning btn-sm py-0 px-2 fw-bold">
                                                                        <i class="bi bi-download me-1"></i>下載 <?php echo htmlspecialchars($di['merch_file_format'] ?: '檔案'); ?>
                                                                    </a>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php elseif ($status === 2 && !empty($gr['grec_shipping_note'])): ?>
                                                            <small class="text-info fw-bold"><?php echo htmlspecialchars($gr['grec_shipping_note']); ?></small>
                                                        <?php else: ?>
                                                            <span class="small text-muted">已完成歸戶</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>


                            <!-- 票券卡片列表 -->
                            <div class="row g-3" id="voucherCardList"></div>

                        </div> <!-- 結束區塊 2：vouchers-pane -->

                        <!-- ============================================================
                             區塊 3：專屬優惠 (資料庫動態讀取 VIP 等級專屬禮遇與解鎖狀態)
                             ============================================================ -->
                        <div class="tab-pane fade" id="exclusive-gifts-pane" role="tabpanel" tabindex="0">
                            <div class="row g-3">
                                <?php if (empty($vip_gifts)): ?>
                                    <div class="col-12">
                                        <div class="p-4 text-center bg-light rounded-3 border text-muted small">
                                            目前尚無等級專屬優惠活動，敬請期待下一季尊榮禮遇！
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($vip_gifts as $vg):
                                        $vg_id       = (int)$vg['gift_id'];
                                        $req_lvl     = (int)$vg['gift_target_minlvl'];
                                        $lvl_label   = $lvl_names[$req_lvl] ?? ('等級 ' . $req_lvl . ' 專屬');
                                        $is_unlocked = ($mb_lvl >= $req_lvl);
                                        $is_claimed  = isset($claimed_vip_ids[$vg_id]);
                                        $pts_reward  = (int)$vg['gift_pts'];
                                    ?>
                                        <div class="col-md-6">
                                            <div class="card <?php echo $is_unlocked ? 'border-warning bg-white' : 'border bg-light'; ?> h-100 shadow-sm">
                                                <div class="card-body d-flex flex-column justify-content-between">
                                                    <div>
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <?php if ($is_unlocked): ?>
                                                                <span class="badge bg-dark text-warning"><i class="bi bi-crown-fill me-1"></i><?php echo htmlspecialchars($lvl_label); ?>獨享</span>
                                                                <span class="<?php echo $is_claimed ? 'text-muted' : 'text-success'; ?> small fw-bold">
                                                                    <?php echo $is_claimed ? '本月已領取' : '本月可領取'; ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i><?php echo htmlspecialchars($lvl_label); ?>限定</span>
                                                                <span class="text-muted small">達「<?php echo htmlspecialchars($lvl_label); ?>」解鎖</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($vg['gift_title']); ?></h5>
                                                        <p class="text-muted small mb-3">
                                                            <?php echo htmlspecialchars($vg['gift_subtitle'] ?: '尊榮會員等級專屬月度禮遇，領取後自動歸戶至您的禮品櫃與點數帳戶。'); ?>
                                                        </p>
                                                    </div>

                                                    <?php if (!$is_unlocked): ?>
                                                        <button type="button" class="btn btn-outline-secondary w-100" disabled>
                                                            <i class="bi bi-lock me-1"></i>達到<?php echo htmlspecialchars($lvl_label); ?>後解鎖
                                                        </button>
                                                    <?php elseif ($is_claimed): ?>
                                                        <button type="button" class="btn btn-secondary text-white w-100" disabled>
                                                            <i class="bi bi-check-circle-fill me-1"></i>本月禮包已領取完畢
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button"
                                                                class="btn btn-warning text-dark fw-bold w-100 btn-claim-vip-gift"
                                                                data-gift-id="<?php echo $vg_id; ?>"
                                                                data-points="<?php echo $pts_reward; ?>"
                                                                data-title="<?php echo htmlspecialchars($vg['gift_title'], ENT_QUOTES, 'UTF-8'); ?>">
                                                            <i class="bi bi-gift me-1"></i>立即領取<?php echo ($pts_reward > 0) ? " {$pts_reward} PTS" : '專屬禮遇'; ?>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div> <!-- 結束區塊 3：exclusive-gifts-pane -->

                    </div> <!-- 結束 tab-content -->

                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- ==========================================================================
     彈窗：PTS 紅利點數異動明細紀錄
     ========================================================================== -->
<div class="modal fade" id="ptsHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-coin me-2 text-warning"></i>PTS 紅利點數異動明細</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">異動日期</th>
                                <th>項目說明</th>
                                <th>點數異動</th>
                                <th class="pe-4 text-end">結餘</th>
                            </tr>
                        </thead>
                        <tbody id="ptsHistoryTbody">
                            <?php if (empty($pts_logs)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">目前尚無紅利點數異動明細紀錄。</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pts_logs as $log):
                                    $trade     = (int)$log['pt_trade'];
                                    $is_plus   = ($trade >= 0);
                                    $trade_cls = $is_plus ? 'text-success' : 'text-danger';
                                    $trade_str = ($is_plus ? '+' : '') . number_format($trade) . ' PTS';
                                    $memo_text = !empty($log['pt_memo'])
                                        ? $log['pt_memo']
                                        : ($is_plus ? '點數獲得入帳' : '點數折抵扣除');
                                ?>
                                    <tr>
                                        <td class="ps-4 text-muted text-nowrap"><?php echo date('Y-m-d H:i', strtotime($log['pt_date'])); ?></td>
                                        <td class="fw-bold text-dark"><?php echo htmlspecialchars($memo_text); ?></td>
                                        <td class="<?php echo $trade_cls; ?> fw-bold text-nowrap"><?php echo $trade_str; ?></td>
                                        <td class="pe-4 text-end fw-bold text-nowrap"><?php echo number_format((int)$log['pt_available']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">關閉視窗</button>
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
    #giftsTab .nav-link {
        color: #495057;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
    }
    #giftsTab .nav-link:hover {
        background-color: #e9ecef;
        color: #212529;
    }
    #giftsTab .nav-link.active {
        background-color: #212529 !important;
        color: #ffc107 !important;
        border-color: #212529 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
</style>

<?php include_once '5sl_include_footer.php'; ?>

<script>
$(function() {
    let currentPts = <?php echo (int)$member_pts; ?>;
    let currentVoucherCount = <?php echo count($gift_records); ?>;

    // 1. 通用提示框控制
    function showGiftsAlert(type, msg) {
        const $box = $('#giftsAlertBox');
        const $icon = $('#giftsAlertIcon');
        $box.removeClass('d-none alert-success alert-danger alert-warning').addClass('d-flex');
        $icon.removeClass('bi-check-circle-fill bi-exclamation-triangle-fill');

        if (type === 'success') {
            $box.addClass('alert-success');
            $icon.addClass('bi-check-circle-fill');
        } else {
            $box.addClass('alert-danger');
            $icon.addClass('bi-exclamation-triangle-fill');
        }
        $('#giftsAlertMsg').text(msg);
        $('html, body').animate({ scrollTop: $box.offset().top - 120 }, 300);
    }

    // 2. 一鍵複製優惠代碼至剪貼簿
    $(document).on('click', '.btn-copy-code', function() {
        const $btn = $(this);
        const code = $btn.attr('data-code');
        navigator.clipboard.writeText(code).then(function() {
            const originalHtml = $btn.html();
            $btn.removeClass('btn-outline-dark').addClass('btn-success').html('<i class="bi bi-check2 me-1"></i>已複製');
            setTimeout(function() {
                $btn.removeClass('btn-success').addClass('btn-outline-dark').html(originalHtml);
            }, 1800);
        });
    });

    // 3. 優惠代碼即時兌換歸戶（串接 5sl_act_member.php -> act_redeem_code）
    $('#formRedeemPromoCode').on('submit', function(e) {
        e.preventDefault();
        const code = $('#inputPromoCode').val().trim().toUpperCase();
        if (!code) return;

        if (window.fslLoader) window.fslLoader.show();

        $.post('funcFiles/5sl_act_member.php', {
            action: 'act_redeem_code',
            code: code
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();

            if (res.success === 1) {
                currentVoucherCount++;
                $('#voucherCountBadge').text(currentVoucherCount);

                const safeTitle = $('<div>').text(res.gift_title).html();
                const safeSub   = $('<div>').text(res.gift_sub).html();
                const safeCode  = $('<div>').text(res.gift_code).html();
                const badgeTag  = (res.added_pts > 0)
                    ? `<span class="badge bg-warning text-dark fw-bold">+${res.added_pts} PTS 禮遇</span>`
                    : `<span class="badge bg-warning text-dark fw-bold">專屬活動禮券</span>`;

                const newCardHtml = `
                    <div class="col-md-6">
                        <div class="border border-warning rounded-3 p-3 bg-white shadow-sm h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    ${badgeTag}
                                    <small class="text-danger fw-bold"><i class="bi bi-alarm me-1"></i>${res.expired_str} 到期</small>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">${safeTitle}</h5>
                                <p class="text-muted small mb-3">${safeSub}</p>
                            </div>
                            <div class="pt-2 border-top d-flex justify-content-between align-items-center bg-light p-2 rounded">
                                <div class="small"><span class="text-muted">代碼：</span><code class="fw-bold text-dark fs-6">${safeCode}</code></div>
                                <button type="button" class="btn btn-outline-dark btn-sm py-0 px-2 btn-copy-code" data-code="${safeCode}"><i class="bi bi-clipboard me-1"></i>複製代碼</button>
                            </div>
                        </div>
                    </div>`;

                $('#voucherCardList').prepend(newCardHtml);
                $('#inputPromoCode').val('');

                // 若兌換代碼內含點數，同步更新頁面餘額與點數明細彈窗
                if (res.added_pts > 0 && res.new_pts !== null) {
                    currentPts = parseInt(res.new_pts, 10);
                    $('#memberPtsDisplay').html(`${currentPts.toLocaleString()} <span class="fs-6 fw-normal text-white-50">PTS</span>`);
                    const nowStr = new Date().toLocaleString('zh-TW', { hour12: false }).slice(0, 16);
                    $('#ptsHistoryTbody').prepend(`
                        <tr>
                            <td class="ps-4 text-muted text-nowrap">${nowStr}</td>
                            <td class="fw-bold text-dark">兌換優惠代碼：${safeTitle} (${safeCode})</td>
                            <td class="text-success fw-bold text-nowrap">+${res.added_pts.toLocaleString()} PTS</td>
                            <td class="pe-4 text-end fw-bold text-nowrap">${currentPts.toLocaleString()}</td>
                        </tr>
                    `);
                }

                showGiftsAlert('success', res.message);
            } else {
                showGiftsAlert('danger', res.message || '優惠代碼兌換失敗，請確認後再試。');
            }
        }, 'json').fail(function() {
            if (window.fslLoader) window.fslLoader.hide();
            showGiftsAlert('danger', '網路連線異常，請稍後再試。');
        });
    });

    // 4. 領取 VIP 等級專屬優惠（串接資料庫 act_claim_vip_gift + 3D 特效彈窗）
    $(document).on('click', '.btn-claim-vip-gift', function() {
        const $btn = $(this);
        const giftId = $btn.attr('data-gift-id');
        const title = $btn.attr('data-title');

        if (window.fslLoader) window.fslLoader.show();

        $.post('funcFiles/5sl_act_member.php', {
            action: 'act_claim_vip_gift',
            gift_id: giftId
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();

            if (res.success === 1) {
                $btn.prop('disabled', true)
                    .removeClass('btn-warning text-dark')
                    .addClass('btn-secondary text-white')
                    .html('<i class="bi bi-check-circle-fill me-1"></i>本月禮包已領取完畢');

                // 更新頂部點數餘額與點數明細彈窗
                if (res.added_pts > 0 && res.new_pts !== null) {
                    currentPts = parseInt(res.new_pts, 10);
                    $('#memberPtsDisplay').html(`${currentPts.toLocaleString()} <span class="fs-6 fw-normal text-white-50">PTS</span>`);
                    const nowStr = new Date().toLocaleString('zh-TW', { hour12: false }).slice(0, 16);
                    const safeTitle = $('<div>').text(title).html();
                    $('#ptsHistoryTbody').prepend(`
                        <tr>
                            <td class="ps-4 text-muted text-nowrap">${nowStr}</td>
                            <td class="fw-bold text-dark">VIP 專屬禮遇領取：${safeTitle}</td>
                            <td class="text-success fw-bold text-nowrap">+${res.added_pts.toLocaleString()} PTS</td>
                            <td class="pe-4 text-end fw-bold text-nowrap">${currentPts.toLocaleString()}</td>
                        </tr>
                    `);
                }

                // 觸發全站 3D 慶祝特效彈窗
                if (window.fslEpicEngine) {
                    window.fslEpicEngine.trigger({
                        contentType: 'html',
                        contentSource: window.fslEpicEngine.createCouponTemplate({
                            bgUrl: 'imageFiles/test/<?php echo rand(1,18); ?>.jpg',
                            points: (res.added_pts > 0) ? res.added_pts.toString() : 'VIP GIFT',
                            unit: (res.added_pts > 0) ? 'PTS' : 'UNLOCKED',
                            title: 'VIP REWARD CLAIMED!',
                            subTitle: title,
                            label: '★ 專屬禮遇已匯入您的帳戶與禮品櫃 ★'
                        }),
                        theme: 'celebration',
                        onConfirm: function(close) { close(); }
                    });
                } else {
                    showGiftsAlert('success', res.message);
                }
            } else {
                showGiftsAlert('danger', res.message || '領取失敗，請稍後再試。');
            }
        }, 'json').fail(function() {
            if (window.fslLoader) window.fslLoader.hide();
            showGiftsAlert('danger', '網路連線異常，請稍後再試。');
        });
    });

    // 5. 串接資料庫：一鍵領取待領禮品 (呼叫 5sl_act_member.php -> act_claim_gift)
    $(document).on('click', '.btn-claim-db-gift', function() {
        const $btn = $(this);
        const grecId = $btn.attr('data-grec-id');
        const giftTitle = $btn.attr('data-title');

        if (window.fslLoader) window.fslLoader.show();

        $.post('funcFiles/5sl_act_member.php', {
            action: 'act_claim_gift',
            grec_id: grecId
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();

            if (res.success === 1) {
                const $card = $(`#giftCard_${grecId}`);
                $card.removeClass('border-warning border-2');

                // 更新右上角狀態徽章
                if (res.grec_status === 2) {
                    $card.find('.gift-status-badge').html('<span class="badge bg-info text-dark">實體寄送中</span>');
                } else {
                    $card.find('.gift-status-badge').html('<span class="badge bg-success">已領取入帳</span>');
                }

                // 若內含數位檔案，動態產生下載按鈕；否則顯示已完成歸戶
                if (res.digital_files && res.digital_files.length > 0) {
                    let dlHtml = '<div class="d-flex flex-wrap gap-1">';
                    res.digital_files.forEach(function(f) {
                        const fmt = f.format || '檔案';
                        dlHtml += `<a href="${f.url}" target="_blank" class="btn btn-dark text-warning btn-sm py-0 px-2 fw-bold"><i class="bi bi-download me-1"></i>下載 ${fmt}</a>`;
                    });
                    dlHtml += '</div>';
                    $btn.replaceWith(dlHtml);
                } else {
                    $btn.replaceWith('<span class="small text-muted">已完成歸戶</span>');
                }

                // 同步更新點數顯示
                if (res.added_pts > 0) {
                    currentPts += parseInt(res.added_pts, 10);
                    $('#memberPtsDisplay').html(`${currentPts.toLocaleString()} <span class="fs-6 fw-normal text-white-50">PTS</span>`);
                }

                // 更新待領取數量紅標
                const remainUnclaimed = $('.btn-claim-db-gift').length;
                if (remainUnclaimed > 0) {
                    $('#unclaimedGiftBadge').text(`${remainUnclaimed} 待領取`);
                } else {
                    $('#unclaimedGiftBadge').remove();
                }

                // 連動全站 3D 慶祝特效彈窗
                if (window.fslEpicEngine) {
                    window.fslEpicEngine.trigger({
                        contentType: 'html',
                        contentSource: window.fslEpicEngine.createCouponTemplate({
                            bgUrl: 'imageFiles/test/<?php echo rand(1,18); ?>.jpg',
                            points: (res.added_pts > 0) ? res.added_pts.toString() : 'GIFT',
                            unit: (res.added_pts > 0) ? 'PTS' : 'UNLOCKED',
                            title: 'GIFT CLAIMED!',
                            subTitle: giftTitle,
                            label: res.message
                        }),
                        theme: 'celebration',
                        onConfirm: function(close) { close(); }
                    });
                } else {
                    showGiftsAlert('success', res.message);
                }
            } else {
                showGiftsAlert('danger', res.message || '領取失敗，請稍後再試。');
            }
        }, 'json').fail(function() {
            if (window.fslLoader) window.fslLoader.hide();
            showGiftsAlert('danger', '網路連線異常，請稍後再試。');
        });
    });



});
</script>
