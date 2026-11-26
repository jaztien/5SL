<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 定義本頁面代碼（對應左側樹狀導航列與頂部操作面板）
define('PAGE_MARK', 'PG_MERCH');
define('PAGE_ITEM', 'ITEM_MERCH');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$merchID = currID;
$res     = ($merchID > 0) ? DB->row("SELECT * FROM fsl_merchandise WHERE merch_id = ? AND merch_status != 9", array($merchID)) : array();
if (!is_array($res)) $res = array();

// 取得可關聯的禮品/優惠券清單 (供贈品屬性商品反向關聯)
$linkedGifts = ($merchID > 0) ? DB->query("SELECT gift_id, gift_code, gift_title, gift_status FROM fsl_gifts WHERE FIND_IN_SET(?, gift_merch_ids) AND gift_status != 9 ORDER BY gift_id DESC", array($merchID)) : array();
if (!is_array($linkedGifts)) $linkedGifts = array();
// 查詢該商品的歷史銷售明細紀錄 (關聯訂單主表、會員資料與發票狀態)
$salesRows     = array();
$totalSalesQty = 0;
$totalSalesAmt = 0.00;

if ($merchID > 0) {
  $salesRows = DB->query("
    SELECT oi.ordi_id, oi.ordi_unit_price, oi.ordi_qty, oi.ordi_subtotal,
           o.ord_id, o.ord_no, o.ord_created, o.ord_status,
           m.mb_id, m.mb_name, m.mb_email,
           i.inv_no
    FROM fsl_order_items oi
    INNER JOIN fsl_orders o ON oi.ord_id = o.ord_id
    LEFT JOIN fsl_members m ON o.mb_id = m.mb_id
    LEFT JOIN fsl_invoices i ON o.ord_id = i.ord_id
    WHERE oi.ordi_type = 2 AND oi.merch_id = ?
    ORDER BY o.ord_id DESC
    LIMIT 200
  ", array($merchID));
  if (!is_array($salesRows)) $salesRows = array();

  foreach ($salesRows as $sr) {
    // 排除已取消/退費(6)後累計有效銷售件數與金額
    if ((int)$sr['ord_status'] !== 6) {
      $totalSalesQty += (int)$sr['ordi_qty'];
      $totalSalesAmt += (float)$sr['ordi_subtotal'];
    }
  }
}

// 掃描 6 張商品主圖檔案存在狀態 (mimg_{merch_id}_{1~6}_.jpg)
$merchImages = array();
for ($idx = 1; $idx <= 6; $idx++) {
  $filePath = "../uploadFiles/merch/mimg_" . $merchID . "_" . $idx . "_.jpg";
  $exists   = ($merchID > 0 && file_exists($filePath));
  $merchImages[$idx] = array(
    'exists' => $exists,
    'src'    => $exists ? ($filePath . "?t=" . filemtime($filePath)) : "../imageFiles/nopic_200x200.jpg"
  );
}

$typeLabels = array(1 => '實體商品 (書籍/工具盒)', 2 => '數位物件 (音訊/圖像/文件)', 3 => '實體沙龍票券');
$giftLabels = array(0 => '一般販售商品', 1 => '純贈品 (不單獨販售)', 2 => '販售＋贈品雙用');
$statusLabels = array(0 => '下架停用', 1 => '上架啟用', 2 => '預購或待審');



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
  <title>Fifth Season Life 人生第五季 | 商品贈品詳情管理</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <!-- SunEditor 視覺化 HTML 編輯器樣式 -->
  <link rel="stylesheet" href="../pluginFiles/suneditor/css/suneditor.min.css">
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">

<style>
  /* 6 張商品主圖網格槽位樣式 */
  .merch-slot-card {
    position: relative;
    border: 1.5px dashed #ced4da;
    border-radius: 10px;
    background-color: #f8f9fa;
    overflow: hidden;
    aspect-ratio: 1 / 1;
    cursor: pointer;
    transition: border-color 0.2s ease, transform 0.2s ease;
  }
  .merch-slot-card:hover {
    border-color: #df8233;
    transform: translateY(-2px);
  }
  .merch-slot-card.has-img {
    border-style: solid;
    border-color: #adb5bd;
  }
  .merch-slot-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .merch-slot-badge {
    position: absolute;
    top: 6px;
    left: 6px;
    z-index: 5;
    font-size: 0.7rem;
    padding: 2px 7px;
    border-radius: 4px;
    background: rgba(33, 37, 41, 0.82);
    color: #fff;
  }
  .merch-slot-badge.is-main {
    background: #df8233;
    font-weight: 700;
  }
  .merch-slot-del {
    position: absolute;
    top: 6px;
    right: 6px;
    z-index: 6;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: 1px solid #dc3545;
    background: #ffffff;
    color: #dc3545;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    transition: all 0.15s ease;
  }
  .merch-slot-del:hover {
    background: #dc3545;
    color: #ffffff;
  }
  .merch-slot-caption {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.58);
    color: #fff;
    font-size: 0.72rem;
    text-align: center;
    padding: 3px 0;
    opacity: 0;
    transition: opacity 0.2s ease;
  }
  .merch-slot-card:hover .merch-slot-caption {
    opacity: 1;
  }
  .sun-editor {
    font-family: 'Noto Sans TC', sans-serif !important;
    border-radius: 8px !important;
  }
