@extends('layouts.master')

@section('title') ハッシュタグ設定 @stop

@section('content')
    <div class="page-title">
        <div class="title_left">
            <h3>ハッシュタグ設定</h3>
        </div>
    </div>
    <div class="clearfix"></div>

    <div class="row">
        <div class="col-xs-12">
            <div class="x_panel">
                <div class="x_content">
                    @include('templates.alert')
                    <div class="nocrowing-area">
                        @if((count($searchConditionList) - 1) < $maxCount)
                            <div class="text-center">
                                <form class="form-inline" method="POST" action="{{URL::route('store_hashtag')}}" role="form" id="add-hashtag-form">
                                    {{ csrf_field() }}
                                    <div class="hashtag-input-group">
                                        <div class="hashtag-input-item">
                                            <br>
                                            <div class="form-group item">
                                                <label class="nocrowing-modal-hash">#</label>
                                                <input type="text" name="hashtags[]" required="required" class="form-control nocrowing-hash-input" placeholder="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="new_input_btn">
                                        <i class="fa fa-plus-circle fa-lg mt20 fontawesome-add-icon" aria-hidden="true"></i>追加
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-danger mt15" id="add-hashtag-btn" onclick="ga('send', 'event', 'add_hashtag', 'hashtag_all', '{{\Auth::guard('advertiser')->user()->id}}');
">登録する</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                    <!-- nocrowing-area -->

                    <table class="table table-striped projects mt40 hashtag-list">
                        <thead>
                        <tr>
                            <th>ハッシュタグ</th>
                            <th>UGC数</th>
                            <th>Offer</th>
                            <th>承認済</th>
                            <th>UGC on Live</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($searchConditionList as $item)
                            <?php if ($item->id == $defaultSearchCondition->id && !$isAdmin) continue;?>
                        <tr class="hashtags" data-hashtag-id="{{$item->id}}">
                            <td>
                                <a href="{{URL::route('image_list', ['hashtagId' => $item->id])}}">{{$item->title}}</a>
                            </td>
                            <td id="all_count_{{$item->id}}">
                                --
                            </td>
                            <td id="offer_count_{{$item->id}}">
                                --
                            </td>
                            <td id="approved_count_{{$item->id}}">
                                --
                            </td>
                            <td id="live_count_{{$item->id}}">
                                --
                            </td>
                            <td>
                                @if($item->id != $defaultSearchCondition->id)
                                <form action="{{URL::route('remove_search_condition', ['id' => $item->id])}}" method="post">
                                    {{ csrf_field() }}
                                    {{ method_field('delete') }}

                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <button type="submit" class="btn btn-delete delete-hashtag"><i class="fa fa-trash" aria-hidden="true"></i>削除</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <!-- projects -->

                </div>
                <!-- x_content -->
            </div>
            <!-- x_panel -->
        </div>
    </div>
@stop

@section('script')
    <script>
        var searchConditionStatisticApi = '{{URL::route('api_search_condition_statistic', ['searchConditionId' => ''])}}'
    </script>
    <script src="https://cdn.jsdelivr.net/npm/axios@0.12.0/dist/axios.min.js"></script>
    <script src="{{static_file_version('/js/validator/validator.js')}}"></script>
    <script src="{{static_file_version('/js/custom/formValidator.js')}}"></script>
    <script src="https://malsup.github.io/jquery.blockUI.js"></script>
    <script src="{{static_file_version('/js/custom/hashtagListPage.js')}}"></script>
@stop
