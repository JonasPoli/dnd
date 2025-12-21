<?php

namespace App\Form;

use App\Entity\Feature;
use App\Entity\RulesSource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('key', null, ['label' => 'Key (Unique Identifier)'])
            ->add('ownerType', ChoiceType::class, [
                'choices' => [
                    'Class' => 'class',
                    'Subclass' => 'subclass',
                    'Species' => 'species',
                    'Background' => 'background',
                    'Feat' => 'feat',
                    'Item' => 'item',
                ],
                'label' => 'Owner Type',
            ])
            ->add('ownerId', IntegerType::class, [
                'label' => 'Owner ID (Entity ID)',
                'required' => false,
            ])
            ->add('levelRequired', IntegerType::class, [
                'required' => false,
            ])
            ->add('descriptionMd', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 6],
                'label' => 'Description (Markdown)',
            ])
            ->add('grantsJson', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 4],
                'label' => 'Grants (JSON)',
            ])
            ->add('rulesSource', EntityType::class, [
                'class' => RulesSource::class,
                'choice_label' => 'name',
            ])
        ;

        $builder->get('grantsJson')
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
            'data_class' => Feature::class,
        ]);
    }
}
