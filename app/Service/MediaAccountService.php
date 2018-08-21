<?php


namespace App\Service;


use App\Models\Image;
use App\Models\MediaAccount;
use App\Models\Post;
use App\Repositories\Eloquent\MediaImageEntryRepository;
use App\Repositories\Eloquent\MediaAccountRepository;
use Classes\Constants;
use Classes\FacebookGraphClient;
use Classes\TwitterApiClient;
use Hborras\TwitterAdsSDK\TwitterAdsException;

class MediaAccountService extends BaseService {

    /** @var MediaAccountRepository  */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(MediaAccountRepository::class);
    }

    /**
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function findWithToken($attribute, $value)
    {
        return $this->repository->findWithToken($attribute, $value);
    }

    /**
     * @param $advertiserId
     * @return mixed
     */
    public function getFirstMediaAccountByAdvertiserId($advertiserId)
    {
        return $this->repository->findBy('advertiser_id', $advertiserId);
    }

    /**
     * @param null $advertiserId
     * @return mixed
     */
    public function getMediaAccountsWithToken($advertiserId = null)
    {
        return $this->repository->getMediaAccountsWithToken($advertiserId);
    }

    /**
     * @param MediaAccount $mediaAccount
     * @param Image $material
     * @return mixed|static
     * @throws \Exception
     */
    public function uploadFbMaterial(MediaAccount $mediaAccount, Image $material)
    {
        try {
            $apiClient = new FacebookGraphClient($mediaAccount->access_token);

            if ($material->file_format == Post::VIDEO) {
                $hash = $apiClient->uploadVideoFromUrl($material->video_url, $mediaAccount->media_account_id);
                $url = $material->video_url;
            } else {
                list($hash, $url) = $apiClient->uploadImageFromURL($material->image_url, $mediaAccount->media_account_id);
            }

            /** @var MediaImageEntryRepository $materialEntryRepository */
            $materialEntryRepository = app(MediaImageEntryRepository::class);
            $materialEntry = $materialEntryRepository->createOrUpdate(
                [
                    'image_id' => $material->id,
                    'media_account_id' => $mediaAccount->id,
                ],
                [
                    'image_id' => $material->id,
                    'media_account_id' => $mediaAccount->id,
                    'img_url' => $url ? $url : $material->image_url,
                    'hash_code' => $hash
                ]
            );
        } catch (\Exception $e) {
            \Log::error($e);
            throw new \Exception('同期に失敗しました');
        }

        return $materialEntry;
    }

    /**
     * @param MediaAccount $mediaAccount
     * @param Image $material
     * @param $creativeType
     * @param $tweet
     * @return mixed|static
     * @throws \Exception
     */
    public function uploadTwMaterial(MediaAccount $mediaAccount, Image $material, $creativeType, $tweet)
    {
        try {
            $adAccountId = $mediaAccount->media_account_id;
            $apiClient = TwitterApiClient::createInstance($mediaAccount->access_token, $mediaAccount->refresh_token, $adAccountId);

            if ($material->file_format == Post::VIDEO) {
                $id = $apiClient->uploadMaterial($adAccountId, $mediaAccount->media_user_id, $material->video_url, $tweet, $creativeType, true);
            } else {
                $id = $apiClient->uploadMaterial($adAccountId, $mediaAccount->media_user_id, $material->image_url, $tweet, $creativeType);
            }

            /** @var MediaImageEntryRepository $materialEntryRepository */
            $materialEntryRepository = app(MediaImageEntryRepository::class);
            $materialEntry = $materialEntryRepository->create(
                [
                'image_id' => $material->id,
                'media_account_id' => $mediaAccount->id,
                'img_url' => $material->image_url,
                'text' => $tweet,
                'hash_code' => $id,
                'creative_type' => $creativeType
            ]);
        } catch(TwitterAdsException $e) {
            \Log::error($e->getErrors());
            throw new \Exception($e->getErrors()[0]->message);
        } catch (\Exception $e) {
            \Log::error($e);
            throw new \Exception('同期に失敗しました');
        }

        return $materialEntry;
    }
}