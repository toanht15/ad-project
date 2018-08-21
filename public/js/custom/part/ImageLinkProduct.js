Vue.component('image_link_product', {
    template: '#image_link_product',
    props: ['image', 'shouldfetchdata', 'productlist'],
    data: function() {
        return {
            productLimit: 10,
            imageDetail: null,
            selectedProducts: []
        }
    },
    
    methods: {
        getImageDetail: function () {
            if (this.imageDetail != null) {
                return;
            }
            var self = this;

            axios.get(apiGetProductUrl + '/' + self.image.post_id, {
                enableBlockUI: false
            })
                .then(function (response) {
                    self.imageDetail = response.data;
                    var selected = [];
                    for (index in response.data.bin_products) {
                        selected.push(response.data.bin_products[index].url);
                    }
                    self.selectedProducts = selected;
                })
                .catch(function (reason) {

                });
        },
        saveProductImage: function () {
            var url = apiPostImageProductUrl.replace('imgId', this.imageDetail.id),
                self = this;

            axios.post(url, {
                product_urls: self.selectedProducts
            }).then(function (response) {
                self.$emit('update', {products: response.data.keepData});
                toastr.success('更新しました');
            }).catch(function (reason) {
                toastr.success('更新に失敗しました');
            });
        },
        initSelectProduct: function() {
            this.$nextTick(function () {
                var self = this;
                $("#select-product-" + self.image.post_id).not('.select2-done').addClass('select2-done').multiselect({
                    buttonWidth: "100%",
                    maxHeight: 300,
                    enableFiltering: true,
                    nonSelectedText: '商品を選択',
                    enableHTML: true,
                    filterPlaceholder: 'テキストもしくはURLの一部からでも検索できます',
                    optionLabel: function(element) {
                        return '<img src="' + $(element).attr('data-img-url') + '" style="max-width: 20px; max-height: 20px">' + $(element).html() + '(' + $(element).val() + ')';
                    },
                    onChange: function (option, checked) {
                        if (self.selectedProducts.length < self.productLimit) {
                            var tmp = $('#select-product-' + self.image.post_id).val();
                            if (tmp == null) {
                                tmp = [];
                            }
                            self.selectedProducts = tmp;
                        }
                    }
                });
            });
        }
    },
    watch: {
        shouldfetchdata: function (newVal, oldVal) {
            this.getImageDetail();
        },
        productlist: function () {
            if (this.productlist != null && this.imageDetail != null) {
                return this.initSelectProduct();
            }
        },
        imageDetail: function () {
            if (this.productlist != null && this.imageDetail != null) {
                return this.initSelectProduct();
            }
        }
    },
    mounted: function () {
        if (this.shouldfetchdata && this.imageDetail == null) {
            this.getImageDetail();
        }
    }
});


