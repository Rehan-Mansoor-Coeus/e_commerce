<?php

namespace App\Controller;

use App\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @Route("/order/record", name="order-record")
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository(Order::class)->findAll();

        return $this->render('order/index.html.twig', [
            'result' => $result,
        ]);
    }
    /**
     * @Route("/order/view/{id}", name="order-view")
     */
    public function view(Order $order): Response
    {
        dd($order->getOrderDetailId()['quantity']);
        return $this->render('order/view.html.twig', [
            'order' => $order,
        ]);
    }
}
