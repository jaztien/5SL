<?php
//////////////////////////// bcNormalize.class.php /////////////////////////////////
//////////////////////////// 建案名稱分析、正常化以及規則化類 ///////////////////////////
//////////////////////////// 2025-07-08 By Jaz Tien ///////////////////////////////
//////////////////////////// jaztien@gmail.com ////////////////////////////////////
class NormBC
{
    private $origFixed;// @var origFixed 原始目標字串
    private $normalized;// @var normalized 目標字串規則化
    private $origInvestor;// @var origInvestor 投資商原始字串
    private $origConstructer;// @var origConstructer 營造商原始字串
    private $investorAbbr;// @var investorAbbr 投資商前綴分析字串
    private $constructerAbbr;// @var constructerAbbr 營造商前綴分析字串
    private $ivctAbbr;// @var ivctAbbr 投資商營造商簡寫合併字串
    private $arrIVCTabbr;// @var arrIVCTabbr 投資商營造商簡寫合併陣列
    private $normBasic = [
        '、' => ',',
        ';' => ',',
        '；' => ',',
        '.' => ',',
        '/' => ',',
        '，' => ',',
        '．' => ',',
        '：' => ':',
        '-' => ',',
        '&' => ',',
        '（股）' => '',
        '(股)' => '',
        '(有)' => '',
        '(無)' => '',
        '(免會同)' => '',
        '(詳名冊)' => '',
        '代定' => '',
        '代訂' => '',
        '待訂' => '',
        '待定' => '',
        '未知' => '',
        '代埋人' => '代理人',
        '屋主自建' => '',
        '自行承造' => '',
        '自建' => '',
        '暫無' => '',
        '營照' => '營造',
        '贏造' => '營造',
        '營造工程' => '營造',
        '件設' => '建設',
        '連設' => '建設',
        '建捨' => '建設',
        '建社' => '建設',
        '健設' => '建設',
        '建? ' => '建設',
        '股份有限公司' => '',
        '股份有限公' => '',
        '股份有' => '',
        '股份限' => '',
        '有限公司' => '',
        '無限公司' => '',
        '有線公司' => '',
        '無線公司' => '',
    ];
    private $skipBasic = "待定|待查|待補|不明|不清楚|查詢中|地主|自售|國民住宅|私人|自建|委建|建構中|無|查無|待確認|暫定|未知|不知|地主|個人名義|個人|不詳|無法查詢|無資料|company|未提供|建商|營商|承包商|營造商|查無資料|不可考|改建|社區|籌備|啟造|起造|OO|Ｏ|中華民國|同鄉會|農會|同業公會|多媒體|北捷|合作社|警察局|信用社|國宅|都更|都市更新|公用社|經濟部|學校|政府|財政部|教育部|法務部|內政部|國防部|交通部|交通處|公路局|調查局|林務局|市場處|地政處|婦女會|國宅處|國民黨|國立|小學|中學|大學|師大|鄉公所|教會|財團法人|管委會|福委會|浸信會|水利會|公教住宅|試驗所|就業所|中研院|農學會";
    private $passBasic = "企業社|企業|實業社|實業|全球|興業|商業大樓|產業|貿易|事業|事務所|事務|機構|公司|工業社|商社|集團|股份|有限|建造|聯合|聯合建設|建設|關係|都市開發|開發|發展|特化|精密|建研|建築師|建築社|建築|自力造屋|投資|創新|營造廠|營造|營建|建業|工程|不動產|資產|法定|土地|地產|工廠|團隊|土木包|土木|綜合|顧問社|顧問";
    private $typeBasic = "資產|商標|物業|展業|科技|電子|育樂|鋼鐵|化學|租賃|造紙|紙業|設計|保險|生物|工業|國際|工商|機械|創新|精機|機電|課程商品|環保|生活|童衣|木器|五金廠|五金|塑膠|印刷|針織|纖維|時尚|酒店|賓館|工藝社|工藝|水泥|超級市場|網路|書局|麵粉|報業|報社|窯業|汽車|租賃|文化|能源|祭祀|油脂|倉儲|光電|生技|傢俱|化工|鋼管|電機|空調|資訊|紡織|電工|模具|玻璃|電氣|百貨|農產|房屋|系統|藝術|製衣|廣告|貨物|貨運|大飯店|飯店|電腦|管理|林業|景觀|結構|經理|規劃|醫療|器材|生醫|飼料|觀光|置地|電業|玩具廠|玩具|電線|電纜|加油站|鞋廠|電器";//商銀|銀行|
    private $lastName = "陳|林|黃|張|李|王|吳|劉|蔡|楊|許|鄭|謝|郭|洪|曾|邱|廖|賴|周|徐|蘇|葉|莊|呂|江|何|蕭|羅|高|簡|朱|鍾|施|游|詹|沈|彭|胡|余|盧|潘|顏|梁|趙|柯|翁|魏|方|孫|張簡|戴|范|歐陽|宋|鄧|杜|侯|曹|薛|傅|丁|溫|紀|范姜|蔣|歐|藍|連|唐|馬|董|石|卓|程|姚|康|馮|古|姜|湯|汪|白|田|涂|鄒|巫|尤|鐘|龔|嚴|韓|黎|阮|袁|童|陸|金|錢|邵|于|崔|任|夏|譚|韋|賈|熊|孟|秦|閻|雷|龍|段|郝|孔|史|毛|常|萬|顧|賀|尹|牛|倪|梅|褚";
    private $lastNameExcept = ['葉財記','金漢官','金旺宏','金旺鴻','金旺盛','金運金','金連城','金聯盛','金榮朝代','金添富','金棟隆','金寶弘','金廣威','金萬樹','金帝王','金順祥','金亮億','金日盛','金旺盛','金泰成','金和美','金禾豐','金建興','金冠竹','金富陞','金滿意','金士發','金茂揚','金祥發銅板','萬神殿','萬里虹','萬誼成','戴姆樂','金山大建','陽光綠意','萬代堡','萬客隆','萬事好','龍騰建','萬年鑫','萬寶鎮','連山鼎','連大運','藍寶石','康百力','康昔力','夏威夷','林上林','林三號','馬上發'];
    
    //private $parseTitle;// @var parseTitle 分析過後的單純建案名稱
    private $parsePhase;// @var parsePhase 建案期數
    private $parseBCtype;// @var parseType 建案型態(非名稱相關敘述，如:別墅、大樓...等)
    private $parseArea;// @var parseArea 建案子區域(非名稱以及型態相關敘述，如:金區、銀區、A棟、B棟...等)
    private $parseAreaExt;// @var parseAreaExt 建案子區域屬區(子區域的再分區，如:A區、B區、之一、之二...等)
    private $parseLocated;// @var parseLocated 建案關係位置(非名稱相關敘述，如:南京東路、忠孝東路二段、56巷、八張犁段、社子店、陽光街...等)
    private $parseAka1;// @var parseAka1 建案名稱第一附屬名稱
    private $parseAka2;// @var parseAka2 建案名稱第二附屬名稱
    private $tmatches;
    private $tDebug;
    private $tempInBrackets;// @var tempInBrackets 暫存括號內容
    private $tempAfterBrackets;// @var tempInPost 暫存括號之後內容
    //private $toSubArea = false;// @var toSubArea 是否為子區域/子分屬（已存在分區值時為 true）
    private $bcMode = 1;// @var bcMode 建案名稱命名模式歸類 1-標準命名；2-標準命名+期數；3-標準命名+分區；4-期數+分區；5-以住址命名；6-以投資商命名；7-以政府單位命名(縣市區里單位等)；8-以私人單位命名(協會、基金會、財團法人...等)
    private $phaseMax = 30;// @var phaseMax 常規分析下期數最大值（超過此值時不進行匹配分析）
    //---------------- 建案名稱例外處理 -------------------
    private $expTitlePart = ['泰第','富富','金統','幕府','大學','璞真','森','明水','半九','騰青','鋐微','硯如','城貳','綠十','堡雙','鑫如','皇居','境斗','美無','南第','匱拾','駁','緯不','當代','歡樂','砌傳','知一','豐鉅'];// @array 建案名稱例外處理
    private $SPexpTitlePart;//加了分隔符「|」的字串
    //private $expTitlePart2 = ['大學','瑝家','富富二代','富二代','韻A'];
    //匹配例外：系統僅針對兩字元建案分區名稱進行匹配，特殊命名或高於兩字元的分區名稱較為少見，不進行分析匹配，以陣列內值直接調用。
    private $expSingleChar = ['尊龍','翔鳳','白金','百金','龍鳳','澄','金','銀','龍','鳳','如','虹','藍','意','日','捷','新'];//,'捷','寶'涵,'星'
    private $SPexpSingleChar;//加了分隔符「|」的字串
    //
    private $expMultiChars = ['航空城','小湯泉','小國揚','大聯盟','京都城','惠比壽','大雅市','博愛大道','CEO','二三','世家樓中','文華道','比佛利','忠義國小','真善美','紀念館','新世紀','新生活','麗水苑','江水藍','達文西','貝多芬','莫札特','維瓦第','迷迭香','波士頓','夏威夷','曼哈頓','威尼斯','伊莉諾','奧斯汀','柏克萊','松柏樓','懷石庭','植物園','葉綠宿','地中海','坎城新','大吉利','國家公園','高鐵新','台北大學'];
    private $SPexpMultiChars;//加了分隔符「|」的字串
    //匹配例外：名稱單位前綴例外處理，超過兩字的單位前綴可進行獨立分析，例如「新竹高科技園區」，自動辨識僅為兩字元，若無例外獨立處理，系統將辨識為「新竹高」與「科技園區」
    private $expMultiUnit = ['高科技'];
    private $SPexpMultiUnit;//加了分隔符「|」的字串
    //匹配例外：解決建案名稱最末字元為「第」時，與「第」(後接期數)相衝突的情況，如「狀元及第一期」中「第」為名稱而非為期數的字元，系統應辨識為「狀元及第」與「一期」，而非「狀元及」與「第一期」
    private $expDI = ['甲第','元及第','高吉第','上第','高第','九龍世第','鎮金第'];
    private $SPexpDI;//加了分隔符「|」的字串
    //匹配例外：解決建案名稱內中帶有數字+「區」或是名稱末字元為「區棟樓館」其一時，避免系統誤認為區域的情況，例如「巴黎十六區」整句為建案名稱，而非為「巴黎」社區中的第16區
    private $expArea = ['上東區','YOYO館','巴黎十六區','巴黎16區','巴黎第六區','巴黎第6區'];//'YOYO館',
    private $SPexpArea;//加了分隔符「|」的字串
    //匹配例外：解決建案名稱中帶有數字+後綴，但卻不是期數的情況，例如「皇家1號苑」、「費城3部曲」等等，整句為建案名稱，而非為「皇家」社區第一期名為「號苑」的區域或別稱。
    //private $expPhase = ['號院','號苑','部曲'];
    private $expPhase = ['樂章','篇章','重奏','部曲','號苑','號院','號','章','篇','部','品','寀','季','園','等','見'];
    private $SPexpPhase;//加了分隔符「|」的字串
    //匹配例外：帶有城市名稱的建案名稱，系統將自動排除而不進行分析匹配
    private $expCity = ['臺北市','新北市','桃園市','新竹縣','新竹市','苗栗縣','臺中市','彰化縣','南投縣','雲林縣','嘉義縣','嘉義市','臺南市','高雄市','屏東縣','花蓮縣','臺東縣','澎湖縣','金門縣','連江縣'];
    private $SPexpCity;//加了分隔符「|」的字串
    //建案名稱中建物類型的前綴字串
    private $prefixType = ['全新','舊'];
    private $SPprefixType;//加了分隔符「|」的字串
    //建案名稱中建物類型前綴字串的延伸，通常為該類型的相關使用功能
    //private $functionType = ['綜合','高科技','科技','金融','財經','創新','藝術','國際','科學','工業','產業','智慧','管理','生醫','大學','企業','商業','工商','商務','住宅','辦公','住辦','商辦','廠辦','電梯','車站','捷運','共構','溫泉度假','溫泉','度假','站前','庭園'];
    private $functionType = ['公共','綜合','高科技','科技','科學','金融','財經','經貿','創新','藝術','國際貿易','貿易','國際','科學','工業','產業','實業','通商','智慧','管理','生醫','大學','企業','商業','工商','商務','住宅','辦公','住辦','商辦','廠辦','產經','電梯','車站','車庫','雙捷運','捷運','共構','溫泉度假','溫泉','度假'];
    //private $functionType = ['電梯','車站','車庫','雙捷運','捷運','共構','溫泉度假','溫泉','度假'];
    private $SPfunctionType;//加了分隔符「|」的字串
    //用來分析建案名稱中相關建物類型的字串
    //private $bcTypeBasic = ['社區','小別墅','大別墅','別墅','公寓','透天','金大樓','大樓','大廈','華廈','國宅','廠房','工廠','金套房','套房','商圈','店面','美居','名廈','園區','住宅','廣場'];//,'廣場'
    private $bcTypeBasic = ['社區','小別墅','大別墅','別墅','公寓','透天','金大樓','大樓','大廈','華廈','國宅','廠房','工廠','金套房','套房','商圈','店面','美居','名廈','園區','住宅','廣場'];//,'廣場'
    private $SPbcTypeBasic;//加了分隔符「|」的字串
    //    '公寓','透天厝','店面','大樓','華廈','套房','廠房','廠辦','農舍','倉庫','別墅'
    private $bcModeBasic = ['公寓','透天','店面','大廈','大樓','華廈','套房','廠房','廠辦','農舍','倉庫','別墅'];//,'廣場'
    private $SPbcModeBasic;//加了分隔符「|」的字串
    //---------------- //建案名稱例外處理【結束】 -------------------
    /////////
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
    public function __construct($bctitle = '', $investor = '', $constructer = '')
    {
        if (!empty($bctitle)) {
            $this->set($bctitle, $investor, $constructer);
        }
    }
    /**
     * @param  static|string  $bctitle
     * @return $this
     */
    private function set($bctitle, $investor, $constructer)
    {
        //------------- 投資商簡稱前綴 -------------
        $this->origFixed = $bctitle;//起始時先行定義住址，並指定為未經處理的住址
        $this->origInvestor = $investor;
        $this->origConstructer = $constructer;
        $this->investorAbbr = $investor;//起始時先行定義投資商，並指定為未經處理的投資商名稱
        $this->constructerAbbr = $constructer;//起始時先行定義投資商，並指定為未經處理的投資商名稱  
        $this->investorAbbr = $this->extractInvestor();//將未經處理的投資商名稱進行解析並提取代表字串處理
        $this->constructerAbbr = $this->extractConstructer();
        $this->ivctAbbr = '';
        $this->arrIVCTabbr = [];
        //------------- 建案名稱 -------------
        $this->normalized = $bctitle;//起始時先行定義原始建案名稱，並指定為未經處理的名稱，此值最終將為除錯後的原始名稱
        
        //$this->SPexpTitlePart = implode('|', $this->expTitlePart);//加了分隔符「|」的字串
        $this->SPexpSingleChar = implode('|', $this->expSingleChar);//加了分隔符「|」的字串
        $this->SPexpMultiChars = implode('|', $this->expMultiChars);//加了分隔符「|」的字串
        $this->SPexpMultiUnit = implode('|', $this->expMultiUnit);//加了分隔符「|」的字串
        $this->SPexpDI = implode('|', $this->expDI);//加了分隔符「|」的字串
        $this->SPexpArea = implode('|', $this->expArea);//加了分隔符「|」的字串
        $this->SPexpPhase = implode('|', $this->expPhase);//加了分隔符「|」的字串
        $this->SPexpCity = implode('|', $this->expCity);//加了分隔符「|」的字串
        $this->SPprefixType = implode('|', $this->prefixType);//加了分隔符「|」的字串
        $this->SPfunctionType = implode('|', $this->functionType);//加了分隔符「|」的字串
        $this->SPbcTypeBasic = implode('|', $this->bcTypeBasic);//加了分隔符「|」的字串
        //$this->xxxxx = implode('|', $this->xxxxx);
        //$this->xxxxx = implode('|', $this->xxxxx);
        
        $this->normalized = $this->regulating();//將未經處理的建案名稱進行正常化處理，修改一些不正常的內容
        //$this->normalized = $this->normalizing();
        /*
        $this->normalized = $this->normalizing();//將已經正常化的住址，進行規則化處理，調整為正規化的住址格式
        $this->groups = $this->grouping();//將之前已處理完成並正規化的住址準進行拆分，並創建二維陣列。grouping()中包含特殊命名處理。
        $this->mapping = $this->remapping();//將正規化後的二維陣列，進行重新構造，並將住址進行重新編排，產生各種不同需求的住址內容排列
        */
    }

