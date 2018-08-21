<?php


namespace App\Service;

use App\Models\AdsUseImage;
use App\Models\Advertiser;
use App\Models\Author;
use App\Models\MediaAd;
use App\Models\AdsConversion;
use App\Models\MediaAdsInsight;
use App\Models\MediaImageEntry;
use App\Models\Hashtag;
use App\Models\HashtagHasPost;
use App\Models\Image;
use App\Models\MediaAccount;
use App\Models\Offer;
use App\Models\OfferSet;
use App\Models\OfferSetGroup;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Eloquent\HashtagRepository;
use App\Repositories\Eloquent\ImageRepository;
use App\Repositories\Eloquent\PostRepository;
use App\Repositories\Eloquent\SlideshowImageRepository;
use Classes\Constants;
use Classes\FacebookGraphClient;
use FacebookAds\Object\Fields\AdImageFields;
use Instagram\Media;
use Intervention\Image\ImageManagerStatic;

class ImageService extends BaseService
{
    /** @var  ImageRepository */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(ImageRepository::class);
    }

    /**
     * @param $postIds
     */
    public function deletePost($postIds) {
        $offers = Offer::whereIn('post_id', $postIds)->pluck('id');
        $images = Image::whereIn('offer_id', $offers)->pluck('id');
        $mediaImages = MediaImageEntry::whereIn('image_id', $images)->pluck('id');
        $ads = AdsUseImage::whereIn('image_entry_id', $mediaImages)->pluck('ad_id');

        try {
            \DB::beginTransaction();
            // delete action insights
            AdsConversion::whereIn('facebook_ad_id', $ads)->delete();
            // delete insights
            MediaAdsInsight::whereIn('facebook_ad_id', $ads)->delete();
            // delete ads and relations

            AdsUseImage::whereIn('image_entry_id', $mediaImages)->delete();
            MediaAd::whereIn('id', $ads)->delete();
            MediaImageEntry::whereIn('id', $mediaImages)->delete();

            // delete image, offer and post
            Image::whereIn('id', $images)->delete();
            Offer::whereIn('id', $offers)->delete();
            HashtagHasPost::whereIn('post_id', $postIds)->delete();
            Post::whereIn('id', $postIds)->delete();

            \DB::commit();
        } catch(\Exception $e) {
            \Log::error($e);
            \DB::rollBack();
        }
    }
    
    public function getCrawledImages() {
        $crawledImages = Image::join('offers', 'images.offer_id', '=', 'offers.id')
        ->join('posts', 'offers.post_id', '=', 'posts.id')
        ->where('images.image_url', 'NOT LIKE', '%letro.jp%')
        ->where('images.image_url', 'NOT LIKE', '%casanova.com%')
        ->whereNotNull('posts.post_id')
        ->where('posts.post_id', '<>', '')
        ->select(['images.*', 'posts.post_id'])
        ->get();
        
        return $crawledImages;
    }

    /**
     * @param $imageId
     * @param $url
     * @return boolean
     */
    public function updateImageUrl($imageId, $url) {
        return Image::where('id', $imageId)->update(['image_url' => $url]);
    }

    /**
     * @param $imageId
     * @param $imageUrl
     * @param $videoUrl
     * @return boolean
     */
    public function updateVideoUrl($imageId, $imageUrl, $videoUrl) {
        return Image::where('id', $imageId)->update(
            [
                'image_url' => $imageUrl,
                'video_url' => $videoUrl
            ]);
    }

    /**
     * @param User $user
     * @param MediaAccount $mediaAccount
     * @param $hashcodeList
     */
    public function getFacebookImage(User $user, MediaAccount $mediaAccount, $hashcodeList)
    {
        $facebookClient = new FacebookGraphClient($mediaAccount->access_token);
        $fbImages = $facebookClient->getImage($mediaAccount->media_account_id, 500);

        if (!isset($fbImages['data'])) {
            return;
        }

        try {
            \DB::beginTransaction();
            /** @var HashtagRepository $hashtagRepository */
            $hashtagRepository = app(HashtagRepository::class);
            /** @var SearchConditionService $searchConditionService */
            $searchConditionService = app(SearchConditionService::class);
            $defaultSearchCondition = $searchConditionService->getDefaultSearchCondition($mediaAccount->advertiser_id);
            $defaultHashtag = $hashtagRepository->getHashtagBySearchConditionId($defaultSearchCondition->id)->first();

            $offerSetGroup = null;
            $offerSet = null;
            foreach ($fbImages['data'] as $imageData) {
                if (!in_array($imageData[AdImageFields::HASH], $hashcodeList)) {
                    continue;
                }
                // default offer set group
                if (!$offerSetGroup) {
                    $offerSetGroup = OfferSetGroup::createDefaultOfferSetGroup($mediaAccount->advertiser_id);
                }
                if (!$offerSet) {
                    $offerSet = OfferSet::createDefaultOfferSet($offerSetGroup->id, $mediaAccount->advertiser_id, $user->id);
                }
                $this->saveImageByFacebookData($imageData, $user, $mediaAccount, $defaultHashtag, $offerSet);
            }

            $searchService = new SearchConditionService();
            $searchService->updateSearchConditionResultCount($defaultHashtag);

            \DB::commit();
        } catch(\Exception $e) {
            \DB::rollBack();
            \Log::error($e);
        }
    }

    /**
     * @param $imageId
     * @return mixed
     */
    public function getImageKpi($imageId)
    {
        return $this->repository->getImageKpi($imageId);
    }

    /**
     * @param $imageData
     * @param $user
     * @param $mediaAccount
     * @param $hashtag
     * @param $offerSet
     * @return int|mixed|null
     */
    public function saveImageByFacebookData($imageData, $user, $mediaAccount, $hashtag, $offerSet)
    {
        $mediaImage = MediaImageEntry::where('hash_code', $imageData[AdImageFields::HASH])->first();
        if ($mediaImage) {
            return null;
        }

        //create new post
        $post = new Post();
        $post->image_url = $imageData[AdImageFields::URL];
        $post->author_id = env('LETRO_AUTHOR_ID');
        $post->pub_date = (new \DateTime($imageData[AdImageFields::CREATED_TIME]))->format('Y-m-d H:i:s');
        $post->file_format = Post::IMAGE;
        $post->save();

        //hashtag and post relation
        $hashtagHasPost = new HashtagHasPost();
        $hashtagHasPost->hashtag_id = $hashtag->id;
        $hashtagHasPost->post_id = $post->id;
        $hashtagHasPost->save();

        //create offer
        $offer = new Offer();
        $offer->offer_set_id = $offerSet->id;
        $offer->post_id = $post->id;
        $offer->advertiser_id = $mediaAccount->advertiser_id;
        $offer->user_id = $user->id;
        $offer->status = Offer::STATUS_APPROVED;
        $offer->save();

        //create images
        $image = new Image();
        $image->origin_author_id = env('LETRO_AUTHOR_ID');
        $image->offer_id = $offer->id;
        $image->advertiser_id = $mediaAccount->advertiser_id;
        $image->image_url = $post->image_url;
        $image->user_id = $user->id;
        $image->file_format = $post->file_format;
        $image->save();

        //create facebook image
        $mediaImageEntry = new MediaImageEntry();
        $mediaImageEntry->image_id = $image->id;
        $mediaImageEntry->media_account_id = $mediaAccount->id;
        $mediaImageEntry->img_url = $imageData['url'];
        $mediaImageEntry->hash_code = $imageData['hash'];
        $mediaImageEntry->save();

        return $post->id;
    }

    /**
     * @param $imageData
     * @param $width
     * @param $height
     * @param $folderName
     * @return string
     * @throws \Exception
     */
    public function checkAndSaveImage($imageData, $width, $height, $folderName)
    {
        if (!in_array([$width, $height], [[1080,1080], [1080, 1920], [1200,628], [600,600], [1000,400]])) {
            throw new \Exception('画像サイズが正しくありません');
        }

        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

        $urlPath = 'images/edited_images';
        if (!is_dir(public_path($urlPath))) {
            mkdir(public_path($urlPath), 755);
        }

        if ($folderName) {
            $urlPath = $urlPath . '/' . $folderName;

            if (!is_dir(public_path($urlPath))) {
                mkdir(public_path($urlPath), 755);
            }
        }

        $imagePath = $urlPath . '/' . (new \DateTime())->format('YmdHis') . '.png';

        ImageManagerStatic::make($data)->resize($width, $height)->save(public_path($imagePath));

        return $imagePath;
    }

    /**
     * @param $offer
     * @param $post
     * @return mixed
     */
    public function createPostImage($offer, $post)
    {
        $image = $this->repository->queryWhere([
            'offer_id'      => $offer->id,
            'advertiser_id' => $offer->advertiser_id
        ])->first();
        if ($image) {
            return $image;
        }

        $data = [
            'offer_id' => $offer->id,
            'advertiser_id' => $offer->advertiser_id,
            'origin_author_id' => $post->author_id,
            'user_id' => $offer->user_id,
            'image_url' =>  $post->image_url,
            'file_format' => $post->file_format
        ];
        if (in_array($post->file_format, [Post::VIDEO, Post::CAROUSEL_VIDEO])) {
            $data['video_url'] = $post->video_url;
        }

        $image = $this->repository->create($data);

        return $image;
    }

    /**
     * @param $images
     * @param Advertiser $advertiser
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function uploadImageToDefaultHashtag($images, Advertiser $advertiser)
    {
        /** @var SearchConditionService $searchConditionService */
        $searchConditionService = app(SearchConditionService::class);
        /** @var OfferService $offerService */
        $offerService = app(OfferService::class);

        $defaultSearchCondition = $searchConditionService->getDefaultSearchCondition($advertiser->id);
        $defaultHashtag = $searchConditionService->getHashtagBySearchConditionId($defaultSearchCondition->id)->first();
        
        $path = 'storage/images/'.$advertiser->facebook_ads_id.'/';
        $postIds = [];
        foreach ($images as $image) {
            $fileName = $path.$image->hashName();
            \Storage::disk('public')->put($fileName, file_get_contents($image->getRealPath()));

            $post = new Post();
            $post->image_url = url($fileName);
            $post->author_id = env('LETRO_AUTHOR_ID', 1);
            $post->pub_date = (new \DateTime())->format('Y-m-d H:i:s');
            $post->text = '';
            $post->save();
            $postIds[] = $post->id;

            $hashtagHasPost = new HashtagHasPost();
            $hashtagHasPost->hashtag_id = $defaultHashtag->id;
            $hashtagHasPost->post_id = $post->id;
            $hashtagHasPost->save();
        }

        //許諾にする
        $offerService->createBulkDummyOffers($advertiser->id, $postIds, Offer::STATUS_APPROVED);

        return $defaultHashtag;
    }

    /**
     * @param $image
     * @return bool
     */
    public function isUsedForSlideshow($image)
    {
        /** @var SlideshowImageRepository $slideshowImageRepository */
        $slideshowImageRepository = app(SlideshowImageRepository::class);
        $slideshow = $slideshowImageRepository->findBy('image_id', $image->id);

        return $slideshow ? true : false;
    }

    /**
     * @param $offerId
     * @param $adAccountId
     * @return mixed
     */
    public function getImageListWithSummaryByOfferId($offerId, $adAccountId)
    {
        return $this->repository->getImageListWithSummaryByOfferId($offerId, $adAccountId);
    }
}
