var app = new Vue({
    el: '#app',
    data: {
        editingImage: {id: null, url: null},
        currentImage: {id: null, url: null},
        currentKpi: [],
        imageList: [],
        originImage: null,
        fullListImage: [],
        mediaAccount: {id: null, name: null},
        mediaAccountList: mediaAccountList,
        tweet: "",
        firstTime: true,
        isOffer: null,
        offer: [],
        offerId: '',
        statusClass: '',
        statusLabel: '',
        STATUS_APPROVED: 3,
        STATUS_LIVING: 4,
        STATUS_ARCHIVE: 5,
        ownedImage: [],
        showAddPartsForm: false,
        parts: [],
        products: [],
        currentProductUrl: null,
        selectedPartId: null,
        isActive: false,
        showOwnedTab: false,
        productList: null,
        selectedImages: [],
        shouldGenImageProductList: false,
        registeredParts: [],
        unregisterParts: [],
        partId: null,
        isExistProduct: false
    },
    watch: {
        editingImage: function() {
            $('#edit_image_id').val(this.editingImage.id);
            $('#edit_modal_image').attr('src', this.editingImage.url);
            if (!this.firstTime) {
                $('.edit_image_modal').css({'visibility': 'hidden', 'display': 'block'}).modal();
            } else {
                this.firstTime = false;
            }
        },
        currentImage: function() {
            app.getImageKpi();
        },
        offer: function () {
            app.getImageList();
        }
    },
    methods: {
        setEditingImage: function (id, url) {
            var imageObj = {
                id: id,
                url: url
            };
            if (imageObj.id == this.originImage.id) {
                imageObj.id = null;
            }
            this.editingImage = imageObj;
        },
        setCurrentImage: function (id, url) {
            var imageObj = {
                id: id,
                url: url
            };
            this.currentImage = imageObj;
        },
        uploadImage: function() {
            this.mediaAccount = {
                name: $('[name="media_account_id"] option:selected').text(),
                id: $('[name="media_account_id"] option:selected').val()
            };

            if (this.mediaAccountList[this.mediaAccount.id] == 1) {
                ga('send', 'event', 'sync', 'sync_FB', advertiserId);
                //fb
                if (confirm('Facebookと同期しますか？')) {
                    $('#upload_fb_form').submit();
                }
            } else {
                ga('send', 'event', 'sync', 'sync_TW', advertiserId);
                //tw
                $('#upload_tw_modal').modal('show');
            }
        },
        setStatusLabel: function (status) {
            if (status == 0 || status == 1) {
                this.statusClass = 'label-applying';
                this.statusLabel = '申請中'
            } else if (status == 2) {
                this.statusClass = 'label-failed';
                this.statusLabel = '失敗'
            } else if (status == 3) {
                this.statusClass = 'label-approved';
                this.statusLabel = '承認'
            } else if (status == 4) {
                this.statusClass = 'label-synchronis';
                this.statusLabel = '出稿済'
            } else if (status == 5) {
                this.statusClass = 'label-uploaded';
                this.statusLabel = '取り消し'
            }
        },
        isAprroved: function (status) {
            if ([app.STATUS_APPROVED, app.STATUS_LIVING].includes(status)){
                return true;
            }
            return false;
        },
        deleteProductLink: function () {
            var data = {
                url: this.currentProductUrl,
                postId: postId
            }
            axios.post(deleteProductImageUrl, data)
                .then(function (response) {
                    app.getBindProduct();
                    toastr.success("商品ページ紐付を解除しました");
                }).catch(function (error) {
                    toastr.error("商品ページ紐付解除に失敗しました")
            });
        },
        deletePartImage: function () {
            var data = {
                partId: this.selectedPartId,
                postId: postId
            }
            axios.post(deletePartImageUrl, data)
                .then(function (response) {
                    app.getRegisteredPart();
                    toastr.success("UGCセットの登録を解除しました")
                }).catch(function (error) {
                    toastr.error("UGCセットの登録解除に失敗しました")
                });
        },
        numberFormat: function(number, decimalLength) {
            return number_format(number, decimalLength);
        },
        openLinkProduct: function () {
            var self = this;
            if (self.productList == null) {
                axios.get(getAllProductsUrl, {
                    enableBlockUI: false
                }).then(function (response) {
                    self.productList = response.data;
                }).catch(function (reason) {
                    toastr.error("エラーが発生しました")
                });
            }
            this.shouldGenImageProductList = true;
            self.selectedImages = [{post_id: postId, image_url: imageUrl }];

            this.$nextTick(function () {
                $('#image_link_product_modal').modal('show');
            });
        },
        updateProductList: function (response) {
            this.products = response.products;
        },
        getRegisteredPart: function () {
            getRegisteredPartUrl = getRegisteredPartUrl.replace('%s', postId);
            axios.get(getRegisteredPartUrl, {
                enableBlockUI: false
            }).then(function (response) {
                app.registeredParts = response.data.registeredParts;
                app.unregisterParts = response.data.unregisterParts;
            }).catch(function (error) {
                toastr.error("エラーが発生しました")
            });
        },
        getBindProduct: function () {
            axios.get(getVtdrImgDetailUrl + '/' + postId, {
                enableBlockUI: false
            }).then(function (response) {
                app.products = response.data.bin_products;
            }).catch(function (error) {
                toastr.error("エラーが発生しました")
            });
        },
        registerPartImage: function () {
            axios.post(apiRegisterPartUrl, {
                part_id: this.partId,
                post_id: postId
            }).then(function (response) {
                app.getRegisteredPart();
                app.partId = null;
                toastr.success("UGCの追加登録しました");
            }).catch(function (error) {
                toastr.error("UGCの追加登録を失敗しました")
            });
        },
        setPartStatusLabel: function (status) {
            switch (status) {
                case　'1':
                    app.partStatusClass = 'label-publish'
                    return "公開中";
                case '2':
                    return "PV上限のため停止";
                case '3':
                    app.partStatusClass = 'label-unpublished'
                    return "非公開";
                default:
                    return "";
            }
        },
        setPartStatusClass: function (status) {
            switch (status) {
                case '1':
                    return 'label-publish';
                case '3':
                    return 'label-unpublished';
                default:
                    return 'label-uploaded';
            }
        },
        loadVideoJs: function () {
            if (videoUrl) {
                $('.video-modal-tmp').find('source').attr('src', videoUrl);
                videojsCustom.initVideo('.video-modal-tmp', postId);
            }
        }
    },
    mounted () {
        getOfferDetailUrl = getOfferDetailUrl.replace('%s', postId);
        axios.get(getOfferDetailUrl, {
            enableBlockUI: false
        }).then(function (response) {
            if (response.data == 'No offer') {
                app.isOffer = false
                app.isActive = true
                app.loadVideoJs();
            } else {
                app.isOffer = true
                app.offer = response.data
                app.setStatusLabel(response.data.status);
                if (response.data.status != app.STATUS_ARCHIVE) {
                    app.loadVideoJs();
                }
            }
        }).catch(function (error) {
            toastr.error("エラーが発生しました")
        })

        if (siteId) {
            axios.get(getPartImageDetailUrl + '/' + postId, {
                enableBlockUI: false
            }).then(function (response) {
                    app.ownedImage = response.data;
                }).catch(function (error) {
                toastr.error("エラーが発生しました")
            });

            axios.get(getAllProductsUrl, {
                enableBlockUI: false
            }).then(function (response) {
                if (response.data.length > 0) {
                    app.isExistProduct = true;
                }
            }).catch(function (error) {
                toastr.error("エラーが発生しました")
            });

            this.getRegisteredPart();
            this.getBindProduct();
        }
    },
    updated: function () {
        $('#upload_fb_form [name="media_account_id"]').select2({
            templateResult: app.selectFormatState
        });
        this.showOwnedTab = true;
    }
});

