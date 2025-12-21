<?php

namespace App\Form;

use App\Entity\Background;
use App\Entity\RulesSource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BackgroundType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ruleSlug')
            ->add('name')
            ->add('descriptionMd', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 5],
                'label' => 'Description (Markdown)',
            ])
            ->add('skillProficiencies', TextType::class, ['required' => false])
            ->add('toolProficiencies', TextType::class, ['required' => false])
            ->add('languages', TextType::class, ['required' => false])
            ->add('equipment', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('feature', TextType::class, ['required' => false])
            ->add('featureDesc', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 5],
                'label' => 'Feature Description',
            ])
            ->add('suggestedCharacteristics', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 5],
            ])
            ->add('grantsJson', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 3],
                'label' => 'Grants (JSON)',
            ])
            ->add('rulesSource', EntityType::class, [
                'class' => RulesSource::class,
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Background::class,
        ]);
    }
}
