@extends('layouts.master')

@section('title')
    Instagram連携
@stop

@section('content')
    <div class="page-title">
        <div class="title_left">
            <h3>Instagram連携</h3>
        </div>
    </div>
    <div class="clearfix"></div>

    <div class="row">
        <div class="col-xs-12">
            <div class="x_panel">
                <div class="x_content">
                    @include('templates.alert')


                    @if(isset( $instagramAccount ))
                    <p class="cooperation-img">
                        <img src="{{ $instagramAccount->profile_image }}" class="img-circle center-block"/>
                    </p>
                    <h2 class="text-center"><a href="http://www.instagram.com/{{ $instagramAccount->name }}" target="_blank">User Name: {{ $instagramAccount->name }}</a></h2>
                    <div style="margin: 30px;">
                        <a href="{{ URL::route('remove_instagram', ['d' => $instagramAccount->id]) }}" class="btn btn-delete form-control middle1"
                                ><i class="fa fa-unlock" aria-hidden="true"></i>
                            連携を解除する
                        </a>
                    </div>
                    @else

                        <p class="cooperation-txt">Instagramアカウントを連携していません。</p>

                        <a href="{{ URL::route('connect_instagram') }}" class="btn btn-primary form-control middle1">
                            <i class="icon-instagram"> </i>連携する
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop