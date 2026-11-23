<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 定義本頁面代碼，用於 SideBar 開合與頂部操作權限連動
define('PAGE_MARK', 'PG_COURSE');
define('PAGE_ITEM', 'ITEM_COURSE');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$csID = currID;
$res  = ($csID > 0) ? DB->row("SELECT * FROM fsl_courses WHERE course_id = ? AND course_status != 9", array($csID)) : array();
if (!is_array($res)) $res = array();

$parentID = (int)($res['parent_id'] ?? 0);
$masterID = ($parentID > 0) ? $parentID : $csID;

// 主課程下拉選單（供特惠子方案選擇關聯主課程）
$masterCourses = DB->query("SELECT course_id, course_title FROM fsl_courses WHERE parent_id = 0 AND course_status != 9 AND course_id != ? ORDER BY course_sort ASC, course_id ASC", array($csID));
if (!is_array($masterCourses)) $masterCourses = array();

// 課程封面圖片設定（支援子方案繼承主課程封面預覽）
if (!empty($csID) && file_exists("../uploadFiles/cs/cimg_" . $csID . "_.jpg")) {
  $cropImgDisplay = 'show';
  $mainimgpath    = '../uploadFiles/cs/cimg_' . $csID . '_.jpg?' . time();
  $mainimgchk     = 'cimg_' . $csID . '_.jpg';
} elseif ($parentID > 0 && file_exists("../uploadFiles/cs/cimg_" . $parentID . "_.jpg")) {
  $cropImgDisplay = 'hide';
  $mainimgpath    = '../uploadFiles/cs/cimg_' . $parentID . '_.jpg?' . time();
  $mainimgchk     = '';
} else {
  $cropImgDisplay = 'hide';
  $mainimgpath    = '../imageFiles/nopic_400x300.jpg';
  $mainimgchk     = '';
}

// 查詢關聯資料：開課梯次、參與學員名單、資格條件規則
$sessionRows = array();
$enrollRows  = array();
$ruleRows    = array();

