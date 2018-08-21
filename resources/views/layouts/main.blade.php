<!DOCTYPE html>
<html lang="ja">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Letro - @yield('title')</title>

    <!-- Bootstrap core CSS -->
    <link rel="icon" type="image/png" href="{{static_file_version('images/logo/favicon.ico')}}" />
    <link href="{{static_file_version('/css/bootstrap.min.css')}}" rel="stylesheet">

    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

    <link href="{{static_file_version('css/animate.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{static_file_version('/css/non-responsive.css')}}">

    <!-- Custom styling plus plugins -->
    <link href="{{static_file_version('/css/admin.css')}}" rel="stylesheet">
    <link href="{{static_file_version('/css/custom.css')}}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{static_file_version('/css/maps/jquery-jvectormap-2.0.3.css')}}" />
    <link href="{{static_file_version('/css/floatexamples.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{static_file_version('/bower_components/toastr/toastr.css')}}" rel="stylesheet" type="text/css" />

    <script src="{{static_file_version('/js/jquery.min.js')}}"></script>
    <script src="{{static_file_version('/js/nprogress.js')}}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @yield('header')
    @stack('push-header')
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    @if(!env('APP_DEBUG'))
        <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

            ga('create', 'UA-83580133-1', 'auto');
            ga('send', 'pageview');

        </script>

        <script>
            window['_fs_debug'] = false;
            window['_fs_host'] = 'fullstory.com';
            window['_fs_org'] = '9323T';
            window['_fs_namespace'] = 'FS';
            (function(m,n,e,t,l,o,g,y){
                if (e in m) {if(m.console && m.console.log) { m.console.log('FullStory namespace conflict. Please set window["_fs_namespace"].');} return;}
                g=m[e]=function(a,b){g.q?g.q.push([a,b]):g._api(a,b);};g.q=[];
                o=n.createElement(t);o.async=1;o.src='https://'+_fs_host+'/s/fs.js';
                y=n.getElementsByTagName(t)[0];y.parentNode.insertBefore(o,y);
                g.identify=function(i,v){g(l,{uid:i});if(v)g(l,v)};g.setUserVars=function(v){g(l,v)};
                g.identifyAccount=function(i,v){o='account';v=v||{};v.acctId=i;g(o,v)};
                g.clearUserCookie=function(c,d,i){if(!c || document.cookie.match('fs_uid=[`;`]*`[`;`]*`[`;`]*`')){
                    d=n.domain;while(1){n.cookie='fs_uid=;domain='+d+
                    ';path=/;expires='+new Date(0).toUTCString();i=d.indexOf('.');if(i<0)break;d=d.slice(i+1)}}};
            })(window,document,window['_fs_namespace'],'script','user');

            @if(Auth::check())
            FS.identify('{{Auth::user()->id}}', {
                userName: '{{Auth::user()->user_name}}',
                advertiserName: '{{Auth::guard('advertiser')->check() ? Auth::guard('advertiser')->user()->name : 'None'}}'
            });
            @endif
        </script>
        @else
         {{--init ga function when not production env--}}
        <script>
            function ga() {
                return false;
            }
        </script>
    @endif
</head>

@yield('body')

</html>