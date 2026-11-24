<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 定義本頁面代碼（對應左側樹狀導航列與頂部操作面板）
define('PAGE_MARK', 'PG_ORDER');
define('PAGE_ITEM', 'ITEM_MERCH');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$ordID = currID;
$res   = ($ordID > 0) ? DB->row("
  SELECT o.*,
         m.mb_name, m.mb_email, m.mb_cellphone,
         i.inv_id, i.inv_no, i.inv_random_code, i.inv_type, i.inv_carrier_type, i.inv_carrier_code,
         i.inv_tax_id, i.inv_company_title, i.inv_pay_gateway, i.inv_paid_at, i.inv_issued_at, i.inv_voided_at, i.inv_status
  FROM fsl_orders o
  LEFT JOIN fsl_members m ON o.mb_id = m.mb_id
  LEFT JOIN fsl_invoices i ON o.ord_id = i.ord_id
  WHERE o.ord_id = ?
", array($ordID)) : array();
if (!is_array($res)) $res = array();

if (empty($res)) {
  echo "<script>alert('找不到該筆訂單資料！'); window.location.replace('ord_list.php');</script>";
  exit();
}

$mbID = (int)$res['mb_id'];
$decBuyerName  = !empty($res['mb_name']) ? _fn_secure_crypto($res['mb_name'], 'de') : '會員 #' . $mbID;
$decBuyerEmail = !empty($res['mb_email']) ? _fn_secure_crypto($res['mb_email'], 'de') : '';
$decBuyerPhone = !empty($res['mb_cellphone']) ? _fn_secure_crypto($res['mb_cellphone'], 'de') : '';

// 收件人加密欄位解密（若有填寫）
$decRecvName  = !empty($res['ord_receiver_name'])  ? _fn_secure_crypto($res['ord_receiver_name'], 'de')  : $decBuyerName;
$decRecvPhone = !empty($res['ord_receiver_phone']) ? _fn_secure_crypto($res['ord_receiver_phone'], 'de') : $decBuyerPhone;
$decRecvAddr  = !empty($res['ord_receiver_addr'])  ? _fn_secure_crypto($res['ord_receiver_addr'], 'de')  : '';

// 查詢訂單品項明細 (fsl_order_items)
$itemRows = DB->query("
  SELECT oi.*,
         c.course_title,
         m.merch_title, m.merch_sku, m.merch_type
  FROM fsl_order_items oi
  LEFT JOIN fsl_courses c ON (oi.ordi_type = 1 AND oi.course_id = c.course_id)
  LEFT JOIN fsl_merchandise m ON (oi.ordi_type = 2 AND oi.merch_id = m.merch_id)
  WHERE oi.ord_id = ?
  ORDER BY oi.ordi_id ASC
", array($ordID));
if (!is_array($itemRows)) $itemRows = array();

// 查詢售後退換貨申請紀錄 (fsl_refunds)
$refundRows = DB->query("SELECT * FROM fsl_refunds WHERE ord_id = ? ORDER BY ref_id DESC", array($ordID));
if (!is_array($refundRows)) $refundRows = array();

$ordStatusLabels = array(
  1 => array('text' => '1 - 待付款', 'cls' => 'text-warning-emphasis fw-bold'),
  2 => array('text' => '2 - 已付款處理中', 'cls' => 'text-info fw-bold'),
  3 => array('text' => '3 - 已出貨配送中', 'cls' => 'text-primary fw-bold'),
  4 => array('text' => '4 - 已完成', 'cls' => 'text-success fw-bold'),
  5 => array('text' => '5 - 售後退換申請中', 'cls' => 'text-danger fw-bold'),
  6 => array('text' => '6 - 已退費 / 取消', 'cls' => 'text-secondary fw-bold')
);
$currOrdSta = (int)($res['ord_status'] ?? 1);
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
  <title>Fifth Season Life 人生第五季 | 訂單與發票退換貨詳情</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>
<body class="bg-body-tertiary theme-gray">
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                訂單、發票與售後審核詳情
                <span class="fs-5 text-primary">【<?php echo htmlspecialchars($res['ord_no']); ?>】</span>
              </h4>
              <h6 class="m-0 fw-light text-muted">訂單品項明細、物流出貨單號、電子發票開立作廢與退換貨審核退款</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">商品銷售管理</li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回列表">訂單與發票退換貨管理</li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($res['ord_no']); ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <!-- 左側訂單財務與買家摘要卡片 -->
            <div class="col-md-3 mb-3">
              <div class="card card-dark card-outline shadow-lg">
                <div class="card-body">
                  <div class="text-center mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border text-dark mb-2" style="width: 64px; height: 64px;">
                      <i class="bi bi-receipt-cutoff fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($res['ord_no']); ?></h5>
                    <div class="small text-muted"><?php echo $res['ord_created']; ?></div>
                  </div>

                  <ul class="list-group list-group-flush small">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>訂單狀態</b>
                      <span id="sideOrdStatus" class="<?php echo $ordStatusLabels[$currOrdSta]['cls'] ?? ''; ?>">
                        <?php echo $ordStatusLabels[$currOrdSta]['text'] ?? '-'; ?>
                      </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>訂購會員</b>
                      <a href="mb_detail.php?currID=<?php echo $mbID; ?>" class="fw-bold text-decoration-none"><?php echo htmlspecialchars($decBuyerName); ?></a>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>商品小計</b>
                      <span>NT$ <?php echo number_format((float)$res['ord_subtotal']); ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>優惠券折抵</b>
                      <span class="text-danger">- NT$ <?php echo number_format((float)$res['ord_discount']); ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>PTS 點數折抵</b>
                      <span class="text-warning-emphasis">- <?php echo number_format((int)$res['ord_pts_used']); ?> PTS</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>實付總金額</b>
                      <span class="fw-bold text-success fs-6">NT$ <?php echo number_format((float)$res['ord_total']); ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>電子發票</b>
                      <span class="fw-bold text-dark"><?php echo !empty($res['inv_no']) ? htmlspecialchars($res['inv_no']) : '未開立'; ?></span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <!-- 右側多分頁訂單與財務處理區 -->
            <div class="col-md-9">
              <div class="card shadow-lg modern-card-tabs">
                <div class="card-header p-2">
                  <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                      <a class="nav-link active" href="#tabMenu1" data-index="1" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-box-seam me-1"></i>訂單品項明細與物流出貨
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu2" data-index="2" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-receipt me-1"></i>電子發票與載具憑證
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu3" data-index="3" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-arrow-return-left me-1"></i>售後退換貨審核 (<?php echo count($refundRows); ?>)
                      </a>
                    </li>
                  </ul>
                </div>

                <div class="card-body bg-opacity-50">
                  <div class="tab-content">
                    <!-- Tab 1: 訂單品項明細、收件人與物流出貨管理 -->
                    <div class="tab-pane fade show active" id="tabMenu1" role="tabpanel">
                      <h6 class="fw-bold mb-2"><i class="bi bi-list-check me-1 text-primary"></i>訂購品項清單</h6>
                      <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered align-middle w-100 small">
                          <thead class="table-light">
                            <tr>
                              <th class="text-center" style="width: 75px;">類型</th>
                              <th>品項名稱 / 貨號</th>
                              <th class="text-end">成交單價</th>
                              <th class="text-center">數量</th>
                              <th class="text-end">小計金額</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($itemRows as $it):
                              $iType = (int)$it['ordi_type'];
                              $iName = ($iType === 1) ? ($it['course_title'] ?? '課程方案') : ($it['merch_title'] ?? '商品項目');
                            ?>
                            <tr>
                              <td class="text-center">
                                <?php echo ($iType === 1) ? '<span class="badge text-bg-success">課程方案</span>' : '<span class="badge text-bg-warning">周邊商品</span>'; ?>
                              </td>
                              <td>
                                <span class="fw-bold"><?php echo htmlspecialchars($iName); ?></span>
                                <?php if (!empty($it['merch_sku'])): ?>
                                  <code class="ms-1 text-muted">[<?php echo htmlspecialchars($it['merch_sku']); ?>]</code>
                                <?php endif; ?>
                              </td>
                              <td class="text-end">NT$ <?php echo number_format((float)$it['ordi_unit_price']); ?></td>
                              <td class="text-center fw-bold"><?php echo (int)$it['ordi_qty']; ?></td>
                              <td class="text-end fw-bold">NT$ <?php echo number_format((float)$it['ordi_subtotal']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>

                      <form id="form_tabMenu1" action="../funcFiles/ord_act_func.php" method="post" data-func="func_ord_updateShipping" novalidate>
                        <div class="row g-3">
                          <div class="col-12"><div class="hr-more">收件人資訊與物流配送管理</div><hr class="my-1"></div>

                          <div class="col-md-4">
                            <label class="form-label small fw-bold">收件人姓名 (加密儲存)</label>
                            <input type="text" class="form-control" name="ord_receiver_name" value="<?php echo htmlspecialchars($decRecvName); ?>">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">收件人聯絡電話 (加密儲存)</label>
                            <input type="text" class="form-control" name="ord_receiver_phone" value="<?php echo htmlspecialchars($decRecvPhone); ?>">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">訂單處理與物流狀態</label>
                            <select class="form-select" id="ord_status" name="ord_status">
                              <option value="1" <?php echo ($currOrdSta === 1) ? 'selected' : ''; ?>>1 - 待付款</option>
                              <option value="2" <?php echo ($currOrdSta === 2) ? 'selected' : ''; ?>>2 - 已付款處理中</option>
                              <option value="3" <?php echo ($currOrdSta === 3) ? 'selected' : ''; ?>>3 - 已出貨配送中</option>
                              <option value="4" <?php echo ($currOrdSta === 4) ? 'selected' : ''; ?>>4 - 已完成</option>
                              <option value="5" <?php echo ($currOrdSta === 5) ? 'selected' : ''; ?>>5 - 售後退換申請中</option>
                              <option value="6" <?php echo ($currOrdSta === 6) ? 'selected' : ''; ?>>6 - 已退費 / 取消</option>
                            </select>
                          </div>

                          <div class="col-md-12">
                            <label class="form-label small fw-bold">宅配收件地址 (加密儲存)</label>
                            <input type="text" class="form-control" name="ord_receiver_addr" value="<?php echo htmlspecialchars($decRecvAddr); ?>">
                          </div>

                          <div class="col-md-4">
                            <label class="form-label small fw-bold">物流配送業者</label>
                            <input type="text" class="form-control" id="ord_shipping_carrier" name="ord_shipping_carrier"
                                   value="<?php echo htmlspecialchars($res['ord_shipping_carrier'] ?? ''); ?>" placeholder="如：黑貓宅急便 / 郵局 / 7-11店到店">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">物流追蹤單號 (Tracking No.)</label>
                            <input type="text" class="form-control" id="ord_tracking_no" name="ord_tracking_no"
                                   value="<?php echo htmlspecialchars($res['ord_tracking_no'] ?? ''); ?>" placeholder="輸入包裹追蹤碼">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">管理者內部處理備註</label>
                            <input type="text" class="form-control" name="ord_admin_note"
                                   value="<?php echo htmlspecialchars($res['ord_admin_note'] ?? ''); ?>" placeholder="內部出貨或客服備註">
                          </div>
                        </div>
                      </form>
                    </div>

                    <!-- Tab 2: 電子發票開立與作廢憑證管理 -->
                    <div class="tab-pane fade" id="tabMenu2" role="tabpanel">
                      <?php if (empty($res['inv_id'])): ?>
                        <div class="alert alert-warning small">
                          <i class="bi bi-exclamation-circle me-1"></i>此訂單尚未建立電子發票紀錄。
                        </div>
                      <?php else:
                        $iSta = (int)($res['inv_status'] ?? 0);
                      ?>
                        <div class="p-3 bg-light border rounded-3 mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                          <div>
                            <span class="small text-muted d-block">電子發票號碼與隨機碼</span>
                            <strong class="fs-5 text-dark"><?php echo !empty($res['inv_no']) ? htmlspecialchars($res['inv_no']) : '尚未開立號碼'; ?></strong>
                            <?php if (!empty($res['inv_random_code'])): ?>
                              <span class="ms-2 badge bg-white text-dark border">防偽隨機碼：<?php echo htmlspecialchars($res['inv_random_code']); ?></span>
                            <?php endif; ?>
                          </div>
                          <div class="d-flex gap-2">
                            <?php if ($iSta === 0): ?>
                              <button type="button" class="btn btn-success btn-sm fw-bold" id="btnIssueInvoice">
                                <i class="bi bi-receipt-cutoff me-1"></i>確認收款並立即開立發票
                              </button>
                            <?php elseif ($iSta === 1): ?>
                              <button type="button" class="btn btn-outline-danger btn-sm fw-bold" id="btnVoidInvoice">
                                <i class="bi bi-x-octagon me-1"></i>執行電子發票作廢 / 折讓
                              </button>
                            <?php endif; ?>
                          </div>
                        </div>

                        <div class="row g-3 small">
                          <div class="col-md-4">
                            <div class="p-3 border rounded-2 bg-white">
                              <span class="text-muted d-block">發票開立狀態</span>
                              <strong class="fs-6">
                                <?php
                                  if ($iSta === 1) echo '<span class="text-success">已正式開立</span>';
                                  elseif ($iSta === 2) echo '<span class="text-danger">已作廢 / 折讓</span>';
                                  else echo '<span class="text-warning-emphasis">待付款未開立</span>';
                                ?>
                              </strong>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="p-3 border rounded-2 bg-white">
                              <span class="text-muted d-block">支付管道與入帳時間</span>
                              <strong><?php echo htmlspecialchars($res['inv_pay_gateway'] ?? '-'); ?> • <?php echo $res['inv_paid_at'] ?? '尚未入帳'; ?></strong>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="p-3 border rounded-2 bg-white">
                              <span class="text-muted d-block">發票開立 / 作廢時間</span>
                              <strong><?php echo ($iSta === 2) ? ($res['inv_voided_at'] ?? '-') : ($res['inv_issued_at'] ?? '-'); ?></strong>
                            </div>
                          </div>
                          <div class="col-12">
                            <div class="p-3 border rounded-2 bg-white">
                              <span class="text-muted d-block mb-1">買受人發票類型與載具歸戶資訊</span>
                              <strong>
                                <?php
                                  if ((int)($res['inv_type'] ?? 1) === 2) {
                                    echo '三聯式公司發票｜統一編號：' . htmlspecialchars($res['inv_tax_id'] ?? '') . '｜抬頭：' . htmlspecialchars($res['inv_company_title'] ?? '');
                                  } elseif ((int)($res['inv_carrier_type'] ?? 0) === 1) {
                                    echo '二聯式個人電子發票｜手機條碼載具：' . htmlspecialchars($res['inv_carrier_code'] ?? '');
                                  } elseif ((int)($res['inv_carrier_type'] ?? 0) === 2) {
                                    echo '二聯式個人電子發票｜自然人憑證載具：' . htmlspecialchars($res['inv_carrier_code'] ?? '');
                                  } else {
                                    echo '二聯式個人電子發票｜人生第五季會員載具（系統自動託管兌獎）';
                                  }
                                ?>
                              </strong>
                            </div>
                          </div>
                        </div>
                      <?php endif; ?>
                    </div>
                    <!-- Tab 3: 售後退換貨審核與退款處理 (fsl_refunds) -->
                    <div class="tab-pane fade" id="tabMenu3" role="tabpanel">
                      <?php if (empty($refundRows)): ?>
                        <div class="text-center text-muted py-5 small">
                          <i class="bi bi-shield-check fs-2 d-block mb-2 text-success"></i>
                          此訂單目前無任何售後退費或換貨申請紀錄。
                        </div>
                      <?php else:
                        $refTypeMap = array(1 => '全額退費取消', 2 => '部分品項退費', 3 => '商品瑕疵換貨');
                        $refStaMap  = array(
                          1 => '<span class="badge text-bg-danger">1 - 待審核</span>',
                          2 => '<span class="badge text-bg-warning">2 - 審核通過退貨中</span>',
                          3 => '<span class="badge text-bg-success">3 - 已完成退款/換貨</span>',
                          4 => '<span class="badge text-bg-secondary">4 - 已駁回申請</span>'
                        );
                        foreach ($refundRows as $rf):
                          $rfId  = (int)$rf['ref_id'];
                          $rfSta = (int)$rf['ref_status'];
                      ?>
                        <div class="card border mb-3 shadow-sm">
                          <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                              <span class="fw-bold text-danger me-2"><?php echo htmlspecialchars($rf['ref_no']); ?></span>
                              <span class="badge bg-dark me-1"><?php echo $refTypeMap[(int)$rf['ref_type']] ?? '售後申請'; ?></span>
                              <?php echo $refStaMap[$rfSta] ?? ''; ?>
                            </div>
                            <span class="small text-muted">申請時間：<?php echo $rf['ref_created']; ?></span>
                          </div>
                          <div class="card-body small">
                            <div class="row g-3 mb-3">
                              <div class="col-md-4">
                                <span class="text-muted d-block">申請退費金額</span>
                                <strong class="fs-6 text-danger">NT$ <?php echo number_format((float)$rf['ref_amount']); ?></strong>
                              </div>
                              <div class="col-md-4">
                                <span class="text-muted d-block">退還折抵 PTS 點數</span>
                                <strong class="text-warning-emphasis"><?php echo number_format((int)$rf['ref_pts_return']); ?> PTS</strong>
                              </div>
                              <div class="col-md-4">
                                <span class="text-muted d-block">處理完成時間</span>
                                <strong><?php echo $rf['ref_processed_at'] ?? '尚未結案'; ?></strong>
                              </div>
                              <div class="col-12">
                                <span class="text-muted d-block">會員申請原因說明：</span>
                                <div class="p-2 bg-light border rounded-2 mt-1"><?php echo nl2br(htmlspecialchars($rf['ref_reason'] ?? '未填寫原因')); ?></div>
                              </div>
                            </div>

                            <div class="row g-2 align-items-end">
                              <div class="col-md-7">
                                <label class="form-label small fw-bold">審核回覆與客服備註 (將同步通知會員)</label>
                                <input type="text" class="form-control form-control-sm" id="refReply_<?php echo $rfId; ?>"
                                       value="<?php echo htmlspecialchars($rf['ref_admin_reply'] ?? ''); ?>" placeholder="輸入審核說明、退貨寄回地址或退款匯款資訊...">
                              </div>
                              <div class="col-md-5 d-flex flex-wrap justify-content-md-end gap-1">
                                <?php if ($rfSta === 1): ?>
                                  <button type="button" class="btn btn-warning btn-sm fw-bold btn-refund-act" data-rfid="<?php echo $rfId; ?>" data-act="approve">
                                    <i class="bi bi-check-lg me-1"></i>核准退換
                                  </button>
                                  <button type="button" class="btn btn-outline-secondary btn-sm btn-refund-act" data-rfid="<?php echo $rfId; ?>" data-act="reject">
                                    <i class="bi bi-x-lg me-1"></i>駁回
                                  </button>
                                <?php endif; ?>
                                <?php if (in_array($rfSta, array(1, 2), true)): ?>
                                  <button type="button" class="btn btn-success btn-sm fw-bold btn-refund-act" data-rfid="<?php echo $rfId; ?>" data-act="complete">
                                    <i class="bi bi-cash-coin me-1"></i>完成退款並退點
                                  </button>
                                <?php endif; ?>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; endif; ?>
                    </div>
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
  <script src="../scriptFiles/adminlte.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

  <script type="text/javascript">
  function doReDirect() { window.history.back(); }

  function doButSave() {
    var frm = $('#form_' + $('.tab-pane.active').prop("id"));
    if (!frm.length) return false;
    ajaxPOST(frm);
  }

  function doButReset() { location.reload(true); }

  function ajaxPOST(frm) {
    var $frm = frm || $('#form_tabMenu1');
    var datA = getFormObjects($frm);

    $.ajax({
      type: 'post',
      url: '../funcFiles/ord_act_func.php',
      data: {
        'arrDatA': datA,
        'formAcce': $frm.data('func'),
        'token': getTokenVal(),
        'ordID': '<?php echo $ordID; ?>'
      },
      dataType: 'json',
      success: function(data) {
        if (window.parent && window.parent.toastr) {
          window.parent.toastr[data['msgType']](data['msgStr']);
        }
        if (data['RTN'] == 1) {
          $('#sideOrdStatus').text($('#ord_status option:selected').text());
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
    // 1. 確認收款並立即開立電子發票
    $('#btnIssueInvoice').on('click', function() {
      if (!confirm('確定此筆訂單款項已入帳，並立即開立電子發票嗎？')) return;
      $.post('../funcFiles/ord_act_func.php', {
        'formAcce': 'func_ord_issueInvoice',
        'token': getTokenVal(),
        'ordID': '<?php echo $ordID; ?>'
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 2. 執行電子發票作廢 / 折讓
    $('#btnVoidInvoice').on('click', function() {
      if (!confirm('警告：確定要將此張電子發票執行「作廢 / 折讓」嗎？此動作無法復原！')) return;
      $.post('../funcFiles/ord_act_func.php', {
        'formAcce': 'func_ord_voidInvoice',
        'token': getTokenVal(),
        'ordID': '<?php echo $ordID; ?>'
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 3. 售後退換貨審核處理 (approve / reject / complete)
    $(document).on('click', '.btn-refund-act', function() {
      const rfId  = $(this).data('rfid');
      const act   = $(this).data('act');
      const reply = $('#refReply_' + rfId).val().trim();
      const msgMap = {
        'approve': '確定核准此筆售後退換貨申請嗎？',
        'reject': '確定駁回此筆售後退換貨申請嗎？',
        'complete': '確定已完成退款/換貨？系統將自動退還折抵的 PTS 點數並同步作廢發票！'
      };
      if (!confirm(msgMap[act] || '確定執行此操作？')) return;

      $.post('../funcFiles/ord_act_func.php', {
        'formAcce': 'func_ord_refundAction',
        'token': getTokenVal(),
        'ordID': '<?php echo $ordID; ?>',
        'refID': rfId,
        'refundAct': act,
        'adminReply': reply
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    $('li.prevLink').on('click', function() { window.location.replace(getCurrentURI('', 'ord_list.php')); });
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
