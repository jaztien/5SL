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
$robotStatus = $DB->getConfigVal("mess_robot");
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
$getNum = $DB->single("SELECT COUNT(arti_id) FROM articles WHERE arti_status=?",array(6));
$getSum = $getNum;
$sidebar[$sIDX]['badgeClass']='badge-warning';
$sidebar[$sIDX]['badgeNum']=$getSum;
$sidebar[$sIDX]['subgroup'][0]['badgeClass']='badge-warning';
$sidebar[$sIDX]['subgroup'][0]['badgeNum']=$getNum;
// 未讀訊息數量 index: 5
/*
$sIDX = $sideBarIDX['ITEM_MESS'];
$toTime   = (new DateTime())->modify('-180 days')->format('Y-m-d H:i:s');//保留至N日前
$getNum = $DB->single("SELECT COUNT(chat_id) FROM data_chats WHERE chat_type = ? AND chat_posttype=? AND chat_published > ? AND chat_status=?",array(1,2,$toTime,2));
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
$getNum = $DB->single("SELECT COUNT(sv_id) FROM sv_service WHERE sv_status IN (?,?,?)",array(6,7,8));
$getSum = $getNum;
$sidebar[$sIDX]['badgeClass']='badge-danger';
$sidebar[$sIDX]['badgeNum']=$getSum;
$sidebar[$sIDX]['subgroup'][0]['badgeClass']='badge-danger';
$sidebar[$sIDX]['subgroup'][0]['badgeNum']=$getNum;
*/
// 廣告相關待辦案件數量 index: 7
/*
$sIDX = $sideBarIDX['ITEM_AD'];
$getNum = $DB->single("SELECT COUNT(advp_id) FROM advi_pack WHERE advp_status=?",array(6));
$getNum1 = $DB->single("SELECT COUNT(advi_id) FROM advitising WHERE advi_status=?",array(6));
$getSum = $getNum + $getNum1;
$sidebar[$sIDX]['badgeClass']='badge-warning';
$sidebar[$sIDX]['badgeNum']=$getSum;
$sidebar[$sIDX]['subgroup'][0]['badgeClass']='badge-warning';
$sidebar[$sIDX]['subgroup'][0]['badgeNum']=$getNum;
$sidebar[$sIDX]['subgroup'][1]['badgeClass']='badge-warning';
$sidebar[$sIDX]['subgroup'][1]['badgeNum']=$getNum1;
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <META HTTP-EQUIV="EXPIRES" CONTENT="0">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
  <title>Fifth Season Life 人生第五季 | Administration System</title>
  <!-- Google Font: Source Sans Pro
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"> -->
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.min.css">
  <!-- Toastr -->
  <link rel="stylesheet" href="../pluginFiles/toastr/toastr.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css<?php echo "?t=".time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.mainiframe.css<?php echo "?t=".time(); ?>">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="../pluginFiles/overlayScrollbars/css/OverlayScrollbars.min.css">
  <style>
    #messDropdown{
      padding-top: 16px;
    }
    .ezmess{
      display: none;
      margin-top: -8px
    }
    .ezmess:nth-child(-n+10){
      display: list-item;
    }
    #notifyDropdown{
      padding-top: 16px;
    }
    .eznotify{
      display: none;
      margin-top: -8px
    }
    .eznotify:nth-child(-n+10){
      display: list-item;
    }
    .colorsuccess{
      color: #28a745;
    }
    .colorsecondary{
      color: #6c757d;
    }
    .colorinfo{
      color: #17a2b8; 
    }
    .colorwarning{
      color: #ffc107;
    }
    .colordanger{
      color: #dc3545;
    }



  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed" data-panel-auto-height-mode="height">
  <!-- processing waiting -->
  <div id="underMaintenance" style="top:0;left:0;position:absolute;width:100%;height:100%;background-color:#C35050;background-position: 50% 0;opacity: 0.75;margin:0;text-align: center; z-index:99999;display:none;">
    <div style="color:#FFFFFF;position: relative;top: 50%;transform: translate(0, -50%);">
      <i class="fa-solid fa-hand" style="left:50%;top:50%;font-size:32em;color:#c43b3b;"></i><br><br> <span style="font-size:2.8em;color:#c43b3b;">改造維護中，<span>請</span>稍候...</span>
    </div>
  </div>
  <!-- processing waiting -->
  <div id="loadingMask" style="top:0;left:0;position:absolute;width:100%;height:100%;background-color:#2A2826;background-position: 50% 0;opacity: 0.75;margin:0;text-align: center; z-index:99999;display:none;">
    <div style="color:#FFFFFF;position: relative;top: 50%;transform: translate(0, -50%);">
      <img src="../imageFiles/loading2.gif" style="left:50%;top:50%;"><br><span style="font-size:1.6em;color:#EEEEEE;"></span>
    </div>
  </div>
  <div class="wrapper">
    <!-- /////////////////////////////////////////////////////////// -->
    <!-- ////////////////////////// 上方訊息區 /////////////////////// -->
    <!-- /////////////////////////////////////////////////////////// -->
    <nav class="main-header navbar navbar-expand text-sm navbar-white navbar-light">
      <!-- --------------------- 轉移 Focus 目標 --------------------- -->
      <input type="text" id="searchURI" name="searchURI" style="width:1px;height:1px;opacity:0;" value="">
      <!-- --------------------- 左側功能連結區 --------------------- -->
      <a class="nav-link" style="width:20px;height:52px;background-color:#2C333E;border-radius: 0px 8px 8px 0px;position:absolute;top:0;left:0;color:#FFFFFF;cursor:pointer;display:flex;align-items:center;padding-left:2px;" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars"></i>
      </a>
      <ul class="navbar-nav" style="height:32px;margin-top:4px;gap:10px;padding-left:16px;">
        <!-- 
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button" style="padding-left:0;padding-right:10px;">
            <i class="fas fa-bars"></i>
          </a>
        </li>
        -->
        <!-- 全螢幕按鈕 -->
        <li class="nav-item">
          <a class="nav-link" data-widget="fullscreen" href="#" role="button" style="padding-left:0;padding-right:10px;">
            <i class="fas fa-expand-arrows-alt"></i>
          </a>
        </li>
        <!-- /全螢幕按鈕 -->
        <!-- 頁面權限圖示顯示 -->
        <li class="nav-item nav-authpanel">
            <i id="topAuth_browser" name="topAuth_browser" class="fas fa-eye" alt="瀏覽" title="瀏覽"></i>
            <i id="topAuth_addnew" name="topAuth_addnew" class="fas fa-file-medical" alt="新增" title="新增"></i>
            <i id="topAuth_edit" name="topAuth_edit" class="fas fa-edit" alt="編輯" title="編輯"></i>
            <i id="topAuth_delete" name="topAuth_delete" class="fas fa-trash" alt="刪除" title="刪除"></i>
        </li>
        <!-- /頁面權限圖示顯示 -->
        <li class="nav-item nav-toppanel">
          <i id="topBut_upload" name="topBut_upload" class="fas fa-upload" alt="上傳" title="上傳"></i>
          <i id="topBut_add" name="topBut_add" class="fas fa-circle-plus" alt="新增" title="新增"></i>
          <i id="topBut_save" name="topBut_save" class="fas fa-save" alt="儲存" title="儲存"></i>
          <i id="topBut_reset" name="topBut_reset" class="fas fa-sync" alt="重置" title="重置"></i>
          <i id="topBut_delete" name="topBut_delete" class="fas fa-trash-alt" alt="刪除" title="刪除"></i>
        </li>
        <li class="nav-item nav-topsearch">
          <i id="topBut_search" name="topBut_search" class="fas fa-search active" alt="開啟搜尋視窗" title="開啟搜尋視窗"></i>
        </li>
        <li class="nav-item nav-topexport">
          <i id="topBut_excel" name="topBut_excel" class="fas fa-file-excel active" alt="輸出 Excel" title="輸出 Excel"></i>
          <i id="topBut_print" name="topBut_print" class="fas fa-solid fa-print active" alt="頁面列印" title="頁面列印"></i>
        </li>
        <li class="nav-item nav-topbkredir">
          <i id="topBut_bkurl" name="topBut_bkurl" class="fas fa-reply-all active" alt="返回關聯前頁" title="返回關聯前頁"></i>
        </li>
      </ul>
      <!-- -------------------- /左側功能連結區 -------------------- -->
      <!-- -------------------- 右側功能連結區 --------------------- -->
        <ul class="navbar-nav ml-auto" style="height:32px;margin-top:4px;">
          <!-- 個人&公共訊息下拉選單 -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
              <i class="far fa-comments"></i>
              <span id="badge_NAVBAR_MESS" class="badge badge-danger navbar-badge" style="margin-top:-5px;margin-right:-3px;"><?php //echo $unreadBadge; ?></span>
            </a>
            <div id="messDropdown" class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
              <a href="mess_board.php" target="iFrame_main" class="dropdown-item dropdown-footer">瀏覽所有個人及公共訊息</a>
            </div>
          </li>
        <!-- /個人&公共訊息下拉選單 -->
        <!-- 系統通知下拉選單 -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
              <i class="far fa-bell"></i>
              <span id="badge_notify" class="badge badge-warning navbar-badge" style="margin-top:-5px;margin-right:-3px;"></span>
            </a>
            <div id="notifyDropdown" class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
              <a href="mess_notifylist.php" target="iFrame_main" class="dropdown-item dropdown-footer">瀏覽所有的系統通知訊息</a>
            </div>
          </li>
        <!-- /系統通知下拉選單 -->
        <!-- 管理者功能選單 -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <img src="<?php echo DOC_PATH_LAYER.$_SESSION['SESS_AD_THUMB'].'?t='.time(); ?>" width="30" height="30" class="img-circle elevation-1 adTopNaviPhoto" style="margin-top: -8px;">
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
              <div style="text-align:center;padding-top:8px;">管理執行者</div>
              <div class="image" style="margin-top:10px;text-align: center;">
                <img src="<?php echo DOC_PATH_LAYER.$_SESSION['SESS_AD_THUMB'].'?t='.time(); ?>" width="80" height="80" class="img-circle elevation-2 adTopNaviPhoto">
              </div>
              <span class="dropdown-header"><?php echo $_SESSION['SESS_AD_NICK']; ?></span>
              <div class="dropdown-divider"></div>
              <a href="ad_profile.php?currID=<?php echo $_SESSION['SESS_AD_ID']; ?>" target="iFrame_main" class="dropdown-item" style="cursor:pointer;">
                <i class="fas fa-user mr-1 fa-pull-right"></i> 編輯個人資料
              </a>
              <div class="dropdown-divider"></div>
              <div id="lockscreen" class="dropdown-item" style="cursor:pointer;">
                <i class="fas fa-lock mr-1 fa-pull-right"></i> 鎖定(暫離)系統
              </div>
              <div class="dropdown-divider"></div>
              <div id="logout" class="dropdown-item" style="cursor:pointer;">
                <i class="fas fa-power-off mr-1 fa-pull-right"></i> 登出
              </div>
            </div>
          </li>
          <li class="nav-item">&nbsp;&nbsp;&nbsp;&nbsp;</li>
        <!-- /管理者功能選單 -->

        <!-- 右側隱藏功能區按鈕 -->
        <!--    
          <li class="nav-item">
            <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
              <i class="fas fa-th-large"></i>
            </a>
          </li>
        -->    
        <!-- /右側隱藏功能區按鈕 -->
        </ul>
    </nav>
    <!-- /////////////////////////////////////////////////////////// -->
    <!-- ///////////////////////// 左側方導覽區 ////////////////////// -->
    <!-- /////////////////////////////////////////////////////////// -->
    <!-- 左側導覽區內容 -->
    <!-- [變更1] .main-sidebar 與 .sidebar-dark-primary 改為 .app-sidebar 與 data-bs-theme="dark" -->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
      <!-- 左上方主圖標示 Logo：[變更2] 必須包裹在 .sidebar-brand 內 -->
      <div class="sidebar-brand" style="height: 53px;">
        <a href="5sladmin_startpage.html" target="iFrame_main" class="brand-link">
          <img src="../imageFiles/5slLOGO_sW.png" alt="5 Season Life Logo" class="brand-image shadow" style="vertical-align:baseline;margin-top:-4px;">
          <!-- [變更3] font-weight-light 改為 fw-light -->
          <span class="brand-text fw-normal" style="margin-top:7px;margin-bottom:-12px;">
            5SeasonLife<span class="brand-text fw-light" style="color:#888888"> ADMIN</span>
          </span>
        </a>
      </div>
      <!-- /左上方主圖標示 Logo -->

      <!-- 功能區塊：[變更4] .sidebar 改為 .sidebar-wrapper -->
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
          <!-- [變更5] 改用 .sidebar-menu 與 data-lte-toggle="treeview"；text-sm 改為 small -->
          <ul class="nav sidebar-menu flex-column small nav-child-indent nav-compact" data-lte-toggle="treeview" role="menu" data-accordion="true">
            <?php
              $i = 0;
              while($i < count($sidebar)){ // 樹狀首層「功能主分類」
                  $sidebar2 = $sidebar[$i]['subgroup'] ?? array();
                  $subnum2 = count($sidebar2);
                  $topIcon = null != $sidebar[$i]['icon'] ? " ".$sidebar[$i]['icon'] : '';
                  
                  echo '<li data-pagecode="'.$sidebar[$i]['pagecode'].'" class="nav-item">';
                  echo '<a href="'.$sidebar[$i]['url'].'" class="nav-link">';
                  echo '<i class="nav-icon fas'.$topIcon.'"></i>';
                  echo '<p>';
                  echo $sidebar[$i]['title'];
                  
                  // [變更6] 徽章改用 float-end me-3（避免與最右側的 nav-arrow 箭頭重疊）
                  if(!empty($sidebar[$i]['badgeNum']) || !empty($sidebar[$i]['badgeClass'])){
                      echo '<span id="badge_'.$sidebar[$i]['pagecode'].'" name="badge_'.$sidebar[$i]['pagecode'].'" class="badge float-end me-3';
                      if(!empty($sidebar[$i]['badgeClass'])) { echo ' '.$sidebar[$i]['badgeClass']; }
                      echo '">';
                      if(!empty($sidebar[$i]['badgeNum'])) { echo $sidebar[$i]['badgeNum']; }
                      echo '</span>';
                  }

                  // [變更7] AdminLTE 4 樹狀箭頭改用 .nav-arrow，且預設向右 (fa-angle-right)，展開時會自動旋轉向下
                  if($subnum2 > 0){
                      echo '<i class="nav-arrow fas fa-angle-right"></i>';
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
                          $subIcon2 = ' far fa-dot-circle';
                          if($subnum3 > 0){
                              if(null != $sidebar2[$si]['icon']){ $subIcon2 = " fas ".$sidebar2[$si]['icon']; }
                          }

                          echo '<li data-pagecode="'.$sidebar2[$si]['pagecode'].'" class="nav-item">';
                          echo '<a href="'.$sidebar2[$si]['url'].'" class="nav-link">';
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

                          // 第二層箭頭或狀態圖示
                          if($subnum3 > 0) { 
                              echo '<i class="nav-arrow fas fa-angle-right"></i>'; 
                          } else { 
                              echo '<i class="fas activeicon float-end"></i>'; 
                          }
                          echo '</p>';
                          echo '</a>';

                          // 第三層選單
                          if($subnum3 > 0){
                              echo '<ul class="nav nav-treeview">';
                              $ti = 0;
                              while($ti < $subnum3){
                                  echo '<li data-pagecode="'.$sidebar3[$ti]['pagecode'].'" class="nav-item">';
                                  echo '<a href="'.$sidebar3[$ti]['url'].'" class="nav-link">';
                                  echo '<i class="nav-icon far fa-circle"></i>';
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

                                  echo '<i class="fas activeicon float-end"></i>';
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
          </ul> <!-- [修正] 補上原本遺漏的 </ul> 閉合標籤 -->
        </nav>
        <!-- /樹狀導航列 -->
      </div>
      <!-- /功能區塊 -->
    </aside>
    <!-- /左側導覽區內容 -->
    <!-- /////////////////////////////////////////////////////////// -->
    <!-- //////////////////////// 主頁面 iFrame ///////////////////// -->
    <!-- /////////////////////////////////////////////////////////// -->
    <div class="content-wrapper iframe-mode" data-widget="iframe" data-loading-screen="750">
      <div class="tab-content">
        <iframe id="iFrame_main" name="iFrame_main" src=""></iframe>
      </div>
    </div>
    <!-- /////////////////////////////////////////////////////////// -->
    <!-- ////////////////////////// 下方訊息區 /////////////////////// -->
    <!-- /////////////////////////////////////////////////////////// -->
    <!--  -->
    <footer class="main-footer text-sm">
      <span style="font-size:0.75em;"><b>Version</b> <span style="color:#C35050;"><?php $getVersion = $DB->getConfigVal('sys_version'); echo $getVersion['config_value']; ?></span>&nbsp;&nbsp;&nbsp;&nbsp;Copyright &copy; 2023 <strong><?php $getENname = $DB->getConfigVal('comp_enname');echo $getENname['config_value']; ?> </strong> All rights reserved.</span>
      <div class="float-right d-none d-sm-inline-block">
        <span style="font-size:0.75em;"><b>WebSocket:</b></span>&nbsp;&nbsp;<button id="ind_websocket" class="socketalert"></button>
      </div>
    </footer>
    <!-- /下方訊息區 -->
  </div>
  <!-- ./wrapper -->

<!-- jQuery -->
<script src="../pluginFiles/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../pluginFiles/jquery-ui/jquery-ui.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Toastr -->
<script src="../pluginFiles/toastr/toastr.min.js"></script>
<!-- overlayScrollbars -->
<script src="../pluginFiles/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin_outframe.js<?php echo "?t=".time(); ?>"></script>
<script src="../scriptFiles/page.basic.js"></script>
<!-- 5SeasonLife for demo purposes 
<script src="dist/js/demo.js"></script> -->
<script type="text/javascript" src="../scriptFiles/fliptimer/lib/jquery-backward-timer.src.js"></script>
<script type="text/javascript">
////////////////////////////////////////////////////////////////
// ---------------- JAVASCRIPTS 全域調用參數設置 --------------- //
////////////////////////////////////////////////////////////////






////////////////////////////////////////////////////////////////
// -------------- JAVASCRIPTS FUNCTIONs 全域調用 -------------- //
// -------------- 可接收include檔案要求後自定義動作 -------------- //
////////////////////////////////////////////////////////////////

  window.treeviewCheck = function(item = '', page = '', item2 = '') {
    if (item === '' || page === '') { return false; }
    let elM = $('#sidebarL');
    let currItemELM = elM.find('li.nav-item[data-pagecode="' + item + '"]');
    let currPageELM = elM.find('li.nav-item[data-pagecode="' + page + '"] a.nav-link');
    // 如果有第二個父節點
    if (item2 !== '') { 
      var currItemELM2 = elM.find('li.nav-item[data-pagecode="' + item2 + '"]'); 
    }
    // 只要目標頁面目前不是 active，才進行外觀變更
    if (!currPageELM.hasClass('active')) {
      // 1. 【清除舊狀態】移除所有舊的 active 與選單開啟狀態
      elM.find('a.nav-link.active').removeClass('active');
      // 找到所有目前開著的選單，收合它們並移除相關 Class
      elM.find('li.menu-open').each(function() {
          // 如果這個開著的選單不是我們即將要打開的選單，才把它關掉
          if ($(this).attr('data-pagecode') !== item && (item2 === '' || $(this).attr('data-pagecode') !== item2)) {
              $(this).removeClass('menu-is-opening menu-open');
              $(this).find('> .nav-treeview').slideUp(200); // 加上時間讓動畫平滑
          }
      });
      // 2. 【設定新狀態】為新頁面加上高亮 active
      currPageELM.addClass('active');
      // 3. 【開啟父選單】確保目標分類節點被順暢展開
      if (currItemELM.length > 0 && !currItemELM.hasClass('menu-open')) {
          currItemELM.addClass('menu-is-opening menu-open');
          // 使用 stop(true, true) 確保多次切換時動畫不會卡住
          currItemELM.find('> .nav-treeview').stop(true, true).slideDown(200); 
      }
      // 4. 【處理第二層父選單】如果有傳入第二個父節點，也一併開啟
      if (item2 !== '' && currItemELM2 && currItemELM2.length > 0 && !currItemELM2.hasClass('menu-open')) {
          currItemELM2.addClass('menu-is-opening menu-open');
          currItemELM2.find('> .nav-treeview').stop(true, true).slideDown(200);
      }
      //console.log("【狀態】選單外觀同步完成（已避開超連結跳轉）");
    }
  };
  window.treeviewReset = function() {
      $('.nav-sidebar .active').removeClass('active');
      // 2. 尋找外框側邊欄所有處於「展開狀態」的樹狀目錄，並將其收起
      $('.nav-sidebar .menu-open').each(function() {
          // 移除 AdminLTE 判斷展開的 class
          $(this).removeClass('menu-open');
          // 將子選單用動畫縮回去，並確保顯示狀態為隱藏
          $(this).children('.nav-treeview').slideUp().css('display', 'none');
      });

  }








    ///////////////////////////////////////////////////////
    //--------------- 網頁歷史紀錄操作全域函數 --------------//
    ///////////////////////////////////////////////////////
// 1. 每當內頁發生網址切換，即時更新外框的 F5 記憶
window.updateCurrentIframeUrl = function(url) {
    sessionStorage.setItem('Current_Iframe_URL', url);
};

// 2. 手動精準清除函數 (特定前綴清除法)
window.clearIframeCache = function() {
    console.log("【外框】執行精準清除快取...");
    var targetPrefixes = ['DT_State_', 'Tab_State_', 'Last_List_URL', 'Last_Detail_URL', 'Manual_DT_Page_'];
    
    Object.keys(sessionStorage).forEach(function(key) {
        var shouldRemove = targetPrefixes.some(function(prefix) { return key.indexOf(prefix) === 0; });
        if (shouldRemove) { 
            sessionStorage.removeItem(key); 
        }
    });
};

function backIframe() {
    console.log("【外框守門員】收到返回請求，觸發原生歷史回退...");
    var activeIframe = document.querySelector('.tab-pane.active iframe') || 
                      document.querySelector('iframe:not([style*="display: none"])') ||
                      document.getElementById('iFrame_main');

    if (activeIframe && activeIframe.contentWindow) {
        // 直接、全權移交給瀏覽器最乾淨的歷史回退，絕對不再轉址製造新歷史節點
        activeIframe.contentWindow.history.back();
    }
}
//////////////////////////////////////////////////////////////////////////









  //直接取得iFrame中網址字串（URI）
  function getUrlHex() {
    try {
      const iframeElement = document.getElementById('iFrame_main');
      const iframeUrl = iframeElement.contentWindow.location.href;
      const url = new URL(iframeUrl);
      let fullPATH = url.pathname.split('/').pop() + url.search;
      return urlEncodeHex(fullPATH);
    } catch (error) {
      console.error("無效的網址字串");
      return "";
    }
  }
  /** *************** JavaScript 十六進位 (Hex) 字串加解密 ************************* **/
  /** *************** 可使用URL傳遞的字串 ******************** **/
  //加密：字串轉換成純英文與數字
  function urlEncodeHex(text) {
    const encoder = new TextEncoder();
    const bytes = encoder.encode(text);
    return Array.from(bytes).map(b => b.toString(16).padStart(2, '0')).join('');
  }
  //解密：純英文與數字 還原成原字串
  function urlDecodeHex(hexString) {
      const bytes = new Uint8Array(hexString.match(/.{1,2}/g).map(byte => parseInt(byte, 16)));
      const decoder = new TextDecoder();
      return decoder.decode(bytes);
  }
  //更新未讀訊息
  function unreadUpdate(nuM) {
    //更新左側導航列未讀標註
    var $badge = $('#badge_PG_MESSBOARD');
    if($badge.length == 0){ return false; }
    var $badgeNum = parseInt($badge.text()) - parseInt(nuM);//扣除已讀的訊息數量
    if($badgeNum > 0){//更新顯示的未讀標註
      $badge.text($badgeNum);
    } else {
      $badge.text('');
    }
    //更新左側樹狀導航列中的未讀標註
    var $badge2 = $('#badge_ITEM_MESS');
    var $badgeNum2 = parseInt($badge2.text()) - parseInt(nuM);
    if($badgeNum2 > 0){
      $badge2.text($badgeNum2);
    } else {
      $badge2.text('');
    }
    //更新上方導航列中的未讀標註
    var $badgeT = $('#badge_NAVBAR_MESS');
    var $badgeNumT = parseInt($badgeT.text()) - parseInt(nuM);
    if($badgeNumT > 0){
      $badgeT.text($badgeNumT);
    } else {
      $badgeT.text('');
    }
    return true;
  }
  //------- 取得簡易訊息(上方導航列使用) -------
  //------- 預設取得最新50筆，顯示10筆，當顯示的紀錄被點閱後自動刪除 -------
  //------- 由後續被隱藏的紀錄遞補顯示，直到紀錄低於10筆時重新獲取50筆 -------
  function getEasyMess(nuM=50,daTa='') {
    if(daTa){
      var messShort = daTa.chat_message.length > 20 ? daTa.chat_message.substring(0, 20) + '...' : daTa.chat_message;//將最新訊息縮短顯示
      //var postPast = Math.floor((parseInt('<?php echo time(); ?>') - daTa.chat_published) / 3600);
      //JazFIX: 要找尋時間數據的傳遞軌跡，將時間格式徹底更改
      var postPast = Math.floor((parseInt('<?php echo time(); ?>') - daTa.chat_published) / 3600);
            if (postPast > 24) {
                postPast = Math.floor(postPast / 24) + ' 天前';
            } else {
              if(postPast<=0){
                    postPast = '小於1小時';
                } else {
                    postPast = postPast + ' 小時前';
                }
            }
      var chatAvatar = checkIfFileExist(`../uploadFiles/mb/mbphoto_${daTa.chat_postid}_thumb.jpg`);
      var newUnread = `
          <a href="mess_board.php?chatType=1&joinID=${daTa.chat_joinid}" target="iFrame_main" class="dropdown-item ezmess" data-chatid="${daTa.chat_id}" data-joinid="${daTa.chat_joinid}">
            <div class="media">
              <img src="${chatAvatar}" alt="User Avatar" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">${daTa.chat_poster}</h3>
                <p class="text-sm">${messShort}</p>
                <p class="text-xxs text-muted"><i class="far fa-clock mr-1"></i> ${postPast}</p>
              </div>
            </div>
            <div class="dropdown-divider" style="margin-top:4px;"></div>
          </a>
      `;
      $('#messDropdown').prepend(newUnread);
      return true;
    } else {
      $.ajax({
            type: "POST",
            url: "../funcFiles/mb_chat_func.php",
            data: {
              'nuM': nuM, 
              'formAcce': 'func_chat_ezmess',
              'token': getTokenVal()
            },
            dataType: 'json',
            async: false, 
          success: function (data) {
              if(data['RTN']==1){
                var d = data.messRes;
                if(data.messRes.length > 0){
                  var content = '';
                  for (var i = 0; i < d.length; i++) {
                    content += `
                        <a href="mess_board.php?chatType=1&joinID=${d[i].chat_joinid}" target="iFrame_main" class="dropdown-item ezmess" data-chatid="${d[i].chat_id}" data-joinid="${d[i].chat_joinid}">
                          <div class="media">
                            <img src="${d[i].chatAvatar}" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                            <div class="media-body">
                              <h3 class="dropdown-item-title">${d[i].chat_poster}</h3>
                              <p class="text-sm">${d[i].message}</p>
                              <p class="text-xxs text-muted"><i class="far fa-clock mr-1"></i> ${d[i].postPast}</p>
                            </div>
                          </div>
                          <div class="dropdown-divider" style="margin-top:4px;"></div>
                        </a>
                    `;
                  }
                }
                $('#messDropdown').prepend(content);
              }
            },
            error: function (data) {
              console.log(data);
              //window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
              toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
              return false;
            }
        });
      }

  }
  //getEasyMess();//開頁時同時取得簡易訊息
  //------- 取得簡易通知(上方導航列使用) -------
  //------- 預設取得最新50筆，顯示10筆，顯示的通知被點閱後不會刪除 -------
  //------- 由後續被隱藏的通知遞補顯示，直到通知低於10筆時重新獲取50筆 -------
  function getEasyNotify(nuM=50,daTa='') {
    if(daTa){
      var infoShort = daTa.noti_content.length > 20 ? daTa.noti_content.substring(0, 20) + '...' : daTa.noti_content;//將最新訊息縮短顯示
      var postPast = Math.floor((parseInt('<?php echo time(); ?>') - daTa.noti_published) / 3600);
            if (postPast > 24) {
                postPast = Math.floor(postPast / 24) + ' 天前';
            } else {
              if(postPast<=0){
                    postPast = '小於1小時';
                } else {
                    postPast = postPast + ' 小時前';
                }
            }
      var colorClass = daTa.cAlert !== '' ? ' color' + daTa.cAlert : '';
      var newInfo = `
            <a href="mess_notidetail.php?currID=${daTa.noti_id}" target="iFrame_main" class="dropdown-item eznotify" style="position:relative;" data-ifid="${daTa.noti_id}">
              <i class="fas fa-envelope mr-2${colorClass}"></i>${infoShort}
              <span class="text-muted text-sm" style="position:absolute;bottom:8px;right:8px;"> ${postPast}</span>
              <div class="dropdown-divider" style="margin-top:24px;"></div>
            </a>
      `;
      $('#notifyDropdown').prepend(newInfo);
    } else {
      $.ajax({
            type: "POST",
            url: "../funcFiles/mb_chat_func.php",
            data: {
              'nuM': nuM, 
              'formAcce': 'func_chat_eznotify',
              'token': getTokenVal()
            },
            dataType: 'json',
            async: false, 
          success: function (data) {
              if(data.RTN == 1){
                var d = data.notifyRes;
                if(d.length > 0){
                  var infocontent = '';
                  for (var i = 0; i < d.length; i++) {
                    var colorClass = d[i].cAlert !== '' ? ' color' + d[i].cAlert : '';
                    infocontent += `
                        <a href="mess_notidetail.php?currID=${d[i].noti_id}" target="iFrame_main" class="dropdown-item eznotify" style="position:relative;" data-ifid="${d[i].noti_id}">
                          <i class="fas fa-envelope mr-2${colorClass}"></i>${d[i].content}
                          <span class="text-muted text-sm" style="position:absolute;bottom:8px;right:8px;"> ${d[i].postPast}</span>
                          <div class="dropdown-divider" style="margin-top:24px;"></div>
                        </a>
                    `;
                  }
                }
                $('#badge_notify').html(data.totalNum);
                $('#notifyDropdown').prepend(infocontent);
              }
            },
            error: function (data) {
              console.log(data);
              toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
            }
        });
      }

  }
  //getEasyNotify();//開頁時同時取得簡易訊息
  //-------- 檢查頭像檔案是否存在 ---------
  function checkIfFileExist(fileName) {
    filename = fileName.trim();
    var response = $.ajax({
      url: filename,
      type: 'HEAD',
      async: false
    }).status;	
    return (response != "200") ? '../imageFiles/nopic_user_64x64.jpg' : filename;
  }
  //-------- 若有返回字串時重新整理 iframe ---------
  <?php if(!empty($iFrameURI) && strlen($iFrameURI) % 2 === 0){ ?>
    const currIframe = document.getElementById('iFrame_main');
    currIframe.src = currIframe.src;

    window.addEventListener('DOMContentLoaded', () => {
        // 直接拿掉問號（參數）和井字號（錨點），只保留純路徑
        window.history.replaceState({}, '', window.location.pathname);
    });


  <?php } ?>






