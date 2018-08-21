<?php


namespace App\Repositories\Eloquent;


use App\classes\PartAPIClient;
use Classes\Parts\Image;

class VtdrImageRepository extends PartAPIClient
{

    /**
     * @param $id
     * @return Image
     * @throws \App\Exceptions\APIRequestException
     */
    public function getDetail($id)
    {
        $response = $this->httpRequest('GET', '/images/get_image_detail_receive.json',
            [
                'bind_product' => 1,
                'image_id' => $id
            ]);

        return new Image($response['data']['image']);
    }
}