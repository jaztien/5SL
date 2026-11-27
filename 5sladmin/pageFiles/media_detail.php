<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 定義本頁面代碼（系統設定管理 ITEM_SYS -> 系統檔案庫管理 PG_MEDIA）
define('PAGE_MARK', 'PG_MEDIA');
define('PAGE_ITEM', 'ITEM_SYS');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$medID = currID;
$res   = ($medID > 0) ? DB->row("SELECT * FROM fsl_media WHERE med_id = ? AND med_status != 9", array($medID)) : array();
if (!is_array($res)) $res = array();

// 撈取上架禮品與商品清單供關聯選擇
$giftOptions  = DB->query("SELECT gift_id, gift_title FROM fsl_gifts WHERE gift_status != 9 ORDER BY gift_id DESC");
$merchOptions = DB->query("SELECT merch_id, merch_sku, merch_title FROM fsl_merchandise WHERE merch_status != 9 ORDER BY merch_id DESC");
if (!is_array($giftOptions))  $giftOptions  = array();
if (!is_array($merchOptions)) $merchOptions = array();

// 輔助函式：擷取 YouTube Video ID 並轉為標準 embed 網址
function _getYoutubeEmbedUrl($url)
{
  if (empty($url)) return '';
  if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $m)) {
    return 'https://www.youtube.com/embed/' . $m[1];
  }
  return '';
}

$mType    = (int)($res['med_type'] ?? 1);
$mSrc     = (int)($res['med_source'] ?? 1);
$rawUrl   = $res['med_url'] ?? '';
$ytEmbed  = ($mType === 2) ? _getYoutubeEmbedUrl($rawUrl) : '';
$fullUrl  = ($rawUrl !== '' && strpos($rawUrl, 'http') !== 0) ? ('../../' . ltrim($rawUrl, '/')) : $rawUrl;
$safeDlUrl = ($medID > 0) ? ("5sl_download.php?id=" . $medID) : "";
?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">

<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
  <title>Fifth Season Life 人生第五季 | 檔案庫與活動花絮詳情</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/flatpickr/flatpickr.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>

