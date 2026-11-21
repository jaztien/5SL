<?php
//UPDATE buildcase SET bc_structure = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(bc_structure,'"T"','1'),'"M"','2'),'"D"','3'),'"B"','4'),'"RC"','5'),'"W"','6'),'"RB"','7'),'"SC"','8'),'"SRC"','9'),'"PSC"','10'),'"OT"','11')
//UPDATE buildcase SET bc_plan =REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(bc_plan,'["',''),'"]',''),'","',','),'[]',''),'--坪','')
//UPDATE buildcase SET bc_layernum=REPLACE(bc_layernum,'戶','')
//UPDATE buildcase SET bc_mfee=REPLACE(REPLACE(bc_mfee,'元/戶/月',''),'無管理費','0')
//UPDATE buildcase SET bc_mfee=CONCAT(bc_mfee, '元/戶/月') WHERE bc_mfee NOT LIKE '%元/坪/月%' AND bc_mfee <> ''
//UPDATE buildcase SET bc_mfee='0元/戶/月') WHERE bc_mfee='0'
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_BCASE');
define('PAGE_ITEM','ITEM_TOOLS');
define('PAGE_ITEM2','ITEM_DATAS');
define('SEARCH_OPANEL',1);
define('bkRedir',1);
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//主要資料表欄位資料取得
$bcID=currID;
$tabIDX = !empty($_REQUEST['tabIDX'])?intval($_REQUEST['tabIDX']) : 1;
//
//$jumpData = isset($_REQUEST['jumpData']) ? intval($_REQUEST['jumpData']) : '';

$backUrl =  explode('pageMax',$_SERVER['REQUEST_URI']);
$backUrl = "pageMax".$backUrl[1];
//merch_coordinates.STDistance(geography::STGeomFromText('Point(".$searchLNG." ".$searchLAT.")', 4326)) AS disTance
$sql = "SELECT *";
$sql .= ", (CASE WHEN bc_coordinates.EnvelopeAngle() > 90 THEN bc_coordinates.ReorientObject().EnvelopeCenter().Long ELSE bc_coordinates.EnvelopeCenter().Long END) AS lng";
$sql .= ", (CASE WHEN bc_coordinates.EnvelopeAngle() > 90 THEN bc_coordinates.ReorientObject().EnvelopeCenter().Lat ELSE bc_coordinates.EnvelopeCenter().Lat END) AS lat";
$sql .= ", bc_coordinates.STGeometryType() AS coordType";
//$sql .= ", bc_coordinates.STDistance(geography::STGeomFromText('Point(121.51161817528575 25.0500639221563)', 4326)) AS disTance";
$sql .= ", bc_coordinates.ToString() AS coorDinates";
$sql .= " FROM buildcase WHERE bc_id=?";
$res = $DB->row($sql,array($bcID));
$cityCode = isset($res['bc_city']) ? $res['bc_city'] : '';
$distCode = isset($res['bc_district']) ? $res['bc_district'] : '';
//將 Geography 欄位值轉換為顯示於 textarea 元件內的字串，以便於更邏輯化的座標輸入處理
$coordType = !empty($res['coordType']) ? strtoupper($res['coordType']) : '';
$coorDinates = '';
echo $res['disTance'];
if(!empty($res['coorDinates'])){
  if($coordType == 'POINT'){
    $coordString = str_replace('POINT (', '', $res['coorDinates']);
    $coordString = str_replace(')', '', $coordString);
    $coordString = str_replace(', ', ',', $coordString);
    $coordArray = explode(',', $coordString);
    foreach($coordArray as $key => $val){
      $pointArray = explode(' ', $val);
      $coorDinates .= $pointArray[1].','.$pointArray[0].'&#10;';
    }
    $coorDinates = substr($coorDinates, 0, -5);
  } else if($coordType == 'LINESTRING' || $coordType == 'POLYGON'){
    $coordString = str_replace($coordType.' ((', '', $res['coorDinates']);
    $coordString = str_replace('))', '', $coordString);
    $coordString = str_replace(', ', ',', $coordString);
    $coordArray = explode(',', $coordString);
    foreach($coordArray AS $key => $val){
      $pointArray = explode(' ', $val);
      $coorDinates .= $pointArray[1].','.$pointArray[0].'&#10;';
    }
    $coorDinates = substr($coorDinates, 0, -5);
  } else if($coordType == 'MULTIPOLYGON'){
    $coordString = str_replace('MULTIPOLYGON (((', '', $res['coorDinates']);
    $coordString = str_replace(')))', '', $coordString);
    $coordString = str_replace(', ', ',', $coordString);
    $polyArray = explode(')),((', $coordString);
    foreach($polyArray AS $pkey => $pval){
      $coordArray = explode(',', $pval);
      foreach($coordArray AS $key => $val){
        $pointArray = explode(' ', $val);
        $coorDinates .= $pointArray[1].','.$pointArray[0].'&#10;';
      }
      $coorDinates .= '+&#10;';
    }
    $coorDinates = substr($coorDinates, 0, -11);
  }

}
//功能陣列建立，取自JSON檔案
$adJS=_fn_getCachedJsonData("../jsonFiles/administrator.json");
$geJS=_fn_getCachedJsonData("../jsonFiles/general.json");
$merchJS=_fn_getCachedJsonData("../jsonFiles/merchandise.json");
$locJS=_fn_getCachedJsonData("../jsonFiles/locations.json");//若要給JAVA使用，必須去除 whitespace
$bcJS=_fn_getCachedJsonData("../jsonFiles/buildcase.json");

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../../imageFiles/favicon.ico" type="image/ico" />
	
  <title> Fifth Season Life 人生第五季 | 社區建案資料編輯 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <link rel="stylesheet" href="../pluginFiles/font-gis/css/font-gis.css" />
  <!-- Select2 -->
  <link rel="stylesheet" href="../pluginFiles/select2/css/select2.min.css">
  <link rel="stylesheet" href="../pluginFiles/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Leaflet Map -->
  <link rel="stylesheet" href="../pluginFiles/leaflet/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css<?php echo '?t='.time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.lightbox.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css<?php echo '?t='.time(); ?>">
<!-- -------------------------- /CSS Link -------------------------- -->
 <style>
    #map { height: 240px; }
