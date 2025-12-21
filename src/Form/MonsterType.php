<?php

namespace App\Form;

use App\Entity\Monster;
use App\Entity\RulesSource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonsterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('ruleSlug')
            ->add('size')
            ->add('type')
            ->add('subtype', TextType::class, ['required' => false])
            ->add('group', TextType::class, ['required' => false, 'label' => 'Monster Group'])
            ->add('alignment')
            ->add('challengeRating')
            ->add('armorClass', IntegerType::class, ['required' => false])
            ->add('armorDesc', TextType::class, ['required' => false])
            ->add('hitPoints', IntegerType::class, ['required' => false])
            ->add('hitDice', TextType::class, ['required' => false])
            ->add('strength', IntegerType::class, ['required' => false])
            ->add('dexterity', IntegerType::class, ['required' => false])
            ->add('constitution', IntegerType::class, ['required' => false])
            ->add('intelligence', IntegerType::class, ['required' => false])
            ->add('wisdom', IntegerType::class, ['required' => false])
            ->add('charisma', IntegerType::class, ['required' => false])
            ->add('strengthSave', IntegerType::class, ['required' => false])
            ->add('dexteritySave', IntegerType::class, ['required' => false])
            ->add('constitutionSave', IntegerType::class, ['required' => false])
            ->add('intelligenceSave', IntegerType::class, ['required' => false])
            ->add('wisdomSave', IntegerType::class, ['required' => false])
            ->add('charismaSave', IntegerType::class, ['required' => false])
            ->add('perception', IntegerType::class, ['required' => false])
            ->add('senses')
            ->add('languages')
            ->add('damageImmunities', TextareaType::class, ['required' => false])
            ->add('damageResistances', TextareaType::class, ['required' => false])
            ->add('damageVulnerabilities', TextareaType::class, ['required' => false])
            ->add('conditionImmunities', TextareaType::class, ['required' => false])
            ->add('descriptionMd', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 10]
            ])
            ->add('legendaryDesc', TextareaType::class, ['required' => false])
            ->add('speedJson', TextareaType::class, ['required' => false, 'label' => 'Speed (JSON)'])
            ->add('skillsJson', TextareaType::class, ['required' => false, 'label' => 'Skills (JSON)'])
            ->add('specialAbilitiesJson', TextareaType::class, ['required' => false, 'label' => 'Special Abilities (JSON)', 'attr' => ['rows' => 10]])
            ->add('actionsJson', TextareaType::class, ['required' => false, 'label' => 'Actions (JSON)', 'attr' => ['rows' => 10]])
            ->add('bonusActionsJson', TextareaType::class, ['required' => false, 'label' => 'Bonus Actions (JSON)', 'attr' => ['rows' => 5]])
            ->add('reactionsJson', TextareaType::class, ['required' => false, 'label' => 'Reactions (JSON)', 'attr' => ['rows' => 5]])
            ->add('legendaryActionsJson', TextareaType::class, ['required' => false, 'label' => 'Legendary Actions (JSON)', 'attr' => ['rows' => 5]])
            ->add('environments', TextareaType::class, ['required' => false, 'label' => 'Environments (JSON)'])
            ->add('imgMain', TextType::class, ['required' => false])
            ->add('pageNo', IntegerType::class, ['required' => false])
            ->add('rulesSource', EntityType::class, [
                'class' => RulesSource::class,
                'choice_label' => 'name',
            ])
        ;

        $jsonTransformer = new CallbackTransformer(
            function ($array) {
                // transform the array to a string
                return empty($array) ? '' : json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            },
            function ($string) {
                // transform the string back to an array
                if (empty($string)) {
                    return [];
                }
                $decoded = json_decode($string, true);
                return is_array($decoded) ? $decoded : [];
            }
        );

        $builder->get('speedJson')->addModelTransformer($jsonTransformer);
        $builder->get('skillsJson')->addModelTransformer($jsonTransformer);
        $builder->get('specialAbilitiesJson')->addModelTransformer($jsonTransformer);
        $builder->get('actionsJson')->addModelTransformer($jsonTransformer);
        $builder->get('bonusActionsJson')->addModelTransformer($jsonTransformer);
        $builder->get('reactionsJson')->addModelTransformer($jsonTransformer);
        $builder->get('legendaryActionsJson')->addModelTransformer($jsonTransformer);
        $builder->get('environments')->addModelTransformer($jsonTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Monster::class,
        ]);
    }
}
