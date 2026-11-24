<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 定義本頁面代碼（對應左側樹狀導航列與頂部操作面板）
define('PAGE_MARK', 'PG_GIFT');
define('PAGE_ITEM', 'ITEM_MERCH');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$giftID = currID;
$res    = ($giftID > 0) ? DB->row("SELECT * FROM fsl_gifts WHERE gift_id = ? AND gift_status != 9", array($giftID)) : array();
if (!is_array($res)) $res = array();

// 解析已關聯的商品 ID 陣列 (gift_merch_ids: 逗號分隔)
$selectedMerchIds = (!empty($res['gift_merch_ids'])) ? array_map('trim', explode(',', $res['gift_merch_ids'])) : array();

// 撈取所有可作為贈品的商品清單 (merch_is_gift IN (1, 2) 或全部上架商品)
$merchOptions = DB->query("
  SELECT merch_id, merch_sku, merch_title, merch_type, merch_is_gift, merch_stock
  FROM fsl_merchandise
  WHERE merch_status = 1
  ORDER BY merch_is_gift DESC, merch_id DESC
");
if (!is_array($merchOptions)) $merchOptions = array();

// 展示圖設定
if (!empty($giftID) && file_exists("../uploadFiles/gift/gimg_" . $giftID . "_.jpg")) {
  $cropImgDisplay = 'show';
  $mainimgpath    = '../uploadFiles/gift/gimg_' . $giftID . '_.jpg?' . time();
  $mainimgchk     = 'gimg_' . $giftID . '_.jpg';
} else {
  $cropImgDisplay = 'hide';
  $mainimgpath    = '../imageFiles/nopic_user_200x200.jpg';
  $mainimgchk     = '';
}

// 查詢本禮品的會員領收與寄送流水紀錄 (fsl_gift_records)
$recordRows  = array();
$claimedCnt  = 0;
$memberOpts  = array();
$ruleRows = array();
if ($giftID > 0) {
  $recordRows = DB->query("
    SELECT r.*, m.mb_name, m.mb_email, m.mb_cellphone
    FROM fsl_gift_records r
    LEFT JOIN fsl_members m ON r.mb_id = m.mb_id
    WHERE r.gift_id = ?
    ORDER BY r.grec_id DESC
    LIMIT 200
  ", array($giftID));
  if (!is_array($recordRows)) $recordRows = array();

  foreach ($recordRows as $rr) {
    if (in_array((int)$rr['grec_status'], array(1, 2, 3), true)) $claimedCnt++;
  }

  // 撈取有效會員供後台主動派發選擇
  $mList = DB->query("SELECT mb_id, mb_name, mb_email FROM fsl_members WHERE mb_status = 1 ORDER BY mb_id DESC LIMIT 300");
  if (is_array($mList)) {
    foreach ($mList as $ml) {
      $memberOpts[] = array(
        'mb_id' => (int)$ml['mb_id'],
        'label' => '#' . $ml['mb_id'] . ' - ' . _fn_secure_crypto($ml['mb_name'], 'de') . ' (' . _fn_secure_crypto($ml['mb_email'], 'de') . ')'
      );
    }
  }

  // 查詢本禮品的領收資格條件規則 (erule_target_type = 2)
  $ruleRows = DB->query("
    SELECT * FROM fsl_eligibility_rules
    WHERE erule_target_type = 2 AND erule_target_id = ?
    ORDER BY erule_sort ASC, erule_id ASC
  ", array($giftID));
  if (!is_array($ruleRows)) $ruleRows = array();  
}

$typeLabels = array(1 => '純紅利點數', 2 => '商品贈品 (實體/數位)', 3 => '複合大禮包 (點數+商品)', 4 => '課程折抵券');
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
  <title>Fifth Season Life 人生第五季 | 禮品優惠券與派發管理</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">

<style>
  .gift-cover-area.image_area label[for^="cropUploadImage"] {
    border-radius: 12px !important;
    border: 1px solid #dee2e6;
  }
  .gift-cover-area.image_area label[for^="cropUploadImage"] img.imgEDicon {
    border-radius: 12px !important;
  }
  .merch-pick-box {
    max-height: 210px;
    overflow-y: auto;
    background-color: #ffffff;
  }
</style>
</head>
<body class="bg-body-tertiary theme-red">
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                <?php echo !empty($giftID) ? "編輯" : "新增"; ?>禮品與優惠券方案
                <span class="fs-5 text-danger">【ID: <?php echo $giftID; ?>】</span>
              </h4>
              <h6 class="m-0 fw-light text-muted">設定點數獎勵、課程折抵券、關聯商品禮包、對象限制與派發寄送追蹤</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">商品銷售管理</li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回列表">禮品優惠券管理</li>
                <li class="breadcrumb-item active"><?php echo !empty($giftID) ? "禮品方案詳情" : "新增禮品"; ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <!-- 左側禮品展示圖與發放統計卡 -->
            <div class="col-md-3 mb-3">
              <div class="card card-danger card-outline shadow-lg">
                <div class="card-body box-profile">
                  <div class="text-center">
                    <div class="image_area gift-cover-area">
                      <i class="nav-icon bi bi-trash3 fs-4 cropImgTrash <?php echo $cropImgDisplay; ?>" title="刪除圖像"></i>
                      <form method="post">
                        <label for="cropUploadImage">
                          <img id="avatarImage" name="avatarImage" class="img-fluid imgEDicon" src="<?php echo $mainimgpath; ?>" width="200" alt="禮品展示圖" title="禮品展示圖"/>
                          <div class="overlay" style="border-radius: 0 0 12px 12px;">
                            <div class="overtext"><?php echo !empty($giftID) ? "更改" : "新增"; ?>禮品圖示</div>
                          </div>
                          <input type="file" name="image" class="image d-none" id="cropUploadImage" crop-width="400" crop-height="400">
                        </label>
                      </form>
                      <?php include('include_avatarmodal.php'); ?>
                    </div>
                  </div>
                  <h4 class="profile-username text-center mt-3 fs-5" id="sideGiftTitle"><?php echo !empty($res['gift_title']) ? htmlspecialchars($res['gift_title']) : ($giftID > 0 ? '未命名禮品' : '新建立禮品方案'); ?></h4>
                  <p class="text-danger text-center small fw-bold mb-3" id="sideGiftType"><?php echo $typeLabels[(int)($res['gift_type'] ?? 1)] ?? '純紅利點數'; ?></p>

                  <ul class="list-group list-group-flush small">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>啟用狀態</b>
                      <span id="sideGiftStatus" class="<?php echo ((int)($res['gift_status'] ?? 1) === 1) ? 'text-success fw-bold' : 'text-secondary'; ?>">
                        <?php echo ((int)($res['gift_status'] ?? 1) === 1) ? '啟用發放中' : '停用關閉'; ?>
                      </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>禮品代碼</b>
                      <code id="sideGiftCode"><?php echo !empty($res['gift_code']) ? htmlspecialchars($res['gift_code']) : '-'; ?></code>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>累計已發放</b>
                      <span class="fw-bold text-primary" id="sideIssuedCnt"><?php echo number_format((int)($res['gift_issued_count'] ?? 0)); ?> 份</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>已拆封 / 領取</b>
                      <span class="fw-bold text-success"><?php echo number_format($claimedCnt); ?> 份</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>最後更新</b>
                      <span class="text-muted" id="sideGiftUpdated"><?php echo $res['gift_updated'] ?? '-'; ?></span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <!-- 右側多分頁管理區 -->
            <div class="col-md-9">
              <div class="card shadow-lg modern-card-tabs">
                <div class="card-header p-2">
                  <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                      <a class="nav-link active" href="#tabMenu1" data-index="1" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-gift me-1"></i>禮品規則與獎勵內容設定
                      </a>
                    </li>
                    <?php if ($giftID > 0): ?>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu2" data-index="2" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-send-check me-1"></i>主動派發與領收寄送紀錄 (<?php echo count($recordRows); ?>)
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu3" data-index="3" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-shield-check me-1"></i>領收條件規則 (<?php echo count($ruleRows); ?>)
                      </a>
                    </li>
                    <?php endif; ?>

                  </ul>
                </div>

                <div class="card-body bg-opacity-50">
                  <div class="tab-content">
                    <!-- Tab 1: 禮品規則、點數/折抵與多選關聯商品 -->
                    <div class="tab-pane fade show active" id="tabMenu1" role="tabpanel">
                      <form id="form_tabMenu1" action="../funcFiles/gift_act_func.php" method="post" data-func="func_gift_infoUpdate" novalidate>
                        <input type="hidden" name="mainimgchk" id="mainimgchk" class="notSubmit" value="<?php echo $mainimgchk; ?>">
                        <div class="row g-3">
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">禮品調用代碼 (gift_code)</label>
                            <input type="text" class="form-control text-uppercase" id="gift_code" name="gift_code"
                                   value="<?php echo htmlspecialchars($res['gift_code'] ?? ''); ?>" placeholder="如：WELCOME_2026">
                          </div>
                          <div class="col-md-5">
                            <label class="form-label small fw-bold">禮品名稱 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="gift_title" name="gift_title" required
                                   value="<?php echo htmlspecialchars($res['gift_title'] ?? ''); ?>" placeholder="如：第五季向光啟程禮">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">啟用狀態</label>
                            <select class="form-select" id="gift_status" name="gift_status">
                              <option value="1" <?php echo ((int)($res['gift_status'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 啟用發放中</option>
                              <option value="0" <?php echo ((int)($res['gift_status'] ?? 1) === 0) ? 'selected' : ''; ?>>0 - 停用關閉</option>
                            </select>
                          </div>

                          <div class="col-md-4">
                            <label class="form-label small fw-bold">禮品類型 (gift_type)</label>
                            <select class="form-select" id="gift_type" name="gift_type">
                              <option value="1" <?php echo ((int)($res['gift_type'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 純紅利點數 (PTS)</option>
                              <option value="2" <?php echo ((int)($res['gift_type'] ?? 1) === 2) ? 'selected' : ''; ?>>2 - 商品贈品 (實體或數位)</option>
                              <option value="3" <?php echo ((int)($res['gift_type'] ?? 1) === 3) ? 'selected' : ''; ?>>3 - 複合大禮包 (點數+商品)</option>
                              <option value="4" <?php echo ((int)($res['gift_type'] ?? 1) === 4) ? 'selected' : ''; ?>>4 - 課程折抵券 (USD)</option>
                            </select>
                          </div>
                          <div class="col-md-8">
                            <label class="form-label small fw-bold">禮品副標說明</label>
                            <input type="text" class="form-control" name="gift_subtitle"
                                   value="<?php echo htmlspecialchars($res['gift_subtitle'] ?? ''); ?>" placeholder="顯示於會員禮品卡片上的溫馨說明">
                          </div>

                          <div class="col-12"><div class="hr-more">獎勵內容配置（點數 / 折抵金 / 關聯商品多選）</div><hr class="my-1"></div>

                          <div class="col-md-4">
                            <label class="form-label small fw-bold">內含紅利點數 (PTS)</label>
                            <input type="number" class="form-control" name="gift_pts" min="0" step="1"
                                   value="<?php echo (int)($res['gift_pts'] ?? 0); ?>">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">折抵金額 (USD，折價券用)</label>
                            <input type="number" class="form-control" name="gift_discount_usd" min="0" step="0.01"
                                   value="<?php echo number_format((float)($res['gift_discount_usd'] ?? 0), 2, '.', ''); ?>">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">領取後有效天數 (0=永久)</label>
                            <input type="number" class="form-control" name="gift_valid_days" min="0" step="1"
                                   value="<?php echo (int)($res['gift_valid_days'] ?? 30); ?>">
                          </div>

                          <!-- 多選關聯商品 (gift_merch_ids) -->
                          <div class="col-12">
                            <label class="form-label small fw-bold d-block">
                              關聯商品與贈品 (可複選，自動組合為 <code>gift_merch_ids</code>)
                            </label>
                            <div class="border rounded-2 p-2 merch-pick-box">
                              <?php if (empty($merchOptions)): ?>
                                <div class="text-muted small py-2 text-center">目前尚無上架商品可關聯</div>
                              <?php else: foreach ($merchOptions as $mo):
                                $midStr = (string)$mo['merch_id'];
                                $isChk  = in_array($midStr, $selectedMerchIds, true) ? 'checked' : '';
                                $mTypeTag = ((int)$mo['merch_type'] === 2) ? '數位' : '實體';
                              ?>
                              <div class="form-check py-1 border-bottom">
                                <input class="form-check-input chk-gift-merch" type="checkbox" value="<?php echo $midStr; ?>" id="gm_<?php echo $midStr; ?>" <?php echo $isChk; ?>>
                                <label class="form-check-label small d-flex justify-content-between align-items-center" for="gm_<?php echo $midStr; ?>">
                                  <span>
                                    <code class="text-primary me-1">[#<?php echo $midStr; ?> <?php echo htmlspecialchars($mo['merch_sku'] ?? ''); ?>]</code>
                                    <strong><?php echo htmlspecialchars($mo['merch_title']); ?></strong>
                                    <span class="badge bg-light text-dark border ms-1"><?php echo $mTypeTag; ?></span>
                                  </span>
                                  <span class="text-muted" style="font-size: 0.72rem;">
                                    庫存：<?php echo ((int)$mo['merch_stock'] < 0) ? '無限' : $mo['merch_stock']; ?>
                                  </span>
                                </label>
                              </div>
                              <?php endforeach; endif; ?>
                            </div>
                            <input type="hidden" name="gift_merch_ids" id="gift_merch_ids" value="<?php echo htmlspecialchars($res['gift_merch_ids'] ?? ''); ?>">
                          </div>

                          <div class="col-12"><div class="hr-more">領取對象規則與發放名額上限</div><hr class="my-1"></div>

                          <div class="col-md-4">
                            <label class="form-label small fw-bold">領取對象規則</label>
                            <select class="form-select" id="gift_target_type" name="gift_target_type">
                              <option value="0" <?php echo ((int)($res['gift_target_type'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 不限 (憑活動/輪盤/任務領取)</option>
                              <option value="1" <?php echo ((int)($res['gift_target_type'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 限制最低會員等級</option>
                              <option value="2" <?php echo ((int)($res['gift_target_type'] ?? 0) === 2) ? 'selected' : ''; ?>>2 - 指定特定會員 ID 名單</option>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">最低會員等級門檻 (規則=1時)</label>
                            <select class="form-select" name="gift_target_minlvl">
                              <option value="0" <?php echo ((int)($res['gift_target_minlvl'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 無等級限制</option>
                              <option value="1" <?php echo ((int)($res['gift_target_minlvl'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 一般會員以上</option>
                              <option value="2" <?php echo ((int)($res['gift_target_minlvl'] ?? 0) === 2) ? 'selected' : ''; ?>>2 - 向光學員以上</option>
                              <option value="3" <?php echo ((int)($res['gift_target_minlvl'] ?? 0) === 3) ? 'selected' : ''; ?>>3 - 僅限 VIP 會員</option>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">總發放名額上限 (-1=無限量)</label>
                            <input type="number" class="form-control" name="gift_total_quota" min="-1" step="1"
                                   value="<?php echo (int)($res['gift_total_quota'] ?? -1); ?>">
                          </div>
                          <div class="col-12">
                            <label class="form-label small fw-bold">指定領取對象會員 ID (規則=2時生效，逗號分隔如 1,2,15)</label>
                            <input type="text" class="form-control" name="gift_target_mbids"
                                   value="<?php echo htmlspecialchars($res['gift_target_mbids'] ?? ''); ?>" placeholder="例如：1,5,12">
                          </div>
                        </div>
                      </form>
                    </div>
                    <!-- Tab 2: 主動派發禮品與會員領收物流追蹤 (fsl_gift_records) -->
                    <?php if ($giftID > 0): ?>
                    <div class="tab-pane fade" id="tabMenu2" role="tabpanel">
                      <form id="form_issue_gift" class="row g-2 align-items-end p-3 bg-light border rounded-3 mb-4">
                        <div class="col-md-5">
                          <label class="form-label small fw-bold">選擇派發對象會員 <span class="text-danger">*</span></label>
                          <select class="form-select form-select-sm" id="issueMbId" required>
                            <option value="">-- 請選擇有效會員 --</option>
                            <?php foreach ($memberOpts as $mo): ?>
                              <option value="<?php echo $mo['mb_id']; ?>"><?php echo htmlspecialchars($mo['label']); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">取得來源標記</label>
                          <select class="form-select form-select-sm" id="issueSourceType">
                            <option value="4">4 - 系統後台主動派發</option>
                            <option value="1">1 - 優惠卡片兌換</option>
                            <option value="3">3 - 完課小禮物</option>
                            <option value="5">5 - 任務獎勵補發</option>
                          </select>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small fw-bold">派發份數</label>
                          <input type="number" class="form-control form-control-sm" id="issueQty" min="1" max="10" value="1">
                        </div>
                        <div class="col-md-2 d-grid">
                          <button type="submit" class="btn btn-danger btn-sm fw-bold"><i class="bi bi-send-plus-fill me-1"></i>立即派發</button>
                        </div>
                      </form>

                      <div class="table-responsive">
                        <table id="dataTable_giftrec" class="table table-striped table-sm table-bordered table-hover align-middle w-100 small" data-search="1">
                          <thead class="table-light text-nowrap">
                            <tr>
                              <th>流水ID</th>
                              <th>領收會員</th>
                              <th class="text-center">來源</th>
                              <th class="text-center">領收狀態</th>
                              <th>到期日 / 領取時間</th>
                              <th>實體寄送物流備註 (grec_shipping_note)</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            $srcMap = array(1 => '優惠卡片', 2 => '幸運輪盤', 3 => '完課小禮', 4 => '系統派發', 5 => '任務獎勵');
                            $recStaMap = array(
                              0 => '<span class="badge text-bg-warning">0 - 待領取(未拆封)</span>',
                              1 => '<span class="badge text-bg-success">1 - 已領取/入帳</span>',
                              2 => '<span class="badge text-bg-primary">2 - 實體寄送中</span>',
                              3 => '<span class="badge text-bg-secondary">3 - 已使用完畢</span>',
                              9 => '<span class="badge text-bg-dark">9 - 已逾期失效</span>'
                            );
                            foreach ($recordRows as $rr):
                              $rid      = (int)$rr['grec_id'];
                              $decName  = !empty($rr['mb_name']) ? _fn_secure_crypto($rr['mb_name'], 'de') : '會員 #' . $rr['mb_id'];
                              $decEmail = !empty($rr['mb_email']) ? _fn_secure_crypto($rr['mb_email'], 'de') : '';
                              $rSrc     = (int)($rr['grec_source_type'] ?? 4);
                              $rSta     = (int)($rr['grec_status'] ?? 0);
                            ?>
                            <tr>
                              <td class="fw-bold">#<?php echo $rid; ?></td>
                              <td>
                                <a href="mb_detail.php?currID=<?php echo (int)$rr['mb_id']; ?>" class="fw-bold text-decoration-none"><?php echo htmlspecialchars($decName); ?></a>
                                <div class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($decEmail); ?></div>
                              </td>
                              <td class="text-center"><span class="badge bg-light text-dark border"><?php echo $srcMap[$rSrc] ?? '系統'; ?></span></td>
                              <td class="text-center"><?php echo $recStaMap[$rSta] ?? $recStaMap; ?></td>
                              <td style="font-size: 0.74rem;">
                                <div>派發：<?php echo substr($rr['grec_created'], 0, 16); ?></div>
                                <div class="text-muted">到期：<?php echo !empty($rr['grec_expired_at']) ? substr($rr['grec_expired_at'], 0, 10) : '永久有效'; ?></div>
                              </td>
                              <td>
                                <div class="input-group input-group-sm">
                                  <input type="text" class="form-control inp-ship-note" id="shipNote_<?php echo $rid; ?>"
                                         value="<?php echo htmlspecialchars($rr['grec_shipping_note'] ?? ''); ?>" placeholder="填寫物流單號或備註...">
                                  <button type="button" class="btn btn-outline-primary btn-save-ship" data-rid="<?php echo $rid; ?>" title="儲存並標記實體寄送中">
                                    <i class="bi bi-truck"></i>
                                  </button>
                                </div>
                              </td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <!-- Tab 3: 禮品領收與發送資格條件規則 (共用 fsl_eligibility_rules, target_type=2) -->
                    <div class="tab-pane fade" id="tabMenu3" role="tabpanel">
                      <form id="form_add_gift_rule" class="row g-2 align-items-end p-3 bg-light border rounded-3 mb-4">
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">條件判斷類型</label>
                          <select class="form-select form-select-sm" id="gRuleType">
                            <option value="gender">會員性別 (F / M)</option>
                            <option value="time_range">領取時間區間 (起,迄)</option>
                            <option value="birthday_month">當月壽星限定 (1)</option>
                            <option value="city">居住城市代碼 (如 01,02)</option>
                            <option value="mb_level">會員等級門檻 (1/2/3)</option>
                            <option value="total_spent">累計消費金額門檻</option>
                            <option value="id_last_digit">身分證尾數 (如 1,3,5,7,9)</option>
                          </select>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small fw-bold">比對運算子</label>
                          <select class="form-select form-select-sm" id="gRuleOp">
                            <option value="=">= (等於)</option>
                            <option value="IN">IN (包含於清單)</option>
                            <option value="BETWEEN">BETWEEN (區間)</option>
                            <option value=">=">&gt;= (大於等於)</option>
                            <option value="<=">&lt;= (小於等於)</option>
                            <option value="!=">!= (不等於)</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">條件值 <span class="text-danger">*</span></label>
                          <input type="text" class="form-control form-control-sm" id="gRuleVal" placeholder="如：1 或 2026-11-01 00:00:00,2026-12-31 23:59:59" required>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">不符條件提示警語 <span class="text-danger">*</span></label>
                          <input type="text" class="form-control form-control-sm" id="gRuleMsg" placeholder="顯示給會員的提示訊息" required>
                        </div>
                        <div class="col-md-1 d-grid">
                          <button type="submit" class="btn btn-danger btn-sm fw-bold" title="新增領收規則"><i class="bi bi-plus-lg"></i></button>
                        </div>
                      </form>

                      <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle w-100 small">
                          <thead class="table-light">
                            <tr>
                              <th>ID</th>
                              <th>條件類型</th>
                              <th>運算子</th>
                              <th>比對值</th>
                              <th>不符資格提示警語</th>
                              <th class="text-center" style="width: 70px;">刪除</th>
                            </tr>
                          </thead>
                          <tbody id="gRuleTbody">
                            <?php if (empty($ruleRows)): ?>
                              <tr><td colspan="6" class="text-center text-muted py-3">尚未設定額外領收限制條件（依 Tab 1 基本對象規則發放）</td></tr>
                            <?php else: foreach ($ruleRows as $rl):
                              $rid = (int)$rl['erule_id'];
                            ?>
                              <tr id="gRuleRow_<?php echo $rid; ?>">
                                <td><?php echo $rid; ?></td>
                                <td class="fw-bold text-danger"><?php echo htmlspecialchars($rl['erule_type']); ?></td>
                                <td><code><?php echo htmlspecialchars($rl['erule_operator']); ?></code></td>
                                <td><?php echo htmlspecialchars($rl['erule_val']); ?></td>
                                <td><?php echo htmlspecialchars($rl['erule_fail_msg']); ?></td>
                                <td class="text-center">
                                  <button type="button" class="btn btn-outline-danger btn-xs py-0 px-2 btn-del-gift-rule" data-rid="<?php echo $rid; ?>">
                                    <i class="bi bi-trash3"></i>
                                  </button>
                                </td>
                              </tr>
                            <?php endforeach; endif; ?>
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
      </div>
    </main>
  </div>

  <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <script src="../pluginFiles/datatables3/datatables.min.js"></script>
  <script src="../pluginFiles/cropper/cropper.js"></script>
  <script src="../scriptFiles/adminlte.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/fileupload.js<?php echo '?t='.time(); ?>"></script>
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

  <script type="text/javascript">
  function syncGiftMerchIds() {
    const ids = [];
    $('.chk-gift-merch:checked').each(function() { ids.push($(this).val()); });
    $('#gift_merch_ids').val(ids.join(','));
  }

  function validateBS5Form($form) {
    if (!$form || $form.length === 0) return true;
    const isValid = $form.checkValidity();
    $form.classList.add('was-validated');
    return isValid;
  }

  function doReDirect() { window.history.back(); }

  function doButSave() {
    syncGiftMerchIds();
    var frm = $('#form_' + $('.tab-pane.active').prop("id"));
    if (!frm.length) return false;
    if (!validateBS5Form(frm)) {
      window.parent.toastr['warning']('請檢查標示紅框的必填或格式錯誤欄位！');
      return false;
    }
    ajaxPOST(frm);
  }

  function doButDelete() {
    if (0 == parseInt('<?php echo $giftID; ?>')) return false;
    $.post('../funcFiles/gift_act_func.php', {
      'formAcce': 'func_gift_delete',
      'token': getTokenVal(),
      'giftID': '<?php echo $giftID; ?>'
    }, function(data) {
      if (data['RTN'] == 1) {
        window.parent.toastr['success'](data['msgStr']);
        window.location.replace("gift_list.php");
      } else {
        window.parent.toastr['error'](data['msgStr'] || '刪除失敗');
      }
    }, 'json');
  }

  function doButReset() { location.reload(true); }

  function ajaxPOST(frm) {
    var $frm = frm || $('#form_tabMenu1');
    var accE = (0 == parseInt('<?php echo $giftID; ?>')) ? 'func_gift_addnew' : $frm.data('func');
    var datA = getFormObjects($frm);
    var imgBase64 = (typeof cropImage64 !== 'undefined' && cropImage64 !== '')
                    ? cropImage64
                    : ($('#main_crop_area').data('cropImage64') || '');

    $.ajax({
      type: 'post',
      url: '../funcFiles/gift_act_func.php',
      data: {
        'arrDatA': datA,
        'formAcce': accE,
        'imgUp': imgBase64,
        'token': getTokenVal(),
        'giftID': '<?php echo $giftID; ?>'
      },
      dataType: 'json',
      success: function(data) {
        if (accE == 'func_gift_addnew' && data['RTN'] == 1) {
          window.parent.toastr['success'](data['msgStr']);
          window.location.replace("gift_detail.php?currID=" + data['newID']);
          return false;
        }
        if (window.parent && window.parent.toastr) {
          window.parent.toastr[data['msgType']](data['msgStr']);
        }
        if (data['RTN'] == 1) {
          $frm.removeClass('was-validated');
          $('#sideGiftTitle').text($('#gift_title').val() || '未命名禮品');
          $('#sideGiftType').text($('#gift_type option:selected').text().replace(/^\d+\s*-\s*/, ''));
          $('#sideGiftCode').text($('#gift_code').val() ? $('#gift_code').val().toUpperCase() : '-');
          const stVal = $('#gift_status').val();
          $('#sideGiftStatus')
            .text(stVal === '1' ? '啟用發放中' : '停用關閉')
            .toggleClass('text-success fw-bold', stVal === '1')
            .toggleClass('text-secondary', stVal !== '1');
          if (data['upTime']) $('#sideGiftUpdated').text(data['upTime']);
          cropImage64 = '';
          if (typeof doSerialize === 'function') doSerialize();
        }
      }
    });
  }

  $(document).ready(function() {
    if ($('#dataTable_giftrec').length) datatableCreate('dataTable_giftrec');

    $('.chk-gift-merch').on('change', function() {
      syncGiftMerchIds();
      if (window.parent && window.parent.$) {
        window.parent.$('#topBut_save').addClass('active');
      }
    });

    // 後台主動派發禮品給指定會員
    $('#form_issue_gift').on('submit', function(e) {
      e.preventDefault();
      const mbId = $('#issueMbId').val();
      const src  = $('#issueSourceType').val();
      const qty  = parseInt($('#issueQty').val(), 10) || 1;
      if (!mbId) {
        window.parent.toastr['warning']('請選擇要派發的會員！');
        return;
      }
      $.post('../funcFiles/gift_act_func.php', {
        'formAcce': 'func_gift_issue',
        'token': getTokenVal(),
        'giftID': '<?php echo $giftID; ?>',
        'mbID': mbId,
        'sourceType': src,
        'qty': qty
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 更新實體贈品寄送物流單號
    $(document).on('click', '.btn-save-ship', function() {
      const rid  = $(this).data('rid');
      const note = $('#shipNote_' + rid).val().trim();
      $.post('../funcFiles/gift_act_func.php', {
        'formAcce': 'func_gift_updateShipping',
        'token': getTokenVal(),
        'grecID': rid,
        'shippingNote': note
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1 && note !== '') location.reload(true);
      }, 'json');
    });

    // 新增 / 刪除禮品領收資格規則
    $('#form_add_gift_rule').on('submit', function(e) {
      e.preventDefault();
      $.post('../funcFiles/gift_act_func.php', {
        'formAcce': 'func_gift_rule_add',
        'token': getTokenVal(),
        'giftID': '<?php echo $giftID; ?>',
        'erule_type': $('#gRuleType').val(),
        'erule_operator': $('#gRuleOp').val(),
        'erule_val': $('#gRuleVal').val().trim(),
        'erule_fail_msg': $('#gRuleMsg').val().trim()
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    $(document).on('click', '.btn-del-gift-rule', function() {
      if (!confirm('確定刪除此禮品領收限制條件？')) return;
      const rid = $(this).data('rid');
      $.post('../funcFiles/gift_act_func.php', {
        'formAcce': 'func_gift_rule_delete',
        'token': getTokenVal(),
        'eruleID': rid
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) $('#gRuleRow_' + rid).fadeOut(250, function() { $(this).remove(); });
      }, 'json');
    });




    $('li.prevLink').on('click', function() { window.location.replace(getCurrentURI('', 'gift_list.php')); });
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
