<?php
$current_page = 'index'; // 💡 宣告身分為首頁
// 引入完整頁首設定與大焦點區
include_once '5sl_include_header.php';
// 取得目前登入會員的 ID、等級以及已收藏的課程 ID 陣列
$mb_id = (int)($_SESSION['mbID'] ?? $_SESSION['user_id'] ?? 0);
$mb_lvl     = isset($_SESSION['mbLevel']) ? (int)$_SESSION['mbLevel'] : 3;
// 查詢目前登入會員已收藏的課程 ID 陣列（供首頁卡片自動點亮實心愛心）
$user_wished_courses = [];
if (!empty($mb_id) && $mb_id > 0 && isset($DB)) {
    $wish_rows = $DB->query(
        "SELECT wish_target_id FROM fsl_wishlists WHERE mb_id = ? AND wish_target_type = 1",
        [$mb_id]
    );
    if (is_array($wish_rows)) {
        foreach ($wish_rows as $wr) {
            $user_wished_courses[(int)$wr['wish_target_id']] = true;
        }
    }
}

// 預載所有上架課程詳細資訊與開放梯次（供首頁點擊「立即預約報名 / 我要預約」先跳出確認彈窗）
$home_courses_map = array();
if (isset($DB)) {
    $cs_rows = $DB->query("SELECT course_id, course_title, course_brief, course_cost, course_type, course_max FROM fsl_courses WHERE course_status = 1");
    if (is_array($cs_rows)) {
        foreach ($cs_rows as $cr) {
            $cid = (int)$cr['course_id'];
            $cover_path = './5sladmin/uploadFiles/cs/cimg_' . $cid . '_.jpg';
            $cover_url  = file_exists($cover_path)
                ? ($cover_path . '?v=' . filemtime($cover_path))
                : ('imageFiles/test/' . ((($cid - 1) % 18) + 1) . '.jpg');

            $home_courses_map[$cid] = array(
                'course_id'    => $cid,
                'course_title' => $cr['course_title'],
                'course_brief' => strip_tags($cr['course_brief'] ?? '人生第五季精選陪跑與生命美學課程，陪伴您一步一步找回方向與力量。'),
                'course_cost'  => (int)round((float)$cr['course_cost']),
                'course_type'  => (int)$cr['course_type'],
                'cover_url'    => $cover_url,
                'sessions'     => array()
            );
        }
    }

    $sess_rows = $DB->query("
        SELECT s.sess_id, s.course_id, s.sess_batch, s.sess_altname, s.sess_startdate, s.sess_starttime, s.sess_duration, s.sess_enrollee, c.course_max
        FROM fsl_course_sessions s
        INNER JOIN fsl_courses c ON s.course_id = c.course_id
        WHERE s.sess_status = 1 AND c.course_status = 1 AND s.sess_enrollee < c.course_max
        ORDER BY s.sess_startdate ASC, s.sess_starttime ASC
    ");
    $wk_chars = array('日', '一', '二', '三', '四', '五', '六');
    if (is_array($sess_rows)) {
        foreach ($sess_rows as $sr) {
            $cid = (int)$sr['course_id'];
            if (!isset($home_courses_map[$cid])) continue;
            $ts1 = strtotime($sr['sess_startdate'] . ' ' . $sr['sess_starttime']);
            $ts2 = $ts1 + ((int)$sr['sess_duration'] * 60);
            $rem = max(1, (int)$sr['course_max'] - (int)$sr['sess_enrollee']);
            $lbl = '第 ' . (int)$sr['sess_batch'] . ' 梯 • ' . date('Y/m/d', $ts1) . ' (' . $wk_chars[(int)date('w', $ts1)] . ') ' . date('H:i', $ts1) . '-' . date('H:i', $ts2);
            if (!empty($sr['sess_altname'])) $lbl .= '｜' . $sr['sess_altname'];
            $lbl .= " (剩餘 {$rem} 名額)";

            $home_courses_map[$cid]['sessions'][] = array(
                'sess_id' => (int)$sr['sess_id'],
                'label'   => $lbl
            );
        }
    }
}

?>

<!-- 為何需要人生第五季 -->
<section id="whyneed" class="py-5 static-bg-zone" style="background-image: url('imageFiles/indexBG03.jpg');">
    <div class="container text-white py-5">
        <div class="text-center mb-4">
            <h2 class="fsl-section-title-box mb-3">為何需要人生第五季</h2>
            <p class="fs-5 text-white-50 mb-4">您或許還不知道，您已經擁有或正在需要您的人生第五季</p>
            <!-- 低調半透明細橫線（透明度超過 50%） -->
            <hr class="fsl-section-divider">
            <!-- 五行清單與按鈕區塊（加上 reveal-on-scroll 交由現有 IntersectionObserver 觸發依序進場） -->
            <div class="d-inline-block text-start mt-2 reveal-on-scroll fsl-stagger-group">
                <ul class="list-unstyled fsl-fifth-season-list mb-5">
                    <li class="fsl-list-item">
                        <span class="fsl-item-inner">
                            <span class="fsl-circle-icon"><i class="bi bi-chevron-right"></i></span>
                            <span class="fsl-list-text">走在人生路上</span>
                        </span>
                    </li>
                    <li class="fsl-list-item">
                        <span class="fsl-item-inner">
                            <span class="fsl-circle-icon"><i class="bi bi-chevron-right"></i></span>
                            <span class="fsl-list-text">會卡住 會懷疑 會停滯</span>
                        </span>
                    </li>
                    <li class="fsl-list-item">
                        <span class="fsl-item-inner">
                            <span class="fsl-circle-icon"><i class="bi bi-chevron-right"></i></span>
                            <span class="fsl-list-text">會徬徨 會迷惘</span>
                        </span>
                    </li>
                    <li class="fsl-list-item">
                        <span class="fsl-item-inner">
                            <span class="fsl-circle-icon"><i class="bi bi-chevron-right"></i></span>
                            <span class="fsl-list-text">曾經快要失去力氣.</span>
                        </span>
                    </li>
                    <li class="fsl-list-item">
                        <span class="fsl-item-inner">
                            <span class="fsl-circle-icon"><i class="bi bi-chevron-right"></i></span>
                            <span class="fsl-list-text">卻仍然相信自己還能重新發光人</span>
                        </span>
                    </li>
                </ul>

                <!-- READ MORE > 長方形圓角按鈕（大幅拉開與上方五行文字的距離，視覺區隔更明確） -->
                <div class="mt-5 pt-3 fsl-btn-reveal-wrap">
                    <a href="#features" class="btn btn-outline-light rounded-3 px-4 py-2 fw-semibold fsl-read-more-btn">
                        READ MORE <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 我們的核心服務 -->
<section id="coreservices" class="py-5 bg-dark text-white fsl-guide-section-dark" style="background-image: url('imageFiles/test/<?php echo rand(1, 18); ?>.jpg');">
    <div class="container py-5 position-relative" style="z-index: 2;">
        <div class="text-center mb-5">
            <h2 class="fsl-section-title-box mb-3">我們的核心服務</h2>
            <p class="fs-5 text-white-50 mb-4">引導您順利進入您的人生第五季</p>
            <hr class="fsl-section-divider">
        </div>
        <div class="row g-4">
            <!-- 卡片 1 -->
            <div class="col-md-6 col-lg-3 reveal-on-scroll delay-1">
                <div class="card h-100 hover-card fsl-guide-card px-2 py-4 text-center">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-3 px-1">
                        <div class="fsl-guide-icon mb-4">
                            <i class="bi bi-compass"></i>
                        </div>
                        <h4 class="card-title fw-bold mb-0 fsl-guide-title">看清自己・釐清現狀</h4>
                    </div>
                </div>
            </div>
            <!-- 卡片 2 -->
            <div class="col-md-6 col-lg-3 reveal-on-scroll delay-2">
                <div class="card h-100 hover-card fsl-guide-card px-2 py-4 text-center">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-3 px-1">
                        <div class="fsl-guide-icon mb-4">
                            <i class="bi bi-key"></i>
                        </div>
                        <h4 class="card-title fw-bold mb-0 fsl-guide-title">找到卡點・看懂限制</h4>
                    </div>
                </div>
            </div>
            <!-- 卡片 3 -->
            <div class="col-md-6 col-lg-3 reveal-on-scroll delay-3">
                <div class="card h-100 hover-card fsl-guide-card px-2 py-4 text-center">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-3 px-1">
                        <div class="fsl-guide-icon mb-4">
                            <i class="bi bi-feather"></i>
                        </div>
                        <h4 class="card-title fw-bold mb-0 fsl-guide-title">面對恐懼・鬆開自己</h4>
                    </div>
                </div>
            </div>
            <!-- 卡片 4 -->
            <div class="col-md-6 col-lg-3 reveal-on-scroll delay-4">
                <div class="card h-100 hover-card fsl-guide-card px-2 py-4 text-center">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-3 px-1">
                        <div class="fsl-guide-icon mb-4">
                            <i class="bi bi-sunrise"></i>
                        </div>
                        <h4 class="card-title fw-bold mb-0 fsl-guide-title">重整價值・重新選擇</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- =========================================================
                    品牌核心宣言：高奢靜謐三欄陳列（接續於核心服務四張卡片下方）
                    ========================================================= -->
        <div class="fsl-credo-section mt-5 pt-5">
            <div class="row g-4 g-lg-5 align-items-stretch">

                <!-- 第 1 段：我們相信 -->
                <div class="col-12 col-lg-4">
                    <div class="fsl-credo-card h-100">
                        <div class="fsl-credo-header">
                            <span class="fsl-credo-icon" aria-hidden="true">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                            <h3 class="fsl-credo-title">我們相信</h3>
                        </div>
                        <div class="fsl-credo-body">
                            <p class="fsl-credo-lead">人生第五季，不是重來</p>
                            <p>而是走過許多春夏秋冬之後<br>依然願意熱愛生命的模樣</p>
                            <hr class="fsl-credo-divider">
                            <p>在這裡陪你重新看見自己<br>不是否定過去<br>而是把走過的路<br><span class="fsl-credo-accent">整理成繼續前行的力量</span></p>
                        </div>
                    </div>
                </div>

                <!-- 第 2 段：我們陪伴 -->
                <div class="col-12 col-lg-4">
                    <div class="fsl-credo-card h-100">
                        <div class="fsl-credo-header">
                            <span class="fsl-credo-icon" aria-hidden="true">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                            <h3 class="fsl-credo-title">我們陪伴</h3>
                        </div>
                        <div class="fsl-credo-body">
                            <p class="fsl-credo-lead">陪伴你在人生轉折中</p>
                            <p>舒理生命卡點、整理過去的故事<br>重新找回方向與力量</p>
                            <hr class="fsl-credo-divider">
                            <p>透過課程、陪跑、書寫、提問與藝術創作<br>讓你看懂自己為什麼卡住<br><span class="fsl-credo-accent">也一步一步找回新的選擇</span></p>
                        </div>
                    </div>
                </div>

                <!-- 第 3 段：我們融合 -->
                <div class="col-12 col-lg-4">
                    <div class="fsl-credo-card h-100">
                        <div class="fsl-credo-header">
                            <span class="fsl-credo-icon" aria-hidden="true">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                            <h3 class="fsl-credo-title">我們融合</h3>
                        </div>
                        <div class="fsl-credo-body">
                            <p class="fsl-credo-lead">藝術輔療與生命梳理</p>
                            <p>色彩與形象美學</p>
                            <hr class="fsl-credo-divider">
                            <p>讓內在重新舒展<br>讓外在重新發光<br><span class="fsl-credo-accent">活出屬於自己的生命樣貌</span></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

<!-- 見證人生第五季的力量 -->
<section id="videoshowcase" class="py-5 text-white" style="background-image: url('imageFiles/mainBG6.jpg');">
    <div class="container text-center py-4">
        <div class="fsl-header-gap">
            <h2 class="fsl-section-title-box mb-3">見證人生第五季的力量</h2>
            <p class="fs-5 text-white-50 mb-0">深入瞭解我們如何開啟您這段人生的大門</p>
            <hr class="fsl-section-divider">
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- 影片封面大圖容器 -->
                <div class="video-cover-wrapper position-relative rounded-3 overflow-hidden shadow-lg" style="background-image: url('imageFiles/test/<?php echo rand(1, 18); ?>.jpg');">
                    <!-- 70% 黑色遮罩，保護質感 -->
                    <div class="video-cover-overlay"></div>
                    <button type="button" class="video-play-btn" data-bs-toggle="modal" data-bs-target="#videoLightboxModal" data-video-url="https://youtu.be/XEtb5IvLVnY">
                        <div class="play-icon-triangle"></div>
                        <div class="play-btn-ripple"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Modal Lightbox -->
<div class="modal fade" id="videoLightboxModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <!-- 右上角精緻大關閉鈕 -->
            <div class="text-end mb-2">
                <button type="button" class="btn-close btn-close-white fs-4 shadow-none" data-bs-dismiss="modal" aria-label="Close" id="btnOverlayVideoClose"></button>
            </div>
            <div class="modal-body p-0">
                <!-- 核心：16:9 黃金比例自適應容器 -->
                <div class="ratio ratio-16x9 shadow-2xl rounded-3 overflow-hidden bg-black">
                    <!-- iframe 預設留空，由 JS 智慧管理注入與關閉拔除 -->
                    <iframe src="" id="lightboxIframe" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; web-share; compute-pressure" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Image Preview Lightbox -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <!-- 右上角關閉鈕 -->
            <div class="text-end mb-2">
                <button type="button" class="btn-close btn-close-white fs-4 shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <div class="d-inline-block position-relative rounded-3 overflow-hidden shadow-lg bg-dark border border-secondary border-opacity-25">
                    <img src="" id="previewLightboxImg" class="img-fluid d-block" style="max-height: 80vh; width: auto; object-fit: contain;">
                    <div class="p-3 bg-dark bg-opacity-95 text-start border-top border-secondary border-opacity-25">
                        <h5 class="fw-bold text-white mb-1" id="previewLightboxTitle"></h5>
                        <p class="text-white-50 small mb-0" id="previewLightboxDesc"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 課程介紹 -->
<section id="courseinfo" class="py-5 parallax-bg-zone text-white">
    <!-- 背景視差大圖 -->
    <img src="imageFiles/test/<?php echo rand(1, 18); ?>.jpg" class="parallax-bg">
    <!-- 70% 黑色漸層遮罩 -->
    <div class="parallax-overlay"></div>
    <div class="container py-4">
        <div class="text-center fsl-header-gap">
            <h2 class="fsl-section-title-box mb-3">課程系統介紹</h2>
            <p class="fs-5 text-white-50 mb-0">人生第五季的課程系統主要分為兩大方向</p>
            <hr class="fsl-section-divider">
        </div>

        <!-- 第一組：步步向光 -->
        <!-- 第一組：步步向光（左側圖片欄） -->
        <div class="row align-items-center mb-5 pb-lg-4">
            <div class="col-lg-6 mb-4 mb-lg-0 text-center reveal-from-left">
                <div class="fsl-book-frame mx-auto">
                    <span class="fsl-corner fsl-corner-tl"></span>
                    <span class="fsl-corner fsl-corner-tr"></span>
                    <span class="fsl-corner fsl-corner-bl"></span>
                    <span class="fsl-corner fsl-corner-br"></span>
                    <img src="imageFiles/course01-3.jpg" class="img-fluid fsl-course-book-img" alt="步步向光－人生重啟陪跑系統" onerror="this.src='imageFiles/test/<?php echo rand(1, 18); ?>.jpg'">
                </div>
            </div>
            <div class="col-lg-6 ps-lg-5 reveal-from-right">
                <div class="fsl-course-content-box">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                        <h3 class="h2 fw-bold text-white mb-0" style="letter-spacing: 3px;">步步向光</h3>
                        <span class="badge rounded-pill border border-warning text-warning bg-transparent px-3 py-2 fs-6 fw-normal" style="letter-spacing: 2px;">
                            人生重啟陪跑系統
                        </span>
                    </div>

                    <!-- 細框線外框容器（與第二組一致） -->
                    <div class="fsl-course-series-group">
                        <div class="fsl-series-header">
                            <h4 class="h5 text-warning fw-semibold mb-1" style="letter-spacing: 2px;">12堂核心人生重啟課程</h4>
                            <p class="small text-white-50 mb-0" style="letter-spacing: 1.5px;">四大秘密 × 12堂主題課程</p>
                        </div>

                        <!-- 8 項完全獨立的課程配置清單 -->
                        <ul class="list-unstyled fsl-course-feature-list mb-4">
                            <li>
                                <i class="bi bi-check2-circle text-warning"></i>
                                <span><strong>2場</strong>線上深度交流聚會</span>
                            </li>
                            <li>
                                <i class="bi bi-check2-circle text-warning"></i>
                                <span><strong>2次</strong>一對一深度陪談</span>
                            </li>
                            <li>
                                <i class="bi bi-check2-circle text-warning"></i>
                                <span><strong>七大工具系統</strong></span>
                            </li>
                            <li>
                                <i class="bi bi-check2-circle text-warning"></i>
                                <span><strong>三十多套</strong>生命整理與支持工具</span>
                            </li>
                            <li>
                                <i class="bi bi-check2-circle text-warning"></i>
                                <span><strong>16週</strong>深度陪跑</span>
                            </li>
                            <li>
                                <i class="bi bi-check2-circle text-warning"></i>
                                <span><strong>90天</strong>季行動地圖</span>
                            </li>
                            <li>
                                <i class="bi bi-check2-circle text-warning"></i>
                                <span><strong>一百多則</strong>田田姐親聲陪伴</span>
                            </li>
                            <li>
                                <i class="bi bi-check2-circle text-warning"></i>
                                <span><strong>每日向光短影音</strong></span>
                            </li>
                        </ul>

                        <!-- 暖金左框金句 -->
                        <div class="fsl-course-quote-box mb-0">
                            <p class="mb-1 text-white-50">陪妳每天完成一個小行動</p>
                            <p class="mb-0 text-warning fw-medium">一天一天看見自己開始發光</p>
                        </div>
                        <?php
                        $cid        = 10; // 對應 fsl_courses.course_id（例如：10=步步向光陪跑, 11=身心之美孵化, 12=一對一深度陪談）
                        $is_wished  = !empty($user_wished_courses[$cid]);
                        $heart_icon = $is_wished ? 'bi-heart-fill text-danger' : 'bi-heart';
                        ?>
                        <div class="d-flex align-items-center gap-2 mt-3">
                            <!-- 立即預約 / 報名課程（自動寫入 fsl_cart_items 並跳轉 5sl_myCart.php） -->
                            <button type="button"
                                class="btn btn-dark text-warning fw-bold flex-grow-1 btn-fsl-add-cart"
                                data-type="1"
                                data-course-id="<?php echo $cid; ?>"
                                data-redirect="1">
                                <i class="bi bi-calendar-check me-1"></i>立即預約報名
                            </button>

                            <!-- 一鍵愛心收藏 / 取消收藏（自動寫入 fsl_wishlists） -->
                            <button type="button"
                                class="btn btn-outline-secondary px-3 btn-fsl-wishlist"
                                data-type="1"
                                data-id="<?php echo $cid; ?>"
                                title="加入 / 取消收藏追蹤">
                                <i class="bi <?php echo $heart_icon; ?>"></i>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- 兩組系統之間的極細暖金分隔線 -->
        <hr class="fsl-course-divider my-5">

        <!-- 第二組：身心之美 -->
        <!-- 第二組：身心之美（右側圖片欄） -->
        <div class="row align-items-center pt-lg-4">
            <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0 text-center reveal-from-right">
                <div class="fsl-book-frame mx-auto">
                    <span class="fsl-corner fsl-corner-tl"></span>
                    <span class="fsl-corner fsl-corner-tr"></span>
                    <span class="fsl-corner fsl-corner-bl"></span>
                    <span class="fsl-corner fsl-corner-br"></span>
                    <img src="imageFiles/course02-3.jpg" class="img-fluid fsl-course-book-img" alt="身心之美－孵化課程系統" onerror="this.src='imageFiles/test/<?php echo rand(1, 18); ?>.jpg'">
                </div>
            </div>
            <div class="col-lg-6 order-lg-1 pe-lg-5 reveal-from-left">
                <div class="fsl-course-content-box">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                        <h3 class="h2 fw-bold text-white mb-0" style="letter-spacing: 3px;">身心之美</h3>
                        <span class="badge rounded-pill border border-warning text-warning bg-transparent px-3 py-2 fs-6 fw-normal" style="letter-spacing: 2px;">
                            孵化課程系統
                        </span>
                    </div>

                    <!-- 系列一：藝術輔療系列 -->
                    <div class="fsl-course-series-group mb-4">
                        <div class="fsl-series-header">
                            <h4 class="h5 text-warning fw-semibold mb-1" style="letter-spacing: 2px;">藝術輔療系列</h4>
                            <p class="small text-white-50 mb-0" style="letter-spacing: 1.5px;">內在探索 × 情緒療癒 × 生命整合</p>
                        </div>

                        <div class="fsl-series-items">
                            <div class="fsl-subcourse-item">
                                <span class="fsl-subcourse-num">01</span>
                                <div class="fsl-subcourse-info">
                                    <h5>生命故事藝術繪本</h5>
                                    <p>把你的一生，畫成一本溫柔的書，讓過去被理解，讓未來被祝福</p>
                                </div>
                            </div>

                            <div class="fsl-subcourse-item">
                                <span class="fsl-subcourse-num">02</span>
                                <div class="fsl-subcourse-info">
                                    <h5>IKIGAI x 幸福夢想花磚</h5>
                                    <p>找回你為什麼而活，用圖像與色彩，拼回你人生的熱愛、天賦與價值</p>
                                </div>
                            </div>

                            <div class="fsl-subcourse-item">
                                <span class="fsl-subcourse-num">03</span>
                                <div class="fsl-subcourse-info">
                                    <h5>和諧粉彩的心靈藝術之旅</h5>
                                    <p>一筆一色，為心靈卸下重量，進入柔軟、療癒、安靜的內在花園</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 系列二：創造形象魅力系列 -->
                    <div class="fsl-course-series-group">
                        <div class="fsl-series-header">
                            <h4 class="h5 text-warning fw-semibold mb-1" style="letter-spacing: 2px;">創造形象魅力系列</h4>
                            <p class="small text-white-50 mb-0" style="letter-spacing: 1.5px;">外在美 × 自信 × 人生舞台感</p>
                        </div>

                        <div class="fsl-series-items">
                            <div class="fsl-subcourse-item">
                                <span class="fsl-subcourse-num">01</span>
                                <div class="fsl-subcourse-info">
                                    <h5>做自己人生舞台的模特</h5>
                                    <p>你不是配角，你是主角，舉手投足，就是你人生的光</p>
                                </div>
                            </div>

                            <div class="fsl-subcourse-item">
                                <span class="fsl-subcourse-num">02</span>
                                <div class="fsl-subcourse-info">
                                    <h5>第五季色語權</h5>
                                    <p>專屬色彩系統，打造獨特魅力，一一找到最適合你的色彩語言，讓你的外在與內在共鳴，散發獨特的生命光彩。</p>
                                </div>
                            </div>

                            <div class="fsl-subcourse-item">
                                <span class="fsl-subcourse-num">03</span>
                                <div class="fsl-subcourse-info">
                                    <h5>身材加分穿搭技巧</h5>
                                    <p>顯瘦 × 顯年輕 × 顯氣場，學會運用剪裁、線條與配色，讓每一套穿搭都成為你的加分項。展現最美的自己。</p>
                                </div>
                            </div>

                            <div class="fsl-subcourse-item">
                                <span class="fsl-subcourse-num">04</span>
                                <div class="fsl-subcourse-info">
                                    <h5>衣櫥再造工程</h5>
                                    <p>斷捨離 × 風格定位，重新愛上鏡子裡的自己</p>
                                </div>
                            </div>
                        </div>
                    </div>


                    <?php
                    $cid        = 11; // 對應 fsl_courses.course_id（例如：10=步步向光陪跑, 11=身心之美孵化, 12=一對一深度陪談）
                    $is_wished  = !empty($user_wished_courses[$cid]);
                    $heart_icon = $is_wished ? 'bi-heart-fill text-danger' : 'bi-heart';
                    ?>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <!-- 立即預約 / 報名課程（自動寫入 fsl_cart_items 並跳轉 5sl_myCart.php） -->
                        <button type="button"
                            class="btn btn-dark text-warning fw-bold flex-grow-1 btn-fsl-add-cart"
                            data-type="1"
                            data-course-id="<?php echo $cid; ?>"
                            data-redirect="1">
                            <i class="bi bi-calendar-check me-1"></i>立即預約報名
                        </button>

                        <!-- 一鍵愛心收藏 / 取消收藏（自動寫入 fsl_wishlists） -->
                        <button type="button"
                            class="btn btn-outline-secondary px-3 btn-fsl-wishlist"
                            data-type="1"
                            data-id="<?php echo $cid; ?>"
                            title="加入 / 取消收藏追蹤">
                            <i class="bi <?php echo $heart_icon; ?>"></i>
                        </button>
                    </div>


                </div>
            </div>
        </div>
    </div>
</section>

<!-- 營運成果 -->
<!-- 
        <section class="py-5 bg-dark text-white text-center">
            <div class="container py-4">
                <div class="row g-4">
                    <div class="col-sm-6 col-md-3">
                        <h2 class="display-4 fw-bold text-warning" data-target="150">
                            <span class="stat-number" data-target="150">0</span>+
                        </h2>
                        <p class="text-white-50 mb-0">成功結案項目</p>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <h2 class="display-4 fw-bold text-warning" data-target="99.2">
                            <span class="stat-number" data-target="99.2">0</span>%
                        </h2>
                        <p class="text-white-50 mb-0">客戶滿意度</p>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <h2 class="display-4 fw-bold text-warning" data-target="1200">
                            <span class="stat-number" data-target="1200">0</span> M+
                        </h2>
                        <p class="text-white-50 mb-0">資料庫處理量</p>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <h2 class="display-4 fw-bold text-warning">24/7</h2>
                        <p class="text-white-50 mb-0">全天候技術支援</p>
                    </div>
                </div>
            </div>
        </section>
         -->
<!-- 課程&活動花絮 -->
<section id="portfolio" class="py-5 text-white" style="background-image: url('imageFiles/mainBG5.jpg');">
    <div class="container py-4">
        <div class="text-center fsl-header-gap">
            <h2 class="fsl-section-title-box mb-3">課程&amp;活動花絮</h2>
            <p class="fs-5 text-white-50 mb-0">生命中的「改變」往往只是過程中的那一微秒</p>
            <hr class="fsl-section-divider">
        </div>

        <!-- 水平滑動藝廊舞台 -->
        <div class="fsl-highlights-wrapper position-relative" id="fslHighlightsCarousel">
            <!-- 左側換圖按鈕 < -->
            <button type="button" class="fsl-carousel-nav-btn fsl-nav-prev" id="fslHighlightsPrev" aria-label="上一批花絮">
                <i class="bi bi-chevron-left"></i>
            </button>

            <!-- 可滑動軌道視窗（支援滑鼠拖曳與觸控滑動） -->
            <div class="fsl-highlights-viewport" id="fslHighlightsViewport">
                <div class="fsl-highlights-track" id="fslHighlightsTrack">
                    <?php
                    $sql_home_media = "
                                SELECT med_id, med_event_date, med_category, med_type, med_title, med_brief, med_url, med_duration
                                FROM fsl_media
                                WHERE med_status = 1
                                ORDER BY med_is_featured DESC, med_event_date DESC, med_sort ASC, med_id DESC
                                LIMIT 12
                            ";
                    $home_media_rows = isset($DB) ? $DB->query($sql_home_media) : array();
                    if (!is_array($home_media_rows)) $home_media_rows = array();

                    foreach ($home_media_rows as $row):
                        $mid       = (int)$row['med_id'];
                        $mtype     = (int)$row['med_type']; // 1=圖檔, 2=YouTube視訊, 3=聲音檔
                        $media_url = trim((string)($row['med_url'] ?? ''));
                        $has_video = ($mtype === 2 && $media_url !== '');
                        $has_audio = ($mtype === 3 && $media_url !== '');
                        $date_fmt  = !empty($row['med_event_date']) ? date('Y.m.d', strtotime($row['med_event_date'])) : '';

                        // 優先讀取後台上傳縮圖與主圖 (./5sladmin/uploadFiles/md/mdimg_{id}_thumb.jpg)
                        $thumb_disk = './5sladmin/uploadFiles/md/mdimg_' . $mid . '_thumb.jpg';
                        $main_disk  = './5sladmin/uploadFiles/md/mdimg_' . $mid . '_.jpg';
                        $fallback   = 'imageFiles/test/' . ((($mid - 1) % 18) + 1) . '.jpg';

                        $thumb_url = file_exists($thumb_disk) ? ($thumb_disk . '?v=' . filemtime($thumb_disk)) : (file_exists($main_disk) ? ($main_disk . '?v=' . filemtime($main_disk)) : $fallback);
                        $main_url  = file_exists($main_disk)  ? ($main_disk . '?v=' . filemtime($main_disk))   : $thumb_url;
                    ?>
                        <div class="fsl-highlight-item portfolio-card-wrap">
                            <div class="card border-0 shadow-sm overflow-hidden portfolio-card fsl-highlight-card"
                                style="cursor: pointer;"
                                data-media-type="<?php echo $mtype; ?>"
                                data-main-img="<?php echo htmlspecialchars($main_url); ?>"
                                data-media-url="<?php echo htmlspecialchars($media_url); ?>"
                                data-title="<?php echo htmlspecialchars($row['med_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-desc="<?php echo htmlspecialchars($row['med_brief'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                data-date="<?php echo htmlspecialchars($date_fmt); ?>">

                                <?php if ($date_fmt !== ''): ?>
                                    <span class="badge bg-dark bg-opacity-75 text-warning border border-warning border-opacity-50 position-absolute top-0 end-0 m-3 px-2 py-1" style="z-index: 4; font-size: 0.72rem; letter-spacing: 1px;">
                                        <?php echo $date_fmt; ?>
                                    </span>
                                <?php endif; ?>

                                <img src="<?php echo htmlspecialchars($thumb_url); ?>"
                                    class="card-img-top"
                                    alt="<?php echo htmlspecialchars($row['med_title']); ?>"
                                    draggable="false"
                                    onerror="this.src='imageFiles/test/1.jpg'">

                                <?php if ($has_video): ?>
                                    <button type="button"
                                        class="video-play-btn fsl-card-play-btn"
                                        style="position: absolute !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important; z-index: 5;"
                                        aria-label="播放影片">
                                        <div class="play-icon-triangle"></div>
                                        <div class="play-btn-ripple"></div>
                                    </button>
                                <?php elseif ($has_audio): ?>
                                    <button type="button"
                                        class="video-play-btn fsl-card-play-btn"
                                        style="position: absolute !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important; display: flex !important; align-items: center !important; justify-content: center !important; background: rgba(13, 202, 240, 0.92) !important; border-color: #ffffff !important; z-index: 5;"
                                        aria-label="聆聽音頻">
                                        <i class="bi bi-headphones text-dark fs-3" style="line-height: 1; margin: 0;"></i>
                                        <div class="play-btn-ripple"></div>
                                    </button>
                                <?php endif; ?>

                                <div class="card-body portfolio-overlay text-white">
                                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($row['med_title']); ?></h6>
                                    <p class="text-white-50 small mb-0"><?php echo htmlspecialchars($row['med_brief'] ?? ''); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>

            <!-- 右側換圖按鈕 > -->
            <button type="button" class="fsl-carousel-nav-btn fsl-nav-next" id="fslHighlightsNext" aria-label="下一批花絮">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        <!-- 滑動圖片群下方正中央：「更多花絮」按鈕 -->
        <div class="text-center mt-5">
            <a href="5sl_highlights.php" class="btn btn-outline-light rounded-3 px-5 py-2 fw-semibold fsl-read-more-btn">
                更多花絮 <i class="bi bi-chevron-right ms-1"></i>
            </a>
        </div>
    </div>
</section>

<!-- 價格方案 -->
<section id="pricing" class="py-5 parallax-bg-zone text-white">
    <!-- 背景視差大圖 -->
    <img src="imageFiles/test/<?php echo rand(1, 18); ?>.jpg" class="parallax-bg">
    <!-- 70% 黑色漸層遮罩 -->
    <div class="parallax-overlay"></div>
    <div class="container py-4">
        <div class="text-center fsl-header-gap">
            <h2 class="fsl-section-title-box mb-3">課程價格方案</h2>
            <p class="fs-5 text-white-50 mb-0">根據您的生命階段與需求，選擇最適合的專屬引導方案</p>
            <hr class="fsl-section-divider">
        </div>

        <!-- 價格方案景深輪播舞台 -->
        <div class="fsl-pricing-carousel position-relative" id="fslPricingCarousel">
            <!-- 左側翻頁按鈕 -->
            <button type="button" class="fsl-carousel-nav-btn fsl-nav-prev" id="fslPricingPrev" aria-label="上一個方案">
                <i class="bi bi-chevron-left"></i>
            </button>

            <!-- 視窗裁切與滑動感應區 -->
            <div class="fsl-pricing-viewport" id="fslPricingViewport">
                <div class="fsl-pricing-stage" id="fslPricingStage">
                    <?php
                    $sql_pricing = "
                                SELECT course_id, parent_id, course_badge_text, course_subtitle, course_title, course_subscript,
                                    course_brief, course_cost, course_price_unit, course_tier_class, course_is_hot
                                FROM fsl_courses
                                WHERE course_status = 1 AND course_show_pricing = 1
                                ORDER BY course_sort ASC, course_id ASC
                            ";
                    $pricing_cards = isset($DB) ? $DB->query($sql_pricing) : array();
                    if (!is_array($pricing_cards)) $pricing_cards = array();

                    foreach ($pricing_cards as $pc):
                        $card_id    = (int)$pc['course_id'];
                        $parent_id  = (int)$pc['parent_id'];
                        // 主課程 ID：供愛心收藏、梯次與封面圖繼承使用
                        $master_id  = ($parent_id > 0) ? $parent_id : $card_id;

                        $tier_class = trim((string)($pc['course_tier_class'] ?? 'plan-tier-standard'));
                        $is_hottest = ((int)($pc['course_is_hot'] ?? 0) === 1 || $tier_class === 'plan-tier-premium');
                        $badge_txt  = trim((string)($pc['course_badge_text'] ?? ''));
                        $sub_top    = trim((string)($pc['course_subtitle'] ?? ''));
                        $sub_bot    = trim((string)($pc['course_subscript'] ?? ''));
                        $unit_txt   = trim((string)($pc['course_price_unit'] ?? ''));
                        $price_num  = (int)round((float)$pc['course_cost']);

                        $is_plan_wished = !empty($user_wished_courses[$master_id]);
                    ?>
                        <div class="fsl-plan-slide <?php echo $is_hottest ? 'hottest' : ''; ?>">
                            <div class="card h-100 fsl-plan-card <?php echo htmlspecialchars($tier_class, ENT_QUOTES, 'UTF-8'); ?> text-center p-4">
                                <div class="card-body d-flex flex-column">
                                    <div class="fsl-plan-header mb-3">
                                        <?php if ($badge_txt !== ''): ?>
                                            <span class="fsl-plan-badge"><?php echo htmlspecialchars($badge_txt, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>

                                        <?php if ($sub_top !== ''): ?>
                                            <p class="fsl-plan-subtitle small mt-2 mb-0"><?php echo htmlspecialchars($sub_top, ENT_QUOTES, 'UTF-8'); ?></p>
                                        <?php endif; ?>

                                        <h4 class="fsl-plan-title fw-bold mt-2 mb-1"><?php echo htmlspecialchars($pc['course_title'], ENT_QUOTES, 'UTF-8'); ?></h4>

                                        <?php if ($sub_bot !== ''): ?>
                                            <p class="fsl-plan-subtitle small mb-0"><?php echo htmlspecialchars($sub_bot, ENT_QUOTES, 'UTF-8'); ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="fsl-plan-price-wrap my-3">
                                        <span class="fsl-plan-currency">NT$</span>
                                        <span class="fsl-plan-price"><?php echo number_format($price_num); ?></span>
                                        <?php if ($unit_txt !== ''): ?>
                                            <span class="fsl-plan-unit">/ <?php echo htmlspecialchars($unit_txt, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <p class="fsl-plan-desc small my-3 flex-grow-1">
                                        <?php echo nl2br(htmlspecialchars(strip_tags($pc['course_brief'] ?? ''), ENT_QUOTES, 'UTF-8')); ?>
                                    </p>

                                    <!-- 預約按鈕傳入本卡片 ID ($card_id)；愛心收藏傳入主課程 ID ($master_id) -->
                                    <div class="mt-auto pt-3 d-flex align-items-center gap-2">
                                        <button type="button"
                                            class="btn fsl-plan-btn flex-grow-1 py-2 fw-bold btn-home-book-course"
                                            data-course-id="<?php echo $card_id; ?>">
                                            <i class="bi bi-calendar-check me-1"></i>我要預約
                                        </button>
                                        <button type="button"
                                            class="btn btn-outline-light py-2 px-3 btn-fsl-wishlist"
                                            data-type="1"
                                            data-id="<?php echo $master_id; ?>"
                                            title="加入收藏追蹤">
                                            <i class="bi <?php echo $is_plan_wished ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>

            <!-- 右側翻頁按鈕 -->
            <button type="button" class="fsl-carousel-nav-btn fsl-nav-next" id="fslPricingNext" aria-label="下一個方案">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        <!-- 底部頁碼指示點 -->
        <div class="d-flex justify-content-center gap-2 mt-4" id="fslPricingDots"></div>
    </div>
</section>

<!-- 顧問&講師 imageFiles/mainBG2.jpg -->
<section id="mentor" class="py-5 text-white static-bg-zone" style="background-image: url('imageFiles/mainBG2.jpg');">
    <div class="container py-4">

        <!-- 區塊主標題（遵循全站 2.4 秒微幅呼吸細線外框與 90px 區隔間距規範） -->
        <div class="text-center fsl-header-gap">
            <h2 class="fsl-section-title-box mb-3">顧問&amp;講師</h2>
            <hr class="fsl-section-divider">
        </div>

        <!-- 上半部：田玫琪老師（田田姐）形象照 + 經歷介紹 -->
        <div class="row align-items-center g-4 g-lg-5 mb-5 pb-lg-3">
            <!-- 左側講師形象照（含鎏金四角裝飾與不裁切保護） -->
            <div class="col-lg-5 text-center reveal-from-left">
                <div class="fsl-instructor-frame mx-auto">
                    <span class="fsl-corner fsl-corner-tl"></span>
                    <span class="fsl-corner fsl-corner-tr"></span>
                    <span class="fsl-corner fsl-corner-bl"></span>
                    <span class="fsl-corner fsl-corner-br"></span>
                    <img src="imageFiles/reinaphoto01.jpg" class="img-fluid fsl-instructor-img" alt="田玫琪老師｜田田姐 Reina Tien" onerror="this.src='imageFiles/test/<?php echo rand(1, 18); ?>.jpg'">
                </div>
            </div>

            <!-- 右側講師頭銜與四段生平經歷 -->
            <div class="col-lg-7 ps-lg-4 reveal-from-right">
                <div class="fsl-instructor-content">

                    <div class="d-flex flex-wrap align-items-baseline gap-3 mb-2">
                        <h3 class="h2 fw-bold text-white mb-0" style="letter-spacing: 3px;">田玫琪老師 ｜ 田田姐</h3>
                        <span class="text-warning fs-5 fst-italic" style="font-family: Georgia, 'Times New Roman', serif; letter-spacing: 1.5px;">Reina Tien</span>
                    </div>

                    <div class="mb-4">
                        <p class="text-white fw-medium mb-2" style="letter-spacing: 1.5px;">人生第五季生命藝術工作室創辦人</p>
                        <span class="badge rounded-pill border border-warning text-warning bg-transparent px-3 py-2 fs-6 fw-normal" style="letter-spacing: 1.5px;">
                            生命故事孵化師 x 藝術輔療師 x 形象魅力導師
                        </span>
                    </div>

                    <div class="fsl-instructor-bio text-white-50">
                        <p>田玫琪老師長年投入服裝設計 形象美學 模特兒訓練 服裝秀導演 企業培訓與大專院校教學，曾擔任佳音電台單元節目主講人，並出版《職場名模》一書。</p>
                        <p>她曾陪伴不同年齡 不同背景與不同生命階段的人，透過形象整理 儀態訓練 舞台展現 生命探索與藝術陪伴，重新看見自己，找回內在力量。</p>
                        <p>走過 T 台 講台 公益舞台與心靈成長的生命歷程後，田玫琪老師以《人生第五季》為核心，推動一種屬於熟齡女性的新生命文化。</p>
                    </div>

                    <!-- 暖金左框核心使命金句（已加上 mb-4 與下方書冊區塊拉開距離） -->
                    <div class="fsl-course-quote-box mt-4 mb-4">
                        <p class="mb-1 text-white-50">陪伴走過人生前半場的女性，重新看見自己，舒理生命，找回方向</p>
                        <p class="mb-0 text-warning fw-medium">活出有光 有愛 有力量的第五季人生</p>
                    </div>

                    <!-- 代表著作《職場名模》典藏展示卡（已移至講師介紹文字正下方） -->
                    <div class="fsl-book-feature-card mt-2">
                        <div class="row align-items-center g-3">
                            <div class="col-sm-4 text-center">
                                <img src="imageFiles/reinabook01.jpg" class="img-fluid fsl-reina-book-img" alt="職場名模－田玫琪老師代表著作" onerror="this.src='imageFiles/test/<?php echo rand(1, 18); ?>.jpg'">
                            </div>
                            <div class="col-sm-8 ps-sm-3">
                                <div class="mb-2">
                                    <span class="badge rounded-pill bg-warning text-dark px-3 py-1 fw-semibold" style="letter-spacing: 2px;">代表著作</span>
                                </div>
                                <h4 class="h3 fw-bold text-white mb-2" style="letter-spacing: 3px;">職場名模</h4>
                                <p class="text-warning fs-6 mb-2" style="letter-spacing: 1.5px;">一本分享職場即舞台的技巧</p>
                                <p class="text-white-50 small lh-lg mb-0" style="letter-spacing: 0.8px;">
                                    田玫琪老師代表著作，融合形象美學、職場禮儀與自信表達，協助讀者提升專業形象與個人魅力，成為職場與人生舞台上最美好的自己。
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 常見問題 FAQ（10 題完整版 + 分類快速篩選標籤｜PHP 陣列迴圈優化版） -->
<?php include_once '5sl_include_qa.php'; ?>
<section id="faqSection" class="py-5 text-white static-bg-zone" style="background-image: url('imageFiles/test/<?php echo rand(1, 18); ?>.jpg');">
    <div class="container py-4" style="max-width: 840px;">
        <div class="text-center fsl-header-gap">
            <h2 class="fsl-section-title-box mb-3">常見問題 FAQ</h2>
            <p class="fs-5 text-white-50 mb-0">為您解答關於人生第五季引導課程、方案預約與會員功能的常見疑問</p>
            <hr class="fsl-section-divider">
        </div>

        <!-- 分類快速篩選標籤列 -->
        <div class="d-flex flex-wrap justify-content-center gap-2 mb-4" id="fslFaqFilterBar">
            <button type="button" class="btn btn-sm fsl-faq-filter-btn active" data-faq-filter="all">
                全部問題 (<?php echo $total_faq_count; ?>)
            </button>
            <?php foreach ($faq_categories as $cat_key => $cat_label): ?>
                <button type="button" class="btn btn-sm fsl-faq-filter-btn" data-faq-filter="<?php echo htmlspecialchars($cat_key, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($cat_label, ENT_QUOTES, 'UTF-8'); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- 10 題問答手風琴列表（淺色底深色字高對比版） -->
        <div class="accordion fsl-faq-accordion" id="faqAccordion">
            <?php foreach ($faq_items as $index => $faq):
                $q_num       = $index + 1;
                $heading_id  = 'faq-heading-' . $q_num;
                $collapse_id = 'faq-collapse-' . $q_num;
                $is_first    = ($index === 0);
            ?>
                <!-- Q<?php echo $q_num; ?> -->
                <div class="accordion-item shadow-sm mb-3 rounded-3 overflow-hidden"
                    data-faq-cat="<?php echo htmlspecialchars($faq['cat'], ENT_QUOTES, 'UTF-8'); ?>">
                    <h2 class="accordion-header" id="<?php echo $heading_id; ?>">
                        <button class="accordion-button <?php echo $is_first ? '' : 'collapsed'; ?> fw-bold"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#<?php echo $collapse_id; ?>"
                            aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>"
                            aria-controls="<?php echo $collapse_id; ?>">
                            <span class="badge bg-warning text-dark me-3">Q<?php echo $q_num; ?></span>
                            <?php echo htmlspecialchars($faq['question'], ENT_QUOTES, 'UTF-8'); ?>
                        </button>
                    </h2>
                    <div id="<?php echo $collapse_id; ?>"
                        class="accordion-collapse collapse <?php echo $is_first ? 'show' : ''; ?>"
                        aria-labelledby="<?php echo $heading_id; ?>"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body border-top border-warning border-opacity-25 lh-lg">
                            <?php echo $faq['answer']; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- 與我們取得聯繫
<section id="contactus" class="py-5 text-white" style="background-image: url('imageFiles/mainBG4.jpg');"> -->
<section id="contactus" class="py-5 text-dark">
    <div class="container py-4" style="max-width: 650px;">
        <div class="text-center fsl-header-gap">
            <h2 class="fsl-section-title-box bg-white bg-opacity-50 mb-3">與我們取得聯繫</h2>
            <p class="fs-5 text-dark-50 mb-0">歡迎留下您的需求，我們將儘速回覆</p>
            <hr class="fsl-section-divider">
        </div>
        <form action="thank-you.php" method="POST" class="card bg-dark bg-opacity-50 text-white border border-secondary border-opacity-25 shadow-sm p-4 p-md-5">
            <div class="mb-3">
                <label class="form-label fw-bold small text-white-50">您的姓名</label>
                <input type="text" class="form-control border-secondary" name="username" placeholder="例如：王小明" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold small text-white-50">電子信箱</label>
                <input type="email" class="form-control border-secondary" name="email" placeholder="example@email.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold small text-white-50">諮詢項目</label>
                <select class="form-select border-secondary" name="project_type">
                    <option selected disabled>請選擇您的需求項目...</option>
                    <option value="1">xxxx</option>
                    <option value="2">xxxx</option>
                    <option value="3">xxxx</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold small text-white-50">需求描述</label>
                <textarea class="form-control border-secondary" name="message" rows="4" placeholder="請填寫您希望建置的功能或大方向..."></textarea>
            </div>
            <button type="submit" class="btn btn-warning text-dark w-100 fw-bold py-2">確認送出表單</button>
        </form>
    </div>
</section>

<!-- Footer 之前留白區 -->
        <section class="py-5">
            <p>&nbsp;</p>
        </section>
        
<!-- 首頁聲音檔播放 Lightbox -->
<div class="modal fade" id="homeAudioLightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border border-warning shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-secondary">
                <span class="badge bg-info text-dark fw-bold me-2"><i class="bi bi-soundwave me-1"></i>AUDIO STORY</span>
                <span class="small text-warning fw-bold" id="homeAudioDate"></span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <img src="" id="homeAudioCover" class="rounded-3 shadow mb-3 border border-secondary" style="width: 160px; height: 160px; object-fit: cover;" alt="音頻封面">
                <h5 class="fw-bold text-white mb-1" id="homeAudioTitle"></h5>
                <p class="text-white-50 small mb-4" id="homeAudioDesc"></p>
                <audio controls id="homeAudioPlayer" class="w-100">
                    <source src="" type="audio/mpeg">
                </audio>
            </div>
        </div>
    </div>
</div>
<!-- 首頁課程與價格方案預約確認彈窗（含主圖、簡介、價格、梯次選擇與同意/放棄） -->
<div class="modal fade" id="homeCourseConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="position-relative">
                <img id="hcModalCover" src="" class="w-100" style="height: 200px; object-fit: cover;" alt="課程主圖">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 bg-dark p-2 rounded-circle" data-bs-dismiss="modal" aria-label="Close"></button>
                <span class="badge bg-warning text-dark fw-bold position-absolute bottom-0 start-0 m-3 px-3 py-1 shadow-sm" id="hcModalTypeBadge">課程方案</span>
            </div>
            <div class="modal-body p-4 text-dark">
                <h5 class="fw-bold text-dark mb-2" id="hcModalTitle">-</h5>
                <p class="text-muted small lh-lg mb-3" id="hcModalBrief">-</p>

                <div class="p-3 bg-light rounded-3 border mb-3 d-flex justify-content-between align-items-center">
                    <span class="small fw-bold text-muted">課程／服務費用</span>
                    <span class="fs-4 fw-bold text-danger" id="hcModalPrice">NT$ 0</span>
                </div>

                <div class="mb-2" id="hcModalSessionWrap">
                    <label for="hcModalSessionSelect" class="form-label small fw-bold text-dark">
                        選擇報名梯次 / 預約時段 <span class="text-danger">*</span>
                    </label>
                    <select class="form-select form-select-sm" id="hcModalSessionSelect"></select>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>放棄返回
                </button>
                <button type="button" class="btn btn-dark text-warning fw-bold btn-sm px-4" id="btnHomeConfirmAddCart">
                    <i class="bi bi-check2-circle me-1"></i>同意並前往購物車結帳
                </button>
            </div>
        </div>
    </div>
</div>


<style>

    body {
        background-image: url("imageFiles/mainBG-3.jpg");
        background-attachment: fixed;
        background-size: cover;
        background-position: center left;
        background-repeat: no-repeat;
    }

    /* A. 網頁元素滾動智慧緩入 (Reveal On Scroll) */
    .reveal-on-scroll {
        opacity: 0;
        transform: translateY(60px);
        transition: opacity 1.8s cubic-bezier(0.1, 0.8, 0.2, 1), transform 1.8s cubic-bezier(0.1, 0.8, 0.2, 1);
        will-change: opacity, transform;
    }

    .reveal-from-left {
        opacity: 0;
        transform: translateX(-60px);
        transition: opacity 1.8s cubic-bezier(0.1, 0.8, 0.2, 1), transform 1.8s cubic-bezier(0.1, 0.8, 0.2, 1);
        will-change: opacity, transform;
    }

    .reveal-from-right {
        opacity: 0;
        transform: translateX(60px);
        transition: opacity 1.8s cubic-bezier(0.1, 0.8, 0.2, 1), transform 1.8s cubic-bezier(0.1, 0.8, 0.2, 1);
        will-change: opacity, transform;
    }

    .reveal-on-scroll.active,
    .reveal-from-left.active,
    .reveal-from-right.active {
        opacity: 1;
        transform: translate(0, 0);
    }

    .delay-1 {
        transition-delay: 0.1s;
    }

    .delay-2 {
        transition-delay: 0.3s;
    }

    .delay-3 {
        transition-delay: 0.5s;
    }

    .delay-4 {
        transition-delay: 0.7s;
    }

    /* B. 原生滾動視差景深引擎 (Parallax Scrolling Layout) */
    .parallax-bg-zone {
        position: relative;
        overflow: hidden;
        background-color: #000;
    }

    .parallax-bg-zone .parallax-bg {
        position: absolute;
        top: -40%;
        left: 0;
        width: 100%;
        height: 180%;
        object-fit: cover;
        z-index: 1;
        will-change: transform;
        pointer-events: none;
    }

    .parallax-bg-zone .parallax-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.55) 0%, rgba(0, 0, 0, 0.4) 100%);
        z-index: 2;
    }

    .parallax-bg-zone .container {
        position: relative;
        z-index: 3;
    }

    /* C. 第一區塊 (#whyneed)：五行情境依序滑入與 READ MORE 按鈕 */
    .fsl-subtle-divider {
        width: 100%;
        max-width: 560px;
        border: 0;
        border-top: 1px solid rgba(255, 255, 255, 0.28);
        opacity: 1;
    }

    .fsl-stagger-group.reveal-on-scroll {
        opacity: 1;
        transform: none;
        transition: none;
    }

    .fsl-fifth-season-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .fsl-list-item {
        opacity: 0;
        transform: translateX(-45px);
        transition: opacity 0.85s cubic-bezier(0.16, 1, 0.3, 1), transform 0.85s cubic-bezier(0.16, 1, 0.3, 1);
        will-change: opacity, transform;
    }

    .fsl-stagger-group.active .fsl-list-item {
        opacity: 1;
        transform: translateX(0);
    }

    .fsl-stagger-group.active .fsl-list-item:nth-child(1) {
        transition-delay: 0.15s;
    }

    .fsl-stagger-group.active .fsl-list-item:nth-child(2) {
        transition-delay: 0.35s;
    }

    .fsl-stagger-group.active .fsl-list-item:nth-child(3) {
        transition-delay: 0.55s;
    }

    .fsl-stagger-group.active .fsl-list-item:nth-child(4) {
        transition-delay: 0.75s;
    }

    .fsl-stagger-group.active .fsl-list-item:nth-child(5) {
        transition-delay: 0.95s;
    }

    .fsl-item-inner {
        display: flex;
        align-items: center;
        font-size: calc(1.25rem + 0.45vw);
        font-weight: 700;
        line-height: 1.5;
        letter-spacing: 1.5px;
        color: rgba(255, 255, 255, 0.72);
        padding: 6px 16px 6px 6px;
        border-radius: 50px;
        cursor: pointer;
        transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94), color 0.3s ease, background-color 0.3s ease;
    }

    @media (min-width: 1200px) {
        .fsl-item-inner {
            font-size: 1.5rem;
        }
    }

    .fsl-circle-icon {
        width: 34px;
        height: 34px;
        min-width: 34px;
        border-radius: 50%;
        border: 1.5px solid rgba(255, 193, 7, 0.6);
        background-color: rgba(255, 193, 7, 0.12);
        color: rgba(255, 193, 7, 0.85);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        margin-right: 16px;
        transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease, border-color 0.3s ease;
    }

    .fsl-item-inner:hover {
        transform: translateX(12px);
        color: rgba(255, 255, 255, 0.95);
        background-color: rgba(255, 255, 255, 0.08);
    }

    .fsl-item-inner:hover .fsl-circle-icon {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #16181b;
        transform: scale(1.1);
    }

    .fsl-btn-reveal-wrap {
        opacity: 0;
        transform: translateY(15px);
        transition: opacity 0.8s ease, transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        will-change: opacity, transform;
    }

    .fsl-stagger-group.active .fsl-btn-reveal-wrap {
        opacity: 1;
        transform: translateY(0);
        transition-delay: 1.65s;
    }

    .fsl-read-more-btn {
        letter-spacing: 1.5px;
        border-width: 1.5px;
        transition: all 0.3s ease;
    }

    .fsl-read-more-btn:hover {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #16181b !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 193, 7, 0.3);
    }

    .fsl-read-more-btn i {
        display: inline-block;
        transition: transform 0.3s ease;
    }

    .fsl-read-more-btn:hover i {
        transform: translateX(4px);
    }

    @media (max-width: 575.98px) {
        .fsl-fifth-season-list {
            gap: 0.85rem;
        }

        .fsl-item-inner {
            font-size: 1.25rem;
            letter-spacing: 1px;
            line-height: 1.45;
        }

        .fsl-circle-icon {
            width: 28px;
            height: 28px;
            min-width: 28px;
            font-size: 0.85rem;
            margin-right: 12px;
        }
    }

    /* D. 第二區塊 (#coreservices)：四欄引導卡片與品牌核心宣言三欄 */
    .fsl-guide-section-dark {
        position: relative;
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
    }

    .fsl-guide-section-dark::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.55);
        z-index: 1;
    }

    .fsl-guide-card {
        background-color: rgba(255, 255, 255, 0.72) !important;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1.5px solid rgba(255, 255, 255, 0.85) !important;
        border-radius: 16px;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.16), 0 2px 6px rgba(0, 0, 0, 0.08), inset 0 1px 1px rgba(255, 255, 255, 0.9) !important;
        transition: transform 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94), box-shadow 0.35s ease, background-color 0.35s ease, border-color 0.35s ease;
    }

    .fsl-guide-card:hover {
        background-color: rgba(255, 255, 255, 0.88) !important;
        border-color: rgba(255, 193, 7, 0.85) !important;
        box-shadow: 0 22px 44px rgba(0, 0, 0, 0.24), 0 4px 12px rgba(255, 193, 7, 0.18), inset 0 1px 2px rgba(255, 255, 255, 1) !important;
    }

    .fsl-guide-icon {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        background-color: #212529;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        line-height: 1;
        box-shadow: 0 10px 24px rgba(33, 37, 41, 0.22);
        transition: transform 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94), background-color 0.35s ease, color 0.35s ease, box-shadow 0.35s ease;
    }

    .fsl-guide-card:hover .fsl-guide-icon {
        background-color: #ffc107;
        color: #16181b;
        transform: translateY(-4px) scale(1.06);
        box-shadow: 0 12px 28px rgba(255, 193, 7, 0.38);
    }

    .fsl-guide-title {
        white-space: nowrap;
        font-size: clamp(0.95rem, 1.15vw, 1.18rem);
        letter-spacing: 0.5px;
        color: #16181b;
        line-height: 1.4;
    }

    @media (max-width: 991.98px) {
        .fsl-guide-title {
            font-size: 1.2rem;
            letter-spacing: 1px;
        }
    }

    .fsl-credo-section {
        position: relative;
        border-top: 1px solid rgba(255, 193, 7, 0.16);
    }

    .fsl-credo-card {
        position: relative;
        padding: 36px 32px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.025) 0%, rgba(255, 255, 255, 0.005) 100%);
        border: 1px solid rgba(255, 193, 7, 0.15);
        border-radius: 12px;
        transition: transform 0.4s ease, border-color 0.4s ease, background 0.4s ease;
    }

    .fsl-credo-card:hover {
        transform: translateY(-4px);
        border-color: rgba(255, 193, 7, 0.45);
        background: linear-gradient(180deg, rgba(255, 193, 7, 0.04) 0%, rgba(255, 255, 255, 0.01) 100%);
    }

    .fsl-credo-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-bottom: 18px;
        margin-bottom: 24px;
        border-bottom: 1px solid rgba(255, 193, 7, 0.25);
    }

    .fsl-credo-icon {
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: rgba(255, 193, 7, 0.14);
        border: 1px solid rgba(255, 193, 7, 0.4);
        color: #ffc107;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        line-height: 1;
        transition: background 0.35s ease, color 0.35s ease, transform 0.35s ease;
    }

    .fsl-credo-icon i {
        transform: translateX(1px);
    }

    .fsl-credo-card:hover .fsl-credo-icon {
        background: #ffc107;
        color: #121316;
        transform: translateX(3px);
    }

    .fsl-credo-title {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 200;
        color: #ffffff;
        letter-spacing: 4px;
    }

    .fsl-credo-body {
        color: rgba(255, 255, 255, 0.78);
        font-size: 1.02rem;
        line-height: 2;
        letter-spacing: 1.2px;
    }

    .fsl-credo-body p {
        margin-bottom: 0;
    }

    .fsl-credo-lead {
        color: #ffffff;
        font-size: 1.12rem;
        font-weight: 500;
        margin-bottom: 6px !important;
    }

    .fsl-credo-divider {
        width: 36px;
        height: 1px;
        margin: 20px 0;
        border: 0;
        background-color: #ffc107;
        opacity: 0.55;
        transition: width 0.4s ease;
    }

    .fsl-credo-card:hover .fsl-credo-divider {
        width: 56px;
    }

    .fsl-credo-accent {
        display: inline-block;
        margin-top: 4px;
        color: #ffc107;
        font-weight: 500;
    }

    @media (max-width: 991.98px) {
        .fsl-credo-card {
            padding: 28px 24px;
        }

        .fsl-credo-icon {
            width: 30px;
            height: 30px;
            font-size: 0.9rem;
        }

        .fsl-credo-title {
            font-size: 1.55rem;
        }
    }

    /* E. 價格方案景深輪播與四種階級樣式 (#pricing) */
    .fsl-pricing-carousel {
        position: relative;
        padding: 10px 0;
        user-select: none;
    }

    .fsl-pricing-viewport {
        position: relative;
        width: 100%;
        height: 510px;
        overflow: hidden;
        cursor: grab;
        touch-action: pan-y;
    }

    .fsl-pricing-viewport:active {
        cursor: grabbing;
    }

    .fsl-pricing-stage {
        position: relative;
        width: 100%;
        height: 100%;
    }

    .fsl-plan-slide {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 330px;
        height: 470px !important;
        margin-left: -165px;
        margin-top: -235px;
        transition: transform 0.55s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.55s cubic-bezier(0.22, 1, 0.36, 1);
        will-change: transform, opacity;
    }

    .fsl-plan-slide.is-side-clickable {
        cursor: pointer;
    }

    .fsl-plan-slide.is-side-clickable:hover {
        opacity: 0.95 !important;
    }

    .fsl-plan-slide.is-far-blur .card-body>* {
        opacity: 0 !important;
        visibility: hidden !important;
        transition: opacity 0.25s ease;
    }

    .fsl-plan-card {
        height: 100% !important;
        padding: 1.2rem 1.15rem !important;
        border-radius: 18px;
        overflow: hidden;
        display: flex !important;
        flex-direction: column !important;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .fsl-plan-card .card-body {
        height: 100% !important;
        min-height: 0 !important;
        padding: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
    }

    .fsl-plan-header {
        margin-bottom: 0.35rem !important;
        flex-shrink: 0 !important;
    }

    .fsl-plan-price-wrap {
        flex-shrink: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: baseline !important;
        justify-content: center !important;
        gap: 0.15rem 0.35rem !important;
        margin-top: 0.55rem !important;
        margin-bottom: 0.65rem !important;
        padding: 0.52rem 0 !important;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .fsl-plan-badge {
        display: inline-block;
        font-size: 0.75rem;
        letter-spacing: 1.5px;
        padding: 0.3rem 0.9rem;
        border-radius: 50px;
    }

    .fsl-plan-title {
        font-size: 1.35rem;
        letter-spacing: 1px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        background-color: rgba(0, 0, 0, 0.26);
        border-radius: 10px;
        padding: 0.42rem 0.75rem !important;
        margin-top: 0.42rem !important;
        margin-bottom: 0.32rem !important;
    }

    .fsl-plan-currency,
    .fsl-plan-price,
    .fsl-plan-unit {
        white-space: nowrap !important;
    }

    .fsl-plan-currency {
        font-size: 1rem;
        font-weight: 600;
    }

    .fsl-plan-price {
        font-size: 2.75rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.5px;
    }

    .fsl-plan-unit {
        font-size: 0.85rem;
    }

    .fsl-plan-desc {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        overflow: hidden !important;
        display: -webkit-box !important;
        -webkit-box-orient: vertical !important;
        line-clamp: 5 !important;
        -webkit-line-clamp: 5 !important;
        line-height: 1.55 !important;
        margin-top: 0.2rem !important;
        margin-bottom: 0.45rem !important;
    }

    .fsl-plan-card .mt-auto {
        margin-top: auto !important;
        flex-shrink: 0 !important;
        padding-top: 0.45rem !important;
    }

    .fsl-plan-btn {
        border-radius: 10px;
        letter-spacing: 1.5px;
        transition: all 0.3s ease;
    }

    #fslPricingStage .fsl-plan-spacer .fsl-plan-card {
        background: linear-gradient(155deg, rgba(255, 255, 255, 0.16) 0%, rgba(255, 193, 7, 0.08) 100%) !important;
        border: 2px dashed rgba(255, 193, 7, 0.55) !important;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5), inset 0 0 24px rgba(255, 193, 7, 0.08) !important;
    }

    #fslPricingStage .fsl-plan-spacer .card-body::after {
        content: "FIFTH SEASON LIFE";
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: rgba(255, 193, 7, 0.42);
        font-family: 'Montserrat', sans-serif;
        font-size: 0.9rem;
        font-weight: 700;
        letter-spacing: 3px;
    }

    .plan-tier-basic {
        background: linear-gradient(180deg, #1c1f23 0%, #16181b 100%) !important;
        border: 1px solid rgba(255, 255, 255, 0.14) !important;
        color: #ced4da !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
    }

    .plan-tier-basic .fsl-plan-badge {
        background-color: rgba(255, 255, 255, 0.06);
        color: #adb5bd;
    }

    .plan-tier-basic .fsl-plan-title {
        color: #e9ecef;
        background-color: rgba(0, 0, 0, 0.32) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    .plan-tier-basic .fsl-plan-subtitle,
    .plan-tier-basic .fsl-plan-unit,
    .plan-tier-basic .fsl-plan-desc {
        color: #868e96;
    }

    .plan-tier-basic .fsl-plan-price,
    .plan-tier-basic .fsl-plan-currency {
        color: #dee2e6;
    }

    .plan-tier-basic .fsl-plan-btn {
        background-color: transparent;
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: #ced4da;
    }

    .plan-tier-basic .fsl-plan-btn:hover {
        background-color: rgba(255, 255, 255, 0.12);
        border-color: #ffffff;
        color: #ffffff;
    }

    .plan-tier-standard {
        background: linear-gradient(165deg, #282d34 0%, #1b1e23 100%) !important;
        border: 1.5px solid rgba(255, 255, 255, 0.36) !important;
        color: #f8f9fa !important;
        box-shadow: 0 14px 34px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.18);
    }

    .plan-tier-standard .fsl-plan-badge {
        background-color: rgba(255, 255, 255, 0.14);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }

    .plan-tier-standard .fsl-plan-title {
        color: #ffffff;
        background-color: rgba(255, 255, 255, 0.08) !important;
        border-color: rgba(255, 255, 255, 0.24) !important;
    }

    .plan-tier-standard .fsl-plan-subtitle,
    .plan-tier-standard .fsl-plan-unit {
        color: #adb5bd;
    }

    .plan-tier-standard .fsl-plan-desc {
        color: #ced4da;
    }

    .plan-tier-standard .fsl-plan-price,
    .plan-tier-standard .fsl-plan-currency {
        color: #ffffff;
    }

    .plan-tier-standard .fsl-plan-btn {
        background-color: #f8f9fa;
        border: 1px solid #f8f9fa;
        color: #16181b;
    }

    .plan-tier-standard .fsl-plan-btn:hover {
        background-color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(255, 255, 255, 0.25);
    }

    .plan-tier-premium {
        background: linear-gradient(155deg, #2b2416 0%, #191714 55%, #241d11 100%) !important;
        border: 2px solid #ffc107 !important;
        color: #ffffff !important;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.65), 0 0 32px rgba(255, 193, 7, 0.28), inset 0 0 20px rgba(255, 193, 7, 0.1);
    }

    .plan-tier-premium .fsl-plan-badge {
        background: linear-gradient(90deg, #ffc107, #ffda6a);
        color: #16181b;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.35);
    }

    .plan-tier-premium .fsl-plan-title {
        color: #ffda6a;
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.16) 0%, rgba(0, 0, 0, 0.35) 100%) !important;
        border-color: rgba(255, 193, 7, 0.45) !important;
    }

    .plan-tier-premium .fsl-plan-subtitle {
        color: rgba(255, 255, 255, 0.78);
    }

    .plan-tier-premium .fsl-plan-price-wrap {
        border-top-color: rgba(255, 193, 7, 0.32) !important;
        border-bottom-color: rgba(255, 193, 7, 0.32) !important;
    }

    .plan-tier-premium .fsl-plan-currency,
    .plan-tier-premium .fsl-plan-price {
        color: #ffc107;
        text-shadow: 0 2px 16px rgba(255, 193, 7, 0.35);
    }

    .plan-tier-premium .fsl-plan-unit {
        color: rgba(255, 255, 255, 0.7);
    }

    .plan-tier-premium .fsl-plan-desc {
        color: rgba(255, 255, 255, 0.88);
    }

    .plan-tier-premium .fsl-plan-btn {
        background: linear-gradient(90deg, #ffc107 0%, #ffca2c 100%);
        border: none;
        color: #16181b;
        box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
    }

    .plan-tier-premium .fsl-plan-btn:hover {
        background: linear-gradient(90deg, #ffca2c 0%, #ffe082 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 26px rgba(255, 193, 7, 0.55);
    }

    .fsl-plan-card.plan-tier-special {
        position: relative;
        overflow: hidden;
        background-color: #bef264 !important;
        background-image: none !important;
        border: 2px solid #1a2e05 !important;
        color: #1a2e05 !important;
        box-shadow: 0 14px 32px rgba(26, 46, 5, 0.35), inset 0 0 28px rgba(255, 255, 255, 0.72), inset 0 0 60px rgba(236, 252, 203, 0.55) !important;
        transition: transform 0.35s ease, box-shadow 0.35s ease, background-color 0.35s ease, border-color 0.35s ease;
        animation: fslSpecialInnerGlow 3.2s ease-in-out infinite alternate;
    }

    .fsl-plan-card.plan-tier-special:hover {
        background-color: #d9f99d !important;
        border: 2px solid #0d1902 !important;
        box-shadow: 0 18px 40px rgba(26, 46, 5, 0.5), inset 0 0 36px rgba(255, 255, 255, 0.9), inset 0 0 72px rgba(236, 252, 203, 0.75) !important;
    }

    .fsl-plan-card.plan-tier-special::after {
        content: "";
        position: absolute;
        top: -10%;
        left: -120%;
        width: 55%;
        height: 120%;
        background: linear-gradient(115deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.15) 35%, rgba(255, 255, 255, 0.65) 50%, rgba(255, 255, 255, 0.15) 65%, rgba(255, 255, 255, 0) 100%);
        transform: skewX(-22deg);
        pointer-events: none;
        z-index: 3;
        animation: fslSpecialCardSheen 4.2s ease-in-out infinite;
    }

    .fsl-plan-card.plan-tier-special .fsl-plan-badge {
        background-color: #365314 !important;
        background-image: none !important;
        color: #ffffff !important;
        font-weight: 800;
        letter-spacing: 1.5px;
        padding: 0.35em 1em;
        border-radius: 50rem;
    }

    .fsl-plan-card.plan-tier-special .fsl-plan-title {
        color: #1a2e05 !important;
        letter-spacing: 1.5px;
        background-color: rgba(26, 46, 5, 0.12) !important;
        border-color: rgba(26, 46, 5, 0.32) !important;
    }

    .fsl-plan-card.plan-tier-special .fsl-plan-subtitle {
        color: #3f6212 !important;
        font-weight: 600;
    }

    .fsl-plan-card.plan-tier-special .fsl-plan-price-wrap {
        border-top: 1px solid rgba(26, 46, 5, 0.24) !important;
        border-bottom: 1px solid rgba(26, 46, 5, 0.24) !important;
    }

    .fsl-plan-card.plan-tier-special .fsl-plan-currency,
    .fsl-plan-card.plan-tier-special .fsl-plan-price {
        color: #365314 !important;
        text-shadow: none !important;
    }

    .fsl-plan-card.plan-tier-special .fsl-plan-unit {
        color: #4d7c0f !important;
        font-weight: 600;
    }

    .fsl-plan-card.plan-tier-special .fsl-plan-desc {
        color: #1a2e05 !important;
    }

    .fsl-plan-card.plan-tier-special .fsl-plan-btn {
        background-color: #365314 !important;
        background-image: none !important;
        border: none !important;
        color: #ffffff !important;
        letter-spacing: 1.5px;
        box-shadow: 0 6px 16px rgba(54, 83, 20, 0.28) !important;
        transition: all 0.25s ease;
    }

    .fsl-plan-card.plan-tier-special .fsl-plan-btn:hover {
        background-color: #1a2e05 !important;
        color: #ffffff !important;
        transform: translateY(-2px);
    }

    .fsl-plan-card.plan-tier-special .btn-fsl-wishlist {
        border-color: #365314 !important;
        color: #365314 !important;
    }

    .fsl-plan-card.plan-tier-special .btn-fsl-wishlist:hover {
        background-color: #365314 !important;
        color: #ffffff !important;
    }

    @keyframes fslSpecialInnerGlow {
        0% {
            box-shadow: 0 14px 32px rgba(26, 46, 5, 0.35), inset 0 0 20px rgba(255, 255, 255, 0.5), inset 0 0 42px rgba(236, 252, 203, 0.35);
        }

        100% {
            box-shadow: 0 16px 36px rgba(26, 46, 5, 0.42), inset 0 0 34px rgba(255, 255, 255, 0.85), inset 0 0 68px rgba(236, 252, 203, 0.7);
        }
    }

    @keyframes fslSpecialCardSheen {
        0% {
            left: -120%;
        }

        38% {
            left: 165%;
        }

        100% {
            left: 165%;
        }
    }

    .fsl-carousel-nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background-color: rgba(33, 37, 41, 0.88);
        border: 1.5px solid rgba(255, 193, 7, 0.55);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        cursor: pointer;
        z-index: 65;
        backdrop-filter: blur(4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.45);
        transition: all 0.25s ease;
    }

    .fsl-carousel-nav-btn:hover:not(:disabled) {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #16181b;
        transform: translateY(-50%) scale(1.1);
    }

    .fsl-carousel-nav-btn:disabled {
        opacity: 0.25;
        cursor: not-allowed;
        border-color: rgba(255, 255, 255, 0.2);
    }

    .fsl-nav-prev {
        left: 12px;
    }

    .fsl-nav-next {
        right: 12px;
    }

    .fsl-pricing-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: none;
        background-color: rgba(255, 255, 255, 0.25);
        padding: 0;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .fsl-pricing-dot.active {
        width: 28px;
        border-radius: 10px;
        background-color: #ffc107;
    }

    @media (min-width: 992px) {
        #fslPricingStage .fsl-plan-slide {
            visibility: visible !important;
        }

        #fslPricingStage .fsl-plan-slide:not(.fsl-plan-spacer) .fsl-plan-card,
        #fslPricingStage .fsl-plan-slide:not(.fsl-plan-spacer) .card-body,
        #fslPricingStage .fsl-plan-slide:not(.fsl-plan-spacer) .fsl-plan-header,
        #fslPricingStage .fsl-plan-slide:not(.fsl-plan-spacer) .fsl-plan-price-wrap,
        #fslPricingStage .fsl-plan-slide:not(.fsl-plan-spacer) .fsl-plan-desc {
            opacity: 1 !important;
            visibility: visible !important;
        }

        #fslPricingStage .fsl-plan-slide:not(.is-center):not(.active) {
            opacity: 0.72 !important;
            transition: transform 0.45s ease, opacity 0.45s ease;
        }

        #fslPricingStage .fsl-plan-slide:not(.is-center):not(.active):hover {
            opacity: 0.95 !important;
        }
    }

    @media (max-width: 991.98px) {

        #fslPricingCarousel,
        #fslPricingViewport,
        #fslPricingStage {
            height: 550px !important;
            min-height: 550px !important;
            max-height: none !important;
        }

        #fslPricingViewport {
            overflow-x: clip !important;
            overflow-y: visible !important;
            padding-top: 10px !important;
            padding-bottom: 20px !important;
        }

        #fslPricingStage .fsl-plan-slide {
            top: 10px !important;
            margin-top: 0 !important;
            height: 510px !important;
            max-height: 510px !important;
        }

        #fslPricingStage .fsl-plan-unit {
            display: block !important;
            width: 100% !important;
            text-align: center !important;
            margin-top: 0.15rem !important;
            font-size: 0.85rem !important;
        }

        #fslPricingStage .fsl-plan-card .fsl-plan-desc {
            line-clamp: 4 !important;
            -webkit-line-clamp: 4 !important;
        }

        #fslPricingCarousel #fslPricingPrev,
        #fslPricingCarousel #fslPricingNext {
            position: absolute !important;
            top: 265px !important;
            bottom: auto !important;
            transform: translateY(-50%) !important;
            z-index: 65 !important;
        }
    }

    @media (max-width: 575.98px) {
        #fslPricingStage .fsl-plan-slide {
            width: 280px !important;
            margin-left: -140px !important;
        }

        #fslPricingCarousel .fsl-carousel-nav-btn {
            width: 40px !important;
            height: 40px !important;
            font-size: 1.05rem !important;
        }

        #fslPricingCarousel #fslPricingPrev {
            left: 4px !important;
            right: auto !important;
        }

        #fslPricingCarousel #fslPricingNext {
            right: 4px !important;
            left: auto !important;
        }
    }

    /* F. 首頁課程&活動花絮水平滑動藝廊 (#portfolio) */
    .fsl-highlights-wrapper {
        position: relative;
        padding: 0 12px;
    }

    .fsl-highlights-viewport {
        width: 100%;
        overflow: hidden;
        cursor: grab;
        padding: 16px 4px;
        touch-action: pan-y;
    }

    .fsl-highlights-viewport.is-dragging {
        cursor: grabbing;
    }

    .fsl-highlights-viewport.is-dragging .portfolio-card,
    .fsl-highlights-viewport.is-gliding .portfolio-card {
        pointer-events: none !important;
        transition: none !important;
    }

    .fsl-highlights-track {
        display: flex;
        gap: 24px;
        width: max-content;
        will-change: transform;
        transform: translate3d(0px, 0, 0);
    }

    .fsl-highlight-item {
        width: 290px;
        height: 360px;
        flex-shrink: 0;
        contain: layout paint;
    }

    .fsl-highlight-card {
        width: 100%;
        height: 100%;
        position: relative;
        cursor: pointer;
    }

    .fsl-highlight-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        user-select: none;
        -webkit-user-drag: none;
    }

    .fsl-highlight-card .portfolio-overlay {
        pointer-events: none;
        z-index: 2;
    }

    .fsl-card-play-btn {
        position: absolute !important;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 64px;
        height: 64px;
        z-index: 5;
    }

    .fsl-card-play-btn .play-icon-triangle {
        border-top-width: 10px;
        border-bottom-width: 10px;
        border-left-width: 16px;
        margin-left: 4px;
    }

    .fsl-card-play-btn:hover {
        transform: translate(-50%, -50%) scale(1.12);
    }

    @media (max-width: 575.98px) {
        .fsl-highlight-item {
            width: 250px;
            height: 310px;
        }

        .fsl-highlights-track {
            gap: 16px;
        }
    }

    /* G. 常見問題 FAQ 手風琴 (#faqSection) */
    .fsl-faq-accordion .accordion-item {
        background-color: transparent !important;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        border: 1px solid rgba(255, 193, 7, 0.45) !important;
        transition: transform 0.25s ease, border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .fsl-faq-accordion .accordion-item:hover {
        transform: translateY(-2px);
        border-color: #ffc107 !important;
        box-shadow: 0 10px 26px rgba(0, 0, 0, 0.45) !important;
    }

    .fsl-faq-accordion .accordion-button {
        padding: 1.15rem 1.35rem;
        font-size: 1.05rem;
        letter-spacing: 0.5px;
        color: #121316 !important;
        background-color: rgba(252, 248, 236, 0.68) !important;
        box-shadow: none !important;
        transition: color 0.25s ease, background-color 0.25s ease;
    }

    .fsl-faq-accordion .accordion-button:not(.collapsed) {
        color: #000000 !important;
        background: linear-gradient(90deg, rgba(255, 241, 200, 0.78) 0%, rgba(255, 230, 165, 0.72) 100%) !important;
    }

    .fsl-faq-accordion .accordion-button::after {
        filter: none !important;
    }

    .fsl-faq-accordion .accordion-button:focus {
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.35) !important;
    }

    .fsl-faq-accordion .accordion-body {
        background-color: rgba(255, 255, 255, 0.55) !important;
        color: #16181b !important;
        font-weight: 500;
        padding: 1.15rem 1.4rem;
        font-size: 0.98rem;
    }

    .fsl-faq-accordion .accordion-body strong {
        color: #000000;
        font-weight: 700;
    }

    .fsl-faq-filter-btn {
        color: #adb5bd;
        border: 1px solid rgba(255, 255, 255, 0.22);
        background-color: rgba(255, 255, 255, 0.04);
        border-radius: 50px;
        padding: 0.4rem 1.15rem;
        letter-spacing: 1px;
        transition: all 0.25s ease;
    }

    .fsl-faq-filter-btn:hover {
        color: #ffffff;
        border-color: rgba(255, 193, 7, 0.65);
        background-color: rgba(255, 193, 7, 0.1);
    }

    .fsl-faq-filter-btn.active {
        color: #16181b;
        background-color: #ffc107;
        border-color: #ffc107;
        font-weight: 700;
        box-shadow: 0 4px 14px rgba(255, 193, 7, 0.3);
    }

    /* H. 課程系統介紹 (#courseinfo) */
    .fsl-book-frame {
        position: relative;
        display: inline-block;
        width: 100%;
        max-width: 500px;
        padding: 24px;
        border-radius: 16px;
        background: radial-gradient(circle at center, rgba(255, 193, 7, 0.05) 0%, rgba(255, 193, 7, 0) 70%);
        transition: transform 0.5s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .fsl-book-frame .fsl-corner {
        position: absolute;
        width: 28px;
        height: 28px;
        pointer-events: none;
        transition: all 0.45s cubic-bezier(0.22, 1, 0.36, 1);
        z-index: 2;
    }

    .fsl-book-frame .fsl-corner::before {
        content: "";
        position: absolute;
        width: 100%;
        height: 100%;
        border-color: rgba(255, 193, 7, 0.75);
        border-style: solid;
    }

    .fsl-book-frame .fsl-corner::after {
        content: "";
        position: absolute;
        width: 16px;
        height: 16px;
        border-color: rgba(255, 193, 7, 0.35);
        border-style: solid;
    }

    .fsl-corner-tl {
        top: 6px;
        left: 6px;
    }

    .fsl-corner-tl::before {
        top: 0;
        left: 0;
        border-width: 1.5px 0 0 1.5px;
        border-top-left-radius: 6px;
    }

    .fsl-corner-tl::after {
        top: 5px;
        left: 5px;
        border-width: 1px 0 0 1px;
    }

    .fsl-corner-tr {
        top: 6px;
        right: 6px;
    }

    .fsl-corner-tr::before {
        top: 0;
        right: 0;
        border-width: 1.5px 1.5px 0 0;
        border-top-right-radius: 6px;
    }

    .fsl-corner-tr::after {
        top: 5px;
        right: 5px;
        border-width: 1px 1px 0 0;
    }

    .fsl-corner-bl {
        bottom: 6px;
        left: 6px;
    }

    .fsl-corner-bl::before {
        bottom: 0;
        left: 0;
        border-width: 0 0 1.5px 1.5px;
        border-bottom-left-radius: 6px;
    }

    .fsl-corner-bl::after {
        bottom: 5px;
        left: 5px;
        border-width: 0 0 1px 1px;
    }

    .fsl-corner-br {
        bottom: 6px;
        right: 6px;
    }

    .fsl-corner-br::before {
        bottom: 0;
        right: 0;
        border-width: 0 1.5px 1.5px 0;
        border-bottom-right-radius: 6px;
    }

    .fsl-corner-br::after {
        bottom: 5px;
        right: 5px;
        border-width: 0 1px 1px 0;
    }

    .fsl-book-frame:hover .fsl-corner-tl {
        transform: translate(-4px, -4px);
    }

    .fsl-book-frame:hover .fsl-corner-tr {
        transform: translate(4px, -4px);
    }

    .fsl-book-frame:hover .fsl-corner-bl {
        transform: translate(-4px, 4px);
    }

    .fsl-book-frame:hover .fsl-corner-br {
        transform: translate(4px, 4px);
    }

    .fsl-book-frame:hover .fsl-corner::before {
        border-color: #ffc107;
        box-shadow: 0 0 10px rgba(255, 193, 7, 0.35);
    }

    #courseinfo .fsl-course-book-img {
        width: 100%;
        height: auto !important;
        max-height: none !important;
        object-fit: contain !important;
        aspect-ratio: auto !important;
        border-radius: 8px;
        filter: drop-shadow(0 24px 42px rgba(0, 0, 0, 0.85));
        transition: transform 0.5s cubic-bezier(0.22, 1, 0.36, 1), filter 0.5s ease;
    }

    .fsl-book-frame:hover .fsl-course-book-img {
        transform: translateY(-6px) scale(1.02);
        filter: drop-shadow(0 30px 50px rgba(255, 193, 7, 0.22));
    }

    .fsl-course-content-box {
        text-align: left;
    }

    .fsl-course-divider {
        border: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 193, 7, 0.25), transparent);
    }

    .fsl-course-feature-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .fsl-course-feature-list li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        color: rgba(255, 255, 255, 0.85);
        font-size: 1.02rem;
        line-height: 1.6;
        letter-spacing: 1px;
    }

    .fsl-course-feature-list li i {
        font-size: 1.12rem;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .fsl-course-feature-list li strong {
        color: #ffffff;
        font-weight: 600;
        margin-right: 4px;
    }

    .fsl-course-quote-box {
        padding: 14px 20px;
        border-left: 2px solid #ffc107;
        background: linear-gradient(90deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0) 100%);
        border-radius: 0 8px 8px 0;
        font-size: 1.02rem;
        line-height: 1.8;
        letter-spacing: 1.5px;
    }

    .fsl-course-series-group {
        padding: 20px 22px;
        background: rgba(255, 255, 255, 0.025);
        border: 1px solid rgba(255, 193, 7, 0.18);
        border-radius: 12px;
    }

    .fsl-series-header {
        padding-bottom: 12px;
        margin-bottom: 16px;
        border-bottom: 1px solid rgba(255, 193, 7, 0.3);
    }

    .fsl-series-items {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .fsl-subcourse-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .fsl-subcourse-num {
        flex-shrink: 0;
        font-family: Georgia, 'Times New Roman', serif;
        font-style: italic;
        font-size: 1rem;
        color: #ffc107;
        line-height: 1.5;
        padding-top: 1px;
    }

    .fsl-subcourse-info h5 {
        margin: 0 0 4px 0;
        font-size: 1.06rem;
        font-weight: 600;
        color: #ffffff;
        letter-spacing: 1px;
    }

    .fsl-subcourse-info p {
        margin: 0;
        font-size: 0.92rem;
        line-height: 1.65;
        color: rgba(255, 255, 255, 0.72);
        letter-spacing: 0.5px;
    }

    @media (max-width: 991.98px) {
        .fsl-book-frame {
            max-width: 380px;
            padding: 18px;
        }
    }

    @media (max-width: 575.98px) {
        .fsl-book-frame {
            max-width: 300px;
            padding: 14px;
        }

        .fsl-book-frame .fsl-corner {
            width: 22px;
            height: 22px;
        }

        .fsl-course-series-group {
            padding: 16px 16px;
        }
    }

    /* I. 顧問&講師 (#mentor) */
    .fsl-instructor-frame {
        position: relative;
        display: inline-block;
        width: 100%;
        max-width: 420px;
        padding: 18px;
        border-radius: 16px;
        background: radial-gradient(circle at center, rgba(255, 193, 7, 0.06) 0%, rgba(255, 193, 7, 0) 75%);
    }

    .fsl-instructor-frame .fsl-corner {
        position: absolute;
        width: 26px;
        height: 26px;
        pointer-events: none;
        transition: all 0.45s cubic-bezier(0.22, 1, 0.36, 1);
        z-index: 2;
    }

    .fsl-instructor-frame .fsl-corner::before {
        content: "";
        position: absolute;
        width: 100%;
        height: 100%;
        border-color: rgba(255, 193, 7, 0.75);
        border-style: solid;
    }

    .fsl-instructor-frame .fsl-corner::after {
        content: "";
        position: absolute;
        width: 15px;
        height: 15px;
        border-color: rgba(255, 193, 7, 0.35);
        border-style: solid;
    }

    .fsl-instructor-frame:hover .fsl-corner-tl {
        transform: translate(-4px, -4px);
    }

    .fsl-instructor-frame:hover .fsl-corner-tr {
        transform: translate(4px, -4px);
    }

    .fsl-instructor-frame:hover .fsl-corner-bl {
        transform: translate(-4px, 4px);
    }

    .fsl-instructor-frame:hover .fsl-corner-br {
        transform: translate(4px, 4px);
    }

    #mentor .fsl-instructor-img {
        width: 100%;
        height: auto !important;
        max-height: none !important;
        object-fit: contain !important;
        aspect-ratio: auto !important;
        border-radius: 10px;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.75);
        transition: transform 0.5s ease;
    }

    .fsl-instructor-frame:hover .fsl-instructor-img {
        transform: scale(1.015);
    }

    .fsl-instructor-bio p {
        font-size: 1.02rem;
        line-height: 1.9;
        letter-spacing: 1px;
        margin-bottom: 1rem;
    }

    .fsl-book-feature-card {
        padding: 20px 24px;
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.06) 0%, rgba(255, 255, 255, 0.02) 100%);
        border: 1px solid rgba(255, 193, 7, 0.25);
        border-radius: 14px;
        transition: border-color 0.4s ease, transform 0.4s ease;
    }

    .fsl-book-feature-card:hover {
        border-color: rgba(255, 193, 7, 0.55);
        transform: translateY(-3px);
    }

    #mentor .fsl-reina-book-img {
        width: 100%;
        max-width: 135px;
        height: auto !important;
        border-radius: 6px;
        filter: drop-shadow(0 12px 22px rgba(0, 0, 0, 0.8));
    }

    .fsl-book-feature-card:hover .fsl-reina-book-img {
        transform: translateY(-5px) scale(1.03);
    }

    @media (max-width: 991.98px) {
        .fsl-instructor-frame {
            max-width: 340px;
            padding: 14px;
        }
    }

    @media (max-width: 767.98px) {
        .fsl-book-feature-card {
            padding: 24px 20px;
            text-align: center;
        }

        .fsl-book-feature-card .d-flex {
            justify-content: center;
        }

        #mentor .fsl-reina-book-img {
            max-width: 180px;
            margin-bottom: 8px;
        }
    }
