<?php


namespace App\Repositories\Eloquent;


use App\Models\Offer;

class OfferRepository extends BaseRepository {

    public function modelClass(){
        return Offer::class;
    }

    /**
     * @return mixed
     */
    private function joinToInsight()
    {
        return $this->model->join('images', 'images.offer_id', '=', 'offers.id')
            ->join('media_image_entries', 'media_image_entries.image_id', '=', 'images.id')
            ->join('ads_use_images', 'ads_use_images.image_entry_id', '=', 'media_image_entries.id')
            ->join('media_ads_insights', 'media_ads_insights.facebook_ad_id', '=', 'ads_use_images.ad_id');
    }

    /**
     * @param null $advertiserId
     * @return mixed
     */
    public function getApprovedOfferByAdvertiserId($advertiserId = null)
    {
        $query = $this->model->join('advertisers', 'offers.advertiser_id', '=', 'advertisers.id')
            ->selectRaw('advertisers.id, count(offers.id) as num_offer_approved')
            ->groupBy('advertisers.id')
            ->whereIn('offers.status', [Offer::STATUS_APPROVED, Offer::STATUS_LIVING]);
        if ($advertiserId) {
            $query = $query->where('advertisers.id', $advertiserId);
        }
        return $query->get();
    }

    /**
     * @return mixed
     */
    public function getOfferWithReportBaseQuery()
    {
        return $this->model->join('posts', 'posts.id', '=', 'offers.post_id')
            ->join('authors', 'authors.id', '=', 'posts.author_id')
            ->join('images', 'images.offer_id', '=', 'offers.id')
            ->leftJoin('media_image_entries', 'media_image_entries.image_id', '=', 'images.id')
            ->leftJoin('ads_use_images', 'ads_use_images.image_entry_id', '=', 'media_image_entries.id')
            ->leftJoin('media_ads_insights', 'media_ads_insights.facebook_ad_id', '=', 'ads_use_images.ad_id')
            ->groupBy('offers.id')
            ->selectRaw('offers.id,offers.status,offers.created_at,approved_at, DATE_FORMAT(offers.approved_at,"%Y/%m/%d %H:%i") as approved_at_formatted,
            (offers.today_ctr - offers.yesterday_ctr) as ctr_compare,posts.like,posts.image_url,posts.file_format,posts.video_url,posts.text,posts.pub_date,posts.post_url,posts.id as post_id,
            authors.profile_url as author_url,authors.icon_img,authors.name,authors.username,authors.follower,authors.post_count,sum(IFNULL(media_ads_insights.spend, 0)) as spend,
            sum(IFNULL(media_ads_insights.impression,0)) as imp,sum(IFNULL(media_ads_insights.click,0)) as click,
            (sum(IFNULL(media_ads_insights.click,0))/sum(IFNULL(media_ads_insights.impression,0))) as ctr');
    }

    /**
     * @param $offerId
     * @return mixed
     */
    public function getOfferWithReport($offerId)
    {
        return $this->getOfferWithReportBaseQuery()->where('offers.id', $offerId)
            ->join('offer_sets', 'offer_sets.id', '=', 'offers.offer_set_id')
            ->selectRaw('offers.advertiser_id,offers.created_at,offer_sets.title,posts.post_id as post_media_id,posts.post_url,offer_sets.answer_tag,posts.pub_date,offer_sets.hashtag')->first();
    }

    /**
     * @param $originImageId
     * @param $advertiserId
     * @return mixed
     */
    public function getOfferBaseInfoByImageId($originImageId, $advertiserId)
    {
        return $this->model->join('posts', 'posts.id', '=', 'offers.post_id')
            ->join('images', 'images.offer_id', '=', 'offers.id')
            ->where([
                'images.id' => $originImageId,
                'offers.advertiser_id' => $advertiserId
            ])
            ->selectRaw('offers.id as offer_id, posts.author_id, posts.id as post_id')
            ->first();
    }

    /**
     * @param $offerSetGroupId
     * @param $limit
     * @return array|static[]
     */
    public function getTodayUpOffers($offerSetGroupId, $limit = 50)
    {
        return $this->getOfferWithReportBaseQuery()
            ->join('offer_sets', 'offer_sets.id', '=', 'offers.offer_set_id')
            ->where('offer_sets.offer_set_group_id', $offerSetGroupId)
            ->whereRaw('offers.today_ctr >= offers.yesterday_ctr')
            ->selectRaw('(offers.today_ctr - offers.yesterday_ctr) as ctr_compare')
            ->orderBy('ctr_compare', 'desc')
            ->having('spend', '>', 0)
            ->limit($limit)
            ->get();
    }

    /**
     * @param $offerSetGroupId
     * @param int $limit
     * @return array|static[]
     */
    public function getTodayDownOffers($offerSetGroupId, $limit = 50)
    {
        return $this->getOfferWithReportBaseQuery()
            ->join('offer_sets', 'offer_sets.id', '=', 'offers.offer_set_id')
            ->where('offer_sets.offer_set_group_id', $offerSetGroupId)
            ->whereRaw('offers.today_ctr < offers.yesterday_ctr')
            ->selectRaw('(offers.today_ctr - offers.yesterday_ctr) as ctr_compare')
            ->orderBy('ctr_compare', 'asc')
            ->having('spend', '>', 0)
            ->limit($limit)
            ->get();
    }

    /**
     * @param $offerSetGroupId
     * @param int $limit
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getUnLiveOffers($offerSetGroupId, $limit = 50)
    {
        return $this->getOfferWithReportBaseQuery()
            ->join('offer_sets', 'offer_sets.id', '=', 'offers.offer_set_id')
            ->where('offer_sets.offer_set_group_id', $offerSetGroupId)
            ->having('spend', '=', 0)
            ->limit($limit)
            ->get();
    }

    /**
     * @param $advertiserId
     * @param int $limit
     * @return mixed
     */
    public function getYesterdayUpOffers($advertiserId, $limit = 50)
    {
        return $this->getOfferWithReportBaseQuery()
            ->join('offer_sets', 'offer_sets.id', '=', 'offers.offer_set_id')
            ->join('offer_set_groups', 'offer_set_groups.id', '=', 'offer_sets.offer_set_group_id')
            ->where('offers.advertiser_id', $advertiserId)
            ->whereRaw('offers.yesterday_ctr > offers.two_day_ago_ctr')
            ->selectRaw('(offers.yesterday_ctr - offers.two_day_ago_ctr) as ctr_compare, offer_set_groups.title')
            ->orderBy('ctr_compare', 'desc')
            ->having('spend', '>', 0)
            ->limit($limit)
            ->get();
    }

    /**
     * @param $advertiserId
     * @param int $limit
     * @return mixed
     */
    public function getYesterdayDownOffers($advertiserId, $limit = 50)
    {
        return $this->getOfferWithReportBaseQuery()
            ->join('offer_sets', 'offer_sets.id', '=', 'offers.offer_set_id')
            ->join('offer_set_groups', 'offer_set_groups.id', '=', 'offer_sets.offer_set_group_id')
            ->where('offers.advertiser_id', $advertiserId)
            ->whereRaw('offers.yesterday_ctr < offers.two_day_ago_ctr')
            ->selectRaw('(offers.yesterday_ctr - offers.two_day_ago_ctr) as ctr_compare, offer_set_groups.title')
            ->orderBy('ctr_compare', 'asc')
            ->having('spend', '>', 0)
            ->limit($limit)
            ->get();
    }

    /**
     * @param $advertiserId
     * @param $date
     * @param int $limit
     * @param string $order
     * @param string $direction
     * @return mixed
     */
    public function getApprovedByDate($advertiserId, $date, $limit = 50, $order = 'approved_at', $direction = 'desc')
    {
        return $this->model->join('posts', 'posts.id', '=', 'offers.post_id')
            ->join('authors', 'authors.id', '=', 'posts.author_id')
            ->join('offer_sets', 'offer_sets.id', '=', 'offers.offer_set_id')
            ->join('offer_set_groups', 'offer_set_groups.id', '=', 'offer_sets.offer_set_group_id')
            ->where([
                'offers.advertiser_id' => $advertiserId
            ])
            ->whereRaw("approved_at BETWEEN '$date 00:00:00' AND '$date 23:59:59'")
            ->selectRaw('posts.image_url,posts.like,authors.name,authors.username,authors.icon_img,authors.follower,authors.post_count,offer_set_groups.title')
            ->orderBy($order, $direction)
            ->limit($limit)
            ->get();
    }

    /**
     * @param $advertiserId
     * @param $date
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function getOfferCTRByAdvertiserIdAndDate($advertiserId, $date = null)
    {
        $response = $this->joinToInsight();

        if ($date) {
            $response = $response->where('media_ads_insights.date', $date);
        }

        $response = $response->where('offers.advertiser_id', $advertiserId)
            ->groupBy('offers.id')
            ->selectRaw('offers.*,(sum(media_ads_insights.click)/sum(media_ads_insights.impression)) as ctr,sum(media_ads_insights.spend) as spend')
            ->get();

        return $response;
    }

    /**
     * @param $advertiserId
     * @param null $mediaType
     * @param null $beginDate
     * @param null $endDate
     * @param int $limit
     * @return mixed
     */
    public function getTopPerformanceUgc($advertiserId, $mediaType = null, $beginDate = null, $endDate = null, $limit = 20)
    {
        $query = $this->joinToInsight()
            ->join('media_accounts', 'media_accounts.id', '=', 'media_image_entries.media_account_id');
        if ($beginDate && $endDate) {
            $query = $query->whereBetween('media_ads_insights.date', [$beginDate, $endDate]);
        }
        if ($mediaType) {
            $query = $query->where('media_type', $mediaType);
        }
        return $query->where('offers.advertiser_id', $advertiserId)
            ->groupBy(['offers.id', 'media_type'])
            ->havingRaw('sum(spend) > 0')
            ->orderByRaw('sum(click) / sum(impression) desc')
            ->limit($limit)
            ->selectRaw(
                'images.image_url, sum(click) as click, sum(spend) as spend, sum(impression) as imp, media_type, today_ctr, yesterday_ctr, two_day_ago_ctr'
            )->get();
    }

    /**
     * @param $advertiserId
     * @param $beginDate
     * @param $endDate
     * @return mixed
     */
    public function getActiveOfferWithKpi($advertiserId, $beginDate, $endDate)
    {
        $query = $this->joinToInsight()
            ->join('posts', 'posts.id', '=', 'offers.post_id')
            ->selectRaw('offers.id as offer_id, sum(media_ads_insights.spend) as spend')
            ->whereBetween('media_ads_insights.date', [$beginDate, $endDate])
            ->where('offers.advertiser_id', '=', $advertiserId)
            ->where('spend', '>' , 0)
            ->groupBy('offers.id');

        return $query->get();
    }

    /**
     * @param $ids
     * @param $dateStart
     * @param $dateStop
     * @return mixed
     */
    public function getDailyReportByOfferIds($ids, $dateStart, $dateStop)
    {
        return $this->model->whereIn('offers.id', $ids)
            ->join('images', 'images.offer_id', '=', 'offers.id')
            ->join('media_image_entries', 'media_image_entries.image_id', '=', 'images.id')
            ->join('ads_use_images', 'ads_use_images.image_entry_id', '=', 'media_image_entries.id')
            ->join('media_ads_insights', 'media_ads_insights.facebook_ad_id', '=', 'ads_use_images.ad_id')
            ->where('media_ads_insights.date', '>=', $dateStart)
            ->where('media_ads_insights.date', '<=', $dateStop)
            ->groupBy(['offers.id', 'date'])
            ->orderBy('date')
            ->selectRaw('offers.id, date, sum(click) as click, sum(impression) as imp, sum(spend) as spend')
            ->get();
    }

    /**
     * @param $offerSetGroupId
     * @return mixed
     */
    public function getLiveOfferByOfferSetGroupId($offerSetGroupId)
    {
        return $this->getOfferWithReportBaseQuery()
            ->join('offer_sets', 'offer_sets.id', '=', 'offers.offer_set_id')
            ->selectRaw('offer_sets.id as offer_set_id')
            ->where('offer_sets.offer_set_group_id', $offerSetGroupId)
            ->having('spend', '>', 0)
            ->get();
    }

    public function getOfferGroupByPostId($advertiserId)
    {
        return $this->model->join('posts', 'posts.id', '=', 'offers.post_id')
            ->where('offers.advertiser_id', $advertiserId)
            ->groupBy('posts.post_id')
            ->selectRaw("offers.*, 
                posts.file_format as file_format, 
                posts.image_url as image_url, 
                posts.post_url as post_url")
            ->get();
    }
}