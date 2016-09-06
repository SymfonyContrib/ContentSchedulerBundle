<?php

namespace SymfonyContrib\Bundle\ContentSchedulerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScheduledUnpublishingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('scheduleUnpublish', CheckboxType::class, [
                'mapped'         => false,
                'required'       => false,
                'error_bubbling' => true,
                'label'          => 'Schedule unpublishing',
                'attr'           => [
                    'class' => 'schedule-unpublish',
                ],
            ])
            ->add('unpublishWhen', DateTimeType::class, [
                'mapped'         => false,
                'required'       => false,
                'error_bubbling' => true,
                'label'          => false,
                'date_widget'    => 'single_text',
                'time_widget'    => 'single_text',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //'data_class' => 'SymfonyContrib\Bundle\ContentSchedulerBundle\Entity\Schedule',
            'label' => false,
        ]);
    }
}