<body class="bg-body-tertiary theme-indigo">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1 ms-2"><i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?></div>
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                <?php echo !empty($medID) ? "編輯" : "新增"; ?>多媒體與檔案庫資料
                <span class="fs-7">【ID: <?php echo $medID; ?>】</span>
              </h4>
              <h6 class="m-0 fw-light text-muted fs-7 mt-1 lh-xs mb-n2"><i class="bi bi-arrow-return-right"></i> 支援本地上傳與雲端連結、YouTube Embed 預覽、下載權限與隱藏檔名下載</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">系統設定管理</li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回列表">系統檔案庫管理</li>
                <li class="breadcrumb-item active"><?php echo !empty($medID) ? "檔案詳情編輯" : "新增檔案"; ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <!-- 左側即時多媒體預覽與安全下載資訊卡 -->
            <div class="col-md-4 mb-3">
              <div class="card card-primary card-outline shadow-lg">
                <div class="card-header fw-bold small">
                  <i class="bi bi-eye-fill me-1 text-primary"></i>即時預覽與前台安全調用
                </div>
                <div class="card-body">
                  <!-- 動態預覽容器 https://www.youtube.com/embed/-->
                  <div id="mediaPreviewBox" class="rounded-3 border bg-light p-2 text-center mb-3" style="min-height: 180px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                    <?php if ($mType === 1 && $fullUrl !== ''): ?>
                      <img src="<?php echo htmlspecialchars($fullUrl); ?>" class="img-fluid rounded" style="max-height: 240px;" alt="Preview">
                    <?php elseif ($mType === 2 && $ytEmbed !== ''): ?>
                      <div class="ratio ratio-16x9 w-100">
                        <iframe src="<?php echo htmlspecialchars($ytEmbed); ?>" title="YouTube video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                      </div>
                    <?php elseif ($mType === 3 && $fullUrl !== ''): ?>
                      <div class="w-100 px-2">
                        <i class="bi bi-music-note-beamed fs-1 text-info d-block mb-2"></i>
                        <audio controls class="w-100">
                          <source src="<?php echo htmlspecialchars($fullUrl); ?>">
                        </audio>
                      </div>
                    <?php elseif ($mType === 4 && $fullUrl !== ''): ?>
                      <div>
                        <i class="bi bi-file-earmark-check-fill fs-1 text-warning d-block mb-1"></i>
                        <span class="badge bg-dark"><?php echo strtoupper(htmlspecialchars($res['med_ext'] ?? 'DOC')); ?> 文件檔案</span>
                      </div>
                    <?php else: ?>
                      <div class="text-muted small">
                        <i class="bi bi-cloud-upload fs-2 d-block mb-1"></i>
                        尚未設定檔案或連結
                      </div>
                    <?php endif; ?>
                  </div>

                  <?php if ($medID > 0): ?>
                    <div class="mb-3">
                      <label class="form-label small fw-bold text-primary">隱藏真實檔名安全下載網址（前端用）</label>
                      <div class="input-group input-group-sm">
                        <input type="text" class="form-control bg-light" id="safeDownloadInput" value="<?php echo htmlspecialchars($safeDlUrl); ?>" readonly>
                        <button type="button" class="btn btn-outline-primary" id="btnCopySafeDl" title="複製安全下載網址">
                          <i class="bi bi-clipboard"></i>
                        </button>
                      </div>
                    </div>
                  <?php endif; ?>

                  <ul class="list-group list-group-flush small">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>檔案格式 / 大小</b>
                      <span>
                        <?php echo strtoupper(htmlspecialchars($res['med_ext'] ?? '-')); ?>
                        <?php if (!empty($res['med_filesize'])): ?>
                          (<?php echo round((int)$res['med_filesize'] / 1024, 1); ?> KB)
                        <?php endif; ?>
                      </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>累計安全下載</b>
                      <span class="fw-bold text-success"><?php echo number_format((int)($res['med_dl_count'] ?? 0)); ?> 次</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>SunEditor 圖庫</b>
                      <span><?php echo ((int)($res['med_in_gallery'] ?? 1) === 1 && $mType === 1) ? '<span class="text-success fw-bold">已開放插入</span>' : '<span class="text-muted">未開放</span>'; ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>最後更新</b>
                      <span class="text-muted"><?php echo $res['med_updated'] ?? '-'; ?></span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <!-- 右側檔案建檔、下載權限與業務關聯設定區 -->
            <div class="col-md-8">
              <div class="card shadow-lg">
                <div class="card-header bg-white fw-bold small">
                  <i class="bi bi-sliders me-1 text-primary"></i>多媒體基本資訊、檔案來源與權限關聯配置
                </div>
                <div class="card-body">
                  <form id="form_mediaMaster" action="../funcFiles/media_act_func.php" method="post" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="medID" value="<?php echo $medID; ?>">
                    <input type="hidden" name="formAcce" value="<?php echo ($medID > 0) ? 'func_media_update' : 'func_media_addnew'; ?>">

                    <div class="row g-3">
                      <div class="col-md-7">
                        <label class="form-label small fw-bold">媒體名稱 / 標題 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="med_title" name="med_title" required
                          value="<?php echo htmlspecialchars($res['med_title'] ?? ''); ?>" placeholder="例如：2026 冬季向光工作坊現場紀實 / 冥想引導音檔">
                      </div>
                      <div class="col-md-5">
                        <label class="form-label small fw-bold">活動 / 素材分類</label>
                        <select class="form-select" name="med_category">
                          <option value="workshop" <?php echo (($res['med_category'] ?? 'workshop') === 'workshop') ? 'selected' : ''; ?>>workshop - 課程與工作坊</option>
                          <option value="salon" <?php echo (($res['med_category'] ?? '') === 'salon') ? 'selected' : ''; ?>>salon - 生命藝術沙龍</option>
                          <option value="retreat" <?php echo (($res['med_category'] ?? '') === 'retreat') ? 'selected' : ''; ?>>retreat - 自然能量營</option>
                          <option value="audio" <?php echo (($res['med_category'] ?? '') === 'audio') ? 'selected' : ''; ?>>audio - 音頻紀實</option>
                          <option value="resource" <?php echo (($res['med_category'] ?? '') === 'resource') ? 'selected' : ''; ?>>resource - 教材與限定素材</option>
                        </select>
                      </div>

                      <div class="col-md-4">
                        <label class="form-label small fw-bold">媒體類型 (med_type)</label>
                        <select class="form-select" id="med_type" name="med_type">
                          <option value="1" <?php echo ($mType === 1) ? 'selected' : ''; ?>>1 - 圖檔 (Photo: JPG/PNG/WEBP)</option>
                          <option value="2" <?php echo ($mType === 2) ? 'selected' : ''; ?>>2 - YouTube 視訊 (Video Embed)</option>
                          <option value="3" <?php echo ($mType === 3) ? 'selected' : ''; ?>>3 - 聲音檔 (Audio: MP3/M4A)</option>
                          <option value="4" <?php echo ($mType === 4) ? 'selected' : ''; ?>>4 - 文件檔 (Document: PDF/ZIP)</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label small fw-bold">檔案來源模式 (med_source)</label>
                        <select class="form-select" id="med_source" name="med_source">
                          <option value="1" <?php echo ($mSrc === 1) ? 'selected' : ''; ?>>1 - 本地伺服器上傳 (uploadFiles/md/)</option>
                          <option value="2" <?php echo ($mSrc === 2) ? 'selected' : ''; ?>>2 - 雲端外部連結 (YouTube / 雲端硬碟)</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label small fw-bold">活動 / 事件發生日期 <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="med_event_date" required
                          value="<?php echo htmlspecialchars($res['med_event_date'] ?? date('Y-m-d')); ?>">
                      </div>

                      <!-- 本地檔案上傳欄位 (med_source = 1 時顯示) -->
                      <div class="col-12" id="boxLocalUpload" style="<?php echo ($mSrc === 2) ? 'display:none;' : ''; ?>">
                        <label class="form-label small fw-bold text-primary">
                          選擇本地檔案上傳（支援圖片、MP3音訊、PDF/ZIP文件）
                        </label>
                        <input type="file" class="form-control" id="med_upload_file" name="med_upload_file">
                        <?php if ($mSrc === 1 && $rawUrl !== ''): ?>
                          <div class="form-text text-muted">目前實體路徑：<code><?php echo htmlspecialchars($rawUrl); ?></code>（不更換請留空）</div>
                        <?php endif; ?>
                      </div>

                      <!-- 雲端連結 / YouTube 網址輸入欄位 -->
                      <div class="col-md-9" id="boxCloudUrl">
                        <label class="form-label small fw-bold">檔案路徑或雲端 / YouTube 連結 (med_url)</label>
                        <input type="text" class="form-control" id="med_url" name="med_url"
                          value="<?php echo htmlspecialchars($rawUrl); ?>" placeholder="貼上 YouTube 網址（自動轉 Embed 預覽）或雲端檔案連結">
                      </div>
                      <div class="col-md-3">
                        <label class="form-label small fw-bold">影音長度標示</label>
                        <input type="text" class="form-control" name="med_duration"
                          value="<?php echo htmlspecialchars($res['med_duration'] ?? ''); ?>" placeholder="如：08:45">
                      </div>

                      <div class="col-12">
                        <div class="hr-more">下載權限控管與業務關聯設定</div>
                        <hr class="my-1">
                      </div>

                      <div class="col-md-4">
                        <label class="form-label small fw-bold">下載權限註記 (med_allow_download)</label>
                        <select class="form-select" name="med_allow_download">
                          <option value="0" <?php echo ((int)($res['med_allow_download'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 禁止下載 (僅供前台預覽/內嵌)</option>
                          <option value="1" <?php echo ((int)($res['med_allow_download'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 公開免費下載 (不限身分)</option>
                          <option value="2" <?php echo ((int)($res['med_allow_download'] ?? 0) === 2) ? 'selected' : ''; ?>>2 - 僅限登入會員安全下載</option>
                          <option value="3" <?php echo ((int)($res['med_allow_download'] ?? 0) === 3) ? 'selected' : ''; ?>>3 - 僅限已購關聯課程/商品會員</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label small fw-bold">關聯模組代碼 (med_for)</label>
                        <select class="form-select" name="med_for">
                          <option value="general" <?php echo (($res['med_for'] ?? 'general') === 'general') ? 'selected' : ''; ?>>general - 全站一般花絮 / 素材</option>
                          <option value="course" <?php echo (($res['med_for'] ?? '') === 'course') ? 'selected' : ''; ?>>course - 關聯特定課程 (fsl_courses)</option>
                          <option value="session" <?php echo (($res['med_for'] ?? '') === 'session') ? 'selected' : ''; ?>>session - 關聯開課梯次 (fsl_course_sessions)</option>
                          <option value="article" <?php echo (($res['med_for'] ?? '') === 'article') ? 'selected' : ''; ?>>article - 關聯專欄文章</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label small fw-bold">關聯模組主鍵 ID (med_forid)</label>
                        <input type="number" class="form-control" name="med_forid" min="0"
                          value="<?php echo (int)($res['med_forid'] ?? 0); ?>" placeholder="0 代表不指定特定ID">
                      </div>

                      <div class="col-md-6">
                        <label class="form-label small fw-bold">關聯禮品項目 (gift_id，選填)</label>
                        <select class="form-select" name="gift_id">
                          <option value="">-- 不關聯特定禮品 --</option>
                          <?php foreach ($giftOptions as $go): ?>
                            <option value="<?php echo (int)$go['gift_id']; ?>" <?php echo ((int)($res['gift_id'] ?? 0) === (int)$go['gift_id']) ? 'selected' : ''; ?>>
                              #<?php echo (int)$go['gift_id']; ?> - <?php echo htmlspecialchars($go['gift_title']); ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label small fw-bold">關聯商品項目 (merch_id，選填)</label>
                        <select class="form-select" name="merch_id">
                          <option value="">-- 不關聯特定商品 --</option>
                          <?php foreach ($merchOptions as $mo): ?>
                            <option value="<?php echo (int)$mo['merch_id']; ?>" <?php echo ((int)($res['merch_id'] ?? 0) === (int)$mo['merch_id']) ? 'selected' : ''; ?>>
                              #<?php echo (int)$mo['merch_id']; ?> [<?php echo htmlspecialchars($mo['merch_sku'] ?? ''); ?>] <?php echo htmlspecialchars($mo['merch_title']); ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <div class="col-12">
                        <div class="hr-more">展示曝光、SunEditor 圖庫與紀實說明</div>
                        <hr class="my-1">
                      </div>

                      <div class="col-md-3">
                        <label class="form-label small fw-bold">首頁精選花絮展示</label>
                        <select class="form-select" name="med_is_featured">
                          <option value="0" <?php echo ((int)($res['med_is_featured'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 僅花絮總覽</option>
                          <option value="1" <?php echo ((int)($res['med_is_featured'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 同步精選至首頁</option>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label small fw-bold">SunEditor 圖庫開放</label>
                        <select class="form-select" name="med_in_gallery">
                          <option value="1" <?php echo ((int)($res['med_in_gallery'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 開放編輯器挑選</option>
                          <option value="0" <?php echo ((int)($res['med_in_gallery'] ?? 1) === 0) ? 'selected' : ''; ?>>0 - 隱藏不顯示於圖庫</option>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label small fw-bold">排序權重 (小靠前)</label>
                        <input type="number" class="form-control" name="med_sort" min="0" value="<?php echo (int)($res['med_sort'] ?? 100); ?>">
                      </div>
                      <div class="col-md-3">
                        <label class="form-label small fw-bold">上下架狀態</label>
                        <select class="form-select" name="med_status">
                          <option value="1" <?php echo ((int)($res['med_status'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 公開上架</option>
                          <option value="0" <?php echo ((int)($res['med_status'] ?? 1) === 0) ? 'selected' : ''; ?>>0 - 下架隱藏</option>
                        </select>
                      </div>

                      <div class="col-12">
                        <label class="form-label small fw-bold">媒體簡述說明（含活動地點、場次紀實或素材使用指引）</label>
                        <textarea class="form-control" name="med_brief" rows="3" maxlength="500"><?php echo htmlspecialchars($res['med_brief'] ?? ''); ?></textarea>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <script src="../pluginFiles/flatpickr/flatpickr.min.js"></script>
  <script src="../pluginFiles/flatpickr/location/zh_tw.js"></script>
  <script src="../scriptFiles/adminlte.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t=' . time(); ?>"></script>

  <script type="text/javascript">
    function extractYoutubeId(url) {
      if (!url) return '';
      const reg = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i;
      const m = url.match(reg);
      return m ? m[1] : '';
    }


    function refreshMediaPreview() {
      const type = parseInt($('#med_type').val(), 10);
      const url = $('#med_url').val().trim();
      const $box = $('#mediaPreviewBox');

      if (type === 2) {
        const ytId = extractYoutubeId(url);
        if (ytId) {
          $box.html(`<div class="ratio ratio-16x9 w-100"><iframe src="https://youtube.com{ytId}" allowfullscreen></iframe></div>`);
        } else {
          $box.html(`<div class="text-danger small"><i class="bi bi-youtube fs-2 d-block mb-1"></i>請輸入有效的 YouTube 影片網址以產生 Embed 預覽</div>`);
        }
      } else if (type === 1 && url) {
        const src = url.startsWith('http') ? url : ('../../' + url.replace(/^\/+/, ''));
        $box.html(`<img src="${src}" class="img-fluid rounded" style="max-height: 240px;">`);
      } else if (type === 3 && url) {
        const src = url.startsWith('http') ? url : ('../../' + url.replace(/^\/+/, ''));
        $box.html(`<div class="w-100 px-2"><i class="bi bi-music-note-beamed fs-1 text-info d-block mb-2"></i><audio controls class="w-100"><source src="${src}"></audio></div>`);
      }
    }

    function doReDirect() {
      window.history.back();
    }

    function doButSave() {
      const frm = document.getElementById('form_mediaMaster');
      if (!frm.checkValidity()) {
        frm.classList.add('was-validated');
        window.parent.toastr['warning']('請填寫必填欄位（媒體標題與活動日期）！');
        return false;
      }

      const fd = new FormData(frm);
      fd.append('token', getTokenVal());

      $.ajax({
        type: 'post',
        url: '../funcFiles/media_act_func.php',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(data) {
          window.parent.toastr[data['msgType']](data['msgStr']);
          if (data['RTN'] == 1) {
            if (data['newID']) {
              window.location.replace('media_detail.php?currID=' + data['newID']);
            } else {
              location.reload(true);
            }
          }
        },
        error: function() {
          window.parent.toastr['error']('檔案上傳或寫入失敗，請檢查檔案大小限制！');
        }
      });
    }

    function doButDelete() {
      if (0 == parseInt('<?php echo $medID; ?>')) return false;
      $.post('../funcFiles/media_act_func.php', {
        'formAcce': 'func_media_delete',
        'token': getTokenVal(),
        'medID': '<?php echo $medID; ?>'
      }, function(data) {
        if (data['RTN'] == 1) {
          window.parent.toastr['success'](data['msgStr']);
          window.location.replace('media_list.php');
        } else {
          window.parent.toastr['error'](data['msgStr'] || '刪除失敗');
        }
      }, 'json');
    }

    function doButReset() {
      location.reload(true);
    }

    $(document).ready(function() {
      if (typeof flatpickr !== 'undefined') {
        flatpickr($('input[type="date"]'), {
          locale: 'zh_tw',
          dateFormat: 'Y-m-d',
          allowInput: true,
          disableMobile: true
        });
      }

      $('#med_source').on('change', function() {
        if ($(this).val() === '1') {
          $('#boxLocalUpload').slideDown(180);
        } else {
          $('#boxLocalUpload').slideUp(180);
        }
      });

      $('#med_type').on('change', function() {
        if ($(this).val() === '2') {
          $('#med_source').val('2').trigger('change');
        }
        refreshMediaPreview();
      });

      $('#med_url').on('input change blur', refreshMediaPreview);

      $('#btnCopySafeDl').on('click', function() {
        const inp = document.getElementById('safeDownloadInput');
        if (!inp) return;
        inp.select();
        navigator.clipboard.writeText(inp.value);
        window.parent.toastr['success']('已複製隱藏檔名安全下載網址：' + inp.value);
      });

      $('li.prevLink').on('click', function() {
        window.location.replace(getCurrentURI('', 'media_list.php'));
      });
      $('body').on('keypress click', function() {
        let timerElm = window.parent.$('#timer_on_exhausted');
        if (timerElm.length) timerElm.backward_timer('reset');
      });

      //-------- 加載完成後更改外框主頁中樹狀目錄開合狀態 --------//
      if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
      }
    });
  </script>
</body>

</html>