    ////////////////////////////////////////////////////////////////////////////////
    ////////////////// 將未經處理的投資商名稱進行解析並提取代表字串處理 ////////////////////
    ////////////////////////////////////////////////////////////////////////////////
    public function extractInvestor()
    {
        if (empty($this->investorAbbr)) {
            return '';
        }

        $this->investorAbbr = strtr($this->investorAbbr, $this->normBasic);
        if(preg_match('/'.$this->skipBasic.'/u', $this->investorAbbr )){
            $this->investorAbbr = '';
            return $this->investorAbbr;
        } elseif(preg_match('/\W*((等?\s?(\d+|[一二三四五六七八九十〇壹兩]+)\s?[名人戶])|\s?等人)$/u', $this->investorAbbr)){
            $this->investorAbbr = '';
            return $this->investorAbbr;
        }

        $this->investorAbbr = preg_replace_callback('/\s?(法定)?(總經理|經理|董事長|董事|股東|負責人|責責人|福澤人|法代人|代表人|代里人|代人|法定|信託人|代理人|法代|代\:)(\s?\:?\s?)(.*|$)/ui', function ($m) {
            return '';
        },$this->investorAbbr);
        
        $this->investorAbbr = $this->builderFilter($this->investorAbbr);

        $this->investorAbbr = preg_replace_callback('/^(?:(?P<pre>(.+?)))(?:(?P<post>(\s['.$this->lastName.']{1,2}.{2})))$/ui', function ($m) {
            return $m['pre'];
        },$this->investorAbbr);
        $this->investorAbbr = preg_replace_callback('/^(?:(?P<pre>(.+?)))(?:(?P<post>(\s?\(?(股票代號)?\:?[0-9]+\)?\s?)))$/ui', function ($m) {
            return $m['pre'];
        },$this->investorAbbr);
        $this->investorAbbr = preg_replace_callback('/^(?:(?P<pre>(\(?([a-zA-Z]+[0-9]*[戶\,\:\~\.]?)+\)?)?))(?:(?P<investor>(.+?)))(?:(?P<post>([\,\(【]?)))$/ui', function ($m) {
            return $m['investor'];
        },$this->investorAbbr);

        $this->investorAbbr = str_replace([' ',',,',',.,'], ',', $this->investorAbbr);
        $this->investorAbbr = preg_replace_callback('/^(?:(?P<pre>(\,?)))(?:(?P<investor>(.+?)))(?:(?P<post>(\,?)))$/ui', function ($m) {
            return $m['investor'];
        },$this->investorAbbr);
        if($this->investorAbbr==','){
            $this->investorAbbr = '';
        }

        if (mb_strlen($this->investorAbbr) >= 4) {
            if (mb_strlen($this->investorAbbr) >= 6) {
                $this->investorAbbr = str_replace(['台灣','中國','中華民國'], '', $this->investorAbbr);
            } else {
                $this->investorAbbr = str_replace(['台灣','中國'], '', $this->investorAbbr);
            }
        }

        return $this->investorAbbr;
        //台灣土地開發信託投資 股份有限公司法定//高雄市政府國民住宅處 法定//永冠營建設//台灣省政府住宅及都市發展局//台灣省政府住宅及都市發展局//吳順明-德旺建設企業有限公司//宏禹建設有限公司等12戶//(C1)光順建設有限公司//德旺集團-德冠建設//德旺集團,德冠建設//
        //大研建設/實踐家建設//毅楓建設,奕捷建設//再勵建設、營豐建設//簡局能，等8名//李素葉（地主自建）//寶路、寶富、寶盛開發//合環機構   沛豐開發//永平建設.基宏實業//台鳳股份有限公司,和翰建設股份有限公司//蔡一民(地主自建)//經查證無法尋得該建設公司//
        //勝輝機構-勝輝地產//地主廖平吉//彭榮宗等70人//進OO設//國礎建設機構．僑礎建設//員寶建設；員邦建設//長耀機構/誠邦建築.村泉建設//
    }


    public function extractConstructer()
    {
        if (empty($this->constructerAbbr)) {
            return '';
        }
        $this->constructerAbbr = strtr($this->constructerAbbr, $this->normBasic);
        $this->constructerAbbr = preg_replace_callback('/^(?:(?P<pre>(.*?)))(?:(?P<inb>(\(.+?\))))(?:(?P<post>(.*?)))/ui', function ($m) {
            return str_replace(['(', ')', ''], '', $m['inb']);
        },$this->constructerAbbr);

        $this->constructerAbbr = preg_replace_callback('/\s?(負責人).+?$/ui', function ($m) {
            return '';
        },$this->constructerAbbr);

        $this->constructerAbbr = $this->builderFilter($this->constructerAbbr);

        if (mb_strlen($this->constructerAbbr) >= 4) {
            if (mb_strlen($this->constructerAbbr) >= 6) {
                $this->constructerAbbr = str_replace(['台灣','中國','日系','中華民國'], '', $this->constructerAbbr);
            } else {
                $this->constructerAbbr = str_replace(['台灣','中國','日系'], '', $this->constructerAbbr);
            }
        }


        return $this->constructerAbbr;
    }

