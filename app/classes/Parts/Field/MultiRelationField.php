<?php
/**
 * Created by PhpStorm.
 * User: letuananh
 * Date: 2/21/18
 * Time: 16:08:33
 */

namespace Classes\Parts\Field;

class MultiRelationField extends Field
{
    protected $listObjs = [];
    protected $classType;

    public function __construct($class, $data)
    {
        $this->classType = $class;
        $this->serialize($data);
    }


    public function add(Obj $obj)
    {
        $this->listObjs[] = $obj;
    }

    public function serialize($data)
    {
        foreach ($data as $eachObjData) {
            $obj = new $this->classType ($eachObjData);
            $this->listObjs[] = $obj;
        }
        $this->data = collect($this->listObjs);
    }

    public function deserialize()
    {
        $data = [];
        foreach ($this->listObjs as $obj) {
            $data[] = $obj->deserialize();
        }
        return $data;
    }
}