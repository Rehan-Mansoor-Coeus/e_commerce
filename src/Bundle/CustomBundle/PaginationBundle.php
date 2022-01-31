<?php

namespace  App\Bundle\CustomBundle;


use Symfony\Component\HttpKernel\Bundle\Bundle;

class PaginationBundle extends Bundle
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