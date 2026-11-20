<?php
session_start();
require('../includeFiles/db.inc.php');
require('../funcFiles/mssql.func.php');
//功能陣列建立，取自JSON檔案
$advJS=_fn_getCachedJsonData("../jsonFiles/advitising.json");
$svJS=_fn_getCachedJsonData("../jsonFiles/service.json");
?>
<!-- iCheck for checkboxes and radio inputs -->
<link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.css">
<link rel="stylesheet" href="../cssFiles/5sl.sysimages.css">
<style>
    .wrap-custom-file label i {
        margin-top:6px;
        margin-right:2px;
        display:none;
    }

    .wrap-custom-file label i.funcClose{
        display:block;
    }
    .card-body hr{
        border-color: #5F6E86;
        margin-top: 24px;
        margin-bottom: 0px;

    }
    .hr-more {
      height: 36px;
      width: 180px;
      position: relative;
      left: 50%;
      right: 50%;
      margin-top: -12px;
      margin-left:-60px;
      color: #5F6E86;
      font: normal 1em/12px;
      vertical-align: middle;
      text-align: center;
      /*border-radius: 4px;*/
      background-color: #FFFFFF;
    }

</style>
<div class="card-body">
    <div class="form-group row">
        <label for="advp_cost" class="col-sm-2 col-form-label">使用點數</label>
        <div class="col-sm-3">
            <div class="input-group">
                <input type="text" class="form-control" id="advp_cost" name="advp_cost" value="<?php echo !empty($_GET['cost']) ? $_GET['cost'] : "0"; ?>" placeholder="花費點數" readonly>
                <div class="input-group-append">
                    <span class="input-group-text areaUnit">點</span>
                </div>
            </div>
        </div>

        <label for="advi_startdate" class="col-sm-2 col-form-label">預計起始日</label>
        <div class="input-group date col-sm-5" id="reservationdate" data-target-input="nearest">
            <input type="text" id="advi_startdate" name="advi_startdate" class="form-control datetimepicker-input" data-target="#reservationdate"/>
            <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
            </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;起&nbsp;&nbsp;<span style="font-size:120%;font-weight:bold;"><?php echo $_GET['period'] ?></span>&nbsp;&nbsp;日
        </div>
    </div>
    <div class="form-group row">
        <label for="advi_advtype" class="col-sm-2 col-form-label">廣告標的課程商品</label>
        <div class="col-sm-10">
            <?php
            $i=1;
            foreach($advJS['advtype'] as $key => $value){
                if($key==""&&$key!="0") continue;
                echo '<div class="icheck-primary d-inline">';
                echo '<input type="radio" id="advi_advtype'.$i.'" name="advi_advtype" value="'.$key.'"';
                if(!empty($res['advi_advtype']) && intval($res['advi_advtype'])==$key) echo " checked";
                else if($i==1) echo " checked";
                echo '>';
                echo '<label for="advi_advtype'.$i.'" class="font-weight-light">'.$value.'&nbsp;&nbsp;&nbsp;&nbsp;</label>';
                echo '</div>';
                $i++;
            }
            ?>
        </div>
    </div>
    <div class="form-group row">
        <label for="merch_id" class="col-sm-2 col-form-label">站內房地課程商品</label>
        <div class="col-sm-10">
            <select class="form-control" id="merch_id" name="merch_id">
                <option value="">-請選擇站內房地課程商品-</option>
                <?php
                    $mdsql = "SELECT merch_id, merch_title, merch_add FROM merchandise WHERE mb_id=?";
                    $mdsr = $DB->query($mdsql,array($_GET['mbID']));
                    $i = 0; 
                    $n = count($mdsr);
                    while($i<$n){
                        echo '<option value="'.$mdsr[$i]['merch_id'].'"';
                        echo '>'.$mdsr[$i]['merch_title'].'('.$mdsr[$i]['merch_add'].')</option>';
                        $i++;
                    }
                ?>
                <!-- <option value="0">非站內課程商品,鏈結至站外網頁</option> -->
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="sv_id" class="col-sm-2 col-form-label">開放式服務課程商品</label>
        <div class="col-sm-10">
            <select class="form-control" id="sv_id" name="sv_id" disabled="disabled">
                <option value="">-請選擇開放式服務課程商品-</option>
                <?php
                    $svsql = "SELECT * FROM sv_service WHERE mb_id=?";
                    $svsr = $DB->query($svsql,array($_GET['mbID']));
                    $i = 0; 
                    $n = count($svsr);
                    while($i<$n){
                        echo '<option value="'.$svsr[$i]['sv_id'].'"';
                        echo '>【'.$svJS['type'][$svsr[$i]['sv_type']].'】【'.$svJS['cate'][$svsr[$i]['sv_cate']].'】'.$svsr[$i]['sv_title'].'</option>';
                        $i++;
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="advi_outsite" class="col-sm-2 col-form-label">站外網頁</label>
        <div class="col-sm-10">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">http(s)://</span>
                </div>
                <input type="text" class="form-control float-right" id="advi_outsite" name="advi_outsite" value="" placeholder="站外網頁" disabled="disabled">
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="advi_slogan" class="col-sm-2 col-form-label">廣告詞</label>
        <div class="col-sm-10">
            <input type="text" class="form-control float-right" id="advi_slogan" name="advi_slogan" value="" placeholder="廣告詞">
        </div>
    </div>
    <hr><div class="hr-more">廣告版位顯示圖像上傳</div>
    <div class="form-group row">
       <div class="col-sm-4">
            <div style="margin-bottom:16px;">廣告立式圖樣 400x300</div>
            <div class="area-upload">
                <div class="wrap-custom-file" style="margin-left:-8px;">
                    <input type="file" name="imgUpload_S" id="imgUpID_S" accept="jpg" />
                    <label for="imgUpID_S" fileext="" class="file-ok" alt="上傳廣告圖樣" title="上傳廣告圖樣" style="background-size:cover;background-image:url('../uploadFiles/imageFiles/nopic_400x300.jpg')">
                        <i class="fas fa-2x fa-window-close" alt="移除图樣" title="移除图樣"></i>
                    </label>
                </div>
                <div class="icheck-primary d-inline">
                    <input type="checkbox" id="advi_defaultimg" name="advi_defaultimg" value="1">
                    <label for="advi_defaultimg" class="font-weight-light">複製課程商品預設主圖</label>
                </div>
            </div>
        </div>


        <div class="col-sm-8">

            <div style="margin-bottom:16px;">廣告橫幅 1140x340</div>
            <div class="area-upload">
                <div class="wrap-custom-file" style="width:480px;height:143px;">
                    <input type="file" name="imgUpload_B" id="imgUpID_B" accept="jpg" />
                    <label for="imgUpID_B" fileext="" class="file-ok" alt="上傳廣告圖樣" title="上傳廣告圖樣" style="background-size:cover;background-image:url('../uploadFiles/imageFiles/nopic_1140x340.jpg')">
                        <i class="fas fa-2x fa-window-close" alt="移除图樣" title="移除图樣"></i>
                    </label>
                </div>
            </div>


        </div>
    </div>
</div>

<!-- Cropper JS -->
<script>
  $(function () {
    //--------------- 上傳彈出視窗中檔案元件動作 ------------------
    //$('#form_imgupdate').on('change','#img_upload',function(e){
    $('input[name^="imgUpload_"').change(function(event){
        var ev = event.target.files[0],
            tmppath = URL.createObjectURL(ev),//取得圖檔暫存路徑
            fSplit = ev.name.split('.'),//
            fName = fSplit[0],//
            fExt = fSplit[1],//
            fSize = Math.round(ev.size / 1024),//取得準備上傳的檔案大小（Kb）
            showPopup = tmppath;
        $(this).next().css("background-image", "url('"+showPopup+"')"); 
        $(this).next().find('i').addClass('funcClose'); 
        if($(this).attr('id')=='imgUpload_S'){$('#advi_defaultimg').prop( "checked", false );}
        return false;

      });
    //////////////////////////////////////////////////////////
    //Date picker
    $('#reservationdate').datetimepicker({
        format: 'L'
    });

    $("input[name='advi_advtype']").change(function(e){
        //clearImage('S');
        //clearImage('B');
        switch($(this).val()){
            case "1": 
                $('#merch_id').removeAttr("disabled");
                $('#sv_id').val('').prop('disabled', 'disabled');
                $('#advi_outsite').val('').prop('disabled', 'disabled');
            break;
            case "2":
                $('#merch_id').val('').prop('disabled', 'disabled')
                $('#sv_id').removeAttr("disabled");;
                $('#advi_outsite').val('').prop('disabled', 'disabled');
            break;
            case "3":
                $('#merch_id').val('').prop('disabled', 'disabled')
                $('#sv_id').val('').prop('disabled', 'disabled');
                $('#advi_outsite').removeAttr("disabled");
            break;
        }

    });
    

    $('#merch_id,#sv_id').on('change', function(){
        var toID = Number($(this).val());
        if(toID >0){
            var isFrom = $(this).prop('id');
            if(isFrom=='merch_id'){
                var subFolder=Math.ceil(toID/100);
                var defImgPath = "../uploadFiles/md/"+subFolder+"/mdimg_"+toID+"_default_thumb.jpg";
            } else if(isFrom=='sv_id'){
                var defImgPath = "../uploadFiles/sv/svimg_"+toID+"_default_thumb.jpg";
            }
            if($('#advi_defaultimg').is(":checked")==true){
                checkIfFileLoaded(defImgPath).on("error", function(e) {
                        alert('這個課程商品沒有預設主圖，請另外上傳廣告圖樣，或是更換推廣課程商品！');
                        clearImage('S');
                        $('#advi_defaultimg').prop( "checked", false );
                    })
                    .on("load", function(e) {
                        armImageS(defImgPath);
                    });
            }
        } else {
            clearImage('S');
            $('#advi_defaultimg').prop( "checked", false );
        }

    });

    $('.wrap-custom-file label i').click(function() { 
        if(confirm('此動作將會刪除這個已經上傳的檔案，確定執行刪除？！')){
            var eDefault='';
            var eImg = $(this).parent();
            var eFile = eImg.parent().find('input:file');
            if(eFile.prop('id')=='imgUpID_S'){ eDefault='../uploadFiles/imageFiles/nopic_400x300.jpg'; }
            else{ eDefault='../uploadFiles/imageFiles/nopic_1140x340.jpg'; }
            eImg.removeClass('file-ok').css('background-image','url("'+eDefault+'")');//清除圖像以及調整圖檔的Class
            $(this).removeClass('funcClose'); 
            eFile.val('');//清空File Browser中的檔案連結
        }
        return false;
    });

    $('#advi_defaultimg').on('change', function(){
        var isCHK = $(this).is(":checked");
        clearImage('S');

        if(isCHK){
            var isFromEle='';
            var defID = 0;
            var defTXT = '';
            var defImgPath = '';
            if(Number($('#merch_id').val())>0){
                isFromEle = 'merch_id';
                defID = Number($('#merch_id').val());
                defTXT = '課程商品';
                var subFolder=Math.ceil(defID/100);
                defImgPath = "../uploadFiles/md/"+subFolder+"/mdimg_"+defID+"_default_thumb.jpg";
            } else {
                if(Number($('#sv_id').val())>0){
                    isFromEle = 'sv_id';
                    defID = Number($('#sv_id').val());
                    defTXT = '服務';
                    defImgPath = "../uploadFiles/sv/svimg_"+defID+"_default_thumb.jpg";
                }
            }
            if(defID==0){
                alert('請在列表中選擇一個推廣標的！');
            } else {
                checkIfFileLoaded(defImgPath).on("error", function(e) {
                    alert('這個'+defTXT+'沒有預設主圖，請另外上傳廣告圖樣，或是更換推廣標的！');
                    clearImage('S');
                    $('#advi_defaultimg').prop( "checked", false );
                })
                .on("load", function(e) {
                    armImageS(defImgPath);
                });
            }
        } else {
            alert('請上傳一個廣告圖檔！');
        }
        
    });

    function checkIfFileLoaded(src) {
        return $("<img>").attr('src', src);
    }
    function clearImage(imG) {
        var eImg = $('#imgUpID_'+imG).next();
        var eFile = eImg.parent().find('input:file');
        var noPic;
        if(imG == 'S'){ noPic = "../uploadFiles/imageFiles/nopic_400x300.jpg"; }
        else if(imG == 'B'){ noPic = "../uploadFiles/imageFiles/nopic_1140x340.jpg"; }
        eImg.removeClass('file-ok').css('background-image','url("'+noPic+'")');//清除圖像以及調整圖檔的Class
        eImg.find('i').removeClass('funcClose'); 
        eFile.val('');//清空File Browser中的檔案連結
       
    }
    function armImageS(fPath) {
        var eImg = $('#imgUpID_S').next();
        var eFile = eImg.parent().find('input:file');
        eImg.addClass('file-ok').css('background-image','url("'+fPath+'")');//清除圖像以及調整圖檔的Class
        eImg.find('i').addClass('funcClose'); 
        eFile.val('');//清空File Browser中的檔案連結
    }

});

</script>
