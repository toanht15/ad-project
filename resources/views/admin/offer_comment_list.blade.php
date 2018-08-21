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
                                <table class="table table-striped responsive-utilities jambo_table bulk_action" id="advertiser_comment_table">
                                    <thead>
                                    <tr class="headings">
                                        <th class="column-title text-left" style="width:20%;">Advertiser Name </th>
                                        <th class="column-title text-center" style="width:20%;">リクエスト数</th>
                                        <th class="column-title text-center" style="width:20%;">承認待ち数</th>
                                        <th class="column-title text-center" style="width:20%;">IG Account </th>
                                        <th class="column-title text-center" style="width:20%;">Detail</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($advertisers as $advertiser)
                                        <tr class="even pointer">
                                            <td class=" text-left">{{$advertiser->name}}</td>
                                            <td class=" text-center">{{$advertiser->offered_offer}}</td>
                                            <td class=" text-center">{{$advertiser->commented_offer}}</td>
                                            <td class=" text-center">{{$advertiser->instagram_account_name}}</td>
                                            <td class=" text-center">
                                                <a class="btn btn-info btn-lg" href="{{URL::route('advertiser_offer', ['advId' => $advertiser->id])}}" >Detail</a>
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

    <script src="{{static_file_version('/js/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/dataTables.bootstrap.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/dataTables.buttons.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/buttons.bootstrap.min.js')}}"></script>
    <script src="{{static_file_version('/js/datatables/dataTables.responsive.min.js')}}"></script>
    <script>
        $(document).ready(function () {
            var table = $('#advertiser_comment_table').DataTable({
                responsive: true,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
            });
        });

    </script>

@stop