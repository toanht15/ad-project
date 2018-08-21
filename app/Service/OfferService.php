<?php
namespace App\Service;

use App\Models\Advertiser;
use App\Models\CommentTemplate;
use App\Models\Image;
use App\Models\Offer;
use App\Models\OfferSet;
use App\Models\OfferSetGroup;
use App\Models\Post;
use App\Repositories\Eloquent\OfferRepository;
use App\Repositories\Eloquent\OfferSetRepository;
use App\Repositories\Eloquent\SlideshowRepository;
use App\Repositories\Eloquent\ArchivedPostRepository;
use Classes\Constants;
use Classes\PMAPIClient;

class OfferService extends BaseService
{
    protected $repository;

    CONST DEFAULT_TIME = '0000-00-00 00:00:00';

    public function __construct(OfferRepository $offerRepository)
    {
        $this->repository = $offerRepository;
    }

    /**
     * @param $advertiserId
     * @param array $status
     * @return mixed
     */
    public function countOfferByAdvertiserId($advertiserId, $status = [])
    {
        $whereCondition = [
            'advertiser_id' => $advertiserId
        ];
        if (count($status)) {
            $whereCondition['status'] = ['in', $status];
        }

        return $this->repository->queryWhere($whereCondition,true);
    }
    
    /**
     * @param $advertiserId
     * @return mixed
     */
    public function getOfferGroupByMediaId($advertiserId)
    {
        return $this->repository->getOfferGroupByPostId($advertiserId);
    }

    /**
     * @param $advertiserId
     * @return int
     */
    public function countLivingOffer($advertiserId)
    {
        /** @var SlideshowRepository $slideshowRepository */
        $slideshowRepository = app(SlideshowRepository::class);
        $endDate = (new \DateTime())->format('Y-m-d');
        $beginDate = (new \DateTime('-2 days'))->format('Y-m-d');
        $images = $this->repository->getActiveOfferWithKpi($advertiserId, $beginDate, $endDate)->pluck('offer_id')->toArray();
        $slideshows = $slideshowRepository->getActiveSlideshowWithKpi($advertiserId, $beginDate, $endDate)->pluck('offer_id')->toArray();
        $result = array_unique(array_merge($images, $slideshows));

        return count($result);
    }

    /**
     * @param $offerId
     * @return mixed
     */
    public function getOfferWithReport($offerId)
    {
        return $this->repository->getOfferWithReport($offerId);
    }

    /**
     * @param $originImageId
     * @param $advertiserId
     * @return mixed
     */
    public function getOfferBaseInfoByImageId($originImageId, $advertiserId)
    {
        return $this->repository->getOfferBaseInfoByImageId($originImageId, $advertiserId);
    }

    /**
     * @param $advertiserId
     * @param int $limit
     * @return mixed
     */
    public function getYesterdayUpOffers($advertiserId, $limit = 50)
    {
        return $this->repository->getYesterdayUpOffers($advertiserId, $limit);
    }

    /**
     * @param $advertiserId
     * @param int $limit
     * @return mixed
     */
    public function getYesterdayDownOffers($advertiserId, $limit = 50)
    {
        return $this->repository->getYesterdayDownOffers($advertiserId, $limit);
    }

    /**
     * @param $advertiserId
     * @param $date
     * @param int $limit
     * @param string $order
     * @param string $direction
     * @return mixed
     */
    public function getApprovedByDate($advertiserId, $date, $limit = 50, $order = 'approved_at', $direction = 'desc')
    {
        return $this->repository->getApprovedByDate($advertiserId, $date, $limit, $order, $direction);
    }

    /**
     * @param $advertiserId
     * @param null $date
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function getOfferCTRByAdvertiserIdAndDate($advertiserId, $date = null)
    {
        return $this->repository->getOfferCTRByAdvertiserIdAndDate($advertiserId, $date);
    }

    /**
     * @param $advertiserId
     * @param null $mediaType
     * @param null $beginDate
     * @param null $endDate
     * @param int $limit
     * @return mixed
     */
    public function getTopPerformanceUgc($advertiserId, $mediaType = null, $beginDate = null, $endDate = null, $limit = 20)
    {
        return $this->repository->getTopPerformanceUgc($advertiserId, $mediaType, $beginDate, $endDate, $limit);
    }

