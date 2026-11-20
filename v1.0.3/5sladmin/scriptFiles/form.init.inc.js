let locobj;
$.getJSON('../jsonFiles/locations.json', function(data) {
  locobj = data;
});

//////////////////// 載入並定義頁面表單現狀 ////////////////////////
var CHGform = $('.content-wrapper .content form');
var ORGForm = CHGform.serialize();
////////////////////////////////////////////////////////////////
// --------------------- JQUERY FUNCTIONs ------------------- //
////////////////////////////////////////////////////////////////
$(function () {
  
  // --------------- 當改變城市選項時 ---------------------
  //var distSelectorELement;
  //$("select[data-select=city]").on('change', function(){
  $('.tab-pane.active form, #form_search').on('change', "select[data-select=city]", function(){
    var elM = $(this);
    var cityId = elM.val() || '';
    var cityElm = elM.prop('id');
    var distElm = $('select[dist-fromcity=' + cityElm +']');
    if(distElm.prop('multiple')==true) {
      distElm.html('');
    }
    else { distElm.html('<option value="">-請選擇行政劃區-</option>'); }
    if(cityId&&cityId!=''){
      var disT=locobj[cityId]['district'];
      $.each(disT,function(key, value){
        distElm.append('<option value="' + key + '">'+ value +'</option>');
      });
    }
    clearoptionselector($("select[bc-fromcity="+cityElm+"]"));
    clearoptionselector($("select[circ-fromcity="+cityElm+"]"));
  });
  // 改變責任社區選單
  function clearoptionselector(elM){
    if(elM.prop('multiple')==true){
      elM.val(null).trigger('change');
      elM.html('');
    } else {
      elM.html('<option value="">' + elM.data('placeholder') + '</option>');
    }
  }
  // --------------- 當改變區劃選項時 ---------------------
  $("select[data-select=dist]").on("change", function(e){
    var distThs = $(this);
    var distID = distThs.val();
    var listBc = distThs.attr('list-bc');
    var listCirc = distThs.attr('list-circ');
    var cityElm = distThs.attr('dist-fromcity');
    $.ajax({
        type: 'post',
        url: '../funcFiles/district_subselector.php',
        data: {
          'arrDatA':distID, 
          'commSelector': listBc, 
          'circleSelector': listCirc
        },
        dataType: 'json',//data format  
        async: false, 
      success: function (data) {
        console.log(data);
          var commArr = data['comm'];
          var circleArr = data['circle'];
          var bcElm = $("select[bc-fromcity="+cityElm+"]");
          //bcElm.html('<option value="">-請選擇社區建案-</option>');
          clearoptionselector(bcElm);
          for(var i=0; i<commArr.length; i++){
            var optStr='<option value="' + commArr[i]['bc_id'] + '">[' + locobj[commArr[i]['bc_city']]['district'][commArr[i]['bc_district']] + '] '+ commArr[i]['bc_title'] +'</option>';
            bcElm.append(optStr);
          }
          if(bcElm.data('addnew')=="1"){
            bcElm.append('<option value="N">自填其他社區建案</option>');
          }
          //----------------
          var circElm = $("select[circ-fromcity="+cityElm+"]");
          //circElm.html('<option value="">-請選擇生活商圈-</option>');
          clearoptionselector(circElm)
          for(var i=0; i<circleArr.length; i++){
            var coptStr='<option value="' + circleArr[i]['scrc_id'] + '">'+ circleArr[i]['scrc_title'];
            if(circleArr[i]['scrc_circletitle']!=""){ coptStr+='('+circleArr[i]['scrc_circletitle']+')'; }
            coptStr+='</option>';
            circElm.append(coptStr);
          }
        },
        error: function (data) {
          console.log(data);
        }
    });
  });
  // --------------- 當改變社區建案選項時 ------------------addbc-fromcity
  $("select[data-select=bc]").on('change', function(){
    var bcThs = $(this);
    var commId = bcThs.val();
    var cityElm = bcThs.attr('bc-fromcity');
    var addbcElm = $("[addbc-fromcity='"+cityElm+"']");
    if(commId=='N'){//當選擇自行新增建案項目時
      addbcElm.removeAttr("disabled").focus();
    } else {
      addbcElm.val('').prop('disabled', 'disabled').blur();
    }
  });

  //JazFIX: 改為外框操作，移往 5sladmin_iframe.php
  /////////////// Top Navigator 按鈕群操作 //////////////////////
  // 操作按鈕框_儲存/（新增/更新/上傳 通用）
  /*
  window.parent.$('#topBut_save').click(function(e){ 
    e.preventDefault();
    if(!$(this).hasClass('active')){ return false;}
    var getFormPost = $('.tab-pane.active').prop("id");
    if (confirm('當按下按鍵繼續進行操作時，即代表同意遵循「人生第五季管理辦法」進行操作！\r\r確定進行資料儲存操作？！')){
      doButSave($('#form_'+ getFormPost));
    }
  });
  // 操作按鈕框_復原
  window.parent.$('#topBut_reset').click(function(e){ 
    var getFormPost = $('.tab-pane.active').prop("id"); 
    doButReset($('#form_'+ getFormPost));
  });
  // 操作按鈕框_刪除
  window.parent.$('#topBut_delete').click(function(e){ 
    var getFormPost = $('.tab-pane.active').prop("id");
    if (confirm('這個操作將刪除這個頁面的資料，以及其相關聯的所有資料表記錄，部份刪除的資料將置入垃圾桶內，並有保留期限！\r\r確定刪除此筆資料？！')){
      let recomF = prompt("請輸入安全驗證碼");
      if (recomF == "我是豬") {
        doButDelete();
      }
    }
  });
  */
  // 表單異動處理
  $('.content-wrapper .content form :input:not(:file)').on('change input', function() {
    doSerializeCheck();
  });
  /////////////// Datatable 整合操作 //////////////////////
  /** ************ 使用 AJAX Serialize（Array）POST 傳遞資料 ***************** **/
  /** *********** 合併陣列欄位處理（尤指 Select & Checkbox & Radiobox） ******* **/
  $.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    var $radio = $('input[type=radio],input[type=checkbox]',this);
    $.each($radio,function(){
        if(!o.hasOwnProperty(this.name)){
            o[this.name] = '0';
        }
    });
    return o;
  };
/**
 * 還原序列化到表單
 * @param {string} serializeString 序列化字串
 * 2024/11/05 : Jaz 修正 select2 通用語法，以及預清除多選選單內容
 * //使用方式
  var serializeString = $('#myForm').serialize(); // 序列化
  $('#myForm').doDeSerialize(serializeString); // 還原表單
 */
  $.fn.doDeSerialize = function(serializedString) {
    var $form = $(this);
    if($form.length==0) return;
    $form[0].reset();
    $('select[multiple = multiple]').val(null).trigger('change');//預先清除所有多選選單內容
    //serializedString = serializedString.replace(/+/g, '%20');
    var formFieldArray = serializedString.split("&");
    //Loop over all name-value pairs
    $.each(formFieldArray, function(i, pair) {
        var nameValue = pair.split("=");
        var name = decodeURIComponent(nameValue[0]);
        var value = decodeURIComponent(nameValue[1]);
        //Find one or more fields
        var $field = $form.find('[name="' + name + '"]');
        if($field.is("input")){
            //Checkboxes and Radio types need to be handled differently
            if ($field[0].type == "radio" || $field[0].type == "checkbox") {
                var $fieldWithValue = $field.filter('[value="' + value + '"]');
                var isFound = ($fieldWithValue.length > 0);
                //Special case if the value is not defined; value will be"on"
                if (!isFound && value == "on") {
                    $field.first().prop("checked", true);
                } else {
                    $fieldWithValue.prop("checked", isFound);
                }
            } else { //input
                $field.val(value);
            }
        }else if($field.is("select")){
          if($field[0].type == "select-multiple"){
            // 多選的下拉選單
            $field.val($field.val().concat([value]));
            $field.trigger('change');
          } else {
            // 單選的下拉選單
            $field.val(value);
          }
        } else { // textarea
            $field.val(value);
        }
  
    });//each
    return this;
  }


});

