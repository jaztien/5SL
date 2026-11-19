<?php
// 宣告當前頁面身分為 myOrders（我的訂購）
$current_page = 'myOrders';

// 引入共用頁首
include_once '5sl_include_header.php';

$mb_id = (int)($_SESSION['mbID'] ?? $_SESSION['user_id'] ?? 0);

// 1. 查詢會員所有訂單主檔 + 關聯線上支付與電子發票紀錄 (fsl_orders + fsl_invoices)
$sql_orders = "
    SELECT o.*,
           i.inv_id, i.inv_pay_gateway, i.inv_pay_method, i.inv_trade_no, i.inv_card_last4,
           i.inv_paid_amount, i.inv_paid_at, i.inv_no, i.inv_random_code, i.inv_type,
           i.inv_tax_id, i.inv_company_title, i.inv_carrier_type, i.inv_carrier_code, i.inv_status
    FROM fsl_orders o
    LEFT JOIN fsl_invoices i ON o.ord_id = i.ord_id
    WHERE o.mb_id = ?
    ORDER BY o.ord_created DESC
";
$order_list = ($mb_id > 0) ? $DB->query($sql_orders, [$mb_id]) : [];
if (!is_array($order_list)) $order_list = [];

// 2. 預先批次撈取這些訂單底下的所有課程/商品明細 (fsl_order_items)，避免 N+1 查詢
$items_by_order = [];
if (!empty($order_list)) {
    $ord_ids = array_column($order_list, 'ord_id');
    $placeholders = implode(',', array_fill(0, count($ord_ids), '?'));
    $all_items = $DB->query("SELECT * FROM fsl_order_items WHERE ord_id IN ($placeholders) ORDER BY ordi_id ASC", $ord_ids);
    if (is_array($all_items)) {
        foreach ($all_items as $it) {
            $items_by_order[(int)$it['ord_id']][] = $it;
        }
    }
}

// 3. 查詢會員所有退費與售後申請紀錄 (fsl_refunds + 關聯訂單編號與發票號碼)
$sql_refunds = "
    SELECT r.*, o.ord_no, i.inv_no
    FROM fsl_refunds r
    INNER JOIN fsl_orders o ON r.ord_id = o.ord_id
    LEFT JOIN fsl_invoices i ON r.inv_id = i.inv_id
    WHERE r.mb_id = ?
    ORDER BY r.ref_created DESC
";
$refund_list = ($mb_id > 0) ? $DB->query($sql_refunds, [$mb_id]) : [];
if (!is_array($refund_list)) $refund_list = [];

// 計算進行中退費案件數量
$active_rma_count = 0;
foreach ($refund_list as $rf) {
    if (in_array((int)$rf['ref_status'], [1, 2], true)) $active_rma_count++;
}

// 訂單狀態對照表（對齊 fsl_orders.ord_status）
$ord_status_map = [
    1 => ['code' => 'pending',    'label' => '待付款',       'badge' => 'bg-danger',            'icon' => 'bi-credit-card'],
    2 => ['code' => 'processing', 'label' => '已付款處理中', 'badge' => 'bg-warning text-dark', 'icon' => 'bi-gear-wide-connected'],
    3 => ['code' => 'shipped',    'label' => '實體已出貨',   'badge' => 'bg-primary',           'icon' => 'bi-truck'],
    4 => ['code' => 'completed',  'label' => '已完成',       'badge' => 'bg-success',           'icon' => 'bi-check-circle-fill'],
    5 => ['code' => 'refunding',  'label' => '退費申請中',   'badge' => 'bg-info text-dark',    'icon' => 'bi-arrow-return-left'],
    6 => ['code' => 'refunded',   'label' => '已退費',       'badge' => 'bg-secondary',         'icon' => 'bi-arrow-counterclockwise'],
    9 => ['code' => 'cancelled',  'label' => '已取消',       'badge' => 'bg-secondary',         'icon' => 'bi-x-circle'],
];
?>

