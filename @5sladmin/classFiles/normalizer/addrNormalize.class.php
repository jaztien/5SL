<?php
//////////////////////////// addrNormalizer.class.php //////////////////////////////////////
//////////////////////////// 住址分析、正常化以及規則化類 ///////////////////////////////
//////////////////////////// 2025-07-08 By Jaz Tien ///////////////////////////////
//////////////////////////// jaztien@gmail.com ////////////////////////////////////


class NormAddress
{
    private $normalized;// @var Normalized
    private $groups = [];// @var Array
    private $mapping = [];// @var Array
    private static $cached = [];
    private $outputs = [
        'city' => null,
        'district' => null,
        'road' => null,
        'lane' => null,
        'address' => null,
        'shortAddress' => null,
        'geoAddress' => null,
        'fullAddress' => null,
        'extraString' => null
    ];

     //@param  static|string  $address
    public function __construct($address = '')
    {
        if (empty($address) === false) {
            $this->set($address);
        }
    }
    /**
     * @param  static|string  $address
     * @return $this
     */
    private function set($address)
    {
        $this->normalized = $address;//起始時先行定義住址，並指定為未經處理的住址
        $this->normalized = $this->regulating();//將未經處理的住址進行正常化處理，修改一些不正常的內容
        $this->normalized = $this->normalizing();//將已經正常化的住址，進行規則化處理，調整為正規化的住址格式
        $this->groups = $this->grouping();//將之前已處理完成並正規化的住址準進行拆分，並創建二維陣列。grouping()中包含特殊命名處理。
        $this->mapping = $this->remapping();//將正規化後的二維陣列，進行重新構造，並將住址進行重新編排，產生各種不同需求的住址內容排列
    }
    ////////////////////////////////////////////////////////////////////////////////
    //////////////// 將未經處理的住址進行正常化處理，修改一些不正常的內容 ///////////////////
    ////////////////////////////////////////////////////////////////////////////////
    private function regulating()
    {
        $this->normalized = $this->trim();//去除前後空白
        $this->normalized = $this->regularize();//進行正常化處理
        $this->normalized = $this->digitize();//進行數字類的格式統一
        return $this->normalized;//回傳正常化後的住址。並寫入常數
    }
    //-------------------- 將未經處理的住址進行修改一些不正常的內容 ----------------------
    private function regularize()
    {
        $this->normalized = $this->toHalfCase();//將所有字元轉為半形
        $this->normalized = $this->replace('/^\d+/', '');//移除住址之前的所有數字（郵遞區號）
        $this->normalized = $this->replace([//替換必要字串
                                ' ' => '',
                                '(' => '',
                                ')' => '',
                                '[' => '',
                                ']' => '',
                                '{' => '',
                                '}' => '',
                                '「' => '',
                                '」' => '',
                                ',' => '、',
                                //'、' => '',
                                //'~' => '之',
                                '~' => '至',
                                '-' => '之',
                                '單號' => '單',
                                '雙號' => '雙'
                            ]);
        $this->normalized = $this->replace([//再次替換必要字串，修正前次替換後可能產生不合邏輯的單字搭配
                                //'((' => '(',
                                //'))' => ')',
                                '號單' => '單號',
                                '號雙' => '雙號',
                                '號、' => '、',
                                '號至' => '至'
                            ]);
        
        return $this->normalized;//回傳正常化後的住址。並寫入常數
    }
    //-------------------- 將已經初步正常化的住址，進行數字類的格式統一 ----------------------
    private function digitize()
    {
        //'/[一二三四五六七八九十百千]+(?=[段路街巷弄號樓])/u'(原始規則)
        //將「段路街巷弄之號樓室」之前的國字數字，一律替換為阿拉伯數字
        return $this->replace('/[零〇一二三四五六七八九十百千]+(?=([至、]|[段路街巷弄之號樓層室]))/u', function ($m) {
            return $this->toNumber($m[0]);//國字數字轉阿拉伯數字的必要規則
        });
    }
    ////////////////////////////////////////////////////////////////////////////////
    ///////////// 將已經正常化的住址，進行規則化處理，調整為正規化的住址格式 /////////////////
    ////////////////////////////////////////////////////////////////////////////////
    //-------------------- 將已經正常化的住址，進行規則化處理，調整為正規化的住址格式 ----------------------
    private function normalizing()
    {
        $this->normalized = $this->replace([//替換錯誤的名稱以及單位
                //NOTE：以下為可編輯部份，當有需要時可自行更改
                '台灣' => '臺灣',
                '台北' => '臺北',
                '台中' => '臺中',
                '台南' => '臺南',
                '台東' => '臺東',
                '釣魚台' => '釣魚臺',
                '台西鄉' => '臺西鄉',
                '霧台鄉' => '霧臺鄉',
                '[石曹]村' => '[石曹]里',
                '頭家東村' => '頭家東里',
                '拉芙蘭村' => '拉芙蘭里',
                '那瑪夏鄉' => '那瑪夏區',
                '達卡努瓦村' => '達卡努瓦里',
                '瑪雅村' => '瑪雅里',
                '南沙魯村' => '南沙魯里',
        ]);
        $this->normalized = $this->replace('/^北縣/', '臺北縣');//將所有的北縣改為臺北縣
        $this->normalized = $this->replace('/^北市/', '臺北市');//將所有的北市改為臺北市
        $this->normalized = $this->replace('/^(桃園|臺北|臺中|臺南|高雄)縣(?:(\w{2})[市鄉鎮])?(?:(\w{2})村)?/u', function ($m) {//將改制前的縣市以及區劃單位改為新制的縣市以及區劃單位
                return ($m[1] === '臺北' ? '新北' : $m[1]).'市'.//若是為臺北縣則改為新北市
                    (isset($m[2]) === true ? $m[2].'區' : '').//舊制的區劃單位為「市鄉鎮」時，改為新制的區
                    (isset($m[3]) === true ? $m[3].'里' : '');//舊制的區劃單位為「村」時，改為新制的里
            });
        $this->normalized = $this->replace([//特殊的改制名稱替換
                //NOTE：以下為可編輯部份，當有需要時可自行更改
                '苗栗縣頭份鎮' => '苗栗縣頭份市',
                '彰化縣員林鎮' => '彰化縣員林市',
                '臺南市中區' => '臺南市中西區',
                '臺南市西區' => '臺南市中西區',
                '南海諸島東沙' => '南海島東沙群島',
                '南海諸島南沙' => '南海島南沙群島',
                '南海諸島釣魚臺列嶼' => '釣魚臺釣魚臺',
            ]);
        $this->normalized = $this->replace('/地下[室樓層]\d+/u', function ($m) {//將非住址字串中的「地下層n層」中衍生的第一個「層」去除
                return str_replace(['室','樓','層'], '', $m[0]);
            });
        $this->normalized = $this->replace('/(地下[室樓層]?((\d+)[樓層]?)?)$|(地下[室樓層]?(\d+)[樓層]?至(地下[室樓層]?)?(\d+)[樓層]?)|(地下[室樓層]?(\d+)[樓層]?(、(地下[室樓層]?)?(\d+)[樓層]?)+)/u', function ($m) {//過濾非住址字串中的「樓」為「層」
                return str_replace('樓', '層', $m[0]);
            });
        return $this->normalized;//回傳正規化後的住址。並寫入常數
    }

