var HashtagSliderList = {};

HashtagSliderList.loadList = function() {
    $('[data-asyn-condition-id]').each(function() {
        var conditionId = $(this).attr('data-asyn-condition-id');
        var apiUrl = apiSearchConditionPreview + '/' + conditionId + '?limit=10';

        $.getJSON(apiUrl, function (data) {
            HashtagSliderList.mount(conditionId, data);

        }).error(function(jpXHR, status, errors){
            console.log("ERROR" , status);
        });
    });
};

HashtagSliderList.mount = function(conditionId, data) {
    var tag = 'search_condition_list_' + conditionId;

    riot.tag(tag,
        $('search_condition_preview_temp').html(),
        function (opts) {
            this.list = data;
        }
    );

    riot.mount(tag);

    $(tag).find('.content-slider').lightSlider({
        slideMove: 3,
        loop:true,
        keyPress:true
    });

    $(tag).find('[data-status-label]').each(function() {
        $(this).html(Utility.getStatusLabel($(this).attr('data-status-label')));
    });

    $('#loading-img-'+conditionId).remove();
};

$(document).ready(function() {
    HashtagSliderList.loadList();
});