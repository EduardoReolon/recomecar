<?php

class RouteParams {
    private static $params = [];

    public static function set($key, $value) {
        self::$params[$key] = $value;
    }

    public static function get($key) {
        return isset(self::$params[$key]) ? self::$params[$key] : null;
    }
}