<?php
/**
 * Created by PhpStorm.
 * User: Russ
 * Date: 25/07/2015
 * Time: 12:28
 */

namespace Meldon\AuditBundle\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Meldon\StrongholdBundle\Entity\LogItem;

class InsertLogSubscriber implements EventSubscriber
{
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::postPersist
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        echo get_class($args->getEntity());
        if ($args->getEntity() instanceof LogItem) {
            foreach ($uow->getScheduledEntityInsertions() as $entity) {
                echo get_class($entity);
            }
        }
    }

}