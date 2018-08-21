<!-- modal -->
<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="upload_tw_modal">
    <div class="modal-dialog">
        <div class="modal-content p10">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">Twitterと同期</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form action="{{$formAction}}" id="upload_tw_form" method="POST">
                        {{csrf_field()}}
                        <input type="hidden" name="image_id" v-model="currentImage.id">
                        <input type="hidden" name="media_account_id" v-model="mediaAccount.id">
                        <div class="form-group col-md-12">
                            <div class="col-md-3">
                                <b>クリエイティブ・タイプ:</b>
                            </div>
                            <div class="col-md-6">
                                <select class="form-control" id="tw_creative_type" name="creative_type">
                                    @if ($fileFormat == \App\Models\Post::VIDEO)
                                        @foreach(\Classes\Constants::$twitterVideoCreativeType as $key => $value)
                                            <option value="{{$key}}">{{$value}}</option>
                                        @endforeach
                                    @else
                                        @foreach(\Classes\Constants::$twitterImageCreativeType as $key => $value)
                                            <option value="{{$key}}">{{$value}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-md-12 mt20">
                            <div class="col-md-3">
                                <b>広告の内容:</b>
                            </div>
                            <div class="col-md-6">
                                <div class="col-md-12 pd0" style="height: 40px">
                                    <img src="{{static_file_version('/images/default_profile.png')}}" width="40" height="40"> @{{ mediaAccount.name }}
                                </div>
                                <div class="col-md-12 text-right pd0 note">
                                    @{{ tweet.length }} / 140
                                </div>
                                <div class="col-md-12 pd0 gray-border">
                                    <textarea onkeyup="app.textAreaAdjust(this, 80)" minlength="1" v-model="tweet" name="tweet" required="required" placeholder="テキストを入力"></textarea>
                                    <div class="col-md-12 text-center pd0 black-background">
                                        <img v-bind:src="currentImage.url">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- /.modal -->

            <div class="text-center mt20">
                <button type="button" class="btn btn-cancel" data-dismiss="modal">キャンセル</button>
                <button type="submit" form="upload_tw_form" class="btn btn-success"><i class="fa fa-upload" aria-hidden="true"></i>同期する</button>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->