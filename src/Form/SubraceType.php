<?php

namespace App\Form;

use App\Entity\RulesSource;
use App\Entity\Species;
use App\Entity\Subrace;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubraceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('ruleSlug')
            ->add('species', EntityType::class, [
                'class' => Species::class,
                'choice_label' => 'name',
                'label' => 'Parent Species',
            ])
            ->add('descriptionMd', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 6],
                'label' => 'Description (Markdown)',
            ])
            ->add('asiDescription', TextType::class, [
                'required' => false,
                'label' => 'ASI Description',
            ])
            ->add('asi', TextareaType::class, [
                'required' => false,
                'label' => 'ASI (JSON)',
                'attr' => ['rows' => 3],
            ])
            ->add('traits', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 5],
                'label' => 'Traits (Markdown/Text)',
            ])
            ->add('rulesSource', EntityType::class, [
                'class' => RulesSource::class,
                'choice_label' => 'name',
            ])
        ;

        $builder->get('asi')
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
            'data_class' => Subrace::class,
        ]);
    }
}
