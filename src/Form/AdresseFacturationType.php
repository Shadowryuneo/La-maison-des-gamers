<?php

namespace App\Form;

use App\Entity\Adressefacturationcommande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdresseFacturationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rue', TextType::class)
            ->add('ville', TextType::class)
            ->add('codePostal', TextType::class)
            ->add('pays', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adressefacturationcommande::class,
        ]);
    }
}
