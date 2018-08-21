var CommentTemplate = {};
CommentTemplate.minCommentChars = 1;
CommentTemplate.maxCommentChars = 200;

CommentTemplate.countTotalCommentChars = function (id) {
    var prefixLength = $('#prefix_' + id).val().length;
    var suffixLength = $('#suffix_' + id).val().length;
    return prefixLength + suffixLength;
}

$(document).ready(function(){
    $('.cmt-template-del-btn').unbind('click').click(function (e) {
        if (confirm('コメントテンプレートを削除しますか？')) {
            window.location.href = $(this).attr('data-url');
        }
    })

    $('.cmt-template-save-btn').unbind('click').click(function () {
        var id = $(this).attr('data-id');
        if (!id) {
            id = 0;
        }
        var lenght = CommentTemplate.countTotalCommentChars(id);

        if (lenght < CommentTemplate.minCommentChars) {
            alert("Empty input");
            return false;
        }

        if(lenght > CommentTemplate.maxCommentChars) {
            alert("Too long");
            return false
        }

        return true;
    })
});