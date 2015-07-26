<?php
/**
 * Created by PhpStorm.
 * User: Russ
 * Date: 22/07/2015
 * Time: 21:32
 *
 * @TODO Audit deletes
 */

namespace Meldon\AuditBundle\Subscriber;


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

    /**
     * Acquires unit of work and creates an AuditEntry for every updated entity
     * If the entry is an object it inserts the changed autoincrement ID for that object
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $changeDate = new \DateTime("now");
        $class = $em->getClassMetadata('Meldon\AuditBundle\Entity\AuditEntry');

        foreach($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Auditable) {
                $changeSet = $uow->getEntityChangeSet($entity);

                foreach ($changeSet as $field => $vals) {
                    list($oldValue, $newValue) = $vals;
                    if (is_object($oldValue)) {
                        $oldValue = $oldValue->getId();
                        $newValue = $newValue->getId();
                    }
                    $audit = new AuditEntry(
                        get_class($entity),
                        $entity->getId(),
                        'UPDATE',
                        $changeDate,
                        $field,
                        $oldValue,
                        $newValue
                    );
                    $audit->addLog($this->logManager->getLog());
                    $em->persist($audit);
                    $em->getUnitOfWork()
                        ->computeChangeSet($class, $audit);
                }
            }
        }
    }
}