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
    public function __construct(LogItemRepository $repository)
    {
        $this->logItem = new LogItem();
        $repository->save($this->logItem);
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