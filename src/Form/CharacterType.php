<?php

namespace App\Form;

use App\Entity\Background;
use App\Entity\Character;
use App\Entity\ClassDef;
use App\Entity\Species;
use App\Entity\SubclassDef;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('level')
            ->add('alignment')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('updatedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('classDef', EntityType::class, [
                'class' => ClassDef::class,
                'choice_label' => 'id',
            ])
            ->add('subclassDef', EntityType::class, [
                'class' => SubclassDef::class,
                'choice_label' => 'id',
            ])
            ->add('species', EntityType::class, [
                'class' => Species::class,
                'choice_label' => 'id',
            ])
            ->add('background', EntityType::class, [
                'class' => Background::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Character::class,
        ]);
    }
}
