<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_CSVIO');
define('PAGE_ITEM','ITEM_TOOLS');
define('PAGE_ITEM2','ITEM_TOOLS');
define('PAGE_OPANEL',9);
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');
//
$currID = currID;
//功能陣列建立，取自JSON檔案
$colJS=_fn_getCachedJsonData("../jsonFiles/cols.json");
/*
$toColArr[5] = "A-";
$toColArr[8] = "B-";
$toColArr[9] = "C-";
$toColArr[12] = "D-";

$line[5] = "AAAA";
$line[8] = "BBBBB";
$line[9] = "CCCCCC";
$line[12] = "DDDDDDD";

foreach( $toColArr as $key => $value){
  $actArr[$value]=$line[$key];
}

//print_r($actArr);
echo $actArr['B-'];
*/
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
	
  <title> Fifth Season Life 人生第五季 | CSV檔案匯入匯出器 </title>
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
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <!--
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-bs4/css/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-responsive/css/responsive.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-buttons/css/buttons.bootstrap4.css">
  -->
  <!-- Toastr 簡易提示訊息
  <link rel="stylesheet" href="../pluginFiles/toastr/toastr.min.css"> -->
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css">
<!-- -------------------------- /CSS Link -------------------------- -->
</head>
<body class="layout-navbar-fixed hold-transition sidebar-mini">
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
            <div class="col-sm-7">
              <h4 class="m-0">CSV檔案匯入匯出器</h4>
            </div>
            <div class="col-sm-5">
              <ol class="breadcrumb float-sm-right small">
                  <li class="breadcrumb-item">首頁</li>
                  <li class="breadcrumb-item">基礎數據&管理工具</li>
                  <li class="breadcrumb-item">基礎結構數據</li>
                <li class="breadcrumb-item active">CSV檔案匯入匯出器</li>
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
              <div class="card card-info card-outline">
                <div class="card-body box-profile">

                  <div class="text-center">
                    <div class="image_area">
                      <form method="post">
                        <label for="csvUploadFile">
                          <img id="avatarImage" name="avatarImage" class="img-responsive imgEDicon" src="../imageFiles/excel_400x300.jpg"/>
                          <div class="overlay">
                            <div class="overtext">上傳CSV檔案</div>
                          </div>
                          <input type="file" name="image" id="csvUploadFile" class="image" style="display:none" crop-width="400" crop-height="400">
                        </label>
                      </form>
                    </div>
                  </div>
                  <ul class="list-group list-group-unbordered mb-3">
                    <form id="form_tabMenu1" name="form_tabMenu1" action="../funcFiles/ad_act_func.php" method="post" data-formmode="csvupdate" data-func="func_ad_csvedit">
                      <li class="list-group-item">
                        <b>檔案抬頭</b><div id="upFile_title" class="col-sm-12"></div>
                      </li>
                      <li class="list-group-item">
                        <b>檔案名稱</b> <a id="upFile_name" class="float-right"></a>
                      </li>
                      <li class="list-group-item">
                        <b>段落</b> <a id="upFile_part" class="float-right"></a>
                      </li>
                      <li class="list-group-item">
                        <b>批次</b> <a id="upFile_batch" class="float-right"></a>
                      </li>
                      <li class="list-group-item">
                        <b>檔案類別</b> <a id="upFile_ext" class="float-right" style="font-size:1.2em;font-weight:bold;"></a>
                      </li>
                      <li class="list-group-item">
                        <b>檔案日期</b> <a id="upFile_create" class="float-right"></a>
                      </li>
                      <li class="list-group-item">
                        <b>檔案大小</b> <a id="upFile_size" class="float-right"></a>
                      </li>
                      <li class="list-group-item">
                        <b>目標資料表</b> <a id="upFile_table" class="float-right"></a>
                      </li>
                      <li class="list-group-item">
                        <b>ID順位</b> <a id="upFile_nextinsert" class="float-right"></a>
                      </li>
                      <li class="list-group-item">
                        <b>總紀錄數</b> <a id="upFile_totalrows" class="float-right"></a>
                      </li>
                      <li class="list-group-item">
                        <b>註記</b><div id="upFile_remark" class="col-sm-12"></div>
                      </li>
                    </form>
                  </ul>
                </div>
                <input type="hidden" id="currCsvID" name="currCsvID" value="0">
                <!-- /.card-body -->
                <?php if(!empty(auEDIT)){ ?>
                <div class="card-body" style="margin-top:-48px;">
                  <div class="form-group row">
                    <div class="col-sm-12">
                      <div class="icheck-primary d-inline">
                        <input type="radio" id="radioPrimary1" name="csvMode" value="i" disabled=disabled>
                        <label for="radioPrimary1" class="font-weight-light">新增&nbsp;&nbsp;</label>
                      </div>
                      <div class="icheck-primary d-inline">
                        <input type="radio" id="radioPrimary2" name="csvMode" value="u" disabled=disabled>
                        <label for="radioPrimary2" class="font-weight-light">更新&nbsp;&nbsp;</label>
                      </div>
                      <div class="icheck-green d-inline">
                        <input type="radio" id="radioPrimary3" name="csvMode" value="a" disabled=disabled>
                        <label for="radioPrimary3" class="font-weight-light">自動</label>
                      </div>
                      <div class="icheck-danger d-inline" style="padding-left:40px">
                        <input type="checkbox" id="doTruncate" name="doTruncate" value="t" disabled=disabled>
                        <label for="doTruncate" class="font-weight-light">清空</label>
                      </div>
                    </div>
                  </div>
                  <a id="but_import" class="btn btn-app"> <!-- bg-info -->
                    <span class="badge bg-danger"></span>
                    <i class="fas fa-solid fa-file-import"></i> 執行匯入
                  </a>
                  <a id="but_edit" class="btn btn-app"> <!-- bg-warning -->
                    <i class="fas fa-solid fa-pen-to-square"></i> 編輯
                  </a>
                  <a id="but_delete" class="btn btn-app"> <!-- bg-danger -->
                    <i class="fas fa-solid fa-trash-can"></i> 刪除檔案
                  </a>
                </div>
                <?php } ?>

              </div>
              <!-- /.card -->

            </div>
            <!-- /.col -->
            <div class="col-md-9">
              <div class="card card-secondary">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#tabMenu1" data-index="1" data-toggle="tab" data-topbut="0">檔案庫</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu2" data-index="2" data-toggle="tab" data-topbut="0">操作說明</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tabMenu3" data-index="3" data-toggle="tab" data-topbut="0">模板檔案下載</a></li>
                  </ul>
                </div><!-- /.card-header -->
                <div class="card-body">
                  <div class="tab-content">
                    <!-- 第一分頁【匯入資料檔案庫】 -->
                    <div class="tab-pane active" id="tabMenu1">
                      <div style="font-size:0.85em;line-height:1.4em;">
                        <i class="nav-icon fas fa fa-exclamation-circle" style="color:#DF8233;"></i>&nbsp;&nbsp;可於檔案庫中選擇要操作的檔案，對於不存在於檔案庫中新的匯入檔案，可以直接進行上傳操作，並同時進行檔案驗證，驗證通過後亦可直接進行匯入資料庫操作。
                      </div>
                      <div class="card-body">
                        <!--
                        <div class="form-group row">
                          <div class="col-sm-10">
                            <input type="file" name="csvUpload" id="csvUpload">
                          </div>
                        </div>
                        <hr>
                        -->
                        <table id="dataTable_csvList" class="table table-bordered table-striped table-hover" data-pagemax="25" data-dirto="" data-search>
                          <thead>
                            <tr>
                              <th>模式</th>
                              <th>最近更新</th>
                              <th>檔案抬頭</th>
                              <th>段落</th>
                              <th>批次</th>
                              <th>檔案種類</th>
                              <th>大小(Kb)</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                              $sql="SELECT * FROM sys_csvio WHERE csvio_status=?";
                              $srr = $DB->query($sql,array(1));
                              $i = 0; //以$i為$nameList這個陣列的索引值
                              while($i<count($srr)){ 
                                ?>
                                <tr style="cursor:pointer;" data-csvid="<?php echo $srr[$i]['csvio_id'] ?? ''; ?>">
                                  <td><?php echo $colJS['mode'][$srr[$i]['csvio_mode']] ?? '&nbsp;'; ?></td>
                                  <td><?php echo $srr[$i]['csvio_updated'] ?? '&nbsp;'; ?></td>
                                  <td><?php echo $srr[$i]['csvio_filetitle'] ?? '&nbsp;'; ?></td>
                                  <td><?php echo $srr[$i]['csvio_part'] ?? '&nbsp;'; ?></td>
                                  <td><?php echo $srr[$i]['csvio_batch'] ?? '&nbsp;'; ?></td>
                                  <td><?php echo $srr[$i]['csvio_filetype'] ?? '&nbsp;'; ?></td>
                                  <td><?php echo number_format($srr[$i]['csvio_size'] ?? 0, 2, '.', ',') ?? '&nbsp;'; ?></td>
                                </tr>
                            <?php  
                                $i++;
                                } 
                            ?>
                          </tbody>
                          <tfoot>
                            <tr>
                              <th>模式</th>
                              <th>最近更新</th>
                              <th>檔案抬頭</th>
                              <th>段落</th>
                              <th>批次</th>
                              <th>檔案種類</th>
                              <th>大小(Kb)</th>
                            </tr>
                          </tfoot>
                        </table>

                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第一分頁【匯入資料檔案庫】 -->
                    <!-- 第二分頁【主要】主體編輯 -->
                    <div class="tab-pane" id="tabMenu2">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                        <i class="nav-icon fas fa fa-exclamation-circle"></i>&nbsp;&nbsp;使用匯入功能時，請特別注意操作流程以及規則，以避免資料庫結構以及數據，在不當的操作下受到損壞。
                      </div>
                      <hr>
                      <div class="card-body">

                        <ul class="list-group list-group-unbordered mb-3">
                          <li class="list-group-item">
                            <b>步驟一、</b><a class="float-right">建立合於規定結構的 CSV 檔案。</a>
                            <div class="col-sm-12">一個合於規定的 CSV 檔案，必須具備三個條件，一是要有首列的基本操作訊息；二是要有次列的相對應的資料表欄位名稱，而這個欄位名稱必須與資料表的欄位名稱完全的對應，任何一欄的欄位名稱不同於所對應的資料表欄位名稱，將導致匯入失敗；三是有效的資料內容依列及欄自第四列開始填入，系統預設自第四列開始讀取資料。</div>
                            <div style="padding:8px 0 8px 0;"><img src="../imageFiles/csvio/csvio_step1-1.png"></div>
                            <div>
                              <ul style="list-style-type:disc;">
                                <li>第一列A欄位固定為「 DataBase: 」做為驗證用；B欄位為資料庫名稱，在本站固定為「 5seasonlife 」；C欄位固定為「 Table Name: 」做為驗證用；D欄位為資料表名稱，亦即是這個檔案中的資料準備匯入的目的資料表名稱；E欄位固定為「 StartRow: 」做為驗證用；F欄位為有效資料起始列，在本站固定為「 4 」；G欄位固定為「 EndRow: 」做為驗證用；H欄位為有效資料結束列，亦即本檔案最後的資料列；</li>
                                <li>第二列為資料表欄位名稱(英文)，例如欄位C為「建案名稱」，則這個欄位就必須填入「bc_title」，與資料表 fh.buildcase 中的「建案名稱」欄位名一致，而不屬於目標資料表中的欄位，在匯入檔案前就必須除去。此列的欄位可以選擇性設置，非必要寫入的欄位可以忽略，系統將自動執行僅出現於檔案中的欄位。</li>
                                <li>第三列同樣為名稱欄位，只是可以不與資料表欄位名稱一致，僅做為操作者明確註記使用，可以為任何內容。</li>
                                <li>第四列之後則為有效的匯入資料，直至尾列。</li>
                              </ul>
                            </div>
                          </li>
                          <li class="list-group-item">
                            <b>步驟二、</b><a class="float-right">以有效的檔案名稱儲存</a>
                            <div class="col-sm-12">檔案名稱是系統第一關的辨識依據，必須指定一個有效的格式才能夠順利進行匯入，檔案名稱規則為：<br><b>【檔案抬頭(不帶標點符號)】_【簡述(可帶標點符號「，」以及「。」)】_【日期時間(Ymdhis)】_【批號】.csv</b><br>其中「批號」的定義為相同的匯入資料(相同的「檔案抬頭」)中，所拆分出的不同檔案，這些檔案依照數字號碼順序排列，依序進行匯入。</div>
                            <div><img src="../imageFiles/csvio/csvio_step2-1.png"></div>
                          </li>
                          <li class="list-group-item">
                            <b>步驟三、</b><a class="float-right">將檔案上傳至檔案庫</a>
                            <div class="col-sm-12">使用管理介面上傳檔案至伺服器中，上傳的檔案將經過系統的一系列驗證，通過驗證後始得入庫，上傳成功的同時，亦可繼續進行資料匯入的操作，以完成最終的匯入目的。</div>
                            <div><img src="../imageFiles/csvio/csvio_step3-1.jpg"></div>
                          </li>
                          <li class="list-group-item">
                            <b>步驟四、</b><a class="float-right">選擇並檢視待匯入的檔案</a>
                            <div class="col-sm-12">請記得，當檔案上傳成功的同時，即代表已經選定了這個檔案準備匯入資料，您可以繼續操作或是選擇另一個已經存在於檔案庫中的檔案，做為即將匯入的資料檔案，也同時可以編輯或是刪除這個已選定的檔案。當選定了匯入檔案，於左側視窗中將出現這個檔案的詳細訊息作為匯入前的參考。</div>
                            <div><img src="../imageFiles/csvio/csvio_step4-1.png"></div>
                          </li>
                          <li class="list-group-item">
                            <b>步驟五、</b><a class="float-right">執行匯入</a>
                            <div class="col-sm-12">在檢視完成待匯入檔案的資訊後，便可進行匯入操作，在按下匯入按鍵之前，必須先行選擇匯入的模式，匯入模式共分三種，分別是「新增」、「更新」以及「自動」，在「新增」模式下，也可以勾選「清空」先行清空目標資料表中的資料。</div>
                            <div><img src="../imageFiles/csvio/csvio_step5-1.png"></div>
                            <div>
                              <ul style="list-style-type:disc;">
                                <li><b>新增： </b>「新增」模式匯入時，系統將依鍵值順位自動賦予沒有ID值的匯入資料一個新的ID，而有ID值的匯入資料將被剔除忽略，也就是說，ID欄位賦值過後便認定為更新用途，便無法新增進入資料庫。而所有匯入的紀錄皆為新增，系統將無法辨識資料表中已經存在相同的匯入資料。</li>
                                <li><b>更新： </b>「更新」模式匯入時，每筆匯入資料必須含有ID鍵值始得有效進行匯入，沒有ID鍵值的匯入資料將被系統忽略，也就是說，更新模式不接受無值的ID欄位。這個模式不會新增紀錄，只會將匯入資料更新在對應ID的紀錄中。</li>
                                <li><b>自動： </b>「自動」模式匯入時，系統將針對每一筆匯入資料，自動辨識選擇使用新增或是更新，帶有ID鍵值的匯入資料將更新對應的ID紀錄，有ID鍵值卻沒有對應紀錄的匯入資料將被系統忽略，沒有ID鍵值則將新增一筆紀錄並同時賦予一個新的ID鍵值。</li>
                                <li><b>清空： </b> 在「新增」模式下，操作者可以勾選先行清空目標資料表中的資料，這個功能動作產生的結果為不可逆，請慎思而行。「更新」以及「自動」模式不接受操作前清空紀錄。</li>
                              </ul>
                            </div>
                          </li>
                        </ul>

                      </div>
                    </div>
                    <!-- /第二分頁【主要】主體編輯 -->
                    <!-- 第三分頁授權紀錄 -->
                    <div class="tab-pane" id="tabMenu3">
                      <div style="font-size:0.85em;line-height:1.4em;text-align:left;">
                        <i class="nav-icon fas fa fa-exclamation-circle"></i>&nbsp;&nbsp;範例檔案即是模板，下載範例檔案後，可以直接套用填寫數值後進行匯入。
                      </div>
                      <hr>
                      <!-- /.card-header -->
                      <div class="card-body">
                      <div class="form-group row">
                            <div class="col-sm-6">
                              <button type="button" class="btn btn-block btn-info btn-lg" id="csvdown_csvtest" name="csvdown_csvtest" value="../dataFiles/csvio/csv_template_csvtest.csv">測試匯入模板【fn.csvtest】</button>
                            </div>
                            <div class="col-sm-6"></div>
                          </div>
                          <div class="form-group row">
                            <div class="col-sm-6">
                              <button type="button" class="btn btn-block btn-primary btn-lg" id="csvdown_buildcase" name="csvdown_buildcase" value="../dataFiles/csvio/csv_template_buildcase.csv">社區建案匯入模板【fn.data_buildcase】</button>
                            </div>
                            <div class="col-sm-6"></div>
                          </div>
                          <div class="form-group row">
                            <div class="col-sm-6">
                              <button type="button" class="btn btn-block btn-success btn-lg" id="csvdown_aprret" name="csvdown_aprret" value="../dataFiles/csvio/csv_template_aprret.csv">實價登錄資料匯入模板【fn.data_aprret】</button>
                            </div>
                            <div class="col-sm-6"></div>
                          </div>

                      </div>
                      <!-- /.card-body -->
                    </div>
                    <!-- /第三分頁授權紀錄 -->
                  </div>
                  <!-- /.tab-content -->
                </div><!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div>
        <!-- /.container-fluid -->

      </section>
      <!-- /主要內容區域 ------------------------------------ -->
    </div>
    <!-- /頁內全頁內容 -->

    <!-- jQuery -->
    <script src="../pluginFiles/jquery/jquery.min.js"></script>

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
<!-- <script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script> -->
<!-- Bootstrap 5 -->
<script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
<!-- DataTables2 & Plugins -->
<!--
<script src="../pluginFiles/datatables/datatables/jquery.dataTables.min.js"></script>
<script src="../pluginFiles/datatables/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../pluginFiles/datatables/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../pluginFiles/datatables/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
-->
<!-- DataTables3 & Plugins -->
<script src="../pluginFiles/datatables3/datatables.min.js"></script>
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

  var colobj = <?php echo json_encode($colJS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
////////////////////////////////////////////////////////////////
// ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
// ---------------------------------------------------------- //
////////////////////////////////////////////////////////////////
  $(document).ready(function() {
    //* iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });
    //------------------------------------------------------
    var csvlistTB = datatableCreate('dataTable_csvList');

    $('#csvUploadFile').change(function(event){
      /*
      var ev = event.target.files[0],
        tmppath = URL.createObjectURL(ev),//取得圖檔暫存路徑
        fSplit = ev.name.split('.'),//
        fName = fSplit[0],//
        fExt = fSplit[1],//
        fSize = Math.round(ev.size / 1024),//取得準備上傳的檔案大小（Kb）
        t = new Date(ev.lastModified),
        fUpdate = t.toISOString().split('T')[0]+' '+t.toISOString().split('T')[1].slice(0,8);
        //showPopup = tmppath;
      */
      //[檔案抬頭(不帶標點符號)]_[簡述(不帶標點符號)]_[日期時間(Ymdhis)]_[批次]_[版本].csv
      //var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.csv|.txt)$/;
      const upthis = $(this);
      let veriInfo,veriCol;
      var file_data = upthis.prop('files')[0];   //取得上傳檔案屬性
      //var file_name = upthis.val().split('\\').pop();
      var file_name = file_data.name;
      var file_purename = file_name.split('.');
      var file_nameinfo = file_purename[0].split("_");
      if(file_nameinfo.length!=4 || $.isNumeric(file_nameinfo[3])!=true){
        upthis.val('');
        window.parent.toastr['error']("匯入檔案命名格式錯誤，請更名後重新上傳！");
        return false;
      }
      var file_title = file_nameinfo[0];
      var file_remark = file_nameinfo[1];
      var file_create = file_nameinfo[2];
      var file_part = file_nameinfo[3];
      //var file_batch = file_nameinfo[4];
      var file_ext = file_purename[1];
      var file_size = (file_data.size / 1024).toFixed(2);//MB
      //比對檔案大小
      if(file_size>50000){//驗證檔案大小
          upthis.val('');
          window.parent.toastr['error']("檔案超過 50M 最高上限，請分割後分批上傳及匯入！");
          return false;
      }
      //console.log(file_nameinfo);return false;
      var regex = /(.csv|.txt)$/;
      //if (regex.test(upthis.val().toLowerCase())) {
      if (regex.test(file_name.toLowerCase())) {
        if (typeof(FileReader) != "undefined") {
          let clearCont=false;
          var reader = new FileReader();
          reader.onload = function (e) {
            var rows = e.target.result.split("\n");
            veriInfo = rows[0].split(",");//取得第一列資料
            veriCol = rows[1].split(",");//取得第二列資料\
            //alert(veriCol.length); return false;
            //比對第一列資料表欄位驗證值
            if(
              veriInfo[0].toLowerCase()!='database:' ||
              veriInfo[1].toLowerCase()!='5seasonlife' ||
              veriInfo[2].toLowerCase()!='table name:' ||
              //veriInfo[3].substr(0,3).toLowerCase()!='fh.' ||
              veriInfo[4].toLowerCase()!='startrow:' ||
              $.isNumeric(veriInfo[5])!==true ||
              veriInfo[6].toLowerCase()!='endrow:' ||
              $.isNumeric(veriInfo[7])!==true
            ){
              upthis.val('');
              window.parent.toastr['error']("匯入檔案文件資訊欄位值比對不符！");
              return false;
            }
            //比對第二列資料表欄位名稱，當有不符的欄位名稱，則回傳錯誤
            if(typeof colobj[veriInfo[3].replace('fh.','')]==='undefined'){
              upthis.val('');
              window.parent.toastr['error']("系統找不到任何可以比對的資料表，操作中止！");
              return false;
            }
            var colArr = colobj[veriInfo[3].replace('fh.','')]['cols'].split(",");
            for (var i = 0; i < veriCol.length; i++) {
              if($.trim(veriCol[i])==''){ continue; }//當資料欄位數量少於驗證值欄位時，空白欄位將被忽略
              else if($.inArray($.trim(veriCol[i]), colArr) == -1){
                window.parent.toastr['error']("資料欄位比對不符，請檢查匯入檔案的資料欄位名稱！！");
                upthis.val('');
                break;
              } 
            }
            //執行上傳
            //創建上傳元件
            var form_data = new FormData();  //建構new FormData()

            form_data.append('csvfile', file_data);  //吧課程商品加到file後面
            form_data.append('file_table', veriInfo[3].replace('fh.','')); 
            form_data.append('file_title', file_title); 
            form_data.append('file_remark', file_remark); 
            form_data.append('file_create', file_create); 
            //form_data.append('file_batch', file_batch); 
            form_data.append('file_part', file_part); 
            form_data.append('file_size', file_size); 
            form_data.append('file_ext', file_ext); 
            form_data.append('file_rows', veriInfo[7]-veriInfo[5]+1); 
            form_data.append('formAcce', 'func_ad_csvupload'); 
            form_data.append('token', getTokenVal()); 

            $.ajax({
                url: '../funcFiles/ad_act_func.php',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,     //data只能指定單一課程商品                 
                type: 'post',
                dataType: 'json', //data format  
                  async: false, 
              beforeSend:function(){//送出前的動作
                  if(!confirm('確定將這個檔案「'+file_name+'」上傳至伺服器中，並準備進行匯入前的檔案解析？！')){return false;}
              },
              success: function(data){
                console.log(data);
                if(data['RTN']==1){
                  //------------------------------
                  //var oTable = $('#dataTable_csvList').DataTable();
                  var rowNode = csvlistTB.row.add([colobj['mode'][data['fInfo']['csvio_mode']],data['fInfo']['csvio_updated'],data['fInfo']['csvio_filetitle'], data['fInfo']['csvio_part'], data['fInfo']['csvio_batch'], data['fInfo']['csvio_filetype'],data['fInfo']['csvio_size']]).draw().node();
                  csvlistTB.search(data['fInfo']['csvio_filetitle']).draw();
                  $(rowNode).attr("data-csvid",data['fInfo']['csvio_id']).addClass('csvarmed').css( 'background-color', 'rgba(23, 162, 184, 0.5)').css( 'cursor', 'pointer').animate({color: 'black'});
                  //------------------------------
                  var totalRows = parseInt(veriInfo[7])-parseInt(veriInfo[5])+1;
                  $('#upFile_title').html('<input type="text" id="csvio_filetitle" name="csvio_filetitle" class="form-control" value="'+file_title+'">');
                  $('#upFile_name').html(data['fInfo']['csvio_filename']);
                  $('#upFile_part').html(data['fInfo']['csvio_part']);
                  $('#upFile_batch').html(data['fInfo']['csvio_batch']);
                  $('#upFile_ext').html(file_ext.toUpperCase());
                  $('#upFile_create').html(file_create);
                  $('#upFile_size').html(file_size);
                  $('#upFile_table').html(veriInfo[3]);
                  $('#upFile_nextinsert').html(parseInt(data['fInfo']['getLastID'])+1);
                  $('#upFile_totalrows').html(totalRows);
                  $('#upFile_remark').html('<textarea id="csvio_filetitle" name="csvio_filetitle" class="form-control" rows="3">'+file_remark+'</textarea>');
                  $('#currCsvID').val(data['fInfo']['csvio_id']);
                  $('#but_import').addClass('bg-info');//bg-info bg-warning bg-danger
                  $('#but_import').find('span').html(totalRows);
                  $('#but_edit').addClass('bg-warning');
                  $('#but_delete').addClass('bg-danger');
                  $('input[name="csvMode"]').removeAttr("disabled");
                  //$('#doTruncate').removeAttr("disabled");
                  //$('#radioPrimary1').removeAttr("disabled");
                  //$('#radioPrimary2').removeAttr("disabled");
                  //------------------------------
                  upthis.val('');
                  window.parent.toastr['success']("匯入檔案已經驗證成功並備妥，請準備進行下一步「匯入」操作！");
                }
              }
            });//$.ajax
          }//reader.onload
          reader.readAsText(upthis[0].files[0]);
        } else {
          upthis.val('');
          window.parent.toastr['error']("這個瀏覽器不支援 HTML5！");
          return false;
        }//FileReader
      } else {
        upthis.val('');
        window.parent.toastr['error']("請上傳有效的 CSV 檔案，或格式化過後的 TXT 檔案！");
        return false;
      }//regex.test

    });

    $('#dataTable_csvList').on('click',"tbody tr",function(e){ 
    //$("#dataTable_csvList tbody tr").click(function(){
      var csvID = $(this).attr('data-csvid');
      var el = $(this);
      //alert(csvID);return false;
      $.ajax({
          type: 'post',
          url: '../funcFiles/ad_act_func.php',
          data: {
            'formAcce':'func_ad_getcsvinfo',
            'token': getTokenVal(), 
            'csvID': csvID
          },     //data只能指定單一課程商品                 
          dataType: 'json', //data format  
            async: false,
        success: function(data){
          //console.log(data);
          window.parent.toastr[data['msgType']](data['msgStr']);
          if(data['RTN']==1){
            if (el.css('background-color') == 'rgba(23, 162, 184, 0.5)') {
              el.css( 'background-color', '').removeClass('csvarmed');
              $('#upFile_title').html('');
              $('#upFile_name').html('');
              $('#upFile_part').html('');
              $('#upFile_batch').html('');
              $('#upFile_ext').html('');
              $('#upFile_create').html('');
              $('#upFile_size').html('');
              $('#upFile_table').html('');
              $('#upFile_nextinsert').html('');
              $('#upFile_totalrows').html('');
              $('#upFile_remark').html('');
              $('#currCsvID').val('0');
              $('#but_import').removeClass('bg-info');//bg-info bg-warning bg-danger
              $('#but_import').find('span').html('');
              $('#but_edit').removeClass('bg-warning');
              $('#but_delete').removeClass('bg-danger');
              $('input[name="csvMode"]').prop('checked', false).prop('disabled', 'disabled');
              $('#doTruncate').prop('checked', false).prop('disabled', 'disabled');
              
            }
            else {
              el.parent().find('tr').removeClass('csvarmed').css( 'background-color', '').animate({color: 'black'});
              el.addClass('csvarmed').css( 'background-color', 'rgba(23, 162, 184, 0.5)').animate({color: 'black'});
              let fSize = parseFloat(data['fInfo']['csvio_size']).toFixed(2);
              $('#upFile_title').html('<input type="text" id="csvio_filetitle" name="csvio_filetitle" class="form-control" value="'+data['fInfo']['csvio_filetitle']+'">');
              $('#upFile_name').html(data['fInfo']['csvio_filename']);
              $('#upFile_part').html(data['fInfo']['csvio_part']);
              $('#upFile_batch').html(data['fInfo']['csvio_batch']);
              $('#upFile_ext').html(data['fInfo']['csvio_filetype'].toUpperCase());
              $('#upFile_create').html(data['fInfo']['csvio_published']);
              $('#upFile_size').html(fSize+' Kb');
              $('#upFile_table').html(data['fInfo']['csvio_table']);
              $('#upFile_nextinsert').html(parseInt(data['fInfo']['csvio_lastinsert'])+1);
              $('#upFile_totalrows').html(data['fInfo']['csvio_rows']);
              $('#upFile_remark').html('<textarea id="csvio_filetitle" name="csvio_filetitle" class="form-control" rows="3">'+data['fInfo']['csvio_remark']+'</textarea>');
              $('#currCsvID').val(data['fInfo']['csvio_id']);
              $('#but_import').addClass('bg-info');//bg-info bg-warning bg-danger
              $('#but_import').find('span').html(data['fInfo']['csvio_rows']);
              $('#but_edit').addClass('bg-warning');
              $('#but_delete').addClass('bg-danger');
              $('input[name="csvMode"]').prop('checked', false).removeAttr("disabled");
              $('#doTruncate').prop('checked', false).prop('disabled', 'disabled');
            }
          }
        }
      });//$.ajax

    });

    //----------- 執行按鈕
    $('button[id^="csvdown_"]').click(function(){
      var filePath = $(this).val();
      var daTa = {
        'downfile': filePath,
        'bkUrl': '../pageFiles/tool_csvio.php?tabIDX=3'
      }
      postDataToken('../funcFiles/file_download_func.php',daTa);
    });

    $('input[name="csvMode"]').change(function(){
      if($(this).val()=='i'){
        $('#doTruncate').prop('checked', false).removeAttr('disabled');
      } else {
        $('#doTruncate').prop('checked', false).prop('disabled', 'disabled');
      }
    });

    $('#but_import').click(function(){//bg-info bg-warning bg-danger
      if($(this).hasClass("bg-info")!==true){ return false; }
      var csvModeTXT;
      if($("input:radio[name='csvMode']").is(":checked")==false){
        window.parent.toastr['error']('匯入時必須選擇匯入方式：「新增」、「更新」或是「自動」！');
        return false;
      }
      var radioVal = $('input[name="csvMode"]:checked').val();
      var doTrunVal = $('#doTruncate').is(":checked") ? 't' : '';
      if(radioVal=='i'){ csvModeTXT='新增'; }
      else if(radioVal=='u'){ csvModeTXT='更新'; }
      else{ csvModeTXT='自動'; }
      $.ajax({
              type: 'post',
              url: '../funcFiles/ad_act_func.php',
              data: {
                'formAcce': 'func_ad_csvimport',
                'csvID': $('#currCsvID').val(), 
                'csvMode': radioVal,
                'csvTrun': doTrunVal,
                'csvName': $('#upFile_name').html(), 
                'token': getTokenVal()
              },
              dataType: 'json',
              async: false, 
              beforeSend:function(){//送出前的動作
                  if(!confirm('您正準備以「'+csvModeTXT+'」模式匯入檔案「'+$('#upFile_title #csvio_filetitle').val()+'」中的資料進入資料表 '+$('#upFile_table').html()+'，一旦進行匯入便至完成前無法中止！請再次確認匯入資料並無損傷原資料結構的疑慮，確定進行下一步？！')){return false;}
              },
            success: function (data) {
              console.log(data);
                window.parent.toastr[data['msgType']](data['msgStr']);
                if(data['RTN']==1){
                  csvlistTB.cell($('#dataTable_csvList tbody').find('tr.csvarmed'), 2).data($('#csvio_filetitle').val())
                } 
              },
              error: function (data) {
                console.log(data);
                window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
              }
          });

    });


    $('#but_edit').click(function(){//bg-info bg-warning bg-danger
      if($(this).hasClass("bg-warning")!==true){ return false; }
      $.ajax({
            type: 'post',
              url: '../funcFiles/ad_act_func.php',
            data: {
              'formAcce': 'func_ad_csvedit',
              'csvID': $('#currCsvID').val(), 
              'arrDatA': {
                csvio_filetitle:    $('#csvio_filetitle').val(),                
                csvio_remark:       $('#csvio_remark').val()
              }, 
              'token': getTokenVal()
            },
            dataType: 'json',
            async: false, 
            beforeSend:function(){//送出前的動作
                if(!confirm('您正準備編輯匯入檔案「'+$('#upFile_title #csvio_filetitle').val()+'」，確定進行下一步？！')){return false;}
            },
          success: function (data) {
              window.parent.toastr[data['msgType']](data['msgStr']);
              if(data['RTN']==1){
                csvlistTB.cell($('#dataTable_csvList tbody').find('tr.csvarmed'), 2).data($('#csvio_filetitle').val())
              } 
            },
            error: function (data) {
              console.log(data);
              window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
            }
        });

    });


    $('#but_delete').click(function(){//bg-info bg-warning bg-danger
      if($(this).hasClass("bg-danger")!==true){ return false; }
      $.ajax({
              type: 'post',
              url: '../funcFiles/ad_act_func.php',
              data: {
                'formAcce': 'func_ad_csvdelete',
                'csvID': $('#currCsvID').val(), 
                'csvName': $('#upFile_name').html(), 
                'token': getTokenVal()
              },
              dataType: 'json',
              async: false, 
              beforeSend:function(){//送出前的動作
                  if(!confirm('您正準備刪除匯入檔案「'+$('#upFile_title #csvio_filetitle').val()+'」的所有資料庫記錄，並同時刪除相關聯的檔案！這個動作將無法復原，確定進行下一步？！')){return false;}
              },
            success: function (data) {
                window.parent.toastr[data['msgType']](data['msgStr']);
                if(data['RTN']==1){
                  csvlistTB.row($('#dataTable_csvList tbody').find('tr.csvarmed')).remove().draw();
                  $('#upFile_title').html('');
                  $('#upFile_name').html('');
                  $('#upFile_part').html('');
                  $('#upFile_batch').html('');
                  $('#upFile_ext').html('');
                  $('#upFile_create').html('');
                  $('#upFile_size').html('');
                  $('#upFile_table').html('');
                  $('#upFile_nextinsert').html('');
                  $('#upFile_totalrows').html('');
                  $('#upFile_remark').html('');
                  $('#currCsvID').val('0');
                  $('#but_import').removeClass('bg-info');//bg-info bg-warning bg-danger
                  $('#but_import').find('span').html('');
                  $('#but_edit').removeClass('bg-warning');
                  $('#but_delete').removeClass('bg-danger');
                  $('input[name="csvMode"]').prop('checked', false).prop('disabled', 'disabled');
                  $('#doTruncate').prop('checked', false).prop('disabled', 'disabled');
                } 
              },
              error: function (data) {
                console.log(data);
                window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！！');
              }
          });

    });

  });


  ////////////////////////////////////////////////////////////////     
  // ------------------ JAVASCRIPTS FUNCTIONs ----------------- //
  // -------------- 可接收include檔案要求後自定義動作 ------------- //
  ////////////////////////////////////////////////////////////////
  function doReDirect(){
    window.history.back();
  }
  // 當「重置」按鈕按下時的動作
  function doButReset(){
    location.reload(true);
  }


</script>
</body>
</html>
