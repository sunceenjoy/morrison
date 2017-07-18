<?php

namespace Morrison\Core\Repository\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Morrison\Core\Repository\EmailQueueRepository")
 * @ORM\Table(name="email_send_queue")
 * @ORM\HasLifecycleCallbacks
 */
class EmailEntity extends BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id",type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\Column(type="string",length=255) */
    protected $email_address;
   
    /** @ORM\Column(type="string",length=100) */
    protected $subject;
    
    /** @ORM\Column(type="text") */
    protected $content;
    
    /** @ORM\Column(type="integer") */
    protected $type;

    /**
     * 1: text/html
     * 2: text/plain
     */
    const TYPE_HTML  = 1;
    const TYPE_PLAIN = 2;
    
    public function setEmailAddress($emailAddress)
    {
        $this->email_address = $emailAddress;
    }

    public function getEmailAddress()
    {
        return $this->email_address;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }
    
    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }
    
    public static function getTypeText($type)
    {
        switch ($type) {
            case self::TYPE_HTML:
                return 'text/html';
            case self::TYPE_PLAIN:
                return 'text/plain';
            default:
                assert(false);
        }
    }
}
