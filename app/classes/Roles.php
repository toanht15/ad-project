<?php

namespace Classes;


class Roles {

    const ADMIN         = "admin";
    const USER          = "user";
    const ADVERTISER    = "advertiser";
    const AGENT         = "agent";
    const AA_USER       = "aa_user";

    const PERMISSION_VIEW   = "view";
    const PERMISSION_UPDATE = "update";
    const PERMISSION_DELETE = "delete";

    public static function getAdvertiserRoles()
    {
        return [
            static::ADVERTISER,
            static::AGENT,
            static::AA_USER
        ];
    }
}