<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetail;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Acme\TestBundle\AcmeTestBundle;

class OrderController extends AbstractController
{
    /**
     * @Route("/order/record", name="order-record")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(AcmeTestBundle $acme , PaginatorInterface $paginator , Request $request): Response
    {
        $array = ['pending','complete','Rejected'];
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository(Order::class)->findAll();

        $pagination = $acme->get($result,$paginator,$request);

        return $this->render('order/index.html.twig', [
            'pagination' => $pagination,
            'array' => $array,
        ]);
    }
    /**
     * @Route("/order/record/user", name="order-record-user")
     */
    public function indexUser(AcmeTestBundle $acme , PaginatorInterface $paginator , Request $request): Response
    {
        $array = ['pending','complete','Rejected'];
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository(Order::class)->findBy([
            'seller'=>$this->getUser()
        ]);

        $pagination = $acme->get($result,$paginator,$request);

        return $this->render('order/user.html.twig', [
            'pagination' => $pagination,
            'array' => $array,
        ]);
    }


    /**
     * @Route("/order/view/{id}", name="order-view")
     */
    public function view(Order $order): Response
    {
        return $this->render('order/view.html.twig', [
            'order' => $order,
        ]);
    }
    /**
     * @Route("/order/edit/{id}", name="order-edit")
     */
    public function edit(Order $order , Request $request): Response
    {
        $array = ['pending','complete','Rejected'];
        return $this->render('order/edit.html.twig', [
            'result' => $order,
            'array' => $array
        ]);
    }
    /**
     * @Route("/order/update/{id}", name="order-update")
     */
    public function update(Order $order , Request $request): Response
    {
            $em = $this->getDoctrine()->getManager();
            $data = $request->request->all();
            $order->setStatus($data['status']);
            $em->persist($order);
            $em->flush();

            $this->addFlash('success', 'Order has been Updated!');
            return $this->redirectToRoute('order-record-user');
    }
    /**
     * @Route("/order/delete/{id}", name="order-delete")
     */
    public function remove(Order $order): Response
    {
        $em = $this->getDoctrine()->getManager();
        $detail = $em->getRepository(OrderDetail::class)->findBy([
            'orderr' => $order,
        ]);
        foreach ($detail as $item){
        $em->remove($item);
        }
        $em->remove($order);
        $em->flush();

        $this->addFlash('danger', 'Order has been Deleted!');

        return $this->redirectToRoute('order-record');
    }
}
