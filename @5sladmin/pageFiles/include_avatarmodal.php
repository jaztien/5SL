<!-- include_avatarmodal.php 內容 -->
<!-- 💡 移除 id="avatarModal"，改用 class="avatarModal" -->
<div class="modal fade avatarModal" style="z-index: 99999;" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- Header -->
      <div class="modal-header">
        <h4 class="modal-title">代表圖像</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <!-- Body -->
      <div class="modal-body">
        <div class="img-container">
            <div class="row">
                <div class="col-md-8">
                    <!-- 💡 移除 id="cropImageSample"，改用 class="cropImageSample" -->
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
        <button type="button" class="btn btn-default" data-dismiss="modal">關閉</button>
        <!-- 💡 移除 id="crop"，改用 class="crop_btn_submit" -->
        <button type="button" class="btn btn-primary crop_btn_submit">裁切並上傳</button>
      </div>
    </div>
  </div>
</div>







<template>
      <!-- -------------------- 裁切頭像彈出視窗 -------------------- -->
        <div id="avatarModal" class="modal fade" style="z-index: 99999; " tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <!-- Header -->
              <div class="modal-header">
                <h4 class="modal-title">代表圖像</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <!-- /Header -->
              <!-- Model Body -->
              <div class="modal-body">
                <div class="img-container">
                    <div class="row">
                        <div class="col-md-8">
                            <img src="" id="cropImageSample" style="display:block;max-width:100%;" />
                        </div>
                        <div class="col-md-4">
                            <div class="preview"></div>
                        </div>
                    </div>
                </div>
              </div>
              <!-- /Model Body -->
              <!-- Footer -->
              <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">關閉</button>
                <button type="button" id="crop" class="btn btn-primary">裁切並上傳</button>
              </div>
              <!-- /Footer -->
            </div>
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- -------------------- /裁切頭像彈出視窗 -------------------- -->
</template>
