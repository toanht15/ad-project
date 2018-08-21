<?php

namespace App\Http\Controllers\Admin;

use App\Models\MediaAdAccountInsight;
use App\Models\MediaAdsInsight;
use App\Models\Offer;
use App\Service\AdvertiserService;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function dashboard(Request $request)
    {
        $yesterday = (new \DateTime())->modify('-1 day')->format('Y-m-d');

        list($dateStart, $dateStop) = get_request_datetime($request);

        $totalData               = MediaAdsInsight::getDailyTotalInsightData($dateStart, $dateStop);
        $totalAdAccountSpend     = MediaAdAccountInsight::getTotalAdAccountSpend($dateStart, $dateStop);
        $yesterdayAdAccountSpend = MediaAdAccountInsight::getTotalAdAccountSpendByDate($yesterday);

        $totalSpend      = MediaAdsInsight::getTotalSpend($dateStart, $dateStop);
        $yesterdaySpend  = MediaAdsInsight::getSumSpendByDate($yesterday);
        $yesterdayCtr    = MediaAdsInsight::getTotalCtrByDate($yesterday);

        $minYesterdayCtr = empty($yesterdayCtr) ? false : reset($yesterdayCtr);
        $maxYesterdayCtr = empty($yesterdayCtr) ? false : end($yesterdayCtr);

        $allOfferCount      = Offer::countInPeriod($dateStart, $dateStop);
        $approvalOfferCount = Offer::countInPeriod($dateStart, $dateStop, [Offer::STATUS_APPROVED, Offer::STATUS_LIVING]);

        return \View::make('admin.admin_dashboard', [
            'totalSpend'              => $totalSpend,
            'yesterdaySpend'          => $yesterdaySpend,
            'minYesterdayCtr'         => $minYesterdayCtr,
            'maxYesterdayCtr'         => $maxYesterdayCtr,
            'dateStart'               => $dateStart,
            'dateStop'                => $dateStop,
            'totalData'               => $totalData,
            'totalAdAccountSpend'     => $totalAdAccountSpend,
            'yesterdayAdAccountSpend' => $yesterdayAdAccountSpend,
            'allOfferCount'           => $allOfferCount,
            'approvalOfferCount'      => $approvalOfferCount
        ]);
    }

    /**
     * @param Request $request
     * @param AdvertiserService $advertiserService
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function exportCSV(Request $request, AdvertiserService $advertiserService)
    {
        list($dateStart, $dateStop) = get_request_datetime($request);
        $dates = get_all_date_of_period($dateStart, $dateStop);
        $header = ['account_name', 'fb_ad_account_id', 'type'];
        foreach ($dates as $date) {
            array_push($header, $date);
        }
        $advertisers = $advertiserService->all();

        $entireData = $advertiserService->getAdvertiserFullDailySpend($dateStart, $dateStop)->toArray();
        $ugcData    = $advertiserService->getAdvertiserDailySpend($dateStart, $dateStop);

        $entireSpend = $advertiserService->createCSVData($entireData, $dates, $advertisers, 'account_spend');
        $ugcSpend    = $advertiserService->createCSVData($ugcData, $dates, $advertisers, 'ugc_spend');

        $stream = fopen('php://temp', 'w');
        fputcsv($stream, $header);
        foreach ($advertisers as $adAccount) {
            fputcsv($stream, $entireSpend[$adAccount->id]['csv_data']);
            fputcsv($stream, $ugcSpend[$adAccount->id]['csv_data']);
        }

        rewind($stream);
        $csv = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = mb_convert_encoding($csv, 'Shift_JIS', 'UTF-8');
        fclose($stream);

        return response(
            $csv,
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=Letro_$dateStart-$dateStop.csv",
            ]
        );
    }
}
