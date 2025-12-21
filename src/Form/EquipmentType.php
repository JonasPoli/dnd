<?php

namespace App\Form;

use App\Entity\Equipment;
use App\Entity\RulesSource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ruleSlug')
            ->add('name')
            ->add('type')
            ->add('weaponCategory', null, ['required' => false, 'label' => 'Weapon Category'])
            ->add('weaponRange', null, ['required' => false, 'label' => 'Weapon Range'])
            ->add('damageDice', null, ['required' => false, 'label' => 'Damage Dice'])
            ->add('damageType', null, ['required' => false, 'label' => 'Damage Type'])
            ->add('costGp')
            ->add('weightLb')
            ->add('propertiesJson', TextareaType::class, [
                'required' => false,
                'label' => 'Properties (JSON)',
                'attr' => ['rows' => 4],
            ])
            ->add('descriptionMd', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 6]
            ])
            ->add('rulesSource', EntityType::class, [
                'class' => RulesSource::class,
                'choice_label' => 'name',
            ])
        ;

        $builder->get('propertiesJson')
            ->addModelTransformer(new CallbackTransformer(
                function ($array) {
                    return empty($array) ? '' : json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                },
                function ($string) {
                    if (empty($string)) {
                        return [];
                    }
                    $decoded = json_decode($string, true);
                    return is_array($decoded) ? $decoded : [];
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipment::class,
        ]);
    }
}
