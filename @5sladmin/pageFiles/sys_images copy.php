<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_SYSIMG');
define('PAGE_ITEM','ITEM_SYS');
define('PAGE_OPANEL',3);
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//define('pcSave',3);//1-新增；2-重置/編輯/刪除;3-重置/刪除 

$cateArr = isset($_REQUEST['cateArr']) ? explode(',', $_REQUEST['cateArr']) : array(0);
//功能陣列建立，取自JSON檔案
//$imgGet=file_get_contents("../jsonFiles/imagelist.min.json");
//$imgGet = DB->single("SELECT sj_json FROM sys_json WHERE sj_code=?",array('sysimglist'));
$imgGet = DB->single("SELECT xj_json FROM sys_json WHERE xj_code=?",array('sysimglist'));
$imgJS = json_decode($imgGet,true);

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
	
	<title> Fifth Season Life 人生第五季 | 系統圖庫管理 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.sysimages.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
<!-- -------------------------- /CSS Link -------------------------- -->
<style>
  ul.list-group-unbordered > li div{
    padding-right:12px;
  }
  ul.list-group-unbordered > li.incate{
    background-color:#C1DBF3;
  }
  ul.list-group-unbordered > li i{
    float:right;
    margin-right:4px;
  }
  ul.list-group-unbordered > li:hover{
    cursor: pointer;
    background-color:#74ADE3;/*C1DBF3*/
  }
/*
  modalHtml += '<div class="form-group row">';
      modalHtml += '<label for="sample_image" class="col-sm-2 col-form-label"></label>';
      modalHtml += '<div for="img_upload" id="sample_image" class="col-md-10">';
      modalHtml += '<label for="img_upload"><img src="../imageFiles/nopic_400x300.jpg"></label>';
      modalHtml += '<input type="file" class="form-control" id="img_upload" name="img_upload[]" style="display:none" multiple="multiple">';
      modalHtml += '</div>';
      modalHtml += '<input type="hidden" id="img_idX" name="img_idX" value="N">';
      modalHtml += '</div>';
      */
.wrap-custom-file label img{
    position:absolute;
    left:0;
    right:0;
    width: 100%;
    height: 100%; 
    object-fit: cover;
    z-index:-1;
}

