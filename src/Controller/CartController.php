<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Entity\OrderDetail;
use App\Entity\Product;
use App\Service\MailService;
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
     * @Route("/email")
     */
    public function sendEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('rehan.mansoor@coeus-solutions.de')
            ->to('rehanfaby36@gmail.com')
            ->subject('Order PLaced!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);
        return new Response(1);

    }


    /**
     * @Route("/checkout/complete", name="checkout-commplete")
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request , MailerInterface $mailer, MailService $mail)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $session = new Session();

        $seller = [];
        $total = [];

        $index = 0;

        foreach ($session->get('cart') as $key=>$item) {
            $seller = $em->getRepository(User::class)->find($item['seller']);
            $product = $em->getRepository(Product::class)->find($key);

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

        $index2 = 0;
        foreach($cart as $ki=>$items){

            $order = new Order();
            $order->setTotal($total[$ki]);
            $order->setStatus(0);
            $order->setUser($user);
            $order->setSeller($items[$index2]['seller']);
            $order->setCreated(new \DateTime(date('Y-m-d')));
            $em->persist($order);
            $em->flush();


            foreach ($items as $key=>$item){

                $orderDetail = new OrderDetail();
                $orderDetail->setProduct($item['product']);
                $orderDetail->setOrderr($order);
                $orderDetail->setPrice($item['product']->getPrice());
                $orderDetail->setQuantiity($item['quantity']);
                $em->persist($orderDetail);
                $em->flush();

                $product = $item['product'];
                $product->setStock($product->getStock() - $item['quantity']);
                $em->persist($product);
                $em->flush();
                $seller = $items[$index2]['seller'];
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
