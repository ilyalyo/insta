<?php

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use UserBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="accounts")
 */
class Accounts
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
     * @ORM\OneToMany(targetEntity="TaskBundle\Entity\Tasks", mappedBy="account_id")
     */
    protected $tasks;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Proxy")
     * @ORM\JoinColumn(name="proxy", referencedColumnName="id")
     */
    protected $proxy;

    public function __construct()
    {
       // $this->$tasks = new ArrayCollection();
    }

    /**
     * @ORM\Column(type="string", length=100,unique=true)
     */
    protected $account_id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $token;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $instLogin;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $instPass;
    /**
     * @ORM\Column(type="integer")
     */
    protected $isTrue;

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
     * Set account_id
     *
     * @param string $accountId
     * @return Accounts
     */
    public function setAccountId($accountId)
    {
        $this->account_id = $accountId;

        return $this;
    }

    /**
     * Get account_id
     *
     * @return string 
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Accounts
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
     * Set token
     *
     * @param string $token
     * @return Accounts
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     * @return Accounts
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
     * Add tasks
     *
     * @param \TaskBundle\Entity\Tasks $tasks
     * @return Accounts
     */
    public function addTask(\TaskBundle\Entity\Tasks $tasks)
    {
        $this->tasks[] = $tasks;

        return $this;
    }

    /**
     * Remove tasks
     *
     * @param \TaskBundle\Entity\Tasks $tasks
     */
    public function removeTask(\TaskBundle\Entity\Tasks $tasks)
    {
        $this->tasks->removeElement($tasks);
    }

    /**
     * Get tasks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Set proxy
     *
     * @param \AppBundle\Entity\Proxy $proxy
     * @return Accounts
     */
    public function setProxy(\AppBundle\Entity\Proxy $proxy = null)
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * Get proxy
     *
     * @return \AppBundle\Entity\Proxy 
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * Set instLogin
     *
     * @param string $instLogin
     * @return Accounts
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
     * Set instPass
     *
     * @param string $instPass
     * @return Accounts
     */
    public function setInstPass($instPass)
    {
        $this->instPass = $instPass;

        return $this;
    }

    /**
     * Get instPass
     *
     * @return string 
     */
    public function getInstPass()
    {
        return $this->instPass;
    }

    /**
     * Set isTrue
     *
     * @param integer $isTrue
     * @return Accounts
     */
    public function setIsTrue($isTrue)
    {
        $this->isTrue = $isTrue;

        return $this;
    }

    /**
     * Get isTrue
     *
     * @return integer 
     */
    public function getIsTrue()
    {
        return $this->isTrue;
    }
}
