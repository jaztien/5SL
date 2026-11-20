<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//架構搜尋條件(以搜尋條件架構搜尋表格，以及URI傳遞值字串)     
$searchText = isset($_REQUEST['searchText']) ? rawurldecode($_REQUEST['searchText']) : ''; 
$seachMBlevel = isset($_REQUEST['seachMBlevel']) ? $_REQUEST['seachMBlevel'] : '';
$searchMBcate = isset($_REQUEST['searchMBcate']) ? $_REQUEST['searchMBcate'] : '';
$searchClass = isset($_REQUEST['searchClass']) ? $_REQUEST['searchClass'] : '';
$searchClassID = isset($_REQUEST['searchClassID']) ? $_REQUEST['searchClassID'] : '';
//$searchParent = isset($_REQUEST['searchParent']) ? $_REQUEST['searchParent'] : '';
$searchCity = isset($_REQUEST['searchCity']) ? $_REQUEST['searchCity'] : '';
$searchDist = isset($_REQUEST['searchDist']) ? $_REQUEST['searchDist'] : '';
$searchCOtype = isset($_REQUEST['searchCOtype']) ? $_REQUEST['searchCOtype'] : '';
$searchCOexpert = isset($_REQUEST['searchCOexpert']) ? $_REQUEST['searchCOexpert'] : '';
//$searchMBdialog = isset($_REQUEST['searchMBdialog']) ? $_REQUEST['searchMBdialog'] : '';
//$searchMBconsult = isset($_REQUEST['searchMBconsult']) ? $_REQUEST['searchMBconsult'] : '';
//$searchMBopenhouse = isset($_REQUEST['searchMBopenhouse']) ? $_REQUEST['searchMBopenhouse'] : '';
//$searchMBlive = isset($_REQUEST['searchMBlive']) ? $_REQUEST['searchMBlive'] : '';
$searchSubscribe = isset($_REQUEST['searchSubscribe'])&&$_REQUEST['searchSubscribe']!='' ? $_REQUEST['searchSubscribe'] : '';
$searchMBstatus = isset($_REQUEST['searchMBstatus']) ? $_REQUEST['searchMBstatus'] : '';
//
$searchURI = "";
//
if($searchText!=''){ $searchURI .= "&searchText=".$searchText; }
if($seachMBlevel!=''){ $searchURI .= "&seachMBlevel=".$seachMBlevel; }
if($searchMBcate!=''){ $searchURI .= "&searchMBcate=".$searchMBcate; }
if(!empty($searchClass)){ $searchURI .= "&searchClass=".$searchClass; }
if($searchClassID!=''){ $searchURI .= "&searchClassID=".$searchClassID; }
if($searchCity!=''){ $searchURI .= "&searchCity=".$searchCity; }
if($searchDist!=''){ $searchURI .= "&searchDist=".$searchDist; }
if($searchCOtype!=''){ $searchURI .= "&searchCOtype=".$searchCOtype; }
if($searchCOexpert!=''){ $searchURI .= "&searchCOexpert=".$searchCOexpert; }
//if($searchMBdialog!=''){ $searchURI .= "&searchMBdialog=".$searchMBdialog; }
//if($searchMBconsult!=''){ $searchURI .= "&searchMBconsult=".$searchMBconsult; }
//if($searchMBopenhouse!=''){ $searchURI .= "&searchMBopenhouse=".$searchMBopenhouse; }
//if($searchMBlive!=''){ $searchURI .= "&searchMBlive=".$searchMBlive; }
if($searchSubscribe!=''){ $searchURI .= "&searchSubscribe=".$searchSubscribe; }
if($searchMBstatus!=''){ $searchURI .= "&searchMBstatus=".$searchMBstatus; }
if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}
//定義條件搜尋之前以及之後不同的顯示筆數
$maxResult = (!empty($searchURI)) ? 200 : 50;
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_MEM');
define('PAGE_ITEM','ITEM_MEM');
define('PAGE_ITEM2','ITEM_MEMSHIP');
define('PAGE_OPANEL',1);//1-新增；2-編輯；3-上傳；4-編輯/刪除；9-[空]。
define('SEARCH_OPANEL',!empty($searchURI)?2:1);
define('EXPORT_OPANEL',3);//1-Excel；2-PDF；3-Excel+PDF；
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//功能陣列建立，取自JSON檔案
$mbJS=_fn_getCachedJsonData("../jsonFiles/member.json");
foreach($mbJS['cotype'] as $key=>$Val){
  $coTypeJS[$key]=$Val['type'];
  foreach($Val['expert'] as $keyT=>$ValT){
    $expertJS[$keyT]=$ValT;
  }
}
$locJS=_fn_getCachedJsonData("../jsonFiles/locations.json");

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
	
  <title> Fifth Season Life 人生第五季 | 會員資料紀錄 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-bs4/css/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-responsive/css/responsive.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css">
  <!-- -------------------------- /CSS Link -------------------------- -->
  <style>
    i[data-bs-toggle="tooltip"]{
      color: #5F6E86;
      cursor: help;
    }
    i[data-bs-toggle="tooltip"]:hover{
      color: #236FB7;/*2C333E*/ 
    }
    #dataSearch {
      background-color: #dbf0e6;
      color: #000000;
      height: 54px;
    }
    /**/ 
