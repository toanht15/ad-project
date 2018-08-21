$(function () {
    if ($('#reportChart').length > 0) {
        Highcharts.setOptions({
            lang: {
                thousandsSep: ','
            }
        });
        //daily report
        $('#reportChart').highcharts({
            chart: {
                zoomType: 'xy'
            },
            title: {
                text: ''
            },
            credits: {
                enabled: false
            },
            xAxis: [{
                categories: dateData,
                crosshair: true
            }],
            yAxis: [{ // Primary yAxis
                labels: {
                    format: '{value}%',
                    style: {
                        color: Highcharts.getOptions().colors[2]
                    }
                },
                title: {
                    text: 'CTR',
                    style: {
                        color: Highcharts.getOptions().colors[2]
                    }
                },
                opposite: true

            }, { // Secondary yAxis
                gridLineWidth: 0,
                title: {
                    text: 'Spend',
                    style: {
                        color: Highcharts.getOptions().colors[0]
                    }
                },
                labels: {
                    format: '¥{value}',
                    style: {
                        color: Highcharts.getOptions().colors[0]
                    }
                }

            }],
            tooltip: {
                shared: true
            },
            legend: {
                layout: 'vertical',
                align: 'left',
                x: 80,
                verticalAlign: 'top',
                y: 55,
                floating: true,
                backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
            },
            series: [{
                name: 'Spend',
                type: 'column',
                yAxis: 1,
                data: spendData,
                tooltip: {
                    valuePrefix: '¥'
                }

            }, {
                name: 'CTR',
                type: 'spline',
                data: ctrData,
                color: Highcharts.getOptions().colors[2],
                tooltip: {
                    valueSuffix: '%'
                }
            }]
        });
    }
});

$(document).ready(function() {
    $('#date_range').daterangepicker({
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
    }, function(start, end, label) {
        $('#date_range').val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
    });

    $('#date_range').on('apply.daterangepicker', function(ev, picker) {
        window.location.href = dashboardURL + '?time_range=' + encodeURIComponent($(this).val());
    });

    $('#csv_download_btn').click(function () {
        $('#csv_time_range').val($('#date_range').val());
        $('#csv_download').submit();
    });
});


