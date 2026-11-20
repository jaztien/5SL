<?php
$current_page = 'highlights'; // 宣告身分為「更多花絮」子頁面
include_once '5sl_include_header.php';

// 💡 預留後台 MySQL 資料庫對接陣列結構（未來可直接替換為 SELECT * FROM fsl_highlights ORDER BY sort_order ASC）
$all_highlights = array(
    array('id' => 1, 'cat' => 'workshop', 'date' => '2026.10', 'img' => 'imageFiles/test/1.jpg', 'title' => '靜心覺察工作坊', 'desc' => '2026 秋季場・台北工作室', 'video' => ''),
    array('id' => 2, 'cat' => 'salon',    'date' => '2026.10', 'img' => 'imageFiles/test/2.jpg', 'title' => '第五季生命對話沙龍', 'desc' => '精選活動紀錄影片・深度對談', 'video' => 'https://youtu.be'),
    array('id' => 3, 'cat' => 'workshop', 'date' => '2026.09', 'img' => 'imageFiles/test/3.jpg', 'title' => '內在卡點探索課程', 'desc' => '小組深度共學與信念梳理', 'video' => ''),
    array('id' => 4, 'cat' => 'salon',    'date' => '2026.09', 'img' => 'imageFiles/test/4.jpg', 'title' => '藝術療癒與自我重塑', 'desc' => '週末沉浸式色彩與自我對話體驗', 'video' => ''),
    array('id' => 5, 'cat' => 'video',    'date' => '2026.08', 'img' => 'imageFiles/test/5.jpg', 'title' => '學員蛻變真實分享', 'desc' => '訪談實錄・聽見重新發光的故事', 'video' => 'https://youtu.be'),
    array('id' => 6, 'cat' => 'retreat',  'date' => '2026.08', 'img' => 'imageFiles/test/6.jpg', 'title' => '山林靜謐能量營', 'desc' => '兩天一夜自然對話與身心歸零', 'video' => ''),
    array('id' => 7, 'cat' => 'workshop', 'date' => '2026.07', 'img' => 'imageFiles/test/7.jpg', 'title' => '價值觀重整實戰班', 'desc' => '第 12 期結業花絮與生命藍圖繪製', 'video' => ''),
    array('id' => 8, 'cat' => 'workshop', 'date' => '2026.07', 'img' => 'imageFiles/test/8.jpg', 'title' => '呼吸與身體覺察課', 'desc' => '釋放日常緊繃與情緒印記練習', 'video' => ''),
    array('id' => 9, 'cat' => 'salon',    'date' => '2026.06', 'img' => 'imageFiles/test/9.jpg', 'title' => '年度生命藝術展覽', 'desc' => '開幕式精華片段與創作導覽', 'video' => 'https://youtu.be'),
    array('id' => 10,'cat' => 'workshop', 'date' => '2026.06', 'img' => 'imageFiles/test/10.jpg','title' => '一對一教練引導實況', 'desc' => '專注傾聽與陪伴的微光時刻', 'video' => ''),
    array('id' => 11,'cat' => 'salon',    'date' => '2026.05', 'img' => 'imageFiles/test/11.jpg','title' => '女性力量覺醒茶會', 'desc' => '午後溫暖交流與生命故事分享', 'video' => ''),
    array('id' => 12,'cat' => 'salon',    'date' => '2026.05', 'img' => 'imageFiles/test/12.jpg','title' => '跨界心靈美學講座', 'desc' => '特邀講師對談紀錄・美學與自癒', 'video' => ''),
    array('id' => 13,'cat' => 'retreat',  'date' => '2026.04', 'img' => 'imageFiles/test/13.jpg','title' => '海岸晨光靜心步行', 'desc' => '在海風與浪聲中重新校準內在節奏', 'video' => ''),
    array('id' => 14,'cat' => 'workshop', 'date' => '2026.04', 'img' => 'imageFiles/test/14.jpg','title' => '面對恐懼・鬆開自己專場', 'desc' => '高安全感團體支持與情緒釋放', 'video' => ''),
    array('id' => 15,'cat' => 'retreat',  'date' => '2026.03', 'img' => 'imageFiles/test/15.jpg','title' => '春季茶席與靜觀練習', 'desc' => '一茶一念・回到當下的純粹感知', 'video' => ''),
    array('id' => 16,'cat' => 'salon',    'date' => '2026.03', 'img' => 'imageFiles/test/16.jpg','title' => '第五季創始回顧之夜', 'desc' => '歷屆學員重聚與生命藝術展演', 'video' => 'https://youtu.be')
);
?>

        <!-- 頁面頂部視覺 Banner 區 -->
        <section class="py-5 static-bg-zone text-white" style="background-image: url('imageFiles/heroImage002.jpg');">
            <div class="container py-5 text-center">
                <div class="fsl-header-gap mb-4">
                    <h2 class="fsl-section-title-box mb-3">課程&amp;活動花絮</h2>
                    <p class="fs-5 text-white-50 mb-0">生命中的「改變」往往只是過程中的那一微秒</p>
                </div>
                <p class="text-white-50 mx-auto mb-0" style="max-width: 640px; letter-spacing: 1px;">
                    紀錄每一次真誠相遇、靜心覺察與重新發光的片刻。點擊任意照片可放大檢視完整畫面與活動紀實，點擊播放鍵即可觀看現場影音實錄。
                </p>
            </div>
        </section>

        <!-- 花絮網格總覽與分類篩選區 -->
        <section class="py-5 bg-dark text-white border-top border-secondary border-opacity-25">
            <div class="container py-4">
                
                <!-- 分類篩選按鈕列 -->
                <div class="d-flex flex-wrap justify-content-center gap-2 mb-5" id="fslGalleryFilterBar">
                    <button type="button" class="btn btn-sm fsl-faq-filter-btn active" data-gallery-filter="all">全部紀錄 (<?php echo count($all_highlights); ?>)</button>
                    <button type="button" class="btn btn-sm fsl-faq-filter-btn" data-gallery-filter="workshop">課程與工作坊</button>
                    <button type="button" class="btn btn-sm fsl-faq-filter-btn" data-gallery-filter="salon">生命藝術沙龍</button>
                    <button type="button" class="btn btn-sm fsl-faq-filter-btn" data-gallery-filter="retreat">自然能量營</button>
                    <button type="button" class="btn btn-sm fsl-faq-filter-btn" data-gallery-filter="video"><i class="bi bi-play-circle me-1"></i>影音實錄</button>
                </div>

                <!-- 響應式四欄花絮作品網格 -->
                <div class="row g-4" id="fslHighlightsGrid">
                    <?php foreach ($all_highlights as $idx => $item): 
                        $has_video = !empty($item['video']);
                        $is_initially_hidden = ($idx >= 8); // 預設先顯示前 8 筆，其餘點擊「載入更多」展開
                    ?>
                    <div class="col-sm-6 col-md-4 col-lg-3 fsl-grid-card-col <?php echo $is_initially_hidden ? 'd-none fsl-extra-card' : ''; ?>" 
                         data-cat="<?php echo htmlspecialchars($item['cat']); ?>" 
                         data-has-video="<?php echo $has_video ? '1' : '0'; ?>">
                        
                        <div class="portfolio-card-wrap h-100">
                            <div class="card border-0 shadow-sm overflow-hidden portfolio-card fsl-grid-highlight-card">
                                <!-- 右上角時間標籤 -->
                                <span class="badge bg-dark bg-opacity-75 text-warning border border-warning border-opacity-50 position-absolute top-0 end-0 m-3 px-2 py-1" style="z-index: 4; font-size: 0.72rem; letter-spacing: 1px;">
                                    <?php echo htmlspecialchars($item['date']); ?>
                                </span>

                                <img src="<?php echo htmlspecialchars($item['img']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                     loading="lazy"
                                     onerror="this.src='imageFiles/test/1.jpg'">

                                <?php if ($has_video): ?>
                                    <!-- 影片卡片：共用全站白色波紋播放鈕與 Lightbox 彈窗 -->
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

                    </div>
                    <?php endforeach; ?>
                </div>
                <!-- 底部控制區：載入更多 & 返回首頁 -->
                <div class="text-center mt-5 pt-3 d-flex flex-wrap justify-content-center gap-3">
                    <?php if (count($all_highlights) > 8): ?>
                        <button type="button" class="btn btn-warning text-dark rounded-3 px-5 py-2 fw-bold" id="fslLoadMoreHighlightsBtn">
                            載入更多花絮 <i class="bi bi-plus-lg ms-1"></i>
                        </button>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-outline-light rounded-3 px-4 py-2 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> 返回首頁
                    </a>
                </div>

            </div>
        </section>

        <!-- ==========================================================================
            共用 Lightbox 1：與螢幕大小完美自適應的影音彈出視窗
            ========================================================================== -->
        <div class="modal fade" id="videoLightboxModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content bg-transparent border-0">
                    <div class="text-end mb-2">
                        <button type="button" class="btn-close btn-close-white fs-4 shadow-none" data-bs-dismiss="modal" aria-label="Close" id="btnOverlayVideoClose"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="ratio ratio-16x9 shadow-2xl rounded-3 overflow-hidden bg-black">
                            <iframe src="" id="lightboxIframe" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================================================
            共用 Lightbox 2：純圖片花絮放大預覽燈箱
            ========================================================================== -->
        <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content bg-transparent border-0">
                    <div class="text-end mb-2">
                        <button type="button" class="btn-close btn-close-white fs-4 shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0 text-center">
                        <div class="d-inline-block position-relative rounded-3 overflow-hidden shadow-lg bg-dark border border-secondary border-opacity-25">
                            <img src="" id="previewLightboxImg" alt="花絮大圖預覽" class="img-fluid d-block" style="max-height: 80vh; width: auto; object-fit: contain;">
                            <div class="p-3 bg-dark bg-opacity-95 text-start border-top border-secondary border-opacity-25">
                                <h5 class="fw-bold text-white mb-1" id="previewLightboxTitle"></h5>
                                <p class="text-white-50 small mb-0" id="previewLightboxDesc"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php
// 引入完整頁尾與地端腳本載入區
include_once '5sl_include_footer.php';
?>
