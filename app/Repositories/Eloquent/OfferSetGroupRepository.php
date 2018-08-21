<?php


namespace App\Repositories\Eloquent;


use App\Models\OfferSetGroup;

class OfferSetGroupRepository extends BaseRepository {

    public function modelClass()
    {
        return OfferSetGroup::class;
    }

    /**
     * @param $advertiserId
     * @param null $limit
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getOfferSetGroupListWithSummary($advertiserId, $limit = null)
    {
        $subQuery = "(select distinct media_ads_insights.id as insight_id,offer_set_groups.id,media_ads_insights.spend,
                    media_ads_insights.impression,media_ads_insights.click
                    from `offer_set_groups`
                    inner join `offer_sets` on `offer_sets`.`offer_set_group_id` = `offer_set_groups`.`id`
                    inner join `offers` on `offers`.`offer_set_id` = `offer_sets`.`id`
                    inner join `posts` on `posts`.`id` = `offers`.`post_id`
                    inner join `authors` on `authors`.`id` = `posts`.`author_id`
                    inner join `images` on `images`.`offer_id` = `offers`.`id`
                    left join `media_image_entries` on `media_image_entries`.`image_id` = `images`.`id`
                    left join `ads_use_images` on `ads_use_images`.`image_entry_id` = `media_image_entries`.`id`
                    left join `media_ads_insights` on `media_ads_insights`.`facebook_ad_id` = `ads_use_images`.`ad_id`
                    where `offer_set_groups`.`advertiser_id` = ?
                    order by `offer_sets`.`created_at` desc) as subq";

        $query = $this->model->join(\DB::raw($subQuery), 'subq.id', '=', 'offer_set_groups.id')
            ->setBindings([$advertiserId])
            ->selectRaw('offer_set_groups.*,sum(subq.spend) as spend,
            sum(subq.impression) as imp,sum(subq.click) as click,
            (sum(subq.click)/sum(subq.impression)) as ctr');

        if ($limit) {
            $query = $query->limit($limit);
        }

        return $query->groupBy('offer_set_groups.id')
            ->orderBy('offer_set_groups.created_at', 'desc')
            ->get();
    }
}