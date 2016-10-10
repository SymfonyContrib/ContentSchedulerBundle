<?php

namespace SymfonyContrib\Bundle\ContentSchedulerBundle\ScheduledPublishing;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use SymfonyContrib\Bundle\ContentSchedulerBundle\Entity\Schedule;
use Doctrine\ORM\EntityManager;

/**
 * Publishing scheduler.
 * @todo: This needs to be refactored to allow other actions to extend an abstract.
 */
class PublishingScheduler
{
    /**
     * @var EntityManager
     */
    public $em;

    /**
     * PublishingScheduler constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $entityClass
     * @param int    $entityId
     * @param string $method
     *
     * @return null|Schedule
     */
    public function getSchedule($entityClass, $entityId, $method)
    {
        $repo   = $this->em->getRepository('ContentSchedulerBundle:Schedule');
        $action = $entityClass . ':' . $entityId . ':' . $method;

        return $repo->findOneBy(['action' => $action]) ?: new Schedule();
    }

    public function formSetData(Form $form, array $data)
    {
        // Datetime does not inherit properly so they are being done individually.
        if (isset($data['schedulePublish'])) {
            if (!empty($data['publishWhen']['date']) &&
                !empty($data['publishWhen']['time']) &&
                !$form->isSubmitted()
            ) {
                $form->get('schedulePublish')->setData(true);
                $data['publishWhen'] = new \DateTime(implode(' ', $data['publishWhen']));
                $publishWhen = $form->get('publishWhen');
                $publishWhen->get('date')->setData([
                    'year' => $data['publishWhen']->format('Y'),
                    'month' => $data['publishWhen']->format('m'),
                    'day' => $data['publishWhen']->format('d'),
                ]);
                $publishWhen->get('time')->setData([
                    'hour' => $data['publishWhen']->format('H'),
                    'minute' => $data['publishWhen']->format('i'),
                ]);
            } else {
                if (empty($data['publishWhen']['date']) ||
                    empty($data['publishWhen']['time'])
                ) {
                    $form->get('publishWhen')->get('date')
                        ->addError(new FormError('Publish date and time are required for scheduling.'));
                }
            }
        }

        if (isset($data['scheduleUnpublish'])) {
            if (!empty($data['unpublishWhen']['date']) &&
                !empty($data['unpublishWhen']['time']) &&
                !$form->isSubmitted()
            ) {
                $form->get('scheduleUnpublish')->setData(true);
                $data['unpublishWhen'] = new \DateTime(implode(' ', $data['unpublishWhen']));
                $unpublishWhen = $form->get('unpublishWhen');
                $unpublishWhen->get('date')->setData([
                    'year' => $data['unpublishWhen']->format('Y'),
                    'month' => $data['unpublishWhen']->format('m'),
                    'day' => $data['unpublishWhen']->format('d'),
                ]);
                $unpublishWhen->get('time')->setData([
                    'hour' => $data['unpublishWhen']->format('H'),
                    'minute' => $data['unpublishWhen']->format('i'),
                ]);
            } else {
                if (empty($data['unpublishWhen']['date']) ||
                    empty($data['unpublishWhen']['time'])
                ) {
                    $form->get('unpublishWhen')
                        ->addError(new FormError('Unpublish date and time are required for scheduling.'));
                }
            }
        }
    }

    public function createOrUpdateSchedule($entityClass, $entityId, $method, $when)
    {
        $action   = $entityClass.':'.$entityId.':'.$method;
        $schedule = $this->em->getRepository('ContentSchedulerBundle:Schedule')
            ->findOneBy(['action' => $action]) ?: new Schedule();
        $schedule
            ->setAction($action)
            ->setWhen($when);

        $this->em->persist($schedule);
        // Implementing application is responsible for flushing.
    }

    public function deleteScheduleByAction($entityClass, $entityId, $method)
    {
        $action = $entityClass . ':' . $entityId . ':' . $method;
        $this->em->createQueryBuilder()
            ->delete('ContentSchedulerBundle:Schedule', 'c')
            ->where('c.action = :action')
            ->setParameter('action', $action)
            ->getQuery()
            ->execute();
    }

    /**
     * Run only the actions that are due.
     */
    public function runDueActions()
    {
        $repo = $this->em->getRepository('ContentSchedulerBundle:Schedule');
        $due  = $repo->findDue();

        foreach ($due as $schedule) {
            $this->executeSchedule($schedule);
        }
    }

    /**
     * Execute a scheduled action.
     *
     * @param Schedule $schedule
     */
    public function executeSchedule(Schedule $schedule)
    {
        // Split out action into relevant parts.
        list($class, $id, $method) = explode(':', $schedule->getAction());
        // Execute the action.
        $this->em->find($class, $id)->{$method}();
        // Delete the schedule.
        $this->em->remove($schedule);
        $this->em->flush();
    }
}
