<?php

namespace App\Form;

use App\Entity\MagicItem;
use App\Entity\RulesSource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MagicItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('namePt', null, ['label' => 'Nome (PT)'])
            ->add('ruleSlug')
            ->add('type')
            ->add('typePt', null, ['label' => 'Tipo (PT)'])
            ->add('rarity')
            ->add('rarityPt', null, ['label' => 'Raridade (PT)'])
            ->add('requiresAttunement')
            ->add('requiresAttunementPt', null, ['label' => 'Requer Sintonização (PT)'])
            ->add('descriptionMd', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 10]
            ])
            ->add('descriptionMdPt', TextareaType::class, [
                'label' => 'Descrição (PT)',
                'required' => false,
                'attr' => ['rows' => 10]
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
            'data_class' => MagicItem::class,
        ]);
    }
}
