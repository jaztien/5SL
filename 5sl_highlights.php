<?php
$current_page = 'highlights'; // 宣告身分為「更多花絮」子頁面
include_once '5sl_include_header.php';

// 1. 從公開多媒體資料表 fsl_media 撈取所有公開上架紀錄（依事件發生日期與排序權重）
$sql_media = "
    SELECT med_id, med_event_date, med_category, med_type, med_title, med_brief, med_url, med_duration
    FROM fsl_media
    WHERE med_status = 1
    ORDER BY med_event_date DESC, med_sort ASC, med_id DESC
";
$raw_media = isset($DB) ? DB->query($sql_media) : array();
if (!is_array($raw_media)) $raw_media = array();

$all_highlights = array();
foreach ($raw_media as $row) {
    $mid   = (int)$row['med_id'];
    $mtype = (int)$row['med_type']; // 1=圖檔, 2=YouTube視訊, 3=聲音檔

    // 優先讀取後台上傳的主圖與縮圖 (./5sladmin/uploadFiles/md/mdimg_{id}_.jpg)
    $thumb_disk = './5sladmin/uploadFiles/md/mdimg_' . $mid . '_thumb.jpg';
    $main_disk  = './5sladmin/uploadFiles/md/mdimg_' . $mid . '_.jpg';
    $fallback   = 'imageFiles/test/' . ((($mid - 1) % 18) + 1) . '.jpg';

    $thumb_url = file_exists($thumb_disk) ? ($thumb_disk . '?v=' . filemtime($thumb_disk)) : (file_exists($main_disk) ? ($main_disk . '?v=' . filemtime($main_disk)) : $fallback);
    $main_url  = file_exists($main_disk)  ? ($main_disk . '?v=' . filemtime($main_disk))   : $thumb_url;

    $all_highlights[] = array(
        'id'        => $mid,
        'cat'       => $row['med_category'] ?: 'workshop',
        'type'      => $mtype,
        'date'      => !empty($row['med_event_date']) ? date('Y.m.d', strtotime($row['med_event_date'])) : '',
        'thumb_img' => $thumb_url,
        'main_img'  => $main_url,
        'title'     => $row['med_title'],
        'desc'      => $row['med_brief'] ?? '',
        'media_url' => trim((string)($row['med_url'] ?? '')),
        'duration'  => trim((string)($row['med_duration'] ?? ''))
    );
}
?>

        <!-- 頁面頂部視覺 Banner 區 -->
        <section class="py-5 static-bg-zone text-white" style="background-image: url('imageFiles/heroImage002.jpg');">
            <div class="container py-5 text-center">
                <div class="fsl-header-gap mb-4">
                    <h2 class="fsl-section-title-box mb-3">課程&amp;活動花絮</h2>
                    <p class="fs-5 text-white-50 mb-0">生命中的「改變」往往只是過程中的那一微秒</p>
                </div>
                <p class="text-white-50 mx-auto mb-0" style="max-width: 640px; letter-spacing: 1px;">
                    紀錄每一次真誠相遇、靜心覺察與重新發光的片刻。點擊照片可放大檢視完整紀實，點擊播放鍵即可觀看現場影音或聆聽聲頻實錄。
                </p>
            </div>
        </section>

        <!-- 花絮網格總覽與分類篩選區 -->
        <section class="py-5 bg-transparent border-top border-secondary border-opacity-25">
            <div class="container py-4">
                
                <!-- 分類篩選按鈕列 -->
                <div class="d-flex flex-wrap justify-content-center gap-2 mb-5" id="fslGalleryFilterBar">
                    <button type="button" class="btn btn-sm fsl-faq-filter-btn active" data-gallery-filter="all">全部紀錄 (<?php echo count($all_highlights); ?>)</button>
                    <button type="button" class="btn btn-sm fsl-faq-filter-btn" data-gallery-filter="workshop">課程與工作坊</button>
                    <button type="button" class="btn btn-sm fsl-faq-filter-btn" data-gallery-filter="salon">生命藝術沙龍</button>
                    <button type="button" class="btn btn-sm fsl-faq-filter-btn" data-gallery-filter="retreat">自然能量營</button>
                    <button type="button" class="btn btn-sm fsl-faq-filter-btn" data-gallery-filter="video"><i class="bi bi-play-circle me-1"></i>影音實錄</button>
                    <button type="button" class="btn btn-sm fsl-faq-filter-btn" data-gallery-filter="audio"><i class="bi bi-headphones me-1"></i>聲音紀實</button>
                </div>

                <!-- 響應式四欄花絮作品網格 -->
                <div class="row g-4" id="fslHighlightsGrid">
                    <?php if (empty($all_highlights)): ?>
                        <div class="col-12 text-center py-5 text-white-50">
                            目前尚無公開上架的活動花絮紀錄，敬請期待近期活動更新！
                        </div>
                    <?php else: ?>
                        <?php foreach ($all_highlights as $idx => $item): 
                            $mtype     = (int)$item['type']; // 1=圖檔, 2=YouTube視訊, 3=聲音檔
                            $has_video = ($mtype === 2 && !empty($item['media_url']));
                            $has_audio = ($mtype === 3 && !empty($item['media_url']));
                            $is_initially_hidden = ($idx >= 8);
                        ?>
                        <div class="col-sm-6 col-md-4 col-lg-3 fsl-grid-card-col <?php echo $is_initially_hidden ? 'd-none fsl-extra-card' : ''; ?>" 
                             data-cat="<?php echo htmlspecialchars($item['cat']); ?>" 
                             data-has-video="<?php echo $has_video ? '1' : '0'; ?>"
                             data-has-audio="<?php echo $has_audio ? '1' : '0'; ?>">
                            
                            <div class="portfolio-card-wrap h-100">
                                <div class="card border-0 shadow-sm overflow-hidden portfolio-card fsl-grid-highlight-card"
                                     style="cursor: pointer;"
                                     data-media-type="<?php echo $mtype; ?>"
                                     data-main-img="<?php echo htmlspecialchars($item['main_img']); ?>"
                                     data-media-url="<?php echo htmlspecialchars($item['media_url']); ?>"
                                     data-title="<?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                     data-desc="<?php echo htmlspecialchars($item['desc'], ENT_QUOTES, 'UTF-8'); ?>"
                                     data-date="<?php echo htmlspecialchars($item['date']); ?>">
                                    
                                    <!-- 左上角媒體類型小標籤（僅視訊與音檔顯示，保持純照片簡潔美感） -->
                                    <?php if ($has_video): ?>
                                        <span class="badge bg-danger position-absolute top-0 start-0 m-3 px-2 py-1 shadow-sm" style="z-index: 4; font-size: 0.7rem;">
                                            <i class="bi bi-youtube me-1"></i>影音<?php echo $item['duration'] ? ' • ' . htmlspecialchars($item['duration']) : ''; ?>
                                        </span>
                                    <?php elseif ($has_audio): ?>
                                        <span class="badge bg-info text-dark position-absolute top-0 start-0 m-3 px-2 py-1 shadow-sm" style="z-index: 4; font-size: 0.7rem;">
                                            <i class="bi bi-soundwave me-1"></i>音頻<?php echo $item['duration'] ? ' • ' . htmlspecialchars($item['duration']) : ''; ?>
                                        </span>
                                    <?php endif; ?>

                                    <!-- 右上角事件發生日期標籤 -->
                                    <span class="badge bg-dark bg-opacity-75 text-warning border border-warning border-opacity-50 position-absolute top-0 end-0 m-3 px-2 py-1" style="z-index: 4; font-size: 0.72rem; letter-spacing: 1px;">
                                        <?php echo htmlspecialchars($item['date']); ?>
                                    </span>

                                    <img src="<?php echo htmlspecialchars($item['thumb_img']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                         loading="lazy"
                                         onerror="this.src='imageFiles/test/1.jpg'">

                                    <?php if ($has_video): ?>
                                        <!-- YouTube 視訊播放鈕 -->
                                        <button type="button" class="video-play-btn fsl-card-play-btn" aria-label="播放影片">
                                            <div class="play-icon-triangle"></div>
                                            <div class="play-btn-ripple"></div>
                                        </button>
                                    <?php elseif ($has_audio): ?>
                                        <!-- 聲音檔耳機聲波播放鈕（保留 fsl-card-play-btn 確保正中央定位） -->
                                        <button type="button" class="video-play-btn fsl-card-play-btn fsl-card-audio-btn" style="background: rgba(13, 202, 240, 0.92); border-color: #fff;" aria-label="聆聽音頻">
                                            <i class="bi bi-headphones text-dark fs-3" style="line-height: 1;"></i>
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
                    <?php endif; ?>
                </div>

                <!-- 底部控制區：載入更多 & 返回首頁 -->
                <div class="text-center mt-5 pt-3 d-flex flex-wrap justify-content-center gap-3">
                    <?php if (count($all_highlights) > 8): ?>
                        <button type="button" class="btn btn-warning text-dark rounded-3 px-5 py-2 fw-bold" id="fslLoadMoreHighlightsBtn">
                            載入更多花絮 <i class="bi bi-plus-lg ms-1"></i>
                        </button>
                    <?php endif; ?>
                    <a href="index.php#portfolio" class="btn btn-outline-light rounded-3 px-4 py-2 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> 返回首頁
                    </a>
                </div>

            </div>
        </section>

        <!-- ==========================================================================
            共用 Lightbox 1：與螢幕大小完美自適應的影音彈出視窗 (YouTube 視訊)
            ========================================================================== -->
        <div class="modal fade" id="hlVideoLightboxModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content bg-transparent border-0">
                    <div class="d-flex justify-content-between align-items-center mb-2 text-white">
                        <span class="fw-bold text-warning small" id="hlVideoLightboxTitle"></span>
                        <button type="button" class="btn-close btn-close-white fs-4 shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="ratio ratio-16x9 shadow-2xl rounded-3 overflow-hidden bg-black">
                            <iframe src="" id="hlLightboxIframe" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; web-share; compute-pressure" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
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
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h5 class="fw-bold text-white mb-0" id="previewLightboxTitle"></h5>
                                    <span class="badge bg-warning text-dark" id="previewLightboxDate"></span>
                                </div>
                                <p class="text-white-50 small mb-0" id="previewLightboxDesc"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================================================
            共用 Lightbox 3：聲音檔音頻播放燈箱
            ========================================================================== -->
        <div class="modal fade" id="audioLightboxModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark text-white border border-warning shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header border-secondary">
                        <span class="badge bg-info text-dark fw-bold me-2"><i class="bi bi-soundwave me-1"></i>AUDIO STORY</span>
                        <span class="small text-warning fw-bold" id="audioLightboxDate"></span>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <img src="" id="audioLightboxCover" class="rounded-3 shadow mb-3 border border-secondary" style="width: 160px; height: 160px; object-fit: cover;" alt="音頻封面">
                        <h5 class="fw-bold text-white mb-1" id="audioLightboxTitle"></h5>
                        <p class="text-white-50 small mb-4" id="audioLightboxDesc"></p>
                        <audio controls id="audioLightboxPlayer" class="w-100">
                            <source src="" type="audio/mpeg">
                            您的瀏覽器不支援音訊播放元件。
                        </audio>
                    </div>
                </div>
            </div>
        </div>
