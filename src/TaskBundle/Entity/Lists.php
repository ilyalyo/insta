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
     * @ORM\JoinColumn(name="id", referencedColumnName="id",onDelete="CASCADE")
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
     * Set task
     *
     * @param \TaskBundle\Entity\Tasks $task
     * @return Lists
     */
    public function setTask(\TaskBundle\Entity\Tasks $task = null)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return \TaskBundle\Entity\Tasks 
     */
    public function getTask()
    {
        return $this->task;
    }
}
