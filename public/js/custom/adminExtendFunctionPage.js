var app = new Vue({
    el: '#app',
    data: {
        adAccountId: '',
        videoList: [],
        instaActors: [],
        errorMsg: ''
    },
    methods: {
        getVideo: _.debounce(
            function () {
                if (!this.adAccountId) {
                    this.errorMsg = "広告アカウントIDを入力してください";
                    return
                }
                var vm = this;
                axios.get(getVideoApiUrl + '/' + this.adAccountId)
                    .then(function (response) {
                        vm.videoList = response.data;
                        $(".js-video-id-multiple").select2({
                            placeholder: "広告ビデオを選択",
                            templateResult: format
                        });
                    })
                    .catch(function (error) {
                        vm.errorMsg = 'Error! Could not reach the API. ' + error
                    })
            },
            50
        ),
        getInstagram: _.debounce(
            function () {
                if (!this.adAccountId) {
                    this.errorMsg = "広告アカウントIDを入力してください";
                    return
                }
                Utility.blockUI();
                var vm = this;
                axios.get(getInstagramApiUrl + '/' + this.adAccountId)
                    .then(function (response) {
                        vm.instaActors = response.data;
                        Utility.unblockUI();
                    })
                    .catch(function (error) {
                        vm.errorMsg = 'Error! Could not reach the API. ' + error
                    })
            },
            50
        )
    }
});

function format (option) {
    if (!option.id) { return option.text; }
    var ob = '<img width="50" src="'+$(option.element).attr('data-url')+'" />' + option.text;
    return ob;
}

$(document).ready(function() {
    $(".js-video-id-multiple").select2({
        placeholder: "広告アカウントを選択してください"
    });
});