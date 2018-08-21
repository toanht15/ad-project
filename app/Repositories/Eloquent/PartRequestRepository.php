<?php


namespace App\Repositories\Eloquent;


use App\classes\OwnedApiClient;
use Classes\Parts\Exceptions\JsonParseException;


class PartRequestRepository extends OwnedApiClient
{
    public function getPartRequest($filter)
    {
        $response = $this->httpRequest('GET', '/part_requests_list.json', $filter);

        return $response;
    }
}