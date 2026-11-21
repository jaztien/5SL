// JavaScript Document
var cropImage64='';

$(function () {
    //--------------  File Upload ------------------
  var extARRpath = {
    rtf : '../imageFiles/uploadBG_text.jpg',
    txt : '../imageFiles/uploadBG_text.jpg',
    doc : '../imageFiles/uploadBG_word.jpg',
    docx : '../imageFiles/uploadBG_word.jpg',
    xls : '../imageFiles/uploadBG_excel.jpg',
    xlsx : '../imageFiles/uploadBG_excel.jpg',
    pdf : '../imageFiles/uploadBG_pdf.jpg'
  };
  ////////////////////////////////////////////////////////////////
  // ---------------- 單獨圖像（頭像）上傳及裁切操作 --------------- //
  ////////////////////////////////////////////////////////////////
  var amodal = $('#avatarModal');
  var currIMG = $('#avatarImage');
  var cropUpImg = $('#cropUploadImage,#cropUploadImage2');
  var mainImgCheck = $('#mainimgchk');
  var cropImgTrash = $('.cropImgTrash');
  //var aimage = $('.cropImageSample');
  var aimage = document.getElementById('cropImageSample');
  var cropper;
  var upFileName='';



  cropUpImg.change(function(event){
    var files = event.target.files;
    upFileName = files[0].name;
    var done = function (url) {
        aimage.src = url;
        amodal.modal('toggle');
    };
    if (files && files.length > 0){
          reader = new FileReader();
          reader.onload = function (event) {
              done(reader.result);
          };
          reader.readAsDataURL(files[0]);
    }
  });
  amodal.on('shown.bs.modal', function() {
    var imgRatio = parseFloat(cropUpImg.attr('crop-width') / cropUpImg.attr('crop-height'));
    cropper = new Cropper(aimage, {
      aspectRatio: imgRatio,
      viewMode: 2,
      preview: '.preview'
    });
  }).on('hidden.bs.modal', function() {
    cropper.destroy();
    cropper = null;
  });
  $("#crop").click(function(){
    canvas = cropper.getCroppedCanvas({
        width: cropUpImg.attr('crop-width'),//800
        height: cropUpImg.attr('crop-height'),//600
    });
    canvas.toBlob(function(blob) {
      var reader = new FileReader();
      reader.readAsDataURL(blob); 
      reader.onloadend = function() {
        cropImage64 = reader.result;
        amodal.modal('hide');
        currIMG.prop('src', cropImage64);
        mainImgCheck.val(upFileName);//寫入隱藏表單，做為啟動編輯按鈕的比對依據
        cropImgTrash.removeClass('hide').addClass('show');
        //$('.image_area >i').css('display','block');
        doSerializeCheck();
      }
    }, 'image/jpeg', 0.75);//將預設轉存PNG格式改為JPG（壓縮檔案更小）
  });

  cropImgTrash.on('click', function(e){
    if(confirm('執行編輯功能更新後，此圖像將自伺服器中移除，確認移除這個圖像？！')){
      var nopicImg;
      var imgRatio = parseFloat(cropUpImg.attr('crop-width') / cropUpImg.attr('crop-height')).toFixed(2);
      switch(imgRatio){
        case '1.00': 
          if(currIMG.hasClass("profile-user-img")){ nopicImg = "../uploadFiles/imageFiles/nopic_user_128x128.jpg"; }
          else { nopicImg = "../uploadFiles/imageFiles/nopic_200x200.jpg";  }
        break;
        case '1.33': nopicImg = "../uploadFiles/imageFiles/nopic_400x300.jpg"; break;
        case '1.50': nopicImg = "../uploadFiles/imageFiles/nopic_300x200.jpg"; break;
        case '1.78': nopicImg = "../uploadFiles/imageFiles/nopic_320x180.jpg"; break;
      }
      cropUpImg.val('');
      currIMG.prop('src', nopicImg);
      $('.overtext').html('新增代表圖');
      cropImage64 = '@DELETE@';
      $(this).removeClass('show').addClass('hide');
      mainImgCheck.val('');//寫入隱藏表單，做為啟動編輯按鈕的比對依據
      doSerializeCheck();
    }
  }); 



  
////////////////////////////////////////////////////////////////
// --------------- 圖庫圖像上傳操作（帶預設圖像操作） ------------- //
////////////////////////////////////////////////////////////////
    $('.tab-content input[type="file"]').each(function(i,e){
      // 開始迴圈
      var file = $(this),funcClose,funcView,funcDefault,
          label = file.next('label'),
          labelSpan = label.find('span'),
          labelRadio = label.find('input:radio'),
          labelDefault = label.find('span').text(),
          topElm = file.parent();
          if(labelDefault==''){ labelDefault = 'Index: '+i; }
      // 當檔案被選擇後的動作
      var _URL = window.URL || window.webkitURL;
      file.on('change', function(event){
        var fileName = file.val().split( '\\' ).pop(),//取得原始檔案名稱（含副檔名）
            ev = event.target.files[0],
            tmppath = URL.createObjectURL(ev),//取得圖檔暫存路徑
            fSplit = ev.name.split('.'),//
            fName = fSplit[0],//
            fExt = fSplit[1],//
            fSize = Math.round(ev.size / 1024),//取得準備上傳的檔案大小（Kb）
            showPopup = tmppath;
        //當檔案選擇並成立完成上傳準備時的動作
        if( fileName ){
          var cFile,cImg;
          if ((cFile = this.files[0])) {
            cImg = new Image();
            cImg.onload = function() {
              if(fExt!='jpg' && fExt!='jpeg'){
                showPopup = extARRpath[fExt];
              } else {
                if(this.width<992 || this.height<558){
                  window.parent.toastr['error']('圖像長寬尺寸至少一邊小於 992*558，請選擇較大的圖像上傳！！');  
                  return false;
                }
                if(fSize > 600 || fSize < 30){
                  window.parent.toastr['error']('圖像大小超過上限 600Kb，請選擇較小的檔案！！');  
                  return false;
                }
              }
              label.addClass('file-ok').css('background-image', 'url(' + showPopup + ')').attr('fileext',fExt);//圖像貼入File Browser中
              labelSpan.html('<i class="fas fa-window-close funcClose"></i><div>'+labelDefault+'</div><i class="fas fa-external-link-square-alt funcView"></i>');//產生功能圖檔
              label.find("input[id^='imgCheck_']").val($.now());//賦予圖像框檢查亂數，以備表單異動時的比對
              labelRadio.show();//貼上檔案時取消勾選框的隱藏狀態
              if(labelRadio.is(':checked')==true){ $('#defaultPreview').prop('src',showPopup); }//當為預設主圖時，同時更改左上方主圖框中圖像

              //-------- 清除上傳 Data --------
              funcClose = label.find('span').find('i.funcClose');//選擇器選擇功能圖檔，定位後以做為後續觸發功能對象
              doSerializeCheck();
              funcClose.on('click',function() {//點擊功能圖檔清除上傳Data
                if(confirm('確定清除上傳資訊？！')){
                  label.removeClass('file-ok').css('background-image','url("../imageFiles/nopic_240x180.jpg")').attr('fileext','');//清除圖像以及調整圖檔的Class
                  if(labelRadio.is(':checked')==true){ $('#defaultPreview').prop('src','../imageFiles/nopic_400x300.jpg'); }//當為預設主圖時，同時更改左上方主圖框中圖像
                  labelRadio.prop('checked', false).hide();//移除檔案時啟動勾選框的隱藏狀態
                  labelSpan.html(labelDefault);//回復預設狀態
                  file.val('');//清空File Browser中的檔案連結
                  label.find("input[id^='imgCheck_']").val('');
                  doSerializeCheck();return false;
                }
                return false;//點擊時防止file browser 啟動
              });
              //-------- Light BOX 放大圖檔 --------
              funcView = label.find('span').find('i.funcView');//選擇器選擇功能圖檔，定位後以做為後續觸發功能對象
              funcView.on('click',function() {//點擊功能圖檔......
                if(fExt=='jpg'||fExt=='jpeg'){
                  //alert(label.css("background-image").replace(/^url\(['"](.+)['"]\)/, '$1'));
                  imagePopView(i,label.css("background-image").replace(/^url\(['"](.+)['"]\)/, '$1'),labelDefault);
                }
                return false;//點擊時防止file browser 啟動
              });
            };
            cImg.src = _URL.createObjectURL(cFile);
          }
        }else{//若檔案選擇無法成立時
          label.removeClass('file-ok');
          labelText.text(labelDefault);
        }
      });// 結束迴圈        
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
    $("form[data-formmode=photoUpload]").find("input[type='radio']").click(function(){
      //var currCheck = $("#currIdx").val();
      //alert($(this).val()+' / '+currCheck);
      var currImg = $(this).parent().css('background-image').replace(/^url\(['"](.+)['"]\)/, '$1');
      $('#defaultPreview').prop('src',currImg);
      //$("#currIdx").val($(this).val());
    });
});




function imagePopView(idX, patH='', txT=''){
  $("#boxModal").css('display','block');
  $("#imageViewer").prop('src', patH);
  $("#imageViewTXT").html(txT);
}

function clearUpload(elM,idX,fPath='',accE=''){//已上傳檔案的清除動作
  if(idX>0 && $("#currIdx").val()==idX){
    window.parent.toastr['error']('待刪除的檔案為預設圖檔，請先轉移預設並儲存後再進行刪除！！');
    return false;
  }
  //alert(fPath);return false;
  if(confirm('此動作將會刪除這個已經上傳的檔案，確定執行刪除？！')){
    //------ 以下刪除資料庫記錄，以及伺服器中的檔案 --------
    if(fPath==''){
      window.parent.toastr['error']('遺失待刪除的檔案路徑，刪除失敗！！');
      return false;
    }
    $.ajax({
      type: 'post',
      url: '../funcFiles/file_act_func.php',
      data: {
        'arrDatA': fPath, 
        'formAcce':'func_file_deleteByPath',
        'token': getTokenVal()
      },
      dataType: 'json',
      async: false, 
      success: function (data) {
        if(data['RTN']==1){
          var laBspan = $(elM).parent();
          var laB = laBspan.parent();
          var laBwrap = laB.parent();
          var laBfile = laBwrap.find('input:file');
          var laBcheck = laB.find("input[id^='imgCheck_']");
          laB.removeClass('file-ok').css('background-image','');//清除圖像以及調整圖檔的Class
          laBspan.html(laBspan.find('div').html());//回復預設狀態
          laBfile.val('');//清空File Browser中的檔案連結
          laBcheck.val('');
          laB.find('input:radio').prop('checked', false).hide();
          doSerialize();
          if(accE=='photo'){//當為圖像處理時，假設有預設圖預覽功能下，進行預覽框清除
            if( $('#currIdx').val()==idX) { 
              $('#currIdx').val(''); 
              $('#defaultPreview').prop('src','../imageFiles/nopic_400x300.jpg');
            }
          }
          window.parent.$('#topBut_save').removeClass('actived');
          window.parent.toastr['success']('檔案刪除成功！！');
        } else {
          window.parent.toastr['error']('檔案刪除失敗，請重新操作或聯絡管理人員！！');
        }
      },
        error: function (data) {
          console.log(data);
          window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
      }
    });

  }
}


  