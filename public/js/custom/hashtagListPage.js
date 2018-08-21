var Hashtag = {};
Hashtag.maxHashtagFields = 3;
Hashtag.minHashtagFields = 1;

Hashtag.dynamicInputHashtag = function () {
    var currentInputCount = 1;
    var wrapper           = $(".hashtag-input-group");
    var add_button        = $(".new_input_btn");
    var html              = '<div class="hashtag-input-item"></br>'
                        + '<div class="form-group item" style="margin-left: 26px">'
                        + '<label class="nocrowing-modal-hash" style="margin-right: 8px">#</label>'
                        + '<input type="text" name="hashtags[]" required="required" class="form-control nocrowing-hash-input">'
                        + '<i class="fa fa-minus-circle fa-lg input-remove auto-modal-hash-icon fontawesome-remove-icon"></i></div>'
                        + '</div>';

    $(add_button).click(function(e){
        e.preventDefault();
        if(currentInputCount < Hashtag.maxHashtagFields){
            currentInputCount++;
            $(wrapper).append(html);
        }
        if (currentInputCount == Hashtag.maxHashtagFields) {
            $(add_button).hide();
        }

        FormValidator.preventSpecialCharacter($('.nocrowing-hash-input'));
    });

    $(wrapper).on("click",".input-remove", function(e){
        e.preventDefault();
        $(this).closest(".hashtag-input-item").remove();
        currentInputCount--;
        if (currentInputCount == Hashtag.minHashtagFields) {
            $(add_button).show();
        }
    });
}

$(document).ready(function() {

    $('.hashtags').each(function() {
        var hashtagId = $(this).data('hashtag-id'),
            url = searchConditionStatisticApi + '/' + hashtagId;
        axios.get(url)
            .then(function (response) {
                $('#all_count_'+hashtagId).html(number_format(response.data.allCount));
                $('#offer_count_'+hashtagId).html(number_format(response.data.offeredCount));
                $('#approved_count_'+hashtagId).html(number_format(response.data.approvedCount));
                $('#live_count_'+hashtagId).html(number_format(response.data.livingCount));
            });
    });

    $('.delete-hashtag').click(function(event) {
       event.preventDefault();
       if (confirm('ハッシュタグを削除しますか？')) {
           $(this).closest('form').submit();
       }
    });

    validator.message['empty'] = '入力してください';
    // validate a field on "blur" event, a 'select' on 'change' event & a '.reuired' classed multifield on 'keyup':
    $('form')
        .on('blur', 'input[required], input.optional, select.required, textarea[required]', validator.checkField)
        .on('change', 'select.required', validator.checkField)
        .on('keypress', 'input[required][pattern]', validator.keypress);

    $('#add-hashtag-btn').unbind('click').click(function() {
        if (!validator.checkAll($(this).closest('form'))) {
            return false;
        }
        var hasInvalidInput = false;

        $('.nocrowing-hash-input').each(function (i) {
            if (FormValidator.hasSpecialCharacter($(this))) {
                hasInvalidInput = true;
                return false;
            }
        });
        if (hasInvalidInput) {
            return;
        }

        Utility.blockUI();

        $('#add-hashtag-form').submit();
    });

    FormValidator.preventSpecialCharacter($('.nocrowing-hash-input'));
    Hashtag.dynamicInputHashtag();
});