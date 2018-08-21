<?php

namespace Classes\Parts\Field;
Use Classes\Parts\Serializable;

class Field implements Serializable
{
    protected $data;

    public function __toString(){
        return $this->str();
    }

    public function __construct($data)
    {
        $this->serialize($data);
    }

    public function str()
    {
        return $this->data;
    }

    public function data()
    {
        return $this->data;
    }

    public function __get($name)
    {
        if ($name == 'data') {
            return $this->data();
        }
        if ($name == 'str') {
            return $this->str();
        }
    }

    public function serialize($data)
    {
        $this->data = $data;
    }

    public function deserialize()
    {
        return $this->data;
    }
}

