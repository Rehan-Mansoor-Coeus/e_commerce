<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Security\Voter\productVoter;

class ProductController extends AbstractController
{
    /**
     * @Route("/product", name="product")
     */
    public function index(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class , $product , [
            'action' => $this->generateUrl('product')
        ]);


        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();

            if($request->files->get('product')['image']) {
                $file = $request->files->get('product')['image'];
                $upload_directory = $this->getParameter('upload_directory');
                $file_name = rand(100000, 999999) . '.' . $file->guessExtension();

                $file->move($upload_directory, $file_name);
                $product->setImage($file_name);
            }

            $product->setUser($this->getUser());
            $product->setCreated(new \DateTime(date('Y-m-d')));


            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();


            $this->addFlash('success', 'Product has been Uploaded!');

            return $this->redirect($this->generateUrl('product'));
        }

        return $this->render('product/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/product-record", name="product-record")
     */
    public function record(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository(product::class)->findAll();
        $header = "products Records";
        return $this->render('product/record.html.twig', [
            'product' => $result,
            'header' => $header,
        ]);
    }



    /**
     * @Route("/product-record-user", name="product-record-user")
     */
    public function recordUser(): Response
    {

        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository(product::class)->findBy([
            'user' => $this->getUser()
        ]);
        $header = "My products Records";
        return $this->render('product/user-record.html.twig', [
            'product' => $result,
            'header' => $header,
        ]);
    }


    /**
     * @Route("/delete-product/{id}", name="delete-product")
     */
    public function remove(product $product){

        $this->denyAccessUnlessGranted('DELETE', $product);

        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();

        $this->addFlash('success', 'product has been Deleted!');

        return $this->redirectToRoute('product-record');
    }


    /**
     * @Route("/product/edit/{id}", name="product-edit")
     */
    public function edit(product $product ,Request $request , $id): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $product);


        $form = $this->createForm(ProductType::class , $product);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();

            if($request->files->get('product')['image']){
                $file = $request->files->get('product')['image'];
                $upload_directory = $this->getParameter('upload_directory');
                $file_name = rand(100000,999999).'.'.$file->guessExtension();

                $file->move($upload_directory,$file_name);

                $product->setImage($file_name);
            }

            $product->setUser($this->getUser());

            $em->flush();

            $this->addFlash('success', 'product has been Updated!');
            return $this->redirect($this->generateUrl('product-record'));
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }


}
