var handleDataTableButtons = function() {
        "use strict";
        0 !== $("#datatable-buttons").length && $("#datatable-buttons").DataTable({
            dom: "Bfrtip",
            pageLength: 100,
            buttons: [{
                extend: "copy",
                className: "btn-sm"
            }, {
                extend: "excel",
                className: "btn-sm"
            }, {
                extend: "print",
                className: "btn-sm"
            }],
            responsive: !0
        })
    },
    TableManageButtons = function() {
        "use strict";
        return {
            init: function() {
                handleDataTableButtons()
            }
        }
    }();
TableManageButtons.init();

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
        window.location.href = url + '?time_range=' + encodeURIComponent($(this).val());
    });

    $("input[type='checkbox']").change(function () {
        if (this.checked) {
            $(this).val(1);
            return;
        }

        $(this).val(0);
    });

    $(".remove-adaccount").on('click', function() {
        $("#remove-adaccount-form").attr('action', $(this).data('action'));
    });
});
