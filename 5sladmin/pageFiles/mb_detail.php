<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 定義本頁面代碼，用於 SideBar 開合與頂部操作權限連動
define('PAGE_MARK','PG_MEM');
define('PAGE_ITEM','ITEM_MEM');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$mbID = currID;
$res  = ($mbID > 0) ? DB->row("SELECT * FROM fsl_members WHERE mb_id = ? AND mb_status != 9", array($mbID)) : array();
if (!is_array($res)) $res = array();

// 敏感加密欄位解密供後台檢視編輯（手機、身分證、地址）
$decPhone = (!empty($res['mb_cellphone'])) ? _fn_secure_crypto($res['mb_cellphone'], 'de') : '';
//$decIdno  = (!empty($res['mb_idno']))  ? _fn_secure_crypto($res['mb_idno'], 'de')  : '';
$decAddr  = (!empty($res['mb_add']))  ? _fn_secure_crypto($res['mb_add'], 'de')  : '';

// 代表圖像設定
if (!empty($mbID) && file_exists("../uploadFiles/mb/mbphoto_" . $mbID . "_.jpg")) {
  $cropImgDisplay = 'show';
  $mainimgpath    = '../uploadFiles/mb/mbphoto_' . $mbID . '_.jpg?' . time();
  $mainimgchk = 'mbphoto_'.$mbID.'_.jpg';
} else {
  $cropImgDisplay = 'hide';
  $mainimgpath    = '../imageFiles/nopic_user_200x200.jpg';
  $mainimgchk = "";
}

// 統計數據與關聯清單（僅編輯既有會員時查詢）
$totalSpent   = 0;
$enrollCount  = 0;
$ptsLogs      = array();
$enrollRows   = array();
$orderRows    = array();
$giftRows     = array();

