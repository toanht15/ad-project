<?php
/**
 * Created by PhpStorm.
 * User: letuananh
 * Date: 3/1/18
 * Time: 14:34:16
 */

namespace App\classes\Parts;


use Classes\Parts\Obj;
use Classes\Parts\Field\FieldFactory;

class Page extends Obj
{

    public function serialize($data)
    {
        foreach ($data as $fieldName => $fieldData) {
            $this->fields[$fieldName] = FieldFactory::make($fieldName, $fieldData);
        }

        if(isset($data ['crawled_flg']) && isset($data ['crawled_flg'])){
            if($data ['crawled_flg'] == 1 && $data['stop_flg'] == 0)
                $status = 2;
            if($data ['crawled_flg'] == 0)
                $status = 1;
            if($data ['crawled_flg'] == 1 && $data['stop_flg'] == 1)
                $status = 3;

            $this->fields['status'] = FieldFactory::make('pagestatus', $status);
        }
    }
}