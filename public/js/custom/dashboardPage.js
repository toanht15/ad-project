Vue.config.devtools = true;


var app = new Vue({
    el: '#app',
    data: {
        totalSpend: 0,
        totalClick: 0,
        totalImp: 0,
        mediaType: [],
        timeRange: null,
        topPerformanceUgc: [],
        slider: null,
        ugcSpending: 0,
        ugcSpended: 0,
        ugcApproved: 0,
        ugcOffer: 0,
        conversionType: null,
        conversion: 0
    },
    computed: {
        totalCtr: function () {
            if (this.totalImp != null && this.totalImp > 0) {
                return number_format(this.totalClick * 100 / this.totalImp, 2);
            }
            return 0;
        },
        ugcSpendingRate: function () {
            return this.ugcSpending * 100 / this.ugcOffer;
        },
        ugcSpendedRate: function () {
            return (this.ugcSpended - this.ugcSpending) * 100 / this.ugcOffer;
        },
        ugcApprovedRate: function () {
            return (this.ugcApproved - this.ugcSpended) * 100 / this.ugcOffer;
        },
        ugcOfferRate: function () {
            return (this.ugcOffer - this.ugcApproved) * 100 / this.ugcOffer;
        },
        cvr: function () {
            return number_format(this.totalClick > 0 ? this.conversion * 100 / this.totalClick : 0, 2);
        },
        cpa: function () {
            return number_format(this.conversion > 0 ? this.totalSpend / this.conversion : 0)
        }
    },
    watch: {
        mediaType: function (val, oldVal) {
            this.getGraphData();
            this.getTopPerformanceUgc();
            this.getConversionKpi();
        },
        timeRange: function (val, oldVal) {
            this.getGraphData();
            this.getTopPerformanceUgc();
            this.getConversionKpi();
        },
        conversionType: function (val, oldVal) {
            this.getConversionKpi();
        },
        topPerformanceUgc: function (val, oldVal) {
            //if (this.slider != null) {
            //    this.slider.destroy();
            //}
            //this.slider = $('.offerset-ugclist').lightSlider({
            //    slideMove: 3,
            //    item: 5,
            //    loop:false,
            //    keyPress:true
            //});
        }
    },
    methods: {
        setTimeRange: function (timeRange) {
            this.timeRange = timeRange;
        },
        setMediaType: function (mediaType) {
            if (this.mediaType.length == 1 && this.mediaType[0] == mediaType) {
                return;
            }
            var index = this.mediaType.indexOf(mediaType);
            if (index > -1) {
                this.mediaType.splice(index, 1);
            } else {
                this.mediaType.push(mediaType);
            }
        },
        setKPI: function (totalData) {
            var totalSpend = 0,
                totalImp = 0,
                totalClick = 0;
            for (date in totalData) {
                totalSpend += totalData[date].sum_spend;
                totalImp += totalData[date].sum_impression;
                totalClick += totalData[date].sum_click;
            }
            this.totalSpend = totalSpend;
            this.totalImp = totalImp;
            this.totalClick = totalClick;
        },
        validateStatusTextWidth: function (value) {
            if (value > 0 && value < 3) return 3;
            return value;
        },
        hasMedia: function (mediaTypes) {
            for (var i = 0; i < mediaTypes.length; i++) {
                if (this.mediaType.indexOf(mediaTypes[i]) < 0) {
                    return false;
                }
            }
            return true;
        },
        number_format: function (n, d, x, s, c) {
            var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (d > 0 ? '\\D' : '$') + ')',
                num = parseFloat(n).toFixed(Math.max(0, ~~d));

            return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
        }
    }
});

app.buildGraph = function (dateList, totalData, fbData, twData) {
    Highcharts.setOptions({
        lang: {
            thousandsSep: ','
        }
    });

    var series = [];

    if (this.hasMedia([0])) {
        series.push({
            name: 'Total Spend',
            type: 'column',
            yAxis: 1,
            data: totalData.spend,
            color: '#ffc281',
            index: 0,
            tooltip: {
                valuePrefix: '¥'
            }
        }, {
            name: 'Total CTR',
            type: 'spline',
            data: totalData.ctr,
            color: Highcharts.getOptions().colors[3],
            index: 3,
            tooltip: {
                valueSuffix: '%'
            }
        });
    }

    if (this.hasMedia([1]) && fbData.spend.length > 0) {
        series.push(
            {
                name: 'Facebook Spend',
                type: 'column',
                yAxis: 1,
                data: fbData.spend,
                color: '#5472b1',
                index: 1,
                tooltip: {
                    valuePrefix: '¥'
                }
            }
        );
        series.push(
            {
                name: 'Facebook CTR',
                type: 'spline',
                data: fbData.ctr,
                color: '#3b5998',
                index: 4,
                tooltip: {
                    valueSuffix: '%'
                }
            });
    }
    if (this.hasMedia([2]) && twData.spend.length > 0) {
        series.push(
            {
                name: 'Twitter Spend',
                type: 'column',
                yAxis: 1,
                data: twData.spend,
                color: '#50c3ff',
                index: 2,
                tooltip: {
                    valuePrefix: '¥'
                }
            }
        );
        series.push(
            {
                name: 'Twitter CTR',
                type: 'spline',
                data: twData.ctr,
                color: '#1DA1F2',
                index: 5,
                tooltip: {
                    valueSuffix: '%'
                }
            }
        );
    }

    //daily report
    $('#reportContainer').highcharts({
        chart: {
            zoomType: 'xy'
        },
        title: {
            text: ''
        },
        credits: {
            enabled: false
        },
        tooltip: {
            shared: true
        },
        legend: {
            layout: 'horizontal',
            align: 'center',
            verticalAlign: 'top',
            floating: true,
            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
        },
        xAxis: [{
            categories: dateList,
            crosshair: true
        }],
        yAxis: [{
            labels: {
                format: '{value}%'
            },
            title: {
                text: 'CTR',
                align: 'high',
                rotation: 0
            },
            opposite: true,
            min: 0
        }, {
            gridLineWidth: 0,
            title: {
                text: 'Spend',
                align: 'high',
                rotation: 0
            },
            labels: {
                format: '¥{value}'
            }

        }],
        series: series
    });
};

