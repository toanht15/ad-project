<?php


namespace Classes;


use App\UGCConfig;
use Hborras\TwitterAdsSDK\TwitterAds;

class TwitterApiClient extends TwitterAds {

    public static function createInstance($oauthToken = '', $oauthTokenSecret = '', $accountId = '')
    {
        $consumerKey = UGCConfig::get('twitter.consumerKey');
        $consumerSecret = UGCConfig::get('twitter.consumerSecret');

        return self::init($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret, $accountId);
    }

    /**
     * @param $url
     * @param $twUserId
     * @param $promotableUserId
     * @return mixed
     */
    public function uploadImageFromURL($url, $twUserId, $promotableUserId)
    {
        $fullPath = download_file($url,storage_path('images') ,md5($url));

        $response = $this->upload([
            'media' => $fullPath,
            'media_type' => 'image/jpeg',
            'additional_owners' => [$twUserId, $promotableUserId]
        ], false);

        \File::delete($fullPath);

        return $response->media_id;
    }

    /**
     * @param $url
     * @param $twUserId
     * @param $promotableUserId
     * @return mixed
     */
    public function uploadVideoFromUrl($url, $twUserId, $promotableUserId)
    {
        $fullPath = download_video($url, storage_path('videos'), md5($url));

        $response = $this->upload([
            'media' => $fullPath,
            'media_type' => 'video/mp4',
            'additional_owners' => [$twUserId, $promotableUserId]
        ], true);

        \File::delete($fullPath);

        return $response->media_id;
    }

    /**
     * @param $url
     * @param $twUserId
     * @param $promotableUserId
     * @return TwitterAds\Resource
     */
    public function createVideoFromUrl($url, $twUserId, $promotableUserId)
    {
        $mediaId = $this->uploadVideoFromUrl($url, $twUserId, $promotableUserId);
        $video = new TwitterAds\Creative\Video();
        $video->setVideoMediaId($mediaId);

        return $video->save();
    }

    /**
     * @param TwitterAds\Account $account
     * @param $mediaId
     * @param $tweet
     * @return mixed
     */
    public function createTweet(TwitterAds\Account $account, $mediaId, $tweet, $promotableUserId)
    {
        return TwitterAds\Campaign\Tweet::create($account, $tweet, ['media_ids' => $mediaId, 'as_user_id' => $promotableUserId, 'text' => $tweet]);
    }

    /**
     * @param $name
     * @param $mediaId
     * @return TwitterAds\Resource
     */
    public function createImageAppDownloadCard($name, $mediaId)
    {
        $imageAppDownloadCard = new TwitterAds\Creative\ImageAppDownloadCard();
        $imageAppDownloadCard->setName($name);
        $imageAppDownloadCard->setAppCountryCode('JP');
        $imageAppDownloadCard->setIphoneAppId('333903271');
        $imageAppDownloadCard->setWideAppImageMediaId($mediaId);

        return $imageAppDownloadCard->save();
    }

    /**
     * @param $name
     * @param $mediaId
     * @return TwitterAds\Resource
     */
    public function createVideoAppDownloadCard($name, $mediaId)
    {
        $videoAppDownloadCard = new TwitterAds\Creative\VideoAppDownloadCard();
        $videoAppDownloadCard->setName($name);
        $videoAppDownloadCard->setAppCountryCode('JP');
        $videoAppDownloadCard->setIphoneAppId('333903271');
        $videoAppDownloadCard->setVideoId($mediaId);

        return $videoAppDownloadCard->save();
    }

    /**
     * @param $name
     * @param $mediaId
     * @return TwitterAds\Resource
     */
    public function createImageConversationCard($name, $mediaId)
    {
        $imageConversionCard = new TwitterAds\Creative\ImageConversationCard();
        $imageConversionCard->setFirstCta('#ShareNow');
        $imageConversionCard->setFirstCtaTweet('share now');
        $imageConversionCard->setImageMediaId($mediaId);
        $imageConversionCard->setName($name);
        $imageConversionCard->setThankYouText('Thank you');
        $imageConversionCard->setTitle('title');

        return $imageConversionCard->save();
    }

    /**
     * @param $name
     * @param $mediaId
     * @return TwitterAds\Resource
     */
    public function createVideoConversationCard($name, $mediaId)
    {
        $videoConversionCard = new TwitterAds\Creative\VideoConversationCard();
        $videoConversionCard->setFirstCta('#ShareNow');
        $videoConversionCard->setFirstCtaTweet('share now');
        $videoConversionCard->setVideoId($mediaId);
        $videoConversionCard->setName($name);
        $videoConversionCard->setThankYouText('Thank you');
        $videoConversionCard->setTitle('title');

        return $videoConversionCard->save();
    }

