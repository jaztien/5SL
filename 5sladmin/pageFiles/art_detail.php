<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');

// 定義本頁面代碼
define('PAGE_MARK', 'PG_ARTI');
define('PAGE_ITEM', 'ITEM_ARTI');
define('PAGE_TOPAUTH', true);
define('FORMEDIT_TOPAUTH', true);
define('bkRedir', 1);

require_once('../includeFiles/page.basic.inc.php');
require_once('../includeFiles/common.lib.php');

$artiID = currID;
$res    = ($artiID > 0) ? DB->row("SELECT * FROM fsl_articles WHERE arti_id = ? AND arti_status != 9", array($artiID)) : array();
if (!is_array($res)) $res = array();

// 撈取上架課程與商品清單（供文章關聯推薦）
$courseOptions = DB->query("SELECT course_id, course_title FROM fsl_courses WHERE parent_id = 0 AND course_status != 9 ORDER BY course_id DESC");
$merchOptions  = DB->query("SELECT merch_id, merch_sku, merch_title FROM fsl_merchandise WHERE merch_status != 9 ORDER BY merch_id DESC");
if (!is_array($courseOptions)) $courseOptions = array();
if (!is_array($merchOptions))  $merchOptions  = array();

// 封面圖設定 (800x500)
if (!empty($artiID) && file_exists("../uploadFiles/art/aimg_" . $artiID . "_.jpg")) {
  $cropImgDisplay = 'show';
  $mainimgpath    = '../uploadFiles/art/aimg_' . $artiID . '_.jpg?' . time();
  $mainimgchk     = 'aimg_' . $artiID . '_.jpg';
} else {
  $cropImgDisplay = 'hide';
  $mainimgpath    = '../imageFiles/nopic_user_200x200.jpg';
  $mainimgchk     = '';
}

$modeLabels = array(
  1 => '1 - 知識專欄文章',
  2 => '2 - 系統通知與推播模板',
  3 => '3 - Email / EDM 郵件模板',
  4 => '4 - 法律條款 / 靜態頁面'
);
$statusLabels = array(0 => '下架停用', 1 => '上架啟用', 2 => '草稿待審');
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
  <title>Fifth Season Life 人生第五季 | 知識專欄與動態模板編輯</title>

  <link rel="stylesheet" href="../pluginFiles/googleapis/fonts.googleapis.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <link rel="stylesheet" href="../pluginFiles/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../pluginFiles/flatpickr/flatpickr.min.css">
  <link rel="stylesheet" href="../cssFiles/adminlte.css">
  <link rel="stylesheet" href="../pluginFiles/cropper/cropper.css"/>
  <link rel="stylesheet" href="../pluginFiles/suneditor/css/suneditor.min.css">
  <link rel="stylesheet" href="../cssFiles/5sl.fileupload.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl.formaccess.css?t=<?php echo time(); ?>">
  <link rel="stylesheet" href="../cssFiles/5sl_iniframe.css?t=<?php echo time(); ?>">

<style>
  .art-cover-area.image_area label[for^="cropUploadImage"] {
    border-radius: 12px !important;
    border: 1px solid #dee2e6;
    width: 100% !important;
    max-width: 240px;
  }
  .art-cover-area.image_area label[for^="cropUploadImage"] img.imgEDicon {
    border-radius: 12px !important;
    width: 100%;
    object-fit: cover;
  }
  .sun-editor {
    font-family: 'Noto Sans TC', sans-serif !important;
    border-radius: 8px !important;
  }
