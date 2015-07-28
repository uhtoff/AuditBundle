<?php
/**
 * Created by PhpStorm.
 * User: Russ
 * Date: 25/07/2015
 * Time: 11:42
 */

namespace Meldon\AuditBundle\Services;

use Meldon\AuditBundle\Entity\LogItem;
use Meldon\AuditBundle\Repositories\LogItemRepository;

class LogManager
{
    /**
     * @var LogItem
     */
    private $logItem;
    /**
     * @var LogItemRepository
     */
    private $repository;
    public function __construct()
    {
        $this->logItem = new LogItem();
    }
    public function setRepository(LogItemRepository $logItemRepository)
    {
        $this->repository = $logItemRepository;
        $this->repository->save($this->logItem);
    }
    public function addText($text)
    {
        $this->logItem->setText($text);
    }
    public function getLog()
    {
        return $this->logItem;
    }
}