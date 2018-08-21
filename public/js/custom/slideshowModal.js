Vue.component('slideshow_confirm_vue_component', {
    template: '#slideshow_confirm_vue_component',
    props: ['slideshowimages', 'slideshow'],
    data: function () {
        return {
            imageListSlider: null,
            progressBar: null
        }
    },
    computed: {
        videoDuration: function () {
            return this.slideshow.time_per_img * this.slideshowimages.length;
        }
    },
    watch: {
        slideshow: function () {
            if (this.slideshow.url != null) {
                this.initVideoPlayer(this.slideshow.url);
            }
        }
    },
    methods: {
        removeImage: function (postId) {
            this.$parent.selectImage(this.$parent.genViewId(postId));
        },
        createVideo: function (isPreview) {
            if (this.slideshow.video_type === 1 && this.videoDuration > 15) {
                toastr.error('Instagram Storiesビデオの再生最大時間は15秒です');
                return;
            }
            if (this.slideshowimages.length === 0) {
                toastr.error('画像を選択してください');
                return;
            }

            $.blockUI({
                message: '作成中です',
                baseZ: 2000
            });
            this.createProgressBar(1,1);
            this.updateProgressBar(isPreview);

            var data = Utility.objectifyForm('create_slideshow_form'),
                self = this;
            data.is_fix = !isPreview;
            axios.post(createSlideshowAPIUrl + '/' + this.slideshow.id, data, {
                enableBlockUI: false
            }).then(function (response) {
                var video = self.slideshow;
                video.url = response.data.url;
                video.size = response.data.size;
                self.slideshow = video;
                self.initVideoPlayer(self.slideshow.url);
                clearInterval(self.progressBar);
                self.createProgressBar(100,0);
                setTimeout(function(){
                    Utility.unblockUI();
                }, 500);
            }).catch(function (reason) {
                clearInterval(self.progressBar);
                Utility.unblockUI();
            });
        },
        createProgressBar: function(progression, duration) {
            var html = '作成中です。<br>約'+duration+'秒<div class="progress"> <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:'+ progression +'%"> </div> </div>';

            $('.blockUI.blockMsg.blockPage').html(html).css('padding-top', 20);
        },
        updateProgressBar: function(isPreview) {
            var progression = 0,
                duration = 0,
                self = this;
            this.progressBar = setInterval(function() {
                if(self.slideshow.effect_type == 0 || self.slideshow.effect_type == 1) {
                    if(isPreview) {
                        duration = (1 * self.slideshowimages.length) + 1;
                    } else {
                        duration = (4 * self.slideshowimages.length) + 10;
                    }
                }
                else {
                    if(isPreview) {
                        duration = (((self.slideshow.time_per_img * 30) * self.slideshowimages.length) * 0.15) + 50;
                    } else {
                        duration = (((self.slideshow.time_per_img * 30) * self.slideshowimages.length) * 0.4) + 50;
                    }
                }

                progression = progression + 100 / duration;
                self.createProgressBar(Math.round(progression), duration);
                if (progression >= 100) {
                    clearInterval(self.progressBar);
                }
            }, 1000);
        },
        openEditImageModal: function (image) {
            $('#js_images_slide_show').modal('hide');
            if (this.slideshow.video_type == 1) {
                cropImage.defaultAspectRatio = 0.5625;
            } else {
                cropImage.defaultAspectRatio = 1;
            }
            $('#edit_modal_image').attr('src', image.image_url);
            $('.edit_image_modal').css({'visibility': 'hidden', 'display': 'block'}).modal('show');
            $('[name="origin_image_id"]').val(image.image_id);
        },
        initImageList: function () {
            if (this.imageListSlider != null) {
                this.imageListSlider.destroy();
                this.imageListSlider = null;
            }
            this.imageListSlider = $(".slide-show-ugc").lightSlider({
                slideMove: 1,
                loop: false,
                keyPress: true,
                slideMargin: 5,
                autoWidth: false
            });

            $('[data-toggle="tooltip"]').tooltip({
                animated : 'fade',
                placement : 'top'
            });
        },
        initVideoPlayer: function (url) {
            this.$nextTick(function () {
                var aspectRatio = '"1:1"';

                if (this.slideshow.video_type == 1) {
                    aspectRatio = '"9:16"';
                }
                $('#no_video_img').addClass('hidden');
                $('.create-slideshow-video-preview-tmp').attr('data-setup', '{"aspectRatio":'+aspectRatio+', "fluit":"true"}');
                $('.create-slideshow-video-preview-tmp').find('source').attr('src', url);
                console.log(url);
                videojsCustom.initVideo('.create-slideshow-video-preview-tmp');
            });
        },
        changeVideoType: function () {
            this.slideshow.url = null;
            videojsCustom.clearVideo();
        }
    },
    mounted: function () {

        this.$nextTick(function () {
            var self = this;
            $('#js_images_slide_show').on('show.bs.modal', function (e) {
                $(this).css('visibility', 'visible');
                $(this).css('display', 'block');
                self.initImageList();
            }).on('hide.bs.modal', function () {
                $(this).css('visibility', 'hidden');
            });

            $('.edit_image_modal').on('show.bs.modal', function (e) {
                $('.edit_image_modal').css('visibility','visible');
                cropImage.imageCroper = $('.img-container > img');
                cropImage.initCropImage();

            }).on('hide.bs.modal', function (e) {
                cropImage.imageCroper.cropper('destroy');
                $('#js_images_slide_show').modal('show');
            });
        });
    }
});

saveEditedImageCallback = function(data) {
    $('.edit_image_modal').modal('hide');
    $('#js_slideshow_image_'+data.post_id).find('img').attr('src', data.image_url);
    $('#js_slideshow_image_'+data.post_id).find('[name="image_ids[]"]').val(data.id);
    $('#js_slideshow_image_'+data.post_id).find('[data-image-id]').attr('data-image-id', data.id);
};
