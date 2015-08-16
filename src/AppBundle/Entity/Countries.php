<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="countries")
 */
class Countries
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=3)
     */
    protected $country_code;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $country_name;

    /**
     * Set country_code
     *
     * @param string $countryCode
     * @return Countries
     */
    public function setCountryCode($countryCode)
    {
        $this->country_code = $countryCode;

        return $this;
    }

    /**
     * Get country_code
     *
     * @return string 
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * Set country_name
     *
     * @param string $countryName
     * @return Countries
     */
    public function setCountryName($countryName)
    {
        $this->country_name = $countryName;

        return $this;
    }

    /**
     * Get country_name
     *
     * @return string 
     */
    public function getCountryName()
    {
        return $this->country_name;
    }
}
