(function($) {
  // === 1. 快取與宣告區域（限定在 IIFE 範圍內，拒絕全域污染） ===
  var amodal2 = $('#avatarModal2');
  var currIMG2 = $('#avatarImage2');
  var cropUpImg2 = $('#cropUploadImage2');
  var mainImgCheck2 = $('#mainimgchk2');
  var cropImgTrash2 = $('.cropImgTrash2');
  var aimage2 = document.getElementById('cropImageSample2');
  var cropper2 = null;
  var upFileName2 = '';
  // 💡 修正原本漏掉 var 宣告的變數，防止變成全域變數
  var cropImage64_2 = ''; 

  // === 2. 事件解綁防禦 (off) ===
  // 核心：每次此 JS 檔案被 load() 重複執行時，先洗掉之前的舊監聽器，避免疊加發送
  cropUpImg2.off('change');
  amodal2.off('shown.bs.modal hidden.bs.modal');
  $(document).off('click', '#crop2');
  cropImgTrash2.off('click');

  // === 3. 處理圖片上傳選擇 ===
  cropUpImg2.on('change', function(event) {
    var files = event.target.files;
    if (!files || files.length === 0) return;

    upFileName2 = files[0].name;
    var done = function(url) {
      if (aimage2) {
        aimage2.src = url;
        amodal2.modal('toggle');
      }
    };

    var reader2 = new FileReader(); // 💡 加上 var
    reader2.onload = function(e) {
      done(e.target.result);
    };
    reader2.readAsDataURL(files[0]);
  });

  // === 4. Modal 彈出視窗事件 (Cropper 生命週期管控) ===
  amodal2.on('shown.bs.modal', function() {
    var width = parseFloat(cropUpImg2.attr('crop-width')) || 1;
    var height = parseFloat(cropUpImg2.attr('crop-height')) || 1;
    var imgRatio = width / height;

    // 💡 雙重防禦：建立新實例前，先叫舊的徹底瓦解釋放記憶體
    if (cropper2 && typeof cropper2.destroy === 'function') {
      cropper2.destroy();
    }

    cropper2 = new Cropper(aimage2, {
      aspectRatio: imgRatio,
      viewMode: 2,
      preview: '.preview'
    });
  }).on('hidden.bs.modal', function() {
    // 💡 關閉彈出視窗時確實銷毀，釋放 Canvas 記憶體快取
    if (cropper2 && typeof cropper2.destroy === 'function') {
      cropper2.destroy();
    }
    cropper2 = null;
  });

  // === 5. 確認裁切按鈕 (改為 document 事件代理 + 防禦) ===
  $(document).on('click', '#crop2', function() {
    if (!cropper2) return;

    var targetWidth = parseInt(cropUpImg2.attr('crop-width'), 10) || 800;
    var targetHeight = parseInt(cropUpImg2.attr('crop-height'), 10) || 600;

    var canvas = cropper2.getCroppedCanvas({ // 💡 加上 var
      width: targetWidth,
      height: targetHeight
    });

    if (canvas) {
      canvas.toBlob(function(blob) {
        if (!blob) return;
        var reader2 = new FileReader(); // 💡 加上 var
        reader2.readAsDataURL(blob); 
        reader2.onloadend = function() {
          cropImage64_2 = reader2.result;
          amodal2.modal('hide');
          
          currIMG2.prop('src', cropImage64_2);
          mainImgCheck2.val(upFileName2);
          cropImgTrash2.removeClass('hide').addClass('show');
          
          if (typeof doSerializeCheck === 'function') {
            doSerializeCheck();
          }
        };
      }, 'image/jpeg', 0.75);
    }
  });

  // === 6. 垃圾桶移除圖像 ===
  cropImgTrash2.on('click', function(e) {
    e.preventDefault();
    if (confirm('執行編輯功能更新後，此圖像將自伺服器中移除，確認移除這個圖像？！')) {
      var nopicImg = "../uploadFiles/imageFiles/nopic_200x200.jpg"; // 預設值
      var width = parseFloat(cropUpImg2.attr('crop-width')) || 1;
      var height = parseFloat(cropUpImg2.attr('crop-height')) || 1;
      var imgRatio = (width / height).toFixed(2);

      switch(imgRatio) {
        case '1.00': 
          nopicImg = currIMG2.hasClass("profile-user-img") 
            ? "../uploadFiles/imageFiles/nopic_user_128x128.jpg" 
            : "../uploadFiles/imageFiles/nopic_200x200.jpg";
          break;
        case '1.33': nopicImg = "../uploadFiles/imageFiles/nopic_400x300.jpg"; break;
        case '1.50': nopicImg = "../uploadFiles/imageFiles/nopic_300x200.jpg"; break;
        case '1.78': nopicImg = "../uploadFiles/imageFiles/nopic_320x180.jpg"; break;
      }

      cropUpImg2.val('');
      currIMG2.prop('src', nopicImg);
      $('.overtext').html('新增代表圖');
      cropImage64_2 = '@DELETE@';
      
      $(this).removeClass('show').addClass('hide');
      mainImgCheck2.val('');
      
      if (typeof doSerializeCheck === 'function') {
        doSerializeCheck();
      }
    }
  });

})(jQuery);
