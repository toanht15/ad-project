<!-- /puslish_part_modal -->
<div id="part_link_confirm" class="modal fade">
    <div class="modal-dialog modal-center">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">登録確認</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 border-dot">
                        <div class="col-lg-3 text-center">
                            <h4>選択したUGC</h4>
                        </div>
                        <div class="col-lg-1">
                            <h4>@{{selectedImages.length}}</h4>
                        </div>

                        <div class="col-lg-8 testimonial-group-2">
                            <div class="row">
                                <div class="col-xs-2" v-for="image in selectedImages">
                                    <img alt="64x64" class="media-object width-ratio-100" style="height: 64px"
                                         :src="image.image_url"
                                         data-holder-rendered="true">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt10 border-dot" v-if="seletedPulishedPart()">
                    <div class="col-md-12 mb20">
                        <div class="col-lg-3 text-center">
                            <h4>登録先UGCセット</h4>
                        </div>
                        <div class="col-lg-9">
                            <div class="col-md-12" v-for="part in selectedParts" v-if="part.status == {{\Classes\Parts\Field\StatusField::STATUS_NORMAL}}">
                                <h4><b>@{{ part.title }}</b></h4>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-12 mb20">
                        <div class="col-lg-3 text-center">
                            <h4>ステータス</h4>
                        </div>
                        <div class="col-lg-9">
                            <div class="col-md-12">
                                <h4 style="margin-top: 0"><b>公開中</b></h4>
                            </div>
                        </div>

                    </div>
                    <div class="row text-center">
                        <button type="button" id="create_hashtag_btn" data-dismiss="modal" class="btn btn-cancel mt20">キャンセル</button>
                        <button type="button" id="create_hashtag_btn" class="btn btn-danger mt20" :class="{disabled: isSubmittedImagePart}" v-on:click="registerImage(true)">
                            反映
                        </button>
                    </div>
                </div>

                <div class="row mt10 mb10" v-if="selectedUnPublishedPart()">
                    <div class="col-md-12 mb20">
                        <div class="col-lg-3 text-center">
                            <h4>登録先UGCセット</h4>
                        </div>
                        <div class="col-lg-9">
                            <div class="col-md-12" v-for="part in selectedParts" v-if="part.status != {{\Classes\Parts\Field\StatusField::STATUS_NORMAL}}">
                                <h4><b>@{{ part.title }}</b></h4>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-12 mb20">
                        <div class="col-lg-3 text-center">
                            <h4>ステータス</h4>
                        </div>
                        <div class="col-lg-9">
                            <div class="col-md-12">
                                <h4><b>未公開</b></h4>
                            </div>
                        </div>

                    </div>
                    <div class="row text-center">
                        <button type="button" id="create_hashtag_btn" data-dismiss="modal" class="btn btn-cancel mt20">キャンセル</button>
                        <button type="button" id="create_hashtag_btn" class="btn btn-danger mt20" v-on:click="registerImage(false)">
                            保存してUGCセット詳細へ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push("push-script")
    <script>
        var apiRegisterImage = '{{URL::route('register_images_parts')}}',
            partDetailUrl = '{{URL::route('part_detail', ['id' => ''])}}',
            partListUrl = '{{URL::route('part_list')}}';
    </script>
@endpush