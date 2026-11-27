<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
define('PAGE_MARK', 'PG_FIN_LEDGER');
define('PAGE_ITEM', 'ITEM_FIN');
define('PAGE_OPANEL', 2); // 2-編輯儲存模式
define('DRAWER_OPANEL', 0);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$csrfToken = function_exists('_fn_genToken') ? _fn_genToken() : ($_SESSION['token'] ?? '');
$ledID     = (int)($_GET['currID'] ?? 0);

$row = [
  'led_tx_date'      => date('Y-m-d'),
  'led_no'           => '系統自動產生 (FIN-YYYYMMDD-XXXX)',
  'led_dir'          => 1,
  'led_source'       => 3,
  'led_category'     => 'course_fee',
  'ord_id'           => '',
  'inv_id'           => '',
  'mb_id'            => '',
  'led_pay_channel'  => 'BankTransfer',
  'led_currency'     => 'TWD',
  'led_amount'       => '',
  'led_counterparty' => '',
  'led_summary'      => '',
  'led_voucher_ref'  => '',
  'led_status'       => 1
];

if ($ledID > 0) {
  $dbRow = DB->row("SELECT * FROM fsl_fin_ledger WHERE led_id = ?", [$ledID]);
  if ($dbRow) {
    $row = array_merge($row, $dbRow);
  } else {
    header('Location: fin_ledger_list.php');
    exit();
  }
}

