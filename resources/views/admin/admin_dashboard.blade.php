@extends('layouts.admin')

@section('title') Admin Dashboard @stop

@section('content')
    <div class="x_panel">
        <div class="row">
            <div class="col-md-2">
                    <h3><strong>Admin Dashboard</strong></h3>
            </div>
        </div>

        <div class="row text-center">
            <div class="col-xs-3 input-group">
                <span class="add-on input-group-addon"><i class="fa fa-calendar"></i></span>
                <input type="text" style="width: 200px" name="date_range" id="date_range" class="form-control" value="{{(new DateTime($dateStart))->format('Y-m-d') . ' - ' . (new DateTime($dateStop))->format('Y-m-d')}}" />
            </div>
            <button id="csv_download_btn" class="pull-right btn btn-primary">CSV Download</button>
            <form id="csv_download" method="post" action="{{URL::route('csv_download')}}">
                {{csrf_field()}}
                <input type="hidden" name="time_range" id="csv_time_range" value="">
            </form>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="x_panel stats_panel">
                    <div class="row">
                        <div class="animated flipInY col-md-12 tile_stats_count stats_area">
                            <div class="left"></div>
                            <div class="right">
                                <span class="count_top"><i class="fa fa-money"></i> UGC出稿額（全広告アカウント）</span>
                                <div class="count">¥{{ number_format($totalSpend) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="animated flipInY col-md-12 tile_stats_count stats_area">
                            <div class="left"></div>
                            <div class="right">
                                <span class="count_top"><i class="fa fa-money"></i> 昨日のUGC出稿額（全広告アカウント）</span>
                                <div class="count">¥{{ number_format($yesterdaySpend) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="x_panel stats_panel">
                    <div class="row">
                        <div class="animated flipInY col-md-12 tile_stats_count stats_area">
                            <div class="left"></div>
                            <div class="right">
                                <span class="count_top"><i class="fa fa-money"></i> 出稿額（全広告アカウント）</span>
                                <div class="count">¥{{ number_format($totalAdAccountSpend) }}</div>
                                @php $totalSpendRatio = $totalAdAccountSpend ? ($totalSpend / $totalAdAccountSpend * 100) : 0 @endphp
                                <span class="count_bottom">比率: {{number_format($totalSpendRatio, 2, '.', '')}}%</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="animated flipInY col-md-12 tile_stats_count stats_area">
                            <div class="left"></div>
                            <div class="right">
                                <span class="count_top"><i class="fa fa-money"></i> 昨日の出稿額（全広告アカウント）</span>
                                <div class="count">¥{{ number_format($yesterdayAdAccountSpend) }}</div>
                                @php $yesterdayRatio = $yesterdayAdAccountSpend ? ($yesterdaySpend / $yesterdayAdAccountSpend * 100) : 0 @endphp
                                <span class="count_bottom">比率: {{number_format($yesterdayRatio, 2, '.', '')}}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="x_panel stats_panel">
                    <div class="row">
                        <div class="animated flipInY col-md-12 tile_stats_count stats_area">
                            <div class="left"></div>
                            <div class="right">
                                <span class="count_top"><i class="fa fa-user"></i>昨日のMAX CTR</span>
                                @if ($maxYesterdayCtr)
                                    <div class="count">{{ $maxYesterdayCtr['sum_ctr'] * 100 }}%</div>
                                    <span class="count_bottom">{{$maxYesterdayCtr['name']}}
                                        <i class="green"><a href="{{URL::route('login_as_adaccount', ['adAccountId' => $maxYesterdayCtr['id']])}}" target="_blank">Detail</a></i>
                                    </span>
                                @else
                                    <div class="count">0.00%</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="animated flipInY col-md-12 tile_stats_count stats_area">
                            <div class="left"></div>
                            <div class="right">
                                <span class="count_top"><i class="fa fa-user"></i>昨日のMIN CTR</span>
                                @if ($minYesterdayCtr)
                                    <div class="count">{{ $minYesterdayCtr['sum_ctr'] * 100 }}%</div>
                                    <span class="count_bottom">{{$minYesterdayCtr['name']}}
                                        <i class="green"><a href="{{URL::route('login_as_adaccount', ['adAccountId' => $minYesterdayCtr['id']])}}" target="_blank">Detail</a></i>
                                    </span>
                                @else
                                    <div class="count">0.00%</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="x_panel stats_panel">
                    <div class="row">
                        <div class="animated flipInY col-md-12 tile_stats_count stats_area">
                            <div class="left"></div>
                            <div class="right">
                                <span class="count_top"> オファー数（全広告アカウント）</span>
                                <div class="count">{{ number_format($allOfferCount) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="animated flipInY col-md-12 tile_stats_count stats_area">
                            <div class="left"></div>
                            <div class="right">
                                <span class="count_top"></i> 許諾数（全広告アカウント）</span>
                                <div class="count">{{ number_format($approvalOfferCount) }}</div>
                                @php $approvedRatio = ($allOfferCount && $approvalOfferCount) ? ($approvalOfferCount / $allOfferCount * 100) : 0 @endphp
                                <span class="count_bottom">比率: {{number_format($approvedRatio, 2, '.', '')}}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="admin-daily-chart">
                    <h3>Daily Report</h3>
                    @if (count($totalData) > 0)
                        <div id="reportChart"></div>
                    @else
                        <div class="no-report">
                            <p>出稿中のADがありません。<br>
                                <span>Daily ReportはFacebookにUGCが出稿後表示されます。</span></p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    <!-- daterangepicker -->
    <script type="text/javascript" src="{{static_file_version('/js/moment/moment.min.js')}}"></script>
    <script type="text/javascript" src="{{static_file_version('/js/datepicker/daterangepicker.js')}}"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/funnel.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>

    <script src="https://malsup.github.io/jquery.blockUI.js"></script>

    <script>
        //grapth data
        var ctrData = [],
            spendData = [],
            dateData = [],
            dateStart = '{{(new DateTime($dateStart))->format('m/d/Y')}}',
            dateStop = '{{(new DateTime($dateStop))->format('m/d/Y')}}',
            dashboardURL = '{{URL::route('admin_dashboard')}}';
            offerCount = 0,
            approvedCount = 0,
            liveAdCount = 0;

        //here we generate data for chart
        @foreach($totalData as $data)
            dateData.push('{{(new DateTime($data['date']))->format('m/d')}}');
            ctrData.push({{$data['sum_ctr'] * 100}});
            spendData.push({{$data['sum_spend']}});
        @endforeach
    </script>
    <script src="{{static_file_version('/js/custom/adminDashboardPage.js')}}"></script>
@stop
