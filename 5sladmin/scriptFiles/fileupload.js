var cropImage64 = '';

$(function () {
  var extARRpath = {
    rtf : '../imageFiles/uploadBG_text.jpg',
    txt : '../imageFiles/uploadBG_text.jpg',
    doc : '../imageFiles/uploadBG_word.jpg',
    docx : '../imageFiles/uploadBG_word.jpg',
    xls : '../imageFiles/uploadBG_excel.jpg',
    xlsx : '../imageFiles/uploadBG_excel.jpg',
    pdf : '../imageFiles/uploadBG_pdf.jpg'
  };


  var amodal = $('#avatarModal');
  var currIMG = $('#avatarImage');
  var cropUpImg = $('#cropUploadImage, #cropUploadImage2');
  var mainImgCheck = $('#mainimgchk');
  var cropImgTrash = $('.cropImgTrash');
  var cropper = null;
  var upFileName = '';
  var activeCropInput = null; // 記錄當前是哪一個 input 觸發裁切
  var $activeArea = null; // 記錄當前操作的 .image_area 區塊

   // 輔助函式：取得當前區塊對應的 Bootstrap 5 Modal 實例
  function getCurrentModalInstance($area) {
    var modalEl = $area ? $area.find('.avatarModal')[0] : document.querySelector('.avatarModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
      return bootstrap.Modal.getOrCreateInstance(modalEl);
    }
    return null;
  }
  // 1. 選擇圖片後讀取並開啟對應的 Bootstrap 5 裁切視窗
  cropUpImg.on('change', function(event){
    var $input = $(this);
    $activeArea = $input.closest('.image_area'); // 鎖定所屬的 .image_area 容器
    var files = event.target.files;

    if (files && files.length > 0){
      var file = files[0];
      upFileName = file.name;

      var done = function (url) {
        var $targetImg = $activeArea.length ? $activeArea.find('.cropImageSample') : $('.cropImageSample').first();
        if (!$targetImg.length) {
          console.error('找不到 .cropImageSample 元素，請檢查 include_avatarmodal.php！');
          return;
        }
        $targetImg.attr('src', url);

        var modalInst = getCurrentModalInstance($activeArea);
        if (modalInst) {
          modalInst.show();
        } else {
          console.error('找不到 .avatarModal 彈出視窗！');
        }
      };

      var reader = new FileReader();
      reader.onload = function (e) {
        done(e.target.result);
      };
      reader.readAsDataURL(file);
    }
  });

  // 2. Modal 完全展開後初始化 Cropper.js（改用事件委派支援多個 Modal）
  $(document).on('shown.bs.modal', '.avatarModal', function() {
    var $modal = $(this);
    var $area = $modal.closest('.image_area');
    var targetImg = $modal.find('.cropImageSample')[0];
    if (!targetImg) return;

    var $input = $area.length ? $area.find('input[type="file"]') : cropUpImg.first();
    var imgRatio = parseFloat($input.attr('crop-width')) / parseFloat($input.attr('crop-height'));

    cropper = new Cropper(targetImg, {
      aspectRatio: imgRatio,
      viewMode: 2,
      preview: $modal.find('.preview')[0] // 精準指定當前 Modal 內的預覽區
    });
  }).on('hidden.bs.modal', '.avatarModal', function() {
    if (cropper) {
      cropper.destroy();
      cropper = null;
    }
    if ($activeArea) {
      $activeArea.find('input[type="file"]').val('');
    }
  });

  // 3. 按下「裁切並上傳」按鈕（改用 .crop_btn_submit 類別）
  $(document).on('click', '.crop_btn_submit', function(){
    if (!cropper) return;
    var $modal = $(this).closest('.avatarModal');
    var $area = $modal.closest('.image_area');
    var $input = $area.length ? $area.find('input[type="file"]') : cropUpImg.first();

    var canvas = cropper.getCroppedCanvas({
      width: parseInt($input.attr('crop-width')) || 400,
      height: parseInt($input.attr('crop-height')) || 400
    });

    canvas.toBlob(function(blob) {
      var reader = new FileReader();
      reader.readAsDataURL(blob); 
      reader.onloadend = function() {
        cropImage64 = reader.result;
        if ($('#main_crop_area').length) {
          $('#main_crop_area').data('cropImage64', cropImage64);
        }
        // 關閉當前 BS5 Modal
        var modalEl = $modal[0];
        if (modalEl && typeof bootstrap !== 'undefined') {
          bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        }
        // 更新對應區塊的縮圖與刪除鈕
        var $currIMG = $area.length ? $area.find('img.imgEDicon') : $('#avatarImage');
        var $trashIcon = $area.length ? $area.find('.cropImgTrash') : $('.cropImgTrash');
        
        $currIMG.prop('src', cropImage64);
        $('#mainimgchk').val(upFileName);
        $trashIcon.removeClass('hide d-none').addClass('show');
        
        if (typeof doSerializeCheck === 'function') {
          doSerializeCheck();
        }
      };
    }, 'image/jpeg', 0.75);
  });

  // 4. 點擊刪除代表圖垃圾桶圖示
  cropImgTrash.on('click', function(e){
    if(confirm('執行編輯功能更新後，此圖像將自伺服器中移除，確認移除這個圖像？！')){
      var nopicImg = "../imageFiles/nopic_user_200x200.jpg";
      var imgRatio = (parseFloat(cropUpImg.attr('crop-width')) / parseFloat(cropUpImg.attr('crop-height'))).toFixed(2);
      switch(imgRatio){
        case '1.00': 
          if(currIMG.hasClass("profile-user-img")){ nopicImg = "../uploadFiles/imageFiles/nopic_user_128x128.jpg"; }
          else { nopicImg = "../uploadFiles/imageFiles/nopic_200x200.jpg"; }
        break;
        case '1.33': nopicImg = "../uploadFiles/imageFiles/nopic_400x300.jpg"; break;
        case '1.50': nopicImg = "../uploadFiles/imageFiles/nopic_300x200.jpg"; break;
        case '1.78': nopicImg = "../uploadFiles/imageFiles/nopic_320x180.jpg"; break;
      }
      cropUpImg.val('');
      currIMG.prop('src', nopicImg);
      $('.overtext').html('新增代表圖');
      cropImage64 = '@DELETE@';
      if ($('#main_crop_area').length) {
        $('#main_crop_area').data('cropImage64', '@DELETE@');
      }
      $(this).removeClass('show').addClass('hide d-none');
      mainImgCheck.val('');
      if (typeof doSerializeCheck === 'function') {
        doSerializeCheck();
      }
    }
  }); 

  // 5. 多圖/附件分頁檔案選擇與預覽邏輯
  $('.tab-content input[type="file"]').each(function(i, e){
    var file = $(this), funcClose, funcView,
        label = file.next('label'),
        labelSpan = label.find('span'),
        labelRadio = label.find('input:radio'),
        labelDefault = label.find('span').text();
    if(labelDefault == ''){ labelDefault = 'Index: ' + i; }

    var _URL = window.URL || window.webkitURL;
    file.on('change', function(event){
      var fileName = file.val().split('\\').pop(),
          ev = event.target.files[0];

      if(fileName && ev){
        var tmppath = _URL.createObjectURL(ev),
            fSplit = ev.name.split('.'),
            fExt = fSplit.pop().toLowerCase(),
            fSize = Math.round(ev.size / 1024),
            showPopup = tmppath;

        var cImg = new Image();
        cImg.onload = function() {
          if(fExt != 'jpg' && fExt != 'jpeg'){
            showPopup = extARRpath[fExt] || '../imageFiles/uploadBG_text.jpg';
          } else {
            if(this.width < 992 || this.height < 558){
              if (window.parent && window.parent.toastr) {
                window.parent.toastr['error']('圖像長寬尺寸至少一邊小於 992*558，請選擇較大的圖像上傳！！');
              }
              return false;
            }
            if(fSize > 600 || fSize < 30){
              if (window.parent && window.parent.toastr) {
                window.parent.toastr['error']('圖像大小超過上限 600Kb，請選擇較小的檔案！！');
              }
              return false;
            }
          }
          label.addClass('file-ok').css('background-image', 'url(' + showPopup + ')').attr('fileext', fExt);
          // [變更4] 動態圖示改用 Bootstrap Icons (bi-x-square-fill 與 bi-box-arrow-up-right)
          labelSpan.html('<i class="bi bi-x-square-fill funcClose"></i><div>' + labelDefault + '</div><i class="bi bi-box-arrow-up-right funcView"></i>');
          label.find("input[id^='imgCheck_']").val(Date.now());
          labelRadio.show();
          if(labelRadio.is(':checked') == true){ $('#defaultPreview').prop('src', showPopup); }

          // 清除上傳 Data
          funcClose = label.find('span').find('i.funcClose');
          if (typeof doSerializeCheck === 'function') doSerializeCheck();
          funcClose.on('click', function() {
            if(confirm('確定清除上傳資訊？！')){
              label.removeClass('file-ok').css('background-image', 'url("../imageFiles/nopic_240x180.jpg")').attr('fileext', '');
              if(labelRadio.is(':checked') == true){ $('#defaultPreview').prop('src', '../imageFiles/nopic_400x300.jpg'); }
              labelRadio.prop('checked', false).hide();
              labelSpan.html(labelDefault);
              file.val('');
              label.find("input[id^='imgCheck_']").val('');
              if (typeof doSerializeCheck === 'function') doSerializeCheck();
              return false;
            }
            return false;
          });

          // Light BOX 放大圖檔
          funcView = label.find('span').find('i.funcView');
          funcView.on('click', function() {
            if(fExt == 'jpg' || fExt == 'jpeg'){
              imagePopView(i, label.css("background-image").replace(/^url\(['"](.+)['"]\)/, '$1'), labelDefault);
            }
            return false;
          });
        };
        // 若非圖片檔案（如 pdf/doc），直接觸發 onload 邏輯顯示對應預設圖示
        if (fExt != 'jpg' && fExt != 'jpeg' && fExt != 'png' && fExt != 'gif') {
          cImg.onload();
        } else {
          cImg.src = _URL.createObjectURL(ev);
        }
      } else {
        label.removeClass('file-ok');
        // [修正] 修正原本寫成未定義變數 labelText 的 Bug
        labelSpan.text(labelDefault);
      }
    });
  });

  $("#boxModal").on('click', function() { 
    $(this).css('display', 'none');
  });

  $('#upMDimgBut').on('click', function(e){ 
    e.preventDefault();
    $('#merchImgUpload').submit();
  });

  $("form[data-formmode=photoUpload]").find("input[type='radio']").on('click', function(){
    var currImg = $(this).parent().css('background-image').replace(/^url\(['"](.+)['"]\)/, '$1');
    $('#defaultPreview').prop('src', currImg);
  });
});

function imagePopView(idX, patH='', txT=''){
  $("#boxModal").css('display', 'block');
  $("#imageViewer").prop('src', patH);
  $("#imageViewTXT").html(txT);
}

function clearUpload(elM, idX, fPath = '', accE = '') { // 已上傳檔案的清除動作
  if (idX > 0 && $("#currIdx").val() == idX) {
    if (window.parent && window.parent.toastr) {
      window.parent.toastr['error']('待刪除的檔案為預設圖檔，請先轉移預設並儲存後再進行刪除！！');
    }
    return false;
  }
  if (confirm('此動作將會刪除這個已經上傳的檔案，確定執行刪除？！')) {
    // ------ 以下刪除資料庫記錄，以及伺服器中的檔案 --------
    if (fPath == '') {
      if (window.parent && window.parent.toastr) {
        window.parent.toastr['error']('遺失待刪除的檔案路徑，刪除失敗！！');
      }
      return false;
    }
    $.ajax({
      type: 'post',
      url: '../funcFiles/file_act_func.php',
      data: {
        'arrDatA': fPath, 
        'formAcce': 'func_file_deleteByPath',
        'token': getTokenVal()
      },
      dataType: 'json',
      async: false, 
      success: function (data) {
        if (data['RTN'] == 1) {
          var laBspan = $(elM).parent();
          var laB = laBspan.parent();
          var laBwrap = laB.parent();
          var laBfile = laBwrap.find('input:file');
          var laBcheck = laB.find("input[id^='imgCheck_']");
          
          laB.removeClass('file-ok').css('background-image', ''); // 清除圖像以及調整圖檔的Class
          laBspan.html(laBspan.find('div').html()); // 回復預設狀態
          laBfile.val(''); // 清空File Browser中的檔案連結
          laBcheck.val('');
          laB.find('input:radio').prop('checked', false).hide();
          
          if (typeof doSerialize === 'function') {
            doSerialize();
          }
          
          if (accE == 'photo') { // 當為圖像處理時，假設有預設圖預覽功能下，進行預覽框清除
            if ($('#currIdx').val() == idX) { 
              $('#currIdx').val(''); 
              $('#defaultPreview').prop('src', '../imageFiles/nopic_400x300.jpg');
            }
          }
          
          // [修正] 將原本拼錯的 'actived' 改為正確的 'active'
          if (window.parent && window.parent.$) {
            window.parent.$('#topBut_save').removeClass('active');
          }
          if (window.parent && window.parent.toastr) {
            window.parent.toastr['success']('檔案刪除成功！！');
          }
        } else {
          if (window.parent && window.parent.toastr) {
            window.parent.toastr['error']('檔案刪除失敗，請重新操作或聯絡管理人員！！');
          }
        }
      },
      error: function (data) {
        console.log(data);
        if (window.parent && window.parent.toastr) {
          window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
        }
      }
    });
  }
}
  