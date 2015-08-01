<?php
/**
 * Created by PhpStorm.
 * User: Russ
 * Date: 22/07/2015
 * Time: 21:26
 */

namespace Meldon\AuditBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="audit_entry", options={"collate"="utf8mb4_unicode_ci", "charset"="utf8mb4"})
 */
 
class AuditEntry  {
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var string
     *
     * @ORM\Column(length=250)
     */
    private $resourceName;
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $resourceId;
    /**
     * @var string
     *
     * @ORM\Column(length=10)
     */
    private $changeType;
    /**
     * @var string
     *
     * @ORM\Column(length=250, nullable=true)
     */
    private $fieldName;
    /**
     * @var string
     *
     * @ORM\Column(length=250, nullable=true)
     */
    private $oldValue;
    /**
     * @var string
     *
     * @ORM\Column(length=250, nullable=true)
     */
    private $newValue;
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetimetz")
     */
    private $changeDate;
    /**
     * @var LogItem
     *
     * @ORM\ManyToOne(targetEntity="LogItem")
     */
    private $log;

    public function __construct(
        $resourceName,
        $resourceId,
        $changeType,
        $changeDate,
        $fieldName = NULL,
        $oldValue = NULL,
        $newValue = NULL
    )
    {
        $this->resourceName = $resourceName;
        $this->resourceId = $resourceId;
        $this->fieldName = $fieldName;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
        $this->changeType = $changeType;
        $this->changeDate = $changeDate;
    }

    public function addLog($log)
    {
        $this->log = $log;
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * @return int
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @return string
     */
    public function getChangeType()
    {
        return $this->changeType;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * @return string
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

    /**
     * @return \DateTime
     */
    public function getChangeDate()
    {
        return $this->changeDate;
    }

    /**
     * @return LogItem
     */
    public function getLog()
    {
        return $this->log;
    }
}
