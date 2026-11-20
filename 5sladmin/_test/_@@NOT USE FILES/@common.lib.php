<?php

///////////////////////////////system functions/////////////////////////////////////
//取得客戶端瀏覽器種類
function _FUN_GET_BROWSER()
{
}
//取得客戶端操作平台
function _FUN_GET_OS()
{
}
//取得客戶端IP位置
function _FUN_GET_IPADD()
{
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip=getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $ip=getenv('HTTP_CLIENT_IP');
        } else {
            $ip=getenv('REMOTE_ADDR');
        }
    }
    return $ip;
}
/////////////////////////////// Configurations //////////////////////////////////////


/////////////////////////////// Date & Time ////////////////////////////////////////
//返回指定星期值的中文([星期(0-6)],[完整顯示])
function _FUN_WEEK_BIG5($weeK, $fulL=false)
{
    $getWEEK[0]='日';
    $getWEEK[1]='一';
    $getWEEK[2]='二';
    $getWEEK[3]='三';
    $getWEEK[4]='四';
    $getWEEK[5]='五';
    $getWEEK[6]='六';
    $getHEAD=$fulL==true ? '星期' : '';
    return $getHEAD.$getWEEK[$weeK];
}
//返回指定日期(YYYYMMDD)處於特定期間內布林值([起始日],[終止日])
function _FUN_INDATE($startDATE, $endDATE)
{
    $nowDATE=date('Ymd', CF_NOWTIME);
    if (empty($startDATE)==true&&empty($endDATE)==true) {
        return true;
    } else {
        if (!empty($startDATE)&&!empty($endDATE)) {
            if ($startDATE <= $nowDATE && $endDATE >= $nowDATE) {
                return true;
            } else {
                return false;
            }
        } elseif (!empty($startDATE)&&empty($endDATE)==true) {
            if ($startDATE <= $nowDATE) {
                return true;
            } else {
                return false;
            }
        } elseif (empty($startDATE)==true&&!empty($endDATE)) {
            if ($endDATE >= $nowDATE) {
                return true;
            } else {
                return false;
            }
        }
    }
}
//指定日期(YYYYMMDD)轉正式日期時間([日期],[分格符])
function _FUN_SHOWDATE($datE, $sP)
{
    if (!empty($datE)&&!empty($sP)) {
        return substr($datE, 0, 4).$sP.substr($datE, 4, 2).$sP.substr($datE, 6, 2);
    } else {
        return $datE;
    }
}
//時間格式轉換([日期],[分格],[格式 (YYYYMMDD,YYYYMMDDHHMM,YYYYMMDDhhmmss)])
function _FUN_DATEFORMAT($datE, $sP, $typE)
{
    $yR=date('Y', strtotime($datE));
    $mN=date('m', strtotime($datE));
    $dY=date('d', strtotime($datE));
    $hR=date('H', strtotime($datE));
    $mI=date('i', strtotime($datE));
    $sD=date('s', strtotime($datE));
    if ($typE=='YYYYMMDD') {
        $rtn=$yR.$sP.$mN.$sP.$dY;
    } elseif ($typE=='YYYYMMDDhhmm') {
        $rtn=$yR.$sP.$mN.$sP.$dY.' '.$hR.':'.$mI;
    } elseif ($typE=='YYYYMMDDhhmmss') {
        $rtn=$yR.$sP.$mN.$sP.$dY.' '.$hR.':'.$mI.':'.$sD;
    } else {
        $rtn=$datE;
    }
    return $rtn;
}
//依TimeStamp取得當日日期 ([時間戳])
function _FUN_DAY_NUM($datE)
{
    if (empty($datE)==true) {
        return 0;
    }
    if (date("n", $datE)==2) {
        if (date("Y", $datE)%4==0) {
            return 29;
        } else {
            return 28;
        }
    } elseif (date("n", $datE)==4||date("n", $datE)==6||date("n", $datE)==9||date("n", $datE)==11) {
        return 30;
    } else {
        return 31;
    }
}
/////////////////////////////// File & Folder /////////////////////////////////////
//取得檔案附檔名或HEADER _FUN_FILE_TYPE([檔案名稱],[附檔名(none or false|HEADER true)])
function _FUN_FILE_TYPE($filE, $heaD=false)
{
    $tempARR = explode('.', $filE);
    $tempTYPE = strtolower($tempARR[sizeof($tempARR)-1]);
    if ($tempTYPE !== false) {
        if ($heaD==true) {
            $fileHEADS = array(
                "jpg" => "image/pjpeg" ,
                "jpge" => "image/pjpeg" ,
                "png" => "image/x-png" ,
                "bmp" => "image/bmp" ,
                "gif" => "image/gif" ,
                "wmv" => "video/x-ms-wmv" ,
                "mp3" => "audio/mpeg" ,
                "mpg" => "video/mpg" ,
                "mpeg" => "video/mpg" ,
                "rm" => "application/vnd.rn-realmedia" ,
                "swf" => "application/x-shockwave-flash" ,
                "asf" => "video/x-ms-asf" ,
                "asx" => "video/x-ms-asf" ,
                "avi" => "video/avi" ,
                "rmvb" => "application/vnd.rn-realmedia-vbr" ,
                "txt" => "text/plain" ,
                "doc" => "application/msword" ,
                "elx" => "application/vnd.ms-excel" ,
                "pdf" => "application/pdf" ,
                "ppt" => "application/vnd.ms-powerpoint" ,
                "html" => "text/html" ,
                "htm" => "text/html" ,
                "zip" => "application/x-zip-compressed"
            );
            $rtn=$fileHEADS[$tempTYPE];
            return !empty($rtn) ? $rtn : 'application/octet-stream';
        } else {
            return $tempTYPE;
        }
    } else {
        return false;
    }
}
function _FUN_FILE_BASETITLE($filE)
{
    $tempARR = explode('.', $filE);
    return !empty($tempARR[0]) ? $tempARR[0] : false;
}
/////////////////////////////// PHOTO PROCESSES ////////////////////////////////////
/*
function _FUN_IMAGE_SHOW($elM='',$patH='',$newW=0,$newH=0,$jsCLICK='',$alT='',$loadeR=false){
    $imgINFO=_FUN_IMAGE_NEWSIZE($patH,$newW,$newH);
    $rtn='<div class="bF bgMLIGHT" style="width:'.$newW.'px;height:'.$newH.'px;overflow:hidden;position:relative;">';
    if($loadeR==true){
        $rtn.='
            <div id="'.$elM.'_MASK" class="bgW posA bgOP50 dispN" style="width:'.$newW.'px;height:'.$newH.'px;z-index:1001;left:0px;top:0px;">
              <table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td class="taC"><img src="'.DOC_PATH_LAYER.'imageFiles/loading_24x24.gif"></td>
                </tr>
              </table>
            </div>
        ';
    }
    if(!empty($jsCLICK)) $rtn.='<div id="'.$elM.'_LINK" onclick="'.$jsCLICK.'" style="width:'.$newW.'px;height:'.$newH.'px;position:absolute;z-index:1000;cursor:pointer;" title="'.$alT.'" alt="'.$alT.'"><img src="'.DOC_PATH_LAYER.'imageFiles/BK_blank2x2.gif" width="'.$newW.'" height="'.$newH.'"></div>';
    $rtn.='
            <div id="'.$elM.'_IMG">
              <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="'.(!empty($imgINFO['WIDTH']) ? $imgINFO['WIDTH'] : $newW).'" height="'.(!empty($imgINFO['HEIGHT']) ? $imgINFO['HEIGHT'] : $newH).'">
                <param name="movie" value="'.$patH.'?t='.CF_NOWTIME.'">
                <param name="wmode" value="opaque">
                <!--[if !IE]>-->
                <object type="application/x-shockwave-flash" wmode="opaque" data="'.$patH.'?t='.CF_NOWTIME.'" width="'.(!empty($imgINFO['WIDTH']) ? $imgINFO['WIDTH'] : $newW).'" height="'.(!empty($imgINFO['HEIGHT']) ? $imgINFO['HEIGHT'] : $newH).'"></object>
                <!--<![endif]-->
              </object>
            </div>
    ';
    $rtn.='</div>';
    return $rtn;
}
*/
/////////////////////////////// String /////////////////////////////////////////////
//UTF8字串顯示限制 ([原始字串],[顯示字數],[尾端加註...])
function _FUN_UTF8_TEXTCUT($sourceSTR, $cutLEN, $appenD=false)
{
    $returnSTR='';
    $i=0;
    $n=0;
    $stringLEN=strlen($sourceSTR);//字符串的字節數
    while (($n<$cutLEN) && ($i<=$stringLEN)) {//001
        $tempSTR=substr($sourceSTR, $i, 1);
        $ascNUM=Ord($tempSTR);//得到字符串中第$i位字符的ascii碼
        if ($ascNUM>=224) { //如果ASCII位高與224，
            $returnSTR.=substr($sourceSTR, $i, 3); //根據UTF-8編碼規範，將3個連續的字符計為單個字符
            $i=$i+3; //實際Byte計為3
            $n++; //字串長度計
        } elseif ($ascNUM>=192) {//如果ASCII位高與192，
            $returnSTR.=substr($sourceSTR, $i, 2); //根據UTF-8編碼規範，將2個連續的字符計為單個字符
            $i=$i+2; //實際Byte計為2
            $n++; //字串長度計1
        } elseif ($ascNUM>=65 && $ascNUM<=90) {//如果是大寫字母，
            $returnSTR.=substr($sourceSTR, $i, 1);
            $i=$i+1; //實際的Byte數仍計1個
            $n=$n+0.75; //但考慮整體美觀，大寫字母計成0.75個高位字符
        } else {//其他情況下，包括小寫字母和半角標點符號，
            $returnSTR.=substr($sourceSTR, $i, 1);
            $i=$i+1; //實際的Byte數計1個
            $n=$n+0.5; //小寫字母和半角標點等與半個高位字符寬...
        }
    }//001
    if ($appenD==true&&$stringLEN>$cutLEN) {
        $returnSTR .= "…";
    }//超過長度時在尾處加上省略號
    return $returnSTR;
}
//UTF8字串分行顯示 ([原始字串],[顯示字數],[顯示行數],[尾端加註...])
function _FUN_UTF8_LINESTR($sourceSTR, $cutLEN, $lineNUM, $appenD=false)
{
    $returnSTR='';
    $aRR=array();
    $i=0;
    $n=0;
    $l=1;
    $stringLEN=strlen($sourceSTR);//字符串的字節數
    while ($i<=$stringLEN) {//001
        $tempSTR=substr($sourceSTR, $i, 1);
        $ascNUM=Ord($tempSTR);//得到字符串中第$i位字符的ascii碼
        if ($ascNUM>=224) { //如果ASCII位高與224，
            $returnSTR.=substr($sourceSTR, $i, 3); //根據UTF-8編碼規範，將3個連續的字符計為單個字符
            $i=$i+3; //實際Byte計為3
            $n++; //字串長度計1
        } elseif ($ascNUM>=192) {//如果ASCII位高與192，
            $returnSTR.=substr($sourceSTR, $i, 2); //根據UTF-8編碼規範，將2個連續的字符計為單個字符
            $i=$i+2; //實際Byte計為2
            $n++; //字串長度計1
        } elseif ($ascNUM>=65 && $ascNUM<=90) {//如果是大寫字母
            $returnSTR.=substr($sourceSTR, $i, 1);
            $i=$i+1; //實際的Byte數仍計1個
            $n++; //但考慮整體美觀，大寫字母計成一個高位字符
        } else {//其他情況下，包括小寫字母和半角標點符號，
            $returnSTR.=substr($sourceSTR, $i, 1);
            $i=$i+1; //實際的Byte數計1個
            $n=$n+0.5; //小寫字母和半角標點等與半個高位字符寬...
        }
        if ($n==$cutLEN||$i==$stringLEN) {
            if ($l==$lineNUM&&$appenD==true) {
                $returnSTR.='...';
            }
            array_push($aRR, $returnSTR);
            if ($l==$lineNUM) {
                break;
            } else {
                $returnSTR='';
                $l++;
                $n=0;
            }
        }
    }//001
    return $aRR;
}

