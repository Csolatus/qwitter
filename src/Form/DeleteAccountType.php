<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;

class DeleteAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre mot de passe pour confirmer.'),
                    new UserPassword(message: 'Le mot de passe est incorrect.'),
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-200 outline-none transition-all',
                    'placeholder' => '••••••••'
                ],
            ])
            ->add('confirm', CheckboxType::class, [
                'label' => 'Je comprends que cette action est irréversible et que toutes mes données seront supprimées.',
                'mapped' => false,
                'constraints' => [
                    new IsTrue(message: 'Vous devez cocher cette case pour confirmer la suppression.'),
                ],
                'attr' => [
                    'class' => 'w-5 h-5 text-red-600 rounded focus:ring-red-500 border-gray-300'
                ],
                'label_attr' => [
                    'class' => 'ml-2 text-sm text-gray-700'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_token_id' => 'delete_account',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'delete_account_form';
    }
}
