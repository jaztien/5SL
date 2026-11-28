<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');

// iFrame內部網頁解析
$iFrameURI = isset($_GET['backURI']) ? $_GET['backURI'] : '';
$currURI   = "5sladmin_startpage.html";
if (!empty($iFrameURI) && strlen($iFrameURI) % 2 === 0) {
  $currURI = hex2bin($iFrameURI);
}

$currAdId = (int)($_SESSION['SESS_AD_ID'] ?? 0);

// =========================================================================
// 1. 計算各子頁面待辦事件數量與顏色分類
//    - 紅色 (text-bg-danger, prio=3) ：工作預約類、雙向訊息、服務工單、資料表維護
//    - 綠色 (text-bg-success, prio=2)：指定管理類（通知給當前登入管理員的未讀訊息）
//    - 黃色 (text-bg-warning, prio=1)：系統通知與待審類（圖文模版待審等）
// =========================================================================
// (1) PG_MSG：雙向訊息待人工回覆數（紅）
$pendingMsgCount = (int)DB->single("SELECT COUNT(*) FROM fsl_messages WHERE parent_id = 0 AND msg_status = 1 AND msg_reply_status = 0");

// (2) PG_ROBOT：自動應答未命中待優化對話數（紅）
$pendingRobotCount = 0;
try {
  $pendingRobotCount = (int)DB->single("SELECT COUNT(*) FROM fsl_messages WHERE parent_id = 0 AND msg_status = 1 AND msg_reply_status = 0 AND msg_bot_handled = 0");
} catch (Throwable $e) {
  $pendingRobotCount = 0;
}

// (3) PG_SYS_DB：資料表維護管理（碎片 >= 64KB 需最佳化之資料表數，紅）
$fragTableCount = 0;
try {
  foreach ((DB->query("SHOW TABLE STATUS") ?: []) as $ts) {
    if ((int)($ts['Data_free'] ?? 0) >= 65536) $fragTableCount++;
  }
} catch (Throwable $e) {
  $fragTableCount = 0;
}

// (4) PG_NOTIFY：指定管理類（指派/通知給當前登入管理員且未讀數，綠）
$unreadAdminNotiCount = 0;
$personalNotiItems    = array();
if ($currAdId > 0) {
  try {
    $unreadAdminNotiCount = (int)DB->single("SELECT COUNT(*) FROM fsl_schedule_logs WHERE recipient_type = 2 AND recipient_id = ? AND channel = 'noti' AND send_status = 0", [$currAdId]);
    $personalNotiItems    = DB->query("SELECT slog_id, slog_created, send_note FROM fsl_schedule_logs WHERE recipient_type = 2 AND recipient_id = ? AND channel = 'noti' AND send_status = 0 ORDER BY slog_id DESC LIMIT 5", [$currAdId]) ?: array();
  } catch (Throwable $e) {
    $unreadAdminNotiCount = 0;
  }
}

