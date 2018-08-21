@if(can_use_ads())
    <div class="col-md-12 mb10 mln10">
        <div class="col-md-2">SNS広告プラン</div>
        <div class="col-md-9">
            {{ (new DateTime($adContract->start_date))->format('Y/m/d')}}
            ~ {{ (new DateTime($adContract->end_date))->format('Y/m/d')}}
            <br>
        </div>
    </div>
@endif

@if(can_use_ugc_set())
    <div class="col-md-12 mln10">
        <div class="col-md-2">オウンドメディアプラン</div>
        <div class="col-md-9">
            {{ (new DateTime($ownedContract->start_date))->format('Y/m/d')}}
            ~ {{ (new DateTime($ownedContract->end_date))->format('Y/m/d')}}
            <br>
        </div>
    </div>
@endif