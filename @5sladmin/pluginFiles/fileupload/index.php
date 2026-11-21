<?php


$imgUpArray = array(
  'upDefault' => 3,
  'upType' => '.jpg',
  'upTitle' => array('客廳','餐廳','廚房','主臥室','臥房一','臥房二','臥房三/功能房','功能房','前廊/玄關','前院/前陽台','後院/後陽台','主衛浴間','附加圖像一','附加圖像二','附加圖像三','附加圖像四'),
  'upImgPath' => array('','','','img_3_.jpg','','','','','','','','','','','',''),
  'upDes' => array('','','','第三張圖','','','','','','','','','','','','')
)
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title></title>
<!-- Font Awesome -->
<link rel="stylesheet" href="../../pluginFiles/fontawesome-free/css/all.css">
<link rel="stylesheet" href="fileupload.css">
<link rel="stylesheet" href="lightbox.css">
<style>
  body {
    margin: 0;
    padding: 1rem;
    font-family: 'Roboto', sans-serif;
    background:  rgba(68,162,115);
  }
  
</style>

</head>
<body>
  <!-- Light BOX -->
  <div id="boxModal" class="lightBoxFrame">
    <span class="close">&times;</span>
    <img class="imageViewURL" id="imageViewer">
    <div id="imageViewTXT"></div>
  </div>
  <!-- /Light BOX -->
  <div id="fileUpSession">
    <header>
      <h1>5SeasonLife 課程商品圖像圖庫</h1>
    </header>
    <form id="merchImgUpload" name="merchImgUpload" role="form" action="upload.php" method="post" enctype="multipart/form-data">
      <!-- Our File Inputs -->
      <input type="hidden" id="imgUpDefault" name="imgUpDefault" value="<?php echo $imgUpArray['upDefault']; ?>">
      <?php
      $totalUpNum = count($imgUpArray['upTitle']);
      for($i=0;$i<$totalUpNum;$i++){
        $imgOK=false;
        if(!empty($imgUpArray['upImgPath'][$i]) && file_exists('./uploads/'.$imgUpArray['upImgPath'][$i])){
          $imgOK = true;
        }
        if($i>0 && $i%4==0) {
          echo '<div class="clear-inline"></div>';
        }
      ?>
      <div class="area-upload">
        <div class="wrap-custom-file">
          <input type="file" name="imgUpload[<?php echo $i; ?>]" id="image<?php echo $i; ?>" accept="<?php echo $imgUpArray['upType']; ?>" multiple="multiple" />
          <label for="image<?php echo $i; ?>"<?php echo ($imgOK==true) ? ' class="file-ok" style="background-image:url(\'./uploads/'.$imgUpArray['upImgPath'][$i].'\')"' : ''; ?>>
            <i class="fa<?php echo ($i == $imgUpArray['upDefault']) ? ' fa-check-circle' : ' fa-circle'; ?>" onClick="setDefaultImg(this,'<?php echo $i; ?>');return false;"></i>
            
            <?php if ($imgOK==true) { ?>
              <span>
                <i class="fas fa-window-close funcClose" onClick="clearUpload(this,'<?php echo $i; ?>');return false;"></i>
                <div><?php echo $imgUpArray['upTitle'][$i]; ?></div>
                <i class="fas fa-external-link-square-alt funcView" onClick="imagePopView('<?php echo $i; ?>','<?php echo './uploads/'.$imgUpArray['upImgPath'][$i]; ?>','<?php echo $imgUpArray['upTitle'][$i]; ?>');return false;"></i>
              </span>
            <?php } else { ?>
              <span><?php echo $imgUpArray['upTitle'][$i]; ?></span>
            <?php } ?>
            <i class="fa fa-image funcAdd"></i>
          </label>
        </div>
        <textarea name="imgDes[<?php echo $i; ?>]" id="imgDes[<?php echo $i; ?>]" rows="3" placeholder="描述"<?php echo ($imgOK==true) ? '' :' disabled="disabled"'; ?>><?php if ($imgOK==true) echo $imgUpArray['upDes'][$i]; ?></textarea>
      </div>
      <?php } ?>

      <div class="clear-inline"></div>
      <button type="button" id="upMDimgBut" name="upMDimgBut">UpLoad</button>
    </form>
  </div>
