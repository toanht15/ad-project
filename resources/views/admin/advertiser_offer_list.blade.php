@extends('layouts.admin')

@section('title') 管理者一覧 @stop

@section('content')
    <div class="">
        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>リクエスト一覧</h2>
                        <div class="clearfix"></div>
                    </div>
                    @include('templates.alert')
                    <div class="x_content">

                        <div class="row">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group col-md-3 col-sm-3 col-xs-3">
                                    <label>Advertiser Name: {{$advertiserName}}</label>
                                </div>
                                <div class="form-group col-md-3 col-sm-3 col-xs-3">
                                    <label>Instagram Account Name: {{$instagramAccountName}}</label>
                                </div>
                                <div class="col-md-3" style="float: right;">
                                    <a class="btn btn-success btn-lg btn-block btn-huge" href="https://www.instagram.com/accounts/login/" target="_blank" style="float: right;">Log in to Instagram</a>
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12" style="margin-top:50px;">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div >
                                            <label>Filter by offer status: </label>
                                        </div>
                                        <div>
                                            <select id="offer_status_filter_select" class="form-control" style="margin-bottom: 15px">
                                                <option value=""> All status </option>
                                                @foreach(\App\Models\Offer::$offerStatusLabels as $status => $label)
                                                    <option value="{{$label}}" @if($status == \App\Models\Offer::STATUS_OFFERED) selected="selected" @endif>{{$label}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div>
                                            <label>Filter by created day</label>
                                        </div>
                                        <div class="input-group">
                                            <span class="add-on input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" style="width: 200px; height: 100%" name="date_range" id="date_range" class="form-control" value="" />
                                            <input type="hidden" id="created_at_from" value="" />
                                            <input type="hidden" id="created_at_to" value="" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <table class="table table-striped responsive-utilities jambo_table bulk_action" id="advertiser_offer_table">
                                        <thead>
                                        <tr class="headings">
                                            <th class="column-title text-left">Offer ID </th>
                                            <th class="column-title text-left">Offer created at </th>
                                            <th class="column-title text-center">Comment</th>
                                            <th class="column-title text-center">Post type</th>
                                            <th class="column-title text-center">Post URL</th>
                                            <th class="column-title text-center">Image</th>
                                            <th class="column-title text-center">Offer status</th>
                                            <th class="column-title text-center">Action</th>
                                            <th class="column-title text-center">Status</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        {{--*/ $egcHasPermissionStatus = [\App\Models\Offer::STATUS_OFFERED, \App\Models\Offer::STATUS_COMMENTED, \App\Models\Offer::STATUS_COMMENT_FALSE]; /*--}}
                                        {{--*/ $egcNotHasPermissionStatus = [\App\Models\Offer::STATUS_LIVING, \App\Models\Offer::STATUS_ARCHIVE, \App\Models\Offer::STATUS_REGISTERED_PART, \App\Models\Offer::STATUS_APPROVED]; /*--}}
                                        @foreach($offers as $offer)
                                            @if ($offer->offerSet->comment == 'dummy')
                                                @continue
                                            @endif
                                            <tr class="even pointer">
                                                <td class=" text-left">{{$offer->id}}</td>
                                                <td class=" text-left">{{$offer->created_at}}</td>
                                                <td class=" text-center">
                                                    <textarea id="offer_comment_{{$offer->id}}" style="width: 100%" readonly rows="5" class="comment_text">{{$offer->offerSet->comment}}</textarea>
                                                    <!-- Trigger -->
                                                    <button class="btn mg-top-5" data-clipboard-action="copy" data-clipboard-target="#offer_comment_{{$offer->id}}">
                                                        Copy to clipboard
                                                    </button>
                                                </td>

                                                    <td class=" text-center">{{\App\Models\Post::$label[$offer->file_format]}}</td>
                                                    <td class=" text-center"><a href="{{$offer->post_url}}" target="_blank">{{$offer->post_url}}</a></td>
                                                    <td class=" text-center"><img style="width: 100px;" src="{{$offer->image_url}}" alt="image" /></td>

                                                <td class=" text-center">
                                                    <select class="form-control advertiser_offer_status_select" @if(in_array($offer->status, $egcNotHasPermissionStatus)) disabled @endif>
                                                        @foreach(\App\Models\Offer::$offerStatusLabels as $status => $label)
                                                            @if(in_array($status, $egcHasPermissionStatus))
                                                                <option value="{{$status}}" @if($status == $offer->status) selected @endif>{{$label}}</option>
                                                            @endif
                                                            @if(in_array($offer->status, $egcNotHasPermissionStatus))
                                                                <option value="{{$status}}" @if($status == $offer->status) selected disabled @endif>{{$label}}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class=" text-center">
                                                    <button class="btn btn-info btn-sm update_offer_data_button" data-offer_id="{{$offer->id}}" @if(in_array($offer->status, $egcNotHasPermissionStatus)) disabled @endif>Save</button>
                                                </td>
                                                <td class=" text-left">{{\App\Models\Offer::$offerStatusLabels[$offer->status]}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@push("push-header")
<link rel="stylesheet" href="{{static_file_version('/bower_components/smalot-bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')}}">
@endpush

@section('script')
    <script>
        var url = '{{URL::route('change_advertiser_offer_status')}}';

    </script>
    <script src="{{static_file_version('/bower_components/smalot-bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.10/clipboard.min.js"></script>
    <script src="{{static_file_version('/js/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/dataTables.bootstrap.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/dataTables.buttons.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/buttons.bootstrap.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/dataTables.responsive.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/jszip.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/vfs_fonts.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/buttons.html5.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/buttons.print.min.js')}}"></script>
    <script src="{{static_file_version('/js/clipboard/dist/clipboard.min.js')}}"></script>

    <script type="text/javascript" src="{{static_file_version('/js/moment/moment.min.js')}}"></script>
    <script type="text/javascript" src="{{static_file_version('/js/datepicker/daterangepicker.js')}}"></script>

    <script src="{{static_file_version('/js/custom/AdvertiserOfferListPage.js')}}"></script>
@stop

