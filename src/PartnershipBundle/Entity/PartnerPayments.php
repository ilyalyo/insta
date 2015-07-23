<?php

namespace PartnershipBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use UserBundle\Entity\User;
use AppBundle\Entity\Token;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="partnerpayments")
 */
class PartnerPayments
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
    * @ORM\Column(type="boolean")
    */
    protected $isWithdraw;

    /**
     * @ORM\Column(type="string", length=250)
     */
    protected $amount;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     * @return PartnerPayments
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
     * Set isWithdraw
     *
     * @param integer $isWithdraw
     * @return PartnerPayments
     */
    public function setIsWithdraw($isWithdraw)
    {
        $this->isWithdraw = $isWithdraw;

        return $this;
    }

    /**
     * Get isWithdraw
     *
     * @return PartnerPayments
     */
    public function getIsWithdraw()
    {
        return $this->isWithdraw;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     * @return PartnerPayments
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return PartnerPayments
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    public function __construct()
    {
        $this->date=(new \DateTime())->add(new \DateInterval('PT3H'));
       // $this->$tasks = new ArrayCollection();
    }

}
