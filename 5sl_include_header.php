<?php
require_once('5sladmin/includeFiles/cli.init.inc.php');
require_once('5sladmin/includeFiles/db.inc.php');
include_once '5sladmin/includeFiles/common.lib.php';
$mb_id = isset($_SESSION['mbID']) ? (int)$_SESSION['mbID'] : (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0);
// =========================================================================
// 計算會員中心「待處理事件總數」($global_todo_count)，供頭像右上角紅標顯示
// =========================================================================
$global_cart_count  = 0;
$global_unread_msg  = 0;
$pending_gifts      = 0;
$pending_quests     = 0;
$global_claim_count = 0;
$global_todo_count  = 0;

if ($mb_id > 0 && isset($DB)) {
    try {
        $mb_lvl = (int)($_SESSION['mbLevel'] ?? $_SESSION['user_lvl'] ?? 0);

        // 1. 購物車件數
        $global_cart_count = (int)$DB->single(
            "SELECT COUNT(*) FROM fsl_cart_items WHERE mb_id = ?",
            [$mb_id]
        );

        // 2. 未讀系統通知（與 5sl_myConsole.php 完全一致：nor_id 為 NULL 代表未讀）
        $unread_ntf = (int)$DB->single(
            "SELECT COUNT(*)
             FROM fsl_notifications n
             LEFT JOIN fsl_notification_reads nr ON n.noti_id = nr.noti_id AND nr.mb_id = ?
             WHERE n.noti_status = 1
               AND (n.noti_target_type = 0 OR (n.noti_target_type = 1 AND n.noti_target_val = ?) OR (n.noti_target_type = 2 AND n.noti_target_val <= ?))
               AND nr.nor_id IS NULL",
            [$mb_id, $mb_id, $mb_lvl]
        );
        $global_unread_msg = $unread_ntf;

        // 3. 待領取禮品 + 已達成待領取任務獎勵
        $pending_gifts = (int)$DB->single(
            "SELECT COUNT(*) FROM fsl_gift_records WHERE mb_id = ? AND grec_status = 0",
            [$mb_id]
        );
        $pending_quests = (int)$DB->single(
            "SELECT COUNT(*) FROM fsl_quest_records WHERE mb_id = ? AND qrec_status = 1",
            [$mb_id]
        );
        $global_claim_count = $pending_gifts + $pending_quests;

        // 4. 全站待處理總數
        $global_todo_count = $global_cart_count + $global_unread_msg + $global_claim_count;
    } catch (Throwable $e) {
        $global_todo_count = $global_cart_count;
    }
}

$mbJS=_fn_getCachedJsonData("./5sladmin/jsonFiles/member.json");
define('MEMBER_GRADE',$mbJS['levels'][$_SESSION['mbLevel'] ?? 0] ?? []);
/**
 * 取得會員專屬頭像網址（優先讀取本地裁切 JPG，無檔案時自動退回預設頭像）
 * @param int  $mbid    會員 ID
 * @param bool $is_thumb true=讀取 100x100 縮圖 (適合側邊欄/導覽列), false=讀取 400x400 主圖
 * @return string 圖片路徑網址
 */
function _fn_getMemberAvatar($mbid, $is_thumb = false) {
    $id = (int)$mbid;
    if ($id > 0) {
        $suffix    = $is_thumb ? '_thumb.jpg' : '_.jpg';
        $file_path = './5sladmin/uploadFiles/mb/mbphoto_' . $id . $suffix;
        if (file_exists($file_path)) {
            // 加上檔案修改時間戳記，確保剛換完大頭貼立刻刷新畫面不卡快取
            return $file_path . '?v=' . filemtime($file_path);
        }
    }
    return 'https://mockmind-api.uifaces.co/content/human/'.($id % 223).'.jpg';
}
function _fn_getResourceCover($res_id, $is_thumb = false, $res_type = 1, $media_url = '') {
    $id     = (int)$res_id;
    $suffix = $is_thumb ? '_thumb.jpg' : '_.jpg';
    $path   = './5sladmin/uploadFiles/re/reimg_' . $id . $suffix;

    if ($id > 0 && file_exists($path)) {
        return $path . '?v=' . filemtime($path);
    }
    // 若為 YouTube 視訊且未另傳封面，自動抓取 YouTube 高畫質縮圖
    if ((int)$res_type === 4 && !empty($media_url)) {
        if (preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $media_url, $m)) {
            return 'https://i.ytimg.com/vi/' . $m[1] . '/hqdefault.jpg';
        }
    }
    // 預設本地風格圖
    $fallback_idx = ($id % 18) + 1;
    return 'imageFiles/test/' . $fallback_idx . '.jpg';
}

