var app = new Vue({
    el: '#app',
    data: {
        currentImage: slideshow,
        kpis: [],
        mediaAccount: {id: null, name: null},
        mediaAccountList: mediaAccountList,
        tweet: ""
    },
    methods: {
        uploadImage: function() {
            this.mediaAccount = {
                name: $('[name="media_account_id"] option:selected').text(),
                id: $('[name="media_account_id"]').val()
            };

            if (this.mediaAccountList[this.mediaAccount.id] == 1) {
                //fb
                if (confirm('Facebookと同期しますか？')) {
                    $('#upload_form').submit();
                }
            } else {
                //tw
                $('#upload_tw_modal').modal('show');
            }
        }
    }
});

app.selectFormatState = function(state) {
    if (!state.id) { return state.text; }
    if ($(state.element).attr('data-type') == 1) {
        return '<span class="fa fa-facebook-square"></span> ' + state.text;
    } else {
        return '<span class="fa fa-twitter-square"></span> ' + state.text;
    }
};

app.getKpi = function() {
    var app = this;
    Utility.blockUI();
    axios.get(getKpiApiUrl + '/' + app.currentImage.id)
        .then(function (response) {
            app.kpis = response.data;
            Utility.unblockUI();
        });
};

app.textAreaAdjust = function(o, minH) {
    if (o.scrollHeight <= minH) {
        return;
    }
    o.style.height = "1px";
    o.style.height = (10 + o.scrollHeight)+"px";
};

$(document).ready(function(){
    app.getKpi();

    $('#upload_form [name="media_account_id"]').select2({
        templateResult: app.selectFormatState
    });
});
