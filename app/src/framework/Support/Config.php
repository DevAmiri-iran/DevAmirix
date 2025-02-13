<?php

namespace App\Support;

class Config
{
    /**
     * آرایه‌ای برای نگهداری تنظیمات بارگذاری شده.
     *
     * @var array
     */
    private static array $config = [];

    /**
     * دریافت مقدار پیکربندی با استفاده از dot notation.
     *
     * مثال: Config::get('app.locale')
     *
     * @param string $key کلید پیکربندی.
     * @param mixed $default مقدار پیش‌فرض در صورت عدم وجود کلید.
     * @return mixed مقدار پیکربندی یا مقدار پیش‌فرض.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $file = array_shift($segments);

        self::load($file);

        if (!isset(self::$config[$file])) {
            return $default;
        }

        if (empty($segments)) {
            return self::$config[$file];
        }

        return self::arrayGet(self::$config[$file], $segments, $default);
    }

    /**
     * تعیین مقدار پیکربندی با استفاده از dot notation.
     *
     * مثال: Config::set('app.debug', true)
     *
     * @param string $key کلید پیکربندی.
     * @param mixed $value مقداری که باید تنظیم شود.
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $file = array_shift($segments);

        // ابتدا فایل پیکربندی را بارگذاری یا در صورت عدم وجود، آرایه اولیه تعریف می‌کنیم.
        self::load($file);
        if (!isset(self::$config[$file])) {
            self::$config[$file] = [];
        }

        if (empty($segments)) {
            self::$config[$file] = $value;
            return;
        }

        self::$config[$file] = self::arraySet(self::$config[$file], $segments, $value);
    }

    /**
     * بررسی می‌کند که کلید پیکربندی وجود دارد یا خیر.
     *
     * @param string $key کلید پیکربندی.
     * @return bool در صورت وجود کلید، true و در غیر این صورت false.
     */
    public static function has(string $key): bool
    {
        $segments = explode('.', $key);
        $file = array_shift($segments);

        self::load($file);

        if (!isset(self::$config[$file])) {
            return false;
        }

        if (empty($segments)) {
            return true;
        }

        $array = self::$config[$file];
        foreach ($segments as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * حذف یک کلید پیکربندی با استفاده از dot notation.
     *
     * @param string $key کلید پیکربندی.
     * @return void
     */
    public static function forget(string $key): void
    {
        $segments = explode('.', $key);
        $file = array_shift($segments);

        self::load($file);

        if (!isset(self::$config[$file])) {
            return;
        }

        if (empty($segments)) {
            unset(self::$config[$file]);
            return;
        }

        $array = &self::$config[$file];
        while (count($segments) > 1) {
            $segment = array_shift($segments);
            if (!isset($array[$segment]) || !is_array($array[$segment])) {
                return;
            }
            $array = &$array[$segment];
        }

        unset($array[array_shift($segments)]);
    }

    /**
     * دریافت تمامی تنظیمات بارگذاری شده.
     *
     * @return array
     */
    public static function all(): array
    {
        return self::$config;
    }

    /**
     * بارگذاری فایل پیکربندی در صورت وجود.
     *
     * @param string $file نام فایل (بدون پسوند) پیکربندی.
     * @return void
     */
    protected static function load(string $file): void
    {
        // در صورتی که تنظیمات این فایل قبلاً بارگذاری شده باشد، کاری انجام نمی‌دهیم.
        if (isset(self::$config[$file])) {
            return;
        }

        $path = app_path("config/{$file}.php");
        if (file_exists($path)) {
            // فایل پیکربندی باید آرایه‌ای از تنظیمات را برگرداند.
            self::$config[$file] = include $path;
        }
    }

    /**
     * دریافت مقدار از آرایه به صورت ریکرسیو با استفاده از کلیدهای داده شده.
     *
     * @param array $array آرایه‌ای که باید جستجو شود.
     * @param array $keys آرایه کلیدها برای پیمایش.
     * @param mixed $default مقدار پیش‌فرض در صورت عدم وجود کلید.
     * @return mixed
     */
    protected static function arrayGet(array $array, array $keys, mixed $default = null): mixed
    {
        foreach ($keys as $key) {
            if (is_array($array) && array_key_exists($key, $array)) {
                $array = $array[$key];
            } else {
                return $default;
            }
        }
        return $array;
    }

    /**
     * تعیین مقدار در یک آرایه به صورت ریکرسیو با استفاده از کلیدهای داده شده.
     *
     * @param array $array آرایه اصلی.
     * @param array $keys آرایه کلیدها برای پیمایش.
     * @param mixed $value مقداری که باید تنظیم شود.
     * @return array آرایه تغییر یافته.
     */
    protected static function arraySet(array $array, array $keys, mixed $value): array
    {
        $key = array_shift($keys);
        if (empty($keys)) {
            $array[$key] = $value;
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array[$key] = self::arraySet($array[$key], $keys, $value);
        }
        return $array;
    }
}