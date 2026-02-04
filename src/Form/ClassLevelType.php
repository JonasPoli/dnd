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
                'label' => 'PB (Proficiency Bonus)',
                'help' => 'Bônus de Proficiência. Valor somado a testes que o personagem é proficiente. Padrão D&D: +2 (Lv 1-4), +3 (Lv 5-8), +4 (Lv 9-12), etc.',
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
                'help' => 'Número de magias que podem ser preparadas (ex: Clérigo, Druida).',
                'attr' => ['class' => 'w-16']
            ])
            ->add('featsKnown', IntegerType::class, [
                'required' => false,
                'label' => 'Talentos',
                'help' => 'Quantidade de talentos de classe que o personagem pode escolher neste nível.',
                'attr' => ['class' => 'w-16']
            ])
            ->add('spellSlotsJson', TextareaType::class, [
                'required' => false,
                'label' => 'Slots de Magia (JSON)',
                'help' => 'Slots por círculo. Ex: {"1": 4, "2": 2} indica 4 slots de 1º círculo e 2 de 2º círculo.',
                'attr' => ['rows' => 1, 'class' => 'font-mono text-xs w-full']
            ])
            ->add('featuresList', TextareaType::class, [
                'required' => false,
                'label' => 'Recursos de Classe (Texto)',
                'help' => 'Lista de nomes dos recursos ganhos neste nível (ex: Ataque Extra, Evasão).',
                'attr' => ['rows' => 2, 'class' => 'w-full']
            ])
            ->add('customDie', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'required' => false,
                'label' => 'Dado de Energia/Recurso',
                'help' => 'Ex: d6, d8, d10 (para classes com dado progressivo como Bardo/Monge/Psiônico).',
                'attr' => ['class' => 'w-full']
            ])
            ->add('customCount', IntegerType::class, [
                'required' => false,
                'label' => 'Qtd. de Dados/Pontos',
                'help' => 'Quantidade do recurso especial (ex: Pontos de Ki, Dados de Superioridade).',
                'attr' => ['class' => 'w-full']
            ])
            ->add('featuresConfig', TextareaType::class, [
                'required' => false,
                'label' => 'Features Config (JSON)',
                'help' => 'Configuração técnica de recursos (ex: pontos de ki, dados de furtivo). Ex: {"sneak_dice": "1d6"}.',
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
