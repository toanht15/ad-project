var FormValidator = {};

// check if has url
FormValidator.isHasUrl = function (element) {
    var validateUrlRegex = /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/gi;
    if (element.val().match(validateUrlRegex)) {
        validator.mark(element, 'URLは入れることがきません');
        return true;
    }

    return false;
};

// check if all characters is uppercase
FormValidator.isAllUppercase = function (element) {
    if (FormValidator.hasJapanese(element))
        return false;
    
    if (element.val().length > 0 && element.val() === element.val().toUpperCase()) {
       validator.mark(element, '大文字のみの英字は不可です');
       return true;
    }

    return false;
};

// check limit hashtag
FormValidator.validateLimitHashTag = function (element, limitHashtag) {
    var validateHastagRegex = /(^|)(#[a-z\d][\w-]*)/gi;
    var num_hashtags = element.val().match(validateHastagRegex) ? element.val().match(validateHastagRegex).length : 0;
    if (num_hashtags > limitHashtag) {
        validator.mark(element, 'ハッシュタグは3つまで');
        return false;
    }

    return true;
};

// count number of characters in a elemnent on keyup
FormValidator.countChars = function (parentEle, ChildEle) {
    parentEle.keyup(function() {
        var length = $(this).val().length;
        ChildEle.text(length);
    });
};

// check has japanese
FormValidator.hasJapanese = function (element) {
    var japaneseRegex = /[\u3000-\u303f\u3040-\u309f\u30a0-\u30ff\uff00-\uff9f\u4e00-\u9faf\u3400-\u4dbf]/gi;
    if (element.val().match(japaneseRegex))
        return true;

    return false
};

// check has special character
FormValidator.hasSpecialCharacter = function (element) {
    var regex = /^[0-9A-Za-zぁ-んァ-ヶ０-９_ー一-龯Ａ-ｚ]+$/gi;
    if (!element.val().match(regex)) {
        validator.mark(element, '記号や空白は使用できない文字です');
        return true;
    }

    return false;
}

FormValidator.preventSpecialCharacter = function (element) {
    element.unbind('keyup').keyup(function () {
        if (FormValidator.hasSpecialCharacter($(this))) {
            return false;
        }
    });
    // prevent enter
    element.unbind('keypress').keypress(function (event) {
        if (event.keyCode == 10 || event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    })
}

FormValidator.isEmailAddress = function (element) {
    var emailIllegalChars =  /[\(\)\<\>\,\;\:\\\/\"\[\]]/;
    var emailFilter = /^.+@.+\..{2,6}$/;

    if(!element.val().match(emailIllegalChars)){
        validator.mark(element, 'error message:has illegal chars');
        return false;
    }

    if (!element.val().match(emailFilter)){
         validator.mark(element,'error message: bad email')
        return false;
    }

    return true;
}

FormValidator.isIPAddress = function (ipAddressArray) {
    var regex = /^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/;

    var result = true;
    $.each(ipAddressArray, function (i, address) {
       if (!address.match(regex)){
           result = false;
       }
    });
    return result;
};

FormValidator.hasUrlExtraCharactor = function(url){
    // var regex = /\{|\}|\||\\|\^|\[|]|`|\s/;
    var regex = /'|"|\s/;

    if (url.match(regex)){
        return true;
    }
    return false;
};