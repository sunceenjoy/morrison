<?php

namespace Morrison\Core\Repository;

use Morrison\Core\Repository\Entity\EmailEntity;

class EmailQueueRepository extends BaseRepository
{
    /**
     * Retrieve records
     * @param integer $limit
     * @param bool $delete true delete the retrieved records from queue
     * @return EmailEntity[]
     */
    public function getEmails($limit, $delete = false)
    {
        $entiyArray =  $this->findBy([], null, $limit);
        
        if ($delete) {
            foreach ($entiyArray as $entity) {
                $this->_em->remove($entity);
            }
            $this->_em->flush();
        }
        return $entiyArray;
    }
    
    /**
     * Add a new record to queue
     * @param string $emailAddress
     * @param string $subject
     * @param string $content email body
     * @param integer $type mail type
     * @return boolean
     */
    public function addNew($emailAddress, $subject, $content, $type)
    {
        /** @var EmailEntity $emailEntity */
        $emailEntity = new EmailEntity();
        $emailEntity->setEmailAddress($emailAddress);
        $emailEntity->setSubject($subject);
        $emailEntity->setContent($content);
        $emailEntity->setType($type);
        $this->_em->persist($emailEntity);
        $this->_em->flush();
        return true;
    }
}
