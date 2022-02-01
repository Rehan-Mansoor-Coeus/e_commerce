<?php

namespace App\Controller;
use App\Bundle\CustomBundle\PaginationBundle;
use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\Query\AST\LikeExpression;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Contracts\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;




class IndexController extends AbstractController
{

    private $security;

    /**
     * @Route("/admin", name="index")
     */
    public function index(): Response
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    /**
     * @Route("/products/{id}", name="product-category")
     */
    public function productCategory(Category $category , ProductRepository $productRepository , CategoryRepository $categoryRepository): Response
    {
        $result = $productRepository->findBy([
            'category' => $category
        ]);
        $category = $categoryRepository->findAll();

        return $this->render('index/home.html.twig', [
            'result' => $result,
            'category' => $category
        ]);
    }

    /**
     * @Route("/home", name="home")
     */
    public function home(Request $request , ProductRepository $productRepository , CategoryRepository $categoryRepository): Response
    {

        $q = $request->query->get('search');
        if($q){
            $result = $productRepository->findAllWithSearch($q);
        }else{
            $result = $productRepository->findAll();
        }

        $category = $categoryRepository->findAll();

        return $this->render('index/home.html.twig', [
            'result' => $result,
            'category' => $category
        ]);
    }

    /**
     * @Route("/account", name="account")
     */
    public function account(PaginationBundle $page , PaginatorInterface $paginator ,OrderRepository $orderRepository , CategoryRepository $categoryRepository ,  Request $request): Response
    {
        $array = ['pending','complete','Rejected'];
        $category = $categoryRepository->findAll();
        $result = $orderRepository->findBy([
            'user'=>$this->getUser()
        ]);
        $result = $page->get($result,$paginator,$request);

        return $this->render('index/account.html.twig', [
            'result' => $result,
            'array' => $array,
            'category' => $category
        ]);
    }

    /**
     * @Route("/logger", name="logger")
     */
    public function logger(LoggerInterface $logger)
    {
        $logger->info('Info Logger');
        $logger->error('An error occurred');
        $logger->critical('Critical error found!', [
            // include extra "context" info in your logs
            'cause' => 'bad coding ..!',
        ]);

        return new Response('logger practice');
    }



    /**
     * @Route("/markdown", name="markdown")
     */
    public function markdown(MarkdownParserInterface $markdownParser)
    {
       $data = "<h3>This is <b>H3</b> Tag</h3>";
       $process_data = $markdownParser->transformMarkdown($data);

        return $this->render('index/markdown.html.twig', [
            'data' => $data ,
            'markdown' => $process_data ,
        ]);

    }
    /**
     * @Route("/translation", name="translation")
     */
    public function translation(TranslatorInterface $translator )
    {

        $translated = $translator->trans(
            'Symfony is great',
            [],
            'messages',
            'fr_FR'
        );

        return $this->render('index/translated.html.twig', [
            'translated' => $translated ,
        ]);

    }

}
