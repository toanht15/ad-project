<?php

namespace App\Service;


use App\Exceptions\APIRequestException;
use App\Exceptions\InvalidRequestException;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\PartImageTemporaryRepository;
use App\Repositories\Eloquent\PartRepository;
use App\Repositories\Eloquent\PostRepository;
use App\Repositories\Eloquent\SiteRepository;
use App\Repositories\Eloquent\VtdrImageRepository;
use Classes\Parts\Field\SortField;
use Classes\Parts\PartKpi;
use \DatePeriod;
use \DateTime;
use \DateInterval;
use Classes\Parts\Image;


class PartService extends BaseService
{
    /** @var PartRepository */
    protected $repository;
    /** @var SiteRepository */
    protected $siteRepository;
    /** @var PartImageTemporaryRepository */
    protected $partImageTemporaryRepository;
    /** @var VtdrImageRepository */
    protected $imageRepository;
    /** @var ProductRepository */
    protected $productRepository;


    public function __construct($siteId)
    {
        $this->repository = app(PartRepository::class, ['siteId' => $siteId]);
        $this->siteRepository = app(SiteRepository::class, ['siteId' => $siteId]);
        $this->partImageTemporaryRepository = app(PartImageTemporaryRepository::class);
        $this->productRepository = app(ProductRepository::class, ['siteId' => $siteId]);
        $this->imageRepository = app(VtdrImageRepository::class, ['siteId' => $siteId]);
    }

    /**
     * @param $title
     * @param $partType
     * @param string $startAtDate
     * @param string $startAtTime
     * @param string $closeAtDate
     * @param string $closeAtTime
     * @return mixed
     */
    public function createDefaultPart($title, $partType, $startAtDate, $startAtTime, $closeAtDate, $closeAtTime)
    {
        return $this->createModel([
            'title' => $title,
            'template' => $partType,
            'start_at_date' => $startAtDate,
            'start_at_time' => $startAtTime,
            'close_at_date' => $closeAtDate,
            'close_at_time' => $closeAtTime,
            'sort' => SortField::SORT_BY_SELECTED_ORDER,
            'close_timing_type' => 1,
            'abtest_flg' => 0,
            'height' => 150
        ]);
    }

    public function findWithDateRange($id, $termFrom, $termTo, $display = false)
    {
        return $this->repository->find($id, $termFrom, $termTo, $display);
    }

    public function publish($id)
    {

        # check
        $temp = $this->partImageTemporaryRepository->findBy('vtdr_part_id', $id);

        if (!$temp) {
            throw  new \Exception('紐付けUGCはありません');
        }
        return $this->repository->publish($id);
    }

    /**
     * @param array $data
     * @throws \App\Exceptions\APIRequestException
     */
    public function createCvTargetPage($data = [])
    {
        $this->siteRepository->createCvTargetPage($data);
    }

    /**
     * @param array $data
     * @return mixed
     * @throws \App\Exceptions\APIRequestException
     */
    public function createCvTargetPages($data = [])
    {
        return $this->siteRepository->createCvTargetPages($data);
    }

    public function createExcludeAddresses($addresses)
    {
        return $this->siteRepository->createExcludeAddresses($addresses);
    }

    /** @param $data
     * @return mixed|static
     */
    public function createTemporatiData($data)
    {
        return $this->partImageTemporaryRepository->createOrUpdate($data, $data);
    }

    /**
     * @param $siteId
     * @return \Classes\Parts\Site
     * @throws \App\Exceptions\APIRequestException
     */
    public function findSite($siteId)
    {
        return $this->siteRepository->find($siteId);
    }

    /**
     * @return mixed
     */
    public function getAllSites()
    {
        return $this->siteRepository->all();
    }

    /**
     * @return bool
     */
    public function setAdmin()
    {
        // check admin
        if (!\Auth::guard('admin')->check()) {
            return false;
        }

        $this->siteRepository->setAdmin(true);

        return true;
    }

    /**
     * @param string $search
     * @return \Illuminate\Support\Collection
     * @throws APIRequestException
     * @throws \Classes\Parts\Exceptions\JsonParseException
     */
    public function getAllProducts($search='')
    {
        return $this->productRepository->all(['*'], $search);
    }

    /**
     * @param $page
     * @param $itemPerPage
     * @param $search
     * @return \Illuminate\Support\Collection
     * @throws \Classes\Parts\Exceptions\JsonParseException
     */
    public function paginateProducts($page, $itemPerPage = 20, $search = '')
    {
        return $this->productRepository->paginate($page, $itemPerPage, $search);
    }

