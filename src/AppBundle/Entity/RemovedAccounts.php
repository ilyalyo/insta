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
    protected $user;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $instLogin;

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
}
