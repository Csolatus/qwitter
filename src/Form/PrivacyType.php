<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PrivacyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isPrivate', CheckboxType::class, [
                    'label' => 'Compte Privé',
                    'help' => 'Seuls vos abonnés pourront voir vos posts et photos',
                    'required' => false,
                    'label_attr' => ['class' => 'font-medium text-slate-800'],
                ])
            ->add('messagePrivacy', ChoiceType::class, [
                    'label' => 'Qui peut vous envoyer des messages ?',
                    'choices' => [
                        'Tout le monde' => 'everyone',
                        'Mes abonnés uniquement' => 'followers',
                        'Personne' => 'nobody',
                    ],
                    'expanded' => false,
                    'multiple' => false,
                    'attr' => ['class' => 'w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition-all'],
                    'label_attr' => ['class' => 'block text-sm font-semibold text-slate-700 mb-2'],
                ])
            ->add('isOnlineVisible', CheckboxType::class, [
                    'label' => 'Statut en ligne',
                    'help' => 'Afficher quand vous êtes actif',
                    'required' => false,
                    'label_attr' => ['class' => 'font-medium text-slate-800'],
                ])
            ->add('isIndexed', CheckboxType::class, [
                    'label' => 'Référencement',
                    'help' => 'Autoriser les moteurs de recherche à indexer votre profil',
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
