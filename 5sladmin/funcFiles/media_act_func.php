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

$token    = $_POST['token'] ?? '';
$formAcce = trim((string)($_POST['formAcce'] ?? ''));
$medID    = (int)($_POST['medID'] ?? 0);

if (function_exists('_fn_chkToken') && !_fn_chkToken($token)) {
  echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '安全驗證失敗（Token 無效），請重新整理頁面後再試！']);
  exit();
}

switch ($formAcce) {

  // =========================================================================
  // 1. 新增 / 更新多媒體與檔案庫資料 (func_media_addnew / func_media_update)
  // =========================================================================
  case 'func_media_addnew':
  case 'func_media_update':
    $acc       = ($formAcce === 'func_media_addnew') ? 'add' : 'up';
    $title     = trim((string)($_POST['med_title'] ?? ''));
    $eventDate = trim((string)($_POST['med_event_date'] ?? date('Y-m-d')));
    $medType   = (int)($_POST['med_type'] ?? 1);
    $medSource = (int)($_POST['med_source'] ?? 1);
    $medUrl    = trim((string)($_POST['med_url'] ?? ''));

    if ($title === '' || $eventDate === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請填寫媒體名稱標題與活動發生日期！']);
      exit();
    }

    $ext      = '';
    $fileSize = 0;

    // 若為修改模式，先取得原有檔案資訊
    if ($acc === 'up') {
      if ($medID <= 0) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的媒體 ID！']);
        exit();
      }
      $oldRow   = DB->row("SELECT med_url, med_ext, med_filesize FROM fsl_media WHERE med_id = ?", array($medID));
      $ext      = $oldRow['med_ext'] ?? '';
      $fileSize = (int)($oldRow['med_filesize'] ?? 0);
      if ($medUrl === '' && !empty($oldRow['med_url'])) {
        $medUrl = $oldRow['med_url'];
      }
    }

    // 處理本地檔案上傳 (med_source = 1 且有選取檔案)
    if ($medSource === 1 && isset($_FILES['med_upload_file']) && $_FILES['med_upload_file']['error'] === UPLOAD_ERR_OK) {
      $origName = $_FILES['med_upload_file']['name'];
      $tmpPath  = $_FILES['med_upload_file']['tmp_name'];
      $fileSize = (int)$_FILES['med_upload_file']['size'];
      $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

      // 安全白名單副檔名檢查
      $allowExts = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'mp3', 'm4a', 'wav', 'pdf', 'zip', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx');
      if (!in_array($ext, $allowExts, true)) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => "不支援的檔案格式 (.{$ext})，請上傳圖片、音訊或常見文件格式！"]);
        exit();
      }

      $uploadDir = '../../uploadFiles/md/';
      if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
      }

      // 產生隨機雜湊安全檔名，防止猜測實體路徑
      $safeFileName = 'md_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
      $destPath     = $uploadDir . $safeFileName;

      if (!move_uploaded_file($tmpPath, $destPath)) {
        echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '檔案儲存至伺服器失敗，請檢查 uploadFiles/md/ 目錄權限！']);
        exit();
      }

      // 若是修改並替換新檔，刪除舊的本地實體檔案
      if ($acc === 'up' && !empty($oldRow['med_url']) && strpos($oldRow['med_url'], 'uploadFiles/md/') === 0) {
        $oldPhysical = '../../' . $oldRow['med_url'];
        if (file_exists($oldPhysical)) {
          @unlink($oldPhysical);
        }
      }

      $medUrl = 'uploadFiles/md/' . $safeFileName;
    } elseif ($medSource === 2) {
      // 雲端外連或 YouTube 視訊
      $fileSize = 0;
      if ($medType === 2) {
        $ext = 'yt';
      } elseif ($medUrl !== '') {
        $pathExt = strtolower(pathinfo(parse_url($medUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        $ext = $pathExt ?: 'url';
      }
    }

    if ($medUrl === '') {
      echo json_encode(['RTN' => 0, 'msgType' => 'warning', 'msgStr' => '請上傳本地檔案，或輸入雲端 / YouTube 連結！']);
      exit();
    }

    $dataArr = array(
      'med_event_date'     => $eventDate,
      'med_category'       => trim((string)($_POST['med_category'] ?? 'workshop')),
      'med_type'           => $medType,
      'med_source'         => $medSource,
      'med_title'          => $title,
      'med_brief'          => trim((string)($_POST['med_brief'] ?? '')) ?: null,
      'med_url'            => $medUrl,
      'med_ext'            => strtoupper($ext),
      'med_filesize'       => $fileSize,
      'med_duration'       => trim((string)($_POST['med_duration'] ?? '')) ?: null,
      'med_allow_download' => (int)($_POST['med_allow_download'] ?? 0),
      'med_for'            => trim((string)($_POST['med_for'] ?? 'general')) ?: 'general',
      'med_forid'          => max(0, (int)($_POST['med_forid'] ?? 0)),
      'gift_id'            => !empty($_POST['gift_id']) ? (int)$_POST['gift_id'] : null,
      'merch_id'           => !empty($_POST['merch_id']) ? (int)$_POST['merch_id'] : null,
      'med_is_featured'    => (int)($_POST['med_is_featured'] ?? 0),
      'med_in_gallery'     => (int)($_POST['med_in_gallery'] ?? 1),
      'med_sort'           => max(0, (int)($_POST['med_sort'] ?? 100)),
      'med_status'         => (int)($_POST['med_status'] ?? 1)
    );

    if ($acc === 'add') {
      $dataArr['med_dl_count'] = 0;
      DB->insert('fsl_media', $dataArr);
      $newID = (int)DB->lastInsertId();
      echo json_encode([
        'RTN'     => 1,
        'msgType' => 'success',
        'msgStr'  => '多媒體與檔案庫資料已成功建立！',
        'newID'   => $newID
      ]);
    } else {
      DB->update('fsl_media', $dataArr, ['med_id' => $medID]);
      echo json_encode([
        'RTN'     => 1,
        'msgType' => 'success',
        'msgStr'  => '多媒體與檔案庫資料已儲存更新！'
      ]);
    }
    exit();
  break;

  // =========================================================================
  // 2. 軟刪除檔案庫資料 (func_media_delete)
  // =========================================================================
  case 'func_media_delete':
    if ($medID <= 0) {
      echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '無效的媒體 ID！']);
      exit();
    }
    DB->update('fsl_media', ['med_status' => 9], ['med_id' => $medID]);
    echo json_encode([
      'RTN'     => 1,
      'msgType' => 'success',
      'msgStr'  => '檔案庫紀錄已成功刪除封存！'
    ]);
    exit();
  break;

  default:
    echo json_encode(['RTN' => 0, 'msgType' => 'error', 'msgStr' => '未知的操作指令！']);
    exit();
  break;
}
