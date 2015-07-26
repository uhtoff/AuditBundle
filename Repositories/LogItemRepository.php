<?php
/**
 * Created by PhpStorm.
 * User: Russ
 * Date: 25/07/2015
 * Time: 21:25
 */

namespace Meldon\AuditBundle\Repositories;


use Doctrine\ORM\EntityRepository;

class LogItemRepository extends EntityRepository
{
    public function save(LogItem $logItem)
    {
        $this->_em->persist($logItem);
    }

}