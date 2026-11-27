<!-- include_avatarmodal.php (Bootstrap 5 相容版) -->
<div class="modal fade avatarModal" style="z-index: 99999;" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- Header -->
      <div class="modal-header">
        <h5 class="modal-title">代表圖像</h5>
        <!-- [變更1] BS5 關閉按鈕寫法 -->
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Body -->
      <div class="modal-body">
        <div class="img-container">
          <div class="row">
            <div class="col-md-8">
              <img src="" class="cropImageSample" style="display:block;max-width:100%;" />
            </div>
            <div class="col-md-4">
              <div class="preview"></div>
            </div>
          </div>
        </div>
      </div>
      <!-- Footer -->
      <div class="modal-footer justify-content-between">
        <!-- [變更2] BS5 關閉屬性 data-bs-dismiss="modal"，按鈕改用 btn-secondary -->
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
        <button type="button" class="btn btn-primary crop_btn_submit">裁切並上傳</button>
      </div>
    </div>
  </div>
</div>