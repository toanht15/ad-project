<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tenant;
use App\Service\AdvertiserService;
use App\Service\SnsAccountService;
use App\Service\TenantService;
use App\Service\UserService;
use Classes\Constants;
use Classes\Roles;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function listPage()
    {
        /** @var UserService $userService */
        $userService = app(UserService::class);
        /** @var TenantService $tenantService */
        $tenantService = app(TenantService::class);
        /** @var AdvertiserService $advertiserService */
        $advertiserService = app(AdvertiserService::class);
        $users = $userService->all();
        $tenants = $tenantService->all();
        $advertisers = $advertiserService->all();

        return \View::make('admin.user_list', [
            'userList' => $users,
            'tenantList' => $tenants,
            'advertiserList' => $advertisers,
            'roles' => Roles::getAdvertiserRoles()
        ]);
    }

    /**
     * @param Request $request
     * @param UserService $userService
     * @param SnsAccountService $snsAccountService
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Request $request, UserService $userService,SnsAccountService $snsAccountService, $id)
    {
        try {
            $userService->deleteModel($id);
            $snsAccountService->deleteBy('user_id', $id);
            $request->session()->flash(Constants::INFO_MESSAGE, 'アカウントを削除しました。');
        } catch (\Exception $e) {
            $request->session()->flash(Constants::ERROR_MESSAGE, '削除に失敗しました。');
        }

        return back();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function invite(Request $request)
    {
        $validate = [
            'tenant_id' => 'required',
            'advertiser_id' => 'required',
            'role' => 'required'
        ];
        $userId = $request->input('user_id');
        if (!$userId) {
            $validate['email'] = 'required|email';
            $validate['name'] = 'required';
        }

        $this->validate($request, $validate);

        $email = $request->input('email');
        $tenantId = $request->input('tenant_id');
        $advertiserId = $request->input('advertiser_id');
        $role = $request->input('role');
        $name = $request->input('name');
        $admin = \Auth::guard('admin')->user();

        /** @var UserService $userService */
        $userService = app(UserService::class);
        /** @var TenantService $tenantService */
        $tenantService = app(TenantService::class);

        if (!in_array($role, Roles::getAdvertiserRoles())) {
            abort(404);
        }

        try {
            $tenant = $tenantService->findModel($tenantId);
            $userData = [
                'id' => $userId,
                'email' => $email,
                'user_name' => $name,
                'tenant_id' => $tenantId
            ];
            $invitationCodeData = [
                'invited_email' => $email,
                'created_user_id' => $admin->id
            ];

            $user = null;
            if ($email) {
                $user = $userService->findBy('email', $email);
            }

            list ($newUser, $newInvitationCode) = $userService->createUserWithInvitationCode($userData, $invitationCodeData, $advertiserId, $role);

            if (!$userId && !$user) {
                // 新ユーザーのみに招待メールを送る
                \Mail::send('emails.invite_user', ['code' => $newInvitationCode->code, 'tenant' => $tenant, 'name' => $name], function ($message) use ($email) {
                    $message->to($email)->cc(env('INVITE_MAIL_CC'))->subject('【Letro／アライドアーキテクツ】管理画面へのご招待');
                });
                $request->session()->flash(Constants::INFO_MESSAGE, '招待メールを送信しました');
            } else {
                $request->session()->flash(Constants::INFO_MESSAGE, 'アドバタイザーにアクセス権限を設定しました');
            }
        } catch (\Exception $e) {
            \Log::error('AdAccountController invite false '.$email);
            \Log::error($e);
            $request->session()->flash(Constants::ERROR_MESSAGE, '招待メールを送信に失敗しました');
        }

        return back();
    }

    /**
     * @param Request $request
     * @param UserService $userService
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginAsUser(Request $request,UserService $userService, $userId)
    {
        if (\Auth::guard('advertiser')->check()) {
            \Auth::guard('advertiser')->logout();
        }
        if (\Auth::check()) {
            \Auth::logout();
        }
        $user = $userService->findModel($userId);
        \Auth::login($user);

        return redirect()->route('select_advertiser');
    }
}
