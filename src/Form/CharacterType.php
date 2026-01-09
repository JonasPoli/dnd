<?php

namespace App\Form;

use App\Entity\Background;
use App\Entity\Character;
use App\Entity\ClassDef;
use App\Entity\Species;
use App\Entity\SubclassDef;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('level')
            ->add('alignment')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('updatedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('classDef', EntityType::class, [
                'class' => ClassDef::class,
                'choice_label' => 'id',
            ])
            ->add('subclassDef', EntityType::class, [
                'class' => SubclassDef::class,
                'choice_label' => 'id',
            ])
            ->add('species', EntityType::class, [
                'class' => Species::class,
                'choice_label' => 'id',
            ])
            ->add('background', EntityType::class, [
                'class' => Background::class,
                'choice_label' => 'name',
            ])
            ->add('subrace', EntityType::class, [
                'class' => \App\Entity\Subrace::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Nenhuma',
            ])
            ->add('alignment', null, ['label' => 'Alinhamento'])
            ->add('imagePath', null, ['label' => 'Caminho da Imagem (URL/Path)'])
            ->add('appearance', null, ['label' => 'Aparência & Personalidade'])
            ->add('bonds', null, ['label' => 'Vínculos'])
            ->add('origin', null, ['label' => 'Origem'])
            
            ->add('skills', EntityType::class, [
                'class' => \App\Entity\Skill::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true, // Checkboxes for easier selection? Or select2? Let's try select2 (default) first or expanded if few. Skills are ~18. Expanded takes space.
                'required' => false,
                'label' => 'Perícias Treinadas',
                'attr' => ['class' => 'select2'], // Hint for JS if we had it, or use standard select
            ])
            ->add('toolProficiencies', EntityType::class, [
                'class' => \App\Entity\Equipment::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
                'label' => 'Proficiência em Ferramentas',
                // This might be HUGE list. Only Tools?
                // The entity is Equipment. We should ideally filter by type 'Tool'.
                // 'query_builder' => function ...
            ])
            ->add('languages', EntityType::class, [
                'class' => \App\Entity\Language::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
                'label' => 'Idiomas',
            ])
            ->add('characterAttributes', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, [
                'entry_type' => CharacterAttributeType::class,
                'entry_options' => ['label' => false],
                'allow_add' => false, // Attributes are fixed 6 usually
                'allow_delete' => false,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Character::class,
        ]);
    }
}
