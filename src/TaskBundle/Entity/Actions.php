<?php

namespace TaskBundle\Entity;

use TaskBundle\Entity\Tasks;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="actions")
 */
class Actions
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="TaskBundle\Entity\Tasks")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
     */
    protected $task_id;

    /**
     * @ORM\Column(type="string", length=250)
     */
    protected $target_user_id;

    /**
     * @ORM\Column(type="string", length=250)
     */
    protected $responce;

    /**
     * @ORM\Column(type="string", length=250)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=250)
     */
    protected $resource_id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }
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
     * Set target_user_id
     *
     * @param string $targetUserId
     * @return Actions
     */
    public function setTargetUserId($targetUserId)
    {
        $this->target_user_id = $targetUserId;

        return $this;
    }

    /**
     * Get target_user_id
     *
     * @return string 
     */
    public function getTargetUserId()
    {
        return $this->target_user_id;
    }

    /**
     * Set responce
     *
     * @param string $responce
     * @return Actions
     */
    public function setResponce($responce)
    {
        $this->responce = $responce;

        return $this;
    }

    /**
     * Get responce
     *
     * @return string 
     */
    public function getResponce()
    {
        return $this->responce;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Actions
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set resource_id
     *
     * @param string $resourceId
     * @return Actions
     */
    public function setResourceId($resourceId)
    {
        $this->resource_id = $resourceId;

        return $this;
    }

    /**
     * Get resource_id
     *
     * @return string 
     */
    public function getResourceId()
    {
        return $this->resource_id;
    }

    /**
     * Set task_id
     *
     * @param \TaskBundle\Entity\Tasks $taskId
     * @return Actions
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
}