// 載入近期有效訂單供快速關聯（含退費申請中 ord_status=5 的訂單優先排前）
$recentOrders = DB->query("
  SELECT o.ord_id, o.ord_no, o.mb_id, o.ord_total, o.ord_currency, o.ord_status,
         COALESCE(m.mb_name, o.ord_receiver_name, '') AS member_name,
         i.inv_id, i.inv_no
  FROM fsl_orders o
  LEFT JOIN fsl_members m ON o.mb_id = m.mb_id
  LEFT JOIN fsl_invoices i ON o.ord_id = i.ord_id
  WHERE o.ord_status != 9
  ORDER BY (o.ord_status = 5) DESC, o.ord_id DESC
  LIMIT 100
") ?: [];
?>
<!doctype html>
<html lang="zh-Hant-TW">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Fifth Season Life 人生第五季 | <?php echo $ledID > 0 ? '編輯會計傳票' : '登錄線下收支與退款傳票'; ?></title>
  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>

<body class="bg-body-tertiary theme-cyan p-2">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1">
    <i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?>
  </div>

  <div class="container-fluid py-2">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item prevLink" style="cursor:pointer;"><a href="fin_ledger_list.php">進出帳總流水帳</a></li>
        <li class="breadcrumb-item active"><?php echo $ledID > 0 ? '編輯傳票：' . htmlspecialchars($row['led_no']) : '新增線下收支 / 訂單退款傳票'; ?></li>
      </ol>
    </nav>

    <form id="form_tabMenu1" data-func="func_fin_ledger_update" class="needs-validation" novalidate>
      <div class="row g-3">
        <!-- 左側：收支方向、金額與會計科目 -->
        <div class="col-lg-7">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-2 fw-bold">
              <i class="bi bi-journal-plus text-primary me-1"></i>收支傳票核心明細
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">傳票單號</label>
                  <input type="text" class="form-control bg-light font-monospace" value="<?php echo htmlspecialchars($row['led_no']); ?>" readonly>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold" for="led_tx_date">會計入帳日期 <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="led_tx_date" name="led_tx_date" value="<?php echo htmlspecialchars($row['led_tx_date']); ?>" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-semibold" for="led_dir">收支方向 <span class="text-danger">*</span></label>
                  <select class="form-select" id="led_dir" name="led_dir" required>
                    <option value="1" <?php echo (int)$row['led_dir'] === 1  ? 'selected' : ''; ?>>收入 (+ 進帳)</option>
                    <option value="-1" <?php echo (int)$row['led_dir'] === -1 ? 'selected' : ''; ?>>支出 / 退款 (- 出帳)</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-semibold" for="led_source">帳務來源類型 <span class="text-danger">*</span></label>
                  <select class="form-select" id="led_source" name="led_source" required>
                    <option value="3" <?php echo (int)$row['led_source'] === 3 ? 'selected' : ''; ?>>3 - 線下收入 (現金/銀行匯款)</option>
                    <option value="4" <?php echo (int)$row['led_source'] === 4 ? 'selected' : ''; ?>>4 - 線下營運支出 (講師/場地/雜支)</option>
                    <option value="2" <?php echo (int)$row['led_source'] === 2 ? 'selected' : ''; ?>>2 - 訂單退款支出</option>
                    <option value="1" <?php echo (int)$row['led_source'] === 1 ? 'selected' : ''; ?>>1 - 線上金流補登收款</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-semibold" for="led_category">會計科目分類 <span class="text-danger">*</span></label>
                  <select class="form-select" id="led_category" name="led_category" required>
                    <option value="course_fee" <?php echo $row['led_category'] === 'course_fee' ? 'selected' : ''; ?>>學費與商品收入 (course_fee)</option>
                    <option value="refund" <?php echo $row['led_category'] === 'refund'     ? 'selected' : ''; ?>>退費支出 (refund)</option>
                    <option value="lecturer" <?php echo $row['led_category'] === 'lecturer'   ? 'selected' : ''; ?>>講師鐘點費 (lecturer)</option>
                    <option value="venue" <?php echo $row['led_category'] === 'venue'      ? 'selected' : ''; ?>>場地設備費 (venue)</option>
                    <option value="marketing" <?php echo $row['led_category'] === 'marketing'  ? 'selected' : ''; ?>>行銷宣傳雜支 (marketing)</option>
                    <option value="other" <?php echo $row['led_category'] === 'other'      ? 'selected' : ''; ?>>其他收支 (other)</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-semibold" for="led_pay_channel">收付管道 <span class="text-danger">*</span></label>
                  <select class="form-select" id="led_pay_channel" name="led_pay_channel" required>
                    <option value="BankTransfer" <?php echo $row['led_pay_channel'] === 'BankTransfer' ? 'selected' : ''; ?>>銀行匯款 / 轉帳</option>
                    <option value="Cash" <?php echo $row['led_pay_channel'] === 'Cash'         ? 'selected' : ''; ?>>現金收付</option>
                    <option value="ECPay" <?php echo $row['led_pay_channel'] === 'ECPay'        ? 'selected' : ''; ?>>綠界 ECPay</option>
                    <option value="NewebPay" <?php echo $row['led_pay_channel'] === 'NewebPay'     ? 'selected' : ''; ?>>藍新 NewebPay</option>
                    <option value="LinePay" <?php echo $row['led_pay_channel'] === 'LinePay'      ? 'selected' : ''; ?>>LINE Pay</option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label fw-semibold" for="led_currency">幣別</label>
                  <select class="form-select" id="led_currency" name="led_currency">
                    <option value="TWD" <?php echo $row['led_currency'] === 'TWD' ? 'selected' : ''; ?>>TWD (新台幣)</option>
                    <option value="USD" <?php echo $row['led_currency'] === 'USD' ? 'selected' : ''; ?>>USD (美元)</option>
                  </select>
                </div>

                <div class="col-md-8">
                  <label class="form-label fw-semibold" for="led_amount">交易金額（請填正數） <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text fw-bold">$</span>
                    <input type="number" step="0.01" min="1" class="form-control fw-bold" id="led_amount" name="led_amount" value="<?php echo htmlspecialchars((string)$row['led_amount']); ?>" placeholder="0.00" required>
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label fw-semibold" for="led_summary">會計摘要說明 <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="led_summary" name="led_summary" value="<?php echo htmlspecialchars($row['led_summary']); ?>" placeholder="例如：企業包班線下匯款尾款、王大明老師11月課程鐘點費..." required>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 右側：關聯訂單、交易對象與憑證核帳 -->
        <div class="col-lg-5">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-2 fw-bold">
              <i class="bi bi-link-45deg text-success me-1"></i>關聯訂單、憑證與核帳狀態
            </div>
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label fw-semibold" for="selQuickOrder">快速帶入關聯訂單（選填，退費或線下收款適用）</label>
                <select class="form-select form-select-sm" id="selQuickOrder" name="ord_id">
                  <option value="">-- 無關聯訂單（一般線下收支） --</option>
                  <?php foreach ($recentOrders as $ord):
                    $isRefReq = ((int)$ord['ord_status'] === 5);
                    $selAttr  = ((int)$row['ord_id'] === (int)$ord['ord_id']) ? 'selected' : '';
                  ?>
                    <option value="<?php echo (int)$ord['ord_id']; ?>"
                      data-mbid="<?php echo (int)$ord['mb_id']; ?>"
                      data-invid="<?php echo (int)($ord['inv_id'] ?? 0); ?>"
                      data-name="<?php echo htmlspecialchars(_fn_safe_dec_name($ord['member_name'])); ?>"
                      data-amount="<?php echo htmlspecialchars((string)$ord['ord_total']); ?>"
                      data-ordno="<?php echo htmlspecialchars($ord['ord_no']); ?>"
                      data-invno="<?php echo htmlspecialchars($ord['inv_no'] ?? ''); ?>"
                      <?php echo $selAttr; ?>>
                      <?php echo ($isRefReq ? '【退費申請中】' : '') . htmlspecialchars($ord['ord_no']) . '｜' . htmlspecialchars(_fn_safe_dec_name($ord['member_name'])) . '（$' . number_format((float)$ord['ord_total']) . '）'; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <input type="hidden" id="mb_id" name="mb_id" value="<?php echo htmlspecialchars((string)$row['mb_id']); ?>">
                <input type="hidden" id="inv_id" name="inv_id" value="<?php echo htmlspecialchars((string)$row['inv_id']); ?>">
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold" for="led_counterparty">交易對象 / 會員姓名 / 廠商抬頭</label>
                <input type="text" class="form-control" id="led_counterparty" name="led_counterparty" value="<?php echo htmlspecialchars(_fn_safe_dec_name($row['led_counterparty']) ?? ''); ?>" placeholder="輸入收付款對象姓名或公司名稱">
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold" for="led_voucher_ref">外部憑證 / 匯款帳號後五碼 / 發票字軌</label>
                <input type="text" class="form-control font-monospace" id="led_voucher_ref" name="led_voucher_ref" value="<?php echo htmlspecialchars($row['led_voucher_ref'] ?? ''); ?>" placeholder="如：帳號後五碼 88321 或發票 EF-99887766">
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold" for="led_status">核帳入帳狀態</label>
                <select class="form-select" id="led_status" name="led_status">
                  <option value="1" <?php echo (int)$row['led_status'] === 1 ? 'selected' : ''; ?>>1 - 已核帳入帳（計入結算總額）</option>
                  <option value="0" <?php echo (int)$row['led_status'] === 0 ? 'selected' : ''; ?>>0 - 待核帳（尚未確認款項）</option>
                  <option value="9" <?php echo (int)$row['led_status'] === 9 ? 'selected' : ''; ?>>9 - 已作廢沖銷</option>
                </select>
              </div>

              <div class="alert alert-light border small text-muted mb-0">
                <i class="bi bi-info-circle text-primary me-1"></i>
                若帳務來源選擇「<strong>2 - 訂單退款支出</strong>」並關聯訂單，儲存時系統將自動把該筆訂單狀態更新為「<strong>已退費</strong>」。
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t=' . time(); ?>"></script>
  <script>
    const LED_ID = <?php echo $ledID; ?>;

    function doReDirect() {
      window.location.replace('fin_ledger_list.php');
    }

    function doButSave() {
      const $frm = $('#form_tabMenu1');
      if (!$frm.length) return false;
      const formEl = $frm.get(0);
      if (!formEl.checkValidity()) {
        formEl.classList.add('was-validated');
        if (window.parent && window.parent.toastr) {
          window.parent.toastr['warning']('請檢查標示紅框的必填欄位！');
        }
        return false;
      }
      ajaxPOST($frm);
    }

    function doButDelete() {
      if (LED_ID <= 0) return false;
      $.post('../funcFiles/fin_act_func.php', {
        formAcce: 'func_fin_ledger_delete',
        token: getTokenVal(),
        ledID: LED_ID
      }, function(data) {
        if (data.RTN == 1) {
          if (window.parent && window.parent.toastr) window.parent.toastr['success'](data.msgStr);
          window.location.replace('fin_ledger_list.php');
        } else {
          if (window.parent && window.parent.toastr) window.parent.toastr['error'](data.msgStr || '作廢失敗');
        }
      }, 'json');
    }

    function doButReset() {
      location.reload(true);
    }

    function ajaxPOST($frm) {
      const accE = (LED_ID === 0) ? 'func_fin_ledger_addnew' : $frm.data('func');
      const datA = getFormObjects($frm);

      $.ajax({
        type: 'post',
        url: '../funcFiles/fin_act_func.php',
        data: {
          arrDatA: datA,
          formAcce: accE,
          token: getTokenVal(),
          ledID: LED_ID
        },
        dataType: 'json',
        success: function(data) {
          if (window.parent && window.parent.toastr) {
            window.parent.toastr[data.msgType](data.msgStr);
          }
          if (data.RTN == 1) {
            if (accE === 'func_fin_ledger_addnew' && data.newID) {
              window.location.replace('fin_ledger_detail.php?currID=' + data.newID);
            } else {
              $frm.removeClass('was-validated');
              if (typeof doSerialize === 'function') doSerialize();
              if (window.parent && window.parent.document) {
                $('#topBut_save', window.parent.document).removeClass('active');
              }
            }
          }
        }
      });
    }

    $(document).ready(function() {
      $('#selQuickOrder').on('change', function() {
        const $opt = $(this).find('option:selected');
        if (!$opt.val()) {
          $('#mb_id').val('');
          $('#inv_id').val('');
          return;
        }
        $('#mb_id').val($opt.data('mbid') || '');
        $('#inv_id').val($opt.data('invid') || '');
        if (!$('#led_counterparty').val()) $('#led_counterparty').val($opt.data('name') || '');
        if (!$('#led_amount').val()) $('#led_amount').val($opt.data('amount') || '');
        if (!$('#led_voucher_ref').val() && $opt.data('invno')) $('#led_voucher_ref').val($opt.data('invno'));
        if (!$('#led_summary').val()) {
          const isRefund = ($('#led_source').val() === '2' || $('#led_dir').val() === '-1');
          $('#led_summary').val((isRefund ? '訂單退費支出（單號：' : '訂單線下收款（單號：') + $opt.data('ordno') + '）');
        }
      });

      $('#led_dir').on('change', function() {
        if ($(this).val() === '-1') {
          if ($('#led_source').val() === '3') $('#led_source').val('4');
          if ($('#led_category').val() === 'course_fee') $('#led_category').val('other');
        } else {
          if ($('#led_source').val() === '4') $('#led_source').val('3');
          $('#led_category').val('course_fee');
        }
      });

      $('li.prevLink').on('click', function() {
        window.location.replace('fin_ledger_list.php');
      });

      if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
      }
    });
  </script>
</body>

</html>