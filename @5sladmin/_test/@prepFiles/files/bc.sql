
DROP TABLE IF EXISTS `data_buildcase`;

CREATE TABLE `data_buildcase` (
	`bc_id` int NOT NULL,
	`bc_city` varchar(2) NOT NULL COMMENT '所在城市 ## 與data_locations.loc_code 值相關聯 coverage',
	`bc_district` varchar(4) NOT NULL COMMENT '所在區劃 ## 與data_locations.loc_code 值相關聯',
	`bc_title` varchar(128) NOT NULL COMMENT '社區名稱',
	`bc_subtitle` varchar(128) NOT NULL COMMENT '社區別稱',
	`bc_subtitle2` varchar(128) NOT NULL COMMENT '社區別稱2',
	`bc_mode` tinyint(1) NOT NULL COMMENT '建案類別 ## 0-老舊建案;1-新建案;2-預售屋(未完工)',
	`bc_type` tinyint(1) NOT NULL COMMENT '建案型態 ## 1-公寓(5樓含以下無電梯);2-透天厝;3-店面(店鋪);4-辦公商業大樓;5-住宅大樓(11層含以上有電梯);6-華廈(10層含以下有電梯);7-套房(1房1廳1衛);8-工廠;9-廠辦;10-農舍;11-倉庫;',
	`bc_usefor` tinyint(1) NOT NULL COMMENT '用途規劃 ## 1-住家用;2-商業用;3-工業用;4-農業用;5-住商用; 6-住工用; 7-工商用; 8-停車空間; 9-見使用執照;0-其他',
	`bc_addr` varchar(128) NULL COMMENT '基地地址 ##10 排除城市與行政區後的詳細住址或地段',
    `bc_lat` DECIMAL(10, 8) NULL COMMENT '基地座標X ##',
    `bc_lng` DECIMAL(11, 8) NULL COMMENT '基地座標Y ##',
    `bc_area` float NULL COMMENT '基地面積 ##以「坪」計',
    `bc_totalnum` smallint NULL COMMENT '總戶數',
	`bc_pratio` decimal(2,2) NULL COMMENT '公設比 ## ',
	`bc_coverage` decimal(2,2) NULL COMMENT '建蔽率 ## 建築物的水平投影面積與建築基地面積的比值',
	`bc_far` decimal(3,2) NULL COMMENT '容積率 ## 各樓層的樓地板面積（即為容積）加起來除以基地面積',
	`bc_completion` date NULL COMMENT '## 交屋時間/預期交屋 ## 空值為隨時交屋',
	`bc_state` tinyint(1) NULL COMMENT '## 交屋屋況 ## 0-其他;1-毛胚屋;2-標準配備;3-裝潢;4-裝潢+傢俱',
	`bc_plan` varchar(128) NULL COMMENT '## 格局規劃 ##20 #例# 二房(28坪)、三房(41坪)',
    `bc_layernum` varchar(64) NULL COMMENT '## 每層戶數',
	`bc_des` text NULL COMMENT '## 特色說明 ## 特色說明 #例# 無',
	`bc_unitprice` varchar(24) NULL COMMENT '## 建案單價 ## #例# 82~84 萬/坪',
	`bc_totalprice` varchar(26) NULL COMMENT '## 建案總價 ## #例# 2500~3550 萬/戶',
	`bc_pprice` varchar(24) NULL COMMENT '## 車位價格 ## #例# 190~250 (萬)',
	`bc_sprice` varchar(24) NULL COMMENT '## 商鋪價格 ## #例# 190~250 (萬)',
	`bc_selling` varchar(24) NULL COMMENT '## 公開銷售 ## 0-無;1-銷售中',
	`bc_loan` decimal(2,2) NULL COMMENT '## 貸款成數 ## 不帶%記號 #例# 80',
	`bc_meetadd` varchar(128) NULL COMMENT '## 接待會館地址(完整地址)',
	`bc_parkratio` decimal(2,2) NULL COMMENT '## 車位配比##30 社區規劃 #例# 1-一戶一位(1:1);2-一戶二位',
	`bc_parkplan` varchar(128) NULL COMMENT '## 車位規劃 ## 社區規劃 #例# 平面式25個、機械式5個',
	`bc_parknum` int NULL COMMENT '## 車位總數量 ##',
	`bc_building` varchar(128) NULL COMMENT '## 棟戶規劃 ## 社區規劃 #例# 1幢,1棟,28戶住家,2戶店面',
	`bc_floor` varchar(128) NULL COMMENT '## 樓層規劃 ## 社區規劃 #例# 地上8層，地下3層',
	`bc_direction` varchar(128) NULL COMMENT '## 座向規劃 ## 社區規劃 #例# 朝北、朝南、朝西、朝東',
	`bc_pmc` bit(1) NOT NULL COMMENT '## 物業公司 ## 社區規劃 #例# 0-無;1-有',
	`bc_rmc` bit(1) NOT NULL COMMENT '## 管委會 ## 社區規劃 #例# 0-無;1-已成立',
	`bc_mfee` varchar(24) NULL COMMENT '## 管理費用 ## 社區規劃 #例# 120元/坪/月',
	`bc_structure` varchar(24) NULL COMMENT '## 結構工程 ## 1-木構;2-磚構;3-石構;4-鋼筋混凝土(RC);5-鋼骨構造(SC);6-鋼骨鋼筋混凝土(SRC);7-塑膠構造;8-橡皮構造',
	`bc_zoning` varchar(24) NULL COMMENT '## 土地分區 ##40 社區規劃 #例# 第三種住宅區',
	`bc_blicense` varchar(128) NULL COMMENT '## 建造執照 ## 社區規劃 #例# 109建字第0221號',
	`bc_ulicense` varchar(128) NULL COMMENT '## 使用執照 ## 社區規劃 #例# 111使字第0129號',
	`bc_materials` varchar(128) NULL COMMENT '## 建材說明 ## 社區規劃 #例# 結構體保固20年、防水保固5年、固定設備保固2年，社區搭配OTIS電梯，Panasonic廚具，KOHLER衛浴瓷器、Hansgrohe冷熱水龍頭，',
	`bc_inpublic` varchar(128) NULL COMMENT '## 公共設施 ## 社區規劃 #例# 接待大廳交誼廳兒童遊戲區',
    `bc_pile` tinyint(1) NOT NULL COMMENT '## 充電樁',
    `bc_trash` varchar(24) NOT NULL COMMENT '## 垃圾處理',
	`bc_investor` varchar(128) NULL COMMENT '## 投資建設 ## 建築設計 #例# 樂揚建設股份有限公司',
	`bc_constructer` varchar(128) NULL COMMENT '## 營造公司 ## 建築設計 #例# 順緯營造股份有限公司',
	`bc_saleteam` varchar(128) NULL COMMENT '## 企劃銷售 ## 建築設計',
	`bc_cdesign` varchar(128) NULL COMMENT '## 建築設計 ##50 建築設計 #例# 昌瑜建築師事務所',
	`bc_vdesign` varchar(128) NULL COMMENT '## 景觀設計 ## 建築設計 #例# 紅谷花苑',
	`bc_pdesign` varchar(128) NULL COMMENT '## 公設設計 ## 建築設計 #例# 左越設計/晴璞設計',
	`bc_ldesign` varchar(128) NULL COMMENT '## 燈光設計 ## 建築設計 #例# 無',
	`bc_mrt` varchar(128) NULL COMMENT '## 捷運系統 ## 交通出行 #例# 萬大線加蚋站',
	`bc_hiway` varchar(128) NULL COMMENT '## 高速公路 ## 交通出行 #例# 暫無',
	`bc_speedway` varchar(128) NULL COMMENT '## 快速道路 ## 交通出行 #例# 水源快速道路',
	`bc_thsr` varchar(128) NULL COMMENT '## 高鐵系統 ## 交通出行 #例# 暫無',
	`bc_tra` varchar(128) NULL COMMENT '## 臺鐵系統 ## 交通出行 #例# 萬華車站',
	`bc_othertraffic` varchar(128) NULL COMMENT '## 其他方式 ## 交通出行 #例# 暫無',
	`bc_edu` varchar(128) NULL COMMENT '## 學區 ##60 周邊機能 #例# 萬大國小、萬華國中',
	`bc_market` varchar(128) NULL COMMENT '## 超商/賣場 ## 周邊機能 #例# 全聯福利中心',
	`bc_omarket` varchar(128) NULL COMMENT '## 傳統市場 ## 周邊機能 #例# 東園市場ZZ',
	`bc_outpublic` varchar(128) NULL COMMENT '## 公共建設 ## 周邊機能 #例# 第一果菜市場',
	`bc_circle` varchar(128) NULL COMMENT '## 熱門商圈 ## 周邊機能 #例# 暫無',
	`bc_medical` varchar(128) NULL COMMENT '## 醫療機構 ## 周邊機能 #例# 暫無',
	`bc_gov` varchar(128) NULL COMMENT '## 政府機構 ## 周邊機能 #例# 暫無',
	`bc_pack` varchar(128) NULL COMMENT '## 其他配套 ## 周邊機能 #例# 暫無',
	`bc_image64` mediumblob NULL COMMENT '',
	`bc_status` bit(1) NOT NULL COMMENT '狀態 ## 0-停用(無法查詢);1-啟用(可以查詢)',
    `bc_similar` varchar(128) NULL COMMENT '##70',/*591*/
    `bc_similaradd` varchar(128) NULL COMMENT '##',/*591*/
    `bc_similarid` int NULL COMMENT '##',/*591*/
    `bc_infomark` tinyint(1) NOT NULL COMMENT '##',/*1-591;2-永慶;3-好房網;4-台灣成家網;5-中信*/
    `bc_imagemark` tinyint(1) NOT NULL COMMENT '##',/*1-591;2-永慶;3-好房網;4-台灣成家網;5-中信*/
	PRIMARY KEY (`bc_id`)
);






