<?php

namespace App\Repository;

use App\Entity\OrderDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderDetail[]    findAll()
 * @method OrderDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderDetailRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderDetail::class);
    }

    /**
     * create order detail
     *@const $item $order
     *@return true
     */
    public function createOrderDetail($item , $order):bool{

        $orderDetail = new OrderDetail();
        $orderDetail->setProduct($item['product']);
        $orderDetail->setOrderr($order);
        $orderDetail->setPrice($item['product']->getPrice());
        $orderDetail->setQuantiity($item['quantity']);
        $this->_em->persist($orderDetail);
        $this->_em->flush();
        return true;
    }
}
