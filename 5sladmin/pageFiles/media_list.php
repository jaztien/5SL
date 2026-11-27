<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 架構搜尋與資料夾篩選條件
$searchText = isset($_REQUEST['searchText']) ? trim(rawurldecode($_REQUEST['searchText'])) : '';
$searchType = isset($_REQUEST['searchType']) ? trim($_REQUEST['searchType']) : '';
$searchCate = isset($_REQUEST['searchCate']) ? trim($_REQUEST['searchCate']) : '';
$searchGall = isset($_REQUEST['searchGall']) ? trim($_REQUEST['searchGall']) : '';

$searchURI = "";
if ($searchText !== '') {
  $searchURI .= "&searchText=" . rawurlencode($searchText);
}
if ($searchType !== '') {
  $searchURI .= "&searchType=" . rawurlencode($searchType);
}
if ($searchCate !== '') {
  $searchURI .= "&searchCate=" . rawurlencode($searchCate);
}
if ($searchGall !== '') {
  $searchURI .= "&searchGall=" . rawurlencode($searchGall);
}
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}

$maxResult = (!empty($searchURI)) ? 300 : 160;

// 定義本頁面代碼（系統設定管理 ITEM_SYS -> 系統檔案庫管理 PG_MEDIA）
define('PAGE_MARK', 'PG_MEDIA');
define('PAGE_ITEM', 'ITEM_SYS');
define('PAGE_OPANEL', 1); // 1-新增
define('DRAWER_OPANEL', !empty($searchURI) ? 2 : 1);
define('EXPORT_OPANEL', 0);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

// 統計左側資料夾與各類型數量、本地儲存空間用量
$statAll     = (int)DB->single("SELECT COUNT(*) FROM fsl_media WHERE med_status != 9");
$statGallery = (int)DB->single("SELECT COUNT(*) FROM fsl_media WHERE med_status != 9 AND med_type = 1 AND med_in_gallery = 1");
$totalBytes  = (int)DB->single("SELECT COALESCE(SUM(med_filesize), 0) FROM fsl_media WHERE med_status != 9 AND med_source = 1");

$cateStatsRaw = DB->query("SELECT med_category, COUNT(*) AS cnt FROM fsl_media WHERE med_status != 9 GROUP BY med_category");
$cateCounts   = array();
if (is_array($cateStatsRaw)) {
  foreach ($cateStatsRaw as $cs) {
    $cateCounts[$cs['med_category']] = (int)$cs['cnt'];
  }
}

$typeStatsRaw = DB->query("SELECT med_type, COUNT(*) AS cnt FROM fsl_media WHERE med_status != 9 GROUP BY med_type");
$typeCounts   = array();
if (is_array($typeStatsRaw)) {
  foreach ($typeStatsRaw as $ts) {
    $typeCounts[(int)$ts['med_type']] = (int)$ts['cnt'];
  }
}

// 組合主列表查詢條件
$whereSql = " WHERE m.med_status != 9 ";
$params   = array();

if ($searchText !== '') {
  $whereSql .= " AND (m.med_title LIKE ? OR m.med_brief LIKE ? OR m.med_for LIKE ?) ";
  $likeStr = '%' . $searchText . '%';
  array_push($params, $likeStr, $likeStr, $likeStr);
}
if ($searchType !== '') {
  $whereSql .= " AND m.med_type = ? ";
  $params[] = (int)$searchType;
}
if ($searchCate !== '') {
  $whereSql .= " AND m.med_category = ? ";
  $params[] = $searchCate;
}
if ($searchGall === '1') {
  $whereSql .= " AND m.med_type = 1 AND m.med_in_gallery = 1 ";
}

