<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\AccountHasAdAccount;
use App\Models\AdAccount;
use App\Models\Admin;
use App\Models\Advertiser;
use App\Models\AdvertiserInstagramAccount;
use App\Models\MediaAccount;
use App\Models\MediaToken;
use App\Models\SnsAccount;
use App\Models\User;
use App\Models\UserAdvertiser;
use Classes\Constants;
use Classes\Roles;
use Illuminate\Console\Command;

class OneTimeMoveDatabaseToNew extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oneTimeMoveDatabaseToNew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function doCommand()
    {
        try {
            $this->moveUserData();
            $this->moveAdAccountData();
        } catch (\Exception $e) {
            \Log::error($e);
        }
    }

    /**
     * move data from accounts to users, sns_accounts and media_tokens
     */
    public function moveUserData()
    {
        $oldAccounts = Account::withTrashed()->get();

        foreach ($oldAccounts as $oldAccount) {
            try {
                \DB::beginTransaction();
                // create users
                $user = new User();
                $user->id = $oldAccount->id;
                $user->email = $oldAccount->email;
                $user->user_name = $oldAccount->name;
                $user->profile_img_url = $oldAccount->profile_image;
                $user->tenant_id = $oldAccount->tenant_id;
                $user->deleted_at = $oldAccount->deleted_at;
                $admin = Admin::where('facebook_id', $oldAccount->facebook_id)->first();
                if ($admin) {
                    $user->role = Roles::ADMIN;
                } else {
                    $user->role = Roles::USER;
                }
                $user->save();

                // create media tokens
                $mediaToken = new MediaToken();
                $mediaToken->id = $oldAccount->id;
                $mediaToken->media_account_id = $oldAccount->facebook_id;
                $mediaToken->media_type = Constants::MEDIA_FACEBOOK;
                $mediaToken->access_token = $oldAccount->access_token;
                $mediaToken->refresh_token = "";
                $mediaToken->token_expired_flg = $oldAccount->token_expired_flg;
                $mediaToken->save();

                // create sns accounts
                $snsAccount = new SnsAccount();
                $snsAccount->id = $oldAccount->id;
                $snsAccount->user_id = $user->id;
                $snsAccount->media_user_id = $oldAccount->facebook_id;
                $snsAccount->name = $oldAccount->name;
                $snsAccount->profile_img_url = $oldAccount->profile_image;
                $snsAccount->access_token = $oldAccount->access_token;
                $snsAccount->refresh_token = "";
                $snsAccount->media_type = Constants::MEDIA_FACEBOOK;
                $snsAccount->deleted_at = $oldAccount->deleted_at;
                $snsAccount->save();

                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error($e);
            }
        }
    }

    /**
     * move data from adaccounts to advertiser, media_accounts, user_advertisers
     */
    public function moveAdAccountData()
    {
        $oldAdAccounts = AdAccount::withTrashed()->leftjoin('account_has_adaccount', 'account_has_adaccount.ad_account_id', '=', 'ad_accounts.id')
        ->leftjoin('accounts', 'accounts.id', '=', 'account_has_adaccount.account_id')
        ->selectRaw('ad_accounts.*, accounts.tenant_id, accounts.id as account_id')
        ->groupBy('ad_accounts.id')
        ->get();

        foreach ($oldAdAccounts as $oldAdAccount) {
            try {
                \DB::beginTransaction();
                // create advertiser
                $advertiser = new Advertiser();
                $advertiser->id = $oldAdAccount->id;
                $advertiser->name = $oldAdAccount->name;
                if ($oldAdAccount->tenant_id) {
                    $advertiser->tenant_id = $oldAdAccount->tenant_id;
                } else {
                    // deleted ad_account
                    $advertiser->tenant_id = 1;
                }
                
                $advertiser->max_search_condition = $oldAdAccount->max_search_condition;
                $advertiser->completed_tutorial_flg = $oldAdAccount->is_completed_tutorial;
                $advertiser->deleted_at = $oldAdAccount->deleted_at;
                $advertiser->save();
                // create media accounts
                $mediaAccount = new MediaAccount();
                $mediaAccount->id = $oldAdAccount->id;
                if ($oldAdAccount->account_id) {
                    $mediaAccount->media_token_id = $oldAdAccount->account_id;
                } else {
                    // deleted ad_account
                    $mediaAccount->media_token_id = 1;
                }
                $mediaAccount->advertiser_id = $oldAdAccount->id;
                $mediaAccount->media_type = Constants::MEDIA_FACEBOOK;
                $mediaAccount->media_account_id = $oldAdAccount->facebook_ads_account_id;
                $mediaAccount->name = $oldAdAccount->name;
                $mediaAccount->last_crawled_ad_id = $oldAdAccount->last_crawled_ad_id;
                $mediaAccount->save();

                if ($oldAdAccount->instagram_account_id) {
                    $advertiserInstagram = new AdvertiserInstagramAccount();
                    $advertiserInstagram->advertiser_id = $advertiser->id;
                    $advertiserInstagram->instagram_account_id = $oldAdAccount->instagram_account_id;
                    $advertiserInstagram->save();
                }

                $tenants = AccountHasAdAccount::join('accounts', 'accounts.id', '=', 'account_has_adaccount.account_id')
                    ->where('account_has_adaccount.ad_account_id', $oldAdAccount->id)
                    ->selectRaw('distinct tenant_id')
                    ->get();

                foreach($tenants as $tenant) {
                    $users = Account::where('tenant_id', $tenant->tenant_id)->get();
                    foreach($users as $user) {
                        $userAdvertiser = new UserAdvertiser();
                        $userAdvertiser->user_id = $user->id;
                        $userAdvertiser->advertiser_id = $oldAdAccount->id;
                        $userAdvertiser->role = Roles::ADVERTISER;
                        $userAdvertiser->save();
                    }
                }
                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error($e);
            }
        }
    }
}
