<?php

namespace App\Console;

use App\Console\Commands\CheckAndUpdateStoppedAd;
use App\Console\Commands\CreateDailyMailReport;
use App\Console\Commands\CreatePromotedTweetInsightJob;
use App\Console\Commands\CreateTwMediaAccountInsightJob;
use App\Console\Commands\DeleteUselessPosts;
use App\Console\Commands\GenerateContractServiceData;
use App\Console\Commands\GetDailyFbMediaAccountInsight;
use App\Console\Commands\GetDailyFbAdsInsight;
use App\Console\Commands\GetTwJobsResult;
use App\Console\Commands\InstagramHashtagCrawler;
use App\Console\Commands\InstagramAccountCrawler;
use App\Console\Commands\MatchImageHashWithFbAds;
use App\Console\Commands\MatchImageHashWithTwAds;
use App\Console\Commands\OneTimeChangeDefaultSearchConditionTitle;
//use App\Console\Commands\OneTimeCopyHashtagDataToSearchCondition;
//use App\Console\Commands\OnetimeCreateOfferSetGroup;
//use App\Console\Commands\OneTimeDeleteDuplicatedHashtag;
//use App\Console\Commands\OneTimeMoveDatabaseToNew;
use App\Console\Commands\OneTimeCopySlideshowToMediaSlideshow;
use App\Console\Commands\OneTimeFillConversionLabel;
use App\Console\Commands\OneTimeGeneratePostUsername;
use App\Console\Commands\OneTimeUpdateOfferLivingStatus;
//use App\Console\Commands\OneTimeUpdateScoreStatus;
use App\Console\Commands\OneTimeUpdateVideoUrl;
use App\Console\Commands\ReloadMediaUrl;
use App\Console\Commands\SyncHashtagImage;
use App\Console\Commands\SyncOfferStatus;
use App\Console\Commands\SyncOwnedContractSchedule;
use App\Console\Commands\SyncPartDataTemporary;
use App\Console\Commands\UpdateAdsStatus;
use App\Console\Commands\UpdateInstagramAccount;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        MatchImageHashWithFbAds::class,
        MatchImageHashWithTwAds::class,
        SyncHashtagImage::class,
        GetDailyFbAdsInsight::class,
        SyncOfferStatus::class,
        InstagramHashtagCrawler::class,
        InstagramAccountCrawler::class,
        CreateDailyMailReport::class,
        UpdateAdsStatus::class,
        GetDailyFbMediaAccountInsight::class,
        DeleteUselessPosts::class,
        CreateTwMediaAccountInsightJob::class,
        CreatePromotedTweetInsightJob::class,
        GetTwJobsResult::class,
//        OnetimeCreateOfferSetGroup::class,
//        OneTimeUpdateOfferLivingStatus::class,
//        OneTimeCopyHashtagDataToSearchCondition::class,
//        OneTimeDeleteDuplicatedHashtag::class,
//        OneTimeUpdateScoreStatus::class,
//        OneTimeMoveDatabaseToNew::class,
        OneTimeChangeDefaultSearchConditionTitle::class,
        OneTimeCopySlideshowToMediaSlideshow::class,
        OneTimeFillConversionLabel::class,
        OneTimeUpdateVideoUrl::class,
        CheckAndUpdateStoppedAd::class,
        GenerateContractServiceData::class,
        SyncPartDataTemporary::class,
        ReloadMediaUrl::class,
        UpdateInstagramAccount::class,
        SyncOwnedContractSchedule::class,
        OneTimeGeneratePostUsername::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /** 広告のインサイトを取得するバッチ */
        $schedule->command('getDailyFbAdsInsight')->cron('55 */4 * * *')->withoutOverlapping();
        /**
         * Promoted Tweetのインサイトを取得するジョブ作成バッチ
         * 1時間後に結果を取得
         */
        $schedule->command('createPromotedTweetInsightJob')->cron('01 */4 * * *')->withoutOverlapping();
        /** Fb 広告アカウントのインサイトを取得するバッチ */
        $schedule->command('getDailyFbMediaAccountInsight')->cron('30 3 * * *')->withoutOverlapping();
        /**
         * Tw 広告アカウントのインサイトジョブを作成するバッチ
         * 1時間後に結果を取得
         */
        $schedule->command('createTwMediaAccountInsightJob')->cron('01 */6 * * *')->withoutOverlapping();
        /** Tw ジョブの結果を取得するバッチ */
        $schedule->command('getTwJobsResult')->cron('00 * * * *')->withoutOverlapping();

        /** 画像とFB広告をマッチイングバッチ */
        $schedule->command('matchImageHashWithFbAds')->cron('00 3-24/8 * * *')->withoutOverlapping();
        /** 画像とTW広告をマッチイングバッチ */
        $schedule->command('matchImageHashWithTwAds')->cron('00 */4 * * *')->withoutOverlapping();

        /** PMからオファーのステータスを取得するバッチ */
        $schedule->command('syncOfferStatus')->cron('*/5 * * * *')->withoutOverlapping();

        /** ハッシュタグの投稿を取得するバッチ */
        $schedule->command('instagramHashtagCrawler')->cron('00 2-23/2 * * *');
    
        /** ハッシュタグの投稿を取得するバッチ */
        $schedule->command('instagramAccountCrawler')->cron('30 1-23/2 * * *')->withoutOverlapping();

        /** デーリーレポート作成するバッチ */
        $schedule->command('createDailyMailReport')->cron('00 09 * * *')->withoutOverlapping();

        /** アドのステータスを更新するバッチ */
        $schedule->command('updateAdsStatus')->cron('00 23 * * *')->withoutOverlapping();

        /** 使わない投稿を削除するバッチ */
//        $schedule->command('deleteUselessPosts')->cron('00 01 * * *')->withoutOverlapping();

        /** check and update stopped ads status */
        $schedule->command('checkAndUpdateStoppedAd')->cron('05 01 */3 * *')->withoutOverlapping();

        /** sync data from vtdr */
        $schedule->command('syncPartDataTemporary')->cron('00 1-23/4 * * *')->withoutOverlapping();

        /** sync owned contract schedule - every day at 4:30 */
        $schedule->command('syncOwnedContractSchedule')->cron('30 4 * * *')->withoutOverlapping();
    }
}
