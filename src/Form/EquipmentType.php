<?php

namespace App\Form;

use App\Entity\Equipment;
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

            ->add('name')
            ->add('type')
            ->add('typePt', null, ['label' => 'Tipo (PT)'])
            ->add('weaponCategory', null, ['required' => false, 'label' => 'Weapon Category'])
            ->add('weaponCategoryPt', null, ['required' => false, 'label' => 'Categoria de Arma (PT)'])
            ->add('weaponRange', null, ['required' => false, 'label' => 'Weapon Range'])
            ->add('damageDice', null, ['required' => false, 'label' => 'Damage Dice'])
            ->add('damageType', null, ['required' => false, 'label' => 'Damage Type'])
            ->add('damageTypePt', null, ['required' => false, 'label' => 'Tipo de Dano (PT)'])
            ->add('costGp')
            ->add('weightLb')
            ->add('weightKg')
            ->add('propertiesJson', TextareaType::class, [
                'required' => false,
                'label' => 'Properties (JSON)',
                'attr' => ['rows' => 4],
            ])
            ->add('descriptionMd', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 6]
            ])
            ->add('descriptionMdPt', TextareaType::class, [
                'required' => false,
                'label' => 'Descrição (PT)',
                'attr' => ['rows' => 6]
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
