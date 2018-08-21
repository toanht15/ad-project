<script type="text/x-template" id="create_part_template">
<!-- modal -->
<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="create_part_modal">
    <div class="modal-dialog">
        <div class="modal-content p10">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">UGCセット作成</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form action="" method="POST" id="create_part_form" v-on:submit.prevent>
                        <div class="form-group col-md-12 mt20">
                            <div class="col-md-3 pl40">
                                <b>UGCセットの名前</b>
                            </div>
                            <div class="col-md-9">
                                <input type="text" class="form-control bordered" name="title" v-model="title" required="required" maxlength="30">
                            </div>
                        </div>
                        <div class="form-group col-md-12 mt20">
                            <div class="col-md-3 pl40">
                                <b>UGCセットのタイプ</b>
                            </div>
                        </div>
                        <div class="form-group col-md-12 mt20">
                            <div class="col-md-6">
                                <div class="col-md-11 part-type part-type-select"
                                     :class="{selected: template == {{\Classes\Parts\Field\TemplateField::TYPE_SLIDER}} }"
                                     @click="template = {{\Classes\Parts\Field\TemplateField::TYPE_SLIDER}}">
                                    <div class="col-md-12">
                                        <div class="col-md-6 no-padding"><h3>スライダー</h3></div>
                                        <div class="col-md-6 text-right no-padding">
                                            <div class="custom-checkbox">
                                                <input type="checkbox" id="create_slider_part" :checked="template == {{\Classes\Parts\Field\TemplateField::TYPE_SLIDER}}"/>
                                                <label for="create_slider_part"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-11">横にスライドするUIでUGCを表示します。<br>コメントの表示・非表示を選べます。</div>
                                    <div class="col-md-12 media-part-create slider-part">
                                        <div class="col-md-1 thumbnail">
                                            <span class="glyphicon glyphicon-menu-left" aria-hidden="true"></span>
                                        </div>
                                        @for($i = 1; $i < 6; $i++)
                                            <div class="col-md-2 thumbnail">
                                                <img src="{{static_file_version('/images/dummy.jpg')}}">
                                            </div>
                                        @endfor
                                        <div class="col-md-1 thumbnail">
                                            <span class="glyphicon glyphicon-menu-right" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="col-md-11 part-type part-type-select"
                                     :class="{selected: template == {{\Classes\Parts\Field\TemplateField::TYPE_MEDIA}} }"
                                     @click="template = {{\Classes\Parts\Field\TemplateField::TYPE_MEDIA}}">
                                    <div class="col-md-12">
                                        <div class="col-md-6 no-padding"><h3>一覧表示</h3></div>
                                        <div class="col-md-6 text-right no-padding">
                                            <div class="custom-checkbox">
                                                <input type="checkbox" id="create_media_part" :checked="template == {{\Classes\Parts\Field\TemplateField::TYPE_MEDIA}}"/>
                                                <label for="create_media_part"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">タイル状に多くのUGCを表示します。</div>
                                    <div class="col-md-12 media-part-create">
                                        @for($i = 1; $i < 18; $i++)
                                        <div class="col-md-2 thumbnail">
                                            <img src="{{static_file_version('/images/dummy.jpg')}}">
                                        </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- /.modal -->

            <div class="text-center mt20">
                <button type="button" @click="createPart()" class="btn btn-danger">作成する</button>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
</script>

@push("push-script-before-main-script")
    <script>
        var apiCreatePart = '{{URL::route('api_part_create')}}'
    </script>
    <script src="{{static_file_version('/js/custom/part/partCreate.js')}}"></script>
@endpush