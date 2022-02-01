<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ProductService;
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
    public function index(Request $request , ProductRepository $productRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class , $product , [
            'action' => $this->generateUrl('product')
        ]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $path = $this->getParameter('upload_directory');
            $user = $this->getUser();
            $productRepository->createProduct($request,$product,$path,$user);

            $this->addFlash('success', 'Product has been Uploaded!');
             return $this->redirect($this->generateUrl('product'));
        }

        return $this->render('product/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/product/record", name="product-record")
     */
    public function record(ProductRepository $product): Response
    {
        $result = $product->findAll();
        $header = "products Records";
        return $this->render('product/record.html.twig', [
            'product' => $result,
            'header' => $header,
        ]);
    }

    /**
     * @Route("/product/record/user", name="product-record-user")
     */
    public function recordUser(ProductRepository $product): Response
    {
        $result = $product->findBy([
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
    public function remove(ProductService $productService,ProductRepository $productRepository,  product $product = null)
    {
        if(!$this->isGranted('DELETE', $product)) {
            return $this->notPermission();
        }
         try{
            $productService->checkParam($product);
            $productRepository->removeProduct($product);
            $this->addFlash('success', 'product has been Deleted!');
        } catch (\Exception $ex) {
            $this->addFlash('error', $ex->getMessage());
        }
        return $this->redirectToRoute('product-record-user');
    }


    /**
     * @Route("/product/edit/{id}", name="product-edit")
     *
     */
    public function edit(Request $request, ProductRepository $productRepository,ProductService $productService , product $product = null): Response
    {
        if(!$this->isGranted('EDIT', $product)) {
            return $this->notPermission();
        }
        try{
            $productService->checkParam($product);
        } catch (\Exception $ex) {
            $this->addFlash('error', $ex->getMessage());
        }

        $form = $this->createForm(ProductType::class , $product);
       $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $path = $this->getParameter('upload_directory');
            $user = $this->getUser();
            $productRepository->createProduct($request,$product,$path,$user);

            $this->addFlash('success', 'product has been Updated!');
            return $this->redirect($this->generateUrl('product-record-user'));

        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView()
        ]);

    }


}
