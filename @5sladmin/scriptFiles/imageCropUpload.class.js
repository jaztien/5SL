/**
 * 放置於前端主網頁的 Cropper 多組件共用管控腳本 (萬用Class版)
 */
(function($) {

    var extARRpath = {
        rtf : '../imageFiles/uploadBG_text.jpg',
        txt : '../imageFiles/uploadBG_text.jpg',
        doc : '../imageFiles/uploadBG_word.jpg',
        docx : '../imageFiles/uploadBG_word.jpg',
        xls : '../imageFiles/uploadBG_excel.jpg',
        xlsx : '../imageFiles/uploadBG_excel.jpg',
        pdf : '../imageFiles/uploadBG_pdf.jpg'
    };

    // =========================================================================
    // 💡 核心機制：利用 jQuery 的 data() 將各自的 cropper 實例與資料鎖在各自的 HTML 容器上
    // =========================================================================

    // 1. 處理圖片選取改變事件
    $(document).on('change', '.cropUploadImage', function(event) {
        var files = event.target.files;
        if (!files || files.length === 0) return;

        var $thisInput = $(this);
        var $group = $thisInput.closest('.crop-group'); // 🎯 鎖定當前的群組容器
        var file = files[0];

        // ＝===========================================================
        // 🛡️ 條件 A：檢查檔案容量大小 (Size)
        // ＝===========================================================
        var maxFileSize = 4 * 1024 * 1024; // 限制最大 5MB (可自行調整)
        if (file.size > maxFileSize) {
            alert('錯誤：上傳的檔案大小不得超過 4MB！');
            $thisInput.val(''); // 清空選取，終止操作
            return;
        }

        var $aimage = $group.find('.cropImageSample');
        var $amodal = $group.find('.avatarModal');

        var done = function(url) {
            if ($aimage.length > 0) {
                $aimage.attr('src', url);
                $amodal.modal('show');
            }
        };

        // ＝===========================================================
        // 🛡️ 條件 B：檢查圖片原始像素長寬 (Dimensions)
        // ＝===========================================================
        var reader = new FileReader();
        reader.onload = function(e) {
            var imgUrl = e.target.result;

            // 建立一個暫時的隱形 Image 物件來讀取原始解析度
            var img = new Image();
            img.onload = function() {
                var originalWidth = this.width;   // 原始圖片寬度
                var originalHeight = this.height; // 原始圖片高度

                // 取得這個群組設定的目標裁切尺寸限制 (例如：800x600)
                var minWidth = parseInt($thisInput.attr('crop-width'), 10) || 800;
                var minHeight = parseInt($thisInput.attr('crop-height'), 10) || 600;

                // 判斷：如果原圖比要求的裁切尺寸還小，直接終止操作
                if (originalWidth < minWidth || originalHeight < minHeight) {
                    alert('錯誤：選取的圖片解析度過低（' + originalWidth + 'x' + originalHeight + '）。\n請改上傳大於或等於 ' + minWidth + 'x' + minHeight + ' 像素的圖片，以確保裁切後清晰度！');
                    $thisInput.val(''); // 清空選取，終止操作
                    return;
                }

                // 檢查完全通過！暫存檔名並開啟 Modal
                $group.data('upFileName', file.name);
                done(imgUrl);
            };
            img.src = imgUrl; // 觸發 img.onload
        };
        reader.readAsDataURL(file);
    });

    // 2. 監聽各自 Modal 彈出視窗的生命週期
    $(document).on('shown.bs.modal', '.avatarModal', function() {
        var $amodal = $(this);
        var $group = $amodal.closest('.crop-group'); // 🎯 鎖定當前的群組容器
        var $cropUpImg = $group.find('.cropUploadImage');
        var aimage = $group.find('.cropImageSample')[0]; // 取得原生 DOM 節點
        
        var width = parseFloat($cropUpImg.attr('crop-width')) || 1;
        var height = parseFloat($cropUpImg.attr('crop-height')) || 1;
        var imgRatio = width / height;

        // 檢查該群組是否已有舊的 cropper 實例，有的話先銷毀
        var oldCropper = $group.data('cropperInstance');
        if (oldCropper && typeof oldCropper.destroy === 'function') {
        oldCropper.destroy();
        }

        // 建立新實例，並回存到該群組的 data 中
        var previewSelector = $group.find('.preview')[0] ? $group.find('.preview') : '.preview';
        var cropper = new Cropper(aimage, {
        aspectRatio: imgRatio,
        viewMode: 2,
        preview: previewSelector
        });
        
        $group.data('cropperInstance', cropper);
    });

    $(document).on('hidden.bs.modal', '.avatarModal', function() {
        var $amodal = $(this);
        var $group = $amodal.closest('.crop-group');
        
        var cropper = $group.data('cropperInstance');
        if (cropper && typeof cropper.destroy === 'function') {
        cropper.destroy();
        }
        $group.data('cropperInstance', null);
    });

    // 3. 確認裁切按鈕
    $(document).on('click', '.crop_btn_submit', function() {
        var $thisBtn = $(this);
        var $group = $thisBtn.closest('.crop-group'); // 🎯 鎖定當前的群組容器
        var cropper = $group.data('cropperInstance');
        
        if (!cropper) return;

        var $cropUpImg = $group.find('.cropUploadImage');
        var targetWidth = parseInt($cropUpImg.attr('crop-width'), 10) || 800;
        var targetHeight = parseInt($cropUpImg.attr('crop-height'), 10) || 600;

        var canvas = cropper.getCroppedCanvas({
        width: targetWidth,
        height: targetHeight
        });

        if (canvas) {
        canvas.toBlob(function(blob) {
            if (!blob) return;
            var reader = new FileReader();
            reader.readAsDataURL(blob); 
            reader.onloadend = function() {
            var cropImage64 = reader.result;
            
            // 寫回當前群組對應的元件中
            $group.find('.avatarModal').modal('hide');
            $group.find('.avatarImage').prop('src', cropImage64);
            $group.find('.mainimgchk').val($group.data('upFileName') || '');
            $group.find('.cropImgTrash').removeClass('hide').addClass('show');
            
            // 儲存當前群組的 Base64 碼到 DOM 上，以便 getFormObjects 或存檔時讀取
            $group.data('cropImage64', cropImage64);
            
            if (typeof doSerializeCheck === 'function') {
                doSerializeCheck();
            }
            };
        }, 'image/jpeg', 0.75);
        }
    });

    // 4. 垃圾桶移除圖像
    $(document).on('click', '.cropImgTrash', function(e) {
        e.preventDefault();
        var $thisTrash = $(this);
        var $group = $thisTrash.closest('.crop-group'); // 🎯 鎖定當前的群組容器
        
        if (confirm('執行編輯功能更新後，此圖像將自伺服器中移除，確認移除這個圖像？！')) {
            var $cropUpImg = $group.find('.cropUploadImage');
            var $currIMG = $group.find('.avatarImage');
            
            var nopicImg = "../uploadFiles/imageFiles/nopic_200x200.jpg";
            var width = parseFloat($cropUpImg.attr('crop-width')) || 1;
            var height = parseFloat($cropUpImg.attr('crop-height')) || 1;
            var imgRatio = (width / height).toFixed(2);

            switch(imgRatio) {
                case '1.00': 
                nopicImg = $currIMG.hasClass("profile-user-img") 
                    ? "../uploadFiles/imageFiles/nopic_user_128x128.jpg" 
                    : "../uploadFiles/imageFiles/nopic_200x200.jpg";
                break;
                case '1.33': nopicImg = "../uploadFiles/imageFiles/nopic_400x300.jpg"; break;
                case '1.50': nopicImg = "../uploadFiles/imageFiles/nopic_300x200.jpg"; break;
                case '1.78': nopicImg = "../uploadFiles/imageFiles/nopic_320x180.jpg"; break;
            }

            $cropUpImg.val('');
            $currIMG.prop('src', nopicImg);
            $group.find('.overtext').html('新增代表圖');
            
            $group.data('cropImage64', '@DELETE@'); // 回寫刪除狀態
            $thisTrash.removeClass('show').addClass('hide');
            $group.find('.mainimgchk').val('');
            
            if (typeof doSerializeCheck === 'function') {
                doSerializeCheck();
            }
        }
    });


    ////////////////////////////////////////////////////////////////
    // --------------- 圖庫圖像上傳操作（帶預設圖像操作） ------------- //
    ////////////////////////////////////////////////////////////////
    $('.tab-content input[type="file"]:not(#main_crop_area *)').each(function(i,e){
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


})(jQuery);


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


