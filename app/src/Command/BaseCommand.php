<?php

namespace Morrison\Command;

use Morrison\Core\Container;
use Symfony\Component\Console\Command\Command;

abstract class BaseCommand extends Command
{
    /** @var Container $container */
    protected $container;
    
    /** @var \Doctrine\DBAL\Connection $db */
    protected $db;
    
    /** @var \Doctrine\ORM\EntityManager $em */
    protected $em;

    public function __construct(Container $container)
    {
        parent::__construct();

        $this->container = $container;
        $this->db = $this->container['db.morrison'];
        $this->em = $this->container['doctrine.entity_manager'];
    }
}