////////////////////////////////////////////////////////////////
// ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
// ---------------------------------------------------------- //
////////////////////////////////////////////////////////////////
  $(document).ready(function() {
    // 測試用
    $('#But_test').click(function(e){
      //$("#iFrame_main")[0].contentWindow.avatarObj[1][1142]="test";
      //$("#iFrame_main").contents().find( "a" )
      //unreadUpdate(-1);
    });
    $('#loadingMask span > span').dblclick(function(){$('#loadingMask').hide();});

    //----------- WebSocket 功能程式碼(模組可變動) -----------//
    let pingCount;
    let reConnect;
    function websocketISon() {
      $('#ind_websocket').removeClass('socketalert');
    }
    function websocketISoff() {
      $('#ind_websocket').addClass('socketalert');
    }
    //------- 頁面開啟時啟動監聽 --------
    let onBoard = false;
    let chatType = parseInt('<?php echo $chatType; ?>');
    let inchargeID = 0;//本頁面僅能設定為0
    let joinID = 0;
    let postID = parseInt('<?php echo $_SESSION['SESS_AD_ID']; ?>');
    let postType = 1; //1-Admin, 2-User //本頁面僅能設定為1

    let ws = new WebSocket("wss://www.5seasonlife.com:8089");
    //let ws = new WebSocket("ws://localhost:8089");
    ws.onopen = function() {
      websocketISon();
      // 加入群組
      ws.send(JSON.stringify({
        action: "onChat",
        chat_type: chatType,
        chat_joinid: joinID,
        chat_postid: postID,
        chat_posttype: postType
      }));
    };
    ws.onmessage = function(event) {
      let cObj = JSON.parse(event.data);
      if (cObj.action == "onlineList") {
        return false;
      }
      else if(cObj.action=="connected"){//PingPong
        clearTimeout(pingCount);
        websocketISon();
        pingCount = setTimeout(websocketISoff, 1500);//1.5秒後若未接受到 Ping 訊號，則切換為未連線，此值要高於 Server pingNow 間隔值
        return;
      }
      else if(cObj.action=="notify"){// {"action":"notify","content":"","level":"1","mode":"1","forid":"10"}
        if(cObj.noti_content === ""){
          return false;
        }
        //if(cObj.mode == 1 || cObj.mode == 2) return false;//避免重複顯示通知
        //getEasyNotify(1,cObj);//插入並顯示最新通知(取自webSocket)
        //toastr[cObj.cAlert](cObj.noti_content);
      }
      //當為訊息或消息或是廣播，且應答種類為單聊時
      if(cObj.chat_type==1 && cObj.chat_posttype==2 && (cObj.action === "message" || cObj.action === "broadcast")){
        unreadUpdate(-1);//更新外層未讀數值
        //getEasyMess(1,cObj);//插入並顯示單一最新訊息(取自webSocket)
      }
    };
    ws.onclose = function(event) {
      ws.send(JSON.stringify({
        action: "outChat",
        chat_type: chatType,
        chat_joinid: joinID,
        chat_postid: postID,
        chat_posttype: postType
      }));
    };
    ws.onerror = function() {
      
      //
    };


    function disconnect(){
      ws.close();
      ws.onclose = () => {
        //appendSystemMessage('-- Server Closed --', 'system');
      }
    }

    function reconnect() {
      let ws = new WebSocket("wss://www.5seasonlife.com:8089");
      ws.onopen = function() {
      // 加入應答服務
        ws.send(JSON.stringify({
                action: "onChat",
              chat_type: chatType,
            chat_joinid: joinID,
            chat_postid: postID,
          chat_posttype: postType
        }));
        //appendSystemMessage('-- Server Reconnected --', 'system');
        //每隔5秒發送心跳消息
        //heartBeats.start();
      }
    }





    //離開頁面前的動作(關閉 WebSocket 服務)
    $(window).bind('beforeunload', function(){
      ws.onclose();
    });

    //-------------------------------------------------------//
    var wrapperHeight = parseFloat($('.content-wrapper').css('height'));
    $('#iFrame_main').height(wrapperHeight);//無需減除上方導航列的高度
    //JazFIX: 移自 form.init.inc.js
    /////////////// Top Navigator 按鈕群操作 //////////////////////
    $('#topBut_upload').click(function(e){ 
      e.preventDefault();
      if(!$(this).hasClass('active')){ return false;}
      $("#iFrame_main")[0].contentWindow.doButUpload();
    });
    // 操作按鈕框_儲存/（新增/更新/上傳 通用）
    $('#topBut_add').click(function(e){ 
      e.preventDefault();
      if(!$(this).hasClass('active')){ return false;}
      if (confirm('當按下按鍵繼續進行操作時，即代表同意遵循「人生第五季管理辦法」進行操作！\r\r確定進行資料新增操作？！')){
        $("#iFrame_main")[0].contentWindow.doButAdd();
      }
    });
    // 操作按鈕框_存儲
    $('#topBut_save').click(function(e){ 
      e.preventDefault();
      if(!$(this).hasClass('active')){ return false;}
      if (confirm('當按下按鍵繼續進行操作時，即代表同意遵循「人生第五季管理辦法」進行操作！\r\r確定進行資料儲存操作？！')){
        $("#iFrame_main")[0].contentWindow.doButSave();
      }
    });
    // 操作按鈕框_復原
    $('#topBut_reset').click(function(e){ 
      e.preventDefault();
      if(!$(this).hasClass('active')){ return false;}
      $("#iFrame_main")[0].contentWindow.doButReset();
    });
    // 操作按鈕框_刪除
    $('#topBut_delete').click(function(e){ 
      e.preventDefault();
      if(!$(this).hasClass('active')){ return false;}
      $("#iFrame_main")[0].contentWindow.doButDelete();
    });
    //
    
    $('#topBut_bkurl').click(function(e){
      //$("#iFrame_main")[0].contentWindow.doReDirect();
      //alert('tyjutyu');
      backIframe();
    });
    
    //
    $('#topBut_search').click(function(e){
      e.preventDefault();
      /*
      var sidebarElm = $("#iFrame_main").contents().find("#sidebarControl");
      if (sidebarElm.length) {
        sidebarElm.click();
      }
      */
      $("#iFrame_main")[0].contentWindow.doButSerach();
    });
    $('#topBut_excel').click(function(e){
      e.preventDefault();
      $("#iFrame_main")[0].contentWindow.doButExcel();
    });
    $('#topBut_print').click(function(e){
      e.preventDefault();
      $("#iFrame_main")[0].contentWindow.doButPrint();
    });

    //
    $('#lockscreen').click(function(e){
      window.location.assign("../lockscreen.php?backURI=" + getUrlHex());
    }); 
    $('#logout').click(function(e){
      if(!confirm('確定登出系統？！')){return false;}
      window.location.replace("../5sladmin.php");
    });
    //------------------------------------------------------------------------


    /*NOTE: 左側樹狀目錄首層數值標註顯示控制 */
    $('#sidebarL > ul > li > a').click(function() { 
      var parentLI = $(this).parent();
      var bedgeSPAN = $(this).parent().find('> a span');
      var allLIspan = parentLI.parent().find('> li').find('span');//遍歷所有下一層的 Li
      if(parentLI.hasClass('menu-open')){
        bedgeSPAN.css("visibility", "visible");
      } else {
        allLIspan.css("visibility", "visible");
        bedgeSPAN.css("visibility", "hidden");
      }
    });
    
    //------------------------------------------------------------------------
    //let countdownSec = parseInt('<?php $getidleout = $DB->getConfigVal('sys_idleout'); echo $getidleout['config_value']; ?>');
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
    //------------------------------------------------------
    $('body').on('keypress click', function(){
      $('#timer_on_exhausted').backward_timer('reset');
    });
    ///////////////////////////////////////////////////////
    //--------------- 網頁歷史紀錄操作區域函數 --------------//
    ///////////////////////////////////////////////////////
    var iframe = document.getElementById('iFrame_main');
    var lastIframeUrl = sessionStorage.getItem('Current_Iframe_URL');
    
    // 💡 F5 重新整理原地復活機制：重新整理時，把 src 屬性指回剛剛的內頁
    if (lastIframeUrl) {
        iframe.setAttribute('src', lastIframeUrl);
    } else {
        iframe.setAttribute('src', '5sladmin_startpage.html'); 
    }

    // 💡 側邊欄點擊事件：切換主功能時精準清洗舊狀態
    $('.main-sidebar, .nav-sidebar').on('click', 'a:not([data-widget="pushmenu"])', function() {
        //alert('側邊欄點擊事件');
        //console.log("【外框選單切換】切換不同主功能，清除分頁快取");
        sessionStorage.removeItem('Current_Iframe_URL');
        if (typeof window.clearIframeCache === 'function') {
            window.clearIframeCache();
        }
    });    
//////////////////////////////////////////////////////////////////////////
  });

</script>

</body>
</html>
