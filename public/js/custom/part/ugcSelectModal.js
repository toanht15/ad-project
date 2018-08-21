Vue.component('image-component', {
    props: ['image', 'selected_items'],
    template: '#image_detail',
    data: function () {
        return {
            local_selected_items: this.selected_items,
            errorImage: false
        }
    },
    methods: {
        update: function (value) {
            if (this.errorImage) {
                this.local_selected_items = false;
                toastr.error('投稿が削除された、アカウントが非公開に変更、またはその他Instagramの仕様変更で利用できません');
                return;
            }

            var checked = this.$el.getElementsByTagName('input')[0].checked;
            if (checked)
                this.$emit('add', value)
            else
                this.$emit('delete', value)
        },
        onImgError: function () {
            this.errorImage = true;
        }
    },
});


Vue.component('select_ugc_modal', {
    props: ['search_conditions', 'existed_items', 'part'],
    template: '#images_modal',
    mounted: function () {
        var self = this;
        $(this.$el).on('shown.bs.modal', function () {
            self.fetchImages();
        });

        $(this.$el).on('hidden.bs.modal', function () {
            self.reset();
        });
    },
    data: function () {
        return {
            search_condition_id: 0,
            apiRegisterImage: apiRegisterImage,
            status: -1,
            order: 'new',
            page: 1,
            search_url: api_get_images,
            selected_items: [],
            next_page: null,

            status_list: [
                {
                    id: -1,
                    name: 'ALL'
                },
                {
                    id: 1,
                    name: 'リクエスト申請中'
                },
                {
                    id: 2,
                    name: 'リクエスト承認済'
                },
                {
                    id: 3,
                    name: '出稿済'
                },
                {
                    id: 4,
                    name: 'リクエスト失敗'
                },
            ],

            sort_orders: [
                {
                    value: 'new',
                    name: '新着投稿順'
                },
                {
                    value: 'like',
                    name: 'Like数順'
                },
            ],
            images: [],
        }
    },
    computed: {
        search_condition: function () {
            return {
                search_condition_id: this.search_condition_id,
                status: this.status,
                order: this.order,
                page: this.page
            }
        },
    },
    watch: {
        search_condition_id: function (oldValue, newValue) {
            this.fetchImages();
        },
        status: function (oldValue, newValue) {
            this.fetchImages();
        },
        order: function (oldValue, newValue) {
            this.fetchImages();
        },
        selected_items: function (oldValue, newValue) {
        },
        page: function (oldValue, newValue) {
            this.nextPageImages();
        }

    },
    methods: {
        fetchImages: function () {
            var self = this;
            axios.get(this.search_url, {
                params: {
                    search_condition_id: this.search_condition_id,
                    status: this.status,
                    page: this.page,
                    order: this.order,
                    limit: 12,
                }
            })
                .then(function (response) {
                    self.images = [];
                    var data = response.data;
                    if (data.next_page_url) {
                        self.next_page = self.page + 1;
                    } else
                        self.next_page = null

                    data.data.forEach(function (image) {
                        if (!self.existed_items.includes(image.post_id)) {
                            self.images.push(image);
                        }
                    })
                })
                .catch(function (reason) {
                    console.log(reason);
                    var errors = reason.data.errors;
                    errors.forEach(function (error) {
                        toastr.error(error);
                    })
                });
            return []
        },


        nextPageImages: function () {
            console.log('next page images');
            var self = this;
            axios.get(this.search_url, {
                params: {
                    search_condition_id: this.search_condition_id,
                    status: this.status,
                    page: this.page,
                    order: this.order,
                    limit: 12,
                }
            })
                .then(function (response) {
                    var data = response.data;
                    if (data.next_page_url) {
                        self.next_page = self.page + 1;
                    } else
                        self.next_page = null

                    console.log(data);
                    data.data.forEach(function (image) {
                        if (!self.existed_items.includes(image.post_id)) {
                            self.images.push(image);
                        }
                    })
                })
                .catch(function (reason) {
                    console.log(reason);
                    var errors = reason.data.errors;
                    errors.forEach(function (error) {
                        toastr.error(error);
                    })
                });
            return []
        },
        updateSearchCondition: function (id, event) {
            if (event) event.preventDefault();
            this.search_condition_id = id;
        },
        registerImage: function () {
            var self = this;
            axios.post(this.apiRegisterImage, {
                post_ids: this.selected_items,
                part_ids: [this.part.id]
            }).then(function (response) {
                self.selected_items = [];
                self.$emit('ugc_register');
                $(self.$el).modal('hide');

            }).catch(function (reason) {
                var errors = reason.data.errors;
                for (var key in errors) {
                    toastr.error(errors[key]);
                }
            });
        },

        reset: function () {
            this.images = [];
            this.selected_items = [];
        },

        addImage: function (value) {
            this.selected_items.push(value);
        },
        deleteImage: function (value) {
            var index = this.selected_items.indexOf(value);
            if (index > -1) {
                this.selected_items.splice(index, 1);
            }
        }
    }
});
