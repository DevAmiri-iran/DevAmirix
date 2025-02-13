<?php

use App\Artisan;
use App\Encryption\Crypt;
use App\Encryption\JWT;
use App\Mail\Mailer;
use App\Support\Config;
use App\Support\CookieManager;
use App\Support\LocalizeScriptManager;
use App\Support\Option;
use App\Support\Translator;
use App\System;
use App\View;
use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\Session\Session;

if (!function_exists('view')) {
    /**
     * Render a view.
     *
     * @param string $viewName The name of the view file to render.
     * @param array $data The data to pass to the view.
     * @throws Exception If the view cannot be rendered.
     */
    function view(string $viewName, array $data = []): void
    {
        View::render($viewName, $data);
    }
}

if (!function_exists('api'))
{
    /**
     * Include an API view.
     *
     * @param string $view The name of the API view to include.
     * @param array $data The data to pass to the API view.
     * @throws InvalidArgumentException If the API view file is not found.
     */
    function api(string $view, array $data = []): void
    {
        $path = resources_path("api/$view.php");
        if (is_file($path))
        {
            require_once $path;
        }
        else
        {
            throw new \InvalidArgumentException("File ". basename($view) .".php could not be found in directory ". dirname($path));
        }
    }
}

if (!function_exists('trans')) {
    /**
     * Translate the given key.
     *
     * @param string $key The translation key.
     * @param mixed|null $default The default value if the translation key does not exist.
     * @return string The translated value.
     */
    function trans(string $key, mixed $default = null): string
    {
        return Translator::trans($key, $default);
    }
}

if (!function_exists('__')) {
    /**
     * Alias for the trans() function.
     *
     * @param string $key The translation key.
     * @param mixed|null $default The default value if the translation key does not exist.
     * @return string The translated value.
     */
    function __(string $key, mixed $default = null): string
    {
        return trans($key, $default);
    }
}

if (!function_exists('config')) {
    /**
     * Get the configuration value for the given key.
     *
     * @param string $key The configuration key.
     * @param mixed|null $default The default value if the configuration key does not exist.
     * @return mixed The configuration value.
     */
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('session')) {
    /**
     * Start and return the current session.
     *
     * @return Session The current session.
     */
    function session(): Session
    {
        return System::start_session();
    }
}

if (!function_exists('cookie')) {
    /**
     * Get an instance of the CookieManager.
     *
     * @return CookieManager An instance of the CookieManager.
     */
    function cookie(): CookieManager
    {
        return new CookieManager;
    }
}

