<?php
/* This file is part of Sod | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Sod\Trades;

class SodiumCrypt
{
    private $Sod;

    public function __construct($Sod)
    {
        $this->Sod = $Sod;
    }

    public function encrypt(string $message, string $key)
    {
        try {
            if (mb_strlen($key, '8bit') !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
                $this->Sod->record('require-32-bytes-key');
                return false;
            }
            $exch_nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

            $cipher = base64_encode(
                $exch_nonce .
                sodium_crypto_secretbox(
                    $message,
                    $exch_nonce,
                    $key
                )
            );
            sodium_memzero($message);
            sodium_memzero($key);
            return $cipher;
        } catch (\Exception$e) {
            $this->Sod->record('libsodium-not-installed');
            return false;
        }
    }

    public function decrypt(string $message, string $key)
    {
        try {
            $decoded = base64_decode($message);
            $out_nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
            $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

            $plain = sodium_crypto_secretbox_open(
                $ciphertext,
                $out_nonce,
                $key
            );
            if (!is_string($plain)) {
                $this->Sod->record('invalid-decrypt-output');
                return false;
            }
            sodium_memzero($ciphertext);
            sodium_memzero($key);
            return $plain;
        } catch (\Exception$e) {
            $this->Sod->record('libsodium-not-installed');
            return false;
        }
    }

}
