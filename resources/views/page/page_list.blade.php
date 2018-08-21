@extends('layouts.master')

@section('title')
    商品ページ一覧
@stop

@section('header')
    <link rel="stylesheet" href="{{static_file_version('/css/app/page_list.css')}}"
          xmlns:v-on="http://www.w3.org/1999/xhtml" xmlns:v-on="http://www.w3.org/1999/xhtml">
@stop

@section('content')
    <div class="row app">
        <div class="col-xs-12">
            <div class="x_panel">
                <div class="x_content">
                    @include('templates.alert')
                    <div class="nocrowing-area clearfix">
                        <div class="left">
                            <form action="{{URL::route('page_list')}}" method="GET">
                            <div class="input-group stylish-input-group w400">
                                    <input type="text" class="form-control" id="searchInput" value="{{$search}}"  name="search" placeholder="URL・商品名・ページタイトルの一部で検索" >
                                    <span class="input-group-addon">
                                        <button type="submit" id="searchButton">
                                            <span class="glyphicon glyphicon-search"></span>
                                        </button>
                                    </span>
                            </div>
                            </form>
                        </div>
                        <div class="right">
                            <button type="button" class="btn btn-danger" id="add_product_page_btn"
                                    data-toggle="modal" data-target="#add_product_page_modal">商品ページを追加
                            </button>
                        </div>
                    </div>

                    <!-- nocrowing-area -->
                    <div class="row justify-content-md-center mt40">
                        <div class="row item" v-for="item in items">
                            <div class="col-lg-2 col-lg-offset-1">
                                <div class="item-image-modal">
                                    <img alt="" data-src="holder.js/140x140"
                                         v-bind:src="item.image"
                                         data-holder-rendered="true">
                                </div>
                            </div>
                            <template v-if="item.editing">
                                <div class="col-lg-6">
                                    <div class="row item-detail">
                                        <div class="col-md-2"><kbd v-bind:class="{ stopped: item.unsuccessful}">@{{item.status_str}}</kbd>
                                        </div>
                                        <div class="col-md-10"><a v-bind:href="item.url">@{{ item.url }}</a></div>
                                    </div>

                                    <div class="row item-detail">
                                        <div class="col-md-2">商品名</div>
                                        <div class="col-md-10">
                                            <input class="form-control bordered" v-model="item.title"
                                                   placeholder="商品名">
                                            <template v-if="item.hasError('title')">
                                                <span class="red form-error"
                                                      v-for="error in item.validateMessages.title">@{{error}}</span>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="row item-detail">
                                        <div class="col-md-2">画像URL <span class="glyphicon glyphicon-question-sign"
                                                                          data-toggle="tooltip" data-placement="top"
                                                                          title="Web上に公開されている画像のURLを入れることで、商品画像を表示させることができます。"
                                                                          aria-hidden="true"></span></div>
                                        <div class="col-md-10">
                                            <input class="form-control bordered" v-model="item.image"
                                                   placeholder="画像のURLをして入力してください">
                                            <template v-if="item.hasError('image')">
                                                <span class="red form-error"
                                                      v-for="error in item.validateMessages.image">@{{error}}</span>
                                            </template>
                                        </div>
                                    </div>
                                    <hr>
                                    <div v-if="item.loading_status">
                                        <div class="row">
                                            <i class="fa fa-spinner fa-spin"
                                               style="font-size:10px; margin:0px ; padding: 0px"></i>
                                        </div>
                                    </div>
                                    <div v-else-if="item.images.length > 0" class="row">
                                        <div class="col-md-2">
                                            <div class="row item-detail">登録済UGC</div>
                                        </div>
                                        <div class="col-md-8 testimonial-group">
                                            <div class="row">
                                                <div class="col-xs-2 show-image" v-for="image in item.images">
                                                    <img alt="64x64" v-bind:src="image.url" class="media-object"
                                                         style="width: 48px; height: 48px;" data-holder-rendered="true">
                                                    <div type="button" v-on:click="onDelete(item, image, $event)"
                                                         class="btn"><span
                                                                class="glyphicon glyphicon-remove-circle blue"
                                                                aria-hidden="true"></span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-2">
                                    <button type="button" v-on:click="onClick(item, $event)" class="btn mt15 update"
                                            id="add-parts-btn"> 保存
                                    </button>

                                    <button type="button" v-on:click="toggleItemStatus(item, $event)" id="add-parts-btn" class="btn mt15 btn-detail">キャンセル</button>
                                </div>
                            </template>


                            <template v-else>
                                <div class="col-lg-6">
                                    <div class="row item-detail">
                                        <div class="col-md-2"><kbd v-bind:class="{ stopped: item.unsuccessful}">@{{item.status_str}}</kbd>
                                        </div>
                                        <div class="col-md-10"><a target="_blank" v-bind:href="item.url">@{{ item.url
                                                }}</a></div>
                                    </div>

                                    <div class="row item-detail">
                                        <div class="col-md-2">商品名</div>
                                        <div class="col-md-10">@{{item.title}}</div>
                                    </div>
                                    <hr>
                                    <div v-if="item.loading_status">
                                        <div class="row">
                                            <i class="fa fa-spinner fa-spin"
                                               style="font-size:10px; margin:0px ; padding: 0px"></i>
                                        </div>
                                    </div>
                                    <div v-else-if="item.images.length > 0" class="row">
                                        <div class="col-md-2">
                                            <div class="row item-detail">登録済UGC</div>
                                        </div>
                                        <div class="col-md-8 testimonial-group">
                                            <div class="row">
                                                <div class="col-xs-2 show-image" v-for="image in item.images">
                                                    <img alt="64x64" v-bind:src="image.url" class="media-object"
                                                         style="width: 48px; height: 48px;" data-holder-rendered="true">
                                                    <div type="button" v-on:click="onDelete(item, image, $event)"
                                                         class="btn"><span
                                                                class="glyphicon glyphicon-remove-circle blue"
                                                                aria-hidden="true"></span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-lg-1">
                                    <button type="button" class="btn mt15" id="add-parts-btn"
                                            v-on:click="toggleItemStatus(item, $event)"><span aria-hidden="true"
                                                                                     class="glyphicon glyphicon-pencil fa-lg"></span><br>
                                        <small>編集</small>
                                    </button>
                                </div>
                            </template>

                        </div>
                    </div>

                    <div class="text-align-center">
                        <div>
                            {{$response->total()}}件中{{ ($response->currentPage() - 1) * $response->perPage() + 1}}
                            件～{{( $response->currentPage() - 1) * $response->perPage() + $response->count()}}件表示しています
                        </div>
                        <div>
                            {{ $response->links() }}
                        </div>
                    </div>


                </div>
                <!-- x_content -->
            </div>
            <!-- x_panel -->

            @include("templates.add_product_page_modal")
            <add-product-page-modal></add-product-page-modal>
        </div>
    </div>
    <script type="text/x-template" id="on_preview">
        </script>

@stop

@push('push-script')
    <script type="application/javascript">
        var data = {!! json_encode($products['data']) !!};
        var apiPageDetail = '{{URL::route('api_page_detail', '%s')}}';
        var apiDeleteImage = '{{URL::route('api_page_delete_image', '%s')}}';
        var baseUrl = '{{URL::route('page_list')}}';
    </script>
    <script src="{{static_file_version('/js/custom/page/pageList.js')}}"></script>
@endpush
