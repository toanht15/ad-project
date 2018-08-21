<script type="text/x-template" id="slideshow_confirm_vue_component">
<div id="js_images_slide_show" class="modal fade" role="dialog" style="display:block; visibility: hidden;">
    <div class="modal-dialog imglist-confirm-modal imglist-slide-show">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" class="close">×</button>
                <h4 class="modal-title">スライドショー作成</h4>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="create_slideshow_form">
                    <div class="col-xs-12 content">
                        <div class="input-area">
                            <dl>
                                <dt class="small-dd-left small-dd-left-90">サイズ</dt>
                                <dd class="small-dd-right">
                                    <label><input type="radio" value="{{\App\Models\Slideshow::VIDEO_TYPE_SQUARE}}" name="video_type" @click="changeVideoType" v-model="slideshow.video_type" class="radio-button" id="square_video_radio">正方形</label>
                                    <label><input type="radio" value="{{\App\Models\Slideshow::VIDEO_TYPE_STORIES}}" name="video_type" @click="changeVideoType"  v-model="slideshow.video_type" class="radio-button" id="stories_video_radio">Instagram Stories</label>
                                </dd>
                            </dl>
                            <dl class="item border-item">
                                <dt class="small-dd-left">合計</dt>
                                <dd class="small-dd-right">約 <span id="video_duration" class="big-number" v-text="videoDuration">  </span> 秒 /約
                                    <span class="big-number" v-text="slideshow.size">  </span> MB</dd>
                            </dl>
                            <small class="stories_notice" v-if="slideshow.video_type == {{\App\Models\Slideshow::VIDEO_TYPE_STORIES}}">※Instagram Storiesは全体で15秒までとなります</small>
                            <div class="item slide-show-setting">
                                <dl class="form-inline">
                                    <dt class="small-dd-left">画像１枚の表示時間</dt>
                                    <dd class="small-dd-right"><input type="number" v-model="slideshow.time_per_img" name="time_per_img" value="2" min="1" required="required" class="input-number form-control"> 秒</dd>
                                </dl>
                                <dl class="form-inline">
                                    <dt class="small-dd-left">エフェクト</dt>
                                    <dd class="small-dd-right">
                                        <ul class="effect-selecter">
                                            <li>
                                                <label>
                                                    <input class="radio-butto-rm" type="radio" v-model="slideshow.effect_type" name="effect_type" value="{{\App\Models\Slideshow::EFFECT_TYPE_FADEINOUT}}">
                                                    <img src="{{static_file_version('/images/effect-gif/01_FadeInOut_AniGIF.gif')}}" width="45" height="45" border="0">
                                                </label>
                                            </li>
                                            <li>
                                                <label>
                                                    <input class="radio-butto-rm" type="radio" v-model="slideshow.effect_type" name="effect_type" value="{{\App\Models\Slideshow::EFFECT_TYPE_HORIZONTAL_SLIDE}}">
                                                    <img src="{{static_file_version('/images/effect-gif/02_HorizontalSlide.gif')}}" width="45" height="45" border="0">
                                                </label>
                                            </li>
                                            <li>
                                                <label>
                                                    <input class="radio-butto-rm" type="radio" v-model="slideshow.effect_type" name="effect_type" value="{{\App\Models\Slideshow::EFFECT_TYPE_ZOOMIN}}">
                                                    <img src="{{static_file_version('/images/effect-gif/03_zoonInFade.gif')}}" width="45" height="45" border="0">
                                                </label>
                                            </li>
                                            <li>
                                                <label>
                                                    <input class="radio-butto-rm" type="radio" v-model="slideshow.effect_type" name="effect_type" value="{{\App\Models\Slideshow::EFFECT_TYPE_ZOOMOUT}}">
                                                    <img src="{{static_file_version('/images/effect-gif/04_zoonOutFade.gif')}}" width="45" height="45" border="0">
                                                </label>
                                            </li>
                                        </ul>
                                    </dd>
                                </dl>
                            </div>
                        </div>

                        <div class="preview-area" v-if="slideshow.video_type == {{\App\Models\Slideshow::VIDEO_TYPE_STORIES}}">
                            <p class="preview-title">プレビュー</p>
                            <div id="video_preview_area" class="preview-inner mb10 story-video-preview">
                                <img src="{{static_file_version('/images/no_story.jpg')}}" height="280" id="no_video_img" v-if="slideshow.url == null">
                                <!-- /modal-dialog -->
                                <video controls class="create-slideshow-video-preview-tmp img-responsive video-fill-frame video-js vjs-styles-dimensions vjs-big-play-centered vjs-skin-colors-orange hidden">
                                    <source  src="" type="video/mp4">
                                </video>
                            </div>
                            <button type="button" class="btn btn-blue form-control story-video-preview" @click="createVideo(1)" onclick="ga('send', 'event', 'slideshow', 'preview', '{{$advertiser->id}}');">プレビュー作成</button>
                        </div>
                        <div class="preview-area" v-else>
                            <p class="preview-title">プレビュー</p>
                            <div id="video_preview_area" class="preview-inner mb10">
                                <img src="{{static_file_version('/images/no_video.jpg')}}" id="no_video_img" height="280" v-if="slideshow.url == null">
                                <!-- /modal-dialog -->
                                <video controls class="create-slideshow-video-preview-tmp img-responsive video-fill-frame video-js vjs-styles-dimensions vjs-big-play-centered vjs-skin-colors-orange hidden">
                                    <source  src="" type="video/mp4">
                                </video>
                            </div>
                            <button type="button" class="btn btn-blue form-control" @click="createVideo(1)" onclick="ga('send', 'event', 'slideshow', 'preview', '{{$advertiser->id}}');">プレビュー作成</button>
                        </div>
                    </div>

                    <h2>選択済みのUGC</h2>
                    <ul class="col-md-12 slide-show-ugc">
                        <li class="slide-show-image" id="add_image_btn_tmp">
                            <a id="add_image_url"><i class="fa fa-plus-circle fa-3x fontawesome-add-icon" id="add-image-icon" data-dismiss="modal" aria-hidden="true"></i></a>
                        </li>
                        <li class="slide-show-image" v-for="(image,index) in slideshowimages" :id="'js_slideshow_image_' + image.post_id">
                            <input type="hidden" name="image_ids[]" :value="image.image_id">
                            <h3 v-text="index + 1"></h3>
                            <div class="image view view-first not-zoom-img">
                                <div class="image_url">
                                    <img class="center-block" :src="image.image_url">
                                </div>
                                <div class="slideshow-img-overlay" v-if="slideshow.video_type == {{\App\Models\Slideshow::VIDEO_TYPE_STORIES}}"><p></p></div>
                                <div class="slideshow-img-overlay right-overlay" v-if="slideshow.video_type == {{\App\Models\Slideshow::VIDEO_TYPE_STORIES}}"><p></p></div>
                                <div class="mask">
                                    <p></p>
                                    <div class="tools tools-bottom">
                                        <a href="javascript:void(0)" @click="openEditImageModal(image)" class="edit_image_btn" data-toggle="tooltip" title="編集する"><i class="fa fa-pencil"></i></a>
                                        <a href="javascript:void(0)" v-if="slideshowimages.length > 2" class="js-remove-slideshow-image" data-toggle="tooltip" title="削除する" @click="removeImage(image.post_id)"><i class="fa fa-times"></i></a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </form>
            </div>

            <!-- /modal-body -->
            <div class="modal-footer form-inline footer-btn">
                <button type="button" class="btn btn-cancel form-control" data-dismiss="modal">
                    キャンセル
                </button>
                <button type="button" @click="createVideo(0)" class="btn btn-primary form-control" onclick="ga('send', 'event', 'slideshow', 'save', '{{$advertiser->id}}');">
                    <i class="fa fa-lg fa-download"></i>保存
                </button>
            </div>
            <!-- /modal-footer -->
        </div>
        <!-- /modal-content -->
    </div>

</div>
</script>

@include('templates.edit_image_modal', ['imageUrl' => static_file_version('/images/no_video.jpg')])

@push("push-script-before-main-script")
    <script>
        var createSlideshowAPIUrl = '{{URL::route('create_slideshow')}}',
            editingImageId = 0;
    </script>
    <script src="{{static_file_version('/js/custom/videojs.js')}}"></script>
    <script src="{{static_file_version('/js/lightslider.js')}}"></script>
    <script src="{{static_file_version('/js/custom/slideshowModal.js')}}"></script>
@endpush
