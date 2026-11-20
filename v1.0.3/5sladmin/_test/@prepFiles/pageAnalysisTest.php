<?php
##############################################################
################### 5SeasonLife 後備作業 #########################
############### 自資料庫讀取並生成 CSV 工作檔案 ###################
##############################################################

error_reporting(E_ALL); 
session_start();
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
$doact = isset($_POST['doact']) ? $_POST['doact'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	
  <title> 人生第五季 Fifth Season Life 測試檔案</title>
  </head>
<body>


<?php

/*
    $res = $DB->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ?",array('data_buildcase'));

    foreach($res as $key => $value) {
        echo "'XXXXXXXX\\r\\n".$value['COLUMN_NAME']."',<br>";
    }



    //echo "<pre>";
    //print_r($res);
    //echo "</pre>";
    exit();
*/
    /*
    $getBCid = 12794;//Wejoin 課程商品ID
    $getWJdata =_fn_wejoin_infoByID($getBCid);//取得 Wejoin 網頁中課程商品內容

    echo "<pre>";
    print_r($getWJdata);
    echo "</pre>";
    exit();
    */


    //大華理想家二期//首府經貿B棟//佳茂6962御景莊園//鹿鳴館-山嵐區//微風VILLA
    $orignalTitle = '恆美第2~3期(山水居)';
    $getPureTitle = $orignalTitle;//主要名稱解析
    $getSubTitle1 = '';//別名1
    $getSubTitle2 = '';//別名2
    //$getSubTitle3 = '';//別名2
    $getPurePrefix = '';//## 建商名稱或附加名稱 ## 如「遠雄」、「威京」、「興富發」、「昇陽」...等
    $getPurePhase = '';//## 期數 ## 捨去單位如「期」「部」「座」...等
    $getPureExt = '';//## 後綴 ## 「大樓」「華廈」「別墅」「透天」「公寓」「座」「棟」「區」「特區」「樂章」「篇」...等附加分類或辨識用字O

    //$pureTitleSplit = preg_split('/([0-9]{1,2}|[〇一二三四五六七八九十百]+)期/',$getPureTitle);
    //preg_match_all('/([0-9]{1,2}|[ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ])期/u', $getPureTitle, $match);

    $getPureTitle = _fn_str_f2h($getPureTitle);//全形字轉半形字用

    preg_match_all('/([0-9]{1,2}|[ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ])~([0-9]{1,2}|[ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ])/', $getPureTitle, $match);
    echo "<pre>";
    print_r($match);
    echo "</pre>";
    exit();



    preg_match_all('/第?\s?([0-9]{1,2}|[ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ])\s?期/', $getPureTitle, $match);
        echo "<pre>";
        print_r($match);
        echo "</pre>";
        exit();


    $getPureTitle = _fn_str_removepunc($getPureTitle);
    //以「期數」分隔字串，同時去除「第」字元以及「期」字元，可作陣列操作
    if($getPureTitle = preg_split('/第?\s?([0-9]{1,2}|[ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ])\s?期\s?/u', $getPureTitle, -1, PREG_SPLIT_DELIM_CAPTURE)){
        echo "<pre>";
        print_r($getPureTitle);
        echo "</pre>";
        exit();

    }

exit();

    //FIXME: 請思考去除所有空白的必要性（兩單詞以上的英文名稱將被破壞）
    //移除所有空白
    //$getPureTitle = func_stripewhite($getPureTitle);
    //所有全形字轉半形字（統一為半形字以利精確比對）
    $getPureTitle = _fn_str_f2h($getPureTitle);
    //修正以英文字母拼湊成的羅馬數字回歸正體羅馬數字（超過半數的羅馬數字被以大寫英文字母誤寫）
    $getPureTitle = _fn_str_letter2rom($getPureTitle);
    //英文大寫轉小寫（統一為小寫以利精確比對）
    // NOTE:或是使用/i 忽略大小寫
    //$getPureTitle = _fn_str_maj2min($getPureTitle);
    //----------------- 「 () 」括號內字串處理 ----------------
    if (strpos($getPureTitle, '(')) {//當原名稱包含括號時
        preg_match_all('/\((.*?)\)/', $getPureTitle, $match);//提取「所有」左右括號之間的內容並定義
        $getPureTitle = preg_replace('/\((.*?)\)/', '', $getPureTitle);//定義爲變數後即可移除「所有」括號以及括號間內容
        if(!empty($match[1])) {//當括號內容不為空時
            if (_fn_str_extifexist($match[1])) {//當括號內容包含「別墅」、「大樓」、「區」...等標注時
                $getPureExt = implode(",", $match[1]);//將括號內容以逗號連接，此時單組結果沒有逗號，而兩組以上的結果將以逗號分開
            } else {//比對不到含「「別墅」、「大樓」、「區」...等標注時，只取陣列前兩值（因為僅預留兩欄位），分別定義別名欄位
                $getSubTitle1 = $match[1][0];//定義「別名1」變數
                $getSubTitle2 = $match[1][1];//定義「別名2」變數
            }
        }
    }
    //----------------- 「 - 」破折號前後內容分隔處理 ----------------
    if (preg_match('/\W\-\W/i', $getPureTitle)) {//當原名稱包含破折號，且前後字元不為數字以及字母時
        $getPurePhaseSplit = explode('-', $getPureTitle);//以破折號分隔原名稱字串
        $getPureTitle = $getPurePhaseSplit[0];//暫時定義前段內容為主要名稱結構（可能需要再處理）
        $getPurePhase = $getPurePhaseSplit[0];//暫時定義後段內容為期數，或包含期數的字串（可能需要再處理）
        if(_fn_str_extifexist($getPurePhaseSplit[1])){
            $getPureExt = $getPurePhaseSplit[1];
        } else {
            $getSubTitle1 = $getPurePhaseSplit[1];
        }
    }

    //移除指定的標點符號（處理完破折號後，其餘標點符號可能成為比對失敗的因素）
    $getPureTitle = _fn_str_removepunc($getPureTitle);//移除指定的標點符號（部分標點符號將被保留）

    if(preg_match('/no([0-9]{1,2}|\s[0-9]{1,2})/i', $getPureTitle)){//以判斷式判斷是否包含 「no+數字」或「no+空白+數字」 的字串，若是包含...
        $pureTitleSplit = preg_split('/no(?=[0-9]{1,2}|\s[0-9]{1,2})/i', $getPureTitle);//以「no」字串分隔前後斷
        $getPureTitle = $pureTitleSplit[0];//暫時定義前段內容為主要名稱結構（可能需要再處理）
        $getPurePhase = $pureTitleSplit[1];//暫時定義後段內容為期數，或包含期數的字串（可能需要再處理）
    } elseif(preg_match('/([0-9]{1,2}|Ⅰ|Ⅱ|Ⅲ|Ⅳ|Ⅴ|Ⅵ|Ⅶ|Ⅷ|Ⅸ|Ⅹ)期/i', $getPureTitle)){//當包含字元「期」，並且之前的字元為二位以內數字或是羅馬數字時
        if(strpos($getPureTitle,'第')){
            $pureTitleSplit = explode('第',$getPureTitle);
            $getPureTitle =  $pureTitleSplit[0];
            $getPurePhase = $pureTitleSplit[1];
        } else {
            $pureTitleSplit = preg_split('/([0-9]{1,2}|[〇一二三四五六七八九十百]+)期/',$getPureTitle);
            //$pureTitleSplit = preg_split('/[0-9]{1,2}期|Ⅰ期|Ⅱ期|Ⅲ期|Ⅳ期|Ⅴ期|Ⅵ期|Ⅶ期|Ⅷ期|Ⅸ期|Ⅹ期|一期|二期|三期|四期|五期|六期|七期|八期|九期|十期|十一期|十二期|十三期|十四期|十五期|十六期|十七期|十八期|十九期|二十期|二十一期|二十二期|二十三期|二十四期/',$getPureTitle);
            $getPurePhase = str_replace($pureTitleSplit[0],'',$getPureTitle);
            $getPureTitle =  $pureTitleSplit[0];
        }
    } else {
        $pureTitleSplit = preg_split('/([0-9]{1,2}|Ⅰ|Ⅱ|Ⅲ|Ⅳ|Ⅴ|Ⅵ|Ⅶ|Ⅷ|Ⅸ|Ⅹ|一|二|三|四|五|六|七|八|九|十|十一|十二|十三|十四|十五|十六|十七|十八|十九|二十|二十一|二十二|二十三|二十四)期/',$getPureTitle);
        $getPurePhase = str_replace($pureTitleSplit[0],'',$getPureTitle);
        $getPureTitle =  $pureTitleSplit[0];
    }

    if(strpos($getPurePhase,'期')){
        $getPurePhaseSplit = explode("期",$getPurePhase);
        $getPurePhase = $getPurePhaseSplit[0];
        $getPureExt = $getPurePhaseSplit[1];
    }else{
        $getPurePhaseSplit = preg_split('/^[0-9]{1,2}|Ⅰ|Ⅱ|Ⅲ|Ⅳ|Ⅴ|Ⅵ|Ⅶ|Ⅷ|Ⅸ|Ⅹ/',$getPurePhase);
        if(!empty($getPurePhaseSplit[1])){
            $getPurePhase = str_replace($getPurePhaseSplit[1],'',$getPurePhase);
            $getPureExt = $getPurePhaseSplit[1];
        }
    }
    $getPureTitle = trim($getPureTitle);
    $getPurePhase = trim($getPurePhase);
    if(!is_numeric($getPurePhase)){
        $getPurePhase = _fn_num_b2h($getPurePhase);
    }

    $getPureExt = trim($getPureExt);
    $return = array('title'=>$getPureTitle, 'getSubTitle1'=>$getSubTitle1, 'getSubTitle2'=>$getSubTitle2, 'phase'=>$getPurePhase,'ext'=>$getPureExt);


    echo "<pre>";
    print_r($return);
    echo "</pre>";
    exit();



/*
*/
?>
</body>
</html>
