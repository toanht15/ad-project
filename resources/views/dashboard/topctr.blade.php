@extends('layouts.master')

@section('title')
    トップCTR
@stop

@section('content')
    <div class="">
        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>{{$title}}</h2>
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
                                                <th class="column-title" style="width: 15%">画像 </th>
                                                <th class="column-title text-center" >ユーザー</th>
                                                <th class="column-title text-center">CTR </th>
                                                <th class="column-title text-center">CPC </th>
                                                <th class="column-title text-center">Spend </th>
                                                <th class="column-title text-center">Click </th>
                                                <th class="column-title text-center">Impression </th>
                                            </tr>
                                            </thead>

                                            <tbody>
                                            @foreach($images as $i => $image)
                                            <tr class="even pointer">
                                                <td class=" text-center">{{$i+1}}</td>
                                                <td class=" text-center"><img style="width: 100%;" src="{{$image['image_url']}}" alt="image" /></td>
                                                <td class=" text-center">{{$image['name']}}</td>
                                                <td class=" text-center">{{$image['sum_ctr']*100}}%</td>
                                                <td class=" text-center">¥{{number_format($image['sum_cpc'])}}</td>
                                                <td class=" text-center">¥{{number_format($image['sum_spend'])}}</td>
                                                <td class=" text-center">{{number_format($image['sum_click'])}}</td>
                                                <td class=" text-center">{{number_format($image['sum_imp'])}}</td>
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