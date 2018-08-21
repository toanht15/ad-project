@extends('layouts.admin')

@section('title') Part Request List @stop

@section('content')
    <div class="">
        <div class="clearfix"></div>
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Part Requests</h2>
                        <div class="clearfix"></div>
                    </div>
                    @if(!empty($errorMessage))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                            {{ $errorMessage }}
                        </div>
                    @endif
                    <div class="x_content">
                        <div class="row">
                            <form>
                                <div class="form-group col-md-3">
                                    <label for="Advertisers">Advertisers</label>
                                    <select class="form-control" id="Advertisers">
                                        <option value="">--- All ---</option>
                                        @foreach($advertisers as $advertiser)
                                            <option value="{{$advertiser['id']}}" @if($advertiser['id'] == $advertiserId) selected @endif>{{$advertiser['id'] .' - '. $advertiser['name']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="siteName">Site name</label>
                                    <input type="text" class="form-control" id="siteName">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="siteDomain">Site domain</label>
                                    <input type="text" class="form-control" id="siteDomain">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="partTitle">Part title</label>
                                    <input type="text" class="form-control" id="partTitle">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="requestURL">Request URL</label>
                                    <input type="text" class="form-control" id="requestURL">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="fromDate">From date</label>
                                    <input type="text" class="form-control" id="fromDate" @if(isset($fromDate)) value="{{$fromDate}}" @endif>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="toDate">To date</label>
                                    <input type="text" class="form-control" id="toDate"  @if(isset($toDate)) value="{{$toDate}}" @endif>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="viewOrder">Sort by</label>
                                    <select class="form-control" id="viewOrder">
                                        <option selected value="DESC">Views: High to Low</option>
                                        <option value="ASC">Views: Low to High</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-1">
                                    <a style="margin-top: 25px" class="form-group btn btn-info btn-block" id="filterButton">Filter</a>
                                </div>

                            </form>
                        </div>

                        <div class="clearfix"></div>

                        <div class="row" style="margin-top: 25px">

                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <table class="table table-striped responsive-utilities jambo_table">
                                    <thead>
                                    <tr class="headings">
                                        <th class="column-title text-left" >Site ID</th>
                                        <th class="column-title text-left" >Site</th>
                                        <th class="column-title text-left" >Part ID</th>
                                        <th class="column-title text-left" >UGCセット</th>
                                        <th class="column-title text-left" >Request URL</th>
                                        <th class="column-title text-right" >Views</th>
                                        <th class="column-title text-right" >Last view at</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($partRequests['data'] as $partRequest)
                                        <tr class="even pointer">
                                            <td class=" text-left">{{$partRequest['site_id']}}</td>
                                            <td class=" text-left"><a href="{{$partRequest['site_base_domain']}}" target="_blank">{{$partRequest['site_name']}}</a></td>
                                            <td class=" text-left">{{$partRequest['part_id']}}</td>
                                            <td class=" text-left">{{$partRequest['part_title']}}</td>
                                            <td class=" text-left" style="width: 40%;word-break: break-all;"><a href="{{$partRequest['request_url']}}" target="_blank">{{$partRequest['request_url']}}</a> </td>
                                            <td class=" text-right">{{$partRequest['views']}}</td>
                                            <td class=" text-right">{{$partRequest['last_viewed_at']}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                <div class="container">
                                    <ul class="pagination justify-content-end" style="float: right">
                                        @if($currentPage <= 1)
                                            <li class="disabled"><a href="JavaScript:Void(0);">Previous</a></li>
                                        @else
                                            <li><a href="JavaScript:Void(0);" id="previous" data-current_page="{{$currentPage}}" data-item_per_page="{{$itemPerPage}}">Previous</a></li>
                                        @endif
                                        @for($i = 1; $i<$partRequests['totalPage']+1; $i++)
                                            <li @if($i == $currentPage) class="active" @endif><a class="pageNumber" data-page_number="{{$i}}" data-item_per_page="{{$itemPerPage}}" href="JavaScript:Void(0);">{{$i}}</a></li>
                                        @endfor

                                        @if($currentPage >= $partRequests['totalPage'])
                                                <li class="disabled"><a href="JavaScript:Void(0);" >Next</a></li>
                                        @else
                                            <li><a href="JavaScript:Void(0);" id="next" data-current_page="{{$currentPage}}" data-item_per_page="{{$itemPerPage}}">Next</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
@stop


@section('script')
    <link href="{{static_file_version('/bower_components/select2/dist/css/select2.min.css')}}" rel="stylesheet">
    <script src="{{static_file_version('/bower_components/select2/dist/js/select2.min.js')}}"></script>
    <script src="{{static_file_version('/bower_components/smalot-bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js')}}"></script>
    <script src="{{static_file_version('/js/custom/adminPartRequestList.js')}}"></script>
@stop