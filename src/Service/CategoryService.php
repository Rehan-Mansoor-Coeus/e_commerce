<?php
// src/Service/MessageGenerator.php
namespace App\Service;

use App\Repository\CategoryRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @property CategoryRepository $categoryRepository
 */
class CategoryService
{

    /**
     * check parameter is invalid or null
     * @param $category
     * @return bool
     * @throws Exception
     */
    public function checkParam($category){
        if (!isset($category)) {
            throw new Exception('Response is empty', 201);
        } else{
            return true;
        }
    }

}
