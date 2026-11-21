<?php
##############################################################
################### 5SeasonLife 後備作業 #########################
################ 自資料庫讀取並生成 CSV工作檔案 ###################
##############################################################
    error_reporting(E_ALL); 
    session_start();
    require_once('../includeFiles/db.inc.php');
    require_once('../includeFiles/common.lib.php');
    $doact = isset($_POST['doact']) ? $_POST['doact'] : '';
    $filename = isset($_POST['filename']) ? $_POST['filename'] : '';
    $filenameStart = '';
    $writeMode = isset($_POST['writeMode']) ? $_POST['writeMode'] : '';
    $startID = isset($_POST['startID']) ? $_POST['startID'] : 1;
    $endID = isset($_POST['endID']) ? $_POST['endID'] : 0;
    $dataTable = isset($_POST['dataTable']) ? $_POST['dataTable'] : '';
    if($dataTable == 'bc'){
        $fromTB = 'fh.buildcase';
        $fromIDcol = 'bc_id';
        $filenameStart = 'BuildCase_';
        $fromTBtext = '社區建案';
    }else if($dataTable == 'ret'){
        $fromTB = 'fh.aprret';
        $fromIDcol = 'ret_id';
        $filenameStart = 'Aprret_';
        $fromTBtext = '實價登錄';
    }
    $csvFolder = 'csvFiles/';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	
  <title> 人生第五季 Fifth Season Life </title>
    <!-- -------------------------- CSS Link -------------------------- -->
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
    <link rel="stylesheet" href="../pluginFiles/font-gis/css/font-gis.css" />
    <!-- Theme style -->
    <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
    <!-- -------------------------- /CSS Link -------------------------- -->

  </head>