    ////////////////////////////////////////////////////////////////////////////////
    //////////////// 將未經處理的建案名稱進行正常化處理，修改一些不正常的內容 ///////////////////
    ////////////////////////////////////////////////////////////////////////////////
    private function regulating()
    {
        //去除前後空白
        $this->normalized = $this->trim($this->normalized);//去除前後空白
        // 將所有字元轉為半形
        $this->normalized = $this->toHalfCase($this->normalized);
        //替換錯誤的名稱以及單位
        $this->normalized = $this->replace([
                //NOTE：以下為可編輯部份，當有需要時可自行更改//ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ
                ' ‧ ' => '‧',
                '‧ ' => '‧',
                ' ‧' => '‧',
                '《' => '(',
                '》' => ')',
                '[' => '(',
                ']' => ')',
                '「' => '(',
                '」' => ')',
                '【' => '(',
                '】' => ')',
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
                '别' => '別',
                '。' => ''
        ]);
        
        // 修復補齊缺少的右括號
        if (str_contains($this->normalized, '(')) {
            if(!str_contains($this->normalized, ')')){
                $this->normalized = $this->normalized.')';
            }
        }
        // 替換錯誤的名稱以及單位，以及刪除無需分析或分析無效的字串
        $this->normalized = $this->replace([
                //NOTE：以下為可編輯部份，當有需要時可自行更改
                '-(' => '(',
                'NO.' => 'NO',
                'ON.' => 'NO',
                'NO:' => 'NO',
                ':' => '-',
                '(雙號)' => '(雙)',
                '(單號)' => '(單)',
                '/' => '、',
                '(未公開)' => '',
                '(舊門牌)' => '',
                '(住家)' => '',
                '(獨棟)' => '',
                '樓中樓' => '(樓中樓)',
                //'(樓中樓)' => '',
                '-市區內' => '',
                '(甲標)' => '',
                '(乙標)' => '',
                '(丙標)' => '',
                '甲標' => '',
                '乙標' => '',
                '丙標' => '',
                '(新)' => '',
                '(地上權)' => '',
                '地上權' => '',
                '(海砂屋)' => '',
                '(辦公)' => '',
                '(純辦)' => '',
                '(八張犁)' => '(八張犁段)',
                '(花園)' => '花園',
                '大樓區' => '-大樓區',
                '華廈區' => '-華廈區',
                '透天區' => '-透天區',
                '公寓區' => '-公寓區',
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
                '管理委員會' => '',
                '管委會' => '',
                '管理中心' => '',
                '管理' => '',
                '警衛室' => '',
                '臺灣省' => ''
        ]);

        $this->normalized = $this->replace([
                '--' => '-',
                ',,' => ',',
                '((' => '(',
                '))' => ')'
        ]);

        //【R1】將「-NO」字串中的連結符號「-」移除，並且同時歸類
        $patternR1 = '/';
        $patternR1 .= '^(?:(?P<pre>(.*?)))';
        $patternR1 .= '(?:(?P<phase>(';
        //$patternR1 .= '\s?\-\s?';
        $patternR1 .= '\s?\-?\s?';
        $patternR1 .= '((';
        $patternR1 .= '(';
        $patternR1 .= '(NO)[0-9]+|';
        $patternR1 .= '第?[0-9一二三四五六七八九十ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ]+期';
        $patternR1 .= ')、?\,?)+';
        $patternR1 .= ')';
        $patternR1 .= ')))';
        $patternR1 .= '(?:(?P<post>(.*?)))$';
        $patternR1 .= '/iu';
        $this->normalized = $this->replace($patternR1, function ($m) {
            $this->tmatches .= '<span style="color:#3366cc;">[R1]</span>' . $m['phase'];
            $numPart = $this->numAnalysis($m['phase']);
            $postContent = $this->assortPost($m['post']);
            //若數字分析結果不為空時，將視其為期數值，同時寫入
            if(!empty($numPart)){
                $this->parsePhase = $numPart;
                return $m['pre'] . $postContent;
            }
            return $m['pre'] . $m['phase'] . $postContent;
        });

        //【R2】
        $this->normalized = $this->replace('/(NO|第)?[0-9]+[號區棟樓期]?\+((NO|第)?[0-9]+[號區棟樓期]?\+?)+/iu', function ($m) {
            $this->tmatches .= '<span style="color:#3366cc;">[R2]</span>' . $m[0];
            $lastUnit = mb_substr($m[0], -1);
            if(!in_array($lastUnit, ['號', '區', '棟', '樓', '期'])){
                $lastUnit = '';
            }
            $firstUnit = mb_substr($m[0], 0, 1);
            $newUnit = str_ireplace([' ', 'NO', '第', '號', '區', '棟', '樓', '期','+'], ['', '', '', '', '', '', '', '', ','], $m[0]);
            if (in_array($lastUnit, ['區', '棟', '樓'])) {
                $this->parseArea .= $newUnit.$lastUnit;
            } elseif ($lastUnit == '號') {
                $this->parseLocated .= $newUnit.$lastUnit;
            } elseif (($firstUnit == 'N' && $lastUnit == '') || ($firstUnit == '第' && $lastUnit == '期')) {
                $this->parsePhase = $newUnit.$lastUnit;
            }else {
                return $m[0];
            }
            return '';
        });


        //++++++++++++++++++++ 【R4】建案名稱中括號內容分析 +++++++++++++++++++++
        //【R4】處理括號內字串，分析、指派並擷取後將其刪除，並分類儲存到相關變數中，並同時分析括號之後的字串
        // 藉移除括號內容，將建案名稱簡單化
        $patternR4 = '/';
        $patternR4 .= '^(?:(?P<pre>(.*?)))';
        $patternR4 .= '(?:(?P<brackets>(\-?\(.+?\))))';
        $patternR4 .= '(?:(?P<post>(.*?)))$';
        $patternR4 .= '/ui';
        $this->normalized = $this->replace($patternR4, function ($m) {
            $this->tmatches .= '<span style="color:#3366cc;">[R4]</span>'.$m['brackets'];
            $this->tempInBrackets = $m['brackets'];//暫時存於變數中待後續處理
            return $m['pre'] . $m['post'];//刪除括號的內容
        });

        //進行基礎分析，過濾無需進行匹配的建案名稱
        $analysisFilter = $this->analysisFilter();
        //當回傳為false時為無需進行匹配的建案名稱，直接回傳原名稱
        if ($analysisFilter == false) {
            $this->finalizing();//finalizing
            return $this->normalized;//此行非 replace 語句，代表真實直接執行返回，並終止之後的動作
        }
        //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓//
        ///////////////////////////////////////////////////////////////////////
        /////////////////////// 以下繼續進行正常化處理 ////////////////////////////
        ///////////////////////////////////////////////////////////////////////
        //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓//

        //修正以英文字母拼湊成的羅馬數字回歸正體羅馬數字（超過半數的羅馬數字被以大寫英文字母誤寫），於此定義為除錯後的原始名稱
        $this->normalized = $this->fixToRomanNumber();

        //////////////////////////// 分析匹配作業 ///////////////////////////////
        //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓//
        /////////////////////// 連結符號以及前後空格處理 //////////////////////////
        ///////////////////////////////////////////////////////////////////////

        
        //【C1】純數字間的連結號，以及連結號間的空白，依單組或多組重複後緊接著[號區棟樓期]其一的單位，分別歸類
        // 不在此匹配範圍內的連結號，則以[@dash@]與[@space@]代替
        $patternC1 = '/';        
        $patternC1 .= '(?:(?P<pre>.*?))';
        $patternC1 .= '(?:(?P<string>(';
        $patternC1 .= '(';
        $patternC1 .= '(\s?(NO|[東南西北])?\d+\s?[號區棟樓期]?[\‧\.\-\~\,]\s?(NO)?\d+\s?[號區棟樓期]?)';
        $patternC1 .= '(之[0-9一二三四五六七八九十]+[號區棟樓期]?)?';
        $patternC1 .= '\s?\,?\s?';
        $patternC1 .= ')+';
        $patternC1 .= ')))';
        $patternC1 .= '(?:(?P<post>.*?))';
        $patternC1 .= '/ui';
        $this->normalized = $this->replace($patternC1, function ($m) {
            if(preg_match('/^([0-9]+[\.\‧][0-9]+)$/u', $m['string'])) {
                $this->tmatches .= '<span style="color:#2952a3;">[C1-1]</span>' . $m['string'];
                return $m[0];
            } else if(preg_match('/^(NO[0-9]+[\-][0-9]+)$/u', $m['string'])){
                $this->tmatches .= '<span style="color:#2952a3;">[C1-2]</span>' . $m['string'];
                $splitNO = explode('-', str_replace('NO', '', $m['string']));
                $this->parsePhase = $splitNO[0];
                $this->parseArea .= $splitNO[1];
            } else {
                $lastUnit = mb_substr($m['string'], -1);
                if(!in_array($lastUnit, ['號', '區', '棟', '樓', '期'])){
                    $lastUnit = '';
                }
                $newUnit = '';
                $pureString = str_replace(['號', '區', '棟', '樓', '期'], '', $m['string']);
                
                if (preg_match('/^((([東南西北]?[0-9]+[號區棟樓期]?)\,?)+)$/u', $m['string'])) {
                    $this->tmatches .= '<span style="color:#2952a3;">[C1-3]</span>' . $m['string'];
                    $newUnit = $pureString;
                }
                elseif (preg_match('/^((([東南西北]?\d+\~\d+[號區棟樓期]?)、?\,?)+)$/u', $m['string'])) {
                    $this->tmatches .= '<span style="color:#2952a3;">[C1-4]</span>' . $m['string'];
                    $newUnit = $pureString;
                }
                elseif (preg_match('/^((([0-9]+[\-\~][0-9]+[號區棟樓期]?)\,?)+)$/u', $m['string'])) {
                    $this->tmatches .= '<span style="color:#2952a3;">[C1-5]</span>' . $m['string'];
                    $unitArray = explode(',', $pureString);
                    foreach ($unitArray as $key => $val) {
                        $sp = preg_split("/[\-\~]+/", $val);
                        if($lastUnit =='' || ($lastUnit == '號' && preg_match('/^([0-9]+[\-][0-9]+)$/', $val))){
                            $newUnit .= $val . ',';
                        } else {
                            for ($i = $sp[0]; $i <= $sp[1]; $i++) {
                                $newUnit .= $i . ',';
                            }
                        }
                    }
                    $newUnit = substr($newUnit, 0, -1);
                }

                if (in_array($lastUnit, ['區', '棟', '樓'])) {
                    $this->parseArea .= $newUnit.$lastUnit;
                } elseif ($lastUnit == '號') {
                    $this->parseLocated .= $newUnit.$lastUnit;
                } elseif ($lastUnit == '期') {
                    $this->parsePhase = $newUnit;
                }else {
                    return $m[0];
                }
            }
            $this->tmatches .= '<span style="color:#2952a3;">[C1]</span>' . $m['string'];
            return $m['pre'].$m['post'];
        });


        //【C2】字母數字間以及字母與字母間的連結號，連結號左右兩側最末字元可以(是/不是)「區」、「棟」或「樓」
        // 排除連接線左側頭兩字元為「NO」，以及右側最末字元為「區」、「棟」或「樓」之外的連結號，則以[@dash@]代替
        $patternC2 = '/';        
        $patternC2 .= '^(?:(?P<pre>[^a-zA-Z0-9\-]*))';
        $patternC2 .= '(?:(?P<string>';
        $patternC2 .= '(\s?\-?\s?[a-zA-Z0-9]+[區棟樓]?)';
        $patternC2 .= '(\s?\-\s?[a-zA-Z0-9\+]+[區棟樓館期]?([A-Z0-9]*)?[區棟樓館]?)';
        $patternC2 .= '))';
        $patternC2 .= '(?:(?P<post>.*?))$';
        $patternC2 .= '/u';//NOTE:SKIP i
        $this->normalized = $this->replace($patternC2, function ($m) {
            //字串去除空白間隔
            $pureString = str_replace(' ', '', $m['string']);
            //定義原始字串第一個字元
            $dashPart = '';
            //當原始字串第一個字元為「-」時
            if(mb_substr($pureString,0,1) == '-'){
                $pureString = mb_substr($pureString,1);//將原始字串第一個字元刪除
                $dashPart = '[@dash@]';//設定原始字串第一個字元為替代組合
            }
            //定義前區字段最後一個單位字元
            $firstUnit = '';
            //定義後區字段最後一個單位字元
            $lastUnit = '';
            //將原始字串以「-」分隔符號切分為陣列
            $splitNO = explode('-', $pureString);
            $preString = $splitNO[0];
            $postString = $splitNO[1];
            //取得前區字段最後一個單位字元
            $getFirstUnit = mb_substr($preString, -1);
            //同時設定後區字段取得的最後一個單位字元
            $getLastUnit = mb_substr($postString, -1);
            //當取得的前區字段單位字元為「區」「棟」「樓」其一時，設定前區字段單位為取得的值
            if(in_array($getFirstUnit, ['區','棟','樓'])) {
                $firstUnit = $getFirstUnit;
            }
            //當取得的後區字段單位字元為「區」「棟」「樓」其一時，設定後區字段單位為取得的值
            if (in_array($getLastUnit, ['區','棟','樓'])) {
                $lastUnit = $getLastUnit;
            }

            if(preg_match('/^((NO\d+)+|\d+)\-([a-zA-Z]+[1-9\+]*[區棟樓館]?([A-Z0-9]*)?[區棟樓館]?)$/u', $pureString)){
                $this->tmatches .= '<span style="color:#1f3d7a;">[C2-1]</span>' . $m['string'];
                //若前區字段開頭是「NO」，將視其為期數值
                if(mb_substr($preString,0,2) == 'NO'){
                    //將移除「NO」的前區字段寫入期數變數中
                    $this->parsePhase = str_replace('NO', '', $preString);
                    $postContent = $this->assortPost($postString);
                    return $m['pre'] . $postContent;

                    //前區字段既為期數值，則後區字段直接寫入區域變數中
                    if ($this->checkIfIsArea($postString) == true) {

                        $this->parseArea .= $postString;//分配至主區域
                    } else {
                        $this->parseAka2 .= $postString;
                    }
                    
                }
                //若前區字段開頭不是「NO」
                else{
                    //定義一個新的字串，暫時為後區字段
                    $newPostString = $postString;
                    //當後區字段的單位為空值時，將後區字段的數字部分取出另行定義，同時將後區字段移除數字部分
                    if(empty($lastUnit)){
                        $splitNum = preg_match("/\d+$/", $postString, $matches);
                        if($splitNum){
                            $postNumPart = $matches[0];
                            $newPostString = str_replace($postNumPart, '', $postString);
                        }
                    }
                    //當前區字段之前為空時，則整個原始字串視為建案名稱的一部份，分析出期數值後原樣返回
                    if(empty($m['pre'])){
                        $this->parsePhase =  !empty($postNumPart) ? $postNumPart : $this->parsePhase;
                        return $m['pre'] . $dashPart . $preString . '[@dash@]' . $newPostString . $m['post'];
                    }
                    //當前區字段之前不為空時，當後區字段為區域時，則分配至主區域
                    if ($this->checkIfIsArea($newPostString) == true) {
                        $this->parseArea .= $newPostString;//分配至主區域
                    }
                    //當後區字段非為區域時，則分配至第二別名
                    else{
                        $this->parseAka2 = $postString;//分配至第二別名
                    }
                    //將前區字段進行數字分析
                    $preNumPart = $this->numAnalysis($preString);
                    //若數字分析結果不為空時，將視其為期數值，同時寫入
                    if (!empty($preNumPart)) {
                        $this->parsePhase = $preNumPart;
                    }
                    //若前區字段分析結果為空時，將前區字段視為建案名稱的一部份，同時返回
                    else {
                        return $m['pre'] . $dashPart . $preString . $m['post'];
                    }
                }

            }
            //當字串是由單一個字母（無論是否有單位），之後接著分隔號，之後接著數字或單一個字母（無論是否有單位），以上條件皆視為主區域以及其下的子區域
            else if(preg_match('/^([a-zA-Z][區棟樓]?\-([1-9][0-9]?|[a-zA-Z])[區棟樓]?)$/u', $pureString)){
                $this->tmatches .= '<span style="color:#1f3d7a;">[C2-2]</span>' . $m['string'];
                $this->parseArea .= $preString;//分配至主區域
                $this->parseAreaExt .= $postString;//分配至子區域
            }
            //當字串是由單一個字母，之後接著分隔號，之後接著至少兩個字母結尾，且經過篩檢後並非連續字母，以上條件皆視為建案名稱的一部份，不進行分配直接傳回原始字串
            else if(preg_match('/^([a-zA-Z]\-[a-zA-Z]{2,}([1-9][0-9]*)?)$/u', $pureString)){
                $this->tmatches .= '<span style="color:#1f3d7a;">[C2-3]</span>' . $m['string'];
                //
                $newPostString = $postString;
                $splitNum = preg_match("/\d+$/", $postString, $matches);
                if($splitNum){
                    $numPart = $matches[0];
                    $newPostString = str_replace($numPart, '', $postString);
                }
                if($this->checkIfIsArea($newPostString) == true){
                    $this->parseArea .= $preString;//分配至主區域
                    $this->parseAreaExt .= $postString;//分配至子區域
                } else {
                    if(!empty($numPart)){
                        $this->parsePhase =  $numPart;
                    }
                    return $m['pre'] . $dashPart . $preString . '[@dash@]' . $newPostString . $m['post'];
                }
            }
            //當字串是由至少兩個字母，且經過篩檢後並非連續字母，之後接著分隔號，之後接著單一個H至Z字母結尾，以上條件皆視為建案名稱的一部份，不進行分配直接傳回原始字串
            else if(preg_match('/^([a-zA-Z]{2,}\-[h-zH-Z]([1-9][0-9]*)?)$/u', $pureString)){
                $this->tmatches .= '<span style="color:#1f3d7a;">[C2-4]</span>' . $m['string'];
                $newPostString = $postString;
                $splitNum = preg_match("/\d+$/", $postString, $matches);
                if($splitNum){
                    $numPart = $matches[0];
                    $newPostString = str_replace($numPart, '', $postString);
                }
                if($this->checkIfIsArea($preString) == true){
                    $this->parseArea .= $preString;//分配至主區域
                    $this->parseAreaExt .=  $newPostString;//分配至子區域
                } else {
                    if(!empty($numPart)){
                        $this->parsePhase =  $numPart;
                    }
                    return $m['pre'] . $dashPart . $preString . '[@dash@]' . $newPostString . $m['post'];
                }
            }
            //當字串是由至少兩個字母，且經過篩檢後並非連續字母，之後接著分隔號，之後接著至少兩個字母，且經過篩檢後並非連續字母，之後加上0或多個數字結尾，以上條件皆視為建案名稱的一部份，不進行分配直接傳回原始字串
            else if(preg_match('/^([a-zA-Z]{2,}\-[a-zA-Z]{2,}([1-9][0-9]*)?)$/u', $pureString)){
                $this->tmatches .= '<span style="color:#1f3d7a;">[C2-5]</span>' . $m['string'];
                $newPostString = $postString;
                $splitNum = preg_match("/\d+$/", $postString, $matches);
                if($splitNum){
                    $numPart = $matches[0];
                    if(!empty($numPart)){
                        $this->parsePhase =  $numPart;
                        $newPostString = str_replace($numPart, '', $postString);
                    }
                }
                //前區字段檢查是否為區域型態
                $preCheck = $this->checkIfIsArea($preString);
                //後區字段檢查是否為區域型態
                $postCheck = $this->checkIfIsArea($postString);
                //當前區字段與後區字段都非為區域型態時，回傳替換分隔符號後的原始字串
                if($preCheck !== true && $postCheck !== true){
                    return $m['pre'] . $dashPart . $preString . '[@dash@]' . $newPostString . $m['post'];
                }
                //當前區字段與後區字段其中之一非為區域型態，或是前後區字段皆為區域型態時，進行區域分配
                else {
                    //前區字段為非區域型態，後區字段為區域型態
                    if($preCheck !== true && $postCheck == true){
                        $this->parseArea .= $newPostString;//後區字段分配至主區域
                        return $m['pre'] . $dashPart . $preString . $m['post'];//前區字段回傳原始字串
                    }
                    //前區字段為區域型態，後區字段為非區域型態
                    else if($preCheck == true && $postCheck !== true){
                        $this->parseArea .= $preString;//前區字段分配至主區域
                        return $m['pre'] . $dashPart . $newPostString . $m['post'];//後區字段回傳原始字串
                    }
                    //前後區字段皆為區域型態
                    else {
                        $this->parseArea .= $preString;//前區字段分配至主區域
                        $this->parseAreaExt .=  $newPostString;//後區字段分配至子區域
                    }
                }
            }
            //當字串是由至少兩個字母，且經過篩檢後並非連續字母，之後接著分隔號，之後接著0或多個數字結尾，以上條件皆視為建案名稱的一部份，不進行分配直接傳回原始字串
            else if(preg_match('/^([a-zA-Z]{2,}\-([1-9][0-9]*[區棟樓館期]?)?)$/u', $pureString)){
                $this->tmatches .= '<span style="color:#1f3d7a;">[C2-6]</span>' . $m['string'];
                //將後區字段進行數字分析
                $numPart = $this->numAnalysis($postString);
                //若數字分析結果不為空時，將視其為期數值，同時寫入
                if(!empty($numPart)){
                    $this->parsePhase =  $numPart;
                    return $m['pre'] . $dashPart . $preString . $m['post'];
                }
                else{
                    return $m['pre'] . $dashPart . $preString . '[@dash@]' . $postString . $m['post'];
                }
            }
            //當字串是由至少兩個字母，之後接著分隔號，之後接著至少一個字母加「區」「棟」「樓」其一個文字結尾
            else if(preg_match('/^([a-zA-Z]{2,}\-([a-zA-Z]+)[區棟樓]?)$/u', $pureString)){
                $this->tmatches .= '<span style="color:#1f3d7a;">[C2-7]</span>' . $m['string'];
                //前區字段檢查是否為區域型態
                $preCheck = $this->checkIfIsArea($preString);
                if($preCheck !== true){
                    $this->parseArea .= $postString;
                    return $m['pre'] . $dashPart . $preString . $m['post'];
                }
                else {
                    $this->parseArea .= $preString;//前區字段分配至主區域
                    $this->parseAreaExt .=  $postString;//後區字段分配至子區域
                }
            }
            else {
                $this->tmatches .= '<span style="color:#1f3d7a;">[C2]</span>' . $m['string'];
            }
            return $m['pre'].$m['post'];
        });

        //【C3】字母數字間以及字母與字母間的連結號，連結號左右兩側最末字元可以(是/不是)「區」、「棟」或「樓」

        $patternC3 = '/';
        $patternC3 .= '^(?:(?P<pre>.*?))';
        $patternC3 .= '(?:(?P<string>';
        $patternC3 .= '[^a-zA-Z0-9]+';
        $patternC3 .= '(\s?[\-]\s?';
        $patternC3 .= '[^a-zA-Z0-9]+[區棟樓]?)?';
        $patternC3 .= '\s?[\-]\s?';
        $patternC3 .= '[a-zA-Z]+[區棟樓]?';
        $patternC3 .= '))';
        $patternC3 .= '(?:(?P<post>';
        $patternC3 .= '.*?';
        $patternC3 .= '))$';
        $patternC3 .= '/ui';
        $this->normalized = $this->replace($patternC3, function ($m) {
            $this->tmatches .= '<span style="color:#3366cc;">[C3]</span>' . $m['string'];
            $stringSplit = explode('-', $m['string']);
            $postContent = $this->assortPost($stringSplit[1] . $m['post']);
            if(!empty($stringSplit[2])){
                $postContent2 = $this->assortPost($stringSplit[2] . $m['post']);
            }
            return $m['pre'] . $stringSplit[0] . $postContent;
        });

        //【C4】非字母數字間的連結號，以及連結號間的空白，['一律刪除']???
        $patternC4 = '/';
        $patternC4 .= '^(?:(?P<pre>.+?[^a-zA-Z0-9]))';
        $patternC4 .= '(?!樓[\-\~\s])';
        $patternC4 .= '(?:(?P<post>';
        $patternC4 .= '([A-Z]?[0-9]*[區棟樓]?)';
        $patternC4 .= '\s?[\-\~]\s?[^a-zA-Z0-9]+.*?';
        $patternC4 .= '))$';
        $patternC4 .= '/u';//NOTE:SKIP i
        $this->normalized = $this->replace($patternC4, function ($m) {
            $this->tmatches .= '<span style="color:#3366cc;">[C4]</span>' . $m['post'];
            //
            $keepBefore = '';
            $sendPost = $m['post'];
            if(str_contains($m['post'], '-') || str_contains($m['post'], '~')){
                $postSplit = preg_split("/[\-\~]+/", $m['post']);
                $keepBefore = $postSplit[0];
                $sendPost = $postSplit[1];
            }

            //
            $postContent = $this->assortPost($sendPost);//準備分析post內容
            return $m['pre'] . $keepBefore . $postContent;
        });


        //------ 其餘的連字符以「-」替換，以利後續分組 ------
        $this->normalized = $this->replace([
            '~' => '-'
        ]);


        ///////////////////////////////////////////////////////////////////////
        ///////////////////// 連結符號以及前後空格處理結束 /////////////////////////
        ///////////////////////////////////////////////////////////////////////

        $this->normalized = $this->normalizing();
        return $this->normalized;
    }
    //-------------------- 將已經初步正常化的住址，進行數字類的格式統一 ----------------------
    private function analysisFilter()
    {
        /*
        //【F1】字母開頭，數字結尾，其中沒有任何的文字符號的情況，此情況下停止分析
        if (preg_match('/^[0-9]*[a-zA-Z一二三四五六七八九十]*(([0-9]+)|(館|行館))$/', $this->normalized)) {
        //if (preg_match('/^[0-9]*([^0-9a-zA-Z]+[a-zA-Z])*(([0-9]+)|(館|行館))$/', $this->normalized)) {
            $this->tmatches .= '<span style="color:#ff0000;">[F1]</span>';
            return false;
        }
        */
        //【F2】字串結尾為數字，之前接著「+」符號，再之前接著數字或是字母，認定此種組合為建案名稱的一部份，且之後沒有期數或附加訊息，此情況下停止分析
        //if (preg_match('/^[^a-zA-Z0-9一二三四五六七八九十\-\+\.\~]+[0-9一二三四五六七八九十]\+[0-9一二三四五六七八九十]$/u', $this->normalized)) {
        
        if (preg_match('/^[^a-zA-Z0-9一二三四五六七八九十\-\+\.\~]+[A-Z0-9一二三四五六七八九十]?\+[0-9一二三四五六七八九十]{1,2}[號區棟樓期]?$/u', $this->normalized, $matches)) {
            $this->tmatches .= '<span style="color:#ff0000;">[F2]</span>' . $matches[0];
            return false;
        }
        /*
        if (preg_match('/^(.*?[0-9一二三四五六七八九十]{1,2}[樓].*?)$/u', $this->normalized, $matches)) {
            $this->tmatches .= '<span style="color:#ff0000;">[F3]</span>' . $matches[0];
            return false;
        }
        */
        //++++++++++++++++++++++++++++++++++++++++++++ 【F4】 ++++++++++++++++++++++++++++++++++++++++++++
        //【F4】排除建案名稱中包含中華民國的縣市名，以及排除建案名稱中包含住址片段或非以建案名稱命名的檔案名稱，無需進行接下來的動作，並終止後續規則化分析處理，暫時將其顯示為灰色
        // 將建案名稱中，包含有「路街道段巷」任一字元後接著「數字+巷號弄」（可重複）的字串,視為不正規化建案名稱，歸類後設為不後續處理
        // 將建案名稱中，任何位置只要包含「數字」+「樓」，則視為不正規化建案名稱，歸類後設為不後續處理
        // 建案名稱中本身帶有「街號」如唐寧街、福人街等等，則視為例外，可繼續進行規則化分析
        $ptnF4 = '/';
        $ptnF4 .= '(?!(花田一路|一番街|一品商店街|唐寧街|大學道))';
        $ptnF4 .= '(';
        $ptnF4 .= '(^('.$this->SPexpCity.'))|';
        //$ptnF4 .= '^([^唐寧|福人|好運|大學][路街道段巷]';
        $ptnF4 .= '^(';
        $ptnF4 .= '(.*?區)?.*?[路街道段巷]';
        $ptnF4 .= '(';
        $ptnF4 .= '([0-9一二三四五六七八九十單雙]+[段巷弄號]?([\-\,\~]?\d+(之[0-9一二三四五六七八九十]+)?[巷弄號])?)+';
        $ptnF4 .= ')|';
        $ptnF4 .= '(^(.*?[0-9一二三四五六七八九十]{1,2}[樓].*?))|';
        $ptnF4 .= '(^(.*?區)?(([^\-\,\~]{1,4}[^大小][路街])|(.*?[0-9一二三四五六七八九十]巷))([^\-\,\~]+段)?)|';
        $ptnF4 .= '(^([0-9]+[巷弄號]).*?)';
        $ptnF4 .= ')';
        $ptnF4 .= ')';
        $ptnF4 .= '[a-eA-E]{0,3}('.$this->SPprefixType.'|弄|口)?('.$this->SPfunctionType.')?(('.$this->SPbcTypeBasic.'|大[1-9一二三四五]房)+)?[1-9a-eA-E]?$';
        $ptnF4 .= '/iu';
        if(preg_match($ptnF4, $this->normalized, $matches)){
            $this->tmatches .= '<span style="color:#33ccff;">[F4]</span>' . $matches[0];
            $this->BCtypeFilter();
            $this->finalLocSplitter();
            $this->finalInvestorSplitter();
            //$this->normalized = '<span style="color:lightgray;">'.$this->normalized.'</span>';//NOTE：暫時設定為灰色，應移除
            $this->bcMode = 5;
            return false;
        }
        $this->tmatches .= '<span style="color:red;">[Fpass]</span>';
        return true;

    }