if ($mbID > 0) {
  $totalSpent  = (float)DB->single("SELECT COALESCE(SUM(ord_total), 0) FROM fsl_orders WHERE mb_id = ? AND ord_status IN (2, 3, 4)", array($mbID));
  $enrollCount = (int)DB->single("SELECT COUNT(enr_id) FROM fsl_course_enrolls WHERE mb_id = ? AND enr_status = 1", array($mbID));

  $ptsLogs = DB->query("SELECT * FROM fsl_points WHERE mb_id = ? ORDER BY pt_id DESC LIMIT 100", array($mbID));
  if (!is_array($ptsLogs)) $ptsLogs = array();

  $enrollRows = DB->query("
    SELECT e.*, c.course_title, s.sess_batch, s.sess_startdate, s.sess_starttime
    FROM fsl_course_enrolls e
    LEFT JOIN fsl_courses c ON e.course_id = c.course_id
    LEFT JOIN fsl_course_sessions s ON e.sess_id = s.sess_id
    WHERE e.mb_id = ?
    ORDER BY e.enr_id DESC
  ", array($mbID));
  if (!is_array($enrollRows)) $enrollRows = array();

  $orderRows = DB->query("
    SELECT o.*, i.inv_no, i.inv_type, i.inv_carrier_type, i.inv_carrier_code, i.inv_tax_id
    FROM fsl_orders o
    LEFT JOIN fsl_invoices i ON o.ord_id = i.ord_id
    WHERE o.mb_id = ?
    ORDER BY o.ord_id DESC
  ", array($mbID));
  if (!is_array($orderRows)) $orderRows = array();

  $giftRows = DB->query("
    SELECT r.*, g.gift_title, g.gift_type, g.gift_discount_usd
    FROM fsl_gift_records r
    LEFT JOIN fsl_gifts g ON r.gift_id = g.gift_id
    WHERE r.mb_id = ?
    ORDER BY r.grec_id DESC
  ", array($mbID));
  if (!is_array($giftRows)) $giftRows = array();
}

$levelLabels  = array(1 => '一般會員', 2 => '向光學員', 3 => 'VIP 會員');
$statusLabels = array(0 => '停權凍結', 1 => '正常啟用', 2 => '待信箱驗證');
//功能陣列建立，取自JSON檔案
$mbJS = _fn_getCachedJsonData("../jsonFiles/member.json");
$locJS = _fn_getCachedJsonData("../jsonFiles/locations.json");
?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
  <title>Fifth Season Life 人生第五季 | 會員資料管理</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <!-- flatpickr -->
  <link rel="stylesheet" href="../pluginFiles/flatpickr/flatpickr.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../pluginFiles/dropzone/dropzone.css" />
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.lightbox.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">
</head>
<body class="bg-body-tertiary theme-blue">
  <div class="text-muted text-end fs-7 position-absolute top-0 end-0 pe-4 pt-1 ms-2"><i class="bi bi-file-earmark-code"></i> <?php echo basename(__FILE__); ?></div>
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                <?php echo !empty($mbID) ? "編輯" : "新增"; ?>會員資料
                <span class="fs-7">【ID: <?php echo $mbID; ?>】</span>
              </h4>
              <h6 class="m-0 fw-light text-muted fs-7 mt-1 mb-n2"><i class="bi bi-arrow-return-right"></i> 會員帳號身份、點數調帳、修課與訂單全覽</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">會員管理系統</li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回列表">會員資料列表</li>
                <li class="breadcrumb-item active"><?php echo !empty($mbID) ? "會員檔案詳情" : "新增會員"; ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <!-- 左側個人資訊卡片 -->
            <div class="col-md-3 mb-3">
              <div class="card card-primary card-outline shadow-lg">
                <div class="card-body box-profile">
                  <div class="text-center">
                    <div class="image_area">
                      <i class="nav-icon bi bi-trash3 fs-4 cropImgTrash <?php echo $cropImgDisplay; ?>" title="刪除圖像"></i>
                      <form method="post">
                        <label for="cropUploadImage">
                          <img id="avatarImage" name="avatarImage" class="profile-user-img img-fluid rounded-circle imgEDicon" src="<?php echo $mainimgpath; ?>" width="200" alt="會員頭像" title="會員頭像"/>
                          <div class="overlay">
                            <div class="overtext"><?php echo !empty($mbID) ? "更改" : "新增"; ?>會員頭像</div>
                          </div>
                          <input type="file" name="image" class="image d-none" id="cropUploadImage" crop-width="400" crop-height="400">
                        </label>
                      </form>
                      <?php include('include_avatarmodal.php'); ?>
                    </div>
                  </div>
                  <h3 class="profile-username text-center mt-2" id="sideMbName"><?php echo !empty($res['mb_name']) ? _fn_secure_crypto(htmlspecialchars($res['mb_name']),'de') : ($mbID > 0 ? '未填寫姓名' : '新建立會員'); ?></h3>
                  <p class="text-warning text-center small mb-3" id="sideMbLevel"><?php echo $mbJS['levels'][$res['mb_level']]['title'] ?? '一般會員'; ?></p>

                  <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>帳號狀態</b>
                      <span id="sideMbStatus" class="<?php echo ((int)($res['mb_status'] ?? 1) === 1) ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $mbJS[(int)($res['mb_status'] ?? 1)] ?? '正常啟用'; ?>
                      </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>可用 PTS 點數</b>
                      <span class="fw-bold text-warning" id="sideMbPoints"><?php echo number_format((int)($res['mb_points'] ?? 0)); ?> PTS</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>累計有效消費</b>
                      <span class="text-info fw-bold">NT$ <?php echo number_format($totalSpent); ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>已報名課程數</b>
                      <span><?php echo $enrollCount; ?> 堂</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>最後更新</b>
                      <span class="text-dark" id="sideMbUpdated"><?php echo $res['mb_updated'] ?? '-'; ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>註冊時間</b>
                      <span class="text-dark"><?php echo $res['mb_published'] ?? '-'; ?></span>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="card card-success shadow-lg mt-4">
                <div class="card-header">
                    <h3 class="card-title">會員受評</h3>
                </div>
                <div class="card-body py-2">

                </div>
              </div>
            </div>
            <!-- 右側多分頁管理區 -->
            <div class="col-md-9">
              <div class="card shadow-lg modern-card-tabs">
                <div class="card-header p-2">
                  <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                      <a class="nav-link active" href="#tabMenu1" data-index="1" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-person-vcard me-1"></i>基本資料與發票載具
                      </a>
                    </li>
                    <?php if ($mbID > 0): ?>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu2" data-index="2" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-coin me-1"></i>PTS 點數調帳 (<?php echo number_format((int)($res['mb_points'] ?? 0)); ?>)
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu3" data-index="3" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-journal-bookmark me-1"></i>修課紀錄 (<?php echo count($enrollRows); ?>)
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu4" data-index="4" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-bag-check me-1"></i>訂單與優惠券 (<?php echo count($orderRows); ?>)
                      </a>
                    </li>
                    <?php endif; ?>
                  </ul>
                </div>

                <div class="card-body bg-opacity-50">
                  <div class="tab-content">
                    <!-- Tab 1: 基本資料、等級權限與預設發票載具 -->
                    <div class="tab-pane fade show active" id="tabMenu1" role="tabpanel">
                      <div class="small text-secondary">
                        <i class="bi bi-exclamation-circle me-1"></i>被授權的管理者可以新增或更改會員資料，唯「會員帳號(電郵)」必須具有最高管理權限的管理者始得編輯，由工作人員新增資料之後的會員，仍需要經過郵箱驗證才可以行使會員權益。
                      </div>
                      <hr>
                      <form id="form_tabMenu1" action="../funcFiles/mb_act_func.php" method="post" data-func="func_mb_infoUpdate" novalidate>
                        <input type="hidden" name="mainimgchk" id="mainimgchk" class="notSubmit" value="<?php echo $mainimgchk; ?>">
                        <div class="row g-3">
                          <div class="col-md-6">
                            <label class="form-label small fw-bold">登入帳號 (Email)(加密儲存) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <!-- 💡 換成平面簡約圖標 -->
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control<?php echo ($mbID > 0) ? ' notSubmit' : ''; ?>" id="mb_email" name="mb_email"
                                      value="<?php echo htmlspecialchars(_fn_secure_crypto($res['mb_email'] ?? '','de')); ?>"
                                      <?php echo ($mbID > 0) ? 'readonly' : 'required'; ?> placeholder="member@example.com">
                                <span class="input-group-text b-0 p-0">
                                  <input type="checkbox" class="btn-check" id="mb_email_verified" name="mb_email_verified" data-unchecked="0" value="1" autocomplete="off"<?php echo !empty($res['mb_email_verified']) ? ' checked' : ''; ?>/>
                                  <label class="btn btn-outline-primary btn-toggle-status" for="mb_email_verified" data-on="已驗證" data-off="未驗證"></label>
                                </span>
                                
                            </div>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label small fw-bold">
                              <?php echo ($mbID > 0) ? '重設登入密碼 (不修改請留空)' : '初始登入密碼 *'; ?>
                            </label>
                            <div class="input-group">
                                <!-- 💡 換成平面簡約圖標 -->
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" autocomplete="new-password" id="mb_password" name="mb_password"
                                   <?php echo ($mbID === 0) ? 'required minlength="6"' : ''; ?> placeholder="至少 6 碼英數字">
                                <!-- 💡 整合實用功能：明隱切換眼球 (平面化) -->
                                <span class="input-group-text password-toggle text-muted" id="togglePasswordBtn"><i class="bi bi-eye"></i></span>
                            </div>
                            
                          </div>

                          <div class="col-12"><div class="hr-more">基本資料</div><hr class="my-1"></div>
                          
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">真實姓名(加密儲存) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mb_name" name="mb_name" required
                                   value="<?php echo htmlspecialchars(_fn_secure_crypto($res['mb_name'] ?? '','de')); ?>">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">顯示暱稱</label>
                            <input type="text" class="form-control" id="mb_title" name="mb_title"
                                   value="<?php echo htmlspecialchars($res['mb_title'] ?? ''); ?>">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">性別</label>
                            <select class="form-select" id="mb_gender" name="mb_gender">
                            <?php
                                foreach($mbJS['gender'] as $key => $value){
                                    echo '<option value="'.$key.'"';
                                    if($key==""){ echo " disabled"; }
                                    if(($res['mb_gender'] ?? null) == $key) echo " selected";
                                    echo '>'.$value.'</option>';
                                }
                            ?>
                            </select>
                          </div>

                          <div class="col-md-4">
                            <label class="form-label small fw-bold">出生年月日</label>
                            <input type="date" class="form-control" id="mb_birth" name="mb_birth"
                                   value="<?php echo htmlspecialchars($res['mb_birth'] ?? ''); ?>" placeholder="yyyy-mm-dd">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">聯絡手機 (加密儲存)</label>
                            <div class="input-group">
                              <input type="text" class="form-control" id="mb_cellphone" name="mb_cellphone"
                                    value="<?php echo htmlspecialchars($decPhone); ?>" placeholder="09xxxxxxxx">
                              <span class="input-group-text p-0">
                                <input type="checkbox" class="btn-check" id="mb_cellphone_verified" name="mb_cellphone_verified" data-unchecked="0" value="1" autocomplete="off"<?php echo !empty($res['mb_cellphone_verified']) ? ' checked' : ''; ?>/>
                                <label class="btn btn-outline-primary btn-toggle-status" for="mb_cellphone_verified" data-on="已驗證" data-off="未驗證"></label>
                              </span>
                           </div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">身分證字號 (加密儲存)</label>
                            <input type="text" class="form-control text-uppercase notSubmit" id="mb_idno" name="mb_idno"
                                   value="<?php //echo htmlspecialchars($decIdno); ?>" placeholder="A123456789">
                          </div>

                          <div class="col-md-4">
                            <label class="form-label small fw-bold">居住城市</label>
                            <select class="form-control" id="mb_city" name="mb_city" placeholder="通訊地址" data-placeholder="-請選擇居住城市-" data-select="city">
                            <option value="">-請選擇居住城市-</option>
                            <?php
                            foreach($locJS as $key => $value) {
                              echo "<option value='".$value['value']."'";
                              if(!empty($res['mb_city']) && $res['mb_city']==$value['value']) { echo " selected"; }
                              echo ">".$value['text']."</option>";
                            }
                            ?>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">行政區劃</label>
                            <select class="form-control" id="mb_district" name="mb_district" placeholder="行政區劃" data-placeholder="-請選擇行政區劃-" dist-fromcity="mb_city" style="width: 100%;">
                            <?php
                            if(!empty($res['mb_city'])) {
                              echo "<option value='0'>-請選擇行政劃區-</option>";
                              foreach($locJS[$res['mb_city']]['district'] as $key => $value){
                                echo "<option value='".$key."'";
                                if(!empty($res['mb_district']) && $res['mb_district']==$key) { echo " selected"; }
                                echo ">".$value."</option>";
                              }
                            }
                            ?>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">通訊 / 宅配地址 (加密儲存)</label>
                            <input type="text" class="form-control" id="mb_add" name="mb_add"
                                   value="<?php echo htmlspecialchars($decAddr); ?>">
                          </div>

                          <div class="col-12"><div class="hr-more">帳戶訊息</div><hr class="my-1"></div>

                          <!-- 會員等級、狀態與驗證設定 -->
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">會員分類</label>
                            <select class="form-select" id="mb_type" name="mb_type">
                            <?php
                                foreach($mbJS['mbtype'] as $key => $value){
                                    echo '<option value="'.$key.'"';
                                    if($key==""){ echo " disabled"; }
                                    if(($res['mb_type'] ?? null) == $key) echo " selected";
                                    echo '>'.$value.'</option>';
                                }
                            ?>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">會員等級</label>
                            <select class="form-select" id="mb_level" name="mb_level">
                            <?php
                                foreach($mbJS['levels'] as $key => $value){
                                    echo '<option value="'.$key.'"';
                                    if($key==""){ echo " disabled"; }
                                    if(($res['mb_level'] ?? null) == $key) echo " selected";
                                    echo '>'.$value['title'].'</option>';
                                }
                            ?>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">帳號狀態</label>
                            <select class="form-select" id="mb_status" name="mb_status">
                            <?php
                                foreach($mbJS['status'] as $key => $value){
                                    echo '<option value="'.$key.'"';
                                    if($key==""){ echo " disabled"; }
                                    if(($res['mb_status'] ?? null) == $key) echo " selected";
                                    echo '>'.$value.'</option>';
                                }
                            ?>
                            </select>
                          </div>

                          <div class="col-12"><div class="hr-more">交易參數</div><hr class="my-1"></div>

                          <!-- 常用電子發票載具與公司統編偏好 -->
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">預設發票類型</label>
                            <select class="form-select" id="mb_inv_type" name="mb_inv_type">
                            <?php
                                foreach($mbJS['invtype'] as $key => $value){
                                    echo '<option value="'.$key.'"';
                                    if($key==""){ echo " disabled"; }
                                    if(($res['mb_inv_type'] ?? null) == $key) echo " selected";
                                    echo '>'.$value.'</option>';
                                }
                            ?>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">預設載具方式</label>
                            <select class="form-select" id="mb_carrier_type" name="mb_carrier_type">
                            <?php
                                foreach($mbJS['carrier'] as $key => $value){
                                    echo '<option value="'.$key.'"';
                                    if($key==""){ echo " disabled"; }
                                    if(($res['mb_carrier_type'] ?? null) == $key) echo " selected";
                                    echo '>'.$value.'</option>';
                                }
                            ?>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">預設載具條碼 (如 /AB12345)</label>
                            <input type="text" class="form-control text-uppercase" id="mb_carrier_code" name="mb_carrier_code"
                                   value="<?php echo htmlspecialchars($res['mb_carrier_code'] ?? ''); ?>">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">預設公司統一編號</label>
                            <input type="text" class="form-control" id="mb_tax_id" name="mb_tax_id" maxlength="8"
                                   value="<?php echo htmlspecialchars($res['mb_tax_id'] ?? ''); ?>">
                          </div>
                          <div class="col-md-8">
                            <label class="form-label small fw-bold">預設公司發票抬頭</label>
                            <input type="text" class="form-control" id="mb_company_title" name="mb_company_title"
                                   value="<?php echo htmlspecialchars($res['mb_company_title'] ?? ''); ?>">
                          </div>

                          <div class="col-12"><div class="hr-more">管理相關</div><hr class="my-1"></div>
                          
                          <div class="col-md-12">
                            <label class="form-label small fw-bold">管理者註記</label>
                            <textarea class="form-control" id="mb_remark" name="mb_remark" col="4"><?php echo htmlspecialchars($res['mb_remark'] ?? ''); ?></textarea>
                          </div>
                        </div>
                      </form>
                    </div>

                    <!-- Tab 2: PTS 紅利點數手動調帳與流水帳 -->
                    <div class="tab-pane fade" id="tabMenu2" role="tabpanel">
                      <form id="form_adjust_pts" class="row g-2 align-items-end p-3 bg-light border rounded-3 mb-4">
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">異動方向</label>
                          <select class="form-select" id="adjPtsDir">
                            <option value="add">補發增加 (+)</option>
                            <option value="sub">手動扣除 (-)</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">異動點數 (PTS)</label>
                          <input type="number" class="form-control" id="adjPtsVal" min="1" max="100000" placeholder="輸入正整數" required>
                        </div>
                        <div class="col-md-4">
                          <label class="form-label small fw-bold">異動原因備註 <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="adjPtsMemo" placeholder="如：活動專案加贈 / 客服補償" required>
                        </div>
                        <div class="col-md-2 d-grid">
                          <button type="submit" class="btn btn-warning btn-sm fw-bold"><i class="bi bi-plus-slash-minus me-1"></i>執行調帳</button>
                        </div>
                      </form>

                      <div class="table-responsive">
                        <table id="dataTable_mbpts" class="table table-sm table-bordered table-hover align-middle w-100 small">
                          <thead class="table-light">
                            <tr>
                              <th>ID</th>
                              <th>時間</th>
                              <th class="text-end">異動點數</th>
                              <th class="text-end">異動後餘額</th>
                              <th>摘要說明</th>
                            </tr>
                          </thead>
                          <tbody id="ptsLogTbody">
                            <?php foreach ($ptsLogs as $pl):
                              $delta = (int)$pl['pt_trade'];
                            ?>
                            <tr>
                              <td><?php echo (int)$pl['pt_id']; ?></td>
                              <td class="text-muted"><?php echo htmlspecialchars($pl['pt_date'] ?? ''); ?></td>
                              <td class="text-end fw-bold <?php echo ($delta >= 0) ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($delta >= 0 ? '+' : '') . number_format($delta); ?>
                              </td>
                              <td class="text-end fw-semibold"><?php echo number_format((int)$pl['pt_available']); ?></td>
                              <td><?php echo htmlspecialchars($pl['pt_memo'] ?? ''); ?></td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <!-- Tab 3: 課程報名與上課進度紀錄 -->
                    <div class="tab-pane fade" id="tabMenu3" role="tabpanel">
                      <div class="table-responsive">
                        <table id="dataTable_mbenroll" class="table table-sm table-bordered table-hover align-middle w-100 small">
                          <thead class="table-light">
                            <tr>
                              <th>報名ID</th>
                              <th>課程名稱</th>
                              <th>梯次與開課時間</th>
                              <th class="text-center">付款狀態</th>
                              <th class="text-center">上課進度</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($enrollRows as $er):
                              $ePay = (int)($er['enr_pay_status'] ?? 0);
                              $eSta = (int)($er['enr_status'] ?? 1);
                            ?>
                            <tr>
                              <td><?php echo (int)$er['enr_id']; ?></td>
                              <td class="fw-bold"><?php echo htmlspecialchars($er['course_title'] ?? '課程已下架'); ?></td>
                              <td>
                                <?php if (!empty($er['sess_startdate'])): ?>
                                  第 <?php echo (int)$er['sess_batch']; ?> 梯 • <?php echo $er['sess_startdate'] . ' ' . substr($er['sess_starttime'], 0, 5); ?>
                                <?php else: ?>
                                  <span class="text-muted">隨報隨上 / 未指定梯次</span>
                                <?php endif; ?>
                              </td>
                              <td class="text-center">
                                <?php echo ($ePay === 2) ? '<span class="badge text-bg-success">已結清</span>' : '<span class="badge text-bg-warning">待付款</span>'; ?>
                              </td>
                              <td class="text-center">
                                <?php
                                  if ($eSta === 1) echo '<span class="badge text-bg-primary">進行中/已預約</span>';
                                  elseif ($eSta === 2) echo '<span class="badge text-bg-success">已結業完課</span>';
                                  else echo '<span class="badge text-bg-secondary">已取消</span>';
                                ?>
                              </td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <!-- Tab 4: 歷史訂單與持有優惠券 -->
                    <div class="tab-pane fade" id="tabMenu4" role="tabpanel">
                      <h6 class="fw-bold mb-2"><i class="bi bi-receipt me-1 text-primary"></i>消費訂單與發票載具紀錄</h6>
                      <div class="table-responsive mb-4">
                        <table id="dataTable_mborders" class="table table-sm table-bordered table-hover align-middle w-100 small">
                          <thead class="table-light">
                            <tr>
                              <th>訂單編號</th>
                              <th class="text-end">實付金額</th>
                              <th>發票號碼與載具</th>
                              <th class="text-center">訂單狀態</th>
                              <th>下單時間</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            $ordStaMap = array(1 => '待付款', 2 => '已付款處理中', 3 => '已出貨', 4 => '已完成', 5 => '退費申請中', 6 => '已退費/取消');
                            foreach ($orderRows as $or):
                              $ost = (int)$or['ord_status'];
                              $invInfo = !empty($or['inv_no']) ? $or['inv_no'] : '尚未開立';
                              if ((int)($or['inv_type'] ?? 1) === 2) {
                                $invInfo .= ' (統編:' . htmlspecialchars($or['inv_tax_id'] ?? '') . ')';
                              } elseif ((int)($or['inv_carrier_type'] ?? 0) === 1) {
                                $invInfo .= ' (手機:' . htmlspecialchars($or['inv_carrier_code'] ?? '') . ')';
                              } elseif ((int)($or['inv_carrier_type'] ?? 0) === 2) {
                                $invInfo .= ' (憑證:' . htmlspecialchars($or['inv_carrier_code'] ?? '') . ')';
                              }
                            ?>
                            <tr>
                              <td class="fw-bold text-primary"><?php echo htmlspecialchars($or['ord_no']); ?></td>
                              <td class="text-end fw-bold">NT$ <?php echo number_format((float)$or['ord_total']); ?></td>
                              <td><?php echo $invInfo; ?></td>
                              <td class="text-center"><span class="badge text-bg-secondary"><?php echo $ordStaMap[$ost] ?? '未知'; ?></span></td>
                              <td class="text-muted"><?php echo $or['ord_created'] ?? '-'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>

                      <h6 class="fw-bold mb-2"><i class="bi bi-ticket-perforated me-1 text-warning"></i>禮品與折價券持有紀錄</h6>
                      <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle w-100 small">
                          <thead class="table-light">
                            <tr>
                              <th>紀錄ID</th>
                              <th>禮品 / 優惠券名稱</th>
                              <th class="text-center">狀態</th>
                              <th>領取時間</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if (empty($giftRows)): ?>
                              <tr><td colspan="4" class="text-center text-muted py-3">尚無禮品或優惠券領取紀錄</td></tr>
                            <?php else: foreach ($giftRows as $gr):
                              $gst = (int)$gr['grec_status'];
                            ?>
                              <tr>
                                <td><?php echo (int)$gr['grec_id']; ?></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($gr['gift_title'] ?? ''); ?></td>
                                <td class="text-center">
                                  <?php
                                    if ($gst === 1) echo '<span class="badge text-bg-success">未使用</span>';
                                    elseif ($gst === 2 || $gst === 3) echo '<span class="badge text-bg-secondary">已使用</span>';
                                    else echo '<span class="badge text-bg-danger">已過期</span>';
                                  ?>
                                </td>
                                <td class="text-muted"><?php echo $gr['grec_created'] ?? '-'; ?></td>
                              </tr>
                            <?php endforeach; endif; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <script src="../pluginFiles/datatables3/datatables.min.js"></script>
  <script src="../pluginFiles/flatpickr/flatpickr.min.js"></script>
  <script src="../pluginFiles/flatpickr/location/zh_tw.js"></script>
  <script src="../pluginFiles/dropzone/dropzone.js"></script>
  <script src="../pluginFiles/cropper/cropper.js"></script>
  <script src="../scriptFiles/adminlte.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/fileupload.js<?php echo '?t='.time(); ?>"></script>
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

  <script type="text/javascript">
  function validateBS5Form($form) {
    if (!$form || $form.length === 0) return true;
    const isValid = $form[0].checkValidity();
    $form[0].classList.add('was-validated');
    return isValid;
  }

  function doReDirect() {
    window.history.back();
  }

  function doButSave() {
    var frm = $('#form_' + $('.tab-pane.active').prop("id"));
    if (!frm.length) return false;
    if (!validateBS5Form(frm)) {
      window.parent.toastr['warning']('請檢查標示紅框的必填或格式錯誤欄位！');
      return false;
    }
    ajaxPOST(frm);
  }

  function doButDelete() {
    if (0 == parseInt('<?php echo $mbID; ?>')) return false;
    $.ajax({
      type: 'post',
      url: '../funcFiles/mb_act_func.php',
      data: {
        'arrDatA': {},
        'formAcce': 'func_mb_delete',
        'token': getTokenVal(),
        'mbID': '<?php echo $mbID; ?>'
      },
      dataType: 'json',
      success: function(data) {
        if (data['RTN'] == 1) {
          window.parent.toastr['success'](data['msgStr'] || '會員資料已停權封存');
          window.location.replace("mb_list.php");
        } else {
          window.parent.toastr['error'](data['msgStr'] || '刪除失敗');
        }
      }
    });
  }

  function doButReset() {
    location.reload(true);
  }

  function ajaxPOST(frm) {
    var $frm = frm || $('#form_tabMenu1');
    var accE = (0 == parseInt('<?php echo $mbID; ?>')) ? 'func_mb_addnew' : $frm.data('func');
    if($('#mb_password').val() !== ''){
      $('#mb_password').removeClass('notSubmit');
    } else {
      $('#mb_password').addClass('notSubmit');
    }
    //var datA = $frm.find(":input").not(".notSubmit").serializeArray();
    var datA = getFormObjects($frm);
    var imgBase64 = (typeof cropImage64 !== 'undefined' && cropImage64 !== '')
                    ? cropImage64
                    : ($('#main_crop_area').data('cropImage64') || '');

    $.ajax({
      type: 'post',
      url: '../funcFiles/mb_act_func.php',
      data: {
        'arrDatA': datA,
        'formAcce': accE,
        'imgUp': imgBase64,
        'token': getTokenVal(),
        'mbID': '<?php echo $mbID; ?>'
      },
      dataType: 'json',
      success: function(data) {
        console.log(data);
        if (accE == 'func_mb_addnew' && data['RTN'] == 1) {
          window.parent.toastr['success'](data['msgStr']);
          window.location.replace("mb_detail.php?currID=" + data['newID']);
          return false;
        }
        if (window.parent && window.parent.toastr) {
          window.parent.toastr[data['msgType']](data['msgStr']);
        }
        if (data['RTN'] == 1) {
          $frm.removeClass('was-validated');
          $('#sideMbName').text($('#mb_name').val() || '未填寫姓名');
          $('#sideMbLevel').text($('#mb_level option:selected').text().replace(/^\d+\s*-\s*/, ''));
          const stVal = $('#mb_status').val();
          $('#sideMbStatus')
            .text($('#mb_status option:selected').text().replace(/^\d+\s*-\s*/, ''))
            .toggleClass('text-success', stVal === '1')
            .toggleClass('text-danger', stVal !== '1');
          if (data['upTime']) $('#sideMbUpdated').text(data['upTime']);
          $('#mb_password').val('');
          cropImage64 = '';
          if (typeof doSerialize === 'function') doSerialize();
        }
      },
      error: function(err) {
        if (window.parent && window.parent.toastr) {
          window.parent.toastr['error']('系統讀寫錯誤，請聯絡管理人員！');
        }
      }
    });
  }

  $(document).ready(function() {
    ////////////////////////////////////////////////////////
    if (typeof flatpickr !== 'undefined') {
            flatpickr($('input[type="date"]'), {
                locale: 'zh_tw',
                dateFormat: 'Y-m-d',   // 嚴格統一為 yyyy-mm-dd
                allowInput: true,      // 允許直接用鍵盤輸入 2026-11-17
                disableMobile: true    // 手機與平板也強制維持 yyyy-mm-dd 格式，不被系統原生輪盤覆蓋
            });
    }

    if ($('#dataTable_mbpts').length)    datatableCreate('dataTable_mbpts');
    if ($('#dataTable_mbenroll').length) datatableCreate('dataTable_mbenroll');
    if ($('#dataTable_mborders').length) datatableCreate('dataTable_mborders');

    // 手動調帳 PTS 點數表單送出
    $('#form_adjust_pts').on('submit', function(e) {
      e.preventDefault();
      const dir  = $('#adjPtsDir').val();
      const val  = parseInt($('#adjPtsVal').val(), 10) || 0;
      const memo = $('#adjPtsMemo').val().trim();
      if (val <= 0 || memo === '') {
        window.parent.toastr['warning']('請輸入大於 0 的點數與異動原因！');
        return;
      }
      $.post('../funcFiles/mb_act_func.php', {
        'formAcce': 'func_mb_adjustPts',
        'token': getTokenVal(),
        'mbID': '<?php echo $mbID; ?>',
        'adjDir': dir,
        'adjVal': val,
        'adjMemo': memo
      }, function(data) {
        console.log(data);
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) {
          $('#sideMbPoints').text(Number(data['newPoints']).toLocaleString() + ' PTS');
          $('#adjPtsVal').val('');
          $('#adjPtsMemo').val('');
          const sign = data['delta'] >= 0 ? '+' : '';
          const cls  = data['delta'] >= 0 ? 'text-success' : 'text-danger';
          $('#ptsLogTbody').prepend(`
            <tr>
              <td>${data['ptId']}</td>
              <td class="text-muted">${data['createdAt']}</td>
              <td class="text-end fw-bold ${cls}">${sign}${Number(data['delta']).toLocaleString()}</td>
              <td class="text-end fw-semibold">${Number(data['newPoints']).toLocaleString()}</td>
              <td>${$('<div>').text(memo).html()}</td>
            </tr>
          `);
        }
      }, 'json');
    });

    $('li.prevLink').on('click', function() {
      window.location.replace(getCurrentURI('', 'mb_list.php'));
    });

    $('#togglePasswordBtn').on('click', function() {
        const passwordInput = document.getElementById('mb_password');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            $(this).text('🙈');
        } else {
            passwordInput.type = 'password';
            $(this).text('👁️');
        }
    });

    $('body').on('keypress click', function() {
      let timerElm = window.parent.$('#timer_on_exhausted');
      if (timerElm.length) timerElm.backward_timer('reset');
    });

    if (window.parent && typeof window.parent.treeviewCheck === 'function') {
      window.parent.treeviewCheck('<?php echo PAGE_ITEM; ?>', '<?php echo PAGE_MARK; ?>', '');
    }
  });
  </script>
</body>
</html>