單價(萬/坪)
總價(萬)
車位價格(萬)
公開銷售
貸款成數
付款方式
接待會館
建案類別
預期完工
屋齡
總戶數
建築型態
用途規劃
交屋屋況
格局規劃
基地地址
管委會
管理費
充電樁
垃圾處理
公設比
建蔽率
基地面積(坪)
建築構造
土地分區
車位配比
車位規劃
棟戶規劃
樓層規劃
座向規劃
建造執照
使用執照
建材說明
公共設施
投資建設
營造公司
企劃銷售
建築設計
景觀設計
公設設計
燈光設計
特色說明
周邊機能
學區
超商/賣場
熱門商圈
傳統市場
醫療機構
政府機構
公共建設
其他配套
捷運系統
高速公路
快速道路
高鐵系統
台鐵系統
其他方式

(NULL,
'11',
'1105',
'萬象大樓',
'',
'',
5,
0,
2,
'台北市大安區復興南路一段122巷4號',
'25.04398586327836',
'121.5427110848979',
0.00,
,
17.50,
0.00,
0.00,
'1981-01-01',
0,
'["一房(5.57~25.05坪)",
"二房(9.54~40.37坪)",
"三房(23.55~48.68坪)",
"四房(21.05~43.72坪)",
"五房(41.39坪)"]',
'',
'',
'67.5~92.4',
'330~4,
398',
'',
'',
'時間待定',
0.00,
'',
0.00,
'',
,
'1棟',
'地上12層',
'朝北、朝南、朝西、朝西南',
0,
1,
'100元/戶/月',
'RC造',
'市場用地',
'',
'70使字2134號',
'',
'["無公設"]',
0,
'社區統一處理（社區外集中處理）',
'南華營造開發',
'大亞營造',
'',
'今日',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'4',
'',
'',
0,
1,
4);







