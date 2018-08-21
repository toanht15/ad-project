@extends('layouts.master')

@section('title')
    UGCセットー覧
@stop

@section('header')
    <link rel="stylesheet" href="{{static_file_version('/css/app/part_list.css')}}">
@stop

@section('content')
    <div class="page-title">
        <div class="title_left">
            <h3>UGCセット一覧</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="x_panel">
                @if(count($parts))
                    <div class="x_content">
                        @include('templates.alert')
                        <div class="nocrowing-area">
                            <div class="text-left">
                                <div>
                                    @if($can_create_part)
                                        <button type="button" class="btn btn-danger mt15 add-parts-btn"
                                                id="add-parts-btn">新規UGCセット作成
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- nocrowing-area -->

                        <table class="table table-striped projects mt40 hashtag-list video-list-table">
                            <thead>
                            <tr>
                                <th class="col-lg-4">UGCセット名</th>
                                <th class="col-lg-2">UGCセットタイプ</th>
                                <th class="col-lg-2">ステータス</th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th class="col-lg-1"></th>
                                <th class="col-lg-1"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($parts as $part)
                                <tr id="part-{{$part->id}}">
                                    <td class="ugcSetTitle">
                                        <a href="{{URL::route('part_detail', $part->id)}}">{{$part->title}}</a>
                                    </td>
                                    <td>
                                        {{$part->fields['template']->str}}表示
                                    </td>
                                    <td>
                                        {{$part->fields['status']->str}}
                                    </td>
                                    <td>
                                        <div type="button" class="btn mt15" data-part-id="{{$part->id}}"
                                             data-toggle="modal"
                                             data-target="#ugc_select_modal"><span
                                                    class="glyphicon glyphicon-picture fa-lg" aria-hidden="true"></span><br>
                                            <small>UGC追加</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div type="button" class="btn mt15"
                                             onclick="partList.openDetailModal({{$part->id}})"><span
                                                    class="glyphicon glyphicon-pencil fa-lg"
                                                    aria-hidden="true"></span><br>
                                            <small>編集</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div type="button" class="btn mt15" data-toggle="modal"
                                             data-part-id="{{$part->id}}"
                                             data-target="#preview_modal"><span
                                                    class="glyphicon glyphicon-new-window fa-lg"
                                                    aria-hidden="true"></span><br>
                                            <small>プレビュー</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($part->status == 3)
                                            <div type="button" class="btn btn-danger mt15" data-toggle="modal"
                                                 data-part-id="{{$part->id}}"
                                                 data-target="#publish_part_modal">
                                                公開
                                            </div>
                                        @else
                                            <div type="button" class="btn mt15" disabled="disabled">
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isAdmin)
                                            <div type="button" class="btn mt15 delete" id="{{$part->id}}">
                                                <span class="glyphicon glyphicon-trash fa-lg" aria-hidden="true"
                                                      id="{{$part->id}}"></span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <!-- projects -->

                    </div>
                @else
                    <div class="x_content">
                        @include('templates.alert')
                        <div class="nocrowing-area">
                            <div class="text-center">
                                <div class="">
                                    <h4>UGCを登録して表示させるUGCセットがまだありません。</h4>
                                </div>
                                <div class="">
                                    <h5>まずはUGCセットを作成しましょう。</h5>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-danger mt15 middle1 add-parts-btn"
                                            id="add-parts-btn">
                                        新規UGCセット作成
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
            @endif
            <!-- x_content -->
                <div class="x_content">
                    <div @if(! count($parts))class="text-center" @endif>
                        <div>
                            <h4><b>商品ページの登録</b></h4>
                        </div>
                        <div>
                            <h5>商品ページを追加すると、各UGCのモーダル内に商品ページのリンクを設定できるようになります。</h5>
                        </div>
                        <div>
                            @if(count($pages))
                                <a href="{{URL::route('page_list')}}" class="btn btn-product"
                                   　style="border-color: #d65801 !important;">商品ページ一覧</a>
                            @else
                                <div type="button" class="btn btn-detail" data-toggle="modal"
                                     　style="border-color: #d65801 !important;"
                                     data-target="#add_product_page_modal">商品ページ一覧

                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>


            <!-- x_panel -->
        </div>


        <!-- /puslish_part_modal -->

        <div id="part_modals">
            <create_part_template :redirecturl="'{{URL::route('part_list')}}'"></create_part_template>
            <part_detail_setting_template :partid="currentPartId" :status="part.status"></part_detail_setting_template>
            <publish_part_modal v-bind:part="part" v-on:publish="reload"></publish_part_modal>
            <select_ugc_modal v-bind:search_conditions="search_conditions"
                              v-bind:existed_items="existed_items"
                              v-on:ugc_register="update_image"
                              v-bind:part="part"></select_ugc_modal>

            <add-product-page-modal></add-product-page-modal>
        </div>

        <!-- /preview_modal -->
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
        <!-- /preview_modal -->
    </div>

    @include("templates.part.create_part")
    @include("templates.part.part_detail_setting")
    @include("templates.part.ugc_select_modal")
    @include("templates.part.publish_part_modal")
    @include("templates.add_product_page_modal")
@stop

@push('push-script')
    <script>
        var part_detail = '{{URL::route('api_part_detail', '%s')}}';
        var searchConditionList = {!! json_encode($searchConditionList->toArray()) !!};
        var parts_data = {!! json_encode($parts_data) !!};
        var part_list_api = '{{URL::route('api_part_list')}}';
        var part_kpi = '{{URL::route('part_kpi', '%s')}}';
        var web_part_detail = '{{URL::route('part_detail', '%s')}}';
        var api_part_update_sort_value = '{{URL::route('api_part_update_sort_value', '%s')}}';
        var apiRegisterImage = '{{URL::route('register_images_parts')}}';
        var api_get_images = '{{ URL::route('api_get_images') }}';
        var api_publish_part = '{{URL::route('api_publish_part', '%s')}}';

        $('#preview_modal').on('show.bs.modal', function (e) {
            var url = $('#preview_url').val();
            //get data-id attribute of the clicked element
            var part_id = $(e.relatedTarget).data('part-id');
            $('#preview_button').click(function (e) {
                var url = $('#preview_url').val();
                if (url.indexOf('?') != -1) {
                    url += '&vtdr_preview_parts_id=' + part_id;
                } else {
                    url += '?vtdr_preview_parts_id=' + part_id;
                }
                window.open(url);
            })
        });
    </script>
    <script type="text/javascript" src="{{static_file_version('/js/moment/moment.min.js')}}"></script>
    <script type="text/javascript" src="{{static_file_version('/js/custom/part/part.js')}}"></script>
    <script type="text/javascript" src="{{static_file_version('/js/custom/part/ugcSelectModal.js')}}"></script>
    <script type="text/javascript" src="{{static_file_version('/js/custom/part/publishModal.js')}}"></script>
    <script src="{{static_file_version('/js/custom/part/partList.js')}}"></script>
    <script src="{{static_file_version('/js/custom/part/newPartModal.js')}}"></script>
@endpush
