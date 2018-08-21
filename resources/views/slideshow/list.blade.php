@extends('layouts.master')

@section('title') スライドショー @stop
@section('header')
    <link rel="stylesheet" href="{{asset('/css/introjs.css')}}">
    <link rel="stylesheet" href="{{static_file_version('/css/lightslider.css')}}">
    <link rel="stylesheet" href="{{static_file_version('/css/sliderCustom.css')}}">
    <link rel="stylesheet" href="{{static_file_version('/css/app/imagelist.css')}}">
    <link href="https://vjs.zencdn.net/5.20.1/video-js.css" rel="stylesheet">
    <link rel="stylesheet" href="{{static_file_version('/css/videojs/videojs-skin-color.css')}}">
@stop
@section('content')
    <div class="page-title">
        <div class="title_left">
            <h3>スライドショー</h3>
        </div>

    </div>
    <div class="clearfix"></div>

    <div class="row" id="app">
        <div class="col-xs-12">
            <div class="x_panel">
                <div class="x_content">
                    @include('templates.alert')
                    <!-- nocrowing-area -->

                    <table class="table table-striped projects mt40 hashtag-list video-list-table">
                        <thead>
                            <tr>
                                <th>スライドショー</th>
                                <th class="text-center">フォーマット</th>
                                <th class="text-center">ステータス</th>
                                <th class="text-center">時間</th>
                                <th class="text-center">画像数</th>
                                <th class="text-center">サイズ</th>
                                <th class="text-center">最終更新日</th>
                                <th width="100"></th>
                                <th width="180"></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($slideshows as $slideshow)
                            <tr>
                                <td>
                                    <div class="slideshow-video-box">
                                        @if($slideshow->video_type == \App\Models\Slideshow::VIDEO_TYPE_STORIES)
                                            <video class="video-js vjs-big-play-centered video-fill-frame vjs-skin-colors-orange" data-setup='{"fluit":"true", "aspectRatio": "9:16" ,"controlBar": {"volumeMenuButton": false}}' style="width:120px;" controls>
                                                <source src="{{\App\Service\SlideshowService::getVideoUrl($slideshow->name, $advertiser->id)}}" type="video/mp4">
                                            </video>
                                        @else
                                            <video class="video-js vjs-big-play-centered video-fill-frame vjs-skin-colors-orange" data-setup='{"fluit":"true", "controlBar": {"volumeMenuButton": false}}' style="width: 120px; height: 120px" controls>
                                                <source src="{{\App\Service\SlideshowService::getVideoUrl($slideshow->name, $advertiser->id)}}" type="video/mp4">
                                            </video>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($slideshow->video_type == \App\Models\Slideshow::VIDEO_TYPE_STORIES)
                                        Instagram Stories
                                    @else
                                        正方形
                                    @endif
                                </td>
                                <td class="text-center">
                                    <?php echo (\App\Service\SlideshowService::getStatusHtml($slideshow->status)) ?>
                                </td>
                                <td class="text-center">
                                    {{$slideshow->duration}}秒
                                </td>
                                <td class="text-center">
                                    {{$slideshow->image_count}}
                                </td>
                                <td class="text-center">
                                    {{\App\Service\SlideshowService::changeVideoSizeFormat($slideshow->size)}} MB
                                </td>
                                <td class="text-center">
                                    {{(new DateTime($slideshow->updated_at))->format('Y/m/d H:i')}}
                                </td>
                                <td>
                                    <button class="btn btn-cancel slideshow-edit-btn" data-id="{{$slideshow->id}}" @click="openModal({{$slideshow->id}})"><i class="fa fa-lg fa-pencil"></i>編集する</button>
                                </td>
                                <td>
                                    <a type="button" href="{{URL::route('slideshow_detail', ['slideshowId' => $slideshow->id])}}" class="btn btn-cancel col-xs-12 form-control">
                                        詳細
                                    </a>
                                </td>
                                <td>
                                    @if($slideshow->status < \App\Models\Slideshow::STATUS_UPLOADED)
                                        <a href="javascript:void(0)" data-href="{{URL::route('delete_slideshow', ['slideshowId' => $slideshow->id])}}" data-confirm="ビデオを削除しますか？"><span class="fa fa-lg fa-trash"></span></a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <!-- projects -->
                    @include('templates.pager', ['list' => $slideshows])

                </div>
                <!-- x_content -->
            </div>
            <!-- x_panel -->
        </div>

        <!-- スライドショー作成モーダル  -->
        <slideshow_confirm_vue_component :slideshowimages="slideshowImages" :slideshow="slideshow"></slideshow_confirm_vue_component>
        @include('templates.slideshow_confirm_modal')
    </div>
@stop

@section('script')
    <script src="{{static_file_version('/js/custom/slideshowListPage.js')}}"></script>
    <script src="{{static_file_version('/js/validator/validator.js')}}"></script>
    <script src="https://vjs.zencdn.net/5.20.1/video.js"></script>
    <script>
        var apiGetSlideshowDataUrl = '{{URL::route('api_get_slideshow_data')}}',
            imageListURL = '{{URL::route('image_list')}}',
            slideshowListUrl = '{{URL::route('slideshows')}}',
            baseUrl = '{{URL::to("/")}}';
    </script>
@stop
