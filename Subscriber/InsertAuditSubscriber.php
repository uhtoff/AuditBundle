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
use Meldon\AuditBundle\Entity\LogItem;
use Meldon\StrongholdBundle\Events\LogFileEvent;

class InsertAuditSubscriber implements EventSubscriber
{
    /**
     * @var LogItem
     */
    private $log;
    /**
     * @var bool
     */
    private $needsFlush = false;

    /**
     * Receives LogFileEvent and extracts new log item from it
     * @param LogFileEvent $log
     */
    public function setLog(LogFileEvent $log)
    {
        $this->log = $log->getLog();
    }

    /**
     * Part of Subscriber interface, returns subscribed events
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::postPersist,
            Events::postFlush
        );
    }

    /**
     * If Auditable entity is newly persisted then add an insert entry into the AuditEntry
     * Mark to be flushed if any audits added
     * @param LifecycleEventArgs $args
     */
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
            if ($this->log) {
                $audit->addLog($this->log);
            }
            $em->persist($audit);
            $this->needsFlush = true;
        }
    }

    /**
     * Ensure AuditEntry is flushed and stop recurrent flushing
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if ($this->needsFlush) {
            $this->needsFlush = false;
            $eventArgs->getEntityManager()->flush();
        }
    }

}