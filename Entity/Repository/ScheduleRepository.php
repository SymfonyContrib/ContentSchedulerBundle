<?php
namespace SymfonyContrib\Bundle\ContentSchedulerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class ScheduleRepository extends EntityRepository
{
    /**
     * Find all due content schedules.
     *
     * @return array
     */
    public function findDue()
    {
        $dql = "SELECT s
                FROM ContentSchedulerBundle:Schedule s
                WHERE s.when <= :when";

        return $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('when', new \DateTime())
            ->getResult();
    }
}
