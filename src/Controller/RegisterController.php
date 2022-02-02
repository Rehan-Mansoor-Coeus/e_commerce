<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class RegisterController extends AbstractController
{

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request , UserPasswordEncoderInterface $passEncode , UserRepository $userRepository)
    {
//        refactor 3
        $user = new User();
        $form = $this->createForm(UserType::class , $user , [
            'action' => $this->generateUrl('register')
        ]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            try{
            $userRepository->createUser($data,$passEncode);
            } catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
            }
            return $this->redirect($this->generateUrl('app_login'));
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/users", name="users")
     */

    public function user(UserRepository $user){
        $user = $user->findAll();

        return $this->render('register/record.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/delete-user/{id}", name="delete-user")
     */
    public function remove(User $user , UserRepository $userRepository){
        // refactor 2
        try{
            $userRepository->removeUser($user);
            $this->addFlash('success', 'User has been Deleted!');
        } catch (\Exception $ex) {
            $this->addFlash('error', $ex->getMessage());
        }
        return $this->redirectToRoute('users');
    }

    /**
     * @Route("/user/create", name="user-create")
     */
    public function create(Request $request , UserPasswordEncoderInterface $passEncode , UserRepository $userRepository)
    {
        $user = new User();
        $form = $this->createForm(UserType::class , $user , [
            'action' => $this->generateUrl('user-create')
        ]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $userType = $form['userType']->getData();

            try{
                $userRepository->createUser($data,$passEncode,$userType);
                $this->addFlash('success', 'New User has been Created!');
            } catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
            }

            return $this->redirect($this->generateUrl('users'));
        }

        return $this->render('register/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/edit/{id}", name="edit-user")
     */
    public function edit(Request $request , User $user , UserPasswordEncoderInterface $passEncode , UserRepository $userRepository)
    {
        $form = $this->createForm(UserType::class , $user );

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();

            try{
                $userRepository->updateUser($data,$passEncode , $user);
                $this->addFlash('success', 'User has been Updated!');
            } catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
            }

            return $this->redirect($this->generateUrl('users'));
        }

        return $this->render('register/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }





}
