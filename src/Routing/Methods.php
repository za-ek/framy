<?php
namespace Zaek\Framy\Routing;

class Methods {
    /**
     * List of available HTTP methods
     * @var array
     */
    protected static $available_methods = [
        'GET',
        'HEAD',
        'POST',
        'PUT',
        'DELETE',
        'CONNECT',
        'OPTIONS',
        'TRACE',
        'CLI',
        'WEB',
    ];

    public static function list() { return self::$available_methods; }

    public static function overlaps($method, $in)
    {
        if($method === $in) return true;

        if($in === "WEB") {
            return in_array($method, [
                'GET',
                'POST',
                'HEAD',
                'OPTIONS',
            ]);
        }

        return false;
    }
}