<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//架構搜尋條件(以搜尋條件架構搜尋表格，以及URI傳遞值字串)
$searchText       = isset($_REQUEST['searchText']) ? rawurldecode($_REQUEST['searchText']) : '';
$searchBatch      = $_REQUEST['searchBatch'] ?? '';
$searchStartdate  = $_REQUEST['searchStartdate'] ?? '';
$searchEnddate    = $_REQUEST['searchEnddate'] ?? '';
$searchStarttime  = $_REQUEST['searchStarttime'] ?? '';
$searchEndtime    = $_REQUEST['searchEndtime'] ?? '';
$searchDurationS  = $_REQUEST['searchDurationS'] ?? '';
$searchDurationE  = $_REQUEST['searchDurationE'] ?? '';
$searchLessonS    = $_REQUEST['searchLessonS'] ?? '';
$searchLessonE    = $_REQUEST['searchLessonE'] ?? '';
$searchTA         = $_REQUEST['searchTA'] ?? '';
$searchBatchMode  = $_REQUEST['searchBatchMode'] ?? '';
$searchStatus     = $_REQUEST['searchStatus'] ?? '';

$searchURI = "";
if(!empty($searchText)){ $searchURI .= "&searchText=".$searchText; }
if(!empty($searchBatch)){ $searchURI .= "&searchBatch=".$searchBatch; }
if(!empty($searchStartdate)){ $searchURI .= "&searchStartdate=".$searchStartdate; }
if(!empty($searchEnddate)){ $searchURI .= "&searchEnddate=".$searchEnddate; }
if(!empty($searchStarttime)){ $searchURI .= "&searchStarttime=".$searchStarttime; }
if(!empty($searchEndtime)){ $searchURI .= "&searchEndtime=".$searchEndtime; }
if(!empty($searchDurationS)){ $searchURI .= "&searchDurationS=".$searchDurationS; }
if(!empty($searchDurationE)){ $searchURI .= "&searchDurationE=".$searchDurationE; }
if(!empty($searchLessonS)){ $searchURI .= "&searchLessonS=".$searchLessonS; }
if(!empty($searchLessonE)){ $searchURI .= "&searchLessonE=".$searchLessonE; }
if(!empty($searchTA)){ $searchURI .= "&searchTA=".$searchTA; }
if(!empty($searchBatchMode)){ $searchURI .= "&searchBatchMode=".$searchBatchMode; }
if(!empty($searchStatus)){ $searchURI .= "&searchStatus=".$searchStatus; }

if (!empty($searchURI)) {
  $searchURI = substr($searchURI, 1);
}
//定義條件搜尋之前以及之後不同的顯示筆數
$maxResult = (!empty($searchURI)) ? 200 : 50;
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_SESSION');
define('PAGE_ITEM','ITEM_MERCH');
define('PAGE_OPANEL',1);//1-新增；2-編輯；3-上傳；4-編輯/刪除；9-[空]。
define('SEARCH_OPANEL',!empty($searchURI)?2:1);
define('EXPORT_OPANEL',3);//1-Excel；2-PDF；3-Excel+Print；
define('bkRedir',1);
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//功能陣列建立，取自JSON檔案
$courseJS=_fn_getCachedJsonData("../jsonFiles/courses.json");
//$locJS=_fn_getCachedJsonData("../jsonFiles/locations.json");
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
	
  <title> Fifth Season Life 人生第五季 | 課程資料紀錄 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <link href="../pluginFiles/font-gis/css/font-gis.css" rel="stylesheet" />
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <!--
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-bs4/css/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-responsive/css/responsive.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-buttons/css/buttons.bootstrap4.css">
  -->
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css<?php echo '?t='.time(); ?>">
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


 </style>
