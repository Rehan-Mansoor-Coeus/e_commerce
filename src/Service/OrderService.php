<?php
namespace App\Service;

use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Exception;

/**
 *@property ProductRepository $productRepository
 *@property UserRepository $userRepository
 *
 */
class OrderService
{
    public function __construct(ProductRepository $productRepository , UserRepository $userRepository)
    {
        $this->productRepository = $productRepository;
        $this->userRepository = $userRepository;

    }


    public function manageOrders($carts,$user){

        $total = [];
        $cart = [];
        $index = 0;

        foreach ($carts as $key=>$item) {
            $seller = $this->userRepository->find($item['seller']);
            $product = $this->productRepository->find($key);

            $cart[$item['seller']][$index] = [
                "product" => $product,
                "quantity" => $item['quantity'],
                "buyyer" => $user,
                "seller" => $seller,
            ];
            @$total[$item['seller']] += $item['price'] * $item['quantity'];
            $index++;
        }


        return [$cart,$total];
    }

}
