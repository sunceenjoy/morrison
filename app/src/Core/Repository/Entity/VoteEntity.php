<?php

namespace Morrison\Core\Repository\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Morrison\Core\Repository\VoteRepository")
 * @ORM\Table(name="vote_list")
 * @ORM\HasLifecycleCallbacks
 */
class VoteEntity extends BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id",type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\Column(type="string",length=255) */
    protected $email_address;

    /** @ORM\Column(type="integer") */
    protected $sticker;

    /** @ORM\Column(type="datetime") */
    protected $created_at;
   
    /** @ORM\Column(type="string",length=15) */
    protected $ip_address;

    public function setEmailAddress($emailAddress)
    {
        $this->email_address = $emailAddress;
    }

    public function getEmailAddress()
    {
        return $this->email_address;
    }

    public function setSticker($sticker)
    {
        $this->sticker = $sticker;
    }

    public function getSticker()
    {
        return $this->sticker;
    }

    public function setIpAddress($ipAddress)
    {
        return $this->ip_address = $ipAddress;
    }
    
    public function getIpAddress()
    {
        $this->roles = $this->ip_address;
    }
    
    /** some others functions will be on later */
    
    /**
     * @ORM\PreFlush
     * see http://symfony.com/doc/current/cookbook/doctrine/file_uploads.html
     * see http://doctrine-orm.readthedocs.org/en/latest/reference/events.html#lifecycle-events
     */
    public function preFlush()
    {
        $this->created_at = new \Datetime('now');
    }
}
