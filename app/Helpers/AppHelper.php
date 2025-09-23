<?php

if (!function_exists('app_version')) {
    /**
     * Get the application version.
     *
     * @return string
     */
    function app_version()
    {
        return config('app.version', '1.0.0');
    }
}

if (!function_exists('app_build')) {
    /**
     * Get the application build number based on current date.
     *
     * @return string
     */
    function app_build()
    {
        return date('Y.m.d');
    }
}

if (!function_exists('app_info')) {
    /**
     * Get complete application information.
     *
     * @return array
     */
    function app_info()
    {
        return [
            'name' => config('app.name'),
            'version' => app_version(),
            'build' => app_build(),
            'environment' => config('app.env'),
            'debug' => config('app.debug'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
        ];
    }
}