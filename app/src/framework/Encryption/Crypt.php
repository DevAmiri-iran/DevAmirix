<?php
namespace App\Encryption;

use Exception;

class Crypt
{
    private static string $tag = 'VA';
    private static string $cipher_algo;

    /**
     * Retrieves the encryption key from the environment variables.
     *
     * @return false|string Returns the encryption key or false if not set.
     */
    private static function Key(): false|string
    {
        $config = config('app');
        self::$cipher_algo = $config['cipher'];
        return env('APP_KEY');
    }

    /**
     * Encrypts the given data using the specified cipher algorithm and key.
     *
     * @param mixed $data The data to be encrypted.
     * @return string The encrypted data.
     * @throws Exception If the APP_KEY format is invalid.
     */
    public static function encrypt(mixed $data): string
    {
        if (self::Key()) {
            $ivSize = openssl_cipher_iv_length(self::$cipher_algo);
            $iv = openssl_random_pseudo_bytes($ivSize);
            $encodedData = openssl_encrypt($data, self::$cipher_algo, self::Key(), 0, $iv);
            return self::$tag . rtrim(str_replace(['+', '/'], ['.', '_'], base64_encode($iv . $encodedData)), '=');
        }
        throw new Exception("Invalid APP_KEY format");
    }

    /**
     * Decrypts the given encrypted data.
     *
     * @param string $data The encrypted data.
     * @return string|null The decrypted data, or null if decryption fails.
     */
    public static function decrypt(string $data): ?string
    {
        if (self::Key()) {
            $data = str_replace(' ', '', $data);
            $tag = substr($data, 0, 2);
            $data = substr($data, 2);
            if ($tag == self::$tag) {
                try {
                    $data = str_replace(['.', '_'], ['+', '/'], $data);
                    $data = base64_decode($data);
                    $ivSize = openssl_cipher_iv_length(self::$cipher_algo);
                    $iv = substr($data, 0, $ivSize);
                    $encryptedData = substr($data, $ivSize);
                    $decryptedData = openssl_decrypt($encryptedData, self::$cipher_algo, self::Key(), 0, $iv);
                    return $decryptedData ?: null;
                } catch (Exception $e) {
                    return null;
                }
            } else {
                return null;
            }
        }
        return null;
    }
}