<?php
error_reporting(E_ALL);
session_start();
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
//require_once("../classFiles/addrNormalizer/lodash_string.php");
require_once("../classFiles/normalizer/addrNormalize.class.php");
$doact = isset($_POST['doact']) ? $_POST['doact'] : '';
?>
<!DOCTYPE html>
<html lang="en">
  <head>	
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
<title>人生第五季 Fifth Season Life | Address Normalizer Test</title>
    <!-- -------------------------- CSS Link -------------------------- -->
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
    <!-- -------------------------- /CSS Link -------------------------- -->
</head>

<body>
    <div class="wrapper">
        <div class="card-body">
            <hr>
            <div style="font-size:16px;line-height:20px;">
                <h5>人生第五季後備工作--地址格式化測試</h5>
                <hr>
                <div class="card-body" style="width:540px;background-color:#D6DAE1;">

                  <form id="transform" name="transform" action="#" method="post">
                    <input type="hidden" id="doact" name="doact" value="doact">
                    <button type="submit" class="btn btn-danger">執行</button>
                    <button type="button" class="btn btn-warning" onClick="window.location.replace('test_addNormalizer.php');">重置</button>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
                    <button type="button" class="btn btn-secondary" onClick="window.location.replace('index.php');">首頁</button>
                  </form>
                </div>
                <!-- jQuery -->
                <script src="../pluginFiles/jquery/jquery.min.js"></script>
                <!-- Bootstrap 4 -->
                <script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script>
                <script>
                    $(function () {



                    });
                </script>
                <hr>
                <?php
                  if(empty($doact)){ echo "請按「執行」按鍵進行操作！";exit(); }

                  $address = '彰化縣彰化市至善五邨二街20巷六二六至648-2號15樓地下層5~地下層6樓';
                  //$address = '彰化縣彰化市光復新邨二街626之1至640之3號建築物之地下室底二層';
                  $NORM = new NormAddress($address);

                  echo "<pre>";
                  print_r($NORM->extraString());
                  //print_r($NORM->road());
                  //print_r($locJS);
                  echo "</pre>";
                  exit();

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

*/
/*

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
</body>
</html>