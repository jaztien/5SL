<?php
error_reporting(E_ALL);
session_start();
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
//require_once("../classFiles/addrNormalizer/lodash_string.php");
require_once("../classFiles/normalizer/bcNormalize.class.php");
$doact = isset($_POST['doact']) ? $_POST['doact'] : '';
$modeldata = isset($_POST['modeldata']) ? $_POST['modeldata'] : '';
$dataact = isset($_POST['dataact']) ? $_POST['dataact'] : '';
$dataanchor = isset($_POST['dataanchor']) ? $_POST['dataanchor'] : '';


/*
    $expSingleChar = ['尊龍','翔鳳','白金','百金','龍鳳','澄','金','銀','龍','鳳','如','虹','藍','意','日','捷','新'];//,'捷','寶'涵,'星'
    $SPexpSingleChar = implode('|', $expSingleChar);//加了分隔符「|」的字串


    $expMultiChars = ['航空城','小湯泉','小國揚','大聯盟','京都城','惠比壽','大雅市','博愛大道','CEO','二三','世家樓中','文華道','比佛利','忠義國小','真善美','紀念館','新世紀','新生活','麗水苑','江水藍','達文西','貝多芬','莫札特','維瓦第','迷迭香','波士頓','夏威夷','曼哈頓','威尼斯','伊莉諾','奧斯汀','柏克萊','松柏樓','懷石庭','植物園','葉綠宿','地中海','坎城新','大吉利','國家公園','高鐵新','台北大學'];
    $SPexpMultiChars = implode('|', $expMultiChars);//加了分隔符「|」的字串
    $prefixType = ['全新','舊'];
    $SPprefixType = implode('|', $prefixType);




        $patternA3 = '/';
        
        $patternA3 .= '(?:(?P<pre>.{2,}?))';
        $patternA3 .= '(?<!(紀念|生活))';
        $patternA3 .= '(?:(?P<area>\-?(';
        
            $patternA3 .= '(';
                $patternA3 .= $SPexpMultiChars.'|';//超過兩字元命名（特例名稱）
                //$patternA3 .= '[花公校學]園|';//或//「花園」、「公園」、「校園」、「學園」其一
                $patternA3 .= '('.$SPprefixType.')+|';//或//建物類型的前綴字串
                $patternA3 .= '(?!(花園|公園|校園|學園))[^0-9a-zA-Z]{2}區';
                //$patternA3 .= '[^a-zA-Z0-9(花園)(公園)(校園)(學園)]{2}';//或//不是「花園」、「公園」、「校園」、「學園」或字母數字的兩個字元
            $patternA3 .= ')';

            $patternA3 .= '(';//其後可能出現的尾綴
                $patternA3 .= '(新?[社特園專]?[區])|';//如字串尾為「區」的字串//（新）「社區」、「特區」、「園區」、「專區」等
                $patternA3 .= '([新小一二三四五六七八九十]?[會公賓分別行涵]?[館])|';//或字串尾為「館」的字串//
                $patternA3 .= '(基地|商圈|總部|寶座|王座)|';
                $patternA3 .= '(新?('.$SPexpSingleChar.')+[館座])|';//或//字串尾為「館」或「座」但之前只有一個字元的字串
                $patternA3 .= '([^a-zA-Z0-9'.$SPexpSingleChar.']{2}[館座])|';//或//字串尾為「館」或「座」但之前不是字母數字或是指定的單一字元搭配出來的的兩個字元
                $patternA3 .= '([^a-zA-Z0-9'.$SPexpSingleChar.'][^大]樓)|';//或//字串尾為「樓」但之前不是字母數字或是指定的單一字元搭配，且第二個字元非為「大」
                $patternA3 .= '$';//或//直接結尾
            $patternA3 .= ')';


        $patternA3 .= ')))';
        $patternA3 .= '(?:(?P<post>.*?))';

        $patternA3 .= '/iu';

            if (preg_match($patternA3, '區花區園') == true) {
                
                echo "true";
            }
            else {
                echo "false";
            }
exit();
*/
?>
<!DOCTYPE html>
<html lang="en">
  <head>	
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
    <title>人生第五季 Fifth Season Life | Buildcase Normalizer Test</title>
    <!-- -------------------------- CSS Link -------------------------- -->
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
    <!-- -------------------------- /CSS Link -------------------------- -->
    <!-- jQuery -->
    <script src="../pluginFiles/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>

        $(function () {
            $('#doSubmit,#addmodel,#adderror').on('click', function(){
                var scrollPosition = $('#tablediv').scrollTop(); 
                var butID = $(this).attr('id');
                //$('#dataanchor').val($('#modeldata').val());
                $('#dataanchor').val(scrollPosition);
                $('#dataact').val(butID);
                if(butID == 'addmodel' || butID == 'adderror'){
                    var checkedValues = $('input[type="checkbox"]:checked').map(function() {
                        return $(this).val();
                    }).get();
                    $('#modeldata').val(checkedValues);
                }
                $('#transform').submit();
            });
        });
    </script>
     <style>
        /* global */
        
        :root {
        --main-dark: #03396a;
        --grey-1: #e4e4e4;
        --background-grey: #f3f3f3;
        --main-color: #ff6500;
        --border-color: #e4e4e4;
        --zebra-color: #e9e9e9;
        --header-color: #404040;
        --table-grey-text: #828282;
        --white: white;
        --main-text-color: black;
        --header-text-color: black;
        --table-background-color: #ffffff;
        --logo-url: url("https://raw.githubusercontent.com/ALPHACamp/WFE-data-table/0f97f3113bff18353154b8644eb0b31fff2a3bef/icons/logo.svg");
        --darkmode-toggle-url: url("https://raw.githubusercontent.com/ALPHACamp/WFE-data-table/0f97f3113bff18353154b8644eb0b31fff2a3bef/icons/darkmode-on.svg");
        }
        #tablediv {
            width: 100%;
            height: 76vh;
            overflow: auto;
        }

        table {
            position: relative;
            border-collapse: collapse;
        }

        table th {
            position: sticky;
            top: 0;
            background: var(--header-color);
            vertical-align: center;
            border-bottom: 0;
            z-index: 2;
        }

        tbody td {
            left: 0;
            z-index: 1;
        }
        
     </style>
