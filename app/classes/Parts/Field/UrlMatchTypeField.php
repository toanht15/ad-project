<?php


namespace Classes\Parts\Field;


class UrlMatchTypeField extends MultiOptionsField
{
    CONST URL_MATCH_TYPE_PARTIAL            = 1;
    CONST URL_MATCH_TYPE_REGULAR_EXPRESSION = 2;

    public static $urlMatchTypeList = array(
        self::URL_MATCH_TYPE_PARTIAL            => "部分一致",
        self::URL_MATCH_TYPE_REGULAR_EXPRESSION => "正規表現",
    );
}