</head>
<body class="hold-transition sidebar-mini layout-navbar-fixed layout-footer-fixed">
  <input type="hidden" id="searchURI" name="searchURI" value="<?php //echo $searchURI; ?>">
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
                課程梯次/課時資料列表
                <i class="fa-solid fa-circle-question text-md" data-bs-toggle="tooltip" data-bs-placement="top" title="xxxx"></i>
                <span class="text-muted text-sm" style="margin-left:6px;">course_sesslist.php</span>
              </h4>
            </div><!-- /.col -->
            <div class="col-sm-5">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item">課程商品資料管理</li>
                <li class="breadcrumb-item active">課程梯次/課時資料列表</li>
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
                <div class="card-body theme-green">
                  <table id="dataTable_sesslist" class="table table-bordered table-striped table-hover data-table table-sm" data-colorder="0,2,11,12,13" data-pagemax="1" data-dirto="course_sessinfo.php" but-excel but-print="人生第五季課程梯次資料紀錄" data-search>
                    <thead>
                      <tr>
                        <th data-toggle="tooltip" title="狀態">狀態</th>
                        <th>ID</th>
                        <th>圖像</th>
                        <th>課程名稱</th>
                        <th>梯次別名</th>
                        <th>模式</th>
                        <th>種類</th>
                        <th>時長</th>
                        <th>堂數</th>
                        <th>日期</th>
                        <th>時間</th>
                        <th>定價</th>
                        <th>ZoomID</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $listSql = "SELECT *, COUNT(*) OVER() 'totalNum' FROM fsl_course_sessions";//, course_coordinates.STGeometryType() AS coordType
                        if(!empty($searchURI) || !empty($searchAdmin)){ $listSql .= " WHERE 1=1"; }
                        if(!empty($searchText)){ 
                            $listSql .= " AND (
                              sess_keycode LIKE '%".$searchText."%' OR 
                              sess_altname LIKE '%".$searchText."%' OR 
                              sess_subscript LIKE '%".$searchText."%' OR 
                              sess_brief LIKE '%".$searchText."%' OR 
                              sess_meetingid LIKE '%".$searchText."%'
                              )"; 
                        }
                        if(!empty($searchBatch)){ $listSql .= " AND sess_batch = ".$searchBatch; }
                        if(!empty($searchStartdate) || !empty($searchEnddate)){ 
                            if(!empty($searchStartdate) && !empty($searchEnddate)){
                                $listSql .= " AND sess_startdate BETWEEN '".$searchStartdate."' AND '".$searchEnddate."'";
                            }else if(!empty($searchStartdate)){
                                $listSql .= " AND sess_startdate >= '".$searchStartdate."'";
                            }else if(!empty($searchEnddate)){
                                $listSql .= " AND sess_startdate <= '".$searchEnddate."'";
                            }
                        }
                        if(!empty($searchStarttime) || !empty($searchEndtime)){
                            if(!empty($searchStarttime) && !empty($searchEndtime)){
                                $listSql .= " AND sess_starttime BETWEEN '".$searchStarttime."' AND '".$searchEndtime."'";
                            }else if(!empty($searchStarttime)){
                                $listSql .= " AND sess_starttime >= '".$searchStarttime."'";
                            }else if(!empty($searchEndtime)){
                                $listSql .= " AND sess_starttime <= '".$searchEndtime."'";
                            }
                        }
                        if(!empty($searchDurationS) || !empty($searchDurationE)){ 
                            if(!empty($searchDurationS) && !empty($searchDurationE)){
                                $listSql .= " AND sess_duration BETWEEN '".$searchDurationS."' AND '".$searchDurationE."'";
                            }else if(!empty($searchDurationS)){
                                $listSql .= " AND sess_duration >= '".$searchDurationS."'";
                            }else if(!empty($searchDurationE)){
                                $listSql .= " AND sess_duration <= '".$searchDurationE."'";
                            }
                        }
                        if(!empty($searchLessonS) || !empty($searchLessonE)){ 
                            if(!empty($searchLessonS) && !empty($searchLessonE)){
                                $listSql .= " AND sess_lesson BETWEEN '".$searchLessonS."' AND '".$searchLessonE."'";
                            }else if(!empty($searchDurationS)){
                                $listSql .= " AND sess_lesson >= '".$searchLessonS."'";
                            }else if(!empty($searchDurationE)){
                                $listSql .= " AND sess_lesson <= '".$searchLessonE."'";
                            }
                        }
                        if(!empty($searchTA)){ $listSql .= " AND (sess_ta1 = ".$searchTA." OR sess_ta2 = ".$searchTA.")"; }
                        if(!empty($searchBatchMode)){ $listSql .= " AND sess_batchmode = ".$searchBatchMode; }
                        if(!empty($searchStatus)){ $listSql .= " AND sess_status = ".$searchStatus; }

                        $listSql .= " ORDER BY sess_startdate DESC";
                        $sr = $DB->query($listSql);
                        $totalNum = intval(@$sr[0]['totalNum']);
                        $i = 0; //以$i為$nameList這個陣列的索引值 
                        while($i<count($sr)){
                          $courseRes = $DB->row("SELECT course_title FROM fsl_courses WHERE course_id = ? LIMIT 1",array($sr[$i]['course_id']));
                        ?>
                          <tr style="cursor:pointer;" data-currid="<?php echo $sr[$i]['sess_id'] ?? 0; ?>">
                            <td><span class="right badge<?php echo $courseJS['actionbadge'][$sr[$i]['sess_status'] ?? ''] ?? ''; ?>"><?php echo str_replace("/", "<br>/", $courseJS['status'][$sr[$i]['sess_status'] ?? ''] ?? ''); ?></span></td>
                            <td><?php echo $sr[$i]['sess_id']; ?></td>
                            <td><?php
                              $tinypic = "../uploadFiles/course/sessimg_".$sr[$i]['sess_id']."_thumb.jpg";
                              echo "<img style='margin:-6px 0px -6px;' width='120px' src='";
                              if(file_exists($tinypic)) echo $tinypic."?".time();
                              else echo "../imageFiles/nopic_160x90.jpg";
                              echo "'>";
                            ?></td>
                            <td><?php echo $courseRes['course_title'] ?? '&nbsp;'; ?></td>
                            <td><?php echo $sr[$i]['sess_altname'] ?? '&nbsp;'; ?></td>
                            <td><?php echo $courseJS['mode'][$sr[$i]['course_mode'] ?? ''] ?? '&nbsp;'; ?></td>
                            <td><?php echo $courseJS['type'][$sr[$i]['course_type'] ?? ''] ?? '&nbsp;'; ?></td>
                            <td><?php echo (($sr[$i]['sess_duration'] ?? '') !== '') ? $sr[$i]['sess_duration'] . ' min' : '&nbsp;'; ?></td>
                            <td><?php echo $sr[$i]['sess_lesson'] ?? '&nbsp;'; ?></td>
                            <td><?php echo $sr[$i]['sess_startdate'] ?? '&nbsp;'; ?></td>
                            <td><?php echo $sr[$i]['sess_starttime'] ?? '&nbsp;'; ?></td>
                            <td><?php echo $sr[$i]['course_cost'] ?? '&nbsp;'; ?></td>
                            <td><?php echo $sr[$i]['sess_meetingid'] ?? '&nbsp;'; ?></td>
                            <td style="text-align:center;"><i class="fa-solid fa-2x fa-pen-to-square"></i></td>
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
                        <th>圖像</th>
                        <th>課程名稱</th>
                        <th>梯次別名</th>
                        <th>模式</th>
                        <th>種類</th>
                        <th>時長</th>
                        <th>堂數</th>
                        <th>日期</th>
                        <th>時間</th>
                        <th>定價</th>
                        <th>ZoomID</th>
                        <th></th>
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
        <div style="height:100%;display:flex;flex-direction:column;">
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
            <div><input type="text" class="form-control" id="searchText" name="searchText" value="<?php echo $searchText; ?>" placeholder="請輸入搜尋條件" autocomplete="off"></div>
            <div>指定搜尋</div>
            <div class="form-group row">
                <div class="col-sm-5" style="margin-right:0;padding-right:0;">
                  <div class="input-group">
                    <input type="text" class="form-control" id="searchStartdate" name="searchStartdate" style="margin:0;padding:0;text-align:center" placeholder="起始日期" value="<?php echo $searchStartdate ?? ''; ?>">
                  </div>
                </div>
                <div class="col-sm-2"style="margin:0;padding:0;"><span class="input-group-text">至</span></div>
                <div class="col-sm-5" style="margin-left:0;padding-left:0;">
                  <div class="input-group">
                    <input type="text" class="form-control" id="searchEnddate" name="searchEnddate" style="margin:0;padding:0;text-align:center" placeholder="結束日期" value="<?php echo $searchEnddate ?? ''; ?>">
                  </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-5" style="margin-right:0;padding-right:0;">
                  <div class="input-group">
                    <input type="text" class="form-control" id="searchStarttime" name="searchStarttime" style="margin:0;padding:0;text-align:center" placeholder="起始時間" value="<?php echo $searchStarttime ?? ''; ?>">
                  </div>
                </div>
                <div class="col-sm-2"style="margin:0;padding:0;"><span class="input-group-text">至</span></div>
                <div class="col-sm-5" style="margin-left:0;padding-left:0;">
                  <div class="input-group">
                    <input type="text" class="form-control" id="searchEndtime" name="searchEndtime" style="margin:0;padding:0;text-align:center" placeholder="結束時間" value="<?php echo $searchEndtime ?? ''; ?>">
                  </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-5" style="margin-right:0;padding-right:0;">
                  <div class="input-group">
                    <input type="text" class="form-control" id="searchLessonS" name="searchLessonS" style="margin:0;padding:0;text-align:center" placeholder="最小堂數" value="<?php echo $searchLessonS ?? ''; ?>">
                  </div>
                </div>
                <div class="col-sm-2"style="margin:0;padding:0;"><span class="input-group-text">至</span></div>
                <div class="col-sm-5" style="margin-left:0;padding-left:0;">
                  <div class="input-group">
                    <input type="text" class="form-control" id="searchLessonE" name="searchLessonE" style="margin:0;padding:0;text-align:center" placeholder="最大堂數" value="<?php echo $searchLessonE ?? ''; ?>">
                  </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-5" style="margin-right:0;padding-right:0;">
                  <div class="input-group">
                    <input type="text" class="form-control" id="searchDurationS" name="searchDurationS" style="margin:0;padding:0;text-align:center" placeholder="最小課程時長" value="<?php echo $searchDurationS ?? ''; ?>">
                  </div>
                </div>
                <div class="col-sm-2"style="margin:0;padding:0;"><span class="input-group-text">至</span></div>
                <div class="col-sm-5" style="margin-left:0;padding-left:0;">
                  <div class="input-group">
                    <input type="text" class="form-control" id="searchDurationE" name="searchDurationE" style="margin:0;padding:0;text-align:center" placeholder="最大課程時長" value="<?php echo $searchDurationE ?? ''; ?>">
                  </div>
                </div>
            </div>
            <div>
              <select class="form-control" id="searchBatchMode" name="searchBatchMode">
                <option value="">-請選擇課程屬性-</option>
                <?php
                  foreach($courseJS['mode'] as $key => $value){
                    if($key==""){ continue; }
                    echo '<option value="'.$key.'"';
                    echo (($searchBatchMode ?? '') === $key) ? ' selected' : '';
                    echo '>'.$value.'</option>';
                  }
                ?>
              </select>
            </div>
            <div>
              <select class="form-control" id="searchType" name="searchType">
                <option value="">-請選擇課程內容分類-</option>
                <?php
                  foreach($courseJS['type'] as $key => $value){
                    if($key==""){ continue; }
                    echo '<option value="'.$key.'"';
                    echo (($searchType ?? '') === $key) ? ' selected' : '';
                    echo '>'.$value.'</option>';
                  }
                ?>
              </select>
            </div>
            <div>
              <select class="form-control" id="searchQualify" name="searchQualify">
                <option value="">-請選擇身份要求-</option>
                <?php
                  foreach($courseJS['qualify'] as $key => $value){
                    if($key==""){ continue; }
                    echo '<option value="'.$key.'"';
                    echo (($searchQualify ?? '') === $key) ? ' selected' : '';
                    echo '>'.$value.'</option>';
                  }
                ?>
              </select>
            </div>
            <div>
              <select class="form-control" id="searchUnlock" name="searchUnlock">
                <option value="">-請選擇開通模式-</option>
                <?php
                  foreach($courseJS['unlock'] as $key => $value){
                    if($key==""){ continue; }
                    echo '<option value="'.$key.'"';
                    echo (($searchUnlock ?? '') === $key) ? ' selected' : '';
                    echo '>'.$value.'</option>';
                  }
                ?>
              </select>
            </div>
            <div>
              <select class="form-control" id="searchDeduct" name="searchDeduct">
                <option value="">-請選擇付費模式-</option>
                <?php
                  foreach($courseJS['deduct'] as $key => $value){
                    if($key==""){ continue; }
                    echo '<option value="'.$key.'"';
                    echo (($searchDeduct ?? '') === $key) ? ' selected' : '';
                    echo '>'.$value.'</option>';
                  }
                ?>
              </select>
            </div>
            <div>
              <select class="form-control" id="searchStatus" name="searchStatus">
                <option value="">-請選擇上架狀態-</option>
                <?php
                  foreach($courseJS['status'] as $key => $value){
                      echo '<option value="'.$key.'"';
                      if($key==""){ continue; }
                      echo (($searchStatus ?? '') === $key) ? ' selected' : '';
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
<script src="../pluginFiles/jszip/jszip.min.js"></script>
<!-- InputMask 日期輸入元件JS -->
<script src="../pluginFiles/moment/moment.min.js"></script>
<script src="../pluginFiles/inputmask/jquery.inputmask.min.js"></script>
<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
<script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>
<!-- ------------------------ /Scripts Link ------------------------ -->
<script type="text/javascript">
////////////////////////////////////////////////////////////////
// ---------------- JAVASCRIPTS 全域調用參數設置 --------------- //
////////////////////////////////////////////////////////////////
  var sesslistTB;
////////////////////////////////////////////////////////////////
// -------------- JAVASCRIPTS FUNCTIONs 全域調用 -------------- //
// -------------- 可接收include檔案要求後自定義動作 -------------- //
////////////////////////////////////////////////////////////////
  // 當「儲存」按鈕按下時的動作
  function doButAdd(){
    window.location.replace('course_sessinfo.php?currID=0');
  }
  // 當「重置」按鈕按下時的動作
  function doButReset(){
    location.reload(true);
  }
  //側邊隱藏區域開啟閉合
  function doButSerach(){
    $('#sidebarControl').click();
  }
  //系統自動生成Excel按鍵  .dt-buttons
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
    //------------------------- Tooltip -------------------------//
    $('[data-toggle="tooltip"]').tooltip();
    //動態顯示筆數
    $('#resultCount').html('<?php echo $totalNum > $maxResult ? $totalNum."筆符合，表列".$maxResult : $totalNum; ?>');
    //* iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });
    //----------------------------------------
    sesslistTB = datatableCreate('dataTable_sesslist');
    //外置表列內容搜尋框動作操作
    $('#dataSearch').on('keyup search input paste cut', function () {
      if ($(this).is(':focus') || this.value !== '') {
          sesslistTB.search(this.value).draw();
      }
    });

    //搜尋表格重置清除
    $('#searchReset').click(function() {
      if($('#dataSearch').val() != '') {
        $('#dataSearch').val('');
        sesslistTB.search('').page(0).draw();
      } else {
        $('#form_search').find('input').val('');
        $("#form_search").find("select").val("");
        $("#searchDist,#searchScrc,#searchBC").find("option:gt(0)").remove();
      }
    });
    /*
    $(window).on('wheel', function (event) {
      // 檢查右側控制側邊欄目前是否處於「開啟」狀態
      if ($('body').hasClass('control-sidebar-slide-open')) {
        //【防誤觸】確保滑鼠游標「不在」右側側邊欄內部
        if ($(event.target).closest('.control-sidebar').length === 0) {
          // 呼叫 AdminLTE 3 官方 API 關閉右側側邊欄
          $('[data-widget="control-sidebar"]').ControlSidebar('collapse');
          //$('#sidebarControl').click(); 
        }
      }
    });
    */
    //////////////////// DATEMASK 日期輸入元件初始化 //////////////
    $('#sess_startdate,#sess_enddate').inputmask({
        alias: "datetime",
        inputFormat: "yyyy-mm-dd",
        placeholder: "yyyy-mm-dd",
        removeMaskOnSubmit: true, 
        showMaskOnFocus: true, // 只有滑鼠點擊時才顯示提示底線  
        jitMasking: false,//關閉即時遮罩，確保網頁一載入就看得到完整 placeholder   
        clearIncomplete: true //如果沒填完整，離開焦點（Blur）時會自動清空整個欄位
    });
    $('#sess_starttime,#sess_endtime').inputmask({
        alias: "datetime",
        inputFormat: "HH:MM",
        placeholder: "hh:mm",
        removeMaskOnSubmit: true, 
        showMaskOnFocus: true,        
        jitMasking: false,       
        clearIncomplete: true
    });
    //-------- 加載完成後更改外框主頁中樹狀目錄開合狀態 --------//
    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
        window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
////////////////////////////////////////////////////////////////     
  });

  </script>
</body>
</html>
