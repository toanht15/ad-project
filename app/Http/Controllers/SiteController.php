<?php

namespace App\Http\Controllers;

use App\Exceptions\APIRequestException;
use App\Service\PartService;
use Classes\Constants;
use Illuminate\Http\Request;


class SiteController extends Controller
{
    public function create(Request $request, PartService $partService)
    {
        $this->validate($request, [
            'title' => 'required|string|max:30',
            'url' => 'required|string|max:100',
        ]);

        $data = [[
            'url_string' => $request->get('url'),
            'label' => $request->get('title')
        ]];

        try {
            $partService->createCvTargetPage($data);
        } catch (APIRequestException $e) {

            return response()->json(['errors'=> $e->getApiErrors()], 400);

        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(['errors'=> [Constants::TOASTR_ERROR => '設定できませんでした']], 400);
        }

        return response()->json();
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\APIRequestException
     */
    public function apiGetSite(Request $request, PartService $partService, $id)
    {
        $site = $partService->findSite($id);
        return response()->json($site->data);
    }

    /**
     * @param PartService $partService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetAllSites(PartService $partService)
    {
        $partService->setAdmin(true);
        $sites = $partService->getAllSites();

        return response()->json($sites);
    }

    /**
     * @param PartService $partService
     * @return \Illuminate\Http\JsonResponse
     * @throws APIRequestException
     */
    public function apiGetListProduct(PartService $partService)
    {
        $products = $partService->getListProduct();

        return response()->json($products);
    }
}
