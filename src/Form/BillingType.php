<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BillingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Formulaire vide pour l'instant (mock de facturation)
        /*
        $builder
            ->add('field_name')
        ;
        */
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