    ////////////////////////////////////////////////////////////////////////////////
    //////// 將之前已處理完成並正規化的住址準進行拆分，並將其拆分後的內容創建二維陣列 //////////
    /* ///// 原始規則 /////////////
        $patterns = implode('', [//分析住址的正規表達式
            '(?:(?P<no>\d+)(?P<subno>之\d+)?(?=[巷弄號樓室]|$)|(?P<name>.+?))',
            '(?:(?P<unit>([島縣市鄉鎮市區村里道鄰路街段巷弄號樓室]|魚臺))|(?=\d+(?:之\d+)?[巷弄號樓室]|$))',
        ]);
    */
    ////////////////////////////////////////////////////////////////////////////////
    private function grouping()
    {
        $this->trickyInit();//名稱內含鄉、里、路...等會混淆系統過濾規則的區劃路名，必須事先過濾出來，以代碼替換後再進行正規化。
        $units = [0 => 'no', 1 => 'subno', 2 => 'name', 3 => 'unit', 4 => 'extra'];
        $patterns = implode('', [//分析住址的正規表達式
            '(?:(?P<no>\d+)(?P<subno>之\d+)?(?=[鄰巷弄號樓]|$)|(?P<name>.+?))',
            '(?:(?P<unit>([島縣市鄉鎮市區村里道鄰路街段巷弄號樓]|魚臺))|(?=\d+(?:之\d+)?[鄰巷弄號樓]|$))',
        ]);
        $addrHashed = $this->hash();//【特殊命名處理】將住址中易於混淆的字編碼替換
        $groups = [];
        if (preg_match_all('/'.$patterns.'/u', $addrHashed, $matches, PREG_SET_ORDER) !== false) {
            $para_neighbor = '';//路、街、巷、弄或是一個地理位置名稱，做為焦點搜尋的一部份，此部分獨立資料欄位儲存
            $para_number = '';//單純門牌號碼，或是地點最終辨識的依據
            $plateCount = 0;//【實際門牌數】整個住址出現多少個門牌號，通常只會在銷售種類為車位的課程商品中出現超過一個門牌號，超過一個號碼時，系統將停止後續操作，並發出警告
            $noCount = 0;//依迴圈計算出規則化後的分組中以「號」為單位的分組數，此值用做整合門牌號碼陣列的依據
            //預先計算整個住址字串在「規則化」之後中出現的門牌號數
            preg_match_all('/號/u', $addrHashed, $ma);//出現在“規則化後”的住址中的門牌號
            $totalPlates = count($ma[0]);//出現在“規則化後”的住址中的門牌號數
            //正規表達式分析後進行分組迴圈
            foreach ($matches as $key => $values) {
                $para = array_map(static function ($unit) use ($values) {
                    return isset($values[$unit]) === true ? $values[$unit] : '';//依正規表達式分析出來的住址內容進行對應
                }, $units);
                $para[2] = $this->flip($para[2]);//【特殊命名處理】將住址中已編碼替換的字元，進行逆向替換（替換為原來的字元）
                $groupPass = true;//預設通過執行分組陣列的建立
                $extraString = '';//非住址附加字串預設為空值
                if(empty($para[0]) && empty($para[1]) && empty($para[3]) && !empty($para[2])) {//當這一個分組中只有<name>(鍵值為2)欄位有值時，此值將被系統認定為非縣市區劃以及路名，可能為縣市之下的聚落區域或是一個地理位置名稱，更有可能是門牌號碼之後的附加字串，例如地下層資訊。
                    if(preg_match('/^(之\d+)/', $para[2], $matches) === 1){//當分析出來的附加字串開頭為「之n」時
                       $extraString = str_replace($matches[0], '', $para[2]);//將這個分組中<name>(鍵值為2)欄位字串，過濾出「之n」的部分，使其成為非住址附加字串
                        $groups[] = ['', $matches[0], '', '', ''];//「之n」這個值可以自立為一個獨立分組，層級種類為「號」
                    } 
                    else if(preg_match('/((\d+)(之(\d+))?至((\d+)(之(\d+))?)?)|((\d+)(之(\d+))?(、)?)+/', $para[2], $mat) === 1) {//分析多門牌住址如「<no>(之n)至<no>(之n)」或是「<no>(之n)、<no>(之n)、<no>(之n)、...」
                        $plateFilter = str_replace($mat[0], '', $para[2]);//將這個分組中<name>(鍵值為2)欄位字串，過濾出門牌號碼的部分
                        if ($noCount == $totalPlates) {
                            $extraString = $para[2];
                        } else {
                            $para_number .= $mat[0];
                            if(!empty($plateFilter)){//當過濾門牌號碼之後的字串帶值時，系統將認定此為門牌號碼之前的附加字串，可能為沒有單位的路名或是縣市之下的聚落區域或是一個地理位置名稱。
                                $groups[] = ['', '', $plateFilter, '', ''];//這個值可以自立為一個獨立分組，層級種類為「區劃」或「路街」
                            }
                        }
                        $plateCount++;//門牌分組數加1
                        $groupPass = false;//在這個狀態下，不執行後續的分組處理(直到最後一個單位為「號」的分組時再整合處理)
                    } else {
                        if($noCount == $totalPlates){//當這個分組已經過了最後一個「號」分組時
                            $extraString = $para[2];//設置非住址附加字串為這個分組的<name>(鍵值為2)欄位值
                        }
                    }
                    if(!empty($extraString)){//非住址附加字串不為空值時
                        //地下樓//地下n樓//地下n層//地下//地下室//房屋地下層//地下二層等//門牌地下一層至二層//建物門牌地下二層至地下三層
                        //門牌房屋地下四層//等房屋地下層//等共有部分//共同使用//建築物地下二、三層//地下層(一至六層)//地下二層至地下四層
                        //地下一層、二層//地下一層、地下二層//地下之9//地下二、三樓//地下一樓等公共設施//建築物之地下室底一,二層
                        //建築物之地下室底一,二層//地下室地址證明//等及地下一~三樓//地下一層等共同使用部份//底一層//建築物之地下室底二層
                        //---------------- 修正以下非住址附加字串 ----------------
                        $extraString = str_replace([//去除不必要字串
                            '建築物之',
                            '建築物',
                            '建物之',
                            '建物',
                            '門牌之',
                            '門牌',
                            '房屋之',
                            '房屋',
                            '地址之',
                            '地址',
                            '證明',
                            '等',
                        ], '', $extraString);
                        $extraString = strtr($extraString, [//替換必要字串，盡量修正為為統一格式
                            '部分' => '部份',
                            '樓' => '層',
                            '室' => '層',
                            '底' => '地下',
                            '共同使用' => '共有部份',
                            '公共設施' => '公設部份',
                            '層、' => '、',
                            '層至' => '至',
                            '地下之' => '地下層之',
                        ]);
                        $extraString = strtr($extraString, [//修正統一格式後，替換所造成的非邏輯字串
                            '地下層地下' => '地下',
                            '地下地下' => '地下',
                            '、地下' => '、',
                            '至地下' => '至',
                            '部份部份' => '部份'
                        ]);
                        $groups[] = ['', '', '', '', $extraString];//這個非住址附加字串可以自立為一個獨立分組，層級種類為「附加」
                        $groupPass = false;//在這個狀態下，不執行後續的分組處理，避免重複處理
                    }
                    if($groupPass){//當後續的分組處理狀態為「允許」時
                        $groups[] = $para;//進行正常的分組處理
                    }
                } else if($para[3]=='號'){//當這個分組中的<unit>(鍵值為3)欄位值為「號」時
                    $noCount++;//門牌分組數加1(分組中單位為「號」的數量)
                    $plateCount++;//字串中實際門牌數加1
                    if($noCount == $totalPlates){//當這個分組正當為最後一個「號」分組時
                        $para_number .= $para[0];//將門牌號碼進行串接(與先前單位為「號」的門牌號結合)
                        if(is_numeric($para_number)){//當結合過後的門牌號為純數字時，代表只有一個門牌數，將這個值寫入這個分組的第一個鍵值<no>中
                            $para[0] = $para_number;
                        } else {//若此值非為純數字
                            $para_number .= $para[1].$para[2];//再將鍵值為<subno>[1]以及<name>[2]的值進行串接
                            $para[0] = $para_number;//將此值寫入這個分組的第一個鍵值<no>中，此時這個值為字串型態並非數字型態，其中可能會有「至」或是「、」等多門牌的結構。
                            $para[1] = '';//清空鍵值為<subno>[1]的原始值
                            $para[2] = '';//清空鍵值為<name>[2]的原始值
                        }
                        $groups[] = $para;//寫入陣列分組中
                    } else {//當這個分組不是最後一個「號」分組時
                        $para_number .= $para[0].$para[1].$para[2].$para[3];//繼續將門牌號碼進行串接(與先前單位為「號」的門牌號結合)
                    }
                } else {//排除上列的兩種狀況後，一律進行正常的陣列分組
                    $groups[] = $para;
                }
            }
        }
        return $groups;//回傳二維陣列
    }
    ////////////////////////////////////////////////////////////////////////////////
    ////////////////////////// 【預先執行】特殊命名格式處理 /////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////
    //名稱內含鄉、里、路...等會混淆系統過濾規則的區劃路名，必須事先過濾出來，以代碼替換後再進行正規化。
    private function trickyInit(){//Tricky.php->init
        //NOTE：以下為可編輯部份，當有新的易於混淆的字元時，可自行新增
        $tricky = ['島', '嶼', '鄉', '市', '鎮', '區', '村', '里', '路', '新市', '阿里山', '鎮興里平'];//這些字元會混淆系統過濾規則的區劃路名
        self::$cached['hash'] = array_reduce($tricky, static function ($acc, $unit) {//將會混淆的字元進行編碼，成為獨一無二的代碼串
            return $acc + [$unit => md5($unit)];
        }, []);

        self::$cached['flip'] = array_flip(self::$cached['hash']);//反向回覆這些易於混淆的字元，成為原始名稱，此陣列值寫入為常數等待處理
        self::$cached['replace'] = [//將這些易於混淆的字元進行代碼替換，此陣列值寫入為常數等待處理
            //NOTE：以下為可編輯部份，當有新的易於混淆的字元時，可自行新增
            '島鄉' => self::$cached['hash']['島'].'鄉',
            '嶼鄉' => self::$cached['hash']['嶼'].'鄉',
            '村鄉' => self::$cached['hash']['村'].'鄉',
            '里鄉' => self::$cached['hash']['里'].'鄉',
            '村市' => self::$cached['hash']['村'].'市',
            '里區' => self::$cached['hash']['里'].'區',
            '鎮區' => self::$cached['hash']['鎮'].'區',
            '里鎮' => self::$cached['hash']['里'].'鎮',
            '里村' => self::$cached['hash']['里'].'村',
            '鄉村' => self::$cached['hash']['鄉'].'村',
            '路鄉' => self::$cached['hash']['路'].'鄉',
            '新市區' => self::$cached['hash']['新市'].'區',
            '阿里山鄉' => self::$cached['hash']['阿里山'].'鄉',
            '鎮興里平鎮' => self::$cached['hash']['鎮興里平'].'鎮',
        ];
    }
    //-------------------- 將易於混淆的字元進行代碼替換 ---------------------------
    private function hash(){//Tricky.php->hash
        return strtr($this->normalized, self::$cached['replace']);
    }
    //-------------------- 將代碼替換的字元進行逆向替換，恢復正常 -----------------------
    private function flip($para){//Tricky.php->flip
        return strtr($para, self::$cached['flip']);
    }