<body>
    <div class="wrapper">
        <div class="card-body">
            <hr>
            <div style="font-size:16px;line-height:20px;">
                <h4>人生第五季後備工作--匯出資料庫紀錄並生成CSV檔案</h4>
                <hr>
                <div class="card-body" style="width:540px;background-color:#D6DAE1;">
                    <form id="form_action" name="form_action" action="#" method="post">
                        <input type="hidden" id="doact" name="doact" style="width:100px;" value="1">
                        <div class="form-group row">
                            <label for="dataTable" class="col-sm-2 col-form-label">來源資料</label>
                            <div class="col-sm-10">
                                <select class="form-control" id="dataTable" name="dataTable" value="BuildingCase_" placeholder="來源資料">
                                    <option value="">- 請選擇來源資料表 -</option>
                                    <option value="bc"<?php if($dataTable == 'bc') echo ' selected'; ?>>建案資料 fh.data_buildingcase</option>
                                    <option value="ret"<?php if($dataTable == 'ret') echo ' selected'; ?> disabled>實價登錄資料 fh.aprret</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="startID" class="col-sm-2 col-form-label">起始ID</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="startID" name="startID" value="<?php echo $startID; ?>" placeholder="起始ID">
                            </div>
                            <label for="endID" class="col-sm-2 col-form-label">終止ID</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="endID" name="endID" value="<?php echo $endID; ?>" placeholder="終止ID">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="writeMode" class="col-sm-2 col-form-label">建檔方式</label>
                            <div class="col-sm-10">
                                <input type="radio" id="writeMode" name="writeMode" value="new"<?php if($writeMode == 'new') echo ' checked'; ?>> 新建複寫 
                                &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
                                <input type="radio" id="writeMode" name="writeMode" value="apend"<?php if($writeMode == 'apend') echo ' checked'; ?>> 追加紀錄
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="filename" class="col-sm-2 col-form-label">檔案名稱</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <div id="namePrepend" class="input-group-prepend"><span class="input-group-text"><?php echo $filenameStart; ?></span></div>
                                    <input type="text" class="form-control" id="filename" name="filename" value="<?php echo $filename; ?>" placeholder="檔案名稱">
                                    <div class="input-group-append"><span class="input-group-text">.csv</span></div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-danger">開始匯出</button>
                        <button type="button" class="btn btn-warning" onClick="window.location.replace('BC_DBtoCSV.php');">重置</button>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
                        <button type="button" class="btn btn-secondary" onClick="window.location.replace('index.php');">首頁</button>
                    </form>
                </div>
                <!-- jQuery -->
                <script src="../pluginFiles/jquery/jquery.min.js"></script>
                <!-- Bootstrap 4 -->
                <script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script>
                <script>
                    $(function () {
                        $('#dataTable').on('change',function(){
                            var dataTable = $(this).val();
                            if(dataTable == 'bc'){
                                $('#namePrepend span').html('BuildingCase_');
                            }else if(dataTable == 'ret'){
                                $('#namePrepend span').html('Aprret_');
                            }
                        });

                        $('#form_action').on('submit',function(){
                            var dataTable = $('#dataTable').val();
                            var fromTB = '';
                            var fromTBtext = '';
                            var filenameStart = '';
                            switch(dataTable){
                                case 'bc':
                                    fromTB = 'fh.buildcase';
                                    fromTBtext = '社區建案';
                                    filenameStart = 'BuildCase_';
                                break;
                                case 'ret':
                                    fromTB = 'fh.aprret';
                                    fromTBtext = '實價登錄';
                                    filenameStart = 'Aprret_';
                                break;
                            }
                            var filename = $('#filename').val();
                            var startID = $('#startID').val();
                            var endID = $('#endID').val();
                            var writeMode = $('input[name=writeMode]:checked').val();
                            var confirmText = (writeMode=='new' ? '新建' : '追加') + fromTBtext + '紀錄於檔案 ' + filenameStart + filename + '.csv 內，取自資料表 ' + fromTB + ' 紀錄，開始 ID 為' + startID + '，終止 ID 為' + ((endID=='' || endID==0) ? '最末紀錄' : endID) + '，是否繼續?';
                            if(!confirm(confirmText)){ return false; }
                        })
                    });
                </script>
                <hr>
                <?php
                if(empty($doact)){ echo "請按「開始轉移」按鍵進行操作！";exit(); }
                if(empty($filename)){ echo "請輸入檔案名稱！";exit(); }
                if(empty($writeMode)){ echo "請選擇建檔方式！";exit(); }
                if(empty($dataTable)){ echo "請選擇來源資料表！";exit(); }

                $csvName = $filenameStart.$filename.'.csv';//預備生成的檔名
                $selectStatment = '*';
                if($dataTable == 'bc'){
                    $selectStatment .= ", (CASE WHEN bc_coordinates.EnvelopeAngle() > 90 THEN bc_coordinates.ReorientObject().EnvelopeCenter().Long ELSE bc_coordinates.EnvelopeCenter().Long END) AS lng";
                    $selectStatment .= ", (CASE WHEN bc_coordinates.EnvelopeAngle() > 90 THEN bc_coordinates.ReorientObject().EnvelopeCenter().Lat ELSE bc_coordinates.EnvelopeCenter().Lat END) AS lat";
                    $selectStatment .= ", bc_coordinates.ToString() AS coorDinates";
                }else if($dataTable == 'ret'){
                    $selectStatment .= '';
                }
                

                $sql = "SELECT {$selectStatment} FROM {$fromTB}";
                //$sql .= " WHERE bc_district='".$di."' AND dt_year='".$yi."'";
                $sql .= " WHERE {$fromIDcol} >= {$startID}";
                if(!empty($endID)){ $sql .= " AND {$fromIDcol} <= {$endID}"; }
                $sql .= " ORDER BY {$fromIDcol} ASC";
                $res = $DB->query($sql, array());
                $cTotalRows = 0;
                $headrow = array();
                $fullPath = $csvFolder.$csvName;
                $fp = fopen($fullPath, $writeMode=='new' ? 'w' : 'a'); // open in write only mode (write at the start of the file)
                if($writeMode=='new'){
                    fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM修正
                    switch($dataTable){
                        case 'bc':
                            $headrow = [
                                'ID'.PHP_EOL.'bc_id',
                                '建檔日期'.PHP_EOL.'bc_published',
                                '編輯日期'.PHP_EOL.'bc_updated',
                                '所在城市'.PHP_EOL.'bc_city',
                                '所在區劃'.PHP_EOL.'bc_district',
                                '社區全名'.PHP_EOL.'bc_title',
                                '別稱一'.PHP_EOL.'bc_subtitle',
                                '別稱二'.PHP_EOL.'bc_subtitle2',
                                '建商簡稱'.PHP_EOL.'bc_pureprefix',
                                '社區名稱'.PHP_EOL.'bc_puretitle',
                                '建案期數'.PHP_EOL.'bc_purephase',
                                '案內分區'.PHP_EOL.'bc_pureext',
                                '建案型態'.PHP_EOL.'bc_mode',
                                '建案類別'.PHP_EOL.'bc_type',
                                '用途規劃'.PHP_EOL.'bc_usefor',
                                '基地地址'.PHP_EOL.'bc_addr',
                                '緯度座標'.PHP_EOL.'bc_lng',
                                '經度座標'.PHP_EOL.'bc_lat',
                                '地理座標'.PHP_EOL.'bc_coordinates',
                                '基地面積'.PHP_EOL.'bc_area',
                                '總戶數'.PHP_EOL.'bc_totalnum',
                                '公設比'.PHP_EOL.'bc_pratio',
                                '建蔽率'.PHP_EOL.'bc_coverage',
                                '容積率'.PHP_EOL.'bc_far',
                                '交屋時間'.PHP_EOL.'bc_completion',
                                '交屋屋況'.PHP_EOL.'bc_state',
                                '格局規劃'.PHP_EOL.'bc_plan',
                                '單層戶數'.PHP_EOL.'bc_layernum',
                                '特色說明'.PHP_EOL.'bc_des',
                                '建案單價'.PHP_EOL.'bc_unitprice',
                                '建案總價'.PHP_EOL.'bc_totalprice',
                                '車位價格'.PHP_EOL.'bc_pprice',
                                '商鋪價格'.PHP_EOL.'bc_sprice',
                                '公開銷售'.PHP_EOL.'bc_selling',
                                '貸款成數'.PHP_EOL.'bc_loan',
                                '接待地址'.PHP_EOL.'bc_meetadd',
                                '車位配比'.PHP_EOL.'bc_parkratio',
                                '車位規劃'.PHP_EOL.'bc_parkplan',
                                '車位總數'.PHP_EOL.'bc_parknum',
                                '棟戶規劃'.PHP_EOL.'bc_building',
                                '地上最低樓層'.PHP_EOL.'bc_abovemin',
                                '地上最高樓層'.PHP_EOL.'bc_abovemax',
                                '地下最低樓層'.PHP_EOL.'bc_undermin',
                                '地下最高樓層'.PHP_EOL.'bc_undermax',
                                '座向規劃'.PHP_EOL.'bc_direction',
                                '物業公司'.PHP_EOL.'bc_pmc',
                                '管委會'.PHP_EOL.'bc_rmc',
                                '管理費用'.PHP_EOL.'bc_mfee',
                                '工程結構'.PHP_EOL.'bc_structure',
                                '土地分區'.PHP_EOL.'bc_zoning',
                                '建造執照'.PHP_EOL.'bc_blicense',
                                '使用執照'.PHP_EOL.'bc_ulicense',
                                '建材說明'.PHP_EOL.'bc_materials',
                                '公共設施'.PHP_EOL.'bc_inpublic',
                                '充電樁'.PHP_EOL.'bc_pile',
                                '垃圾處理'.PHP_EOL.'bc_trash',
                                '投資建設'.PHP_EOL.'bc_investor',
                                '營造公司'.PHP_EOL.'bc_constructer',
                                '企劃銷售'.PHP_EOL.'bc_saleteam',
                                '建築設計'.PHP_EOL.'bc_cdesign',
                                '景觀設計'.PHP_EOL.'bc_vdesign',
                                '公設設計'.PHP_EOL.'bc_pdesign',
                                '燈光設計'.PHP_EOL.'bc_ldesign',
                                '捷運系統'.PHP_EOL.'bc_mrt',
                                '高速公路'.PHP_EOL.'bc_hiway',
                                '快速道路'.PHP_EOL.'bc_speedway',
                                '高鐵系統'.PHP_EOL.'bc_thsr',
                                '臺鐵系統'.PHP_EOL.'bc_tra',
                                '其他方式'.PHP_EOL.'bc_othertraffic',
                                '學區'.PHP_EOL.'bc_edu',
                                '超商/賣場'.PHP_EOL.'bc_market',
                                '傳統市場'.PHP_EOL.'bc_omarket',
                                '公共建設'.PHP_EOL.'bc_outpublic',
                                '熱門商圈'.PHP_EOL.'bc_circle',
                                '醫療機構'.PHP_EOL.'bc_medical',
                                '政府機構'.PHP_EOL.'bc_gov',
                                '其他配套'.PHP_EOL.'bc_pack',
                                '預設圖檔'.PHP_EOL.'bc_defaultphoto',
                                '圖檔碼'.PHP_EOL.'bc_image64',
                                '檢號'.PHP_EOL.'bc_check',
                                '狀態'.PHP_EOL.'bc_status',
                                '相似建案'.PHP_EOL.'bc_similar',
                                '相似建案住址'.PHP_EOL.'bc_similaradd',
                                '相似建案ID'.PHP_EOL.'bc_similarid',
                                '文字來源'.PHP_EOL.'bc_infomark',
                                '圖檔來源'.PHP_EOL.'bc_imagemark',
                                '補登註記'.PHP_EOL.'bc_updateinfo',
                                '兩點距離'.PHP_EOL.'bc_distance'
                            ];
                        break;
                        case 'ret':
                            $headrow = [];
                        break;
                    }

                    fputcsv($fp, $headrow);
                }
                $valrows = array();
                foreach ($res as $key => $value) {
                    switch($dataTable){
                        case 'bc':
                            $valrows= [
                                    $value['bc_id'],//'ID'
                                    $value['bc_published'],//'建檔日期'
                                    $value['bc_updated'],//'編輯日期'
                                    $value['bc_city'],//'所在城市'
                                    $value['bc_district'],//'所在區劃'
                                    $value['bc_title'],//'社區名稱'
                                    $value['bc_subtitle'],//'別稱一'
                                    $value['bc_subtitle2'],//'別稱二'
                                    $value['bc_pureprefix'],//'建商簡稱'
                                    $value['bc_puretitle'],//'社區名稱'
                                    $value['bc_purephase'],//'建案期數'
                                    $value['bc_pureext'],//'案內分區'
                                    $value['bc_mode'],//'建案型態'
                                    $value['bc_type'],//'建案類別'
                                    $value['bc_usefor'],//'用途規劃 '
                                    $value['bc_addr'],//'基地地址'
                                    $value['lng'],//'緯度座標'
                                    $value['lat'],//'經度座標'
                                    $value['coorDinates'],//'地理坐標' $value['bc_coordinates']
                                    $value['bc_area'],//'基地面積「坪」'
                                    $value['bc_totalnum'],//'總戶數'
                                    $value['bc_pratio'],//'公設比'
                                    $value['bc_coverage'],//'建蔽率'
                                    $value['bc_far'],//'容積率'
                                    $value['bc_completion'],//'交屋時間/預期交屋'
                                    $value['bc_state'],//'交屋屋況'
                                    $value['bc_plan'],//'格局規劃(原)'
                                    $value['bc_layernum'],//'單層戶數'
                                    $value['bc_des'],//'特色說明'
                                    $value['bc_unitprice'],//'建案單價(萬/坪)'
                                    $value['bc_totalprice'],//'建案總價(萬/戶)'
                                    $value['bc_pprice'],//'車位價格(萬)'
                                    $value['bc_sprice'],//'商鋪價格(萬)'
                                    $value['bc_selling'],//'公開銷售'
                                    $value['bc_loan'],//'貸款成數'
                                    $value['bc_meetadd'],//'接待會館地址'
                                    $value['bc_parkratio'],//'車位配比'
                                    $value['bc_parkplan'],//'車位規劃'
                                    $value['bc_parknum'],//'車位總數量'
                                    $value['bc_building'],//'棟戶規劃'
                                    $value['bc_abovemin'],//'地上最低樓層'
                                    $value['bc_abovemax'],//'地上最高樓層'
                                    $value['bc_undermin'],//'地下最低樓層'
                                    $value['bc_undermax'],//'地下最高樓層'
                                    $value['bc_direction'],//'座向規劃'
                                    $value['bc_pmc'],//'物業公司'
                                    $value['bc_rmc'],//'管委會'
                                    $value['bc_mfee'],//'管理費用(元/坪/月)'
                                    $value['bc_structure'],//'工程結構'
                                    $value['bc_zoning'],//'土地分區'
                                    $value['bc_blicense'],//'建造執照'
                                    $value['bc_ulicense'],//'使用執照'
                                    $value['bc_materials'],//'建材說明'
                                    $value['bc_inpublic'],//'公共設施'
                                    $value['bc_pile'],//'充電樁'
                                    $value['bc_trash'],//'垃圾處理'
                                    $value['bc_investor'],//'投資建設'
                                    $value['bc_constructer'],//'營造公司'
                                    $value['bc_saleteam'],//'企劃銷售'
                                    $value['bc_cdesign'],//'建築設計'
                                    $value['bc_vdesign'],//'景觀設計'
                                    $value['bc_pdesign'],//'公設設計'
                                    $value['bc_ldesign'],//'燈光設計'
                                    $value['bc_mrt'],//'捷運系統'
                                    $value['bc_hiway'],//'高速公路'
                                    $value['bc_speedway'],//'快速道路'
                                    $value['bc_thsr'],//'高鐵系統'
                                    $value['bc_tra'],//'臺鐵系統'
                                    $value['bc_othertraffic'],//'其他方式描述'
                                    $value['bc_edu'],//'學區'
                                    $value['bc_market'],//'超商/賣場'
                                    $value['bc_omarket'],//'傳統市場'
                                    $value['bc_omarket'],//'公共建設'
                                    $value['bc_circle'],//'熱門商圈'
                                    $value['bc_medical'],//'醫療機構'
                                    $value['bc_gov'],//'政府機構'
                                    $value['bc_pack'],//'其他配套'
                                    $value['bc_defaultphoto'],//'預設圖檔'
                                    $value['bc_image64'],//'圖檔碼'
                                    $value['bc_check'],//'檢號'
                                    $value['bc_status'],//'狀態'
                                    $value['bc_similar'],//'相似建案(操作)'
                                    $value['bc_similaradd'],//'相似建案住址(操作)'
                                    $value['bc_similarid'],//'相似建案ID(操作)'
                                    $value['bc_infomark'],//'文字來源'
                                    $value['bc_imagemark'],//'圖檔來源'
                                    $value['bc_updateinfo'],//'補登註記'
                                    $value['bc_distance']//'兩點距離'
                            ];
                        break;
                        case 'ret':
                            $valrows = [];
                        break;
                    }
                    $writeIn = fputcsv($fp,array_map(function($v){
                        return $v."\r";//每列最後加入 "\r" 符號，強制設定儲存格格式為字串。
                    },  $valrows));
                    if($writeIn != false) { $cTotalRows++; }
                    else {
                        echo "<div>{$fromIDcol} ID: {$value[$fromIDcol]} Wrote Failed!</div>";
                    }
                }
                fclose($fp);

                echo '<hr>';
                echo "Total Done Rows: {$cTotalRows}";
                echo '<br>';

                ?>
            </div>
        </div>
    </div>
</body>
</html>

