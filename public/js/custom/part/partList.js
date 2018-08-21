var publishPartApp = new Vue({
    el: '#part_modals',
    data: {
        parts: {},
        currentPartId: 0,
        current_active_part: null,
        part: parts_data.length ? new Part(parts_data[0]) : new Part({}),
        search_conditions: searchConditionList,
    },
    created: function () {
        var self = this;
        parts_data.forEach(function (part_data) {
            var part = new Part(part_data);
            self.parts[part.id] = (part);
            part.fetch();
        })
        if (parts_data.length > 0)
            this.part.fetch();
    },
    computed: {
        existed_items: function () {
            var posts = [];
            this.part.ugcs.forEach(function (ugc) {
                if (ugc.post_id)
                    posts.push(ugc.post_id)
            })
            return posts;
        }
    },
    methods: {
        update_part: function (id) {
            this.part = this.parts[id];
        },

        update_image: function () {
            this.part.reset();
            this.part.fetch(true);
        },

        reload: function () {
            location.reload(true);
        }
    }
});


var partList = {
    openDetailModal: function (partId) {
        publishPartApp.currentPartId = partId;
        publishPartApp.update_part(partId);
        $('#part_detail_setting_modal').modal('show');
    }
};

$(document).ready(function () {
    $('#publish_part_modal').on('shown.bs.modal', function (e) {
        var partId = $(e.relatedTarget).data('part-id');
        publishPartApp.update_part(partId);
    });

    $('#ugc_select_modal').on('shown.bs.modal', function (e) {
        var partId = $(e.relatedTarget).data('part-id');
        publishPartApp.update_part(partId);
    });


    $("div.delete").click(function (e) {
        var id = $(e.target).attr("id");
        if (confirm("削除しますか"))
            axios.post("/advertiser/part/" + id + "/delete")
                .then(function (response) {
                    $("tr#part-" + id).fadeOut(1000, function () {
                        $(this).remove();
                    });
                })
                .catch(function (reason) {

                });
    });
});


