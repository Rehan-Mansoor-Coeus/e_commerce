<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;


/**
 * @class ProductVoter
 * @property Security $security
 */
class ProductVoter extends Voter
{

    /**
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param string $attribute
     * @param $subject
     * @return bool
     */
    protected function supports(string $attribute, $subject): bool
    {

        return in_array($attribute, ['EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Product;
    }

    /**
     * @param string $attribute
     * @param $product
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $product, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case $this->security->isGranted('ROLE_ADMIN'):
                return true;
            case 'DELETE' || 'EDIT':
            return $product->getUser()->getId() == $user->getId();
        }

        return false;
    }
}
