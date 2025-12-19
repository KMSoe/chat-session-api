<?php

namespace App\Helpers;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Config;

class CustomerEncrypter
{
      static $customerEncrypterInstance;
      private $encrypter;

      private function __construct()
      {
            $this->createEncrypter();
      }

      public static function getInstance()
      {
            if (self::$customerEncrypterInstance == null) {
                  self::$customerEncrypterInstance = new CustomerEncrypter();
            }

            return self::$customerEncrypterInstance;
      }

      public function createEncrypter()
      {
            $key = base64_decode(Config::get('app.encryption_key'));
            $this->encrypter = new Encrypter($key, Config::get('app.cipher'));
      }

      public function getEncrypter()
      {
            return $this->encrypter;
      }
}
