var oldFunctionObject = {
    initJqueryEvent: function () {
        $('[data-toggle="tooltip"]').tooltip();
        $(".allHash").click(function(){
            $(this).prev().slideToggle();
            $(this).toggleClass("open");
            if($(this).hasClass('open')){
                $(this).html('<i class="fa fa-minus-circle" aria-hidden="true"></i>表示するタグを減らす')
            }else{
                $(this).html('<i class="fa fa-plus-circle" aria-hidden="true"></i>全てのハッシュタグを表示する')
            }
            return false;
        });

        OfferOperator.validateConfirmModal();
        $('#create_hashtag_btn').click(function() {
            if (!validator.checkAll($(this).closest('form'))) {
                return false;
            }

            var hasInvalidInput = false;

            $('.hashtag-input').each(function (i) {
                if (FormValidator.hasSpecialCharacter($(this))) {
                    hasInvalidInput = true;
                    return false;
                }
            });

            if (hasInvalidInput) {
                return;
            }

            var hashtag = $('[name="newHashtag"]').val();

            if (hashtag == "") {
                alert('ハッシュタグを入力してください');
                return false;
            }

            $('#create_hashtag_modal').modal('hide');

            Utility.blockUI();

            $('#add_new_hashtag').submit();
        });

        $('.hashtag-input').keypress(function (event) {
            if (event.keyCode == 10 || event.keyCode == 13) {
                event.preventDefault();
            }
        });

        FormValidator.preventSpecialCharacter($('.hashtag-input'));

        $('#add_new_hashtag_input').click(function() {
            var newdiv = document.createElement('div');
            newdiv.className = "hashtag-input-remove";
            newdiv.innerHTML = '<br><div class="form-group item mt20 hashtag_input">'
                + '<label class="auto-modal-hash auto-modal-hash-label">#</label>   '
                + '<input type="text" name="hashtags[]" class="form-control auto-modal-hash-input hashtag-input" required="required">'
                + '<i class="fa fa-minus-circle fa-lg input-remove auto-modal-hash-icon fontawesome-remove-icon" onclick="oldFunctionObject.removeHashtagInput(this)"></i></div>';

            document.getElementById('newHashtagForm').appendChild(newdiv);

            var number_hashtag_input = $('#newHashtagForm .hashtag-input-remove').length;
            if (number_hashtag_input >= 3) {
                $('#add_new_hashtag_input').hide();
            }

            FormValidator.preventSpecialCharacter($('.hashtag-input'));
        });
    },
    removeHashtagInput: function ($this) {
        $this.closest(".hashtag-input-remove").remove();

        var limit_hashtag_input = 2;
        var number_hashtag_input = $('#newHashtagForm .hashtag-input-remove').length;

        if (number_hashtag_input < limit_hashtag_input) {
            $('#add_new_hashtag_input').show();
        }

        return false;
    }
};

