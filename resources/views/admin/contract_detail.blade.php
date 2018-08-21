@extends('layouts.admin')

@section('title') 契約管理 @stop

@section('header')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker.css" rel="stylesheet" type="text/css" />
    <style>
        input:disabled{
            background-color:#cec7c2;
        }
    </style>
@stop

@section('content')
    <div id="app">
    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>契約管理</h2>
                    <div class="clearfix"></div>
                </div>
                @include('templates.alert')
                <div class="x_content">
                    <div class="row">
                        <div class="col-md-4">
                            <h4>ID: <strong>{{$advertiser->id}} </strong></h4>
                            <h4>広告アカウント名:  <strong>{{$advertiser->adv_name}}</strong></h4>
                            <h4>テナント名:  <strong>{{$advertiser->tenant_name}} </strong></h4>
                        </div>
                        <div class="col-md-8 col-sm-8 col-xs-8">
                            <table class="table table-striped responsive-utilities jambo_table bulk_action" v-cloak>
                                <thead>
                                <tr class="headings">
                                    <th class="column-title text-center">契約 ID </th>
                                    <th class="column-title text-center">契約サービス </th>
                                    <th class="column-title text-center">Owned Site Id</th>
                                    <th class="column-title text-center">Start Date</th>
                                    <th class="column-title text-center">End Date</th>
                                    <th class="column-title text-center">状態</th>
                                </tr>
                                </thead>

                                <tbody>
                                <tr class="even pointer" v-for="contract in contracts" v-cloak>
                                    <td class=" text-center" v-text="contract.contract_service_id"></td>
                                    <td class=" text-center">@{{setServiceTypeLabel(contract.service_type)}}</td>
                                    <td class=" text-center">@{{contract.vtdr_site_id}}</td>
                                    <td class=" text-center">
                                        @{{ changeDateFormat(contract.start_date)}}
                                    </td>
                                    <td class=" text-center">
                                        @{{ changeDateFormat(contract.end_date)}}
                                    </td>
                                    <td class=" text-center"></td>
                                </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#update_contract" >契約更新</button>
                            <button type="button" class="btn btn-success pull-right" data-toggle="modal" data-target="#add_contract" >新規契約</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div id="add_contract" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">新規契約を作成する</h4>
                </div>
                <form method="POST" action="{{URL::route('contract_create')}}">
                    <input type="hidden" value="{{$advertiser->id}}" name="advertiser_id">
                    <div class="modal-body">
                        <div style="margin:20px;">
                            {{ csrf_field() }}
                            <div class="col-xs-12" style="margin-bottom: 10px;">
                                <table style="width:100%; margin-bottom:10px;">
                                    <tr>
                                        <th style="width: 20%">契約サービス</th>
                                        <td>
                                            <select class="form-control" name="service_type" id="service_type" v-model="serviceType">
                                                <option disabled selected value> -- select type of service -- </option>
                                                <option value="1">AD</option>
                                                <option value="2">Owned</option>
                                                <option value="3">Post</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr class="owned-id-select" v-show="serviceType == 2">
                                        <th style="width: 20%">Owned ID</th>
                                        <td style="padding-top:5px;">
                                            <select class="form-control" name="owned_id" id="owned_select" v-model="ownedId" @change="addContractPeriod">
                                                <option disabled selected value> -- select a client -- </option>
                                                <option v-for="site in sites" v-bind:value="site.id"  v-text="site.site_name"></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%">Start date</th>
                                        <td style="padding-top:5px;"><input type="text" class="form-control date-picker" name="contract_start_date" v-model="start_date"></td>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%">End date</th>
                                        <td style="padding-top:5px;"><input type="text" class="form-control date-picker" name="contract_end_date" v-model="end_date"></td>
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

    <div id="update_contract" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">契約更新</h4>
                </div>
                <form method="POST" action="{{URL::route('contract_update')}}" >
                    <input type="hidden" :value="serviceType" name="service_type">
                    <input type="hidden" :value="siteId" name="vtdr_site_id">
                    <div class="modal-body">
                        <div style="margin:20px;">
                            {{ csrf_field() }}
                            <div class="col-xs-12" style="margin-bottom: 10px;">
                                <table style="width:100%; margin-bottom:10px;">
                                    <tr>
                                        <th style="width: 20%">契約 ID</th>
                                        <td>
                                            <select class="form-control" name="contract_service_id" id="contract_service_id" v-model="contractServiceId" @change="updateDatePicker">
                                                <option disabled selected value> -- select a contract id -- </option>
                                                <option v-for="contract in contracts" v-bind:value="contract.contract_service_id">@{{ contract.contract_service_id }} - @{{setServiceTypeLabel(contract.service_type)}}</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%">Start date</th>
                                        <td style="padding-top:5px;"><input type="text" class="form-control schedule-date-picker" name="contract_start_date" v-model="start_date"></td>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%">End date</th>
                                        <td style="padding-top:5px;"><input type="text" class="form-control schedule-date-picker" name="contract_end_date" v-model="end_date"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: none">
                        <button type="submit" class="btn btn-primary form-control">
                            保存する
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
    </div>
@stop

@section('script')
    <script>
            getSiteDataUrl = '{{URL::route('api_get_site')}}'
            getLatestScheduleUrl = '{{URL::route('get_latest_schedule')}}'
            getAllContractUrl = '{{URL::route('api_get_all_contract', ['advId' => $advertiser->id])}}'
            getAllSiteUrl = '{{URL::route('api_get_all_site')}}'
            syncOwnedContractUrl = '{{URL::route('sync_owned_contract', ['advId' => $advertiser->id])}}'
            getContractScheduleUrl = '{{URL::route('api_get_contract_schedule', ['advId' => $advertiser->id])}}'
            updateContractUrl = '{{URL::route('contract_update')}}'
    </script>
    <script src="../../js/moment/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/locales/bootstrap-datepicker.ja.min.js"></script>
    <script src="{{static_file_version('/js/custom/adminContractDetailPage.js')}}"></script>
    <script>
        $('.date-picker').datepicker({
            format: 'yyyy-mm-dd',
            language: 'ja'
        });
    </script>
@stop