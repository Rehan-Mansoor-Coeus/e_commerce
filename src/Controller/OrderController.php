<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\Product;
use App\Repository\OrderDetailRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use App\Bundle\CustomBundle\PaginationBundle;
use App\Service\MailService;

class OrderController extends AbstractController
{
    /**
     * @Route("/order/record", name="order-record")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(PaginationBundle $page , PaginatorInterface $paginator , Request $request , OrderRepository $order): Response
    {
        $array = ['pending','complete','Rejected'];
        $em = $this->getDoctrine()->getManager();
        $result = $order->findAll();

        $pagination = $page->get($result,$paginator,$request);

        return $this->render('order/index.html.twig', [
            'pagination' => $pagination,
            'array' => $array,
        ]);
    }
    /**
     * @Route("/order/record/user", name="order-record-user")
     */
    public function indexUser(PaginationBundle $page , PaginatorInterface $paginator , Request $request , OrderRepository $order): Response
    {
        $array = ['pending','complete','Rejected'];
        $em = $this->getDoctrine()->getManager();
        $result = $order->findBy([
            'seller'=>$this->getUser()
        ]);

        $pagination = $page->get($result,$paginator,$request);

        return $this->render('order/user.html.twig', [
            'pagination' => $pagination,
            'array' => $array,
        ]);
    }


    /**
     * @Route("/order/view/{id?1}", name="order-view")
     */
    public function view(Order $order): Response
    {
       try{
            return $this->render('order/view.html.twig', [
                'order' => $order,
            ]);
        } catch (\Exception $ex) {
            return $this->render('bundles/TwigBundle/Exception/error404.html.twig');
        }

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
    public function update(Order $order , Request $request , MailService $mail , MailerInterface $mailer , ProductRepository $productRepository): Response
    {
        $em = $this->getDoctrine()->getManager();
        $data = $request->request->all();
        $array = ['pending','completed','Rejected'];

        if($data['status'] == 2){
            foreach($order->getOrderDetail() as $item){
                $product = $productRepository->find($item->getProduct()->getId());
                $product->setStock($product->getStock() + $item->getQuantiity());
                $em->persist($product);
                $em->flush();
            }
        }

            $order->setStatus($data['status']);
            $em->persist($order);
            $em->flush();


            $to = $order->getUser()->getEmail();
            $customer = $order->getUser()->getUsername();
            $order_no = $order->getId();
            $status = $array[$order->getStatus()];
            $subject = "Order Status";
            $message = "<h1>Dear $customer !</h1><hr><p>Your Order No # $order_no has been $status </p>";
            $mail->sendMail($to,$subject,$message,$mailer);

            $this->addFlash('success', 'Order has been Updated!');
            return $this->redirectToRoute('order-record-user');
    }
    /**
     * @Route("/order/delete/{id}", name="order-delete")
     */
    public function remove(Order $order , OrderDetailRepository $detailRepository): Response
    {
        $em = $this->getDoctrine()->getManager();
        $detail = $detailRepository->findBy([
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
