<?php


namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Service\InvitationCodeService;
use App\Service\MediaAccountService;
use App\Service\MediaTokenService;
use App\Service\SnsAccountService;
use App\Service\UserService;
use App\UGCConfig;
use \Auth;
use Classes\Constants;
use Classes\FacebookGraphClient;
use Illuminate\Http\Request;
use Illuminate\Log;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * ログインページ
     */
    public function loginPage(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            /** @var User $user */
            $user = Auth::guard('admin')->user();
            if ($user->isEgcStaff()) {
                return redirect()->route('offer_comments');
            }
            return redirect()->route('admin_dashboard');
        }

        if ($request->get('code')) {
            $request->session()->flash('code', $request->get('code'));
        }

        $callback = \URL::route('admin_fb_callback');
        $scopes = UGCConfig::get('facebook.scope');

        return view('user.login', [
            'login_link' => FacebookGraphClient::getInstant()->getLoginUrl($callback, $scopes)
        ]);
    }

    /**
     * @param Request $request
     * @param InvitationCodeService $invitationCodeService
     * @param SnsAccountService $snsAccountService
     * @param UserService $userService
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function fbCallback(Request $request, InvitationCodeService $invitationCodeService, SnsAccountService $snsAccountService, UserService $userService)
    {
        try {
            FacebookGraphClient::getInstant()->setAccessTokenFromCode(\URL::route('admin_fb_callback'));
            $facebook = FacebookGraphClient::getInstant();
            $fbAccount = $facebook->getAccount();
            if (!$fbAccount) {
                throw new \Exception('Facebook Loginに失敗しました。');
            }
            $fbAccount['media_user_id'] = $fbAccount['facebook_id'];
            $fbAccount['profile_img_url'] = $fbAccount['profile_image'];
            $fbAccount['token_expired_flg'] = false;
            unset($fbAccount['facebook_id']);
            unset($fbAccount['profile_image']);

            $snsAccount = $snsAccountService->getSnsAccountByMediaUserId($fbAccount['media_user_id']);

            if (!$snsAccount) {
                $inviteCode = $request->session()->get('code');
                \Log::info('invite code: '.$inviteCode);
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

            $user = $userService->findModel($snsAccount->user_id);
            if (!$user || !$user->isAdmin()) {
                \Log::error('Adminに不明のアクセスがありました。 user Id: '.$snsAccount->user_id);
                \Session::flash(Constants::ERROR_MESSAGE, 'ログインに失敗しました。');
                return back();
            }
            // update admin info
            $userService->updateModel([
                'user_name' => $fbAccount['name'],
                'profile_img_url' => $fbAccount['profile_img_url']
            ], $user->id);

            Auth::guard('admin')->login($user);

            /** @var User $user */
            $user = Auth::guard('admin')->user();
            if ($user->isEgcStaff()) {
                return redirect()->route('offer_comments');
            }
            return redirect()->route('admin_dashboard');

        } catch (\Exception $e) {
            \Log::error($e);
            \Session::flash(Constants::ERROR_MESSAGE, $e->getMessage());
            return back();
        }
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect('/admin/login');
    }
}
