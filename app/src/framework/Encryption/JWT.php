<?php

namespace App\Encryption;

use stdClass;

class JWT
{
    /**
     * Encodes the given data into a JSON Web Token.
     *
     * @param array|stdClass $data The data to encode.
     * @return string The encoded JWT.
     * @throws \Exception If encryption fails.
     */
    public function encode(array|stdClass $data): string
    {
        return Crypt::encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Decodes the given JSON Web Token into its original data.
     *
     * @param string $data The encoded JWT.
     * @return array|stdClass The decoded data.
     */
    public function decode(string $data): array|stdClass
    {
        return json_decode(Crypt::decrypt($data));
    }
}