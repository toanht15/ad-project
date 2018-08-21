<?php

namespace App\Repositories\Contracts;


interface RepositoryInterface {

    public function all($columns = ['*']);

    public function with(array $relations);

    public function lists($value, $key = null);

    public function paginate($perPage = 24, $columns = ['*']);

    public function create(array $data);

    public function createOrUpdate(array $identities, array $createAttributes, $updateAttributes = []);

    public function saveModel(array $data);

    public function update(array $data, $id, $attribute = "id");

    public function updateRich(array $data, $id);

    public function delete($id);

    public function deleteBy($attribute, $value);

    public function deleteWhere($where);

    public function count($attribute, $value);

    public function find($id, $columns = ['*']);

    public function findBy($field, $value, $columns = ['*']);

    public function findAllBy($attribute, $value, $columns = ['*']);

    public function queryWhere($where, $count = false, $order = [], $limit = null, $columns = ['*']);
}