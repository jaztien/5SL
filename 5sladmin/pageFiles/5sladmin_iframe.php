<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
//iFrame內部網頁解析
$iFrameURI = isset($_GET['backURI']) ? $_GET['backURI'] : '';
$currURI = "5sladmin_startpage.html";
if(!empty($iFrameURI) && strlen($iFrameURI) % 2 === 0){
  $currURI = hex2bin($iFrameURI);
}
//
$chatType = !empty($_REQUEST['chatType'])?intval($_REQUEST['chatType']):1;
$joinID = !empty($_REQUEST['joinID'])?intval($_REQUEST['joinID']):0;
$inchargeID = !empty($_REQUEST['inchargeID'])?intval($_REQUEST['inchargeID']):0;
$podCast = !empty($_REQUEST['podCast'])?intval($_REQUEST['podCast']):0;
if(!empty($podCast)){
  $chatType = 0;
  $joinID = 0;
  $inchargeID = 0;
}
$sendActive = (empty($chatType)&&!empty($podCast))||(!empty($chatType)&&!empty($joinID)) ? 1 : 0;
$onChatter = '';
$robotStatus = DB->getConfigVal("mess_robot");
$robotStatus = intval($robotStatus['config_value']);

/* [*] TreeMenu 左側選單佈局，包含 Badge 計數框 ------------ */
$admJS=_fn_getCachedJsonData("../jsonFiles/adminpage.json");
$sidebar = $admJS['sidebar'];
$sideBarIDX = array();
$ii = 0;
while($ii<count($sidebar)){
  $sideBarIDX[$sidebar[$ii]['pagecode']]=$ii;
  $ii++;
}
// 圖文模版相關待辦案件數量 index: 4
$sIDX = $sideBarIDX['ITEM_ARTI'];
$getNum = DB->single("SELECT COUNT(arti_id) FROM articles WHERE arti_status=?",array(6));
$getSum = $getNum;
$sidebar[$sIDX]['badgeClass']='text-bg-warning';
$sidebar[$sIDX]['badgeNum']=$getSum;
$sidebar[$sIDX]['subgroup'][0]['badgeClass']='text-bg-warning';
$sidebar[$sIDX]['subgroup'][0]['badgeNum']=$getNum;
// 未讀訊息數量 index: 5
/*
$sIDX = $sideBarIDX['ITEM_MESS'];
$toTime   = (new DateTime())->modify('-180 days')->format('Y-m-d H:i:s');//保留至N日前
$getNum = DB->single("SELECT COUNT(chat_id) FROM data_chats WHERE chat_type = ? AND chat_posttype=? AND chat_published > ? AND chat_status=?",array(1,2,$toTime,2));
$getSum = $getNum;
$sidebar[$sIDX]['badgeClass']='badge-success';
$sidebar[$sIDX]['badgeNum']=$getSum;
$unreadBadge = $getNum;
$sidebar[$sIDX]['subgroup'][0]['badgeClass']='badge-success';
$sidebar[$sIDX]['subgroup'][0]['badgeNum']=$getNum;
*/
// 服務相關待辦案件數量 index: 6
/*
$sIDX = $sideBarIDX['ITEM_SERV'];
$getNum = DB->single("SELECT COUNT(sv_id) FROM sv_service WHERE sv_status IN (?,?,?)",array(6,7,8));
$getSum = $getNum;
$sidebar[$sIDX]['badgeClass']='badge-danger';
$sidebar[$sIDX]['badgeNum']=$getSum;
$sidebar[$sIDX]['subgroup'][0]['badgeClass']='badge-danger';
$sidebar[$sIDX]['subgroup'][0]['badgeNum']=$getNum;
*/
// 廣告相關待辦案件數量 index: 7
/*
$sIDX = $sideBarIDX['ITEM_AD'];
$getNum = DB->single("SELECT COUNT(advp_id) FROM advi_pack WHERE advp_status=?",array(6));
$getNum1 = DB->single("SELECT COUNT(advi_id) FROM advitising WHERE advi_status=?",array(6));
$getSum = $getNum + $getNum1;
$sidebar[$sIDX]['badgeClass']='text-bg-warning';
$sidebar[$sIDX]['badgeNum']=$getSum;
$sidebar[$sIDX]['subgroup'][0]['badgeClass']='text-bg-warning';
$sidebar[$sIDX]['subgroup'][0]['badgeNum']=$getNum;
$sidebar[$sIDX]['subgroup'][1]['badgeClass']='text-bg-warning';
$sidebar[$sIDX]['subgroup'][1]['badgeNum']=$getNum1;
*/
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

  <!-- Google Font: Source Sans Pro 
  <link rel="stylesheet" href="https://googleapis.com">-->
  <!--end::Accessibility Features-->
  <!--begin::Fonts
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.3.1/index.css" integrity="sha256-VzMk9RVNVApO0cXfOO7il3dD8OPyu0aDyyLDd8EEGk8=" crossorigin="anonymous" media="print" onload="this.media = 'all'"/> -->
  <!--end::Fonts-->
  <!--end::Third Party Plugin(OverlayScrollbars)-->
  <!--begin::Third Party Plugin(Bootstrap Icons)
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.2/font/bootstrap-icons.min.css" crossorigin="anonymous"/>-->
  <!--end::Third Party Plugin(Bootstrap Icons)-->
  <!--end::Required Plugin(AdminLTE)-->
    
    <!-- 本地端外掛載入 -->
    <link href="../pluginFiles/bootstrap5/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
    <!-- 💡 核心新增：本地端 Bootstrap 圖示字型檔 -->
    <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
    <!-- flatpickr -->
    <link rel="stylesheet" href="../pluginFiles/flatpickr/flatpickr.min.css">
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="../cssFiles/adminlte.css" />
    <link rel="stylesheet" href="../cssFiles/5sl.mainiframe.css" />
    <!--begin::Third Party Plugin(OverlayScrollbars)
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.17.0/styles/overlayscrollbars.min.css" crossorigin="anonymous"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@7.1.5/skeleton.css" crossorigin="anonymous"/>
    --> 
  <style>
    /* 確保滿版 IFrame 架構下外層視窗不產生多餘捲軸 */
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
      height: calc(100vh - 57px - 45px); /* 扣除 Header 與 Footer 高度，或由 Flexbox 自動撐滿 */
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

