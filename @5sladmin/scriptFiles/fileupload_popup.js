// JavaScript Document
var cropImage64_2='';

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
  var amodal2 = $('#avatarModal2');
  var currIMG2 = $('#avatarImage2');
  var cropUpImg2 = $('#cropUploadImage2');
  var mainImgCheck2 = $('#mainimgchk2');
  var cropImgTrash2 = $('.cropImgTrash2');
  //var aimage = $('.cropImageSample');
  var aimage2 = document.getElementById('cropImageSample2');
  var cropper2;
  var upFileName2='';



  cropUpImg2.change(function(event){
    var files2 = event.target.files;
    upFileName2 = files2[0].name;
    var done = function (url) {
        aimage2.src = url;
        amodal2.modal('toggle');
    };
    if (files2 && files2.length > 0){
          reader2 = new FileReader();
          reader2.onload = function (event) {
              done(reader2.result);
          };
          reader2.readAsDataURL(files2[0]);
    }
  });
  amodal2.on('shown.bs.modal', function() {
    var imgRatio2 = parseFloat(cropUpImg2.attr('crop-width') / cropUpImg2.attr('crop-height'));
    cropper2 = new Cropper(aimage2, {
      aspectRatio: imgRatio2,
      viewMode: 2,
      preview: '.preview2'
    });
  }).on('hidden.bs.modal', function() {
    cropper2.destroy();
    cropper2 = null;
  });
  $("#crop2").click(function(){
    canvas = cropper2.getCroppedCanvas({
        width: cropUpImg2.attr('crop-width'),//800
        height: cropUpImg2.attr('crop-height'),//600
    });
    canvas.toBlob(function(blob) {
      var reader2 = new FileReader();
      reader2.readAsDataURL(blob); 
      reader2.onloadend = function() {
        cropImage64_2 = reader2.result;
        amodal2.modal('hide');
        currIMG2.prop('src', cropImage64_2);
        mainImgCheck2.val(upFileName2);//寫入隱藏表單，做為啟動編輯按鈕的比對依據
        cropImgTrash2.removeClass('hide').addClass('show');
        //$('.image_area >i').css('display','block');
        doSerializeCheck();
      }
    }, 'image/jpeg', 0.75);//將預設轉存PNG格式改為JPG（壓縮檔案更小）
  });

  cropImgTrash2.on('click', function(e){
    if(confirm('執行編輯功能更新後，此圖像將自伺服器中移除，確認移除這個圖像？！')){
      var nopicImg2;
      var imgRatio2 = parseFloat(cropUpImg2.attr('crop-width') / cropUpImg2.attr('crop-height')).toFixed(2);
      switch(imgRatio2){
        case '1.00': 
          if(currIMG2.hasClass("profile-user-img")){ nopicImg2 = "../uploadFiles/imageFiles/nopic_user_128x128.jpg"; }
          else { nopicImg2 = "../uploadFiles/imageFiles/nopic_200x200.jpg";  }
        break;
        case '1.33': nopicImg2 = "../uploadFiles/imageFiles/nopic_400x300.jpg"; break;
        case '1.50': nopicImg2 = "../uploadFiles/imageFiles/nopic_300x200.jpg"; break;
        case '1.78': nopicImg2 = "../uploadFiles/imageFiles/nopic_320x180.jpg"; break;
      }
      cropUpImg2.val('');
      currIMG2.prop('src', nopicImg2);
      $('.overtext').html('新增代表圖');
      cropImage64_2 = '@DELETE@';
      $(this).removeClass('show').addClass('hide');
      mainImgCheck2.val('');//寫入隱藏表單，做為啟動編輯按鈕的比對依據
      doSerializeCheck();
    }
  }); 

});

  