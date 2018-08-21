<!-- modal -->
<div class="modal fade edit_image_modal" tabindex="-1" role="dialog" aria-hidden="true"  style="display:block; visibility: hidden;">
    <div class="modal-dialog imglist-confirm-modal">
        <div class="modal-content p10">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">画像編集</h4>
            </div>
            <div class="modal-body">
                <div class="row limit-height-modal" >
                    <div class="col-xs-12 img-container">
                        <img id="edit_modal_image" src="{{$imageUrl}}" alt="Picture">
                    </div>
                </div>
                <div class="row">
                <div class="col-xs-8 docs-buttons">
                    <div class="btn-group">
                        <button class="btn btn-edit-modal btn-ig btn-ig-active" data-method="setAspectRatio" data-option="1" title="Set Aspect Ratio" data-imgwidth="1080" data-imgheight="1080">
                            <input class="sr-only" id="aspestRatio1" name="aspestRatio" value="1" type="radio">
                            <span class="docs-tooltip" data-toggle="tooltip" title="1080 × 1080　Instagram広告にお薦めのサイズです。">
                                <i class="fa fa-instagram" aria-hidden="true"></i><span class=edit-image-size>1080 x 1080</span>
                            </span>
                        </button>

                        <button class="btn btn-edit-modal btn-ig" data-method="setAspectRatio" data-option="0.5625" title="Set Aspect Ratio" data-imgwidth="1080" data-imgheight="1920">
                            <input class="sr-only" id="aspestRatio1" name="aspestRatio" value="0.5625" type="radio">
                            <span class="docs-tooltip" data-toggle="tooltip" title="1080 x 1920　Instagram Stories広告にお薦めのサイズです。">
                                <i class="fa fa-instagram" aria-hidden="true"></i><span class=edit-image-size>1080 x 1920</span>
                            </span>
                        </button>

                        <button class="btn btn-edit-modal btn-fb" data-method="setAspectRatio" data-option="1.91" title="Set Aspect Ratio" data-imgwidth="1200" data-imgheight="628">
                            <input class="sr-only" id="aspestRatio2" name="aspestRatio" value="1.91" type="radio">
                            <span class="docs-tooltip" data-toggle="tooltip" title="1200 × 628　Facebook広告にお薦めのサイズです。">
                                <i class="fa fa-facebook-official" aria-hidden="true"></i><span class="edit-image-size">1200 x 628</span>
                            </span>
                        </button>

                        <button class="btn btn-edit-modal btn-fb" data-method="setAspectRatio" data-option="1" title="Set Aspect Ratio" data-imgwidth="600" data-imgheight="600">
                            <input class="sr-only" id="aspestRatio3" name="aspestRatio" value="1" type="radio">
                            <span class="docs-tooltip" data-toggle="tooltip" title="600 × 600　Facebookカルーセル広告にお薦めのサイズです。">
                                <i class="fa fa-facebook-official" aria-hidden="true"></i><span class="edit-image-size">600 x 600</span>
                            </span>
                        </button>

                        <button class="btn btn-edit-modal sns-btn mt10" data-method="setAspectRatio" data-option="2.5" title="Set Aspect Ratio" data-imgwidth="1000" data-imgheight="400">
                            <input class="sr-only" id="aspestRatio3" name="aspestRatio" value="2.5" type="radio">
                            <span class="docs-tooltip" data-toggle="tooltip" title="1000 x 400　Twitterカードにお薦めのサイズです。">
                                <i class="fa fa-twitter-square" aria-hidden="true"></i><span class="edit-image-size">1000 x 400</span>
                            </span>
                        </button>
                    </div>
                </div>
                <div class="col-xs-4 docs-buttons text-right">

                    <div class="btn-group">
                        <button class="btn btn-edit-modal" data-method="setDragMode" data-option="move" type="button" title="Move">
                                    <span class="docs-tooltip" data-toggle="tooltip" title="Move">
                                      <span class="fa fa-arrows"></span>
                                  </span>
                        </button>
                        <button class="btn btn-edit-modal" data-method="setDragMode" data-option="crop" type="button" title="Crop">
                                <span class="docs-tooltip" data-toggle="tooltip" title="Crop">
                                  <span class="fa fa-crop"></span>
                              </span>
                        </button>
                        <button class="btn btn-edit-modal" data-method="zoom" data-option="0.1" type="button" title="Zoom In">
                            <span class="docs-tooltip" data-toggle="tooltip" title="Zoom in">
                              <span class="fa fa-search-plus"></span>
                          </span>
                        </button>
                        <button class="btn btn-edit-modal" data-method="zoom" data-option="-0.1" type="button" title="Zoom Out">
                        <span class="docs-tooltip" data-toggle="tooltip" title="Zoom out">
                          <span class="fa fa-search-minus"></span>
                      </span>
                        </button>
                        <button class="btn btn-edit-modal" data-method="reset" type="button" title="Reset">
                <span class="docs-tooltip" data-toggle="tooltip" title="Refresh">
                  <span class="fa fa-refresh"></span>
              </span>
                        </button>
                    </div>
                    <!-- /.btn-group -->
                </div>
                <!-- /.docs-buttons -->
            </div>
            </div>
            <!-- /.modal -->

            <div class="text-center mt20">
                <form method="POST" action="" id="save_image_form">
                    <button type="button" class="btn btn-cancel" data-dismiss="modal">キャンセル</button>
                    {{csrf_field()}}
                    <input type="hidden" name="image_id" value="" id="edit_image_id">
                    <input type="hidden" name="origin_image_id" value="">
                    <textarea name="image_data" class="hidden"></textarea>
                    <input type="hidden" name="image_width" value="1080">
                    <input type="hidden" name="image_height" value="1080">
                    <button type="button" class="btn btn-primary" data-method="getDataURL" data-option="image/png" onclick="ga('send', 'event', 'image_edit', 'save', '{{\Auth::guard('advertiser')->user()->id}}');">
                        <i class="fa fa-download" aria-hidden="true"></i>
                        保存する
                    </button>
                </form>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- image cropping -->
@push("push-script-before-main-script")
    <script>
        var apiUrl = '{{URL::route('save_edited_image')}}';
    </script>
    <script src="{{static_file_version('/js/cropping/cropper.min.js')}}"></script>
    <script src="{{static_file_version('/js/custom/cropImage.js')}}"></script>
@endpush