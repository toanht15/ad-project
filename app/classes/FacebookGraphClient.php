<?php

namespace Classes;

use App\Models\MediaAccount;
use App\Service\MediaAccountService;
use App\Service\MediaTokenService;
use App\UGCConfig;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\FacebookRequest;
use Facebook\FileUpload\FacebookFile;
use Facebook\FileUpload\FacebookResumableUploader;
use Facebook\FileUpload\FacebookTransferChunk;
use Facebook\FileUpload\FacebookVideo;
use FacebookAds\Object\Fields\AdCreativeFields;
use FacebookAds\Object\Fields\AdFields;
use FacebookAds\Object\Fields\AdImageFields;

class FacebookGraphClient
{

    /** @var  Facebook */
    public $facebook;
    /** @var  FacebookGraphClient $instant */
    public static $instant;

    protected $accessToken;
    protected $appId;
    protected $appSecret;
    protected $graphVersion;

    const BATCH_LIMIT = 30;

    public function __construct($accessToken = null)
    {

        $this->appId = UGCConfig::get('facebook.app_id');
        $this->appSecret = UGCConfig::get('facebook.app_secret');
        $this->graphVersion = UGCConfig::get('facebook.default_graph_version');

        $this->getFacebookInstant();
        if ($accessToken) {
            $this->setAccessToken($accessToken);
        }
    }

    /**
     * @param null $accessToken
     * @return FacebookGraphClient
     */
    public static function getInstant($accessToken = null)
    {
        if (!static::$instant) {
            static::$instant = new FacebookGraphClient($accessToken);
        }
        return static::$instant;
    }

    /**
     * @return \Facebook\Helpers\FacebookRedirectLoginHelper
     */
    public function getLoginHelper()
    {
        return $this->facebook->getRedirectLoginHelper();
    }

    public function setAccessTokenFromCode($redirectUrl)
    {
        $accessToken = $this->getLoginHelper()->getAccessToken($redirectUrl);
        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $this->facebook->getOAuth2Client();
        if (! $accessToken->isLongLived()) {
            // Exchanges a short-lived access token for a long-lived one
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        }

        $this->setAccessToken($accessToken->getValue());
    }

    /**
     * @param $fbCallback
     * @param array $scope
     * @return string
     */
    public function getLoginUrl($fbCallback, array $scope)
    {
        return $this->getLoginHelper()->getLoginUrl($fbCallback, $scope);
    }

    /**
     * @return Facebook
     */
    public function getFacebookInstant()
    {
        if (!$this->facebook) {
            $this->facebook = new Facebook([
                'app_id' => $this->appId,
                'app_secret' => $this->appSecret,
                'default_graph_version' => $this->graphVersion,
                'persistent_data_handler' => new MyLaravelPersistentDataHandler(),
            ]);
        }
        return $this->facebook;
    }

    /**
     * @param $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->facebook->setDefaultAccessToken($accessToken);
        $this->accessToken = $accessToken;
    }

    /**
     * @param $accessToken
     * @return bool
     */
    public function isValidAccessToken($accessToken)
    {
        try {
            $accessTokenMetaData = $this->facebook->getOAuth2Client()->debugToken($accessToken);
            if (!$accessTokenMetaData->getIsValid()) {
                \Log::error("AccessToken is already invalid: $accessToken");
                return false;
            }

            $this->setAccessToken($accessToken);

            return true;
        } catch (FacebookSDKException $ex) {
            // Session not valid, Graph API returned an exception with the reason.
            \Log::error($ex);
        } catch (\Exception $ex) {
            // Graph API returned info, but it may mismatch the current app or have expired.
            \Log::error($ex);
        }

        return false;
    }

