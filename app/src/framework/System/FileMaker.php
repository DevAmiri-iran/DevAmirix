<?php

namespace App\System;

trait FileMaker
{
    /**
     * لیست فایل‌هایی که باید ایجاد شوند به همراه محتوای هر فایل.
     *
     * @var array<string, string>
     */
    protected static array $files = [];

    /**
     * تولید فایل‌های پیکربندی برای سرور Apache یا LiteSpeed.
     *
     * @return void
     */
    protected static function generateHtaccess(): void
    {
        $baseHtaccess = <<<HTACCESS
<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteCond %{REQUEST_URI}::$1 ^(.*?/)(.*)::\\2$
    RewriteRule ^(.*)$ - [E=BASE:%1]

    RewriteCond %{HTTP_HOST} !^$
    RewriteCond %{HTTP_HOST} ^(.+)$
    RewriteRule ^(.*)$ %{ENV:BASE}/public/\$1 [L]

    RewriteRule ^$ %{ENV:BASE}/public/ [L]

    RewriteRule ^(.*)$ %{ENV:BASE}/public/\$1 [L]

    <IfModule mime_module>
        AddHandler application/x-httpd-ea-php8 .php .php8 .phtml
    </IfModule>
</IfModule>
HTACCESS;

        $publicHtaccess = <<<HTACCESS
<IfModule mod_rewrite.c>
    RewriteEngine On

    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    Header set Access-Control-Allow-Origin "*"

    RewriteCond %{REQUEST_FILENAME} -f
    RewriteCond %{REQUEST_FILENAME} "\.(css|js)$" [NC]
    RewriteRule ^(.*)$ index.php?file=$1 [QSA,L,B]
    
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-l
    RewriteRule ^(.*)$ index.php?route=/\$1 [QSA,L,B]
</IfModule>
HTACCESS;

        static::$files[base_path('.htaccess')] = $baseHtaccess;
        static::$files[public_path('.htaccess')] = $publicHtaccess;
    }

    /**
     * تولید فایل .env با تنظیمات مورد نیاز برنامه.
     *
     * @return void
     */
    protected static function generateEnv(): void
    {
        // دریافت URL فعلی و تولید APP_KEY تصادفی (تابع random() باید تعریف شده باشد)
        $url = getCurrentUrl();
        $key = str_replace('=', '', base64_encode(random()))
            . '5'
            . str_replace('=', '', base64_encode(random()));

        // خواندن مقادیر محیطی یا استفاده از مقدار پیش‌فرض
        $appDebug      = env('APP_DEBUG', 'false');
        $dbDriver      = env('DB_DRIVER', 'mysql');
        $dbHost        = env('DB_HOST', '127.0.0.1');
        $dbDatabase    = env('DB_DATABASE', '');
        $dbUsername    = env('DB_USERNAME', '');
        $dbPassword    = env('DB_PASSWORD', '');
        $dbCharset     = env('DB_CHARSET', 'utf8');
        $dbCollation   = env('DB_COLLATION', 'utf8_unicode_ci');
        $dbPrefix      = env('DB_PREFIX', '');

        $sessionName      = env('SESSION_NAME', 'DevAmirix');
        $sessionDriver    = env('SESSION_DRIVER', 'files');
        $sessionLifetime  = env('SESSION_LIFETIME', '43200');
        $sessionPath      = env('SESSION_PATH', '/');
        $sessionSavePath  = env('SESSION_SAVE_PATH', '/tmp/sessions');
        $sessionDomain    = env('SESSION_DOMAIN', 'null');
        $sessionSecure    = env('SESSION_SECURE', 'false'); // اصلاح مقدار پیش‌فرض
        $sessionHttpOnly  = env('SESSION_HTTPONLY', 'true');

        $mailMailer     = env('MAIL_MAILER', 'log');
        $mailHost       = env('MAIL_HOST', '127.0.0.1');
        $mailPort       = env('MAIL_PORT', '2525');
        $mailUsername   = env('MAIL_USERNAME', 'null');
        $mailPassword   = env('MAIL_PASSWORD', 'null');
        $mailEncryption = env('MAIL_ENCRYPTION', 'null');
        $mailLogChannel = env('MAIL_LOG_CHANNEL', 'storage/logs/email/');

        $envContent = <<<ENV
APP_NAME=DevAmirix
APP_URL={$url}
APP_KEY={$key}
APP_DEBUG={$appDebug}

DB_DRIVER={$dbDriver}
DB_HOST={$dbHost}
DB_DATABASE={$dbDatabase}
DB_USERNAME={$dbUsername}
DB_PASSWORD={$dbPassword}
DB_CHARSET={$dbCharset}
DB_COLLATION={$dbCollation}
DB_PREFIX={$dbPrefix}

SESSION_NAME={$sessionName}
SESSION_DRIVER={$sessionDriver}
SESSION_LIFETIME={$sessionLifetime}
SESSION_PATH={$sessionPath}
SESSION_SAVE_PATH={$sessionSavePath}
SESSION_DOMAIN={$sessionDomain}
SESSION_SECURE={$sessionSecure}
SESSION_HTTPONLY={$sessionHttpOnly}

MAIL_MAILER={$mailMailer}
MAIL_HOST={$mailHost}
MAIL_PORT={$mailPort}
MAIL_USERNAME={$mailUsername}
MAIL_PASSWORD={$mailPassword}
MAIL_ENCRYPTION={$mailEncryption}
MAIL_LOG_CHANNEL={$mailLogChannel}
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"
ENV;

        static::$files[base_path('.env')] = $envContent;
    }

    /**
     * تعیین فایل‌هایی که باید تولید شوند بر اساس اطلاعات سرور.
     *
     * @return void
     */
    protected static function handleFiles(): void
    {
        // تولید فایل .env
        static::generateEnv();

        // بررسی نوع سرور و در صورت Apache یا LiteSpeed، تولید فایل .htaccess
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';
        if (stripos($serverSoftware, 'Apache') !== false || stripos($serverSoftware, 'LiteSpeed') !== false) {
            static::generateHtaccess();
        }
    }

    /**
     * شروع فرآیند تولید فایل‌ها.
     *
     * @return void
     * @throws \RuntimeException در صورت عدم موفقیت در نوشتن فایل.
     */
    public static function startFileMaker(): void
    {
        static::handleFiles();

        foreach (static::$files as $path => $content) {
            // اطمینان از وجود دایرکتوری مربوطه
            $directory = dirname($path);
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                    throw new \RuntimeException("Failed to create directory: {$directory}");
                }
            }

            // نوشتن محتوا در فایل و در صورت شکست، پرتاب استثنا
            if (file_put_contents($path, $content) === false) {
                throw new \RuntimeException("Failed to write to file: {$path}");
            }
        }
    }
}