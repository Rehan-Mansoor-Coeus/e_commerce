<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;


use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('email',EmailType::class)
            ->add('phone',NumberType::class)
            ->add('address' ,  TextareaType::class)
            ->add('locale' ,  ChoiceType::class , [
                'choices'  => [
                    'English' => 'en',
                    'French' => 'fr_FR',
                ],
            ])
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
            ->add('userType' ,  ChoiceType::class , [
                'mapped' => false,
                'choices'  => [
                    'Buyer' => '0',
                    'Seller' => '1',
                ],
            ])
            ->add('Register' , SubmitType::class , [
                'attr' => [
                    'class' => 'btn btn-success float-right'
                ]
             ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