    /**
     * @param string $method
     * @param $endpoint
     * @param array $params
     * @param bool $decode
     * @return array|\Facebook\FacebookResponse|null
     */
    public function executeRequest($method = "GET", $endpoint, $params = [], $decode = false)
    {
        $response = null;
        try {
            $response = $this->facebook->sendRequest($method, $endpoint, $params);

            if ($decode && $response) {
                $response = $response->getDecodedBody();
            }
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            \Log::error($e);
            $paramLogs = ['params' => $params];
            $paramLogs['response'] = $e->getResponseData();
            $paramLogs['endpoint'] = $endpoint;
            $paramLogs['method'] = $method;
            \Log::error($paramLogs);
            return $e->getResponseData();

        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            \Log::error($e);

        } catch (\Exception $e) {
            \Log::error($e);

        }

        $this->writeAPILog($endpoint, false);

        return $response;
    }

    /**
     * @param $batchParams
     * @return array
     */
    public function sendBatchRequest($batchParams)
    {
        $this->writeAPILog($batchParams, true);
        $response = $this->facebook->sendBatchRequest($batchParams);
        if ($response) {
            $response = $response->getDecodedBody();
        }

        return $response;
    }

    /**
     * @param $param
     * @param $isBatchRequest
     */
    private function writeAPILog($param, $isBatchRequest)
    {
        try {
            $endpoint = $isBatchRequest ? $param[0]->getEndpoint() . ' ' . count($param) . ' requests' : $param;
            if (\App::runningInConsole()) {
                \Log::debug('Console API ' . $endpoint . ' COMMAND:' . $GLOBALS['argv'][1]);
            } else {
                $url = \Route::getCurrentRequest()->url();
                \Log::debug('Web API ENDPOINT:' . $endpoint . ' URL:' . $url);
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }
    }

    /**
     * @param $adAccountId
     * @param array $imageHashs
     * @param array $videoIds
     * @param array $bodies
     * @param array $titles
     * @param array $descriptions
     * @param $adFormat
     * @param $linkUrl
     * @param $callToActionType
     * @return array|\Facebook\FacebookResponse|null
     */
    public function createAssetFeeds($adAccountId, array $imageHashs, array $videoIds, array $bodies, array $titles, array $descriptions, $adFormat, $linkUrl, $callToActionType)
    {
        $imagesParam = [];
        $videosParam = [];
        $bodiesParam = [];
        $titlesParam = [];
        $descParam = [];
        foreach ($imageHashs as $imageHash) {
            if (!trim($imageHash)) {
                continue;
            }
            $imagesParam[] = [
                'hash' => trim($imageHash)
            ];
        }
        foreach ($videoIds as $videoId) {
            if (!trim($videoId)) {
                continue;
            }
            $videosParam[] = [
                'video_id' => trim($videoId)
            ];
        }
        foreach ($bodies as $body) {
            if (!trim($body)) {
                continue;
            }
            $bodiesParam[] = [
                'text' => $body
            ];
        }
        foreach ($titles as $title) {
            if (!trim($title)) {
                continue;
            }
            $titlesParam[] = [
                'text' => $title
            ];
        }
        foreach ($descriptions as $description) {
            if (!trim($description)) {
                continue;
            }
            $descParam[] = [
                'text' => $description
            ];
        }
        $params = [
            'link_urls' => [['website_url' => $linkUrl]],
            'ad_formats' => [$adFormat],
            'call_to_action_types' => [$callToActionType],
            'bodies' => $bodiesParam,
            'titles' => $titlesParam,
            'descriptions' => $descParam
        ];

        if (count($imagesParam) > 0) {
            $params['images'] = $imagesParam;
        }

        if (count($videosParam) > 0) {
            $params['videos'] = $videosParam;
        }

        return $this->executeRequest('POST', '/'.$adAccountId.'/adasset_feeds', $params, true);
    }

    /**
     * @param $adAccountId
     * @param $assetFeedId
     * @param $pageId
     * @param null $instagramActorId
     * @return array|\Facebook\FacebookResponse|null
     */
    public function createDynamicCreative($adAccountId, $assetFeedId, $pageId, $instagramActorId = null)
    {
        $params = [
            'asset_feed_id' => $assetFeedId,
            'object_story_spec' => [
                'page_id' => $pageId
            ]
        ];

        if ($instagramActorId) {
            $params['object_story_spec']['instagram_actor_id'] = $instagramActorId;
        }

        return $this->executeRequest('POST', '/'.$adAccountId.'/adcreatives', $params, true);
    }

    /**
     * @param $adAccountId
     * @param $adSetId
     * @param $creativeId
     * @param $adName
     * @return array|\Facebook\FacebookResponse|null
     */
    public function createDynamicCreativeAd($adAccountId, $adSetId, $creativeId, $adName)
    {
        $params = [
            'adset_id' => $adSetId,
            'name' => $adName,
            'creative' => [
                'creative_id' => $creativeId
            ]
        ];

        return $this->executeRequest('POST', '/'.$adAccountId.'/ads', $params, true);
    }

    /**
     * アカウント情報の取得
     * @return object|null
     */
    public function getAccount()
    {
        $result = null;
        $response = $this->executeRequest('GET', '/me');
        $me = $response->getGraphUser();
        if ($me->getId()) {
            $result = [
                'facebook_id' => $me->getId(),
                'name' => $me->getName(),
                'profile_image' => 'https://graph.facebook.com/' . $me->getId() . '/picture',
                'access_token' => $this->facebook->getDefaultAccessToken()->getValue()
            ];
            return $result;
        }
        return $result;
    }

    /**
     * @param $userId
     * @return \Facebook\FacebookResponse
     */
    public function getPermission($userId)
    {
        return $this->executeRequest('GET', '/'.$userId.'/permissions');
    }

    /**
     * @param string $fields
     * @param int $limit
     * @return \Facebook\FacebookResponse
     */
    public function getAdAccounts($fields = '', $limit = 100)
    {
        return $this->executeRequest('GET', '/me/adaccounts', [
            'fields' => $fields,
            'limit'  => $limit
        ], true);
    }

    /**
     * @param $id
     * @param string $fields
     * @return \Facebook\FacebookResponse
     */
    public function getAdAccountById($id, $fields = '')
    {
        return $this->executeRequest('GET', '/'.$id, [
            'fields' => $fields
        ], true);
    }

    /**
     * @param string $method
     * @param $endpoint
     * @param $params
     * @return FacebookRequest
     */
    public function createRequest($method = "GET", $endpoint, $params)
    {
        return new FacebookRequest(
            $this->facebook->getApp(),
            $this->facebook->getDefaultAccessToken(),
            $method,
            $endpoint,
            $params,
            null,
            $this->facebook->getDefaultGraphVersion()
        );
    }

    /**
     * @param $accountIds
     * @param $params
     * @return array|null
     */
    public function getAccountsInsights($accountIds, $params)
    {
        if (!$accountIds) {
            return null;
        }
        if (!is_array($accountIds)) {
            $accountIds = [$accountIds];
        }

        $batchParams = [];
        foreach ($accountIds as $accountId) {
            $batchParams[] = $this->createRequest("GET", "/act_".$accountId."/insights", $params);
        }

        return $this->sendBatchRequest($batchParams);
    }

    /**
     * @param $ids (campaignIds or adIds or adsetIds ...)
     * @param $params
     * @return array|null
     */
    public function getInsights($ids, $params)
    {
        return $this->getDataByBatchRequest($ids, "/insights", $params);
    }

    /**
     * @param $accountId
     * @param array $params
     * @return array|\Facebook\FacebookResponse|null
     */
    public function getCustomConversions($accountId, $params = ['fields' => 'name'])
    {
        return $this->executeRequest('GET', '/act_'.$accountId.'/customconversions', $params, true);
    }

    /**
     * @param $ids
     * @return array
     */
    public function getCreativeByIds($ids)
    {
        $fields = [
            AdCreativeFields::ID,
            AdCreativeFields::IMAGE_URL,
            AdCreativeFields::BODY,
            AdCreativeFields::OBJECT_STORY_SPEC,
            AdCreativeFields::THUMBNAIL_URL
        ];
        return $this->getDataByBatchRequest($ids, "", ["fields" => implode(',', $fields)]);
    }

    /**
     * @param $ids
     * @param $lastEndPoint
     * @param $params
     * @return array
     */
    public function getDataByBatchRequest($ids, $lastEndPoint, $params)
    {
        if (!$ids) {
            return [];
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $idGroups = array_chunk($ids, self::BATCH_LIMIT);

        $result = [];
        $params["limit"] = 500;
        try {
            foreach ($idGroups as $idGroup) {
                $batchParams = [];
                foreach ($idGroup as $key => $id) {
                    $batchParams[] = $this->createRequest("GET", "/" . $id . $lastEndPoint, $params);
                }
                $response = $this->sendBatchRequest($batchParams);

                $result = array_merge($result, $this->getDataFromBatchResponse($response));
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }

        return $result;
    }

    /**
     * @param $campaignIds
     * @return array
     */
    public function getAdsByCampaignIds($campaignIds)
    {
        $fields = [
            AdFields::ID,
            AdFields::NAME,
            AdFields::CREATIVE,
        ];

        return $this->getDataByBatchRequest($campaignIds, "/ads", ["fields" => implode(',', $fields)]);
    }

    /**
     * @param $campaignIds
     * @param $params
     * @return array
     */
    public function getCampaignsInsights($campaignIds, $params)
    {
        return $this->getDataByBatchRequest($campaignIds, "/insights", $params);
    }

    /**
     * @param $batchResponse
     * @return array
     */
    public function getDataFromBatchResponse($batchResponse)
    {
        $result = [];
        foreach ($batchResponse as $response) {
            $responseBody = json_decode($response["body"], true);
            if (isset($responseBody["data"])) {
                $result = array_merge($result, $responseBody["data"]);
            } elseif (isset($responseBody["error"])) {
                \Log::error($responseBody);
            } else {
                $result[] = $responseBody;
            }
        }
        return $result;
    }

    /**
     * @param $accountId
     * @param $params
     * @return array
     */
    public function getAdsByAccountId($accountId, $params)
    {
        $response = $this->executeRequest("GET", "/act_".$accountId. "/ads", $params, true);

        return $response;
    }

    /**
     * @param $adId
     * @param $params
     * @return array|\Facebook\FacebookResponse|null
     */
    public function getAdInfo($adId, $params)
    {
        return $this->executeRequest("GET", "/".$adId, $params, true);
    }

    /**
     * @param $accountId
     * @param $params
     * @return array
     */
    public function getCampaignsByAccountId($accountId, $params)
    {
        return $this->executeRequest(
            "GET",
            "/".$accountId."/campaigns",
            $params,
            true
        );
    }

    /**
     * @param $campaignIds
     * @param $params
     * @return array
     */
    public function getAdsetsByCampaignId($campaignIds, $params)
    {
        return $this->getDataByBatchRequest($campaignIds, "/adsets", $params);
    }

    /**
     * @param $accountId
     * @param $params
     * @return array
     */
    public function getAdsetsByAccountId($accountId, $params)
    {
        if (!$accountId) {
            return [];
        }

        $result = [];
        $params["limit"] = 500;
        $batchParams[] = $this->createRequest("GET", "/act_".$accountId. "/adsets", $params);

        $response = $this->sendBatchRequest($batchParams);

        $result = array_merge($result, $this->getDataFromBatchResponse($response));

        return $result;
    }

    /**
     * @param MediaAccount $mediaAccount
     * @return bool
     */
    public function setAccessTokenByMediaAccount(MediaAccount $mediaAccount)
    {
        if ($this->isValidAccessToken($mediaAccount->access_token)) {
            $this->accessToken = $mediaAccount->access_token;
            return true;

        } else {
            /** @var MediaAccountService $mediaTokenService */
            $mediaTokenService = app(MediaTokenService::class);
            $mediaTokenService->updateModel([
                'token_expired_flg' => true
            ], $mediaAccount->media_token_id);

            return false;
        }
    }

    /**
     * @param $accountId
     * @param int $limit
     * @return \Facebook\FacebookResponse
     */
    public function getImage($accountId, $limit = 50)
    {
        return $this->executeRequest("GET", "/act_".$accountId. "/adimages", [
            "limit" => $limit,
            "fields" => "hash,url,name,creatives,status,created_time"
        ], true);
    }

    /**
     * @param $accountId
     * @param int $limit
     * @return \Facebook\FacebookResponse
     */
    public function getVideos($accountId, $limit = 50)
    {
        return $this->executeRequest("GET", "/".$accountId. "/advideos", [
            "limit" => $limit,
            "fields" => "source,picture,id,published,created_time,title"
        ], true);
    }

    /**
     * @param $accountId
     * @return array|\Facebook\FacebookResponse|null
     */
    public function getInstagramAccount($accountId)
    {
        return $this->executeRequest("GET", "/".$accountId."/instagram_accounts", [
            "fields" => "id,username,profile_pic"
        ], true);
    }

    /**
     * @param $url
     * @param $adAccountId
     * @return array
     * @throws \Exception
     */
    public function uploadImageFromURL($url, $adAccountId)
    {
        $fullPath = download_file($url,storage_path('images') ,md5($url));
        $fullFileName = last(explode('/', $fullPath));

        $response = $this->executeRequest('POST', '/act_'.$adAccountId.'/adimages', [
            'filename' => new FacebookFile($fullPath)
        ], true);

        \File::delete($fullPath);

        return [
            $response['images'][$fullFileName][AdImageFields::HASH],
            $response['images'][$fullFileName][AdImageFields::URL]
        ];
    }

    /**
     * @param $path
     * @param $adAccountId
     * @return array|null
     */
    public function uploadVideo($path, $adAccountId)
    {
        $video = new FacebookVideo($path);

        if ($video->getSize() < 2000000) {
            //2M以下
            $response = $this->uploadVideoOneStep($video, $adAccountId);
        } else {
            $response = $this->uploadVideoChunked($path, $adAccountId);
        }

        return $response;
    }

    /**
     * @param $url
     * @param $adAccountId
     * @return mixed
     */
    public function uploadVideoFromUrl($url, $adAccountId)
    {
        $path = download_video($url, storage_path('videos'), md5($url));
        $response = $this->uploadVideo($path, $adAccountId);
        \File::delete($path);

        return $response['id'];
    }

    /**
     * @param FacebookVideo $video
     * @param $adAccountId
     * @return array
     */
    public function uploadVideoOneStep(FacebookVideo $video, $adAccountId)
    {
        $response = $this->executeRequest('POST', '/act_'.$adAccountId.'/advideos', [
            'source' => $video
        ], true);

        return $response;
    }

    /**
     * @param $path
     * @param $adAccountId
     * @return array|null
     */
    public function uploadVideoChunked($path, $adAccountId)
    {
        $response = null;
        try {
            $uploader = new FacebookResumableUploader($this->facebook->getApp(), $this->facebook->getClient(), $this->accessToken, $this->graphVersion);
            $endpoint = '/act_'.$adAccountId.'/advideos';
            $file = $this->facebook->videoToUpload($path);
            $chunk = $uploader->start($endpoint, $file);

            do {
                $chunk = $this->maxTriesTransfer($uploader, $endpoint, $chunk, 5);
            } while (!$chunk->isLastChunk());

            return [
                'id' => $chunk->getVideoId(),
                'success' => $uploader->finish($endpoint, $chunk->getUploadSessionId()),
            ];
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            \Log::error($e);
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            \Log::error($e);
        } catch (\Exception $e) {
            \Log::error($e);
        }

        return $response;
    }

    /**
     * Attempts to upload a chunk of a file in $retryCountdown tries.
     *
     * @param FacebookResumableUploader $uploader
     * @param string $endpoint
     * @param FacebookTransferChunk $chunk
     * @param int $retryCountdown
     *
     * @return FacebookTransferChunk
     *
     * @throws FacebookSDKException
     */
    private function maxTriesTransfer(FacebookResumableUploader $uploader, $endpoint, FacebookTransferChunk $chunk, $retryCountdown)
    {
        $newChunk = $uploader->transfer($endpoint, $chunk, $retryCountdown < 1);

        if ($newChunk !== $chunk) {
            return $newChunk;
        }

        $retryCountdown--;

        // If transfer() returned the same chunk entity, the transfer failed but is resumable.
        return $this->maxTriesTransfer($uploader, $endpoint, $chunk, $retryCountdown);
    }
}
