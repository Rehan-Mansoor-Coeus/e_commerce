<?php
namespace App\Service;

use Exception;


class OrderService
{

    /**
     * check parameter is invalid or null
     * @param $category
     * @return bool
     * @throws Exception
     */
    public function manageOrders($cart){
        $seller = [];
        $total = [];
        $index = 0;

        foreach ($session->get('cart') as $key=>$item) {
            $seller = $userRepository->find($item['seller']);
            $product = $productRepository->find($key);

            $cart[$item['seller']][$index] = [
                "product" => $product,
                "quantity" => $item['quantity'],
                "buyyer" => $user,
                "seller" => $seller,
            ];
            @$total[$item['seller']] += $item['price'] * $item['quantity'];
            $em->flush();
            $index++;
        }
    }

}
