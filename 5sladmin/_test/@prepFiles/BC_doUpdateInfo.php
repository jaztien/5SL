<?php
##############################################################
################### 5SeasonLife 後備作業 #########################
################## 以範例 ID 取得並更新資料 ######################
##############################################################
    error_reporting(E_ALL);
    session_start();
    require_once('../includeFiles/db.inc.php');
    require_once('../includeFiles/common.lib.php');
    require_once('../includeFiles/common.lib.php');
	require_once('aprret_func.php');

    $locJS=_fn_getCachedJsonDatac("files/locations.json");
    $bcJS=_fn_getCachedJsonDatac("../jsonFiles/buildcase.json");

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
	
  <title> 人生第五季後備工作--以範例 ID 取得並更新資料 </title>
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
            <div style="font-size:16px;line-height:20px;">
                <h4>人生第五季後備工作--以範例 ID 取得並更新資料 </h4>
                <hr>
                <form id="form_action" name="form_action" action="#" method="post">
                    <input type="hidden" id="doact" name="doact" style="width:100px;" value="1">
                    <button type="submit" class="btn btn-danger">開始截取</button>
                    <button type="button" class="btn btn-warning" onClick="window.location.replace('BC_doUpdateInfo.php');">重置</button>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
                    <button type="button" class="btn btn-secondary" onClick="window.location.replace('index.php');">首頁</button>
                </form>
                <hr>
                
                <?php
                if(empty($doact)){ echo "請按「開始截取」按鍵進行操作！"; exit(); }
                        //$cFileFolder = 'aprretFiles/Original/1130326/'.$cCity;
                        //$Addr = "汀州路三段160巷112之1號";
                        //$Addr = preg_replace("/\d+(?=[號])+號/","",$Addr);
                        //$Addr = preg_replace("/\d+(?=-)/","",$Addr);
                        //echo mb_substr($Addr, -1);
                        //exit();
                        $sql = "SELECT *";
                        $sql .= ", (CASE WHEN bc_coordinates.EnvelopeAngle() > 90 THEN bc_coordinates.ReorientObject().EnvelopeCenter().Long ELSE bc_coordinates.EnvelopeCenter().Long END) AS lng";
                        $sql .= ", (CASE WHEN bc_coordinates.EnvelopeAngle() > 90 THEN bc_coordinates.ReorientObject().EnvelopeCenter().Lat ELSE bc_coordinates.EnvelopeCenter().Lat END) AS lat";
                        $sql .= ", bc_coordinates.STGeometryType() AS coordType";

                        $sql .= " FROM fh.buildcase WHERE bc_infomark=? AND bc_updateinfo=?";
                        //$sql .= " AND bc_id IN (2326,4204,12738)";
                        $sql .= " ORDER BY bc_id ASC LIMIT 2";
                        //$sql .= " LIMIT 0,10";
                        $cTotalRows = 0;
                        $cDoneRows = 0;
                        $cErrorRows = 0;

                        $res = $DB->query($sql, array(3,9));
                        $rowNum = count($res);
                        echo '<table style="border-collapse:collapse;">';
                        foreach($res AS $key => $value){

                        //while($bcrow = mysqli_fetch_assoc($result)){
                            $basePath = "uploadFiles/bc/".$value['bc_city']."/";
                            //$value['bc_city']
                            //$value['bc_district']
                            //$value['bc_title']
                            //$value['bc_subtitle']
                            //$value['bc_addr']
                            //$value['bc_similar']
                            //$value['bc_similaradd']
                            //$value['bc_similarid']
                            //$cStartRow,$value['bc_infomark']
                            //$value['bc_imagemark']
                            //bc_updateinfo
                            //$cStartRow++;
                            //"UPDATE `data_buildcase` SET `bc_published`='2024-03-19 01:23:34', `bc_updated`='2024-03-19 01:23:34', `bc_city`='11', `bc_district`='1101', `bc_title`='延平花園', `bc_subtitle`='', `bc_subtitle2`='', `bc_mode`='6', `bc_type`='0', `bc_usefor`='0', `bc_addr`='臺北市中正區延平南路216巷', `bc_lat`='25.03443450', `bc_lng`='121.50726260', `bc_area`='0', `bc_totalnum`='164', `bc_pratio`='0.00', `bc_coverage`='0.00', `bc_far`='0.00', `bc_completion`='1974-09-01', `bc_state`='0', `bc_plan`='[\"2~4房(室)\",\"12~44坪\"]', `bc_layernum`='2~6戶', `bc_des`='鄰近南門國中、國小，小孩上學好方便，且近植物園，假日休閒好去處，周邊交通便捷', `bc_unitprice`='', `bc_totalprice`='', `bc_pprice`='', `bc_sprice`='', `bc_selling`='', `bc_loan`=''0.00, `bc_meetadd`='', `bc_parkratio`='0.00', `bc_parkplan`='', `bc_parknum`='0', `bc_building`='', `bc_abovemin`='1', `bc_abovemax`='7', `bc_undermin`='1', `bc_undermax`='1', `bc_direction`='', `bc_pmc`='0', `bc_rmc`='0', `bc_mfee`='', `bc_structure`='鋼筋混凝土(RC),加強磚造或其他建材', `bc_zoning`='', `bc_blicense`='', `bc_ulicense`='', `bc_materials`'國泰人壽起造，面延平南路216巷及延平南路230巷為雙併，面延平南路為一層五戶及中華路二段75巷1弄為一層六戶', `bc_inpublic`='一樓門廳、梯廳、樓梯間等', `bc_pile`='0', `bc_trash`='', `bc_investor`'國泰人壽保險股份有限公司', `bc_constructer`='百興營造廠', `bc_saleteam`='', `bc_cdesign`='蔡柏鋒建築師事務所', `bc_vdesign`='', `bc_pdesign`='', `bc_ldesign`='', `bc_mrt`='', `bc_hiway`='', `bc_speedway`='', `bc_thsr`='', `bc_tra`='', `bc_othertraffic`='步行就可到達小南門捷運站，沿重慶南路可以直上國道1號，鄰近水源快速道路，南接到新店北可以通往士林；過中正橋可以通往新北市永和區，過光復橋到新北市板橋區。', `bc_edu`='', `bc_market`='', `bc_omarket`='', `bc_outpublic`='', `bc_circle`='', `bc_medical`='', `bc_gov`='', `bc_pack`='', `bc_image64`='', `bc_status`='4', `bc_similar`='延平花園大廈', `bc_similaradd`='中正區延平南路216巷7號', `bc_similarid`='5901013', `bc_infomark`='3', `bc_imagemark`='0', `bc_updateinfo`='1' WHERE `bc_id`='33';"

                            //進入591網站獲取詳細資料
                            $rtn = _fn_wejoin_infoByID($value['bc_similarid']);
                            //當591網站獲取詳細資料失敗，更新「更新狀態」為[9]
                            if(!is_array($rtn) || count($rtn)==0){
                                ////$upError = mysqli_query($link, "UPDATE `data_buildcase` SET `bc_updateinfo`='9' WHERE `bc_id`='".$value['bc_id']."'");//[9]為截取資料失敗
                                //$upError = $DB->update('fh.buildcase',array('bc_updateinfo'=>9),array("bc_id"=>$value['bc_id']));//[9]為截取資料失敗
                                echo "<span style='color:purple;'>ERROR @@ ".$value['bc_id']." @@ ".$value['bc_similar']." @@ 資料擷取失敗！！</span><br>";
                                echo "<br>";
                                $cErrorRows++;
                                $cTotalRows++;
                                continue;
                            }
                            //比較兩個經緯度間的距離，小於30公尺者視為相同地點。
                /*
                            if($value['bc_lat']!="" && $value['bc_lng']!="" && $getDistance > 100){
                                $upError = mysqli_query($link, "UPDATE `data_buildcase` SET `bc_updateinfo`='8', `bc_distance`='".$getDistance."' WHERE `bc_id`='".$value['bc_id']."'");//[7]為兩點距離過遠30m終止操作[8]為兩點距離過遠100m終止操作
                                echo "<span style='color:green;'>ERROR @@ ".$value['bc_id']." @@ ".$value['bc_similar']." @@ 兩點距離 ".$getDistance." 公尺，超過最小限制距離！！</span><br>";
                                $cErrorRows++;
                                $cTotalRows++;
                                continue;
                            } elseif($value['bc_lat']=="" || $value['bc_lng']==""){
                                $getDistance = 0;
                            }
                */

                            $upSQL = "UPDATE fh.buildcase SET ";// ".$rtn['XXXXX']."
                            //$upSQL .= "bc_published='2024-03-19 01:23:34', ";
                            $upSQL .= "bc_updated = '[EXPR]GETDATE()', ";
                            //$upSQL .= "bc_city='11', ";
                            //$upSQL .= "bc_district='1101', ";
                            //$upSQL .= "bc_title='延平花園', ";
                            $upSQL .= "bc_subtitle='".$value['bc_similar']."', ";
                            $upSQL .= "bc_mode='".$bcJS['rpurpose'][$rtn['purpose_str']]."', ";//'建案型態 ## 1-公寓(5樓含以下無電梯);2-透天厝;3-店面(店鋪);4-辦公商業大樓;5-住宅大樓(11層含以上有電梯);6-華廈(10層含以下有電梯);7-套房(1房1廳1衛);8-工廠;9-廠辦;10-農舍;11-倉庫;'
                            $upSQL .= "bc_type=0, ";                        //'建案類別 ## 0-老舊建案;1-新建案;2-預售屋(未完工)
                            $upSQL .= "bc_usefor='".$bcJS['rbcmode'][$rtn['purpose_str']]."', ";//'建案型態 ## 1-公寓(5樓含以下無電梯);2-透天厝;3-店面(店鋪);4-辦公商業大樓;5-住宅大樓(11層含以上有電梯);6-華廈(10層含以下有電梯);7-套房(1房1廳1衛);8-工廠;9-廠辦;10-農舍;11-倉庫;'
                            $upSQL .= "bc_usefor='".$bcJS['rpurpose'][$rtn['purpose_other2']]."', "; //'用途規劃 ## 1-住家用;2-商業用;3-工業用;4-農業用;5-住商用; 6-住工用; 7-工商用; 8-停車空間; 9-見使用執照;0-其他'
                            $upSQL .= "bc_addr='".$rtn['address']."', ";    //基地地址
                            if(empty($value['bc_lat']) && !empty($rtn['lat']) && empty($value['bc_lng']) && !empty($rtn['lng'])){
                                $upSQL .= "bc_lat='".$rtn['lat']."', ";
                                $upSQL .= "bc_lng='".$rtn['lng']."', ";
                                $upSQL .= "bc_coordinates = geography::STGeomFromText('POINT(".$rtn['lng']." ".$rtn['lat'].")'), 4326), ";
                            }
                            if(!empty($rtn['base_area_num'])) {
                                $setAreanum = number_format((float)str_replace(',','',$rtn['base_area_num']), 2, '.', '');
                                $upSQL .= "bc_area=".$setAreanum.", ";                        //基地面積
                            }
                            if(!empty($rtn['all_house_num']['content'])) {
                                $setHousenum = intval(@$rtn['all_house_num']['content']);//總戶數
                                $upSQL .= "bc_totalnum=".$setHousenum.", ";  //總戶數
                            }
                            if(!empty($rtn['ratio'])) {
                                $setRatio = number_format((float)str_replace('%','',@$rtn['ratio']), 2, '.', '');//公設比
                                $upSQL .= "bc_pratio=".$setRatio.", ";    //公設比
                            }
                            if(!empty($rtn['jbrate'])) {
                                $setJbrate = number_format((float)str_replace('%','',@$rtn['jbrate']), 2, '.', '');//建蔽率
                                $upSQL .= "bc_coverage=".$setJbrate.", ";//建蔽率
                            }
                            $upSQL .= "bc_far=0.00, ";                      //容積率 FIXME: 
                            if(!empty($rtn['completion_date'])) {
                                $setCompletion = !empty($rtn['age']['content']) ? (date('Y') - $rtn['age']['content'] - 1)."-01-01" : null;
                                $upSQL .= "bc_completion='".$setCompletion."', ";         //交屋時間
                            }
                            $upSQL .= "bc_state='".$bcJS['rdecorate'][$rtn['decorate']]."', ";
                            if(!empty($rtn['layout'])) {
                                $setLayOut = _fn_str_arr2str($rtn['layout']);
                                $upSQL .= "bc_plan='".$setLayOut."', ";  //格局規劃 // ".$rtn['XXXXX']."
                            }
                            //$upSQL .= "bc_layernum='2~6戶', ";                 //每層戶數
                            if(!empty($rtn['build_remark'])) $upSQL .= "bc_des='".strip_tags($rtn['build_remark'])."', ";  //特色說明
                            if(!empty($rtn['price']['price'])) $upSQL .= "bc_unitprice='".$rtn['price']['price']."', "; //建案單價
                            if(!empty($rtn['total_price']['price'])) $upSQL .= "bc_totalprice='".$rtn['total_price']['price']."', "; //建案總價
                            if(!empty($rtn['park_price']['price'])) $upSQL .= "bc_pprice='".$rtn['park_price']['price']."', "; //車位價格
                            if(!empty($rtn['shop_price']['price'])) $upSQL .= "bc_sprice='".$rtn['shop_price']['price']."', "; //商鋪價格
                            if(!empty($rtn['sell_status'])) $upSQL .= "bc_selling='".$rtn['sell_status']."', "; //公開銷售 ## 0-無;1-銷售中'
                            if(!empty($rtn['lend_rate'])) {
                                $setLendrate = number_format((float)str_replace('%','',$rtn['lend_rate']), 2, '.', '');//貸款成數 
                                $upSQL .= "bc_loan=".$setLendrate.", "; //貸款成數
                            }
                            if(!empty($rtn['meeting_address'])) $upSQL .= "bc_meetadd='".$rtn['meeting_address']."', "; //接待會館地址
                            if(!empty($rtn['park_rate'])) {
                                $setParkrate = number_format(substr(strrchr($rtn['park_rate'],':'),1), 2, '.', '');//車位配比//##30
                                $upSQL .= "bc_parkratio=".$setParkrate.", "; //車位配比
                            }
                            if(!empty($rtn['park_type_str'])) $upSQL .= "bc_parkplan='".$rtn['park_type_str']."', "; //車位規劃 park_type_str
                            if(!empty($rtn['all_park_num'])) {
                                $setallparknum = intval(str_replace(['，', ','],'',$rtn['all_park_num']));
                                $upSQL .= "bc_parknum=".$setallparknum.", "; //車位總數量
                            }
                            if(!empty($rtn['build'])) $upSQL .= "bc_building='".$rtn['build']."', "; //棟戶規劃 build
                            if(!empty($rtn['floor'])) {
                                $setFloor['aboveMin'] = 0;
                                $setFloor['aboveMax'] = 0;
                                $setFloor['underMin'] = 0;
                                $setFloor['underMax'] = 0;
                                if(!empty($rtn['floor'])){
                                    $getFloor = str_replace(['地上','地下','層',' '],'',$rtn['floor']);
                                    $setFloorSplit = explode(',',$getFloor);
                                    if(isset($setFloorSplit[0])&&!empty($setFloorSplit[0])){
                                        $aboveSplit = explode('~',$setFloorSplit[0]);
                                        $setFloor['aboveMin'] = (isset($aboveSplit[0])&&!empty($aboveSplit[0])) ? intval($aboveSplit[0]) : 0;
                                        $setFloor['aboveMax'] = (isset($aboveSplit[1])&&!empty($aboveSplit[1])) ? intval($aboveSplit[1]) : $setFloor['aboveMin'];
                                    }
                                    if(isset($setFloorSplit[1])&&!empty($setFloorSplit[1])){
                                        $underSplit = explode('~',$setFloorSplit[1]);
                                        $setFloor['underMin'] = (isset($underSplit[0])&&!empty($underSplit[0])) ? intval($underSplit[0]) : 0;
                                        $setFloor['underMax'] = (isset($underSplit[1])&&!empty($underSplit[1])) ? intval($underSplit[1]) : $setFloor['underMin'];
                                    }
                                }
                                $upSQL .= "bc_abovemin='".$setFloor['aboveMin']."', ";                        //樓層規劃
                                $upSQL .= "bc_abovemax='".$setFloor['aboveMax']."', ";                        //樓層規劃
                                $upSQL .= "bc_undermin='".$setFloor['underMin']."', ";                        //樓層規劃
                                $upSQL .= "bc_undermax='".$setFloor['underMax']."', ";                        //樓層規劃
                            }
                            if(!empty($rtn['direction_rule'])) $upSQL .= "bc_direction='".$rtn['direction_rule']."', "; //## 座向規劃 ## 社區規劃 #例# 朝北、朝南、朝西、朝東
                            $setProperty = ($rtn['property_company']=='有' ? 1 : 0);//物業公司
                            $upSQL .= "bc_pmc=".$setProperty.", ";   //## 物業公司 ## 社區規劃 #例# 0-無;1-有
                            $setCommit = ($rtn['manage_committee']=='有' ? 1 : 0);//管委會
                            $upSQL .= "bc_rmc=".$setCommit.", ";                             //## 管委會 ## 社區規劃 #例# 0-無;1-已成立
                            $setManagecost = $rtn['manage_cost']['price'].$rtn['manage_cost']['unit'];//管理費用//
                            $upSQL .= "bc_mfee='".$setManagecost."', ";                             //## 管理費用 ## 社區規劃 #例# 120元/坪/月
                            if(!empty($rtn['structural_engine'])) $upSQL .= "bc_structure='".$rtn['structural_engine']."', ";   //## 結構工程 ## 1-木構;2-磚構;3-石構;4-鋼筋混凝土(RC);5-鋼骨構造(SC);6-鋼骨鋼筋混凝土(SRC);7-塑膠構造;8-橡皮構造
                            if(!empty($rtn['land_division'])) $upSQL .= "bc_zoning='".$rtn['land_division']."', ";                           //## 土地分區 ## 社區規劃 #例# 第三種住宅區
                            if(!empty($rtn['license'])) $upSQL .= "bc_blicense='".$rtn['license']."', ";                         //## 建造執照 ## 社區規劃 #例# 109建字第0221號
                            if(!empty($rtn['use_license'])) $upSQL .= "bc_ulicense='".$rtn['use_license']."', ";                         //## 使用執照 ## 社區規劃 #例# 111使字第0129號
                            if(!empty($rtn['build_intro'])) $upSQL .= "bc_materials='".strip_tags($rtn['build_intro'])."', ";               //## 建材說明 ## 社區規劃 #例# 結構體保固20年、防水保固5年、固定設備保固2年，社區搭配OTIS電梯，Panasonic廚具，KOHLER衛浴瓷器、Hansgrohe冷熱水龍頭，
                            if(!empty($rtn['facility'])) {
                                $setFacility = _fn_str_arr2str($rtn['facility']);//內部公共設施
                                $upSQL .= "bc_inpublic='".$setFacility."', ";     //## 公共設施 ## 社區規劃 #例# 接待大廳交誼廳兒童遊戲區
                            }
                            $setPiles = ($rtn['has_piles_str']=='有' ? 1 : 0);//充電樁 
                            $upSQL .= "bc_pile=".$setPiles.", ";                //## 充電樁
                            if(!empty($rtn['refuse_disposal_str'])) $upSQL .= "bc_trash='".$rtn['refuse_disposal_str']."', ";                            //## 垃圾處理
                            if(!empty($rtn['company'])) $upSQL .= "bc_investor='".$rtn['company']."', ";      //## 投資建設 ## 建築設計 #例# 樂揚建設股份有限公司
                            if(!empty($rtn['build_company'])) $upSQL .= "bc_constructer='".$rtn['build_company']."', ";              //## 營造公司 ## 建築設計 #例# 順緯營造股份有限公司
                            if(!empty($rtn['sell_company'])) $upSQL .= "bc_saleteam='".$rtn['sell_company']."', ";                         //## 企劃銷售 ## 建築設計 #### 0-自售;1-託售
                            if(!empty($rtn['construction_company'])) $upSQL .= "bc_cdesign='".$rtn['construction_company']."', ";           //## 建築設計 ## 建築設計 #例# 昌瑜建築師事務所
                            if(!empty($rtn['landscape_name'])) $upSQL .= "bc_vdesign='".$rtn['landscape_name']."', ";                          //## 景觀設計 ## 建築設計 #例# 紅谷花苑
                            if(!empty($rtn['postulate_name'])) $upSQL .= "bc_pdesign='".$rtn['postulate_name']."', ";                          //## 公設設計 ## 建築設計 #例# 左越設計/晴璞設計
                            if(!empty($rtn['light_company'])) $upSQL .= "bc_ldesign='".$rtn['light_company']."', ";                          //## 燈光設計 ## 建築設計 #例# 無
                            if(!empty($rtn['subway'])) $upSQL .= "bc_mrt='".strip_tags($rtn['subway'])."', ";                              //## 捷運系統 ## 交通出行 #例# 萬大線加蚋站
                            if(!empty($rtn['freeway'])) $upSQL .= "bc_hiway='".strip_tags($rtn['freeway'])."', ";                            //## 高速公路 ## 交通出行 #例# 暫無
                            if(!empty($rtn['rapid_traffic'])) $upSQL .= "bc_speedway='".strip_tags($rtn['rapid_traffic'])."', ";                         //## 快速道路 ## 交通出行 #例# 水源快速道路
                            if(!empty($rtn['speed_railway'])) $upSQL .= "bc_thsr='".strip_tags($rtn['speed_railway'])."', ";                             //## 高鐵系統 ## 交通出行 #例# 暫無
                            if(!empty($rtn['tw_railway'])) $upSQL .= "bc_tra='".strip_tags($rtn['tw_railway'])."', ";                              //## 臺鐵系統 ## 交通出行 #例# 萬華車站
                            if(!empty($rtn['other_traffic'])) $upSQL .= "bc_othertraffic='".strip_tags($rtn['other_traffic'])."', ";               //## 其他方式 ## 交通出行 #例# 暫無
                            if(!empty($rtn['school'])) $upSQL .= "bc_edu='".strip_tags($rtn['school'])."', ";                              //## 學區 ## 周邊機能 #例# 萬大國小、萬華國中
                            if(!empty($rtn['market'])) $upSQL .= "bc_market='".strip_tags($rtn['market'])."', ";                           //## 超商/賣場 ## 周邊機能 #例# 全聯福利中心
                            if(!empty($rtn['tradition_market'])) $upSQL .= "bc_omarket='".strip_tags($rtn['tradition_market'])."', ";                          //## 傳統市場 ## 周邊機能 #例# 東園市場ZZ
                            if(!empty($rtn['public_construction'])) $upSQL .= "bc_outpublic='".strip_tags($rtn['public_construction'])."', ";                        //## 公共建設 ## 周邊機能 #例# 第一果菜市場
                            if(!empty($rtn['hot_business_area'])) $upSQL .= "bc_circle='".strip_tags($rtn['hot_business_area'])."', ";                           //## 熱門商圈 ## 周邊機能 #例# 暫無
                            if(!empty($rtn['hospital'])) $upSQL .= "bc_medical='".strip_tags($rtn['hospital'])."', ";                          //## 醫療機構 ## 周邊機能 #例# 暫無
                            if(!empty($rtn['government'])) $upSQL .= "bc_gov='".strip_tags($rtn['government'])."', ";                              //## 政府機構 ## 周邊機能 #例# 暫無
                            if(!empty($rtn['other'])) $upSQL .= "bc_pack='".strip_tags($rtn['other'])."', ";                             //## 其他配套 ## 周邊機能 #例# 暫無
                            //$upSQL .= "bc_image64='', ";                        //圖檔碼
                            $upSQL .= "bc_status=3, ";                          //狀態 見 buildcase.json「 bcstatus 」
                            //$upSQL .= "bc_similar='".$value['bc_title']."', ";    //近似課程商品
                            //$upSQL .= "bc_similaradd='".$value['bc_addr']."', ";    //近似課程商品住址
                            //$upSQL .= "bc_similarid='".$value['bc_similarid']."', ";                 //相應591ID
                            $upSQL .= "bc_infomark=1, ";                        //原資料來源 見 buildcase.json「 cocomp 」
                            $getImageMark = $value['bc_imagemark'];
                            if(!empty($rtn['comm_photo']) && ($getImageMark==0 || $getImageMark==3)) {
                                if(_fn_url_ifexists($rtn['comm_photo'])){
                                    if($rtn['comm_photo']!='s.591.com.tw/v4/list-pc-default.DKi8CdeN.png'){//非暫無圖片檔案
                                        $setImageUrl = $rtn['comm_photo'];
                                        $setIMGname = $basePath.'bcimg_'.$value['bc_id'].'_default.jpg';
                                        $setImage = _fn_load_contents($setImageUrl,true);
                                        if(!empty($setImage) && file_put_contents($setIMGname, $setImage)){
                                            $getImageMark = 1;
                                        }
                                    }
                                } 
                            }
                            $upSQL .= "bc_imagemark=".$getImageMark.", ";       //原圖像來源
                            $upSQL .= "bc_updateinfo=6, ";                        //內容增替註記 見 buildcase.json「 bcupinfo 」
                            $getDistance = _fn_getdistance($value['bc_lat'],$value['bc_lng'],$rtn['lat'],$rtn['lng']);
                            $upSQL .= "bc_distance=".$getDistance;                        //兩座標點距離
                            $upSQL .= " WHERE bc_id=".$value['bc_id'];
                            $up = 0;
                            //$up = $DB->query($upSQL, array());
                            if($up){
                                $cDoneRows++;
                            } else {
                                //輸出資料庫操作代碼，以利偵錯
                                echo "<span style='color:red;'>ERROR @@ ".$value['bc_id']." @@ ".$value['bc_similar']." @@ 寫入資料失敗！！</span><br>";
                                echo "<br>";
                                echo $upSQL;
                                echo "<br><br>";
                                $cTotalRows++;       
                                $cErrorRows++;
                                continue;
                            }
                            $cTotalRows++;       
                            //echo $cFileName.'__'.$cTotalRows.' Rows <br>';
                        }
                        echo '</table>';

                        echo '<hr>';
                        echo 'Total Rows: '.$cDoneRows.' / '.$cTotalRows;
                        echo '<hr>';
                        echo 'Error Rows: '.$cErrorRows;
                        echo '<br>';
                        echo '<br>';
                        echo '<br>';
                        echo '<br>';
                ?>
            </div>
        </div>
    </div>
</body>
</html>

