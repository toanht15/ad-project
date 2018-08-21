Vue.component('add-product-page-modal', {
    template: '#add_product_page',
    data: function() {
        return {
            siteMapErrors: [],
            urlListErrors: [],
            sitemap: null,
            goalSetting: false,
            urlList: null,
            cvPages: [],
            cvPageId: null
        }
    },
    watch: {
        goalSetting: function () {
            if (this.goalSetting && this.cvPages.length == 0) {
                var self = this;
                axios.get(apiGetCvPageUrl)
                    .then(function (response) {
                       self.cvPages = response.data;
                    })
            }
            if (!this.goalSetting) {
                this.cvPageId = null;
            }
        }
    },
    methods: {
        addBySitemap: function () {
            if (this.validateSitemap()) {
                axios.post(apiAddProductBySitemap, {
                    sitemap_url: this.sitemap,
                    match_cv_page_id: this.cvPageId
                }).then(function (response) {
                    window.location.reload();
                }).catch(function () {
                    window.location.reload();
                });
            }
        },
        addByUrlList: function () {
            if (this.validateUrlList()) {
                var self = this;
                axios.post(apiAddProductByUrlList, {
                    url_list: this.urlList
                }).then(function (response) {
                    window.location.reload();
                }).catch(function (response) {
                    if (response.data.errors.url_list) {
                        self.urlListErrors.push(response.data.errors.url_list)
                    } else {
                        window.location.reload()
                    }
                });
            }
        },
        isUrl: function (url) {
            return /^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:[/?#]\S*)?$/i.test(url);
        },
        validateSitemap: function () {
            this.siteMapErrors = [];
            if (!this.sitemap) {
                this.siteMapErrors.push("サイトマップのURLを入力してください。");
                return false;
            }
            if (!this.isUrl(this.sitemap)){
                this.siteMapErrors.push("URLを正しく入力してください");
                return false;
            }

            return true;
        },
        validateUrlList: function () {
            this.urlListErrors = [];
            if (!this.urlList) {
                this.urlListErrors.push('URLを入力してください')
                return false;
            }

            return true;
        }
    }
})