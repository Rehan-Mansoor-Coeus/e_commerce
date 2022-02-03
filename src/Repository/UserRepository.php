<?php

namespace App\Repository;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    private $roleRepository;
    public function __construct(ManagerRegistry $registry , RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * remove user
     * @param User $user
     * @return bool
     */
    public function removeUser(User $user): bool
    {
        try{
            $this->_em->remove($user);
            $this->_em->flush();
            return true;
        }catch (\Exception $ex){
            throw new Exception('You cannot delete this User', 201);
        }
    }

    /**
     * create user code
     *@return bool
     */
    public function createUser($data , $passEncode , $userType):bool
    {
        try{
        $user = new User();
        $user->setUsername($data->getUsername());
        $user->setEmail($data->getEmail());
        $user->setPhone($data->getPhone());
        $user->setAddress($data->getAddress());
        $user->setLocale($data->getLocale());
        if($userType==1){
            $user->addRole($this->roleRepository->findOneBy(['roleName'=>'ROLE_SELLER']));
        }
        $user->setPassword(
            $passEncode->encodePassword($user , $data->getPassword())
        );
        $user->setCreated(new \DateTime(date('Y-m-d')));

        $this->_em->persist($user);
        $this->_em->flush();
        return true;
        }catch (\Exception $ex){
            throw new Exception('You cannot Create this User', 201);
        }
    }



    /**
     * update user code
     *@return bool
     */
    public function updateUser($data , $passEncode , $user):bool
    {
        try{

            $user->setUsername($data->getUsername());
            $user->setEmail($data->getEmail());
            $user->setPhone($data->getPhone());
            $user->setAddress($data->getAddress());
            $user->setLocale($data->getLocale());
            $user->setPassword(
                $passEncode->encodePassword($user , $data->getPassword())
            );
            $user->setCreated(new \DateTime(date('Y-m-d')));

            $this->_em->persist($user);
            $this->_em->flush();
            return true;
        }catch (\Exception $ex){
            throw new Exception('You cannot Update this User', 201);
        }
    }

}
