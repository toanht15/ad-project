<?php
/**
 * Created by PhpStorm.
 * User: letuananh
 * Date: 2/21/18
 * Time: 12:55:19
 */

namespace Classes\Parts;


use Classes\Parts\Exceptions\FieldNotFoundException;
use Classes\Parts\Field\FieldFactory;
use Classes\Parts\Field\MultiOptionsField;

abstract class Obj implements Serializable
{
    public $data = [];

    public function __construct($data)
    {
        $this->data = $data;
        $this->serialize($data);
    }


    public $fields = [];

    public function __get($name)
    {
        if (isset($this->fields[$name])) {
            return $this->fields[$name]->data;
        } else {
            throw new FieldNotFoundException();
        }
//            throw new FieldNotFoundException();
    }

    public function __set($name, $value)
    {
        $this->fields[$name] = FieldFactory::make($name, $value);
    }

    public function deserialize()
    {
        $data = [];
        foreach ($this->fields as $key => $field) {
            $data[$key] = $field->deserialize();
            if (is_subclass_of($field, MultiOptionsField::class)) {
                $data['__' . $key . '__str'] = $field->str;
            }

        }
        return $data;
    }


    public function serialize($data)
    {
        foreach ($data as $fieldName => $fieldData) {
            $this->fields[$fieldName] = FieldFactory::make($fieldName, $fieldData);
        }
    }

    // Model function
    public function toArray()
    {
        return $this->deserialize();
    }
}