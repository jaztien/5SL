<?php
//////////////////////////// bcNormalize.class.php /////////////////////////////////
//////////////////////////// 建案名稱分析、正常化以及規則化類 ///////////////////////////
//////////////////////////// 2025-07-08 By Jaz Tien ///////////////////////////////
//////////////////////////// jaztien@gmail.com ////////////////////////////////////


class NormBC
{
    private $origFixed;// @var origFixed 原始目標字串
    private $normalized;// @var normalized 目標字串規則化
    private $builderAbbr;// @var builderAbbr 建商前綴分析字串
    //private $parseTitle;// @var parseTitle 分析過後的單純建案名稱
    private $parsePhase;// @var parsePhase 建案期數
    private $parseBCtype;// @var parseType 建案型態(非名稱相關敘述，如:別墅、大樓...等)
    private $parseSubarea;// @var parseSubarea 建案子區域(非名稱以及型態相關敘述，如:金區、銀區、A棟、B棟...等)
    private $parseAreaExt;// @var parseAreaExt 建案子區域屬區(子區域的再分區，如:A區、B區、之一、之二...等)
    private $parseLocated;// @var parseLocated 建案關係位置(非名稱相關敘述，如:南京東路、忠孝東路二段、56巷、八張犁段、社子店、陽光街...等)
    private $parseAka1;// @var parseAka1 建案名稱第一附屬名稱
    private $parseAka2;// @var parseAka2 建案名稱第二附屬名稱
    private $tmatches;
    private $bcMode = 1;// @var bcMode 建案名稱命名模式歸類 1-標準命名；2-標準命名+期數；3-標準命名+分區；4-期數+分區；5-以住址命名；6-以建商命名；7-以政府單位命名(縣市區里單位等)；8-以私人單位命名(協會、基金會、財團法人...等)
    private $phaseMax = 100;// @var phaseMax 常規分析下期數最大值（超過此值時不進行匹配分析）
    private $exception = ['泰第','富富','金統','幕府','大學','璞真','森','明水','半九','騰青','鋐微','硯如','城貳','綠十','堡雙','鑫如','皇居','境斗','美無','南第','匱拾','駁','緯不','當代','歡樂','砌傳','知一','豐鉅'];// @array 建案名稱例外處理
    //private $exception2 = ['大學','瑝家','富富二代','富二代','韻A'];
    private $multichars = ['世家樓中','文華道','比佛利','忠義國小','真善美','紀念館','新世紀','新生活','麗水苑','江水藍','達文西','貝多芬','莫札特','維瓦第','迷迭香','比佛利','波士頓','夏威夷','曼哈頓','伊莉諾','奧斯汀','柏克萊','松柏樓','懷石庭','植物園','葉綠宿','地中海','坎城新','大吉利','國家公園','高鐵新','台北大學'];
    private $cityskip = ['臺北市','新北市','桃園市','新竹縣','新竹市','苗栗縣','臺中市','彰化縣','南投縣','雲林縣','嘉義縣','嘉義市','臺南市','高雄市','屏東縣','花蓮縣','臺東縣','澎湖縣','金門縣','連江縣'];
    private $groups = [];// @var Array 建案名稱解析後分組
    private $mapping = [];// @var Array 解析分組後重新構造
    private static $cached = [];
    private $outputs = [
        'origfixed' => null,
        'preix' => null,
        'title' => null,
        'subtitle1' => null,
        'subtitle2' => null,
        'phase' => null,
        'extraString' => null,
        'regularizeString' => null,
        'tmatches' => null
    ];

     //@param  static|string  $bctitle
    public function __construct($bctitle = '', $investor = '')
    {
        if (!empty($bctitle)) {
            $this->set($bctitle, $investor);
        }
    }
    /**
     * @param  static|string  $bctitle
     * @return $this
     */
    private function set($bctitle, $investor)
    {
        //------------- 建商簡稱前綴 -------------
        $this->origFixed = $bctitle;//起始時先行定義住址，並指定為未經處理的住址
        $this->builderAbbr = $investor;//起始時先行定義投資商，並指定為未經處理的投資商名稱  
        $this->builderAbbr = $this->extracting();//將未經處理的投資商名稱進行解析並提取代表字串處理
        //------------- 建案名稱 -------------
        $this->normalized = $bctitle;//起始時先行定義原始建案名稱，並指定為未經處理的名稱，此值最終將為除錯後的原始名稱
        $this->normalized = $this->regulating();//將未經處理的建案名稱進行正常化處理，修改一些不正常的內容
        $this->normalized = $this->normalizing();
        /*
        $this->normalized = $this->normalizing();//將已經正常化的住址，進行規則化處理，調整為正規化的住址格式
        $this->groups = $this->grouping();//將之前已處理完成並正規化的住址準進行拆分，並創建二維陣列。grouping()中包含特殊命名處理。
        $this->mapping = $this->remapping();//將正規化後的二維陣列，進行重新構造，並將住址進行重新編排，產生各種不同需求的住址內容排列
        */
    }

