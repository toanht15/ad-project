<script type="text/x-template" id="part_detail_setting_template">
<!-- modal -->
<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="part_detail_setting_modal">
    <div class="modal-dialog">
        <div class="modal-content p10">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">UGCセット編集</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="" role="tabpanel" data-example-id="togglable-tabs">
                            <ul id="myTab" class="nav nav-tabs nav-tabsLetro" role="tablist">
                                <li role="presentation" class="active"><a href="#tab_content1" v-on:click="submitUrlType = 0" id="part-basic-setting" role="tab" data-toggle="tab" aria-expanded="true">表示設定</a>
                                </li>
                                <li role="presentation" class=""><a href="#tab_content2" v-on:click="submitUrlType = 1" role="tab" id="part-design-01" data-toggle="tab" aria-expanded="false">デザイン：UGCセット</a>
                                </li>
                                <li role="presentation" class=""><a href="#tab_content3" v-on:click="submitUrlType = 1" role="tab" id="part-design-02" data-toggle="tab" aria-expanded="false">デザイン：拡大表示</a>
                                </li>
                            </ul>
                            <div id="myTabContent" class="tab-content" v-if="part != null">
                                <div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="part-basic-setting">
                                    <form action="" method="POST" id="part_basic_setting_form">
                                    <div class="form-group col-md-12 mt20">
                                        <div class="col-md-12">
                                            <label>UGCセット名</label>
                                        </div>
                                        <div class="col-md-5 ml10">
                                            <input class="form-control bordered" type="text" name="title" v-model="part.title" placeholder="UGCセット001（スライダー）">
                                        </div>

                                        <div class="col-md-12 mt20 mln10">
                                            <div class="col-md-5"><label>表示ページのURLパターン</label></div>
                                            <div class="col-lg-4 text-right">
                                                <div class="radio-inline">
                                                    <input type="radio" class="custom-radio" id="part_url_type_01" v-model="part.url_match_type" value="{{\Classes\Parts\Field\UrlMatchTypeField::URL_MATCH_TYPE_PARTIAL}}" name="url_match_type">
                                                    <label for="part_url_type_01" class="text-normal">{{\Classes\Parts\Field\UrlMatchTypeField::$urlMatchTypeList[\Classes\Parts\Field\UrlMatchTypeField::URL_MATCH_TYPE_PARTIAL]}}</label>
                                                </div>
                                                <div class="radio-inline">
                                                    <input type="radio" class="custom-radio" id="part_url_type_02" v-model="part.url_match_type" name="url_match_type" value="{{\Classes\Parts\Field\UrlMatchTypeField::URL_MATCH_TYPE_REGULAR_EXPRESSION}}">
                                                    <label for="part_url_type_02" class="text-normal">{{\Classes\Parts\Field\UrlMatchTypeField::$urlMatchTypeList[\Classes\Parts\Field\UrlMatchTypeField::URL_MATCH_TYPE_REGULAR_EXPRESSION]}}</label>
                                                </div>
                                                <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="正規表現は、ある文字の並び（文字列）を表現する一つの方式です。記述方法には専門的な知識が必要になりますので、ご利用にあたっては十分にご注意ください。" aria-hidden="true"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-9 ml10">
                                            <input class="form-control bordered" type="text" name="url_match_string" v-model="part.url_match_string" placeholder="" maxlength="100">
                                        </div>
                                        <div class="col-md-12 ml10">
                                            <label class="text-normal"><input type="checkbox" v-model="useUrlExcludeString"> 除外URLパターン設定</label>
                                        </div>
                                        <transition name="fade">
                                            <div class="col-md-9 text-right mln10" v-if="useUrlExcludeString">
                                                <div class="radio-inline">
                                                    <input type="radio" class="custom-radio" id="exclude_url_radio01" v-model="part.url_exclude_type" name="url_exclude_type" value="{{\Classes\Parts\Field\UrlMatchTypeField::URL_MATCH_TYPE_PARTIAL}}" checked>
                                                    <label class="text-normal" for="exclude_url_radio01">{{\Classes\Parts\Field\UrlMatchTypeField::$urlMatchTypeList[\Classes\Parts\Field\UrlMatchTypeField::URL_MATCH_TYPE_PARTIAL]}}</label>
                                                </div>
                                                <div class="radio-inline">
                                                    <input type="radio" class="custom-radio" id="exclude_url_radio02" v-model="part.url_exclude_type" name="url_exclude_type" value="{{\Classes\Parts\Field\UrlMatchTypeField::URL_MATCH_TYPE_REGULAR_EXPRESSION}}">
                                                    <label class="text-normal" for="exclude_url_radio02">{{\Classes\Parts\Field\UrlMatchTypeField::$urlMatchTypeList[\Classes\Parts\Field\UrlMatchTypeField::URL_MATCH_TYPE_REGULAR_EXPRESSION]}}</label>
                                                </div>
                                            </div>
                                        </transition>
                                        <transition name="fade">
                                            <div class="col-md-9 ml10" v-if="useUrlExcludeString">
                                                <input class="form-control bordered" type="text" name="url_exclude_string" v-model="part.url_exclude_string" placeholder="" maxlength="100">
                                            </div>
                                        </transition>

                                        <div class="col-md-12 mt20 mln10">
                                            <div class="col-md-12"><label>UGCセット表示位置</label></div>
                                            <div class="col-md-12">
                                                <div class="col-md-6">CSSパスで設定した要素の後ろにUGCセットを表示します。</div>
                                                <div class="col-md-3 text-right">CSSパスの調べ方
                                                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip" title="ブラウザのデベロッパーツールでページの該当箇所を選択し、「Copy」→「Copy selector」(※Google Chrome)や、「コピー」→「CSSセレクター」(※Firefox)でコピーできます。そのままペーストしてください。"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-9 ml10">
                                            <input class="form-control bordered" type="text" name="replace_container_css_selector" v-model="part.replace_container_css_selector" placeholder="">
                                        </div>

                                        <div class="col-md-12 mt20">
                                            <label>UGCの並び順</label>
                                        </div>
                                        <div class="col-md-8 ml10">
                                            @foreach(\Classes\Parts\Field\SortField::$options as $key => $value)
                                                <div class="radio-inline" @if($key === \Classes\Parts\Field\SortField::SORT_BY_POST_PUBLISH_ORDER)
                                                v-if="part.template == '{{\Classes\Parts\Field\TemplateField::TYPE_MEDIA}}'" @endif>
                                                    <input type="radio" class="custom-radio" name="sort" v-model="part.sort" value="{{$key}}" id="part_type_{{$key}}">
                                                    <label class="text-normal" for="part_type_{{$key}}">{{$value}}</label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="col-md-12 mt20">
                                            <label>表示期間</label>
                                        </div>
                                        <div class="col-md-9 ml10">
                                            <div class="col-md-4">
                                                <input type="text" value="2012/06/15 14:45" name="start_at_date"  v-model="part.start_at_date" readonly class="form-control bordered form_datetime">
                                            </div>
                                            <div class="col-md-1 text-center mt10">〜</div>
                                            <div class="col-md-4">
                                                <input type="text" value="2012/06/15 14:45" name="close_at_date"  v-model="part.close_at_date" readonly class="form-control bordered form_datetime">
                                                <label class="text-normal"><input type="checkbox" name="close_timing_type" v-model="part.close_timing_type"> 契約完了日時と合わせる</label>
                                            </div>
                                        </div>
                                        @if(Session::get('site') && Session::get('site')->ab_test)
                                        <div class="col-md-12 mt20">
                                            <label>ABテスト</label>
                                        </div>
                                        <div class="col-md-9 ml10">
                                            <div class="col-md-12">
                                                <span>UGCセットの表示をユーザーごとで出し分け、比較します</span>
                                            </div>
                                            <div class="col-md-12 ml10">
                                                <div class="radio-inline">
                                                    <input type="radio" class="custom-radio" id="abtest_true" v-model="part.abtest_flg" name="abtest_flg" value="1" checked>
                                                    <label class="text-normal" for="abtest_true">する</label>
                                                </div>
                                                <div class="radio-inline">
                                                    <input type="radio" class="custom-radio" id="abtest_false" v-model="part.abtest_flg" name="abtest_flg" value="0">
                                                    <label class="text-normal" for="abtest_false">しない</label>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    </form>
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="tab_content2" v-if="part.template == '{{\Classes\Parts\Field\TemplateField::TYPE_MEDIA}}' && partDesign != null" aria-labelledby="part-design-01">
                                    <form action="" method="POST" id="part_design_setting_form">
                                    <div class="form-group col-md-5 mt20">
                                        <div class="col-md-12">
                                            <label>コメント</label>
                                        </div>
                                        <div class="col-md-5 ml10">
                                            <div class="custom-switch" v-on:click="shouldMergePart = true">
                                                <input type="checkbox" name="show_text_flg" v-model="part.show_text_flg" id="show_text_media_ip_id">
                                                <label for="show_text_media_ip_id" class="custom-switch-label">
                                                    <span class="inner"></span>
                                                    <span class="switch"></span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <label>見出しテキスト</label>
                                        </div>
                                        <div class="col-md-10 ml10">
                                            <input class="form-control bordered" type="text" v-model="partDesign['3_5']" placeholder="">
                                        </div>

                                        <div class="col-md-12 mt30">
                                            <label>１行の画像枚数</label>
                                        </div>
                                        <div class="col-md-12 ">
                                            <div class="col-md-2 mt10 text-center">PC</div>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control bordered" placeholder="10" v-model="partDesign['3_1']" max="50" min="0">
                                            </div>
                                            <div class="col-md-1 mln10 mt10">
                                                <span>枚</span>
                                            </div>

                                            <div class="col-md-2 mt10 text-right">余白</div>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control bordered" placeholder="10" v-model="partDesign['3_2']" max="50" min="0">
                                            </div>
                                            <div class="col-md-1 mln10 mt10">
                                                <span>%</span>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt10">
                                            <div class="col-md-2 mt10 text-center no-padding">スマホ</div>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control bordered" placeholder="10" v-model="partDesign['3_6']" max="50" min="0">
                                            </div>
                                            <div class="col-md-1 mln10 mt10">
                                                <span>枚</span>
                                            </div>

                                            <div class="col-md-2 mt10 text-right">余白</div>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control bordered" placeholder="10" v-model="partDesign['3_7']" max="50" min="0">
                                            </div>
                                            <div class="col-md-1 mln10 mt10">
                                                <span>%</span>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mt30">
                                            <label>一度に表示する枚数</label>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="col-md-2 mt10 text-center">PC</div>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control bordered" placeholder="10" v-on:change="shouldMergePart = true" v-model="part.item_per_page_pc" max="5000" min="0">
                                            </div>
                                            <div class="col-md-1 mln10 mt10">
                                                <span>枚</span>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt10">
                                            <div class="col-md-2 mt10 text-center no-padding">スマホ</div>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control bordered" placeholder="10" v-on:change="shouldMergePart = true" v-model="part.item_per_page_sp" max="5000" min="0">
                                            </div>
                                            <div class="col-md-1 mln10 mt10">
                                                <span>枚</span>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mt30">
                                            <label>「もっと見る」ボタンの文言</label>
                                        </div>
                                        <div class="col-md-10 ml10">
                                            <input class="form-control bordered" type="text" v-model="partDesign['3_4']" name="" placeholder="もっと見る">
                                        </div>
                                        <div class="col-md-12 ml10">
                                            <label class="text-normal"><input type="checkbox" v-model="useShowNextButtonCss"> デザインをカスタマイズする（CSS）</label>
                                        </div>
                                        <transition name="fade">
                                            <div class="col-md-10 ml10" v-if="useShowNextButtonCss">
                                                <textarea class="form-control bordered" v-model="partDesign['3_3']" name="" placeholder=""></textarea>
                                            </div>
                                        </transition>
                                    </div>

                                    <div class="col-md-7">
                                        <div class="col-md-12 media-preview no-padding">
                                            <div class="col-md-12 h31"></div>
                                            <div class="col-md-12 text-center"> @{{ partDesign['3_5'] }} </div>
                                            <div class="col-md-12 part-type mt10 ">
                                                <div v-for="index in parseInt(part.item_per_page_pc)">
                                                    <div v-if="index%partDesign['3_1'] == 1" style="float: left" v-bind:style="{ width: imageSizePc + 'px', marginBottom: spaceSizePc + 'px' }">
                                                        <img v-if="!part.show_text_flg" src="/images/dummy_preview.png" class="width-ratio-100">
                                                        <img v-if="part.show_text_flg" src="/images/media_text_preview.jpg" class="width-ratio-100">
                                                    </div>
                                                    <div v-else style="float: left; display: block; overflow: auto" v-bind:style="{ width: imageSizePc + 'px', marginLeft: spaceSizePc + 'px', marginBottom: spaceSizePc + 'px' }">
                                                        <img v-if="!part.show_text_flg" src="/images/dummy_preview.png" class="width-ratio-100">
                                                        <img v-if="part.show_text_flg" src="/images/media_text_preview.jpg" class="width-ratio-100">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 text-center mt10">
                                                <button type="button" class="btn btn-default btn-circle btn-xl">@{{ partDesign['3_4'] }}</button>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt30 text-right">
                                            <small>※プレビューはPC表示のイメージです。</small>
                                        </div>
                                    </div>
                                    </form>
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="tab_content2" v-if="part.template == '{{\Classes\Parts\Field\TemplateField::TYPE_SLIDER}}' && partDesign != null " aria-labelledby="part-design-01">
                                    <form action="" method="POST" id="part_design_setting_form">
                                    <div class="form-group col-md-5 mt20">
                                        <div class="col-md-12">
                                            <label>コメント</label>
                                        </div>
                                        <div class="col-md-5 ml10">
                                            <div class="custom-switch" v-on:click="shouldMergePart = true">
                                                <input type="checkbox" name="show_text_flg" v-model="part.show_text_flg" id="show_text_ip_id">
                                                <label for="show_text_ip_id" class="custom-switch-label">
                                                    <span class="inner"></span>
                                                    <span class="switch"></span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mt10">
                                            <label>見出しテキスト</label>
                                        </div>
                                        <div class="col-md-10 ml10">
                                            <input :disabled="part.show_text_flg || part.show_text_flg != 0" class="form-control bordered" type="text" v-model="partDesign['2_3']" name="" placeholder="見出しテキスト">
                                        </div>

                                        <div class="col-md-12 mt20">
                                            <label>画像サイズ</label>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="col-md-4">
                                                <input type="number" :disabled="part.show_text_flg || part.show_text_flg !=0" class="form-control bordered" placeholder="100" v-model="part.height" max="400" min="0">
                                            </div>
                                            <div class="col-md-3 mln10 mt10">
                                                <span>px</span>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mt20">
                                            <label>画像間の余白</label>
                                        </div>
                                        <div class="col-md-12 ">
                                            <div class="col-md-4">
                                                <input type="number" :disabled="part.show_text_flg || part.show_text_flg !=0" class="form-control bordered" placeholder="10" v-model="partDesign['2_2']" max="50" min="0">
                                            </div>
                                            <div class="col-md-3 mln10 mt10">
                                                <span>px</span>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mt20">
                                            <label>矢印の色</label>
                                        </div>
                                        <div class="col-md-12 ml10">
                                            <div class="col-md-5 input-group color-picker">
                                                <input type="text" value="" :disabled="part.show_text_flg || part.show_text_flg !=0" name="arrow_color" id="arrow-color" v-model="partDesign['2_1']" class="form-control bordered"/>
                                                <span class="input-group-addon"><i></i></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-7 media-preview">
                                        <div class="col-md-12" style="height: 60px"></div>
                                        <div class="col-md-12 text-center" v-if="!part.show_text_flg">@{{ partDesign['2_3'] }}</div>
                                        <div class="col-md-12 part-type mt10 ">
                                            <div class="col-md-1">
                                                <span v-if="!part.show_text_flg" class="glyphicon glyphicon-menu-left" v-bind:style="{ marginTop: part.height/2 - 14 + 'px', color: partDesign['2_1'] }" aria-hidden="true"></span>
                                            </div>
                                            <div class="col-md-10 mln10 overflow_hidden" :class="{'overflow_hidden': !part.show_text_flg, 'overflow_scroll': part.show_text_flg}">
                                                <div style="width: 1500px" v-if="!part.show_text_flg">
                                                    <div style="float: left" v-bind:style="{ width: part.height + 'px' }">
                                                        <img src="/images/dummy_preview.png" class="width-ratio-100">
                                                    </div>
                                                    @for($i=1; $i<4; $i++)
                                                        <div style="float: left" v-bind:style="{ width: part.height + 'px', marginLeft: partDesign['2_2'] + 'px' }">
                                                            <img src="/images/dummy_preview.png" class="width-ratio-100">
                                                        </div>
                                                    @endfor
                                                </div>
                                                <div style="width: 1500px" v-else>
                                                    @for($i=1; $i<6; $i++)
                                                        <div style="float: left; width: 150px; margin-left: 10px;">
                                                            <img src="/images/part_with_text.png" class="width-ratio-100">
                                                        </div>
                                                    @endfor
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <span v-if="!part.show_text_flg" class="glyphicon glyphicon-menu-right" v-bind:style="{ marginTop: part.height/2 - 14 + 'px', color: partDesign['2_1'] }" aria-hidden="true"></span>
                                            </div>
                                        </div>
                                    </div>
                                    </form>
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="tab_content3" aria-labelledby="part-design-02" v-if="partDesign != null">
                                    <form action="" method="POST" id="part_modal_setting_form">
                                    <div class="form-group col-md-6 mt40">
                                        <div class="col-md-12">
                                            <label>下地の背景</label>
                                        </div>
                                        <div class="col-md-12 mt10">
                                            <div class="col-md-2 text-center">色</div>
                                            <div class="col-md-4 mln10">
                                                <div class="radio-inline">
                                                    <input type="radio" class="custom-radio" value="#000000" id="bg_black_radio" name="bgr_color" v-model="partDesign['0_1']">
                                                    <label class="text-normal" for="bg_black_radio">黒<div class="black-box"></div></label>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="radio-inline">
                                                    <input type="radio" class="custom-radio" value="#ffffff" id="bg_white_radio" name="bgr_color" v-model="partDesign['0_1']">
                                                    <label class="ml10 text-normal" for="bg_white_radio">白<div class="white-box"></div></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt10">
                                            <div class="col-md-2 mt10 text-center no-padding">透明度</div>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control bordered" placeholder="50" v-model="partDesign['0_9']" min="0" max="100">
                                            </div>
                                            <div class="col-md-1 mln10 mt10">
                                                <span>%</span>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mt30">
                                            <label>関連する商品ページリンクの見出し</label>
                                        </div>
                                        <div class="col-md-10 ml10">
                                            <input class="form-control bordered" type="text" v-model="partDesign['0_4']" placeholder="この画像に関するページ">
                                        </div>
                                    </div>

                                    <div class="col-md-6 mt20">
                                        <div class="vtdr-modal-preview">
                                            <div class="preview-background" v-bind:style="{ backgroundColor: partDesign['0_1'], opacity: partDesign['0_9']/100 }">
                                            </div>
                                            <div class="modal-preview">
                                                <div class="text-center">
                                                    <span class="glyphicon glyphicon-menu-left" aria-hidden="true"></span>
                                                </div>
                                                <div class="modal-content-preview" style="font-size: 10px">
                                                    <div class="col-md-5 mt10">
                                                        <img src="/images/dummy_insta.jpg?v=1" class="width-ratio-100">
                                                    </div>
                                                    <div class="col-md-7 mln10 mt10">
                                                        <div class="col-md-12 mln10">
                                                            <label>User Account Name</label>
                                                        </div>
                                                        <div class="mt10"><hr style="margin-bottom: 10px"></div>
                                                        <div class="col-md-12 mln10">
                                                            <small>テキストテキストテキストテキストテキストテキストテキストテ</small>
                                                        </div>
                                                        <div class="col-md-12 mln10 mt10">
                                                            <span>@{{ partDesign['0_4'] }}</span>
                                                        </div>

                                                        <div class="col-md-12 div-border-gray no-padding">
                                                            <div style="margin-left: 5px" class="img">
                                                                <img src="/images/dummy.jpg" class="width-ratio-100">
                                                                <span>テキストテキストテキ</span>
                                                            </div>
                                                            <div class="img">
                                                                <img src="/images/dummy.jpg" class="width-ratio-100">
                                                                <span>テキストテキストテキ</span>
                                                            </div>
                                                            <div class="img">
                                                                <img src="/images/dummy.jpg" class="width-ratio-100">
                                                                <span>テキストテキストテキ</span>
                                                            </div>
                                                        </div>
                                                        <div class="text-right mt20" style="width: 180px; float: left">
                                                            <p>テキストテキストテキストテキストテ</p>
                                                            <p>テキストテキストテ</p>
                                                            <p>テキストテ</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <span class="glyphicon glyphicon-menu-right" aria-hidden="true"></span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
            <!-- /.modal -->

            <div class="text-center mt20">
                <button v-on:click="saveSetting()" class="btn btn-danger w150">保存</button>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