</style>
</head>

  <!-- ----- nXXXXXX ---------------------------------------------- -->
  <!-- ----- layout-navbar-fixed layout-footer-fixed 上下導覽條固定不動 ------------------ -->
  <!-- ----- layout-fixed 左側導覽固定不動（但上下捲動時LOGO固定不動，畫面不佳） -------------- -->
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <!-- Light BOX -->
  <div id="boxModal" class="lightBoxFrame">
    <span class="close">&times;</span>
    <img class="imageViewURL" id="imageViewer">
    <div id="imageViewTXT"></div>
  </div>
  <!-- /Light BOX -->
  <!-- 全頁框 -->
  <div class="wrapper">
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="../imageFiles/preloaderLOGO.png" alt="Fifth Season Life LOGO" height="60" width="60"> 
    </div>
    <!-- /Preloader -->
    <!-- 上方訊息區 -->
    <?php //include_once('include_navbar.php'); ?>
    <!-- /上方訊息區 -->
    <!-- 左側方導覽區 -->
    <?php //include_once('include_sidebarL.php'); ?>
    <!-- /左側方導覽區 -->
    <!-- 頁內全頁內容 -->
    <div class="content-wrapper">
      <!-- 頁頭導覽條 -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-7">
              <h4 class="m-0">社區建案資料編輯【ID: <?php echo isset($res['bc_id']) ? $res['bc_id'] : '<span style="font-size:0.5em;">新增成功後自動生成</span>'; ?>】</h4>
              <div class="text-muted text-sm" style="margin-top:-6px;">data_bcedit.php</div>
            </div>
            <div class="col-sm-5">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">基礎數據&管理工具</li>
                <li class="breadcrumb-item">基礎結構數據</li>
                <li class="breadcrumb-item prevLink" alt="返回列表" title="返回列表">社區建案列表</li>
                <li class="breadcrumb-item active">社區建案資料編輯</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <!-- /頁頭導覽條 -->
      <!-- 主要內容區域 ------------------------------------- -->
      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-3">
              <!-- Profile Image $res['merch_defaultphoto'] -->
              <div class="card card-primary card-outline shadow">
                <div class="card-body box-profile">
                  <div class="text-center">
                    <div class="image_area">
                        <img id="defaultPreview" name="defaultPreview" class="img-responsive imgEDicon" width="100%" src="<?php
                        $defaultPhoto = "../uploadFiles/bc/".$cityCode."/".$distCode."/bcimg_".@$res['bc_id']."_".@$res['bc_defaultphoto']."_.jpg";
                        if(file_exists($defaultPhoto)){
                          echo $defaultPhoto."?".time();
                        } else {
                          echo "../imageFiles/nopic_400x300.jpg";
                        }
                        ?>" alt="社區建案圖像" title="社區建案圖像"/>
                    </div>
                  </div>
                  <h3 class="profile-title text-center" style="margin-top:8px;"><?php echo @$res['bc_title'] ?: ''; ?></h3>
                  <p class="profile-mdtype text-muted text-center"><?php //echo $merchJS['mdtype'][$res['merch_mdtype']]; ?></p>
                  <p class="profile-bdtype text-muted text-center"><?php //echo $merchJS['bdtype'][$res['merch_bdtype']]; ?></p>

                  <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                      <b>狀態</b> <a class="profile-status float-right<?php echo (@$res['bc_status']==1) ? "" : " text-danger"; ?>"><?php echo isset($res['bc_status']) ? $bcJS['bcstatus'][$res['bc_status']] : ''; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>編輯日期</b> <a class="profile-updated float-right"><?php echo @$res['bc_updated'] ?: ''; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>建立日期</b> <a class="float-right" style="color:black;"><b><?php echo @$res['bc_published'] ?: ''; ?></b></a>
                    </li>
                  </ul>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->

              <div class="card card-primary card-outline shadow">
                <div class="card-body box-profile row fromWhere" onclick="
                  window.location.replace('ad_profile.php?currID=<?php echo CF_AD_ID; ?>');
                    /*
                    var daTa = {
                      'backPG': 'merch_info.php',
                      'mbID': '<?php echo @$res['bc_id']; ?>',
                      'bkTag' : 'mdID',
                      'bkId' : '<?php echo $bcID; ?>',
                      'bkTab' : '1'
                    };
                    postData('mem_profile.php',daTa);
                    */
                    " alt="進入登錄者資料頁" title="進入登錄者資料頁">
                  <div class="col-sm-5">
                    <div class="image_area text-left">
                        <label for="profilePhoto" style="padding-left:8px;">
                          <img id="profilePhoto" name="profilePhoto" class="img-responsive profile-user-img img-fluid img-circle imgEDicon" style="width:160px;" src="<?php
                          $defFilePath = "../uploadFiles/ad/adphoto_".CF_AD_ID."_";
                          if(file_exists($defFilePath.".jpg")) {
                            echo $defFilePath.".jpg?".time();
                          } else echo "../imageFiles/nopic_user_200x200.png";
                          ?>" alt="登錄者圖像" title="登錄者圖像"/>
                        </label>
                    </div>
                  </div>
                  <div class="col-sm-7">
                    <p>登錄者：</p>
                    <hr>
                    <h4 class="profile-username"><?php echo CF_AD_NICK; ?></h4>
                    <p class="text-muted"><?php echo $adJS['authlevel'][CF_AD_LEVEL]?:''; ?></p>
                  </div>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
              <div class="card card-danger card-outline">
                <div class="card-header">
                  <h3 class="card-title">地圖顯示</h3>
                  <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body box-profile">
                  <div id="map"></div>
                </div>
              <!-- /.card -->
              </div>


              <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">社區建案資料完善操作</h3>
                    <div class="card-tools">
                      <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                      </button>
                      <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                      </button>
                    </div>
                </div>
                <div class="card-body box-profile">
                  <div class="form-group row">
                    <div class="col-sm-6">
                        <a id="but_getfrominfo" class="btn btn-app bg-success" style="width:100%;height:72px;font-size:1em;border-radius: 0.5rem;">
                          <i class="nav-icon fas fa-2x fa-cloud-download-alt"></i> 資料內容搜集
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <a id="but_addrformat" class="btn btn-app bg-danger" style="width:100%;height:72px;font-size:1em;border-radius: 0.5rem;">
                          <i class="nav-icon fas fa-2x fa-house-user"></i> 地址格式校正
                        </a>
                    </div>
                  </div>
                  <div class="form-group row">
                    <div class="col-sm-6">
                      <a id="but_dupfilter" class="btn btn-app bg-warning" style="width:100%;height:72px;font-size:1em;border-radius: 0.5rem;">
                        <i class="nav-icon fas fa-2x fa-layer-group"></i> 重複資料篩除
                      </a>
                    </div>
                    <div class="col-sm-6">
                      <a id="but_getgeoinfo" class="btn btn-app bg-info" style="width:100%;height:72px;font-size:1em;border-radius: 0.5rem;">
                        <i class="nav-icon fas fa-2x fa-globe-americas"></i> 取得座標定位
                      </a>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->
            <div class="col-md-9">
              <div class="card card-secondary">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link<?php if($tabIDX==1) echo " active"; ?>" href="#tabMenu1" data-index="1" data-toggle="tab" data-topbut="1">社區建案基本訊息</a></li>
                    <?php if(!empty($bcID)){ ?>
                    <li class="nav-item"><a class="nav-link<?php if($tabIDX==2) echo " active"; ?>" href="#tabMenu2" data-index="2" data-toggle="tab" data-topbut="1">社區建案圖像管理</a></li>
                    <li class="nav-item"><a class="nav-link<?php if($tabIDX==3) echo " active"; ?>" href="#tabMenu3" data-index="3" data-toggle="tab" data-topbut="0">地圖與街景</a></li>
                    <?php } ?>
                    <li class="nav-item"><a class="nav-link<?php if($tabIDX==4) echo " active"; ?>" href="#tabMenu4" data-index="4" data-toggle="tab" data-topbut="0">座標填寫說明</a></li>
                  </ul>
                </div>
                <!-- /.card-header -->
                <!-- ///////////////////////////////////////////////////////////////////////////////////// -->
                <!-- /////////////////////////////////// 功能分頁  //////////////////////////////////////// -->
                <!-- ///////////////////////////////////////////////////////////////////////////////////// -->
                <div class="card-body" style="border:1px solid #5F6E86;">
                  <div class="tab-content">
                    <!-- 第1分頁管理者資料修改 -->
                    <div class="tab-pane<?php if($tabIDX==1) echo " active"; ?>" id="tabMenu1">
                      <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/bc_act_func.php" method="post" data-formmode="infoupdate" data-func="func_bc_infoUpdate">
                        <div class="card-body">

                          <hr class="grouphr">
                          <div class="groupfield">建案基本資料</div>
                          
                          <div class="form-group row">
                          <label for="bc_title" class="col-sm-2 col-form-label">建案完整名稱</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_title" name="bc_title" value="<?php echo @$res['bc_title']; ?>" placeholder="建案名稱" data-min="2" data-required<?php echo NreadOnly; ?>>
                            </div>
                            <label for="bc_mode" class="col-sm-2 col-form-label">建案型態</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="bc_mode" name="bc_mode" placeholder="建案型態" data-required<?php echo NdisAble; ?>>
                                        <?php
                                            foreach($bcJS['bcmode'] as $key => $value){
                                                echo '<option value="'.$key.'"';
                                                if(!empty($bcID) && $key==""){ echo " disabled"; }
                                                if(empty($bcID)){
                                                  if($key=='') { echo " selected"; }
                                                } else {
                                                  if($key==@$res['bc_mode']) { echo " selected"; }
                                                }
                                                echo '>'.$value.'</option>';
                                            }
                                        ?>
                                </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_subtitle" class="col-sm-2 col-form-label">第一別稱</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_subtitle" name="bc_subtitle" value="<?php echo @$res['bc_subtitle']; ?>" placeholder="第一別稱"<?php echo NreadOnly; ?>>
                            </div>
                            <label for="bc_subtitle2" class="col-sm-2 col-form-label">第二別稱</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_subtitle2" name="bc_subtitle2" value="<?php echo @$res['bc_subtitle2']; ?>" placeholder="第二別稱"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_city" class="col-sm-2 col-form-label">城市區域</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="bc_city" name="bc_city" placeholder="所在城市" data-placeholder="請選擇所在城市" data-select="city" data-required<?php echo NdisAble; ?>>
                              <option value=""<?php echo !empty($bcID) ? ' disabled' : ' selected'; ?>>-請選擇所在城市-</option>
                              <?php
                              foreach($locJS as $key => $value){
                                echo "<option value='".$value['value']."'";
                                if(@$res['bc_city']==$value['value']) { echo " selected"; }
                                echo ">".$value['text']."</option>";
                              }
                              ?>
                              </select>
                            </div>
                            
                            <div class="col-sm-6">
                              <select class="form-control" id="bc_district" name="bc_district" placeholder="行政區劃" data-placeholder="請選擇行政區劃" dist-fromcity="bc_city" style="width: 100%;" data-required<?php echo NdisAble; ?>>
                              <option value=''>請選擇行政劃區</option>
                              <?php
                              if(!empty($res['bc_city'])) {
                                foreach($locJS[$res['bc_city']]['district'] as $key => $value){
                                  echo "<option value='".$key."'";
                                  if($res['bc_district']==$key) { echo " selected"; }
                                  echo ">".$value."</option>";
                                }
                              }
                              ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_addr" class="col-sm-2 col-form-label">住址</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="bc_addr" name="bc_addr" value="<?php echo @$res['bc_addr']; ?>" placeholder="住址" data-required<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_type" class="col-sm-2 col-form-label">建案類別</label>
                            <div class="col-sm-4">
                            <select class="form-control" id="bc_type" name="bc_type" style="width: 100%;"<?php echo NdisAble; ?>>
                                <?php
                                    foreach($bcJS['bctype'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if(!empty($bcID) && $key==""){ echo " disabled"; }
                                        if(empty($bcID)){
                                          if($key=='') { echo " selected"; }
                                        } else {
                                          if($key==@$res['bc_type']) { echo " selected"; }
                                        }
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            <label for="bc_usefor" class="col-sm-2 col-form-label">用途規劃</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="bc_usefor" name="bc_usefor" style="width: 100%;"<?php echo NdisAble; ?>>
                                <?php
                                    foreach($merchJS['usage'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if(!empty($bcID) && $key==""){ echo " disabled"; }
                                        if(empty($bcID)){
                                          if($key=='') { echo " selected"; }
                                        } else {
                                          if($key==@$res['bc_usefor']) { echo " selected"; }
                                        }
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_des" class="col-sm-2 col-form-label">特色說明</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_des" name="bc_des" rows="6" placeholder="特色說明"<?php echo NdisAble; ?>><?php echo @$res['bc_des']; ?></textarea>
                            </div>
                          </div>

                          <hr class="grouphr">
                          <div class="groupfield">建案座標定位</div>

                          <div class="form-group row">
                            <label for="bc_lat" class="col-sm-2 col-form-label">橫向中心坐標</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">緯度</span></div>
                                <input type="text" class="form-control notSubmit" id="bc_lat" name="bc_lat" value="<?php echo !empty($res['lat']) ? $res['lat'] : ""; ?>" placeholder="緯度" readOnly>
                                <div class="input-group-append"><span class="input-group-text">度</span></div>
                              </div>
                            </div>
                            <label for="bc_lng" class="col-sm-2 col-form-label">縱向中心坐標</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">經度</span></div>
                                <input type="text" class="form-control notSubmit" id="bc_lng" name="bc_lng" value="<?php echo !empty($res['lng']) ? $res['lng'] : ""; ?>" placeholder="經度" readOnly>
                                <div class="input-group-append"><span class="input-group-text">度</span></div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="" class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-10 text-sm text-muted"><i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;建案座標形態為「點」座標時，此經緯度為建案座標位置。而形態為「線」、「面」或是「集合」座標時，此經緯度即為座標群的的中心點，此時，中心座標自動生成不必填寫。</div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_lat" class="col-sm-2 col-form-label">集合坐標群</label>
                            <div class="col-sm-6">
                              <textarea class="form-control" id="bc_coordinates" name="bc_coordinates" placeholder="坐標群" rows="10"<?php echo NdisAble; ?>><?php echo $coorDinates; ?></textarea>
                            </div>
                            <div class="col-sm-4">
                              <a id="but_toOSM" class="btn btn-app bg-primary" style="width:100%;height:52px;font-size:0.6em;border-radius:0.5rem;">
                                <i class="fg-2x fg-search-globe" style="margin-top:-4px;font-style:normal;"> OSM建案名稱搜尋</i>
                              </a>
                              <a id="but_getWayPoints" class="btn btn-app bg-info" style="width:100%;height:52px;font-size:0.6em;border-radius: 0.5rem;">
                                <i class="fg-2x fg-pois" style="margin-top:-4px;font-style:normal;"> OSM路徑節點座標</i>
                              </a>
                              <a id="but_toGM" class="btn btn-app bg-warning" style="width:100%;height:52px;font-size:0.6em;border-radius:0.5rem;">
                                <i class="fg-2x fg-world-map" style="margin-top:-4px;font-style:normal;"> GoogleMap地址定位</i>
                              </a>
                              <a id="but_toGMxy" class="btn btn-app bg-warning" style="width:100%;height:52px;font-size:0.6em;border-radius:0.5rem;">
                                <i class="fg-2x fg-world-map" style="margin-top:-4px;font-style:normal;"> GoogleMap中心座標定位</i>
                              </a>

                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="" class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-10 text-sm text-muted"><i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;座標群包含「線」、「面」或是「集合」座標，一旦填寫了座標群，則中心座標自動生成無需填寫，即使填寫也將被自動更換。建議在外部編寫完成後，再複製到座標群輸入框中，座標群的填寫方式以及格式說明請點擊這裡。</div>
                          </div>

                          <hr class="grouphr">
                          <div class="groupfield">建案資料比對元素</div>

                          <div class="form-group row">
                            <label for="bc_pureprefix" class="col-sm-2 col-form-label">建商&前綴</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_pureprefix" name="bc_pureprefix" value="<?php echo @$res['bc_pureprefix']; ?>" placeholder="建案名稱前綴"<?php echo NreadOnly; ?>>
                            </div>
                            <label for="bc_puretitle" class="col-sm-2 col-form-label">原始名稱</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_puretitle" name="bc_puretitle" value="<?php echo @$res['bc_puretitle']; ?>" placeholder="建案名稱(不含期數及分區)"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_purephase" class="col-sm-2 col-form-label">建案期數</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_purephase" name="bc_purephase" value="<?php echo @$res['bc_purephase']; ?>" placeholder="建案期數" data-type="number" data-max="2"<?php echo NreadOnly; ?>>
                            </div>
                            <label for="bc_pureext" class="col-sm-2 col-form-label">分區&後綴</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_pureext" name="bc_pureext" value="<?php echo @$res['bc_pureext']; ?>" placeholder="分區&後綴"<?php echo NreadOnly; ?>>
                            </div>
                          </div>

                          <hr class="grouphr">
                          <div class="groupfield">建案格局規劃</div>

                          <div class="form-group row">
                            <label for="bc_area" class="col-sm-2 col-form-label">基地面積</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="bc_area" name="bc_area" value="<?php echo isset($res['bc_area']) ? number_format($res['bc_area'], 2,".","") : ''; ?>" placeholder="基地面積"  data-type="number"<?php echo NreadOnly; ?>>
                                <div class="input-group-append">
                                  <span class="input-group-text">坪</span>
                                </div>
                              </div>
                            </div>
                            <label for="bc_totalnum" class="col-sm-2 col-form-label">總戶數</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="bc_totalnum" name="bc_totalnum" value="<?php echo @$res['bc_totalnum']; ?>" placeholder="總戶數" data-type="number"<?php echo NreadOnly; ?>>
                                <div class="input-group-append"><span class="input-group-text">戶</span></div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_pratio" class="col-sm-2 col-form-label">公設比</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="bc_pratio" name="bc_pratio" value="<?php echo isset($res['bc_pratio']) ? number_format($res['bc_pratio'], 2) : ''; ?>" placeholder="公設比" data-type="number"<?php echo NreadOnly; ?>>
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                              </div>  
                            </div>
                            <label for="bc_far" class="col-sm-2 col-form-label">容積率</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="bc_far" name="bc_far" value="<?php echo isset($res['bc_far']) ? number_format($res['bc_far'], 2) : ''; ?>" placeholder="容積率" data-type="number"<?php echo NreadOnly; ?>>
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_coverage" class="col-sm-2 col-form-label">建蔽率</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="bc_coverage" name="bc_coverage" value="<?php echo isset($res['bc_coverage']) ? number_format($res['bc_coverage'], 2) : ''; ?>" placeholder="建蔽率" data-type="number"<?php echo NreadOnly; ?>>
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                              </div>
                            </div>
                            <label for="bc_plan" class="col-sm-2 col-form-label">格局規劃說明</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_plan" name="bc_plan" value="<?php echo @$res['bc_plan']; ?>" placeholder="格局規劃 ex.二房(43.02坪),三房(36.88坪)"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_completion" class="col-sm-2 col-form-label">交屋時間</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="text" class="form-control" id="bc_completion" name="bc_completion" data-inputmask-alias="datetime" data-inputmask-inputformat="yyyy-mm-dd" value="<?php echo $res['bc_completion'] ?? ''; ?>" placeholder="交屋時間" data-mask<?php echo NreadOnly; ?>>
                              </div>
                            </div>
                            <label for="bc_state" class="col-sm-2 col-form-label">交屋狀態</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="bc_state" name="bc_state" style="width: 100%;"<?php echo NdisAble; ?>>
                                <?php
                                    foreach($bcJS['bcover'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if(!empty($bcID) && $key==""){ echo " disabled"; }
                                        if(empty($bcID)){
                                          if($key=='') { echo " selected"; }
                                        } else {
                                          if($key==@$res['bc_state']) { echo " selected"; }
                                        }
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_direction" class="col-sm-2 col-form-label">座向說明</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_direction" name="bc_direction" value="<?php echo @$res['bc_direction']; ?>" placeholder="座向 ex.北、東南"<?php echo NreadOnly; ?>>
                            </div>  
                            <label for="bc_layernum" class="col-sm-2 col-form-label">單層戶數</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="bc_layernum" name="bc_layernum" value="<?php echo @$res['bc_layernum']; ?>" placeholder="單層戶數"<?php echo NreadOnly; ?>>
                                <div class="input-group-append"><span class="input-group-text">戶</span></div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_meetadd" class="col-sm-2 col-form-label">接待中心住址</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="bc_meetadd" name="bc_meetadd" value="<?php echo @$res['bc_meetadd']; ?>" placeholder="接待中心住址"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_parkplan" class="col-sm-2 col-form-label">車位規劃說明</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_parkplan" name="bc_parkplan" value="<?php echo @$res['bc_parkplan']; ?>" placeholder="車位規劃 ex.平面式、機械式"<?php echo NreadOnly; ?>>
                            </div>
                            <label for="bc_parknum" class="col-sm-2 col-form-label">車位總數量</label>
                            <div class="col-sm-1">
                              <input type="text" class="form-control" id="bc_parknum" name="bc_parknum" value="<?php echo @$res['bc_parknum']; ?>" placeholder="總數量" data-type="number"<?php echo NreadOnly; ?>>
                            </div>
                            <label for="bc_parkratio" class="col-sm-2 col-form-label">車位配比(一戶N位)</label>
                            <div class="col-sm-1">
                              <input type="text" class="form-control" id="bc_parkratio" name="bc_parkratio" value="<?php echo isset($res['bc_parkratio']) ? number_format($res['bc_parkratio'], 2) : ''; ?>" placeholder="配比" data-type="number"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_building" class="col-sm-2 col-form-label">棟戶規劃說明</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="bc_building" name="bc_building" value="<?php echo @$res['bc_building']; ?>" placeholder="棟戶規劃 ex.2棟，63戶住家"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_abovemin" class="col-sm-2 col-form-label">地上樓層規劃</label>
                            <div class="col-sm-2">
                                <div class="input-group">
                                  <div class="input-group-prepend"><span class="input-group-text">最低</span></div>
                                  <input type="text" class="form-control" id="bc_abovemin" name="bc_abovemin" value="<?php echo @$res['bc_abovemin']; ?>" placeholder="層" data-type="number"<?php echo NreadOnly; ?>>
                                  <div class="input-group-append"><span class="input-group-text">層</span></div>
                                </div>   
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group">
                                  <div class="input-group-prepend"><span class="input-group-text">最高</span></div>
                                  <input type="text" class="form-control" id="bc_abovemax" name="bc_abovemax" value="<?php echo @$res['bc_abovemax']; ?>" placeholder="層" data-type="number"<?php echo NreadOnly; ?>>
                                  <div class="input-group-append"><span class="input-group-text">層</span></div>
                                </div>
                            </div>
                            <label for="bc_undermin" class="col-sm-2 col-form-label">地下樓層規劃</label>
                            <div class="col-sm-2">
                                <div class="input-group">
                                  <div class="input-group-prepend"><span class="input-group-text">最少</span></div>
                                  <input type="text" class="form-control" id="bc_undermin" name="bc_undermin" value="<?php echo @$res['bc_undermin']; ?>" placeholder="層" data-type="number"<?php echo NreadOnly; ?>>
                                  <div class="input-group-append"><span class="input-group-text">層</span></div>
                                </div>   
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group">
                                  <div class="input-group-prepend"><span class="input-group-text">最多</span></div>
                                  <input type="text" class="form-control" id="bc_undermax" name="bc_undermax" value="<?php echo @$res['bc_undermax']; ?>" placeholder="層" data-type="number"<?php echo NreadOnly; ?>>
                                  <div class="input-group-append"><span class="input-group-text">層</span></div>
                                </div>
                            </div>
                          </div>

                          <hr class="grouphr">
                          <div class="groupfield">建案價格規劃</div>

                          <div class="form-group row">
                            <label for="bc_totalprice" class="col-sm-2 col-form-label">建案總價</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="bc_totalprice" name="bc_totalprice" value="<?php echo @$res['bc_totalprice']; ?>" placeholder="建案總價"<?php echo NreadOnly; ?>>
                                <div class="input-group-append"><span class="input-group-text">萬元</span></div>
                              </div>
                            </div>
                            <label for="bc_unitprice" class="col-sm-2 col-form-label">建案單價</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="bc_unitprice" name="bc_unitprice" value="<?php echo @$res['bc_unitprice']; ?>" placeholder="建案單價"<?php echo NreadOnly; ?>>
                                <div class="input-group-append"><span class="input-group-text">萬元</span></div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_pprice" class="col-sm-2 col-form-label">車位價格</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="bc_pprice" name="bc_pprice" value="<?php echo @$res['bc_pprice']; ?>" placeholder="車位價格"<?php echo NreadOnly; ?>>
                                <div class="input-group-append"><span class="input-group-text">萬元</span></div>
                              </div>
                            </div>
                            <label for="bc_loan" class="col-sm-2 col-form-label">貸款成數</label>
                            <div class="col-sm-4">
                              <div class="input-group">
                                <input type="text" class="form-control" id="bc_loan" name="bc_loan" value="<?php echo isset($res['bc_loan']) ? number_format($res['bc_loan'], 2) : ''; ?>" placeholder="貸款成數" data-type="number"<?php echo NreadOnly; ?>>
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                              </div>
                            </div>
                          </div>

                          <hr class="grouphr">
                          <div class="groupfield">建案管理規劃</div>

                          <div class="form-group row">
                            <label for="bc_pmc" class="col-sm-2 col-form-label">物業管理公司</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="bc_pmc" name="bc_pmc" style="width: 100%;"<?php echo NdisAble; ?>>
                                <?php
                                    foreach($geJS['boolean'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if(!empty($bcID) && $key==""){ echo " disabled"; }
                                        if(empty($bcID)){
                                          if($key=='') { echo " selected"; }
                                        } else {
                                          if($key==@$res['bc_pmc']) { echo " selected"; }
                                        }
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            <label for="XXXX" class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-4">
                              
                            </div>                    
                          </div>
                          <div class="form-group row">
                            <label for="bc_rmc" class="col-sm-2 col-form-label">管委會</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="bc_rmc" name="bc_rmc" style="width: 100%;"<?php echo NdisAble; ?>>
                                <?php
                                    foreach($geJS['boolean'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if(!empty($bcID) && $key==""){ echo " disabled"; }
                                        if(empty($bcID)){
                                          if($key=='') { echo " selected"; }
                                        } else {
                                          if($key==@$res['bc_rmc']) { echo " selected"; }
                                        }
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            <label for="bc_mfee" class="col-sm-2 col-form-label">管理費用說明</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="bc_mfee" name="bc_mfee" value="<?php echo @$res['bc_mfee']; ?>" placeholder="管理費用說明"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_pile" class="col-sm-2 col-form-label">充電樁</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="bc_pile" name="bc_pile" style="width: 100%;"<?php echo NdisAble; ?>>
                                <?php
                                    foreach($geJS['boolean'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if(!empty($bcID) && $key==""){ echo " disabled"; }
                                        if(empty($bcID)){
                                          if($key=='') { echo " selected"; }
                                        } else {
                                          if($key==@$res['bc_pile']) { echo " selected"; }
                                        }
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                            <label for="bc_trash" class="col-sm-2 col-form-label">垃圾處理說明</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_trash" name="bc_trash" value="<?php echo @$res['bc_trash']; ?>" placeholder="垃圾處理"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_inpublic" class="col-sm-2 col-form-label">公共設施說明</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="bc_inpublic" name="bc_inpublic" value="<?php echo @$res['bc_inpublic']; ?>" placeholder="公共設施"<?php echo NreadOnly; ?>>
                            </div>
                          </div>

                          <hr class="grouphr">
                          <div class="groupfield">建案建築規劃</div>

                          <div class="form-group row">
                            <label for="bc_structure" class="col-sm-2 col-form-label" style="margin-bottom:18px;">結構工程</label>
                            <div class="col-sm-10 select2-info">
                              <select class="select2" id="bc_structure" name="bc_structure" multiple="multiple" data-placeholder="結構建材" data-dropdown-css-class="select2-info" style="width: 100%;"<?php echo NdisAble; ?>>
                              <?php
                                  $quaJS=!empty(@$res['bc_structure'])?json_decode(@$res['bc_structure']):array();
                                  foreach($merchJS['material'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if($key==""){ echo " disabled"; }
                                      if(in_array($key, $quaJS)) { echo " selected"; }
                                      echo '>'.$value.'</option>';
                                  }
                                  ?>
                              </select>
                            </div>
                            <label for="bc_zoning" class="col-sm-2 col-form-label">土地分區說明</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="bc_zoning" name="bc_zoning" value="<?php echo @$res['bc_zoning']; ?>" placeholder="土地分區說明 ex.商業區、第三種住宅區..."<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_blicense" class="col-sm-2 col-form-label">建造執照</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_blicense" name="bc_blicense" value="<?php echo @$res['bc_blicense']; ?>" placeholder="建造執照"<?php echo NreadOnly; ?>>
                            </div>
                            <label for="bc_ulicense" class="col-sm-2 col-form-label">使用執照</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_ulicense" name="bc_ulicense" value="<?php echo @$res['bc_ulicense']; ?>" placeholder="使用執照"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_materials" class="col-sm-2 col-form-label">建材說明</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_materials" name="bc_materials" rows="5" placeholder="建材說明"<?php echo NdisAble; ?>><?php echo @$res['bc_materials']; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_investor" class="col-sm-2 col-form-label">投資建設</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_investor" name="bc_investor" value="<?php echo @$res['bc_investor']; ?>" placeholder="投資建設"<?php echo NreadOnly; ?>>
                            </div>
                            <label for="bc_constructer" class="col-sm-2 col-form-label">營造公司</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_constructer" name="bc_constructer" value="<?php echo @$res['bc_constructer']; ?>" placeholder="營造公司"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_saleteam" class="col-sm-2 col-form-label">企劃銷售</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_saleteam" name="bc_saleteam" value="<?php echo @$res['bc_saleteam']; ?>" placeholder="企劃銷售"<?php echo NreadOnly; ?>>
                            </div>
                            <label for="bc_cdesign" class="col-sm-2 col-form-label">建築設計</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_cdesign" name="bc_cdesign" value="<?php echo @$res['bc_cdesign']; ?>" placeholder="建築設計"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_vdesign" class="col-sm-2 col-form-label">景觀設計</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_vdesign" name="bc_vdesign" value="<?php echo @$res['bc_vdesign']; ?>" placeholder="景觀設計"<?php echo NreadOnly; ?>>
                            </div>
                            <label for="bc_pdesign" class="col-sm-2 col-form-label">公設設計</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_pdesign" name="bc_pdesign" value="<?php echo @$res['bc_pdesign']; ?>" placeholder="公設設計"<?php echo NreadOnly; ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="XXXX" class="col-sm-2 col-form-label">燈光設計</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control" id="bc_ldesign" name="bc_ldesign" value="<?php echo @$res['bc_ldesign']; ?>" placeholder="燈光設計"<?php echo NreadOnly; ?>>
                            </div>
                          </div>


                          <hr class="grouphr">
                          <div class="groupfield">建案周邊生活機能</div>
                          
                          <div class="form-group row">
                            <label for="bc_mrt" class="col-sm-2 col-form-label">捷運系統</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_mrt" name="bc_mrt" placeholder="捷運系統"<?php echo NdisAble; ?>><?php echo @$res['bc_mrt']; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_hiway" class="col-sm-2 col-form-label">高速公路</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_hiway" name="bc_hiway" placeholder="高速公路"<?php echo NdisAble; ?>><?php echo @$res['bc_hiway']; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_speedway" class="col-sm-2 col-form-label">快速道路</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_speedway" name="bc_speedway" placeholder="快速道路"<?php echo NdisAble; ?>><?php echo @$res['bc_speedway']; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_thsr" class="col-sm-2 col-form-label">高鐵系統</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_thsr" name="bc_thsr" placeholder="高鐵系統"<?php echo NdisAble; ?>><?php echo @$res['bc_thsr']; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_tra" class="col-sm-2 col-form-label">臺鐵系統</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_tra" name="bc_tra" placeholder="臺鐵系統"<?php echo NdisAble; ?>><?php echo @$res['bc_tra']; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_othertraffic" class="col-sm-2 col-form-label">其他交通方式描述</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_othertraffic" name="bc_othertraffic" placeholder="其他交通方式描述"<?php echo NdisAble; ?>><?php echo @$res['bc_othertraffic']; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_edu" class="col-sm-2 col-form-label">學區</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_edu" name="bc_edu" placeholder="學區"<?php echo NdisAble; ?>><?php echo @$res['bc_edu']; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_market" class="col-sm-2 col-form-label">超商/賣場</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_market" name="bc_market" placeholder="超商/賣場"<?php echo NdisAble; ?>><?php echo @$res['bc_market']; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_omarket" class="col-sm-2 col-form-label">傳統市場</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_omarket" name="bc_omarket" placeholder="傳統市場"<?php echo NdisAble; ?>><?php echo @$res['bc_omarket']; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_outpublic" class="col-sm-2 col-form-label">公共建設</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_outpublic" name="bc_outpublic" placeholder="公共建設"<?php echo NdisAble; ?>><?php echo @$res['bc_outpublic']; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_circle" class="col-sm-2 col-form-label">熱門商圈</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_circle" name="bc_circle" placeholder="熱門商圈"<?php echo NdisAble; ?>><?php echo @$res['bc_circle']; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_medical" class="col-sm-2 col-form-label">醫療機構</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_medical" name="bc_medical" placeholder="醫療機構"<?php echo NdisAble; ?>><?php echo @$res['bc_medical']; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_gov" class="col-sm-2 col-form-label">政府機構</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_gov" name="bc_gov" placeholder="政府機構"<?php echo NdisAble; ?>><?php echo @$res['bc_gov']; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="bc_pack" class="col-sm-2 col-form-label">其他配套</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="bc_pack" name="bc_pack" placeholder="其他配套"<?php echo NdisAble; ?>><?php echo @$res['bc_pack']; ?></textarea>
                            </div>
                          </div>

                          <hr class="grouphr">
                          <div class="groupfield">系統參數設定</div>

                          <div class="form-group row">
                            <label for="bc_check" class="col-sm-2 col-form-label">建案檢號</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="bc_check" name="bc_check" style="width: 100%;"<?php echo NdisAble; ?>>
                              <?php
                                    foreach($bcJS['bccheck'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if(!empty($bcID) && $key==""){ echo " disabled"; }
                                      if(empty($bcID)){
                                        if($key=='') { echo " selected"; }
                                      } else {
                                        if($key==@$res['bc_check']) { echo " selected"; }
                                      }
                                    echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                            <label for="bc_status" class="col-sm-2 col-form-label" style="margin-bottom:18px;">建案狀態</label>
                            <div class="col-sm-4">
                              <select class="form-control" id="bc_status" name="bc_status" style="width: 100%;"<?php echo NdisAble; ?>>
                              <?php
                                    foreach($bcJS['bcstatus'] as $key => $value){
                                      echo '<option value="'.$key.'"';
                                      if(!empty($bcID) && $key==""){ echo " disabled"; }
                                      if(empty($bcID)){
                                        if($key=='') { echo " selected"; }
                                      } else {
                                        if($key==@$res['bc_status']) { echo " selected"; }
                                      }
                                      echo '>'.$value.'</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>

                      </div>
                      </form>
                    </div>
                    <!-- /第1分頁管理者資料修改 -->
                    <?php if(!empty($bcID)){ ?>
                      <!-- 第2分頁展示圖像管理 -->
                    <div class="tab-pane<?php if($tabIDX==2) echo " active"; ?>" id="tabMenu2" style=”background-color:#5F6E86;“>
                      <h5>社區建案圖像管理</h5>
                      <div style="font-size:0.85em;line-height:1.4em;">
                        <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;社區建案資料共提供 12 個圖檔位，前四個順位為建案主視圖，後8個順位為附加主視圖或是建案周邊商圈建設視圖，上傳的圖檔大小限制在 30Kb 至 300Kb 以內寬高比為 16:9 的 JPG 檔案，超過尺寸系統將自動按比例裁切，建議圖檔寬高 992*558px (含)以上以及 1920*1080px (含)以下，檔案大小越接近上限則越清晰，重要部分請勿接近圖像外側邊緣。
                      </div>
                      <form id="form_tabMenu2" name="form_tabMenu2" role="form" action="../funcFiles/file_act_func.php" method="post" enctype="multipart/form-data" data-formmode="photoUpload" data-func="func_bc_imgUpload">
                        <div class="card-body" style="text-align:center;">
                          <input type="hidden" id="currIdx" name="currIdx" data-orival="<?php echo intval(@$res['bc_defaultphoto']); ?>" value="<?php echo intval(@$res['bc_defaultphoto']); ?>">
                            <!--Photo List -->
                              <!-- Our File Inputs -->
                              <?php
                              $totalUpNum = 12;
                              for($i=0;$i<$totalUpNum;$i++){
                                if($i==0){
                                  echo '<hr style="border: 1px solid #2C333E"><div class="hr-more">建案主視圖</div>';
                                } elseif($i==4){
                                  echo '<hr style="border: 1px solid #2C333E"><div class="hr-more">附加或周邊視圖</div>';
                                }
                                $imgOK=false;
                                $filename = 'bcimg_'.$bcID.'_'.$i.'_';
                                $filePath = '../uploadFiles/bc/'.$cityCode.'/'.$distCode.'/'.$filename;
                                if(file_exists($filePath.'.jpg')){
                                    $imgOK = true;
                                }
                              ?>
                              <div class="area-upload-notext">
                                <div class="wrap-custom-file">
                                  <input type="file" name="imgUpload_<?php echo $i; ?>" id="imgUpID_<?php echo $i; ?>" accept=".jpg,.jpeg" />
                                  <label for="imgUpID_<?php echo $i; ?>" fileext="jpg"<?php echo ($imgOK==true) ? ' class="file-ok" style="background-image:url(\''.$filePath.'.jpg?'.time().'\')"' : ' style="background-image:url(\'../imageFiles/nopic_240x180.jpg\');background-size: cover;background-position: center;"'; ?>>
                                    <input type="hidden" name="imgCheck_<?php echo $i; ?>" id="imgCheck_<?php echo $i; ?>" value="<?php echo ($imgOK==true) ? microtime(true) : ''; ?>" />
                                    <input type="radio" name="defIdx" id="defIdx" style="<?php echo ($imgOK==true) ? "display:block;" : "display:none;"; ?>" value="<?php echo $i; ?>" title="設為預設主圖" alt="設為預設主圖"<?php echo intval(@$res['bc_defaultphoto'])==$i ? " checked": ''; ?>>
                                    <?php if ($imgOK==true) { ?>
                                      <span>
                                        <i class="fas fa-window-close funcClose" onClick="clearUpload(this,'<?php echo $i; ?>','<?php echo $filePath;//不加副檔名 ?>','photo');return false;"></i>
                                        <div>Index: <?php echo $i; ?></div>
                                        <i class="fas fa-external-link-square-alt funcView" onClick="imagePopView('<?php echo $i; ?>','<?php echo $filePath.'.jpg?'.time(); ?>','Index: <?php echo $i; ?>');return false;"></i>
                                      </span>
                                    <?php } else { ?>
                                      <span>Index: <?php echo $i; ?></span>
                                    <?php } ?>
                                    <i class="fa fa-plus funcAdd"></i>
                                  </label>
                                </div>
                              </div>
                              <?php } ?>
                              <div class="clear-inline"></div>
                            <!-- /Photo List -->
                        </div>
                      </form>
                    </div>
                    <!-- /第2分頁展示圖像管理 -->
                    <!-- 第3分頁地圖與街景 -->
                    <div class="tab-pane<?php if($tabIDX==3) echo " active"; ?>" id="tabMenu3" style=”background-color:#5F6E86;“>
                      <h5>地圖與街景</h5>
                      <div style="font-size:0.85em;line-height:1.4em;">
                        <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;
                      </div>
                      
                      <div class="card-body" style="text-align:center;">
                        <!--
                        <table id="dataTable_bcadlist" class="table table-bordered table-striped table-hover">
                          <thead>
                          <tr style="background-color:rgba(68,162,115,0.8);color:#D4EDE0;">
                              <th>NO</th>
                              <th>建案ID</th>
                              <th>地區</th>
                              <th>建案名稱</th>
                              <th>建案別名</th>
                              <th>住址</th>
                              <th>型態</th>
                              <th>狀態</th>
                          </tr>
                          </thead>
                          <tbody>
                          </tbody>
                          <tfoot>
                          <tr style="background-color:rgba(68,162,115,0.8);color:#D4EDE0;">
                              <th>NO</th>
                              <th>建案ID</th>
                              <th>地區</th>
                              <th>建案名稱</th>
                              <th>建案別名</th>
                              <th>住址</th>
                              <th>型態</th>
                              <th>狀態</th>
                          </tr>
                          </tfoot>
                        </table>
                        -->
                      </div>
                    </div>
                    <!-- /第3分頁地圖與街景 -->
                    <!-- 第4分頁座標填寫說明 -->
                    <div class="tab-pane<?php if($tabIDX==4) echo " active"; ?>" id="tabMenu4" style=”background-color:#5F6E86;“>
                      <h5>座標填寫說明</h5>
                      <div style="font-size:0.85em;line-height:1.4em;">
                        <i class="nav-icon fas fa-exclamation-circle"></i>&nbsp;&nbsp;
                      </div>
                      
                      <div class="card-body" style="text-align:center;">

                      </div>
                    </div>
                    <!-- /第4分頁座標填寫說明 -->
                    <?php } ?>

                  </div>
                  <!-- /.tab-content -->
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
        <!-- -------------------- 裁切頭像彈出視窗 -------------------- -->
         
        <!-- -------------------- /裁切頭像彈出視窗 -------------------- -->
        <!-- -------------------- 取得 OSM 路徑節點座標彈出視窗 -------------------- -->
        <div id="osmway_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="background:rgba(0,0,0,0.75);">
          <div class="modal-dialog modal-dialog-scrollable modal-lg" style="width:50%;position:relative;">
            <div id="analysisMask" style="position:absolute;left:0;right:0;top:0;bottom:0;z-index:999;width:100%;height:100%;background-color:rgba(0,0,0,0.25);display:none;">
              <div style="left:50%;top:50%;position:absolute;transform:translate(-50%,-50%);opacity:0.5;">
                <img src="../imageFiles/loading3.gif" alt="Loading" height="60" width="60"><br>資料分析中
              </div>
            </div>
            <div class="modal-content">
              <!-- Header -->
              <div class="modal-header bg-secondary">
                <h5 class="modal-title">取得 OSM 路徑節點座標</h5>
                <button type="button" id="osmwayModalCloseTop" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <!-- /Header -->
              <!-- Model Body  class="waitloader flex-column justify-content-center align-items-center" -->
              <div class="modal-body" style="position:relative;">              
                <h6 class="modal-des"></h6>
                <div id="toDoAnalysis" class="form-group row">
                  <label for="wayCode" class="col-sm-2 col-form-label">路徑代碼</label>
                  <div class="col-sm-6">
                    <input type="text" class="form-control" id="wayCode" name="wayCode" value="" placeholder="路徑代碼"<?php echo NreadOnly; ?>>
                  </div>
                  <label for="wayVersion" class="col-sm-2 col-form-label">路徑版本</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control" id="wayVersion" name="wayVersion" value="" placeholder="路徑版本"<?php echo NreadOnly; ?>>
                  </div>
                </div>
                <div class="form-group row" id="toDoPoston">
                  <textarea type="text" class="form-control" id="nodePoints" name="nodePoints" rows="10"></textarea>
                </div>
              </div>
              <!-- /Model Body -->
              <!-- Footer -->
              <div class="modal-footer justify-content-between">
                <button type="button" id="modal_copyBut" class="btn btn-warning" style="visibility:hidden;">複製內容</button>
                <button type="button" id="modal_postBut" class="btn btn-info" style="visibility:hidden;">接續內容</button>
                <button type="button" id="modal_upBut" class="btn btn-primary" data-action="analysis">分析路徑節點</button>
              </div>
              <!-- /Footer -->
            </div>
          </div>
          <!-- /.modal-dialog -->
        </div>
      <!-- -------------------- /取得 OSM 路徑節點座標彈出視窗 -------------------- -->
      </section>
      <!-- /主要內容區域 ------------------------------------ -->
    </div>
    <!-- /頁內全頁內容 -->

    <!-- jQuery -->
    <script src="../pluginFiles/jquery/jquery.min.js"></script>

    <!-- 下方訊息區 -->
    <?php //include_once('include_footer.php'); ?>
    <!-- /下方訊息區 -->
    <!-- 右側功能控制區 -->
      <aside class="control-sidebar control-sidebar-dark">
        <!-- 功能區內容 ------------------------------------ -->

        <!-- /功能區內容 ----------------------------------- -->
      </aside>
    <!-- /右側功能控制區 -->
  </div>
  <!-- /全頁框 -->
<!-- ------------------------ Scripts Link ------------------------ -->
<!-- jQuery is in front of footer file. -->
<!-- Bootstrap 4 -->
<script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="../pluginFiles/select2/js/select2.full.min.js"></script>
<!-- InputMask 日期輸入元件JS -->
<script src="../pluginFiles/moment/moment.min.js"></script>
<script src="../pluginFiles/inputmask/jquery.inputmask.min.js"></script>
<!-- ezValidation -->
<script src="../pluginFiles/validator/validator.js<?php echo '?t='.time(); ?>"></script>
<!-- Leaflet Map -->
<script src="../pluginFiles/leaflet/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/fileupload.js<?php echo '?t='.time(); ?>"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>
<!-- ------------------------ /Scripts Link ------------------------ -->
<script>

  $(function () {
    //當未經過 TreeView 轉跳頁面時，改變外層 TreeView 結構與之相關(必須要載入 page.basic.js 才會執行)
    treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>');
    //* iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });
    //-----------------------------------------
    //////////////////// OSM 地圖顯示 ///////////////////////////
    <?php if(!empty(@$res['lat']) && !empty(@$res['lng'])) { ?>
    var map = L.map('map').setView([<?php echo @$res['lat']; ?>, <?php echo @$res['lng']; ?>], 17);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
    var marker = L.marker([<?php echo @$res['lat']; ?>, <?php echo @$res['lng']; ?>]).addTo(map);
    <?php } ?>
    //////////////////// 基礎JSON數據轉換 ////////////////////////
    //////////////////// SELECT2 複選元件初始化 //////////////////
    $('.select2').select2();
    //////////////////// DATEMASK 日期輸入元件初始化 //////////////
    $('#datemask2').inputmask('yyyy-mm-dd', { 'placeholder': 'yyyy-mm-dd' });
    $('[data-mask]').inputmask();
    //------------------------------------------------//   
    $('#but_getfrominfo').click(function(e){ 
      alert('but_getfrominfo');
    });

    $('#but_addrformat').click(function(e){ 
      alert('but_addrformat');
    });

    $('#but_dupfilter').click(function(e){ 
      alert('but_dupfilter');
    });

    $('#but_getgeoinfo').click(function(e){ 
      alert('but_getgeoinfo');
    });

    $('#but_toOSM').click(function(e){
      var toSearch = $('#bc_title').val();
      if(toSearch == '') {
        alert('請先輸入建案名稱，盡量輸入具有唯一性以及代表性的名稱！');
        return false;
      }
      toSearch = encodeURIComponent(toSearch);
      window.open("https://www.openstreetmap.org/search?query="+ toSearch, '_blank');
    });
    $('#but_toGM').click(function(e){
      if($("#bc_city").val() == '' || $("#bc_district").val() == '' || $("#bc_addr").val() == '') {
        alert('請先選擇城市區劃，以及輸入正確住址！');
        return false;
      }
      var currCityDist = $( "#bc_city option:selected" ).text() + $( "#bc_district option:selected" ).text();
      var currFullAddress = currCityDist + $( "#bc_addr" ).val().replace(currCityDist, "");
      currFullAddress = encodeURIComponent(currFullAddress);
      window.open("https://www.google.com/maps/search/"+ currFullAddress +"/", '_blank');
    });
    $('#but_toGMxy').click(function(e){
      if($("#bc_lat").val() == '' || $("#bc_lng").val() == '') {
        alert('請先選擇城市區劃，以及輸入正確住址');
        return false;
      }
      var centerPoint = $("#bc_lat").val() + "," + $("#bc_lng").val();
      window.open("https://www.google.com/maps/search/"+ centerPoint +"/", '_blank');

    });
    $('li.prevLink').on('click', function(){
      doReDirect();
    });

    $('#but_getWayPoints').click(function(e){   
      $('#osmway_modal').show().addClass('show');
      $('#osmway_modal #modal_delBut').css("visibility", "visible");
    });
    $('#osmwayModalCloseTop').click(function(e){
      $('#osmway_modal').removeClass('show').hide();
    });
    $('#modal_copyBut').click(function () {
        navigator.clipboard.writeText($('#nodePoints').val());
        $('#nodePoints').val('');
        $('#modal_upBut').data('action', 'analysis').html('分析路徑節點');
        $('#modal_copyBut').css("visibility", "hidden");
        $('#modal_postBut').css("visibility", "hidden");
        $('#osmway_modal').removeClass('show').hide();
    });
    $('#modal_postBut').click(function () {
        var fullPost = $('#bc_coordinates').val() + '\n+\n' + $('#nodePoints').val(); 
        $('#bc_coordinates').val(fullPost);
        $('#nodePoints').val('');
        $('#modal_upBut').data('action', 'analysis').html('分析路徑節點');
        $('#modal_copyBut').css("visibility", "hidden");
        $('#modal_postBut').css("visibility", "hidden");
        ORGForm = CHGform.serialize();
        window.parent.$('#topBut_save').addClass('active');
        $('#osmway_modal').removeClass('show').hide();
    });

    /////////////////////////////////////////////////////////////////////////
    //-- 進行 OSM 路徑分析 modal_upBut modal_delBut
    $('#modal_upBut').click(function(e){
      if($(this).data('action') == 'poston') {
        $('#bc_coordinates').val($('#nodePoints').val());
        $('#nodePoints').val('');
        $('#modal_upBut').data('action', 'analysis').html('分析路徑節點');
        $('#modal_copyBut').css("visibility", "hidden");
        $('#modal_postBut').css("visibility", "hidden");
        //$('#osmway_modal').removeClass('show').modal('hide');
        ORGForm = CHGform.serialize();
        window.parent.$('#topBut_save').addClass('active');
        $('#osmway_modal').removeClass('show').hide();
        return false;
      } else if($(this).data('action') == 'analysis') {
        if($('#wayCode').val() == '') {
          alert('請先輸入 OSM 專屬的路徑編號!');
          return false;
        }
      }
      $.ajax({
              type: 'post',
              url: '../funcFiles/md_act_func.php',
              data: {
                //action: $(this).data('action'),
                wayCode: $('#wayCode').val(),
                wayVersion: $('#wayVersion').val(),
                formAcce:'func_osmway_getosmway',
                token: getTokenVal()
              },
              dataType: 'json',
              //async: false, 
              beforeSend:function(){
                var comF = '這個動作將進入 OSM 頁面，並且分析取得路徑節點資訊。\r\n確定進行分析操作？！';
                if(!confirm(comF)){return false;}
                $('#analysisMask').show();
              },
              success: function (data) {
                console.log(data);
                if(data['RTN']==1){
                  window.parent.toastr['success'](data['msgStr']);
                  $('#nodePoints').val(data['nodePoints']);
                  $('#modal_upBut').data('action', 'poston').html('轉貼並更新內容');
                  $('#modal_copyBut').css("visibility", "visible");
                  $('#modal_postBut').css("visibility", "visible");
                  $('#analysisMask').hide();
                }
              },
              error: function (data) {
                console.log(data);
              }
          });
      });

  });


////////////////////////////////////////////////////////////////     
// ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
// -------------- 可接收include檔案要求後自定義動作 ------------- //
////////////////////////////////////////////////////////////////
// 當「儲存」按鈕按下時的動作
function doButSave(){
  var frm = $('#form_'+$('.tab-pane.active').prop("id"));
	var accE = frm.data('func');
  if(accE=='func_bc_infoUpdate'){
    var getValid = Validator.validate(frm);
    if(!getValid){
      return false;
    } else {
      ajaxPOST(frm);
    }
  }else if(accE=='func_bc_imgUpload'){
    filePOST(frm);
  }
}
// 當「重置」按鈕按下時的動作
function doButReset(){
  if (!confirm('重整頁面將會強制自伺服器重新載入這個頁面，確定執行這個操作？！')) {
    return false;
  }
  location.reload(true);
}
// 當「刪除」按鈕按下時的動作
function doButDelete(){
  $.ajax({
      type: 'post',
      url: '../funcFiles/bc_act_func.php',
      data: {
        'arrDatA': {}, 
        'formAcce':'func_bc_delete',
        'token': getTokenVal(), 
        'bcID':'<?php echo $bcID; ?>'
      },
      dataType: 'json',
      async: false, 
    success: function (data) {
        if(data['RTN']==1){
          window.location.replace("data_bclist.php?<?php echo $backUrl; ?>");
        } else {
          console.log(data);
          window.parent.toastr['error']('刪除檔案失敗，請重新操作或聯絡管理人員！！');
        }
      },
      error: function (data) {
          console.log(data);
          window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
      }
  });
}
function doReDirect(){
  let redirURL = '<?php echo $backUrl; ?>';
  if(redirURL=='') { return false; }
  window.location.replace(getCurrentURI(redirURL,'data_bclist.php'));
}
// 資料新增更新
function ajaxPOST(frm){
  var url = frm.prop('action');
  var typE = frm.prop('method');
  var frm = $('#form_'+$('.tab-pane.active').prop("id"));
	var accE = frm.data('func');
  var datA = getFormObjects(frm);
/*
    var datA = {};
    frm.find(":input").not(".notSubmit").each(function(index) {
      var itype = $(this).attr('type');
      var ikey = $(this).attr('id');
      if(ikey=='' || typeof(ikey) == 'undefined' ){ return; }
      if($(this).is('select')) {
        if($(this).prop("multiple")){ datA[ikey] = arrayTOstring($(this).val()); }
        else { datA[ikey] = $(this).val(); }
      } else if (itype=='radio' || itype=='checkbox') {
        if($(this).is(':checked')) { datA[ikey] = $(this).val(); }
      } else { datA[ikey] = $(this).val(); }
    });
    */
/*
    var datA = {
        //bc_id:          $('#bc_id').val(),
        //bc_published:   $('#bc_published').val(),
        //bc_updated:     $('#bc_updated').val(),
        bc_city:        $('#bc_city').val(),
        bc_district:    $('#bc_district').val(),
        bc_title:       $('#bc_title').val(),
        bc_subtitle:    $('#bc_subtitle').val(),
        bc_subtitle2:   $('#bc_subtitle2').val(),
        bc_pureprefix:  $('#bc_pureprefix').val(),
        bc_puretitle:   $('#bc_puretitle').val(),
        bc_purephase:   $('#bc_purephase').val(),
        bc_pureext:     $('#bc_pureext').val(),
        bc_mode:        $('#bc_mode').val(),
        bc_type:        $('#bc_type').val(),
        bc_usefor:      $('#bc_usefor').val(),
        bc_addr:        $('#bc_addr').val(),
        bc_lat:         $('#bc_lat').val(),
        bc_lng:         $('#bc_lng').val(),
        bc_area:        $('#bc_area').val(),
        bc_totalnum:    $('#bc_totalnum').val(),
        bc_pratio:      $('#bc_pratio').val(),
        bc_coverage:    $('#bc_coverage').val(),
        bc_far:         $('#bc_far').val(),
        bc_completion:  $('#bc_completion').val(),
        bc_state:       $('#bc_state').val(),
        bc_plan:        $('#bc_plan').val(),
        bc_layernum:    $('#bc_layernum').val(),
        bc_des:         $('#bc_des').val(),
        bc_unitprice:   $('#bc_unitprice').val(),
        bc_totalprice:  $('#bc_totalprice').val(),
        bc_pprice:      $('#bc_pprice').val(),
        bc_sprice:      $('#bc_sprice').val(),
        bc_selling:     $('#bc_selling').val(),
        bc_loan:        $('#bc_loan').val(),
        bc_meetadd:     $('#bc_meetadd').val(),
        bc_parkratio:   $('#bc_parkratio').val(),
        bc_parkplan:    $('#bc_parkplan').val(),
        bc_parknum:     $('#bc_parknum').val(),
        bc_building:    $('#bc_building').val(),
        bc_abovemin:    $('#bc_abovemin').val(),
        bc_abovemax:    $('#bc_abovemax').val(),
        bc_undermin:    $('#bc_undermin').val(),
        bc_undermax:    $('#bc_undermax').val(),
        bc_direction:   $('#bc_direction').val(),
        bc_pmc:         $('#bc_pmc').val(),
        bc_rmc:         $('#bc_rmc').val(),
        bc_mfee:        $('#bc_mfee').val(),
        bc_structure:   arrayTOstring($('#bc_structure').val()),
        bc_zoning:      $('#bc_zoning').val(),
        bc_blicense:    $('#bc_blicense').val(),
        bc_ulicense:    $('#bc_ulicense').val(),
        bc_materials:   $('#bc_materials').val(),
        bc_inpublic:    $('#bc_inpublic').val(),
        bc_pile:        $('#bc_pile').val(),
        bc_trash:       $('#bc_trash').val(),
        bc_investor:    $('#bc_investor').val(),
        bc_constructer: $('#bc_constructer').val(),
        bc_saleteam:    $('#bc_saleteam').val(),
        bc_cdesign:     $('#bc_cdesign').val(),
        bc_vdesign:     $('#bc_vdesign').val(),
        bc_pdesign:     $('#bc_pdesign').val(),
        bc_ldesign:     $('#bc_ldesign').val(),
        bc_mrt:         $('#bc_mrt').val(),
        bc_hiway:       $('#bc_hiway').val(),
        bc_speedway:    $('#bc_speedway').val(),
        bc_thsr:        $('#bc_thsr').val(),
        bc_tra:         $('#bc_tra').val(),
        bc_othertraffic: $('#bc_othertraffic').val(),
        bc_edu:         $('#bc_edu').val(),
        bc_market:      $('#bc_market').val(),
        bc_omarket:     $('#bc_omarket').val(),
        bc_outpublic:   $('#bc_outpublic').val(),
        bc_circle:      $('#bc_circle').val(),
        bc_medical:     $('#bc_medical').val(),
        bc_gov:         $('#bc_gov').val(),
        bc_pack:        $('#bc_pack').val(),
        //bc_image64:     $('#bc_image64').val(),
        bc_check:       $('#bc_check').val(),
        bc_status:      $('#bc_status').val()
        //bc_similar:     $('#bc_similar').val(), 
        //bc_similaradd:  $('#bc_similaradd').val(),
        //bc_similarid:   $('#bc_similarid').val(),
        //bc_infomark:    $('#bc_infomark').val(),
        //bc_imagemark:   $('#bc_imagemark').val(),
        //bc_updateinfo:  $('#bc_updateinfo').val(),
        //bc_distance:    $('#bc_distance').val()
      };
*/
    if(0=='<?php echo $bcID; ?>') { 
      accE = 'func_bc_addnew';
    }
    $.ajax({
        type: typE,
        url: url,
        data: {
          'arrDatA':datA, 
          'formAcce':accE,
          'token': getTokenVal(), 
          'bcID':'<?php echo $bcID; ?>'
        },
        dataType: 'json',  
        async: false, 
      success: function (data) {
          console.log(data);
          if(data['RTN']==1){
            ORGForm = CHGform.serialize();
            window.parent.$('#topBut_save').removeClass('active');
          } else if(data['RTN']==2){
            window.location.replace("data_bcedit.php?currID="+data['newID']+'&<?php echo $backUrl; ?>');
            return true;
          }
          window.parent.toastr[data['msgType']](data['msgStr']);
          //console.log(data);
        },
        error: function (data) {
          console.log(data);
          window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
        }
    });
}
//
function filePOST(frm){
  var url = frm.prop('action');
  var typE = frm.prop('method');
  var frm = $('#form_'+$('.tab-pane.active').prop("id"));
	var accE = frm.data('func');
  var defIdx = $("#defIdx:checked").val();
  var currIdx = $('#currIdx').val();
  var formData = new FormData();
      formData.append("formAcce",accE);
      formData.append("bcID",'<?php echo $bcID; ?>');
      formData.append("token",getTokenVal());
      formData.append("cityCode",'<?php echo @$res['bc_city']; ?>');
      formData.append("distCode",'<?php echo @$res['bc_district']; ?>');
      if(defIdx!=currIdx){
        formData.append("defIdx",defIdx);
      }
      for (var i=0;i<12;i++) {
        var file_data = $('#imgUpID_'+i).prop('files')[0];
        if(file_data){
          formData.append("imgUp["+i+"]",file_data);
        }
      }
      $.ajax({
            type: typE,
            url: url,
            data: formData,
            dataType: 'json',
            contentType: false,
            cache: false,
            processData:false,
           async: false,
          success: function (data) {
            ORGForm = CHGform.serialize();
            window.parent.$('#topBut_save').removeClass('active');
            //console.log(data);
            window.parent.toastr[data['msgType']](data['msgStr']);
          },
            error: function (data) {
              console.log(data);
              window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！'); 
            }
        });

}

</script>
</body>
</html>