    ////////////////////////////////////////////////////////////////////////////////
    ////////////////// 將未經處理的投資商名稱進行解析並提取代表字串處理 ////////////////////
    ////////////////////////////////////////////////////////////////////////////////
    public function extracting()
    {

        if (empty($this->builderAbbr)) {
            return '';
        } else {
            $normBasic = array(
                ' ' => '',
                '.' => '',
                '(' => '',
                ')' => '',
                '，' => '',
                '．' => '',
                '；' => '',
                ',' => '',
                '/' => '',
                '-' => '',
                'X' => '',
                '件設' => '建設',
                '連設' => '建設',
                '建捨' => '建設',
                '建社' => '建設',
                '健設' => '建設',
                '建? ' => '建設',
                '待' => '待定',
                '代定' => '待定',
                '代訂' => '待定',
                '待訂' => '待定',
                '台灣銀行' => '台銀',
                '台灣糖業' => '台糖',
                '台灣紙業' => '台紙',
                '台灣肥料' => '台肥',
                '台灣鐵路' => '台鐵',
                '台灣鳳梨' => '台鳳',
                '遠雄' => '遠雄機構',
            );
            $skipBasic = "待定|待查|待補|不明|不清楚|查詢中|地主|自售|國民住宅|建商|私人|自建|委建|建構中|本業建設|無|查無|待確認|暫定|未知|不知|地主|個人名義|不詳|無法查詢|無資料|company|未提供|建商|營商|承包商|營造商|查無資料|不可考|改建|社區|籌備|啟造|起造|OO|Ｏ|中華民國|同鄉會|農會|同業公會|多媒體|北捷|合作社|警察局|信用社|國宅|都更|都市更新|公用社|經濟部|學校|政府|財政部|教育部|法務部|內政部|國防部|交通部|交通處|公路局|調查局|林務局|市場處|地政處|代表人|信託人|婦女會|國宅處|國民黨|國立|小學|中學|大學|師大|鄉公所|教會|財團法人|管委會|福委會|浸信會|水利會|公教住宅|試驗所|就業所|中研院|農學會";
            $passBasic = "企業|實業|全球|興業|商業|產業|貿易|事業|事務|機構|公司|工業社|商社|集團|股份|有限|建設|建造|聯合|聯合建設|關係|開發|發展|建研|建築|自力造屋|投資|創新|營造|營建|建業|興建|工程|不動產|資產|法定|土地|地產|工廠|團隊";
            $typeBasic = "商銀|銀行|資產|商標|物業|展業|科技|電子|育樂|鋼鐵|化學|租賃|造紙|紙業|設計|保險|生物|工業|工商|機械|創新|精機|機電|課程商品|環保|生活|童衣|木器|五金|塑膠|印刷|針織|纖維|時尚|酒店|賓館|工藝|水泥|超級市場|網路|書局|麵粉|報業|報社|窯業|汽車|租賃|文化|能源|祭祀|油脂|倉儲|光電|生技|傢俱|化工|鋼管|電機|空調|資訊|紡織|電工|模具|玻璃|電氣|百貨|農產|房屋|製衣|廣告|貨物|貨運|飯店|大飯店|電腦|管理";
            $lastName = "陳|林|黃|張|李|王|吳|劉|蔡|楊|許|鄭|謝|郭|洪|曾|邱|廖|賴|周|徐|蘇|葉|莊|呂|江|何|蕭|羅|高|簡|朱|鍾|施|游|詹|沈|彭|胡|余|盧|潘|顏|梁|趙|柯|翁|魏|方|孫|張簡|戴|范|歐陽|宋|鄧|杜|侯|曹|薛|傅|丁|溫|紀|范姜|蔣|歐|藍|連|唐|馬|董|石|卓|程|姚|康|馮|古|姜|湯|汪|白|田|涂|鄒|巫|尤|鐘|龔|嚴|韓|黎|阮|袁|童|陸|金|錢|邵|于|崔|任|夏|譚|韋|賈|熊|孟|秦|閻|雷|龍|段|郝|孔|史|毛|常|萬|顧|賀|尹|牛|倪|梅";
            //$lastNameExcept = "葉財記|龍巖|石磐|高立|金記|常殷|萬台|龍杰|龍鎰|金旺宏|金旺鴻|金旺盛|金鋒|金榮朝代|金祥發銅板|金鳳|萬里虹|龍邦|金旺盛|常隆|金磊|金山大建|陽光綠意|萬代堡|萬客隆|龍騰建|金棟隆|萬年鑫";
            $lastNameExcept = ['葉財記','龍巖','石磐','高立','金記','常殷','萬台','龍杰','龍鎰','金旺宏','金旺鴻','金旺盛','金鋒','金榮朝代','金祥發銅板','金鳳','萬里虹','龍邦','金旺盛','常隆','金磊','金山大建','陽光綠意','萬代堡','萬客隆','龍騰建','金棟隆','萬年鑫'];
            
            $this->builderAbbr = $this->trim($this->builderAbbr);
            $this->builderAbbr = strtr($this->builderAbbr, $normBasic);
            if(preg_match('/'.$skipBasic.'/u', $this->builderAbbr )){ return ''; }
            elseif(preg_match('/\W*((等?(\d+|[一二三四五六七八九十〇壹兩]+)[名人戶])|等人)/u', $this->builderAbbr)){ return ''; }
            elseif(preg_match('/'.$passBasic.'|'.$typeBasic.'/u', $this->builderAbbr, $match)){
                $splitPass = explode($match[0], $this->builderAbbr);
                return $splitPass[0];
            }
            elseif(!preg_match('/^['.$lastName.']{1,2}/u', $this->builderAbbr) || in_array($this->builderAbbr, $lastNameExcept)){ return $this->builderAbbr; }
            else { return ''; }

        }
        //台灣土地開發信託投資 股份有限公司法定//高雄市政府國民住宅處 法定//永冠營建設//台灣省政府住宅及都市發展局//台灣省政府住宅及都市發展局//吳順明-德旺建設企業有限公司//宏禹建設有限公司等12戶//(C1)光順建設有限公司//德旺集團-德冠建設//德旺集團,德冠建設//
        //大研建設/實踐家建設//毅楓建設,奕捷建設//再勵建設、營豐建設//簡局能，等8名//李素葉（地主自建）//寶路、寶富、寶盛開發//合環機構   沛豐開發//永平建設.基宏實業//台鳳股份有限公司,和翰建設股份有限公司//蔡一民(地主自建)//經查證無法尋得該建設公司//
        //勝輝機構-勝輝地產//地主廖平吉//彭榮宗等70人//進OO設//國礎建設機構．僑礎建設//員寶建設；員邦建設//長耀機構/誠邦建築.村泉建設//
    }
    ////////////////////////////////////////////////////////////////////////////////
    //////////////// 將未經處理的建案名稱進行正常化處理，修改一些不正常的內容 ///////////////////
    ////////////////////////////////////////////////////////////////////////////////
    private function regulating()
    {
        $this->normalized = $this->trim($this->normalized);//去除前後空白
        $this->normalized = $this->regularize();//進行正常化處理
        return $this->normalized;//回傳正常化後的住址。並寫入常數
    }
    //-------------------- 將未經處理的建案名稱進行修改一些不正常的內容 ----------------------
    private function regularize()
    {
        $this->normalized = $this->toHalfCase($this->normalized);//將所有字元轉為半形
        $this->normalized = $this->replace([//替換錯誤的名稱以及單位
                //NOTE：以下為可編輯部份，當有需要時可自行更改
                //' ' => '',
                '《' => '(',
                '》' => ')',
                '[' => '(',
                ']' => ')',
                '「' => '(',
                '」' => ')',
                '【' => '(',
                '】' => ')',//ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ
                //'Ⅰ' => 'I',//ⅠⅤⅩ
                'no' => 'NO',
                'No' => 'NO',
                'nO' => 'NO',
                'NO' => 'NO',
                'N0' => 'NO',
                'NO.I' => 'NO.1',
                'NO.II' => 'NO.2',
                'NO.III' => 'NO.3',
                '台' => '臺',
                '厦' => '廈',
                '舘' => '館',
                '滙' => '匯',
                '峯' => '峰',
                '号' => '號',
                '双' => '雙',
                '塩' => '鹽',
                //'-(' => '(',
                //' - ' => '-',
                //' -' => '-',
                //'- ' => '-',
                '。' => '',
                '‧' => '',
                '系列' => '(',
                '作品' => '(',
                //'&' => '',
        ]);
        
        //修復缺少的右括號
        if (str_contains($this->normalized, '(')) {
            if(!str_contains($this->normalized, ')')){
                $this->normalized = $this->normalized.')';
            }
        }

        $this->normalized = $this->replace([//替換錯誤的名稱以及單位
                //NOTE：以下為可編輯部份，當有需要時可自行更改
                '-(' => '(',
                'NO.' => 'NO',
                'ON.' => 'NO',
                'NO:' => 'NO',
                ':' => '-',
                '號(双)' => '號雙號',
                '號(双號)' => '號雙號',
                '號(雙)' => '號雙號',
                '號(雙號)' => '號雙號',
                '號(單)' => '號單號',
                '號(單號)' => '號單號',
                //'&' => '、',
                '/' => '、',
                '(未公開)' => '',
                '(舊門牌)' => '',
                '(住家)' => '',
                '(獨棟)' => '',
                '(樓中樓)' => '',
                '-市區內' => '',
                '(甲標)' => '',
                '(乙標)' => '',
                '(丙標)' => '',
                '(新)' => '',
                '(地上權)' => '',
                '地上權' => '',
                '(海砂屋)' => '',
                '(辦公)' => '',
                '(純辦)' => '',
                '(八張犁)' => '(八張犁段)',
                '(花園)' => '花園',
                '大樓區' => '-大樓區',
                '大樓社區' => '大樓',
                '大廈社區' => '大廈',
                '公寓社區' => '公寓',
                '別墅社區' => '別墅',
                '華廈社區' => '華廈',
                '國宅社區' => '國宅',
                '公寓大樓' => '公寓',
                '公寓大廈' => '公寓',
                '華夏區' => '華廈區',
                '公園館' => '-公園館',
                //'區大樓' => '區-大樓區',
        ]);
        $this->normalized = $this->fixToRomanNumber();//修正以英文字母拼湊成的羅馬數字回歸正體羅馬數字（超過半數的羅馬數字被以大寫英文字母誤寫），於此定義為除錯後的原始名稱

        $this->normalized = $this->replace('/\([^0-9]*\s?\-\s?[^0-9]*\)/ui', function ($m) {
            $this->tmatches = '<span style="color:#3366cc;">[C1]</span>' . $m[0] . $this->tmatches;
            return str_replace(['-', ' '], '', $m[0]);
        });
        $this->normalized = $this->replace('/[^a-zA-Z0-9]+[a-zA-Z]\s?(、|,)\s?[a-zA-Z][^a-zA-Z0-9]+/iu', function ($m) {
            $this->tmatches = '<span style="color:#3366cc;">[C2]</span>' . $m[0] . $this->tmatches;
            return str_replace(['、', ',', ' '], '', $m[0]);
        });
        $this->normalized = $this->replace('/[\x{4e00}-\x{9fa5}]+\s?[\+\~]\s?[\x{4e00}-\x{9fa5}]+/u', function ($m) {
            $this->tmatches = '<span style="color:#3366cc;">[C3]</span>' . $m[0] . $this->tmatches;
            return strtr($m[0], ['+' => '-', '~' => '-', ' ' => '']);
        });

        $this->normalized = $this->replace('/(\d+\-\d+)/i', function ($m) {
            $this->tmatches = '<span style="color:#3366cc;">[C4]</span>' . $m[0] . $this->tmatches;
            //$this->normalized = $this->replace('/\d+\-\d+/i', function ($m) {
            return str_replace('-', '[@dash@]', $m[0]);
        });

        $this->normalized = $this->replace('/(?:(?P<pre>[a-zA-Z0-9]+\-))(?:(?P<post>[a-zA-Z]+[區棟樓]?))/iu', function ($m) {
            $this->tmatches = '<span style="color:#3366cc;">[C5]</span>' . $m[0] . $this->tmatches;
            if(mb_substr($m['post'],0,2) == 'NO' || in_array(mb_substr($m['post'],-1),array('區','棟','樓'))){
                return $m[0];
            } else{
                //return str_replace('-', '[@dash@]', $m[0]);
                return str_replace('-', '[@dash@]', $m[0]);
            }
        });


        /*
        $this->normalized = $this->replace('/[\x{4e00}-\x{9fa5}]+\-第?([0-9]{1,3}|[一二三四五六七八九十]+|[ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ])[期代輯]+/u', function ($m) {
            return str_replace(['-',' '], '', $m[0]);
        });
        */

        //------ 連字符前後皆為數字時，保留符號暫時以[@tilde@]代替 ------
        /*
        $this->normalized = $this->replace('/\d+~\d+/i', function ($m) {
            return strtr($m[0], ['~' => '[@tilde@]', ' ' => '']);
        });
        //------ 其餘的連字符以「-」替換，以利後續分組 ------
        //$this->normalized = str_replace('~', '-', $this->normalized);
        $this->normalized = $this->replace([//替換錯誤的名稱以及單位
                '~' => '-'
        ]);

        //------ Dash 前後皆為數字時，或是不為「NO」、數字、或是以「-」開頭的任何字串，再接著時，保留符號暫時以[@dash@]代替 ------
        //$this->normalized = $this->replace('/(\d+\-\d+)|(^((?<!NO)(?<![0-9]){1,}(?<!\-))\w+?[a-zA-Z0-9\-\.]+)/i', function ($m) {
        $this->normalized = $this->replace('/(\d+\-\d+)|(^((?<!NO)(?<![0-9]){1,}(?<!\-))\w+?[a-zA-Z0-9\-\.]+)/i', function ($m) {
            $this->tmatches = '<span style="color:#3366cc;">[C5]</span>' . $this->tmatches;
            //$this->normalized = $this->replace('/\d+\-\d+/i', function ($m) {
            return str_replace('-', '[@dash@]', $m[0]);
        });

        //$this->normalized = $this->replace('/((\d+\-\d+)|(.*[a-zA-Z]+\-(([a-zA-Z]+)|([0-9]{1,3}))([^a-zA-Z0-9區棟期]+|$)))/u', function ($m) {
        //    return str_replace('-', '[@dash@]', $m[0]);
        //});
       
        $this->normalized = $this->replace('/^[\x{4e00}-\x{9fa5}]{1,}[a-zA-Z]{1,3}\-[a-zA-Z]{2,}[\x{4e00}-\x{9fa5}]{0,}$/iu', function ($m) {
            return str_replace('-', '[@dash@]', $m[0]);
        });

        $this->normalized = $this->replace('/^[a-zA-Z]{1,}(\[@dash@\])NO\d+$/iu', function ($m) {
            return str_replace('[@dash@]', '-', $m[0]);
        }); 
     */   
        return $this->normalized;
    }
    //-------------------- 將已經初步正常化的住址，進行數字類的格式統一 ----------------------
    private function digitize()
    {
        //'/[一二三四五六七八九十百千]+(?=[段路街巷弄號樓])/u'(原始規則)
        //將「段路街巷弄之號樓室」之前的國字數字，一律替換為阿拉伯數字
        return $this->replace('/[零〇一二三四五六七八九十百千]+(?=([至、期區代]|$))/u', function ($m) {
            return $this->toNumber($m[0]);//國字數字轉阿拉伯數字的必要規則
        });
    }
    ////////////////////////////////////////////////////////////////////////////////
    ///////////// 將已經正常化的住址，進行規則化處理，調整為正規化的住址格式 /////////////////
    ////////////////////////////////////////////////////////////////////////////////
    //-------------------- 將已經正常化的住址，進行規則化處理，調整為正規化的住址格式 ----------------------
    private function normalizing()
    {
        //【MD7】當建案名稱中包含中華民國的縣市名時，無需進行接下來的動作，並終止後續規則化分析處理，暫時將其顯示為灰色
        if(preg_match('/^('.implode('|',$this->cityskip).')/u',$this->normalized)){
            $this->tmatches = '<span style="color:#33ccff;">【MD7】</span>' . $this->tmatches;
            $this->bcMode = 7;
            $this->normalized = '<span style="color:lightgray;">'.$this->normalized.'</span>';//NOTE：暫時設定為灰色，應移除
            return $this->normalized;
        }

        $this->normalized = $this->titleElimination();
        //$this->normalized = $this->phaseSplit();
        return $this->normalized;//回傳正規化後的住址，並寫入常數，此值將為沒有括號內容的單純字串
    }