//----------------------------------- CURRENCY INFO -------------------------------------
function _GET_CURR_PRICE($vaL, $ratE, $deC, $eliM=0, $spL=1)
{//$eliM 0-保留0 1-去除0  $spL 0-不加','  1-加','
    $getSPL="";
    if (!empty($vaL)) {
        $getSPL=intval($spL)==0 ? "" : ",";
        $getVAL=number_format($vaL/$ratE, intval($deC), '.', $getSPL);
        if ($deC>0&&$eliM==1) {
            $valARR=@explode('.', $getVAL);
            if (intval($valARR[1])==0) {
                $getVAL=$valARR[0];
            } else {
                $post="";
                for ($i=1;$i<=$deC;$i++) {
                    if (intval(substr($valARR[1], 0-$i, 1))>0) {
                        $post=substr($valARR[1], 0, ($deC+1)-$i);
                        break;
                    }
                }
                $getVAL=!empty($post) ? $valARR[0].".".$post : $valARR[0];
            }
        }
        return $getVAL;
    } else {
        return $eliM==1 ? 0 : number_format(0, intval($deC), '.', $getSPL);
    }
}
//----------------------------------- ONLINE PAYMENT -------------------------------------
function _GET_PAY_TYPE($typE)
{
    switch ($typE) {
        case 1: return '信用卡'; break;
        case 2: return '虛擬帳號轉帳'; break;
        case 4: return 'WebATM'; break;
        case 8: return '便利商店'; break;
        case 9: return '7-11 ibon付款'; break;
        case 10: return '中國銀聯卡'; break;
        case 11: return 'PayPal'; break;
        case 12: return '全家 FamiPort'; break;
        default: return false; break;
    }
}
//----------------------------------- ONLINE PAYMENT -------------------------------------