////////////////////////////////////////////////////////////////     
// ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
// -------------------- 可接收外部要求後動作 ------------------- //
////////////////////////////////////////////////////////////////
// 表單異動處理執行
function doSerializeCheck() {
  if(CHGform.serialize() !== ORGForm){
    //window.parent.$('#topBut_save').addClass('active');
    window.parent.$('#topBut_save').addClass('active');
  } else { 
    //window.parent.$('#topBut_save').removeClass('active');
    window.parent.$('#topBut_save').removeClass('active');
  }
}
function doSerialize() {
  //window.parent.$('#topBut_save').removeClass('active');
  window.parent.$('#topBut_save').removeClass('active');
  ORGForm = CHGform.serialize();
}
// 将form.serialize() 转json
function formToJson (data) {
  data=data.replace(/&/g,"\",\"");
  data=data.replace(/=/g,"\":\"");
  data="{\""+data+"\"}";
  return data;
}
  
//////////////////////// AJAX 表單處理- form 內元件值結合成課程商品準備送出 ///////////////////////////////////////////
// 在 class 中置入 「 notSubmit 」，代表這個元件值不將經由 Form Post 送出
/*
function getFormObjects(frm){
  var formOBJ = {};
  frm.find(":input").not(".notSubmit").each(function(index) {
    var itype = $(this).attr('type');
    var ikey = $(this).attr('id');
    if(ikey=='' || typeof(ikey) == 'undefined' ){ return; }
    if($(this).is('select')) {
      if($(this).prop("multiple")){ formOBJ[ikey] = arrayTOstring($(this).val()); }
      else { formOBJ[ikey] = $(this).val(); }
    } else if (itype=='radio' || itype=='checkbox') {
      ikey = $(this).attr('name');
      if($(this).is(':checked')) { formOBJ[ikey] = $(this).val(); }
      else { formOBJ[ikey] = ''; }
    } else { formOBJ[ikey] = $(this).val(); }
  });
  return formOBJ;
}
*/

