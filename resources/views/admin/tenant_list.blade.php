@extends('layouts.admin')

@section('title') 管理者一覧 @stop

@section('content')
    <div class="">
        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>テナント管理</h2>
                        <div class="nav navbar-right">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#create_tenant" >新しいテナントを作成する</button>
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
                                        <th class="column-title text-center" >テナント名</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($tenants as $tenant)
                                        <tr class="even pointer">
                                            <td class=" text-center">{{$tenant->id}}</td>
                                            <td class=" text-center">{{$tenant->name}}</td>
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
    <div id="create_tenant" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">テナント作成</h4>
                </div>
                <form method="POST" action="{{URL::route('create_tenant')}}">
                    <div class="modal-body">
                        <div style="margin:20px;">
                            {{ csrf_field() }}
                            <div class="col-xs-12" style="margin-bottom: 10px;">
                                <table style="width:100%; margin-bottom:10px;">
                                    <tr>
                                        <th style="width: 20%">テナント名</th>
                                        <td style="padding-top:5px;">
                                            <input type="text" class="form-control" name="name">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: none">
                        <button type="submit" class="btn btn-primary form-control">
                            作成する
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
@stop

@section('script')

@stop