$mediaList = DB->query("
  SELECT m.*, g.gift_title, mc.merch_title
  FROM fsl_media m
  LEFT JOIN fsl_gifts g ON m.gift_id = g.gift_id
  LEFT JOIN fsl_merchandise mc ON m.merch_id = mc.merch_id
  {$whereSql}
  ORDER BY m.med_event_date DESC, m.med_sort ASC, m.med_id DESC
  LIMIT {$maxResult}
", $params);
if (!is_array($mediaList)) $mediaList = array();

$cateDefs = array(
  'workshop' => array('name' => '課程與工作坊', 'icon' => 'bi-folder-fill text-warning'),
  'salon'    => array('name' => '生命藝術沙龍', 'icon' => 'bi-folder-fill text-info'),
  'retreat'  => array('name' => '自然能量營',   'icon' => 'bi-folder-fill text-success'),
  'audio'    => array('name' => '音頻紀實',     'icon' => 'bi-folder-fill text-primary'),
  'resource' => array('name' => '教材與限定素材', 'icon' => 'bi-folder-fill text-danger')
);

// 計算容量顯示 (預設配額 5 GB)
$quotaBytes = 5 * 1024 * 1024 * 1024;
$usedPct    = min(100, round(($totalBytes / $quotaBytes) * 100, 1));
$usedLabel  = ($totalBytes >= 1048576) ? (round($totalBytes / 1048576, 1) . ' MB') : (round($totalBytes / 1024, 1) . ' KB');
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
  <title>Fifth Season Life 人生第五季 | 系統檔案庫管理 (File Manager)</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">

  <style>
    /* AdminLTE 4 File Manager 專屬視覺卡片樣式 */
    .fm-sidebar-nav .nav-link {
      color: #495057;
      border-radius: 8px;
      padding: 0.48rem 0.75rem;
      font-size: 0.875rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: all 0.15s ease;
    }

    .fm-sidebar-nav .nav-link:hover {
      background-color: #f1f3f5;
      color: #212529;
    }

    .fm-sidebar-nav .nav-link.active {
      background-color: #eef2ff;
      color: #4f46e5;
      font-weight: 700;
    }

    .fm-file-card {
      border: 1px solid #e9ecef;
      border-radius: 12px;
      background: #ffffff;
      overflow: hidden;
      transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
      cursor: pointer;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .fm-file-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      border-color: #6366f1;
    }

    .fm-thumb-box {
      position: relative;
      width: 100%;
      aspect-ratio: 16 / 10;
      background-color: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      border-bottom: 1px solid #f1f3f5;
    }

    .fm-thumb-box img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.25s ease;
    }

    .fm-file-card:hover .fm-thumb-box img {
      transform: scale(1.04);
    }

    .fm-type-pill {
      position: absolute;
      top: 8px;
      left: 8px;
      z-index: 3;
      font-size: 0.68rem;
      padding: 2px 7px;
      border-radius: 6px;
      backdrop-filter: blur(4px);
    }

    .fm-dl-pill {
      position: absolute;
      top: 8px;
      right: 8px;
      z-index: 3;
      font-size: 0.68rem;
    }

    .fm-yt-play {
      position: absolute;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(220, 38, 38, 0.9);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }

    .fm-card-body {
      padding: 0.65rem 0.75rem;
      flex: 1 1 auto;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .fm-title {
      font-size: 0.85rem;
      font-weight: 700;
      color: #212529;
      display: -webkit-box;
      -webkit-line-clamp: 1;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  </style>
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
                系統檔案庫與活動花絮總管
              </h4>
              <h6 class="m-0 fw-light text-muted fs-7 mt-1 lh-xs mb-n2"><i class="bi bi-arrow-return-right"></i> File Manager</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">系統設定管理</li>
                <li class="breadcrumb-item active">系統檔案庫管理</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域：AdminLTE 4 File Manager 雙欄佈局 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row g-3">
            <!-- 左側：資料夾與類型分類欄 + 儲存空間指標 -->
            <div class="col-md-3 col-lg-2">
              <div class="card shadow-sm h-100">
                <div class="card-body p-2 d-flex flex-column">
                  <a href="media_detail.php?currID=0" class="btn btn-primary btn-sm fw-bold w-100 mb-3">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i>上傳 / 新增檔案
                  </a>

                  <div class="small fw-bold text-muted px-2 mb-1">活動與素材資料夾</div>
                  <nav class="nav flex-column fm-sidebar-nav mb-3">
                    <a class="nav-link <?php echo ($searchCate === '' && $searchType === '' && $searchGall === '') ? 'active' : ''; ?>" href="media_list.php">
                      <span><i class="bi bi-hdd-stack-fill me-2 text-primary"></i>全部檔案庫</span>
                      <span class="badge bg-light text-dark border"><?php echo $statAll; ?></span>
                    </a>
                    <?php foreach ($cateDefs as $cKey => $cInfo): ?>
                      <a class="nav-link <?php echo ($searchCate === $cKey) ? 'active' : ''; ?>" href="media_list.php?searchCate=<?php echo $cKey; ?>">
                        <span><i class="bi <?php echo $cInfo['icon']; ?> me-2"></i><?php echo $cInfo['name']; ?></span>
                        <span class="badge bg-light text-dark border"><?php echo $cateCounts[$cKey] ?? 0; ?></span>
                      </a>
                    <?php endforeach; ?>
                  </nav>

                  <div class="small fw-bold text-muted px-2 mb-1">媒體檔案類型</div>
                  <nav class="nav flex-column fm-sidebar-nav mb-3">
                    <a class="nav-link <?php echo ($searchType === '1' && $searchGall === '') ? 'active' : ''; ?>" href="media_list.php?searchType=1">
                      <span><i class="bi bi-image me-2 text-primary"></i>圖檔 Photo</span>
                      <span class="badge bg-light text-dark border"><?php echo $typeCounts[1] ?? 0; ?></span>
                    </a>
                    <a class="nav-link <?php echo ($searchType === '2') ? 'active' : ''; ?>" href="media_list.php?searchType=2">
                      <span><i class="bi bi-youtube me-2 text-danger"></i>YouTube 視訊</span>
                      <span class="badge bg-light text-dark border"><?php echo $typeCounts[2] ?? 0; ?></span>
                    </a>
                    <a class="nav-link <?php echo ($searchType === '3') ? 'active' : ''; ?>" href="media_list.php?searchType=3">
                      <span><i class="bi bi-music-note-beamed me-2 text-info"></i>聲音檔 Audio</span>
                      <span class="badge bg-light text-dark border"><?php echo $typeCounts[3] ?? 0; ?></span>
                    </a>
                    <a class="nav-link <?php echo ($searchType === '4') ? 'active' : ''; ?>" href="media_list.php?searchType=4">
                      <span><i class="bi bi-file-earmark-pdf me-2 text-warning"></i>文件檔 Doc</span>
                      <span class="badge bg-light text-dark border"><?php echo $typeCounts[4] ?? 0; ?></span>
                    </a>
                    <a class="nav-link <?php echo ($searchGall === '1') ? 'active' : ''; ?>" href="media_list.php?searchGall=1">
                      <span><i class="bi bi-stars me-2 text-indigo"></i>SunEditor 圖庫</span>
                      <span class="badge bg-light text-dark border"><?php echo $statGallery; ?></span>
                    </a>
                  </nav>

                  <div class="mt-auto p-2 bg-light rounded-3 border">
                    <div class="d-flex justify-content-between small mb-1">
                      <span class="fw-bold text-dark"><i class="bi bi-server me-1"></i>本地空間</span>
                      <span class="text-muted"><?php echo $usedPct; ?>%</span>
                    </div>
                    <div class="progress mb-1" style="height: 6px;">
                      <div class="progress-bar bg-primary" style="width: <?php echo $usedPct; ?>%;"></div>
                    </div>
                    <div class="text-muted" style="font-size: 0.72rem;">已使用 <?php echo $usedLabel; ?> / 5.0 GB</div>
                  </div>
                </div>
              </div>
            </div>
            <!-- 右側：檔案大圖網格 (Grid View) 與清單 (List View) 主區 -->
            <div class="col-md-9 col-lg-10">
              <div class="card shadow-sm h-100">
                <!-- 頂部工具列：目前資料夾路徑、即時搜尋框與視圖切換按鈕 -->
                <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 py-2">
                  <div class="d-flex align-items-center gap-2 small">
                    <span class="fw-bold text-primary">
                      <i class="bi bi-folder2-open me-1"></i>
                      <?php
                      if ($searchGall === '1') echo 'SunEditor 編輯器圖庫';
                      elseif ($searchCate !== '' && isset($cateDefs[$searchCate])) echo $cateDefs[$searchCate]['name'];
                      elseif ($searchType === '1') echo '圖檔 Photo 專區';
                      elseif ($searchType === '2') echo 'YouTube 視訊專區';
                      elseif ($searchType === '3') echo '聲音檔 Audio 專區';
                      elseif ($searchType === '4') echo '文件檔 Document 專區';
                      else echo '全站多媒體與活動花絮總覽';
                      ?>
                    </span>
                    <span class="badge text-bg-light border">共 <span id="visibleCount"><?php echo count($mediaList); ?></span> 個項目</span>
                  </div>

                  <div class="d-flex align-items-center gap-2 flex-grow-1 flex-md-grow-0">
                    <div class="input-group input-group-sm" style="min-width: 220px;">
                      <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                      <input type="text" id="fmQuickSearch" class="form-control" placeholder="即時篩選檔名、標籤或說明...">
                    </div>
                    <div class="btn-group btn-group-sm" role="group" aria-label="View Mode">
                      <button type="button" class="btn btn-primary active" id="btnViewGrid" title="大圖網格視圖">
                        <i class="bi bi-grid-fill"></i>
                      </button>
                      <button type="button" class="btn btn-outline-secondary" id="btnViewList" title="詳細清單視圖">
                        <i class="bi bi-list-ul"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <div class="card-body">
                  <?php if (empty($mediaList)): ?>
                    <div class="text-center text-muted py-5 my-4">
                      <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                      <div class="fw-bold">此資料夾目前尚無符合條件的多媒體檔案</div>
                      <a href="media_detail.php?currID=0" class="btn btn-primary btn-sm mt-3">
                        <i class="bi bi-plus-circle me-1"></i>立即上傳或新增檔案
                      </a>
                    </div>
                  <?php else: ?>

                    <!-- 1. 大圖網格視圖 (Grid View) -->
                    <div id="fmGridView" class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
                      <?php foreach ($mediaList as $row):
                        $mid     = (int)$row['med_id'];
                        $mType   = (int)($row['med_type'] ?? 1);
                        $mSrc    = (int)($row['med_source'] ?? 1);
                        $dlMode  = (int)($row['med_allow_download'] ?? 0);
                        $sta     = (int)($row['med_status'] ?? 1);
                        $url     = $row['med_url'] ?? '';
                        $title   = $row['med_title'] ?? '';
                        $cateTxt = $cateDefs[$row['med_category']]['name'] ?? ($row['med_category'] ?? '');
                        $searchStr = mb_strtolower($title . ' ' . $cateTxt . ' ' . ($row['med_brief'] ?? '') . ' ' . ($row['med_ext'] ?? ''), 'UTF-8');
                      ?>
                        <div class="col fm-item-node" data-search="<?php echo htmlspecialchars($searchStr); ?>">
                          <div class="fm-file-card <?php echo ($sta === 0) ? 'opacity-50' : ''; ?>" onclick="window.location.href='media_detail.php?currID=<?php echo $mid; ?>';">
                            <div class="fm-thumb-box">
                              <?php if ($mType === 1 && $url !== ''):
                                $imgSrc = (strpos($url, 'http') === 0) ? $url : ('../../' . ltrim($url, '/'));
                              ?>
                                <span class="fm-type-pill bg-primary text-white"><i class="bi bi-image me-1"></i>圖檔</span>
                                <img src="<?php echo htmlspecialchars($imgSrc); ?>" loading="lazy" alt="<?php echo htmlspecialchars($title); ?>">
                              <?php elseif ($mType === 2):
                                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $ytM);
                                $ytId = $ytM[1] ?? '';
                              ?>
                                <span class="fm-type-pill bg-danger text-white"><i class="bi bi-youtube me-1"></i>YouTube</span>
                                <?php if ($ytId !== ''): ?>
                                  <img src="https://i.ytimg.com/vi/<?php echo htmlspecialchars($ytId); ?>/mqdefault.jpg" loading="lazy" alt="YouTube">
                                <?php else: ?>
                                  <i class="bi bi-youtube fs-1 text-danger"></i>
                                <?php endif; ?>
                                <div class="fm-yt-play"><i class="bi bi-play-fill"></i></div>
                              <?php elseif ($mType === 3): ?>
                                <span class="fm-type-pill bg-info text-dark"><i class="bi bi-music-note-beamed me-1"></i>音訊</span>
                                <div class="text-center text-info">
                                  <i class="bi bi-soundwave fs-1 d-block"></i>
                                  <span class="badge bg-white text-dark border mt-1"><?php echo strtoupper(htmlspecialchars($row['med_ext'] ?: 'MP3')); ?></span>
                                </div>
                              <?php else: ?>
                                <span class="fm-type-pill bg-warning text-dark"><i class="bi bi-file-earmark-text me-1"></i>文件</span>
                                <div class="text-center text-warning-emphasis">
                                  <i class="bi bi-file-earmark-pdf-fill fs-1 d-block"></i>
                                  <span class="badge bg-white text-dark border mt-1"><?php echo strtoupper(htmlspecialchars($row['med_ext'] ?: 'DOC')); ?></span>
                                </div>
                              <?php endif; ?>

                              <?php if ($dlMode > 0): ?>
                                <button type="button" class="btn btn-light btn-xs py-0 px-1 border shadow-sm fm-dl-pill btn-copy-dl"
                                  data-dl="5sl_download.php?id=<?php echo $mid; ?>" title="複製隱藏檔名安全下載連結">
                                  <i class="bi bi-link-45deg text-primary"></i>
                                </button>
                              <?php endif; ?>
                            </div>

                            <div class="fm-card-body">
                              <div>
                                <div class="fm-title" title="<?php echo htmlspecialchars($title); ?>">
                                  <?php if ((int)($row['med_is_featured'] ?? 0) === 1): ?>
                                    <i class="bi bi-star-fill text-warning me-1" title="首頁精選"></i>
                                  <?php endif; ?>
                                  <?php echo htmlspecialchars($title); ?>
                                </div>
                                <div class="text-muted mt-1" style="font-size: 0.72rem;">
                                  <?php echo $row['med_event_date']; ?> • <?php echo htmlspecialchars($cateTxt); ?>
                                </div>
                              </div>

                              <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top" style="font-size: 0.7rem;">
                                <span class="text-muted">
                                  <?php
                                  if (!empty($row['med_filesize'])) echo round((int)$row['med_filesize'] / 1024, 1) . ' KB';
                                  elseif (!empty($row['med_duration'])) echo htmlspecialchars($row['med_duration']);
                                  else echo ($mSrc === 1) ? '本地檔案' : '雲端外連';
                                  ?>
                                </span>
                                <span>
                                  <?php if ($dlMode > 0): ?>
                                    <span class="text-success fw-semibold"><i class="bi bi-download me-1"></i><?php echo (int)$row['med_dl_count']; ?></span>
                                  <?php else: ?>
                                    <span class="text-muted">僅預覽</span>
                                  <?php endif; ?>
                                </span>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>

                    <!-- 2. 詳細清單視圖 (List View，預設隱藏) -->
                    <div id="fmListView" class="table-responsive d-none">
                      <table class="table table-hover table-bordered align-middle w-100 small">
                        <thead class="table-light">
                          <tr>
                            <th style="width: 55px;" class="text-center">ID</th>
                            <th>檔案 / 活動名稱</th>
                            <th>資料夾分類</th>
                            <th>格式 / 大小</th>
                            <th>下載權限</th>
                            <th>活動日期</th>
                            <th class="text-center" style="width: 90px;">安全連結</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($mediaList as $row):
                            $mid     = (int)$row['med_id'];
                            $dlMode  = (int)($row['med_allow_download'] ?? 0);
                            $cateTxt = $cateDefs[$row['med_category']]['name'] ?? ($row['med_category'] ?? '');
                            $searchStr = mb_strtolower(($row['med_title'] ?? '') . ' ' . $cateTxt . ' ' . ($row['med_brief'] ?? ''), 'UTF-8');
                          ?>
                            <tr class="fm-item-node" data-search="<?php echo htmlspecialchars($searchStr); ?>" style="cursor: pointer;" onclick="window.location.href='media_detail.php?currID=<?php echo $mid; ?>';">
                              <td class="text-center fw-bold"><?php echo $mid; ?></td>
                              <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['med_title']); ?></td>
                              <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($cateTxt); ?></span></td>
                              <td>
                                <code><?php echo strtoupper(htmlspecialchars($row['med_ext'] ?: '-')); ?></code>
                                <?php if (!empty($row['med_filesize'])): ?>
                                  <span class="text-muted ms-1">(<?php echo round((int)$row['med_filesize'] / 1024, 1); ?> KB)</span>
                                <?php endif; ?>
                              </td>
                              <td><?php echo ($dlMode > 0) ? '<span class="text-success fw-bold">開放下載 (' . (int)$row['med_dl_count'] . '次)</span>' : '<span class="text-muted">禁止下載</span>'; ?></td>
                              <td class="text-muted"><?php echo $row['med_event_date']; ?></td>
                              <td class="text-center">
                                <?php if ($dlMode > 0): ?>
                                  <button type="button" class="btn btn-outline-primary btn-xs py-0 px-2 btn-copy-dl" data-dl="5sl_download.php?id=<?php echo $mid; ?>">
                                    <i class="bi bi-clipboard"></i> 複製
                                  </button>
                                <?php else: ?>
                                  <span class="text-muted">-</span>
                                <?php endif; ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>

                  <?php endif; ?>
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
  <script src="../scriptFiles/adminlte.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t=' . time(); ?>"></script>

  <script type="text/javascript">
    function doReDirect() {
      window.history.back();
    }

    function doButAdd() {
      window.location.href = 'media_detail.php?currID=0';
    }

    function doButReset() {
      window.location.replace('media_list.php');
    }

    $(document).ready(function() {
      // 1. 大圖網格 (Grid) vs 詳細清單 (List) 雙視圖切換
      $('#btnViewGrid').on('click', function() {
        $(this).addClass('btn-primary active').removeClass('btn-outline-secondary');
        $('#btnViewList').removeClass('btn-primary active').addClass('btn-outline-secondary');
        $('#fmListView').addClass('d-none');
        $('#fmGridView').removeClass('d-none');
      });

      $('#btnViewList').on('click', function() {
        $(this).addClass('btn-primary active').removeClass('btn-outline-secondary');
        $('#btnViewGrid').removeClass('btn-primary active').addClass('btn-outline-secondary');
        $('#fmGridView').addClass('d-none');
        $('#fmListView').removeClass('d-none');
      });

      // 2. 即時快篩過濾卡片與清單列
      $('#fmQuickSearch').on('keyup search input', function() {
        const kw = $(this).val().trim().toLowerCase();
        let shown = 0;
        $('#fmGridView .fm-item-node').each(function() {
          const hay = String($(this).data('search') || '');
          const match = (kw === '' || hay.indexOf(kw) !== -1);
          $(this).toggle(match);
          if (match) shown++;
        });
        $('#fmListView .fm-item-node').each(function() {
          const hay = String($(this).data('search') || '');
          $(this).toggle(kw === '' || hay.indexOf(kw) !== -1);
        });
        $('#visibleCount').text(shown);
      });

      // 3. 一鍵複製隱藏真實檔名安全下載網址
      $(document).on('click', '.btn-copy-dl', function(e) {
        e.stopPropagation();
        const dlUrl = $(this).data('dl');
        navigator.clipboard.writeText(dlUrl);
        if (window.parent && window.parent.toastr) {
          window.parent.toastr['success']('已複製安全下載網址：' + dlUrl);
        }
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