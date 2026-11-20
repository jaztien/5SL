<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_COURSE');
define('PAGE_ITEM','ITEM_COURSE');
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//
$courseID=currID;
$tabIDX = !empty($_REQUEST['tabIDX'])?intval($_REQUEST['tabIDX']):1;
$pageMax = isset($_REQUEST['pageMax']) ? intval($_REQUEST['pageMax']) : 25;
$jumpData = isset($_REQUEST['jumpData']) ? intval($_REQUEST['jumpData']) : 1;
//
$backUrl = !empty($pageMax) ? '&pageMax='.$pageMax : '';
$backUrl .= !empty($jumpData) ? '&jumpData='.$jumpData : '';
$backUrl = substr($backUrl,1);
//
$res = $DB->row("SELECT * FROM advitising WHERE advi_id=?",array($courseID));
$pres = $DB->row("SELECT * FROM advi_pack WHERE advp_id=?",array($res['advp_id']));
$mres = $DB->row("SELECT * FROM members AS a INNER JOIN mb_info AS b ON b.mb_id=a.mb_id WHERE a.mb_id=?",array($res['mb_id']));
$mdres = $DB->row("SELECT *, merch_coordinates.Long AS lng, merch_coordinates.Lat AS lat FROM merchandise WHERE merch_id=?",array($res['merch_id']));
unset($mdres['merch_lng']);//移除經緯度欄位
unset($mdres['merch_lat']);//移除經緯度欄位
unset($mdres['merch_coordinates']);//移除經緯度欄位

/*

//以下爲四句合併一句的寫法，可增加效率，並且減少程式碼量，請注意 SQL 語法的正確性與欄位別名的使用。
try {
    // ==========================================
    // 【核心設計：以廣告主表為錨點，橫向聯查四張關聯表】
    // 1. ad      : 廣告主表 (advitising)
    // 2. pk      : 廣告方案包表 (advi_pack)
    // 3. m / mi  : 會員主表與延伸資訊表 (使用 INNER JOIN 綁定後，再一起被 LEFT JOIN)
    // 4. md      : 項目/課程商品主表 (merchandise)
    // ==========================================
    $sqlCombined = "
        SELECT 
            -- 1. 廣告主表欄位 (別名 ad)
            ad.advi_id, ad.advp_id AS ad_advp_id, ad.mb_id AS ad_mb_id, ad.merch_id AS ad_merch_id,
            
            -- 2. 廣告方案包欄位 (別名 pk)
            pk.advp_name, pk.advp_price, -- 💡 這裡手動精準列出你 pack 表需要的欄位
            
            -- 3. 會員主表與資訊表欄位 (別名 m 與 mi)
            m.mb_account, m.mb_status,
            mi.mb_name, mi.mb_cellphone, mi.mb_email,
            
            -- 4. 課程商品主表與座標欄位 (別名 md)
            md.merch_title, md.merch_listprice,
            md.merch_coordinates.Long AS lng, -- 🟢 忠實保留你要求的空間地理座標 X 軸
            md.merch_coordinates.Lat AS lat   -- 🟢 忠實保留你要求的空間地理座標 Y 軸
            
        FROM advitising ad
        LEFT JOIN advi_pack pk ON ad.advp_id = pk.advp_id
        LEFT JOIN members m ON ad.mb_id = m.mb_id
        LEFT JOIN mb_info mi ON m.mb_id = mi.mb_id
        LEFT JOIN merchandise md ON ad.merch_id = md.merch_id
        WHERE ad.advi_id = :advi_id
    ";

    // 準備參數袋（因為四合一了，參數袋大瘦身，只需要準備 1 個初始廣告 ID！）
    $bindData = array(
        'advi_id' => $adviID
    );

    // 執行查詢：因為是依據單一廣告 ID 撈取整合同一筆大明細，呼叫 row() 最為俐落！
    $combinedRes = $DB->row($sqlCombined, $bindData);

    // 進行一擊必殺防退化攔截 [INDEX]
    if (empty($combinedRes)) {
        echo "【提示】找不到此廣告編號，或是該廣告資料已失效！";
    } else {
        // 成功一趟打包拿回所有聯動數據！
        echo "廣告編號: " . $combinedRes['advi_id'] . "<br>";
        echo "方案名稱: " . $combinedRes['advp_name'] . "<br>";
        echo "會員姓名: " . $combinedRes['mb_name'] . "<br>";
        echo "建案標題: " . $combinedRes['merch_title'] . "<br>";
        echo "經緯座標: " . $combinedRes['lng'] . " , " . $combinedRes['lat'] . "<br>";
    }

} catch (PDOException $e) {
    echo "廣告大整合聯查異常：" . $e->getMessage();
}



*/

//$DB->closeConnection();
//功能陣列建立，取自JSON檔案
$advJS=_fn_getCachedJsonData("../jsonFiles/advitising.json");
$mbJS=_fn_getCachedJsonData("../jsonFiles/member.json");
$mdJS=_fn_getCachedJsonData("../jsonFiles/merchandise.json");
$svJS=_fn_getCachedJsonData("../jsonFiles/service.json");
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
	
  <title> Fifth Season Life 人生第五季 | 廣告登載申購詳細資料 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="../pluginFiles/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-bs4/css/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-responsive/css/responsive.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-buttons/css/buttons.bootstrap4.css">
  <!-- Dropzone JS style -->
  <link rel="stylesheet" href="../pluginFiles/dropzone/dropzone.css" />
  <!-- Cropper JS style -->
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <!-- suneditor -->
  <link rel="stylesheet" href="../pluginFiles/suneditor/dist/css/suneditor.min.css">
  <!-- codeMirror -->
  <link rel="stylesheet" href="../pluginFiles/codemirror/lib/codemirror.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css<?php echo '?t='.time(); ?>">


