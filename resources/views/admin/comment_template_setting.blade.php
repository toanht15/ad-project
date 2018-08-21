@extends('layouts.admin')

@section('title') コメントテンプレート管理 @stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>コメントテンプレート管理</h2>
                    <div class="clearfix"></div>
                </div>
                @include('templates.alert')
                <div class="x_content">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <table class="table table-striped responsive-utilities jambo_table bulk_action">
                                <thead>
                                <tr class="headings">
                                    <th class="column-title text-center" >ID </th>
                                    <th class="column-title text-center" >Prefix</th>
                                    <th class="column-title text-center" >Suffix</th>
                                    <th class="column-title text-center" style="width: 10%;">操作</th>
                                </tr>
                                </thead>

                                <tbody>
                                @foreach($commentTemplates as $commentTemplate)
                                    <form action="{{URL::route('update_comment_template')}}" method="POST">
                                        {{csrf_field()}}
                                        <input type="hidden" name="id" value="{{$commentTemplate->id}}">
                                        <tr class="even pointer">
                                            <td class=" text-center">{{$commentTemplate->id}}</td>
                                            <td class=" text-center"><input name="prefix" id="prefix_{{$commentTemplate->id}}" value="{{$commentTemplate->prefix}}" style="width: 100%"></td>
                                            <td class=" text-center"><input name="suffix" id="suffix_{{$commentTemplate->id}}" value="{{$commentTemplate->suffix}}" style="width: 100%"></td>
                                            <td>
                                                <button type="submit" data-id="{{$commentTemplate->id}}" class="btn btn-xs btn-success cmt-template-save-btn">保存</button>
                                                <button type="button" data-url="{{ url('/admin/remove_comment_template/'.$commentTemplate->id) }}" class="btn btn-xs btn-danger cmt-template-del-btn">削除</button>
                                            </td>
                                        </tr>
                                    </form>
                                @endforeach
                                <form action="update_comment_template" method="POST">
                                    {{csrf_field()}}
                                    <tr class="even pointer">
                                        <td class=" text-center"></td>
                                        <td class=" text-center"><input name="prefix" id="prefix_0" value="" style="width: 100%"></td>
                                        <td class=" text-center"><input name="suffix" id="suffix_0" value="" style="width: 100%"></td>
                                        <td ><button class="btn btn-xs btn-success cmt-template-save-btn">保存</button></td>
                                    </tr>
                                </form>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    <script src="{{static_file_version('/js/custom/commentTemplateSettingPage.js')}}"></script>
@stop