<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\File;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('avatar', FileType::class, [
                    'label' => 'Photo de profil',
                    'mapped' => false,
                    'required' => false,
                    'constraints' => [
                        new File(
                            maxSize: '2M',
                            mimeTypes: [
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ],
                            mimeTypesMessage: 'Veuillez uploader une image valide (JPEG, PNG, WEBP)',
                        )
                    ],
                ])
            ->add('pseudo', TextType::class, [
                    'label' => 'Pseudo',
                    'attr' => ['placeholder' => 'Votre pseudo']
                ])
            ->add('email', EmailType::class, [
                    'label' => 'Adresse Email',
                    'attr' => ['placeholder' => 'exemple@email.com']
                ])
            ->add('bio', TextareaType::class, [
                    'label' => 'Biographie',
                    'required' => false,
                    'attr' => ['rows' => 4, 'placeholder' => 'Dites quelque chose sur vous...']
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
