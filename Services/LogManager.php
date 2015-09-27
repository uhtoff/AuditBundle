<?php
/**
 * Created by PhpStorm.
 * User: Russ
 * Date: 25/07/2015
 * Time: 11:42
 */

namespace Meldon\AuditBundle\Services;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface LogManager
{
    public function __construct(EntityRepository $logItemRepository, EventDispatcherInterface $dispatcher);

    public function addText($text);

    public function getLog();

}