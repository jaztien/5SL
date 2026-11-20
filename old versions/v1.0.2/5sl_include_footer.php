    </main>

    <!-- 隱形觀測錨點：精準埋在大 Footer 上方，只要它一露頭，立刻驅動電梯級無縫交接 -->
    <div id="5sl-footer-observer-anchor" style="height: 1px; margin-bottom: -1px; clear: both;"></div>

    <!-- 【智慧型雙階固定懸浮控制條】與大 Footer 結構完全獨立，避免打包干擾 -->
    <div class="fsl-minimal-sticky-bar" id="stickyFloatingBar">
        <div class="container footer-logo-row position-relative d-flex align-items-center">
            <!-- 獨立 LOGO 圖層：精準向上再升高 20px (透過 CSS margin-top 達成極致破格) -->
            <div class="sticky-logo-box">
                <a href="index.php" class="d-inline-block">
                    <img src="imageFiles/5slLOGO_W_200x200.png" alt="5SL LOGO" class="img-fluid">
                </a>
            </div>
            <!-- 智慧型動態抬頭文字 -->
            <div class="sticky-text-box">
                <h5 class="fw-bold text-white m-0">
                    <span class="brand-text-en fw-bold">
                        F<span class="fw-light" style="font-weight:200 !important;font-size:0.85em;">ifth </span>
                        S<span class="fw-light" style="font-weight:200 !important;font-size:0.85em;">eason </span>
                        L<span class="fw-light" style="font-weight:200 !important;font-size:0.85em;">ife | </span>
                        <span class="fw-light" style="font-weight:400 !important;font-size:0.85em;">人生第五季生命藝術工作室</span>
                    </span>
                </h5>
            </div>
        </div>
    </div>
    <div class="bg-dark fsl-minimal-sticky-bar" id="stickyFloatingBarBG">&nbsp;</div>
    <!-- 大 Footer 文字之上部份（含 LOGO），此部份不必使用動態反相  -->
    <div class="bg-dark fsl-minimal-sticky-bar" id="mainFloatingBar">
        <div class="container footer-logo-row position-relative d-flex align-items-center">
            <!-- 獨立 LOGO 圖層：精準向上再升高 20px (透過 CSS margin-top 達成極致破格) -->
            <div class="sticky-logo-box">
                <a href="index.php" class="d-inline-block">
                    <img src="imageFiles/5slLOGO_W_200x200.png" alt="5SL LOGO" class="img-fluid">
                </a>
            </div>
        </div>
    </div>

    <!-- 【常規大 Footer】 -->
    <footer class="bg-dark text-light pb-3 footer-overflow-master" id="realMainFooter">
        <div class="container position-relative">
            <!-- 下方所有訊息 row -->
            <div class="row g-4 mb-5 footer-content-main-row">
                <!-- 最左欄 -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="fw-bold text-white mb-3">
                        <span class="brand-text-en fw-bold">
                            F<span class="fw-light" style="font-weight:200 !important;">ifth </span>
                            S<span class="fw-light" style="font-weight:200 !important;">eason </span>
                            L<span class="fw-light" style="font-weight:200 !important;">ife | </span>
                            <span class="fw-light" style="font-weight:400 !important;">人生第五季生命藝術工作室</span>
                        </span>
                    </h5>
                    <p class="footer-text-gray small lh-lg mb-0">我們致力於提供高穩定度、前後端分離邏輯的架架模板。透過 Bootstrap 5 與 jQuery 的完美結合，打造出兼具美觀與商用實效的優質網站。</p>
                    <div class="d-flex gap-3 fs-5 mt-4">
                        <a href="#" class="footer-social-icon" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="footer-social-icon" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="footer-social-icon" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="footer-social-icon" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <!-- 中間第二欄：快速導覽 -->
                <div class="col-6 col-md-3 col-lg-2 offset-lg-1">
                    <h6 class="fw-bold text-white mb-3 border-bottom border-secondary pb-2">快速導覽</h6>
                    <ul class="list-unstyled small lh-lg">
                        <li class="mb-2"><a href="index.php" class="footer-link-item"><i class="bi bi-chevron-right small me-1"></i>網站首頁</a></li>
                        <li class="mb-2"><a href="index.php#features" class="footer-link-item"><i class="bi bi-chevron-right small me-1"></i>服務項目</a></li>
                        <li class="mb-2"><a href="index.php#portfolio" class="footer-link-item"><i class="bi bi-chevron-right small me-1"></i>課程介紹</a></li>
                        <li class="mb-2"><a href="index.php#pricing" class="footer-link-item"><i class="bi bi-chevron-right small me-1"></i>資費方案</a></li>
                        <li><a href="index.php#mentor" class="footer-link-item"><i class="bi bi-chevron-right small me-1"></i>顧問講師</a></li>
                    </ul>
                </div>

                <!-- 中間第三欄：法律與政策 -->
                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="fw-bold text-white mb-3 border-bottom border-secondary pb-2">法律與政策</h6>
                    <ul class="list-unstyled small lh-lg">
                        <li class="mb-2"><a href="5sl_privacy.php" class="footer-link-item"><i class="bi bi-chevron-right small me-1"></i>隱私權政策</a></li>
                        <li class="mb-2"><a href="5sl_terms.php" class="footer-link-item"><i class="bi bi-chevron-right small me-1"></i>服務條款</a></li>
                        <li class="mb-2"><a href="5sl_delivery.php" class="footer-link-item"><i class="bi bi-chevron-right small me-1"></i>商品交付政策</a></li>
                        <li class="mb-2"><a href="5sl_refund.php" class="footer-link-item"><i class="bi bi-chevron-right small me-1"></i>退費退款政策</a></li>
                        <li><a href="index.php#faqSection" class="footer-link-item"><i class="bi bi-chevron-right small me-1"></i>常見問題 FAQ</a></li>
                    </ul>
                </div>

                <!-- 最右欄：Newsletter 訂閱 -->
                <div class="col-md-6 col-lg-3">
                    <h6 class="fw-bold text-white mb-3 border-bottom border-secondary pb-2">電子報訂閱</h6>
                    <p class="footer-text-gray small mb-3">訂閱我們的最新週報，獲取第一手產品開發動態與優惠資訊。</p>
                    <form class="input-group input-group-sm mb-4" id="newsletterForm">
                        <input type="email" class="form-control bg-secondary text-white border-0 py-2" placeholder="您的電子信箱" aria-label="Email" required style="font-size: 0.8rem; background-color: #343a40 !important;">
                        <button class="btn btn-outline-light px-3 fw-bold" type="submit">訂閱</button>
                    </form>
                    <div class="pt-2 border-top border-secondary border-opacity-20">
                        <p class="footer-text-gray small mb-2 d-flex align-items-center">
                            <i class="bi bi-telephone text-white me-2"></i> 02-2911-XXXX
                        </p>
                        <p class="footer-text-gray small mb-0 d-flex align-items-center">
                            <i class="bi bi-envelope text-white me-2"></i> info@5sl-studio.com
                        </p>
                    </div>
                </div>
            </div>

            <!-- 最底部版權宣告 -->
            <hr class="border-secondary opacity-25">
            <div class="row pt-2 small footer-text-gray">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; 2026 Fifth Season Life Studio. 版權所有。</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <p class="mb-0">Designed with by 5SL Group</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- 返回頂端浮動按鈕 -->
    <button type="button" id="backToTopBtn" class="btn-back-to-top shadow-sm" aria-label="返回頂端">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- ==========================================================================
        💾 VERSION LOG: 2026-11-08 18:00 CST 
        🚀 幸運大抽獎轉盤實體舞台面板 (預留 Canvas 2D 高密度黑金扇區)
        ========================================================================== -->
    <div style="position: fixed; bottom: 100px; left: 30px; z-index: 1050; display: none;" id="fslGlobalWheelPanel">
        <!-- 🚀 補強：為大轉盤增設專屬高奢右上角關閉鈕 -->
        <button type="button" class="fsl-close-stage-btn" id="fslCloseWheelBtn" aria-label="關閉轉盤"><i class="bi bi-x-lg"></i></button>
        <div class="fsl-wheel-wrapper">
            <div class="fsl-wheel-outer-ring">
                <!-- 內盤 Canvas 實體 -->
                <canvas id="fslLotteryCanvas"></canvas>
            </div>
            <!-- 中央 3D 黃金懸浮抽獎指針按紐 -->
            <div class="fsl-wheel-pointer-box" id="fslWheelStartBtn"></div>
        </div>
    </div>

    <!-- ==========================================================================
        🚀 遮罩 2：全站通用 AJAX 智慧型半透明等待遮罩實體
        ========================================================================== -->
    <div class="fsl-global-loading" id="globalLoadingOverlay">
        <div class="fsl-spinner mb-3"></div>
        <div class="text-light fw-bold small tracking-wide">系統處理中，請稍候...</div>
    </div>

    <!-- ==========================================================================
        💾 VERSION LOG: 2026-11-08 15:22 CST 
        🚀 遮罩 3 終極外殼：工業級通用動態注入舞台 (不綁定特定內容，由 Class 全權接管)
        ========================================================================== -->
    <div class="fsl-coupon-modal" id="couponGiftModal">
        <!-- 左右 3D 舞台投射燈 -->
        <div class="fsl-stage-light-l"></div>
        <div class="fsl-stage-light-r"></div>
        <!-- Canvas 物理引擎畫布 -->
        <canvas id="fslParallaxCanvas"></canvas>
        <!-- 🚀 核心插槽：內部的卡片與按鈕結構已被完全釋放，由 JS 自動注入 -->
        <div id="fslDynamicContentSlot" style="z-index: 2000005 !important; position: relative;"></div>
    </div>
