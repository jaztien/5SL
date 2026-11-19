<?php
// 宣告當前頁面身分為 myCart（購物車與結帳）
$current_page = 'myCart';

// 引入共用頁首（Session 檢查與 $DB 已於此載入）
include_once '5sl_include_header.php';

$mb_id = (int)($_SESSION['mbID'] ?? $_SESSION['user_id'] ?? 0);

// 1. 查詢會員基本資料與可用 PTS 點數餘額（自動帶入預設收件人資訊）
$member_info = ($mb_id > 0) ? $DB->row("SELECT mb_name, mb_title, mb_cellphone, mb_add, mb_points FROM members WHERE mb_id = ?", [$mb_id]) : [];
$member_pts  = (int)($member_info['mb_points'] ?? 0);
$def_name    = !empty($member_info['mb_name']) ? _fn_secure_crypto($member_info['mb_name'], 'de') : ($member_info['mb_title'] ?? '');
$def_phone   = !empty($member_info['mb_cellphone']) ? _fn_secure_crypto($member_info['mb_cellphone'], 'de') : '';
$def_addr    = !empty($member_info['mb_add']) ? _fn_secure_crypto($member_info['mb_add'], 'de') : '';

// 2. 查詢購物車內所有項目（關聯 courses、course_sessions 與 merchandise）
$sql_cart = "
    SELECT ci.cart_id, ci.cart_item_type, ci.course_id, ci.sess_id, ci.merch_id, ci.cart_qty,
           c.course_title, c.course_cost, c.course_type,
           s.sess_batch, s.sess_startdate, s.sess_starttime,
           m.merch_title, m.merch_type, m.merch_price_twd, m.merch_price_usd, m.merch_sale_usd
    FROM fsl_cart_items ci
    LEFT JOIN courses c ON ci.cart_item_type = 1 AND ci.course_id = c.course_id
    LEFT JOIN course_sessions s ON ci.cart_item_type = 1 AND ci.sess_id = s.sess_id
    LEFT JOIN merchandise m ON ci.cart_item_type = 2 AND ci.merch_id = m.merch_id
    WHERE ci.mb_id = ?
    ORDER BY ci.cart_id ASC
";
$cart_rows = ($mb_id > 0) ? $DB->query($sql_cart, [$mb_id]) : [];
if (!is_array($cart_rows)) $cart_rows = [];

// 整理購物車項目顯示數據
$cart_items   = [];
$subtotal_twd = 0;
$has_physical = false;

foreach ($cart_rows as $cr) {
    $qty = max(1, (int)$cr['cart_qty']);
    if ((int)$cr['cart_item_type'] === 1) {
        $is_consult = ((int)$cr['course_type'] === 2);
        $sub_info   = !empty($cr['sess_startdate'])
            ? '梯次/時段：' . $cr['sess_startdate'] . ' ' . substr($cr['sess_starttime'], 0, 5)
            : '最近開放梯次';
        $unit_price = (int)round((float)$cr['course_cost']);
        $item_sub   = $unit_price * $qty;
        $subtotal_twd += $item_sub;

        $cart_items[] = [
            'cart_id'    => (int)$cr['cart_id'],
            'tag'        => $is_consult ? '一對一顧問預約' : '線上/實體課程',
            'tag_cls'    => $is_consult ? 'bg-info text-dark' : 'bg-warning text-dark',
            'title'      => $cr['course_title'],
            'sub_info'   => $sub_info,
            'unit_price' => $unit_price,
            'qty'        => 1,
            'can_qty'    => false,
            'subtotal'   => $item_sub
        ];
    } else {
        $m_type = (int)$cr['merch_type'];
        if ($m_type === 1) $has_physical = true;

        $twd_p = (int)round((float)$cr['merch_price_twd']);
        if ($twd_p <= 0) {
            $usd_p = ($cr['merch_sale_usd'] !== null) ? (float)$cr['merch_sale_usd'] : (float)$cr['merch_price_usd'];
            $twd_p = (int)round($usd_p * 32);
        }
        $item_sub = $twd_p * $qty;
        $subtotal_twd += $item_sub;

        $cart_items[] = [
            'cart_id'    => (int)$cr['cart_id'],
            'tag'        => ($m_type === 1) ? '實體精裝商品' : (($m_type === 2) ? '數位下載物件' : '實體沙龍票券'),
            'tag_cls'    => ($m_type === 1) ? 'bg-primary' : 'bg-success',
            'title'      => $cr['merch_title'],
            'sub_info'   => ($m_type === 1) ? '宅配寄送商品' : '結帳開通後可立即下載',
            'unit_price' => $twd_p,
            'qty'        => $qty,
            'can_qty'    => ($m_type !== 2),
            'subtotal'   => $item_sub
        ];
    }
}

