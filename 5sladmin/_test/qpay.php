<?php
require_once('../includeFiles/db.inc.php');
require_once('../includeFiles/common.lib.php');
$tabIDX = !empty($_REQUEST['tabIDX'])?intval($_REQUEST['tabIDX']):1;

$mbID = 96;
$pointArr = $DB->getTotalPoints($mbID);
$payArr = $DB->getTotalPayments($mbID);
$getCurrPoint = $pointArr['totalPoints'];
$geJS=_fn_getCachedJsonData("../jsonFiles/general.json");
//4143-5200-0187-0127
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
	
  <title> 人生第五季 Fifth Season Life｜QPay Test </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-bs4/css/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-responsive/css/responsive.bootstrap4.css">
  <link rel="stylesheet" href="../pluginFiles/datatables/datatables-buttons/css/buttons.bootstrap4.css">
  <!-- Toastr -->
  <link rel="stylesheet" href="../pluginFiles/toastr/toastr.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
  <!-- Dropzone JS style -->
  <link rel="stylesheet" href="../pluginFiles/dropzone/dropzone.css" />
  <!-- Cropper JS style -->
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <!-- suneditor -->
  <link rel="stylesheet" href="../pluginFiles/suneditor/dist/css/suneditor.min.css">
  <!-- codeMirror -->
  <link rel="stylesheet" href="../pluginFiles/codemirror/lib/codemirror.min.css">
    <!-- Custom JS style -->
  <link rel="stylesheet" href="../cssFiles/fh.fileupload.css">
  <link rel="stylesheet" href="../cssFiles/fh.lightbox.css">
  <link rel="stylesheet" href="../cssFiles/fh.formaccess.css">

<!-- -------------------------- /CSS Link -------------------------- -->
<style>
  .processingBut{
    cursor: pointer;
  }
