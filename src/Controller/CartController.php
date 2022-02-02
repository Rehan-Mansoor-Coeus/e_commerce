<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Entity\OrderDetail;
use App\Entity\Product;
use App\Repository\OrderDetailRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\MailService;
use App\Service\OrderService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart")
     */
    public function index(): Response
    {
        $session = new Session();
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
     * @Route("/checkout/complete", name="checkout-commplete")
     * @IsGranted("ROLE_USER")
     */
    public function create(MailerInterface $mailer, MailService $mail , UserRepository $userRepository , ProductRepository $productRepository , OrderRepository $orderRepository , OrderDetailRepository $detailRepository , OrderService $orderService)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $session = new Session();

        $result = $orderService->manageOrders($session->get('cart') , $user);
        $cart = $result[0];
        $total = $result[1];

        $index2 = 0;
        foreach($cart as $ki=>$items){

            $total_amount =  $total[$ki];
            $seller = $items[$index2]['seller'];
            $order = $orderRepository->createOrder($seller,$user,$total_amount);

            foreach ($items as $key=>$item){
                $detailRepository->createOrderDetail($item,$order);

                $product = $item['product'];
                $product->setStock($product->getStock() - $item['quantity']);
                $em->persist($product);
                $em->flush();

                $index2 ++;
            }

//            mail to buyer
            $to = $user->getEmail();
            $customer = $user->getUsername();
            $order_no = $order->getId();
            $subject = "Order Placed ..!";
            $message = "<h1>Dear $customer !</h1><hr><p>Your Order No # $order_no has been placed successfully  </p>";
            $mail->sendMail($to,$subject,$message,$mailer);

//            mail to Seller

            $to = $seller->getEmail();
            $customer = $seller->getUsername();
            $order_no = $order->getId();
            $subject = "Order Received..!";
            $message = "<h1>Dear $customer !</h1><hr><p>YOu have received an order with Order No # $order_no  </p>";
            $mail->sendMail($to,$subject,$message,$mailer);

        }
        $session->clear();
        return $this->redirect('/complete/');
    }

    /**
     * @Route("/complete", name="complete")
     */

    public function complete()
    {
        return $this->render('cart/complete.html.twig');
    }

    /**
     * @Route("/cart/plus/{id}", name="cart-plus")
     */

    public function cartPlus($id)
    {
        $session = new Session();
        $cart = $session->get('cart');

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] ++;
            $session->set('cart',$cart);
        }

        return $this->redirect('/cart');
    }

    /**
     * @Route("/cart/minus/{id}", name="cart-minus")
     */

    public function cartMinus($id)
    {
        $session = new Session();
        $cart = $session->get('cart');

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] --;
            $session->set('cart',$cart);
        }

        return $this->redirect('/cart');
    }

    /**
     * @Route("/cart/remove/{id}", name="cart-remove")
     */

    public function cartRemove($id)
    {
        $session = new Session();
        $cart = $session->get('cart');
        unset($cart[$id]);
        $session->set('cart',$cart);
        return $this->redirect('/cart');

    }

    /**
     * @Route("add-to-cart/{id}", name="add-to-cart")
     */

    public function addToCart($id, Product $product){

        $session = new Session();
        $cart = $session->get('cart');

        if (!$cart) {
            $cart = [
                $id => [
                    "name" => $product->getName(),
                    "quantity" => 1,
                    "price" => $product->getPrice(),
                    "image" => $product->getImage(),
                    "seller" => $product->getUser()->getId()
                ]
            ];
            $session->set('cart',$cart);
//            return $this->redirect('/home');
            return new JsonResponse(['cart' => $session->get('cart')]);
        }

        // if cart not empty then check if this product exist then increment quantity

        if (isset($cart[$id])) {
            $session->set('cart',$cart);
            if ($session->has('cart')) {

               $cart[$id]['quantity'] = $cart[$id]['quantity'] + 1;
            }

            $session->set('cart',$cart);
//            return $this->redirect('/home');
            return new JsonResponse(['cart' => $session->get('cart')]);
        }

        // if item not exist in cart then add to cart with quantity = 1
        $cart[$id] = [
            "name" => $product->getName(),
            "quantity" => 1,
            "price" => $product->getPrice(),
            "image" => $product->getImage(),
            "seller" => $product->getUser()->getId()

        ];
        $session->set('cart',$cart);
//        return $this->redirect('/home');
        return new JsonResponse(['cart' => $session->get('cart')]);



    }
}
