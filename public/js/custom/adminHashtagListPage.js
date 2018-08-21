var handleDataTableButtons = function() {
        "use strict";
        0 !== $("#datatable-buttons").length && $("#datatable-buttons").DataTable({
            dom: "Bfrtip",
            pageLength: 50,
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

$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#datatable-buttons').on('click', 'td > .hashtag-crawl-btn', (function () {
        if(confirm("このハッシュタグをクロールしますか？")){
            var id = $(this).attr('data-hashtag-id');
            var url = executeCommandUrl;
            $.ajax({
                type: "POST",
                url: url,
                data: {id: id},
                success: function (response) {
                    setTimeout(function(){
                        window.location.reload();
                    }, 2000);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    window.location.reload();
                }
            });
        }
    }));

    $('#datatable-buttons').on('click', 'td > .inactive-hashtag-btn', (function () {
        if(confirm("このハッシュタグをOFFにしますか？")){
            var id = $(this).attr('data-hashtag-id');
            var url = inactiveHashtagUrl;
            $.ajax({
                type: "POST",
                url: url,
                data: {id: id},
                success: function (response) {
                    setTimeout(function(){
                        window.location.reload();
                    }, 2000);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    window.location.reload();
                }
            });
        }
    }));
});