app.selectFormatState = function (state) {
    if (!state.id) {
        return state.text;
    }
    if ($(state.element).attr('data-type') == 1) {
        return '<span class="fa fa-facebook-square"></span> ' + state.text;
    } else {
        return '<span class="fa fa-twitter-square"></span> ' + state.text;
    }
};

app.getImageList = function () {
    var app = this;
    axios.get('/advertiser/api/offer/get_edited_img_list/' + app.offer.id, {
        enableBlockUI: false
    })
        .then(function (response) {
            app.imageList = response.data.imageList;
            app.originImage = response.data.originImage;
            app.currentImage = {id: response.data.originImage.id, url: response.data.originImage.image_url};
            app.fullListImage = response.data.imageList;

            app.setEditingImage(app.originImage.id, app.originImage.image_url);
            $('[name="origin_image_id"]').val(app.originImage.id);
        });
};

app.getImageKpi = function () {
    var app = this;
    Utility.blockUI();
    axios.get(getEditedImageKpiApiUrl + '/' + app.currentImage.id)
        .then(function (response) {
            app.currentKpi = response.data;
            Utility.unblockUI();
        });
};

app.clickEditButton = function(id, url) {
    this.setEditingImage(id, url);
};

app.textAreaAdjust = function(o, minH) {
    if (o.scrollHeight <= minH) {
        return;
    }
    o.style.height = "1px";
    o.style.height = (10 + o.scrollHeight)+"px";
};

saveEditedImageCallback = function(data) {
    window.location.reload();
};


app.generateVideoHtlm = function (url) {
    var html = $('.video-modal-tmp').clone();
    html.find('source').attr('src', url);
    html.removeClass('hidden');
    html.attr('id', 'offer_detail_video_preview')
    $('.video-preview-box').append(html);
};

$(document).ready(function(){

    validator.message['empty'] = '入力してください';
    // validate a field on "blur" event, a 'select' on 'change' event & a '.reuired' classed multifield on 'keyup':
    $('form')
        .on('blur', 'input[required], input.optional, select.required, textarea[required]', validator.checkField)
        .on('change', 'select.required', validator.checkField)
        .on('keypress', 'input[required][pattern]', validator.keypress);

    $('[data-href]').click(function(event) {
        event.preventDefault();
        if (confirm($(this).attr('data-confirm'))) {
            window.location.href = $(this).attr('data-href');
        }
    });

    $('#offer_cancel_btn').unbind('click').click(function () {
        var text = "リクエストを取り消すとUGC一覧から表示されなくなります。\n" + "もとに戻すことが出来なくなりますので注意してください。"
        if (confirm(text)) {
            $('#offer_cancel_form').submit();
        }

        return false;
    });
});
