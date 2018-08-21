@if (Session::has(\Classes\Constants::INFO_MESSAGE))
    <div class="alert alert-info alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
        {{Session::get(\Classes\Constants::INFO_MESSAGE)}}
    </div>
@elseif (Session::has(\Classes\Constants::ERROR_MESSAGE))
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
        {{Session::get(\Classes\Constants::ERROR_MESSAGE)}}
    </div>
@endif

@if (count($errors) > 0)
    @foreach ($errors->all() as $error)
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            {{ $error }}
        </div>
    @endforeach
@endif
