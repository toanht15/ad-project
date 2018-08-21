@if(isset( $instagramAccount ))
    <div class="row">
        <div class="sns-profile-icon col-md-1" >
            <img src="{{ $instagramAccount->profile_image }}" class="img-circle"/>
        </div>
        <div class="col-md-3" >
            <a href="http://www.instagram.com/{{ $instagramAccount->username }}" target="_blank">
                <span>{{ $instagramAccount->name }}</span></a>
        </div>
        <div class="col-md-2" >
            <a href="{{ URL::route('remove_instagram', ['d' => $instagramAccount->id]) }}"
               class="btn btn-delete form-control connect-button">
                <i class="fa fa-unlock" aria-hidden="true"></i>
                連携解除
            </a>
        </div>
    </div>

@else
    <div class="col-md-4" style="width: 100px">
        <a href="{{ URL::route('connect_instagram', ['redirect' => $redirect]) }}" class="btn btn-primary form-control">
            <i class="icon-instagram"> </i>連携
        </a>
    </div>
@endif