    ////////////////////////////////////////// 【【 規則化處理 】】 ///////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////// normalizing 將已經正常化的住址，進行規則化處理，調整為正規化的住址格式 /////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    private function normalizing()
    {
        //+++++++++++++++++++++++++++++++++++++++++++++ 【E1】 ++++++++++++++++++++++++++++++++++++++++++++++
        //【E1】將尾端明顯存在的投資商簡稱析出重新歸類
        $this->normalized = $this->replace('/^(?:(?P<bctitle>.+))(?:(?P<builder>([\-\(].+(建設)\)?)))/ui', function ($m) {
            if (empty($m['builder'])) {
                return $m[0];
            }
            $getBuilderSplit = explode('建設', str_replace(['(',')','-'], '', $m['builder']));
            $getBuilderAbbr = $getBuilderSplit[0];
            if (!empty($getBuilderAbbr) && $getBuilderAbbr != $this->investorAbbr) {
                $this->investorAbbr = $getBuilderAbbr;
            }
            $this->tmatches .= '<span style="color:#009900;">[E1]</span>' . $m['builder'];
            if(empty($m['bctitle'])){
                return $m['builder'];
            }
            return $m['bctitle'];
        });

        //+++++++++++++++++++++++++++++++++++++++++++++ 【E2】 ++++++++++++++++++++++++++++++++++++++++++++++
        //【E2】將開頭明顯存在的投資商簡稱析出重新歸類
        $patternE2 = '/';
        $patternE2 .= '^(?:(?P<builder>(.+(建設|營造|集團)(機構)?)))';
        $patternE2 .= '(?:(?P<phase>(((NO)?[0-9一二三四五六七八九十]+)?期?)))';
        $patternE2 .= '(?:(?P<bctitle>~?\-?.*))';
        $patternE2 .= '/ui';
        $this->normalized = $this->replace($patternE2, function ($m) {

            //當名稱字串中沒有建商相關字樣時，直接回傳原始字串不做任何動作。
            if (empty($m['builder'])) {
                return $m[0];
            }
            ////////// 以下當名稱字串包含建商相關字樣時，進行分析。 //////////
            $newString = '';
            //針對分析出的建商名稱進行「行號 (商號)」的剝離，以取得簡稱
            $getBuilderAbbr = $this->builderFilter($m['builder']);
            //當建商名稱析出簡稱回傳帶值時
            if (!empty($getBuilderAbbr)){
                //將回傳的簡稱字串加入到建商簡稱變數中，若變數中已有值時則以逗號分隔
                if (!empty($this->investorAbbr) && !str_contains($this->investorAbbr, $getBuilderAbbr)) {
                    $this->investorAbbr = $this->investorAbbr.','.$getBuilderAbbr;
                } else {
                    $this->investorAbbr = $getBuilderAbbr;
                }
            } else {
                $newString .= $m['builder'];
            }
            //當字串中包含期數痕跡時，將相關期數分析出來（分析過程中可能會將期數主動脫離字串並另行歸類）
            if (!empty($m['phase'])) {
                $phaseCheck = str_replace('期', '', $m['phase']);
                $numPart = $this->numAnalysis($phaseCheck);
                if(!empty($numPart)){//有分析到期數時主動脫離字串並另行歸類
                    $this->parsePhase =  $numPart;
                }
            } else {
                $newString .= $m['phase'];
            }
            //將分析結果寫入分析註記中
            $this->tmatches .= '<span style="color:#ff9900;">[E2]</span>'.$m['builder'];
            //當建案名稱經過正規表達式篩檢後，建案名稱為空時，代表建案名稱可能由建商的名稱代替（部份建案尚未申辦命名），此狀況下直接回傳原始字串
            if(empty($m['bctitle'])){
                return $newString;
            }
            $newString .= $m['bctitle'];
            return $newString;//NOTE:此位置之下停止執行


            //當有正常的建案名稱析出時，則將此名稱字串導入另一個字尾分析器中，進行額外分析
            $postContent = $this->assortPost($m['bctitle']);
            //$this->tDebug = $postContent;
            if(empty($postContent)){
                return $m['builder'].$m['phase'];
            } else if(in_array($postContent, ['大樓','大廈','華廈','別墅'])){
                return $m[0];
            }
           return $postContent;

        });

        //+++++++++++++++++++++++++++++++++++++++++++++ 【E3】 ++++++++++++++++++++++++++++++++++++++++++++++
        //【E3】處理(非括號內)連結號後至結尾字串，分析、指派並排除指定字串後回傳，分析後字串分類儲存到相關變數中
        $patternE3 = '/';
        $patternE3 .= '^\-(?:(?P<pre>([0-9一二三四五六七八九十][期])?))';
        $patternE3 .= '(?:(?P<string>(.*?)))';
        $patternE3 .= '(?:(?P<post>([0-9一二三四五六七八九十][期])?))';
        $patternE3 .= '/iu';
        $this->normalized = $this->replace($patternE3, function ($m) {
            $this->tmatches .= '<span style="color:#cc7a00;">[E3]</span>' . $m['string'];
            if (!empty($m['pre']) || !empty($m['post'])) {
                $phaseCheck = '';
                if (!empty($m['pre'])) {
                    $phaseCheck = $m['pre'];
                } elseif (!empty($m['post'])) {
                    $phaseCheck = $m['post'];
                }
                $phaseCheck = str_replace('期', '', $phaseCheck);
                $numPart = $this->numAnalysis($phaseCheck);
                if(!empty($numPart)){
                    $this->parsePhase =  $numPart;
                }
            }
            $postContent = $this->assortPost($m['string']);
            return $postContent;//做期數預處理（賦予固定格式以利後續規則化處理）
        });

        $this->normalized = $this->phaseSplit();
        return $this->normalized;
    }
    //////////////////////////////////////// 【【 字串分段意義處理 】】 ////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////// phaseSplit 依據字串中的含義進行分段處理，將不同意義的字段分開後個別處理 ///////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    private function phaseSplit()
    {
        //+++++++++++++++++++++++++++++++++++++++++++++ 【A1】 ++++++++++++++++++++++++++++++++++++++++++++++
        //【A1】先處理建案名稱末端的分區資訊，並儲存到 parseArea 變數中，匹配條件為： 
        // 除了「數字」、「字母」、「-」、「空白」以及「NO」外的任何字串，緊接著單一字母，再（加上/不加上）數字的組合
        // 注意： 這組匹配處理必須位於【P1】之前
        $patternA1 = '/';
        $patternA1 .= '^(?:(?P<pre>[^a-zA-Z0-9\-]+))';
        $patternA1 .= '(?:(?P<area>\-?(?!NO|DC)';
        $patternA1 .= '(';
        $patternA1 .= '([A-Z]{1,2}[0-9一二三四五六七八九十]{0,2})|';//此行生效後所有A-Z大寫字母後接數字時的組合，無論是否包含於建案名稱中皆視為「分區」並重新歸類
        $patternA1 .= '([雄駁][一二三四五六七八九十])';//或是1-2位A-G大寫字母（或一個「雄」字元），後不接著或是接著1-2位數字結尾
        $patternA1 .= ')|';
        $patternA1 .= '(';
        $patternA1 .= '([A-Z]{1,2}[0-9一二三四五六七八九十]{0,2}區)|';
        $patternA1 .= '([雄駁][0-9一二三四五六七八九十]特區)';//或是1-2位大寫字母（或一個「雄」字元），後不接著或是接著1-2位數字，之後必須接著「區」字元
        $patternA1 .= ')|';
        $patternA1 .= '([A-Z])';//或是尾端僅接著一位數大寫字母結尾
        $patternA1 .= '))$';
        $patternA1 .= '/u';
        $this->normalized = $this->replace($patternA1, function ($m) {
            $this->tmatches .= '<span style="color:#B8860B;">[A1]</span>'.$m['area'];
            $pureArea = str_replace('-', '', $m['area']);

            if(!empty($this->parseArea)) {
                $this->parseAreaExt .= $pureArea;
            } else {
                $this->parseArea .= $pureArea;
            }
            return $m['pre'];//做分區預處理（賦予固定格式以利後續規則化處理）
        });
        //+++++++++++++++++++++++++++++++++++++++++++++ 【P1】 ++++++++++++++++++++++++++++++++++++++++++++++
        //【P1】期數分析第一步驟：先過濾出字串中帶有意寓為「期數」，但單位用字非為「期」的字串，匹配條件為，整個字串開頭可以為任
        // 意值，中間待擷取的數字前可為「第」字元，後為「號」、「章」、「樂章」、「重奏」、「部曲」、「篇章」....等等，並以之分
        // 隔前後字串組，後組進行再區域分析其中待截取的數字之後若為「號」字元時，前組字串尾字不得為「路、街、道、巷、弄」任一字元 
        $patternP1 = '/';
        $patternP1 .= '(?:(?P<pre>(.*?[^0-9a-zA-Z\-路街道巷弄第])))';
        $patternP1 .= '(?:(?P<phase>';
        $patternP1 .= '(';
        $patternP1 .= '(';
        $patternP1 .= '\-(第)?';
        $patternP1 .= '([0-9]+|[一二三四五六七八九十]|[ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ])';
        $patternP1 .= '[期代輯章]?';
        $patternP1 .= ')|';
        $patternP1 .= '(';
        $patternP1 .= '(第)?';
        $patternP1 .= '[0-9一二三四五六七八九十ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ]+';
        $patternP1 .= '[期代輯章]、?\,?';
        //$patternP1 .= '([期代輯章]|['.$this->SPexpPhase.'])、?\,?';
        $patternP1 .= ')+';
        $patternP1 .= ')';
        $patternP1 .= '))';
        $patternP1 .= '(?:(?P<post>(.*?[^區棟樓田居宅])))$';
        $patternP1 .= '/ui';
        $this->normalized = $this->replace($patternP1, function ($m) {
            $this->tmatches .= '<span style="color:#3d0000;">[P1]</span>'.$m['post'];
            if(preg_match('/([富星][一二]代)$/u', $m['pre'].$m['phase'])){
                return $m[0];
            }
            $postContent = $this->assortPost($m['post']);
            if(!empty($m['phase'])){
                $numPart = $this->numAnalysis($m['phase']);
                if(!empty($numPart)){
                    $this->parsePhase =  $numPart;
                    return $m['pre'] . $postContent;
                }
            }
            return $m['pre'] . $m['phase'] . $postContent;
        });   

        //+++++++++++++++++++++++++++++++++++++++++++++ 【A2】 ++++++++++++++++++++++++++++++++++++++++++++++
        //【A2】區域處理，再處理字串內含「區」、「棟」、「樓」...等的區域 
        $patternA2 = '/';
        $patternA2 .= '^(?!('.$this->SPexpArea.'))';//摒除例外
        $patternA2 .= '(?:(?P<pre>.{2,}?))';
        $patternA2 .= '(?:(?P<area>(';
        $patternA2 .= '[\-期第雄]?\(?';
        $patternA2 .= '(';
        $patternA2 .= '[特]([a-zA-Z]、?)+[區棟樓館座]|';
        $patternA2 .= '([ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ][區棟樓館座])+|';
        $patternA2 .= '(([a-zA-Z]{1,6}[0-9]{0,2}[、\,]?)+[區棟樓館座])+|';//NOTE：字母最大值若是超過6字元，則會導致分析出錯
        $patternA2 .= '[零壹貳貮參参叄叁肆䦉伍陸柒扒玖拾]{1}[區棟館]|';
        $patternA2 .= '([0-9一二三四五六七八九十]{1,2}[區棟館])|';
        $patternA2 .= '[甲乙丙丁戊]{1,3}[區棟座]|';
        $patternA2 .= '[上下大]?[前後東南西北]{1,2}[0-9]?[區棟側]';
        $patternA2 .= ')';
        $patternA2 .= ')))';
        $patternA2 .= "(?:(?P<post>.*?))$";
        $patternA2 .= '/ui';
        $this->normalized = $this->replace($patternA2, function ($m) {
            $this->tmatches .= '<span style="color:#999900;">[A2]</span>'.$m['area'];
            $pureArea = str_replace(['第','期','(',')','-','、'], ['','','','','',','], $m['area']);
            $this->parseAreaExt .= $pureArea;
            /*
            if(!empty($this->parseArea)) {
                $this->parseAreaExt .= $pureArea;
            } else {
                $this->parseArea .= $pureArea;
            }
            */
            $postContent = $this->assortPost($m['post']);
            return $m['pre'].$postContent;
        });

        //+++++++++++++++++++++++++++++++++++++++++++++ 【A3】 ++++++++++++++++++++++++++++++++++++++++++++++


        $this->BCtypeFilter();




        //+++++++++++++++++++++++++++++++++++++++++++++ 【A3】 ++++++++++++++++++++++++++++++++++++++++++++++
        //【A3】區域處理，處理特殊命名 
        
        $patternA3 = '/';
        $patternA3 .= '(?:(?P<pre>.{2,}?))';
        $patternA3 .= '(?<!(紀念|生活))';
        $patternA3 .= '(?:(?P<area>\-?(';
        $patternA3 .= '(';
            $patternA3 .= '(';
                $patternA3 .= $this->SPexpMultiChars.'|';
                $patternA3 .= '[花公校學]園|';
                $patternA3 .= '(('.$this->SPprefixType.')+|';
                $patternA3 .= '(?!(花園|公園|校園|學園))[^0-9a-zA-Z]{2})';
                //$patternA3 .= '[^0-9a-zA-Z]{2}';
            $patternA3 .= ')';

            $patternA3 .= '(';
                $patternA3 .= '(新?[社特園專]?[區])|';
                $patternA3 .= '(新?[特專]?[區])|';
                $patternA3 .= '([新小一二三四五六七八九十]?[會公賓分別行涵]?[館])|';
                $patternA3 .= '基地|商圈|總部|寶座|王座';
            $patternA3 .= ')';
        $patternA3 .= ')|';

        $patternA3 .= '(新?('.$this->SPexpSingleChar.')+[館座])|';
        $patternA3 .= '((?!('.$this->SPexpSingleChar.'))([^a-zA-Z0-9]{2}[館座]))|';
        //$patternA3 .= '([^a-zA-Z0-9'.$this->SPexpSingleChar.']{2}[館座])|';
        //$patternA3 .= '樓中樓|';
        $patternA3 .= '([^a-zA-Z0-9'.$this->SPexpSingleChar.'][^大]樓)|';
        $patternA3 .= '$';
        
        $patternA3 .= ')))';
        $patternA3 .= '(?:(?P<post>.*?))';
        $patternA3 .= '/iu';
        $this->normalized = $this->replace($patternA3, function ($m) {
            if(empty($m['area'])){
                return $m[0];
            }
            $pureArea = str_replace('-','', $m['area']);
            //+++++++++++++++++++++++++++++++++++++++++++++ 【A3-1】 ++++++++++++++++++++++++++++++++++++++++++++++
            if($this->normalized == $m['area']){
                $this->tmatches .= '<span style="color:#999900;">[A3-1]</span>'.$m[0];
                return $m[0];
            }
            //+++++++++++++++++++++++++++++++++++++++++++++ 【A3-2】 ++++++++++++++++++++++++++++++++++++++++++++++
            if (preg_match('/^([^a-zA-Z0-9]{1,2}社區)$/u', $pureArea) == true || preg_match('/^('.$this->SPexpArea.')$/u', $m['pre'].$pureArea) == true) {
                $this->tmatches .= '<span style="color:#999900;">[A3-2]</span>'.$pureArea;
                return $m[0];
            }
            //+++++++++++++++++++++++++++++++++++++++++++++ 【A3-3】 ++++++++++++++++++++++++++++++++++++++++++++++
            if (preg_match('/^(('.$this->SPbcTypeBasic.')區?&?)+$/u', $pureArea) == true) {
                $this->tmatches .= '<span style="color:#999900;">[A3-3]</span>'.$pureArea;
                $this->parseBCtype =  str_replace('區', '', $pureArea) . $this->parseBCtype;
                //return $m['pre'].$m['post'];
            }
            //+++++++++++++++++++++++++++++++++++++++++++++ 【A3】 ++++++++++++++++++++++++++++++++++++++++++++++
            $this->tmatches .= '<span style="color:#999900;">[A3]</span>'.$m['area'];
            //當pre值不為空值時(區域值在後)
            if(!empty($m['pre'])){
                if(!empty($this->parseArea)) {//當「區域」項不為空值時
                    $this->parseAreaExt .= $pureArea;//將area值加入到區域屬區項
                } else {//當「區域」項為空值時
                    $this->parseArea .= $pureArea;//將area值加入到區域項
                }
            }
            //當pre值為空值時(區域值在前)
            else {
                if(!empty($this->parseArea)) {//當「區域」項不為空值時
                    $this->parseArea .= $pureArea;//將area值加入到區域項之後
                } else {//當「區域」項為空值時
                    return $pureArea.$m['post'];//回傳area值+post值
                }
            }
            return $m['pre'].$m['post'];//統一回傳不帶「區域」項內容的字串（去除區域項的建案名稱）
        });
        //+++++++++++++++++++++++++++++++++++++++++++++ 【P2】 ++++++++++++++++++++++++++++++++++++++++++++++
        //【P2】期數分析第二步驟：將字串中間的數字分離成為獨立的期數字串條件為，整個字串開頭可以為任意值，中間待擷取的數字前後
        // 不得為數字以及字母，且之後的字串必須兩個字元以上。               
        $ptnP2 = '/';
        $ptnP2 .= '(?:(?P<pre>(.*?[^0-9a-zA-Z]+)))';
        $ptnP2 .= '(?:(?P<phase>';
        $ptnP2 .= '([0-9ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ]+[期代輯章]?)';
        $ptnP2 .= '(?!('.$this->SPexpPhase.'))';//當數字之後不是「期」、「代」、「輯」、「章」時，同時也不能是期數例外內的字串。
        $ptnP2 .= '))(?!行館)';
        $ptnP2 .= '(?:(?P<post>([^0-9a-zA-Z]{2,})))$';
        $ptnP2 .= '/ui';
        $this->normalized = $this->replace($ptnP2, function ($m) {
            $this->tmatches .= '<span style="color:#3d0000;">[P2]</span>'.$m['phase'];
            $postContent = $this->assortPost($m['post']);
            if(!empty($m['phase'])){
                $numPart = $this->numAnalysis(str_replace(['期','代','輯','章'], '', $m['phase']));
                if(!empty($numPart)){
                    $this->parsePhase =  $numPart;
                    return $m['pre'] . $postContent;
                }
            }
            return $m['pre'] . $m['phase'] . $postContent;
        });   

        //+++++++++++++++++++++++++++++++++++++++++++++ 【P3】 ++++++++++++++++++++++++++++++++++++++++++++++
        //【P3】期數分析第三步驟：無論在整個字串中的任何位置，以建案期數切割，前段保留，後段分析歸類後，依分析結果保留或刪除。
        $patternP3 = '/';
        $patternP3 .= '(?!([0-9一二三四五六七八九十][期代輯章]))';//建案名稱字串開頭不得為大小寫數字接著「期」、「代」、「輯」、「章」的組合
        $patternP3 .= '(?:(?P<pre>(.*?(([0-9]|'.$this->SPexpDI.')*?))))';//過濾名稱中最末字元為「第」時，與期數之前的「第」衝突
        $patternP3 .= '(?:(?P<phase>(';
            $patternP3 .= '(';
                $patternP3 .= '(';
                $patternP3 .= '(\s?\-?\s?第[0-9一二三四五六七八九十]+)|';//擷取期數值，包含「第」字元開頭時，期數值可為無限字元
                    $patternP3 .= '(';
                        $patternP3 .= '\s?\-?\s?';
                        $patternP3 .= '(';
                        $patternP3 .= '([一二三四五六七八九十]+)|';//擷取期數值，不包含「第」字元的純數字時，期數值僅可為至多一位字元
                        $patternP3 .= '[0-9]+|';
                        $patternP3 .= '[ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ]+';
                        $patternP3 .= ')';
                    $patternP3 .= ')';
                $patternP3 .= ')';
            $patternP3 .= '([期代輯章](、?\,?)|$)';
            
            $patternP3 .= ')+|';
            $patternP3 .= '(\s?\-?\s?((NO)\s?[0-9]+)+)';
        $patternP3 .= ')';//<phase>第一個右括號
        $patternP3 .= '(?!(田|居|宅))';

        $patternP3 .= '))';
        $patternP3 .= '(?:(?P<post>(.*)))';
        $patternP3 .= '/u';
        $this->normalized = $this->replace($patternP3, function ($m) {
            $this->tmatches .= '<span style="color:#4d9900;">[P3]</span>'.$m['phase'];
            $this->bcMode = 2;
            //當已存在期數或頭段字串尾端為「[@dot@]」、「.」或是「字母」時，不進行分析並返回
            if(!empty($this->parsePhase) || (preg_match('/((\[@dot@\]|\.))$/',$m['pre']) && !in_array(mb_substr($m['phase'], -1), ['期','代','輯','章']))){
                return $m[0];
            }
            else if(preg_match('/([富星][一二]代)$/u', $m['pre'].$m['phase'])){
                return $m[0];
            }
            else if(preg_match('/(([第壹貳參肆伍陸柒捌玖拾|砌傳|硯如|騰青|鴻柏鴻|鋐微|金統][一二三四五六七八九十])|[一二三四五六七八九十]{2,}|無二|雙十|不二)$/u',$m['pre'].$m['phase']) || mb_strlen($m['pre'])==1){
                return $m[0];
            }

            //將純淨的尾段字串後送分析
            //將匹配出的數字類字串進行分析轉換後返回
            $numPart = $this->numAnalysis($m['phase']);
            $postContent = '';
            if(!empty($m['pre'])){ $postContent = $this->assortPost($m['post']); }
            else{ $postContent = $m['post']; }
            //分析過後的數字字串若非為空值，則寫入期數變數中，同時於原始字串中刪除
            if(!empty($numPart)){
                $this->parsePhase =  $numPart;
                return $m['pre'] . $postContent;
            }
            //分析過後的數字字串若為空值，則原樣返回不刪除（此時尾段字串已完成分析）
            return $m['pre'] . $m['phase'] . $postContent;
        });
        $this->normalized = $this->finalizing();
        return $this->normalized;
    }

