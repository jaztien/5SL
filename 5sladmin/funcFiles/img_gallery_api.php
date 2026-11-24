<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

header('Content-Type: application/json; charset=utf-8');

// 驗證管理者登入 Session
if (empty($_SESSION['SESS_AD_ID'])) {
  echo json_encode(['statusCode' => 401, 'errorMessage' => 'Unauthorized']);
  exit();
}

// 查詢所有「圖檔 (med_type=1)」、「上架中 (med_status=1)」且「開放 SunEditor 圖庫 (med_in_gallery=1)」的紀錄
$rows = DB->query("
  SELECT med_id, med_title, med_category, med_url
  FROM fsl_media
  WHERE med_type = 1 AND med_status = 1 AND med_in_gallery = 1 AND med_url IS NOT NULL AND med_url != ''
  ORDER BY med_id DESC
  LIMIT 200
");

$cateLabels = array(
  'workshop' => '課程工作坊',
  'salon'    => '藝術沙龍',
  'retreat'  => '能量營',
  'audio'    => '音頻紀實',
  'resource' => '限定素材'
);

$result = array();
if (is_array($rows)) {
  foreach ($rows as $r) {
    $rawUrl = trim($r['med_url']);
    // 轉換為前台與後台 IFrame 皆可正常渲染的相對或絕對路徑
    $srcUrl = (strpos($rawUrl, 'http') === 0) ? $rawUrl : ('../../' . ltrim($rawUrl, '/'));
    $tag    = $cateLabels[$r['med_category']] ?? '全站圖庫';

    $result[] = array(
      'src'  => $srcUrl,
      'name' => $r['med_title'],
      'alt'  => $r['med_title'],
      'tag'  => $tag
    );
  }
}

echo json_encode(array(
  'statusCode' => 200,
  'result'     => $result
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
