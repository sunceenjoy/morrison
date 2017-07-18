<?php

namespace Morrison\Core\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

abstract class BaseRepository extends EntityRepository
{
    /** @var \Doctrine\DBAL\Connection $db */
    protected $db = null;

    public function __construct($em, ClassMetadata $class)
    {
        parent::__construct($em, $class);

        $this->db = $em->getConnection();
    }
}
