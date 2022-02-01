<?php

namespace App\Service;

use Exception;

class ProductService
{

    /**
     * check parameter is invalid or null
     * @param $product
     * @return bool
     * @throws Exception
     */
    public function checkParam($product){
        if (!isset($product)) {
            throw new Exception('Response is empty', 201);
        }else{
            return true;
        }
    }

}
