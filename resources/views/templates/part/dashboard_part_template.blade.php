<script type="text/x-template" id="part_template">
    <div v-if="part.status == 1" class="jumbotron" style="height: 890px; padding-left: 40px; padding-right: 40px">
        <p class="text-center ugcSetTitle">@{{ part.title }}（@{{part.template_str}}表示）　</p>
        <div class="row">
            <div class="row">
                <div class="col-sm-2">impression</div>
                <div class="col-sm-2">Inview</div>
                <div class="col-sm-2">Click</div>
                <div class="col-sm-2">CTR</div>
                <div class="col-sm-2">CV</div>
                <div class="col-sm-2">CVR</div>
            </div>
            <div class="row mb20">
                <div class="col-sm-2"><h4>@{{ part.number_format(part.impression) }}</h4></div>
                <div class="col-sm-2"><h4>@{{ part.number_format(part.inview) }}</h4></div>
                <div class="col-sm-2"><h4>@{{ part.number_format(part.click) }}</h4></div>
                <div class="col-sm-2"><h4>@{{ part.ctr }} %</h4></div>
                <div class="col-sm-2"><h4>@{{ part.number_format(part.cv) }}</h4></div>
                <div class="col-sm-2"><h4>@{{ part.cvr }} %</h4></div>
            </div>

        </div>
        <div class="row" style="height: 350px">
            <template v-if="part.loading_kpi">
                <i class="fa fa-spinner fa-spin" style="font-size:40px; margin:0px ; padding: 0px"></i>
            </template>
            <template v-else>
                <template v-if="part.hasData">
                    <line-chart v-bind:chartData="chart_data"
                                :width="300" :height="300"
                                :options="chart_options">
                    </line-chart>
                </template>
                <template v-else>
                    <div class="no-report text-center" 　style="height: 300px">
                        <p>公開中のUGCセットはありません</p>
                    </div>
                </template>
            </template>
        </div>
        <div class="row testimonial-group mb15">

            <template v-if="part.loading_status">
                <div class="row" style="text-align: center;    height: 380px;">
                    <i class="fa fa-spinner fa-spin" style="font-size:40px; margin:0px ; padding: 0px"></i>
                </div>

            </template>

            <template v-else>
                <div class="row">
                    <part-image-component :ugc="ugc" :part="part" v-bind:key="key"
                                          v-for="(ugc, key) in topUgcs"></part-image-component>
                </div>
            </template>

        </div>
        <div class="row" v-if="!part.loading_status">
            <div class="col-lg-12 " style="text-align:center">
                <a :href="part.web_part_detail" style="margin: auto" type="button" class="btn btn-detail middle1">UGCセット詳細</a>
            </div>
        </div>
    </div>

    <div v-else class="jumbotron" style="height: 890px; padding-left: 40px; padding-right: 40px">
        <p class="text-center ugcSetTitle">@{{ part.title }}（@{{part.template_str}}表示）　</p>
        <div class="row">
            <div class="row">
                <div class="col-sm-2">impression</div>
                <div class="col-sm-2">Inview</div>
                <div class="col-sm-2">Click</div>
                <div class="col-sm-2">CTR</div>
                <div class="col-sm-2">CV</div>
                <div class="col-sm-2">CVR</div>
            </div>
            <div class="row">
                <div class="col-sm-2"><h4>-</h4></div>
                <div class="col-sm-2"><h4>-</h4></div>
                <div class="col-sm-2"><h4>-</h4></div>
                <div class="col-sm-2"><h4>-</h4></div>
                <div class="col-sm-2"><h4>-</h4></div>
                <div class="col-sm-2"><h4>-</h4></div>
            </div>
        </div>
        <div class="row" style="height: 300px">
        </div>
        <div class="row" style="height: 50px">
            <div class="middle1">
                <h6>UGCセットが公開されていません</h6>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 " style="text-align:center">
                <a :href="part.web_part_detail" style="margin: auto" type="button" class="btn btn-detail middle1">UGCセット詳細</a>
            </div>
        </div>
    </div>


</script>

<script type="text/x-template" id="image_template">
    <div v-if="display" class="col-lg-3 show-image">
        <div class="row image-panel">
            <img class="ugc-image" v-bind:src="ugc.img_url">
            <div class="row first-row" style="">
                <div class="col-lg-8">
                    <p class="ugc_text">クリック数</p>
                </div>
                <div class="col-lg-4">
                    <p class="ugc_text">@{{ ugc.click }}</p>
                </div>
            </div>

            <div v-if="part.template == 3" class="row">
                <div class="col-lg-8">
                    <p class="ugc_text">遷移数</p>
                </div>
                <div class="col-lg-4">
                    <p class="ugc_text">@{{ ugc.outer_contents_product_link_count }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <p class="ugc_text">
                        CV</p>
                </div>
                <div class="col-lg-4">
                    <p class="ugc_text">@{{ ugc.cv }}</p>
                </div>
            </div>

            <div v-if="part.sort == 3" class="row image_sort_value">
                <div class="col-lg-8">
                    <p class="ugc_text">
                        表示順</p>
                </div>
                <div class="col-lg-4">
                    <input type="number" style="margin-top: 10px;width: 50px" v-model="sort_value" min="0">
                </div>
            </div>


            <div v-if="!ugc.hidden" type="button" v-on:click="deleteImage()"
                 class="btn "><span
                        class="glyphicon glyphicon-remove-circle blue delete-button"
                        aria-hidden="true"></span>
            </div>
        </div>
        <div class="row last-row">
            <a href="#"
               class="text-right part-link-panel"
               role="button">
                <i class="fa fa-angle-double-right" aria-hidden="true"></i></a>
        </div>
    </div>
</script>

