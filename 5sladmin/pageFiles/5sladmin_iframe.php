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

// 統計待後台人工回覆之雙向訊息數
$pendingMsgCount = (int)DB->single("SELECT COUNT(*) FROM fsl_messages WHERE parent_id = 0 AND msg_status = 1 AND msg_reply_status = 0");

/* [*] TreeMenu 左側選單佈局，包含 Badge 計數框 ------------ */
$admJS      = _fn_getCachedJsonData("../jsonFiles/adminpage.json");
$sidebar    = $admJS['sidebar'];
$sideBarIDX = array();
$ii         = 0;
while ($ii < count($sidebar)) {
  $sideBarIDX[$sidebar[$ii]['pagecode']] = $ii;
  $ii++;
}

// 圖文模版相關待辦案件數量
if (isset($sideBarIDX['ITEM_ARTI'])) {
  $sIDX   = $sideBarIDX['ITEM_ARTI'];
  $getNum = (int)DB->single("SELECT COUNT(arti_id) FROM fsl_articles WHERE arti_status = ?", array(6));
  if ($getNum > 0) {
    $sidebar[$sIDX]['badgeClass'] = 'text-bg-warning';
    $sidebar[$sIDX]['badgeNum']   = $getNum;
    if (isset($sidebar[$sIDX]['subgroup'][0])) {
      $sidebar[$sIDX]['subgroup'][0]['badgeClass'] = 'text-bg-warning';
      $sidebar[$sIDX]['subgroup'][0]['badgeNum']   = $getNum;
    }
  }
}

// 雙向訊息待回覆數量徽章
if (isset($sideBarIDX['ITEM_MSG']) && $pendingMsgCount > 0) {
  $mIDX = $sideBarIDX['ITEM_MSG'];
  $sidebar[$mIDX]['badgeClass'] = 'text-bg-danger';
  $sidebar[$mIDX]['badgeNum']   = $pendingMsgCount;
  if (isset($sidebar[$mIDX]['subgroup'][0])) {
    $sidebar[$mIDX]['subgroup'][0]['badgeClass'] = 'text-bg-danger';
    $sidebar[$mIDX]['subgroup'][0]['badgeNum']   = $pendingMsgCount;
  }
}
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

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
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

          <!-- 雙向訊息下拉選單 -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#" title="雙向訊息管理">
              <i class="bi bi-chat-dots"></i>
              <span id="badge_NAVBAR_MESS" class="badge text-bg-danger navbar-badge" style="margin-top:-5px;margin-right:-3px;"><?php echo $pendingMsgCount > 0 ? $pendingMsgCount : ''; ?></span>
            </a>
            <div id="messDropdown" class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <a href="msg_list.php" target="iFrame_main" class="dropdown-item dropdown-footer">瀏覽所有前後台雙向訊息</a>
            </div>
          </li>

          <!-- 站內通知下拉選單 -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#" title="站內通知管理">
              <i class="bi bi-bell"></i>
              <span id="badge_notify" class="badge text-bg-warning navbar-badge" style="margin-top:-5px;margin-right:-3px;"></span>
            </a>
            <div id="notifyDropdown" class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
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

