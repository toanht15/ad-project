<!-- modal -->
<div class="modal fade" role="dialog" aria-hidden="true" id="image_link_product_modal">
    <div class="modal-dialog">
        <div class="modal-content p10">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">商品ページの紐付け</h4>
            </div>
            <div class="modal-body">
                <div class="row limit-height-modal">
                    <image_link_product v-on:update="updateProductList" v-for="image in selectedImages" :key="image.post_id" :image="image" :shouldfetchdata="shouldGenImageProductList" :productlist="productList"></image_link_product>
                </div>
            </div>
            <!-- /.modal -->
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->


<script type="text/x-template" id="image_link_product">
    <div class="col-md-12 no-padding">
        <div class="col-md-12 mb15">
            <div class="col-md-3">
                <img class="post-img" :src="image.image_url">
            </div>
            <div class="col-md-9 form-horizontal form-label-left">
                <div class="col-md-12 text-right">
                    <span><b v-text="selectedProducts.length"></b>/10</span>
                </div>
                <div class="col-md-12 form-group">
                    <select class="form-control select-product" multiple="multiple" :id="'select-product-' + image.post_id">
                        <option v-for="product in productlist" :value="product.url" v-text="product.view_title ? product.view_title : product.title" :data-img-url="product.product_image_url ? product.product_image_url : '{{static_file_version('/images/dummy.jpg')}}'" :selected="selectedProducts.includes(product.url)"></option>
                    </select>
                </div>
                <div class="col-md-12 text-center mt20">
                    <button class="btn btn-xs btn-danger" style="width: 50px" @click="saveProductImage">保存</button>
                </div>
            </div>
        </div>
        <hr>
    </div>
</script>

@push("push-header")
    <link rel="stylesheet" href="{{static_file_version('/bower_components/bootstrap-multiselect/dist/css/bootstrap-multiselect.css')}}">
@endpush

@push("push-script-before-main-script")
    <script>
        var apiGetProductUrl = '{{URL::route('api_get_vtdr_img_detail', ['postId' => ''])}}',
            apiPostImageProductUrl = '{{URL::route('api_save_image_product', ['id' => 'imgId'])}}';
    </script>
    <script src="{{static_file_version('/bower_components/bootstrap-multiselect/dist/js/bootstrap-multiselect.js')}}"></script>
    <script src="{{static_file_version('/js/custom/part/ImageLinkProduct.js')}}"></script>
@endpush