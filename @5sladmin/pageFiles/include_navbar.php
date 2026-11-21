<!-- ######################################################### -->
<!-- #################### 上方導航區嵌入檔案 #################### -->
<!-- ######################################################### -->
<?php
//if(!defined('pcSave')){ define('pcSave',0); }
//if(!defined('bkRedir')){ define('bkRedir',0); }
?>
<style>
  #topBut_save{
    color: #D6DAE1;
  }
  #topBut_add.active,#topBut_save.active,#topBut_upload.active,#topBut_reset.active,#topBut_delete.active,#topBut_bkurl.active,#topBut_search.active {
    color: #2C333E;
    cursor:pointer;
  }
  #topBut_bkurl.active:hover {
    color: #5F6E86;
  }
  #topBut_save.active:hover {
    color: #44a273;
  }
  #topBut_upload.active:hover, #topBut_add.active:hover {
    color: #236FB7;
  }
  #topBut_reset.active:hover {
    color: #ECC244;
  }
  #topBut_delete.active:hover {
    color: #C35050;
  }
  #topBut_search.active:hover {
    color: #C35050;
  }
  /* -------------------------- */
  /*
  .nav-item.nav-authpanel,.nav-item.nav-toppanel,.nav-item.nav-topbkredir{
    display: none;
  }
  */
  .nav-item.nav-authpanel{
    display: none;
    padding-left:2px;
    padding-right:2px;
    border:1px solid #BAC3CD;
    border-radius: 0.35rem;
  }
  .nav-item.nav-authpanel i{
    margin:8px 4px 0px 4px;
    color: #DADFE4;
  }
  .nav-item.nav-authpanel i.active{
    color: #2C333E;
  }
  .nav-item.nav-toppanel{
    display: none;
    padding-top:4px;
    padding-left:2px;
    padding-right:2px;
    border:1px solid #BAC3CD;
    border-radius: 0.35rem;
    color:#DADFE4;
  }
  .nav-item.nav-toppanel i{
    display: none;
    margin:8px 4px 0px 4px;
    font-size:1.6em;
  }
  .nav-item.nav-topbkredir{
    display: none;
    border:1px solid #BAC3CD;
    border-radius: 0.35rem;
    padding-top:4px;
    padding-left:2px;
    padding-right:2px;
    color:#DADFE4;
  }
  .nav-item.nav-topbkredir i{
    display: none;
    margin:8px 4px 0px 4px;
    font-size:1.6em;
  }
  .nav-item.nav-topsearch{
    display: block;
    border:1px solid #BAC3CD;
    border-radius: 0.35rem;
    padding-top:4px;
    padding-left:2px;
    padding-right:2px;
    color:#DADFE4;
  }
  .nav-item.nav-topsearch i{
    display: block;
    margin:8px 4px 0px 4px;
    font-size:1.6em;
  }
