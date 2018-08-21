@extends('layouts.admin')

@section('title') 管理者一覧 @stop

@section('content')
    <div class="">
        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>コンバージョン管理</h2>
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
                                        <th class="column-title text-center" >コンバージョンコード</th>
                                        <th class="column-title text-center" style="width: 30%">コンバージョンラベル</th>
                                        <th class="column-title text-center" >操作</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($conversionTypes as $conversionType)
                                        <form action="{{URL::route('update_conversion_label')}}" method="POST">
                                            {{csrf_field()}}
                                        <tr class="even pointer">
                                            <td class=" text-center">{{$conversionType->id}}</td>
                                            <td class=" text-center">{{$conversionType->action_type}}</td>
                                            <td class=" text-center"><input name="{{$conversionType->id}}" value="{{$conversionType->label}}" style="width: 100%"></td>
                                            <td class=" text-center"><button class="btn btn-xs btn-success">保存</button></td>
                                        </tr>
                                        </form>
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

@stop