    /**
     * @param $mediaAccount
     */
    public function updateOfferCTR($mediaAccount)
    {
        $today = (new \DateTime())->format('Y-m-d');
        $offers = $this->getOfferCTRByAdvertiserIdAndDate($mediaAccount->advertiser_id, $today);

        foreach ($offers as $offer) {
            $updateData = [
                'today_ctr' => $offer->ctr*100
            ];

            if ($offer->spend > 0 && $offer->status != Offer::STATUS_LIVING) {
                $updateData['status'] = Offer::STATUS_LIVING;
            }
            $this->updateModel($updateData, $offer->id);
        }

        $yesterday = (new \DateTime('-1 day'))->format('Y-m-d');
        $offers = $this->getOfferCTRByAdvertiserIdAndDate($mediaAccount->advertiser_id, $yesterday);

        foreach ($offers as $offer) {
            $this->updateModel(['yesterday_ctr' => $offer->ctr*100], $offer->id);
        }

        $twoDayAgo = (new \DateTime('-2 day'))->format('Y-m-d');
        $offers = $this->getOfferCTRByAdvertiserIdAndDate($mediaAccount->advertiser_id, $twoDayAgo);

        foreach ($offers as $offer) {
            $this->updateModel(['two_day_ago_ctr' => $offer->ctr*100], $offer->id);
        }
    }

    /**
     * @param $offerId
     * @param $postId
     * @param $advertiserId
     * @param $userId
     */
    public function archive($offerId, $postId, $advertiserId, $userId){
        $this->repository->update([
            'status' => Offer::STATUS_ARCHIVE
        ], $offerId);

        /** @var ArchivedPostRepository $archivedRepository */
        $archivedRepository = app(ArchivedPostRepository::class);
        $archivedRepository->create([
            'advertiser_id' => $advertiserId,
            'post_id' => $postId,
            'created_account_id' => $userId
        ]);
    }

    /**
     * @param $advertiser
     * @param $instagramAccount
     * @param $posts
     * @param $rawComment
     * @param $answerHashtag
     * @param $isUseCommentTemplate
     * @return array
     * @throws \Exception
     */
    public function sendOffers($advertiser, $instagramAccount, $posts, $rawComment, $answerHashtag, $isUseCommentTemplate)
    {
        //get last comment index
        $lastCommentIndex = -1;
        $commentTempCount = 0;
        $commentTemps = [];
        if ($isUseCommentTemplate) {
            $lastComment = Offer::where('advertiser_id', $advertiser->id)->orderBy('id', 'desc')->select('comment_temp_id')->first();
            $commentTemps = CommentTemplate::all();
            $commentTempCount = count($commentTemps);
            if ($lastComment) {
                foreach ($commentTemps as $index => $commentTemp) {
                    if ($commentTemp->id == $lastComment->comment_temp_id) {
                        $lastCommentIndex = $index;
                        break;
                    }
                }
            }
        }
        $comment = $rawComment;
        $offerInfos = [];
        $postCommentMatch = [];
        foreach ($posts as $index => $post) {
            if (!$post->post_id || $post->admin_approved_flg) {
                continue;
            }

            if ($isUseCommentTemplate) {
                $commentTempIndex = ($index + $lastCommentIndex + 1) % $commentTempCount;
                $commentData = $commentTemps[$commentTempIndex];
                $postCommentMatch[$post->id] = $commentData->id;
                $comment = $rawComment . $commentData->prefix . $answerHashtag . $commentData->suffix;
            } else {
                $comment = $rawComment . Constants::TERMS_OF_USE;
            }

            $offerInfo = [
                'media_id'          => $post->post_id,
                'comment'           => $comment
            ];

            $offerInfos[] = $offerInfo;
        }

        $pmResponse = ['offer_set_id' => 0];

        if (count($offerInfos) > 0) {
            //APIリクエスト
            $instagramToken = $instagramAccount->access_token;

            $apiClient = new PMAPIClient();

            $pmResponse = $apiClient->requestOfferSet(
                $offerInfos,
                $instagramToken,
                $answerHashtag
            );

            if (!isset($pmResponse['offer_set_id'])) {
                throw new \Exception('PMからoffer_set_idを取得できませんでした。');
            }
        }

        return [$pmResponse, $postCommentMatch, $comment];
    }