Array (  
    /*[hid] => 116055 */ 
    /*[service_phone] => 02-55722000  */
    /*[build_type] => m  */
    /*[build_type_str] => a */
    /*bc_unitprice*/ 
    [price] => Array (  price total_price park_price shop_price
        [price] => 145  
        [unit] => 萬/坪 
    )  
    /*bc_totalprice*/
    [total_price] => Array (  
        [price] => a  
        [unit] => g 
    )  
    /*bc_pprice*/
    [park_price] => Array (  
        [price] => 300~400  
        [unit] => g 
    ) 
    /*bc_sprice*/ 
    [shop_price] => Array (  
        [price] => a  
        [unit] => g 
    )  
    /*[community_type] => e  */
    [age] => Array (  /*屋齡*/
        [content] => 2  
        [unit] => 年 
    )  
    /*bc_totalnum*/
    [all_house_num] => Array (  
        [content] => 70  
        [unit] => 戶 
    )  
    /*[sell_status] => 已完銷  */
    /*[deal_date] => a*/
    /*bc_completion*/  
    [deal_time] => 2022年第一季度  
    /*bc_completion*/
    [completion_date] => 2021年  
    /*bc_pratio*/
    [ratio] => 31%  
    /*建蔽率*/
    [jbrate] => 42.78%  
    /*bc_type*/
    [purpose_str] => 住宅大樓  
    /*bc_usefor*/
    [purpose_other2] => 住商用  
    /*bc_structure*/
    [structural_engine] => SRC造  
    /*bc_floor*/
    [floor] => 地上22層,地下5層  
    /*bc_building*/
    [build] => 1幢，1棟，68戶住家，2戶店面，12戶一般事務所  
    /*bc_plan*/
    [layout] => Array (  
        [0] => 三房(90坪) 
    ) 
    /*bc_meetadd*/ 
    [meeting_address] => 中正區羅斯福路三段136號  
    /*[down_pay] => 40%  */
    /*[project_pay] => a */
    /*bc_state*/ 
    [decorate] => 標準配備  
    /*bc_loan*/
    [lend_rate] => 60%  
    /*[base_area] => 734坪*/
    /*bc_area*/  
    [base_area_num] => 734 
    /*bc_zoning*/  
    [land_division] => 第三種商業區（特）  
    /*[park] => p*/
    /*bc_parkratio*/
    [park_rate] => 1:2.35  
    /*bc_direction*/
    [direction_rule] => 朝西南
    /*bc_parknum*/  
    [all_park_num] => 193 
    /*(bc_materials)*/ 
    [build_intro] => 待定
    /*bc_inpublic*/  
    [facility] => Array (  
        [0] => 接待大廳  
        [1] => 中庭花園  
        [2] => 空中花園  
        [3] => 會議室  
        [4] => 閱覽室  
        [5] => 游泳池  
        [6] => 健身房  
        [7] => 廚藝教室  
        [8] => Lounge Bar 
    )  
    /*bc_rmc*/
    [manage_committee] => 有 
    /*bc_pmc*/ 
    [property_company] => a 
    /* bc_mfee */ 
    [manage_cost] => Array (  
        [price] => 130  
        [unit] => 元/坪/月  
        [type] => b 
    )  
    /*bc_saleteam*/
    [sell_company] => 創意家行銷 
    /*bc_investor*/
    [company] => 冠德建設股份有限公司  
    /*bc_constructer*/
    [build_company] => 根基營造股份有限公司  
    /*bc_cdesign*/
    [construction_company] => 黃秀莊建築師事務所  
    /*bc_vdesign*/
    [landscape_name] => a  
    /*bc_pdesign*/
    [postulate_name] => 王勝正  
    /*bc_ldesign*/
    [light_company] => a  
    /*bc_des*/ 
    [build_remark] => 主打訴求：SRC制震宅、40米羅斯福路三段，鄰近台灣大學與台灣師範大學、捷運台電大樓站，都更案。 「冠德羅斯福」位於中正區羅斯福路三段，由冠德建設投資興建，基地面積約733坪、建蔽率42.78％、公設31%，樓層規劃為1棟，68戶住家，2戶店面，12戶一般事務所，格局坪數規劃3房88坪。 車位共規劃有193個平面式車位，休閒設施方面有接待大廳、中庭花園、空中花園、會議室、閱覽室、游泳池、健身房、廚藝教室、Lounge Bar，結構方面採用SRC鋼骨鋼筋混凝土構造，。 周邊環境鄰近師大商圈、公館商圈、古亭商圈，生活採買便利。 交通方面，步行即可至捷運台電大樓站。 
    /*bc_edu*/
    [school] => 古亭國小、螢橋國中、台灣師範大學、台灣大學  
    /*bc_market*/
    [market] => 全聯福利中心  
    /*bc_omarket*/
    [tradition_market] => 龍泉市場  
    /*bc_outpublic*/
    [public_construction] => 客家文化公園  
    /*bc_circle*/
    [hot_business_area] => 師大商圈、公館商圈、古亭商圈  
    /*bc_medical*/
    [hospital] => a  
    /*bc_gov*/
    [government] => a  
    /*bc_pack*/
    [other] => n  
    /*bc_mrt*/
    [subway] => 台電大樓站  
    /*bc_hiway*/
    [freeway] => a  
    /*bc_speedway*/
    [rapid_traffic] => a  
    /*bc_thsr*/
    [speed_railway] => a  
    /*bc_tra*/
    [tw_railway] => a  
    /*bc_othertraffic*/
    [other_traffic] => a  
    /*bc_addr*/
    [address] => 台北市中正區羅斯福路三段136、138號 
    /*bc_pile*/
    [has_piles_str] => a  
    /*bc_trash*/
    [refuse_disposal_str] => 社區統一處理（地下室專區堆置）  
    /*bc_blicense*/
    [license] => 106建字第107號  
    /*bc_ulicense*/
    [use_license] => 110使字第0068號  
    /*bc_parkplan*/
    [park_type_str] => 平面式  
    /*[park_count_str] => p */
    /*bc_lat*/
    [lat] => 25.02059683412547  
    /*bc_lng*/
    [lng] => 121.5280891209239  
    /*bc_title*/
    [comm_title] => 冠德羅斯福 
 )












