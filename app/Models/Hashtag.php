<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * hashタグマスター
 * レコードの削除はしない。
 * Class Hashtag
 * @package App\Models
 *
 * @mixin \Eloquent
 */
class Hashtag extends Model
{
    const UNACTIVE  = 0;
    const ACTIVE    = 1;
    const CRAWLING  = 2;
    const FAIL      = 3;
    const WAIT      = 4;

    const TYPE_HASHTAG  = 1;
    const TYPE_USER     = 2;

    const CRAW_LIMIT = 100;

    protected $fillable = [
        'hashtag',
        'active_flg',
        'type'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function postRelations()
    {
        return $this->hasMany(HashtagHasPost::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function searchHashtag()
    {
        return $this->hasMany(SearchHashtag::class);
    }

    /**
     * @param $tag
     * @param int $sync
     * @return Hashtag|Model|null|static
     */
    public static function createOrUpdate($tag, $sync = Hashtag::ACTIVE)
    {
        if (!$tag) {
            return null;
        }
        $hashtag = self::where("hashtag", $tag)->first();
        if (!$hashtag) {
            $hashtag = new Hashtag();
            $hashtag->hashtag = $tag;
//            $hashtag->hashtag_code = encode_hashtag($tag);
        }
        $hashtag->active_flg = $sync;
        $hashtag->save();

        return $hashtag;
    }

    /**
     * @param $tag
     * @param int $sync
     * @return Hashtag|Model|null|static
     */
    public static function createUnique($tag, $sync = Hashtag::ACTIVE)
    {
        if (!$tag) {
            return null;
        }
        $hashtag = self::where("hashtag", $tag)->first();
        if ($hashtag) {
            return $hashtag;
        }
        $hashtag = new Hashtag();
        $hashtag->hashtag = $tag;
//        $hashtag->hashtag_code = encode_hashtag($tag);
        $hashtag->active_flg = $sync;
        $hashtag->save();

        return $hashtag;
    }

    /**
     * 関連の投稿と紐付けて削除する
     *
     * @throws \Exception
     */
    public function fDelete()
    {
        $this->postRelations()->delete();
        parent::delete();
    }

    /**
     * @param $status
     * @return string
     */
    public static function setStatusLabel($status)
    {
        switch ($status) {
            case self::UNACTIVE: return '<span class="label label-approved p5 status">Unactive</span>'; break;
            case self::ACTIVE: return '<span class="label label-approved p5 status">Success</span>'; break;
            case self::CRAWLING: return '<span class="label label-synchronis p5 status">Running</span>'; break;
            case self::FAIL: return '<span class="label label-failed p5 status">Failed</span>'; break;
            case self::WAIT: return '<span class="label label-failed p5 status">Queue</span>'; break;
            default: return ""; break;
        }
    }
}
