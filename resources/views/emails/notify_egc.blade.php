<h3>Please request for these offers:</h3><br>
<h4>Advertiser ID: {{$advertiser->id}}</h4>
<h4>Advertiser name: {{$advertiser->name}}</h4>
<h4>Offer set ID: {{$offerSet->id}}</h4>
<h4>Offer set title: {{$offerSet->title}}</h4>
<h4>Admin offer URL: <a href="{{URL::route('advertiser_offer', ['advId' => $advertiser->id])}}" target="_blank">{{URL::route('advertiser_offer', ['advId' => $advertiser->id])}}</a> </h4><br>
<hr>