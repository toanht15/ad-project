<?php

namespace App\Console\Commands;


use App\Models\Hashtag;
use App\Models\HashtagHasPost;
use App\Models\Post;
use Helpers\SlackNotification;

class DeleteUselessPosts extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deleteUselessPosts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $backupPath;
    const LIMIT = 10000;

    /**
     * Create a new command instance.
     *
     * @return void
     */
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
        $this->backupPath = storage_path('app/backup');
        if(!\File::isDirectory($this->backupPath)) {
            \File::makeDirectory($this->backupPath, 755, true);
        }

        for ($i = 1; $i<= 100; $i++) {
            // loop max 100 times
            $result = $this->deletePosts();
            if (!$result) {
                // break if can not delete anymore
                break;
            }
        }

        $this->deleteNotRelatedHashtag();
    }

    /**
     * @return int
     */
    public function deletePosts()
    {
        $sixMonthBefore = (new \DateTime())->modify('-6 months')->format('Y-m-d H:i:s');
        $unusePosts = Post::withTrashed()->leftJoin('archived_posts', 'archived_posts.post_id', '=', 'posts.id')
            ->leftJoin('offers', 'offers.post_id', '=', 'posts.id')
            ->leftJoin('part_images_temporaries', 'part_images_temporaries.post_id', '=', 'posts.id')
            ->whereNull('archived_posts.id')
            ->whereNull('offers.id')
            ->whereNull('part_images_temporaries.id')
            ->where('posts.created_at', '<', $sixMonthBefore)
            ->groupBy('posts.id')
            ->select('posts.*')
            ->limit(self::LIMIT)
            ->get();
        if (!$unusePosts->count()) {
            return 0;
        }
        $postIds = $unusePosts->pluck('id');
        $this->deleteHashtagHasPost($postIds);

        $postArr = $unusePosts->toArray();

        // write posts backup
        $time = (new \DateTime())->format('YmdHis');
        $out = fopen($this->backupPath.'/posts_'.$time.'.csv', 'w+');
        fputcsv($out, array_keys($postArr[1]));
        foreach($postArr as $line)
        {
            fputcsv($out, $line);
        }
        fclose($out);

        // delete from database
        Post::whereIn('id', $postIds)->forceDelete();

        // send slack notification
        $message = "Deleted ".$unusePosts->count()." post, id from ".$unusePosts->first()->id.' to '.$unusePosts->last()->id. ' last created: '.$unusePosts->last()->created_at;
        \Log::info($message);
        SlackNotification::send($message, "Info", 'info');

        return $unusePosts->count();
    }

    /**
     * @param $postIds
     * @throws \Exception
     */
    public function deleteHashtagHasPost($postIds)
    {

        do {
            $hashtagHasPost = HashtagHasPost::whereIn('post_id', $postIds)->limit(self::LIMIT)->get();
            $hashtagHasPostArr = $hashtagHasPost->toArray();
            if (!count($hashtagHasPostArr)) {
                return;
            }
            // write hashtag_has_posts backup
            $time = (new \DateTime())->format('YmdHis');
            $out = fopen($this->backupPath.'/hashtag_has_post_'.$time.'.csv', 'w+');
            fputcsv($out, array_keys($hashtagHasPostArr[1]));
            foreach($hashtagHasPostArr as $line)
            {
                fputcsv($out, $line);
            }
            fclose($out);

            // delete from database
            HashtagHasPost::whereIn('post_id', $postIds)->limit(self::LIMIT)->delete();

            // slack notification
            $message = "Deleted ".$hashtagHasPost->count().' hashtag_has_post last created: '.$hashtagHasPost->last()->created_at;
            \Log::info($message);
            SlackNotification::send($message, "Info", 'info');

        } while (count($hashtagHasPost) == self::LIMIT);
    }

    /**
     *
     */
    public function deleteNotRelatedHashtag()
    {
        do {
            $baseSQL = Hashtag::leftJoin('hashtag_has_post', 'hashtags.id', '=', 'hashtag_has_post.hashtag_id')
                ->leftJoin('hashtag_has_categories', 'hashtag_has_categories.hashtag_id', '=', 'hashtags.id')
                ->leftJoin('search_hashtags', 'search_hashtags.hashtag_id', '=', 'hashtags.id')
                ->whereNull('hashtag_has_post.post_id')
                ->whereNull('hashtag_has_categories.hashtag_category_id')
                ->whereNull('search_hashtags.search_condition_id')
                ->limit(self::LIMIT);

            $hashtagData = $baseSQL->get();
            $count = $hashtagData->count();
            if (!$count) {
                return;
            }
            // write hashtag_has_posts backup
            $dataArr = $hashtagData->toArray();
            $time = (new \DateTime())->format('YmdHis');
            $out = fopen($this->backupPath.'/hashtags_'.$time.'.csv', 'w+');
            fputcsv($out, array_keys($dataArr[1]));
            foreach($dataArr as $line)
            {
                fputcsv($out, $line);
            }
            fclose($out);

            // delete from database
            $baseSQL->delete();

            // slack notification
            $message = "Deleted ".$count.' hashtags';
            \Log::info($message);
            SlackNotification::send($message, "Info", 'info');

        } while($count == self::LIMIT);
    }
}
