<?php

namespace App\Form;

use App\Entity\ClassDef;
use App\Entity\RulesSource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClassDefType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ruleSlug')
            ->add('name')
            ->add('hitDie')
            ->add('descriptionMd')
            ->add('primaryAbilities')
            ->add('savingThrowProficiencies')
            ->add('rulesSource', null, [
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClassDef::class,
        ]);
    }
}
