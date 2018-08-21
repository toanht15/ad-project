<script type="text/x-template" id="publish_part_modal_template">
    <div id="publish_part_modal" class="modal fade">
        <div class="modal-dialog modal-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title ">公開確認</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-2">
                            <h4 class="">UGC</h4>
                        </div>
                        <div class="col-lg-1">
                            <h4 class=""><b>@{{part.ugcs.length}}</b></h4>
                        </div>

                        <div class="col-lg-8 testimonial-group-2">
                            <div class="row">
                                <div class="col-xs-2" v-for="ugc in part.ugcs">
                                    <img alt="64x64" class="media-object" style="width: 64px; height: 64px;"
                                         v-bind:src="ugc.img_url">
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-lg-2">
                            <h4 class="">UGCセット</h4>
                        </div>
                        <div class="col-lg-9">
                            <h4 class="text-left "><b>@{{part.title}}</b></h4>
                        </div>

                    </div>
                    <div class="row text-center">
                        <button type="button" id="create_hashtag_btn" data-dismiss="modal"
                                class="btn mt20 ">キャンセル
                        </button>
                        <button type="button" id="create_hashtag_btn" v-on:click="publish" class="btn btn-danger mt20 ">
                            公開する
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>