@extends('layouts.master')

@section('title')
    Detail
@stop
@section('header')
    <link href="https://vjs.zencdn.net/5.20.1/video-js.css" rel="stylesheet">
    <link rel="stylesheet" href="{{static_file_version('/css/videojs/videojs-skin-color.css')}}">
    <link href="{{static_file_version('/css/select/select2.min.css')}}" rel="stylesheet">
    <style>


    </style>
@stop

@section('content')
    <div class="page-title">
        <div class="title_left">
            <h3>クリエイティブ詳細</h3>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="x_panel" id="app">
        @include('templates.alert')
        <div class="imagedetail-info">
            <div class="col-xs-4">
                @if (!$post->video_url)
                    <div class="imgdetail-box">
                        <img class="img-responsive" v-if="isOffer" v-bind:src="currentImage.url">
                        <img class="img-responsive" v-else src="{{$post->image_url}}">
                    </div>
                    <div class="clearfix"></div>
                    <p class="btn-edit-area" v-cloak v-if="offer.id && isAprroved(offer.status)">
                        <button class="btn btn-default btn-edit"
                                v-on:click="clickEditButton(currentImage.id, currentImage.url)"><i class="fa fa-pencil"
                                                                                                   aria-hidden="true"></i>編集する
                        </button>
                    </p>
                @else
                    <div class="imgdetail-box video-preview-box" style="height: auto">
                        <video controls
                               class="img-responsive video-modal-tmp video-fill-frame video-js vjs-styles-dimensions vjs-big-play-centered vjs-skin-colors-orange vjs-fluid vjs-16-9 hidden"
                               muted>
                            <source src="" type="video/mp4">
                        </video>
                    </div>
                @endif

                    <div v-cloak v-if="offer.id && !offer.video_url">
                        <h2 class="mt40 pl10 h4">作成画像一覧</h2>
                        <div class="imgdetail-area">
                            <div class="js_post_box imgdetail-list" style="margin-bottom: 10px;" v-for="image in imageList">
                                <div class="editimage-list">
                                    <div class="imglist-panel-image -panel-height">
                                        <a href="javascript:void(0)"
                                           v-on:click="setCurrentImage(image.id, image.image_url)"><img class="ugc-img"
                                                                                                        v-bind:src="image.image_url"></a>
                                    </div>
                                    <div class="container mb10">
                                        <div class="col-xs-12">
                                            <p class="text-center imgdetail-list-label-area"><span
                                                        class="label label-synchronis imgdetail-list-label"></span></p>
                                            <div v-if="offer.id && isAprroved(offer.status)">
                                                <a href="javascript:void(0)" class="btn btn-default form-control btn-edit-small"
                                                   v-on:click="clickEditButton(image.id, image.image_url)">
                                                    <i class="fa fa-pencil fa-lg" aria-hidden="true"></i>
                                                </a>
                                                <span data-toggle="tooltip" title="オリジナルの画像は削除できません。"
                                                      v-if="image.id == originImage.id">
                                                <a href="" style="background-color: #d3d3d3"
                                                   class="disabled btn btn-default form-control btn-edit-small btn-trash">
                                                    <i class="fa fa-trash fa-lg" aria-hidden="true"></i>
                                                </a>
                                            </span>
                                                <a v-else v-bind:href="'{{URL::route('remove_edited_image')}}/'+image.id"
                                                   class="btn btn-default form-control btn-edit-small btn-trash">
                                                    <i class="fa fa-trash fa-lg" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center">@{{image.created_at.replace(/\-/g, '/')}}</div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    @include('templates.tw_upload_modal', ['formAction' => URL::route('upload_material_tw'), 'fileFormat' => $post->file_format])
            </div>


            <div class="col-xs-8">
                <table class="table mt10">
                    <tbody>
                    <tr>
                        <td>ユーザー :</td>
                        <td>
                            <div class="imgdetail-autohorname"><a href="{{$post->author_url}}"
                                                                  target="_blank">{{ $post->name ? $post->name : $post->username }}</a></div>
                        </td>
                    </tr>
                    <tr>
                        <td>投稿情報</td>
                        <td><span class="red"><i class="fa fa-heart" aria-hidden="true"></i>{{$post->like}}</span><span
                                    class="post-date"><a href="{{$post->post_url}}"
                                                         target="_blank"> Post</a> : {{format_string_date_time($post->pub_date, 1)}}</span></td>
                    </tr>
                    <tr v-if="isOffer" v-cloak>
                        <td>リクエスト情報</td>
                        <td v-text="offer.created_at.replace(/\-/g, '/')"><span class="hash-tag"># @{{ offer.hashtag }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>投稿フォーマット</td>
                        <td>{{ \App\Models\Post::$label[$post->file_format] }}</td>
                    </tr>
                    </tbody>
                </table>

                <div id="creative_data_tab" class="container" v-cloak>
                    <ul class="nav nav-pills">
                        @if(can_use_ads())
                            <li v-if="isOffer" class="active text-center">
                                <a href="#ad_tab" data-toggle="tab"><strong>SNS広告プラン</strong></a>
                            </li>
                        @endif
                        @if(Session::get('site'))
                            <li class="text-center" :class="{ active: isActive}"
                                v-if="!isOffer  || (originImage && currentImage.id == originImage.id)"
                                v-show="showOwnedTab">
                                <a href="#owned_tab" data-toggle="tab" class="text-center"><strong>オウンドメディアプラン</strong></a>
                            </li>
                        @endif
                    </ul>

                    <div class="tab-content clearfix">
                        @if(can_use_ads())
                            <div class="tab-pane active" id="ad_tab" v-if="isOffer">
                                <table class="table">
                                    <tbody>
                                    <tr class="creative-detail-kpi" v-cloak
                                        v-if="isAprroved(offer.status) && currentKpi.length > 0">
                                        <td>KPI</td>
                                        <td>
                                            <div class="col-md-12" v-for="(kpi, index) in currentKpi"
                                                 v-bind:class="{'edited-image-kpi': index != (currentKpi.length - 1)}">
                                                <div class="kpi sns-btn fix-width-100"
                                                     v-if="kpi.media_type == {{\Classes\Constants::MEDIA_FACEBOOK}}">
                                                    <span class="fa fa-facebook-square"></span>@{{ kpi.name }}
                                                </div>
                                                <div class="kpi sns-btn fix-width-100"
                                                     v-if="kpi.media_type == {{\Classes\Constants::MEDIA_TWITTER}}">
                                                    <span class="fa fa-twitter-square"></span>@{{ kpi.name }}
                                                </div>
                                                <div class="kpi">
                                                    <div class="left">
                                                        CTR
                                                        <span>@{{ numberFormat(kpi.ctr == null ? 0 : kpi.ctr*100, 2) }}%</span>
                                                    </div>
                                                </div>
                                                <div class="kpi">
                                                    <div class="left">
                                                        Spend
                                                        <span>¥@{{ kpi.spend == null ? 0 : numberFormat(kpi.spend, 0) }}</span>
                                                    </div>
                                                </div>
                                                <div class="kpi">
                                                    <div class="left">
                                                        Impressions
                                                        <span>@{{ kpi.imp == null ? 0 : numberFormat(kpi.imp, 0) }}</span>
                                                    </div>
                                                </div>
                                                <div class="kpi">
                                                    <div class="left">
                                                        Click
                                                        <span>@{{ kpi.click == null ? 0 : numberFormat(kpi.click, 0) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-cloak v-if="offer.id">
                                        <td>ステータス</td>
                                        <td>
                                            <div class="col-md-offset-2 col-md-1">
                                            <span class="label p5 status" :class="statusClass"
                                                  v-text="statusLabel">承認</span>
                                            </div>
                                            <div class="col-md-3" v-if="offer.status == STATUS_APPROVED">
                                                <form action="{{URL::route('archive_offer')}}" method="POST"
                                                      id="offer_cancel_form">
                                                    {{csrf_field()}}
                                                    <input type="hidden" name="offer_id" :value="offer.id">
                                                    <input type="hidden" name="post_id" value="{{$post->id}}">
                                                    <button id="offer_cancel_btn"
                                                            class="btn btn-default btn-edit btn-xs"
                                                            style="margin-top: 2px">リクエスト取消
                                                    </button>
                                                </form>

                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-cloak v-if="offer.id && isAprroved(offer.status)">
                                        <td>
                                            メディアに同期
                                        </td>
                                        <td>
                                            <div class="col-md-4">
                                                <form action="{{URL::route('upload_material_fb')}}" id="upload_fb_form"
                                                      method="POST">
                                                    {{csrf_field()}}
                                                    <input type="hidden" name="image_id" v-model="currentImage.id">
                                                    <select class="form-control sns-btn" name="media_account_id"
                                                            data-show-icon="true">
                                                        @foreach($mediaAccounts as $mediaAccount)
                                                            <option data-type="{{$mediaAccount->media_type}}"
                                                                    value="{{$mediaAccount->id}}">{{$mediaAccount->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            </div>
                                            <div class="col-md-3">
                                                <button class="btn btn-Synchronis form-control mt3"
                                                        v-on:click="uploadImage">
                                                    <i class="fa fa-cloud-upload" aria-hidden="true"></i>同期する
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <hr>
                            </div>
                        @endif
                        @if(Session::get('site'))
                            <div class="tab-pane" id="owned_tab" :class="{ active: isActive}"
                                 v-if="!isOffer || (originImage && currentImage.id == originImage.id)">
                                <table class="table">
                                    <tbody>
                                    <tr class="creative-detail-kpi">
                                        <td>KPI</td>
                                        <td>
                                            <div class="col-md-12">
                                                <div class="kpi">
                                                    <div class="left">
                                                        Click
                                                        <span>@{{ ownedImage.click == null ? 0 : ownedImage.click }}</span>
                                                    </div>
                                                </div>
                                                <div class="kpi">
                                                    <div class="left">
                                                        CV
                                                        <span>@{{ownedImage.cv == null ? 0 : ownedImage.cv }}</span>
                                                    </div>
                                                </div>
                                                <div class="kpi">
                                                    <div class="left">
                                                        CVR
                                                        <span>@{{ (!ownedImage.cv || !ownedImage.click) ? 0 : numberFormat(ownedImage.cv / ownedImage.click * 100, 2) }}%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>登録済UGCセット</td>
                                        <td>
                                            <div v-for="part in registeredParts">
                                                <span class="label p5 status ml10" :class="setPartStatusClass(part.data.status)"
                                                      style="border-radius: 1em;">@{{setPartStatusLabel(part.data.status)}}</span>
                                                <a :href="'{{URL::route('part_detail', '')}}/' + part.data.id">@{{ part.data.title }}</a><span
                                                        @click="selectedPartId = part.data.id"
                                                        class="glyphicon glyphicon-remove-sign icon pull-right"
                                                        　data-toggle="modal"
                                                        data-target="#cancel_part_modal"></span></br>
                                                <hr>
                                            </div>
                                            <div v-show="!showAddPartsForm"><span
                                                        class="glyphicon glyphicon-plus-sign icon"
                                                        @click="showAddPartsForm = true"></span>登録UGCセット追加
                                            </div>
                                            <div v-show="showAddPartsForm">
                                                <div class="input-group parts-select">
                                                    <select class="form-control" name="part_id" id="parts_select"
                                                            v-model="partId">
                                                        <option disabled value="">Please select one</option>
                                                        <option v-for="part in unregisterParts" :value="part.data.id">
                                                            @{{ part.data.title }}
                                                        </option>
                                                    </select>
                                                    <span class="input-group-btn">
                                                    <button type="submit" class="btn btn-primary"
                                                            @click="registerPartImage" :disabled="!partId">
                                                        登録する
                                                    </button>
                                                </span>
                                                    <span class="glyphicon glyphicon-minus-sign icon"
                                                          @click="showAddPartsForm = false"></span>
                                                </div>
                                            </div>
                                            </form>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>商品ページリンク</td>
                                        <td>
                                            <div class="owned-product" v-for="product in products">
                                                <div class="row">
                                                    <div class="col-xs-1">
                                                        <img v-bind:src="product.view_product_image_url ? product.view_product_image_url : product.product_image_url"
                                                             width="50px" height="50px">
                                                    </div>
                                                    <div class="col-xs-11">
                                                        @{{ product.view_title ? product.view_title : product.title }}<br>
                                                        <a v-bind:href="product.url">@{{ product.url }}</a>
                                                        <span @click="currentProductUrl = product.url"
                                                              class="glyphicon glyphicon-remove-sign icon pull-right"
                                                              　data-toggle="modal"
                                                              data-target="#cancel_product_modal"></span>
                                                    </div>
                                                </div>
                                                <hr>
                                            </div>
                                            <div>
                                                <span v-if="isExistProduct" class="glyphicon glyphicon-plus-sign icon"
                                                      @click="openLinkProduct"></span>
                                                <span v-else class="glyphicon glyphicon-plus-sign icon" data-toggle="modal" data-target="#add_product_page_modal"></span>
                                                商品ページ紐付を追加 (@{{ products.length
                                                }}/10)
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <hr>
                            </div>

                            @include("templates.image_link_product_modal")
                            @include("templates.add_product_page_modal")
                            <add-product-page-modal></add-product-page-modal>
                        @endif
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>


        <div class="modal fade" id="cancel_product_modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content p10" style="width: 600px">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">×</button>
                        <h4 class="modal-title"><strong>確認してください</strong></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            商品ページ紐付を解除してよろしいですか？
                        </div>
                        <div class="row">
                            <button class="pull-right" data-dismiss="modal" @click="deleteProductLink">OK</button>
                            <button class="pull-right" data-dismiss="modal">キャンセル</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="cancel_part_modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content p10" style="width: 600px">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">×</button>
                        <h4 class="modal-title"><strong>確認してください</strong></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            UGCセット登録を解除してよろしいですか？
                        </div>
                        <div class="row">
                            <button class="pull-right" data-dismiss="modal" @click="deletePartImage">OK</button>
                            <button class="pull-right" data-dismiss="modal">キャンセル</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('templates.edit_image_modal', ['imageUrl' => $post->image_url])



@stop

@section('script')
    <script>
        var getEditedImageKpiApiUrl = '{{URL::route('get_edited_img_kpi', ['imageId' => ''])}}',
            mediaAccountList = [],
            videoUrl = '{{$post->video_url}}',
            postId = '{{$post->id}}',
            imageUrl = '{{$post->image_url}}',
            advertiserId = '{{\Auth::guard('advertiser')->user()->id}}'
            ugcListPageUrl = '{{URL::route('image_list')}}'
            siteId = '{{Session::get('site') ? Session::get('site')->id : null}}',
            getAllProductsUrl = '{{URL::route('api_get_all_product')}}',
            apiRegisterPartUrl = '{{URL::route('api_register_image_part')}}',
            deleteProductImageUrl = '{{URL::route('api_delete_product_image')}}',
            deletePartImageUrl = '{{URL::route('cancel_part')}}';
            getPartImageDetailUrl = '{{URL::route('api_get_part_image_detail', ['postId' => ''])}}';
            getVtdrImgDetailUrl = '{{URL::route('api_get_vtdr_img_detail', ['postId' => ''])}}';
            getOfferDetailUrl = '{{URL::route('api_get_offer_detail', ['id' => '%s'])}}';
            getRegisteredPartUrl = '{{URL::route('api_get_registered_part', ['id' => '%s'])}}';
            getAllProductsUrl = '{{URL::route('api_get_all_product')}}';
        @foreach($mediaAccounts as $mediaAccount)
            mediaAccountList['{{$mediaAccount->id}}'] = {{$mediaAccount->media_type}};
        @endforeach
    </script>
    <script src="{{static_file_version('/js/custom/videojs.js')}}"></script>
    <script src="{{static_file_version('/js/select/select2.full.js')}}"></script>
    <script src="{{static_file_version('/js/validator/validator.js')}}"></script>
    <script src="https://vjs.zencdn.net/5.20.1/video.js"></script>
    <script src="{{static_file_version('/js/custom/offerDetail.js')}}"></script>
@stop