</style>


<?php
// 引入完整頁尾與地端腳本載入區
include_once '5sl_include_footer.php';
?>

<script>
$(function() {
    // 1. 滾動智慧緩入監聽 (IntersectionObserver)
    if ("IntersectionObserver" in window) {
        const scrollObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    $(entry.target).addClass("active");
                    observer.unobserve(entry.target);
                }
            });
        }, { root: null, rootMargin: "0px 0px -50px 0px", threshold: 0.35 });

        $(".reveal-on-scroll, .reveal-from-left, .reveal-from-right").each(function() {
            scrollObserver.observe(this);
        });
    } else {
        $(".reveal-on-scroll, .reveal-from-left, .reveal-from-right").addClass("active");
    }

    // 2. 原生滾動視差景深引擎 (Parallax)
    const $parallaxZones = $(".parallax-bg-zone");
    if ($parallaxZones.length > 0) {
        let ticked = false;
        function updateParallax() {
            const scrollTop = $(window).scrollTop();
            const windowHeight = $(window).height();
            $parallaxZones.each(function() {
                const $zone = $(this);
                const $bg = $zone.find(".parallax-bg");
                if ($bg.length === 0) return;
                const zoneTop = $zone.offset().top;
                const zoneHeight = $zone.outerHeight();
                if (scrollTop + windowHeight > zoneTop && scrollTop < zoneTop + zoneHeight) {
                    const translateY = (scrollTop - zoneTop) * 0.55;
                    $bg.css("transform", "translate3d(0, " + translateY + "px, 0)");
                }
            });
            ticked = false;
        }
        $(window).on("scroll.fsl_parallax", function() {
            if (!ticked) {
                window.requestAnimationFrame(updateParallax);
                ticked = true;
            }
        });
    }

    // 3. 見證影片展示中控 (#videoLightboxModal)
    const $videoModal = $("#videoLightboxModal");
    const $videoIframe = $("#lightboxIframe");
    if ($videoModal.length > 0 && $videoIframe.length > 0) {
        $(document).on("click", ".video-play-btn[data-video-url]", function() {
            const rawUrl = $(this).attr("data-video-url") || "";
            let videoId = "";
            if (rawUrl.indexOf("v=") !== -1) videoId = rawUrl.split("v=").pop().split("&").shift();
            else if (rawUrl.indexOf("youtu.be/") !== -1) videoId = rawUrl.split("youtu.be/").pop().split("?").shift();

            if (videoId !== "") {
                const embedSrc = "https://" + "youtube.com" + "/em" + "bed/" + videoId + "?autoplay=1&rel=0&playsinline=1";
                $videoIframe.attr("src", embedSrc);
            }
        });
        $videoModal.on("hide.bs.modal", function() {
            if (document.activeElement && this.contains(document.activeElement)) document.activeElement.blur();
        });
        $videoModal.on("hidden.bs.modal", function() {
            $videoIframe.attr("src", "");
        });
    }

    // 4. 價格方案無限多階景深輪播引擎 (#fslPricingStage)
    const $pricingStage = $("#fslPricingStage");
    const $pricingViewport = $("#fslPricingViewport");
    if ($pricingStage.length > 0) {
        const $realSlides = $pricingStage.children(".fsl-plan-slide");
        const realCount = $realSlides.length;
        let activeRealIndex = Math.floor(realCount / 2);

        $realSlides.each(function(idx) {
            if ($(this).hasClass("hottest") || $(this).find(".hottest").length > 0) {
                activeRealIndex = idx;
                return false;
            }
        });

        const padCount = 3;
        const spacerHtml = '<div class="fsl-plan-slide fsl-plan-spacer" aria-hidden="true"><div class="card h-100 fsl-plan-card"><div class="card-body"></div></div></div>';
        $pricingStage.prepend(spacerHtml.repeat(padCount));
        $pricingStage.append(spacerHtml.repeat(padCount));
        const $allSlides = $pricingStage.children(".fsl-plan-slide");

        const $dotsContainer = $("#fslPricingDots");
        if ($dotsContainer.length > 0) {
            $dotsContainer.empty();
            for (let i = 0; i < realCount; i++) {
                $dotsContainer.append('<button type="button" class="fsl-pricing-dot" data-target-idx="' + i + '" aria-label="切換至第 ' + (i + 1) + ' 個方案"></button>');
            }
        }

        function getDepthConfig() {
            const w = $(window).width();
            if (w >= 1400) return { maxVisibleTier: 3, offsets: new Array(0, 300, 520, 680), scales: new Array(1, 0.85, 0.66, 0.48), opacities: new Array(1, 0.82, 0.58, 0.36) };
            if (w >= 992)  return { maxVisibleTier: 2, offsets: new Array(0, 295, 510), scales: new Array(1, 0.84, 0.65), opacities: new Array(1, 0.8, 0.56) };
            if (w >= 576)  return { maxVisibleTier: 2, offsets: new Array(0, 220, 360), scales: new Array(1, 0.8, 0.52), opacities: new Array(1, 0.72, 0.42) };
            return { maxVisibleTier: 1, offsets: new Array(0, 155), scales: new Array(1, 0.76), opacities: new Array(1, 0.5) };
        }
        function renderPricingCarousel() {
            const centerDomIndex = activeRealIndex + padCount;
            const cfg = getDepthConfig();
            $allSlides.each(function(domIdx) {
                const diff = domIdx - centerDomIndex;
                const absDiff = Math.abs(diff);
                const dir = diff === 0 ? 0 : diff > 0 ? 1 : -1;
                const $slide = $(this);
                const isSpacer = $slide.hasClass("fsl-plan-spacer");
                $slide.removeClass("is-center is-side-clickable is-far-blur").attr("data-diff", diff);

                if (absDiff === 0) {
                    $slide.addClass("is-center").css({ transform: "translate3d(0px, 0, 0) scale(1)", opacity: 1, zIndex: 50, pointerEvents: "auto" });
                } else if (absDiff <= cfg.maxVisibleTier) {
                    const tx = dir * cfg.offsets[absDiff];
                    const sc = cfg.scales[absDiff];
                    const op = cfg.opacities[absDiff];
                    $slide.addClass(isSpacer ? "is-far-blur" : "is-side-clickable").css({ transform: "translate3d(" + tx + "px, 0, 0) scale(" + sc + ")", opacity: op, zIndex: 50 - absDiff * 10, pointerEvents: !isSpacer ? "auto" : "none" });
                } else {
                    const maxOffset = cfg.offsets[cfg.offsets.length - 1] + 140;
                    $slide.addClass("is-far-blur").css({ transform: "translate3d(" + dir * maxOffset + "px, 0, 0) scale(0.25)", opacity: 0, zIndex: 1, pointerEvents: "none" });
                }
            });
            $("#fslPricingPrev").prop("disabled", activeRealIndex <= 0);
            $("#fslPricingNext").prop("disabled", activeRealIndex >= realCount - 1);
            $dotsContainer.find(".fsl-pricing-dot").removeClass("active");
            $dotsContainer.find('.fsl-pricing-dot[data-target-idx="' + activeRealIndex + '"]').addClass("active");
        }

        function goToPlan(newIdx) {
            activeRealIndex = Math.max(0, Math.min(realCount - 1, newIdx));
            renderPricingCarousel();
        }

        renderPricingCarousel();
        $(window).off("resize.fsl_pricing").on("resize.fsl_pricing", renderPricingCarousel);
        $("#fslPricingPrev").on("click", function() { goToPlan(activeRealIndex - 1); });
        $("#fslPricingNext").on("click", function() { goToPlan(activeRealIndex + 1); });
        $pricingStage.on("click", ".fsl-plan-slide.is-side-clickable", function() {
            const diff = parseInt($(this).attr("data-diff"), 10) || 0;
            if (diff !== 0) goToPlan(activeRealIndex + diff);
        });
        $dotsContainer.on("click", ".fsl-pricing-dot", function() {
            goToPlan(parseInt($(this).attr("data-target-idx"), 10));
        });

        let dragStartX = 0, isDragging = false;
        $pricingViewport.on("mousedown touchstart", function(e) {
            if ($(e.target).closest("a, button").length) return;
            isDragging = true;
            const point = e.type === "touchstart" ? e.originalEvent.touches.item(0) : e;
            dragStartX = point.clientX;
        });
        $(window).on("mouseup.fsl_pricing_drag touchend.fsl_pricing_drag", function(e) {
            if (!isDragging) return;
            isDragging = false;
            const point = e.type === "touchend" ? e.originalEvent.changedTouches.item(0) : e;
            const deltaX = point.clientX - dragStartX;
            if (deltaX > 45) goToPlan(activeRealIndex - 1);
            else if (deltaX < -45) goToPlan(activeRealIndex + 1);
        });
    }

    // 5. 課程&活動花絮水平滑動藝廊 + 三種 Lightbox
    const $highlightsViewport = $("#fslHighlightsViewport");
    const $highlightsTrack = $("#fslHighlightsTrack");
    if ($highlightsViewport.length > 0 && $highlightsTrack.length > 0) {
        const viewportEl = $highlightsViewport.get(0);
        const trackEl = $highlightsTrack.get(0);
        let currentX = 0, minX = 0, isDown = false, startPointerX = 0, startTrackX = 0, lastPointerX = 0, lastTime = 0, velocity = 0, rafId = null, totalDragDistance = 0;

        function applyTransform(x) { trackEl.style.transform = "translate3d(" + x.toFixed(2) + "px, 0, 0)"; }
        function updateNavButtons() {
            $("#fslHighlightsPrev").prop("disabled", currentX >= -2);
            $("#fslHighlightsNext").prop("disabled", currentX <= minX + 2);
        }
        function recalcBounds() {
            minX = Math.min(0, viewportEl.clientWidth - trackEl.scrollWidth);
            currentX = Math.max(minX, Math.min(0, currentX));
            applyTransform(currentX);
            updateNavButtons();
        }
        function stopAnimation() {
            if (rafId !== null) { cancelAnimationFrame(rafId); rafId = null; }
            $highlightsViewport.removeClass("is-gliding");
        }
        function smoothGlideBy(distance) {
            stopAnimation();
            const startPos = currentX;
            const targetPos = Math.max(minX, Math.min(0, startPos + distance));
            if (Math.abs(targetPos - startPos) < 1) return;
            const startTime = performance.now();
            $highlightsViewport.addClass("is-gliding");
            function step(now) {
                const progress = Math.min((now - startTime) / 520, 1);
                currentX = startPos + (targetPos - startPos) * (1 - Math.pow(1 - progress, 3));
                applyTransform(currentX);
                if (progress < 1) rafId = requestAnimationFrame(step);
                else { rafId = null; $highlightsViewport.removeClass("is-gliding"); updateNavButtons(); }
            }
            rafId = requestAnimationFrame(step);
        }

        $("#fslHighlightsPrev").on("click", function() {
            const cardW = $highlightsTrack.find(".fsl-highlight-item").first().outerWidth(true) || 314;
            smoothGlideBy(window.innerWidth < 768 ? cardW : cardW * 2);
        });
        $("#fslHighlightsNext").on("click", function() {
            const cardW = $highlightsTrack.find(".fsl-highlight-item").first().outerWidth(true) || 314;
            smoothGlideBy(-(window.innerWidth < 768 ? cardW : cardW * 2));
        });

        function getClientX(e) {
            return e.type.indexOf("touch") === 0
                ? (e.originalEvent.touches.length ? e.originalEvent.touches : e.originalEvent.changedTouches).item(0).clientX
                : e.clientX;
        }
        $highlightsViewport.on("mousedown touchstart", function(e) {
            if (e.type === "mousedown" && e.button !== 0) return;
            stopAnimation();
            isDown = true;
            totalDragDistance = 0;
            startPointerX = lastPointerX = getClientX(e);
            startTrackX = currentX;
            lastTime = performance.now();
            velocity = 0;
        });
        $(window).on("mousemove.fsl_highlights touchmove.fsl_highlights", function(e) {
            if (!isDown) return;
            if (e.type === "mousemove") e.preventDefault();
            const now = performance.now(), px = getClientX(e), dt = now - lastTime, dx = px - lastPointerX;
            totalDragDistance = Math.max(totalDragDistance, Math.abs(px - startPointerX));
            if (totalDragDistance > 6) $highlightsViewport.addClass("is-dragging");
            if (dt > 0) velocity = velocity * 0.35 + ((dx / dt) * 16.67) * 0.65;
            let nextX = startTrackX + (px - startPointerX);
            if (nextX > 0) nextX *= 0.25;
            if (nextX < minX) nextX = minX + (nextX - minX) * 0.25;
            currentX = nextX;
            applyTransform(currentX);
            lastPointerX = px;
            lastTime = now;
        });
        $(window).on("mouseup.fsl_highlights touchend.fsl_highlights", function() {
            if (!isDown) return;
            isDown = false;
            $highlightsViewport.removeClass("is-dragging");
            if (currentX > 0) { smoothGlideBy(-currentX); return; }
            if (currentX < minX) { smoothGlideBy(minX - currentX); return; }
            updateNavButtons();
        });

        $highlightsTrack.on("click", ".fsl-highlight-card", function(e) {
            e.preventDefault();
            if (totalDragDistance > 8) return;
            const $card = $(this);
            const mType = parseInt($card.attr("data-media-type"), 10) || 1;
            const mainImg = $card.attr("data-main-img") || "";
            const mediaUrl = ($card.attr("data-media-url") || "").trim();
            const title = $card.attr("data-title") || "";
            const desc = $card.attr("data-desc") || "";
            const dateStr = $card.attr("data-date") || "";

            if (mType === 2 && mediaUrl !== "") {
                let ytId = "";
                if (mediaUrl.indexOf("v=") !== -1) ytId = mediaUrl.split("v=").pop().split("&").shift();
                else if (mediaUrl.indexOf("youtu.be/") !== -1) ytId = mediaUrl.split("youtu.be/").pop().split("?").shift();
                else if (mediaUrl.indexOf("/embed/") !== -1) ytId = mediaUrl.split("/embed/").pop().split("?").shift();
                if (ytId !== "") {
                    $("#lightboxIframe").attr("src", "https://" + "youtube.com" + "/em" + "bed/" + ytId + "?autoplay=1&rel=0&playsinline=1");
                    bootstrap.Modal.getOrCreateInstance(document.getElementById("videoLightboxModal")).show();
                }
            } else if (mType === 3 && mediaUrl !== "") {
                $("#homeAudioCover").attr("src", mainImg);
                $("#homeAudioDate").text(dateStr);
                $("#homeAudioTitle").text(title);
                $("#homeAudioDesc").text(desc);
                const player = document.getElementById("homeAudioPlayer");
                player.src = mediaUrl;
                bootstrap.Modal.getOrCreateInstance(document.getElementById("homeAudioLightboxModal")).show();
                player.play().catch(function() {});
            } else {
                $("#previewLightboxImg").attr("src", mainImg);
                $("#previewLightboxTitle").text(title);
                $("#previewLightboxDesc").text(desc);
                bootstrap.Modal.getOrCreateInstance(document.getElementById("imagePreviewModal")).show();
            }
        });

        $("#homeAudioLightboxModal").on("hidden.bs.modal", function() {
            const player = document.getElementById("homeAudioPlayer");
            if (player) { player.pause(); player.currentTime = 0; player.src = ""; }
        });

        $(window).off("resize.fsl_highlights").on("resize.fsl_highlights", recalcBounds);
        recalcBounds();
        setTimeout(recalcBounds, 300);
    }

    // 6. 常見問題 FAQ 分類標籤快速篩選
    $("#fslFaqFilterBar").on("click", ".fsl-faq-filter-btn", function() {
        const $btn = $(this);
        const filterCat = $btn.attr("data-faq-filter");
        $("#fslFaqFilterBar .fsl-faq-filter-btn").removeClass("active");
        $btn.addClass("active");
        const $items = $("#faqAccordion .accordion-item");
        if (filterCat === "all") $items.stop(true, true).fadeIn(250);
        else $items.each(function() { $(this).attr("data-faq-cat") === filterCat ? $(this).stop(true, true).fadeIn(250) : $(this).stop(true, true).hide(); });
    });

    // 7. 首頁課程與價格方案預約確認彈窗 (#homeCourseConfirmModal)
    const homeCoursesMap = <?php echo json_encode((object)$home_courses_map, JSON_UNESCAPED_UNICODE); ?>;
    const isMemberLoggedIn = <?php echo ($mb_id > 0) ? 'true' : 'false'; ?>;
    let selectedHomeCourseId = 0;

    $(document).on("click", ".btn-home-book-course", function(e) {
        e.preventDefault();
        const cid = parseInt($(this).attr("data-course-id"), 10) || 0;
        const info = homeCoursesMap[cid];
        if (!info) { alert("此方案目前尚未開放線上預約或已下架，請聯繫客服人員！"); return; }

        selectedHomeCourseId = cid;
        const isConsult = (info.course_type === 2);
        $("#hcModalCover").attr("src", info.cover_url);
        $("#hcModalTypeBadge").text(isConsult ? "一對一顧問預約" : "陪跑與線上課程");
        $("#hcModalTitle").text(info.course_title);
        $("#hcModalBrief").text(info.course_brief);
        $("#hcModalPrice").text("NT$ " + info.course_cost.toLocaleString());
        $("#btnHomeConfirmAddCart").html(`<i class="bi bi-check2-circle me-1"></i>${isConsult ? "同意預約並前往結帳" : "同意報名並前往結帳"}`);

        const $sel = $("#hcModalSessionSelect").empty();
        if (Array.isArray(info.sessions) && info.sessions.length > 0) {
            $("#hcModalSessionWrap").removeClass("d-none");
            info.sessions.forEach(function(s) { $sel.append(`<option value="${s.sess_id}">${s.label}</option>`); });
        } else {
            $("#hcModalSessionWrap").addClass("d-none");
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById("homeCourseConfirmModal")).show();
    });

    $("#btnHomeConfirmAddCart").on("click", function() {
        if (!isMemberLoggedIn) { alert("請先登入 Fifth Season Life 會員帳號！"); window.location.href = "5sl_login.php"; return; }
        if (!selectedHomeCourseId) return;
        const sessId = $("#hcModalSessionSelect").val() || 0;
        bootstrap.Modal.getInstance(document.getElementById("homeCourseConfirmModal")).hide();
        if (window.fslLoader) window.fslLoader.show();

        $.post("./funcFiles/5sl_act_member.php", { action: "act_cart_add", item_type: 1, course_id: selectedHomeCourseId, sess_id: sessId, qty: 1 }, function(res) {
            if (window.fslLoader) window.fslLoader.hide();
            if (res.success === 1) window.location.href = "5sl_myCart.php";
            else if (res.need_login === 1) window.location.href = "5sl_login.php";
            else alert(res.message || "加入購物車失敗，請稍後再試。");
        }, "json");
    });


});

</script>


