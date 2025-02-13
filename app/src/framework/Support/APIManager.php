<?php

namespace App\Support;

use Exception;
use JetBrains\PhpStorm\NoReturn;

class APIManager
{
    /**
     * داده‌های دریافتی از درخواست.
     *
     * @var array
     */
    private array $request;

    /**
     * تعیین می‌کند که آیا بررسی XSS انجام شود یا خیر.
     *
     * @var bool
     */
    private bool $checkXSS;

    /**
     * تعیین می‌کند که آیا توکن معتبر بررسی شود یا خیر.
     *
     * @var bool
     */
    private bool $checkToken;

    /**
     * لیست پارامترهای اجباری.
     *
     * @var array
     */
    private array $mandatoryParams = [];

    /**
     * لیستی از کلیدهایی که از بررسی XSS مستثنی هستند.
     *
     * @var array
     */
    private array $dontXSS = [];

    /**
     * ایجاد یک نمونه از APIManager.
     *
     * @param bool $checkXSS آیا بررسی XSS انجام شود.
     * @param bool $checkToken آیا توکن بررسی شود.
     */
    public function __construct(bool $checkXSS = true, bool $checkToken = true)
    {
        $this->request    = $this->getInput();
        $this->checkXSS   = $checkXSS;
        $this->checkToken = $checkToken;
    }

    /**
     * تنظیم دستی داده‌های درخواست.
     *
     * @param array $request داده‌های درخواست
     * @return self
     */
    public function setInput(array $request): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * دریافت داده‌های ورودی از php://input به صورت آرایه.
     *
     * @return array
     */
    private function getInput(): array
    {
        $input = file_get_contents('php://input');
        if ($input === false) {
            return [];
        }
        $data = json_decode($input, true);
        return is_array($data) ? $data : [];
    }

    /**
     * اعتبارسنجی درخواست ورودی و اجرای callback در صورت معتبر بودن.
     *
     * @param callable $callback تابعی که در صورت موفقیت اجرا می‌شود.
     * @throws Exception در صورت بروز خطا در اعتبارسنجی.
     */
    public function handle(callable $callback): void
    {
        if (empty($this->request)) {
            $this->forbidden();
        }

        // اعتبارسنجی پارامترهای اجباری
        foreach ($this->mandatoryParams as $param) {
            if (!array_key_exists($param, $this->request)) {
                self::respond(false, 'پارامترهای ارسالی کامل نیستند');
            }
        }

        // اعتبارسنجی توکن
        if (!$this->isValidToken()) {
            self::respond(false, 'توکن منقضی شده است. صفحه را مجدد بارگزاری کنید.');
        }

        // بررسی حملات XSS در صورت فعال بودن
        if ($this->checkXSS && $this->hasXSS()) {
            self::respond(false, 'اطلاعات وارد شده مجاز نیستند');
        }

        // در صورت معتبر بودن، تابع callback با داده‌های درخواست اجرا می‌شود
        $callback($this->request);
    }

    /**
     * اعتبارسنجی توکن CSRF موجود در درخواست.
     *
     * @return bool
     */
    private function isValidToken(): bool
    {
        if (!$this->checkToken) {
            return true;
        }

        $token = $this->request['token'] ?? null;
        if ($token === null) {
            return false;
        }

        // فرض بر این است که session() یک شیء مدیریت سشن را برمی‌گرداند
        return session()->has('csrf_token') && session()->get('csrf_token') === $token;
    }

    /**
     * بررسی وجود حملات XSS در داده‌های ورودی.
     *
     * @return bool در صورت شناسایی XSS، true برگردانده می‌شود.
     */
    private function hasXSS(): bool
    {
        $dataToCheck = $this->request;

        // حذف کلیدهایی که نیازی به بررسی XSS ندارند
        foreach ($this->dontXSS as $key) {
            if (isset($dataToCheck[$key])) {
                unset($dataToCheck[$key]);
            }
        }

        // فرض بر این است که تابع hasXSS() در جای دیگری تعریف شده است
        return hasXSS($dataToCheck);
    }

    /**
     * تعیین پارامترهای اجباری برای درخواست.
     *
     * @param mixed ...$params لیست پارامترهای اجباری
     * @return self
     */
    public function validateParameters(...$params): self
    {
        $this->mandatoryParams = $params;
        return $this;
    }

    /**
     * تعیین کلیدهایی که از بررسی XSS مستثنی می‌شوند.
     *
     * @param mixed ...$params لیست کلیدها
     * @return self
     */
    public function dontCheckXSSValidation(...$params): self
    {
        $this->dontXSS = $params;
        return $this;
    }

    /**
     * ارسال پاسخ JSON و خاتمه اجرای اسکریپت.
     *
     * @param bool $status وضعیت پاسخ.
     * @param string $message پیام پاسخ.
     * @param array $data داده‌های اضافی.
     */
    #[NoReturn]
    public static function respond(bool $status, string $message, array $data = []): void
    {
        $response = [
            'status'  => $status,
            'message' => $message,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        header('Content-Type: application/json');
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * ارسال پاسخ Forbidden (403) و خاتمه اجرای اسکریپت.
     */
    #[NoReturn]
    private function forbidden(): void
    {
        // فرض بر این است که تابع showError در جای دیگری تعریف شده است
        showError(403, 403);
    }
}