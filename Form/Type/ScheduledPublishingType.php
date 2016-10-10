<?php

namespace SymfonyContrib\Bundle\ContentSchedulerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SymfonyContrib\Bundle\ContentSchedulerBundle\Entity\Schedule;
use SymfonyContrib\Bundle\ContentSchedulerBundle\ScheduledPublishing\PublishingScheduler;

/**
 * Form to schedule publishing of content.
 */
class ScheduledPublishingType extends AbstractType
{
    /** @var  PublishingScheduler */
    protected $scheduler;

    public function __construct(PublishingScheduler $scheduler)
    {
        $this->scheduler = $scheduler;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('action', CheckboxType::class, [
                'required'       => false,
                'error_bubbling' => true,
                'label'          => 'Schedule publishing',
                'attr'           => [
                    'class' => 'schedule-publish',
                ],
            ])
            ->add('when', DateTimeType::class, [
                'required'       => false,
                'error_bubbling' => true,
                'label'          => false,
                'date_widget'    => 'single_text',
                'time_widget'    => 'single_text',
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);

            $builder
                ->get('action')
                ->addModelTransformer(new CallbackTransformer(
                    function ($action) {
                        // Transform the action string to a boolean.
                        return (bool)$action;
                    },
                    function ($isScheduled) {
                        // Transform the boolean back to a action.
                        return $isScheduled ? Schedule::ACTION_PUBLISH : null;
                    }
                ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'SymfonyContrib\Bundle\ContentSchedulerBundle\Entity\Schedule',
            'label'      => false,
            'mapped'     => false,
        ]);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $content = $event->getForm()->getParent()->getData();

        $schedule = $this->scheduler->getSchedule(get_class($content), $content->getId(), Schedule::ACTION_PUBLISH);
        $event->setData($schedule);
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $schedule = $event->getData();
        $content  = $event->getForm()->getParent()->getData();

        // @todo: Handle validation.
        /*if (empty($data['publishWhen']['date']) ||
            empty($data['publishWhen']['time'])
        ) {
            $form->get('publishWhen')->get('date')
                 ->addError(new FormError('Publish date and time are required for scheduling.'));
        }*/

        if ($schedule->getAction()) {
            $schedule->setAction(get_class($content).':'.$content->getId().':'.Schedule::ACTION_PUBLISH);
            $this->scheduler->em->persist($schedule);
        }
    }
}
