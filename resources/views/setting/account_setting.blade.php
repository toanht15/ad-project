@extends('layouts.master')

@section('title') Setting @stop

@section('header')
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    <link href="{{url('/')}}/css/bootstrap-social.css" rel="stylesheet">
    <link href="{{static_file_version('/css/select/select2.min.css')}}" rel="stylesheet">
@stop

@section('content')
    <div class="page-title">
        <div class="title_left">
            <h3>設定</h3>
        </div>
    </div>
    <div class="clearfix"></div>

    <div class="row">
        <div class="col-md-12">
            <div class="x_panel" id="app">
                <div class="x_content">
                    @include('templates.alert')
                    <div class="col-md-12">
                        <table class="table mt10 none-border-table">
                            <tbody>
                            <tr>
                                <td class="table-title">アカウント名</td>
                                <td><div class="col-md-12">{{$advertiser->name}}</div></td>
                            </tr>
                            <tr>
                                <td class="table-title">プラン・契約期間</td>
                                <td> @include('setting.contract_schedule')</td>
                            </tr>
                            <tr>
                                <td class="table-title">メール通知</td>
                                <td>
                                    {{--radio button--}}
                                    <div class="row">
                                        <div class="col-md-1">
                                            <div class="custom-switch">
                                                <input id="email-toggle" type="checkbox">
                                                <label for="email-toggle" class="custom-switch-label">
                                                    <span class="inner"></span>
                                                    <span class="switch"></span>
                                                </label>
                                            </div>
                                        </div>

                                        <form class="form-label-left" action="{{URL::route('save_email')}}"
                                              method="POST" id="mail_noti_form">
                                            {{csrf_field()}}
                                            <div class="col-md-4">
                                                <input id="email-form" type="email" name="email" value="{{$email}}"
                                                       class="form-control input-form" placeholder="メールアドレス">
                                            </div>
                                            <div class="col-md-2">
                                                <button id="email-submit-btn" type="submit" class="btn btn-primary">
                                                    保存
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="table-title">Instagram連携</td>
                                <td>@include('templates.instagram_account', ['redirect' => URL::route('account_setting')])</td>
                            </tr>

                            <tr>
                                <td class="table-title">自社アカウントの投稿を収集</td>
                                <td>
                                    {{--radio button--}}
                                    <div class="row">
                                        <div class="col-md-1">
                                            <div class="custom-switch">
                                                <input id="crawl-account-post-toggle" type="checkbox" @if($advertiser->is_crawl_own_post) checked @endif>
                                                <label for="crawl-account-post-toggle" class="custom-switch-label">
                                                    <span class="inner"></span>
                                                    <span class="switch"></span>
                                                </label>
                                            </div>
                                        </div>

                                    </div>
                                </td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                    <div class="clearfix"></div>

                    {{--start tab ui --}}
                    <ul class="nav nav-tabs nav-tabsLetro" role="tablist">
                        @if (can_use_ads())
                            <li role="presentation" class="active" id="ads_nav_tab">
                                <a href="#ad" aria-controls="home" role="tab" data-toggle="tab">SNS広告</a></li>
                        @endif
                        @if(can_use_ugc_set())
                            <li role="presentation" id="owned_nav_tab">
                                <a href="#owned" aria-controls="profile" role="tab" data-toggle="tab">オウンドメディア</a></li>
                        @endif
                    </ul>

                    <div class="tab-content">
                        {{--for AD--}}
                        <div role="tabpanel" class="tab-pane active" id="ad">
                            {{--media_accounts--}}
                            @if(can_use_ads())
                                @include('templates.media_account_list')
                            @endif
                        </div>
                        {{--for OWNED--}}
                        <div role="tabpanel" class="tab-pane" id="owned">
                            @if(can_use_ugc_set())
                                @include('templates.owned_info')

                            @endif
                        </div>
                    </div>
                    {{--end tab ui --}}
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    <script src="{{static_file_version('/js/progressbar/bootstrap-progressbar.min.js')}}"></script>
    <script src="{{static_file_version('/js/validator/validator.js')}}"></script>
    <script src="{{static_file_version('/js/custom/formValidator.js')}}"></script>
    <script src="{{static_file_version('/js/custom/accountSetting.js')}}"></script>
    <script src="{{static_file_version('/js/select/select2.full.js')}}"></script>

    <script>

        var getMediaAccountListApiUrl = '{{URL::route('api_media_account_list')}}';
        var postAdvertiserCrawlApiUrl = '{{URL::route('api_advertiser_crawl_post_setting')}}';

        @if(count($mediaAccounts))
            $('#add_media_account_modal').modal('show');
        @endif
        @if (!can_use_ads())
            $('#owned').addClass('active');
            $('#owned_nav_tab').addClass('active');
        @endif
        @if (Session::has('currentTab') && Session::get('currentTab') == 'owned')
            $('#ad').removeClass('active');
            $('#ads_nav_tab').removeClass('active');
            $('#owned').addClass('active');
            $('#owned_nav_tab').addClass('active');
        @endif
        $('[data-toggle="tooltip"]').tooltip();
    </script>
@stop
