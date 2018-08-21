var videojsCustom = {
    player: null,
    clearVideo: function () {
        if (this.player) {
            this.player.dispose();
        }
        this.player = null;
    },
    initVideo: function (videoTempSelector, postId) {
        this.clearVideo();
        var clone = $(videoTempSelector).clone();
        clone.removeClass('hidden').attr('id', 'video_modal');
        clone.insertAfter(videoTempSelector);
        this.player = videojs("video_modal", {
            muted: true,
            controlBar: {
                volumeMenuButton: false
            }
        });
        this.player.on('error', function (e) {
            toastr.error('投稿が削除された、アカウントが非公開に変更、またはその他Instagramの仕様変更で利用できません');
        });
    }
};