//-- --------------------------------------------------------------------------------- --//
//-- -------------------------------- New Functions----------------------------------- --//
//-- --------------------------------------------------------------------------------- --//
function func_logToString($stR, $params = null)
{
    foreach ($params as $parameter_key => $parameter) {
        $array_parameter_found = true;
        $stR = preg_replace("/@".$parameter_key."/", $parameter, $stR);
        // removes array form $params
        unset($params[$parameter_key]);
    }
    return $stR;
}

function func_getPageGroup($ni)
{
    $strARR[1]="管理首頁控制台";
    $strARR[2]="系統設定管理";
    $strARR[3]="會員管理";
    $strARR[4]="課程商品管理";
    $strARR[5]="知識庫文章管理";
    $strARR[6]="訊息傳遞管理";
    $strARR[7]="會員活動服務管理";
    $strARR[8]="廣告登載投放管理";
    $strARR[9]="財務會計資料管理";
    if (isset($strARR[$ni])) {
        return $strARR[$ni];
    } else {
        return false;
    }
}

function _fn_getLogString($rVal, $oStr)
{
    //$replaceVal = array("number","performed");
    //$str = "If passed, this will be set to the ?1 of replacements ?2.";
    for ($i=0;$i<count($rVal);$i++) {
        $sVal[$i]='?'.$i+1;
    }
    $newString = str_replace($sVal, $rVal, $oStr);
    return $newString;
}
//------------------
function _fn_token_verify($uid='', $key='', $time='', $token='')
{
    if (empty($uid) || empty($key) || empty($time) || empty($token)) {
        return array('exit'=>true,'rtn'=>json_encode(array('done'=>0, 'code'=>'0000', 'des'=>'遺失 TOKEN 驗證值，表單傳送失敗！請聯絡相關管理人員！！')));
    } else {
        $v_recall = $uid.'|'._fn_secure_crypto($key, 'de').'|'.$time;
        $v_recall = _fn_secure_crypto($v_recall);
        if ($token != $v_recall) {
            return array('exit'=>true,'rtn'=>json_encode(array('done'=>0, 'code'=>'0000', 'des'=>'TOKEN 驗證值比對不符，表單傳送失敗！請聯絡相關管理人員！！')));
        } else {
            if (time() > $time) {
                return array('exit'=>true,'rtn'=>json_encode(array('done'=>0, 'code'=>'0000', 'des'=>'TOKEN 使用期限已過，表單傳送失敗！請重新登入！！')));
            }
        }
    }
    return array('exit'=>false);
}
function _fn_FH_crypto($str, $crypto='de')
{
    $passCode = 'flushfullhouse';
    $encryptMode = 'AES-256-CBC';
    $keyCode = substr(hash('sha256', $passCode, true), 0, 32);
    //$ivCode = chr(112).chr(105).chr(103).chr(103).chr(121).chr(115).chr(101).chr(118).chr(101).chr(114).chr(121).chr(119).chr(104).chr(101).chr(114).chr(101);//piggyseverywhere
    $ivCode = chr(80).chr(105).chr(103).chr(103).chr(121).Chr(84).Chr(97).Chr(105).Chr(108).Chr(79).Chr(110).Chr(77).Chr(121).Chr(67).Chr(97).Chr(116);//PiggyTailOnMyCat
    if ($crypto=='de') {
        $crypted = openssl_decrypt(base64_decode($str), $encryptMode, $keyCode, OPENSSL_RAW_DATA, $ivCode);
    } else {
        $crypted = base64_encode(openssl_encrypt($str, $encryptMode, $keyCode, OPENSSL_RAW_DATA, $ivCode));
    }
    return empty($crypted) || $crypted==false ? '' : $crypted;
}
function _fn_FHMD5_encrypt($str)
{
    $origString=md5($str);
    $saltH = substr($origString, 0, 12);
    $saltT = substr($origString, -12);
    return md5($saltH.$str.$saltT);
}