if ($csID > 0) {
  // 梯次與參與學員一律依 masterID 查詢（確保主課程與特惠子方案共用）
  $sessionRows = DB->query("
    SELECT * FROM fsl_course_sessions
    WHERE course_id = ? AND sess_status != 9
    ORDER BY sess_batch ASC, sess_startdate ASC, sess_starttime ASC
  ", array($masterID));
  if (!is_array($sessionRows)) $sessionRows = array();

  $enrollRows = DB->query("
    SELECT e.*, m.mb_name, m.mb_email, m.mb_cellphone,
           s.sess_batch, s.sess_startdate, s.sess_starttime,
           o.ord_no, o.ord_total
    FROM fsl_course_enrolls e
    LEFT JOIN fsl_members m ON e.mb_id = m.mb_id
    LEFT JOIN fsl_course_sessions s ON e.sess_id = s.sess_id
    LEFT JOIN fsl_orders o ON e.ord_id = o.ord_id
    WHERE e.course_id = ?
    ORDER BY e.enr_id DESC
  ", array($masterID));
  if (!is_array($enrollRows)) $enrollRows = array();

  // 資格條件規則依本方案 csID 查詢（例如限定女性特惠方案專屬條件）
  $ruleRows = DB->query("
    SELECT * FROM fsl_eligibility_rules
    WHERE erule_target_type = 1 AND erule_target_id = ?
    ORDER BY erule_sort ASC, erule_id ASC
  ", array($csID));
  if (!is_array($ruleRows)) $ruleRows = array();
}

$tierLabels = array(
  'plan-tier-basic'    => '基本階級 (Basic)',
  'plan-tier-standard' => '一般階級 (Standard)',
  'plan-tier-premium'  => '尊榮熱門 (Premium)',
  'plan-tier-special'  => '特殊特惠 (Special)'
);
$statusLabels = array(0 => '下架停售', 1 => '上架販售中', 2 => '草稿籌備中');
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
  <title>Fifth Season Life 人生第五季 | 課程與方案管理</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/datatables3/datatables.min.css">
  <link rel="stylesheet" href="../pluginFiles/flatpickr/flatpickr.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../pluginFiles/dropzone/dropzone.css" />
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.lightbox.css">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">

<style>
  /* 課程主圖改為滿寬自適應矩形（支援橫式與直式），覆蓋全站預設圓形頭像裁切 */
  .cs-cover-area.image_area {
    width: 100%;
  }
  .cs-cover-area.image_area label[for^="cropUploadImage"] {
    display: block !important;
    width: 100% !important;
    border-radius: 10px !important;
    overflow: hidden !important;
    border: 1px solid #dee2e6;
    background-color: #f8f9fa;
  }
  .cs-cover-area.image_area label[for^="cropUploadImage"] img.imgEDicon {
    width: 100% !important;
    height: auto !important;
    max-height: 360px;
    object-fit: cover;
    border-radius: 10px !important;
    display: block;
  }
  .cs-cover-area.image_area label[for^="cropUploadImage"] .overlay {
    border-radius: 0 0 10px 10px !important;
  }
  .cs-cover-area.image_area label[for^="cropUploadImage"]:hover .overlay {
    height: 44px !important;
  }
</style>
</head>
<body class="bg-body-tertiary theme-green">
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                <?php echo !empty($csID) ? "編輯" : "新增"; ?>課程與價格方案
                <span class="fs-5 text-success">【ID: <?php echo $csID; ?>】</span>
              </h4>
              <h6 class="m-0 fw-light text-muted">價格卡片設定、開課梯次排程、參與學員名單與優惠資格規則</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">課程管理系統</li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回列表">課程與方案列表</li>
                <li class="breadcrumb-item active"><?php echo !empty($csID) ? "課程詳情管理" : "新增課程方案"; ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <!-- 左側課程封面與狀態卡片 -->
            <div class="col-md-3 mb-3">
              <div class="card card-success card-outline shadow-lg">
                <div class="card-body box-profile">
                  <div class="text-center">
                    <div class="image_area cs-cover-area">
                      <i class="nav-icon bi bi-trash3 fs-4 cropImgTrash <?php echo $cropImgDisplay; ?>" title="刪除圖像"></i>
                      <form method="post">
                        <label for="cropUploadImage">
                          <img id="avatarImage" name="avatarImage" class="img-fluid imgEDicon" src="<?php echo $mainimgpath; ?>" alt="課程封面" title="課程封面"/>
                          <div class="overlay">
                            <div class="overtext"><?php echo !empty($csID) ? "更改" : "新增"; ?>課程封面</div>
                          </div>
                          <input type="file" name="image" class="image d-none" id="cropUploadImage" crop-width="800" crop-height="600">
                        </label>
                      </form>
                      <?php include('include_avatarmodal.php'); ?>
                    </div>
                  </div>
                  <h4 class="profile-username text-center mt-3 fs-5" id="sideCsTitle"><?php echo !empty($res['course_title']) ? htmlspecialchars($res['course_title']) : ($csID > 0 ? '未命名課程' : '新建立課程方案'); ?></h4>
                  <p class="text-success text-center small fw-bold mb-3" id="sideCsTier"><?php echo $tierLabels[$res['course_tier_class'] ?? 'plan-tier-standard'] ?? '一般階級'; ?></p>

                  <ul class="list-group list-group-flush mb-2 small">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>販售狀態</b>
                      <span id="sideCsStatus" class="<?php echo ((int)($res['course_status'] ?? 1) === 1) ? 'text-success fw-bold' : 'text-danger'; ?>">
                        <?php echo $statusLabels[(int)($res['course_status'] ?? 1)] ?? '上架販售中'; ?>
                      </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>方案售價</b>
                      <span class="fw-bold text-primary" id="sideCsCost">NT$ <?php echo number_format((float)($res['course_cost'] ?? 0)); ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>關聯歸屬</b>
                      <span><?php echo ($parentID > 0) ? "子方案 (#{$parentID})" : "獨立主課程"; ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>開課梯次數</b>
                      <span id="sideSessCnt"><?php echo count($sessionRows); ?> 梯次</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>有效報名學員</b>
                      <span><?php echo count($enrollRows); ?> 人次</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>最後更新</b>
                      <span class="text-dark" id="sideCsUpdated"><?php echo $res['course_updated'] ?? '-'; ?></span>
                    </li>
                  </ul>
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
                        <i class="bi bi-card-heading me-1"></i>課程與價格卡片設定
                      </a>
                    </li>
                    <?php if ($csID > 0): ?>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu2" data-index="2" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-calendar3 me-1"></i>開課梯次排程 (<?php echo count($sessionRows); ?>)
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu3" data-index="3" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-people me-1"></i>參與學員名單 (<?php echo count($enrollRows); ?>)
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu4" data-index="4" data-bs-toggle="tab" data-topbut="0" role="tab">
                        <i class="bi bi-shield-check me-1"></i>優惠資格規則 (<?php echo count($ruleRows); ?>)
                      </a>
                    </li>
                    <?php endif; ?>
                  </ul>
                </div>

                <div class="card-body bg-opacity-50">
                  <div class="tab-content">
                    <!-- Tab 1: 課程與價格卡片設定 -->
                    <div class="tab-pane fade show active" id="tabMenu1" role="tabpanel">
                      <form id="form_tabMenu1" action="../funcFiles/cs_act_func.php" method="post" data-func="func_cs_infoUpdate" novalidate>
                        <input type="hidden" name="mainimgchk" id="mainimgchk" class="notSubmit" value="<?php echo $mainimgchk; ?>">
                        <div class="row g-3">
                          <div class="col-md-6">
                            <label class="form-label small fw-bold">課程 / 價格卡片主標題 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="course_title" name="course_title" required
                                   value="<?php echo htmlspecialchars($res['course_title'] ?? ''); ?>" placeholder="例如：步步向光陪跑系統">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label small fw-bold">方案歸屬（主課程關聯）</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                              <option value="0" <?php echo ($parentID === 0) ? 'selected' : ''; ?>>0 - 獨立主課程（擁有獨立開課梯次與教材）</option>
                              <?php foreach ($masterCourses as $mc): ?>
                                <option value="<?php echo (int)$mc['course_id']; ?>" <?php echo ($parentID === (int)$mc['course_id']) ? 'selected' : ''; ?>>
                                  依附主課程 #<?php echo (int)$mc['course_id']; ?>：<?php echo htmlspecialchars($mc['course_title']); ?>（特惠子方案）
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>

                          <div class="col-12"><div class="hr-more">價格方案卡片視覺與行銷文案</div><hr class="my-1"></div>

                          <div class="col-md-4">
                            <label class="form-label small fw-bold">頂部徽章文字</label>
                            <input type="text" class="form-control" name="course_badge_text"
                                   value="<?php echo htmlspecialchars($res['course_badge_text'] ?? ''); ?>" placeholder="如：★ 熱門首選 / ✦ 女性專屬特惠">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">標題上方副標題</label>
                            <input type="text" class="form-control" name="course_subtitle"
                                   value="<?php echo htmlspecialchars($res['course_subtitle'] ?? ''); ?>" placeholder="如：16 週全方位人生重啟陪跑">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">標題下方補充小字</label>
                            <input type="text" class="form-control" name="course_subscript"
                                   value="<?php echo htmlspecialchars($res['course_subscript'] ?? ''); ?>" placeholder="如：四大秘密 × 12堂核心主題課程">
                          </div>

                          <div class="col-md-3">
                            <label class="form-label small fw-bold">方案售價 (NT$) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="course_cost" name="course_cost" min="0" step="1" required
                                   value="<?php echo (int)round((float)($res['course_cost'] ?? 0)); ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">價格計費單位</label>
                            <input type="text" class="form-control" name="course_price_unit"
                                   value="<?php echo htmlspecialchars($res['course_price_unit'] ?? ''); ?>" placeholder="如：單次 / 完整系列 / 限時特惠價">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">價格卡片階級樣式</label>
                            <select class="form-select" id="course_tier_class" name="course_tier_class">
                              <option value="plan-tier-basic" <?php echo (($res['course_tier_class'] ?? '') === 'plan-tier-basic') ? 'selected' : ''; ?>>基本 (plan-tier-basic)</option>
                              <option value="plan-tier-standard" <?php echo (($res['course_tier_class'] ?? 'plan-tier-standard') === 'plan-tier-standard') ? 'selected' : ''; ?>>一般 (plan-tier-standard)</option>
                              <option value="plan-tier-premium" <?php echo (($res['course_tier_class'] ?? '') === 'plan-tier-premium') ? 'selected' : ''; ?>>尊榮黑金 (plan-tier-premium)</option>
                              <option value="plan-tier-special" <?php echo (($res['course_tier_class'] ?? '') === 'plan-tier-special') ? 'selected' : ''; ?>>特殊特惠亮綠 (plan-tier-special)</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">輪播排序權重</label>
                            <input type="number" class="form-control" name="course_sort" min="0" max="9999"
                                   value="<?php echo (int)($res['course_sort'] ?? 100); ?>">
                          </div>

                          <div class="col-md-4">
                            <label class="form-label small fw-bold">顯示於首頁價格卡片</label>
                            <select class="form-select" name="course_show_pricing">
                              <option value="1" <?php echo ((int)($res['course_show_pricing'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 顯示於前台價格輪播</option>
                              <option value="0" <?php echo ((int)($res['course_show_pricing'] ?? 1) === 0) ? 'selected' : ''; ?>>0 - 隱藏（僅供專屬連結報名）</option>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">首頁輪播預設置中主打</label>
                            <select class="form-select" name="course_is_hot">
                              <option value="0" <?php echo ((int)($res['course_is_hot'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 一般位置</option>
                              <option value="1" <?php echo ((int)($res['course_is_hot'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 預設置中焦點 (hottest)</option>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">上下架狀態</label>
                            <select class="form-select" id="course_status" name="course_status">
                              <option value="1" <?php echo ((int)($res['course_status'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 上架販售中</option>
                              <option value="2" <?php echo ((int)($res['course_status'] ?? 1) === 2) ? 'selected' : ''; ?>>2 - 草稿籌備中</option>
                              <option value="0" <?php echo ((int)($res['course_status'] ?? 1) === 0) ? 'selected' : ''; ?>>0 - 下架停售</option>
                            </select>
                          </div>

                          <div class="col-12"><div class="hr-more">開課規格與簡介說明</div><hr class="my-1"></div>

                          <div class="col-md-3">
                            <label class="form-label small fw-bold">課程性質類型</label>
                            <select class="form-select" name="course_type">
                              <option value="1" <?php echo ((int)($res['course_type'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 系列陪跑 / 團體工作坊</option>
                              <option value="2" <?php echo ((int)($res['course_type'] ?? 1) === 2) ? 'selected' : ''; ?>>2 - 一對一深度顧問預約</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">總堂數</label>
                            <input type="number" class="form-control" name="course_lesson" min="1"
                                   value="<?php echo (int)($res['course_lesson'] ?? 1); ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">單堂時長 (分鐘)</label>
                            <input type="number" class="form-control" name="course_duration" min="15" step="15"
                                   value="<?php echo (int)($res['course_duration'] ?? 60); ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">單梯次名額上限</label>
                            <input type="number" class="form-control" name="course_max" min="1"
                                   value="<?php echo (int)($res['course_max'] ?? 20); ?>">
                          </div>

                          <div class="col-md-6">
                            <label class="form-label small fw-bold">預設開課日期</label>
                            <input type="date" class="form-control" name="course_startdate"
                                   value="<?php echo htmlspecialchars($res['course_startdate'] ?? date('Y-m-d')); ?>">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label small fw-bold">預設開課時間</label>
                            <input type="text" class="form-control" name="course_starttime"
                                   value="<?php echo htmlspecialchars($res['course_starttime'] ?? '14:00:00'); ?>" placeholder="14:00:00">
                          </div>

                          <div class="col-12">
                            <label class="form-label small fw-bold">價格卡片簡短描述 (course_brief) <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="course_brief" rows="3" required><?php echo htmlspecialchars($res['course_brief'] ?? ''); ?></textarea>
                          </div>
                        </div>
                      </form>
                    </div>
                    <!-- Tab 2: 開課梯次排程管理 (共用 masterID) -->
                    <div class="tab-pane fade" id="tabMenu2" role="tabpanel">
                      <?php if ($parentID > 0): ?>
                        <div class="alert alert-info py-2 small">
                          <i class="bi bi-info-circle-fill me-1"></i>本方案為特惠子方案，目前直接顯示並共用主課程 <strong>#<?php echo $masterID; ?></strong> 的開課梯次與名額。
                        </div>
                      <?php endif; ?>

                      <form id="form_add_session" class="row g-2 align-items-end p-3 bg-light border rounded-3 mb-4">
                        <div class="col-md-2">
                          <label class="form-label small fw-bold">梯次編號</label>
                          <input type="number" class="form-control form-control-sm" id="newSessBatch" min="1" value="<?php echo count($sessionRows) + 1; ?>" required>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">開課日期</label>
                          <input type="date" class="form-control form-control-sm" id="newSessDate" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small fw-bold">上課時間</label>
                          <input type="text" class="form-control form-control-sm" id="newSessTime" value="14:00:00" placeholder="14:00:00" required>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">會議室 / 上課地點備註</label>
                          <input type="text" class="form-control form-control-sm" id="newSessRoom" placeholder="Zoom 線上會議室 / 台北教室">
                        </div>
                        <div class="col-md-2 d-grid">
                          <button type="submit" class="btn btn-success btn-sm fw-bold"><i class="bi bi-plus-circle me-1"></i>新增梯次</button>
                        </div>
                      </form>

                      <div class="table-responsive">
                        <table id="dataTable_cssess" class="table table-sm table-bordered table-hover align-middle w-100 small">
                          <thead class="table-light">
                            <tr>
                              <th class="text-center">梯次</th>
                              <th>開課日期與時間</th>
                              <th>會議室 / 地點</th>
                              <th class="text-center">已報名 / 上限</th>
                              <th class="text-center">狀態</th>
                              <th class="text-center" style="width: 110px;">操作</th>
                            </tr>
                          </thead>
                          <tbody id="sessTbody">
                            <?php
                            $maxCap = (int)($res['course_max'] ?? 20);
                            foreach ($sessionRows as $sr):
                              $sid = (int)$sr['sess_id'];
                              $sst = (int)($sr['sess_status'] ?? 1);
                            ?>
                            <tr id="sessRow_<?php echo $sid; ?>">
                              <td class="text-center fw-bold">第 <?php echo (int)$sr['sess_batch']; ?> 梯</td>
                              <td><?php echo htmlspecialchars($sr['sess_startdate']) . ' ' . substr($sr['sess_starttime'], 0, 5); ?></td>
                              <td><?php echo htmlspecialchars($sr['sess_location'] ?? '線上視訊會議室'); ?></td>
                              <td class="text-center">
                                <span class="badge text-bg-primary"><?php echo (int)$sr['sess_enrollee']; ?> / <?php echo $maxCap; ?> 人</span>
                              </td>
                              <td class="text-center">
                                <?php echo ($sst === 1) ? '<span class="badge text-bg-success">開放報名</span>' : '<span class="badge text-bg-secondary">已額滿/關閉</span>'; ?>
                              </td>
                              <td class="text-center">
                                <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 btn-toggle-sess" data-sid="<?php echo $sid; ?>" data-curr="<?php echo $sst; ?>" title="切換開放/關閉">
                                  <i class="bi bi-arrow-repeat"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-xs py-0 px-2 btn-del-sess" data-sid="<?php echo $sid; ?>" title="刪除梯次">
                                  <i class="bi bi-trash3"></i>
                                </button>
                              </td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <!-- Tab 3: 參與學員名單與出席進度管理 -->
                    <div class="tab-pane fade" id="tabMenu3" role="tabpanel">
                      <div class="table-responsive">
                        <table id="dataTable_csenroll" class="table table-sm table-bordered table-hover align-middle w-100 small">
                          <thead class="table-light">
                            <tr>
                              <th>報名ID</th>
                              <th>學員姓名 / 信箱</th>
                              <th>聯絡手機</th>
                              <th>報名梯次</th>
                              <th>關聯訂單</th>
                              <th class="text-center">付款狀態</th>
                              <th class="text-center">修課狀態</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($enrollRows as $er):
                              $eid      = (int)$er['enr_id'];
                              $decName  = !empty($er['mb_name']) ? _fn_secure_crypto($er['mb_name'], 'de') : '未填寫';
                              $decMail  = !empty($er['mb_email']) ? _fn_secure_crypto($er['mb_email'], 'de') : '';
                              $decTel   = !empty($er['mb_cellphone']) ? _fn_secure_crypto($er['mb_cellphone'], 'de') : '-';
                              $ePay     = (int)($er['enr_pay_status'] ?? 0);
                              $eSta     = (int)($er['enr_status'] ?? 1);
                            ?>
                            <tr>
                              <td><?php echo $eid; ?></td>
                              <td>
                                <a href="mb_detail.php?currID=<?php echo (int)$er['mb_id']; ?>" class="fw-bold text-decoration-none"><?php echo htmlspecialchars($decName); ?></a>
                                <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($decMail); ?></div>
                              </td>
                              <td><?php echo htmlspecialchars($decTel); ?></td>
                              <td>
                                <?php if (!empty($er['sess_startdate'])): ?>
                                  第 <?php echo (int)$er['sess_batch']; ?> 梯 (<?php echo $er['sess_startdate']; ?>)
                                <?php else: ?>
                                  <span class="text-muted">不限梯次</span>
                                <?php endif; ?>
                              </td>
                              <td>
                                <?php if (!empty($er['ord_no'])): ?>
                                  <span class="text-primary fw-semibold"><?php echo htmlspecialchars($er['ord_no']); ?></span>
                                  <div class="text-muted" style="font-size: 0.75rem;">NT$ <?php echo number_format((float)$er['ord_total']); ?></div>
                                <?php else: ?>
                                  <span class="text-muted">手動匯入/免費</span>
                                <?php endif; ?>
                              </td>
                              <td class="text-center">
                                <?php echo ($ePay === 2) ? '<span class="badge text-bg-success">已結清</span>' : '<span class="badge text-bg-warning">待付款</span>'; ?>
                              </td>
                              <td class="text-center">
                                <select class="form-select form-select-sm sel-enroll-status" data-eid="<?php echo $eid; ?>" style="min-width: 110px;">
                                  <option value="1" <?php echo ($eSta === 1) ? 'selected' : ''; ?>>進行中/已預約</option>
                                  <option value="2" <?php echo ($eSta === 2) ? 'selected' : ''; ?>>已結業完課</option>
                                  <option value="0" <?php echo ($eSta === 0) ? 'selected' : ''; ?>>已取消退課</option>
                                </select>
                              </td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <!-- Tab 4: 特惠資格與購買條件規則 (fsl_eligibility_rules) -->
                    <div class="tab-pane fade" id="tabMenu4" role="tabpanel">
                      <form id="form_add_rule" class="row g-2 align-items-end p-3 bg-light border rounded-3 mb-4">
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">條件判斷類型</label>
                          <select class="form-select form-select-sm" id="ruleType">
                            <option value="gender">會員性別 (F / M)</option>
                            <option value="time_range">報名時間區間 (起,迄)</option>
                            <option value="birthday_month">當月壽星限定 (1)</option>
                            <option value="city">居住城市代碼 (如 01,02)</option>
                            <option value="mb_level">會員等級門檻 (1/2/3)</option>
                            <option value="total_spent">累計消費金額門檻</option>
                            <option value="id_last_digit">身分證尾數 (如 1,3,5,7,9)</option>
                          </select>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small fw-bold">比對運算子</label>
                          <select class="form-select form-select-sm" id="ruleOp">
                            <option value="=">= (等於)</option>
                            <option value="IN">IN (包含於清單)</option>
                            <option value="BETWEEN">BETWEEN (區間)</option>
                            <option value=">=">&gt;= (大於等於)</option>
                            <option value="<=">&lt;= (小於等於)</option>
                            <option value="!=">!= (不等於)</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">條件值 <span class="text-danger">*</span></label>
                          <input type="text" class="form-control form-control-sm" id="ruleVal" placeholder="如：F 或 2026-11-01 00:00:00,2026-12-31 23:59:59" required>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small fw-bold">不符條件提示警語 <span class="text-danger">*</span></label>
                          <input type="text" class="form-control form-control-sm" id="ruleMsg" placeholder="顯示給會員的提示訊息" required>
                        </div>
                        <div class="col-md-1 d-grid">
                          <button type="submit" class="btn btn-primary btn-sm fw-bold" title="新增規則"><i class="bi bi-plus-lg"></i></button>
                        </div>
                      </form>

                      <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle w-100 small">
                          <thead class="table-light">
                            <tr>
                              <th>ID</th>
                              <th>條件類型</th>
                              <th>運算子</th>
                              <th>比對值</th>
                              <th>不符資格提示警語</th>
                              <th class="text-center" style="width: 70px;">刪除</th>
                            </tr>
                          </thead>
                          <tbody id="ruleTbody">
                            <?php if (empty($ruleRows)): ?>
                              <tr id="emptyRuleRow"><td colspan="6" class="text-center text-muted py-3">尚未設定任何購買限制條件（所有會員皆可報名）</td></tr>
                            <?php else: foreach ($ruleRows as $rl):
                              $rid = (int)$rl['erule_id'];
                            ?>
                              <tr id="ruleRow_<?php echo $rid; ?>">
                                <td><?php echo $rid; ?></td>
                                <td class="fw-bold text-primary"><?php echo htmlspecialchars($rl['erule_type']); ?></td>
                                <td><code><?php echo htmlspecialchars($rl['erule_operator']); ?></code></td>
                                <td><?php echo htmlspecialchars($rl['erule_val']); ?></td>
                                <td><?php echo htmlspecialchars($rl['erule_fail_msg']); ?></td>
                                <td class="text-center">
                                  <button type="button" class="btn btn-outline-danger btn-xs py-0 px-2 btn-del-rule" data-rid="<?php echo $rid; ?>">
                                    <i class="bi bi-trash3"></i>
                                  </button>
                                </td>
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

  function doReDirect() { window.history.back(); }

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
    if (0 == parseInt('<?php echo $csID; ?>')) return false;
    $.post('../funcFiles/cs_act_func.php', {
      'formAcce': 'func_cs_delete',
      'token': getTokenVal(),
      'csID': '<?php echo $csID; ?>'
    }, function(data) {
      if (data['RTN'] == 1) {
        window.parent.toastr['success'](data['msgStr']);
        window.location.replace("cs_list.php");
      } else {
        window.parent.toastr['error'](data['msgStr'] || '刪除失敗');
      }
    }, 'json');
  }

  function doButReset() { location.reload(true); }

  function ajaxPOST(frm) {
    var $frm = frm || $('#form_tabMenu1');
    var accE = (0 == parseInt('<?php echo $csID; ?>')) ? 'func_cs_addnew' : $frm.data('func');
    var datA = getFormObjects($frm);
    var imgBase64 = (typeof cropImage64 !== 'undefined' && cropImage64 !== '')
                    ? cropImage64
                    : ($('#main_crop_area').data('cropImage64') || '');

    $.ajax({
      type: 'post',
      url: '../funcFiles/cs_act_func.php',
      data: {
        'arrDatA': datA,
        'formAcce': accE,
        'imgUp': imgBase64,
        'token': getTokenVal(),
        'csID': '<?php echo $csID; ?>'
      },
      dataType: 'json',
      success: function(data) {
        if (accE == 'func_cs_addnew' && data['RTN'] == 1) {
          window.parent.toastr['success'](data['msgStr']);
          window.location.replace("cs_detail.php?currID=" + data['newID']);
          return false;
        }
        if (window.parent && window.parent.toastr) {
          window.parent.toastr[data['msgType']](data['msgStr']);
        }
        if (data['RTN'] == 1) {
          $frm.removeClass('was-validated');
          $('#sideCsTitle').text($('#course_title').val() || '未命名課程');
          $('#sideCsTier').text($('#course_tier_class option:selected').text());
          $('#sideCsCost').text('NT$ ' + Number($('#course_cost').val() || 0).toLocaleString());
          const stVal = $('#course_status').val();
          $('#sideCsStatus')
            .text($('#course_status option:selected').text().replace(/^\d+\s*-\s*/, ''))
            .toggleClass('text-success fw-bold', stVal === '1')
            .toggleClass('text-danger', stVal !== '1');
          if (data['upTime']) $('#sideCsUpdated').text(data['upTime']);
          cropImage64 = '';
          if (typeof doSerialize === 'function') doSerialize();
        }
      }
    });
  }

  $(document).ready(function() {
    if (typeof flatpickr !== 'undefined') {
      flatpickr($('input[type="date"]'), { locale: 'zh_tw', dateFormat: 'Y-m-d', allowInput: true, disableMobile: true });
    }
    if ($('#dataTable_cssess').length)   datatableCreate('dataTable_cssess');
    if ($('#dataTable_csenroll').length) datatableCreate('dataTable_csenroll');

    // 新增開課梯次
    $('#form_add_session').on('submit', function(e) {
      e.preventDefault();
      $.post('../funcFiles/cs_act_func.php', {
        'formAcce': 'func_sess_add',
        'token': getTokenVal(),
        'masterID': '<?php echo $masterID; ?>',
        'sess_batch': $('#newSessBatch').val(),
        'sess_startdate': $('#newSessDate').val(),
        'sess_starttime': $('#newSessTime').val(),
        'sess_location': $('#newSessRoom').val().trim()
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    // 切換梯次狀態 / 刪除梯次
    $(document).on('click', '.btn-toggle-sess', function() {
      const sid = $(this).data('sid');
      const nxt = ($(this).data('curr') == 1) ? 0 : 1;
      $.post('../funcFiles/cs_act_func.php', { 'formAcce': 'func_sess_status', 'token': getTokenVal(), 'sessID': sid, 'status': nxt }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    $(document).on('click', '.btn-del-sess', function() {
      if (!confirm('確定要刪除此開課梯次嗎？')) return;
      const sid = $(this).data('sid');
      $.post('../funcFiles/cs_act_func.php', { 'formAcce': 'func_sess_delete', 'token': getTokenVal(), 'sessID': sid }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) $('#sessRow_' + sid).fadeOut(250, function() { $(this).remove(); });
      }, 'json');
    });

    // 更新參與學員修課狀態
    $(document).on('change', '.sel-enroll-status', function() {
      const eid = $(this).data('eid');
      const st  = $(this).val();
      $.post('../funcFiles/cs_act_func.php', { 'formAcce': 'func_enroll_status', 'token': getTokenVal(), 'enrID': eid, 'enrStatus': st }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
      }, 'json');
    });

    // 新增 / 刪除優惠資格條件規則
    $('#form_add_rule').on('submit', function(e) {
      e.preventDefault();
      $.post('../funcFiles/cs_act_func.php', {
        'formAcce': 'func_rule_add',
        'token': getTokenVal(),
        'csID': '<?php echo $csID; ?>',
        'erule_type': $('#ruleType').val(),
        'erule_operator': $('#ruleOp').val(),
        'erule_val': $('#ruleVal').val().trim(),
        'erule_fail_msg': $('#ruleMsg').val().trim()
      }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) location.reload(true);
      }, 'json');
    });

    $(document).on('click', '.btn-del-rule', function() {
      if (!confirm('確定刪除此資格限制條件？')) return;
      const rid = $(this).data('rid');
      $.post('../funcFiles/cs_act_func.php', { 'formAcce': 'func_rule_delete', 'token': getTokenVal(), 'eruleID': rid }, function(data) {
        window.parent.toastr[data['msgType']](data['msgStr']);
        if (data['RTN'] == 1) $('#ruleRow_' + rid).fadeOut(250, function() { $(this).remove(); });
      }, 'json');
    });

    $('li.prevLink').on('click', function() { window.location.replace(getCurrentURI('', 'cs_list.php')); });
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
