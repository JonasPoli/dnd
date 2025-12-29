<?php

namespace App\Form;

use App\Entity\ClassDef;
use App\Entity\RulesSource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClassDefType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ruleSlug', TextType::class, ['label' => 'Slug', 'attr' => ['class' => 'form-input']])
            ->add('name', TextType::class, ['label' => 'Nome', 'attr' => ['class' => 'form-input']])
            ->add('hitDie', IntegerType::class, ['label' => 'Dado de Vida (Ex: 8)', 'attr' => ['class' => 'form-input']])
            ->add('subtypesName', TextType::class, ['label' => 'Nome da Subclasse (Ex: Arquétipo Marcial)', 'required' => false, 'attr' => ['class' => 'form-input']])
            ->add('spellcastingAbility', TextType::class, ['label' => 'Habilidade de Conjuração', 'required' => false, 'attr' => ['class' => 'form-input']])
            ->add('hpAt1stLevel', TextType::class, ['label' => 'PV no Nível 1', 'required' => false, 'attr' => ['class' => 'form-input']])
            ->add('hpAtHigherLevels', TextType::class, ['label' => 'PV em Níveis Superiores', 'required' => false, 'attr' => ['class' => 'form-input']])
            
            ->add('primaryAbilities', TextareaType::class, ['label' => 'Atributos Primários (JSON)', 'required' => false, 'attr' => ['class' => 'form-textarea', 'rows' => 3]])
            ->add('savingThrowProficiencies', TextareaType::class, ['label' => 'Prof. Resistência (JSON)', 'required' => false, 'attr' => ['class' => 'form-textarea', 'rows' => 3]])
            
            ->add('profArmor', TextareaType::class, ['label' => 'Prof. Armadura', 'required' => false, 'attr' => ['rows' => 2]])
            ->add('profWeapons', TextareaType::class, ['label' => 'Prof. Armas', 'required' => false, 'attr' => ['rows' => 2]])
            ->add('profTools', TextareaType::class, ['label' => 'Prof. Ferramentas', 'required' => false, 'attr' => ['rows' => 2]])
            ->add('profSkills', TextareaType::class, ['label' => 'Prof. Perícias', 'required' => false, 'attr' => ['rows' => 2]])
            ->add('equipment', TextareaType::class, ['label' => 'Equipamento Inicial', 'required' => false, 'attr' => ['rows' => 4]])
            
            ->add('descriptionMd', TextareaType::class, ['label' => 'Descrição (MD)', 'required' => false, 'attr' => ['rows' => 6]])
            ->add('classTableMd', TextareaType::class, ['label' => 'Tabela da Classe (MD)', 'required' => false, 'attr' => ['rows' => 6]])
            ->add('characterCreationHelp', TextareaType::class, ['label' => 'Ajuda ao Criar Personagem (MD)', 'required' => false, 'attr' => ['rows' => 6]])
            
            ->add('rulesSource', EntityType::class, [
                'class' => RulesSource::class,
                'choice_label' => 'name',
                'label' => 'Fonte de Regras',
            ])
        ;

        $jsonTransformer = new CallbackTransformer(
            function ($array) {
                return empty($array) ? '' : json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            },
            function ($string) {
                if (empty($string)) return [];
                $decoded = json_decode($string, true);
                return is_array($decoded) ? $decoded : [];
            }
        );

        $builder->get('primaryAbilities')->addModelTransformer($jsonTransformer);
        $builder->get('savingThrowProficiencies')->addModelTransformer($jsonTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClassDef::class,
            'csrf_protection' => false, // FIXME: Persisting CSRF error with Turbo on HTTPS. Disabled to unblock.
        ]);
    }
}
