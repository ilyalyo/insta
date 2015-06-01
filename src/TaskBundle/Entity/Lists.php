<?php

namespace TaskBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use TaskBundle\Entity\Tasks;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="lists")
 */
class Lists
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="TaskBundle\Entity\Tasks")
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
    protected $task;


    /**
     * @Assert\Length(
     *      min = 2,
     *      max = 5000,
     *      minMessage = "Минимальная длинна {{ limit }} символов",
     *      maxMessage = "Максимальная длинна {{ limit }} символов"
     * )
     * @ORM\Column(type="string", length=5000)
     */
    protected $list;



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
     * Set list
     *
     * @param string $list
     * @return Lists
     */
    public function setList($list)
    {
        $this->list = $list;

        return $this;
    }

    /**
     * Get list
     *
     * @return string 
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * Set task_id
     *
     * @param \TaskBundle\Entity\Tasks $taskId
     * @return Lists
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
