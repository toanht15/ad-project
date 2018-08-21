<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Bootstrap core CSS -->

    <link href="{{url('/')}}/css/bootstrap.min.css" rel="stylesheet">

    <link href="{{url('/')}}/fonts/css/font-awesome.min.css" rel="stylesheet">
    <link href="{{url('/')}}/css/animate.min.css" rel="stylesheet">

    <!-- Custom styling plus plugins -->
    <link href="{{url('/')}}/css/custom.css" rel="stylesheet">

    <script src="{{url('/')}}/js/jquery.min.js"></script>

    @yield('header')

    <!--[if lt IE 9]>
    <script src="{{url('/')}}/assets/js/ie8-responsive-file-warning.js"></script>
    <![endif]-->

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>


<body class="nav-md">

<div class="container body">

    <div class="main_container">

        @yield('content')
    </div>

</div>

<script src="{{url('/')}}/js/bootstrap.min.js"></script>

@yield('script')

{{--<script src="{{url('/')}}/js/custom.js"></script>--}}

<!-- /footer content -->
</body>

</html>
