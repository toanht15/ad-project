<div class="imglist-panel" v-for="(image, key) in imageList">
    <div class="js_post_box imglist-panel2">
        <div class="imglist-panel-image" v-on:click="openBigModalWithImage(genViewId(image.post_id))">
            <img class="ugc-img lazy" :data-src="image.image_url" :onCustomError="'imageListApp.onImgError(\''+genViewId(image.post_id)+'\')'">
            <div class="row" style="height: 80px;">
                <div  style="height: 35px" v-if="image.video_url">
                    <i class="fa fa-2x video-icon fa-video-camera" aria-hidden="true"></i>
                </div>
                <div style="height: 40px;">
                    <img class="carousel-icon" src="{{static_file_version('/images/icon_carousel.png')}}"
                         v-if="image.file_format == {{\App\Models\Post::CAROUSEL_IMAGE}} || image.file_format == {{\App\Models\Post::CAROUSEL_VIDEO}}">
                </div>
            </div>
        </div>
        <div class="col-md-12 no-padding h31">
            <div class="col-md-9 pl6 pt6">
                <span class="label label-registed-part p5 status" v-if="image.vtdr_part_id != null">UGCセット</span>
                <span v-html="Utility.getStatusLabel(image.offer_status)"></span>
            </div>
            <div class="col-md-3 text-right">
                <div class="custom-checkbox">
                    <input type="checkbox" :id="'checkbox1_' + image.post_id" :checked="isSelected(genViewId(image.post_id))"/>
                    <label :for="'checkbox1_' + image.post_id" v-on:click="selectImage(genViewId(image.post_id))"></label>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <p class="red hash-like no-padding"><i class="fa fa-heart" aria-hidden="true"></i>@{{ image.like }}</p>
        </div>
        <div class="col-md-12 no-padding">
            <div class="col-md-9">
                <p class="js_imagebox_author overflow_hidden">
                    <a target="_blank" class="author_name" :href="image.author_url">@{{ image.author_name ? image.author_name : image.username }}</a>
                </p>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="col-md-12 no-padding">
            <a :href="'{{URL::route('post_detail', ['id' => ''])}}/'+image.post_id"
               v-if="image.vtdr_part_id != null || (image.offer_status != null && status != {{\App\Models\Offer::STATUS_ARCHIVE}})"
               class="btn btn-detail btn-block col-xs-12 imglist-detail-btn" role="button">詳細を見る
                <i class="fa fa-angle-double-right" aria-hidden="true"></i></a>
        </div>
        <div class="clearfix"></div>
    </div>
    <!-- imglist-panel -->
</div>
<div class="col-md-12 text-center" v-if="isLoadingImage">
    <img src="/images/loading.gif" alt="Loading" style="width: 60px">
</div>
<a v-if="showNextBtn && !isLoadingImage" class="jscroll-next btn btn-primary form-control middle1" v-on:click="page += 1"><i class="fa fa-angle-double-down fa-lg" aria-hidden="true"></i>さらに表示</a>