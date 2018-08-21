<?php

namespace App\Http\Controllers;

use App\Exceptions\APIRequestException;
use App\Models\Advertiser;
use App\Repositories\Eloquent\SiteRepository;
use App\Service\AccountSettingService;
use App\Service\AdvertiserInstaAccountService;
use App\Service\AdvertiserService;
use App\Service\HashtagService;
use App\Service\SearchConditionService;
use App\Service\MediaTokenService;
use App\Service\PartService;
use App\UGCConfig;
use Classes\Constants;
use Classes\FacebookGraphClient;
use Classes\TwitterApiClient;
use Illuminate\Http\Request;
use App\Service\InstagramAccountService;
use App\Service\MediaAccountService;
use App\Service\UserService;
use Illuminate\Support\Facades\Auth;
use Classes\InstagramApiClient;
use Illuminate\Support\Facades\Input;
use App\Models\Hashtag;


class AccountSettingController  extends Controller
{
    /**
     * @param Request $request
     * @param InstagramAccountService $instagramAccountService
     * @param PartService $partService
     * @param AdvertiserService $advertiserService
     * @return \Illuminate\Contracts\View\View
     * @throws APIRequestException
     */
    public function view(Request $request, InstagramAccountService $instagramAccountService, PartService $partService, AdvertiserService $advertiserService)
    {
        //base
        $advertiser       = Auth::guard('advertiser')->user();
        $email            = Auth::user()->email;
        $instagramAccount = $instagramAccountService->getInstagramAccountByAdvertiserId($advertiser->id);
        $maxNo            = 0;
        $adContract       = $advertiserService->getActiveContract($advertiser->id, \App\Models\ContractService::FOR_AD);
        $ownedContract    = $advertiserService->getActiveContract($advertiser->id, \App\Models\ContractService::FOR_OWNED);

        // Owned
        if ($ownedContract) {
            $ownedSettings = $partService->findSite($ownedContract->vtdr_site_id);
            $maxNo         = $partService->getCvPagesMaxNo($ownedSettings);
        } else {
            $ownedSettings = [];
        }

        // Ad
        $mediaAccounts = $request->session()->get('mediaAccounts');
        if (!$mediaAccounts) {
            $mediaAccounts = [];
        }
        $request->session()->keep(['mediaAccounts', 'mediaToken']);

        return view()->make(
            'setting.account_setting', [
            'advertiser'       => $advertiser,
            'email'            => $email,
            'adContract'       => $adContract,
            'ownedContract'    => $ownedContract,
            'instagramAccount' => $instagramAccount->first(),
            'mediaAccounts'    => $mediaAccounts,
            'ownedSettings'    => $ownedSettings,
            'maxNo'            => $maxNo
        ]);
    }

    /** 目標設定などOwned系情報の取得
     *
     *
     */
    public function getOwnedInfo($site_id)
    {
        $siteRepository = app(SiteRepository::class, ['siteId' => $site_id]);

        return $siteRepository->find($site_id);
    }

    public function saveEmail(Request $request, UserService $userService){

        $this->validate($request, [
            'email' => 'email'
        ]);

        $user = \Auth::user();

        $userService->updateModel([
            'email' => $request->input('email')
        ], $user->id);

        $request->session()->flash(Constants::INFO_MESSAGE, '保存しました');

        return back();
    }

    // from MediaAccountController @listPage
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function listPage(Request $request)
    {
        $mediaAccounts = $request->session()->get('mediaAccounts');
        if (!$mediaAccounts) {
            $mediaAccounts = [];
        }
        $request->session()->reflash();

        return $mediaAccounts;

    }

