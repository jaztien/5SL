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
        <section id="coreservices" class="py-5 bg-dark text-white fsl-guide-section-dark" style="background-image: url('imageFiles/test/<?php echo rand(1,18); ?>.jpg');">
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
                        <div class="video-cover-wrapper position-relative rounded-3 overflow-hidden shadow-lg" style="background-image: url('imageFiles/test/<?php echo rand(1,18); ?>.jpg');">
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
                            <!-- <iframe src="" id="lightboxIframe" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe> -->
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
            <img src="imageFiles/test/<?php echo rand(1,18); ?>.jpg" class="parallax-bg">
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
                            <img src="imageFiles/course01-3.jpg" class="img-fluid fsl-course-book-img" alt="步步向光－人生重啟陪跑系統" onerror="this.src='imageFiles/test/<?php echo rand(1,18); ?>.jpg'">
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
                                $cid        = 10; // 對應 courses.course_id（例如：10=步步向光陪跑, 11=身心之美孵化, 12=一對一深度陪談）
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
                            <img src="imageFiles/course02-3.jpg" class="img-fluid fsl-course-book-img" alt="身心之美－孵化課程系統" onerror="this.src='imageFiles/test/<?php echo rand(1,18); ?>.jpg'">
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
                                $cid        = 11; // 對應 courses.course_id（例如：10=步步向光陪跑, 11=身心之美孵化, 12=一對一深度陪談）
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
                            // 💡 預留後台資料庫對接陣列結構（未來可直接替換為 MySQL SELECT 查詢結果，支援 10~20+ 筆資料）
                            $highlights_items = array(
                                array('img' => 'imageFiles/test/' . rand(1, 18) . '.jpg', 'title' => '靜心覺察工作坊', 'desc' => '2026 春季場・台北', 'video' => ''),
                                array('img' => 'imageFiles/test/' . rand(1, 18) . '.jpg', 'title' => '第五季生命對話沙龍', 'desc' => '精選活動紀錄影片', 'video' => 'https://youtu.be'),
                                array('img' => 'imageFiles/test/' . rand(1, 18) . '.jpg', 'title' => '內在卡點探索課程', 'desc' => '小組深度共學時刻', 'video' => ''),
                                array('img' => 'imageFiles/test/' . rand(1, 18) . '.jpg', 'title' => '藝術療癒與自我重塑', 'desc' => '週末沉浸式體驗', 'video' => ''),
                                array('img' => 'imageFiles/test/' . rand(1, 18) . '.jpg', 'title' => '學員蛻變真實分享', 'desc' => '訪談實錄・影音版', 'video' => 'https://youtu.be'),
                                array('img' => 'imageFiles/test/' . rand(1, 18) . '.jpg', 'title' => '山林靜謐能量營', 'desc' => '兩天一夜自然對話', 'video' => ''),
                                array('img' => 'imageFiles/test/' . rand(1, 18) . '.jpg', 'title' => '價值觀重整實戰班', 'desc' => '第 12 期結業花絮', 'video' => ''),
                                array('img' => 'imageFiles/test/' . rand(1, 18) . '.jpg', 'title' => '呼吸與身體覺察課', 'desc' => '釋放緊繃情緒練習', 'video' => ''),
                                array('img' => 'imageFiles/test/' . rand(1, 18) . '.jpg', 'title' => '年度生命藝術展覽', 'desc' => '開幕式精華片段', 'video' => 'https://youtu.be'),
                                array('img' => 'imageFiles/test/' . rand(1, 18) . '.jpg', 'title' => '一對一教練引導實況', 'desc' => '專注傾聽的微光時刻', 'video' => ''),
                                array('img' => 'imageFiles/test/' . rand(1, 18) . '.jpg', 'title' => '女性力量覺醒茶會', 'desc' => '午後溫暖交流聚會', 'video' => ''),
                                array('img' => 'imageFiles/test/' . rand(1, 18) . '.jpg', 'title' => '跨界心靈美學講座', 'desc' => '特邀講師對談紀錄', 'video' => '')
                            );

                            foreach ($highlights_items as $item):
                                $has_video = !empty($item['video']);
                            ?>
                            <div class="fsl-highlight-item portfolio-card-wrap">
                                <div class="card border-0 shadow-sm overflow-hidden portfolio-card fsl-highlight-card">
                                    <img src="<?php echo htmlspecialchars($item['img']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>" draggable="false" onerror="this.src='imageFiles/test/1.jpg'">
                                    
                                    <?php if ($has_video): ?>
                                        <!-- 若該筆花絮包含 YouTube 連結，中央置入與影音展示區完全相同的播放鈕並共用 Lightbox 彈窗 -->
                                        <button type="button" class="video-play-btn fsl-card-play-btn" data-bs-toggle="modal" data-bs-target="#videoLightboxModal" data-video-url="<?php echo htmlspecialchars($item['video']); ?>" aria-label="播放影片">
                                            <div class="play-icon-triangle"></div>
                                            <div class="play-btn-ripple"></div>
                                        </button>
                                    <?php endif; ?>

                                    <div class="card-body portfolio-overlay text-white">
                                        <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($item['title']); ?></h6>
                                        <p class="text-white-50 small mb-0"><?php echo htmlspecialchars($item['desc']); ?></p>
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
            <img src="imageFiles/test/<?php echo rand(1,18); ?>.jpg" class="parallax-bg">
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

                            <!-- 方案 1：普通收費項目 (plan-tier-basic) -->
                            <div class="fsl-plan-slide">
                                <div class="card h-100 fsl-plan-card plan-tier-basic text-center p-4">
                                    <div class="card-body d-flex flex-column">
                                        <div class="fsl-plan-header mb-3">
                                            <span class="fsl-plan-badge">基礎入門</span>
                                            <h4 class="fsl-plan-title fw-bold mt-2 mb-1">初探覺察體驗</h4>
                                            <p class="fsl-plan-subtitle small mb-0">適合剛開始接觸自我對話的您</p>
                                        </div>
                                        <div class="fsl-plan-price-wrap my-3">
                                            <span class="fsl-plan-currency">NT$</span>
                                            <span class="fsl-plan-price">1,200</span>
                                            <span class="fsl-plan-unit">/ 單次</span>
                                        </div>
                                        <p class="fsl-plan-desc small my-3 flex-grow-1">
                                            透過一對一基礎引導問卷與 45 分鐘線上對談，協助您初步梳理當前卡點與生活狀態。
                                        </p>


                                        <!-- 每張價格卡片都可以置入的預約以及最愛按鈕 -->
                                        <?php
                                        $plan_course_id = 12; // 對應各方案綁定的課程或一對一顧問服務 ID（例如：12=一對一陪談, 10=16週陪跑系統）
                                        $is_plan_wished = !empty($user_wished_courses[$plan_course_id]);
                                        ?>
                                        <div class="mt-auto pt-3 d-flex align-items-center gap-2">
                                            <button type="button"
                                                    class="btn fsl-plan-btn flex-grow-1 py-2 fw-bold btn-fsl-add-cart"
                                                    data-type="1"
                                                    data-course-id="<?php echo $plan_course_id; ?>"
                                                    data-redirect="1">
                                                <i class="bi bi-bag-check me-1"></i>立即預約方案
                                            </button>
                                            <button type="button"
                                                    class="btn btn-outline-light py-2 px-3 btn-fsl-wishlist"
                                                    data-type="1"
                                                    data-id="<?php echo $plan_course_id; ?>"
                                                    title="加入收藏追蹤">
                                                <i class="bi <?php echo $is_plan_wished ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                            </button>
                                        </div>
                                        <!-- //預約以及最愛按鈕佈置完畢 -->


                                    </div>
                                </div>
                            </div>

                            <!-- 方案 2：次要收費項目 (plan-tier-standard) -->
                            <div class="fsl-plan-slide">
                                <div class="card h-100 fsl-plan-card plan-tier-standard text-center p-4">
                                    <div class="card-body d-flex flex-column">
                                        <div class="fsl-plan-header mb-3">
                                            <span class="fsl-plan-badge">進階探索</span>
                                            <h4 class="fsl-plan-title fw-bold mt-2 mb-1">卡點釐清工作坊</h4>
                                            <p class="fsl-plan-subtitle small mb-0">深入看見限制性信念與情緒根源</p>
                                        </div>
                                        <div class="fsl-plan-price-wrap my-3">
                                            <span class="fsl-plan-currency">NT$</span>
                                            <span class="fsl-plan-price">3,600</span>
                                            <span class="fsl-plan-unit">/ 兩週</span>
                                        </div>
                                        <p class="fsl-plan-desc small my-3 flex-grow-1">
                                            包含兩次深度對談與專屬心智圖分析報告，陪伴您逐步鬆開內在緊繃與猶豫。
                                        </p>
                                        <div class="mt-auto pt-3">
                                            <a href="#contact" class="btn fsl-plan-btn w-100 py-2 fw-bold">我要預約</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 方案 3（加上 hottest 類別即自動置中）：重要收費項目 (plan-tier-premium) -->
                            <div class="fsl-plan-slide hottest">
                                <div class="card h-100 fsl-plan-card plan-tier-premium text-center p-4">
                                    <div class="card-body d-flex flex-column">
                                        <div class="fsl-plan-header mb-3">
                                            <span class="fsl-plan-badge">★ 熱門首選推薦</span>
                                            <h4 class="fsl-plan-title fw-bold mt-2 mb-1">第五季重整蛻變計畫</h4>
                                            <p class="fsl-plan-subtitle small mb-0">全方位重塑人生價值與核心選擇</p>
                                        </div>
                                        <div class="fsl-plan-price-wrap my-3">
                                            <span class="fsl-plan-currency">NT$</span>
                                            <span class="fsl-plan-price">8,800</span>
                                            <span class="fsl-plan-unit">/ 完整療程</span>
                                        </div>
                                        <p class="fsl-plan-desc small my-3 flex-grow-1">
                                            四週完整一對一深度陪伴、生命藍圖重整與行動落地追蹤，開啟屬於您的全新生命篇章。
                                        </p>
                                        <div class="mt-auto pt-3">
                                            <a href="#contact" class="btn fsl-plan-btn w-100 py-2 fw-bold">我要預約</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 方案 4：次要收費項目 (plan-tier-standard) -->
                            <div class="fsl-plan-slide">
                                <div class="card h-100 fsl-plan-card plan-tier-standard text-center p-4">
                                    <div class="card-body d-flex flex-column">
                                        <div class="fsl-plan-header mb-3">
                                            <span class="fsl-plan-badge">持續陪伴</span>
                                            <h4 class="fsl-plan-title fw-bold mt-2 mb-1">季度生命教練陪跑</h4>
                                            <p class="fsl-plan-subtitle small mb-0">穩定推進目標與日常狀態校準</p>
                                        </div>
                                        <div class="fsl-plan-price-wrap my-3">
                                            <span class="fsl-plan-currency">NT$</span>
                                            <span class="fsl-plan-price">15,800</span>
                                            <span class="fsl-plan-unit">/ 三個月</span>
                                        </div>
                                        <p class="fsl-plan-desc small my-3 flex-grow-1">
                                            每月兩次深度校準會議與專屬線上即時回饋，確保您在轉變過程中擁有穩固支持力量。
                                        </p>
                                        <div class="mt-auto pt-3">
                                            <a href="#contact" class="btn fsl-plan-btn w-100 py-2 fw-bold">我要預約</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 方案 5：普通收費項目 (plan-tier-basic) -->
                            <div class="fsl-plan-slide">
                                <div class="card h-100 fsl-plan-card plan-tier-basic text-center p-4">
                                    <div class="card-body d-flex flex-column">
                                        <div class="fsl-plan-header mb-3">
                                            <span class="fsl-plan-badge">企業／團體</span>
                                            <h4 class="fsl-plan-title fw-bold mt-2 mb-1">專屬團隊生命藝術沙龍</h4>
                                            <p class="fsl-plan-subtitle small mb-0">客製化小型團體共學與能量重整</p>
                                        </div>
                                        <div class="fsl-plan-price-wrap my-3">
                                            <span class="fsl-plan-currency">NT$</span>
                                            <span class="fsl-plan-price">24,000</span>
                                            <span class="fsl-plan-unit">/ 專場</span>
                                        </div>
                                        <p class="fsl-plan-desc small my-3 flex-grow-1">
                                            為 4 至 8 人小型團隊量身設計的半日沉浸式工作坊，凝聚共識並喚醒團隊內在創造力。
                                        </p>
                                        <div class="mt-auto pt-3">
                                            <a href="#contact" class="btn fsl-plan-btn w-100 py-2 fw-bold">我要預約</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

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
                            <img src="imageFiles/reinaphoto01.jpg" class="img-fluid fsl-instructor-img" alt="田玫琪老師｜田田姐 Reina Tien" onerror="this.src='imageFiles/test/<?php echo rand(1,18); ?>.jpg'">
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
                                        <img src="imageFiles/reinabook01.jpg" class="img-fluid fsl-reina-book-img" alt="職場名模－田玫琪老師代表著作" onerror="this.src='imageFiles/test/<?php echo rand(1,18); ?>.jpg'">
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
        <section id="faqSection" class="py-5 text-white static-bg-zone" style="background-image: url('imageFiles/test/<?php echo rand(1,18); ?>.jpg');">
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

        <!-- 與我們取得聯繫 -->
        <section id="contactus" class="py-5 text-white" style="background-image: url('imageFiles/mainBG4.jpg');">
            <div class="container py-4" style="max-width: 650px;">
                <div class="text-center fsl-header-gap">
                    <h2 class="fsl-section-title-box mb-3">與我們取得聯繫</h2>
                    <p class="fs-5 text-white-50 mb-0">歡迎留下您的需求，我們將儘速回覆</p>
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
<?php 
// 引入完整頁尾與地端腳本載入區
include_once '5sl_include_footer.php'; 
?>
