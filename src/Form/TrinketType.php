<?php

namespace App\Form;

use App\Entity\RulesSource;
use App\Entity\Trinket;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrinketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rollKey', IntegerType::class, [
                'label' => 'Roll Result (d100)',
            ])
            ->add('textMd', TextareaType::class, [
                'label' => 'Description (Markdown)',
                'attr' => ['rows' => 3],
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
            'data_class' => Trinket::class,
        ]);
    }
}