    /**
     * @param $id
     * @return \App\classes\Parts\Page
     * @throws APIRequestException
     * @throws \Classes\Parts\Exceptions\JsonParseException
     */
    public function getProductDetail($id)
    {
        return $this->productRepository->find($id);
    }

    /**
     * @param $id
     * @param $data
     * @return bool
     * @throws APIRequestException
     */
    public function updateProduct($id, $data)
    {
        return $this->productRepository->update($data, $id);
    }

    /**
     * @param $vtdrImageId
     * @param $pageUrl
     * @throws APIRequestException
     */
    public function deleteProductImage($vtdrImageId, $pageUrl)
    {
        # get image info
        try {
            $imageInfo = $this->imageRepository->getDetail($vtdrImageId);
            $pageUrls = $imageInfo->bin_products;
            $newArr = array();
            foreach ($pageUrls as $value) {
                if ($value['url'] == $pageUrl) {
                    continue;
                } else {
                    $newArr[] = $value['url'];
                }
            }

            $this->updateProductImage($vtdrImageId, $newArr);

        } catch (\Exception $e) {
            \Log::error($e);
            throw new APIRequestException();
        }
    }

    public function getSiteId()
    {
        return $this->repository->getSiteId();
    }

    /**
     * @param $partId
     * @return \Classes\Parts\Part
     * @throws InvalidRequestException
     * @throws \App\Exceptions\APIRequestException
     */
    public function getPartBasicSetting($partId)
    {
        $part = $this->repository->getPartBasicSetting($partId);
        if ($part->site_id != $this->repository->getSiteId()) {
            throw new InvalidRequestException();
        }

        return $part;
    }

    public function getPartKpi($partId, $startDate, $endDate)
    {
        $endDate = (new DateTime($endDate))->modify('+1 day')->format('Y-m-d');
        $response = $this->repository->httpRequest('GET', '/parts/graph_parts_receive.json',
            [
                'parts_id' => $partId,
                'sum' => 'daily',
                'from' => $startDate,
                'to' => $endDate,
            ]);


        $data = $response['data'];
        if (!isset($data['date']))
            $data = [];
        else
            $data = $data['date'];

        $dates = [];

        $period = new DatePeriod(
            new DateTime($startDate),
            new DateInterval('P1D'),
            new DateTime($endDate)
        );

        $numberOfDate = count($data);
        $index = 0;

        $allDateData = [];

        foreach ($period as $key => $value) {
            $currentDate = $value->format('Y-m-d');

            if ($index < $numberOfDate && $currentDate == $data[$index]['date']) {
                $allDateData[] = $data[$index++];
            } else {
                $allDateData[] = [
                    "date" => $currentDate,
                    "imp" => 0,
                    "inview" => 0,
                    "uu" => 0,
                    "cv" => 0,
                    "cvr" => 0,
                    "click_count" => 0
                ];
            }
        }

        foreach ($allDateData as $date_data) {
            $dates[] = new PartKpi($date_data);
        }
        return collect($dates);
    }

    public function updateSortValue($partId, $values)
    {
        $postDatas = ['parts_id' => $partId];
        $postDatas['order_manual'] = [];
        foreach ($values as $key => $value) {
            $postDatas['order_manual[' . $key . ']'] = $value;
        }
        $response = $this->repository->httpRequest('POST', '/sites/order_weight_receive.json', [], $postDatas, []);
    }

    /**
     * @param $partId
     * @return \Classes\Parts\Part
     * @throws \App\Exceptions\APIRequestException
     */
    public function getPartDesignSetting($partId)
    {
        $designSetting = $this->repository->getPartDesignSetting($partId);

        return $designSetting;
    }

    /**
     * @param $partId
     * @return \Classes\Parts\Part
     * @throws APIRequestException
     */
    public function getPartImageList($partId)
    {
        return $this->repository->getImages($partId);
    }

    /**
     * @param $partId
     * @param $data
     * @throws \App\Exceptions\APIRequestException
     */
    public function updatePartDesign($data, $partId)
    {
        return $this->repository->updateDesign($data, $partId);
    }

    /**
     * @param $postId
     * @throws APIRequestException
     */
    public function getPartImageDetail($postId)
    {
        $siteId = $this->repository->getSiteId();
        $partIds = '';
        /** @var PostRepository $postRepository */
        $postRepository = app(PostRepository::class);
        $post = $postRepository->find($postId, ['post_id']);
        $items = $this->partImageTemporaryRepository->getPartIds($postId, $siteId);
        foreach ($items as $item) {
            $partIds = $partIds . ',' . $item->vtdr_part_id;
        }

        return $this->repository->getImageData($post->post_id, $partIds);
    }


