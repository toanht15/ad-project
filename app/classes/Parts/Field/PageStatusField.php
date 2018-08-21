<?php
/**
 * Created by PhpStorm.
 * User: letuananh
 * Date: 3/1/18
 * Time: 15:28:34
 */

namespace Classes\Parts\Field;


class PageStatusField extends MultiOptionsField
{
    CONST STATUS_NOT_CRAWLED = 1;
    CONST STATUS_CRAWLED = 2;
    CONST STATUS_STOPPED = 3;

    public static $options = array(
        self::STATUS_NOT_CRAWLED => "未取得",
        self::STATUS_CRAWLED => "取得済",
        self::STATUS_STOPPED => "取得失敗"
    );
}