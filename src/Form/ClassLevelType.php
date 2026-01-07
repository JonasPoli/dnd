<?php

namespace App\Form;

use App\Entity\ClassLevel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClassLevelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('level', IntegerType::class, [
                'label' => 'Nível',
                'attr' => ['class' => 'w-16']
            ])
            ->add('proficiencyBonus', IntegerType::class, [
                'label' => 'PB',
                'attr' => ['class' => 'w-16']
            ])
            ->add('cantripsKnown', IntegerType::class, [
                'required' => false,
                'label' => 'Truques',
                'attr' => ['class' => 'w-16']
            ])
            ->add('spellsPrepared', IntegerType::class, [
                'required' => false,
                'label' => 'Magias Prep.',
                'attr' => ['class' => 'w-16']
            ])
            ->add('spellSlotsJson', TextareaType::class, [
                'required' => false,
                'label' => 'Slots (JSON)',
                'attr' => ['rows' => 1, 'class' => 'font-mono text-xs w-full']
            ])
            ->add('featuresConfig', TextareaType::class, [
                'required' => false,
                'label' => 'Features (JSON)',
                'attr' => ['rows' => 1, 'class' => 'font-mono text-xs w-full']
            ])
        ;

        $jsonTransformer = new CallbackTransformer(
            function ($array) {
                return empty($array) ? '' : json_encode($array);
            },
            function ($string) {
                return empty($string) ? [] : json_decode($string, true);
            }
        );

        $builder->get('spellSlotsJson')->addModelTransformer($jsonTransformer);
        $builder->get('featuresConfig')->addModelTransformer($jsonTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClassLevel::class,
        ]);
    }
}
