<?php

namespace App\Repositories\Eloquent;


use App\classes\PartAPIClient;
use App\classes\Parts\Page;
use Classes\Parts\Exceptions\JsonParseException;

class ProductRepository extends PartAPIClient
{
    /**
     * @param array $columns
     * @param string $search
     * @return \Illuminate\Support\Collection
     * @throws JsonParseException
     */
    public function all($columns = array('*'), $search = '')
    {
        $response = $this->httpRequest('GET', '/page/index.json');
        if (!isset($response['data']['urlList']))
            throw new JsonParseException();
        $parts = [];
        try {
            if(empty($search)){
                foreach ($response['data']['urlList'] as $pageData) {
                    $parts[] = new Page($pageData);
                }
            } else {
                foreach ($response['data']['urlList'] as $pageData) {
                    if (array_filter($pageData, function ($item) use ($search) {
                        if (stripos($item, $search) !== false) {
                            return true;
                        }
                        return false;
                    })) {
                        $parts[] = new Page($pageData);
                    }
                }
            }
        } catch (\Exception $e) {
            throw new JsonParseException();
        }

        return collect($parts);
    }
    
    public function paginate($page, $itemPerPage = 20, $search = '')
    {
        $conditionParams = array(
            'p' => $page,
            'item_per_page' => $itemPerPage
        );

        if(!empty($search)) {
            $conditionParams['search_url_pattern'] = $search;
        }

        $response = $this->httpRequest('GET', '/page/index.json', $conditionParams);
        if (!isset($response['data']['urlList']))
            throw new JsonParseException();
        $products = [];
        try {
            foreach ($response['data']['urlList'] as $pageData) {
                $products[] = new Page($pageData);
            }
        } catch (\Exception $e) {
            throw new JsonParseException();
        }
    
        $productList = [
            'products' => collect($products),
            'total_count' => $response['data']['totalCount'],
            'item_per_page' => $response['data']['countPerPage']
        ];

        return $productList;
    }

    /**
     * @param $id
     * @return Page
     * @throws JsonParseException
     * @throws \App\Exceptions\APIRequestException
     */
    public function find($id)
    {
        $response = $this->httpRequest('GET', '/page/detail.json', [
            'id' => $id
        ]);
        if (!isset($response['data']))
            throw new JsonParseException();
        try {
            $images = $response['data'];
            $data = [];
            $data['images'] = $images;
            $page = new Page($data);
        } catch (\Exception $e) {
            throw  new JsonParseException();
        }

        return $page;
    }

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return bool
     * @throws \App\Exceptions\APIRequestException
     */
    public function update(array $data, $id, $attribute = "id")
    {
        $response = $this->httpRequest('POST', '/parts/url_edit_receive.json', [], [
            "type" => "product_edit",
            "product_id" => $id,
            "view_product_image_url" => $data["image"],
            "view_title" => $data["title"],
            "use_product_image_flg" => 1
        ]);
        return true;
    }

    /**
     * @param $vtdrImageId
     * @param $productUrls
     * @return mixed
     * @throws \App\Exceptions\APIRequestException
     *
     * update product and image relation
     *
     */
    public function updateProductImage($vtdrImageId, $productUrls)
    {
        return $this->httpRequest('POST', '/parts/image_edit_receive.json', [],
            [
                'full_product_info' => 1,
                'type' => 'image_info',
                'image_id' => $vtdrImageId,
                'url' => $productUrls
            ]);
    }
}