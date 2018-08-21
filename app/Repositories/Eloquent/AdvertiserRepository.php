<?php


namespace App\Repositories\Eloquent;


use App\Models\Advertiser;

class AdvertiserRepository extends BaseRepository {

    /**
     * @return mixed
     */
    public function modelClass()
    {
        return Advertiser::class;
    }

    public function getAdvertiserByUserId($userId)
    {
        return $this->model->join('user_advertisers', 'advertisers.id', '=', 'user_advertisers.advertiser_id')
            ->where('user_advertisers.user_id', $userId)
            ->select('advertisers.*')
            ->get();
    }

    /**
     * @return mixed
     */
    public function getAdvertiserListWithOfferKpi()
    {
        $offerCountSubQuery = \DB::raw("(select advertisers.id, count(offers.id) as offer_count from advertisers left join offers on offers.advertiser_id = advertisers.id group by advertisers.id) as offers");
        $offerSetCountSubQuery = \DB::raw("(select advertisers.id, count(offer_set_groups.id) as offer_set_group_count from advertisers left join offer_set_groups on offer_set_groups.advertiser_id = advertisers.id group by advertisers.id) as offer_sets");
        $conditionCountSubQuery = \DB::raw("(select advertisers.id, count(search_conditions.id) as condition_count from advertisers left join search_conditions on search_conditions.advertiser_id = advertisers.id group by advertisers.id) as conditions");
        $mediaAccountCountSubQuery = \DB::raw("(select advertisers.id, count(media_accounts.id) as media_account_count from advertisers left join `media_accounts` on `media_accounts`.`advertiser_id` = advertisers.id group by advertisers.id) as media_accounts");

        return $this->model->join('tenants', 'tenants.id', '=', 'advertisers.tenant_id')
            ->join($offerCountSubQuery, 'offers.id', '=', 'advertisers.id')
            ->join($offerSetCountSubQuery, 'offer_sets.id', '=', 'advertisers.id')
            ->join($conditionCountSubQuery, 'conditions.id', '=', 'advertisers.id')
            ->join($mediaAccountCountSubQuery, 'media_accounts.id', '=', 'advertisers.id')
            ->selectRaw('advertisers.*, tenants.name as tenant_name, offers.offer_count, offer_sets.offer_set_group_count, conditions.condition_count, media_accounts.media_account_count')
            ->get();
    }

    /**
     * @param $dateStart
     * @param $dateStop
     * @param null $advertiserId
     * @return mixed
     */
    public function getAdAccountSpend($dateStart, $dateStop, $advertiserId = null)
    {
        $query = $this->model->join('media_accounts', 'media_accounts.advertiser_id', '=', 'advertisers.id')
            ->join('media_ad_account_insights', 'media_ad_account_insights.media_account_id', '=', 'media_accounts.id')
            ->where('media_ad_account_insights.date', '>=', $dateStart)
            ->where('media_ad_account_insights.date', '<=', $dateStop)
            ->selectRaw('advertisers.id, sum(media_ad_account_insights.spend) as ad_account_spend')
            ->groupBy('advertisers.id');
        if ($advertiserId) {
            $query = $query->where('advertisers.id', $advertiserId);
        }

        return $query->get();
    }

    /**
     * @param $beginDate
     * @param $endDate
     * @param null $advertiserId
     * @return mixed
     */
    public function getAdvertiserFullDailySpend($beginDate, $endDate, $advertiserId = null)
    {
        $select = [
            'advertisers.id',
            'advertisers.name',
            'media_ad_account_insights.date',
        ];
        $query = $this->model->join('media_accounts', 'media_accounts.advertiser_id', '=', 'advertisers.id')
            ->join('media_ad_account_insights', 'media_accounts.id', '=', 'media_ad_account_insights.media_account_id')
            ->whereBetween('media_ad_account_insights.date', [$beginDate, $endDate])
            ->select($select)
            ->selectRaw('sum(media_ad_account_insights.spend) as spend')
            ->groupBy('advertisers.id', 'media_ad_account_insights.date');
        if ($advertiserId) {
            $query = $query->where('advertisers.id', $advertiserId);
        }

        return $query->get();
    }

    /**
     * @param $beginDate
     * @param $endDate
     * @return array
     */
    public function getAdvertiserDailySpend($beginDate, $endDate)
    {
        $select = [
            'advertisers.id',
            'advertisers.name',
            'media_ads_insights.date',
        ];
        $query = $this->model->join('media_accounts', 'media_accounts.advertiser_id', '=', 'advertisers.id')
            ->join('media_ads_insights', 'media_accounts.id', '=', 'media_ads_insights.media_account_id')
            ->whereBetween('media_ads_insights.date', [$beginDate, $endDate])
            ->select($select)
            ->selectRaw('sum(media_ads_insights.spend) as spend')
            ->groupBy('advertisers.id', 'media_ads_insights.date');
        return $query->get();
    }

    /**
     * @return mixed
     */
    public function getAdvertiserWithContract($id = null)
    {
        $select = [
            'advertisers.id',
            'advertisers.name as adv_name',
            'tenants.name as tenant_name',
            'contract_services.service_type',
            'contract_services.vtdr_site_id',
            'contract_schedules.start_date',
            'contract_schedules.end_date'
        ];
        $query = $this->model->join('tenants', 'tenants.id', '=', 'advertisers.tenant_id')
            ->join('contract_services', 'contract_services.advertiser_id', '=', 'advertisers.id')
            ->join('contract_schedules', 'contract_schedules.contract_service_id', '=', 'contract_services.id')
            ->orderBy('advertisers.id')
            ->select($select);
        if ($id) {
            $query->where('advertisers.id', '=', $id);
        }

        return $query->get();
    }

    public function getAdvertiserWithTenant($id)
    {
        $select = [
            'advertisers.id',
            'advertisers.name as adv_name',
            'tenants.name as tenant_name'
        ];

        return $this->model->join('tenants', 'tenants.id', '=', 'advertisers.tenant_id')
            ->where('advertisers.id', '=', $id)
            ->select($select)
            ->first();
        }

}