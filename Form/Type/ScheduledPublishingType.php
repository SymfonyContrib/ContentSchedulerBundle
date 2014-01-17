<?php
/**
 *
 */

namespace SymfonyContrib\Bundle\ContentSchedulerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ScheduledPublishingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('schedulePublish', 'checkbox', [
                'mapped' => false,
                'required' => false,
                'label' => 'Schedule publishing at:',
                'attr' => [
                    'data-toggle' => 'collapse',
                    'data-target' => '#schedule-publish-date',
                ],
            ])
            ->add('publishWhen', 'datetime', [
                'mapped' => false,
                'required' => false,
                'label' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('scheduleUnpublish', 'checkbox', [
                'mapped' => false,
                'required' => false,
                'label' => 'Schedule unpublishing at:',
                'attr' => [
                    'data-toggle' => 'collapse',
                    'data-target' => '#schedule-unpublish-date',
                ],
            ])
            ->add('unpublishWhen', 'datetime', [
                'mapped' => false,
                'required' => false,
                'label' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            //'data_class' => 'SymfonyContrib\Bundle\ContentSchedulerBundle\Entity\Schedule',
            'label' => false,
        ]);
    }

    public function getName()
    {
        return 'scheduled_publishing';
    }
}
