<?php
// 宣告當前頁面身分為 myQuests（我的任務）
$current_page = 'myQuests';

// 引入共用頁首
include_once '5sl_include_header.php';

$mb_id = (int)($_SESSION['mbID'] ?? $_SESSION['user_id'] ?? 0);
$mb_lvl = (int)($_SESSION['user_lvl'] ?? 3); // 預設黃金高階 VIP (等級 3)

// 1. 查詢所有啟用中的任務定義 + 關聯獎品 (fsl_gifts) + 該會員的任務進度紀錄 (fsl_quest_records)
$sql_quests = "
    SELECT 
        q.*,
        g.gift_title, g.gift_pts, g.gift_merch_ids,
        qr.qrec_id, qr.qrec_curr_val, qr.qrec_status, qr.qrec_claimed_at, qr.grec_id
    FROM fsl_quests q
    INNER JOIN fsl_gifts g ON q.gift_id = g.gift_id
    LEFT JOIN fsl_quest_records qr ON q.quest_id = qr.quest_id AND qr.mb_id = ?
    WHERE q.quest_status = 1
    ORDER BY q.quest_tier_lvl ASC, q.quest_is_grand ASC, q.quest_sort ASC
";
$all_quests = ($mb_id > 0) ? $DB->query($sql_quests, [$mb_id]) : [];
if (!is_array($all_quests)) $all_quests = [];

// 2. 預先批次撈出任務獎品內含的「數位下載檔」與「實體商品」設定 (merchandise)，避免迴圈內重複查詢
$merch_info_map = [];
$all_mids = [];
foreach ($all_quests as $q) {
    if (!empty($q['gift_merch_ids'])) {
        foreach (explode(',', $q['gift_merch_ids']) as $mid) {
            $mid_int = (int)trim($mid);
            if ($mid_int > 0) $all_mids[$mid_int] = $mid_int;
        }
    }
}
if (!empty($all_mids)) {
    $placeholders = implode(',', array_fill(0, count($all_mids), '?'));
    $m_rows = $DB->query(
        "SELECT merch_id, merch_type, merch_title, merch_download_url, merch_file_format FROM merchandise WHERE merch_id IN ($placeholders)",
        array_values($all_mids)
    );
    if (is_array($m_rows)) {
        foreach ($m_rows as $mr) {
            $merch_info_map[(int)$mr['merch_id']] = $mr;
        }
    }
}

// 3. 依等級任務群分類（1=一般會員, 3=黃金高階VIP, 4=黑鑽尊享VIP），並拆出「終極達成大禮」
$quests_by_tier = [1 => [], 3 => [], 4 => []];
$grand_by_tier  = [];

foreach ($all_quests as &$q) {
    $t_lvl = (int)$q['quest_tier_lvl'];
    $q['has_physical']  = false;
    $q['digital_files'] = [];

    if (!empty($q['gift_merch_ids'])) {
        foreach (explode(',', $q['gift_merch_ids']) as $mid) {
            $m_obj = $merch_info_map[(int)trim($mid)] ?? null;
            if ($m_obj) {
                if ((int)$m_obj['merch_type'] === 1) {
                    $q['has_physical'] = true;
                } elseif ((int)$m_obj['merch_type'] === 2 && !empty($m_obj['merch_download_url'])) {
                    $q['digital_files'][] = $m_obj;
                }
            }
        }
    }

    if ((int)$q['quest_is_grand'] === 1) {
        $grand_by_tier[$t_lvl] = $q;
    } else {
        $quests_by_tier[$t_lvl][] = $q;
    }
}
unset($q);

// 4. 計算「目前會員等級任務群」的完成度進度條與終極大禮解鎖狀態
$active_tier_lvl    = isset($quests_by_tier[$mb_lvl]) && !empty($quests_by_tier[$mb_lvl]) ? $mb_lvl : 3;
$curr_tier_quests   = $quests_by_tier[$active_tier_lvl] ?? [];
$curr_grand_quest   = $grand_by_tier[$active_tier_lvl] ?? null;