if (!function_exists('hasXSS')) {
    /**
     * Check if the given input contains potential XSS attacks.
     *
     * @param array|string $input The input to check.
     * @return bool True if the input contains potential XSS attacks, false otherwise.
     */
    function hasXSS(array|string $input): bool
    {
        $checkInput = function($value) {
            $blacklist = [
                'sql_injection' => '/\b(SELECT|INSERT|UPDATE|DELETE)\b/i',
                'xss_script' => '/<script\b[^>]*>(.*?)<\/script>/i',
                'xss_img' => '/<img\b[^>]*>/i',
                'php_code' => '/<\?php|<\?/i',
                'alert' => '/\balert\b/i',
                'function' => '/\bfunction\b/i',
                'eval' => '/\beval\b/i',
                'script' => '/\bscript\b/i',
            ];
            foreach ($blacklist as $pattern) {
                if (preg_match($pattern, $value)) {
                    return true;
                }
            }
            return false;
        };

        if (is_array($input)) {
            foreach ($input as $item) {
                if ($checkInput($item)) {
                    return true;
                }
            }
        } else {
            return $checkInput($input);
        }

        return false;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the given variables and end the script.
     *
     * @param mixed ...$args The variables to dump.
     */
    #[NoReturn] function dd(...$args): void
    {
        echo '<pre>';
        foreach ($args as $arg) {
            var_dump($arg);
        }
        echo '</pre>';
        die(1);
    }
}

if (!function_exists('d')) {
    /**
     * Dump the given variables without ending the script.
     *
     * @param mixed ...$args The variables to dump.
     */
    function d(...$args): void
    {
        echo '<pre>';
        foreach ($args as $arg) {
            var_dump($arg);
        }
        echo '</pre>';
    }
}

if (!function_exists('encrypt')) {
    /**
     * Encrypt the given data.
     *
     * @param mixed $data The data to encrypt.
     * @return string The encrypted data.
     * @throws Exception If encryption fails.
     */
    function encrypt(mixed $data): string
    {
        return Crypt::encrypt($data);
    }
}

if (!function_exists('decrypt')) {
    /**
     * Decrypt the given data.
     *
     * @param string $data The encrypted data.
     * @return string The decrypted data.
     */
    function decrypt(string $data): string
    {
        return Crypt::decrypt($data);
    }
}

if (!function_exists('random')) {
    /**
     * Generate a random string.
     *
     * @param int $length The length of the random string.
     * @param bool $letters Include letters in the random string.
     * @param bool $numbers Include numbers in the random string.
     * @param bool $symbols Include symbols in the random string.
     * @param bool $spaces Include spaces in the random string.
     * @return string The generated random string.
     * @throws Exception
     */
    function random(int $length = 32, bool $letters = true, bool $numbers = true, bool $symbols = true, bool $spaces = false): string
    {
        $characters = '';
        $string = '';

        if ($letters) {
            $characters .= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if ($numbers) {
            $characters .= '0123456789';
        }
        if ($symbols) {
            $characters .= '~!#$%^&*()_-.,<>?/\\{}[]|:;';
        }
        if ($spaces) {
            $characters .= ' ';
        }

        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $string;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to the given URL.
     *
     * @param string $url The URL to redirect to.
     */
    #[NoReturn] function redirect(string $url): void
    {
        header('Location: ' . $url);
        echo "<meta http-equiv='refresh' content='0; url={$url}' />";
        echo "<script>window.location.href = '{$url}';</script>";
        exit;
    }
}

if (!function_exists('asset')) {
    /**
     * Generate a URL for an asset.
     *
     * @param string $path The path to the asset.
     * @return string The URL to the asset.
     */
    function asset(string $path): string
    {
        $path = trim($path, '/\\');
        return url("assets/" . $path);
    }
}

if (!function_exists('url')) {
    /**
     * Generate a URL.
     *
     * @param string $path The path to append to the base URL.
     * @return string The generated URL.
     */
    function url(string $path = '/'): string
    {
        $path = trim($path, '/\\');
        return rtrim($_ENV['APP_URL'], '/') . '/' . $path;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Generate and return a CSRF token.
     *
     * @return string The generated CSRF token.
     * @throws Exception If token generation fails.
     */
    function csrf_token(): string
    {
        $session = session();
        if (!$session->has('csrf_token')) {
            $session->set('csrf_token', bin2hex(random_bytes(22)));
        }
        return $session->get('csrf_token');
    }
}

if (!function_exists('getUserIP')) {
    /**
     * Get the user's IP address.
     *
     * @return string The user's IP address.
     */
    function getUserIP(): string
    {
        $ipSources = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR'
        ];

        foreach ($ipSources as $source) {
            if (!empty($_SERVER[$source])) {
                $ips = explode(',', $_SERVER[$source]);
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        }
        return 'UNKNOWN';
    }
}

if (!function_exists('getCurrentUrl')) {
    /**
     * Get the current URL.
     *
     * @return string|false The current URL, or false if it cannot be determined.
     */
    function getCurrentUrl(): string|false
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $uri = $_SERVER['REQUEST_URI'];
            return $protocol . "://" . $host . $uri;
        }
        return false;
    }
}

if (!function_exists('back'))
{
    /**
     * Redirect to the previous page.
     */
    #[NoReturn]
    function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        redirect($referer);
    }
}

if (!function_exists('route'))
{
    /**
     * Generate a URL for the given named route.
     *
     * @param string $name The name of the route.
     * @param array $params The route parameters.
     * @return string The generated URL.
     * @throws Exception If route generation fails.
     */
    function route(string $name, array $params = []): string
    {
        $router = new \App\Route();
        return $router->generateUrl($name, $params);
    }
}

if (!function_exists('showError'))
{
    /**
     * Show an error view.
     *
     * @param string|int $error The error key.
     * @param int $httpCode The HTTP status code.
     * @throws Exception If the error view cannot be rendered.
     */
    #[NoReturn] function showError(string|int $error, int $httpCode = 500): void
    {
        $errors = config('app.errors');
        if (isset($errors[$error])) {
            $view = $errors[$error];
            if ($view == '') {
                $view = "errors" . DIRECTORY_SEPARATOR . $error;
                View::setViews(src_path("framework"));
            }
            http_response_code($httpCode);
            View::render($view);
            exit();
        } else {
            $path = app_path('config\\app.php');
            throw new \InvalidArgumentException("Key $error is not defined in the $path file.");
        }
    }
}

if (!function_exists('base_path'))
{
    /**
     * Get the base path of the application.
     *
     * @param string $path The path to append to the base path.
     * @return string The full base path.
     */
    function base_path(string $path = ''): string
    {
        $path = trim($path, '/\\');
        $fullPath = ROOT . DIRECTORY_SEPARATOR . $path;
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fullPath);
    }
}



if (!function_exists('build_path'))
{
    function build_path(string $path = ''): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}

if (!function_exists('localize_script'))
{
    function localize_script(): LocalizeScriptManager
    {
        return LocalizeScriptManager::getInstance();
    }
}