// 3. 查詢會員名下可用的折價券 (fsl_gift_records + fsl_gifts)
$my_coupons = ($mb_id > 0) ? $DB->query("
    SELECT r.grec_id, g.gift_title, g.gift_discount_usd
    FROM fsl_gift_records r
    INNER JOIN fsl_gifts g ON r.gift_id = g.gift_id
    WHERE r.mb_id = ? AND r.grec_status = 1 AND g.gift_discount_usd > 0
      AND (r.grec_expired_at IS NULL OR r.grec_expired_at > NOW())
    ORDER BY g.gift_discount_usd DESC
", [$mb_id]) : [];
if (!is_array($my_coupons)) $my_coupons = [];
?>

<div class="container my-5 py-4">
    <div class="row g-4 mt-2">
        
        <!-- 左側：會員中心共用子導航列 -->
        <div class="col-md-4 col-lg-3">
            <?php include_once '5sl_include_subnavi.php'; ?>
        </div>

        <!-- 右側：極簡一頁式購物車與結帳核心視窗 -->
        <div class="col-md-8 col-lg-9">
            <div class="card border-0 shadow-sm p-4 p-md-5 bg-white bg-opacity-75 rounded-3 h-100" style="min-height: 620px;">
                <div class="card-body p-0">
                    
                    <!-- 頁面主標題 -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 mb-4">
                        <div>
                            <h3 class="fw-bold text-dark mb-1">
                                <i class="bi bi-cart-check-fill me-2 text-warning"></i>購物車與極速結帳 (Checkout)
                            </h3>
                            <p class="text-muted small mb-0">確認預約時段與商品明細，套用優惠券與紅利點數即可一鍵完成訂購。</p>
                        </div>
                        <div class="mt-3 mt-md-0">
                            <span class="badge bg-dark text-warning px-3 py-2">
                                <i class="bi bi-coin me-1"></i>可用點數：<?php echo number_format($member_pts); ?> PTS
                            </span>
                        </div>
                    </div>

                    <!-- 操作結果提示區 -->
                    <div id="cartAlertBox" class="alert d-none align-items-center mb-4" role="alert">
                        <i id="cartAlertIcon" class="bi me-2 fs-5"></i>
                        <div id="cartAlertMsg" class="small fw-bold"></div>
                    </div>

                    <?php if (empty($cart_items)): ?>
                        <!-- 空購物車引導畫面 -->
                        <div class="text-center py-5 bg-light rounded-3 border">
                            <i class="bi bi-cart-x display-3 text-muted mb-3 d-block"></i>
                            <h5 class="fw-bold text-dark mb-2">您的購物車目前還是空的</h5>
                            <p class="text-muted small mb-4">探索《步步向光》陪跑課程、一對一深度陪談或生命整理美學商品，開啟您的第五季旅程！</p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="index.php#pricing" class="btn btn-dark text-warning fw-bold px-4">前往探索課程與服務</a>
                                <a href="5sl_myOrders.php" class="btn btn-outline-secondary px-4">檢視歷史訂單</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- 購物車明細清單 -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-bag-heart-fill me-2 text-primary"></i>已選購與預約項目 (<?php echo count($cart_items); ?> 項)</h6>
                            <div class="d-flex flex-column gap-2" id="cartItemsWrapper">
                                <?php foreach ($cart_items as $ci): ?>
                                    <div class="border rounded-3 p-3 bg-white shadow-sm cart-row-item"
                                         data-cart-id="<?php echo $ci['cart_id']; ?>"
                                         data-unit-price="<?php echo $ci['unit_price']; ?>">
                                        <div class="row align-items-center g-2">
                                            <div class="col-md-6">
                                                <span class="badge <?php echo $ci['tag_cls']; ?> mb-1"><?php echo $ci['tag']; ?></span>
                                                <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($ci['title']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($ci['sub_info']); ?></small>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <?php if ($ci['can_qty']): ?>
                                                    <div class="input-group input-group-sm" style="max-width: 115px;">
                                                        <button class="btn btn-outline-secondary btn-qty-minus" type="button">-</button>
                                                        <input type="text" class="form-control text-center fw-bold input-cart-qty" value="<?php echo $ci['qty']; ?>" readonly>
                                                        <button class="btn btn-outline-secondary btn-qty-plus" type="button">+</button>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark border">數量：1</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-6 col-md-3 text-end">
                                                <div class="fw-bold text-dark fs-6 mb-1">NT$ <span class="row-subtotal"><?php echo number_format($ci['subtotal']); ?></span></div>
                                                <button type="button" class="btn btn-link text-danger btn-sm p-0 text-decoration-none btn-remove-cart">
                                                    <i class="bi bi-trash3 me-1"></i>移除
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- 極簡一頁式結帳表單 -->
                        <form id="formExpressCheckout">
                            <div class="row g-4">
                                
                                <!-- 左欄：優惠折抵、收件資訊與電子發票 -->
                                <div class="col-lg-7">
                                    
                                    <!-- 1. 優惠券與 PTS 紅利點數折抵 -->
                                    <div class="p-3 bg-light border rounded-3 mb-3">
                                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-ticket-perforated-fill me-2 text-warning"></i>優惠券與 PTS 點數折抵</h6>
                                        <div class="mb-3">
                                            <label for="selectCheckoutCoupon" class="form-label small fw-bold text-muted">選擇可用折價券</label>
                                            <select class="form-select form-select-sm" id="selectCheckoutCoupon" name="grec_id">
                                                <option value="0" data-discount="0">-- 不使用折價券 --</option>
                                                <?php foreach ($my_coupons as $cp):
                                                    $disc_twd = (int)round((float)$cp['gift_discount_usd'] * 32);
                                                ?>
                                                    <option value="<?php echo (int)$cp['grec_id']; ?>" data-discount="<?php echo $disc_twd; ?>">
                                                        <?php echo htmlspecialchars($cp['gift_title']); ?>（折抵 NT$ <?php echo number_format($disc_twd); ?>）
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label for="inputUsePts" class="form-label small fw-bold text-muted mb-0">使用 PTS 紅利折抵 (1 PTS = NT$ 1)</label>
                                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-bold" id="btnUseAllPts">全額折抵</button>
                                            </div>
                                            <input type="number" class="form-control form-control-sm" id="inputUsePts" name="use_pts" min="0" max="<?php echo $member_pts; ?>" value="0" placeholder="輸入欲折抵點數">
                                        </div>
                                    </div>

                                    <!-- 2. 實體商品宅配收件資訊（僅當購物車內含實體商品時自動顯示） -->
                                    <?php if ($has_physical): ?>
                                        <div class="p-3 bg-light border border-primary-subtle rounded-3 mb-3">
                                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-truck me-2 text-primary"></i>實體商品收件資訊</h6>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small fw-bold">收件人姓名 <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-sm" name="receiver_name" value="<?php echo htmlspecialchars($def_name); ?>" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-bold">聯絡電話 <span class="text-danger">*</span></label>
                                                    <input type="tel" class="form-control form-control-sm" name="receiver_phone" value="<?php echo htmlspecialchars($def_phone); ?>" required>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold">配送地址 <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-sm" name="receiver_address" value="<?php echo htmlspecialchars($def_addr); ?>" placeholder="請輸入完整宅配地址" required>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- 3. 付款方式與電子發票設定 -->
                                    <div class="p-3 bg-light border rounded-3">
                                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-receipt me-2 text-success"></i>付款方式與電子發票</h6>
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-muted">線上支付管道</label>
                                                <select class="form-select form-select-sm" name="pay_gateway">
                                                    <option value="ECPay">綠界金流 ECPay</option>
                                                    <option value="NewebPay">藍新金流 NewebPay</option>
                                                    <option value="LinePay">LINE Pay 行動支付</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-muted">付款別</label>
                                                <select class="form-select form-select-sm" name="pay_method">
                                                    <option value="Credit">信用卡一次付清</option>
                                                    <option value="Installment">信用卡分期零利率</option>
                                                    <option value="ATM">ATM 虛擬帳號轉帳</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-5">
                                                <label class="form-label small fw-bold text-muted">發票類型</label>
                                                <select class="form-select form-select-sm" id="selectInvType" name="inv_type">
                                                    <option value="1">二聯式個人電子發票</option>
                                                    <option value="2">三聯式公司統一編號</option>
                                                </select>
                                            </div>
                                            <div class="col-7" id="carrierBox">
                                                <label class="form-label small fw-bold text-muted">手機條碼載具 (選填)</label>
                                                <input type="text" class="form-control form-control-sm text-uppercase" name="inv_carrier_code" placeholder="如 /AB12345，留空存會員載具">
                                            </div>
                                        </div>
                                        <div class="row g-2 d-none" id="companyTaxBox">
                                            <div class="col-5">
                                                <input type="text" class="form-control form-control-sm" name="inv_tax_id" maxlength="8" placeholder="8碼統一編號">
                                            </div>
                                            <div class="col-7">
                                                <input type="text" class="form-control form-control-sm" name="inv_company_title" placeholder="公司發票抬頭">
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <input type="text" class="form-control form-control-sm" name="ord_note" placeholder="訂單備註或預約補充說明 (選填)">
                                        </div>
                                    </div>

                                </div>

                                <!-- 右欄：金額結算摘要卡片 -->
                                <div class="col-lg-5">
                                    <div class="p-4 rounded-3 bg-dark text-white shadow-sm sticky-top" style="top: 100px; border: 1px solid #ffc107;">
                                        <h6 class="fw-bold text-warning border-bottom border-secondary pb-2 mb-3">
                                            <i class="bi bi-calculator me-1"></i>結帳金額明細
                                        </h6>
                                        <div class="d-flex justify-content-between small mb-2">
                                            <span class="text-white-50">課程與商品小計</span>
                                            <span class="fw-bold">NT$ <span id="summarySubtotal"><?php echo number_format($subtotal_twd); ?></span></span>
                                        </div>
                                        <div class="d-flex justify-content-between small mb-2">
                                            <span class="text-white-50">優惠券折抵</span>
                                            <span class="text-success fw-bold">- NT$ <span id="summaryCoupon">0</span></span>
                                        </div>
                                        <div class="d-flex justify-content-between small mb-3 pb-3 border-bottom border-secondary">
                                            <span class="text-white-50">PTS 紅利點數折抵</span>
                                            <span class="text-warning fw-bold">- NT$ <span id="summaryPts">0</span></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-baseline mb-4">
                                            <span class="fw-bold">應付實付總額</span>
                                            <span class="fs-3 fw-bold text-warning">NT$ <span id="summaryFinal"><?php echo number_format($subtotal_twd); ?></span></span>
                                        </div>
                                        <button type="submit" class="btn btn-warning text-dark fw-bold w-100 py-2 shadow-sm">
                                            <i class="bi bi-lock-fill me-1"></i>確認送出並建立訂單
                                        </button>
                                        <div class="text-center text-white-50 mt-2" style="font-size: 0.72rem;">
                                            點擊送出即代表同意服務條款，課程與預約將自動保留名額
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
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
</style>

<?php include_once '5sl_include_footer.php'; ?>

<script>
$(function() {
    const maxMemberPts = <?php echo $member_pts; ?>;

    // 1. 即時重算結帳金額
    function recalculateCart() {
        let subtotal = 0;
        $('.cart-row-item').each(function() {
            const unitPrice = parseInt($(this).attr('data-unit-price'), 10) || 0;
            const qty = parseInt($(this).find('.input-cart-qty').val(), 10) || 1;
            const rowSub = unitPrice * qty;
            $(this).find('.row-subtotal').text(rowSub.toLocaleString());
            subtotal += rowSub;
        });

        const couponDisc = parseInt($('#selectCheckoutCoupon option:selected').attr('data-discount'), 10) || 0;
        const maxDeduct  = Math.max(0, subtotal - couponDisc);
        let usePts       = parseInt($('#inputUsePts').val(), 10) || 0;

        if (usePts < 0) usePts = 0;
        if (usePts > maxMemberPts) usePts = maxMemberPts;
        if (usePts > maxDeduct) usePts = maxDeduct;
        $('#inputUsePts').val(usePts);

        const finalTotal = Math.max(0, subtotal - couponDisc - usePts);

        $('#summarySubtotal').text(subtotal.toLocaleString());
        $('#summaryCoupon').text(couponDisc.toLocaleString());
        $('#summaryPts').text(usePts.toLocaleString());
        $('#summaryFinal').text(finalTotal.toLocaleString());
    }

    // 2. 數量加減與移除購物車項目 (同步呼叫 act_cart_update)
    $(document).on('click', '.btn-qty-plus, .btn-qty-minus', function() {
        const $row   = $(this).closest('.cart-row-item');
        const cartId = $row.attr('data-cart-id');
        const $input = $row.find('.input-cart-qty');
        let qty      = parseInt($input.val(), 10) || 1;

        qty = $(this).hasClass('btn-qty-plus') ? (qty + 1) : Math.max(1, qty - 1);
        $input.val(qty);
        recalculateCart();

        $.post('./funcFiles/5sl_act_member.php', { action: 'act_cart_update', op: 'qty', cart_id: cartId, qty: qty }, 'json');
    });

    $(document).on('click', '.btn-remove-cart', function() {
        const $row   = $(this).closest('.cart-row-item');
        const cartId = $row.attr('data-cart-id');

        $.post('./funcFiles/5sl_act_member.php', { action: 'act_cart_update', op: 'remove', cart_id: cartId }, function(res) {
            if (res.success === 1) {
                // 同步更新導航列選單內的購物車品項筆數徽章
                if (res.cart_count > 0) {
                    $('.nav-cart-badge').text(res.cart_count).removeClass('d-none');
                } else {
                    $('.nav-cart-badge').text('0').addClass('d-none');
                }

                $row.fadeOut(200, function() {
                    $(this).remove();
                    if ($('.cart-row-item').length === 0) {
                        window.location.reload();
                    } else {
                        recalculateCart();
                    }
                });
            }
        }, 'json');
    });


    // 3. 優惠券、點數全額折抵與三聯式發票欄位切換
    $('#selectCheckoutCoupon, #inputUsePts').on('change input', recalculateCart);

    $('#btnUseAllPts').on('click', function() {
        $('#inputUsePts').val(maxMemberPts);
        recalculateCart();
    });

    $('#selectInvType').on('change', function() {
        if ($(this).val() === '2') {
            $('#companyTaxBox').removeClass('d-none');
            $('#carrierBox').addClass('d-none');
        } else {
            $('#companyTaxBox').addClass('d-none');
            $('#carrierBox').removeClass('d-none');
        }
    });

    // 4. 一鍵送出結帳表單 (呼叫 5sl_act_member.php -> act_checkout)
    $('#formExpressCheckout').on('submit', function(e) {
        e.preventDefault();
        if (window.fslLoader) window.fslLoader.show();

        const formData = $(this).serialize() + '&action=act_checkout';
        $.post('./funcFiles/5sl_act_member.php', formData, function(res) {
            if (window.fslLoader) window.fslLoader.hide();
            if (res.success === 1) {
                alert(`${res.message}\n即將為您跳轉至「我的訂購」檢視訂單與電子發票明細！`);
                window.location.href = '5sl_myOrders.php';
            } else {
                const $box = $('#cartAlertBox');
                $box.removeClass('d-none alert-success').addClass('d-flex alert-danger');
                $('#cartAlertIcon').addClass('bi-exclamation-triangle-fill');
                $('#cartAlertMsg').text(res.message || '結帳處理失敗，請確認欄位後再試。');
                $('html, body').animate({ scrollTop: $box.offset().top - 120 }, 300);
            }
        }, 'json').fail(function() {
            if (window.fslLoader) window.fslLoader.hide();
            alert('網路連線異常，請稍後再試。');
        });
    });
});
</script>
