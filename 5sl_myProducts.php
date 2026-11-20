<?php
// 宣告當前頁面身分為 myProducts（課程與服務）
$current_page = 'myProducts';

// 引入共用頁首
include_once '5sl_include_header.php';

$mb_id  = (int)($_SESSION['mbID'] ?? $_SESSION['user_id'] ?? 0);
$mb_lvl = (int)($_SESSION['mbLevel'] ?? 1);

// 1. 查詢會員所有報名課程與梯次紀錄 (fsl_course_enrolls + fsl_courses + fsl_course_sessions)
$sql_enrolls = "
    SELECT 
        e.enr_id, e.enr_quota_type, e.enr_pay_status, e.enr_status, e.enr_done_lessons, e.enr_last_watched, e.enr_expire_at, e.enr_topic,
        c.course_id, c.course_title, c.course_type,
        s.sess_id, s.sess_batch, s.sess_altname, s.sess_startdate, s.sess_starttime, s.sess_duration, s.sess_lesson, s.sess_meetingid
    FROM fsl_course_enrolls e
    INNER JOIN fsl_courses c ON e.course_id = c.course_id
    INNER JOIN fsl_course_sessions s ON e.sess_id = s.sess_id
    WHERE e.mb_id = ? AND e.enr_status != 0
    ORDER BY e.enr_pay_status DESC, s.sess_startdate DESC, s.sess_starttime DESC
";
$all_enrolls = ($mb_id > 0) ? $DB->query($sql_enrolls, [$mb_id]) : [];
if (!is_array($all_enrolls)) $all_enrolls = [];

// 拆分為「一般線上/實體課程 (course_type=1)」與「一對一顧問預約 (course_type=2)」
// 同時收集已付款報名的課程 ID，供下方限定素材資源權限比對
$my_courses          = [];
$my_consults         = [];
$enrolled_course_ids = [];
$total_pct_sum       = 0;

foreach ($all_enrolls as $row) {
    if (in_array((int)$row['enr_pay_status'], array(1, 2), true)) {
        $enrolled_course_ids[(int)$row['course_id']] = true;
    }

    if ((int)$row['course_type'] === 2) {
        $my_consults[] = $row;
    } else {
        $total_lessons = max(1, (int)$row['sess_lesson']);
        $done_lessons  = min($total_lessons, (int)$row['enr_done_lessons']);
        $row['progress_pct'] = (int)round(($done_lessons / $total_lessons) * 100);
        $total_pct_sum += $row['progress_pct'];
        $my_courses[] = $row;
    }
}
$avg_progress = !empty($my_courses) ? (int)round($total_pct_sum / count($my_courses)) : 0;

