<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_ROBOT');
define('PAGE_ITEM','ITEM_MESS');
define('PAGE_OPANEL',4);//1-新增；2-編輯；3-上傳；4-編輯/刪除；9-[空]。
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//
$rbotID=currID;
//
$res = $DB->row("SELECT * FROM chatbot_response WHERE rbot_id=?",array($rbotID));
//$DB->closeConnection();

//功能陣列建立
// Get the contents of the JSON file
$geJS=_fn_getCachedJsonData("../jsonFiles/general.json");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
	
  <title> Fifth Season Life 人生第五季 | 自動應答內容編輯 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <!--
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-bs4/css/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-responsive/css/responsive.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-buttons/css/buttons.bootstrap4.css">
  -->
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Dropzone JS style -->
  <link rel="stylesheet" href="../pluginFiles/dropzone/dropzone.css" />
  <!-- Cropper JS style -->
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <!-- suneditor -->
  <link rel="stylesheet" href="../pluginFiles/suneditor/dist/css/suneditor.min.css<?php echo '?t='.time(); ?>">
  <!-- codeMirror -->
  <link rel="stylesheet" href="../pluginFiles/codemirror/lib/codemirror.min.css<?php echo '?t='.time(); ?>">
    <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css<?php echo '?t='.time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.lightbox.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css<?php echo '?t='.time(); ?>">

<!-- -------------------------- /CSS Link -------------------------- -->
<style>
  .processingBut{
    cursor: pointer;
  }
