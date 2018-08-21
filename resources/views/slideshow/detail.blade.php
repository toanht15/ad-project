@extends('layouts.master')

@section('title')
    Detail
@stop
@section('header')
    <link href="https://vjs.zencdn.net/5.20.1/video-js.css" rel="stylesheet">
    <link rel="stylesheet" href="{{static_file_version('/css/videojs/videojs-skin-color.css')}}">
    <link href="{{static_file_version('/css/select/select2.min.css')}}" rel="stylesheet">
@stop

@section('content')
    <div class="page-title">
        <div class="title_left">
            <h3>スライドショー詳細</h3>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="x_panel" id="app">
        @include('templates.alert')
        <div class="imagedetail-info">
            <div class="col-xs-3">
                <div class="imgdetail-box" id="slideshow_detail_preview_box">
                    <video controls class="img-responsive video-fill-frame video-js vjs-styles-dimensions vjs-big-play-centered vjs-skin-colors-orange vjs-fluid" data-setup='{"controlBar": {"volumeMenuButton": false}}'>
                        <source src="{{\App\Service\SlideshowService::getVideoUrl($slideshow->name, $adAccount->id)}}" type="video/mp4">
                    </video>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="col-xs-9">
                <table class="table mt10">
                    <tbody>
                    <tr>
                        <td>ユーザー :</td>
                        <td>
                            <div class="imgdetail-autohorname">{{ $adAccount->name }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>サイズ</td>
                        <td>{{\App\Service\SlideshowService::changeVideoSizeFormat($slideshow->size)}} MB</td>
                    </tr>
                    <tr>
                        <td>画像数</td>
                        <td>{{$imageCount}}</td>
                    </tr>
                    <tr>
                        <td>再生時間</td>
                        <td>{{$slideshow->duration}} 秒</td>
                    </tr>
                    <tr>
                        <td>作成日付</td>
                        <td>{{ $slideshow->created_at->format('Y/m/d H:i') }}</td>
                    </tr>
                    <tr>
                        <td>状態</td>
                        <td>
                            <?php echo \App\Service\SlideshowService::getStatusHtml($slideshow->status) ?>
                        </td>
                    </tr>
                    <tr v-cloak v-if="kpis.length > 0">
                        <td>KPI</td>
                        <td>
                            <div class="col-md-12" v-for="(kpi, index) in kpis" v-bind:class="{'edited-image-kpi': index != (kpis.length - 1)}">
                                <div class="kpi sns-btn fix-width-100" v-if="kpi.media_type == {{\Classes\Constants::MEDIA_FACEBOOK}}">
                                    <span class="fa fa-facebook-square"></span>@{{ kpi.name  }}
                                </div>
                                <div class="kpi sns-btn fix-width-100" v-if="kpi.media_type == {{\Classes\Constants::MEDIA_TWITTER}}">
                                    <span class="fa fa-twitter-square"></span>@{{ kpi.name  }}
                                </div>
                                <div class="kpi">
                                    <img src="{{static_file_version('/images/icon_ctr.png')}}" width="28" alt="">
                                    <div class="left">
                                        CTR
                                        <span>@{{ parseFloat(kpi.ctr == null ? 0 : kpi.ctr*100).toFixed(2) }}%</span>
                                    </div>
                                </div>
                                <div class="kpi">
                                    <img src="{{static_file_version('/images/icon_spend.png')}}" width="22" alt="">
                                    <div class="left">
                                        Spend
                                        <span>¥@{{ kpi.spend == null ? 0 : kpi.spend }}</span>
                                    </div>
                                </div>
                                <div class="kpi">
                                    <img src="{{static_file_version('/images/icon_impression.png')}}" width="21" alt="">
                                    <div class="left">
                                        Impressions
                                        <span>@{{ kpi.imp == null ? 0 : kpi.imp }}</span>
                                    </div>
                                </div>
                                <div class="kpi">
                                    <img src="{{static_file_version('/images/icon_click.png')}}" width="22" alt="">
                                    <div class="left">
                                        Click
                                        <span>@{{ kpi.click == null ? 0 : kpi.click }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            メディアに同期
                        </td>
                        <td>
                            <div class="col-md-4">
                                <form action="{{URL::route('upload_slideshow', ['slideshowId' => $slideshow->id])}}" id="upload_form" method="POST">
                                    {{csrf_field()}}
                                    <select class="form-control sns-btn" name="media_account_id" data-show-icon="true">
                                        @foreach($mediaAccounts as $mediaAccount)
                                            <option data-type="{{$mediaAccount->media_type}}" value="{{$mediaAccount->id}}">{{$mediaAccount->name}}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-Synchronis form-control mt3" v-on:click="uploadImage">
                                    <i class="fa fa-cloud-upload" aria-hidden="true"></i>同期する
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr><td></td><td></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="clearfix"></div>
        </div>
        @include('templates.tw_upload_modal', ['formAction' => URL::route('upload_slideshow', ['slideshowId' => $slideshow->id]), 'fileFormat' => \App\Models\Post::VIDEO])
    </div>
@stop

@section('script')
    <script>
        var mediaAccountList = [],
            getKpiApiUrl = '{{URL::route('get_slideshow_kpi', ['slideshowId' => $slideshow->id])}}',
            slideshow = {id: '', url:'{{$firstImage->image_url}}'};
        @foreach($mediaAccounts as $mediaAccount)
            mediaAccountList['{{$mediaAccount->id}}'] = {{$mediaAccount->media_type}};
        @endforeach
    </script>
    <script src="{{static_file_version('/js/select/select2.full.js')}}"></script>
    <script src="{{static_file_version('/js/custom/slideshowDetailPage.js')}}"></script>
    <script src="https://vjs.zencdn.net/5.20.1/video.js"></script>
@stop