var imageListApp = new Vue({
    el: '#imageListApp',
    data: {
        firstTime: true,
        page: 1,
        sort: 'new',
        status: currentStatus,
        actionMode: 1, // 1: オファー、2: UGCセット
        searchConditionId: currentSearchConditionId,
        shouldAppendImage: false,
        showNextBtn: false,
        shouldGenImageProductList: false,
        imageList: {},
        selectedImageIds: [],
        selectedImages: [],
        errorImageIds: [],
        productList: null,
        partList: null,
        selectedOfferedImageCount: 0,
        selectedApprovedImageCount: 0,
        selectedPartImageCount: 0,
        bigModalImage: null,
        loadImgLoop: null,
        filterPartId: 0,
        slideshow: {
            url: null,
            video_type: 0,
            effect_type: 0,
            time_per_img: 2,
            size: 0,
            id: 0
        },
        selectedPartIds: [],
        selectedParts: [],
        isLoadingImage: true,
        isSubmittedImagePart: false,
        loadAsync: loadAsync,
        Utility: Utility
    },
    computed: {
        enableArchiveBtnFlg: function () {
            return ["-1", "6"].includes(this.status) && this.selectedImageIds.length > 0 && this.selectedOfferedImageCount == 0 && this.selectedPartImageCount == 0;
        },
        enableUnArchiveBtnFlg: function () {
            return ["5"].includes(this.status) && this.selectedImageIds.length > 0 && this.selectedOfferedImageCount == 0 && this.selectedPartImageCount == 0;
        },
        enableOfferBtnFlg: function () {
            return ["-1", "5", "6"].includes(this.status) && this.selectedImageIds.length > 0 && this.selectedOfferedImageCount == 0;
        },
        enableSlideshowBtnFlg: function () {
            return this.selectedApprovedImageCount > 1 && this.selectedImageIds.length == this.selectedApprovedImageCount;
        },
        enableSelectPartBtnFlg: function () {
            // return this.actionMode == 2 && this.selectedImageIds.length > 0;
            return true;
        },
        selectedPostIdsStr: function () {
            var ids = [];
            for (var index in this.selectedImages) {
                ids.push(this.selectedImages[index].post_id);
            }

            return ids.join(',');
        }
    },
    watch: {
        imageList: function() {
            this.$nextTick(function () {
                if (this.page == 1) {
                    $("img.lazy").unbind('lazyload').lazyload({
                        effect : "fadeIn"
                    }).addClass('lazy-done');
                } else {
                    $("img.lazy").not('.lazy-done').unbind('lazyload').lazyload({
                        effect : "fadeIn"
                    }).addClass('lazy-done');
                }

                $('img').on('dragstart', function(event) { event.preventDefault(); });
            });
        },
        selectedImageIds: function () {
            var result = [];
            for (var index in this.selectedImageIds) {
                var postIdStr = this.selectedImageIds[index];
                if (postIdStr in this.imageList) {
                    result.push(this.imageList[postIdStr]);
                } else {
                    for (var objIndex in this.selectedImages) {
                        var image = this.selectedImages[objIndex];
                        if (this.genViewId(image.post_id) === postIdStr) {
                            result.push(image);
                            break;
                        }
                    }
                }
            }

            this.selectedImages = result;
            this.isSubmittedImagePart = false;
        },
        bigModalImage: function () {
            if (!this.bigModalImage.video_url) {
                $('#video_modal').remove();
                $('#image_modal').removeClass('hidden');
                return;
            }
            this.$nextTick(function () {
                videojsCustom.initVideo('.video-modal-tmp', this.bigModalImage.post_id);
            });
        },
        page: function(newVal, oldVal) {
            if (newVal != 1) {
                this.shouldAppendImage = true;
            }
            this.getImages();
        },
        sort: function() {
            this.resetSearch();
        },
        status: function (newVal, oldVal) {
            if (newVal == 6 && this.filterPartId == 0) {
                return;
            }
            this.resetSearch();
        },
        filterPartId: function () {
            this.resetSearch();
        },
        searchConditionId: function () {
            this.resetSearch();
        },
        enableSelectPartBtnFlg: function () {
            this.initSelectPart();
        },
        actionMode: function () {
            this.initSelectPart();
        }
    },
    methods: {
        getImages: function() {
            var url = getImageAPIBaseUrl + '?search_condition_id=' + this.searchConditionId + '&status=' + this.status + '&page=' + this.page + '&order=' + this.sort;
            if (this.status == 6 && this.filterPartId != 0) {
                url += '&part_id=' + this.filterPartId;
            }
            if (this.loadAsync) {
                url += '&get_crawling_flg=1';
            }
            this.isLoadingImage = true;
            var self = this;
            axios.get(url, {
                enableBlockUI: false
            })
                .then(function (response) {
                    var temp = {};
                    if (self.shouldAppendImage) {
                        temp = self.imageList;
                        self.shouldAppendImage = false;
                    }
                    for (key in response.data.data) {
                        var id = self.genViewId(response.data.data[key].post_id);
                        temp[id] = response.data.data[key];
                    }
                    self.imageList = {};
                    self.imageList = temp;
                    if(response.data.current_page < response.data.last_page) {
                        self.showNextBtn = true;
                    } else {
                        self.showNextBtn = false;
                    }
                    if(self.loadAsync && (!response.data.crawlingFlg || Object.keys(self.imageList).length >= 24)) {
                        self.loadAsync = false;
                        clearInterval(self.loadImgLoop);
                        self.isLoadingImage = false;
                    }
                    if(!self.loadAsync || Object.keys(self.imageList).length >= 24) {
                        self.isLoadingImage = false;
                    }
                })
                .catch(function (reason) {
                    self.isLoadingImage = false;
                });
        },
        getPartList: function () {
            if (typeof getPartListApiUrl === 'undefined') {
                return;
            }
            var self = this;
            axios.get(getPartListApiUrl, {
                enableBlockUI: false
            })
                .then(function (response) {
                    self.partList = response.data;
                    self.initSelectPart();
                    if (typeof hasAdsContract == 'undefined') {
                        self.actionMode = 2;
                    }
                    if (response.data.length === 0 && typeof introJS === 'undefined') {
                        $('#create_part_modal').modal('show');
                    }
                })
                .catch(function (reason) {});
        },
        registerImage: function (isPublish) {
            if (isPublish && this.isSubmittedImagePart) {
                return;
            }
            var partIds = [];
            for (var index in this.selectedParts) {
                var part = this.selectedParts[index];
                if (isPublish) {
                    if (part.status == 1) {
                        partIds.push(part.id);
                    }
                } else {
                    if (part.status != 1) {
                        partIds.push(part.id);
                    }
                }
            }
            var self = this;
            if(!isPublish && self.selectedParts.length != partIds.length && !self.isSubmittedImagePart) {
                toastr.warning('反映されていないUGCセットがあります');
                return;
            }
            if (!confirm('画像をUGCセットに登録しますか？')) {
                return;
            }
            axios.post(apiRegisterImage, {
                post_ids : imageListApp.getSelectedPostIds(),
                part_ids : partIds
            }).then(function (response) {
                if (!isPublish && self.selectedParts.length == 1 && self.selectedParts[0].status != 1) {
                    location.href = partDetailUrl + '/' + self.selectedParts[0].id + '?public_setting=1';
                } else if (!isPublish && self.selectedUnPublishedPart()) {
                    location.href = partListUrl;
                }
                if (isPublish) {
                    if (self.page === 1) {
                        self.getImages();
                    } else {
                        self.page = 1;
                    }
                    self.isSubmittedImagePart = true;
                    toastr.success('UGCセットへの反映を完了しました');
                }
            }).catch(function (reason) {  });
        },
        seletedPulishedPart: function () {
            for (var index in this.selectedParts) {
                if (this.selectedParts[index].status == 1) {
                    return true;
                }
            }

            return false;
        },
        selectedUnPublishedPart: function () {
            for (var index in this.selectedParts) {
                if (this.selectedParts[index].status != 1) {
                    return true;
                }
            }

            return false;
        },
        resetSearch: function () {
            if (this.page > 1) {
                this.page = 1;
            } else {
                this.getImages();
            }
        },
        initSelectPart: function () {
            var self = this;
            this.$nextTick(function () {
                $('#select_parts').multiselect({
                    dropUp: true,
                    buttonWidth: "100%",
                    buttonClass: "select-parts-btn",
                    maxHeight: 300,
                    nonSelectedText: 'UGCセットを選択',
                    onChange: function (option, checked, select) {
                        var temp = $('#select_parts').val();
                        if (temp === null) {
                            temp = [];
                        }
                        imageListApp.selectedPartIds = temp;
                    },
                    onInitialized: function (select, container) {
                        var multiselect = $(".btn-offer .multiselect-container");

                        var maxHeight = 0;
                        if (self.partList.length <= 10) {
                            maxHeight = self.partList.length * 36;
                        } else {
                            maxHeight = 10 * 36;
                        }
                        var marginTop = maxHeight + 55;

                        multiselect.css('margin-top', '-' + marginTop + 'px');
                        multiselect.css('max-height', maxHeight + 'px');

                    }
                });
            });
        },
        selectImage: function(postIdStr) {
            var index = this.selectedImageIds.indexOf(postIdStr),
                image = this.imageList[postIdStr],
                errorIndex = this.errorImageIds.indexOf(postIdStr);

            if (errorIndex > -1) {
                var temp = this.selectedImageIds;
                this.selectedImageIds = [];
                this.selectedImageIds = temp;
                toastr.error('投稿が削除された、アカウントが非公開に変更、またはその他Instagramの仕様変更で利用できません');
                return;
            }

            if(index > -1) {
                this.selectedImageIds.splice(index, 1);
                if (image.offer_status != null) {
                    this.selectedOfferedImageCount --;
                }
                if (image.offer_status == 3) {
                    this.selectedApprovedImageCount --;
                }
            } else {
                this.selectedImageIds.push(postIdStr);
                if (image.offer_status != null) {
                    this.selectedOfferedImageCount ++;
                }
                if (image.offer_status == 3) {
                    this.selectedApprovedImageCount ++;
                }
            }
        },
        showConfirmPartLink: function () {
            var temp = [];
            for (var index in this.partList) {
                if (this.selectedPartIds.includes(this.partList[index].id)) {
                    temp.push(this.partList[index]);
                }
            }
            this.selectedParts = temp;
            if (this.selectedImageIds.length === 0) {
                toastr.error('画像を選択してください');
            } else if (this.selectedParts.length === 0) {
                toastr.error('UGCセットを選択してください');
            } else {
                $('#part_link_confirm').modal('show');
            }
        },
        isSelected: function (postIdStr) {
            return this.selectedImageIds.indexOf(postIdStr) > -1;
        },
        genViewId: function (postId) {
            return 'id_' + postId;
        },
        getPostIdFromPostIdStr: function (postIdStr) {
            return postIdStr.split('id_')[1];
        },
        getSelectedPostIds: function () {
            var result = [];
            for (var index in this.selectedImageIds) {
                var postIdStr = this.selectedImageIds[index];
                result.push(this.getPostIdFromPostIdStr(postIdStr));
            }

            return result;
        },
        setBigModalImage: function (imageId) {
            this.bigModalImage = this.imageList[imageId];
        },
        openBigModalWithImage: function (imageId) {
            this.setBigModalImage(imageId);
            this.$nextTick(function () {
                $('#big_image_modal').modal('show');
            });
        },
        nextBigModal: function () {
            var currentBigModalKey = this.genViewId(this.bigModalImage.post_id),
                allKeys = Object.keys(imageListApp.imageList),
                index = allKeys.indexOf(currentBigModalKey);
            if (allKeys.length > (index+1)) {
                this.bigModalImage = this.imageList[allKeys[index+1]];
            }
        },
        prevBigModal: function () {
            var currentBigModalKey = this.genViewId(this.bigModalImage.post_id),
                allKeys = Object.keys(imageListApp.imageList),
                index = allKeys.indexOf(currentBigModalKey);
            if (index > 0) {
                this.bigModalImage = this.imageList[allKeys[index-1]];
            }
        },
        openLinkProduct: function () {
            var self = this;
            if (this.productList == null) {
                axios.get(getAllProductsUrl).then(function (response) {
                    self.productList = response.data;

                }).catch(function (reason) {  });
            }
            this.shouldGenImageProductList = true;

            this.$nextTick(function () {
                $('#image_link_product_modal').modal('show');
            });
        },
        updateProductList: function () {

        },
        onImgError: function (postIdStr) {
            if (this.errorImageIds.indexOf(postIdStr) === -1) {
                this.errorImageIds.push(postIdStr);
            }
        }
    },
    mounted: function() {
        if(this.loadAsync) {
            var self = this;
            this.loadImgLoop = setInterval(function() {
                self.getImages();
            }, 3000); //5 seconds
        } else {
            this.getImages();
        }
        this.getPartList();
    },
    updated: function () {
        if (this.firstTime) {
            oldFunctionObject.initJqueryEvent();
            if (typeof initIntro != 'undefined') {
                initIntro();
            }
            if (typeof slideshowId != 'undefined') {
                var url = apiGetSlideshowDataUrl + '/' + slideshowId,
                    self = this;
                axios.get(url, {
                    enableBlockUI: false
                }).then(function (response) {
                    self.slideshow = response.data;
                    var imageList = [];
                    for (var index in response.data.images) {
                        imageList.push(self.genViewId(response.data.images[index].post_id));
                    }
                    self.selectedImageIds = imageList;
                    self.selectedImages = response.data.images;
                    self.selectedApprovedImageCount = response.data.images.length;
                    self.$nextTick(function () {
                        $('#js_images_slide_show').modal('show');
                    });
                }).catch(function (reason) {  });
            }
            this.firstTime = false;
        }
    }
});