<div class="container my-5 py-4">
    <div class="row g-4 mt-2">

        <!-- 左側：會員中心共用子導航列 -->
        <div class="col-md-4 col-lg-3">
            <?php include_once '5sl_include_subnavi.php'; ?>
        </div>

        <!-- 右側：我的訂購核心工作視窗 -->
        <div class="col-md-8 col-lg-9">
            <div class="card border-0 shadow-sm p-4 p-md-5 bg-white bg-opacity-75 rounded-3 h-100" style="min-height: 600px;">
                <div class="card-body p-0">

                    <!-- 頁面主標題 -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 mb-4">
                        <div>
                            <h3 class="fw-bold text-dark mb-1">
                                <i class="bi bi-receipt-cutoff me-2 text-warning"></i>我的訂購 (Order History)
                            </h3>
                            <p class="text-muted small mb-0">查詢您的課程報名、顧問預約與實體/數位商品訂購明細，並線上辦理售後申請。</p>
                        </div>
                        <div class="mt-3 mt-md-0">
                            <span class="badge bg-dark text-white px-3 py-2">
                                <i class="bi bi-bag-check me-1 text-warning"></i>累計訂單：<?php echo count($order_list); ?> 筆
                            </span>
                        </div>
                    </div>

                    <!-- 操作結果即時提示區 -->
                    <div id="ordersAlertBox" class="alert d-none align-items-center mb-4" role="alert">
                        <i id="ordersAlertIcon" class="bi me-2 fs-5"></i>
                        <div id="ordersAlertMsg" class="small fw-bold"></div>
                    </div>

                    <!-- 主分頁切換 (Nav Pills) -->
                    <ul class="nav nav-pills mb-4 gap-2" id="ordersTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold px-4" id="history-tab" data-bs-toggle="pill" data-bs-target="#history-pane" type="button" role="tab">
                                <i class="bi bi-list-ul me-2"></i>歷史訂購查詢
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-4" id="rma-tab" data-bs-toggle="pill" data-bs-target="#rma-pane" type="button" role="tab">
                                <i class="bi bi-arrow-return-left me-2"></i>退換貨與售後進度 (<span id="rmaCountBadge"><?php echo count($refund_list); ?></span>)
                            </button>
                        </li>
                    </ul>

                    <!-- 分頁內容主體 -->
                    <div class="tab-content" id="ordersTabContent">

                        <!-- ============================================================
                             區塊 1：歷史訂購查詢 (多狀態檢視與搜尋)
                             ============================================================ -->
                        <div class="tab-pane fade show active" id="history-pane" role="tabpanel" tabindex="0">

                            <!-- 狀態快速篩選與關鍵字搜尋列 -->
                            <div class="p-3 bg-light border rounded-3 mb-4">
                                <div class="row g-2 align-items-center">
                                    <div class="col-lg-8">
                                        <div class="d-flex flex-wrap gap-1" id="orderStatusFilter">
                                            <button type="button" class="btn btn-sm btn-dark active" data-status="all">全部狀態</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-status="pending">待付款</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-status="processing">處理中</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-status="shipped">已出貨</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-status="completed">已完成</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-status="refunding">退費中</button>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" id="inputOrderSearch" placeholder="搜尋訂單編號或商品名稱...">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 歷史訂單列表 -->
                            <div class="d-flex flex-column gap-3" id="orderCardsContainer">
                                <?php if (empty($order_list)): ?>
                                    <div class="text-center py-5 bg-light rounded-3 border">
                                        <i class="bi bi-bag-x display-4 text-muted mb-2 d-block"></i>
                                        <h6 class="fw-bold text-muted">目前尚無任何訂購紀錄</h6>
                                        <p class="text-muted small mb-0">前往探索課程與生命美學商品，開啟您的第五季旅程！</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($order_list as $ord):
                                        $oid       = (int)$ord['ord_id'];
                                        $st_num    = (int)$ord['ord_status'];
                                        $st_info   = $ord_status_map[$st_num] ?? $ord_status_map[1]; // 補上 [1] 預設為待付款
                                        $items     = $items_by_order[$oid] ?? [];
                                        $currency  = htmlspecialchars($ord['ord_currency'] ?: 'TWD');
                                        $total_fmt = $currency . '$ ' . number_format((float)$ord['ord_total'], ($currency === 'USD' ? 2 : 0));

                                        // 組合商品標題摘要與搜尋關鍵字
                                        $titles_arr = array_column($items, 'ordi_title');
                                        $main_title = !empty($titles_arr) ? (string)$titles_arr[0] : '人生第五季訂購方案'; // 補上 [0] 取第一筆標題字串
                                        if (count($titles_arr) > 1) {
                                            $main_title .= ' 等共 ' . count($titles_arr) . ' 項';
                                        }

                                        $search_str = $ord['ord_no'] . ' ' . implode(' ', $titles_arr);

                                        // 組合付款與電子發票顯示字串
                                        $pay_text = !empty($ord['inv_pay_gateway']) ? $ord['inv_pay_gateway'] : '線上金流';
                                        if (!empty($ord['inv_card_last4'])) {
                                            $pay_text .= ' (末四碼 ' . htmlspecialchars($ord['inv_card_last4']) . ')';
                                        }
                                        if (!empty($ord['inv_no'])) {
                                            $inv_text = htmlspecialchars($ord['inv_no']);
                                            if ((int)$ord['inv_type'] === 2 && !empty($ord['inv_tax_id'])) {
                                                $inv_text .= ' (統編 ' . htmlspecialchars($ord['inv_tax_id']) . ')';
                                            }
                                        } else {
                                            $inv_text = ($st_num === 1) ? '待付款後自動開立' : '免開立 / 系統處理中';
                                        }

                                        // 組合完整明細 JSON 供彈窗顯示
                                        $items_summary = [];
                                        foreach ($items as $it) {
                                            $tag = ((int)$it['ordi_type'] === 1) ? '[課程]' : '[商品]';
                                            $items_summary[] = "{$tag} {$it['ordi_title']} × {$it['ordi_qty']}";
                                        }
                                        $note_detail = !empty($ord['ord_note']) ? $ord['ord_note'] : '訂單系統正常處理中，若有疑問歡迎聯繫專屬客服。';
                                        if (!empty($ord['ord_shipping_no'])) {
                                            $note_detail .= ' ｜ 物流單號：' . $ord['ord_shipping_no'];
                                        }
                                    ?>
                                        <div class="border rounded-3 p-3 p-md-4 bg-white shadow-sm order-card-item"
                                            data-status="<?php echo $st_info['code']; ?>"
                                            data-search="<?php echo htmlspecialchars($search_str, ENT_QUOTES, 'UTF-8'); ?>">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-3 gap-2">
                                                <div class="small">
                                                    <span class="text-muted">訂單編號：</span>
                                                    <strong class="text-dark"><?php echo htmlspecialchars($ord['ord_no']); ?></strong>
                                                    <span class="text-muted ms-3">訂購日期：<?php echo date('Y/m/d H:i', strtotime($ord['ord_created'])); ?></span>
                                                </div>
                                                <span class="badge <?php echo $st_info['badge']; ?> px-3 py-1">
                                                    <i class="bi <?php echo $st_info['icon']; ?> me-1"></i><?php echo $st_info['label']; ?>
                                                </span>
                                            </div>

                                            <div class="row align-items-center g-3">
                                                <div class="col-md-7">
                                                    <div class="d-flex flex-wrap gap-1 mb-1">
                                                        <?php foreach ($items as $it): ?>
                                                            <?php if ((int)$it['ordi_type'] === 1): ?>
                                                                <span class="badge bg-warning-subtle text-dark border border-warning">課程方案</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-primary-subtle text-primary">實體/數位商品</span>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                        <?php if ((int)$ord['ord_pts_used'] > 0): ?>
                                                            <span class="badge bg-success-subtle text-success">折抵 <?php echo number_format((int)$ord['ord_pts_used']); ?> PTS</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($main_title); ?></h6>
                                                    <p class="text-muted small mb-0">
                                                        付款管道：<?php echo $pay_text; ?> • 電子發票：<strong><?php echo $inv_text; ?></strong>
                                                        <?php if (!empty($ord['ord_shipping_no'])): ?>
                                                            <br>物流追蹤單號：<code><?php echo htmlspecialchars($ord['ord_shipping_no']); ?></code>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>

                                                <div class="col-md-5 text-md-end">
                                                    <div class="text-muted small">實付金額</div>
                                                    <div class="fs-5 fw-bold text-dark mb-2"><?php echo $total_fmt; ?></div>
                                                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                                        <button type="button"
                                                                class="btn btn-outline-dark btn-sm btn-view-order"
                                                                data-id="<?php echo htmlspecialchars($ord['ord_no']); ?>"
                                                                data-title="<?php echo htmlspecialchars(implode(' ＋ ', $items_summary), ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-amount="<?php echo $total_fmt; ?>"
                                                                data-status="<?php echo $st_info['label']; ?>"
                                                                data-invoice="<?php echo htmlspecialchars($inv_text, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-note="<?php echo htmlspecialchars($note_detail, ENT_QUOTES, 'UTF-8'); ?>">
                                                            檢視明細
                                                        </button>
                                                        <?php if (in_array($st_num, [2, 3, 4], true)): ?>
                                                            <button type="button"
                                                                    class="btn btn-outline-danger btn-sm btn-open-rma"
                                                                    data-id="<?php echo htmlspecialchars($ord['ord_no']); ?>"
                                                                    data-title="<?php echo htmlspecialchars($main_title, ENT_QUOTES, 'UTF-8'); ?>">
                                                                申請退換貨
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- 查無訂單提示 (預設隱藏) -->
                            <div id="noOrderFoundAlert" class="text-center py-5 bg-light rounded-3 border d-none">
                                <i class="bi bi-search display-4 text-muted mb-2 d-block"></i>
                                <h6 class="fw-bold text-muted">找不到符合條件的訂單紀錄</h6>
                                <p class="text-muted small mb-0">請嘗試切換其他訂單狀態或清除搜尋關鍵字。</p>
                            </div>

                        </div> <!-- 結束區塊 1：history-pane -->

                        <!-- ============================================================
                             區塊 2：退換商品與售後申請 (發起申請與追蹤進度)
                             ============================================================ -->
                        <div class="tab-pane fade" id="rma-pane" role="tabpanel" tabindex="0">

                            <!-- 頂部快速發起售後申請引導列 -->
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center p-3 bg-light border rounded-3 mb-4 gap-3">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">需要辦理退貨、換貨或專案退款？</h6>
                                    <p class="text-muted small mb-0">實體商品享有到貨 7 日鑑賞期；線上課程於開課 7 日內且觀看進度未達 10% 可申請全額退費。</p>
                                </div>
                                <button type="button" class="btn btn-dark text-warning fw-bold btn-sm px-3 text-nowrap btn-open-rma" data-id="" data-title="">
                                    <i class="bi bi-plus-circle me-1"></i>填寫售後申請單
                                </button>
                            </div>

                            <!-- 售後案件進度追蹤列表 -->
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>售後與退款案件處理進度</h6>
                        <div class="d-flex flex-column gap-3" id="rmaCasesContainer">
                            <?php if (empty($refund_list)): ?>
                                <div class="text-center py-4 bg-light rounded-3 border text-muted small" id="emptyRmaHint">
                                    目前尚無退換貨或退款申請紀錄。
                                </div>
                            <?php else: ?>
                                <?php foreach ($refund_list as $rf):
                                    $r_status = (int)$rf['ref_status']; // 1審核中, 2核准退款中, 3已完成退款, 9已駁回
                                    $type_names = [1 => '全額退費申請', 2 => '部分比例退費', 3 => '實體商品瑕疵換貨'];
                                    $r_type_label = $type_names[(int)$rf['ref_type']] ?? '售後服務申請';

                                    if ($r_status === 1) {
                                        $r_badge = '<span class="badge bg-warning text-dark px-3 py-1"><i class="bi bi-hourglass-split me-1"></i>專員審核中</span>';
                                    } elseif ($r_status === 2) {
                                        $r_badge = '<span class="badge bg-primary px-3 py-1"><i class="bi bi-arrow-repeat me-1"></i>已核准，財務退款/作業中</span>';
                                    } elseif ($r_status === 3) {
                                        $r_badge = '<span class="badge bg-success px-3 py-1"><i class="bi bi-check-circle-fill me-1"></i>已完成退款結案</span>';
                                    } else {
                                        $r_badge = '<span class="badge bg-secondary px-3 py-1"><i class="bi bi-x-circle me-1"></i>申請已駁回</span>';
                                    }
                                ?>
                                    <div class="border <?php echo in_array($r_status, [1, 2], true) ? 'border-warning' : ''; ?> rounded-3 p-4 bg-white shadow-sm">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-3 gap-2">
                                            <div class="small">
                                                <span class="text-muted">售後單號：</span>
                                                <strong class="text-dark"><?php echo htmlspecialchars($rf['ref_no']); ?></strong>
                                                <span class="text-muted ms-3">原訂單：<?php echo htmlspecialchars($rf['ord_no']); ?></span>
                                                <?php if (!empty($rf['inv_no'])): ?>
                                                    <span class="text-muted ms-2">(發票：<?php echo htmlspecialchars($rf['inv_no']); ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php echo $r_badge; ?>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">
                                            <?php echo htmlspecialchars($r_type_label); ?> — <?php echo htmlspecialchars($rf['ref_reason']); ?>
                                            <?php if ((float)$rf['ref_amount'] > 0): ?>
                                                <span class="text-danger ms-2">(申請退款金額：NT$ <?php echo number_format((float)$rf['ref_amount']); ?>)</span>
                                            <?php endif; ?>
                                        </h6>
                                        <?php if (!empty($rf['ref_detail'])): ?>
                                            <p class="text-muted small mb-3"><?php echo nl2br(htmlspecialchars($rf['ref_detail'])); ?></p>
                                        <?php endif; ?>

                                        <div class="bg-light p-3 rounded border small">
                                            <div class="fw-bold text-dark mb-1"><i class="bi bi-chat-left-text me-1 text-primary"></i>最新處理進度與客服備註：</div>
                                            <div class="text-muted">
                                                <?php echo !empty($rf['ref_admin_note']) ? htmlspecialchars($rf['ref_admin_note']) : '系統已於 ' . date('Y/m/d H:i', strtotime($rf['ref_created'])) . ' 收到您的申請，專員將於 3 個工作日內完成審核。'; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <!-- 結束 rmaCasesContainer -->

                        </div> <!-- 結束區塊 2：rma-pane -->

                    </div> <!-- 結束 tab-content -->

                </div>
            </div>
        </div>

    </div>
</div>

<!-- ==========================================================================
     彈窗 1：訂單與電子發票詳情檢視
     ========================================================================== -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-text me-2 text-warning"></i>訂單與電子發票明細</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 small">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">訂單編號</span>
                    <strong class="text-dark" id="modalOrderId">-</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">訂購內容</span>
                    <strong class="text-dark text-end" id="modalOrderTitle" style="max-width: 65%;">-</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">目前狀態</span>
                    <span class="badge bg-dark text-warning" id="modalOrderStatus">-</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">實付金額</span>
                    <strong class="text-primary fs-6" id="modalOrderAmount">-</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">電子發票號碼</span>
                    <strong class="text-dark" id="modalOrderInvoice">-</strong>
                </div>
                <div class="mt-3 p-3 bg-light rounded border">
                    <div class="fw-bold text-dark mb-1">最新處理進度備註：</div>
                    <div class="text-muted mb-0" id="modalOrderNote">-</div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">關閉視窗</button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     彈窗 2：線上退換貨與退款申請表單
     ========================================================================== -->
<div class="modal fade" id="rmaRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-return-left me-2 text-warning"></i>線上退換貨與退款申請</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSubmitRma">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">選擇或輸入訂單編號 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inputRmaOrderId" placeholder="例如：ORD-20261102-688" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">申請類型 <span class="text-danger">*</span></label>
                        <select class="form-select" id="selectRmaType" required>
                            <option value="1">全額退費申請 (實體商品 7 日鑑賞期 / 課程未啟用)</option>
                            <option value="3">實體商品瑕疵或運送壓損換貨</option>
                            <option value="2">部分比例退費 (依陪跑課程進行比例計算)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">詳細原因說明 <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="inputRmaReason" rows="3" placeholder="請簡述您的退換貨原因，若為商品瑕疵可於客服回信時補附照片..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-danger fw-bold btn-sm px-4">確認送出申請單</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    body {
        background-image: url("imageFiles/mainBG-3.jpg");
        background-attachment: fixed;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    #ordersTab .nav-link {
        color: #495057;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
    }

    #ordersTab .nav-link:hover {
        background-color: #e9ecef;
        color: #212529;
    }

    #ordersTab .nav-link.active {
        background-color: #212529 !important;
        color: #ffc107 !important;
        border-color: #212529 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
