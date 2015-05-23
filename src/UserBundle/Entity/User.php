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
}
