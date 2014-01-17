<?php
/**
 *
 */

namespace SymfonyContrib\Bundle\ContentSchedulerBundle\Doctrine;

use Doctrine\Common\EventSubscriber as EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class EventSubscriber implements EventSubscriberInterface {

    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
        ];
    }

    /**
     * Delete schedules when entities are removed.
     *
     * postRemove event does not pass the entity primary key.
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $em        = $args->getEntityManager();
        $entity    = $args->getEntity();
        $aliases   = array_flip($em->getConfiguration()->getEntityNamespaces());
        $class     = get_class($entity);
        $namespace = substr($class, 0, strrpos($class, '\\'));

        if (!isset($aliases[$namespace])) {
            return;
        }

        $bundle     = $aliases[$namespace];
        $entityName = substr($class, strrpos($class, '\\') + 1);
        $metadata   = $em->getClassMetadata($class);
        $idField    = $metadata->getSingleIdentifierFieldName();
        $id         = $entity->{'get' . ucfirst($idField)}();
        $action     = $bundle . ':' . $entityName . ':' . $id;

        // Delete any schedule matching this entity.
        $em->createQueryBuilder()
            ->delete('ContentSchedulerBundle:Schedule', 's')
            ->where('s.action LIKE :action')
            ->setParameter('action', $action . '%')
            ->getQuery()
            ->execute();
    }

}
