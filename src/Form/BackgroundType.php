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
            ->add('name', null, ['label' => 'Nome', 'attr' => ['class' => 'form-input']])
            ->add('attribute1', EntityType::class, [
                'class' => \App\Entity\Attribute::class,
                'choice_label' => 'name',
                'label' => 'Atributo 1',
                'required' => false,
            ])
            ->add('attribute2', EntityType::class, [
                'class' => \App\Entity\Attribute::class,
                'choice_label' => 'name',
                'label' => 'Atributo 2',
                'required' => false,
            ])
            ->add('attribute3', EntityType::class, [
                'class' => \App\Entity\Attribute::class,
                'choice_label' => 'name',
                'label' => 'Atributo 3',
                'required' => false,
            ])
            ->add('feat', EntityType::class, [
                'class' => \App\Entity\Feat::class,
                'choice_label' => 'name',
                'label' => 'Talento',
                'required' => false,
            ])
            ->add('skill1', EntityType::class, [
                'class' => \App\Entity\Skill::class,
                'choice_label' => 'name',
                'label' => 'Perícia 1',
                'required' => false,
            ])
            ->add('skill2', EntityType::class, [
                'class' => \App\Entity\Skill::class,
                'choice_label' => 'name',
                'label' => 'Perícia 2',
                'required' => false,
            ])
            ->add('toolProficiency', EntityType::class, [
                'class' => \App\Entity\Equipment::class,
                'choice_label' => fn($choice) => $choice->getNamePt() ?: $choice->getName(),
                'label' => 'Proficiência com Ferramenta',
                'required' => false,
            ])
            ->add('startingEquipment', EntityType::class, [
                'class' => \App\Entity\Equipment::class,
                'choice_label' => fn($choice) => $choice->getNamePt() ?: $choice->getName(),
                'label' => 'Equipamento Inicial',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => ['class' => 'form-select', 'size' => 10], // Make it taller since it's a list
            ])
            ->add('descriptionMd', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 5],
                'label' => 'Descrição (Markdown)',
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
