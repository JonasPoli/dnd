<?php

namespace App\Form;

use App\Entity\Attribute;
use App\Entity\Skill;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, ['label' => 'Nome'])
            ->add('key', null, ['label' => 'Chave (Slug/ID)'])
            ->add('attribute', EntityType::class, [
                'class' => Attribute::class,
                'choice_label' => 'name',
                'label' => 'Atributo Base',
                'placeholder' => 'Selecione um atributo',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Exemplos de Usos',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Skill::class,
        ]);
    }
}
