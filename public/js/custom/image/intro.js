var introJS;

initIntro = function () {
    var target = $('.imglist-panel:visible:first');
    target.attr('data-intro', '登録されたハッシュタグで収集した画像です');
    target.attr('data-step', '1');
    target.attr('data-position', 'right');

    var addButton = target.find('.custom-checkbox').find('label');
    if (addButton.length > 0) {
        addButton.attr('data-intro', '利用したいUGCを選択します');
        addButton.attr('data-step', '2');
        addButton.attr('data-position', 'right');
    }

    introJS = introJs().setOptions({
        'skipLabel': 'スキップ',
        'nextLabel': '次へ',
        'prevLabel': '前へ',
        'doneLabel': '完了'

    }).oncomplete(function () {
        axios.post(completeTutorialURL);
    }).onexit(function () {
        if (!confirm('後でみますか？')) {
            axios.post(completeTutorialURL);
            // if (UPE.canStartTutorial) {
            //     UPE.startTutorialModal(currentSearchConditionId);
            // }
        }
    }).onbeforechange(function () {
        if (this._currentStep == 3) {
            $('#js_images_comfirm_tmp').modal('show');
        }

    }).onafterchange(function () {
        if (this._currentStep == 2) {
            $('#js_images_comfirm_tmp').modal('hide');
        }
        if (this._currentStep >= 3) {
            $('.introjs-overlay').addClass('view-none');
            $('.introjs-helperLayer').addClass('bgc-none');
        } else {
            $('.introjs-overlay').removeClass('view-none');
            $('.introjs-helperLayer').removeClass('bgc-none');
        }
        if (this._currentStep == 5) {
            if ($(window).height() < 816) {
                $('body').addClass('move-introjs');
            }
        } else {
            $('body').removeClass('move-introjs');
        }
    }).start();
}