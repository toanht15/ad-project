Vue.config.devtools = true;
$(document).ready(function () {

    $('form#cv_setting').submit(function (e) {
        e.preventDefault();
        var formData = Utility.objectifyForm('cv_setting');
        console.log(formData);
        axios.post(cv_setting, formData)
            .then(function (response) {
                $('#cv_setting_modal').modal('toggle');
            })
            .catch(function (reason) {
                console.log(reason)
            });
    });
    var flag = true;
    if ($('.service-bar >li').length == 1 && $('.service-bar >li > a[href="#profile"]').length == 1) {
        $('.service-bar >li > a').tab('show');
        owner_dashboard.init();
    }


    $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
        var target = $(e.target).attr("href") // activated tab`
        if (target == '#profile' && flag) {
            flag = !flag;
            owner_dashboard.init();
        }
    })

});

Vue.component('part-image-component', {
    props: ['ugc', 'part', 'show_hidden_image'],
    template: '#image_template',
    methods: {
        deleteImage: function () {
            var self = this;
            var data = {
                partId: this.part.id,
                postId: this.ugc.post_id
            }
            axios.post(part_image_delete, data)
                .then(function (response) {
                    var index = self.part.ugcs.indexOf(self.ugc);
                    console.log(index);
                    if (index > -1) {
                        self.part.ugcs.splice(index, 1);
                    }
                }).catch(function (reason) {
            });
        }
    },
    computed: {
        display: function () {
            return (!this.ugc.hidden) || this.show_hidden_image;
        },
        sort_value: {
            get: function () {
                return this.ugc.sort_value;
            },
            set: function (newValue) {
                console.log(newValue);
                this.$emit('sort_value_updated', [this.ugc.image_id, newValue]);
            }
        }
    }
});


Vue.component('line-chart', {
    extends: VueChartJs.Bar,
    mixins: [VueChartJs.mixins.reactiveProp],
    props: ['chartData', 'options'],
    mounted() {
        this.renderChart(this.chartData, this.options);
    },
    watch: {
        chartData: function (oldData, newData) {
            console.log("ChartDataChanged");
            this.$data._chart.update();
        }
    }
})


