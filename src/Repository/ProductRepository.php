<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

     /**
      * finding products
      * @param null|string $term
      * @return Product[] Returns an array of Product objects
      */

    public function findAllWithSearch(?string $term)
    {
        $qb = $this->createQueryBuilder('c');

        if($term){
            $qb->andWhere('c.name LIKE :term OR c.description Like :term')
                ->setParameter('term','%'.$term.'%')
                ;
        }
        return $qb
            ->orderBy('c.created','DESC')
            ->getQuery()
            ->getResult()
            ;

    }

    /**
     *create product
     */
    public function createProduct($request , $product, $path, $user){

        if($request->files->get('product')['image']) {
            $file = $request->files->get('product')['image'];
            $file_name = rand(100000, 999999) . '.' . $file->guessExtension();
            $file->move($path, $file_name);
            $product->setImage($file_name);
        }
        $product->setUser($user);
        $product->setCreated(new \DateTime(date('Y-m-d')));

        $this->_em->persist($product);
        $this->_em->flush();
    }

    /**
     *remove product
     */
    public function removeProduct(Product $user): bool
    {
        try{
            $this->_em->remove($user);
            $this->_em->flush();
            return true;
        }catch (\Exception $ex){
            throw new Exception('You cannot delete this Product', 201);
        }
    }




    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
