@extends('layouts.admin')

@section('title') 管理者一覧 @stop

@section('content')
    <div class="">
        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>管理者管理</h2>
                        <div class="nav navbar-right">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#add_admin" >招待する</button>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    @include('templates.alert')
                    <div class="x_content">

                        <div class="row">

                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <table class="table table-striped responsive-utilities jambo_table bulk_action">
                                    <thead>
                                    <tr class="headings">
                                        <th class="column-title" style="width:5%;">ID </th>
                                        <th class="column-title text-center" >名前</th>
                                        <th class="column-title text-center" style="width: 5%">画像 </th>
                                        <th class="column-title text-center">登録日付 </th>
                                        <th class="column-title text-center">最終更新日付 </th>
                                        <th class="column-title text-center">EGC</th>
                                        <th class="column-title text-center">操作</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($adminList as $admin)
                                        <tr class="even pointer">
                                            <td class=" text-center">{{$admin->id}}</td>
                                            <td class=" text-center">{{$admin->user_name}}</td>
                                            <td class=" text-center"><img style="width: 100%;" src="{{$admin->profile_img_url}}" alt="image" /></td>
                                            <td class=" text-center">{{$admin->created_at->format('Y/m/d H:i:s')}}</td>
                                            <td class=" text-center">{{$admin->updated_at->format('Y/m/d H:i:s')}}</td>
                                            <td class=" text-center">
                                                @if($admin->id != \Auth::guard('admin')->user()->id)
                                                    @if(empty($admin->is_egc_staff))
                                                        <button type="button" class="btn btn-success btn-xs change_egc_setting" data-is_egc_staff="0" data-admin_id="{{$admin->id}}" data-url="{{URL::route('change_egc_setting', ['id' => $admin->id])}}" >Mark as EGC</button>
                                                    @else
                                                        <button type="button" class="btn btn-warning btn-xs change_egc_setting" data-is_egc_staff="1" data-admin_id="{{$admin->id}}" data-url="{{URL::route('change_egc_setting', ['id' => $admin->id])}}" >Unmark as EGC</button>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class=" text-center">
                                                @if($admin->id != \Auth::guard('admin')->user()->id)
                                                    <button type="button" class="btn btn-danger btn-xs remove" data-url="{{URL::route('remove_admin', ['id' => $admin->id])}}" >削除</button>
                                                @endif
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

    <div id="add_admin" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">管理者を招待する</h4>
                </div>
                <form method="POST" action="{{URL::route('invite_admin')}}">
                <div class="modal-body">
                    <div style="margin:20px;">
                        {{ csrf_field() }}
                        <div class="col-xs-12" style="margin-bottom: 10px;">
                            <table style="width:100%; margin-bottom:10px;">
                                <tr>
                                    <th style="width: 20%">メルアド</th>
                                    <td style="padding-top:5px;"><input type="text" class="form-control" name="email"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: none">
                    <button type="submit" class="btn btn-primary form-control">
                        招待する
                    </button>
                </div>
                </form>
            </div>

        </div>
    </div>
@stop

@section('script')
    <script>
        $(document).ready(function() {
            $('.remove').click(function() {
                if (confirm('管理者アカウントを削除しますか？')) {
                    window.location.href = $(this).attr('data-url');
                }
            });

            $('.change_egc_setting').click(function() {
                var message = $(this).data('is_egc_staff')
                    ? 'Do you want to unmark this user as EGC staff?'
                    : 'Do you want to mark this user as EGC staff?';
                if(confirm(message)) {
                    axios.post($(this).data('url'), {
                        userId: $(this).data("admin_id")
                    }).then(function (response) {
                        window.location.reload();
                    }).catch(function () {
                        window.location.reload();
                    });
                }
            });
        });

    </script>
@stop