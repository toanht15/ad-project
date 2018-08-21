<?php


namespace App\Repositories\Eloquent;


use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Exceptions\RepositoryException;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryInterface {

    /** @var Model  */
    protected $model;

    public function __construct(Container $app)
    {
        $this->makeModel($this->modelClass(), $app);
    }

    /**
     * @return mixed
     */
    abstract protected function modelClass();

    /**
     * @param $modelClass
     * @param Container $app
     * @return Model
     * @throws RepositoryException
     */
    public function makeModel($modelClass, Container $app)
    {
        $model = $app->make($modelClass);
        if (!$model instanceof Model)
            throw new RepositoryException("Class {$model} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        return $this->model = $model;
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        return $this->model->get($columns);
    }

    /**
     * @param array $relations
     * @return $this
     */
    public function with(array $relations)
    {
        $this->model = $this->model->with($relations);
        return $this;
    }

    /**
     * @param  string $value
     * @param  string $key
     * @return array
     */
    public function lists($value, $key = null)
    {
        $lists = $this->model->lists($value, $key);
        if (is_array($lists)) {
            return $lists;
        }
        return $lists->all();
    }

    /**
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginate($perPage = 24, $columns = ['*'])
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param array $identities
     * @param array $createAttributes
     * @param array $updateAttributes
     * @return mixed|static
     */
    public function createOrUpdate(array $identities, array $createAttributes, $updateAttributes = [])
    {
        $model = $this->model->where($identities)->first();
        if ($model && count($updateAttributes) > 0) {
            // update model
            $this->update($updateAttributes, $model->id);

            return $model->fresh();

        } else if (!$model) {
            $createAttributes = array_merge($createAttributes, $updateAttributes);
            // create new model
            return $this->model->create($createAttributes);

        } else {
            return $model;
        }
    }

    /**
     * save a model without massive assignment
     *
     * @param array $data
     * @return bool
     */
    public function saveModel(array $data)
    {
        foreach ($data as $k => $v) {
            $this->model->$k = $v;
        }
        return $this->model->save();
    }

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return mixed
     */
    public function update(array $data, $id, $attribute = "id")
    {
        return $this->model->where($attribute, '=', $id)->update($data);
    }

    /**
     * @param  array $data
     * @param  $id
     * @return mixed
     */
    public function updateRich(array $data, $id)
    {
        if (!($model = $this->model->find($id))) {
            return false;
        }
        return $model->fill($data)->save();
    }

    /**
     * @param $conditions
     * @param $data
     * @return mixed
     */
    public function updateBy($conditions, $data)
    {
        return $this->model->where($conditions)->update($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function deleteBy($attribute, $value)
    {
        return $this->model->where($attribute, '=', $value)->delete();
    }

    /**
     * @param $where
     * @return bool|null
     * @throws \Exception
     */
    public function deleteWhere($where)
    {
        $query = $this->buildWhereQuery($where);

        return $query->delete();
    }

    /**
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function count($attribute, $value)
    {
        return $this->model->where($attribute, '=', $value)->count();
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = ['*'])
    {
        return $this->model->where($attribute, '=', $value)->first($columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findAllBy($attribute, $value, $columns = ['*'])
    {
        return $this->model->where($attribute, '=', $value)->get($columns);
    }

    /**
     * @param $where
     * @return Model
     */
    protected function buildWhereQuery($where) {
        $model = $this->model;
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                if (count($value) === 2) {
                    list($operator, $search) = $value;
                    if ($operator == 'in') {
                        $model = $model->whereIn($field, $search);
                    } elseif ($operator == 'not in') {
                        $model = $model->whereNotIn($field, $search);
                    } else {
                        $model = $model->where($field, $operator, $search);
                    }
                } elseif (count($value) === 3) {
                    list($key, $operator, $search) = $value;
                    if ($key == 'or') {
                        $model = $model->orWhere($field, $operator, $search);
                    }
                }
            } else if ($value === null) {
                $model = $model->whereNull($field);
            } else {
                $model = $model->where($field, '=', $value);
            }
        }

        return $model;
    }

    /**
     * @param $where
     * @param bool $count
     * @param array $order
     * @param null $limit
     * @param array $columns
     * @return mixed
     */
    public function queryWhere($where, $count = false, $order = [], $limit = null, $columns = ['*'])
    {
        $model = $this->buildWhereQuery($where);

        if ($count) {
            return $model->count();
        }
        if (count($order) > 0) {
            $model = $model->orderBy($order[0], $order[1]);
        }
        if ($limit) {
            $model = $model->limit($limit);
        }
        return $model->get($columns);
    }
    
    public function firstOrCreate($data) {
        
        return $this->model->firstOrCreate($data);
    }
}