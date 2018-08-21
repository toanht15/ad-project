@extends('layouts.admin')

@section('title') ハッシュタグ一覧 @stop

@section('header')
    <link href="{{static_file_version('/js/datatables/buttons.bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{static_file_version('/js/datatables/responsive.bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
@stop

@section('content')
    <div class="">
        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>ハッシュタグ一覧</h2>
                        <div class="clearfix"></div>
                    </div>
                    @include('templates.alert')
                    <div class="x_content">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <table class="table table-striped responsive-utilities jambo_table bulk_action" id="datatable-buttons">
                                    <thead>
                                    <tr class="headings">
                                        <th class="column-title" style="width:5%;">ID</th>
                                        <th class="column-title text-center">Hashtag</th>
                                        <th class="column-title text-center">Advertiser</th>
                                        <th class="column-title text-center">Last crawled</th>
                                        <th class="column-title text-center">Status</th>
                                        <th class="column-title text-center">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($hashtags as $hashtag)
                                            <tr class="even pointer">
                                                <td class=" text-center">{{$hashtag->id}}</td>
                                                <td class=" text-center">{{$hashtag->hashtag}}</td>
                                                <td class="">
                                                    <select class="form-control">
                                                        @if (isset($advertisers[$hashtag->id]))
                                                            @foreach($advertisers[$hashtag->id] as $advertiser)
                                                                <option>{{$advertiser}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>

                                                </td>
                                                <td class=" text-center">{{$hashtag->last_crawled_at}}</td>
                                                <td class=" text-center"><?php echo \App\Models\Hashtag::setStatusLabel($hashtag->active_flg) ?></td>
                                                <td class=" text-center">
                                                    <button type="button" class="btn btn-info btn-sm hashtag-crawl-btn" id="hashtag_{{$hashtag->id}}"
                                                            data-hashtag-id="{{$hashtag->id}}"
                                                            @if(in_array($hashtag->active_flg, [\App\Models\Hashtag::CRAWLING, \App\Models\Hashtag::WAIT])) disabled @endif>
                                                        クロール
                                                    </button>
                                                    <button type="button" class="btn btn-primary btn-sm inactive-hashtag-btn" id="inactive_hashtag_{{$hashtag->id}}"
                                                            data-hashtag-id="{{$hashtag->id}}">
                                                        Inactive
                                                    </button>
                                                </td>
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

@stop

@section('script')
    <!-- Datatables-->
    <script src="{{static_file_version('/js/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/dataTables.bootstrap.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/dataTables.buttons.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/buttons.bootstrap.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/jszip.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/vfs_fonts.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/buttons.html5.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/buttons.print.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/dataTables.responsive.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/responsive.bootstrap.min.js')}}"></script>

    <script src="{{static_file_version('/js/custom/adminHashtagListPage.js')}}"></script>
    <script>
        var executeCommandUrl = "{{URL::route('execute_hashtag_command')}}";
        var inactiveHashtagUrl = "{{URL::route('inactive_hashtag')}}";
    </script>
@stop