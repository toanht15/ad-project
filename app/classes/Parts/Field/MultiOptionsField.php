<?php
/**
 * Created by PhpStorm.
 * User: letuananh
 * Date: 2/21/18
 * Time: 16:07:08
 */

namespace Classes\Parts\Field;


abstract class MultiOptionsField extends Field
{
    public static $options = [];

    public function __construct($data)
    {
        $this->serialize($data);
    }

    public function str()
    {
        $class = get_called_class();
        return $class::$options[$this->data];
    }

    public function data()
    {
        return $this->data;
    }

    public function deserialize()
    {
        return $this->data();
    }

}


