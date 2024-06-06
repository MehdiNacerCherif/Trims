<?php

namespace App\Form;

use App\Entity\Message;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', TextType::class, [
                "required" => false,
                'empty_data' => '',
            ])
            ->add('titre')
            ->add('contenu')
            ->add('categories', TextType::class, [
                "required" => false,
            ])
            ->add('date', TextType::class, [
                "required" => false,
                'empty_data' => new \DateTime(),
            ])
            ->add('auteur', EntityType::class, [
                "class" => User::class,
                "choice_label" => "nom",
                "required" => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
