<?php

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use UserBundle\Entity\User;
use AppBundle\Entity\Token;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="accounts_log")
 */
class AccountsLog
{
    /**
     * Constructor
     */
    public function __construct(\AppBundle\Entity\Accounts $account)
    {
        $this->user = $account->getUser();
        $this->instLogin = $account->getInstLogin();
        $this->instPass = $account->getInstPass();
        $this->proxy = $account->getProxy();
        $this->country = $account->getCountry();
        $this->createdAt = $account->getCreatedAt();
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
     * @ORM\Column(type="string", length=100)
     */
    protected $instLogin;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $instPass;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Proxy")
     * @ORM\JoinColumn(name="proxy", referencedColumnName="id")
     */
    protected $proxy;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $try;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Countries")
     * @ORM\JoinColumn(name="country", referencedColumnName="country_code")
     */
    protected $country;

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
     * @return AccountsLog
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
     * @return AccountsLog
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return AccountsLog
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
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     * @return AccountsLog
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
     * Set country
     *
     * @param \AppBundle\Entity\Countries $country
     * @return AccountsLog
     */
    public function setCountry(\AppBundle\Entity\Countries $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return \AppBundle\Entity\Countries 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set try
     *
     * @param integer $try
     * @return AccountsLog
     */
    public function setTry($try)
    {
        $this->try = $try;

        return $this;
    }

    /**
     * Get try
     *
     * @return integer 
     */
    public function getTry()
    {
        return $this->try;
    }

    /**
     * Set proxy
     *
     * @param \AppBundle\Entity\Proxy $proxy
     * @return AccountsLog
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
}
