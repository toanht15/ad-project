<?php


namespace App\classes;


use App\Exceptions\APIRequestException;
use Classes\BaseAPIClient;

class OwnedApiClient extends BaseAPIClient
{
    /**
     * @return mixed
     */
    public function getApiAddress()
    {
        return env('OWNED_API_URL');
    }

    /**
     * @param string $method
     * @param $end_point
     * @param array $query
     * @param array $formParams
     * @param array $json
     * @param array $multipart
     * @return mixed
     * @throws APIRequestException
     */
    public function httpRequest($method = 'GET', $end_point, $query = [], $formParams = [], $json = [], $multipart = [])
    {
        $response = $this->request($method, $end_point, $query, $formParams, $json, $multipart);
        if (!isset($response['result']) || (isset($response['result']) && ($response['result'] == 'ng' || $response['result'] == 'missing'))) {
            \Log::error([
                'url' => $end_point,
                'method' => $method,
                'query' => $query,
                'formParams' => $formParams,
                'response' => $response
            ]);
            throw new APIRequestException($response['errors']);
        }

        return $response;
    }
}