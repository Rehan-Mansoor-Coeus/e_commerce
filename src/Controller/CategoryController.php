<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/category", name="category")
     */
    public function index(Request $request): Response
    {
        $Category = new Category();
        $form = $this->createForm(CategoryType::class , $Category , [
            'action' => $this->generateUrl('category')
        ]);


        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $Category->setCreated(new \DateTime(date('Y-m-d')));
            $em = $this->getDoctrine()->getManager();
            $em->persist($Category);
            $em->flush();
            $this->addFlash('success', 'Category has been Uploaded!');
            return $this->redirect($this->generateUrl('category'));
        }

        return $this->render('Category/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/category/record", name="category-record")
     */
    public function record(CategoryRepository $categoryRepository): Response
    {
        $result = $categoryRepository->findAll();
        $header = "Categorys Records";
        return $this->render('Category/record.html.twig', [
            'category' => $result,
            'header' => $header,
        ]);
    }

    /**
     * @Route("/category/delete/{id}", name="delete-Category")
     */
    public function remove(CategoryService $categoryService ,CategoryRepository $categoryRepository, Category $category = null)
    {
//        Refector 1
         try{
            $categoryService->checkParam($category);
            $categoryRepository->removeCategory($category);
            $this->addFlash('success', 'Category has been Deleted!');
        } catch (\Exception $ex) {
            $this->addFlash('error', $ex->getMessage());
        }
        return $this->redirectToRoute('category-record');
    }


    /**
     * @Route("/category/edit/{id}", name="category-edit")
     */
    public function edit(Category $Category ,Request $request): Response
    {

        $form = $this->createForm(CategoryType::class , $Category);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();
            $em->flush();

            $this->addFlash('success', 'Category has been Updated!');
            return $this->redirect($this->generateUrl('category-record'));
        }

        return $this->render('Category/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }


}
