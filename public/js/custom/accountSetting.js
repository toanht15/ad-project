var AccountSetting ={

};
AccountSetting.maxCVPages = 10;


var app = new Vue({
    el: '#app',
    data: {
        mediaAccountList: null,
        isCVEdit : false,
        isAddressEdit : false,
        maxNo: null,
        plusNo:1,
        formAddState:true,
        address: null,
        cvPages : null
    },
    mounted: function () {
        this.initValue();
    },
    methods: {
        editCVPage: function () {
            this.isCVEdit = true;
            this.cvPages++;
        },
        saveCVPage: function () {
            var inputForms = $("[name*='url_string_']");
            var result = true;
            var formValid = true;
            $('.cv-pages-inputs').each(function (i) {
                var url = $(this).find("[name*='url_string_']").first().val();
                var label = $(this).find("[name*='label_']").first().val();
                if (url == "" && label == "") {
                    return;
                } else if (url == "") {
                    alert( (i + 1) + "目行の目標ページURLを入力してください");
                    formValid = false;
                } else if (label == "") {
                    formValid = false;
                    alert((i + 1) + "目行の目標タイトルを入力してください");
                }

            })


            $.each(inputForms, function (i, urlForm) {
                var url = urlForm.value;
                if (url.trim().length > 0) {
                    if (FormValidator.hasUrlExtraCharactor(url)) {
                        result = false;
                        alert('目標ページURLに使用できない文字が含まれています');
                        return result;
                    }
                }
            });

            if(result && formValid){
                $('#cv-page-form').submit();
                app.isCVEdit = false;
            }

        },
        editExcludeAddress:function () {
            this.isAddressEdit = true;
            this.address++;
        },
        saveExcludeAddress:function () {
            var ipAddressesArray = $('#exclude-addresses').val().trim().split(/\r\n|\r|\n/);

            if ("" === $('#exclude-addresses').val().trim()) {
                // is empty post
                $('#add-exclude-address-form').submit();
                app.isAddressEdit = false;
            } else {
                if (FormValidator.isIPAddress(ipAddressesArray)) {
                    $('#add-exclude-address-form').submit();
                    app.isAddressEdit = false;
                } else {
                    alert('IPアドレスが間違っています');
                    return false;
                }
            }

        },
        addCVPageForm: function (plusCnt) {
            console.debug('plus = ' + plusCnt + ' plus NO = ' + this.plusNo  );
            if ($('.cv-pages-inputs').length < AccountSetting.maxCVPages) {
                app.plusNo++;
            }
        },
        initValue: function () {
            if (typeof maxNo != 'undefined') {
                this.maxNo = maxNo;
            }
            if (typeof cvPagesCount != 'undefined') {
                this.cvPagesCount = cvPagesCount;
            }
            if (typeof address != 'undefined') {
                this.address = address;
            }
        }

    }
});

app.getMediaAccountList = function() {
    var url = getMediaAccountListApiUrl,
        app = this;
    Utility.blockUI();
    axios.get(url)
        .then(function (response) {
            app.mediaAccountList = response.data;
            Utility.unblockUI();
        }).catch(function (error) {
        app.errorMsg = 'Error! Could not API' + error;
    });
};

$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip({
        container: 'body'
    });
    $('#save-cv-pages-btn').click(function () {
        if (!validator.checkAll($(this).closest('form'))) {
            return false;
        }
    })
    app.getMediaAccountList();

    $(".select2_single").select2({
        placeholder: "広告アカウントを選択",
        allowClear: true
    });
    var email = $('#email-form').val();
    if (email ===""){
        $('#email-toggle').prop('checked', false);
        $('#mail_noti_form').addClass('hidden');
    }else {
        $('#email-toggle').prop('checked', true);
        $('#mail_noti_form').removeClass('hidden');
    }

    $('#email-toggle').change(function () {

        var state = $(this).prop('checked');
        if (!state){
            $('#mail_noti_form').addClass('hidden');
            $('#email-form').val('');
        } else {
            $('#mail_noti_form').removeClass('hidden');
        }
    });

    $('#email-submit-btn').unbind('click').click(function () {
        var inputEmail = $('#email-form').val();
        var mailState = $('#email-toggle').prop('checked');

        if(mailState === false && inputEmail === ""){
            //  todo set email empty = sending state = OFF
            $('#email-form').submit();
        }
        if (mailState === true){
            if (FormValidator.isEmailAddress($(this))){
                $('#email-form').submit();
            }
        }
    });

    $('#crawl-account-post-toggle').change(function () {

        var state = $(this).prop('checked');
        var url = postAdvertiserCrawlApiUrl,
            app = this;
        axios.post(url, {crawlPostSetting:state}).
        then(function (response) {
            Utility.unblockUI();
        }).catch(function (errors) {
            app.errorMsg = 'Error! Could not API' + errors;
        });
    });

});