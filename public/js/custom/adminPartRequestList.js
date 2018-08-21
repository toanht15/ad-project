$(document).ready(function () {
    var currentUrl = window.location.href;
    var url = new URL(currentUrl);
    $("#fromDate").datetimepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        minView: 2
    });

    $("#toDate").datetimepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        minView: 2
    });

    $('#Advertisers').select2();

    if(url.searchParams.get('site_name') != '') {
        $('#siteName').val(url.searchParams.get('site_name'));
    }

    if(url.searchParams.get('site_domain') != '') {
        $('#siteDomain').val(url.searchParams.get('site_domain'));
    }

    if(url.searchParams.get('part_title') != '') {
        $('#partTitle').val(url.searchParams.get('part_title'));
    }

    if(url.searchParams.get('request_url') != '') {
        $('#requestURL').val(url.searchParams.get('request_url'));
    }

    if((url.searchParams.get('order_type') != null) && url.searchParams.get('order_type') != '') {
        $('#viewOrder').val(url.searchParams.get('order_type'));
    }

    $('#filterButton').click(function () {
        if($('#siteName').val() != '') {
            url.searchParams.set('site_name', $('#siteName').val());
        } else {
            url.searchParams.delete('site_name');
        }
        if($('#siteDomain').val() != '') {
            url.searchParams.set('site_domain', $('#siteDomain').val());
        }else {
            url.searchParams.delete('site_domain');
        }
        if($('#partTitle').val() != '') {
            url.searchParams.set('part_title', $('#partTitle').val());
        }else {
            url.searchParams.delete('part_title');
        }
        if($('#requestURL').val() != '') {
            url.searchParams.set('request_url', $('#requestURL').val());
        }else {
            url.searchParams.delete('request_url');
        }
        if($('#Advertisers option:selected').val() != '') {
            url.searchParams.set('advertiser_id', $('#Advertisers option:selected').val());
        } else {
            url.searchParams.delete('advertiser_id');
        }
        if($('#fromDate').val() != '') {
            url.searchParams.set('from_date', $('#fromDate').val());
        }else {
            url.searchParams.delete('from_date');
        }
        if($('#toDate').val() != '') {
            url.searchParams.set('to_date', $('#toDate').val());
        }else {
            url.searchParams.delete('to_date');
        }
        if((($('#viewOrder option:selected').val() != 'undefined') && $('#viewOrder option:selected').val() != '')) {
            url.searchParams.set('order_type', $('#viewOrder option:selected').val());
        }else {
            url.searchParams.delete('to_date');
        }
        url.searchParams.set('order_by', 'views');
        window.location.href = url;
    });

    $('#previous').click(function () {
        var previousPage = $(this).data("current_page") -1;
        var itemPerPage =  $(this).data("item_per_page");
        url.searchParams.set('page', previousPage);
        url.searchParams.set('per_page', itemPerPage);
        window.location.href = url;
    });
    $('#next').click(function () {
        var nextPage = $(this).data("current_page") +1;
        var itemPerPage =  $(this).data("item_per_page");
        url.searchParams.set('page', nextPage);
        url.searchParams.set('per_page', itemPerPage);
        window.location.href = url;
    });
    $('.pageNumber').click(function () {
       var pageNumber =  $(this).data("page_number");
       var itemPerPage =  $(this).data("item_per_page");
       url.searchParams.set('page', pageNumber);
       url.searchParams.set('per_page', itemPerPage);
       window.location.href = url;
    });
    $('.pagination').each(function(){
        var allLi = $(this).find('li');
        var activeId = allLi.filter('.active').index();
        allLi.eq(0)
            .add(allLi.eq(1))
            .add(allLi.eq(-1))
            .add(allLi.eq(-2))
            .add(allLi.eq(activeId))
            .add(allLi.eq(activeId-1))
            .add(allLi.eq(activeId+1))
            .addClass('allow');
        var replacedWithDots = false;
        allLi.each(function() {
            if( $(this).hasClass('allow') ) {
                replacedWithDots = false;
            } else if(!replacedWithDots) {
                replacedWithDots = true;
                $(this).html('<a>...</a>');
            } else {
                $(this).remove();
            }
        })
    });
});

