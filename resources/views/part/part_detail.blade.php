@extends('layouts.master')

@section('title')
    UGCセット詳細
@stop

@section('header')
    <link rel="stylesheet" href="{{static_file_version('/css/app/part_detail.css')}}">
@stop

@section('content')
    <div class="page-title">
        <div class="title_left">
            <h3>UGCセット詳細</h3>
        </div>
    </div>
    <div class="row" id="app2">
        <div class="col-xs-12" v-cloak>

            <div class="x_panel">
                <div class="row">
                    <div class="col-md-9 pl20"><h3>@{{ part.title }}<span class="label label-publish p5 status ml10">@{{part.status_str}}</span>
                        </h3>
                    </div>
                    <div class="col-md-3 text-right">
                        <div type="button" data-toggle="modal" data-target="#ugc_select_modal" class="btn mt15"><span
                                    aria-hidden="true" class="glyphicon glyphicon-picture fa-lg"></span><br>
                            <small>UGC追加</small>
                        </div>


                        <div type="button" onclick="partDetail.openDetailModal({{$part->id}})" class="btn mt15"><span
                                    aria-hidden="true" class="glyphicon glyphicon-pencil fa-lg"></span><br>
                            <small>編集</small>
                        </div>
                        <div type="button" data-toggle="modal" data-target="#preview_modal" class="btn mt15"><span
                                    aria-hidden="true" class="glyphicon glyphicon-new-window fa-lg"></span><br>
                            <small>プレビュー</small>
                        </div>
                        <div v-if="part.status == 3" type="button" class="btn btn-danger mt15" data-toggle="modal"
                             data-target="#publish_part_modal">
                            公開
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2 pl20">
                        <h5>タイプ： <b>@{{part.template_str}}</b></h5>
                    </div>

                    <div class="col-md-5">
                        <h5>表示期間： <b>@{{ part.display_start_date}} ~ @{{part.display_close_date}}</b></h5>
                    </div>

                    <div class="col-md-5 text-right">
                        <div class="input-group dashboard-date-input" style="margin-right:10px;">
                            <span class="add-on input-group-addon"><i class="fa fa-calendar"></i></span>
                            <daterangepicker v-bind:value="dateRange"
                                             v-bind:default_start_date="start_date"
                                             v-bind:default_finish_date="finish_date"
                                             v-on:update="updateDateRange"></daterangepicker>
                        </div>
                    </div>
                </div>
                <part v-bind:part="part"></part>
            </div>
        </div>

        <select_ugc_modal v-bind:search_conditions="search_conditions"
                          v-bind:existed_items="existed_items"
                          v-on:ugc_register="update_image"
                          v-bind:part="part"></select_ugc_modal>

        <publish_part_modal v-bind:part="part" v-on:publish="update_part"></publish_part_modal>
        <part_detail_setting_template :partid="part.id" :loadnow="true" :redirect="'{{URL::route('part_detail', ['id' => $part->id])}}'" :status="{{$part->status}}"></part_detail_setting_template>
    </div>

    <div id="preview_modal" class="modal fade">
        <div class="modal-dialog modal-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title ">UGCセットプレビュー</h4>
                </div>
                <div class="modal-body text-center">
                    <div class="row alignleft">
                        <h4>表示を確認するページのURLを入力してください</h4>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="preview_url">
                    </div>
                    <div class="row">
                        <button type="button" id="preview_button" class="btn btn-danger mt20 ">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include("templates.part.part_detail_setting")


@stop