/* ==========================================================================
   【最終終結版】同時修正 AdminLTE 透明背景與勾勾斷裂問題
   ========================================================================== */

/* 1. 未選中狀態：強制白底 + 灰色邊框 */
.icheck-primary > input[type="checkbox"]:not(:checked) + label::before,
.icheck-primary > input[type="checkbox"]:not(:checked) + input[type="hidden"] + label::before {
    background-color: #ffffff !important;
    border-color: #ced4da !important;
}

/* 2. 已選中狀態：強制維持白底 + 藍色邊框 */
.icheck-primary > input[type="checkbox"]:checked + label::before,
.icheck-primary > input[type="checkbox"]:checked + input[type="hidden"] + label::before {
    background-color: #ffffff !important;
    border-color: #007bff !important;
}

/* 3. 已選中狀態的打勾圖示：精準覆寫 icheck-bootstrap 採用的 right 與 bottom 屬性 */
.icheck-primary > input[type="checkbox"]:checked + label::after,
.icheck-primary > input[type="checkbox"]:checked + input[type="hidden"] + label::after {
    border-right-color: #007bff !important;  /* 修正隱形的右半邊勾勾 */
    border-bottom-color: #007bff !important; /* 修正原本的底邊勾勾 */
}


</style>
</head>
<body class="hold-transition sidebar-mini layout-navbar-fixed layout-footer-fixed">
  <!-- 全頁框 -->
  <div class="wrapper">
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="../imageFiles/preloaderLOGO.png" alt="Fifth Season Life LOGO" height="60" width="60"> 
    </div>
    <!-- /Preloader -->
    <!-- 頁內全頁內容 -->
    <div class="content-wrapper">
      <input type="text" id="searchURI" name="searchURI" style="width:1px;height:1px;opacity:0;" value="<?php //echo $searchURI; ?>">
      <!-- 頁頭導覽條 -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-7">
              <h4 class="m-0">
                會員列表
                <i class="fa-solid fa-circle-question text-md" data-bs-toggle="tooltip" data-bs-placement="top" title="人生第五季系統內的會員帳號清單。"></i>
                <span class="text-muted text-sm" style="margin-left:6px;">mem_mblist.php</span>
              </h4>
            </div><!-- /.col -->
            <div class="col-sm-5">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">會員管理</li>
                <li class="breadcrumb-item active">會員資料列表</li>
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </section>
      <!-- /頁頭導覽條 -->
      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <!-- 主要內容區域 ------------------------------------- -->
              <div class="card">
                <div class="card-header">
                    <?php if(!empty($searchURI)){ ?>
                    <span class="right badge badge-warning" style="position:absolute; top: -6px;">條件搜尋中</span>
                    <?php } ?>
                    <div class="card-title" style="font-size: 1.1em;">
                      <?php echo (!empty($searchURI)) ? "依條件搜尋共" : '最新'; ?> <span id="resultCount"></span> 筆紀錄
                      <i class="nav-icon fas fa fa-exclamation-circle" style="font-size:0.75em;font-weight:light;color:#5F6E86;"> 搜尋上限預設為<?php echo $maxResult; ?>筆，請縮小搜尋範圍以減少系統負載。若要回復原始表列內容，請開啟進階搜尋側欄清除搜尋條件。</i>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                  <table id="dataTable_mblist" class="table table-bordered table-striped table-hover" data-pagemax="25" data-dirto="mem_profile.php" but-excel but-print="人生第五季會員資料紀錄" data-search>
                    <thead>
                      <tr>
                        <th>狀態</th>
                        <th>ID</th>
                        <th>註冊日期</th>
                        <th>顯示名稱</th>
                        <th>分類</th>
                        <th>種類</th>
                        <th>等級</th>
                        <th>電子郵箱</th>
                        <th>兌換幣</th>
                        <th>訂閱</th>
                        <th>上次登入</th>
                        <th><div class="icheck-primary d-inline"><input type="checkbox" id="selectall" name="selectall" style="background-color: #fff;" value="selectall"><label for="selectall"></label></div></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        //$sr = $DB->query("SELECT * FROM members");
                        //$listSql = "SELECT *, COUNT(*) OVER() 'totalNum' FROM members a INNER JOIN mb_info b ON b.mb_id = a.mb_id";
                        $listSql = "SELECT *, COUNT(*) OVER() 'totalNum' FROM members";
                        if(!empty($searchURI)){ $listSql .= " WHERE 1=1"; }
                        if(!empty($searchText)){
                          $listSql .= " AND (";
                          $listSql .= "mb_name_hash = '"._fn_hash_hmac($searchText)."'";
                          $listSql .= " OR mb_title LIKE '%".$searchText."%'";
                          $listSql .= " OR mb_cellphone_hash = '"._fn_hash_hmac($searchText)."'";
                          $listSql .= " OR mb_account_hash = '"._fn_hash_hmac($searchText)."'";
                          $listSql .= " OR mb_linesub LIKE '%".$searchText."%'";
                          $listSql .= " OR mb_line LIKE '%".$searchText."%'";
                          $listSql .= " OR mb_lineuser LIKE '%".$searchText."%'";
                          $listSql .= " OR mb_google LIKE '%".$searchText."%'";
                          $listSql .= " OR mb_googlesub LIKE '%".$searchText."%'";
                          $listSql .= " OR mb_email_hash = '"._fn_hash_hmac($searchText)."'";
                          $listSql .= ")"; 
                        }
                        if(!empty($seachMBlevel)){ $listSql .= " AND mb_level = ".$seachMBlevel; }
                        if(!empty($searchMBcate)){ $listSql .= " AND mb_cate = ".$searchMBcate; }
                        if(!empty($searchParent)){ $listSql .= " AND parent_id = ".$searchParent; }
                        if(!empty($searchCity)){ $listSql .= " AND mb_city = ".$searchCity; }
                        if(!empty($searchDist)){ $listSql .= " AND mb_district = ".$searchDist; }
                        //////////////////////////////////////////////////////////////////////////////*/
                        if(!empty($searchCOtype)){
                          $listSql .= " AND TRANSLATE(mb_cotype, '[]', ',,') LIKE '%,".$searchCOtype.",%'";
                        }
                        if(!empty($searchCOexpert)){
                          $listSql .= " AND TRANSLATE(mb_coexpert, '[]', ',,') LIKE '%,".$searchCOexpert.",%'";
                        }

                        if($searchSubscribe != ''){ $listSql .= " AND mb_subscribe = ".$searchSubscribe; }
                        if(!empty($searchMBstatus)){ $listSql .= " AND mb_status = ".$searchMBstatus; }

                        //關聯課程(商品)資料表
                        if (!empty($searchClass) || !empty($searchClassID)) {


                        }

                        $listSql .= " ORDER BY mb_id DESC LIMIT {$maxResult}";
                        $sr = $DB->query($listSql);
                        $totalNum = intval(@$sr[0]['totalNum']);
                        $i = 0; //以$i為$nameList這個陣列的索引值
                        while($i<count($sr)){ 
                        ?>
                          <tr style="cursor:pointer;" data-currid="<?php echo $sr[$i]['mb_id']; ?>">
                            <td>
                              <span class="right badge<?php echo $mbJS['badge'][$sr[$i]['mb_status']]; ?>"><?php echo $mbJS['status'][$sr[$i]['mb_status']]; ?></span>
                            </td>
                            <td><?php echo $sr[$i]['mb_id']; ?></td>
                            <td><?php echo $sr[$i]['mb_published']; ?></td>
                            <td><?php echo $sr[$i]['mb_title']; ?></td>
                            <td><?php echo $mbJS['cate'][intval($sr[$i]['mb_cate'])] ?? '&nbsp;'; ?></td>
                            <td><?php echo $mbJS['type'][intval($sr[$i]['mb_type'])] ?? '&nbsp;'; ?></td>
                            <td><span class="<?php echo $mbJS['levels'][intval($sr[$i]['mb_level'])]['color'] ?? ''; ?>"><?php echo $mbJS['levels'][intval($sr[$i]['mb_level'])]['title'] ?? '&nbsp;'; ?></span></td>
                            <td><?php echo !empty($sr[$i]['mb_email']) ? _fn_secure_crypto($sr[$i]['mb_email'], 'de') : '&nbsp;'; ?></td>
                            <td><?php echo $sr[$i]['mb_points']; ?></td>
                            <td><?php echo $mbJS['yesorno'][intval($sr[$i]['mb_subscribe'])] ?? '&nbsp;'; ?></td>
                            <td><?php //echo $mbJS['mbboolean'][intval($sr[$i]['mb_subscribe'])]; ?></td>
                            <td><div class="icheck-primary d-inline"><input type="checkbox" id="mbid_<?php echo $sr[$i]['mb_id']; ?>" name="mbids[]" value="<?php echo $sr[$i]['mb_id']; ?>"><label for="mbid_<?php echo $sr[$i]['mb_id']; ?>" class="font-weight-light"></label></div></td>
                          </tr>
                      <?php 
                          $i++;
                          } 
                      ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>狀態</th>
                        <th>ID</th>
                        <th>註冊日期</th>
                        <th>顯示名稱</th>
                        <th>分類</th>
                        <th>種類</th>
                        <th>等級</th>
                        <th>電子郵箱</th>
                        <th>兌換幣</th>
                        <th>訂閱</th>
                        <th>上次登入</th>
                        <th>選擇</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
              <!-- /主要內容區域 ------------------------------------ -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
      </section>
    </div>
    <!-- /頁內全頁內容 -->
    <!-- jQuery -->
    <script src="../pluginFiles/jquery/jquery.min.js"></script>
    <!-- 右側功能控制區 -->
      <aside class="control-sidebar control-sidebar-dark">
        <!-- 功能區內容 ------------------------------------ -->
        <form id="form_search" name="form_search" action="" method="get">
          <div style="height:100%;display:flex;flex-direction: column;">
            <!-- 上格 -->
            <div style="flex: 1;padding:4px">
              <div>
                <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="此欄位專為已完成搜尋結果的紀錄，進行縮小範圍的再次查詢，輸入即查不必按搜尋按鈕，查詢後點按「清除」按鍵即可回復原始查詢結果。"></i>
                列表內資料過濾查尋
              </div>
              <div><input type="text" class="form-control notSubmit" id="dataSearch" name="dataSearch" value="" placeholder="-請輸入搜尋條件-" autocomplete="off"></div>
              <hr style="border-color:#5F6E86;">
              <div>
                <i class="fa-solid fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" title="部份開放查詢的文字型欄位皆列入被查目標，搜尋條件(欄位)可以依照管理者需求增減，請產生需求時通知工程人員修改。"></i>
                綜合搜尋
              </div>
              <div><input type="text" class="form-control" id="searchText" name="searchText" value="<?php echo $searchText; ?>" placeholder="請輸入文字搜尋條件" autocomplete="off"></div>
              <div>指定搜尋</div>
              <div>
                <select class="form-control" id="seachMBlevel" name="seachMBlevel" placeholder="-請選擇會員等級-">
                  <option value="">-請選擇會員等級-</option>
                  <?php
                    foreach($mbJS['levels'] as $key => $value){
                        if($key=="") continue;
                        echo '<option value="'.$key.'"';
                        if(@$seachMBlevel==$key) echo " selected";
                        echo '>'.$value['title'].'</option>';
                    }
                  ?>
                </select>
              </div>
              <div>
                <select class="form-control" id="searchMBcate" name="searchMBcate" placeholder="-請選擇會員分類-">
                  <option value="">-請選擇會員分類-</option>
                  <?php
                    foreach($mbJS['cate'] as $key => $value){
                        if($key=="") continue;
                        echo '<option value="'.$key.'"';
                        if(@$searchMBcate==$key) echo " selected";
                        echo '>'.$value.'</option>';
                    }
                  ?>
                </select>
              </div>
              <div>
                <select class="form-control" id="searchClass" name="searchClass" placeholder="-請選擇參與課程-" style="width: 100%;">
                  <option value="">-請選擇參與課程-</option>
                  <option value="0"<?php if($searchClass==0) echo " selected";?>>輸入課程ID或代碼</option>
                </select>
              </div>
              <div><input type="text" class="form-control" id="searchClassID" name="searchClassID" value="<?php echo $searchClassID; ?>" placeholder="請輸入參與課程ID" autocomplete="off" disabled></div>
              <div>
                <select class="form-control" id="searchCity" name="searchCity" data-placeholder="-請選擇所在城市-" data-select="city">
                  <option value="">-請選擇所在城市-</option>
                  <?PHP
                    foreach($locJS as $key => $value){
                      echo "<option value='".$value['value']."'";
                      if(@$searchCity!="" && @$searchCity==$key) echo " selected";
                      echo ">".$value['text']."</option>";
                    }
                  ?>
                </select>
              </div>
              <div>
                <select class="form-control" id="searchDist" name="searchDist" data-placeholder="-請選擇行政區劃-" data-select="dist" dist-fromcity="searchCity" list-bc="1" list-circ="1" style="width: 100%;">
                  <option value="">-請選擇行政劃區-</option>
                  <?PHP
                  if(!empty($searchCity)) {
                    foreach($locJS[$searchCity]['district'] as $key => $value){
                      echo "<option value='".$key."'";
                        if(@$searchDist!="" && @$searchDist==$key) echo " selected";
                      echo ">".$value."</option>";
                    }
                  }
                  ?>
                </select>
              </div>
              <div>
                <select class="form-control" id="searchCOtype" name="searchCOtype" data-placeholder="-選擇合作方種類-" style="width: 100%;">
                  <option value="">-選擇合作方種類-</option>
                  <?php
                    foreach($mbJS['cotype'] as $key => $value){
                      if($key=="") continue;
                      echo '<option value="'.$key.'"';
                      if(@$searchCOtype==$key) echo " selected";
                      echo '>'.$value['type'].'</option>';
                    }
                  ?>
                </select>
              </div>
              <div>
                <select class="form-control" id="searchCOexpert" name="searchCOexpert" data-placeholder="-選擇專業服務項目-" style="width: 100%;">
                  <option value="">-選擇專業服務項目-</option>
                  <?php
                  if(!empty($searchCOtype)){
                    echo '<optgroup label="'.$mbJS['cotype'][$searchCOtype]['type'].'">';
                    foreach($mbJS['cotype'][$searchCOtype]['expert'] as $key => $value){
                      echo '<option value="'.$key.'"';
                      if(@$searchCOexpert == $key) echo " selected";
                      echo '>'.$value.'</option>';
                    }
                    echo "</optgroup>";
                  }
                  ?>
                </select>
              </div>
              <div>刊物訂閱</div>
              <div>
                <div class="icheck-primary d-inline">
                  <input type="checkbox" id="searchSubscribe" name="searchSubscribe" value="1"<?php if(@$searchSubscribe==1) echo " checked"; ?>>
                  <label for="searchSubscribe" class="font-weight-light">已訂閱</label>
                </div>
              </div>
              <div>
                <select class="form-control" id="searchMBstatus" name="searchMBstatus" placeholder="-請選擇會員狀態-" data-required>
                  <option value="">-請選擇會員狀態-</option>
                    <?php
                      foreach($mbJS['status'] as $key => $value){
                          if($key=="") continue;
                          echo '<option value="'.$key.'"';
                          if(@$searchMBstatus==$key) echo " selected";
                          echo '>'.$value.'</option>';
                      }
                    ?>
                </select>
              </div>
            </div>
            <!-- 按鈕爛 -->
            <div class="form-group row" style="height:56px;margin:4px;">
              <button type="submit" class="col-sm-7 btn btn-block btn-info notSubmit"  id="searchSend" name="searchSend" style="height:48px;padding:1px;margin:0;"><i class="fa fa-solid fa-magnifying-glass"></i> 執行搜尋</button>
              <button type="button" class="col-sm-3 btn btn-block btn-secondary"  id="searchReset" name="searchReset" style="height:48px;padding:1px;margin:0;">清除</button>
              <button type="button" class="col-sm-2 btn btn-block btn-warning"  id="sidebarControl" name="sidebarControl" style="height:48px;padding:1px;margin:0;" data-widget="control-sidebar" data-controlsidebar-slide="true"><i class="fa fa-solid fa-right-from-bracket"></i></button>
            </div>
          </div>
        </form>
        <!-- /功能區內容 ----------------------------------- -->
      </aside>
    <!-- /右側功能控制區 -->
  </div>
  <!-- /全頁框 -->
