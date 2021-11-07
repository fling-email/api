<?php

declare(strict_types=1);

namespace App\Traits;

trait GeneratesDkimKeys
{
    /**
     * Generates a new key pair to use for DKIM signing
     *
     * @return array
     * @phan-return array{string, string}
     */
    protected function generateDkimKeys(): array
    {
        $openssl_key = \openssl_pkey_new([
            "digest_alg" => "sha256",
            "private_key_bits" => 2048,
            "private_key_type" => \OPENSSL_KEYTYPE_RSA,
        ]);

        \openssl_pkey_export($openssl_key, $private_key);

        $public_key = \openssl_pkey_get_details($openssl_key)["key"];

        return [
            \trim($private_key),
            \trim($public_key),
        ];
    }
}
