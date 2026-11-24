<?php
require_once('5sladmin/includeFiles/init.inc.php');
require_once('5sladmin/includeFiles/db.inc.php');

$medID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($medID <= 0) {
  http_response_code(400);
  exit('無效的檔案請求參數。');
}

$media = DB->row("SELECT * FROM fsl_media WHERE med_id = ? AND med_status = 1", array($medID));
if (!$media) {
  http_response_code(404);
  exit('找不到指定的檔案，或該檔案已下架。');
}

$allowMode = (int)($media['med_allow_download'] ?? 0);
$mbID      = (int)($_SESSION['SESS_MB_ID'] ?? 0);
$isAdmin   = !empty($_SESSION['SESS_AD_ID']);

// 1. 權限檢核（後台管理者預覽測試不受限制）
if (!$isAdmin) {
  if ($allowMode === 0) {
    http_response_code(403);
    exit('此檔案僅供線上預覽，未開放下載權限。');
  }

  if ($allowMode >= 2 && $mbID <= 0) {
    http_response_code(401);
    exit('請先登入「人生第五季」會員帳號後再進行下載。');
  }

  // 權限等級 3：需驗證是否已購買關聯課程、商品或已領取關聯禮品
  if ($allowMode === 3) {
    $hasAccess = false;

    // 檢查關聯課程報名 (med_for = 'course')
    if (($media['med_for'] ?? '') === 'course' && (int)$media['med_forid'] > 0) {
      $cnt = (int)DB->single(
        "SELECT COUNT(*) FROM fsl_course_enrolls WHERE mb_id = ? AND course_id = ? AND enr_pay_status IN (1, 2) AND enr_status IN (1, 2, 3)",
        array($mbID, (int)$media['med_forid'])
      );
      if ($cnt > 0) $hasAccess = true;
    }

    // 檢查關聯商品購買 (merch_id)
    if (!$hasAccess && !empty($media['merch_id'])) {
      $cnt = (int)DB->single(
        "SELECT COUNT(*) FROM fsl_order_items oi INNER JOIN fsl_orders o ON oi.ord_id = o.ord_id WHERE o.mb_id = ? AND oi.ordi_type = 2 AND oi.merch_id = ? AND o.ord_status IN (2, 3, 4)",
        array($mbID, (int)$media['merch_id'])
      );
      if ($cnt > 0) $hasAccess = true;
    }

    // 檢查關聯禮品領取 (gift_id)
    if (!$hasAccess && !empty($media['gift_id'])) {
      $cnt = (int)DB->single(
        "SELECT COUNT(*) FROM fsl_gift_records WHERE mb_id = ? AND gift_id = ? AND grec_status IN (1, 2, 3)",
        array($mbID, (int)$media['gift_id'])
      );
      if ($cnt > 0) $hasAccess = true;
    }

    if (!$hasAccess) {
      http_response_code(403);
      exit('您尚未取得此限定素材的下載資格（需完成購買關聯課程/商品或領取專屬禮品）。');
    }
  }
}

// 2. 累計安全下載次數
DB->query("UPDATE fsl_media SET med_dl_count = med_dl_count + 1 WHERE med_id = ?", array($medID));

$medSource = (int)($media['med_source'] ?? 1);
$rawUrl    = trim((string)($media['med_url'] ?? ''));

// 3. 若為雲端外連檔案，執行伺服器端安全跳轉
if ($medSource === 2 || strpos($rawUrl, 'http') === 0) {
  header('Location: ' . $rawUrl);
  exit();
}

// 4. 若為本地伺服器檔案，透過 PHP Readfile 串流輸出，完全隱藏真實路徑與內部檔名
$physicalPath = __DIR__ . '/' . ltrim($rawUrl, '/');
if (!file_exists($physicalPath) || !is_file($physicalPath)) {
  http_response_code(404);
  exit('實體檔案遺失，請聯絡網站管理員。');
}

$ext          = strtolower(pathinfo($physicalPath, PATHINFO_EXTENSION));
$safeTitle    = preg_replace('/[\\\\\/:*?"<>|]/', '_', $media['med_title']);
$downloadName = $safeTitle . '.' . $ext;

$mimeTypes = array(
  'pdf'  => 'application/pdf',
  'zip'  => 'application/zip',
  'mp3'  => 'audio/mpeg',
  'm4a'  => 'audio/mp4',
  'wav'  => 'audio/wav',
  'jpg'  => 'image/jpeg',
  'jpeg' => 'image/jpeg',
  'png'  => 'image/png',
  'webp' => 'image/webp',
  'doc'  => 'application/msword',
  'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'xls'  => 'application/vnd.ms-excel',
  'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  'ppt'  => 'application/vnd.ms-powerpoint',
  'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
);
$contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

// 清除輸出緩衝區避免檔案損毀
if (ob_get_level()) {
  ob_end_clean();
}

header('Content-Description: File Transfer');
header('Content-Type: ' . $contentType);
header('Content-Disposition: attachment; filename="' . rawurlencode($downloadName) . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($physicalPath));

readfile($physicalPath);
exit();
