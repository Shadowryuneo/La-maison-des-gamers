<?php

namespace App\Form;

use App\Entity\Avis;
use App\Entity\Produit;
use App\Entity\Utilisateurs;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class , [
                'label' => false,
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Donnez votre avis'
                ],
                'constraints' => [
                    new NotBlank([
                    'message' => 'veuillez écrire donnez votre avis'
                    ])
                ]
            ])

            // ->add('produit', EntityType::class, [
            //     'class' => Produit::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('utilisateurs', EntityType::class, [
            //     'class' => Utilisateurs::class,
            //     'choice_label' => 'id',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Avis::class,
        ]);
    }
}
