@extends('layouts.master')

@section('title')
    Images
@stop

@section('content')
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3> Image List</h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                            <div class="" role="tabpanel" data-example-id="togglable-tabs">
                                    <p>hashtagが登録されていません。こちらから登録してください。</p>
                                    <p><a href="{{URL::route('hashtag_list')}}" class="btn btn-success form-control">ハッシュタグの登録</a>
                                    </p>
                            </div>
                </div>
             </div>
        </div>
    </div>
@stop