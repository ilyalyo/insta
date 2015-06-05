<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="history")
 */
class History
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Accounts")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id",onDelete="CASCADE")
     */
    protected $account_id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $followed_by;
    /**
     * @ORM\Column(type="integer")
     */
    protected $follows;

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
     * Set followed_by
     *
     * @param integer $followedBy
     * @return History
     */
    public function setFollowedBy($followedBy)
    {
        $this->followed_by = $followedBy;

        return $this;
    }

    /**
     * Get followed_by
     *
     * @return integer 
     */
    public function getFollowedBy()
    {
        return $this->followed_by;
    }

    /**
     * Set follows
     *
     * @param integer $follows
     * @return History
     */
    public function setFollows($follows)
    {
        $this->follows = $follows;

        return $this;
    }

    /**
     * Get follows
     *
     * @return integer 
     */
    public function getFollows()
    {
        return $this->follows;
    }

    /**
     * Set account_id
     *
     * @param \AppBundle\Entity\Accounts $accountId
     * @return History
     */
    public function setAccountId(\AppBundle\Entity\Accounts $accountId = null)
    {
        $this->account_id = $accountId;

        return $this;
    }

    /**
     * Get account_id
     *
     * @return \AppBundle\Entity\Accounts 
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

}
