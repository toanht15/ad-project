<?php


namespace App\Repositories\Eloquent;


use App\Models\ConversionType;

class ConversionTypeRepository extends BaseRepository {

    public function modelClass()
    {
        return ConversionType::class;
    }

    /**
     * @param $mediaAccountId
     * @return mixed
     */
    public function getUnlabelCustomConversions($mediaAccountId)
    {
        return $this->model->join('ads_conversions', 'ads_conversions.facebook_action_id', '=', 'conversion_types.id')
            ->where('conversion_types.action_type', 'like', 'offsite_conversion.custom.%')
            ->where([
                'conversion_types.label' => '',
                'ads_conversions.media_account_id' => $mediaAccountId
            ])
            ->groupBy('conversion_types.id')
            ->selectRaw('conversion_types.*')
            ->get();
    }

    /**
     * @param $advertiserId
     * @return mixed
     */
    public function getAvailableConversionType($advertiserId)
    {
        return $this->model->join('ads_conversions', 'ads_conversions.facebook_action_id', '=', 'conversion_types.id')
            ->join('media_accounts', 'media_accounts.id', '=', 'ads_conversions.media_account_id')
            ->where([
                'media_accounts.advertiser_id' => $advertiserId
            ])
            ->where('conversion_types.label', '!=', '')
            ->groupBy('conversion_types.label')
            ->selectRaw('conversion_types.*')
            ->get();
    }
}