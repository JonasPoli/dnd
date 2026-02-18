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
use Vich\UploaderBundle\Form\Type\VichImageType;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Repository\MonsterRepository;

class MonsterType extends AbstractType
{
    private MonsterRepository $monsterRepository;

    public function __construct(MonsterRepository $monsterRepository)
    {
        $this->monsterRepository = $monsterRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isActive', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, [
                'label' => 'Ativo',
                'required' => false,
            ])
            ->add('name')
            ->add('namePt', TextType::class, ['required' => false, 'label' => 'Name (PT)'])
            ->add('ruleSlug')
            ->add('size', ChoiceType::class, [
                'required' => false,
                'choices' => $this->monsterRepository->getDistinctValues('size'),
                'placeholder' => 'Selecione ou deixe vazio',
                'label' => 'Size'
            ])
            ->add('sizePt', ChoiceType::class, [
                'required' => false,
                'label' => 'Size (PT)',
                'choices' => $this->monsterRepository->getDistinctValues('sizePt'),
                'placeholder' => 'Selecione ou deixe vazio'
            ])
            ->add('type', ChoiceType::class, [
                'required' => false,
                'choices' => $this->monsterRepository->getDistinctValues('type'),
                'placeholder' => 'Selecione ou deixe vazio'
            ])
            ->add('typePt', ChoiceType::class, [
                'required' => false,
                'label' => 'Type (PT)',
                'choices' => $this->monsterRepository->getDistinctValues('typePt'),
                'placeholder' => 'Selecione ou deixe vazio'
            ])
            ->add('subtype', ChoiceType::class, [
                'required' => false,
                'choices' => $this->monsterRepository->getDistinctValues('subtype'),
                'placeholder' => 'Selecione ou deixe vazio'
            ])
            ->add('subtypePt', ChoiceType::class, [
                'required' => false,
                'label' => 'Subtype (PT)',
                'choices' => $this->monsterRepository->getDistinctValues('subtypePt'),
                'placeholder' => 'Selecione ou deixe vazio'
            ])
            ->add('group', ChoiceType::class, [
                'required' => false,
                'label' => 'Monster Group',
                'choices' => $this->monsterRepository->getDistinctValues('monsterGroup'),
                'placeholder' => 'Selecione ou deixe vazio'
            ])
            ->add('groupPt', ChoiceType::class, [
                'required' => false,
                'label' => 'Monster Group (PT)',
                'choices' => $this->monsterRepository->getDistinctValues('monsterGroupPt'),
                'placeholder' => 'Selecione ou deixe vazio'
            ])
            ->add('alignment', ChoiceType::class, [
                'required' => false,
                'choices' => $this->monsterRepository->getDistinctValues('alignment'),
                'placeholder' => 'Selecione ou deixe vazio'
            ])
            ->add('alignmentPt', ChoiceType::class, [
                'required' => false,
                'label' => 'Alignment (PT)',
                'choices' => $this->monsterRepository->getDistinctValues('alignmentPt'),
                'placeholder' => 'Selecione ou deixe vazio'
            ])
            ->add('challengeRating')
            ->add('armorClass', IntegerType::class, ['required' => false])
            ->add('armorDesc', TextType::class, ['required' => false])
            ->add('armorDescPt', TextType::class, ['required' => false, 'label' => 'Armor Desc (PT)'])
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
            ->add('descriptionMdPt', TextareaType::class, [
                'required' => false,
                'label' => 'Description (PT)',
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
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => true,
            ])
            ->add('pageNo', IntegerType::class, ['required' => false])
            ->add('spellList', TextareaType::class, ['required' => false, 'label' => 'Spell List (JSON)'])
            ->add('srcJson', TextareaType::class, ['required' => false, 'label' => 'Sources (JSON)'])
            ->add('srcJsonPt', TextareaType::class, ['required' => false, 'label' => 'Sources (PT) (JSON)'])
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
        $builder->get('spellList')->addModelTransformer($jsonTransformer);
        $builder->get('srcJson')->addModelTransformer($jsonTransformer);
        $builder->get('srcJsonPt')->addModelTransformer($jsonTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Monster::class,
            'csrf_protection' => false,
        ]);
    }
}
