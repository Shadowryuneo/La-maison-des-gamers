<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\Categories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prix')
            ->add('description')
            ->add('nom')
            ->add('status')
            ->add('categorie', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'nom'
            ])
            ->add('image', FileType::class, [
                'label' => 'Image à charger',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/png',
                            'image/jpg',
                            'image/jpeg',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez charger des fichiers de type image et d\'extensions : PNG, JPG ou WEBP'
                    ])
                ]
            ])

            ->add('stock', IntegerType::class,[
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
