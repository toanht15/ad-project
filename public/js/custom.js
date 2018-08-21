var Utility = {};
Utility.videoType = 2;

Utility.getStatusLabel = function (status) {
    if (status !== '' && (status === 0 || status === '0') || status == 1) {
        return '<span class="label label-applying p5 status">申請中</span>';
    } else if (status == 2) {
        return '<span class="label label-failed p5 status">失敗</span>';
    } else if (status == 3) {
        return '<span class="label label-approved p5 status">承認</span>';
    } else if (status == 4) {
        return '<span class="label label-synchronis p5 status">出稿済</span>';
    } else if (status == 5) {
        return '<span class="label label-uploaded p5 status">取り消し</span>';
    }

    return '';
};

Utility.blockUI = function () {
    $.blockUI({
        message: '<img class="loader" src="/images/loading.gif" /> 少々お待ち下さい...',
        baseZ: 2000
    });
};

Utility.unblockUI = function () {
    $.unblockUI();
};

Utility.objectifyForm = function (formId) {
    var formArray = $('#'+formId).serializeArray(),
        returnArray = {};
    for (var i = 0; i < formArray.length; i++){
        var inputName = formArray[i]['name'],
            value = formArray[i]['value'];
        if (inputName.indexOf('[]') !== -1) {
            inputName = inputName.split('[]')[0];
            if (inputName in returnArray) {
                returnArray[inputName].push(value);
            } else {
                returnArray[inputName] = [value];
            }
        } else {
            returnArray[inputName] = value;
        }
    }

    return returnArray;
};

$(document).ready(function(){
    if (typeof toastr != 'undefined') {
        toastr.options.progressBar = true;
    }
    if (typeof axios != 'undefined') {
        axios.defaults.enableBlockUI = true;
        axios.defaults.enableValidationError = true;
        axios.defaults.headers.common = {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'accept': 'application/json'
        };

        axios.interceptors.request.use(function (config) {
            if (config.enableBlockUI === true) {
                Utility.blockUI();
            }

            return config;
        });
        axios.interceptors.response.use(function (response) {
            if (response.config.enableValidationError) {
                $('.form-error').remove();
            }
            if (response.config.enableBlockUI) {
                Utility.unblockUI();
            }
            return response;

        }, function (errors) {
            if (errors.config.enableBlockUI) {
                Utility.unblockUI();
            }
            if (errors.config.enableValidationError) {
                $('.form-error').remove();
                for (key in errors.data.errors) {
                    if (key == 'toastrErrMsg') {
                        toastr.error(errors.data.errors[key]);
                        return Promise.reject(errors);
                    }
                    if ($('#' + key + 'Error').length == 0) {
                        $('[name="' + key + '"]').not('.no-err-msg').after('<span class="red form-error" id="' + key + 'Error">' + errors.data.errors[key] + '</span>');
                    }
                }
            }

            return Promise.reject(errors);
        });
    }

    $('img.check-miss').error(function() {
        //置換処理
        $(this).attr({
            src: '/images/no_image.jpg',
            alt: 'none image'
        });
    });

    $("body").on("contextmenu", "img", function(e) {
        return false;
    });

    $('img').on('dragstart', function(event) { event.preventDefault(); });
});

/**
 * Number.prototype.format(n, x, s, c)
 *
 * @param float   n: the number
 * @param integer d: length of decimal
 * @param integer x: length of whole part
 * @param mixed   s: sections delimiter
 * @param mixed   c: decimal delimiter
 */
number_format = function(n, d, x, s, c) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (d > 0 ? '\\D' : '$') + ')',
        num = parseFloat(n).toFixed(Math.max(0, ~~d));

    return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
};



String.prototype.cut = function(length) {
    if(this.length > length) {
        return this.substring(0,length)+'...';
    }
    return this;
};

String.prototype.isLater = function(string) {
    var d1 = Date.parse(this);
    var d2 = Date.parse(string);
    if (d1 >= d2) {
        return true;
    }
    return false;
};

function textAreaAdjust(o, minH) {
    if (o.scrollHeight <= minH) {
        return;
    }
    o.style.height = "1px";
    o.style.height = (o.scrollHeight)+"px";
}
// Sidebar
$(function () {
    var URL = window.location.href.split('?')[0],
        $SIDEBAR_MENU = $('#sidebar-menu');

    $SIDEBAR_MENU.find('li ul').slideUp();
    $SIDEBAR_MENU.find('li').removeClass('active');

    $SIDEBAR_MENU.find('li').on('click', function(ev) {
        var link = $('a', this).attr('href');

        // prevent event bubbling on parent menu
        if (link) {
            ev.stopPropagation();
        } 
        // execute slidedown if parent menu
        else {
            if ($(this).is('.active')) {
                $(this).removeClass('active');
                $('ul', this).slideUp();
            } else {
                $SIDEBAR_MENU.find('li').removeClass('active');
                $SIDEBAR_MENU.find('li ul').slideUp();
                
                $(this).addClass('active');
                $('ul', this).slideDown();
            }
        }
    });

    // check active menu
    $SIDEBAR_MENU.find('a').filter(function () {
        return URL.indexOf(this.href) >= 0;
    }).parent('li').addClass('current-page').parent('ul').slideDown().parent().addClass('active');
});

// Nav
$('body.nav-md .container.body .col-md-3.left_col').hover(function(){
        $('.site_title img').attr('src','/images/letro_Std_Posi.png');
     },function(){
        $('.site_title img').attr('src','/images/letro_Square_Posi.png');
     }
);


function createCookie(name, value, days = 2) {
    var expires;

    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    } else {
        expires = "";
    }
    document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
}

function readCookie(name) {
    var nameEQ = encodeURIComponent(name) + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ')
            c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0)
            return decodeURIComponent(c.substring(nameEQ.length, c.length));
    }
    return null;
}
