<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="proxy")
 */
class Proxy
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=25)
     */
    protected $ip;

    /**
     * @ORM\Column(type="string", length=11)
     */
    protected $port;

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
     * Set ip
     *
     * @param string $ip
     * @return Proxy
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set port
     *
     * @param string $port
     * @return Proxy
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port
     *
     * @return string 
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set country
     *
     * @param \AppBundle\Entity\Countries $country
     * @return Proxy
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
}
