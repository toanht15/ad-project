@extends('layouts.master')

@section('title')
    Dashboard
@stop

@section('header')
    <link rel="stylesheet" href="{{static_file_version('/css/lightslider.css')}}">
    <link rel="stylesheet" href="{{static_file_version('/css/app/customDashboard.css')}}">
    <link href="{{static_file_version('/css/select/select2.min.css')}}" rel="stylesheet">
@stop

@section('content')
    <div class="row">
        <div class="col-md-2" style="margin-left: 10px;">
            <h3>Overview</h3>
        </div>
    </div>
    <div class="col-xs-12">
        <div class="x_panel">
            <ul class="nav nav-tabs service-bar" role="tablist">
                @if(can_use_ads())
                    <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab"
                                                              data-toggle="tab">SNS広告</a></li>
                @endif
                @if(can_use_ugc_set())
                    <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">オウンドメディア</a>
                    </li>
                @endif
            </ul>

            <div class="tab-content">
                @if(can_use_ads())
                    <div role="tabpanel" class="tab-pane active" id="home">
                        <div class="row" id="app">

                            <div class="col-md-12 x_title">
                                <div class="col-md-6 mb10 mt10">
                                    @if (count($mediaTypes) > 1)
                                        <div class="btn-group" v-cloak>
                                            <button type="button" class="btn btn-gray nonactive"
                                                    v-bind:class="{ 'active': hasMedia([{{\Classes\Constants::MEDIA_ALL}}]) }"
                                                    v-on:click="setMediaType({{\Classes\Constants::MEDIA_ALL}})">
                                                <span class="text">Total</span>
                                            </button>
                                            <button type="button" class="btn btn-gray nonactive"
                                                    v-bind:class="{ 'active': hasMedia([{{\Classes\Constants::MEDIA_FACEBOOK}}]) }"
                                                    v-on:click="setMediaType({{\Classes\Constants::MEDIA_FACEBOOK}})">
                                                <span class="fa fa-facebook-square"></span>
                                            </button>
                                            <button type="button" class="btn btn-gray nonactive"
                                                    v-bind:class="{ 'active': hasMedia([{{\Classes\Constants::MEDIA_TWITTER}}]) }"
                                                    v-on:click="setMediaType({{\Classes\Constants::MEDIA_TWITTER}})">
                                                <span class="fa fa-twitter-square"></span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6 text-right">
                                    <div class="input-group dashboard-date-input">
                                        <span class="add-on input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" style="width: 200px" name="reservation" id="reservation"
                                               class="form-control reservation"
                                               value="{{(new DateTime($dateStart))->format('Y/m/d') . ' - ' . (new DateTime($dateStop))->format('Y/m/d')}}"/>
                                    </div>
                                </div>
                            </div>

                            <div class="x_content">
                                <div class="clearfix"></div>
                                <div class="col-md-12">
                                    <div class="col-md-12 well-custom auto-overflow">
                                        <div class="col-md-12">
                                            <div class="col-md-2">KPIサマリー</div>
                                            <div class="col-md-10">
                                                <div class="col-md-12 mb10">
                                                    <div class="col-md-3">
                                                        <div class="col-md-4">CTR</div>
                                                        <div class="col-md-8"><span class="kpi-data"
                                                                                    v-cloak>@{{totalCtr}}%</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="col-md-4">Spend</div>
                                                        <div class="col-md-8"><span class="kpi-data" v-cloak>¥@{{number_format(totalSpend)}}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="col-md-4">Impression</div>
                                                        <div class="col-md-8"><span class="kpi-data" v-cloak>@{{number_format(totalImp)}}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="col-md-4">Click</div>
                                                        <div class="col-md-8"><span class="kpi-data" v-cloak>@{{number_format(totalClick)}}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr class="dot">
                                                <div class="col-md-12 mb10">
                                                    <div class="col-md-3">
                                                        <div class="col-md-4">CV</div>
                                                        <div class="col-md-8"><span class="kpi-data"
                                                                                    v-cloak>@{{conversion}}</span>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="col-md-4">CPA</div>
                                                        <div class="col-md-8"><span class="kpi-data"
                                                                                    v-cloak>¥@{{cpa}}</span></div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="col-md-4">CVR</div>
                                                        <div class="col-md-8"><span class="kpi-data"
                                                                                    v-cloak>@{{cvr}}%</span></div>
                                                    </div>
                                                    <div class="col-md-3" v-cloak>
                                                        <select name="conversion_type" v-model="conversionType"
                                                                class="select2_single">
                                                            @foreach($conversionTypes as $conversionType)
                                                                <option value="{{$conversionType->id}}">{{$conversionType->label ? $conversionType->label : $conversionType->action_type}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="col-md-12 ugc-status-all" v-if="ugcOffer > 0" v-cloak>
                                            <div class="col-md-2">UGC ステータス</div>
                                            <div class="col-md-10">
                                                <div class="col-md-12 text-center mb15">
                                                    <span class="livingad-text mr20"> ▪️出稿中(@{{ ugcSpending }})</span>
                                                    <span class="livead-text mr20"> ▪出稿済(@{{ ugcSpended }})</span>
                                                    <span class="arrroved-text mr20"> ▪承認されたUGC(@{{ ugcApproved }})</span>
                                                    <span class="offerd-text"> ▪リクエストしたUGC(@{{ ugcOffer }})</span>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="progress offerd ugc-status">
                                                        <div class="progress-bar progress-bar-danger livingad"
                                                             role="progressbar" v-if="ugcSpending > 0"
                                                             v-bind:style="{width:ugcSpendingRate+'%'}">
                                                        </div>
                                                        <div class="progress-bar progress-bar-warning livead"
                                                             role="progressbar" v-if="ugcSpended > 0"
                                                             v-bind:style="{width:ugcSpendedRate+'%'}">
                                                        </div>
                                                        <div class="progress-bar progress-bar-info arrroved"
                                                             role="progressbar" v-if="ugcApproved > 0"
                                                             v-bind:style="{width:ugcApprovedRate+'%'}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="left text-right status-text"
                                                         v-bind:style="{width:ugcSpendingRate+'%'}">@{{ ugcSpending
                                                        }}
                                                    </div>
                                                    <div class="left text-right status-text"
                                                         v-bind:style="{width:ugcSpendedRate+'%'}">@{{ ugcSpended }}
                                                    </div>
                                                    <div class="left text-right status-text"
                                                         v-bind:style="{width:ugcApprovedRate+'%'}">@{{ ugcApproved
                                                        }}
                                                    </div>
                                                    <div class="left text-right status-text"
                                                         v-bind:style="{width:ugcOfferRate+'%'}">@{{ ugcOffer }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <div class="col-md-12 daily-chart" v-cloak>
                                    <div v-bind:class="{hidden : totalSpend == 0 || totalSpend == null}"
                                         id="reportContainer"></div>
                                    <div v-bind:class="{hidden : totalSpend > 0}" class="no-report text-center">
                                        <p>出稿中のSNS広告がありません<br>
                                            <span>デイリーレポートはUGCを広告に出稿後表示されます</span></p>
                                        <p>
                                            <a href="{{URL::route('image_list')}}" class="btn btn-detail middle1">承認済のUGCを確認<i
                                                        class="fa fa-angle-double-right" aria-hidden="true"></i></a>
                                        </p>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <div class="col-md-12 offer-set ugc-best-performance"
                                     v-if="topPerformanceUgc != null && topPerformanceUgc.length > 0">
                                    <div class="col-md-2">Performance</div>
                                    <div class="col-md-10">
                                        <ul class="offerset-ugclist sns-btn">
                                            <li class="rankingcell left" v-for="ugc in topPerformanceUgc" v-cloak>
                                                <div class="offer-img-listbox">
                                                    <img alt="" class="offer-img" v-bind:src="ugc.image_url">
                                                </div>
                                                <div class="offer-img-info">
                                                    <p class="m0">
                                                    <span class="fa fa-facebook-square"
                                                          v-if="ugc.media_type == 1"></span>
                                                        <span class="fa fa-twitter-square"
                                                              v-if="ugc.media_type == 2"></span>
                                                    </p>
                                                    <strong class="red"
                                                            v-if="(ugc.yesterday_ctr - ugc.two_day_ago_ctr) > 0">@{{
                                                        number_format(ugc.yesterday_ctr - ugc.two_day_ago_ctr, 2)
                                                        }}%<i
                                                                class="fa fa-arrow-up"
                                                                aria-hidden="true"></i></strong>
                                                    <strong class="blue"
                                                            v-if="(ugc.yesterday_ctr - ugc.two_day_ago_ctr) < 0">@{{
                                                        number_format(ugc.two_day_ago_ctr - ugc.yesterday_ctr, 2)
                                                        }}%<i
                                                                class="fa fa-arrow-down"
                                                                aria-hidden="true"></i></strong>
                                                    <p class="m0">
                                                        <small>CTR Ave: @{{ ugc.imp == 0 ? 0 :
                                                            number_format(ugc.click*100/ugc.imp, 2) }}%
                                                        </small>
                                                    </p>
                                                    <p class="m0">
                                                        <small>Total Spend: ¥@{{ number_format(ugc.spend) }}</small>
                                                    </p>
                                                    <div class="clearfix"></div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /page content -->
                    </div>
                @endif
                <div role="tabpanel" class="tab-pane" id="profile">
                    <div class="row" id="app2">

                        <div class="col-md-12 x_title">
                            <div class="col-md-2">
                                <h3></h3>
                            </div>

                            <div class="col-md-10 text-right">
                                <div class="input-group dashboard-date-input">
                                    <span class="add-on input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <daterangepicker v-bind:value="dateRange"
                                                     v-bind:default_start_date="start_date"
                                                     v-bind:default_finish_date="finish_date"
                                                     v-on:update="updateDateRange"></daterangepicker>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-12" v-if="parts.length > 0">
                            <div v-bind:class="colClass" v-for="part in parts">
                                <part v-bind:part="part"></part>
                            </div>
                        </div>
                        <div class="col-md-12 " style="text-align: center" v-else-if="!init_loading">
                            <div class="middle5">
                                <p>
                                    <b>公開中のUGCセットがありません </b><br>

                                </p>
                                <p>
                                    デイリーレポートはUGCセットをオウンドメディアに公開後に表示されます
                                </p>
                                <p>
                                    <a href="{{URL::route('part_list')}}" class="btn btn-danger middle1">UGCセットを作成<i
                                                class="fa fa-angle-double-right" aria-hidden="true"></i></a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @if(can_use_ads())
        @if (!$hasInstagramAccounts)
            <div id="tutorial_modal" class="modal fade tutorial_modal" data-sort="2">
                <div class="modal-dialog modal-center">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;
                            </button>
                            <h4 class="modal-title ">Instagramアカウントを連携してみましょう!</h4>
                        </div>
                        <div class="modal-body text-center">
                            <p>※リクエストやコメントをするには、Instagram連携が必須となります。</p>
                            <div class="row">
                                <div class="col-sm-offset-4 col-sm-2">
                                    <a type="button" class="btn btn-danger mt10"
                                       href="{{ URL::route('connect_instagram', ['redirect' => URL::route('dashboard')]) }}"><i
                                                class="icon-instagram"> </i>Instagramに連携</a>
                                </div>
                                <div class="col-sm-2">
                                    <button type="button" data-dismiss="modal" class="btn btn-primary mt10">
                                        後から設定
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if ($searchConditionCount <= 1)
            <div id="tutorial_modal" class="modal fade tutorial_modal" data-sort="3">
                <div class="modal-dialog modal-center">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;
                            </button>
                            <h4 class="modal-title ">集めたいUGCのハッシュタグを登録してみましょう!</h4>
                        </div>
                        <div class="modal-body text-center">
                            <form class="form-inline mt20" role="form" action="{{URL::route('store_hashtag')}}"
                                  method="POST" id="add_hashtag_form">
                                {{csrf_field()}}
                                <div class="form-group">
                                    <label class="auto-modal-hash">#</label>
                                    <input type="text" name="hashtags[]" id="first_hashtag"
                                           class="form-control auto-modal-hash-input" value="">
                                    <input type="hidden" name="next_url" value="{{URL::route('image_list')}}">
                                </div>
                                <div>
                                    <button type="button" id="create_first_hashtag" class="btn btn-danger mt20">登録する
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    @if(can_use_ugc_set())
        @if($isFirstOwnerLogin)
            <div id="cv_setting_modal" class="modal fade tutorial_modal" data-sort="1">
                <div class="modal-dialog modal-center">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;
                            </button>
                            <h4 class="modal-title">オウンドメディア初期設定</h4>
                        </div>

                        <div class="modal-body text-center">
                            <div class="form-group text-left" id="letro-tag-form"
                                 style="background-color:#F0ECEC; padding: 10px">
                                <label for="letro-tag">Letro タグ</label>
                                <input type="email" class="form-control" id="letro-tag" placeholder="code"
                                       value='{{$js_tag}}'>
                                <span id="helpBlock2" class="help-block"><strong>このタグをコピーして、UGCセットを表示するページの＜/body＞直前に貼り付けてください。</strong></span>
                            </div>


                            <form class="form-horizontal" style="padding: 10px" id="cv_setting" method="post">
                                {{csrf_field()}}
                                <div class="row text-left">
                                    <h4>目標設定</h4>
                                </div>
                                <div class="row text-left">
                                    <p>コンバージョン（CV）を計測するための設定です。あとから設定もできます。</p>
                                </div>
                                <div class="form-group" style="margin-bottom: 15px;">
                                    <label for="" class="col-sm-2 control-label">目標タイトル</label>
                                    <div class="col-sm-10">
                                        <input class="form-control" name="title" placeholder="（列）購入完了計測">
                                    </div>
                                </div>

                                <div class="form-group" style="margin-bottom: 15px;">
                                    <label for="" class="col-sm-2 control-label">目標ページURL<i
                                                class="glyphicon glyphicon-question-sign" data-toggle="tooltip"
                                                title="購入完了、サンクスページなどのURLを入れます"></i></label>
                                    <div class="col-sm-10">
                                        <input class="form-control" name="url">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-offset-4 col-sm-1">
                                        <button type="submit" id="create_cv_targer" class="btn btn-danger mt20">
                                            保存
                                        </button>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" data-dismiss="modal" class="btn mt20 btn-primary">
                                            後から設定
                                        </button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
@stop

@section('script')
    <!-- bootstrap progress js -->
    <script src="{{static_file_version('/js/progressbar/bootstrap-progressbar.min.js')}}"></script>

    <!-- daterangepicker -->
    <script type="text/javascript" src="{{static_file_version('/js/moment/moment.min.js')}}"></script>
    <script type="text/javascript"
            src="{{static_file_version('/js/datepicker/daterangepicker.js')}}"></script>

    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/funnel.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>

    <script>
        //grapth data
        var getGraphDataApiUrl = '{{URL::route('dashboard_graph_api')}}',
            getTopPerformanceUgcApiUrl = '{{URL::route('get_top_performance_ugc_api')}}',
            getUgcStatusApiUrl = '{{URL::route('get_ugc_status_api')}}',
            getConversionKpiApiUrl = '{{URL::route('get_total_cv_api')}}',
            dateStart = '{{(new DateTime($dateStart))->format('m/d/Y')}}',
            dateStop = '{{(new DateTime($dateStop))->format('m/d/Y')}}',
            mediaTypes = [{{implode(',', $mediaTypes)}}];
        var api_part_update_sort_value = '{{URL::route('api_part_update_sort_value', '%s')}}';
        if (readCookie('start_date') && readCookie('finish_date')) {
            start_date = readCookie('start_date');
            finish_date = readCookie('finish_date');
        }

        @if (count($mediaTypes) > 1)
        mediaTypes.push(0);
        @endif

        $('#letro-tag').on('focus', function () {
            $(this).select();
        });


        var listModal = ($('.tutorial_modal').sort(function (a, b) {
            return a.dataset.sort - b.dataset.sort;
        }).toArray());

        var currentModal = listModal.shift();
        $(currentModal).modal('show');
        $(currentModal).on('hidden.bs.modal', function (e) {
            currentModal = listModal.pop();
            $(currentModal).modal('show');
        });

        $('[data-toggle="tooltip"]').tooltip({
            container: 'body'
        });

        // $('#cv_setting_modal').modal({backdrop: 'static', keyboard: false, show: true});

        // if ($('#tutorial_modal').length > 0) {
        //     //指定したセレクタのモーダルを表示させる
        //     $('#tutorial_modal').modal({backdrop: 'static', keyboard: false, show: true});
        //     //Modalが閉じられたらイベント発動
        //     $('#tutorial_modal').on('hidden.bs.modal', function (e) {
        //         console.log(e);
        //         //非表示を解除
        //         $('#hide_modal').removeClass('hidden');
        //     });
        // }
    </script>
    <script src="{{static_file_version('/js/validator/validator.js')}}"></script>
    <script src="{{static_file_version('/js/custom/formValidator.js')}}"></script>
    <script src="{{static_file_version('/js/lightslider.js')}}"></script>
    <script src="{{static_file_version('/js/select/select2.full.js')}}"></script>
    @if(can_use_ads())
        <script src="{{static_file_version('/js/custom/dashboardPage.js')}}"></script>
    @endif
    <script src="{{static_file_version('/js/chartjs/chart.min.js')}}"></script>
    <script src="{{static_file_version('/js/chartjs/vue-chartjs.min.js')}}"></script>
@stop


@push("push-script")
    <script>
        var cv_setting = '{{URL::route('site_list')}}';
        var part_list_api = '{{URL::route('api_part_list')}}'
        var web_part_detail = '{{URL::route('part_detail', '%s')}}';
        var part_detail = '{{URL::route('api_part_detail', '%s')}}';
        var part_kpi = '{{URL::route('part_kpi', '%s')}}';
        var api_publish_part = '{{URL::route('api_publish_part', '%s')}}';
        var start_date = '{{(new DateTime($dateStart))->format('Y/m/d')}}';
        var finish_date = '{{(new DateTime($dateStop))->format('Y/m/d')}}';
    </script>

    @include("templates.part.dashboard_part_template")

    <script type="text/javascript" src="{{static_file_version('/js/custom/part/part.js')}}"></script>
    <script type="text/javascript" src="{{static_file_version('/js/custom/part/siteList.js')}}"></script>

@endpush