<script type="text/x-template" id="add_product_page">
    <div class="modal fade add-product-page-modal" id="add_product_page_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content p10">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title"><h3>商品ページ追加</h3></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <h4>URLのリストから追加</h4>
                    <div class="text">
                        商品ページのURLを改行区切りで登録出来ます。
                    </div>
                    <ul class="errors">
                        <li v-for="error in urlListErrors" v-text="error"></li>
                    </ul>
                    <textarea name="" rows="5" cols="50" class="form-control" v-model="urlList"></textarea>
                    <div class="text-center mt20">
                        <button class="btn btn-danger save-btn" @click="addByUrlList">保存</button>
                    </div>
                </div>
                <div class="row mt40">
                    <h4>サイトマップから追加</h4>
                    <div class="text">
                        sitemap.xmlのURLを入力することで、自動的に商品ページURL、商品タイトル、og:Imageから画像を登録出来ます
                    </div>
                    <ul class="errors">
                        <li v-for="error in siteMapErrors" v-text="error"></li>
                    </ul>
                    <input type="text" class="form-control mt10" v-model="sitemap">
                    <div class="mt10">
                        <label><input type="checkbox" v-model="goalSetting"> [目標設定」で「商品詳細ページ」タイプに登録されているページで絞り込む</label> <br>
                        <div v-show="goalSetting">
                            目標名
                            <select name="cv_page" v-model="cvPageId" class="form-control mt10 cv-page-select">
                                <option v-for="page in cvPages" :value="page.id" v-text="page.label"></option>
                            </select>
                        </div>

                    </div>
                    <div class="text-center mt20">
                        <button class="btn btn-danger save-btn" @click="addBySitemap">保存</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</script>

@push("push-script-before-main-script")
    <script>
        var apiGetCvPageUrl = '{{URL::route('api_get_cv_page')}}',
            apiAddProductBySitemap = '{{URL::route('api_add_product_by_sitemap')}}',
            apiAddProductByUrlList = '{{URL::route('api_add_product_by_url_list')}}'
    </script>
    <script src="{{static_file_version('/js/custom/page/addProductPage.js')}}"></script>
@endpush