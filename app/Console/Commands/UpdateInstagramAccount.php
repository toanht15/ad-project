<?php

namespace App\Console\Commands;


use App\Models\InstagramAccount;
use App\Repositories\Eloquent\InstagramAccountRepository;
use Classes\InstagramApiClient;

class UpdateInstagramAccount extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateInstagramAccount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Instagram Account Info ';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function doCommand()
    {
        $instagramClient = new InstagramApiClient();

        /** @var InstagramAccountRepository $instagramAccountRepository */
        $instagramAccountRepository = app(InstagramAccountRepository::class);

        $instagramAccounts = $instagramAccountRepository->all();
        /**
         * @var InstagramAccount $instagramAccount
         */
        foreach ($instagramAccounts as $instagramAccount) {
            try {
                $instagramUser = $instagramClient->getCurrentUserByToken($instagramAccount->access_token);

                $name = $instagramUser->getFullName();
                $userName = $instagramUser->getUserName();

                $instagramAccount->name = $name ? $name : $userName;
                $instagramAccount->username = $userName;
                $instagramAccount->profile_image = $instagramUser->getProfilePicture();
                $instagramAccount->save();

            } catch (\Exception $e) {
                \Log::error($e);
            }
        }
    }
}
