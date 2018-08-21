<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Hashtag;
use App\Models\HashtagHasPost;
use App\Models\Post;
use Classes\PMAPIClient;
use Illuminate\Console\Command;

class SyncHashtagImage extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncHashtagImage';

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
        $hashtags = Hashtag::where(['sync_flg' => 1])->get();

        $client = new PMAPIClient();
        foreach ($hashtags as $hashtag) {
            try {
                $images = $client->getImages($hashtag->hashtag);

                if (!isset($images['data'])) {
                    \Log::error($images);
                    continue;
                }

                foreach ($images['data'] as $image) {
                    $post = Post::where(['post_id' => $image['post_id']])->first();
                    if ($post) {
                        break;
                    }

                    if (!($postId = $this->storeImage($image))) {
                        continue;
                    }
                    $hashtagPost = HashtagHasPost::where(['hashtag_id' => $hashtag->id, 'post_id' => $postId])->first();

                    if ($hashtagPost) {
                        break;
                    } else {
                        HashtagHasPost::createNew($hashtag->id, $postId);
                    }
                }

                $this->updateHashtagInfo($hashtag);
            } catch (\Exception $e) {
                \Log::error($e);
            }
        }

        //update other hashtag
        $defaultHashtags = Hashtag::where(['sync_flg' => 0])->get();
        foreach ($defaultHashtags as $hashtag) {
            $this->updateHashtagInfo($hashtag);
        }
    }

    /**
     * @param $image
     * @return bool
     */
    public function storeImage($image)
    {
        try {
            \DB::beginTransaction();
            $author = Author::where(['media_id' => $image['author_id']])->first();
            if (!$author) {
                $author = new Author();
                $author->media_id       = $image['author_id'];
                $author->profile_url    = $image['author_url'];
                $author->name           = $image['author_name'];
                $author->icon_img       = $image['author_icon_img'];
                $author->save();
            }
            $post = new Post();
            $post->post_id      = $image['post_id'];
            $post->image_url    = $image['image_url'];
            $post->post_url     = $image['post_url'];
            $post->author_id    = $author->id;
            $post->pub_date     = $image['pub_date'];
            $post->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e);

            return null;
        }

        return $post->id;
    }

    /**
     * @param Hashtag $hashtag
     */
    public function updateHashtagInfo(Hashtag $hashtag)
    {
        $postCount = HashtagHasPost::where(['hashtag_id' => $hashtag->id])->count();
        $hashtag->post_count = $postCount;
        $hashtag->save();
    }
}