    /**
     * @param $name
     * @param $mediaId
     * @return TwitterAds\Resource
     */
    public function createWebsiteCard($name, $mediaId)
    {
        $websiteCard = new TwitterAds\Creative\WebsiteCard();
        $websiteCard->setName($name);
        $websiteCard->setImageMediaId($mediaId);
        $websiteCard->setWebsiteUrl('https://www.letro.jp');
        $websiteCard->setWebsiteTitle('Letro');

        return $websiteCard->save();
    }

    /**
     * @param $name
     * @param $mediaId
     * @return TwitterAds\Resource
     */
    public function createVideoWebsiteCard($name, $mediaId)
    {
        $websiteCard = new TwitterAds\Creative\VideoWebsiteCard();
        $websiteCard->setName($name);
        $websiteCard->setVideoId($mediaId);
        $websiteCard->setWebsiteUrl('https://www.letro.jp');
        $websiteCard->setTitle('Letro');

        return $websiteCard->save();
    }

    /**
     * @param $adAccountId
     * @return TwitterAds\Account
     */
    public function getAccount($adAccountId)
    {
        return new TwitterAds\Account($adAccountId);
    }

    /**
     * @return TwitterAds\Campaign\FundingInstrument
     */
    public function getFundingInstrument()
    {
        return (new TwitterAds\Campaign\FundingInstrument())->all();
    }

    /**
     * @return TwitterAds\Cursor
     */
    public function getPromotableUser()
    {
        return (new TwitterAds\Campaign\PromotableUser())->all();
    }

    /**
     * @param array $params
     * @return TwitterAds\Cursor
     */
    public function getPromotedTweet($params = [])
    {
        return (new TwitterAds\Creative\PromotedTweet())->all($params);
    }

    /**
     * @param $tweetId
     * @return \Hborras\TwitterAdsSDK\Response
     */
    public function getTweet($tweetId)
    {
        return $this->http('GET', self::API_HOST_OAUTH, 'statuses/show.json', ['id' => $tweetId]);
    }

    /**
     * @param $jobIds
     * @return TwitterAds\Cursor
     */
    public function getJobs($jobIds)
    {
        return (new TwitterAds\Analytics\Job())->all(['job_ids' => $jobIds]);
    }

    /**
     * @param $adAccountId
     * @param $twUserId
     * @param $mediaUrl
     * @param $tweet
     * @param $creativeType
     * @param bool $isVideo
     * @return mixed
     */
    public function uploadMaterial($adAccountId, $twUserId, $mediaUrl, $tweet, $creativeType, $isVideo = false)
    {
        $promotableUser = $this->getPromotableUser();
        $promotableUserId = $promotableUser[0]->getUserId();

        if ($isVideo) {
            $video = $this->createVideoFromUrl($mediaUrl, $twUserId, $promotableUserId);
            if ($creativeType == Constants::TW_LINK_TWEET) {
                $mediaId = $video->getVideoMediaId();
            } else {
                $mediaId = $video->getId();
            }
        } else {
            $mediaId = $this->uploadImageFromURL($mediaUrl, $twUserId, $promotableUserId);
        }
        $account = $this->getAccount($adAccountId);
        switch ($creativeType) {
            case Constants::TW_LINK_TWEET:
                $tweet = $this->createTweet($account, $mediaId, $tweet, $promotableUserId);
                $id = $tweet->id;
                break;
            case Constants::TW_CARD_IMG_APP_DOWNLOAD:
                $card = $this->createImageAppDownloadCard($tweet, $mediaId);
                $id = $card->getId();
                break;
            case Constants::TW_CARD_IMG_CONVERSION:
                $card = $this->createImageConversationCard($tweet, $mediaId);
                $id = $card->getId();
                break;
            case Constants::TW_CARD_WEBSITE:
                $card = $this->createWebsiteCard($tweet, $mediaId);
                $id = $card->getId();
                break;
            case Constants::TW_CARD_VIDEO_WEBSITE:
                $card = $this->createVideoWebsiteCard($tweet, $mediaId);
                $id = $card->getId();
                break;
            case Constants::TW_CARD_VIDEO_APP_DOWNLOAD:
                $card = $this->createVideoAppDownloadCard($tweet, $mediaId);
                $id = $card->getId();
                break;
            case Constants::TW_CARD_VIDEO_CONVERSION:
                $card = $this->createVideoConversationCard($tweet, $mediaId);
                $id = $card->getId();
                break;
            default:
                $tweet = $this->createTweet($account, $mediaId, $tweet, $promotableUserId);
                $id = $tweet->id;
                break;
        }

        return $id;
    }
}