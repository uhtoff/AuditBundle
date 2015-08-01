<?php
/**
 * Created by PhpStorm.
 * User: Russ
 * Date: 01/08/2015
 * Time: 11:53
 *
 * @TODO Revert LogItem
 * @TODO Revert auditEntry and all following changes
 * @TODO Revert Collection change
 */

namespace Meldon\AuditBundle\Services;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Meldon\AuditBundle\Entity\AuditEntry;

class AuditManager
{
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param $entity
     * @return string
     */
    protected function getEntityName($entity)
    {
        return $this->em->getClassMetadata(get_class($entity))->getName();
    }

    /**
     * @param $entity
     * @return EntityRepository
     */
    protected function getEntityRepository($entity)
    {
        return $this->em->getRepository($this->getEntityName($entity));
    }
    public function revertAudit(AuditEntry $audit)
    {
        $entity = $this->em->getRepository($audit->getResourceName())->find($audit->getResourceId());
        $getter = 'get'.ucfirst($audit->getFieldName());
        $setter = 'set'.ucfirst($audit->getFieldName());
        $entityValue = $entity->$getter();
        if(!is_object($entityValue)){
            if($audit->getNewValue() == $entityValue){
                $entity->$setter($audit->getOldValue());
            } else {
                throw EntityInWrongStateException;
            }
        } elseif(!$entityValue instanceof Collection){
            $oldValue = $this->getEntityRepository($entityValue)->find($audit->getOldValue());
            $newValue = $this->getEntityRepository($entityValue)->find($audit->getNewValue());
            if($newValue === $entityValue){

                $entity->$setter($oldValue);
            } else {
                throw EntityInWrongStateException;
            }

        } else {

        }
    }
}