$total_quest_cnt    = count($curr_tier_quests);
$done_quest_cnt     = 0;
foreach ($curr_tier_quests as $cq) {
    if ((int)($cq['qrec_status'] ?? 0) === 2) {
        $done_quest_cnt++;
    }
}
$quest_progress_pct = ($total_quest_cnt > 0) ? (int)round(($done_quest_cnt / $total_quest_cnt) * 100) : 0;
$is_grand_unlocked  = ($total_quest_cnt > 0 && $done_quest_cnt >= $total_quest_cnt);
$is_grand_claimed   = ($curr_grand_quest && (int)($curr_grand_quest['qrec_status'] ?? 0) === 2);

// 等級名稱對照表
$tier_labels = [
    1 => '一般會員',
    2 => '進階會員',
    3 => '黃金高階 VIP',
    4 => '黑鑽尊享 VIP',
];
$curr_tier_name = $tier_labels[$active_tier_lvl] ?? '黃金高階 VIP';
?>

<div class="container my-5 py-4">
    <div class="row g-4 mt-2">
        
        <!-- 左側：會員中心共用子導航列 -->
        <div class="col-md-4 col-lg-3">
            <?php include_once '5sl_include_subnavi.php'; ?>
        </div>

        <!-- 右側：我的任務核心工作視窗 -->
        <div class="col-md-8 col-lg-9">
            <div class="card border-0 shadow-sm p-4 p-md-5 bg-white bg-opacity-75 rounded-3 h-100" style="min-height: 620px;">
                <div class="card-body p-0">
                    
                    <!-- 頁面主標題 -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 mb-4">
                        <div>
                            <h3 class="fw-bold text-dark mb-1">
                                <i class="bi bi-trophy-fill me-2 text-warning"></i>我的任務 (Member Quests)
                            </h3>
                            <p class="text-muted small mb-0">完成系統為您專屬佈置的階段任務，領取紅利點數、限定資源與限量實體好禮。</p>
                        </div>
                        <div class="mt-3 mt-md-0">
                            <span class="badge bg-dark text-warning border border-warning px-3 py-2">
                                <i class="bi bi-crown-fill me-1"></i>目前任務群：黃金尊榮會員
                            </span>
                        </div>
                    </div>

                    <!-- 操作結果即時提示區 -->
                    <div id="questsAlertBox" class="alert d-none align-items-center mb-4" role="alert">
                        <i id="questsAlertIcon" class="bi me-2 fs-5"></i>
                        <div id="questsAlertMsg" class="small fw-bold"></div>
                    </div>

                    <!-- ============================================================
                        區塊 1：任務總進度與「任務達成大禮」終極寶箱橫幅
                        ============================================================ -->
                    <div class="p-4 rounded-3 text-white mb-4 shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, #16181b 0%, #2b3035 100%); border: 2px solid #ffc107;">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-warning text-dark fw-bold">STAGE COMPLETION BONUS</span>
                                    <span class="text-white-50 small">全任務通關終極獎勵</span>
                                </div>
                                <h4 class="fw-bold text-white mb-2">
                                    <?php echo $curr_grand_quest ? htmlspecialchars($curr_grand_quest['quest_title']) : ($curr_tier_name . '季度達成大禮包'); ?>
                                </h4>
                                <p class="text-white-50 small mb-3">
                                    <?php if ($curr_grand_quest): ?>
                                        <?php echo htmlspecialchars($curr_grand_quest['quest_desc']); ?><br>
                                        <span class="text-warning fw-bold"><i class="bi bi-gift-fill me-1"></i><?php echo htmlspecialchars($curr_grand_quest['quest_reward_label']); ?></span>
                                    <?php else: ?>
                                        完成下方全部 <strong class="text-white"><?php echo $total_quest_cnt; ?> 項</strong> 專屬任務，即可解鎖終極獎勵！
                                    <?php endif; ?>
                                </p>

                                <!-- 任務完成度進度條 -->
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-white-50">目前任務完成度 (<span id="questCompletedCount"><?php echo $done_quest_cnt; ?></span> / <span id="questTotalCount"><?php echo $total_quest_cnt; ?></span> 項)</span>
                                    <span class="fw-bold text-warning" id="questProgressPercent"><?php echo $quest_progress_pct; ?>%</span>
                                </div>
                                <div class="progress bg-dark border border-secondary" style="height: 12px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" id="questMainProgressBar" role="progressbar" style="width: <?php echo $quest_progress_pct; ?>%;" aria-valuenow="<?php echo $quest_progress_pct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>

                            <!-- 右側：終極達成大禮領取狀態卡 -->
                            <div class="col-lg-5">
                                <div class="p-3 rounded-3 bg-dark border border-warning text-center h-100 d-flex flex-column justify-content-center align-items-center" id="grandPrizeCard">
                                    <?php if ($is_grand_claimed): ?>
                                        <div class="mb-2" id="grandPrizeIconBox">
                                            <i class="bi bi-patch-check-fill display-4 text-success"></i>
                                        </div>
                                        <h6 class="fw-bold text-warning mb-1" id="grandPrizeStatusTitle">季度任務全數制霸！</h6>
                                        <p class="text-white-50 small mb-3" id="grandPrizeStatusDesc" style="font-size: 0.75rem;">終極達成大禮包已成功領取歸戶</p>
                                        <button type="button" class="btn btn-success text-white btn-sm w-100 fw-bold py-2" disabled>
                                            <i class="bi bi-patch-check-fill me-1"></i>季度達成大禮已全數領取
                                        </button>
                                    <?php elseif ($is_grand_unlocked && $curr_grand_quest): ?>
                                        <div class="mb-2" id="grandPrizeIconBox">
                                            <i class="bi bi-gift-fill display-4 text-warning" style="animation: fslPulse 1s infinite alternate;"></i>
                                        </div>
                                        <h6 class="fw-bold text-warning mb-1" id="grandPrizeStatusTitle">終極達成大禮已解鎖！</h6>
                                        <p class="text-white-50 small mb-3" id="grandPrizeStatusDesc" style="font-size: 0.75rem;">恭喜完成全部任務，立即領取終極大獎</p>
                                        <button type="button"
                                                class="btn btn-warning text-dark btn-sm w-100 fw-bold py-2 badge-alarm-pulse"
                                                id="btnClaimGrandPrize"
                                                data-quest-id="<?php echo (int)$curr_grand_quest['quest_id']; ?>"
                                                data-gift-title="<?php echo htmlspecialchars($curr_grand_quest['gift_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-pts="<?php echo (int)$curr_grand_quest['gift_pts']; ?>">
                                            <i class="bi bi-stars me-1"></i>立即領取任務達成大禮
                                        </button>
                                    <?php else: ?>
                                        <?php $remain_cnt = max(1, $total_quest_cnt - $done_quest_cnt); ?>
                                        <div class="mb-2" id="grandPrizeIconBox">
                                            <i class="bi bi-lock-fill display-4 text-secondary"></i>
                                        </div>
                                        <h6 class="fw-bold text-white mb-1" id="grandPrizeStatusTitle">大禮包尚未解鎖</h6>
                                        <p class="text-white-50 small mb-3" id="grandPrizeStatusDesc" style="font-size: 0.75rem;">尚差 <?php echo $remain_cnt; ?> 項任務即可開啟終極獎勵</p>
                                        <button type="button"
                                                class="btn btn-secondary btn-sm w-100 fw-bold py-2"
                                                id="btnClaimGrandPrize"
                                                data-quest-id="<?php echo $curr_grand_quest ? (int)$curr_grand_quest['quest_id'] : 0; ?>"
                                                data-gift-title="<?php echo $curr_grand_quest ? htmlspecialchars($curr_grand_quest['gift_title'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                                                data-pts="<?php echo $curr_grand_quest ? (int)$curr_grand_quest['gift_pts'] : 0; ?>"
                                                disabled>
                                            <i class="bi bi-lock me-1"></i>完成全部任務後解鎖
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- ============================================================
                         區塊 2：會員等級任務群切換標籤 (Nav Pills)
                         ============================================================ -->
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-list-check me-2 text-primary"></i>等級專屬任務清單</h5>
                        <ul class="nav nav-pills nav-sm gap-1" id="tierQuestTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-1 px-3 small fw-bold" id="tier-basic-tab" data-bs-toggle="pill" data-bs-target="#tier-basic-pane" type="button" role="tab">一般會員任務</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active py-1 px-3 small fw-bold" id="tier-gold-tab" data-bs-toggle="pill" data-bs-target="#tier-gold-pane" type="button" role="tab">
                                    <i class="bi bi-star-fill me-1"></i>黃金會員 (目前等級)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-1 px-3 small fw-bold" id="tier-diamond-tab" data-bs-toggle="pill" data-bs-target="#tier-diamond-pane" type="button" role="tab">黑鑽領袖任務</button>
                            </li>
                        </ul>
                    </div>

                    <!-- 分頁內容主體 -->
                    <div class="tab-content" id="tierQuestTabContent">

                        <!-- ============================================================
                             分頁 A：黃金尊榮會員任務群 (目前啟用等級 - 含三種獎品類型)
                             ============================================================ -->
                        <div class="tab-pane fade show active" id="tier-gold-pane" role="tabpanel" tabindex="0">
                            <div class="d-flex flex-column gap-3" id="goldQuestList">
                                <?php if (empty($curr_tier_quests)): ?>
                                    <div class="p-4 text-center bg-light rounded-3 border text-muted small">
                                        目前此等級任務群尚無進行中的階段任務。
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($curr_tier_quests as $q):
                                        $qid       = (int)$q['quest_id'];
                                        $q_status  = (int)($q['qrec_status'] ?? 0); // 0=進行中, 1=條件達成待領獎, 2=已完成並領獎
                                        $curr_val  = (int)($q['qrec_curr_val'] ?? 0);
                                        $targ_val  = max(1, (int)$q['quest_target_val']);
                                        $unit      = htmlspecialchars($q['quest_unit'] ?: '次');
                                        $is_phys   = !empty($q['has_physical']);
                                        $dig_files = $q['digital_files'] ?? [];

                                        // 卡片外框與徽章樣式依狀態自動切換
                                        if ($q_status === 2) {
                                            $card_cls  = 'border-success bg-light completed';
                                            $top_badge = '<span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>任務已完成</span>';
                                        } elseif ($q_status === 1) {
                                            $card_cls  = 'border-warning bg-white shadow-sm';
                                            $top_badge = '<span class="badge bg-warning text-dark badge-alarm-pulse quest-status-badge"><i class="bi bi-stars me-1"></i>條件已達成 • 待領取</span>';
                                        } else {
                                            $card_cls  = 'border bg-white';
                                            $top_badge = '<span class="badge bg-secondary px-2 py-1"><i class="bi bi-hourglass-split me-1"></i>任務進行中 (' . $curr_val . '/' . $targ_val . $unit . ')</span>';
                                        }
                                    ?>
                                        <div class="border <?php echo $card_cls; ?> rounded-3 p-4 position-relative quest-card" id="questCard_<?php echo $qid; ?>">
                                            <div class="row align-items-center g-3">
                                                <div class="col-md-8">
                                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                        <?php echo $top_badge; ?>
                                                        <span class="badge bg-dark text-warning">
                                                            <i class="bi bi-gift me-1"></i>獎品：<?php echo htmlspecialchars($q['quest_reward_label']); ?>
                                                        </span>
                                                    </div>
                                                    <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($q['quest_title']); ?></h6>
                                                    <p class="text-muted small mb-0">
                                                        <?php echo htmlspecialchars($q['quest_desc']); ?>
                                                        （進度：<?php echo min($curr_val, $targ_val) . '/' . $targ_val . $unit; ?>）
                                                    </p>
                                                </div>

                                                <div class="col-md-4 text-md-end quest-action-box">
                                                    <?php if ($q_status === 2): ?>
                                                        <?php if (!empty($dig_files)): ?>
                                                            <div class="d-flex flex-wrap justify-content-md-end gap-1">
                                                                <?php foreach ($dig_files as $df): ?>
                                                                    <a href="<?php echo htmlspecialchars($df['merch_download_url']); ?>" target="_blank" class="btn btn-outline-success btn-sm fw-bold px-3">
                                                                        <i class="bi bi-cloud-arrow-down-fill me-1"></i>下載 <?php echo htmlspecialchars($df['merch_file_format'] ?: '獎品包'); ?>
                                                                    </a>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php elseif ($is_phys): ?>
                                                            <div class="d-inline-flex align-items-center text-success fw-bold small border border-success rounded-pill px-3 py-1 bg-white">
                                                                <i class="bi bi-truck fs-5 me-1"></i>實物獎品已登記寄送
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="d-inline-flex align-items-center text-success fw-bold small border border-success rounded-pill px-3 py-1 bg-white">
                                                                <i class="bi bi-patch-check-fill fs-5 me-1"></i>獎勵已領取入帳
                                                            </div>
                                                        <?php endif; ?>

                                                    <?php elseif ($q_status === 1): ?>
                                                        <?php if ($is_phys): ?>
                                                            <button type="button"
                                                                    class="btn btn-warning text-dark fw-bold btn-sm px-4 py-2 shadow-sm btn-open-phys-quest"
                                                                    data-quest-id="<?php echo $qid; ?>"
                                                                    data-giftname="<?php echo htmlspecialchars($q['gift_title'], ENT_QUOTES, 'UTF-8'); ?>">
                                                                <i class="bi bi-gift-fill me-1"></i>領取實物獎品
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button"
                                                                    class="btn btn-warning text-dark fw-bold btn-sm px-4 py-2 shadow-sm btn-claim-normal-quest"
                                                                    data-quest-id="<?php echo $qid; ?>">
                                                                <i class="bi bi-gift-fill me-1"></i>立即領取獎勵
                                                            </button>
                                                        <?php endif; ?>

                                                    <?php else: ?>
                                                        <span class="badge bg-light text-muted border px-3 py-2">
                                                            尚未達成目標條件
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div> <!-- 結束分頁 A：tier-gold-pane -->

                        <!-- ============================================================
                             分頁 B：一般會員任務群 (基礎任務與獎勵預覽)
                             ============================================================ -->
                        <div class="tab-pane fade" id="tier-basic-pane" role="tabpanel" tabindex="0">
                            <div class="alert alert-secondary small mb-3">
                                <i class="bi bi-info-circle-fill me-1"></i>此為「一般會員」階段任務群紀錄（您已晉升至黃金會員，以下任務已全數通關並解鎖更高級別獎勵）。
                            </div>
                            <div class="d-flex flex-column gap-3">
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>已通關</span>
                                        <span class="badge bg-secondary">獎品：+50 PTS 點數</span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">新手啟航一：完成首次會員註冊與信箱驗證</h6>
                                    <p class="text-muted small mb-0">建立您的 Fifth Season Life 專屬帳戶並啟用安全通知。</p>
                                </div>
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>已通關</span>
                                        <span class="badge bg-secondary">獎品：入門 UI 色彩指南 PDF 下載</span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">新手啟航二：完成首次幸運大轉盤抽獎體驗</h6>
                                    <p class="text-muted small mb-0">體驗全站互動轉盤並領取首張迎新點券。</p>
                                </div>
                                <div class="p-3 rounded-3 bg-dark text-white small d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-trophy text-warning me-2"></i>一般會員全任務達成大禮：<strong>+100 PTS 紅利點數</strong></span>
                                    <span class="badge bg-success">已發放完畢</span>
                                </div>
                            </div>
                        </div> <!-- 結束分頁 B：tier-basic-pane -->

                        <!-- ============================================================
                             分頁 C：黑鑽領袖任務群 (高階升級預告與豪華實物大獎)
                             ============================================================ -->
                        <div class="tab-pane fade" id="tier-diamond-pane" role="tabpanel" tabindex="0">
                            <div class="alert alert-warning small mb-3 text-dark">
                                <i class="bi bi-crown-fill me-1"></i>累積消費達 <strong>NT$ 50,000</strong> 晉升「黑鑽領袖會員」後自動開啟本任務群（尚差 NT$ 11,500）。
                            </div>
                            <div class="d-flex flex-column gap-3">
                                <div class="border rounded-3 p-3 bg-light opacity-75">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i>未解鎖</span>
                                        <span class="badge bg-warning text-dark">獎品：+500 PTS ＋ 專屬雲端主機折抵券</span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">領袖挑戰一：完成兩項企業級客製化建置專案驗收</h6>
                                    <p class="text-muted small mb-0">與 5SL 技術團隊深度合作打造高轉換商業網站。</p>
                                </div>
                                <div class="border rounded-3 p-3 bg-light opacity-75">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i>未解鎖</span>
                                        <span class="badge bg-danger">獎品：5SL 客製雷雕機械鍵盤 (實物免運寄送)</span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">領袖挑戰二：推薦兩位企業夥伴加入年度 VIP 方案</h6>
                                    <p class="text-muted small mb-0">透過專屬推薦碼邀請好友共同成長。</p>
                                </div>
                                <div class="p-3 rounded-3 bg-dark text-white small d-flex justify-content-between align-items-center border border-warning">
                                    <span><i class="bi bi-gem text-warning me-2"></i>黑鑽全任務達成大禮：<strong>2,000 PTS ＋ 年度閉門晚宴 VIP 席次</strong></span>
                                    <span class="badge bg-secondary">晉升後解鎖</span>
                                </div>
                            </div>
                        </div> <!-- 結束分頁 C：tier-diamond-pane -->

                    </div> <!-- 結束 tab-content -->

                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- ==========================================================================
     彈窗：實物獎品收件地址確認表單
     ========================================================================== -->