    //////////////////////////////////////// 【【 建案名稱結束處理 】】 ////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////// finalizing 經過分段意義處理過後的建案名稱，應該已經為簡化的字串， ///////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    private function finalizing()
    {
        //$this->tempInBrackets = $m['brackets'];//暫時存於變數中待後續處理
        //$this->tempAfterBrackets = $m['post'];//暫時存於變數中待後續處理
        $this->normalized = $this->replace([
            '()' => '',
            '_' => '',
            '‧' => '',
            ' ' => ''
        ]);
        //合併建商簡稱以及營造商簡稱
        $getIVabbr = $this->investorAbbr;
        $getCTabbr = $this->constructerAbbr;
        //合併建商簡稱以及營造商簡稱為一組建商簡稱陣列，此陣列可能為空值
        if(!empty($getIVabbr) && !empty($getCTabbr)){
            $this->ivctAbbr = $getIVabbr . ',' . $getCTabbr;
        } else if(!empty($getIVabbr) && empty($getCTabbr)){
            $this->ivctAbbr = $getIVabbr;
        } else if(empty($getIVabbr) && !empty($getCTabbr)){
            $this->ivctAbbr = $getCTabbr;
        }
        $this->arrIVCTabbr = explode(',', $this->ivctAbbr);

        $bracketsContent = '';
        $postContent = '';
        if(!empty($this->tempInBrackets)) {
            $bracketsContent = $this->assortPost($this->tempInBrackets);
        }
        if(!empty($this->tempAfterBrackets)) {
            $postContent = $this->assortPost($this->tempAfterBrackets);
        }
        //$this->BCtypeFilter();
        //$this->finalLocSplitter();
        $this->finalInvestorSplitter();
        //整理「子區」欄位內容
    
        if(!empty($this->parseArea)){
            $this->parseArea = preg_replace_callback('/^(.*?)$/u', function ($m) {
                
                return $m[0];

            }, $this->parseArea);
        }
        //$this->normalized = str_replace([' ','[@dash@]'], ['','-'], $this->normalized);
        
        if(mb_strlen($this->normalized) == 1 || in_array($this->normalized, $this->arrIVCTabbr) == true){
            $this->tmatches .= '<span style="color:#009999;">[FN-1]</span>';
            $this->normalized .= $bracketsContent . $postContent;
        }

        return $this->normalized;
    }
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    ///////////////////////////////////// 建商名稱分析處理 //////////////////////////////////////////
    /////////////////////////// 取得建商名稱字串並去除指定的字元輸出簡稱 ////////////////////////////////
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    private function builderFilter($builderString='')
    {
        if(empty($builderString)){ return ''; }
        $builderString = preg_replace('/(?!^(企業|生活|建築|藝術|科學))(('. $this->passBasic.'|'.$this->typeBasic.')+|(.{2}分公司))/u','', $builderString);
        $this->tmatches .= '<span style="color:#009999;">[B0]</span>'.$builderString;
        return $builderString;//輸出為簡稱(通常為兩個中文字元，偶爾會有三個甚至四個以上的字元)
    }

    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    /////////////////////////////////// 建案名稱字串意義分析處理 ////////////////////////////////////////
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    private function assortPost($post='')
    {   
        if(empty($post)){ return ''; }
        //+++++++++++++++++++++++++++++++++++++++++++++ 【AP0】 ++++++++++++++++++++++++++++++++++++++++++++++
        //【AP0】 當字串僅為一個右括號「)」時，終止分析返回原始字串
        if($post == ')'){
            $this->tmatches .= '<span style="color:#cc0099;">[AP0]</span>'.$post;
            return $post;
        }
        ///////////// 當字串不僅為一個右括號「)」時，繼續進行意義分析
        else {
            ////////// 首先先將字串中的特殊符號去除
            $doPost = str_replace(['[@dash@]','(',')','-','~','、',' '], '', $post);
            ////////////////////////////////////////////////////////////////////////////////////////////
            ////////// 進行字串意義分析 ///////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////////

            //+++++++++++++++++++++++++++++++++++++++++++++ 【AP1】 ++++++++++++++++++++++++++++++++++++++++++++++
            //【AP1】當字串為「單」、「雙」、「單號」或是「雙號」時，代表建案名稱為住址型態，且該住址為指定道路段的單號或雙號
            // 歸類為建案子區域屬區(子區域的再分區，此時子區域可能為住址或是路段)，並於字串中刪除
            if(preg_match('/^([雙單]號?)$/u', $doPost)) {
                $this->tmatches .= '<span style="color:#cc0099;">[AP1]</span>'.$doPost;
                $this->parseLocated .= $doPost;
            }
            //+++++++++++++++++++++++++++++++++++++++++++++ 【AP2】 ++++++++++++++++++++++++++++++++++++++++++++++
            //【AP2】當字串為建案類型列表中至少一組時，代表此字串為建案類型，將其分配至建案類型欄位項目中，並在字串中刪除
            else if (preg_match('/^(('.$this->SPbcTypeBasic.')區?&?)+$/u', $doPost) == true) {
                $this->tmatches .= '<span style="color:#cc0099;">[AP2]</span>'.$doPost;
                if(mb_substr($doPost, -1) == '區') {
                    if ($doPost == '社區') {
                        return $doPost;
                    } else {
                        $this->parseArea .= $doPost;
                        $this->parseBCtype =  str_replace('區', '', $doPost) . $this->parseBCtype;
                        return '';
                    }
                    
                }
                $this->parseBCtype =  $doPost . $this->parseBCtype;
            }
            //+++++++++++++++++++++++++++++++++++++++++++++ 【AP3】 ++++++++++++++++++++++++++++++++++++++++++++++
            //【AP3】當字串為「字母」開頭（不包含「NO」字元），其後為至少一位數字結尾時，代表此字串為建案名稱以下的區域
            // 歸類為建案無論是否帶有期數的區域，並於字串中刪除
            else if(preg_match('/^(?!NO)([a-zA-Z][0-9]*區?)$/u', $doPost)) {
                $this->tmatches .= '<span style="color:#cc0099;">[AP3]</span>'.$doPost;
                if(!empty($this->parseArea)) {
                    $this->parseAreaExt .= $doPost;
                } else {
                    $this->parseArea .= $doPost;
                }
            }
            //+++++++++++++++++++++++++++++++++++++++++++++ 【AP4】 ++++++++++++++++++++++++++++++++++++++++++++++
            //【AP4】當字串為「阿拉伯數字」或是「國字數字」開頭，無論開始是否帶有「第」，或是之後是否帶有「期、代、輯、章」等字元，而之後允許接著無論
            // 是否帶有「區、棟」結尾的字母時，取得的數字即為建案的期數，而之後的字串則為建案期數相關的區域
            // 歸類為建案期數，以及可能帶有這個期數相關聯的區域
            else if(preg_match('/^(第?[0-9一二三四五六七八九十ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ]+[期代輯章]?)(([a-zA-Z]+[區棟]?)?)$/u', $doPost)) {
                $this->tmatches .= '<span style="color:#cc0099;">[AP4]</span>'.$doPost;
                //把頭尾相關的字元去除，只留取數值
                $numPart = $this->toNumber(str_replace(['第','期','代','輯','章'], '', $doPost));
                //當所得數值為純數字，且僅為單一數值，則取得的數值為唯一期數值
                if(is_numeric($numPart)){
                    //當所得數值大於最大期數設定值時，終止分析返回原始字串
                    if ($numPart > $this->phaseMax) {
                        return $doPost;
                    }
                    //當所得數值小於等於最大期數設定值時，將其寫入期數變數值，並於字串中刪除
                    if(empty($this->parsePhase)){
                        $this->parsePhase = $numPart;
                    } else {
                        if(!empty($this->parseArea)){
                            $this->parseAreaExt = $doPost;
                        } else {
                            $this->parseArea .= $doPost;
                        }
                        
                    }
                }
                //當所得數值非為純數字，並且期數之後包含了至少一個字母字元時，這組字串無論是否以「區、棟」結尾皆成立
                //同時歸類為建案期數以及其相關的區域，並於字串中刪除
                else if(preg_match('/^(第?[0-9一二三四五六七八九十]+[期代輯章])(([a-zA-Z]+[區棟]?))$/u', $doPost)) {
                    $postSplit = preg_split('/[期代輯章]/u', $doPost);
                    $this->parsePhase = $this->toNumber(str_replace('第', '', $postSplit[0]));
                    if(!empty($this->parseArea)) {
                        $this->parseAreaExt .= $postSplit[1];
                    } else {
                        $this->parseArea .= $postSplit[1];
                    }
                }
                //沒有成立的字串則終止分析返回原始字串
                else {
                    return $post;
                }                                    
            }
            //+++++++++++++++++++++++++++++++++++++++++++++ 【AP5】 ++++++++++++++++++++++++++++++++++++++++++++++
            //【AP5】當字串以「NO」開頭，並以至少一位至多2位數字結尾時，代表此字串為建案期數
            // 歸類為建案期數 ，並於字串中刪除
            else if(preg_match('/^NO([0-9]{1,2})$/', $doPost)) {
                $this->tmatches .= '<span style="color:#cc0099;">[AP5]</span>'.$doPost;
                $this->parsePhase = str_replace('NO', '', $doPost);
            }
            //+++++++++++++++++++++++++++++++++++++++++++++ 【AP6】 ++++++++++++++++++++++++++++++++++++++++++++++
            //【AP6】當字串以「之」開頭，並以至少一位阿拉伯數字或國字數字結尾時，代表此字串為期數或區域之下的再分類
            // 歸類為建案子區域屬區，並於字串中刪除
            else if(preg_match('/^之([0-9一二三四五六七八九十]+)$/u', $doPost)) {
                $this->tmatches .= '<span style="color:#cc0099;">[AP6]</span>'.$doPost;
                $this->parseAreaExt .= $this->toNumber(str_replace('之', '', $doPost));
            }
            //+++++++++++++++++++++++++++++++++++++++++++++ 【AP7】 ++++++++++++++++++++++++++++++++++++++++++++++
            //【AP7】當字串中出現下列字詞時，代表此字串為建案的建築結構屬性或是用途
            // 歸類為建案子區域屬區，除了「社區」保留於建案名稱外，其餘從字串中刪除，並同時去除尾字「區」，另外於建案型態變數中加入此字詞
            //(?:(?P<pre>(
            //else if(preg_match('/^(\(?('.$this->SPprefixType.')?('.$this->SPfunctionType.')?(('.$this->SPbcTypeBasic.'|大[1-9一二三四五]房)+)?區?[A-Z]?區?\)?)$/u', $doPost, $matches)) {
            else if(preg_match('/^(\(?(?:(?P<pre>(([0-9]+[F樓])?('.$this->SPprefixType.')?('.$this->SPfunctionType.')?(('.$this->SPbcTypeBasic.'|大[1-9一二三四五]房)+)?區?([0-9]+[F樓])?)))(?:(?P<post>([A-Z]?區?)))\)?)$/u', $doPost, $matches)) {
                $this->tmatches .= '<span style="color:#cc0099;">[AP7]</span>'.$matches['pre'];

                if(mb_substr($matches['pre'], -1) == '區'){
                    $lastSecond = mb_substr($matches['pre'], -2, 1);

                    if($lastSecond=='社'){
                        return '社區';
                    } else {
                        if(!empty($this->parseArea)) {
                            $this->parseAreaExt .= $matches['pre'];
                        } else {
                            $this->parseArea .= $matches['pre'];
                        }
                        $this->parseBCtype =  (in_array($lastSecond, ['校','園']) ? $matches['pre'] : str_replace('區', '', $matches['pre'])) . $this->parseBCtype;
                        //return '';
                    }
                } else {
                    $this->parseBCtype =  $matches['pre'] . $this->parseBCtype;
                    //return '';
                }

                if(!empty($matches['post'])){
                    if(!empty($this->parseArea)){
                        $this->parseAreaExt .= $matches['post'];
                    } else {
                        $this->parseArea = $matches['post'];
                    }
                }




                return '';
            }
            //+++++++++++++++++++++++++++++++++++++++++++++ 【AP8】 ++++++++++++++++++++++++++++++++++++++++++++++
            //【AP8】當字串為「甲、乙、丙、丁、戊」任一字元，或是文字字串並以「區、棟、樓、側、館、座」其一結尾時
            // 歸類為建案子區域屬區，並於字串中刪除
            else if(preg_match('/^(([甲乙丙丁戊])|([^\-]+[區棟樓側館座]))$/u', $doPost)) {
                $this->tmatches .= '<span style="color:#cc0099;">[AP8]</span>'.$doPost;
                //當字串不是「區、棟、樓、側、館、座」中的任一單一字元時(具備完整字串)，寫入建案子區域屬區變數
                $lastUnit = mb_substr($doPost, -1);
                if(!in_array($lastUnit, ['區','棟','樓','側','館','座'])){
                    $this->parseAreaExt .=  $doPost;
                } else {
                    if ($doPost == '樓中樓') {
                        $this->parseBCtype =  $doPost . $this->parseBCtype;
                        return '樓中樓';
                    } else {
                        if (!empty($this->parseArea)) {
                            $this->parseAreaExt .= $doPost;
                        } else {
                            $this->parseArea .= $doPost;
                        }
                    }
                }
            }
            //+++++++++++++++++++++++++++++++++++++++++++++ 【AP9】 ++++++++++++++++++++++++++++++++++++++++++++++
            //【AP9】當字串為住址或類住址字串時
            // 歸類為建案關係位置，並於字串中刪除
            else if(preg_match('/^(.*?([巷弄街路段號宅店舖]|(\d+[巷號弄]?([\-\,\~]\d+[巷號弄])?)+[巷號弄]))$/u', $doPost)) {
                $this->tmatches .= '<span style="color:#cc0099;">[AP9]</span>'.$doPost;
                $this->bcMode = 5;
                $this->parseLocated = $doPost . $this->parseLocated;
            }
            //+++++++++++++++++++++++++++++++++++++++++++++ 【AP10】 ++++++++++++++++++++++++++++++++++++++++++++++
            //【AP10】
            else if(preg_match('/^([A-Z]\+?[1-9]?[區棟樓館][A-Z0-9]*)$/u', $doPost)) {
                $this->tmatches .= '<span style="color:#cc0099;">[AP10]</span>'.$doPost;
                $postSplit = preg_split('/(區|棟|樓|館)/u', $doPost, -1, PREG_SPLIT_DELIM_CAPTURE);
                $this->parseArea = $postSplit[0] . $postSplit[1];
                $this->parseAreaExt = $postSplit[2];
            }
            //+++++++++++++++++++++++++++++++++++++++++++++ 【AP11】 ++++++++++++++++++++++++++++++++++++++++++++++
            //【AP11】當字串比對條件皆不符時，代表有極大的可能性為建案的別名
            // 統一歸類為建案名稱第二附屬名稱，並於字串中刪除
            //此變數值或許會有較高誤差結果，不建議拿來做深入比對使用
            else {
                $this->tmatches .= '<span style="color:#cc0099;">[AP11]</span>'.$doPost;
                $this->parseAka2 = $doPost . $this->parseAka2;
            }
            return '';
        }
        //+++++++++++++++++++++++++++++++++++++++++++++ 【AP】 ++++++++++++++++++++++++++++++++++++++++++++++
        //【AP】預設不做分析，返回值為原始字串
        $this->tmatches .= '<span style="color:#cc0099;">[AP]</span>'.$doPost;
        return $post;//做期數預處理（賦予固定格式以利後續規則化處理）

    }
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    /////////////////////////////// BCtypeFilter 歸類建案類別 //////////////////////////////////////
    // 在最終比對名稱的時候，進行建案類別歸類，也就是說，當經過層層篩檢過後，最終判斷出來的建案名稱中，包含了特定的字
    // 詞時，就將該字詞歸類為建案類別。例如「大樓」、「大廈」、「別墅」、「公寓」...等等。
    // 但是，仍有些許特例，例如「別墅」可能是別墅的別名，也可能是別墅的名稱，但是這兩者是不同的建案類別。如「愛情別墅」
    // 可能是一棟大樓，此時就必須手動偵錯修改。
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    private function BCtypeFilter()
    {
        $ptnFM = '/^';
        $ptnFM .= '(?:(?P<pre>(.*?)))';
        $ptnFM .= '(?:(?P<bctype>(';
        //$ptnFM .= '([0-9]+[F樓])?';
        $ptnFM .= '('.$this->SPprefixType.')?';//建案類別特定字詞前綴
        $ptnFM .= '(';
        $ptnFM .= '(('.$this->SPfunctionType.')?[花公校學]?('.$this->SPbcTypeBasic.'){0,2})|';//建案類別特定字詞「功能特點」+「基本類別」
        $ptnFM .= '([花公校學]?('.$this->SPbcTypeBasic.')?('.$this->SPfunctionType.'){0,2})';//或是建案類別特定字詞「基本類別」+「功能特點」
        $ptnFM .= ')';
        //$ptnFM .= '([0-9]+[F樓])?';
        $ptnFM .= ')))';
        $ptnFM .= '(?:(?P<post>(';
        $ptnFM .= '[A-Z0-9一二三四五六七八九十〇]*';//分析為「建案類別」的字詞之後，緊接著可能為數字或字母的部分，送去「區域分析器」中進行處理
        $ptnFM .= ')))';
        $ptnFM .= '$/ui';
        $this->normalized = $this->replace($ptnFM, function ($m) {
            if(!empty($m['bctype'])){
                $numPart = '';
                if(!empty($m['post'])){
                    $numPart = $this->typeAreaAnalysis($m['post']);
                    if(!empty($numPart)){
                        $this->parseArea .= $numPart;
                        $numPart = '';
                    }
                }
                $this->parseBCtype =  $m['bctype'] . $this->parseBCtype;
                //當整個原始名稱只有建案類別時，也就是說建案名稱僅為「科技大樓」或是「智慧公寓」等等無法明確判別建案歸屬時，則主要比對名稱便以建案類別為主。
                if(!empty($m['pre'])){//有正常的建案名稱時
                    $this->tmatches .= '<span style="color:#cc0099;">[FT]</span>'.$m['pre'];
                    if(mb_strlen($m['pre'])==1){
                        return $m['pre'].$m['bctype'].$numPart;
                    }
                    /*
                    else if(mb_substr($m['pre'], -1)=='大' || mb_substr($m['pre'], -1)=='小'){
                        return $m['pre'].$m['bctype'].$numPart;
                    }
                    */
                    return $m['pre'].$numPart;
                } else {//沒有明確的建案名稱時，將建案類別當作建案名稱
                    $this->tmatches .= '<span style="color:#009900;">[FP]</span>'.$m['bctype'];
                    return $m['bctype'].$numPart;
                }
            }
            return $m[0];
        });
    }
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    ///////////////////////// finalInvestorSplitter 建案名稱分離器 /////////////////////////////////
    // 在比對名稱經過層層篩檢過後，最終判斷出來的建案名稱中，包含了建商或是營造商的簡稱時，透過此方法將建商或是營造
    // 商的簡稱分離出來，並自比對名稱中移除。
    // 但是，部份建案名稱可能是由建商或是營造商的名稱命名，若是建案名稱全名皆為建商或是營造商的名稱，則系統將其判定
    // 為建案名稱，但當建商或是營造商的簡稱之後還有其他名稱命名時，此時系統將無法判斷，並視其非為建案名稱中的一部份
    // ，仍然移出建案名稱中。
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    private function finalInvestorSplitter()//之星//之戀//之旅//之心//之頂//之冠//之道//之郷//之上//之最//之都//之居//之光
    {
        //當建商簡稱陣列不為空值時
        if(!empty($this->arrIVCTabbr)){
            $splitJoinIVCT = $this->arrIVCTabbr;
            for($i=0; $i < count($splitJoinIVCT); $i++){
                //當當下的比對名稱完全相同於建商簡稱陣列中的一組簡稱時
                if(trim($this->normalized) == $splitJoinIVCT[$i]){
                    //當「子區」欄位不為空值，且「子區」欄位不為英文字母、數字（不包括「區」）時
                    //if(!empty($this->parseArea) && preg_match('/^([^A-Za-z0-9\-\~]{2,}區?)$/u', $this->parseArea, $matches)){
                    if(!empty($this->parseArea) && !preg_match('/^(('.$this->SPbcTypeBasic.'){1,2}區?)$/u', $this->parseArea, $matches)){
                        $this->normalized = $this->parseArea;
                        $this->parseArea = '';
                        $this->tmatches .= '<span style="color:#660066;">[AreaBack]</span>';
                        break;//一旦比對名稱完全相同於建商簡稱陣列中的一組簡稱時，則跳出迴圈不再繼續
                    }
                    //當「子區」欄位為空值時，檢查兩個「別名」欄位是否有值，依別名順位補入比對名稱，並同時清除被補入的別名欄位
                    else {
                        if(!empty($this->parseAka1)){
                            $this->normalized = $this->parseAka1;
                            $this->parseAka1 = '';
                            $this->tmatches .= '<span style="color:#660066;">[Aka1Back]</span>';
                            break;//一旦比對名稱完全相同於建商簡稱陣列中的一組簡稱時，則跳出迴圈不再繼續
                        }
                        else if(!empty($this->parseAka2)){
                            $this->normalized = $this->parseAka2;
                            $this->parseAka2 = '';
                            $this->tmatches .= '<span style="color:#660066;">[Aka2Back]</span>';
                            break;//一旦比對名稱完全相同於建商簡稱陣列中的一組簡稱時，則跳出迴圈不再繼續
                        }
                    }
                    
                }
                //當當下的比對名稱不同於建商簡稱陣列中的一組簡稱時
                else {
                    $getIVlen = mb_strlen($splitJoinIVCT[$i]);//建商簡稱長度
                    //當當下的比對名稱前「建商簡稱長度字元數」與建商簡稱相同時，將比對名稱前「建商簡稱長度字元數」刪除
                    if(mb_substr($this->normalized, 0, $getIVlen) == $splitJoinIVCT[$i]){
                        $this->tmatches .= '<span style="color:#660066;">[IVCT]</span>'.$splitJoinIVCT[$i];
                        $getAfterSplitIV = trim(mb_substr($this->normalized, $getIVlen));
                        //當去除建商簡稱之後的建案名稱為「之」後連結任何一個非字母數字以及指定符號的字元結尾時，則跳出迴圈，不刪除建商簡稱
                        if(preg_match('/^之[^A-Za-z0-9\-\~]$/u', $getAfterSplitIV, $matches)){
                            break;
                        }
                        //當去除建商簡稱之後的建案名稱，比對成功後，將比對名稱前「建商簡稱長度字元數」（即建商簡稱）刪除
                        $ptnFIS = '/^';
                        $ptnFIS .= '(';
                        $ptnFIS .= '([^A-Za-z0-9\-\~]{2,})|';//非字母數字及指定符號，且字元數大於2
                        $ptnFIS .= '([^A-Za-z0-9\-\~]{1,}[a-zA-Z0-9&\+\s]{1,})|';//非字母數字及指定符號，且字元數大於1，後接1個字元以上的字母數字及指定符號//NOTE：大於1或大於2字元的分析結果不同
                        $ptnFIS .= '(之[^A-Za-z0-9\-\~])|';//第一個字元必須為「之」，之後接著1個字元的非字母數字及指定符號
                        $ptnFIS .= '([a-zA-Z0-9&\+\s]{3,})';//3個字元以上的字母數字及指定符號
                        $ptnFIS .= ')';
                        $ptnFIS .= '$/u';
                        if(preg_match($ptnFIS, $getAfterSplitIV, $matches)){
                            $this->normalized = $getAfterSplitIV;//刪除建商簡稱後的字串
                            $this->tmatches .= '<span style="color:#660066;">[FIS]</span>' . $getAfterSplitIV;
                        }

                    }
                }
            }
        }
    }

    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    /////////////////////////////////// XXXXXXXX(XXXXX) //////////////////////////////////////////
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    private function addLocSplitter()
    {
        $ptnAS = '/^';
        $ptnAS .= '(?:(?P<pre>(.*?[路街巷弄])))';
        $ptnAS .= '(?:(?P<post>([^0-9一二三四五六七八九十〇]+)))';
        $ptnAS .= '$/ui';

    }
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    /////////////////////////////////// XXXXXXXX(XXXXX) //////////////////////////////////////////
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    private function finalLocSplitter()
    {
        $ptnFS = '/^';
        $ptnFS .= '(?:(?P<pre>(.*?[路街巷弄])))';
        $ptnFS .= '(?:(?P<post>([^0-9一二三四五六七八九十〇]+)))';
        $ptnFS .= '$/ui';
        $this->normalized = $this->replace($ptnFS, function ($m) {
            if(empty($m['post'])){
                return $m[0];
            }
            $lastOne = mb_substr($m['pre'], -1);
            $firstOne = mb_substr($m['post'], 0, 1);
            if(
                ($lastOne == '街' && ($firstOne == '口' || $firstOne == '頭'))
                || (($lastOne == '路' || $lastOne == '巷') && $firstOne == '口')
                || ($lastOne == '巷' && $firstOne == '弄')
                ){
                return $m[0];
            }
            if(!empty($m['pre'])){
                $this->parseLocated =  $m['pre'] . $this->parseLocated;
                return $m['post'];
            }
            return $m[0];
        });
    }

//↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
//////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////// Functions ////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
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
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    //////////////////////////////// 國字轉阿拉伯數字(數學邏輯) //////////////////////////////////////
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
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
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    /////////////////////////////////// 修正羅馬數字(直轉) //////////////////////////////////////////
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    private function fixToRomanNumber()
    {
        $this->normalized = str_replace('VIII','Ⅷ',$this->normalized);//先將不易混淆的錯誤羅馬數字矯正
        $this->normalized = str_replace('VII','Ⅶ',$this->normalized);//先將不易混淆的錯誤羅馬數字矯正
        $this->normalized = str_replace('III','Ⅲ',$this->normalized);//先將不易混淆的錯誤羅馬數字矯正
        $this->normalized = $this->replace('/[\x{4e00}-\x{9fa5}|,|、|\-](II)期?([^0-9a-zA-Z\-]|$)/u', function ($m) {//依次替換「II」為正規羅馬數字「Ⅱ」
            return str_replace('II', 'Ⅱ', $m[0]);
        });
        $this->normalized = $this->replace('/[\x{4e00}-\x{9fa5}|,|、|\-](VI)期?([^0-9a-zA-Z\-]|$)/u', function ($m) {//依次替換「VI」為正規羅馬數字「Ⅵ」
            return str_replace('VI', 'Ⅵ', $m[0]);
        });
        $this->normalized = $this->replace('/[\x{4e00}-\x{9fa5}|,|、|\-](IV)期?([^0-9a-zA-Z\-]|$)/u', function ($m) {//依次替換「IV」為正規羅馬數字「Ⅳ」
            return str_replace('IV', 'Ⅳ', $m[0]);
        });
        $this->normalized = $this->replace('/[\x{4e00}-\x{9fa5}|,|、|\-](IX)期?([^0-9a-zA-Z\-]|$)/u', function ($m) {//依次替換「IX」為正規羅馬數字「Ⅸ」
            return str_replace('IX', 'Ⅸ', $m[0]);
        });
        $this->normalized = $this->replace('/[\x{4e00}-\x{9fa5}|,|、|\-]I[期區棟樓館座]?([^0-9a-zA-Z\-&]|$)/u', function ($m) {//依次替換「I」為正規羅馬數字「Ⅰ」
            return str_replace('I', 'Ⅰ', $m[0]);
        });
        $this->normalized = $this->replace('/[\x{4e00}-\x{9fa5}|,|、|\-]V[期區棟樓館座]?([^0-9a-zA-Z\-&]|$)/u', function ($m) {//依次替換「V」為正規羅馬數字「Ⅴ」
            return str_replace('V', 'Ⅴ', $m[0]);
        });
        $this->normalized = $this->replace('/[\x{4e00}-\x{9fa5}|,|、|\-]X[期區棟樓館座]?([^0-9a-zA-Z\-&]|$)/u', function ($m) {//依次替換「X」為正規羅馬數字「Ⅹ」
            return str_replace('X', 'Ⅹ', $m[0]);
        });
        $patternRoman = '/';
        $patternRoman .= '(?:(?P<numpart>(\s?第?\s?[ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ]\s?)))';
        $patternRoman .= '(?:(?P<unit>([期區棟樓館座]?)))';
        $patternRoman .= '/u';
        $this->normalized = $this->replace($patternRoman, function ($m) {
            $pureRN = str_replace(['第',' '], '', $m['numpart']);
            if ($m['unit'] == '期') {
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
                return '第'.$numSTR.'期';
            } else {
                return $m[0];
            }
        });
        return $this->normalized;
    }
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    //////////////////////////////////// 括號中內容處理 ////////////////////////////////////////////
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    private function insideBrackets()
    {
        if (strpos($this->normalized, '(')) {//當原名稱包含括號時
            preg_match_all('/\((.*?)\)/', $this->normalized, $match);//提取「所有」左右括號之間的內容並定義
            $this->normalized = preg_replace('/\((.*?)\)/', '', $this->normalized);//定義爲變數後即可移除「所有」括號以及括號間內容
            if (!empty($match[1])) {//當括號內容不為空時
                $matchJoin = implode(",",$match[1]);
                if (preg_match('/大樓|華廈|別墅|透天|公寓|座|棟|區|座|特區|樂章|篇|樓|店/i', $matchJoin)) {//當括號內容包含「別墅」、「大樓」、「區」...等標注時
                    $this->parseArea = $matchJoin;//將括號內容以逗號連接，此時單組結果沒有逗號，而兩組以上的結果將以逗號分開
                } else {//比對不到含「「別墅」、「大樓」、「區」...等標注時，只取陣列前兩值（因為僅預留兩欄位），分別定義別名欄位
                    $this->parseAka1 = !empty($match[1][0]) ? $match[1][0] : '';//定義「別名1」變數
                    $this->parseAka2 = !empty($match[1][1]) ? $match[1][1] : '';//定義「別名2」變數
                }
            }
        }
        return $this->normalized;
    }
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    //////////// 檢查是否為一般文字或地區格式 // 分析字串必須為純字母或是字母數字混合字串 ///////////////////
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    private function checkIfIsArea($string=''){
        if (preg_match('/[區棟樓館]/u', $string)) {
            return true;
        } else {
            //當字母字串中含有小寫字母時，一律視為建案名稱的一部份，不視為區域
            if (ctype_upper($string) == false) {
                return false;
            }
            //建立基礎字母比對字串，以此字串比對特定字母在其中的位置
            $baseString = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            //將字串轉換為字符陣列
            $charArray = str_split($string);
            //當超過一個字母時，進行字母分析
            if (count($charArray) > 1) {
                //建立一個新的字符陣列
                $sortedCharArray = $charArray;
                //將這個字符陣列重新按照A-Z的順序排列
                sort($sortedCharArray);
                //將重新排列後的陣列轉換成字串
                $sortedString = implode('', $sortedCharArray);
                //當重新組合後的字串與原字串相同時，代表這個原始字串是按照字母順序排列的，經過連續順序排列檢查通過後，系統將視為數個字母型態的區域，如ABCD區、DFHI區
                if ($string === $sortedString) {
                    //NOTE: 確定為順序排列後，在這兒先做一個迴圈，確認字串是否為連續順序排列
                    for ($i = 0; $i < count($charArray); $i++) {
                        $nowPosition = strpos($baseString, $charArray[$i]) + 1;//本字母的位置
                        $prevPosition = $i == 0 ? 0 : strpos($baseString, $charArray[$i - 1]) + 1;//前一個字母的位置
                        $gap = $nowPosition - $prevPosition;//與前一個字母間的間隔
                        if ($gap > 3) {//當與前一個字母的位置大於3個字母時，代表這個字串不是連續順序排列，終止迴圈並回傳
                            return false;
                            break;
                        }
                    }
                    return true;//經過連續順序排列檢查通過後，系統將視原始字串為數個字母型態的區域
                }
            } else {
                return true;
            }
        }
    }
    private function checkIfArea($string=''){


    }

