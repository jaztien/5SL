<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_MESSBOARD');
define('PAGE_ITEM','ITEM_MESS');
define('PAGE_OPANEL',9);//1-新增；2-編輯；3-上傳；4-編輯/刪除；9-[空]。
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//
$adJS = _fn_getCachedJsonData("../jsonFiles/administrator.json");
//
$privTab = !empty($_REQUEST['privTab'])?intval($_REQUEST['privTab']):1;
$chatType = !empty($_REQUEST['chatType'])?intval($_REQUEST['chatType']):1;//## 訊息傳遞種類方式 ## 0-廣播(單向)；1-私聊(與管理者)；2-私聊(與會員)；3-會員發起群聊；4-管理者發起群聊
$joinID = !empty($_REQUEST['joinID'])?intval($_REQUEST['joinID']):0;
$inchargeID = !empty($_REQUEST['inchargeID'])?intval($_REQUEST['inchargeID']):0;
$postID = !empty($_REQUEST['postID'])?intval($_REQUEST['postID']):0;
$podCast = !empty($_REQUEST['podCast'])?intval($_REQUEST['podCast']):0;
if(!empty($podCast)){//當公共廣播模式開啟時，定義三大主值皆為0
  $chatType = 0;
  $postID = 0;
  $joinID = 0;
}
$sendActive = (empty($chatType) && !empty($podCast)) || (!empty($chatType) && !empty($joinID)) ? 1 : 0;//發送訊息狀態啟用/凍結
$onChatter = '';
//
$backUrl = !empty($chatType) ? '&chatType='.$chatType : '';
$backUrl .= !empty($postID) ? '&postID='.$postID : '';
$backUrl .= !empty($joinID) ? '&joinID='.$joinID : '';
$backUrl .= !empty($podCast) ? '&podCast='.$podCast : '';
$backUrl .= !empty($privTab) ? '&privTab='.$privTab : '';
$backUrl = substr($backUrl,1);
//
$robotStatus = DB->getConfigVal("mess_robot");//自系統設定資料表中取得機器人回覆狀態
$robotStatus = intval($robotStatus['config_value']);//取得機器人回覆狀態中的數值。0｜1
// 生成管理者列表（依本頁面權限）
$posterAvatar = array();
$posterAvatar[1][0] = '../uploadFiles/ad/adphoto_0_thumb.jpg';
$adTop = '';
$adList = '';
$adsql = "
SELECT ad_id, ad_title, ad_authlevel, ad_status, 
(SELECT CONVERT(INT, CONCAT(auth_browse, auth_addnew, auth_edit, auth_delete)) FROM fsl_admin_auth a WHERE ap_pagecode='PG_MESSBOARD' AND a.ad_id=d.ad_id) AS messauth, 
(SELECT COUNT(*) FROM data_chats e WHERE e.chat_incharge = d.ad_id AND e.chat_status <= 2) AS inchargeTotal 
FROM fsl_administrator d ORDER BY messauth DESC, ad_authlevel DESC";
$adres = DB->query($adsql, array());
for($i=0;$i<count($adres);$i++){
  $adminID = $adres[$i]['ad_id'];
  $authClass = intval($adres[$i]['messauth']) < 1100 ? ' woPermit' : ' wPermit';//佈置有無權限狀態
  $inchargeBadge = intval($adres[$i]['inchargeTotal'])>0 ? intval($adres[$i]['inchargeTotal']) : '';
  if(file_exists('../uploadFiles/ad/adphoto_'.$adminID.'_thumb.jpg')){ $posterAvatar[1][$adminID] = '../uploadFiles/ad/adphoto_'.$adminID.'_thumb.jpg'; }
  else { $posterAvatar[1][$adminID] = '../imageFiles/nopic_user_50x50.jpg'; }
  if($adminID==$_SESSION['SESS_AD_ID']){
    $adTop .= '
      <div class="dropdown-item top active online" data-adminid="'.$adminID.'">
        <div class="text-center">
          <img src="'.$posterAvatar[1][$adminID].'" alt="User Avatar" class="img-size-64 img-circle mr-3">
          <div>
            <span class="dropdown-item-marker badge bg-danger">'.$inchargeBadge.'</span>
            <h3 class="dropdown-item-title">
              <b>'.$adres[$i]['ad_title'].'</b>
              <div class="text-sm">'.@$adJS['authlevel'][$adres[$i]['ad_authlevel']].'</div>
            </h3>
          </div>
        </div>
        <div class="dropdown-divider"></div>
      </div>
    ';
  } else {// img-grayscale
    $adList .= '
      <div class="dropdown-item list'.$authClass.'" data-adminid="'.$adminID.'">
        <div class="text-center">
          <img src="'.$posterAvatar[1][$adminID].'" alt="User Avatar" class="img-size-50 img-circle mr-3">
          <div>
            <span class="dropdown-item-marker badge bg-warning">'.$inchargeBadge.'</span>
            <h3 class="dropdown-item-title">
              <b>'.$adres[$i]['ad_title'].'</b>
              <div class="text-sm">'.@$adJS['authlevel'][$adres[$i]['ad_authlevel']].'</div>
            </h3>
          </div>
        </div>
        <div class="dropdown-divider"></div>
      </div>
    ';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <META HTTP-EQUIV="EXPIRES" CONTENT="0">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
	
	<title> Fifth Season Life 人生第五季 | 即時應答服務 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css<?php echo '?t='.time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.chat.css<?php echo '?t='.time(); ?>">
<!-- -------------------------- /CSS Link -------------------------- -->
<style>

</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <!-- 全頁框 -->
  <div class="wrapper">
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="../imageFiles/preloaderLOGO.png" alt="Fifth Season Life LOGO" height="60" width="60"> 
    </div>
    <!-- /Preloader -->
    <!-- Waitloader -->
    <div class="waitloader flex-column justify-content-center align-items-center">
      <img class="animation__heartbeat" src="../imageFiles/preloaderLOGO.png" alt="Fifth Season Life LOGO" height="60" width="60">
      <div>資 料 處 理 中 ...</div>
    </div>
    <!-- /Waitloader -->
    <!-- 頁內全頁內容 -->
    <div class="content-wrapper">
      <!-- 頁頭導覽條 -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">即時應答服務</h4>
                <h6 class="m-0 font-weight-light">即時對話面板，提供自動通知、會員留言消息、討論及提問回覆等服務。</h6>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">訊息傳遞管理</li>
                <li class="breadcrumb-item active">即時應答服務</li>
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </section>
      <!-- //頁頭導覽條 -->
      <!-- 主要內容區域 ------------------------------------- -->
      <section class="content" style="height:calc(100vh - 86px);">
        <div class="container-fluid" style="height:100%;">
          <div class="row" style="height:100%;">
            <!-- 左側會員列表 -->
            <div class="col-md-3" style="height:100%;">
              <div id="messfrom" class="card card-secondary card-outline shadow" style="height:100%;min-height:560px;">
                <!-- Message Header -->
                <div class="card-header" style="height:48px;padding-right:8px;">
                  <div style="display: inline-block;">待應答會員列表</div>
                  <div class="card-froms" style="display: inline-block;float:right;">
                    <button type="button" class="btn btn-tool" style="">
                      <i class="fas fa-2x fa-solid fa-podcast<?php echo !empty($podCast) ? ' active' : ''; ?>" title="公共廣播模式" alt="公共廣播模式"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body box-profile accent-secondary" style="overflow:auto;">
                  <div id="usersOnline"></div>
                  <div id="usersOffline">
                  <!-- 在線會員列表 -->
                  <?php
                    //取得有未讀訊息的來源會員資料，含有上線記錄子查詢
                    //條件：近180天內談話室種類1(單聊管理者)，貼文者種類2(會員)的以貼文者ID為群組不重複過濾
                    $toTime = (new DateTime())->modify('-180 days')->format('Y-m-d H:i:s');//保留至N日前
                    /*
                    $sql = "
                    SELECT chat_postid, chat_published, chat_type, chat_joinid, chat_incharge, chat_poster, chat_message, chat_messtype, chat_read, chat_status, 
                    chat_postid AS postid, 
                    dateadd(S, chat_published, '1970-01-01 00:00:00') AS datetime, 
                    (SELECT COUNT(*) FROM data_chats e WHERE e.chat_type = 1 AND e.chat_postid = a.chat_postid AND e.chat_status = ?) AS unreadNum, 
                    (SELECT COUNT(*) FROM data_chats d WHERE d.chat_type = 1 AND d.chat_postid = a.chat_postid AND d.chat_incharge =". $_SESSION['SESS_AD_ID'] ." AND d.chat_status <= 2) AS inchargeNum
                    FROM data_chats a WHERE a.chat_type = ? AND a.chat_posttype = ? AND a.chat_published > ?
                    ORDER BY a.chat_published ASC, unreadNum DESC
                    ";
                    */
                    $sql = "
                    SELECT chat_postid, chat_published, chat_type, chat_joinid, chat_incharge, chat_poster, chat_message, chat_messtype, chat_read, chat_status, 
                    chat_postid AS postid, 
                    (SELECT COUNT(*) FROM data_chats e WHERE e.chat_type = 1 AND e.chat_postid = a.chat_postid AND e.chat_status = ?) AS unreadNum, 
                    (SELECT COUNT(*) FROM data_chats d WHERE d.chat_type = 1 AND d.chat_postid = a.chat_postid AND d.chat_incharge =". $_SESSION['SESS_AD_ID'] ." AND d.chat_status <= 2) AS inchargeNum
                    FROM data_chats a WHERE a.chat_type = ? AND a.chat_posttype = ? AND a.chat_published > ?
                    ORDER BY a.chat_published ASC, unreadNum DESC
                    ";
                    $fromres = DB->query($sql, array(2,1,2,$toTime), PDO::FETCH_GROUP | PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);
                    if($fromres){
                      foreach($fromres as $key => $val){
                        //預製頭像縮圖陣列
                        $thumbPath = '../uploadFiles/mb/mbphoto_'.$key.'_thumb.jpg';
                        if(file_exists($thumbPath)){
                          $posterAvatar[2][$key] = $thumbPath;
                        } else {
                          $posterAvatar[2][$key] = '../imageFiles/nopic_user_64x64.jpg';
                        }
                        $message = $val['chat_message'];
                        $message = mb_strlen($message) > 10 ? mb_substr($message, 0, 10).'...' : $message;
                        $unread = (!empty($joinID) && $joinID==$val['chat_joinid']) ? '' : ($val['unreadNum'] > 0 ? $val['unreadNum'] : '');
                        $inchargeNum = $val['inchargeNum']>0 ? $val['inchargeNum'] : '';
                        if(!empty($joinID) && $val['chat_joinid']==$joinID){
                          $onChatter = $val['chat_poster'];
                        }
                  ?>
                    <div class="dropdown-item<?php if(empty($podCast) && !empty($joinID) && $joinID==$val['chat_joinid']) echo ' active'; ?>" data-mbid="<?php echo $val['chat_joinid']; ?>" data-joinid="<?php echo $val['chat_joinid']; ?>" data-datetime="<?php echo $val['chat_published']; ?>">
                      <div class="media">
                        <span class="onlinebadge"></span>
                        <img src="<?php echo $posterAvatar[2][$key]; ?>" alt="User Avatar" class="img-size-40 img-circle mr-3">
                        <div class="media-body">
                          <span class="dropdown-item-unread badge float-right bg-success"><?php echo $unread; ?></span>
                          <h3 class="dropdown-item-title">
                            <b><?php echo $val['chat_poster']; ?></b>
                            <span class="dropdown-item-marker badge bg-danger"><?php echo $inchargeNum; ?></span>
                          </h3>
                          <div class="text-sm"><?php echo $message; ?></div>
                        </div>
                      </div>
                      <div class="dropdown-divider"></div>
                    </div>
                    <?php } } else { ?>
                    <div class="usersOffline-bottom"></div>
                  <?php } ?>
                  </div>
                  <!-- /.User Info -->
                </div>
              </div>
            </div>
            <!-- //左側會員列表 -->
            <!-- 中間對話框架 -->
            <div class="col-md-7" style="height:100%;">
              <div id="messBoard" class="card card-success card-outline direct-chat direct-chat-success shadow" style="height:100%;min-height:560px;">
                <!-- Message Header -->
                <div class="card-header" style="height:48px;">
                  <h5 class="card-title text-sm statusNav">
                    Join ID: <input type="text" id="joinID" name="joinID" style="width:72px;" value="<?php echo $joinID; ?>" readonly>  
                    Join With: <input type="text" id="onChatter" name="onChatter" style="width:92px;" value="<?php echo $onChatter; ?>" readonly>    
                    <!-- chatType: --><input type="hidden" id="chatType" name="chatType" value="<?php echo $chatType; ?>" style="width:24px;" readonly>  
                    <!-- inchargeID: --><input type="hidden" id="inchargeID" name="inchargeID" value="<?php echo $inchargeID; ?>" style="width:24px;" readonly>  
                  </h5>
                  <div class="card-tools">
                    <button type="button" class="btn btn-tool">
                      <i class="fas fa-robot<?php echo $robotStatus==1 ? ' active' : ($robotStatus==2 ? ' factive' : ''); ?>" style="font-size:24px;"<?php echo $robotStatus==1 ? ' title="智慧自動應答模式" alt="智慧自動應答模式"' : ($robotStatus==2 ? ' title="強制自動應答模式" alt="強制自動應答模式"' : ' title="自動應答模式關閉" alt="自動應答模式關閉"'); ?>></i>
                    </button>
                    <button type="button" class="btn btn-tool">
                      <i class="fas fa-solid fa-users-rectangle" style="font-size:1.5em;margin-left:-8px;" title="移動至最早具有指定管理標註的對話紀錄！" alt="移動至最早具有指定管理標註的對話紀錄！"></i>
                    </button>
                    <button type="button" class="btn btn-tool">
                      <i class="far fa-arrow-alt-circle-down" style="font-size:24px;margin-left:-8px;" title="滾動至最新訊息" alt="滾動至最新訊息"></i>
                    </button>
                    <button type="button" class="btn btn-tool" style="margin-left:-8px;" data-card-widget="maximize">
                      <i class="fas fa-expand" style="font-size:24px"></i>
                    </button>
                  </div>
                </div>
                <!-- /.Message Header -->
                <!-- Message Body -->
                <div class="card-body" style="overflow:auto;position:ralative;">
                  <div class="direct-chat-messages" style="height:100%;padding-left:1.5em;padding-right:1.5em;"></div>
                  <div class="end-convo"></div>
                  <!-- /.direct-chat-pane -->
                </div>
                <!-- /.Message Body -->
                <!-- Message Footer ( Message Send ) -->
                <div class="card-footer">
                  <!-- <form id="send_chat" method="post"> $('#messBoard .input-group .direct-chat-replyfrom['data-replyid'='']) -->
                    
                    <div class="input-group">
                      <div class="direct-chat-replyfrom" data-replyid="0">
                        <div></div>
                        <i class="fa-solid fa-xmark<?php echo !empty($sendActive) ? '' : ' hide'; ?>"></i>
                      </div>
                      <div class="input-group-prepend">
                        <button type="button" id="attachBut" name="attachBut" class="btn btn-secondary<?php echo !empty($sendActive) ? '' : ' disabled'; ?>"><i class="fas fa-solid fa-paperclip" style="font-size:1.25em;"></i></button>
                      </div>
                      <div id="attachWrap" name="attachWrap" class="input-group-prepend messAttach">
                        <button type="button" id="textBtn" name="textBtn" class="btn btn-info"><i class="fa-solid fa-comment-dots" style="font-size:1.25em;"></i></button>
                        <button type="button" id="uploadFileBtn" name="uploadFileBtn" class="btn btn-info"><i class="fa-solid fa-photo-film" style="font-size:1.25em;"></i></button>
                        <button type="button" id="emojiBtn" name="emojiBtn" class="btn btn-info"><i class="fa-solid fa-face-grin-wide" style="font-size:1.25em;"></i></i></button>
                      </div>
                      <input type="file" id="fileInput" name="fileInput" class="file-input" accept=".jpg,.jpeg,.png,.pdf,.txt,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.mov,.mp4,.zip">
                      <!-- Message Input Area -->
                      <div id="messageInput" name="messageInput" class="form-control" contenteditable="<?php echo !empty($sendActive) ? 'true' : 'false'; ?>" placeholder="Enter text here..."></div>
                      <!-- <textarea type="text" id="messageInput" name="messageInput" placeholder="Type Message ..." class="form-control" required=""<?php echo !empty($sendActive) ? '' : ' disabled'; ?>></textarea> -->
                      <span class="input-group-append" style="z-index:99;">
                        <button type="button" id="sendMessage" name="sendMessage" class="btn bg-info<?php echo !empty($sendActive) ? '' : ' disabled'; ?>"><i class="fas fa-solid fa-paper-plane" style="font-size:1.5em;"></i><br>Send</button>
                      </span>
                    </div>
                  <!-- </form> -->
                </div>
                <!-- /.Message Footer ( Message Send ) -->
              </div>
            </div>
            <!-- //中間對話框架 -->
            <!-- 右側管理者列表（已於頁面前端建立） -->
            <div class="col-md-2" style="height:100%;">
              <div id="messAdmin" class="card card-info card-outline shadow" style="height:100%;min-height:560px;">
                <div class="card-body box-profile accent-info" style="overflow:auto;">
                  <?php echo $adTop.$adList; ?>
                </div>
              </div>
            </div>
            <!-- //右側管理者列表 -->
          </div>
        </div>
      </section>
      <!-- //主要內容區域 ------------------------------------ -->
    </div>
    <!-- //頁內全頁內容 -->
    <!-- jQuery -->
    <script src="../pluginFiles/jquery/jquery.min.js"></script>
    <!-- 下方訊息區 -->
    <?php //include_once('include_footer.php'); ?>
    <!-- //下方訊息區 -->
    <!-- 右側功能控制區 -->
      <aside class="control-sidebar control-sidebar-dark">
        <!-- 功能區內容 ------------------------------------ -->

        <!-- /功能區內容 ----------------------------------- -->
      </aside>
    <!-- //右側功能控制區 -->
  </div>
  <!-- //全頁框 -->
<!-- ------------------------ Scripts Link ------------------------ -->
<!-- jQuery is in front of footer file. -->
<!-- Bootstrap 4 -->
<script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
<!-- ------------------------ /Scripts Link ------------------------ -->
<script type="text/javascript">
////////////////////////////////////////////////////////////////
// ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
// ---------------------------------------------------------- //
////////////////////////////////////////////////////////////////
  $(document).ready(function() {
    //* iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });
    //-----------------------------------------
    let avatarObj = $.parseJSON( '<?php echo func_stripewhite(json_encode($posterAvatar)); ?>' );//創建頭像縮圖課程商品
    /////////////////////////////////////////////////////////
    //------------ Web Socket Scripts Start Here-----------//
    /////////////////////////////////////////////////////////
    let chatID = 0;
    let chatType = parseInt('<?php echo $chatType; ?>');
    let inchargeID = 0;//本頁面僅能設定為0
    let joinID = parseInt($('#joinID').val());
    let onChatter = $('#onChatter').val();
    let postName = "<?php echo $_SESSION['SESS_AD_NICK']; ?>";
    let postID = parseInt('<?php echo $_SESSION['SESS_AD_ID']; ?>');
    let postType = 1; //1-Admin, 2-User //本頁面僅能設定為1
    //let messType = 0;
    let podCast = $('.card-froms .fa-podcas').hasClass('active') ? 1 : 0;
    let robotStatus = parseInt('<?php echo $robotStatus; ?>');
    let scrollNum = 0;
    /////////////////////////////////////////////////////////
    //----------- WebSocket 功能程式碼(模組可變動) -----------//
    /////////////////////////////////////////////////////////
    //let ws = new WebSocket("wss://www.5seasonlife.com:8089");
    let ws = new WebSocket("ws://localhost:8089");
    //------- 頁面開啟時啟動監聽 --------
    ws.onopen = function() {
      // 加入群組 //與 WebSocket 連通由外框的 5sladmin_iframe.php 建立
      ws.send(JSON.stringify({
        action: "onlineList",
      }));
    };
    //------- 接收並解析監聽訊息 --------
    ws.onmessage = function(event) {
      let cObj = JSON.parse(event.data);
      var triggerON = 0;
      //依條件值決定是否列入此應答列表// chat_type 0-廣播(單向)；1-私聊(與管理者)；2-私聊(與會員)；3-會員發起群聊；4-管理者發起群聊
      if(
        cObj.action === "onlineList" || 
        (cObj.chat_type==0 && cObj.chat_joinid==0) || 
        (cObj.chat_type>0 && cObj.chat_type!=2 && cObj.chat_joinid==joinID) || 
        (cObj.chat_type==2 && ((cObj.chat_postid==postID && cObj.chat_joinid==joinID) || (cObj.chat_joinid==postID && cObj.chat_postid==joinID)))//當為1對1會員間私聊時交叉比對 joinID 和 inchargeID，正反組任一成立即可
      ){
        triggerON = 1;
      }
      /////////////////////////////////////////////////////
      ///////////////// 每次監聽後必須的動作 //////////////////
      //當為訊息或廣播時
      if(triggerON === 0 && (cObj.action === "message" || cObj.action === "broadcast")){
        var mbListElm = $('#messfrom .dropdown-item[data-joinid="' + cObj.chat_joinid + '"] .media-body');
        //var unreadNow = 1;
        var messShort = cObj.chat_message.length > 10 ? cObj.chat_message.substring(0, 10) + '...' : cObj.chat_message;//將最新訊息縮短顯示
        if(mbListElm.length > 0){//如果有對應的列表項目,依需求修改項目內容(主要為未讀訊息更新)
          var unreadElm = mbListElm.find(' > .badge');
          var unreadNow = unreadElm.html() === '' ? 1 : parseInt(unreadElm.html())+1;//未讀數值+1
          unreadElm.html(unreadNow);//更新未讀數值
          //window.parent.unreadUpdate(-1);//更新外層未讀數值
          mbListElm.find('div').html(messShort);//更新最新訊息
        } else {//如果沒有對應的列表項目,則新增一個列表項目(按理應為第一筆新的未讀訊息的在線會員)
          var chatAvatar = window.parent.checkIfFileExist(`../uploadFiles/mb/mbphoto_${cObj.chat_postid}_thumb.jpg`);
          avatarObj[2][cObj.chat_postid] = chatAvatar;
          var fromHtml = `
              <div class="dropdown-item" data-mbid="${cObj.chat_postid}" data-joinid="${cObj.chat_joinid}" data-datetime="${cObj.chat_published}">
                <div class="media online">
                  <span class="onlinebadge"></span>
                  <img src="${chatAvatar}" alt="User Avatar" class="img-size-40 img-circle mr-3">
                  <div class="media-body">
                    <span class="dropdown-item-unread badge float-right bg-success">1</span>
                    <h3 class="dropdown-item-title">
                      <b>${cObj.chat_poster}</b>
                      <span class="dropdown-item-marker badge bg-danger"></span>
                    </h3>
                    <div class="text-sm">${messShort}</div>
                  </div>
                </div>
                <div class="dropdown-divider"></div>
              </div>
            `;
            $('#usersOnline').append(fromHtml);//同時更新外層未讀數值，由外框的 5sladmin_iframe.php 自行處理
        }
      }
    
      //當為會員登入/登出談話間,更改上線狀態
      else if(cObj.action === "onChat" || cObj.action === "outChat"){
        if(cObj.chat_posttype === 1){//當為管理者登入談話間時，取消透明灰階
          var cMBelm = $('#messAdmin .dropdown-item[data-adminid="' + cObj.chat_postid + '"]');
          if(cObj.action === "onChat"){
            cMBelm.addClass('online');
          } else {
            cMBelm.removeClass('online');
          }
        } else if(cObj.chat_posttype === 2){//當為會員登入談話間時，取消透明灰階並向上移動置頂
          var cMBelm = $('#messfrom .dropdown-item[data-mbid="' + cObj.chat_postid + '"]');//data-joinid 也可以用 data-mbid ，兩值是相同的
          if(cObj.action === "onChat"){
            cMBelm.find('.media').addClass('online');
            var movedItem = $(cMBelm).clone();
            $(cMBelm).remove();
            $('#usersOnline').append(movedItem);
            usersOnlineResort();
          }
          else {
            cMBelm.find('.media').removeClass('online');
            var movedItem = $(cMBelm).clone();
            $(cMBelm).remove();
            $('#usersOffline').append(movedItem);
          }
        }
      }
      //當為機器人狀態時，進行狀態切換（管理後台專用）
      else if(cObj.action === "robotStatus"){
        robotStatus = cObj.robotSta;
        if(robotStatus==1){
          $('.card-tools .fa-robot').attr('title','智慧自動應答模式').attr('alt','智慧自動應答模式').removeClass('factive').addClass('active');
        } else if(robotStatus==2){
          $('.card-tools .fa-robot').attr('title','強制自動應答模式').attr('alt','強制自動應答模式').removeClass('active').addClass('factive');
        } else {
          $('.card-tools .fa-robot').attr('title','自動應答模式關閉').attr('alt','自動應答模式關閉').removeClass('active').removeClass('factive');
        }
      }
      ////////// 每次監聽後是否互相關聯並寫入應答列表 ////////////
      //當不被允許寫入訊息框時，在此終止
      if(triggerON==0){ return false; }
      //當接收到 onlineList 動作訊號時
      if (cObj.action === "onlineList"){
        console.log(cObj);
        if($.isEmptyObject(cObj.adminList)===false){
          $.each(cObj.adminList, function (key, value) {
            var nlElm = $('#messAdmin .dropdown-item[data-adminid="' + value + '"]');
            if(nlElm.length>0){ nlElm.addClass('online'); }
          });
        }
        //
        if($.isEmptyObject(cObj.userList)===false){
          $.each(cObj.userList, function (key, value) {
            var nlElm = $('#messfrom .dropdown-item[data-mbid="' + value + '"]');
            if(nlElm.length>0){
              nlElm.find('.media').addClass('online');
              var movedItem = $(nlElm).clone();
              $(nlElm).remove();
              $('#usersOnline').append(movedItem);
            }
          });
          usersOnlineResort();
        }
      }
      //當接收到 message/broadcast 動作訊號時，此時雙方均在同一個聊天區
      else if (cObj.action === "message") {
        $.ajax({//更新且標示為已讀
					   'url': "../funcFiles/mb_chat_func.php",
					'method': "POST",
					  'data': {
                'chatID': cObj.chat_id,
                'formAcce':'func_chat_readupdate',
                'token': getTokenVal()
              },
					dataType: "JSON",
					success:function(data) {
            if(data.RTN==1){
              var chatContent = chatContentInit([cObj]);
              $('.direct-chat-messages').append(chatContent);
              scrollToBottom();
            }
          }
        });
      }
      // 機器人回覆以及廣播內容寫入
      else if (cObj.action === "robotmess" || cObj.action === "broadcast") {//當接收到 message/broadcast 動作訊號時
        var chatContent = chatContentInit([cObj]);//以留言訊息格式寫入聊天區
        $('.direct-chat-messages').append(chatContent);
        scrollToBottom();//滾動聊天區至最下方
      }
      //當接收到的是系統通知訊息時
      else if (cObj.action === "system") {
        var chatContent = appendSystemMessage([cObj]);
        $('.direct-chat-messages').append(chatContent);
        scrollToBottom();
      }
    };
    ws.onclose = function(event) {
      //由外框的 5sladmin_iframe.php 處理
    };
    ws.onerror = function () {
      //由外框的 5sladmin_iframe.php 處理
    };
    //離開頁面前的動作(關閉 WebSocket 服務)由外框的 5sladmin_iframe.php 處理
    //------------ // Web Socket Scripts End Here-------------//
  
    /////////////////////////////////////////////////////////
    //------ 以下為 Jquery Selector 功能程式碼(不可變動) ------//
    /////////////////////////////////////////////////////////
    //////////////// 按下傳送訊息按鍵時動作 ////////////////
    $('#sendMessage').click(function() {
      var messageText = $('#messageInput').html().trim();
      var replyID = $('.direct-chat-replyfrom').data('replyid');
      if (messageText !== '') {
        var messData = {
          chat_type: $('#chatType').val(),//聊天室種類：0-單向廣播；1-服務私聊(與管理者)；2-會員私聊（專家對話等）；3-會員發起群聊；4-管理者發起群聊
          chat_postid: postID,//發文者ID
          chat_joinid: $('#joinID').val(),//私聊室ID（chat_type為0時此值為0；1-會員ID；2-會員ID；3、4-群組資料表ID
          chat_poster: postName,//發文者暱稱
          chat_posttype: postType,//發文者種類 1-管理者；2-會員（包含專家等）
          chat_incharge: $('#inchargeID').val(),//談話間種類關聯ID chat_type為時此值為0；1-指定管理者ID；2-專家等ID（會員）；3-群組發起人ID(會員)；4-群組發起人ID(管理者)
          chat_message: messageText,//發文內容
          chat_filename: '',//檔案名稱
          chat_messtype: 0,//發文種類：0-文字；1-圖檔；2-文檔；3-影片
          chat_replyid: replyID,//本文的回覆目的對象ID
          chat_read: 0,//已讀數量 //送出訊息時一律標示為0
          chat_keep: 0,//是否保留檔案 0-否；1-是
          chat_status: 2 //應答紀錄狀態 1-正常已讀；2-正常未讀；3-收回；4-刪除；8-違規停權；9-封存 //送出訊息時一律標示為未讀
        };
        $.ajax({//資料表記錄
					url     : "../funcFiles/mb_chat_func.php",
					method  : "POST",
					data    :{
            'messData': messData,
            'messAction': 'message',
            'messType': 0,
            'formAcce':'func_chat_poston',
            'token': getTokenVal()
					},
					dataType:"JSON",
					success:function(data) {
            //僅在本機端（發文者）訊息框內顯示系統訊息
            //系統通知訊息時貼入訊息框
            if(data.action=='system'){
              var chatContent = appendSystemMessage([data]);
            }
            //非系統訊息則送出進行監聽
            else {
              ws.send(JSON.stringify(data));
              var chatContent = chatContentInit([data]);
            }
            $('.direct-chat-messages').append(chatContent);
            $('#messageInput').html(''); 
            var replyfrom = $('.direct-chat-replyfrom');
            replyfrom.data('replyid','0').removeClass('replyOpen');
            replyfrom.find('div').html('');
            scrollToBottom();
          }
        });
      }
    });
    //////////////////// 附加檔案按鈕 ///////////////////////////////////////////
    $('#attachBut').click(function() {
      if($('#sendMessage').hasClass('disabled')){ return false; }
      if($('#attachWrap').hasClass('attachOpen')){
        $('.messAttach').removeClass('attachOpen');
      } else {
        $('.messAttach').addClass('attachOpen');
      }
    });
    // 聯動檔案選擇檔案選擇&上傳視窗
    $('#uploadFileBtn').click(function() {
      $('#fileInput').click();
    });
    ///////////////// 檔案選擇&上傳 /////////////////////////////////////////////
    $('#fileInput').on('change', function() {
      //var thisID = $(this).attr('id');
      $('.messAttach').removeClass('attachOpen');
      var file = this.files[0];
      var typePass = ['jpg', 'jpeg', 'png', 'pdf', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'mov', 'mp4', 'wav', 'aiff', 'mp3', 'aac', 'm4a'];
      var fileSplit = file.name.split('.'),
          fileExt = fileSplit.pop().toLowerCase(),
          fileName = fileSplit.shift(),
          extIDX = typePass.indexOf(fileExt),
          fileSize = file.size;
      if(extIDX < 0){
        alert('這個檔案格式不被系統接受，請轉檔後重新操作！！');
        return false;
      }
      if(fileSize > 600000){
        alert('檔案大小超過最高限制 600K，請減少檔案大小後後重新操作！！');
        return false;
      }
      var replyID = $('.direct-chat-replyfrom').data('replyid');
      var reader = new FileReader();
      var messType = 0;
      var messContent = '';
      reader.onload = function(e) {
        if(extIDX>=0 && extIDX<=2){
          messType = 1;
        } else if(extIDX>=3 && extIDX<=11){
          messType = 2;
        } else if(extIDX>=12 && extIDX<=13){
          messType = 3;
        } else if(extIDX>=14 && extIDX<=18){
          messType = 4;
        }
        var messData = {
          chat_type: $('#chatType').val(),//聊天室種類：0-單向廣播；1-服務私聊(與管理者)；2-會員私聊（專家對話等）；3-會員發起群聊；4-管理者發起群聊
          chat_postid: postID,//發文者ID
          chat_joinid: $('#joinID').val(),//私聊室ID（chat_type為0時此值為0；1-會員ID；2-會員ID；3、4-群組資料表ID
          chat_poster: postName,//發文者暱稱
          chat_posttype: postType,//發文者種類 1-管理者；2-會員（包含專家等）
          chat_incharge: $('#inchargeID').val(),//談話間種類關聯ID chat_type為時此值為0；1-指定管理者ID；2-專家等ID（會員）；3-群組發起人ID(會員)；4-群組發起人ID(管理者)
          chat_message: '',//發文內容
          chat_filename: file.name,//檔案名稱
          chat_messtype: messType,//發文種類：0-文字；1-圖檔；2-文檔；3-影片
          chat_replyid: replyID,//本文的回覆目的對象ID
          chat_read: 0,//已讀數量 //送出訊息時一律標示為0
          chat_keep: 0,//是否保留檔案 0-否；1-是
          chat_status: 2 //應答紀錄狀態 1-正常已讀；2-正常未讀；3-收回；4-刪除；8-違規停權；9-封存 //送出訊息時一律標示為未讀
        };
        $.ajax({//寫入資料表記錄
					url     : "../funcFiles/mb_chat_func.php",
					method  : "POST",
					data    : {
            'messData': messData,
            'messAction': 'message',
            'messType': messType,
            'fileData': e.target.result,
            'fileName': fileName,
            'fileExt': fileExt,
            'formAcce':'func_chat_poston',
            'token': getTokenVal()
					},
					dataType:"JSON",
					success:function(data) {
            if(data.action=='error'){
              return false;
            } else if(data.action=='system'){//僅在本機端（發文者）訊息框內顯示系統訊息
              var chatContent = appendSystemMessage([data]);
            } else {//非系統訊息則送出進行監聽
              ws.send(JSON.stringify(data));
              var chatContent = chatContentInit([data]);
            }
            $('.direct-chat-messages').append(chatContent);
            $('#messageInput').html(''); 
            scrollToBottom();
          }
        });
      };
      reader.readAsDataURL(file);
    });
    ///////////////// Enter 按鈕觸發動作 ////////////////////////////////////////
    $('#messageInput').keypress(function(e) {
      if (e.which == 13) {
        $('#sendMessage').click();
      }
    });
    //////////////////// 管理者列表（本人除外）點擊操作動作 /////////////////////////
    $('#messAdmin').on('click', '.dropdown-item.list', function() {//:not(.active)
      if($('.card-froms .fa-podcast').hasClass("active")){ 
        alert('公共廣播模式下無法指定管理人員！');
        return false;
      }
      if($(this).hasClass("active")){
        $(this).removeClass('active');
        $('#inchargeID').val('0');
      } else {
        //img-grayscale
        if($(this).hasClass('woPermit')){
          alert('您所選擇的管理者沒有即時應答的權限，請自行應答或選擇其他的管理者！！');
          return false;
        }
        $('#messAdmin .dropdown-item.list.active').removeClass('active');
        $(this).addClass('active');
        //$('#inchargeID').val($(this).data('adminid'));
      }
    });
    ////////////////// 會員列表點擊操作動作（取得應答紀錄） /////////////////////////
    $('#messfrom').on('click', '.dropdown-item', function() {
      if($(this).hasClass("active")){//當原本已被選時取消選擇
        $(this).removeClass('active');
        $('#joinID').val('0');
        $('#onChatter').val('');
        $('.direct-chat-messages').empty();
        $('#messBoard .card-footer .input-group').find('button').addClass('disabled');
        $('#messageInput').attr('contenteditable', 'false');
        $('.fa-xmark').addClass('hide');
      } else {//當未被選時的動作操作
        $('.waitloader').css({'visibility':'visible'});
        joinID = parseInt($(this).data('joinid'));
        onChatter = $(this).find('.dropdown-item-title b').html();
        $('#messfrom .dropdown-item.active').removeClass('active');
        $(this).addClass('active');
        $('#joinID').val(joinID);
        $('#onChatter').val(onChatter);
        $('#inchargeID').val('0');
        $('#chatType').val('1');
        //------ 取得所選會員的聊天記錄 ------
				$.ajax({
					url     : "../funcFiles/mb_chat_func.php",
					method  : "POST",
					data    : {
            'chatType': chatType,
            'inchargeID': inchargeID,
            'joinID': joinID,
            'formAcce':'func_chat_getdata',
            'token': getTokenVal()
					},
					dataType:"JSON",
					success:function(data) {
            if (data.list.length > 0) {//當搜尋出任何聊天記錄時
              var chatContent = chatContentInit(data.list);
              //啟用訊息欄/按鈕
              $('.direct-chat-messages').html(chatContent);
              $('#messBoard .card-footer .input-group').find('button').removeClass('disabled');
              $('#messageInput').attr('contenteditable', 'true');
              $('.fa-xmark').removeClass('hide');
              //關閉公共廣播
              $('.card-froms .fa-podcast').removeClass('active');
              //指定管理定位歸零
              scroolTO = 0;
              //更新未讀數量標示
              if(data.upNum > 0){//當取得資料同時更改已讀訊息時的數量大於0時
                $('#messfrom .dropdown-item[data-joinid="' + joinID + '"] .dropdown-item-unread').html('');
                window.parent.$('#messDropdown a.ezmess[data-joinid="' + joinID + '"]').remove();
                window.parent.unreadUpdate(data.upNum);
              }
            } 
            /*
            else {
              $('.direct-chat-messages').html('<div class="system-message">近 30 日無聊天記錄！</div>');
            }
            */
            scrollToBottom();
          }
				});
      }
    });
    ///////////////// 訊息內容框附加操作按鈕動作 //////////////////////////////////
    $('.direct-chat-messages').on('click', '.direct-chat-msg .optlink span', function(event) {
      var ths = $(this);
      var target = ths.parent().parent().parent();
      var spanClass = ths.attr('class');
      var chatID = target.data('chatid');
      var TpostID = target.data('postid');
      var TinchargeID = parseInt(target.data('incharge'))
      var TmessType = target.data('messtype');
      var downFile = target.data('downfile');
      var origFilename = target.find('.direct-chat-text .origFilename').html();
      switch(spanClass){
        case 'optresponse':
          var thisMarker = target.find('.direct-chat-marker').html();
          if(TinchargeID>0 && TinchargeID != postID){
            if(!confirm('這則訊息已經被指定管理給管理者 ' + thisMarker + ' ，按下「確定」取消指定管理標註逕行回覆，按下「取消」鍵則返回交由原指定管理者 ' + thisMarker + ' 回覆！！')){
              return false;
            }
          }
          var origTitle = TmessType == 0 ? target.find('.direct-chat-text').html().substring(0, 10) + '...' : origFilename;
          var replyAvatar = target.find('img').attr('src');
          var replyImage = TmessType==1 ?  target.find('.direct-chat-text').find('img').attr('src') : '';
          var replyfrom = $('.direct-chat-replyfrom');
          var replyHtml = `
            <img class="direct-chat-img replyAvatar" src="${replyAvatar}" align="bottom">
            <div style="display:inline;">${origTitle}</div>`;
          if(replyImage !== '') { replyHtml += `<div class="replyImage"><img src="${replyImage}"></div>`; }
          replyfrom.find('div').html(replyHtml);
          replyfrom.data('replyid', chatID).addClass('replyOpen');
        break;
        /*
        case 'optdown':
          if(!confirm('確定下載這個檔案？！')){ return false; }
          //var fileDownPath = '../uploadFiles/chat/'+ downFile;
          var inForm = `
            <input type="hidden" id="downFile" name="downFile" value="${downFile}">
            <input type="hidden" id="origFilename" name="origFilename" value="${origFilename}">
            <input type="hidden" id="token" name="token" value="${getTokenVal()}">
            <input type="hidden" id="backURL" name="backURL" value="mess_board.php?chatType=${chatType}&inchargeID=${inchargeID}&joinID=${joinID}">
          `;
          $('<form></form>').attr("method","post").attr('action', 'sys_download.php').html(inForm).appendTo('body').submit().remove();
          break;
          */
        case 'optkeep':
          var nextKeep = ths.find('i').length>0 ? 0 : 1;
          if(nextKeep==1){
            if(!confirm('若是保留紀錄，系統將不會對這則紀錄定期執行刪除，確定保留這則紀錄？！')){ return false; }
          } else {
            if(!confirm('若是移除保留，系統將針對過期紀錄定期執行刪除，確定移除保留這則紀錄？！')){ return false; }
          }
          var nextKeep = ths.find('i').length>0 ? 0 : 1;
          $.ajax({
            url     : "../funcFiles/mb_chat_func.php",
            method  : "POST",
            data    : {
              'chatID': chatID,
              'act': 'keep',
              'nextStep': nextKeep,
              'formAcce':'func_chat_optlink',
              'token': getTokenVal()
            },
            dataType:"JSON",
            success:function(data) {
              if(data.RTN==0){ return false; }
              if(nextKeep==1){
                ths.html(' <i class="fa-solid fa-unlock-keyhole"></i> ');
              } else {
                ths.html('保留');
              }
            }
          });
        break;
        //////////////////////////////////////// optincharge 指定管理者 ////////////////////////////////////////////
        case 'optincharge':
          let nextMarkerElm = $('#messAdmin .dropdown-item.list.active'),
              adMarkElm = $('#messAdmin .dropdown-item[data-adminid="' + TinchargeID + '"]'),//指定右側管理者列外框
              nowMarker = '',
              nextMarkerID = 0,
              nextMarker = '';
          var nowUserElm = $('#messfrom .dropdown-item.active');
          if(TinchargeID > 0){
            nowMarker = adMarkElm.find('.dropdown-item-title b').html();
            nowMarkerBadge = parseInt(adMarkElm.find('.dropdown-item-marker').html());
            if(!confirm('確定將這則紀錄的指定管理者 '+ nowMarker +' 移除？！')){ return false; }
          } else {
            if(nextMarkerElm.length==0){
              alert('請先選擇一位管理者！');
              return false;
            }
            nextMarker = nextMarkerElm.find('.dropdown-item-title b').html();
            nextMarkerBadge = parseInt(nextMarkerElm.find('.dropdown-item-marker').html());
            if(!confirm('確定將這則紀錄標記給指定管理者 ' + nextMarker + ' ？！')){ return false; }
            nextMarkerID = parseInt(nextMarkerElm.data('adminid'));//取自右側管理者列表被選的管理者
          }
          $.ajax({
            url     : "../funcFiles/mb_chat_func.php",
            method  : "POST",
            data    : {
              'chatID': chatID,
              'act': 'marker',
              'nextStep': nextMarkerID,
              'formAcce': 'func_chat_optlink',
              'token': getTokenVal()
            },
            dataType: "JSON",
            success: function(data) {/////////////// FIXXXXXXXXXX!!!   
              if(data.RTN==0){ return false; }
              //當已經指定了管理者時
              if(TinchargeID == 0){
                target.data('incharge', nextMarkerID);
                var markerBagdeClass = nextMarkerID > 0 ? (nextMarkerID==TpostID ? ' bg-danger' : ' bg-warning') : '';
                ths.html('取消指定');
                target.find('.direct-chat-marker').html(nextMarker);
                if(markerBagdeClass!=''){ target.find('.direct-chat-marker').addClass(markerBagdeClass); }
                nextMarkerElm.find('.dropdown-item-marker').html(nextMarkerBadge+1);
              }
              //當已經移除了指定管理者時
              else {
                target.data('incharge', '0');
                ths.html('指定管理');
                target.find('.direct-chat-marker').html('');
                var countMarkerBadge = nowMarkerBadge-1;
                if(countMarkerBadge == 0){ countMarkerBadge = ''; }
                var nowUserBadge = parseInt(nowUserElm.find('.dropdown-item-marker').html()) - 1;
                if(nowUserBadge == 0){ nowUserBadge = ''; }
                adMarkElm.find('.dropdown-item-marker').html(countMarkerBadge);
                nowUserElm.find('.dropdown-item-marker').html(nowUserBadge);
              }
              nextMarkerElm.removeClass('active');
            }
          });
        break;
        //////////////////////////////////////// optback 收回 //////////////////////////////////////////////
        case 'optback':
          var nextBack = target.find('.direct-chat-text.backBG').length>0 ? 1 : 3;
          if(nextBack==3){
            if(!confirm('當紀錄回收後，受話方將僅顯示已收回註記，直到發話方取消收回，確定回收這則紀錄？！')){ return false; }
          } else {
            if(!confirm('取消收回後受話端將重新顯示紀錄內容，確定取消收回這則紀錄？！')){ return false; }
          }
          //var nextBack = target.find('.direct-chat-text.backBG').length>0 ? 1 : 3;
          $.ajax({
                'url': "../funcFiles/mb_chat_func.php",
             'method': "POST",
               'data': {
                'chatID': chatID,
                   'act': 'back',
              'nextStep': nextBack,
              'formAcce':'func_chat_optlink',
                 'token': getTokenVal()
              },
            dataType: "JSON",
            success:function(data) {
              if(data.RTN==0){ return false; }
              if(nextBack==3){
                var bkMess = target.find('.direct-chat-text').html();
                target.find('.direct-chat-text').addClass('backBG').html(`<span class="text-muted">[ 此訊息已被收回 ]</span><div class="text-shrinkOri">${bkMess}</div>`);
                ths.html('取消收回');
              } else {
                var bkMess = target.find('.direct-chat-text').find('.text-shrinkOri').html();
                target.find('.direct-chat-text').removeClass('backBG').html(bkMess);
                ths.html('收回');
              }
              ws.send(JSON.stringify({
                 action: "updateOption",
                 chatID: chatID,
                oriMess: nextBack==1 ? bkMess : '',
                messSta: nextBack
              }));
            }
          });
        break;
        //////////////////////////////////////// opthidemess 隱藏 //////////////////////////////////////////////
        case 'opthidemess':
          var nextHidemess = target.find('.direct-chat-text.hidemessBG').length>0 ? 1 : 4;
          if(nextHidemess==4){
            if(!confirm('在可能傷害到會員隱私權，或是雙方商業利益的洩露情事下，管理者有權隱藏會員發佈的紀錄，一旦執行隱藏後，會員端將不會顯示這則紀錄的內容，確定隱藏這則紀錄？！')){ return false; }
          } else {
            if(!confirm('取消隱藏後受話端將重新顯示紀錄內容，確定取消隱藏這則紀錄？！')){ return false; }
          }
          $.ajax({
                'url': "../funcFiles/mb_chat_func.php",
             'method': "POST",
               'data': {
                    'chatID': chatID,
                    'act': 'hidemess',
                    'nextStep': nextHidemess,
                    'formAcce':'func_chat_optlink',
                    'token': getTokenVal()
               },
            dataType: "JSON",
            success:function(data) {
              if(data.RTN==0){ return false; }
              if(nextHidemess==4){
                var bkMess = target.find('.direct-chat-text').html();
                target.find('.direct-chat-text').addClass('hidemessBG').html(`<span class="text-muted">[ 此訊息已被隱藏 ]</span><div class="text-shrinkOri">${bkMess}</div>`);
                ths.html('取消隱藏');
              } else {
                var bkMess = target.find('.direct-chat-text').find('.text-shrinkOri').html();
                target.find('.direct-chat-text').removeClass('hidemessBG').html(bkMess);
                ths.html('隱藏');
              }
              ws.send(JSON.stringify({
                 action: "updateOption",
                 chatID: chatID,
                oriMess: nextHidemess==1 ? bkMess : '',
                messSta: nextHidemess
              }));
            }
          });
        break;
        //////////////////////////////////////// optvio 違規記點 //////////////////////////////////////////////
        case 'optvio':
          if(!confirm('會員發佈的內容若是違反人生第五季訊息發佈規則，管理者有權踢除這則紀錄並且列入違規記點，系統亦無法恢復原始內容，確定進行訊息違規處理？！')){ return false; }
          var nextVio = 6;
          $.ajax({
                'url':"../funcFiles/mb_chat_func.php",
             'method':"POST",
               'data': {
                  'chatID': chatID,
                  'act': 'vio',
                  'nextStep': nextVio,
                  'formAcce':'func_chat_optlink',
                  'token': getTokenVal()
                },
            dataType: "JSON",
            success:function(data) {
              if(data.RTN==0){ return false; }//.remove()
              var bkMess = target.find('.direct-chat-text').html();
              target.find('.direct-chat-text').addClass('vioBG').html(`<span class="text-muted">[ 此訊息已違規並被剔除 ]</span><div class="text-shrinkOri">${bkMess}</div>`);
              var removeStart = ths.prev().prev();
              removeStart.nextAll().add(removeStart).remove();
              ws.send(JSON.stringify({
                action: "updateOption",
                chatID: chatID,
                oriMess: '',
                messSta: nextVio
              }));
            }
          });
        break;
        //////////////////////////////////////// optdelete 刪除 //////////////////////////////////////////////
        case 'optdelete':
          if(!confirm('管理者有權刪除自己發佈的紀錄，一旦執行刪除後，受話方將完全移除這則紀錄，系統亦無法恢復原始內容，確定刪除這則紀錄？！')){ return false; }
          var nextDelete = 5;
          $.ajax({
                'url': "../funcFiles/mb_chat_func.php",
             'method': "POST",
               'data': {
                  'chatID': chatID,
                  'act': 'delete',
                  'nextStep': nextDelete,
                  'formAcce':'func_chat_optlink',
                  'token': getTokenVal()
                },
            dataType: "JSON",
            success:function(data) {
              if(data.RTN==0){ return false; }//.remove()
              var bkMess = target.find('.direct-chat-text').html();
              target.find('.direct-chat-text').addClass('delBG').html(`<span class="text-muted">[ 此訊息已被刪除 ]</span><div class="text-shrinkOri">${bkMess}</div>`);
              var removeStart = ths.prev().prev();
              removeStart.nextAll().add(removeStart).remove();
              ws.send(JSON.stringify({
                 action: "updateOption",
                 chatID: chatID,
                oriMess: '',
                messSta: nextDelete
              }));
            }
          });
        break;
        default:
        break;
      }
    });
    //-------------- 主回覆文框滾動至原文 ---------------
    $('#messBoard .input-group .direct-chat-replyfrom > div').click(function(){
      var replyID = $(this).parent().data('replyid');
      scrollToPosition('chatid', replyID);
    });
    //-------------- 關閉回覆文框 ----------------------
    $('.fa-xmark').click(function(e){
      //e.preventDefault();
      var replyfrom = $('.direct-chat-replyfrom');
      replyfrom.data('replyid','0').removeClass('replyOpen');
      replyfrom.find('div').html('');
    });
    //-------------- 已回覆訊息滾動至原文 ---------------
    $('.direct-chat-messages').on('click', '.direct-chat-msg .direct-chat-text .replyMess', function(){
      var thisReplyID = $(this).parent().parent().data('replyid');
      scrollToPosition('chatid', thisReplyID);
    });
    //----------- 指定管理者定位按鈕操作動作 -------------
    $('.card-tools .fa-users-rectangle').click(function(){
      var nowUserElm = $('#messfrom .dropdown-item.active');
      if(nowUserElm.length==0){
        alert('請先選擇左側列表中準備對話的會員！');
        return false;
      }
      //
      if(nowUserElm.find('.dropdown-item-marker').html()==''){
        alert('準備對話的會員沒有任何關聯的指定管理標註！');
        return false;
      }
      scrollToPosition('incharge', postID);
    });
    //---------------- 滾動至最新訊息 ------------------
    $('.card-tools .fa-arrow-alt-circle-down').click(function(){
      scrollToBottom();
    });
    //------------- 機器人應答按鈕操作動作 --------------
    $('.card-tools .fa-robot').click(function(){
      var ths = $(this);
      var nowon = ths.hasClass("active")? 1 : (ths.hasClass("factive") ? 2 : 0);
      var nexton = nowon==0 ? 1 : (nowon==1 ? 2 : 0);
      if(nexton == 0 && !confirm('確定關閉自動應答模式？！')){ return false; }
      //else if(nexton==1 && !confirm('確定開啟自動應答的智慧應答模式？！')){ return false; }
      //else if(nexton==2 && !confirm('確定開啟自動應答的強制應答模式？！')){ return false; }
      $.ajax({
            'url': "../funcFiles/mb_chat_func.php",
         'method': "POST",
           'data': {
              'nextOn': nexton,
              'formAcce': 'func_chat_robotconfig',
              'token': getTokenVal()
            },
        dataType: "JSON",
        success:function(data) {
          if(data.nextOn==1){
            ths.removeClass('factive').attr('title','智慧自動應答模式').attr('alt','智慧自動應答模式').addClass('active');
          } else if(data.nextOn==2){
            ths.removeClass('active').attr('title','強制自動應答模式').attr('alt','強制自動應答模式').addClass('factive');
          } else {
            ths.removeClass('active').attr('title','自動應答模式關閉').attr('alt','自動應答模式關閉').removeClass('factive');
          }
          ws.send(JSON.stringify({
              action: "robotStatus",
            robotSta: data.nextOn
          }));
        }
      });
    });
    //------------ 公共廣播按鈕操作動作 -----------------
    $('.card-froms .fa-podcast').click(function(){
      if($(this).hasClass("active")){//當處於共廣播模式時
        $(this).removeClass('active');
        $('#joinID').val('0');
        $('#onChatter').val('');
        $('#inchargeID').val('0');
        $('#chatType').val(chatType);
        $('#messRead').val('0');
        $('.direct-chat-messages').empty();
        $('#messBoard .card-footer .input-group').find('button').addClass('disabled');
        $('#messageInput').attr('contenteditable', 'false');//解除訊息框可編輯模式
        $('.fa-xmark').addClass('hide');
      } else {//當未處於共廣播模式時
        if(!confirm('開啟公共廣播模式後，所有貼文的發佈，將會公開顯示於每個會員的訊息傳遞媒介中，確定開啟公共廣播模式？！')){
          return false;
        }
        $(this).addClass('active');//啟動公共廣播模式
        $('#messAdmin .dropdown-item.list.active').removeClass('active');//移除先前已選擇的管理者
        $('#messfrom .dropdown-item.active').removeClass('active');//同時移除先前已選擇的會員
        //同時更新嵌入的數值
        $('#joinID').val('0');
        $('#onChatter').val('所有會員');
        $('#inchargeID').val('0');
        $('#chatType').val('0');
        $('#messRead').val('0');
        //取得公共廣播的資料記錄（單純公共廣播不包含其他一般對話）
        $.ajax({
              'url': "../funcFiles/mb_chat_func.php",
           'method': "POST",
             'data': {
                'chatType': 0,
                'inchargeID': 0,
                'joinID': 0,
                'formAcce':'func_chat_getdata',
                'token': getTokenVal()
              },
          dataType: "JSON",
          success:function(data) {
            if (data.list.length > 0) { 
              var chatContent = chatContentInit(data.list);
              $('.direct-chat-messages').html(chatContent);
              $('#messBoard .card-footer .input-group').find('button').removeClass('disabled');
              $('#messageInput').attr('contenteditable', 'true');//允許訊息框可編輯模式
              $('.fa-xmark').removeClass('hide');
              scrollToBottom();
            } else {
              var chatContent = appendSystemMessage([{
                message: '近 30 日無聊天記錄'
              }]);
              $('.direct-chat-messages').append(chatContent);
              scrollToBottom();
            }
          }
        });
      }
    });

    /* ----------------------- 聊天區域內容初始化 ---------------------- */
    function chatContentInit(chatObj){
      if($.isArray(chatObj) && chatObj.length > 0) {
        var messageHtml = '';
        var optSpliter = '<span class="optSpliter">｜</span>';
        for (var i=0; i<chatObj.length; i++){
          var posterTitle=chatObj[i]['chat_poster'],classPart='',floatName=' float-left',floatTime=' float-right',
              respoonsText='回覆',keepText='保留',backText='收回',hidemessText='隱藏',vioText='違規',delText='刪除',
              markerText='指定管理',markerTitle='',textBG = '',castIcon = '';
          var avatarPath = avatarObj[chatObj[i]['chat_posttype']][chatObj[i]['chat_postid']];
          var dateTime = chatObj[i]['datetime'].substring(0,19);
          var message = chatObj[i]['chat_message'];
          var replyID = parseInt(chatObj[i]['chat_replyid']);
          var downfile = '';
          var origFilename = chatObj[i]['chat_filename'];
          //文字訊息框位置佈局（本頁為管理者在右，會員在左）
          if(chatObj[i]['chat_posttype'] == 1){
            posterFrom = chatObj[i]['chat_postid']==0 ? '[-自動應答服務-]' : '[-服務管理人-]';
            classPart = ' right float-right';
            floatName = ' float-right';
            floatTime = ' float-left';
            posterTitle = '<span class="text-shrink">' + posterFrom + '</span>&nbsp;&nbsp;' + posterTitle;
          }
          //訊息傳遞種類佈局，0為文字，大於0為檔案，1為圖檔直接顯示，其他為文件類可提供下載
          if(chatObj[i]['chat_messtype'] > 0){
            downfile = chatObj[i]['chat_message'].replace('thumb', '');
            if(chatObj[i]['chat_messtype']==1){
              message = '<img src="../uploadFiles/chat/' + chatObj[i]['chat_message'] + '">';
              if(chatObj[i]['chat_filename'] !== ''){
                message += '<div class="origFilename">' + origFilename + '</div>';
              }
            } else {
              message = '<div class="origFilename">' + origFilename + '</div>';
            }
          }
          //當具有回覆對象ID，並且有回覆內容時，視為本訊息為回覆訊息
          if(replyID>0 && chatObj[i]['replymess'] !== null){
            var replySplit = chatObj[i]['replymess'].split("|");//回覆訊息為陣列字串，分割回覆訊息為陣列
            var replyMessType = parseInt(replySplit[0]);//第一值為訊息種類
            var replyMess = replyMessType==1 ? replySplit[2] : replySplit[1];//第二值為主要訊息；第三值為檔案名稱
            var preMess = '<div class="replyMess"><i class="fas fa-level-up-alt"></i> '+ replyMess +'</div>';
            if(replyMessType==1) {//當回覆訊息為文字時
              var replyImage = `../uploadFiles/chat/${replySplit[1]}`;
              if(replyImage != '') { preMess = `<div class="replyMess"><i class="fas fa-level-up-alt"></i>${replyMess}<div class="replyFromImage"><img src="${replyImage}"></div></div>`; }
            }
            message = preMess + message;
          }
          //訊息框下方附加功能顯示內容佈局
          //## 留言狀態 ## 1-正常已讀；2-正常未讀；3-收回(管理端可復原)；4-隱藏(會員端可復原)；5-刪除(管理端無法復原)；6-違規停權(踢除+違規記點)；9-封存
          if(chatObj[i]['chat_status']==3){
            textBG = ' backBG';
            backText = '取消收回'; 
            message = `<span class="text-muted">[ 此訊息已被收回 ]</span><div class="text-shrinkOri">${chatObj[i]['chat_message']}</div>`;
          } else if(chatObj[i]['chat_status']==4){
            textBG = ' hidemessBG';
            hidemessText = '取消隱藏';
            message = `<span class="text-muted">[ 此訊息已被隱藏 ]</span><div class="text-shrinkOri">${chatObj[i]['chat_message']}</div>`;
          } else if(chatObj[i]['chat_status']==5){
            textBG = ' delBG';
            delText = '取消刪除';
            message = `<span class="text-muted">[ 此訊息已被刪除 ]</span><div class="text-shrinkOri">${chatObj[i]['chat_message']}</div>`;
          } else if(chatObj[i]['chat_status']==6){
            textBG = ' vioBG';
            message = `<span class="text-muted">[ 此訊息已違規並被剔除 ]</span><div class="text-shrinkOri">${chatObj[i]['chat_message']}</div>`;
          }
          //附加功能保留按鈕顯示內容（此值決定改紀錄是否將被定期刪除，需備查的重要內容當可保留）
          if(chatObj[i]['chat_keep']==true){ keepText = ' <i class="fa-solid fa-unlock-keyhole"></i> '; }
          //指定管理者顯示內容（提醒指定管理者處理的標註）
          var markerBagdeClass = chatObj[i]['chat_incharge'] > 0 ? (chatObj[i]['chat_incharge']==postID ? ' bg-danger' : ' bg-warning') : '';
          //當聊天種類為一對一服務模式，且關係人員ID不為0時，取得關係人員名稱後寫入指定管理標註
          if(chatObj[i]['chat_type'] == 1 && chatObj[i]['chat_incharge'] > 0){
            var markerTitle = $('#messAdmin .dropdown-item[data-adminid="'+chatObj[i]['chat_incharge']+'"]').find('.dropdown-item-title b').html();
            markerText = '取消指定';
          }
          //當聊天種類為公共廣播時，增加廣播圖標
          if(chatObj[i]['chat_type']==0){
            textBG = ' podCast';
            castIcon = '<i class="fas fa-solid fa-podcast"></i>';
          }
          //訊息框內容佈局
          messageHtml += `<div id="msg_${chatObj[i]['chat_id']}" name="msg_${chatObj[i]['chat_id']}" class="direct-chat-msg${classPart}" style="width:70%;" data-chatid="${chatObj[i]['chat_id']}" data-postid="${chatObj[i]['chat_postid']}" data-messtype="${chatObj[i]['chat_messtype']}" data-incharge="${chatObj[i]['chat_incharge']}" data-replyid="${chatObj[i]['chat_replyid']}" data-downfile="${downfile}">`;
          messageHtml += `<div class="direct-chat-infos clearfix">`;
          messageHtml += `<span class="direct-chat-name${floatName}">${posterTitle}</span>`;
          messageHtml += `<span class="direct-chat-timestamp${floatTime}">${castIcon} ${dateTime}</span>`;
          messageHtml += `</div>`;
          messageHtml += `<img class="direct-chat-img" src="${avatarPath}" alt="Message User Image">`;
          messageHtml += `<span class="direct-chat-marker badge${markerBagdeClass}" >${markerTitle}</span>`;
          messageHtml += `<div class="direct-chat-text${textBG}">${message}</div>`;
          messageHtml += `<div class="listopt">`;  
          messageHtml += `<div class="optlink">${optSpliter}`;
          if(floatName === ' float-right'){//管理者
            if(chatObj[i]['chat_messtype'] > 0){ messageHtml += `<span class="optdown"><a href="../uploadFiles/chat/${downfile}" download="${origFilename}">下載</a></span>${optSpliter}<span class="optdown"><a href="../uploadFiles/chat/${downfile}" target="_new">預覽</a></span>${optSpliter}`; }
            messageHtml += `<span class="optkeep">${keepText}</span>${optSpliter}`;
            if(chatObj[i]['chat_status'] != 5){
              messageHtml += `<span class="optback">${backText}</span>${optSpliter}`;
              messageHtml += `<span class="optdelete">${delText}</span>${optSpliter}`;
            }
          } else{//會員端
            messageHtml += `<span class="optresponse">回覆</span>${optSpliter}`;
            if(chatObj[i]['chat_messtype'] > 0){ messageHtml += `<span class="optdown"><a href="../uploadFiles/chat/${downfile}" download="${origFilename}">下載</a></span>${optSpliter}<span class="optdown"><a href="../uploadFiles/chat/${downfile}" target="_new">預覽</a></span>${optSpliter}`; }
            messageHtml += `<span class="optkeep">${keepText}</span>${optSpliter}`;
            messageHtml += `<span class="opthidemess">${hidemessText}</span>${optSpliter}`;
            if(chatObj[i]['chat_status']!=6){
              messageHtml += `<span class="optvio">${vioText}</span>${optSpliter}`;
              messageHtml += `<span class="optincharge">${markerText}</span>${optSpliter}`;
            }
          }
          messageHtml += `</div></div></div>`;
          messageHtml += `<div class="clearfix"></div>`;
        }//for
        return messageHtml;
      } else {
        return '';
      }
    }
    //系統通知顯示
    function appendSystemMessage(asObj) {
      var messageHtml = `
          <div class="direct-chat-msg" style="width:100%;text-align:center;">
            <span class="system-message">-- [系統通知] ${asObj[0]['message']} --</span>
          </div>
          <div class="clearfix"></div>
          `;
      return messageHtml;
    }
    //上線使用者重新排序
    function usersOnlineResort() {
      $('#usersOnline .dropdown-item').sort(sort_item).appendTo('#usersOnline');
    }
    function sort_item(a, b) {
      return ($(b).data('datetime')) > ($(a).data('datetime')) ? 1 : -1;//desc
      //return ($(b).data('datetime')) < ($(a).data('datetime')) ? 1 : -1;//asc
    }
    //滾動至最下方
    function scrollToBottom() {
      $('.direct-chat-messages') .animate ({
        scrollTop: $('.direct-chat-messages').get(0).scrollHeight}, 500, 'linear', function () {
          var waitloader = $('.waitloader');
          if(waitloader.length > 0 && waitloader.css('visibility') == 'visible'){
            waitloader.css({'visibility':'hidden'});
          }
      });
    }
    //滾動至指定位置
    function scrollToPosition(tyPe, iD){//data-[【tyPe】]=【iD】//
      var container = $('.direct-chat-messages');
      var scrollTo = container.find('.direct-chat-msg[data-'+ tyPe +'="'+ iD +'"]');
      var pi = 0;
      var pLength = scrollTo.length;
      $(scrollTo).each(function(key, val){
        //console.log(key, val);
        var thisID = $(val).data(tyPe);
        if(thisID === '0'){
          pLength = pLength - 1;
          return;
        } else {
          if(pi === scrollNum){ 
            scrollTo = $(val);
            scrollNum++;
            if(scrollNum == pLength){ scrollNum = 0; }
            return false;
          } else {
            pi++;
            return;
          }
        }
      })

     
      //scrollTo = scrollTo.first();
      //alert(iD+'/'+scrollTo.data(tyPe));
      container.animate({
        scrollTop: scrollTo.offset().top
        - container.offset().top  
        + container.scrollTop()
      }, 500);
      
    }
    /*
    function js_addslashes(str) {
        str = str.replace(/\\/g, '\\\\');
        str = str.replace(/\'/g, '\\\'');
        str = str.replace(/\"/g, '\\"');
        str = str.replace(/\0/g, '\\0');
        return str;
    }
    function js_stripslashes(str) {
        str = str.replace(/\\'/g, '\'');
        str = str.replace(/\\"/g, '"');
        str = str.replace(/\\0/g, '\0');
        str = str.replace(/\\\\/g, '\\');
        return str;
    }
    */
    //------------- URL 帶值開啟頁面時處理----------------
    let cCastElm = $('.card-froms .fa-podcast');
    if(cCastElm.length > 0 && cCastElm.hasClass('active')){//當URI值帶有公共廣播模式時
      cCastElm.removeClass('active');//先移除預設的公共廣播模式，再重新啟動以正常驅動。
      cCastElm.click();
    } else {//當URI值不帶有公共廣播模式時
      var cMBelm = $('#messfrom .dropdown-item[data-joinid="'+joinID+'"]');
      if(joinID > 0 && cMBelm.length > 0){//且URI值帶有談話室ID(會員ID)時
        cMBelm.removeClass('active');
        cMBelm.click();
      }
    }

  });

  ////////////////////////////////////////////////////////////////     
  // ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
  // -------------- 可接收include檔案要求後自定義動作 ------------- //
  ////////////////////////////////////////////////////////////////
  // 當「重置」按鈕按下時的動作
  function doButReset(){
    location.reload(true);
  }




</script>
</body>
</html>
