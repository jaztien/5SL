<?php
require_once('../includeFiles/common.lib.php');
$mdJS=_fn_getCachedJsonData("../jsonFiles/merchandise.json");
?>
<div class="card-body">
    <hr><div class="hr-more">基本訊息</div>
    <input type="hidden" id="mb_id" name="mb_id" value="<?php echo isset($_GET['mbID']) ? trim($_GET['mbID']) : 0; ?>">
    <input type="hidden" id="merch_id" name="merch_id" value="<?php echo isset($_GET['mdID']) ? trim($_GET['mdID']) : 0; ?>">
    <div class="form-group row">
        <div class="col-sm-6" style="font-size:0.85em;">opdID: <span style="font-weight:bold;font-size:1em"><?php echo isset($_GET['opdID']) ? trim($_GET['opdID']) : 0; ?></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;oppID: <span id="opp_id" style="font-weight:bold;font-size:1em"><?php echo isset($_GET['oppID']) ? trim($_GET['oppID']) : 0; ?></span></div>
        <div class="col-sm-6" style="text-align:right;font-size:0.85em;">(同於對應的加購服務項目)</div>
    </div>
    <div class="form-group row">
        <label for="opp_title" class="col-sm-2 col-form-label">加價購名稱</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" id="opp_title" name="opp_title" value="<?php echo isset($_GET['oppTitle']) ? trim($_GET['oppTitle']) : ""; ?>" placeholder="加價購名稱" readonly>
        </div>
        <label for="opd_keycode" class="col-sm-2 col-form-label">代碼</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" id="opd_keycode" name="opd_keycode" value="<?php echo isset($_GET['opdKeycode']) ? trim($_GET['opdKeycode']) : ""; ?>" placeholder="代碼" readonly>
        </div>
    </div>
    <div class="form-group row">
        <label for="opd_type-select" class="col-sm-2 col-form-label">加購種類</label>
        <div class="col-sm-4">
            <input type="hidden" id="opd_type" name="opd_type" value="<?php echo isset($_GET['opd_type']) ? trim($_GET['opd_type']) : ""; ?>">
            <select class="form-control" id="opd_type-select" name="opd_type-select" disabled>
            <?php
                foreach($mdJS['opptype'] as $key => $value){
                    echo '<option value="'.$key.'"';
                    if($key==""){ echo " disabled"; }
                    if(isset($_GET['opdType'])){
                        if(!is_null($_GET['opdType']) && $_GET['opdType']==$key) echo " selected";
                    }
                    echo '>'.$value.'</option>';
                }
            ?>
            </select>
        </div>
        <label for="opd_mode-select" class="col-sm-2 col-form-label">加購模式</label>
        <div class="col-sm-4">
            <input type="hidden" id="opd_mode" name="opd_mode" value="<?php echo isset($_GET['opd_mode']) ? trim($_GET['opd_mode']) : ""; ?>">
            <select class="form-control" id="opd_mode-select" name="opd_mode-select" disabled>
            <?php
                foreach($mdJS['oppmode'] as $key => $value){
                    echo '<option value="'.$key.'"';
                    if($key==""){ echo " disabled"; }
                    if(isset($_GET['opdMode'])){
                        if(!is_null($_GET['opdMode']) && $_GET['opdMode']==$key) echo " selected";
                    }
                    echo '>'.$value.'</option>';
                }
            ?>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="opd_applydate" class="col-sm-2 col-form-label">申購日期</label>
        <div class="input-group date col-sm-4" id="opdApplydate" data-target-input="nearest">
            <input type="text" id="opd_applydate" name="opd_applydate" value="<?php echo $_GET['opdApplyDate'] ?? ''; ?>" class="form-control datetimepicker-input" data-target="#reservationdate" placeholder="申購日期"/>
            <div class="input-group-append" data-target="#opdApplydate" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
            </div>
        </div>
        <label for="opd_days" class="col-sm-2 col-form-label">有效天數</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" id="opd_days" name="opd_days" value="<?php echo isset($_GET['opdDays']) ? trim($_GET['opdDays']) : ""; ?>" placeholder="有效天數">
        </div>
    </div>
    <div class="form-group row">
        <label for="opd_startdate" class="col-sm-2 col-form-label">刊登起始日</label>
        <div class="input-group date col-sm-4" id="opdStartdate" data-target-input="nearest">
            <input type="text" id="opd_startdate" name="opd_startdate" value="<?php echo $_GET['opdStartDate'] ?? ''; ?>" class="form-control datetimepicker-input" data-target="#reservationdate" placeholder="刊登起始日"/>
            <div class="input-group-append" data-target="#opdStartdate" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
            </div>
        </div>
        <label for="opd_enddate" class="col-sm-2 col-form-label">刊登終止日</label>
        <div class="input-group date col-sm-4" id="opdEnddate" data-target-input="nearest">
            <input type="text" id="opd_enddate" name="opd_enddate" value="<?php echo $_GET['opdEndDate'] ?? ''; ?>" class="form-control datetimepicker-input" data-target="#reservationdate" placeholder="刊登終止日"/>
            <div class="input-group-append" data-target="#opdEnddate" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
            </div>
        </div>
    </div>
    <br>
    <hr><div class="hr-more">系統設定</div>
    <div class="form-group row">
        <label for="opd_cost" class="col-sm-2 col-form-label">應付點數</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" id="opd_cost" name="opd_cost" value="<?php echo isset($_GET['opdCost']) ? trim($_GET['opdCost']) : ""; ?>" placeholder="應付點數" readonly>
        </div>
        <label for="opd_paid" class="col-sm-2 col-form-label">實付點數</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" id="opd_paid" name="opd_paid" value="<?php echo isset($_GET['opdPaid']) ? trim($_GET['opdPaid']) : ""; ?>" placeholder="實付點數">
        </div>
    </div>
    <div class="form-group row">
        <label for="opd_notify" class="col-sm-2 col-form-label">通知</label>
        <div class="col-sm-4">
            <select class="form-control" id="opd_notify" name="opd_notify">
            <?php
                foreach($mdJS['yesorno'] as $key => $value){
                    echo '<option value="'.$key.'"';
                    if($key==""){ echo " disabled"; }
                    if(isset($_GET['opdNotify'])){
                        if(!is_null($_GET['opdNotify']) && $_GET['opdNotify']==$key) echo " selected";
                    }
                    echo '>'.$value.'</option>';
                }
            ?>
            </select>
        </div>
        <label for="opd_status" class="col-sm-2 col-form-label">狀態</label>
        <div class="col-sm-4">
            <select class="form-control" id="opd_status" name="opd_status">
            <?php
                foreach($mdJS['opdstatus'] as $key => $value){
                    echo '<option value="'.$key.'"';
                    if($key==""){ echo " disabled"; }
                    if(isset($_GET['opdStatus'])){
                        if(!is_null($_GET['opdStatus']) && $_GET['opdStatus']==$key) echo " selected";
                    }
                    echo '>'.$value.'</option>';
                }
            ?>
            </select>
        </div>
    </div>