<!-- jQuery -->
<script src="../../pluginFiles/jquery/jquery.min.js"></script>
<script>
$(function () {
  'use strict'

  $('input[type="file"]').each(function(i,e){
  // 開始迴圈
    var $file = $(this),$funcClose,$funcView,$funcDefault,
        $label = $file.next('label'),
        $labelSpan = $label.find('span'),
        labelDefault = $label.find('span').text(),
        $topElm = $file.parent(),
        $textArea = $topElm.next('textarea');
    // 當檔案被選擇後的動作
    $file.on('change', function(event){
      var fileName = $file.val().split( '\\' ).pop(),//取得原始檔案名稱（含副檔名）
          tmppath = URL.createObjectURL(event.target.files[0]),//取得圖檔暫存路徑
          fSize = Math.round(event.target.files[0].size / 1024);//取得準備上傳的檔案大小（Kb）
      //當檔案選擇並成立完成上傳準備時的動作
      if( fileName ){
        $label.addClass('file-ok').css('background-image', 'url(' + tmppath + ')');//圖像貼入File Browser中
        $labelSpan.html('<i class="fas fa-window-close funcClose"></i><div>'+labelDefault+'</div><i class="fas fa-external-link-square-alt funcView"></i>');//產生功能圖檔
        $textArea.prop("disabled",false);//將輸入欄位解除輸入限制
        //-------- 清除上傳 Data --------
        $funcClose = $label.find('span').find('i.funcClose');//選擇器選擇功能圖檔，定位後以做為後續觸發功能對象
        $funcClose.on('click',function() {//點擊功能圖檔清除上傳Data
          if(confirm('確定清除上傳資訊？！')){
            $label.removeClass('file-ok').css('background-image','url("")');//清除圖像以及調整圖檔的Class
            $labelSpan.html(labelDefault);//回復預設狀態
            $file.val('');//清空File Browser中的檔案連結
            $textArea.val('');
            $textArea.prop("disabled","disabled");//將輸入欄位解除輸入限制
            $label.find('.fa-check-circle').removeClass('fa-check-circle').addClass('fa-circle');
            if( $('#imgUpDefault').val()==i) { $('#imgUpDefault').val(''); }
          }
          return false;//點擊時防止file browser 啟動
        });
        //-------- Light BOX 放大圖檔 --------
        $funcView = $label.find('span').find('i.funcView');//選擇器選擇功能圖檔，定位後以做為後續觸發功能對象
        $funcView.on('click',function() {//點擊功能圖檔......
          imagePopView(i,tmppath,labelDefault);
          return false;//點擊時防止file browser 啟動
        });
        //-------- 設為預設值 --------
        $funcDefault = $label.find('i.fa-circle');
        $funcDefault.on('click',function() {
          $topElm.parent().parent().find('.fa-check-circle').removeClass('fa-check-circle').addClass('fa-circle');
          $funcDefault.removeClass('fa-circle').addClass('fa-check-circle');
          $('#imgUpDefault').val(i);
          return false;
        });
      }else{//若檔案選擇無法成立時
        $label.removeClass('file-ok');
        $labelText.text(labelDefault);
      }
    });
  // 結束迴圈
});


  /* ------------------- Light BOX ---------------------------- */
  $("#boxModal").click(function() { 
    $(this).css('display', 'none');
  });
  
  $('#upMDimgBut').click(function(e){ 
        e.preventDefault();
        var forM = $('#merchImgUpload');
        //ajaxPOST(forM, 'up_profile');
        forM.submit();
    });

});
/* ------------------- /Light BOX ---------------------------- */

  function imagePopView(idX, patH='', txT=''){
      $("#boxModal").css('display','block');
      $("#imageViewer").prop('src', patH);
      $("#imageViewTXT").html(txT);
  }

  function setDefaultImg(elM,idX){
    if($(elM).hasClass('fa-check-circle')){ return false; }
    $(elM).parent().parent().parent().parent().find('.fa-check-circle').removeClass('fa-check-circle').addClass('fa-circle');
    $(elM).removeClass('fa-circle').addClass('fa-check-circle');
    $('#imgUpDefault').val(idX);
  }
  function clearUpload(elM,idX){//已上傳檔案的清除動作
    if(confirm('此動作將會刪除這個已經上傳的檔案，確定執行刪除？！')){
      //------ 以下刪除資料庫記錄，以及伺服器中的檔案 --------


      //------------------------------------------------
      var laBspan = $(elM).parent();
      var laB = laBspan.parent();
      var laBwrap = laB.parent();
      var laBtext = laBwrap.parent().find('textarea');
      var laBfile = laBwrap.find('input:file');
      laB.removeClass('file-ok').css('background-image','url("")');//清除圖像以及調整圖檔的Class
      laBspan.html(laBspan.find('div').html());//回復預設狀態
      laBfile.val('');//清空File Browser中的檔案連結
      laBtext.val('');
      laBtext.prop("disabled","disabled");//將輸入欄位解除輸入限制
      laB.find('.fa-check-circle').removeClass('fa-check-circle').addClass('fa-circle');
      if( $('#imgUpDefault').val()==idX) { $('#imgUpDefault').val(''); }
    }
  }
</script>
</body>
</html>