    ////////////////////////////////////////////////////////////////////////////////
    ///////////// 進行規則化處理前，排除以及區分顯著的建型，或是無正規建案名稱，並 ////////////
    ///////////// 且或以地址為名的建案 ////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////
    private function titleElimination()
    {
    
        $this->normalized = $this->replace('/(?:(?P<numpart>\s?\-?\s?第?\s?[一二三四五六七八九十壹貳貮參参叄叁肆䦉伍陸柒扒玖拾]+))(?:(?P<post>[區棟樓]))/ui', function ($m) {
            $this->tmatches .= '<span style="color:#00cc99;">[N1]</span>'.$m[0];
            return $this->toNumber(str_replace(['第','-',' '], '', $m['numpart'])) . $m['post'];//做分區預處理（賦予固定格式以利後續規則化處理）
        });

        $this->normalized = $this->replace('/(?:(?P<top>.+?))(?:(?P<pre>\-?富?))(?:(?P<numpart>[一二三四五六七八九十壹貳貮參参叄叁肆䦉伍陸柒扒玖拾]+[期代輯]))(?:(?P<post>([居城田情旭緣]|(別墅))?))/ui', function ($m) {
            $this->tmatches .= '<span style="color:#00cc99;">[N2]</span>'.$m['post'];
            if ($m[0] == '富二代' || preg_match('/^[0-9一二三四五六七八九十]+期/iu', $m['top']) || preg_match('/^[一二三四五六七八九十]代([居城田情旭緣]|(別墅))$/ui', $m['numpart'].$m['post'])) {
                return $m[0];
            }
            $postContent = $this->assortPost($m['post']);
            return $m['top'] . $m['pre'] . $this->toNumber(str_replace(['期','代','輯'], '', $m['numpart'])) . $postContent;//做分區預處理（賦予固定格式以利後續規則化處理）
        });

        $this->normalized = $this->replace('/(?:(?P<pre>\-))(?:(?P<numpart>[一二三四五六七八九十壹貳貮參参叄叁肆䦉伍陸柒扒玖拾]+))$/ui', function ($m) {
            $this->tmatches .= '<span style="color:#00cc99;">[N3]</span>'.$m[0];
            return $m['pre'] . $this->toNumber($m['numpart']) . '期';//做分區預處理（賦予固定格式以利後續規則化處理）
        });


        $this->normalized = $this->replace('/NO[0-9]+/ui', function ($m) {
            $this->tmatches .= '<span style="color:#00cc99;">[N4]</span>'.$m[0];
            $this->parsePhase = str_replace('NO', '', $m[0]);
            return '';//做分區預處理（賦予固定格式以利後續規則化處理）
        });


        //++++++++++++++++++++ 【E1】建案名稱中括號內容分析 +++++++++++++++++++++
        //【E1】處理括號內字串，分析、指派並擷取後將其刪除，並分類儲存到相關變數中
        $this->normalized = $this->replace('/(\-?\(.+?\)|\-.+?)$/ui', function ($m) {
            $this->tmatches .= '<span style="color:#ff0066;">【E1】</span>'.$m[0];
            //$inBrackets = str_replace(['(',')','-',' '], '', $m[0]);
            $postContent = $this->assortPost($m[0]);
            return $postContent;//做期數預處理（賦予固定格式以利後續規則化處理）
        });


        //【E2】排除以及區分顯著的建型，不區分是否存在於括號內，分析並擷取後將其刪除，並儲存到 parseBCtype 變數中
        //$this->normalized = $this->replace('/(?:(?P<pre>([社特]?區?)))(?:(?P<post>(\-?\(?((別墅|公寓|透天){1,2}(區?)|((大樓|大廈|華廈)區))\)?$)))/u', function ($m) {
        $this->normalized = $this->replace('/(?:(?P<pre>([社特]?區?)))(?:(?P<post>((\s?\-?\s?\(?(別墅|公寓|透天|大樓|大廈|華廈|國宅|住宅|辦公|廠辦){1,2}區?|\s?\-\s?\(?[a-zA-Z0-9]+[區棟樓側]?|\s?\-?\s?\(?[a-zA-Z0-9甲乙丙丁戊]+[區棟樓側])\)?$)))/u', function ($m) {
            $this->tmatches .= '<span style="color:#cccc00;">【E2】</span>'.$m[0];
            $postContent = $this->assortPost($m['post']);
            //$getBCtype = str_replace(['區','(',')','-'], '', $m['post']);
            //$this->parseBCtype =  $getBCtype;
            return $m['pre'] . $postContent;//僅回傳post段回規則化名稱
        });
        


        //++++++++++++++++++++ 【E】建商簡稱分析 +++++++++++++++++++++++
        //【E3】將尾端明顯存在的建商簡稱析出重新歸類
        $this->normalized = $this->replace('/^(?:(?P<bctitle>.+))(?:(?P<builder>([\-\(].+(建設)\)?)))/ui', function ($m) {
            if (empty($m['builder'])) {
                return $m[0];
            }
            $getBuilderSplit = explode('建設', str_replace(['(',')','-'], '', $m['builder']));
            $getBuilderAbbr = $getBuilderSplit[0];
            if (!empty($getBuilderAbbr) && $getBuilderAbbr != $this->builderAbbr) {
                $this->builderAbbr = $getBuilderAbbr;
            }
            $this->tmatches .= '<span style="color:#009900;">[E3]</span>' . $m['builder'];
            if(empty($m['bctitle'])){
                return $m['builder'];
            }
            return $m['bctitle'];
        });

        //【E4】將開頭明顯存在的建商簡稱析出重新歸類
        $this->normalized = $this->replace('/^(?:(?P<builder>(.+(建設)((NO)?[0-9一二三四五六七八九十]+)?期?)))(?:(?P<bctitle>.*))/ui', function ($m) {
            if (empty($m['builder'])) {
                return $m[0];
            }
            $getBuilderSplit = explode('建設', $m['builder']);
            $getBuilderAbbr = $getBuilderSplit[0];
            if (!empty($getBuilderAbbr) && $getBuilderAbbr != $this->builderAbbr) {
                $this->builderAbbr = $getBuilderAbbr;
            }
            $this->tmatches .= '<span style="color:#ff9900;">[E4]</span>'.$m['builder'];
            if(empty($m['bctitle'])){
                return $m['builder'];
            } 
            return str_replace(['(',')','-'], '', $m['bctitle']);
        });



        //+++++++++【MD5】排除建案名稱中以住址片段或非以建案名稱命名的名稱 ++++++++++
        //【MD5】將建案名稱中，包含有「路街道段巷」任一字元後接著「數字+巷號弄」（可重複）的字串,視為不正規化建案名稱，歸類後設為不後續處理
        // 建案名稱中本身帶有「街號」如唐寧街、福人街等等，則視為例外，可繼續進行規則化分析
        if (preg_match('/[^唐寧|福人][路街道段巷](\d+[巷號弄]?([\-\,\~]\d+[巷號弄])?)+/ui', $this->normalized)) {
            $this->tmatches = '<span style="color:#33ccff;">[MD5]</span>' . $this->tmatches;
            $this->bcMode = 5;
            $this->normalized = '<span style="color:lightgray;">'.$this->normalized.'</span>';//NOTE：暫時設定為灰色，應移除
            return $this->normalized;
        } else {//若非為不正規化建案名稱，則繼續處理規則化
            $this->normalized = $this->phaseSplit();
            return $this->normalized;
        }

    }

