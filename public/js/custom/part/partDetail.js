Vue.config.devtools = true;


var partDetail = {
    openDetailModal: function () {
        $('#part_detail_setting_modal').modal('show');
    },

    openpublishlModal: function () {
        $('#publish_part_modal').modal('show');
    }

};


Vue.component('line-chart', {
    extends: VueChartJs.Bar,
    mixins: [VueChartJs.mixins.reactiveProp],
    props: ['chartData', 'options'],
    mounted: function () {
        this.renderChart(this.chartData, this.options)
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
    data: function () {
        return {
            show_hidden_image: false,
            sort_values: {}
        }
    },
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
            return this.part.ugcs;
        },
        numberDisplayImage: function () {
            return this.part.ugcs.filter(function (ugc) {
                return !ugc.hidden
            }).length
        }
    },
    methods: {
        update_sort_value: function (value) {
            this.sort_values[value[0]] = value[1];
            console.log(this.sort_values);
        },
        submit_sort_value: function () {
            console.log(this.sort_values);
            axios.post(this.part.image_sort_value_update, {
                values: this.sort_values
            }).then(function (response) {
                self.selected_items = []

            }).catch(function (reason) {
                var errors = reason.data.errors;
                console.log(errors);
                for (var key in errors) {
                    errors[key].forEach(function (error) {
                        toastr.error(error);
                    })
                }
            });
        }
    }
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
            if (confirm("UGCの登録を解除してもよろしいですか？"))
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


var vm = new Vue({
    el: '#app2',
    data: {
        part: new Part(part_data),
        start_date: start_date,
        finish_date: finish_date,
        search_conditions: searchConditionList,
    },
    created: function () {
        if (readCookie('start_date') && readCookie('finish_date')) {
            this.start_date = readCookie('start_date');
            this.finish_date = readCookie('finish_date');
        }
        if (this.part.status != 1) {
            this.part.hasData = false;
        } else {
            this.part.hasData = true;
        }

        if (shouldOpenEditModal === 1) {
            this.$nextTick(function () {
                partDetail.openDetailModal();
            });
        }
    },
    mounted: function () {
        this.part.start_date = this.start_date;
        this.part.finish_date = this.finish_date;

        this.update_part();
    },
    methods: {
        updateDateRange: function (value) {
            var re = /(\d{4}[\/.]\d{2}[\/.]\d{2}) - (\d{4}[\/.]\d{2}[\/.]\d{2})/;
            var found = value.match(re);
            if (found) {
                this.start_date = found[1];
                this.finish_date = found[2];
            }
        },

        update_part: function () {
            this.part.reset();
            this.part.fetchKpi();
            this.part.fetch(true);
            this.part.fetchHiddenImage();
        },

        update_image: function () {
            this.part.reset();
            this.part.fetch(true);
            this.part.fetchHiddenImage();
        }
    },
    watch: {
        dateRange: function () {
            console.log('update');
            var self = this;
            this.part.start_date = self.start_date;
            this.part.finish_date = self.finish_date;
            this.update_part();
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
        },
        existed_items: function () {
            var posts = [];
            this.part.ugcs.forEach(function (ugc) {
                if (ugc.post_id)
                    posts.push(ugc.post_id)
            })
            return posts;
        }

    }
})