Vue.component('part', {
    props: ['part'],
    template: '#part_template',
    computed: {
        chart_data: function () {
            return {
                labels: this.part.dates,
                type: 'bar',
                datasets: [
                    {
                        type: 'line',
                        label: 'CVR',
                        borderColor: 'orange',
                        borderWidth: 2,
                        fill: false,
                        data: this.part.cvrs
                    },
                    {
                        label: 'Inview',
                        yAxisID: 'Inview',
                        data: this.part.inviews,
                        fillColor: "blue",
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                ]
            }
        },
        chart_options: function () {
            if (this.part.inviews.length > 0) {
                var max_value = this.part.inviews.reduce(function (a, b) {
                    return Math.max(a, b);
                });
                var max_cvr = this.part.cvrs.reduce(function (a, b) {
                    return Math.max(a, b);
                })
            } else {
                var max_value = 0;
                var max_cvr = 0;
            }

            max_value = max_value ? max_value : 100;
            max_cvr = max_cvr ? Math.round(max_cvr) + 1 : 100;
            return {
                responsive: true, maintainAspectRatio: false, scales: {
                    yAxes: [
                        {
                            gridLines: {
                                display: false
                            },
                            id: 'CVR',
                            type: 'linear',
                            display: true,
                            position: 'right',
                            ticks: {
                                beginAtZero: true,
                                max: max_cvr,
                                min: 0,
                            },
                            scaleLabel: {
                                display: true,
                                labelString: 'CVR'
                            }
                        },
                        {
                            id: 'Inview',
                            type: 'linear',
                            display: true,
                            position: 'left',
                            ticks: {
                                beginAtZero: true,
                                max: max_value,
                                min: 0,
                            },
                            scaleLabel: {
                                display: true,
                                labelString: 'Inview'
                            }
                        }
                    ],
                    xAxes: [{
                        ticks: {
                            fontSize: 8
                        }
                    }]
                }
            }
        },
        topUgcs: function () {
            return this.part.ugcs.filter(function (ugc) {
                return !ugc.hidden;
            }).slice(0, 4);
        },
    },
});


Vue.component('daterangepicker', {
    props: ['value', 'default_start_date', 'default_finish_date'],
    template: '<input type="text" style="width: 200px" class="form-control" v-model="value"/>',
    mounted: function () {
        var self = this;
        var start = moment(this.default_start_date);
        var finish = moment(this.default_finish_date);
        $(this.$el).daterangepicker({
            startDate: start,
            endDate: finish,
            ranges: {
                'Today': [moment(), moment().add(1, 'days')],
                'Yesterday': [moment().subtract(1, 'days'), moment()],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            maxDate: moment(),
            locale: {
                format: 'YYYY/MM/DD'
            },
        }, function (start, end, label) {
            createCookie('start_date', start.format('YYYY/MM/DD'));
            createCookie('finish_date', end.format('YYYY/MM/DD'));
            $(self.$el).val(start.format('YYYY/MM/DD') + ' - ' + end.format('YYYY/MM/DD'));
            self.update(start.format('YYYY/MM/DD') + ' - ' + end.format('YYYY/MM/DD'));
        });

    },
    methods: {
        update: function (value) {
            console.log('value: ' + value);
            this.$emit('update', value);
        }
    },
});


var owner_dashboard = new Vue({
    el: '#app2',
    data: {
        message: 'Hello World',
        parts: [],
        start_date: start_date,
        finish_date: finish_date,
        hasData: false,
        init_loading: true,
    },
    created() {
        if (readCookie('start_date') && readCookie('finish_date')) {
            this.start_date = readCookie('start_date');
            this.finish_date = readCookie('finish_date');
        }
        console.log(this.start_date);
        console.log(this.finish_date);
    },
    methods: {
        init: function () {
            var self = this;
            var flg = false;
            axios.get(part_list_api)
                .then(function (response) {
                    console.log('success');
                    response.data.forEach(function (data) {
                        data.start_date = self.start_date;
                        data.finish_date = self.finish_date;
                        var part = new Part(data);
                        console.log(part.status);
                        if (part.status == 1)
                            flg = true;
                        part.fetch();
                        part.fetchKpi();
                        self.parts.push(part);
                    })
                    console.log(flg);
                    self.hasData = flg;
                    self.init_loading = false;
                })
                .catch(function (reason) {
                    self.init_loading = false;
                });
        },
        updateDateRange: function (value) {
            console.log('updateDateRange');
            console.log('updated value: ' + value);
            var re = /(\d{4}[\/.]\d{2}[\/.]\d{2}) - (\d{4}[\/.]\d{2}[\/.]\d{2})/;
            var found = value.match(re);
            if (found) {
                this.start_date = found[1];
                this.finish_date = found[2];
            }
        },
    },
    watch: {
        dateRange: function () {
            var self = this;
            this.parts.forEach(function (part) {
                part.start_date = self.start_date;
                part.finish_date = self.finish_date;
                part.reset();
                part.fetchKpi();
                part.fetch(true);
            })
        },
        hasData: function (newValue, oldValue) {
            console.log('updated', oldValue, newValue);
            if (newValue) {
                this.parts.forEach(function (part) {
                    console.log('update hasData')
                    part.hasData = newValue;
                });
            }
        }
    },
    computed: {
        dateRange: {
            get: function () {
                return this.start_date + ' - ' + this.finish_date;
            },
            set: function (val) {
                dateRange = val.split("-");
                this.start_date = dateRange[0].trim();
                this.finish_date = dateRange[1].trim();
            }
        },
        colClass: {
            get: function () {
                if (this.parts.length == 1) {
                    return 'col-lg-12'
                }
                else {
                    return 'col-lg-6';
                }
            }
        }
    }
})

