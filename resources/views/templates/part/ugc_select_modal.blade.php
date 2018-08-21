<script type="text/x-template" id="images_modal">
    <div class="modal fade" id="ugc_select_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel"></h4>
                </div>
                <div class="modal-body" style=" height: 500px; overflow-y: auto;">
                    <div class="row">
                        <ul class="hash-list">
                            <li><a href="#"
                                   v-on:click="updateSearchCondition(0, $event)"> ALL </a></li>
                            <li v-for="search_condition in search_conditions"><a href="#" :class="{ active: search_condition_id == search_condition.id }" v-on:click="updateSearchCondition(search_condition.id, $event)">@{{
                                    search_condition.title}} </a></li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="form-group sort-area mb20">
                            <div class="sort-list2">
                                絞り込み :
                                <div class="radio-inline" v-for="status_value in status_list"><input
                                            :value="status_value.id"
                                            v-model="status"
                                            name="status"
                                            :id="'status_apply' + status_value.id"
                                            class="status_apply"
                                            type="radio"> <label
                                            :for="'status_apply' + status_value.id">@{{ status_value.name }}</label>
                                </div>

                            </div>
                            <div class="sort-list2">

                                並び替え :
                                <div class="radio-inline" v-for="order_value in sort_orders"><input
                                            :value="order_value.value"
                                            v-model="order"
                                            name="sort"
                                            :id="order_value.value"
                                            class="status_apply"
                                            type="radio"> <label
                                            :for="order_value.value">@{{ order_value.name }}</label></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div role="tabpanel" class="imglist-area">
                            <image-component :image="image" :key="key" v-bind:selected_items="selected_items"
                                             v-for="(image, key) in images" v-on:add="addImage"
                                             v-on:delete="deleteImage"></image-component>
                        </div>
                    </div>
                    <a v-if="next_page" class="jscroll-next btn btn-primary form-control middle1"
                       v-on:click="page += 1"><i
                                class="fa fa-angle-double-down fa-lg" aria-hidden="true"></i>さらに表示</a>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn  btn-danger middle1" v-on:click="registerImage">UGCセットに追加する (@{{
                        selected_items.length }})
                    </button>
                </div>
            </div>
        </div>
    </div>
</script>

<script type="text/x-template" id="image_detail">
    <div class="imglist-panel">
        <div class="js_post_box imglist-panel2">
            <div class="imglist-panel-image">
                <img class="ugc-img check-miss lazy" :src="image.image_url" @error="onImgError">
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
            <p class="hidden ugc-text">@{{ image.text }}</p>
            <div class="col-md-12 no-padding" style="height: 31px">
                <div class="col-md-6" style="padding: 5px 0 0 5px">
                </div>
                <div class="col-md-6 text-right">
                    <div class="custom-checkbox">
                        <input type="checkbox" :id="'checkbox1_' + image.post_id" v-bind:value="image.post_id"
                               v-model="local_selected_items" @change="update($event.target.value)"/>
                        <label :for="'checkbox1_' + image.post_id"></label>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <p class="red hash-like no-padding"><i class="fa fa-heart" aria-hidden="true"></i>@{{ image.like
                    }}
                </p>
            </div>
            <div class="col-md-12 no-padding">
                <div class="col-md-9">
                    <p class="js_imagebox_author overflow_hidden">
                        <a target="_blank" class="author_name" :href="image.author_url">@{{ image.author_name ? image.author_name : image.username }}</a>
                    </p>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</script>