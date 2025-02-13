<?php

namespace App\Support;

use App\Encryption\Crypt;
use Exception;

class CookieManager
{
    private bool $encode = true;

    /**
     * Sets a cookie with the given parameters.
     *
     * @param string $name The name of the cookie.
     * @param string $value The value of the cookie.
     * @param int $expiry The expiration time of the cookie in seconds.
     * @param string $path The path on the server in which the cookie will be available.
     * @param string $domain The (sub)domain that the cookie is available to.
     * @param bool $secure Indicates that the cookie should only be transmitted over a secure HTTPS connection.
     * @param bool $httponly When true the cookie will be made accessible only through the HTTP protocol.
     * @return CookieManager Returns the instance of CookieManager.
     * @throws Exception If encryption fails.
     */
    public function set(string $name, string $value, int $expiry = 3600, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = true): CookieManager
    {
        if ($this->encode) {
            $value = Crypt::encrypt($value);
        }

        $expiryTime = time() + $expiry;
        setcookie($name, $value, $expiryTime, $path, $domain, $secure, $httponly);
        return $this;
    }

    /**
     * Retrieves the value of the specified cookie.
     *
     * @param string $name The name of the cookie.
     * @return string|null The value of the cookie or null if not found.
     */
    public function get(string $name): ?string
    {
        $response = null;
        if ($this->has($name)) {
            if ($this->encode) {
                $response = Crypt::decrypt($_COOKIE[$name]);
            } else {
                $response = $_COOKIE[$name];
            }
        }
        return $response;
    }

    /**
     * Deletes the specified cookie.
     *
     * @param string $name The name of the cookie.
     * @return CookieManager Returns the instance of CookieManager.
     */
    public function delete(string $name): CookieManager
    {
        setcookie($name, '', time() - 3600, '/', null, false, true);
        unset($_COOKIE[$name]);
        return $this;
    }

    /**
     * Checks if the specified cookie exists.
     *
     * @param string $name The name of the cookie.
     * @return bool True if the cookie exists, otherwise false.
     */
    public function has(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Disables encryption for the cookies.
     *
     * @return CookieManager Returns the instance of CookieManager.
     */
    public function disable_encoding(): CookieManager
    {
        $this->encode = false;
        return $this;
    }
}