<?php

namespace Classes\Parts;


use App\classes\PartAPIClient;
use App\classes\Parts\SaveAble;
use Classes\Parts\Field\FieldFactory;

class Site extends Obj implements SaveAble
{
    public function save()
    {
    }

    const PLAN_TRIAL = '1';
    const PLAN_SMALL = '2';
    const PLAN_STANDARD = '3';




    public function __get($name)
    {
        if (isset($this->fields[$name])) {
            return $this->fields[$name]->data;
        } else if ($name == 'max_parts') {
            switch ($this->plan_id) {
                case self::PLAN_TRIAL:
                    return 1;
                case self::PLAN_SMALL:
                    return 5;
                case self::PLAN_STANDARD:
                    return 10;
                default:
                    return 1;
            }
        } else {
            throw new FieldNotFoundException();
        }
//            throw new FieldNotFoundException();
    }


}