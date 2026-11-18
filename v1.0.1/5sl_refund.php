<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = 'refund';
$page_title   = '退費退款政策｜Fifth Season Life 人生第五季';

// 引入共用頁首與導航列
include_once '5sl_include_header.php';
?>

<!-- =========================================================
     退費退款政策主內容區 (#refundPolicy)
     ========================================================= -->
<section id="refundPolicy" class="py-5 bg-dark text-white position-relative">
    <div class="container py-4" style="max-width: 920px;">

        <!-- 麵包屑導覽 -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-warning text-decoration-none">首頁</a></li>
                <li class="breadcrumb-item active text-white-50" aria-current="page">退費退款政策</li>
            </ol>
        </nav>

        <!-- 區塊主標題 + 全幅前後淡入水平線 -->
        <div class="text-center fsl-header-gap">
            <h1 class="h2 fsl-section-title-box mb-3">退費退款政策</h1>
            <p class="fs-6 text-white-50 mb-0" style="letter-spacing: 1.5px;">
                REFUND POLICY ｜ 最後更新日期：2026 年 11 月
            </p>
            <hr class="fsl-section-divider">
        </div>

        <!-- 前言導讀 -->
        <div class="fsl-policy-intro mb-4">
            <p class="text-white-50 lh-lg mb-0">
                感謝您對「<strong class="text-white">Fifth Season Life 人生第五季</strong>」（人生第五季生命藝術工作室）之信任與支持。為保障您的消費權益並維持課程與陪跑服務品質，我們依據中華民國《消費者保護法》及相關法規，制定以下清晰透明的退費與退款作業規範，請您於報名前詳閱。
            </p>
        </div>

        <!-- 退費退款政策主卡片 -->
        <div class="fsl-policy-card">

            <!-- 第一條 -->
            <div class="fsl-policy-section">
                <h2 class="fsl-policy-heading">
                    <span class="fsl-policy-num">01</span>
                    <span>線上課程與 16 週深度陪跑系統退費標準</span>
                </h2>
                <p>針對「步步向光人生重啟陪跑系統」及「身心之美孵化課程系統」等結合線上影音與陪跑之方案，退費比例依課程開通與實際進度計算如下：</p>
                <ul class="fsl-policy-list">
                    <li><strong>購買後 7 日內且尚未啟用（全額退費）：</strong>自完成付款次日起算 7 日內，若您尚未登入觀看任何付費課程影片、尚未下載數位工具包，且尚未進行一對一陪談者，得申請<strong>全額退費（100%）</strong>。</li>
                    <li><strong>課程開通後未逾總陪跑期程三分之一（退還 50%）：</strong>若已開始觀看課程影音或下載數位工具包，但自開課／開通日起算尚未超過總陪跑週數三分之一（例如 16 週陪跑之第 5 週內），得申請退還實付金額之 <strong>50%</strong>（若已使用一對一深度陪談，需先扣除單次陪談定價費用後計算）。</li>
                    <li><strong>已逾總陪跑期程三分之一（恕不退費）：</strong>自開課／開通日起算已超過總陪跑週數三分之一，或已完成超過三分之一核心課程單元者，恕不受理退費申請。</li>
                </ul>
            </div>

            <!-- 第二條 -->
            <div class="fsl-policy-section">
                <h2 class="fsl-policy-heading">
                    <span class="fsl-policy-num">02</span>
                    <span>一對一深度陪談單次預約之改期與退費</span>
                </h2>
                <ul class="fsl-policy-list">
                    <li><strong>預約時間 7 日前取消：</strong>於原定一對一對談時間 7 日（含）前申請取消者，可退還實付金額之 <strong>90%</strong>（扣除 10% 行政與時段保留手續費），或免費轉為課程抵用金。</li>
                    <li><strong>預約時間 24 小時前改期：</strong>於原定時間<strong>至少 24 小時前</strong>透過會員中心「訊息中心」通知者，享有 <strong>1 次免費改期</strong>權益；若於 24 小時前申請取消不改期，退還實付金額之 <strong>70%</strong>。</li>
                    <li><strong>預約時間 24 小時內或無故缺席：</strong>因專屬導師時段已為您保留，於原定時間 24 小時內臨時取消或未依約出席者，視同該次服務已完成，恕不予退費或補課。</li>
                </ul>
            </div>

            <!-- 第三條 -->
            <div class="fsl-policy-section">
                <h2 class="fsl-policy-heading">
                    <span class="fsl-policy-num">03</span>
                    <span>實體工作坊與生命藝術沙龍退費規範</span>
                </h2>
                <p>因實體活動需提前預訂場地與準備專屬藝術媒材（如和諧粉彩、花磚材料包等），退費標準如下：</p>
                <ul class="fsl-policy-list">
                    <li><strong>活動日 14 日（含）前申請：</strong>退還實付金額之 <strong>90%</strong>。</li>
                    <li><strong>活動日前 7 至 13 日申請：</strong>退還實付金額之 <strong>70%</strong>。</li>
                    <li><strong>活動日前 3 至 6 日申請：</strong>退還實付金額之 <strong>50%</strong>，或可免費將名額轉讓予親友參加（須於活動前 48 小時通知本工作室登記）。</li>
                    <li><strong>活動日前 2 日內及當日未出席：</strong>因材料與場地名額已全數備妥，恕不予退費。</li>
                    <li><strong>主辦單位異動：</strong>若因天災（依人事行政局停班停課標準）或未達開班人數導致活動延期或取消，學員可選擇全額無息退費或順延至下一梯次。</li>
                </ul>
            </div>

            <!-- 第四條 -->
            <div class="fsl-policy-section">
                <h2 class="fsl-policy-heading">
                    <span class="fsl-policy-num">04</span>
                    <span>實體書籍與生命整理工具包退換貨原則</span>
                </h2>
                <ul class="fsl-policy-list">
                    <li><strong>7 日鑑賞期保障：</strong>購買《職場名模》等實體書籍或獨立販售之實體工具盒，享有商品到貨次日起算 7 日鑑賞期（鑑賞期非試用期）。</li>
                    <li><strong>退貨商品狀態要求：</strong>申請退貨之商品必須保持全新未拆封、無劃記折損且包裝完整（含附贈之書籤、卡片或工具配件）；若已拆封使用、沾染污漬或人為損毀，恕無法辦理退貨。</li>
                    <li><strong>瑕疵換貨：</strong>若收到商品有印刷缺頁、裝訂脫落或運送破損情形，請於到貨 7 日內拍照並透過訊息中心聯繫我們，本工作室將免費為您更換新品並負擔來回運費。</li>
                </ul>
            </div>

            <!-- 第五條 -->
            <div class="fsl-policy-section">
                <h2 class="fsl-policy-heading">
                    <span class="fsl-policy-num">05</span>
                    <span>PTS 點數、優惠點券與分期付款之退款處理</span>
                </h2>
                <ul class="fsl-policy-list">
                    <li><strong>點券與贈品返還：</strong>訂單結帳時所折抵之會員 PTS 點數或優惠點券，於全額退費成立後將自動退回您的會員帳戶（有效期限依原點券規定計算）；若訂單包含實體贈品，須將全新未拆封之贈品一併退回，否則將自退款金額中扣除該贈品定價。</li>
                    <li><strong>分期付款訂單退費：</strong>使用信用卡分期付款之訂單，若符合部分退費比例條件，本工作室將依實際應退金額辦理刷退，或協助結清後退還差額，實際入帳時間依各發卡銀行作業週期為準。</li>
                </ul>
            </div>

            <!-- 第六條 -->
            <div class="fsl-policy-section mb-0">
                <h2 class="fsl-policy-heading">
                    <span class="fsl-policy-num">06</span>
                    <span>退費申請流程與退款入帳時間</span>
                </h2>
                <p class="mb-0">
                    欲申請退費者，請登入「會員中心 ＞ 訊息中心」或透過首頁「聯絡我們」表單提交您的<strong>訂單編號、註冊姓名與退費原因</strong>。專人將於收到申請後 <strong>3 個工作日內</strong>完成審核並與您確認退款明細；審核通過後，款項將於 <strong>7 至 14 個工作日內</strong>依原付款方式退回（信用卡刷退將退回原刷卡帳戶，ATM 轉帳則匯入您指定之本人同名銀行帳戶）。
                </p>
            </div>

        </div>

        <!-- 返回首頁按鈕 -->
        <div class="text-center mt-5">
            <a href="index.php" class="btn btn-outline-warning rounded-pill px-4 py-2" style="letter-spacing: 2px;">
                <i class="bi bi-arrow-left me-2"></i>返回首頁
            </a>
        </div>

    </div>
</section>

<!-- Footer 之前留白區 -->
<section class="py-5">
    <p>&nbsp;</p>
</section>

<?php
// 引入共用頁尾與腳本載入區
include_once '5sl_include_footer.php';
?>
