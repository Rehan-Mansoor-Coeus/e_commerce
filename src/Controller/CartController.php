<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart")
     */
    public function index(): Response
    {
        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
        ]);
    }

    /**
     * @Route("add-to-cart/{id}", name="add-to-cart")
     */

    public function addToCart($id, Product $product){
        $session = new Session();
        $session->start();
        $cart = [];
        if (!$cart) {

            $cart = [
                $id => [
                    "name" => $product->getName(),
                    "quantity" => 1,
                    "price" => $product->getPrice(),
                    "image" => $product->getImage(),
                ]
            ];
        }
        $session->set('cart',$cart);

        dd($session->get('cart') , $cart);
    }
}
