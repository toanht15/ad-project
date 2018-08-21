<?php


namespace App\Http\Controllers;

use App\Repositories\Eloquent\AdvertiserRepository;
use App\Service\AdvertiserService;
use App\Service\ContractService;
use App\Service\InvitationCodeService;
use App\Service\MediaAccountService;
use App\Service\MediaTokenService;
use App\Service\PartService;
use App\Service\SnsAccountService;
use App\Service\UserService;
use App\UGCConfig;
use \Auth;
use Classes\Constants;
use Classes\FacebookGraphClient;
use Classes\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class LoginController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|View
     */
    public function loginPage(Request $request)
    {
        // check user account login
        if (Auth::check()) {
            // check advertiser account login
            if (!Auth::guard('advertiser')->check()) {
                return redirect()->route('select_advertiser');
            }

            return redirect()->route('dashboard');
        }

        if ($request->get('code')) {
            $request->session()->flash('code', $request->get('code'));
        }

        $callback = \URL::route('fb_callback');
        $scopes = UGCConfig::get('facebook.scope');

        return view('user.login', [
            'login_link' => FacebookGraphClient::getInstant()->getLoginUrl($callback, $scopes)
        ]);
    }

    /**
     * @param Request $request
     * @param SnsAccountService $snsAccountService
     * @param InvitationCodeService $invitationCodeService
     * @param UserService $userService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function fbCallback(Request $request, SnsAccountService $snsAccountService, InvitationCodeService $invitationCodeService, UserService $userService)
    {
        try {
            $facebook = FacebookGraphClient::getInstant();

            if ($request->get('code')) {
                $facebook->setAccessTokenFromCode(\URL::route('fb_callback'));
                $fbAccount = $facebook->getAccount();
                if (!$fbAccount) {
                    throw new \Exception('Facebook Loginに失敗しました');
                }
                // match FB data with database columns
                $fbAccount['media_user_id'] = $fbAccount['facebook_id'];
                $fbAccount['profile_img_url'] = $fbAccount['profile_image'];
                $fbAccount['token_expired_flg'] = false;
                unset($fbAccount['facebook_id']);
                unset($fbAccount['profile_image']);

                $snsAccount = $snsAccountService->getSnsAccountByMediaUserId($fbAccount['media_user_id']);

                if (!$snsAccount) {
                    $inviteCode = $request->session()->get('code');
                    \Log::info('invite code: ' . $inviteCode);
                    $code = $invitationCodeService->getValidCode($inviteCode);
                    $fbAccount['media_type'] = Constants::MEDIA_FACEBOOK;
                    $fbAccount['user_id'] = $code->user_id;
                    $snsAccount = $snsAccountService->createModel($fbAccount);
                    $invitationCodeService->updateModel(['is_used_flg' => true], $code->id);
                } else {
                    // update access token
                    $snsAccountService->updateModel($fbAccount, $snsAccount->id);
                    // update media token which has same fb id
                    /** @var MediaAccountService $mediaTokenService */
                    $mediaTokenService = app(MediaTokenService::class);
                    $mediaTokenService->updateModel([
                        'access_token' => $fbAccount['access_token'],
                        'token_expired_flg' => false
                    ], $fbAccount['media_user_id'], 'media_account_id');
                }
            } else {
                \Log::error('Facebook コードがなかった');
                $request->session()->flash(Constants::ERROR_MESSAGE, 'ログインに失敗しました');
                return back();
            }

            $user = $userService->findModel($snsAccount->user_id);
            if (!$user) {
                \Log::error('ユーザーが見つかりませんでした');
                $request->session()->flash(Constants::ERROR_MESSAGE, 'ログインに失敗しました');
                return back();
            }
            // update admin info
            $userService->updateModel([
                'user_name' => $fbAccount['name'],
                'profile_img_url' => $fbAccount['profile_img_url']
            ], $user->id);

            Auth::login($user);

            return redirect()->route('select_advertiser');
        } catch (\Exception $e) {
            \Log::error($e);
            $request->session()->flash(Constants::ERROR_MESSAGE, $e->getMessage());

            return back();
        }
    }

    /**
     * @param Request $request
     * @param AdvertiserService $advertiserService
     * @param ContractService $contractService
     * @param PartService $partService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setAdAccount(Request $request, AdvertiserService $advertiserService)
    {
        $advertiserId = $request->input('advertiser_id');

        if (!$advertiserId) {
            $request->session()->flash(Constants::ERROR_MESSAGE, '広告主を選択してください');
            return back();
        }

        $advertiser = $advertiserService->findModel($advertiserId);
        $user = Auth::user();
        // check permission
        if (!$user->can(Roles::PERMISSION_VIEW, $advertiser)) {
            \Log::error('Invalid access userId: ' . $user->id . ' advertiserId: ' . $advertiser->id);
            \Session::flash(Constants::ERROR_MESSAGE, 'ログインに失敗しました');
            return back();
        }

        if ($advertiser) {
            Auth::guard('advertiser')->login($advertiser);
            $advertiser->last_login = (new \DateTime())->format('Y-m-d H:i:s');
            $advertiser->save();

            try {
                // check contract info
                $info = $advertiserService->getContractInfo($advertiserId);
                $canUseAds = $info['adsContract'] ? true : false;
                $canUsePost = $info['post'] ? true : false;

                \Session::put('post', $canUsePost);

                \Session::put('canUseAds', $canUseAds);
                if (isset($info['site']) && isset($info['isOwnedFirst'])) {
                    \Session::put('site', $info['site']);
                    \Session::put('isOwnedFirst', $info['isOwnedFirst']);
                }
            } catch (\Exception $e) {
                \Log::error($e);
            }

            if (\Session::get('redirect_url')) {
                $redirectUrl = \Session::get('redirect_url');
                \Session::remove('redirect_url');
                return redirect()->to($redirectUrl);
            }

            if ($canUsePost && !($canUseAds || isset($info['site']))) {
                return Redirect::to(env('POST_DOMAIN'));
            }
            return redirect()->route('dashboard');
        }

        return back();
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function logout()
    {
        if (\Session::has('site')) {
            \Session::forget('site');
        }
        if (\Session::has('canUseAds')) {
            \Session::forget('canUseAds');
        }
        if (\Session::has('post')) {
            \Session::forget('post');
        }

        Auth::guard('advertiser')->logout();
        Auth::logout();
        return redirect()->route('advertiser_login');
    }

    /**
     * @param AdvertiserRepository $advertiserRepository
     * @return View
     */
    public function selectAdvertiser(AdvertiserRepository $advertiserRepository)
    {
        $user = Auth::user();
        $advertisers = $advertiserRepository->getAdvertiserByUserId($user->id);
        $advertisers = ($advertisers && $advertisers->count()) ? $advertisers->toArray() : [];

        return view('user.select_advertiser', ['advertiserList' => $advertisers, 'loginPage' => true]);
    }
}
