<?php

namespace App\Controller;
use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\Query\AST\LikeExpression;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Acme\TestBundle\AcmeTestBundle;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;




class IndexController extends AbstractController
{

    private $security;

    /**
     * @Route("/admin", name="index")
     *
     *
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
    public function productCategory(Category $category): Response
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository(Product::class)->findBy([
            'category' => $category
        ]);
        $category = $em->getRepository(Category::class)->findAll();

        return $this->render('index/home.html.twig', [
            'result' => $result,
            'category' => $category
        ]);
    }

    /**
     * @Route("/home", name="home")
     */
    public function home(Request $request): Response
    {
        $q = $request->query->get('search');
        $em = $this->getDoctrine()->getManager();

        if($q){
            $result = $em->getRepository(Product::class)->findAllWithSearch($q);
        }else{
            $result = $em->getRepository(Product::class)->findAll();
        }

        $category = $em->getRepository(Category::class)->findAll();

        return $this->render('index/home.html.twig', [
            'result' => $result,
            'category' => $category
        ]);
    }

    /**
     * @Route("/account", name="account")
     */
    public function account(): Response
    {
        $array = ['pending','complete','Rejected'];
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository(Category::class)->findAll();
        $result = $em->getRepository(Order::class)->findBy([
            'user'=>$this->getUser()
        ]);

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
