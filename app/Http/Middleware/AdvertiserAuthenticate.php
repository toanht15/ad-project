<?php

namespace App\Http\Middleware;

use App\Models\Advertiser;
use App\Models\UserAdvertiser;
use Closure;

class AdvertiserAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $advertiserId = $request->route('advertiserId');

        if (!$advertiserId || !is_numeric($advertiserId)) {
            return $next($request);
        }

        if (!\Auth::check()) {
            if (!$this->advertiserLogin($advertiserId)) {
                //TODO 不正アクセス処理
                abort(404);
            }
        } else {
            $currentAdvertiser = \Auth::guard('advertiser')->user();
            if ($currentAdvertiser->id != $advertiserId && !$this->advertiserLogin($advertiserId)) {
                //TODO 不正アクセス処理
                abort(404);
            }
        }


        return $next($request);
    }

    /**
     * @param $advertiserId
     * @return bool
     */
    public function advertiserLogin($advertiserId)
    {
        $user = \Auth::user();
        $advertiser = Advertiser::find($advertiserId);
        $userAdvertiser = UserAdvertiser::where([
            'user_id' => $user->id,
            'advertiser_id' => $advertiserId
        ])->first();

        if (!$userAdvertiser) {
            return false;
        }

        \Auth::login($advertiser);

        return true;
    }
}
