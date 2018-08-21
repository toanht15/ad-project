Vue.config.devtools = true;

function Page(data) {
    this.id = data.id;
    this.editing = false;
    this.image = data.view_product_image_url ? data.view_product_image_url : data.product_image_url;
    this.title = data.view_title ? data.view_title : data.title;
    this.images = [];
    this.url = data.url;
    this.status = data.status;
    this.base_url = apiPageDetail;
    this.validateMessages = {};
    this.crawled_flag = data.crawled_flg;
    this.stop_flag = data.stop_flg;
    this.loading_status = true;
    this.status_str = data.__status__str;
    this.fetch();

    if (this.stop_flag == 1 && this.crawled_flag == 1)
        this.unsuccessful = true;
    else
        this.unsuccessful = false;
}

function Image(data, page) {
    this.id = data.id;
    this.url = data.url;
    this.page = page;
    this.base_url = apiDeleteImage;

}

Image.prototype.delete = function () {
    url = this.base_url.replace('%s', this.id);
    console.log(url);
    var self = this;
    axios.post(url, {
        'page_url': self.page.url
    })
        .then(function (response) {
            self.page.images.splice(self.page.images.indexOf(self), 1);
        })
        .catch(function (reason) {
            console.log(reason);
        });

}

Page.prototype.fetch = function () {
    url = this.base_url.replace('%s', this.id);
    var self = this;
    axios.get(url)
        .then(function (response) {
            response.data.images.forEach(function (img) {
                self.images.push(new Image(img, self))
            })
            self.loading_status = false;
        })
        .catch(function (reason) {
            self.loading_status = false;
        });
}

Page.prototype.hasError = function (fieldName) {
    var errors = this.validateMessages;
    if (errors.hasOwnProperty(fieldName))
        return true;
    else
        return false;
};

Page.prototype.update = function () {
    url = this.base_url.replace('%s', this.id);
    console.log(this.id);
    var self = this;
    axios.post(url, {
        'title': self.title,
        'image': self.image
    }, {enableValidationError: false})
        .then(function (response) {
            self.editing = !self.editing;
            self.validateMessages = {};
        })
        .catch(function (reason) {
            var test = reason.data.errors;
            self.validateMessages = test;
        });
};


pages = [];

data.forEach(function (i) {
    pages.push(new Page(i))
});


var app = new Vue({
    el: '.app',
    data: {
        'items': pages,
    },
    methods: {
        onClick: function (item, event) {
            console.log(item.editing);
            item.update();
        },
        toggleItemStatus: function (item, event) {
            item.editing = !item.editing;
            this.$nextTick(function () {
                $('[data-toggle="tooltip"]').tooltip({
                    container: 'body'
                });
            });
        },
        onDelete: function (item, image, event) {
            if (confirm('商品ページ紐付を解除してよろしいですか？')) {
                image.delete()
            }
        }
    }
});
