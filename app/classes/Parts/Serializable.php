<?php
/**
 * Created by PhpStorm.
 * User: letuananh
 * Date: 2/21/18
 * Time: 10:24:21
 */

namespace Classes\Parts;


interface Serializable
{
    public function serialize($data);
    public function deserialize();
}