<?php

namespace Classes;

class Constants
{
    const ADMIN = 'admin';
    const ERROR_MESSAGE = 'error';
    const INFO_MESSAGE  = 'info';
    const WARNING_MESSAGE = 'warning';
    const TERMS_OF_USE = ' https://www.aainc.co.jp/service/letro/terms.html';

    const MEDIA_ALL = 0;
    const MEDIA_FACEBOOK = 1;
    const MEDIA_TWITTER = 2;

    const VIDEO_TYPE_TEXT = 'video';
    const IMG_TYPE_TEXT = 'image';
    const CAROUSEL_TYPE_TEXT = 'carousel';

    const TW_LINK_TWEET = 'tweet';
    const TW_CARD_WEBSITE = 'website';
    const TW_CARD_VIDEO_WEBSITE = 'video_website';
    const TW_CARD_IMG_APP_DOWNLOAD = 'image_app_download';
    const TW_CARD_VIDEO_APP_DOWNLOAD = 'video_app_download';
    const TW_CARD_IMG_CONVERSION = 'image_conversation';
    const TW_CARD_VIDEO_CONVERSION = 'video_conversation';

    public static $twitterVideoCreativeType = [
        self::TW_LINK_TWEET => 'リンクツイート',
        self::TW_CARD_VIDEO_WEBSITE => 'ビデオウェブサイトカード',
        self::TW_CARD_VIDEO_APP_DOWNLOAD => 'ビデオアプリカード',
        self::TW_CARD_VIDEO_CONVERSION => 'ビデオカンバセーショナルカード'
    ];

    public static $twitterImageCreativeType = [
        self::TW_LINK_TWEET => 'リンクツイート',
        self::TW_CARD_WEBSITE => '画像ウェブサイトカード',
        self::TW_CARD_IMG_APP_DOWNLOAD => 'イメージアプリカード',
        self::TW_CARD_IMG_CONVERSION => 'イメージカンバセーショナルカード'
    ];

    CONST TYPE_DEFAULT          = 0;
    CONST TYPE_CART             = 1;
    CONST TYPE_BUY              = 2;
    CONST TYPE_PRODUCT          = 3;
    CONST TYPE_LP               = 4;

    public static $cvTypes = array(
        self::TYPE_DEFAULT => "指定なし",
        self::TYPE_CART => "カート",
        self::TYPE_BUY => "購入完了",
        self::TYPE_PRODUCT => "商品詳細",
        self::TYPE_LP => "LP",
    );

    CONST TOASTR_ERROR = 'toastrErrMsg';
}
