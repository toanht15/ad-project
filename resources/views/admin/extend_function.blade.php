@extends('layouts.admin')

@section('header')
    <!-- select2 -->
    <link href="{{static_file_version('/css/select/select2.min.css')}}" rel="stylesheet">
@stop

@section('content')
    <div class="">
        @include('templates.alert')
        <div class="row" id="app">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Dynamic Creative</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <form class="form-inline mt20" role="form" method="POST" action="{{URL::route('create_dynamic_creative')}}">
                                    {{csrf_field()}}
                                    <div class="form-group">
                                        <input name="ad_account_id" class="form-control" v-model="adAccountId" placeholder="Ad Account ID (act_*****)" value="{{old('ad_account_id')}}">
                                    </div>
                                    <div class="form-group">
                                        <input name="ad_set_id" class="form-control" placeholder="Ad Set ID" value="{{old('ad_set_id')}}">
                                    </div>
                                    <div class="form-group">
                                        <input name="link_urls" class="form-control" placeholder="Website URL" value="{{old('link_urls')}}">
                                    </div>
                                    <div class="form-group">
                                        <input name="page_id" class="form-control" placeholder="Page ID" value="{{old('page_id')}}">
                                    </div>
                                    <div class="form-group">
                                        <div class="form-group">
                                            <select name="instagram_actor_id" class="form-control">
                                                <option>Instagram Account</option>
                                                <option v-for="account in instaActors" v-bind:value="account.id">@{{account.username}}</option>
                                            </select>
                                        </div>
                                        <a class="btn btn-xs btn-success" type="button" v-on:click="getInstagram">取得</a>
                                    </div>
                                    <div class="form-group">
                                        <input name="ad_name" class="form-control" placeholder="Ad Name" value="{{old('ad_name')}}">
                                    </div>
                                    <div class="form-group">
                                        <label>Ad Format</label>
                                        <select name="ad_formats" class="form-control">
                                            <option value="SINGLE_IMAGE">SINGLE_IMAGE</option>
                                            <option value="CAROUSEL_IMAGE">CAROUSEL_IMAGE</option>
                                            <option value="SINGLE_VIDEO">SINGLE_VIDEO</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Call To Action</label>
                                        <select name="call_to_action_types" class="form-control">
                                            <option value="OPEN_LINK">OPEN_LINK</option>
                                            <option value="LIKE_PAGE">LIKE_PAGE</option>
                                            <option value="SHOP_NOW">SHOP_NOW</option>
                                            <option value="PLAY_GAME">PLAY_GAME</option>
                                            <option value="INSTALL_APP">INSTALL_APP</option>
                                            <option value="USE_APP">USE_APP</option>
                                            <option value="INSTALL_MOBILE_APP">INSTALL_MOBILE_APP</option>
                                            <option value="USE_MOBILE_APP">USE_MOBILE_APP</option>
                                            <option value="BOOK_TRAVEL">BOOK_TRAVEL</option>
                                            <option value="LISTEN_MUSIC">LISTEN_MUSIC</option>
                                            <option value="LEARN_MORE" selected>LEARN_MORE</option>
                                            <option value="SIGN_UP">SIGN_UP</option>
                                            <option value="DOWNLOAD">DOWNLOAD</option>
                                            <option value="WATCH_MORE">WATCH_MORE</option>
                                            <option value="NO_BUTTON">NO_BUTTON</option>
                                            <option value="CALL_NOW">CALL_NOW</option>
                                            <option value="APPLY_NOW">APPLY_NOW</option>
                                            <option value="BUY_NOW">BUY_NOW</option>
                                            <option value="GET_OFFER">GET_OFFER</option>
                                            <option value="GET_OFFER_VIEW">GET_OFFER_VIEW</option>
                                            <option value="GET_DIRECTIONS">GET_DIRECTIONS</option>
                                            <option value="MESSAGE_PAGE">MESSAGE_PAGE</option>
                                            <option value="MESSAGE_USER">MESSAGE_USER</option>
                                            <option value="SUBSCRIBE">SUBSCRIBE</option>
                                            <option value="SELL_NOW">SELL_NOW</option>
                                            <option value="DONATE_NOW">DONATE_NOW</option>
                                            <option value="GET_QUOTE">GET_QUOTE</option>
                                            <option value="CONTACT_US">CONTACT_US</option>
                                            <option value="START_ORDER">START_ORDER</option>
                                            <option value="RECORD_NOW">RECORD_NOW</option>
                                            <option value="VOTE_NOW">VOTE_NOW</option>
                                            <option value="REGISTER_NOW">REGISTER_NOW</option>
                                            <option value="REQUEST_TIME">REQUEST_TIME</option>
                                            <option value="SEE_MENU">SEE_MENU</option>
                                            <option value="EMAIL_NOW">EMAIL_NOW</option>
                                            <option value="GET_SHOWTIMES">GET_SHOWTIMES</option>
                                            <option value="TRY_IT">TRY_IT</option>
                                            <option value="LISTEN_NOW">LISTEN_NOW</option>
                                            <option value="OPEN_MOVIES">OPEN_MOVIES</option>
                                        </select>
                                    </div>
                                    <br>
                                    <div class="form-group middle4">
                                        <p v-if="errorMsg" class="red">@{{ errorMsg }}</p>
                                        <select class="js-video-id-multiple" name="video_ids[]" multiple="multiple">
                                            <option>取得ボタンに押してください</option>
                                            <option v-for="video in videoList" v-bind:value="video.id" v-bind:data-url="video.picture">@{{ video.title }}</option>
                                        </select>
                                        <a class="btn btn-xs btn-success" type="button" v-on:click="getVideo">ビデオを取得</a>
                                    </div>
                                    <div class="form-group">
                                        <textarea name="image_hashs" rows="10" cols="50" style="font-size: 14px" class="form-control auto-modal-hash-input" placeholder="Image Hash">{{old('image_hashs')}}</textarea>
                                        <p><small class="red">︎改行で区別</small></p>
                                    </div>
                                    <div class="form-group">
                                        <textarea name="bodies" rows="10" cols="50" style="font-size: 14px" class="form-control auto-modal-hash-input" placeholder="Body texts">{{old('bodies')}}</textarea>
                                        <p><small class="red">---で区別︎</small></p>
                                    </div>
                                    <div class="form-group">
                                        <textarea name="titles" rows="10" cols="50" style="font-size: 14px" class="form-control auto-modal-hash-input" placeholder="Titles">{{old('titles')}}</textarea>
                                        <p><small class="red">---で区別︎</small></p>
                                    </div>
                                    <div class="form-group">
                                        <textarea name="descriptions" rows="10" cols="50" style="font-size: 14px" class="form-control auto-modal-hash-input" placeholder="Descriptions">{{old('descriptions')}}</textarea>
                                        <p><small class="red">---で区別︎</small></p>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-danger mt20">作成</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('script')
    <script>
        var getVideoApiUrl = "{{URL::route('api_get_ad_videos', ['adAccountId' => ''])}}",
            getInstagramApiUrl = "{{URL::route('api_get_instagram_actor', ['adAccountId' => ''])}}";
    </script>
    <script src="https://cdn.jsdelivr.net/npm/lodash@4.13.1/lodash.min.js"></script>
    <script src="{{static_file_version('/js/select/select2.full.js')}}"></script>
    <script src="{{static_file_version('/js/custom/adminExtendFunctionPage.js')}}"></script>
@stop