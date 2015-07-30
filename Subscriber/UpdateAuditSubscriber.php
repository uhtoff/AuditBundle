<?php
/**
 * Created by PhpStorm.
 * User: Russ
 * Date: 22/07/2015
 * Time: 21:32
 *
 */

namespace Meldon\AuditBundle\Subscriber;


use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Meldon\AuditBundle\Entity\Auditable;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Meldon\AuditBundle\Entity\AuditEntry;
use Meldon\AuditBundle\Services\LogManager;

class UpdateAuditSubscriber implements EventSubscriber
{
    /**
     * @var LogManager
     */
    private $logManager;
    public function setLogManager(LogManager $lm)
    {
        $this->logManager = $lm;
    }
    /**
     * Part of Subscriber interface, returns subscribed events
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(Events::onFlush);
    }
    protected function createAudit(EntityManager $em, $entity, $type, $field = NULL, $oldVal = NULL, $newVal = NULL)
    {
        $changeDate = new \DateTime("now");
        $audit = new AuditEntry(
            get_class($entity),
            $entity->getId(),
            'UPDATE',
            $changeDate,
            $field,
            $oldVal,
            $newVal
        );
        if ($this->logManager) {
            $em->persist($this->logManager->getLog());
            $audit->addLog($this->logManager->getLog());
            $em->getUnitOfWork()
                ->computeChangeSet($em->getClassMetadata('Meldon\AuditBundle\Entity\LogItem'),
                    $this->logManager->getLog());
        }
        $em->persist($audit);
        $em->getUnitOfWork()
            ->computeChangeSet($em->getClassMetadata('Meldon\AuditBundle\Entity\AuditEntry'), $audit);
    }
    /**
     * Acquires unit of work and creates an AuditEntry for every updated entity
     * If the entry is an object it inserts the changed autoincrement ID for that object
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
                $cols = $em->getClassMetadata(get_class($entity))->getColumnNames();
                foreach($cols as $col){
                    $getter = 'get'.ucfirst($col);
                    $this->createAudit($em,$entity,'REMOVE',$col,$entity->$getter());
                }
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