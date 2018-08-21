<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Service\UserService;
use Classes\Constants;
use Illuminate\Http\Request;

class UserSettingController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function settingPage(Request $request)
    {
        return view()->make('user.setting', [
            'email' => \Auth::user()->email
        ]);
    }

    /**
     * @param Request $request
     * @param UserService $userService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveSetting(Request $request, UserService $userService)
    {
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
}
