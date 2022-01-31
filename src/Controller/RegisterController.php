<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Types\TextType;
//use http\Env\Request;
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
    public function register(Request $request , UserPasswordEncoderInterface $passEncode)
    {
        $form = $this->createFormBuilder()
            ->add('username')
            ->add('email',EmailType::class)
            ->add('phone',NumberType::class)
            ->add('address' ,  TextareaType::class)
            ->add('password' ,RepeatedType::class , [
                'type' => PasswordType::class,
                'required' => true ,
                'first_options' => ['label' => 'Password'] ,
                'second_options' => ['label' => 'Confirm Password'],
                'constraints' => [new Assert\Length([
                    'min' => 6,
                    'max' => 8,
                ])],
            ])
            ->add('Register' , SubmitType::class , [
                'attr' => [
                    'class' => 'btn btn-success float-right'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();

            $user = new User();
            $user->setUsername($data['username']);
            $user->setEmail($data['email']);
            $user->setPhone($data['phone']);
            $user->setAddress($data['address']);
            $user->setPassword(
                $passEncode->encodePassword($user , $data['password'])
            );
            $user->setCreated(new \DateTime(date('Y-m-d')));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

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
        $em = $this->getDoctrine()->getManager();
        $user = $user->findAll();

        return $this->render('register/record.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/delete-user/{id}", name="delete-user")
     */
    public function remove(int $id , UserRepository $user){
        $em = $this->getDoctrine()->getManager();
        $user = $user->find($id);

        try{
            $em->remove($user);
            $em->flush();
        } catch (\Exception $ex) {
            return $this->render('bundles/TwigBundle/Exception/error403.html.twig');
        }

        $this->addFlash('success', 'User has been Deleted!');
        return $this->redirectToRoute('users');
    }


    /**
     * @Route("/user/create", name="user-create")
     */
    public function create(Request $request , UserPasswordEncoderInterface $passEncode)
    {
        $form = $this->createFormBuilder()
            ->add('username')
            ->add('email',EmailType::class)
            ->add('phone',NumberType::class)
            ->add('address' ,  TextareaType::class)
            ->add('password' ,RepeatedType::class , [
                'type' => PasswordType::class,
                'required' => true ,
                'first_options' => ['label' => 'Password'] ,
                'second_options' => ['label' => 'Confirm Password'],
                'constraints' => [new Assert\Length([
                    'min' => 6,
                    'max' => 8,
                    ])]
            ])
            ->add('Register' , SubmitType::class , [
                'attr' => [
                    'class' => 'btn btn-success float-right'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();

            $user = new User();
            $user->setUsername($data['username']);
            $user->setEmail($data['email']);
            $user->setPhone($data['phone']);
            $user->setAddress($data['address']);
            $user->setPassword(
                $passEncode->encodePassword($user , $data['password'])
            );
            $user->setCreated(new \DateTime(date('Y-m-d')));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'New Author has been Created!');
            return $this->redirect($this->generateUrl('users'));
        }


        return $this->render('register/form.html.twig', [
            'form' => $form->createView()
        ]);
    }





}