</style>

<?php include_once '5sl_include_footer.php'; ?>

<script>
    $(function() {
        let currentStatusFilter = 'all';
        let rmaCount = 1;

        // 1. 通用提示框控制
        function showOrdersAlert(type, msg) {
            const $box = $('#ordersAlertBox');
            const $icon = $('#ordersAlertIcon');
            $box.removeClass('d-none alert-success alert-danger alert-warning').addClass('d-flex');
            $icon.removeClass('bi-check-circle-fill bi-exclamation-triangle-fill');

            if (type === 'success') {
                $box.addClass('alert-success');
                $icon.addClass('bi-check-circle-fill');
            } else {
                $box.addClass('alert-danger');
                $icon.addClass('bi-exclamation-triangle-fill');
            }
            $('#ordersAlertMsg').text(msg);
            $('html, body').animate({
                scrollTop: $box.offset().top - 120
            }, 300);
        }

        // 2. 訂單多維度即時篩選 (狀態 + 關鍵字連動)
        function filterOrders() {
            const keyword = $('#inputOrderSearch').val().trim().toLowerCase();
            let visibleCount = 0;

            $('.order-card-item').each(function() {
                const itemStatus = $(this).attr('data-status');
                const itemSearchText = $(this).attr('data-search').toLowerCase();

                const matchStatus = (currentStatusFilter === 'all' || itemStatus === currentStatusFilter);
                const matchKeyword = (keyword === '' || itemSearchText.indexOf(keyword) !== -1);

                if (matchStatus && matchKeyword) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            if (visibleCount === 0) {
                $('#noOrderFoundAlert').removeClass('d-none');
            } else {
                $('#noOrderFoundAlert').addClass('d-none');
            }
        }

        $('#orderStatusFilter button').on('click', function() {
            $('#orderStatusFilter button').removeClass('btn-dark active').addClass('btn-outline-secondary');
            $(this).removeClass('btn-outline-secondary').addClass('btn-dark active');
            currentStatusFilter = $(this).attr('data-status');
            filterOrders();
        });

        $('#inputOrderSearch').on('input', filterOrders);

        // 3. 開啟訂單詳情與電子發票彈窗
        $(document).on('click', '.btn-view-order', function() {
            $('#modalOrderId').text($(this).attr('data-id'));
            $('#modalOrderTitle').text($(this).attr('data-title'));
            $('#modalOrderStatus').text($(this).attr('data-status'));
            $('#modalOrderAmount').text($(this).attr('data-amount'));
            $('#modalOrderInvoice').text($(this).attr('data-invoice'));
            $('#modalOrderNote').text($(this).attr('data-note'));

            const detailModal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
            detailModal.show();
        });

        // 4. 開啟退換貨 / 退款申請視窗 (自動帶入訂單編號)
        $(document).on('click', '.btn-open-rma', function() {
            const orderId = $(this).attr('data-id');
            $('#inputRmaOrderId').val(orderId);
            const rmaModal = new bootstrap.Modal(document.getElementById('rmaRequestModal'));
            rmaModal.show();
        });

        // 5. 送出退換貨售後申請單
        $('#formSubmitRma').on('submit', function(e) {
            e.preventDefault();
            const orderId = $('#inputRmaOrderId').val().trim().toUpperCase();
            const rmaType = $('#selectRmaType').val();
            const reason = $('#inputRmaReason').val().trim();

            bootstrap.Modal.getInstance(document.getElementById('rmaRequestModal')).hide();
            if (window.fslLoader) window.fslLoader.show();

            setTimeout(function() {
                if (window.fslLoader) window.fslLoader.hide();
                rmaCount++;
                $('#rmaCountBadge').text(rmaCount);

                const newRmaNo = 'RMA-' + new Date().toISOString().slice(0, 10).replace(/-/g, '') + '-' + Math.floor(100 + Math.random() * 900);
                const newCaseHtml = `
                <div class="border border-primary rounded-3 p-4 bg-white shadow-sm">
                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-3 gap-2">
                        <div class="small">
                            <span class="text-muted">售後單號：</span>
                            <strong class="text-dark">${newRmaNo}</strong>
                            <span class="text-muted ms-3">原訂單：${orderId}</span>
                        </div>
                        <span class="badge bg-primary px-3 py-1"><i class="bi bi-clock me-1"></i>已受理，等待客服審核</span>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">申請項目：${rmaType}</h6>
                    <p class="text-muted small mb-3">申請原因：${$('<div>').text(reason).html()}</p>
                    <div class="bg-light p-2 rounded border small text-primary">
                        <i class="bi bi-info-circle-fill me-1"></i>系統已收到您的申請，專屬客服將於 1 個工作天內主動與您聯繫處理後續事宜。
                    </div>
                </div>`;

                $('#rmaCasesContainer').prepend(newCaseHtml);
                $('#formSubmitRma')[0].reset();

                const rmaTabTrigger = new bootstrap.Tab(document.getElementById('rma-tab'));
                rmaTabTrigger.show();
                showOrdersAlert('success', `售後申請單（${newRmaNo}）已成功送出！您可於下方隨時追蹤審核進度。`);
            }, 700);
        });
    });
</script>