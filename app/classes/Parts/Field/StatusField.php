<?php
/**
 * Created by PhpStorm.
 * User: letuananh
 * Date: 2/21/18
 * Time: 16:14:11
 */

namespace Classes\Parts\Field;
use Classes\Parts\Field\MultiOptionsField;

class StatusField extends MultiOptionsField
{
    CONST STATUS_NORMAL = 1;
    CONST STATUS_OVER_PV_STOP = 2;
    CONST STATUS_DEMO = 3;

    public static $options = array(
        self::STATUS_NORMAL => "公開中",
        self::STATUS_OVER_PV_STOP => "PV上限のため停止",
        self::STATUS_DEMO => "非公開"
    );
}