Vue.component('create_part_template', {
    template: '#create_part_template',
    props: ['redirecturl'],
    data: function() {
        return {
            title: '',
            template: 2
        }
    },
    methods: {
        createPart: function () {
            var self = this;
            if (self.title.length === 0) {
                toastr.error('UGCセットの名前を入力してください');
                return;
            }
            axios.post(apiCreatePart, {
                title: self.title,
                template: self.template
            })
                .then(function (response) {
                    if (self.redirecturl !== null && typeof self.redirecturl !== 'undefined') {
                        location.href = self.redirecturl;
                    } else {
                        toastr.success('UGCセットを作成しました');
                    }
                })
                .catch(function (reason) {
                    toastr.error('UGCセットを作成できませんでした');
                });
        }
    }
});