//------------------
function func_stripewhite($stR)
{
    return preg_replace("/\s+/", "", $stR);
}
//取得圖像指定等比長寬 ([圖像路徑],[指定寬],[指定長])
function _fn_image_newsize($patH, $newW, $newH)
{
    list($Mwidth, $Mheight)= @getimagesize($patH);
    $landscape=$Mwidth>$Mheight ? 1 : 0;
    $nMwidth=$newW;
    $nMheight=ceil(($nMwidth*$Mheight)/$Mwidth);
    if ($nMheight<$newH) {
        $nMheight=$newH;
        $nMwidth=floor(($Mwidth*$nMheight)/$Mheight);
    }
    $arr=array('LANDSCAPE'=>$landscape,'WIDTH'=>$nMwidth,'HEIGHT'=>$nMheight);
    return $arr;
}

function _fn_resize_image($file, $target, $w, $h, $crop=false)
{
    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width-($width*abs($r-$w/$h)));
        } else {
            $height = ceil($height-($height*abs($r-$w/$h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        if ($w/$h > $r) {
            $newwidth = $h*$r;
            $newheight = $h;
        } else {
            $newheight = $w/$r;
            $newwidth = $w;
        }
    }
    $src = @imagecreatefromjpeg($file);
    $dst = @imagecreatetruecolor($newwidth, $newheight);
    @imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    @imagejpeg($dst, $target, 75);
    return $dst;
}
 //$img_to_resize = _fn_resize_image(‘path-to-jpg-image’, 250, 250);

 function _fn_resize_crop($source_file, $dst_dir, $max_width, $max_height, $quality = 80)
 {
     $imgsize = getimagesize($source_file);
     $width = $imgsize[0];
     $height = $imgsize[1];
     $mime = $imgsize['mime'];
     switch ($mime) {
        case 'image/gif':
            $image_create = "imagecreatefromgif";
            $image = "imagegif";
            break;
        case 'image/png':
            $image_create = "imagecreatefrompng";
            $image = "imagepng";
            $quality = 7;
            break;
        case 'image/jpeg':
            $image_create = "imagecreatefromjpeg";
            $image = "imagejpeg";
            $quality = 80;
            break;
        default:
            return false;
            break;
    }
     $dst_img = @imagecreatetruecolor($max_width, $max_height);
     $src_img = $image_create($source_file);
     $width_new = $height * $max_width / $max_height;
     $height_new = $width * $max_height / $max_width;
     if ($width_new > $width) {
         $h_point = (($height - $height_new) / 2);
         @imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
     } else {
         $w_point = (($width - $width_new) / 2);
         @imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
     }
     $image($dst_img, $dst_dir, $quality);
     if ($dst_img) {
         @imagedestroy($dst_img);
     }
     if ($src_img) {
         @imagedestroy($src_img);
     }
     return $dst_img;
 }
//全形字轉半形字用
function _fn_str_f2h($str)
{
    //全形英數字及符號
    $f=array("　","０","１","２","３","４","５","６","７","８","９","Ａ","Ｂ","Ｃ","Ｄ","Ｅ","Ｆ","Ｇ","Ｈ","Ｉ","Ｊ","Ｋ","Ｌ","Ｍ","Ｎ","Ｏ","Ｐ","Ｑ","Ｒ","Ｓ","Ｔ","Ｕ","Ｖ","Ｗ","Ｘ","Ｙ","Ｚ","ａ","ｂ","ｃ","ｄ","ｅ","ｆ","ｇ","ｈ","ｉ","ｊ","ｋ","ｌ","ｍ","ｎ","ｏ","ｐ","ｑ","ｒ","ｓ","ｔ","ｕ","ｖ","ｗ","ｘ","ｙ","ｚ","～","！","＠","＃","＄","％","＾","＆","＊","（","）","＿","＋","｜","‘","－","＝","＼","｛","｝","〔","〕","：","”","；","’","＜","＞","？","，","．","／");
    //半形英數字及符號
    $h=array(" ","0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","~","!","@","#","\$","%","^","&","*","(",")","_","+","|","`","-","=","\\\\","{","}","[","]",":","\"",";","'","<",">","?",",",".","/");//注意"\\\\"為四條
    return str_replace($f, $h, $str);
}

//英文小寫轉大寫
function _fn_str_min2maj($str)
{
    $min=array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
    $maj=array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
    return str_replace($min, $maj, $str);
}

//Google Map 相關
//合成完整住址
function _fn_get_fulladdress($citY='', $disT='', $adD='')
{
    $getCityarea = '';
    $getOnlyAdd = '';
    $getFullAdd = '';
    $getGEOAdd = '';
    if (empty($citY)&&empty($disT)&&empty($adD)) {
        return false;
    }
    if (!empty($adD)) {
        $getOnlyAdd = preg_replace("/\s+/", "", _fn_str_f2h($adD));
    }
    if (!empty($citY) && !empty($disT)) {
        $locJS=_fn_getCachedJsonDatac("../jsonFiles/locations.json");
        $getCityarea = $locJS[$citY]['text'].$locJS[$citY]['district'][$disT];
        $getFullAdd = $getCityarea.$getOnlyAdd;
    }
    if (!empty($getFullAdd)) {
        $getGEOAdd=strchr($getFullAdd, "號", true)."號";
    }
    return array("cityarea"=>$getCityarea,"addonly"=>$getOnlyAdd,"fulladd"=>$getFullAdd,"geoadd"=>$getGEOAdd);
}
//API 取得座標
function _fn_get_geocode($adD='')
{
    if (!empty($adD)) {
        $adD = urlencode($adD); //URL編碼
        $url = "https://maps.googleapis.com/maps/api/geocode/json?sensor=false&address={$adD}&key=AIzaSyDB3bxYzRdZxv_F6jiBJJh-PmrCHsSqZdw&language=zh-TW";
        $mapJS = _fn_getCachedJsonDatac($url);
        if ($mapJS['status']=='OK') {//狀態條件
            // 取得需要值
            $lat = isset($mapJS['results'][0]['geometry']['location']['lat']) ? $mapJS['results'][0]['geometry']['location']['lat'] : "";
            $lng = isset($mapJS['results'][0]['geometry']['location']['lng']) ? $mapJS['results'][0]['geometry']['location']['lng'] : "";
            $formatted_address = isset($mapJS['results'][0]['formatted_address']) ? $mapJS['results'][0]['formatted_address'] : "";
            // 檢查確認資料完整
            if ($lat && $lng && $formatted_address) {
                // 置入陣列
                $geoARR = array(
                    "lat" => $lat,
                    "lng" => $lng,
                    "fadd" => $formatted_address
                );
                //$data_arr = array();
                //array_push($data_arr, $lat, $lng, $formatted_address);
                return $geoARR;
            }
        }
    } else {
        $geoARR = array(
            "lat" => '',
            "lng" => '',
            "fadd" => ''
        );
        return $geoARR;
    }
    return false;
}
//------------------ SMS Options ------------------------
function _fn_SMS_messageSend($id, $to, $mess='', $delay='')
{
    if (empty($id)||empty($to)||empty($mess)) {
        $rtn = array('smsdone'=>'0','smsdes'=>'遺失重要傳遞值，請重新操作!!');
        return false;
    }
    $curl = curl_init();
    // url
    $url = 'https://smsapi.mitake.com.tw/api/mtk/SmSend?';//https://smsapi.mitake.com.tw/api/mtk/SmSend?CharsetURL=UTF-8&username=90113379SMS&password=Sms90113379&dstaddr=0919218661&smbody=test
    $url .= 'CharsetURL=UTF-8';
    // parameters
    $data = 'username=90113379SMS';
    $data .= '&password=Sms90113379';
    $data .= '&dstaddr='.$to;
    $data .= '&smbody='.urlencode($mess);//'親愛的會員,您的 5SeasonLife 人生第五季的簡訊驗證碼為 123456 ,請於5分鐘內完成驗證,謝謝!'
    if (!empty($delay) || !is_numeric($delay)) {
        $data .= '&dlvtime='.$delay;
    }
    // 設定curl網址
    curl_setopt($curl, CURLOPT_URL, $url);
    // 略過SSL驗證
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    // 設定Header
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    // 執行
    $output = curl_exec($curl);
    curl_close($curl);
    $output = preg_replace("/\r\n|\n|\r|\s/", ",", trim($output));
    $output = preg_replace("/\[[0-9]\],/", "", $output);
    $output = str_replace('=', '":"', $output);
    $output = str_replace(',', '","', $output);
    $output = '{"'.$output.'"}';
    $respJS = json_decode($output, true);// msgid // statuscode // AccountPoint
    $exArr = array('smsdone'=>$respJS['statuscode']<=4 ? 1 : 0,'smsdes'=>$respJS['statuscode']<=4 ? '成功發送簡訊!!' : '發送簡訊失敗!!','mbid'=>$id);
    //$exArr = array('mbid'=>$id);
    $rtnArray = array_merge($exArr, $respJS);
    return $rtnArray;
}

//------------------ Export/Import Verification ----------------
function _fn_csvio_getdata($filE='', $getAll=false)
{
    $fpPath = "../dataFiles/csvio/".$filE;
    $fpFsize=filesize($fpPath)+1;
    $fpInfo = array();
    if (($handle=fopen($fpPath, "r")) !== false) {
        $fpRow=1;
        while ($data=fgetcsv($handle, $fpFsize, ",")) {
            if ($fpRow==1) {
                for ($c=0; $c < 8; $c++) {
                    $fpInfo['verify'][$c] = $data[$c];
                }
            } elseif ($fpRow==2) {
                $num = count($data);
                for ($c=0; $c < $num; $c++) {
                    $fpInfo['column'][$c] = $data[$c];
                }
                if ($getAll==false) {
                    break;
                }
            } elseif ($fpRow==3) {
                //continue;
            } else {
                $num = count($data);
                for ($c=0; $c < $num; $c++) {
                    $fpInfo['data'][$fpRow-4][$c] = $data[$c];
                }
            }
            $fpRow++;
        }
        fclose($handle);
        return $fpInfo;
    }
    return false;
}
