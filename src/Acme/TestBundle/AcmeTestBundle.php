<?php

namespace App\Acme\TestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AcmeTestBundle extends Bundle
{
    public function get($url){
        $result = file_get_contents($url);
        return json_decode($result , true);
    }
}