<?php
/* This file is part of Sod | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Sod;

class Sod implements Sod_i
{
    private $flavour = 'Sodium';
    private $cryptKey;

    private $SugarCrypt;
    private $SodiumCrypt;

    private $logs = [];

    public function __construct(array $config = [])
    {
        if (!empty($config["flavour"])) {
            $this->setFlavour($config["flavour"]);
        }
        if (!empty($config["cryptKey"])) {
            $this->setCryptKey($config["cryptKey"]);
        }
    }

    public function hasCryptKey()
    {
       return !empty($this->cryptKey);
    }

    public function setCryptKey(string $key)
    {
        if (is_string($key) && strlen($key) >= 32) {
            $this->cryptKey = hex2bin($key);
            return true;
        }
        return false;
    }

    public function setFlavour(string $flavour)
    {
        if (in_array(ucfirst($flavour), ['Sodium', 'Sugar'])) {
            $this->flavour = ucfirst($flavour);
            return true;
        }
        return false;
    }

    public function encrypt(string $message)
    {
        if (!$this->hasCryptKey()) {
            return false;
        }
        return $this->Trade()->encrypt($message, $this->cryptKey);
    }

    public function decrypt(string $message)
    {
        if (!$this->hasCryptKey()) {
            return false;
        }
        return $this->Trade()->decrypt($message, $this->cryptKey);
    }

    public function isLibSodiumOn()
    {
            try {
                $rslt = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
            } catch (\Exception$e) {
                $rslt = false;
            } finally {
                return !empty($rslt);
            }
    }

    public function getLogs()
    {
        return $this->logs;
    }

    public function record($log)
    {
        $this->logs[] = $log;
    }

    private function Trade()
    {
        $Trade = 'SSITU\Sod\Trades\\'.$this->flavour . 'Crypt';

        if(empty($this->$Trade)){
            $this->$Trade = new $Trade($this);
        }
        return $this->$Trade;      
    }
}