    /**
     * @param Request $request
     * @param MediaAccountService $mediaAccountService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetList(Request $request, MediaAccountService $mediaAccountService)
    {
        $advertiser = \Auth::guard('advertiser')->user();
        $mediaAccounts = $mediaAccountService->getWhere([
            'advertiser_id' => $advertiser->id
        ], false, ['created_at', 'desc']);

        $request->session()->reflash();

        return response()->json($mediaAccounts->toArray(), 200, [], JSON_NUMERIC_CHECK);
    }
    
    /**
     * @param Request $request
     * @param InstagramAccountService $instagramAccountService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiChangeAdvertiserCrawlPostsSetting(Request $request, InstagramAccountService $instagramAccountService)
    {
        
        /** @var AdvertiserService $advertiserService */
        $advertiserService = app(AdvertiserService::class);
        /** @var SearchConditionService $searchConditionService */
        $searchConditionService = app(SearchConditionService::class);
        /** @var HashtagService $hashtagService */
        $hashtagService = app(HashtagService::class);
        try{
            $advertiser = \Auth::guard('advertiser')->user();
            $crawlPostSetting = $request->get('crawlPostSetting') ? Advertiser::SETTING_VALUE_YES : Advertiser::SETTING_VALUE_NO;
            $advertiserData = array(
                "is_crawl_own_post" => $crawlPostSetting
            );
            $advertiserService->updateModel($advertiserData, $advertiser->id);
            $instagramAccounts = $instagramAccountService->getInstagramAccountByAdvertiserId($advertiser->id);

            foreach ($instagramAccounts as $instagramAccount) {
                if (!$instagramAccount->username) {
                    \Log::error('Instagram usernameがない instagramAccountId: ' . $instagramAccount->id);
                    return response()->json([
                        'errors' => [Constants::TOASTR_ERROR => 'Instagramのアカウント情報に問題があるため、再度Instagram連携を行ってください。']],
                        400
                    );
                    continue;
                }
    
                if($crawlPostSetting) {
                    $searchConditionService->createSearchCondition($advertiser->id, [$instagramAccount->username], "@", $crawlPostSetting, false, Hashtag::TYPE_USER);
                    $hashtag = $hashtagService->findBy('hashtag', $instagramAccount->username);
                    if (!$hashtag) {
                        continue;
                    }
                    $searchConditionService->realTimeCrawlAccount($instagramAccount->id);
                } else {
                    $hashtag = $hashtagService->findBy('hashtag', $instagramAccount->username);
                    if (!$hashtag) {
                        continue;
                    }
                    $searchCondition = $searchConditionService->getSearchConditionByHashtagIdAndAdvertiserId($hashtag->id, $advertiser->id);
                    $searchConditionService->deleteSearchCondition($searchCondition->id, $advertiser->id);
                }
            }

            return response()->json($advertiser->toArray(), 200, [], JSON_NUMERIC_CHECK);
        }catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'errors' => [Constants::TOASTR_ERROR => '更新できませんでした。']],
                400
            );
        }
        
    }

    /**
     * Facebook callback
     *
     * @param Request $request
     * @param MediaTokenService $mediaTokenService
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function createFbMediaAccountPage(Request $request, MediaTokenService $mediaTokenService)
    {
        $facebook = FacebookGraphClient::getInstant();
        try {
            if ($request->get('code')) {
                $facebook->setAccessTokenFromCode(\URL::route('create_media_account_page'));
                $fbAccount = $facebook->getAccount();
                if (!$fbAccount) {
                    $request->session()->flash(Constants::ERROR_MESSAGE, 'Facebook連携に失敗しました');
                    return redirect()->route('account_setting');
                }

                $mediaToken = $mediaTokenService->createOrUpdate(
                    ['media_account_id' => $fbAccount['facebook_id']],
                    [
                        'media_account_id' => $fbAccount['facebook_id'],
                        'media_type' => Constants::MEDIA_FACEBOOK
                    ],
                    [
                        'access_token' => $fbAccount['access_token'],
                        'refresh_token' => '',
                        'token_expired_flg' => false
                    ]
                );

                $fbAdsAccounts = $facebook->getAdAccounts('id,name,account_id,business', 1000);

                if (!$fbAdsAccounts) {
                    $request->session()->flash(Constants::ERROR_MESSAGE, '広告アカウントが取得できません');
                    return back();
                }

                $fbAdsAccounts = isset($fbAdsAccounts['data']) ? $fbAdsAccounts['data'] : [];

                foreach ($fbAdsAccounts as $index => $fbAccount) {
                    $fbAdsAccounts[$index]['id'] = $fbAccount['account_id'];
                }

                // save crawled information in to flash session
                $request->session()->flash('mediaToken', $mediaToken->toArray());
                $request->session()->flash('mediaAccounts', $fbAdsAccounts);

            } else {
                $request->session()->flash(Constants::ERROR_MESSAGE, 'Facebook連携に失敗しました');
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }

        return redirect()->route('account_setting');
    }

    /**
     * @param Request $request
     * @param MediaAccountService $mediaAccountService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createMediaAccount(Request $request, MediaAccountService $mediaAccountService)
    {
        $this->validate($request, [
            'media_account_id' => 'required'
        ]);
        $advertiser = \Auth::guard('advertiser')->user();
        $selectedMediaId = $request->get('media_account_id');
        $mediaToken = $request->session()->get('mediaToken');
        $mediaAccounts = $request->session()->get('mediaAccounts');

        foreach ($mediaAccounts as $mediaAccountInfo) {
            if ($mediaAccountInfo['id'] != $selectedMediaId) {
                continue;
            }
            $mediaAccount = $mediaAccountService->findBy('media_account_id', $mediaAccountInfo['id']);
            if ($mediaAccount) {
                $request->session()->flash(Constants::ERROR_MESSAGE, '選択したアカウントは既に連携されています');
                return redirect()->route('account_setting');
            }
            $mediaAccountService->createOrUpdate(
                ['media_account_id' => $mediaAccountInfo['id']],
                [
                    'media_token_id' => $mediaToken['id'],
                    'advertiser_id' => $advertiser->id,
                    'media_type' => $mediaToken['media_type'],
                    'media_account_id' => $mediaAccountInfo['id']
                ],
                ['name' => $mediaAccountInfo['name']]);
            break;
        }

        return redirect()->route('account_setting');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginWithTwitter(Request $request)
    {
        $twitterClient = TwitterApiClient::createInstance();
        $requestToken = $twitterClient->getRequestToken(\URL::route('media_account_tw_callback'));
        $request->session()->flash('requestToken', $requestToken);
        $loginUrl = $twitterClient->getAuthorizeURL($requestToken);

        return redirect()->to($loginUrl);
    }

    /**
     *
     */
    public function loginWithFacebook()
    {
        $callback = \URL::route('create_media_account_page');
        $scopes = UGCConfig::get('facebook.scope');
        $url = FacebookGraphClient::getInstant()->getLoginUrl($callback, $scopes);

        return redirect()->to($url);
    }

    /**
     * Twitter callback
     *
     * @param Request $request
     * @param MediaTokenService $mediaTokenService
     * @return \Illuminate\Contracts\View\View
     */
    public function createTwMediaAccountPage(Request $request, MediaTokenService $mediaTokenService)
    {
        $oauthToken = $request->get('oauth_token');
        $oauthVerifier = $request->get('oauth_verifier');
        if (!$oauthToken || !$oauthVerifier) {
            $request->session()->flash(Constants::ERROR_MESSAGE, 'Twitter連携に失敗しました');
            return redirect()->route('account_setting');
        }
        $requestToken = $request->session()->get('requestToken');
        $twitterClient = TwitterApiClient::createInstance($oauthToken, $requestToken['oauth_token_secret']);

        $twAccount = $twitterClient->getAccessToken($oauthVerifier);

        if (!isset($twAccount['user_id'])) {
            $request->session()->flash(Constants::ERROR_MESSAGE, 'Twitter連携に失敗しました');
            return redirect()->route('account_setting');
        }

        $mediaToken = $mediaTokenService->createOrUpdate(
            ['media_account_id' => $twAccount['user_id']],
            [
                'media_account_id' => $twAccount['user_id'],
                'media_type' => Constants::MEDIA_TWITTER
            ],
            [
                'access_token' => $twAccount['oauth_token'],
                'refresh_token' => $twAccount['oauth_token_secret'],
                'token_expired_flg' => false
            ]
        );
        $twitterClient->setOauthToken($twAccount['oauth_token'], $twAccount['oauth_token_secret']);
        $twAccounts = $twitterClient->getAccounts(['count' => 1000]);

        $mediaAccounts = [];
        foreach ($twAccounts as $twAccount) {
            $mediaAccounts[] = $twAccount->toArray();
        }

        // save crawled information in to flash session
        $request->session()->flash('mediaToken', $mediaToken->toArray());
        $request->session()->flash('mediaAccounts', $mediaAccounts);

        return redirect()->route('account_setting');
    }

    //    TODO 更新後の表示反映
    public function saveSiteCVPage(Request $request, PartService $partService){
        $this->validate($request, [
            'cv-pages' => 'required|array',
        ]);

        $data = Input::get('cv-pages');

        try {
            $result = $partService->createCvTargetPages($data);

            $request->session()->flash('currentTab', 'owned');
            if ($result['result'] == 'ok') {
                $request->session()->flash(Constants::INFO_MESSAGE, '目標設定を削除しました');

                return back();
            }
            $request->session()->flash(Constants::ERROR_MESSAGE, '目標設定の削除に失敗しました');

            return back();
        } catch (\Exception $e) {
            \Log::error($e);
            $request->session()->flash('currentTab', 'owned');
            $request->session()->flash(Constants::ERROR_MESSAGE, '目標設定の更新に失敗しました');

            return back();
        }

        return back();
    }