// 2. 預撈一對一顧問服務「目前開放可改期的未來空檔時段」(fsl_course_sessions)
$open_slots_by_course = [];
$slot_rows = $DB->query("
    SELECT s.sess_id, s.course_id, s.sess_startdate, s.sess_starttime, s.sess_duration, s.sess_altname
    FROM fsl_course_sessions s
    INNER JOIN fsl_courses c ON s.course_id = c.course_id
    WHERE c.course_type = 2
      AND s.sess_status = 1
      AND s.sess_enrollee < c.course_max
      AND CONCAT(s.sess_startdate, ' ', s.sess_starttime) > NOW()
    ORDER BY s.sess_startdate ASC, s.sess_starttime ASC
");
$week_chars = ['日', '一', '二', '三', '四', '五', '六'];
if (is_array($slot_rows)) {
    foreach ($slot_rows as $sr) {
        $ts1 = strtotime($sr['sess_startdate'] . ' ' . $sr['sess_starttime']);
        $ts2 = $ts1 + ((int)$sr['sess_duration'] * 60);
        $label = date('Y-m-d', $ts1) . ' (' . $week_chars[(int)date('w', $ts1)] . ') ' . date('H:i', $ts1) . ' - ' . date('H:i', $ts2);
        if (!empty($sr['sess_altname'])) {
            $label .= '｜' . $sr['sess_altname'];
        }
        $open_slots_by_course[(int)$sr['course_id']][] = [
            'sess_id' => (int)$sr['sess_id'],
            'label'   => $label
        ];
    }
}

// 查詢會員剩餘免費一對一輔導次數
$free_consult_quota = ($mb_id > 0)
    ? (int)$DB->single("SELECT mb_free_consult_quota FROM fsl_members WHERE mb_id = ?", [$mb_id])
    : 0;

// 查詢所有開放預約的一對一顧問服務項目 (course_type = 2)
$consult_services = $DB->query("
    SELECT course_id, course_title, course_brief, course_cost, course_max
    FROM fsl_courses
    WHERE course_type = 2 AND course_status = 1
    ORDER BY course_id ASC
");
if (!is_array($consult_services)) $consult_services = [];

// 3. 查詢「限定內容專區」課程素材資源 (fsl_resources + fsl_resource_access，取代舊版 articles)
$sql_resources = "
    SELECT r.*,
           c.course_title AS rel_course_title,
           ra.racc_id, ra.racc_used_count, ra.racc_max_count, ra.racc_expire_at, ra.racc_rated, ra.racc_status
    FROM fsl_resources r
    LEFT JOIN fsl_courses c ON r.course_id = c.course_id
    LEFT JOIN fsl_resource_access ra ON r.res_id = ra.res_id AND ra.mb_id = ?
    WHERE r.res_status = 1
    ORDER BY r.res_sort ASC, r.res_id DESC
";
$raw_resources = ($mb_id > 0) ? $DB->query($sql_resources, [$mb_id]) : [];
if (!is_array($raw_resources)) $raw_resources = [];

$resource_list = [];
$cat_map_res   = [1 => 'article', 2 => 'image', 3 => 'file', 4 => 'video', 5 => 'audio'];

foreach ($raw_resources as $rr) {
    $rid       = (int)$rr['res_id'];
    $rtype     = (int)$rr['res_type']; // 1文字, 2圖檔, 3文書, 4YouTube, 5音檔
    $auth_rule = (int)$rr['res_auth_rule'];
    $has_acc   = !empty($rr['racc_id']) && (int)$rr['racc_status'] === 1;

    // 檢核是否符合免費開通資格
    $can_free_unlock = $has_acc;
    if (!$can_free_unlock) {
        $allow_ids = array_filter(array_map('intval', explode(',', (string)($rr['res_allow_mbids'] ?? ''))));
        if (!empty($allow_ids) && in_array($mb_id, $allow_ids, true)) {
            $can_free_unlock = true;
        } elseif ($auth_rule === 0) {
            $can_free_unlock = true;
        } elseif ($auth_rule === 2 && isset($enrolled_course_ids[(int)$rr['course_id']])) {
            $can_free_unlock = true;
        } elseif ($auth_rule === 3 && $mb_lvl >= (int)$rr['res_min_level']) {
            $can_free_unlock = true;
        }
    }

    // 封面縮圖路徑（優先讀取 ./5sladmin/uploadFiles/re/reimg_{id}_thumb.jpg）
    $cover_url = './5sladmin/uploadFiles/re/reimg_' . $rid . '_thumb.jpg';
    if (!file_exists($cover_url)) {
        if ($rtype === 4 && preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', (string)$rr['res_media_url'], $ym)) {
            $yt_id     = $ym[1];
            $cover_url = 'https://i.ytimg.com/vi/' . $yt_id . '/hqdefault.jpg';
        } else {
            $cover_url = 'imageFiles/test/' . (($rid % 18) + 1) . '.jpg';
        }
    } else {
        $cover_url .= '?v=' . filemtime($cover_url);
    }

    $rr['cat_code']        = $cat_map_res[$rtype] ?? 'file';
    $rr['cover_url']       = $cover_url;
    $rr['can_free_unlock'] = $can_free_unlock;
    $rr['has_acc']         = $has_acc;
    $resource_list[]       = $rr;
}
?>

<div class="container my-5 py-4">
    <div class="row g-4 mt-2">
        
        <!-- 左側：會員中心共用子導航列 -->
        <div class="col-md-4 col-lg-3">
            <?php include_once '5sl_include_subnavi.php'; ?>
        </div>

        <!-- 右側：課程與服務核心工作視窗 -->
        <div class="col-md-8 col-lg-9">
            <div class="card border-0 shadow-sm p-4 p-md-5 bg-white bg-opacity-75 rounded-3 h-100" style="min-height: 600px;">
                <div class="card-body p-0">
                    
                    <!-- 頁面主標題 -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 mb-4">
                        <div>
                            <h3 class="fw-bold text-dark mb-1">
                                <i class="bi bi-journal-bookmark-fill me-2 text-warning"></i>課程與服務 (Courses & Services)
                            </h3>
                            <p class="text-muted small mb-0">存取您的專屬限定內容、追蹤線上學習與預約進度，並管理您的訂閱方案。</p>
                        </div>
                        <div class="mt-3 mt-md-0">
                            <span class="badge bg-warning text-dark fw-bold px-3 py-2 shadow-sm">
                                <i class="bi bi-gem me-1"></i>VIP 尊榮訂閱會員
                            </span>
                        </div>
                    </div>

                    <!-- 【補足模組 A】：有效權益總覽儀表板 -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 bg-dark text-white border-start border-4 border-warning h-100">
                                <div class="text-white-50 small mb-1">目前訂閱方案</div>
                                <div class="fw-bold fs-5 text-warning">年度尊榮全通方案</div>
                                <div class="small text-white-50 mt-1" style="font-size: 0.75rem;">下次扣款：2027/05/15</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 bg-light border h-100">
                                <div class="text-muted small mb-1">線上課程 / 服務</div>
                                <div class="fw-bold fs-5 text-dark"><?php echo count($my_courses); ?> 門課程 <span class="fs-6 fw-normal text-muted">/ <?php echo count($my_consults); ?> 筆預約</span></div>
                                <div class="small text-success mt-1" style="font-size: 0.75rem;"><i class="bi bi-graph-up-arrow me-1"></i>平均完成度 <?php echo $avg_progress; ?>%</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 bg-light border h-100">
                                <div class="text-muted small mb-1">限定內容庫權限</div>
                                <div class="fw-bold fs-5 text-dark">已解鎖全站資源</div>
                                <div class="small text-primary mt-1" style="font-size: 0.75rem;"><i class="bi bi-unlock-fill me-1"></i>影音 / 音訊 / 原始碼</div>
                            </div>
                        </div>
                    </div>

                    <!-- 三大核心功能分頁導航 (Nav Pills) -->
                    <ul class="nav nav-pills mb-4 gap-2" id="productsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold px-3 px-md-4" id="progress-tab" data-bs-toggle="pill" data-bs-target="#progress-pane" type="button" role="tab">
                                <i class="bi bi-bar-chart-steps me-1"></i>課程與服務進度
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-3 px-md-4" id="exclusive-tab" data-bs-toggle="pill" data-bs-target="#exclusive-pane" type="button" role="tab">
                                <i class="bi bi-stars me-1"></i>限定內容專區
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-3 px-md-4" id="subscription-tab" data-bs-toggle="pill" data-bs-target="#subscription-pane" type="button" role="tab">
                                <i class="bi bi-credit-card-2-front me-1"></i>訂閱與帳務管理
                            </button>
                        </li>
                    </ul>

                    <!-- 操作結果提示框 -->
                    <div id="productsAlertBox" class="alert d-none align-items-center mb-4" role="alert">
                        <i id="productsAlertIcon" class="bi me-2 fs-5"></i>
                        <div id="productsAlertMsg" class="small fw-bold"></div>
                    </div>

                    <!-- 分頁內容主體 -->
                    <div class="tab-content" id="productsTabContent">

                        <!-- ============================================================
                             區塊 1：線上課程 / 服務進度 (學習進度、到期日、預約記錄)
                             ============================================================ -->
                        <div class="tab-pane fade show active" id="progress-pane" role="tabpanel" tabindex="0">
                            
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-play-btn-fill me-2 text-primary"></i>已購線上課程進度</h6>
                            <div class="row g-3 mb-4">
                                <?php if (empty($my_courses)): ?>
                                    <div class="col-12">
                                        <div class="p-4 text-center bg-light rounded-3 border text-muted small">
                                            目前尚無已報名的課程紀錄，歡迎前往課程總覽探索適合您的陪跑系統！
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($my_courses as $mc):
                                        $pct      = $mc['progress_pct'];
                                        $is_paid  = in_array((int)$mc['enr_pay_status'], [1, 2], true);
                                        $exp_text = !empty($mc['enr_expire_at']) ? '到期日：' . date('Y-m-d', strtotime($mc['enr_expire_at'])) : '永久觀看權限';
                                        $last_txt = !empty($mc['enr_last_watched']) ? '上次觀看：' . date('m/d H:i', strtotime($mc['enr_last_watched'])) : '尚未開始觀看';
                                        $next_unit = min((int)$mc['sess_lesson'], (int)$mc['enr_done_lessons'] + 1);
                                    ?>
                                        <div class="col-md-6">
                                            <div class="card border shadow-none h-100 hover-card">
                                                <div class="card-body d-flex flex-column justify-content-between">
                                                    <div>
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <?php if (!$is_paid): ?>
                                                                <span class="badge bg-danger">待完成付款</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-primary-subtle text-primary"><?php echo $exp_text; ?></span>
                                                            <?php endif; ?>
                                                            <small class="text-muted">第 <?php echo (int)$mc['sess_batch']; ?> 梯 • <?php echo $last_txt; ?></small>
                                                        </div>
                                                        <h6 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($mc['course_title']); ?></h6>
                                                        <div class="d-flex justify-content-between small text-muted mb-1">
                                                            <span>學習進度 (<?php echo (int)$mc['enr_done_lessons']; ?> / <?php echo (int)$mc['sess_lesson']; ?> 單元)</span>
                                                            <span class="fw-bold text-dark"><?php echo $pct; ?>%</span>
                                                        </div>
                                                        <div class="progress mb-3" style="height: 8px;">
                                                            <div class="progress-bar <?php echo ($pct >= 100) ? 'bg-success' : 'bg-warning'; ?>" role="progressbar" style="width: <?php echo $pct; ?>%;"></div>
                                                        </div>
                                                    </div>

                                                    <?php if (!$is_paid): ?>
                                                        <a href="5sl_myOrders.php" class="btn btn-outline-danger btn-sm w-100 fw-bold">
                                                            <i class="bi bi-credit-card me-1"></i>前往訂單完成付款
                                                        </a>
                                                    <?php else: ?>
                                                        <button type="button"
                                                                class="btn btn-dark btn-sm w-100 fw-bold btn-open-media"
                                                                data-title="<?php echo htmlspecialchars($mc['course_title'] . " - 第 {$next_unit} 單元", ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-type="video"
                                                                data-meeting="<?php echo htmlspecialchars($mc['sess_meetingid'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                            <i class="bi bi-play-circle me-1 text-warning"></i>繼續上課
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- 醒目一對一顧問輔導預約行動橫幅 -->
                            <div class="p-4 rounded-3 text-white mb-4 shadow-sm" style="background: linear-gradient(135deg, #16181b 0%, #2b3035 100%); border-left: 5px solid #ffc107;">
                                <div class="row align-items-center g-3">
                                    <div class="col-md-8">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-stars me-1"></i>1-ON-1 MENTORSHIP</span>
                                            <?php if ($free_consult_quota > 0): ?>
                                                <span class="badge bg-success"><i class="bi bi-ticket-detailed me-1"></i>您目前擁有 <?php echo $free_consult_quota; ?> 次免費顧問輔導額度</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">免費額度已用畢（可單次付費加購預約）</span>
                                            <?php endif; ?>
                                        </div>
                                        <h5 class="fw-bold text-white mb-1">預約一對一專屬顧問深度輔導</h5>
                                        <p class="text-white-50 small mb-0">
                                            選擇適合您的顧問主題與開放時段；可使用 VIP 贈送之免費次數直接扣抵預約，或單次加購深度陪談。
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <button type="button" class="btn btn-warning text-dark fw-bold px-4 py-2 shadow-sm w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#bookConsultModal">
                                            <i class="bi bi-calendar-plus-fill me-1"></i>立即預約一對一顧問輔導
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 專屬顧問 / 服務預約記錄 -->
                            <h6 class="fw-bold text-dark mb-3 pt-2 border-top"><i class="bi bi-calendar2-check-fill me-2 text-success"></i>一對一顧問輔導預約記錄</h6>
                            <div class="table-responsive border rounded-3">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>預約項目</th>
                                            <th>預約時段</th>
                                            <th>進行方式</th>
                                            <th>狀態</th>
                                            <th class="text-end">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($my_consults)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">目前尚無一對一顧問預約紀錄。</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($my_consults as $cs):
                                                $enr_id   = (int)$cs['enr_id'];
                                                $cid      = (int)$cs['course_id'];
                                                $st_num   = (int)$cs['enr_status']; // 1待開課, 2進行中, 3已完課, 4改期中
                                                $ts1      = strtotime($cs['sess_startdate'] . ' ' . $cs['sess_starttime']);
                                                $ts2      = $ts1 + ((int)$cs['sess_duration'] * 60);
                                                $time_str = date('Y-m-d', $ts1) . ' (' . $week_chars[(int)date('w', $ts1)] . ') ' . date('H:i', $ts1) . ' - ' . date('H:i', $ts2);
                                                $is_future = ($ts1 > time()) && in_array($st_num, [1, 2, 4], true);
                                            ?>
                                                <tr id="consultRow_<?php echo $enr_id; ?>">
                                                    <td class="fw-bold <?php echo $is_future ? 'text-dark' : 'text-muted'; ?>">
                                                        <?php echo htmlspecialchars($cs['course_title']); ?>
                                                    </td>
                                                    <td class="consult-time-cell <?php echo $is_future ? 'text-dark' : 'text-muted'; ?>">
                                                        <?php echo $time_str; ?>
                                                    </td>
                                                    <td><span class="badge bg-info-subtle text-info">線上會議室</span></td>
                                                    <td>
                                                        <?php if ($st_num === 3 || !$is_future): ?>
                                                            <span class="badge bg-secondary">已完成服務</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">已確認預約</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <?php if ($is_future): ?>
                                                            <button type="button"
                                                                    class="btn btn-outline-dark btn-sm py-0 px-2 fw-bold btn-open-reschedule"
                                                                    data-enr-id="<?php echo $enr_id; ?>"
                                                                    data-course-id="<?php echo $cid; ?>"
                                                                    data-service="<?php echo htmlspecialchars($cs['course_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-curr-time="<?php echo $time_str; ?>">
                                                                改期
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button"
                                                                    class="btn btn-link btn-sm p-0 text-decoration-none btn-open-media"
                                                                    data-title="<?php echo htmlspecialchars($cs['course_title'] . ' - 會議紀要與錄影回放', ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-type="video">
                                                                查看紀要
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>

                                </table>
                            </div>
                        </div> <!-- 結束區塊 1：progress-pane -->

                        <!-- ============================================================
                             區塊 2：限定內容專區 (文章、影片、音訊、檔案下載)
                             ============================================================ -->
                        <div class="tab-pane fade" id="exclusive-pane" role="tabpanel" tabindex="0">
                            <!-- 資源類型快速篩選列 -->
                            <div class="d-flex flex-wrap gap-2 mb-4" id="exclusiveFilterGroup">
                                <button type="button" class="btn btn-sm btn-dark active" data-filter="all">全部素材 (<?php echo count($resource_list); ?>)</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="video"><i class="bi bi-camera-video me-1"></i>YouTube 視訊</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="audio"><i class="bi bi-headphones me-1"></i>私房音檔</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="file"><i class="bi bi-file-earmark-pdf me-1"></i>文書簡報檔案</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="article"><i class="bi bi-file-earmark-richtext me-1"></i>文字專文</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="image"><i class="bi bi-image me-1"></i>圖檔教材</button>
                            </div>

                            <!-- 素材卡片網格 -->
                            <div class="row g-3" id="exclusiveResourceGrid">
                                <?php if (empty($resource_list)): ?>
                                    <div class="col-12">
                                        <div class="p-4 text-center bg-light rounded-3 border text-muted small">
                                            目前尚無已上架的課程素材資源，敬請期待更新！
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($resource_list as $res):
                                        $rid        = (int)$res['res_id'];
                                        $rtype      = (int)$res['res_type'];
                                        $can_unlock = $res['can_free_unlock'];
                                        $cost_pts   = (int)$res['res_cost_pts'];
                                        $usage_mode = (int)$res['res_usage_mode'];

                                        $type_badges = [
                                            1 => ['label' => '文字專文', 'cls' => 'bg-success',          'icon' => 'bi-file-earmark-text-fill'],
                                            2 => ['label' => '圖檔教材', 'cls' => 'bg-primary',          'icon' => 'bi-image-fill'],
                                            3 => ['label' => '文書簡報', 'cls' => 'bg-warning text-dark','icon' => 'bi-file-earmark-pdf-fill'],
                                            4 => ['label' => 'YouTube 視訊', 'cls' => 'bg-danger',       'icon' => 'bi-youtube'],
                                            5 => ['label' => '私房音檔', 'cls' => 'bg-info text-dark',   'icon' => 'bi-headphones'],
                                        ];
                                        $tb = $type_badges[$rtype] ?? $type_badges;

                                        // 使用模式提示文字
                                        if ($res['has_acc'] && (int)$res['racc_max_count'] > 0) {
                                            $rem = max(0, (int)$res['racc_max_count'] - (int)$res['racc_used_count']);
                                            $mode_txt = "剩餘 {$rem} / {$res['racc_max_count']} 次";
                                        } elseif ($res['has_acc'] && !empty($res['racc_expire_at'])) {
                                            $mode_txt = '效期至 ' . date('Y-m-d', strtotime($res['racc_expire_at']));
                                        } elseif ($usage_mode === 2) {
                                            $mode_txt = "計次制 (限 {$res['res_usage_limit']} 次)";
                                        } elseif ($usage_mode === 3) {
                                            $mode_txt = "計日制 ({$res['res_valid_days']} 天)";
                                        } else {
                                            $mode_txt = '永久無限次';
                                        }
                                    ?>
                                        <div class="col-md-6 exclusive-item" data-category="<?php echo $res['cat_code']; ?>">
                                            <div class="card border h-100 shadow-none overflow-hidden bg-light">
                                                <div class="position-relative">
                                                    <img src="<?php echo htmlspecialchars($res['cover_url']); ?>" class="card-img-top" style="height: 150px; object-fit: cover;" alt="<?php echo htmlspecialchars($res['res_title']); ?>">
                                                    <span class="badge <?php echo $tb['cls']; ?> position-absolute top-0 start-0 m-2 shadow-sm">
                                                        <i class="bi <?php echo $tb['icon']; ?> me-1"></i><?php echo $tb['label']; ?>
                                                    </span>
                                                    <span class="badge bg-dark text-warning position-absolute top-0 end-0 m-2">
                                                        <?php echo $mode_txt; ?>
                                                    </span>
                                                </div>

                                                <div class="card-body d-flex flex-column justify-content-between p-3">
                                                    <div>
                                                        <?php if (!empty($res['res_group_title'])): ?>
                                                            <div class="text-primary fw-bold mb-1" style="font-size: 0.72rem;">
                                                                <i class="bi bi-folder2-open me-1"></i><?php echo htmlspecialchars($res['res_group_title']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($res['res_title']); ?></h6>
                                                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($res['res_brief'] ?? ''); ?></p>

                                                        <!-- 統計列：瀏覽次數 + 好評讚數 -->
                                                        <div class="d-flex align-items-center gap-3 text-muted mb-3" style="font-size: 0.75rem;">
                                                            <span><i class="bi bi-eye me-1"></i><?php echo number_format((int)$res['res_count']); ?> 次使用</span>
                                                            <span class="text-success"><i class="bi bi-hand-thumbs-up-fill me-1"></i><span id="resTopCount_<?php echo $rid; ?>"><?php echo number_format((int)$res['res_ratetop']); ?></span></span>
                                                        </div>
                                                    </div>

                                                    <!-- 操作按鈕區（依權限自動顯示預覽、下載或 PTS 點數兌換） -->
                                                    <div class="d-flex gap-2 mt-auto">
                                                        <?php if ($can_unlock): ?>
                                                            <?php if ((int)$res['res_can_preview'] === 1): ?>
                                                                <button type="button"
                                                                        class="btn btn-dark text-warning btn-sm flex-grow-1 fw-bold btn-use-resource"
                                                                        data-res-id="<?php echo $rid; ?>"
                                                                        data-mode="preview">
                                                                    <i class="bi bi-play-circle me-1"></i>線上觀看 / 閱讀
                                                                </button>
                                                            <?php endif; ?>
                                                            <?php if ((int)$res['res_can_download'] === 1): ?>
                                                                <button type="button"
                                                                        class="btn btn-warning text-dark btn-sm <?php echo ((int)$res['res_can_preview'] === 1) ? '' : 'flex-grow-1'; ?> fw-bold btn-use-resource"
                                                                        data-res-id="<?php echo $rid; ?>"
                                                                        data-mode="download">
                                                                    <i class="bi bi-download me-1"></i>下載檔案
                                                                </button>
                                                            <?php endif; ?>
                                                        <?php elseif ($cost_pts > 0): ?>
                                                            <button type="button"
                                                                    class="btn btn-warning text-dark btn-sm w-100 fw-bold btn-redeem-resource"
                                                                    data-res-id="<?php echo $rid; ?>"
                                                                    data-pts="<?php echo $cost_pts; ?>"
                                                                    data-title="<?php echo htmlspecialchars($res['res_title'], ENT_QUOTES, 'UTF-8'); ?>">
                                                                <i class="bi bi-coin me-1"></i>使用 <?php echo number_format($cost_pts); ?> PTS 兌換解鎖
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-outline-secondary btn-sm w-100" disabled>
                                                                <i class="bi bi-lock-fill me-1"></i>
                                                                <?php echo ((int)$res['res_auth_rule'] === 2) ? '報名指定課程後自動解鎖' : '需升級更高 VIP 等級解鎖'; ?>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- ============================================================
                             區塊 3：訂閱管理 (續費、變更方案、取消訂閱、更新信用卡)
                             ============================================================ -->
                        <div class="tab-pane fade" id="subscription-pane" role="tabpanel" tabindex="0">
                            <div class="row g-4">
                                
                                <!-- 左側：目前訂閱方案與操作按鈕 -->
                                <div class="col-lg-7">
                                    <div class="p-4 border rounded-3 bg-light h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="badge bg-success px-3 py-2" id="subStatusBadge"><i class="bi bi-check-circle-fill me-1"></i>自動續訂啟用中</span>
                                            <span class="text-muted small">方案編號：#SUB-20260515</span>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1">年度尊榮全通方案 (Annual VIP)</h5>
                                        <p class="text-muted small mb-3">包含全站限定影音、原始碼下載庫及每季一次一對一架構顧問諮詢。</p>
                                        
                                        <div class="bg-white p-3 rounded border mb-4 small">
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">計費週期與金額</span>
                                                <span class="fw-bold text-dark">NT$ 29,900 / 年 (已省下 NT$ 5,980)</span>
                                            </div>
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">本期有效期間</span>
                                                <span class="fw-bold text-dark">2026/05/15 - 2027/05/14</span>
                                            </div>
                                            <div class="d-flex justify-content-between pt-1">
                                                <span class="text-muted">下次自動扣款日</span>
                                                <span class="fw-bold text-primary" id="nextBillingDateText">2027/05/15</span>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button" class="btn btn-dark text-warning fw-bold btn-sm px-3" data-bs-toggle="modal" data-bs-target="#changePlanModal">
                                                <i class="bi bi-arrow-left-right me-1"></i>變更訂閱方案
                                            </button>
                                            <button type="button" class="btn btn-outline-primary fw-bold btn-sm px-3" id="btnManualRenew">
                                                <i class="bi bi-lightning-charge-fill me-1"></i>立即手動續費一年
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm px-3 ms-auto" id="btnToggleCancelSub">
                                                取消自動續訂
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- 右側：自動扣款信用卡資訊 -->
                                <div class="col-lg-5">
                                    <div class="p-4 border rounded-3 bg-white h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-credit-card-fill me-2 text-warning"></i>預設自動扣款信用卡</h6>
                                            
                                            <!-- 黑金虛擬信用卡視覺 -->
                                            <div class="p-3 rounded-3 text-white mb-3 shadow-sm" style="background: linear-gradient(135deg, #1e2125 0%, #343a40 100%); border: 1px solid #ffc107;">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="small text-warning fw-bold tracking-wide">VISA INFINITE</span>
                                                    <i class="bi bi-wifi fs-5 text-white-50" style="transform: rotate(90deg);"></i>
                                                </div>
                                                <div class="fs-5 fw-bold mb-3" style="letter-spacing: 2px;" id="cardMaskDisplay">•••• •••• •••• 8842</div>
                                                <div class="d-flex justify-content-between small text-white-50">
                                                    <span>HOLDER: 5SL MEMBER</span>
                                                    <span id="cardExpiryDisplay">EXP: 09/29</span>
                                                </div>
                                            </div>
                                            <p class="text-muted small mb-3">您的卡片資訊均透過 SSL 256-bit 加密並由合規金流商代碼化（Tokenization）託管，本系統不儲存完整卡號。</p>
                                        </div>
                                        
                                        <button type="button" class="btn btn-outline-dark fw-bold w-100 btn-sm py-2" data-bs-toggle="modal" data-bs-target="#updateCardModal">
                                            <i class="bi bi-pencil-square me-1"></i>更新信用卡資訊
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div> <!-- 結束區塊 3：subscription-pane -->

                    </div> <!-- 結束 tab-content -->

                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- ==========================================================================
     彈窗 1：限定影音 / 音訊 / 文章預覽燈箱
     ========================================================================== -->
<div class="modal fade" id="exclusiveMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold text-warning" id="mediaModalTitle">限定內容預覽</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center" id="mediaModalBody">
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     彈窗 2：變更訂閱方案選擇視窗
     ========================================================================== -->
<div class="modal fade" id="changePlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-left-right me-2 text-warning"></i>變更訂閱週期與方案</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="form-check p-3 border rounded mb-3">
                    <input class="form-check-input ms-1" type="radio" name="planOption" id="planMonthly" value="月費標準方案 (NT$ 2,990 / 月)">
                    <label class="form-check-label ms-2 w-100" for="planMonthly">
                        <div class="fw-bold text-dark">月費標準方案 — NT$ 2,990 / 月</div>
                        <div class="text-muted small">彈性按月扣款，適合短期專案衝刺學習。</div>
                    </label>
                </div>
                <div class="form-check p-3 border border-warning bg-light rounded">
                    <input class="form-check-input ms-1" type="radio" name="planOption" id="planYearly" value="年度尊榮全通方案 (NT$ 29,900 / 年)" checked>
                    <label class="form-check-label ms-2 w-100" for="planYearly">
                        <div class="fw-bold text-dark">年度尊榮全通方案 — NT$ 29,900 / 年 <span class="badge bg-danger ms-1">目前方案</span></div>
                        <div class="text-muted small">享兩個月免費優惠，並額外贈送每季一對一顧問諮詢。</div>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-dark text-warning fw-bold btn-sm px-4" id="btnConfirmChangePlan">確認變更方案</button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     彈窗 3：更新自動扣款信用卡表單
     ========================================================================== -->
<div class="modal fade" id="updateCardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-credit-card me-2 text-warning"></i>更新自動扣款信用卡</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUpdateCreditCard">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">持卡人英文姓名</label>
                        <input type="text" class="form-control text-uppercase" id="inputCardHolder" placeholder="例如：WANG XIAO MING" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">信用卡卡號 (16碼)</label>
                        <input type="text" class="form-control" id="inputCardNumber" placeholder="4123 4567 8901 2345" maxlength="19" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">有效期限 (MM/YY)</label>
                            <input type="text" class="form-control" id="inputCardExpiry" placeholder="08/30" maxlength="5" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">背面安全碼 (CVC)</label>
                            <input type="password" class="form-control" placeholder="•••" maxlength="4" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold btn-sm px-4">驗證並綁定新卡</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==========================================================================
     彈窗 4：一對一顧問預約自助改期選擇視窗
     ========================================================================== -->
<div class="modal fade" id="rescheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-calendar2-week me-2 text-warning"></i>變更一對一顧問預約時段</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSubmitReschedule">
                <input type="hidden" id="rescheduleEnrId" value="">
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded border small mb-3">
                        <div class="text-muted mb-1">預約服務項目：</div>
                        <div class="fw-bold text-dark mb-2" id="rescheduleServiceTitle">-</div>
                        <div class="text-muted mb-1">目前原定時段：</div>
                        <div class="fw-bold text-primary" id="rescheduleCurrentTime">-</div>
                    </div>

                    <div class="mb-3">
                        <label for="selectNewSessionSlot" class="form-label small fw-bold text-dark">
                            選擇新的顧問開放時段 <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="selectNewSessionSlot" required>
                            <option value="">-- 請選擇可用的開放時段 --</option>
                        </select>
                        <div class="form-text small text-muted mt-1" id="slotHelperText">
                            系統僅列出目前尚未額滿之顧問開放時段，送出後原時段將自動釋出。
                        </div>
                    </div>

                    <!-- 選擇「無合適時段，請聯絡另訂時段」時動態展開 -->
                    <div class="p-3 bg-warning-subtle border border-warning rounded-3 d-none" id="customTimeRequestBox">
                        <label for="inputCustomTimeNote" class="form-label small fw-bold text-dark mb-1">
                            <i class="bi bi-chat-dots-fill me-1 text-warning"></i>請填寫您方便的期望時段與需求 <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control form-control-sm" id="inputCustomTimeNote" rows="3" placeholder="例如：平日晚上 19:30 以後或週六下午皆可，希望改約下週時段，謝謝！"></textarea>
                        <div class="form-text small text-dark mt-1">
                            送出後系統將自動為您建立專屬客服協調工單，顧問助理將於 1 個工作日內與您確認新時間。
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-dark text-warning fw-bold btn-sm px-4" id="btnConfirmReschedule">
                        確認變更時段
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- ==========================================================================
     彈窗 5：立即預約一對一顧問輔導視窗
     ========================================================================== -->
<div class="modal fade" id="bookConsultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus-fill me-2 text-warning"></i>預約一對一顧問深度輔導</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formBookConsult">
                <div class="modal-body p-4">
                    <!-- 1. 選擇輔導服務項目 -->
                    <div class="mb-3">
                        <label for="bookCourseSelect" class="form-label small fw-bold text-dark">選擇一對一輔導主題 <span class="text-danger">*</span></label>
                        <select class="form-select" id="bookCourseSelect" name="course_id" required>
                            <option value="">-- 請選擇顧問輔導項目 --</option>
                            <?php foreach ($consult_services as $srv): ?>
                                <option value="<?php echo (int)$srv['course_id']; ?>"
                                        data-cost="<?php echo (int)round((float)$srv['course_cost']); ?>">
                                    <?php echo htmlspecialchars($srv['course_title']); ?>（單次定價 NT$ <?php echo number_format((float)$srv['course_cost']); ?>）
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 2. 選擇預約時段（依所選項目動態載入空檔） -->
                    <div class="mb-3">
                        <label for="bookSessionSelect" class="form-label small fw-bold text-dark">選擇開放預約時段 <span class="text-danger">*</span></label>
                        <select class="form-select" id="bookSessionSelect" name="sess_id" required>
                            <option value="">-- 請先選擇上方輔導主題 --</option>
                        </select>
                    </div>

                    <!-- 3. 選擇計費方式（免費額度扣除 vs 單次付費購買） -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">選擇預約折抵／付款方式 <span class="text-danger">*</span></label>
                        <?php if ($free_consult_quota > 0): ?>
                            <div class="form-check p-3 border border-success bg-success-subtle rounded mb-2">
                                <input class="form-check-input ms-1" type="radio" name="use_free_quota" id="quotaFreeOpt" value="1" checked>
                                <label class="form-check-label ms-2 small text-dark" for="quotaFreeOpt">
                                    <strong>使用 VIP 免費輔導額度（扣除 1 次，本次 NT$ 0）</strong>
                                    <div class="text-muted">目前剩餘 <?php echo $free_consult_quota; ?> 次免費額度，送出後立即鎖定名額！</div>
                                </label>
                            </div>
                        <?php endif; ?>
                        <div class="form-check p-3 border rounded bg-light">
                            <input class="form-check-input ms-1" type="radio" name="use_free_quota" id="quotaPaidOpt" value="0" <?php echo ($free_consult_quota <= 0) ? 'checked' : ''; ?>>
                            <label class="form-check-label ms-2 small text-dark" for="quotaPaidOpt">
                                <strong>單次付費加購預約（<span id="bookPaidPriceLabel">請先選擇服務項目</span>）</strong>
                                <div class="text-muted">將為您加入購物車，可搭配 PTS 紅利點數或折價券結帳。</div>
                            </label>
                        </div>
                    </div>

                    <!-- 4. 諮詢問題與需求簡述 -->
                    <div class="mb-1">
                        <label for="bookTopicNote" class="form-label small fw-bold text-dark">本次希望討論的主題與問題重點 <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm" id="bookTopicNote" name="topic_note" rows="3" placeholder="請簡述您目前面臨的卡點或期望顧問協助的方向，方便老師提前準備..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-dark text-warning fw-bold btn-sm px-4" id="btnSubmitBookConsult">
                        確認送出預約
                    </button>
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

    #productsTab .nav-link {
        color: #495057;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
    }
    #productsTab .nav-link:hover {
        background-color: #e9ecef;
        color: #212529;
    }
    #productsTab .nav-link.active {
        background-color: #212529 !important;
        color: #ffc107 !important;
        border-color: #212529 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
</style>

<?php include_once '5sl_include_footer.php'; ?>

<script>
$(function() {
    // 1. 通用提示框控制
    function showProductsAlert(type, msg) {
        const $box = $('#productsAlertBox');
        const $icon = $('#productsAlertIcon');
        $box.removeClass('d-none alert-success alert-danger alert-warning').addClass('d-flex');
        $icon.removeClass('bi-check-circle-fill bi-exclamation-triangle-fill');

        if (type === 'success') {
            $box.addClass('alert-success');
            $icon.addClass('bi-check-circle-fill');
        } else if (type === 'warning') {
            $box.addClass('alert-warning');
            $icon.addClass('bi-exclamation-triangle-fill');
        } else {
            $box.addClass('alert-danger');
            $icon.addClass('bi-exclamation-triangle-fill');
        }
        $('#productsAlertMsg').text(msg);
        $('html, body').animate({ scrollTop: $box.offset().top - 120 }, 300);
    }

    // 2. 限定內容專區分類篩選器
    $('#exclusiveFilterGroup button').on('click', function() {
        $('#exclusiveFilterGroup button').removeClass('btn-dark active').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-dark active');

        const filter = $(this).attr('data-filter');
        if (filter === 'all') {
            $('.exclusive-item').fadeIn(200);
        } else {
            $('.exclusive-item').hide();
            $(`.exclusive-item[data-category="${filter}"]`).fadeIn(200);
        }
    });

    // 3. 課程素材線上預覽 / 檔案下載（串接 act_access_resource）
    $(document).on('click', '.btn-use-resource', function() {
        const resId   = $(this).attr('data-res-id');
        const useMode = $(this).attr('data-mode') || 'preview';

        if (window.fslLoader) window.fslLoader.show();

        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_access_resource',
            res_id: resId,
            use_mode: useMode
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();

            if (res.success !== 1) {
                showProductsAlert('danger', res.message || '無法存取此素材資源');
                return;
            }

            // 若為直接下載檔案模式
            if (useMode === 'download' && res.res_media_url) {
                showProductsAlert('success', `權限驗證通過！正在為您開啟/下載「${res.res_title}」（剩餘額度：${res.remain_str}）`);
                window.open(res.res_media_url, '_blank');
                return;
            }

            // 線上預覽模式：依 res_type 渲染對應播放器或閱讀器
            $('#mediaModalTitle').text(res.res_title);
            let contentHtml = '';
            const rType = parseInt(res.res_type, 10);
            const mUrl  = res.res_media_url || '';

            if (rType === 4) {
                // YouTube 視訊自動轉換為 embed 播放器
                //let ytId = '';
                let finalEmbedUrl = '';
                const m = mUrl.match(/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
                if (m && m[1]) {
                    //ytId = m[1];
                    var embedPath = "em" + "bed";
                    finalEmbedUrl = "https://youtube.com" + "/" + embedPath + "/" + m[1];
                }
                contentHtml = finalEmbedUrl
                    ? `<div class="ratio ratio-16x9 bg-black rounded overflow-hidden">`
                        + `<iframe src="`+ finalEmbedUrl +`" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; web-share; compute-pressure" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen>`
                        + `</iframe></div>`
                    : `<div class="p-4 text-warning">無法解析 YouTube 影片網址</div>`;
            } else if (rType === 5) {
                // 私房音檔播放器
                contentHtml = `
                    <div class="p-4 bg-black rounded border border-secondary text-center">
                        <i class="bi bi-soundwave display-3 text-info mb-3 d-block"></i>
                        <h6 class="text-white mb-3">${$('<div>').text(res.res_title).html()}</h6>
                        <audio controls autoplay class="w-100"><source src="${mUrl}"></audio>
                    </div>`;
            } else if (rType === 2) {
                // 高解析圖檔預覽
                contentHtml = `<div class="text-center"><img src="${mUrl}" class="img-fluid rounded shadow" style="max-height: 480px;" alt="素材圖檔"></div>`;
            } else if (rType === 3) {
                // 文書簡報檔案 (PDF 可內嵌預覽，PPT/DOC 提供安全下載鈕)
                const isPdf = (res.res_file_ext || '').toUpperCase() === 'PDF' || mUrl.toLowerCase().endsWith('.pdf');
                contentHtml = isPdf
                    ? `<div class="ratio ratio-16x9 bg-white rounded overflow-hidden"><iframe src="${mUrl}"></iframe></div>`
                    : `<div class="p-4 bg-black rounded border border-secondary text-center">
                           <i class="bi bi-file-earmark-slides display-3 text-warning mb-2 d-block"></i>
                           <p class="text-white mb-3">文書簡報檔案 (${res.res_file_ext}) 已準備就緒</p>
                           ${res.can_download === 1 ? `<a href="${mUrl}" target="_blank" class="btn btn-warning text-dark fw-bold px-4"><i class="bi bi-download me-1"></i>立即下載檔案</a>` : ''}
                       </div>`;
            } else {
                // 文字專文閱讀器
                contentHtml = `<div class="text-start p-4 bg-white text-dark rounded lh-lg" style="max-height: 440px; overflow-y: auto;">${res.res_body}</div>`;
            }

            // 底部加入「好評按讚 / 負評」互動列
            const rateBarHtml = `
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary text-start small">
                    <span class="text-white-50">使用額度狀態：<strong class="text-warning">${res.remain_str}</strong></span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-white-50">這份素材對您有幫助嗎？</span>
                        <button type="button" class="btn btn-sm btn-outline-success btn-rate-res" data-res-id="${res.res_id}" data-vote="1">
                            <i class="bi bi-hand-thumbs-up-fill me-1"></i>有幫助 (<span id="modalTopCount">${res.res_ratetop}</span>)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-rate-res" data-res-id="${res.res_id}" data-vote="-1">
                            <i class="bi bi-hand-thumbs-down"></i>
                        </button>
                    </div>
                </div>`;

            $('#mediaModalBody').html(contentHtml + rateBarHtml);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('exclusiveMediaModal')).show();
        }, 'json');
    });
    // 確保會員關閉預覽彈窗時影片聲音也會立刻停止
    $('#consoleInstantModal, #exclusiveMediaModal').on('hidden.bs.modal', function() {
        $(this).find('.modal-body').empty();
    });

    // 4. PTS 點數兌換解鎖素材 (act_redeem_resource)
    $(document).on('click', '.btn-redeem-resource', function() {
        const resId = $(this).attr('data-res-id');
        const pts   = $(this).attr('data-pts');
        const title = $(this).attr('data-title');

        if (!confirm(`確定要使用 ${pts} PTS 紅利點數兌換解鎖「${title}」嗎？`)) return;
        if (window.fslLoader) window.fslLoader.show();

        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_redeem_resource',
            res_id: resId
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();
            if (res.success === 1) {
                alert(res.message);
                window.location.reload();
            } else {
                showProductsAlert('danger', res.message || '兌換失敗，請稍後再試。');
            }
        }, 'json');
    });

    // 5. 素材按讚/負評評價 (act_rate_resource)
    $(document).on('click', '.btn-rate-res', function() {
        const resId = $(this).attr('data-res-id');
        const vote  = $(this).attr('data-vote');

        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_rate_resource',
            res_id: resId,
            vote: vote
        }, function(res) {
            if (res.success === 1) {
                $('#modalTopCount').text(res.res_ratetop);
                $(`#resTopCount_${resId}`).text(res.res_ratetop);
            } else {
                alert(res.message);
            }
        }, 'json');
    });


    // 5. 顧問服務自助改期互動（支援現有空檔直接換時段 + 無合適時段自動轉客服協調工單）
    const openSlotsByCourse = <?php echo json_encode((object)$open_slots_by_course, JSON_UNESCAPED_UNICODE); ?>;

    $(document).on('click', '.btn-open-reschedule', function() {
        const enrId    = $(this).attr('data-enr-id');
        const courseId = $(this).attr('data-course-id');
        const title    = $(this).attr('data-service');
        const currTime = $(this).attr('data-curr-time');

        $('#rescheduleEnrId').val(enrId);
        $('#rescheduleServiceTitle').text(title);
        $('#rescheduleCurrentTime').text(currTime);

        // 重置自訂時段輸入區
        $('#customTimeRequestBox').addClass('d-none');
        $('#inputCustomTimeNote').prop('required', false).val('');
        $('#btnConfirmReschedule').prop('disabled', false).text('確認變更時段');

        const $select = $('#selectNewSessionSlot');
        $select.empty();

        const slots = openSlotsByCourse[courseId] || [];
        if (slots.length === 0) {
            $select.append('<option value="">-- 目前無既定空檔，請選擇下方另訂時段 --</option>');
        } else {
            $select.append('<option value="">-- 請選擇新的顧問開放時段 --</option>');
            slots.forEach(function(s) {
                $select.append(`<option value="${s.sess_id}">${s.label}</option>`);
            });
        }

        // 固定在最尾端加入「無合適時段，請聯絡另訂時段」選項
        $select.append('<option value="custom_request">無合適時段，請聯絡另訂時段</option>');

        const rModal = new bootstrap.Modal(document.getElementById('rescheduleModal'));
        rModal.show();
    });

    // 監聽下拉選單切換：若選中「無合適時段」，展開期望時段輸入框
    $('#selectNewSessionSlot').on('change', function() {
        const val = $(this).val();
        if (val === 'custom_request') {
            $('#customTimeRequestBox').removeClass('d-none');
            $('#inputCustomTimeNote').prop('required', true).focus();
            $('#btnConfirmReschedule').text('送出另訂時段協調申請');
        } else {
            $('#customTimeRequestBox').addClass('d-none');
            $('#inputCustomTimeNote').prop('required', false);
            $('#btnConfirmReschedule').text('確認變更時段');
        }
    });

    // 送出改期表單（自動分流「直接換時段」或「另訂時段客服工單」）
    $('#formSubmitReschedule').on('submit', function(e) {
        e.preventDefault();
        const enrId      = $('#rescheduleEnrId').val();
        const newSessId  = $('#selectNewSessionSlot').val();
        const customNote = $('#inputCustomTimeNote').val().trim();

        if (!newSessId) {
            alert('請先從下拉選單選擇一個選項！');
            return;
        }
        if (newSessId === 'custom_request' && !customNote) {
            alert('請填寫您方便的期望時段與需求說明！');
            $('#inputCustomTimeNote').focus();
            return;
        }

        bootstrap.Modal.getInstance(document.getElementById('rescheduleModal')).hide();
        if (window.fslLoader) window.fslLoader.show();

        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_reschedule_session',
            enr_id: enrId,
            new_sess_id: newSessId,
            custom_note: customNote
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();
            if (res.success === 1) {
                const $row = $(`#consultRow_${enrId}`);
                if (res.mode === 'custom_request') {
                    // 狀態徽章改為「申請改期協調中」，並停用重複點擊
                    $row.find('td:eq(3)').html('<span class="badge bg-warning text-dark">改期時段協調中</span>');
                    $row.find('.btn-open-reschedule').prop('disabled', true).text('已送出協調');
                    showProductsAlert('warning', res.message);
                } else {
                    // 直接更新表格內的預約時段
                    $row.find('.consult-time-cell').text(res.new_time_str);
                    $row.find('.btn-open-reschedule').attr('data-curr-time', res.new_time_str);
                    showProductsAlert('success', res.message);
                }
            } else {
                showProductsAlert('danger', res.message || '時段變更失敗，請稍後再試。');
            }
        }, 'json').fail(function() {
            if (window.fslLoader) window.fslLoader.hide();
            showProductsAlert('danger', '網路連線異常，請稍後再試。');
        });
    });

    // 6. 立即預約一對一顧問輔導彈窗連動
    $('#bookCourseSelect').on('change', function() {
        const courseId = $(this).val();
        const cost     = parseInt($(this).find(':selected').attr('data-cost'), 10) || 0;
        const $sessSel = $('#bookSessionSelect');

        $('#bookPaidPriceLabel').text(courseId ? `NT$ ${cost.toLocaleString()}` : '請先選擇服務項目');
        $sessSel.empty();

        if (!courseId) {
            $sessSel.append('<option value="">-- 請先選擇上方輔導主題 --</option>');
            return;
        }

        const slots = openSlotsByCourse[courseId] || [];
        if (slots.length === 0) {
            $sessSel.append('<option value="">-- 目前無既定空檔，請聯繫客服開檔 --</option>');
        } else {
            $sessSel.append('<option value="">-- 請選擇顧問開放時段 --</option>');
            slots.forEach(function(s) {
                $sessSel.append(`<option value="${s.sess_id}">${s.label}</option>`);
            });
        }
    });

    $('#formBookConsult').on('submit', function(e) {
        e.preventDefault();
        const courseId     = $('#bookCourseSelect').val();
        const sessId       = $('#bookSessionSelect').val();
        const useFreeQuota = $('input[name="use_free_quota"]:checked').val() || '0';
        const topicNote    = $('#bookTopicNote').val().trim();

        if (!courseId || !sessId || !topicNote) {
            alert('請完整選擇輔導主題、時段並填寫諮詢重點！');
            return;
        }

        bootstrap.Modal.getInstance(document.getElementById('bookConsultModal')).hide();
        if (window.fslLoader) window.fslLoader.show();

        $.post('./funcFiles/5sl_act_member.php', {
            action: 'act_book_consult',
            course_id: courseId,
            sess_id: sessId,
            use_free_quota: useFreeQuota,
            topic_note: topicNote
        }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();
            if (res.success === 1) {
                if (res.mode === 'free_booked') {
                    alert(res.message);
                    window.location.reload(); // 重新整理頁面，立刻在下方預約紀錄看到新建立的一筆
                } else {
                    window.location.href = '5sl_myCart.php'; // 付費項目自動加入購物車並跳轉結帳
                }
            } else {
                showProductsAlert('danger', res.message || '預約處理失敗，請稍後再試。');
            }
        }, 'json').fail(function() {
            if (window.fslLoader) window.fslLoader.hide();
            showProductsAlert('danger', '網路連線異常，請稍後再試。');
        });
    });


    // 7. 訂閱管理：立即手動續費
    $('#btnManualRenew').on('click', function() {
        if (window.fslLoader) window.fslLoader.show();
        setTimeout(function() {
            if (window.fslLoader) window.fslLoader.hide();
            $('#nextBillingDateText').text('2028/05/15');
            showProductsAlert('success', '手動續費成功！您的年度尊榮全通方案已延長一年至 2028/05/14。');
        }, 700);
    });

    // 8. 訂閱管理：取消 / 恢復自動續訂切換
    let isAutoRenewActive = true;
    $('#btnToggleCancelSub').on('click', function() {
        const $btn = $(this);
        if (window.fslLoader) window.fslLoader.show();

        setTimeout(function() {
            if (window.fslLoader) window.fslLoader.hide();
            if (isAutoRenewActive) {
                isAutoRenewActive = false;
                $('#subStatusBadge').removeClass('bg-success').addClass('bg-secondary').html('<i class="bi bi-pause-circle-fill me-1"></i>自動續訂已取消');
                $btn.removeClass('btn-outline-danger').addClass('btn-success').text('重新啟用自動續訂');
                showProductsAlert('warning', '您已取消下期自動續訂。您仍可完整享有目前訂閱權益直到 2027/05/14 到期為止。');
            } else {
                isAutoRenewActive = true;
                $('#subStatusBadge').removeClass('bg-secondary').addClass('bg-success').html('<i class="bi bi-check-circle-fill me-1"></i>自動續訂啟用中');
                $btn.removeClass('btn-success').addClass('btn-outline-danger').text('取消自動續訂');
                showProductsAlert('success', '已重新啟用自動續訂服務！您的會員權益將無縫延續。');
            }
        }, 600);
    });

    // 9. 訂閱管理：更新信用卡資訊
    $('#inputCardNumber').on('input', function() {
        let val = $(this).val().replace(/\D/g, '').substring(0, 16);
        $(this).val(val.replace(/(.{4})/g, '$1 ').trim());
    });

    $('#inputCardExpiry').on('input', function() {
        let val = $(this).val().replace(/\D/g, '').substring(0, 4);
        if (val.length >= 3) {
            $(this).val(val.substring(0, 2) + '/' + val.substring(2));
        } else {
            $(this).val(val);
        }
    });

    $('#formUpdateCreditCard').on('submit', function(e) {
        e.preventDefault();
        const rawCard = $('#inputCardNumber').val().replace(/\s+/g, '');
        const expiry = $('#inputCardExpiry').val();

        if (rawCard.length < 16) {
            alert('請輸入完整的 16 碼信用卡號！');
            return;
        }

        const last4 = rawCard.slice(-4);
        bootstrap.Modal.getInstance(document.getElementById('updateCardModal')).hide();
        if (window.fslLoader) window.fslLoader.show();

        setTimeout(function() {
            if (window.fslLoader) window.fslLoader.hide();
            $('#cardMaskDisplay').text(`•••• •••• •••• ${last4}`);
            $('#cardExpiryDisplay').text(`EXP: ${expiry}`);
            $('#formUpdateCreditCard').reset();
            showProductsAlert('success', `扣款信用卡已成功更新為末四碼 ${last4} 之卡片！`);
        }, 700);
    });


    // 補回「已購線上課程進度 -> 繼續上課」與「歷史顧問預約 -> 查看紀要」按鈕事件 (.btn-open-media)
    $(document).on('click', '.btn-open-media', function() {
        const title      = $(this).attr('data-title') || '課程單元學習視窗';
        const meetingUrl = ($(this).attr('data-meeting') || '').trim();
        const safeTitle  = $('<div>').text(title).html();

        $('#mediaModalTitle').text(title);

        let bodyHtml = '';
        if (meetingUrl !== '') {
            // 若該梯次有設定線上會議室連結（Zoom / Google Meet / 直播室）
            const isFullUrl = /^https?:\/\//i.test(meetingUrl);
            bodyHtml = `
                <div class="p-4 bg-black rounded border border-warning text-center">
                    <i class="bi bi-camera-video-fill display-3 text-warning mb-3 d-block"></i>
                    <h5 class="fw-bold text-white mb-2">${safeTitle}</h5>
                    <p class="text-white-50 small mb-3">專屬線上直播／互動教室已準備就緒</p>
                    <div class="p-2 bg-dark rounded border border-secondary text-warning small mb-4">
                        會議室代碼 / 連結：<code>${$('<div>').text(meetingUrl).html()}</code>
                    </div>
                    ${isFullUrl ? `<a href="${meetingUrl}" target="_blank" class="btn btn-warning text-dark fw-bold px-4 py-2"><i class="bi bi-box-arrow-up-right me-1"></i>立即前往線上教室</a>` : ''}
                </div>`;
        } else {
            // 一般錄播課程單元播放器 + 引導前往限定內容專區下載講義
            bodyHtml = `
                <div class="ratio ratio-16x9 bg-black rounded overflow-hidden border border-secondary mb-3">
                    <div class="d-flex flex-column align-items-center justify-content-center text-white-50 p-3">
                        <i class="bi bi-play-btn-fill display-1 text-warning mb-2"></i>
                        <h6 class="text-white fw-bold mb-1">${safeTitle}</h6>
                        <p class="small text-white-50 mb-0">VIP 專屬課程串流播放器已就緒（權限驗證通過）</p>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2">
                    <span class="small text-white-50">需要搭配本課程的簡報講義、音檔或延伸資料嗎？</span>
                    <button type="button" class="btn btn-warning text-dark btn-sm fw-bold px-3" id="btnJumpToCourseResources">
                        <i class="bi bi-folder2-open me-1"></i>前往課程素材庫
                    </button>
                </div>`;
        }

        $('#mediaModalBody').html(bodyHtml);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('exclusiveMediaModal')).show();
    });

    // 點擊彈窗內的「前往課程素材庫」自動關閉彈窗並切換至「限定內容專區」分頁
    $(document).on('click', '#btnJumpToCourseResources', function() {
        bootstrap.Modal.getInstance(document.getElementById('exclusiveMediaModal')).hide();
        const tabTrigger = document.getElementById('exclusive-tab');
        if (tabTrigger) {
            bootstrap.Tab.getOrCreateInstance(tabTrigger).show();
        }
    });





});
</script>
