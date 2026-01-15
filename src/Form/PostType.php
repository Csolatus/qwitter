<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Quoi de neuf ?',
                    'class' => 'w-full border-none focus:ring-0 text-lg resize-none placeholder-gray-400 text-slate-800 h-12 p-2 outline-none bg-transparent',
                    'rows' => 1
                ],
                'constraints' => [
                    new Length(max: 2000, maxMessage: 'Votre message ne peut pas dépasser {{ limit }} caractères.')
                ]
            ])
            ->add('image', FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'hidden'],
                'constraints' => [
                    new File(
                        maxSize: '20M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        mimeTypesMessage: 'Veuillez uploader une image valide (JPG, PNG, GIF, WEBP)',
                    )
                ],
            ])
            ->add('pollQuestion', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Posez votre question', 'class' => 'w-full bg-transparent border-b border-gray-200 focus:border-blue-500 outline-none py-2 mb-2']
            ])
            ->add('pollOption1', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Choix 1', 'class' => 'w-full bg-gray-50 rounded-lg px-4 py-2 mb-2 border border-gray-200 focus:border-blue-500 outline-none']
            ])
            ->add('pollOption2', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Choix 2', 'class' => 'w-full bg-gray-50 rounded-lg px-4 py-2 mb-2 border border-gray-200 focus:border-blue-500 outline-none']
            ])
            ->add('pollOption3', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Choix 3 (optionnel)', 'class' => 'w-full bg-gray-50 rounded-lg px-4 py-2 mb-2 border border-gray-200 focus:border-blue-500 outline-none']
            ])
            ->add('pollOption4', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Choix 4 (optionnel)', 'class' => 'w-full bg-gray-50 rounded-lg px-4 py-2 mb-2 border border-gray-200 focus:border-blue-500 outline-none']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