function getFormObjects(frm){
  var formOBJ = {};
  
  frm.find(":input").not(".notSubmit").each(function(index) {
    var itype = $(this).attr('type');
    var ikey = $(this).attr('id');
    
    if (itype == 'radio' || itype == 'checkbox') {
      ikey = $(this).attr('name');
    }
    
    if(!ikey || ikey == ''){ return; }
    
    var isGroup = ikey.indexOf('[]') !== -1;
    if (isGroup) {
      ikey = ikey.replace('[]', '');
    }
    
    if($(this).is('select')) {
      if($(this).prop("multiple")){ 
        formOBJ[ikey] = arrayTOstring($(this).val()); 
      }
      else { formOBJ[ikey] = $(this).val(); }
      
    } else if (itype == 'checkbox') {
      if (isGroup) {
        if ($(this).is(':checked')) {
          if (!formOBJ[ikey] || typeof formOBJ[ikey] === 'string') {
            formOBJ[ikey] = [];
          }
          formOBJ[ikey].push($(this).val());
        } else {
          if (!formOBJ[ikey]) { formOBJ[ikey] = []; }
        }
      } else {
        // ⭕【核心修改】：非群組的單個 checkbox
        if ($(this).is(':checked')) { 
          formOBJ[ikey] = $(this).val(); 
        } else { 
          // 讀取 HTML 上設定的 data-unchecked 值，如果沒設，就預設給空字串 ''
          var uncheckedValue = $(this).data('unchecked');
          formOBJ[ikey] = (typeof uncheckedValue !== 'undefined') ? String(uncheckedValue) : ''; 
        }
      }
      
    } else if (itype == 'radio') {
      if ($(this).is(':checked')) { 
        formOBJ[ikey] = $(this).val(); 
      } else if (!formOBJ[ikey]) { 
        formOBJ[ikey] = ''; 
      }
      
    } else { 
      formOBJ[ikey] = $(this).val(); 
    }
  });

  for (var key in formOBJ) {
    if (Array.isArray(formOBJ[key])) {
      formOBJ[key] = formOBJ[key].join(',');
    }
  }

  for (var key in formOBJ) {
    if (key.startsWith('suneditor_') || key.startsWith('cke_') || key.startsWith('tinymce_') || key.includes('__')) {
      delete formOBJ[key];
    }
  }

  return formOBJ;
}




