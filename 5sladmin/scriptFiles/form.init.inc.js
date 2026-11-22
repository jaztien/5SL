let locobj;
$.getJSON('../jsonFiles/locations.json', function(data) {
  locobj = data;
});

//////////////////// 載入並定義頁面表單現狀 ////////////////////////
// [變更1] 兼容 AdminLTE 4 (.app-main .app-content) 與舊版 (.content-wrapper .content)
var CHGform = $('.app-main .app-content form, .content-wrapper .content form');
var ORGForm = CHGform.serialize();

////////////////////////////////////////////////////////////////
// --------------------- JQUERY FUNCTIONs ------------------- //
////////////////////////////////////////////////////////////////
$(function () {
  // 重新確保 DOM 載入完成後抓到正確的表單初始狀態
  CHGform = $('.app-main .app-content form, .content-wrapper .content form');
  ORGForm = CHGform.serialize();

  // --------------- 當改變城市選項時 ---------------------
  // [變更2] 納入 #offcanvasSearch 與 .app-main 範圍進行事件委派
  $(document).on('change', '.tab-pane.active form select[data-select=city], #form_search select[data-select=city], .app-main form select[data-select=city]', function(){
    var elM = $(this);
    var cityId = elM.val() || '';
    var cityElm = elM.prop('id');
    var distElm = $('select[dist-fromcity=' + cityElm +']');
    if(distElm.prop('multiple')==true) {
      distElm.html('');
    }
    else { distElm.html('<option value="">-請選擇行政劃區-</option>'); }
    if(cityId && cityId != '' && locobj && locobj[cityId]){
      var disT = locobj[cityId]['district'];
      $.each(disT, function(key, value){
        distElm.append('<option value="' + key + '">'+ value +'</option>');
      });
    }
    clearoptionselector($("select[bc-fromcity="+cityElm+"]"));
    clearoptionselector($("select[circ-fromcity="+cityElm+"]"));
  });

  // 改變責任社區選單
  function clearoptionselector(elM){
    if(elM.length === 0) return;
    if(elM.prop('multiple')==true){
      elM.val(null).trigger('change');
      elM.html('');
    } else {
      elM.html('<option value="">' + (elM.data('placeholder') || '-請選擇-') + '</option>');
    }
  }

  // --------------- 當改變區劃選項時 ---------------------
  // [變更3] 改用 $(document).on 事件委派，確保分頁或 Offcanvas 內的選單皆可正常觸發
  $(document).on("change", "select[data-select=dist]", function(e){
    var distThs = $(this);
    var distID = distThs.val();
    var listBc = distThs.attr('list-bc');
    var listCirc = distThs.attr('list-circ');
    var cityElm = distThs.attr('dist-fromcity');
    $.ajax({
        type: 'post',
        url: '../funcFiles/district_subselector.php',
        data: {
          'arrDatA': distID, 
          'commSelector': listBc, 
          'circleSelector': listCirc
        },
        dataType: 'json',
        async: false, 
      success: function (data) {
        console.log(data);
          var commArr = data['comm'] || [];
          var circleArr = data['circle'] || [];
          var bcElm = $("select[bc-fromcity="+cityElm+"]");
          clearoptionselector(bcElm);
          for(var i=0; i<commArr.length; i++){
            var distName = (locobj && locobj[commArr[i]['bc_city']]) ? locobj[commArr[i]['bc_city']]['district'][commArr[i]['bc_district']] : '';
            var optStr='<option value="' + commArr[i]['bc_id'] + '">[' + distName + '] '+ commArr[i]['bc_title'] +'</option>';
            bcElm.append(optStr);
          }
          if(bcElm.data('addnew')=="1"){
            bcElm.append('<option value="N">自填其他社區建案</option>');
          }
          //----------------
          var circElm = $("select[circ-fromcity="+cityElm+"]");
          clearoptionselector(circElm);
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

  // --------------- 當改變社區建案選項時 ------------------
  $(document).on('change', "select[data-select=bc]", function(){
    var bcThs = $(this);
    var commId = bcThs.val();
    var cityElm = bcThs.attr('bc-fromcity');
    var addbcElm = $("[addbc-fromcity='"+cityElm+"']");
    if(commId=='N'){ // 當選擇自行新增建案項目時
      addbcElm.removeAttr("disabled").focus();
    } else {
      addbcElm.val('').prop('disabled', true).blur();
    }
  });

  // 表單異動處理
  // [變更4] 更新選取器至 AdminLTE 4 (.app-main .app-content)，同時保留舊版相容
  $(document).on('change input', '.app-main .app-content form :input:not(:file), .content-wrapper .content form :input:not(:file)', function() {
    doSerializeCheck();
  });

  /////////////// Datatable 整合操作 //////////////////////
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
   */
  $.fn.doDeSerialize = function(serializedString) {
    var $form = $(this);
    if($form.length==0) return;
    $form[0].reset();
    $('select[multiple = multiple]').val(null).trigger('change');
    var formFieldArray = serializedString.split("&");
    $.each(formFieldArray, function(i, pair) {
        var nameValue = pair.split("=");
        var name = decodeURIComponent(nameValue[0]);
        var value = decodeURIComponent(nameValue[1]);
        var $field = $form.find('[name="' + name + '"]');
        if($field.is("input")){
            if ($field[0].type == "radio" || $field[0].type == "checkbox") {
                var $fieldWithValue = $field.filter('[value="' + value + '"]');
                var isFound = ($fieldWithValue.length > 0);
                if (!isFound && value == "on") {
                    $field.first().prop("checked", true);
                } else {
                    $fieldWithValue.prop("checked", isFound);
                }
            } else {
                $field.val(value);
            }
        } else if($field.is("select")){
          if($field[0].type == "select-multiple"){
            $field.val(($field.val() || []).concat([value]));
            $field.trigger('change');
          } else {
            $field.val(value);
          }
        } else {
            $field.val(value);
        }
    });
    return this;
  };
});

////////////////////////////////////////////////////////////////     
// ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
// -------------------- 可接收外部要求後動作 ------------------- //
////////////////////////////////////////////////////////////////
// 表單異動處理執行
function doSerializeCheck() {
  if(CHGform.serialize() !== ORGForm){
    if(window.parent && window.parent.$) {
      window.parent.$('#topBut_save').addClass('active');
    }
  } else { 
    if(window.parent && window.parent.$) {
      window.parent.$('#topBut_save').removeClass('active');
    }
  }
}

function doSerialize() {
  if(window.parent && window.parent.$) {
    window.parent.$('#topBut_save').removeClass('active');
  }
  ORGForm = CHGform.serialize();
}

// 将form.serialize() 转json
function formToJson (data) {
  data=data.replace(/&/g,"\",\"");
  data=data.replace(/=/g,"\":\"");
  data="{\""+data+"\"}";
  return data;
}
  
//////////////////////// AJAX 表單處理 ///////////////////////////////////////////
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
        if ($(this).is(':checked')) { 
          formOBJ[ikey] = $(this).val(); 
        } else {
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
