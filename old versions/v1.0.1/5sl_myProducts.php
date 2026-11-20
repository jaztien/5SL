<?php

// 宣告當前頁面身分為 myProducts（課程與服務）
$current_page = 'myProducts';

// 引入共用頁首
include_once '5sl_include_header.php';

$mb_id = (int)($_SESSION['mbID'] ?? $_SESSION['user_id'] ?? 0);

// 1. 查詢會員所有報名課程與梯次紀錄 (fsl_course_enrolls + courses + course_sessions)
$sql_enrolls = "
    SELECT 
        e.enr_id, e.enr_pay_status, e.enr_status, e.enr_done_lessons, e.enr_last_watched, e.enr_expire_at,
        c.course_id, c.course_title, c.course_type,
        s.sess_id, s.sess_batch, s.sess_altname, s.sess_startdate, s.sess_starttime, s.sess_duration, s.sess_lesson, s.sess_meetingid
    FROM fsl_course_enrolls e
    INNER JOIN courses c ON e.course_id = c.course_id
    INNER JOIN course_sessions s ON e.sess_id = s.sess_id
    WHERE e.mb_id = ? AND e.enr_status != 0
    ORDER BY e.enr_pay_status DESC, s.sess_startdate DESC, s.sess_starttime DESC
";
$all_enrolls = ($mb_id > 0) ? $DB->query($sql_enrolls, [$mb_id]) : [];
if (!is_array($all_enrolls)) $all_enrolls = [];

// 拆分為「一般線上/實體課程 (course_type=1)」與「一對一顧問預約 (course_type=2)」
$my_courses    = [];
$my_consults   = [];
$total_pct_sum = 0;

foreach ($all_enrolls as $row) {
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

// 2. 預撈一對一顧問服務「目前開放可改期的未來空檔時段」(course_sessions)
$open_slots_by_course = [];
$slot_rows = $DB->query("
    SELECT s.sess_id, s.course_id, s.sess_startdate, s.sess_starttime, s.sess_duration, s.sess_altname
    FROM course_sessions s
    INNER JOIN courses c ON s.course_id = c.course_id
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
        $label = date('Y/m/d', $ts1) . ' (' . $week_chars[(int)date('w', $ts1)] . ') ' . date('H:i', $ts1) . ' - ' . date('H:i', $ts2);
        if (!empty($sr['sess_altname'])) {
            $label .= '｜' . $sr['sess_altname'];
        }
        $open_slots_by_course[(int)$sr['course_id']][] = [
            'sess_id' => (int)$sr['sess_id'],
            'label'   => $label
        ];
    }
}

