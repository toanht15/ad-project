<?php


namespace App\classes;


use App\Exceptions\APIRequestException;
use Classes\BaseAPIClient;

class PartAPIClient extends BaseAPIClient
{

    protected $siteId;
    private $isAdmin;

    public function __construct($siteId)
    {
        $this->siteId = $siteId;
        $this->isAdmin = false;
    }

    public function setAdmin($flg)
    {
        $this->isAdmin = $flg;
    }

    public function getSiteId()
    {
        return $this->siteId;
    }

    /**
     * @return mixed
     */
    public function getApiAddress()
    {
        if ($this->isAdmin) {
            return env('PART_API_HOST_ADMIN');
        }

        return env('PART_API_HOST');
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
        if (!isset($query['site_id']) && $this->siteId) {
            $query['site_id'] = $this->siteId;
        }

        $response = $this->request($method, $end_point, $query, $formParams, $json, $multipart);
        if (isset($response['json_data'])) {
            $response = $response['json_data'];
        }
        if (!isset($response['result']) || (isset($response['result']) && $response['result'] == 'ng')) {
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