<div class="modal fade" id="physicalGiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-box2-heart me-2 text-warning"></i>實物獎品寄送資訊確認</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formClaimPhysicalGift">
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded border mb-3 small">
                        <div class="text-muted">領取獎品項目：</div>
                        <div class="fw-bold text-dark fs-6" id="modalPhysicalGiftName">5SL 限量黑金精裝筆記本禮盒</div>
                    </div>
                    <input type="hidden" id="hiddenQuestTarget" value="">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">收件人真實姓名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inputReceiverName" value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : '第五季尊榮會員'; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">聯絡行動電話 <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="inputReceiverPhone" placeholder="例如：0912-345-678" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">台灣本島/離島收件地址 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inputReceiverAddress" placeholder="請輸入郵遞區號與完整收件地址" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold btn-sm px-4">確認地址並申請寄出</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    body {
        background-image: url("imageFiles/mainBG-3.jpg");
        background-attachment: fixed;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    #tierQuestTab .nav-link {
        color: #495057;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
    }
    #tierQuestTab .nav-link:hover {
        background-color: #e9ecef;
        color: #212529;
    }
    #tierQuestTab .nav-link.active {
        background-color: #212529 !important;
        color: #ffc107 !important;
        border-color: #212529 !important;
    }
</style>

<?php include_once '5sl_include_footer.php'; ?>

