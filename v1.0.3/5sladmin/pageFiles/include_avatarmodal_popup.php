        <!-- -------------------- 裁切頭像彈出視窗 POPUP -------------------- -->
        <div id="avatarModal2" class="modal fade" style="z-index: 99999; " tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
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
                            <img src="" id="cropImageSample2" style="display:block;max-width:100%;" />
                        </div>
                        <div class="col-md-4">
                            <div class="preview2"></div>
                        </div>
                    </div>
                </div>
              </div>
              <!-- /Model Body -->
              <!-- Footer -->
              <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">關閉</button>
                <button type="button" id="crop2" class="btn btn-primary">裁切並上傳</button>
              </div>
              <!-- /Footer -->
            </div>
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- -------------------- /裁切頭像彈出視窗 -------------------- -->
