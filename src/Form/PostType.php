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
                            maxSize: '5M',
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
