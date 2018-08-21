@if (!$instagramAccount)
    <div id="js_images_comfirm_tmp" class="modal fade">
        <div class="modal-dialog modal-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title ">Instagramアカウントを連携してみましょう!</h4>
                </div>
                <div class="modal-body text-center">
                    <p class="text-center">※リクエストやコメントをするには、Instagram連携が必須となります。</p>
                    <a type="button" class="btn btn-danger" href="{{ URL::route('connect_instagram', ['redirect' => URL::route('image_list')]) }}"><i class="icon-instagram"> </i>Instagramに連携する</a>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- 確認画面  -->
    <div id="js_images_comfirm_tmp" class="modal fade" role="dialog"  style="display:block; visibility: hidden;">
        <div class="modal-dialog imglist-confirm-modal">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h4 class="modal-title">リクエストの確認</h4>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{URL::route('store_offerset')}}" id="create_offer_form">
                        {{csrf_field()}}
                        <div class="col-xs-12 content">
                            <div class="condition">
                                @if (isset($reOffer))
                                    <input class="hidden" type="radio" value="re-offer" name="create_type" checked>
                                    <input type="hidden" value="{{$offerSetGroup->id}}" name="offer_set_group_id">
                                @else
                                    <input type="hidden" value="new" name="create_type">
                                @endif
                            </div>

                            <div class="input-area mt20">
                                <dl data-intro="送信するリクエストの文言を変更できます。" data-step="4" data-position="bottom">
                                    <dt>コメント</dt>
                                    <dd>
                                        <div class="item">
                                            <textarea type="text" id="comment-area" required="required" class="form-control" name="comment">はじめまして{{$instagramAccount->name}}です！素敵な投稿をありがとうございます！突然ですが、ぜひプロモーションの一環として、この投稿内容を使用させていただけないでしょうか？</textarea>
                                        </div>
                                        <small class="pull-right"><span id="comment-remain-chars"></span>/<span id="preview-remain-chars"></span></small>
                                        <div class="row">
                                            <div class="col-md-10 ">
                                                <label for="comment_template_checkbox">
                                                    <input type="checkbox" name="no_comment_template" id="comment_template_checkbox" value="1">コメントテンプレートを利用しない
                                                </label>
                                            </div>
                                        </div>
                                        <small>※最大300文字（回答ハッシュタグ、プリセット文含）。ハッシュタグは3つまで。URL、大文字のみの英字は不可。</small>
                                    </dd>
                                </dl>
                                <dl data-intro="投稿者がこのハッシュタグをつけてリクエストに返信することで、そのUGCが利用可能となります。" class="item" data-step="5" data-position="top">
                                    <dd id="answer-hashtag"><strong>回答ハッシュタグ</strong>
                                        <input type="text" name="answer_tag" id="answer-input" value="" placeholder="hiletro" maxlength="10" required="required">
                                        <small class="pull-right"><span id="answer-remain-chars"></span>/10</small><br></dd>
                                </dl>
                                <dl>
                                    <dt>コメント投稿<br>アカウント</dt>
                                    <dd>
                                        @if ($instagramAccount)
                                            <p class="accountname">
                                                <img src="{{$instagramAccount->profile_image}}" alt="" class="img-author img-circle">
                                                {{$instagramAccount->name}}</p>
                                        @else
                                            未連携
                                        @endif
                                        <small>※公式アカウントであることをご確認下さい</small>
                                    </dd>
                                </dl>
                            </div>

                            <div class="preview-area">
                                <p class="preview-title">プレビュー</p>
                                <div class="preview-slider-js">
                                    <?php $commentTemps = \App\Models\CommentTemplate::all(); ?>
                                    <ul class="preview-slider">
                                        @foreach($commentTemps as $commentTemp)
                                        <li>
                                            <div class="preview-inner">
                                            <img src="{{static_file_version('/images/280x140.png')}}" height="140" width="280">
                                            <div class="actionicon">
                                                <i class="fa fa-heart-o fa-lg" aria-hidden="true"></i>
                                                <i class="fa fa-comment-o fa-lg" aria-hidden="true"></i>
                                                <i class="fa fa-share fa-lg" aria-hidden="true"></i>
                                            </div>
                                            <p>
                                                <strong class="accountname">
                                                    @if ($instagramAccount)
                                                        {{$instagramAccount->name}}
                                                    @else
                                                        Account Name
                                                    @endif
                                                </strong>
                                                <span class="comment_preview">
                                                </span>
                                                <span class="fixedComment">
                                                    {{$commentTemp->prefix}}
                                                </span>
                                                <span class="answer_hashtag_preview">
                                                </span>
                                                <span class="fixedComment">
                                                    {{$commentTemp->suffix}}
                                                </span>
                                            </p>
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="none-tempalte-preview-area hidden">
                                    <ul class="none-template-preview-slider">
                                        <li>
                                            <div class="preview-inner">
                                                <img src="{{static_file_version('/images/280x140.png')}}" height="140" width="280">
                                                <div class="actionicon">
                                                    <i class="fa fa-heart-o fa-lg" aria-hidden="true"></i>
                                                    <i class="fa fa-comment-o fa-lg" aria-hidden="true"></i>
                                                    <i class="fa fa-share fa-lg" aria-hidden="true"></i>
                                                </div>
                                                <p>
                                                    <strong class="accountname">
                                                        @if ($instagramAccount)
                                                            {{$instagramAccount->name}}
                                                        @else
                                                            Account Name
                                                        @endif
                                                    </strong>
                                                    <span class="comment_preview"> </span>
                                                    <span id="fixedLink">https://www.aainc.co.jp/service/letro/terms.html</span>
                                                </p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <h2>リクエスト予定</h2>
                        <div class="col-xs-3 js_offer_images" v-for="image in selectedImages">
                            <input type="hidden" name="post_id[]" :value="image.post_id">
                            <div class="js_post_box offer-plan-list overflow_hidden">
                                <div>
                                    <img class="image_url" :src="image.image_url">
                                </div>

                                <div class="container">
                                    <p class="offer-plan-authorname ml10">
                                        <a :href="image.author_url" target="_blank">@{{ image.author_name ? image.author_name : image.username}}</a>
                                    </p>
                                    <div>
                                        <div class="col-xs-12">
                                            <a class="btn btn-delete form-control js_remove_offer_draft" v-on:click="selectImage(genViewId(image.post_id))"><i class="fa fa-trash" aria-hidden="true"></i>削除</a>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" id="submit-offer" class="btn btn-danger form-control middle1" data-intro="リクエスト文面と回答ハッシュタグが決まりましたら、リクエストしてください。リクエストの承認状況はUGC一覧にて確認可能です。" data-position="top" data-step="6">
                        <i class="fa fa-arrow-circle-o-right"> </i>リクエストする
                    </button>
                </div>
            </div>

        </div>
    </div>
@endif

@push("push-script-before-main-script")
<script>
    var TOS = '{{\Classes\Constants::TERMS_OF_USE}}';
</script>
@endpush