<?php
/**
 * Created by PhpStorm.
 * User: Russ
 * Date: 25/07/2015
 * Time: 11:42
 */

namespace Meldon\AuditBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Meldon\AuditBundle\Repositories\LogItemRepository")
 * @ORM\Table(name="log_item", options={"collate"="utf8mb4_unicode_ci", "charset"="utf8mb4"})
 */
 
class LogItem  {
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
     * @ORM\Column(type="text")
     */
    private $text;
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return LogItem
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }
}
