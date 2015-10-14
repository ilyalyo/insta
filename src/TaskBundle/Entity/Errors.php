<?php

namespace TaskBundle\Entity;

use TaskBundle\Entity\Tasks;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="errors")
 */
class Errors
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="TaskBundle\Entity\Tasks")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id",onDelete="CASCADE")
     */
    protected $task_id;


    /**
     * @ORM\Column(type="string", length=250)
     */
    protected $message;
    /**
     * @ORM\Column(type="string", length=250)
     */
    protected $tmp;

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
     * Set message
     *
     * @param string $message
     * @return Errors
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set task_id
     *
     * @param \TaskBundle\Entity\Tasks $taskId
     * @return Errors
     */
    public function setTaskId(\TaskBundle\Entity\Tasks $taskId = null)
    {
        $this->task_id = $taskId;

        return $this;
    }

    /**
     * Get task_id
     *
     * @return \TaskBundle\Entity\Tasks 
     */
    public function getTaskId()
    {
        return $this->task_id;
    }

    /**
     * Set tmp
     *
     * @param string $tmp
     * @return Errors
     */
    public function setTmp($tmp)
    {
        $this->tmp = $tmp;

        return $this;
    }

    /**
     * Get tmp
     *
     * @return string 
     */
    public function getTmp()
    {
        return $this->tmp;
    }
}