// (5) PG_SCH_CONSULT：工作預約排程申請審核（已有申請待審但尚未確認歸屬之諮詢時段數，紅）
$pendingConsultCount = 0;
try {
  $pendingConsultCount = (int)DB->single("
    SELECT COUNT(DISTINCT s.sch_id)
    FROM fsl_schedules s
    INNER JOIN fsl_consult_bookings b ON s.sch_id = b.sch_id AND b.bkg_status = 0
    WHERE s.sch_source = 3 AND s.sch_exec_status = 0
      AND NOT EXISTS (SELECT 1 FROM fsl_consult_bookings b2 WHERE b2.sch_id = s.sch_id AND b2.bkg_status = 1)
  ");
} catch (Throwable $e) {
  $pendingConsultCount = 0;
}

// (6) PG_SRV_TICKET：服務工單接收列表（待受理 + 處理中之工單數，紅）
$pendingTicketCount = 0;
try {
  $pendingTicketCount = (int)DB->single("SELECT COUNT(*) FROM fsl_service_tickets WHERE tkt_status IN (0, 1)");
} catch (Throwable $e) {
  $pendingTicketCount = 0;
}

// (7) PG_ARTI / ITEM_ARTI：通知與待審類（圖文模版待辦案件數量，黃）
$pendingArtiCount = (int)DB->single("SELECT COUNT(arti_id) FROM fsl_articles WHERE arti_status = ?", array(6));

// 頂部右側雙向訊息下拉選單加總（紅色 Badge：雙向訊息 + 自動應答 + 服務工單）
$topRedMsgTotal = $pendingMsgCount + $pendingRobotCount + $pendingTicketCount;

// 頂部右側站內通知下拉選單加總（黃色 Badge：工作預約待審 + 指定管理未讀 + 圖文待審 + 資料庫維護）
$topYellowNotiTotal = $pendingConsultCount + $unreadAdminNotiCount + $pendingArtiCount + $fragTableCount;

// 左側樹狀選單子項目 Badge 定義（工作預約/雙向訊息=紅、指定管理=綠、通知類=黃）
$leafBadges = array(
  'PG_SYS_DB'      => array('num' => $fragTableCount,       'cls' => 'text-bg-danger',  'prio' => 3),
  'PG_MSG'         => array('num' => $pendingMsgCount,      'cls' => 'text-bg-danger',  'prio' => 3),
  'PG_ROBOT'       => array('num' => $pendingRobotCount,    'cls' => 'text-bg-danger',  'prio' => 3),
  'PG_NOTIFY'      => array('num' => $unreadAdminNotiCount, 'cls' => 'text-bg-success', 'prio' => 2),
  'PG_SCH_CONSULT' => array('num' => $pendingConsultCount,  'cls' => 'text-bg-danger',  'prio' => 3),
  'PG_SRV_TICKET'  => array('num' => $pendingTicketCount,   'cls' => 'text-bg-danger',  'prio' => 3),
  'PG_ARTI'        => array('num' => $pendingArtiCount,     'cls' => 'text-bg-warning', 'prio' => 1)
);

/* [*] TreeMenu 左側選單佈局，自動注入子分類 Badge 並向上加總至主分類 ------------ */
$admJS   = _fn_getCachedJsonData("../jsonFiles/adminpage.json");
$sidebar = $admJS['sidebar'] ?? array();

foreach ($sidebar as $i => &$lv1) {
  $lv1Sum  = 0;
  $lv1Prio = 0;
  $lv1Cls  = '';

  if (!empty($lv1['subgroup']) && is_array($lv1['subgroup'])) {
    foreach ($lv1['subgroup'] as $si => &$lv2) {
      $lv2Code = $lv2['pagecode'] ?? '';
      $lv2Sum  = 0;
      $lv2Prio = 0;
      $lv2Cls  = '';

      if (!empty($lv2['subgroup']) && is_array($lv2['subgroup'])) {
        foreach ($lv2['subgroup'] as $ti => &$lv3) {
          $lv3Code = $lv3['pagecode'] ?? '';
          if (isset($leafBadges[$lv3Code]) && $leafBadges[$lv3Code]['num'] > 0) {
            $b = $leafBadges[$lv3Code];
            $lv3['badgeNum']   = $b['num'];
            $lv3['badgeClass'] = $b['cls'];
            $lv2Sum += $b['num'];
            if ($b['prio'] > $lv2Prio) { $lv2Prio = $b['prio']; $lv2Cls = $b['cls']; }
          }
        }
        unset($lv3);
      } elseif (isset($leafBadges[$lv2Code]) && $leafBadges[$lv2Code]['num'] > 0) {
        $b = $leafBadges[$lv2Code];
        $lv2Sum  = $b['num'];
        $lv2Prio = $b['prio'];
        $lv2Cls  = $b['cls'];
      }

      if ($lv2Sum > 0) {
        $lv2['badgeNum']   = $lv2Sum;
        $lv2['badgeClass'] = $lv2Cls;
        $lv1Sum += $lv2Sum;
        if ($lv2Prio > $lv1Prio) { $lv1Prio = $lv2Prio; $lv1Cls = $lv2Cls; }
      }
    }
    unset($lv2);
  }

  if ($lv1Sum > 0) {
    $lv1['badgeNum']   = $lv1Sum;
    $lv1['badgeClass'] = $lv1Cls;
  }
}
unset($lv1);

?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
  <title>Fifth Season Life 人生第五季 | 後台管理系統</title>

  <link href="../pluginFiles/bootstrap5/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/flatpickr/flatpickr.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css" />
  <link rel="stylesheet" href="../cssFiles/5sl.mainiframe.css?t=<?php echo time(); ?>" />

  <style>
    html, body {
      height: 100%;
      overflow: hidden;
    }
    .app-wrapper {
      height: 100vh;
    }
    .app-main {
      display: flex;
      flex-direction: column;
      height: calc(100vh - 57px - 45px);
      flex: 1 1 auto;
      overflow: hidden;
    }
    #iFrame_main {
      width: 100%;
      height: 100%;
      border: 0;
      display: block;
      flex: 1 1 auto;
    }
  </style>
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-mini bg-body-tertiary">
  <div class="app-wrapper">

    <!-- ==================== 1. 頂部導覽列 (.app-header) ==================== -->
    <nav class="app-header navbar navbar-expand small bg-body border-bottom">
      <div class="container-fluid">

        <input type="text" id="searchURI" name="searchURI" class="visually-hidden" value="">

        <!-- 側邊欄展開/收合按鈕 -->
        <a class="nav-link" style="width:20px;height:52px;background-color:#2C333E;border-radius: 0px 8px 8px 0px;position:absolute;top:0;left:0;color:#FFFFFF;cursor:pointer;display:flex;align-items:center;padding-left:2px;" data-lte-toggle="sidebar" href="#" role="button">
          <i class="bi bi-list"></i>
        </a>

        <!-- 左側功能連結區 -->
        <ul class="navbar-nav align-items-center" style="height:32px;gap:10px;padding-left:16px;">
          <!-- 全螢幕按鈕 -->
          <li class="nav-item">
            <a class="nav-link ps-0 pe-2" data-lte-toggle="fullscreen" href="#" role="button">
              <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
              <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none;"></i>
            </a>
          </li>

          <!-- 頁面權限圖示顯示 -->
          <li class="nav-item nav-authpanel">
            <i id="topAuth_browser" name="topAuth_browser" class="bi bi-eye" title="瀏覽"></i>
            <i id="topAuth_addnew" name="topAuth_addnew" class="bi bi-file-earmark-plus" title="新增"></i>
            <i id="topAuth_edit" name="topAuth_edit" class="bi bi-pencil-square" title="編輯"></i>
            <i id="topAuth_delete" name="topAuth_delete" class="bi bi-trash" title="刪除"></i>
          </li>

          <!-- 操作功能按鈕列 -->
          <li class="nav-item nav-toppanel">
            <i id="topBut_upload" name="topBut_upload" class="bi bi-upload" title="上傳"></i>
            <i id="topBut_add" name="topBut_add" class="bi bi-plus-circle" title="新增"></i>
            <i id="topBut_save" name="topBut_save" class="bi bi-floppy" title="儲存"></i>
            <i id="topBut_reset" name="topBut_reset" class="bi bi-arrow-repeat" title="重置"></i>
            <i id="topBut_delete" name="topBut_delete" class="bi bi-trash3" title="刪除"></i>
          </li>

          <!-- 雙向抽屜控制按鈕（自動偵測子頁面 #offcanvasLeft 與 #offcanvasSearch） -->
          <li class="nav-item nav-topdrawer">
            <i id="topBut_SideBarL" name="topBut_SideBarL" class="bi bi-layout-sidebar-inset" title="切換左側輔助抽屜"></i>
            <i id="topBut_SideBarR" name="topBut_SideBarR" class="bi bi-layout-sidebar-inset-reverse" title="切換右側搜尋抽屜"></i>
          </li>

          <!-- 匯出與列印按鈕 -->
          <li class="nav-item nav-topexport">
            <i id="topBut_excel" name="topBut_excel" class="bi bi-file-earmark-excel active" title="輸出 Excel"></i>
            <i id="topBut_print" name="topBut_print" class="bi bi-printer active" title="頁面列印"></i>
          </li>

          <!-- 返回關聯前頁按鈕 -->
          <li class="nav-item nav-topbkredir">
            <i id="topBut_bkurl" name="topBut_bkurl" class="bi bi-reply-all active" title="返回關聯前頁"></i>
          </li>
        </ul>

        <!-- 右側功能連結區 -->
        <ul class="navbar-nav ms-auto align-items-center" style="height:32px;">

          <!-- 雙向訊息下拉選單（紅色 Badge 加總：雙向留言 + 自動應答 + 服務工單） -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#" title="雙向訊息與客服待辦">
              <i class="bi bi-chat-dots"></i>
              <span id="badge_NAVBAR_MESS" class="badge text-bg-danger navbar-badge" style="margin-top:-5px;margin-right:-3px;"><?php echo $topRedMsgTotal > 0 ? $topRedMsgTotal : ''; ?></span>
            </a>
            <div id="messDropdown" class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <span class="dropdown-item dropdown-header fw-bold">
                <?php echo $topRedMsgTotal > 0 ? "共 {$topRedMsgTotal} 項訊息與客服待辦" : "目前無待處理訊息"; ?>
              </span>
              <?php if ($pendingMsgCount > 0): ?>
                <div class="dropdown-divider"></div>
                <a href="msg_list.php" target="iFrame_main" class="dropdown-item d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-chat-left-text text-danger me-2"></i>會員雙向留言待回覆</span>
                  <span class="badge text-bg-danger rounded-pill"><?php echo $pendingMsgCount; ?></span>
                </a>
              <?php endif; ?>
              <?php if ($pendingRobotCount > 0): ?>
                <div class="dropdown-divider"></div>
                <a href="robot_list.php" target="iFrame_main" class="dropdown-item d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-robot text-danger me-2"></i>自動應答待優化對話</span>
                  <span class="badge text-bg-danger rounded-pill"><?php echo $pendingRobotCount; ?></span>
                </a>
              <?php endif; ?>
              <?php if ($pendingTicketCount > 0): ?>
                <div class="dropdown-divider"></div>
                <a href="srv_ticket_list.php" target="iFrame_main" class="dropdown-item d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-headset text-danger me-2"></i>服務工單待受理/處理</span>
                  <span class="badge text-bg-danger rounded-pill"><?php echo $pendingTicketCount; ?></span>
                </a>
              <?php endif; ?>
              <div class="dropdown-divider"></div>
              <a href="msg_list.php" target="iFrame_main" class="dropdown-item dropdown-footer">瀏覽所有前後台雙向訊息</a>
            </div>
          </li>

          <!-- 站內通知下拉選單（黃色 Badge 加總：工作預約 + 指定管理 + 圖文待審 + 資料庫維護） -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#" title="站內通知與預約審核待辦">
              <i class="bi bi-bell"></i>
              <span id="badge_notify" class="badge text-bg-warning navbar-badge" style="margin-top:-5px;margin-right:-3px;"><?php echo $topYellowNotiTotal > 0 ? $topYellowNotiTotal : ''; ?></span>
            </a>
            <div id="notifyDropdown" class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <span class="dropdown-item dropdown-header fw-bold">
                <?php echo $topYellowNotiTotal > 0 ? "共 {$topYellowNotiTotal} 項通知與排程審核待辦" : "目前無未讀站內通知"; ?>
              </span>
              <?php if ($pendingConsultCount > 0): ?>
                <div class="dropdown-divider"></div>
                <a href="consult_booking_list.php" target="iFrame_main" class="dropdown-item d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-calendar-check text-danger me-2"></i>一對一諮詢預約待審核</span>
                  <span class="badge text-bg-danger rounded-pill"><?php echo $pendingConsultCount; ?></span>
                </a>
              <?php endif; ?>
              <?php if ($unreadAdminNotiCount > 0): ?>
                <div class="dropdown-divider"></div>
                <a href="noti_list.php" target="iFrame_main" class="dropdown-item d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-person-check-fill text-success me-2"></i>指派給您的未讀通知</span>
                  <span class="badge text-bg-success rounded-pill"><?php echo $unreadAdminNotiCount; ?></span>
                </a>
              <?php endif; ?>
              <?php if ($pendingArtiCount > 0): ?>
                <div class="dropdown-divider"></div>
                <a href="art_list.php" target="iFrame_main" class="dropdown-item d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-file-earmark-richtext text-warning me-2"></i>圖文模板待審案件</span>
                  <span class="badge text-bg-warning rounded-pill"><?php echo $pendingArtiCount; ?></span>
                </a>
              <?php endif; ?>
              <?php if ($fragTableCount > 0): ?>
                <div class="dropdown-divider"></div>
                <a href="sys_db_maint.php" target="iFrame_main" class="dropdown-item d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-database-gear text-danger me-2"></i>資料表建議最佳化維護</span>
                  <span class="badge text-bg-danger rounded-pill"><?php echo $fragTableCount; ?></span>
                </a>
              <?php endif; ?>
              <div class="dropdown-divider"></div>
              <a href="noti_list.php" target="iFrame_main" class="dropdown-item dropdown-footer">瀏覽所有站內通知紀錄</a>
            </div>
          </li>

          <!-- 管理者功能選單 -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#">
              <img src="<?php echo DOC_PATH_LAYER.$_SESSION['SESS_AD_THUMB'].'?t='.time(); ?>" width="30" height="30" class="rounded-circle shadow-sm adTopNaviPhoto" style="margin-top: -6px;">
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <div class="text-center pt-2">管理執行者</div>
              <div class="image mt-2 text-center">
                <img src="<?php echo DOC_PATH_LAYER.$_SESSION['SESS_AD_THUMB'].'?t='.time(); ?>" width="80" height="80" class="rounded-circle shadow adTopNaviPhoto">
              </div>
              <span class="dropdown-header text-center d-block"><?php echo $_SESSION['SESS_AD_NICK']; ?></span>
              <div class="dropdown-divider"></div>
              <a href="ad_profile.php?currID=<?php echo $_SESSION['SESS_AD_ID']; ?>" target="iFrame_main" class="dropdown-item" style="cursor:pointer;">
                <i class="bi bi-person float-end mt-1"></i> 編輯個人資料
              </a>
              <div class="dropdown-divider"></div>
              <div id="lockscreen" class="dropdown-item" style="cursor:pointer;">
                <i class="bi bi-lock float-end mt-1"></i> 鎖定(暫離)系統
              </div>
              <div class="dropdown-divider"></div>
              <div id="logout" class="dropdown-item" style="cursor:pointer;">
                <i class="bi bi-power float-end mt-1"></i> 登出
              </div>
            </div>
          </li>
          <li class="nav-item">&nbsp;&nbsp;&nbsp;&nbsp;</li>

        </ul>
      </div>
    </nav>

    <!-- ==================== 2. 左側選單欄 (.app-sidebar) ==================== -->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
      <div class="sidebar-brand" style="height: 53px;">
        <a href="5sladmin_startpage.html" target="iFrame_main" class="brand-link">
          <img src="../imageFiles/5slLOGO_sW.png" alt="5 Season Life Logo" class="brand-image shadow" style="vertical-align:baseline;margin-top:-4px;">
          <span class="brand-text fw-normal" style="margin-top:7px;margin-bottom:-12px;">
            5SeasonLife<span class="brand-text fw-light" style="color:#888888"> ADMIN</span>
          </span>
        </a>
      </div>

      <div class="sidebar-wrapper">
        <!-- 安全停滯倒數 -->
        <div class="user-panel mt-3 pb-3 mb-3 border-bottom border-secondary" style="height:50px;background-color:#30363b;">
          <div class="container">
            <div class="timer-block">
              <span id="timer_on_exhausted" class="fliptimer"></span>
            </div>
          </div>
        </div>

        <!-- 樹狀導航列 -->
        <nav id="sidebarL" name="sidebarL" class="mt-2">
          <ul class="nav sidebar-menu flex-column small nav-child-indent nav-compact" data-lte-toggle="treeview" role="menu" data-accordion="true">
            <?php
              $i = 0;
              while ($i < count($sidebar)) {
                  $sidebar2 = $sidebar[$i]['subgroup'] ?? array();
                  $subnum2  = count($sidebar2);

                  $rawIcon1 = $sidebar[$i]['icon'] ?? '';
                  $topIcon  = !empty($rawIcon1) ? ' '.str_replace('fa-', 'bi-', $rawIcon1) : ' bi-folder';

                  echo '<li data-pagecode="'.$sidebar[$i]['pagecode'].'" class="nav-item">';
                  $target1 = ($sidebar[$i]['url'] != '#' && !empty($sidebar[$i]['url'])) ? ' target="iFrame_main"' : '';
                  echo '<a href="'.$sidebar[$i]['url'].'"'.$target1.' class="nav-link">';
                  echo '<i class="nav-icon bi'.$topIcon.'"></i>';
                  echo '<p>';
                  echo $sidebar[$i]['title'];

                  if (!empty($sidebar[$i]['badgeNum']) || !empty($sidebar[$i]['badgeClass'])) {
                      echo '<span id="badge_'.$sidebar[$i]['pagecode'].'" name="badge_'.$sidebar[$i]['pagecode'].'" class="badge float-end me-3';
                      if (!empty($sidebar[$i]['badgeClass'])) { echo ' '.$sidebar[$i]['badgeClass']; }
                      echo '">';
                      if (!empty($sidebar[$i]['badgeNum'])) { echo $sidebar[$i]['badgeNum']; }
                      echo '</span>';
                  }

                  if ($subnum2 > 0) {
                      echo '<i class="nav-arrow bi bi-chevron-right"></i>';
                  }
                  echo '</p>';
                  echo '</a>';

                  if ($subnum2 > 0) {
                      echo '<ul class="nav nav-treeview">';
                      $si = 0;
                      while ($si < $subnum2) {
                          $sidebar3 = $sidebar2[$si]['subgroup'] ?? array();
                          $subnum3  = count($sidebar3);

                          $subIcon2 = ' bi bi-record-circle';
                          if ($subnum3 > 0 && !empty($sidebar2[$si]['icon'])) {
                              $subIcon2 = ' bi '.str_replace('fa-', 'bi-', $sidebar2[$si]['icon']);
                          }

                          echo '<li data-pagecode="'.$sidebar2[$si]['pagecode'].'" class="nav-item">';
                          $target2 = ($sidebar2[$si]['url'] != '#' && !empty($sidebar2[$si]['url'])) ? ' target="iFrame_main"' : '';
                          echo '<a href="'.$sidebar2[$si]['url'].'"'.$target2.' class="nav-link">';
                          echo '<i class="nav-icon'.$subIcon2.'"></i>';
                          echo '<p>';
                          echo $sidebar2[$si]['title'];

                          if (!empty($sidebar2[$si]['badgeNum']) || !empty($sidebar2[$si]['badgeClass'])) {
                              echo '<span id="badge_'.$sidebar2[$si]['pagecode'].'" name="badge_'.$sidebar2[$si]['pagecode'].'" class="badge float-end'.($subnum3 > 0 ? ' me-3' : '');
                              if (!empty($sidebar2[$si]['badgeClass'])) { echo ' '.$sidebar2[$si]['badgeClass']; }
                              echo '">';
                              if (!empty($sidebar2[$si]['badgeNum'])) { echo $sidebar2[$si]['badgeNum']; }
                              echo '</span>';
                          }

                          if ($subnum3 > 0) {
                              echo '<i class="nav-arrow bi bi-chevron-right"></i>';
                          } else {
                              echo '<i class="bi activeicon float-end"></i>';
                          }
                          echo '</p>';
                          echo '</a>';

                          if ($subnum3 > 0) {
                              echo '<ul class="nav nav-treeview">';
                              $ti = 0;
                              while ($ti < $subnum3) {
                                  echo '<li data-pagecode="'.$sidebar3[$ti]['pagecode'].'" class="nav-item">';
                                  $target3 = ($sidebar3[$ti]['url'] != '#' && !empty($sidebar3[$ti]['url'])) ? ' target="iFrame_main"' : '';
                                  echo '<a href="'.$sidebar3[$ti]['url'].'"'.$target3.' class="nav-link">';
                                  echo '<i class="nav-icon bi bi-circle"></i>';
                                  echo '<p>';
                                  echo $sidebar3[$ti]['title'];

                                  if (!empty($sidebar3[$ti]['badgeNum']) || !empty($sidebar3[$ti]['badgeClass'])) {
                                      echo '<span id="badge_'.$sidebar3[$ti]['pagecode'].'" name="badge_'.$sidebar3[$ti]['pagecode'].'" class="badge float-end';
                                      if (!empty($sidebar3[$ti]['badgeClass'])) { echo ' '.$sidebar3[$ti]['badgeClass']; }
                                      echo '">';
                                      if (!empty($sidebar3[$ti]['badgeNum'])) { echo $sidebar3[$ti]['badgeNum']; }
                                      echo '</span>';
                                  }

                                  echo '<i class="bi activeicon float-end"></i>';
                                  echo '</p>';
                                  echo '</a>';
                                  echo '</li>';
                                  $ti++;
                              }
                              echo '</ul>';
                          }
                          echo '</li>';
                          $si++;
                      }
                      echo '</ul>';
                  }
                  echo '</li>';
                  $i++;
              }
            ?>
          </ul>
        </nav>
      </div>
    </aside>

    <!-- ==================== 3. 主要內容區 (.app-main + IFrame) ==================== -->
    <main class="app-main p-0">
      <iframe name="iFrame_main" id="iFrame_main" src="<?php echo htmlspecialchars($currURI); ?>"></iframe>
    </main>

    <!-- ==================== 4. 頁尾 (.app-footer) ==================== -->
    <footer class="app-footer small py-2">
      <div class="float-end d-none d-sm-inline-flex align-items-center">
        <span style="font-size:0.85em;" class="fw-bold me-2">WebSocket:</span>
        <button type="button" id="ind_websocket" class="socketalert" title="WebSocket 連線狀態" aria-label="WebSocket Status"></button>
      </div>

      <span style="font-size:0.85em;">
        <b>Version</b>
        <span style="color:#C35050;"><?php $getVersion = DB->getConfigVal('sys_version'); echo $getVersion['config_value']; ?></span>
        &nbsp;&nbsp;&nbsp;&nbsp;Copyright &copy; 2023-<?php echo date('Y'); ?>
        <strong><?php $getENname = DB->getConfigVal('comp_enname'); echo $getENname['config_value']; ?></strong> All rights reserved.
      </span>
    </footer>

  </div>

  <!-- Bootstrap 5 原生 Toast 通知容器 -->
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 10900;" id="bs5ToastContainer"></div>

  <!-- Scripts -->
  <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <script src="../pluginFiles/flatpickr/flatpickr.min.js"></script>
  <script src="../pluginFiles/flatpickr/location/zh_tw.js"></script>
  <script src="../scriptFiles/adminlte.js"></script>
  <script src="../scriptFiles/fliptimer/lib/jquery-backward-timer.src.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.17.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="../scriptFiles/5sladmin_iframe.js?t=<?php echo time(); ?>"></script>
</body>
</html>

