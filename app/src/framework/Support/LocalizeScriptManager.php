<?php

namespace App\Support;

use Exception;

class LocalizeScriptManager
{
    private static ?LocalizeScriptManager $instance = null;
    private array $localizedData = [];
    private array $defaultData = [];
    private bool $useTag = true;
    private bool $immediatePrint = false;

    /**
     * دریافت نمونه Singleton از LocalizeScriptManager
     *
     * @return LocalizeScriptManager
     */
    public static function getInstance(): LocalizeScriptManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->defaultData['devamiri']['url'] = url();
        }
        return self::$instance;
    }

    /**
     * اضافه کردن داده‌های محلی‌سازی شده برای یک handle خاص
     *
     * @param string $handle
     * @param string $objectName
     * @param array $data
     * @return void
     */
    public function set(string $handle, string $objectName, array $data): void
    {
        $this->localizedData[$handle][$objectName] = $data;
    }

    /**
     * دریافت داده‌های محلی‌سازی شده یک handle
     *
     * @param string $handle
     * @return array
     */
    public function get(string $handle): array
    {
        return $this->localizedData[$handle] ?? [];
    }

    /**
     * حذف یک handle از لیست داده‌های محلی‌سازی شده
     *
     * @param string $handle
     * @return void
     */
    public function remove(string $handle): void
    {
        unset($this->localizedData[$handle]);
    }

    /**
     * تولید خروجی اسکریپت‌های محلی‌سازی شده
     *
     * @return string|null
     */
    public function output(): ?string
    {
        return $this->generateOutput($this->localizedData);
    }

    /**
     * تولید خروجی اسکریپت‌های پیش‌فرض
     *
     * @return string|null
     */
    public function outputDefault(): ?string
    {
        return $this->generateOutput($this->defaultData);
    }

    /**
     * فعال یا غیرفعال کردن استفاده از تگ <script>
     *
     * @return self
     */
    public function withoutTag(): self
    {
        $this->useTag = false;
        return $this;
    }

    /**
     * فعال کردن چاپ مستقیم به خروجی (echo)
     *
     * @return self
     */
    public function print(): self
    {
        $this->immediatePrint = true;
        return $this;
    }

    /**
     * تولید خروجی جاوااسکریپت از داده‌های ورودی
     *
     * @param array $dataArray
     * @return string|null
     */
    private function generateOutput(array $dataArray): ?string
    {
        if (empty($dataArray)) {
            return null;
        }

        $output = '';
        foreach ($dataArray as $handle => $scripts) {
            $jsonData = json_encode($scripts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            $scriptTag = $this->useTag ? "<script>" : "";
            $scriptContent = "const {$handle} = {$jsonData};";
            $scriptTagClose = $this->useTag ? "</script>" : "";

            $generatedScript = "{$scriptTag}{$scriptContent}{$scriptTagClose}\n";

            if ($this->immediatePrint) {
                echo $generatedScript;
            } else {
                $output .= $generatedScript;
            }
        }

        // بازنشانی تنظیمات
        $this->useTag = true;
        return $this->immediatePrint ? null : $output;
    }
}