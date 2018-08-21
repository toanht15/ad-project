var app = new Vue({
    el: '#app',
    data: {
        mediaAccountList: null
    },
    methods: {

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
        });
};

$(document).ready(function() {
    app.getMediaAccountList();
    $(".select2_single").select2({
        placeholder: "広告アカウントを選択",
        allowClear: true
    });
});