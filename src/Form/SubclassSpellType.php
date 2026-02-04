<?php

namespace App\Form;

use App\Entity\Spell;
use App\Entity\SubclassSpell;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubclassSpellType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('levelAcquired', IntegerType::class, [
                'label' => 'Nível',
                'attr' => [
                    'class' => 'form-input w-20 text-center px-2 py-1.5 rounded-lg border-slate-300 dark:border-slate-600 focus:ring-2 focus:ring-primary/20 focus:border-primary',
                    'min' => 1,
                    'max' => 20,
                    'placeholder' => '1'
                ]
            ])
            ->add('spell', EntityType::class, [
                'class' => Spell::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->where('s.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('s.name', 'ASC');
                },
                'choice_label' => function (Spell $spell) {
                    $pt = $spell->getNamePt();
                    $en = $spell->getName();
                    
                    if ($pt && $pt !== $en) {
                        return sprintf('%s (%s)', $pt, $en);
                    }
                    
                    return $en;
                },
                'label' => 'Magia',
                'attr' => [
                    'class' => 'form-select w-full rounded-lg border-slate-300 dark:border-slate-600 focus:ring-2 focus:ring-primary/20 focus:border-primary',
                    'data-controller' => 'tom-select',
                    'placeholder' => 'Selecione uma magia...'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SubclassSpell::class,
        ]);
    }
}
