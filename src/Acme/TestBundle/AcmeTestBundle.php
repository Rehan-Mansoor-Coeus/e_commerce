<?php

namespace  App\Acme\TestBundle;


use Symfony\Component\HttpKernel\Bundle\Bundle;

class AcmeTestBundle extends Bundle
{
    public function get($result ,$paginator, $request ){
        $pagination = $paginator->paginate(
            $result,
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );
        return $pagination;
    }
}