    ////////////////////////////////////////////////////////////////////////////////
    ///////////////////// 重新編排各種不同需求的住址內容排列 /////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////
    //將正規化後的二維陣列，進行重新構造，並將住址進行重新編排，產生各種不同需求的住址內容排列
    private function remapping()
    {
        $roadSkip = ['市','縣','鄉','鎮','區','鄰','里','巷','弄','號','樓'];//在「簡易住址」中，不會出現的部分
        $laneSkip = ['市','縣','鄉','鎮','區','鄰','里','路','街','道','段','號','樓'];//在「部分住址」中，不會出現的部分
        $partSkip = ['鄰','里'];//在「部分住址」中，不會出現的部分
        $shortSkip = ['市','縣','鄉','鎮','區','鄰','里'];//在「簡易住址」中，不會出現的部分
        $geoSkip = ['鄰','里','樓'];//在「定位住址」中，不會出現的部分，取得座標定位時無需樓層資訊
        $roadGroup = [];//「街道」起始空陣列值
        $laneGroup = [];//「巷弄」起始空陣列值
        $partGroup = [];//「部分住址」起始空陣列值
        $shortGroup = [];//「簡易住址」起始空陣列值
        $geoGroup = [];//「定位住址」起始空陣列值
        $extraGroup = '';// 「非住址字串」起始為空字串值
        $topLayer = true;//判斷第一次出現縣市時，其層級是否為第一層級「城市」，以住址內容順位來判斷，先預設首先遇上的縣市為第一層級；
        $secondLayer = ['鄉','鎮','區'];//第二層級「區劃」通常為鄉、鎮、區，但是當第一層級為「縣」級單位時，第二層級的「行政區劃」中，有可能包含「市」命名的區域。例如：彰化縣彰化市
        foreach ($this->groups as $value) {
            $roadPass = 1;//預設「街道」為顯示
            $lanePass = 1;//預設「巷弄」為顯示
            $partPass = 1;//預設「部份住址」為顯示
            $shortPass = 1;//預設「簡易住址」為顯示
            $geoPass = 1;//預設「定位住址」為顯示
            //---------------------------------------
            if(!empty($value[3])){//當「單位 unit」所代表的鍵值下，不為空值時
                if ($topLayer && in_array($value[3], ['市','縣']) && !empty($value[2])) {
                    $this->outputs['city'] = [$value];//將第一層級為縣市的住址內容進行常數存入
                    $topLayer = false;//已比對出第一層級後，便將這個層級的判斷值改為false，代表之後的比對不將是第一層級的比對
                    if($value[3] == '縣') {//當第一層級為縣級單位
                        $secondLayer[] = '市';//當第一層級為縣級單位時，第二層級的「行政區劃」中，要新增一個「市」進入比對條件。
                    }
                }
                if ($topLayer == false && in_array($value[3], $secondLayer) && !empty($value[2])) {//當不是第一層級的比對時，進行第二層級的比對，比對條件依照第一層級是否為縣級單位的結果而更改。
                    $this->outputs['district'] = [$value];//將第二層級為行政區劃的住址內容進行常數存入
                }
                //---------- 比對不將出現的住址部分 --------------------
                if(in_array($value[3], $roadSkip)){//在「街道」比對中，當「單位unit」值為'市','縣','鄉','鎮','區','鄰','里','巷','弄','號','樓']時
                    $roadPass = 0;//標註這個部份在「街道」中不會出現
                }
                if(in_array($value[3], $laneSkip)){//在「巷弄」比對中，當「單位unit」值為'鄰'或'里'時
                    $lanePass = 0;//標註這個部份在「巷弄」中不會出現
                }
                if(in_array($value[3], $partSkip)){//在「部分住址」比對中，當「單位unit」值為'鄰'或'里'時
                    $partPass = 0;//標註這個部份在「部分住址」中不會出現
                }
                if(in_array($value[3], $shortSkip)){//在「簡易住址」比對中，當「單位unit」值為'市'、'縣'、'鄉'、'鎮'、'區'、'鄰'或'里'時
                    $shortPass = 0;//標註這個部份在「簡易住址」中不會出現
                }
                if(in_array($value[3], $geoSkip)){//在「定位住址」比對中，當「單位unit」值為'鄰'、'里'或'樓'時
                    $geoPass = 0;//標註這個部份在「定位住址」中不會出現
                }
            } else {//當單位unit為空值時
                if(!empty($value[4])){//當「extra」所代表的鍵值下，不為空值時，亦即非住址附加字串值不為空時，除了非住址字串外，其他分組一律不顯示
                    $roadPass = 0;
                    $lanePass = 0;
                    $partPass = 0;
                    $shortPass = 0;
                    $geoPass = 0;
                    $extraGroup = $value[4];//將這個部份的非住址字串內容進行存入字串值中
                } else if(!empty($value[2]) && empty($value[0]) && empty($value[1])) {//當「name」所代表的鍵值下不為空值，且「no」、「subno」與「unit」同為空值時
                    $lanePass = 0;//「巷弄」時不顯示
                }
            }
            //--------------- 住址尾端為「之【n】」時處理 ------------------------
            if(empty($value[0]) && empty($value[2]) && empty($value[3]) && !empty($value[1])) {//當這個部份代表「號碼no」、「字號碼subno」、「單位unit」為空值時，此時若是代表「名稱name」內容為「之【n】」格式時
                if(preg_match('/之[1-9]/u',$value[1])==true){ $geoPass = 0; }//標註這個部份在「定位住址」中不會出現
            }
            //------------------------ 生成 --------------------------
            if(!empty($roadPass)) {//當這個部份在「街道」中可以出現時
                $roadGroup[] = $value;//將這個部份的住址內容進行存入新的陣列中
            }
            if(!empty($lanePass)) {//當這個部份在「巷弄」中可以出現時
                $laneGroup[] = $value;//將這個部份的住址內容進行存入新的陣列中
            }
            if(!empty($partPass)) {//當這個部份在「部分住址」中可以出現時
                $partGroup[] = $value;//將這個部份的住址內容進行存入新的陣列中
            }
            if(!empty($shortPass)) {//當這個部份在「簡易住址」中可以出現時
                $shortGroup[] = $value;//將這個部份的住址內容進行存入新的陣列中
            }
            if(!empty($geoPass)) {//當這個部份在「定位住址」中可以出現時
                $geoGroup[] = $value;//將這個部份的住址內容進行存入新的陣列中
            }

        }
        $this->outputs['road'] = $roadGroup;//將「街道」內容進行常數存入
        $this->outputs['lane'] = $laneGroup;//將「巷弄」內容進行常數存入
        $this->outputs['address'] = $partGroup;//將「部分住址」內容進行常數存入
        $this->outputs['shortAddress'] = $shortGroup;//將「簡易住址」內容進行常數存入
        $this->outputs['geoAddress'] = $geoGroup;//將「定位住址」內容進行常數存入
        $this->outputs['extraString'] = $extraGroup;//將「非住址字串」內容進行常數存入
        /*
        $this->outputs['road'] = !empty($roadGroup) ? $roadGroup : '';//將「街道」內容進行常數存入
        $this->outputs['lane'] = !empty($laneGroup) ? $laneGroup : '';//將「巷弄」內容進行常數存入
        $this->outputs['address'] = !empty($partGroup) ? $partGroup : '';//將「部分住址」內容進行常數存入
        $this->outputs['shortAddress'] = !empty($shortGroup) ? $shortGroup : '';//將「簡易住址」內容進行常數存入
        $this->outputs['geoAddress'] = !empty($geoGroup) ? $geoGroup : '';//將「定位住址」內容進行常數存入
        $this->outputs['extrastring'] = !empty($extraGroup) ? $extraGroup : '';//將「「非住址字串」內容進行常數存入
        */
    }
    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////// Functions //////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////
    private function trim($characterMask = " \t\n\r\0\x0B")
    {
        return trim($this->normalized, $characterMask);
    }
    private function toHalfCase()
    {
     $fullCaseMap = [
        '　' => ' ',
        '！' => '!',
        '＂' => '"',
        '＃' => '#',
        '＄' => '$',
        '％' => '%',
        '＆' => '&',
        '＇' => "'",
        '（' => '(',
        '）' => ')',
        '＊' => '*',
        '＋' => '+',
        '，' => ',',
        '－' => '-',
        '．' => '.',
        '／' => '/',
        '０' => '0',
        '１' => '1',
        '２' => '2',
        '３' => '3',
        '４' => '4',
        '５' => '5',
        '６' => '6',
        '７' => '7',
        '８' => '8',
        '９' => '9',
        '：' => ':',
        '；' => ';',
        '＜' => '<',
        '＝' => '=',
        '＞' => '>',
        '？' => '?',
        '＠' => '@',
        'Ａ' => 'A',
        'Ｂ' => 'B',
        'Ｃ' => 'C',
        'Ｄ' => 'D',
        'Ｅ' => 'E',
        'Ｆ' => 'F',
        'Ｇ' => 'G',
        'Ｈ' => 'H',
        'Ｉ' => 'I',
        'Ｊ' => 'J',
        'Ｋ' => 'K',
        'Ｌ' => 'L',
        'Ｍ' => 'M',
        'Ｎ' => 'N',
        'Ｏ' => 'O',
        'Ｐ' => 'P',
        'Ｑ' => 'Q',
        'Ｒ' => 'R',
        'Ｓ' => 'S',
        'Ｔ' => 'T',
        'Ｕ' => 'U',
        'Ｖ' => 'V',
        'Ｗ' => 'W',
        'Ｘ' => 'X',
        'Ｙ' => 'Y',
        'Ｚ' => 'Z',
        '［' => '[',
        '＼' => '\\',
        '］' => ']',
        '＾' => '^',
        '＿' => '_',
        '｀' => '`',
        'ａ' => 'a',
        'ｂ' => 'b',
        'ｃ' => 'c',
        'ｄ' => 'd',
        'ｅ' => 'e',
        'ｆ' => 'f',
        'ｇ' => 'g',
        'ｈ' => 'h',
        'ｉ' => 'i',
        'ｊ' => 'j',
        'ｋ' => 'k',
        'ｌ' => 'l',
        'ｍ' => 'm',
        'ｎ' => 'n',
        'ｏ' => 'o',
        'ｐ' => 'p',
        'ｑ' => 'q',
        'ｒ' => 'r',
        'ｓ' => 's',
        'ｔ' => 't',
        'ｕ' => 'u',
        'ｖ' => 'v',
        'ｗ' => 'w',
        'ｘ' => 'x',
        'ｙ' => 'y',
        'ｚ' => 'z',
        '｛' => '{',
        '｜' => '|',
        '｝' => '}',
        '～' => '~',
    ];
        return strtr($this->normalized, $fullCaseMap);
    }
    private function replace($regexp, $replacement = null)
    {
        if (is_array($regexp) === true && is_null($replacement) === true) {
            return strtr($this->normalized, $regexp);
        }

        if (is_callable($replacement) === true) {
            return preg_replace_callback($regexp, $replacement, $this->normalized);
        }

        return preg_replace($regexp, $replacement, $this->normalized);
    }

    private function toNumber($cNum){///lodash/src/JString/Extensions/Chinese.php->toNumber
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

    }
    private function toFlat($groups){
        if(empty($groups)){ return false; }
        $res = array_map(function ($para) {
            return implode('', $para);
        }, $groups);
        return implode('', $res);
    }
    ////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////// OutPuts //////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////
    public function grouparray()
    {
        return $this->groups;
    }
    public function fulladdress()
    {
        return $this->toFlat($this->groups);
    }
    public function city()
    {
        return $this->toFlat($this->outputs['city']);
    }
    public function district()
    {
        return $this->toFlat($this->outputs['district']);
    }
    public function road()
    {
        return $this->toFlat($this->outputs['road']);
    }
    public function lane()
    {
        return $this->toFlat($this->outputs['lane']);
    }
    public function address()
    {
        return $this->toFlat($this->outputs['address']);
    }
    public function shortAddress()
    {
        return $this->toFlat($this->outputs['shortAddress']);
    }
    public function geoAddress()
    {
        return $this->toFlat($this->outputs['geoAddress']);
    }
    public function extraString()
    {
        return $this->outputs['extraString'];
    }



}