<?php
// =========================================================================
// 讀取全站幸運大轉盤設定與獎項格位 (fsl_lotteries + fsl_lottery_items)
// =========================================================================
$wheel_keycode = 'LUCKY_WHEEL_2026';
$wheel_awards_js = [];

if (isset($DB)) {
    $lot_row = $DB->row("SELECT lot_id, lot_title FROM fsl_lotteries WHERE lot_keycode = ? AND lot_status = 1", [$wheel_keycode]);
    if ($lot_row) {
        $slot_rows = $DB->query(
            "SELECT loti_slot_order, loti_label, loti_color FROM fsl_lottery_items WHERE lot_id = ? ORDER BY loti_slot_order ASC",
            [(int)$lot_row['lot_id']]
        );
        if (is_array($slot_rows)) {
            foreach ($slot_rows as $idx => $sr) {
                $bg_color = !empty($sr['loti_color']) ? $sr['loti_color'] : (($idx % 2 === 0) ? '#1e2125' : '#2b3035');
                // 若底色是亮金色 (#ffc107)，文字自動用深黑，否則用暖金或純白
                $txt_color = (strtolower(trim($bg_color)) === '#ffc107') ? '#121316' : (($idx % 2 === 0) ? '#ffc107' : '#ffffff');
                $wheel_awards_js[] = [
                    'slot'  => (int)$sr['loti_slot_order'],
                    'name'  => $sr['loti_label'],
                    'color' => $bg_color,
                    'text'  => $txt_color
                ];
            }
        }
    }
}
?>
<script>
window.fslWheelConfig = {
    keycode: <?php echo json_encode($wheel_keycode); ?>,
    isLoggedIn: <?php echo isset($_SESSION['mbID']) ? 'true' : 'false'; ?>,
    awards: <?php echo json_encode($wheel_awards_js, JSON_UNESCAPED_UNICODE); ?>
};
</script>

    <!-- 本地端外掛載入 -->
    <script src="pluginFiles/jquery/jquery-4.0.0.min.js"></script>
    <script src="pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
    <script src="pluginFiles/flatpickr/flatpickr.min.js"></script>
    <script src="pluginFiles/flatpickr/location/zh_tw.js"></script>
    <script src="scriptFiles/5seasonlife.js"></script>
    <script src="scriptFiles/5sl_EpicEngine.js"></script>
    <script src="scriptFiles/5sl_LotteryWheel.js"></script>
    <!-- ==========================================================================
        💾 VERSION LOG: 2026-11-08 09:56 CST 
        💡 狀態：電腦雙階無縫對接 (防先潛後浮) + 首頁透明導航列 JS 監聽穩固版
       ========================================================================== */ -->
    <script>
        $(function() {
            // ==========================================================================
            // 等待遮罩系統連動操作
            // ==========================================================================
            const $navbarCollapse = $('.navbar-collapse');
            const $body = $('body');

            // 當手機選單抽屜「完全展開完成」的那一微秒，強制鎖死 body
            $navbarCollapse.on('shown.bs.collapse', function() {
                $body.addClass('nav-menu-open');
            });

            // 當手機選單抽屜「開始收合」或隱藏的那一微秒，立刻解除 body 鎖定並還原滾動
            $navbarCollapse.on('hidden.bs.collapse', function() {
                $body.removeClass('nav-menu-open');
            });

            // 防錯輔助防線：點擊導航抽屜以外的任何網頁空白處，自動溫和收回選單並解除鎖定
            $(document).on('click', function(e) {
                if ($body.hasClass('nav-menu-open')) {
                    if (!$(e.target).closest('.navbar').length) {
                        $navbarCollapse.collapse('hide');
                        $body.removeClass('nav-menu-open');
                    }
                }
            });

            // 【觸發與解除方法】：在任何 AJAX 操作前，直接依據以下方法調用
            // fslLoader.show();fslLoader.hide();
            window.fslLoader = {
                show: function() {
                    $('#globalLoadingOverlay').addClass('show');
                },
                hide: function() {
                    $('#globalLoadingOverlay').removeClass('show');
                }
            };

            // 核心狀態記憶體：用來記憶當前是誰想關閉 ('wheel' 或 'coupon')
            // 🎯 3. 二次確認：使用者按「繼續操作」➜ 溫和退場，隱藏防呆黑紗
            $(document).off('click', '#fslConfirmNo').on('click', '#fslConfirmNo', function(e) {
                e.preventDefault();
                $('#fslConfirmAlertMask').removeClass('show');
            });

            // 🎯 4. 二次確認：使用者按「是，放棄」➜ 根據畫面上正在進行的動作，現地分流處理
            $(document).off('click', '#fslConfirmYes').on('click', '#fslConfirmYes', function(e) {
                e.preventDefault();
                $('#fslConfirmAlertMask').removeClass('show');

                // 🚀【現地狀態感知】：如果 3D 優惠券大舞台正亮著（有 .show 類別），優先摧毀它
                if ($('#couponGiftModal').hasClass('show') && window.fslEpicEngine) {
                    window.fslEpicEngine.close();
                }

                // 🚀【現地狀態感知】：如果此時大轉盤是醒著的（正在顯示），命令它淡出隱形
                if ($('#fslGlobalWheelPanel').is(':visible')) {
                    $('#fslGlobalWheelPanel').fadeOut(300);
                }
            });

            // =======================================================================
            // =======================================================================
            // 【觀察器任務】偵測網頁進入/離開頂端以及底端兩個位置點時的操作，並統一控制
            //  主要用於操控「回到頂端」按鈕的顯示，以及「懸浮Footer」以及「固定Footer」間的切換
            // =======================================================================
            // =======================================================================
            // 1. 全域狀態開關
            let isAwayFromTop = false;
            let isAtFooter = false;

            // 2. 💡 統一的目標控制中心（僅在電腦版首頁啟用懸浮 Footer 交替，其餘頁面始終顯示固定 Footer）
            function updateTargetDisplay() {
                // 📱 行動裝置過濾：偵測當前是否為手機或平板（螢幕寬度小於 992px）
                var isMobileOrTablet = $(window).width() < 992;

                // 🏠 首頁環境偵測：透過是否存在 #heroSection 精準判斷當前是否為首頁
                var isHomePage = $('#heroSection').length > 0;

                // 🚀【非首頁（含會員中心所有子頁面）專屬鎖定】：
                // 停用懸浮 Footer，讓 #mainFloatingBar 始終顯示，僅保留「回到頂端」按鈕的動態顯示
                if (!isHomePage) {
                    $('#stickyFloatingBar').removeClass('visible');
                    $('#stickyFloatingBarBG').removeClass('visible');
                    $('#mainFloatingBar').addClass('visible');

                    if (isAwayFromTop) {
                        $('#backToTopBtn').addClass('visible');
                    } else {
                        $('#backToTopBtn').removeClass('visible');
                    }
                    return;
                }

                // 以下為【首頁 index.php】專屬的雙階無縫交接邏輯
                var activeFooterState = isMobileOrTablet ? false : isAtFooter;

                if (activeFooterState) {
                    // 【狀態一：到達底部】執行任務 B（桌機版限定動作）
                    $('#stickyFloatingBar').removeClass('visible');
                    $('#stickyFloatingBarBG').removeClass('visible');
                    $('#mainFloatingBar').addClass('visible');

                } else if (isAwayFromTop) {
                    // 【狀態二：離開頂端 300px】且「目前不在底部」
                    $('.navbar').addClass('navbar-scrolled');
                    $('#stickyFloatingBar').addClass('visible');
                    $('#stickyFloatingBarBG').addClass('visible');
                    $('#backToTopBtn').addClass('visible');
                    $('#mainFloatingBar').removeClass('visible');

                } else {
                    // 【狀態三：回到最頂端 300px 內】隱藏目標
                    $('.navbar').removeClass('navbar-scrolled');
                    $('#stickyFloatingBar').removeClass('visible');
                    $('#stickyFloatingBarBG').removeClass('visible');
                    $('#backToTopBtn').removeClass('visible');
                    // 行動版始終顯示大 Footer LOGO
                    if (isMobileOrTablet) {
                        $('#mainFloatingBar').addClass('visible');
                    }
                }
            }

            // ------------------------------------------
            // 【任務 A】偵測網頁離開最頂端 300px 之後
            // ------------------------------------------
            const fslTopOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0
            };

            if (!window.fslTopObserverInstance) {
                window.fslTopObserverInstance = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (!entry.isIntersecting) {
                            isAwayFromTop = true;
                        } else {
                            isAwayFromTop = false;
                        }
                        updateTargetDisplay();
                    });
                }, fslTopOptions);
            }

            var topSentinel = document.getElementById('top-sentinel');
            if (topSentinel && window.fslTopObserverInstance) {
                window.fslTopObserverInstance.observe(topSentinel);
            }

            // ------------------------------------------
            // 【任務 B】偵測到達底部 Footer / 離開底端
            // ------------------------------------------
            const fslFooterOptions = {
                root: null,
                rootMargin: '0px 0px 0px 0px', // 可調整提早觸發的緩衝 px
                threshold: 0
            };

            if (!window.fslFooterObserverInstance) {
                window.fslFooterObserverInstance = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            // 往下滾：碰到大 Footer，狀態改為 true
                            isAtFooter = true;
                        } else {
                            // 往上滾：離開大 Footer，且確認其座標掉到視窗底線下方時
                            if (entry.boundingClientRect.top > window.innerHeight) {
                                // 💡 離開底端！狀態洗回 false，觸發回滾
                                isAtFooter = false;
                            }
                        }
                        updateTargetDisplay();
                    });
                }, fslFooterOptions);
            }

            var footerElement = document.getElementById('realMainFooter');
            if (footerElement && window.fslFooterObserverInstance) {
                window.fslFooterObserverInstance.observe(footerElement);
            }

            // ------------------------------------------
            // 視窗縮放監聽與初始執行
            // ------------------------------------------
            $(window).off('resize.fsl_nav').on('resize.fsl_nav', function() {
                updateTargetDisplay();
            });

            updateTargetDisplay();

            //=======================================================================
            //=======================================================================
            // 各類按鍵操作
            //=======================================================================
            //=======================================================================

            // 1. AJAX 安全登出
            $(document).on('click', '.btn-ajax-logout', function(e) {
                e.preventDefault();
                $.ajax({
                    url: './funcFiles/5sl_act_member.php',
                    type: 'POST',
                    data: {
                        action: 'act_member_logout'
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        if(!confirm('確定要登出嗎？')) {
                            return false;
                        }
                    },
                    success: function(response) {
                        if (response.success) {
                            window.location.href = 'index.php';
                        }
                    }
                });
            });

            // 2. 智慧置頂
            $('#backToTopBtn').on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: 0
                }, 400);
            });



        }); // jQuery 閉合
    </script>
    <!-- 🚀【萬用高奢雙重防呆二次確認彈窗實體】 -->
    <div class="fsl-confirm-alert-mask" id="fslConfirmAlertMask">
        <div class="fsl-confirm-alert-box">
            <div class="text-warning fs-1 mb-2"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <h5 class="text-white fw-bold mb-2">確定要放棄？</h5>
            <p class="text-muted small mb-4">當前操作尚未儲存，放棄後將無法復原。</p>
            <div class="d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold text-white-50" id="fslConfirmNo">繼續操作</button>
                <button type="button" class="btn btn-warning btn-sm px-4 fw-bold" id="fslConfirmYes">是，放棄</button>
            </div>
        </div>
    </div>
    </body>

    </html>