</style>
</head>
<body class="bg-body-tertiary theme-blue">
  <div class="app-wrapper">
    <main class="app-main">
      <!-- 頁頭導覽條 -->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h4 class="m-0">
                <?php echo !empty($artiID) ? "編輯" : "新增"; ?>知識專欄與動態模板<span class="fs-7">【ID: <?php echo $artiID; ?>】</span>
              </h4>
              <h6 class="m-0 fw-light text-muted fs-7 mt-1 lh-xs mb-n2"><i class="bi bi-arrow-return-right"></i> 支援 SunEditor 圖庫串接、{{變數}} 動態字串替換、PTS 點數解鎖與排程上下架</h6>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end small mb-0">
                <li class="breadcrumb-item"><a href="ad_home.php">首頁</a></li>
                <li class="breadcrumb-item">互動行銷管理</li>
                <li class="breadcrumb-item prevLink" style="cursor:pointer;" title="返回列表">知識專欄與動態模板</li>
                <li class="breadcrumb-item active"><?php echo !empty($artiID) ? "內容編輯" : "新增內容"; ?></li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- 主要內容區域 -->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <!-- 左側封面圖與發布統計卡片 -->
            <div class="col-md-3 mb-3">
              <div class="card card-primary card-outline shadow-lg">
                <div class="card-body box-profile">
                  <div class="text-center">
                    <div class="image_area art-cover-area">
                      <i class="nav-icon bi bi-trash3 fs-4 cropImgTrash <?php echo $cropImgDisplay; ?>" title="刪除封面圖"></i>
                      <form method="post">
                        <label for="cropUploadImage">
                          <img id="avatarImage" name="avatarImage" class="img-fluid imgEDicon" src="<?php echo $mainimgpath; ?>" alt="文章封面圖" title="文章封面圖 (800×500)"/>
                          <div class="overlay" style="border-radius: 0 0 12px 12px;">
                            <div class="overtext"><?php echo !empty($artiID) ? "更改" : "上傳"; ?>封面主圖 (800×500)</div>
                          </div>
                          <input type="file" name="image" class="image d-none" id="cropUploadImage" crop-width="800" crop-height="500">
                        </label>
                      </form>
                      <?php include('include_avatarmodal.php'); ?>
                    </div>
                  </div>

                  <h5 class="fw-bold text-center mt-3 mb-1" id="sideArtTitle"><?php echo !empty($res['arti_title']) ? htmlspecialchars($res['arti_title']) : ($artiID > 0 ? '未命名內容' : '新建立文章/模板'); ?></h5>
                  <div class="text-center small text-primary fw-bold mb-3" id="sideArtMode"><?php echo $modeLabels[(int)($res['arti_mode'] ?? 1)] ?? '知識專欄文章'; ?></div>

                  <ul class="list-group list-group-flush small">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>上下架狀態</b>
                      <span id="sideArtStatus" class="<?php echo ((int)($res['arti_status'] ?? 1) === 1) ? 'text-success fw-bold' : 'text-secondary'; ?>">
                        <?php echo $statusLabels[(int)($res['arti_status'] ?? 1)] ?? '上架啟用'; ?>
                      </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>調用代碼</b>
                      <code id="sideArtCode"><?php echo !empty($res['arti_keycode']) ? htmlspecialchars($res['arti_keycode']) : '-'; ?></code>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>累計瀏覽次數</b>
                      <span><?php echo number_format((int)($res['arti_visits'] ?? 0)); ?> 次</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>按讚推薦數</b>
                      <span><?php echo number_format((int)($res['arti_likes'] ?? 0)); ?> 讚</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <b>最後更新</b>
                      <span class="text-muted" id="sideArtUpdated"><?php echo $res['arti_updated'] ?? '-'; ?></span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <!-- 右側多分頁內容與動態模板編輯區 -->
            <div class="col-md-9">
              <div class="card shadow-lg modern-card-tabs">
                <div class="card-header p-2">
                  <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                      <a class="nav-link active" href="#tabMenu1" data-index="1" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-sliders me-1"></i>基本設定、調用代碼與閱讀權限
                      </a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link" href="#tabMenu2" data-index="2" data-bs-toggle="tab" data-topbut="1" role="tab">
                        <i class="bi bi-file-earmark-code me-1"></i>內文與動態變數模板 (SunEditor)
                      </a>
                    </li>
                  </ul>
                </div>

                <div class="card-body bg-opacity-50">
                  <form id="form_artMaster" action="../funcFiles/art_act_func.php" method="post" data-func="func_art_infoUpdate" novalidate>
                    <input type="hidden" name="mainimgchk" id="mainimgchk" class="notSubmit" value="<?php echo $mainimgchk; ?>">
                    <div class="tab-content">
                      <!-- Tab 1: 基本規格、調用代碼、關聯推薦與權限排程 -->
                      <div class="tab-pane fade show active" id="tabMenu1" role="tabpanel">
                        <div class="row g-3">
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">內容定位模式 (arti_mode)</label>
                            <select class="form-select" id="arti_mode" name="arti_mode">
                              <option value="1" <?php echo ((int)($res['arti_mode'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 知識專欄文章</option>
                              <option value="2" <?php echo ((int)($res['arti_mode'] ?? 1) === 2) ? 'selected' : ''; ?>>2 - 系統通知與推播模板</option>
                              <option value="3" <?php echo ((int)($res['arti_mode'] ?? 1) === 3) ? 'selected' : ''; ?>>3 - Email / EDM 郵件模板</option>
                              <option value="4" <?php echo ((int)($res['arti_mode'] ?? 1) === 4) ? 'selected' : ''; ?>>4 - 法律條款與靜態頁面</option>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">唯一調用代碼 (arti_keycode)</label>
                            <input type="text" class="form-control text-uppercase" id="arti_keycode" name="arti_keycode"
                                   value="<?php echo htmlspecialchars($res['arti_keycode'] ?? ''); ?>" placeholder="如：MAIL_WELCOME / PRIVACY_POLICY">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">所屬分類 (arti_cate)</label>
                            <select class="form-select" name="arti_cate">
                              <option value="mindset" <?php echo (($res['arti_cate'] ?? 'mindset') === 'mindset') ? 'selected' : ''; ?>>mindset - 心靈與生命整理</option>
                              <option value="healing" <?php echo (($res['arti_cate'] ?? '') === 'healing') ? 'selected' : ''; ?>>healing - 自然療癒與實作</option>
                              <option value="notice" <?php echo (($res['arti_cate'] ?? '') === 'notice') ? 'selected' : ''; ?>>notice - 官方公告與快訊</option>
                              <option value="tpl" <?php echo (($res['arti_cate'] ?? '') === 'tpl') ? 'selected' : ''; ?>>tpl - 系統動態變數模板</option>
                              <option value="legal" <?php echo (($res['arti_cate'] ?? '') === 'legal') ? 'selected' : ''; ?>>legal - 服務條款與隱私權</option>
                            </select>
                          </div>

                          <div class="col-md-7">
                            <label class="form-label small fw-bold">文章標題 / 郵件主旨 (支援變數如 {{member_name}}) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="arti_title" name="arti_title" required
                                   value="<?php echo htmlspecialchars($res['arti_title'] ?? ''); ?>" placeholder="輸入專欄標題或通知/郵件主旨...">
                          </div>
                          <div class="col-md-5">
                            <label class="form-label small fw-bold">副標題 / 郵件摘要前言</label>
                            <input type="text" class="form-control" name="arti_subscript"
                                   value="<?php echo htmlspecialchars($res['arti_subscript'] ?? ''); ?>" placeholder="顯示於文章標題下方或郵件預覽摘要">
                          </div>

                          <div class="col-md-6">
                            <label class="form-label small fw-bold">標籤關鍵字 (逗號分隔)</label>
                            <input type="text" class="form-control" name="arti_tag"
                                   value="<?php echo htmlspecialchars($res['arti_tag'] ?? ''); ?>" placeholder="如：生命整理,色彩心理,第五季">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label small fw-bold">支援動態變數清單說明 (arti_tpl_vars)</label>
                            <input type="text" class="form-control" id="arti_tpl_vars" name="arti_tpl_vars"
                                   value="<?php echo htmlspecialchars($res['arti_tpl_vars'] ?? ''); ?>" placeholder="如：member_name,order_no,course_title,date,url">
                          </div>

                          <div class="col-12">
                            <label class="form-label small fw-bold">內容摘要說明 (arti_brief)</label>
                            <textarea class="form-control" name="arti_brief" rows="2" maxlength="500"><?php echo htmlspecialchars($res['arti_brief'] ?? ''); ?></textarea>
                          </div>

                          <div class="col-12"><div class="hr-more">閱讀解鎖權限、上下架排程與關聯推薦</div><hr class="my-1"></div>

                          <div class="col-md-3">
                            <label class="form-label small fw-bold">閱讀權限控管</label>
                            <select class="form-select" id="arti_guestbrowse" name="arti_guestbrowse">
                              <option value="1" <?php echo ((int)($res['arti_guestbrowse'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 公開所有人可讀</option>
                              <option value="2" <?php echo ((int)($res['arti_guestbrowse'] ?? 1) === 2) ? 'selected' : ''; ?>>2 - 僅限登入會員</option>
                              <option value="3" <?php echo ((int)($res['arti_guestbrowse'] ?? 1) === 3) ? 'selected' : ''; ?>>3 - 需消耗 PTS 解鎖</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">解鎖消耗 PTS 點數</label>
                            <input type="number" class="form-control" name="arti_cost_pts" min="0" step="1"
                                   value="<?php echo (int)($res['arti_cost_pts'] ?? 0); ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">精選置頂設定</label>
                            <select class="form-select" name="arti_is_featured">
                              <option value="0" <?php echo ((int)($res['arti_is_featured'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - 一般排序</option>
                              <option value="1" <?php echo ((int)($res['arti_is_featured'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - 精選置頂曝光</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">上下架狀態</label>
                            <select class="form-select" id="arti_status" name="arti_status">
                              <option value="1" <?php echo ((int)($res['arti_status'] ?? 1) === 1) ? 'selected' : ''; ?>>1 - 上架啟用</option>
                              <option value="2" <?php echo ((int)($res['arti_status'] ?? 1) === 2) ? 'selected' : ''; ?>>2 - 草稿待審</option>
                              <option value="0" <?php echo ((int)($res['arti_status'] ?? 1) === 0) ? 'selected' : ''; ?>>0 - 下架停用</option>
                            </select>
                          </div>

                          <div class="col-md-3">
                            <label class="form-label small fw-bold">上架日期 (留空=立即)</label>
                            <input type="date" class="form-control" name="arti_dateopen" value="<?php echo htmlspecialchars($res['arti_dateopen'] ?? ''); ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">下架日期 (留空=永久)</label>
                            <input type="date" class="form-control" name="arti_dateclose" value="<?php echo htmlspecialchars($res['arti_dateclose'] ?? ''); ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">關聯推薦課程 (選填)</label>
                            <select class="form-select" name="course_id">
                              <option value="">-- 不關聯課程 --</option>
                              <?php foreach ($courseOptions as $co): ?>
                                <option value="<?php echo (int)$co['course_id']; ?>" <?php echo ((int)($res['course_id'] ?? 0) === (int)$co['course_id']) ? 'selected' : ''; ?>>
                                  #<?php echo (int)$co['course_id']; ?> - <?php echo htmlspecialchars($co['course_title']); ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">關聯推薦商品 (選填)</label>
                            <select class="form-select" name="merch_id">
                              <option value="">-- 不關聯商品 --</option>
                              <?php foreach ($merchOptions as $mo): ?>
                                <option value="<?php echo (int)$mo['merch_id']; ?>" <?php echo ((int)($res['merch_id'] ?? 0) === (int)$mo['merch_id']) ? 'selected' : ''; ?>>
                                  #<?php echo (int)$mo['merch_id']; ?> - <?php echo htmlspecialchars($mo['merch_title']); ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                        </div>
                      </div>

                      <!-- Tab 2: SunEditor 圖文編輯器與動態變數插入預覽 -->
                      <div class="tab-pane fade" id="tabMenu2" role="tabpanel">
                        <div class="p-2 px-3 bg-light border rounded-3 mb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                          <div class="d-flex flex-wrap align-items-center gap-1 small">
                            <span class="fw-bold text-dark me-1"><i class="bi bi-braces text-primary me-1"></i>點擊插入動態變數：</span>
                            <button type="button" class="btn btn-outline-primary btn-xs py-0 px-2 btn-insert-var" data-var="{{member_name}}">受文者姓名</button>
                            <button type="button" class="btn btn-outline-primary btn-xs py-0 px-2 btn-insert-var" data-var="{{member_email}}">會員信箱</button>
                            <button type="button" class="btn btn-outline-primary btn-xs py-0 px-2 btn-insert-var" data-var="{{order_no}}">訂單編號</button>
                            <button type="button" class="btn btn-outline-primary btn-xs py-0 px-2 btn-insert-var" data-var="{{course_title}}">課程名稱</button>
                            <button type="button" class="btn btn-outline-primary btn-xs py-0 px-2 btn-insert-var" data-var="{{pts}}">點數數值</button>
                            <button type="button" class="btn btn-outline-primary btn-xs py-0 px-2 btn-insert-var" data-var="{{date}}">系統日期</button>
                            <button type="button" class="btn btn-outline-primary btn-xs py-0 px-2 btn-insert-var" data-var="{{url}}">專屬連結網址</button>
                          </div>
                          <button type="button" class="btn btn-warning btn-xs fw-bold" id="btnPreviewTplVars">
                            <i class="bi bi-eye-fill me-1"></i>模擬變數替換預覽
                          </button>
                        </div>

                        <textarea id="arti_body" name="arti_body" class="form-control" rows="16"><?php echo htmlspecialchars($res['arti_body'] ?? ''); ?></textarea>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
  <!-- 動態變數替換模擬預覽視窗 Modal -->
  <div class="modal fade" id="tplPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header bg-dark text-white py-2">
          <h6 class="modal-title"><i class="bi bi-eye-fill me-2 text-warning"></i>動態變數模板模擬渲染預覽</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="p-2 px-3 bg-light border rounded-2 mb-3 small">
            <span class="text-muted">模擬標題 / 郵件主旨：</span>
            <strong class="fs-6 text-primary d-block mt-1" id="previewRenderedTitle"></strong>
          </div>
          <div class="p-3 border rounded-2 bg-white" id="previewRenderedBody" style="min-height: 220px;"></div>
        </div>
        <div class="modal-footer py-2">
          <span class="me-auto small text-muted">* 以上已自動帶入測試會員（林向光）與模擬訂單數據進行渲染驗證</span>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">關閉預覽</button>
        </div>
      </div>
    </div>
  </div>

  <script src="../pluginFiles/jquery/jquery-4.0.0.min.js"></script>
  <script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
  <script src="../pluginFiles/flatpickr/flatpickr.min.js"></script>
  <script src="../pluginFiles/flatpickr/location/zh_tw.js"></script>
  <script src="../pluginFiles/cropper/cropper.js"></script>
  <script src="../pluginFiles/suneditor/suneditor.min.js"></script>
  <script src="../pluginFiles/suneditor/lang/zh_tw.js"></script>
  <script src="../scriptFiles/adminlte.js"></script>
  <script src="../scriptFiles/page.basic.js"></script>
  <script src="../scriptFiles/iframe-state-keeper.js"></script>
  <script src="../scriptFiles/fileupload.js<?php echo '?t='.time(); ?>"></script>
  <script src="../scriptFiles/form.init.inc.js<?php echo '?t='.time(); ?>"></script>

  <script type="text/javascript">
  let artSunEditor = null;

  function validateBS5Form($form) {
    if (!$form || $form.length === 0) return true;
    const isValid = $form.checkValidity();
    $form.classList.add('was-validated');
    return isValid;
  }

  function doReDirect() { window.history.back(); }

  function doButSave() {
    if (artSunEditor) {
      $('#arti_body').val(artSunEditor.getContents());
    }
    var frm = $('#form_artMaster');
    if (!validateBS5Form(frm)) {
      const tab1El = document.querySelector('a[href="#tabMenu1"]');
      if (tab1El) bootstrap.Tab.getOrCreateInstance(tab1El).show();
      window.parent.toastr['warning']('請檢查標示紅框的必填欄位（如文章標題/郵件主旨）！');
      return false;
    }
    ajaxPOST(frm);
  }

  function doButDelete() {
    if (0 == parseInt('<?php echo $artiID; ?>')) return false;
    $.post('../funcFiles/art_act_func.php', {
      'formAcce': 'func_art_delete',
      'token': getTokenVal(),
      'artiID': '<?php echo $artiID; ?>'
    }, function(data) {
      if (data['RTN'] == 1) {
        window.parent.toastr['success'](data['msgStr']);
        window.location.replace('art_list.php');
      } else {
        window.parent.toastr['error'](data['msgStr'] || '刪除失敗');
      }
    }, 'json');
  }

  function doButReset() { location.reload(true); }

  function ajaxPOST(frm) {
    var $frm = frm || $('#form_artMaster');
    var accE = (0 == parseInt('<?php echo $artiID; ?>')) ? 'func_art_addnew' : $frm.data('func');
    var datA = getFormObjects($frm);
    if (artSunEditor) {
      datA['arti_body'] = artSunEditor.getContents();
    }
    var imgBase64 = (typeof cropImage64 !== 'undefined' && cropImage64 !== '')
                    ? cropImage64
                    : ($('#main_crop_area').data('cropImage64') || '');

    $.ajax({
      type: 'post',
      url: '../funcFiles/art_act_func.php',
      data: {
        'arrDatA': datA,
        'formAcce': accE,
        'imgUp': imgBase64,
        'token': getTokenVal(),
        'artiID': '<?php echo $artiID; ?>'
      },
      dataType: 'json',
      success: function(data) {
        if (accE == 'func_art_addnew' && data['RTN'] == 1) {
          window.parent.toastr['success'](data['msgStr']);
          window.location.replace('art_detail.php?currID=' + data['newID']);
          return false;
        }
        if (window.parent && window.parent.toastr) {
          window.parent.toastr[data['msgType']](data['msgStr']);
        }
        if (data['RTN'] == 1) {
          $frm.removeClass('was-validated');
          $('#sideArtTitle').text($('#arti_title').val() || '未命名內容');
          $('#sideArtMode').text($('#arti_mode option:selected').text());
          $('#sideArtCode').text($('#arti_keycode').val() ? $('#arti_keycode').val().toUpperCase() : '-');
          const stVal = $('#arti_status').val();
          $('#sideArtStatus')
            .text($('#arti_status option:selected').text().replace(/^\d+\s*-\s*/, ''))
            .toggleClass('text-success fw-bold', stVal === '1')
            .toggleClass('text-secondary', stVal !== '1');
          if (data['upTime']) $('#sideArtUpdated').text(data['upTime']);
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

    // 1. 初始化 SunEditor（聯動檔案庫圖庫 img_gallery_api.php）
    if (typeof SUNEDITOR !== 'undefined' && document.getElementById('arti_body')) {
      artSunEditor = SUNEDITOR.create(document.getElementById('arti_body'), {
        lang: SUNEDITOR_LANG['zh_tw'],
        width: '100%',
        height: '420px',
        imageGalleryUrl: '../funcFiles/img_gallery_api.php',
        buttonList: [
          ['undo', 'redo'],
          ['font', 'fontSize', 'formatBlock'],
          ['bold', 'underline', 'italic', 'strike'],
          ['fontColor', 'hiliteColor'],
          ['removeFormat'],
          ['outdent', 'indent', 'align', 'horizontalRule', 'list', 'table'],
          ['link', 'image', 'imageGallery', 'video'],
          ['fullScreen', 'showBlocks', 'codeView']
        ]
      });

      artSunEditor.onChange = function() {
        if (window.parent && window.parent.$) {
          window.parent.$('#topBut_save').addClass('active');
        }
      };
    }

    // 2. 點擊一鍵插入 {{變數}} 至 SunEditor 游標處並同步更新變數說明欄
    $(document).on('click', '.btn-insert-var', function() {
      const vTag = $(this).data('var');
      if (artSunEditor) {
        artSunEditor.insertHTML(`<span style="background:#eef2ff;color:#4f46e5;padding:1px 5px;border-radius:4px;font-family:monospace;">${vTag}</span>&nbsp;`);
      }
      const cleanName = vTag.replace(/[{}]/g, '');
      const curVars   = $('#arti_tpl_vars').val().trim();
      const varList   = curVars ? curVars.split(',').map(s => s.trim()) : [];
      if (!varList.includes(cleanName)) {
        varList.push(cleanName);
        $('#arti_tpl_vars').val(varList.join(','));
      }
    });

    // 3. 模擬動態變數替換預覽
    $('#btnPreviewTplVars').on('click', function() {
      const rawTitle = $('#arti_title').val() || '（未設定標題）';
      const rawBody  = artSunEditor ? artSunEditor.getContents() : $('#arti_body').val();
      const mockData = {
        '{{member_name}}': '林向光',
        '{{member_email}}': 'guang@5slife.com',
        '{{order_no}}': 'ORD202611230088',
        '{{course_title}}': '《人生第五季：色彩覺察與生命微整理》',
        '{{pts}}': '500',
        '{{date}}': '2026-11-23',
        '{{url}}': 'https://5slife.com'
      };

      let renderedTitle = rawTitle;
      let renderedBody  = rawBody;
      Object.keys(mockData).forEach(key => {
        const reg = new RegExp(key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
        renderedTitle = renderedTitle.replace(reg, mockData[key]);
        renderedBody  = renderedBody.replace(reg, mockData[key]);
      });

      $('#previewRenderedTitle').text(renderedTitle);
      $('#previewRenderedBody').html(renderedBody);
      bootstrap.Modal.getOrCreateInstance(document.getElementById('tplPreviewModal')).show();
    });

    $('li.prevLink').on('click', function() { window.location.replace(getCurrentURI('', 'art_list.php')); });
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
