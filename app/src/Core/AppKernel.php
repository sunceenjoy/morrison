<?php

namespace Morrison\Core;

use Morrison\Core\Container;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;

class AppKernel extends HttpKernel
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;

        parent::__construct(
            $this->container['dispatcher'],
            $this->container['controller_resolver']
        );

        $this->addListeners();
    }
    
    private function addListeners()
    {
        $this->dispatcher->addSubscriber(new RouterListener(
            $this->container['router.url_matcher'],
            $this->container['router.request_context'],
            null, /* we don't need logs so far */
            $this->requestStack
        ));
        
        $this->dispatcher->addSubscriber($this->container['event_listener.page_not_found']);
    }
}
