<?php
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
$courseJS=_fn_getCachedJsonData("../jsonFiles/courses.json");

if(!empty($_GET['sessid'])&&file_exists("../uploadFiles/course/sessimg_".$_GET['sessid']."_thumb.jpg")){
  $cropImgDisplay2 = 'show';
  $mainimgpath2 = '../uploadFiles/course/sessimg_'.$_GET['sessid'].'_thumb.jpg?'.time();
  $mainimgchk2 = 'sessimg_'.$_GET['sessid'].'_.jpg';
} else {
  $cropImgDisplay2 = 'hide';
  $mainimgpath2 = '../uploadFiles/imageFiles/nopic_400x300.jpg';
  $mainimgchk2 = "";
}
$courseres = DB->row("SELECT course_duration,course_lesson FROM fsl_courses WHERE course_id=?",array($_GET['courseid'] ?? 0));
$sessres = DB->row("SELECT * FROM fsl_course_sessions WHERE sess_id=?",array($_GET['sessid'] ?? 0));
$lessonCount = $sessres['sess_lesson'] ?? 0;
?>
<div class="card card-body">
    <input type="hidden" id="sess_id" name="sess_id" class="notSubmit" value="<?php echo $sessres['sess_id'] ?? ''; ?>">
    <div class="form-group row">
        <label for="sess_batch" class="col-sm-2 col-form-label">課程梯次號</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" id="sess_batch" name="sess_batch" value="<?php echo $sessres['sess_batch'] ?? ''; ?>" placeholder="課程梯次號" data-required readonly>
        </div>
        <label for="sess_keycode" class="col-sm-2 col-form-label">梯次代碼</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" id="sess_keycode" name="sess_keycode" value="<?php echo $sessres['sess_keycode'] ?? ''; ?>" placeholder="課程梯次代碼">
        </div>
    </div>
    <div class="form-group row">
        <label for="sess_altname" class="col-sm-2 col-form-label">梯次別名</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="sess_altname" name="sess_altname" value="<?php echo $sessres['sess_altname'] ?? ''; ?>" placeholder="課程梯次別名">
        </div>
    </div>
    <br>
    <hr><div class="hr-more">開課日期時間</div>
    <div class="form-group row">
        <label for="sess_startdate" class="col-sm-2 col-form-label">開課日期</label>
        <div class="col-sm-4">
            <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
            </div>
            <input type="text" class="form-control" id="sess_startdate" name="sess_startdate" value="<?php echo $sessres['sess_startdate'] ?? ''; ?>" placeholder="開課日期" data-required>
            </div>
        </div>
        <label for="sess_starttime" class="col-sm-2 col-form-label">開課時間</label>
        <div class="col-sm-4">
            <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="far fa-clock"></i></span>
            </div>
            <input type="text" class="form-control" id="sess_starttime" name="sess_starttime" value="<?php echo $sessres['sess_starttime'] ?? ''; ?>" placeholder="開課時間" data-required>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="sess_duration" class="col-sm-2 col-form-label">課程時長</label>
        <div class="col-sm-4">
            <div class="input-group">
            <input type="text" class="form-control" id="sess_duration" name="sess_duration" value="<?php echo $sessres['sess_duration'] ?? ($courseres['course_duration'] ?? '0'); ?>" placeholder="課程時長" data-required>
            <div class="input-group-append">
                <span class="input-group-text areaUnit">分鐘</span>
            </div>
            </div>
        </div>
        <label for="sess_lesson" class="col-sm-2 col-form-label">課程週期</label>
        <div class="col-sm-4">
            <div class="input-group">
            <input type="text" class="form-control" id="sess_lesson" name="sess_lesson" value="<?php echo $sessres['sess_lesson'] ?? ($courseres['course_lesson'] ?? '0'); ?>" placeholder="課程週期" data-required>
            <div class="input-group-append">
                <span class="input-group-text areaUnit">堂課</span>
            </div>
            </div>
        </div>
    </div> 
    <div class="form-group row">
        <label for="sess_onweek" class="col-sm-2 col-form-label">週間星期<?php echo $lessonCount; ?></label>
        <div class="col-sm-10" style="font: size 1rem;color: #6c757d;line-height:1;">堂數超過1堂時，週間星期可複選，並依照堂數自動順序排列直至堂數結束。若每週只有一堂課時，則預設為首堂課的星期數。</div>
    </div>
    <div class="form-group row">
        <label class="col-sm-2 col-form-label"></label>
        <div class="col-sm-9">
            <?php
            $getDateWeek = $sessres['sess_startdate'] ?? '';
            if ($getDateWeek != '') $getDateWeek = date('w', strtotime($sessres['sess_startdate'] ?? ''));
            $weeks = [
                0 => '星期日',
                1 => '星期一',
                2 => '星期二',
                3 => '星期三',
                4 => '星期四',
                5 => '星期五',
                6 => '星期六'
            ];
            $savedWeeks = !empty($sessres['sess_starttime']) ? explode(',', $sessres['sess_onweek'] ?? '') : []; // 預設勾選範例
            foreach ($weeks as $key => $value) {
                $disAble = $lessonCount > 1 ? '' : ' disabled';
                $iCheckClass = $getDateWeek == $key ? 'icheck-danger' : 'icheck-primary';
                $disAble = $getDateWeek == $key ? ' disabled' : '';
                if(in_array($key, $savedWeeks)){
                    $checked = ' checked';
                    //$disAble = '';
                } else {
                    $checked = '';
                }
                // 👇 調整結構：套用 Bootstrap 4 的 form-check 和 form-check-inline
                echo '<div class="'.$iCheckClass.' d-inline" style="margin-right:30px;"><input type="checkbox" id="sess_onweek'.$key.'" name="sess_onweek[]" value="' . $key . '"' . $checked.$disAble . ' data-readonly="true"><label for="sess_onweek'.$key.'" style="margin-top:10px;font-weight: normal; cursor: pointer;">' . $value . '</label></div>';
            }
            ?>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-2 col-form-label"></label>
        <div class="col-sm-9">
            <button type="button" class="btn btn-info notSubmit" id="viewAllDates" name="viewAllDates" title="檢視產生的所有課程日期">檢視產生的所有課程日期</button>   
        </div>
    </div>
    <br>
    <hr><div class="hr-more">代表圖像</div>
    <div class="form-group row">
        <div class="image_area crop-group" id="sess_crop_area">
            <i class="nav-icon fas fa-2x fa-trash-can cropImgTrash<?php echo " ".$cropImgDisplay2; ?>" alt="刪除圖像" title="刪除圖像"></i>
            <form method="post">
                <input type="hidden" name="mainimgchk" id="mainimgchk" class="mainimgchk notSubmit" value="<?php echo $mainimgchk2; ?>">
                <label for="sess_cropUploadImage">
                    <img id="avatarImage" name="avatarImage" style="border-radius:10px;" class="img-responsive img-fluid imgEDicon avatarImage" src="<?php echo $mainimgpath2; ?>" alt="代表圖像" title="代表圖像"/>
                    <div class="overlay">
                    <div class="overtext"><?php echo !empty($sessres['sess_id']) ? "更改" : "新增"; ?>代表圖像</div>
                    </div>
                    <input type="file" id="sess_cropUploadImage" name="image" class="image cropUploadImage notSubmit" style="display:none" crop-width="800" crop-height="600">
                </label>
            </form>
            <!-- -------------------- 裁切主圖彈出視窗 -------------------- -->
            <?php include('include_avatarmodal.php'); ?>
        </div>
    </div>
    <br>
    <hr><div class="hr-more">建立教室</div>
    <div class="form-group row">
        <label for="sess_secured" class="col-sm-2 col-form-label">
            <div class="icheck-primary d-inline">
                <input type="checkbox" name="sess_secured" id="sess_secured" value="1"<?php echo ($sessres['sess_secured'] ?? '') == 1 ? "checked" : ""; ?>>
                <label for="sess_secured"></label>
            </div>
        </label>
        <div class="col-sm-4">安全限制模式開關，動態強化教室防護。</div>
        <label for="sess_secured" class="col-sm-2 col-form-label">註冊審核</label>
        <div class="col-sm-4">
            <select class="form-control" id="sess_approval" name="sess_approval">
                <option value="0" <?php echo ($sessres['sess_approval'] ?? '') == 0 ? "selected" : ""; ?>>自動審核(取得專屬網址)</option>
                <option value="1" <?php echo ($sessres['sess_approval'] ?? '') == 1 ? "selected" : ""; ?>>手動審核</option>
                <option value="2" <?php echo ($sessres['sess_approval'] ?? '') == 2 ? "selected" : ""; ?>>無需註冊</option>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="sess_beforehost" class="col-sm-2 col-form-label">
            <div class="icheck-primary d-inline">
                <input type="checkbox" name="sess_beforehost" id="sess_beforehost" value="1" data-unchecked="0"<?php echo ($sessres['sess_beforehost'] ?? '') == 1 ? "checked" : ""; ?>>
                <label for="sess_beforehost"></label>
            </div>
        </label>
        <div class="col-sm-4">與會者在主持人正式上線前，可先進入教室互動或等待。</div>
        <label for="sess_jbhtime" class="col-sm-2 col-form-label">提前時間</label>
        <div class="col-sm-4">
            <div class="input-group">
                <input type="text" class="form-control" id="sess_jbhtime" name="sess_jbhtime" value="<?php echo $sessres['sess_jbhtime'] ?? ""; ?>" placeholder="提前加入時間"<?php echo ($sessres['sess_beforehost'] ?? '') == 1 ? "" : " readonly"; ?>>
                <div class="input-group-append">
                    <span class="input-group-text">分鐘</span>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="sess_watermark" class="col-sm-2 col-form-label">
            <div class="icheck-primary d-inline">
                <input type="checkbox" name="sess_watermark" id="sess_watermark" value="1" data-unchecked="0"<?php echo ($sessres['sess_watermark'] ?? '') == 1 ? "checked" : ""; ?>>
                <label for="sess_watermark"></label>
            </div>
        </label>
        <div class="col-sm-4">視訊畫面強制加上該與會者電子郵件的浮水印。</div>
        <label for="sess_muteentry" class="col-sm-2 col-form-label">
            <div class="icheck-primary d-inline">
                <input type="checkbox" name="sess_muteentry" id="sess_muteentry" value="1" data-unchecked="0"<?php echo ($sessres['sess_muteentry'] ?? '') == 1 ? "checked" : ""; ?>>
                <label for="sess_muteentry"></label>
            </div>
        </label>
        <div class="col-sm-4">與會者進入教室時麥克風預設為靜音狀態。</div>
    </div>
    <div class="form-group row">
        <label for="sess_meetingid" class="col-sm-2 col-form-label">教室編號ID</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" id="sess_meetingid" name="sess_meetingid" value="<?php echo $sessres['sess_meetingid'] ?? ""; ?>" placeholder="教室編號ID" readonly>
        </div>
        <div class="col-sm-6">
            <button type="button" class="btn btn-primary notSubmit" id="creatMeetingID" name="creatMeetingID" title="建立教室取得編號ID"<?php echo !empty($sessres['sess_meetingid']) ? " disabled" : ""; ?>>建立</button>
            <button type="button" class="btn btn-info notSubmit" id="upMeetingID" name="upMeetingID" title="更新教室狀態"<?php echo !empty($sessres['sess_meetingid']) ? "" : " disabled"; ?>>更新</button>
            <button type="button" class="btn btn-danger notSubmit" id="deleteMeetingID" name="deleteMeetingID" title="刪除教室"<?php echo !empty($sessres['sess_meetingid']) ? "" : " disabled"; ?>>刪除</button>
            <button type="button" class="btn btn-secondary notSubmit" id="syncMeeting" name="syncMeeting" title="同步教室狀態"<?php echo !empty($sessres['sess_meetingid']) ? "" : " disabled"; ?>>同步</button>
        </div>
    </div>
    <hr><div class="hr-more">課程上架狀態</div>
    <div class="form-group row">
        <label for="sess_enrollee" class="col-sm-2 col-form-label">報名人數</label>
        <div class="col-sm-4">
            <div class="input-group">
                <input type="text" class="form-control" id="sess_enrollee" name="sess_enrollee" value="<?php echo $sessres['sess_enrollee'] ?? ""; ?>" placeholder="報名人數" readonly>
                <div class="input-group-append">
                    <span class="input-group-text">人</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <button type="button" class="btn btn-primary notSubmit" id="checkEnrollee" name="checkEnrollee" title="檢視報名學員">檢視報名學員</button>
        </div>
    </div>
    <div class="form-group row">
        <label for="sess_status" class="col-sm-2 col-form-label">狀態</label>
        <div class="col-sm-10">
            <select class="form-control" id="sess_status" name="sess_status">
            <?php
                foreach($courseJS['sessstatus'] as $key => $value){
                    echo '<option value="'.$key.'"';
                    if($key==""){ echo " disabled"; }
                    echo (($sessres['sess_status'] ?? '') == $key) ? ' selected' : '';
                    echo '>'.$value.'</option>';
                }
            ?>
            </select>
        </div>
    </div>