?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./imageFiles/favicon.ico" type="image/ico" />
    <title>Fifth Season Life 人生第五季 | 首頁</title>
    <!-- 本地端外掛載入 -->
    <link href="pluginFiles/bootstrap5/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="pluginFiles/googleapis/fonts.googleapis.css">
    <!-- 💡 核心新增：本地端 Bootstrap 圖示字型檔 -->
    <link rel="stylesheet" href="pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
    <!-- flatpickr -->
    <link rel="stylesheet" href="pluginFiles/flatpickr/flatpickr.min.css">
    <!-- 客製化 CSS -->
    <link rel="stylesheet" href="cssFiles/5seasonlife.css">
    <link rel="stylesheet" href="cssFiles/5sl_EpicEngine.css">
    <link rel="stylesheet" href="cssFiles/5sl_LotteryWheel.css">
    
</head>
<body>
    <!-- 1. 頂端哨兵（用來精準偵測網頁是否離開最頂端） -->
    <div id="top-sentinel" style="position: absolute; top: 0; left: 0; width: 100%; height: 1px; visibility: hidden;"></div>
    <!-- Header 導航列 -->
    <!-- Header 導航列（還原最乾淨原生結構，絕不撐寬破壞排版） -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center py-0" href="index.php">
                <img src="./imageFiles/5slLOGO_W_100x100.png" alt="5SL LOGO" style="max-height: 40px; width: auto; object-fit: contain; margin-right: 20px;" onerror="this.style.display='none';">
                <div class="d-flex flex-column lh-1">
                    <span class="brand-text-en fw-bold">
                        F<span class="fw-light" style="font-weight:200 !important;font-size:0.85em;">ifth </span>
                        S<span class="fw-light" style="font-weight:200 !important;font-size:0.85em;">eason </span>
                        L<span class="fw-light" style="font-weight:200 !important;font-size:0.85em;">ife</span><br>
                    </span>
                    <span class="brand-text-zh mt-1 text-secondary" style="font-size:0.56em;">|人生第五季生命藝術工作室</span>
                </div>                
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                    
                    <!-- 手機版專用大頭像區塊 -->
                    <li class="nav-item text-center d-lg-none my-4" id="mobileAvatarZone">
                        <?php if (isset($_SESSION['mbID'])): ?>
                            <div class="py-2 border-bottom border-secondary mb-3">
                                <div class="position-relative d-inline-block mb-2">
                                    <img src="<?php echo _fn_getMemberAvatar($mb_id, true); ?>" class="user-avatar-lg" alt="User Large Avatar">
                                </div>
                                <div class="text-white fw-bold small">歡迎回來，<?php echo $_SESSION['mbTitle'] ?? '會員'; ?></div>
                                <div class="d-flex justify-content-center gap-2 mt-2">
                                    <a href="5sl_myConsole.php" class="btn btn-sm btn-outline-warning py-0 px-2" style="font-size: 0.75rem;">主控台</a>
                                    <a href="5sl_myCart.php" class="btn btn-sm btn-warning text-dark fw-bold py-0 px-2" style="font-size: 0.75rem;">
                                        購物車 <span class="badge bg-dark text-warning nav-cart-badge <?php echo ($global_cart_count > 0) ? '' : 'd-none'; ?>"><?php echo $global_cart_count; ?></span>
                                    </a>
                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-danger py-0 px-2 btn-ajax-logout" style="font-size: 0.75rem;">安全登出</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="py-2">
                                <div class="d-inline-flex align-items-center justify-content-center bg-secondary rounded-circle" style="width: 80px; height: 80px;">
                                    <span class="text-light fs-1"><i class="bi bi-person"></i></span>
                                </div>
                                <div class="text-muted small mt-2">訪客身分</div>
                            </div>
                        <?php endif; ?>
                    </li>

                    <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'index') ? 'active' : ''; ?>" href="index.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#coreservices">服務</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#courseinfo">課程</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#pricing">價格</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#mentor">講師</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#faqSection">FAQ</a></li>

                    <?php if (!isset($_SESSION['mbID'])): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'login') ? 'active' : ''; ?>" href="5sl_login.php">登入</a>
                        </li>
                    <?php else: ?>
                        <!-- 📱 手機版專用：會員中心下拉選單 (d-lg-none) -->
                        <li class="nav-item dropdown d-lg-none">
                            <a class="nav-link dropdown-toggle" href="#" id="memberDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                會員中心
                                <span class="badge rounded-pill bg-danger ms-1 <?php echo ($global_todo_count > 0) ? '' : 'd-none'; ?>" style="font-size: 0.65rem;">
                                    <?php echo ($global_todo_count > 99) ? '99+' : $global_todo_count; ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark shadow-lg border-0">
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center <?php echo ($current_page == 'myConsole') ? 'active' : ''; ?>" href="5sl_myConsole.php">
                                        <span><i class="bi bi-grid-1x2 me-2"></i>訊息中心</span>
                                        <?php if ($global_unread_msg > 0): ?><span class="badge rounded-pill bg-danger ms-2"><?php echo $global_unread_msg; ?></span><?php endif; ?>
                                    </a>
                                </li>
                                <li><a class="dropdown-item <?php echo ($current_page == 'myAccount') ? 'active' : ''; ?>" href="5sl_myAccount.php"><i class="bi bi-person-badge me-2"></i>帳號身份</a></li>
                                <li><a class="dropdown-item <?php echo ($current_page == 'myProducts') ? 'active' : ''; ?>" href="5sl_myProducts.php"><i class="bi bi-journal-bookmark me-2"></i>課程與服務</a></li>
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center <?php echo ($current_page == 'myGifts') ? 'active' : ''; ?>" href="5sl_myGifts.php">
                                        <span><i class="bi bi-gift me-2"></i>禮物與點券</span>
                                        <?php if ($pending_gifts > 0): ?><span class="badge rounded-pill bg-warning text-dark ms-2"><?php echo $pending_gifts; ?></span><?php endif; ?>
                                    </a>
                                </li>
                                <li><a class="dropdown-item <?php echo ($current_page == 'myOrders') ? 'active' : ''; ?>" href="5sl_myOrders.php"><i class="bi bi-receipt-cutoff me-2"></i>我的訂購</a></li>
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center <?php echo ($current_page == 'myQuests') ? 'active' : ''; ?>" href="5sl_myQuests.php">
                                        <span><i class="bi bi-trophy me-2"></i>我的任務</span>
                                        <?php if ($pending_quests > 0): ?><span class="badge rounded-pill bg-warning text-dark ms-2"><?php echo $pending_quests; ?></span><?php endif; ?>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider opacity-25"></li>
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center <?php echo ($current_page == 'myCart') ? 'active' : ''; ?>" href="5sl_myCart.php">
                                        <span><i class="bi bi-cart3 me-2"></i>購物車</span>
                                        <span class="badge rounded-pill bg-danger ms-2 nav-cart-badge <?php echo ($global_cart_count > 0) ? '' : 'd-none'; ?>"><?php echo $global_cart_count; ?></span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider opacity-25"></li>
                                <li><a class="dropdown-item text-white fw-bold btn-ajax-logout" href="javascript:void(0);"><i class="bi bi-power me-1"></i>安全登出</a></li>
                            </ul>
                        </li>

                        <!-- 💻 電腦版專用：最右側圓形小頭像＋待辦總數紅標 (d-none d-lg-block) -->
                        <li class="nav-item dropdown d-none d-lg-block ms-lg-3 fsl-avatar-hover-menu">
                            <a href="5sl_myConsole.php" class="d-inline-flex align-items-center text-decoration-none dropdown-toggle text-white" id="userMenu" title="進入會員訊息中心">
                                <div class="position-relative d-inline-block">
                                    <img src="<?php echo _fn_getMemberAvatar($mb_id, true); ?>" class="user-avatar" alt="User Avatar">
                                    <span id="headerAvatarBadge"
                                        class="position-absolute badge rounded-pill bg-danger border border-2 border-dark <?php echo ($global_todo_count > 0) ? '' : 'd-none'; ?>"
                                        style="top: -4px; right: -4px; font-size: 0.62rem; padding: 0.22em 0.48em; line-height: 1; z-index: 2;">
                                        <?php echo ($global_todo_count > 99) ? '99+' : $global_todo_count; ?>
                                    </span>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-lg border-0" aria-labelledby="userMenu">
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center <?php echo ($current_page == 'myConsole') ? 'active' : ''; ?>" href="5sl_myConsole.php">
                                        <span><i class="bi bi-grid-1x2 me-2"></i>訊息中心</span>
                                        <?php if ($global_unread_msg > 0): ?>
                                            <span class="badge rounded-pill bg-danger ms-2"><?php echo $global_unread_msg; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li><a class="dropdown-item <?php echo ($current_page == 'myAccount') ? 'active' : ''; ?>" href="5sl_myAccount.php"><i class="bi bi-person-badge me-2"></i>帳號身份</a></li>
                                <li><a class="dropdown-item <?php echo ($current_page == 'myProducts') ? 'active' : ''; ?>" href="5sl_myProducts.php"><i class="bi bi-journal-bookmark me-2"></i>課程與服務</a></li>
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center <?php echo ($current_page == 'myGifts') ? 'active' : ''; ?>" href="5sl_myGifts.php">
                                        <span><i class="bi bi-gift me-2"></i>禮物與點券</span>
                                        <?php if ($pending_gifts > 0): ?>
                                            <span class="badge rounded-pill bg-warning text-dark ms-2"><?php echo $pending_gifts; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li><a class="dropdown-item <?php echo ($current_page == 'myOrders') ? 'active' : ''; ?>" href="5sl_myOrders.php"><i class="bi bi-receipt-cutoff me-2"></i>我的訂購</a></li>
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center <?php echo ($current_page == 'myQuests') ? 'active' : ''; ?>" href="5sl_myQuests.php">
                                        <span><i class="bi bi-trophy me-2"></i>我的任務</span>
                                        <?php if ($pending_quests > 0): ?>
                                            <span class="badge rounded-pill bg-warning text-dark ms-2"><?php echo $pending_quests; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider opacity-25"></li>
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center <?php echo ($current_page == 'myCart') ? 'active' : ''; ?>" href="5sl_myCart.php">
                                        <span><i class="bi bi-cart3 me-2"></i>購物車</span>
                                        <span class="badge rounded-pill bg-danger ms-2 nav-cart-badge <?php echo ($global_cart_count > 0) ? '' : 'd-none'; ?>"><?php echo $global_cart_count; ?></span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider opacity-25"></li>
                                <li><a class="dropdown-item text-white fw-bold btn-ajax-logout" href="javascript:void(0);"><i class="bi bi-power me-1"></i>安全登出</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>

            <!-- 💡 如果不是首頁(index)，就不要顯示那層會跑 Cross Fade 動畫的滿版大底圖(Hero Section) -->
        <?php if ($current_page == 'index'): ?>
            <header class="hero-section" id="heroSection"  
                        data-bg1="imageFiles/heroImage001.jpg"
                        data-bg2="imageFiles/heroImage002.jpg"
                        data-bg3="imageFiles/heroImage003.jpg"
                        data-bg4="imageFiles/heroImage004.jpg"
                        >
                <div class="hero-bg-layer layer-a active" id="heroBgLayerA"></div>
                <div class="hero-bg-layer layer-b" id="heroBgLayerB"></div>
                <div id="userMetadata" class="d-none" data-avatar-url="<?php echo _fn_getMemberAvatar($mb_id, true); ?>"></div>

                <div class="container">
                    <h1 class="display-3 mb-3">人生第五季</h1>
                    <!--<h1 class="display-3 mb-3"><img src="imageFiles/5sl_herophoto_title.png" style="filter: invert(100%);"></h1> -->
                    <h4><span class="fw-bold">F</span>ifth <span class="fw-bold">S</span>eason <span class="fw-bold">L</span>ife</h4>
                    <h5>啟發生命自癒的能力...</h5>
                </div>

                
            </header>
        <?php else: ?>
            <!-- 💡 子頁面預留 76px 完整高度，100% 避開上方 fixed-top 導航列 -->
            <div style="height: 60px; background-color: #212529;"></div>
        <?php endif; ?>

    <main>