//var datA = frm.find(":input:not(.notSubmit,[name^=suneditor_])").serializeArray();
function suneditorCreate(elM,typE='ez',w='100%',h='auto',holD='請利用編輯器，或直接留下HTML語法的內容...'){
  var buttonType=[];
  buttonType['full']=[
                      // default
                      ['undo', 'redo'],
                      ['font', 'fontSize', 'formatBlock'],
                      ['paragraphStyle', 'blockquote'],
                      ['bold', 'underline', 'italic', 'strike', 'subscript', 'superscript'],
                      ['fontColor', 'hiliteColor', 'textStyle'],
                      ['removeFormat'],
                      ['outdent', 'indent'],
                      ['align', 'horizontalRule', 'list', 'lineHeight'],
                      ['table', 'link', 'image', 'video', 'audio'],
                      ['imageGallery'],
                      ['fullScreen', 'showBlocks', 'codeView'],
                      ['preview', 'print'],
                      ['template'],
                      // (min-width: 1546)
                      ['%1546', [
                          ['undo', 'redo'],
                          ['font', 'fontSize', 'formatBlock'],
                          ['paragraphStyle', 'blockquote'],
                          ['bold', 'underline', 'italic', 'strike', 'subscript', 'superscript'],
                          ['fontColor', 'hiliteColor', 'textStyle'],
                          ['removeFormat'],
                          ['outdent', 'indent'],
                          ['align', 'horizontalRule', 'list', 'lineHeight'],
                          ['table', 'link', 'image', 'video', 'audio'],
                          ['imageGallery'],
                          ['fullScreen', 'showBlocks', 'codeView'],
                          ['-right', ':i-More Misc-default.more_vertical', 'preview', 'print', 'template']
                      ]],
                      // (min-width: 1455)
                      ['%1455', [
                          ['undo', 'redo'],
                          ['font', 'fontSize', 'formatBlock'],
                          ['paragraphStyle', 'blockquote'],
                          ['bold', 'underline', 'italic', 'strike', 'subscript', 'superscript'],
                          ['fontColor', 'hiliteColor', 'textStyle'],
                          ['removeFormat'],
                          ['outdent', 'indent'],
                          ['align', 'horizontalRule', 'list', 'lineHeight'],
                          ['table', 'link', 'image', 'video', 'audio'],
                          ['imageGallery'],
                          ['-right', ':i-More Misc-default.more_vertical', 'fullScreen', 'showBlocks', 'codeView', 'preview', 'print', 'template']
                      ]],
                      // (min-width: 1326)
                      ['%1326', [
                          ['undo', 'redo'],
                          ['font', 'fontSize', 'formatBlock'],
                          ['paragraphStyle', 'blockquote'],
                          ['bold', 'underline', 'italic', 'strike', 'subscript', 'superscript'],
                          ['fontColor', 'hiliteColor', 'textStyle'],
                          ['removeFormat'],
                          ['outdent', 'indent'],
                          ['align', 'horizontalRule', 'list', 'lineHeight'],
                          ['-right', ':i-More Misc-default.more_vertical', 'fullScreen', 'showBlocks', 'codeView', 'preview', 'print', 'template'],
                          ['-right', ':r-More Rich-default.more_plus', 'table', 'link', 'image', 'video', 'audio', 'imageGallery']
                      ]],
                      // (min-width: 1123)
                      ['%1123', [
                          ['undo', 'redo'],
                          [':p-More Paragraph-default.more_paragraph', 'font', 'fontSize', 'formatBlock', 'paragraphStyle', 'blockquote'],
                          ['bold', 'underline', 'italic', 'strike', 'subscript', 'superscript'],
                          ['fontColor', 'hiliteColor', 'textStyle'],
                          ['removeFormat'],
                          ['outdent', 'indent'],
                          ['align', 'horizontalRule', 'list', 'lineHeight'],
                          ['-right', ':i-More Misc-default.more_vertical', 'fullScreen', 'showBlocks', 'codeView', 'preview', 'print', 'template'],
                          ['-right', ':r-More Rich-default.more_plus', 'table', 'link', 'image', 'video', 'audio', 'imageGallery']
                      ]],
                      // (min-width: 817)
                      ['%817', [
                          ['undo', 'redo'],
                          [':p-More Paragraph-default.more_paragraph', 'font', 'fontSize', 'formatBlock', 'paragraphStyle', 'blockquote'],
                          ['bold', 'underline', 'italic', 'strike'],
                          [':t-More Text-default.more_text', 'subscript', 'superscript', 'fontColor', 'hiliteColor', 'textStyle'],
                          ['removeFormat'],
                          ['outdent', 'indent'],
                          ['align', 'horizontalRule', 'list', 'lineHeight'],
                          ['-right', ':i-More Misc-default.more_vertical', 'fullScreen', 'showBlocks', 'codeView', 'preview', 'print', 'template'],
                          ['-right', ':r-More Rich-default.more_plus', 'table', 'link', 'image', 'video', 'audio', 'imageGallery']
                      ]],
                      // (min-width: 673)
                      ['%673', [
                          ['undo', 'redo'],
                          [':p-More Paragraph-default.more_paragraph', 'font', 'fontSize', 'formatBlock', 'paragraphStyle', 'blockquote'],
                          [':t-More Text-default.more_text', 'bold', 'underline', 'italic', 'strike', 'subscript', 'superscript', 'fontColor', 'hiliteColor', 'textStyle'],
                          ['removeFormat'],
                          ['outdent', 'indent'],
                          ['align', 'horizontalRule', 'list', 'lineHeight'],
                          [':r-More Rich-default.more_plus', 'table', 'link', 'image', 'video', 'audio', 'imageGallery'],
                          ['-right', ':i-More Misc-default.more_vertical', 'fullScreen', 'showBlocks', 'codeView', 'preview', 'print', 'template']
                      ]],
                      // (min-width: 525)
                      ['%525', [
                          ['undo', 'redo'],
                          [':p-More Paragraph-default.more_paragraph', 'font', 'fontSize', 'formatBlock', 'paragraphStyle', 'blockquote'],
                          [':t-More Text-default.more_text', 'bold', 'underline', 'italic', 'strike', 'subscript', 'superscript', 'fontColor', 'hiliteColor', 'textStyle'],
                          ['removeFormat'],
                          ['outdent', 'indent'],
                          [':e-More Line-default.more_horizontal', 'align', 'horizontalRule', 'list', 'lineHeight'],
                          [':r-More Rich-default.more_plus', 'table', 'link', 'image', 'video', 'audio', 'imageGallery'],
                          ['-right', ':i-More Misc-default.more_vertical', 'fullScreen', 'showBlocks', 'codeView', 'preview', 'print', 'template']
                      ]],
                      // (min-width: 420)
                      ['%420', [
                          ['undo', 'redo'],
                          [':p-More Paragraph-default.more_paragraph', 'font', 'fontSize', 'formatBlock', 'paragraphStyle', 'blockquote'],
                          [':t-More Text-default.more_text', 'bold', 'underline', 'italic', 'strike', 'subscript', 'superscript', 'fontColor', 'hiliteColor', 'textStyle', 'removeFormat'],
                          [':e-More Line-default.more_horizontal', 'outdent', 'indent', 'align', 'horizontalRule', 'list', 'lineHeight'],
                          [':r-More Rich-default.more_plus', 'table', 'link', 'image', 'video', 'audio', 'imageGallery'],
                          ['-right', ':i-More Misc-default.more_vertical', 'fullScreen', 'showBlocks', 'codeView', 'preview', 'print', 'template']
                      ]]
                  ];

  buttonType['ez']=[
                      ['undo', 'redo', 'font', 'fontSize', 'formatBlock', 'link'],
                      ['bold', 'underline', 'italic', 'strike', 'subscript', 'superscript'],
                      ['fontColor', 'hiliteColor', 'outdent', 'indent', 'align', 'horizontalRule', 'list'],
                      ['codeView']
                  ];      


  const editor = SUNEDITOR.create(elM, {
        display: 'block',
        width: w,
        height: h,
        videoWidth : '80%',
        youtubeQuery : 'autoplay=1&mute=1&enablejsapi=1',
        resizingBar : false,
        popupDisplay: 'full',
        charCounter: true,
        charCounterLabel: 'Characters :',
        imageGalleryUrl: '../jsonFiles/imagelist.json.php',
      buttonList: buttonType[typE],
      placeholder: holD,
      templates: [
          {
              name: '操作結果回函模板-會員',
              html: '<p><span style="font-size: 14px">親愛的會員&nbsp;</span><span style="font-size: 16px"><strong>[@username@]</strong></span><span style="font-size: 14px">&nbsp;【 ID: <span style="font-size: 14px"><strong>[@userid@] 】&nbsp;</strong></span><strong>：</strong></span><br><br><span style="font-size: 14px">( 請於此處放置您要通知的內容，同時加入動態替代標籤，替代標籤說明詳見文章管理。 )</span></p><p><span style="font-size: 14px"><br></span></p><p><span style="font-size: 14px">(以下為額外的說明：)</span></p><p><span style="font-size: 14px">[@extrain@]</span></p><p><br></p><p><br></p><p><br></p><p>來自&nbsp;</p><p><span style="font-size: 16px"><strong>人生第五季自動通知系統</strong></span></p><hr class="__se__solid"><table><tbody><tr><td><div class="se-component se-image-container __se__float-right"><figure style="width: 100%;"><img src="../uploadFiles/sysimg/sysimg_20241117020846167.png" alt="5SeasonLifeADMINlogo_full" data-rotate="" data-proportion="true" data-size="100%," data-align="right" data-percentage="100," data-file-name="sysimg_20241117020846167.png" data-file-size="0" origin-size="1000,160" data-origin="," style="width: 100%;" data-index="1"></figure></div></td></tr></tbody></table>'
          },
          {
              name: '提示回函模板-管理者',
              html: '<p><span style="font-size: 14px">親愛的會員&nbsp; </span><span style="font-size: 16px"><strong>[@username@]</strong></span><span style="font-size: 14px">&nbsp;【 ID: <span style="font-size: 14px"><strong>[@userid@] 】&nbsp;</strong></span><strong>：</strong></span><br><br><span style="font-size: 14px">( 請於此處放置您要通知的內容，同時加入動態替代標籤，替代標籤說明詳見文章管理。 )</span></p><p><br></p><p><br></p><p><br></p><p>來自&nbsp; </p><p><span style="font-size: 16px"><strong>人生第五季自動通知系統</strong></span></p><hr class="__se__solid"><table><tbody><tr><td><div class="se-component se-image-container __se__float-right"><figure style="width: 100%;"><img src="../uploadFiles/sysimg/sysimg_20241111122550223.png" alt="5SeasonLifeADMINlogo_full" data-rotate="" data-proportion="true" data-size="100%," data-align="right" data-percentage="100," data-file-name="sysimg_20241111122550223.png" data-file-size="0" origin-size="1000,160" data-origin="," style="width: 100%;"></figure></div></td></tr></tbody></table>'
          }
        ],
      "lang": SUNEDITOR_LANG['zh_tw'],
      codeMirror: CodeMirror
  });

  return editor;
}


