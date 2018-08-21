<?php


namespace App\Service;


use App\Repositories\Eloquent\AdvertiserRepository;
use App\Repositories\Eloquent\InstagramAccountRepository;
use App\Repositories\Eloquent\OfferRepository;
use App\Repositories\Eloquent\ContractServiceRepository;
use App\Repositories\Eloquent\SiteRepository;


class AdvertiserService extends BaseService
{

    /** @var AdvertiserRepository */
    protected $repository;

    public function __construct(AdvertiserRepository $advertiserRepository)
    {
        $this->repository = $advertiserRepository;
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function getAdvertisersByUserId($userId)
    {
        return $this->repository->getAdvertiserByUserId($userId);
    }

    /**
     * @param $startDate
     * @param $stopDate
     * @return mixed
     */
    public function getAdAccountInfo($startDate, $stopDate)
    {
        /** @var OfferRepository $offerRepository */
        $offerRepository = app(OfferRepository::class);
        $adAccounts = $this->repository->getAdvertiserListWithOfferKpi()->toArray();

        $numberOfferApproved = $offerRepository->getApprovedOfferByAdvertiserId()->toArray();
        $adAccountSpend = $this->repository->getAdAccountSpend($startDate, $stopDate)->toArray();

        $adAccounts = $this->addInfo($adAccounts, $numberOfferApproved);
        $adAccounts = $this->addInfo($adAccounts, $adAccountSpend);

        return $adAccounts;
    }

    /**
     * @param $advertiserId
     * @return bool
     */
    public function isConnectInstagram($advertiserId)
    {
        /** @var InstagramAccountRepository $instagramAccountRepository */
        $instagramAccountRepository = app(InstagramAccountRepository::class);
        $igAccounts = $instagramAccountRepository->getInstagramAccountByAdvertiserId($advertiserId);

        return $igAccounts->count() ? true : false;
    }

    /**
     * @param $beginDate
     * @param $endDate
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAdvertiserFullDailySpend($beginDate, $endDate)
    {
        return $this->repository->getAdvertiserFullDailySpend($beginDate, $endDate);
    }

    /**
     * @param $beginDate
     * @param $endDate
     * @return array
     */
    public function getAdvertiserDailySpend($beginDate, $endDate)
    {
        return $this->repository->getAdvertiserDailySpend($beginDate, $endDate);
    }

    /**
     * @param $adAccounts
     * @param $info
     * @return mixed
     */
    public function addInfo($adAccounts, $info)
    {
        $values = [];
        foreach ($info as $item) {
            $values[$item['id']] = $item;
        }

        for ($i = 0; $i < count($adAccounts); $i++) {
            if (isset($values[$adAccounts[$i]['id']])) {
                $adAccounts[$i] = array_merge($adAccounts[$i], $values[$adAccounts[$i]['id']]);
            }
        }

        return $adAccounts;
    }

    /**
     * @param $name
     * @param $tenantId
     * @throws \Exception
     */
    public function createNewAdvertiser($name, $tenantId)
    {
        /** @var SearchConditionService $searchConditionService */
        $searchConditionService = app(SearchConditionService::class);

        try {
            \DB::beginTransaction();
            $advertiser = $this->createModel([
                'name' => $name,
                'tenant_id' => $tenantId
            ]);

            $defaultHashtag = SearchConditionService::getDefaultSearchConditionTitle($advertiser->id);
            //create default search condition
            $searchConditionService->createSearchCondition($advertiser->id, [$defaultHashtag], '', false);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param $spendData
     * @param $dates
     * @param $advertisers
     * @param $type
     * @return array
     */
    public function createCSVData($spendData, $dates, $advertisers, $type)
    {
        $result = [];
        foreach ($spendData as $data) {
            $result[$data['id']]['spend'][$data['date']] = $data['spend'];
        }

        foreach ($advertisers as $advertiser) {
            $result[$advertiser->id]['csv_data'] = [];

            array_push($result[$advertiser->id]['csv_data'], $advertiser->name);
            array_push($result[$advertiser->id]['csv_data'], $advertiser->facebook_ads_id);
            array_push($result[$advertiser->id]['csv_data'], $type);
            foreach ($dates as $date) {
                if (isset($result[$advertiser->id]['spend'][$date])) {
                    array_push($result[$advertiser->id]['csv_data'], $result[$advertiser->id]['spend'][$date]);
                    continue;
                }
                array_push($result[$advertiser->id]['csv_data'], 0);
            }
        }

        return $result;
    }

    /**
     * TODO complete this function with new database
     *
     * @param $adAccountId
     * @param $adAccountName
     * @throws \Exception
     *
     * public function removeAdAccount($adAccountId, $adAccountName)
     * {
     * $adAccount = AdAccount::findOrFail($adAccountId);
     * if ($adAccount->name !== $adAccountName) {
     * throw new \Exception('広告アカウントが確認できませんでした。');
     * }
     *
     * try {
     * \DB::beginTransaction();
     * \Log::info('Start deleting AdAccount '.$adAccountName.' id:'.$adAccountId);
     * // slideshow
     * $slideshowIds = Slideshow::where('ad_account_id', $adAccountId)->pluck('id');
     * if (count($slideshowIds)) {
     * AdsUseSlideshow::whereIn('slideshow_id', $slideshowIds)->delete();
     * SlideshowImage::whereIn('slideshow_id', $slideshowIds)->delete();
     * Slideshow::whereIn('id', $slideshowIds)->forceDelete();
     * }
     *
     * // facebook data
     * AdsConversion::where('ad_account_id', $adAccountId)->delete();
     * $facebookAdIds = MediaAd::where('ad_account_id', $adAccountId)->pluck('id');
     * if (count($facebookAdIds)) {
     * MediaAdsInsight::whereIn('facebook_ad_id', $facebookAdIds)->delete();
     * AdsUseImage::whereIn('ad_id', $facebookAdIds)->delete();
     * MediaAd::whereIn('id', $facebookAdIds)->delete();
     * }
     * MediaAdAccountInsight::where('ad_account_id', $adAccountId)->delete();
     * MediaImageEntry::where('ad_account_id', $adAccountId)->delete();
     *
     * // other
     * ArchivedPost::where('ad_account_id', $adAccountId)->delete();
     * ApprovedNotification::where('ad_account_id', $adAccountId)->delete();
     *
     * $searchConditionIds = SearchCondition::where('ad_account_id', $adAccountId)->pluck('id');
     * // offers
     * Image::where('ad_account_id', $adAccountId)->delete();
     * Offer::where('ad_account_id', $adAccountId)->delete();
     * OfferSet::where('ad_account_id', $adAccountId)->delete();
     * OfferSetGroup::where('ad_account_id', $adAccountId)->delete();
     *
     * // search hashtag
     * SearchHashtag::whereIn('search_condition_id', $searchConditionIds)->delete();
     * SearchCondition::where('ad_account_id', $adAccountId)->delete();
     *
     * // account
     * AccountHasAdAccount::where('ad_account_id', $adAccountId)->delete();
     * $adAccount->forceDelete();
     *
     * \DB::commit();
     *
     * \Log::info('Stop deleting AdAccount '.$adAccountName);
     * } catch (\Exception $e) {
     * \Log::error($e);
     * \DB::rollBack();
     * \Log::info('Canceled deleting AdAccount '.$adAccountName);
     * throw new \Exception($e);
     * }
     * } */

    /**
     * @param $advId
     * @param $serviceType
     * @return bool
     */
    public function getActiveContract($advId, $serviceType)
    {

        /** @var ContractServiceRepository $contractServiceRepository */
        $contractServiceRepository = app(ContractServiceRepository::class);
        $contracts = $contractServiceRepository->getContractListByServiceType($advId, $serviceType);
        $now = (new \DateTime())->format('Y-m-d');
        foreach ($contracts as $contract) {
            $endDate = date('Y-m-d', strtotime('+ 7 days', strtotime($contract->end_date)));
            if (check_date_in_range($contract->start_date, $endDate, $now) == 0) {
                return $contract;
            };
        }

        return false;
    }

    /**
     * @param $avdId
     * @return array
     * @throws \App\Exceptions\APIRequestException
     */
    public function getContractInfo($avdId)
    {
        $result = [];
        $adsContract = $this->getActiveContract($avdId, \App\Models\ContractService::FOR_AD);
        $result['adsContract'] = $adsContract;
        $ownedContract = $this->getActiveContract($avdId, \App\Models\ContractService::FOR_OWNED);
        $postContract = $this->getActiveContract($avdId, \App\Models\ContractService::FOR_POST);
        $result['post'] = $postContract;
        if ($ownedContract) {
            /** @var SiteRepository $siteRepository */
            $siteRepository = app(SiteRepository::class, ['siteId' => $ownedContract->vtdr_site_id]);
            $site = $siteRepository->find($ownedContract->vtdr_site_id);
            $result['site'] = $site;
            $result['isOwnedFirst'] = $ownedContract->is_owned_first;
        }

        return $result;
    }

    /**
     * @param $avdId
     * @return mixed
     */
    public function getAdvertiserWithTenant($avdId)
    {
        return $this->repository->getAdvertiserWithTenant($avdId);
    }

    public function getContractSchedules($advId)
    {
        /** @var ContractServiceRepository $contractServiceRepository */
        $contractServiceRepository = app(ContractServiceRepository::class);

        return $contractServiceRepository->getContractByAdvId($advId);
    }

    /**
     * @param $advId
     * @param $serviceType
     * @return bool
     */
    public function hasActiveContract($advId, $serviceType)
    {
        /** @var ContractServiceRepository $contractServiceRepository */
        $contractServiceRepository = app(ContractServiceRepository::class);
        $contracts = $contractServiceRepository->getContractListByServiceType($advId, $serviceType);
        $now = (new \DateTime())->format('Y-m-d');
        foreach ($contracts as $contract) {
            $endDate = (new \DateTime($contract->end_date))->modify('+7 days')->format('Y-m-d');
            if (check_date_in_range($contract->start_date, $endDate, $now) == 0) {
                return true;
            };
        }

        return false;
    }

}