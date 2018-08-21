<?php
/**
 * Created by PhpStorm.
 * User: letuananh
 * Date: 2/21/18
 * Time: 16:13:57
 */

namespace Classes\Parts\Field;

class TemplateField extends MultiOptionsField
{
    CONST TYPE_REPLACE = 1;
    CONST TYPE_SLIDER = 2;
    CONST TYPE_MEDIA = 3;
    CONST TYPE_LIST = 4;
    CONST TYPE_CART_REMINDER = 5;

    public static $options = array(
        self::TYPE_REPLACE => "画像差し替え",
        self::TYPE_SLIDER => "スライダー",
        self::TYPE_LIST => "リスト",
        self::TYPE_MEDIA => "一覧",
        self::TYPE_CART_REMINDER => "カゴ落ち",
    );
}