</div>
<script type="text/javascript">
////////////////////////////////////////////////////////////////
// ---------------- JAVASCRIPTS 全域調用參數設置 --------------- //
////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////
// ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
// ---------------------------------------------------------- //
////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////
// ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
// ---------------------------------------------------------- //
////////////////////////////////////////////////////////////////
  $(document).ready(function() {
    //------------------- DATEMASK 日期輸入元件初始化 -------------------//
    $('#sess_startdate').inputmask({
        alias: "datetime",
        inputFormat: "yyyy-mm-dd",
        placeholder: "yyyy-mm-dd",
        removeMaskOnSubmit: true, 
        showMaskOnFocus: true, // 只有滑鼠點擊時才顯示提示底線  
        jitMasking: false,//關閉即時遮罩，確保網頁一載入就看得到完整 placeholder   
        clearIncomplete: true //如果沒填完整，離開焦點（Blur）時會自動清空整個欄位
    });
    $('#sess_starttime').inputmask({
        alias: "datetime",
        inputFormat: "HH:MM",
        placeholder: "hh:mm",
        removeMaskOnSubmit: true, 
        showMaskOnFocus: true,        
        jitMasking: false,       
        clearIncomplete: true
    });
    //---------------------- 日期選擇後自動勾選星期 --------------------//
    $('#sess_startdate').change(function(){
        var currDate = $(this).val();
        if(currDate == '') { return false; } //alert('changed!');
        var getWeekday = _js_fn_getWeekday(currDate); 
        // 先尋找所有 name 為 sess_onweek[] 的 checkbox，並命令 iCheck 把勾勾通通拔除
        //$('input[name="sess_onweek[]"]').iCheck('uncheck').iCheck('enable').parent().removeClass('icheck-danger').addClass('icheck-primary');
        $('input[name="sess_onweek[]"]').prop('checked', false).prop('disabled', false).parent().removeClass('icheck-danger').addClass('icheck-primary');
        //$('input[name="sess_onweek[]"][value="' + getWeekday.number + '"]').iCheck('check').iCheck('disable').parent().addClass('icheck-danger');
        $('input[name="sess_onweek[]"][value="' + getWeekday.number + '"]').prop('checked', true).prop('disabled', true).parent().addClass('icheck-danger');
    });

    $('#sess_beforehost').change(function(){
        var isChecked = $(this).prop('checked'); 
        var currentVal = $(this).val(); // 拿到當前點擊的數值 (例如 0~6)
        var jbhELM = $('#sess_jbhtime');
        if (isChecked) {
            jbhELM.val('5').prop('readonly', false).select();
        } else {
            jbhELM.val('0').prop('readonly', true);
        }

    });

    $('input[name="sess_onweek[]"]').change(function(){
        var totalChecked = $('input[name="sess_onweek[]"]:checked').length;
        var totalLesson = parseInt($('#sess_lesson').val()); 
        if(totalChecked > totalLesson){
            alert('您選擇的週間星期數已經超過了課程總堂數 '+totalLesson+' 堂，請先取消其他星期的勾選，再重新選擇！！');
            $(this).prop('checked', false);
        }
    });


    $('#sess_lesson').change(function(){
        var totalChecked = $('input[name="sess_onweek[]"]:checked').length;
        var totalLesson = parseInt($(this).val()); 
        if(totalChecked > totalLesson){
            alert('您設定的課程總堂數 '+totalLesson+' 堂，低於所選的週間星期數，請先取消足夠的星期的勾選，再重新輸入課堂數！！');
            $(this).val(totalChecked);
        }

    });

    $('#viewAllDates').click(function(){
        var sessCount = parseInt($('#sess_lesson').val());
        var startDate = $('#sess_startdate').val();
        var weekValues = $('input[name="sess_onweek[]"]:checked').map(function() {
            return parseInt($(this).val(), 10); // 轉成整數（int）再塞入陣列
        }).get(); //將 jQuery 物件轉換成真正的 JavaScript 陣列
        var currAllDates = getCourseDatesTriple(startDate, weekValues, sessCount);
        var allDates = courseDateCombine(currAllDates.dates, currAllDates.weekdays, currAllDates.dayNums);
        alert("產生上課日期如下：\n" + allDates.join('\n') + "\n\n結業日期：\n" + currAllDates.endDate + "(" + currAllDates.endWeekday + ")");
    });

    $('#creatMeetingID').click(function(){
        Swal.fire({
            title: "人生第五季系統提醒",
            imageUrl: '../imageFiles/5slLOGO_sB&W.png', 
            imageWidth: 60,  // 👈 2. 直接指定寬度 (px)，一秒縮小！
            imageHeight: 60, // 👈 3. 直接指定高度 (px)
            html: '<div class="text-left pl-5 small" style="line-height: 1.5;">這個動作將會利用 Zoom API 進行遠端會議/課程管理，並依據這筆課程梯次紀錄內容，自動建立一個新的會議室(教室)，確定要繼續進行嗎？！</div>',
            //icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3c8dbc",
            cancelButtonColor: "#C35050",
            confirmButtonText: "是的，我要繼續!",
            cancelButtonText: "取消"
        }).then((result) => {            
            if (result.isConfirmed) {
                var sessID = $('#sess_id').val();
                var sessName = $('#sess_altname').val();
                var sessCount = parseInt($('#sess_lesson').val());
                var startDate = $('#sess_startdate').val();
                var startTime = $('#sess_starttime').val();
                var weekValues = $('input[name="sess_onweek[]"]:checked').map(function() {
                    return parseInt($(this).val(), 10); // 轉成整數（int）再塞入陣列
                }).get(); //將 jQuery 物件轉換成真正的 JavaScript 陣列
                var currAllDates = getCourseDatesTriple(startDate, weekValues, sessCount);
                var allDates = currAllDates.dates;
                //var allDatesStr = weekValues.join(',');
                //sess_secured sess_approval sess_beforehost sess_jbhtime sess_watermark sess_muteentry
                var zm_secured = $('#sess_secured').prop('checked') ? "true" : "false";
                var zm_approval = $('#sess_approval').prop('checked') ? "true" : "false";
                var zm_beforehost = $('#sess_beforehost').prop('checked') ? "true" : "false";
                var zm_jbhtime = $('#sess_jbhtime').val();
                var zm_watermark = $('#sess_watermark').prop('checked') ? "true" : "false";
                var zm_muteentry = $('#sess_muteentry').prop('checked') ? "true" : "false";

                var creatDatas = '以下是準備建立的會議間/教室資料，請先行確認:<br>';
                    creatDatas += '課程ID: ' + sessID + '<br>';
                    creatDatas += '課程名稱: ' + sessName + '<br>';
                    creatDatas += '週期(堂數): ' + sessCount + '<br>';
                    creatDatas += '開課日期: ' + startDate + ' ' + startTime + '<br>';
                    creatDatas += '每堂課日期: <br>';
                    creatDatas += '---------------------------------<br>';
                    creatDatas += allDates.join('<br>');
                    creatDatas += '<br>---------------------------------<br>';
                    //creatDatas += 'allDatesStr: ' + allDatesStr + '<br>';     
                    creatDatas += '安全限制模式: ' + zm_secured + '<br>';
                    creatDatas += '預先註冊審核: ' + zm_approval + '<br>';
                    creatDatas += '提前進入教室等待: ' + zm_beforehost + '<br>';
                    creatDatas += '提前等待時間: ' + zm_jbhtime + '<br>';
                    creatDatas += '視訊浮水印: ' + zm_watermark + '<br>';
                    creatDatas += '麥克風靜音: ' + zm_muteentry + '<br>';
                    creatDatas += '<br><br>確定要繼續進行嗎？！';

                    Swal.fire({
                        title: "人生第五季系統提醒",
                        imageUrl: '../imageFiles/5slLOGO_sB&W.png', 
                        imageWidth: 60,  // 👈 2. 直接指定寬度 (px)，一秒縮小！
                        imageHeight: 60, // 👈 3. 直接指定高度 (px)
                        html: '<div class="text-left pl-5 small" style="line-height: 1.5;">' + creatDatas + '</div>',
                        showCancelButton: true,
                        confirmButtonColor: "#3c8dbc",
                        cancelButtonColor: "#C35050",
                        confirmButtonText: "是的，我要繼續!",
                        cancelButtonText: "取消"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            //-------------- ZOOM API 連結 --------------



                            //-------------- //ZOOM API 連結 --------------
                            Swal.fire({
                                title: "完成!",
                                text: "已經成功建立了會議/課程！並更新了課程編號紀錄",
                                icon: "success",
                                confirmButtonColor: "#3c8dbc",
                                confirmButtonText: "確定"
                            });
                        }
                    });

            }
            
        });
        //myAlert('---------------------------------\n' + creatDatas + '\n確定要繼續進行嗎？！');
        //if(!confirm('---------------------------------\n' + creatDatas + '\n確定要繼續進行嗎？！')) { return false; }
    });


});

</script>
