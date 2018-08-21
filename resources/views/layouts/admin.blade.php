@extends('layouts.main')

@section('body')
    <body class="nav-md">

    <div class="container body">
        <div class="main_container">

            <div class="col-md-3 left_col">
                <div class="left_col scroll-view">

                    <div class="navbar nav_title">
                        <a href="{{url('/')}}" class="site_title"><img src="{{static_file_version('/images/letro_Square_Posi.png')}}"></a>
                    </div>
                    <div class="clearfix"></div>

                    <!-- menu prile quick info -->
                    <div class="profile">
                        <p class="profile-name-area">
                            <img src="{{ Auth::guard('admin')->user()->profile_img_url }}" alt="{{ Auth::guard('admin')->user()->user_name }}" class="profile-initial">
                            <span class="profile-info">
                                <span class="profile-name">{{ Auth::guard('admin')->user()->user_name }}</span><br>
                            </span>
                        </p>
                    </div>
                    <!-- /menu prile quick info -->

                    <br />

                    <!-- sidebar menu -->
                    <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">

                        <div class="menu_section">
                            <ul class="nav side-menu">
                                @if(Auth::guard('admin')->user()->isSuperAdmin())
                                <li><a href="{{URL::route('admin_dashboard')}}"><i class="fa fa-dashboard"></i>  ダッシュボード </a></li>
                                <li><a href="{{URL::route('tenant_list')}}"><i class="fa fa-user-md"></i> テナント</a></li>
                                <li><a href="{{URL::route('admin_list')}}"><i class="fa fa-user"></i> 管理者</a></li>
                                <li><a href="{{URL::route('user_list')}}"><i class="fa fa-users"></i> ユーザー</a></li>
                                <li><a href="{{URL::route('adaccount_list')}}"><i class="fa fa-adn"></i> アドバタイザー</a></li>
                                <li><a href="{{URL::route('admin_conversion_setting')}}"><i class="fa fa-copyright"></i> コンバージョン </a></li>
                                <li><a href="{{URL::route('comment_template_setting')}}"><i class="fa fa-comments"></i> コメントテンプレート </a></li>
                                <li><a href="{{URL::route('extend_function')}}"><i class="fa fa-plus"></i> 拡張機能</a></li>
                                <li><a href="{{URL::route('admin_hashtag_list')}}"><i class="fa fa-hashtag"></i> ハッシュタグ</a></li>
                                @endif
                                <li><a href="{{URL::route('offer_comments')}}"><i class="fa fa-instagram"></i> リクエスト一覧</a></li>
                                <li><a href="{{URL::route('part_requests_list')}}"><i class="fa fa-cogs"></i> Part Request</a></li>
                            </ul>
                        </div>

                    </div>
                    <!-- /sidebar menu -->
                </div>
            </div>

            <!-- top navigation -->
            <div class="top_nav">

                <div class="nav_menu">
                    <nav class="" role="navigation">
                        <div class="nav toggle">
                            <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                        </div>

                        <ul class="nav navbar-nav navbar-right">
                            <li class="">
                                <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <img src="{{ Auth::guard('admin')->user()->profile_img_url }}" alt="{{ Auth::guard('admin')->user()->user_name }}" class="img-circle"> {{ Auth::guard('admin')->user()->user_name }}
                                    <span class=" fa fa-angle-down"></span>
                                </a>
                                <ul class="dropdown-menu dropdown-usermenu pull-right">
                                    <li><a href="{{ URL::route('admin_logout') }}"><i class="fa fa-sign-out pull-right"></i> ログアウト</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </nav>
                </div>

            </div>
            <!-- /top navigation -->


            <!-- page content -->
            <div class="right_col" role="main">
                @yield('content')
                <!-- footer content -->
                <footer>
                    <div class="pull-right">

                    </div>
                    <div class="clearfix"></div>
                </footer>
                <!-- /footer content -->
            </div>
            <!-- /page content -->
        </div>

    </div>

    <div id="custom_notifications" class="custom-notifications dsp_none">
        <ul class="list-unstyled notifications clearfix" data-tabbed_notifications="notif-group">
        </ul>
        <div class="clearfix"></div>
        <div id="notif-group" class="tabbed_notifications"></div>
    </div>

    <script src="{{static_file_version('/js/bootstrap.min.js')}}"></script>
    <script src="{{static_file_version('/js/custom.js')}}"></script>
    <script src="https://malsup.github.io/jquery.blockUI.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios@0.12.0/dist/axios.min.js"></script>
    <script src="{{static_file_version('/bower_components/vue/dist/vue.js')}}"></script>
    @yield('script')

    <!-- /footer content -->
    </body>

@stop
