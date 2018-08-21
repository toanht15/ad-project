<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Hashtag;
use App\Models\Post;
use \DB;

/**
 * App\Models\HashtagHasPost
 *
 * @property-read \App\Models\Post $post
 * @property-read \App\Models\Hashtag $hashCount
 * @mixin \Eloquent
 */
class HashtagHasPost extends Model
{
    /**
     * @var string table name
     */
    public $table = 'hashtag_has_post';

    /**
     * hasOne
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * @param array $hashtagId
     * @param $postid
     * @return HashtagHasPost
     */
    public static function createNew($hashtagId, $postid)
    {
        $hashtagRelation = HashtagHasPost::where(['hashtag_id' => $hashtagId, 'post_id' => $postid])->first();
        if (!$hashtagRelation) {
            $hashtagRelation = new HashtagHasPost();
            $hashtagRelation->hashtag_id = $hashtagId;
            $hashtagRelation->post_id = $postid;
            $hashtagRelation->save();
        }

        return $hashtagRelation;
    }
}
