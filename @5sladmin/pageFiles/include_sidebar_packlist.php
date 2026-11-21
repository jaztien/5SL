<?php
$advpJS=_fn_getCachedJsonData("../jsonFiles/advitising.json");
?>
<div class="card-body">
    <div class="form-group row">
        <label for="advr_fixed" class="col-sm-2 col-form-label">顯示模式</label>
        <div class="col-sm-10">
            <select class="form-control" id="advr_fixed" name="advr_fixed">
            <?php
                foreach($advpJS['fixed'] as $key => $value){
                    echo '<option value="'.$key.'"';
                    if(isset($_GET['fixed'])){
                        if($key==""){ echo " disabled"; }
                        if(!is_null($_GET['fixed']) && $_GET['fixed']==$key) echo " selected";
                    }
                    echo '>'.$value.'</option>';
                }
            ?>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="advr_positnum" class="col-sm-2 col-form-label">版面編號</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="advr_positnum" name="advr_positnum" value="<?php echo isset($_GET['posit']) ? trim($_GET['posit']) : ""; ?>" placeholder="版面編號">
        </div>
    </div>
    <div class="form-group row">
        <label for="advr_positgroup" class="col-sm-2 col-form-label">版面級數</label>
        <div class="col-sm-10">
            <select class="form-control" id="advr_positgroup" name="advr_positgroup">
            <?php
                foreach($advpJS['positgroup'] as $key => $value){
                    echo '<option value="'.$key.'"';
                    if(isset($_GET['group'])){
                        if($key==""){ echo " disabled"; }
                        if(!is_null($_GET['group']) && $_GET['group']==$key) echo " selected";
                    }
                    echo '>'.$value.'</option>';
                }
            ?>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="advr_topsearch" class="col-sm-2 col-form-label">搜尋置頂</label>
        <div class="col-sm-6">
            <select class="form-control" id="advr_topsearch" name="advr_topsearch">
            <?php
                foreach($advpJS['topsearch'] as $key => $value){
                    echo '<option value="'.$key.'"';
                    if(isset($_GET['top'])){
                        if($key==""){ echo " disabled"; }
                        if(!is_null($_GET['top']) && $_GET['top']==$key) echo " selected";
                    }
                    echo '>'.$value.'</option>';
                }
            ?>
            </select>
        </div>
        <div class="col-sm-4">
            <div class="icheck-primary d-inline">
                <input type="checkbox" id="advr_indistinct" name="advr_indistinct" value="1"<?php if(isset($_GET['indist'])){if(!is_null($_GET['indist']) && intval($_GET['indist']==1)) echo " checked";} ?>>
                <label for="advr_indistinct" class="font-weight-light">無差別搜尋&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="advr_weekend" class="col-sm-2 col-form-label">投放日</label>
        <div class="col-sm-10">
            <div class="icheck-primary d-inline">
                <input type="checkbox" id="advr_weekday" name="advr_weekday" value="1"<?php if(isset($_GET['weekday'])){if(!is_null($_GET['weekday']) && intval($_GET['weekday']==1)) echo " checked";} ?>>
                <label for="advr_weekday" class="font-weight-light">平日投放&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
            </div>
            <div class="icheck-primary d-inline">
                <input type="checkbox" id="advr_weekend" name="advr_weekend" value="1"<?php if(isset($_GET['weekend'])){if(!is_null($_GET['weekend']) && intval($_GET['weekend']==1)) echo " checked";} ?>>
                <label for="advr_weekend" class="font-weight-light">週末投放&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
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
                    if(isset($_GET['period'])){
                        if($key==""){ echo " disabled"; }
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
                        else if($i==1) echo " checked";
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
<div class="card-footer">

</div>