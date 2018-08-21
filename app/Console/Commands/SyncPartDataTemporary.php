<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Hashtag;
use App\Models\HashtagHasPost;
use App\Models\Image;
use App\Models\InstagramAccount;
use App\Models\Offer;
use App\Models\OfferSet;
use App\Models\OfferSetGroup;
use App\Models\Post;
use App\Repositories\Eloquent\OfferRepository;
use App\Repositories\Eloquent\PostRepository;
use App\Service\ContractService;
use App\Service\OfferService;
use App\Service\PartService;
use App\Service\PostService;
use App\Service\SearchConditionService;
use App\Service\UserService;
use App\UGCConfig;
use Classes\InstagramApiClient;
use Illuminate\Support\Facades\DB;

class SyncPartDataTemporary extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncPartDataTemporary {siteId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $postService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        /** @var PostService postService */
        $this->postService = app(PostService::class);
    }

    /**
     *
     */
    public function doCommand()
    {
        $siteId = $this->argument('siteId');
        if ($siteId) {
            $this->syncData($siteId);
            return;
        }
        /** @var ContractService $contractService */
        $contractService = app(ContractService::class);
        $siteIds = $contractService->getActiveVtdrSites()->pluck('vtdr_site_id');
        foreach ($siteIds as $siteId) {
            $this->syncData($siteId);
        }
    }

    /**
     * @param $siteId
     */
    private function syncData($siteId)
    {
        /** @var ContractService $contractService */
        $contractService = app(ContractService::class);
        $contract = $contractService->findBy('vtdr_site_id', $siteId);
        if (!$contract) {
            \Log::error('SiteId '.$siteId.' 契約が存在しない。');
        }

        /** @var PartService $partService */
        $partService = new PartService($siteId);
        /** @var SearchConditionService $searchConditionService */
        $searchConditionService = app(SearchConditionService::class);
        /** @var OfferService $offerService */
        $offerService = app(OfferService::class);

        $allParts = $partService->all();
        $client = new InstagramApiClient();

        $instagramAccount = InstagramAccount::find(UGCConfig::get('instagram.crawlAccountId'));
        $client->setToken($instagramAccount->access_token);
        foreach ($allParts as $part) {
            try {
                $partImages = $partService->getPartImageList($part->id);
                if (!is_array($partImages->data['images'])) {
                    continue;
                }
                $courseImages = [];
                $approvedPostIds = [];
                $commentedPostIds = [];

                $imageIds  = [];
                foreach ($partImages->data['images'] as $image) {
                    try {
                        if (!$image['resource']) {
                            continue;
                        }
                        \DB::beginTransaction();
                        $status = $this->convertStatus($image['permission_status']);
                        if (!$partService->isRegisteredImageIdWithPart($image['id'], $part->id)) {
                            if ($image['resource_type'] == 8) {
                                // account
                                $searchConditionService->increaseSearchConditionLimit($contract->advertiser_id);
                                $searchCondition = $searchConditionService->createSearchCondition($contract->advertiser_id, [$image['resource']], "", false, false, Hashtag::TYPE_USER);
                            } else {
                                $searchConditionService->increaseSearchConditionLimit($contract->advertiser_id);
                                $searchCondition = $searchConditionService->createSearchCondition($contract->advertiser_id, [$image['resource']], "#", true, false, Hashtag::TYPE_HASHTAG);
                            }
                            $searchHashtag = $searchConditionService->getSearchHashtagsBySearchConditionId($searchCondition->id);
                            //save author
                            $author = $this->saveVtdrPostAuthor($image['post_author_name']);
                            //save post
                            $post = $this->saveVtdrPost($image, $author);
                            // create offer
                            if ($status != null) {
                                if ($status == Offer::STATUS_APPROVED) {
                                    $approvedPostIds[] = $post->id;
                                } else {
                                    $commentedPostIds[] = $post->id;
                                }
                            }

                            //save post hashtag relation
                            HashtagHasPost::createNew($searchHashtag->hashtag_id, $post->id);

                            $imageIds[] = $image['id'];
                            //save vtdr post temporary
                            $partService->createTemporatiData([
                                'post_id' => $post->id,
                                'post_media_id' => $post->post_id,
                                'search_condition_id' => $searchCondition->id,
                                'vtdr_image_id' => $image['id'],
                                'vtdr_site_id' => $siteId,
                                'vtdr_part_id' => $part->id
                            ]);
                        } else {
                            // just update url of this post
                            $post = $this->postService->getWhere([
                                'post_id'     => $image['post_uid'],
                                'carousel_no' => $image['carousel_no']
                            ])->first();
                            if ($post) {
                                $this->updatePostS3Url($post, $image['image_url']);
                            }
                            $imageIds[] = $image['id'];
                            // vtdr status = 0
                            if ($status === null) {
                                \DB::commit();
                                continue;
                            }

                            // update all carouse post offer
                            $postIds = $this->postService->getWhere(['post_id' => $image['post_uid']])->pluck('id');
                            $offers  = Offer::whereIn('post_id', $postIds)->where('advertiser_id', $contract->advertiser_id)->get();
                            if (!count($offers)) {
                                foreach ($postIds as $postId) {
                                    if ($status == Offer::STATUS_APPROVED) {
                                        $approvedPostIds[] = $postId;
                                    } else {
                                        $commentedPostIds[] = $postId;
                                    }
                                }
                            } else {
                                foreach ($offers as $offer) {
                                    if ($offer->status != $status) {
                                        $offer->status = $status;
                                        $offer->save();
                                    }
                                }
                            }
                        }
                        \DB::commit();
                    } catch (\Exception $e) {
                        \DB::rollBack();
                        \Log::error('part id '.$part->id.' can not get image '.$image['post_url']);
                        \Log::error($e);
                    }
                }

                $offerService->createBulkDummyOffers($contract->advertiser_id, $approvedPostIds, Offer::STATUS_APPROVED);
                $offerService->createBulkDummyOffers($contract->advertiser_id, $commentedPostIds, Offer::STATUS_COMMENTED);

                if (count($courseImages)) {
                    \Log::debug($courseImages);
                }
                // delete part_images_temporaries where vtdr_image_id not in $imageIds and parts_id = $part->id
                $partService->deletePartImageTemporaries($part->id, $imageIds);
                \Log::debug('Sync Part Id: '.$part->id.' images count: '.count($partImages->data['images']));
            } catch (\Exception $e) {
                \Log::error("part Id ".$part->id);
                \Log::error($e);
            }
        }
    }

    /**
     * @param $authorName
     * @return Author
     */
    private function saveVtdrPostAuthor($authorName)
    {
        $author = Author::where('name', $authorName)->first();
        if ($author) {
            return $author;
        }
        $author = new Author();
        $author->media_id = '';
        $author->profile_url = '';
        $author->name = $authorName;
        $author->icon_img = '';
        $author->save();

        return $author;
    }

    /**
     * @param $vtdrImage
     * @param Author $author
     * @return Post|\Illuminate\Database\Eloquent\Model|null|static
     */
    private function saveVtdrPost($vtdrImage, Author $author)
    {
        $post = $this->postService->getWhere([
            'post_id'     => $vtdrImage['post_uid'],
            'carousel_no' => $vtdrImage['carousel_no']
        ])->first();
        if ($post) {
            //update s3 url
            $this->updatePostS3Url($post, $vtdrImage['image_url']);
            return $post;
        }

        $post = new Post();
        $post->post_id = $vtdrImage['post_uid'];
        $post->post_url = $vtdrImage['post_url'];
        $post->author_id = $author->id;
        $post->pub_date = $vtdrImage['pub_date'] ? $vtdrImage['pub_date'] : '2016-01-01 00:00:00';
        $post->image_url = $vtdrImage['image_url'];
        $post->like = 0;
        $post->text = $vtdrImage['post_description'] ? $vtdrImage['post_description'] : '';
        $post->comment = 0;
        $post->file_format = $this->convertType($vtdrImage['post_type']);
        $post->carousel_no = isset($vtdrImage['carousel_no']) ? $vtdrImage['carousel_no'] : null;
        $post->save();

        return $post;
    }

    /**
     * @param Post $post
     * @param $s3Url
     */
    private function updatePostS3Url(Post $post, $s3Url)
    {
        if ($post->image_url == $s3Url) {
            return;
        }
        $oldUrl = $post->image_url;
        $post->image_url = $s3Url;
        $post->save();
        // 投稿がアップデートできないため、念のため旧URLをログに書く
        \Log::debug('post id: '.$post->id.' old url:'.$oldUrl.' new url:'.$s3Url);
    }

    /**
     * convert vtdr status to letro status
     * @param $vtdrStatus
     * @return int
     */
    private function convertStatus($vtdrStatus){
        switch ($vtdrStatus) {
            case 1:
                return Offer::STATUS_OFFERED;
            case 2:
                return Offer::STATUS_COMMENTED;
            case 3:
                return Offer::STATUS_APPROVED;
            case 4:
                return Offer::STATUS_COMMENTED;
            case 5:
                return Offer::STATUS_COMMENT_FALSE;
            default:
                return null;
        }
    }

    /**
     * @param $vtdrType
     * @return int|null
     */
    private function convertType($vtdrType)
    {
        switch ($vtdrType) {
            case 0:
                return Post::IMAGE;
            case 1:
                return Post::VIDEO;
            case 2:
                return Post::CAROUSEL_IMAGE;
            default:
                return null;
        }
    }
}