app.getTopPerformanceUgc = function () {
    var url = getTopPerformanceUgcApiUrl + '?media_type=' + this.mediaType.join(),
        app = this;
    axios.get(url, {
        enableBlockUI: false
    })
        .then(function (response) {
            app.topPerformanceUgc = response.data;
        });
};

app.getUgcStatus = function () {
    var url = getUgcStatusApiUrl,
        app = this;
    axios.get(url, {
        enableBlockUI: false
    })
        .then(function (response) {
            app.ugcSpending = response.data.living;
            app.ugcSpended = response.data.spended;
            app.ugcApproved = response.data.approved;
            app.ugcOffer = response.data.offered;
        });
};

app.getGraphData = function () {
    var url = getGraphDataApiUrl + '?media_type=' + this.mediaType.join();
    if (this.timeRange != null) {
        url = url + '&time_range=' + this.timeRange;
    }
    var app = this;
    axios.get(url, {
        enableBlockUI: false
    })
        .then(function (response) {
            //here we generate data for chart
            var dateList = [],
                totalData = {spend: [], ctr: []},
                fbData = {spend: [], ctr: []},
                twData = {spend: [], ctr: []};

            app.setKPI(response.data.totalData);

            for (date in response.data.totalData) {
                var responseTotalData = response.data.totalData[date],
                    totalCtr = responseTotalData.sum_ctr;

                dateList.push(date);
                totalData.spend.push(responseTotalData.sum_spend);
                totalData.ctr.push(totalCtr == null ? 0 : totalCtr);
                if (typeof response.data.fbData[date] != 'undefined') {
                    var fbCtr = response.data.fbData[date].sum_ctr;
                    fbData.spend.push(response.data.fbData[date].sum_spend);
                    fbData.ctr.push(fbCtr == null ? 0 : fbCtr);
                } else if (app.hasMedia([1])) {
                    fbData.spend.push(0);
                    fbData.ctr.push(0);
                }
                if (typeof response.data.twData[date] != 'undefined') {
                    var twCtr = response.data.twData[date].sum_ctr;
                    twData.spend.push(response.data.twData[date].sum_spend);
                    twData.ctr.push(twCtr == null ? 0 : twCtr);
                } else if (app.hasMedia([2])) {
                    twData.spend.push(0);
                    twData.ctr.push(0);
                }
            }
            if (app.totalSpend > 0) {
                app.buildGraph(dateList, totalData, fbData, twData);
            }
        })
        .catch(function (error) {
            app.errorMsg = 'Error! Could not reach the API. ' + error
        })
};

app.getConversionKpi = function () {
    if (this.conversionType == null) {
        return;
    }
    var url = getConversionKpiApiUrl + '?media_type=' + this.mediaType.join() + '&cv_type=' + this.conversionType,
        app = this;
    if (this.timeRange != null) {
        url = url + '&time_range=' + this.timeRange;
    }
    axios.get(url, {
        enableBlockUI: false
    })
        .then(function (response) {
            app.conversion = response.data;
        });
};

$(function () {
    $(".acordion").hide();
    $(".allHash").click(function () {
        $(this).prev().slideToggle();
        $(this).toggleClass("open");
        if ($(this).hasClass('open')) {
            $(this).html('<i class="fa fa-minus-circle" aria-hidden="true"></i>表示するお知らせを減らす')
        } else {
            $(this).html('<i class="fa fa-plus-circle" aria-hidden="true"></i>全てのお知らせを表示する')
        }
        return false;
    });
});


$(function () {
    $('[data-toggle="popover"]').popover();
});

$(function () {
    var flg = "default";
    $('.info-badge-btn').click(function () {
        if (flg == "default") {
            $(this).text("×");
            flg = "changed";
        } else {
            $(this).text("！");
            flg = "default";
        }
    });
});


$(document).ready(function () {
    app.mediaType = mediaTypes;
    app.getUgcStatus();

    $('.select2_single').select2({
        placeholder: "CVタイプを選択",
        allowClear: true,
        width: '200px'
    }).on("select2:select", function (e) {
        app.conversionType = $(this).val();
    });

    $('.reservation').daterangepicker({
        startDate: dateStart,
        endDate: dateStop,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        maxDate: moment()
    }, function (start, end, label) {
        $('.reservation').val(start.format('YYYY/MM/DD') + ' - ' + end.format('YYYY/MM/DD'));
    });

    $('#reservation').on('apply.daterangepicker', function (ev, picker) {
        app.setTimeRange(encodeURIComponent($(this).val()));
    });

    $('#create_first_hashtag').unbind('click').click(function () {
        var hashtag = $('#first_hashtag').val();
        if (hashtag == "") {
            alert('ハッシュタグを入力してください');
            return false;
        }

        if (FormValidator.hasSpecialCharacter($('#first_hashtag'))) {
            return false;
        }

        Utility.blockUI();

        $('#add_hashtag_form').submit();
    });

    FormValidator.preventSpecialCharacter($('#first_hashtag'));
});