    //字串取代
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

    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    ///////// 數字分析，通常用於單個期數以及多個期數整合為統一規格，例如：第一期第二期 => 1,2 ////////////////
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    private function numAnalysis($numSTR){
        $numReplace = [
            '第' => '',
            ' ' => '',
            '(' => '',
            ')' => '',
            '-' => ''
        ];
        $numSTR = strtr($numSTR, $numReplace);
        if(preg_match('/^([0-9ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ一二三四五六七八九十]+[期代輯章]?)$/u', $numSTR)){
            $this->tmatches .= '<span style="color:purple;">[NA-1]</span>'.$numSTR;
            $numSTR = str_replace(['期', '代', '輯', '章'], '', $numSTR);
            $setNumber = $this->toNumber($numSTR);

            if(is_numeric($setNumber) && $setNumber > $this->phaseMax){
                return '';
            } else {
                return $setNumber;
            }
        }
        $numReplace = [
            '期、' => '|',
            '代、' => '|',
            '輯、' => '|',
            '章、' => '|',
            'NO、' => '|',
            '期,' => '|',
            '代,' => '|',
            '輯,' => '|',
            '章,' => '|',
            'NO,' => '|'
        ];
        $numSTR = strtr($numSTR, $numReplace);
        $numReplace = [
            '、' => '|',
            '期' => '|',
            '代' => '|',
            '輯' => '|',
            '章' => '|',
            'NO' => '|'
        ];
        $getNumStandard = strtr($numSTR, $numReplace);
        $getNumStandard = str_replace('||', '|', $getNumStandard);
        if(mb_substr($getNumStandard, -1) == '|'){
            $getNumStandard = mb_substr($getNumStandard, 0, -1);
        }
        if(mb_substr($getNumStandard, 0, 1) == '|'){
            $getNumStandard = mb_substr($getNumStandard, 1);
        }
        $getNumSplit = explode('|', $getNumStandard);
        $setNumString = '';
        $countNum = count($getNumSplit);
        if($countNum > 1){
            for($i=0; $i<$countNum; $i++){
                $setNumString .= $this->toNumber($getNumSplit[$i]) . ',';
            }
            $setNumString = mb_substr($setNumString, 0, -1);
        } else {
            $setNumString = $this->toNumber($getNumStandard);
        }
        return $setNumString;

    }

