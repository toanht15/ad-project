<?php

namespace App\Service;

use App\Models\Advertiser;
use App\Models\CommentTemplate;
use App\Models\Image;
use App\Models\Offer;
use App\Models\OfferSet;
use App\Models\OfferSetGroup;
use App\Models\Post;
use App\Repositories\Eloquent\OfferSetGroupRepository;
use App\Repositories\Eloquent\OfferSetRepository;
use App\Repositories\Eloquent\PostRepository;
use Classes\Constants;
use Classes\InstagramApiClient;
use Classes\PMAPIClient;
use Instagram\Core\ApiAuthException;

class OfferSetService extends BaseService
{

    /** @var  OfferSetRepository */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(OfferSetRepository::class);
    }

    /**
     * @param $user
     * @param $advertiser
     * @param $postIds
     * @param $comment
     * @param $answerHashtag
     * @param string $createType
     * @param $useCommentTemlate
     * @return OfferSet|bool
     * @throws \Exception
     */
    public function createNewOfferSet($user, $advertiser, $postIds, $comment, $answerHashtag, $createType = 'new', $useCommentTemlate)
    {
        if (!count($postIds)) {
            throw new \Exception('画像を選択してください');
        }
        /** @var PostRepository $postRepository */
        $postRepository = app(PostRepository::class);
        $postMedia      = $postRepository->getPostGroupByMediaId($postIds);
        $mediaIds       = $postMedia->pluck('post_id');
        $posts          = $postRepository->queryWhere(['post_id' => ['in', $mediaIds]], false, [], null,
            ['id', 'post_id', 'admin_approved_flg', 'author_id', 'image_url', 'file_format', 'video_url']);

        /** @var InstagramAccountService $instagramAccountService */
        $instagramAccountService = app(InstagramAccountService::class);
        /** @var OfferService $offerService */
        $offerService = app(OfferService::class);

        $instagramAccount = $instagramAccountService->getInstagramAccountByAdvertiserId($advertiser->id)->first();

        if (!$instagramAccount) {
            throw new \Exception('Instagramとの連携がありません');
        }

        if (!(new InstagramApiClient())->isValidToken($instagramAccount->access_token)) {
            throw new \Exception('Instagramのアクセストークンが切れています。再度、Instagram連携を行ってください');
        }

        $isReOffer = $createType == 're-offer';

        if ($isReOffer) {
            //再オファー
            Offer::whereIn('post_id', $posts->pluck('id'))->where('advertiser_id', '=', $advertiser->id)->update(['status' => Offer::STATUS_OFFERED]);
        }

        if (!$posts) {
            throw new \Exception('不明なリクエスト');
        }

        //        $isUseCommentTemplate = isset($data['no_comment_template']) ? false : true;
        // APIで自動でコメントできなくなるため、コメントテンプレートを利用しなくする
        list($pmResponse, $postCommentMatch, $lastComment) = $offerService->sendOffers($advertiser, $instagramAccount, $postMedia, $comment, $answerHashtag, $useCommentTemlate);

        $offerSet = $offerService->createOffers($user, $advertiser, $posts, $postCommentMatch, $pmResponse['offer_set_id'], $isReOffer, $lastComment);
        if (!$offerSet) {
            throw new \Exception('リクエストが失敗しました');
        }

        return $offerSet;
    }
}
