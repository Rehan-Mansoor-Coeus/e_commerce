<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $session = new Session();
        $session->start();

        return $this->render('cart/index.html.twig', [
            'cart' => $session->get('cart'),
        ]);
    }

    /**
     * @Route("/checkout", name="checkout")
     * @IsGranted("ROLE_USER")
     */
    public function checkout(): Response
    {
        $session = new Session();

        return $this->render('cart/checkout.html.twig', [
            'cart' => $session->get('cart'),
        ]);
    }


    /**
     * @Route("/order", name="order")
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request)
    {
        $session = new Session();
        $data = $request->request->all();

        $order = new Order();


        $em = $this->getDoctrine()->getManager();
        $order->setTotal($data['grand_total']);
        $order->setStatus(0);
        $order->setUser($this->getUser());
        $order->setCreated(new \DateTime(date('Y-m-d')));
        $em->persist($order);
        $em->flush();

        foreach ($session->get('cart') as $key=>$item){
            $orderDetail = new OrderDetail();
            $product = $em->getRepository(Product::class)->find($key);

            $em = $this->getDoctrine()->getManager();
            $orderDetail->setProduct($product);
            $orderDetail->setOrderr($order);
            $orderDetail->setPrice($item['price']);
            $orderDetail->setQuantiity($item['quantity']);
            $em->persist($orderDetail);
            $em->flush();
        }

        $session->clear();
        return $this->redirect('/complete/'.$order->getId());


    }
    /**
     * @Route("complete/{id}", name="complete")
     */

    public function complete($id)
    {
        return $this->render('cart/complete.html.twig', [
            'order' => $id
        ]);
    }

    /**
     * @Route("add-to-cart/{id}", name="add-to-cart")
     */

    public function addToCart($id, Product $product){
        $session = new Session();
        $session->start();
        $cart = $session->get('cart');

        if (!$cart) {
            $cart = [
                $id => [
                    "name" => $product->getName(),
                    "quantity" => 1,
                    "price" => $product->getPrice(),
                    "image" => $product->getImage(),
                ]
            ];
            $session->set('cart',$cart);
            return new JsonResponse(['cart' => $session->get('cart')]);
        }

        // if cart not empty then check if this product exist then increment quantity

        if (isset($cart[$id])) {
            $session->set('cart',$cart);
            if ($session->has('cart')) {

               $cart[$id]['quantity'] = $cart[$id]['quantity'] + 1;
            }

            $session->set('cart',$cart);
            return new JsonResponse(['cart' => $session->get('cart')]);
        }

        // if item not exist in cart then add to cart with quantity = 1
        $cart[$id] = [
            "name" => $product->getName(),
            "quantity" => 1,
            "price" => $product->getPrice(),
            "image" => $product->getImage(),

        ];
        $session->set('cart',$cart);
        return new JsonResponse(['cart' => $session->get('cart')]);



    }
}
