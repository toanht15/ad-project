var slideshowListApp = new Vue({
    el: '#app',
    data: {
        slideshowId: 0,
        slideshowImages: [],
        slideshow: {
            url: null,
            video_type: 0,
            effect_type: 0,
            time_per_img: 2,
            size: 0,
            id: 0
        }
    },
    methods: {
        fetchSlideshowData: function (slideshowId) {
            var url = apiGetSlideshowDataUrl + '/' + slideshowId,
                self = this;
            axios.get(url).then(function (response) {
                self.setSlideshowData(response.data.images, response.data);
                $("#add_image_url").unbind('click').click(function () {
                    location.href = imageListURL + '?slideshow_id=' + self.slideshow.id;
                })
            }).catch(function (reason) {  });
        },
        openModal: function (slideshowId) {
            this.fetchSlideshowData(slideshowId);
        },
        setSlideshowData: function (images, slideshow) {
            this.slideshowImages = images;
            this.slideshow = slideshow;
            this.$nextTick(function () {
                $('#js_images_slide_show').modal('show');
            });
        },
        selectImage: function (postId) {
            for (var index in this.slideshowImages) {
                if (this.slideshowImages[index].post_id == postId) {
                    this.slideshowImages.splice(index, 1);
                    break;
                }
            }
        },
        genViewId: function (postId) {
            return postId;
        }
    }
});