</style>
</head>
<body>
  <!-- processing waiting -->
  <div id="loadingMask" style="top:0;left:0;position:absolute;display:block;width:100%;height:100%;background-color:#2A2826;background-position: 50% 0;opacity: 0.75;margin:0;text-align: center; z-index:99999;display:none;">
    <div style="color:#FFFFFF;position: relative;top: 50%;transform: translate(0, -50%);">
      <img src="../imageFiles/loading2.gif" style="left:50%;top:50%;"><br> 操作進行中，請稍候...
    </div>
  </div>
  <!-- 全頁框 -->
  <div class="wrapper">
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="../imageFiles/preloaderLOGO.png" alt="Fifth Season Life LOGO" height="60" width="60"> 
    </div>
    <!-- /Preloader -->
    <!-- 上方訊息區 -->
    <!-- /上方訊息區 -->
    <!-- 左側方導覽區 -->

    <!-- /左側方導覽區 -->
    <!-- 頁內全頁內容 -->
    <div>
      <!-- 頁頭導覽條 -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-12">
              <h5 class="m-0"><img src="../imageFiles/5SeasonLifeLOGOemail.jpg" style="vertical-align:bottom;"> 永豐銀行線上支付測試頁</span></h5>
              <hr>

            </div>
          </div>
        </div>
      </section>
      <!-- /頁頭導覽條 -->
      <!-- 主要內容區域 ------------------------------------- -->
      <section class="content">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-12">
              <h6 style="padding-left:12px;padding-bottom:24px;">加值時以白金會員享9折優惠為例，付款額計為總額之90%；而扣付時不與優惠折扣產生必然關係。</h6>
              <div class="form-group clearfix">
                <div class="icheck-primary d-inline" style="padding-left:12px;padding-right:54px;">
                  <input type="radio" id="pointsSEL1" name="pointsSEL" value="300" data-payamount="270" checked>
                  <label for="pointsSEL1">加值(支付$270)/扣付 300 點</label>
                </div>
                <div class="icheck-primary d-inline" style="padding-left:12px;padding-right:54px;">
                  <input type="radio" id="pointsSEL2" name="pointsSEL" value="500" data-payamount="450">
                  <label for="pointsSEL2">加值(支付$450)/扣付 500 點</label>
                </div>
                <div class="icheck-primary d-inline" style="padding-left:12px;">
                  <input type="radio" id="pointsSEL3" name="pointsSEL" value="1000" data-payamount="900">
                  <label for="pointsSEL3">加值(支付$900)/扣付 1000 點</label>
                </div>
              </div>
            </div>
          </div>
          <div class="row mb-2">
            <div class="col-sm-12" style="margin-bottom:20px;">
                <a class="btn btn-app bg-info onlinepay" data-paytype="C" style="width:140px;height:100px;margin-right:24px;border-radius:10px;">
                    <i class="fa-3x fa-solid fa-credit-card"></i><div style="padding-top:12px;font-size:1.2em;">信用卡支付</div>
                </a>
                <a class="btn btn-app bg-info onlinepay" data-paytype="A" style="width:140px;height:100px;margin-right:24px;border-radius:10px;">
                    <i class="fa-3x fa-solid fa-tachograph-digital"></i><div style="padding-top:12px;font-size:1.2em;">ATM 支付</div>
                </a>
                <a class="btn btn-app bg-info onlinepay" data-paytype="M" style="width:140px;height:100px;margin-right:24px;border-radius:10px;">
                  <i class="fa-3x fa-solid fa-comments-dollar"></i><div style="padding-top:12px;font-size:1.2em;">行動支付</div>
                </a>
                <a class="btn btn-app bg-info onlinepay" data-paytype="L" style="width:140px;height:100px;margin-right:36px;border-radius:10px;">
                  <i class="fa-3x fa-brands fa-line"></i><div style="padding-top:12px;font-size:1.2em;">LINE Pay</div>
                </a>
                <a class="btn btn-app bg-success outcome" style="width:180px;height:100px;border-radius:10px;">
                  <i class="fa-3x fa-brands fa-dollar-sign"></i><div style="padding-top:12px;font-size:1.2em;">以會員升等測試扣點</div>
                </a>
            </div>
            <hr>
          </div>
          <!-- /.row -->
        </div><!-- /.container-fluid -->
      </section>
      <hr>
      <section>

        <div class="card-body">
          <div class="card-header p-2">
            <ul class="nav nav-pills">
              <li class="nav-item"><a class="nav-link<?php if($tabIDX==1) echo " active"; ?>" href="#tabMenu1" data-toggle="tab">儲值紀錄</a></li>
              <li class="nav-item"><a class="nav-link<?php if($tabIDX==2) echo " active"; ?>" href="#tabMenu2" data-toggle="tab">點數使用紀錄</a></li>
              <li class="nav-item"><a class="nav-link<?php if($tabIDX==3) echo " active"; ?>" href="#tabMenu3" data-toggle="tab">發票開立紀錄</a></li>
            </ul>
          </div>
          <div class="tab-content">


            <!-- 第1分頁管理者資料修改 -->
            <div class="tab-pane<?php if($tabIDX==1) echo " active"; ?>" id="tabMenu1">
              <div class="form-group row">
                <div class="col-sm-4">支付總額： <span style="font-size:1.25em;font-weight:bold;color:red;"><?php echo number_format($payArr['totalIncome'], 2, '.', ','); ?></span> 元</div>
                <div class="col-sm-4">返現總額： <span style="font-size:1.25em;font-weight:bold;color:green;"><?php echo number_format($payArr['totalPayTo'], 2, '.', ','); ?></span> 元</div>
              </div>
              <table id="dataTable_payList" class="table table-bordered table-striped table-hover" data-butClass="gray" data-pagemax="25"  data-dirto="" data-search>
                <thead>
                  <tr class="gray">
                    <th>日期時間</th>
                    <th>訂單號碼</th>
                    <th>支付類型</th>
                    <th>支付總額</th>
                    <th>ATM轉帳號碼</th>
                    <th>購得點數</th>
                    <th>狀態</th>
                    <th>發票</th>
                  </tr>
                </thead>
                <tbody>
                  <?PHP
                    $prr = $DB->query("SELECT * FROM fh.data_payments WHERE mb_id=? AND pay_status<? ORDER BY pay_date ASC",array($mbID,9));
                    $i = 0; //以$i為$nameList這個陣列的索引值
                    $totalAmount=0;
                    while($i<count($prr)){
                      if($prr[$i]['pay_status']==2){
                        $fontColor="blue";
                      } else if($prr[$i]['pay_status']==3){
                        $fontColor="red";
                      } else if($prr[$i]['pay_status']==8){
                        $fontColor="gray";
                      } else {
                        $fontColor="";
                      }
                      //$invres = $DB->row("SELECT iv_id FROM fh.invoices WHERE mb_id=? AND pay_id=? AND iv_status=?",array($mbID,$prr[$i]['pay_id'],1));
                      ?>
                      <tr style="<?php echo !empty($fontColor) ? "color:".$fontColor : ''; ?>">
                        <td><?PHP echo $prr[$i]['pay_date'] ?? '&nbsp;'; ?></td>
                        <td><?PHP echo $prr[$i]['pay_orderno'] ?? '&nbsp;'; ?></td>
                        <td><?PHP echo $geJS['paytype'][$prr[$i]['pay_type']] ?? '&nbsp;'; ?></td>
                        <td>$<?PHP echo $prr[$i]['pay_amount'] ?? '&nbsp;'; ?></td>
                        <td><?PHP echo $prr[$i]['pay_atmpayno'] ?? '&nbsp;'; ?></td>
                        <td><?PHP echo $prr[$i]['pay_points'] ?? '&nbsp;'; ?><img src="../imageFiles/coin_24x24.png" width="16" height="16" style="margin-left:4px;"></td>
                        <td><?PHP echo $geJS['paystatus'][$prr[$i]['pay_status']] ?? '&nbsp;'; ?></td>
                        <td><?PHP echo !empty($prr[$i]['iv_id']) ? '<button type="button" class="btn btn-block btn-info btn-sm invBut" data-ivid="'.$prr[$i]['iv_id'].'">預覽發票</button>' : '<button type="button" class="btn btn-block btn-secondary btn-sm disabled">尚未開立</button>'; ?></td>
                      </tr>
                  <?PHP 
                      $i++;
                      } 
                    ?>
                </tbody>
                <tfoot>
                  <tr class="gray">
                    <th>日期時間</th>
                    <th>訂單號碼</th>
                    <th>支付類型</th>
                    <th>支付總額</th>
                    <th>ATM轉帳號碼</th>
                    <th>購得點數點數</th>
                    <th>狀態</th>
                    <th>發票</th>
                 </tr>
                </tfoot>
              </table>

            </div>
          
            <!-- 第2分頁管理者資料修改 -->
            <div class="tab-pane<?php if($tabIDX==2) echo " active"; ?>" id="tabMenu2">
              <div class="form-group row">
                <div class="col-sm-4">剩餘點數： <span style="font-size:1.25em;font-weight:bold;color:blue;"><?php echo $pointArr['totalPoints']; ?></span> 點</div>
              </div>
              <table id="dataTable_pointList" class="table table-bordered table-striped table-hover" data-butClass="gray" data-pagemax="25"  data-dirto="" data-search>
                <thead>
                  <tr class="gray">
                    <th>日期時間</th>
                    <th>交易類型</th>
                    <th>用途/來源</th>
                    <th>儲值點數</th>
                    <th>消費點數</th>
                    <th>剩餘點數</th>
                  </tr>
                </thead>
                <tbody>
                  <?PHP
                    $prr = $DB->query("SELECT * FROM fh.mb_points WHERE mb_id=? AND mbp_status=? ORDER BY mbp_date ASC",array($mbID,1));
                    $i = 0; //以$i為$nameList這個陣列的索引值
                    $totalAmount=0;
                    while($i<count($prr)){
                      $getForInfo = !empty($geJS['idfor'][$prr[$i]['mbp_for']]) ? $geJS['idfor'][$prr[$i]['mbp_for']] : array();//紀錄
                      $getFromInfo = !empty($getForInfo['joinid']) ? (!empty($geJS['idfor'][$getForInfo['joinid']]) ? $geJS['idfor'][$getForInfo['joinid']] :array()) : array();//項目
                      $getTypeInfo = '' ;
                      $oTypeIdx = intval($prr[$i]['mbp_type']);
                      if(!empty($oTypeIdx)){
                        $getTypeInfo = !empty($getFromInfo['otype'][$oTypeIdx]) ? $getFromInfo['otype'][$oTypeIdx] : (!empty($getForInfo['otype'][$oTypeIdx]) ? $getForInfo['otype'][$oTypeIdx] : '');
                      }
                      
                      //$idforTitle = $getForInfo['title'];
                      $totalAmount += $prr[$i]['mbp_trade'];
                      //$idforTitle = $DB->getIDFORtitle($prr[$i]['mbp_forid'],$getForInfo,$getFromInfo);
                      $idforTitle = $DB->getIDFORtitle($prr[$i]['mbp_forid'],$getForInfo,$getFromInfo,$getTypeInfo);
                      
                      /*
                      if(!empty($idforTitle)){

                      } else if(empty($idforTitle) && !empty($getForInfo['titlecol'])){
                        $ssql = "SELECT {$getForInfo['titlecol']} FROM {$getForInfo['table']} WHERE {$getForInfo['idcol']}=?";
                        $idforTitle = $DB->single($ssql,array($prr[$i]['mbp_forid']));
                      } else if(empty($idforTitle) && empty($getForInfo['titlecol']) && !empty($getForInfo['joinid'])){
                        $getFromInfo = $geJS['idfor'][$getForInfo['joinid']];
                        $idforTitle = $DB->getIDFORtitle($getFromInfo['table'],$getForInfo['table'],$getFromInfo['titlecol'],$getFromInfo['idcol'],$getForInfo['idcol'],$prr[$i]['mbp_forid']);
                      } else {
                        $idforTitle = $getForInfo['table'].".".$getForInfo['idcol']."=".$prr[$i]['mbp_forid'];
                      }
                        */
                      ?>
                      <tr>
                        <td><?PHP echo $prr[$i]['mbp_date'] ?? '&nbsp;'; ?></td>
                        <td><?PHP echo $geJS['idfor'][$prr[$i]['mbp_for']]['short'] ?? '&nbsp;'; ?></td>
                        <td><?PHP echo $idforTitle; ?></td>
                        <td><?PHP echo $prr[$i]['mbp_trade'] ?? '&nbsp;'; ?></td>
                        <td><?PHP echo $prr[$i]['mbp_trade'] ?? '&nbsp;'; ?></td>
                        <td><?PHP echo $totalAmount; ?></td>
                      </tr>
                  <?PHP 
                      $i++;
                      } 
                    ?>
                </tbody>
                <tfoot>
                  <tr class="gray">
                    <th>日期時間</th>
                    <th>交易類型</th>
                    <th>扣點來源</th>
                    <th>儲值點數</th>
                    <th>消費點數</th>
                    <th>剩餘點數</th>
                  </tr>
                </tfoot>
              </table>
            </div>
            <!-- 第3分頁管理者資料修改 -->
            <div class="tab-pane<?php if($tabIDX==3) echo " active"; ?>" id="tabMenu3">
  

              <table id="dataTable_invList" class="table table-bordered table-striped table-hover" data-butClass="gray" data-pagemax="25"  data-dirto="" data-search>
                <thead>
                  <tr class="gray">
                    <th>開立時間</th>
                    <th>發票類型</th>
                    <th>發票號碼</th>
                    <th>載具號碼</th>
                    <th>稅類</th>
                    <th>稅金</th>
                    <th>總額</th>
                    <th>買受人</th>
                    <th>統一編號</th>
                    <th>捐贈</th>
                    <th>通知</th>
                    <th>狀態</th>
                  </tr>
                </thead>
                <tbody>
                  <?PHP
                    $prr = $DB->query("SELECT * FROM fh.invoices WHERE mb_id=? AND iv_status>? AND iv_status<? ORDER BY iv_invoicedate ASC",array($mbID,0,9));
                    $i = 0; //以$i為$nameList這個陣列的索引值
                    while($i<count($prr)){
                  ?>
                      <tr>
                        <td><?PHP echo $prr[$i]['iv_invoicedate']; ?></td>
                        <td><?PHP echo $geJS['invtype'][$prr[$i]['iv_invoicetype']]; ?></td>
                        <td><?PHP echo $prr[$i]['iv_invoiceno']; ?></td>
                        <td><?PHP echo $prr[$i]['iv_carrier']; ?></td>
                        <td><?PHP echo $geJS['taxmode'][$prr[$i]['iv_taxmode']]; ?></td>
                        <td><?PHP echo $prr[$i]['iv_tax']; ?></td>
                        <td><?PHP echo $prr[$i]['iv_aftertax']; ?></td>
                        <td><?PHP echo $prr[$i]['iv_buyer']; ?></td>
                        <td><?PHP echo $prr[$i]['iv_buyerid']; ?></td>
                        <td style="text-align:center;"><?PHP echo !empty($prr[$i]['iv_donate']) ? '<i class="fa-solid fa-check" style="color:green;"></i>' : ''; ?></td>
                        <td style="text-align:center;"><?PHP echo !empty($prr[$i]['iv_notify']) ? '<i class="fa-solid fa-check" style="color:green;"></i>' : ''; ?></td>
                        <td><?PHP echo $geJS['invstatus'][$prr[$i]['iv_status']]; ?></td>
                      </tr>
                  <?PHP 
                      $i++;
                      } 
                    ?>
                </tbody>
                <tfoot>
                  <tr class="gray">
                    <th>開立時間</th>
                    <th>發票類型</th>
                    <th>發票號碼</th>
                    <th>載具號碼</th>
                    <th>稅類</th>
                    <th>稅金</th>
                    <th>總額</th>
                    <th>買受人</th>
                    <th>統一編號</th>
                    <th>捐贈</th>
                    <th>通知</th>
                    <th>狀態</th>
                  </tr>
                </tfoot>
              </table>

            </div>
          </div>  
        <!-- /.card-body -->

      </section>
      <section>
        <!-- -------------------- 裁切頭像彈出視窗 -------------------- -->
          <div id="qpay_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="background:rgba(0,0,0,0.75);">
            <div class="modal-dialog modal-dialog-scrollable modal-lg" style="width:50%;">
              <div class="modal-content">
                <!-- Header -->
                <div class="modal-header bg-secondary">
                  <h5 class="modal-title">ATM 支付訊息</h5>
                  <button type="button" id="modalCloseTop" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button> 
                </div>
                <!-- /Header -->
                <!-- Model Body -->
                <div class="modal-body">
                  <h6 class="modal-des"></h6>
                    <div class="modal-container"></div>
                </div>
                <!-- /Model Body -->
                <!-- Footer -->
                <div class="modal-footer justify-content-between">
                  <button type="button" id="modal_urlBut" class="btn btn-danger" data-tourl="">進入永豐銀行網路ATM頁面</button>
                  <button type="button" id="modal_doneBut" class="btn btn-primary">確定並關閉</button>
                </div>
                <!-- /Footer -->
              </div>
            </div>
          <!-- /.modal-dialog -->
          </div>
  
            <!-- -------------------- /裁切頭像彈出視窗 -------------------- -->
            <!-- -------------------- 裁切頭像彈出視窗 -------------------- -->
          <div id="qpayiv_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="background:rgba(0,0,0,0.75);">
            <div class="modal-dialog modal-dialog-scrollable modal-lg" style="width:50%;">
              <div class="modal-content">
                <!-- Header -->
                <div class="modal-header bg-secondary">
                  <h5 class="modal-title">準備線上支付-發票開立訊息</h5>
                  <button type="button" id="modalCloseTop" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <!-- /Header -->
                <!-- Model Body -->
                <div class="modal-body">
                  <h6 class="modal-des"></h6>
                    <div class="modal-container">

                      <div class="row mb-2" style="margin-left:24px;margin-right:24px;margin-bottom:36px;">
                        <div style="padding:8px;border:2px red solid;font-size:0.85em;border-radius:10px;">
                          <span style="font-weight:bold;">聲明事項: </span>本發票依北區國稅局OO稽徵所OOO年O月O日北區國稅OO字第OOOOOO函核准，於本網站消費開立之統一發票資訊， 依「電子發票實施作業要點」將於期限內上傳至整合服務平臺留存，消費者可利用財政部電子發票整合平臺查詢消費記錄。
                        </div>
                        <div class="card-body">
                          <div class="text-subtitle-1 font-weight-bold form-group row">選擇發票 (支付完成後依所選內容開立)</div>
                          <!-- 電子發票手機載具 -->
                          <div class="col-sm-12 form-group row">
                            <div class="icheck-primary d-inline">
                              <input type="radio" id="iv_ifcarrier" name="r1" value="N" checked>
                              <label for="iv_ifcarrier" class="font-weight-light"><span>電子發票手機載具&nbsp;&nbsp;&nbsp;&nbsp;</span></label>
                            </div>
                            <div class="d-inline">
                              <input type="text" class="form-control" id="iv_carrier" name="iv_carrier" placeholder="請輸入手機載具條碼" value="">
                            </div>
                            <div class="d-inline">
                            &nbsp;&nbsp;&nbsp;&nbsp;( <a href="https://www.einvoice.nat.gov.tw/accounts/signup/mw" target="_blank">申請載具</a> )
                            </div>
                          </div>
                          <div class="col-sm-12 form-group row">
                            <div class="icheck-primary d-inline">
                              <input type="radio" id="iv_ifinvoice" name="r1" value="Y">
                              <label for="iv_ifinvoice" class="font-weight-light"><span>寄送實體發票</span></label>
                            </div>
                          </div>
                          <hr>
                          <div class="col-sm-12 form-group row">
                              <div class="icheck-info d-inline" style="padding-right:54px;">
                                <input type="radio" id="invSEL1" name="invSEL" value="300" data-payamount="270" checked>
                                <label for="invSEL1">個人發票</label>
                              </div>
                              <div class="icheck-info d-inline">
                                <input type="radio" id="invSEL2" name="invSEL" value="500" data-payamount="450">
                                <label for="invSEL2">公司發票</label>
                              </div>
                          </div>
                          <div class="form-group row">
                            <div class="col-sm-7">
                              <input type="text" class="form-control" id="iv_buyer" name="iv_buyer" placeholder="請輸入公司名稱(個人發票則免填)" value="">
                            </div>
                            <div class="col-sm-5">
                              <input type="text" class="form-control" id="iv_buyerid" name="iv_buyerid" placeholder="公司統一編號(個人發票則免填)" value="">
                            </div>
                          </div>
                          <div class="form-group row">
                            <div class="col-sm-7">
                              <input type="text" class="form-control" id="iv_mailto" name="iv_mailto" placeholder="請輸入收件人名稱" value="">
                            </div>
                            <div class="col-sm-5">
                              <input type="text" class="form-control" id="iv_phone" name="iv_phone" placeholder="請輸入連絡電話" value="">
                            </div>
                          </div>
                          <div class="form-group row">
                            <div class="col-sm-12">
                              <input type="text" class="form-control" id="iv_mailadd" name="iv_mailadd" placeholder="請輸入收件地址" value="">
                            </div>
                          </div>
                          <hr>
                          <div class="col-sm-12 form-group row">
                            <div class="icheck-primary d-inline">
                              <input type="checkbox" id="iv_donation" name="iv_donation" value="1">
                              <label for="iv_donation" class="font-weight-light">發票捐贈公益團體或慈善機構</label>
                            </div>
                          </div>
                          <div class="form-group row">
                            <div class="col-sm-7">
                              <select class="form-control" id="iv_npoban" name="iv_npoban" style="width: 100%;">
                                <?php
                                    foreach($geJS['npoban'] as $key => $value){
                                        echo '<option value="'.$key.'"';
                                        if($key=="RS") { echo " selected"; }
                                        echo '>'.$value.'</option>';
                                    }
                                ?>
                              </select>
                            </div>
                          </div>
                          
                          <div class="form-group row">
                            <div class="col-sm-7">
                              <label for="iv_email" class="font-weight-light">訊息通知信箱(接收發票開立訊息)</label>
                              <input type="text" class="form-control" id="iv_email" name="iv_email" placeholder="請輸入訊息通知信箱" value="">
                            </div>
                          </div>


                        </div>
                    </div>
                </div>
                <!-- /Model Body -->
                <!-- Footer -->
                <div class="modal-footer justify-content-between">
                  <button type="button" id="modal_closeBut" class="btn btn-danger">放棄並結束</button>
                  <button type="button" id="modal_doneBut" class="btn btn-primary" data-paytype="">確認並進入支付作業</button>
                </div>
                <!-- /Footer -->
              </div>
            </div>
            <!-- /.modal-dialog -->
          </div>
  
        <!-- -------------------- /裁切頭像彈出視窗 -------------------- -->
      </section>
      <!-- /主要內容區域 ------------------------------------ -->
    </div>
    <!-- /頁內全頁內容 -->

    <!-- jQuery -->
    <script src="../pluginFiles/jquery/jquery.min.js"></script>
    <!-- 下方訊息區 -->

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
  <!-- DataTables  & Plugins -->
  <script src="../pluginFiles/datatables/datatables/jquery.dataTables.min.js"></script>
  <script src="../pluginFiles/datatables/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
  <!-- ezValidation -->
  <script src="../pluginFiles/validator/validator.js"></script>
  <!-- Toastr -->
  <script src="../pluginFiles/toastr/toastr.min.js"></script>
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
  <!-- Custom JS style -->
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/fileupload.js"></script>
  <script src="../scriptFiles/form.init.inc.js"></script>

  <!-- ------------------------ /Scripts Link ------------------------ -->

