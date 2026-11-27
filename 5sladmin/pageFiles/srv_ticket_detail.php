<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
define('PAGE_MARK', 'PG_SRV_TICKET');
define('PAGE_ITEM', 'ITEM_SRV');
define('PAGE_OPANEL', 0);
define('DRAWER_OPANEL', 0);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$csrfToken = function_exists('_fn_genToken') ? _fn_genToken() : ($_SESSION['token'] ?? '');
$tktID     = (int)($_GET['currID'] ?? 0);
$myAdId    = (int)($_SESSION['SESS_AD_ID'] ?? 0);

if ($tktID <= 0) {
    header('Location: srv_ticket_list.php');
    exit();
}

$tkt = DB->row("
  SELECT t.*,
         m.mb_name, m.mb_email, m.mb_cellphone, m.mb_level,
         o.ord_no, o.ord_total, o.ord_currency, o.ord_status, o.ord_paid_at,
         i.inv_id, i.inv_no, i.inv_status,
         a.ad_name AS handler_name
  FROM fsl_service_tickets t
  LEFT JOIN fsl_members m ON t.mb_id = m.mb_id
  LEFT JOIN fsl_orders o ON t.ord_id = o.ord_id
  LEFT JOIN fsl_invoices i ON t.ord_id = i.ord_id
  LEFT JOIN fsl_administrator a ON t.ad_id = a.ad_id
  WHERE t.tkt_id = ?
", [$tktID]);

if (!$tkt) {
    header('Location: srv_ticket_list.php');
    exit();
}

// 載入所有回覆與系統處理歷程（含內部交接備註）
$replies = DB->query("
  SELECT * FROM fsl_ticket_replies
  WHERE tkt_id = ?
  ORDER BY rep_created ASC, rep_id ASC
", [$tktID]) ?: [];

// 載入管理員清單供轉派負責人
$admins = DB->query("SELECT ad_id, ad_name FROM fsl_administrator WHERE ad_status = 1 ORDER BY ad_id ASC") ?: [];

$catLabels = [
    'course'  => '課程諮詢與改期',
    'refund'  => '退費與帳務',
    'invoice' => '電子發票修改',
    'system'  => '系統操作問題',
    'general' => '其他服務諮詢'
];
$ordStaMap = [
    1 => '待付款',
    2 => '已付款處理中',
    3 => '實體已出貨',
    4 => '已完成',
    5 => '退費申請中',
    6 => '已退費',
    9 => '已取消'
];
?>
<!doctype html>
<html lang="zh-Hant-TW">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fifth Season Life 人生第五季 | 處理工單 <?php echo htmlspecialchars($tkt['tkt_no']); ?></title>
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
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item prevLink" style="cursor:pointer;"><a href="srv_ticket_list.php">服務工單接收中心</a></li>
                    <li class="breadcrumb-item active font-monospace fw-bold"><?php echo htmlspecialchars($tkt['tkt_no']); ?></li>
                </ol>
            </nav>

            <div class="d-flex gap-2">
                <?php if ((int)$tkt['ad_id'] !== $myAdId): ?>
                    <button type="button" class="btn btn-warning btn-sm fw-bold" id="btnClaimNow">
                        <i class="bi bi-hand-index-thumb me-1"></i>由我認領接手
                    </button>
                <?php endif; ?>
                <a href="srv_ticket_list.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>返回工單列表
                </a>
            </div>
        </div>

        <div class="row g-3">
            <!-- 左側 8 欄：問題原始描述、雙向溝通歷程與回覆工作區 -->
            <div class="col-lg-8">
                <!-- 1. 會員原始立案問題卡片 -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge text-bg-primary me-1"><?php echo htmlspecialchars($catLabels[$tkt['tkt_category']] ?? '其他'); ?></span>
                            <span class="fw-bold fs-6"><?php echo htmlspecialchars($tkt['tkt_subject']); ?></span>
                        </div>
                        <small class="text-muted">立案時間：<?php echo htmlspecialchars($tkt['tkt_created']); ?></small>
                    </div>
                    <div class="card-body">
                        <div class="p-3 bg-light rounded-2 border" style="white-space: pre-wrap;"><?php echo htmlspecialchars($tkt['tkt_content']); ?></div>
                    </div>
                </div>

                <!-- 2. 雙向處理與回覆歷程時間軸 -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2 fw-bold">
                        <i class="bi bi-chat-left-dots text-primary me-1"></i>案件處理歷程與雙向溝通紀錄（共 <?php echo count($replies); ?> 則）
                    </div>
                    <div class="card-body d-flex flex-column gap-3" style="max-height: 460px; overflow-y: auto;">
                        <?php if (empty($replies)): ?>
                            <div class="text-center text-muted py-4 small">尚無回覆紀錄</div>
                            <?php else: foreach ($replies as $rep):
                                $sType = (int)$rep['sender_type'];
                                $isInt = ((int)$rep['rep_is_internal'] === 1);
                            ?>
                                <?php if ($sType === 3): ?>
                                    <div class="text-center">
                                        <span class="badge rounded-pill text-bg-light border text-muted fw-normal px-3 py-1">
                                            <i class="bi bi-clock-history me-1"></i><?php echo htmlspecialchars($rep['rep_message']); ?>（<?php echo substr($rep['rep_created'], 5, 11); ?>）
                                        </span>
                                    </div>
                                <?php elseif ($isInt): ?>
                                    <div class="p-3 rounded-3 border border-warning bg-warning-subtle">
                                        <div class="d-flex justify-content-between small fw-bold text-warning-emphasis mb-1">
                                            <span><i class="bi bi-lock-fill me-1"></i>[內部交接備註 - 前台不可見] <?php echo htmlspecialchars($rep['sender_name']); ?></span>
                                            <span><?php echo substr($rep['rep_created'], 0, 16); ?></span>
                                        </div>
                                        <div style="white-space: pre-wrap;"><?php echo htmlspecialchars($rep['rep_message']); ?></div>
                                    </div>
                                <?php elseif ($sType === 1): ?>
                                    <div class="p-3 rounded-3 border bg-white me-lg-5">
                                        <div class="d-flex justify-content-between small fw-bold text-dark mb-1">
                                            <span><i class="bi bi-person-circle text-secondary me-1"></i>會員：<?php echo htmlspecialchars($rep['sender_name']); ?></span>
                                            <span class="text-muted"><?php echo substr($rep['rep_created'], 0, 16); ?></span>
                                        </div>
                                        <div style="white-space: pre-wrap;"><?php echo htmlspecialchars($rep['rep_message']); ?></div>
                                    </div>
                                <?php else: ?>
                                    <div class="p-3 rounded-3 border border-primary-subtle bg-primary-subtle ms-lg-5">
                                        <div class="d-flex justify-content-between small fw-bold text-primary mb-1">
                                            <span><i class="bi bi-headset me-1"></i>客服專員：<?php echo htmlspecialchars($rep['sender_name']); ?></span>
                                            <span><?php echo substr($rep['rep_created'], 0, 16); ?></span>
                                        </div>
                                        <div style="white-space: pre-wrap;"><?php echo htmlspecialchars($rep['rep_message']); ?></div>
                                    </div>
                                <?php endif; ?>
                        <?php endforeach;
                        endif; ?>
                    </div>
                </div>

                <!-- 3. 發送回覆 / 內部備註表單 -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                        <span class="fw-bold"><i class="bi bi-reply-fill text-success me-1"></i>撰寫回覆或內部交接紀錄</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="chkInternalNote">
                            <label class="form-check-label small fw-bold text-warning-emphasis" for="chkInternalNote">
                                <i class="bi bi-lock-fill"></i> 設為內部交接備註（不通知會員）
                            </label>
                        </div>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control mb-3" id="rep_message" rows="4" placeholder="請輸入給會員的處理回覆，或勾選上方「內部交接備註」記錄跨部門處理進度..."></textarea>
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <label class="small text-muted text-nowrap mb-0">送出後狀態變更為：</label>
                                <select class="form-select form-select-sm w-auto" id="selNextStatus">
                                    <option value="">維持原狀態</option>
                                    <option value="1" selected>1 - 處理中</option>
                                    <option value="2">2 - 待會員補充回覆</option>
                                    <option value="3">3 - 已結案完成</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm fw-bold px-4" id="btnSendReply">
                                <i class="bi bi-send me-1"></i>送出回覆並通知會員
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 右側 4 欄：會員資訊、關聯訂單/發票快捷與工單屬性控制 -->
            <div class="col-lg-4">
                <!-- 申請會員資訊卡 -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2 fw-bold">
                        <i class="bi bi-person-vcard text-primary me-1"></i>申請會員資料
                    </div>
                    <div class="card-body small">
                        <div class="mb-1"><strong>姓名：</strong><?php echo htmlspecialchars(_fn_safe_dec_name($tkt['mb_name']) ?: ('會員 #' . $tkt['mb_id'])); ?></div>
                        <div class="mb-1"><strong>Email：</strong><?php echo htmlspecialchars(_fn_safe_dec_name($tkt['mb_email']) ?: '—'); ?></div>
                        <div class="mb-2"><strong>電話：</strong><?php echo htmlspecialchars(_fn_safe_dec_name($tkt['mb_cellphone']) ?: '—'); ?></div>
                        <a href="mb_detail.php?currID=<?php echo (int)$tkt['mb_id']; ?>" class="btn btn-outline-primary btn-xs w-100">
                            <i class="bi bi-box-arrow-up-right me-1"></i>開啟會員完整檔案
                        </a>
                    </div>
                </div>

                <?php if (!empty($tkt['ord_id'])): ?>
                    <div class="card border-0 shadow-sm mb-3 border-start border-4 border-danger">
                        <div class="card-header bg-white py-2 fw-bold">
                            <i class="bi bi-bag-check text-danger me-1"></i>關聯訂單與發票明細
                        </div>
                        <div class="card-body small">
                            <div class="mb-1"><strong>訂單編號：</strong><span class="font-monospace fw-bold"><?php echo htmlspecialchars($tkt['ord_no']); ?></span></div>
                            <div class="mb-1"><strong>實付金額：</strong><?php echo htmlspecialchars($tkt['ord_currency'] ?: 'TWD'); ?> <?php echo number_format((float)$tkt['ord_total']); ?></div>
                            <div class="mb-1"><strong>訂單狀態：</strong><span class="badge text-bg-warning text-dark"><?php echo $ordStaMap[(int)$tkt['ord_status']] ?? '處理中'; ?></span></div>
                            <div class="mb-2"><strong>電子發票：</strong><span class="font-monospace"><?php echo htmlspecialchars($tkt['inv_no'] ?: '尚未開立'); ?></span></div>
                            <div class="d-grid gap-1">
                                <a href="fin_invoice_list.php?kw=<?php echo urlencode($tkt['ord_no']); ?>" class="btn btn-outline-danger btn-xs">
                                    <i class="bi bi-receipt-cutoff me-1"></i>前往發票折讓 / 作廢退費
                                </a>
                                <a href="fin_ledger_detail.php?currID=0" class="btn btn-outline-secondary btn-xs">
                                    <i class="bi bi-journal-plus me-1"></i>前往登錄退款支出傳票
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2 fw-bold">
                        <i class="bi bi-sliders text-secondary me-1"></i>工單屬性與結案管理
                    </div>
                    <div class="card-body small">
                        <div class="mb-2">
                            <label class="form-label fw-semibold mb-1">目前狀態</label>
                            <select class="form-select form-select-sm" id="edit_tkt_status">
                                <option value="0" <?php echo (int)$tkt['tkt_status'] === 0 ? 'selected' : ''; ?>>0 - 待受理</option>
                                <option value="1" <?php echo (int)$tkt['tkt_status'] === 1 ? 'selected' : ''; ?>>1 - 處理中</option>
                                <option value="2" <?php echo (int)$tkt['tkt_status'] === 2 ? 'selected' : ''; ?>>2 - 待會員回覆</option>
                                <option value="3" <?php echo (int)$tkt['tkt_status'] === 3 ? 'selected' : ''; ?>>3 - 已結案完成</option>
                                <option value="9" <?php echo (int)$tkt['tkt_status'] === 9 ? 'selected' : ''; ?>>9 - 已取消作廢</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold mb-1">優先等級</label>
                            <select class="form-select form-select-sm" id="edit_tkt_priority">
                                <option value="1" <?php echo (int)$tkt['tkt_priority'] === 1 ? 'selected' : ''; ?>>1 - 一般</option>
                                <option value="2" <?php echo (int)$tkt['tkt_priority'] === 2 ? 'selected' : ''; ?>>2 - 急件</option>
                                <option value="3" <?php echo (int)$tkt['tkt_priority'] === 3 ? 'selected' : ''; ?>>3 - 特急 (退費/客訴)</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold mb-1">指派負責專員</label>
                            <select class="form-select form-select-sm" id="edit_tkt_ad_id">
                                <option value="">-- 尚未指派 --</option>
                                <?php foreach ($admins as $adm): ?>
                                    <option value="<?php echo (int)$adm['ad_id']; ?>" <?php echo (int)$tkt['ad_id'] === (int)$adm['ad_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($adm['ad_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-1">案件總結備註（僅內部可見）</label>
                            <textarea class="form-control form-control-sm" rows="3" id="edit_tkt_internal_note"><?php echo htmlspecialchars($tkt['tkt_internal_note'] ?? ''); ?></textarea>
                        </div>

                        <button type="button" class="btn btn-dark btn-sm w-100 fw-bold" id="btnSaveTicketMeta">
                            <i class="bi bi-save me-1"></i>儲存工單屬性變更
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
    <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
    <script src="../scriptFiles/page.basic.js"></script>
    <script src="../scriptFiles/iframe-state-keeper.js"></script>
    <script>
        const CSRF_TOKEN = '<?php echo htmlspecialchars($csrfToken, ENT_QUOTES); ?>';
        const TKT_ID = <?php echo $tktID; ?>;

        function doReDirect() {
            window.history.back();
        }

        function doButReset() {
            window.location.replace('srv_ticket_datail.php');
        }

        $(document).ready(function() {
            // 切換公開回覆 / 內部備註時的按鈕文字提示
            $('#chkInternalNote').on('change', function() {
                const isInt = $(this).is(':checked');
                $('#btnSendReply')
                    .toggleClass('btn-primary', !isInt)
                    .toggleClass('btn-warning', isInt)
                    .html(isInt ?
                        '<i class="bi bi-lock-fill me-1"></i>儲存內部交接備註' :
                        '<i class="bi bi-send me-1"></i>送出回覆並通知會員'
                    );
            });

            // 由我認領接手
            $('#btnClaimNow').on('click', function() {
                $.post('../funcFiles/srv_act_func.php', {
                    formAcce: 'func_srv_ticket_assign',
                    token: CSRF_TOKEN,
                    tktID: TKT_ID
                }, function(res) {
                    if (window.parent && window.parent.toastr) window.parent.toastr[res.msgType](res.msgStr);
                    if (res.RTN == 1) location.reload();
                }, 'json');
            });

            // 送出回覆或內部備註
            $('#btnSendReply').on('click', function() {
                const msg = ($('#rep_message').val() || '').trim();
                if (!msg) {
                    alert('請先輸入回覆內容！');
                    return;
                }
                $.post('../funcFiles/srv_act_func.php', {
                    formAcce: 'func_srv_ticket_reply',
                    token: CSRF_TOKEN,
                    tktID: TKT_ID,
                    rep_message: msg,
                    rep_is_internal: $('#chkInternalNote').is(':checked') ? 1 : 0,
                    next_status: $('#selNextStatus').val()
                }, function(res) {
                    if (window.parent && window.parent.toastr) window.parent.toastr[res.msgType](res.msgStr);
                    if (res.RTN == 1) location.reload();
                }, 'json');
            });

            // 儲存右側工單屬性
            $('#btnSaveTicketMeta').on('click', function() {
                $.post('../funcFiles/srv_act_func.php', {
                    formAcce: 'func_srv_ticket_update',
                    token: CSRF_TOKEN,
                    tktID: TKT_ID,
                    tkt_status: $('#edit_tkt_status').val(),
                    tkt_priority: $('#edit_tkt_priority').val(),
                    ad_id: $('#edit_tkt_ad_id').val(),
                    tkt_internal_note: $('#edit_tkt_internal_note').val()
                }, function(res) {
                    if (window.parent && window.parent.toastr) window.parent.toastr[res.msgType](res.msgStr);
                    if (res.RTN == 1) location.reload();
                }, 'json');
            });

            $('li.prevLink').on('click', function() {
                window.location.replace('srv_ticket_list.php');
            });

            if (window.parent && typeof window.parent.treeviewCheck === 'function') {
                window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
            }
        });
    </script>
</body>

</html>