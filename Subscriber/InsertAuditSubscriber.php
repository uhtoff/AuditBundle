<?php
/**
 * Created by PhpStorm.
 * User: Russ
 * Date: 22/07/2015
 * Time: 23:02
 */

namespace Meldon\AuditBundle\Subscriber;


use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Meldon\AuditBundle\Entity\Auditable;
use Meldon\AuditBundle\Entity\AuditEntry;

class InsertAuditSubscriber implements EventSubscriber
{
    private $needsFlush = false;
    public function getSubscribedEvents()
    {
        return array(
            Events::postPersist,
            Events::postFlush
        );
    }
    public function postPersist(LifecycleEventArgs $args) {

        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if($entity instanceof Auditable) {
            $changeDate = new \DateTime("now");
            $audit = new AuditEntry(
                get_class($entity),
                $entity->getId(),
                'INSERT',
                $changeDate
            );
            $em->persist($audit);
            $this->needsFlush = true;
        }
    }

    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if ($this->needsFlush) {
            $this->needsFlush = false;
            $eventArgs->getEntityManager()->flush();
        }
    }

}