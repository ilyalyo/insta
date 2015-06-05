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
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id",onDelete="CASCADE")
     */
    protected $task_id;

    /**
     * @ORM\Column(type="string", length=250)
     */
    protected $resource_id;

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
