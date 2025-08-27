<?php

namespace VaahCms\Modules\Store\Traits;

trait ApiAuthUser
{
    public static function getApiAuthUserId()
    {
        $route_prefix = request()->route() ? request()->route()->getPrefix() : null;
        $is_api_request = $route_prefix && str_starts_with($route_prefix, 'api/store') && auth('api')->check();
        return $is_api_request ? auth('api')->id() : null;
    }
}