    /**
     * @param $user
     * @param $advertiser
     * @param $posts
     * @param $postCommentMatch
     * @param $pmOfferSetId
     * @param bool $isReOffer
     * @param $comment
     * @return OfferSet|bool
     * @throws \Exception
     */
    public function createOffers($user, $advertiser, $posts, $postCommentMatch, $pmOfferSetId, $isReOffer = false, $comment)
    {
        try {
            /** @var ImageService $imageService */
            $imageService = app(ImageService::class);
            /** @var OfferSetGroupService $offerSetGroupService */
            $offerSetGroupService = app(OfferSetGroupService::class);
            /** @var OfferSetService $offerSetService */
            $offerSetService = app(OfferSetService::class);
            /** @var OfferService $offerService */
            $offerService = app(OfferService::class);

            \DB::beginTransaction();
            $offerSetGroup = $offerSetGroupService->create($advertiser->id, 'リクエスト_'.time());

            $offerSet = $offerSetService->createModel([
                'offer_set_group_id' => $offerSetGroup->id,
                'title' => $offerSetGroup->title,
                'comment' => $comment,
                'advertiser_id' => $advertiser->id,
                'user_id' => $user->id,
                'pm_id' => $pmOfferSetId,
                'target_count' => $posts->count()
            ]);

            foreach ($posts as $post) {
                $isApproved = !$post->post_id || $post->admin_approved_flg;

                if ($isApproved) {
                    $offerSetGroup->approved_image_count ++;
                } else {
                    $offerSetGroup->offering_image_count ++;
                }

                if (isset($postCommentMatch[$post->id])) {
                    $commentId = $postCommentMatch[$post->id];
                } else {
                    $commentId = null;
                }

                if ($isReOffer) {
                    $offerService->updateWhere([
                        'advertiser_id' => $advertiser->id,
                        'post_id' =>  $post->id
                    ], [
                        'offer_set_id' => $offerSet->id,
                        'comment_temp_id' => $commentId
                    ]);
                } else {
                    $offerData = [
                        'offer_set_id' => $offerSet->id,
                        'advertiser_id' => $advertiser->id,
                        'post_id' => $post->id,
                        'user_id' => $user->id,
                        'comment_temp_id' => $commentId
                    ];
                    if ($isApproved) {
                        $offerData['status'] = Offer::STATUS_APPROVED;
                    } else {
                        $offerData['status'] = Offer::STATUS_OFFERED;
                    }
                    $offer = $offerService->createModel($offerData);

                    //create default image
                    $imageService->createPostImage($offer, $post);
                }
            }

            $offerSetGroup->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e);
            return false;
        }
        $this->sendMailToEgc($advertiser, $offerSet);

