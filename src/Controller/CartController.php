<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Entity\OrderDetail;
use App\Entity\Product;
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
            ->from('rehanfaby36@gmail.com')
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);
        return new Response(1);

    }


    /**
     * @Route("/order", name="order")
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository(User::class)->find(1);
        dd($product);
        $user = $this->getUser();
        $session = new Session();
        $data = $request->request->all();

        $seller = [];
        $total = [];
//        dd($session->get('cart'));
        foreach ($session->get('cart') as $key=>$item) {
            $em = $this->getDoctrine()->getManager();
            $product = $em->getRepository(Product::class)->find($key);
            $em->flush();

            $seller_id = $item['seller']->getId();

                $cart[$seller_id][$key] = [
                            "product" => $product,
                            "quantity" => $item['quantity'],
                            "buyyer" => $user,
                            "seller" => $item['seller'],
                ];
                $total[$seller_id] += $item['price'] * $item['quantity'];
                $em->flush();
        }
//        dd($cart,$total);


        foreach($cart as $ki=>$items){
            dd($user,$cart);
            $em = $this->getDoctrine()->getManager();
            $order = new Order();
            $order->setTotal($total[$ki]);
            $order->setStatus(0);
            $order->setUser($user);
            $order->setSeller($items[$ki]['seller']);
            $order->setCreated(new \DateTime(date('Y-m-d')));
            $em->persist($order);
            $em->flush();


            foreach ($items as $key=>$item){
                $em = $this->getDoctrine()->getManager();
                $orderDetail = new OrderDetail();
                $product = $em->getRepository(Product::class)->find($key);

                $orderDetail->setProduct($product);
                $orderDetail->setOrderr($order);
                $orderDetail->setPrice($product->getPrice());
                $orderDetail->setQuantiity($item['quantity']);
                $em->persist($orderDetail);
                $em->flush();

                $em = $this->getDoctrine()->getManager();
                $product->setStock($product->getStock() - $item['quantity']);
                $em->persist($product);
                $em->flush();

            }

        }

        $session->clear();
        return $this->redirect('/complete/');


    }
    /**
     * @Route("/complete", name="complete")
     */

    public function complete()
    {
        return $this->render('cart/complete.html.twig', [
            'order' => 12
        ]);
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
                    "seller" => $product->getUser()
                ]
            ];
            $session->set('cart',$cart);
            return $this->redirect('/home');
//            return new JsonResponse(['cart' => $session->get('cart')]);
        }

        // if cart not empty then check if this product exist then increment quantity

        if (isset($cart[$id])) {
            $session->set('cart',$cart);
            if ($session->has('cart')) {

               $cart[$id]['quantity'] = $cart[$id]['quantity'] + 1;
            }

            $session->set('cart',$cart);
            return $this->redirect('/home');
            return new JsonResponse(['cart' => $session->get('cart')]);
        }

        // if item not exist in cart then add to cart with quantity = 1
        $cart[$id] = [
            "name" => $product->getName(),
            "quantity" => 1,
            "price" => $product->getPrice(),
            "image" => $product->getImage(),
            "seller" => $product->getUser()

        ];
        $session->set('cart',$cart);
        return $this->redirect('/home');
        return new JsonResponse(['cart' => $session->get('cart')]);



    }
}
