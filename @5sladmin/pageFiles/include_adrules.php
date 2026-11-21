<?php
require_once('../includeFiles/common.lib.php');
$advpJS=_fn_getCachedJsonData("../jsonFiles/advitising.json");
?>
<div class="card-body">
    <div class="form-group row">
        <label for="advr_mode" class="col-sm-2 col-form-label">規則模式</label>
        <div class="col-sm-10">
            <select class="form-control" id="advr_mode" name="advr_mode">
            <?php
                foreach($advpJS['rulemode'] as $key => $value){
                    echo '<option value="'.$key.'"';
                    if($key==""){ echo " disabled"; }
                    if(isset($_GET['rulemode'])){
                        if(!is_null($_GET['rulemode']) && $_GET['rulemode']==$key) echo " selected";
                    }
                    echo '>'.$value.'</option>';
                }
            ?>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="advr_fixed" class="col-sm-2 col-form-label">顯示佔比</label>
        <div class="col-sm-10">
            <select class="form-control" id="advr_fixed" name="advr_fixed">
            <?php
                foreach($advpJS['fixed'] as $key => $value){
                    echo '<option value="'.$key.'"';
                    if($key==""){ echo " disabled"; }
                    if(isset($_GET['fixed'])){
                        if(!is_null($_GET['fixed']) && $_GET['fixed']==$key) echo " selected";
                    }
                    echo '>'.$value.'</option>';
                }
            ?>
            </select>
        </div>
    </div>
    <br>
    <hr><div class="hr-more">版面投放</div>
    <div class="form-group row">
        <label for="advr_positgroup" class="col-sm-2 col-form-label">以群組投放</label>
        <div class="col-sm-10">
            <select class="form-control" id="advr_positgroup" name="advr_positgroup"<?php 
            if(isset($_GET['rulemode'])){
                if($_GET['rulemode']!=1) { echo ' disabled="disabled"'; }
            }
            ?>>
            <?php
                foreach($advpJS['positgroup'] as $key => $value){
                    echo '<option value="'.$key.'"';
                    if($key==""){ echo " disabled"; }
                    if(isset($_GET['group'])){
                        if(!is_null($_GET['group']) && $_GET['group']==$key) echo " selected";
                    }
                    echo '>'.$value.'</option>';
                }
            ?>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="advr_positnum" class="col-sm-2 col-form-label">以編號投放</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="advr_positnum" name="advr_positnum" value="<?php echo isset($_GET['posit']) ? trim($_GET['posit']) : ""; ?>" placeholder="版面編號"<?php 
            if(isset($_GET['rulemode'])){
                if($_GET['rulemode']!=1) { echo ' disabled="disabled"'; }
            }
            ?>>
        </div>
    </div>
    <br>
    <hr><div class="hr-more">搜尋置頂</div>
    <div class="form-group row">
        <label for="advr_topsearch" class="col-sm-2 col-form-label">置頂順位</label>
        <div class="col-sm-6">
            <select class="form-control" id="advr_topsearch" name="advr_topsearch"<?php echo (isset($_GET['rulemode']) && $_GET['rulemode']==2) ? '' : ' disabled="disabled"'; ?>>
            <?php
                foreach($advpJS['topsearch'] as $key => $value){
                    echo '<option value="'.$key.'"';
                    if($key==""){ echo " disabled"; }
                    if(isset($_GET['top'])){
                        if(!is_null($_GET['top']) && $_GET['top']==$key) echo " selected";
                    }
                    echo '>'.$value.'</option>';
                }
            ?>
            </select>
        </div>
        <div class="col-sm-4">
            <div class="icheck-primary d-inline">
                <input type="checkbox" id="advr_indistinct" name="advr_indistinct" value="1"<?php if(isset($_GET['indist'])){if(!is_null($_GET['indist']) && intval($_GET['indist']==1)) echo " checked";} ?><?php echo (isset($_GET['rulemode']) && $_GET['rulemode']==2) ? '' : ' disabled="disabled"'; ?>>
                <label for="advr_indistinct" class="font-weight-light">無差別搜尋&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
            </div>
        </div>
    </div>
    <hr style="margin-bottom:24px;">
    <div class="form-group row">
        <label for="advr_weekend" class="col-sm-2 col-form-label">投放日</label>
        <div class="col-sm-10">
            <div class="icheck-primary d-inline">
                <input type="checkbox" id="advr_weekday" name="advr_weekday" value="1"<?php if(isset($_GET['weekday'])){if(!is_null($_GET['weekday']) && intval($_GET['weekday']==1)) echo " checked";} ?>>
                <label for="advr_weekday" class="font-weight-light">平日投放(週一～週五)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
            </div>
            <div class="icheck-primary d-inline">
                <input type="checkbox" id="advr_weekend" name="advr_weekend" value="1"<?php if(isset($_GET['weekend'])){if(!is_null($_GET['weekend']) && intval($_GET['weekend']==1)) echo " checked";} ?>>
                <label for="advr_weekend" class="font-weight-light">週末投放(週六～週日)</label>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="advr_period" class="col-sm-2 col-form-label">投放時段</label>
        <div class="col-sm-10">
            <select class="form-control" id="advr_period" name="advr_period">
            <?php
                foreach($advpJS['period'] as $key => $value){
                    echo '<option value="'.$key.'"';
                    if($key==""){ echo " disabled"; }
                    if(isset($_GET['period'])){
                        if(!is_null($_GET['period']) && $_GET['period']==$key) echo " selected";
                    }
                    echo '>'.$value.'</option>';
                }
            ?>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="advr_status" class="col-sm-2 col-form-label">狀態</label>
        <div class="col-sm-10">
            <?php
                $i=1;
                foreach($advpJS['rulstatus'] as $key => $value){
                    if($key==""&&$key!="0") continue;
                    echo '<div class="icheck-primary d-inline">';
                    echo '<input type="radio" id="advr_status'.$i.'" name="advr_status" value="'.$key.'"';
                    if(isset($_GET['status'])){
                        if(!is_null($_GET['status']) && intval($_GET['status']==$key)) echo " checked";
                    } else {
                        if($i==1) echo " checked";
                    }
                    echo '>';
                    echo '<label for="advr_status'.$i.'" class="font-weight-light">'.$value.'&nbsp;&nbsp;&nbsp;&nbsp;</label>';
                    echo '</div>';
                    $i++;
                }
            ?>
        </div>
    </div>
</div>
<script type="text/javascript">

$(function () {

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
