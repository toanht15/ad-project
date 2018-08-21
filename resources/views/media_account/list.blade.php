@extends('layouts.master')

@section('header')
    <link href="{{url('/')}}/css/bootstrap-social.css" rel="stylesheet">
    <link href="{{static_file_version('/css/select/select2.min.css')}}" rel="stylesheet">
@stop

@section('title')
    メディアアカウント一覧
@stop

@section('content')
    <div class="page-title">
        <div class="title_left">
            <h3>メディアアカウント一覧</h3>
        </div>
    </div>
    <div class="clearfix"></div>

    <div class="row" id="app">
        <div class="col-xs-12">
            <div class="x_panel">
                        {{csrf_field()}}
                <div class="x_content">
                    @include('templates.alert')
                    <div class="col-md-3"></div>
                    <div class="col-md-6" v-cloak>
                        <p class="mt20 ml10" v-if="mediaAccountList != null && mediaAccountList.length < {{$advertiser->max_media_account}}"><b>アカウントの追加</b></p>
                        <div class="col-md-12" v-if="mediaAccountList != null && mediaAccountList.length < {{$advertiser->max_media_account}}">
                            <div class="col-md-4">
                                <a class="btn btn-block btn-social btn-facebook" href="{{URL::route('media_account_login_fb')}}" onclick="ga('send', 'event', 'account_connection', 'connect_FB', '{{\Auth::guard('advertiser')->user()->id}}');">
                                    <span class="fa fa-facebook"></span> アカウント追加
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a class="btn btn-block btn-social btn-twitter" href="{{URL::route('media_account_login_tw')}}" onclick="ga('send', 'event', 'account_connection', 'connect_TW', '{{\Auth::guard('advertiser')->user()->id}}');">
                                    <span class="fa fa-twitter"></span> アカウント追加
                                </a>
                            </div>
                        </div>
                        <div class="col-md-12 mt20">
                            <p class=""><b>連携中のアカウント</b></p>
                        </div>
                        <div class="col-md-12" v-if="mediaAccountList != null" >
                            <hr>
                            <div class="col-md-12" v-for="mediaAccount in mediaAccountList">
                                <div v-if="mediaAccount.media_type == {{\Classes\Constants::MEDIA_FACEBOOK}}">
                                    <a class="btn btn-social-icon btn-facebook nohover">
                                        <span class="fa fa-facebook"></span>
                                    </a>@{{mediaAccount.name}}
                                </div>
                                <div v-if="mediaAccount.media_type == {{\Classes\Constants::MEDIA_TWITTER}}">
                                    <a class="btn btn-social-icon btn-twitter nohover">
                                        <span class="fa fa-twitter"></span>
                                    </a>@{{mediaAccount.name}}
                                </div>
                                <hr>
                            </div>
                        </div>
                        <div class="pl20" v-if="mediaAccountList != null && mediaAccountList.length >= {{$advertiser->max_media_account}}">
                            <p>広告アカウントの追加をご要望の場合は、アライドアーキテクツ担当者までご連絡ください。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="add_media_account_modal" class="modal fade">
        <div class="modal-dialog modal-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title ">広告アカウントの追加</h4>
                </div>
                <div class="modal-body text-center">
                    <form class="form-horizontal form-label-left" action="{{URL::route('create_media_account')}}" method="POST">
                    <div class="middle4">
                            @include('templates.alert')
                            <select name="media_account_id" class="select2_single form-control" tabindex="-1">
                                <option></option>
                                @foreach ( $mediaAccounts as $mediaAccount )
                                    @if(isset($mediaAccount['name']) && trim($mediaAccount['name']))
                                        <option value="{{ $mediaAccount['id'] }}"><i class='icon-user'></i>{{ $mediaAccount['name'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="form-group">
                                <div class="col-md-12 mt20 text-center">
                                    <button type="submit" class="btn btn-success">追加</button>
                                </div>
                            </div>
                        </div>
                        <!-- /.panel-body -->

                        <div class="clearfix"></div>
                    </form>
                    <!-- form -->
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    <script src="{{static_file_version('/js/select/select2.full.js')}}"></script>
    <script>
        var getMediaAccountListApiUrl = '{{URL::route('api_media_account_list')}}';
        @if(count($mediaAccounts))
            $('#add_media_account_modal').modal('show');
        @endif
    </script>
    <script src="{{static_file_version('/js/custom/mediaAccountListPage.js')}}"></script>
@stop