</head>

<body>
    <div class="wrapper">
        <!-- Waitloader -->
        <div id="analysisMask" style="position:absolute;left:0;right:0;top:0;bottom:0;z-index:999;width:100%;height:100%;background-color:rgba(0,0,0,0.25);display:none;">
            <div style="left:50%;top:50%;position:absolute;transform:translate(-50%,-50%);opacity:0.5;">
            <img src="../imageFiles/loading3.gif" alt="Loading" height="60" width="60"><br>資料分析中
            </div>
        </div>
        <!-- /Waitloader -->
        <div class="card-body">
            <div style="font-size:16px;line-height:20px;">
                <h6>人生第五季後備工作--建案名稱規則化測試</h6>
                <div class="card-body" style="width:100%;background-color:#D6DAE1;">
                  <form id="transform" name="transform" action="#" method="post">
                    <input type="hidden" id="doact" name="doact" value="doact">
                    <button type="button" id="doSubmit" name="doSubmit" class="btn btn-danger">執行</button>
                    <button type="button" class="btn btn-success" onClick="window.location.replace('test_bcNormalizer.php');">重置</button>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
                    <button type="button" class="btn btn-secondary" onClick="window.location.replace('index.php');">首頁</button>
                    &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<input type="text" id="showInfo" name="showInfo" style="height:36px;width:80px;background-color:#D6DAE1;" value="">
                    &nbsp&nbsp&nbsp&nbsp<span style="font-size:32px;width:20px;border-right:1px solid gray;">&nbsp</span>&nbsp&nbsp&nbsp&nbsp管理：<input type="hidden" id="modeldata" name="modeldata" style="height:36px;width:320px;" value="" placeholder="請輸入建案ID,以逗號分隔">
                    <input type="hidden" id="dataact" name="dataact" value="">
                    <input type="hidden" id="dataanchor" name="dataanchor" value="<?php echo $dataanchor; ?>">
                    <button type="button" id="addmodel" name="addmodel" class="btn btn-info">模板標註</button>
                    <button type="button" id="adderror" name="adderror" class="btn btn-danger">錯誤標註</button>
                  </form>
                </div>
                <hr>
                <div id="tablediv">
                <?php
                  if(empty($doact)){ echo "請按「執行」按鍵進行操作！";exit(); }
                  if(!empty($dataact) && $dataact != 'doSubmit'){
                    if(empty($modeldata)){
                        echo "請填寫相應的「建案名稱ID」！建案名稱ID將出現在每一筆列表的最左側！！"; exit();
                    } else {
                        $modelSplit = explode(',', $modeldata);
                        foreach($modelSplit as $key => $value){
                            $targetID = intval($value);
                            if($dataact == 'addmodel'){
                                $checkModel = $DB->single("SELECT prep_m_bcid FROM fh.prep_bcmodel WHERE prep_m_bcid=?", array($targetID));
                                if(empty($checkModel)){
                                    $inArr = array("prep_m_bcid" => $targetID);
                                    $ins = $DB->insert('fh.prep_bcmodel',$inArr);
                                } else {
                                    $delm = $DB->query("DELETE fh.prep_bcmodel WHERE prep_m_bcid=?", array($targetID));
                                }
                            } else if($dataact == 'adderror'){
                                $checkError = $DB->single("SELECT prep_e_bcid FROM fh.prep_bcerror WHERE prep_e_bcid=?", array($targetID));
                                if(empty($checkError)){
                                    $inArr = array("prep_e_bcid" => $targetID);
                                    $ins = $DB->insert('fh.prep_bcerror',$inArr);
                                } else {
                                    $dele = $DB->query("DELETE fh.prep_bcerror WHERE prep_e_bcid=?", array($targetID));
                                }
                            }
                        }

                    }
                    
                  }
                ?>
                <script>
                    $('#analysisMask').show();
                </script>
                <?php   //LIKE '%社區%' OR bc_title LIKE '%別墅%' OR bc_title LIKE '%公寓%' OR bc_title LIKE '%透天%' OR bc_title LIKE '%大樓%' OR bc_title LIKE '%大廈%' OR bc_title LIKE '%華廈%' OR bc_title LIKE '%國宅%' OR bc_title LIKE '%廠房%' OR bc_title LIKE '%工廠%' OR bc_title LIKE '%套房%' OR bc_title LIKE '%商圈%' OR bc_title LIKE '%店面%' OR bc_title LIKE '%美居%' OR bc_title LIKE '%中心%' OR bc_title LIKE '%名廈%'
                        $bcTypeBasic = ['社區','別墅','公寓','透天','大樓','大廈','華廈','國宅','廠房','工廠','套房','商圈','店面','美居','中心','名廈'];
                        $SPbcTypeBasic = implode('|', $bcTypeBasic);
                        $totalPass = 0;
                        //$bgColor_sorted = '#e6e6e6';
                        $modelArray = $DB->query("SELECT prep_m_bcid FROM fh.prep_bcmodel ORDER BY prep_m_bcid ASC", array(), PDO::FETCH_COLUMN);
                        $modelSTR = implode(',', $modelArray); //string
                        $errorArray = $DB->query("SELECT prep_e_bcid FROM fh.prep_bcerror ORDER BY prep_e_bcid ASC", array(), PDO::FETCH_COLUMN);
                        $errorSTR = implode(',', $errorArray); //string
                        //33483,36270,34055,33702,43741,43733,18770,17781,28198,02063,52108,16968,27815,33365,25861,19138,41615,41601,37896,25337,,,
                        $sql = "SELECT";
                        //$sql .= " TOP 20000";
                        $sql .= " bc_id, bc_district,bc_title, bc_addr, bc_investor, bc_constructer";
                        $sql .= " FROM fh.buildcase";
                        //------------------ All Possibilities ------------------
                        //$sql .= " WHERE bc_title <> ''";
                        //$sql .= " WHERE bc_title LIKE '%[-~,、(]%' OR bc_title LIKE '%(NO|No|no|ON|N.|.)%' OR bc_title LIKE '%第%' OR bc_title LIKE '%[期代輯區棟樓館宅座苑]%' OR bc_title LIKE '%[ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ]%' OR bc_title LIKE '%[0-9一二三四五六七八九十]%'";//部份帶有額外訊息的建案名稱
                        //$sql .= " WHERE bc_title LIKE '%[廠辦|大樓]%'";//特區|專區|公園|會館|公館|賓館|分館|別館|行館|學苑
                        //$sql .= " OR bc_title LIKE '[0-9一二三四五六七八九十][期代輯]%'";//特區|專區|公園|會館|公館|賓館|分館|別館|行館|學苑
                        //------------------ Individule test --------------------
                        //$sql .= " AND bc_id >= ? AND bc_id <= ?";
                        //$sql .= " WHERE bc_title LIKE '%[0-9一二三四五六七八九十][巷]%' OR  bc_title LIKE '%[0-9一二三四五六七八九十][弄]%' OR  bc_title LIKE '%[0-9一二三四五六七八九十][號]%'";
                        //$sql .= " WHERE bc_title LIKE '%(%' OR bc_title LIKE '%-%'";
                        //$sql .= " OR bc_title LIKE '%[a-zA-Z]館%'";
                        //$sql .= " WHERE bc_title LIKE '%區%' OR bc_title LIKE '%棟%' OR bc_title LIKE '%側%' OR bc_title LIKE '%樓%'";
                        //$sql .= " WHERE bc_title LIKE '%NO[0-9]%'";
                        //$sql .= " WHERE bc_title LIKE '%段%' OR bc_title LIKE '%區%' OR bc_title LIKE '%棟%' OR bc_title LIKE '%樓)' OR bc_title LIKE '%路)' OR bc_title LIKE '%街)' OR bc_title LIKE '%巷)'";
                        //$sql .= " WHERE bc_title LIKE '%[0-9]期[^a-zA-Z0-9]區'";
                        //$sql .= " WHERE bc_title LIKE '%公寓[0-9]'";
                        //$sql .= " WHERE bc_title LIKE '%之[0-9一二三四五六七八九十]%'";
                        //$sql .= " WHERE bc_title LIKE '%[-~]%'";
                        //$sql .= " WHERE bc_title LIKE '%[路街道段巷]%'";
                        //$sql .= " WHERE bc_title LIKE '%[^0-9a-zA-Z][一二三四五六七八九十][^0-9a-zA-Z期]%'";
                        //$sql .= " WHERE bc_title LIKE '%[^A-Za-z0-9][A-Z][0-9]'";
                            //$sql .= " OR bc_title LIKE '%[A-Z][0-9][0-9]'";
                        //$sql .= " WHERE (bc_title LIKE '%社區%' OR bc_title LIKE '%別墅%' OR bc_title LIKE '%公寓%' OR bc_title LIKE '%透天%' OR bc_title LIKE '%大樓%' OR bc_title LIKE '%大廈%' OR bc_title LIKE '%華廈%' OR bc_title LIKE '%國宅%' OR bc_title LIKE '%廠房%' OR bc_title LIKE '%工廠%' OR bc_title LIKE '%套房%' OR bc_title LIKE '%商圈%' OR bc_title LIKE '%店面%' OR bc_title LIKE '%美居%' OR bc_title LIKE '%中心%' OR bc_title LIKE '%名廈%' OR bc_title LIKE '%園區%' OR bc_title LIKE '%住宅%' OR bc_title LIKE '%廣場%')";//廣場
                        $sql .= " WHERE bc_title LIKE '%花%'";
                        //$sql .= " WHERE REGEXP_MATCHES ('bc_title', '[A-Z]+')";
                        //$sql .= " WHERE bc_title LIKE '%館%' OR bc_title LIKE '%座%'";
                        //$sql .= " WHERE bc_title LIKE '%[^a-zA-Z0-9][^a-zA-Z0-9][1-9][^a-zA-Z0-9][^a-zA-Z0-9]%'";
                        //$sql .= " WHERE bc_investor != ''";
                        //------------------ All Models and error records --------------------
                        //$sql .= " WHERE bc_id IN (".$errorSTR.")";//各類模式與有問題的紀錄
                        //$sql .= " OR bc_id IN (".$modelSTR.")";
                        //$sql .= " AND bc_id IN (".$errorSTR.")";//各類模式與有問題的紀錄
                        $sql .= " ORDER BY bc_title,bc_district ASC";
                        $res = $DB->query($sql, array(51916,51916));//使用 FETCH_COLUMN 來回傳一維陣列, PDO::FETCH_COLUMN
                        //------------------- 將全部精選好物進行篩濾並分類 -------------------//
                        echo '<table id="sel_table" border="1">';
                        echo '<tr style="color:#f2f2f2;font-size:0.75em;line-height:1em;height:32px;">';
                        echo '<th>&nbsp;</th>';
                        echo '<th style="text-align:center;">No.</th>';
                        echo '<th style="width:40px;text-align:center;">ID</th>';
                        echo '<th style="width:60px;">投資商</th>';
                        echo '<th style="width:60px;">營造商</th>';
                        echo '<th style="width:40px;text-align:center;">區劃</th>';
                        echo '<th>比對名稱</th>';
                        echo '<th>子區</th>';
                        echo '<th style="width:60px;">子分屬</th>';
                        echo '<th style="width:40px;text-align:center;">期數</th>';
                        echo '<th style="width:40px;">建型</th>';
                        echo '<th>原始名稱</th>';
                        echo '<th style="width:100px;">位置區分</th>';
                        echo '<th style="width:120px;">別名1</th>';
                        echo '<th style="width:120px;">別名2</th>';
                        echo '<th style="width:40px;text-align:center;">模式</th>';
                        echo '<th>比對結果敘述</th>';
                        echo '</tr>';
                        $i = 1;
                        foreach ($res as $key => $value) {
                            /*
                            if(!preg_match('/\(?([NO|No|nO|no]\d+)|(第?([0-9]{1,2}|[零〇一二三四五六七八九十]+|[ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ])[期代號輯]?)\)?/u', $value['bc_title'] )){
                                //continue;
                            }
                            */
                            /*
                            $investor = strtr($value['bc_investor'], $normBasic);
                            if(empty($investor)){ continue; }
                            //if(preg_match('/'.$skipBasic.'|'.$passBasic.'/u', $investor )){ continue; }
                            if(preg_match('/'.$skipBasic.'|'.$passBasic.'|'.$typeBasic.'/u', $investor )){ continue; }
                            if(preg_match('/(等?((\d+)|[一二三四五六七八九十〇壹兩])[名人]?)|等人/u', $investor )){ continue; }
                            if(!preg_match('/^['.$lastName.']{1,2}/u', $investor ) || in_array($investor, $lastNameExcept)){
                                    continue;
                            }
                            //if(preg_match('/'.$passBasic.'/u', $investor )){ continue; }
                            $bcNORM = new NormBC($value['bc_title'], $value['bc_investor']);
                            $returnSTR = $bcNORM->investorAbbr();
                            echo $value['bc_id']." @@@@ ".$value['bc_title']." @@@@ <span style='color:blue;'>".$value['bc_investor']."</span> @@@@ <span style='color:red;'>".$returnSTR."</span><br>";
                            $totalPass++;
                            */

                            //if(!preg_match('/[一二三四五六七八九十]代/u', $value['bc_title'] )){ continue; }
                            //if(!preg_match('/[^0-9a-zA-Z]([〇一二三四五六七八九十]|[0-9])+$/u', $value['bc_title'] )){ continue; }
                            //if(!preg_match('/[0-9]+\.[0-9]+/u', $value['bc_title'] )){ continue; }
                            //if(!preg_match('/[^0-9a-zA-Z][一二三四五六七八九十]+[^期代號輯]/u', $value['bc_title'] )){ continue; }
                            //$bctitle = '恆美第2,3,4,8期(山水居)';
                            //$investor = '君鑑機構';
                            $bctitle = $value['bc_title'];
                            $investor = $value['bc_investor'];
                            $constructer = $value['bc_constructer'];
                            $bcNORM = new NormBC($bctitle, $investor,$constructer);
                            $rString = $bcNORM->normalized();
                        //if (trim($rString['investorAbbr']) == '') {
                            echo '<tr id="'.$value['bc_id'].'"style="';
                            if (in_array($value['bc_id'], $errorArray)) {
                                echo ' background-color: #ffccff;';
                            }
                            if (in_array($value['bc_id'], $modelArray)) {
                                echo ' border: #00cc66 ipx solid;';
                            }
                            echo '">';
                            echo '<td style="font-size:0.75em;line-height:1em;background-color:#ccc;text-align:left;padding:4px;"><input type="checkbox" id="bc_sel" name="bc_sel" style="height:20px;width:20px;cursor:pointer;" class="bc_sel" value="'.$value['bc_id'].'"></td>';
                            echo '<td style="font-size:0.75em;line-height:1em;text-align:center;">'.$i.'</td>';
                            echo '<td style="font-size:0.75em;line-height:1em;background-color:#ffb3b3;text-align:center;">'.sprintf('%05d', $value['bc_id']).'</td>';
                            echo '<td style="font-size:0.75em;line-height:1em;">'.$rString['investorAbbr'].'<div style="font-size:0.75em;line-height:1em;color:red;">'.$rString['origInvestor'].'</div></td>';
                            echo '<td style="font-size:0.75em;line-height:1em;">'.$rString['constructerAbbr'].'<div style="font-size:0.75em;line-height:1em;color:red;">'.$rString['origConstructer'].'</div></td>';
                            echo '<td style="font-size:0.75em;line-height:1em;text-align:center;">'._fn_get_citydistrict($value['bc_district']).'</td>';
                            echo '<td>'.$rString['parseTitle'].'</td>';
                            echo '<td style="font-size:0.75em;line-height:1em;">'.$rString['parseArea'].'</td>';
                            echo '<td style="font-size:0.75em;line-height:1em;">'.$rString['parseAreaExt'].'</td>';
                            echo '<td style="text-align:center;">'.$rString['parsePhase'].'</td>';
                            echo '<td style="font-size:0.75em;line-height:1em;">'.$rString['parseBCtype'].'</td>';
                            echo '<td style="font-size:0.75em;line-height:1em;">'.$bctitle.'<div style="color:red;">'.$rString['origFixed'].'</div></td>';
                            echo '<td style="font-size:0.75em;line-height:1em;">'.$rString['parseLocated'].'</td>';
                            echo '<td style="font-size:0.75em;line-height:1em;">'.$rString['parseAka1'].'</td>';
                            echo '<td style="font-size:0.75em;line-height:1em;">'.$rString['parseAka2'].'</td>';
                            echo '<td style="font-size:0.75em;line-height:1em;text-align:center;">'.$rString['parseBCmode'].'</td>';
                            echo '<td style="font-size:0.75em;line-height:1em;">'.$rString['tmatches'].'</td>';
                            echo '</tr>';
                            //echo "@@".$value['bc_addr'];
                            //echo $rString."<br>";
                            /*
                            echo "<pre>";
                            print_r($rString);
                            //print_r($NORM->road());
                            //print_r($locJS);
                            echo "</pre>";
                            */
                            $totalPass++;
                            $i++;
                        //}
                        }
                        ?>
                            </table>
                        <div>
                <?php
                        echo "<hr>";
                        echo "總筆數: ".$totalPass." 筆";
                        echo "<script>$('#showInfo').val('".$totalPass."')</script>";