</style>
</head>
<body class="bg-body-tertiary theme-orange">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1 ms-2"><i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?></div>
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                <?php echo !empty($merchID) ? "編輯" : "新增"; ?>商品與贈品資料
                <span class="fs-5 text-warning-emphasis">【ID: <?php echo $merchID; ?>】</span>
              </h4>
              <h6 class="m-0 fw-light text-muted fs-7 mt-1 lh-xs mb-n2"><i class="bi bi-arrow-return-right"></i> 商品上下架、雙幣定價、庫存管理、6張主圖庫與 SunEditor 詳細圖文</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">商品銷售管理</li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回列表">商品贈品管理</li>
                <li class="breadcrumb-item active"><?php echo !empty($merchID) ? "商品詳情編輯" : "新增商品"; ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <!-- 左側商品摘要與狀態卡片 -->
            <div class="col-md-3 mb-3">
              <div class="card card-warning card-outline shadow-lg">
                <div class="card-body">
                  <div class="text-center mb-3">
                    <img id="sideMainCover" src="<?php echo $merchImages[1]['src']; ?>" class="img-fluid rounded-3 border shadow-sm" style="max-height: 220px; width: 100%; object-fit: cover;" alt="商品主圖#1">
                    <h5 class="fw-bold mt-3 mb-1" id="sideMerchTitle"><?php echo !empty($res['merch_title']) ? htmlspecialchars($res['merch_title']) : ($merchID > 0 ? '未命名商品' : '新建立商品'); ?></h5>
                    <div class="small text-muted" id="sideMerchSku"><?php echo !empty($res['merch_sku']) ? 'SKU: ' . htmlspecialchars($res['merch_sku']) : '尚未設定貨號'; ?></div>
                  </div>

                  <ul class="list-group list-group-flush small">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>上下架狀態</b>
                      <span id="sideMerchStatus" class="<?php echo ((int)($res['merch_status'] ?? 1) === 1) ? 'text-success fw-bold' : 'text-danger'; ?>">
                        <?php echo $statusLabels[(int)($res['merch_status'] ?? 1)] ?? '上架啟用'; ?>
                      </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>台幣定價</b>
                      <span class="fw-bold text-dark" id="sidePriceTwd">NT$ <?php echo number_format((int)($res['merch_price_twd'] ?? 0)); ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>美金定價</b>
                      <span class="fw-bold text-primary" id="sidePriceUsd">USD $<?php echo number_format((float)($res['merch_price_usd'] ?? 0), 2); ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>目前庫存</b>
                      <span class="fw-bold text-warning-emphasis" id="sideStockVal">
                        <?php
                          $stNum = (int)($res['merch_stock'] ?? 999);
                          echo ($stNum < 0) ? '無限量 (數位)' : number_format($stNum) . ' 件';
                        ?>
                      </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>累計銷售 / 發放</b>
                      <span><?php echo number_format((int)($res['merch_sold'] ?? 0)); ?> 件</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>最後更新</b>
                      <span class="text-muted" id="sideMerchUpdated"><?php echo $res['merch_updated'] ?? '-'; ?></span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <!-- 右側多分頁商品管理區 -->
            <div class="col-md-9">
              <div class="card shadow-lg modern-card-tabs">
                <div class="card-header p-2">
                  <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                      <a class="nav-link active" href="#tabMenu1" data-index="1" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-box-seam me-1"></i>基本資訊
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu2" data-index="2" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-images me-1"></i>商品主圖庫
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu3" data-index="3" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-file-richtext me-1"></i>詳細圖文介紹
                      </a>
                    </li>
                    <?php if ($merchID > 0): ?>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu4" data-index="4" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-receipt me-1"></i>銷售紀錄 (<?php echo count($salesRows); ?>)
                      </a>
                    </li>
                    <?php endif; ?>
                  </ul>
                </div>

                <div class="card-body bg-opacity-50">
                  <form id="form_merchMaster" action="../funcFiles/merch_act_func.php" method="post" data-func="func_merch_infoUpdate" novalidate>
                    <div class="tab-content">
                      <!-- Tab 1: 基本規格、雙幣定價與庫存管理 -->
                      <div class="tab-pane fade show active" id="tabMenu1" role="tabpanel">
                        <div class="row g-3">
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">商品貨號 / SKU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" id="merch_sku" name="merch_sku" required
                                   value="<?php echo htmlspecialchars($res['merch_sku'] ?? ''); ?>" placeholder="如：5SL-PHY-001">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">指定調用代碼</label>
                            <input type="text" class="form-control text-uppercase" name="merch_keycode"
                                   value="<?php echo htmlspecialchars($res['merch_keycode'] ?? ''); ?>" placeholder="獨立調用代碼">
                          </div>
                          <div class="col-md-5">
                            <label class="form-label small fw-bold">商品分類目錄 (逗號分隔)</label>
                            <input type="text" class="form-control" name="merch_cate"
                                   value="<?php echo htmlspecialchars($res['merch_cate'] ?? ''); ?>" placeholder="如：療癒書籍,生命整理工具">
                          </div>

                          <div class="col-md-7">
                            <label class="form-label small fw-bold">商品名稱 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="merch_title" name="merch_title" required
                                   value="<?php echo htmlspecialchars($res['merch_title'] ?? ''); ?>" placeholder="例如：《我的生命微整理素描地圖》">
                          </div>
                          <div class="col-md-5">
                            <label class="form-label small fw-bold">商品副標題 / 下標</label>
                            <input type="text" class="form-control" name="merch_subtitle"
                                   value="<?php echo htmlspecialchars($res['merch_subtitle'] ?? ''); ?>" placeholder="簡短亮點說明">
                          </div>

                          <div class="col-md-3">
                            <label class="form-label small fw-bold">商品型態</label>
                            <select class="form-select" id="merch_type" name="merch_type">
                              <option value="1" <?php echo ((int)($res['merch_type'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 實體商品 (書籍/工具盒)</option>
                              <option value="2" <?php echo ((int)($res['merch_type'] ?? 1) === 2) ? 'selected' : ''; ?>>2 - 數位物件 (音訊/圖像/文件)</option>
                              <option value="3" <?php echo ((int)($res['merch_type'] ?? 1) === 3) ? 'selected' : ''; ?>>3 - 實體沙龍票券</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">贈品屬性</label>
                            <select class="form-select" id="merch_is_gift" name="merch_is_gift">
                              <option value="0" <?php echo ((int)($res['merch_is_gift'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 一般販售商品</option>
                              <option value="1" <?php echo ((int)($res['merch_is_gift'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 純贈品 (不單獨販售)</option>
                              <option value="2" <?php echo ((int)($res['merch_is_gift'] ?? 0) === 2) ? 'selected' : ''; ?>>2 - 可販售亦可作為贈品</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">邊角絲帶標註</label>
                            <select class="form-select" name="merch_ribbon">
                              <option value="0" <?php echo ((int)($res['merch_ribbon'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 無標註</option>
                              <option value="1" <?php echo ((int)($res['merch_ribbon'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 熱銷</option>
                              <option value="2" <?php echo ((int)($res['merch_ribbon'] ?? 0) === 2) ? 'selected' : ''; ?>>2 - 限量</option>
                              <option value="3" <?php echo ((int)($res['merch_ribbon'] ?? 0) === 3) ? 'selected' : ''; ?>>3 - 新品</option>
                              <option value="4" <?php echo ((int)($res['merch_ribbon'] ?? 0) === 4) ? 'selected' : ''; ?>>4 - 完課禮</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">上下架狀態</label>
                            <select class="form-select" id="merch_status" name="merch_status">
                              <option value="1" <?php echo ((int)($res['merch_status'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 上架啟用</option>
                              <option value="2" <?php echo ((int)($res['merch_status'] ?? 1) === 2) ? 'selected' : ''; ?>>2 - 預購或待審</option>
                              <option value="0" <?php echo ((int)($res['merch_status'] ?? 1) === 0) ? 'selected' : ''; ?>>0 - 下架停用</option>
                            </select>
                          </div>

                          <div class="col-md-6">
                            <label class="form-label small fw-bold d-block">關聯禮品與優惠券活動 (由 <code>fsl_gifts.gift_merch_ids</code> 自動連動)</label>
                            <div class="p-2 bg-light border rounded-2 small">
                              <?php if (empty($linkedGifts)): ?>
                                <span class="text-muted">目前尚未被任何禮品或優惠券活動引用</span>
                              <?php else: foreach ($linkedGifts as $lg): ?>
                                <a href="gift_detail.php?currID=<?php echo (int)$lg['gift_id']; ?>" class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle text-decoration-none me-1 mb-1">
                                  <i class="bi bi-gift me-1"></i>#<?php echo (int)$lg['gift_id']; ?> <?php echo htmlspecialchars($lg['gift_title']); ?>
                                </a>
                              <?php endforeach; endif; ?>
                            </div>
                          </div>

                          <div class="col-md-2">
                            <label class="form-label small fw-bold">數位檔案格式</label>
                            <input type="text" class="form-control text-uppercase" name="merch_file_format"
                                   value="<?php echo htmlspecialchars($res['merch_file_format'] ?? ''); ?>" placeholder="PDF/MP3">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">數位物件下載連結 (型態=2時填寫)</label>
                            <input type="text" class="form-control" name="merch_download_url"
                                   value="<?php echo htmlspecialchars($res['merch_download_url'] ?? ''); ?>" placeholder="https:// 或 /uploadFiles/...">
                          </div>

                          <div class="col-12"><div class="hr-more">雙幣定價、PTS 折抵與庫存水位</div><hr class="my-1"></div>

                          <div class="col-md-3">
                            <label class="form-label small fw-bold">台幣參考定價 (NT$) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="merch_price_twd" name="merch_price_twd" min="0" step="1" required
                                   value="<?php echo (int)($res['merch_price_twd'] ?? 0); ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">美金定價 (USD)</label>
                            <input type="number" class="form-control" id="merch_price_usd" name="merch_price_usd" min="0" step="0.01"
                                   value="<?php echo number_format((float)($res['merch_price_usd'] ?? 0), 2, '.', ''); ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">美金特價 (USD，留空=無特價)</label>
                            <input type="number" class="form-control" name="merch_sale_usd" min="0" step="0.01"
                                   value="<?php echo ($res['merch_sale_usd'] !== null) ? number_format((float)$res['merch_sale_usd'], 2, '.', '') : ''; ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">單件最高可折抵 PTS 上限</label>
                            <input type="number" class="form-control" name="merch_pts_limit" min="0" step="1"
                                   value="<?php echo (int)($res['merch_pts_limit'] ?? 0); ?>">
                          </div>

                          <div class="col-md-4">
                            <label class="form-label small fw-bold">庫存數量 (-1 代表數位無限量)</label>
                            <input type="number" class="form-control" id="merch_stock" name="merch_stock" min="-1" step="1"
                                   value="<?php echo (int)($res['merch_stock'] ?? 999); ?>">
                          </div>
                          <div class="col-md-8">
                            <label class="form-label small fw-bold">商品列表簡述 (merch_brief)</label>
                            <input type="text" class="form-control" name="merch_brief" maxlength="300"
                                   value="<?php echo htmlspecialchars($res['merch_brief'] ?? ''); ?>" placeholder="顯示於商品卡片與搜尋摘要的簡短介紹">
                          </div>
                        </div>
                      </div>

                      <!-- Tab 2: 商品 6 張主圖槽位網格管理 (直接檔名+索引號，不寫入資料庫) -->
                      <div class="tab-pane fade" id="tabMenu2" role="tabpanel">
                        <div class="alert alert-light border small d-flex justify-content-between align-items-center mb-3">
                          <div>
                            <i class="bi bi-info-circle-fill text-warning me-1"></i>
                            圖片直接依檔名規則 <code>mimg_<?php echo $merchID ?: '{ID}'; ?>_1_.jpg</code> ～ <code>_6_.jpg</code> 儲存於伺服器，<strong>第 1 張為主封面圖</strong>，2～6 張為商品細節與多角度展示圖。
                          </div>
                        </div>

                        <div class="row g-3">
                          <?php for ($idx = 1; $idx <= 6; $idx++):
                            $slot = $merchImages[$idx];
                          ?>
                          <div class="col-6 col-md-4">
                            <div class="merch-slot-card <?php echo $slot['exists'] ? 'has-img' : ''; ?>" id="merchSlotBox_<?php echo $idx; ?>" data-slot="<?php echo $idx; ?>">
                              <span class="merch-slot-badge <?php echo ($idx === 1) ? 'is-main' : ''; ?>">
                                <?php echo ($idx === 1) ? '主圖 #1 (封面)' : '展示圖 #' . $idx; ?>
                              </span>
                              <?php if ($slot['exists']): ?>
                                <button type="button" class="merch-slot-del btn-del-merch-img" data-slot="<?php echo $idx; ?>" title="刪除此張圖片">
                                  <i class="bi bi-trash3"></i>
                                </button>
                              <?php endif; ?>
                              <img id="merchSlotImg_<?php echo $idx; ?>" src="<?php echo $slot['src']; ?>" alt="Slot <?php echo $idx; ?>">
                              <div class="merch-slot-caption">
                                <i class="bi bi-camera me-1"></i>點擊上傳 / 裁切替換 (800×800)
                              </div>
                            </div>
                          </div>
                          <?php endfor; ?>
                        </div>
                        <input type="file" id="merchSlotFileInput" class="d-none notSubmit" accept="image/*">
                      </div>

                      <!-- Tab 3: SunEditor 詳細圖文介紹 (聯動圖像資料庫) -->
                      <div class="tab-pane fade" id="tabMenu3" role="tabpanel">
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                          <label class="form-label small fw-bold mb-0">商品詳細介紹頁面 HTML 內容 (merch_body)</label>
                          <span class="small text-muted"><i class="bi bi-image me-1"></i>支援直接從「圖像資料庫」選取或上傳插入圖片</span>
                        </div>
                        <textarea id="merch_body" name="merch_body" class="form-control" rows="16"><?php echo htmlspecialchars($res['merch_body'] ?? ''); ?></textarea>
                      </div>
                    </div>
                  </form>

                  <?php if ($merchID > 0): ?>
                  <!-- Tab 4: 商品歷史銷售紀錄與營收統計 -->
                  <div class="tab-pane fade" id="tabMenu4" role="tabpanel">
                    <div class="row g-2 mb-3">
                      <div class="col-md-4">
                        <div class="p-2 px-3 bg-light border rounded-3 d-flex justify-content-between align-items-center">
                          <span class="small text-muted">有效成交筆數</span>
                          <strong class="fs-6 text-dark"><?php echo count($salesRows); ?> 筆</strong>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="p-2 px-3 bg-light border rounded-3 d-flex justify-content-between align-items-center">
                          <span class="small text-muted">有效銷售總件數</span>
                          <strong class="fs-6 text-primary"><?php echo number_format($totalSalesQty); ?> 件</strong>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="p-2 px-3 bg-light border rounded-3 d-flex justify-content-between align-items-center">
                          <span class="small text-muted">有效銷售總營收</span>
                          <strong class="fs-6 text-success">NT$ <?php echo number_format($totalSalesAmt); ?></strong>
                        </div>
                      </div>
                    </div>

                    <div class="table-responsive">
                      <table id="dataTable_merchsales" class="table table-striped table-sm table-bordered table-hover align-middle w-100 small" data-search="1">
                        <thead class="table-light text-nowrap">
                          <tr>
                            <th>訂單編號</th>
                            <th>購買會員</th>
                            <th class="text-end">成交單價</th>
                            <th class="text-center">數量</th>
                            <th class="text-end">小計金額</th>
                            <th>電子發票</th>
                            <th class="text-center">訂單狀態</th>
                            <th>下單時間</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $ordStaBadges = array(
                            1 => '<span class="badge text-bg-warning">待付款</span>',
                            2 => '<span class="badge text-bg-info">已付款待出貨</span>',
                            3 => '<span class="badge text-bg-primary">已出貨配送中</span>',
                            4 => '<span class="badge text-bg-success">已完成</span>',
                            5 => '<span class="badge text-bg-danger">退費申請中</span>',
                            6 => '<span class="badge text-bg-secondary">已退費/取消</span>'
                          );
                          foreach ($salesRows as $sr):
                            $decBuyer = !empty($sr['mb_name']) ? _fn_secure_crypto($sr['mb_name'], 'de') : '會員 #' . $sr['mb_id'];
                            $decEmail = !empty($sr['mb_email']) ? _fn_secure_crypto($sr['mb_email'], 'de') : '';
                            $oSta     = (int)$sr['ord_status'];
                          ?>
                          <tr>
                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($sr['ord_no']); ?></td>
                            <td>
                              <a href="mb_detail.php?currID=<?php echo (int)$sr['mb_id']; ?>" class="fw-bold text-decoration-none"><?php echo htmlspecialchars($decBuyer); ?></a>
                              <div class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($decEmail); ?></div>
                            </td>
                            <td class="text-end">NT$ <?php echo number_format((float)$sr['ordi_unit_price']); ?></td>
                            <td class="text-center fw-bold"><?php echo (int)$sr['ordi_qty']; ?></td>
                            <td class="text-end fw-bold text-success">NT$ <?php echo number_format((float)$sr['ordi_subtotal']); ?></td>
                            <td><?php echo !empty($sr['inv_no']) ? htmlspecialchars($sr['inv_no']) : '<span class="text-muted">未開立</span>'; ?></td>
                            <td class="text-center"><?php echo $ordStaBadges[$oSta] ?? '<span class="badge text-bg-secondary">未知</span>'; ?></td>
                            <td class="text-muted"><?php echo $sr['ord_created'] ?? '-'; ?></td>
                          </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
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
  <!-- 6 張商品主圖專用 Cropper 裁切視窗 -->
  <div class="modal fade" id="merchCropModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-dark text-white py-2">
          <h6 class="modal-title"><i class="bi bi-crop me-2 text-warning"></i>裁切商品圖片（槽位 #<span id="cropSlotLabel">1</span> • 800×800）</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-2 bg-light text-center" style="max-height: 70vh; overflow: hidden;">
          <img id="merchCropTargetImg" src="" style="max-width: 100%; max-height: 62vh; display: block; margin: 0 auto;" alt="Crop Preview">
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
          <button type="button" class="btn btn-warning btn-sm fw-bold" id="btnConfirmMerchCrop">
            <i class="bi bi-check2-circle me-1"></i>確認裁切並暫存
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <script src="../pluginFiles/datatables3/datatables.min.js"></script>
  <script src="../pluginFiles/cropper/cropper.js"></script>
  <!-- SunEditor 視覺化 HTML 編輯器與繁體中文語系 -->
  <script src="../pluginFiles/suneditor/suneditor.min.js"></script>
  <script src="../pluginFiles/suneditor/lang/zh_tw.js"></script>
  <script src="../scriptFiles/adminlte.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

  <script type="text/javascript">
  let merchSunEditor = null;
  let activeSlotIdx  = 1;
  let merchCropper   = null;
  const pendingSlotImages = {}; // 暫存各槽位 (1~6) 裁切後的 Base64 圖片

  function validateBS5Form($form) {
    if (!$form || $form.length === 0) return true;
    const formEl = $form[0];
    const isValid = formEl.checkValidity();
    formEl.classList.add('was-validated');
    return isValid;
  }

  function doReDirect() { window.history.back(); }

  function doButSave() {
    // 儲存前先將 SunEditor 編輯器內容同步回 textarea (#merch_body)
    if (merchSunEditor) {
      $('#merch_body').val(merchSunEditor.getContents());
    }
    var frm = $('#form_merchMaster');
    if (!validateBS5Form(frm)) {
      // 若有必填欄位在 Tab 1 沒填，自動切回 Tab 1 提示
      const tab1El = document.querySelector('a[href="#tabMenu1"]');
      if (tab1El) bootstrap.Tab.getOrCreateInstance(tab1El).show();
      window.parent.toastr['warning']('請檢查標示紅框的必填或格式錯誤欄位（如商品貨號與名稱）！');
      return false;
    }
    ajaxPOST(frm);
  }

  function doButDelete() {
    if (0 == parseInt('<?php echo $merchID; ?>')) return false;
    $.post('../funcFiles/merch_act_func.php', {
      'formAcce': 'func_merch_delete',
      'token': getTokenVal(),
      'merchID': '<?php echo $merchID; ?>'
    }, function(data) {
      if (data['RTN'] == 1) {
        window.parent.toastr['success'](data['msgStr']);
        window.location.replace("merch_list.php");
      } else {
        window.parent.toastr['error'](data['msgStr'] || '刪除失敗');
      }
    }, 'json');
  }

  function doButReset() { location.reload(true); }

  function ajaxPOST(frm) {
    var $frm = frm || $('#form_merchMaster');
    var accE = (0 == parseInt('<?php echo $merchID; ?>')) ? 'func_merch_addnew' : $frm.data('func');
    var datA = getFormObjects($frm);
    // 確保 merch_body 帶入 SunEditor 最新 HTML
    if (merchSunEditor) {
      datA['merch_body'] = merchSunEditor.getContents();
    }

    $.ajax({
      type: 'post',
      url: '../funcFiles/merch_act_func.php',
      data: {
        'arrDatA': datA,
        'formAcce': accE,
        'slotImages': pendingSlotImages,
        'token': getTokenVal(),
        'merchID': '<?php echo $merchID; ?>'
      },
      dataType: 'json',
      success: function(data) {
        if (accE == 'func_merch_addnew' && data['RTN'] == 1) {
          window.parent.toastr['success'](data['msgStr']);
          window.location.replace("merch_detail.php?currID=" + data['newID']);
          return false;
        }
        if (window.parent && window.parent.toastr) {
          window.parent.toastr[data['msgType']](data['msgStr']);
        }
        if (data['RTN'] == 1) {
          $frm.removeClass('was-validated');
          // 清空已上傳的暫存 Base64
          Object.keys(pendingSlotImages).forEach(k => delete pendingSlotImages[k]);

          $('#sideMerchTitle').text($('#merch_title').val() || '未命名商品');
          $('#sideMerchSku').text($('#merch_sku').val() ? 'SKU: ' + $('#merch_sku').val().toUpperCase() : '尚未設定貨號');
          $('#sidePriceTwd').text('NT$ ' + Number($('#merch_price_twd').val() || 0).toLocaleString());
          $('#sidePriceUsd').text('USD $' + Number($('#merch_price_usd').val() || 0).toFixed(2));
          const stk = parseInt($('#merch_stock').val(), 10);
          $('#sideStockVal').text(stk < 0 ? '無限量 (數位)' : stk.toLocaleString() + ' 件');

          const stVal = $('#merch_status').val();
          $('#sideMerchStatus')
            .text($('#merch_status option:selected').text().replace(/^\d+\s*-\s*/, ''))
            .toggleClass('text-success fw-bold', stVal === '1')
            .toggleClass('text-danger', stVal !== '1');
          if (data['upTime']) $('#sideMerchUpdated').text(data['upTime']);
          if (typeof doSerialize === 'function') doSerialize();
        }
      },
      error: function() {
        if (window.parent && window.parent.toastr) {
          window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！');
        }
      }
    });
  }

  $(document).ready(function() {
    if ($('#dataTable_merchsales').length) {
      datatableCreate('dataTable_merchsales');
    }
    // 1. 初始化 SunEditor（聯動圖像資料庫 imageGalleryUrl）
    if (typeof SUNEDITOR !== 'undefined' && document.getElementById('merch_body')) {
      merchSunEditor = SUNEDITOR.create(document.getElementById('merch_body'), {
        lang: SUNEDITOR_LANG['zh_tw'],
        width: '100%',
        height: '420px',
        imageGalleryUrl: '../funcFiles/img_gallery_api.php',
        buttonList: [
          ['undo', 'redo'],
          ['font', 'fontSize', 'formatBlock'],
          ['bold', 'underline', 'italic', 'strike'],
          ['fontColor', 'hiliteColor'],
          ['removeFormat'],
          ['outdent', 'indent', 'align', 'horizontalRule', 'list', 'table'],
          ['link', 'image', 'imageGallery', 'video'],
          ['fullScreen', 'showBlocks', 'codeView']
        ]
      });

      // 當內容異動時點亮頂部儲存按鈕
      merchSunEditor.onChange = function() {
        if (window.parent && window.parent.$) {
          window.parent.$('#topBut_save').addClass('active');
        }
      };
    }

    // 2. 點擊 6 張主圖任一槽位開啟選檔
    $(document).on('click', '.merch-slot-card', function(e) {
      if ($(e.target).closest('.btn-del-merch-img').length) return;
      activeSlotIdx = parseInt($(this).data('slot'), 10) || 1;
      $('#merchSlotFileInput').val('').trigger('click');
    });

    // 選取圖片後開啟 Cropper 裁切視窗
    $('#merchSlotFileInput').on('change', function(e) {
      const files = e.target.files;
      if (!files || !files.length) return;
      const reader = new FileReader();
      reader.onload = function(evt) {
        $('#cropSlotLabel').text(activeSlotIdx);
        $('#merchCropTargetImg').attr('src', evt.target.result);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('merchCropModal')).show();
      };
      reader.readAsDataURL(files[0]);
    });

    // Modal 開啟時初始化 Cropper (1:1 正方形 800x800)
    $('#merchCropModal').on('shown.bs.modal', function() {
      const imgEl = document.getElementById('merchCropTargetImg');
      if (merchCropper) merchCropper.destroy();
      merchCropper = new Cropper(imgEl, {
        aspectRatio: 1,
        viewMode: 1,
        autoCropArea: 0.92
      });
    }).on('hidden.bs.modal', function() {
      if (merchCropper) {
        merchCropper.destroy();
        merchCropper = null;
      }
    });

    // 確認裁切並暫存至 pendingSlotImages[activeSlotIdx]
    $('#btnConfirmMerchCrop').on('click', function() {
      if (!merchCropper) return;
      const canvas = merchCropper.getCroppedCanvas({ width: 800, height: 800 });
      const base64 = canvas.toDataURL('image/jpeg', 0.9);
      pendingSlotImages[activeSlotIdx] = base64;

      $('#merchSlotImg_' + activeSlotIdx).attr('src', base64);
      $('#merchSlotBox_' + activeSlotIdx).addClass('has-img');
      if (activeSlotIdx === 1) {
        $('#sideMainCover').attr('src', base64);
      }
      if (window.parent && window.parent.$) {
        window.parent.$('#topBut_save').addClass('active');
      }
      bootstrap.Modal.getInstance(document.getElementById('merchCropModal')).hide();
      window.parent.toastr['info'](`展示圖 #${activeSlotIdx} 已裁切暫存，請記得點擊頂部「儲存」寫入伺服器！`);
    });

    // 3. 刪除單張已上傳的商品主圖
    $(document).on('click', '.btn-del-merch-img', function(e) {
      e.stopPropagation();
      const slot = parseInt($(this).data('slot'), 10) || 0;
      if (!confirm(`確定要刪除商品圖片 #${slot} 嗎？`)) return;

      // 若只是尚未儲存的暫存圖，直接清除畫面即可
      if (pendingSlotImages[slot]) {
        delete pendingSlotImages[slot];
        $('#merchSlotImg_' + slot).attr('src', '../imageFiles/nopic_200x200.jpg');
        $('#merchSlotBox_' + slot).removeClass('has-img');
        return;
      }

      $.post('../funcFiles/merch_act_func.php', {
        'formAcce': 'func_merch_delImage',
        'token': getTokenVal(),
        'merchID': '<?php echo $merchID; ?>',
        'slot': slot
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) {
          $('#merchSlotImg_' + slot).attr('src', '../imageFiles/nopic_200x200.jpg');
          $('#merchSlotBox_' + slot).removeClass('has-img');
          $(`.btn-del-merch-img[data-slot="${slot}"]`).remove();
          if (slot === 1) $('#sideMainCover').attr('src', '../imageFiles/nopic_200x200.jpg');
        }
      }, 'json');
    });

    $('li.prevLink').on('click', function() { window.location.replace(getCurrentURI('', 'merch_list.php')); });
    $('body').on('keypress click', function() {
      let timerElm = window.parent.$('#timer_on_exhausted');
      if (timerElm.length) timerElm.backward_timer('reset');
    });

    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
      window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  });
  </script>
</body>
</html>
