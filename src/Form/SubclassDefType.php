<?php

namespace App\Form;

use App\Entity\ClassDef;
use App\Entity\RulesSource;
use App\Entity\SubclassDef;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubclassDefType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('ruleSlug')
            ->add('classDef', EntityType::class, [
                'class' => ClassDef::class,
                'choice_label' => 'name',
                'label' => 'Parent Class',
            ])
            ->add('availableFromLevel', IntegerType::class, [
                'label' => 'Available From Level',
            ])
            ->add('descriptionMd', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 6],
                'label' => 'Description (Markdown)',
            ])
            ->add('subclassSpells', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, [
                'entry_type' => SubclassSpellType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Spells (Always Prepared)',
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
            'data_class' => SubclassDef::class,
        ]);
    }
}
