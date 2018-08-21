Vue.config.devtools = true;


function Part(data) {
    this.id = data.id;
    this.kpi_url = part_kpi.replace('%s', this.id);
    this.url = part_detail.replace('%s', this.id);
    if (data.hasOwnProperty('viewImp')) {
        this.has_ab_test = true;
    } else {
        this.has_ab_test = false;
    }


    this.hasData = false;

    this.origin_impression = 0;
    this.origin_cv = 0;
    this.origin_cvr = 0;
    this.start_date = data.start_date;
    this.finish_date = data.finish_date;

    this.impression = 0;
    this.inview = 0;
    this.click = 0;
    this.ctr = 0.0;
    this.cv = 0;
    this.cvr = 0.0;
    this.dates = [];
    this.inviews = [];
    this.cvrs = [];
    this.ugcs = [];
    this.loading_status = true;
    this.loading_kpi = true;


    this.display_start_date = moment(data.start_at).format('YYYY/MM/DD HH:mm');
    this.display_close_date = moment(data.close_at).format('YYYY/MM/DD HH:mm');


    this.image_sort_value_update = api_part_update_sort_value.replace('%s', this.id);
    this.web_part_detail = web_part_detail.replace('%s', this.id);
    this.api_publish_part = api_publish_part.replace('%s', this.id);


    this.parseData(data);

    if (this.template == 2)
        this.ugc_limit = 20;
    else if (this.template == 3)
        this.ugc_limit = 1000;
    else
        this.ugc_limit = 100;
    var self = this;
    if (data.hasOwnProperty('images')) {
        data.images.forEach(function (img_data) {
            self.ugcs.push(new UGC(img_data));
        })
    }
    // ;
    // this.fetchKpi();
    // this.fetchHiddenImage();
}


Part.prototype.reset = function () {
    this.ugcs = [];
}

Part.prototype.publish = function () {
    axios.post(this.api_publish_part)
        .then(function (response) {
            toastr.success('公開されました');
            this.status = 1;
        })
        .catch(function (reason) {
            console.log(reason);
            var errors = reason.data.errors;
            errors.forEach(function (error) {
                toastr.error(error);
            })
        });
}


Part.prototype.fetchHiddenImage = function () {
    var self = this;
    this.loading_status = true;
    axios.get(this.url, {
        params: {
            start_date: this.start_date,
            finish_date: this.finish_date,
            display: 'hidden'
        }
    })
        .then(function (response) {
            var part_data = response.data;
            part_data.images.forEach(function (data) {
                var ugc = new UGC(data);
                ugc.hidden = true;
                self.ugcs.push(ugc);
            })
            self.loading_status = false;
        })
        .catch(function (reason) {
            self.loading_status = false;
            var errors = reason.data.errors;
            errors.forEach(function (error) {
                toastr.error(error);
            })
        });
}

Part.prototype.parseData = function (part_data) {
    var data = part_data;
    this.type = data.type;
    this.template = data.template;
    this.impression = 100;
    this.title = data.title;
    this.show_start_date = data.start_at;
    this.show_close_date = data.close_at;
    this.sort = data.sort;
    this.status = data.status;
    this.status_str = data.__status__str;
    this.template_str = data.__template__str;
    this.sort_str = data.__sort__str;
    if (part_data.hasOwnProperty('viewImp')) {
        this.has_ab_test = true;
        this.impression = part_data.viewImp ? part_data.viewImp : 0;
        this.inview = part_data.viewInview ? part_data.viewInview : 0;
        this.click = part_data.click ? part_data.click : 0;


        this.ctr = this.inview ? this.click * 100 / this.inview : 0.0;
        this.ctr = Math.round(this.ctr * 100) / 100;
        this.cv = part_data.viewCV ? part_data.viewCV : 0;
        this.cvr = this.inview == 0 ? 0 : number_format((this.cv / this.inview) * 100, 2);


        this.origin_impression = part_data.orgImp ? part_data.orgImp : 0;
        this.origin_cv = part_data.orgCV ? part_data.orgCV : 0;
        this.origin_cvr = part_data.orgCVR ? part_data.orgCVR : 0;

    } else {
        this.has_ab_test = false;
        this.impression = part_data.impression_count ? part_data.impression_count : 0;
        this.inview = part_data.inview_count ? part_data.inview_count : 0;
        this.click = part_data.click ? part_data.click : 0;
        this.ctr = parseInt(this.inview) ? this.click * 100 / this.inview : 0.0;
        this.ctr = Math.round(this.ctr * 100) / 100;
        this.cv = part_data.cv_count ? part_data.cv_count : 0;
        this.cvr = this.inview == 0 ? 0 : number_format((this.cv / this.inview) * 100, 2);
    }
}


Part.prototype.fetch = function (silent = false) {
    var self = this;
    if (silent)
        this.loading_status = true;
    axios.get(this.url, {
        params: {
            start_date: this.start_date,
            finish_date: this.finish_date
        }
    })
        .then(function (response) {
            var part_data = response.data;
            self.parseData(part_data);
            part_data.images.forEach(function (data) {
                self.ugcs.push(new UGC(data));
            })


            self.loading_status = false;
        })
        .catch(function (reason) {
            self.loading_status = false;
            var errors = reason.data.errors;
            errors.forEach(function (error) {
                toastr.error(error);
            })
        });
}

Part.prototype.fetchKpi = function () {
    var self = this;

    self.loading_kpi = true;
    axios.get(this.kpi_url, {
        params: {
            start_date: this.start_date,
            finish_date: this.finish_date
        }
    })
        .then(function (response) {
            self.loading_kpi = false;
            var data = response.data;
            var dates = [];
            var inviews = [];
            var cvrs = [];

            data.forEach(function (day) {
                cvr = day['inview'] == 0 ? 0 : number_format((day['cv'] / day['inview']) * 100, 2);
                dates.push(day['date'].replace(/\-/g, "/"));
                inviews.push(parseInt(day['inview']));
                cvrs.push(cvr);
            })
            self.inviews = inviews;
            self.dates = dates;
            self.cvrs = cvrs;
        })
        .catch(function (reason) {
            console.log(reason);
            self.hasData = false;
            self.loading_kpi = false;
            var errors = reason.data.errors;
            errors.forEach(function (error) {
                toastr.error(error);
            })
        });
}

Part.prototype.number_format = function (n, d, x, s, c) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (d > 0 ? '\\D' : '$') + ')',
        num = parseFloat(n).toFixed(Math.max(0, ~~d));

    return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
}


function UGC(data) {
    this.id = data.id;
    this.hidden = false;
    this.click = data.image_click_count ? data.image_click_count : 0;
    this.cv = data.cv_count ? data.cv_count : 0;
    this.img_url = data.img_url;
    this.post_id = data.post_id;
    this.url = '/advertiser/post/' + this.post_id;
    this.image_id = data.image_id;
    this.add_to_part_date = data.add_to_part_date;
    this.sort_value = parseInt(data.order_manual);
    this.outer_contents_product_link_count = data.outer_contents_product_link_count ? data.outer_contents_product_link_count : 0;
}