</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <!-- processing waiting -->
  <div id="loadingMask" style="top:0;left:0;position:absolute;display:block;width:100%;height:100%;background-color:#2A2826;background-position: 50% 0;opacity: 0.75;margin:0;text-align: center; z-index:99999;display:none;">
    <div style="color:#FFFFFF;position: relative;top: 50%;transform: translate(0, -50%);">
      <img src="../imageFiles/loading2.gif" style="left:50%;top:50%;"><br> 操作進行中，請稍候...
    </div>
  </div>
  <!-- 全頁框 -->
  <div class="wrapper">
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="../imageFiles/preloaderLOGO.png" alt="Fifth Season Life LOGO" height="60" width="60"> 
    </div>
    <!-- /Preloader -->
    <!-- 頁內全頁內容 -->
    <div class="content-wrapper">
      <!-- 頁頭導覽條 -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-7">
              <h4 class="m-0"><?php echo !empty($rbotID) ? "編輯" : "新增"; ?>自動應答資料<span style="font-size:large;">【ID: <?php echo $rbotID; ?>】</span></h4>
            </div>
            <div class="col-sm-5">
              <ol class="breadcrumb float-sm-right small">
                  <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                  <li class="breadcrumb-item">訊息傳遞管理</li>
                  <li class="breadcrumb-item prevLink" alt="返回列表" title="返回列表" onClick="window.location.replace('mess_resplist.php');">自動應答紀錄</a></li>
                <li class="breadcrumb-item active">自動應答詳細資料</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <!-- /頁頭導覽條 -->
      <!-- 主要內容區域 ------------------------------------- -->
      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-4">
              <!-- Profile Image -->
              <div class="card card-primary">
                <div class="card-header">
                  <h3 class="card-title">自動應答回覆內容</h3>
                </div><!-- /.card-header -->

                <div class="card-body">
                    <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/mb_chat_func.php" method="post" data-formmode="infoupdate" data-func="func_resp_infoUpdate">
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <label for="rbot_des" class="col-sm-12 col-form-label">簡述</label>
                            </div>
                            <div class="col-sm-12">
                                <textarea id="rbot_des" name="rbot_des" style="width:100%;height:100px;"><?php echo !empty($res['rbot_des']) ? $res['rbot_des'] : "&nbsp;"; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <label for="rbot_response" class="col-sm-12 col-form-label">回覆內容</label>
                            </div>
                            <div class="col-sm-12">
                                <textarea id="rbot_response" name="rbot_response" style="width:100%;height:240px;"><?php echo !empty($res['rbot_response']) ? $res['rbot_response'] : "&nbsp;"; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <label for="rbot_status" class="col-sm-12 col-form-label">狀態</label>
                            </div>
                            <div class="col-sm-12">
                                <select class="form-control" id="rbot_status" name="rbot_status"<?php echo NdisAble; ?>>
                                    <?php
                                        foreach($geJS['onoff'] as $key => $value){
                                            echo '<option value="'.$key.'"';
                                            if($key==""){ echo " disabled"; }
                                            if((!is_null(@$res['rbot_status']) && @$res['rbot_status']==$key) || (is_null(@$res['rbot_status']) && $key==1)) echo " selected";
                                            echo '>'.$value.'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->

            </div>
            <!-- /.col -->
            <div class="col-md-8">
              <div class="card card-secondary">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#tabMenu1" data-index="1" data-toggle="tab" data-topbut="1">提問內容列表</a></li>
                  </ul>
                </div><!-- /.card-header -->
                <div class="card-body">
                  <div class="tab-content">
                    <!-- 第一分頁文章資料修改 -->
                    <div class="tab-pane active" id="tabMenu1">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;每一組自動回覆內容，都會有至少一個以上的提問，提問內容越簡潔越有效，。</div>
                      <hr>
                        <div class="card-body">
                            <ul id="qbot_list" class="navbar-nav qbot_list">
                                <?php
                                    $qr = $DB->query("SELECT * FROM chatbot_question WHERE rbot_id=?",array($rbotID));
                                    $qi = 0; //以$i為$nameList這個陣列的索引值
                                    while ($qi < count($qr)) {
                                ?>
                                <li id="qbot_<?php echo $qr[$qi]['qbot_id']; ?>" data-qbotid="<?php echo $qr[$qi]['qbot_id']; ?>" data-qbotsta="<?php echo $qr[$qi]['qbot_status']; ?>" class="nav-item">
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" id="qbot_cont" value="<?php echo $qr[$qi]['qbot_question']; ?>"<?php if($qr[$qi]['qbot_status']==0){ echo "disabled"; } ?>>
                                        </div>
                                        <div class="col-sm-6">
                                            <button id="qbot_sta" type="button" class="btn btn-md<?php echo $qr[$qi]['qbot_status']==1 ? " btn-danger" : " btn-success"; ?>" data_qbotid="<?php echo $qr[$qi]['qbot_id']; ?>"><?php echo $qr[$qi]['qbot_status']==1 ? "停用" : "啟用"; ?></button>
                                            <button id="qbot_up" type="button" class="btn btn-md btn-primary" data_qbotid="<?php echo $qr[$qi]['qbot_id']; ?>">編輯</button>
                                            <button id="qbot_del" type="button" class="btn btn-md btn-primary" data_qbotid="<?php echo $qr[$qi]['qbot_id']; ?>">刪除</button>
                                        </div>
                                    </div>
                                </li>
                                <?php 
                                        $qi++;
                                    }
                                ?>
                            </ul>
                            <hr>
                            <ul class="navbar-nav qbot_list">
                              <li class="nav-item" data-qbotid="0" data-qbotsta="1">
                                <div class="form-group row">
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="qbot_question" name="qbot_question" value="">
                                    </div>
                                    <div class="col-sm-2">
                                        <select class="form-control" id="rbot_status" name="rbot_status"<?php echo NdisAble; ?>>
                                        <?php
                                            foreach($geJS['onoff'] as $key => $value){
                                                echo '<option value="'.$key.'"';
                                                if($key == ""){ echo " disabled"; }
                                                else if($key == 1){ echo " selected"; }
                                                echo '>'.$value.'</option>';
                                            }
                                        ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <button id="qbot_add" type="button" class="btn btn-md btn-info" data_qbotid="0">新增</button>
                                    </div>
                                </div>
                              </li>
                            </ul>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /第一分頁文章資料修改 -->
                  </div>
                  <!-- /.tab-content -->
                </div><!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
      </section>
      <!-- /主要內容區域 ------------------------------------ -->
    </div>
    <!-- /頁內全頁內容 -->

    <!-- jQuery -->
    <script src="../pluginFiles/jquery/jquery.min.js"></script>
    <!-- 右側功能控制區 -->
      <aside class="control-sidebar control-sidebar-dark">
        <!-- 功能區內容 ------------------------------------ -->

        <!-- /功能區內容 ----------------------------------- -->
      </aside>
    <!-- /右側功能控制區 -->
  </div>
  <!-- /全頁框 -->
  <!-- ------------------------ Scripts Link ------------------------ -->
  <!-- jQuery is in front of footer file. -->
  <!-- Bootstrap 4 -->
  <!-- <script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script> -->
  <!-- Bootstrap 5 -->
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <!-- DataTables2 & Plugins -->
  <!--
  <script src="../pluginFiles/datatables/datatables/jquery.dataTables.min.js"></script>
  <script src="../pluginFiles/datatables/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
  -->
  <!-- DataTables3 & Plugins -->
  <script src="../pluginFiles/datatables3/datatables.min.js"></script>

  <!-- ezValidation -->
  <script src="../pluginFiles/validator/validator.js<?php echo '?t='.time(); ?>"></script>
  <!-- Toastr
  <script src="../pluginFiles/toastr/toastr.min.js"></script> -->
  <!-- Dropzone JS -->
  <script src="../pluginFiles/dropzone/dropzone.js"></script>
  <!-- Cropper JS -->
  <script src="../pluginFiles/cropper/cropper.js"></script>
  <!-- suneditor -->
  <script src="../pluginFiles/suneditor/dist/suneditor.min.js<?php echo '?t='.time(); ?>"></script>
  <script src="../pluginFiles/suneditor/sample/js/common.js<?php echo '?t='.time(); ?>"></script>
  <script src="../pluginFiles/suneditor/src/lang/zh_tw.js<?php echo '?t='.time(); ?>"></script>
  <!-- codemirror -->
  <script src="../pluginFiles/codemirror/lib/codemirror.min.js<?php echo '?t='.time(); ?>"></script>
  <script src="../pluginFiles/codemirror/mode/htmlmixed/htmlmixed.js<?php echo '?t='.time(); ?>"></script>
  <script src="../pluginFiles/codemirror/mode/xml/xml.js<?php echo '?t='.time(); ?>"></script>
  <script src="../pluginFiles/codemirror/mode/css/css.js<?php echo '?t='.time(); ?>"></script>
  <!-- 5SeasonLife App -->
  <script src="../scriptFiles/5sladmin.js"></script>
  <!-- Custom JS style -->
  <script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/fileupload.js<?php echo '?t='.time(); ?>"></script>
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

  <!-- ------------------------ /Scripts Link ------------------------ -->
  <script type="text/javascript">
////////////////////////////////////////////////////////////////
// ---------------- JAVASCRIPTS 全域調用參數設置 --------------- //
////////////////////////////////////////////////////////////////



////////////////////////////////////////////////////////////////
// -------------- JAVASCRIPTS FUNCTIONs 全域調用 -------------- //
// -------------- 可接收include檔案要求後自定義動作 -------------- //
////////////////////////////////////////////////////////////////
    // 當「儲存」按鈕按下時的動作
    function doButSave(){
      var frm = $('#form_'+$('.tab-pane.active').prop("id"));
      var accE = frm.data('func');
      var getValid = Validator.validate(frm);
      if(!getValid){
        return false;
      } else {
        ajaxPOST(frm);
      }
    }
    // 當「重置」按鈕按下時的動作
    function doButReset(){
      location.reload(true);
    }
    // 當「刪除」按鈕按下時的動作
    function doButDelete(){
      $.ajax({
          type: 'post',
          url: '../funcFiles/mb_chat_func.php',
          data: {
            'arrDatA': {}, 
            'formAcce':'func_resp_delete',
            'token': getTokenVal(), 
            'rbotID':'<?php echo $rbotID; ?>'
          },
          dataType: 'json',
          async: false,
      beforeSend: function() {
          if(!confirm('刪除自動應答紀錄同時，將同時刪除相關的提問內容，確定刪除此筆自動應答紀錄 ?')){ return false; }
          },
        success: function (data) {
            if(data['RTN']==1){
              window.location.replace("mess_resplist.php");
            } else {
              console.log(data);
              window.parent.toastr['error']('刪除檔案失敗，請重新操作或聯絡管理人員！！');
            }
          },
          error: function (data) {
            console.log(data);
            window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
          }
      });
    }
    function doReDirect(){
    window.history.back();
    }

    ///////////////////////////// AJAX 表單處理 ////////////////////////////////

    function ajaxPOST(frm){
      var frm = $('#form_tabMenu1');
      var accE = frm.data('func');
      var url = frm.prop('action');
      var typE = frm.prop('method');
      var qDatas = $('#qbot_list li');
      var datA={
        'rbot_des': $('#rbot_des').val(),
        'rbot_response': $('#rbot_response').val(),
        'rbot_status': $('#rbot_status').val()
      },datB={};
      if(0==parseInt('<?php echo $rbotID; ?>')){
        accE = 'func_resp_addnew';
        qDatas.each(function(key, val){
          datB[key] = {
            'qbot_question':$(val).find('#qbot_cont').val(),
            'qbot_status':$(val).data('qbotsta')
          };
        });
      }
      else{
        accE = 'func_resp_infoUpdate';
        //datA = getFormObjects(frm);
      }
      console.log(datA,datB);
      $.ajax({
          type: typE,
          url: url,
          data: {
            'arrDatA':datA, 
            'arrDatB':datB, 
            'formAcce':accE,
            'token': getTokenVal(), 
            'rbotID':'<?php echo $rbotID; ?>'
          },
          dataType: 'json',
          async: false, 
        success: function (data) {
            if(accE == 'func_resp_addnew'){
              window.location.replace("mess_respinfo.php?currID="+data['newID']);
            }
            window.parent.toastr[data['msgType']](data['msgStr']);
          },
          error: function (data) {
            console.log(data);
            window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
          }
      });
    }

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
  ///////////////////////// 「操作進行中等待」觸發處理 ///////////////////////////////
      $('.processingBut').on('click',function(){
        //$('.preloader').css("height", "").children().show();
        $('#loadingMask').show();
        var type = $('#xxxx_type').val();
          $.ajax({
              type: 'post',
              url: '../funcFiles/xxxxxx_func.php',
              data: {
                'arrDatA': {
                  "subject": $('#xxxx_subscript').val(),
                  "body": $('#xxxx_body').val()
                }, 
                'formAcce':'func_xxxxxx',
                'token': getTokenVal(), 
                'rbotID':'<?php echo $rbotID; ?>'
              },
              dataType: 'json',
              //async: false,
              success: function (data) {
                  //$('.preloader').css("height", 0).children().hide();
                  $('#loadingMask').hide();
                  window.parent.toastr[data['msgType']](data['msgStr']);
                  console.log(data);
              },
              error: function (data) {
                $('.preloader').css("height", 0).children().hide();
                window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
                console.log(data);
              }
          });
      });

      $('.qbot_list').on('click', 'li button',function(){
        var qbotLi = $(this).parent().parent().parent();
        var qbotID = parseInt(qbotLi.data('qbotid'));
        var qbotSta = qbotLi.data('qbotsta');
        var button = $(this).attr('id');
        var datA=0, accE='', addStatus, addQuestion;

        if(0 === parseInt('<?php echo $rbotID; ?>')) {
          if(button === 'qbot_add'){
            addStatus = qbotLi.find("#rbot_status");
            addQuestion = qbotLi.find("#qbot_question");
            var newDisable = "", newClass = " btn-danger", newText = "停用";
            if(addStatus.val() === '0') {
              newDisable = " disabled";
              newClass = " btn-success";
              newText = "啟用";
            }
            var addHTML = `
                  <li id="qbot_0" data-qbotid="0" data-qbotsta="${addStatus.val()}" class="nav-item">
                      <div class="form-group row">
                          <div class="col-sm-6">
                              <input type="text" class="form-control" id="qbot_cont" value="${addQuestion.val()}"${newDisable}>
                          </div>
                          <div class="col-sm-6">
                              <button id="qbot_sta" type="button" class="btn btn-md${newClass}" data_qbotid="0">${newText}</button>
                              <button id="qbot_up" type="button" class="btn btn-md btn-primary" data_qbotid="0">編輯</button>
                              <button id="qbot_del" type="button" class="btn btn-md btn-primary" data_qbotid="0">刪除</button>
                          </div>
                      </div>
                  </li>
            `;
            $('#qbot_list').append(addHTML);
            qbotLi.find("#rbot_status").val('1').change();
            qbotLi.find("#qbot_question").val('');

          }
          return false;

        }

        switch(button){
          case 'qbot_sta':
            if(qbotSta=='0') { datA = 1; }
            accE = 'func_qbot_status';
          break;
          case 'qbot_del':
            accE = 'func_qbot_delete';
          break;
          case 'qbot_up':
            datA = {
              'qbot_question': qbotLi.find('input').val()
            };
            accE = 'func_qbot_update';
          break;
          case 'qbot_add':
            //addStatus = qbotLi.find("#rbot_status option:selected");
            addStatus = qbotLi.find("#rbot_status");
            addQuestion = qbotLi.find("#qbot_question");
            datA = {
              'qbot_question': addQuestion.val(),
              'qbot_status': addStatus.val()
            };
            accE = 'func_qbot_addnew';
          break;
        }
        $.ajax({
              type: 'post',
              url: '../funcFiles/mb_chat_func.php',
              data: {
                'arrDatA': datA, 
                'formAcce': accE,
                'token': getTokenVal(), 
                'rbotID': '<?php echo $rbotID; ?>',
                'qbotID': qbotID
              },
              dataType: 'json',
              //async: false,
              beforeSend: function() {
                if(button === 'qbot_del'){
                  if(!confirm('確定刪除此筆提問內容 ?')){ return false; }
                }
                else if(button === 'qbot_add'){
                  if(!confirm('確定新增此筆提問內容 ?')){ return false; }
                  else {
                    if($.trim(qbotLi.find('input').val()) === '') { 
                      window.parent.toastr['error']('請輸入提問內容！'); 
                      return false; 
                    }
                  }
                }
              },
              success: function (data) {
                  window.parent.toastr[data['msgType']](data['msgStr']);
                  console.log(data);
                  if(data.RTN == 0){ return false; }
                  if(button==='qbot_sta'){
                    if(qbotSta=='0') {
                        qbotLi.data('qbotsta', '1');
                        qbotLi.find('input').attr('disabled', false);
                        qbotLi.find('button#qbot_sta').removeClass('btn-success').addClass('btn-danger').text('停用');
                    }
                    else {
                        qbotLi.data('qbotsta', '0');
                        qbotLi.find('input').attr('disabled', true);
                        qbotLi.find('button#qbot_sta').removeClass('btn-danger').addClass('btn-success').text('啟用');
                      }
                  } else if(button==='qbot_del'){
                    qbotLi.remove();
                  } else if(button==='qbot_add'){
                    var newDisable = "", newClass = " btn-danger", newText = "停用";
                    if(addStatus.val() === '0') {
                      newDisable = " disabled";
                      newClass = " btn-success";
                      newText = "啟用";
                    }
                    var addHTML = `
                          <li id="qbot_${data.newID}" data-qbotid="${data.newID}" data-qbotsta="${addStatus.val()}" class="nav-item">
                              <div class="form-group row">
                                  <div class="col-sm-6">
                                      <input type="text" class="form-control" id="qbot_cont" value="${addQuestion.val()}"${newDisable}>
                                  </div>
                                  <div class="col-sm-6">
                                      <button id="qbot_sta" type="button" class="btn btn-md${newClass}" data_qbotid="${data.newID}">${newText}</button>
                                      <button id="qbot_up" type="button" class="btn btn-md btn-primary" data_qbotid="${data.newID}">編輯</button>
                                      <button id="qbot_del" type="button" class="btn btn-md btn-primary" data_qbotid="${data.newID}">刪除</button>
                                  </div>
                              </div>
                          </li>
                    `;
                      $('#qbot_list').append(addHTML);
                      qbotLi.find("#rbot_status").val('1').change();
                      qbotLi.find("#qbot_question").val('');
                  }
                  $(this).blur();
              },
              error: function (data) {
                window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
                console.log(data);
              }
          });
      });

    //-------- 加載完成後更改外框主頁中樹狀目錄開合狀態 --------//
    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  //////////////////////////////////////////////////////////////////
  });


  </script>
</body>
</html>