//    TODO 更新後の表示反映
    public function saveExcludeIPAddress(Request $request, PartService $partService)
    {
        $this->validate($request, [
            'excludeAddresses' => 'string'
        ]);
        $addresses = $request->get('excludeAddresses');

        try {
            $response = $partService->createExcludeAddresses($addresses);

            $request->session()->flash('currentTab', 'owned');
            if ($response['result'] == 'ok') {
                $request->session()->flash(Constants::INFO_MESSAGE, '除外IPアドレスを更新しました');
                return redirect()->route('account_setting');
            }
            $request->session()->flash(Constants::ERROR_MESSAGE, '除外IPアドレスの更新に失敗しました');

            return back();
        } catch (\Exception $e) {
            \Log::error($e);
            $request->session()->flash('currentTab', 'owned');
            $request->session()->flash(Constants::ERROR_MESSAGE, '除外アドレスの更新に失敗しました');

            return back();
        }

        return back();
    }

    /**
     * @param Request $request
     */
    public function auth(Request $request)
    {
        (new InstagramApiClient($request->get('redirect')))->goLoginUrl();
    }

    /**
     * @param Request $request
     * @param InstagramAccountService $instagramAccountService
     * @param AdvertiserInstaAccountService $advertiserInstaAccountService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request, InstagramAccountService $instagramAccountService,
                             AdvertiserInstaAccountService $advertiserInstaAccountService)
    {
        try {
            $api = new InstagramApiClient($request->get('redirect'));
            $user = $api->login($request->get('code'));
            $name = $user->getFullName();
            $userName = $user->getUserName();
            $account = $instagramAccountService->createOrUpdate(
                ['instagram_id' => $user->getId()],
                ['instagram_id' => $user->getId()],
                [
                    'name' => $name ? $name : $userName,
                    'username' => $userName,
                    'profile_image' => $user->getProfilePicture(),
                    'access_token' => $api->getToken()
                ]);

            $advertiser = Auth::guard('advertiser')->user();
            $relationAttributes = [
                'advertiser_id' => $advertiser->id,
                'instagram_account_id' => $account->id
            ];
            $advertiserInstaAccountService->createOrUpdate($relationAttributes, $relationAttributes);

        } catch (\Exception $e) {
            \Log::error($e);
            $request->session()->flash(Constants::ERROR_MESSAGE, 'Instagram連携に失敗しました');
        }

        $redirect = $request->get('redirect');
        if ($redirect) {
            return \Redirect::to($redirect);
        } else {
            return back();
        }
    }

    /**
     * @param AdvertiserInstaAccountService $advertiserInstaAccountService
     * @param $instagramAccountId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disconnect(AdvertiserInstaAccountService $advertiserInstaAccountService, $instagramAccountId)
    {
        $advertiser = Auth::guard('advertiser')->user();
        $relation = $advertiserInstaAccountService->findByAdvertiserIdAndIgAccountId($advertiser->id, $instagramAccountId);
        $advertiserInstaAccountService->deleteModel($relation->id);

        return redirect()->route('account_setting');
    }
}