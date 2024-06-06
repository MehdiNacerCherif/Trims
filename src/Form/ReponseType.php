<?php

namespace App\Form;

use App\Entity\Reponse;
use App\Entity\User;
use App\Entity\Message;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', TextType::class, [
                "required" => false,
                'empty_data' => new \DateTime(),
            ])
            ->add('contenu')
            ->add('auteur', EntityType::class, [
                "class" => User::class,
                "choice_label" => "nom",
                "required" => false,
            ])
            ->add('message', EntityType::class, [
                "class" => Message::class,
                "choice_label" => "titre",
                "required" => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reponse::class,
        ]);
    }
}
