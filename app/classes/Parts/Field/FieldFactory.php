<?php

namespace Classes\Parts\Field;

use Classes\Parts\Image;

class FieldFactory
{
    static public function make($type, $data)
    {
        switch ($type) {
            case 'template' :
                return new TemplateField($data);
            case 'pagestatus':
                return new PageStatusField($data);
            case 'status':
                return new StatusField($data);
            case 'sort':
                return new SortField($data);
            case 'images':
                return new MultiRelationField(Image::class, $data);
            default:
                return new Field($data);
        }
    }
}