    private function phaseSplit()
    {
        //【P1】以建案期數切割，前段保留，後段分析歸類後，依分析結果保留或刪除
        $this->normalized = $this->replace('/(?![0-9甲(狀元及)]+)(?:(?P<pre>(.+?)))(?:(?P<phase>(((\s?\-?\s?第[0-9]+)|(\s?\-?\s?([0-9]{1,3})))([期代輯]|$)|\s?\-?\s?(NO[0-9]+)+)))(?:(?P<post>(.*)))/ui', function ($m) {
            $this->bcMode = 2;
            if(!empty($this->parsePhase)){
                return $m[0];
            }
            $inPost = isset($m['post']) ? str_replace(['(',')','-',' '], '', $m['post']) : '';
            $postContent = $this->assortPost($inPost);
            $this->tmatches .= '<span style="color:#009900;">[P1]</span>'.$m['phase'];
            //$numPart = $this->toNumber(str_replace(['第','期','代','輯','(',')','-','NO',' '], '', $m['phase']));//將匹配出的數字類字串轉換為阿拉伯數字
            $numPart = $this->numAnalysis($m['phase']);
            if(!empty($numPart)){
                $this->parsePhase =  $numPart;
                return $m['pre'] . $postContent;
            }
            return $m['pre'] . $m['phase'] . $postContent;
        });


        //【L3】
        //$this->normalized = $this->replace('/(?:(?P<pre>(([\x{4e00}-\x{9fa5}]{2}|[0-9ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ]期?)\-?\(?)))(?:(?P<area>(([A-Z]{1}[0-9]{0,2}|('.implode('|',$this->multichars).')|[\x{4e00}-\x{9fa5}]{2})((特區|專區)|[區棟樓側]))))(?:(?P<post>.*?))$/iu', function ($m) {//|真善美
        //$this->normalized = $this->replace('/(?:(?P<pre>((.{2,}?|[0-9ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ]期?)\-?\(?)))(?:(?P<area>(([A-Z]{1}[0-9]{0,2}|('.implode('|',$this->multichars).')|[\x{4e00}-\x{9fa5}]{2})((特區|專區)|[區棟樓側]))))(?:(?P<post>.*?))$/iu', function ($m) {//|真善美
        //$this->normalized = $this->replace('/(?:(?P<pre>((.{2,}?|[0-9]期?)\-?\(?)))(?:(?P<area>(([A-Z]{1}[0-9]{0,2}|('.implode('|',$this->multichars).')|[\x{4e00}-\x{9fa5}]{2})((特區|專區)|([a-zA-Z甲乙丙丁戊]*[公園|會館|公館|賓館|分館|別館|行館|區|棟|樓|側|館][a-zA-Z]?)))))(?:(?P<post>([a-zA-Z0-9]*[公園|會館|公館|賓館|分館|別館|行館|區|棟|樓|側|館]?\)?)*))/iu', function ($m) {//|真善美
        $l3pattern = "/";
        $l3pattern .= "(?![a-zA-Z])";
        $l3pattern .= "(?:(?P<pre>((.{2,}?|[0-9]期?)\-?\(?)))";
        $l3pattern .= "(?:(?P<area>(";
        $l3pattern .= "(";
        $l3pattern .= "[a-zA-Z]+[0-9]{0,2}[區棟樓側館]|";
        $l3pattern .= "[甲乙丙丁戊]+[區棟]?|";
        //$l3pattern .= "(".implode('|',$this->multichars).")((特區|專區)|[區棟樓側館])|";
        $l3pattern .= "(".implode('|',$this->multichars).")((特區|專區|會館區)|[區棟樓側館])|";
        $l3pattern .= "[^a-zA-Z0-9甲乙丙丁戊]{2}((特區|專區)|[區棟樓側])";
        //$l3pattern .= "|[\x{4e00}-\x{9fa5}]{2}((特區|專區)|[區棟樓側館])";
        $l3pattern .= ")";
        $l3pattern .= "";
        $l3pattern .= ")))";
        $l3pattern .= "(?:(?P<post>.*?))$";
        $l3pattern .= "/iu";
        
        $this->normalized = $this->replace($l3pattern, function ($m) {//|真善美
            $this->tmatches .= '<span style="color:#009900;">[L3]</span>'.$m['area'];
            if(preg_match('/^(.{1})特區$/u', $m['area']) == true){
                return $m[0];
            } else if(preg_match('/.*?(社區|園區|業區|劃區|大樓|獨棟|樓中樓)[a-zA-Z0-9]*\)?/u', $m['area'].$m['post'])){//避免「樓區園」疊字混淆處理
                if(preg_match('/.*?(花園區|公園區|校園區|大樓區)[a-zA-Z0-9]*\)?/u', $m['area'].$m['post']) != true){//包含「樓區園」疊字時，排除以下匹配目標
                    return $m[0];
                }
            }
            $this->parseSubarea = str_replace(['期','(',')','-',' '], '', $m['area'] . $m['post']) . $this->parseSubarea;
            return str_replace(['(',')','-',' '], '', $m['pre']);

        });

       
        return $this->normalized;
    }

