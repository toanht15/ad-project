@extends('layouts.master')

@section('title')
    UGC一覧
@stop

@section('header')
    <link rel="stylesheet" href="{{asset('/css/introjs.css')}}">
    <link rel="stylesheet" href="{{static_file_version('/css/lightslider.css')}}">
    <link rel="stylesheet" href="{{static_file_version('/css/sliderCustom.css')}}">
    <link rel="stylesheet" href="{{static_file_version('/css/app/imagelist.css')}}">
    <link rel="stylesheet" href="{{static_file_version('/bower_components/bootstrap-multiselect/dist/css/bootstrap-multiselect.css')}}">
    <link href="https://vjs.zencdn.net/5.20.1/video-js.css" rel="stylesheet">
    <link rel="stylesheet" href="{{static_file_version('/css/videojs/videojs-skin-color.css')}}">
    <style>
        .jscroll-next {
            display: block;
            clear: both;
        }
    </style>
@stop

@section('content')
    <?php $offerSetId = app('request')->input('offerset_id') ?>
    <div id="ImageListBody">
        <div class="page-title">
            <div class="title_left">
                <h3> UGC一覧</h3>
            </div>
        </div>
        @if ($isAdmin)
            {{--<button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#delete_facebook_image">同期した画像を削除</button>--}}
            <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#get_facebook_image">Facebookライブラリーから取得</button>
        @endif
        <div class="clearfix"></div>
        <div class="row" id="imageListApp" v-cloak>
            <div class="col-xs-12">
                <div class="x_panel">
                    <div class="x_content">
                        @include('templates.alert')
                        <div class="row">
                            <ul class="hash-list">
                                <li @if (!isset($currentSearchCondition)) :class="{'current-tag': searchConditionId == 0}" @endif v-on:click="searchConditionId = 0">
                                    <a href="javascript:void(0)">ALL({{number_format($allUgcCount)}})</a>
                                </li>
                                @foreach($searchConditionList as $index => $searchCondition)
                                    <?php if ($index > 4) break; ?>
                                <li class="@if(isset($currentSearchCondition) && $currentSearchCondition->id == $searchCondition['id']) current-tag @endif @if($searchCondition['id'] == $defaultSearchCondition->id) recommend-off @endif"
                                    v-on:click="searchConditionId = {{$searchCondition['id']}}" :class="{'current-tag': searchConditionId == {{$searchCondition['id']}} }">
                                    <a href="javascript:void(0)">{{$searchCondition['title']}}(<span class="total-count">{{number_format($searchCondition['post_count'])}}</span>)</a>
                                </li>
                                @endforeach
                            </ul>
                            <div class="btn-area-hashlist">
                                <button type="button" class="btn btn-danger pull-right" id="add-hashtag-btn" data-toggle="modal" data-target="#create_hashtag_modal">ハッシュタグを登録する</button>
                            </div>
                            <div class="clearfix"></div>
                            @if(count($searchConditionList) > 5)
                            <div class="acordion" style="display: none;">
                                <ul class="hash-list">
                                    @for($i = 5; $i < count($searchConditionList); $i++)
                                        <?php $tag = $searchConditionList[$i] ?>
                                        <li class="@if(isset($currentSearchCondition) && $currentSearchCondition->id == $tag['id']) current-tag @endif @if($tag['id'] == $defaultSearchCondition->id) recommend-off @endif"  v-on:click="searchConditionId = {{$tag['id']}}">
                                            <a href="javascript:void(0)">{{ $tag['title']}}({{$tag['post_count']}})</a>
                                        </li>
                                    @endfor
                                </ul>
                            </div>

                            <a href="" class="allHash pl10 mb30"><i class="fa fa-plus-circle" aria-hidden="true"></i>全てのハッシュタグを表示する</a>
                            @endif

                            <div class="clearfix"></div>

                            <div class="form-group sort-area mb20">
                                絞り込み :
                                <div class="radio-inline">
                                    <input type="radio" value="-1" name="status" v-model="status" id="status_all" checked>
                                    <label for="status_all">ALL</label>
                                </div>
                                <div class="radio-inline">
                                    <input type="radio" value="{{\App\Models\Offer::STATUS_COMMENTED}}" v-model="status" name="status" class="status_apply" id="status_apply">
                                    <label for="status_apply">リクエスト中</label>
                                </div>
                                <div class="radio-inline">
                                    <input type="radio" value="{{\App\Models\Offer::STATUS_APPROVED}}" v-model="status" name="status" class="status_approval" id="status_approval">
                                    <label for="status_approval">承認済</label>
                                </div>
                                <div class="radio-inline">
                                    <input type="radio" value="{{\App\Models\Offer::STATUS_LIVING}}" v-model="status" name="status" class="status_living" id="status_living">
                                    <label for="status_living">出稿済</label>
                                </div>
                                <div class="radio-inline">
                                    <input type="radio" value="{{\App\Models\Offer::STATUS_COMMENT_FALSE}}" v-model="status" name="status" class="status_failed" id="status_failed">
                                    <label for="status_failed">リクエスト失敗</label>
                                </div>
                                @if(can_use_ugc_set())
                                <div class="radio-inline">
                                    <div class="filter-by-part mt5">
                                        <input type="radio" value="{{\App\Models\Offer::STATUS_REGISTERED_PART}}" v-model="status" id="part_linked" name="status">
                                        <label for="part_linked">UGCセット登録済</label>
                                    </div>
                                    <div v-if="partList != null" class="floatleft" style="max-width: 200px">
                                        <select class="form-control" :disabled="partList == null || status != {{\App\Models\Offer::STATUS_REGISTERED_PART}}" v-model="filterPartId">
                                            <option value="0">UGCセットを選択</option>
                                            <option v-for="part in partList" :value="part.id">@{{ part.title }}</option>
                                        </select>
                                    </div>
                                </div>
                                @endif
                                <div class="radio-inline">
                                    <input type="radio" value="{{\App\Models\Offer::STATUS_ARCHIVE}}" v-model="status" name="status" class="status_archive" id="status_archive">
                                    <label for="status_archive">非表示</label>
                                </div>
                                <div class="sort-list2">
                                    並び替え :
                                    <div class="radio-inline"><input type="radio" v-model="sort" name="sort" value="new" id="new_sort" class="new_sort" checked /><label for="new_sort">新着投稿順</label></div>
                                    <div class="radio-inline"><input type="radio" v-model="sort" name="sort" value="like" id="like_sort" class="like_sort" /><label for="like_sort">Like数順</label></div>
                                </div>
                                <div class="clearfix"></div>
                                <!-- /.sort-list2 -->
                            </div>
                            <div class="clearfix"></div>

                            <div class="nocrowing-area">
                                {{--<div class="hidden no-offer">--}}
                                    {{--<p class="text-center nocrowing-lead2 p0">--}}
                                    {{--まだオファーをしていません。<br>UGCを選択してオファーを申請してみましょう!</p>--}}
                                    {{--<p class="offer-arrow">--}}
                                        {{--<img src="/images/icon-arrow-down.png" height="23" width="26" alt="" class="scroll">--}}
                                    {{--</p>--}}
                                {{--</div>--}}
                                <div class="no-image-message" v-if="!isLoadingImage && status == {{\App\Models\Offer::STATUS_APPROVED}} && Object.keys(imageList).length == 0">
                                    <p class="text-center nocrowing-lead2 p0">承認済みのUGCはまだありません。<br>UGCを選択してリクエストしてみましょう!</p>
                                    {{--<p class="offer-arrow">--}}
                                        {{--<img src="/images/icon-arrow-down.png" height="23" width="26" alt="" class="scroll">--}}
                                    {{--</p>--}}
                                </div>
                            </div>

                            <div class="no-image-message" v-if="!isLoadingImage && status != {{\App\Models\Offer::STATUS_APPROVED}} && Object.keys(imageList).length  == 0">
                                <p class="h1 text-center">Whoops<i class="fa fa-tint" aria-hidden="true"></i></p>
                                <div class="nocrowing-lead text-center">UGCが見つかりませんでした</div>
                            </div>

                            <div role="tabpanel" class="imglist-area" v-cloak>
                                <div class="clearfix"></div>
                                @include('templates.image_list')
                            </div>

                            <div class="btn-offer col-md-12 no-padding">
                                <div class="col-md-4 imglist-main-btn-left-div">
                                    <div class="radio-inline">
                                        <input type="radio" value="1" v-model="actionMode" name="main_action_type" class="custom-radio" id="make_offer_radio" checked>
                                        <label for="make_offer_radio" class="text-normal">リクエスト</label>
                                    </div>
                                    @if(can_use_ugc_set())
                                    <div class="radio-inline">
                                        <input type="radio" value="2" v-model="actionMode" name="main_action_type" class="custom-radio" id="make_part_radio" :disabled="partList == null">
                                        <label for="make_part_radio" class="text-normal">UGCセット登録</label>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    <button type="button" v-if="actionMode == 1" class="btn btn-danger big-btn" :class="{disabled: !enableOfferBtnFlg}" data-intro="選択したUGCの投稿者に対し、リクエストを送信します。" data-step="3" data-position="top" data-target="#js_images_comfirm_tmp" id="jsShowOfferBtn" data-toggle="modal">
                                        <i class="fa fa-paper-plane"></i>リクエストする<span class="offer_selected_count" v-if="enableOfferBtnFlg">(@{{ selectedImageIds.length }})</span>
                                    </button>
                                    <div v-if="actionMode == 2 && partList.length > 0">
                                        <div class="col-md-3">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="col-md-10 no-padding">
                                                <select id="select_parts" v-model="selectedPartIds" multiple="multiple" v-if="partList != null" :disabled="!enableSelectPartBtnFlg">
                                                    <option v-for="part in partList" :value="part.id">@{{ part.title }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <button class="btn btn-danger big-btn" :disabled="!enableSelectPartBtnFlg" v-on:click="showConfirmPartLink()">確認<span v-if="selectedImageIds.length != 0">(@{{ selectedImageIds.length }})</span></button>
                                            </div>
                                            <div class="col-md-10 no-pa">

                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" v-if="actionMode == 2 && partList.length == 0" class="btn btn-danger big-btn" data-target="#create_part_modal" data-toggle="modal">
                                        UGCセットを作成
                                    </button>
                                </div>
                                <div class="col-md-4 imglist-main-btn-right-div">
                                    <div class="small-hide-btn" id="undo_archive_area" v-if="status == {{\App\Models\Offer::STATUS_ARCHIVE}}">
                                        <button type="submit" form="archive_form" class="btn small-btn archive_btn archive_undo_btn" :disabled="!enableUnArchiveBtnFlg">
                                            <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
                                            <div><span class="glyphicon-class archive_selected_count text-normal" v-if="enableUnArchiveBtnFlg">表示(@{{ selectedImageIds.length }})</span></div>
                                            <div><span class="glyphicon-class archive_selected_count text-normal" v-if="!enableUnArchiveBtnFlg">表示</span></div>
                                        </button>
                                    </div>
                                    <div class="small-hide-btn" id="do_archive_area" v-if="status != {{\App\Models\Offer::STATUS_ARCHIVE}}">
                                        <button type="submit" form="archive_form" class="btn small-btn archive_btn archive_do_btn" :disabled="!enableArchiveBtnFlg">
                                            <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
                                            <span class="glyphicon-class archive_selected_count text-normal" v-if="enableArchiveBtnFlg">非表示(@{{ selectedImageIds.length }})</span>
                                            <span class="glyphicon-class archive_selected_count text-normal" v-if="!enableArchiveBtnFlg">非表示</span>
                                        </button>
                                    </div>

                                    <div class="small-slideshow-btn">
                                        @if(can_use_ads())
                                        <button  class="btn small-btn" id="make-slideshow-btn" data-toggle="modal" data-target="#js_images_slide_show" onclick="ga('send', 'event', 'slideshow', 'create', '{{$advertiser->id}}');"
                                                 :disabled="!enableSlideshowBtnFlg" v-if="actionMode == 1">
                                            <img id="slideshow_btn_icon" data-toggle="tooltip" data-container="#make-slideshow-btn" data-placement="top" title="承認済みのUGCを複数選び、スライドショー（正方形サイズ、Instagram Storiesサイズ）を作成できます。（10枚まで）" src="{{static_file_version('/images/icon_slideshow_btn.png')}}">
                                            <span class="glyphicon-class slideshow_img_selected_count" v-if="!enableSlideshowBtnFlg">スライドショー作成</span>
                                            <span class="glyphicon-class slideshow_img_selected_count" v-else>スライドショー作成(@{{ selectedImageIds.length }})</span>
                                        </button>
                                        @endif
                                        <button  class="btn small-btn" @click="openLinkProduct" onclick="ga('send', 'event', 'link product', 'create', '{{$advertiser->id}}');" v-if="actionMode == 2" :disabled="selectedImageIds.length == 0">
                                            <span class="glyphicon glyphicon-link" aria-hidden="true"></span>
                                            <span class="glyphicon-class slideshow_img_selected_count">商品ページ紐付<span v-if="selectedImageIds.length > 0">(@{{ selectedImageIds.length }})</span></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- btn-offer -->
                            <div class="clearfix"></div>
                        </div>
                        <!-- row -->
                        @if($isAdmin)
                        <div>
                            <a class="btn btn-info form-control middle1" onclick="$('#upload_file').click()">アップロード</a>
                            <form method="POST" action="{{URL::route('upload_image')}}" id="uploadImageForm" enctype="multipart/form-data">
                                {{csrf_field()}}
                                <input type="file" name="images[]" id="upload_file" class="hidden" multiple accept="image/*">
                            </form>
                        </div>
                        @endif
                    </div>
                    <!-- x-content -->
                </div>
                <!-- x-panel -->
            </div>
            <!-- big image Modal -->
            @include('templates.big_image_modal_vue', ['showAction' => true])
            <!-- imglist confirm Modal -->
            @include("templates.offer_confirm_modal")
            <!-- part link confirm modal -->
            @include("templates.register_part_image_confirm_modal")
            <!-- image link product modal -->
            @include("templates.image_link_product_modal")
            <!-- スライドショー作成モーダル  -->
            @if(can_use_ads())
                @include('templates.slideshow_confirm_modal')
                <slideshow_confirm_vue_component :slideshowimages="selectedImages" :slideshow="slideshow"></slideshow_confirm_vue_component>
            @endif
            @if(can_use_ugc_set())
            <!-- Create Part Modal -->
                @include("templates.part.create_part")
                <create_part_template :redirecturl="'{{URL::route('image_list')}}'"></create_part_template>
            @endif
            <form class="hidden archive_form" id="archive_form" method="POST" :action="[status == {{\App\Models\Offer::STATUS_ARCHIVE}} ? '{{URL::route('un_archive')}}' : '{{URL::route('archive')}}']">
                {{csrf_field()}}
                <input type="hidden" name="archive_ids" value="" v-model="selectedPostIdsStr">
            </form>
        </div>
        <!-- row -->
    </div>

    <div id="create_hashtag_modal" class="modal fade">
        <div class="modal-dialog modal-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title ">集めたいUGCのハッシュタグを登録してみましょう!</h4>
                </div>
                <div class="modal-body text-center">
                    <form class="form-inline mt20" role="form" action="{{URL::route('store_hashtag')}}" method="POST" id="add_new_hashtag">
                        {{csrf_field()}}
                        <div class="input-form" id="newHashtagForm">
                            <div class="form-group item">
                                <label class="auto-modal-hash">#</label>
                                <input type="text" name="hashtags[]" class="form-control hashtag-input auto-modal-hash-input" required="required">
                            </div>
                        </div>
                        <div id="add_new_hashtag_input">
                            <i class="fa fa-plus-circle fa-lg mt20 fontawesome-add-icon" aria-hidden="true"></i>追加
                        </div>
                        <div>
                            <button type="button" id="create_hashtag_btn" class="btn btn-danger mt20" onclick="ga('send', 'event', 'add_hashtag', 'ugc_all', '{{$advertiser->id}}');
">登録する</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($isAdmin)
        <!-- big image Modal -->
        <div id="delete_facebook_image" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <form class="mt20" role="form" method="POST" action="{{URL::route('delete_facebook_image')}}">
                            {{csrf_field()}}
                            <div class="form-group mb20">
                                <select name="media_account_id" class="form-control">
                                    @foreach($mediaAccountList as $mediaAccount)
                                        @if($mediaAccount->media_type == \Classes\Constants::MEDIA_FACEBOOK)
                                        <option value="{{$mediaAccount->id}}">{{$mediaAccount->name}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <textarea name="hashcode_list" rows="10" cols="50" style="font-size: 14px" class="form-control" placeholder="HashCode"></textarea>
                                <p><small class="text-danger">※画像を削除するとリクエスト履歴と実績も削除します。</small></p>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-danger mt20">削除する</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- big image Modal -->
        <div id="get_facebook_image" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <form class="mt20" role="form" method="POST" action="{{URL::route('get_facebook_image')}}">
                            {{csrf_field()}}
                            <div class="form-group mb20">
                                <select name="media_account_id" class="form-control">
                                    @foreach($mediaAccountList as $mediaAccount)
                                        @if($mediaAccount->media_type == \Classes\Constants::MEDIA_FACEBOOK)
                                        <option value="{{$mediaAccount->id}}">{{$mediaAccount->name}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <textarea name="hashcode_list" rows="10" cols="50" style="font-size: 14px" class="form-control" placeholder="HashCode"></textarea>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-danger mt20">取得する</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    @endif
@stop

@section('script')
    <script src="{{static_file_version('/js/custom/image/customLazyLoad.js')}}"></script>
    <script src="{{static_file_version('/js/intro.js')}}"></script>
    <script src="{{static_file_version('/bower_components/bootstrap-multiselect/dist/js/bootstrap-multiselect.js')}}"></script>
    @if ($slideshowId)
        <script>
            var slideshowId = {{$slideshowId}},
            apiGetSlideshowDataUrl = '{{URL::route('api_get_slideshow_data')}}';
        </script>
    @endif
    @if (can_use_ugc_set())
        <script>
            var getAllProductsUrl = '{{URL::route('api_get_all_product')}}',
                getPartListApiUrl = '{{URL::route('api_get_all_part')}}';
        </script>
    @endif
    @if(can_use_ads())
        <script> var hasAdsContract = true;</script>
    @endif
    <script>
        var getImageAPIBaseUrl = '{{URL::route('api_get_images')}}',
            slideshowListUrl = '{{URL::route('slideshows')}}',
            completeTutorialURL = '{{URL::route('api_complete_tutorial')}}',
            baseUrl = '{{URL::to("/")}}',
            currentSearchConditionId = '{{$currentSearchCondition ? $currentSearchCondition->id : 0}}',
            currentStatus = '{{$status ? $status : -1}}',
            loadAsync = {{$loadAsync ? 1 : 0}},
            ugcPerPage = {{\App\Http\Controllers\ImageController::UGC_PER_PAGE}},
            isCompletedTutorial = {{$advertiser->completed_tutorial_flg ? 1 : 0}},
            advertiserId = '{{$advertiser->id}}';
    </script>
    @if($shouldShowTutorial && can_use_ads())
        <script src="{{static_file_version('/js/custom/image/intro.js')}}"></script>
    @endif

    <script src="{{static_file_version('/js/custom/videojs.js')}}"></script>
    <script src="{{static_file_version('/js/validator/validator.js')}}"></script>
    <script src="{{static_file_version('/js/custom/offerOperator.js')}}"></script>
    <script src="{{static_file_version('/js/custom/formValidator.js')}}"></script>
    <script src="{{static_file_version('/js/lightslider.js')}}"></script>
    <script src="{{static_file_version('/js/custom/image/imagesPage.js')}}"></script>
    <script src="https://vjs.zencdn.net/5.20.1/video.js"></script>

    <script>
        @if($isAdmin)
        $('#upload_file').change(function() {
            $('#uploadImageForm').submit();
        });
        @endif

    </script>
@stop
