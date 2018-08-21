<?php

namespace App\Http\Controllers\Admin;

use App\Service\AdvertiserService;
use App\Service\PartRequestService;
use App\Http\Controllers\Controller;
use Classes\Constants;
use Illuminate\Http\Request;

class PartRequestController extends Controller
{
    /**
     * @param Request $request
     * @param AdvertiserService $advertiserService
     * @param PartRequestService $partRequestService
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request, AdvertiserService $advertiserService, PartRequestService $partRequestService)
    {
        $query = $request->all();
        $page = $request->input('page') ? $request->input('page') : 1;
        $perPage = $request->input('per_page') ? $request->input('per_page') : 20;
        $orderBy = $request->input('order_by') ? $request->input('order_by') : 'views';
        $orderType = $request->input('order_type') ? $request->input('order_type') : 'DESC';
        $advertisers = [];
        try {
            list($dateStart, $dateStop) = get_request_datetime($request);
            $advertisers = $advertiserService->getAdAccountInfo($dateStart, $dateStop);
            $partRequestsFilter = array(
                'page' => $page,
                'per_page' => $perPage,
                'order_by' => $orderBy,
                'order_type' => $orderType
            );
            
            if(!empty($query['site_name'])) {
                $partRequestsFilter['site_name'] = $query['site_name'];
            }
            if(!empty($query['site_domain'])) {
                $partRequestsFilter['site_domain'] = $query['site_domain'];
            }
            if(!empty($query['part_title'])) {
                $partRequestsFilter['part_title'] = $query['part_title'];
            }
            if(!empty($query['request_url'])) {
                $partRequestsFilter['request_url'] = $query['request_url'];
            }
            if(!empty($query['from_date'])) {
                $partRequestsFilter['from_date'] = $query['from_date'];
            }
            if(!empty($query['to_date'])) {
                $partRequestsFilter['to_date'] = $query['to_date'];
            }
            if(!empty($query['advertiser_id'])) {
                $advertiserId = $query['advertiser_id'];
                if (!in_array($advertiserId, array_column($advertisers, 'id'))) {
                    throw new \Exception('This advertiser does not exist.');
                }
                $contract = $advertiserService->getActiveContract($advertiserId,  \App\Models\ContractService::FOR_OWNED);
                if(empty($contract)) {
                    throw new \Exception('This advertiser does not have any sites.');
                }

                $partRequestsFilter['site_id'] = $contract->vtdr_site_id;
            }
            
            $partRequests = $partRequestService->getPartRequest($partRequestsFilter);
            $partRequests['totalPage'] = isset($partRequests['totalCount']) ? ceil($partRequests['totalCount'] / $perPage) : 0;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $partRequests = [
                'data' => [],
                'totalPage' => 0
            ];
        }
        return view()->make('admin.part_requests', [
            'partRequests' => $partRequests,
            'currentPage'  => $page,
            'advertisers'  => $advertisers,
            'advertiserId' => isset($advertiserId) ? $advertiserId : null,
            'fromDate'     => isset($query['from_date']) ? $query['from_date'] : null,
            'toDate'       => isset($query['to_date']) ? $query['to_date'] : null,
            'itemPerPage'  => $perPage,
            'errorMessage' => isset($errorMessage) ? $errorMessage :null
        ]);
    }
}
