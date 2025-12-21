<?php

namespace App\Form;

use App\Entity\RulesSource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RulesSourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('slug', null, [
                'attr' => ['placeholder' => 'ex: open5e'],
                'label' => 'Slug (Identificador único)'
            ])
            ->add('name', null, [
                'attr' => ['placeholder' => 'ex: Open5e Content'],
                'label' => 'Nome da Fonte'
            ])
            ->add('license', null, [
                'attr' => ['placeholder' => 'ex: CC-BY-4.0'],
                'label' => 'Licença'
            ])
            ->add('versionLabel', null, [
                'attr' => ['placeholder' => 'ex: 5.2'],
                'label' => 'Versão'
            ])
            ->add('originUrl', null, [
                'attr' => ['placeholder' => 'ex: https://...'],
                'label' => 'URL de Origem'
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RulesSource::class,
        ]);
    }
}
