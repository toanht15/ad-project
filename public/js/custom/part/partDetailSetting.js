Vue.component('part_detail_setting_template', {
    template: '#part_detail_setting_template',
    props: ['partid', 'loadnow', 'redirect', 'status'],
    data: function () {
        return {
            part: null,
            partDesign: null,
            useUrlExcludeString: false,
            useShowNextButtonCss: false,
            submitUrlType: 0,
            shouldMergePart: false
        }
    },
    watch: {
        partid: function (newValue, oldValue) {
            this.fetchData();
        },
        useUrlExcludeString: function () {
            this.$nextTick(function () {
                this.initCustomUi();
            });
        },
        imgHeight: function (newValue, oldValue) {
            if (oldValue != -1) {
                this.shouldMergePart = true;
            }
        }
    },
    computed: {
        imageSizePc: function () {
            return 450 * (1 - this.partDesign['3_2'] * (this.partDesign['3_1'] - 1) / 100) / this.partDesign['3_1'];
        },
        imageSizeSmp: function () {
            return 450 * (1 - this.partDesign['3_7'] * (this.partDesign['3_6'] - 1) / 100) / this.partDesign['3_6'];
        },
        spaceSizePc: function () {
            return 450 * this.partDesign['3_2'] / 100;
        },
        spaceSizeSmp: function () {
            return 450 * this.partDesign['3_7'] / 100;
        },
        imgHeight: function () {
            if (this.part == null) {
                return -1;
            }
            return this.part.height;
        }
    },
    methods: {
        fetchData: function () {
            var partSettingUrl = apiPartBasicSettingUrl.replace(/partId/, this.partid),
                partDesignUrl = apiPartDesignSettingUrl.replace(/partId/, this.partid),
                self = this;
            axios.get(partSettingUrl)
                .then(function (response) {
                    self.part = response.data;
                    self.useUrlExcludeString = self.part.url_exclude_string.length > 0;
                    self.part.close_at_date = self.part.close_at_date.replace(/\-/g, "/") + ' ' + self.part.close_at_time.substring(0, self.part.close_at_time.length - 3);
                    self.part.start_at_date = self.part.start_at_date.replace(/\-/g, "/") + ' ' + self.part.start_at_time.substring(0, self.part.close_at_time.length - 3);

                    if (self.part.close_timing_type == '0') {
                        self.part.close_timing_type = 0;
                    }
                    if (self.part.show_text_flg == '0') {
                        self.part.show_text_flg = 0;
                    }
                    if (!self.part.item_per_page_pc) {
                        self.part.item_per_page_pc = 20;
                    }
                    if (!self.part.item_per_page_sp) {
                        self.part.item_per_page_sp = 20;
                    }
                    if (!self.part.url_match_type) {
                        self.part.url_match_type = 1;
                    }
                    self.initCustomUi();
                })
                .catch(function (reason) {
                });

            axios.get(partDesignUrl, {
                    enableBlockUI: false
                }
            ).then(function (response) {
                self.partDesign = response.data;
                if ('3_3' in self.partDesign) {
                    self.useShowNextButtonCss = self.partDesign['3_3'].length > 0;
                }
                self.initCustomUi();
            }).catch(function (reason) {
            })
        },
        initCustomUi: function () {
            this.$nextTick(function () {
                var self = this;
                $(".form_datetime").datetimepicker({format: 'yyyy/mm/dd hh:ii'});
                $('[name="bgr_color"]').on('ifChanged', function (event) {
                    if ($(this).is(':checked')) {
                        self.partDesign['0_1'] = $(this).val();
                    }
                });
                $('.color-picker').colorpicker({
                    format: 'hex'
                }).on('changeColor',
                    function (ev) {
                        self.partDesign['2_1'] = $("#arrow-color").val();
                    });
                $('[data-toggle="tooltip"]').tooltip({
                    container: 'body'
                });
            });
        },
        saveSetting: function () {
            var url = apiSubmitUrl[this.submitUrlType],
                self = this;
            url = url.replace(/partId/, this.partid);
            this.part.start_at_date = $('[name="start_at_date"]').val().replace(/\//g, "-");
            this.part.close_at_date = $('[name="close_at_date"]').val().replace(/\//g, "-");

            var postData = {};
            if (this.submitUrlType == 0) {
                postData = this.part;
            } else {
                if (this.shouldMergePart) {
                    postData = Object.assign(this.partDesign, this.part);
                } else {
                    postData = this.partDesign;
                }
            }
            postData.parts_id = this.partid;
            postData.part_status = this.status;
            axios.post(url, postData)
                .then(function (response) {
                    toastr.success('UGCセットの設定を更新しました');
                    if (typeof self.redirect != 'undefined') {
                        location.href = self.redirect;
                    } else {
                        location.reload();
                    }
                })
                .catch(function (reason) {
                    toastr.error('UGCセットの設定が更新できませんでした');
                });
        }
    },
    mounted: function () {
        if (this.loadnow) {
            this.fetchData();
        }
        this.initCustomUi();
    }
});

