<?php

namespace App\Controller;
use App\Entity\Category;
use App\Entity\Product;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Acme\TestBundle\AcmeTestBundle;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * Require ROLE_ADMIN for all the actions of this controller
 *
 * @IsGranted("ROLE_USER")
 */


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
     * @Route("/home", name="home")
     */
    public function home(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository(Product::class)->findAll();
        $category = $em->getRepository(Category::class)->findAll();

        return $this->render('index/home.html.twig', [
            'result' => $result,
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
     * @Route("/acme", name="acme")
     */
    public function acme(AcmeTestBundle $acme)
    {
        $acme = $acme->get('https://api.publicapis.org/entries');
        $data = $acme['entries'];
        dd(array_slice($data,1,10));

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