    /**
     * @param $postId
     * @throws APIRequestException
     */
    public function registerImageWithSite($postId)
    {
        /** @var PostRepository $postRepository */
        $postRepository = app(PostRepository::class);
        $postHashtags = $postRepository->getPostHashtagData($postId);
        foreach ($postHashtags as $postHashtag ){
            $response = $this->repository->registerImageWithSite($postHashtag);
            if (isset($response['image_id'])) {
                $this->partImageTemporaryRepository->create([
                    'post_id' => $postHashtag->post_id,
                    'post_media_id' => $postHashtag->post_media_id,
                    'vtdr_image_id' => $response['image_id'],
                    'vtdr_site_id' => $this->repository->getSiteId(),
                ]);
            }
        }
    }

    /**
     * @param $partId
     * @param $postIds
     * @throws APIRequestException
     */
    public function registerImagesWithPart($partId, $postIds)
    {
        $siteId = $this->getSiteId();
        $temps = $this->partImageTemporaryRepository->queryWhere([
            'post_id' => ['in', $postIds],
            'vtdr_site_id' => $siteId,
        ]);
        // TODO : need refactoring
        $registeredPostIds = [];
        $recordsForRegister = [];
        foreach ($temps as $temp) {
            if (!in_array($temp->post_id, $registeredPostIds)) {
                $registeredPostIds[] = $temp->post_id;
                $imgIds[] = $temp->vtdr_image_id;
                $recordsForRegister[] = $temp;
            }
        }
        $this->repository->registerImageWithPart($partId, $imgIds);

        foreach ($recordsForRegister as $temp) {
            $this->partImageTemporaryRepository->createOrUpdate([
                'post_id' => $temp->post_id,
                'vtdr_site_id' => $siteId,
                'vtdr_part_id' => $partId
            ], [
                'post_id' => $temp->post_id,
                'post_media_id' => $temp->post_media_id,
                'vtdr_image_id' => $temp->vtdr_image_id,
                'vtdr_site_id' => $siteId,
                'vtdr_part_id' => $partId
            ]);
        }
    }

    /**
     * @param $partIds
     * @param $postIds
     * @throws APIRequestException
     */
    public function registerImage($partIds, $postIds)
    {
        foreach ($postIds as $postId) {
            // 投稿をサイトに登録
            if (!$this->isRegisteredImageWithSite($postId)) {
                $this->registerImageWithSite($postId);
            }
        }
        // 投稿をUGCセットに登録
        foreach ($partIds as $partId) {
            $this->registerImagesWithPart($partId, $postIds);
        }
    }

