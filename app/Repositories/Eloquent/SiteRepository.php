<?php
/**
 * Created by PhpStorm.
 * User: letuananh
 * Date: 2/26/18
 * Time: 16:54:52
 */

namespace App\Repositories\Eloquent;
use App\classes\PartAPIClient;
use App\Exceptions\APIRequestException;
use Classes\Parts\Site;

class SiteRepository extends PartAPIClient
{

    /**
     * @param $id
     * @return Site
     * @throws \App\Exceptions\APIRequestException
     */
    public function find($id)
    {
        $response = $this->httpRequest('GET', '/sites/index.json', [
            'site_id' => $id
        ]);

        return new Site($response['data']['site']);
    }

    public function all($columns = array('*'))
    {
        $response = $this->httpRequest('GET', '/sites/index.json');

        return $response['data'];
    }

    /**
     * @param array $data
     * @throws \App\Exceptions\APIRequestException
     */
    public function createCvTargetPage(array $cv_data)
    {
        $data = [
            'type' => 'cv_pages'
        ];
        foreach ($cv_data as $key => $value){
            $key = $key + 1;
            $data['url_string_' . $key] = $value['url_string'];
            $data['label_' . $key] = $value['label'];
            $data['type_' . $key] = 0;
            $data['url_type_' . $key] = 1;
        }
        $response = $this->httpRequest('POST', '/sites/edit_receive.json', [], $data);
    }

    public function createOrUpdate(array $identities, array $createAttributes, $updateAttributes = [])
    {
    }

    public function update(array $data, $id, $attribute = "id")
    {
    }

    public function delete($id)
    {
    }

    public function createExcludeAddresses($address){

        $data = [ 'type' => 'exclude_remote_address'];
        $data['exclude_remote_address'] = $address;

        $response = $this->httpRequest('POST', '/sites/edit_receive.json', [], $data);

        return $response;
    }

    public function createCVTargetPages(array $cv_data)
    {

        $data = ['type' => 'cv_pages'];

        foreach ($cv_data as $key => $value) {
            if (!isset($value['url_type_' . $key])) {
                $value['url_type_' . $key] = 1;
            }

            $data += $value;
        }

        return $this->httpRequest('POST', '/sites/edit_receive.json', [], $data);
    }

    /**
     * @return mixed
     * @throws APIRequestException
     */
    public function getListProduct()
    {
        $response = $this->httpRequest('GET', '/page/index.json');

        return $response['data'];
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return mixed
     * @throws APIRequestException
     */
    public function updateContractSchedule($startDate, $endDate)
    {
        $data = [
            'contract_start_at' => $startDate,
            'contract_end_at' => $endDate
        ];

        return $this->httpRequest('POST', '/sites/edit_save.json', $data);
    }
}