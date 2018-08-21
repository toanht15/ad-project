Vue.component('publish_part_modal', {
    props: ['part'],
    template: '#publish_part_modal_template',
    methods: {
        publish: function () {
            var self = this;
            axios.post(this.part.api_publish_part)
                .then(function (response) {
                    $(self.$el).modal('hide');
                    self.$emit('publish');
                    toastr.success('公開されました');
                    self.part.status = 1;
                })
                .catch(function (reason) {
                    console.log(reason);
                    var errors = reason.data.errors;
                    errors.forEach(function (error) {
                        toastr.error(error);
                    })
                });

        }
    }
})