</script>

@push("push-header")
    <link rel="stylesheet" href="{{static_file_version('/css/icheck/flat/grey.css')}}">
    <link rel="stylesheet" href="{{static_file_version('/bower_components/smalot-bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')}}">
    <link rel="stylesheet" href="{{static_file_version('/bower_components/bootstrap-toggle/css/bootstrap-toggle.min.css')}}">
    <link rel="stylesheet" href="{{static_file_version('/css/colorpicker/bootstrap-colorpicker.min.css')}}">
@endpush

@push("push-script-before-main-script")
    <script src="{{static_file_version('/bower_components/bootstrap-toggle/js/bootstrap-toggle.min.js')}}"></script>
    <script src="{{static_file_version('/bower_components/smalot-bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js')}}"></script>
    <script src="{{static_file_version('/js/icheck/icheck.min.js')}}"></script>
    <script src="{{static_file_version('/js/colorpicker/bootstrap-colorpicker.min.js')}}"></script>
    <script>
        var apiPartBasicSettingUrl = '{{URL::route('api_get_part_basic_setting', ['id' => 'partId'])}}',
            apiPartDesignSettingUrl = '{{URL::route('api_get_part_design_setting', ['id' => 'partId'])}}',
            apiSubmitUrl = [
                '{{URL::route('api_update_basic_setting', ['id' => 'partId'])}}',
                '{{URL::route('api_update_part_design', ['id' => 'partId'])}}'
            ];
    </script>
    <script src="{{static_file_version('/js/custom/part/partDetailSetting.js')}}"></script>
@endpush