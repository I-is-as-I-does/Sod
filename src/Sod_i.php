<?php
/* This file is part of Sod | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Sod;

interface Sod_i
{

    public function hasCryptKey();
    public function setCryptKey(string $key);
    public function encrypt(string $message);
    public function decrypt(string $message);
    public function getLogs();

}
