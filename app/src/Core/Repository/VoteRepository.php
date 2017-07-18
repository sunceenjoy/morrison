<?php

namespace Morrison\Core\Repository;

use Morrison\Core\Repository\Entity\VoteEntity;

class VoteRepository extends BaseRepository
{
    /**
     * Find a record by email address
     * @param string $emailAddress
     * @return VoteEntity | false
     */
    public function getByEmailAddress($emailAddress)
    {
        return $this->findOneBy(['email_address' => $emailAddress]);
    }
    
    /**
     * Add a new vote record
     * @param string $emailAddress
     * @param integer $sticker
     * @param string $ipAddress
     * @return boolean
     */
    public function addNew($emailAddress, $sticker, $ipAddress)
    {
        /** @var VoteEntity $voteEntity */
        $voteEntity = new VoteEntity();
        $voteEntity->setEmailAddress($emailAddress);
        $voteEntity->setSticker($sticker);
        $voteEntity->setIpAddress($ipAddress);
        $this->_em->persist($voteEntity);
        $this->_em->flush();
        return true;
    }
}