        return $offerSet;
    }

    /**
     * @param Advertiser $advertiser
     * @param OfferSet $offerSet
     */
    public function sendMailToEgc(Advertiser $advertiser, OfferSet $offerSet)
    {
        if (env('APP_DEBUG', false)) {
            return;
        }
        try {
            \Mail::send('emails.notify_egc', [
                'offerSet'   => $offerSet,
                'advertiser' => $advertiser,
            ], function ($message) use ($advertiser) {
                $message->from(env('MAIL_FROM'), env('MAIL_NAME'));
                $message->to(explode(',',
                    env('EGC_EMAILS')))->subject("[Letro EGC][{$advertiser->name}からオファーが送信されました]");
            });
        } catch (\Exception $e) {
            \Log::error('OfferSetService::sendMailToEgc() Advertiser ID:'. $advertiser->id);
            \Log::error($e);
        }
    }

    /**
     * @param $advertiserId
     * @param array $postIds
     * @param $status
     */
    public function createBulkDummyOffers($advertiserId, array $postIds, $status)
    {
        if(!count($postIds)) {
            return;
        }
        /** @var OfferSetRepository $offerSetRepository */
        $offerSetRepository = app(OfferSetRepository::class);
        /** @var ImageService $imageService */
        $imageService = app(ImageService::class);
        /** @var OfferService $offerService */
        $offerService = app(OfferService::class);
        /** @var PostService $postService */
        $postService = app(PostService::class);

        $offerSet = $offerSetRepository->findBy('title', 'Dummy_' . $advertiserId);
        if (!$offerSet) {
            $offerSet = $this->createDummyOfferSet($advertiserId);
        }

        $posts = $postService->getWhere([
            'id' => [
                'in',
                $postIds
            ]
        ]);

        foreach ($posts as $post) {
            $offer = $offerService->createOrUpdate([
                'post_id' => $post->id,
                'advertiser_id' => $offerSet->advertiser_id
            ], [
                'post_id' => $post->id,
                'advertiser_id' => $offerSet->advertiser_id,
                'offer_set_id' => $offerSet->id,
                'user_id' => $offerSet->user_id
            ], [
                'status' => $status
            ]);

            $imageService->createPostImage($offer, $post);
        }
    }

    /**
     * @param $advertiserId
     * @return OfferSet
     */
    private function createDummyOfferSet($advertiserId)
    {
        /** @var UserService $userService */
        $userService   = app(UserService::class);
        /** @var OfferSetGroupService $offerSetGroupService */
        $offerSetGroupService = app(OfferSetGroupService::class);
        /** @var OfferSetService $offerSetService */
        $offerSetService = app(OfferSetService::class);

        $user          = $userService->getUserByAdvertiserId($advertiserId)->first();
        $offerSetGroup = $offerSetGroupService->create($advertiserId, 'Dummy_'.$advertiserId);

        $offerSet = $offerSetService->createModel([
            'offer_set_group_id' => $offerSetGroup->id,
            'title' => $offerSetGroup->title,
            'comment' => 'dummy',
            'answer_tag' => 'dummy',
            'advertiser_id' => $advertiserId,
            'user_id' => $user->id
        ]);

        return $offerSet;
    }

    /**
     * @param $offerId
     * @param $offerStatus
     * @return bool
     * @throws \Exception
     */
    public function updateStatusOffer($offerId, $offerStatus)
    {
        /** @var PostService $postService */
        $postService = app(PostService::class);

        \DB::beginTransaction();
        try {

            $offer = $this->findBy('id', $offerId);
            $post = $offer->post;
            $offerSet = $offer->offerSet;

            $pmOfferSetId = $offerSet->pm_id;

            if ($post->file_format == Post::IMAGE || $post->file_format == Post::VIDEO) {
                $this->updateModel(['status' => $offerStatus], $offerId);

            } else { // Carousel
                $carouselPostIds = $postService->getPostsByMediaId($post->post_id)->pluck('id')->all();
                $carouselOffers = $this->getWhere([
                    'offer_set_id' => $offer->offer_set_id,
                    'advertiser_id' => $offer->advertiser_id,
                    'user_id' => $offer->user_id,
                    'post_id' => ['in', $carouselPostIds]
                ]);

                foreach ($carouselOffers as $carouselOffer) {
                    $this->updateModel(['status' => $offerStatus], $carouselOffer->id);
                }
            }

            //Update PM
            /** @var PMAPIClient $pmClient */
            $pmClient = new PMAPIClient();
            $pmClient->updateOfferStatus($pmOfferSetId, $post->post_id, $offerStatus);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
        return true;
    }

}