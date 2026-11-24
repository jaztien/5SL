<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');

header('Content-Type: application/json; charset=utf-8');

// 1. 驗證管理者登入 Session 與 CSRF Token
if (empty($_SESSION['SESS_AD_ID'])) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '登入已逾時，請重新登入後台系統！']);
  exit();
}

$token      = $_POST['token'] ?? '';
$formAcce   = trim((string)($_POST['formAcce'] ?? ''));
$formData   = (array)($_POST['arrDatA'] ?? array());
$slotImages = (array)($_POST['slotImages'] ?? array());
$merchID    = (int)($_POST['merchID'] ?? 0);

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

switch ($formAcce) {

  // =========================================================================
  // 1. 新增 / 更新商品與贈品資料 (func_merch_addnew / func_merch_infoUpdate)
  // =========================================================================
  case 'func_merch_addnew':
  case 'func_merch_infoUpdate':
    $acc   = ($formAcce === 'func_merch_addnew') ? 'add' : 'up';
    $sku   = strtoupper(trim((string)($formData['merch_sku'] ?? '')));
    $title = trim((string)($formData['merch_title'] ?? ''));

    if ($sku === '' || $title === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請完整填寫商品貨號 (SKU) 與商品名稱！']);
      exit();
    }

    // 檢查貨號 (SKU) 唯一性
    $dupId = (int)DB->single(
      "SELECT merch_id FROM fsl_merchandise WHERE UPPER(merch_sku) = ? AND merch_id != ? AND merch_status != 9",
      array($sku, $merchID)
    );
    if ($dupId > 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => "商品貨號「{$sku}」已被其他商品（ID: #{$dupId}）使用，請更換貨號！"]);
      exit();
    }

    $upTime = date('Y-m-d H:i:s');
    $formData['merch_sku']       = $sku;
    $formData['merch_keycode']   = strtoupper(trim((string)($formData['merch_keycode'] ?? ''))) ?: null;
    $formData['merch_title']     = $title;
    $formData['merch_type']      = (int)($formData['merch_type'] ?? 1);
    $formData['merch_is_gift']   = (int)($formData['merch_is_gift'] ?? 0);
    $formData['gift_id']         = !empty($formData['gift_id']) ? (int)$formData['gift_id'] : null;
    $formData['merch_ribbon']    = (int)($formData['merch_ribbon'] ?? 0);
    $formData['merch_status']    = (int)($formData['merch_status'] ?? 1);
    $formData['merch_price_twd'] = max(0, (int)($formData['merch_price_twd'] ?? 0));
    $formData['merch_price_usd'] = max(0, (float)($formData['merch_price_usd'] ?? 0));
    $formData['merch_sale_usd']  = (isset($formData['merch_sale_usd']) && $formData['merch_sale_usd'] !== '') ? max(0, (float)$formData['merch_sale_usd']) : null;
    $formData['merch_pts_limit'] = max(0, (int)($formData['merch_pts_limit'] ?? 0));
    $formData['merch_stock']     = (int)($formData['merch_stock'] ?? 999);

    if ($acc === 'add') {
      $formData['mb_id']           = (int)$_SESSION['SESS_AD_ID'];
      $formData['merch_published'] = $upTime;
      $formData['merch_sold']      = 0;
      $formData['merch_visits']    = 0;
      DB->insert('fsl_merchandise', $formData);
      $targetID = (int)DB->lastInsertId();
    } else {
      if ($merchID <= 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的商品 ID！']);
        exit();
      }
      $formData['merch_updated'] = $upTime;
      DB->update('fsl_merchandise', $formData, ['merch_id' => $merchID]);
      $targetID = $merchID;
    }

    // 處理 6 張商品主圖裁切上傳 (直接檔名 + 索引號 mimg_{id}_{1~6}_.jpg，不寫入資料庫)
    $uploadedCnt = 0;
    if ($targetID > 0 && !empty($slotImages)) {
      $uploadDir = '../uploadFiles/merch/';
      if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
      }
      foreach ($slotImages as $slotKey => $base64Data) {
        $idx = (int)$slotKey;
        if ($idx >= 1 && $idx <= 6 && is_string($base64Data) && $base64Data !== '') {
          $ok = _fn_image_upload($base64Data, $uploadDir . 'mimg_' . $targetID . '_' . $idx, 800, 800);
          if ($ok) $uploadedCnt++;
        }
      }
    }

    $imgNote = ($uploadedCnt > 0) ? "並同步儲存 {$uploadedCnt} 張商品圖片！" : "";
    $rtnArr  = array(
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '商品與贈品資料已成功' . ($acc === 'add' ? '建立' : '更新') . '！' . $imgNote,
      'upTime'  => $upTime
    );
    if ($acc === 'add') {
      $rtnArr['newID'] = $targetID;
    }
    echo json_encode($rtnArr);
    exit();
  break;

  // =========================================================================
  // 2. 刪除單張商品主圖槽位檔案 (func_merch_delImage)
  // =========================================================================
  case 'func_merch_delImage':
    $slot = (int)($_POST['slot'] ?? 0);
    if ($merchID <= 0 || $slot < 1 || $slot > 6) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的商品或圖片槽位參數！']);
      exit();
    }

    $prefix = '../uploadFiles/merch/mimg_' . $merchID . '_' . $slot;
    foreach (array($prefix . '_.jpg', $prefix . '_thumb.jpg') as $f) {
      if (file_exists($f)) {
        @unlink($f);
      }
    }

    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => "商品圖片 #{$slot} 已從伺服器移除！"
    ]);
    exit();
  break;

  // =========================================================================
  // 3. 軟刪除 / 下架封存商品資料 (func_merch_delete)
  // =========================================================================
  case 'func_merch_delete':
    if ($merchID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的商品 ID！']);
      exit();
    }
    DB->update('fsl_merchandise', ['merch_status' => 9], ['merch_id' => $merchID]);
    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '商品資料已成功刪除封存！'
    ]);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
