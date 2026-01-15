<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Votre nom',
                'attr' => [
                    'placeholder' => 'John Doe',
                    'class' => 'w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition'
                ],
                'constraints' => [
                    // CORRECTION : Arguments nommés au lieu d'un tableau
                    new NotBlank(message: 'Veuillez entrer votre nom'),
                    new Length(
                        min: 2, 
                        minMessage: 'Votre nom doit contenir au moins {{ limit }} caractères'
                    )
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre email',
                'attr' => [
                    'placeholder' => '[email protected]',
                    'class' => 'w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition'
                ],
                'constraints' => [
                    // CORRECTION ICI AUSSI
                    new NotBlank(message: 'Veuillez entrer votre email'),
                    new Email(message: 'L\'email {{ value }} n\'est pas valide')
                ]
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'attr' => [
                    'placeholder' => 'À propos de...',
                    'class' => 'w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition'
                ],
                'constraints' => [
                    // ET ICI
                    new NotBlank(message: 'Veuillez entrer un sujet')
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'attr' => [
                    'placeholder' => 'Écrivez votre message ici...',
                    'rows' => 6,
                    'class' => 'w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none'
                ],
                'constraints' => [
                    // ET ENFIN ICI
                    new NotBlank(message: 'Veuillez entrer un message'),
                    new Length(
                        min: 10, 
                        minMessage: 'Votre message doit contenir au moins {{ limit }} caractères'
                    )
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer le message',
                'attr' => [
                    'class' => 'w-full bg-slate-800 hover:bg-slate-900 text-white font-medium py-3 px-6 rounded-xl transition shadow-lg'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
