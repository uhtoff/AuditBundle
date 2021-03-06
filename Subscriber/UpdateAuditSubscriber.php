<?php
/**
 * Created by PhpStorm.
 * User: Russ
 * Date: 22/07/2015
 * Time: 21:32
 * @TODO Test with collections, almost certainly doesn't work
 */

namespace Meldon\AuditBundle\Subscriber;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Meldon\AuditBundle\Entity\Auditable;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Meldon\AuditBundle\Entity\AuditEntry;
use Meldon\AuditBundle\Entity\LogItem;
use Meldon\StrongholdBundle\Events\LogFileEvent;

class UpdateAuditSubscriber implements EventSubscriber
{
    /**
     * @var LogItem
     */
    private $log;

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
        return array(Events::onFlush);
    }

    /**
     * Create a new AuditEntry and add a log entry to it if the log is set
     *
     * @param EntityManager $em
     * @param $entity
     * @param $type
     * @param null $field
     * @param null $oldVal
     * @param null $newVal
     */
    protected function createAudit(EntityManager $em, $entity, $type, $field = NULL, $oldVal = NULL, $newVal = NULL)
    {
        $changeDate = new \DateTime("now");
        $audit = new AuditEntry(
            get_class($entity),
            $entity->getId(),
            $type,
            $changeDate,
            $field,
            $oldVal,
            $newVal
        );
        if ( $this->log ) {
            $audit->addLog($this->log);
        }
        $em->persist($audit);
        $em->getUnitOfWork()
            ->computeChangeSet($em->getClassMetadata(get_class($audit)), $audit);
    }
    /**
     * Acquires unit of work and creates an AuditEntry for every updated and deleted entity
     * If the entry is an object it inserts the ID for that object
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Auditable) {
                $changeSet = $uow->getEntityChangeSet($entity);
                foreach ($changeSet as $field => $vals) {
                    list($oldValue, $newValue) = $vals;
                    if (is_object($oldValue)) {
                        $oldValue = $oldValue->getId();
                    }
                    if (is_object($newValue)) {
                        $newValue = $newValue->getId();
                    }
                    $this->createAudit($em,$entity,'UPDATE',$field,$oldValue,$newValue);
                }
            }
        }

        foreach($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof Auditable) {
                // Iterate through columns (plain values)
                $cols = $em->getClassMetadata(get_class($entity))->getColumnNames();
                foreach($cols as $col){
                    $getter = 'get'.ucfirst($col);
                    $this->createAudit($em,$entity,'REMOVE',$col,$entity->$getter());
                }
                // Iterate through associations (objects) - probably won't work for nested associations
                // @TODO Nested assocations fix
                $assocs = $em->getClassMetadata(get_class($entity))->getAssociationNames();
                foreach($assocs as $assoc){
                    $getter = 'get'.ucfirst($assoc);
                    if($entity->$getter() instanceof Collection){
                        foreach($entity->$getter() as $assocEntity){
                            $this->createAudit($em,$entity,'REMOVE',$assoc,$assocEntity->getId());
                        }
                    } else {
                        $this->createAudit($em,$entity,'REMOVE',$assoc,$entity->$getter()->getId());
                    }
                }
            }
        }
    }
}