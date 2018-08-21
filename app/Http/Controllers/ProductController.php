<?php

namespace App\Http\Controllers;

use App\Service\PartService;
use Illuminate\Http\Request;
use Classes\Constants;
use App\Exceptions\APIRequestException;
use Illuminate\Pagination\LengthAwarePaginator;


class ProductController extends Controller
{
    /**
     * @param PartService $partService
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \App\Exceptions\APIRequestException
     * @throws \Classes\Parts\Exceptions\JsonParseException
     */
    public function listPage(PartService $partService, Request $request)
    {
        $p = $request->get('page', 1);
        $search = $request->get('search', '');
        $itemPerPage = 10;
        $pages = $partService->paginateProducts($p, $itemPerPage, $search);
        $pageData = [];
        foreach ($pages['products'] as $product) {
            $pageData[] = $product->deserialize();
        }

        $response = new LengthAwarePaginator($pageData, $pages['total_count'], $pages['item_per_page'], $p);
        $response->setPath("/advertiser/pages?search=$search");
        $products = $response->toArray();
        return view('page.page_list', [
            'search' => $search,
            'page_data' => $pageData,
            'products' => $products,
            'response' => $response
        ]);
    }

    /**
     * @param PartService $partService
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\APIRequestException
     * @throws \Classes\Parts\Exceptions\JsonParseException
     */
    public function apiGetAllProduct(PartService $partService)
    {
        $products = $partService->getAllProducts();
        $response = [];
        foreach ($products as $product) {
            $response[] = $product->toArray();
        }

        return response()->json($response);
    }

    /**
     * @param PartService $partService
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\APIRequestException
     * @throws \Classes\Parts\Exceptions\JsonParseException
     */
    public function detail(PartService $partService, $id)
    {
        $page = $partService->getProductDetail($id);
        return response()->json($page->deserialize());
    }

    /**
     * @param PartService $partService
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\APIRequestException
     */
    public function update(PartService $partService, Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'string|required',
            'image' => 'url|required',
        ]);

        $partService->updateProduct($id,
            [
                'title' => $request->get('title'),
                'image' => $request->get('image'),
            ]);
        return response()->json();
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @param $imageId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\APIRequestException
     */
    public function apiSaveImageProduct(Request $request, PartService $partService, $imageId)
    {
        $productUrls = $request->get('product_urls');
        if (!$productUrls) {
            $productUrls = [];
        }

        $response = $partService->updateProductImage($imageId, $productUrls);

        return response()->json($response['data']);
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\APIRequestException
     */
    public function deteleImage(Request $request, PartService $partService, $id)
    {
        $this->validate($request, [
            'page_url' => 'url|required'
        ]);
        $partService->deleteProductImage($id, $request->get('page_url'));
        return response()->json();
    }

    /**
     * @param PartService $partService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetCvPages(PartService $partService)
    {
        $site = \Session::get('site');
        $cvPages = $partService->getCvPages($site);

        return response()->json($cvPages, 200);
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiAddProductBySitemap(Request $request, PartService $partService)
    {
        $this->validate($request, [
            'sitemap_url' => 'required',
        ]);

        try {
            $partService->addProductBySitemap($request->input('sitemap_url'), $request->input('match_cv_page_id'));
            $request->session()->flash(Constants::INFO_MESSAGE, '商品ページを追加しました');

            return response()->json([], 200);
        } catch (\Exception $e) {
            \Log::error($e);
            $request->session()->flash(Constants::INFO_MESSAGE, '商品ページの追加に失敗しました');

            return response()->json([], 400);
        }
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiAddProductByUrlList(Request $request, PartService $partService)
    {
        $this->validate($request, [
            'url_list' => 'required',
        ]);

        try {
            $response = $partService->addProductByUrlList($request->input('url_list'));

            return response()->json($response, 200);
        } catch (APIRequestException $e) {
            return response()->json(['errors' => $e->getApiErrors()], 404);
        } catch (\Exception $e) {
            \Log::error($e);
            $request->session()->flash(Constants::INFO_MESSAGE, '商品ページを追加に失敗しました');

            return response()->json([], 400);
        }
    }
}
