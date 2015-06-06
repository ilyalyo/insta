<?php

namespace TaskBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Accounts;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="tasks")
 * @ORM\Entity(repositoryClass="TaskBundle\Entity\TasksRepository")
 */
class Tasks
{
    const CREATED=0;
    const RUNNING=2;
    const DONE=1;
    const INTERRUPTED=3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Accounts")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id",onDelete="CASCADE")
     */
    protected $account_id;

    /**
     * @Assert\Length(
     *      min = 2,
     *      max = 200,
     *      minMessage = "Минимальная длинна {{ limit }} символов",
     *      maxMessage = "Максимальная длинна {{ limit }} символов"
     * )
     * @ORM\Column(type="string", length=250)
     */
    protected $tags;

    /**
     * @ORM\OneToOne(targetEntity="TaskBundle\Entity\Lists", mappedBy="task")
     **/
    private $list;

    /**
     * @Assert\Length(
     *      min = 2,
     *      max = 10000,
     *      minMessage = "Минимальная длинна {{ limit }} символов",
     *      maxMessage = "Максимальная длинна {{ limit }} символов"
     * )
     */
    protected $tmp_tags;

    /**
     * @ORM\Column(type="integer")
     */
    protected $status;

    /**
     * @ORM\Column(type="integer")
     */
    protected $type;

    /**
     *@Assert\Range(
     *      min = 1,
     *      max = 1000,
     *      minMessage = "Минимальное значение - {{ limit }}",
     *      maxMessage = "Максимальное значение - {{ limit }}"
     * )
     *
     * @ORM\Column(type="integer")
     */
    protected $count;

    /**
     * @ORM\OneToMany(targetEntity="TaskBundle\Entity\Actions", mappedBy="task_id")
     */
        protected $actions;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;


    /**
     * @ORM\Column(type="integer")
     */
    protected $speed;
    
    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
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
     * Set tags
     *
     * @param string $tags
     * @return Tasks
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get tags
     *
     * @return string 
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set tags
     *
     * @param string $tags
     * @return Tasks
     */
    public function setTmpTags($tmp_tags)
    {
        $this->tmp_tags= $tmp_tags;

        return $this;
    }

    /**
     * Get tags
     *
     * @return string
     */
    public function getTmpTags()
    {
        return $this->tmp_tags;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Tasks
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Tasks
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set count
     *
     * @param integer $count
     * @return Tasks
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Get count
     *
     * @return integer 
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set account_id
     *
     * @param \AppBundle\Entity\Accounts $accountId
     * @return Tasks
     */
    public function setAccountId(\AppBundle\Entity\Accounts $accountId = null)
    {
        $this->account_id = $accountId;

        return $this;
    }

    /**
     * Get account_id
     *
     * @return \AppBundle\Entity\Accounts 
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Tasks
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->actions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add actions
     *
     * @param \TaskBundle\Entity\Actions $actions
     * @return Tasks
     */
    public function addAction(\TaskBundle\Entity\Actions $actions)
    {
        $this->actions[] = $actions;

        return $this;
    }

    /**
     * Remove actions
     *
     * @param \TaskBundle\Entity\Actions $actions
     */
    public function removeAction(\TaskBundle\Entity\Actions $actions)
    {
        $this->actions->removeElement($actions);
    }

    /**
     * Get actions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Set speed
     *
     * @param integer $speed
     * @return Tasks
     */
    public function setSpeed($speed)
    {
        $this->speed = $speed;

        return $this;
    }

    /**
     * Get speed
     *
     * @return integer 
     */
    public function getSpeed()
    {
        return $this->speed;
    }

    /**
     * Set list
     *
     * @param \TaskBundle\Entity\Lists $list
     * @return Tasks
     */
    public function setList(\TaskBundle\Entity\Lists $list = null)
    {
        $this->list = $list;

        return $this;
    }

    /**
     * Get list
     *
     * @return \TaskBundle\Entity\Lists 
     */
    public function getList()
    {
        return $this->list;
    }
}
