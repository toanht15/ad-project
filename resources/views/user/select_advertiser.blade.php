@extends('layouts.main')

@section('header')
    <!-- select2 -->
    <link href="{{static_file_version('/css/select/select2.min.css')}}" rel="stylesheet">
@stop

@section('body')
<body style="background:#F7F7F7;">

<div class="">
    <div id="wrapper" style="margin-top: 0px">
        <div id="login" class="animate form" style="margin-top: 30%">
            <section class="login_content">
                <form class="form-horizontal form-label-left" action="@if(isset($loginPage)){{URL::route('login_ad_account')}}@else{{URL::route('add_account')}} @endif" method="POST">
                    {{ csrf_field() }}
                    <h1><img src="{{static_file_version("/images/Letro_K.png")}}" style="width: 100px"></h1>
                    <!-- /.panel-heading -->
                    <div class="panel-body">
                        <div class="form-group">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                @include('templates.alert')
                                <select name="advertiser_id" class="select2_single form-control" tabindex="-1">
                                    <option></option>
                                    @foreach ( $advertiserList as $advertiser )
                                        @if(isset($advertiser['name']) && trim($advertiser['name']))
                                        <option value="{{ $advertiser['id'] }}">{{ $advertiser['name'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3 mt20">
                                <button type="submit" class="btn btn-success">@if(isset($loginPage))次へ &raquo;@else追加@endif</button>
                            </div>
                        </div>
                    </div>
                    <!-- /.panel-body -->

                    <div class="clearfix"></div>
                    {{--<div class="separator">--}}
                        {{--<p class="change_link">--}}
                            {{--@if(isset($loginPage))--}}
                                {{--<a href="{{URL::route('add_advertiser_page')}}" class="to_register"> 広告アカウントを追加する画面へ </a>--}}
                            {{--@else--}}
                                {{--<a href="{{URL::route('fb_callback')}}" class="to_register"> 前へ </a>--}}
                            {{--@endif--}}
                        {{--</p>--}}
                        {{--<div class="clearfix"></div>--}}
                    {{--</div>--}}
                </form>
                <!-- form -->
            </section>
            <!-- content -->
        </div>
    </div>
</div>
<div id="particle"><canvas class="particles-js-canvas-el"></canvas></div>
<!-- select2 -->
<script src="{{static_file_version('/js/select/select2.full.js')}}"></script>
<script>
    $(document).ready(function() {
        $(".select2_single").select2({
            placeholder: "広告アカウントを選択",
            allowClear: true
        });
    });
</script>
<!-- /select2 -->
<script src="{{static_file_version('/js/particles/particles.js')}}"></script>
<script src="{{static_file_version('/js/particles/setting.js')}}"></script>
</body>

@stop
