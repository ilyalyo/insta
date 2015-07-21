<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="removed_accounts")
 */
class RemovedAccounts
{
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
    protected $id_deleted;

    /**
     * @ORM\Column(type="integer")
     */
    protected $user;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $instLogin;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $accountId;

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
     * Set id_deleted
     *
     * @param \AppBundle\Entity\Accounts $id_deleted
     * @return RemovedAccounts
     */
    public function setIdDeleted(\AppBundle\Entity\Accounts $id_deleted = null){
        $this->id_deleted = $id_deleted;

        return $this;
    }
    /**
     * Get id_deleted
     *
     * @return integer
     */
    public function getIdDeleted()
    {
        return $this->id_deleted;
    }

    /**
     * Set instLogin
     *
     * @param string $instLogin
     * @return RemovedAccounts
     */
    public function setInstLogin($instLogin)
    {
        $this->instLogin = $instLogin;

        return $this;
    }

    /**
     * Get instLogin
     *
     * @return string 
     */
    public function getInstLogin()
    {
        return $this->instLogin;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     * @return RemovedAccounts
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
     * Set accountId
     *
     * @param string $accountId
     * @return RemovedAccounts
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }

    /**
     * Get accountId
     *
     * @return string 
     */
    public function getAccountId()
    {
        return $this->accountId;
    }
}
