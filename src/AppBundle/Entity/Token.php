<?php

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use UserBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="tokens")
 */
class Token
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Accounts")
     * @ORM\JoinColumn(name="account", referencedColumnName="id",onDelete="CASCADE")
     */
    protected $account;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $token;
    
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $client;

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
     * Set token
     *
     * @param string $token
     * @return Token
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
     * Set account
     *
     * @param \AppBundle\Entity\Accounts $account
     * @return Token
     */
    public function setAccount(\AppBundle\Entity\Accounts $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return \AppBundle\Entity\Accounts 
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set client
     *
     * @param string $client
     * @return Token
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return string 
     */
    public function getClient()
    {
        return $this->client;
    }
}
