<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity
 * @ORM\Table(name="support")
 */
class Support
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->isRead = 0;
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    protected $user;

    /**
     * @Assert\Length(
     *      max = 250,
     *      maxMessage = "Максимальная длинна {{ limit }} символов"
     * )
     * @ORM\Column(type="string", length=250)
     */
    protected $message;

    /**
     * @ORM\Column(type="integer")
     */
    protected $isRead;

    /**
     * @ORM\Column(type="integer")
     */
    protected $isAnswer;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

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
     * @return Support
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
     * Set isRead
     *
     * @param integer $isRead
     * @return Support
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;

        return $this;
    }

    /**
     * Get isRead
     *
     * @return integer 
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    /**
     * Set isAnswer
     *
     * @param integer $isAnswer
     * @return Support
     */
    public function setIsAnswer($isAnswer)
    {
        $this->isAnswer = $isAnswer;

        return $this;
    }

    /**
     * Get isAnswer
     *
     * @return integer 
     */
    public function getIsAnswer()
    {
        return $this->isAnswer;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     * @return Support
     */
    public function setUser(\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Support
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
}
