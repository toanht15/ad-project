var OfferOperator = {};
OfferOperator.offers = [];
OfferOperator.commentLimitChars = 120;
OfferOperator.noTemplateCmtLimitChars = 300;
OfferOperator.limitHashtag = 3;

OfferOperator.createCommentPreview = function (src) {
    $('.comment_preview').each(function() {
        $(this).html($('[name="comment"]').val());
    });
    $('.answer_hashtag_preview').each(function() {
       $(this).html($('[name="answer_tag"]').val());
    });
};

OfferOperator.validateLimitCommentChars = function () {
    var remainChars = OfferOperator.getCommentRemainChars();
    var currentLength = $('#comment-area').val().length;
    if (currentLength > remainChars) {
        validator.mark($('#comment-area'), validator.message['max']);
        return false;
    }

    return true;
};

OfferOperator.validateTextarea = function () {
    if (!FormValidator.validateLimitHashTag($('#comment-area'), OfferOperator.limitHashtag)) return false;
    if (FormValidator.isHasUrl($('#comment-area'))) return false;
    if (FormValidator.isAllUppercase($('#comment-area'))) return false;
    if (!OfferOperator.validateLimitCommentChars()) return false;

    return true;
};

OfferOperator.setCommentRemainChars = function () {
    var remainChars = OfferOperator.getCommentRemainChars();
    $('#preview-remain-chars').text(remainChars);
    $('#comment-area').attr('maxlength', remainChars);
};

OfferOperator.getCommentRemainChars = function () {
    // var limitChars = OfferOperator.noTemplateCmtLimitChars - TOS.length;
    // return limitChars;

    var limitChars = OfferOperator.commentLimitChars;
    if ($('#comment_template_checkbox').is(':checked')) {
        limitChars = OfferOperator.noTemplateCmtLimitChars - $('#fixedLink').text().length;
    }
    return limitChars - $('[name="answer_tag"]').val().length;

};

OfferOperator.validateCommentHasAnswerHashtag = function () {
    var comment = $('#comment-area').val();
    var answerHashTag = '#' + $('#answer-input').val();
    if ($('#comment_template_checkbox').is(':checked')) {
        if (comment.indexOf(answerHashTag) === -1) {
            validator.mark($('#comment-area'), '回答ハッシュタグが含まれているコメントを入力してください');
            return false;
        }
    }

    return true;
}

OfferOperator.commentTemplateCheckboxEvent = function () {
    $('#comment_template_checkbox').change(function() {
        var isChecked= $(this).is(':checked');
        if(isChecked){
            $('.none-tempalte-preview-area').removeClass('hidden');
            $('.preview-slider-js').addClass('hidden');
        } else {
            $('.none-tempalte-preview-area').addClass('hidden');
            $('.preview-slider-js').removeClass('hidden');
        }
        OfferOperator.setCommentRemainChars();
    });
}


OfferOperator.validateConfirmModal = function () {
    $('#js_images_comfirm_tmp').on('show.bs.modal', function (e) {
        $('#js_images_comfirm_tmp').css('visibility','visible');

    }).on('hide.bs.modal', function (e) {
        $('#js_images_comfirm_tmp').css('visibility','hidden');
    });

    validator.message['empty'] = 'コメントを入力してください';
    validator.message['max'] = 'コメントが長すぎます';
    $('[name="create_type"]').click(function() {
        if ($(this).val() == 'new') {
            $('#select_offer_group').hide(300);
        } else {
            $('#select_offer_group').show(300);
            $('[name="title"]').val($('[name="offer_set_group_id"] option:selected').text());
        }
    });
    $('form')
        .on('blur', 'input[required], input.optional, select.required, textarea[required]', validator.checkField)
        .on('change', 'select.required', validator.checkField)
        .on('keypress', 'input[required][pattern]', validator.keypress);

    $('[name="comment"]').on('input', function() {
        OfferOperator.createCommentPreview();
    });

    $('[name="answer_tag"]').on('input', function() {
        OfferOperator.createCommentPreview();
        OfferOperator.setCommentRemainChars();
        OfferOperator.validateLimitCommentChars();
    });

    $('#submit-offer').click(function(e) {
        e.preventDefault();

        if (!validator.checkAll($('#create_offer_form'))) {
            return false;
        }

        if (FormValidator.hasSpecialCharacter($('#answer-input'))) {
            return false;
        }

        if (imageListApp.selectedImageIds.length == 0) {
            alert('UGCを選択してください');
            return;
        }

        if (!OfferOperator.validateTextarea()) return false;
        if (!OfferOperator.validateCommentHasAnswerHashtag()) return false;

        if (confirm("選択したUGCの投稿者にリクエストしますか？")) {
            // google analytic tracking
            ga('send', 'event', 'offer', 'send_offer', advertiserId, imageListApp.selectedImageIds.length);
            $('#create_offer_form').submit();
        }
    });

    FormValidator.preventSpecialCharacter($('#answer-input'));

    if ($('[name="comment"]').length > 0) {
        $('#comment-area').blur(function () {
            OfferOperator.validateTextarea();
        });

        OfferOperator.setCommentRemainChars();

        $('#answer-remain-chars').text($('#answer-input').val().length);
        FormValidator.countChars($('#answer-input'), $('#answer-remain-chars'));
        $('#comment-remain-chars').text($('[name="comment"]').val().length);
        FormValidator.countChars($('textarea'), $('#comment-remain-chars'));
    }

    OfferOperator.commentTemplateCheckboxEvent();
    OfferOperator.createCommentPreview();

    $(".preview-slider").lightSlider({
        slideMove: 1,
        loop:true,
        keyPress:true,
        slideMargin: 10
    });

    $(".lSPager").css('margin-top', '-30px');
};