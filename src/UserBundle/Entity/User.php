<?php

namespace UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        $this->accounts = new ArrayCollection();
        $this->maxAccounts = 2;
        $this->timezone = 'Europe/Moscow';
        $this->createdAt = new \DateTime();
    }
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Accounts", mappedBy="id")
     */
    protected $accounts;

    /**
     * @ORM\Column(type="integer")
     */
    protected $maxAccounts;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="string", length=50,nullable = TRUE)
     */
    protected $timezone;

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
     * Add accounts
     *
     * @param \AppBundle\Entity\Accounts $accounts
     * @return User
     */
    public function addAccount(\AppBundle\Entity\Accounts $accounts)
    {
        $this->accounts[] = $accounts;

        return $this;
    }

    /**
     * Remove accounts
     *
     * @param \AppBundle\Entity\Accounts $accounts
     */
    public function removeAccount(\AppBundle\Entity\Accounts $accounts)
    {
        $this->accounts->removeElement($accounts);
    }

    /**
     * Get accounts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * Set maxAccounts
     *
     * @param integer $maxAccounts
     * @return User
     */
    public function setMaxAccounts($maxAccounts)
    {
        $this->maxAccounts = $maxAccounts;

        return $this;
    }

    /**
     * Get maxAccounts
     *
     * @return integer 
     */
    public function getMaxAccounts()
    {
        return $this->maxAccounts;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     * @return User
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get timezone
     *
     * @return string 
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return User
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
