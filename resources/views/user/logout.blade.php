@extends('layouts.main')

@section('header')
    <link href="{{url('/')}}/css/bootstrap-social.css" rel="stylesheet">
@stop

@section('title') ログアウト @stop

@section('body')
<body style="background:#F7F7F7;">

<div class="">
    <a class="hiddenanchor" id="toregister"></a>
    <a class="hiddenanchor" id="tologin"></a>

    <div id="wrapper">
        <div id="login" class="animate form">
            <section class="login_content">
                <form>
                    <h1>ログアウト</h1>
                    <!-- /.panel-heading -->
                    <div class="panel-body">
                        <p>ログアウトしました</p>
                        <a class="btn btn-default form-control" href="/login">
                            ログイン画面へ
                        </a>
                    </div>
                    <!-- /.panel-body -->

                    <div class="clearfix"></div>
                    <div class="separator">

                        <div class="clearfix"></div>
                        <br />
                        <div>
                            <img src="/images/Letro_K.png" style="width: 100px">
                        </div>
                    </div>
                </form>
                <!-- form -->
            </section>
            <!-- content -->
        </div>
    </div>
</div>

</body>

@stop