// 3. 查詢「限定內容專區」資源（整合 articles 專欄 + merchandise 數位影音/音訊/檔案）
$exclusive_articles = $DB->query("
    SELECT arti_id, arti_published, arti_title, arti_brief, arti_body
    FROM articles
    WHERE arti_status = 1
    ORDER BY arti_published DESC
    LIMIT 12
");
if (!is_array($exclusive_articles)) $exclusive_articles = [];

$exclusive_merches = $DB->query("
    SELECT merch_id, merch_title, merch_brief, merch_download_url, merch_file_format
    FROM merchandise
    WHERE merch_type = 2 AND merch_status = 1
    ORDER BY merch_published DESC
    LIMIT 12
");
if (!is_array($exclusive_merches)) $exclusive_merches = [];
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
                                        $exp_text = !empty($mc['enr_expire_at']) ? '到期日：' . date('Y/m/d', strtotime($mc['enr_expire_at'])) : '永久觀看權限';
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


                            <!-- 專屬顧問 / 服務預約記錄 -->
                            <h6 class="fw-bold text-dark mb-3 pt-2 border-top"><i class="bi bi-calendar2-check-fill me-2 text-success"></i>一對一技術與品牌顧問預約記錄</h6>
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
                                                $time_str = date('Y/m/d', $ts1) . ' (' . $week_chars[(int)date('w', $ts1)] . ') ' . date('H:i', $ts1) . ' - ' . date('H:i', $ts2);
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
                                <button type="button" class="btn btn-sm btn-dark active" data-filter="all">全部資源</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="video"><i class="bi bi-camera-video me-1"></i>限定影片</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="audio"><i class="bi bi-headphones me-1"></i>私房音訊</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="article"><i class="bi bi-file-earmark-richtext me-1"></i>深度專欄</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="file"><i class="bi bi-file-earmark-zip me-1"></i>原始碼與模板</button>
                            </div>

                            <!-- 資源卡片網格 -->
                            <div class="row g-3" id="exclusiveResourceGrid">
                                <!-- 影片項目 -->
                                <div class="col-md-6 exclusive-item" data-category="video">
                                    <div class="p-3 border rounded-3 h-100 d-flex flex-column justify-content-between bg-light">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-danger"><i class="bi bi-camera-video-fill me-1"></i>VIP 限定影片</span>
                                                <small class="text-muted">片長 42:15</small>
                                            </div>
                                            <h6 class="fw-bold text-dark">2026 高併發電商資料庫分表與快取實戰解析</h6>
                                            <p class="text-muted small mb-3">深入剖析百萬級流量下的 MySQL 索引優化與 Redis 讀寫分離架構。</p>
                                        </div>
                                        <button type="button" class="btn btn-dark btn-sm w-100 fw-bold btn-open-media" data-title="2026 高併發電商資料庫實戰解析" data-type="video">
                                            <i class="bi bi-play-fill text-warning me-1"></i>立即線上觀看
                                        </button>
                                    </div>
                                </div>

                                <!-- 音訊項目 -->
                                <div class="col-md-6 exclusive-item" data-category="audio">
                                    <div class="p-3 border rounded-3 h-100 d-flex flex-column justify-content-between bg-light">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-info text-dark"><i class="bi bi-headphones me-1"></i>私房音訊 Podcast</span>
                                                <small class="text-muted">音長 28:40</small>
                                            </div>
                                            <h6 class="fw-bold text-dark">創辦人夜話：獨立工作室如何定價與篩選高價值客戶</h6>
                                            <p class="text-muted small mb-3">純音訊深度對談，適合通勤或休息時聆聽的商業思維心法。</p>
                                        </div>
                                        <button type="button" class="btn btn-outline-dark btn-sm w-100 fw-bold btn-open-media" data-title="創辦人夜話：高價值客戶定價策略" data-type="audio">
                                            <i class="bi bi-volume-up-fill me-1"></i>開啟播放器聆聽
                                        </button>
                                    </div>
                                </div>

                                <!-- 文章專欄項目 -->
                                <div class="col-md-6 exclusive-item" data-category="article">
                                    <div class="p-3 border rounded-3 h-100 d-flex flex-column justify-content-between bg-light">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-success"><i class="bi bi-file-earmark-text-fill me-1"></i>會員深度專欄</span>
                                                <small class="text-muted">閱讀約 8 分鐘</small>
                                            </div>
                                            <h6 class="fw-bold text-dark">前後端分離架構下的資安防禦白皮書 (2026 最新版)</h6>
                                            <p class="text-muted small mb-3">完整收錄 CSRF、XSS 與 Session 劫持的 PHP 程式碼級別防禦範本。</p>
                                        </div>
                                        <button type="button" class="btn btn-outline-dark btn-sm w-100 fw-bold btn-open-media" data-title="前後端分離架構資安防禦白皮書" data-type="article">
                                            <i class="bi bi-book-half me-1"></i>閱讀完整專文
                                        </button>
                                    </div>
                                </div>

                                <!-- 檔案下載項目 -->
                                <div class="col-md-6 exclusive-item" data-category="file">
                                    <div class="p-3 border rounded-3 h-100 d-flex flex-column justify-content-between bg-light">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-warning text-dark"><i class="bi bi-file-earmark-zip-fill me-1"></i>專屬檔案下載</span>
                                                <small class="text-muted">ZIP • 18.4 MB</small>
                                            </div>
                                            <h6 class="fw-bold text-dark">5SL 黑金商務版 UI 元件庫與 PSD/Figma 設計原始檔</h6>
                                            <p class="text-muted small mb-3">包含全套自適應按鈕、3D 卡片遮罩與標準資料庫 SQL 匯入結構。</p>
                                        </div>
                                        <button type="button" class="btn btn-warning text-dark btn-sm w-100 fw-bold btn-download-file" data-filename="5SL_Premium_UI_Kit_2026.zip">
                                            <i class="bi bi-download me-1"></i>安全驗證並下載檔案
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- 結束區塊 2：exclusive-pane -->

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

    // 3. 開啟限定媒體預覽彈窗 (影片 / 音訊 / 文章)
    $(document).on('click', '.btn-open-media', function() {
        const title = $(this).attr('data-title');
        const type = $(this).attr('data-type');
        $('#mediaModalTitle').text(title);

        let bodyHtml = '';
        if (type === 'video') {
            bodyHtml = `
                <div class="ratio ratio-16x9 bg-black rounded overflow-hidden border border-secondary">
                    <div class="d-flex flex-column align-items-center justify-content-center text-white-50">
                        <i class="bi bi-play-btn-fill display-1 text-warning mb-2"></i>
                        <p class="mb-0">VIP 專屬串流播放器已就緒 (權限驗證通過)</p>
                        <small class="text-muted">實際串接後可載入加密 HLS 串流或專屬 Vimeo/YouTube 私有影片</small>
                    </div>
                </div>`;
        } else if (type === 'audio') {
            bodyHtml = `
                <div class="p-4 bg-black rounded border border-secondary">
                    <i class="bi bi-soundwave display-3 text-info mb-3 d-block"></i>
                    <h6 class="text-white mb-3">${title}</h6>
                    <audio controls class="w-100">
                        <source src="" type="audio/mpeg">
                        您的瀏覽器不支援音訊播放元件。
                    </audio>
                </div>`;
        } else {
            bodyHtml = `
                <div class="text-start p-3 bg-white text-dark rounded" style="max-height: 400px; overflow-y: auto;">
                    <h5 class="fw-bold mb-3">${title}</h5>
                    <p class="text-muted small">發布日期：2026-11-01 • 限定 VIP 訂閱會員閱讀</p>
                    <hr>
                    <p class="lh-lg">在現代前後端分離架構中，Session 安全性與 API 權限控管是保護會員付費內容的核心防線。透過 PHP 後端驗證會員訂閱到期日（<code>expire_date >= NOW()</code>），可確保只有有效訂閱者能取得真實檔案下載憑證...</p>
                </div>`;
        }

        $('#mediaModalBody').html(bodyHtml);
        const mediaModal = new bootstrap.Modal(document.getElementById('exclusiveMediaModal'));
        mediaModal.show();
    });

    // 4. 限定檔案安全驗證下載
    $('.btn-download-file').on('click', function() {
        const filename = $(this).attr('data-filename');
        if (window.fslLoader) window.fslLoader.show();

        setTimeout(function() {
            if (window.fslLoader) window.fslLoader.hide();
            showProductsAlert('success', `權限驗證通過！已開始下載專屬檔案：「${filename}」`);
        }, 600);
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
});
</script>