@push("push-script")
    <script>
        var cv_setting = '{{URL::route('site_list')}}';
        var part_list_api = '{{URL::route('api_part_list')}}';
        var part_detail = '{{URL::route('api_part_detail', '%s')}}';
        var part_kpi = '{{URL::route('part_kpi', '%s')}}';
        var web_part_detail = '{{URL::route('part_detail', '%s')}}';
        var start_date = '{{(new DateTime($dateStart))->format('Y/m/d')}}';
        var finish_date = '{{(new DateTime($dateStop))->format('Y/m/d')}}';
        if (readCookie('start_date') && readCookie('finish_date')) {
            start_date = readCookie('start_date');
            finish_date = readCookie('finish_date');
        }
        var part_data = {!! json_encode($part->deserialize()) !!};
        var searchConditionList = {!! json_encode($searchConditionList->toArray()) !!};
        var api_get_images = '{{ URL::route('api_get_images') }}';
        var apiRegisterImage = '{{URL::route('register_images_parts')}}';
        var part_image_delete = '{{URL::route('cancel_part')}}';
        var api_part_update_sort_value = '{{URL::route('api_part_update_sort_value', '%s')}}';
        var api_publish_part = '{{URL::route('api_publish_part', '%s')}}';
        var post_detail_url = '{{URL::route('post_detail', '%s')}}';
        var shouldOpenEditModal = {{Request::get('public_setting') ? 1 : 0}}

        $('#preview_button').click(function (e) {
            var url = $('#preview_url').val();
            if (url.indexOf('?') != -1) {
                url += '&vtdr_preview_parts_id={{$part->id}}';
            } else {
                url += '?vtdr_preview_parts_id={{$part->id}}';
            }
            window.open(url);
        })
    </script>

    <script type="text/x-template" id="part_template">
        <div class="row" style="padding: 0px 10px;">
            <div class="col-lg-12">
                <div class="jumbotron">

                    <template v-if="! part.has_ab_test">
                        <div class="row" style="width: 900px;margin: auto;">
                            <div class="row">
                                <div class="col-sm-2">Impression</div>
                                <div class="col-sm-2">Inview</div>
                                <div class="col-sm-2">Click</div>
                                <div class="col-sm-2">CTR</div>
                                <div class="col-sm-2">CV</div>
                                <div class="col-sm-2">CVR</div>
                            </div>
                            <div class="row kpiResult">
                                <div class="col-sm-2"><h4>@{{ part.number_format(part.impression) }}</h4></div>
                                <div class="col-sm-2"><h4>@{{ part.number_format(part.inview) }}</h4></div>
                                <div class="col-sm-2"><h4>@{{ part.number_format(part.click) }}</h4></div>
                                <div class="col-sm-2"><h4>@{{ part.ctr }} %</h4></div>
                                <div class="col-sm-2"><h4>@{{ part.number_format(part.cv) }}</h4></div>
                                <div class="col-sm-2"><h4>@{{ part.cvr }} %</h4></div>
                            </div>
                        </div>
                    </template>


                    <template v-else>
                        <div class="row kpiResult" style="width: 900px;margin: auto;">
                            <div class="row">
                                <div class="col-sm-2 col-sm-offset-1"></div>
                                <div class="col-sm-1"></div>
                                <div class="col-sm-1">Impression</div>
                                <div class="col-sm-1">Inview</div>
                                <div class="col-sm-1">Click</div>
                                <div class="col-sm-1">CTR</div>
                                <div class="col-sm-1">CV</div>
                                <div class="col-sm-1">CVR</div>
                            </div>
                            <div class="row" style="background: white;">
                                <div class="col-sm-2 col-sm-offset-1"><h5>A/Bテスト結果</h5></div>
                                <div class="col-sm-1"><h5>表示あり</h5></div>
                                <div class="col-sm-1"><h5>@{{ part.number_format(part.impression) }}</h5></div>
                                <div class="col-sm-1"><h5>@{{ part.number_format(part.inview) }}</h5></div>
                                <div class="col-sm-1"><h5>@{{ part.number_format(part.click) }}</h5></div>
                                <div class="col-sm-1"><h5>@{{ part.ctr }} %</h5></div>
                                <div class="col-sm-1"><h5>@{{ part.number_format(part.cv) }}</h5></div>
                                <div class="col-sm-1"><h5>@{{ part.cvr }} %</h5></div>
                            </div>
                            <div class="row" style="background: white;">
                                <div class="col-sm-2 col-sm-offset-1"><h5></h5></div>
                                <div class="col-sm-1" style="background: #EEEEEE"><h5>表示なし</h5></div>
                                <div class="col-sm-1" style="background: #EEEEEE"><h5>@{{ part.origin_impression }}</h5>
                                </div>
                                <div class="col-sm-1" style="background: #EEEEEE"><h5>-</h5></div>
                                <div class="col-sm-1" style="background: #EEEEEE"><h5>-</h5></div>
                                <div class="col-sm-1" style="background: #EEEEEE"><h5>-</h5></div>
                                <div class="col-sm-1" style="background: #EEEEEE"><h5>@{{ part.origin_cv }}</h5></div>
                                <div class="col-sm-1" style="background: #EEEEEE"><h5>@{{ part.origin_cvr }} %</h5>
                                </div>
                            </div>
                            <div class="row" style="background: white; height: 20px;">
                                <div class="col-sm-12"></div>
                            </div>
                        </div>
                    </template>


                    <div class="row" style="height: 320px">

                        <template v-if="part.status == 1">
                            <template v-if="part.loading_kpi">
                                <i class="fa fa-spinner fa-spin" style="font-size:40px; margin:0px ; padding: 0px"></i>
                            </template>
                            <template v-else>
                                <line-chart v-bind:chartData="chart_data"
                                            :width="300" :height="300"
                                            :options="chart_options">
                                </line-chart>
                            </template>
                        </template>
                        <template v-else>
                            <div class="no-report text-center" style="height: 300px; padding-top: 150px;">
                                <p>UGCセットは公開していません</p>
                                <p><a href="#" onclick="partDetail.openDetailModal({{$part->id}})"
                                      class="btn btn-detail middle1">公開設定を確認</a></p>
                            </div>
                        </template>

                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-md-1">
                        <h5><b>登録画像</b></h5>
                    </div>
                    <div class="col-md-1">
                        <h5>表示数 @{{ numberDisplayImage }}/@{{ part.ugc_limit }}</h5>
                    </div>
                    <div class="col-md-2">
                        <h5>UGC並び順: @{{ part.sort_str }} </h5>
                    </div>
                    <div class="col-md-2">
                        <input type="checkbox" id="checkbox" v-model="show_hidden_image"><label for="checkbox"><h5>
                                過去登録分も表示</h5></label>
                    </div>
                </div>

            </div>
            <div class="col-lg-12">
                <div class="row testimonial-group">
                    <template v-if="part.loading_status">
                        <i class="fa fa-spinner fa-spin" style="font-size:40px; margin:0px ; padding: 0px"></i>
                    </template>

                    <template v-else>
                        <div class="row">
                            <div class="col-lg-3">
                                <input type="image" class="add-image" data-toggle="modal"
                                       data-target="#ugc_select_modal"
                                       src="/images/add-btn.png"/>
                            </div>
                            <part-image-component :ugc="ugc" :part="part" v-bind:key="key"
                                                  v-bind:show_hidden_image="show_hidden_image"
                                                  v-on:sort_value_updated="update_sort_value"
                                                  v-for="(ugc, key) in topUgcs"></part-image-component>
                        </div>
                    </template>
                </div>
            </div>
            <div v-if="part.sort == 3" class="col-lg-12 mt10" style="text-align:center">
                <button style="margin: auto" type="button" class="btn btn-cancel" @click="submit_sort_value">表示順の反映</button>
            </div>
        </div>
    </script>


    <script type="text/x-template" id="image_template">
        <div v-if="display" class="col-lg-3 show-image">
            <div class="row" style="height: 350px;">
                <img class="ugc-image" v-bind:src="ugc.img_url">
                <div class="row first-row">
                    <div class="col-lg-8">
                        <p class="ugc_text">クリック数</p>
                    </div>
                    <div class="col-lg-4 float-right">
                        <p class="ugc_text">@{{ ugc.click }}</p>
                    </div>
                </div>

                <div v-if="part.template == 3" class="row" style="margin: 3px 5px;">
                    <div class="col-lg-8">
                        <p class="ugc_text">遷移数</p>
                    </div>
                    <div class="col-lg-4 float-right">
                        <p class="ugc_text">@{{ ugc.outer_contents_product_link_count }}</p>
                    </div>
                </div>

                <div class="row" style="margin: 3px 5px;">
                    <div class="col-lg-8">
                        <p class="ugc_text">
                            CV</p>
                    </div>
                    <div class="col-lg-4 float-right">
                        <p class="ugc_text">@{{ ugc.cv }}</p>
                    </div>
                </div>

                <div class="row" style="margin: 3px 5px;">
                    <div class="col-lg-3" style="padding-right: 0px;">
                        <p class="ugc_text">
                            登録</p>
                    </div>
                    <div class="col-lg-9 float-right">
                        <p class="ugc_text">@{{ ugc.add_to_part_date}}</p>
                    </div>
                </div>

                <div v-if="part.sort == 3" class="row" style="margin: 3px 5px;">
                    <div class="col-lg-6">
                        <p class="ugc_text">
                            表示順</p>
                    </div>
                    <div class="col-lg-6">
                        <input type="number" style="width: 50px" v-model="sort_value" min="0">
                    </div>
                </div>


                <div v-if="!ugc.hidden && ugc.post_id" type="button" v-on:click="deleteImage()"
                     class="btn "><span
                            class="glyphicon glyphicon-remove-sign blue delete-button"
                            aria-hidden="true"></span>
                </div>
            </div>
            <div class="row" v-if="ugc.post_id">
                <a :href="ugc.url" role="button" class="btn btn-detail btn-block col-xs-12 part-link-panel">詳細を見る
                    <i aria-hidden="true" class="fa fa-angle-double-right"></i></a>
            </div>
        </div>
    </script>
    @include("templates.part.ugc_select_modal")
    @include("templates.part.publish_part_modal")

    <script src="{{static_file_version('/js/jquery.lazyload.min.js')}}"></script>

    <script type="text/javascript" src="{{static_file_version('/js/moment/moment.min.js')}}"></script>
    <script type="text/javascript" src="{{static_file_version('/js/datepicker/daterangepicker.js')}}"></script>
    <script src="{{static_file_version('/js/chartjs/chart.min.js')}}"></script>
    <script src="{{static_file_version('/js/chartjs/vue-chartjs.min.js')}}"></script>
    <script type="text/javascript" src="{{static_file_version('/js/custom/part/part.js')}}"></script>
    <script type="text/javascript" src="{{static_file_version('/js/custom/part/publishModal.js')}}"></script>
    <script type="text/javascript" src="{{static_file_version('/js/custom/part/ugcSelectModal.js')}}"></script>
    <script type="text/javascript" src="{{static_file_version('/js/custom/part/partDetail.js')}}"></script>
@endpush

