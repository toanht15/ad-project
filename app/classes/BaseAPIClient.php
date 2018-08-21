<?php

namespace Classes;

use App\Exceptions\APIRequestException;
use GuzzleHttp\Client;

abstract class BaseAPIClient
{
    protected $client;
    protected $baseUrl;

    /**
     * @return Client
     */
    protected function getClient()
    {
        if (!$this->client) {
            $this->client = new Client(['verify' => false]);
        }
        return $this->client;
    }

    /**
     * @return mixed
     */
    private function getBaseUrl()
    {
        if (!$this->baseUrl) {
            $this->baseUrl = $this->getApiAddress();
        }
        return $this->baseUrl;
    }

    /**
     * @param string $method
     * @param $end_point
     * @param array $query
     * @param array $formParams
     * @param array $json
     * @param $multipart
     * @return mixed
     * @throws APIRequestException
     */
    protected function request($method = 'GET', $end_point, $query = [], $formParams = [], $json = [], $multipart = [])
    {
        $requestParams = [];
        if ($query) {
            $requestParams['query'] = $query;
        }
        if ($formParams) {
            $requestParams['form_params'] = $formParams;
        }
        if ($json) {
            $requestParams['json'] = $json;
        }
        if ($multipart) {
            $requestParams['multipart'] = $multipart;
        }

//      $request_params['auth'] => [Config::get('parameters.basicAuthName'),Config::get('parameters.basicAuthPass'), 'digest']

        try {
            $res = $this->getClient()->request($method, $this->getBaseUrl().$end_point, $requestParams);
            $response = json_decode($res->getBody()->getContents(), true);

            return $response;
        } catch (\Exception $e) {
            \Log::error($e);
            if ($e->getResponse()) {
                throw new APIRequestException($e->getResponse()->getBody()->getContents());
            } else {
                throw new APIRequestException($e->getMessage());
            }
        }
    }

    abstract public function getApiAddress();
}
