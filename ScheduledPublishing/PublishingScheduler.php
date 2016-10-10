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
