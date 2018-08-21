@extends('layouts.main')

@section('body')
    <body class="nav-md">

    <div class="container body">

        <div class="main_container">

            <div class="col-md-3 left_col">
                <div class="left_col scroll-view">
                    <div class="navbar nav_title">
                        <a href="{{url('/')}}" class="site_title"><img
                                    src="{{static_file_version('/images/letro_Square_Posi.png')}}"></a>
                    </div>
                    <div class="clearfix"></div>
                    <div class="profile">
                        <p class="profile-name-area">
                            <span class="profile-initial">{{mb_substr(Auth::guard('advertiser')->user()->name, 0, 1)}}</span>
                            <span class="profile-info">
                            <span class="profile-name">{{ str_limit(Auth::guard('advertiser')->user()->name, 8) }}</span><br>
                            <span class="profile-id">{{str_limit(Auth::guard('advertiser')->user()->facebook_ads_id, 17)}}</span>
                        </span>
                        </p>
                    </div>
                    <!-- /profile -->
                    <br/>
                    <!-- sidebar menu -->
                    <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                        <div class="menu_section">
                            <ul class="nav side-menu">
                                <li><a href="{{URL::route('dashboard')}}"><i class="fa fa-dashboard"></i> ダッシュボード </a>
                                </li>
                                <li><a href="{{URL::route('image_list')}}"><i class="fa fa-image"></i> UGC </a></li>
                                @if(can_use_ads())
                                    <li><a href="{{URL::route('slideshows')}}"><img class="left_menu_icon"
                                                                                    src="{{static_file_version('/images/icon_slideshow_btn.png')}}">スライドショー
                                        </a></li>
                                @endif
                                {{--                            <li><a href="{{URL::route('offerset_list')}}"><i class="fa fa-briefcase"></i> オファーセット </a></li>--}}
                                @if(can_use_ugc_set())
                                    <li><a href="{{URL::route('part_list')}}"><img class="left_menu_icon"
                                                                                   src="{{static_file_version('/images/iconUGCSet.png')}}">
                                            UGCセット</a></li>
                                @endif
                                <li><a href="{{URL::route('hashtag_list')}}"><span
                                                class="icon-hash">#</span>ハッシュタグ設定</a></li>

                                {{--todo remove menu item--}}
                                {{--<li><a href="{{URL::route('media_account_list')}}"><i class="fa fa-clone" aria-hidden="true"></i>メディアアカウント </a></li>--}}
                                {{--<li><a href="{{URL::route('instagram_account')}}"><i class="fa fa-instagram" aria-hidden="true"></i>Instagram連携 </a></li>--}}

                                <li><a href="{{URL::route('account_setting')}}"><i class="fa fa-wrench"></i>設定 </a>
                                </li>

                            </ul>
                            <!-- /side-menu -->
                        </div>
                        <!-- /menu_section -->
                    </div>
                    <!-- /sidebar menu -->
                </div>
                <!-- /sscroll-view -->
            </div>

            <!-- top navigation -->
            <div class="top_nav">
                <div class="nav_menu">
                    <nav role="navigation">
                        <ul class="nav navbar-nav navbar-right">
                            <li>
                                <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown"
                                   aria-expanded="false">
                                    <img src="{{ Auth::user()->profile_img_url }}" alt="" class="img-circle">
                                    <span>{{ Auth::user()->name }}</span>
                                    <span class="fa fa-angle-down"></span>
                                </a>

                                <ul class="dropdown-menu ">
                                    @if(\Illuminate\Support\Facades\Session::get('post'))
                                        <li class="vtdr-link-item">
                                            <a href="{{ env('POST_DOMAIN') }}"><i
                                                        class="fa pull-left menu-item-icon"></i> <b>POST</b> <small>powered by Letro</small></a>
                                        </li>
                                    @endif
                                    <li class="vtdr-link-item">
                                        <a href="{{ URL::route('user_logout') }}"><i
                                                    class="fa fa-sign-out pull-left menu-item-icon"></i> ログアウト</a>
                                    </li>

                                </ul>
                            </li>
                        </ul>
                    </nav>
                </div>
                <!-- /nav_menu -->
            </div>
            <!-- /top navigation -->


            <!-- page content -->
            <div class="right_col" role="main">
                @yield('content')
            </div>
            <!-- /page content -->
        </div>

    </div>

    <footer>
        <p>
            <a href="https://www.aainc.co.jp/service/letro/terms.html" target="_blank">利用規約</a>
        </p>
        <p>Copyright (c)2016 Allied Architects, Inc. All rights reserved.</p>
    </footer>

    <script src="{{static_file_version('/js/bootstrap.min.js')}}"></script>
    <script src="{{static_file_version('/bower_components/jscroll/jquery.jscroll.js')}}"></script>
    <script src="{{static_file_version('/js/custom.js')}}"></script>
    <script src="https://malsup.github.io/jquery.blockUI.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios@0.12.0/dist/axios.min.js"></script>
    <script src="{{static_file_version('/bower_components/vue/dist/vue.js')}}"></script>
    <script src="{{static_file_version('/bower_components/toastr/toastr.js')}}"></script>
    @stack('push-script-before-main-script')
    @yield('script')
    @stack('push-script')
    </body>

@stop
