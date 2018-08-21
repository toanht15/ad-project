<?php


namespace App\Repositories\Eloquent;


use App\classes\PartAPIClient;
use App\Models\Post;
use Classes\Parts\Exceptions\JsonParseException;
use Classes\Parts\Field\TemplateField;
use Classes\Parts\Image;
use Classes\Parts\Part;


class PartRepository extends PartAPIClient
{
    /**
     * @param $id
     * @param null $termFrom
     * @param null $termTo
     * @param bool $ab_test
     * @return Part
     * @throws JsonParseException
     * @throws \App\Exceptions\APIRequestException
     */
    public function find($id, $termFrom = null, $termTo = null, $display = false, $count = 1000)
    {
        $getData = [
            'parts_id' => $id,
            'count' => $count,
            'sort' => 5
        ];
        if ($termFrom && $termTo) {
            $getData['term_from'] = $termFrom;
            $getData['term_to'] = $termTo;
        }

        if ($display) {
            $getData['display'] = 'hidden';
        }
        $response = $this->httpRequest('GET', '/parts/detail.json', $getData);
        if (!isset($response['data']))
            throw new JsonParseException();
        try {
            $data = $response['data'];

            $data['parts']['images'] = $data['imageList'];
            $part = new Part($data['parts']);
            $part->updateImageWithPostId();


        } catch (\Exception $e) {
            throw  new JsonParseException();
        }

        return $part;
    }

    /**
     * @param array $columns
     * @return \Illuminate\Support\Collection
     * @throws JsonParseException
     * @throws \App\Exceptions\APIRequestException
     */
    public function all($columns = array('*'))
    {
        $response = $this->httpRequest('GET', '/parts/index.json');
        if (!isset($response['data']['partsList']))
            throw new JsonParseException();
        $parts = [];
        try {
            foreach ($response['data']['partsList'] as $partData) {
                if (in_array($partData['template'], [TemplateField::TYPE_SLIDER, TemplateField::TYPE_MEDIA])) {
                    $parts[] = new Part($partData);
                }
            }
        } catch (\Exception $e) {
            throw new JsonParseException();
        }

        return collect($parts);
    }

    /**
     * @param array $data
     * @throws \App\Exceptions\APIRequestException
     */
    public function create(array $data)
    {
        // 対象要素の後に挿入するのみ
        $data['insert_position_type'] = 3;
        return $this->httpRequest('POST', '/parts/edit_save.json', [], $data);

    }

    public function createOrUpdate(array $identities, array $createAttributes, $updateAttributes = [])
    {
    }

    /**
     * @param $partId
     * @return Part
     * @throws \App\Exceptions\APIRequestException
     */
    public function getPartBasicSetting($partId)
    {
        $response = $this->httpRequest('GET', '/parts/edit.json', ['parts_id' => $partId]);

        return new Part($response['data']);
    }

    /**
     * @param $partId
     * @return Part
     * @throws \App\Exceptions\APIRequestException
     */
    public function getPartDesignSetting($partId)
    {
        $response = $this->httpRequest('GET', '/parts/design_edit.json', ['parts_id' => $partId]);

        return new Part($response['data']);
    }

    /**
     * @param $partId
     * @return Part
     * @throws \App\Exceptions\APIRequestException
     */
    public function getImages($partId)
    {
        $response = $this->httpRequest('GET', '/parts/api_image_list.json', ['parts_id' => $partId]);

        return new Part($response['data']);
    }

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @throws \App\Exceptions\APIRequestException
     */
    public function update(array $data, $id, $attribute = "id")
    {
        // 対象要素の後に挿入するのみ
        $data['insert_position_type'] = 3;
        $this->httpRequest('POST', '/parts/edit_save.json', ['parts_id' => $id], $data);
    }

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return mixed
     * @throws \App\Exceptions\APIRequestException
     */
    public function updateDesign(array $data, $id, $attribute = "id")
    {
        return $this->httpRequest('POST', '/parts/design_edit_save.json', ['parts_id' => $id], $data);
    }

    /**
     * @param $id
     * @throws \App\Exceptions\APIRequestException
     */
    public function delete($id)
    {
        $this->httpRequest('GET', '/parts/delete.json', [
            'parts_id' => $id
        ]);
    }

    /**
     * @param $postMediaId
     * @param $partIds
     * @param $siteId
     * @return mixed
     * @throws \App\Exceptions\APIRequestException
     */
    public function getImageData($postMediaId, $partIds)
    {
        return $this->httpRequest('GET', '/parts/image_detail_api.json', [
            'post_id' => $postMediaId,
            'parts_ids' => $partIds,
        ]);
    }

    /**
     * @param $data
     * @return mixed
     * @throws \App\Exceptions\APIRequestException
     */
    public function registerImageWithSite($data)
    {
        $response = $this->httpRequest('POST', '/images/add_image_insta_hash_receive.json', ['image_info' => 1], [
            'check' => 1,
            'url' => $data->image_url,
            'hashtag' => $data->hashtag,
            'user_name' => $data->name ? $data->name : $data->user_name,
//            'user_id' => $data->user_media_id,
            'page_link' => $data->post_url,
            'resource_uid' => $data->post_media_id,
            'resource_description' => $data->text,
            'resource_type' => Post::convertToVtdrMediaType($data->type),
            'resource_date' => strtotime($data->pub_date),
            'sub_no' => $data->carousel_no
        ]);

        return $response['data'];
    }

    /**
     * @param $partId
     * @param $vtdrImageIds
     * @return mixed
     * @throws \App\Exceptions\APIRequestException
     */
    public function registerImageWithPart($partId, $vtdrImageIds)
    {
        $data = [
            'batch_action_group' => 10,
            'batch_action_sub_group' => $partId,
            'target_image[]' => $vtdrImageIds
        ];
        $response = $this->httpRequest('POST', '/images/edit_parts_image_receive.json', [], $data);

        return $response;
    }

    /**
     * @param $partId
     * @param $vtdrImageIds
     * @throws \App\Exceptions\APIRequestException
     */
    public function deleteImagePart($partId, $vtdrImageIds)
    {
        $data = [
            'batch_action_group' => 11,
            'batch_action_sub_group' => $partId,
            'target_image[]' => $vtdrImageIds
        ];

        $this->httpRequest('POST', '/images/edit_parts_image_receive.json', [], $data);
    }

    public function publish($part_id)
    {
        $data = [
            'parts_id' => $part_id
        ];

        $this->httpRequest('POST', '/parts/publish_receive.json ', [], $data);
    }

    /**
     * @param $url
     * @param $cvPageId
     * @return mixed
     * @throws \App\Exceptions\APIRequestException
     */
    public function addProductBySitemap($url, $cvPageId)
    {
        return $this->httpRequest('POST', '/page/sitemap_save.json', [], [
            'sitemap_url' => $url,
            'match_cv_page_id' => $cvPageId
        ]);
    }

    /**
     * @param $urls
     * @return mixed
     * @throws \App\Exceptions\APIRequestException
     */
    public function addProductByUrlList($urls)
    {
        return $this->httpRequest('POST', '/page/add_save.json', [], [
            'url_list' => $urls,
        ]);
    }
}