<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [ "label" => "E-mail"])
            ->add('roles', ChoiceType::class, [
                "choices" => [
                    "Utilisateur" => "ROLE_USER",
                    "Administrateur" => "ROLE_ADMIN"
                ],
                "multiple" => true,
                "expanded" => true,
                "required" => false,
            ])
            ->add('password', PasswordType::class, [ 
                "label" => "Mot de passe",
                "help" => "Le mot de passe doit comporter au moins 8 caractères",
                "mapped" => false,
                "required" => false,
                "constraints" => [
                    new Length([
                        "min" => 8,
                        "minMessage" => "Le pseudo doit comporter au minimum {{ limit }} caractères",
                    ])
                    //     new Regex([
                    //         "pattern" => "/^(?=.{6,10}$)(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$/",
                    //         "message" => "Le mot de passe doit comporter entre 6 et 10 caractères, une minuscule, une majuscule, un chiffre, un caractère spécial"
                    //     ])
                ]
                ])
            ->add('nom', TextType::class)
            ->add('slogan', TextType::class, [
                "required" => false,
            ])
            ->add('score', IntegerType::class, [
                "required" => false,
            ])
            ->add('visible', ChoiceType::class, [
                "choices" => [
                    "visible" => "1",
                    "Invisible" => "0"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
