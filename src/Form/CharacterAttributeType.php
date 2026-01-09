<?php

namespace App\Form;

use App\Entity\CharacterAttribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CharacterAttributeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // We listener to set the label dynamically based on the underlying attribute name
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $charAttr = $event->getData();
            $form = $event->getForm();

            $label = 'Atributo';
            if ($charAttr && $charAttr->getAttribute()) {
                $label = $charAttr->getAttribute()->getName();
            }

            $form->add('value', IntegerType::class, [
                'label' => $label,
                'attr' => ['min' => 1, 'max' => 30],
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CharacterAttribute::class,
        ]);
    }
}
