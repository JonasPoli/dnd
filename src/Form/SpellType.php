<?php

namespace App\Form;

use App\Entity\ClassDef;
use App\Entity\RulesSource;
use App\Entity\Spell;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpellType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nome',
                'attr' => ['class' => 'form-input'],
            ])
            ->add('ruleSlug', null, [
                'label' => 'Slug',
                'attr' => ['class' => 'form-input'],
            ])
            ->add('level', null, [
                'label' => 'Nível',
                'attr' => ['class' => 'form-input', 'min' => 0, 'max' => 9],
            ])
            ->add('school', null, [
                'label' => 'Escola de Magia',
                'attr' => ['class' => 'form-input'],
            ])
            ->add('castingTime', null, [
                'label' => 'Tempo de Conjuração',
                'attr' => ['class' => 'form-input'],
            ])
            ->add('spellRange', null, [
                'label' => 'Alcance',
                'attr' => ['class' => 'form-input'],
            ])
            ->add('targetRangeSort', null, [
                'label' => 'Range Sort Order',
                'attr' => ['class' => 'form-input'],
                'required' => false,
            ])
            ->add('duration', null, [
                'label' => 'Duração',
                'attr' => ['class' => 'form-input'],
            ])
            ->add('componentsJson', TextareaType::class, [
                'label' => 'Componentes (JSON)',
                'attr' => ['class' => 'form-textarea', 'rows' => 3],
                'help' => 'Ex: ["V", "S", "M"]',
                'required' => false,
            ])
            ->add('descriptionMd', TextareaType::class, [
                'label' => 'Descrição (Markdown)',
                'attr' => ['class' => 'form-textarea', 'rows' => 8],
                'required' => false,
            ])
            ->add('higherLevelsMd', TextareaType::class, [
                'label' => 'Em Níveis Superiores (Markdown)',
                'attr' => ['class' => 'form-textarea', 'rows' => 4],
                'required' => false,
            ])
            ->add('page', null, [
                'label' => 'Página',
                'required' => false,
                'attr' => ['class' => 'form-input'],
            ])
            ->add('components', null, [
                'label' => 'Componentes (Texto)',
                'required' => false,
                'attr' => ['class' => 'form-input'],
                'help' => 'Ex: V, S, M (a pinch of dust)',
            ])
            // Portuguese Fields
            ->add('namePt', TextType::class, [
                'label' => 'Nome (PT-BR)',
                'required' => false,
                'attr' => ['class' => 'form-input'],
            ])
            ->add('descriptionMdPt', TextareaType::class, [
                'label' => 'Descrição (PT-BR)',
                'required' => false,
                'attr' => ['class' => 'form-textarea', 'rows' => 8],
            ])
            ->add('higherLevelsMdPt', TextareaType::class, [
                'label' => 'Níveis Superiores (PT-BR)',
                'required' => false,
                'attr' => ['class' => 'form-textarea', 'rows' => 4],
            ])
            ->add('material', TextareaType::class, [
                'label' => 'Material',
                'required' => false,
                'attr' => ['class' => 'form-textarea', 'rows' => 2],
            ])
            ->add('archetype', null, [
                'label' => 'Arquétipo',
                'required' => false,
                'attr' => ['class' => 'form-input'],
            ])
            ->add('circles', null, [
                'label' => 'Círculos',
                'required' => false,
                'attr' => ['class' => 'form-input'],
            ])
            ->add('isRitual', CheckboxType::class, [
                'label' => 'Ritual',
                'required' => false,
                'attr' => ['class' => 'form-checkbox'],
            ])
            ->add('isConcentration', CheckboxType::class, [
                'label' => 'Concentração',
                'required' => false,
                'attr' => ['class' => 'form-checkbox'],
            ])
            ->add('isVerbal', CheckboxType::class, [
                'label' => 'Verbal',
                'required' => false,
                'attr' => ['class' => 'form-checkbox'],
            ])
            ->add('isSomatic', CheckboxType::class, [
                'label' => 'Somático',
                'required' => false,
                'attr' => ['class' => 'form-checkbox'],
            ])
            ->add('isMaterial', CheckboxType::class, [
                'label' => 'Material',
                'required' => false,
                'attr' => ['class' => 'form-checkbox'],
            ])
            ->add('rulesSource', EntityType::class, [
                'class' => RulesSource::class,
                'choice_label' => 'name',
                'label' => 'Fonte de Regras',
            ])
            ->add('classes', EntityType::class, [
                'class' => ClassDef::class,
                'choice_label' => 'name',
                'label' => 'Classes',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
            ])
        ;


        $builder->get('componentsJson')
            ->addModelTransformer(new CallbackTransformer(
                function ($array) {
                    return empty($array) ? '' : json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                },
                function ($string) {
                    if (empty($string)) return [];
                    $decoded = json_decode($string, true);
                    return is_array($decoded) ? $decoded : [];
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Spell::class,
        ]);
    }
}
