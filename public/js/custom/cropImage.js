var cropImage = {};
cropImage.imageCroper = $('.img-container > img');
cropImage.defaultAspectRatio = 1;

cropImage.initCropImage = function () {

    var options = {
        aspectRatio: cropImage.defaultAspectRatio,
        crop: function (data) {
        },
        built: function () {
            //cropImage.imageCroper.cropper("setCropBoxData", { width: "50" });
        }
    };

    cropImage.imageCroper.on({
        'build.cropper': function (e) {
            console.log(e.type);
        },
        'built.cropper': function (e) {
            console.log(e.type);
        }
    }).cropper(options);

    // Methods
    $('[data-method]').unbind("click").on('click', function () {

        var data = $(this).data(),
            result;

        if (data.method == 'setAspectRatio') {
            $('.btn-fb-active').removeClass('btn-fb-active');
            $('.btn-ig-active').removeClass('btn-ig-active');
            if ($(this).hasClass('btn-ig')) {
                $(this).addClass('btn-ig-active');
            } else {
                $(this).addClass('btn-fb-active');
            }
        }

        if (data.method) {
            data = $.extend({}, data); // Clone a new one

            result = cropImage.imageCroper.cropper(data.method, data.option);
            if (typeof data.imgwidth !== 'undefined') {
                $('[name="image_width"]').val(data.imgwidth);
            }
            if (typeof data.imgheight !== 'undefined') {
                $('[name="image_height"]').val(data.imgheight);
            }

            if (data.method === 'getDataURL') {
                Utility.blockUI();
                $('#getDataURLModal').modal().find('.modal-body').html('<img src="' + result + '">');
                $('[name="image_data"]').val(result);
                $.ajax({
                    type: 'post',
                    url: apiUrl,
                    data:  $('#save_image_form').serialize(),
                    success: function (response) {
                        Utility.unblockUI();
                        if (typeof saveEditedImageCallback == 'function') {
                            saveEditedImageCallback(response)
                        }
                    }
                });
            }
        }
    }).on('keydown', function (e) {

        switch (e.which) {
            case 37:
                e.preventDefault();
                cropImage.imageCroper.cropper('move', -1, 0);
                break;

            case 38:
                e.preventDefault();
                cropImage.imageCroper.cropper('move', 0, -1);
                break;

            case 39:
                e.preventDefault();
                cropImage.imageCroper.cropper('move', 1, 0);
                break;

            case 40:
                e.preventDefault();
                cropImage.imageCroper.cropper('move', 0, 1);
                break;
        }

    });

    // Tooltips
    $('[data-toggle="tooltip"]').tooltip();
    $('.btn-fb').removeClass('btn-fb-active');
    if (cropImage.defaultAspectRatio == 1) {
        $('.btn-ig[data-option="0.5625"]').removeClass('btn-ig-active');
        $('.btn-ig[data-option="1"]').addClass('btn-ig-active');
        $('[name="image_width"]').val(1080);
        $('[name="image_height"]').val(1080);

    } else if (cropImage.defaultAspectRatio == 0.5625) {
        $('.btn-ig[data-option="0.5625"]').addClass('btn-ig-active');
        $('.btn-ig[data-option="1"]').removeClass('btn-ig-active');
        $('[name="image_width"]').val(1080);
        $('[name="image_height"]').val(1920);
    }
};

$('.edit_image_modal').on('show.bs.modal', function (e) {
    $('.edit_image_modal').css('visibility','visible');
    cropImage.initCropImage();

}).on('hide.bs.modal', function (e) {
    cropImage.imageCroper.cropper('destroy');
    if (typeof editImageModalClosedCallback == 'function') {
        editImageModalClosedCallback();
    }
});