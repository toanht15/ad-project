<?php


namespace Classes\Parts\Field;


class SortField extends MultiOptionsField
{
    CONST SORT_BY_REGISTERED_ORDER      = 1;
    CONST SORT_BY_POPULARITY            = 2;
    CONST SORT_BY_SELECTED_ORDER        = 3;
    CONST SORT_BY_POST_PUBLISH_ORDER    = 4;

    public static $options = [
        self::SORT_BY_REGISTERED_ORDER  => '登録順',
        self::SORT_BY_POST_PUBLISH_ORDER=> '投稿日順',
        self::SORT_BY_POPULARITY        => '人気順',
        self::SORT_BY_SELECTED_ORDER    => '手動設定順'
    ];
}