<script>
$(function() {
    let completedQuests = 2;
    const totalQuests = 3;

    // 1. 通用提示框控制
    function showQuestsAlert(type, msg) {
        const $box = $('#questsAlertBox');
        const $icon = $('#questsAlertIcon');
        $box.removeClass('d-none alert-success alert-danger').addClass('d-flex');
        $icon.removeClass('bi-check-circle-fill bi-exclamation-triangle-fill');

        if (type === 'success') {
            $box.addClass('alert-success');
            $icon.addClass('bi-check-circle-fill');
        } else {
            $box.addClass('alert-danger');
            $icon.addClass('bi-exclamation-triangle-fill');
        }
        $('#questsAlertMsg').text(msg);
        $('html, body').animate({ scrollTop: $box.offset().top - 120 }, 300);
    }

    // 2. 更新任務進度條並自動解鎖終極「任務達成大禮」（改為接收後端回傳的真實數量）
    function updateQuestProgress(doneCnt, totalCnt) {
        const percent = (totalCnt > 0) ? Math.round((doneCnt / totalCnt) * 100) : 0;
        $('#questCompletedCount').text(doneCnt);
        $('#questTotalCount').text(totalCnt);
        $('#questProgressPercent').text(percent + '%');
        $('#questMainProgressBar').css('width', percent + '%').attr('aria-valuenow', percent);

        if (totalCnt > 0 && doneCnt >= totalCnt) {
            $('#grandPrizeIconBox').html('<i class="bi bi-gift-fill display-4 text-warning" style="animation: fslPulse 1s infinite alternate;"></i>');
            $('#grandPrizeStatusTitle').text('終極達成大禮已解鎖！').addClass('text-warning');
            $('#grandPrizeStatusDesc').text('恭喜完成全部本階段任務，立即領取終極大獎');
            $('#btnClaimGrandPrize')
                .prop('disabled', false)
                .removeClass('btn-secondary')
                .addClass('btn-warning text-dark badge-alarm-pulse')
                .html('<i class="bi bi-stars me-1"></i>立即領取任務達成大禮');
        }
    }

    // 4-A. 開啟實物獎品收件資訊彈窗（點擊「領取實物獎品」）
    $(document).on('click', '.btn-open-phys-quest', function() {
        const questId  = $(this).attr('data-quest-id');
        const giftName = $(this).attr('data-giftname');
        $('#hiddenQuestTarget').val(questId);
        $('#modalPhysicalGiftName').text(giftName);

        const physModal = new bootstrap.Modal(document.getElementById('physicalGiftModal'));
        physModal.show();
    });

    // 4-B. 直接領取一般任務獎勵（純點數或數位檔案下載包）
    $(document).on('click', '.btn-claim-normal-quest', function() {
        const $btn    = $(this);
        const questId = $btn.attr('data-quest-id');

        if (window.fslLoader) window.fslLoader.show();

        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_claim_quest',
            quest_id: questId
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();

            if (res.success === 1) {
                const $card = $(`#questCard_${questId}`);
                $card.removeClass('border-warning bg-white shadow-sm').addClass('border-success bg-light completed');
                $card.find('.quest-status-badge').removeClass('bg-warning text-dark badge-alarm-pulse').addClass('bg-success text-white').html('<i class="bi bi-check-circle-fill me-1"></i>任務已完成');

                // 若內含數位下載檔案，動態切換為下載按鈕；否則顯示已入帳徽章
                if (res.digital_files && res.digital_files.length > 0) {
                    let dlHtml = '<div class="d-flex flex-wrap justify-content-md-end gap-1">';
                    res.digital_files.forEach(function(df) {
                        const fmt = df.format || '獎品包';
                        dlHtml += `<a href="${df.url}" target="_blank" class="btn btn-outline-success btn-sm fw-bold px-3"><i class="bi bi-cloud-arrow-down-fill me-1"></i>下載 ${fmt}</a>`;
                    });
                    dlHtml += '</div>';
                    $card.find('.quest-action-box').html(dlHtml);
                } else {
                    $card.find('.quest-action-box').html(`
                        <div class="d-inline-flex align-items-center text-success fw-bold small border border-success rounded-pill px-3 py-1 bg-white">
                            <i class="bi bi-patch-check-fill fs-5 me-1"></i>獎勵已領取入帳
                        </div>`);
                }

                updateQuestProgress(res.done_count, res.total_count);
                showQuestsAlert('success', res.message);
            } else {
                showQuestsAlert('danger', res.message || '領取失敗，請稍後再試。');
            }
        }, 'json').fail(function() {
            if (window.fslLoader) window.fslLoader.hide();
            showQuestsAlert('danger', '網路連線異常，請稍後再試。');
        });
    });

    // 5. 送出實物獎品寄送地址並標記任務完成 (串接 act_claim_quest)
    $('#formClaimPhysicalGift').on('submit', function(e) {
        e.preventDefault();
        const questId  = $('#hiddenQuestTarget').val();
        const giftName = $('#modalPhysicalGiftName').text();
        const recvName = $('#inputReceiverName').val().trim();
        const recvTel  = $('#inputReceiverPhone').val().trim();
        const recvAddr = $('#inputReceiverAddress').val().trim();

        bootstrap.Modal.getInstance(document.getElementById('physicalGiftModal')).hide();
        if (window.fslLoader) window.fslLoader.show();

        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_claim_quest',
            quest_id: questId,
            receiver_name: recvName,
            receiver_phone: recvTel,
            receiver_address: recvAddr
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();

            if (res.success === 1) {
                const $card = $(`#questCard_${questId}`);
                $card.removeClass('border-warning bg-white shadow-sm').addClass('border-success bg-light completed');
                $card.find('.quest-status-badge').removeClass('bg-warning text-dark badge-alarm-pulse').addClass('bg-success text-white').html('<i class="bi bi-check-circle-fill me-1"></i>任務已完成');
                $card.find('.quest-action-box').html(`
                    <div class="d-inline-flex align-items-center text-success fw-bold small border border-success rounded-pill px-3 py-1 bg-white">
                        <i class="bi bi-truck fs-5 me-1"></i>實物獎品已登記寄送
                    </div>`);

                updateQuestProgress(res.done_count, res.total_count);
                showQuestsAlert('success', `實物獎品「${giftName}」已登記寄往（${recvAddr}），進度已同步更新！`);
            } else {
                showQuestsAlert('danger', res.message || '登記失敗，請稍後再試。');
            }
        }, 'json').fail(function() {
            if (window.fslLoader) window.fslLoader.hide();
            showQuestsAlert('danger', '網路連線異常，請稍後再試。');
        });
    });

    // 6. 領取終極「任務達成大禮」(串接 act_claim_quest + 全站 3D 特效彈窗)
    $('#btnClaimGrandPrize').on('click', function() {
        const $btn      = $(this);
        const questId   = $btn.attr('data-quest-id');
        const giftTitle = $btn.attr('data-gift-title') || '季度達成大禮包';
        const pts       = $btn.attr('data-pts') || '500';

        if (!questId || questId === '0') return;
        if (window.fslLoader) window.fslLoader.show();

        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_claim_quest',
            quest_id: questId
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();

            if (res.success === 1) {
                $btn.prop('disabled', true)
                    .removeClass('btn-warning text-dark badge-alarm-pulse')
                    .addClass('btn-success text-white')
                    .html('<i class="bi bi-patch-check-fill me-1"></i>季度達成大禮已全數領取');
                $('#grandPrizeStatusTitle').text('季度任務全數制霸！');
                $('#grandPrizeStatusDesc').text('終極達成大禮包已成功領取歸戶');
                $('#grandPrizeIconBox').html('<i class="bi bi-patch-check-fill display-4 text-success"></i>');

                if (window.fslEpicEngine) {
                    window.fslEpicEngine.trigger({
                        contentType: 'html',
                        contentSource: window.fslEpicEngine.createCouponTemplate({
                            bgUrl: 'https://picsum.photos' + Math.floor(Math.random() * 1000),
                            points: pts.toString(),
                            unit: res.has_physical ? 'PTS + 實體禮盒' : 'PTS',
                            title: 'ALL QUESTS CLEARED!',
                            subTitle: `恭喜達成全部任務，獲得「${giftTitle}」`,
                            label: '★ 點數已即時入帳，實體好禮將盡快為您安排寄出 ★'
                        }),
                        theme: 'celebration',
                        onConfirm: function(close) {
                            close();
                        }
                    });
                }
                showQuestsAlert('success', res.message);
            } else {
                showQuestsAlert('danger', res.message || '領取終極大禮失敗，請稍後再試。');
            }
        }, 'json').fail(function() {
            if (window.fslLoader) window.fslLoader.hide();
            showQuestsAlert('danger', '網路連線異常，請稍後再試。');
        });
    });

});
</script>
