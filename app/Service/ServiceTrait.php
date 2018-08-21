<?php


namespace App\Service;


trait ServiceTrait {

    /**
     * @param array $data fillableカラムの値
     * @return mixed
     */
    public function createModel(array $data)
    {
        return $this->repository->create($data);
    }

    public function all($columns = array('*'))
    {
        return $this->repository->all($columns);
    }

    /**
     * @param array $identities model検索の条件
     * @param array $createAttributes 作成のときしか使わないカラム
     * @param array $updateAttributes 更新のとき使うカラム
     * @return mixed
     */
    public function createOrUpdate(array $identities, array $createAttributes, $updateAttributes = [])
    {
        return $this->repository->createOrUpdate($identities, $createAttributes, $updateAttributes);
    }

    public function updateModel(array $data, $id, $attribute = "id")
    {
        return $this->repository->update($data, $id, $attribute);
    }

    public function updateWhere(array $conditions, array $data)
    {
        return $this->repository->updateBy($conditions, $data);
    }

    public function findModel($id, $columns = ['*'])
    {
        return $this->repository->find($id, $columns);
    }

    public function findBy($attribute, $value, $columns = ['*'])
    {
        return $this->repository->findBy($attribute, $value, $columns);
    }

    public function deleteModel($id)
    {
        return $this->repository->delete($id);
    }

    public function deleteBy($attribute, $value)
    {
        return $this->repository->deleteBy($attribute, $value);
    }

    public function deleteWhere($where)
    {
        return $this->repository->deleteWhere($where);
    }

    public function getWhere($where, $count = false, $order = [], $limit = null, $columns = ['*'])
    {
        return $this->repository->queryWhere($where, $count, $order, $limit, $columns);
    }
}