<!-- AdminLTE 4 固定側邊欄與固定頁首標準 Class -->
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <!-- 最外層容器 -->
  <div class="app-wrapper">

    <!-- [變更1] .main-header 改為 .app-header；navbar-white navbar-light 改為 bg-body border-bottom；text-sm 改為 small -->
    <nav class="app-header navbar navbar-expand small bg-body border-bottom">
      <!-- [變更2] AdminLTE 4 頂部導覽列內部「必須」包含 .container-fluid 才能正確撐開左右兩側排版 -->
      <div class="container-fluid">
        
        <!-- --------------------- 轉移 Focus 目標 --------------------- -->
        <input type="text" id="searchURI" name="searchURI" class="visually-hidden" value="">
        
        <!-- --------------------- 左側功能連結區 --------------------- -->
        <!-- [變更3] data-widget="pushmenu" 改為 data-lte-toggle="sidebar"，圖示改為 bi-list -->
        <a class="nav-link" style="width:20px;height:52px;background-color:#2C333E;border-radius: 0px 8px 8px 0px;position:absolute;top:0;left:0;color:#FFFFFF;cursor:pointer;display:flex;align-items:center;padding-left:2px;" data-lte-toggle="sidebar" href="#" role="button">
          <i class="bi bi-list"></i>
        </a>

        <ul class="navbar-nav align-items-center" style="height:32px;gap:10px;padding-left:16px;">
          <!-- 全螢幕按鈕：[變更4] data-widget="fullscreen" 改為 data-lte-toggle="fullscreen" -->
          <li class="nav-item">
            <a class="nav-link ps-0 pe-2" data-lte-toggle="fullscreen" href="#" role="button">
              <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
              <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none;"></i>
            </a>
          </li>
          <!-- /全螢幕按鈕 -->

          <!-- 頁面權限圖示顯示 (全部轉為 Bootstrap Icons) -->
          <li class="nav-item nav-authpanel">
            <i id="topAuth_browser" name="topAuth_browser" class="bi bi-eye" title="瀏覽"></i>
            <i id="topAuth_addnew" name="topAuth_addnew" class="bi bi-file-earmark-plus" title="新增"></i>
            <i id="topAuth_edit" name="topAuth_edit" class="bi bi-pencil-square" title="編輯"></i>
            <i id="topAuth_delete" name="topAuth_delete" class="bi bi-trash" title="刪除"></i>
          </li>
          <!-- /頁面權限圖示顯示 -->

          <!-- 操作功能按鈕列 (全部轉為 Bootstrap Icons) -->
          <li class="nav-item nav-toppanel">
            <i id="topBut_upload" name="topBut_upload" class="bi bi-upload" title="上傳"></i>
            <i id="topBut_add" name="topBut_add" class="bi bi-plus-circle" title="新增"></i>
            <i id="topBut_save" name="topBut_save" class="bi bi-floppy" title="儲存"></i>
            <i id="topBut_reset" name="topBut_reset" class="bi bi-arrow-repeat" title="重置"></i>
            <i id="topBut_delete" name="topBut_delete" class="bi bi-trash3" title="刪除"></i>
          </li>

          <!-- 搜尋按鈕 -->
          <li class="nav-item nav-topsearch">
            <i id="topBut_search" name="topBut_search" class="bi bi-search active" title="開啟搜尋視窗"></i>
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
        <!-- -------------------- /左側功能連結區 -------------------- -->

        <!-- -------------------- 右側功能連結區 --------------------- -->
        <!-- [變更5] ml-auto 改為 ms-auto -->
        <ul class="navbar-nav ms-auto align-items-center" style="height:32px;">
          
          <!-- 個人&公共訊息下拉選單：[變更6] data-toggle="dropdown" 改為 data-bs-toggle="dropdown" -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#">
              <i class="bi bi-chat-dots"></i>
              <!-- [變更7] badge-danger 改為 text-bg-danger -->
              <span id="badge_NAVBAR_MESS" class="badge text-bg-danger navbar-badge" style="margin-top:-5px;margin-right:-3px;"><?php //echo $unreadBadge; ?></span>
            </a>
            <!-- [變更8] dropdown-menu-right 改為 dropdown-menu-end -->
            <div id="messDropdown" class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <a href="mess_board.php" target="iFrame_main" class="dropdown-item dropdown-footer">瀏覽所有個人及公共訊息</a>
            </div>
          </li>
          <!-- /個人&公共訊息下拉選單 -->

          <!-- 系統通知下拉選單 -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#">
              <i class="bi bi-bell"></i>
              <!-- badge-warning 改為 text-bg-warning -->
              <span id="badge_notify" class="badge text-bg-warning navbar-badge" style="margin-top:-5px;margin-right:-3px;"></span>
            </a>
            <div id="notifyDropdown" class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <a href="mess_notifylist.php" target="iFrame_main" class="dropdown-item dropdown-footer">瀏覽所有的系統通知訊息</a>
            </div>
          </li>
          <!-- /系統通知下拉選單 -->

          <!-- 管理者功能選單 -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#">
              <!-- [變更9] img-circle 改為 rounded-circle -->
              <img src="<?php echo DOC_PATH_LAYER.$_SESSION['SESS_AD_THUMB'].'?t='.time(); ?>" width="30" height="30" class="rounded-circle shadow-sm adTopNaviPhoto" style="margin-top: -6px;">
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <div class="text-center pt-2">管理執行者</div>
              <div class="image mt-2 text-center">
                <img src="<?php echo DOC_PATH_LAYER.$_SESSION['SESS_AD_THUMB'].'?t='.time(); ?>" width="80" height="80" class="rounded-circle shadow adTopNaviPhoto">
              </div>
              <span class="dropdown-header text-center d-block"><?php echo $_SESSION['SESS_AD_NICK']; ?></span>
              <div class="dropdown-divider"></div>
              <!-- [變更10] mr-1 fa-pull-right 改為 float-end mt-1 -->
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
          <!-- /管理者功能選單 -->

        </ul>
      </div>
    </nav>

    <!-- ==================== /頂部導覽列 ==================== -->

    <!-- ==================== 2. 左側選單欄 (.app-sidebar) ==================== -->
    <!-- AdminLTE 4 改用 data-bs-theme="dark" 控制深色主題 -->
      <!--begin::Sidebar-->
    <!-- [變更1] .main-sidebar 與 .sidebar-dark-primary 改為 .app-sidebar 與 data-bs-theme="dark" -->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
      <!-- 左上方主圖標示 Logo -->
      <div class="sidebar-brand" style="height: 53px;">
        <a href="5sladmin_startpage.html" target="iFrame_main" class="brand-link">
          <img src="../imageFiles/5slLOGO_sW.png" alt="5 Season Life Logo" class="brand-image shadow" style="vertical-align:baseline;margin-top:-4px;">
          <span class="brand-text fw-normal" style="margin-top:7px;margin-bottom:-12px;">
            5SeasonLife<span class="brand-text fw-light" style="color:#888888"> ADMIN</span>
          </span>
        </a>
      </div>
      <!-- /左上方主圖標示 Logo -->

      <!-- 功能區塊 -->
      <div class="sidebar-wrapper">
        <!-- 安全停滯倒數 -->
        <div class="user-panel mt-3 pb-3 mb-3 border-bottom border-secondary" style="height:50px;background-color:#30363b;">
          <div class="container">
            <div class="timer-block">
              <span id="timer_on_exhausted" class="fliptimer"></span>
            </div>
          </div>
        </div>
        <!-- /安全停滯倒數 -->

        <!-- 樹狀導航列 -->
        <nav id="sidebarL" name="sidebarL" class="mt-2">
          <ul class="nav sidebar-menu flex-column small nav-child-indent nav-compact" data-lte-toggle="treeview" role="menu" data-accordion="true">
            <?php
              $i = 0;
              while($i < count($sidebar)){ // 樹狀首層「功能主分類」
                  $sidebar2 = $sidebar[$i]['subgroup'] ?? array();
                  $subnum2 = count($sidebar2);
                  
                  // [變更1] 改用 Bootstrap Icons (bi)，若 JSON 裡原本寫 "fa-xxx" 則自動轉成 "bi-xxx"，或直接吃 "bi-xxx"
                  $rawIcon1 = $sidebar[$i]['icon'] ?? '';
                  $topIcon = !empty($rawIcon1) ? ' '.str_replace('fa-', 'bi-', $rawIcon1) : ' bi-folder';
                  
                  echo '<li data-pagecode="'.$sidebar[$i]['pagecode'].'" class="nav-item">';
                  // 1. 第一層選單的 <a> 標籤
                  $target1 = ($sidebar[$i]['url'] != '#' && !empty($sidebar[$i]['url'])) ? ' target="iFrame_main"' : '';
                  echo '<a href="'.$sidebar[$i]['url'].'"'.$target1.' class="nav-link">';
                  echo '<i class="nav-icon bi'.$topIcon.'"></i>';
                  echo '<p>';
                  echo $sidebar[$i]['title'];
                  
                  // 徽章
                  if(!empty($sidebar[$i]['badgeNum']) || !empty($sidebar[$i]['badgeClass'])){
                      echo '<span id="badge_'.$sidebar[$i]['pagecode'].'" name="badge_'.$sidebar[$i]['pagecode'].'" class="badge float-end me-3';
                      if(!empty($sidebar[$i]['badgeClass'])) { echo ' '.$sidebar[$i]['badgeClass']; }
                      echo '">';
                      if(!empty($sidebar[$i]['badgeNum'])) { echo $sidebar[$i]['badgeNum']; }
                      echo '</span>';
                  }

                  // [變更2] 樹狀展開箭頭改用 Bootstrap Icons 的 bi-chevron-right
                  if($subnum2 > 0){
                      echo '<i class="nav-arrow bi bi-chevron-right"></i>';
                  }
                  echo '</p>';
                  echo '</a>';

                  // 第二層選單
                  if($subnum2 > 0){
                      echo '<ul class="nav nav-treeview">';
                      $si = 0;
                      while($si < $subnum2){
                          $sidebar3 = $sidebar2[$si]['subgroup'] ?? array();
                          $subnum3 = count($sidebar3);
                          
                          // [變更3] 第二層預設改用 bi-record-circle (對應原本的 far fa-dot-circle)
                          $subIcon2 = ' bi bi-record-circle';
                          if($subnum3 > 0 && !empty($sidebar2[$si]['icon'])){
                              $subIcon2 = ' bi '.str_replace('fa-', 'bi-', $sidebar2[$si]['icon']);
                          }

                          echo '<li data-pagecode="'.$sidebar2[$si]['pagecode'].'" class="nav-item">';
                          // 2. 第二層選單的 <a> 標籤
                          $target2 = ($sidebar2[$si]['url'] != '#' && !empty($sidebar2[$si]['url'])) ? ' target="iFrame_main"' : '';
                          echo '<a href="'.$sidebar2[$si]['url'].'"'.$target2.' class="nav-link">';
                          echo '<i class="nav-icon'.$subIcon2.'"></i>';
                          echo '<p>';
                          echo $sidebar2[$si]['title'];

                          // 第二層徽章
                          if(!empty($sidebar2[$si]['badgeNum']) || !empty($sidebar2[$si]['badgeClass'])){
                              echo '<span id="badge_'.$sidebar2[$si]['pagecode'].'" name="badge_'.$sidebar2[$si]['pagecode'].'" class="badge float-end'.($subnum3 > 0 ? ' me-3' : '');
                              if(!empty($sidebar2[$si]['badgeClass'])) { echo ' '.$sidebar2[$si]['badgeClass']; }
                              echo '">';
                              if(!empty($sidebar2[$si]['badgeNum'])) { echo $sidebar2[$si]['badgeNum']; }
                              echo '</span>';
                          }

                          // 第二層箭頭或啟用狀態圖示
                          if($subnum3 > 0) { 
                              echo '<i class="nav-arrow bi bi-chevron-right"></i>'; 
                          } else { 
                              echo '<i class="bi activeicon float-end"></i>'; 
                          }
                          echo '</p>';
                          echo '</a>';

                          // 第三層選單
                          if($subnum3 > 0){
                              echo '<ul class="nav nav-treeview">';
                              $ti = 0;
                              while($ti < $subnum3){
                                  echo '<li data-pagecode="'.$sidebar3[$ti]['pagecode'].'" class="nav-item">';
                                  // 3. 第三層選單的 <a> 標籤
                                  $target3 = ($sidebar3[$ti]['url'] != '#' && !empty($sidebar3[$ti]['url'])) ? ' target="iFrame_main"' : '';
                                  echo '<a href="'.$sidebar3[$ti]['url'].'"'.$target3.' class="nav-link">';
                                  // [變更4] 第三層改用 bi-circle (對應原本的 far fa-circle)
                                  echo '<i class="nav-icon bi bi-circle"></i>';
                                  echo '<p>';
                                  echo $sidebar3[$ti]['title'];

                                  // 第三層徽章
                                  if(!empty($sidebar3[$ti]['badgeNum']) || !empty($sidebar3[$ti]['badgeClass'])){
                                      echo '<span id="badge_'.$sidebar3[$ti]['pagecode'].'" name="badge_'.$sidebar3[$ti]['pagecode'].'" class="badge float-end';
                                      if(!empty($sidebar3[$ti]['badgeClass'])) { echo ' '.$sidebar3[$ti]['badgeClass']; }
                                      echo '">';
                                      if(!empty($sidebar3[$ti]['badgeNum'])) { echo $sidebar3[$ti]['badgeNum']; }
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
        <!-- /樹狀導航列 -->
      </div>
      <!-- /功能區塊 -->
    </aside>

      <!--end::Sidebar-->

    <!-- ==================== /左側選單欄 ==================== -->

    <!-- ==================== 3. 主要內容區 (.app-main + IFrame) ==================== -->
    <main class="app-main p-0">
      <iframe name="iFrame_main" id="iFrame_main" src="./5sladmin_startpage.html"></iframe>
    </main>
    <!-- ==================== /主要內容區 ==================== -->

    <!-- ==================== 4. 頁尾 (.app-footer) ==================== -->
    <footer class="app-footer small py-2">
      <!-- [變更2] AdminLTE 4 建議將右側區塊放在前面或使用 .float-end (取代原本的 float-right) -->
      <div class="float-end d-none d-sm-inline-flex align-items-center">
        <span style="font-size:0.85em;" class="fw-bold me-2">WebSocket:</span>
        <!-- 加上 title 提示與 aria-label，並保留原本的 id 與 socketalert 類別 -->
        <button type="button" id="ind_websocket" class="socketalert" title="WebSocket 連線狀態" aria-label="WebSocket Status"></button>
      </div>

      <!-- 左側版本與版權資訊：[修正] 補上 $DB 的 $ 符號 -->
      <span style="font-size:0.85em;">
        <b>Version</b> 
        <span style="color:#C35050;"><?php $getVersion = DB->getConfigVal('sys_version'); echo $getVersion['config_value']; ?></span>
        &nbsp;&nbsp;&nbsp;&nbsp;Copyright &copy; 2023-<?php echo date('Y'); ?> 
        <strong><?php $getENname = DB->getConfigVal('comp_enname'); echo $getENname['config_value']; ?></strong> All rights reserved.
      </span>
    </footer>
    <!-- /下方訊息區 -->
    <!-- ==================== /頁尾 ==================== -->

  </div>
  <!-- /最外層容器 -->
    <!--begin::Third Party Plugin(OverlayScrollbars)
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.17.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../scriptFiles/adminlte.js"></script> -->
    <!--end::Required Plugin(AdminLTE)-->
  <!-- Scripts 
  <script src="../pluginFiles/jquery/jquery.min.js"></script>
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <script src="../pluginFiles/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script src="../pluginFiles/toastr/toastr.min.js"></script>
  <script src="../scriptFiles/5sladmin.js"></script>
  -->
    //**************************** ********************************//
    //************************ 本地端外掛載入 ***********************//
    //**************************** ********************************//
    <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
    <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
    <script src="../pluginFiles/flatpickr/flatpickr.min.js"></script>
    <script src="../pluginFiles/flatpickr/location/zh_tw.js"></script>
    <script src="../scriptFiles/adminlte.js"></script>
    <script src="../scriptFiles/fliptimer/lib/jquery-backward-timer.src.js"></script>
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.17.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>

    <!-- ------------------------ /Scripts Link ------------------------ -->
    <script type="text/javascript">
    //**************************** ********************************//
    //***************** JAVASCRIPTS 全域調用參數設置 *****************//
    //**************************** ********************************//





    //**************************** ********************************//
    //*************** JAVASCRIPTS FUNCTIONs 全域調用 ***************//
    //*************** 可接收include檔案要求後自定義動作 ***************//
    //**************************** ********************************//

      // 1. 呼叫 IFrame 內頁的全域函式 (如 doButSave, doButAdd, doButDelete)
      function callFrameFunc(funcName) {
        const frameWin = document.getElementById('iFrame_main').contentWindow;
        if (frameWin && typeof frameWin[funcName] === 'function') {
          frameWin[funcName]();
        } else {
          toastr.warning('目前頁面不支援此操作功能');
        }
      }
      //---------------------------------------------------------//
      // 2. 供內頁加載完成後觸發自動展開對應側邊欄選單 (樹狀選單狀態同步)
      //---------------------------------------------------------//
      /**
       * 供 IFrame 內頁載入完成後同步展開並高亮側邊欄選單 (支援兩層與三層結構)
       * @param {string} itemCode - 第一層分類代碼 (例如 'ITEM_SYS')
       * @param {string} markCode - 目標頁面代碼 (例如 'PG_SYSADMIN')
       * @param {string} subItemCode - 第二層子目錄代碼 (選填，若有三層結構時傳入)
       */
      function treeviewCheck(itemCode, markCode, subItemCode) {
        const $sidebar = $('#sidebarL');
        if (!$sidebar.length) return;

        // 1. 清除所有現有的 active 與展開狀態
        $sidebar.find('.nav-link').removeClass('active');
        $sidebar.find('.nav-item').removeClass('menu-open');
        $sidebar.find('.nav-treeview').hide(); // 確保未選中的子選單收合

        // 2. 找出目標頁面節點 (透過 data-pagecode 屬性精準定位)
        const $targetLi = markCode ? $sidebar.find('li[data-pagecode="' + markCode + '"]') : $();

        if ($targetLi.length) {
          // 為目標頁面本身加上 active (觸發淺灰底深字與右側小三角)
          $targetLi.children('a.nav-link').addClass('active');

          // 往上尋找所有父層的 .nav-item (自動支援 2 層與 3 層目錄！)
          $targetLi.parents('li.nav-item').each(function() {
            $(this).addClass('menu-open');
            $(this).children('ul.nav-treeview').show(); // 確保子選單保持展開可見
            $(this).children('a.nav-link').addClass('active');
          });
        } else if (itemCode) {
          // 備援：若找不到 markCode，至少展開指定的父層分類
          const $itemLi = $sidebar.find('li[data-pagecode="' + itemCode + '"]');
          $itemLi.addClass('menu-open');
          $itemLi.children('ul.nav-treeview').show();
          $itemLi.children('a.nav-link').addClass('active');
        }
      }

      /**
       * 供 Start Page 載入時重置所有選單狀態
       */
      function treeviewReset() {
        const $sidebar = $('#sidebarL');
        $sidebar.find('.nav-link').removeClass('active');
        $sidebar.find('.nav-item').removeClass('menu-open');
        $sidebar.find('.nav-treeview').slideUp(200);
      }

    //**************************** ********************************//
    //***************** jQuery FUNCTIONs 區域調用 ******************//
    //**************************** ********************************//

      $(document).ready(function() {

        //---------------------------------------------------------//
        // 3. 點擊側邊欄連結時即時切換 Active 樣式
        //---------------------------------------------------------//
        $('.sidebar-menu .nav-treeview .nav-link').on('click', function() {
          $('.sidebar-menu .nav-link').removeClass('active');
          $(this).addClass('active');
          $(this).closest('.nav-item').parent().closest('.nav-item').children('.nav-link').addClass('active');
        });
        //---------------------------------------------------------//
        // 4. 安全停滯倒數 
        //---------------------------------------------------------//
        let countdownSec = 600;
        $('#timer_on_exhausted').backward_timer({
          seconds: countdownSec,//600
          format: 'h%:m%:s%',
          on_exhausted: function(timer) {
            window.location.assign("../lockscreen.php?backURI=" + getUrlHex());
            //window.location.assign("../lockscreen.php");
          }
        });
        $('#timer_on_exhausted').backward_timer('start');
        //點擊畫面重設倒數，這段代碼將出現在每一個頁面尾端
        $('body').on('keypress click', function(){
          $('#timer_on_exhausted').backward_timer('reset');
        });





      });
    </script>
</body>
</html>
