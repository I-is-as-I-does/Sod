<?php
/* This file is part of Sod | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Sod\Trades;

class SugarCrypt
{

/* @doc: if libsodium not available */

    private $hash_algo = 'sha256';
    private $crypt_method = 'aes-256-ctr';

    private $Sod;

    public function __construct($Sod)
    {
        $this->Sod = $Sod;
    }

    /* @doc: encrypts then MACs a message
    $message - plaintext message
    $key - encryption key
    return string (b64)
     */
    public function encrypt(string $message, string $key)
    {
        list($encKey, $authKey) = $this->splitKeys($key);

        $nonceSize = openssl_cipher_iv_length($this->crypt_method);
        $nonce = openssl_random_pseudo_bytes($nonceSize);

        $proto_ciphertext = openssl_encrypt(
            $message,
            $this->crypt_method,
            $encKey,
            OPENSSL_RAW_DATA,
            $nonce
        );

        $ciphertext = $nonce . $proto_ciphertext;
        $mac = hash_hmac($this->hash_algo, $ciphertext, $authKey, true);

        return base64_encode($mac . $ciphertext);
    }

    /* @doc: decrypts a message (after verifying integrity)
    $message - ciphertext message
    $key - encryption key
    return string (b64)
     */
    public function decrypt(string $message, string $key)
    {
        list($encKey, $authKey) = $this->splitKeys($key);
        $message = base64_decode($message);
        if ($message === false) {
            $this->Sod->record('invalid-b64');
            return false;
        }

        // @Doc: Hash Size -- in case $this->hash_algo is changed
        $hs = mb_strlen(hash($this->hash_algo, '', true), '8bit');
        $mac = mb_substr($message, 0, $hs, '8bit');

        $proto_ciphertext = mb_substr($message, $hs, null, '8bit');

        $calculated = hash_hmac(
            $this->hash_algo,
            $proto_ciphertext,
            $authKey,
            true
        );

        if (!$this->hashEquals($mac, $calculated)) {
            $this->Sod->record('data-corrupted');
            return false;

        }

        $nonceSize = openssl_cipher_iv_length($this->crypt_method);
        $nonce = mb_substr($proto_ciphertext, 0, $nonceSize, '8bit');
        $ciphertext = mb_substr($proto_ciphertext, $nonceSize, null, '8bit');

        $plaintext = openssl_decrypt(
            $ciphertext,
            $this->crypt_method,
            $encKey,
            OPENSSL_RAW_DATA,
            $nonce
        );

        return $plaintext;
    }

    /* @doc: splits a key into two separate keys;
    one for encryption
    one for authentication */
    private function splitKeys($key)
    {
        $binkey = bin2hex($key);
        return [
            hash_hmac($this->hash_algo, 'ENCRYPTION', $binkey, true),
            hash_hmac($this->hash_algo, 'AUTHENTICATION', $binkey, true),
        ];
    }

    /* @doc: Compare two strings without leaking timing information
    @ref https://paragonie.com/b/WS1DLx6BnpsdaVQW */
    private function hashEquals($a, $b)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($a, $b);
        }
        $nonce = openssl_random_pseudo_bytes(32);
        return hash_hmac($this->hash_algo, $a, $nonce) === hash_hmac($this->hash_algo, $b, $nonce);
    }

}
