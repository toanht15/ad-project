<?php
/**
 * Created by IntelliJ IDEA.
 * User: le_tung
 * Date: 16/06/01
 * Time: 11:10
 */

namespace Classes;

class PMAPIClient extends BaseAPIClient
{

    /**
     * @return mixed
     */
    public function getApiAddress()
    {
        return env('PM_API_HOST');
    }

    /**
     * @param $hashtag
     * @param int $offset
     * @param int $limit
     * @return mixed
     * @throws \App\Exceptions\APIRequestException
     */
    public function getImages($hashtag, $offset = 0, $limit = 500)
    {
        $response = $this->request('GET', '/get_images', [
            'search_key'    => $hashtag,
            'offset'        => $offset,
            'limit'         => $limit
        ]);

        return $response;
    }

    /**
     * @param array $offerInfos
     * @param $token
     * @param $answerHashtag
     * @return mixed
     * @throws \App\Exceptions\APIRequestException
     */
    public function requestOfferSet($offerInfos = [], $token, $answerHashtag)
    {
        $response = $this->request('POST', '/request_offer', null, [
            'offer_infos'       => $offerInfos,
            'ig_access_token'   => $token,
            'answer_hashtag'    => $answerHashtag
        ]);

        return $response;
    }

    /**
     * @param $offerSetId
     * @return mixed
     * @throws \App\Exceptions\APIRequestException
     */
    public function getOfferStatus($offerSetId)
    {
        $response = $this->request('GET', '/offer_status', [
            'offer_set_id'          => $offerSetId
        ]);

        return $response;
    }

    public function updateOfferStatus($offerSetId, $postId, $status){
        $response = $this->request('POST', '/update_offer_status', [], [
            'offer_set_id' => $offerSetId,
            'post_id' => $postId,
            'permission_status' => $status
        ]);

        return $response;
    }
}
