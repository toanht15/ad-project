<?php

namespace App\Http\Controllers\Admin;
use App\Service\SnsAccountService;
use App\Service\UserService;
use Classes\Constants;
use Classes\Roles;
use Illuminate\Http\Request;
use App\Models\CommentTemplate;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    /**
     * @param UserService $userService
     * @return \Illuminate\Contracts\View\View
     */
    public function adminListPage(UserService $userService)
    {
        $adminList = $userService->getWhere(['role' => Roles::ADMIN]);

        return \View::make('admin.admin_list', ['adminList' => $adminList]);
    }

    /**
     * @param Request $request
     * @param UserService $userService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function invite(Request $request, UserService $userService)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        $email = $request->input('email');
        $admin = \Auth::guard('admin')->user();

        try {
            $userData = [
                'email' => $email,
                'tenant_id' => 1 // allied
            ];
            $invitationCodeData = [
                'invited_email' => $email,
                'created_user_id' => $admin->id
            ];

            list ($user, $invitationCode)= $userService->createAdminWithInvitationCode($userData, $invitationCodeData);

            \Mail::send('emails.invite_admin', ['code' => $invitationCode->code], function ($message) use ($email) {
                $message->to($email)->subject('Letro');
            });

            $request->session()->flash(Constants::INFO_MESSAGE, '招待メールを送信しました');
        } catch (\Exception $e) {
            \Log::error('AdminController invite false '.$email);
            \Log::error($e);
            $request->session()->flash(Constants::ERROR_MESSAGE, '招待メールを送信に失敗しました');
        }

        return back();
    }

    /**
     * @param Request $request
     * @param UserService $userService
     * @param SnsAccountService $snsAccountService
     * @param $adminId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Request $request, UserService $userService, SnsAccountService $snsAccountService, $adminId)
    {
        $userService->deletemodel($adminId);
        $snsAccountService->deleteBy('user_id', $adminId);

        $request->session()->flash(Constants::INFO_MESSAGE, 'アカウントを削除しました。');

        return back();
    }

    /**
     * @param Request $request
     * @param UserService $userService
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeEGCSetting(Request $request, UserService $userService) {
        $userId = $request->input('userId');

        if ($userId == \Auth::guard('admin')->user()->id) {
            return response()->json(200);
        }

        $userService->changeEGCSetting($userId);

        return response()->json(200);
    }
}