<!-- ------------------------ Scripts Link ------------------------ -->
<!-- jQuery is in front of footer file. -->
<!-- Bootstrap 4 -->
<!-- <script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script> -->
<!-- Bootstrap 5 -->
<script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
<!-- DataTables2 & Plugins -->
<!--
<script src="../pluginFiles/datatables/datatables/jquery.dataTables.min.js"></script>
<script src="../pluginFiles/datatables/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.html5.min.js"></script>
-->
<!-- DataTables3 & Plugins -->
<script src="../pluginFiles/datatables3/datatables.min.js"></script>
<!-- <script src="../pluginFiles/pdfmake/pdfmake.min.js"></script>-->
<!-- <script src="../pluginFiles/pdfmake/vfs_fonts.js"></script>-->
<!-- skip for list without advance buttons
<script src="../pluginFiles/datatables/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../pluginFiles/datatables/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.colVis.min.js"></script>
<script src="../pluginFiles/pdfmake/vfs_fonts.js"></script>
-->
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js<?php echo '?t='.time(); ?>"></script>
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>
<!-- ------------------------ /Scripts Link ------------------------ -->
<script type="text/javascript">
////////////////////////////////////////////////////////////////
// ---------------- JAVASCRIPTS 全域調用參數設置 --------------- //
////////////////////////////////////////////////////////////////
  var mbobj = <?php echo json_encode($mbJS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
////////////////////////////////////////////////////////////////
// ---------------- JAVASCRIPTS 全域調用參數設置 --------------- //
////////////////////////////////////////////////////////////////
  // 當「新增」按鈕按下時的動作
  function doButAdd(){
    window.location.replace('mem_profile.php?currID=0');
  }
  // 當「重置」按鈕按下時的動作
  function doButReset(){
    location.reload(true);
  }
  //側邊隱藏區域開啟閉合
  function doButSerach(){
    if($("#sidebarControl").length == 0) {
      return false;
    }
    $('#sidebarControl').click();
  }
  //系統自動生成Excel按鍵
  function doButExcel(){
    $('.buttons-excel').click(); 
  }
  //系統開啟列印視窗按鍵
  function doButPrint(){
    $('.buttons-print').click(); 
  }

////////////////////////////////////////////////////////////////
// ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
// ---------------------------------------------------------- //
////////////////////////////////////////////////////////////////
  $(document).ready(function() {    
    //------------------------- Tooltips -----------------------------//
    $('[data-toggle="tooltip"]').tooltip();
    //動態顯示筆數
    $('#resultCount').html('<?php echo $totalNum > $maxResult ? $totalNum."筆符合，表列".$maxResult : $totalNum; ?>');
    // iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });
    //----------------- Datatable 操作 ------------------------
    var mblistTB = datatableCreate('dataTable_mblist');
    //外置表列內容搜尋框動作操作
    $('#dataSearch').on('keyup search input paste cut', function () {
      if ($(this).is(':focus') || this.value !== '') {
          mblistTB.search(this.value).draw();
      }
    });
    //全選/全不選
    $('#selectall').on('change', function() {
        // 獲取當前全選框的「勾選狀態」(true / false)
        var isChecked = $(this).prop('checked');
        //JazNOTE: 不要只用 $('input[name="mbids[]"]') 尋找，那樣會漏掉其他分頁的 DOM。使用 table.cells().nodes() 可以抓到 DataTable 快取中「所有分頁」的 <td> 元素。
        var cells = mblistTB.cells().nodes();
        // 將所有分頁內部的 checkbox 狀態，同步成跟全選框一樣
        $(cells).find('input[name="mbids[]"]').prop('checked', isChecked);
    });
    // 【優化】當使用者逐一取消勾選某個會員，導致不滿全選時，自動取消「全選框」的勾選狀態
    $('#dataTable_mblist tbody').on('change', 'input[name="mbids[]"]', function() {
        var cells = mblistTB.cells().nodes();
        var totalCheckboxes = $(cells).find('input[name="mbids[]"]').length;
        var checkedCheckboxes = $(cells).find('input[name="mbids[]"]:checked').length;
        // 如果勾選的總數等於全部的總數，全選框就亮起，否則就熄滅
        $('#selectall').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    //----------------- 搜尋課程動作 ------------------------//
    $('#searchClass').on('change', function(){
      var classIDX = $(this).prop('selectedIndex');
      if(classIDX === 1){
        $('#searchClassID').prop('disabled', false);
      } else {
        $('#searchClassID').prop('disabled', true).val('');
      }
    });


    // --------------- 當改變合作方種類選項時 ------------------//searchCOtype searchCOexpert
    $('#searchCOtype').on('change', function(){
      var typId = $(this).val();//Array
      var expId = $('#searchCOexpert').val();//Array
      var ct = mbobj['cotype'];
      $('#searchCOexpert').html('<option value="">-選擇專業服務項目-</option>');
      for(var i=0; i<typId.length; i++){
        $('#searchCOexpert').append('<optgroup label="-- '+ ct[typId[i]]['type'] +' --">');
        $.each(ct[typId[i]]['expert'], function( index, value ) {
          var appendHTML = '<option value="' + index + '"';
          if ($.inArray(index, expId) >= 0) { appendHTML += ' selected'; }
          appendHTML += '>'+ value +'</option>';
          $('#searchCOexpert').append(appendHTML);
        });
      }
    });
    //--------------- 搜尋表格重置清除按鈕動作 ------------------
    $('#searchReset').click(function() {
      if($('#dataSearch').val() != '') {
        $('#dataSearch').val('');
        mblistTB.search('').draw();
      } else {
        $('#form_search').find('input').val('');
        $('#form_search').find('select').val('');
        $("#searchDist,#searchCOexpert").find("option:gt(0)").remove();
        $("#searchCOexpert").find("optgroup").remove();
        $('#form_search input[type="checkbox"]').prop('checked', false);
      }
    });
    //-------- 加載完成後更改外框主頁中樹狀目錄開合狀態 --------//
    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  //////////////////////////////////////////////////////////////////
  });

</script>
</body>
</html>