<style>
/* ==========================================================================
   更多花絮總覽頁面 (5sl_highlights.php) 專屬樣式
   ========================================================================== */
.fsl-grid-highlight-card {
    width: 100%;
    height: 340px;
    position: relative;
    cursor: pointer;
}

.fsl-grid-highlight-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.fsl-grid-highlight-card .portfolio-overlay {
    pointer-events: none;
    z-index: 2;
}
</style>

<?php include_once '5sl_include_footer.php'; ?>

<script>

$(function() {
    // ==========================================================================
    // 更多花絮頁面 (5sl_highlights.php) 專屬互動控制：圖檔 / YouTube視訊 / 聲音檔 Lightbox
    // ==========================================================================
    const $highlightsGrid = $("#fslHighlightsGrid");

    if ($highlightsGrid.length > 0) {
        // 1. 點擊花絮卡片：依據 med_type 自動分流開啟三種 Lightbox
        $highlightsGrid.on("click", ".fsl-grid-highlight-card", function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $card    = $(this);
            const mType    = parseInt($card.attr("data-media-type"), 10) || 1;
            const mainImg  = $card.attr("data-main-img") || $card.find("img").attr("src") || "";
            const mediaUrl = ($card.attr("data-media-url") || "").trim();
            const title    = $card.attr("data-title") || $card.find(".portfolio-overlay h6").text() || "";
            const desc     = $card.attr("data-desc") || $card.find(".portfolio-overlay p").text() || "";
            const dateStr  = $card.attr("data-date") || "";

            if (mType === 2 && mediaUrl !== "") {
                // YouTube 視訊 Lightbox
                let ytId = "";
                if (mediaUrl.indexOf("v=") !== -1) {
                    ytId = mediaUrl.split("v=").pop().split("&").shift();
                } else if (mediaUrl.indexOf("youtu.be/") !== -1) {
                    ytId = mediaUrl.split("youtu.be/").pop().split("?").shift();
                } else if (mediaUrl.indexOf("/embed/") !== -1) {
                    ytId = mediaUrl.split("/embed/").pop().split("?").shift();
                }

                if (ytId !== "") {
                    const embedSrc = "https://" + "youtube.com" + "/em" + "bed/" + ytId + "?autoplay=1&rel=0&playsinline=1";
                    $("#hlVideoLightboxTitle").text((dateStr ? "[" + dateStr + "] " : "") + title);
                    $("#hlLightboxIframe").attr("src", embedSrc);
                    const vModalEl = document.getElementById("hlVideoLightboxModal") || document.getElementById("videoLightboxModal");
                    if (vModalEl) bootstrap.Modal.getOrCreateInstance(vModalEl).show();
                }
            } else if (mType === 3 && mediaUrl !== "") {
                // 聲音檔 Lightbox
                $("#audioLightboxCover").attr("src", mainImg);
                $("#audioLightboxDate").text(dateStr);
                $("#audioLightboxTitle").text(title);
                $("#audioLightboxDesc").text(desc);
                const player = document.getElementById("audioLightboxPlayer");
                if (player) {
                    player.src = mediaUrl;
                    bootstrap.Modal.getOrCreateInstance(document.getElementById("audioLightboxModal")).show();
                    player.play().catch(function() {});
                }
            } else if (mainImg !== "") {
                // 純圖片放大預覽 Lightbox
                $("#previewLightboxImg").attr("src", mainImg);
                $("#previewLightboxTitle").text(title);
                $("#previewLightboxDate").text(dateStr);
                $("#previewLightboxDesc").text(desc);
                const pModalEl = document.getElementById("imagePreviewModal");
                if (pModalEl) bootstrap.Modal.getOrCreateInstance(pModalEl).show();
            }
        });

        // 2. 分批載入更多花絮按鈕（每次展開 8 筆）
        $("#fslLoadMoreHighlightsBtn").on("click", function() {
            const $hiddenCards = $highlightsGrid.find(".fsl-extra-card.d-none");
            $hiddenCards.slice(0, 8).removeClass("d-none").hide().fadeIn(350);
            if ($highlightsGrid.find(".fsl-extra-card.d-none").length === 0) {
                $(this).fadeOut(250);
            }
        });

        // 3. 分類篩選按鈕列（支援 workshop / salon / retreat / video / audio）
        $("#fslGalleryFilterBar").on("click", ".fsl-faq-filter-btn", function() {
            const $btn = $(this);
            const filter = $btn.attr("data-gallery-filter");

            $("#fslGalleryFilterBar .fsl-faq-filter-btn").removeClass("active");
            $btn.addClass("active");

            const $allCols = $highlightsGrid.find(".fsl-grid-card-col");

            if (filter === "all") {
                $allCols.each(function() {
                    const $col = $(this);
                    if ($col.hasClass("fsl-extra-card") && $col.hasClass("d-none")) {
                        $col.hide();
                    } else {
                        $col.stop(true, true).fadeIn(250);
                    }
                });
                if ($highlightsGrid.find(".fsl-extra-card.d-none").length > 0) {
                    $("#fslLoadMoreHighlightsBtn").fadeIn(250);
                }
            } else {
                $("#fslLoadMoreHighlightsBtn").hide();
                $allCols.each(function() {
                    const $col = $(this);
                    let isMatch = false;
                    if (filter === "video") isMatch = $col.attr("data-has-video") === "1";
                    else if (filter === "audio") isMatch = $col.attr("data-has-audio") === "1";
                    else isMatch = $col.attr("data-cat") === filter;

                    if (isMatch) {
                        $col.removeClass("d-none").stop(true, true).fadeIn(250);
                    } else {
                        $col.stop(true, true).hide();
                    }
                });
            }
        });
    }

    // 4. 關閉視訊或音檔 Lightbox 時立即停止播放
    $("#hlVideoLightboxModal").on("hidden.bs.modal", function() {
        $("#hlLightboxIframe").attr("src", "");
    });
    $("#audioLightboxModal").on("hidden.bs.modal", function() {
        const player = document.getElementById("audioLightboxPlayer");
        if (player) {
            player.pause();
            player.currentTime = 0;
            player.src = "";
        }
    });
});
</script>