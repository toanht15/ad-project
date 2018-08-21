<div class="modal fade" role="dialog" v-if="bigModalImage != null" id="big_image_modal">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body">
                <div class="ugc-image video-div">
                    <video v-if="bigModalImage.video_url" controls class="ugc-video-preview video-modal-tmp img-responsive video-fill-frame video-js vjs-styles-dimensions vjs-big-play-centered vjs-skin-colors-orange hidden">
                        <source  :src="bigModalImage.video_url" type="video/mp4">
                    </video>
                    <img v-else :src="bigModalImage.image_url" id="image_modal" style="max-width: 100%">
                    <img v-show="bigModalImage.file_format == {{\App\Models\Post::CAROUSEL_IMAGE}} || bigModalImage.file_format == {{\App\Models\Post::CAROUSEL_VIDEO}}" id="carousel-icon" src="{{static_file_version('/images/icon_carousel.png')}}">
                </div>
                <div class="ugc-info">
                    <div class="ugc-author-info">
                        <p>
                            <a :href="bigModalImage.author_url" id="author_name" target="_blank">@{{ bigModalImage.author_name ? bigModalImage.author_name : bigModalImage.username }}</a>
                        </p>
                    </div>
                    <div class="ugc-post-info">
                        <div class="red hash-like"><i class="fa fa-heart" aria-hidden="true"></i><span id="ugc_like">@{{ bigModalImage.like }}</span></div>
                        <div class="post-date"><a :href="bigModalImage.post_url" id="ugc_url" target="_blank">Post</a> : <span id="ugc_pub">@{{ bigModalImage.pub_date.replace(/\-/g, '/') }}</span></div>
                        <span class="label label-registed-part p5 status" v-if="bigModalImage.vtdr_part_id != null">UGCセット</span>
                        <p class="kpi-status" id="js-ugc-status" v-html="Utility.getStatusLabel(bigModalImage.offer_status)"></p>
                    </div>
                    <pre class="ugc-description" id="ugc_text">@{{ bigModalImage.text }}</pre>

                    <div class="btn-area-ugcmodal btn-area">
                        @if (isset($showAction) && $showAction)
                            {{--<div class="custom-checkbox">--}}
                                {{--<input type="checkbox" id="select_big_modal_img" :checked="isSelected(genViewId(bigModalImage.post_id))"/>--}}
                                {{--<label for="select_big_modal_img" v-on:click="selectImage(genViewId(bigModalImage.post_id))"></label>--}}
                            {{--</div>--}}
                            <div class="col-xs-12" v-if="!isSelected(genViewId(bigModalImage.post_id))" v-on:click="selectImage(genViewId(bigModalImage.post_id))">
                                <a class="btn btn-primary btn-block js_add_offer_draft">
                                    <i class="fa fa-check-circle" aria-hidden="true"></i>選択</a>
                            </div>
                            <div class="col-xs-12" v-else v-on:click="selectImage(genViewId(bigModalImage.post_id))">
                                <a class="btn btn-delete btn-block js_remove_offer_draft"><i class="fa fa-check-circle-o" aria-hidden="true"></i>選択解除</a>
                            </div>
                        @endif
                        <div class="clearfix"></div>
                    </div>
                    <!-- /.btn-area-ugcmodal -->
                </div>
                <!-- /.ugc-info -->
                <div class="clearfix"></div>
            </div>
            <!-- /.modal-body -->
            @if (isset($showAction) && $showAction)
                <div class="modalnext" v-on:click="nextBigModal()"><img src="{{static_file_version('/images/icon-arrow-right-lg.png')}}" alt=""></div>
                <div class="modalprev" v-on:click="prevBigModal()"><img src="{{static_file_version('/images/icon-arrow-left-lg.png')}}" alt=""></div>
            @endif
        </div>
    </div>
</div>
@push("push-script")
@endpush