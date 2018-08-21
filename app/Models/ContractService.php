<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractService extends Model
{
    const FOR_AD = 1;
    const FOR_OWNED = 2;
    const FOR_POST = 3;

    protected $fillable = [
        'advertiser_id',
        'service_type',
        'vtdr_site_id',
        'max_media_account'
    ];

    /**
     * @param $type
     * @return string
     */
    public static function getServiceTypeLabel($type)
    {
        switch ($type) {
            case 1:
                return "AD";
            case 2:
                return "OWNED";
            case 3:
                return "POST";
            default:
                return "";
        }
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return string
     */
    public static function getStatusLabel($startDate, $endDate)
    {
        $now = (new \DateTime())->format('Y-m-d');
        $result = check_date_in_range($startDate, $endDate, $now);
        switch ($result) {
            case -1:
                return "Next";
            case 0:
                return "Active";
            case 1:
                return "Expired";
            default:
                return "";
        }
    }
}