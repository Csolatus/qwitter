<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\Length;

class SecurityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Mot de passe actuel',
                    'attr' => ['class' => 'w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition-all'],
                    'label_attr' => ['class' => 'block text-sm font-semibold text-slate-700 mb-2'],
                ])
            ->add('newPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'mapped' => false,
                    'required' => false,
                    'first_options' => [
                        'label' => 'Nouveau mot de passe',
                        'attr' => ['class' => 'w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition-all'],
                        'label_attr' => ['class' => 'block text-sm font-semibold text-slate-700 mb-2'],
                    ],
                    'second_options' => [
                        'label' => 'Confirmer le nouveau mot de passe',
                        'attr' => ['class' => 'w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition-all'],
                        'label_attr' => ['class' => 'block text-sm font-semibold text-slate-700 mb-2'],
                    ],
                    'constraints' => [
                        // new Length(['min' => 6]), // À décommenter si besoin
                    ],
                ])
            ->add('is2FAEnabled', CheckboxType::class, [
                    'label' => 'Double Authentification (2FA)',
                    'help' => 'Sécurisez votre compte avec une deuxième étape de connexion',
                    'required' => false,
                    'label_attr' => ['class' => 'font-medium text-slate-800'],
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