<script type="text/javascript">
/* https://smsapi.mitake.com.tw/api/mtk/SmSend?username=90113379SMS&password=Sms90113379&dstaddr=0919218661&smbody=testAPI&clientid=3 */

$(function () {
  var pointList = datatableCreate('dataTable_pointList');
  var payList = datatableCreate('dataTable_payList');
  $('.outcome').click(function(e){
    var pointSUM = $('input[name="pointsSEL"]:checked').val();
    if(!confirm('這個操作將測試會員為升級會員等級，共扣付了 '+ pointSUM +' 點數，此操作僅增加了扣點紀錄，並無實際聯動會員升等功能，確定繼續？！')){ return false; }
    $.ajax({
      type: 'post',
      url: '../api_userAction_func.php',//
      data: {
        formAcce: 'api_user_outgotest',
        mbID: 96,//登入會員ID
        payPoints: pointSUM,
        time: '1800000000',
        uid: '96',
        key: 'HHCsF7cIMqNu5SDw4x9T9w==',
        token: 'uNB6aNo2YfHZH6aEeaqmHfOihF2NRHq5nlLDgM6MNFQ='
      },
      dataType: 'json',
      //async: false,
      success: function (data) {
        console.log(data);
        $('#loadingMask').hide();
        //alert(data['payURL']);return false;
        if(data['done']==1){
          alert('成功扣除點數，現在返回點數紀錄頁面！');
          window.location.replace('http://10.19.0.101/_test/qpay.php?tabIDX=2');
          return true;
        } else {

        }
      },
      error: function (data) {
        //alert(JSON.stringify(data));
        console.log(data);
      }
    });

    

  });
  $('.onlinepay').click(function(e){
      var pType = $(this).attr('data-paytype');
    $('#qpayiv_modal #modal_doneBut').attr('data-paytype', pType);
    var textTitle = '';
    switch(pType){
        case 'A':
            textTitle = "您已選擇了「ATM線上支付」，系統將產生一組虛擬帳號，以供您至各家合作銀行的ATM機上轉帳，若是您使用永豐銀行的帳戶，也可以點選使用永豐銀行的線上ATM轉帳作業。";
        break;
        case 'C':
            textTitle = "您已選擇了「信用卡線上支付」，線上支付作業必須跳轉至金融業者的專屬頁面上進行。";
        break;
        case 'M':
            textTitle = "您已選擇了「行動支付」儲值點數，行動支付作業必須跳轉至金融業者的專屬頁面上進行。";
        break;
        case 'L':
            textTitle = "您已選擇了「 LINE Pay 」儲值點數，支付作業必須跳轉至金融業者的專屬頁面上進行。";
        break;
    }
    textTitle += '請先閱讀以下的聲明，並填寫正確的發票訊息以利作業！'
    $('#qpayiv_modal .modal-des').html(textTitle);
    $('#qpayiv_modal').show().addClass('show');
  });
  $('#qpayiv_modal #modalCloseTop, #qpayiv_modal #modal_closeBut').click(function(e){ 
        $('#qpayiv_modal').removeClass('show').hide();
  });
  //$('.onlinepay').click(function(e){
  $('#qpayiv_modal #modal_doneBut').click(function(e){
    var pType = $('#qpayiv_modal #modal_doneBut').attr('data-paytype');
    var pointSUM = $('input[name="pointsSEL"]:checked').val();
    var amountPay = $('input[name="pointsSEL"]:checked').attr('data-payamount');
    var ifPrintOut = $('input[name="r1"]:checked').val();
    if(ifPrintOut=='N'){
      if($.trim($('#iv_carrier').val())==''){
        alert('選擇電子發票時必須填寫載具號碼，若無載具號碼，請至財政部電子發票整合服務平台申辦！');
        return false;
      }
    } else if(ifPrintOut=='Y'){
      if($.trim($('#iv_mailto').val())=='' || $.trim($('#iv_phone').val())=='' || $.trim($('#iv_mailadd').val())==''){
        alert('選擇實體發票時必須填寫住址電話以及收件人，公司發票請填寫公司名稱以及統一編號！');
        return false;
      }
    }
    $('#loadingMask').show();  
    //-----------------------------------------------------
    var invoiceString = '';
    invoiceString += ifPrintOut + "|";
    invoiceString += $.trim($('#iv_carriertype').val()) + "|"; //new
    invoiceString += $.trim($('#iv_carrier').val()) + "|"; 
    invoiceString += ($.trim($('#iv_buyer').val())!='' ? $.trim($('#iv_buyer').val()) : '1234') + "|";//buyerCode from localstorage
    invoiceString += ($.trim($('#iv_buyerid').val())!='' ? $.trim($('#iv_buyerid').val()) : '0000000000') + "|";
    invoiceString += $.trim($('#iv_mailto').val()) + "|";
    invoiceString += $.trim($('#iv_phone').val()) + "|";
    invoiceString += $.trim($('#iv_mailadd').val()) + "|";
    invoiceString += ($('#iv_donation').is(':checked') ? 1 : 0) + "|";
    invoiceString += $.trim($('#iv_npoban').val()) + "|";
    invoiceString += $.trim($('#iv_email').val());
    //-----------------------------------------------------
    $('#qpayiv_modal').removeClass('show').hide();

    if(!confirm('確認進行線上支付作業？！')){ return false; }
    $.ajax({
      type: 'post',
      url: '../api_userAction_func.php',//
      data: {
        formAcce: 'api_user_onlinepay',
        mbID: 96,//登入會員ID
        mbUser: 'Jaz',
        mbLevel: 1,//登入會員等級
        payType: pType,
        payAmount: amountPay,
        payPoints: pointSUM,
        inVoice: invoiceString,
        time: '1800000000',
        uid: '96',
        key: 'HHCsF7cIMqNu5SDw4x9T9w==',
        token: 'uNB6aNo2YfHZH6aEeaqmHfOihF2NRHq5nlLDgM6MNFQ='
      },
      dataType: 'json',
      //async: false,
      success: function (data) {
        console.log(data);
        $('#loadingMask').hide();
        //alert(data['payURL']);return false;
        if(data['done']==1){
          if(typeof data['payNo'] !== "undefined" && data['payNo']!=='' && pType=='A'){
              var html = '<h6>';
                  html += '您已成功新建一筆線上支付帳單！';
                  html += '</h6>';
                  html += '<div>';
                  html += '帳單編號：';
                  html += '<span style="font-size:1.2em;font-weight:bold;">';
                  html += data['OrderNo'];
                  html += '</span>';
                  html += '</div>';
                  html += '<div>';
                  html += '待繳金額：';
                  html += '<span style="font-size:1.2em;font-weight:bold;">';
                  html += data['payAmount'];
                  html += '</span>';
                  html += '</div>';
                  html += '<div style="margin-top:24px;color:red;">';
                  html += '永豐銀行虛擬帳號：';
                  html += '</div>';
                  html += '<div style="width:280px;border:2px red solid;left:0px;right:0px;font-size:2em;font-weight:bold;text-align:center;">';
                  html += data['payNo'];
                  html += '</div>';
                  html += '</div>';
                  html += '<div style="margin-top:24px;">';
                  html += '選擇ATM支付方式付費時，會產生一組永豐銀行14碼的虛擬帳號，繳款金額須與訂單金額相符合，金額不正確會被退款。虛擬帳號支付不能重複繳款，並且僅能選擇 ATM 或 WebATM 轉帳或匯款方式付費。若是使用永豐銀行的網路ATM，可以直接點選下方的「進入永豐銀行網路ATM頁面」按鍵！';
                  html += '</div>';
              $('#qpay_modal .modal-container').html(html);
              $('#qpay_modal #modal_urlBut').attr('data-tourl',data['payURL']);
              $('#qpay_modal').show().addClass('show');
              return false;
          } else {
              if(typeof data['payURL'] !== "undefined" && data['payURL']!=''){
                  window.location.replace(data['payURL']);
              }
              return false;
          }
        } else {
          var mess = "\r\n永豐銀行QPay回傳錯誤，操作終止！錯誤訊息如下:\r\n";
          mess += "-------------------------------------- \r\n";
          mess += "\r\n訂單編號: " + data['QPayOrder']['OrderNo'] + " \r\n";
          mess += "錯誤訊息:  \r\n";
          mess += "\r\n【 " +  data['QPayOrder']['Description'] + " 】\r\n";
          alert(mess);
        }
      },
      error: function (data) {
        //alert(JSON.stringify(data));
        console.log(data);
      }
    });

  });
    //-----------------  -----------------------
    $('.invBut').click(function(e){ 
        var invID = $(this).attr('data-ivid');
        alert('開啟發票【 ID：'+invID+' 】預覽頁面！');
    });
    //-----------------  -----------------------

    $('#modal_urlBut').click(function(e){ 
        var toURL = $(this).attr('data-tourl');
        //$('#qpay_modal').removeClass('show').hide();
        window.location.replace(toURL);
    });
    //----------------- 彈出窗關閉按鍵 -----------------------
    $('#qpay_modal #modalCloseTop, #qpay_modal #modal_doneBut').click(function(e){ 
        $('#qpay_modal .modal-title').html('');
        $('#form_qpay_modal').attr('data-ruleid','0');
        $('#qpay_modal .modal-container').html('');
        //$('#qpay_modal #modal_upBut').html('');
        //$('#qpay_modal #modal_delBut').css("visibility", "hidden");
        $('#qpay_modal').removeClass('show').hide();
    });


});
</script>
</body>
</html>
