@extends('layouts.admin')

@section('title')
    広告アカウント一覧
@stop

@section('header')
    {{--<link href="{{static_file_version('/js/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css" />--}}
    <link href="{{static_file_version('/js/datatables/buttons.bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{static_file_version('/js/datatables/responsive.bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
@stop

@section('content')
    <div class="">
        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>広告アカウント管理</h2>
                        <div class="nav navbar-right">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#create_advertiser" >アドバタイザー追加</button>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    @include('templates.alert')
                    <div class="x_content">
                        <div class="row text-center">
                            <div class="col-xs-3 input-group">
                                <span class="add-on input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input type="text" style="width: 200px" name="date_range" id="date_range" class="form-control" value="{{(new DateTime($dateStart))->format('Y-m-d') . ' - ' . (new DateTime($dateStop))->format('Y-m-d')}}" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <table class="table table-striped responsive-utilities jambo_table bulk_action" id="datatable-buttons">
                                    <thead>
                                    <tr class="headings">
                                        <th class="column-title" style="width:5%;">ID </th>
                                        <th class="column-title text-center" >広告アカウント名</th>
                                        <th class="column-title text-center" >テナント</th>
                                        <th class="column-title text-center" >メディアアカウント数</th>
                                        <th class="column-title text-center" >登録ハッシュタグ数</th>
                                        <th class="column-title text-center" >リクエスト数</th>
                                        <th class="column-title text-center" >許諾数</th>
                                        <th class="column-title text-center" >許諾レート</th>
                                        <th class="column-title text-center" >アカウント出稿額</th>
                                        <th class="column-title text-center" >UGC出稿額</th>
                                        <th class="column-title text-center" >UGC出稿額比率</th>
                                        <th class="column-title text-center" >昨日の出稿額</th>
                                        <th class="column-title text-center" >Tutorial</th>
                                        <th class="column-title text-center" >メディアアカウントリミット</th>
                                        <th class="column-title text-center" >ハッシュタグリミット</th>
                                        <th class="column-title text-center" >最終ログイン</th>
                                        <th class="column-title text-center" style="width: 10%">操作</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($adAccounts as $adAccount)
                                        <?php
                                        if (isset($adAccount['num_offer_approved']) && isset($adAccount['offer_count'])) {
                                            $approvedRate = $adAccount['num_offer_approved'] / $adAccount['offer_count'] * 100;
                                        } else {
                                            $approvedRate = 0;
                                        }
                                        if (isset($adAccount['ad_account_spend']) &&isset($ugcSpend[$adAccount['id']])){
                                            $spendRate = $ugcSpend[$adAccount['id']] / $adAccount['ad_account_spend'] * 100;
                                            $spendRate = number_format($spendRate, 2, '.', '');
                                        } else {
                                            $spendRate = 0;
                                        }
                                        ?>
                                        <tr class="even pointer">
                                            <td class="text-center">{{$adAccount['id']}}</td>
                                            <td class="text-center">{{$adAccount['name']}}</td>
                                            <td class="text-center">{{$adAccount['tenant_name']}}</td>
                                            <td class="text-center">{{$adAccount['media_account_count']}}</td>
                                            @if(isset($adAccount['condition_count']))
                                                <td class="text-center">{{$adAccount['condition_count'] - 1}}</td>
                                            @else
                                                <td class="text-center">0</td>
                                            @endif
                                            @if(isset($adAccount['offer_count']))
                                                <td class="text-center">{{$adAccount['offer_count']}}</td>
                                            @else
                                                <td class="text-center">0</td>
                                            @endif
                                            @if(isset($adAccount['num_offer_approved']))
                                                <td class="text-center">{{$adAccount['num_offer_approved']}}</td>
                                            @else
                                                <td class="text-center">0</td>
                                            @endif
                                            @if(isset($approvedRate))
                                                <td class="text-center">{{ number_format($approvedRate) }}%</td>
                                            @else
                                                <td class="text-center">0</td>
                                            @endif
                                            @if(isset($adAccount['ad_account_spend']))
                                                <td class="text-center">
                                                    ¥{{number_format($adAccount['ad_account_spend'])}}
                                                </td>
                                            @else
                                                <td class="text-center">0</td>
                                            @endif
                                            @if(isset($ugcSpend[$adAccount['id']]))
                                                <td class="text-center">
                                                    ¥{{number_format($ugcSpend[$adAccount['id']])}}
                                                </td>
                                            @else
                                                <td class="text-center">0</td>
                                            @endif
                                            <td class="text-center">{{$spendRate}}%</td>
                                            @if(isset($yesterdayUgcSpend[$adAccount['id']]))
                                                <td class="text-center">
                                                    ¥{{number_format($yesterdayUgcSpend[$adAccount['id']])}}
                                                </td>
                                            @else
                                                <td class="text-center">0</td>
                                            @endif
                                            <form action="{{URL::route('update_ad_account_info')}}" method="POST" id="update_ad_account_form_{{$adAccount['id']}}">
                                                {{csrf_field()}}
                                                <input type="hidden" name="id" value="{{$adAccount['id']}}">
                                                <td class=" text-center"><input type="checkbox" id="is_complete_tutorial_{{$adAccount['id']}}" name="completed_tutorial_flg" @if ($adAccount['completed_tutorial_flg']) checked @endif value="{{$adAccount['completed_tutorial_flg']}}"></td>
                                                <td class=" text-center"><input type="number" id="max_media_account_{{$adAccount['id']}}" name="max_media_account" min="1" value="{{$adAccount['max_media_account']}}" required style="width: 50%"></td>
                                                <td class=" text-center"><input type="number" id="max_search_condition_{{$adAccount['id']}}" name="max_search_condition" min="1" value="{{$adAccount['max_search_condition']}}" required style="width: 50%"></td>
                                            </form>
                                            <td class="text-center">{{$adAccount['last_login']}}</td>
                                            <td class="text-center">
                                                <button type="submit" form="update_ad_account_form_{{$adAccount['id']}}" data-id="{{$adAccount['id']}}" class="btn btn-success btn-xs update-hashtag-limit-btn">保存</button>
                                                <a type="button" class="btn btn-warning btn-xs" href="{{URL::route('login_as_adaccount', ['adAccountId' => $adAccount['id']])}}" target="_blank">ログイン</a>
                                                <a type="button" class="btn btn-info btn-xs" href="{{URL::route('contract_detail', ['id' => $adAccount['id']])}}">契約管理</a>
                                                <a type="button" class="btn btn-danger btn-xs" href="{{URL::route('part_requests_list', ['advertiser_id' => $adAccount['id']])}}" target="_blank">Part</a>
                                                {{--<button type="button" class="btn btn-danger btn-xs remove-adaccount" data-action="{{URL::route('remove_ad_account', ['id' => $adAccount['id']])}}"  data-toggle="modal" data-target="#remove_adaccount" >削除</button>--}}
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
    <div id="remove_adaccount" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">広告アカウント名確認</h4>
                </div>
                <form method="POST" action="" id="remove-adaccount-form">
                    <div class="modal-body">
                        <div style="margin:20px;">
                            {{ csrf_field() }}
                            <div class="col-xs-12" style="margin-bottom: 10px;">
                                <table style="width:100%; margin-bottom:10px;">
                                    <tr>
                                        <th style="width: 20%">広告アカウント名</th>
                                        <td style="padding-top:5px;">
                                            <input type="text" class="form-control" required="required" name="adaccount_name">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: none">
                        <button type="submit" class="btn btn-danger">
                            削除
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
    <div id="create_advertiser" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">アドバタイザー作成</h4>
                </div>
                <form method="POST" action="{{URL::route('create_advertiser')}}">
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
                                        <th style="width: 20%">アドバタイザー名</th>
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
    <!-- daterangepicker -->
    <script type="text/javascript" src="{{static_file_version('/js/moment/moment.min.js')}}"></script>
    <script type="text/javascript" src="{{static_file_version('/js/datepicker/daterangepicker.js')}}"></script>
    <script>
        var dateStart = '{{(new DateTime($dateStart))->format('m/d/Y')}}',
            dateStop = '{{(new DateTime($dateStop))->format('m/d/Y')}}',
            url = '{{URL::route('adaccount_list')}}';
    </script>
    <script src="{{static_file_version('/js/custom/adminAdAccountListPage.js')}}"></script>
@stop