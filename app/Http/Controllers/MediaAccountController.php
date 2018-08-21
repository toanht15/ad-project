<?php


namespace App\Http\Controllers;


use App\Service\MediaAccountService;
use App\Service\MediaTokenService;
use App\UGCConfig;
use Classes\Constants;
use Classes\FacebookGraphClient;
use Classes\TwitterApiClient;
use Illuminate\Http\Request;

class MediaAccountController extends Controller {

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

        return view()->make('media_account.list', [
            'mediaAccounts' => $mediaAccounts,
            'advertiser' => \Auth::guard('advertiser')->user()
        ]);
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
                    return redirect()->route('media_account_list');
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

        return redirect()->route('media_account_list');
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
                return redirect()->route('media_account_list');
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

        return redirect()->route('media_account_list');
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
            return redirect()->route('media_account_list');
        }
        $requestToken = $request->session()->get('requestToken');
        $twitterClient = TwitterApiClient::createInstance($oauthToken, $requestToken['oauth_token_secret']);

        $twAccount = $twitterClient->getAccessToken($oauthVerifier);

        if (!isset($twAccount['user_id'])) {
            $request->session()->flash(Constants::ERROR_MESSAGE, 'Twitter連携に失敗しました');
            return redirect()->route('media_account_list');
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

        return redirect()->route('media_account_list');
    }
}