</style>
<nav class="main-header navbar navbar-expand text-sm navbar-white navbar-light">
  <!-- --------------------- 轉移 Focus 目標 --------------------- -->
    <input type="text" id="searchURI" name="searchURI" style="width:1px;height:1px;opacity:0;" value="<?php //echo isset($searchURI) ? $searchURI : '';//此值來自列表主頁的 $searchURI ?>">
  <!-- --------------------- 左側功能連結區 --------------------- -->
  <ul class="navbar-nav" style="height:32px;margin-top:4px;gap:10px;">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button" style="padding-left:0;padding-right:10px;">
        <i class="fas fa-bars"></i>
      </a>
    </li>
    <!--
    <li class="nav-item d-none d-sm-inline-block">
      <a href="console3.php" class="nav-link">Home</a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="#" class="nav-link">Contact</a>
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
      <i id="topBut_search" name="topBut_search" class="fas fa-magnifying-glass" alt="開啟搜尋視窗" title="開啟搜尋視窗"></i>
    </li>
    <li class="nav-item nav-topbkredir">
      <i id="topBut_bkurl" name="topBut_bkurl" class="fas fa-reply-all" alt="返回關聯前頁" title="返回關聯前頁"></i>
    </li>
    <!-- 上方搜尋條 -->
      <!--
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>
      -->
    <!-- /上方搜尋條 -->
    </ul>
  <!-- -------------------- /左側功能連結區 -------------------- -->
  <!-- -------------------- 右側功能連結區 --------------------- -->
    <ul class="navbar-nav ml-auto" style="height:32px;margin-top:4px">
      <!-- 個人&公共訊息下拉選單 -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-comments"></i>
          <span class="badge badge-danger navbar-badge" style="margin-top:-5px;margin-right:-3px;">3</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="../imageFiles/nopic_user_64x64.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  [個人]Brad Diesel
                  <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">可以隨時聯絡我嗎？我有重要的問題請教...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 小時前</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="../imageFiles/nopic_user_64x64.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  [團體]信議房屋
                  <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">請問，線上支付具體步驟......</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 小時前</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="../imageFiles/nopic_user_64x64.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  [個人]Nora Silvester
                  <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">The subject goes here</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 10分鐘前</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="mess_board.php" class="dropdown-item dropdown-footer">瀏覽所有個人及公共訊息</a>
        </div>
      </li>
    <!-- /個人&公共訊息下拉選單 -->
    <!-- 系統通知下拉選單 -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge" style="margin-top:-5px;margin-right:-3px;">152</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header">共 152 筆新的系統通知訊息</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- <i class="fas fa-envelope mr-2"></i> --> 會員125468線上支付$500元
            <span class="float-right text-muted text-sm">3m</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- <i class="fas fa-users mr-2"></i> --> 達到同時在線最高警示數量
            <span class="float-right text-muted text-sm">12h</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- <i class="fas fa-file mr-2"></i> --> 課程商品編號54862交易結案
            <span class="float-right text-muted text-sm">2d</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="sys_notice.php" class="dropdown-item dropdown-footer">瀏覽所有的系統通知訊息</a>
        </div>
      </li>
    <!-- /系統通知下拉選單 -->
    <!-- 管理者功能選單 -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
            <img src="<?php echo DOC_PATH_LAYER.$_SESSION['SESS_AD_THUMB'].'?'.time(); ?>" width="30" height="30" class="img-circle elevation-1 adTopNaviPhoto" style="margin-top: -8px;">
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <div style="text-align:center;padding-top:8px;">管理執行者</div>
          <div class="image" style="margin-top:10px;text-align: center;">
            <img src="<?php echo DOC_PATH_LAYER.'5sladmin/'.$_SESSION['SESS_AD_THUMB'].'?'.time(); ?>" width="80" height="80" class="img-circle elevation-2 adTopNaviPhoto">
          </div>
          <span class="dropdown-header"><?php echo $_SESSION['SESS_AD_NICK']; ?></span>
          <div class="dropdown-divider"></div>
          <div class="dropdown-item" onclick="
          window.location.replace('ad_profile.php?currID=<?php echo $_SESSION['SESS_AD_ID']; ?>');
          " style="cursor:pointer;">
            <i class="fas fa-user mr-1 fa-pull-right"></i> 編輯個人資料
          </div>
          <div class="dropdown-divider"></div>
          <!-- 
          <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-1"></i> Logs
          </a>
          -->
          <p>&nbsp;</p>
          <div class="dropdown-divider"></div>
          <a href="../lockscreen.php" class="dropdown-item" onclick="
          //if(!confirm('鎖定系統將保留管理者ID以及鎖定前頁面 Session 資料，其餘資料一律刪除，鎖定狀態中可以再次輸入密碼進入系統，10分鐘內若沒有登入動作，系統將自動登出！\n\n確定鎖定系統！？')){
          //return false;
          //}
          ">
            <i class="fas fa-lock mr-1 fa-pull-right"></i> 鎖定(暫離)系統
          </a>
          <div class="dropdown-divider"></div>
          <a href="<?php echo DOC_PATH_LAYER; ?>5sladmin.php" class="dropdown-item" onclick="if(!confirm('確定登出系統？！')){return false;}">
            <i class="fas fa-power-off mr-1 fa-pull-right"></i> 登出
          </a>
        </div>
      </li>
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
  <!-- -------------------- 右側功能連結區 -------------------- -->
</nav>