/*
                  //$address = '建築物之地下室底1、2層';
                  $address = '彰化縣彰化市至善五邨二街20巷六二六至648-2號8樓地下5樓、地下6樓、地下8樓';
                  //$address = preg_match('/(地下(\d+)(樓)?(至(\d+)(樓)?)?)|(地下((\d+)(樓)?(、)?)+)/', $address, $matches);
                  //$doFilter = preg_match('/(地下(\d+)(樓)?(至(\d+)(樓)?)?)|(地下((\d+)(樓)?(、)?)+)/', $address, $matches);
                  $address = preg_replace_callback('/(地下(\d+)(樓)?(至(\d+)(樓)?)?)|(地下((\d+)(樓)?(、)?)+)/', function ($m) {
                      return str_replace('樓', '層', $m[0]);
                  }, $address);

                  echo "<pre>";
                  print_r($address);
                  echo "</pre>";


                  exit();
*/
/*   
                $addrHashed = '彰化縣彰化市開封街一段2~5號之60地下二層';//將住址中易於混淆的字編碼替換
                //$addrHashed = '彰化縣彰化市至善路2-2號至2-8號雙號,2-5號至2-9號單號建築物之地下室底七~九層';
                $units = [0 => 'no', 1 => 'subno', 2 => 'name', 3 => 'unit', 4 => 'extra'];
                $groups = [];


                //Regularizer///////////////////////////////////
                $addrHashed = strtr($addrHashed, [//替換必要字串
                        ' ' => '',
                        ',' => '',
                        //'、' => '',
                        //'~' => '之',
                        '~' => '至',
                        '-' => '之',
                        '單號' => '單',
                        '雙號' => '雙',
                    ]);
                $addrHashed = strtr($addrHashed, [//替換必要字串
                        //'((' => '(',
                        //'))' => ')',
                        '號單' => '單號',
                        '號雙' => '雙號',
                        '號、' => '、',
                        '號至' => '至'
                    ]);
                //Digitizer////////////////////////////////////////
                $addrHashed =  preg_replace_callback('/[零〇一二三四五六七八九十百千]+(?=[段路街巷弄之號樓室層至、])/u', function ($m) {
                    //return $this->toNumber($m[0]);//國字數字轉阿拉伯數字的必要規則
                    $cNum = $m[0];
                    $len = mb_strlen($cNum);
                    $cTOnumObj = [
                        '零' => '0',
                        '〇' => '0',
                        '一' => '1',
                        '二' => '2',
                        '三' => '3',
                        '四' => '4',
                        '五' => '5',
                        '六' => '6',
                        '七' => '7',
                        '八' => '8',
                        '九' => '9',
                        '十' => '10'
                    ];
                    if($len == 1){ return strtr($cNum, $cTOnumObj); }
                    else if($len==2){
                        if(mb_substr($cNum, 0, 1) == "十"){
                                $cTOnumObj['十']="1";
                                return strtr($cNum, $cTOnumObj);
                            }
                        else{
                                $cTOnumObj['十']="0";
                                return strtr($cNum, $cTOnumObj);
                            }
                    }
                    else{
                        $cTOnumObj['十']="";
                        return strtr($cNum, $cTOnumObj);
                    }





                }, $addrHashed);

                  //echo $addrHashed;
                  //exit();
                preg_match_all('/號/u', $addrHashed, $ma);
                $totalPlates = count($ma[0]);//出現在規則化後的住址中的門牌號數


                $patterns = '(?:';
                $patterns .= '(?P<no>\d+)';
                $patterns .= '(?P<subno>之\d+)?';
                $patterns .= '(?=[鄰巷弄號樓]|$)';
                $patterns .= '|(?P<name>.+?)';
                $patterns .= ')';
                $patterns .= '(?:';
                $patterns .= '(?P<unit>';
                $patterns .= '([島縣市鄉鎮市區村里道鄰路街段巷弄號樓]|魚臺)';
                $patterns .= ')';
                $patterns .= '|(?=\d+';
                $patterns .= '(?:之\d+)?[鄰巷弄號樓]|$';
                $patterns .= ')';
                $patterns .= ')';


                if (preg_match_all('/'.$patterns.'/u', $addrHashed, $matches, PREG_SET_ORDER) !== false) {

                    $para_neighbor = '';//路、街、巷、弄或是一個地理位置名稱，做為焦點搜尋的一部份，此部分獨立資料欄位儲存
                    $para_number = '';//單純門牌號碼，或是地點最終辨識的依據
                    $plateCount = 0;//【實際門牌數】整個住址出現多少個門牌號，通常只會在銷售種類為車位的課程商品中出現超過一個門牌號，超過一個號碼時，系統將停止後續操作，並發出警告
                    $noCount = 0;//依迴圈計算出現在規則化後的住址中的門牌號數，此值用做整合門牌號碼陣列的依據
                    foreach ($matches as $key => $values) {
                        $para = array_map(static function ($unit) use ($values) {
                            return isset($values[$unit]) === true ? $values[$unit] : '';//依正規表達式分析出來的住址內容進行對應
                        }, $units);
                        //$para[static::NAME] = $this->flip($para[static::NAME]);//將住址中已編碼替換的字元，進行逆向替換（替換為原來的字元）
                        
                        if (empty($para[0]) && empty($para[1]) && empty($para[3]) && !empty($para[2])) {
                            $extraString = $para[2];
                            if (preg_match('/^(\d+)至$/', $extraString)) {
                                $para_number .= $para[2];
                                $plateCount++;
                            } else if(preg_match('/^(之\d+)/', $extraString, $matches) === 1){
                                $extraString = str_replace($matches[0], '', $extraString);
                                $groups[] = ['', $matches[0], '', '', ''];
                                if(!empty($extraString)){
                                    //地下樓//地下n樓//地下n層//地下//地下室//房屋地下層//地下二層等//門牌地下一層至二層//建物門牌地下二層至地下三層
                                    //門牌房屋地下四層//等房屋地下層//等共有部分//共同使用//建築物地下二、三層//地下層(一至六層)//地下二層至地下四層
                                    //地下一層、二層//地下一層、地下二層//地下之9//地下二、三樓//地下一樓等公共設施//建築物之地下室底一,二層
                                    //建築物之地下室底一,二層//地下室地址證明//等及地下一~三樓//地下一層等共同使用部份//底一層//建築物之地下室底二層
                                    $groups[] = ['', '', '', '', $extraString];
                                }
                            } else {
                                $groups[] = $para;
                            }
                        } else if($para[3]=='號'){
                            $noCount++;
                            $plateCount++;
                            if($noCount == $totalPlates){
                                $para_number .= $para[0].$para[1].$para[2];
                                $para[0] = is_numeric($para_number) ? $para_number : '';//將門牌號碼進行對應
                                $para[1] = '';
                                $para[2] = is_numeric($para_number) ? '' : $para_number;//將門牌號碼進行對應
                                $groups[] = $para;
                            } else {
                                $para_number .= $para[0].$para[1].$para[2].$para[3];
                            }
                        } else {
                            $groups[] = $para;
                        }
                    }
                }


                  echo "<pre>";
                  print_r($groups);
                  //print_r($locJS);
                  echo "</pre>";
                  exit();


                function addr_set($address){//Address.php->set()
                    //$this->normalizer = (new Normalizer($address))->normalize()->normalizeAddress();
                    //$this->tokens = $this->tokenize();
                    $normalizer = norm_regularize($address);
                    $normalizer = normalizeAddress($normalizer);
                    $tokens = tokenize($address);

                    //return $this;
                    return array('normalizer' => $normalizer, 'tokens' => $tokens);
                } 

                function normalizeAddress($address){//Normalizer.php->normalizeAddress()
                    
                    $address = lodash_replace([
                          '臺灣' => '臺灣',
                          '臺北' => '臺北',
                          '臺中' => '臺中',
                          '臺南' => '臺南',
                          '臺東' => '臺東',
                          '釣魚臺' => '釣魚臺',
                          '臺西鄉' => '臺西鄉',
                          '霧臺鄉' => '霧臺鄉',
                          '[石曹]村' => '[石曹]裡',
                          '頭家東村' => '頭家東裡',
                          '拉芙蘭村' => '拉芙蘭裡',
                          '那瑪夏鄉' => '那瑪夏區',
                          '達卡努瓦村' => '達卡努瓦里',
                          '瑪雅村' => '瑪雅裡',
                          '南沙魯村' => '南沙魯裡',
                      ], null, $address);
                    $address = lodash_replace('/^北縣/', '臺北縣', $address);
                    $address = lodash_replace('/^北市/', '臺北市', $address);
                    $address = lodash_replace('/^(桃園|臺北|臺中|臺南|高雄)縣(?:(\w{2})[市鄉鎮])?(?:(\w{2})村)?/u', function ($m) {
                            return ($m[1] === '臺北' ? '新北' : $m[1]).'市'.
                                (isset($m[2]) === true ? $m[2].'區' : '').
                                (isset($m[3]) === true ? $m[3].'裡' : '');
                        }, $address);
                    $address = lodash_replace([
                          '苗栗縣頭份鎮' => '苗栗縣頭份市',
                          '彰化縣員林鎮' => '彰化縣員林市',
                          '臺南市中區' => '臺南市中西區',
                          '臺南市西區' => '臺南市中西區',
                          '南海諸島東沙' => '南海島東沙群島',
                          '南海諸島南沙' => '南海島南沙群島',
                          '南海諸島釣魚臺列嶼' => '釣魚臺釣魚臺',
                      ], null, $address);
                    
                    return $address;
                    
                }
                function norm_regularize($address){//Normalizer.php->regularize()

                  $address = trim($address);
                  //$address = toHalfCase($address);//??
                  $address = lodash_replace('/^\d+/', '', $address);//preg_replace

                  //$from = [' ', ',', '~', '-', '臺灣', '臺北', '臺中', '臺南', '臺東', '釣魚臺', '臺西鄉', '霧臺鄉'];
                  //$to = ['', '', '之', '之', '臺灣', '臺北', '臺中', '臺南', '臺東', '釣魚臺', '臺西鄉', '霧臺鄉'];
                  //$address = str_replace($from, $to, $address);

                  $address = lodash_replace([
                    ' ' => '',
                    ',' => '',
                    '~' => '之',
                    '-' => '之',
                    '臺灣' => '臺灣',
                    '臺北' => '臺北',
                    '臺中' => '臺中',
                    '臺南' => '臺南',
                    '臺東' => '臺東',
                    '釣魚臺' => '釣魚臺',
                    '臺西鄉' => '臺西鄉',
                    '霧臺鄉' => '霧臺鄉',
                  ], null, $address);


                  return $address;
                }

                function norm_digitize($address){//Normalizer.php->digitize()
                    return lodash_replace('/[零〇一二三四五六七八九十百千]+(?=[段路街巷弄之號樓室])/u', function ($m) {
                        return toNumber($m[0]);//NOTE: HERE!!!!
                        //return (new static($m[0]))->toNumber($address);
                    }, $address);
                }

                function tokenize($address){//Address.php->tokenize()
                        //$units = [static::NO => 'no', static::SUBNO => 'subno', static::NAME => 'name', static::UNIT => 'unit'];
                        $units = [0 => 'no', 1 => 'subno', 2 => 'name', 3 => 'unit'];
                        $patterns = implode('', [
                            '(?:(?P<no>\d+)(?P<subno>之\d+)?(?=[巷弄號樓室]|$)|(?P<name>.+?))',
                            '(?:(?P<unit>([島縣市鄉鎮市區村裡道鄰路街段巷弄號樓室]|魚臺))|(?=\d+(?:之\d+)?[巷弄號樓室]|$))',
                        ]);
                        //$tricky = Tricky::instance();
                        //$address = $tricky->hash($this->normalizer)->value();
                        $tokens = [];
                        if (preg_match_all('/'.$patterns.'/u', $address, $matches, PREG_SET_ORDER) !== false) {
                            foreach ($matches as $values) {
                                $token = array_map(static function ($unit) use ($values) {
                                    return isset($values[$unit]) === true ? $values[$unit] : '';
                                }, $units);
                                //$token[2] = flip($token[2]);
                                $tokens[] = $token;
                            }
                        }
                        return $tokens;
                    }
                    
                    //@return JArray
                    
                    function tokens(){//Address.php->tokens()
                        return new JArray($this->tokens);
                    }

                    //norm_trickypart
                    //@return Array
                    //名稱內含鄉、裡、路...等會混淆系統過濾規則的區劃路名，必須事先過濾出來，以代碼替換後再進行正規化。
                    function norm_trickypart($opt){//Tricky.php->init
                        if($opt === null || empty($opt) || ($opt !== 'hash' && $opt !== 'flip')){ return false;}
                        if($opt == 'hash'){ $opt = 'replace';}
                        $cached = array();
                        $tricky = ['島', '嶼', '鄉', '市', '鎮', '區', '村', '裡', '路', '新市', '阿里山', '鎮興裡平'];
                        $cached['hash'] = array_reduce($tricky, static function ($acc, $unit) {
                            return $acc + [$unit => md5($unit)];
                        }, []);

                        $cached['flip'] = array_flip($cached['hash']);
                        $cached['replace'] = [
                            '島鄉' => $cached['hash']['島'].'鄉',
                            '嶼鄉' => $cached['hash']['嶼'].'鄉',
                            '村鄉' => $cached['hash']['村'].'鄉',
                            '裡鄉' => $cached['hash']['裡'].'鄉',
                            '村市' => $cached['hash']['村'].'市',
                            '裡區' => $cached['hash']['裡'].'區',
                            '鎮區' => $cached['hash']['鎮'].'區',
                            '裡鎮' => $cached['hash']['裡'].'鎮',
                            '裡村' => $cached['hash']['裡'].'村',
                            '鄉村' => $cached['hash']['鄉'].'村',
                            '路鄉' => $cached['hash']['路'].'鄉',
                            '新市區' => $cached['hash']['新市'].'區',
                            '阿里山鄉' => $cached['hash']['阿里山'].'鄉',
                            '鎮興裡平鎮' => $cached['hash']['鎮興裡平'].'鎮',
                        ];

                        //return strtr($token, $cached['flip']);
                        return $cached[$opt];
                    }

                    
                    //@param  Normalizer  $normalizer
                    //@return Normalizer
                    function lodash_hash($address){//Tricky.php->hash
                        //return lodash_replace(norm_trickypart('replace'), null, $address);
                        return strtr($address, norm_trickypart('hash'));
                    }
                    
                    //@param  string  $token
                    //@return string
                    function lodash_flip($address){//Tricky.php->flip
                        //return lodash_replace(norm_trickypart('replace'), null, $address);
                        return strtr($address, norm_trickypart('flip'));
                    }



                    function lodash_replace($regexp, $replacement = null, $address = null){///lodash/src/JString/Javascript.php->replace
                        if (is_array($regexp) === true && is_null($replacement) === true) {
                            return strtr($address, $regexp);
                        }

                        if (is_callable($replacement) === true) {
                            return preg_replace_callback($regexp, $replacement, $address);
                        }

                        return preg_replace($regexp, $replacement, $address);
                    }


                     function flat($length = null, $offset = 0, $tokens){//Address.php->flat
                        //$tokens = $this->tokens();
                        $length = $length ?: count($tokens);
                        $end = $offset + $length;

                        $tokens = slice($offset, $end, $tokens);
                        $res = array_map(function ($token) {
                            return implode('', $token);
                        }, $tokens);
                        return implode('', $res);
                        
                        //return (string) $tokens->slice($offset, $end)->map(function ($token) {
                        //    return implode('', $token);
                        //})->join('');
                        
                    }

                    function slice($begin, $end = null, $array){//lodash/src/JArray/Javascript.php
                        //$array = $this->getArrayCopy();
                        //$length = $this->length();
                        $length = count($array);
                        $begin = $begin < 0 ? $length + $begin : $begin;

                        if (is_null($end) === true) {
                            //return new static(array_slice($array, $begin, $length - $begin));
                            return array_slice($array, $begin, $length - $begin);
                        }

                        $end = $end < 0 ? $length + $end : $end;
                        $end -= $begin;

                        return array_slice($array, $begin, $end);
                    }




                    function toNumber($cNum){///lodash/src/JString/Extensions/Chinese.php->toNumber
                        $len = mb_strlen($cNum);
                        $cTOnumObj = [
                            '零' => '0',
                            '〇' => '0',
                            '一' => '1',
                            '二' => '2',
                            '三' => '3',
                            '四' => '4',
                            '五' => '5',
                            '六' => '6',
                            '七' => '7',
                            '八' => '8',
                            '九' => '9',
                            '十' => '10'
                        ];
                        //strtr($address, $regexp);
                        if($len == 1){ return strtr($cNum, $cTOnumObj); }
                        else if($len==2){
                            if(mb_substr($cNum, 0, 1) == "十"){
                                    $cTOnumObj['十']="1";
                                    return strtr($cNum, $cTOnumObj);
                                }
                            else{
                                    $cTOnumObj['十']="0";
                                    return strtr($cNum, $cTOnumObj);
                                }
                        }
                        else{
                            $cTOnumObj['十']="";
                            return strtr($cNum, $cTOnumObj);
                        }

                    }
*/

                ?>
            </div>
        </div>
    </div>
<script>
$(function () {
    var anc = parseInt('<?php echo $dataanchor; ?>');
    var container = $('#tablediv');
    /*
    var scrollTo = container.find('#'+anc);
    container.animate({
        scrollTop: scrollTo.offset().top
        - container.offset().top  
        + container.scrollTop() - 88
    }, 500);
    */

    container.animate({
        scrollTop: anc
        //- container.offset().top  
        //+ container.scrollTop() - 88
    }, 500, function() {
        $('#analysisMask').hide();
    });
    

})

</script>
</body>
</html>