    /**
     * @param $postId
     * @return bool
     */
    public function isRegisteredImageWithSite($postId)
    {
        $count = $this->partImageTemporaryRepository->queryWhere([
            'post_id' => $postId,
            'vtdr_site_id' => $this->getSiteId()
        ], true);
        if ($count > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param $imageId
     * @param $partId
     * @return bool
     */
    public function isRegisteredImageIdWithPart($imageId, $partId)
    {
        $items = $this->partImageTemporaryRepository->queryWhere([
            'vtdr_image_id' => $imageId,
            'vtdr_part_id' => $partId
        ]);
        if (count($items)) {
            return true;
        }

        return false;
    }

    /**
     * @param $postId
     * @param $partId
     * @return bool
     */
    public function isRegisteredImageWithPart($postId, $partId)
    {
        $items = $this->partImageTemporaryRepository->queryWhere([
            'post_id' => $postId,
            'vtdr_part_id' => $partId
        ]);
        if (count($items)) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     * @throws APIRequestException
     */
    public function getListProduct()
    {
        $response = $this->siteRepository->getListProduct();

        return $response['urlList'];
    }

    /**
     * @param $postId
     * @return Image
     * @throws APIRequestException
     */
    public function getImageDetailByPostId($postId)
    {
        if (!$this->isRegisteredImageWithSite($postId)) {
            $this->registerImageWithSite($postId);
        }
        $temp = $this->partImageTemporaryRepository->queryWhere([
            'post_id' => $postId,
            'vtdr_site_id' => $this->getSiteId()
        ])->first();

        return $this->imageRepository->getDetail($temp->vtdr_image_id);
    }

    /**
     * @param $postId
     * @param $url
     * @throws APIRequestException
     */
    public function deletePageImageByPostId($postId, $url)
    {
        $item = $this->partImageTemporaryRepository->queryWhere([
            'post_id' => $postId,
            'vtdr_site_id' => $this->getSiteId()
        ])->first();

        $this->deleteProductImage($item->vtdr_image_id, $url);
    }

    /**
     * @param $postId
     * @return array
     * @throws APIRequestException
     * @throws \Classes\Parts\Exceptions\JsonParseException
     */
    public function getRegisteredPart($postId)
    {
        $siteId = $this->repository->getSiteId();
        $parts = $this->repository->all();
        $partIds = [];
        $partList = [];
        $registeredParts = [];
        foreach ($parts as $part) {
            $partIds[] = $part->id;
            $partList[$part->id] = $part;
        }

        $items = $this->partImageTemporaryRepository->getRegiteredParts($postId, $siteId, $partIds);

        foreach ($items as $item) {
            $partDetail = $this->repository->find($item->vtdr_part_id, null, null, true);
            if (!$this->isHiddenImage($item->vtdr_image_id, $partDetail->images)) {
                $registeredParts[] = $partList[$item->vtdr_part_id];
                unset($partList[$item->vtdr_part_id]);
            }
        }

        $result = [
            'registeredParts' => $registeredParts,
            'unregisterParts' => $partList
        ];

        return $result;
    }

    /**
     * @param $postId
     * @param $partId
     * @throws APIRequestException
     * @throws \Exception
     */
    public function deletePartImage($postId, $partId)
    {
        $data = $this->partImageTemporaryRepository->queryWhere([
            'post_id' => $postId,
            'vtdr_site_id' => $this->getSiteId(),
        ])->first();

        $this->repository->deleteImagePart($partId, [$data->vtdr_image_id]);

        $this->partImageTemporaryRepository->deleteWhere([
            'post_id' => $postId,
            'vtdr_site_id' => $this->getSiteId(),
            'vtdr_part_id' => $partId
        ]);
    }

    /**
     * @param $partId
     * @param $exceptedImageIds
     * @throws APIRequestException
     * @throws \Exception
     */
    public function deletePartImageTemporaries($partId, array $exceptedImageIds)
    {
        $this->partImageTemporaryRepository->deleteWhere([
            'vtdr_site_id' => $this->getSiteId(),
            'vtdr_part_id' => $partId,
            'vtdr_image_id' => [
                'not in',
                $exceptedImageIds
            ]
        ]);
    }
    /**
     * @param $vtdrImageId
     * @param $productUrls
     * @return mixed
     * @throws APIRequestException
     *
     * * update product and image relation
     */
    public function updateProductImage($vtdrImageId, $productUrls)
    {
        return $this->productRepository->updateProductImage($vtdrImageId, $productUrls);
    }

    /**
     * @param $site
     * @return array
     */
    public function getCvPages($site)
    {
        $cvPages = [];
        foreach ($site->cv_pages as $page) {
            if ($page['type'] == 3) {
                $cvPages[] = $page;
            }
        }

        return $cvPages;
    }

    /**
     * @param $url
     * @param $cvPageId
     * @return mixed
     * @throws APIRequestException
     */
    public function addProductBySitemap($url, $cvPageId)
    {
        return $this->repository->addProductBySitemap($url, $cvPageId);
    }

    /**
     * @param $urls
     * @return mixed
     * @throws APIRequestException
     */
    public function addProductByUrlList($urls)
    {
        return $this->repository->addProductByUrlList($urls);
    }

    /**
     * @param $site
     * @return int
     */
    public function getCvPagesMaxNo($site)
    {
        $max = 0;
        foreach ($site->cv_pages as $page) {
            if ($page['no'] > $max) {
                $max = $page['no'];
            }
        }

        return $max;
    }

    /**
     * @param $vtdrImageId
     * @param $partImages
     * @return bool
     */
    private function isHiddenImage($vtdrImageId, $partImages)
    {
        foreach ($partImages as $image) {
            if ($image->image_id == $vtdrImageId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $date
     * @return mixed
     */
    public function getOldVtdrSites($date)
    {
        return $this->partImageTemporaryRepository->getOldVtdrSites($date);
    }

    /**
     * @param $data
     * @param $partId
     * @param $publicType
     * @return mixed
     */
    public function getPartDesignData($data, $partId, $publicType)
    {
        $data['publish_type'] = $publicType;
        if (!isset($data['parts_id'])) {
            $data['parts_id'] = $partId;
        }

        // save smart phone title same pc title
        if (isset($data['2_3'])) {
            $data['2_4'] = $data['2_3'];
        }

        if (isset($data['3_5'])) {
            $data['3_9'] = $data['3_5'];
        }

        foreach ($data as $key => $value) {
            if (strpos($key, 'view') !== false) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function updateSiteContract($startDate, $endDate)
    {
        return $this->siteRepository->updateContractSchedule($startDate, $endDate);
    }
}