    private function assortPost($post='')
    {   if(empty($post)){ return ''; }
        
        if($post == ')'){
            $this->tmatches .= '<span style="color:#996633;">[AP0]</span>'.$post;
            return $post;
        } else {
            $doPost = str_replace(['(',')','-','、',' '], '', $post);
            if (preg_match('/^NO([0-9]{1,2})$/', $doPost)) {
                $this->tmatches .= '<span style="color:#996633;">[AP1]</span>'.$post;
                $this->parsePhase = str_replace('NO', '', $doPost);
            //} elseif (preg_match('/^([0-9一二三四五六七八九十]+)[期代輯]?$/u', $post)) {
                //$this->parseSubarea = str_replace('區', '', $post) . $this->parseSubarea;
            } elseif (preg_match('/^([0-9]+)[期代輯]?$/u', $doPost)) {
                $this->tmatches .= '<span style="color:#996633;">[AP2]</span>'.$post;
                $getNum = $this->toNumber(str_replace(['期','代','輯'], '', $doPost));
                if($getNum > $this->phaseMax){
                    return $post;
                }
                $this->parsePhase = $getNum;
            } elseif (preg_match('/^之([0-9一二三四五六七八九十]+)$/u', $doPost)) {
                $this->tmatches .= '<span style="color:#996633;">[AP3]</span>'.$post;
                $this->parseAreaExt .= '之' . $this->toNumber(str_replace('之', '', $doPost));
            } elseif (preg_match('/^(\(?(社區|別墅|公寓|透天|大樓|大廈|華廈|國宅|住宅|辦公|廠辦){1,2}區?\)?)$/u', $doPost)) {
                $this->tmatches .= '<span style="color:#996633;">[AP4]</span>'.$post;
                if($doPost=='社區'){
                    return '社區';
                }
                $this->parseBCtype =  str_replace('區', '', $doPost) . $this->parseBCtype;
            } elseif (preg_match('/^(([甲乙丙丁戊])|(.*?[區棟樓側]))$/u', $doPost)) {
                $this->tmatches .= '<span style="color:#996633;">[AP5]</span>'.$post;
                $this->parseSubarea =  $doPost . $this->parseSubarea;
            } elseif (preg_match('/^(.*?[巷弄街路段號宅店舖])$/u', $doPost)) {
                $this->tmatches .= '<span style="color:#996633;">[AP6]</span>'.$post;
                $this->parseLocated = $doPost . $this->parseLocated;
            } else {
                $this->tmatches .= '<span style="color:#996633;">[AP7]</span>'.$post;
                $this->parseAka2 = $doPost . $this->parseAka2;
            }
            return '';
        }
        $this->tmatches .= '<span style="color:#996633;">[AP10]</span>'.$post;
        return $post;//做期數預處理（賦予固定格式以利後續規則化處理）

    }
    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////// Functions //////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////
    private function trim($subject, $characterMask = " \t\n\r\0\x0B")
    {
        return trim($subject, $characterMask);
    }
    private function toHalfCase($subject)
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
            '～' => '~'
        ];
        return strtr($subject, $fullCaseMap);
    }
    private function twnTOarabic($cNum)
    {
        $len = mb_strlen($cNum);
        $cTOnumObj = [
            '零' => '0',
            '壹' => '1',
            '貳' => '2',
            '貮' => '2',
            '參' => '3',
            '参' => '3',
            '叄' => '3',
            '叁' => '3',
            '肆' => '4',
            '䦉' => '4',
            '伍' => '5',
            '陸' => '6',
            '柒' => '7',
            '扒' => '8',
            '玖' => '9',
            '拾' => '10'
        ];
        if($len == 1){ return strtr($cNum, $cTOnumObj); }
        else if(preg_match('/^([零壹貳貮參参叄叁肆䦉伍陸柒扒玖拾]+)$/u', $cNum, $matches)){
            if(mb_strlen($matches[0])==2){
                if(mb_substr($matches[0], 0, 1) == "拾"){
                        $cTOnumObj['拾']="1";
                        return strtr($matches[0], $cTOnumObj);
                    }
                else{
                        $cTOnumObj['拾']="0";
                        return strtr($matches[0], $cTOnumObj);
                    }
            }else{
                $cTOnumObj['拾']="";
                return strtr($matches[0], $cTOnumObj);
            }
        }else{ return $cNum; }
    }
    private function fixToRomanNumber()
    {
        //$phaseString = '';
        $this->normalized = str_replace('VIII','Ⅷ',$this->normalized);//先將不易混淆的錯誤羅馬數字矯正
        $this->normalized = str_replace('VII','Ⅶ',$this->normalized);//先將不易混淆的錯誤羅馬數字矯正
        $this->normalized = str_replace('III','Ⅲ',$this->normalized);//先將不易混淆的錯誤羅馬數字矯正
        $this->normalized = str_replace('II','Ⅱ',$this->normalized);//先將不易混淆的錯誤羅馬數字矯正
        $this->normalized = $this->replace('/[\x{4e00}-\x{9fa5}|,|、|\-](VI)期?([^a-zA-Z\-]|$)/ui', function ($m) {//依次替換「VI」為正規羅馬數字「Ⅵ」
            //return str_replace('VI', 'Ⅵ', $m[0]);
            return preg_replace('/VI/i', 'Ⅵ', $m[0]);
        });
        $this->normalized = $this->replace('/[\x{4e00}-\x{9fa5}|,|、|\-](IV)期?([^a-zA-Z\-]|$)/ui', function ($m) {//依次替換「IV」為正規羅馬數字「Ⅳ」
            //return str_replace('IV', 'Ⅳ', $m[0]);
            return preg_replace('/IV/i', 'Ⅳ', $m[0]);
        });
        $this->normalized = $this->replace('/[\x{4e00}-\x{9fa5}|,|、|\-](IX)期?([^a-zA-Z\-]|$)/u', function ($m) {//依次替換「IX」為正規羅馬數字「Ⅸ」
            //return str_replace('IX', 'Ⅸ', $m[0]);
            return preg_replace('/IX/i', 'Ⅸ', $m[0]);
        });

        $this->normalized = $this->replace('/[\x{4e00}-\x{9fa5}|,|、|\-]I期?([^a-zA-Z\-]|$)/ui', function ($m) {//依次替換「I」為正規羅馬數字「Ⅰ」
            //return str_replace('I', 'Ⅰ', $m[0]);
            return preg_replace('/I/i', 'Ⅰ', $m[0]);
        });
        $this->normalized = $this->replace('/[\x{4e00}-\x{9fa5}|,|、|\-]V期?([^a-zA-Z\-]|$)/ui', function ($m) {//依次替換「V」為正規羅馬數字「Ⅴ」
            //return str_replace('V', 'Ⅴ', $m[0]);
            $this->parseAka2 = $m[0];
            return preg_replace('/V/i', 'Ⅴ', $m[0]);
        });
        $this->normalized = $this->replace('/[\x{4e00}-\x{9fa5}|,|、|\-]X期?([^a-zA-Z\-]|$)/u', function ($m) {//依次替換「X」為正規羅馬數字「Ⅹ」
            //return str_replace('X', 'Ⅹ', $m[0]);
            return preg_replace('/X/i', 'Ⅹ', $m[0]);
        });


        $this->normalized = $this->replace('/\s?第?\s?[ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ]\s?期?/u', function ($m) {
            $pureRN = str_replace(['第','期',' '], '', $m[0]);
            $numReplace = [
                'Ⅰ' => '1',
                'Ⅱ' => '2',
                'Ⅲ' => '3',
                'Ⅳ' => '4',
                'Ⅴ' => '5',
                'Ⅵ' => '6',
                'Ⅶ' => '7',
                'Ⅷ' => '8',
                'Ⅸ' => '9',
                'Ⅹ' => '10'
            ];
            $numSTR = strtr($pureRN, $numReplace);

            return $numSTR.'期';
        });




        //$this->origFixed = $this->normalized;//此值固定為除錯後的原始名稱，之後不再更動
        //$this->normalized = str_replace(['Ⅰ','Ⅱ','Ⅲ','Ⅳ','Ⅴ','Ⅵ','Ⅶ','Ⅷ','Ⅸ','Ⅹ'],[1,2,3,4,5,6,7,8,9,10],$this->normalized);
        return $this->normalized;
    }
    /*
    private function insideBrackets()
    {
        if (strpos($this->normalized, '(')) {//當原名稱包含括號時
            preg_match_all('/\((.*?)\)/', $this->normalized, $match);//提取「所有」左右括號之間的內容並定義
            $this->normalized = preg_replace('/\((.*?)\)/', '', $this->normalized);//定義爲變數後即可移除「所有」括號以及括號間內容
            if (!empty($match[1])) {//當括號內容不為空時
                $matchJoin = implode(",",$match[1]);
                if (preg_match('/大樓|華廈|別墅|透天|公寓|座|棟|區|座|特區|樂章|篇|樓|店/i', $matchJoin)) {//當括號內容包含「別墅」、「大樓」、「區」...等標注時
                    $this->parseSubarea = $matchJoin;//將括號內容以逗號連接，此時單組結果沒有逗號，而兩組以上的結果將以逗號分開
                } else {//比對不到含「「別墅」、「大樓」、「區」...等標注時，只取陣列前兩值（因為僅預留兩欄位），分別定義別名欄位
                    $this->parseAka1 = !empty($match[1][0]) ? $match[1][0] : '';//定義「別名1」變數
                    $this->parseAka2 = !empty($match[1][1]) ? $match[1][1] : '';//定義「別名2」變數
                }
            }
        }
        return $this->normalized;
    }
    */
    private function insideBrackets()
    {
        if (strpos($this->normalized, '(')) {//當原名稱包含括號時
            preg_match_all('/\((.*?)\)/', $this->normalized, $match);//提取「所有」左右括號之間的內容並定義
            $this->normalized = preg_replace('/\((.*?)\)/', '', $this->normalized);//定義爲變數後即可移除「所有」括號以及括號間內容
            if (!empty($match[1])) {//當括號內容不為空時
                $matchJoin = implode(",",$match[1]);
                if (preg_match('/大樓|華廈|別墅|透天|公寓|座|棟|區|座|特區|樂章|篇|樓|店/i', $matchJoin)) {//當括號內容包含「別墅」、「大樓」、「區」...等標注時
                    $this->parseSubarea = $matchJoin;//將括號內容以逗號連接，此時單組結果沒有逗號，而兩組以上的結果將以逗號分開
                } else {//比對不到含「「別墅」、「大樓」、「區」...等標注時，只取陣列前兩值（因為僅預留兩欄位），分別定義別名欄位
                    $this->parseAka1 = !empty($match[1][0]) ? $match[1][0] : '';//定義「別名1」變數
                    $this->parseAka2 = !empty($match[1][1]) ? $match[1][1] : '';//定義「別名2」變數
                }
            }
        }
        return $this->normalized;
    }











    private function replace($regexp, $replacement = null)
    {
        if (is_array($regexp) == true && is_null($replacement) == true) {
            return strtr($this->normalized, $regexp);
        }

        if (is_callable($replacement) == true) {
            return preg_replace_callback($regexp, $replacement, $this->normalized);
        }

        return preg_replace($regexp, $replacement, $this->normalized);
    }

    private function numAnalysis($numSTR){
        $numReplace = [
            ' ' => '',
            '(' => '',
            ')' => '',
            '-' => ''
        ];
        $numSTR = strtr($numSTR, $numReplace);
        if(preg_match('/^([0-9ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ一二三四五六七八九十]+)$/', $numSTR)){
            $setNumber = $this->toNumber($numSTR);
            if($setNumber > $this->phaseMax){
                return '';
            } else {
                return $setNumber;
            }
        }
        $numReplace = [
            //' ' => '',
            '第' => '',
            //'(' => '',
            //')' => '',
            //'-' => '',
            '、' => '|',
            '期' => '|',
            '代' => '|',
            '輯' => '|',
            'NO' => '|'
        ];
        $getNumStandard = strtr($numSTR, $numReplace);
        if(mb_substr($getNumStandard, -1) == '|'){
            $getNumStandard = mb_substr($getNumStandard, 0, -1);
        }
        if(mb_substr($getNumStandard, 0, 1) == '|'){
            $getNumStandard = mb_substr($getNumStandard, 1);
        }
        /*
        if(is_numeric(str_replace('|', '', $getNumStandard)) != true){
            $this->parseSubarea = $getNumStandard;
            return '';
        }
        */
        $getNumSplit = explode('|', $getNumStandard);
        $setNumString = '';
        if(count($getNumSplit) > 1){
            foreach ($getNumSplit as $key => $value) {
                $setNumString .= $value.',';            
            }
            $setNumString = mb_substr($setNumString, 0, -1);
        } else {
            $setNumString = $this->toNumber($getNumStandard);
        }

        return $setNumString;


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
            '十' => '10',

            'Ⅰ' => '1',//ⅠⅤⅩ
            'Ⅱ' => '2',
            'Ⅲ' => '3',
            'Ⅳ' => '4',
            'Ⅴ' => '5',
            'Ⅵ' => '6',
            'Ⅶ' => '7',
            'Ⅷ' => '8',
            'Ⅸ' => '9',
            'Ⅹ' => '10',

            '零' => '0',
            '壹' => '1',
            '貳' => '2',
            '貮' => '2',
            '參' => '3',
            '参' => '3',
            '叄' => '3',
            '叁' => '3',
            '肆' => '4',
            '䦉' => '4',
            '伍' => '5',
            '陸' => '6',
            '柒' => '7',
            '扒' => '8',
            '玖' => '9',
            '拾' => '10'

        ];
        if($len == 1){ return strtr($cNum, $cTOnumObj); }
        else if(preg_match('/^([〇一二三四五六七八九十零壹貳貮參参叄叁肆䦉伍陸柒扒玖拾]+)$/u', $cNum, $matches)){
            $firstNum = mb_substr($matches[0], 0, 1);
            if(mb_strlen($matches[0])==2){
                if($firstNum == "十" || $firstNum == "拾"){
                        $cTOnumObj['十']="1";
                        $cTOnumObj['拾']="1";
                        return strtr($matches[0], $cTOnumObj);
                    }
                else{
                        $cTOnumObj['十']="0";
                        $cTOnumObj['拾']="0";
                        return strtr($matches[0], $cTOnumObj);
                    }
            }else{
                $cTOnumObj['十']="";
                $cTOnumObj['拾']="";
                return strtr($matches[0], $cTOnumObj);
            }
        }else{ return $cNum; }
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
    public function builderAbbr()
    {
        return $this->builderAbbr;
    }
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

    public function regularizeString()
    {
        return $this->outputs['regularizeString'];
    }

    public function normalized()
    {
        return array(
            'builderAbbr' => $this->builderAbbr,
            'normalized' => $this->normalized,
            'parseBCtype' => $this->parseBCtype,
            'parsePhase' => $this->parsePhase,        
            'parseAka1' => $this->parseAka1,
            'parseAka2' => $this->parseAka2,
            'parseSubarea' => $this->parseSubarea,
            'parseLocated' => $this->parseLocated,
            'parseAreaExt' => $this->parseAreaExt,
            'origFixed' => $this->origFixed,
            'parseBCmode' => $this->bcMode,
            'tmatches' => $this->tmatches,
        );
        //'@@【NT】<span style="color:red;">'.$this->normalized.'</span>@@【OF】<span style="color:blue;">'.$this->origFixed.'</span>@@【IV】'.$this->builderAbbr.'@@【PH】'.$this->parsePhase.'@@【SB1】'.$this->parseAka1.'@@【SB2】'.$this->parseAka2.'@@【LO】'.$this->parseLocated.'@@【EX】'.$this->parseSubarea;
    }

}
