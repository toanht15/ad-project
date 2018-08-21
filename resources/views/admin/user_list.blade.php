@extends('layouts.admin')

@section('title') ユーザー一覧 @stop

@section('content')
    <div class="">
        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>ユーザー管理</h2>
                        <div class="nav navbar-right">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_user" >招待する</button>
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
                                        <th class="column-title" style="width:5%;">No </th>
                                        <th class="column-title text-center" >名前</th>
                                        <th class="column-title text-center" >テナント </th>
                                        <th class="column-title text-center">登録日付 </th>
                                        <th class="column-title text-center">操作</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($userList as $user)
                                        <tr class="even pointer">
                                            <td class=" text-center">{{$user->id}}</td>
                                            <td class=" text-center">{{$user->user_name}}</td>
                                            <td class=" text-center">{{$user->tenant->name}}</td>
                                            <td class=" text-center">{{$user->created_at->format('Y/m/d H:i:s')}}</td>
                                            <td class=" text-center">
                                                <button type="button" class="btn btn-danger btn-xs remove" data-url="{{URL::route('remove_user', ['id' => $user->id])}}" >削除</button>
                                                <a type="button" class="btn btn-warning btn-xs" target="_blank" href="{{URL::route('login_as_user', ['userId' => $user->id])}}">ログイン</a>
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

    <div id="add_user" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">広告主を招待する</h4>
                </div>
                <form method="POST" action="{{URL::route('invite_advertiser')}}">
                    <div class="modal-body">
                        <div style="margin:20px;">
                            {{ csrf_field() }}
                            <div class="col-xs-12" style="margin-bottom: 10px;">
                                <table style="width:100%; margin-bottom:10px;">
                                    <tr>
                                        <th style="width: 20%">テナント</th>
                                        <td style="padding-top:5px;">
                                            <select class="form-control" name="tenant_id">
                                                @foreach($tenantList as $tenant)
                                                    <option value="{{$tenant->id}}">{{$tenant->name}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%">アドバタイザー</th>
                                        <td style="padding-top:5px;">
                                            <select class="form-control" name="advertiser_id">
                                                @foreach($advertiserList as $advertiser)
                                                    <option value="{{$advertiser->id}}">{{$advertiser->name}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%">ロール</th>
                                        <td style="padding-top:5px;">
                                            <select class="form-control" name="role">
                                                @foreach($roles as $role)
                                                    <option value="{{$role}}">{{$role}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%">ユーザー</th>
                                        <td style="padding-top:5px;">
                                            <select class="form-control" name="user_id">
                                                <option value="">New</option>
                                                @foreach($userList as $user)
                                                    <option value="{{$user->id}}">{{$user->user_name}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%">名前</th>
                                        <td style="padding-top:5px;"><input type="text" class="form-control" name="name"></td>
                                    </tr>
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
                if (confirm('ユーザーを削除しますか？')) {
                    window.location.href = $(this).attr('data-url');
                }
            });
        });

    </script>
@stop