    private function typeAreaAnalysis($areaSTR){
        if(empty($areaSTR)){
            return '';
        }
        $setNumber = $this->toNumber($areaSTR);
        return $setNumber;
    }
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    /////////////////////////////// 國字數字規格化（數學邏輯） ///////////////////////////////////////
    //↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
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
        else if(preg_match('/^([一二三四五六七八九零壹貳貮參参叄叁肆䦉伍陸柒扒玖]{2,})$/u', $cNum, $matches)){
            $groupNum = strtr($matches[0], $cTOnumObj);
            $groupNum = implode(',', str_split($groupNum));
            return $groupNum;
        }
        else if(preg_match('/^([〇一二三四五六七八九十零壹貳貮參参叄叁肆䦉伍陸柒扒玖拾]+)$/u', $cNum, $matches)){
            $firstNum = mb_substr($matches[0], 0, 1);
            if(mb_strlen($matches[0])==2){
                if($firstNum == "十" || $firstNum == "拾"){
                    $cTOnumObj['十']="1";
                    $cTOnumObj['拾']="1";
                    return strtr($matches[0], $cTOnumObj);
                } else {
                    $cTOnumObj['十']="0";
                    $cTOnumObj['拾']="0";
                    return strtr($matches[0], $cTOnumObj);
                }
            } else {
                $cTOnumObj['十']="";
                $cTOnumObj['拾']="";
                return strtr($matches[0], $cTOnumObj);
            }
        } else {
            return $cNum;
        }
    }
    //
    private function toFlat($groups){
        if(empty($groups)){ return false; }
        $res = array_map(function ($para) {
            return implode('', $para);
        }, $groups);
        return implode('', $res);
    }
//↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////// OutPuts /////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ //
    public function investorAbbr()
    {
        return $this->investorAbbr;
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
            'origInvestor' => $this->origInvestor,//已知的投資商(建商)
            'origConstructer' => $this->origConstructer,//已知的營造單位
            'investorAbbr' => $this->investorAbbr,//建商縮寫(通常為兩個字) 
            'constructerAbbr' => $this->constructerAbbr,//營造單位縮寫(通常為兩個字)
            'parseTitle' => $this->normalized,//經過正規化的字串，主要比對目標
            'parseBCtype' => $this->parseBCtype,//建案中建物型態
            'parsePhase' => $this->parsePhase,   //建案期數(空值時視為1期)     
            'parseAka1' => $this->parseAka1,//比對別名1
            'parseAka2' => $this->parseAka2,//比對別名2
            'parseLocated' => $this->parseLocated,//相關座落地理位置
            'parseArea' => $this->parseArea,//建案子區域
            'parseAreaExt' => $this->parseAreaExt,//建案子區域屬區
            'origFixed' => $this->origFixed,//正規化後的全名
            'parseBCmode' => $this->bcMode,//資料建立狀態歸類
            'tmatches' => $this->tmatches,//分析註記
            'tDebug' => $this->tDebug//Debug 註記
        );
        //'@@【NT】<span style="color:red;">'.$this->normalized.'</span>@@【OF】<span style="color:blue;">'.$this->origFixed.'</span>@@【IV】'.$this->investorAbbr.'@@【PH】'.$this->parsePhase.'@@【SB1】'.$this->parseAka1.'@@【SB2】'.$this->parseAka2.'@@【LO】'.$this->parseLocated.'@@【EX】'.$this->parseArea;
    }

}
