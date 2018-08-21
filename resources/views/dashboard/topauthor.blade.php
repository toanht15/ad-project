@extends('layouts.master')

@section('title')
    トップオーサー
@stop

@section('content')
    <div class="">
        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>トップオーサー</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">

                        <div class="row">

                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="x_panel">
                                    <div class="x_content">

                                        <table class="table table-striped responsive-utilities jambo_table bulk_action">
                                            <thead>
                                            <tr class="headings">
                                                <th class="column-title" style="width:5%;">No </th>
                                                <th class="column-title" style="width: 15%">プロフィール画像 </th>
                                                <th class="column-title text-center" >名前 </th>
                                                <th class="column-title text-center">CTR </th>
                                            </tr>
                                            </thead>

                                            <tbody>
                                            @foreach($authors as $i => $author)
                                            <tr class="even pointer">
                                                <td class=" text-center">{{$i+1}}</td>
                                                <td class=" text-center"><img class="img-circle" style="width: 100%;" src="{{$author['icon_img']}}" alt="image" onerror="$(this).attr({'src':'/images/user.png'})"/></td>
                                                <td class=" text-center">{{$author['name']}}</td>
                                                <td class=" text-center">{{$author['sum_ctr']*100}}%</td>
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
        </div>
    </div>
@stop

@section('script')

@stop