$(document).ready(function () {
    var clipboard = new ClipboardJS('.btn');
    clipboard.on('success', function(e) {
        alert("Copy success");
    });

    clipboard.on('error', function(e) {
        window.prompt("Can not auto copy. Please copy below text", e.text);
    });


    var daterangepicker = $('#date_range').daterangepicker({
        startDate: moment(),
        endDate: moment(),
        ranges: {
            'Today': [moment(), moment().add(1, 'days')],
            'Yesterday': [moment().subtract(1, 'days'), moment().add(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment().add(1, 'days')],
            'Last 30 Days': [moment().subtract(29, 'days'), moment().add(1, 'days')],
            'This Month': [moment().startOf('month'), moment().endOf('month').add(1, 'days')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month').add(1, 'days')]
        },
        maxDate: moment().add(1, 'days')
    }, function(start, end, label) {
        // var from = start.format('YYYY-MM-DD');
        // var to = end.format('YYYY-MM-DD');
        $('#created_at_from').val(start.format('YYYY-MM-DD'));
        $('#created_at_to').val(end.format('YYYY-MM-DD'));
        // $('#date_range').val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
    });

    daterangepicker.on('apply.daterangepicker', function(ev, picker) {
        // console.log(Date.parse($('#created_at_from').val()));
        // console.log(Date.parse($('#created_at_to').val()));
        table.draw();
    });

    var table = $('#advertiser_offer_table').DataTable({
        responsive: true,
        order: [[ 0, "desc" ]],
        lengthMenu: [[30, 50, 100, -1], [30, 50, 100, "All"]],
        columnDefs: [
            {
                "targets": [8],
                "visible": false
            }
        ]
    });

    $('#created_at_from, #created_at_to').keyup( function() {
        table.draw();
    } );

    $.fn.dataTable.ext.search.push(
        function( settings, data, dataIndex ) {
            var from = Date.parse($('#created_at_from').val());
            var to = Date.parse($('#created_at_to').val());

            var created_at = Date.parse( data[1] ) || 0;

            if ( ( isNaN( from ) && isNaN( to ) ) ||
                ( isNaN( from ) && created_at <= to ) ||
                ( from <= created_at   && isNaN( to ) ) ||
                ( from <= created_at   && created_at <= to ) )
            {
                return true;
            }
            return false;
        }
    );

    $(document).on("change", "#offer_status_filter_select",  function() {
        table.columns( 8 ).search( this.value ).draw();
    } );

    table.on("click", ".update_offer_data_button", function(){

        axios.post(url, {
            offerId: $(this).data("offer_id"),
            offerStatus: $(this).parent().parent().find('.advertiser_offer_status_select option:selected').val()
        }).then(function (response) {
            window.location.reload();
        }).catch(function () {
            alert("can not change offer status, please try again");
            window.location.reload();
        });
    });
    table.columns( 8 ).search( $("#offer_status_filter_select").val() ).draw();

});