if (!function_exists('app_path'))
{
    /**
     * Get the application path.
     *
     * @param string $path The path to append to the application path.
     * @return string The full application path.
     */
    function app_path(string $path = ''): string
    {
        $path = trim($path, '/\\');
        return base_path('app' . DIRECTORY_SEPARATOR . $path);
    }
}

if (!function_exists('public_path'))
{
    /**
     * Get the public path.
     *
     * @param string $path The path to append to the public path.
     * @return string The full public path.
     */
    function public_path(string $path = ''): string
    {
        $path = trim($path, '/\\');
        return base_path('public' . DIRECTORY_SEPARATOR . $path);
    }
}

if (!function_exists('src_path'))
{
    /**
     * Get the source path.
     *
     * @param string $path The path to append to the source path.
     * @return string The full source path.
     */
    function src_path(string $path = ''): string
    {
        $path = trim($path, '/\\');
        return base_path('app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $path);
    }
}

if (!function_exists('resources_path'))
{
    /**
     * Get the resources' path.
     *
     * @param string $path The path to append to the resources' path.
     * @return string The full resources' path.
     */
    function resources_path(string $path = ''): string
    {
        $path = trim($path, '/\\');
        return base_path('resources' . DIRECTORY_SEPARATOR . $path);
    }
}

if (!function_exists('storage_path'))
{
    /**
     * Get the storage path.
     *
     * @param string $path The path to append to the storage path.
     * @return string The full storage path.
     */
    function storage_path(string $path = ''): string
    {
        $path = trim($path, '/\\');
        return resources_path('storage' . DIRECTORY_SEPARATOR . $path);
    }
}

if (!function_exists('str_slug'))
{
    /**
     * Generate a URL-friendly "slug" from the given string.
     *
     * @param string $string The string to slugify.
     * @param string $separator The separator to use in the slug.
     * @return string The generated slug.
     */
    function str_slug(string $string, string $separator = '-'): string
    {
        $pattern = '/[^a-z0-9\p{L}-]+/u';
        $slug = preg_replace($pattern, $separator, mb_strtolower($string, 'UTF-8'));
        return trim($slug, $separator);
    }

}

if (!function_exists('array_except'))
{
    /**
     * Get all the given array except for a specified array of keys.
     *
     * @param array $array The array to check.
     * @param array|string $keys The keys to exclude.
     * @return array The filtered array.
     */
    function array_except(array $array, array|string $keys): array
    {
        foreach ((array)$keys as $key) {
            unset($array[$key]);
        }
        return $array;
    }
}

if (!function_exists('option'))
{
    /**
     * Get an instance of the Option class.
     *
     * @return Option An instance of the Option class.
     */
    function option(): Option
    {
        return new Option('settings.json', true);
    }
}

if (!function_exists('JWT'))
{
    /**
     * Get an instance of the JWT class.
     *
     * @return JWT An instance of the JWT class.
     */
    function JWT(): JWT
    {
        return new JWT;
    }
}

if (!function_exists('refresh'))
{
    /**
     * Refresh the current page.
     */
    #[NoReturn] function refresh(): void
    {
        redirect(getCurrentUrl());
    }
}

if (!function_exists('migrations'))
{
    /**
     * Run database migrations.
     *
     * @param string $migration The migration to run.
     * @return Artisan\Migrations An instance of the Artisan\Migrations class.
     * @throws Exception If the migration cannot be run.
     */
    function migrations(string $migration): Artisan\Migrations
    {
        return \Artisan()->Migrations($migration);
    }
}

if (!function_exists('factory'))
{
    /**
     * Get an instance of a model factory.
     *
     * @param string $factory The factory to get.
     * @return Factory An instance of the Factory class.
     * @throws Exception If the factory cannot be found.
     */
    function factory(string $factory): Factory
    {
        return \Artisan()->Factory($factory);
    }
}

if (!function_exists('fake'))
{
    /**
     * Get an instance of the Faker generator.
     *
     * @return \Faker\Generator An instance of the Faker\Generator class.
     */
    function fake(): \Faker\Generator
    {
        return Faker::create();
    }
}

if (!function_exists('Artisan'))
{
    /**
     * Get an instance of the Artisan class.
     *
     * @return Artisan An instance of the Artisan class.
     */
    function Artisan(): Artisan
    {
        return new Artisan();
    }
}

if (!function_exists('Mailer'))
{
    /**
     * Get an instance of the Mailer class.
     *
     * @return Mailer An instance of the Mailer class.
     */
    function Mailer(): Mailer
    {
        return new Mailer();
    }
}

if (!function_exists('get_params'))
{
    function get_params($name_params=null): mixed
    {
        if ($name_params === null)
        {
            return $_SERVER['SentParameters'];
        }
        else
        {
            return $_SERVER['SentParameters'][$name_params] ?? null;
        }
    }
}