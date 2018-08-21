<search_condition_preview_temp class="hidden">
<div class="x_content">
    <div class="col-xs-12">
        <div class="item">
            <ul class="content-slider content-slider-js">
                <li each="{ post,index in list.data }">
                    <div class="col-xs-12 ugc-img-box">
                        <div class="js_post_box imglist-panel" data-status="{ post.offer_status }" data-pub-date="{ post.pub_date }" data-format="{ post.file_format }">
                            <div class="imglist-panel-image">
                                <img class="ugc-img" onclick="Utility.showUGCBigImageModal($(this).closest('.imglist-panel'));" data-post-url="{ post.post_url }" data-original="{ post.image_url }" data-video-url="{ post.video_url }" src="{ post.image_url }">
                                <i class="fa fa-3x fa-video-camera video-icon" if="{ post.file_format == 2 }" aria-hidden="true"></i>
                            </div>
                            <p class="red hash-like text-right" data-like="{ post.like }"><i class="fa fa-heart" aria-hidden="true"></i>{ post.like }</p>
                            <p class="hidden ugc-text">{ post.text }</p>
                            <p class="kpi-status" data-status-label="{ post.offer_status }" if="{ post.offer_status != null }">
                            </p>
                            <div class="container">
                                <div class="author-info">
                                    <p class="js_imagebox_author">
                                        <a class="author_name" data-full-name="{ post.author_name ? post.author_name : post.username }" target="_blank" href="{ post.author_url }">{ post.author_name.substring(0,9) }</a>
                                    </p>
                                    <div class="clearfix"></div>
                                    <div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
</search_condition_preview_temp>