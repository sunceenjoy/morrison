<?php

namespace Morrison\Core\HttpKernel\Controller;

use Morrison\Core\Container;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as BaseControllerResolver;
use Psr\Log\LoggerInterface;

/**
 * Class ControllerResolver
 * @package Morrison\Core\HttpKernel\Controller
 */
class ControllerResolver extends BaseControllerResolver
{
    private $container;

    public function __construct(Container $container, LoggerInterface $logger = null)
    {
        $this->container = $container;
        parent::__construct($logger);
    }

    /**
     * Instantiates container aware controllers
     *
     * {@inheritdoc}
     */
    protected function instantiateController($class)
    {
        return new $class($this->container);
    }
}