#sample_image,#sample_image label,#sample_image label div.siimgwrap{
  width: 100%;
  margin:0;
  overflow:hidden;
  cursor: pointer;
}
#sample_image div:not(.sioverlay,.siovertext,.siimgwrap){
  position: relative;
  width:160px;
  height:120px;
  border:1px solid black;
  /*padding:10px;*/
  border-radius: 10px;
  color: #2C333E;
  display:inline-block;
  /*background-color: #5F6E86; */
  margin: 10px;
  overflow: hidden;
  /*display: flex;*/
  justify-content: center;
  align-items: center;
}
#sample_image label div.siimgwrap img{
  width: 100%;
  border-radius: 10px;
}
#sample_image div.siimgwrap div img{
  width:100%;
  height:100%;
  object-fit: cover;
}
/*
#sample_image div i{
  position: absolute;
  left: 0px;
  bottom: 0px;
  margin-bottom: -8px;
}
*/
/*
modalHtml += '<div class="form-group row">';
modalHtml += '  <label for="sample_image" class="col-sm-2 col-form-label">選擇圖像</label>';
modalHtml += '  <div for="img_upload" id="sample_image" class="col-md-10" style="position: relative;">';
modalHtml += '      <label for="img_upload">';
modalHtml += '        <div class="siimgwrap"><img src="'+imgList[idX]['src']+'"></div>';
modalHtml += '        <input type="file" class="form-control" id="img_upload" name="img_upload[]" style="display:none">';
modalHtml += '        <div class="sioverlay">';
modalHtml += '          <div class="siovertext">更改代表圖</div>';
modalHtml += '        </div>';
modalHtml += '      </label>';
modalHtml += '  </div>';
modalHtml += '  <input type="hidden" id="img_idX" name="img_idX" value="'+idX+'">';
modalHtml += '  <input type="hidden" id="img_src" name="img_src" value="'+imgList[idX]['src']+'">';
modalHtml += '</div>';
*/
.sioverlay {
  position: absolute;
  width: 100%;
  bottom: 10px;
  left: 10px;
  right: 10px;
  background-color: rgba(0, 0, 0, 0.65);
  overflow: hidden;
  height: 0;
  transition: .5s ease;
}
#sample_image:hover .sioverlay {  
  height: 25%;
  
}
.siovertext {
  color: #fff;
  font-size: 1.2em;
  position: absolute;
  top: 50%;
  left: 50%;
  -webkit-transform: translate(-50%, -50%);
  -ms-transform: translate(-50%, -50%);
  transform: translate(-50%, -50%);
}
.sioverlay div{
  /*right: 50%;*/
  margin-left: 0px;
  border-radius: 0.5em;
}
</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <!-- 全頁框 -->
  <div class="wrapper">
    <!-- Preloader  -->
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
            <div class="col-sm-6">
              <h4 class="m-0">系統圖庫管理</h4>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">系統設定管理</li>
                <li class="breadcrumb-item active">系統圖庫管理</li>
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </section>
      <!-- /頁頭導覽條 -->
      <!-- 主要內容區域 ------------------------------------- -->
      <section class="content">
        <div class="container-fluid">
          <div class="row">

            <div class="col-md-3">
              <div class="card card-primary card-outline shadow">
                <div class="card-header">
                  <h5 class="card-title">圖庫分類</h5>
                </div>
                <div class="card-body">
                  <ul class="list-group list-group-unbordered mb-3">
                  <?php
                    $nowon = array();
                    //$cateList = array_unique(array_column($imgJS['result'],'tag'));
                    //$cateList = array_reverse(array_unique(array_column($imgJS['result'],'tag')),true);
                    $cateList = array_unique(array_column($imgJS['result'],'tag'));
                    sort($cateList);
                    foreach( $cateList as $key => $value){
                      $incate = '';
                      $inchk = '';
                      if(in_array($key, $cateArr)){
                        $incate = ' incate';
                        $inchk = ' fa-check';
                        array_push($nowon, $value);
                      }
                      //echo $key;
                      echo '<li class="list-group-item'.$incate.'">';
                      echo '<i class="nav-icon fas'.$inchk.'"></i>';
                      echo '<div class="float-right imgcate" idxkey="'.$key.'">'.$value.'</div>';
                      echo '</li>';
                    }
                  ?>
                  </ul>
                </div>
              </div>
            </div>

            <div class="col-md-9">
              <div class="card card-secondary shadow">
                <div class="card-header">
                  <h5 class="card-title">圖像列表</h5>
                </div>
                <form id="func_ad_imgdelete" name="func_ad_imgdelete" action="../funcFiles/ad_act_func.php" method="post">
                  <div style="font-size:0.85em;line-height:1.4em;text-align:left;padding:8px 24px 0px 24px;">
                    <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;完全由後台管理的圖庫，以圖檔型態儲存於本機端，運用於本站前後台各類文書傳遞類模板中圖像的鏈結使用，圖檔鏈結方式較為快速且低資源；另一個使用HTML編輯器顯示圖像的方式為使用Base64編碼直接寫入HTML程式碼中，或存入資料庫中調用，較為耗費資源但容易管理，兩種圖像顯示方式在編輯其中皆可選擇。
                    <hr>
                  </div>
                  <div class="card-body">
                  <?php
                    $imgARR = array_reverse($imgJS['result'],true);
                    //$imgARR = $imgJS['result'];
                    $i=0;
                    //while($i<count($imgARR)){
                      foreach($imgARR as $key => $value ){
                      if(count($nowon)==0 || in_array($imgARR[$key]['tag'], $nowon)){
                        //$viID = $imgARR[$key]['id'];
                  ?>
                    <div class="area-upload">
                      <div class="wrap-custom-file">
                        <label for="imgUpID_<?php echo $key; ?>" fileext="" class="file-ok">
                          <input type="checkbox" name="imgDel[<?php echo $key; ?>]" id="imgDel[<?php echo $key; ?>]" value="<?php echo $key; ?>" title="刪除" alt="刪除">
                          <!-- <i class="nav-icon fas fa-2x fa-eye"></i> -->
                          <span>
                            <div><?php echo $key."_"; ?><?php echo $imgARR[$key]['name']; ?></div>
                            <i><?php echo $imgARR[$key]['tag']; ?></i>
                          </span>
                          <img alt="<?php echo $imgARR[$key]['alt']; ?>" title="<?php echo $imgARR[$key]['alt']; ?>" src="<?php echo $imgARR[$key]['src'].'?t='.time(); ?>">
                        </label>
                      </div>
                    </div>
                  <?php
                      }
                      $i++;
                    } 
                  ?>
                  </div>
                </form>
              </div>
            </div>

          </div>
        </div>
      <!-- -------------------- 裁切頭像彈出視窗 -------------------- -->
      <div id="imgListModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="background:rgba(0,0,0,0.75);">
          <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
              <!-- Header -->
              <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button type="button" id="modalCloseTop" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <!-- /Header -->
              <!-- Model Body -->
              <div class="modal-body">
                <h6 class="modal-des"></h6>
                <form id="func_ad_imgupdate" name="func_ad_imgupdate" action="../funcFiles/ad_act_func.php" method="post"  enctype="multipart/form-data">
                  <div class="img-container"></div>
                </form>
              </div>
              <!-- /Model Body -->
              <!-- Footer -->
              <div class="modal-footer justify-content-between">
                <button type="button" id="modalCloseBut"  class="btn btn-default" data-dismiss="modal">關閉</button>
                <button type="button" id="crop" class="btn btn-primary"></button>
              </div>
              <!-- /Footer -->
            </div>
          </div>
        <!-- /.modal-dialog -->
        </div>
      <!-- -------------------- /裁切頭像彈出視窗 -------------------- -->
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
<script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
<script src="../scriptFiles/demo.js"></script>
<!-- ------------------------ /Scripts Link ------------------------ -->
<script type="text/javascript">
  $(function(){
  

  

    //* iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });
    //-----------------------------------------
    window.parent.$('#topBut_delete').removeClass('active');
    // --------------------------- 各式按鈕行為 --------------------- //
    var imgObj = $.parseJSON( '<?php echo func_stripewhite($imgGet); ?>' ); 
    var keyList = '<?php echo implode(',', $cateArr); ?>';
    var imgList = imgObj['result']; 
    var CHGform = $('#func_ad_imgdelete');
    var ORGForm = CHGform.serialize();
    var CHGform2 = $('#func_ad_imgupdate');
    var ORGForm2;
    //導覽子頁連動操作按鈕框隱藏/顯示
    $('.nav-toppanel').show();
    //window.parent.$('#topBut_download').addClass('active');
    window.parent.$('#topBut_delete').removeClass('active');//關閉刪除按鍵
    //---------------- 圖庫分類異動處理 ----------------------
    //---------------- 當點選分類格時的處理動作 ---------------
    $('ul.list-group-unbordered > li').click(function(e){ 
      e.preventDefault();
      if($(this).hasClass('incate')){
        $(this).removeClass('incate');
        $(this).find('i').removeClass('fa-check');
      } else {
        $(this).addClass('incate');
        $(this).find('i').addClass('fa-check');
      }
      //------------- 生成分類陣列值 -------------------
      var cateOn = [];
      var cateOff = [];
      keyList = '';
      $('.imgcate').each(function( index ) {
        if($(this).parent().hasClass('incate')) {
          //alert($(this).html());
          cateOn.push($(this).html().replace(/ /g,''));
          keyList += $(this).attr('idxkey')+',';
        } else {
          cateOff.push($(this).html().replace(/ /g,''));
        }
      });
      if(cateOn.length <= 0){
        cateOn = cateOff;
        keyList = '';
      }
      keyList = keyList.slice(0, -1);
      //------------ 生成（已選的分類中）新的圖像列表 HTML ------------
      var newImgList = '';
      var ri = imgList.length-1;
      $.each(imgList, function(i) {
        if($.inArray(imgList[ri].tag.replace(/ /g,''), cateOn)>=0){
          var imgHTML = '';
          imgHTML += '<div class="area-upload">';
          imgHTML += '<div class="wrap-custom-file">';
          imgHTML += '<label for="imgUpID_'+ ri +'" class="file-ok">';
          imgHTML += '<input type="checkbox" name="imgDel['+ ri +']" id="imgDel['+ ri +']" value="'+ ri +'" title="刪除" alt="刪除">';
          //imgHTML += '<i class="nav-icon fas fa-2x fa-eye"></i>';
          imgHTML += '<span>';
          imgHTML += '<div>'+ imgList[ri].name +'</div>';
          imgHTML += '<i>'+ imgList[ri].tag +'</i>';
          imgHTML += '</span>';
          imgHTML += '<img alt="'+ imgList[ri].alt +'" title="'+ imgList[ri].alt +'" src="'+ imgList[ri].src +'?t='+ $.now() +'">';
          imgHTML += '</label>';
          imgHTML += '</div>';
          imgHTML += '</div>';
          newImgList += imgHTML;
        }
        ri--;
      });
      $('#func_ad_imgdelete .card-body').html(newImgList);
      window.parent.$('#topBut_delete').removeClass('active');
      var ORGForm = CHGform.serialize();
    });
    //------------------- /分類格動作處理結束 ------------------------
    //----------------- 顯示圖像右上鈕編輯彈出窗 -----------------------
    //$('#func_ad_imgdelete').on('click','label i',function(e){ 
    $('#func_ad_imgdelete').on('click','label img',function(e){ 
      $('#imgListModal').show().addClass('show');
      var idX = $(this).parent().find('input[type="checkbox"]').val();
      var modalHtml = '';
      modalHtml += '<div class="form-group row">';
      modalHtml += '  <label for="sample_image" class="col-sm-2 col-form-label">選擇圖像</label>';
      modalHtml += '  <div for="img_upload" id="sample_image" class="col-md-10" style="position: relative;">';
      modalHtml += '      <label for="img_upload"><div class="siimgwrap"><img src="'+imgList[idX]['src']+'"></div>';
      modalHtml += '      <input type="file" class="form-control" id="img_upload" name="img_upload[]" style="display:none">';
      modalHtml += '      <div class="sioverlay">';
      modalHtml += '        <div class="siovertext">更改代表圖</div>';
      modalHtml += '      </div></label>';
      modalHtml += '  </div>';
      modalHtml += '  <input type="hidden" id="img_idX" name="img_idX" value="'+idX+'">';
      modalHtml += '  <input type="hidden" id="img_src" name="img_src" value="'+imgList[idX]['src']+'">';
      modalHtml += '</div>';
      modalHtml += '<div class="form-group row" style="text-align:center;">';
      modalHtml += '<label for="img_name" class="col-sm-2 col-form-label">名稱</label>';
      modalHtml += '<div class="col-md-10">';
      modalHtml += '<input type="text" class="form-control" id="img_name" name="img_name" value="'+imgList[idX]['name']+'">';
      modalHtml += '</div>';
      modalHtml += '</div>';
      modalHtml += '<div class="form-group row">';
      modalHtml += '<label for="img_alt" class="col-sm-2 col-form-label">Alt</label>';
      modalHtml += '<div class="col-md-10">';
      modalHtml += '<input type="text" class="form-control" id="img_alt" name="img_alt" value="'+imgList[idX]['alt']+'">';
      modalHtml += '</div>';
      modalHtml += '</div>';
      modalHtml += '<div class="form-group row">';
      modalHtml += '<label for="img_tag" class="col-sm-2 col-form-label">分類</label>';
      modalHtml += '<div class="col-md-10">';
      modalHtml += '<select class="form-control" id="img_tag" name="img_tag">';
      modalHtml += '<option value="N">-新增分類-</option>';
      $('.imgcate').each(function( index ) {
        modalHtml += '<option value="'+$(this).html()+'"';
        if($(this).html() == imgList[idX]['tag']) {  modalHtml += ' selected'; }
        modalHtml += '>'+$(this).html()+'</option>';
      });
      modalHtml += '</select>';
      modalHtml += '</div>';
      modalHtml += '</div>';
      modalHtml += '<div class="form-group row">';
      modalHtml += '<label for="img_newcate" class="col-sm-2 col-form-label">新建分類</label>';
      modalHtml += '<div class="col-md-10">';
      modalHtml += '<input type="text" class="form-control" id="img_newcate" name="img_newcate" value="" disabled="disabled">';
      modalHtml += '</div>';
      modalHtml += '</div>';
      //---------------------------
      $('#imgListModal .modal-title').html('圖像編輯');
      $('#imgListModal .btn-primary').html('儲存');
      $('#imgListModal .img-container').html(modalHtml);
      //------------------- 彈出視窗狀態下Form元件異動處理 -------- 
      var ORGForm2 = CHGform2.serialize();
      $('#func_ad_imgupdate').on('change',':input',function() {
        if(CHGform2.serialize() !== ORGForm2){
          $('.btn-primary').removeAttr('disabled');
        }
        else { 
          $('.btn-primary').attr('disabled','disabled');
        }
      });

    });
    //----------------- /顯示圖像右上鈕編輯彈出窗結束 -----------------------
    //--------------- 編輯/上傳彈窗中新增分類項目操作 ------------------------
    $('#func_ad_imgupdate').on('change',':input[id="img_tag"]',function() {
      //alert($(this).val());
      if($(this).val()=='N'){
        $('#img_newcate').removeAttr('disabled');
      } else {
        $('#img_newcate').val('').attr('disabled','disabled');
      }

    });
    //----------------- 彈出窗關閉按鍵 -----------------------
    $('#imgListModal #modalCloseTop, #imgListModal #modalCloseBut').click(function(e){ 
      $('.btn-primary').attr('disabled','disabled');
      $('#imgListModal #modal-title').html('');
      $('#imgListModal .btn-primary').html('');
      $('#imgListModal #sample_image').prop('src','');
      $('#imgListModal .img-container').html('');
      $('#imgListModal').removeClass('show').hide();
    });
    //----------------------------------------------
    //---------------- 彈框按鈕_編輯/上傳按鈕 ----------------
    $('#imgListModal .btn-primary').click(function(e){
      ajaxPOST($('#func_ad_imgupdate'));
    });
    //---------------- 頁面操作按鈕框_刪除 ---------------
    window.parent.$('#topBut_delete').click(function(e){
      if (confirm('這個操作將刪除已選的圖檔！\r\r確定執行刪除？！')){
        let recomF = prompt("請輸入安全驗證碼");
        if (recomF == "我是豬") {
          ajaxPOST($('#func_ad_imgdelete'));
        }else{
          window.parent.toastr['error']('驗證碼錯誤！圖檔刪除失敗！！');
          $('#func_ad_imgdelete input:checkbox').prop('checked', false);
          window.parent.$('#topBut_delete').removeClass('active');
        }
      }
    });
    //---------------- 頁面操作按鈕框_上傳 ---------------
    //---------------- 開啟上傳彈出視窗 ---------------
    window.parent.$('#topBut_upload').click(function(e){
      $('#imgListModal').show().addClass('show');
      var modalHtml = '';
      modalHtml += '<div class="form-group row">';
      modalHtml += '  <label for="sample_image" class="col-sm-2 col-form-label">選擇圖像</label>';
      modalHtml += '  <div for="img_upload" id="sample_image" class="col-md-10" style="position: relative;">';
      modalHtml += '      <label for="img_upload"><div class="siimgwrap"><img src="../imageFiles/nopic_400x300.jpg"></div>';
      modalHtml += '      <input type="file" class="form-control" id="img_upload" name="img_upload[]" style="display:none" multiple="multiple">';
      modalHtml += '      <div class="sioverlay">';
      modalHtml += '        <div class="siovertext">更改代表圖</div>';
      modalHtml += '      </div></label>';
      modalHtml += '  </div>';
      modalHtml += '  <input type="hidden" id="img_idX" name="img_idX" value="">';
      modalHtml += '</div>';
      modalHtml += '<div class="form-group row">';
      modalHtml += '  <label for="img_name" class="col-sm-2 col-form-label">名稱</label>';
      modalHtml += '  <div class="col-md-10">';
      modalHtml += '    <input type="text" class="form-control" id="img_name" name="img_name" value="" disabled="disabled">';
      modalHtml += '  </div>';
      modalHtml += '</div>';
      modalHtml += '<div class="form-group row">';
      modalHtml += '  <label for="img_alt" class="col-sm-2 col-form-label">Alt</label>';
      modalHtml += '  <div class="col-md-10">';
      modalHtml += '    <input type="text" class="form-control" id="img_alt" name="img_alt" value="" disabled="disabled">';
      modalHtml += '  </div>';
      modalHtml += '</div>';
      modalHtml += '<div class="form-group row">';
      modalHtml += '  <label for="img_tag" class="col-sm-2 col-form-label">分類</label>';
      modalHtml += '  <div class="col-md-10">';
      modalHtml += '    <select class="form-control" id="img_tag" name="img_tag" disabled="disabled">';
      modalHtml += '      <option value="N">-新增分類-</option>';
      $('.imgcate').each(function( index ) {
        modalHtml += '<option value="'+$(this).html()+'"';
        modalHtml += '>'+$(this).html()+'</option>';
      });
      modalHtml += '    </select>';
      modalHtml += '  </div>';
      modalHtml += '</div>';
      modalHtml += '<div class="form-group row">';
      modalHtml += '  <label for="img_newcate" class="col-sm-2 col-form-label">新建分類</label>';
      modalHtml += '  <div class="col-md-10">';
      modalHtml += '    <input type="text" class="form-control" id="img_newcate" name="img_newcate" value="" disabled="disabled">';
      modalHtml += '  </div>';
      modalHtml += '</div>';
      //---------------------------
      $('#imgListModal .modal-title').html('圖像上傳');
      $('#imgListModal .modal-des').html('可同時上傳多個圖像，當選擇單一圖像上傳時，可以於上傳前編輯圖檔訊息，多圖像上傳時僅能選擇這些圖像的共同分類，若要更改圖像訊息時，請上傳後個別編輯！');
      $('#imgListModal .btn-primary').html('上傳');
      $('#imgListModal .img-container').html(modalHtml);
    });
    //--------------- 上傳彈出視窗中檔案元件動作 ------------------
    $('#func_ad_imgupdate').on('change','#img_upload',function(e){
      var currIdx = $(this).parent().parent().next().val();
        //alert(currIdx);return false;
        var upNum = $(this).get(0).files.length;
        var imgBody = '';
        for(var i=0;i<upNum;i++){
          //var fileName = $file.val().split( '\\' ).pop();//取得原始檔案名稱（含副檔名）
          var ev = event.target.files[i],
            tmppath = URL.createObjectURL(ev),//取得圖檔暫存路徑
            fSplit = ev.name.split('.'),//
            fName = fSplit[0],//
            fExt = fSplit[1],//
            fSize = Math.round(ev.size / 1024),//取得準備上傳的檔案大小（Kb）
            showPopup = tmppath;
            if (upNum==1) {imgBody += '<img src="'+showPopup+'">'; }
            else {imgBody += '<div><img src="'+showPopup+'"></div>'; }
        }
        
        if (upNum > 0) {
          $('#sample_image label div.siimgwrap').html(imgBody);
          if(upNum==1){
            $('#img_name').removeAttr('disabled').val(fName);//img_idX
            $('#img_alt').removeAttr('disabled').val(fName);
          }
          $('#img_tag').removeAttr('disabled').val('N');
          $('#img_newcate').removeAttr('disabled').val('');
          $('#imgListModal .btn-primary').removeAttr('disabled');
        } else {
          $('#sample_image label div.siimgwrap').html('<img src="../imageFiles/nopic_400x300.jpg">');
          $('#img_name').val('').attr('disabled','disabled');
          $('#img_alt').val('').attr('disabled','disabled');
          $('#img_tag').val('N').attr('disabled','disabled');
          $('#img_newcate').val('').attr('disabled','disabled');
        }

      });
    //////////////////// 表單異動行為 ////////////////////////////
    //當表單內容有改變時，操作框按鈕功能啟動
      $('#func_ad_imgdelete').on('click',':input[type="checkbox"]',function() {
        if(CHGform.serialize() !== ORGForm){
          window.parent.$('#topBut_delete').addClass('active');
        }
        else { 
          window.parent.$('#topBut_delete').removeClass('active');
        }
      });
     
  });


  ////////////////////////////////////////////////////////////////     
  // ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
  // -------------- 可接收include檔案要求後自定義動作 ------------- //
  ////////////////////////////////////////////////////////////////
  function doButUpload(){

    $('#imgListModal').show().addClass('show');
      var modalHtml = '';
      modalHtml += '<div class="form-group row">';
      modalHtml += '  <label for="sample_image" class="col-sm-2 col-form-label">選擇圖像</label>';
      modalHtml += '  <div for="img_upload" id="sample_image" class="col-md-10" style="position: relative;">';
      modalHtml += '      <label for="img_upload"><div class="siimgwrap"><img src="../imageFiles/nopic_400x300.jpg"></div>';
      modalHtml += '      <input type="file" class="form-control" id="img_upload" name="img_upload[]" style="display:none" multiple="multiple">';
      modalHtml += '      <div class="sioverlay">';
      modalHtml += '        <div class="siovertext">更改代表圖</div>';
      modalHtml += '      </div></label>';
      modalHtml += '  </div>';
      modalHtml += '  <input type="hidden" id="img_idX" name="img_idX" value="">';
      modalHtml += '</div>';
      modalHtml += '<div class="form-group row">';
      modalHtml += '  <label for="img_name" class="col-sm-2 col-form-label">名稱</label>';
      modalHtml += '  <div class="col-md-10">';
      modalHtml += '    <input type="text" class="form-control" id="img_name" name="img_name" value="" disabled="disabled">';
      modalHtml += '  </div>';
      modalHtml += '</div>';
      modalHtml += '<div class="form-group row">';
      modalHtml += '  <label for="img_alt" class="col-sm-2 col-form-label">Alt</label>';
      modalHtml += '  <div class="col-md-10">';
      modalHtml += '    <input type="text" class="form-control" id="img_alt" name="img_alt" value="" disabled="disabled">';
      modalHtml += '  </div>';
      modalHtml += '</div>';
      modalHtml += '<div class="form-group row">';
      modalHtml += '  <label for="img_tag" class="col-sm-2 col-form-label">分類</label>';
      modalHtml += '  <div class="col-md-10">';
      modalHtml += '    <select class="form-control" id="img_tag" name="img_tag" disabled="disabled">';
      modalHtml += '      <option value="N">-新增分類-</option>';
      $('.imgcate').each(function( index ) {
        modalHtml += '<option value="'+$(this).html()+'"';
        modalHtml += '>'+$(this).html()+'</option>';
      });
      modalHtml += '    </select>';
      modalHtml += '  </div>';
      modalHtml += '</div>';
      modalHtml += '<div class="form-group row">';
      modalHtml += '  <label for="img_newcate" class="col-sm-2 col-form-label">新建分類</label>';
      modalHtml += '  <div class="col-md-10">';
      modalHtml += '    <input type="text" class="form-control" id="img_newcate" name="img_newcate" value="" disabled="disabled">';
      modalHtml += '  </div>';
      modalHtml += '</div>';
      //---------------------------
      $('#imgListModal .modal-title').html('圖像上傳');
      $('#imgListModal .modal-des').html('可同時上傳多個圖像，當選擇單一圖像上傳時，可以於上傳前編輯圖檔訊息，多圖像上傳時僅能選擇這些圖像的共同分類，若要更改圖像訊息時，請上傳後個別編輯！');
      $('#imgListModal .btn-primary').html('上傳').attr('disabled','disabled');
      $('#imgListModal .img-container').html(modalHtml);
    

  }

  function doButDelete(){
    if (confirm('這個操作將刪除已選的圖檔！\r\r確定執行刪除？！')){
        let recomF = prompt("請輸入安全驗證碼");
        if (recomF == "我是豬") {
          ajaxPOST($('#func_ad_imgdelete'));
        }else{
          window.parent.toastr['error']('驗證碼錯誤！圖檔刪除失敗！！');
          $('#func_ad_imgdelete input:checkbox').prop('checked', false);
          window.parent.$('#topBut_delete').removeClass('active');
        }
      }
  }
  // 當「重置」按鈕按下時的動作
  function doButReset(){
    location.reload(true);
  }
  //////////////////////////////////////////////////////////
  //////////////////////// AJAX 執行 ////////////////////////
  function ajaxPOST(frm){
    var accE = frm.prop('id');
    var taG='';
    var datA = {};
    var base64data = '';
    var form_data = new FormData();
    if(accE=='func_ad_imgupdate'){
      //alert(accE);return false;
      var ins = $('#img_upload').get(0).files.length;

      if(ins>1){
        if(($('#img_tag').val()=='N' && $('#img_newcate').val()=='')
        || ($('#img_tag').val()!='N' && $('#img_tag').val()=='')){
          alert('請完整輸入圖像表單內容！');
          return false;
        } 
      } else {
        if($('#img_name').val()==''
        || $('#img_alt').val()==''
        || ($('#img_tag').val()=='N' && $('#img_newcate').val()=='')
        || ($('#img_tag').val()!='N' && $('#img_tag').val()=='')){
          alert('請完整輸入圖像表單內容！');
          return false;
        } 
      }

      if($('#img_tag').val()=='N'){
        taG = $('#img_newcate').val();
      } else {
        taG = $('#img_tag').val();
      }
      var elMidX = $('#img_idX').val();//img_idX
      var srC = $('#img_upload').length ? $('#img_upload').val().split('\\').pop().replace(/ /g,'_') : '';
      //alert($('#img_src').val());
      form_data.append("formAcce",accE);
      form_data.append("token",getTokenVal());
      form_data.append("idX",elMidX);
      form_data.append("srC",srC);
      form_data.append("fname",$('#img_src').val());
      form_data.append("name",$('#img_name').val());
      form_data.append("alt",$('#img_alt').val());
      form_data.append("tag",taG);
      for (var xi=0; xi<ins; xi++) {
        form_data.append("img_upload[]", $('#img_upload').get(0).files[xi]);
      }

    } else if(accE=='func_ad_imgdelete'){
      form_data.append("formAcce",accE);
      form_data.append("token",getTokenVal());
      $('#func_ad_imgdelete').find("input[type=checkbox]:checked").each(function() {
        form_data.append("arrDatA[]",$(this).val());
      });
    }
    var urL = frm.attr('action');
    var typE = frm.attr('method');
    //{"statusCode":200,"result":[{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214039.jpg","name":"le_sserafim_sakura_profile_1-1633x2000","alt":"le_sserafim_sakura_profile_1-1633x2000","tag":"Funny"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214040.jpg","name":"Cat paw","alt":"Cat paw","tag":"Cat"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214041.jpg","name":"Cat","alt":"Cat","tag":"Cat"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214042.jpg","name":"Dog","alt":"Dog","tag":"Dog"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214043.jpg","name":"Welsh Corgi","alt":"Welsh Corgi","tag":"Dog"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214044.jpg","name":"Retriever","alt":"Retriever","tag":"Dog"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214051.jpg","name":"Tiger-1","alt":"Tiger","tag":"Tiger"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214052.jpg","name":"Tiger-2","alt":"Tiger","tag":"Tiger"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214053.jpg","name":"Tiger-3","alt":"Tiger","tag":"Tiger"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214054.jpg","name":"White eagle","alt":"Bird-White eagle","tag":"Bird"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214055.jpg","name":"Ara","alt":"Bird-Ara","tag":"Bird"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214056.jpg","name":"shutterstock_2233169475","alt":"shutterstock_2233169475","tag":"\u52d5\u7269"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214057.jpg","name":"Cat-looking-up","alt":"Cat-looking-up","tag":"\u52d5\u7269"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214058.jpg","name":"16071828377_85109fdee4_o.0.0","alt":"16071828377_85109fdee4_o.0.0","tag":"\u52d5\u7269"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214059.jpg","name":"Starfish,_Caswell_Bay_-_geograph.org.uk_-_409413.jpg","alt":"Starfish,_Caswell_Bay_-_geograph","tag":"\u6d77\u6d0b"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214060.jpg","name":"funny-profile-picture-r8l2gifvpdd0kt25.jpg","alt":"funny-profile-picture-r8l2gifvpdd0kt25","tag":"Funny"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214061.jpg","name":"depositphotos_82720548-Beautiful-mystic-woman-profile-with-long-hair-looking.-Black-and.jpg","alt":"depositphotos_82720548-Beautiful-mystic-woman-profile-with-long-hair-looking","tag":"Funny"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_2024083003214038.jpg","name":"woman-1151562_64044","alt":"woman-1151562_64044","tag":"Funny"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_20240901122726708.jpg","name":"portrait-657116_640","alt":"portrait-657116_640","tag":"Funny"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_20240901122907724.jpg","name":"sysimg_20240901122907724","alt":"sysimg_20240901122907724","tag":"Funny"},{"src":"..\/Webstore\/uploadFiles\/sysimg\/sysimg_20240901122907725.jpg","name":"sysimg_20240901122907725","alt":"sysimg_20240901122907725","tag":"Funny"}]}
    //console.log(form_data);return false;
      $.ajax({
        type: typE,
          url: urL,
        data: form_data,
    dataType: 'json',
  contentType: false,
        cache: false,
  processData: false,
        async: false, 
      success: function (data) {
        //console.log(data);return false;
          if(data['RTN']==1){
            var keyList = '';
            if(data['newIdx']!=''){ keyList += ','+data['newIdx']; }
            /*
            if(accE=='func_ad_imgupdate'){
              
            } else if(accE=='func_ad_imgdelete'){
              
            }
            */
            window.location.replace("sys_images.php?cateArr=" + keyList);
          } else {
            window.parent.toastr[data['msgType']](data['msgStr']);
          }
        },
      error: function (data) {
          console.log(data);
          window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
        }
    });
  }
  //////////////////////// 執行返回行為 ////////////////////////////
  ////////////////////////////////////////////////////////////////
  function ajaxRTN(datA,acCE,rE){
    window.parent.toastr[data['msgType']](datA['msgStr']);
    if(acCE=='func_ad_imgdelete'){
        if (datA['RTN']==1) {
          //window.location.replace("sys_images.php");
        } else {
              
        }
        //window.parent.$('#topBut_delete').removeClass('active');
    } else if(acCE=='func_ad_imgupdate') {
        //window.location.replace("sys_images.php");
    } else if(acCE=='form_imgupload') {
      //$('#form_passupdate')[0].reset();
      //window.parent.$('#topBut_save').removeClass('active');
    }
  }


</script>
<?php
unset($imgGet);
?>
</body>
</html>