<!-- -------------------------- /CSS Link -------------------------- -->
<style>
  /*
    .wrap-custom-file label i {
        margin-top:6px;
        margin-right:2px;
        display:none;
    }

    .wrap-custom-file label i.funcClose{
        display:block;
    }
    .torightSide{ 
      text-align: right; 
      padding-right: 8px; 
    }
/*
    /* ----- Image Crop Style ----- */
    /*
		.image_area { position: relative; }
		#uploaded_image {
		  	display: block;
		  	max-width: 100%;
		}
		.preview {
  			overflow: hidden;
  			width: 160px; 
  			height: 160px;
  			margin: 10px;
  			border: 1px solid red;
		}
		.modal-lg{ max-width: 1000px !important; }
		.overlay {
		  position: absolute;
		  bottom: 10px;
		  left: 0;
		  right: 0;
		  background-color: rgba(255, 255, 255, 0.5);
		  overflow: hidden;
		  height: 0;
		  transition: .5s ease;
		  width: 100%;
		}
		.image_area:hover .overlay {
		  height: 50%;
		  cursor: pointer;
		}
		.overtext {
		  color: #333;
		  font-size: 16px;
		  position: absolute;
		  top: 50%;
		  left: 50%;
		  -webkit-transform: translate(-50%, -50%);
		  -ms-transform: translate(-50%, -50%);
		  transform: translate(-50%, -50%);
		  text-align: center;
		}
    .hr-more {
      height: 24px;
      width: 140px;
      position: relative;
      left: 50%;
      right: 50%;
      top: -30px;
      margin-left:-70px;
      color: #5F6E86;
      font: normal 1em/14px;
      vertical-align: middle;
      text-align: center;
      border-radius: 4px;
      background-color: #DDDDDD;
    }
*/
</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <!-- 全頁框 -->
  <div class="wrapper">
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="../imageFiles/preloaderLOGO.png" alt="Fifth Season Life LOGO" height="60" width="60"> 
    </div>
    <!-- /Preloader -->
    <!-- 頁內全頁內容 -->
    <div class="content-wrapper">
      <!-- 頁頭導覽條 -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h4 class="m-0"><?php echo !empty($courseID) ? "編輯" : "新增"; ?>課程資料<span style="font-size:large;">【ID: <?php echo $courseID; ?>】</span></h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                  <li class="breadcrumb-item">首頁</li>
                  <li class="breadcrumb-item">課程管理</li>
                  <li class="breadcrumb-item prevLink" alt="返回列表" title="返回列表" onClick="window.location.replace('course_list.php?<?php echo $backUrl; ?>');">課程管理</li>
                <li class="breadcrumb-item active">課程詳細資料</li>
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
              <!-- Profile Image -->
              <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                  <div class="text-center fa-ul">
                    <div class="image_area">
                        <label for="upload_image">
                          <img id="uploaded_image" name="uploaded_image" style="border-radius:10px;" class="img-responsive img-fluid imgEDicon" src="<?php
                              if(!empty($res['advi_defaultimg'])) {
                                if(!empty($mdres['merch_id'])){
                                  $subFolder=ceil(intval($mdres['merch_id'])/100);
                                  if(file_exists("../uploadFiles/md/".$subFolder."/mdimg_".$mdres['merch_id']."_default_thumb.jpg")) echo "../uploadFiles/md/".$subFolder."/mdimg_".$mdres['merch_id']."_default_thumb.jpg?".time();
                                  else echo "../uploadFiles/imageFiles/nopic_400x300.jpg";
                                } else echo "../uploadFiles/imageFiles/nopic_400x300.jpg";
                              } else {
                                if(!empty($adviID)){
                                  if(file_exists("../uploadFiles/adv/adviphoto_".$adviID."_.jpg")) echo "../uploadFiles/adv/adviphoto_".$adviID."_.jpg?".time();
                                  else echo "../uploadFiles/imageFiles/nopic_400x300.jpg";
                                } else echo "../uploadFiles/imageFiles/nopic_400x300.jpg";
                              }
                              ?>" alt="廣告圖樣" title="廣告圖樣">
                          <!-- <div id="changeProfilePhoto" class="fa fa-2x fa-camera imgEDiconLi" data-toggle="modal" data-target="#changeProfilePhotoModal" alt="編輯頭像" title="編輯頭像"></div> -->
                        </label>
                    </div>
                  </div>
                  <h5 class="profile-main text-center"><?php echo !empty($mdres['merch_title']) ? $mdres['merch_title']: "&nbsp;"; ?></h5>
                  <p class="text-muted text-center"><?php echo !empty($mres['merch_subtitle']) ? $mres['merch_subtitle'] : "&nbsp;"; ?></p>

                  <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                      <b>預計起始日</b> <a class="float-right"><?php echo !empty($res['advi_startdate']) ? $res['advi_startdate'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>預計终止日</b> <a class="float-right"><?php echo !empty($res['advi_enddate']) ? $res['advi_enddate'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>選用套裝</b> <a class="float-right"><?php echo !empty($pres['advp_title']) ? $pres['advp_title'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>花費點數</b> <a class="float-right"><?php echo !empty($pres['advp_cost']) ? $pres['advp_cost'] : "&nbsp;"; ?></a>
                    </li>
                    <li class="list-group-item">
                      <b>申購日期</b> <a class="float-right" style="color:#cd5757;"><b><?php echo !empty($res['advi_published']) ? $mres['mb_published'] : "&nbsp;"; ?></b></a>
                    </li>
                  </ul>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
              <?php if(!empty($res['advi_status']) && intval($res['advi_status'])==6) { ?>
                <div id="butApply_group" name="butApply_group" class="form-group row">
                  <div class="col-sm-6">
                    <button type="button" id="but_doapply" name="but_doapply" class="btn btn-block bg-gradient-success btn-lg" style="cursor:pointer;height:100px;"><i class="nav-icon fas fa-2x fa-file-signature"></i><br>發佈執行</button>
                  </div>
                  <div class="col-sm-6">
                    <button type="button" id="but_doreject" name="but_doreject" class="btn btn-block bg-gradient-secondary btn-lg" style="cursor:pointer;height:100px;"><i class="nav-icon fas fa-2x fa-hand"></i><br>退回</button>
                  </div>
                </div>
              <?php } ?>

            </div>
            <!-- /.col -->
            <div class="col-md-9">
              <div class="card card-secondary">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link<?php if($tabIDX==1) echo " active"; ?>" href="#tabMenu1" data-index="1" data-toggle="tab" data-topbut="1">廣告申購資料</a></li>
                    <li class="nav-item"><a class="nav-link<?php if($tabIDX==2) echo " active"; ?>" href="#tabMenu2" data-index="2" data-toggle="tab" data-topbut="1">廣告圖樣管理</a></li>
                    <li class="nav-item"><a class="nav-link<?php if($tabIDX==3) echo " active"; ?>" href="#tabMenu3" data-index="3" data-toggle="tab" data-topbut="0">投放紀錄</a></li>
                    <li class="nav-item"><a class="nav-link<?php if($tabIDX==4) echo " active"; ?>" href="#tabMenu4" data-index="4" data-toggle="tab" data-topbut="0">搜尋置頂紀錄</a></li>
                    <li class="nav-item"><a class="nav-link<?php if($tabIDX==5) echo " active"; ?>" href="#tabMenu5" data-index="5" data-toggle="tab" data-topbut="0">有效點擊紀錄</a></li>
                  </ul>
                </div><!-- /.card-header -->
                <div class="card-body">
                  <div class="tab-content">
                    <!-- 第一分頁【主要】資料修改 -->
                    <div class="tab-pane<?php if($tabIDX==1) echo " active"; ?>" id="tabMenu1">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-2x fa-exclamation-circle"></i>&nbsp;&nbsp;會員登載申購的廣告套餐基本不作改動，除非所提供的素材違反人生第五季的廣告登載規定，而即使內容不符，也必須經由申購會員的同意始得代之修改，否則一律退回由會員本人自行處理後再提出申購。</div>
                      <hr>
                      <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/adv_act_func.php" method="post" data-formmode="infoupdate" data-func="func_adv_applyUpdate">
                        <div class="card-body">
                          <div class="form-group row">
                              <div class="col-sm-3">
                                  <div class="image_area">
                                      <label for="upload_image">
                                      <img id="uploaded_image" name="uploaded_image" style="border-radius:10px;" class="img-responsive img-fluid imgEDicon" src="<?php
                                        if(!empty($pres['advp_id'])){
                                          $advpThumb = "../uploadFiles/adv/advpphoto_".$pres['advp_id']."_thumb.jpg";
                                          if(file_exists($advpThumb)) echo $advpThumb."?".time();
                                          else echo "../imageFiles/nopic_400x300.jpg";
                                        } else echo "../imageFiles/nopic_400x300.jpg";
                                      ?>" alt="廣告套餐代表圖" title="廣告套餐代表圖"/>
                                      </label>
                                  </div>
                              </div>
                              <div class="col-sm-9">
                                  <div class="form-group row">
                                      <label for="advp_title" class="col-sm-2 col-form-label">套餐名稱</label>
                                      <div class="col-sm-10">
                                      <input type="text" class="form-control" id="advp_title" name="advp_title" value="<?php echo $pres['advp_title'] ?? ""; ?>" placeholder="套餐名稱" readonly>
                                      </div>
                                  </div>
                                  <div class="form-group row">
                                      <label for="advp_subscript" class="col-sm-2 col-form-label">套餐下標</label>
                                      <div class="col-sm-10">
                                      <input type="text" class="form-control" id="advp_subscript" name="advp_subscript" value="<?php echo $pres['advp_subscript'] ?? ""; ?>" placeholder="套餐下標" readonly>
                                      </div>
                                  </div>
                                  <div class="form-group row">
                                      <label for="advp_type" class="col-sm-2 col-form-label">類別</label>
                                      <div class="col-sm-10">
                                        <input type="text" class="form-control" id="advp_type" name="advp_type" value="<?php echo $advJS['type'][$pres['advp_type']] ?? ""; ?>" placeholder="類別" readonly>
                                      </div>
                                  </div>
                                  <div class="form-group row">
                                      <label for="advp_period" class="col-sm-2 col-form-label">週期</label>
                                      <div class="input-group col-sm-4">
                                          <input type="text" class="form-control" id="advp_period" name="advp_period" value="<?php echo $pres['advp_period'] ?? ""; ?>" placeholder="週期" readonly>
                                          <div class="input-group-append">
                                              <span class="input-group-text areaUnit">日</span>
                                          </div>
                                      </div>
                                      <label for="advp_cost" class="col-sm-2 col-form-label">花費點數</label>
                                      <div class="input-group col-sm-4">
                                          <input type="text" class="form-control" id="advp_cost" name="advp_cost" value="<?php echo $pres['advp_cost'] ?? "0"; ?>" placeholder="花費點數" readonly>
                                          <div class="input-group-append">
                                              <span class="input-group-text areaUnit">點</span>
                                          </div>
                                      </div>
                                  </div>
                                </div>
                          </div>
                          <hr>
                          <div class="form-group row">
                            <label for="advi_startdate" class="col-sm-2 col-form-label">預計起始日</label>
                            <div class="input-group date col-sm-4" id="Rstartdate" data-target-input="nearest">
                                <input type="text" id="advi_startdate" name="advi_startdate" value="<?php echo $res['advi_startdate'] ?? ""; ?>" class="form-control datetimepicker-input" data-target="#reservationdate" placeholder="預計起始日"/>
                                <div class="input-group-append" data-target="#Rstartdate" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                            <label for="advi_enddate" class="col-sm-2 col-form-label">預計终止日</label>
                            <div class="input-group date col-sm-4" id="Renddate" data-target-input="nearest">
                                <input type="text" id="advi_enddate" name="advi_enddate" value="<?php echo $res['advi_enddate'] ?? ""; ?>" class="form-control datetimepicker-input" data-target="#reservationdate" placeholder="預計终止日"/>
                                <div class="input-group-append" data-target="#Renddate" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="advi_advtype" class="col-sm-2 col-form-label">廣告標的課程商品</label>
                            <div class="col-sm-10">
                            <?php
                              $i=1;
                              foreach($advJS['advtype'] as $key => $value){
                                  if($key==""&&$key!="0") continue;
                                  echo '<div class="icheck-primary d-inline">';
                                  echo '<input type="radio" id="advi_advtype'.$i.'" name="advi_advtype" value="'.$key.'"';
                                  if(!empty($res['advi_advtype']) && intval($res['advi_advtype'])==$key) echo " checked";
                                  else if($i==1) echo " checked";
                                  echo '>';
                                  echo '<label for="advi_advtype'.$i.'" class="font-weight-light">'.$value.'&nbsp;&nbsp;&nbsp;&nbsp;</label>';
                                  echo '</div>';
                                  $i++;
                              }
                            ?>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="merch_id" class="col-sm-2 col-form-label">站內房地課程商品</label>
                            <div class="col-sm-10">
                                <select class="form-control" id="merch_id" name="merch_id"<?php if(!empty($res['advi_advtype']) && intval($res['advi_advtype'])!=1) { echo " disabled='disabled'"; } ?>>
                                  <option value="">-請選擇站內房地課程商品-</option>
                                  <?php
                                      $mdsql = "SELECT merch_id, merch_title, merch_add FROM merchandise WHERE mb_id=?";
                                      $mdsr = @$DB->query($mdsql,array($mres['mb_id']));
                                      $i = 0; 
                                      $n = count($mdsr);
                                      while($i<$n){
                                          echo '<option value="'.$mdsr[$i]['merch_id'].'"';
                                          if($mdsr[$i]['merch_id']==@$mdres['merch_id']){ echo " selected"; }
                                          echo '>'.$mdsr[$i]['merch_title'].'('.$mdsr[$i]['merch_add'].')</option>';
                                          $i++;
                                      }
                                  ?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="sv_id" class="col-sm-2 col-form-label">開放式服務課程商品</label>
                            <div class="col-sm-10">
                                <select class="form-control" id="sv_id" name="sv_id"<?php if(!empty($res['advi_advtype']) && intval($res['advi_advtype'])!=2) { echo " disabled='disabled'"; } ?>>
                                  <option value="">-請選擇開放式服務課程商品-</option>
                                  <?php
                                      $svsql = "SELECT * FROM sv_service WHERE mb_id=?";
                                      $svsr = @$DB->query($svsql,array($mres['mb_id']));
                                      $i = 0; 
                                      $n = count($svsr);
                                      while($i<$n){
                                          echo '<option value="'.$svsr[$i]['sv_id'].'"';
                                          echo '>【'.$svJS['svtype'][$svsr[$i]['sv_type']].'】【'.$svJS['svcate'][$svsr[$i]['sv_cate']].'】'.$svsr[$i]['sv_title'].'</option>';
                                          $i++;
                                      }
                                  ?>
                                </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="advi_outsite" class="col-sm-2 col-form-label">站外網頁</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="advi_outsite" name="advi_outsite" value="<?php echo $res['advi_outsite'] ?? ""; ?>" placeholder="站外網頁"<?php if(!empty($res['advi_advtype']) && intval($res['advi_advtype'])!=3) { echo " disabled='disabled'"; } ?>>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="advi_slogan" class="col-sm-2 col-form-label">廣告詞</label>
                            <div class="col-sm-10">
                                <textarea id="advi_slogan" name="advi_slogan" style="width:100%;"><?php echo $res['advi_slogan'] ?? ""; ?></textarea>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="advi_status" class="col-sm-2 col-form-label">廣告申購狀態</label>
                            <div class="col-sm-10">
                              <select class="form-control" id="advi_status" name="advi_status"<?php echo NdisAble; ?>>
                                  <?php
                                    foreach($advJS['status'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key==""||$key==1){ echo " disabled"; }
                                        if(isset($res['advi_status'])){
                                            if($res['advi_status']==4 && ($key==0||$key==2||$key==6)){ echo " disabled"; }
                                            if(!is_null($res['advi_status']) && $res['advi_status']==$key) echo " selected";
                                        }
                                        echo '>'.$value.'</option>';
                                    }
                                  ?>
                              </select>
                            </div>
                          </div>
                          <hr style="margin-top:46px;"><div class="hr-more">廣告樣圖</div>
                          <div class="form-group row">
                            <div class="col-sm-4">
                              <div style="margin-bottom:16px;">廣告立式圖樣 400x300</div>
                              <div class="area-upload">
                                  <div class="wrap-custom-file" style="margin-left:-8px;">
                                      <input type="file" name="imgUpload_S" id="imgUpID_S" accept="jpg" />
                                      <label for="imgUpID_S" fileext="" class="file-ok" alt="上傳廣告圖樣" title="上傳廣告圖樣" style="background-size:cover;background-image:url('<?php
                                      $hasImg=false;
                                      /*
                                      if(!empty($res['advi_defaultimg'])) {
                                        if(!empty($mdres['merch_id'])){
                                          $subFolder=ceil(intval($mdres['merch_id'])/100);
                                          if(file_exists("../uploadFiles/md/".$subFolder."/mdimg_".$mdres['merch_id']."_default_thumb.jpg")) echo "../uploadFiles/md/".$subFolder."/mdimg_".$mdres['merch_id']."_default_thumb.jpg?".time();
                                          else echo "../uploadFiles/imageFiles/nopic_400x300.jpg";
                                        } else echo "../uploadFiles/imageFiles/nopic_400x300.jpg";
                                      } else {
                                      */
                                        if(!empty($adviID)){
                                          $imgPath = "../uploadFiles/adv/adviphoto_".$adviID."_.jpg";
                                          if(file_exists($imgPath)) {
                                            echo $imgPath.'?'.time();
                                            $hasImg=true;
                                          }
                                          else echo "../uploadFiles/imageFiles/nopic_400x300.jpg";
                                        } else echo "../uploadFiles/imageFiles/nopic_400x300.jpg";
                                      /*
                                      }
                                      */
                                      ?>')">
                                          <i class="fas fa-2x fa-window-close<?php if($hasImg) echo " funcClose"; ?>" alt="移除图樣" title="移除图樣"></i>
                                      </label>
                                  </div>
                                  <div class="icheck-primary d-inline">
                                      <input type="checkbox" id="advi_defaultimg" name="advi_defaultimg" value="1">
                                      <label for="advi_defaultimg" class="font-weight-light">複製課程商品預設主圖</label>
                                  </div>
                              </div>
                                
                            </div>
                            <div class="col-sm-8">
                              <div style="margin-bottom:16px;">廣告橫幅 1140x340</div>
                              <div class="area-upload">
                                  <div class="wrap-custom-file" style="width:480px;height:143px;">
                                      <input type="file" name="imgUpload_B" id="imgUpID_B" accept="jpg" />
                                      <label for="imgUpID_B" fileext="" class="file-ok" alt="上傳廣告圖樣" title="上傳廣告圖樣" style="background-size:cover;background-image:url('<?php
                                      $hasBanner=false;
                                        if(!empty($adviID)){
                                          $imgPath = "../uploadFiles/adv/adviphoto_".$adviID."_banner.jpg";
                                          if(file_exists($imgPath)) {
                                            echo $imgPath.'?'.time();
                                            $hasBanner=true;
                                          }
                                          else echo "../uploadFiles/imageFiles/nopic_1140x340.jpg";
                                        } else echo "../uploadFiles/imageFiles/nopic_1140x340.jpg";
                                      ?>')">
                                          <i class="fas fa-2x fa-window-close<?php if($hasBanner) echo " funcClose"; ?>" alt="移除图樣" title="移除图樣"></i>
                                      </label>
                                  </div>
                              </div>
                                
                            </div>
                          </div>

                          <hr>
                        </div>
                        <!-- /.card-body -->
                      </form>
                    </div>
                    <!-- /第一分頁【主要】資料修改 -->
                    <!-- 第二分頁【主要】主體編輯 -->
                    <div class="tab-pane<?php if($tabIDX==2) echo " active"; ?>" id="tabMenu2">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                      <i class="nav-icon fas fa-2x fa-exclamation-circle"></i>&nbsp;&nbsp;Html 編輯器使用 SunEditor 2.45.1 版本，並依照 MIT 授權條款內容行使權利及義務。</div>
                      <hr>
                      <form id="form_tabMenu2" name="form_tabMenu2" action="../funcFiles/adv_act_func.php" method="post">
                        <div class="card-body">


                        
                        </div>
                      </form>  
                    </div>
                    <!-- /第二分頁【主要】主體編輯 -->
                    <!-- 第三分頁投放紀錄 -->
                    <div class="tab-pane<?php if($tabIDX==3) echo " active"; ?>" id="tabMenu3">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                      <i class="nav-icon fas fa-2x fa-exclamation-circle"></i>&nbsp;&nbsp;xxxx。</div>
                      <hr>
                      <!-- /.card-header -->
                      <div class="card-body">


                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第三分頁投放紀錄 -->
                    <!-- 第四分頁搜尋置頂紀錄 -->
                    <div class="tab-pane<?php if($tabIDX==4) echo " active"; ?>" id="tabMenu4">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-2x fa-exclamation-circle"></i>&nbsp;&nbsp;xxxx。</div>
                      <hr>
                      <!-- card-body -->
                      <div class="card-body">

                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第四分頁搜尋置頂紀錄 -->
                    <!-- 第五分頁有效點擊紀錄 -->
                    <div class="tab-pane<?php if($tabIDX==5) echo " active"; ?>" id="tabMenu5">
                      <div style="font-size:0.85em;line-height:1.4em;">
                      <i class="nav-icon fas fa-2x fa-exclamation-circle"></i>&nbsp;&nbsp;xxxx。</div>
                      <hr>
                      <!-- card-body -->
                      <div class="card-body">

                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第五分頁有效點擊紀錄 -->
                  </div>
                  <!-- /.tab-content -->
                </div><!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div><!-- /.container-fluid -->

        <!-- -------------------- 裁切頭像彈出視窗 -------------------- -->
        <?php //include('include_avatarmodal.php'); ?>
        <!-- -------------------- /裁切頭像彈出視窗 -------------------- -->
        <!-- -------------------- 審核建議彈出視窗 -------------------- -->
        <div id="reviewModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="background:rgba(0,0,0,0.75);">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <!-- Header -->
              <div class="modal-header">
                <h4 class="modal-title">駁回廣告申購建議說明</h4>
                <button type="button" id="modalCloseTop" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <!-- /Header -->
              <!-- Model Body -->
              <div class="modal-body">
                <h6 class="modal-des"></h6>
                <form id="form_addreview" name="form_addreview" data-ruleid="" action="../funcFiles/adv_act_func.php" method="post">
                  <div class="modal-container"><textarea id="advv_content" name="advv_content" rows="6" style="width:100%;"></textarea></div>
                </form>
              </div>
              <!-- /Model Body -->
              <!-- Footer -->
              <div class="modal-footer justify-content-between">
                <button type="button" id="modalCloseBut" class="btn btn-default" data-dismiss="modal">關閉</button>
                <button type="button" id="revUpBut" name="revUpBut" class="btn btn-primary">執行</button>
              </div>

              <!-- /Footer -->
            </div>
          </div>
        <!-- /.modal-dialog -->
        </div>
        <!-- -------------------- /審核建議彈出視窗 -------------------- -->
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
<!-- Tempusdominus Bootstrap 4 -->
<script src="../pluginFiles/moment/moment.js"></script>
<script src="../pluginFiles/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- DataTables  & Plugins -->
<script src="../pluginFiles/datatables/datatables/jquery.dataTables.min.js"></script>
<script src="../pluginFiles/datatables/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../pluginFiles/datatables/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<!-- ezValidation -->
<script src="../pluginFiles/validator/validator.js<?php echo '?t='.time(); ?>"></script>
<!-- Dropzone JS -->
<script src="../pluginFiles/dropzone/dropzone.js"></script>
<!-- Cropper JS -->
<script src="../pluginFiles/cropper/cropper.js"></script>
<!-- suneditor -->
<script src="../pluginFiles/suneditor/dist/suneditor.min.js"></script>
<script src="../pluginFiles/suneditor/sample/js/common.js"></script>
<script src="../pluginFiles/suneditor/src/lang/zh_tw.js"></script>
<!-- codemirror -->
<script src="../pluginFiles/codemirror/lib/codemirror.min.js"></script>
<script src="../pluginFiles/codemirror/mode/htmlmixed/htmlmixed.js"></script>
<script src="../pluginFiles/codemirror/mode/xml/xml.js"></script>
<script src="../pluginFiles/codemirror/mode/css/css.js"></script>
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/fileupload.js<?php echo '?t='.time(); ?>"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

<!-- ------------------------ /Scripts Link ------------------------ -->
<script type="text/javascript">
  var mainobj = <?php echo json_encode($advJS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
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
    ////////////////////////////////////////////////////////////
    //Date picker
    $('#Rstartdate,#Renddate').datetimepicker({
      format: 'L'
    });
    ////////////////////////////////////////////////////////////

    $('input[name^="imgUpload_"]').change(function(event){
        var ev = event.target.files[0],
            tmppath = URL.createObjectURL(ev),//取得圖檔暫存路徑
            fSplit = ev.name.split('.'),//
            fName = fSplit[0],//
            fExt = fSplit[1],//
            fSize = Math.round(ev.size / 1024),//取得準備上傳的檔案大小（Kb）
            showPopup = tmppath;
        $(this).next().css("background-image", "url('"+showPopup+"')"); 
        $(this).next().find('i').addClass('funcClose'); 
        if($(this).attr('id')=='imgUpload_S'){$('#advi_defaultimg').prop( "checked", false );}
        return false;

      });
    //////////////////////////////////////////////////////////
    $("input[name='advi_advtype']").change(function(e){
        switch($(this).val()){
            case "1": 
                $('#merch_id').removeAttr("disabled");
                $('#sv_id').val('').prop('disabled', 'disabled');
                $('#advi_outsite').val('').prop('disabled', 'disabled');
            break;
            case "2":
                $('#merch_id').val('').prop('disabled', 'disabled')
                $('#sv_id').removeAttr("disabled");;
                $('#advi_outsite').val('').prop('disabled', 'disabled');
            break;
            case "3":
                $('#merch_id').val('').prop('disabled', 'disabled')
                $('#sv_id').val('').prop('disabled', 'disabled');
                $('#advi_outsite').removeAttr("disabled");
            break;
        }

    });

    $('#merch_id,#sv_id').on('change', function(){
        var toID = Number($(this).val());
        if(toID >0){
            var isFrom = $(this).prop('id');
            if(isFrom=='merch_id'){
                var subFolder=Math.ceil(toID/100);
                var defImgPath = "../uploadFiles/md/"+subFolder+"/mdimg_"+toID+"_0_thumb.jpg";
            } else if(isFrom=='sv_id'){
                var defImgPath = "../uploadFiles/sv/svphoto_"+toID+"_thumb.jpg";
            }
            if($('#advi_defaultimg').is(":checked")==true){
                checkIfFileLoaded(defImgPath).on("error", function(e) {
                        alert('這個課程商品沒有預設主圖，請另外上傳廣告圖樣，或是更換推廣課程商品！');
                        clearImage('S');
                        $('#advi_defaultimg').prop( "checked", false );
                    })
                    .on("load", function(e) {
                        armImageS(defImgPath);
                    });
            }
        } else {
            clearImage('S');
            $('#advi_defaultimg').prop( "checked", false );
        }

    });

    $('.wrap-custom-file label i').click(function() { 
        if(confirm('此動作將會刪除這個已經上傳的檔案，確定執行刪除？！')){
            var eDefault='';
            var eImg = $(this).parent();
            var eFile = eImg.parent().find('input:file');
            if(eFile.prop('id')=='imgUpID_S'){ eDefault='../uploadFiles/imageFiles/nopic_400x300.jpg'; }
            else{ eDefault='../uploadFiles/imageFiles/nopic_1140x340.jpg'; }
            eImg.removeClass('file-ok').css('background-image','url("'+eDefault+'")');//清除圖像以及調整圖檔的Class
            $(this).removeClass('funcClose'); 
            eFile.val('');//清空File Browser中的檔案連結
        }
        return false;
    });

    $('#advi_defaultimg').on('change', function(){
        var isCHK = $(this).is(":checked");
        clearImage('S');

        if(isCHK){
            var isFromEle='';
            var defID = 0;
            var defTXT = '';
            var defImgPath = '';
            if(Number($('#merch_id').val())>0){
                isFromEle = 'merch_id';
                defID = Number($('#merch_id').val());
                defTXT = '課程商品';
                var subFolder=Math.ceil(defID/100);
                defImgPath = "../uploadFiles/md/"+subFolder+"/mdimg_"+defID+"_0_thumb.jpg";
            } else {
                if(Number($('#sv_id').val())>0){
                    isFromEle = 'sv_id';
                    defID = Number($('#sv_id').val());
                    defTXT = '服務';
                    defImgPath = "../uploadFiles/sv/svphoto_"+defID+"_thumb.jpg";
                }
            }
            if(defID==0){
                alert('請在列表中選擇一個推廣標的！');
            } else {
                checkIfFileLoaded(defImgPath).on("error", function(e) {
                    alert('這個'+defTXT+'沒有預設主圖，請另外上傳廣告圖樣，或是更換推廣標的！');
                    clearImage('S');
                    $('#advi_defaultimg').prop( "checked", false );
                })
                .on("load", function(e) {
                    armImageS(defImgPath);
                });
            }
        } else {
            alert('請上傳一個廣告圖檔！');
        }
        
    });

    function checkIfFileLoaded(src) {
        return $("<img>").attr('src', src);
    }
    function clearImage(imG) {
        var eImg = $('#imgUpID_'+imG).next();
        var eFile = eImg.parent().find('input:file');
        var noPic;
        if(imG == 'S'){ noPic = "../uploadFiles/imageFiles/nopic_400x300.jpg"; }
        else if(imG == 'B'){ noPic = "../uploadFiles/imageFiles/nopic_1140x340.jpg"; }
        eImg.removeClass('file-ok').css('background-image','url("'+noPic+'")');//清除圖像以及調整圖檔的Class
        eImg.find('i').removeClass('funcClose'); 
        eFile.val('');//清空File Browser中的檔案連結
       
    }
    function armImageS(fPath) {
        var eImg = $('#imgUpID_S').next();
        var eFile = eImg.parent().find('input:file');
        eImg.addClass('file-ok').css('background-image','url("'+fPath+'")');//清除圖像以及調整圖檔的Class
        eImg.find('i').addClass('funcClose'); 
        eFile.val('');//清空File Browser中的檔案連結
    }


  ////////////////////////////////////////////////////////////
  /*
    var $modal = $('#avatarModal');
    var image = document.getElementById('sample_image');
    var cropper;
    $('#upload_image').change(function(event){
      var files = event.target.files;
      var done = function (url) {
          image.src = url;
          $modal.modal('toggle');
      };
      if (files && files.length > 0){
            reader = new FileReader();
            reader.onload = function (event) {
                done(reader.result);
            };
            reader.readAsDataURL(files[0]);
      }
    });
    $modal.on('shown.bs.modal', function() {
      cropper = new Cropper(image, {
        aspectRatio: 4/3,
        viewMode: 1,
        preview: '.preview'
      });
    }).on('hidden.bs.modal', function() {
      cropper.destroy();
      cropper = null;
    });

    $("#crop").click(function(){
      canvas = cropper.getCroppedCanvas({
          width: 800,
          height: 600,
      });
      canvas.toBlob(function(blob) {
          var reader = new FileReader();
          reader.readAsDataURL(blob); 
          reader.onloadend = function() {
              var base64data = reader.result;  
              $.ajax({
                url: "../funcFiles/adv_act_func.php",
                  method: "POST",                	
                    data: {
                            image : base64data, 
                            formAcce : 'form_photoupdate', 
                            token : getTokenVal(), 
                            adviID : '<?php echo $adviID; ?>'
                          },
              dataType: 'json',
                  async: false, 
                  success: function(data){
                      ajaxRTN(data,'form_photoupdate');
                      $modal.modal('hide');
                      if(data['RTN']==1 && data['imgPATH']!=''){
                        $('#uploaded_image').prop('src', data['imgPATH']);
                      }
                    },
                    error: function (data) {
                      ajaxRTN(data,'form_photoupdate');
                      $modal.modal('hide');
                    }
                });
          }
        }, 'image/jpeg', 0.75);//將預設轉存PNG格式改為JPG（壓縮檔案更小）
      });
    */
    // --------------------------- 各式按鈕行為 --------------------- //
    $('.nav-pills a.nav-link').click(function(e){ 
      var getTarget = $(this).prop("href").split('#');
      switch(getTarget[1]){
          case 'tabMenu1':
            $('.nav-toppanel').show();
          break;
          case 'tabMenu2':
            $('.nav-toppanel').show();
          break;
          default:
            $('.nav-toppanel').hide();
          break;
      }
    });
/*      
    window.parent.$('#topBut_save').click(function(e){ 
      e.preventDefault();
      if(!$(this).hasClass('active')){ return false;}
      var getFormPost = $('.tab-pane.active').prop("id");
      if (confirm('當按下按鍵繼續進行操作時，即代表同意遵循「人生第五季管理辦法」進行操作！\r\r確定進行資料儲存操作？！')){
        if(getFormPost=='tabMenu2'){ ajaxPOST($('#form_tabMenu2')); }
        else{ 
          $('#form_'+ getFormPost).submit(); 
        }
      }
    });
    window.parent.$('#topBut_reset').click(function(e){
      var getFormPost = $('.tab-pane.active').prop("id");
      if (confirm('確定重置這個表單資料？！')){
        $('#form_'+ getFormPost)[0].reset();
        window.parent.$('#topBut_save').removeClass('active');
      }
    });
    window.parent.$('#topBut_delete').click(function(e){ 
      var getFormPost = $('.tab-pane.active').prop("id");
      if (confirm('這個操作將刪除這個頁面的資料，以及其相關聯的所有資料表記錄，部份刪除的資料將置入垃圾桶內，並有保留期限！\r\r確定刪除此筆資料？！')){
        let recomF = prompt("請輸入安全驗證碼");
        if (recomF == "我是豬") {
          $.ajax({
              type: 'post',
              url: '../funcFiles/adv_act_func.php',
              data: {
                'arrDatA': {}, 
                'formAcce':'form_delete',
                'token': getTokenVal(), 
                'adviID':'<?php echo $adviID; ?>'
              },
              dataType: 'json',                //data format  
              async: false, 
            success: function (data) {
                if(data['RTN']==1){
                  window.location.replace("advp_list.php?msg=5&doneid=<?php echo $adviID; ?>");
                } else {
                  ajaxRTN(data,'form_delete','error');
                }
              },
              error: function (data) {
                ajaxRTN(data,'form_delete','error');
              }
          });
        } else {
          alert('安全驗證碼錯誤！！');
        }
      }
    });
    */
    //-- 廣告申購審核通過
    $('#but_doapply').click(function(e){ 
      $.ajax({
              type: 'post',
              url: '../funcFiles/adv_act_func.php',
              data: {
                'arrDatA': {}, 
                'formAcce':'form_doapply',
                'token': getTokenVal(), 
                'adviID':'<?php echo $adviID; ?>',
                'advpID':'<?php echo !empty($pres['advp_id']) ? $pres['advp_id']:0; ?>'
              },
              dataType: 'json',
              async: false, 
              beforeSend:function(){
                var comF = '這個動作將會無修改同意此份廣告申購案件，並且直接生成廣告的投放執行紀錄，由系統按投放規則自動執行，且同時扣除相應的會員點數，管理者必須確實查驗文稿內容以及所附附件的正確性、完整性以及合適性。\r\n\r\n確定同意並執行此份廣告申購案件？！';
                if(!confirm(comF)){return false;}
              },
              success: function (data) {
                if(data['RTN']==1){
                  window.parent.toastr['success'](data['msgStr']);
                  //-- 更改當前頁面的相關數值
                 $('#butApply_group').hide();
                 var newNum = parseInt($('#badge_<?php echo PAGE_MARK; ?>').html())-1;
                 $('#badge_<?php echo PAGE_MARK; ?>').html(newNum);

                } else {

                   window.parent.toastr['error'](data['msgStr']);
                }
              },
              error: function (data) {
                ajaxRTN(data,'form_doapply','error');
              }
          });
    });
    //-- 廣告申購審核駁回
    $('#revUpBut').click(function(e){ 
      $.ajax({
              type: 'post',
              url: '../funcFiles/adv_act_func.php',
              data: {
                'arrDatA': {
                  advi_id: '<?php echo $adviID; ?>',
                  mb_id: '<?php echo !empty($mres['mb_id']) ? $mres['mb_id'] : 0; ?>',
                  ad_id: '<?php echo CF_AD_ID; ?>',
                  advv_content: $('textarea#advv_content').val(),
                  advv_step:'4'
                }, 
                'formAcce':'form_doreject',
                'token': getTokenVal(), 
                //'advpID':'<?php echo !empty($pres['advp_id']) ? $pres['advp_id'] : 0; ?>',
                'adviID':'<?php echo $adviID; ?>'
              },
              dataType: 'json',
              async: false, 
              beforeSend:function(){
                var comF = '這個動作將會駁回此份廣告申購案件，同時退回預扣的點數，並且無法再次使用這筆紀錄。\r\n\r\n確定駁回此份廣告申購案件？！';
                if(!confirm(comF)){return false;}
              },
              success: function (data) {
                if(data['RTN']==1){
                    window.parent.toastr['success'](data['msgStr']);
                    //-- 更改當前頁面的相關數值
                  $('#butApply_group').hide();
                  $('#advv_content').html('');
                  $('#reviewModal').removeClass('show').fadeOut(200);
                  var newNum = parseInt($('#badge_<?php echo PAGE_MARK; ?>').html())-1;
                  if(newNum==0){ newNum=""; }
                  $('#badge_<?php echo PAGE_MARK; ?>').html(newNum);
                  $('#advi_status').val("4").change();
                } else {
                   window.parent.toastr['error'](data['msgStr']);
                }
              },
              error: function (data) {
                ajaxRTN(data,'form_doreject','error');
              }
          });
    });


    $('#but_doreject').click(function(e){ 
      //var currID=$(this).attr("data-advrid");
      //var currURI=$(this).attr("data-uri");
      //var ruleNum=$(this).attr("data-ruleno");
      $('#reviewModal').fadeIn(200).addClass('show');
      //$('#reviewModal .modal-title').html('編輯投放規則'+ruleNum);
      //$('#reviewModal #revUpBut').html('更新');
      //$('#reviewModal #revDelBut').css("visibility", "visible");
      //$('#form_ruleupdate').attr('data-ruleid',currID);
      //$('#reviewModal .modal-container').load('include_adrules.php?'+currURI);
    });
    //----------------- 彈出窗關閉按鍵 -----------------------
    $('#reviewModal #modalCloseTop, #reviewModal #modalCloseBut').click(function(e){ 
      //$('#reviewModal #modal-title').html('');
      //$('#reviewModal .modal-container').html('');
      $('#advv_content').html('');
      $('#reviewModal').removeClass('show').fadeOut(200);
    });

  /** ------------------更改資料輸入限制----------------------- **/
  /*
  $('#form_tabMenu1').validate({
    submitHandler: function (form) {
        ajaxPOST($('#form_tabMenu1'));
        //form.submit();
      },
      rules: {
        advi_startdate: { required: true },
        advi_enddate: { required: true }
      },
      messages: {
        advi_startdate: { required: "請輸入起始日期" },
        advi_enddate: { required: "請輸入終止日期" }
      },
      errorElement: 'div',
      errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        error.addClass('torightSide');
        element.closest('.form-group').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
      }
  });
  */
  /** ------------------------------------------------------- **/
  ////////////////////// DataTable表格初始化 //////////////////////////////
  $('#loTableList').DataTable({
      "paging": true,
      "pagingType": "full_numbers",
      "lengthChange": false,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
      //"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
      //"select": true,
      //"keys": false,
      //"colReorder": true,
      language: {
        "lengthMenu": "顯示 _MENU_ 筆搜尋紀錄",
        "sProcessing": "處理中...",
        "sZeroRecords": "没有匹配结果",
        "sInfo": "目前有 _MAX_ 筆搜尋紀錄",
        "sInfoEmpty": "目前共有 0 筆紀錄",
        "sInfoFiltered": " ",
        "sInfoPostFix": "",
        "sSearch": "在搜尋紀錄中尋找:",
        "sUrl": "",
        "sEmptyTable": "尚未有資料紀錄存在",
        "sLoadingRecords": "載入資料中...",
        "sInfoThousands": ",",
        "oPaginate": {
            "sFirst": "首頁",
            "sPrevious": "上一頁",
            "sNext": "下一頁",
            "sLast": "末頁"
        },
        "order": [[0, "desc"]],
        "oAria": {
            "sSortAscending": ": 以升序排列此列",
            "sSortDescending": ": 以降序排列此列"
        }
      }
    });
  //////////////////// 表單異動行為 ////////////////////////////
  ////////////////////////////////////////////////////////////
  $('form :input').on('change input', function() {
    if(CHGform.serialize() !== ORGForm){
      window.parent.$('#topBut_save').addClass('active');
     }
    else { 
      window.parent.$('#topBut_save').removeClass('active');
     }
  });
  //  /表單異動行為


//////////////////// SUNEDITOR ////////////////////////////

/////////////////////////////////////////////////////////////


});

  ////////////////////////////////////////////////////////////////     
  // ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
  // -------------- 可接收include檔案要求後自定義動作 ------------- //
  ////////////////////////////////////////////////////////////////
  // 當「儲存」按鈕按下時的動作
  function doButSave(){
	  var frm = $('#form_'+$('.tab-pane.active').prop("id"));
    var accE = frm.data('func');
    var getValid = Validator.validate(frm);
    if(!getValid){
      return false;
    } else {
      ajaxPOST(frm);
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
        url: '../funcFiles/adv_act_func.php',
        data: {
          'arrDatA': {}, 
          'formAcce':'func_advi_delete',
          'token': getTokenVal(), 
          'adviID':'<?php echo $adviID; ?>'
        },
        dataType: 'json',//data format  
        async: false, 
      success: function (data) {
          if(data['RTN']==1){
            window.location.replace("adv_packlist.php");
          } else {
            window.parent.toastr[data['msgType']](data['msgStr']);
          }
        },
        error: function (data) {
          console.log(data);
          window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
        }
    });
  }
  function doReDirect(){
    let redirURL = '<?php echo bkRedir; ?>';
    if(redirURL=='') { return false; }
    window.location.replace(redirURL);
  }

  ///////////////////////////// AJAX 表單處理 ////////////////////////////////
  //-------------------------- form_tabMenu1 等  ------------------------------//
  function ajaxPOST(frm){
      var accE = frm.data('func');
      var url = frm.prop('action');
      var typE = frm.prop('method');
      var datA,datB;
      if(accE=='func_adv_applyUpdate'){
        datA = {
          'advp_id': '<?php echo !empty($pres['advp_id']) ? $pres['advp_id'] : 0; ?>',
          'mb_id': '<?php echo !empty($mres['mb_id']) ? $mres['mb_id'] : 0; ?>',
          'merch_id': $('#merch_id').val(),
          'sv_id': $('#sv_id').val(),
          'advi_advtype': $('input[name="advi_advtype"]:checked').val(),
          'advi_outsite': $('#advi_outsite').val(),
          'advi_slogan': $('#advi_slogan').val(),
          'advi_defaultimg': $('#advi_defaultimg').is(':checked') ? '1' : '0',
          'advi_startdate': $('#advi_startdate').val(),
          'advi_enddate': $('#advi_enddate').val(),
          'advi_limitmode': '<?php echo !empty($pres['advi_limitmode']) ? $pres['advi_limitmode'] : 0; ?>',
          'advi_visitlimit': '<?php echo !empty($pres['advp_visitlimit']) ? $pres['advp_visitlimit'] : 0; ?>',
          'advi_status': $('#advi_status').val()
        }
      }
      /*
      else if(accE=='form_tabMenu2'){
        var datA = $('#advp_body').val();
      }
      */
      $.ajax({
          type: typE,
          url: url,
          data: {
            'arrDatA':datA, 
            //'arrDatB':datB, 
            'formAcce':accE,
            'token': getTokenVal(), 
            'adviID':'<?php echo $adviID; ?>',
            'advpID':'<?php echo !empty($pres['advp_id']) ? $pres['advp_id'] : 0; ?>'
          },
          dataType: 'json',
          async: false, 
          beforeSend:function(){//送出前的動作
              //if(!confirm(comF)){return false;}
          },
        success: function (data) {
          //console.log("SUCCESS: " + data);
          ajaxRTN(data,accE,'success');
          },
          error: function (data) {
            //console.log("ERROR: " + data);
            ajaxRTN(data,accE,'error');
          }
      });
  }

  function ajaxRTN(datA,acCE,rE){
    window.parent.toastr[datA['msgType']](datA['msgStr']);
    if(acCE=='form_tabMenu1'){
        if (datA['RTN']==1) {
          var newType = mainobj[$('#advp_type').val()];
          var newSta = mainobj[$('#advp_status').val()];
          switch (newSta){
            case '0': 
              $('.box-profile > ul > li:nth-child(1) > a').addClass( "text-danger" );
            break;
            case '1': 
              $('.box-profile > ul > li:nth-child(1) > a').removeClass( "text-success" );
            break;
            case '2': 
              $('.box-profile > ul > li:nth-child(1) > a').removeClass( "text-warning" );
            break;
            case '3': 
              $('.box-profile > ul > li:nth-child(1) > a').removeClass( "text-warning" );
            break;
          }
          $('.profile-main').html($('#advp_title').val());
          $('.profile-main + p').html($('#advp_subscript').val());
          $('.box-profile > ul > li:nth-child(1) > a').html(newType);
          $('.box-profile > ul > li:nth-child(2) > a').html(newSta);
          $('.box-profile > ul > li:nth-child(3) > a').html(datA['upTime']);
        } else {
          $('#form_tabMenu1')[0].reset();   
        }
        window.parent.$('#topBut_save').removeClass('active');
    }
    else if(acCE=='form_tabMenu2') {
      //$('#form_passupdate')[0].reset();
      window.parent.$('#topBut_save').removeClass('active');
    }
    else if(acCE=='form_doapply') {
      
      //alert('Write to cues!!');
    }
  }

</script>
</body>
</html>
