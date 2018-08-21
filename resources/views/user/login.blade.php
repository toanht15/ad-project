@extends('layouts.main')

@section('title') LogIn @stop

@section('header')
    <link href="{{url('/')}}/css/bootstrap-social.css" rel="stylesheet">
@stop

@section('body')
<body style="background:#F7F7F7;">

    <div id="wrapper" style="margin-top: 0px">
        <div id="login" class="animate form" style="margin-top: 30%">
            <section class="login_content">
                <form>
                    <h1><img src="{{static_file_version("/images/Letro_K.png")}}" style="width: 100px"></h1>
                    <!-- /.panel-heading -->
                    <div class="panel-body">
                        @if (Session::has(\Classes\Constants::ERROR_MESSAGE))
                            <div class="alert login-error-message">{!! session(\Classes\Constants::ERROR_MESSAGE) !!}</div>
                        @endif
                        <a class="btn btn-block btn-social btn-facebook" href="{{ $login_link }}">
                            <i class="fa fa-facebook"></i> Sign in with Facebook
                        </a>
                    </div>
                    <!-- /.panel-body -->

                    <div class="clearfix"></div>
                </form>
                <!-- form -->
            </section>
            <!-- content -->
        </div>
    </div>
    <div id="particle"><canvas class="particles-js-canvas-el"></canvas></div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="{{static_file_version('/js/particles/particles.js')}}"></script>
<script src="{{static_file_version('/js/particles/setting.js')}}"></script>
</body>

@stop