</div>

<!-- Tempusdominus Bootstrap 4 -->
<script src="../pluginFiles/moment/moment.js"></script>
<script src="../pluginFiles/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>

<script type="text/javascript">

$(function () {
     ////////////////////////////////////////////////////////////
    //Date picker
   //Date and time picker
    $('#opdApplydate, #opdStartdate, #opdEnddate').datetimepicker({
        format:'YYYY-MM-DD HH:mm:ss',
        icons: { time: 'far fa-clock' }
    });
    /*
    $("#Rstartdate").timepicker({
        timeFormat: "HH:mm:ss", // 時間隔式
        interval: 60, //時間間隔
        minTime: "10", //最小時間
        maxTime: "6:00pm", //最大時間
        defaultTime: "11", //預設起始時間
        startTime: "10:00", // 開始時間
        dynamic: true, //是否顯示項目，使第一個項目按時間順序緊接在所選時間之後
        dropdown: true, //是否顯示時間條目的下拉列表
        scrollbar: true, //是否顯示捲軸
    });
*/
    $('#advr_mode').change(function(e){
        if($(this).val()==1){
            $('#advr_positgroup').removeAttr('disabled');
            $('#advr_positnum').removeAttr('disabled');
            //$('#advr_weekday').removeAttr('disabled');
            //$('#advr_weekend').removeAttr('disabled');
            //$('#advr_period').removeAttr('disabled');
            $('#advr_topsearch').val('0').attr('disabled','disabled');
            $('#advr_indistinct').prop('checked', false).attr('disabled','disabled');
        } else if($(this).val()==2){
            $('#advr_positgroup').val('0').attr('disabled','disabled');
            $('#advr_positnum').val('').attr('disabled','disabled');
            //$('#advr_weekday').prop('checked', false).attr('disabled','disabled');
            //$('#advr_weekend').prop('checked', false).attr('disabled','disabled');
            //$('#advr_period').val('0').attr('disabled','disabled');
            $('#advr_topsearch').removeAttr('disabled');
            $('#advr_indistinct').removeAttr('disabled');
        }

    });

});

</script>
